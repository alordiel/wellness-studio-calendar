<?php
/**
 * Admin page functions
 *
 * @package Wellness Studio Calendar
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register admin menu page
 */
function wsc_register_admin_menu() {
    add_menu_page(
        __('Wellness Studio Calendar', 'wellness-studio-calendar'),
        __('Wellness Calendar', 'wellness-studio-calendar'),
        'manage_options',
        'wellness-studio-calendar',
        'wsc_render_admin_page',
        'dashicons-calendar-alt',
        30
    );
}
add_action('admin_menu', 'wsc_register_admin_menu');

/**
 * Render admin page content
 */
function wsc_render_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Wellness Studio Calendar', 'wellness-studio-calendar'); ?></h1>

        <div id="wsc-admin-app">
            <!-- Vue.js application will be mounted here -->
            <div class="wsc-loading">
                <?php echo esc_html__('Loading application...', 'wellness-studio-calendar'); ?>
            </div>
        </div>
    </div>
    <?php
}