<?php
/**
 * Template Name: Account Page
 * User account dashboard for managing profile, subscriptions, and purchases
 */

get_header();

// Redirect if not logged in
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get user meta
$newsletter_subscribed = get_user_meta( $user_id, 'dbc_newsletter_subscribed', true );
$downloaded_chapters = get_user_meta( $user_id, 'dbc_downloaded_chapters', true );
$purchased_books = get_user_meta( $user_id, 'dbc_purchased_books', true );

if ( ! is_array( $downloaded_chapters ) ) $downloaded_chapters = array();
if ( ! is_array( $purchased_books ) ) $purchased_books = array();

// Handle form submissions
$message = '';
$message_type = '';

if ( isset( $_POST['dbc_update_profile'] ) && wp_verify_nonce( $_POST['dbc_profile_nonce'], 'dbc_update_profile' ) ) {
    $first_name = sanitize_text_field( $_POST['first_name'] );
    $last_name = sanitize_text_field( $_POST['last_name'] );
    $display_name = sanitize_text_field( $_POST['display_name'] );
    
    wp_update_user( array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $display_name,
    ) );
    
    $message = 'Profile updated successfully!';
    $message_type = 'success';
    
    // Refresh user data
    $current_user = wp_get_current_user();
}

if ( isset( $_POST['dbc_update_subscription'] ) && wp_verify_nonce( $_POST['dbc_subscription_nonce'], 'dbc_update_subscription' ) ) {
    $newsletter = isset( $_POST['newsletter_subscribed'] ) ? '1' : '0';
    update_user_meta( $user_id, 'dbc_newsletter_subscribed', $newsletter );
    $newsletter_subscribed = $newsletter;
    
    $message = 'Subscription preferences updated!';
    $message_type = 'success';
}

if ( isset( $_POST['dbc_change_password'] ) && wp_verify_nonce( $_POST['dbc_password_nonce'], 'dbc_change_password' ) ) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ( ! wp_check_password( $current_password, $current_user->user_pass, $user_id ) ) {
        $message = 'Current password is incorrect.';
        $message_type = 'error';
    } elseif ( $new_password !== $confirm_password ) {
        $message = 'New passwords do not match.';
        $message_type = 'error';
    } elseif ( strlen( $new_password ) < 8 ) {
        $message = 'Password must be at least 8 characters.';
        $message_type = 'error';
    } else {
        wp_set_password( $new_password, $user_id );
        $message = 'Password changed successfully! Please log in again.';
        $message_type = 'success';
    }
}
?>

