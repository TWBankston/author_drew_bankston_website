<?php
/**
 * Newsletter Integration - Mailchimp + Free Chapter Downloads
 */

defined( 'ABSPATH' ) || exit;

class DBC_Newsletter {
    
    public static function init() {
        // AJAX handlers
        add_action( 'wp_ajax_dbc_newsletter_subscribe', array( __CLASS__, 'handle_subscribe' ) );
        add_action( 'wp_ajax_nopriv_dbc_newsletter_subscribe', array( __CLASS__, 'handle_subscribe' ) );
        add_action( 'wp_ajax_dbc_check_download_access', array( __CLASS__, 'check_download_access' ) );
        add_action( 'wp_ajax_nopriv_dbc_check_download_access', array( __CLASS__, 'check_download_access' ) );
        
        // Download handler
        add_action( 'init', array( __CLASS__, 'handle_download' ) );
        
        // Admin settings
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
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
     * Render settings page
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Newsletter Settings (Mailchimp)</h1>
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
        
        wp_send_json_success( array(
            'message'      => 'Thank you for subscribing!',
            'download_url' => $download_url,
            'book_title'   => $book_title,
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
}

