<?php
/**
 * Plugin Name: ETIM for WooCommerce
 * Plugin URI: https://webronic.com
 * Description: ETIM Classification integration for WooCommerce products. Map products with ETIM Groups, Classes, Features, and Values using the official ETIM API.
 * Version: 3.0.103
 * Author: Webronic
 * Author URI: https://webronic.com
 * Text Domain: etim-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ETIM_WC_VERSION', '3.0.117');
define('ETIM_WC_PLUGIN_FILE', __FILE__);
define('ETIM_WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ETIM_WC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ETIM_WC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main ETIM for WooCommerce Class
 */
final class ETIM_For_WooCommerce {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load plugin files
        add_action('plugins_loaded', [$this, 'load_plugin']);
        
        // Activation/Deactivation hooks
        register_activation_hook(ETIM_WC_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(ETIM_WC_PLUGIN_FILE, [$this, 'deactivate']);
        
        // Add settings link
        add_filter('plugin_action_links_' . ETIM_WC_PLUGIN_BASENAME, [$this, 'add_settings_link']);
        
        // Declare HPOS compatibility
        add_action('before_woocommerce_init', [$this, 'declare_hpos_compatibility']);
    }
    
    /**
     * Load plugin
     */
    public function load_plugin() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }
        
        // Load text domain
        load_plugin_textdomain('etim-for-woocommerce', false, dirname(ETIM_WC_PLUGIN_BASENAME) . '/languages');
        
        // Load required files
        $this->includes();
        
        // Initialize classes
        $this->init_classes();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-api.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-license-manager.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-feature-access.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-admin-settings.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-ajax-handler.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-product-meta.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-frontend-filter.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-bulk-handler.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/class-etim-sync-handler.php';
        require_once ETIM_WC_PLUGIN_DIR . 'includes/db/class-etim-db.php';
    }

    /**
     * Initialize classes
     */
    private function init_classes() {
        ETIM_License_Manager::get_instance();
        ETIM_Feature_Access::get_instance();
        ETIM_Admin_Settings::get_instance();
        ETIM_Ajax_Handler::get_instance();
        ETIM_Product_Meta::get_instance();
        ETIM_Frontend_Filter::get_instance();
        ETIM_Bulk_Handler::get_instance();
        ETIM_Sync_Handler::get_instance();
    }
    
    /**
     * Plugin activation
     */
public function activate() {
    require_once ETIM_WC_PLUGIN_DIR . 'includes/db/class-etim-db.php';

    $db = ETIM_DB::get_instance();
    $db->install_tables();

    update_option('etim_wc_version', ETIM_WC_VERSION);
    wp_cache_flush();
}

    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events if any
        wp_clear_scheduled_hook('etim_wc_cleanup_tokens');
        
        // Clear transients
        delete_transient('etim_access_token');
    }
    
    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=etim-settings'),
            __('Settings', 'etim-for-woocommerce')
        );
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    esc_html__('%s requires WooCommerce to be installed and active. Please install and activate WooCommerce.', 'etim-for-woocommerce'),
                    '<strong>ETIM for WooCommerce</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', ETIM_WC_PLUGIN_FILE, true);
        }
    }
}

/**
 * Initialize plugin
 */
function etim_for_woocommerce() {
    return ETIM_For_WooCommerce::get_instance();
}

// Start the plugin
etim_for_woocommerce();

add_action('wp_ajax_etim_save_data', 'etim_save_data_callback');

function etim_save_data_callback() {

    // 1️⃣ Security check
    check_ajax_referer('etim_ajax_nonce', 'nonce');

    if (!current_user_can('edit_products')) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    // 2️⃣ Get product ID
    $product_id = intval($_POST['productId'] ?? 0);

    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
    }

    // Feature gating: check product assignment limit
    $feature_access = ETIM_Feature_Access::get_instance();
    if (!$feature_access->can_assign_product($product_id)) {
        $limit = $feature_access->get_product_limit();
        $count = $feature_access->get_assigned_product_count();
        wp_send_json_error([
            'message'       => sprintf('Product limit reached (%d/%d). Upgrade your plan to assign ETIM data to more products.', $count, $limit),
            'error_type'    => 'product_limit_reached',
            'current_count' => $count,
            'max_allowed'   => $limit,
            'upgrade_url'   => $feature_access->get_upgrade_url(),
        ]);
    }

    // 3️⃣ Get JSON
    $json = $_POST['etim_data_json'] ?? '';

    $etim_data = json_decode(stripslashes($json), true);

    if (!is_array($etim_data)) {
        wp_send_json_error(['message' => 'Invalid JSON data']);
    }

    error_log("ETIM DECODED ARRAY: " . print_r($etim_data, true));

    // 4️⃣ Save to DB
    require_once ETIM_WC_PLUGIN_DIR . 'includes/db/class-etim-db.php';

    $db = ETIM_DB::get_instance();
    $saved = $db->save_etim_data($product_id, $etim_data);

    if ($saved) {
        wp_send_json_success(['message' => 'ETIM data saved successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to save data']);
    }
}


function webronic_get_github_token() {
    $cached_token = get_transient('webronic_github_token');

    if ($cached_token !== false && !empty($cached_token)) {
        return $cached_token;
    }

    $response = wp_remote_get('https://app.virtualtour360.ai/get-token.php?key=my_super_secret_987654321', [
        'timeout' => 15,
        'sslverify' => true,
    ]);

    if (is_wp_error($response)) {
        error_log('Webronic Virtual Tour: Failed to fetch GitHub token - ' . $response->get_error_message());
        return null;
    }

    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code !== 200) {
        error_log('Webronic Virtual Tour: Token endpoint returned HTTP ' . $http_code);
        return null;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['token']) || empty($body['token'])) {
        error_log('Webronic Virtual Tour: Token not found in response');
        return null;
    }

    // Cache token for 24 hours
    set_transient('webronic_github_token', $body['token'], 24 * HOUR_IN_SECONDS);

    return $body['token'];
}
/**
 * Initialize GitHub Update Checker
 * Only runs in admin context
 */
