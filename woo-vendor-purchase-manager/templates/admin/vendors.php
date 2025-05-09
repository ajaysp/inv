<?php
/**
 * Admin Vendors Template
 *
 * @package WooVendorPurchaseManager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wvpm-vendors">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Vendors', 'woo-vendor-purchase-manager'); ?></h1>
    <a href="#" class="page-title-action add-vendor-btn"><?php echo esc_html__('Add New', 'woo-vendor-purchase-manager'); ?></a>
    
    <hr class="wp-header-end">
    
    <div class="wvpm-filters">
        <div class="wvpm-search-box">
            <input type="search" id="wvpm-vendor-search" name="s" placeholder="<?php esc_attr_e('Search vendors...', 'woo-vendor-purchase-manager'); ?>">
            <button type="button" class="button wvpm-search-btn"><?php echo esc_html__('Search', 'woo-vendor-purchase-manager'); ?></button>
        </div>
    </div>
    
    <div class="wvpm-notices">
        <div class="wvpm-message wvpm-success" style="display:none;"></div>
        <div class="wvpm-message wvpm-error" style="display:none;"></div>
    </div>
    
    <div class="wvpm-vendors-table-wrapper">
        <table class="wp-list-table widefat fixed striped wvpm-vendors-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php echo esc_html__('ID', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-name"><?php echo esc_html__('Name', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-email"><?php echo esc_html__('Email', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-phone"><?php echo esc_html__('Phone', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-created"><?php echo esc_html__('Created', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php echo esc_html__('Actions', 'woo-vendor-purchase-manager'); ?></th>
                </tr>
            </thead>
            <tbody id="wvpm-vendors-list">
                <tr class="wvpm-loading">
                    <td colspan="6"><?php echo esc_html__('Loading vendors...', 'woo-vendor-purchase-manager'); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php echo esc_html__('ID', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-name"><?php echo esc_html__('Name', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-email"><?php echo esc_html__('Email', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-phone"><?php echo esc_html__('Phone', 'woo-vendor-purchase-manager'); ?></th>
                    <th scope="col" class="manage-column column-created"><?php echo esc_html__('Created', 'woo-vendor-purchase-manager'); ?></th>
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
    
    <!-- Add/Edit Vendor Modal -->
    <div class="wvpm-modal wvpm-vendor-modal">
        <div class="wvpm-modal-content">
            <span class="wvpm-modal-close">&times;</span>
            <h2 class="wvpm-modal-title"><?php echo esc_html__('Add New Vendor', 'woo-vendor-purchase-manager'); ?></h2>
            
            <form id="wvpm-vendor-form">
                <input type="hidden" name="vendor_id" id="vendor_id" value="0">
                
                <div class="wvpm-form-group">
                    <label for="vendor_name"><?php echo esc_html__('Name', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                    <input type="text" name="vendor_name" id="vendor_name" class="wvpm-form-control" required>
                </div>
                
                <div class="wvpm-form-group">
                    <label for="vendor_email"><?php echo esc_html__('Email', 'woo-vendor-purchase-manager'); ?> <span class="required">*</span></label>
                    <input type="email" name="vendor_email" id="vendor_email" class="wvpm-form-control" required>
                </div>
                
                <div class="wvpm-form-group">
                    <label for="vendor_phone"><?php echo esc_html__('Phone', 'woo-vendor-purchase-manager'); ?></label>
                    <input type="text" name="vendor_phone" id="vendor_phone" class="wvpm-form-control">
                </div>
                
                <div class="wvpm-form-group">
                    <label for="vendor_address"><?php echo esc_html__('Address', 'woo-vendor-purchase-manager'); ?></label>
                    <textarea name="vendor_address" id="vendor_address" class="wvpm-form-control" rows="3"></textarea>
                </div>
                
                <div class="wvpm-form-group">
                    <label for="vendor_description"><?php echo esc_html__('Description', 'woo-vendor-purchase-manager'); ?></label>
                    <textarea name="vendor_description" id="vendor_description" class="wvpm-form-control" rows="5"></textarea>
                </div>
                
                <div class="wvpm-form-actions">
                    <button type="submit" class="button button-primary"><?php echo esc_html__('Save Vendor', 'woo-vendor-purchase-manager'); ?></button>
                    <button type="button" class="button wvpm-modal-cancel"><?php echo esc_html__('Cancel', 'woo-vendor-purchase-manager'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="wvpm-modal wvpm-delete-modal">
        <div class="wvpm-modal-content">
            <span class="wvpm-modal-close">&times;</span>
            <h2 class="wvpm-modal-title"><?php echo esc_html__('Delete Vendor', 'woo-vendor-purchase-manager'); ?></h2>
            <p><?php echo esc_html__('Are you sure you want to delete this vendor? This action cannot be undone.', 'woo-vendor-purchase-manager'); ?></p>
            
            <div class="wvpm-form-actions">
                <button type="button" class="button button-primary wvpm-confirm-delete"><?php echo esc_html__('Delete', 'woo-vendor-purchase-manager'); ?></button>
                <button type="button" class="button wvpm-modal-cancel"><?php echo esc_html__('Cancel', 'woo-vendor-purchase-manager'); ?></button>
            </div>
        </div>
    </div>
</div>