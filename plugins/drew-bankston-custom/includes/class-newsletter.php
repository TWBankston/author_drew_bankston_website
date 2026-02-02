<?php
/**
 * Newsletter Integration - Mailchimp + Free Chapter Downloads + Member Content
 */

defined( 'ABSPATH' ) || exit;

class DBC_Newsletter {
    
    /**
     * Define available member-only content
     */
    public static function get_member_content() {
        return array(
            'the-t-shirt' => array(
                'title'       => 'The T-Shirt',
                'description' => 'A short story by Drew Bankston',
                'file'        => 'The-T-Shirt.pdf',
                'type'        => 'short_story',
                'cover_image' => '', // Optional: add cover image URL if available
            ),
        );
    }
    
    public static function init() {
        // AJAX handlers
        add_action( 'wp_ajax_dbc_newsletter_subscribe', array( __CLASS__, 'handle_subscribe' ) );
        add_action( 'wp_ajax_nopriv_dbc_newsletter_subscribe', array( __CLASS__, 'handle_subscribe' ) );
        add_action( 'wp_ajax_dbc_footer_subscribe', array( __CLASS__, 'handle_footer_subscribe' ) );
        add_action( 'wp_ajax_nopriv_dbc_footer_subscribe', array( __CLASS__, 'handle_footer_subscribe' ) );
        add_action( 'wp_ajax_dbc_check_download_access', array( __CLASS__, 'check_download_access' ) );
        add_action( 'wp_ajax_nopriv_dbc_check_download_access', array( __CLASS__, 'check_download_access' ) );
        
        // Member content download handlers
        add_action( 'wp_ajax_dbc_download_member_content', array( __CLASS__, 'ajax_download_member_content' ) );
        
        // Download handler
        add_action( 'init', array( __CLASS__, 'handle_download' ) );
        add_action( 'init', array( __CLASS__, 'handle_member_content_download' ) );
        
        // Admin settings
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        
        // Grant member perks on account creation
        add_action( 'user_register', array( __CLASS__, 'grant_new_member_perks' ) );
    }
    
    /**
     * Grant member perks when a new user registers
     */
    public static function grant_new_member_perks( $user_id ) {
        // Set flag that user has member perks
        update_user_meta( $user_id, 'dbc_member_perks_granted', current_time( 'timestamp' ) );
        
        // Set flag to show welcome popup on next page load
        update_user_meta( $user_id, 'dbc_show_welcome_popup', '1' );
        
        // Track discount code eligibility (expires 30 days from now)
        $expiry = strtotime( '+30 days' );
        update_user_meta( $user_id, 'dbc_new10_discount_expires', $expiry );
        update_user_meta( $user_id, 'dbc_new10_discount_used', '0' );
    }
    
    /**
     * Check if user can use the NEW10 discount
     */
    public static function can_use_new10_discount( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        if ( ! $user_id ) {
            return array( 'can_use' => false, 'reason' => 'not_logged_in' );
        }
        
        // Check if already used
        $used = get_user_meta( $user_id, 'dbc_new10_discount_used', true );
        if ( $used === '1' ) {
            return array( 'can_use' => false, 'reason' => 'already_used' );
        }
        
        // Check expiry
        $expiry = get_user_meta( $user_id, 'dbc_new10_discount_expires', true );
        if ( ! $expiry ) {
            // User registered before this feature - give them 30 days from now
            $expiry = strtotime( '+30 days' );
            update_user_meta( $user_id, 'dbc_new10_discount_expires', $expiry );
            update_user_meta( $user_id, 'dbc_new10_discount_used', '0' );
        }
        
        if ( current_time( 'timestamp' ) > $expiry ) {
            return array( 'can_use' => false, 'reason' => 'expired' );
        }
        
        return array( 
            'can_use' => true, 
            'expires' => $expiry,
            'expires_formatted' => date( 'F j, Y', $expiry )
        );
    }
    
    /**
     * Mark NEW10 discount as used for a user
     */
    public static function mark_new10_used( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        if ( $user_id ) {
            update_user_meta( $user_id, 'dbc_new10_discount_used', '1' );
        }
    }
    
