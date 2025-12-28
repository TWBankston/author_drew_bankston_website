<?php
/**
 * Fix Book Taxonomies Script
 * Run this via WP-CLI or upload to WordPress and access directly
 * 
 * Book Taxonomy Requirements:
 * 
 * Tokorel Series (Sci-Fi / Space Opera):
 *   - Khizara (Book One)
 *   - Tokorel (Book Two)
 * 
 * Standalones:
 *   - Lines of Force: Contemporary Fantasy, Adventure
 *   - Sounds of Tomorrow: Contemporary Fantasy, Adventure
 *   - Imagination Stone: Contemporary Fantasy, Adventure
 *   - The Monster's Lap: Non-Fiction, True Crime
 */

// Load WordPress
$wp_load_paths = [
    dirname(__FILE__) . '/../../../../wp-load.php',  // From theme
    dirname(__FILE__) . '/../../../wp-load.php',      // From plugins
    dirname(__FILE__) . '/wp-load.php',               // Same directory
    '/www/wp-load.php',                                // Absolute for wordkeeper
];

$loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    die("Could not find wp-load.php. Please run this from WordPress installation.");
}

echo "<pre>\n";
echo "===========================================\n";
echo "Book Taxonomy Fixer\n";
echo "===========================================\n\n";

// Step 1: Ensure required genres exist
echo "Step 1: Creating/verifying genre terms...\n";

$required_genres = [
    'sci-fi-space-opera' => 'Sci-Fi / Space Opera',
    'contemporary-fantasy' => 'Contemporary Fantasy',
    'adventure' => 'Adventure',
    'non-fiction' => 'Non-Fiction',
    'true-crime' => 'True Crime',
    'fantasy' => 'Fantasy',
    'young-adult' => 'Young Adult',
    'science-fiction' => 'Science Fiction',
];

foreach ($required_genres as $slug => $name) {
    $term = get_term_by('slug', $slug, 'genre');
    if (!$term) {
        $result = wp_insert_term($name, 'genre', ['slug' => $slug]);
        if (is_wp_error($result)) {
            echo "  ❌ Failed to create genre '$name': " . $result->get_error_message() . "\n";
        } else {
            echo "  ✅ Created genre: $name ($slug)\n";
        }
    } else {
        echo "  ✓ Genre exists: $name ($slug)\n";
    }
}

// Step 2: Ensure required series exist
echo "\nStep 2: Creating/verifying series terms...\n";

$required_series = [
    'tokorel' => 'Tokorel',
    'standalones' => 'Standalones',
];

foreach ($required_series as $slug => $name) {
    $term = get_term_by('slug', $slug, 'series');
    if (!$term) {
        $result = wp_insert_term($name, 'series', ['slug' => $slug]);
        if (is_wp_error($result)) {
            echo "  ❌ Failed to create series '$name': " . $result->get_error_message() . "\n";
        } else {
            echo "  ✅ Created series: $name ($slug)\n";
        }
    } else {
        echo "  ✓ Series exists: $name ($slug)\n";
    }
}

// Step 3: Get all books
echo "\nStep 3: Fetching all books...\n";

$books = get_posts([
    'post_type' => 'book',
    'posts_per_page' => -1,
    'post_status' => 'any',
]);

echo "  Found " . count($books) . " books.\n";

// Step 4: Define book configurations
$book_config = [
    // Tokorel Series
    'khizara' => [
        'series' => 'tokorel',
        'genres' => ['sci-fi-space-opera'],
        'series_order' => 1,
    ],
    'tokorel' => [
        'series' => 'tokorel',
        'genres' => ['sci-fi-space-opera'],
        'series_order' => 2,
    ],
    'tokorel-book-2' => [
        'series' => 'tokorel',
        'genres' => ['sci-fi-space-opera'],
        'series_order' => 2,
    ],
    
    // Standalones
    'lines-of-force' => [
        'series' => 'standalones',
        'genres' => ['contemporary-fantasy', 'adventure'],
    ],
    'sounds-of-tomorrow' => [
        'series' => 'standalones',
        'genres' => ['contemporary-fantasy', 'adventure'],
    ],
    'the-imagination-stone' => [
        'series' => 'standalones',
        'genres' => ['contemporary-fantasy', 'adventure'],
    ],
    'imagination-stone' => [
        'series' => 'standalones',
        'genres' => ['contemporary-fantasy', 'adventure'],
    ],
    'the-monsters-lap' => [
        'series' => 'standalones',
        'genres' => ['non-fiction', 'true-crime'],
    ],
    'monsters-lap' => [
        'series' => 'standalones',
        'genres' => ['non-fiction', 'true-crime'],
    ],
];

