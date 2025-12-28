<?php
/**
 * Series Taxonomy Archive
 */

get_header();

$term = get_queried_object();
$primary_genre = get_term_meta( $term->term_id, 'primary_genre', true );
?>

<!-- Series Hero -->
<section class="hero" style="min-height: 60vh;">
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
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <p class="hero__eyebrow">Book Series</p>
            <h1 class="hero__title"><?php single_term_title(); ?></h1>
            <?php if ( $primary_genre ) : ?>
                <div class="hero__genres" style="justify-content: center;">
                    <span class="hero__genre-tag"><?php echo esc_html( $primary_genre ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( term_description() ) : ?>
                <p class="hero__subtitle"><?php echo term_description(); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header gsap-reveal">
            <p class="section-header__eyebrow">Reading Order</p>
            <h2 class="section-header__title">Books in This Series</h2>
        </div>
        
        <?php
        $books = new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'series',
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                ),
            ),
            'meta_key'       => '_dbc_book_series_order',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ) );
        
        if ( $books->have_posts() ) :
        ?>
        <div class="series-books gsap-reveal" style="max-width: none;">
            <?php while ( $books->have_posts() ) : $books->the_post(); 
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
                    <a href="<?php the_permalink(); ?>" class="book-card__link">Learn More →</a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        
        <?php 
        // Show "Start the Series" CTA for first book
        $books->rewind_posts();
        if ( $books->have_posts() ) : $books->the_post();
        ?>
        <div class="series-cta gsap-reveal" style="margin-top: var(--space-12);">
            <a href="<?php the_permalink(); ?>" class="btn btn--primary btn--lg">Start the Series</a>
        </div>
        <?php endif; wp_reset_postdata(); ?>
        
        <?php else : ?>
        <div class="placeholder-notice gsap-reveal">
            <p>No books in this series yet.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();


