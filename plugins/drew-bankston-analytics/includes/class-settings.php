<?php
/**
 * Analytics Settings Page
 *
 * @package Drew_Bankston_Analytics
 */

defined( 'ABSPATH' ) || exit;

class DBA_Settings {

    /**
     * Initialize the settings
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles' ) );
    }

    /**
     * Add settings page to admin menu
     */
    public static function add_settings_page() {
        add_options_page(
            __( 'Analytics Settings', 'dba' ),
            __( 'Analytics', 'dba' ),
            'manage_options',
            'dba-analytics',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings
     */
    public static function register_settings() {
        // Google Analytics 4 Section
        add_settings_section(
            'dba_ga4_section',
            __( 'Google Analytics 4 (GA4)', 'dba' ),
            array( __CLASS__, 'ga4_section_callback' ),
            'dba-analytics'
        );

        register_setting( 'dba_analytics', 'dba_ga4_enabled', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0',
        ) );

        register_setting( 'dba_analytics', 'dba_ga4_measurement_id', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );

        add_settings_field(
            'dba_ga4_enabled',
            __( 'Enable GA4', 'dba' ),
            array( __CLASS__, 'checkbox_field_callback' ),
            'dba-analytics',
            'dba_ga4_section',
            array(
                'id'          => 'dba_ga4_enabled',
                'description' => __( 'Enable Google Analytics 4 tracking on the frontend', 'dba' ),
            )
        );

        add_settings_field(
            'dba_ga4_measurement_id',
            __( 'GA4 Measurement ID', 'dba' ),
            array( __CLASS__, 'text_field_callback' ),
            'dba-analytics',
            'dba_ga4_section',
            array(
                'id'          => 'dba_ga4_measurement_id',
                'placeholder' => 'G-XXXXXXXXXX',
                'description' => __( 'Your GA4 Measurement ID (starts with G-)', 'dba' ),
            )
        );

        // Google Tag Manager Section
        add_settings_section(
            'dba_gtm_section',
            __( 'Google Tag Manager (GTM)', 'dba' ),
            array( __CLASS__, 'gtm_section_callback' ),
            'dba-analytics'
        );

        register_setting( 'dba_analytics', 'dba_gtm_enabled', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0',
        ) );

        register_setting( 'dba_analytics', 'dba_gtm_container_id', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );

        add_settings_field(
            'dba_gtm_enabled',
            __( 'Enable GTM', 'dba' ),
            array( __CLASS__, 'checkbox_field_callback' ),
            'dba-analytics',
            'dba_gtm_section',
            array(
                'id'          => 'dba_gtm_enabled',
                'description' => __( 'Enable Google Tag Manager on the frontend', 'dba' ),
            )
        );

        add_settings_field(
            'dba_gtm_container_id',
            __( 'GTM Container ID', 'dba' ),
            array( __CLASS__, 'text_field_callback' ),
            'dba-analytics',
            'dba_gtm_section',
            array(
                'id'          => 'dba_gtm_container_id',
                'placeholder' => 'GTM-XXXXXXX',
                'description' => __( 'Your GTM Container ID (starts with GTM-)', 'dba' ),
            )
        );

        // General Settings Section
        add_settings_section(
            'dba_general_section',
            __( 'General Settings', 'dba' ),
            array( __CLASS__, 'general_section_callback' ),
            'dba-analytics'
        );

        register_setting( 'dba_analytics', 'dba_exclude_admins', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '1',
        ) );

