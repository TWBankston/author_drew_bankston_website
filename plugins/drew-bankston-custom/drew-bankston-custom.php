<?php
/**
 * Plugin Name: Drew Bankston Custom
 * Description: Custom post types, taxonomies, and functionality for Drew Bankston author website
 * Version: 1.0.0
 * Author: Drew Bankston
 * Text Domain: dbc
 */

defined( 'ABSPATH' ) || exit;

define( 'DBC_VERSION', '1.0.0' );
define( 'DBC_PATH', plugin_dir_path( __FILE__ ) );
define( 'DBC_URL', plugin_dir_url( __FILE__ ) );

// Include class files
require_once DBC_PATH . 'includes/class-cpt-book.php';
require_once DBC_PATH . 'includes/class-cpt-event.php';
require_once DBC_PATH . 'includes/class-taxonomy-series.php';
require_once DBC_PATH . 'includes/class-taxonomy-genre.php';
require_once DBC_PATH . 'includes/class-meta-boxes.php';
require_once DBC_PATH . 'includes/class-schema.php';

/**
 * Initialize the plugin
 */
function dbc_init() {
    DBC_CPT_Book::init();
    DBC_CPT_Event::init();
    DBC_Taxonomy_Series::init();
    DBC_Taxonomy_Genre::init();
    DBC_Meta_Boxes::init();
    DBC_Schema::init();
}
add_action( 'init', 'dbc_init', 0 );

/**
 * Flush rewrite rules on activation
 */
function dbc_activate() {
    dbc_init();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dbc_activate' );

/**
 * Flush rewrite rules on deactivation
 */
function dbc_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'dbc_deactivate' );

