<?php
/**
 * Drew Bankston Theme Functions
 */

defined( 'ABSPATH' ) || exit;

define( 'DBT_VERSION', '3.2.8' );
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
 * Add favicon and app icons
 */
function dbt_add_favicons() {
    $favicon_url = DBT_URL . '/assets/images/favicon';
    ?>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo esc_url( $favicon_url . '/favicon.ico' ); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( $favicon_url . '/favicon-16x16.png' ); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( $favicon_url . '/favicon-32x32.png' ); ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?php echo esc_url( $favicon_url . '/favicon-48x48.png' ); ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?php echo esc_url( $favicon_url . '/favicon-64x64.png' ); ?>">
    <link rel="icon" type="image/png" sizes="128x128" href="<?php echo esc_url( $favicon_url . '/favicon-128x128.png' ); ?>">
    <link rel="icon" type="image/png" sizes="256x256" href="<?php echo esc_url( $favicon_url . '/favicon-256x256.png' ); ?>">
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="256x256" href="<?php echo esc_url( $favicon_url . '/favicon-256x256.png' ); ?>">
    <!-- MS Tile -->
    <meta name="msapplication-TileImage" content="<?php echo esc_url( $favicon_url . '/favicon-256x256.png' ); ?>">
    <meta name="msapplication-TileColor" content="#0f0a1e">
    <!-- Theme color -->
    <meta name="theme-color" content="#0f0a1e">
    <?php
}
add_action( 'wp_head', 'dbt_add_favicons', 1 );

/**
 * Custom Login Page Styling
 */
