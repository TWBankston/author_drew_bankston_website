<?php
/**
 * Single Post Template
 */

get_header();
?>

<section class="section" style="padding-top: var(--space-32);">
    <div class="container container--narrow">
        <?php while ( have_posts() ) : the_post(); ?>
        
        <article class="post gsap-reveal">
            <header class="post__header" style="margin-bottom: var(--space-8);">
                <p class="section-header__eyebrow"><?php echo get_the_date(); ?></p>
                <h1 class="section-header__title"><?php the_title(); ?></h1>
            </header>
            
            <?php if ( has_post_thumbnail() ) : ?>
            <div class="post__image" style="margin-bottom: var(--space-8);">
                <?php the_post_thumbnail( 'large', array( 'style' => 'border-radius: var(--radius-lg);' ) ); ?>
            </div>
            <?php endif; ?>
            
            <div class="post__content book-details__description">
                <?php the_content(); ?>
            </div>
            
            <footer class="post__footer" style="margin-top: var(--space-12); padding-top: var(--space-8); border-top: 1px solid var(--color-border-subtle);">
                <?php
                $categories = get_the_category();
                $tags = get_the_tags();
                ?>
                
                <?php if ( $categories ) : ?>
                <p class="text-muted" style="font-size: var(--text-sm);">
                    <strong>Categories:</strong> 
                    <?php echo implode( ', ', array_map( function( $cat ) {
                        return '<a href="' . get_category_link( $cat->term_id ) . '">' . $cat->name . '</a>';
                    }, $categories ) ); ?>
                </p>
                <?php endif; ?>
                
                <?php if ( $tags ) : ?>
                <p class="text-muted" style="font-size: var(--text-sm);">
                    <strong>Tags:</strong> 
                    <?php echo implode( ', ', array_map( function( $tag ) {
                        return '<a href="' . get_tag_link( $tag->term_id ) . '">' . $tag->name . '</a>';
                    }, $tags ) ); ?>
                </p>
                <?php endif; ?>
            </footer>
        </article>
        
        <?php endwhile; ?>
        
        <div style="margin-top: var(--space-12); text-align: center;">
            <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="btn btn--secondary">← Back to Blog</a>
        </div>
    </div>
</section>

<?php
get_footer();

