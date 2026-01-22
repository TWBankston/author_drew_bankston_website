<?php
/**
 * Single Blog Post Template
 * 
 * Individual blog post view with:
 * - Breadcrumb navigation
 * - Centered post header
 * - Featured image
 * - Share buttons sidebar
 * - Rich content typography
 * - Tags
 * - Subscribe box
 * - Related posts
 */

get_header();

// Get post data
$category = DBC_Taxonomy_Post_Category::get_post_category();
$reading_time = DBC_CPT_Blog::get_reading_time();
$tags = get_the_terms( get_the_ID(), 'post_category' );
?>

<main class="relative pt-32 pb-24 lg:pt-40 min-h-screen antialiased" style="background-color: #050810; color: #e2e8f0;">
    
    <!-- Ambient Background Effects -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[50%] -translate-x-1/2 w-[80%] h-[50%] bg-violet-900/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">

        <!-- Breadcrumb / Back -->
        <div class="max-w-3xl mx-auto mb-12">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'blog' ) ); ?>" class="inline-flex items-center gap-2 text-xs font-medium text-slate-500 hover:text-violet-400 transition-colors uppercase tracking-wider group">
                <iconify-icon icon="solar:arrow-left-linear" class="text-base group-hover:-translate-x-1 transition-transform"></iconify-icon>
                Back to Transmissions
            </a>
        </div>

        <!-- Post Header -->
        <header class="max-w-4xl mx-auto text-center mb-16 space-y-6">
            <div class="flex items-center justify-center gap-3">
                <?php if ( $category ) : ?>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-violet-500/10 text-violet-300 border border-violet-500/20">
                    <?php echo esc_html( $category->name ); ?>
                </span>
                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                <?php endif; ?>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-widest"><?php echo get_the_date( 'M j, Y' ); ?></span>
            </div>
            
            <h1 class="font-serif text-4xl md:text-6xl text-white tracking-tight leading-[1.1]">
                <?php the_title(); ?>
            </h1>
            
            <?php if ( has_excerpt() ) : ?>
            <p class="text-lg md:text-xl text-slate-400 font-light max-w-2xl mx-auto leading-relaxed">
                <?php echo get_the_excerpt(); ?>
            </p>
            <?php endif; ?>

            <!-- Author -->
            <div class="flex items-center justify-center gap-3 pt-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 overflow-hidden">
                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 40, '', '', array( 'class' => 'w-full h-full object-cover' ) ); ?>
                </div>
                <div class="text-left">
                    <div class="text-sm text-white font-medium"><?php the_author(); ?></div>
                    <div class="text-xs text-slate-500"><?php echo esc_html( $reading_time ); ?> min read</div>
                </div>
            </div>
        </header>

        <!-- Featured Image -->
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="max-w-5xl mx-auto mb-20 rounded-2xl overflow-hidden border border-slate-800 bg-slate-900 shadow-2xl shadow-violet-900/10 relative group">
            <div class="aspect-video relative overflow-hidden">
                <?php the_post_thumbnail( 'large', array( 'class' => 'w-full h-full object-cover' ) ); ?>
            </div>
            <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-2xl pointer-events-none"></div>
        </div>
        <?php else : ?>
        <!-- Abstract Placeholder -->
        <div class="max-w-5xl mx-auto mb-20 rounded-2xl overflow-hidden border border-slate-800 bg-slate-900 shadow-2xl shadow-violet-900/10 relative">
            <div class="aspect-video relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-violet-900/40 via-slate-900 to-slate-900 z-0"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] opacity-40" style="background-image: radial-gradient(#6366f1 1px, transparent 1px); background-size: 32px 32px;"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 border border-violet-500/30 rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 border border-slate-500/20 rounded-full"></div>
            </div>
            <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-2xl pointer-events-none"></div>
        </div>
        <?php endif; ?>

        <!-- Content Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <!-- Sidebar Left (Share) -->
            <div class="hidden lg:block lg:col-span-2">
                <div class="sticky top-32 flex flex-col gap-4 items-end">
                    <span class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-2">Share</span>
                    
                    <!-- Copy Link -->
                    <button onclick="navigator.clipboard.writeText(window.location.href); this.querySelector('iconify-icon').setAttribute('icon', 'solar:check-circle-linear'); setTimeout(() => this.querySelector('iconify-icon').setAttribute('icon', 'solar:copy-linear'), 2000);" class="w-10 h-10 rounded-full border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 hover:border-slate-600 flex items-center justify-center transition-all" title="Copy link">
                        <iconify-icon icon="solar:copy-linear" class="text-lg"></iconify-icon>
                    </button>
                    
                    <!-- Twitter/X -->
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" rel="noopener" class="w-10 h-10 rounded-full border border-slate-800 text-slate-400 hover:text-[#1da1f2] hover:bg-slate-800 hover:border-slate-600 flex items-center justify-center transition-all" title="Share on X">
                        <iconify-icon icon="ri:twitter-x-fill" class="text-lg"></iconify-icon>
                    </a>
                    
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" class="w-10 h-10 rounded-full border border-slate-800 text-slate-400 hover:text-[#1877f2] hover:bg-slate-800 hover:border-slate-600 flex items-center justify-center transition-all" title="Share on Facebook">
                        <iconify-icon icon="ri:facebook-fill" class="text-lg"></iconify-icon>
                    </a>
                </div>
            </div>

            <!-- Main Text -->
            <article class="col-span-1 lg:col-span-8">
                <style>
                    .blog-content { max-width: none; color: #cbd5e1; line-height: 2; font-weight: 300; font-size: 1.125rem; }
                    .blog-content > *:first-child { margin-top: 0; }
                    .blog-content h2 { font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif; color: #fff; font-size: 1.875rem; font-weight: 600; letter-spacing: -0.025em; margin-top: 3rem; margin-bottom: 1.5rem; line-height: 1.3; }
                    .blog-content h3 { font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif; color: #fff; font-size: 1.5rem; font-weight: 600; letter-spacing: -0.025em; margin-top: 2.5rem; margin-bottom: 1rem; line-height: 1.3; }
                    .blog-content h4 { font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif; color: #fff; font-size: 1.25rem; font-weight: 600; margin-top: 2rem; margin-bottom: 0.75rem; }
                    .blog-content p { margin-bottom: 1.5rem; }
                    .blog-content a { color: #a78bfa; text-decoration: none; }
                    .blog-content a:hover { text-decoration: underline; }
                    .blog-content strong { color: #fff; font-weight: 500; }
                    .blog-content em { font-style: italic; }
                    .blog-content blockquote { border-left: 2px solid rgba(139, 92, 246, 0.5); padding-left: 1.5rem; font-style: italic; color: #e2e8f0; margin: 2rem 0; }
                    .blog-content ul, .blog-content ol { margin: 1.5rem 0; padding-left: 1.5rem; }
                    .blog-content ul { list-style-type: disc; }
                    .blog-content ol { list-style-type: decimal; }
                    .blog-content li { margin-bottom: 0.5rem; color: #cbd5e1; }
                    .blog-content code { background: #1e293b; color: #c4b5fd; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.875em; }
                    .blog-content pre { background: #0f172a; border: 1px solid #1e293b; border-radius: 0.5rem; padding: 1rem; overflow-x: auto; margin: 1.5rem 0; }
                    .blog-content img { border-radius: 0.5rem; margin: 2rem 0; }
                    .blog-content hr { border: none; border-top: 1px solid #334155; margin: 3rem 0; }
                </style>
                <div class="blog-content">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
                <div class="mt-12 pt-8 border-t border-slate-800/50 flex flex-wrap gap-2">
                    <?php foreach ( $tags as $tag ) : ?>
                    <a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-medium transition-colors">
                        #<?php echo esc_html( $tag->name ); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Mobile Share Buttons -->
                <div class="lg:hidden mt-12 pt-8 border-t border-slate-800/50">
                    <div class="flex items-center gap-4">
                        <span class="text-xs uppercase tracking-widest text-slate-600 font-semibold">Share</span>
                        <button onclick="navigator.clipboard.writeText(window.location.href)" class="w-10 h-10 rounded-full border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 flex items-center justify-center transition-all">
                            <iconify-icon icon="solar:copy-linear" class="text-lg"></iconify-icon>
                        </button>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" class="w-10 h-10 rounded-full border border-slate-800 text-slate-400 hover:text-[#1da1f2] hover:bg-slate-800 flex items-center justify-center transition-all">
                            <iconify-icon icon="ri:twitter-x-fill" class="text-lg"></iconify-icon>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" class="w-10 h-10 rounded-full border border-slate-800 text-slate-400 hover:text-[#1877f2] hover:bg-slate-800 flex items-center justify-center transition-all">
                            <iconify-icon icon="ri:facebook-fill" class="text-lg"></iconify-icon>
                        </a>
                    </div>
                </div>
            </article>

            <!-- Sidebar Right (Empty for balance) -->
            <div class="hidden lg:block lg:col-span-2"></div>
        </div>

        <!-- Subscribe Box -->
        <div class="max-w-4xl mx-auto mt-24 mb-24">
            <?php get_template_part( 'template-parts/subscribe-box' ); ?>
        </div>

        <!-- Related Posts -->
        <?php
        $related_posts = DBC_CPT_Blog::get_related_posts( get_the_ID(), 3 );
        if ( $related_posts->have_posts() ) :
        ?>
        <div class="max-w-7xl mx-auto border-t border-slate-800/50 pt-16">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-serif text-2xl text-white">Related Transmissions</h3>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'blog' ) ); ?>" class="text-sm text-violet-400 hover:text-violet-300 flex items-center gap-1">
                    View all <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="group rounded-xl p-5 flex flex-col h-full bg-slate-900/20 transition-all duration-300 hover:-translate-y-1" style="border: 1px solid rgba(148, 163, 184, 0.1);">
                    <div class="text-xs text-slate-500 mb-3"><?php echo get_the_date( 'M j, Y' ); ?></div>
                    <h4 class="font-serif text-lg text-slate-200 group-hover:text-violet-300 transition-colors mb-2"><?php the_title(); ?></h4>
                    <?php if ( has_excerpt() ) : ?>
                    <p class="text-sm text-slate-500 line-clamp-2"><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                    <?php endif; ?>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

</main>

<?php
get_footer();
