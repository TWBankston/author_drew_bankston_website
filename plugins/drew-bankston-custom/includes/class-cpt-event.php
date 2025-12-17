<?php
/**
 * Event Custom Post Type
 */

defined( 'ABSPATH' ) || exit;

class DBC_CPT_Event {
    
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
    }
    
    public static function register_post_type() {
        $labels = array(
            'name'               => 'Events',
            'singular_name'      => 'Event',
            'menu_name'          => 'Events',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Event',
            'edit_item'          => 'Edit Event',
            'new_item'           => 'New Event',
            'view_item'          => 'View Event',
            'search_items'       => 'Search Events',
            'not_found'          => 'No events found',
            'not_found_in_trash' => 'No events found in Trash',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'events' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        );
        
        register_post_type( 'event', $args );
    }
    
    /**
     * Get upcoming events
     */
    public static function get_upcoming_events( $limit = 3 ) {
        return new WP_Query( array(
            'post_type'      => 'event',
            'posts_per_page' => $limit,
            'meta_key'       => '_dbc_event_start_datetime',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_dbc_event_start_datetime',
                    'value'   => current_time( 'Y-m-d H:i:s' ),
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ),
            ),
        ) );
    }
    
    /**
     * Get past events
     */
    public static function get_past_events( $limit = 10 ) {
        return new WP_Query( array(
            'post_type'      => 'event',
            'posts_per_page' => $limit,
            'meta_key'       => '_dbc_event_start_datetime',
            'orderby'        => 'meta_value',
            'order'          => 'DESC',
            'meta_query'     => array(
                array(
                    'key'     => '_dbc_event_start_datetime',
                    'value'   => current_time( 'Y-m-d H:i:s' ),
                    'compare' => '<',
                    'type'    => 'DATETIME',
                ),
            ),
        ) );
    }
}

