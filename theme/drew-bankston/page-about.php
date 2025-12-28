<?php
/**
 * Template Name: About Page
 */

get_header();
?>

<!-- About Hero -->
<section class="section">
    <div class="container">
        <div class="about-hero gsap-reveal">
            <div class="about-hero__image">
                <img src="<?php echo esc_url( DBT_URL . '/assets/images/author-drew-bankston-headshot.png' ); ?>" alt="Drew Bankston, Author">
            </div>
            <div class="about-hero__content">
                <h1>Drew Bankston</h1>
                <p class="about-hero__descriptor">Award-Winning Science Fiction & Fantasy Author</p>
                <div class="about-hero__bio">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Awards & Achievements -->
<section class="section reviews-awards">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">Recognition</p>
            <h2 class="section-header__title">Awards & Achievements</h2>
        </div>
        
        <?php
        // Collect awards from all books
        $all_books = new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => -1,
        ) );
        
        $all_awards = array();
        if ( $all_books->have_posts() ) {
            while ( $all_books->have_posts() ) {
                $all_books->the_post();
                $awards = get_post_meta( get_the_ID(), '_dbc_book_awards', true );
                if ( is_array( $awards ) ) {
                    foreach ( $awards as $award ) {
                        if ( ! empty( $award['name'] ) ) {
                            $all_awards[] = $award;
                        }
                    }
                }
            }
            wp_reset_postdata();
        }
        ?>
        
        <?php if ( ! empty( $all_awards ) ) : ?>
        <div class="awards-strip gsap-reveal">
            <?php foreach ( $all_awards as $award ) : ?>
            <div class="award-badge">
                <?php if ( ! empty( $award['badge_url'] ) ) : ?>
                    <img src="<?php echo esc_url( $award['badge_url'] ); ?>" alt="<?php echo esc_attr( $award['name'] ); ?>" class="award-badge__image">
                <?php else : ?>
                    <span class="award-badge--fallback" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                    </span>
                <?php endif; ?>
                <span class="award-badge__name"><?php echo esc_html( $award['name'] ); ?></span>
                <?php if ( ! empty( $award['year'] ) ) : ?>
                    <span class="award-badge__year"><?php echo esc_html( $award['year'] ); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="placeholder-notice gsap-reveal">
            <p>Awards will appear here once added to books in WordPress admin.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Callout Cards -->
<section class="section">
    <div class="container">
        <div class="contact-grid gsap-reveal">
            <!-- Screenplay Writing -->
            <div class="callout-card">
                <div class="callout-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
                </div>
                <h3 class="callout-card__title">Screenplay Writing</h3>
                <p class="callout-card__description">Interested in hiring Drew for screenplay writing or film adaptation projects? For film and television inquiries, please contact:</p>
                <a href="mailto:movies@drewbankston.com" class="callout-card__email">movies@drewbankston.com</a>
            </div>
            
            <!-- Business & Booking -->
            <div class="callout-card">
                <div class="callout-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h3 class="callout-card__title">Business & Booking Inquiries</h3>
                <p class="callout-card__description">For business questions, speaking engagements, and booking inquiries, please contact Drew's business manager:</p>
                <a href="mailto:christi@drewbankston.com" class="callout-card__email">christi@drewbankston.com</a>
            </div>
        </div>
    </div>
</section>

<!-- Books CTA -->
<section class="section newsletter-section">
    <div class="container">
        <div class="section-header gsap-reveal">
            <h2 class="section-header__title">Explore Drew's Books</h2>
            <p class="section-header__description">From epic space operas to gripping true crime – discover stories that will transport you.</p>
        </div>
        <div class="series-cta gsap-reveal">
            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--primary btn--lg">View All Books</a>
        </div>
    </div>
</section>

<?php
get_footer();


