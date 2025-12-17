<?php
/**
 * Setup Books Script
 * Run this via WP-CLI or by including in WordPress
 * 
 * Creates:
 * - Series taxonomy terms (Tokorel, Standalones)
 * - Genre taxonomy terms
 * - Sample books with proper categorization
 */

// Load WordPress if not already loaded
if ( ! defined( 'ABSPATH' ) ) {
    $wp_load = dirname( __FILE__ ) . '/../../../../wp-load.php';
    if ( file_exists( $wp_load ) ) {
        require_once $wp_load;
    } else {
        die( 'WordPress not found. Run this from within WordPress context.' );
    }
}

echo "=== Drew Bankston Books Setup ===\n\n";

// Create Series
echo "Creating Series...\n";

$series_data = array(
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

foreach ( $series_data as $slug => $data ) {
    $term = term_exists( $slug, 'series' );
    if ( ! $term ) {
        $term = wp_insert_term( $data['name'], 'series', array(
            'slug'        => $slug,
            'description' => $data['description'],
        ) );
        if ( ! is_wp_error( $term ) ) {
            update_term_meta( $term['term_id'], 'primary_genre', $data['genre'] );
            echo "  Created: {$data['name']}\n";
        }
    } else {
        echo "  Exists: {$data['name']}\n";
        if ( is_array( $term ) ) {
            update_term_meta( $term['term_id'], 'primary_genre', $data['genre'] );
        }
    }
}

// Create Genres
echo "\nCreating Genres...\n";

$genres = array(
    'sci-fi-space-opera' => 'Sci-Fi / Space Opera',
    'contemporary-fantasy' => 'Contemporary Fantasy',
    'adventure' => 'Adventure',
    'non-fiction-true-crime' => 'Non-Fiction / True Crime',
);

foreach ( $genres as $slug => $name ) {
    $term = term_exists( $slug, 'genre' );
    if ( ! $term ) {
        wp_insert_term( $name, 'genre', array( 'slug' => $slug ) );
        echo "  Created: $name\n";
    } else {
        echo "  Exists: $name\n";
    }
}

// Books data
echo "\nCreating/Updating Books...\n";

$books = array(
    // Tokorel Series
    array(
        'title'        => 'Tokorel: Book One',
        'content'      => 'The first installment in the epic Tokorel space opera series. Journey across the galaxy as humanity discovers its place among the stars.',
        'series'       => 'tokorel',
        'genres'       => array( 'sci-fi-space-opera' ),
        'series_order' => 1,
        'tagline'      => 'The journey begins among the stars',
        'featured'     => true,
    ),
    array(
        'title'        => 'Tokorel: Book Two',
        'content'      => 'The saga continues in this thrilling sequel. As alliances shift and new threats emerge, the crew of the Tokorel must face their greatest challenge yet.',
        'series'       => 'tokorel',
        'genres'       => array( 'sci-fi-space-opera' ),
        'series_order' => 2,
        'tagline'      => 'The saga continues',
        'featured'     => true,
    ),
    // Standalones
    array(
        'title'        => 'Lines of Force',
        'content'      => 'A contemporary fantasy adventure that blurs the line between our world and the magical realm that exists just beyond perception.',
        'series'       => 'standalones',
        'genres'       => array( 'contemporary-fantasy', 'adventure' ),
        'tagline'      => 'Where magic meets reality',
        'featured'     => true,
    ),
    array(
        'title'        => 'Sounds of Tomorrow',
        'content'      => 'An adventure through time and possibility, where music becomes the key to unlocking hidden worlds.',
        'series'       => 'standalones',
        'genres'       => array( 'contemporary-fantasy', 'adventure' ),
        'tagline'      => 'Listen to the future',
        'featured'     => true,
    ),
    array(
        'title'        => 'Imagination Stone',
        'content'      => 'A tale of wonder and discovery, where imagination itself becomes the most powerful force in the universe.',
        'series'       => 'standalones',
        'genres'       => array( 'contemporary-fantasy', 'adventure' ),
        'tagline'      => 'Dream it into existence',
        'featured'     => true,
    ),
    array(
        'title'        => "The Monster's Lap",
        'content'      => 'A gripping true crime exploration that delves into the darkest corners of human nature and the search for justice.',
        'series'       => 'standalones',
        'genres'       => array( 'non-fiction-true-crime' ),
        'tagline'      => 'A true story of survival',
        'featured'     => true,
    ),
);

foreach ( $books as $book_data ) {
    // Check if book exists
    $existing = get_page_by_title( $book_data['title'], OBJECT, 'book' );
    
    if ( $existing ) {
        $book_id = $existing->ID;
        echo "  Exists: {$book_data['title']}\n";
    } else {
        $book_id = wp_insert_post( array(
            'post_type'    => 'book',
            'post_title'   => $book_data['title'],
            'post_content' => $book_data['content'],
            'post_status'  => 'publish',
        ) );
        echo "  Created: {$book_data['title']}\n";
    }
    
    if ( $book_id && ! is_wp_error( $book_id ) ) {
        // Set series
        wp_set_object_terms( $book_id, $book_data['series'], 'series' );
        
        // Set genres
        wp_set_object_terms( $book_id, $book_data['genres'], 'genre' );
        
        // Set meta
        if ( isset( $book_data['series_order'] ) ) {
            update_post_meta( $book_id, '_dbc_book_series_order', $book_data['series_order'] );
        }
        if ( isset( $book_data['tagline'] ) ) {
            update_post_meta( $book_id, '_dbc_book_tagline', $book_data['tagline'] );
        }
        if ( isset( $book_data['featured'] ) && $book_data['featured'] ) {
            update_post_meta( $book_id, '_dbc_book_featured', '1' );
        }
    }
}

echo "\n=== Setup Complete ===\n";
echo "Books are now categorized with proper series and genre taxonomy.\n";
echo "\nBook Categorization Summary:\n";
echo "- Tokorel Series: Sci-Fi / Space Opera (Books 1 & 2)\n";
echo "- Lines of Force: Standalone Contemporary Fantasy, Adventure\n";
echo "- Sounds of Tomorrow: Standalone Contemporary Fantasy, Adventure\n";
echo "- Imagination Stone: Standalone Contemporary Fantasy, Adventure\n";
echo "- The Monster's Lap: Standalone Non-Fiction / True Crime\n";

