<?php
/**
 * ETIM API Handler Class
 * 
 * Handles authentication and API requests to ETIM International
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ETIM_API Class
 */
class ETIM_API {
    
    /**
     * ETIM Auth URL
     */
    const AUTH_URL = 'https://etimauth.etim-international.com/connect/token';
    
    /**
     * ETIM API Base URL
     */
    const API_BASE_URL = 'https://etimapi.etim-international.com/api/v2.0';
    
    /**
     * Token transient key
     */
    const TOKEN_TRANSIENT_KEY = 'etim_access_token';
    
    /**
     * Token expiry buffer (seconds before actual expiry to refresh)
     */
    const TOKEN_EXPIRY_BUFFER = 300; // 5 minutes
    
    /**
     * Get access token
     * 
     * @param bool $force_refresh Force token refresh
     * @return string|WP_Error Access token or error
     */
    public static function get_access_token($force_refresh = false) {
        // Check for cached token
        if (!$force_refresh) {
            $cached_token = get_transient(self::TOKEN_TRANSIENT_KEY);
            if ($cached_token) {
                return $cached_token;
            }
        }
        
        // Get credentials from options
        $client_id = get_option('etim_client_id', '');
        $client_secret = get_option('etim_client_secret', '');
        $scope = get_option('etim_scope', 'EtimApi');
        
        // Validate credentials
        if (empty($client_id) || empty($client_secret)) {
            return new WP_Error(
                'etim_credentials_missing',
                __('ETIM API credentials are not configured. Please go to ETIM Settings and enter your Client ID and Client Secret.', 'etim-for-woocommerce')
            );
        }
        
        // Request new token
        $response = wp_remote_post(self::AUTH_URL, [
            'timeout' => 30,
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'scope'         => $scope,
            ],
        ]);
        
        // Check for request errors
        if (is_wp_error($response)) {
            return new WP_Error(
                'etim_auth_request_failed',
                sprintf(
                    __('Failed to connect to ETIM authentication server: %s', 'etim-for-woocommerce'),
                    $response->get_error_message()
                )
            );
        }
        
