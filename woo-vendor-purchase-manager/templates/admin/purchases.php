<?php
/**
 * Admin Purchases Template
 *
 * @package WooVendorPurchaseManager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wvpm-purchases">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Purchases', 'woo-vendor-purchase-manager'); ?></h1>
    <a href="#" class="page-title-action add-purchase-btn"><?php echo esc_html__('Add New', 'woo-vendor-purchase-manager'); ?></a>
    
    <hr class="wp-header-end">
    
    <div class="wvpm-filters">
        <div class="wvpm-filter-row">
            <div class="wvpm-filter-col">
                <label for="wvpm-vendor-filter"><?php echo esc_html__('Vendor:', 'woo-vendor-purchase-manager'); ?></label>
                <select id="wvpm-vendor-filter" class="wvpm-form-control">
                    <option value=""><?php echo esc_html__('All Vendors', 'woo-vendor-purchase-manager'); ?></option>
                    <?php
                    global $wpdb;
                    $vendors_table = $wpdb->prefix . 'wvpm_vendors';
                    $vendors = $wpdb->get_results("SELECT id, name FROM {$vendors_table} ORDER BY name ASC");
                    
                    foreach ($vendors as $vendor) {
                        echo '<option value="' . esc_attr($vendor->id) . '">' . esc_html($vendor->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="wvpm-filter-col">
                <label for="wvpm-product-filter"><?php echo esc_html__('Product:', 'woo-vendor-purchase-manager'); ?></label>
                <select id="wvpm-product-filter" class="wvpm-form-control" data-placeholder="<?php esc_attr_e('Search for a product...', 'woo-vendor-purchase-manager'); ?>">
                    <option value=""><?php echo esc_html__('All Products', 'woo-vendor-purchase-manager'); ?></option>
                </select>
            </div>
            
            <div class="wvpm-filter-col">
                <label for="wvpm-date-from"><?php echo esc_html__('Date From:', 'woo-vendor-purchase-manager'); ?></label>
                <input type="date" id="wvpm-date-from" class="wvpm-form-control">
            </div>
            
            <div class="wvpm-filter-col">
                <label for="wvpm-date-to"><?php echo esc_html__('Date To:', 'woo-vendor-purchase-manager'); ?></label>
                <input type="date" id="wvpm-date-to" class="wvpm-form-control">
            </div>
            
            <div class="wvpm-filter-col">
                <button type="button" class="button button-primary wvpm-filter-btn"><?php echo esc_html__('Filter', 'woo-vendor-purchase-manager'); ?></button>
                <button type="button" class="button wvpm-reset-btn"><?php echo esc_html__('Reset', 'woo-vendor-purchase-manager'); ?></button>
            </div>
        </div>
    </div>
    
    <div class="wvpm-notices">
        <div class="wvpm-message wvpm-success" style="display:none;"></div>
        <div class="wvpm-message wvpm-error" style="display:none;"></div>
    </div>
    
    <div class="wvpm-purchases-table-wrapper">
        <table class="wp-list-table widefat fixed striped wvpm-purchases-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php echo esc_html__('ID', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php echo esc_html__('Date', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-vendor"><?php echo esc_html__('Vendor', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-product"><?php echo esc_html__('Product', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-quantity"><?php echo esc_html__('Quantity', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-cost"><?php echo esc_html__('Cost', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-total"><?php echo esc_html__('Total', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php echo esc_html__('Actions', 'woo-vendor-purchase-manager'); ?></th>
                </tr>
            </thead>
            <tbody id="wvpm-purchases-list">
                <tr class="wvpm-loading">
                    <td colspan="8"><?php echo esc_html__('Loading purchases...', 'woo-vendor-purchase-manager'); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php echo esc_html__('ID', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php echo esc_html__('Date', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-vendor"><?php echo esc_html__('Vendor', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-product"><?php echo esc_html__('Product', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-quantity"><?php echo esc_html__('Quantity', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-cost"><?php echo esc_html__('Cost', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-total"><?php echo esc_html__('Total', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php echo esc_html__('Actions', 'woo-vendor-purchase-manager'); ?></th>
                </tr>
            </tfoot>
        </table>
        
        <div class="wvpm-pagination">
            <div class="wvpm-pagination-counts">
                <span class="wvpm-pagination-current">0</span> - <span class="wvpm-pagination-total">0</span>
            </div>
            <div class="wvpm-pagination-links">
                <a href="#" class="wvpm-pagination-prev button disabled"><?php echo esc_html__('Previous', 'woo-vendor-purchase-manager'); ?></a>
                <span class="wvpm-pagination-pages"></span>
                <a href="#" class="wvpm-pagination-next button disabled"><?php echo esc_html__('Next', 'woo-vendor-purchase-manager'); ?></a>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Purchase Modal -->
    <div class="wvpm-modal wvpm-purchase-modal">
        <div class="wvpm-modal-content">
            <span class="wvpm-modal-close">&times;</span>
            <h2 class="wvpm-modal-title"><?php echo esc_html__('Add New Purchase', 'woo-vendor-purchase-manager'); ?></h2>
            
            <form id="wvpm-purchase-form">
                <input type="hidden" name="purchase_id" id="purchase_id" value="0">
                
                <div class="wvpm-form-row">
                    <div class="wvpm-form-group">
                        <label for="purchase_vendor"><?php echo esc_html__('Vendor', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                        <select name="purchase_vendor" id="purchase_vendor" class="wvpm-form-control" required>
                            <option value=""><?php echo esc_html__('Select Vendor', 'woo-vendor-purchase-manager'); ?></option>
                            <?php
                            foreach ($vendors as $vendor) {
                                echo '<option value="' . esc_attr($vendor->id) . '">' . esc_html($vendor->name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="wvpm-form-group">
                        <label for="purchase_product"><?php echo esc_html__('Product', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                        <select name="purchase_product" id="purchase_product" class="wvpm-form-control" required data-placeholder="<?php esc_attr_e('Search for a product...', 'woo-vendor-purchase-manager'); ?>">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                
                <div class="wvpm-form-row">
                    <div class="wvpm-form-group">
                        <label for="purchase_quantity"><?php echo esc_html__('Quantity', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                        <input type="number" name="purchase_quantity" id="purchase_quantity" class="wvpm-form-control" min="1" step="1" value="1" required>
                    </div>
                    
                    <div class="wvpm-form-group">
                        <label for="purchase_cost"><?php echo esc_html__('Unit Cost', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                        <input type="number" name="purchase_cost" id="purchase_cost" class="wvpm-form-control" min="0.01" step="0.01" value="0.00" required>
                    </div>
                    
                    <div class="wvpm-form-group">
                        <label for="purchase_date"><?php echo esc_html__('Date', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                        <input type="date" name="purchase_date" id="purchase_date" class="wvpm-form-control" required value="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                </div>
                
                <div class="wvpm-form-group">
                    <label for="purchase_notes"><?php echo esc_html__('Notes', 'woo-vendor-purchase-manager'); ?></label>
                    <textarea name="purchase_notes" id="purchase_notes" class="wvpm-form-control" rows="3"></textarea>
                </div>
                
                <div class="wvpm-form-actions">
                    <button type="submit" class="button button-primary"><?php echo esc_html__('Save Purchase', 'woo-vendor-purchase-manager'); ?></button>
                    <button type="button" class="button wvpm-modal-cancel"><?php echo esc_html__('Cancel', 'woo-vendor-purchase-manager'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="wvpm-modal wvpm-delete-modal">
        <div class="wvpm-modal-content">
            <span class="wvpm-modal-close">&times;</span>
            <h2 class="wvpm-modal-title"><?php echo esc_html__('Delete Purchase', 'woo-vendor-purchase-manager'); ?></h2>
            <p><?php echo esc_html__('Are you sure you want to delete this purchase? This action cannot be undone.', 'woo-vendor-purchase-manager'); ?></p>
            
            <div class="wvpm-form-actions">
                <button type="button" class="button button-primary wvpm-confirm-delete"><?php echo esc_html__('Delete', 'woo-vendor-purchase-manager'); ?></button>
                <button type="button" class="button wvpm-modal-cancel"><?php echo esc_html__('Cancel', 'woo-vendor-purchase-manager'); ?></button>
            </div>
        </div>
    </div>
</div>