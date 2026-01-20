<?php
/**
 * Script to update Kindle URLs for all books
 * Run via: /wp-admin/?update_kindle_urls=1
 */

// Only run if the correct parameter is set and user is admin
add_action( 'init', function() {
    if ( ! isset( $_GET['update_kindle_urls'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Book ID => Kindle URL mapping
    $kindle_urls = array(
        9  => 'https://www.amazon.com/Khizara-Book-Tokorel-Drew-Bankston-ebook/dp/B0C8S4TJWW/ref=tmm_kin_swatch_0', // Khizara
        10 => 'https://www.amazon.com/Tokorel-Book-2-Drew-Bankston-ebook/dp/B0DB4LXC68/ref=sr_1_1', // Tokorel
        11 => 'https://www.amazon.com/Lines-Force-Weekend-Adventures-Andrew-ebook/dp/B0DG9W4P23/ref=sr_1_1', // Lines of Force
        12 => 'https://www.amazon.com/Monsters-Lap-Drew-Bankston-ebook/dp/B0151Z52BU/ref=sr_1_1', // Monster's Lap
        13 => 'https://www.amazon.com/Imagination-Stone-Drew-Bankston-ebook/dp/B0DSZHQMTD/ref=sr_1_1', // Imagination Stone
        14 => 'https://www.amazon.com/Sounds-Tomorrow-Drew-Bankston-ebook/dp/B00IP4R8IU/ref=sr_1_1', // Sounds of Tomorrow
    );

    $updated = array();

    foreach ( $kindle_urls as $post_id => $kindle_url ) {
        $post = get_post( $post_id );
        if ( $post && $post->post_type === 'book' ) {
            update_post_meta( $post_id, '_dbc_book_kindle_url', esc_url_raw( $kindle_url ) );
            $updated[] = $post->post_title . ' (ID: ' . $post_id . ')';
        }
    }

    $output = '<h1>✓ Kindle URLs Updated!</h1>';
    $output .= '<p>Updated ' . count( $updated ) . ' books:</p>';
    $output .= '<ul><li>' . implode( '</li><li>', $updated ) . '</li></ul>';
    $output .= '<p><a href="' . admin_url( 'edit.php?post_type=book' ) . '">Back to Books</a></p>';

    wp_die( $output, 'Update Kindle URLs', array( 'response' => 200 ) );
}, 100 );
