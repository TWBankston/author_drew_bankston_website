<?php
/**
 * Front Page Template
 * 
 * Homepage with:
 * - Hero with genre tags (Sci-Fi/Space Opera, Fantasy, Non-Fiction/True Crime)
 * - Tokorel Series section (balanced 2-book layout, expandable for 3rd)
 * - Standalones with genre descriptors
 * - Meet the Author teaser
 * - Reviews & Awards section
 * - Newsletter signup
 */

get_header();
?>

<!-- DEPLOY TEST: v2.1.0 - <?php echo date('Y-m-d H:i:s'); ?> -->

<!-- Hero Section -->
<section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__lottie-bg" aria-hidden="true">
        <lottie-player 
            src="<?php echo esc_url( DBT_URL . '/assets/lottie/cosmic-particles.json' ); ?>"
            background="transparent"
            speed="0.4"
            loop="true"
            autoplay="true">
        </lottie-player>
    </div>
    
    <div class="container">
        <div class="hero__content gsap-reveal">
            <p class="hero__eyebrow">Colorado Author</p>
            <h1 class="hero__title">Drew Bankston</h1>
            <p class="hero__subtitle">Award-winning author crafting immersive worlds where science fiction meets human emotion, and fantasy ignites the imagination.</p>
            
            <div class="hero__genres">
                <span class="hero__genre-tag">Sci-Fi / Space Opera</span>
                <span class="hero__genre-tag">Fantasy</span>
                <span class="hero__genre-tag">Non-Fiction / True Crime</span>
            </div>
            
            <div class="hero__cta">
                <a href="<?php echo esc_url( home_url( '/series/tokorel/' ) ); ?>" class="btn btn--primary btn--lg">Explore Tokorel Series</a>
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--secondary btn--lg">View All Books</a>
            </div>
        </div>
    </div>
</section>

<!-- Section Divider -->
<div class="section-divider" aria-hidden="true">
    <lottie-player 
        src="<?php echo esc_url( DBT_URL . '/assets/lottie/constellation-line.json' ); ?>"
        background="transparent"
        speed="0.3"
        loop="true"
        autoplay="true">
    </lottie-player>
</div>

<!-- Tokorel Series Section -->
<section class="section section--lg series-section">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">Featured Series</p>
            <h2 class="section-header__title">The Tokorel Series</h2>
            <p class="series-header__genre">Sci-Fi / Space Opera</p>
        </div>
        
        <p class="series-description gsap-reveal">
            An epic space opera journey spanning galaxies and generations. Follow the crew of the Tokorel as they navigate interstellar politics, ancient mysteries, and the bonds that define humanity across the stars.
        </p>
        
        <?php
        // Get Tokorel series books
        $tokorel_books = new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => 3, // Room for future Book 3
            'tax_query'      => array(
                array(
                    'taxonomy' => 'series',
                    'field'    => 'slug',
                    'terms'    => 'tokorel',
                ),
            ),
            'meta_key'       => '_dbc_book_series_order',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ) );
        
        if ( $tokorel_books->have_posts() ) :
        ?>
        <div class="series-books gsap-reveal">
            <?php while ( $tokorel_books->have_posts() ) : $tokorel_books->the_post(); 
                $series_order = get_post_meta( get_the_ID(), '_dbc_book_series_order', true );
                $tagline = get_post_meta( get_the_ID(), '_dbc_book_tagline', true );
            ?>
            <article class="book-card">
                <div class="book-card__cover">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'book-cover' ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( $series_order ) : ?>
                        <span class="book-card__series-badge">Book <?php echo esc_html( $series_order ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="book-card__content">
                    <h3 class="book-card__title"><?php the_title(); ?></h3>
                    <?php if ( $tagline ) : ?>
                        <p class="book-card__tagline"><?php echo esc_html( $tagline ); ?></p>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="book-card__link">
                        Learn More 
                        <span class="cta-arrow" aria-hidden="true">→</span>
                    </a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>
        
        <div class="series-cta gsap-reveal">
            <a href="<?php echo esc_url( home_url( '/series/tokorel/' ) ); ?>" class="btn btn--outline">Explore the Full Series</a>
        </div>
    </div>
</section>

<!-- Standalone Books Section -->
<section class="section section--lg">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">More Books</p>
            <h2 class="section-header__title">Standalone Works</h2>
            <p class="section-header__description">Individual stories spanning fantasy, adventure, and true crime – each a complete journey unto itself.</p>
        </div>
        
        <?php
        // Get standalone books
        $standalone_books = new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => 6,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'series',
                    'field'    => 'slug',
                    'terms'    => 'standalones',
                ),
            ),
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ) );
        
        if ( $standalone_books->have_posts() ) :
        ?>
        <div class="standalones-grid gsap-reveal">
            <?php while ( $standalone_books->have_posts() ) : $standalone_books->the_post(); 
                $tagline = get_post_meta( get_the_ID(), '_dbc_book_tagline', true );
                $genre_display = dbt_get_book_genre( get_the_ID() );
                $standalone_label = ! empty( $genre_display ) ? 'Standalone ' . $genre_display : 'Standalone';
            ?>
            <article class="book-card standalone-card">
                <span class="standalone-card__genre-label"><?php echo esc_html( $standalone_label ); ?></span>
                <div class="book-card__cover">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'book-cover' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="book-card__content">
                    <h3 class="book-card__title"><?php the_title(); ?></h3>
                    <p class="book-card__genre"><?php echo esc_html( $genre_display ); ?></p>
                    <?php if ( $tagline ) : ?>
                        <p class="book-card__tagline"><?php echo esc_html( $tagline ); ?></p>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="book-card__link">
                        Learn More 
                        <span class="cta-arrow" aria-hidden="true">→</span>
                    </a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php else : ?>
        <div class="standalones-grid gsap-reveal">
            <!-- Placeholder cards when no books exist yet -->
            <div class="placeholder-notice">
                <p>Standalone books will appear here once added in WordPress admin.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Meet the Author Section -->
