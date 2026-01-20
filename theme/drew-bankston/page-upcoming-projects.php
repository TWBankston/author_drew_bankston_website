<?php
/**
 * Template Name: Upcoming Projects Page
 * 
 * Displays upcoming book projects with:
 * - Hero section
 * - Intro paragraph
 * - Project cards for upcoming books
 */

get_header();
?>

<!-- Upcoming Projects Hero -->
<section class="hero hero--upcoming">
    <div class="hero__bg"></div>
    
    <!-- Ambient Background Effects -->
    <div class="hero__ambient" aria-hidden="true">
        <div class="hero__ambient-orb hero__ambient-orb--violet"></div>
        <div class="hero__ambient-orb hero__ambient-orb--indigo"></div>
        <div class="hero__ambient-orb hero__ambient-orb--cyan"></div>
    </div>
    
    <div class="container">
        <div class="hero__content hero__content--centered">
            <p class="hero__eyebrow">What's Next</p>
            <h1 class="hero__title">Upcoming Projects</h1>
            <p class="hero__subtitle">New adventures are on the horizon. Get a glimpse of the stories currently in the works.</p>
        </div>
    </div>
</section>

<!-- Intro Section -->
<section class="section">
    <div class="container container--narrow">
        <div class="upcoming-intro">
            <p class="upcoming-intro__text">
                Drew is always working on new stories to share with readers. Below you'll find projects currently in development. 
                Sign up for the community newsletter to be the first to know when these books become available.
            </p>
        </div>
    </div>
</section>

<!-- Upcoming Projects Grid -->
<section class="section upcoming-projects">
    <div class="container">
        <div class="upcoming-grid">
            
            <!-- Project 1: Cornerstone -->
            <div class="upcoming-card">
                <div class="upcoming-card__cover">
                    <img src="<?php echo esc_url( DBT_URL . '/assets/images/book-covers/cornerstone.jpg' ); ?>" alt="Cornerstone - Book 3 in the Tokorel Series">
                    <div class="upcoming-card__badge">
                        <span>Coming Soon</span>
                    </div>
                </div>
                <div class="upcoming-card__content">
                    <span class="upcoming-card__series">Tokorel Series - Book 3</span>
                    <h2 class="upcoming-card__title">Cornerstone</h2>
                    <p class="upcoming-card__description">
                        The epic saga continues. Description coming soon...
                    </p>
                    <div class="upcoming-card__actions">
                        <a href="<?php echo esc_url( home_url( '/series/tokorel/' ) ); ?>" class="btn btn--secondary">Explore Series</a>
                    </div>
                </div>
            </div>
            
            <!-- Project 2: Mystery Placeholder -->
            <div class="upcoming-card upcoming-card--mystery">
                <div class="upcoming-card__cover upcoming-card__cover--mystery">
                    <!-- Magical Placeholder Background -->
                    <div class="mystery-placeholder">
                        <div class="mystery-placeholder__bg">
                            <div class="mystery-placeholder__stars"></div>
                            <div class="mystery-placeholder__nebula"></div>
                        </div>
                        
                        <!-- Animated Elements -->
                        <div class="mystery-placeholder__orbs">
                            <div class="mystery-orb mystery-orb--1"></div>
                            <div class="mystery-orb mystery-orb--2"></div>
                            <div class="mystery-orb mystery-orb--3"></div>
                        </div>
                        
                        <!-- Central Icon -->
                        <div class="mystery-placeholder__icon">
                            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Magic sparkle/question mark hybrid -->
                                <defs>
                                    <linearGradient id="mysteryGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#a78bfa"/>
                                        <stop offset="50%" stop-color="#22d3ee"/>
                                        <stop offset="100%" stop-color="#f472b6"/>
                                    </linearGradient>
                                    <filter id="mysteryGlow">
                                        <feGaussianBlur stdDeviation="3" result="blur"/>
                                        <feMerge>
                                            <feMergeNode in="blur"/>
                                            <feMergeNode in="SourceGraphic"/>
                                        </feMerge>
                                    </filter>
                                </defs>
                                <!-- Question mark with sparkles -->
                                <!-- The dot at bottom of question mark -->
                                <circle cx="50" cy="75" r="5" fill="url(#mysteryGradient)" filter="url(#mysteryGlow)"/>
                                <!-- The curved part of question mark -->
                                <path d="M50 60 L50 55" stroke="url(#mysteryGradient)" stroke-width="5" stroke-linecap="round" filter="url(#mysteryGlow)"/>
                                <path d="M50 48 C50 40, 62 38, 62 30 C62 22, 50 18, 40 24 C35 28, 34 32, 34 36" stroke="url(#mysteryGradient)" stroke-width="5" stroke-linecap="round" fill="none" filter="url(#mysteryGlow)"/>
                                <!-- Sparkles -->
                                <circle cx="75" cy="25" r="2" fill="#a78bfa" class="sparkle sparkle--1"/>
                                <circle cx="25" cy="30" r="1.5" fill="#22d3ee" class="sparkle sparkle--2"/>
                                <circle cx="80" cy="60" r="1.5" fill="#f472b6" class="sparkle sparkle--3"/>
                                <circle cx="20" cy="65" r="2" fill="#a78bfa" class="sparkle sparkle--4"/>
                            </svg>
                        </div>
                        
                        <!-- Magical Rings -->
                        <div class="mystery-placeholder__rings">
                            <div class="mystery-ring mystery-ring--outer"></div>
                            <div class="mystery-ring mystery-ring--inner"></div>
                        </div>
                    </div>
                    
                    <div class="upcoming-card__badge upcoming-card__badge--mystery">
                        <span>March 2026</span>
                    </div>
                </div>
                <div class="upcoming-card__content">
                    <span class="upcoming-card__series upcoming-card__series--mystery">Secret Project</span>
                    <h2 class="upcoming-card__title upcoming-card__title--mystery">Surprise Release</h2>
                    <p class="upcoming-card__description">
                        Something magical is brewing... A surprise upcoming release scheduled for March. 
                        Stay tuned for the big reveal!
                    </p>
                    <div class="upcoming-card__actions">
                        <a href="#" class="btn btn--secondary btn--mystery" data-modal="newsletter">Get Notified</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="section newsletter-section">
    <div class="container">
        <div class="section-header">
            <p class="section-header__eyebrow">Stay Updated</p>
            <h2 class="section-header__title">Join the Community</h2>
            <p class="section-header__description">Be the first to know when new books are released. Get exclusive updates, behind-the-scenes content, and special offers.</p>
        </div>
        <div class="series-cta">
            <a href="#" class="btn btn--primary btn--lg" data-modal="newsletter">Sign Up for Updates</a>
        </div>
    </div>
</section>

<?php
get_footer();
