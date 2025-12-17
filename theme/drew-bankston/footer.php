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
                <h4>Newsletter</h4>
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
            <p>&copy; <?php echo date( 'Y' ); ?> Drew Bankston. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

