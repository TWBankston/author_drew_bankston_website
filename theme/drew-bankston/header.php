<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#primary">Skip to content</a>

<header class="site-header" id="site-header">
    <div class="container">
        <div class="header-inner">
            <div class="site-branding">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
                    <img src="<?php echo esc_url( DBT_URL . '/assets/images/logos/horizontal light logo-long.png' ); ?>" alt="Drew Bankston - Author" class="site-logo__image">
                </a>
            </div>
            
            <nav class="primary-nav" aria-label="Primary navigation">
                <ul class="nav-menu">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>Home</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" <?php echo is_post_type_archive( 'book' ) || is_singular( 'book' ) ? 'aria-current="page"' : ''; ?>>Books</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" <?php echo is_post_type_archive( 'event' ) || is_singular( 'event' ) ? 'aria-current="page"' : ''; ?>>Events</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" <?php echo is_page( 'about' ) ? 'aria-current="page"' : ''; ?>>About</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" <?php echo is_page( 'contact' ) ? 'aria-current="page"' : ''; ?>>Contact</a></li>
                </ul>
                
                <div class="header-cta">
                    <?php 
                    $cart_count = class_exists( 'DBC_Cart' ) ? DBC_Cart::get_cart_count() : 0;
                    ?>
                    <a href="<?php echo esc_url( home_url( '/cart/' ) ); ?>" class="header-cart<?php echo $cart_count === 0 ? ' is-hidden' : ''; ?>" id="header-cart-link" aria-label="View cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span class="header-cart__count<?php echo $cart_count === 0 ? ' is-hidden' : ''; ?>" id="header-cart-count"><?php echo esc_html( $cart_count ); ?></span>
                    </a>
                    
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn--secondary btn--sm header-account-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Account
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( wp_login_url( home_url( '/account/' ) ) ); ?>" class="btn btn--secondary btn--sm header-login-btn">Sign In</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--primary">View All Books</a>
                </div>
            </nav>
            
            <button class="mobile-menu-toggle" aria-label="Open menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation -->
<nav class="mobile-nav" id="mobile-nav" aria-label="Mobile navigation">
    <button class="mobile-nav__close" aria-label="Close menu">&times;</button>
    <ul class="mobile-nav__menu">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
        <li><a href="<?php echo esc_url( home_url( '/books/' ) ); ?>">Books</a></li>
        <li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Events</a></li>
        <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
        <li><a href="<?php echo esc_url( home_url( '/cart/' ) ); ?>">Cart<?php if ( $cart_count > 0 ) : ?> (<?php echo esc_html( $cart_count ); ?>)<?php endif; ?></a></li>
        <?php if ( is_user_logged_in() ) : ?>
            <li><a href="<?php echo esc_url( home_url( '/account/' ) ); ?>">My Account</a></li>
            <li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Sign Out</a></li>
        <?php else : ?>
            <li><a href="<?php echo esc_url( wp_login_url( home_url( '/account/' ) ) ); ?>">Sign In</a></li>
        <?php endif; ?>
    </ul>
</nav>

<main id="primary" class="site-main">


