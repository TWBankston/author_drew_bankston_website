<?php
/**
 * Template Part: Subscribe Box
 * 
 * "Join the Interstellar Log" newsletter subscription component
 * 
 * Usage:
 * get_template_part( 'template-parts/subscribe-box' );
 * 
 * Or with minimal style:
 * get_template_part( 'template-parts/subscribe-box', null, array( 'style' => 'minimal' ) );
 */

$style = isset( $args['style'] ) ? $args['style'] : 'full';
$is_minimal = $style === 'minimal';
?>

<?php if ( $is_minimal ) : ?>
<!-- Minimal Style Subscribe Box -->
<div class="text-center">
    <iconify-icon icon="solar:bell-bing-linear" class="text-3xl text-violet-400 mb-4"></iconify-icon>
    <h3 class="font-serif text-2xl text-white mb-2">Never Miss a Transmission</h3>
    <p class="text-slate-500 text-sm mb-6">Get notified when new posts drop.</p>
    <form class="subscribe-form flex justify-center gap-2 max-w-sm mx-auto" data-action="footer">
        <input type="email" name="email" placeholder="Email address" required class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-lg px-4 py-2 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/30 transition-all">
        <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
            <span class="subscribe-text">Notify</span>
            <span class="subscribe-loading hidden">
                <iconify-icon icon="solar:loading-bold-duotone" class="animate-spin"></iconify-icon>
            </span>
        </button>
    </form>
    <p class="subscribe-message mt-4 text-sm hidden"></p>
</div>

<?php else : ?>
<!-- Full Style Subscribe Box -->
<div class="rounded-2xl bg-gradient-to-r from-violet-900/20 to-slate-900/50 border border-slate-800 p-8 md:p-12 text-center relative overflow-hidden">
    <!-- Background Glow -->
    <div class="absolute top-0 right-0 p-32 bg-violet-500/10 blur-[80px] rounded-full pointer-events-none"></div>
    
    <iconify-icon icon="solar:mailbox-linear" class="text-4xl text-violet-300 mb-4 relative z-10"></iconify-icon>
    <h3 class="font-serif text-2xl text-white mb-2 relative z-10">Join the Interstellar Log</h3>
    <p class="text-slate-400 text-sm mb-6 max-w-md mx-auto relative z-10">Get notified when new lore drops and book releases are announced. No spam, just signals.</p>
    
    <form class="subscribe-form flex flex-col sm:flex-row gap-3 max-w-sm mx-auto relative z-10" data-action="footer">
        <input type="email" name="email" placeholder="explorer@nebula.com" required class="flex-1 bg-slate-950/50 border border-slate-700 text-slate-200 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/30 transition-all placeholder:text-slate-600">
        <button type="submit" class="bg-slate-100 hover:bg-white text-slate-900 px-6 py-3 rounded-lg text-sm font-semibold transition-colors whitespace-nowrap">
            <span class="subscribe-text">Subscribe</span>
            <span class="subscribe-loading hidden">
                <iconify-icon icon="solar:loading-bold-duotone" class="animate-spin"></iconify-icon>
            </span>
        </button>
    </form>
    
    <p class="subscribe-message mt-4 text-sm hidden relative z-10"></p>
</div>
<?php endif; ?>

<script>
(function() {
    // Initialize all subscribe forms on the page
    document.querySelectorAll('.subscribe-form').forEach(function(form) {
        if (form.dataset.initialized) return;
        form.dataset.initialized = 'true';
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = form.querySelector('input[type="email"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const textSpan = form.querySelector('.subscribe-text');
            const loadingSpan = form.querySelector('.subscribe-loading');
            const messageEl = form.closest('div').querySelector('.subscribe-message') || 
                              form.parentElement.querySelector('.subscribe-message');
            
            // Show loading state
            if (textSpan) textSpan.classList.add('hidden');
            if (loadingSpan) loadingSpan.classList.remove('hidden');
            submitBtn.disabled = true;
            
            // Submit via AJAX
            const formData = new FormData();
            formData.append('action', 'dbc_footer_subscribe');
            formData.append('email', emailInput.value);
            formData.append('nonce', typeof dbtData !== 'undefined' ? dbtData.newsletterNonce : '');
            
            fetch(typeof dbtData !== 'undefined' ? dbtData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                if (textSpan) textSpan.classList.remove('hidden');
                if (loadingSpan) loadingSpan.classList.add('hidden');
                submitBtn.disabled = false;
                
                // Show message
                if (messageEl) {
                    messageEl.classList.remove('hidden');
                    if (data.success) {
                        messageEl.textContent = data.data.message || 'Thank you for subscribing!';
                        messageEl.classList.remove('text-red-400');
                        messageEl.classList.add('text-green-400');
                        emailInput.value = '';
                    } else {
                        messageEl.textContent = data.data.message || 'Something went wrong. Please try again.';
                        messageEl.classList.remove('text-green-400');
                        messageEl.classList.add('text-red-400');
                    }
                    
                    // Hide message after 5 seconds
                    setTimeout(function() {
                        messageEl.classList.add('hidden');
                    }, 5000);
                }
            })
            .catch(error => {
                // Reset button state
                if (textSpan) textSpan.classList.remove('hidden');
                if (loadingSpan) loadingSpan.classList.add('hidden');
                submitBtn.disabled = false;
                
                if (messageEl) {
                    messageEl.classList.remove('hidden');
                    messageEl.textContent = 'Something went wrong. Please try again.';
                    messageEl.classList.remove('text-green-400');
                    messageEl.classList.add('text-red-400');
                }
            });
        });
    });
})();
</script>
