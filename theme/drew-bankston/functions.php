<?php
/**
 * Drew Bankston Theme Functions
 */

defined( 'ABSPATH' ) || exit;

define( 'DBT_VERSION', '2.1.0' );
define( 'DBT_PATH', get_template_directory() );
define( 'DBT_URL', get_template_directory_uri() );

/**
 * Theme setup
 */
function dbt_setup() {
    // Add theme support
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'editor-styles' );
    
    // Custom image sizes
    add_image_size( 'book-cover', 400, 600, true );
    add_image_size( 'book-cover-large', 600, 900, true );
    add_image_size( 'hero-bg', 1920, 1080, true );
    
    // Register navigation menus
    register_nav_menus( array(
        'primary'   => 'Primary Navigation',
        'footer'    => 'Footer Navigation',
        'mobile'    => 'Mobile Navigation',
    ) );
    
    // Editor styles
    add_editor_style( 'assets/css/editor-style.css' );
}
add_action( 'after_setup_theme', 'dbt_setup' );

/**
 * Enqueue scripts and styles
 */
function dbt_scripts() {
    // Google Fonts - Cormorant Garamond + Inter
    wp_enqueue_style(
        'dbt-google-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );
    
    // Design tokens
    wp_enqueue_style( 'dbt-tokens', DBT_URL . '/assets/css/tokens.css', array(), DBT_VERSION );
    
    // Main stylesheet
    wp_enqueue_style( 'dbt-style', DBT_URL . '/assets/css/style.css', array( 'dbt-tokens' ), DBT_VERSION );
    
    // GSAP
    wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true );
    wp_enqueue_script( 'gsap-scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', array( 'gsap' ), '3.12.2', true );
    
    // Lottie Player
    wp_enqueue_script( 'lottie-player', 'https://unpkg.com/@lottiefiles/lottie-player@2.0.3/dist/lottie-player.js', array(), '2.0.3', true );
    
    // Main JS
    wp_enqueue_script( 'dbt-main', DBT_URL . '/assets/js/main.js', array( 'gsap', 'gsap-scrolltrigger' ), DBT_VERSION, true );
    
    // Pass data to JS
    wp_localize_script( 'dbt-main', 'dbtData', array(
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'themeUrl'  => DBT_URL,
        'lottieUrl' => DBT_URL . '/assets/lottie/',
    ) );
}
add_action( 'wp_enqueue_scripts', 'dbt_scripts' );

/**
 * Register widget areas
 */
