<?php
/**
 * Plugin Name: Drew Bankston Custom
 * Description: Custom post types, taxonomies, and functionality for Drew Bankston author website
 * Version: 1.0.0
 * Author: Drew Bankston
 * Text Domain: dbc
 */

defined( 'ABSPATH' ) || exit;

define( 'DBC_VERSION', '1.5.0' );
define( 'DBC_PATH', plugin_dir_path( __FILE__ ) );
define( 'DBC_URL', plugin_dir_url( __FILE__ ) );

// Include class files
require_once DBC_PATH . 'includes/class-cpt-book.php';
require_once DBC_PATH . 'includes/class-cpt-event.php';
require_once DBC_PATH . 'includes/class-taxonomy-series.php';
require_once DBC_PATH . 'includes/class-taxonomy-genre.php';
require_once DBC_PATH . 'includes/class-meta-boxes.php';
require_once DBC_PATH . 'includes/class-schema.php';
require_once DBC_PATH . 'includes/class-newsletter.php';
require_once DBC_PATH . 'includes/class-cart.php';

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
    DBC_Newsletter::init();
    DBC_Cart::init();
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

/**
 * Admin action to populate book purchase data
 * Trigger via: /wp-admin/?dbc_populate_purchase=1
 */
function dbc_populate_purchase_data() {
    if ( ! isset( $_GET['dbc_populate_purchase'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $books_data = array(
        'khizara' => array(
            'amazon_url'     => 'https://www.amazon.com/gp/product/0997554711?ref_=dbs_m_mng_rwt_calw_tpbk_0&storeType=ebooks&qid=1759106183&sr=1-1',
            'signed_enabled' => '1',
            'signed_price'   => '17.99',
        ),
        'tokorel-book-2' => array(
            'amazon_url'     => 'https://www.amazon.com/gp/product/B0DP5HBHZS?ref_=dbs_m_mng_rwt_calw_tpbk_1&storeType=ebooks&qid=1759106183&sr=1-1',
            'signed_enabled' => '1',
            'signed_price'   => '13.99',
        ),
        'the-imagination-stone' => array(
            'amazon_url'     => 'https://www.amazon.com/Imagination-Stone-Drew-Bankston-ebook/dp/B0DSZHQMTD',
            'signed_enabled' => '1',
            'signed_price'   => '10.99',
            'digital_enabled' => '1',
            'digital_price'  => '4.99',
        ),
        'lines-of-force' => array(
            'amazon_url'     => 'https://www.amazon.com/Lines-Force-Weekend-Adventures-Andrew/dp/B0DJ2J76XS/',
            'signed_enabled' => '1',
            'signed_price'   => '11.99',
            'digital_enabled' => '1',
            'digital_price'  => '4.99',
        ),
        'the-monsters-lap' => array(
            'amazon_url'     => 'https://www.amazon.com/Monsters-Lap-Mr-Drew-Bankston/dp/0988696657',
        ),
    );
    
    $output = '<h2>Populating Book Purchase Data</h2><ul>';
    
    foreach ( $books_data as $slug => $data ) {
        $book = get_page_by_path( $slug, OBJECT, 'book' );
        
        if ( ! $book ) {
            $output .= "<li>❌ Book not found: {$slug}</li>";
            continue;
        }
        
        $output .= "<li>📚 <strong>{$book->post_title}</strong>: ";
        
        if ( ! empty( $data['amazon_url'] ) ) {
            update_post_meta( $book->ID, '_dbc_book_amazon_url', esc_url_raw( $data['amazon_url'] ) );
            $output .= "Amazon ✓ ";
        }
        
        if ( ! empty( $data['signed_enabled'] ) ) {
            update_post_meta( $book->ID, '_dbc_book_signed_enabled', $data['signed_enabled'] );
            update_post_meta( $book->ID, '_dbc_book_signed_price', $data['signed_price'] );
            $output .= "Signed (\${$data['signed_price']}) ✓ ";
        }
        
        if ( ! empty( $data['digital_enabled'] ) ) {
            update_post_meta( $book->ID, '_dbc_book_digital_enabled', $data['digital_enabled'] );
            update_post_meta( $book->ID, '_dbc_book_digital_price', $data['digital_price'] );
            $output .= "Digital (\${$data['digital_price']}) ✓ ";
        }
        
        $output .= "</li>";
    }
    
    $output .= '</ul><p><a href="' . home_url( '/books/' ) . '">View Books →</a></p>';
    
    wp_die( $output, 'Purchase Data Populated', array( 'response' => 200 ) );
}
add_action( 'admin_init', 'dbc_populate_purchase_data' );

/**
 * Admin action to create cart/checkout pages
 * Trigger via: /wp-admin/?dbc_create_shop_pages=1
 */
function dbc_create_shop_pages() {
    if ( ! isset( $_GET['dbc_create_shop_pages'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $pages = array(
        'cart' => array(
            'title'    => 'Cart',
            'template' => 'page-cart.php',
        ),
        'checkout' => array(
            'title'    => 'Checkout',
            'template' => 'page-checkout.php',
        ),
        'order-confirmation' => array(
            'title'    => 'Order Confirmation',
            'template' => 'page-order-confirmation.php',
        ),
    );
    
    $output = '<h2>Creating Shop Pages</h2><ul>';
    
    foreach ( $pages as $slug => $page_data ) {
        $existing = get_page_by_path( $slug );
        
        if ( $existing ) {
            // Update template if needed
            update_post_meta( $existing->ID, '_wp_page_template', $page_data['template'] );
            $output .= "<li>✓ <strong>{$page_data['title']}</strong> already exists (template updated)</li>";
        } else {
            // Create new page
            $page_id = wp_insert_post( array(
                'post_title'   => $page_data['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ) );
            
            if ( $page_id ) {
                update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
                $output .= "<li>✓ <strong>{$page_data['title']}</strong> created</li>";
            } else {
                $output .= "<li>❌ Failed to create {$page_data['title']}</li>";
            }
        }
    }
    
    $output .= '</ul><p><a href="' . home_url( '/cart/' ) . '">View Cart Page →</a></p>';
    
    wp_die( $output, 'Shop Pages Created', array( 'response' => 200 ) );
}
add_action( 'admin_init', 'dbc_create_shop_pages' );


