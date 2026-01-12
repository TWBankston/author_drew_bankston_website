<?php
/**
 * Square Payment Integration
 * Handles Square Web Payments SDK integration and payment processing
 */

defined( 'ABSPATH' ) || exit;

class DBC_Square_Payment {

    /**
     * Initialize the class
     */
    public static function init() {
        // AJAX handlers for payment processing
        add_action( 'wp_ajax_dbc_process_payment', array( __CLASS__, 'process_payment' ) );
        add_action( 'wp_ajax_nopriv_dbc_process_payment', array( __CLASS__, 'process_payment' ) );
        
        // Webhook handler
        add_action( 'init', array( __CLASS__, 'register_webhook_endpoint' ) );
        add_action( 'template_redirect', array( __CLASS__, 'handle_webhook' ) );
    }

    /**
     * Get Square configuration
     */
    public static function get_config() {
        $app_id = get_option( 'dbt_square_app_id', '' );
        $access_token = get_option( 'dbt_square_access_token', '' );
        $location_id = get_option( 'dbt_square_location_id', '' );
        
        return array(
            'enabled'        => ! empty( $app_id ) && ! empty( $access_token ) && ! empty( $location_id ),
            'sandbox'        => get_option( 'dbt_square_sandbox', '1' ) === '1', // Default to sandbox
            'application_id' => $app_id,
            'access_token'   => $access_token,
            'location_id'    => $location_id,
        );
    }

    /**
     * Check if Square is properly configured
     */
    public static function is_configured() {
        $app_id = get_option( 'dbt_square_app_id', '' );
        $access_token = get_option( 'dbt_square_access_token', '' );
        $location_id = get_option( 'dbt_square_location_id', '' );
        
        return ! empty( $app_id ) && ! empty( $access_token ) && ! empty( $location_id );
    }

