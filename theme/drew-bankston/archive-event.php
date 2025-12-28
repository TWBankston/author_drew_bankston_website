<?php
/**
 * Events Archive Template
 */

get_header();
?>

<!-- Events Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <p class="hero__eyebrow">Appearances</p>
            <h1 class="hero__title hero__title--typewriter" data-typewriter-text="Events">
                <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
            </h1>
            <p class="hero__subtitle">Meet Drew at book signings, readings, panels, and conventions.</p>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="section">
    <div class="container">
        <div class="section-header section-header--left gsap-reveal">
            <p class="section-header__eyebrow">Coming Up</p>
            <h2 class="section-header__title">Upcoming Events</h2>
        </div>
        
        <?php
        $upcoming = DBC_CPT_Event::get_upcoming_events( 10 );
        
        if ( $upcoming->have_posts() ) :
        ?>
        <div class="events-timeline gsap-reveal">
            <?php while ( $upcoming->have_posts() ) : $upcoming->the_post(); 
                $start_datetime = get_post_meta( get_the_ID(), '_dbc_event_start_datetime', true );
                $location_name = get_post_meta( get_the_ID(), '_dbc_event_location_name', true );
                $event_type = get_post_meta( get_the_ID(), '_dbc_event_type', true );
                
                $event_date = strtotime( $start_datetime );
            ?>
            <article class="event-card">
                <div class="event-card__date">
                    <span class="event-card__date-day"><?php echo date( 'j', $event_date ); ?></span>
                    <span class="event-card__date-month"><?php echo date( 'M Y', $event_date ); ?></span>
                </div>
                <div class="event-card__content">
                    <?php if ( $event_type ) : ?>
                        <span class="event-card__type"><?php echo esc_html( ucwords( str_replace( '-', ' ', $event_type ) ) ); ?></span>
                    <?php endif; ?>
                    <h3><?php the_title(); ?></h3>
                    <?php if ( $location_name ) : ?>
                        <p class="event-card__location"><?php echo esc_html( $location_name ); ?></p>
                    <?php endif; ?>
                    <?php if ( has_excerpt() ) : ?>
                        <p class="event-card__excerpt"><?php echo get_the_excerpt(); ?></p>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="btn btn--sm btn--secondary">Event Details</a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php else : ?>
        <div class="placeholder-notice gsap-reveal">
            <p>No upcoming events scheduled. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Past Events -->
<?php
$past = DBC_CPT_Event::get_past_events( 10 );

if ( $past->have_posts() ) :
?>
<section class="section" style="background: var(--color-bg-secondary);">
    <div class="container">
        <div class="section-header section-header--left gsap-reveal">
            <p class="section-header__eyebrow">Archive</p>
            <h2 class="section-header__title">Past Events</h2>
        </div>
        
        <div class="events-timeline gsap-reveal">
            <?php while ( $past->have_posts() ) : $past->the_post(); 
                $start_datetime = get_post_meta( get_the_ID(), '_dbc_event_start_datetime', true );
                $location_name = get_post_meta( get_the_ID(), '_dbc_event_location_name', true );
                $event_type = get_post_meta( get_the_ID(), '_dbc_event_type', true );
                
                $event_date = strtotime( $start_datetime );
            ?>
            <article class="event-card" style="opacity: 0.7;">
                <div class="event-card__date">
                    <span class="event-card__date-day"><?php echo date( 'j', $event_date ); ?></span>
                    <span class="event-card__date-month"><?php echo date( 'M Y', $event_date ); ?></span>
                </div>
                <div class="event-card__content">
                    <?php if ( $event_type ) : ?>
                        <span class="event-card__type"><?php echo esc_html( ucwords( str_replace( '-', ' ', $event_type ) ) ); ?></span>
                    <?php endif; ?>
                    <h3><?php the_title(); ?></h3>
                    <?php if ( $location_name ) : ?>
                        <p class="event-card__location"><?php echo esc_html( $location_name ); ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
get_footer();


