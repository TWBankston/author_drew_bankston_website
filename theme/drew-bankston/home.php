<?php
/**
 * Blog Archive Template
 */

get_header();
?>

<!-- Blog Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <p class="hero__eyebrow">News & Updates</p>
            <h1 class="hero__title">Blog</h1>
            <p class="hero__subtitle">Behind-the-scenes updates, writing insights, and announcements from Drew.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ( have_posts() ) : ?>
        <div class="books-grid gsap-reveal">
            <?php while ( have_posts() ) : the_post(); ?>
            <article class="book-card">
                <?php if ( has_post_thumbnail() ) : ?>
                <div class="book-card__cover" style="aspect-ratio: 16/9;">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail( 'medium_large' ); ?>
                    </a>
                </div>
                <?php endif; ?>
                <div class="book-card__content">
                    <p class="book-card__genre"><?php echo get_the_date(); ?></p>
                    <h3 class="book-card__title"><?php the_title(); ?></h3>
                    <p class="book-card__tagline"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                    <a href="<?php the_permalink(); ?>" class="book-card__link">Read More →</a>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
        
        <?php the_posts_pagination( array(
            'mid_size'  => 2,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
        ) ); ?>
        
        <?php else : ?>
        <div class="placeholder-notice gsap-reveal">
            <p>No blog posts yet. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();


