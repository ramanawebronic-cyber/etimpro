<?php
/**
 * ETIM Product Meta Class
 * 
 * Handles ETIM meta box on WooCommerce product edit page
 * 
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ETIM_Product_Meta Class
 */
class ETIM_Product_Meta {
    
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
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('woocommerce_product_data_tabs', [$this, 'add_product_data_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'render_product_data_panel']);
        
        // AJAX handlers
        
        // Frontend display
        add_filter('woocommerce_product_tabs', [$this, 'add_product_tab']);
        
        // Shortcode
        add_shortcode('etim_specs', [$this, 'render_shortcode']);
    }
    
    /**
     * Add meta box to product edit screen
     */
    public function add_meta_box() {
        add_meta_box(
            'etim_classification_meta_box',
            __('ETIM Classification', 'etim-for-woocommerce'),
            [$this, 'render_meta_box'],
            'product',
            'normal',
            'high'
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        global $post;
        
        // Only load on product edit screens
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }
        
        if (!$post || $post->post_type !== 'product') {
            return;
        }
        
        // Check if credentials are configured
        $client_id = get_option('etim_client_id', '');
        if (empty($client_id)) {
            return;
        }
        
        // Enqueue Select2
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0-rc.0');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);
        
        // Enqueue plugin styles
        wp_enqueue_style(
            'etim-product-meta',
            ETIM_WC_PLUGIN_URL . 'assets/css/product-meta.css',
            [],
            ETIM_WC_VERSION
        );
        
        // Enqueue plugin scripts
        wp_enqueue_script(
            'etim-product-meta',
            ETIM_WC_PLUGIN_URL . 'assets/js/product-meta.js',
            ['jquery', 'select2'],
            ETIM_WC_VERSION,
            true
        );
        
    $fa = ETIM_Feature_Access::get_instance();
    wp_localize_script('etim-product-meta', 'etimProductMeta', [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('etim_ajax_nonce'),
        'productId' => get_the_ID(),
        'featureAccess' => $fa->get_js_data(get_the_ID()),
        'strings'   => [
            'selectGroup'         => __('Select an ETIM Group...', 'etim-for-woocommerce'),
            'selectClass'         => __('Select an ETIM Class...', 'etim-for-woocommerce'),
            'selectFeature'       => __('Select a feature', 'etim-for-woocommerce'),
            'selectValue'         => __('Select a value', 'etim-for-woocommerce'),
            'loading'             => __('Loading...', 'etim-for-woocommerce'),
            'noResults'           => __('No results found', 'etim-for-woocommerce'),
            'searchGroups'        => __('Search ETIM Groups...', 'etim-for-woocommerce'),
            'searchClasses'       => __('Search ETIM Classes...', 'etim-for-woocommerce'),
            'saving'              => __('Saving...', 'etim-for-woocommerce'),
            'saved'               => __('Saved!', 'etim-for-woocommerce'),
            'error'               => __('Error:', 'etim-for-woocommerce'),
            'confirmRemoveClass'  => __('Are you sure you want to remove this ETIM class?', 'etim-for-woocommerce'),
            'confirmRemoveFeature'=> __('Are you sure you want to remove this feature?', 'etim-for-woocommerce'),
            'yes'                 => __('Yes', 'etim-for-woocommerce'),
            'no'                  => __('No', 'etim-for-woocommerce'),
            'featureValue'        => __('Feature Value', 'etim-for-woocommerce'),
            'addFeature'          => __('Add Feature', 'etim-for-woocommerce'),
            'addClass'            => __('Add ETIM Class', 'etim-for-woocommerce'),
        ],
    ]);
}

    
    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        // Check if credentials are configured
        $client_id = get_option('etim_client_id', '');
        
        if (empty($client_id)) {
            $this->render_credentials_notice();
            return;
        }
        
        // Get existing ETIM data
        $etim_data = $this->get_product_etim_data($post->ID);
        
        // Render the template
        include ETIM_WC_PLUGIN_DIR . 'templates/product-meta-box.php';
    }
    
    /**
     * Render credentials notice
     */
    private function render_credentials_notice() {
        ?>
        <div class="etim-credentials-notice">
            <p>
                <?php
                printf(
                    wp_kses(
                        __('ETIM API credentials are not configured. Please <a href="%s">configure your credentials</a> to use ETIM classification.', 'etim-for-woocommerce'),
                        ['a' => ['href' => []]]
                    ),
                    esc_url(admin_url('admin.php?page=etim-settings'))
                );
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get product ETIM data
     */
    public function get_product_etim_data($product_id) {
        $etim_db = ETIM_DB::get_instance();
        return $etim_db->get_product_etim_data($product_id);
    }
    


    public function add_product_data_tab($tabs) {
        $tabs['etim'] = [
            'label'    => __('ETIM', 'etim-for-woocommerce'),
            'target'   => 'etim_product_data',
            'class'    => [],
            'priority' => 80,
        ];
        
        return $tabs;
    }
    
    /**
     * Render product data panel
     */
    public function render_product_data_panel() {
        global $post;
        
        ?>
        <div id="etim_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <p class="form-field">
                    <?php esc_html_e('ETIM classification is managed in the ETIM Classification meta box above.', 'etim-for-woocommerce'); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add frontend product tab
     */
    public function add_product_tab($tabs) {
        global $product;
        
        if (!$product) {
            return $tabs;
        }
        
        $etim_data = $this->get_product_etim_data($product->get_id());
        
        // Check if there are features with assigned values
        $has_features = false;
        if (!empty($etim_data)) {
            foreach ($etim_data as $class) {
                if (!empty($class['features'])) {
                    foreach ($class['features'] as $feature) {
                        if (!empty($feature['assignedValue'])) {
                            $has_features = true;
                            break 2;
                        }
                    }
                }
            }
        }
        
        if ($has_features) {
            $tabs['etim_features'] = [
                'title'    => __('Technical Specifications', 'etim-for-woocommerce'),
                'priority' => 50,
                'callback' => [$this, 'render_product_tab'],
            ];
        }
        
        return $tabs;
    }
    
    /**
     * Render frontend product tab
     */
    public function render_product_tab() {
        global $product;
        
        $etim_data = $this->get_product_etim_data($product->get_id());
        
        include ETIM_WC_PLUGIN_DIR . 'templates/product-tab.php';
    }

    /**
     * Render ETIM shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => ''
        ], $atts, 'etim_specs');
        
        $product_id = !empty($atts['id']) ? intval($atts['id']) : get_the_ID();
        
        if (!$product_id) {
            return '';
        }

        $etim_data = $this->get_product_etim_data($product_id);
        
        if (empty($etim_data)) {
            return '';
        }
        
        // Check if there are features with assigned values
        $has_features = false;
        foreach ($etim_data as $class) {
            if (!empty($class['features'])) {
                foreach ($class['features'] as $feature) {
                    if (!empty($feature['assignedValue'])) {
                        $has_features = true;
                        break 2;
                    }
                }
            }
        }
        
        if (!$has_features) {
            return '';
        }
        
        ob_start();
        include ETIM_WC_PLUGIN_DIR . 'templates/product-tab.php';
        return ob_get_clean();
    }
}
