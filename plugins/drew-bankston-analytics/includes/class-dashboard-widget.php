<?php
/**
 * Analytics Dashboard Widget
 *
 * @package Drew_Bankston_Analytics
 */

defined( 'ABSPATH' ) || exit;

class DBA_Dashboard_Widget {

    /**
     * Initialize the dashboard widget
     */
    public static function init() {
        add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_dashboard_styles' ) );
    }

    /**
     * Register the dashboard widget
     */
    public static function register_dashboard_widget() {
        wp_add_dashboard_widget(
            'dba_analytics_widget',
            '📊 Google Analytics Overview',
            array( __CLASS__, 'render_dashboard_widget' ),
            null,
            null,
            'normal',
            'high'
        );
    }

    /**
     * Enqueue dashboard styles
     */
    public static function enqueue_dashboard_styles( $hook ) {
        if ( 'index.php' !== $hook ) {
            return;
        }

        wp_add_inline_style( 'wp-admin', '
            #dba_analytics_widget .inside { padding: 0 !important; margin: 0 !important; }
            .dba-dashboard-widget { padding: 12px; }
            .dba-status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
            .dba-status-card { background: #f6f7f7; border-radius: 6px; padding: 12px; text-align: center; }
            .dba-status-card.active { background: #edfaef; border: 1px solid #46b450; }
            .dba-status-card.inactive { background: #fef7f1; border: 1px solid #dc3232; }
            .dba-status-card h4 { margin: 0 0 4px 0; font-size: 12px; color: #50575e; text-transform: uppercase; letter-spacing: 0.5px; }
            .dba-status-card .status-value { font-size: 14px; font-weight: 600; color: #1d2327; word-break: break-all; }
            .dba-status-card.active .status-value { color: #2e7d32; }
            .dba-status-card.inactive .status-value { color: #c62828; }
            .dba-quick-links { margin-bottom: 16px; }
            .dba-quick-links h4 { margin: 0 0 8px 0; font-size: 13px; color: #1d2327; }
            .dba-link-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .dba-link-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 8px; background: #f0f6fc; border: 1px solid #c5d9ed; border-radius: 6px; text-decoration: none; color: #2271b1; font-size: 12px; font-weight: 500; transition: all 0.2s; }
            .dba-link-btn:hover { background: #e1ecf5; border-color: #2271b1; color: #135e96; }
            .dba-link-btn .icon { font-size: 20px; margin-bottom: 4px; }
            .dba-info-box { background: #f6f7f7; border-radius: 6px; padding: 12px; margin-bottom: 12px; }
            .dba-info-box h4 { margin: 0 0 8px 0; font-size: 13px; color: #1d2327; }
            .dba-info-box p { margin: 0; font-size: 12px; color: #50575e; line-height: 1.5; }
            .dba-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #dcdcde; }
            .dba-footer a { font-size: 12px; color: #2271b1; text-decoration: none; }
            .dba-footer a:hover { text-decoration: underline; }
            .dba-realtime-hint { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border-radius: 6px; padding: 16px; margin-bottom: 16px; }
            .dba-realtime-hint h4 { margin: 0 0 8px 0; font-size: 14px; color: #fff; }
            .dba-realtime-hint p { margin: 0 0 12px 0; font-size: 12px; opacity: 0.9; }
            .dba-realtime-hint a { display: inline-block; background: rgba(255,255,255,0.2); color: #fff; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 500; }
            .dba-realtime-hint a:hover { background: rgba(255,255,255,0.3); }
        ' );
    }

    /**
     * Render the dashboard widget
     */
    public static function render_dashboard_widget() {
        // Get current settings
        $ga4_enabled  = get_option( 'dba_ga4_enabled', '0' ) === '1';
        $ga4_id       = get_option( 'dba_ga4_measurement_id', '' );
        $gtm_enabled  = get_option( 'dba_gtm_enabled', '0' ) === '1';
        $gtm_id       = get_option( 'dba_gtm_container_id', '' );

        $ga4_active = $ga4_enabled && ! empty( $ga4_id );
        $gtm_active = $gtm_enabled && ! empty( $gtm_id );

        // Build GA4 report URLs
        $ga_base_url = 'https://analytics.google.com/analytics/web/';
        ?>
        <div class="dba-dashboard-widget">
            
            <!-- Status Grid -->
            <div class="dba-status-grid">
                <div class="dba-status-card <?php echo $ga4_active ? 'active' : 'inactive'; ?>">
                    <h4>Google Analytics 4</h4>
                    <div class="status-value">
                        <?php echo $ga4_active ? esc_html( $ga4_id ) : 'Not Configured'; ?>
                    </div>
                </div>
                <div class="dba-status-card <?php echo $gtm_active ? 'active' : 'inactive'; ?>">
                    <h4>Tag Manager</h4>
                    <div class="status-value">
                        <?php echo $gtm_active ? esc_html( $gtm_id ) : 'Not Configured'; ?>
                    </div>
                </div>
            </div>

            <?php if ( $ga4_active ) : ?>
            
            <!-- Realtime Hint -->
            <div class="dba-realtime-hint">
                <h4>📈 View Live Data</h4>
                <p>See who's on your site right now and what they're doing in Google Analytics.</p>
                <a href="<?php echo esc_url( $ga_base_url ); ?>#/p<?php echo esc_attr( self::extract_property_id( $ga4_id ) ); ?>/realtime/overview" target="_blank">
                    Open Realtime Report →
                </a>
            </div>

            <!-- Quick Links -->
            <div class="dba-quick-links">
                <h4>Quick Reports</h4>
                <div class="dba-link-grid">
                    <a href="<?php echo esc_url( $ga_base_url ); ?>" target="_blank" class="dba-link-btn">
                        <span class="icon">📊</span>
                        Overview
                    </a>
                    <a href="<?php echo esc_url( $ga_base_url ); ?>#/reports/reportinghub" target="_blank" class="dba-link-btn">
                        <span class="icon">📈</span>
                        Reports
                    </a>
                    <a href="<?php echo esc_url( $ga_base_url ); ?>#/reports/acquisition-overview" target="_blank" class="dba-link-btn">
                        <span class="icon">🎯</span>
                        Acquisition
                    </a>
                    <a href="<?php echo esc_url( $ga_base_url ); ?>#/reports/engagement-overview" target="_blank" class="dba-link-btn">
                        <span class="icon">💬</span>
                        Engagement
                    </a>
                    <a href="<?php echo esc_url( $ga_base_url ); ?>#/reports/demographic-details" target="_blank" class="dba-link-btn">
                        <span class="icon">👥</span>
                        Demographics
                    </a>
                    <a href="<?php echo esc_url( $ga_base_url ); ?>#/reports/technology-overview" target="_blank" class="dba-link-btn">
                        <span class="icon">💻</span>
                        Technology
                    </a>
                </div>
            </div>

            <?php if ( $gtm_active ) : ?>
            <!-- GTM Quick Links -->
            <div class="dba-quick-links">
                <h4>Tag Manager</h4>
                <div class="dba-link-grid">
                    <a href="https://tagmanager.google.com/" target="_blank" class="dba-link-btn">
                        <span class="icon">🏷️</span>
                        Manage Tags
                    </a>
                    <a href="https://tagmanager.google.com/#/container/accounts" target="_blank" class="dba-link-btn">
                        <span class="icon">📦</span>
                        Containers
                    </a>
                    <a href="https://tagmanager.google.com/" target="_blank" class="dba-link-btn">
                        <span class="icon">🔍</span>
                        Preview Mode
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Info Box -->
            <div class="dba-info-box">
                <h4>💡 Did You Know?</h4>
                <p>
                    <?php echo esc_html( self::get_random_tip() ); ?>
                </p>
            </div>

            <?php else : ?>

            <!-- Not Configured Message -->
            <div class="dba-info-box">
                <h4>⚠️ Analytics Not Configured</h4>
                <p>
                    Set up Google Analytics to start tracking visitors, page views, and conversions on your site.
                    <a href="<?php echo esc_url( admin_url( 'options-general.php?page=dba-analytics' ) ); ?>">Configure now →</a>
                </p>
            </div>

            <?php endif; ?>

            <!-- Footer -->
            <div class="dba-footer">
                <a href="<?php echo esc_url( admin_url( 'options-general.php?page=dba-analytics' ) ); ?>">
                    ⚙️ Settings
                </a>
                <span style="color: #50575e; font-size: 11px;">
                    Drew Bankston Analytics v<?php echo esc_html( DBA_VERSION ); ?>
                </span>
            </div>
        </div>
        <?php
    }

    /**
     * Extract property ID from measurement ID (for URL building)
     * Note: This is a placeholder - the actual property ID is different from measurement ID
     * Users will need to find their property ID in GA4 admin
     */
    private static function extract_property_id( $measurement_id ) {
        // The measurement ID (G-XXXXXXX) is not the same as the property ID
        // Return empty for now - users will be redirected to GA home
        return '';
    }

    /**
     * Get a random analytics tip
     */
    private static function get_random_tip() {
        $tips = array(
            'GA4 data can take up to 48 hours to fully process. Realtime reports show instant data!',
            'Use UTM parameters in your links to track which marketing campaigns drive the most traffic.',
            'The Engagement report shows which pages keep visitors interested the longest.',
            'Set up conversion events in GA4 to track book purchases and newsletter signups.',
            'The Acquisition report reveals where your visitors come from—search, social, or direct.',
            'Demographics data helps you understand your reader audience better.',
            'Check the Pages and Screens report to see which book pages are most popular.',
            'Use GA4 Explorations for custom reports tailored to your author business.',
            'The Realtime report is perfect for checking if a new marketing campaign is working.',
            'Connect Google Search Console to see which keywords bring readers to your site.',
        );

        return $tips[ array_rand( $tips ) ];
    }
}
