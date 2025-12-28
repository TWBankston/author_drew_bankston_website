<?php
/**
 * Shopping Cart Management
 * 
 * Handles cart operations for book purchases (signed copies, digital copies)
 */

defined( 'ABSPATH' ) || exit;

class DBC_Cart {
    
    const CART_SESSION_KEY = 'dbc_cart';
    
    /**
     * Initialize cart functionality
     */
    public static function init() {
        // Start session if not already started
        add_action( 'init', array( __CLASS__, 'start_session' ), 1 );
        
        // Clear cart on logout to prevent stale cart display
        add_action( 'wp_logout', array( __CLASS__, 'clear_cart' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_dbc_add_to_cart', array( __CLASS__, 'ajax_add_to_cart' ) );
        add_action( 'wp_ajax_nopriv_dbc_add_to_cart', array( __CLASS__, 'ajax_add_to_cart' ) );
        
        add_action( 'wp_ajax_dbc_update_cart', array( __CLASS__, 'ajax_update_cart' ) );
        add_action( 'wp_ajax_nopriv_dbc_update_cart', array( __CLASS__, 'ajax_update_cart' ) );
        
        add_action( 'wp_ajax_dbc_remove_from_cart', array( __CLASS__, 'ajax_remove_from_cart' ) );
        add_action( 'wp_ajax_nopriv_dbc_remove_from_cart', array( __CLASS__, 'ajax_remove_from_cart' ) );
        
        add_action( 'wp_ajax_dbc_get_cart', array( __CLASS__, 'ajax_get_cart' ) );
        add_action( 'wp_ajax_nopriv_dbc_get_cart', array( __CLASS__, 'ajax_get_cart' ) );
        
        // Register pages
        add_action( 'init', array( __CLASS__, 'register_pages' ) );
    }
    
    /**
     * Start PHP session
     */
    public static function start_session() {
        if ( ! session_id() && ! headers_sent() ) {
            session_start();
        }
    }
    
    /**
     * Register cart and checkout pages
     */
    public static function register_pages() {
        // Pages will be created via WordPress admin or programmatically
    }
    
    /**
     * Get cart contents
     */
    public static function get_cart() {
        self::start_session();
        return isset( $_SESSION[ self::CART_SESSION_KEY ] ) ? $_SESSION[ self::CART_SESSION_KEY ] : array();
    }
    
    /**
     * Add item to cart
     */
    public static function add_to_cart( $book_id, $type = 'signed', $quantity = 1 ) {
        self::start_session();
        
        $book = get_post( $book_id );
        if ( ! $book || $book->post_type !== 'book' ) {
            return false;
        }
        
        // Get price based on type
        $price = 0;
        $product_name = '';
        
        if ( $type === 'signed' ) {
            $price = floatval( get_post_meta( $book_id, '_dbc_book_signed_price', true ) );
            $product_name = $book->post_title . ' (Signed Paperback)';
        } elseif ( $type === 'digital' ) {
            $price = floatval( get_post_meta( $book_id, '_dbc_book_digital_price', true ) );
            $product_name = $book->post_title . ' (Digital Copy)';
        }
        
        if ( $price <= 0 ) {
            return false;
        }
        
        // Generate unique cart item key
        $cart_key = $book_id . '_' . $type;
        
        // Get current cart
        $cart = self::get_cart();
        
        // Add or update item
        if ( isset( $cart[ $cart_key ] ) ) {
            // For digital, quantity is always 1
            if ( $type === 'digital' ) {
                $cart[ $cart_key ]['quantity'] = 1;
            } else {
                $cart[ $cart_key ]['quantity'] += $quantity;
            }
        } else {
            $cart[ $cart_key ] = array(
                'book_id'      => $book_id,
                'type'         => $type,
                'name'         => $product_name,
                'price'        => $price,
                'quantity'     => ( $type === 'digital' ) ? 1 : $quantity,
                'thumbnail'    => get_the_post_thumbnail_url( $book_id, 'medium' ),
            );
        }
        
        $_SESSION[ self::CART_SESSION_KEY ] = $cart;
        
        return true;
    }
    
    /**
     * Update cart item quantity
     */
    public static function update_quantity( $cart_key, $quantity ) {
        self::start_session();
        
        $cart = self::get_cart();
        
        if ( ! isset( $cart[ $cart_key ] ) ) {
            return false;
        }
        
        // Digital items always have quantity 1
        if ( $cart[ $cart_key ]['type'] === 'digital' ) {
            $quantity = 1;
        }
        
        if ( $quantity <= 0 ) {
            unset( $cart[ $cart_key ] );
        } else {
            $cart[ $cart_key ]['quantity'] = intval( $quantity );
        }
        
        $_SESSION[ self::CART_SESSION_KEY ] = $cart;
        
        return true;
    }
    
    /**
     * Remove item from cart
     */
    public static function remove_from_cart( $cart_key ) {
        self::start_session();
        
        $cart = self::get_cart();
        
        if ( isset( $cart[ $cart_key ] ) ) {
            unset( $cart[ $cart_key ] );
            $_SESSION[ self::CART_SESSION_KEY ] = $cart;
            return true;
        }
        
        return false;
    }
    
    /**
     * Clear cart
     */
    public static function clear_cart() {
        self::start_session();
        $_SESSION[ self::CART_SESSION_KEY ] = array();
    }
    
    /**
     * Get cart count
     */
    public static function get_cart_count() {
        $cart = self::get_cart();
        $count = 0;
        
        foreach ( $cart as $item ) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    /**
     * Get cart subtotal
     */
    public static function get_subtotal() {
        $cart = self::get_cart();
        $subtotal = 0;
        
        foreach ( $cart as $item ) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        return $subtotal;
    }
    
    /**
     * Check if cart has physical items (needs shipping)
     */
    public static function has_physical_items() {
        $cart = self::get_cart();
        
        foreach ( $cart as $item ) {
            if ( $item['type'] === 'signed' ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calculate shipping (placeholder)
     */
    public static function get_shipping_cost() {
        if ( ! self::has_physical_items() ) {
            return 0;
        }
        
        // Flat rate shipping for now
        // TODO: Integrate with shipping calculator
        return 5.99;
    }
    
    /**
     * Get cart total
     */
    public static function get_total() {
        return self::get_subtotal() + self::get_shipping_cost();
    }
    
    /**
     * AJAX: Add to cart
     */
    public static function ajax_add_to_cart() {
        check_ajax_referer( 'dbc_cart_nonce', 'nonce' );
        
        $book_id = isset( $_POST['book_id'] ) ? intval( $_POST['book_id'] ) : 0;
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'signed';
        $quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
        
        if ( ! $book_id ) {
            wp_send_json_error( array( 'message' => 'Invalid book ID' ) );
        }
        
        $result = self::add_to_cart( $book_id, $type, $quantity );
        
        if ( $result ) {
            wp_send_json_success( array(
                'message'    => 'Added to cart!',
                'cart_count' => self::get_cart_count(),
                'cart_total' => self::get_total(),
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Could not add to cart' ) );
        }
    }
    
    /**
     * AJAX: Update cart
     */
    public static function ajax_update_cart() {
        check_ajax_referer( 'dbc_cart_nonce', 'nonce' );
        
        $cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
        $quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
        
        if ( ! $cart_key ) {
            wp_send_json_error( array( 'message' => 'Invalid cart item' ) );
        }
        
        self::update_quantity( $cart_key, $quantity );
        
        wp_send_json_success( array(
            'cart'       => self::get_cart(),
            'cart_count' => self::get_cart_count(),
            'subtotal'   => self::get_subtotal(),
            'shipping'   => self::get_shipping_cost(),
            'total'      => self::get_total(),
        ) );
    }
    
    /**
     * AJAX: Remove from cart
     */
    public static function ajax_remove_from_cart() {
        check_ajax_referer( 'dbc_cart_nonce', 'nonce' );
        
        $cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
        
        if ( ! $cart_key ) {
            wp_send_json_error( array( 'message' => 'Invalid cart item' ) );
        }
        
        self::remove_from_cart( $cart_key );
        
        wp_send_json_success( array(
            'cart'       => self::get_cart(),
            'cart_count' => self::get_cart_count(),
            'subtotal'   => self::get_subtotal(),
            'shipping'   => self::get_shipping_cost(),
            'total'      => self::get_total(),
        ) );
    }
    
    /**
     * AJAX: Get cart contents
     */
    public static function ajax_get_cart() {
        wp_send_json_success( array(
            'cart'       => self::get_cart(),
            'cart_count' => self::get_cart_count(),
            'subtotal'   => self::get_subtotal(),
            'shipping'   => self::get_shipping_cost(),
            'total'      => self::get_total(),
            'has_physical' => self::has_physical_items(),
        ) );
    }
}

