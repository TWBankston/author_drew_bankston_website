<?php
/**
 * Template Name: Contact Page
 */

get_header();
?>

<!-- Contact Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container">
        <div class="hero__content gsap-reveal" style="text-align: center; max-width: 100%;">
            <h1 class="hero__title">Contact</h1>
            <p class="hero__subtitle">Have a question, want to book an event, or just want to say hello? Get in touch!</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Contact Form -->
        <div class="contact-form-section gsap-reveal">
            <div class="section-header section-header--left">
                <h2 class="section-header__title">Send a Message</h2>
                <p class="section-header__description" style="margin-left: 0;">Have questions, feedback, or just want to say hello? Fill out the form below and Drew will get back to you as soon as possible.</p>
            </div>
            
            <form class="contact-form" action="#" method="post">
                <div class="contact-form__row">
                    <div class="contact-form__group">
                        <label class="contact-form__label" for="contact-name">Your Name *</label>
                        <input type="text" id="contact-name" name="name" class="contact-form__input" placeholder="Enter your full name" required>
                    </div>
                    <div class="contact-form__group">
                        <label class="contact-form__label" for="contact-email">Email Address *</label>
                        <input type="email" id="contact-email" name="email" class="contact-form__input" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="contact-form__group">
                    <label class="contact-form__label" for="contact-subject">Subject</label>
                    <select id="contact-subject" name="subject" class="contact-form__select">
                        <option value="">Select a topic...</option>
                        <option value="general">General Inquiry</option>
                        <option value="books">Question About Books</option>
                        <option value="events">Event Booking</option>
                        <option value="media">Media / Press</option>
                        <option value="screenplay">Screenplay Inquiry</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="contact-form__group">
                    <label class="contact-form__label" for="contact-message">Message *</label>
                    <textarea id="contact-message" name="message" class="contact-form__textarea" placeholder="Write your message here..." required></textarea>
                </div>
                
                <div class="contact-form__footer">
                    <button type="submit" class="btn btn--primary btn--lg">Send Message</button>
                    <p class="contact-form__required">* Required fields</p>
                </div>
            </form>
        </div>
        
        <!-- Callout Cards -->
        <div class="contact-grid gsap-reveal">
            <div class="callout-card">
                <div class="callout-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <h3 class="callout-card__title">Media & Press</h3>
                <p class="callout-card__description">For media inquiries, interviews, or review copies, please reach out with details about your publication or platform:</p>
                <a href="mailto:christi@drewbankston.com" class="callout-card__email">christi@drewbankston.com</a>
            </div>
            
            <div class="callout-card">
                <div class="callout-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <h3 class="callout-card__title">Event Bookings</h3>
                <p class="callout-card__description">Interested in having Drew speak at your event, school, or bookstore? Please contact with event details and proposed dates:</p>
                <a href="mailto:christi@drewbankston.com" class="callout-card__email">christi@drewbankston.com</a>
            </div>
        </div>
        
        <!-- Social Links -->
        <div class="section-header gsap-reveal" style="margin-top: var(--space-16);">
            <h2 class="section-header__title">Connect Online</h2>
        </div>
        
        <div class="social-links gsap-reveal">
            <?php 
            $socials = dbt_get_social_links();
            foreach ( $socials as $key => $social ) :
            ?>
            <a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="social-link">
                <span class="social-link__icon"><?php echo dbt_get_social_icon( $key ); ?></span>
                <span class="social-link__label"><?php echo esc_html( $social['label'] ); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
get_footer();

