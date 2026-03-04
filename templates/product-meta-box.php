<?php
/**
 * ETIM Product Meta Box Template - Modern UI
 * 
 * @var WP_Post $post
 * @var array $etim_data
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get the plugin URL for assets
$plugin_url = defined('ETIM_PLUGIN_URL') ? ETIM_PLUGIN_URL : plugins_url('', dirname(__FILE__));
$assets_url = $plugin_url . '/assets/images/';
?>

<div id="etim-meta-box-container">
    <input type="hidden" name="etim_data_json" id="etim-data-json" value="" />
    
    <div class="etim-card">
        <!-- Header: Brand + Group Select -->
        <div class="etim-card-header" id="etim-header">
            <div class="etim-brand">
                <div class="etim-brand-icon">
                    <img src="<?php echo esc_url($assets_url . 'webronic.png'); ?>" alt="Webronic" onerror="this.parentElement.innerHTML='<div class=\'etim-brand-icon-fallback\'>W</div>'" />
                </div>
                <!-- Title changed to match image -->
                <span class="etim-brand-text"><?php esc_html_e('ETIM Integration', 'etim-for-woocommerce'); ?></span>
            </div>
            
            <div class="etim-group-dropdown" style="display: flex; align-items: center; gap: 32px;">
                <div class="etim-row-label" style="min-width: unset;"><?php esc_html_e('ETIM Group', 'etim-for-woocommerce'); ?></div>
                <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                    <div class="etim-select-wrapper etim-group-wrapper" style="flex: 1;">
                        <select id="etim-group-select" class="etim-custom-select etim-group-select">
                            <option value=""><?php esc_html_e('Select ETIM Group', 'etim-for-woocommerce'); ?></option>
                        </select>
                        <span class="etim-select-arrow">
                            <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                        </span>
                    </div>
                    <button type="button" id="etim-clear-group" style="display: none; background: none; border: none; cursor: pointer; padding: 0; width: 24px; height: 24px; align-items: center; justify-content: center;" title="Clear">
                        <img src="<?php echo esc_url($assets_url . 'cancel.png'); ?>" alt="Clear" style="width: 14px; height: 14px;"/>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Loading State -->
        <div id="etim-loading-indicator" class="etim-loading etim-hidden">
            <div class="etim-spinner"></div>
            <span><?php esc_html_e('Loading...', 'etim-for-woocommerce'); ?></span>
        </div>
        
        <!-- Class Selection Row -->
        <div id="etim-class-row" class="etim-row" style="display: none;">
            <div class="etim-row-label"><?php esc_html_e('ETIM Class', 'etim-for-woocommerce'); ?></div>
            <div class="etim-row-content">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="etim-select-wrapper etim-class-wrapper" style="flex: 1;">
                        <select id="etim-class-select" class="etim-custom-select etim-class-select">
                            <option value=""><?php esc_html_e('Select ETIM Class', 'etim-for-woocommerce'); ?></option>
                        </select>
                        <span class="etim-select-arrow">
                            <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                        </span>
                    </div>
                    <button type="button" id="etim-clear-class" style="display: none; background: none; border: none; cursor: pointer; padding: 0; width: 24px; height: 24px; align-items: center; justify-content: center;" title="Clear">
                        <img src="<?php echo esc_url($assets_url . 'cancel.png'); ?>" alt="Clear" style="width: 14px; height: 14px;"/>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Features Section -->
        <div id="etim-features-section" class="etim-features-section" style="display: none;">
            <div class="etim-features-header">
                <div class="etim-features-title"><?php esc_html_e('ETIM Features', 'etim-for-woocommerce'); ?></div>
                <div class="etim-features-actions">
                    <button type="button" id="etim-sort-features" class="etim-btn etim-btn-outline">
                        <img src="<?php echo esc_url($assets_url . 'sort.png'); ?>" alt="Sort" class="etim-btn-icon-img" />
                        <span><?php esc_html_e('Sort', 'etim-for-woocommerce'); ?></span>
                    </button>
                    <button type="button" id="etim-add-feature" class="etim-btn etim-btn-primary">
                        <span class="etim-btn-plus">+</span>
                        <span><?php esc_html_e('Add', 'etim-for-woocommerce'); ?></span>
                    </button>
                </div>
            </div>
            
            <div id="etim-features-grid" class="etim-features-grid">
                <!-- Features will be loaded here -->
            </div>
            
            <!-- <div id="etim-empty-features" class="etim-empty-state" style="display: none;">
                <?php esc_html_e('No features added yet. Click "Add" to add features.', 'etim-for-woocommerce'); ?>
            </div> -->
        </div>
        <!-- Action Bar Section: Save, Download, and Shortcode in same row -->
        <div class="etim-action-bar" style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding: 24px; margin-top: 0; flex-wrap: wrap; gap: 16px;">
            <div class="etim-save-section" id="etim-save-section" style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 0; border: none; background: transparent;">
                <button type="button" id="etim-save-btn" class="etim-btn etim-btn-save" style="margin: 0;"><?php esc_html_e('Save ETIM Data', 'etim-for-woocommerce'); ?></button>
                <a href="<?php echo esc_url(admin_url('admin-post.php?action=etim_xml_export_single&product_id=' . $post->ID)); ?>" class="etim-btn etim-btn-xml" id="etim-download-xml-btn" style="margin: 0;"><?php esc_html_e('Download XML', 'etim-for-woocommerce'); ?></a>
                <span id="etim-save-status" class="etim-save-status" style="margin-left: 8px;"></span>
            </div>

            <div class="etim-shortcode-save-bar" style="margin: 0; padding: 0; border: none; background: transparent; box-shadow: none;">
                <div class="etim-shortcode-compact" style="margin: 0; border: 1px solid #e2e8f0;">
                    <span class="etim-shortcode-label"><?php esc_html_e('ETIM Shortcode', 'etim-for-woocommerce'); ?></span>
                    <code class="etim-shortcode-code" id="etim-shortcode-text">[etim_specs id="<?php echo esc_attr($post->ID); ?>"]</code>
                    <button type="button" class="etim-shortcode-copy-btn" id="etim-copy-shortcode" onclick="var t=document.getElementById('etim-shortcode-text').textContent;navigator.clipboard?navigator.clipboard.writeText(t).then(function(){var b=document.getElementById('etim-copy-shortcode');b.classList.add('copied');setTimeout(function(){b.classList.remove('copied');},2000);}):void 0;" title="<?php esc_attr_e('Copy shortcode', 'etim-for-woocommerce'); ?>">
                        <svg class="etim-copy-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path></svg>
                        <svg class="etim-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                    <span class="etim-shortcode-info" title="<?php esc_attr_e('Copy and paste this shortcode to display the ETIM specifications anywhere on your site.', 'etim-for-woocommerce'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Feature Modal -->
    <div id="etim-add-feature-modal">
        <div class="etim-modal-overlay"></div>
        <div class="etim-modal-content">
            <div class="etim-modal-header">
                <h3><?php esc_html_e('Add Feature', 'etim-for-woocommerce'); ?></h3>
                <button type="button" class="etim-modal-close">&times;</button>
            </div>
            <div class="etim-modal-body">
                <div class="etim-select-wrapper etim-modal-select-wrapper">
                    <select id="etim-feature-select" class="etim-custom-select">
                        <option value=""><?php esc_html_e('Select ETIM feature', 'etim-for-woocommerce'); ?></option>
                    </select>
                    <span class="etim-select-arrow">
                        <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                    </span>
                </div>
            </div>
            <div class="etim-modal-footer">
                <button type="button" class="etim-btn etim-btn-outline etim-modal-cancel"><?php esc_html_e('Cancel', 'etim-for-woocommerce'); ?></button>
                <button type="button" class="etim-btn etim-btn-primary" id="etim-confirm-add-feature"><?php esc_html_e('Add', 'etim-for-woocommerce'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Template for Feature Card -->
<script type="text/template" id="etim-feature-card-template">
    <div class="etim-feature-card" data-feature-code="{{code}}">
        <div class="etim-feature-field">
            <label class="etim-feature-label"><?php esc_html_e('Feature', 'etim-for-woocommerce'); ?></label>
            <div class="etim-select-wrapper etim-feature-select-wrapper">
                <select class="etim-custom-select etim-feature-name-select" data-feature-code="{{code}}">{{featureOptions}}</select>
                <span class="etim-select-arrow">
                    <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                </span>
            </div>
        </div>
        
        <div class="etim-feature-field etim-feature-value-field">
            <label class="etim-feature-label"><?php esc_html_e('Feature Value', 'etim-for-woocommerce'); ?></label>
            <div class="etim-feature-value-container">{{valueInput}}</div>
        </div>
        
        <div class="etim-feature-delete">
            <button type="button" class="etim-btn-delete etim-remove-feature" data-feature-code="{{code}}" title="<?php esc_attr_e('Remove Feature', 'etim-for-woocommerce'); ?>">
                <img src="<?php echo esc_url($assets_url . 'cancel.png'); ?>" alt="Delete" />
            </button>
        </div>
    </div>
</script>

<script type="text/javascript">
(function($) {
    // Pass external variables if needed, usually done with wp_localize_script
    window.etimAssetsUrl = '<?php echo esc_js($assets_url); ?>';
    window.etimDataInitial = <?php echo wp_json_encode($etim_data ?: []); ?>;
})(jQuery);
</script>