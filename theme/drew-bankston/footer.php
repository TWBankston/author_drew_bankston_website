</main><!-- #primary -->

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/books/' ) ); ?>">Books</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Events</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Join the Tokorel Family</h4>
                <p class="text-muted" style="font-size: var(--text-sm); margin-bottom: var(--space-2);">Get updates on new releases and exclusive content.</p>
                <form class="footer-newsletter" action="#" method="post">
                    <label class="sr-only" for="footer-email">Email Address</label>
                    <input type="email" id="footer-email" name="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
                <p class="text-muted" style="font-size: var(--text-xs); margin-top: var(--space-2);">No spam, unsubscribe anytime.</p>
            </div>
            
            <div class="footer-column">
                <h4>Connect</h4>
                <div class="footer-social">
                    <?php 
                    $socials = dbt_get_social_links();
                    foreach ( $socials as $key => $social ) :
                    ?>
                    <a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
                        <?php echo dbt_get_social_icon( $key ); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logos/horizontal light logo-long.png' ); ?>" alt="Drew Bankston - Author" class="footer-logo__image">
            </a>
            <p>&copy; <?php echo date( 'Y' ); ?> Drew Bankston. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Free Chapter Modal -->
<div class="modal" id="free-chapter-modal" aria-hidden="true" role="dialog" aria-labelledby="modal-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__container">
        <button class="modal__close" data-modal-close aria-label="Close modal">&times;</button>
        
        <div class="modal__content" id="modal-form-state">
            <div class="modal__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h2 class="modal__title" id="modal-title">Get Your Free Chapter</h2>
            <p class="modal__subtitle">Join the Tokorel Family and get a free chapter of <strong id="modal-book-title"></strong>!</p>
            
            <form class="modal__form" id="free-chapter-form">
                <input type="hidden" id="modal-book-id" name="book_id" value="">
                
                <div class="modal__form-row">
                    <div class="modal__form-group">
                        <label for="modal-first-name" class="modal__label">First Name</label>
                        <input type="text" id="modal-first-name" name="first_name" class="modal__input" placeholder="First name" required>
                    </div>
                    
                    <div class="modal__form-group">
                        <label for="modal-last-name" class="modal__label">Last Name</label>
                        <input type="text" id="modal-last-name" name="last_name" class="modal__input" placeholder="Last name" required>
                    </div>
                </div>
                
                <div class="modal__form-group">
                    <label for="modal-email" class="modal__label">Email Address</label>
                    <input type="email" id="modal-email" name="email" class="modal__input" placeholder="Enter your email" required>
                </div>
                
                <button type="submit" class="btn btn--primary btn--lg modal__submit">
                    <span class="modal__submit-text">Get Free Chapter</span>
                    <span class="modal__submit-loading" style="display: none;">Sending...</span>
                </button>
                
                <p class="modal__privacy">By signing up, you agree to receive occasional emails from Drew Bankston. You can unsubscribe at any time.</p>
            </form>
        </div>
        
        <div class="modal__content modal__success" id="modal-success-state" style="display: none;">
            <div class="modal__icon modal__icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h2 class="modal__title">You're All Set!</h2>
            <p class="modal__subtitle">Thank you for subscribing. Your free chapter is ready to download.</p>
            <a href="#" id="modal-download-link" class="btn btn--primary btn--lg" download>
                Download Free Chapter
            </a>
            <button class="btn btn--secondary" data-modal-close style="margin-top: var(--space-4);">Close</button>
        </div>
        
        <div class="modal__content modal__error" id="modal-error-state" style="display: none;">
            <div class="modal__icon modal__icon--error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h2 class="modal__title">Oops!</h2>
            <p class="modal__subtitle" id="modal-error-message">Something went wrong. Please try again.</p>
            <button class="btn btn--primary" id="modal-try-again">Try Again</button>
        </div>
    </div>
</div>

<!-- Welcome New Member Popup -->
<?php 
// Check if we should show the welcome popup (for logged-in users who just signed up)
$show_welcome_popup = false;
$welcome_download_url = '';

if ( is_user_logged_in() ) {
    $user_id = get_current_user_id();
    $show_welcome = get_user_meta( $user_id, 'dbc_show_welcome_popup', true );
    
    if ( $show_welcome === '1' ) {
        $show_welcome_popup = true;
        // Clear the flag so it only shows once
        delete_user_meta( $user_id, 'dbc_show_welcome_popup' );
        
        // Get download URL for the free short story
        $welcome_download_url = DBC_Newsletter::get_member_content_download_url( 'the-t-shirt' );
    }
}

