<?php
/**
 * Book Custom Post Type
 */

defined( 'ABSPATH' ) || exit;

class DBC_CPT_Book {
    
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type' ) );
    }
    
    public static function register_post_type() {
        $labels = array(
            'name'               => 'Books',
            'singular_name'      => 'Book',
            'menu_name'          => 'Books',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Book',
            'edit_item'          => 'Edit Book',
            'new_item'           => 'New Book',
            'view_item'          => 'View Book',
            'search_items'       => 'Search Books',
            'not_found'          => 'No books found',
            'not_found_in_trash' => 'No books found in Trash',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'books' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-book-alt',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        );
        
        register_post_type( 'book', $args );
    }
    
    /**
     * Get featured books
     */
    public static function get_featured_books( $limit = 6 ) {
        return new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => $limit,
            'meta_query'     => array(
                array(
                    'key'   => '_dbc_book_featured',
                    'value' => '1',
                ),
            ),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );
    }
    
    /**
     * Get books by series
     */
    public static function get_books_by_series( $series_slug, $limit = -1 ) {
        return new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => $limit,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'series',
                    'field'    => 'slug',
                    'terms'    => $series_slug,
                ),
            ),
            'meta_key'       => '_dbc_book_series_order',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ) );
    }
    
    /**
     * Get all books
     */
    public static function get_all_books( $limit = -1 ) {
        return new WP_Query( array(
            'post_type'      => 'book',
            'posts_per_page' => $limit,
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ) );
    }
}