function dbt_widgets_init() {
    register_sidebar( array(
        'name'          => 'Footer Newsletter',
        'id'            => 'footer-newsletter',
        'description'   => 'Newsletter signup widget area',
        'before_widget' => '<div class="footer-newsletter-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'dbt_widgets_init' );

/**
 * Get social links
 */
function dbt_get_social_links() {
    return array(
        'facebook'  => array(
            'url'   => 'https://www.facebook.com/DrewBankstonAuthor',
            'label' => 'Facebook',
        ),
        'twitter'   => array(
            'url'   => 'https://twitter.com/drewbankston',
            'label' => 'X (Twitter)',
        ),
        'instagram' => array(
            'url'   => 'https://www.instagram.com/drewbankston/',
            'label' => 'Instagram',
        ),
        'amazon'    => array(
            'url'   => 'https://www.amazon.com/stores/Drew-Bankston/author/B00J33F9PU',
            'label' => 'Amazon Author Page',
        ),
        'goodreads' => array(
            'url'   => 'https://www.goodreads.com/author/show/8115661.Drew_Bankston',
            'label' => 'Goodreads',
        ),
    );
}

/**
 * Get genre display for a book
 */
function dbt_get_book_genre( $book_id = null ) {
    if ( ! $book_id ) {
        $book_id = get_the_ID();
    }
    
    $genres = get_the_terms( $book_id, 'genre' );
    if ( ! $genres || is_wp_error( $genres ) ) {
        return '';
    }
    
    return implode( ', ', wp_list_pluck( $genres, 'name' ) );
}

/**
 * Get series display for a book
 */
function dbt_get_book_series( $book_id = null ) {
    if ( ! $book_id ) {
        $book_id = get_the_ID();
    }
    
    $series = get_the_terms( $book_id, 'series' );
    if ( ! $series || is_wp_error( $series ) ) {
        return null;
    }
    
    return $series[0];
}

/**
 * Check if book is standalone
 */
function dbt_is_standalone( $book_id = null ) {
    $series = dbt_get_book_series( $book_id );
    if ( ! $series ) {
        return true;
    }
    return $series->slug === 'standalones';
}

/**
 * Get standalone genre label
 */
function dbt_get_standalone_label( $book_id = null ) {
    $genre = dbt_get_book_genre( $book_id );
    if ( empty( $genre ) ) {
        return 'Standalone';
    }
    return 'Standalone ' . $genre;
}

/**
 * Format event date
 */
function dbt_format_event_date( $datetime ) {
    if ( empty( $datetime ) ) {
        return '';
    }
    $timestamp = strtotime( $datetime );
    return date_i18n( 'F j, Y \a\t g:i A', $timestamp );
}

/**
 * Get social icon SVG
 */
function dbt_get_social_icon( $platform ) {
    $icons = array(
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        'amazon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.958 10.09c0 1.232.029 2.256-.591 3.351-.502.891-1.301 1.439-2.186 1.439-1.214 0-1.922-.924-1.922-2.292 0-2.692 2.415-3.182 4.7-3.182v.684zm3.186 7.705c-.209.189-.512.201-.745.074-1.052-.872-1.238-1.276-1.814-2.106-1.734 1.767-2.962 2.297-5.209 2.297-2.66 0-4.731-1.641-4.731-4.925 0-2.565 1.391-4.309 3.37-5.164 1.715-.754 4.11-.891 5.942-1.095v-.41c0-.753.06-1.642-.383-2.294-.385-.579-1.124-.82-1.775-.82-1.205 0-2.277.618-2.54 1.897-.054.285-.261.567-.549.582l-3.061-.333c-.259-.056-.548-.266-.472-.66C6.076 1.567 9.363 0 12.321 0c1.429 0 3.297.38 4.423 1.462 1.429 1.37 1.293 3.2 1.293 5.19v4.697c0 1.415.587 2.035 1.138 2.8.196.271.239.596-.01.794-.623.52-1.731 1.49-2.34 2.033l-.68-.181zm5.636 1.678c-1.129.874-2.754 1.395-4.154 1.395-1.965 0-3.735-.606-5.077-1.615-.115-.088-.025-.211.126-.211 1.48.067 3.39.399 4.845-.2.715-.295 1.235-.877 1.235-1.639V5.203c0-.825-.665-1.495-1.486-1.495H5.486c-.82 0-1.486.67-1.486 1.495v12c0 .825.666 1.495 1.486 1.495h10.028c.82 0 1.486-.67 1.486-1.495v-3.33c0-.211.17-.382.38-.382.088 0 .175.03.244.086l4.162 3.414c.146.12.175.339.064.492-.046.063-.113.113-.19.139z"/></svg>',
        'goodreads' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.01 2.5c-3.31 0-6 3.27-6 7.31 0 4.04 2.69 7.31 6 7.31 2.15 0 4.03-1.3 5.08-3.24v2.99c0 3.06-2.35 4.63-5.42 4.63-2.04 0-3.78-.73-4.92-1.73l-.95 1.46c1.49 1.3 3.56 2.08 5.91 2.08 4.24 0 7.15-2.42 7.15-6.44V2.75h-1.72v2.9C16.04 3.8 14.16 2.5 12.01 2.5zm-.35 12.81c-2.24 0-4.06-2.46-4.06-5.5s1.82-5.5 4.06-5.5c2.24 0 4.06 2.46 4.06 5.5s-1.82 5.5-4.06 5.5z"/></svg>',
    );
    
    return isset( $icons[ $platform ] ) ? $icons[ $platform ] : '';
}

/**
 * Square eCommerce - Configuration holder (not connected yet)
 * Credentials to be added later:
 * - Square Application ID
 * - Access Token
 * - Location ID
 * - Webhook endpoint
 */
function dbt_get_square_config() {
    return array(
        'enabled'        => false,
        'sandbox'        => true,
        'application_id' => get_option( 'dbt_square_app_id', '' ),
        'access_token'   => get_option( 'dbt_square_access_token', '' ),
        'location_id'    => get_option( 'dbt_square_location_id', '' ),
        'webhook_url'    => home_url( '/square-webhook/' ),
    );
}

/**
 * Square settings page (admin)
 */
function dbt_add_square_settings() {
    add_options_page(
        'Square Integration',
        'Square Integration',
        'manage_options',
        'dbt-square',
        'dbt_square_settings_page'
    );
}
add_action( 'admin_menu', 'dbt_add_square_settings' );

function dbt_square_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    if ( isset( $_POST['dbt_square_save'] ) && check_admin_referer( 'dbt_square_settings' ) ) {
        update_option( 'dbt_square_app_id', sanitize_text_field( $_POST['dbt_square_app_id'] ?? '' ) );
        update_option( 'dbt_square_access_token', sanitize_text_field( $_POST['dbt_square_access_token'] ?? '' ) );
        update_option( 'dbt_square_location_id', sanitize_text_field( $_POST['dbt_square_location_id'] ?? '' ) );
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }
    
    $config = dbt_get_square_config();
    ?>
    <div class="wrap">
        <h1>Square Integration Settings</h1>
        <p class="description">Configure Square payment processing. <strong>Not yet connected</strong> - add credentials below when ready.</p>
        
        <form method="post">
            <?php wp_nonce_field( 'dbt_square_settings' ); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="dbt_square_app_id">Application ID</label></th>
                    <td>
                        <input type="text" id="dbt_square_app_id" name="dbt_square_app_id" value="<?php echo esc_attr( $config['application_id'] ); ?>" class="regular-text">
                        <p class="description">From Square Developer Dashboard</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="dbt_square_access_token">Access Token</label></th>
                    <td>
                        <input type="password" id="dbt_square_access_token" name="dbt_square_access_token" value="<?php echo esc_attr( $config['access_token'] ); ?>" class="regular-text">
                        <p class="description">Keep this secret! From Square Developer Dashboard</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="dbt_square_location_id">Location ID</label></th>
                    <td>
                        <input type="text" id="dbt_square_location_id" name="dbt_square_location_id" value="<?php echo esc_attr( $config['location_id'] ); ?>" class="regular-text">
                        <p class="description">Your Square business location ID</p>
                    </td>
                </tr>
                <tr>
                    <th>Webhook URL</th>
                    <td>
                        <code><?php echo esc_html( $config['webhook_url'] ); ?></code>
                        <p class="description">Add this URL to Square Webhooks when ready</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="dbt_square_save" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

