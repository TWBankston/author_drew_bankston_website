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
    // $formats field removed - was stored as array causing PHP warnings
    $series_order = get_post_meta( get_the_ID(), '_dbc_book_series_order', true );
    $amazon_url   = get_post_meta( get_the_ID(), '_dbc_book_amazon_url', true );
    $reviews      = get_post_meta( get_the_ID(), '_dbc_book_reviews', true );
    $awards       = get_post_meta( get_the_ID(), '_dbc_book_awards', true );
    $free_chapter = get_post_meta( get_the_ID(), '_dbc_book_free_chapter', true );
    
    // Purchase options
    $signed_enabled  = get_post_meta( get_the_ID(), '_dbc_book_signed_enabled', true );
    $signed_price    = get_post_meta( get_the_ID(), '_dbc_book_signed_price', true );
    $kindle_url      = get_post_meta( get_the_ID(), '_dbc_book_kindle_url', true );
    
    $series = dbt_get_book_series( get_the_ID() );
    $genre_display = dbt_get_book_genre( get_the_ID() );
    $genres = get_the_terms( get_the_ID(), 'genre' );
    
    $series_label = '';
    if ( $series ) {
        if ( $series->slug === 'standalones' ) {
            $series_label = dbt_get_standalone_label( get_the_ID() );
        } else {
            if ( $series_order ) {
                $series_label = 'Book ' . $series_order . ' in the ' . $series->name . ' Series';
            } else {
                $series_label = $series->name . ' Series';
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
                
                <!-- Purchase Options -->
                <div class="book-hero__purchase">
                    <h3 class="book-hero__purchase-title">Get Your Copy</h3>
                    
                    <div class="book-hero__purchase-options">
                        <?php if ( $amazon_url ) : ?>
                        <a href="<?php echo esc_url( $amazon_url ); ?>" target="_blank" rel="noopener" class="purchase-option purchase-option--amazon">
                            <span class="purchase-option__icon">
                                <svg viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M257.2 162.7c-48.7 1.8-169.5 15.5-169.5 117.5 0 109.5 138.3 114 183.5 43.2 6.5 10.2 35.4 37.5 45.3 46.8l56.8-56S341 288.9 341 261.4V114.3C341 89 316.5 32 228.7 32 140.7 32 94 87 94 136.3l73.5 6.8c16.3-49.5 54.2-49.5 54.2-49.5 40.7-.1 35.5 29.8 35.5 69.1zm0 86.8c0 80-84.2 68-84.2 17.2 0-47.2 50.5-56.7 84.2-57.8v40.6zm136 163.5c-7.7 10-70 67-174.5 67S34.2 408.5 9.7 379c-6.8-7.7 1-11.3 5.5-8.3C88.5 415.2 203 488.5 387.7 401c7.5-3.7 13.3 2 5.5 12zm39.8 2.2c-6.5 15.8-16 26.8-21.2 31-5.5 4.5-9.5 2.7-6.5-3.8s19.3-46.5 12.7-55c-6.5-8.3-37-4.3-48-3.2-10.8 1-13 2-14-.3-2.3-5.7 21.7-15.5 37.5-17.5 15.7-1.8 41-.8 46 5.7 3.7 5.1 0 27.1-6.5 43.1z"/></svg>
                            </span>
                            <span class="purchase-option__text">
                                <span class="purchase-option__label">Buy on Amazon</span>
                                <span class="purchase-option__sublabel">Kindle & Paperback</span>
                            </span>
                            <span class="purchase-option__arrow">→</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ( $signed_enabled && $signed_price ) : ?>
                        <button type="button" class="purchase-option purchase-option--signed" data-purchase="signed" data-book-id="<?php echo esc_attr( get_the_ID() ); ?>" data-book-title="<?php echo esc_attr( get_the_title() ); ?>" data-price="<?php echo esc_attr( $signed_price ); ?>">
                            <span class="purchase-option__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"/></svg>
                            </span>
                            <span class="purchase-option__text">
                                <span class="purchase-option__label">Signed Paperback</span>
                                <span class="purchase-option__sublabel">$<?php echo esc_html( number_format( (float) $signed_price, 2 ) ); ?> + S&H</span>
                            </span>
                            <span class="purchase-option__arrow">→</span>
                        </button>
                        <?php endif; ?>
                        
                        <?php if ( $kindle_url ) : ?>
                        <a href="<?php echo esc_url( $kindle_url ); ?>" target="_blank" rel="noopener" class="purchase-option purchase-option--digital">
                            <span class="purchase-option__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                                </svg>
                            </span>
                            <span class="purchase-option__text">
                                <span class="purchase-option__label">Kindle / eBook</span>
                                <span class="purchase-option__sublabel">Buy on Amazon</span>
                            </span>
                            <span class="purchase-option__arrow">→</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ( $free_chapter ) : ?>
                    <div class="book-hero__free-chapter">
                        <button type="button" class="btn btn--free-chapter" data-free-chapter data-book-id="<?php echo esc_attr( get_the_ID() ); ?>" data-book-title="<?php echo esc_attr( get_the_title() ); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Get a Free Chapter
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="book-hero__nav">
                    <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--text">← Browse All Books</a>
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
                    
                    <?php /* Formats display removed - data stored as array causing errors */ ?>
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

