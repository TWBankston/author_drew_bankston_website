<?php
/**
 * Series Taxonomy
 * 
 * Book Series:
 * - Tokorel Series (Sci-Fi / Space Opera) - Books 1 & 2, with room for Book 3
 * - Standalones (various genres)
 */

defined( 'ABSPATH' ) || exit;

class DBC_Taxonomy_Series {
    
    /**
     * Standard series definitions
     */
    public static $series = array(
        'tokorel' => array(
            'name'        => 'Tokorel Series',
            'description' => 'An epic sci-fi space opera series exploring the far reaches of the galaxy.',
            'genre'       => 'Sci-Fi / Space Opera',
        ),
        'standalones' => array(
            'name'        => 'Standalones',
            'description' => 'Individual works spanning multiple genres.',
            'genre'       => 'Various',
        ),
    );
    
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
        add_action( 'series_add_form_fields', array( __CLASS__, 'add_series_fields' ) );
        add_action( 'series_edit_form_fields', array( __CLASS__, 'edit_series_fields' ) );
        add_action( 'created_series', array( __CLASS__, 'save_series_fields' ) );
        add_action( 'edited_series', array( __CLASS__, 'save_series_fields' ) );
    }
    
    public static function register_taxonomy() {
        $labels = array(
            'name'              => 'Series',
            'singular_name'     => 'Series',
            'search_items'      => 'Search Series',
            'all_items'         => 'All Series',
            'parent_item'       => 'Parent Series',
            'parent_item_colon' => 'Parent Series:',
            'edit_item'         => 'Edit Series',
            'update_item'       => 'Update Series',
            'add_new_item'      => 'Add New Series',
            'new_item_name'     => 'New Series Name',
            'menu_name'         => 'Series',
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'series' ),
        );
        
        register_taxonomy( 'series', array( 'book' ), $args );
    }
    
    public static function add_series_fields() {
        ?>
        <div class="form-field">
            <label for="series_primary_genre">Primary Genre</label>
            <input type="text" name="series_primary_genre" id="series_primary_genre" value="">
            <p class="description">The primary genre for this series (e.g., "Sci-Fi / Space Opera")</p>
        </div>
        <?php
    }
    
    public static function edit_series_fields( $term ) {
        $genre = get_term_meta( $term->term_id, 'primary_genre', true );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="series_primary_genre">Primary Genre</label></th>
            <td>
                <input type="text" name="series_primary_genre" id="series_primary_genre" value="<?php echo esc_attr( $genre ); ?>">
                <p class="description">The primary genre for this series (e.g., "Sci-Fi / Space Opera")</p>
            </td>
        </tr>
        <?php
    }
    
    public static function save_series_fields( $term_id ) {
        if ( isset( $_POST['series_primary_genre'] ) ) {
            update_term_meta( $term_id, 'primary_genre', sanitize_text_field( $_POST['series_primary_genre'] ) );
        }
    }
    
    /**
     * Get series with books
     */
    public static function get_series_with_books() {
        $series = get_terms( array(
            'taxonomy'   => 'series',
            'hide_empty' => true,
        ) );
        
        $result = array();
        foreach ( $series as $s ) {
            $s->books = DBC_CPT_Book::get_books_by_series( $s->slug );
            $s->primary_genre = get_term_meta( $s->term_id, 'primary_genre', true );
            $result[] = $s;
        }
        
        return $result;
    }
}