        add_settings_field(
            'dba_exclude_admins',
            __( 'Exclude Administrators', 'dba' ),
            array( __CLASS__, 'checkbox_field_callback' ),
            'dba-analytics',
            'dba_general_section',
            array(
                'id'          => 'dba_exclude_admins',
                'description' => __( 'Do not track logged-in administrators (recommended to avoid skewing your data)', 'dba' ),
            )
        );
    }

    /**
     * GA4 section description
     */
    public static function ga4_section_callback() {
        echo '<p class="description">' . esc_html__( 'Configure Google Analytics 4 to track visitor behavior, page views, and conversions.', 'dba' ) . '</p>';
    }

    /**
     * GTM section description
     */
    public static function gtm_section_callback() {
        echo '<p class="description">' . esc_html__( 'Configure Google Tag Manager to manage all your marketing and analytics tags from one place.', 'dba' ) . '</p>';
        echo '<p class="description"><strong>' . esc_html__( 'Note:', 'dba' ) . '</strong> ' . esc_html__( 'If using GTM, you can manage GA4 through GTM instead of enabling it separately above. However, enabling both works fine—GTM will use any existing gtag.', 'dba' ) . '</p>';
    }

    /**
     * General section description
     */
    public static function general_section_callback() {
        echo '<p class="description">' . esc_html__( 'General tracking configuration options.', 'dba' ) . '</p>';
    }

    /**
     * Text field callback
     */
    public static function text_field_callback( $args ) {
        $value = get_option( $args['id'], '' );
        printf(
            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s" />',
            esc_attr( $args['id'] ),
            esc_attr( $value ),
            esc_attr( $args['placeholder'] ?? '' )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    /**
     * Checkbox field callback
     */
    public static function checkbox_field_callback( $args ) {
        $value = get_option( $args['id'], '0' );
        printf(
            '<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />',
            esc_attr( $args['id'] ),
            checked( $value, '1', false )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<label for="%s"> %s</label>', esc_attr( $args['id'] ), esc_html( $args['description'] ) );
        }
    }

    /**
     * Enqueue admin styles
     */
    public static function enqueue_admin_styles( $hook ) {
        if ( 'settings_page_dba-analytics' !== $hook ) {
            return;
        }

        wp_add_inline_style( 'wp-admin', '
            .dba-settings-wrap .form-table th { width: 200px; }
            .dba-settings-wrap h2 { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccd0d4; }
            .dba-settings-wrap h2:first-of-type { margin-top: 0; border-top: none; padding-top: 0; }
            .dba-status-box { background: #fff; border: 1px solid #ccd0d4; border-left-width: 4px; padding: 12px; margin: 20px 0; }
            .dba-status-box.active { border-left-color: #46b450; }
            .dba-status-box.inactive { border-left-color: #dc3232; }
            .dba-status-box.warning { border-left-color: #ffb900; }
            .dba-status-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
            .dba-status-indicator.active { background: #46b450; }
            .dba-status-indicator.inactive { background: #dc3232; }
            .dba-help-box { background: #f0f6fc; border: 1px solid #c5d9ed; border-radius: 4px; padding: 15px; margin: 20px 0; }
            .dba-help-box h4 { margin: 0 0 10px 0; color: #1e3a5f; }
            .dba-help-box ol { margin: 0; padding-left: 20px; }
            .dba-help-box li { margin-bottom: 8px; }
            .dba-help-box code { background: #fff; padding: 2px 6px; border-radius: 3px; }
        ' );
    }

    /**
     * Render the settings page
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get current values
        $ga4_enabled     = get_option( 'dba_ga4_enabled', '0' );
        $ga4_id          = get_option( 'dba_ga4_measurement_id', '' );
        $gtm_enabled     = get_option( 'dba_gtm_enabled', '0' );
        $gtm_id          = get_option( 'dba_gtm_container_id', '' );
        $exclude_admins  = get_option( 'dba_exclude_admins', '1' );

        // Determine status
        $ga4_active = ( '1' === $ga4_enabled && ! empty( $ga4_id ) );
        $gtm_active = ( '1' === $gtm_enabled && ! empty( $gtm_id ) );
        ?>
        <div class="wrap dba-settings-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <!-- Status Overview -->
            <div class="dba-status-box <?php echo ( $ga4_active || $gtm_active ) ? 'active' : 'inactive'; ?>">
                <h3 style="margin: 0 0 10px 0;">📊 Tracking Status</h3>
                <p style="margin: 5px 0;">
                    <span class="dba-status-indicator <?php echo $ga4_active ? 'active' : 'inactive'; ?>"></span>
                    <strong>Google Analytics 4:</strong> 
                    <?php echo $ga4_active ? esc_html( $ga4_id ) : 'Not configured'; ?>
                </p>
                <p style="margin: 5px 0;">
                    <span class="dba-status-indicator <?php echo $gtm_active ? 'active' : 'inactive'; ?>"></span>
                    <strong>Google Tag Manager:</strong> 
                    <?php echo $gtm_active ? esc_html( $gtm_id ) : 'Not configured'; ?>
                </p>
                <?php if ( '1' === $exclude_admins && current_user_can( 'manage_options' ) ) : ?>
                    <p style="margin: 10px 0 0 0; color: #826200;">
                        ⚠️ <em>Tracking is disabled for your admin account (recommended)</em>
                    </p>
                <?php endif; ?>
            </div>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'dba_analytics' );
                do_settings_sections( 'dba-analytics' );
                submit_button( __( 'Save Settings', 'dba' ) );
                ?>
            </form>

            <!-- Setup Instructions -->
            <div class="dba-help-box">
                <h4>📋 Setup Instructions</h4>
                
                <h5 style="margin: 15px 0 8px 0;">Google Analytics 4 (GA4)</h5>
                <ol>
                    <li>Go to <a href="https://analytics.google.com/" target="_blank">Google Analytics</a></li>
                    <li>Click <strong>Admin</strong> (gear icon) in the bottom left</li>
                    <li>In the Property column, click <strong>Data Streams</strong></li>
                    <li>Click your web stream (or create one if needed)</li>
                    <li>Copy the <strong>Measurement ID</strong> (format: <code>G-XXXXXXXXXX</code>)</li>
                    <li>Paste it above and enable GA4</li>
                </ol>

                <h5 style="margin: 15px 0 8px 0;">Google Tag Manager (GTM)</h5>
                <ol>
                    <li>Go to <a href="https://tagmanager.google.com/" target="_blank">Google Tag Manager</a></li>
                    <li>Create an account or select your existing account</li>
                    <li>Create a new container (or use existing) with target platform "Web"</li>
                    <li>Copy the <strong>Container ID</strong> (format: <code>GTM-XXXXXXX</code>)</li>
                    <li>Paste it above and enable GTM</li>
                </ol>

                <h5 style="margin: 15px 0 8px 0;">Which Should I Use?</h5>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Just GA4:</strong> Simple setup for basic analytics (page views, traffic sources, user behavior)</li>
                    <li><strong>Just GTM:</strong> Use if you want to manage GA4 and other tags (Facebook Pixel, etc.) through Tag Manager</li>
                    <li><strong>Both:</strong> Works fine together—GTM will use the existing GA4 gtag</li>
                </ul>
            </div>

            <!-- Verification Instructions -->
            <div class="dba-help-box">
                <h4>✅ Verify Tracking Is Working</h4>
                <ol>
                    <li>Temporarily uncheck "Exclude Administrators" and save</li>
                    <li>Open your website in a new tab</li>
                    <li><strong>For GA4:</strong> Go to Analytics → Reports → Realtime to see your visit</li>
                    <li><strong>For GTM:</strong> Use the Preview mode in Tag Manager to verify tags fire</li>
                    <li>Re-enable "Exclude Administrators" when done testing</li>
                </ol>
            </div>
        </div>
        <?php
    }
}
