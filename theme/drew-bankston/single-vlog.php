<?php
/**
 * Single Vlog Template
 * 
 * Individual vlog post view with:
 * - Video player (YouTube embed or local)
 * - Chapter navigation
 * - Description/resources
 * - Related vlogs sidebar
 * - Subscribe section
 */

get_header();

// Get vlog data
$video_source = DBC_CPT_Vlog::get_video_source();
$youtube_id = DBC_CPT_Vlog::get_youtube_id();
$youtube_embed = DBC_CPT_Vlog::get_youtube_embed_url();
$local_video = DBC_CPT_Vlog::get_local_video_url();
$duration = DBC_CPT_Vlog::get_duration();
$vlog_number = DBC_CPT_Vlog::get_vlog_number();
$chapters = DBC_CPT_Vlog::get_chapters();
$category = DBC_Taxonomy_Post_Category::get_post_category();

// Get related vlogs
$related_vlogs = DBC_CPT_Vlog::get_related_vlogs( get_the_ID(), 3 );
?>

<main class="relative pt-24 pb-24 lg:pt-32 min-h-screen antialiased" style="background-color: #050810; color: #e2e8f0;">
    
    <!-- Ambient Background -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[20%] w-[60%] h-[60%] bg-violet-900/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto mb-8">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'blog' ) ); ?>?type=vlog" class="inline-flex items-center gap-2 text-xs font-medium text-slate-500 hover:text-violet-400 transition-colors uppercase tracking-wider group">
                <iconify-icon icon="solar:arrow-left-linear" class="text-base group-hover:-translate-x-1 transition-transform"></iconify-icon>
                Back to Studio Logs
            </a>
        </div>

        <!-- Video Player Section -->
        <div class="max-w-7xl mx-auto mb-10">
            <div class="relative w-full aspect-video bg-black rounded-xl overflow-hidden shadow-2xl shadow-black/50 border border-slate-800 group">
                
                <?php if ( $video_source === 'youtube' && $youtube_embed ) : ?>
                <!-- YouTube Embed -->
                <iframe 
                    class="absolute inset-0 w-full h-full"
                    src="<?php echo esc_url( $youtube_embed ); ?>" 
                    title="<?php echo esc_attr( get_the_title() ); ?>"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen>
                </iframe>
                
                <?php elseif ( $video_source === 'local' && $local_video ) : ?>
                <!-- Local Video Player -->
                <video 
                    class="absolute inset-0 w-full h-full"
                    controls
                    poster="<?php echo has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : ''; ?>"
                    preload="metadata">
                    <source src="<?php echo esc_url( $local_video ); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <?php else : ?>
                <!-- Video Placeholder -->
                <div class="absolute inset-0 bg-slate-900 flex items-center justify-center overflow-hidden">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'large', array( 'class' => 'absolute inset-0 w-full h-full object-cover opacity-60' ) ); ?>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/40"></div>
                    
                    <!-- Center Play Button Placeholder -->
                    <div class="absolute z-20 w-20 h-20 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center border border-white/20">
                        <iconify-icon icon="solar:play-bold" class="text-4xl text-white ml-1"></iconify-icon>
                    </div>
                    
                    <p class="absolute bottom-6 left-1/2 -translate-x-1/2 text-slate-400 text-sm">Video coming soon</p>
                </div>
                <?php endif; ?>
                
                <!-- Vlog Number Badge - positioned below YouTube's top UI -->
                <?php if ( $vlog_number ) : ?>
                <div class="absolute bottom-6 left-6 px-3 py-1 bg-black/60 backdrop-blur border border-white/10 rounded-md text-xs font-mono text-white z-10">
                    VLOG #<?php echo esc_html( $vlog_number ); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <!-- Main Info & Description -->
            <div class="col-span-1 lg:col-span-8 space-y-8">
                
                <!-- Title Header -->
                <div class="border-b border-slate-800/50 pb-8">
                    <h1 class="font-serif text-3xl md:text-4xl text-white tracking-tight mb-4">
                        <?php the_title(); ?>
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-400">
                        <span><?php echo get_the_date( 'M j, Y' ); ?></span>
                        <?php if ( $duration ) : ?>
                        <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                        <span><?php echo esc_html( $duration ); ?></span>
                        <?php endif; ?>
                        <?php if ( $category ) : ?>
                        <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                        <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="text-violet-400 hover:text-violet-300">
                            #<?php echo esc_html( $category->name ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions Bar -->
                <div class="flex items-center gap-4 flex-wrap">
                    <!-- Author -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold border border-white/10">
                            DB
                        </div>
                        <div class="leading-tight">
                            <div class="text-white font-medium text-sm"><?php the_author(); ?></div>
                            <div class="text-xs text-slate-500">Author</div>
                        </div>
                    </div>

                    <div class="flex-1"></div>

                    <!-- Share Buttons -->
                    <div class="flex items-center gap-2">
                        <button onclick="navigator.clipboard.writeText(window.location.href); this.querySelector('iconify-icon').setAttribute('icon', 'solar:check-circle-linear'); setTimeout(() => this.querySelector('iconify-icon').setAttribute('icon', 'solar:share-linear'), 2000);" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition-all" title="Copy link">
                            <iconify-icon icon="solar:share-linear" class="text-lg"></iconify-icon>
                        </button>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" rel="noopener" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-800 text-slate-400 hover:text-[#1da1f2] hover:bg-slate-800 transition-all" title="Share on X">
                            <iconify-icon icon="ri:twitter-x-fill" class="text-lg"></iconify-icon>
                        </a>
                    </div>
                </div>

                <!-- Description Box -->
                <div class="bg-slate-900/30 rounded-xl p-6 border border-slate-800/50">
                    <div class="prose prose-invert prose-sm max-w-none text-slate-400
                                prose-headings:text-white prose-headings:font-serif
                                prose-a:text-violet-400 hover:prose-a:text-violet-300
                                prose-strong:text-white
                                prose-ul:list-none prose-ul:pl-0 prose-li:flex prose-li:items-center prose-li:gap-2">
                        <?php the_content(); ?>
                    </div>
                </div>

                <?php if ( $youtube_id ) : ?>
                <!-- Watch on YouTube Link -->
                <div class="flex items-center gap-4 text-sm">
                    <a href="https://www.youtube.com/watch?v=<?php echo esc_attr( $youtube_id ); ?>" target="_blank" rel="noopener" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                        <iconify-icon icon="ri:youtube-fill" class="text-lg text-red-500"></iconify-icon>
                        Watch on YouTube
                    </a>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Sidebar -->
            <div class="col-span-1 lg:col-span-4 space-y-8">
                
                <?php if ( ! empty( $chapters ) ) : ?>
                <!-- Chapters Widget -->
                <div class="bg-[#050810] border border-slate-800 rounded-xl overflow-hidden sticky top-24">
                    <div class="p-4 border-b border-slate-800 bg-slate-900/50 flex justify-between items-center">
                        <h3 class="font-serif text-white">Video Chapters</h3>
                        <span class="text-xs text-slate-500 uppercase tracking-wider">Index</span>
                    </div>
                    <div class="max-h-[300px] overflow-y-auto p-2 space-y-1">
                        <?php foreach ( $chapters as $index => $chapter ) : ?>
                        <button class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-slate-900 group transition-all text-left">
                            <div class="text-slate-500 group-hover:text-violet-400 text-xs font-mono w-10 shrink-0"><?php echo esc_html( $chapter['timestamp'] ); ?></div>
                            <div class="text-sm text-slate-400 group-hover:text-white truncate flex-1"><?php echo esc_html( $chapter['title'] ); ?></div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( $related_vlogs->have_posts() ) : ?>
                <!-- Up Next -->
                <div>
                    <h3 class="font-serif text-lg text-white mb-4">Up Next</h3>
                    <div class="space-y-4">
                        
                        <?php while ( $related_vlogs->have_posts() ) : $related_vlogs->the_post(); 
                            $related_duration = DBC_CPT_Vlog::get_duration();
                            $related_youtube = DBC_CPT_Vlog::get_youtube_id();
                        ?>
                        <a href="<?php the_permalink(); ?>" class="flex gap-3 group">
                            <div class="relative w-32 aspect-video bg-slate-800 rounded-lg overflow-hidden shrink-0 border border-slate-700/50">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'thumbnail', array( 'class' => 'absolute inset-0 w-full h-full object-cover' ) ); ?>
                                <?php elseif ( $related_youtube ) : ?>
                                    <img src="<?php echo esc_url( DBC_CPT_Vlog::get_youtube_thumbnail( get_the_ID(), 'mqdefault' ) ); ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-slate-900/40 group-hover:bg-transparent transition-colors"></div>
                                <?php if ( $related_duration ) : ?>
                                <div class="absolute bottom-1 right-1 px-1 py-0.5 bg-black/80 rounded text-[10px] text-white font-mono"><?php echo esc_html( $related_duration ); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm text-white font-medium group-hover:text-violet-400 transition-colors line-clamp-2"><?php the_title(); ?></h4>
                                <div class="text-xs text-slate-500 mt-1"><?php the_author(); ?></div>
                                <div class="text-xs text-slate-600"><?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?> ago</div>
                            </div>
                        </a>
                        <?php endwhile; wp_reset_postdata(); ?>

                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Newsletter Subscribe -->
        <div class="max-w-4xl mx-auto mt-24 mb-16 border-t border-slate-800 pt-16">
            <?php get_template_part( 'template-parts/subscribe-box', null, array( 'style' => 'minimal' ) ); ?>
        </div>

    </div>

</main>

<?php
get_footer();
