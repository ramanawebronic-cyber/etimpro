<?php
/**
 * ETIM Bulk Handler Class
 *
 * Adds bulk ETIM assignment action to WooCommerce products list
 * and renders the bulk assignment admin page.
 *
 * @package ETIM_For_WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

class ETIM_Bulk_Handler {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register bulk action in products list
        add_filter('bulk_actions-edit-product', [$this, 'register_bulk_action']);
        add_filter('handle_bulk_actions-edit-product', [$this, 'handle_bulk_action'], 10, 3);

        // Register hidden admin page for bulk assignment
        add_action('admin_menu', [$this, 'register_bulk_page']);

        // Enqueue assets on bulk page
        add_action('admin_enqueue_scripts', [$this, 'enqueue_bulk_assets']);

        // AJAX: save bulk ETIM data
        add_action('wp_ajax_etim_bulk_save_data', [$this, 'ajax_bulk_save']);
    }

    /**
     * Add "Assign ETIM Features" to bulk actions dropdown
     */
    public function register_bulk_action($bulk_actions) {
        $bulk_actions['etim_bulk_assign'] = __('Assign ETIM Features', 'etim-for-woocommerce');
        return $bulk_actions;
    }

    /**
     * Handle the bulk action – redirect to the bulk assignment page
     */
    public function handle_bulk_action($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'etim_bulk_assign') {
            return $redirect_to;
        }

        $redirect_to = admin_url('admin.php?page=etim-bulk-assign&product_ids=' . implode(',', array_map('intval', $post_ids)));
        return $redirect_to;
    }

    /**
     * Register the bulk assignment page (hidden from menu)
     */
    public function register_bulk_page() {
        add_submenu_page(
            null, // hidden page
            __('Bulk ETIM Assignment', 'etim-for-woocommerce'),
            __('Bulk ETIM Assignment', 'etim-for-woocommerce'),
            'edit_products',
            'etim-bulk-assign',
            [$this, 'render_bulk_page']
        );
    }

    /**
     * Enqueue assets only on the bulk assignment page
     */
    public function enqueue_bulk_assets($hook) {
        if ($hook !== 'admin_page_etim-bulk-assign') {
            return;
        }

        $client_id = get_option('etim_client_id', '');
        if (empty($client_id)) {
            return;
        }

        // Select2
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0-rc.0');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);

        // jQuery UI Sortable
        wp_enqueue_script('jquery-ui-sortable');

        // Reuse existing product-meta CSS
        wp_enqueue_style(
            'etim-product-meta',
            ETIM_WC_PLUGIN_URL . 'assets/css/product-meta.css',
            [],
            ETIM_WC_VERSION
        );

        // Bulk-specific CSS
        wp_enqueue_style(
            'etim-bulk-assign',
            ETIM_WC_PLUGIN_URL . 'assets/css/bulk-assign.css',
            ['etim-product-meta'],
            ETIM_WC_VERSION
        );

        // Bulk-specific JS
        wp_enqueue_script(
            'etim-bulk-assign',
            ETIM_WC_PLUGIN_URL . 'assets/js/bulk-assign.js',
            ['jquery', 'select2', 'jquery-ui-sortable'],
            ETIM_WC_VERSION,
            true
        );

        $product_ids = isset($_GET['product_ids']) ? sanitize_text_field($_GET['product_ids']) : '';

        wp_localize_script('etim-bulk-assign', 'etimBulk', [
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('etim_ajax_nonce'),
            'productIds' => $product_ids,
            'strings'    => [
                'selectGroup'   => __('Select an ETIM Group...', 'etim-for-woocommerce'),
                'selectClass'   => __('Select an ETIM Class...', 'etim-for-woocommerce'),
                'selectFeature' => __('Select a feature', 'etim-for-woocommerce'),
                'selectValue'   => __('Select a value', 'etim-for-woocommerce'),
                'loading'       => __('Loading...', 'etim-for-woocommerce'),
                'noResults'     => __('No results found', 'etim-for-woocommerce'),
                'saving'        => __('Saving...', 'etim-for-woocommerce'),
                'saved'         => __('Saved!', 'etim-for-woocommerce'),
                'error'         => __('Error:', 'etim-for-woocommerce'),
            ],
        ]);
    }

    /**
     * Render the bulk assignment admin page
     */
    public function render_bulk_page() {
        if (!current_user_can('edit_products')) {
            wp_die(__('You do not have permission to access this page.', 'etim-for-woocommerce'));
        }

        $feature_access = ETIM_Feature_Access::get_instance();
        $license_manager = ETIM_License_Manager::get_instance();
        $assets_url = ETIM_WC_PLUGIN_URL . 'assets/images/';

        // Parse product IDs first
        $product_ids_raw = isset($_GET['product_ids']) ? sanitize_text_field($_GET['product_ids']) : '';
        $product_ids = array_filter(array_map('intval', explode(',', $product_ids_raw)));

        // If no products selected, show blocking page (nothing to show in background)
        if (empty($product_ids)) {
            echo '<div class="wrap"><h1>' . esc_html__('Bulk ETIM Assignment', 'etim-for-woocommerce') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('No products selected. Please go back and select products.', 'etim-for-woocommerce') . '</p></div>';
            echo '<p><a href="' . esc_url(admin_url('edit.php?post_type=product')) . '" class="button">' . esc_html__('Back to Products', 'etim-for-woocommerce') . '</a></p></div>';
            return;
        }

        // Determine popup type (null = no popup, user has full access)
        $popup_type = null;
        $popup_data = [];

        if (!$license_manager->has_active_license()) {
            $popup_type = 'no_license';
        } elseif (!$feature_access->can_bulk_assign()) {
            $popup_type = 'plan_not_eligible';
        } else {
            $bulk_limit = $feature_access->get_bulk_limit();
            $selected_count = count($product_ids);
            if ($bulk_limit !== PHP_INT_MAX && $selected_count > $bulk_limit) {
                $popup_type = 'bulk_limit_exceeded';
                $popup_data = ['bulk_limit' => $bulk_limit, 'selected_count' => $selected_count];
            } else {
                $remaining = $feature_access->get_remaining_product_slots();
                if ($remaining !== PHP_INT_MAX && $selected_count > $remaining) {
                    $popup_type = 'product_limit_reached';
                    $popup_data = [
                        'assigned'  => $feature_access->get_assigned_product_count(),
                        'limit'     => $feature_access->get_product_limit(),
                        'selected'  => $selected_count,
                        'remaining' => $remaining,
                    ];
                }
            }
        }

        // If no popup needed, check credentials (blocking page for config issue)
        if ($popup_type === null) {
            $client_id = get_option('etim_client_id', '');
            if (empty($client_id)) {
                echo '<div class="wrap"><h1>' . esc_html__('Bulk ETIM Assignment', 'etim-for-woocommerce') . '</h1>';
                echo '<div class="notice notice-error"><p>';
                printf(
                    wp_kses(
                        __('ETIM API credentials are not configured. Please <a href="%s">configure your credentials</a> first.', 'etim-for-woocommerce'),
                        ['a' => ['href' => []]]
                    ),
                    esc_url(admin_url('admin.php?page=etim-settings'))
                );
                echo '</p></div></div>';
                return;
            }
        }

        // Fetch product names for display
        $products = [];
        foreach ($product_ids as $pid) {
            $title = get_the_title($pid);
            if ($title) {
                $products[] = ['id' => $pid, 'title' => $title];
            }
        }

        // Always render the template (visible in background for popup cases)
        include ETIM_WC_PLUGIN_DIR . 'templates/bulk-assign-page.php';

        // If there's an error condition, render the popup overlay
        if ($popup_type !== null) {
            $this->render_bulk_popup($popup_type, $popup_data, $assets_url, $feature_access);
        }
    }

    /**
     * Render popup overlay for bulk assignment restrictions
     */
    private function render_bulk_popup($type, $data, $assets_url, $feature_access) {
        $upgrade_url = $feature_access->get_upgrade_url();
        $products_url = admin_url('edit.php?post_type=product');
        $license_url = admin_url('admin.php?page=etim-license');
        ?>
        <div id="etim-bulk-restriction-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;background:rgba(15,23,42,0.45);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;">
            <div id="etim-bulk-restriction-popup" style="position:relative;max-width:520px;width:90%;background:#fff;border-radius:20px;padding:44px 36px 36px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.15);animation:etimPopupIn 0.3s ease;">

                <!-- Close Button -->
                <button type="button" id="etim-bulk-popup-close" style="position:absolute;top:14px;right:14px;width:34px;height:34px;border-radius:50%;background:#f8fafc;border:1px solid #e2e8f0;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" title="Close">
                    <img src="<?php echo esc_url($assets_url . 'close.png'); ?>" alt="Close" style="width:14px;height:14px;opacity:0.6;" onerror="this.parentElement.innerHTML='&times;';" />
                </button>

                <!-- Icon -->
                <div style="margin-bottom:20px;">
                    <img src="<?php echo esc_url($assets_url . 'open1.png'); ?>" alt="" style="width:220px;height:auto;" />
                </div>

                <?php if ($type === 'no_license'): ?>
                    <h2 style="margin:0 0 14px;font-size:22px;font-weight:700;color:#4888E8;">License Required</h2>
                    <p style="margin:0 0 8px;font-size:16px;color:#0f172a;line-height:1.6;font-weight:700;">You don't have a proper license to use this feature.</p>
                    <p style="margin:0 0 28px;font-size:15px;color:#334155;font-weight:600;">Please activate a valid license key to access Bulk ETIM Assignment.</p>
                    <a href="<?php echo esc_url($license_url); ?>" style="display:inline-flex;align-items:center;gap:8px;background:#4888E8;color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;transition:background 0.2s;box-shadow:0 4px 12px rgba(72,136,232,0.3);">
                        <img src="<?php echo esc_url($assets_url . 'premium.png'); ?>" alt="" style="width:22px;height:22px;object-fit:contain;" onerror="this.style.display='none';" />
                        Upgrade
                    </a>

                <?php elseif ($type === 'plan_not_eligible'): ?>
                    <h2 style="margin:0 0 14px;font-size:22px;font-weight:700;color:#4888E8;">Upgrade Required</h2>
                    <p style="margin:0 0 8px;font-size:16px;color:#0f172a;line-height:1.6;font-weight:700;">Bulk ETIM Assignment is available only on Distributor and WooCommerce Agency Plans.</p>
                    <p style="margin:0 0 28px;font-size:15px;color:#334155;font-weight:600;">Upgrade your plan to assign ETIM data to multiple products at once and streamline your workflow.</p>
                    <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#4888E8;color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;transition:background 0.2s;box-shadow:0 4px 12px rgba(72,136,232,0.3);">
                        <img src="<?php echo esc_url($assets_url . 'pro.png'); ?>" alt="" style="width:22px;height:22px;object-fit:contain;" onerror="this.style.display='none';" />
                        Upgrade
                    </a>

                <?php elseif ($type === 'bulk_limit_exceeded'): ?>
                    <h2 style="margin:0 0 14px;font-size:22px;font-weight:700;color:#4888E8;">Bulk Limit Exceeded</h2>
                    <p style="margin:0 0 8px;font-size:16px;color:#0f172a;line-height:1.6;font-weight:700;">Your plan allows bulk assignment to <?php echo intval($data['bulk_limit']); ?> products at a time.</p>
                    <p style="margin:0 0 8px;font-size:15px;color:#334155;font-weight:600;">You selected <strong><?php echo intval($data['selected_count']); ?></strong> products.</p>
                    <p style="margin:0 0 28px;font-size:14px;color:#64748b;">Please select fewer products or upgrade your plan for higher limits.</p>
                    <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#4888E8;color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;transition:background 0.2s;box-shadow:0 4px 12px rgba(72,136,232,0.3);">
                        <img src="<?php echo esc_url($assets_url . 'pro.png'); ?>" alt="" style="width:22px;height:22px;object-fit:contain;" onerror="this.style.display='none';" />
                        Upgrade
                    </a>

                <?php elseif ($type === 'product_limit_reached'): ?>
                    <h2 style="margin:0 0 14px;font-size:22px;font-weight:700;color:#4888E8;">Product Limit Reached</h2>
                    <p style="margin:0 0 8px;font-size:16px;color:#0f172a;line-height:1.6;font-weight:700;">You have assigned ETIM data to <?php echo intval($data['assigned']); ?> of <?php echo intval($data['limit']); ?> products allowed on your current plan.</p>
                    <p style="margin:0 0 8px;font-size:15px;color:#334155;font-weight:600;">You selected <strong><?php echo intval($data['selected']); ?></strong> products but only <strong><?php echo intval($data['remaining']); ?></strong> slot(s) remaining.</p>
                    <p style="margin:0 0 28px;font-size:14px;color:#64748b;">Upgrade your plan to assign ETIM data to more products.</p>
                    <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#4888E8;color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;transition:background 0.2s;box-shadow:0 4px 12px rgba(72,136,232,0.3);">
                        <img src="<?php echo esc_url($assets_url . 'pro.png'); ?>" alt="" style="width:22px;height:22px;object-fit:contain;" onerror="this.style.display='none';" />
                        Upgrade
                    </a>
                <?php endif; ?>

                <!-- Back to Products -->
                <div style="margin-top:20px;">
                    <a href="<?php echo esc_url($products_url); ?>" style="display:inline-flex;align-items:center;gap:6px;color:#64748b;font-size:14px;font-weight:600;text-decoration:none;padding:8px 20px;border-radius:8px;border:1px solid #e2e8f0;transition:all 0.2s;background:#f8fafc;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        Back to Products
                    </a>
                </div>
            </div>
        </div>

        <style>
            @keyframes etimPopupIn {
                from { opacity: 0; transform: scale(0.92) translateY(20px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            #etim-bulk-popup-close:hover {
                background: #fef2f2 !important;
                border-color: #fecaca !important;
            }
            #etim-bulk-popup-close:hover img {
                opacity: 1 !important;
            }
            #etim-bulk-restriction-popup a[href*="checkout"]:hover,
            #etim-bulk-restriction-popup a[href*="etim-license"]:hover {
                background: #3874CD !important;
            }
        </style>

        <script>
        (function(){
            var closeBtn = document.getElementById('etim-bulk-popup-close');
            var overlay = document.getElementById('etim-bulk-restriction-overlay');
            if (closeBtn && overlay) {
                closeBtn.addEventListener('click', function(){
                    var popup = document.getElementById('etim-bulk-restriction-popup');
                    if (popup) popup.style.display = 'none';
                    overlay.style.pointerEvents = 'none';
                    overlay.style.background = 'rgba(15,23,42,0.25)';
                    overlay.style.backdropFilter = 'blur(2px)';
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * AJAX: Save ETIM data for multiple products
     */
    public function ajax_bulk_save() {
        check_ajax_referer('etim_ajax_nonce', 'nonce');

        if (!current_user_can('edit_products')) {
            wp_send_json_error(['message' => __('Permission denied.', 'etim-for-woocommerce')]);
        }

        $product_ids_raw = isset($_POST['product_ids']) ? sanitize_text_field($_POST['product_ids']) : '';
        $product_ids = array_filter(array_map('intval', explode(',', $product_ids_raw)));

        if (empty($product_ids)) {
            wp_send_json_error(['message' => __('No products specified.', 'etim-for-woocommerce')]);
        }

        $json = isset($_POST['etim_data']) ? stripslashes($_POST['etim_data']) : '';
        $etim_data = json_decode($json, true);

        if (!is_array($etim_data)) {
            wp_send_json_error(['message' => __('Invalid ETIM data.', 'etim-for-woocommerce')]);
        }

        // Feature gating: check bulk limit
        $feature_access = ETIM_Feature_Access::get_instance();
        if (!$feature_access->can_bulk_assign()) {
            wp_send_json_error(['message' => __('Bulk assignment is not available on your current plan.', 'etim-for-woocommerce')]);
        }

        $bulk_limit = $feature_access->get_bulk_limit();
        if ($bulk_limit !== PHP_INT_MAX && count($product_ids) > $bulk_limit) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Your plan allows bulk assignment to %d products at a time. You selected %d.', 'etim-for-woocommerce'),
                    $bulk_limit, count($product_ids)
                ),
                'error_type' => 'bulk_limit_exceeded',
            ]);
        }

        $db = ETIM_DB::get_instance();
        $success = 0;
        $failed = 0;

        foreach ($product_ids as $pid) {
            // Check per-product limit
            if (!$feature_access->can_assign_product($pid)) {
                $failed++;
                continue;
            }
            $saved = $db->save_etim_data($pid, $etim_data);
            if ($saved) {
                $success++;
            } else {
                $failed++;
            }
        }

        if ($failed === 0) {
            wp_send_json_success([
                'message' => sprintf(
                    __('ETIM data saved successfully for %d product(s).', 'etim-for-woocommerce'),
                    $success
                ),
                'success_count' => $success,
                'failed_count'  => $failed,
            ]);
        } else {
            wp_send_json_error([
                'message' => sprintf(
                    __('Saved %d product(s), failed %d product(s).', 'etim-for-woocommerce'),
                    $success,
                    $failed
                ),
                'success_count' => $success,
                'failed_count'  => $failed,
            ]);
        }
    }
}
