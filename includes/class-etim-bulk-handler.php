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

        // Feature gating: block Manufacturer/Free from bulk assign
        $feature_access = ETIM_Feature_Access::get_instance();
        if (!$feature_access->can_bulk_assign()) {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Bulk ETIM Assignment', 'etim-for-woocommerce') . '</h1>';
            echo '<div style="max-width:520px;margin:60px auto;text-align:center;padding:48px 32px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;">';
            echo '<div style="width:64px;height:64px;margin:0 auto 20px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:50%;display:flex;align-items:center;justify-content:center;">';
            echo '<svg width="28" height="28" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#fff" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>';
            echo '</div>';
            echo '<h2 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#0f172a;">Pro Feature</h2>';
            echo '<p style="margin:0 0 24px;font-size:14px;color:#64748b;line-height:1.6;">Bulk ETIM assignment is available on Distributor and WooCommerce Agency plans. Upgrade to assign ETIM data to multiple products at once.</p>';
            echo '<a href="' . esc_url($feature_access->get_upgrade_url()) . '" target="_blank" style="display:inline-block;background:#4888E8;color:#fff;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;transition:background 0.2s;">Upgrade Now</a>';
            echo '<br><a href="' . esc_url(admin_url('edit.php?post_type=product')) . '" style="display:inline-block;margin-top:12px;color:#64748b;font-size:13px;text-decoration:none;">&larr; Back to Products</a>';
            echo '</div></div>';
            return;
        }

        $product_ids_raw = isset($_GET['product_ids']) ? sanitize_text_field($_GET['product_ids']) : '';
        $product_ids = array_filter(array_map('intval', explode(',', $product_ids_raw)));

        if (empty($product_ids)) {
            echo '<div class="wrap"><h1>' . esc_html__('Bulk ETIM Assignment', 'etim-for-woocommerce') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('No products selected. Please go back and select products.', 'etim-for-woocommerce') . '</p></div>';
            echo '<p><a href="' . esc_url(admin_url('edit.php?post_type=product')) . '" class="button">' . esc_html__('Back to Products', 'etim-for-woocommerce') . '</a></p></div>';
            return;
        }

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

        // Fetch product names for display
        $products = [];
        foreach ($product_ids as $pid) {
            $title = get_the_title($pid);
            if ($title) {
                $products[] = ['id' => $pid, 'title' => $title];
            }
        }

        $assets_url = ETIM_WC_PLUGIN_URL . 'assets/images/';

        include ETIM_WC_PLUGIN_DIR . 'templates/bulk-assign-page.php';
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