function webronic_init_update_checker() {
    // Only load in admin
    if (!is_admin()) {
        return;
    }

    // Load the update checker library
    require_once ETIM_WC_PLUGIN_DIR . 'updater/plugin-update-checker.php';

    // Get GitHub token
    $github_token = webronic_get_github_token();

    // Build the update checker using full namespace path
    $updateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/webronic/wordpress_etim_sale',
        __FILE__,
        'wordpress_etim_sale'
    );

    // Set to use releases (recommended for GitHub)
    $updateChecker->getVcsApi()->enableReleaseAssets();
    
    // Or if you want to use a specific branch instead of releases, uncomment below:
    // $updateChecker->setBranch('main');

    // Set authentication if token is available
    if (!empty($github_token)) {
        $updateChecker->setAuthentication($github_token);
    } else {
        error_log('Webronic Virtual Tour: No GitHub token available for update checker');
    }
}
add_action('init', 'webronic_init_update_checker');

/**
 * Add manual update check button in plugins page
 */
function webronic_add_check_update_link($links, $file) {
    if (strpos($file, 'webronic-virtual-tour') !== false || strpos($file, '360-maptour') !== false) {
        $check_update_url = wp_nonce_url(
            admin_url('plugins.php?webronic_check_update=1'),
            'webronic_check_update'
        );
        $links[] = '<a href="' . esc_url($check_update_url) . '">Check for updates</a>';
    }
    return $links;
}
add_filter('plugin_action_links', 'webronic_add_check_update_link', 10, 2);

/**
 * Handle manual update check
 */
function webronic_handle_manual_update_check() {
    if (isset($_GET['webronic_check_update']) && $_GET['webronic_check_update'] == '1') {
        if (!current_user_can('update_plugins')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_GET['_wpnonce'], 'webronic_check_update')) {
            wp_die('Security check failed');
        }

        // Clear all update caches
        delete_site_transient('update_plugins');
        delete_transient('webronic_github_token'); // Force fresh token
        
        // Clear PUC specific cache
        $cache_key = 'puc_check_info-360-maptour-wordpress-plugin';
        delete_site_transient($cache_key);
        delete_option($cache_key);

        // Force WordPress to check for updates
        wp_update_plugins();

        // Redirect back with success message
        wp_redirect(admin_url('plugins.php?webronic_update_checked=1'));
        exit;
    }
}
add_action('admin_init', 'webronic_handle_manual_update_check');

/**
 * Show admin notice after manual update check
 */
function webronic_show_update_check_notice() {
    if (isset($_GET['webronic_update_checked']) && $_GET['webronic_update_checked'] == '1') {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Webronic Virtual Tour:</strong> Update check completed. If an update is available, it will appear above.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'webronic_show_update_check_notice');

/**
 * Debug: Add update status to plugin row (optional - helps with troubleshooting)
 */
function webronic_show_update_debug_info($plugin_meta, $plugin_file) {
    // Only show for this plugin and only for admins
    if (!current_user_can('manage_options')) {
        return $plugin_meta;
    }
    
    if (strpos($plugin_file, 'webronic-virtual-tour') !== false || strpos($plugin_file, '360-maptour') !== false) {
        // Check if debug mode is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $token = webronic_get_github_token();
            $token_status = $token ? 'Token: ✓' : 'Token: ✗';
            // $plugin_meta[] = '<small style="color: #666;">' . esc_html($token_status) . '</small>';
        }
    }
    return $plugin_meta;
}
add_filter('plugin_row_meta', 'webronic_show_update_debug_info', 10, 2);


// ===== ETIM Sidebar Layout for Product Category =====
// ===== ETIM Sidebar Layout for Product Category =====
add_action('wp_head', function () {

    if (!is_product_category() || get_option('etim_enable_filter', 'no') !== 'yes') {
        return;
    }
    if (class_exists('ETIM_Feature_Access') && !ETIM_Feature_Access::get_instance()->can_use_filter()) {
        return;
    }

    ?>
    <style>
        /* Main Layout */
        .etim-archive-wrapper {
            display: flex;
            gap: 30px;
        }

        /* Sidebar */
        .etim-sidebar {
            width: 25%;
            min-width: 250px;
            padding: 20px;
            /* Remove border/background to just rely on the plugin dropdown styling */
            height: fit-content;
        }

        /* Product Area */
        .etim-products {
            width: 75%;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .etim-archive-wrapper {
                flex-direction: column;
            }

            .etim-sidebar,
            .etim-products {
                width: 100%;
            }
        }
    </style>
    <?php
});


add_action('woocommerce_before_shop_loop', function () {

    if (!is_product_category() || get_option('etim_enable_filter', 'no') !== 'yes') {
        return;
    }
    if (class_exists('ETIM_Feature_Access') && !ETIM_Feature_Access::get_instance()->can_use_filter()) {
        return;
    }

    echo '<div class="etim-archive-wrapper">';
    echo '<div class="etim-sidebar">';
    echo do_shortcode('[etim_class_filter]');
    echo '</div>';
    echo '<div class="etim-products">';

}, 5);


add_action('woocommerce_after_shop_loop', function () {

    if (!is_product_category() || get_option('etim_enable_filter', 'no') !== 'yes') {
        return;
    }
    if (class_exists('ETIM_Feature_Access') && !ETIM_Feature_Access::get_instance()->can_use_filter()) {
        return;
    }

    echo '</div>'; // Close products
    echo '</div>'; // Close wrapper

}, 50);