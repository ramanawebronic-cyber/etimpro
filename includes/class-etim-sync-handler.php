<?php
/**
 * ETIM Sync Handler Class
 * 
 * Handles synchronization of ETIM data from an external XML/CSV source
 * into the local plugin database tables.
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ETIM_Sync_Handler {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_etim_sync_data', [$this, 'handle_sync_ajax']);
    }

    /**
     * AJAX action to handle the Sync button
     */
    public function handle_sync_ajax() {
        // Nonce and capability checks
        if (!check_ajax_referer('etim_admin_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed.']);
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'You do not have permission to sync ETIM data.']);
        }

        try {
            // 1. Fetch ETIM Data from Remote Source (Mock API fetch)
            $raw_data = $this->fetch_etim_data('https://api.example.com/etim-export.xml');
            
            // 2. Parse the ETIM data
            $parsed_data = $this->parse_etim_xml($raw_data);
            
            // 3 & 4. Update the plugin database tables & Detect new records
            $sync_results = $this->update_database($parsed_data);
            
            // 5. Save the ETIM version number
            update_option('etim_current_version', sanitize_text_field($parsed_data['version']));
            
            // 6. Record the last sync time
            update_option('etim_last_sync_time', current_time('mysql'));
            
            // 7. Sync status response
            wp_send_json_success([
                'message' => 'Sync completed successfully',
                'stats'   => $sync_results,
                'time'    => date_i18n('h:i A, jS F Y', current_time('timestamp'))
            ]);

        } catch (Exception $e) {
            error_log('ETIM Sync Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Sync failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Fetch remote data
     */
    private function fetch_etim_data($url) {
        // In a real scenario, use wp_remote_get
        /*
        $response = wp_remote_get($url, ['timeout' => 60]);
        if (is_wp_error($response)) {
            throw new Exception("Failed to fetch ETIM data.");
        }
        return wp_remote_retrieve_body($response);
        */
        
        // Mock XML data for parser example
        return '<?xml version="1.0" encoding="UTF-8"?>
        <ETIMData version="9.0">
            <Groups>
                <Group code="EG000001" description="Lighting"/>
            </Groups>
            <Classes>
                <Class code="EC000001" groupCode="EG000001" description="LED Lamp"/>
            </Classes>
            <Features>
                <Feature code="EF000001" description="Color Temperature" type="Numeric"/>
            </Features>
            <FeatureValues>
                <Value featureCode="EF000001" code="EV000001" description="3000K"/>
            </FeatureValues>
        </ETIMData>';
    }

    /**
     * Parse ETIM XML logic
     */
    private function parse_etim_xml($xml_string) {
        $xml = simplexml_load_string($xml_string);
        if (!$xml) {
            throw new Exception("Invalid XML format.");
        }

        $parsed = [
            'version'       => (string)$xml['version'],
            'groups'        => [],
            'classes'       => [],
            'features'      => [],
            'featureValues' => []
        ];

        // Parse Groups
        if (isset($xml->Groups->Group)) {
            foreach ($xml->Groups->Group as $group) {
                $parsed['groups'][] = [
                    'code'        => (string)$group['code'],
                    'description' => (string)$group['description'],
                ];
            }
        }

        // Parse Classes
        if (isset($xml->Classes->Class)) {
            foreach ($xml->Classes->Class as $class) {
                $parsed['classes'][] = [
                    'code'        => (string)$class['code'],
                    'groupCode'   => (string)$class['groupCode'],
                    'description' => (string)$class['description'],
                ];
            }
        }

        // Parse Features
        if (isset($xml->Features->Feature)) {
            foreach ($xml->Features->Feature as $feature) {
                $parsed['features'][] = [
                    'code'        => (string)$feature['code'],
                    'description' => (string)$feature['description'],
                    'type'        => (string)$feature['type'],
                ];
            }
        }

        // Parse Feature Values
        if (isset($xml->FeatureValues->Value)) {
            foreach ($xml->FeatureValues->Value as $value) {
                $parsed['featureValues'][] = [
                    'featureCode' => (string)$value['featureCode'],
                    'code'        => (string)$value['code'],
                    'description' => (string)$value['description'],
                ];
            }
        }

        return $parsed;
    }

    /**
     * Database Update logic (using ON DUPLICATE KEY UPDATE)
     */
    private function update_database($data) {
        global $wpdb;

        $stats = [
            'groups_updated'   => 0,
            'classes_updated'  => 0,
            'features_updated' => 0,
            'values_updated'   => 0
        ];

        // Replace with actual custom table names defined in the plugin
        $table_groups   = $wpdb->prefix . 'etim_groups';
        $table_classes  = $wpdb->prefix . 'etim_classes';
        $table_features = $wpdb->prefix . 'etim_features';
        $table_values   = $wpdb->prefix . 'etim_feature_values';

        // Note: For demonstration. Using replace/insert into the standard WPDB schema.
        // Assuming custom tables have `code` as unique or primary key.

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $wpdb->replace(
                    $table_groups,
                    [
                        'group_code'  => sanitize_text_field($group['code']),
                        'description' => sanitize_text_field($group['description'])
                    ],
                    ['%s', '%s']
                );
                $stats['groups_updated']++;
            }
        }

        if (!empty($data['classes'])) {
            foreach ($data['classes'] as $class) {
                $wpdb->replace(
                    $table_classes,
                    [
                        'class_code'  => sanitize_text_field($class['code']),
                        'group_code'  => sanitize_text_field($class['groupCode']),
                        'description' => sanitize_text_field($class['description'])
                    ],
                    ['%s', '%s', '%s']
                );
                $stats['classes_updated']++;
            }
        }

        if (!empty($data['features'])) {
            foreach ($data['features'] as $feature) {
                $wpdb->replace(
                    $table_features,
                    [
                        'feature_code' => sanitize_text_field($feature['code']),
                        'description'  => sanitize_text_field($feature['description']),
                        'type'         => sanitize_text_field($feature['type'])
                    ],
                    ['%s', '%s', '%s']
                );
                $stats['features_updated']++;
            }
        }

        if (!empty($data['featureValues'])) {
            foreach ($data['featureValues'] as $val) {
                $wpdb->replace(
                    $table_values,
                    [
                        'feature_code' => sanitize_text_field($val['featureCode']),
                        'value_code'   => sanitize_text_field($val['code']),
                        'description'  => sanitize_text_field($val['description'])
                    ],
                    ['%s', '%s', '%s']
                );
                $stats['values_updated']++;
            }
        }

        return $stats;
    }
}

// Initialize handler
ETIM_Sync_Handler::get_instance();
