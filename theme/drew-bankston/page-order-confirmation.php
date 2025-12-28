<?php
/**
 * Template Name: Order Confirmation
 * Displays order confirmation after successful purchase
 */

get_header();

// In production, this would retrieve order details from the database
$order_id = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : '';
?>

<!-- Confirmation Hero -->
<section class="hero hero--compact">
    <div class="container">
        <div class="hero__content hero__content--centered">
            <div class="confirmation-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h1 class="hero__title hero__title--typewriter" data-typewriter-text="Thank You!">
                <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
            </h1>
            <p class="hero__subtitle">Your order has been placed successfully</p>
        </div>
    </div>
</section>

<!-- Confirmation Content -->
<section class="section confirmation-section">
    <div class="container container--narrow">
        <div class="confirmation-card">
            <div class="confirmation-card__header">
                <h2>Order Confirmed</h2>
                <?php if ( $order_id ) : ?>
                <p class="confirmation-card__order-id">Order #<?php echo esc_html( $order_id ); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="confirmation-card__body">
                <p>We've received your order and are preparing it for shipment. You'll receive a confirmation email shortly with your order details and tracking information.</p>
                
                <div class="confirmation-card__next-steps">
                    <h3>What's Next?</h3>
                    <ul>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span>Check your email for order confirmation</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                            <span>Signed books ship within 3-5 business days</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            <span>Digital purchases are available immediately in your account</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="confirmation-card__actions">
                <a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn--primary">View My Account</a>
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<?php 
// Clear the cart after successful order
DBC_Cart::clear_cart();

get_footer(); 
?>
