<?php
/**
 * Main template file
 */

get_header();
?>

<section class="section">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            
            <div class="section-header">
                <h1 class="section-header__title">
                    <?php
                    if ( is_home() && ! is_front_page() ) {
                        single_post_title();
                    } elseif ( is_search() ) {
                        printf( 'Search Results for: %s', get_search_query() );
                    } elseif ( is_archive() ) {
                        the_archive_title();
                    } else {
                        echo 'Latest Posts';
                    }
                    ?>
                </h1>
            </div>
            
            <div class="posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="post-card">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-card__content">
                            <h2 class="post-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="post-card__excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="post-card__link">Read More →</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php the_posts_pagination(); ?>
            
        <?php else : ?>
            
            <div class="no-results">
                <h2>Nothing Found</h2>
                <p>Sorry, but nothing matched your search terms. Please try again with different keywords.</p>
            </div>
            
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();

