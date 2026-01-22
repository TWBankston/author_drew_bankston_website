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
                    
                    // Handle newsletter subscription for new account
                    if ( ! empty( $customer_data['subscribe_newsletter'] ) ) {
                        update_user_meta( $user_id, 'dbc_newsletter_subscribed', '1' );
                    }
                    
                    // Note: We can't log in during AJAX, user will need to log in manually
                }
            } else {
                $user_id = 0; // User exists, don't create duplicate
            }
        }
        
        // Subscribe to Mailchimp if user opted in (works for both new accounts and guest checkout)
        if ( ! empty( $customer_data['subscribe_newsletter'] ) ) {
            $email = sanitize_email( $customer_data['email'] );
            $first_name = sanitize_text_field( $customer_data['first_name'] ?? '' );
            $last_name = sanitize_text_field( $customer_data['last_name'] ?? '' );
            
            // Use the newsletter class to subscribe to Mailchimp with purchase tag
            self::subscribe_to_mailchimp_with_purchase( $email, $first_name, $last_name );
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

        // Send order confirmation email to customer
        self::send_order_confirmation_email( $order_id );
        
        // Send sale notification email to author
        self::send_sale_notification_email( $order_id );

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

        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: Drew Bankston <noreply@drewbankston.com>',
        );
        
        wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Send sale notification email to author
     */
    private static function send_sale_notification_email( $order_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        $order = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $table_name WHERE id = %d", 
            $order_id 
        ) );

        if ( ! $order ) {
            return;
        }

        // Get notification email from settings
        $to = DBC_Orders_Settings::get_notification_email();
        if ( empty( $to ) ) {
            return; // Notifications disabled
        }
        $subject = '🎉 New Book Sale! Order #' . $order_id . ' - $' . number_format( $order->total_amount, 2 );
        
        $items = json_decode( $order->order_items, true );
        $address = json_decode( $order->shipping_address, true );
        
        $items_html = '';
        $has_physical = false;
        
        if ( is_array( $items ) ) {
            foreach ( $items as $item ) {
                $item_total = floatval( $item['price'] ?? 0 ) * intval( $item['quantity'] ?? 1 );
                $items_html .= sprintf(
                    '<tr>
                        <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.1);">%s</td>
                        <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center;">%d</td>
                        <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: right;">$%s</td>
                        <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: right;">$%s</td>
                    </tr>',
                    esc_html( $item['name'] ?? 'Unknown Item' ),
                    intval( $item['quantity'] ?? 1 ),
                    number_format( floatval( $item['price'] ?? 0 ), 2 ),
                    number_format( $item_total, 2 )
                );
                
                // Check if physical item
                if ( isset( $item['type'] ) && $item['type'] === 'signed' ) {
                    $has_physical = true;
                }
            }
        }
        
        // Build shipping address HTML
        $shipping_html = '<p style="color: #8b9dc3;"><em>No shipping required (digital only)</em></p>';
        if ( $has_physical && $address && ! empty( $address['address_1'] ) ) {
            $shipping_html = sprintf(
                '<p style="margin: 0; color: #c9d1e3; line-height: 1.6;">
                    %s<br>
                    %s%s<br>
                    %s, %s %s<br>
                    %s
                </p>',
                esc_html( $order->customer_name ),
                esc_html( $address['address_1'] ),
                ! empty( $address['address_2'] ) ? '<br>' . esc_html( $address['address_2'] ) : '',
                esc_html( $address['city'] ?? '' ),
                esc_html( $address['state'] ?? '' ),
                esc_html( $address['zip'] ?? '' ),
                esc_html( $address['country'] ?? 'US' )
            );
        }
        
        // Build signature request HTML
        $signature_html = '';
        if ( $order->signature_request ) {
            $signature_html = sprintf(
                '<div style="background: linear-gradient(135deg, #3d2b5c 0%%, #2d1f45 100%%); padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #a78bfa;">
                    <h3 style="margin: 0 0 10px; color: #a78bfa; font-size: 16px;">✍️ Personalized Signature Requested</h3>
                    <blockquote style="margin: 0; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 6px; font-style: italic; color: #e9d5ff;">
                        %s
                    </blockquote>
                </div>',
                ! empty( $order->signature_message ) ? esc_html( $order->signature_message ) : '<em>No specific message provided</em>'
            );
        }

        $message = self::get_author_email_template(
            '🎉 New Order!',
            sprintf(
                '<div style="text-align: center; margin-bottom: 30px;">
                    <div style="display: inline-block; background: linear-gradient(135deg, #10b981 0%%, #059669 100%%); color: white; font-size: 32px; font-weight: 700; padding: 15px 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);">
                        $%s
                    </div>
                    <p style="margin: 15px 0 0; color: #8b9dc3; font-size: 14px;">Order #%d</p>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                    <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                        <h3 style="margin: 0 0 15px; color: #ffffff; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Customer</h3>
                        <p style="margin: 0 0 8px; color: #ffffff; font-weight: 600; font-size: 18px;">%s</p>
                        <p style="margin: 0 0 5px;"><a href="mailto:%s" style="color: #60a5fa; text-decoration: none;">%s</a></p>
                        %s
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                        <h3 style="margin: 0 0 15px; color: #ffffff; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Ship To</h3>
                        %s
                    </div>
                </div>
                
                %s
                
                <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h3 style="margin: 0 0 15px; color: #ffffff; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Order Items</h3>
                    <table style="width: 100%%; border-collapse: collapse; color: #c9d1e3;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(255,255,255,0.1);">
                                <th style="padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #8b9dc3;">Item</th>
                                <th style="padding: 12px; text-align: center; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #8b9dc3;">Qty</th>
                                <th style="padding: 12px; text-align: right; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #8b9dc3;">Price</th>
                                <th style="padding: 12px; text-align: right; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #8b9dc3;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            %s
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="padding: 15px 12px; text-align: right; font-weight: 600; color: #ffffff; border-top: 2px solid rgba(255,255,255,0.1);">Total</td>
                                <td style="padding: 15px 12px; text-align: right; font-weight: 700; font-size: 20px; color: #10b981; border-top: 2px solid rgba(255,255,255,0.1);">$%s</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="%s" style="display: inline-block; background: linear-gradient(135deg, #8b5cf6 0%%, #6d28d9 100%%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);">
                        View Order in Dashboard →
                    </a>
                </div>',
                number_format( $order->total_amount, 2 ),
                $order_id,
                esc_html( $order->customer_name ),
                esc_attr( $order->customer_email ),
                esc_html( $order->customer_email ),
                ! empty( $order->customer_phone ) ? '<p style="margin: 5px 0 0; color: #8b9dc3;">' . esc_html( $order->customer_phone ) . '</p>' : '',
                $shipping_html,
                $signature_html,
                $items_html,
                number_format( $order->total_amount, 2 ),
                admin_url( 'admin.php?page=dbc-orders' )
            )
        );

        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: Drew Bankston Website <noreply@drewbankston.com>',
        );
        
        wp_mail( $to, $subject, $message, $headers );
    }
    
    /**
     * Get branded email template for author notifications
     */
    private static function get_author_email_template( $title, $content ) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0f; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 700px; margin: 0 auto; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f1a 100%); border-radius: 16px; overflow: hidden; box-shadow: 0 25px 80px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px 40px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(59, 130, 246, 0.1) 100%); border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 600; color: #ffffff;">Drew Bankston</h1>
                                        <p style="margin: 5px 0 0; font-size: 12px; color: #8b9dc3; text-transform: uppercase; letter-spacing: 2px;">Sales Dashboard</p>
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="display: inline-block; background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">New Sale</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <div style="color: #c9d1e3; font-size: 15px; line-height: 1.7;">
                                ' . $content . '
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 25px 40px; background: rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.1);">
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="margin: 0; font-size: 12px; color: #5a6785;">
                                            This is an automated notification from your website.<br>
                                            <a href="https://drewbankston.com/wp-admin" style="color: #60a5fa; text-decoration: none;">Login to Dashboard</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
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

    /**
     * Subscribe email to Mailchimp with purchase-specific tags
     */
    private static function subscribe_to_mailchimp_with_purchase( $email, $first_name = '', $last_name = '' ) {
        $api_key = get_option( 'dbc_mailchimp_api_key' );
        $list_id = get_option( 'dbc_mailchimp_list_id' );
        $server = get_option( 'dbc_mailchimp_server_prefix' );
        
        if ( empty( $api_key ) || empty( $list_id ) || empty( $server ) ) {
            // Mailchimp not configured, skip silently
            error_log( 'Mailchimp not configured - skipping checkout subscription for: ' . $email );
            return array( 'status' => 'skipped', 'message' => 'Mailchimp not configured' );
        }
        
        $url = "https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}/members";
        
        $data = array(
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => array(
                'FNAME' => $first_name,
                'LNAME' => $last_name,
            ),
            'tags' => array( 'Website Purchase', 'Checkout Signup' ),
        );
        
        $response = wp_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( $data ),
            'timeout' => 15,
        ) );
        
        if ( is_wp_error( $response ) ) {
            error_log( 'Mailchimp subscription error for ' . $email . ': ' . $response->get_error_message() );
            return array( 'status' => 'error', 'message' => $response->get_error_message() );
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $code = wp_remote_retrieve_response_code( $response );
        
        // 200 = success, 400 with "Member Exists" = already subscribed (OK)
        if ( $code === 200 || ( $code === 400 && strpos( $body['title'] ?? '', 'Member Exists' ) !== false ) ) {
            return array( 'status' => 'success' );
        }
        
        error_log( 'Mailchimp subscription failed for ' . $email . ': ' . ( $body['detail'] ?? 'Unknown error' ) );
        return array( 'status' => 'error', 'message' => $body['detail'] ?? 'Unknown error' );
    }
}
