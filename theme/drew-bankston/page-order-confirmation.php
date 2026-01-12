<?php
/**
 * Template Name: Order Confirmation
 * Order confirmation page after successful payment
 */

get_header();

// Get order ID from URL
$order_id = isset( $_GET['order'] ) ? intval( $_GET['order'] ) : 0;

if ( ! $order_id ) {
    wp_redirect( home_url( '/cart/' ) );
    exit;
}

// Get order from database
global $wpdb;
$table_name = $wpdb->prefix . 'dbc_orders';
$order = $wpdb->get_row( $wpdb->prepare( 
    "SELECT * FROM $table_name WHERE id = %d", 
    $order_id 
) );

if ( ! $order ) {
    wp_redirect( home_url( '/cart/' ) );
    exit;
}

$order_items = json_decode( $order->order_items, true );
$shipping_address = json_decode( $order->shipping_address, true );
?>

<!-- Order Confirmation Hero -->
<section class="hero hero--compact">
    <div class="container">
        <div class="hero__content hero__content--centered">
            <div style="text-align: center; margin-bottom: 2rem;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-accent-sky); margin: 0 auto;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h1 class="hero__title">Order Confirmed!</h1>
            <p class="hero__subtitle">Thank you for your purchase, <?php echo esc_html( explode( ' ', $order->customer_name )[0] ); ?>!</p>
        </div>
    </div>
</section>

<!-- Order Details -->
<section class="section">
    <div class="container" style="max-width: 800px;">
        
        <!-- Order Summary -->
        <div class="checkout-block" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-6);">
            <h2 class="checkout-block__title">Order #<?php echo esc_html( $order_id ); ?></h2>
            
            <div style="display: grid; gap: var(--space-4); margin-bottom: var(--space-6);">
                <div style="display: flex; justify-content: space-between; padding-bottom: var(--space-4); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <span style="color: rgba(255, 255, 255, 0.7);">Order Date</span>
                    <span><?php echo esc_html( date( 'F j, Y', strtotime( $order->created_at ) ) ); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: var(--space-4); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <span style="color: rgba(255, 255, 255, 0.7);">Status</span>
                    <span style="color: var(--color-accent-sky);"><?php echo esc_html( ucfirst( $order->status ) ); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: var(--space-4); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <span style="color: rgba(255, 255, 255, 0.7);">Total</span>
                    <span style="font-size: var(--text-xl); font-weight: var(--font-bold); color: var(--color-accent-sky);">$<?php echo esc_html( number_format( $order->total_amount, 2 ) ); ?></span>
                </div>
            </div>
            
            <p style="color: rgba(255, 255, 255, 0.7); font-size: var(--text-sm);">
                A confirmation email has been sent to <strong><?php echo esc_html( $order->customer_email ); ?></strong>
            </p>
        </div>
        
        <!-- Order Items -->
        <div class="checkout-block" style="margin-bottom: var(--space-6);">
            <h3 style="margin-bottom: var(--space-4);">Items Ordered</h3>
            
            <div style="display: grid; gap: var(--space-4);">
                <?php foreach ( $order_items as $item ) : ?>
                <div style="display: flex; gap: var(--space-4); padding: var(--space-4); background: rgba(255, 255, 255, 0.03); border-radius: var(--radius-md);">
                    <?php if ( ! empty( $item['thumbnail'] ) ) : ?>
                    <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" style="width: 60px; height: 90px; object-fit: contain; border-radius: var(--radius-md);">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: var(--space-1);"><?php echo esc_html( $item['name'] ); ?></h4>
                        <p style="color: rgba(255, 255, 255, 0.7); font-size: var(--text-sm);">
                            Quantity: <?php echo esc_html( $item['quantity'] ); ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-weight: var(--font-bold);">$<?php echo esc_html( number_format( $item['price'] * $item['quantity'], 2 ) ); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <?php if ( ! empty( $shipping_address['address_1'] ) ) : ?>
        <div class="checkout-block" style="margin-bottom: var(--space-6);">
            <h3 style="margin-bottom: var(--space-4);">Shipping Address</h3>
            <div style="padding: var(--space-4); background: rgba(255, 255, 255, 0.03); border-radius: var(--radius-md);">
                <p><?php echo esc_html( $order->customer_name ); ?></p>
                <p><?php echo esc_html( $shipping_address['address_1'] ); ?></p>
                <?php if ( ! empty( $shipping_address['address_2'] ) ) : ?>
                <p><?php echo esc_html( $shipping_address['address_2'] ); ?></p>
                <?php endif; ?>
                <p><?php echo esc_html( $shipping_address['city'] . ', ' . $shipping_address['state'] . ' ' . $shipping_address['zip'] ); ?></p>
                <?php if ( $shipping_address['country'] === 'CA' ) : ?>
                <p>Canada</p>
                <?php endif; ?>
            </div>
            
            <?php if ( $order->signature_request ) : ?>
            <div style="margin-top: var(--space-4); padding: var(--space-4); background: rgba(185, 215, 255, 0.1); border: 1px solid rgba(185, 215, 255, 0.2); border-radius: var(--radius-md);">
                <p style="font-weight: var(--font-medium); margin-bottom: var(--space-2);">✍️ Signature Requested</p>
                <?php if ( ! empty( $order->signature_message ) ) : ?>
                <p style="color: rgba(255, 255, 255, 0.7); font-size: var(--text-sm); font-style: italic;">"<?php echo esc_html( $order->signature_message ); ?>"</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Actions -->
        <div style="display: flex; gap: var(--space-4); justify-content: center; margin-top: var(--space-8);">
            <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( home_url( '/account/?section=orders' ) ); ?>" class="btn btn--secondary">
                View All Orders
            </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--primary">
                Continue Shopping
            </a>
        </div>
        
    </div>
</section>

<?php get_footer(); ?>
