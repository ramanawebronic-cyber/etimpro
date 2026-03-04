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
    const OPTION_ENABLE_FILTER = 'etim_enable_filter';
    const OPTION_FILTER_COLOR  = 'etim_filter_color';
    
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
        add_action('admin_post_etim_simple_export', [$this, 'handle_simple_export']);
        add_action('admin_post_etim_simple_import', [$this, 'handle_simple_import']);
        add_action('admin_post_etim_clear_data', [$this, 'handle_clear_data']);
        add_action('admin_post_etim_xml_export_all', [$this, 'handle_xml_export_all']);
        add_action('admin_post_etim_xml_export_single', [$this, 'handle_xml_export_single']);
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
            self::OPTION_ENABLE_FILTER,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'no',
            ]
        );
        
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_FILTER_COLOR,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '#147A74',
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
        
        $fa = ETIM_Feature_Access::get_instance();
        wp_localize_script('etim-admin-settings', 'etimSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('etim_admin_nonce'),
            'strings' => [
                'testing'    => __('Testing connection...', 'etim-for-woocommerce'),
                'success'    => __('Connection successful!', 'etim-for-woocommerce'),
                'error'      => __('Connection failed:', 'etim-for-woocommerce'),
                'saveFirst'  => __('Please save your credentials first.', 'etim-for-woocommerce'),
            ],
            'featureAccess' => $fa->get_js_data(),
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
        
        $assets_url = ETIM_WC_PLUGIN_URL . 'assets/images/';

        // Feature access for gating
        $feature_access = ETIM_Feature_Access::get_instance();
        $can_filter = $feature_access->can_use_filter();
        $can_import = $feature_access->can_import();
        $can_export = $feature_access->can_export();
        $can_download_json = $feature_access->can_download_json();

        $client_id = get_option(self::OPTION_CLIENT_ID, '');
        $api_key = get_option(self::OPTION_CLIENT_SECRET, '');
        $masked_key = '';
        if (!empty($api_key)) {
            $key_len = strlen($api_key);
            if ($key_len > 8) {
                $masked_key = substr($api_key, 0, 4) . str_repeat('*', $key_len - 8) . substr($api_key, -4);
            } else {
                $masked_key = str_repeat('*', $key_len);
            }
        }
        
        // Fetch dynamic stats from database
        global $wpdb;
        $count_posts = wp_count_posts('product');
        $total_products = isset($count_posts->publish) ? intval($count_posts->publish) : 0;
        
        $table_classes = $wpdb->prefix . 'etim_product_classes';
        $table_features = $wpdb->prefix . 'etim_product_features';
        
        // Count mapped classes (distict classes assigned or total mappings)
        $mapped_classes_count = 0;
        $mapped_attributes_count = 0;
        $products_mapped_count = 0;
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_classes'") === $table_classes) {
            $mapped_classes_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_classes");
            $products_mapped_count = $wpdb->get_var("SELECT COUNT(DISTINCT product_id) FROM $table_classes");
        }
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_features'") === $table_features) {
            $mapped_attributes_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_features");
        }
        
        $pending_mappings = max(0, $total_products - $products_mapped_count);
        
        // Fetch Top ETIM Classes
        $top_classes = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_classes'") === $table_classes) {
            $top_classes = $wpdb->get_results("
                SELECT class_code, COUNT(product_id) as p_count 
                FROM $table_classes 
                GROUP BY class_code 
                ORDER BY p_count DESC 
                LIMIT 10
            ");
        }
        
        $max_class_count = 1;
        foreach ($top_classes as $tc) {
            if ($tc->p_count > $max_class_count) {
                $max_class_count = $tc->p_count;
            }
        }
        
        // Fetch Recent Mappings
        $recent_mappings = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_classes'") === $table_classes) {
            $recent_mappings = $wpdb->get_results("
                SELECT product_id, class_code, updated_at
                FROM $table_classes 
                ORDER BY updated_at DESC 
                LIMIT 5
            ");
        }
        
        ?>
        <div class="wrap etim-app-wrapper">
            <!-- Sidebar -->
            <aside class="etim-sidebar">
                <div class="etim-sidebar-brand">
                    <img src="<?php echo esc_url($assets_url . 'webronic.png'); ?>" alt="Logo" class="etim-icon" />
                    <span>ETIM Integration</span>
                </div>
                <nav class="etim-nav">
                    <a href="#dashboard" class="etim-nav-btn active" data-tab="tab-dashboard">
                        <img src="<?php echo esc_url($assets_url . 'dashboard_icon.png'); ?>" alt="" class="etim-icon" /> Dashboard
                    </a>
                    <a href="#configure" class="etim-nav-btn" data-tab="tab-configure">
                        <img src="<?php echo esc_url($assets_url . 'configure-icon.png'); ?>" alt="" class="etim-icon" /> Configure ETIM
                    </a>
                    <a href="#license" class="etim-nav-btn" data-tab="tab-license">
                        <img src="<?php echo esc_url($assets_url . 'license.png'); ?>" alt="" class="etim-icon" /> License Key
                    </a>
                </nav>
            </aside>
            
            <main class="etim-main">
                <!-- DASHBOARD TAB -->
                <section id="tab-dashboard" class="etim-tab active">
                    <div class="etim-tab-header">
                        <h2><img src="<?php echo esc_url($assets_url . 'dashboard_icon.png'); ?>" alt="" class="etim-icon" /> Dashboard</h2>
                    </div>



                    <div class="etim-bento-grid">
                        <div class="etim-panel etim-account">
                            <h4><img src="<?php echo esc_url($assets_url . 'account-icon.png'); ?>" alt="" class="etim-icon" /> <strong>My Account</strong></h4>
                            <div class="etim-account-grid">
                                <span class="etim-label">Client ID:</span>
                                <span class="etim-value"><?php echo esc_html($client_id ?: 'Not Configured'); ?></span>
                                <span class="etim-label">Plugin Status:</span>
                                <span class="etim-value etim-text-green">Active</span>

                                <span class="etim-label">Client Secret:</span>
                                <span class="etim-value"><?php echo esc_html($masked_key ?: 'Not Configured'); ?></span>
                                <span class="etim-label">Last Synced:</span>
                                <span class="etim-value etim-text-green">10:00 AM, <?php echo date('jS F Y'); ?></span>
                            </div>
                        </div>

                        <!-- 4 Stat Cards Box -->
                        <div class="etim-stat-cards">
                            <div class="etim-panel etim-stat">
                                <div>
                                    <h5>Total Products</h5>
                                    <div class="etim-stat-val"><?php echo number_format_i18n($total_products); ?></div>
                                </div>
                                <img class="etim-stat-ico" src="<?php echo esc_url($assets_url . 'total.png'); ?>" alt="Products" />
                            </div>
                            <div class="etim-panel etim-stat">
                                <div>
                                    <h5>Mapped Classes</h5>
                                    <div class="etim-stat-val"><?php echo number_format_i18n($mapped_classes_count); ?></div>
                                </div>
                                <img class="etim-stat-ico" src="<?php echo esc_url($assets_url . 'map_class.png'); ?>" alt="Classes" />
                            </div>
                            <div class="etim-panel etim-stat">
                                <div>
                                    <h5>Mapped Attributes</h5>
                                    <div class="etim-stat-val"><?php echo number_format_i18n($mapped_attributes_count); ?></div>
                                </div>
                                <img class="etim-stat-ico" src="<?php echo esc_url($assets_url . 'map_attribute.png'); ?>" alt="Attributes" />
                            </div>
                            <div class="etim-panel etim-stat">
                                <div>
                                    <h5>Pending Mappings</h5>
                                    <div class="etim-stat-val" style="color:#eab308;"><?php echo number_format_i18n($pending_mappings); ?></div>
                                </div>
                                <img class="etim-stat-ico" src="<?php echo esc_url($assets_url . 'pending.png'); ?>" alt="Alert" />
                            </div>
                        </div>

                        <!-- Sync Summary -->
                        <div class="etim-panel">
                            <h4><img src="<?php echo esc_url($assets_url . 'sync.png'); ?>" alt="" class="etim-icon" /> ETIM Synchronization Summary</h4>
                            <div class="etim-sync-flex">
                                <div class="etim-sync-details">
                                    <p><strong>Recent Sync:</strong> 10:00 AM, <?php echo date('jS F Y'); ?></p>
                                    <p><strong>Products Synced:</strong> <?php echo number_format_i18n($total_products); ?> / <?php echo number_format_i18n($total_products); ?> (100%)</p>
                                    <p><strong>Sync Errors:</strong> 0</p>
                                </div>
                                <div class="etim-sync-chart-placeholder">
                                    <svg viewBox="0 0 200 100" class="etim-line-chart" preserveAspectRatio="none"><path fill="rgba(72,136,232,0.1)" stroke="#4888E8" stroke-width="2" d="M0 80 Q 20 100, 40 60 T 80 80 T 130 30 T 170 80 L 200 80 L 200 100 L 0 100 Z"/><circle cx="40" cy="60" r="3" fill="#4888E8"/><circle cx="80" cy="80" r="3" fill="#4888E8"/><circle cx="130" cy="30" r="3" fill="#4888E8"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Data Quality -->
                        <div class="etim-panel">
                            <h4><img src="<?php echo esc_url($assets_url . 'account-icon.png'); ?>" alt="" class="etim-icon" /> Data Quality Assessment</h4>
                            <div class="etim-progress-item">
                                <div class="etim-pi-label">Products with Complete ETIM Data: <?php echo intval($products_mapped_count); ?> / <?php echo intval($total_products); ?> (<?php echo $total_products > 0 ? round(($products_mapped_count/$total_products)*100) : 0; ?>%)</div>
                                <div class="etim-pi-bar"><div class="etim-pi-fill" style="width:<?php echo $total_products > 0 ? round(($products_mapped_count/$total_products)*100) : 0; ?>%;"></div></div>
                            </div>
                            <div class="etim-progress-item">
                                <div class="etim-pi-label">Complete Images: <?php echo intval($total_products); ?> / <?php echo intval($total_products); ?></div>
                                <div class="etim-pi-bar"><div class="etim-pi-fill" style="width:100%;"></div></div>
                            </div>
                            <div class="etim-progress-item">
                                <div class="etim-pi-label">Complete Features: <?php echo intval($mapped_classes_count); ?> / <?php echo intval($total_products); ?></div>
                                <div class="etim-pi-bar"><div class="etim-pi-fill" style="width:<?php echo $total_products > 0 ? round(($mapped_classes_count/$total_products)*100) : 0; ?>%;"></div></div>
                            </div>
                        </div>

                        <!-- Top Classes Chart -->
                        <div class="etim-panel etim-chart">
                            <h4><img src="<?php echo esc_url($assets_url . 'map_class.png'); ?>" alt="" class="etim-icon" /> Top ETIM Classes Assigne by you</h4>
                            <div class="etim-top-classes-chart">
                                <?php 
                                if (!empty($top_classes)) {
                                    foreach ($top_classes as $index => $tc) {
                                        if ($index >= 10) break;
                                        $height_pct = ($tc->p_count / $max_class_count) * 100;
                                        if ($height_pct > 0 && $height_pct < 5) $height_pct = 5;
                                        ?>
                                        <div class="etim-tcc-col">
                                            <div class="etim-tcc-val"><?php echo intval($tc->p_count); ?></div>
                                            <div class="etim-tcc-bar-wrap" title="<?php echo esc_attr($tc->class_code . ': ' . $tc->p_count . ' products mapped'); ?>">
                                                <div class="etim-tcc-bar" style="height: <?php echo esc_attr($height_pct); ?>%;"></div>
                                            </div>
                                            <div class="etim-tcc-label-wrap">
                                                <div class="etim-tcc-label"><?php echo esc_html($tc->class_code); ?></div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo '<p style="text-align:center; width: 100%; color:#94a3b8;">No data yet.</p>';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="etim-panel">
                            <h4><img src="<?php echo esc_url($assets_url . 'configure-icon.png'); ?>" alt="" class="etim-icon" /> Recent Mapping Activity</h4>
                            <ul class="etim-activity-list">
                                <?php 
                                if (!empty($recent_mappings)) {
                                    $etim_db = ETIM_DB::get_instance();
                                    foreach ($recent_mappings as $mapping) {
                                        $product = wc_get_product($mapping->product_id);
                                        $product_name = $product ? $product->get_name() : 'Unknown Product';
                                        
                                        // The updated_at might be in UTC from DB. Get standard local representation
                                        $time_formatted = date_i18n('h:i A \o\n jS M Y', strtotime(get_date_from_gmt($mapping->updated_at)));
                                        
                                        // Generate JSON data URL
                                        $full_data = $etim_db->get_product_etim_data($mapping->product_id);
                                        // Base64 encoding helps avoid issues with quotes or newlines in the data URI
                                        $json_data = wp_json_encode($full_data);
                                        $data_uri = 'data:application/json;charset=utf-8;base64,' . base64_encode($json_data);
                                        
                                        ?>
                                        <li style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                                            <div style="flex:1;">
                                                <strong style="color: #0f172a;"><?php echo esc_html($time_formatted); ?>:</strong> 
                                                Product <span style="font-weight: 500;">"<?php echo esc_html($product_name); ?>"</span> (ID: <?php echo intval($mapping->product_id); ?>) 
                                                mapped directly to class <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-family: monospace; font-size: 11px;"><?php echo esc_html($mapping->class_code); ?></span>
                                            </div>
                                            <?php if ($can_download_json): ?>
                                            <a href="<?php echo esc_attr($data_uri); ?>" download="etim_mapping_product_<?php echo intval($mapping->product_id); ?>.json" class="etim-btn-blue-outline" style="padding: 4px 10px; font-size: 11px; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                Download JSON
                                            </a>
                                            <?php else: ?>
                                            <span class="etim-btn-blue-outline" style="padding: 4px 10px; font-size: 11px; white-space: nowrap; flex-shrink: 0; opacity:0.5; cursor:not-allowed; display:inline-flex; align-items:center; gap:4px;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                                <span class="etim-pro-badge">PRO</span>
                                            </span>
                                            <?php endif; ?>
                                        </li>
                                        <?php
                                    }
                                } else {
                                    echo '<li>No recent activity logged yet.</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- CONFIGURE ETIM TAB -->
                <section id="tab-configure" class="etim-tab">
                    <div class="etim-tab-header">
                        <h2><img src="<?php echo esc_url($assets_url . 'configure-icon.png'); ?>" alt="" class="etim-icon" /> Configure ETIM</h2>
                        <p>This module provides an interface to configure ETIM standards within your WordPress plugin</p>
                    </div>

                    <div class="etim-panel etim-form-panel">
                        <form method="post" action="options.php" class="etim-form">
                            <?php settings_fields(self::SETTINGS_GROUP); ?>
                            
                            <!-- Client ID -->
                            <div class="etim-f-row">
                                <div class="etim-f-left">
                                    <label>Client ID</label>
                                </div>
                                <div class="etim-f-right">
                                    <input type="text" name="<?php echo esc_attr(self::OPTION_CLIENT_ID); ?>" value="<?php echo esc_attr($client_id); ?>" placeholder="Johndoe_1234" />
                                </div>
                            </div>
                            
                            <!-- Client Secret -->
                            <div class="etim-f-row">
                                <div class="etim-f-left">
                                    <label>Client Secret</label>
                                </div>
                                <div class="etim-f-right">
                                    <div class="etim-pwd-field">
                                        <input type="password" id="etim-api-key" name="<?php echo esc_attr(self::OPTION_CLIENT_SECRET); ?>" value="<?php echo esc_attr($api_key); ?>" />
                                        <button type="button" class="etim-toggle-pwd" onclick="var p = document.getElementById('etim-api-key'); var img = this.querySelector('img'); if(p.type==='password'){p.type='text'; img.src='<?php echo esc_url($assets_url . 'hide.png'); ?>';}else{p.type='password'; img.src='<?php echo esc_url($assets_url . 'open.png'); ?>';}"><img src="<?php echo esc_url($assets_url . 'open.png'); ?>" style="width:20px; vertical-align:middle;" alt="Toggle" /></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Sync Mock -->
                            <div class="etim-f-row etim-flex-row">
                                <div class="etim-f-left">
                                    <label>Sync</label>
                                    <p>Automatically synchronize product data with the remote ETIM database.</p>
                                </div>
                                <div class="etim-f-right" style="justify-content: flex-end;">
                                    <button type="button" class="etim-circular-btn"><img src="<?php echo esc_url($assets_url . 'sync.png'); ?>" style="width: 18px; height: 18px;" alt="Sync" class="etim-icon" /></button>
                                </div>
                            </div>

                            <!-- Filter -->
                            <div class="etim-f-row etim-flex-row <?php echo !$can_filter ? 'etim-pro-locked' : ''; ?>">
                                <div class="etim-f-left">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                        <label style="margin: 0;">Filter</label>
                                        <?php if (!$can_filter): ?><span class="etim-pro-badge">PRO</span><?php endif; ?>
                                    </div>
                                    <p>Enable faceted filtering for mapped ETIM classes on your store frontend.</p>
                                </div>
                                <div class="etim-f-right" style="justify-content: flex-end; display: flex; align-items: center; gap: 8px;">
                                    <label class="etim-switch" style="margin-right: 15px;">
                                        <input type="hidden" name="<?php echo esc_attr(self::OPTION_ENABLE_FILTER); ?>" value="no">
                                        <input type="checkbox" name="<?php echo esc_attr(self::OPTION_ENABLE_FILTER); ?>" value="yes" <?php checked(get_option(self::OPTION_ENABLE_FILTER, 'no'), 'yes'); ?> <?php echo !$can_filter ? 'disabled' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <input type="text" id="etim-filter-color-text" value="<?php echo esc_attr(get_option(self::OPTION_FILTER_COLOR, '#147A74')); ?>" placeholder="#147A74" style="width: 90px; height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px; font-family: monospace;" <?php echo !$can_filter ? 'disabled' : ''; ?> />
                                    <input type="color" id="etim-filter-color-picker" title="Filter Highlight Color" name="<?php echo esc_attr(self::OPTION_FILTER_COLOR); ?>" value="<?php echo esc_attr(get_option(self::OPTION_FILTER_COLOR, '#147A74')); ?>" style="width: 36px; height: 36px; padding: 0; cursor: pointer; border: none; background: transparent; border-radius: 50%; clip-path: circle(50%);" <?php echo !$can_filter ? 'disabled' : ''; ?> />
                                </div>
                            </div>

                            <!-- Data Sheet Mock -->
                            <div class="etim-f-row etim-flex-row">
                                <div class="etim-f-left">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                        <label style="margin: 0;">Data Sheet</label>
                                        <span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Upcoming</span>
                                    </div>
                                    <p>Generate downloadable PDF data sheets for all your categorized products.</p>
                                </div>
                                <div class="etim-f-right" style="justify-content: flex-end;">
                                    <label class="etim-switch" style="opacity: 0.5; cursor: not-allowed;"><input type="checkbox" disabled><span class="slider" style="cursor: not-allowed;"></span></label>
                                </div>
                            </div>
                            
                            <!-- Import Data -->
                            <div class="etim-f-row etim-flex-row <?php echo !$can_import ? 'etim-pro-locked' : ''; ?>">
                                <div class="etim-f-left">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                        <label style="margin: 0;">Import Data</label>
                                        <?php if (!$can_import): ?><span class="etim-pro-badge">PRO</span><?php endif; ?>
                                    </div>
                                    <p>Upload a structured CSV file to bulk assign ETIM classes and features to products.</p>
                                </div>
                                <div class="etim-f-right" style="justify-content: flex-end;">
                                    <?php if ($can_import): ?>
                                    <button type="button" class="etim-btn-blue-outline etim-w-auto" onclick="document.getElementById('real-import-file').click();">Upload CSV File</button>
                                    <?php else: ?>
                                    <button type="button" class="etim-btn-blue-outline etim-w-auto" disabled style="opacity:0.5;cursor:not-allowed;">Upload CSV File</button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Export Data -->
                            <div class="etim-f-row etim-flex-row <?php echo !$can_export ? 'etim-pro-locked' : ''; ?>">
                                <div class="etim-f-left">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                        <label style="margin: 0;">Export Data</label>
                                        <?php if (!$can_export): ?><span class="etim-pro-badge">PRO</span><?php endif; ?>
                                    </div>
                                    <p>Download assigned ETIM product configurations to CSV or XML format.</p>
                                </div>
                                <div class="etim-f-right" style="justify-content: flex-end; gap: 8px; display: flex; flex-wrap: wrap;">
                                    <?php if ($can_export): ?>
                                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=etim_simple_export')); ?>" class="etim-btn-blue etim-w-auto" style="text-decoration:none;">Download CSV</a>
                                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=etim_xml_export_all')); ?>" class="etim-btn-blue-outline etim-w-auto" style="text-decoration:none;">Download XML</a>
                                    <?php else: ?>
                                    <span class="etim-btn-blue etim-w-auto" style="opacity:0.5;cursor:not-allowed;">Download CSV</span>
                                    <span class="etim-btn-blue-outline etim-w-auto" style="opacity:0.5;cursor:not-allowed;">Download XML</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Clear Data -->
                            <div class="etim-f-row etim-flex-row">
                                <div class="etim-f-left">
                                    <label style="color: #ef4444;">Clear ETIM Data</label>
                                    <p>Delete all assigned ETIM data mappings from all products in your store. This cannot be undone.</p>
                                </div>
                                <div class="etim-f-right" style="justify-content: flex-end;">
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=etim_clear_data'), 'etim_clear_data')); ?>" class="etim-btn-light" style="color:#ef4444; border-color:#efacac; background:#fef2f2; text-decoration:none;" onclick="return confirm('Are you sure you want to delete ALL ETIM data from your store? This action cannot be reversed.');">Clear All Data</a>
                                </div>
                            </div>
                            
                            <!-- Default Language -->
                                <!-- Language naturally inherited from WordPress Locale -->
                            <input type="hidden" name="<?php echo esc_attr(self::OPTION_SCOPE); ?>" value="<?php echo esc_attr(get_option(self::OPTION_SCOPE, 'EtimApi')); ?>" />



                            <!-- Actions -->
                            <div class="etim-f-actions">
                                <div><button type="button" class="etim-btn-light">Cancel</button></div>
                                <div><button type="submit" class="etim-btn-blue" style="margin-left: 12px;">Save</button></div>
                            </div>
                        </form>

                        <form id="etim_import_form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="display:none;">
                            <?php wp_nonce_field('etim_simple_import_nonce'); ?>
                            <input type="hidden" name="action" value="etim_simple_import">
                            <input type="file" id="real-import-file" name="import_file" accept=".csv" onchange="if(this.value){ document.getElementById('etim_import_form').submit(); }">
                        </form>
                    </div>
                </section>

                <!-- LICENSE KEY TAB -->
                <section id="tab-license" class="etim-tab">
                    <?php
                    $license_manager = ETIM_License_Manager::get_instance();
                    $lic_info = $license_manager->get_license_info();
                    $is_active = ($lic_info['status'] === 'active');
                    $current_plan = $is_active ? $lic_info['plan_name'] : 'Free';
                    $plan_code = $feature_access->get_current_plan();
                    $product_count = $feature_access->get_assigned_product_count();
                    $product_limit = $feature_access->get_product_limit();
                    $product_limit_display = ($product_limit === PHP_INT_MAX) ? '&infin;' : number_format_i18n($product_limit);
                    $product_pct = ($product_limit !== PHP_INT_MAX && $product_limit > 0) ? min(100, round(($product_count / $product_limit) * 100)) : 0;
                    $features_list = $feature_access->get_plan_features_list();
                    $is_auto_expired = get_option('etim_lic_auto_expired', false);
                    ?>

                    <!-- Header -->
                    <div class="etim-lk-header">
                        <div class="etim-lk-header-icon">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke="#4888E8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div style="flex:1;">
                            <h2 class="etim-lk-title">License Key</h2>
                            <p class="etim-lk-subtitle">Activate your License and Manage your ETIM Subscription</p>
                        </div>
                        <div>
                            <?php if ($is_active): ?>
                                <span class="etim-lk-status-pill etim-lk-status-active">Active</span>
                            <?php elseif ($is_auto_expired): ?>
                                <span class="etim-lk-status-pill etim-lk-status-expired">Expired</span>
                            <?php else: ?>
                                <span class="etim-lk-status-pill etim-lk-status-free">Free</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($is_active): ?>
                    <!-- Active License Details -->
                    <div class="etim-lk-card" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div class="etim-lk-card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="background: #e0e7ff; padding: 12px; border-radius: 12px;">
                                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#4888E8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <div>
                                    <h3 class="etim-lk-card-title" style="font-size: 20px; color: #1e293b; margin-bottom: 4px;"><?php echo esc_html($lic_info['plan_name']); ?> Plan</h3>
                                    <p class="etim-lk-card-desc" style="margin: 0;">Valid until <?php echo esc_html($lic_info['expiry_formatted']); ?></p>
                                </div>
                            </div>
                            <span class="etim-lk-status-pill etim-lk-status-active" style="background: #10b981; color: white;">Active</span>
                        </div>

                        <div class="etim-lk-detail-grid" style="gap: 20px;">
                            <div class="etim-lk-detail-item" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                                <span class="etim-lk-detail-label">License Key</span>
                                <span class="etim-lk-detail-value" id="etim-lic-masked-key" style="font-family: monospace; color: #4888E8; font-size: 16px;"><?php echo esc_html($lic_info['masked_key']); ?></span>
                            </div>
                            <div class="etim-lk-detail-item" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                                <span class="etim-lk-detail-label">Status</span>
                                <span class="etim-lk-detail-value" style="color:#10b981;font-weight:700; font-size: 16px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="vertical-align:-4px;margin-right:4px;"><circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="2"/><path d="M8 12l3 3 5-5" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Active
                                </span>
                            </div>
                        </div>

                        <!-- Product Usage -->
                        <div style="margin-top:24px;padding: 24px;background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <span style="font-size:14px;font-weight:700;color:#1e293b;">Product Usage</span>
                                <span style="font-size:14px;font-weight:700;color:#475569;"><?php echo number_format_i18n($product_count); ?> <span style="font-weight:400; color:#94a3b8;">/ <?php echo $product_limit_display; ?></span></span>
                            </div>
                            <div class="etim-pi-bar" style="height:10px; background: #e2e8f0; border-radius: 5px;">
                                <div class="etim-pi-fill" style="width:<?php echo esc_attr($product_pct); ?>%; border-radius: 5px; background: <?php echo $product_pct >= 90 ? '#ef4444' : 'linear-gradient(90deg, #4888E8, #3b82f6)'; ?>;"></div>
                            </div>
                            <?php if ($product_limit !== PHP_INT_MAX && $product_pct >= 80): ?>
                            <p style="margin:12px 0 0;font-size:13px;color:#ef4444; font-weight: 500;">You're approaching your product limit. <a href="<?php echo esc_url($feature_access->get_upgrade_url()); ?>" target="_blank" style="color:#4888E8;text-decoration:underline;">Upgrade</a> for more capacity.</p>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top:24px; text-align: right;">
                            <button type="button" class="etim-btn-danger" id="etim-deactivate-license" style="background: transparent;">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="margin-right:6px;"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Deactivate License
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Activation Form -->
                    <div class="etim-lk-card">
                        <h3 class="etim-lk-card-title">Enter Your License Key</h3>
                        <p class="etim-lk-card-desc">Enter your License Key below to Activate the Plugin and Unlock ETIM Features</p>
                        <div class="etim-lk-input-row">
                            <input type="text" id="etim-license-key-input" class="etim-lk-input" placeholder="XXXX-XXXX-XXXX-XXXX" autocomplete="off" />
                        </div>
                        <div style="margin-top:16px;">
                            <button type="button" class="etim-lk-btn-activate" id="etim-activate-license">Activate License</button>
                        </div>
                        <div id="etim-lic-message" class="etim-lic-message" style="display:none;"></div>

                        <?php if (!$is_active): ?>
                        <!-- Free plan product usage -->
                        <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:600;color:#0f172a;">Product Usage (Free Plan)</span>
                                <span style="font-size:13px;font-weight:600;color:#475569;"><?php echo number_format_i18n($product_count); ?> / <?php echo $product_limit_display; ?></span>
                            </div>
                            <div class="etim-pi-bar" style="height:8px;">
                                <div class="etim-pi-fill" style="width:<?php echo esc_attr($product_pct); ?>%;<?php echo $product_pct >= 90 ? 'background:#ef4444;' : ''; ?>"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Feature Access Table -->
                    <div class="etim-lk-card" style="margin-bottom:28px;">
                        <h3 class="etim-lk-card-title" style="margin-bottom:20px; font-size: 18px;">Your Plan Features</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                            <?php foreach ($features_list as $feature): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 1px solid <?php echo $feature['included'] ? '#bfdbfe' : '#e2e8f0'; ?>; border-radius: 8px; background: <?php echo $feature['included'] ? '#eff6ff' : '#f8fafc'; ?>;">
                                <span style="font-size: 14px; font-weight: 600; color: <?php echo $feature['included'] ? '#1e3a8a' : '#64748b'; ?>;"><?php echo esc_html($feature['name']); ?></span>
                                <div>
                                    <?php if ($feature['included']): ?>
                                        <?php if (isset($feature['value'])): ?>
                                            <span style="background: #4888E8; color: white; padding: 4px 10px; border-radius: 12px; font-weight: 700; font-size: 12px;"><?php echo esc_html($feature['value']); ?></span>
                                        <?php else: ?>
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#4888E8"/><path d="M8 12l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if (isset($feature['value'])): ?>
                                            <span style="color:#94a3b8;font-weight:600;font-size:13px;"><?php echo esc_html($feature['value']); ?></span>
                                        <?php else: ?>
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#e2e8f0"/><rect x="5" y="11" width="14" height="11" rx="2" fill="#94a3b8" transform="scale(0.6) translate(7.5, 2)"/><path d="M9.5 11V9a2.5 2.5 0 015 0v2" stroke="#94a3b8" stroke-width="1.2" stroke-linecap="round"/></svg>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Available Plans -->
                    <div class="etim-lk-plans-section">
                        <h3 class="etim-lk-plans-title">Available Plans</h3>
                        <p class="etim-lk-plans-subtitle">Choose the plan that best fits your ETIM integration needs</p>
                        <div class="etim-lk-plans-grid">
                            <!-- Manufacturer Plan -->
                            <div class="etim-lk-plan-card <?php echo ($plan_code <= ETIM_License_Manager::PLAN_MANUFACTURER && !$is_active) || ($is_active && $plan_code === ETIM_License_Manager::PLAN_MANUFACTURER) ? 'etim-lk-plan-active' : ''; ?>">
                                <div class="etim-lk-plan-top">
                                    <img src="<?php echo esc_url($assets_url . 'free.png'); ?>" alt="Manufacturer" class="etim-lk-plan-img" />
                                    <h4 class="etim-lk-plan-name">Manufacturer</h4>
                                    <div class="etim-lk-plan-price">
                                        <span class="etim-lk-price-amount">$299</span>
                                        <span class="etim-lk-price-period">/year</span>
                                    </div>
                                    <p class="etim-lk-plan-desc">Perfect for manufacturers getting started with ETIM classification</p>
                                </div>
                                <ul class="etim-lk-feature-list">
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> ETIM Group & Class Assignment</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Dynamic Feature Generation</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Up to 5 Products</li>
                                </ul>
                                <div class="etim-lk-plan-btn-wrap">
                                    <?php if (($plan_code <= ETIM_License_Manager::PLAN_MANUFACTURER && !$is_active) || ($is_active && $plan_code === ETIM_License_Manager::PLAN_MANUFACTURER)): ?>
                                        <span class="etim-lk-btn-current">Current Plan</span>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url($feature_access->get_plan_checkout_url( ETIM_License_Manager::PLAN_MANUFACTURER )); ?>" target="_blank" class="etim-lk-btn-upgrade">Get Started</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Distributor Plan -->
                            <div class="etim-lk-plan-card <?php echo ($is_active && $plan_code === ETIM_License_Manager::PLAN_DISTRIBUTOR) ? 'etim-lk-plan-active' : ''; ?>" style="position:relative;">
                                <?php if (!$is_active || $plan_code < ETIM_License_Manager::PLAN_DISTRIBUTOR): ?>
                                <div class="etim-lk-popular-badge">Popular</div>
                                <?php endif; ?>
                                <div class="etim-lk-plan-top">
                                    <img src="<?php echo esc_url($assets_url . 'standard.png'); ?>" alt="Distributor" class="etim-lk-plan-img" />
                                    <h4 class="etim-lk-plan-name">Distributor</h4>
                                    <div class="etim-lk-plan-price">
                                        <span class="etim-lk-price-amount">$499</span>
                                        <span class="etim-lk-price-period">/year</span>
                                    </div>
                                    <p class="etim-lk-plan-desc">Ideal for distributors and wholesalers managing product catalogs</p>
                                </div>
                                <ul class="etim-lk-feature-list">
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Everything in Manufacturer</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Up to 50 Products</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> ETIM Faceted Filters</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> CSV/XML Import & Export</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Bulk Assignment (10/batch)</li>
                                </ul>
                                <div class="etim-lk-plan-btn-wrap">
                                    <?php if ($is_active && $plan_code === ETIM_License_Manager::PLAN_DISTRIBUTOR): ?>
                                        <span class="etim-lk-btn-current">Current Plan</span>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url($feature_access->get_plan_checkout_url( ETIM_License_Manager::PLAN_DISTRIBUTOR )); ?>" target="_blank" class="etim-lk-btn-upgrade">Upgrade Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Agency / ERP Plan -->
                            <div class="etim-lk-plan-card <?php echo ($is_active && $plan_code === ETIM_License_Manager::PLAN_ERP) ? 'etim-lk-plan-active' : ''; ?>">
                                <div class="etim-lk-plan-top">
                                    <img src="<?php echo esc_url($assets_url . 'pro.png'); ?>" alt="WooCommerce Agency" class="etim-lk-plan-img" />
                                    <h4 class="etim-lk-plan-name">WooCommerce Agency</h4>
                                    <div class="etim-lk-plan-price">
                                        <span class="etim-lk-price-amount">$999</span>
                                        <span class="etim-lk-price-period">/year</span>
                                    </div>
                                    <p class="etim-lk-plan-desc">Unlimited access for agencies managing multiple WooCommerce stores</p>
                                </div>
                                <ul class="etim-lk-feature-list">
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Everything in Distributor</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Unlimited Products</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Unlimited Bulk Mapping</li>
                                    <li><img src="<?php echo esc_url($assets_url . 'tick.png'); ?>" alt="" class="etim-lk-tick" /> Priority Support</li>
                                </ul>
                                <div class="etim-lk-plan-btn-wrap">
                                    <?php if ($is_active && $plan_code === ETIM_License_Manager::PLAN_ERP): ?>
                                        <span class="etim-lk-btn-current">Current Plan</span>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url($feature_access->get_plan_checkout_url( ETIM_License_Manager::PLAN_ERP )); ?>" target="_blank" class="etim-lk-btn-upgrade">Upgrade Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        .etim-app-wrapper {
            display: flex;
            min-height: calc(100vh - 60px);
            background: #ffffff;
            margin: 20px 20px 20px 0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            font-family: 'Poppins', sans-serif;
            color: #334155;
            box-sizing: border-box;
            overflow: hidden;
        }

        .etim-sidebar {
            width: 250px;
            background: #f1f5f9;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e2e8f0;
        }
        
        .etim-sidebar-brand {
            display: flex;
            align-items: center;
            padding: 0 0 30px 10px;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .etim-sidebar-brand img {
            width: 44px;
            margin-right: 10px;
        }

        .etim-nav { display: flex; flex-direction: column; gap: 4px; }

        .etim-nav-btn {
            display: flex; align-items: center; padding: 12px 16px; color: #475569;
            text-decoration: none; font-size: 14px; font-weight: 500;
            transition: all 0.2s; border-radius: 8px;
        }

        .etim-nav-btn:hover { background: #e2e8f0; color: #0f172a; }
        .etim-nav-btn.active {
            background: #ffffff; color: #4888E8;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .etim-nav-btn img { width: 18px; margin-right: 12px; opacity: 0.7; }
        .etim-nav-btn.active img { opacity: 1; }

        .etim-main { flex: 1; padding: 30px 40px; background: #ffffff; overflow-y: auto;}
        .etim-tab { display: none; animation: etimFadeIn 0.3s; }
        .etim-tab.active { display: block; }
        @keyframes etimFadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .etim-tab-header h2 { display: flex; align-items: center; font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
        .etim-tab-header h2 img { width: 20px; margin-right: 10px; }
        .etim-tab-header p { color: #64748b; margin: 0 0 25px 0; font-size: 14px; }

        .etim-panel { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); margin-bottom: 20px;}
        
        .etim-welcome { display: flex; justify-content: space-between; align-items: flex-start; }
        .etim-welcome-txt h3 { margin: 0 0 10px 0; font-size: 18px; color: #0f172a; }
        .etim-welcome-txt p { margin: 0 0 16px 0; color: #64748b; font-size:14px; line-height: 1.5; }
        .etim-welcome-close { background: transparent; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; }

        .etim-bento-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .etim-account h4 { margin:0 0 20px 0; font-size:15px; display:flex; align-items:center; gap:8px;}
        .etim-account h4 img { width:16px; }
        .etim-account-grid { display: grid; grid-template-columns: auto 1fr auto 1fr; gap: 16px 24px; align-items: center; }
        .etim-label { color: #0f172a; font-weight: 600; font-size: 13px; }
        .etim-value { color: #475569; font-size: 13px; }
        .etim-text-green { color: #10b981; font-weight: 600; }

        .etim-stat-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .etim-stat { display: flex; justify-content: space-between; align-items: center; padding: 20px; margin-bottom: 0;}
        .etim-stat h5 { margin: 0 0 8px 0; font-size: 13px; color: #0f172a; }
        .etim-stat-val { font-size: 26px; font-weight: 700; color: #4888E8; }
        .etim-stat-ico { width: 40px; }

        .etim-chart { grid-column: 1; }
        .etim-panel h4 { margin:0 0 20px 0; font-size:15px; display:flex; align-items:center; gap:8px;}
        .etim-panel h4 img { width:16px; }

        /* Sync Section */
        .etim-sync-flex { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .etim-sync-details p { margin: 0 0 10px 0; font-size: 13px; color: #475569; }
        .etim-sync-chart-placeholder { flex: 1; height: 100px; display: flex; align-items: flex-end; }
        .etim-line-chart { width: 100%; height: 100%; }

        /* Data Quality Section */
        .etim-progress-item { margin-bottom: 16px; }
        .etim-progress-item:last-child { margin-bottom: 0; }
        .etim-pi-label { font-size: 13px; font-weight: 500; color: #334155; margin-bottom: 6px; }
        .etim-pi-bar { width: 100%; height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden; }
        .etim-pi-fill { height: 100%; background: #4888E8; border-radius: 5px; transition: width 0.5s ease; }

        /* Top Classes Chart */
        .etim-top-classes-chart { display: flex; align-items: flex-end; justify-content: space-around; gap: 8px; height: 200px; padding-top: 10px; padding-bottom: 20px;}
        .etim-tcc-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; max-width: 45px;}
        .etim-tcc-bar-wrap { height: 100%; width: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; position:relative; }
        .etim-tcc-val { font-size: 11px; color: #475569; font-weight: 600; margin-bottom: 6px; }
        .etim-tcc-bar { width: 100%; background: #60a5fa; border-top-left-radius: 4px; border-top-right-radius: 4px; transition: height 0.3s, background 0.2s; min-height: 2px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);}
        .etim-tcc-bar-wrap:hover .etim-tcc-bar { background: #4888E8; }
        .etim-tcc-label-wrap { height: 30px; margin-top: 8px; display: flex; align-items: flex-start; justify-content: center; width: 100%; }
        .etim-tcc-label { font-size: 10px; font-family: monospace; color: #64748b; transform: rotate(-45deg); font-weight: 500;}

        /* Recent Activity */
        .etim-activity-list { list-style: none; margin: 0; padding: 0; max-height: 250px; overflow-y: auto; padding-right: 8px; }
        .etim-activity-list::-webkit-scrollbar { width: 4px; }
        .etim-activity-list::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .etim-activity-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .etim-activity-list li { padding: 10px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #475569; }
        .etim-activity-list li:last-child { border-bottom: none; }

        /* Forms */
        .etim-form-panel { max-width: 750px; background: transparent; border: none; padding: 0; box-shadow: none; margin-bottom: 20px;}
        .etim-f-row { display: flex; padding: 20px 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; align-items: center;}
        .etim-f-row:last-child { margin-bottom: 0; }
        .etim-f-left { width: 350px; flex-shrink: 0; }
        .etim-f-left label { font-weight: 600; color: #0f172a; font-size: 14px; display: block;}
        .etim-f-left p { margin: 6px 0 0 0; font-size: 13px; color: #a0aec0; padding-right:20px; line-height: 1.5; font-weight: 400;}
        .etim-f-right { flex: 1; display:flex; align-items: center; }
        
        .etim-form input[type="text"], .etim-form input[type="password"], .etim-form select {
            width: 100%; max-width: 350px; padding: 12px 16px; border: 1px solid #f1f5f9; border-radius: 8px; font-size: 14px; background: #f8fafc; color: #334155; box-sizing: border-box; box-shadow:none; transition: all 0.2s;
        }
        .etim-form input[type="text"]:focus, .etim-form input[type="password"]:focus, .etim-form select:focus {
            border-color: #4888E8; outline: none; background: #ffffff; box-shadow: 0 0 0 2px rgba(72, 136, 232, 0.1);
        }

        .etim-pwd-field { position: relative; width: 100%; max-width: 350px; }
        .etim-toggle-pwd { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; }

        .etim-flex-row { justify-content: space-between; align-items: center; }
        .etim-switch { position: relative; display: inline-block; width: 44px; height: 24px; margin-right: 15px;}
        .etim-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #34C759; }
        input:checked + .slider:before { transform: translateX(20px); }

        .etim-circular-btn {
            background: #ffffff; border: 1px solid #f1f5f9; color: #4888E8; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-right: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;
        }
        .etim-circular-btn:hover { background: #f8fafc; }

        .etim-token-row { flex-direction: column; gap: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 20px; margin-top: 10px; }
        .etim-token-top { display: flex; justify-content: space-between; align-items: center; }

        .etim-f-actions { display: flex; align-items: center; justify-content: flex-start; margin-top: 24px; }

        /* Buttons */
        .etim-btn-blue { background: #4888E8; color: white; border: none; padding: 10px 24px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .etim-btn-blue:hover { background: #3874CD; }
        .etim-btn-blue-outline { background: transparent; color: #4888E8; border: 1px solid #4888E8; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; }
        .etim-btn-light { background: #f1f5f9; color: #475569; border: none; padding: 10px 24px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;}
        .etim-btn-light:hover { background: #e2e8f0; color: #0f172a;}
        .etim-w-100 { width: 100%; box-sizing: border-box;}

        /* === License Key Tab Styles === */
        .etim-lk-header {
            display: flex; align-items: flex-start; gap: 12px; margin-bottom: 28px;
        }
        .etim-lk-header-icon {
            width: 40px; height: 40px; border-radius: 10px; background: #eef4fd;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;
        }
        .etim-lk-title {
            margin: 0 0 4px; font-size: 22px; font-weight: 700; color: #0f172a;
        }
        .etim-lk-subtitle {
            margin: 0; font-size: 14px; color: #64748b; font-weight: 400;
        }

        /* License Card */
        .etim-lk-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 28px 32px; margin-bottom: 32px;
        }
        .etim-lk-card-header {
            display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;
        }
        .etim-lk-card-title {
            margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #0f172a;
        }
        .etim-lk-card-desc {
            margin: 0 0 20px; font-size: 13px; color: #64748b; line-height: 1.5;
        }
        .etim-lk-status-pill {
            background: #d1fae5; color: #065f46; padding: 4px 14px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .etim-lk-detail-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
        }
        .etim-lk-detail-item {
            display: flex; flex-direction: column; gap: 4px;
            padding: 14px 18px; background: #f8fafc; border-radius: 10px;
        }
        .etim-lk-detail-label {
            font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .etim-lk-detail-value {
            font-size: 14px; font-weight: 600; color: #0f172a;
        }
        .etim-lk-input-row { max-width: 420px; }
        .etim-lk-input {
            width: 100%; padding: 13px 16px; border: 1px solid #e2e8f0;
            border-radius: 10px; font-size: 14px; background: #f8fafc;
            color: #94a3b8; transition: all 0.2s; box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        .etim-lk-input:focus {
            border-color: #4888E8; outline: none; background: #fff;
            box-shadow: 0 0 0 3px rgba(72,136,232,0.1); color: #334155;
        }
        .etim-lk-btn-activate {
            display: inline-block; background: #4888E8; color: #fff; border: none;
            padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: background 0.2s; font-family: 'Poppins', sans-serif;
        }
        .etim-lk-btn-activate:hover { background: #3874CD; }

        .etim-btn-danger {
            display: inline-flex; align-items: center; background: #fff; color: #ef4444;
            border: 1px solid #fecaca; padding: 10px 20px; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .etim-btn-danger:hover { background: #fef2f2; border-color: #ef4444; }

        /* License Message */
        .etim-lic-message {
            margin-top: 16px; padding: 12px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
        }
        .etim-lic-message.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .etim-lic-message.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .etim-lic-message.loading { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

        /* Plans Section */
        .etim-lk-plans-section { margin-top: 10px; }
        .etim-lk-plans-title {
            font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 4px;
        }
        .etim-lk-plans-subtitle {
            font-size: 13px; color: #64748b; margin: 0 0 24px;
        }
        .etim-lk-plans-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px;
        }
        .etim-lk-plan-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 28px 24px 24px; display: flex; flex-direction: column;
            transition: all 0.25s ease;
        }
        .etim-lk-plan-card:hover {
            transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.07);
        }
        .etim-lk-plan-active { border-color: #4888E8; }
        .etim-lk-plan-top { margin-bottom: 20px; }
        .etim-lk-plan-img {
            width: 48px; height: 48px; margin-bottom: 14px; object-fit: contain;
        }
        .etim-lk-plan-name {
            margin: 0 0 10px; font-size: 18px; font-weight: 700; color: #0f172a;
        }
        .etim-lk-plan-price { display: flex; align-items: baseline; gap: 4px; margin-bottom: 12px; }
        .etim-lk-price-amount {
            font-size: 32px; font-weight: 800; color: #4888E8;
        }
        .etim-lk-price-period {
            font-size: 15px; font-weight: 500; color: #64748b;
        }
        .etim-lk-plan-desc {
            margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;
        }
        .etim-lk-feature-list {
            list-style: none; padding: 0; margin: 0 0 24px 0; flex-grow: 1;
        }
        .etim-lk-feature-list li {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; color: #334155; margin-bottom: 12px; font-weight: 500;
        }
        .etim-lk-tick {
            width: 20px; height: 20px; flex-shrink: 0; object-fit: contain;
        }
        .etim-lk-plan-btn-wrap { margin-top: auto; }
        .etim-lk-btn-current {
            display: block; text-align: center; padding: 12px 24px;
            background: #f1f5f9; color: #64748b; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: default;
        }
        .etim-lk-btn-upgrade {
            display: block; text-align: center; padding: 12px 24px;
            background: #4888E8; color: #fff; border-radius: 10px;
            font-size: 14px; font-weight: 600; text-decoration: none;
            transition: background 0.2s;
        }
        .etim-lk-btn-upgrade:hover { background: #3874CD; color: #fff; }

        .etim-f-list { list-style: none; padding: 0; margin: 0 0 24px 0; }
        .etim-f-list li { margin-bottom: 12px; font-size: 13px; color: #475569; }

        /* Pro Badge */
        .etim-pro-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; padding: 2px 10px; border-radius: 12px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        }

        /* Locked feature row */
        .etim-pro-locked {
            position: relative; opacity: 0.55; pointer-events: none;
        }
        .etim-pro-locked .etim-pro-badge {
            pointer-events: all; position: relative; z-index: 2;
        }

        /* Status pills variants */
        .etim-lk-status-active { background: #d1fae5; color: #065f46; }
        .etim-lk-status-expired { background: #fef2f2; color: #991b1b; }
        .etim-lk-status-free { background: #f1f5f9; color: #64748b; }

        /* Popular badge */
        .etim-lk-popular-badge {
            position: absolute; top: -10px; right: 16px;
            background: linear-gradient(135deg, #4888E8, #6366f1);
            color: #fff; padding: 4px 14px; border-radius: 12px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.03em;
        }

        /* Feature access table */
        .etim-feature-table {
            width: 100%; border-collapse: collapse;
        }
        .etim-feature-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .etim-feature-table tr:last-child {
            border-bottom: none;
        }
        .etim-feature-table td {
            padding: 12px 0; font-size: 13px;
        }
        .etim-ft-name {
            font-weight: 500; color: #334155;
        }
        .etim-ft-status {
            text-align: right; width: 120px;
        }

        @media (max-width: 960px) {
            .etim-lk-plans-grid { grid-template-columns: 1fr; }
            .etim-lk-detail-grid { grid-template-columns: 1fr; }
            .etim-lk-input-row { max-width: 100%; }
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Switching
            var navBtns = document.querySelectorAll('.etim-nav-btn');
            var tabs = document.querySelectorAll('.etim-tab');
            
            // Check for saved tab
            var activeTab = localStorage.getItem('etim_active_tab') || 'tab-dashboard';
            
            // Initial setup based on saved tab
            if (activeTab) {
                navBtns.forEach(function(b) { b.classList.remove('active'); });
                tabs.forEach(function(t) { t.classList.remove('active'); });
                
                var targetBtn = document.querySelector('.etim-nav-btn[data-tab="' + activeTab + '"]');
                var targetTab = document.getElementById(activeTab);
                
                if (targetBtn && targetTab) {
                    targetBtn.classList.add('active');
                    targetTab.classList.add('active');
                } else {
                    // Fallback to dashboard
                    var firstBtn = document.querySelector('.etim-nav-btn[data-tab="tab-dashboard"]');
                    if(firstBtn) firstBtn.classList.add('active');
                    var firstTab = document.getElementById('tab-dashboard');
                    if(firstTab) firstTab.classList.add('active');
                }
            }
            
            navBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetId = this.getAttribute('data-tab');
                    
                    navBtns.forEach(function(b) { b.classList.remove('active'); });
                    tabs.forEach(function(t) { t.classList.remove('active'); });
                    
                    this.classList.add('active');
                    document.getElementById(targetId).classList.add('active');
                    
                    // Save active tab
                    localStorage.setItem('etim_active_tab', targetId);
                });
            });

            // Color Sync logic
            var colorText = document.getElementById('etim-filter-color-text');
            var colorPicker = document.getElementById('etim-filter-color-picker');
            if (colorText && colorPicker) {
                colorText.addEventListener('input', function() {
                    var val = this.value;
                    if(val.match(/^#[0-9a-fA-F]{6}$/) || val.match(/^#[0-9a-fA-F]{3}$/)) {
                        colorPicker.value = val.length === 4 ? '#' + val[1]+val[1]+val[2]+val[2]+val[3]+val[3] : val;
                    }
                });
                colorPicker.addEventListener('input', function() {
                    colorText.value = this.value.toUpperCase();
                });
            }

            // License Activation
            (function() {
                var activateBtn = document.getElementById('etim-activate-license');
                var deactivateBtn = document.getElementById('etim-deactivate-license');

                if (activateBtn) {
                    activateBtn.addEventListener('click', function() {
                        var keyInput = document.getElementById('etim-license-key-input');
                        var msgEl = document.getElementById('etim-lic-message');
                        var key = keyInput ? keyInput.value.trim() : '';

                        if (!key) {
                            msgEl.className = 'etim-lic-message error';
                            msgEl.textContent = 'Please enter a license key.';
                            msgEl.style.display = 'block';
                            return;
                        }

                        activateBtn.disabled = true;
                        activateBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="margin-right:6px;animation:spin 1s linear infinite"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-dasharray="31.4 31.4" stroke-linecap="round"/></svg> Activating...';
                        msgEl.className = 'etim-lic-message loading';
                        msgEl.textContent = 'Verifying license key with server...';
                        msgEl.style.display = 'block';

                        if (typeof jQuery !== 'undefined') {
                            jQuery.ajax({
                                url: etimSettings.ajaxUrl,
                                type: 'POST',
                                data: {
                                    action: 'etim_activate_license',
                                    nonce: etimSettings.nonce,
                                    license_key: key
                                },
                                success: function(response) {
                                    if (response.success) {
                                        msgEl.className = 'etim-lic-message success';
                                        msgEl.textContent = response.data.message;
                                        setTimeout(function() { location.reload(); }, 1200);
                                    } else {
                                        msgEl.className = 'etim-lic-message error';
                                        msgEl.textContent = response.data.message || 'Activation failed.';
                                        activateBtn.disabled = false;
                                        activateBtn.innerHTML = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="margin-right:6px;"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Activate';
                                    }
                                },
                                error: function() {
                                    msgEl.className = 'etim-lic-message error';
                                    msgEl.textContent = 'Network error. Please try again.';
                                    activateBtn.disabled = false;
                                    activateBtn.innerHTML = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="margin-right:6px;"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Activate';
                                }
                            });
                        }
                    });
                }

                if (deactivateBtn) {
                    deactivateBtn.addEventListener('click', function() {
                        if (!confirm('Are you sure you want to deactivate your license? This will remove access to premium features.')) return;

                        deactivateBtn.disabled = true;
                        deactivateBtn.textContent = 'Deactivating...';

                        if (typeof jQuery !== 'undefined') {
                            jQuery.ajax({
                                url: etimSettings.ajaxUrl,
                                type: 'POST',
                                data: {
                                    action: 'etim_deactivate_license',
                                    nonce: etimSettings.nonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        setTimeout(function() { location.reload(); }, 800);
                                    } else {
                                        alert(response.data.message || 'Deactivation failed.');
                                        deactivateBtn.disabled = false;
                                        deactivateBtn.textContent = 'Deactivate License';
                                    }
                                },
                                error: function() {
                                    alert('Network error. Please try again.');
                                    deactivateBtn.disabled = false;
                                    deactivateBtn.textContent = 'Deactivate License';
                                }
                            });
                        }
                    });
                }
            })();

            // Rebind the Test Connection button to match functionality using jQuery
            if (typeof jQuery !== 'undefined') {
                jQuery(document).on('click', '#etim-test-connection', function(e) {
                    e.preventDefault();
                    var $btn = jQuery(this);
                    var $resBox = jQuery('#etim-test-result');
                    var $status = jQuery('#etim-test-status');
                    var $msg = jQuery('#etim-test-message');
                    
                    if (typeof etimSettings === 'undefined') return;
                    
                    $btn.prop('disabled', true).text(etimSettings.strings.testing);
                    
                    jQuery.ajax({
                        url: etimSettings.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'etim_test_connection',
                            nonce: etimSettings.nonce
                        },
                        success: function(response) {
                            $resBox.show();
                            if (response.success) {
                                $resBox.css({'background':'#f0fdf4', 'border-color':'#bbf7d0'});
                                $status.html('✔ Success!').css('color', '#15803d');
                                $msg.html(response.data.message.replace(/\n/g, '<br>')).css('color', '#166534');
                            } else {
                                $resBox.css({'background':'#fef2f2', 'border-color':'#fecaca'});
                                $status.html('✖ Error').css('color', '#b91c1c');
                                $msg.html(response.data.message).css('color', '#991b1b');
                            }
                            $btn.prop('disabled', false).text('Test API Connection');
                        },
                        error: function() {
                            $resBox.show().css({'background':'#fef2f2', 'border-color':'#fecaca'});
                            $status.html('✖ Error').css('color', '#b91c1c');
                            $msg.html('A network error occurred').css('color', '#991b1b');
                            $btn.prop('disabled', false).text('Test API Connection');
                        }
                    });
                });
            }
        });
        </script>
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

    /**
     * Handle the simple export request
     */
    public function handle_simple_export() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Permission denied', 'etim-for-woocommerce'));
        }
        if (!ETIM_Feature_Access::get_instance()->can_export()) {
            wp_die(__('CSV export is not available on your current plan. Please upgrade to Distributor or Agency plan.', 'etim-for-woocommerce'));
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="etim-assigned-data-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Add BOM for UTF-8 compatibility in Excel
        fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // Output Headers
        fputcsv($output, ['SKU', 'ETIM Version', 'ETIM Group Code', 'ETIM Group Name', 'ETIM Class Code', 'ETIM Class Name', 'Feature Code', 'Feature Name', 'Value', 'Unit']);

        global $wpdb;

        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $classes_table = $wpdb->prefix . 'etim_product_classes';
            $features_table = $wpdb->prefix . 'etim_product_features';
            $groups_table = $wpdb->prefix . 'etim_product_groups';
            
            $has_classes_table = $wpdb->get_var("SHOW TABLES LIKE '$classes_table'") === $classes_table;
            $has_features_table = $wpdb->get_var("SHOW TABLES LIKE '$features_table'") === $features_table;

            if ($has_classes_table && $has_features_table) {
                foreach ($query->posts as $pid) {
                    $product = wc_get_product($pid);
                    if (!$product) continue;

                    $classes_query = "
                        SELECT c.*, g.group_name 
                        FROM $classes_table c 
                        LEFT JOIN $groups_table g ON c.product_id = g.product_id AND c.group_code = g.group_code 
                        WHERE c.product_id = %d
                    ";
                    $classes = $wpdb->get_results($wpdb->prepare($classes_query, $pid), ARRAY_A);
                    if (empty($classes)) continue;

                    $version = get_post_meta($pid, '_etim_version', true) ?: 'ETIM-8.0';
                    $sku = $product->get_sku() ?: '';

                    foreach ($classes as $cls) {
                        $group_code = !empty($cls['group_code']) ? $cls['group_code'] : '';
                        $group_name = !empty($cls['group_name']) ? $cls['group_name'] : '';
                        $class_code = !empty($cls['class_code']) ? $cls['class_code'] : '';
                        $class_name = !empty($cls['class_name']) ? $cls['class_name'] : '';

                        $features = $wpdb->get_results($wpdb->prepare("SELECT * FROM $features_table WHERE product_id = %d AND class_code = %s", $pid, $class_code), ARRAY_A);
                        
                        if (empty($features)) {
                            // If a product is mapped but has no features, output the mapping alone to preserve assignment awareness
                            fputcsv($output, [
                                $sku,
                                $version,
                                $group_code,
                                $group_name,
                                $class_code,
                                $class_name,
                                '',
                                '',
                                '',
                                ''
                            ]);
                        } else {
                            // Export all features assigned to this class
                            foreach ($features as $feature) {
                                $value = $feature['feature_value_description'] ?? '';
                                $value = rtrim(strip_tags(str_replace("\n", ' ', $value)));
                                
                                fputcsv($output, [
                                    $sku,
                                    $version,
                                    $group_code,
                                    $group_name,
                                    $class_code,
                                    $class_name,
                                    $feature['feature_code'],
                                    $feature['feature_name'],
                                    $value,
                                    '' // Unit placeholder
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Handle XML export for all products
     */
    public function handle_xml_export_all() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Permission denied', 'etim-for-woocommerce'));
        }
        if (!ETIM_Feature_Access::get_instance()->can_export()) {
            wp_die(__('XML export is not available on your current plan. Please upgrade to Distributor or Agency plan.', 'etim-for-woocommerce'));
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="etim-all-products-' . date('Y-m-d') . '.xml"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('ETIMData');
        $root->setAttribute('exported', date('Y-m-d H:i:s'));
        $dom->appendChild($root);

        global $wpdb;
        $db = ETIM_DB::get_instance();

        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ];
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            foreach ($query->posts as $pid) {
                $product = wc_get_product($pid);
                if (!$product) continue;

                $etim_data = $db->get_product_etim_data($pid);
                if (empty($etim_data)) continue;

                $product_el = $dom->createElement('Product');
                $product_el->setAttribute('id', $pid);
                $product_el->setAttribute('name', $product->get_name());
                $sku = $product->get_sku();
                if ($sku) {
                    $product_el->setAttribute('sku', $sku);
                }

                foreach ($etim_data as $class_data) {
                    $this->build_xml_class_node($dom, $product_el, $class_data);
                }

                $root->appendChild($product_el);
            }
        }

        echo $dom->saveXML();
        exit;
    }

    /**
     * Handle XML export for a single product
     */
    public function handle_xml_export_single() {
        if (!current_user_can('edit_products')) {
            wp_die(__('Permission denied', 'etim-for-woocommerce'));
        }
        if (!ETIM_Feature_Access::get_instance()->can_export()) {
            wp_die(__('XML export is not available on your current plan. Please upgrade to Distributor or Agency plan.', 'etim-for-woocommerce'));
        }

        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        if (!$product_id) {
            wp_die(__('Invalid product ID', 'etim-for-woocommerce'));
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_die(__('Product not found', 'etim-for-woocommerce'));
        }

        $db = ETIM_DB::get_instance();
        $etim_data = $db->get_product_etim_data($product_id);

        $slug = sanitize_title($product->get_name());
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="etim-product-' . $product_id . '-' . $slug . '.xml"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('ETIMData');
        $root->setAttribute('exported', date('Y-m-d H:i:s'));
        $dom->appendChild($root);

        $product_el = $dom->createElement('Product');
        $product_el->setAttribute('id', $product_id);
        $product_el->setAttribute('name', $product->get_name());
        $sku = $product->get_sku();
        if ($sku) {
            $product_el->setAttribute('sku', $sku);
        }

        if (!empty($etim_data)) {
            foreach ($etim_data as $class_data) {
                $this->build_xml_class_node($dom, $product_el, $class_data);
            }
        }

        $root->appendChild($product_el);
        echo $dom->saveXML();
        exit;
    }

    /**
     * Build XML nodes for a single ETIM class
     */
    private function build_xml_class_node($dom, $parent_el, $class_data) {
        if (empty($class_data['code'])) return;

        // Group node
        if (!empty($class_data['group']['code'])) {
            $group_el = $dom->createElement('Group');
            $group_el->setAttribute('code', $class_data['group']['code']);
            if (!empty($class_data['group']['description'])) {
                $group_el->setAttribute('name', $class_data['group']['description']);
            }
            $parent_el->appendChild($group_el);
        }

        // Class node
        $class_el = $dom->createElement('Class');
        $class_el->setAttribute('code', $class_data['code']);
        if (!empty($class_data['description'])) {
            $class_el->setAttribute('name', $class_data['description']);
        }

        // Features
        if (!empty($class_data['features']) && is_array($class_data['features'])) {
            $features_el = $dom->createElement('Features');
            foreach ($class_data['features'] as $feature) {
                if (empty($feature['code'])) continue;

                $feat_el = $dom->createElement('Feature');
                $feat_el->setAttribute('code', $feature['code']);
                if (!empty($feature['description'])) {
                    $feat_el->setAttribute('name', $feature['description']);
                }
                if (!empty($feature['type'])) {
                    $feat_el->setAttribute('type', $feature['type']);
                }

                // Value
                if (isset($feature['assignedValue']) && $feature['assignedValue'] !== '' && $feature['assignedValue'] !== null) {
                    $value_el = $dom->createElement('Value');
                    if (is_array($feature['assignedValue'])) {
                        if (!empty($feature['assignedValue']['code'])) {
                            $value_el->setAttribute('code', $feature['assignedValue']['code']);
                        }
                        $desc = $feature['assignedValue']['description'] ?? '';
                        $value_el->appendChild($dom->createTextNode($desc));
                    } else {
                        $value_el->appendChild($dom->createTextNode((string) $feature['assignedValue']));
                    }
                    $feat_el->appendChild($value_el);
                }

                $features_el->appendChild($feat_el);
            }
            $class_el->appendChild($features_el);
        }

        $parent_el->appendChild($class_el);
    }

    /**
     * Handle the simple CSV import request
     */
    public function handle_simple_import() {
        if (!current_user_can('manage_woocommerce') || !check_admin_referer('etim_simple_import_nonce')) {
            wp_die(__('Permission denied or invalid form submission', 'etim-for-woocommerce'));
        }
        if (!ETIM_Feature_Access::get_instance()->can_import()) {
            wp_die(__('CSV import is not available on your current plan. Please upgrade to Distributor or Agency plan.', 'etim-for-woocommerce'));
        }

        if (empty($_FILES['import_file']['tmp_name'])) {
            wp_redirect(admin_url('admin.php?page=etim-settings&imported=0'));
            exit;
        }

        $file = $_FILES['import_file']['tmp_name'];
        if (($handle = fopen($file, "r")) !== false) {
            global $wpdb;
            $classes_table = $wpdb->prefix . 'etim_product_classes';
            $features_table = $wpdb->prefix . 'etim_product_features';
            $groups_table = $wpdb->prefix . 'etim_product_groups';

            // Skip headers line
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 6) continue;
                
                $sku = sanitize_text_field($row[0]);
                $version = sanitize_text_field($row[1]);
                $group = sanitize_text_field($row[2]);
                $group_name = sanitize_text_field($row[3]);
                $class_code = sanitize_text_field($row[4]);
                $class_name = sanitize_text_field($row[5]);
                $feat_code = sanitize_text_field($row[6] ?? '');
                $feat_name = sanitize_text_field($row[7] ?? '');
                $value = sanitize_text_field($row[8] ?? '');
                
                $pid = 0;
                if (!empty($sku)) {
                    $pid = wc_get_product_id_by_sku($sku);
                }
                
                if (!$pid || empty($class_code)) continue;
                
                update_post_meta($pid, '_etim_version', $version ?: 'ETIM-8.0');
                if (!empty($group)) update_post_meta($pid, '_etim_group_id', $group);
                
                $wpdb->replace(
                    $classes_table,
                    [
                        'product_id' => $pid,
                        'class_code' => $class_code,
                        'class_name' => $class_name ?: $class_code,
                        'group_code' => $group
                    ],
                    ['%d', '%s', '%s', '%s']
                );
                
                if (!empty($group)) {
                    $wpdb->replace(
                        $groups_table,
                        [
                            'product_id' => $pid,
                            'group_code' => $group,
                            'group_name' => $group_name ?: $group
                        ],
                        ['%d', '%s', '%s']
                    );
                }

                if (!empty($feat_code)) {
                    $wpdb->replace(
                        $features_table,
                        [
                            'product_id' => $pid,
                            'class_code' => $class_code,
                            'feature_code' => $feat_code,
                            'feature_name' => $feat_name ?: $feat_code,
                            'feature_value_description' => $value,
                            'feature_type' => 'alphanumeric'
                        ],
                        ['%d', '%s', '%s', '%s', '%s', '%s']
                    );
                }
            }
            fclose($handle);
        }

        wp_redirect(admin_url('admin.php?page=etim-settings&imported=1'));
        exit;
    }

    /**
     * Handle clearing all ETIM data assigned across all products
     */
    public function handle_clear_data() {
        if (!current_user_can('manage_woocommerce') || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'etim_clear_data')) {
            wp_die(__('Permission denied', 'etim-for-woocommerce'));
        }

        global $wpdb;

        $classes_table = $wpdb->prefix . 'etim_product_classes';
        $features_table = $wpdb->prefix . 'etim_product_features';
        $groups_table = $wpdb->prefix . 'etim_product_groups';

        // Truncate tables ensuring clean reset
        $wpdb->query("TRUNCATE TABLE $classes_table");
        $wpdb->query("TRUNCATE TABLE $features_table");
        $wpdb->query("TRUNCATE TABLE $groups_table");
        
        // Remove tracking references inside standard WooCommerce custom fields postmeta array
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_etim_version', '_etim_group_id', '_etim_classification')");

        wp_redirect(admin_url('admin.php?page=etim-settings&cleared=1'));
        exit;
    }
}