// Also check for newsletter signup popup (cookie-based for non-logged-in users)
$show_newsletter_welcome = false;
if ( isset( $_COOKIE['dbc_show_newsletter_welcome'] ) && $_COOKIE['dbc_show_newsletter_welcome'] === '1' ) {
    $show_newsletter_welcome = true;
    // Clear the cookie
    setcookie( 'dbc_show_newsletter_welcome', '', time() - 3600, '/' );
}
?>
<div class="modal" id="welcome-member-modal" aria-hidden="<?php echo ( $show_welcome_popup || $show_newsletter_welcome ) ? 'false' : 'true'; ?>" role="dialog" aria-labelledby="welcome-modal-title" <?php if ( $show_welcome_popup || $show_newsletter_welcome ) echo 'style="display: flex;"'; ?>>
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__container welcome-modal">
        <button class="modal__close" data-modal-close aria-label="Close modal">&times;</button>
        
        <div class="modal__content">
            <!-- Confetti/Celebration Icon -->
            <div class="welcome-modal__celebration">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="64" height="64">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="rgba(167, 139, 250, 0.2)" stroke="currentColor"/>
                </svg>
            </div>
            
            <h2 class="modal__title welcome-modal__title" id="welcome-modal-title">Welcome to the Tokorel Family!</h2>
            <p class="modal__subtitle welcome-modal__subtitle">Thank you for joining! As a member, you've unlocked some exclusive perks.</p>
            
            <!-- Free Short Story Section -->
            <div class="welcome-modal__perk">
                <div class="welcome-modal__perk-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <div class="welcome-modal__perk-content">
                    <h3>Free Short Story: "The T-Shirt"</h3>
                    <p>Enjoy this exclusive short story by Drew Bankston, available now in your <a href="<?php echo esc_url( home_url( '/account/#downloads' ) ); ?>">My Account</a> page.</p>
                </div>
            </div>
            
            <?php if ( $welcome_download_url ) : ?>
            <a href="<?php echo esc_url( $welcome_download_url ); ?>" class="btn btn--primary btn--lg welcome-modal__download" download>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download "The T-Shirt" Now
            </a>
            <?php endif; ?>
            
            <!-- Discount Code Section -->
            <div class="welcome-modal__discount">
                <div class="welcome-modal__discount-badge">
                    <span class="welcome-modal__discount-percent">10% OFF</span>
                    <span class="welcome-modal__discount-label">New Member Discount</span>
                </div>
                <div class="welcome-modal__discount-content">
                    <p>Use code <strong class="welcome-modal__code">NEW10</strong> at checkout for 10% off your first purchase!</p>
                    <p class="welcome-modal__discount-note">Valid for 30 days • One-time use</p>
                </div>
            </div>
            
            <div class="welcome-modal__actions">
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--secondary">Browse Books</a>
                <button class="btn btn--ghost" data-modal-close>Close</button>
            </div>
            
            <p class="welcome-modal__saved-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Your free content is saved in <a href="<?php echo esc_url( home_url( '/account/#downloads' ) ); ?>">My Account</a>
            </p>
        </div>
    </div>
</div>

<style>
/* Welcome Modal Styles */
.welcome-modal {
    max-width: 520px;
}

