<?php
/**
 * Set Free Chapter Meta for Books
 * Run this once to set up the free chapter filenames
 */

// WordPress bootstrap - script runs from /www/
require_once dirname(__FILE__) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress not loaded' );
}

echo "<h1>Setting Free Chapter Meta</h1>";

// Book slug => Free chapter filename mapping
$free_chapters = array(
    'khizara'              => 'Free Chapter - Khizara.pdf',
    'tokorel-book-2'       => 'Free Chapter Tokorel Book 2.pdf',
    'tokorel'              => 'Free Chapter Tokorel Book 2.pdf', // Alternative slug
    'lines-of-force'       => 'Free Chapter - Lines of Force.pdf',
    'sounds-of-tomorrow'   => 'Free Chapter - Sounds of Tomorrow.pdf',
    'the-imagination-stone' => 'Free Chapter - Imagination Stone.pdf',
    'imagination-stone'    => 'Free Chapter - Imagination Stone.pdf', // Alternative slug
);

// Get all books
$books = get_posts( array(
    'post_type'      => 'book',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
) );

echo "<ul>";

foreach ( $books as $book ) {
    $slug = $book->post_name;
    $title = $book->post_title;
    
    if ( isset( $free_chapters[ $slug ] ) ) {
        $filename = $free_chapters[ $slug ];
        update_post_meta( $book->ID, '_dbc_book_free_chapter', $filename );
        echo "<li>✅ <strong>{$title}</strong> (slug: {$slug}) → {$filename}</li>";
    } else {
        echo "<li>⚠️ <strong>{$title}</strong> (slug: {$slug}) → No free chapter mapping found</li>";
    }
}

echo "</ul>";
echo "<p><strong>Done!</strong> Free chapter meta has been set.</p>";
echo "<p><a href='" . admin_url( 'edit.php?post_type=book' ) . "'>Go to Books admin</a></p>";

