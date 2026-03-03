<?php
/**
 * ETIM License Manager - SLM License Verification
 *
 * @package ETIM_For_WooCommerce
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class ETIM_License_Manager {

    /**
     * License API configuration
     */
    private $api_url;
    private $secret_key = '69a5869269b1b3.09401983';
    private $product_reference = 'etim-for-woocommerce';

    /**
     * Plan constants
     */
    const PLAN_FREE         = 0;
    const PLAN_MANUFACTURER = 1;
    const PLAN_DISTRIBUTOR  = 2;
    const PLAN_ERP          = 3;

    /**
     * Cache duration (12 hours)
     */
    const CACHE_DURATION = 43200;

    /**
     * Grace period (7 days)
     */
    const GRACE_PERIOD = 604800;

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Set API URL to the WordPress site URL where SLM is installed
        $this->api_url = home_url('/');
        $this->init_hooks();
        add_action('admin_init', [$this, 'check_and_deactivate_expired_license'], 1);
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_filter('etim_is_licensed', [$this, 'is_valid']);
        add_action('admin_notices', [$this, 'license_notices']);
        add_action('wp_ajax_etim_activate_license', [$this, 'ajax_activate_license']);
        add_action('wp_ajax_etim_deactivate_license', [$this, 'ajax_deactivate_license']);
    }

    // ========================================================================
    // AJAX HANDLERS
    // ========================================================================

    /**
     * AJAX: Activate license
     */
    public function ajax_activate_license() {
        check_ajax_referer('etim_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $license_key = sanitize_text_field(wp_unslash($_POST['license_key'] ?? ''));

        if (empty($license_key)) {
            wp_send_json_error(['message' => 'Please enter a license key.']);
        }

        $result = $this->activate($license_key);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        $info = $this->get_license_info();
        wp_send_json_success([
            'message' => 'License activated successfully!',
            'info'    => $info,
        ]);
    }

    /**
     * AJAX: Deactivate license
     */
    public function ajax_deactivate_license() {
        check_ajax_referer('etim_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $result = $this->deactivate();

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => 'License deactivated successfully.']);
    }

    // ========================================================================
    // AUTO EXPIRY CHECK
    // ========================================================================

    /**
     * Check and auto-deactivate expired license on every admin page load
     */
    public function check_and_deactivate_expired_license() {
        $license_key = get_option('etim_lic_key', '');

        if (empty($license_key)) {
            return;
        }

        $expiry_date = get_option('etim_lic_expiry', '');

        if (empty($expiry_date) || $expiry_date === '0000-00-00') {
            return;
        }

        if ($this->is_date_expired($expiry_date)) {
            $this->force_deactivate_license();
        }
    }

    /**
     * Check if a date has expired
     */
    private function is_date_expired($expiry_date) {
        if (empty($expiry_date) || $expiry_date === '0000-00-00') {
            return false;
        }

        $current_timestamp = strtotime(current_time('Y-m-d'));
        $expiry_timestamp  = strtotime($expiry_date);

        return $current_timestamp > $expiry_timestamp;
    }

    /**
     * Get days remaining until expiry
     */
    private function get_days_remaining($expiry_date) {
        if (empty($expiry_date) || $expiry_date === '0000-00-00') {
            return 999999;
        }

        $current_timestamp = strtotime(current_time('Y-m-d'));
        $expiry_timestamp  = strtotime($expiry_date);

        $diff = $expiry_timestamp - $current_timestamp;
        return (int) floor($diff / 86400);
    }

    /**
     * Force deactivate license (no API call needed)
     */
    private function force_deactivate_license() {
        $license_key = $this->get_stored_license();

        $this->clear_license();

        update_option('etim_lic_auto_expired', true, false);
        update_option('etim_lic_expired_date', current_time('mysql'), false);

        if (!empty($license_key)) {
            $this->notify_api_deactivation($license_key);
        }
    }

    /**
     * Notify API about deactivation (non-blocking)
     */
    private function notify_api_deactivation($license_key) {
        $domain = $this->get_domain();

        $api_params = [
            'slm_action'        => 'slm_deactivate',
            'secret_key'        => $this->secret_key,
            'license_key'       => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => $this->product_reference,
        ];

        wp_remote_post($this->api_url, [
            'timeout'   => 5,
            'blocking'  => false,
            'body'      => $api_params,
            'sslverify' => false,
        ]);
    }

    // ========================================================================
    // ENCRYPTED STORAGE
    // ========================================================================

    /**
     * Encrypt license key
     */
    private function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $key = $this->get_encryption_key();
        $iv  = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt license key
     */
    private function decrypt($data) {
        if (empty($data)) {
            return '';
        }

        $key   = $this->get_encryption_key();
        $parts = @explode('::', base64_decode($data), 2);

        if (count($parts) !== 2) {
            return '';
        }

        list($encrypted_data, $iv) = $parts;
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }

    /**
     * Get encryption key
     */
    private function get_encryption_key() {
        if (defined('AUTH_KEY') && AUTH_KEY) {
            return substr(hash('sha256', AUTH_KEY), 0, 32);
        }
        return substr(hash('sha256', 'etim-wc-fallback'), 0, 32);
    }

    /**
     * Store license key (encrypted)
     */
    private function store_license($license_key, $status = 'valid') {
        $encrypted = $this->encrypt($license_key);

        update_option('etim_lic_key', $encrypted, false);
        update_option('etim_lic_status', $status, false);
        update_option('etim_lic_domain', $this->get_domain(), false);
        update_option('etim_lic_last_check', time(), false);

        delete_option('etim_lic_auto_expired');
        delete_option('etim_lic_expired_date');

        $detected_plan = $this->detect_plan_from_product_reference();
        $this->set_plan_status($detected_plan);

        $this->store_integrity_hash($license_key);

        delete_transient('etim_lic_cache');
    }

    /**
     * Get stored license key (decrypted)
     */
    private function get_stored_license() {
        $encrypted = get_option('etim_lic_key', '');
        return $this->decrypt($encrypted);
    }

    /**
     * Mask license key for display
     */
    private function mask_license($license_key) {
        if (empty($license_key) || strlen($license_key) < 12) {
            return '***';
        }
        return substr($license_key, 0, 8) . '****' . substr($license_key, -4);
    }

    /**
     * Set plan status
     */
    private function set_plan_status($status) {
        $status = (int) $status;
        update_option('etim_user_plan', $status, false);
    }

    // ========================================================================
    // INTEGRITY CHECKS
    // ========================================================================

    /**
     * Store integrity hash
     */
    private function store_integrity_hash($license_key) {
        if (defined('AUTH_KEY')) {
            $hash = hash_hmac('sha256', $license_key, AUTH_KEY);
            update_option('etim_lic_hash', $hash, false);
        }
    }

    /**
     * Verify integrity
     */
    private function verify_integrity() {
        $license_key = $this->get_stored_license();
        $stored_hash = get_option('etim_lic_hash', '');

        if (empty($license_key) || empty($stored_hash) || !defined('AUTH_KEY')) {
            return false;
        }

        $computed_hash = hash_hmac('sha256', $license_key, AUTH_KEY);
        return hash_equals($stored_hash, $computed_hash);
    }

    // ========================================================================
    // DOMAIN LOCKING
    // ========================================================================

    /**
     * Get current domain
     */
    private function get_domain() {
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $domain = preg_replace('/^www\./', '', $domain);
        return strtolower($domain);
    }

    /**
     * Verify domain lock
     */
    private function verify_domain() {
        $activated_domain = get_option('etim_lic_domain', '');
        $current_domain   = $this->get_domain();

        if (empty($activated_domain)) {
            return true;
        }

        return $activated_domain === $current_domain;
    }

    // ========================================================================
    // REMOTE VALIDATION
    // ========================================================================

    /**
     * Remote license check
     */
    private function remote_check($license_key) {
        if (empty($license_key)) {
            return ['status' => 'invalid', 'message' => 'No license key provided'];
        }

        $domain = $this->get_domain();

        $api_params = [
            'slm_action'        => 'slm_check',
            'secret_key'        => $this->secret_key,
            'license_key'       => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => $this->product_reference,
        ];

        $response = wp_remote_post($this->api_url, [
            'timeout'   => 15,
            'body'      => $api_params,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            return [
                'status'  => 'error',
                'message' => $response->get_error_message(),
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['product_ref'])) {
            update_option('etim_actual_product_ref', sanitize_text_field($data['product_ref']));
        }

        return $data ? $data : ['status' => 'invalid', 'message' => 'Invalid response'];
    }

    // ========================================================================
    // CACHE SYSTEM
    // ========================================================================

    private function get_cached_status() {
        return get_transient('etim_lic_cache');
    }

    private function set_cached_status($status) {
        set_transient('etim_lic_cache', $status, self::CACHE_DURATION);
    }

    // ========================================================================
    // GRACE PERIOD
    // ========================================================================

    private function in_grace_period() {
        $last_check  = get_option('etim_lic_last_check', 0);
        $last_status = get_option('etim_lic_status', 'invalid');

        if ($last_status === 'valid' && (time() - $last_check) < self::GRACE_PERIOD) {
            return true;
        }

        return false;
    }

    // ========================================================================
    // LICENSE VALIDATION
    // ========================================================================

    /**
     * Validate license - main validation method
     */
    public function is_valid() {
        delete_transient('etim_lic_cache');

        $license_key = $this->get_stored_license();

        $expiry_date = get_option('etim_lic_expiry', '');

        if ($this->is_date_expired($expiry_date)) {
            $this->force_deactivate_license();
            return false;
        }

        if (empty($license_key)) {
            $this->set_plan_status(self::PLAN_FREE);
            return false;
        }

        if (!$this->verify_integrity()) {
            $this->store_license('', 'invalid');
            return false;
        }

        if (!$this->verify_domain()) {
            $this->set_plan_status(self::PLAN_FREE);
            return false;
        }

        $response = $this->remote_check($license_key);

        if (isset($response['result']) && $response['result'] === 'success') {
            if (!empty($response['date_expiry'])) {
                update_option('etim_lic_expiry', $response['date_expiry']);
            }

            $this->set_cached_status('valid');
            update_option('etim_lic_status', 'valid');
            update_option('etim_lic_last_check', time());

            $detected_plan = $this->detect_plan_from_product_reference();
            $this->set_plan_status($detected_plan);

            return true;
        }

        if ($this->in_grace_period()) {
            return true;
        }

        $this->set_cached_status('invalid');
        update_option('etim_lic_status', 'invalid');
        $this->set_plan_status(self::PLAN_FREE);

        return false;
    }

    // ========================================================================
    // ACTIVATE / DEACTIVATE
    // ========================================================================

    /**
     * Activate license
     */
    public function activate($license_key) {
        $license_key = trim($license_key);

        if (empty($license_key)) {
            return new WP_Error('empty_key', 'Please enter a license key.');
        }

        $domain = $this->get_domain();

        // Pre-activation check for expiry
        $check_params = [
            'slm_action'        => 'slm_check',
            'secret_key'        => $this->secret_key,
            'license_key'       => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => $this->product_reference,
        ];

        $check_response = wp_remote_post($this->api_url, [
            'timeout'   => 15,
            'body'      => $check_params,
            'sslverify' => false,
        ]);

        if (!is_wp_error($check_response)) {
            $check_body = wp_remote_retrieve_body($check_response);
            $check_data = json_decode($check_body, true);

            if (!empty($check_data['product_ref'])) {
                update_option('etim_actual_product_ref', sanitize_text_field($check_data['product_ref']));
            }

            if (isset($check_data['date_expiry']) && !empty($check_data['date_expiry'])) {
                $expiry_date = $check_data['date_expiry'];
                if ($this->is_date_expired($expiry_date)) {
                    return new WP_Error(
                        'license_expired',
                        'This license has expired on ' . date('F j, Y', strtotime($expiry_date)) . '. Please renew your license to activate.'
                    );
                }
            }
        }

        // Activate
        $api_params = [
            'slm_action'        => 'slm_activate',
            'secret_key'        => $this->secret_key,
            'license_key'       => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => $this->product_reference,
        ];

        $response = wp_remote_post($this->api_url, [
            'timeout'   => 15,
            'body'      => $api_params,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            error_log('ETIM License: Activation HTTP error - ' . $response->get_error_message());
            return new WP_Error('connection_error', 'Could not connect to license server: ' . $response->get_error_message());
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($http_code !== 200) {
            error_log('ETIM License: Activation returned HTTP ' . $http_code . ' - Body: ' . $body);
            return new WP_Error('http_error', 'License server returned HTTP ' . $http_code);
        }

        $data = json_decode($body, true);

        if (!$data || !isset($data['result'])) {
            error_log('ETIM License: Invalid response body - ' . $body);
            return new WP_Error('invalid_response', 'Invalid response from license server. Please check your license key and try again.');
        }

        if ($data['result'] === 'success') {
            if (!empty($data['product_ref'])) {
                update_option('etim_actual_product_ref', sanitize_text_field($data['product_ref']));
            }

            $this->store_license($license_key, 'valid');

            if (isset($data['date_expiry'])) {
                update_option('etim_lic_expiry', $data['date_expiry']);
            }

            return true;
        }

        // Handle "already activated" as success - domain may already be registered
        if (isset($data['message']) && strpos(strtolower($data['message']), 'already') !== false) {
            $this->store_license($license_key, 'valid');

            if (isset($data['date_expiry'])) {
                update_option('etim_lic_expiry', $data['date_expiry']);
            }

            return true;
        }

        $message = isset($data['message']) ? $data['message'] : 'License activation failed.';
        error_log('ETIM License: Activation failed - ' . $message);
        return new WP_Error('activation_failed', $message);
    }

    /**
     * Deactivate license
     */
    public function deactivate() {
        $license_key = $this->get_stored_license();

        if (empty($license_key)) {
            return new WP_Error('no_license', 'No license key found.');
        }

        $domain = $this->get_domain();

        $api_params = [
            'slm_action'        => 'slm_deactivate',
            'secret_key'        => $this->secret_key,
            'license_key'       => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => $this->product_reference,
        ];

        wp_remote_post($this->api_url, [
            'timeout'   => 15,
            'body'      => $api_params,
            'sslverify' => false,
        ]);

        $this->clear_license();

        return true;
    }

    /**
     * Clear license data
     */
    private function clear_license() {
        delete_option('etim_lic_key');
        delete_option('etim_lic_status');
        delete_option('etim_lic_domain');
        delete_option('etim_lic_hash');
        delete_option('etim_lic_expiry');
        delete_option('etim_lic_last_check');
        delete_option('etim_actual_product_ref');
        delete_transient('etim_lic_cache');

        $this->set_plan_status(self::PLAN_FREE);
    }

    /**
     * Get license info for display
     */
    public function get_license_info() {
        $license_key = $this->get_stored_license();

        if (empty($license_key)) {
            return [
                'status'            => 'inactive',
                'masked_key'        => '',
                'domain'            => '',
                'expiry'            => '',
                'expiry_formatted'  => '',
                'days_remaining'    => 0,
                'plan_code'         => $this->get_plan_code(),
                'plan_name'         => $this->get_plan_name(),
                'last_check'        => 0,
                'auto_expired'      => get_option('etim_lic_auto_expired', false),
            ];
        }

        $lic_status  = get_option('etim_lic_status', 'invalid');
        $expiry_date = get_option('etim_lic_expiry', '');

        return [
            'status'            => $lic_status === 'valid' ? 'active' : 'inactive',
            'masked_key'        => $this->mask_license($license_key),
            'domain'            => get_option('etim_lic_domain', ''),
            'expiry'            => $expiry_date,
            'expiry_formatted'  => (!empty($expiry_date) && $expiry_date !== '0000-00-00')
                                    ? date('F j, Y', strtotime($expiry_date))
                                    : 'Lifetime',
            'days_remaining'    => $this->get_days_remaining($expiry_date),
            'plan_code'         => $this->get_plan_code(),
            'plan_name'         => $this->get_plan_name(),
            'last_check'        => get_option('etim_lic_last_check', 0),
            'auto_expired'      => get_option('etim_lic_auto_expired', false),
        ];
    }

    // ========================================================================
    // PLAN DETECTION
    // ========================================================================

    /**
     * Detect plan from product reference
     */
    public function detect_plan_from_product_reference() {
        $ref = get_option('etim_actual_product_ref', '');
        if (empty($ref)) {
            $ref = $this->product_reference;
        }
        $ref = strtolower(trim($ref));

        if (strpos($ref, 'erp') !== false) {
            return self::PLAN_ERP;
        }
        if (strpos($ref, 'distributor') !== false || strpos($ref, 'wholesale') !== false) {
            return self::PLAN_DISTRIBUTOR;
        }
        if (strpos($ref, 'manufacturer') !== false || $ref === 'etim-for-woocommerce') {
            return self::PLAN_MANUFACTURER;
        }

        return self::PLAN_FREE;
    }

    /**
     * Get plan code
     */
    public function get_plan_code() {
        $stored = get_option('etim_user_plan', null);
        if ($stored !== null) {
            return (int) $stored;
        }
        return $this->detect_plan_from_product_reference();
    }

    /**
     * Get plan name
     */
    public function get_plan_name() {
        return $this->get_plan_name_by_code($this->get_plan_code());
    }

    /**
     * Map code to name
     */
    private function get_plan_name_by_code($code) {
        switch ((int) $code) {
            case self::PLAN_ERP:
                return 'ERP';
            case self::PLAN_DISTRIBUTOR:
                return 'Distributor';
            case self::PLAN_MANUFACTURER:
                return 'Manufacturer';
            case self::PLAN_FREE:
            default:
                return 'Free';
        }
    }

    /**
     * Check if user has an active license
     */
    public function has_active_license() {
        $status = get_option('etim_lic_status', 'invalid');
        $key    = get_option('etim_lic_key', '');
        return ($status === 'valid' && !empty($key));
    }

    // ========================================================================
    // ADMIN NOTICES
    // ========================================================================

    /**
     * Show license notices
     */
    public function license_notices() {
        $is_auto_expired = get_option('etim_lic_auto_expired', false);

        if ($is_auto_expired) {
            $expired_date = get_option('etim_lic_expired_date', '');
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>ETIM for WooCommerce - License Expired:</strong> Your license expired on ' . esc_html(date('F j, Y', strtotime($expired_date))) . ' and has been automatically deactivated. ';
            echo '<a href="' . esc_url(admin_url('admin.php?page=etim-settings#tab-license')) . '" style="font-weight:600;">Renew License Now</a></p>';
            echo '</div>';
        }
    }
}
