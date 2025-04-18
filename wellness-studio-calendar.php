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

// Register activation hook
register_activation_hook(__FILE__, 'wsc_activate_plugin');

// Register deactivation hook
register_deactivation_hook(__FILE__, 'wsc_deactivate_plugin');

/**
 * Actions to perform on plugin activation
 */
function wsc_activate_plugin() {
    // Create database tables
    require_once WSC_PLUGIN_DIR . 'includes/database.php';
    wsc_create_database_tables();

    // Create the calendar page
    wsc_create_calendar_page();

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

/**
 * Enqueue admin scripts and styles
 */
function wsc_enqueue_admin_scripts($hook) {
    // Only load on our plugin's admin page
    if ('toplevel_page_wellness-studio-calendar' !== $hook) {
        return;
    }

    // Enqueue Vue.js and Vuetify for admin
    wp_enqueue_style('vuetify-css', 'https://cdn.jsdelivr.net/npm/vuetify@3.8.0/dist/vuetify.min.css', array(), '3.8.0');
    wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/css?family=Material+Icons', array(), null);
    wp_enqueue_style('wsc-admin-styles', WSC_PLUGIN_URL . 'assets/css/admin.css', array(), WSC_PLUGIN_VERSION);

    wp_enqueue_script('vue-js', 'https://unpkg.com/vue@3/dist/vue.global.js', array(), '3.0.0', true);
    wp_enqueue_script('vuetify-js', 'https://cdn.jsdelivr.net/npm/vuetify@3.8.0/dist/vuetify.min.js', array('vue-js'), '3.8.0', true);
    wp_enqueue_script('wsc-admin-app', WSC_PLUGIN_URL . 'assets/js/admin-app.js', array('vue-js', 'vuetify-js', 'jquery'), WSC_PLUGIN_VERSION, true);

    // Add localized script with AJAX URL and nonce
    wp_localize_script('wsc-admin-app', 'wscData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wsc_nonce'),
        'restUrl' => rest_url('wellness-studio-calendar/v1'),
        'restNonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('admin_enqueue_scripts', 'wsc_enqueue_admin_scripts');

/**
 * Enqueue frontend scripts and styles
 */
function wsc_enqueue_frontend_scripts() {
    // Only load on our template
    if (is_page_template('wsc-calendar-template.php')) {
        wp_enqueue_style('vuetify-css', 'https://cdn.jsdelivr.net/npm/vuetify@3.8.0/dist/vuetify.min.css', array(), '3.8.0');
        wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/css?family=Material+Icons', array(), null);
        wp_enqueue_style('wsc-frontend-styles', WSC_PLUGIN_URL . 'assets/css/frontend.css', array(), WSC_PLUGIN_VERSION);

        wp_enqueue_script('vue-js', 'https://unpkg.com/vue@3/dist/vue.global.js', array(), '3.0.0', true);
        wp_enqueue_script('vuetify-js', 'https://cdn.jsdelivr.net/npm/vuetify@3.8.0/dist/vuetify.min.js', array('vue-js'), '3.8.0', true);
        wp_enqueue_script('wsc-frontend-app', WSC_PLUGIN_URL . 'assets/js/frontend-app.js', array('vue-js', 'vuetify-js', 'jquery'), WSC_PLUGIN_VERSION, true);

        // Add localized script with REST API URL
        wp_localize_script('wsc-frontend-app', 'wscData', array(
            'restUrl' => rest_url('wellness-studio-calendar/v1'),
            'restNonce' => wp_create_nonce('wp_rest')
        ));
    }
}
add_action('wp_enqueue_scripts', 'wsc_enqueue_frontend_scripts');