<section class="section section--lg" style="background: var(--color-bg-secondary);">
    <div class="container">
        <div class="author-teaser gsap-reveal">
            <div class="author-teaser__image">
                <img src="<?php echo esc_url( DBT_URL . '/assets/images/author-drew-bankston-headshot.png' ); ?>" alt="Drew Bankston, Author">
            </div>
            <div class="author-teaser__content">
                <p class="section-header__eyebrow">About the Author</p>
                <h2>Meet Drew Bankston</h2>
                <p class="author-teaser__bio">
                    Drew Bankston is an award-winning author from Colorado whose work spans the realms of science fiction, fantasy, and non-fiction. With a passion for crafting immersive worlds and complex characters, Drew brings stories to life that explore the depths of human experience against extraordinary backdrops.
                </p>
                <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="btn btn--secondary">Read Full Bio</a>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<?php
$upcoming_events = DBC_CPT_Event::get_upcoming_events( 3 );
if ( $upcoming_events->have_posts() ) :
?>
<section class="section section--lg">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">Coming Soon</p>
            <h2 class="section-header__title">Upcoming Events</h2>
            <p class="section-header__description">Meet Drew at book signings, readings, and conventions.</p>
        </div>
        
        <div class="events-timeline gsap-reveal">
            <?php while ( $upcoming_events->have_posts() ) : $upcoming_events->the_post(); 
                $start_datetime = get_post_meta( get_the_ID(), '_dbc_event_start_datetime', true );
                $location_name = get_post_meta( get_the_ID(), '_dbc_event_location_name', true );
                $event_type = get_post_meta( get_the_ID(), '_dbc_event_type', true );
                
                $event_date = strtotime( $start_datetime );
            ?>
            <article class="event-card">
                <div class="event-card__date">
                    <span class="event-card__date-day"><?php echo date( 'j', $event_date ); ?></span>
                    <span class="event-card__date-month"><?php echo date( 'M', $event_date ); ?></span>
                </div>
                <div class="event-card__content">
                    <?php if ( $event_type ) : ?>
                        <span class="event-card__type"><?php echo esc_html( ucwords( str_replace( '-', ' ', $event_type ) ) ); ?></span>
                    <?php endif; ?>
                    <h3><?php the_title(); ?></h3>
                    <?php if ( $location_name ) : ?>
                        <p class="event-card__location"><?php echo esc_html( $location_name ); ?></p>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="btn btn--sm btn--secondary">Details</a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        
        <div class="series-cta gsap-reveal" style="margin-top: var(--space-10);">
            <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="btn btn--outline">View All Events</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Reviews & Awards Section -->
