<?php
/**
 * Genre Taxonomy
 * 
 * Standard Genres:
 * - Sci-Fi / Space Opera (Tokorel Series)
 * - Contemporary Fantasy, Adventure (Lines of Force, Sounds of Tomorrow, Imagination Stone)
 * - Non-Fiction / True Crime (The Monster's Lap)
 */

defined( 'ABSPATH' ) || exit;

class DBC_Taxonomy_Genre {
    
    /**
     * Standard genre definitions for consistency
     */
    public static $genres = array(
        'sci-fi-space-opera' => array(
            'name'        => 'Sci-Fi / Space Opera',
            'description' => 'Epic science fiction adventures spanning galaxies and civilizations.',
        ),
        'contemporary-fantasy' => array(
            'name'        => 'Contemporary Fantasy',
            'description' => 'Fantasy set in the modern world with magical elements.',
        ),
        'adventure' => array(
            'name'        => 'Adventure',
            'description' => 'Action-packed stories of exploration and excitement.',
        ),
        'non-fiction-true-crime' => array(
            'name'        => 'Non-Fiction / True Crime',
            'description' => 'Real stories exploring crime, investigation, and human nature.',
        ),
    );
    
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
    }
    
    public static function register_taxonomy() {
        $labels = array(
            'name'              => 'Genres',
            'singular_name'     => 'Genre',
            'search_items'      => 'Search Genres',
            'all_items'         => 'All Genres',
            'parent_item'       => 'Parent Genre',
            'parent_item_colon' => 'Parent Genre:',
            'edit_item'         => 'Edit Genre',
            'update_item'       => 'Update Genre',
            'add_new_item'      => 'Add New Genre',
            'new_item_name'     => 'New Genre Name',
            'menu_name'         => 'Genres',
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'genre' ),
        );
        
        register_taxonomy( 'genre', array( 'book' ), $args );
    }
    
    /**
     * Get genre display string for a book
     */
    public static function get_book_genre_display( $book_id ) {
        $genres = get_the_terms( $book_id, 'genre' );
        if ( ! $genres || is_wp_error( $genres ) ) {
            return '';
        }
        
        $names = wp_list_pluck( $genres, 'name' );
        return implode( ', ', $names );
    }
    
    /**
     * Get formatted genre label for standalone books
     */
    public static function get_standalone_genre_label( $book_id ) {
        $genre_display = self::get_book_genre_display( $book_id );
        if ( empty( $genre_display ) ) {
            return 'Standalone';
        }
        return 'Standalone ' . $genre_display;
    }
}