// Step 5: Update each book
echo "\nStep 4: Updating book taxonomies...\n\n";

foreach ($books as $book) {
    $slug = $book->post_name;
    $title = $book->post_title;
    
    echo "📖 $title (slug: $slug)\n";
    
    // Check if we have config for this book
    $config = null;
    foreach ($book_config as $config_slug => $cfg) {
        if (strpos($slug, $config_slug) !== false || $slug === $config_slug) {
            $config = $cfg;
            break;
        }
    }
    
    if (!$config) {
        // Try matching by title
        $title_lower = strtolower($title);
        if (strpos($title_lower, 'khizara') !== false) {
            $config = $book_config['khizara'];
        } elseif (strpos($title_lower, 'tokorel') !== false) {
            $config = $book_config['tokorel'];
        } elseif (strpos($title_lower, 'lines of force') !== false) {
            $config = $book_config['lines-of-force'];
        } elseif (strpos($title_lower, 'sounds of tomorrow') !== false) {
            $config = $book_config['sounds-of-tomorrow'];
        } elseif (strpos($title_lower, 'imagination stone') !== false) {
            $config = $book_config['the-imagination-stone'];
        } elseif (strpos($title_lower, 'monster') !== false) {
            $config = $book_config['the-monsters-lap'];
        }
    }
    
    if (!$config) {
        echo "   ⚠️  No configuration found for this book. Skipping.\n\n";
        continue;
    }
    
    // Update series
    $series_result = wp_set_object_terms($book->ID, $config['series'], 'series');
    if (is_wp_error($series_result)) {
        echo "   ❌ Failed to set series: " . $series_result->get_error_message() . "\n";
    } else {
        echo "   ✅ Series: " . $config['series'] . "\n";
    }
    
    // Update genres
    $genre_result = wp_set_object_terms($book->ID, $config['genres'], 'genre');
    if (is_wp_error($genre_result)) {
        echo "   ❌ Failed to set genres: " . $genre_result->get_error_message() . "\n";
    } else {
        echo "   ✅ Genres: " . implode(', ', $config['genres']) . "\n";
    }
    
    // Update series order if applicable
    if (isset($config['series_order'])) {
        update_post_meta($book->ID, '_dbc_book_series_order', $config['series_order']);
        echo "   ✅ Series Order: " . $config['series_order'] . "\n";
    }
    
    echo "\n";
}

// Step 6: Verify the changes
echo "===========================================\n";
echo "Verification\n";
echo "===========================================\n\n";

echo "Tokorel Series Books:\n";
$tokorel_books = get_posts([
    'post_type' => 'book',
    'posts_per_page' => -1,
    'tax_query' => [
        [
            'taxonomy' => 'series',
            'field' => 'slug',
            'terms' => 'tokorel',
        ],
    ],
]);
foreach ($tokorel_books as $book) {
    $genres = wp_get_object_terms($book->ID, 'genre', ['fields' => 'names']);
    echo "  - " . $book->post_title . " [" . implode(', ', $genres) . "]\n";
}

echo "\nStandalone Books:\n";
$standalone_books = get_posts([
    'post_type' => 'book',
    'posts_per_page' => -1,
    'tax_query' => [
        [
            'taxonomy' => 'series',
            'field' => 'slug',
            'terms' => 'standalones',
        ],
    ],
]);
foreach ($standalone_books as $book) {
    $genres = wp_get_object_terms($book->ID, 'genre', ['fields' => 'names']);
    echo "  - " . $book->post_title . " [" . implode(', ', $genres) . "]\n";
}

echo "\nAll Genres:\n";
$all_genres = get_terms(['taxonomy' => 'genre', 'hide_empty' => false]);
foreach ($all_genres as $genre) {
    $count = $genre->count;
    echo "  - " . $genre->name . " ($genre->slug) - $count books\n";
}

echo "\n===========================================\n";
echo "Done!\n";
echo "===========================================\n";
echo "</pre>";


