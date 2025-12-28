<?php
/**
 * Single Event Template
 */

get_header();

while ( have_posts() ) : the_post();
    $start_datetime = get_post_meta( get_the_ID(), '_dbc_event_start_datetime', true );
    $end_datetime   = get_post_meta( get_the_ID(), '_dbc_event_end_datetime', true );
    $location_name  = get_post_meta( get_the_ID(), '_dbc_event_location_name', true );
    $location_addr  = get_post_meta( get_the_ID(), '_dbc_event_location_address', true );
    $event_type     = get_post_meta( get_the_ID(), '_dbc_event_type', true );
    $event_url      = get_post_meta( get_the_ID(), '_dbc_event_url', true );
    $is_virtual     = get_post_meta( get_the_ID(), '_dbc_event_is_virtual', true );
    
    $event_date = strtotime( $start_datetime );
    $is_past = $event_date < time();
?>

<!-- Event Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <?php if ( $event_type ) : ?>
                <p class="hero__eyebrow"><?php echo esc_html( ucwords( str_replace( '-', ' ', $event_type ) ) ); ?></p>
            <?php endif; ?>
            <h1 class="hero__title"><?php the_title(); ?></h1>
            <p class="hero__subtitle">
                <?php echo date( 'l, F j, Y', $event_date ); ?>
                <?php if ( $location_name ) : ?>
                    • <?php echo esc_html( $location_name ); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="book-details__grid gsap-reveal">
            <div class="book-details__content">
                <h2>About This Event</h2>
                <div class="book-details__description">
                    <?php the_content(); ?>
                </div>
                
                <?php if ( ! $is_past && $event_url ) : ?>
                <div style="margin-top: var(--space-8);">
                    <a href="<?php echo esc_url( $event_url ); ?>" target="_blank" rel="noopener" class="btn btn--primary btn--lg">
                        <?php echo $is_virtual ? 'Join Virtual Event' : 'Event Details / RSVP'; ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <aside class="book-details__meta">
                <h3>Event Details</h3>
                <dl class="book-details__meta-list">
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Date</dt>
                        <dd class="book-details__meta-value"><?php echo date( 'F j, Y', $event_date ); ?></dd>
                    </div>
                    
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Time</dt>
                        <dd class="book-details__meta-value">
                            <?php echo date( 'g:i A', $event_date ); ?>
                            <?php if ( $end_datetime ) : ?>
                                - <?php echo date( 'g:i A', strtotime( $end_datetime ) ); ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                    
                    <?php if ( $event_type ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Type</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( ucwords( str_replace( '-', ' ', $event_type ) ) ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Format</dt>
                        <dd class="book-details__meta-value"><?php echo $is_virtual ? 'Virtual' : 'In-Person'; ?></dd>
                    </div>
                    
                    <?php if ( $location_name ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Location</dt>
                        <dd class="book-details__meta-value"><?php echo esc_html( $location_name ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $location_addr && ! $is_virtual ) : ?>
                    <div class="book-details__meta-item">
                        <dt class="book-details__meta-label">Address</dt>
                        <dd class="book-details__meta-value"><?php echo nl2br( esc_html( $location_addr ) ); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $is_past ) : ?>
                    <div class="book-details__meta-item" style="color: var(--color-text-muted);">
                        <dt class="book-details__meta-label">Status</dt>
                        <dd class="book-details__meta-value">Past Event</dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
        
        <div style="margin-top: var(--space-12); text-align: center;">
            <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="btn btn--secondary">← Back to All Events</a>
        </div>
    </div>
</section>

<?php endwhile; ?>

<?php
get_footer();


