<?php
/**
 * Bulk ETIM Assignment Page Template
 *
 * @var array  $products   Array of ['id' => int, 'title' => string]
 * @var array  $product_ids
 * @var string $assets_url
 *
 * @package ETIM_For_WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap etim-bulk-wrap">

    <!-- Page Header -->
    <div class="etim-bulk-page-header">
        <div class="etim-bulk-header-left">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>" class="etim-bulk-back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <?php esc_html_e('Back to Products', 'etim-for-woocommerce'); ?>
            </a>
            <h1 class="etim-bulk-title"><?php esc_html_e('Bulk ETIM Assignment', 'etim-for-woocommerce'); ?></h1>
            <p class="etim-bulk-subtitle">
                <?php
                printf(
                    esc_html__('Assigning ETIM data to %d selected product(s)', 'etim-for-woocommerce'),
                    count($products)
                );
                ?>
            </p>
        </div>
    </div>

    <!-- Selected Products Summary -->
    <div class="etim-bulk-products-summary">
        <div class="etim-bulk-products-header" id="etim-bulk-products-toggle">
            <div class="etim-bulk-products-header-left">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 01-8 0"></path></svg>
                <span class="etim-bulk-products-count">
                    <?php printf(esc_html__('%d Products Selected', 'etim-for-woocommerce'), count($products)); ?>
                </span>
            </div>
            <svg class="etim-bulk-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <div class="etim-bulk-products-list" id="etim-bulk-products-list" style="display: none;">
            <?php foreach ($products as $p) : ?>
                <div class="etim-bulk-product-item">
                    <span class="etim-bulk-product-id">#<?php echo esc_html($p['id']); ?></span>
                    <span class="etim-bulk-product-name"><?php echo esc_html($p['title']); ?></span>
                    <a href="<?php echo esc_url(get_edit_post_link($p['id'])); ?>" target="_blank" class="etim-bulk-product-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Hidden fields -->
    <input type="hidden" id="etim-bulk-product-ids" value="<?php echo esc_attr(implode(',', $product_ids)); ?>" />
    <input type="hidden" id="etim-bulk-data-json" value="" />

    <!-- Main ETIM Assignment Card -->
    <div id="etim-bulk-container">
        <div class="etim-card">
            <!-- Header: Brand + Group Select -->
            <div class="etim-card-header" id="etim-bulk-header">
                <div class="etim-brand">
                    <div class="etim-brand-icon">
                        <img src="<?php echo esc_url($assets_url . 'webronic.png'); ?>" alt="Webronic" onerror="this.parentElement.innerHTML='<div class=\'etim-brand-icon-fallback\'>W</div>'" />
                    </div>
                    <span class="etim-brand-text"><?php esc_html_e('ETIM Integration', 'etim-for-woocommerce'); ?></span>
                </div>

                <div class="etim-group-dropdown" style="display: flex; align-items: center; gap: 32px;">
                    <div class="etim-row-label" style="min-width: unset;"><?php esc_html_e('ETIM Group', 'etim-for-woocommerce'); ?></div>
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                        <div class="etim-select-wrapper etim-group-wrapper" style="flex: 1;">
                            <select id="etim-bulk-group-select" class="etim-custom-select etim-group-select">
                                <option value=""><?php esc_html_e('Select ETIM Group', 'etim-for-woocommerce'); ?></option>
                            </select>
                            <span class="etim-select-arrow">
                                <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                            </span>
                        </div>
                        <button type="button" id="etim-bulk-clear-group" style="display: none; background: none; border: none; cursor: pointer; padding: 0; width: 24px; height: 24px; align-items: center; justify-content: center;" title="Clear">
                            <img src="<?php echo esc_url($assets_url . 'cancel.png'); ?>" alt="Clear" style="width: 14px; height: 14px;" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="etim-bulk-loading" class="etim-loading etim-hidden">
                <div class="etim-spinner"></div>
                <span><?php esc_html_e('Loading...', 'etim-for-woocommerce'); ?></span>
            </div>

            <!-- Class Selection Row -->
            <div id="etim-bulk-class-row" class="etim-row" style="display: none;">
                <div class="etim-row-label"><?php esc_html_e('ETIM Class', 'etim-for-woocommerce'); ?></div>
                <div class="etim-row-content">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="etim-select-wrapper etim-class-wrapper" style="flex: 1;">
                            <select id="etim-bulk-class-select" class="etim-custom-select etim-class-select">
                                <option value=""><?php esc_html_e('Select ETIM Class', 'etim-for-woocommerce'); ?></option>
                            </select>
                            <span class="etim-select-arrow">
                                <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                            </span>
                        </div>
                        <button type="button" id="etim-bulk-clear-class" style="display: none; background: none; border: none; cursor: pointer; padding: 0; width: 24px; height: 24px; align-items: center; justify-content: center;" title="Clear">
                            <img src="<?php echo esc_url($assets_url . 'cancel.png'); ?>" alt="Clear" style="width: 14px; height: 14px;" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div id="etim-bulk-features-section" class="etim-features-section" style="display: none;">
                <div class="etim-features-header">
                    <div class="etim-features-title"><?php esc_html_e('ETIM Features', 'etim-for-woocommerce'); ?></div>
                    <div class="etim-features-actions">
                        <button type="button" id="etim-bulk-sort-features" class="etim-btn etim-btn-outline">
                            <img src="<?php echo esc_url($assets_url . 'sort.png'); ?>" alt="Sort" class="etim-btn-icon-img" />
                            <span><?php esc_html_e('Sort', 'etim-for-woocommerce'); ?></span>
                        </button>
                        <button type="button" id="etim-bulk-add-feature" class="etim-btn etim-btn-primary">
                            <span class="etim-btn-plus">+</span>
                            <span><?php esc_html_e('Add', 'etim-for-woocommerce'); ?></span>
                        </button>
                    </div>
                </div>

                <div id="etim-bulk-features-grid" class="etim-features-grid">
                    <!-- Features will be loaded here -->
                </div>
            </div>

            <!-- Action Bar: Save, Download XML, Shortcodes -->
            <div class="etim-action-bar" style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding: 16px 24px; flex-wrap: wrap; gap: 16px;">
                <div class="etim-save-section" id="etim-bulk-save-section" style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 0; border: none; background: transparent;">
                    <button type="button" id="etim-bulk-save-btn" class="etim-btn etim-btn-save" style="margin: 0;">
                        <?php esc_html_e('Save ETIM Data', 'etim-for-woocommerce'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=etim_xml_export_all')); ?>" class="etim-btn etim-btn-xml" id="etim-bulk-download-xml-btn" style="margin: 0;">
                        <?php esc_html_e('Download XML (All)', 'etim-for-woocommerce'); ?>
                    </a>
                    <span id="etim-bulk-save-status" class="etim-save-status" style="margin-left: 8px;"></span>
                </div>

                <div class="etim-shortcode-save-bar" style="margin: 0; padding: 0; border: none; background: transparent; box-shadow: none;">
                    <div class="etim-shortcode-compact" style="margin: 0; border: 1px solid #e2e8f0;">
                        <span class="etim-shortcode-label"><?php esc_html_e('ETIM Shortcode', 'etim-for-woocommerce'); ?></span>
                        <code class="etim-shortcode-code" id="etim-bulk-shortcode-text">[etim_specs id="PRODUCT_ID"]</code>
                        <button type="button" class="etim-shortcode-copy-btn" id="etim-bulk-copy-shortcode" title="<?php esc_attr_e('Copy shortcode', 'etim-for-woocommerce'); ?>">
                            <svg class="etim-copy-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path></svg>
                            <svg class="etim-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </button>
                        <span class="etim-shortcode-info" title="<?php esc_attr_e('Use [etim_specs id="PRODUCT_ID"] for each product. Replace PRODUCT_ID with the actual product ID.', 'etim-for-woocommerce'); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Per-Product Shortcodes & XML Downloads -->
            <div class="etim-bulk-product-actions" style="padding: 0 24px 24px;">
                <div class="etim-bulk-product-actions-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                    <span><?php esc_html_e('Product Shortcodes & XML Downloads', 'etim-for-woocommerce'); ?></span>
                </div>
                <div class="etim-bulk-product-shortcodes-list">
                    <?php foreach ($products as $p) : ?>
                        <div class="etim-bulk-shortcode-row">
                            <span class="etim-bulk-shortcode-product-name"><?php echo esc_html($p['title']); ?></span>
                            <code class="etim-shortcode-code">[etim_specs id="<?php echo esc_attr($p['id']); ?>"]</code>
                            <button type="button" class="etim-shortcode-copy-btn etim-bulk-copy-single" data-shortcode='[etim_specs id="<?php echo esc_attr($p['id']); ?>"]' title="<?php esc_attr_e('Copy', 'etim-for-woocommerce'); ?>">
                                <svg class="etim-copy-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path></svg>
                                <svg class="etim-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                            <a href="<?php echo esc_url(admin_url('admin-post.php?action=etim_xml_export_single&product_id=' . $p['id'])); ?>" class="etim-btn etim-btn-xml etim-btn-xml-small" title="<?php esc_attr_e('Download XML', 'etim-for-woocommerce'); ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                XML
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Feature Modal -->
    <div id="etim-bulk-add-feature-modal">
        <div class="etim-modal-overlay"></div>
        <div class="etim-modal-content">
            <div class="etim-modal-header">
                <h3><?php esc_html_e('Add Feature', 'etim-for-woocommerce'); ?></h3>
                <button type="button" class="etim-modal-close">&times;</button>
            </div>
            <div class="etim-modal-body">
                <div class="etim-select-wrapper etim-modal-select-wrapper">
                    <select id="etim-bulk-feature-select" class="etim-custom-select">
                        <option value=""><?php esc_html_e('Select ETIM feature', 'etim-for-woocommerce'); ?></option>
                    </select>
                    <span class="etim-select-arrow">
                        <img src="<?php echo esc_url($assets_url . 'drop.png'); ?>" alt="Dropdown" />
                    </span>
                </div>
            </div>
            <div class="etim-modal-footer">
                <button type="button" class="etim-btn etim-btn-outline etim-modal-cancel"><?php esc_html_e('Cancel', 'etim-for-woocommerce'); ?></button>
                <button type="button" class="etim-btn etim-btn-primary" id="etim-bulk-confirm-add-feature"><?php esc_html_e('Add', 'etim-for-woocommerce'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Feature Card Template (reused from single product meta) -->
<script type="text/template" id="etim-bulk-feature-card-template">
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
    window.etimBulkAssetsUrl = '<?php echo esc_js($assets_url); ?>';
})(jQuery);
</script>
