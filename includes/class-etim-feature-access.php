<?php
/**
 * ETIM Feature Access - Centralized feature gating based on license plan
 *
 * @package ETIM_For_WooCommerce
 * @since 2.1.0
 */

defined('ABSPATH') || exit;

class ETIM_Feature_Access {

    /**
     * Product limits per plan
     */
    const MANUFACTURER_PRODUCT_LIMIT = 5;
    const DISTRIBUTOR_PRODUCT_LIMIT  = 50;

    /**
     * Bulk limits per plan
     */
    const DISTRIBUTOR_BULK_LIMIT = 10;

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {}

    // ========================================================================
    // PLAN RETRIEVAL
    // ========================================================================

    /**
     * Get the current plan code
     */
    public function get_current_plan() {
        $stored = get_option('etim_user_plan', null);
        if ($stored !== null) {
            return (int) $stored;
        }
        return ETIM_License_Manager::PLAN_FREE;
    }

    /**
     * Check if user has at least Distributor plan
     */
    private function is_distributor_or_above() {
        $plan = $this->get_current_plan();
        return ($plan >= ETIM_License_Manager::PLAN_DISTRIBUTOR);
    }

    /**
     * Check if user has ERP/Agency plan
     */
    private function is_erp() {
        return ($this->get_current_plan() >= ETIM_License_Manager::PLAN_ERP);
    }

    // ========================================================================
    // PRODUCT LIMIT
    // ========================================================================

    /**
     * Get product limit for current plan
     */
    public function get_product_limit() {
        $plan = $this->get_current_plan();

        switch ($plan) {
            case ETIM_License_Manager::PLAN_ERP:
                return PHP_INT_MAX;
            case ETIM_License_Manager::PLAN_DISTRIBUTOR:
                return self::DISTRIBUTOR_PRODUCT_LIMIT;
            case ETIM_License_Manager::PLAN_MANUFACTURER:
            case ETIM_License_Manager::PLAN_FREE:
            default:
                return self::MANUFACTURER_PRODUCT_LIMIT;
        }
    }

    /**
     * Get count of products that currently have ETIM data assigned
     */
    public function get_assigned_product_count() {
        global $wpdb;

        $table = $wpdb->prefix . 'etim_product_classes';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return 0;
        }

