<?php
/**
 * Plugin Name: Drew Bankston Analytics
 * Description: Google Analytics 4 and Google Tag Manager integration for Drew Bankston author website
 * Version: 1.0.0
 * Author: Drew Bankston
 * Text Domain: dba
 */

defined( 'ABSPATH' ) || exit;

define( 'DBA_VERSION', '1.0.0' );
define( 'DBA_PATH', plugin_dir_path( __FILE__ ) );
define( 'DBA_URL', plugin_dir_url( __FILE__ ) );

// Include class files
require_once DBA_PATH . 'includes/class-settings.php';
require_once DBA_PATH . 'includes/class-tracking.php';
require_once DBA_PATH . 'includes/class-dashboard-widget.php';

/**
 * Initialize the plugin
 */
function dba_init() {
    DBA_Settings::init();
    DBA_Tracking::init();
    DBA_Dashboard_Widget::init();
}
add_action( 'plugins_loaded', 'dba_init' );

/**
 * Add settings link on plugin page
 */
function dba_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=dba-analytics' ) . '">' . __( 'Settings', 'dba' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dba_settings_link' );

/**
 * Activation hook - set default options
 */
function dba_activate() {
    // Set default options if they don't exist
    if ( false === get_option( 'dba_ga4_enabled' ) ) {
        add_option( 'dba_ga4_enabled', '0' );
    }
    if ( false === get_option( 'dba_gtm_enabled' ) ) {
        add_option( 'dba_gtm_enabled', '0' );
    }
    if ( false === get_option( 'dba_exclude_admins' ) ) {
        add_option( 'dba_exclude_admins', '1' );
    }
}
register_activation_hook( __FILE__, 'dba_activate' );
