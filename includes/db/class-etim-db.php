<?php
/**
 * ETIM Database Handler
 * 
 * Handles database operations for ETIM data
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ETIM_DB {
    
    /**
     * Database version
     */
    const DB_VERSION = '1.0';
    
    /**
     * Option key for database version
     */
    const DB_VERSION_OPTION = 'etim_db_version';
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Get instance
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
        add_action('plugins_loaded', [$this, 'check_db_version']);
    }

    
    /**
     * Install database tables
     */
    public function install_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for ETIM groups
        $table_groups = $wpdb->prefix . 'etim_product_groups';
        $sql_groups = "CREATE TABLE IF NOT EXISTS $table_groups (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            group_code VARCHAR(20) NOT NULL,
            group_name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_product_id (product_id),
            INDEX idx_group_code (group_code),
            UNIQUE KEY uniq_product_group (product_id, group_code)
        ) $charset_collate;";
        
        // Table for ETIM classes
        $table_classes = $wpdb->prefix . 'etim_product_classes';
        $sql_classes = "CREATE TABLE IF NOT EXISTS $table_classes (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            class_code VARCHAR(20) NOT NULL,
            class_name VARCHAR(255) NOT NULL,
            group_code VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_product_id (product_id),
            INDEX idx_class_code (class_code),
            INDEX idx_group_code (group_code),
            UNIQUE KEY uniq_product_class (product_id, class_code)
        ) $charset_collate;";
        
        // Table for ETIM features
        $table_features = $wpdb->prefix . 'etim_product_features';
        $sql_features = "CREATE TABLE IF NOT EXISTS $table_features (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            class_code VARCHAR(20) NOT NULL,
            feature_code VARCHAR(20) NOT NULL,
            feature_name VARCHAR(255) NOT NULL,
            feature_value_code VARCHAR(50) DEFAULT NULL,
            feature_value_description TEXT,
            feature_type VARCHAR(50) DEFAULT 'alphanumeric',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_product_id (product_id),
            INDEX idx_class_code (class_code),
            INDEX idx_feature_code (feature_code),
            INDEX idx_feature_value_code (feature_value_code),
            UNIQUE KEY uniq_product_class_feature (product_id, class_code, feature_code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_groups);
        dbDelta($sql_classes);
        dbDelta($sql_features);
        
        // Migrate existing data from old table
        $this->migrate_old_data();
        
        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }
    
    /**
     * Migrate data from old JSON format to new tables
     */
    private function migrate_old_data() {
        global $wpdb;
        
        $old_table = $wpdb->prefix . 'etim_product_features_old';
        $new_groups_table = $wpdb->prefix . 'etim_product_groups';
        $new_classes_table = $wpdb->prefix . 'etim_product_classes';
        $new_features_table = $wpdb->prefix . 'etim_product_features';
        
        // Check if old table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$old_table'") != $old_table) {
            return;
        }
        
        // Get all old data
        $old_data = $wpdb->get_results("SELECT * FROM $old_table", ARRAY_A);
        
        foreach ($old_data as $row) {
            $product_id = $row['product_id'];
            $json_data = json_decode($row['prod_etim_data'], true);
            
            if (!is_array($json_data)) {
                continue;
            }
            
            // Migrate each class
            foreach ($json_data as $class_data) {
                if (!isset($class_data['code']) || !isset($class_data['group']['code'])) {
                    continue;
                }
                
                // Save group
                $group_code = $class_data['group']['code'];
                $group_name = $class_data['group']['description'] ?? $group_code;
                
                $wpdb->replace(
                    $new_groups_table,
                    [
                        'product_id' => $product_id,
                        'group_code' => $group_code,
                        'group_name' => $group_name
                    ],
                    ['%d', '%s', '%s']
                );
                
                // Save class
                $class_code = $class_data['code'];
                $class_name = $class_data['description'] ?? $class_code;
                
                $wpdb->replace(
                    $new_classes_table,
                    [
                        'product_id' => $product_id,
                        'class_code' => $class_code,
                        'class_name' => $class_name,
                        'group_code' => $group_code
                    ],
                    ['%d', '%s', '%s', '%s']
                );
                
                // Save features
                if (isset($class_data['features']) && is_array($class_data['features'])) {
                    foreach ($class_data['features'] as $feature_data) {
                        if (!isset($feature_data['code']) || !isset($feature_data['assignedValue'])) {
                            continue;
                        }
                        
                        $feature_value = $feature_data['assignedValue'];
                        $feature_name = $feature_data['description'] ?? $feature_data['code'];
                        
                        // Determine value code and description
                        $value_code = null;
                        $value_description = null;
                        
                        if (is_array($feature_value)) {
                            $value_code = $feature_value['code'] ?? null;
                            $value_description = $feature_value['description'] ?? null;
                        } else {
                            $value_description = (string) $feature_value;
                        }
                        
                        $wpdb->replace(
                            $new_features_table,
                            [
                                'product_id' => $product_id,
                                'class_code' => $class_code,
                                'feature_code' => $feature_data['code'],
                                'feature_name' => $feature_name,
                                'feature_value_code' => $value_code,
                                'feature_value_description' => $value_description,
                                'feature_type' => $feature_data['type'] ?? 'alphanumeric'
                            ],
                            ['%d', '%s', '%s', '%s', '%s', '%s', '%s']
                        );
                    }
                }
            }
        }
    }
    
    /**
     * Check database version and update if needed
     */
    public function check_db_version() {
        $current_version = get_option(self::DB_VERSION_OPTION, '0');
        
        if (version_compare($current_version, self::DB_VERSION, '<')) {
            $this->install_tables();
        }
    }
    
    /**
     * Deactivate plugin - clean up
     */
    public function deactivate_plugin() {
        // Optionally drop tables on deactivation
        // Uncomment if you want to remove tables on deactivation
        /*
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'etim_product_groups',
            $wpdb->prefix . 'etim_product_classes',
            $wpdb->prefix . 'etim_product_features',
            $wpdb->prefix . 'etim_product_features_old'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option(self::DB_VERSION_OPTION);
        */
    }
    
    /**
     * Save ETIM data for a product
     * 
     * @param int $product_id The product ID
     * @param array $etim_data The ETIM data array
     * @return bool True on success, false on failure
     */
    public function save_etim_data($product_id, $etim_data) {
        global $wpdb;
        
        if (empty($product_id) || !is_array($etim_data)) {
            error_log("ETIM DB: Invalid product_id or etim_data");
            return false;
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Clear existing data for this product
            $this->clear_product_etim_data($product_id);
            
            // Process each class in the data
            foreach ($etim_data as $class_data) {
                if (empty($class_data['code'])) {
                    continue;
                }
                
                $class_code = sanitize_text_field($class_data['code']);
                $class_name = sanitize_text_field($class_data['description'] ?? $class_code);
                
                // Get group info
                $group_code = '';
                $group_name = '';
                
                if (!empty($class_data['group'])) {
                    $group_code = sanitize_text_field($class_data['group']['code'] ?? '');
                    $group_name = sanitize_text_field($class_data['group']['description'] ?? $group_code);
                }
                
                // Save group
                if (!empty($group_code)) {
                    $group_result = $wpdb->replace(
                        $wpdb->prefix . 'etim_product_groups',
                        [
                            'product_id' => $product_id,
                            'group_code' => $group_code,
                            'group_name' => $group_name
                        ],
                        ['%d', '%s', '%s']
                    );
                    
                    if ($group_result === false) {
                        error_log("ETIM DB: Failed to save group - " . $wpdb->last_error);
                        throw new Exception("Failed to save group: " . $wpdb->last_error);
                    }
                }
                
                // Save class
                $class_result = $wpdb->replace(
                    $wpdb->prefix . 'etim_product_classes',
                    [
                        'product_id' => $product_id,
                        'class_code' => $class_code,
                        'class_name' => $class_name,
                        'group_code' => $group_code
                    ],
                    ['%d', '%s', '%s', '%s']
                );
                
                if ($class_result === false) {
                    error_log("ETIM DB: Failed to save class - " . $wpdb->last_error);
                    throw new Exception("Failed to save class: " . $wpdb->last_error);
                }
                
                // Save features
                if (!empty($class_data['features']) && is_array($class_data['features'])) {
                    foreach ($class_data['features'] as $feature) {
                        if (empty($feature['code'])) {
                            continue;
                        }
                        
                        // Skip features without assigned values
                        if (!isset($feature['assignedValue']) || $feature['assignedValue'] === '' || $feature['assignedValue'] === null) {
                            continue;
                        }
                        
                        $feature_code = sanitize_text_field($feature['code']);
                        $feature_name = sanitize_text_field($feature['description'] ?? $feature_code);
                        $feature_type = sanitize_text_field($feature['type'] ?? 'alphanumeric');
                        
                        // Handle different value formats
                        $value_code = null;
                        $value_description = null;
                        
                        $assigned_value = $feature['assignedValue'];
                        
                        if (is_array($assigned_value)) {
                            $value_code = sanitize_text_field($assigned_value['code'] ?? '');
                            $value_description = sanitize_text_field($assigned_value['description'] ?? '');
                        } else {
                            $value_description = sanitize_text_field((string) $assigned_value);
                        }
                        
                        $feature_result = $wpdb->replace(
                            $wpdb->prefix . 'etim_product_features',
                            [
                                'product_id' => $product_id,
                                'class_code' => $class_code,
                                'feature_code' => $feature_code,
                                'feature_name' => $feature_name,
                                'feature_value_code' => $value_code,
                                'feature_value_description' => $value_description,
                                'feature_type' => $feature_type
                            ],
                            ['%d', '%s', '%s', '%s', '%s', '%s', '%s']
                        );
                        
                        if ($feature_result === false) {
                            error_log("ETIM DB: Failed to save feature - " . $wpdb->last_error);
                            throw new Exception("Failed to save feature: " . $wpdb->last_error);
                        }
                    }
                }
            }
            
            
            // Update post meta as well
            update_post_meta($product_id, '_etim_classification', $etim_data);
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            error_log("ETIM DB: Successfully saved data for product $product_id");
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');
            error_log("ETIM DB: Transaction rolled back - " . $e->getMessage());
            return false;
        }
    }

    
    /**
     * Clear all ETIM data for a product
     */
    private function clear_product_etim_data($product_id) {
        global $wpdb;
        
        $tables = [
            'etim_product_groups',
            'etim_product_classes',
            'etim_product_features'
        ];
        
        foreach ($tables as $table) {
            $wpdb->delete(
                $wpdb->prefix . $table,
                ['product_id' => $product_id],
                ['%d']
            );
        }
        
        
        // Clear post meta
        delete_post_meta($product_id, '_etim_classification');
    }
    
    /**
     * Get ETIM data for a product
     */
    public function get_product_etim_data($product_id) {
        global $wpdb;
        
        $data = [];
        
        // Get all classes for this product
        $classes_table = $wpdb->prefix . 'etim_product_classes';
        $groups_table = $wpdb->prefix . 'etim_product_groups';
        $features_table = $wpdb->prefix . 'etim_product_features';
        
        $classes = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, g.group_name 
             FROM $classes_table c
             LEFT JOIN $groups_table g ON c.group_code = g.group_code AND c.product_id = g.product_id
             WHERE c.product_id = %d
             ORDER BY c.class_code",
            $product_id
        ), ARRAY_A);
        
        foreach ($classes as $class) {
            $class_data = [
                'code' => $class['class_code'],
                'description' => $class['class_name'],
                'group' => [
                    'code' => $class['group_code'],
                    'description' => $class['group_name']
                ],
                'features' => []
            ];
            
            // Get features for this class
            $features = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $features_table 
                 WHERE product_id = %d AND class_code = %s
                 ORDER BY feature_code",
                $product_id,
                $class['class_code']
            ), ARRAY_A);
            
            foreach ($features as $feature) {
                $feature_data = [
                    'code' => $feature['feature_code'],
                    'description' => $feature['feature_name'],
                    'type' => $feature['feature_type'],
                    'assignedValue' => $feature['feature_value_description']
                ];
                
                if (!empty($feature['feature_value_code'])) {
                    $feature_data['assignedValue'] = [
                        'code' => $feature['feature_value_code'],
                        'description' => $feature['feature_value_description']
                    ];
                }
                
                $class_data['features'][] = $feature_data;
            }
            
            $data[] = $class_data;
        }
        
        return $data;
    }
    
    /**
     * Get products by ETIM class
     */
    public function get_products_by_class($class_code) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'etim_product_classes';
        
        return $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT product_id FROM $table WHERE class_code = %s",
            $class_code
        ));
    }
    
    /**
     * Get products by ETIM group
     */
    public function get_products_by_group($group_code) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'etim_product_groups';
        
        return $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT product_id FROM $table WHERE group_code = %s",
            $group_code
        ));
    }
    
    /**
     * Get feature statistics
     */
    public function get_feature_statistics($feature_code) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'etim_product_features';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT feature_value_description, COUNT(*) as count 
             FROM $table 
             WHERE feature_code = %s 
             GROUP BY feature_value_description 
             ORDER BY count DESC",
            $feature_code
        ), ARRAY_A);
    }
}