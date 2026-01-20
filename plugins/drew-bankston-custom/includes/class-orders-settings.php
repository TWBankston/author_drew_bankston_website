<?php
/**
 * Orders Settings
 * 
 * Settings page for order management including email notifications and discount codes
 */

defined( 'ABSPATH' ) || exit;

class DBC_Orders_Settings {
    
    /**
     * Initialize settings
     */
    public static function init() {
        // Add settings submenu
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_menu' ) );
        
        // Register settings
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        
        // AJAX handlers for discount codes
        add_action( 'wp_ajax_dbc_save_discount_code', array( __CLASS__, 'ajax_save_discount_code' ) );
        add_action( 'wp_ajax_dbc_delete_discount_code', array( __CLASS__, 'ajax_delete_discount_code' ) );
        add_action( 'wp_ajax_dbc_apply_discount', array( __CLASS__, 'ajax_apply_discount' ) );
        add_action( 'wp_ajax_nopriv_dbc_apply_discount', array( __CLASS__, 'ajax_apply_discount' ) );
        add_action( 'wp_ajax_dbc_remove_discount', array( __CLASS__, 'ajax_remove_discount' ) );
        add_action( 'wp_ajax_nopriv_dbc_remove_discount', array( __CLASS__, 'ajax_remove_discount' ) );
        
        // Create default discount code on first run
        add_action( 'admin_init', array( __CLASS__, 'maybe_create_default_codes' ) );
    }
    
    /**
     * Add settings submenu under Orders
     */
    public static function add_settings_menu() {
        add_submenu_page(
            'dbc-orders',
            'Order Settings',
            'Settings',
            'manage_options',
            'dbc-order-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting( 'dbc_order_settings', 'dbc_sale_notification_email', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default'           => 'author@drewbankston.com',
        ) );
        
        register_setting( 'dbc_order_settings', 'dbc_sale_notification_enabled', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );
        
