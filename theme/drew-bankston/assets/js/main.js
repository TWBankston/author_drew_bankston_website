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

        // Hero title animation
        const heroTitle = document.querySelector('.hero__title');
        if (heroTitle) {
            gsap.fromTo(heroTitle,
                { opacity: 0, y: 40 },
                { opacity: 1, y: 0, duration: 1, ease: 'power3.out', delay: 0.2 }
            );
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

        // Hero background parallax
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

        // Hero title slight parallax
        const heroTitle = document.querySelector('.hero__title');
        if (heroTitle) {
            gsap.to(heroTitle, {
                y: 50,
                ease: 'none',
                scrollTrigger: {
                    trigger: '.hero',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true
                }
            });
        }
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

})();

