<?php
/**
 * ETIM for WooCommerce Uninstall
 * 
 * Fired when the plugin is uninstalled.
 * 
 * @package ETIM_For_WooCommerce
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete plugin options
delete_option('etim_client_id');
delete_option('etim_client_secret');
delete_option('etim_scope');
delete_option('etim_default_language');
delete_option('etim_wc_version');

// Delete transients
delete_transient('etim_access_token');

// Drop custom database table
$table_name = $wpdb->prefix . 'etim_product_features';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete all product meta related to ETIM
$wpdb->delete(
    $wpdb->postmeta,
    ['meta_key' => '_etim_classification'],
    ['%s']
);

// Clear any cached data
wp_cache_flush();
