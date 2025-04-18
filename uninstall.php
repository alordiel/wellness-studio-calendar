<?php
/**
 * Uninstall Wellness Studio Calendar
 *
 * @package Wellness Studio Calendar
 */

// If not called by WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define constants needed for the uninstall functions
define('WSC_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include the uninstall functionality
require_once WSC_PLUGIN_DIR . 'includes/admin/roles.php';

// Remove roles and capabilities
wsc_remove_roles_and_caps();

// Remove options
delete_option('wsc_calendar_page');
delete_option('wsc_version');
delete_option('wsc_db_version');

// Option to remove database tables (commented out by default)
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsc_exceptions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsc_reservations");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsc_events");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsc_locations");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsc_instructors");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsc_practice_class");
