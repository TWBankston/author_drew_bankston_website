<?php
/**
 * Analytics Tracking Code Injection
 *
 * @package Drew_Bankston_Analytics
 */

defined( 'ABSPATH' ) || exit;

class DBA_Tracking {

    /**
     * Initialize tracking
     */
    public static function init() {
        // Only add tracking on frontend
        if ( is_admin() ) {
            return;
        }

        // Add tracking codes
        add_action( 'wp_head', array( __CLASS__, 'inject_head_tracking' ), 1 );
        add_action( 'wp_body_open', array( __CLASS__, 'inject_body_tracking' ), 1 );
        
        // Fallback for themes that don't support wp_body_open
        add_action( 'wp_footer', array( __CLASS__, 'inject_gtm_noscript_fallback' ), 1 );
    }

    /**
     * Check if tracking should be excluded for current user
     */
    private static function should_exclude_user() {
        if ( '1' === get_option( 'dba_exclude_admins', '1' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Inject tracking codes in <head>
     */
    public static function inject_head_tracking() {
        // Check if we should exclude this user
        if ( self::should_exclude_user() ) {
            echo "\n<!-- Drew Bankston Analytics: Tracking disabled for admin users -->\n";
            return;
        }

        $output = '';

        // Google Tag Manager - Head Script
        if ( '1' === get_option( 'dba_gtm_enabled', '0' ) ) {
            $gtm_id = get_option( 'dba_gtm_container_id', '' );
            if ( ! empty( $gtm_id ) ) {
                $gtm_id = esc_js( $gtm_id );
                $output .= <<<GTM

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$gtm_id}');</script>
<!-- End Google Tag Manager -->

GTM;
            }
        }

        // Google Analytics 4 - gtag.js
        if ( '1' === get_option( 'dba_ga4_enabled', '0' ) ) {
            $ga4_id = get_option( 'dba_ga4_measurement_id', '' );
            if ( ! empty( $ga4_id ) ) {
                $ga4_id = esc_js( $ga4_id );
                $output .= <<<GA4

<!-- Google Analytics 4 (GA4) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$ga4_id}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$ga4_id}');
</script>
<!-- End Google Analytics 4 -->

GA4;
            }
        }

        if ( ! empty( $output ) ) {
            echo $output;
        }
    }

    /**
     * Inject GTM noscript in body (for wp_body_open hook)
     */
    public static function inject_body_tracking() {
        // Check if we should exclude this user
        if ( self::should_exclude_user() ) {
            return;
        }

        // Google Tag Manager - noscript fallback
        if ( '1' === get_option( 'dba_gtm_enabled', '0' ) ) {
            $gtm_id = get_option( 'dba_gtm_container_id', '' );
            if ( ! empty( $gtm_id ) ) {
                $gtm_id = esc_attr( $gtm_id );
                echo <<<GTMNOSCRIPT

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$gtm_id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

GTMNOSCRIPT;
            }
        }

        // Mark that wp_body_open was called
        global $dba_body_open_called;
        $dba_body_open_called = true;
    }

    /**
     * Fallback for themes without wp_body_open support
     * Injects GTM noscript at end of page if wp_body_open wasn't called
     */
    public static function inject_gtm_noscript_fallback() {
        global $dba_body_open_called;
        
        // Only run if wp_body_open wasn't called
        if ( ! empty( $dba_body_open_called ) ) {
            return;
        }

        // Check if we should exclude this user
        if ( self::should_exclude_user() ) {
            return;
        }

        // Google Tag Manager - noscript fallback (at end of page)
        if ( '1' === get_option( 'dba_gtm_enabled', '0' ) ) {
            $gtm_id = get_option( 'dba_gtm_container_id', '' );
            if ( ! empty( $gtm_id ) ) {
                $gtm_id = esc_attr( $gtm_id );
                echo <<<GTMFALLBACK

<!-- Google Tag Manager (noscript) - Fallback placement -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$gtm_id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

GTMFALLBACK;
            }
        }
    }

    /**
     * Helper: Get the dataLayer for custom events (can be used by other plugins/theme)
     * 
     * Usage: DBA_Tracking::push_event('purchase', ['transaction_id' => '12345', 'value' => 29.99]);
     * 
     * @param string $event_name The event name
     * @param array  $event_data Additional event data
     * @return string JavaScript code to push the event
     */
    public static function push_event( $event_name, $event_data = array() ) {
        $event = array_merge(
            array( 'event' => $event_name ),
            $event_data
        );
        
        return sprintf(
            '<script>window.dataLayer = window.dataLayer || []; dataLayer.push(%s);</script>',
            wp_json_encode( $event )
        );
    }

    /**
     * Helper: Track ecommerce purchase event
     * 
     * @param string $transaction_id Order/transaction ID
     * @param float  $value Total value
     * @param string $currency Currency code (default USD)
     * @param array  $items Array of item data
     * @return string JavaScript code
     */
    public static function track_purchase( $transaction_id, $value, $currency = 'USD', $items = array() ) {
        $event_data = array(
            'event'          => 'purchase',
            'transaction_id' => $transaction_id,
            'value'          => $value,
            'currency'       => $currency,
        );

        if ( ! empty( $items ) ) {
            $event_data['items'] = $items;
        }

        return sprintf(
            '<script>window.dataLayer = window.dataLayer || []; dataLayer.push(%s);</script>',
            wp_json_encode( $event_data )
        );
    }

    /**
     * Helper: Track add to cart event
     * 
     * @param string $item_id Product ID
     * @param string $item_name Product name
     * @param float  $price Item price
     * @param int    $quantity Quantity added
     * @return string JavaScript code
     */
    public static function track_add_to_cart( $item_id, $item_name, $price, $quantity = 1 ) {
        $event_data = array(
            'event'    => 'add_to_cart',
            'currency' => 'USD',
            'value'    => $price * $quantity,
            'items'    => array(
                array(
                    'item_id'   => $item_id,
                    'item_name' => $item_name,
                    'price'     => $price,
                    'quantity'  => $quantity,
                ),
            ),
        );

        return sprintf(
            '<script>window.dataLayer = window.dataLayer || []; dataLayer.push(%s);</script>',
            wp_json_encode( $event_data )
        );
    }
}
