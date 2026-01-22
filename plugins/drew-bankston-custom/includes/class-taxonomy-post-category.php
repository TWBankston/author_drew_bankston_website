<?php
/**
 * Post Category Taxonomy
 * 
 * Shared category taxonomy for Blog and Vlog post types
 */

defined( 'ABSPATH' ) || exit;

class DBC_Taxonomy_Post_Category {
    
    /**
     * Initialize the taxonomy
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
        add_action( 'init', array( __CLASS__, 'create_default_terms' ), 20 );
    }
    
    /**
     * Register the Post Category taxonomy
     */
    public static function register_taxonomy() {
        $labels = array(
            'name'                       => 'Post Categories',
            'singular_name'              => 'Post Category',
            'menu_name'                  => 'Categories',
            'all_items'                  => 'All Categories',
            'parent_item'                => 'Parent Category',
            'parent_item_colon'          => 'Parent Category:',
            'new_item_name'              => 'New Category Name',
            'add_new_item'               => 'Add New Category',
            'edit_item'                  => 'Edit Category',
            'update_item'                => 'Update Category',
            'view_item'                  => 'View Category',
            'separate_items_with_commas' => 'Separate categories with commas',
            'add_or_remove_items'        => 'Add or remove categories',
            'choose_from_most_used'      => 'Choose from the most used',
            'popular_items'              => 'Popular Categories',
            'search_items'               => 'Search Categories',
            'not_found'                  => 'Not Found',
            'no_terms'                   => 'No categories',
            'items_list'                 => 'Categories list',
            'items_list_navigation'      => 'Categories list navigation',
        );
        
        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true, // Like WordPress categories
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_in_rest'      => true, // Enable in Gutenberg
            'rewrite'           => array(
                'slug'         => 'topic',
                'with_front'   => false,
                'hierarchical' => true,
            ),
        );
        
        // Register for both blog and vlog post types
        register_taxonomy( 'post_category', array( 'blog', 'vlog' ), $args );
    }
    
    /**
     * Create default category terms
     */
    public static function create_default_terms() {
        $default_terms = array(
            'updates'          => array(
                'name'        => 'Updates',
                'description' => 'Book releases, website updates, and announcements',
            ),
            'writing-process'  => array(
                'name'        => 'Writing Process',
                'description' => 'Behind-the-scenes of the writing journey',
            ),
            'world-building'   => array(
                'name'        => 'World Building',
                'description' => 'Deep dives into the worlds and lore',
            ),
            'personal'         => array(
                'name'        => 'Personal',
                'description' => 'Personal stories and reflections',
            ),
            'behind-the-scenes' => array(
                'name'        => 'Behind the Scenes',
                'description' => 'Exclusive looks at the creative process',
            ),
        );
        
        foreach ( $default_terms as $slug => $term_data ) {
            if ( ! term_exists( $slug, 'post_category' ) ) {
                wp_insert_term(
                    $term_data['name'],
                    'post_category',
                    array(
                        'slug'        => $slug,
                        'description' => $term_data['description'],
                    )
                );
            }
        }
    }
    
    /**
     * Get all post categories
     * 
     * @param array $args Optional arguments for get_terms
     * @return array Array of term objects
     */
    public static function get_all_categories( $args = array() ) {
        $defaults = array(
            'taxonomy'   => 'post_category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        return get_terms( $args );
    }
    
    /**
     * Get category for a post
     * 
     * @param int $post_id Post ID
     * @return WP_Term|false First category or false
     */
    public static function get_post_category( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        
        $categories = wp_get_post_terms( $post_id, 'post_category' );
        
        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return false;
        }
        
        return $categories[0];
    }
    
    /**
     * Get category display name for a post
     * 
     * @param int $post_id Post ID
     * @return string Category name or empty string
     */
    public static function get_category_name( $post_id = null ) {
        $category = self::get_post_category( $post_id );
        return $category ? $category->name : '';
    }
}
