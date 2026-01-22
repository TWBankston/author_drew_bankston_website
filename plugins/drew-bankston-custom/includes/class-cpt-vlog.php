<?php
/**
 * Vlog Custom Post Type
 * 
 * Registers the 'vlog' post type for video content
 * Supports both YouTube embeds and local video files
 */

defined( 'ABSPATH' ) || exit;

class DBC_CPT_Vlog {
    
    /**
     * Initialize the Vlog CPT
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
        add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );
    }
    
    /**
     * Register the Vlog post type
     */
    public static function register_post_type() {
        $labels = array(
            'name'                  => 'Vlogs',
            'singular_name'         => 'Vlog',
            'menu_name'             => 'Vlogs',
            'name_admin_bar'        => 'Vlog',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Vlog',
            'new_item'              => 'New Vlog',
            'edit_item'             => 'Edit Vlog',
            'view_item'             => 'View Vlog',
            'all_items'             => 'All Vlogs',
            'search_items'          => 'Search Vlogs',
            'parent_item_colon'     => 'Parent Vlogs:',
            'not_found'             => 'No vlogs found.',
            'not_found_in_trash'    => 'No vlogs found in Trash.',
            'featured_image'        => 'Thumbnail Image',
            'set_featured_image'    => 'Set thumbnail image',
            'remove_featured_image' => 'Remove thumbnail image',
            'use_featured_image'    => 'Use as thumbnail image',
            'archives'              => 'Vlog Archives',
            'insert_into_item'      => 'Insert into vlog',
            'uploaded_to_this_item' => 'Uploaded to this vlog',
            'filter_items_list'     => 'Filter vlogs list',
            'items_list_navigation' => 'Vlogs list navigation',
            'items_list'            => 'Vlogs list',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 
                'slug'       => 'vlog',
                'with_front' => false,
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-video-alt3',
            'supports'           => array( 
                'title', 
                'editor', 
                'thumbnail', 
                'excerpt',
                'comments',
                'revisions',
            ),
            'show_in_rest'       => true, // Enable Gutenberg editor
        );
        
        register_post_type( 'vlog', $args );
    }
    
    /**
     * Custom update messages for Vlog post type
     */
    public static function updated_messages( $messages ) {
        global $post;
        
        $permalink = get_permalink( $post );
        
        $messages['vlog'] = array(
            0  => '', // Unused
            1  => sprintf( 'Vlog updated. <a target="_blank" href="%s">View vlog</a>', esc_url( $permalink ) ),
            2  => 'Custom field updated.',
            3  => 'Custom field deleted.',
            4  => 'Vlog updated.',
            5  => isset( $_GET['revision'] ) ? sprintf( 'Vlog restored to revision from %s', wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( 'Vlog published. <a href="%s">View vlog</a>', esc_url( $permalink ) ),
            7  => 'Vlog saved.',
            8  => sprintf( 'Vlog submitted. <a target="_blank" href="%s">Preview vlog</a>', esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
            9  => sprintf( 'Vlog scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview vlog</a>', date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ), esc_url( $permalink ) ),
            10 => sprintf( 'Vlog draft updated. <a target="_blank" href="%s">Preview vlog</a>', esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
        );
        
        return $messages;
    }
    
    /**
     * Get recent vlogs
     * 
     * @param int $count Number of vlogs to retrieve
     * @param int $exclude_id Vlog ID to exclude (optional)
     * @return WP_Query
     */
    public static function get_recent_vlogs( $count = 6, $exclude_id = 0 ) {
        $args = array(
            'post_type'      => 'vlog',
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
     * Get related vlogs by category
     * 
     * @param int $post_id Current vlog ID
     * @param int $count Number of vlogs to retrieve
     * @return WP_Query
     */
    public static function get_related_vlogs( $post_id, $count = 3 ) {
        $categories = wp_get_post_terms( $post_id, 'post_category', array( 'fields' => 'ids' ) );
        
        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return self::get_recent_vlogs( $count, $post_id );
        }
        
        return new WP_Query( array(
            'post_type'      => 'vlog',
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
     * Get video source type
     * 
     * @param int $post_id Post ID
     * @return string 'youtube' or 'local'
     */
    public static function get_video_source( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $source = get_post_meta( $post_id, '_dbc_vlog_video_source', true );
        return $source ? $source : 'youtube';
    }
    
    /**
     * Get YouTube video ID from URL or ID
     * 
     * @param int $post_id Post ID
     * @return string|false YouTube video ID or false
     */
    public static function get_youtube_id( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $youtube_url = get_post_meta( $post_id, '_dbc_vlog_youtube_url', true );
        
        if ( empty( $youtube_url ) ) {
            return false;
        }
        
        // If it's already just an ID (11 characters, alphanumeric with dashes/underscores)
        if ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $youtube_url ) ) {
            return $youtube_url;
        }
        
        // Extract ID from various YouTube URL formats
        $patterns = array(
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        );
        
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $youtube_url, $matches ) ) {
                return $matches[1];
            }
        }
        
        return false;
    }
    
    /**
     * Get YouTube embed URL
     * 
     * @param int $post_id Post ID
     * @return string|false Embed URL or false
     */
    public static function get_youtube_embed_url( $post_id = null ) {
        $video_id = self::get_youtube_id( $post_id );
        
        if ( ! $video_id ) {
            return false;
        }
        
        return 'https://www.youtube.com/embed/' . $video_id . '?rel=0&modestbranding=1';
    }
    
    /**
     * Get local video URL
     * 
     * @param int $post_id Post ID
     * @return string|false Video URL or false
     */
    public static function get_local_video_url( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $video_url = get_post_meta( $post_id, '_dbc_vlog_local_video_url', true );
        return $video_url ? $video_url : false;
    }
    
    /**
     * Get video duration
     * 
     * @param int $post_id Post ID
     * @return string Formatted duration (e.g., "14:32")
     */
    public static function get_duration( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $duration = get_post_meta( $post_id, '_dbc_vlog_duration', true );
        return $duration ? $duration : '';
    }
    
    /**
     * Get vlog number
     * 
     * @param int $post_id Post ID
     * @return string Vlog number (e.g., "014")
     */
    public static function get_vlog_number( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $number = get_post_meta( $post_id, '_dbc_vlog_number', true );
        return $number ? $number : '';
    }
    
    /**
     * Get video chapters
     * 
     * @param int $post_id Post ID
     * @return array Array of chapters with 'timestamp' and 'title'
     */
    public static function get_chapters( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $chapters = get_post_meta( $post_id, '_dbc_vlog_chapters', true );
        
        if ( empty( $chapters ) || ! is_array( $chapters ) ) {
            return array();
        }
        
        // Filter out empty chapters
        return array_filter( $chapters, function( $chapter ) {
            return ! empty( $chapter['timestamp'] ) && ! empty( $chapter['title'] );
        } );
    }
    
    /**
     * Get YouTube thumbnail URL
     * 
     * @param int $post_id Post ID
     * @param string $size Thumbnail size: 'default', 'medium', 'high', 'standard', 'maxres'
     * @return string|false Thumbnail URL or false
     */
    public static function get_youtube_thumbnail( $post_id = null, $size = 'maxresdefault' ) {
        $video_id = self::get_youtube_id( $post_id );
        
        if ( ! $video_id ) {
            return false;
        }
        
        return 'https://img.youtube.com/vi/' . $video_id . '/' . $size . '.jpg';
    }
}
