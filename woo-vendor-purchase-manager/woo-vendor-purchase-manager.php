<?php
/**
 * Plugin Name: WooCommerce Vendor and Purchase Manager
 * Plugin URI: https://example.com/woo-vendor-purchase-manager
 * Description: Adds vendor management and purchase tracking functionality to WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: woo-vendor-purchase-manager
 * WC requires at least: 5.0.0
 * WC tested up to: 8.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        ?>
        <div class="error notice">
            <p><?php _e('WooCommerce Vendor and Purchase Manager requires WooCommerce to be installed and active.', 'woo-vendor-purchase-manager'); ?></p>
        </div>
        <?php
    });
    return;
}

// Define plugin constants
define('WVPM_VERSION', '1.0.0');
define('WVPM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WVPM_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class WooVendorPurchaseManager {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the plugin
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Include required files
        $this->includes();
        
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Register deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Add menu items
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register assets
        add_action('admin_enqueue_scripts', array($this, 'register_assets'));
        
        // Add AJAX handlers
        $this->register_ajax_handlers();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Include vendor class
        require_once WVPM_PLUGIN_DIR . 'includes/class-wvpm-vendor.php';
        
        // Include purchase class
        require_once WVPM_PLUGIN_DIR . 'includes/class-wvpm-purchase.php';
    }
    
    /**
     * Activate plugin
     */
    public function activate() {
        // Create database tables
        WVPM_Vendor::create_table();
        WVPM_Purchase::create_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Deactivate plugin
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Add main menu item
        add_menu_page(
            __('Vendor & Purchase Manager', 'woo-vendor-purchase-manager'),
            __('Vendor & Purchase', 'woo-vendor-purchase-manager'),
            'manage_woocommerce',
            'wvpm-dashboard',
            array($this, 'render_dashboard_page'),
            'dashicons-store',
            56
        );
        
        // Add vendors submenu
        add_submenu_page(
            'wvpm-dashboard',
            __('Vendors', 'woo-vendor-purchase-manager'),
            __('Vendors', 'woo-vendor-purchase-manager'),
            'manage_woocommerce',
            'wvpm-vendors',
            array($this, 'render_vendors_page')
        );
        
        // Add purchases submenu
        add_submenu_page(
            'wvpm-dashboard',
            __('Purchases', 'woo-vendor-purchase-manager'),
            __('Purchases', 'woo-vendor-purchase-manager'),
            'manage_woocommerce',
            'wvpm-purchases',
            array($this, 'render_purchases_page')
        );
        
        // Add settings submenu
        add_submenu_page(
            'wvpm-dashboard',
            __('Settings', 'woo-vendor-purchase-manager'),
            __('Settings', 'woo-vendor-purchase-manager'),
            'manage_woocommerce',
            'wvpm-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin assets
     */
    public function register_assets($hook) {
        // Check if we're on our plugin pages
        if (strpos($hook, 'wvpm-') === false) {
            return;
        }
        
        // Register and enqueue styles
        wp_register_style(
            'wvpm-admin-styles',
            WVPM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WVPM_VERSION
        );
        wp_enqueue_style('wvpm-admin-styles');
        
        // Register and enqueue scripts
        wp_register_script(
            'wvpm-admin-scripts',
            WVPM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WVPM_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('wvpm-admin-scripts', 'wvpm_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvpm-nonce')
        ));
        
        wp_enqueue_script('wvpm-admin-scripts');
    }
    
    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // Vendor AJAX handlers
        add_action('wp_ajax_wvpm_add_vendor', array('WVPM_Vendor', 'ajax_add_vendor'));
        add_action('wp_ajax_wvpm_edit_vendor', array('WVPM_Vendor', 'ajax_edit_vendor'));
        add_action('wp_ajax_wvpm_delete_vendor', array('WVPM_Vendor', 'ajax_delete_vendor'));
        add_action('wp_ajax_wvpm_get_vendors', array('WVPM_Vendor', 'ajax_get_vendors'));
        
        // Purchase AJAX handlers
        add_action('wp_ajax_wvpm_add_purchase', array('WVPM_Purchase', 'ajax_add_purchase'));
        add_action('wp_ajax_wvpm_edit_purchase', array('WVPM_Purchase', 'ajax_edit_purchase'));
        add_action('wp_ajax_wvpm_delete_purchase', array('WVPM_Purchase', 'ajax_delete_purchase'));
        add_action('wp_ajax_wvpm_get_purchases', array('WVPM_Purchase', 'ajax_get_purchases'));
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        include WVPM_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    /**
     * Render vendors page
     */
    public function render_vendors_page() {
        include WVPM_PLUGIN_DIR . 'templates/admin/vendors.php';
    }
    
    /**
     * Render purchases page
     */
    public function render_purchases_page() {
        include WVPM_PLUGIN_DIR . 'templates/admin/purchases.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include WVPM_PLUGIN_DIR . 'templates/admin/settings.php';
    }
}

// Initialize the plugin
$woo_vendor_purchase_manager = new WooVendorPurchaseManager();
