<?php
/**
 * Frontend ETIM Filter
 *
 * Adds a filter dropdown to WooCommerce category pages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ETIM_Frontend_Filter {

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
        $this->init_hooks();
    }

    private function init_hooks() {
        // Filter the main product query
        add_action('woocommerce_product_query', [$this, 'filter_product_query']);
        
        // Add shortcode for the filter
        add_shortcode('etim_class_filter', [$this, 'render_filter_ui_shortcode']);
    }

    public function render_filter_ui_shortcode($atts) {
        if (get_option('etim_enable_filter', 'no') !== 'yes') {
            return '';
        }
        ob_start();
        $this->render_filter_ui();
        return ob_get_clean();
    }

    public function render_filter_ui() {
        if (!is_product_category() && !is_shop()) {
            return;
        }

        global $wp_query, $wpdb;

        // Get the current category ID if applicable
        $category_id = 0;
        if (is_product_category()) {
            $category = $wp_query->get_queried_object();
            if ($category) {
                $category_id = $category->term_id;
            }
        }

        $classes_table = $wpdb->prefix . 'etim_product_classes';

        // Get the distinct classes assigned to products
        if ($category_id) {
            $sql = $wpdb->prepare("
                SELECT DISTINCT c.class_code, c.class_name, COUNT(DISTINCT c.product_id) as product_count
                FROM $classes_table c
                INNER JOIN {$wpdb->term_relationships} tr ON c.product_id = tr.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.term_id = %d AND tt.taxonomy = 'product_cat'
                GROUP BY c.class_code, c.class_name
                ORDER BY c.class_name ASC
            ", $category_id);
        } else {
            $sql = "
                SELECT DISTINCT class_code, class_name, COUNT(DISTINCT product_id) as product_count
                FROM $classes_table
                GROUP BY class_code, class_name
                ORDER BY class_name ASC
            ";
        }

        $classes = $wpdb->get_results($sql);

        if (empty($classes)) {
            return;
        }

        $current_filter = isset($_GET['etim_class']) ? sanitize_text_field($_GET['etim_class']) : '';
        $total_products = 0;
        foreach ($classes as $cls) {
            $total_products += intval($cls->product_count);
        }

        $filter_color = get_option('etim_filter_color', '#475569');
        if (empty($filter_color)) {
            $filter_color = '#475569';
        }
        $encoded_color = str_replace('#', '%23', $filter_color);
        
        // Find active class name
        $active_class_name = '';
        if ($current_filter) {
            foreach ($classes as $cls) {
                if ($cls->class_code === $current_filter) {
                    $active_class_name = $cls->class_name ?: $cls->class_code;
                    break;
                }
            }
        }
        $clear_url = esc_url(remove_query_arg(['etim_class', 'paged']));
        ?>

        <div class="etim-sf-wrapper" id="etim-sf-wrapper">

            <?php if ($current_filter && $active_class_name): ?>
            <!-- Active filter pill -->
            <div class="etim-sf-active">
                <div class="etim-sf-active-inner">
                    <svg class="etim-sf-active-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" stroke="<?php echo esc_attr($filter_color); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="etim-sf-active-label">ETIM Class:</span>
                    <span class="etim-sf-active-value"><?php echo esc_html($active_class_name); ?></span>
                    <a href="<?php echo $clear_url; ?>" class="etim-sf-clear-btn" title="Clear filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search box -->
            <div class="etim-sf-search-box">
                <svg class="etim-sf-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="#94a3b8" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" class="etim-sf-input" id="etim-sf-input" placeholder="Search ETIM class..." autocomplete="off" />
                <!-- <span class="etim-sf-count"><?php echo count($classes); ?> classes</span> -->
            </div>

            <!-- Search results dropdown -->
            <div class="etim-sf-results" id="etim-sf-results">
                <?php foreach ($classes as $cls) :
                    $is_active = ($current_filter === $cls->class_code);
                    $filter_url = add_query_arg('etim_class', $cls->class_code, remove_query_arg('paged'));
                ?>
                <a href="<?php echo esc_url($filter_url); ?>" class="etim-sf-result-item <?php echo $is_active ? 'etim-sf-item-active' : ''; ?>" data-name="<?php echo esc_attr(strtolower($cls->class_name ?: $cls->class_code)); ?>">
                    <span class="etim-sf-item-name"><?php echo esc_html($cls->class_name ?: $cls->class_code); ?></span>
                    <span class="etim-sf-item-count"><?php echo intval($cls->product_count); ?></span>
                </a>
                <?php endforeach; ?>
                <div class="etim-sf-no-results" id="etim-sf-no-results" style="display:none;">No classes found</div>
            </div>
        </div>

        <style>
        .etim-sf-wrapper {
            margin-bottom: 24px;
            width: 100%;
            max-width: 380px;
            position: relative;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Active filter pill */
        .etim-sf-active {
            margin-bottom: 10px;
        }
        .etim-sf-active-inner {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef4fd;
            border: 1px solid #c5dafb;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
        }
        .etim-sf-active-icon { flex-shrink: 0; }
        .etim-sf-active-label {
            color: #64748b;
            font-weight: 500;
        }
        .etim-sf-active-value {
            color: <?php echo esc_attr($filter_color); ?>;
            font-weight: 600;
        }
        .etim-sf-clear-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #fff;
            color: #94a3b8;
            text-decoration: none;
            margin-left: 4px;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }
        .etim-sf-clear-btn:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fecaca;
        }

        /* Search box */
        .etim-sf-search-box {
            position: relative;
            display: flex;
            align-items: center;
        }
        .etim-sf-search-icon {
            position: absolute;
            left: 14px;
            pointer-events: none;
            z-index: 1;
        }
        .etim-sf-input {
            width: 100%;
            padding: 12px 90px 12px 42px;
            font-size: 14px;
            color: #334155;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-sizing: border-box;
            transition: all 0.2s ease;
            outline: none;
        }
        .etim-sf-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .etim-sf-input:focus {
            border-color: <?php echo esc_attr($filter_color); ?>;
            box-shadow: 0 0 0 3px rgba(72,136,232,0.1);
        }
        .etim-sf-count {
            position: absolute;
            right: 14px;
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
            pointer-events: none;
        }

        /* Results dropdown */
        .etim-sf-results {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 6px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-height: 260px;
            overflow-y: auto;
            z-index: 9999;
            padding: 6px;
        }
        .etim-sf-results.etim-sf-open {
            display: block;
        }
        .etim-sf-result-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            text-decoration: none;
            color: #334155;
            font-size: 13px;
            border-radius: 7px;
            transition: background 0.15s;
            cursor: pointer;
        }
        .etim-sf-result-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .etim-sf-item-active {
            background: #eef4fd;
            font-weight: 600;
        }
        .etim-sf-item-active:hover {
            background: #dde9fa;
        }
        .etim-sf-item-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 10px;
        }
        .etim-sf-item-count {
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            flex-shrink: 0;
        }
        .etim-sf-item-active .etim-sf-item-count {
            background: <?php echo esc_attr($filter_color); ?>;
            color: #fff;
        }
        .etim-sf-no-results {
            padding: 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        /* Scrollbar styling */
        .etim-sf-results::-webkit-scrollbar { width: 6px; }
        .etim-sf-results::-webkit-scrollbar-track { background: transparent; }
        .etim-sf-results::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .etim-sf-results::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        </style>

        <script>
        (function(){
            var input = document.getElementById('etim-sf-input');
            var results = document.getElementById('etim-sf-results');
            var wrapper = document.getElementById('etim-sf-wrapper');
            if (!input || !results) return;

            var items = results.querySelectorAll('.etim-sf-result-item');
            var noResults = document.getElementById('etim-sf-no-results');

            input.addEventListener('focus', function(){
                results.classList.add('etim-sf-open');
            });

            input.addEventListener('input', function(){
                var q = this.value.toLowerCase().trim();
                var visible = 0;
                for (var i = 0; i < items.length; i++) {
                    var name = items[i].getAttribute('data-name') || '';
                    if (!q || name.indexOf(q) !== -1) {
                        items[i].style.display = '';
                        visible++;
                    } else {
                        items[i].style.display = 'none';
                    }
                }
                noResults.style.display = visible === 0 ? '' : 'none';
                if (!results.classList.contains('etim-sf-open')) {
                    results.classList.add('etim-sf-open');
                }
            });

            document.addEventListener('click', function(e){
                if (!wrapper.contains(e.target)) {
                    results.classList.remove('etim-sf-open');
                }
            });
        })();
        </script>
        <?php
    }

    public function filter_product_query($q) {
        if (!is_admin() && $q->is_main_query() && (is_shop() || is_product_category() || is_product_taxonomy())) {
            if (!empty($_GET['etim_class'])) {
                $class_code = sanitize_text_field($_GET['etim_class']);

                global $wpdb;
                $classes_table = $wpdb->prefix . 'etim_product_classes';

                // Get all product IDs that have this ETIM class
                $product_ids = $wpdb->get_col($wpdb->prepare("SELECT product_id FROM $classes_table WHERE class_code = %s", $class_code));

                if (empty($product_ids)) {
                    $product_ids = [0]; // Force no results
                }

                $q->set('post__in', $product_ids);
            }
        }
    }
}
