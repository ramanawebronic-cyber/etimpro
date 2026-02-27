<?php
/**
 * ETIM AJAX Handler Class
 * 
 * Handles AJAX requests for ETIM data fetching
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ETIM_Ajax_Handler Class
 */
class ETIM_Ajax_Handler {
    
    /**
     * Single instance
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
        // AJAX handlers for fetching ETIM data
        add_action('wp_ajax_etim_fetch_groups', [$this, 'fetch_groups']);
        add_action('wp_ajax_etim_fetch_classes', [$this, 'fetch_classes']);
        add_action('wp_ajax_etim_fetch_class_details', [$this, 'fetch_class_details']);
        add_action('wp_ajax_etim_fetch_features', [$this, 'fetch_features']);
        
        // AJAX handlers for saving/loading product ETIM data
        add_action('wp_ajax_etim_load_product_data', [$this, 'load_product_data']);
        add_action('wp_ajax_etim_remove_product_class', [$this, 'remove_product_class']);
        add_action('wp_ajax_etim_get_class_features', [$this, 'fetch_class_details']);
            add_action('wp_ajax_etim_save_product_data', [$this, 'save_product_data']);
            

        

    }
   /**
 * Save ETIM product data (AJAX)
 */
public function save_product_data() {

    $this->verify_ajax_request();

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $json       = $_POST['etim_data'] ?? '';

    if (!$product_id || empty($json)) {
        wp_send_json_error([
            'message' => 'Invalid product data'
        ]);
    }

    $etim_data = json_decode(stripslashes($json), true);

    if (!is_array($etim_data)) {
        wp_send_json_error([
            'message' => 'Invalid ETIM JSON'
        ]);
    }

    $db = ETIM_DB::get_instance();
    $saved = $db->save_etim_data($product_id, $etim_data);

    if ($saved) {
        wp_send_json_success([
            'message' => 'ETIM data saved successfully'
        ]);
    } else {
        wp_send_json_error([
            'message' => 'Failed to save ETIM data'
        ]);
    }
}

    /**
     * Verify AJAX request
     */
    private function verify_ajax_request() {
        // Verify nonce
        if (!check_ajax_referer('etim_ajax_nonce', 'nonce', false)) {
            wp_send_json_error([
                'message' => __('Security check failed. Please refresh the page and try again.', 'etim-for-woocommerce'),
            ]);
        }
        
        // Check permissions
        if (!current_user_can('edit_products')) {
            wp_send_json_error([
                'message' => __('You do not have permission to perform this action.', 'etim-for-woocommerce'),
            ]);
        }
    }



    
    /**
     * Get default language code
     */
    private function get_language_code() {
        return get_option('etim_default_language', 'EN');
    }
    
    /**
     * Fetch ETIM Groups
     */
    public function fetch_groups() {
        $this->verify_ajax_request();
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $language = $this->get_language_code();
        
        // Fetch groups from API
        $result = ETIM_API::search_groups($search, 0, 100, $language);
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }
        
        // Format response - ETIM API returns 'groups' array, not 'items'
        $groups = [];
        $items_key = isset($result['groups']) ? 'groups' : (isset($result['items']) ? 'items' : null);
        
        if ($items_key && is_array($result[$items_key])) {
            foreach ($result[$items_key] as $group) {
                $groups[] = [
                    'code'        => $group['code'] ?? '',
                    'description' => $group['description'] ?? '',
                    'version'     => $group['version'] ?? '',
                ];
            }
        }
        
        wp_send_json_success([
            'groups' => $groups,
            'total'  => $result['total'] ?? $result['totalCount'] ?? count($groups),
        ]);
    }
    
    /**
     * Fetch ETIM Classes
     */
    public function fetch_classes() {
        $this->verify_ajax_request();
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $group_code = isset($_POST['group_code']) ? sanitize_text_field($_POST['group_code']) : '';
        $language = $this->get_language_code();
        
        // Fetch classes from API
        $result = ETIM_API::search_classes($search, $group_code, 0, 100, $language);
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }
        
        // Format response - ETIM API returns 'classes' array, not 'items'
        $classes = [];
        $items_key = isset($result['classes']) ? 'classes' : (isset($result['items']) ? 'items' : null);
        
        if ($items_key && is_array($result[$items_key])) {
            foreach ($result[$items_key] as $class) {
                $classes[] = [
                    'code'        => $class['code'] ?? '',
                    'description' => $class['description'] ?? '',
                    'group'       => $class['group'] ?? null,
                    'version'     => $class['version'] ?? '',
                ];
            }
        }
        
        wp_send_json_success([
            'classes' => $classes,
            'total'   => $result['total'] ?? $result['totalCount'] ?? count($classes),
        ]);
    }
    
    /**
     * Fetch ETIM Class Details (with features)
     */
    public function fetch_class_details() {
        $this->verify_ajax_request();
        
        $class_code = isset($_POST['class_code']) ? sanitize_text_field($_POST['class_code']) : '';
        
        if (empty($class_code)) {
            wp_send_json_error([
                'message' => __('Class code is required.', 'etim-for-woocommerce'),
            ]);
        }
        
        $language = $this->get_language_code();
        
        // Fetch class details from API
        $result = ETIM_API::get_class_details($class_code, $language);
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }
        
        // Format features
        $features = [];
        if (isset($result['features']) && is_array($result['features'])) {
            foreach ($result['features'] as $feature) {
                $formatted_feature = [
                    'code'        => $feature['code'] ?? '',
                    'description' => $feature['description'] ?? '',
                    'type'        => $feature['type'] ?? 'Alphanumeric',
                    'unit'        => $feature['unit'] ?? null,
                    'values'      => [],
                ];
                
                // Add feature values if present
                if (isset($feature['values']) && is_array($feature['values'])) {
                    foreach ($feature['values'] as $value) {
                        $formatted_feature['values'][] = [
                            'code'        => $value['code'] ?? '',
                            'description' => $value['description'] ?? '',
                        ];
                    }
                }
                
                $features[] = $formatted_feature;
            }
        }
        
        wp_send_json_success([
            'code'     => $result['code'] ?? '',
            'features' => $features
        ]);

    }
    
    /**
     * Fetch ETIM Features (standalone search)
     */
    public function fetch_features() {
        $this->verify_ajax_request();
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $language = $this->get_language_code();
        
        // Fetch features from API
        $result = ETIM_API::search_features($search, 0, 50, $language);
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }
        
        // Format response - ETIM API returns 'features' array, not 'items'
        $features = [];
        $items_key = isset($result['features']) ? 'features' : (isset($result['items']) ? 'items' : null);
        
        if ($items_key && is_array($result[$items_key])) {
            foreach ($result[$items_key] as $feature) {
                $features[] = [
                    'code'        => $feature['code'] ?? '',
                    'description' => $feature['description'] ?? '',
                    'type'        => $feature['type'] ?? '',
                ];
            }
        }
        
        wp_send_json_success([
            'features' => $features,
            'total'    => $result['total'] ?? $result['totalCount'] ?? count($features),
        ]);
    }
    

}
