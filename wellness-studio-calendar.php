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
const WS_CALENDAR_VERSION = '1.0.0';
define( 'WS_CALENDAR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WS_CALENDAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