<main id="primary" class="site-main">
    <!-- Hero Section -->
    <section class="hero" style="min-height: 50vh;">
        <div class="hero__bg"></div>
        <div class="container">
            <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
                <h1 class="hero__title hero__title--typewriter" data-typewriter-text="My Account">
                    <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
                </h1>
                <p class="hero__subtitle">Welcome back, <?php echo esc_html( $current_user->display_name ); ?>!</p>
            </div>
        </div>
    </section>

    <section class="account-dashboard">
        <div class="container">
            <?php if ( $message ) : ?>
                <div class="account-message account-message--<?php echo esc_attr( $message_type ); ?>">
                    <?php echo esc_html( $message ); ?>
                </div>
            <?php endif; ?>

            <div class="account-grid">
                <!-- Sidebar Navigation -->
                <aside class="account-sidebar">
                    <nav class="account-nav">
                        <a href="#profile" class="account-nav__link is-active" data-tab="profile">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profile
                        </a>
                        <a href="#subscription" class="account-nav__link" data-tab="subscription">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            Newsletter
                        </a>
                        <a href="#downloads" class="account-nav__link" data-tab="downloads">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Free Chapters
                        </a>
                        <a href="#purchases" class="account-nav__link" data-tab="purchases">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            Purchases
                        </a>
                        <a href="#security" class="account-nav__link" data-tab="security">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Security
                        </a>
                    </nav>
                    
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="account-nav__logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Sign Out
                    </a>
                </aside>

                <!-- Main Content Area -->
                <div class="account-content">
                    <!-- Profile Tab -->
                    <div class="account-panel is-active" id="profile">
                        <h2 class="account-panel__title">Profile Information</h2>
                        <p class="account-panel__description">Update your personal information and display name.</p>
                        
                        <form method="post" class="account-form">
                            <?php wp_nonce_field( 'dbc_update_profile', 'dbc_profile_nonce' ); ?>
                            
                            <div class="account-form__row">
                                <div class="account-form__group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>">
                                </div>
                                <div class="account-form__group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>">
                                </div>
                            </div>
                            
                            <div class="account-form__group">
                                <label for="display_name">Display Name</label>
                                <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $current_user->display_name ); ?>">
                                <p class="account-form__hint">This is how your name will appear across the site.</p>
                            </div>
                            
                            <div class="account-form__group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" disabled>
                                <p class="account-form__hint">Contact us to change your email address.</p>
                            </div>
                            
                            <button type="submit" name="dbc_update_profile" class="btn btn--primary">Save Changes</button>
                        </form>
                    </div>

                    <!-- Subscription Tab -->
                    <div class="account-panel" id="subscription">
                        <h2 class="account-panel__title">Newsletter Preferences</h2>
                        <p class="account-panel__description">Manage your email subscription and communication preferences.</p>
                        
                        <form method="post" class="account-form">
                            <?php wp_nonce_field( 'dbc_update_subscription', 'dbc_subscription_nonce' ); ?>
                            
                            <div class="account-form__checkbox-group">
                                <label class="account-checkbox">
                                    <input type="checkbox" name="newsletter_subscribed" value="1" <?php checked( $newsletter_subscribed, '1' ); ?>>
                                    <span class="account-checkbox__mark"></span>
                                    <span class="account-checkbox__text">
                                        <strong>Drew Bankston Newsletter</strong>
                                        <span>Receive updates about new books, events, and exclusive content.</span>
                                    </span>
                                </label>
                            </div>
                            
                            <div class="account-form__info">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                <p>You can unsubscribe at any time. We respect your privacy and will never share your information.</p>
                            </div>
                            
                            <button type="submit" name="dbc_update_subscription" class="btn btn--primary">Update Preferences</button>
                        </form>
                    </div>

                    <!-- Downloads Tab -->
                    <div class="account-panel" id="downloads">
                        <h2 class="account-panel__title">Free Chapters</h2>
                        <p class="account-panel__description">Download free chapters you've unlocked.</p>
                        
                        <?php
                        // Get all books with free chapters
                        $books_with_chapters = get_posts( array(
                            'post_type' => 'book',
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => '_dbc_book_free_chapter',
                                    'value' => '',
                                    'compare' => '!='
                                )
                            )
                        ) );
                        
                        if ( ! empty( $books_with_chapters ) ) :
                        ?>
                        <div class="account-downloads">
                            <?php foreach ( $books_with_chapters as $book ) : 
                                $free_chapter = get_post_meta( $book->ID, '_dbc_book_free_chapter', true );
                                $cover_id = get_post_thumbnail_id( $book->ID );
                                $cover_url = $cover_id ? wp_get_attachment_image_url( $cover_id, 'medium' ) : '';
                                
                                // Generate download token for this user
                                $token = wp_hash( $current_user->user_email . $book->ID . 'user_download' );
                                set_transient( 'dbc_download_' . $token, array(
                                    'email'   => $current_user->user_email,
                                    'book_id' => $book->ID,
                                    'file'    => $free_chapter,
                                ), DAY_IN_SECONDS );
                                
                                $download_url = add_query_arg( 'dbc_download', $token, home_url( '/' ) );
                                
                                // Track download
                                if ( ! in_array( $book->ID, $downloaded_chapters ) ) {
                                    $downloaded_chapters[] = $book->ID;
                                    update_user_meta( $user_id, 'dbc_downloaded_chapters', $downloaded_chapters );
                                }
                            ?>
                            <div class="account-download-item">
                                <?php if ( $cover_url ) : ?>
                                    <img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( $book->post_title ); ?>" class="account-download-item__cover">
                                <?php endif; ?>
                                <div class="account-download-item__info">
                                    <h3><?php echo esc_html( $book->post_title ); ?></h3>
                                    <p>Free Chapter • PDF</p>
                                </div>
                                <a href="<?php echo esc_url( $download_url ); ?>" class="btn btn--secondary btn--sm" download>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Download
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else : ?>
                        <div class="account-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <p>No free chapters available yet.</p>
                            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--primary">Browse Books</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Purchases Tab -->
                    <div class="account-panel" id="purchases">
                        <h2 class="account-panel__title">Purchase History</h2>
                        <p class="account-panel__description">View your book purchases and order history.</p>
                        
                        <?php if ( ! empty( $purchased_books ) ) : ?>
                        <div class="account-purchases">
                            <?php foreach ( $purchased_books as $purchase ) : 
                                $book = get_post( $purchase['book_id'] );
                                if ( ! $book ) continue;
                                $cover_id = get_post_thumbnail_id( $book->ID );
                                $cover_url = $cover_id ? wp_get_attachment_image_url( $cover_id, 'medium' ) : '';
                            ?>
                            <div class="account-purchase-item">
                                <?php if ( $cover_url ) : ?>
                                    <img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( $book->post_title ); ?>" class="account-purchase-item__cover">
                                <?php endif; ?>
                                <div class="account-purchase-item__info">
                                    <h3><?php echo esc_html( $book->post_title ); ?></h3>
                                    <p>Purchased: <?php echo esc_html( date( 'F j, Y', strtotime( $purchase['date'] ) ) ); ?></p>
                                    <p class="account-purchase-item__format"><?php echo esc_html( $purchase['format'] ?? 'eBook' ); ?></p>
                                </div>
                                <span class="account-purchase-item__price">$<?php echo esc_html( number_format( $purchase['amount'] ?? 0, 2 ) ); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else : ?>
                        <div class="account-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <p>No purchases yet.</p>
                            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--primary">Shop Books</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Security Tab -->
                    <div class="account-panel" id="security">
                        <h2 class="account-panel__title">Security Settings</h2>
                        <p class="account-panel__description">Update your password and security preferences.</p>
                        
                        <form method="post" class="account-form">
                            <?php wp_nonce_field( 'dbc_change_password', 'dbc_password_nonce' ); ?>
                            
                            <div class="account-form__group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="account-form__row">
                                <div class="account-form__group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required minlength="8">
                                </div>
                                <div class="account-form__group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                                </div>
                            </div>
                            
                            <p class="account-form__hint">Password must be at least 8 characters long.</p>
                            
                            <button type="submit" name="dbc_change_password" class="btn btn--primary">Change Password</button>
                        </form>
                        
                        <hr class="account-divider">
                        
                        <div class="account-danger-zone">
                            <h3>Danger Zone</h3>
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                            <button type="button" class="btn btn--danger" id="delete-account-btn">Delete Account</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const navLinks = document.querySelectorAll('.account-nav__link');
    const panels = document.querySelectorAll('.account-panel');
    
    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            
            // Update active states
            navLinks.forEach(function(l) { l.classList.remove('is-active'); });
            panels.forEach(function(p) { p.classList.remove('is-active'); });
            
            this.classList.add('is-active');
            document.getElementById(tabId).classList.add('is-active');
            
            // Update URL hash
            history.pushState(null, null, '#' + tabId);
        });
    });
    
    // Handle initial hash
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const link = document.querySelector('[data-tab="' + hash + '"]');
        if (link) link.click();
    }
    
    // Delete account confirmation
    const deleteBtn = document.getElementById('delete-account-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                if (confirm('This will permanently delete all your data. Type "DELETE" to confirm.')) {
                    // Would handle account deletion here
                    alert('Please contact support to delete your account.');
                }
            }
        });
    }
});
</script>

<?php get_footer(); ?>

