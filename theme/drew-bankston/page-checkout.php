<?php
/**
 * Template Name: Checkout
 * Checkout page for completing purchases
 */

get_header();

$cart = DBC_Cart::get_cart();
$cart_count = DBC_Cart::get_cart_count();
$subtotal = DBC_Cart::get_subtotal();
$shipping = DBC_Cart::get_shipping_cost();
$has_physical = DBC_Cart::has_physical_items();

// Get applied discount
$applied_discount = DBC_Orders_Settings::get_applied_discount();
$discount_amount = $applied_discount ? $applied_discount['amount'] : 0;
$total = $subtotal - $discount_amount + $shipping;

// Redirect to cart if empty
if ( empty( $cart ) ) {
    wp_redirect( home_url( '/cart/' ) );
    exit;
}

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();

// Check if cart contains digital items (for account benefits messaging)
$has_digital = false;
foreach ( $cart as $item ) {
    if ( isset( $item['type'] ) && $item['type'] === 'digital' ) {
        $has_digital = true;
        break;
    }
}
?>

<!-- Checkout Hero -->
<section class="hero hero--compact">
    <div class="container">
        <div class="hero__content hero__content--centered">
            <h1 class="hero__title hero__title--typewriter" data-typewriter-text="Checkout">
                <span class="typewriter-text"></span><span class="typewriter-cursor typing">|</span>
            </h1>
            <p class="hero__subtitle">Complete your order</p>
        </div>
    </div>
</section>

