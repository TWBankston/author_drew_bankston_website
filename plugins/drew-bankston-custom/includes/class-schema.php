<?php
/**
 * Schema.org Structured Data
 */

defined( 'ABSPATH' ) || exit;

class DBC_Schema {
    
    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'output_schema' ) );
    }
    
    public static function output_schema() {
        if ( is_singular( 'book' ) ) {
            self::book_schema();
        } elseif ( is_singular( 'event' ) ) {
            self::event_schema();
        } elseif ( is_front_page() ) {
            self::author_schema();
        }
    }
    
    private static function book_schema() {
        global $post;
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Book',
            'name'     => get_the_title(),
            'author'   => array(
                '@type' => 'Person',
                'name'  => 'Drew Bankston',
            ),
        );
        
        if ( has_post_thumbnail() ) {
            $schema['image'] = get_the_post_thumbnail_url( $post->ID, 'large' );
        }
        
        $isbn = get_post_meta( $post->ID, '_dbc_book_isbn_print', true );
        if ( $isbn ) {
            $schema['isbn'] = $isbn;
        }
        
        $pub_date = get_post_meta( $post->ID, '_dbc_book_pub_date', true );
        if ( $pub_date ) {
            $schema['datePublished'] = $pub_date;
        }
        
        $genres = get_the_terms( $post->ID, 'genre' );
        if ( $genres && ! is_wp_error( $genres ) ) {
            $schema['genre'] = wp_list_pluck( $genres, 'name' );
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }
    
    private static function event_schema() {
        global $post;
        
        $start = get_post_meta( $post->ID, '_dbc_event_start_datetime', true );
        $end   = get_post_meta( $post->ID, '_dbc_event_end_datetime', true );
        $location_name = get_post_meta( $post->ID, '_dbc_event_location_name', true );
        $location_addr = get_post_meta( $post->ID, '_dbc_event_location_address', true );
        $is_virtual    = get_post_meta( $post->ID, '_dbc_event_is_virtual', true );
        
        $schema = array(
            '@context'  => 'https://schema.org',
            '@type'     => 'Event',
            'name'      => get_the_title(),
            'startDate' => $start,
        );
        
        if ( $end ) {
            $schema['endDate'] = $end;
        }
        
        if ( $is_virtual ) {
            $schema['eventAttendanceMode'] = 'https://schema.org/OnlineEventAttendanceMode';
            $schema['location'] = array(
                '@type' => 'VirtualLocation',
                'url'   => get_post_meta( $post->ID, '_dbc_event_url', true ),
            );
        } elseif ( $location_name ) {
            $schema['location'] = array(
                '@type'   => 'Place',
                'name'    => $location_name,
                'address' => $location_addr,
            );
        }
        
        $schema['performer'] = array(
            '@type' => 'Person',
            'name'  => 'Drew Bankston',
        );
        
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }
    
    private static function author_schema() {
        $schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => 'Drew Bankston',
            'jobTitle'    => 'Author',
            'description' => 'Award-winning Science Fiction & Fantasy author from Colorado.',
            'url'         => home_url(),
            'sameAs'      => array(
                'https://www.facebook.com/DrewBankstonAuthor',
                'https://twitter.com/drewbankston',
                'https://www.instagram.com/drewbankston/',
                'https://www.amazon.com/stores/Drew-Bankston/author/B00J33F9PU',
                'https://www.goodreads.com/author/show/8115661.Drew_Bankston',
            ),
        );
        
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }
}


