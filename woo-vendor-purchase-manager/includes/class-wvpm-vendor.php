<?php
/**
 * Vendor class
 *
 * @package WooVendorPurchaseManager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Vendor Class
 */
class WVPM_Vendor {
    /**
     * Table name
     */
    private static $table_name;
    
    /**
     * Initialize the class
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'wvpm_vendors';
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
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT '',
            address text DEFAULT '',
            description text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql);
    }
    
    /**
     * Get all vendors
     *
     * @param array $args Query arguments
     * @return array
     */
    public static function get_vendors($args = array()) {
        global $wpdb;
        
        self::init();
        
        $defaults = array(
            'number'     => 20,
            'offset'     => 0,
            'orderby'    => 'id',
            'order'      => 'DESC',
            'search'     => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = '';
        
        if (!empty($args['search'])) {
            $where = $wpdb->prepare(
                " WHERE name LIKE %s OR email LIKE %s",
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%'
            );
        }
        
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_name . "{$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d",
                $args['number'],
                $args['offset']
            )
        );
        
        $total = $wpdb->get_var("SELECT COUNT(id) FROM " . self::$table_name . $where);
        
        return array(
            'items' => $items,
            'total' => $total,
        );
    }
    
    /**
     * Get a single vendor
     *
     * @param int $id Vendor ID
     * @return object|null
     */
    public static function get_vendor($id) {
        global $wpdb;
        
        self::init();
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::$table_name . " WHERE id = %d",
                $id
            )
        );
    }
    
    /**
     * Add a new vendor
     *
     * @param array $args Vendor data
     * @return int|false
     */
    public static function add_vendor($args) {
        global $wpdb;
        
        self::init();
        
        $defaults = array(
            'name'        => '',
            'email'       => '',
            'phone'       => '',
            'address'     => '',
            'description' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate required fields
        if (empty($args['name']) || empty($args['email'])) {
            return false;
        }
        
        // Check if email already exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM " . self::$table_name . " WHERE email = %s",
                $args['email']
            )
        );
        
        if ($exists) {
            return false;
        }
        
        $inserted = $wpdb->insert(
            self::$table_name,
            array(
                'name'        => $args['name'],
                'email'       => $args['email'],
                'phone'       => $args['phone'],
                'address'     => $args['address'],
                'description' => $args['description'],
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        
        if (!$inserted) {
            return false;
        }
        
        do_action('wvpm_vendor_added', $wpdb->insert_id, $args);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update a vendor
     *
     * @param int $id Vendor ID
     * @param array $args Vendor data
     * @return bool
     */
    public static function update_vendor($id, $args) {
        global $wpdb;
        
        self::init();
        
        $defaults = array(
            'name'        => '',
            'email'       => '',
            'phone'       => '',
            'address'     => '',
            'description' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate required fields
        if (empty($args['name']) || empty($args['email'])) {
            return false;
        }
        
        // Check if email already exists with a different vendor
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM " . self::$table_name . " WHERE email = %s AND id != %d",
                $args['email'],
                $id
            )
        );
        
        if ($exists) {
            return false;
        }
        
        $updated = $wpdb->update(
            self::$table_name,
            array(
                'name'        => $args['name'],
                'email'       => $args['email'],
                'phone'       => $args['phone'],
                'address'     => $args['address'],
                'description' => $args['description'],
            ),
            array('id' => $id),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ),
            array('%d')
        );
        
        do_action('wvpm_vendor_updated', $id, $args);
        
        return $updated !== false;
    }
    
    /**
     * Delete a vendor
     *
     * @param int $id Vendor ID
     * @return bool
     */
    public static function delete_vendor($id) {
        global $wpdb;
        
        self::init();
        
        // Check if vendor exists
        $vendor = self::get_vendor($id);
        
        if (!$vendor) {
            return false;
        }
        
        // Check if vendor has purchases
        $purchase_table = $wpdb->prefix . 'wvpm_purchases';
        $has_purchases = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM {$purchase_table} WHERE vendor_id = %d",
                $id
            )
        );
        
        if ($has_purchases) {
            return false;
        }
        
        $deleted = $wpdb->delete(
            self::$table_name,
            array('id' => $id),
            array('%d')
        );
        
        do_action('wvpm_vendor_deleted', $id);
        
        return $deleted !== false;
    }
    
    /**
     * AJAX handler for adding a vendor
     */
    public static function ajax_add_vendor() {
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
        
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $address = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        
        if (empty($name) || empty($email)) {
            wp_send_json_error(array(
                'message' => __('Name and email are required fields.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $vendor_id = self::add_vendor(array(
            'name'        => $name,
            'email'       => $email,
            'phone'       => $phone,
            'address'     => $address,
            'description' => $description,
        ));
        
        if (!$vendor_id) {
            wp_send_json_error(array(
                'message' => __('Error adding vendor. Email may already be in use.', 'woo-vendor-purchase-manager')
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Vendor added successfully.', 'woo-vendor-purchase-manager'),
            'vendor'  => self::get_vendor($vendor_id)
        ));
    }
    
    /**
     * AJAX handler for editing a vendor
     */
    public static function ajax_edit_vendor() {
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
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $address = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        
        if (empty($id) || empty($name) || empty($email)) {
            wp_send_json_error(array(
                'message' => __('Invalid data provided.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $success = self::update_vendor($id, array(
            'name'        => $name,
            'email'       => $email,
            'phone'       => $phone,
            'address'     => $address,
            'description' => $description,
        ));
        
        if (!$success) {
            wp_send_json_error(array(
                'message' => __('Error updating vendor. Email may already be in use.', 'woo-vendor-purchase-manager')
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Vendor updated successfully.', 'woo-vendor-purchase-manager'),
            'vendor'  => self::get_vendor($id)
        ));
    }
    
    /**
     * AJAX handler for deleting a vendor
     */
    public static function ajax_delete_vendor() {
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
        
        if (empty($id)) {
            wp_send_json_error(array(
                'message' => __('Invalid vendor ID.', 'woo-vendor-purchase-manager')
            ));
        }
        
        $success = self::delete_vendor($id);
        
        if (!$success) {
            wp_send_json_error(array(
                'message' => __('Error deleting vendor. The vendor may have associated purchases.', 'woo-vendor-purchase-manager')
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Vendor deleted successfully.', 'woo-vendor-purchase-manager')
        ));
    }
    
    /**
     * AJAX handler for getting vendors
     */
    public static function ajax_get_vendors() {
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
        
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
        
        $args = array(
            'number'  => $per_page,
            'offset'  => ($page - 1) * $per_page,
            'search'  => $search,
            'orderby' => 'name',
            'order'   => 'ASC',
        );
        
        $result = self::get_vendors($args);
        
        wp_send_json_success(array(
            'vendors' => $result['items'],
            'total'   => $result['total'],
            'pages'   => ceil($result['total'] / $per_page)
        ));
    }
}

// Initialize static table name
WVPM_Vendor::init();
