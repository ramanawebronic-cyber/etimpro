<?php
/**
 * ETIM Product Tab Template (Frontend)
 * 
 * Displays ETIM features on the single product page
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

/**
 * Get localized description
 */
function etim_get_description($item, $use_swedish = false) {
    if ($use_swedish && !empty($item['descriptionSv'])) {
        return $item['descriptionSv'];
    }
    return $item['description'] ?? '';
}

/**
 * Get formatted value
 */
function etim_get_formatted_value($feature, $use_swedish = false) {
    $type = strtolower($feature['type'] ?? 'alphanumeric');
    $value = $feature['assignedValue'] ?? '';
    
    if (empty($value)) {
        return '-';
    }
    
    $unit = $feature['unit'] ?? null;
    $unit_abbr = $unit['abbreviation'] ?? '';
    
    switch ($type) {
        case 'range':
            $values = explode('::', $value);
            $from = $values[0] ?? '';
            $to = $values[1] ?? '';
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
            return ($value === 'true' || $value === true) 
                ? __('Yes', 'etim-for-woocommerce') 
                : __('No', 'etim-for-woocommerce');
            
        case 'alphanumeric':
        default:
            // Check if value is a code that needs to be looked up
            if (!empty($feature['values'])) {
                foreach ($feature['values'] as $val) {
                    if (($val['code'] ?? '') === $value) {
                        return etim_get_description($val, $use_swedish);
                    }
                }
            }
            return $value;
    }
}
?>

<div class="etim-product-features">
    <?php foreach ($etim_data as $class) : ?>
        <?php 
        // Check if class has any assigned features
        $has_assigned_features = false;
        if (!empty($class['features'])) {
            foreach ($class['features'] as $feature) {
                if (!empty($feature['assignedValue'])) {
                    $has_assigned_features = true;
                    break;
                }
            }
        }
        
        if (!$has_assigned_features) {
            continue;
        }
        ?>
        
        <div class="etim-class-section">
            <h3 class="etim-class-title">
                <?php echo esc_html(etim_get_description($class, $use_swedish)); ?>
                <span class="etim-class-code">(<?php echo esc_html($class['code']); ?>)</span>
            </h3>
            
            <?php if (!empty($class['group'])) : ?>
                <p class="etim-group-info">
                    <strong><?php esc_html_e('Group:', 'etim-for-woocommerce'); ?></strong>
                    <?php echo esc_html(etim_get_description($class['group'], $use_swedish)); ?>
                    <span class="etim-group-code">(<?php echo esc_html($class['group']['code'] ?? ''); ?>)</span>
                </p>
            <?php endif; ?>
            
            <table class="etim-features-table">
                <thead>
                    <tr>
                        <th class="etim-feature-name"><?php esc_html_e('Feature', 'etim-for-woocommerce'); ?></th>
                        <th class="etim-feature-value"><?php esc_html_e('Value', 'etim-for-woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Sort features by order number if available
                    $features = $class['features'];
                    usort($features, function($a, $b) {
                        $order_a = $a['orderNumber'] ?? 999;
                        $order_b = $b['orderNumber'] ?? 999;
                        return $order_a - $order_b;
                    });
                    
                    foreach ($features as $feature) : 
                        if (empty($feature['assignedValue'])) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td class="etim-feature-name">
                                <?php echo esc_html(etim_get_description($feature, $use_swedish)); ?>
                                <span class="etim-feature-code"><?php echo esc_html($feature['code']); ?></span>
                            </td>
                            <td class="etim-feature-value">
                                <?php echo esc_html(etim_get_formatted_value($feature, $use_swedish)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<style>
.etim-product-features {
    margin: 20px 0;
}

.etim-class-section {
    margin-bottom: 30px;
}

.etim-class-title {
    font-size: 1.2em;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.etim-class-code,
.etim-group-code,
.etim-feature-code {
    color: #666;
    font-size: 0.85em;
    font-weight: normal;
}

.etim-group-info {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 15px;
}

.etim-features-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.etim-features-table th,
.etim-features-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.etim-features-table th {
    background-color: #f8f8f8;
    font-weight: 600;
    color: #333;
}

.etim-features-table tr:hover {
    background-color: #fafafa;
}

.etim-features-table .etim-feature-name {
    width: 50%;
}

.etim-features-table .etim-feature-value {
    width: 50%;
}

.etim-features-table .etim-feature-code {
    display: block;
    margin-top: 3px;
}

@media screen and (max-width: 768px) {
    .etim-features-table th,
    .etim-features-table td {
        padding: 10px;
        font-size: 0.9em;
    }
    
    .etim-features-table .etim-feature-name,
    .etim-features-table .etim-feature-value {
        width: auto;
    }
}
</style>
