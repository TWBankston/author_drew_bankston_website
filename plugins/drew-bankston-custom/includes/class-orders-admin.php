<?php
/**
 * Orders Admin Panel
 * 
 * Provides admin interface for tracking and fulfilling orders
 */

defined( 'ABSPATH' ) || exit;

class DBC_Orders_Admin {
    
    /**
     * Initialize the orders admin
     */
    public static function init() {
        // Add admin menu
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        
        // Admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_dbc_update_order_status', array( __CLASS__, 'ajax_update_order_status' ) );
        add_action( 'wp_ajax_dbc_get_order_details', array( __CLASS__, 'ajax_get_order_details' ) );
        add_action( 'wp_ajax_dbc_export_orders', array( __CLASS__, 'ajax_export_orders' ) );
        
        // Ensure fulfillment columns exist
        add_action( 'admin_init', array( __CLASS__, 'ensure_table_columns' ) );
    }
    
    /**
     * Ensure required table columns exist
     */
    public static function ensure_table_columns() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
            return;
        }
        
        // Check for fulfillment_status column
        $column_exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE 'fulfillment_status'" );
        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN fulfillment_status varchar(50) DEFAULT 'pending' AFTER status" );
        }
        
        // Check for tracking_number column
        $column_exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE 'tracking_number'" );
        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN tracking_number varchar(255) DEFAULT '' AFTER signature_message" );
        }
        
        // Check for tracking_carrier column
        $column_exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE 'tracking_carrier'" );
        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN tracking_carrier varchar(100) DEFAULT '' AFTER tracking_number" );
        }
        
        // Check for notes column
        $column_exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE 'admin_notes'" );
        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN admin_notes text AFTER tracking_carrier" );
        }
        
        // Check for shipped_at column
        $column_exists = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE 'shipped_at'" );
        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN shipped_at datetime DEFAULT NULL AFTER created_at" );
        }
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Get pending orders count for badge
        $pending_count = self::get_pending_orders_count();
        $menu_title = 'Orders';
        if ( $pending_count > 0 ) {
            $menu_title .= ' <span class="awaiting-mod">' . $pending_count . '</span>';
        }
        
        add_menu_page(
            'Orders',
            $menu_title,
            'manage_options',
            'dbc-orders',
            array( __CLASS__, 'render_orders_page' ),
            'dashicons-cart',
            26 // Position after Comments
        );
    }
    
    /**
     * Get pending orders count
     */
    public static function get_pending_orders_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
            return 0;
        }
        
        return (int) $wpdb->get_var( 
            "SELECT COUNT(*) FROM $table_name WHERE fulfillment_status IN ('pending', 'processing')" 
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'toplevel_page_dbc-orders' ) {
            return;
        }
        
        wp_enqueue_style( 
            'dbc-orders-admin', 
            DBC_URL . 'assets/css/orders-admin.css', 
            array(), 
            DBC_VERSION 
        );
        
        wp_enqueue_script( 
            'dbc-orders-admin', 
            DBC_URL . 'assets/js/orders-admin.js', 
            array( 'jquery' ), 
            DBC_VERSION, 
            true 
        );
        
        wp_localize_script( 'dbc-orders-admin', 'dbcOrders', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'dbc_orders_nonce' ),
        ) );
    }
    
    /**
     * Render orders page
     */
    public static function render_orders_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
            echo '<div class="wrap"><h1>Orders</h1><p>Orders table not found. Please reactivate the plugin.</p></div>';
            return;
        }
        
        // Get filters
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'created_at';
        $order = isset( $_GET['order'] ) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        // Pagination
        $per_page = 20;
        $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $offset = ( $current_page - 1 ) * $per_page;
        
        // Build query
        $where = array( '1=1' );
        $params = array();
        
        if ( $status_filter ) {
            $where[] = 'fulfillment_status = %s';
            $params[] = $status_filter;
        }
        
        if ( $search ) {
            $where[] = '(customer_name LIKE %s OR customer_email LIKE %s OR id = %d)';
            $params[] = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = intval( $search );
        }
        
        $where_clause = implode( ' AND ', $where );
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
        if ( ! empty( $params ) ) {
            $total_items = $wpdb->get_var( $wpdb->prepare( $count_query, $params ) );
        } else {
            $total_items = $wpdb->get_var( $count_query );
        }
        
        $total_pages = ceil( $total_items / $per_page );
        
        // Get orders
        $allowed_orderby = array( 'id', 'created_at', 'total_amount', 'fulfillment_status', 'customer_name' );
        $orderby = in_array( $orderby, $allowed_orderby ) ? $orderby : 'created_at';
        
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        
        $orders = $wpdb->get_results( $wpdb->prepare( $query, $params ) );
        
        // Get status counts
        $status_counts = $wpdb->get_results( 
            "SELECT fulfillment_status, COUNT(*) as count FROM $table_name GROUP BY fulfillment_status",
            OBJECT_K
        );
        
        ?>
        <div class="wrap dbc-orders-wrap">
            <h1 class="wp-heading-inline">Orders</h1>
            <a href="<?php echo admin_url( 'admin-ajax.php?action=dbc_export_orders&nonce=' . wp_create_nonce( 'dbc_orders_nonce' ) ); ?>" class="page-title-action">Export CSV</a>
            <hr class="wp-header-end">
            
            <!-- Status Tabs -->
            <ul class="subsubsub">
                <li>
                    <a href="<?php echo admin_url( 'admin.php?page=dbc-orders' ); ?>" <?php echo $status_filter === '' ? 'class="current"' : ''; ?>>
                        All <span class="count">(<?php echo $total_items; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo admin_url( 'admin.php?page=dbc-orders&status=pending' ); ?>" <?php echo $status_filter === 'pending' ? 'class="current"' : ''; ?>>
                        Pending <span class="count">(<?php echo isset( $status_counts['pending'] ) ? $status_counts['pending']->count : 0; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo admin_url( 'admin.php?page=dbc-orders&status=processing' ); ?>" <?php echo $status_filter === 'processing' ? 'class="current"' : ''; ?>>
                        Processing <span class="count">(<?php echo isset( $status_counts['processing'] ) ? $status_counts['processing']->count : 0; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo admin_url( 'admin.php?page=dbc-orders&status=shipped' ); ?>" <?php echo $status_filter === 'shipped' ? 'class="current"' : ''; ?>>
                        Shipped <span class="count">(<?php echo isset( $status_counts['shipped'] ) ? $status_counts['shipped']->count : 0; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo admin_url( 'admin.php?page=dbc-orders&status=completed' ); ?>" <?php echo $status_filter === 'completed' ? 'class="current"' : ''; ?>>
                        Completed <span class="count">(<?php echo isset( $status_counts['completed'] ) ? $status_counts['completed']->count : 0; ?>)</span>
                    </a>
                </li>
            </ul>
            
            <!-- Search -->
            <form method="get" class="search-form">
                <input type="hidden" name="page" value="dbc-orders">
                <?php if ( $status_filter ) : ?>
                <input type="hidden" name="status" value="<?php echo esc_attr( $status_filter ); ?>">
                <?php endif; ?>
                <p class="search-box">
                    <label class="screen-reader-text" for="order-search-input">Search Orders:</label>
                    <input type="search" id="order-search-input" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search by name, email, or order #">
                    <input type="submit" id="search-submit" class="button" value="Search Orders">
                </p>
            </form>
            
            <!-- Orders Table -->
            <table class="wp-list-table widefat fixed striped dbc-orders-table">
                <thead>
                    <tr>
                        <th scope="col" class="column-order sortable <?php echo $orderby === 'id' ? 'sorted' : ''; ?> <?php echo strtolower( $order ); ?>">
                            <a href="<?php echo self::get_sort_url( 'id', $orderby, $order ); ?>">
                                <span>Order</span>
                                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                            </a>
                        </th>
                        <th scope="col" class="column-date sortable <?php echo $orderby === 'created_at' ? 'sorted' : ''; ?> <?php echo strtolower( $order ); ?>">
                            <a href="<?php echo self::get_sort_url( 'created_at', $orderby, $order ); ?>">
                                <span>Date</span>
                                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                            </a>
                        </th>
                        <th scope="col" class="column-status sortable <?php echo $orderby === 'fulfillment_status' ? 'sorted' : ''; ?> <?php echo strtolower( $order ); ?>">
                            <a href="<?php echo self::get_sort_url( 'fulfillment_status', $orderby, $order ); ?>">
                                <span>Status</span>
                                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                            </a>
                        </th>
                        <th scope="col" class="column-customer sortable <?php echo $orderby === 'customer_name' ? 'sorted' : ''; ?> <?php echo strtolower( $order ); ?>">
                            <a href="<?php echo self::get_sort_url( 'customer_name', $orderby, $order ); ?>">
                                <span>Customer</span>
                                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                            </a>
                        </th>
                        <th scope="col" class="column-items">Items</th>
                        <th scope="col" class="column-total sortable <?php echo $orderby === 'total_amount' ? 'sorted' : ''; ?> <?php echo strtolower( $order ); ?>">
                            <a href="<?php echo self::get_sort_url( 'total_amount', $orderby, $order ); ?>">
                                <span>Total</span>
                                <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                            </a>
                        </th>
                        <th scope="col" class="column-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $orders ) ) : ?>
                    <tr>
                        <td colspan="7" class="no-orders">No orders found.</td>
                    </tr>
                    <?php else : ?>
                    <?php foreach ( $orders as $order ) : 
                        $items = json_decode( $order->order_items, true );
                        $address = json_decode( $order->shipping_address, true );
                        $item_count = 0;
                        $item_names = array();
                        if ( is_array( $items ) ) {
                            foreach ( $items as $item ) {
                                $item_count += isset( $item['quantity'] ) ? $item['quantity'] : 1;
                                $item_names[] = isset( $item['name'] ) ? $item['name'] : 'Unknown Item';
                            }
                        }
                    ?>
                    <tr data-order-id="<?php echo esc_attr( $order->id ); ?>">
                        <td class="column-order">
                            <strong>#<?php echo esc_html( $order->id ); ?></strong>
                            <?php if ( $order->signature_request ) : ?>
                            <span class="order-badge order-badge--signature" title="Personalized Signature Requested">✍️</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-date">
                            <?php echo date( 'M j, Y', strtotime( $order->created_at ) ); ?>
                            <br><small><?php echo date( 'g:i a', strtotime( $order->created_at ) ); ?></small>
                        </td>
                        <td class="column-status">
                            <span class="order-status order-status--<?php echo esc_attr( $order->fulfillment_status ?: 'pending' ); ?>">
                                <?php echo esc_html( ucfirst( $order->fulfillment_status ?: 'pending' ) ); ?>
                            </span>
                        </td>
                        <td class="column-customer">
                            <strong><?php echo esc_html( $order->customer_name ); ?></strong>
                            <br><a href="mailto:<?php echo esc_attr( $order->customer_email ); ?>"><?php echo esc_html( $order->customer_email ); ?></a>
                            <?php if ( ! empty( $order->customer_phone ) ) : ?>
                            <br><small><?php echo esc_html( $order->customer_phone ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="column-items">
                            <span title="<?php echo esc_attr( implode( ', ', $item_names ) ); ?>">
                                <?php echo $item_count; ?> item<?php echo $item_count !== 1 ? 's' : ''; ?>
                            </span>
                            <br><small><?php echo esc_html( implode( ', ', array_slice( $item_names, 0, 2 ) ) ); ?><?php echo count( $item_names ) > 2 ? '...' : ''; ?></small>
                        </td>
                        <td class="column-total">
                            <strong>$<?php echo number_format( $order->total_amount, 2 ); ?></strong>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small view-order" data-order-id="<?php echo esc_attr( $order->id ); ?>">
                                View
                            </button>
                            <select class="order-status-select" data-order-id="<?php echo esc_attr( $order->id ); ?>">
                                <option value="pending" <?php selected( $order->fulfillment_status, 'pending' ); ?>>Pending</option>
                                <option value="processing" <?php selected( $order->fulfillment_status, 'processing' ); ?>>Processing</option>
                                <option value="shipped" <?php selected( $order->fulfillment_status, 'shipped' ); ?>>Shipped</option>
                                <option value="completed" <?php selected( $order->fulfillment_status, 'completed' ); ?>>Completed</option>
                                <option value="cancelled" <?php selected( $order->fulfillment_status, 'cancelled' ); ?>>Cancelled</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_items; ?> items</span>
                    <span class="pagination-links">
                        <?php if ( $current_page > 1 ) : ?>
                        <a class="prev-page button" href="<?php echo add_query_arg( 'paged', $current_page - 1 ); ?>">‹</a>
                        <?php else : ?>
                        <span class="tablenav-pages-navspan button disabled">‹</span>
                        <?php endif; ?>
                        
                        <span class="paging-input">
                            <span class="tablenav-paging-text"><?php echo $current_page; ?> of <span class="total-pages"><?php echo $total_pages; ?></span></span>
                        </span>
                        
                        <?php if ( $current_page < $total_pages ) : ?>
                        <a class="next-page button" href="<?php echo add_query_arg( 'paged', $current_page + 1 ); ?>">›</a>
                        <?php else : ?>
                        <span class="tablenav-pages-navspan button disabled">›</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Order Details Modal -->
        <div id="order-modal" class="dbc-modal" style="display: none;">
            <div class="dbc-modal__backdrop"></div>
            <div class="dbc-modal__content">
                <button type="button" class="dbc-modal__close">&times;</button>
                <div id="order-modal-body">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get sort URL
     */
    private static function get_sort_url( $column, $current_orderby, $current_order ) {
        $new_order = ( $column === $current_orderby && $current_order === 'DESC' ) ? 'asc' : 'desc';
        return add_query_arg( array(
            'orderby' => $column,
            'order'   => $new_order,
        ) );
    }
    
    /**
     * AJAX: Update order status
     */
    public static function ajax_update_order_status() {
        check_ajax_referer( 'dbc_orders_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
        $tracking_number = isset( $_POST['tracking_number'] ) ? sanitize_text_field( $_POST['tracking_number'] ) : '';
        $tracking_carrier = isset( $_POST['tracking_carrier'] ) ? sanitize_text_field( $_POST['tracking_carrier'] ) : '';
        $admin_notes = isset( $_POST['admin_notes'] ) ? sanitize_textarea_field( $_POST['admin_notes'] ) : '';
        
        if ( ! $order_id || ! $status ) {
            wp_send_json_error( array( 'message' => 'Invalid data' ) );
        }
        
        $allowed_statuses = array( 'pending', 'processing', 'shipped', 'completed', 'cancelled' );
        if ( ! in_array( $status, $allowed_statuses ) ) {
            wp_send_json_error( array( 'message' => 'Invalid status' ) );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        // Get current order status for comparison
        $current_order = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $table_name WHERE id = %d", 
            $order_id 
        ) );
        
        if ( ! $current_order ) {
            wp_send_json_error( array( 'message' => 'Order not found' ) );
        }
        
        $update_data = array(
            'fulfillment_status' => $status,
        );
        $format = array( '%s' );
        
        // Add tracking info if provided
        if ( $tracking_number ) {
            $update_data['tracking_number'] = $tracking_number;
            $format[] = '%s';
        }
        
        if ( $tracking_carrier ) {
            $update_data['tracking_carrier'] = $tracking_carrier;
            $format[] = '%s';
        }
        
        if ( $admin_notes ) {
            $update_data['admin_notes'] = $admin_notes;
            $format[] = '%s';
        }
        
        // Set shipped_at timestamp if status changed to shipped
        if ( $status === 'shipped' && $current_order->fulfillment_status !== 'shipped' ) {
            $update_data['shipped_at'] = current_time( 'mysql' );
            $format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array( 'id' => $order_id ),
            $format,
            array( '%d' )
        );
        
        if ( $result !== false ) {
            // Send shipping notification email if status changed to shipped
            if ( $status === 'shipped' && $current_order->fulfillment_status !== 'shipped' ) {
                self::send_shipping_notification( $order_id, $tracking_number, $tracking_carrier );
            }
            
            wp_send_json_success( array( 
                'message' => 'Order updated successfully',
                'status'  => $status,
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to update order' ) );
        }
    }
    
    /**
     * AJAX: Get order details
     */
    public static function ajax_get_order_details() {
        check_ajax_referer( 'dbc_orders_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
        
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => 'Invalid order ID' ) );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        $order = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $table_name WHERE id = %d", 
            $order_id 
        ) );
        
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => 'Order not found' ) );
        }
        
        $items = json_decode( $order->order_items, true );
        $address = json_decode( $order->shipping_address, true );
        
        ob_start();
        ?>
        <div class="order-details">
            <div class="order-details__header">
                <h2>Order #<?php echo esc_html( $order->id ); ?></h2>
                <span class="order-status order-status--<?php echo esc_attr( $order->fulfillment_status ?: 'pending' ); ?>">
                    <?php echo esc_html( ucfirst( $order->fulfillment_status ?: 'pending' ) ); ?>
                </span>
            </div>
            
            <div class="order-details__meta">
                <p><strong>Date:</strong> <?php echo date( 'F j, Y \a\t g:i a', strtotime( $order->created_at ) ); ?></p>
                <p><strong>Payment ID:</strong> <code><?php echo esc_html( $order->payment_id ); ?></code></p>
                <p><strong>Payment Status:</strong> <?php echo esc_html( $order->status ); ?></p>
            </div>
            
            <div class="order-details__grid">
                <div class="order-details__section">
                    <h3>Customer Information</h3>
                    <p><strong><?php echo esc_html( $order->customer_name ); ?></strong></p>
                    <p><a href="mailto:<?php echo esc_attr( $order->customer_email ); ?>"><?php echo esc_html( $order->customer_email ); ?></a></p>
                    <?php if ( ! empty( $order->customer_phone ) ) : ?>
                    <p><?php echo esc_html( $order->customer_phone ); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ( $address && ! empty( $address['address_1'] ) ) : ?>
                <div class="order-details__section">
                    <h3>Shipping Address</h3>
                    <p><?php echo esc_html( $address['address_1'] ); ?></p>
                    <?php if ( ! empty( $address['address_2'] ) ) : ?>
                    <p><?php echo esc_html( $address['address_2'] ); ?></p>
                    <?php endif; ?>
                    <p><?php echo esc_html( $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'] ); ?></p>
                    <p><?php echo esc_html( $address['country'] ); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ( $order->signature_request ) : ?>
            <div class="order-details__section order-details__section--signature">
                <h3>✍️ Personalized Signature Requested</h3>
                <?php if ( ! empty( $order->signature_message ) ) : ?>
                <blockquote><?php echo esc_html( $order->signature_message ); ?></blockquote>
                <?php else : ?>
                <p><em>No specific message provided</em></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="order-details__section">
                <h3>Order Items</h3>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( is_array( $items ) ) : ?>
                        <?php foreach ( $items as $item ) : ?>
                        <tr>
                            <td><?php echo esc_html( $item['name'] ?? 'Unknown Item' ); ?></td>
                            <td><?php echo esc_html( $item['quantity'] ?? 1 ); ?></td>
                            <td>$<?php echo number_format( floatval( $item['price'] ?? 0 ), 2 ); ?></td>
                            <td>$<?php echo number_format( floatval( $item['price'] ?? 0 ) * intval( $item['quantity'] ?? 1 ), 2 ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="order-total">
                            <th colspan="3">Total</th>
                            <td><strong>$<?php echo number_format( $order->total_amount, 2 ); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="order-details__section">
                <h3>Update Order</h3>
                <form id="update-order-form" class="order-update-form">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr( $order->id ); ?>">
                    
                    <div class="form-row">
                        <label for="modal-status">Status</label>
                        <select name="status" id="modal-status">
                            <option value="pending" <?php selected( $order->fulfillment_status, 'pending' ); ?>>Pending</option>
                            <option value="processing" <?php selected( $order->fulfillment_status, 'processing' ); ?>>Processing</option>
                            <option value="shipped" <?php selected( $order->fulfillment_status, 'shipped' ); ?>>Shipped</option>
                            <option value="completed" <?php selected( $order->fulfillment_status, 'completed' ); ?>>Completed</option>
                            <option value="cancelled" <?php selected( $order->fulfillment_status, 'cancelled' ); ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-row shipping-fields" style="<?php echo $order->fulfillment_status === 'shipped' || $order->fulfillment_status === 'completed' ? '' : 'display:none;'; ?>">
                        <label for="tracking-carrier">Carrier</label>
                        <select name="tracking_carrier" id="tracking-carrier">
                            <option value="">Select Carrier</option>
                            <option value="usps" <?php selected( $order->tracking_carrier ?? '', 'usps' ); ?>>USPS</option>
                            <option value="ups" <?php selected( $order->tracking_carrier ?? '', 'ups' ); ?>>UPS</option>
                            <option value="fedex" <?php selected( $order->tracking_carrier ?? '', 'fedex' ); ?>>FedEx</option>
                            <option value="other" <?php selected( $order->tracking_carrier ?? '', 'other' ); ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-row shipping-fields" style="<?php echo $order->fulfillment_status === 'shipped' || $order->fulfillment_status === 'completed' ? '' : 'display:none;'; ?>">
                        <label for="tracking-number">Tracking Number</label>
                        <input type="text" name="tracking_number" id="tracking-number" value="<?php echo esc_attr( $order->tracking_number ?? '' ); ?>" placeholder="Enter tracking number">
                    </div>
                    
                    <div class="form-row">
                        <label for="admin-notes">Admin Notes</label>
                        <textarea name="admin_notes" id="admin-notes" rows="3" placeholder="Internal notes (not visible to customer)"><?php echo esc_textarea( $order->admin_notes ?? '' ); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary">Update Order</button>
                        <a href="mailto:<?php echo esc_attr( $order->customer_email ); ?>?subject=Order%20%23<?php echo $order->id; ?>%20Update" class="button">Email Customer</a>
                    </div>
                </form>
            </div>
            
            <?php if ( ! empty( $order->shipped_at ) ) : ?>
            <div class="order-details__footer">
                <p><small>Shipped: <?php echo date( 'F j, Y \a\t g:i a', strtotime( $order->shipped_at ) ); ?></small></p>
            </div>
            <?php endif; ?>
        </div>
        <?php
        
        $html = ob_get_clean();
        
        wp_send_json_success( array( 'html' => $html ) );
    }
    
    /**
     * AJAX: Export orders to CSV
     */
    public static function ajax_export_orders() {
        check_admin_referer( 'dbc_orders_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        $orders = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
        
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="orders-' . date( 'Y-m-d' ) . '.csv"' );
        
        $output = fopen( 'php://output', 'w' );
        
        // Header row
        fputcsv( $output, array(
            'Order ID',
            'Date',
            'Status',
            'Payment Status',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Shipping Address',
            'City',
            'State',
            'ZIP',
            'Country',
            'Items',
            'Total',
            'Signature Request',
            'Signature Message',
            'Tracking Carrier',
            'Tracking Number',
            'Admin Notes',
        ) );
        
        foreach ( $orders as $order ) {
            $items = json_decode( $order->order_items, true );
            $address = json_decode( $order->shipping_address, true );
            
            $item_names = array();
            if ( is_array( $items ) ) {
                foreach ( $items as $item ) {
                    $qty = isset( $item['quantity'] ) ? $item['quantity'] : 1;
                    $name = isset( $item['name'] ) ? $item['name'] : 'Unknown';
                    $item_names[] = $qty . 'x ' . $name;
                }
            }
            
            fputcsv( $output, array(
                $order->id,
                $order->created_at,
                $order->fulfillment_status ?? 'pending',
                $order->status,
                $order->customer_name,
                $order->customer_email,
                $order->customer_phone,
                $address['address_1'] ?? '',
                $address['city'] ?? '',
                $address['state'] ?? '',
                $address['zip'] ?? '',
                $address['country'] ?? '',
                implode( '; ', $item_names ),
                '$' . number_format( $order->total_amount, 2 ),
                $order->signature_request ? 'Yes' : 'No',
                $order->signature_message ?? '',
                $order->tracking_carrier ?? '',
                $order->tracking_number ?? '',
                $order->admin_notes ?? '',
            ) );
        }
        
        fclose( $output );
        exit;
    }
    
    /**
     * Send shipping notification email to customer
     */
    private static function send_shipping_notification( $order_id, $tracking_number = '', $tracking_carrier = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dbc_orders';
        
        $order = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $table_name WHERE id = %d", 
            $order_id 
        ) );
        
        if ( ! $order ) {
            return;
        }
        
        $to = $order->customer_email;
        $subject = 'Your Order Has Shipped! - Drew Bankston';
        
        // Get tracking URL
        $tracking_url = '';
        if ( $tracking_number ) {
            switch ( $tracking_carrier ) {
                case 'usps':
                    $tracking_url = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $tracking_number;
                    break;
                case 'ups':
                    $tracking_url = 'https://www.ups.com/track?tracknum=' . $tracking_number;
                    break;
                case 'fedex':
                    $tracking_url = 'https://www.fedex.com/fedextrack/?trknbr=' . $tracking_number;
                    break;
            }
        }
        
        $items = json_decode( $order->order_items, true );
        $items_html = '';
        
        if ( is_array( $items ) ) {
            foreach ( $items as $item ) {
                $items_html .= sprintf(
                    '<li style="padding: 8px 0; border-bottom: 1px solid #eee;">%s × %d</li>',
                    esc_html( $item['name'] ?? 'Item' ),
                    intval( $item['quantity'] ?? 1 )
                );
            }
        }
        
        $tracking_html = '';
        if ( $tracking_number ) {
            $tracking_html = sprintf(
                '<div style="background: #f0f7ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px; color: #1a365d;">Tracking Information</h3>
                    <p style="margin: 0;"><strong>Carrier:</strong> %s</p>
                    <p style="margin: 5px 0;"><strong>Tracking Number:</strong> %s</p>
                    %s
                </div>',
                esc_html( strtoupper( $tracking_carrier ) ),
                esc_html( $tracking_number ),
                $tracking_url ? '<p style="margin: 10px 0 0;"><a href="' . esc_url( $tracking_url ) . '" style="display: inline-block; background: #4a5568; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Track Your Package</a></p>' : ''
            );
        }
        
        $message = self::get_email_template(
            'Your Order is On Its Way! 📦',
            sprintf(
                '<p style="font-size: 16px; color: #4a5568; margin-bottom: 20px;">Hi %s,</p>
                <p style="font-size: 16px; color: #4a5568; margin-bottom: 20px;">Great news! Your order has been shipped and is on its way to you.</p>
                
                %s
                
                <div style="background: #fafafa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="margin: 0 0 15px; color: #1a365d;">Order #%d</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        %s
                    </ul>
                </div>
                
                <p style="font-size: 16px; color: #4a5568;">Thank you for supporting an independent author. I hope you enjoy your books!</p>
                
                <p style="font-size: 16px; color: #4a5568;">Happy reading,<br><strong>Drew Bankston</strong></p>',
                esc_html( explode( ' ', $order->customer_name )[0] ),
                $tracking_html,
                $order_id,
                $items_html
            )
        );
        
        $headers = array( 
            'Content-Type: text/html; charset=UTF-8',
            'From: Drew Bankston <author@drewbankston.com>',
        );
        
        wp_mail( $to, $subject, $message, $headers );
    }
    
    /**
     * Get branded email template
     */
    public static function get_email_template( $title, $content ) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #0d0d12; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px;">Drew Bankston</h1>
                            <p style="margin: 8px 0 0; font-size: 14px; color: #8b9dc3; text-transform: uppercase; letter-spacing: 2px;">Author</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 25px; font-size: 28px; font-weight: 700; color: #ffffff; line-height: 1.3;">' . $title . '</h2>
                            <div style="color: #c9d1e3; font-size: 16px; line-height: 1.7;">
                                ' . $content . '
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1);">
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="margin: 0 0 15px; font-size: 14px; color: #8b9dc3;">
                                            <a href="https://drewbankston.com" style="color: #a78bfa; text-decoration: none;">drewbankston.com</a>
                                        </p>
                                        <p style="margin: 0; font-size: 12px; color: #5a6785;">
                                            © ' . date( 'Y' ) . ' Drew Bankston. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
