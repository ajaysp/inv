<?php
/**
 * Admin Dashboard Template
 *
 * @package WooVendorPurchaseManager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wvpm-dashboard">
    <h1><?php echo esc_html__('Vendor & Purchase Manager Dashboard', 'woo-vendor-purchase-manager'); ?></h1>
    
    <div class="wvpm-welcome-panel">
        <div class="wvpm-welcome-panel-content">
            <h2><?php echo esc_html__('Welcome to WooCommerce Vendor & Purchase Manager!', 'woo-vendor-purchase-manager'); ?></h2>
            <p class="about-description"><?php echo esc_html__('This plugin helps you manage your vendors and product purchases for your WooCommerce store.', 'woo-vendor-purchase-manager'); ?></p>
            
            <div class="wvpm-dashboard-widgets">
                <div class="wvpm-dashboard-widget">
                    <h3><?php echo esc_html__('Quick Navigation', 'woo-vendor-purchase-manager'); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=wvpm-vendors')); ?>"><?php echo esc_html__('Manage Vendors', 'woo-vendor-purchase-manager'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=wvpm-purchases')); ?>"><?php echo esc_html__('Manage Purchases', 'woo-vendor-purchase-manager'); ?></a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=wvpm-settings')); ?>"><?php echo esc_html__('Plugin Settings', 'woo-vendor-purchase-manager'); ?></a></li>
                    </ul>
                </div>
                
                <div class="wvpm-dashboard-widget">
                    <h3><?php echo esc_html__('Recent Activity', 'woo-vendor-purchase-manager'); ?></h3>
                    <?php
                    global $wpdb;
                    $purchases_table = $wpdb->prefix . 'wvpm_purchases';
                    $vendors_table = $wpdb->prefix . 'wvpm_vendors';
                    
                    $recent_purchases = $wpdb->get_results(
                        "SELECT p.*, v.name as vendor_name, prod.post_title as product_name 
                        FROM {$purchases_table} p
                        LEFT JOIN {$vendors_table} v ON p.vendor_id = v.id
                        LEFT JOIN {$wpdb->posts} prod ON p.product_id = prod.ID
                        ORDER BY p.purchase_date DESC
                        LIMIT 5"
                    );
                    
                    if ($recent_purchases) :
                    ?>
                    <table class="wvpm-recent-purchases">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Date', 'woo-vendor-purchase-manager'); ?></th>
                                <th><?php echo esc_html__('Vendor', 'woo-vendor-purchase-manager'); ?></th>
                                <th><?php echo esc_html__('Product', 'woo-vendor-purchase-manager'); ?></th>
                                <th><?php echo esc_html__('Quantity', 'woo-vendor-purchase-manager'); ?></th>
                                <th><?php echo esc_html__('Cost', 'woo-vendor-purchase-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_purchases as $purchase) : ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($purchase->purchase_date))); ?></td>
                                <td><?php echo esc_html($purchase->vendor_name); ?></td>
                                <td><?php echo esc_html($purchase->product_name); ?></td>
                                <td><?php echo esc_html($purchase->quantity); ?></td>
                                <td><?php echo wc_price($purchase->cost); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else : ?>
                    <p><?php echo esc_html__('No recent purchases found.', 'woo-vendor-purchase-manager'); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="wvpm-dashboard-widget">
                    <h3><?php echo esc_html__('Summary', 'woo-vendor-purchase-manager'); ?></h3>
                    <?php
                    $vendor_count = $wpdb->get_var("SELECT COUNT(*) FROM {$vendors_table}");
                    $purchase_count = $wpdb->get_var("SELECT COUNT(*) FROM {$purchases_table}");
                    $total_cost = $wpdb->get_var("SELECT SUM(cost * quantity) FROM {$purchases_table}");
                    ?>
                    <ul class="wvpm-stats">
                        <li><strong><?php echo esc_html__('Total Vendors:', 'woo-vendor-purchase-manager'); ?></strong> <?php echo esc_html($vendor_count); ?></li>
                        <li><strong><?php echo esc_html__('Total Purchases:', 'woo-vendor-purchase-manager'); ?></strong> <?php echo esc_html($purchase_count); ?></li>
                        <li><strong><?php echo esc_html__('Total Cost:', 'woo-vendor-purchase-manager'); ?></strong> <?php echo wc_price($total_cost); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
