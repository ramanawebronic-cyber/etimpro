<?php
/**
 * ETIM Admin Settings Class
 * 
 * Handles admin menu and settings page for ETIM credentials
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ETIM_Admin_Settings Class
 */
class ETIM_Admin_Settings {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Settings group name
     */
    const SETTINGS_GROUP = 'etim_settings_group';
    
    /**
     * Option names
     */
    const OPTION_CLIENT_ID     = 'etim_client_id';
    const OPTION_CLIENT_SECRET = 'etim_client_secret';
    const OPTION_SCOPE         = 'etim_scope';
    const OPTION_LANGUAGE      = 'etim_default_language';
    
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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_etim_test_connection', [$this, 'ajax_test_connection']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('ETIM Settings', 'etim-for-woocommerce'),
            __('ETIM Settings', 'etim-for-woocommerce'),
            'manage_woocommerce',
            'etim-settings',
            [$this, 'render_settings_page'],
            'dashicons-tag',
            56
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_CLIENT_ID,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );
        
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_CLIENT_SECRET,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );
        
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_SCOPE,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'EtimApi',
            ]
        );
        
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_LANGUAGE,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'EN',
            ]
        );
        
        // Add settings section
        add_settings_section(
            'etim_api_section',
            __('ETIM API Credentials', 'etim-for-woocommerce'),
            [$this, 'render_section_description'],
            'etim-settings'
        );
        
        // Add settings fields
        add_settings_field(
            self::OPTION_CLIENT_ID,
            __('Client ID', 'etim-for-woocommerce'),
            [$this, 'render_client_id_field'],
            'etim-settings',
            'etim_api_section'
        );
        
        add_settings_field(
            self::OPTION_CLIENT_SECRET,
            __('Client Secret', 'etim-for-woocommerce'),
            [$this, 'render_client_secret_field'],
            'etim-settings',
            'etim_api_section'
        );
        
        add_settings_field(
            self::OPTION_SCOPE,
            __('API Scope', 'etim-for-woocommerce'),
            [$this, 'render_scope_field'],
            'etim-settings',
            'etim_api_section'
        );
        
        add_settings_field(
            self::OPTION_LANGUAGE,
            __('Default Language', 'etim-for-woocommerce'),
            [$this, 'render_language_field'],
            'etim-settings',
            'etim_api_section'
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if ($hook !== 'toplevel_page_etim-settings') {
            return;
        }
        
        wp_enqueue_style(
            'etim-admin-settings',
            ETIM_WC_PLUGIN_URL . 'assets/css/admin-settings.css',
            [],
            ETIM_WC_VERSION
        );
        
        wp_enqueue_script(
            'etim-admin-settings',
            ETIM_WC_PLUGIN_URL . 'assets/js/admin-settings.js',
            ['jquery'],
            ETIM_WC_VERSION,
            true
        );
        
        wp_localize_script('etim-admin-settings', 'etimSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('etim_admin_nonce'),
            'strings' => [
                'testing'    => __('Testing connection...', 'etim-for-woocommerce'),
                'success'    => __('Connection successful!', 'etim-for-woocommerce'),
                'error'      => __('Connection failed:', 'etim-for-woocommerce'),
                'saveFirst'  => __('Please save your credentials first.', 'etim-for-woocommerce'),
            ],
        ]);
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'etim-for-woocommerce'));
        }
        
        ?>
        <div class="wrap etim-settings-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="etim-settings-container">
                <div class="etim-settings-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields(self::SETTINGS_GROUP);
                        do_settings_sections('etim-settings');
                        submit_button(__('Save Settings', 'etim-for-woocommerce'));
                        ?>
                    </form>
                    
                    <div class="etim-test-connection-container">
                        <h2><?php esc_html_e('Test Connection', 'etim-for-woocommerce'); ?></h2>
                        <p><?php esc_html_e('Click the button below to verify your ETIM API credentials.', 'etim-for-woocommerce'); ?></p>
                        <button type="button" class="button button-secondary" id="etim-test-connection">
                            <?php esc_html_e('Test API Connection', 'etim-for-woocommerce'); ?>
                        </button>
                        <div id="etim-test-result" class="etim-test-result"></div>
                    </div>
                </div>
                
                <div class="etim-settings-sidebar">
                    <div class="etim-info-box">
                        <h3><?php esc_html_e('Getting Started', 'etim-for-woocommerce'); ?></h3>
                        <p><?php esc_html_e('To use this plugin, you need ETIM API credentials. You can obtain them from:', 'etim-for-woocommerce'); ?></p>
                        <a href="https://www.etim-international.com/" target="_blank" class="button">
                            <?php esc_html_e('ETIM International', 'etim-for-woocommerce'); ?>
                        </a>
                    </div>
                    
                    <div class="etim-info-box">
                        <h3><?php esc_html_e('How It Works', 'etim-for-woocommerce'); ?></h3>
                        <ol>
                            <li><?php esc_html_e('Enter your ETIM API credentials above', 'etim-for-woocommerce'); ?></li>
                            <li><?php esc_html_e('Save settings and test the connection', 'etim-for-woocommerce'); ?></li>
                            <li><?php esc_html_e('Edit any WooCommerce product', 'etim-for-woocommerce'); ?></li>
                            <li><?php esc_html_e('Use the ETIM Classification meta box to classify your products', 'etim-for-woocommerce'); ?></li>
                        </ol>
                    </div>
                    
                    <div class="etim-info-box">
                        <h3><?php esc_html_e('Need Help?', 'etim-for-woocommerce'); ?></h3>
                        <p><?php esc_html_e('Visit our support page for documentation and assistance.', 'etim-for-woocommerce'); ?></p>
                        <a href="https://webronic.com/support" target="_blank" class="button">
                            <?php esc_html_e('Get Support', 'etim-for-woocommerce'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . esc_html__('Enter your ETIM API credentials below. These are required to fetch ETIM classification data.', 'etim-for-woocommerce') . '</p>';
    }
    
    /**
     * Render Client ID field
     */
    public function render_client_id_field() {
        $value = get_option(self::OPTION_CLIENT_ID, '');
        ?>
        <input 
            type="text" 
            id="<?php echo esc_attr(self::OPTION_CLIENT_ID); ?>" 
            name="<?php echo esc_attr(self::OPTION_CLIENT_ID); ?>" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            autocomplete="off"
        />
        <p class="description"><?php esc_html_e('Your ETIM API Client ID', 'etim-for-woocommerce'); ?></p>
        <?php
    }
    
    /**
     * Render Client Secret field
     */
    public function render_client_secret_field() {
        $value = get_option(self::OPTION_CLIENT_SECRET, '');
        ?>
        <input 
            type="password" 
            id="<?php echo esc_attr(self::OPTION_CLIENT_SECRET); ?>" 
            name="<?php echo esc_attr(self::OPTION_CLIENT_SECRET); ?>" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            autocomplete="new-password"
        />
        <button type="button" class="button button-secondary etim-toggle-secret" data-target="<?php echo esc_attr(self::OPTION_CLIENT_SECRET); ?>">
            <span class="dashicons dashicons-visibility"></span>
        </button>
        <p class="description"><?php esc_html_e('Your ETIM API Client Secret', 'etim-for-woocommerce'); ?></p>
        <?php
    }
    
    /**
     * Render Scope field
     */
    public function render_scope_field() {
        $value = get_option(self::OPTION_SCOPE, 'EtimApi');
        ?>
        <input 
            type="text" 
            id="<?php echo esc_attr(self::OPTION_SCOPE); ?>" 
            name="<?php echo esc_attr(self::OPTION_SCOPE); ?>" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
        />
        <p class="description"><?php esc_html_e('API scope (default: EtimApi). Only change if instructed by ETIM.', 'etim-for-woocommerce'); ?></p>
        <?php
    }
    
    /**
     * Render Language field
     */
    public function render_language_field() {
        $value = get_option(self::OPTION_LANGUAGE, 'EN');
        $languages = [
            'EN' => __('English', 'etim-for-woocommerce'),
            'DE' => __('German', 'etim-for-woocommerce'),
            'FR' => __('French', 'etim-for-woocommerce'),
            'NL' => __('Dutch', 'etim-for-woocommerce'),
            'IT' => __('Italian', 'etim-for-woocommerce'),
            'ES' => __('Spanish', 'etim-for-woocommerce'),
            'SV' => __('Swedish', 'etim-for-woocommerce'),
            'NO' => __('Norwegian', 'etim-for-woocommerce'),
            'DA' => __('Danish', 'etim-for-woocommerce'),
            'FI' => __('Finnish', 'etim-for-woocommerce'),
            'PL' => __('Polish', 'etim-for-woocommerce'),
            'PT' => __('Portuguese', 'etim-for-woocommerce'),
            'CS' => __('Czech', 'etim-for-woocommerce'),
            'HU' => __('Hungarian', 'etim-for-woocommerce'),
        ];
        ?>
        <select 
            id="<?php echo esc_attr(self::OPTION_LANGUAGE); ?>" 
            name="<?php echo esc_attr(self::OPTION_LANGUAGE); ?>"
        >
            <?php foreach ($languages as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($value, $code); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e('Default language for ETIM data', 'etim-for-woocommerce'); ?></p>
        <?php
    }
    
    /**
     * AJAX handler for testing connection
     */
    public function ajax_test_connection() {
        // Verify nonce
        check_ajax_referer('etim_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error([
                'message' => __('You do not have permission to perform this action.', 'etim-for-woocommerce'),
            ]);
        }
        
        // Clear cached token to force fresh authentication
        ETIM_API::clear_token_cache();
        
        // Test connection
        $result = ETIM_API::test_connection();
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }
        
        wp_send_json_success([
            'message' => $result['message'],
        ]);
    }
}
