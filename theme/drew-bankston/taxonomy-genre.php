<?php
/**
 * Genre Taxonomy Archive
 */

get_header();

$term = get_queried_object();
?>

<!-- Genre Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <p class="hero__eyebrow">Genre</p>
            <h1 class="hero__title"><?php single_term_title(); ?></h1>
            <?php if ( term_description() ) : ?>
                <p class="hero__subtitle"><?php echo term_description(); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="books-grid gsap-reveal">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post();
                    $tagline = get_post_meta( get_the_ID(), '_dbc_book_tagline', true );
                    $series = dbt_get_book_series( get_the_ID() );
                    $series_label = '';
                    if ( $series ) {
                        $series_label = $series->slug === 'standalones' ? 'Standalone' : $series->name;
                    }
                ?>
                <article class="book-card">
                    <div class="book-card__cover">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'book-cover' ); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ( $series_label ) : ?>
                            <span class="book-card__series-badge"><?php echo esc_html( $series_label ); ?></span>
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
                <?php endwhile; ?>
            <?php else : ?>
                <div class="placeholder-notice">
                    <p>No books found in this genre.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="series-cta gsap-reveal" style="margin-top: var(--space-12);">
            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--secondary">View All Books</a>
        </div>
    </div>
</section>

<?php
get_footer();