    /**
     * Process payment via Square API
     */
    public static function process_payment() {
        // Start output buffering to catch any stray output
        ob_start();
        
        try {
            // Verify nonce
            check_ajax_referer( 'dbc-checkout-nonce', 'nonce' );

            // Get payment data
            $source_id = isset( $_POST['source_id'] ) ? sanitize_text_field( $_POST['source_id'] ) : '';
            $amount = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0;
            $customer_data = isset( $_POST['customer'] ) ? json_decode( stripslashes( $_POST['customer'] ), true ) : array();
            $cart_items = isset( $_POST['cart_items'] ) ? json_decode( stripslashes( $_POST['cart_items'] ), true ) : array();

            // Validate
            if ( empty( $source_id ) || $amount <= 0 ) {
                ob_end_clean();
                wp_send_json_error( array( 'message' => 'Invalid payment data.' ) );
                return;
            }

            $config = self::get_config();
        
        if ( ! $config['enabled'] ) {
            ob_end_clean();
            wp_send_json_error( array( 'message' => 'Payment processing is not configured.' ) );
            return;
        }

        // Convert amount to cents (Square uses smallest currency unit)
        $amount_cents = intval( $amount * 100 );

        // Prepare API endpoint
        $base_url = $config['sandbox'] 
            ? 'https://connect.squareupsandbox.com' 
            : 'https://connect.squareup.com';
        
        $api_url = $base_url . '/v2/payments';

        // Generate idempotency key (unique for this transaction)
        $idempotency_key = wp_generate_uuid4();

        // Prepare payment request
        $payment_data = array(
            'source_id'       => $source_id,
            'idempotency_key' => $idempotency_key,
            'amount_money'    => array(
                'amount'   => $amount_cents,
                'currency' => 'USD',
            ),
            'location_id'     => $config['location_id'],
            'note'            => 'Drew Bankston - Book Order',
        );

        // Add customer data if available
        if ( ! empty( $customer_data['email'] ) ) {
            $payment_data['buyer_email_address'] = sanitize_email( $customer_data['email'] );
        }

        // Make API request to Square
        $response = wp_remote_post( $api_url, array(
            'headers' => array(
                'Square-Version' => '2024-12-18', // Use latest API version
                'Authorization'  => 'Bearer ' . $config['access_token'],
                'Content-Type'   => 'application/json',
            ),
            'body'    => wp_json_encode( $payment_data ),
            'timeout' => 30,
        ) );

        // Check for errors
        if ( is_wp_error( $response ) ) {
            error_log( 'Square API Error: ' . $response->get_error_message() );
            ob_end_clean();
            wp_send_json_error( array( 'message' => 'Payment failed. Please try again.' ) );
            return;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $status_code = wp_remote_retrieve_response_code( $response );

        // Handle Square API response
        if ( $status_code === 200 && isset( $body['payment'] ) ) {
            $payment = $body['payment'];
            
            // Create order in database
            $order_id = self::create_order( array(
                'payment_id'    => $payment['id'],
                'amount'        => $amount,
                'status'        => $payment['status'],
                'customer_data' => $customer_data,
                'cart_items'    => $cart_items,
                'idempotency_key' => $idempotency_key,
            ) );

            // Clear cart
            DBC_Cart::clear_cart();

            // Clean any output before sending JSON
            ob_end_clean();
            
            wp_send_json_success( array(
                'message'  => 'Payment successful!',
                'order_id' => $order_id,
                'redirect' => home_url( '/order-confirmation/?order=' . $order_id ),
            ) );
        } else {
            // Payment failed
            $error_message = isset( $body['errors'][0]['detail'] ) 
                ? $body['errors'][0]['detail'] 
                : 'Payment failed. Please try again.';
            
            error_log( 'Square Payment Failed: ' . print_r( $body, true ) );
            
            ob_end_clean();
            wp_send_json_error( array( 'message' => $error_message ) );
        }
        } catch ( Exception $e ) {
            error_log( 'Square Payment Exception: ' . $e->getMessage() );
            ob_end_clean();
            wp_send_json_error( array( 'message' => 'Payment processing error: ' . $e->getMessage() ) );
        }
    }

    /**
     * Create order in database
     */
    private static function create_order( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';

        $customer_data = $data['customer_data'];
        $cart_items = $data['cart_items'];
        
        // Handle user account creation
        $user_id = get_current_user_id();
        
        if ( ! $user_id && ! empty( $customer_data['create_account'] ) && ! empty( $customer_data['account_password'] ) ) {
            // Create new user account
            $email = sanitize_email( $customer_data['email'] );
            $username = sanitize_user( $email ); // Use email as username
            $password = $customer_data['account_password'];
            
            // Check if user already exists
            if ( ! username_exists( $username ) && ! email_exists( $email ) ) {
                $user_id = wp_create_user( $username, $password, $email );
                
                if ( ! is_wp_error( $user_id ) ) {
                    // Update user meta
                    wp_update_user( array(
                        'ID'         => $user_id,
                        'first_name' => sanitize_text_field( $customer_data['first_name'] ),
                        'last_name'  => sanitize_text_field( $customer_data['last_name'] ),
                    ) );
                    
                    // Note: We can't log in during AJAX, user will need to log in manually
                }
            } else {
                $user_id = 0; // User exists, don't create duplicate
            }
        }

        // Insert order
        $insert_result = $wpdb->insert(
            $table_name,
            array(
                'user_id'          => $user_id,
                'payment_id'       => $data['payment_id'],
                'status'           => $data['status'],
                'total_amount'     => $data['amount'],
                'currency'         => 'USD',
                'customer_email'   => sanitize_email( $customer_data['email'] ),
                'customer_name'    => sanitize_text_field( $customer_data['first_name'] . ' ' . $customer_data['last_name'] ),
                'customer_phone'   => sanitize_text_field( $customer_data['phone'] ?? '' ),
                'shipping_address' => wp_json_encode( array(
                    'address_1' => sanitize_text_field( $customer_data['address_1'] ?? '' ),
                    'address_2' => sanitize_text_field( $customer_data['address_2'] ?? '' ),
                    'city'      => sanitize_text_field( $customer_data['city'] ?? '' ),
                    'state'     => sanitize_text_field( $customer_data['state'] ?? '' ),
                    'zip'       => sanitize_text_field( $customer_data['zip'] ?? '' ),
                    'country'   => sanitize_text_field( $customer_data['country'] ?? '' ),
                ) ),
                'order_items'      => wp_json_encode( $cart_items ),
                'signature_request' => isset( $customer_data['signature_request'] ) ? 1 : 0,
                'signature_message' => sanitize_textarea_field( $customer_data['signature_message'] ?? '' ),
                'idempotency_key'  => $data['idempotency_key'],
                'created_at'       => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
        );
        
        if ( $insert_result === false ) {
            error_log( 'Order insert failed: ' . $wpdb->last_error );
            return false;
        }

        $order_id = $wpdb->insert_id;

        // Send order confirmation email
        self::send_order_confirmation_email( $order_id );

        return $order_id;
    }

    /**
     * Send order confirmation email
     */
    private static function send_order_confirmation_email( $order_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        $order = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $table_name WHERE id = %d", 
            $order_id 
        ) );

        if ( ! $order ) {
            return;
        }

        $to = $order->customer_email;
        $subject = 'Order Confirmation - Drew Bankston';
        
        $items = json_decode( $order->order_items, true );
        $items_html = '';
        
        foreach ( $items as $item ) {
            $items_html .= sprintf(
                '<li>%s × %d - $%s</li>',
                esc_html( $item['name'] ),
                intval( $item['quantity'] ),
                number_format( floatval( $item['price'] ) * intval( $item['quantity'] ), 2 )
            );
        }

        $message = sprintf(
            '<html><body style="font-family: sans-serif; line-height: 1.6;">
                <h2>Thank you for your order!</h2>
                <p>Hi %s,</p>
                <p>Your order has been confirmed and will be processed shortly.</p>
                <h3>Order Details</h3>
                <p><strong>Order #:</strong> %d</p>
                <p><strong>Total:</strong> $%s</p>
                <h4>Items:</h4>
                <ul>%s</ul>
                <p>You will receive a shipping confirmation once your order ships.</p>
                <p>Best regards,<br>Drew Bankston</p>
            </body></html>',
            esc_html( $order->customer_name ),
            $order_id,
            number_format( $order->total_amount, 2 ),
            $items_html
        );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        
        wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Register webhook endpoint
     */
    public static function register_webhook_endpoint() {
        add_rewrite_rule( '^square-webhook/?$', 'index.php?square_webhook=1', 'top' );
        add_rewrite_tag( '%square_webhook%', '([^&]+)' );
    }

    /**
     * Handle Square webhook
     */
    public static function handle_webhook() {
        if ( ! get_query_var( 'square_webhook' ) ) {
            return;
        }

        // Get raw POST data
        $body = file_get_contents( 'php://input' );
        $event = json_decode( $body, true );

        // Log webhook for debugging
        error_log( 'Square Webhook Received: ' . print_r( $event, true ) );

        // Handle different event types
        if ( isset( $event['type'] ) ) {
            switch ( $event['type'] ) {
                case 'payment.updated':
                    self::handle_payment_update( $event['data']['object']['payment'] );
                    break;
            }
        }

        // Return 200 OK
        status_header( 200 );
        exit;
    }

    /**
     * Handle payment update webhook
     */
    private static function handle_payment_update( $payment ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';

        $payment_id = $payment['id'];
        $status = $payment['status'];

        // Update order status
        $wpdb->update(
            $table_name,
            array( 'status' => $status ),
            array( 'payment_id' => $payment_id ),
            array( '%s' ),
            array( '%s' )
        );
    }

    /**
     * Create orders table on plugin activation
     */
    public static function create_orders_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            payment_id varchar(255) NOT NULL,
            status varchar(50) NOT NULL,
            total_amount decimal(10,2) NOT NULL,
            currency varchar(3) NOT NULL,
            customer_email varchar(255) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_phone varchar(50) DEFAULT '',
            shipping_address text,
            order_items longtext NOT NULL,
            signature_request tinyint(1) DEFAULT 0,
            signature_message text,
            idempotency_key varchar(255) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY payment_id (payment_id),
            KEY customer_email (customer_email),
            KEY created_at (created_at),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
