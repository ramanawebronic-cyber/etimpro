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
    private $secret_key = '69a6cf32a9baf7.29427578';
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
        $this->init_hooks();
        add_action('admin_init', [$this, 'check_and_deactivate_expired_license'], 1);
        add_action('admin_init', [$this, 'refresh_plan_status'], 2);
        // Also refresh plan status on frontend pages so product reference changes
        // are detected immediately when a user purchases a new plan
        add_action('init', [$this, 'refresh_plan_status'], 20);
    }

    /**
     * Refresh plan status on every page load (admin + frontend)
     * Re-reads the product reference from the license database to detect plan changes
     * when a user activates a new license key for a different plan.
     */
    public function refresh_plan_status() {
        // Prevent running multiple times per request
        static $already_refreshed = false;
        if ($already_refreshed) {
            return;
        }
        $already_refreshed = true;

        $license_key = $this->get_stored_license();
        if (empty($license_key)) {
            $this->set_plan_status(self::PLAN_FREE);
            return;
        }

        $status = get_option('etim_lic_status', 'invalid');
        if ($status !== 'valid') {
            $this->set_plan_status(self::PLAN_FREE);
            return;
        }

        // Re-read product reference from SLM database to detect plan changes
        $check_data = $this->slm_check_direct($license_key);
        if (isset($check_data['result']) && $check_data['result'] === 'success' && !empty($check_data['product_ref'])) {
            $current_ref = get_option('etim_actual_product_ref', '');
            $new_ref = sanitize_text_field($check_data['product_ref']);
            if ($current_ref !== $new_ref) {
                update_option('etim_actual_product_ref', $new_ref);
            }
        }

        $detected_plan = $this->detect_plan_from_product_reference();
        $this->set_plan_status($detected_plan);
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
     * Notify SLM about deactivation (direct DB)
     */
    private function notify_api_deactivation($license_key) {
        $domain = $this->get_domain();
        $this->slm_deactivate_direct($license_key, $domain);
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
     * License check via direct SLM database query (avoids loopback HTTP timeout)
     */
    private function remote_check($license_key) {
        if (empty($license_key)) {
            return ['status' => 'invalid', 'message' => 'No license key provided'];
        }

        $data = $this->slm_check_direct($license_key);

        if (!empty($data['product_ref'])) {
            update_option('etim_actual_product_ref', sanitize_text_field($data['product_ref']));
        }

        return $data;
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
     * Activate license via direct SLM database query
     */
    public function activate($license_key) {
        $license_key = trim($license_key);

        if (empty($license_key)) {
            return new WP_Error('empty_key', 'Please enter a license key.');
        }

        $domain = $this->get_domain();

        // Pre-activation check for expiry
        $check_data = $this->slm_check_direct($license_key);

        if ($check_data['result'] === 'success') {
            if (!empty($check_data['product_ref'])) {
                update_option('etim_actual_product_ref', sanitize_text_field($check_data['product_ref']));
            }

            if (!empty($check_data['date_expiry'])) {
                $expiry_date = $check_data['date_expiry'];
                if ($this->is_date_expired($expiry_date)) {
                    return new WP_Error(
                        'license_expired',
                        'This license has expired on ' . date('F j, Y', strtotime($expiry_date)) . '. Please renew your license to activate.'
                    );
                }
            }
        }

        // Activate via direct DB
        $data = $this->slm_activate_direct($license_key, $domain);

        if ($data['result'] === 'success') {
            if (!empty($check_data['product_ref'])) {
                update_option('etim_actual_product_ref', sanitize_text_field($check_data['product_ref']));
            }

            $this->store_license($license_key, 'valid');

            if (!empty($check_data['date_expiry'])) {
                update_option('etim_lic_expiry', $check_data['date_expiry']);
            }

            return true;
        }

        // Handle "already in use" on this domain as success
        if (isset($data['message']) && strpos(strtolower($data['message']), 'already') !== false) {
            $this->store_license($license_key, 'valid');

            if (!empty($check_data['date_expiry'])) {
                update_option('etim_lic_expiry', $check_data['date_expiry']);
            }

            return true;
        }

        $message = isset($data['message']) ? $data['message'] : 'License activation failed.';
        error_log('ETIM License: Activation failed - ' . $message);
        return new WP_Error('activation_failed', $message);
    }

    /**
     * Deactivate license via direct SLM database query
     */
    public function deactivate() {
        $license_key = $this->get_stored_license();

        if (empty($license_key)) {
            return new WP_Error('no_license', 'No license key found.');
        }

        $domain = $this->get_domain();
        $this->slm_deactivate_direct($license_key, $domain);
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

        if (strpos($ref, 'erp') !== false || strpos($ref, 'agency') !== false) {
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
                return 'WooCommerce Agency';
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

    // ========================================================================
    // DIRECT SLM DATABASE QUERIES (avoids loopback HTTP timeout)
    // ========================================================================

    /**
     * Verify SLM secret key matches the one stored in SLM plugin options
     */
    private function verify_slm_secret_key() {
        $slm_options = get_option('slm_plugin_options', []);
        $right_key   = isset($slm_options['lic_verification_secret']) ? $slm_options['lic_verification_secret'] : '';
        return ($this->secret_key === $right_key);
    }

    /**
     * Direct SLM check - queries the SLM database tables directly
     */
    private function slm_check_direct($license_key) {
        global $wpdb;

        if (!$this->verify_slm_secret_key()) {
            return ['result' => 'error', 'message' => 'Verification API secret key is invalid'];
        }

        $tbl_keys    = $wpdb->prefix . 'lic_key_tbl';
        $tbl_domains = $wpdb->prefix . 'lic_reg_domain_tbl';

        $license = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $tbl_keys WHERE license_key = %s", $license_key)
        );

        if (!$license) {
            return ['result' => 'error', 'message' => 'Invalid license key'];
        }

        $reg_domains = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $tbl_domains WHERE lic_key = %s", $license_key)
        );

        return [
            'result'              => 'success',
            'message'             => 'License key details retrieved.',
            'license_key'         => $license->license_key,
            'status'              => $license->lic_status,
            'max_allowed_domains' => $license->max_allowed_domains,
            'email'               => $license->email,
            'registered_domains'  => $reg_domains,
            'date_created'        => $license->date_created,
            'date_renewed'        => $license->date_renewed,
            'date_expiry'         => $license->date_expiry,
            'date'                => date('Y-m-d'),
            'product_ref'         => $license->product_ref,
            'first_name'          => $license->first_name,
            'last_name'           => $license->last_name,
            'company_name'        => $license->company_name,
            'txn_id'              => $license->txn_id,
        ];
    }

    /**
     * Direct SLM activate - registers domain in SLM database
     */
    private function slm_activate_direct($license_key, $domain) {
        global $wpdb;

        if (!$this->verify_slm_secret_key()) {
            return ['result' => 'error', 'message' => 'Verification API secret key is invalid'];
        }

        $tbl_keys    = $wpdb->prefix . 'lic_key_tbl';
        $tbl_domains = $wpdb->prefix . 'lic_reg_domain_tbl';

        $license = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $tbl_keys WHERE license_key = %s", $license_key)
        );

        if (!$license) {
            return ['result' => 'error', 'message' => 'Invalid license key'];
        }

        if ($license->lic_status === 'blocked') {
            return ['result' => 'error', 'message' => 'Your License key is blocked'];
        }

        if ($license->lic_status === 'expired') {
            return ['result' => 'error', 'message' => 'Your License key has expired'];
        }

        $reg_domains = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $tbl_domains WHERE lic_key = %s", $license_key)
        );

        // Check if domain is already registered
        foreach ($reg_domains as $reg_domain) {
            if ($domain === $reg_domain->registered_domain) {
                return ['result' => 'error', 'message' => 'License key already in use on ' . $domain];
            }
        }

        // Check max domains
        if (count($reg_domains) >= floor($license->max_allowed_domains)) {
            return ['result' => 'error', 'message' => 'Reached maximum allowable domains'];
        }

        // Register the domain
        $wpdb->insert($tbl_domains, [
            'lic_key_id'        => $license->id,
            'lic_key'           => $license_key,
            'registered_domain' => $domain,
            'item_reference'    => $this->product_reference,
        ]);

        // Insert into permanent history table if it exists
        $tbl_permanent = $wpdb->prefix . 'lic_reg_domain_tbl_permanent';
        if ($wpdb->get_var("SHOW TABLES LIKE '$tbl_permanent'") === $tbl_permanent) {
            $wpdb->insert($tbl_permanent, [
                'lic_key_id'        => $license->id,
                'lic_key'           => $license_key,
                'registered_domain' => $domain,
                'item_reference'    => $this->product_reference,
            ]);
        }

        // Update license status to active
        $wpdb->update($tbl_keys, ['lic_status' => 'active'], ['id' => $license->id]);

        return ['result' => 'success', 'message' => 'License key activated'];
    }

    /**
     * Direct SLM deactivate - removes domain from SLM database
     */
    private function slm_deactivate_direct($license_key, $domain) {
        global $wpdb;

        $tbl_domains = $wpdb->prefix . 'lic_reg_domain_tbl';

        $wpdb->delete($tbl_domains, [
            'lic_key'           => $license_key,
            'registered_domain' => $domain,
        ], ['%s', '%s']);
    }
}
