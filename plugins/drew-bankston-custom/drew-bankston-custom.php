<?php
/**
 * Plugin Name: Drew Bankston Custom
 * Description: Custom post types, taxonomies, and functionality for Drew Bankston author website
 * Version: 1.0.0
 * Author: Drew Bankston
 * Text Domain: dbc
 */

defined( 'ABSPATH' ) || exit;

define( 'DBC_VERSION', '1.7.0' );
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
require_once DBC_PATH . 'includes/class-square-payment.php';
require_once DBC_PATH . 'includes/class-orders-admin.php';
require_once DBC_PATH . 'includes/class-orders-settings.php';

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
    DBC_Square_Payment::init();
    DBC_Orders_Admin::init();
    DBC_Orders_Settings::init();
}
add_action( 'init', 'dbc_init', 0 );

/**
 * Flush rewrite rules on activation
 */
function dbc_activate() {
    dbc_init();
    DBC_Square_Payment::create_orders_table();
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
        'sounds-of-tomorrow' => array(
            'amazon_url'     => 'https://www.amazon.com/Sounds-Tomorrow-Drew-Bankston/dp/0997554770/ref=cm_cr_arp_d_product_top?ie=UTF8',
            'signed_enabled' => '1',
            'signed_price'   => '10.95',
            'digital_enabled' => '1',
            'digital_price'  => '4.99',
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
 * Admin action to fix free chapter filenames
 * Trigger via: /wp-admin/?dbc_fix_free_chapters=1
 */
function dbc_fix_free_chapter_filenames() {
    if ( ! isset( $_GET['dbc_fix_free_chapters'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Map of book slugs to correct filenames (with spaces preserved)
    $chapter_files = array(
        'khizara'               => 'Free Chapter - Khizara.pdf',
        'tokorel-book-2'        => 'Free Chapter Tokorel Book 2.pdf',
        'the-imagination-stone' => 'Free Chapter - Imagination Stone.pdf',
        'lines-of-force'        => 'Free Chapter - Lines of Force.pdf',
        'sounds-of-tomorrow'    => 'Free Chapter - Sounds of Tomorrow.pdf',
    );
    
    $output = '<h2>Fixing Free Chapter Filenames</h2><ul>';
    $files_dir = get_template_directory() . '/assets/free chapters/';
    
    foreach ( $chapter_files as $slug => $filename ) {
        $book = get_page_by_path( $slug, OBJECT, 'book' );
        
        if ( $book ) {
            // Delete any existing meta first to ensure clean update
            delete_post_meta( $book->ID, '_dbc_book_free_chapter' );
            update_post_meta( $book->ID, '_dbc_book_free_chapter', $filename );
            
            // Check if file actually exists
            $file_exists = file_exists( $files_dir . $filename ) ? '✅ File exists' : '⚠️ File NOT found';
            $output .= '<li>✅ Updated <strong>' . esc_html( $book->post_title ) . '</strong>: ' . esc_html( $filename ) . ' (' . $file_exists . ')</li>';
        } else {
            $output .= '<li>⚠️ Book not found: ' . esc_html( $slug ) . '</li>';
        }
    }
    
    $output .= '</ul>';
    $output .= '<p><strong>Files directory:</strong> ' . esc_html( $files_dir ) . '</p>';
    $output .= '<p><a href="' . home_url( '/account/' ) . '">Test Downloads →</a></p>';
    
    wp_die( $output, 'Free Chapter Filenames Fixed', array( 'response' => 200 ) );
}
add_action( 'init', 'dbc_fix_free_chapter_filenames', 1 ); // Run early on init hook

/**
 * Admin action to fix book formats stored as arrays
 * Trigger via: /wp-admin/?dbc_fix_formats=1
 */
function dbc_fix_book_formats() {
    if ( ! isset( $_GET['dbc_fix_formats'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $books = get_posts( array(
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ) );
    
    $output = '<h2>Fixing Book Formats</h2><ul>';
    
    foreach ( $books as $book ) {
        $formats = get_post_meta( $book->ID, '_dbc_book_formats', true );
        
        if ( is_array( $formats ) ) {
            // Convert array to comma-separated string
            $formats_string = implode( ', ', array_filter( array_map( function( $f ) {
                return is_array( $f ) ? implode( ', ', $f ) : $f;
            }, $formats ) ) );
            
            update_post_meta( $book->ID, '_dbc_book_formats', $formats_string );
            $output .= '<li>✅ Fixed <strong>' . esc_html( $book->post_title ) . '</strong>: ' . esc_html( $formats_string ) . '</li>';
        } else {
            $output .= '<li>✓ <strong>' . esc_html( $book->post_title ) . '</strong>: Already a string - ' . esc_html( $formats ?: '(empty)' ) . '</li>';
        }
    }
    
    $output .= '</ul><p><a href="' . home_url( '/books/khizara/' ) . '">Test Khizara Page →</a></p>';
    
    wp_die( $output, 'Book Formats Fixed', array( 'response' => 200 ) );
}
add_action( 'admin_init', 'dbc_fix_book_formats' );

/**
 * Admin action to save Square credentials
 * Trigger via: /wp-admin/?dbc_save_square=1
 */
function dbc_save_square_credentials() {
    if ( ! isset( $_GET['dbc_save_square'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Save Square sandbox credentials
    update_option( 'dbt_square_app_id', 'sandbox-sq0idb-aF_D7It7VkqCHd7yDnqifw' );
    update_option( 'dbt_square_access_token', 'EAAAlx-2IkBbfC5vlCfeKL4bGaRx-CGSuFQ11Nb5NviFB4oJ7cg2ew22HzzTRtS0' );
    update_option( 'dbt_square_location_id', 'LTZ598K5Y8CVJ' );
    update_option( 'dbt_square_sandbox', '1' ); // Enable sandbox mode

    // Create orders table
    DBC_Square_Payment::create_orders_table();

    // Flush rewrite rules for webhook endpoint
    flush_rewrite_rules();

    $output = '<h1>✓ Square Credentials Saved Successfully!</h1>';
    $output .= '<p>Sandbox Mode: <strong>ENABLED</strong></p>';
    $output .= '<ul>';
    $output .= '<li>Application ID: sandbox-sq0idb-aF_D7It7VkqCHd7yDnqifw</li>';
    $output .= '<li>Location ID: LTZ598K5Y8CVJ</li>';
    $output .= '<li>Orders table created</li>';
    $output .= '<li>Webhook endpoint registered: <code>/square-webhook/</code></li>';
    $output .= '</ul>';
    $output .= '<p><a href="' . admin_url( 'options-general.php?page=dbt-square' ) . '">View Square Settings →</a></p>';
    $output .= '<p><a href="' . home_url( '/checkout/' ) . '">Test Checkout Page →</a></p>';
    
    wp_die( $output, 'Square Credentials Saved', array( 'response' => 200 ) );
}
add_action( 'init', 'dbc_save_square_credentials', 99 ); // Run late on init hook

/**
 * Admin action to create Square orders table
 * Trigger via: /wp-admin/?dbc_create_orders_table=1
 */
function dbc_create_orders_table_admin() {
    if ( ! isset( $_GET['dbc_create_orders_table'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Create orders table
    DBC_Square_Payment::create_orders_table();
    
    // Flush rewrite rules for webhook endpoint
    flush_rewrite_rules();
    
    $output = '<h1>✓ Square Orders Table Created!</h1>';
    $output .= '<p>The database table for storing orders has been created successfully.</p>';
    $output .= '<p>Webhook endpoint registered: <code>/square-webhook/</code></p>';
    $output .= '<p><a href="' . admin_url( 'options-general.php?page=dbt-square' ) . '">View Square Settings →</a></p>';
    $output .= '<p><a href="' . home_url( '/checkout/' ) . '">Test Checkout Page →</a></p>';
    
    wp_die( $output, 'Orders Table Created', array( 'response' => 200 ) );
}
add_action( 'init', 'dbc_create_orders_table_admin', 99 );

/**
 * Admin action to fix orders table (add missing user_id column)
 * Trigger via: /wp-admin/?dbc_fix_orders_table=1
 */
function dbc_fix_orders_table_admin() {
    if ( ! isset( $_GET['dbc_fix_orders_table'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'dbc_orders';
    
    // Check if user_id column exists
    $column_exists = $wpdb->get_results( $wpdb->prepare(
        "SHOW COLUMNS FROM $table_name LIKE %s",
        'user_id'
    ) );
    
    if ( empty( $column_exists ) ) {
        // Add user_id column
        $wpdb->query( "ALTER TABLE $table_name ADD COLUMN user_id bigint(20) DEFAULT NULL AFTER id" );
        $wpdb->query( "ALTER TABLE $table_name ADD KEY user_id (user_id)" );
        $output = '<h1>✓ Orders Table Fixed!</h1><p>Added user_id column successfully.</p>';
    } else {
        $output = '<h1>✓ Orders Table OK</h1><p>user_id column already exists.</p>';
    }
    
    wp_die( $output, 'Fix Orders Table', array( 'response' => 200 ) );
}
add_action( 'init', 'dbc_fix_orders_table_admin', 99 );

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

/**
 * Admin action to update Kindle URLs for all books
 * Trigger via: /wp-admin/?dbc_update_kindle_urls=1
 */
function dbc_update_kindle_urls() {
    if ( ! isset( $_GET['dbc_update_kindle_urls'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Book ID => Kindle URL mapping
    $kindle_urls = array(
        9  => 'https://www.amazon.com/Khizara-Book-Tokorel-Drew-Bankston-ebook/dp/B0C8S4TJWW/ref=tmm_kin_swatch_0',
        10 => 'https://www.amazon.com/Tokorel-Book-2-Drew-Bankston-ebook/dp/B0DB4LXC68/ref=sr_1_1',
        11 => 'https://www.amazon.com/Lines-Force-Weekend-Adventures-Andrew-ebook/dp/B0DG9W4P23/ref=sr_1_1',
        12 => 'https://www.amazon.com/Monsters-Lap-Drew-Bankston-ebook/dp/B0151Z52BU/ref=sr_1_1',
        13 => 'https://www.amazon.com/Imagination-Stone-Drew-Bankston-ebook/dp/B0DSZHQMTD/ref=sr_1_1',
        14 => 'https://www.amazon.com/Sounds-Tomorrow-Drew-Bankston-ebook/dp/B00IP4R8IU/ref=sr_1_1',
    );

    $updated = array();

    foreach ( $kindle_urls as $post_id => $kindle_url ) {
        $post = get_post( $post_id );
        if ( $post && $post->post_type === 'book' ) {
            update_post_meta( $post_id, '_dbc_book_kindle_url', esc_url_raw( $kindle_url ) );
            $updated[] = esc_html( $post->post_title ) . ' (ID: ' . $post_id . ')';
        }
    }

    $output = '<h1>✓ Kindle URLs Updated!</h1>';
    $output .= '<p>Updated ' . count( $updated ) . ' books:</p>';
    $output .= '<ul><li>' . implode( '</li><li>', $updated ) . '</li></ul>';
    $output .= '<p><a href="' . admin_url( 'edit.php?post_type=book' ) . '">Back to Books</a></p>';

    wp_die( $output, 'Update Kindle URLs', array( 'response' => 200 ) );
}
add_action( 'init', 'dbc_update_kindle_urls', 99 );

