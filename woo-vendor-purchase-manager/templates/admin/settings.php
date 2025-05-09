<?php
/**
 * Admin Settings Template
 *
 * @package WooVendorPurchaseManager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wvpm-settings">
    <h1><?php echo esc_html__('Vendor & Purchase Manager Settings', 'woo-vendor-purchase-manager'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('wvpm_settings_group'); ?>
        <?php do_settings_sections('wvpm-settings'); ?>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo esc_html__('Enable Purchase Notifications', 'woo-vendor-purchase-manager'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wvpm_enable_notifications" value="1" <?php checked(get_option('wvpm_enable_notifications'), 1); ?>>
                        <?php echo esc_html__('Send email notifications when new purchases are added', 'woo-vendor-purchase-manager'); ?>
                    </label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php echo esc_html__('Notification Email', 'woo-vendor-purchase-manager'); ?></th>
                <td>
                    <input type="email" name="wvpm_notification_email" class="regular-text" value="<?php echo esc_attr(get_option('wvpm_notification_email', get_option('admin_email'))); ?>">
                    <p class="description"><?php echo esc_html__('Email address to receive purchase notifications', 'woo-vendor-purchase-manager'); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php echo esc_html__('Default Purchase Status', 'woo-vendor-purchase-manager'); ?></th>
                <td>
                    <select name="wvpm_default_status" class="regular-text">
                        <option value="pending" <?php selected(get_option('wvpm_default_status'), 'pending'); ?>><?php echo esc_html__('Pending', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="completed" <?php selected(get_option('wvpm_default_status'), 'completed'); ?>><?php echo esc_html__('Completed', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="cancelled" <?php selected(get_option('wvpm_default_status'), 'cancelled'); ?>><?php echo esc_html__('Cancelled', 'woo-vendor-purchase-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php echo esc_html__('Currency Format', 'woo-vendor-purchase-manager'); ?></th>
                <td>
                    <select name="wvpm_currency_format" class="regular-text">
                        <option value="symbol" <?php selected(get_option('wvpm_currency_format'), 'symbol'); ?>><?php echo esc_html__('Symbol (e.g. $10.00)', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="code" <?php selected(get_option('wvpm_currency_format'), 'code'); ?>><?php echo esc_html__('Code (e.g. USD 10.00)', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="both" <?php selected(get_option('wvpm_currency_format'), 'both'); ?>><?php echo esc_html__('Both (e.g. $10.00 USD)', 'woo-vendor-purchase-manager'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php echo esc_html__('Date Format', 'woo-vendor-purchase-manager'); ?></th>
                <td>
                    <select name="wvpm_date_format" class="regular-text">
                        <option value="Y-m-d" <?php selected(get_option('wvpm_date_format'), 'Y-m-d'); ?>><?php echo esc_html__('YYYY-MM-DD', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="m/d/Y" <?php selected(get_option('wvpm_date_format'), 'm/d/Y'); ?>><?php echo esc_html__('MM/DD/YYYY', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="d/m/Y" <?php selected(get_option('wvpm_date_format'), 'd/m/Y'); ?>><?php echo esc_html__('DD/MM/YYYY', 'woo-vendor-purchase-manager'); ?></option>
                        <option value="F j, Y" <?php selected(get_option('wvpm_date_format'), 'F j, Y'); ?>><?php echo esc_html__('Month Day, Year', 'woo-vendor-purchase-manager'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>