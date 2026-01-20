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
                    <li class="has-dropdown">
                        <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" <?php echo is_post_type_archive( 'book' ) || is_singular( 'book' ) || is_page( 'upcoming-projects' ) ? 'aria-current="page"' : ''; ?>>Books</a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo esc_url( home_url( '/books/' ) ); ?>">All Books</a></li>
                            <li><a href="<?php echo esc_url( home_url( '/upcoming-projects/' ) ); ?>" <?php echo is_page( 'upcoming-projects' ) ? 'aria-current="page"' : ''; ?>>Upcoming Projects</a></li>
                        </ul>
                    </li>
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
                    
                    <?php 
                    // Redirect back to current page after login (or account page if on login/register)
                    $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $redirect_url = strpos( $current_url, 'wp-login.php' ) !== false ? home_url( '/account/' ) : $current_url;
                    $is_logged_in = is_user_logged_in();
                    ?>
                    <!-- Account button (shown when logged in) -->
                    <a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn--secondary btn--sm header-account-btn" id="header-account-btn" style="<?php echo $is_logged_in ? '' : 'display:none;'; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Account
                    </a>
                    <!-- Sign In button (shown when logged out) -->
                    <a href="<?php echo esc_url( wp_login_url( $redirect_url ) ); ?>" class="btn btn--secondary btn--sm header-login-btn" id="header-login-btn" style="<?php echo $is_logged_in ? 'display:none;' : ''; ?>">Sign In</a>
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
        <li class="mobile-has-submenu">
            <button class="mobile-submenu-toggle" aria-expanded="false">
                Books
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <ul class="mobile-submenu">
                <li><a href="<?php echo esc_url( home_url( '/books/' ) ); ?>">All Books</a></li>
                <li><a href="<?php echo esc_url( home_url( '/upcoming-projects/' ) ); ?>">Upcoming Projects</a></li>
            </ul>
        </li>
        <li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Events</a></li>
        <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
        <li><a href="<?php echo esc_url( home_url( '/cart/' ) ); ?>">Cart<?php if ( $cart_count > 0 ) : ?> (<?php echo esc_html( $cart_count ); ?>)<?php endif; ?></a></li>
        <!-- Mobile nav - logged in items -->
        <li class="mobile-nav-logged-in" style="<?php echo $is_logged_in ? '' : 'display:none;'; ?>"><a href="<?php echo esc_url( home_url( '/account/' ) ); ?>">My Account</a></li>
        <li class="mobile-nav-logged-in" style="<?php echo $is_logged_in ? '' : 'display:none;'; ?>"><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Sign Out</a></li>
        <!-- Mobile nav - logged out items -->
        <li class="mobile-nav-logged-out" style="<?php echo $is_logged_in ? 'display:none;' : ''; ?>"><a href="<?php echo esc_url( wp_login_url( $redirect_url ) ); ?>">Sign In</a></li>
    </ul>
</nav>

<!-- JavaScript to check login status and update nav -->
<script>
(function() {
    // PHP tells us the true login status (this runs fresh on each uncached page)
    var phpLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
    
    // Cookie helper functions
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null;
    }
    
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }
    
    function deleteCookie(name) {
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }
    
    // Sync cookie with PHP status (handles cache mismatches)
    if (phpLoggedIn) {
        setCookie('dbt_logged_in', '1', 14);
    } else {
        // PHP says logged out - clear cookie if it exists
        if (getCookie('dbt_logged_in')) {
            deleteCookie('dbt_logged_in');
        }
    }
    
    // Check login status - trust PHP first, then cookie
    function isLoggedIn() {
        // If PHP says logged in, trust it and ensure cookie is set
        if (phpLoggedIn) return true;
        // Otherwise check cookie (for cached pages)
        return getCookie('dbt_logged_in') === '1';
    }
    
    // Update nav based on login status
    function updateNavAuth() {
        var loggedIn = isLoggedIn();
        
        // Desktop nav
        var accountBtn = document.getElementById('header-account-btn');
        var loginBtn = document.getElementById('header-login-btn');
        
        if (accountBtn) accountBtn.style.display = loggedIn ? '' : 'none';
        if (loginBtn) loginBtn.style.display = loggedIn ? 'none' : '';
        
        // Mobile nav
        var mobileLoggedIn = document.querySelectorAll('.mobile-nav-logged-in');
        var mobileLoggedOut = document.querySelectorAll('.mobile-nav-logged-out');
        
        mobileLoggedIn.forEach(function(el) {
            el.style.display = loggedIn ? '' : 'none';
        });
        mobileLoggedOut.forEach(function(el) {
            el.style.display = loggedIn ? 'none' : '';
        });
    }
    
    // Run immediately
    updateNavAuth();
})();
</script>

<main id="primary" class="site-main">


