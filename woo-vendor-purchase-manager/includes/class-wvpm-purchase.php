<?php
/**
 * Purchase class
 *
 * @package WooVendorPurchaseManager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Purchase Class
 */
class WVPM_Purchase {
    
    /**
     * Table name
     */
    private static $table_name;
    
    /**
     * Initialize the class
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'wvpm_purchases';
    }
    
    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        
        self::init();
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE " . self::$table_name . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            vendor_id mediumint(9) NOT NULL,
            product_id bigint(20) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            cost decimal(10,2) NOT NULL DEFAULT 0,
            purchase_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            notes text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql);
    }
    
    /**
     * Get all purchases
     *
     * @param array $args Query arguments
     * @return array
     */
    public static function get_purchases($args = array()) {
        global $wpdb;
        
        self::init();
        
        $defaults = array(
            'number'     => 20,
            'offset'     => 0,
            'orderby'    => 'purchase_date',
            'order'      => 'DESC',
            'vendor_id'  => 0,
            'product_id' => 0,
            'date_from'  => '',
            'date_to'    => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $vendors_table = $wpdb->prefix . 'wvpm_vendors';
        
        $where = array();
        $prepare_args = array();
        
        if (!empty($args['vendor_id'])) {
            $where[] = "p.vendor_id = %d";
            $prepare_args[] = $args['vendor_id'];
        }
        
        if (!empty($args['product_id'])) {
            $where[] = "p.product_id = %d";
            $prepare_args[] = $args['product_id'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = "p.purchase_date >= %s";
            $prepare_args[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = "p.purchase_date <= %s";
            $prepare_args[] = $args['date_to'];
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Add pagination arguments
        $prepare_args[] = $args['number'];
        $prepare_args[] = $args['offset'];
        
        // Build SQL query
        $query = "SELECT p.*, v.name as vendor_name, 
                 prod.post_title as product_name 
                 FROM " . self::$table_name . " p
                 LEFT JOIN {$vendors_table} v ON p.vendor_id = v.id
                 LEFT JOIN {$wpdb->posts} prod ON p.product_id = prod.ID
                 {$where_clause}
                 ORDER BY p.{$args['orderby']} {$args['order']}
                 LIMIT %d OFFSET %d";
        
        $items = $wpdb->get_results($wpdb->prepare($query, $prepare_args));
        
        // Count total without pagination
        $prepare_args_count = $prepare_args;
        array_pop($prepare_args_count); // Remove LIMIT
        array_pop($prepare_args_count); // Remove OFFSET
        
        $count_query = "SELECT COUNT(p.id) FROM " . self::$table_name . " p {$where_clause}";
        $total = $wpdb->get_var($wpdb->prepare($count_query, $prepare_args_count));
        
        return array(
            'items' => $items,
            'total' => $total,
        );
    }
    
    /**
     * Get a single purchase
     *
     * @param int $id Purchase ID
     * @return object|null
     */
    public static function get_purchase($id) {
        global $wpdb;
        
        self::init();
        
        $vendors_table = $wpdb->prefix . 'wvpm_vendors';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT p.*, v.name as vendor_name, 
                 prod.post_title as product_name 
                 FROM " . self::$table_name . " p
                 LEFT JOIN {$vendors_table} v ON p.vendor_id = v.id
                 LEFT JOIN {$wpdb->posts} prod ON p.product_id = prod.ID
                 WHERE p.id = %d",
                $id
            )
        );
    }
    
    /**
     * Add a new purchase
     *
     * @param array $args Purchase data
     * @return int|false
     */
    public static function add_purchase($args) {
        global $wpdb;
        
        self::init();
        
        $defaults = array(
            'vendor_id'     => 0,
            'product_id'    => 0,
            'quantity'      => 1,
            'cost'          => 0,
            'purchase_date' => current_time('mysql'),
            'notes'         => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate required fields
        if (empty($args['vendor_id']) || empty($args['product_id']) || empty($args['quantity']) || $args['cost'] <= 0) {
            return false;
        }
        
        // Check if vendor exists
        $vendor_table = $wpdb->prefix . 'wvpm_vendors';
        $vendor_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM {$vendor_table} WHERE id = %d",
                $args['vendor_id']
            )
        );
        
        if (!$vendor_exists) {
            return false;
        }
        
        // Check if product exists
        $product = wc_get_product($args['product_id']);
        if (!$product) {
            return false;
        }
        
        $inserted = $wpdb->insert(
            self::$table_name,
            array(
                'vendor_id'     => $args['vendor_id'],
                'product_id'    => $args['product_id'],
                'quantity'      => $args['quantity'],
                'cost'          => $args['cost'],
                'purchase_date' => $args['purchase_date'],
                'notes'         => $args['notes'],
            ),
            array(
                '%d',
                '%d',
                '%d',
                '%f',
                '%s',
                '%s',
            )
        );
        
        if (!$inserted) {
            return false;
        }
        
        do_action('wvpm_purchase_added', $wpdb->insert_id, $args);
        
        // Update product stock
        if ($product->managing_stock()) {
            $new_stock = wc_update_product_stock($product, $args['quantity'], 'increase');
            update_post_meta($args['product_id'], '_last_vendor_purchase', $wpdb->insert_id);
            update_post_meta($args['product_id'], '_last_purchase_date', $args['purchase_date']);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update a purchase
     *
     * @param int $id Purchase ID
     * @param array $args Purchase data
     * @return bool
     */
    public static function update_purchase($id, $args) {
        global $wpdb;
        
        self::init();
        
        // Get existing purchase
        $purchase = self::get_purchase($id);
        
        if (!$purchase) {
            return false;
        }
        
        $defaults = array(
            'vendor_id'     => $purchase->vendor_id,
            'product_id'    => $purchase->product_id,
            'quantity'      => $purchase->quantity,
            'cost'          => $purchase->cost,
            'purchase_date' => $purchase->purchase_date,
            'notes'         => $purchase->notes,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate required fields
        if (empty($args['vendor_id']) || empty($args['product_id']) || empty($args['quantity']) || $args['cost'] <= 0) {
            return false;
        }
        
        // Check if vendor exists
        $vendor_table = $wpdb->prefix . 'wvpm_vendors';
        $vendor_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM {$vendor_table} WHERE id = %d",
                $args['vendor_id']
            )
        );
        
        if (!$vendor_exists) {
            return false;
        }
        
        // Check if product exists
        $product = wc_get_product($args['product_id']);
        if (!$product) {
            return false;
        }
        
        // Update stock if product or quantity changed
        if ($product->managing_stock() && 
            ($purchase->product_id != $args['product_id'] || $purchase->quantity != $args['quantity'])) {
            
            // If product changed, revert stock for old product
            if ($purchase->product_id != $args['product_id']) {
                $old_product = wc_get_product($purchase->product_id);
                if ($old_product && $old_product->managing_stock()) {
                    wc_update_product_stock($old_product, $purchase->quantity, 'decrease');
                }
                
                // Add stock to new product
                wc_update_product_stock($product, $args['quantity'], 'increase');
            } else {
                // Just adjust the quantity difference
                $quantity_diff = $args['quantity'] - $purchase->quantity;
                if ($quantity_diff != 0) {
                    $stock_change_op = $quantity_diff > 0 ? 'increase' : 'decrease';
                    wc_update_product_stock($product, abs($quantity_diff), $stock_change_op);
                }
            }
        }
        
        $updated = $wpdb->update(
            self::$table_name,
            array(
                'vendor_id'     => $args['vendor_id'],
                'product_id'    => $args['product_id'],
                'quantity'      => $args['quantity'],
                'cost'          => $args['cost'],
                'purchase_date' => $args['purchase_date'],
                'notes'         => $args['notes'],
            ),
            array('id' => $id),
            array(
                '%d',
                '%d',
                '%d',
                '%f',
                '%s',
                '%s',
            ),
            array('%d')
        );
        
        do_action('wvpm_purchase_updated', $id, $args, $purchase);
        
        return $updated !== false;
    }
    
    /**
     * Delete a purchase
     *
     * @param int $id Purchase ID
     * @return bool
     */
    public static function delete_purchase($id) {
        global $wpdb;
        
        self::init();
        
        // Get purchase before deleting
        $purchase = self::get_purchase($id);
        
        if (!$purchase) {
            return false;
        }
        
        $deleted = $wpdb->delete(
            self::$table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($deleted) {
            // Adjust stock level
            $product = wc_get_product($purchase->product_id);
            if ($product && $product->managing_stock()) {
                wc_update_product_stock($product, $purchase->quantity, 'decrease');
            }
            
            do_action('wvpm_purchase_deleted', $id, $purchase);
        }
        
        return $deleted !== false;
    }
    
    /**
     * AJAX handler for adding a purchase
     */
    public static function ajax_add_purchase() {
        // Check nonce
        if (!check_ajax_referer('wvpm-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token sent.', 'woo-vendor-purchase-manager')
            ));
        }
        
        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0;
        $purchase_date = isset($_POST['purchase_date']) ? sanitize_text_field($_POST['purchase_date']) : current_time('mysql');
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        if (!$vendor_id || !$product_id || $quantity <= 0 || $cost <= 0) {
            wp_send_json_error(array(
                'message' => __('Please provide all required fields.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $purchase_id = self::add_purchase(array(
            'vendor_id'     => $vendor_id,
            'product_id'    => $product_id,
            'quantity'      => $quantity,
            'cost'          => $cost,
            'purchase_date' => $purchase_date,
            'notes'         => $notes,
        ));
        
        if (!$purchase_id) {
            wp_send_json_error(array(
                'message' => __('Error adding purchase.', 'woo-vendor-purchase-manager')
            ));
        }
        
        wp_send_json_success(array(
            'message'  => __('Purchase added successfully.', 'woo-vendor-purchase-manager'),
            'purchase' => self::get_purchase($purchase_id)
        ));
    }
    
    /**
     * AJAX handler for editing a purchase
     */
    public static function ajax_edit_purchase() {
        // Check nonce
        if (!check_ajax_referer('wvpm-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token sent.', 'woo-vendor-purchase-manager')
            ));
        }
        
        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0;
        $purchase_date = isset($_POST['purchase_date']) ? sanitize_text_field($_POST['purchase_date']) : current_time('mysql');
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        if (!$id || !$vendor_id || !$product_id || $quantity <= 0 || $cost <= 0) {
            wp_send_json_error(array(
                'message' => __('Please provide all required fields.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $success = self::update_purchase($id, array(
            'vendor_id'     => $vendor_id,
            'product_id'    => $product_id,
            'quantity'      => $quantity,
            'cost'          => $cost,
            'purchase_date' => $purchase_date,
            'notes'         => $notes,
        ));
        
        if (!$success) {
            wp_send_json_error(array(
                'message' => __('Error updating purchase.', 'woo-vendor-purchase-manager')
            ));
        }
        
        wp_send_json_success(array(
            'message'  => __('Purchase updated successfully.', 'woo-vendor-purchase-manager'),
            'purchase' => self::get_purchase($id)
        ));
    }
    
    /**
     * AJAX handler for deleting a purchase
     */
    public static function ajax_delete_purchase() {
        // Check nonce
        if (!check_ajax_referer('wvpm-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token sent.', 'woo-vendor-purchase-manager')
            ));
        }
        
        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error(array(
                'message' => __('Invalid purchase ID.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $success = self::delete_purchase($id);
        
        if (!$success) {
            wp_send_json_error(array(
                'message' => __('Error deleting purchase.', 'woo-vendor-purchase-manager')
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Purchase deleted successfully.', 'woo-vendor-purchase-manager')
        ));
    }
    
    /**
     * AJAX handler for getting purchases
     */
    public static function ajax_get_purchases() {
        // Check nonce
        if (!check_ajax_referer('wvpm-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token sent.', 'woo-vendor-purchase-manager')
            ));
        }
        
        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $vendor_id = isset($_GET['vendor_id']) ? intval($_GET['vendor_id']) : 0;
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
        
        $args = array(
            'number'     => $per_page,
            'offset'     => ($page - 1) * $per_page,
            'vendor_id'  => $vendor_id,
            'product_id' => $product_id,
            'date_from'  => $date_from,
            'date_to'    => $date_to,
        );
        
        $result = self::get_purchases($args);
        
        wp_send_json_success(array(
            'purchases' => $result['items'],
            'total'     => $result['total'],
            'pages'     => ceil($result['total'] / $per_page)
        ));
    }
}