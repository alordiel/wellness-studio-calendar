<?php
/**
 * Plugin Name: Wellness Studio Calendar
 * Description: Creates simple and easy to manage events calendar for Yoga and Wellness studios
 * Version: 1.0.0
 * Author: Alex Vi
 * License: GPL v2 or later
 * Text Domain: wellness-studio-calendar
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define plugin constants
const WSC_PLUGIN_VERSION = '1.0.0';
define( 'WSC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


// Include required files
require_once WSC_PLUGIN_DIR . 'includes/admin/admin-page.php';
require_once WSC_PLUGIN_DIR . 'includes/front-end/template-functions.php';
require_once WSC_PLUGIN_DIR . 'includes/database.php';
require_once WSC_PLUGIN_DIR . 'includes/enqueue-scripts.php';

// Register activation hook
register_activation_hook(__FILE__, 'wsc_activate_plugin');

// Register deactivation hook
register_deactivation_hook(__FILE__, 'wsc_deactivate_plugin');

/**
 * Actions to perform on plugin activation
 */
function wsc_activate_plugin() {

    require_once WSC_PLUGIN_DIR . 'includes/database.php';
	require_once WSC_PLUGIN_DIR . 'includes/admin/roles.php';

    wsc_create_database_tables();
    wsc_create_calendar_page();
	wsc_register_roles_and_caps();

    // Flush rewrite rules after registering custom post types/templates
    flush_rewrite_rules();
}

/**
 * Actions to perform on plugin deactivation
 */
function wsc_deactivate_plugin() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Load plugin text domain for translations
 */
function wsc_load_textdomain() {
    load_plugin_textdomain('wellness-studio-calendar', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wsc_load_textdomain');