        // Check response code
        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code !== 200) {
            $error_message = isset($body['error_description']) 
                ? $body['error_description'] 
                : (isset($body['error']) ? $body['error'] : __('Unknown authentication error', 'etim-for-woocommerce'));
            
            return new WP_Error(
                'etim_auth_failed',
                sprintf(__('ETIM authentication failed: %s', 'etim-for-woocommerce'), $error_message)
            );
        }
        
        // Extract token
        if (!isset($body['access_token'])) {
            return new WP_Error(
                'etim_token_missing',
                __('Access token not found in authentication response.', 'etim-for-woocommerce')
            );
        }
        
        $access_token = $body['access_token'];
        $expires_in = isset($body['expires_in']) ? intval($body['expires_in']) : 3600;
        
        // Cache token with expiry buffer
        $cache_duration = max(60, $expires_in - self::TOKEN_EXPIRY_BUFFER);
        set_transient(self::TOKEN_TRANSIENT_KEY, $access_token, $cache_duration);
        
        return $access_token;
    }
    
    /**
     * Make authenticated API request
     * 
     * @param string $endpoint API endpoint
     * @param array $body Request body
     * @param string $method HTTP method
     * @return array|WP_Error Response data or error
     */
    public static function request($endpoint, $body = [], $method = 'POST') {
        // Get access token
        $token = self::get_access_token();
        
        if (is_wp_error($token)) {
            return $token;
        }
        
        // Build request URL
        $url = self::API_BASE_URL . '/' . ltrim($endpoint, '/');
        
        // Prepare request arguments
        $args = [
            'timeout' => 30,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ],
        ];
        
        // Always include body for POST requests (even if empty, send {})
        if ($method === 'POST') {
            $args['body'] = wp_json_encode($body ?: new stdClass());
        }
        
        // Log request for debugging (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ETIM API Request - URL: ' . $url);
            error_log('ETIM API Request - Body: ' . wp_json_encode($body));
        }
        
        // Make request
        if ($method === 'GET') {
            $response = wp_remote_get($url, $args);
        } else {
            $args['method'] = $method;
            $response = wp_remote_post($url, $args);
        }
        
        // Check for request errors
        if (is_wp_error($response)) {
            return new WP_Error(
                'etim_api_request_failed',
                sprintf(
                    __('ETIM API request failed: %s', 'etim-for-woocommerce'),
                    $response->get_error_message()
                )
            );
        }
        
        // Log response for debugging (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ETIM API Response - Code: ' . wp_remote_retrieve_response_code($response));
            error_log('ETIM API Response - Body: ' . wp_remote_retrieve_body($response));
        }
        
        // Parse response
        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Handle authentication errors (token may have expired)
        if ($response_code === 401) {
            // Try to refresh token and retry once
            $token = self::get_access_token(true);
            
            if (is_wp_error($token)) {
                return $token;
            }
            
            // Retry request with new token
            $args['headers']['Authorization'] = 'Bearer ' . $token;
            
            if ($method === 'GET') {
                $response = wp_remote_get($url, $args);
            } else {
                $response = wp_remote_post($url, $args);
            }
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);
        }
        
        // Check for API errors
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = isset($body['message']) ? $body['message'] : __('Unknown API error', 'etim-for-woocommerce');
            
            return new WP_Error(
                'etim_api_error',
                sprintf(__('ETIM API error (HTTP %d): %s', 'etim-for-woocommerce'), $response_code, $error_message)
            );
        }
        
        return $body;
    }
    
    /**
     * Search ETIM Groups
     * 
     * @param string $search_string Search term
     * @param int $from Starting index
     * @param int $size Number of results
     * @param string $language_code Language code (default: EN)
     * @return array|WP_Error Groups data or error
     */
    public static function search_groups($search_string = '', $from = 0, $size = 50, $language_code = 'EN') {
        $body = [
            'From'         => $from,
            'Size'         => $size,
            'Languagecode' => $language_code,
            'Include'      => [
                'Descriptions' => true,
                'Translations' => true,
            ],
        ];
        
        if (!empty($search_string)) {
            $body['SearchString'] = $search_string;
        }
        
        return self::request('Group/Search', $body);
    }
    
    /**
     * Search ETIM Classes
     * 
     * @param string $search_string Search term
     * @param string $group_code Filter by group code
     * @param int $from Starting index
     * @param int $size Number of results
     * @param string $language_code Language code (default: EN)
     * @return array|WP_Error Classes data or error
     */
    public static function search_classes($search_string = '', $group_code = '', $from = 0, $size = 50, $language_code = 'EN') {
        $body = [
            'From'         => $from,
            'Size'         => $size,
            'Languagecode' => $language_code,
            'Include'      => [
                'Descriptions' => true,
                'Fields'       => ['Group'],
            ],
        ];
        
        if (!empty($search_string)) {
            $body['SearchString'] = $search_string;
        }
        
        // Add group filter if provided
        if (!empty($group_code)) {
            $body['Filters'] = [
                [
                    'Code'   => 'Group',
                    'Values' => [$group_code],
                ],
            ];
        }
        
        return self::request('Class/Search', $body);
    }
    
    /**
     * Get Class details with features
     * 
     * @param string $class_code ETIM Class code
     * @param string $language_code Language code (default: EN)
     * @return array|WP_Error Class details or error
     */
    public static function get_class_details($class_code, $language_code = 'EN') {
        $body = [
            'Code'         => $class_code,
            'Languagecode' => $language_code,
            'deprecated'   => true,
            'Include'      => [
                'Descriptions'  => true,
                'translations'  => true,
                'Fields'        => ['Group', 'Releases', 'Features'],
            ],
        ];
        
        return self::request('Class/Details', $body);
    }
    
    /**
     * Search ETIM Features
     * 
     * @param string $search_string Search term
     * @param int $from Starting index
     * @param int $size Number of results
     * @param string $language_code Language code (default: EN)
     * @return array|WP_Error Features data or error
     */
    public static function search_features($search_string = '', $from = 0, $size = 50, $language_code = 'EN') {
        $body = [
            'From'         => $from,
            'Size'         => $size,
            'Languagecode' => $language_code,
            'Include'      => [
                'Descriptions' => true,
            ],
        ];
        
        if (!empty($search_string)) {
            $body['SearchString'] = $search_string;
        }
        
        return self::request('Feature/Search', $body);
    }
    
    /**
     * Get Feature details with values
     * 
     * @param string $feature_code ETIM Feature code
     * @param string $language_code Language code (default: EN)
     * @return array|WP_Error Feature details or error
     */
    public static function get_feature_details($feature_code, $language_code = 'EN') {
        $body = [
            'Code'         => $feature_code,
            'Languagecode' => $language_code,
            'Include'      => [
                'Descriptions' => true,
                'Fields'       => ['Values', 'Unit'],
            ],
        ];
        
        return self::request('Feature/Details', $body);
    }
    
    /**
     * Search ETIM Values
     * 
     * @param string $search_string Search term
     * @param int $from Starting index
     * @param int $size Number of results
     * @param string $language_code Language code (default: EN)
     * @return array|WP_Error Values data or error
     */
    public static function search_values($search_string = '', $from = 0, $size = 50, $language_code = 'EN') {
        $body = [
            'From'         => $from,
            'Size'         => $size,
            'Languagecode' => $language_code,
            'Include'      => [
                'Descriptions' => true,
            ],
        ];
        
        if (!empty($search_string)) {
            $body['SearchString'] = $search_string;
        }
        
        return self::request('Value/Search', $body);
    }
    
    /**
     * Test API connection
     * 
     * @return array|WP_Error Test result
     */
    public static function test_connection() {
        // First test authentication
        $token = self::get_access_token(true);
        
        if (is_wp_error($token)) {
            return $token;
        }
        
        // Then try a simple API call
        $result = self::search_groups('', 0, 1);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return [
            'success' => true,
            'message' => __('Successfully connected to ETIM API.', 'etim-for-woocommerce'),
        ];
    }
    
    /**
     * Clear cached token
     */
    public static function clear_token_cache() {
        delete_transient(self::TOKEN_TRANSIENT_KEY);
    }
}
