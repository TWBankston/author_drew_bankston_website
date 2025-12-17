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
                <h1 class="site-title">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Drew Bankston</a>
                </h1>
                <p class="site-tagline">Award-Winning Science Fiction & Fantasy Author</p>
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
    </ul>
</nav>

<main id="primary" class="site-main">

