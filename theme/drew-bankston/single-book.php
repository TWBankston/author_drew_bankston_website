<?php
/**
 * Single Book Template
 */

get_header();

while ( have_posts() ) : the_post();
    $tagline      = get_post_meta( get_the_ID(), '_dbc_book_tagline', true );
    $subtitle     = get_post_meta( get_the_ID(), '_dbc_book_subtitle', true );
    $page_count   = get_post_meta( get_the_ID(), '_dbc_book_page_count', true );
    $pub_date     = get_post_meta( get_the_ID(), '_dbc_book_pub_date', true );
    $isbn_print   = get_post_meta( get_the_ID(), '_dbc_book_isbn_print', true );
    $audience     = get_post_meta( get_the_ID(), '_dbc_book_audience', true );
    $formats      = get_post_meta( get_the_ID(), '_dbc_book_formats', true );
    $series_order = get_post_meta( get_the_ID(), '_dbc_book_series_order', true );
    $amazon_url   = get_post_meta( get_the_ID(), '_dbc_book_amazon_url', true );
    $reviews      = get_post_meta( get_the_ID(), '_dbc_book_reviews', true );
    $awards       = get_post_meta( get_the_ID(), '_dbc_book_awards', true );
    
    $series = dbt_get_book_series( get_the_ID() );
    $genre_display = dbt_get_book_genre( get_the_ID() );
    $genres = get_the_terms( get_the_ID(), 'genre' );
    
    $series_label = '';
    if ( $series ) {
        if ( $series->slug === 'standalones' ) {
            $series_label = dbt_get_standalone_label( get_the_ID() );
        } else {
            $series_label = $series->name;
            if ( $series_order ) {
                $series_label .= ' - Book ' . $series_order;
            }
        }
    }
?>

<!-- Book Hero -->
<section class="section" style="background: var(--color-bg-secondary);">
    <div class="container">
        <div class="book-hero gsap-reveal">
            <div class="book-hero__cover">
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'book-cover-large' ); ?>
                <?php endif; ?>
            </div>
            
            <div class="book-hero__info">
                <?php if ( $series_label ) : ?>
                    <span class="book-hero__series-label"><?php echo esc_html( $series_label ); ?></span>
                <?php endif; ?>
                
                <h1 class="book-hero__title"><?php the_title(); ?></h1>
                
                <?php if ( $tagline ) : ?>
                    <p class="book-hero__tagline"><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>
                
                <?php if ( $genres && ! is_wp_error( $genres ) ) : ?>
                <div class="book-hero__genres">
                    <?php foreach ( $genres as $g ) : ?>
                        <span class="book-hero__genre-tag"><?php echo esc_html( $g->name ); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="book-hero__cta">
                    <?php if ( $amazon_url ) : ?>
                        <a href="<?php echo esc_url( $amazon_url ); ?>" target="_blank" rel="noopener" class="btn btn--primary btn--lg">Buy on Amazon</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--secondary btn--lg">All Books</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Book Details -->
<section class="section book-details">
    <div class="container">
        <div class="book-details__grid gsap-reveal">
            <div class="book-details__content">
                <h2>About This Book</h2>
                <div class="book-details__description">
                    <?php the_content(); ?>
                </div>
            </div>
            
            <aside class="book-details__meta">
                <h3>Book Details</h3>
                <dl class="book-details__meta-list">
                    <?php if ( $series && $series->slug !== 'standalones' ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Series</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( $series->name ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $genre_display ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Genre</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( $genre_display ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $audience ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Audience</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( ucwords( str_replace( '-', ' ', $audience ) ) ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $page_count ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Pages</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( $page_count ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $pub_date ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Published</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( date( 'F j, Y', strtotime( $pub_date ) ) ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $isbn_print ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">ISBN</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( $isbn_print ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $formats ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Formats</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( $formats ); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</section>

<!-- Reviews & Awards -->
<?php if ( ( is_array( $reviews ) && ! empty( $reviews ) ) || ( is_array( $awards ) && ! empty( $awards ) ) ) : ?>
<section class="section reviews-awards">
    <div class="container">
        <div class="section-header gsap-reveal">
            <h2 class="section-header__title">Reviews & Awards</h2>
        </div>
        
        <?php if ( is_array( $reviews ) && ! empty( $reviews ) ) : ?>
        <div class="reviews-grid gsap-reveal">
            <?php foreach ( $reviews as $review ) : if ( empty( $review['quote'] ) ) continue; ?>
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
                </cite>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ( is_array( $awards ) && ! empty( $awards ) ) : ?>
        <div class="awards-strip gsap-reveal">
            <?php foreach ( $awards as $award ) : if ( empty( $award['name'] ) ) continue; ?>
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
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Related Books -->
<?php
$related_args = array(
    'post_type'      => 'book',
    'posts_per_page' => 3,
    'post__not_in'   => array( get_the_ID() ),
);

if ( $series && $series->slug !== 'standalones' ) {
    $related_args['tax_query'] = array(
        array(
            'taxonomy' => 'series',
            'field'    => 'slug',
            'terms'    => $series->slug,
        ),
    );
} else {
    $related_args['orderby'] = 'rand';
}

$related_books = new WP_Query( $related_args );

if ( $related_books->have_posts() ) :
?>
<section class="section related-books">
    <div class="container">
        <div class="section-header gsap-reveal">
            <h2 class="section-header__title">
                <?php echo $series && $series->slug !== 'standalones' ? 'More in This Series' : 'You May Also Like'; ?>
            </h2>
        </div>
        
        <div class="books-grid books-grid--3 gsap-reveal">
            <?php while ( $related_books->have_posts() ) : $related_books->the_post(); 
                $rel_tagline = get_post_meta( get_the_ID(), '_dbc_book_tagline', true );
            ?>
            <article class="book-card">
                <div class="book-card__cover">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'book-cover' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="book-card__content">
                    <h3 class="book-card__title"><?php the_title(); ?></h3>
                    <?php if ( $rel_tagline ) : ?>
                        <p class="book-card__tagline"><?php echo esc_html( $rel_tagline ); ?></p>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="book-card__link">Learn More →</a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php endwhile; ?>

<?php
get_footer();

