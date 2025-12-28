<?php
/**
 * Create Account Page
 * Run this once to create the account page
 */

// WordPress bootstrap
require_once dirname(__FILE__) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress not loaded' );
}

echo "<h1>Creating Account Page</h1>";

// Check if page exists
$account_page = get_page_by_path( 'account' );

if ( $account_page ) {
    echo "<p>✅ Account page already exists (ID: {$account_page->ID})</p>";
} else {
    // Create the page
    $page_id = wp_insert_post( array(
        'post_title'    => 'My Account',
        'post_name'     => 'account',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_content'  => '', // Template handles content
        'page_template' => 'page-account.php',
    ) );
    
    if ( is_wp_error( $page_id ) ) {
        echo "<p>❌ Error creating page: " . $page_id->get_error_message() . "</p>";
    } else {
        echo "<p>✅ Account page created successfully! (ID: {$page_id})</p>";
    }
}

echo "<p><a href='" . home_url( '/account/' ) . "'>Go to Account Page</a></p>";
echo "<p><a href='" . admin_url( 'edit.php?post_type=page' ) . "'>Go to Pages admin</a></p>";

