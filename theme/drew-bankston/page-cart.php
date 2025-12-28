<?php
/**
 * Template Name: Cart
 * Cart page for viewing and editing shopping cart
 */

get_header();

$cart = DBC_Cart::get_cart();
$cart_count = DBC_Cart::get_cart_count();
$subtotal = DBC_Cart::get_subtotal();
$shipping = DBC_Cart::get_shipping_cost();
$total = DBC_Cart::get_total();
$has_physical = DBC_Cart::has_physical_items();
?>

<!-- Cart Hero -->
<section class="hero hero--compact">
    <div class="container">
        <div class="hero__content hero__content--centered">
            <h1 class="hero__title hero__title--typewriter" data-typewriter-text="Your Cart">
                <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
            </h1>
            <p class="hero__subtitle" id="cart-page-count"><?php echo $cart_count; ?> item<?php echo $cart_count !== 1 ? 's' : ''; ?> in your cart</p>
        </div>
    </div>
</section>

<!-- Cart Content -->
<section class="section cart-section">
    <div class="container">
        <?php if ( empty( $cart ) ) : ?>
        
        <div class="cart-empty">
            <div class="cart-empty__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
            </div>
            <h2 class="cart-empty__title">Your cart is empty</h2>
            <p class="cart-empty__text">Looks like you haven't added any books yet.</p>
            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="btn btn--primary">Browse Books</a>
        </div>
        
        <?php else : ?>
        
        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items" id="cart-items">
                <?php foreach ( $cart as $cart_key => $item ) : ?>
                <div class="cart-item" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
                    <div class="cart-item__image">
                        <?php if ( $item['thumbnail'] ) : ?>
                            <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>">
                        <?php else : ?>
                            <div class="cart-item__placeholder"></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-item__details">
                        <h3 class="cart-item__name"><?php echo esc_html( $item['name'] ); ?></h3>
                        <p class="cart-item__type">
                            <?php echo $item['type'] === 'signed' ? 'Signed Paperback' : 'Digital Download'; ?>
                        </p>
                        <p class="cart-item__price">$<?php echo esc_html( number_format( $item['price'], 2 ) ); ?></p>
                    </div>
                    
                    <div class="cart-item__quantity">
                        <?php if ( $item['type'] === 'digital' ) : ?>
                            <span class="cart-item__qty-static">Qty: 1</span>
                        <?php else : ?>
                            <div class="quantity-control">
                                <button type="button" class="quantity-control__btn quantity-control__btn--minus" data-action="decrease">−</button>
                                <input type="number" class="quantity-control__input" value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1" max="10">
                                <button type="button" class="quantity-control__btn quantity-control__btn--plus" data-action="increase">+</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-item__subtotal">
                        <span class="cart-item__subtotal-label">Subtotal</span>
                        <span class="cart-item__subtotal-value">$<?php echo esc_html( number_format( $item['price'] * $item['quantity'], 2 ) ); ?></span>
                    </div>
                    
                    <button type="button" class="cart-item__remove" data-action="remove" aria-label="Remove item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3 class="cart-summary__title">Order Summary</h3>
                
                <div class="cart-summary__row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">$<?php echo esc_html( number_format( $subtotal, 2 ) ); ?></span>
                </div>
                
                <?php if ( $has_physical ) : ?>
                <div class="cart-summary__row">
                    <span>Shipping</span>
                    <span id="cart-shipping">$<?php echo esc_html( number_format( $shipping, 2 ) ); ?></span>
                </div>
                <p class="cart-summary__shipping-note">Flat rate shipping. Final cost calculated at checkout.</p>
                <?php else : ?>
                <div class="cart-summary__row">
                    <span>Shipping</span>
                    <span>Free (Digital)</span>
                </div>
                <?php endif; ?>
                
                <div class="cart-summary__divider"></div>
                
                <div class="cart-summary__row cart-summary__row--total">
                    <span>Total</span>
                    <span id="cart-total">$<?php echo esc_html( number_format( $total, 2 ) ); ?></span>
                </div>
                
                <a href="<?php echo esc_url( home_url( '/checkout/' ) ); ?>" class="btn btn--primary btn--lg btn--full">
                    Proceed to Checkout
                </a>
                
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="cart-summary__continue">
                    ← Continue Shopping
                </a>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</section>

<script>
// Cart nonce for AJAX
var dbcCartNonce = '<?php echo wp_create_nonce( 'dbc_cart_nonce' ); ?>';
</script>

<?php get_footer(); ?>

