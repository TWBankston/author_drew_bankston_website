<?php
/**
 * Blog Archive Template
 * 
 * Combined feed page showing blogs and vlogs at /blog/
 * Features:
 * - Featured post hero section
 * - Filter by type (All/Blog/Vlog) and category
 * - Search functionality
 * - Responsive card grid
 */

get_header();

// Get filter parameters
$type_filter = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : 'all';
$category_filter = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$search_query = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

// Get featured post (most recent featured blog)
$featured_query = DBC_CPT_Blog::get_featured_posts( 1 );
$has_featured = $featured_query->have_posts();

// Build query for main feed
$query_args = array(
    'posts_per_page' => 9,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

// Set post type based on filter
if ( $type_filter === 'blog' ) {
    $query_args['post_type'] = 'blog';
} elseif ( $type_filter === 'vlog' ) {
    $query_args['post_type'] = 'vlog';
} else {
    $query_args['post_type'] = array( 'blog', 'vlog' );
}

// Add category filter
if ( $category_filter ) {
    $query_args['tax_query'] = array(
        array(
            'taxonomy' => 'post_category',
            'field'    => 'slug',
            'terms'    => $category_filter,
        ),
    );
}

// Add search
if ( $search_query ) {
    $query_args['s'] = $search_query;
}

// Exclude featured post from main query
if ( $has_featured && $type_filter === 'all' && ! $search_query && ! $category_filter ) {
    $query_args['post__not_in'] = array( $featured_query->posts[0]->ID );
}

$posts_query = new WP_Query( $query_args );

// Get categories for filter
$categories = DBC_Taxonomy_Post_Category::get_all_categories( array( 'hide_empty' => true ) );
?>

<main class="relative pt-32 pb-24 lg:pt-40 min-h-screen antialiased" style="background-color: #050810; color: #e2e8f0;">
    
    <!-- Ambient Background Effects -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[20%] w-[60%] h-[60%] bg-violet-900/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[10%] right-[-5%] w-[40%] h-[40%] bg-indigo-900/5 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16 border-b border-slate-800/60 pb-8">
            <div class="space-y-3">
                <span class="inline-block text-xs font-medium tracking-[0.2em] uppercase text-violet-400">
                    Ship's Log
                </span>
                <h1 class="font-serif text-4xl md:text-5xl text-white tracking-tight leading-tight">
                    Transmissions from the Void
                </h1>
                <p class="text-slate-400 max-w-lg text-lg font-light leading-relaxed">
                    Updates on upcoming releases, deep dives into world-building, and thoughts on the craft of sci-fi writing.
                </p>
            </div>
            
            <!-- Search -->
            <div class="w-full md:w-auto flex flex-col gap-3">
                <form class="relative group" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'blog' ) ); ?>">
                    <?php if ( $type_filter && $type_filter !== 'all' ) : ?>
                        <input type="hidden" name="type" value="<?php echo esc_attr( $type_filter ); ?>">
                    <?php endif; ?>
                    <?php if ( $category_filter ) : ?>
                        <input type="hidden" name="category" value="<?php echo esc_attr( $category_filter ); ?>">
                    <?php endif; ?>
                    <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-violet-400 transition-colors"></iconify-icon>
                    <input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search transmissions..." class="w-full md:w-72 bg-slate-900/50 border border-slate-700 text-slate-200 text-sm rounded-lg py-2.5 pl-10 pr-4 placeholder:text-slate-600 focus:outline-none focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/30 transition-all">
                </form>
            </div>
        </div>

        <?php if ( $has_featured && $type_filter === 'all' && ! $search_query && ! $category_filter && $paged === 1 ) : ?>
        <!-- Featured Post (Hero) -->
        <section class="mb-20">
            <h2 class="text-sm font-medium text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                <iconify-icon icon="solar:star-linear" class="text-violet-400"></iconify-icon> Featured Entry
            </h2>
            <?php while ( $featured_query->have_posts() ) : $featured_query->the_post(); 
                $category = DBC_Taxonomy_Post_Category::get_post_category();
                $reading_time = DBC_CPT_Blog::get_reading_time();
            ?>
            <a href="<?php the_permalink(); ?>" class="group block relative rounded-2xl overflow-hidden p-1 transition-all duration-300 hover:-translate-y-1" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.4) 100%); border: 1px solid rgba(148, 163, 184, 0.1);">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 lg:gap-8 h-full">
                    <!-- Image Area -->
                    <div class="relative h-64 lg:h-auto bg-slate-800 rounded-xl overflow-hidden">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'large', array( 'class' => 'absolute inset-0 w-full h-full object-cover' ) ); ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        <?php else : ?>
                            <!-- Abstract Graphic Placeholder -->
                            <div class="absolute inset-0 bg-gradient-to-br from-violet-900/40 via-slate-900 to-slate-900 z-0"></div>
                            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(#6366f1 1px, transparent 1px); background-size: 24px 24px;"></div>
                        <?php endif; ?>
                        <?php if ( $category ) : ?>
                        <div class="absolute bottom-6 left-6 z-10">
                            <span class="bg-violet-600/90 text-white text-xs font-semibold px-3 py-1 rounded-full backdrop-blur-sm border border-violet-500/20">
                                <?php echo esc_html( $category->name ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Content Area -->
                    <div class="flex flex-col justify-center p-6 lg:py-12 lg:pr-12 space-y-4">
                        <div class="flex items-center gap-4 text-xs text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <iconify-icon icon="solar:calendar-linear"></iconify-icon> <?php echo get_the_date( 'M j, Y' ); ?>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <iconify-icon icon="solar:clock-circle-linear"></iconify-icon> <?php echo esc_html( $reading_time ); ?> min read
                            </span>
                        </div>
                        <h3 class="font-serif text-3xl text-white tracking-tight group-hover:text-violet-300 transition-colors">
                            <?php the_title(); ?>
                        </h3>
                        <?php if ( has_excerpt() ) : ?>
                        <p class="text-slate-400 leading-relaxed line-clamp-3 font-light">
                            <?php echo get_the_excerpt(); ?>
                        </p>
                        <?php endif; ?>
                        <div class="pt-2 flex items-center gap-2 text-violet-400 text-sm font-medium group-hover:translate-x-1 transition-transform">
                            Read Article 
                            <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                        </div>
                    </div>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </section>
        <?php endif; ?>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-2 mb-10 overflow-x-auto pb-2">
            <!-- Type Filters -->
            <a href="<?php echo esc_url( add_query_arg( array( 'type' => 'all', 'category' => $category_filter ), get_post_type_archive_link( 'blog' ) ) ); ?>" 
               class="px-4 py-1.5 rounded-full text-xs font-medium border whitespace-nowrap transition-all <?php echo $type_filter === 'all' ? 'bg-slate-800/30 text-slate-300 border-slate-700 bg-violet-500/15 text-violet-200 border-violet-500/30' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-200'; ?>">
                All Posts
            </a>
            <a href="<?php echo esc_url( add_query_arg( array( 'type' => 'blog', 'category' => $category_filter ), get_post_type_archive_link( 'blog' ) ) ); ?>" 
               class="px-4 py-1.5 rounded-full text-xs font-medium border whitespace-nowrap transition-all flex items-center gap-1.5 <?php echo $type_filter === 'blog' ? 'bg-slate-800/30 text-slate-300 border-slate-700 bg-violet-500/15 text-violet-200 border-violet-500/30' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-200'; ?>">
                <iconify-icon icon="solar:document-text-linear"></iconify-icon> Blogs
            </a>
            <a href="<?php echo esc_url( add_query_arg( array( 'type' => 'vlog', 'category' => $category_filter ), get_post_type_archive_link( 'blog' ) ) ); ?>" 
               class="px-4 py-1.5 rounded-full text-xs font-medium border whitespace-nowrap transition-all flex items-center gap-1.5 <?php echo $type_filter === 'vlog' ? 'bg-slate-800/30 text-slate-300 border-slate-700 bg-violet-500/15 text-violet-200 border-violet-500/30' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-200'; ?>">
                <iconify-icon icon="solar:videocamera-linear"></iconify-icon> Vlogs
            </a>
            
            <?php if ( ! empty( $categories ) ) : ?>
            <span class="w-px h-4 bg-slate-700 mx-2"></span>
            
            <!-- Category Filters -->
            <?php foreach ( $categories as $cat ) : ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'type' => $type_filter, 'category' => $cat->slug ), get_post_type_archive_link( 'blog' ) ) ); ?>" 
               class="px-4 py-1.5 rounded-full text-xs font-medium border whitespace-nowrap transition-all <?php echo $category_filter === $cat->slug ? 'bg-slate-800/30 text-slate-300 border-slate-700 bg-violet-500/15 text-violet-200 border-violet-500/30' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-200'; ?>">
                <?php echo esc_html( $cat->name ); ?>
            </a>
            <?php endforeach; ?>
            
            <?php if ( $category_filter ) : ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'type' => $type_filter ), get_post_type_archive_link( 'blog' ) ) ); ?>" 
               class="px-3 py-1.5 rounded-full text-xs font-medium text-slate-500 hover:text-slate-300 transition-all flex items-center gap-1">
                <iconify-icon icon="solar:close-circle-linear"></iconify-icon> Clear
            </a>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ( $posts_query->have_posts() ) : ?>
        <!-- Grid Feed -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            
            <?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); 
                $post_type = get_post_type();
                $category = DBC_Taxonomy_Post_Category::get_post_category();
                $is_vlog = $post_type === 'vlog';
                
                if ( $is_vlog ) {
                    $duration = DBC_CPT_Vlog::get_duration();
                } else {
                    $reading_time = DBC_CPT_Blog::get_reading_time();
                }
            ?>
            <!-- Card -->
            <article class="group rounded-xl p-5 flex flex-col h-full relative overflow-hidden transition-all duration-300 hover:-translate-y-1" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.4) 100%); border: 1px solid rgba(148, 163, 184, 0.1);">
                <div class="h-48 mb-5 rounded-lg bg-slate-800 relative overflow-hidden">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'medium_large', array( 'class' => 'absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500' ) ); ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                    <?php else : ?>
                        <!-- Placeholder -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-<?php echo $is_vlog ? 'cyan' : 'violet'; ?>-900/20"></div>
                        <div class="w-full h-full flex items-center justify-center text-slate-600 group-hover:scale-105 transition-transform duration-500">
                            <iconify-icon icon="<?php echo $is_vlog ? 'solar:videocamera-linear' : 'solar:document-text-linear'; ?>" class="text-4xl opacity-50"></iconify-icon>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Type Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="text-[10px] font-semibold tracking-wider uppercase text-slate-300 bg-slate-900/80 px-2 py-1 rounded backdrop-blur-md border border-slate-700 flex items-center gap-1">
                            <iconify-icon icon="<?php echo $is_vlog ? 'solar:videocamera-linear' : 'solar:document-text-linear'; ?>"></iconify-icon>
                            <?php echo $is_vlog ? 'Vlog' : 'Blog'; ?>
                        </span>
                    </div>
                    
                    <?php if ( $is_vlog && $duration ) : ?>
                    <!-- Duration Badge -->
                    <div class="absolute bottom-3 right-3">
                        <span class="text-[10px] font-mono text-white bg-black/70 px-2 py-1 rounded backdrop-blur-md">
                            <?php echo esc_html( $duration ); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1 flex flex-col space-y-3">
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span><?php echo get_the_date( 'M j, Y' ); ?></span>
                        <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                        <?php if ( $is_vlog && $duration ) : ?>
                            <span><?php echo esc_html( $duration ); ?></span>
                        <?php else : ?>
                            <span><?php echo esc_html( $reading_time ?? 3 ); ?> min read</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-serif text-xl text-slate-100 tracking-tight leading-snug group-hover:text-violet-300 transition-colors">
                        <?php the_title(); ?>
                    </h3>
                    <?php if ( has_excerpt() ) : ?>
                    <p class="text-sm text-slate-400 leading-relaxed line-clamp-3">
                        <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <div class="mt-5 pt-5 border-t border-slate-800/50 flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 group-hover:text-white transition-colors">
                        <?php echo $is_vlog ? 'Watch Now' : 'Read Post'; ?>
                    </span>
                    <iconify-icon icon="solar:arrow-right-up-linear" class="text-slate-500 group-hover:text-violet-400 transition-colors"></iconify-icon>
                </div>
                
                <a href="<?php the_permalink(); ?>" class="absolute inset-0 z-10"></a>
            </article>
            <?php endwhile; ?>

        </div>

        <!-- Pagination -->
        <?php if ( $posts_query->max_num_pages > 1 ) : ?>
        <div class="mt-20 flex justify-center pb-8">
            <?php
            $big = 999999999;
            echo paginate_links( array(
                'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'    => '?paged=%#%',
                'current'   => max( 1, $paged ),
                'total'     => $posts_query->max_num_pages,
                'prev_text' => '<iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Previous',
                'next_text' => 'Next <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>',
                'type'      => 'list',
                'before_page_number' => '',
                'after_page_number'  => '',
            ) );
            ?>
        </div>
        <?php endif; ?>

        <?php else : ?>
        <!-- No Posts Found -->
        <div class="text-center py-20">
            <iconify-icon icon="solar:ghost-linear" class="text-6xl text-slate-600 mb-4"></iconify-icon>
            <h2 class="font-serif text-2xl text-white mb-2">No Transmissions Found</h2>
            <p class="text-slate-400 mb-6">
                <?php if ( $search_query ) : ?>
                    No posts matching "<?php echo esc_html( $search_query ); ?>" were found.
                <?php else : ?>
                    Check back soon for new content!
                <?php endif; ?>
            </p>
            <?php if ( $search_query || $category_filter ) : ?>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'blog' ) ); ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-slate-700 hover:border-slate-500 text-slate-300 hover:text-white transition-all text-sm font-medium">
                <iconify-icon icon="solar:arrow-left-linear"></iconify-icon>
                View All Posts
            </a>
            <?php endif; ?>
        </div>
        <?php endif; wp_reset_postdata(); ?>

    </div>

</main>

<!-- Subscribe Section -->
<?php get_template_part( 'template-parts/subscribe-box' ); ?>

<?php
get_footer();
