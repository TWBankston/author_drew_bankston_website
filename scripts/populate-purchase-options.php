<?php
/**
 * Populate Purchase Options for Books
 * 
 * Run this script once to set up Amazon URLs, signed copy prices, and digital copy options.
 * Access via: https://dbankston.wordkeeper.net/wp-content/themes/drew-bankston/populate-purchase.php
 */

// For WordPress context - this would be run via WP-CLI or as a one-time script
// wp eval-file scripts/populate-purchase-options.php

$books_data = array(
    'khizara' => array(
        'amazon_url'     => 'https://www.amazon.com/gp/product/0997554711?ref_=dbs_m_mng_rwt_calw_tpbk_0&storeType=ebooks&qid=1759106183&sr=1-1',
        'signed_enabled' => '1',
        'signed_price'   => '17.99',
        'digital_enabled' => '',
        'digital_price'  => '',
    ),
    'tokorel-book-2' => array(
        'amazon_url'     => 'https://www.amazon.com/gp/product/B0DP5HBHZS?ref_=dbs_m_mng_rwt_calw_tpbk_1&storeType=ebooks&qid=1759106183&sr=1-1',
        'signed_enabled' => '1',
        'signed_price'   => '13.99',
        'digital_enabled' => '',
        'digital_price'  => '',
    ),
    'the-imagination-stone' => array(
        'amazon_url'     => 'https://www.amazon.com/Imagination-Stone-Drew-Bankston-ebook/dp/B0DSZHQMTD',
        'signed_enabled' => '1',
        'signed_price'   => '10.99',
        'digital_enabled' => '1',
        'digital_price'  => '4.99',
    ),
    'lines-of-force' => array(
        'amazon_url'     => 'https://www.amazon.com/Lines-Force-Weekend-Adventures-Andrew/dp/B0DJ2J76XS/',
        'signed_enabled' => '1',
        'signed_price'   => '11.99',
        'digital_enabled' => '1',
        'digital_price'  => '4.99',
    ),
    'sounds-of-tomorrow' => array(
        // No data provided - leave as is or add later
        'amazon_url'     => '',
        'signed_enabled' => '',
        'signed_price'   => '',
        'digital_enabled' => '',
        'digital_price'  => '',
    ),
    'the-monsters-lap' => array(
        'amazon_url'     => 'https://www.amazon.com/Monsters-Lap-Mr-Drew-Bankston/dp/0988696657',
        'signed_enabled' => '',
        'signed_price'   => '',
        'digital_enabled' => '',
        'digital_price'  => '',
    ),
);

echo "=== Populating Book Purchase Options ===\n\n";

foreach ( $books_data as $slug => $data ) {
    $book = get_page_by_path( $slug, OBJECT, 'book' );
    
    if ( ! $book ) {
        echo "❌ Book not found: {$slug}\n";
        continue;
    }
    
    echo "📚 Updating: {$book->post_title} (ID: {$book->ID})\n";
    
    if ( ! empty( $data['amazon_url'] ) ) {
        update_post_meta( $book->ID, '_dbc_book_amazon_url', esc_url_raw( $data['amazon_url'] ) );
        echo "   ✓ Amazon URL set\n";
    }
    
    if ( ! empty( $data['signed_enabled'] ) ) {
        update_post_meta( $book->ID, '_dbc_book_signed_enabled', $data['signed_enabled'] );
        update_post_meta( $book->ID, '_dbc_book_signed_price', $data['signed_price'] );
        echo "   ✓ Signed copy: \${$data['signed_price']} + S&H\n";
    }
    
    if ( ! empty( $data['digital_enabled'] ) ) {
        update_post_meta( $book->ID, '_dbc_book_digital_enabled', $data['digital_enabled'] );
        update_post_meta( $book->ID, '_dbc_book_digital_price', $data['digital_price'] );
        echo "   ✓ Digital copy: \${$data['digital_price']}\n";
    }
    
    echo "\n";
}

echo "=== Complete ===\n";