<section class="section section--lg reviews-awards">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">Recognition</p>
            <h2 class="section-header__title">Reviews & Awards</h2>
            <p class="section-header__description">What readers and critics are saying about Drew's work.</p>
        </div>
        
        <?php
        // Get reviews from featured books
        $featured_books = new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => 5,
            'meta_query'     => array(
                array(
                    'key'     => '_dbc_book_reviews',
                    'compare' => 'EXISTS',
                ),
            ),
        ) );
        
        $all_reviews = array();
        $all_awards = array();
        
        if ( $featured_books->have_posts() ) {
            while ( $featured_books->have_posts() ) {
                $featured_books->the_post();
                $reviews = get_post_meta( get_the_ID(), '_dbc_book_reviews', true );
                $awards = get_post_meta( get_the_ID(), '_dbc_book_awards', true );
                
                if ( is_array( $reviews ) ) {
                    foreach ( $reviews as $review ) {
                        if ( ! empty( $review['quote'] ) ) {
                            $review['book_title'] = get_the_title();
                            $all_reviews[] = $review;
                        }
                    }
                }
                
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
        
        <?php if ( ! empty( $all_reviews ) ) : ?>
        <div class="reviews-grid gsap-reveal">
            <?php foreach ( array_slice( $all_reviews, 0, 3 ) as $review ) : ?>
            <div class="review-card">
                <blockquote class="review-card__quote">
                    <?php echo esc_html( $review['quote'] ); ?>
                </blockquote>
                <cite class="review-card__source">
                    <?php if ( ! empty( $review['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $review['url'] ); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html( $review['source'] ); ?>
                        </a>
                    <?php else : ?>
                        <?php echo esc_html( $review['source'] ); ?>
                    <?php endif; ?>
                    <?php if ( ! empty( $review['book_title'] ) ) : ?>
                        <span class="text-muted"> — on <?php echo esc_html( $review['book_title'] ); ?></span>
                    <?php endif; ?>
                </cite>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="placeholder-notice gsap-reveal">
            <p><strong>[PLACEHOLDER]</strong> Review quotes will appear here once added to books in WordPress admin.</p>
            <p>To add reviews: Edit a Book → Reviews & Awards section → Add review quotes with source and optional link.</p>
        </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $all_awards ) ) : ?>
        <div class="awards-strip gsap-reveal">
            <?php foreach ( $all_awards as $award ) : ?>
            <div class="award-badge">
                <?php if ( ! empty( $award['badge_url'] ) ) : ?>
                    <?php if ( ! empty( $award['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $award['url'] ); ?>" target="_blank" rel="noopener">
                            <img src="<?php echo esc_url( $award['badge_url'] ); ?>" alt="<?php echo esc_attr( $award['name'] ); ?>" class="award-badge__image">
                        </a>
                    <?php else : ?>
                        <img src="<?php echo esc_url( $award['badge_url'] ); ?>" alt="<?php echo esc_attr( $award['name'] ); ?>" class="award-badge__image">
                    <?php endif; ?>
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
        <?php elseif ( empty( $all_reviews ) ) : ?>
        <!-- Show awards placeholder only if reviews placeholder wasn't shown -->
        <?php else : ?>
        <div class="awards-strip gsap-reveal">
            <div class="placeholder-notice" style="width: 100%;">
                <p><strong>[PLACEHOLDER]</strong> Award badges will appear here once added to books.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Section -->
<section class="section section--lg newsletter-section">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">Stay Connected</p>
            <h2 class="section-header__title">Join the Newsletter</h2>
            <p class="section-header__description">Get exclusive updates on new releases, events, and behind-the-scenes content.</p>
        </div>
        
        <form class="newsletter-form gsap-reveal" action="#" method="post">
            <div class="newsletter-form__input-group">
                <label class="sr-only" for="newsletter-email">Email Address</label>
                <input type="email" id="newsletter-email" name="email" placeholder="Enter your email" required>
                <button type="submit" class="btn btn--primary">Subscribe</button>
            </div>
            <p class="newsletter-form__disclaimer">No spam, ever. Unsubscribe anytime.</p>
        </form>
    </div>
</section>

<?php
get_footer();

