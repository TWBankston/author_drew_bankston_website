<?php
/**
 * Blog Custom Post Type
 * 
 * Registers the 'blog' post type for written articles/transmissions
 */

defined( 'ABSPATH' ) || exit;

class DBC_CPT_Blog {
    
    /**
     * Initialize the Blog CPT
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
        add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );
    }
    
    /**
     * Register the Blog post type
     */
    public static function register_post_type() {
        $labels = array(
            'name'                  => 'Blog Posts',
            'singular_name'         => 'Blog Post',
            'menu_name'             => 'Blog',
            'name_admin_bar'        => 'Blog Post',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Blog Post',
            'new_item'              => 'New Blog Post',
            'edit_item'             => 'Edit Blog Post',
            'view_item'             => 'View Blog Post',
            'all_items'             => 'All Blog Posts',
            'search_items'          => 'Search Blog Posts',
            'parent_item_colon'     => 'Parent Blog Posts:',
            'not_found'             => 'No blog posts found.',
            'not_found_in_trash'    => 'No blog posts found in Trash.',
            'featured_image'        => 'Featured Image',
            'set_featured_image'    => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image'    => 'Use as featured image',
            'archives'              => 'Blog Archives',
            'insert_into_item'      => 'Insert into blog post',
            'uploaded_to_this_item' => 'Uploaded to this blog post',
            'filter_items_list'     => 'Filter blog posts list',
            'items_list_navigation' => 'Blog posts list navigation',
            'items_list'            => 'Blog posts list',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 
                'slug'       => 'blog',
                'with_front' => false,
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-edit-page',
            'supports'           => array( 
                'title', 
                'editor', 
                'thumbnail', 
                'excerpt', 
                'author',
                'comments',
                'revisions',
            ),
            'show_in_rest'       => true, // Enable Gutenberg editor
        );
        
        register_post_type( 'blog', $args );
    }
    
    /**
     * Custom update messages for Blog post type
     */
    public static function updated_messages( $messages ) {
        global $post;
        
        $permalink = get_permalink( $post );
        
        $messages['blog'] = array(
            0  => '', // Unused
            1  => sprintf( 'Blog post updated. <a target="_blank" href="%s">View blog post</a>', esc_url( $permalink ) ),
            2  => 'Custom field updated.',
            3  => 'Custom field deleted.',
            4  => 'Blog post updated.',
            5  => isset( $_GET['revision'] ) ? sprintf( 'Blog post restored to revision from %s', wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( 'Blog post published. <a href="%s">View blog post</a>', esc_url( $permalink ) ),
            7  => 'Blog post saved.',
            8  => sprintf( 'Blog post submitted. <a target="_blank" href="%s">Preview blog post</a>', esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
            9  => sprintf( 'Blog post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview blog post</a>', date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ), esc_url( $permalink ) ),
            10 => sprintf( 'Blog post draft updated. <a target="_blank" href="%s">Preview blog post</a>', esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
        );
        
        return $messages;
    }
    
    /**
     * Get featured blog posts
     * 
     * @param int $count Number of posts to retrieve
     * @return WP_Query
     */
    public static function get_featured_posts( $count = 1 ) {
        return new WP_Query( array(
            'post_type'      => 'blog',
            'posts_per_page' => $count,
            'meta_query'     => array(
                array(
                    'key'     => '_dbc_blog_featured',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
    }
    
    /**
     * Get recent blog posts
     * 
     * @param int $count Number of posts to retrieve
     * @param int $exclude_id Post ID to exclude (optional)
     * @return WP_Query
     */
    public static function get_recent_posts( $count = 6, $exclude_id = 0 ) {
        $args = array(
            'post_type'      => 'blog',
            'posts_per_page' => $count,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        
        if ( $exclude_id ) {
            $args['post__not_in'] = array( $exclude_id );
        }
        
        return new WP_Query( $args );
    }
    
    /**
     * Get related blog posts by category
     * 
     * @param int $post_id Current post ID
     * @param int $count Number of posts to retrieve
     * @return WP_Query
     */
    public static function get_related_posts( $post_id, $count = 3 ) {
        $categories = wp_get_post_terms( $post_id, 'post_category', array( 'fields' => 'ids' ) );
        
        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return self::get_recent_posts( $count, $post_id );
        }
        
        return new WP_Query( array(
            'post_type'      => 'blog',
            'posts_per_page' => $count,
            'post__not_in'   => array( $post_id ),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'post_category',
                    'field'    => 'term_id',
                    'terms'    => $categories,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
    }
    
    /**
     * Calculate reading time for a blog post
     * 
     * @param int $post_id Post ID
     * @return int Reading time in minutes
     */
    public static function get_reading_time( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        // Check for manual override
        $manual_time = get_post_meta( $post_id, '_dbc_blog_reading_time', true );
        if ( $manual_time ) {
            return intval( $manual_time );
        }
        
        // Calculate based on word count (avg 200 words per minute)
        $content = get_post_field( 'post_content', $post_id );
        $word_count = str_word_count( strip_tags( $content ) );
        $reading_time = ceil( $word_count / 200 );
        
        return max( 1, $reading_time ); // Minimum 1 minute
    }
}