<!-- Checkout Content -->
<section class="section checkout-section">
    <div class="container">
        <form id="checkout-form" class="checkout-layout">
            
            <!-- Mobile Order Summary (Top) - Only visible on mobile/tablet -->
            <div class="checkout-summary checkout-summary--mobile-top">
                <div class="checkout-summary__header" id="mobile-summary-toggle">
                    <h3 class="checkout-summary__title">Order Summary</h3>
                    <span class="checkout-summary__toggle">
                        <span class="checkout-summary__item-count"><?php echo $cart_count; ?> item<?php echo $cart_count !== 1 ? 's' : ''; ?></span>
                        <span class="checkout-summary__total-preview">$<?php echo esc_html( number_format( $total, 2 ) ); ?></span>
                        <svg class="checkout-summary__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </span>
                </div>
                <div class="checkout-summary__collapsible" id="mobile-summary-content">
                    <div class="checkout-summary__items">
                        <?php foreach ( $cart as $cart_key => $item ) : ?>
                        <div class="checkout-summary__item">
                            <div class="checkout-summary__item-image">
                                <?php if ( $item['thumbnail'] ) : ?>
                                    <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="">
                                <?php endif; ?>
                                <span class="checkout-summary__item-qty"><?php echo esc_html( $item['quantity'] ); ?></span>
                            </div>
                            <div class="checkout-summary__item-details">
                                <span class="checkout-summary__item-name"><?php echo esc_html( $item['name'] ); ?></span>
                            </div>
                            <div class="checkout-summary__item-price">
                                $<?php echo esc_html( number_format( $item['price'] * $item['quantity'], 2 ) ); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="checkout-summary__totals">
                        <div class="checkout-summary__row">
                            <span>Subtotal</span>
                            <span>$<?php echo esc_html( number_format( $subtotal, 2 ) ); ?></span>
                        </div>
                        <?php if ( $has_physical ) : ?>
                        <div class="checkout-summary__row">
                            <span>Shipping</span>
                            <span>$<?php echo esc_html( number_format( $shipping, 2 ) ); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="checkout-summary__divider"></div>
                        <div class="checkout-summary__row checkout-summary__row--total">
                            <span>Total</span>
                            <span>$<?php echo esc_html( number_format( $total, 2 ) ); ?></span>
                        </div>
                    </div>
                    
                    <a href="<?php echo esc_url( home_url( '/cart/' ) ); ?>" class="checkout-summary__edit">
                        Edit Cart →
                    </a>
                </div>
            </div>
            
            <!-- Checkout Form -->
            <div class="checkout-form">
                
                <!-- Contact Information -->
                <div class="checkout-block">
                    <h2 class="checkout-block__title">Contact Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group form-group--half">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required 
                                   value="<?php echo esc_attr( $current_user->first_name ); ?>">
                        </div>
                        <div class="form-group form-group--half">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required
                                   value="<?php echo esc_attr( $current_user->last_name ); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo esc_attr( $current_user->user_email ); ?>">
                        <p class="form-help">Order confirmation will be sent here</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <?php if ( ! $is_logged_in ) : ?>
                    <!-- Account Creation Option for Guests -->
                    <div class="checkout-account-prompt">
                        <div class="checkout-account-prompt__toggle">
                            <label class="checkbox-label checkbox-label--featured">
                                <input type="checkbox" name="create_account" id="create_account" value="1" checked>
                                <span>Create an account for a better experience</span>
                            </label>
                        </div>
                        
                        <div class="checkout-account-prompt__benefits">
                            <p class="checkout-account-prompt__intro">With an account you can:</p>
                            <ul class="checkout-account-prompt__list">
                                <li>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Access free chapter previews
                                </li>
                                <?php if ( $has_digital ) : ?>
                                <li>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Keep your digital book downloads forever
                                </li>
                                <?php endif; ?>
                                <li>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Track your orders and view invoices
                                </li>
                                <li>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Get notified about new releases and events
                                </li>
                            </ul>
                        </div>
                        
                        <div class="checkout-account-prompt__fields" id="account-fields">
                            <div class="form-group">
                                <label for="account_password">Create Password *</label>
                                <input type="password" id="account_password" name="account_password" minlength="8" 
                                       placeholder="Minimum 8 characters">
                            </div>
                            <div class="form-group">
                                <label for="account_password_confirm">Confirm Password *</label>
                                <input type="password" id="account_password_confirm" name="account_password_confirm" minlength="8">
                            </div>
                            <div class="form-group form-group--checkbox-inline">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="subscribe_newsletter" id="subscribe_newsletter" value="1" checked>
                                    <span>Keep me updated on new releases, events, and exclusive content</span>
                                </label>
                                <p class="form-help form-help--subtle">You can unsubscribe anytime from your account settings.</p>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <p class="checkout-logged-in-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Logged in as <strong><?php echo esc_html( $current_user->display_name ); ?></strong>
                    </p>
                    <?php endif; ?>
                </div>
                
                <?php if ( $has_physical ) : ?>
                <!-- Shipping Address -->
                <div class="checkout-block">
                    <h2 class="checkout-block__title">Shipping Address</h2>
                    
                    <div class="form-group">
                        <label for="address_1">Street Address *</label>
                        <input type="text" id="address_1" name="address_1" required placeholder="123 Main St">
                    </div>
                    
                    <div class="form-group">
                        <label for="address_2">Apartment, Suite, etc.</label>
                        <input type="text" id="address_2" name="address_2" placeholder="Apt 4B">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-group--half">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group form-group--quarter">
                            <label for="state">State *</label>
                            <input type="text" id="state" name="state" required placeholder="CO">
                        </div>
                        <div class="form-group form-group--quarter">
                            <label for="zip">ZIP Code *</label>
                            <input type="text" id="zip" name="zip" required placeholder="80202">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country *</label>
                        <select id="country" name="country" required>
                            <option value="US" selected>United States</option>
                            <option value="CA">Canada</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="signature_request" value="1">
                            <span>Add personalized signature (optional)</span>
                        </label>
                    </div>
                    
                    <div class="form-group" id="signature-message-group" style="display: none;">
                        <label for="signature_message">Personalization Message</label>
                        <textarea id="signature_message" name="signature_message" rows="3" 
                                  placeholder="e.g., To John, Happy Reading!"></textarea>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Payment -->
                <div class="checkout-block">
                    <h2 class="checkout-block__title">Payment Information</h2>
                    
                    <?php 
                    $square_config = dbt_get_square_config();
                    if ( ! $square_config['enabled'] ) : 
                    ?>
                    <div class="payment-placeholder">
                        <div class="payment-placeholder__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                        </div>
                        <p class="payment-placeholder__text">
                            <strong>Payment processing is not configured.</strong><br>
                            Please contact the site administrator.
                        </p>
                    </div>
                    <?php else : ?>
                    <!-- Payment Method Info -->
                    <div class="payment-methods">
                        <div class="payment-methods__accepted">
                            <span class="payment-methods__label">We accept:</span>
                            <div class="payment-methods__icons">
                                <svg viewBox="0 0 48 32" class="payment-icon payment-icon--visa">
                                    <rect width="48" height="32" rx="4" fill="#1434CB"/>
                                    <text x="24" y="20" fill="white" font-size="14" font-weight="bold" text-anchor="middle" font-family="Arial">VISA</text>
                                </svg>
                                <svg viewBox="0 0 48 32" class="payment-icon payment-icon--mastercard">
                                    <rect width="48" height="32" rx="4" fill="#EB001B"/>
                                    <circle cx="18" cy="16" r="10" fill="#FF5F00"/>
                                    <circle cx="30" cy="16" r="10" fill="#F79E1B"/>
                                </svg>
                                <svg viewBox="0 0 48 32" class="payment-icon payment-icon--amex">
                                    <rect width="48" height="32" rx="4" fill="#006FCF"/>
                                    <text x="24" y="20" fill="white" font-size="11" font-weight="bold" text-anchor="middle" font-family="Arial">AMEX</text>
                                </svg>
                                <svg viewBox="0 0 48 32" class="payment-icon payment-icon--discover">
                                    <rect width="48" height="32" rx="4" fill="#FF6000"/>
                                    <text x="24" y="20" fill="white" font-size="7.5" font-weight="bold" text-anchor="middle" font-family="Arial">DISCOVER</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Square Web Payments SDK -->
                    <div id="square-payment-form" class="square-payment-form">
                        <div id="card-container" class="square-card-container"></div>
                        <div id="payment-status-container" class="payment-status" style="display: none;"></div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="payment-security">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span>Your payment information is encrypted and secure. We never store your full card details.</span>
                    </div>
                    
                    <!-- Alternative Payment Methods Notice -->
                    <div class="payment-alternative-notice">
                        <p class="text-muted" style="font-size: var(--text-sm); margin-top: var(--space-4);">
                            <strong>Need to pay another way?</strong> Contact us at <a href="mailto:orders@drewbankston.com" style="color: var(--color-accent-sky);">orders@drewbankston.com</a> for alternative payment options including PayPal or check.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Order Review (Bottom) - Only visible on mobile/tablet -->
                <div class="checkout-review checkout-review--mobile">
                    <h2 class="checkout-review__title">Review Your Order</h2>
                    
                    <!-- Order Items -->
                    <div class="checkout-review__section">
                        <h4 class="checkout-review__section-title">Items</h4>
                        <?php foreach ( $cart as $cart_key => $item ) : ?>
                        <div class="checkout-review__item">
                            <span class="checkout-review__item-name">
                                <?php echo esc_html( $item['quantity'] ); ?>× <?php echo esc_html( $item['name'] ); ?>
                            </span>
                            <span class="checkout-review__item-price">
                                $<?php echo esc_html( number_format( $item['price'] * $item['quantity'], 2 ) ); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Shipping To -->
                    <?php if ( $has_physical ) : ?>
                    <div class="checkout-review__section">
                        <h4 class="checkout-review__section-title">Shipping To</h4>
                        <p class="checkout-review__address" id="review-address">
                            <span class="checkout-review__placeholder">Complete shipping address above</span>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Contact -->
                    <div class="checkout-review__section">
                        <h4 class="checkout-review__section-title">Contact</h4>
                        <p class="checkout-review__contact" id="review-contact">
                            <span class="checkout-review__placeholder">Complete contact info above</span>
                        </p>
                    </div>
                    
                    <!-- Totals -->
                    <div class="checkout-review__totals">
                        <div class="checkout-review__row">
                            <span>Subtotal</span>
                            <span>$<?php echo esc_html( number_format( $subtotal, 2 ) ); ?></span>
                        </div>
                        <?php if ( $has_physical ) : ?>
                        <div class="checkout-review__row">
                            <span>Shipping</span>
                            <span>$<?php echo esc_html( number_format( $shipping, 2 ) ); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="checkout-review__row checkout-review__row--total">
                            <span>Total</span>
                            <span>$<?php echo esc_html( number_format( $total, 2 ) ); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn--primary btn--lg btn--full" id="checkout-submit-btn-mobile">
                        Complete Order — $<?php echo esc_html( number_format( $total, 2 ) ); ?>
                    </button>
                    <?php if ( ! $square_config['enabled'] ) : ?>
                    <p class="checkout-review__note">Payment processing not configured</p>
                    <?php endif; ?>
                </div>
                
            </div>
            
            <!-- Desktop Order Summary Sidebar -->
            <div class="checkout-summary checkout-summary--desktop">
                <h3 class="checkout-summary__title">Order Summary</h3>
                
                <div class="checkout-summary__items">
                    <?php foreach ( $cart as $cart_key => $item ) : ?>
                    <div class="checkout-summary__item">
                        <div class="checkout-summary__item-image">
                            <?php if ( $item['thumbnail'] ) : ?>
                                <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="">
                            <?php endif; ?>
                            <span class="checkout-summary__item-qty"><?php echo esc_html( $item['quantity'] ); ?></span>
                        </div>
                        <div class="checkout-summary__item-details">
                            <span class="checkout-summary__item-name"><?php echo esc_html( $item['name'] ); ?></span>
                        </div>
                        <div class="checkout-summary__item-price">
                            $<?php echo esc_html( number_format( $item['price'] * $item['quantity'], 2 ) ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Discount Code -->
                <div class="checkout-discount">
                    <div class="checkout-discount__applied" id="discount-applied" style="<?php echo $applied_discount ? '' : 'display: none;'; ?>">
                        <div class="checkout-discount__badge">
                            <span class="checkout-discount__code" id="applied-code-text"><?php echo $applied_discount ? esc_html( $applied_discount['code'] ) : ''; ?></span>
                            <button type="button" class="checkout-discount__remove" id="remove-discount-btn">×</button>
                        </div>
                        <span class="checkout-discount__savings" id="applied-savings">-$<?php echo number_format( $discount_amount, 2 ); ?></span>
                    </div>
                    <div class="checkout-discount__form" id="discount-form" style="<?php echo $applied_discount ? 'display: none;' : ''; ?>">
                        <input type="text" id="discount-code-input" placeholder="Discount code" class="checkout-discount__input">
                        <button type="button" id="apply-discount-btn" class="checkout-discount__btn">Apply</button>
                    </div>
                    <div class="checkout-discount__error" id="discount-error" style="display: none;"></div>
                </div>
                
                <div class="checkout-summary__totals" id="checkout-totals">
                    <div class="checkout-summary__row">
                        <span>Subtotal</span>
                        <span id="subtotal-display">$<?php echo esc_html( number_format( $subtotal, 2 ) ); ?></span>
                    </div>
                    
                    <div class="checkout-summary__row checkout-summary__row--discount" id="discount-row" style="<?php echo $discount_amount > 0 ? '' : 'display: none;'; ?>">
                        <span>Discount</span>
                        <span class="discount-amount" id="discount-display">-$<?php echo esc_html( number_format( $discount_amount, 2 ) ); ?></span>
                    </div>
                    
                    <?php if ( $has_physical ) : ?>
                    <div class="checkout-summary__row">
                        <span>Shipping</span>
                        <span id="shipping-display">$<?php echo esc_html( number_format( $shipping, 2 ) ); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="checkout-summary__divider"></div>
                    
                    <div class="checkout-summary__row checkout-summary__row--total">
                        <span>Total</span>
                        <span id="total-amount">$<?php echo esc_html( number_format( $total, 2 ) ); ?></span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn--primary btn--lg btn--full" id="checkout-submit-btn">
                    Complete Order — $<?php echo esc_html( number_format( $total, 2 ) ); ?>
                </button>
                <?php if ( ! $square_config['enabled'] ) : ?>
                <p class="checkout-summary__note">Payment processing not configured</p>
                <?php endif; ?>
                
                <a href="<?php echo esc_url( home_url( '/cart/' ) ); ?>" class="checkout-summary__back">
                    ← Return to Cart
                </a>
            </div>
            
        </form>
    </div>
</section>

<script>
// Toggle signature message field
document.querySelector('input[name="signature_request"]')?.addEventListener('change', function() {
    document.getElementById('signature-message-group').style.display = this.checked ? 'block' : 'none';
});

// Toggle account creation fields
const createAccountCheckbox = document.getElementById('create_account');
const accountFields = document.getElementById('account-fields');
const passwordInput = document.getElementById('account_password');
const passwordConfirm = document.getElementById('account_password_confirm');

function updateAccountFields(checked) {
    if (!accountFields) return;
    
    if (checked) {
        accountFields.style.display = 'block';
        if (passwordInput) passwordInput.required = true;
        if (passwordConfirm) passwordConfirm.required = true;
    } else {
        accountFields.style.display = 'none';
        if (passwordInput) {
            passwordInput.required = false;
            passwordInput.value = '';
        }
        if (passwordConfirm) {
            passwordConfirm.required = false;
            passwordConfirm.value = '';
        }
    }
}

// Ensure checkbox is always checked on page load (override browser form restoration)
if (createAccountCheckbox) {
    createAccountCheckbox.checked = true;
    updateAccountFields(true);
    
    createAccountCheckbox.addEventListener('change', function() {
        updateAccountFields(this.checked);
    });
}

// Mobile summary toggle
document.getElementById('mobile-summary-toggle')?.addEventListener('click', function() {
    const content = document.getElementById('mobile-summary-content');
    const chevron = this.querySelector('.checkout-summary__chevron');
    content.classList.toggle('is-expanded');
    chevron.classList.toggle('is-rotated');
});

// Update review section with form data
function updateReviewSection() {
    const firstName = document.getElementById('first_name')?.value || '';
    const lastName = document.getElementById('last_name')?.value || '';
    const email = document.getElementById('email')?.value || '';
    const phone = document.getElementById('phone')?.value || '';
    
    const reviewContact = document.getElementById('review-contact');
    if (reviewContact) {
        if (firstName || lastName || email) {
            let contactHtml = '';
            if (firstName || lastName) {
                contactHtml += `<strong>${firstName} ${lastName}</strong><br>`;
            }
            if (email) {
                contactHtml += `${email}`;
            }
            if (phone) {
                contactHtml += `<br>${phone}`;
            }
            reviewContact.innerHTML = contactHtml || '<span class="checkout-review__placeholder">Complete contact info above</span>';
        }
    }
    
    const address1 = document.getElementById('address_1')?.value || '';
    const address2 = document.getElementById('address_2')?.value || '';
    const city = document.getElementById('city')?.value || '';
    const state = document.getElementById('state')?.value || '';
    const zip = document.getElementById('zip')?.value || '';
    const country = document.getElementById('country')?.value || '';
    
    const reviewAddress = document.getElementById('review-address');
    if (reviewAddress) {
        if (address1 && city && state && zip) {
            let addressHtml = `<strong>${firstName} ${lastName}</strong><br>`;
            addressHtml += address1;
            if (address2) addressHtml += `, ${address2}`;
            addressHtml += `<br>${city}, ${state} ${zip}`;
            if (country === 'CA') addressHtml += '<br>Canada';
            reviewAddress.innerHTML = addressHtml;
        } else {
            reviewAddress.innerHTML = '<span class="checkout-review__placeholder">Complete shipping address above</span>';
        }
    }
}

// Listen for input changes
document.querySelectorAll('#checkout-form input, #checkout-form select').forEach(function(input) {
    input.addEventListener('input', updateReviewSection);
    input.addEventListener('change', updateReviewSection);
});

// Initial update
updateReviewSection();

<?php if ( $square_config['enabled'] ) : ?>
// Square Web Payments SDK Integration
(async function() {
    const appId = '<?php echo esc_js( $square_config['application_id'] ); ?>';
    const locationId = '<?php echo esc_js( $square_config['location_id'] ); ?>';
    
    if (!appId || !locationId) {
        console.error('Square configuration missing');
        return;
    }

    // Load Square Web Payments SDK
    const script = document.createElement('script');
    script.src = '<?php echo $square_config['sandbox'] ? 'https://sandbox.web.squarecdn.com/v1/square.js' : 'https://web.squarecdn.com/v1/square.js'; ?>';
    script.async = true;
    
    script.onload = async function() {
        try {
            const payments = window.Square.payments(appId, locationId);
            
            // Card styling
            const cardStyle = {
                '.input-container': {
                    borderColor: 'rgba(185, 215, 255, 0.3)',
                    borderRadius: '8px',
                },
                '.input-container.is-focus': {
                    borderColor: 'rgba(185, 215, 255, 0.8)',
                },
                '.input-container.is-error': {
                    borderColor: '#ff6464',
                },
                '.message-text': {
                    color: '#b9d7ff',
                },
                '.message-icon': {
                    color: '#b9d7ff',
                },
                input: {
                    color: '#1a1a1a',
                    backgroundColor: '#ffffff',
                    fontSize: '16px',
                },
                'input::placeholder': {
                    color: 'rgba(0, 0, 0, 0.4)',
                },
            };
            
            const card = await payments.card({ 
                style: cardStyle
            });
            await card.attach('#card-container');
            
            // Handle form submission
            const form = document.getElementById('checkout-form');
            const submitBtn = document.getElementById('checkout-submit-btn');
            const submitBtnMobile = document.getElementById('checkout-submit-btn-mobile');
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Disable submit buttons
                submitBtn.disabled = true;
                submitBtnMobile.disabled = true;
                submitBtn.textContent = 'Processing...';
                submitBtnMobile.textContent = 'Processing...';
                
                try {
                    // Tokenize card
                    const result = await card.tokenize();
                    
                    if (result.status === 'OK') {
                        // Collect form data
                        const formData = new FormData(form);
                        const customerData = {
                            first_name: formData.get('first_name'),
                            last_name: formData.get('last_name'),
                            email: formData.get('email'),
                            phone: formData.get('phone'),
                            address_1: formData.get('address_1'),
                            address_2: formData.get('address_2'),
                            city: formData.get('city'),
                            state: formData.get('state'),
                            zip: formData.get('zip'),
                            country: formData.get('country'),
                            signature_request: formData.get('signature_request'),
                            signature_message: formData.get('signature_message'),
                            create_account: formData.get('create_account'),
                            account_password: formData.get('account_password'),
                            subscribe_newsletter: formData.get('subscribe_newsletter'),
                        };
                        
                        // Send payment to server
                        const response = await fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'dbc_process_payment',
                                nonce: '<?php echo wp_create_nonce( 'dbc-checkout-nonce' ); ?>',
                                source_id: result.token,
                                amount: <?php echo $total; ?>,
                                customer: JSON.stringify(customerData),
                                cart_items: JSON.stringify(<?php echo wp_json_encode( array_values( $cart ) ); ?>),
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Redirect to order confirmation
                            window.location.href = data.data.redirect;
                        } else {
                            // Show error
                            alert('Payment failed: ' + (data.data.message || 'Unknown error'));
                            submitBtn.disabled = false;
                            submitBtnMobile.disabled = false;
                            submitBtn.textContent = 'Complete Order — $<?php echo number_format( $total, 2 ); ?>';
                            submitBtnMobile.textContent = 'Complete Order — $<?php echo number_format( $total, 2 ); ?>';
                        }
                    } else {
                        // Tokenization error
                        let errorMessage = 'Payment failed. Please check your card details.';
                        if (result.errors) {
                            errorMessage = result.errors.map(e => e.message).join(', ');
                        }
                        alert(errorMessage);
                        submitBtn.disabled = false;
                        submitBtnMobile.disabled = false;
                        submitBtn.textContent = 'Complete Order — $<?php echo number_format( $total, 2 ); ?>';
                        submitBtnMobile.textContent = 'Complete Order — $<?php echo number_format( $total, 2 ); ?>';
                    }
                } catch (error) {
                    console.error('Payment error:', error);
                    alert('Payment failed: ' + error.message);
                    submitBtn.disabled = false;
                    submitBtnMobile.disabled = false;
                    submitBtn.textContent = 'Complete Order — $<?php echo number_format( $total, 2 ); ?>';
                    submitBtnMobile.textContent = 'Complete Order — $<?php echo number_format( $total, 2 ); ?>';
                }
            });
            
        } catch (error) {
            console.error('Square initialization error:', error);
            document.getElementById('payment-status-container').innerHTML = '<p style="color: red;">Payment system initialization failed. Please refresh the page.</p>';
            document.getElementById('payment-status-container').style.display = 'block';
        }
    };
    
    script.onerror = function() {
        console.error('Failed to load Square SDK');
        document.getElementById('payment-status-container').innerHTML = '<p style="color: red;">Failed to load payment system. Please refresh the page.</p>';
        document.getElementById('payment-status-container').style.display = 'block';
    };
    
    document.head.appendChild(script);
})();
<?php endif; ?>

