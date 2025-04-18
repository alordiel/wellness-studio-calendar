<?php

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