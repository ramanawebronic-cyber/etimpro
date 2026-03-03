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

<div class="etim-product-features etim-specs-container">
    <?php foreach ($etim_data as $class) : ?>
        <?php
        // Check if class has any assigned features
        $has_assigned_features = false;
        if (!empty($class['features'])) {
            foreach ($class['features'] as $feature) {
                $av = $feature['assignedValue'] ?? '';
                if (!empty($av) || $av === '0' || $av === false) {
                    $has_assigned_features = true;
                    break;
                }
            }
        }

        if (!$has_assigned_features) {
            continue;
        }
        ?>

        <div class="etim-class-section etim-premium-card">
            <div class="etim-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4888E8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <line x1="10" y1="9" x2="8" y2="9"></line>
                </svg>
            </div>

            <h3 class="etim-class-title">
                <?php echo esc_html(etim_get_description($class, $use_swedish)); ?>
                <span class="etim-class-code">(<?php echo esc_html($class['code']); ?>)</span>
            </h3>

            <?php if (!empty($class['group'])) : ?>
                <p class="etim-group-info">
                    <?php esc_html_e('Group:', 'etim-for-woocommerce'); ?>
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
                    $features = $class['features'];
                    usort($features, function($a, $b) {
                        $order_a = $a['orderNumber'] ?? 999;
                        $order_b = $b['orderNumber'] ?? 999;
                        return $order_a - $order_b;
                    });

                    foreach ($features as $feature) :
                        $av = $feature['assignedValue'] ?? '';
                        if (empty($av) && $av !== '0' && $av !== false) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td class="etim-feature-name">
                                <span class="etim-feature-name-text"><?php echo esc_html(etim_get_description($feature, $use_swedish)); ?></span>
                                <span class="etim-feature-code-sub">(<?php echo esc_html($feature['code']); ?>)</span>
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
.etim-specs-container {
    margin: 20px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    color: #333;
    line-height: 1.6;
}

.etim-premium-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 6px 16px rgba(0, 0, 0, 0.04);
    padding: 32px;
    margin-bottom: 24px;
    border: 1px solid #e8edf2;
}

.etim-card-icon {
    text-align: center;
    margin-bottom: 16px;
}

.etim-card-icon svg {
    width: 32px;
    height: 32px;
}

.etim-class-title {
    font-size: 1.35em !important;
    font-weight: 700 !important;
    color: #0b3d6e !important;
    margin: 0 0 12px 0 !important;
    padding-bottom: 12px;
    border-bottom: 2px solid #e8edf2;
    text-align: left;
    display: flex;
    align-items: baseline;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 8px;
}

.etim-class-code {
    color: #94a3b8;
    font-size: 0.7em;
    font-weight: 400;
}

.etim-group-info {
    color: #475569 !important;
    font-size: 0.9em;
    margin: 0 0 20px 0 !important;
}

.etim-group-code {
    color: #94a3b8;
    font-size: 0.85em;
}

.etim-features-table {
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin-top: 8px;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px;
    overflow: hidden;
}

.etim-features-table th,
.etim-features-table td {
    padding: 14px 20px !important;
    text-align: left !important;
    font-size: 0.92em;
    vertical-align: middle !important;
    border: none !important;
}

.etim-features-table th {
    background: linear-gradient(135deg, #f1f5f9 0%, #e8edf2 100%) !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    border-bottom: 2px solid #e2e8f0 !important;
    font-size: 0.85em;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.etim-features-table tbody tr {
    transition: background-color 0.15s ease;
}

.etim-features-table tbody tr:nth-child(even) {
    background-color: #f8fafc !important;
}

.etim-features-table tbody tr:nth-child(odd) {
    background-color: #ffffff !important;
}

.etim-features-table tbody tr:hover {
    background-color: #f0f4ff !important;
}

.etim-features-table td {
    border-bottom: 1px solid #f1f5f9 !important;
    color: #334155 !important;
}

.etim-features-table tbody tr:last-child td {
    border-bottom: none !important;
}

.etim-feature-name {
    width: 48%;
    border-right: 1px solid #f1f5f9 !important;
}

.etim-feature-name-text {
    display: block;
    color: #1e293b;
    font-weight: 500;
}

.etim-feature-code-sub {
    display: block;
    color: #94a3b8;
    font-size: 0.78em;
    margin-top: 2px;
}

td.etim-feature-value {
    font-weight: 600;
    color: #0f172a !important;
}

@media screen and (max-width: 768px) {
    .etim-premium-card {
        padding: 20px;
        border-radius: 8px;
    }

    .etim-features-table th,
    .etim-features-table td {
        padding: 10px 14px !important;
    }

    .etim-class-title {
        font-size: 1.15em !important;
    }
}
</style>