function dbt_login_styles() {
    $logo_url = DBT_URL . '/assets/images/logos/horizontal light logo-long.png';
    $bg_url = DBT_URL . '/assets/images/login-bg.jpg'; // Optional background
    ?>
    <style type="text/css">
        /* Login page background */
        body.login {
            background: linear-gradient(135deg, #0f0a1e 0%, #1a1230 50%, #0f0a1e 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Logo */
        #login h1 a {
            background-image: url('<?php echo esc_url( $logo_url ); ?>');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            width: 320px;
            height: 80px;
            margin-bottom: 30px;
        }
        
        /* Login form container */
        .login form,
        #loginform {
            background: rgba(26, 18, 48, 0.95) !important;
            border: 1px solid rgba(199, 184, 255, 0.15) !important;
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            padding: 30px 24px !important;
        }
        
        /* Labels */
        .login label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Input fields */
        .login input[type="text"],
        .login input[type="password"],
        .login input[type="email"],
        #user_login,
        #user_pass {
            background: rgba(15, 10, 30, 0.9) !important;
            border: 1px solid rgba(199, 184, 255, 0.25) !important;
            border-radius: 8px !important;
            color: #fff !important;
            padding: 12px 16px !important;
            font-size: 16px !important;
            margin-top: 8px !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        
        .login input[type="text"]:focus,
        .login input[type="password"]:focus,
        .login input[type="email"]:focus,
        #user_login:focus,
        #user_pass:focus {
            background: rgba(15, 10, 30, 1) !important;
            border-color: #c7b8ff !important;
            box-shadow: 0 0 0 3px rgba(199, 184, 255, 0.2) !important;
            outline: none !important;
        }
        
        /* Input placeholder */
        .login input::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }
        
        /* Remember me checkbox */
        .login .forgetmenot label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
        }
        
        .login input[type="checkbox"] {
            background: rgba(15, 10, 30, 0.8);
            border: 1px solid rgba(199, 184, 255, 0.3);
            border-radius: 4px;
        }
        
        .login input[type="checkbox"]:checked {
            background: #c7b8ff;
            border-color: #c7b8ff;
        }
        
        /* Submit button */
        .login .button-primary,
        #wp-submit {
            background: linear-gradient(135deg, #c7b8ff 0%, #a890ff 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            color: #0f0a1e !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            text-shadow: none !important;
            box-shadow: 0 4px 15px rgba(199, 184, 255, 0.3) !important;
            transition: all 0.2s ease !important;
            width: 100% !important;
            height: auto !important;
            margin-top: 10px !important;
            line-height: 1.5 !important;
        }
        
        .login .button-primary:hover,
        .login .button-primary:focus,
        #wp-submit:hover,
        #wp-submit:focus {
            background: linear-gradient(135deg, #d4c7ff 0%, #b8a0ff 100%) !important;
            box-shadow: 0 6px 20px rgba(199, 184, 255, 0.4) !important;
            color: #0f0a1e !important;
        }
        
        /* Links */
        .login #nav a,
        .login #backtoblog a {
            color: rgba(199, 184, 255, 0.7);
            font-size: 13px;
            transition: color 0.2s ease;
        }
        
        .login #nav a:hover,
        .login #backtoblog a:hover {
            color: #c7b8ff;
        }
        
        .login #nav,
        .login #backtoblog {
            text-align: center;
        }
        
        /* Error/Info messages */
        .login .message,
        .login .success {
            background: rgba(26, 18, 48, 0.9);
            border-left: 4px solid #c7b8ff;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        
        .login #login_error {
            background: rgba(248, 113, 113, 0.1);
            border-left: 4px solid #f87171;
            border-radius: 8px;
            color: #fca5a5;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        
        .login #login_error a {
            color: #fca5a5;
        }
        
        /* Password field container - use flex to properly align */
        .login .wp-pwd {
            position: relative;
            display: flex;
            align-items: flex-start;
        }
        
        /* Password input - fill available space */
        .login .wp-pwd input[type="password"],
        .login .wp-pwd input[type="text"] {
            flex: 1;
            padding-right: 45px !important;
        }
        
        /* Password visibility toggle - center vertically with input */
        .login .wp-pwd .button.wp-hide-pw {
            background: transparent;
            border: none;
            color: rgba(199, 184, 255, 0.6);
            position: absolute;
            right: 8px;
            /* Account for input margin-top (8px) and center within input height */
            top: calc(8px + 24px);
            transform: translateY(-50%);
            height: 24px;
            width: 24px;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login .wp-pwd .button.wp-hide-pw:hover {
            color: #c7b8ff;
        }
        
        .login .wp-pwd .button.wp-hide-pw .dashicons {
            color: inherit;
            width: 20px;
            height: 20px;
            font-size: 20px;
            line-height: 1;
            vertical-align: middle !important;
            position: relative;
            top: -0.5px;
        }
        
        /* Privacy policy link */
        .login .privacy-policy-page-link a {
            color: rgba(199, 184, 255, 0.5);
            font-size: 12px;
        }
        
        .login .privacy-policy-page-link a:hover {
            color: #c7b8ff;
        }
        
        /* Language switcher */
        .login .language-switcher {
            background: rgba(26, 18, 48, 0.7);
            border: 1px solid rgba(199, 184, 255, 0.1);
            border-radius: 8px;
        }
        
        .login .language-switcher label {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .login .language-switcher select {
            background: rgba(15, 10, 30, 0.8);
            border: 1px solid rgba(199, 184, 255, 0.2);
            color: #fff;
            border-radius: 6px;
        }
        
        /* Register link styling for lost password page */
        .login p#reg_passmail {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        /* Divider */
        .login-divider {
            display: flex;
            align-items: center;
            margin: 16px 0;
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
        }
        
        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(199, 184, 255, 0.2);
        }
        
        .login-divider::before {
            margin-right: 16px;
        }
        
        .login-divider::after {
            margin-left: 16px;
        }
        
        /* Secondary button (Create Account) */
        .login-secondary-button {
            display: block;
            width: 100%;
            background: transparent !important;
            border: 2px solid rgba(199, 184, 255, 0.5) !important;
            border-radius: 8px !important;
            color: #c7b8ff !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            text-align: center;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box;
        }
        
        .login-secondary-button:hover,
        .login-secondary-button:focus {
            background: rgba(199, 184, 255, 0.1) !important;
            border-color: #c7b8ff !important;
            color: #fff !important;
        }
        
        /* Make login form a flex container to reorder elements */
        #loginform,
        #registerform {
            display: flex !important;
            flex-direction: column !important;
        }
        
        /* Reorder login form elements */
        #loginform .user-pass-wrap { order: 1; }
        #loginform .forgetmenot { order: 2; }
        #loginform .submit { order: 3; }
        #loginform .login-inline-action { order: 4; }
        
        /* Reorder register form elements */
        #registerform .user-email-wrap { order: 1; }
        #registerform #reg_passmail { order: 2; }
        #registerform .submit { order: 3; }
        #registerform .login-inline-action { order: 4; }
        
        /* Hide tooltip until fields are filled */
        #registerform #reg_passmail {
            display: none;
            margin-top: 16px;
            margin-bottom: 0;
        }
        
        #registerform #reg_passmail.visible {
            display: block;
        }
        
        /* Inline action section (inside form) */
        .login-inline-action {
            margin-top: 32px !important;
            padding-top: 8px;
            text-align: center;
            width: 100%;
        }
        
        /* Divider inside inline action */
        .login-inline-action .login-divider {
            margin: 0 0 20px 0;
        }
        
        .login-inline-action p,
        .login-inline-hint {
            color: rgba(255, 255, 255, 0.6) !important;
            margin: 0 0 12px 0 !important;
            font-size: 13px !important;
            background: none !important;
            border: none !important;
            padding: 0 !important;
        }
        
        .login-inline-action .login-secondary-button {
            margin-top: 0;
        }
        
        /* Space between submit and inline action section */
        #loginform .submit,
        #registerform .submit {
            margin-bottom: 0 !important;
        }
        
        /* Additional action box (outside form - for register/lostpassword pages) */
        .login-action-box {
            background: rgba(26, 18, 48, 0.95);
            border: 1px solid rgba(199, 184, 255, 0.15);
            border-radius: 16px;
            padding: 24px;
            margin: 20px auto 0;
            text-align: center;
            width: 320px;
            max-width: calc(100% - 40px);
            box-sizing: border-box;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .login-action-box p {
            color: rgba(255, 255, 255, 0.7);
            margin: 0 0 16px;
            font-size: 14px;
        }
        
        /* Registration form specific styles */
        .login.action-register #registerform {
            background: rgba(26, 18, 48, 0.95) !important;
        }
        
        .login #registerform .button-primary {
            background: linear-gradient(135deg, #c7b8ff 0%, #a890ff 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            color: #0f0a1e !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            width: 100% !important;
        }
        
        /* Lost password form specific */
        .login.action-lostpassword #lostpasswordform {
            background: rgba(26, 18, 48, 0.95) !important;
        }
        
        /* Indicator text on registration */
        .login #reg_passmail,
        .login p.indicator-hint {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            margin-top: 16px;
            background: rgba(26, 18, 48, 0.8);
            border: 1px solid rgba(199, 184, 255, 0.15);
            border-radius: 8px;
            padding: 12px 16px;
        }
        
        /* Registration confirmation message */
        .login .message.register,
        .login .message {
            background: rgba(26, 18, 48, 0.9) !important;
            border-left: 4px solid #c7b8ff !important;
            border-radius: 8px !important;
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 12px 16px !important;
        }
        
        .login .message a {
            color: #c7b8ff !important;
            text-decoration: underline;
        }
        
        .login .message a:hover {
            color: #fff !important;
        }
        
        /* Check email confirmation page */
        .login.action-checkemail #login {
            background: transparent;
        }
        
        .login.action-checkemail #login > p {
            background: rgba(26, 18, 48, 0.95) !important;
            border: 1px solid rgba(199, 184, 255, 0.15) !important;
            border-radius: 16px !important;
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 24px !important;
            text-align: center;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .login.action-checkemail #login > p a {
            color: #c7b8ff !important;
            font-weight: 600;
            text-decoration: none;
        }
        
        .login.action-checkemail #login > p a:hover {
            color: #fff !important;
            text-decoration: underline;
        }
        
        /* Description text under inputs on registration */
        .login #registerform p.description,
        .login #registerform .description {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            margin-top: 8px;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Only run on registration page
        var registerForm = document.getElementById('registerform');
        if (!registerForm) return;
        
        var userLogin = document.getElementById('user_login');
        var userEmail = document.getElementById('user_email');
        var regPassmail = document.getElementById('reg_passmail');
        
        if (!userLogin || !userEmail || !regPassmail) return;
        
        function checkFields() {
            var usernameValid = userLogin.value.trim().length > 0;
            var emailValid = userEmail.value.trim().length > 0 && userEmail.value.includes('@');
            
            if (usernameValid && emailValid) {
                regPassmail.classList.add('visible');
            } else {
                regPassmail.classList.remove('visible');
            }
        }
        
        userLogin.addEventListener('input', checkFields);
        userEmail.addEventListener('input', checkFields);
        
        // Check on page load in case of browser autofill
        setTimeout(checkFields, 100);
    });
    </script>
    <?php
}
add_action( 'login_enqueue_scripts', 'dbt_login_styles' );

