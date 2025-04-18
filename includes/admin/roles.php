<?php
/**
 * Roles and Capabilities
 *
 * @package Wellness Studio Calendar
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register plugin capabilities and roles
 */
function wsc_register_roles_and_caps() {
    // Get administrator role
    $admin_role = get_role('administrator');

    // Array of capabilities to add
    $caps = array(
        'manage_wsc_event',
        'manage_wsc_instructor',
        'manage_wsc_locations',
        'manage_wsc_practice_classes',
        'manage_wsc_reservations'
    );

    // Add capabilities to administrator role
    foreach ($caps as $cap) {
        if ($admin_role) {
            $admin_role->add_cap($cap);
        }
    }

    // Check if the event manager role already exists
    $event_manager_role = get_role('wsc_event_manager');

    // If the role doesn't exist, create it
    if (!$event_manager_role) {
        // Define the event manager capabilities
        $event_manager_caps = array(
            'read' => true,
            'manage_wsc_event' => true,
            'manage_wsc_instructor' => true,
            'manage_wsc_locations' => true,
            'manage_wsc_practice_classes' => true,
            'manage_wsc_reservations' => true,
        );

        // Add the role with the defined capabilities
        add_role('wsc_event_manager', __('Wellness Event Manager', 'wellness-studio-calendar'), $event_manager_caps);
    }
}

/**
 * Remove plugin capabilities and roles on uninstall
 */
function wsc_remove_roles_and_caps() {
    // Get administrator role
    $admin_role = get_role('administrator');

    // Array of capabilities to remove
    $caps = array(
        'manage_wsc_event',
        'manage_wsc_instructor',
        'manage_wsc_locations',
        'manage_wsc_practice_classes',
        'manage_wsc_reservations'
    );

    // Remove capabilities from administrator role
    foreach ($caps as $cap) {
        if ($admin_role) {
            $admin_role->remove_cap($cap);
        }
    }

    // Remove the event manager role
    remove_role('wsc_event_manager');
}

/**
 * Check if capabilities need to be registered
 */
function wsc_maybe_register_roles() {
    // Get the version
    $version = get_option('wsc_version', '0.0.0');

    // If version is changed or first install, register roles and capabilities
    if (version_compare($version, WSC_PLUGIN_VERSION, '!=')) {
        wsc_register_roles_and_caps();
        update_option('wsc_version', WSC_PLUGIN_VERSION);
    }
}
add_action('admin_init', 'wsc_maybe_register_roles');