        register_setting( 'dbc_order_settings', 'dbc_discount_codes', array(
            'type'              => 'array',
            'sanitize_callback' => array( __CLASS__, 'sanitize_discount_codes' ),
            'default'           => array(),
        ) );
    }
    
    /**
     * Maybe create default discount codes
     */
    public static function maybe_create_default_codes() {
        $codes = get_option( 'dbc_discount_codes', array() );
        
        // Only create if no codes exist
        if ( empty( $codes ) ) {
            $default_codes = array(
                'BANK$TON' => array(
                    'code'        => 'BANK$TON',
                    'type'        => 'fixed_price',
                    'amount'      => 1.00,
                    'description' => 'Test discount - $1 items',
                    'active'      => true,
                    'usage_limit' => 0, // 0 = unlimited
                    'usage_count' => 0,
                    'expires'     => '', // Empty = never expires
                    'created_at'  => current_time( 'mysql' ),
                ),
            );
            
            update_option( 'dbc_discount_codes', $default_codes );
        }
    }
    
    /**
     * Sanitize discount codes
     */
    public static function sanitize_discount_codes( $codes ) {
        if ( ! is_array( $codes ) ) {
            return array();
        }
        
        $sanitized = array();
        
        foreach ( $codes as $key => $code ) {
            $sanitized_key = strtoupper( sanitize_text_field( $key ) );
            $sanitized[ $sanitized_key ] = array(
                'code'        => strtoupper( sanitize_text_field( $code['code'] ?? '' ) ),
                'type'        => sanitize_text_field( $code['type'] ?? 'percentage' ),
                'amount'      => floatval( $code['amount'] ?? 0 ),
                'description' => sanitize_text_field( $code['description'] ?? '' ),
                'active'      => (bool) ( $code['active'] ?? true ),
                'usage_limit' => intval( $code['usage_limit'] ?? 0 ),
                'usage_count' => intval( $code['usage_count'] ?? 0 ),
                'expires'     => sanitize_text_field( $code['expires'] ?? '' ),
                'created_at'  => sanitize_text_field( $code['created_at'] ?? current_time( 'mysql' ) ),
            );
        }
        
        return $sanitized;
    }
    
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        
        ?>
        <div class="wrap dbc-settings-wrap">
            <h1>Order Settings</h1>
            
            <nav class="nav-tab-wrapper">
                <a href="<?php echo admin_url( 'admin.php?page=dbc-order-settings&tab=general' ); ?>" 
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    General
                </a>
                <a href="<?php echo admin_url( 'admin.php?page=dbc-order-settings&tab=discounts' ); ?>" 
                   class="nav-tab <?php echo $active_tab === 'discounts' ? 'nav-tab-active' : ''; ?>">
                    Discount Codes
                </a>
            </nav>
            
            <div class="tab-content">
                <?php
                if ( $active_tab === 'general' ) {
                    self::render_general_tab();
                } elseif ( $active_tab === 'discounts' ) {
                    self::render_discounts_tab();
                }
                ?>
            </div>
        </div>
        
        <style>
            .dbc-settings-wrap {
                max-width: 900px;
            }
            
            .dbc-settings-wrap .nav-tab-wrapper {
                margin-bottom: 20px;
            }
            
            .dbc-settings-wrap .tab-content {
                background: #fff;
                padding: 25px;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }
            
            .dbc-settings-wrap .form-table th {
                width: 200px;
                padding: 20px 10px 20px 0;
            }
            
            .dbc-settings-wrap .form-table td {
                padding: 15px 10px;
            }
            
            .dbc-settings-wrap input[type="email"],
            .dbc-settings-wrap input[type="text"],
            .dbc-settings-wrap input[type="number"],
            .dbc-settings-wrap select {
                width: 100%;
                max-width: 400px;
            }
            
            .dbc-settings-wrap .description {
                color: #666;
                font-style: italic;
                margin-top: 5px;
            }
            
            /* Discount Codes Table */
            .discount-codes-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            
            .discount-codes-table th,
            .discount-codes-table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }
            
            .discount-codes-table th {
                background: #f5f5f5;
                font-weight: 600;
            }
            
            .discount-codes-table .code-cell {
                font-family: monospace;
                font-weight: 600;
                font-size: 14px;
                background: #f0f0f0;
                padding: 4px 8px;
                border-radius: 4px;
                display: inline-block;
            }
            
            .discount-codes-table .status-active {
                color: #46b450;
            }
            
            .discount-codes-table .status-inactive {
                color: #dc3232;
            }
            
            .discount-codes-table .actions button {
                margin-right: 5px;
            }
            
            /* Add Code Form */
            .add-code-form {
                background: #f9f9f9;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 25px;
            }
            
            .add-code-form h3 {
                margin-top: 0;
                margin-bottom: 20px;
            }
            
            .add-code-form .form-row {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-bottom: 15px;
            }
            
            .add-code-form .form-field {
                flex: 1;
                min-width: 150px;
            }
            
            .add-code-form .form-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .add-code-form .form-field input,
            .add-code-form .form-field select {
                width: 100%;
            }
            
            .add-code-form .form-field--wide {
                flex: 2;
            }
            
            .add-code-form .form-actions {
                margin-top: 15px;
            }
            
            /* Delete confirm */
            .delete-confirm {
                background: #fff3cd;
                padding: 10px;
                border-radius: 4px;
                margin-top: 5px;
            }
        </style>
        <?php
    }
    
    /**
     * Render general settings tab
     */
    private static function render_general_tab() {
        $email = get_option( 'dbc_sale_notification_email', 'author@drewbankston.com' );
        $enabled = get_option( 'dbc_sale_notification_enabled', true );
        
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'dbc_order_settings' ); ?>
            
            <h2>Email Notifications</h2>
            <p class="description">Configure email notifications for new orders.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="dbc_sale_notification_enabled">Enable Notifications</label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="dbc_sale_notification_enabled" id="dbc_sale_notification_enabled" value="1" <?php checked( $enabled ); ?>>
                            Send email notification when a new order is placed
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dbc_sale_notification_email">Notification Email</label>
                    </th>
                    <td>
                        <input type="email" name="dbc_sale_notification_email" id="dbc_sale_notification_email" 
                               value="<?php echo esc_attr( $email ); ?>" class="regular-text">
                        <p class="description">Email address to receive sale notifications. Leave blank to disable.</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button( 'Save Settings' ); ?>
        </form>
        <?php
    }
    
    /**
     * Render discounts tab
     */
    private static function render_discounts_tab() {
        $codes = get_option( 'dbc_discount_codes', array() );
        
        ?>
        <h2>Discount Codes</h2>
        <p class="description">Manage discount codes that customers can apply at checkout.</p>
        
        <!-- Add Code Form -->
        <div class="add-code-form">
            <h3>Add New Discount Code</h3>
            <form id="add-discount-form">
                <?php wp_nonce_field( 'dbc_discount_nonce', 'discount_nonce' ); ?>
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="new_code">Code</label>
                        <input type="text" id="new_code" name="code" placeholder="e.g., SUMMER20" required style="text-transform: uppercase;">
                    </div>
                    <div class="form-field">
                        <label for="new_type">Discount Type</label>
                        <select id="new_type" name="type">
                            <option value="percentage">Percentage Off</option>
                            <option value="fixed_amount">Fixed Amount Off</option>
                            <option value="fixed_price">Fixed Price (Set Item Price)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="new_amount">Amount</label>
                        <input type="number" id="new_amount" name="amount" step="0.01" min="0" placeholder="10" required>
                        <span class="amount-suffix">% / $</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field form-field--wide">
                        <label for="new_description">Description (Internal)</label>
                        <input type="text" id="new_description" name="description" placeholder="e.g., Summer sale - 20% off all books">
                    </div>
                    <div class="form-field">
                        <label for="new_usage_limit">Usage Limit</label>
                        <input type="number" id="new_usage_limit" name="usage_limit" min="0" value="0" placeholder="0 = unlimited">
                    </div>
                    <div class="form-field">
                        <label for="new_expires">Expires</label>
                        <input type="date" id="new_expires" name="expires">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">Add Discount Code</button>
                </div>
            </form>
        </div>
        
        <!-- Existing Codes Table -->
        <h3>Existing Codes</h3>
        <table class="discount-codes-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Usage</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="discount-codes-body">
                <?php if ( empty( $codes ) ) : ?>
                <tr class="no-codes">
                    <td colspan="8" style="text-align: center; padding: 30px; color: #666;">
                        No discount codes yet. Add one above!
                    </td>
                </tr>
                <?php else : ?>
                <?php foreach ( $codes as $code_key => $code ) : 
                    $type_label = self::get_type_label( $code['type'] );
                    $amount_display = self::format_amount( $code['type'], $code['amount'] );
                    $usage_display = $code['usage_limit'] > 0 
                        ? $code['usage_count'] . ' / ' . $code['usage_limit']
                        : $code['usage_count'] . ' (unlimited)';
                    $expires_display = ! empty( $code['expires'] ) 
                        ? date( 'M j, Y', strtotime( $code['expires'] ) )
                        : 'Never';
                    $is_expired = ! empty( $code['expires'] ) && strtotime( $code['expires'] ) < time();
                ?>
                <tr data-code="<?php echo esc_attr( $code_key ); ?>">
                    <td><span class="code-cell"><?php echo esc_html( $code['code'] ); ?></span></td>
                    <td><?php echo esc_html( $type_label ); ?></td>
                    <td><?php echo esc_html( $amount_display ); ?></td>
                    <td><?php echo esc_html( $code['description'] ); ?></td>
                    <td><?php echo esc_html( $usage_display ); ?></td>
                    <td><?php echo esc_html( $expires_display ); ?><?php if ( $is_expired ) echo ' <span style="color:red;">(expired)</span>'; ?></td>
                    <td>
                        <?php if ( $code['active'] && ! $is_expired ) : ?>
                        <span class="status-active">● Active</span>
                        <?php else : ?>
                        <span class="status-inactive">● Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <button type="button" class="button button-small toggle-code" 
                                data-code="<?php echo esc_attr( $code_key ); ?>"
                                data-active="<?php echo $code['active'] ? '1' : '0'; ?>">
                            <?php echo $code['active'] ? 'Deactivate' : 'Activate'; ?>
                        </button>
                        <button type="button" class="button button-small button-link-delete delete-code" 
                                data-code="<?php echo esc_attr( $code_key ); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <script>
        jQuery(document).ready(function($) {
            var nonce = $('#discount_nonce').val();
            
            // Add discount code
            $('#add-discount-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                var originalText = $btn.text();
                
                $btn.prop('disabled', true).text('Adding...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dbc_save_discount_code',
                        nonce: nonce,
                        code: $('#new_code').val().toUpperCase(),
                        type: $('#new_type').val(),
                        amount: $('#new_amount').val(),
                        description: $('#new_description').val(),
                        usage_limit: $('#new_usage_limit').val(),
                        expires: $('#new_expires').val(),
                        active: true
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to add discount code');
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });
            
            // Toggle code active/inactive
            $(document).on('click', '.toggle-code', function() {
                var $btn = $(this);
                var code = $btn.data('code');
                var currentActive = $btn.data('active') === 1;
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dbc_save_discount_code',
                        nonce: nonce,
                        code: code,
                        toggle_active: true,
                        active: !currentActive
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to update code');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
            
            // Delete code
            $(document).on('click', '.delete-code', function() {
                var $btn = $(this);
                var code = $btn.data('code');
                
                if (!confirm('Are you sure you want to delete the discount code "' + code + '"?')) {
                    return;
                }
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dbc_delete_discount_code',
                        nonce: nonce,
                        code: code
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to delete code');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get type label
     */
    private static function get_type_label( $type ) {
        $labels = array(
            'percentage'   => 'Percentage Off',
            'fixed_amount' => 'Fixed Amount Off',
            'fixed_price'  => 'Fixed Price',
        );
        
        return $labels[ $type ] ?? $type;
    }
    
    /**
     * Format amount for display
     */
    private static function format_amount( $type, $amount ) {
        switch ( $type ) {
            case 'percentage':
                return $amount . '%';
            case 'fixed_amount':
                return '-$' . number_format( $amount, 2 );
            case 'fixed_price':
                return '$' . number_format( $amount, 2 ) . '/item';
            default:
                return $amount;
        }
    }
    
    /**
     * AJAX: Save discount code
     */
    public static function ajax_save_discount_code() {
        check_ajax_referer( 'dbc_discount_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $codes = get_option( 'dbc_discount_codes', array() );
        $code_key = strtoupper( sanitize_text_field( $_POST['code'] ?? '' ) );
        
        if ( empty( $code_key ) ) {
            wp_send_json_error( array( 'message' => 'Code is required' ) );
        }
        
        // Toggle active status only
        if ( isset( $_POST['toggle_active'] ) ) {
            if ( isset( $codes[ $code_key ] ) ) {
                $codes[ $code_key ]['active'] = (bool) $_POST['active'];
                update_option( 'dbc_discount_codes', $codes );
                wp_send_json_success( array( 'message' => 'Code updated' ) );
            } else {
                wp_send_json_error( array( 'message' => 'Code not found' ) );
            }
            return;
        }
        
        // Save new or update existing code
        $codes[ $code_key ] = array(
            'code'        => $code_key,
            'type'        => sanitize_text_field( $_POST['type'] ?? 'percentage' ),
            'amount'      => floatval( $_POST['amount'] ?? 0 ),
            'description' => sanitize_text_field( $_POST['description'] ?? '' ),
            'active'      => (bool) ( $_POST['active'] ?? true ),
            'usage_limit' => intval( $_POST['usage_limit'] ?? 0 ),
            'usage_count' => $codes[ $code_key ]['usage_count'] ?? 0,
            'expires'     => sanitize_text_field( $_POST['expires'] ?? '' ),
            'created_at'  => $codes[ $code_key ]['created_at'] ?? current_time( 'mysql' ),
        );
        
        update_option( 'dbc_discount_codes', $codes );
        
        wp_send_json_success( array( 'message' => 'Code saved successfully' ) );
    }
    
    /**
     * AJAX: Delete discount code
     */
    public static function ajax_delete_discount_code() {
        check_ajax_referer( 'dbc_discount_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $code_key = strtoupper( sanitize_text_field( $_POST['code'] ?? '' ) );
        
        if ( empty( $code_key ) ) {
            wp_send_json_error( array( 'message' => 'Code is required' ) );
        }
        
        $codes = get_option( 'dbc_discount_codes', array() );
        
        if ( ! isset( $codes[ $code_key ] ) ) {
            wp_send_json_error( array( 'message' => 'Code not found' ) );
        }
        
        unset( $codes[ $code_key ] );
        update_option( 'dbc_discount_codes', $codes );
        
        wp_send_json_success( array( 'message' => 'Code deleted successfully' ) );
    }
    
    /**
     * AJAX: Apply discount code (for checkout)
     */
    public static function ajax_apply_discount() {
        check_ajax_referer( 'dbc_cart_nonce', 'nonce' );
        
        $code_input = strtoupper( sanitize_text_field( $_POST['code'] ?? '' ) );
        
        if ( empty( $code_input ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a discount code' ) );
        }
        
        $codes = get_option( 'dbc_discount_codes', array() );
        
        if ( ! isset( $codes[ $code_input ] ) ) {
            wp_send_json_error( array( 'message' => 'Invalid discount code' ) );
        }
        
        $code = $codes[ $code_input ];
        
        // Check if active
        if ( ! $code['active'] ) {
            wp_send_json_error( array( 'message' => 'This discount code is no longer active' ) );
        }
        
        // Check if expired
        if ( ! empty( $code['expires'] ) && strtotime( $code['expires'] ) < time() ) {
            wp_send_json_error( array( 'message' => 'This discount code has expired' ) );
        }
        
        // Check usage limit
        if ( $code['usage_limit'] > 0 && $code['usage_count'] >= $code['usage_limit'] ) {
            wp_send_json_error( array( 'message' => 'This discount code has reached its usage limit' ) );
        }
        
        // Store in session
        DBC_Cart::start_session();
        $_SESSION['dbc_discount_code'] = $code_input;
        
        // Calculate discount
        $cart = DBC_Cart::get_cart();
        $subtotal = DBC_Cart::get_subtotal();
        $discount_amount = self::calculate_discount( $code, $cart, $subtotal );
        
        $_SESSION['dbc_discount_amount'] = $discount_amount;
        
        wp_send_json_success( array(
            'message'         => 'Discount applied!',
            'code'            => $code_input,
            'discount_amount' => $discount_amount,
            'discount_display' => '-$' . number_format( $discount_amount, 2 ),
            'new_total'       => $subtotal - $discount_amount + DBC_Cart::get_shipping_cost(),
        ) );
    }
    
    /**
     * AJAX: Remove discount code
     */
    public static function ajax_remove_discount() {
        DBC_Cart::start_session();
        
        unset( $_SESSION['dbc_discount_code'] );
        unset( $_SESSION['dbc_discount_amount'] );
        
        wp_send_json_success( array(
            'message' => 'Discount removed',
            'total'   => DBC_Cart::get_total(),
        ) );
    }
    
    /**
     * Calculate discount amount
     */
    public static function calculate_discount( $code, $cart, $subtotal ) {
        switch ( $code['type'] ) {
            case 'percentage':
                return $subtotal * ( $code['amount'] / 100 );
                
            case 'fixed_amount':
                return min( $code['amount'], $subtotal ); // Don't go negative
                
            case 'fixed_price':
                // Set each item to fixed price
                $item_count = 0;
                foreach ( $cart as $item ) {
                    $item_count += $item['quantity'];
                }
                $fixed_total = $code['amount'] * $item_count;
                return max( 0, $subtotal - $fixed_total );
                
            default:
                return 0;
        }
    }
    
    /**
     * Get current discount code from session
     */
    public static function get_applied_discount() {
        DBC_Cart::start_session();
        
        if ( empty( $_SESSION['dbc_discount_code'] ) ) {
            return null;
        }
        
        $code_key = $_SESSION['dbc_discount_code'];
        $codes = get_option( 'dbc_discount_codes', array() );
        
        if ( ! isset( $codes[ $code_key ] ) ) {
            unset( $_SESSION['dbc_discount_code'] );
            unset( $_SESSION['dbc_discount_amount'] );
            return null;
        }
        
        return array(
            'code'   => $code_key,
            'amount' => $_SESSION['dbc_discount_amount'] ?? 0,
            'data'   => $codes[ $code_key ],
        );
    }
    
    /**
     * Increment usage count after successful order
     */
    public static function increment_usage( $code_key ) {
        $codes = get_option( 'dbc_discount_codes', array() );
        
        if ( isset( $codes[ $code_key ] ) ) {
            $codes[ $code_key ]['usage_count']++;
            update_option( 'dbc_discount_codes', $codes );
        }
    }
    
    /**
     * Clear discount from session
     */
    public static function clear_discount() {
        DBC_Cart::start_session();
        unset( $_SESSION['dbc_discount_code'] );
        unset( $_SESSION['dbc_discount_amount'] );
    }
    
    /**
     * Get sale notification email
     */
    public static function get_notification_email() {
        $enabled = get_option( 'dbc_sale_notification_enabled', true );
        if ( ! $enabled ) {
            return '';
        }
        return get_option( 'dbc_sale_notification_email', 'author@drewbankston.com' );
    }
}
