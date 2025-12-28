<?php
/**
 * Books Archive Template
 */

get_header();
?>

<!-- Books Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <p class="hero__eyebrow">Library</p>
            <h1 class="hero__title hero__title--typewriter" data-typewriter-text="All Books">
                <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
            </h1>
            <p class="hero__subtitle">Explore Drew Bankston's complete collection of science fiction, fantasy, and non-fiction works.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="books-filters gsap-reveal">
            <button class="filter-btn active" data-filter="all">All Books</button>
            <?php
            $series = get_terms( array(
                'taxonomy'   => 'series',
                'hide_empty' => true,
            ) );
            foreach ( $series as $s ) :
            ?>
            <button class="filter-btn" data-filter="series-<?php echo esc_attr( $s->slug ); ?>"><?php echo esc_html( $s->name ); ?></button>
            <?php endforeach; ?>
            
            <?php
            $genres = get_terms( array(
                'taxonomy'   => 'genre',
                'hide_empty' => true,
            ) );
            foreach ( $genres as $g ) :
            ?>
            <button class="filter-btn" data-filter="genre-<?php echo esc_attr( $g->slug ); ?>"><?php echo esc_html( $g->name ); ?></button>
            <?php endforeach; ?>
        </div>
        
        <!-- Books Grid -->
        <div class="books-grid gsap-reveal">
            <?php
            $all_books = new WP_Query( array(
                'post_type'      => 'book',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order date',
                'order'          => 'ASC',
            ) );
            
            if ( $all_books->have_posts() ) :
                while ( $all_books->have_posts() ) : $all_books->the_post();
                    $tagline = get_post_meta( get_the_ID(), '_dbc_book_tagline', true );
                    $series = get_the_terms( get_the_ID(), 'series' );
                    $genres = get_the_terms( get_the_ID(), 'genre' );
                    $genre_display = dbt_get_book_genre( get_the_ID() );
                    
                    $filter_classes = array();
                    if ( $series && ! is_wp_error( $series ) ) {
                        foreach ( $series as $s ) {
                            $filter_classes[] = 'series-' . $s->slug;
                        }
                    }
                    if ( $genres && ! is_wp_error( $genres ) ) {
                        foreach ( $genres as $g ) {
                            $filter_classes[] = 'genre-' . $g->slug;
                        }
                    }
                    
                    $series_label = '';
                    if ( $series && ! is_wp_error( $series ) ) {
                        $series_label = $series[0]->name;
                        if ( $series[0]->slug === 'standalones' ) {
                            $series_label = dbt_get_standalone_label( get_the_ID() );
                        }
                    }
            ?>
            <article class="book-card" data-categories="<?php echo esc_attr( implode( ' ', $filter_classes ) ); ?>">
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
                    <p class="book-card__genre"><?php echo esc_html( $genre_display ); ?></p>
                    <?php if ( $tagline ) : ?>
                        <p class="book-card__tagline"><?php echo esc_html( $tagline ); ?></p>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="book-card__link">Learn More →</a>
                </div>
            </article>
            <?php 
                endwhile;
                wp_reset_postdata();
            else :
            ?>
            <div class="placeholder-notice">
                <p>No books found. Add books in WordPress admin.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
get_footer();


