<?php
/**
 * Template functions
 *
 * @package Wellness Studio Calendar
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register page template for the calendar
 */
function wsc_add_page_template($templates) {
    $templates['wsc-calendar-template.php'] = __('Wellness Studio Calendar', 'wellness-studio-calendar');
    return $templates;
}
add_filter('theme_page_templates', 'wsc_add_page_template');

/**
 * Assign template path
 */
function wsc_load_page_template($template) {
    $post = get_post();
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);

    if ('wsc-calendar-template.php' === $page_template) {
        $template = WSC_PLUGIN_DIR . 'templates/wsc-calendar-template.php';
    }

    return $template;
}
add_filter('page_template', 'wsc_load_page_template');

/**
 * Create a page with the calendar template on plugin activation
 */
function wsc_create_calendar_page() {
    // Check if the calendar page already exists
    $calendar_page_id = get_option('wsc_calendar_page');

    if (!$calendar_page_id) {
        // Create a new page
        $page_args = array(
            'post_title'    => __('Time table', 'wellness-studio-calendar'),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'timetable',
            'page_template' => 'wsc-calendar-template.php'
        );

        // Insert the page
        $page_id = wp_insert_post($page_args);

        if ($page_id && !is_wp_error($page_id)) {
            // Set the page template
            update_post_meta($page_id, '_wp_page_template', 'wsc-calendar-template.php');

            // Save the page ID in the options
            update_option('wsc_calendar_page', $page_id);
        }
    }
}


