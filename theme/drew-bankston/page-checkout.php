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
$total = DBC_Cart::get_total();
$has_physical = DBC_Cart::has_physical_items();

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
            <h1 class="hero__title">Checkout</h1>
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
                    <h2 class="checkout-block__title">Payment</h2>
                    
                    <div class="payment-placeholder">
                        <div class="payment-placeholder__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                        </div>
                        <p class="payment-placeholder__text">
                            <strong>Payment processing coming soon!</strong><br>
                            Square payment integration will be available here.
                        </p>
                    </div>
                    
                    <!-- This is where Square Web Payments SDK will be integrated -->
                    <div id="square-payment-form" style="display: none;">
                        <!-- Card input will be injected here -->
                    </div>
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
                    
                    <button type="submit" class="btn btn--primary btn--lg btn--full" disabled>
                        Complete Order — $<?php echo esc_html( number_format( $total, 2 ) ); ?>
                    </button>
                    <p class="checkout-review__note">Payment integration coming soon</p>
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
                
                <button type="submit" class="btn btn--primary btn--lg btn--full" disabled>
                    Complete Order
                </button>
                <p class="checkout-summary__note">Payment integration coming soon</p>
                
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
</script>

<?php get_footer(); ?>
