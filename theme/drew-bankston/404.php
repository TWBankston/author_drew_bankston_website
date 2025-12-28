<?php
/**
 * 404 Template
 */

get_header();
?>

<section class="hero" style="min-height: 70vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <p class="hero__eyebrow">Error 404</p>
            <h1 class="hero__title hero__title--typewriter" data-typewriter-text="Page Not Found">
                <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
            </h1>
            <p class="hero__subtitle">The page you're looking for seems to have drifted into another dimension. Let's get you back on course.</p>
            
            <div class="hero__cta" style="justify-content: center; margin-top: var(--space-8);">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary btn--lg">Return Home</a>
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--secondary btn--lg">Browse Books</a>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();