/**
 * Add Create Account button inside login form
 */
function dbt_login_form_create_account() {
    ?>
    <div class="login-inline-action">
        <div class="login-divider">or</div>
        <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="login-secondary-button">
            Create an Account
        </a>
    </div>
    <?php
}
add_action( 'login_form', 'dbt_login_form_create_account' );

/**
 * Add Sign In button inside registration form
 */
function dbt_register_form_signin() {
    ?>
    <div class="login-inline-action">
        <div class="login-divider">or</div>
        <p class="login-inline-hint">Already have an account?</p>
        <a href="<?php echo esc_url( wp_login_url() ); ?>" class="login-secondary-button">
            Sign In
        </a>
    </div>
    <?php
}
add_action( 'register_form', 'dbt_register_form_signin' );

/**
 * Add Back to Sign In button on lost password page (via footer since no hook inside form)
 */
function dbt_login_footer_content() {
    $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
    
    // Only show on lost password page
    if ( $action === 'lostpassword' ) {
        ?>
        <div class="login-action-box">
            <p>Remember your password?</p>
            <a href="<?php echo esc_url( wp_login_url() ); ?>" class="login-secondary-button">
                Back to Sign In
            </a>
        </div>
        <?php
    }
}
add_action( 'login_footer', 'dbt_login_footer_content' );

