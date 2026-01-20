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
                <h4>Community</h4>
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
            <p class="modal__subtitle">Join the community and get a free chapter of <strong id="modal-book-title"></strong>!</p>
            
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

<?php wp_footer(); ?>
</body>
</html>


