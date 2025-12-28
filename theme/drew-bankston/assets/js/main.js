/**
 * Drew Bankston Theme - Main JavaScript
 * GSAP animations, Lottie integration, and interactivity
 */

(function() {
    'use strict';

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Initialize when DOM is ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initHeader();
        initMobileMenu();
        initBookFilters();
        
        if (!prefersReducedMotion) {
            initGSAPAnimations();
            initParallax();
        }
    });

    /**
     * Header scroll effect
     */
    function initHeader() {
        const header = document.getElementById('site-header');
        if (!header) return;

        let lastScroll = 0;
        const scrollThreshold = 50;

        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > scrollThreshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        }, { passive: true });
    }

    /**
     * Mobile menu toggle
     */
    function initMobileMenu() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const nav = document.getElementById('mobile-nav');
        const closeBtn = document.querySelector('.mobile-nav__close');
        const links = document.querySelectorAll('.mobile-nav__menu a');

        if (!toggle || !nav) return;

        function openMenu() {
            nav.classList.add('active');
            toggle.classList.add('active');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            
            // GSAP animation for menu items
            if (typeof gsap !== 'undefined' && !prefersReducedMotion) {
                gsap.fromTo('.mobile-nav__menu a', 
                    { opacity: 0, y: 20 },
                    { opacity: 1, y: 0, duration: 0.4, stagger: 0.1, ease: 'power2.out' }
                );
            }
        }

        function closeMenu() {
            nav.classList.remove('active');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function() {
            if (nav.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeMenu);
        }

        // Close on link click
        links.forEach(function(link) {
            link.addEventListener('click', closeMenu);
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && nav.classList.contains('active')) {
                closeMenu();
            }
        });

        // Close on click outside
        nav.addEventListener('click', function(e) {
            if (e.target === nav) {
                closeMenu();
            }
        });
    }

    /**
     * Book archive filters
     */
    function initBookFilters() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const bookCards = document.querySelectorAll('.book-card[data-categories]');

        if (!filterBtns.length || !bookCards.length) return;

        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Update active state
                filterBtns.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                
                // Filter books
                bookCards.forEach(function(card) {
                    if (filter === 'all') {
                        card.style.display = '';
                    } else {
                        const categories = card.dataset.categories || '';
                        if (categories.includes(filter)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
                
                // Animate visible cards
                if (typeof gsap !== 'undefined' && !prefersReducedMotion) {
                    gsap.fromTo('.book-card:not([style*="display: none"])',
                        { opacity: 0, y: 20 },
                        { opacity: 1, y: 0, duration: 0.4, stagger: 0.05, ease: 'power2.out' }
                    );
                }
            });
        });
    }

    /**
     * GSAP reveal animations
     */
    function initGSAPAnimations() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        gsap.registerPlugin(ScrollTrigger);

        // Reveal animations for elements with .gsap-reveal class
        gsap.utils.toArray('.gsap-reveal').forEach(function(elem) {
            gsap.fromTo(elem, 
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    }
                }
            );
        });

        // Staggered animations for grids
        gsap.utils.toArray('.books-grid, .standalones-grid, .series-books').forEach(function(grid) {
            const cards = grid.querySelectorAll('.book-card');
            if (!cards.length) return;

            gsap.fromTo(cards,
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    stagger: 0.1,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: grid,
                        start: 'top 80%',
                        toggleActions: 'play none none none'
                    }
                }
            );
        });

        // Hero title typewriter animation
        const heroTitle = document.querySelector('.hero__title');
        if (heroTitle) {
            initTypewriter(heroTitle, {
                typingSpeed: 80,
                startDelay: 300,
                cursorBlinkAfter: true
            });
        }

        const heroSubtitle = document.querySelector('.hero__subtitle');
        if (heroSubtitle) {
            gsap.fromTo(heroSubtitle,
                { opacity: 0, y: 30 },
                { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out', delay: 0.4 }
            );
        }

        const heroCta = document.querySelector('.hero__cta');
        if (heroCta) {
            gsap.fromTo(heroCta,
                { opacity: 0, y: 20 },
                { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out', delay: 0.6 }
            );
        }
    }

    /**
     * Parallax effects
     */
    function initParallax() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        // Hero background parallax (Lottie only)
        const heroBg = document.querySelector('.hero__lottie-bg');
        if (heroBg) {
            gsap.to(heroBg, {
                y: 100,
                ease: 'none',
                scrollTrigger: {
                    trigger: '.hero',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true
                }
            });
        }

        // No parallax on hero text content - keep it static after intro animation
    }

    /**
     * Lottie player initialization
     * Lottie players are auto-initialized by the lottie-player library
     * This function adds any custom handling
     */
    function initLottie() {
        // Lottie players initialize automatically
        // Add custom handling here if needed
    }

    /**
     * Typewriter effect for text elements
     * @param {HTMLElement} element - The element containing text to animate
     * @param {Object} options - Configuration options
     */
    function initTypewriter(element, options) {
        const defaults = {
            typingSpeed: 100,      // ms per character
            startDelay: 500,       // ms before typing starts
            cursorBlinkAfter: true // keep cursor blinking after typing
        };
        
        const settings = { ...defaults, ...options };
        const originalText = element.textContent.trim();
        
        // Set up the element structure
        element.classList.add('hero__title--typewriter');
        element.innerHTML = '<span class="typewriter-text"></span><span class="typewriter-cursor typing"></span>';
        
        const textSpan = element.querySelector('.typewriter-text');
        const cursor = element.querySelector('.typewriter-cursor');
        
        let charIndex = 0;
        
        function typeNextChar() {
            if (charIndex < originalText.length) {
                textSpan.textContent += originalText.charAt(charIndex);
                charIndex++;
                
                // Vary the speed slightly for more natural feel
                const variance = Math.random() * 40 - 20; // -20 to +20ms
                setTimeout(typeNextChar, settings.typingSpeed + variance);
            } else {
                // Typing complete
                cursor.classList.remove('typing');
                
                if (!settings.cursorBlinkAfter) {
                    // Hide cursor after a short delay
                    setTimeout(function() {
                        cursor.classList.add('hidden');
                    }, 1500);
                }
            }
        }
        
        // Start typing after delay
        setTimeout(typeNextChar, settings.startDelay);
    }

    /**
     * Free Chapter Modal
     */
    document.addEventListener('DOMContentLoaded', function() {
        initFreeChapterModal();
    });

    function initFreeChapterModal() {
        const modal = document.getElementById('free-chapter-modal');
        if (!modal) return;

        const form = document.getElementById('free-chapter-form');
        const formState = document.getElementById('modal-form-state');
        const successState = document.getElementById('modal-success-state');
        const errorState = document.getElementById('modal-error-state');
        const bookTitleEl = document.getElementById('modal-book-title');
        const bookIdInput = document.getElementById('modal-book-id');
        const downloadLink = document.getElementById('modal-download-link');
        const errorMessage = document.getElementById('modal-error-message');
        const tryAgainBtn = document.getElementById('modal-try-again');
        const submitBtn = modal.querySelector('.modal__submit');
        const submitText = modal.querySelector('.modal__submit-text');
        const submitLoading = modal.querySelector('.modal__submit-loading');

        // Open modal when clicking free chapter buttons
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-free-chapter]');
            if (trigger) {
                e.preventDefault();
                const bookId = trigger.dataset.bookId;
                const bookTitle = trigger.dataset.bookTitle;
                
                bookIdInput.value = bookId;
                bookTitleEl.textContent = bookTitle;
                
                // Check if user already has access (logged in or subscribed)
                checkDownloadAccess(bookId, bookTitle);
            }
        });

        function checkDownloadAccess(bookId, bookTitle) {
            const formData = new FormData();
            formData.append('action', 'dbc_check_download_access');
            formData.append('book_id', bookId);

            fetch(dbtData.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success && data.data.has_access) {
                    // User has access - show success state with download link
                    downloadLink.href = data.data.download_url;
                    showState('success');
                    openModal();
                } else {
                    // User needs to subscribe - show form
                    showState('form');
                    openModal();
                }
            })
            .catch(function(error) {
                // On error, show the form
                console.error('Access check error:', error);
                showState('form');
                openModal();
            });
        }

        // Close modal
        modal.querySelectorAll('[data-modal-close]').forEach(function(el) {
            el.addEventListener('click', closeModal);
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) {
                closeModal();
            }
        });

        // Try again button
        if (tryAgainBtn) {
            tryAgainBtn.addEventListener('click', function() {
                showState('form');
            });
        }

        // Form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const firstName = document.getElementById('modal-first-name').value.trim();
                const lastName = document.getElementById('modal-last-name').value.trim();
                const email = document.getElementById('modal-email').value.trim();
                const bookId = bookIdInput.value;

                if (!firstName || !lastName) {
                    showError('Please enter your first and last name.');
                    return;
                }

                if (!email) {
                    showError('Please enter your email address.');
                    return;
                }

                // Disable submit button
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoading.style.display = 'inline';

                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'dbc_newsletter_subscribe');
                formData.append('nonce', dbtData.newsletterNonce || '');
                formData.append('first_name', firstName);
                formData.append('last_name', lastName);
                formData.append('email', email);
                formData.append('book_id', bookId);

                fetch(dbtData.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        downloadLink.href = data.data.download_url;
                        showState('success');
                    } else {
                        showError(data.data.message || 'Something went wrong. Please try again.');
                    }
                })
                .catch(function(error) {
                    console.error('Newsletter error:', error);
                    showError('Connection error. Please check your internet and try again.');
                })
                .finally(function() {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoading.style.display = 'none';
                });
            });
        }

        function openModal() {
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';
            
            // Focus first input
            setTimeout(function() {
                const firstInput = modal.querySelector('input:not([type="hidden"])');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function closeModal() {
            modal.classList.remove('is-active');
            document.body.style.overflow = '';
            
            // Reset form
            if (form) form.reset();
        }

        function showState(state) {
            formState.style.display = state === 'form' ? 'block' : 'none';
            successState.style.display = state === 'success' ? 'block' : 'none';
            errorState.style.display = state === 'error' ? 'block' : 'none';
        }

        function showError(message) {
            errorMessage.textContent = message;
            showState('error');
        }
    }

    /**
     * Shopping Cart Functionality
     */
    document.addEventListener('DOMContentLoaded', function() {
        initCart();
        initCartPage();
    });

    function initCart() {
        // Handle purchase option clicks (add to cart)
        document.querySelectorAll('[data-purchase]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const bookId = this.dataset.bookId;
                const type = this.dataset.purchase; // 'signed' or 'digital'
                const bookTitle = this.dataset.bookTitle;
                
                addToCart(bookId, type, bookTitle);
            });
        });
    }

    function addToCart(bookId, type, bookTitle) {
        const nonce = typeof dbcAjax !== 'undefined' ? dbcAjax.cartNonce : '';
        
        if (!nonce) {
            console.log('Cart nonce not available');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'dbc_add_to_cart');
        formData.append('nonce', nonce);
        formData.append('book_id', bookId);
        formData.append('type', type);
        formData.append('quantity', 1);
        
        fetch(dbcAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in header
                updateHeaderCartCount(data.data.cart_count);
                
                // Show success feedback
                showCartNotification(bookTitle + ' added to cart!');
            } else {
                showCartNotification(data.data.message || 'Error adding to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Cart error:', error);
            showCartNotification('Error adding to cart', 'error');
        });
    }

    function updateHeaderCartCount(count) {
        const badge = document.getElementById('header-cart-count');
        const cartLink = document.getElementById('header-cart-link');
        
        if (count > 0) {
            // Show cart link
            if (cartLink) {
                cartLink.classList.remove('is-hidden');
            }
            // Update badge
            if (badge) {
                badge.classList.remove('is-hidden');
                badge.textContent = count;
            }
        } else {
            // Hide cart link and badge
            if (cartLink) {
                cartLink.classList.add('is-hidden');
            }
            if (badge) {
                badge.classList.add('is-hidden');
            }
        }
        
        // Also update cart page header count if on cart page
        updateCartPageCount(count);
    }
    
    function updateCartPageCount(count) {
        const heroSubtitle = document.getElementById('cart-page-count');
        if (heroSubtitle) {
            heroSubtitle.textContent = count + ' item' + (count !== 1 ? 's' : '') + ' in your cart';
        }
    }

    function isDesktop() {
        return window.innerWidth > 768;
    }

    function showCartNotification(message, type = 'success') {
        if (isDesktop()) {
            showCartDrawer();
        } else {
            showCartToast(message, type);
        }
    }
    
    function showCartToast(message, type = 'success') {
        // Remove existing notification
        const existing = document.querySelector('.cart-notification');
        if (existing) existing.remove();
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = 'cart-notification cart-notification--' + type;
        notification.innerHTML = `
            <span>${message}</span>
            <a href="${dbcAjax.cartUrl || '/cart/'}" class="cart-notification__link">View Cart →</a>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.classList.add('is-visible');
        });
        
        // Remove after delay
        setTimeout(() => {
            notification.classList.remove('is-visible');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
    
    function showCartDrawer() {
        // Fetch current cart and show drawer
        fetch(dbcAjax.ajaxUrl + '?action=dbc_get_cart', {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCartDrawer(data.data);
            }
        });
    }
    
    function renderCartDrawer(cartData) {
        // Remove existing drawer
        const existing = document.querySelector('.cart-drawer');
        const existingOverlay = document.querySelector('.cart-drawer__overlay');
        if (existing) existing.remove();
        if (existingOverlay) existingOverlay.remove();
        
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'cart-drawer__overlay';
        overlay.addEventListener('click', closeCartDrawer);
        
        // Create drawer
        const drawer = document.createElement('div');
        drawer.className = 'cart-drawer';
        
        // Build items HTML
        let itemsHtml = '';
        if (Object.keys(cartData.cart).length === 0) {
            itemsHtml = '<div class="cart-drawer__empty">Your cart is empty</div>';
        } else {
            itemsHtml = '<div class="cart-drawer__items">';
            for (const [key, item] of Object.entries(cartData.cart)) {
                itemsHtml += `
                    <div class="cart-drawer__item">
                        <div class="cart-drawer__item-image">
                            ${item.thumbnail ? `<img src="${item.thumbnail}" alt="">` : ''}
                        </div>
                        <div class="cart-drawer__item-info">
                            <div class="cart-drawer__item-name">${item.name}</div>
                            <div class="cart-drawer__item-type">Qty: ${item.quantity}</div>
                            <div class="cart-drawer__item-price">$${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                    </div>
                `;
            }
            itemsHtml += '</div>';
        }
        
        drawer.innerHTML = `
            <div class="cart-drawer__header">
                <h3 class="cart-drawer__title">Your Cart</h3>
                <button class="cart-drawer__close" aria-label="Close cart">&times;</button>
            </div>
            <div class="cart-drawer__content">
                ${itemsHtml}
            </div>
            <div class="cart-drawer__footer">
                <div class="cart-drawer__totals">
                    <div class="cart-drawer__row">
                        <span>Subtotal</span>
                        <span>$${cartData.subtotal.toFixed(2)}</span>
                    </div>
                    ${cartData.has_physical ? `
                    <div class="cart-drawer__row">
                        <span>Shipping</span>
                        <span>$${cartData.shipping.toFixed(2)}</span>
                    </div>
                    ` : ''}
                    <div class="cart-drawer__row cart-drawer__row--total">
                        <span>Total</span>
                        <span>$${cartData.total.toFixed(2)}</span>
                    </div>
                </div>
                <div class="cart-drawer__actions">
                    <a href="${dbcAjax.cartUrl || '/cart/'}" class="btn btn--primary">View Cart</a>
                    <a href="/checkout/" class="btn btn--secondary">Checkout</a>
                    <button class="cart-drawer__continue">Continue Shopping</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        document.body.appendChild(drawer);
        
        // Bind close handlers
        drawer.querySelector('.cart-drawer__close').addEventListener('click', closeCartDrawer);
        drawer.querySelector('.cart-drawer__continue').addEventListener('click', closeCartDrawer);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Animate in
        requestAnimationFrame(() => {
            overlay.classList.add('is-visible');
            drawer.classList.add('is-open');
        });
    }
    
    function closeCartDrawer() {
        const drawer = document.querySelector('.cart-drawer');
        const overlay = document.querySelector('.cart-drawer__overlay');
        
        if (drawer) drawer.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
        
        document.body.style.overflow = '';
        
        setTimeout(() => {
            if (drawer) drawer.remove();
            if (overlay) overlay.remove();
        }, 300);
    }

    function initCartPage() {
        const cartItems = document.getElementById('cart-items');
        if (!cartItems) return;
        
        const nonce = typeof dbcAjax !== 'undefined' ? dbcAjax.cartNonce : '';
        
        // Quantity controls
        cartItems.addEventListener('click', function(e) {
            const button = e.target.closest('[data-action]');
            if (!button) return;
            
            const cartItem = button.closest('.cart-item');
            const cartKey = cartItem.dataset.cartKey;
            const action = button.dataset.action;
            
            if (action === 'remove') {
                removeFromCart(cartKey, cartItem, nonce);
            } else if (action === 'increase' || action === 'decrease') {
                const input = cartItem.querySelector('.quantity-control__input');
                let qty = parseInt(input.value) || 1;
                
                if (action === 'increase') qty++;
                if (action === 'decrease') qty = Math.max(1, qty - 1);
                
                input.value = qty;
                updateCartQuantity(cartKey, qty, cartItem, nonce);
            }
        });
        
        // Direct quantity input
        cartItems.addEventListener('change', function(e) {
            if (!e.target.classList.contains('quantity-control__input')) return;
            
            const cartItem = e.target.closest('.cart-item');
            const cartKey = cartItem.dataset.cartKey;
            const qty = Math.max(1, parseInt(e.target.value) || 1);
            
            e.target.value = qty;
            updateCartQuantity(cartKey, qty, cartItem, nonce);
        });
    }

    function updateCartQuantity(cartKey, quantity, cartItem, nonce) {
        const formData = new FormData();
        formData.append('action', 'dbc_update_cart');
        formData.append('nonce', nonce);
        formData.append('cart_key', cartKey);
        formData.append('quantity', quantity);
        
        fetch(dbcAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartTotals(data.data);
                updateHeaderCartCount(data.data.cart_count);
                
                // Update item subtotal
                const item = data.data.cart[cartKey];
                if (item) {
                    const subtotalEl = cartItem.querySelector('.cart-item__subtotal-value');
                    if (subtotalEl) {
                        subtotalEl.textContent = '$' + (item.price * item.quantity).toFixed(2);
                    }
                }
            }
        });
    }

    function removeFromCart(cartKey, cartItem, nonce) {
        const formData = new FormData();
        formData.append('action', 'dbc_remove_from_cart');
        formData.append('nonce', nonce);
        formData.append('cart_key', cartKey);
        
        fetch(dbcAjax.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Animate out and remove
                cartItem.style.opacity = '0';
                cartItem.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    cartItem.remove();
                    updateCartTotals(data.data);
                    updateHeaderCartCount(data.data.cart_count);
                    
                    // If cart is empty, reload to show empty state
                    if (data.data.cart_count === 0) {
                        location.reload();
                    }
                }, 300);
            }
        });
    }

    function updateCartTotals(data) {
        const subtotalEl = document.getElementById('cart-subtotal');
        const shippingEl = document.getElementById('cart-shipping');
        const totalEl = document.getElementById('cart-total');
        
        if (subtotalEl) subtotalEl.textContent = '$' + data.subtotal.toFixed(2);
        if (shippingEl) shippingEl.textContent = '$' + data.shipping.toFixed(2);
        if (totalEl) totalEl.textContent = '$' + data.total.toFixed(2);
    }

})();