.welcome-modal__celebration {
    color: var(--color-accent-lavender, #a78bfa);
    margin-bottom: var(--space-4, 1rem);
    animation: celebrationPulse 2s ease-in-out infinite;
}

@keyframes celebrationPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.welcome-modal__title {
    color: var(--color-text-primary, #fff);
    margin-bottom: var(--space-2, 0.5rem);
}

.welcome-modal__subtitle {
    color: var(--color-text-secondary, #94a3b8);
    margin-bottom: var(--space-6, 1.5rem);
}

.welcome-modal__perk {
    display: flex;
    align-items: flex-start;
    gap: var(--space-4, 1rem);
    background: rgba(167, 139, 250, 0.1);
    border: 1px solid rgba(167, 139, 250, 0.2);
    border-radius: var(--radius-lg, 12px);
    padding: var(--space-4, 1rem);
    margin-bottom: var(--space-4, 1rem);
    text-align: left;
}

.welcome-modal__perk-icon {
    flex-shrink: 0;
    color: var(--color-accent-lavender, #a78bfa);
}

.welcome-modal__perk-content h3 {
    font-size: var(--text-base, 1rem);
    font-weight: 600;
    color: var(--color-text-primary, #fff);
    margin-bottom: var(--space-1, 0.25rem);
}

.welcome-modal__perk-content p {
    font-size: var(--text-sm, 0.875rem);
    color: var(--color-text-secondary, #94a3b8);
    margin: 0;
}

.welcome-modal__perk-content a {
    color: var(--color-accent-lavender, #a78bfa);
    text-decoration: underline;
}

.welcome-modal__download {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2, 0.5rem);
    margin-bottom: var(--space-4, 1rem);
}

.welcome-modal__discount {
    display: flex;
    align-items: center;
    gap: var(--space-4, 1rem);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: var(--radius-lg, 12px);
    padding: var(--space-4, 1rem);
    margin-bottom: var(--space-5, 1.25rem);
    text-align: left;
}

.welcome-modal__discount-badge {
    flex-shrink: 0;
    background: linear-gradient(135deg, #22c55e 0%, #10b981 100%);
    border-radius: var(--radius-md, 8px);
    padding: var(--space-2, 0.5rem) var(--space-3, 0.75rem);
    text-align: center;
}

.welcome-modal__discount-percent {
    display: block;
    font-size: var(--text-lg, 1.125rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}

.welcome-modal__discount-label {
    display: block;
    font-size: var(--text-xs, 0.75rem);
    color: rgba(255, 255, 255, 0.9);
}

.welcome-modal__discount-content p {
    font-size: var(--text-sm, 0.875rem);
    color: var(--color-text-secondary, #94a3b8);
    margin: 0 0 var(--space-1, 0.25rem) 0;
}

.welcome-modal__code {
    display: inline-block;
    background: rgba(255, 255, 255, 0.1);
    border: 1px dashed rgba(34, 197, 94, 0.5);
    padding: var(--space-1, 0.25rem) var(--space-2, 0.5rem);
    border-radius: var(--radius-sm, 4px);
    font-family: monospace;
    font-size: var(--text-base, 1rem);
    color: #22c55e;
    letter-spacing: 0.05em;
}

.welcome-modal__discount-note {
    font-size: var(--text-xs, 0.75rem) !important;
    color: var(--color-text-muted, #64748b) !important;
}

.welcome-modal__actions {
    display: flex;
    gap: var(--space-3, 0.75rem);
    justify-content: center;
    margin-bottom: var(--space-4, 1rem);
}

.welcome-modal__saved-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2, 0.5rem);
    font-size: var(--text-xs, 0.75rem);
    color: var(--color-text-muted, #64748b);
}

.welcome-modal__saved-note svg {
    color: #22c55e;
}

.welcome-modal__saved-note a {
    color: var(--color-accent-lavender, #a78bfa);
}

/* Button ghost style if not defined */
.btn--ghost {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--color-text-secondary, #94a3b8);
}

.btn--ghost:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.3);
    color: var(--color-text-primary, #fff);
}

/* Account page additional styles */
.account-section {
    margin-bottom: var(--space-6, 1.5rem);
}

.account-section__title {
    display: flex;
    align-items: center;
    gap: var(--space-2, 0.5rem);
    font-size: var(--text-lg, 1.125rem);
    font-weight: 600;
    color: var(--color-text-primary, #fff);
    margin-bottom: var(--space-2, 0.5rem);
}

.account-section__description {
    font-size: var(--text-sm, 0.875rem);
    color: var(--color-text-secondary, #94a3b8);
    margin-bottom: var(--space-4, 1rem);
}

.account-download-item--featured {
    position: relative;
    background: linear-gradient(135deg, rgba(167, 139, 250, 0.1) 0%, rgba(99, 102, 241, 0.05) 100%);
    border: 1px solid rgba(167, 139, 250, 0.3);
    flex-direction: column;
    text-align: center;
    padding: var(--space-6, 1.5rem);
}

.account-download-item__badge {
    position: absolute;
    top: var(--space-3, 0.75rem);
    right: var(--space-3, 0.75rem);
    display: flex;
    align-items: center;
    gap: var(--space-1, 0.25rem);
    background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
    color: #fff;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 600;
    padding: var(--space-1, 0.25rem) var(--space-2, 0.5rem);
    border-radius: var(--radius-full, 9999px);
}

.account-download-item__icon {
    color: var(--color-accent-lavender, #a78bfa);
    margin-bottom: var(--space-3, 0.75rem);
}

.account-download-item__format {
    display: block;
    font-size: var(--text-xs, 0.75rem);
    color: var(--color-text-muted, #64748b);
    margin-top: var(--space-1, 0.25rem);
}

.account-empty--small {
    padding: var(--space-6, 1.5rem);
}

.account-empty--small svg {
    display: none;
}
</style>

<?php wp_footer(); ?>
</body>
</html>