/**
 * Custom login logo URL
 */
function dbt_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'dbt_login_logo_url' );

/**
 * Custom login logo title
 */
function dbt_login_logo_title() {
    return get_bloginfo( 'name' ) . ' - Author';
}
add_filter( 'login_headertext', 'dbt_login_logo_title' );

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
    
    // Lottie Player - Using latest stable version from CDN
    wp_enqueue_script( 'lottie-player', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js', array(), '2.0.8', array(
        'strategy' => 'defer',
        'in_footer' => true,
    ) );
    
    // Main JS
    wp_enqueue_script( 'dbt-main', DBT_URL . '/assets/js/main.js', array( 'gsap', 'gsap-scrolltrigger' ), DBT_VERSION, true );
    
    // Pass data to JS
    wp_localize_script( 'dbt-main', 'dbtData', array(
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'themeUrl'        => DBT_URL,
        'lottieUrl'       => DBT_URL . '/assets/lottie/',
        'newsletterNonce' => wp_create_nonce( 'dbc_newsletter_nonce' ),
    ) );
    
    // Cart AJAX data
    wp_localize_script( 'dbt-main', 'dbcAjax', array(
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'cartUrl'   => home_url( '/cart/' ),
        'cartNonce' => wp_create_nonce( 'dbc_cart_nonce' ),
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
            'url'   => 'https://www.amazon.com/stores/Drew-Bankston/author/B00I7ICB9A',
            'label' => 'Amazon Author Page',
        ),
        'goodreads' => array(
            'url'   => 'https://www.goodreads.com/author/show/7391165.Drew_Bankston',
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
 * Using Font Awesome 6 Free brand icons for maximum recognition
 * Source: https://fontawesome.com/icons (Free license)
 */
function dbt_get_social_icon( $platform ) {
    $icons = array(
        // Facebook - FA6 Brand
        'facebook' => '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"/></svg>',
        
        // X (Twitter) - FA6 Brand  
        'twitter' => '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>',
        
        // Instagram - FA6 Brand
        'instagram' => '<svg viewBox="0 0 448 512" fill="currentColor"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>',
        
        // Amazon - FA6 Brand (recognizable smile + arrow)
        'amazon' => '<svg viewBox="0 0 448 512" fill="currentColor"><path d="M257.2 162.7c-48.7 1.8-169.5 15.5-169.5 117.5 0 109.5 138.3 114 183.5 43.2 6.5 10.2 35.4 37.5 45.3 46.8l56.8-56S341 288.9 341 261.4V114.3C341 89 316.5 32 228.7 32 140.7 32 94 87 94 136.3l73.5 6.8c16.3-49.5 54.2-49.5 54.2-49.5 40.7-.1 35.5 29.8 35.5 69.1zm0 86.8c0 80-84.2 68-84.2 17.2 0-47.2 50.5-56.7 84.2-57.8v40.6zm136 163.5c-7.7 10-70 67-174.5 67S34.2 408.5 9.7 379c-6.8-7.7 1-11.3 5.5-8.3C88.5 415.2 203 488.5 387.7 401c7.5-3.7 13.3 2 5.5 12zm39.8 2.2c-6.5 15.8-16 26.8-21.2 31-5.5 4.5-9.5 2.7-6.5-3.8s19.3-46.5 12.7-55c-6.5-8.3-37-4.3-48-3.2-10.8 1-13 2-14-.3-2.3-5.7 21.7-15.5 37.5-17.5 15.7-1.8 41-.8 46 5.7 3.7 5.1 0 27.1-6.5 43.1z"/></svg>',
        
        // Goodreads - FA6 Brand (recognizable G)
        'goodreads' => '<svg viewBox="0 0 448 512" fill="currentColor"><path d="M299.9 191.2c5.1 37.3-4.7 79-35.9 100.7-22.3 15.5-52.8 14.1-70.8 5.7-37.1-17.3-49.5-58.6-46.8-97.2 4.3-60.9 40.9-87.9 75.3-87.5 46.9-.2 71.8 31.8 78.2 78.3zM448 88v336c0 30.9-25.1 56-56 56H56c-30.9 0-56-25.1-56-56V88c0-30.9 25.1-56 56-56h336c30.9 0 56 25.1 56 56zM330 313.2s-.1-34-.1-217.3h-29v40.3c-.8 .3-1.2-.5-1.6-1.2-9.6-20.7-35.9-46.3-76-46-51.9 .4-87.2 31.2-100.6 77.8-4.3 14.9-5.8 30.1-5.5 45.6 1.7 77.9 45.1 117.8 112.4 115.2 28.9-1.1 54.5-17 69-45.2 .5-1 1.1-1.9 1.7-2.9 .2 .1 .4 .1 .6 .2 .3 3.8 .2 30.7 .1 34.5-.2 14.8-2 29.5-7.2 43.5-7.8 21-22.3 34.7-44.5 39.5-17.8 3.9-35.6 3.8-53.2-1.2-21.5-6.1-36.5-19-41.1-41.8-.3-1.6-1.3-1.3-2.3-1.3h-26.8c.8 10.6 3.2 20.3 8.5 29.2 24.2 40.5 82.7 48.5 128.2 37.4 49.9-12.3 67.3-54.9 67.4-106.3z"/></svg>',
        
        // YouTube - FA6 Brand
        'youtube' => '<svg viewBox="0 0 576 512" fill="currentColor"><path d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z"/></svg>',
        
        // TikTok - FA6 Brand
        'tiktok' => '<svg viewBox="0 0 448 512" fill="currentColor"><path d="M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z"/></svg>',
        
        // LinkedIn - FA6 Brand
        'linkedin' => '<svg viewBox="0 0 448 512" fill="currentColor"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/></svg>',
        
        // Pinterest - FA6 Brand
        'pinterest' => '<svg viewBox="0 0 496 512" fill="currentColor"><path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3 .8-3.4 5-20.3 6.9-28.1 .6-2.5 .3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"/></svg>',
        
        // Email/Newsletter - FA6 Solid
        'email' => '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>',
        
        // BookBub - Custom (simplified BB)
        'bookbub' => '<svg viewBox="0 0 448 512" fill="currentColor"><path d="M0 88C0 39.4 39.4 0 88 0H360c48.6 0 88 39.4 88 88V424c0 48.6-39.4 88-88 88H88c-48.6 0-88-39.4-88-88V88zm120 48v240h72c48 0 80-28 80-68 0-28-16-48-44-56v-1c22-8 36-28 36-51 0-38-30-64-76-64H120zm48 40h20c20 0 32 10 32 28s-12 28-32 28h-20v-56zm0 96h24c24 0 36 12 36 32s-12 32-36 32h-24v-64zm152-136v240h48V136h-48z"/></svg>',
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