    /**
     * Get member content download URL for logged-in users
     */
    public static function get_member_content_download_url( $content_id ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        
        $content = self::get_member_content();
        if ( ! isset( $content[ $content_id ] ) ) {
            return false;
        }
        
        $user = wp_get_current_user();
        $token = wp_hash( $user->user_email . $content_id . 'member_content' );
        
        // Store token for verification (valid for 24 hours)
        set_transient( 'dbc_member_download_' . $token, array(
            'user_id'    => $user->ID,
            'content_id' => $content_id,
            'file'       => $content[ $content_id ]['file'],
        ), DAY_IN_SECONDS );
        
        return add_query_arg( array(
            'dbc_member_download' => $token,
        ), home_url( '/' ) );
    }
    
    /**
     * AJAX handler for member content download
     */
    public static function ajax_download_member_content() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'You must be logged in to download member content.' ) );
        }
        
        $content_id = sanitize_text_field( $_POST['content_id'] ?? '' );
        $download_url = self::get_member_content_download_url( $content_id );
        
        if ( ! $download_url ) {
            wp_send_json_error( array( 'message' => 'Invalid content or you do not have access.' ) );
        }
        
        wp_send_json_success( array( 'download_url' => $download_url ) );
    }
    
    /**
     * Handle member content file download
     */
    public static function handle_member_content_download() {
        if ( ! isset( $_GET['dbc_member_download'] ) ) {
            return;
        }
        
        $token = sanitize_text_field( $_GET['dbc_member_download'] );
        $data = get_transient( 'dbc_member_download_' . $token );
        
        if ( ! $data || empty( $data['file'] ) ) {
            wp_die( 'This download link has expired or is invalid. Please try again from your account page.', 'Download Expired', array( 'response' => 403 ) );
        }
        
        // Verify user is logged in and matches the token
        if ( ! is_user_logged_in() || get_current_user_id() != $data['user_id'] ) {
            wp_die( 'You must be logged in to download this content.', 'Access Denied', array( 'response' => 403 ) );
        }
        
        // Build file path (member content directory)
        $file_path = get_template_directory() . '/assets/member-content/' . $data['file'];
        
        if ( ! file_exists( $file_path ) ) {
            wp_die( 'File not found. Please contact us for assistance.', 'File Not Found', array( 'response' => 404 ) );
        }
        
        // Track download
        $user_id = get_current_user_id();
        $downloaded = get_user_meta( $user_id, 'dbc_downloaded_member_content', true );
        if ( ! is_array( $downloaded ) ) $downloaded = array();
        if ( ! in_array( $data['content_id'], $downloaded ) ) {
            $downloaded[] = $data['content_id'];
            update_user_meta( $user_id, 'dbc_downloaded_member_content', $downloaded );
        }
        
        // Serve the file
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="' . basename( $data['file'] ) . '"' );
        header( 'Content-Length: ' . filesize( $file_path ) );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        readfile( $file_path );
        exit;
    }
    
    /**
     * Add settings page
     */
    public static function add_settings_page() {
        add_options_page(
            'Newsletter Settings',
            'Newsletter',
            'manage_options',
            'dbc-newsletter',
            array( __CLASS__, 'render_settings_page' )
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting( 'dbc_newsletter_settings', 'dbc_mailchimp_api_key' );
        register_setting( 'dbc_newsletter_settings', 'dbc_mailchimp_list_id' );
        register_setting( 'dbc_newsletter_settings', 'dbc_mailchimp_server_prefix' );
    }
    
    /**
     * Test Mailchimp connection
     */
    public static function test_mailchimp_connection() {
        $api_key = get_option( 'dbc_mailchimp_api_key' );
        $list_id = get_option( 'dbc_mailchimp_list_id' );
        $server = get_option( 'dbc_mailchimp_server_prefix' );
        
        if ( empty( $api_key ) || empty( $list_id ) || empty( $server ) ) {
            return array( 'status' => 'error', 'message' => 'Mailchimp not fully configured' );
        }
        
        // Test by getting list info
        $url = "https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}";
        
        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
            ),
            'timeout' => 15,
        ) );
        
        if ( is_wp_error( $response ) ) {
            return array( 'status' => 'error', 'message' => $response->get_error_message() );
        }
        
        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( $code === 200 ) {
            return array(
                'status' => 'success',
                'list_name' => $body['name'] ?? 'Unknown',
                'member_count' => $body['stats']['member_count'] ?? 0,
            );
        }
        
        return array(
            'status' => 'error',
            'code' => $code,
            'message' => $body['detail'] ?? 'Unknown error',
        );
    }
    
    /**
     * Test adding a subscriber to Mailchimp
     */
    public static function test_add_subscriber( $test_email ) {
        $api_key = get_option( 'dbc_mailchimp_api_key' );
        $list_id = get_option( 'dbc_mailchimp_list_id' );
        $server = get_option( 'dbc_mailchimp_server_prefix' );
        
        if ( empty( $api_key ) || empty( $list_id ) || empty( $server ) ) {
            return array( 'status' => 'error', 'message' => 'Mailchimp not fully configured' );
        }
        
        $url = "https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}/members";
        
        $data = array(
            'email_address' => $test_email,
            'status'        => 'subscribed',
            'tags'          => array( 'Test Signup' ),
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
            return array( 'status' => 'error', 'message' => $response->get_error_message() );
        }
        
        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        return array(
            'status' => ( $code >= 200 && $code < 300 ) ? 'success' : 'error',
            'code' => $code,
            'response' => $body,
        );
    }
    
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Test connection if requested
        $test_result = null;
        if ( isset( $_GET['test_mailchimp'] ) && $_GET['test_mailchimp'] === '1' ) {
            $test_result = self::test_mailchimp_connection();
        }
        
        // Test adding a subscriber if requested
        $add_result = null;
        if ( isset( $_GET['test_add'] ) && ! empty( $_GET['test_email'] ) ) {
            $add_result = self::test_add_subscriber( sanitize_email( $_GET['test_email'] ) );
        }
        ?>
        <div class="wrap">
            <h1>Newsletter Settings (Mailchimp)</h1>
            
            <?php if ( $test_result ) : ?>
                <div class="notice <?php echo $test_result['status'] === 'success' ? 'notice-success' : 'notice-error'; ?>">
                    <p>
                        <?php if ( $test_result['status'] === 'success' ) : ?>
                            <strong>✅ Connection Successful!</strong><br>
                            List Name: <?php echo esc_html( $test_result['list_name'] ); ?><br>
                            Member Count: <?php echo esc_html( $test_result['member_count'] ); ?>
                        <?php else : ?>
                            <strong>❌ Connection Failed!</strong><br>
                            <?php if ( isset( $test_result['code'] ) ) : ?>
                                Error Code: <?php echo esc_html( $test_result['code'] ); ?><br>
                            <?php endif; ?>
                            Message: <?php echo esc_html( $test_result['message'] ); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <p>
                <a href="<?php echo admin_url( 'options-general.php?page=dbc-newsletter&test_mailchimp=1' ); ?>" class="button">Test Mailchimp Connection</a>
            </p>
            
            <h3>Test Add Subscriber</h3>
            <form method="get" action="">
                <input type="hidden" name="page" value="dbc-newsletter">
                <input type="hidden" name="test_add" value="1">
                <input type="email" name="test_email" placeholder="test@example.com" required style="width: 250px;">
                <button type="submit" class="button">Test Add to Mailchimp</button>
            </form>
            
            <?php if ( $add_result ) : ?>
                <div class="notice <?php echo $add_result['status'] === 'success' ? 'notice-success' : 'notice-error'; ?>" style="margin-top: 10px;">
                    <p>
                        <strong><?php echo $add_result['status'] === 'success' ? '✅ Success' : '❌ Failed'; ?></strong><br>
                        HTTP Code: <?php echo esc_html( $add_result['code'] ); ?><br>
                        Response: <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;"><?php echo esc_html( json_encode( $add_result['response'], JSON_PRETTY_PRINT ) ); ?></pre>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'dbc_newsletter_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="dbc_mailchimp_api_key">Mailchimp API Key</label></th>
                        <td>
                            <input type="password" id="dbc_mailchimp_api_key" name="dbc_mailchimp_api_key" 
                                   value="<?php echo esc_attr( get_option( 'dbc_mailchimp_api_key' ) ); ?>" class="regular-text">
                            <p class="description">Get this from Mailchimp → Account → Extras → API Keys</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="dbc_mailchimp_server_prefix">Server Prefix</label></th>
                        <td>
                            <input type="text" id="dbc_mailchimp_server_prefix" name="dbc_mailchimp_server_prefix" 
                                   value="<?php echo esc_attr( get_option( 'dbc_mailchimp_server_prefix' ) ); ?>" class="regular-text" placeholder="e.g., us21">
                            <p class="description">The part after the dash in your API key (e.g., if key ends in -us21, enter us21)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="dbc_mailchimp_list_id">Audience/List ID</label></th>
                        <td>
                            <input type="text" id="dbc_mailchimp_list_id" name="dbc_mailchimp_list_id" 
                                   value="<?php echo esc_attr( get_option( 'dbc_mailchimp_list_id' ) ); ?>" class="regular-text">
                            <p class="description">Mailchimp → Audience → Settings → Audience name and defaults → Audience ID</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'Save Settings' ); ?>
            </form>
            
            <hr>
            <h2>Subscribers</h2>
            <?php self::render_subscribers_list(); ?>
        </div>
        <?php
    }
    
    /**
     * Render subscribers list
     */
    public static function render_subscribers_list() {
        $subscribers = get_option( 'dbc_newsletter_subscribers', array() );
        
        if ( empty( $subscribers ) ) {
            echo '<p>No subscribers yet.</p>';
            return;
        }
        
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Email</th><th>First Name</th><th>Last Name</th><th>Book</th><th>Date</th></tr></thead><tbody>';
        
        $subscribers = array_reverse( $subscribers ); // Most recent first
        foreach ( array_slice( $subscribers, 0, 50 ) as $sub ) {
            echo '<tr>';
            echo '<td>' . esc_html( $sub['email'] ) . '</td>';
            // Support both old format (name) and new format (first_name, last_name)
            $first = $sub['first_name'] ?? ( $sub['name'] ?? '' );
            $last = $sub['last_name'] ?? '';
            echo '<td>' . esc_html( $first ) . '</td>';
            echo '<td>' . esc_html( $last ) . '</td>';
            echo '<td>' . esc_html( $sub['book'] ?? '' ) . '</td>';
            echo '<td>' . esc_html( $sub['date'] ?? '' ) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '<p class="description">Showing latest 50 subscribers. Total: ' . count( get_option( 'dbc_newsletter_subscribers', array() ) ) . '</p>';
    }
    
    /**
     * Check if user can download directly (logged in or already subscribed)
     */
    public static function check_download_access() {
        $book_id = intval( $_POST['book_id'] ?? 0 );
        
        if ( ! $book_id ) {
            wp_send_json_error( array( 'has_access' => false ) );
        }
        
        $free_chapter = get_post_meta( $book_id, '_dbc_book_free_chapter', true );
        if ( ! $free_chapter ) {
            wp_send_json_error( array( 'has_access' => false, 'message' => 'No free chapter available.' ) );
        }
        
        // Check if user is logged in
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $token = self::generate_download_token( $user->user_email, $book_id );
            
            set_transient( 'dbc_download_' . $token, array(
                'email'   => $user->user_email,
                'book_id' => $book_id,
                'file'    => $free_chapter,
            ), DAY_IN_SECONDS );
            
            $download_url = add_query_arg( array(
                'dbc_download' => $token,
            ), home_url( '/' ) );
            
            wp_send_json_success( array(
                'has_access'   => true,
                'download_url' => $download_url,
                'reason'       => 'logged_in',
            ) );
        }
        
        // Check cookies for previous subscription
        if ( isset( $_COOKIE['dbc_subscribed'] ) && $_COOKIE['dbc_subscribed'] === 'yes' ) {
            $email = isset( $_COOKIE['dbc_subscriber_email'] ) ? sanitize_email( $_COOKIE['dbc_subscriber_email'] ) : '';
            
            if ( $email && self::is_subscribed( $email ) ) {
                $token = self::generate_download_token( $email, $book_id );
                
                set_transient( 'dbc_download_' . $token, array(
                    'email'   => $email,
                    'book_id' => $book_id,
                    'file'    => $free_chapter,
                ), DAY_IN_SECONDS );
                
                $download_url = add_query_arg( array(
                    'dbc_download' => $token,
                ), home_url( '/' ) );
                
                wp_send_json_success( array(
                    'has_access'   => true,
                    'download_url' => $download_url,
                    'reason'       => 'subscribed',
                ) );
            }
        }
        
        wp_send_json_success( array( 'has_access' => false ) );
    }
    
    /**
     * Handle newsletter subscription via AJAX
     */
    public static function handle_subscribe() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'dbc_newsletter_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
        }
        
        $email = sanitize_email( $_POST['email'] ?? '' );
        $first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
        $last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
        $book_id = intval( $_POST['book_id'] ?? 0 );
        
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        }
        
        // Get book info
        $book_title = $book_id ? get_the_title( $book_id ) : '';
        $free_chapter = $book_id ? get_post_meta( $book_id, '_dbc_book_free_chapter', true ) : '';
        
        if ( ! $free_chapter ) {
            wp_send_json_error( array( 'message' => 'No free chapter available for this book.' ) );
        }
        
        // Subscribe to Mailchimp
        $mailchimp_result = self::subscribe_to_mailchimp( $email, $first_name, $last_name );
        
        // Store subscriber locally (regardless of Mailchimp result for tracking)
        $subscribers = get_option( 'dbc_newsletter_subscribers', array() );
        $subscribers[] = array(
            'email'      => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'book'       => $book_title,
            'date'       => current_time( 'mysql' ),
        );
        update_option( 'dbc_newsletter_subscribers', $subscribers );
        
        // Set cookies to remember subscription (30 days)
        setcookie( 'dbc_subscribed', 'yes', time() + ( 30 * DAY_IN_SECONDS ), '/' );
        setcookie( 'dbc_subscriber_email', $email, time() + ( 30 * DAY_IN_SECONDS ), '/' );
        
        // If user exists, update their meta
        $user = get_user_by( 'email', $email );
        if ( $user ) {
            update_user_meta( $user->ID, 'dbc_newsletter_subscribed', '1' );
            
            // Track this download
            $downloaded = get_user_meta( $user->ID, 'dbc_downloaded_chapters', true );
            if ( ! is_array( $downloaded ) ) $downloaded = array();
            if ( ! in_array( $book_id, $downloaded ) ) {
                $downloaded[] = $book_id;
                update_user_meta( $user->ID, 'dbc_downloaded_chapters', $downloaded );
            }
        }
        
        // Generate download token
        $token = self::generate_download_token( $email, $book_id );
        
        // Store token temporarily (expires in 24 hours)
        set_transient( 'dbc_download_' . $token, array(
            'email'   => $email,
            'book_id' => $book_id,
            'file'    => $free_chapter,
        ), DAY_IN_SECONDS );
        
        // Build download URL
        $download_url = add_query_arg( array(
            'dbc_download' => $token,
        ), home_url( '/' ) );
        
        // Set cookie to trigger welcome popup on next page (only if this is their first subscription)
        if ( ! isset( $_COOKIE['dbc_welcomed'] ) ) {
            setcookie( 'dbc_show_newsletter_welcome', '1', time() + 60, '/' ); // Short-lived cookie
            setcookie( 'dbc_welcomed', '1', time() + ( 365 * DAY_IN_SECONDS ), '/' ); // Remember we've welcomed them
        }
        
        wp_send_json_success( array(
            'message'      => 'Thank you for subscribing!',
            'download_url' => $download_url,
            'book_title'   => $book_title,
            'show_welcome' => ! isset( $_COOKIE['dbc_welcomed'] ),
        ) );
    }
    
    /**
     * Handle simple footer newsletter subscription via AJAX
     */
    public static function handle_footer_subscribe() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'dbc_newsletter_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
        }
        
        $email = sanitize_email( $_POST['email'] ?? '' );
        
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        }
        
        // Subscribe to Mailchimp with footer-specific tag
        $api_key = get_option( 'dbc_mailchimp_api_key' );
        $list_id = get_option( 'dbc_mailchimp_list_id' );
        $server = get_option( 'dbc_mailchimp_server_prefix' );
        
        if ( ! empty( $api_key ) && ! empty( $list_id ) && ! empty( $server ) ) {
            $url = "https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}/members";
            
            $data = array(
                'email_address' => $email,
                'status'        => 'subscribed',
                'tags'          => array( 'Footer Signup', 'Website Signup' ),
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
                error_log( 'Mailchimp footer subscription error: ' . $response->get_error_message() );
            } else {
                $code = wp_remote_retrieve_response_code( $response );
                $body = wp_remote_retrieve_body( $response );
                error_log( 'Mailchimp footer response code: ' . $code );
                error_log( 'Mailchimp footer response body: ' . $body );
            }
        }
        
        // Store subscriber locally
        $subscribers = get_option( 'dbc_newsletter_subscribers', array() );
        $subscribers[] = array(
            'email'      => $email,
            'first_name' => '',
            'last_name'  => '',
            'book'       => 'Footer Signup',
            'date'       => current_time( 'mysql' ),
        );
        update_option( 'dbc_newsletter_subscribers', $subscribers );
        
        // Set cookies
        setcookie( 'dbc_subscribed', 'yes', time() + ( 30 * DAY_IN_SECONDS ), '/' );
        setcookie( 'dbc_subscriber_email', $email, time() + ( 30 * DAY_IN_SECONDS ), '/' );
        
        // Set cookie to trigger welcome popup on next page (only if this is their first subscription)
        $show_welcome = false;
        if ( ! isset( $_COOKIE['dbc_welcomed'] ) ) {
            setcookie( 'dbc_show_newsletter_welcome', '1', time() + 60, '/' );
            setcookie( 'dbc_welcomed', '1', time() + ( 365 * DAY_IN_SECONDS ), '/' );
            $show_welcome = true;
        }
        
        wp_send_json_success( array( 
            'message' => 'Thank you for subscribing!',
            'show_welcome' => $show_welcome,
        ) );
    }
    
    /**
     * Subscribe email to Mailchimp
     */
    public static function subscribe_to_mailchimp( $email, $first_name = '', $last_name = '' ) {
        $api_key = get_option( 'dbc_mailchimp_api_key' );
        $list_id = get_option( 'dbc_mailchimp_list_id' );
        $server = get_option( 'dbc_mailchimp_server_prefix' );
        
        if ( empty( $api_key ) || empty( $list_id ) || empty( $server ) ) {
            // Mailchimp not configured, but still allow downloads
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
            'tags' => array( 'Free Chapter Download', 'Website Signup' ),
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
            return array( 'status' => 'error', 'message' => $response->get_error_message() );
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $code = wp_remote_retrieve_response_code( $response );
        
        // 200 = success, 400 with "Member Exists" = already subscribed (OK)
        if ( $code === 200 || ( $code === 400 && strpos( $body['title'] ?? '', 'Member Exists' ) !== false ) ) {
            return array( 'status' => 'success' );
        }
        
        return array( 'status' => 'error', 'message' => $body['detail'] ?? 'Unknown error' );
    }
    
    /**
     * Generate download token
     */
    public static function generate_download_token( $email, $book_id ) {
        return wp_hash( $email . $book_id . time() . wp_rand() );
    }
    
    /**
     * Handle file download
     */
    public static function handle_download() {
        if ( ! isset( $_GET['dbc_download'] ) ) {
            return;
        }
        
        $token = sanitize_text_field( $_GET['dbc_download'] );
        $data = get_transient( 'dbc_download_' . $token );
        
        if ( ! $data || empty( $data['file'] ) ) {
            wp_die( 'This download link has expired or is invalid. Please sign up again to get a new link.', 'Download Expired', array( 'response' => 403 ) );
        }
        
        // Build file path
        $file_path = get_template_directory() . '/assets/free chapters/' . $data['file'];
        
        if ( ! file_exists( $file_path ) ) {
            wp_die( 'File not found. Please contact us for assistance.', 'File Not Found', array( 'response' => 404 ) );
        }
        
        // Delete token after use (one-time download) - optional, comment out to allow multiple downloads
        // delete_transient( 'dbc_download_' . $token );
        
        // Serve the file
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="' . basename( $data['file'] ) . '"' );
        header( 'Content-Length: ' . filesize( $file_path ) );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        readfile( $file_path );
        exit;
    }
    
    /**
     * Check if email is already subscribed
     */
    public static function is_subscribed( $email ) {
        $subscribers = get_option( 'dbc_newsletter_subscribers', array() );
        foreach ( $subscribers as $sub ) {
            if ( strtolower( $sub['email'] ) === strtolower( $email ) ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Sync subscription status with Mailchimp (subscribe or unsubscribe)
     */
    public static function sync_mailchimp_subscription( $email, $first_name = '', $last_name = '', $subscribe = true ) {
        $api_key = get_option( 'dbc_mailchimp_api_key' );
        $list_id = get_option( 'dbc_mailchimp_list_id' );
        $server = get_option( 'dbc_mailchimp_server_prefix' );
        
        if ( empty( $api_key ) || empty( $list_id ) || empty( $server ) ) {
            return array( 'status' => 'skipped', 'message' => 'Mailchimp not configured' );
        }
        
        // Use member hash for update/unsubscribe
        $member_hash = md5( strtolower( $email ) );
        $url = "https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$member_hash}";
        
        if ( $subscribe ) {
            // Subscribe or update existing member
            $data = array(
                'email_address' => $email,
                'status_if_new' => 'subscribed',
                'status'        => 'subscribed',
                'merge_fields'  => array(
                    'FNAME' => $first_name,
                    'LNAME' => $last_name,
                ),
                'tags' => array( 'Account Settings Signup' ),
            );
            
            $response = wp_remote_request( $url, array(
                'method'  => 'PUT',
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
                    'Content-Type'  => 'application/json',
                ),
                'body'    => json_encode( $data ),
                'timeout' => 15,
            ) );
        } else {
            // Unsubscribe - set status to unsubscribed
            $data = array(
                'status' => 'unsubscribed',
            );
            
            $response = wp_remote_request( $url, array(
                'method'  => 'PATCH',
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
                    'Content-Type'  => 'application/json',
                ),
                'body'    => json_encode( $data ),
                'timeout' => 15,
            ) );
        }
        
        if ( is_wp_error( $response ) ) {
            error_log( 'Mailchimp sync error for ' . $email . ': ' . $response->get_error_message() );
            return array( 'status' => 'error', 'message' => $response->get_error_message() );
        }
        
        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( $code >= 200 && $code < 300 ) {
            return array( 'status' => 'success' );
        }
        
        // 404 when unsubscribing means they weren't subscribed anyway - that's fine
        if ( ! $subscribe && $code === 404 ) {
            return array( 'status' => 'success', 'message' => 'Not in list' );
        }
        
        error_log( 'Mailchimp sync failed for ' . $email . ': ' . ( $body['detail'] ?? 'Unknown error' ) );
        return array( 'status' => 'error', 'message' => $body['detail'] ?? 'Unknown error' );
    }
}

/**
 * Global helper function for syncing Mailchimp subscription
 * Can be called from theme files
 */
function dbc_sync_mailchimp_subscription( $email, $first_name = '', $last_name = '', $subscribe = true ) {
    return DBC_Newsletter::sync_mailchimp_subscription( $email, $first_name, $last_name, $subscribe );
}

