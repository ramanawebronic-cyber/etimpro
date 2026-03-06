<?php
/**
 * ETIM Product Tab Template (Frontend)
 *
 * Displays ETIM features on the single product page
 * Card-based accordion layout with filter settings color
 *
 * @var array $etim_data
 *
 * @package ETIM_For_WooCommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current locale for Swedish translations
$current_locale = get_locale();
$use_swedish = ($current_locale === 'sv_SE');

// Get user-configured color from ETIM settings
$etim_color = get_option('etim_filter_color', '#4888E8');

/**
 * Get localized description
 */
if (!function_exists('etim_get_description')) {
    function etim_get_description($item, $use_swedish = false) {
        if ($use_swedish && !empty($item['descriptionSv'])) {
            return $item['descriptionSv'];
        }
        return $item['description'] ?? '';
    }
}

/**
 * Get formatted value - handles all value types including arrays
 */
if (!function_exists('etim_get_formatted_value')) {
    function etim_get_formatted_value($feature, $use_swedish = false) {
        $type = strtolower($feature['type'] ?? 'alphanumeric');
        $value = $feature['assignedValue'] ?? '';

        if (empty($value) && $value !== '0' && $value !== false) {
            return '-';
        }

        // Handle array values (code + description pairs from DB)
        if (is_array($value)) {
            if (!empty($value['description'])) {
                return $value['description'];
            }
            if (!empty($value['code'])) {
                return $value['code'];
            }
            return '-';
        }

        $unit = $feature['unit'] ?? null;
        $unit_abbr = '';
        if (is_array($unit)) {
            $unit_abbr = $unit['abbreviation'] ?? '';
        }

        switch ($type) {
            case 'range':
                $values = explode('::', $value);
                $from = $values[0] ?? '';
                $to = $values[1] ?? '';
                if (empty($from) && empty($to)) {
                    return '-';
                }
                $formatted = sprintf('%s - %s', $from, $to);
                if ($unit_abbr) {
                    $formatted .= ' ' . $unit_abbr;
                }
                return $formatted;

            case 'numeric':
                $formatted = $value;
                if ($unit_abbr) {
                    $formatted .= ' ' . $unit_abbr;
                }
                return $formatted;

            case 'logical':
                return ($value === 'true' || $value === true || $value === '1')
                    ? __('Yes', 'etim-for-woocommerce')
                    : __('No', 'etim-for-woocommerce');

            case 'alphanumeric':
            default:
                // Check if value is a code that needs to be looked up
                if (!empty($feature['values']) && is_array($feature['values'])) {
                    foreach ($feature['values'] as $val) {
                        if (($val['code'] ?? '') === $value) {
                            return etim_get_description($val, $use_swedish);
                        }
                    }
                }
                return $value;
        }
    }
}
?>

<div class="etim-product-features etim-specs-container" style="--etim-accent: <?php echo esc_attr($etim_color); ?>;">
    <?php foreach ($etim_data as $index => $class) : ?>
        <?php
        // Check if class has any assigned features
        $has_assigned_features = false;
        $assigned_features = [];
        if (!empty($class['features'])) {
            foreach ($class['features'] as $feature) {
                $av = $feature['assignedValue'] ?? '';
                if (!empty($av) || $av === '0' || $av === false) {
                    $has_assigned_features = true;
                    $assigned_features[] = $feature;
                }
            }
        }

        if (!$has_assigned_features) {
            continue;
        }

        // Sort features by order number
        usort($assigned_features, function($a, $b) {
            $order_a = $a['orderNumber'] ?? 999;
            $order_b = $b['orderNumber'] ?? 999;
            return $order_a - $order_b;
        });

        $class_description = esc_html(etim_get_description($class, $use_swedish));
        $group_info = '';
        if (!empty($class['group'])) {
            $group_info = esc_html(etim_get_description($class['group'], $use_swedish));
        }
        $accordion_id = 'etim-accordion-' . $index;
        ?>

        <div class="etim-class-section etim-accordion-card">
            <!-- Accordion Header -->
            <div class="etim-accordion-header" onclick="(function(el){var c=el.closest('.etim-accordion-card');c.classList.toggle('etim-open');})(this)">
                <div class="etim-accordion-title-wrap">
                    <h3 class="etim-accordion-title"><?php echo $class_description; ?></h3>
                    <?php if ($group_info) : ?>
                        <p class="etim-accordion-subtitle">
                            <?php echo esc_html($class_description); ?>
                            (<?php esc_html_e('group', 'etim-for-woocommerce'); ?> : <?php echo $group_info; ?>)
                        </p>
                    <?php endif; ?>
                </div>
                <span class="etim-accordion-chevron">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </span>
            </div>

            <!-- Accordion Body - Feature Cards Grid -->
            <div class="etim-accordion-body">
                <div class="etim-features-card-grid">
                    <?php foreach ($assigned_features as $feature) : ?>
                        <div class="etim-feature-card-item">
                            <div class="etim-feature-card-name" style="color: <?php echo esc_attr($etim_color); ?>;">
                                <?php echo esc_html(etim_get_description($feature, $use_swedish)); ?>
                            </div>
                            <div class="etim-feature-card-value">
                                <?php echo esc_html(etim_get_formatted_value($feature, $use_swedish)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.etim-specs-container {
    margin: 20px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    color: #333;
    line-height: 1.6;
}

/* Accordion Card */
.etim-accordion-card {
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.etim-accordion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 28px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid transparent;
    user-select: none;
}

.etim-accordion-header:hover {
    background: #f3f4f6;
}

.etim-accordion-card.etim-open .etim-accordion-header {
    border-bottom-color: #e5e7eb;
}

.etim-accordion-title-wrap {
    flex: 1;
}

.etim-accordion-title {
    font-size: 1.1em !important;
    font-weight: 700 !important;
    color: #111827 !important;
    margin: 0 !important;
    padding: 0 !important;
}

.etim-accordion-subtitle {
    font-size: 0.85em;
    color: #6b7280 !important;
    margin: 4px 0 0 0 !important;
    padding: 0 !important;
}

.etim-accordion-chevron {
    color: #9ca3af;
    transition: transform 0.3s ease;
    flex-shrink: 0;
    margin-left: 16px;
}

.etim-accordion-card.etim-open .etim-accordion-chevron {
    transform: rotate(180deg);
}

/* Accordion Body */
.etim-accordion-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.3s ease;
    padding: 0 28px;
}

.etim-accordion-card.etim-open .etim-accordion-body {
    max-height: 2000px;
    padding: 24px 28px;
}

/* Feature Cards Grid - 2 columns */
.etim-features-card-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.etim-feature-card-item {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px 20px;
    transition: box-shadow 0.2s;
}

.etim-feature-card-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.etim-feature-card-name {
    font-size: 0.92em;
    font-weight: 600;
    color: var(--etim-accent, #4888E8);
    margin-bottom: 6px;
}

.etim-feature-card-value {
    font-size: 0.88em;
    font-weight: 500;
    color: #374151;
}

/* Auto-open first accordion */
.etim-accordion-card:first-child {
    /* Will be opened by JS below */
}

@media screen and (max-width: 768px) {
    .etim-features-card-grid {
        grid-template-columns: 1fr;
    }

    .etim-accordion-header {
        padding: 16px 20px;
    }

    .etim-accordion-card.etim-open .etim-accordion-body {
        padding: 16px 20px;
    }
}
</style>

<script>
(function(){
    // Auto-open first accordion
    var firstCard = document.querySelector('.etim-accordion-card');
    if (firstCard) firstCard.classList.add('etim-open');
})();
</script>