// Discount Code Handling
(function() {
    const applyBtn = document.getElementById('apply-discount-btn');
    const removeBtn = document.getElementById('remove-discount-btn');
    const codeInput = document.getElementById('discount-code-input');
    const errorDiv = document.getElementById('discount-error');
    const discountForm = document.getElementById('discount-form');
    const discountApplied = document.getElementById('discount-applied');
    const appliedCodeText = document.getElementById('applied-code-text');
    const appliedSavings = document.getElementById('applied-savings');
    const discountRow = document.getElementById('discount-row');
    const discountDisplay = document.getElementById('discount-display');
    const totalAmount = document.getElementById('total-amount');
    const completeOrderBtn = document.querySelector('.checkout-submit');
    
    // Store original values
    const subtotal = <?php echo $subtotal; ?>;
    const shipping = <?php echo $shipping; ?>;
    let currentDiscount = <?php echo $discount_amount; ?>;
    
    if (applyBtn && codeInput) {
        applyBtn.addEventListener('click', function() {
            const code = codeInput.value.trim().toUpperCase();
            
            if (!code) {
                showError('Please enter a discount code');
                return;
            }
            
            applyBtn.disabled = true;
            applyBtn.textContent = 'Applying...';
            hideError();
            
            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'dbc_apply_discount',
                    nonce: '<?php echo wp_create_nonce( 'dbc_cart_nonce' ); ?>',
                    code: code
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI dynamically
                    currentDiscount = data.data.discount_amount;
                    const newTotal = data.data.new_total;
                    
                    // Show applied discount badge
                    if (appliedCodeText) appliedCodeText.textContent = data.data.code;
                    if (appliedSavings) appliedSavings.textContent = data.data.discount_display;
                    if (discountForm) discountForm.style.display = 'none';
                    if (discountApplied) discountApplied.style.display = 'flex';
                    
                    // Show discount row in totals
                    if (discountRow) {
                        discountRow.style.display = 'flex';
                        if (discountDisplay) discountDisplay.textContent = data.data.discount_display;
                    }
                    
                    // Update total
                    if (totalAmount) totalAmount.textContent = '$' + newTotal.toFixed(2);
                    if (completeOrderBtn) completeOrderBtn.textContent = 'Complete Order — $' + newTotal.toFixed(2);
                    
                    // Clear input
                    codeInput.value = '';
                    
                    // Show success message briefly
                    showSuccess(data.data.message);
                } else {
                    showError(data.data.message || 'Invalid discount code');
                }
                applyBtn.disabled = false;
                applyBtn.textContent = 'Apply';
            })
            .catch(error => {
                console.error('Discount error:', error);
                showError('Connection error. Please try again.');
                applyBtn.disabled = false;
                applyBtn.textContent = 'Apply';
            });
        });
        
        // Enter key to apply
        codeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyBtn.click();
            }
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'dbc_remove_discount',
                    nonce: '<?php echo wp_create_nonce( 'dbc_cart_nonce' ); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                // Update UI dynamically
                currentDiscount = 0;
                const newTotal = subtotal + shipping;
                
                // Hide applied discount badge, show form
                if (discountApplied) discountApplied.style.display = 'none';
                if (discountForm) discountForm.style.display = 'flex';
                
                // Hide discount row in totals
                if (discountRow) discountRow.style.display = 'none';
                
                // Update total
                if (totalAmount) totalAmount.textContent = '$' + newTotal.toFixed(2);
                if (completeOrderBtn) completeOrderBtn.textContent = 'Complete Order — $' + newTotal.toFixed(2);
            });
        });
    }
    
    function showError(message) {
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.style.color = '#ef4444';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
    }
    
    function showSuccess(message) {
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.style.color = '#22c55e';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 3000);
        }
    }
    
    function hideError() {
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }
})();
</script>

<?php get_footer(); ?>