        return (int) $wpdb->get_var("SELECT COUNT(DISTINCT product_id) FROM $table");
    }

    /**
     * Get remaining product slots
     */
    public function get_remaining_product_slots() {
        $limit = $this->get_product_limit();
        if ($limit === PHP_INT_MAX) {
            return PHP_INT_MAX;
        }
        return max(0, $limit - $this->get_assigned_product_count());
    }

    /**
     * Check if a product already has ETIM data assigned
     */
    public function is_product_already_assigned($product_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'etim_product_classes';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }

        return (bool) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE product_id = %d", $product_id)
        );
    }

    /**
     * Check if user can assign ETIM data to a specific product
     * Returns true if product already has data (update) or if under the limit
     */
    public function can_assign_product($product_id) {
        if ($this->is_erp()) {
            return true;
        }

        if ($this->is_product_already_assigned($product_id)) {
            return true;
        }

        return $this->get_assigned_product_count() < $this->get_product_limit();
    }

    // ========================================================================
    // FEATURE ACCESS CHECKS
    // ========================================================================

    /**
     * Can use frontend ETIM filter
     */
    public function can_use_filter() {
        return $this->is_distributor_or_above();
    }

    /**
     * Can import CSV data
     */
    public function can_import() {
        return $this->is_distributor_or_above();
    }

    /**
     * Can export CSV/XML data
     */
    public function can_export() {
        return $this->is_distributor_or_above();
    }

    /**
     * Can use bulk assign feature
     */
    public function can_bulk_assign() {
        return $this->is_distributor_or_above();
    }

    /**
     * Can download JSON files
     */
    public function can_download_json() {
        return $this->is_distributor_or_above();
    }

    /**
     * Can download XML files
     */
    public function can_download_xml() {
        return $this->is_distributor_or_above();
    }

    // ========================================================================
    // BULK LIMITS
    // ========================================================================

    /**
     * Get bulk assignment limit (products per batch)
     */
    public function get_bulk_limit() {
        $plan = $this->get_current_plan();

        switch ($plan) {
            case ETIM_License_Manager::PLAN_ERP:
                return PHP_INT_MAX;
            case ETIM_License_Manager::PLAN_DISTRIBUTOR:
                return self::DISTRIBUTOR_BULK_LIMIT;
            default:
                return 0;
        }
    }

    // ========================================================================
    // UI HELPERS
    // ========================================================================

    /**
     * Get upgrade URL
     */
    public function get_upgrade_url() {
        return site_url( '/checkout/?empty-cart&add-to-cart=20' );
    }

    /**
     * Get direct checkout URL for a specific plan
     */
    public function get_plan_checkout_url( $plan_code = null ) {
        $product_map = [
            ETIM_License_Manager::PLAN_MANUFACTURER => 21,
            ETIM_License_Manager::PLAN_DISTRIBUTOR  => 20,
            ETIM_License_Manager::PLAN_ERP          => 17,
        ];

        if ( $plan_code !== null && isset( $product_map[ $plan_code ] ) ) {
            return site_url( '/checkout/?empty-cart&add-to-cart=' . $product_map[ $plan_code ] );
        }

        // Default to Distributor
        return site_url( '/checkout/?empty-cart&add-to-cart=20' );
    }

    /**
     * Get product limit display text
     */
    public function get_product_limit_display() {
        $limit = $this->get_product_limit();
        if ($limit === PHP_INT_MAX) {
            return __('Unlimited', 'etim-for-woocommerce');
        }
        return number_format_i18n($limit) . ' ' . __('products', 'etim-for-woocommerce');
    }

    /**
     * Get bulk limit display text
     */
    public function get_bulk_limit_display() {
        $limit = $this->get_bulk_limit();
        if ($limit === PHP_INT_MAX) {
            return __('Unlimited', 'etim-for-woocommerce');
        }
        if ($limit === 0) {
            return __('Not available', 'etim-for-woocommerce');
        }
        return sprintf(__('%d products per batch', 'etim-for-woocommerce'), $limit);
    }

    /**
     * Get feature list for current plan (for UI display)
     */
    public function get_plan_features_list() {
        return [
            [
                'name'     => __('ETIM Group & Class Assignment', 'etim-for-woocommerce'),
                'included' => true,
            ],
            [
                'name'     => __('Dynamic Feature Generation', 'etim-for-woocommerce'),
                'included' => true,
            ],
            [
                'name'     => __('Product Limit', 'etim-for-woocommerce'),
                'value'    => $this->get_product_limit_display(),
                'included' => true,
            ],
            [
                'name'     => __('Frontend Faceted Filters', 'etim-for-woocommerce'),
                'included' => $this->can_use_filter(),
            ],
            [
                'name'     => __('CSV Import / Export', 'etim-for-woocommerce'),
                'included' => $this->can_import(),
            ],
            [
                'name'     => __('XML Export', 'etim-for-woocommerce'),
                'included' => $this->can_export(),
            ],
            [
                'name'     => __('JSON Download', 'etim-for-woocommerce'),
                'included' => $this->can_download_json(),
            ],
            [
                'name'     => __('Bulk Assignment', 'etim-for-woocommerce'),
                'value'    => $this->can_bulk_assign() ? $this->get_bulk_limit_display() : __('Not available', 'etim-for-woocommerce'),
                'included' => $this->can_bulk_assign(),
            ],
            [
                'name'     => __('Priority Support', 'etim-for-woocommerce'),
                'included' => $this->is_erp(),
            ],
        ];
    }

    /**
     * Get feature access data array for JS localization
     */
    public function get_js_data($product_id = 0) {
        $license_manager = ETIM_License_Manager::get_instance();

        return [
            'canAssign'      => $product_id ? $this->can_assign_product($product_id) : true,
            'canFilter'      => $this->can_use_filter(),
            'canImport'      => $this->can_import(),
            'canExport'      => $this->can_export(),
            'canBulk'        => $this->can_bulk_assign(),
            'canDownloadJson'=> $this->can_download_json(),
            'canDownloadXml' => $this->can_download_xml(),
            'productLimit'   => $this->get_product_limit() === PHP_INT_MAX ? -1 : $this->get_product_limit(),
            'assignedCount'  => $this->get_assigned_product_count(),
            'remainingSlots' => $this->get_remaining_product_slots() === PHP_INT_MAX ? -1 : $this->get_remaining_product_slots(),
            'bulkLimit'      => $this->get_bulk_limit() === PHP_INT_MAX ? -1 : $this->get_bulk_limit(),
            'upgradeUrl'     => $this->get_upgrade_url(),
            'planName'       => $license_manager->get_plan_name(),
            'planCode'       => $this->get_current_plan(),
        ];
    }
}
