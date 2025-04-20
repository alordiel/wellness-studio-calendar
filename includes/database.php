<?php
/**
 * Database functions
 *
 * @package Wellness Studio Calendar
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create plugin database tables
 */
function wsc_create_database_tables() {
    global $wpdb;

    // Get WordPress database charset and collation
    $charset_collate = $wpdb->get_charset_collate();

    // SQL statements for creating tables
    $sql = array();

    // Practice class table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wsc_activity` (
        `ID` int(4) AUTO_INCREMENT NOT NULL UNIQUE,
        `name` varchar(255) NOT NULL,
        `description` varchar(500) NOT NULL,
        `link` varchar(500) NOT NULL,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";

    // Instructors table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wsc_instructors` (
        `ID` int(4) AUTO_INCREMENT NOT NULL UNIQUE,
        `name` varchar(255) NOT NULL,
        `description` varchar(800) NOT NULL,
        `photo` varchar(500) NOT NULL,
        `link` varchar(500) NOT NULL,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";

    // Locations table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wsc_locations` (
        `ID` int(2) AUTO_INCREMENT NOT NULL UNIQUE,
        `type` varchar(255) NOT NULL,
        `name` varchar(255) NOT NULL,
        `max_participants` TINYINT(1) NOT NULL,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";

    // Events table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wsc_events` (
        `ID` int(8) AUTO_INCREMENT NOT NULL UNIQUE,
        `activity` int(4) NOT NULL,
        `instructor` int(4) NOT NULL,
        `location` int(2) NOT NULL,
        `room` int(2) NOT NULL,
        `color` varchar(10) NOT NULL,
        `week_day` varchar(20) NOT NULL,
        `start_time` time NOT NULL,
        `end_time` time NOT NULL,
        `places` TINYINT(1) NOT NULL,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";

    // Reservations table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wsc_reservations` (
        `ID` int(16) AUTO_INCREMENT NOT NULL UNIQUE,
        `event` int(8) NOT NULL,
        `user_name` varchar(255) NOT NULL,
        `user_contacts` varchar(255) NOT NULL,
        `payment` varchar(255) NOT NULL,
        `user_notes` varchar(500) NOT NULL,
        `admin_notes` varchar(500) NOT NULL,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";

    // Exceptions table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wsc_exceptions` (
        `ID` int AUTO_INCREMENT NOT NULL UNIQUE,
        `event` int(8) NOT NULL,
        `type` varchar(50) NOT NULL,
        `exception_date` date NOT NULL,
        `new_date` date NOT NULL,
        `new_start_time` time NOT NULL,
        `new_end_time` time NOT NULL,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";

    // Add foreign key constraints
    $sql[] = "ALTER TABLE `{$wpdb->prefix}wsc_events` 
        ADD CONSTRAINT `event_fk1` FOREIGN KEY (`activity`) 
        REFERENCES `{$wpdb->prefix}wsc_activity`(`ID`);";

    $sql[] = "ALTER TABLE `{$wpdb->prefix}wsc_events` 
        ADD CONSTRAINT `event_fk2` FOREIGN KEY (`instructor`) 
        REFERENCES `{$wpdb->prefix}wsc_instructors`(`ID`);";

    $sql[] = "ALTER TABLE `{$wpdb->prefix}wsc_events` 
        ADD CONSTRAINT `event_fk3` FOREIGN KEY (`location`) 
        REFERENCES `{$wpdb->prefix}wsc_locations`(`ID`);";

    $sql[] = "ALTER TABLE `{$wpdb->prefix}wsc_events` 
        ADD CONSTRAINT `event_fk4` FOREIGN KEY (`room`) 
        REFERENCES `{$wpdb->prefix}wsc_locations`(`ID`);";

    $sql[] = "ALTER TABLE `{$wpdb->prefix}wsc_reservations` 
        ADD CONSTRAINT `reservations_fk1` FOREIGN KEY (`event`) 
        REFERENCES `{$wpdb->prefix}wsc_events`(`ID`);";

    $sql[] = "ALTER TABLE `{$wpdb->prefix}wsc_exceptions` 
        ADD CONSTRAINT `exceptions_fk1` FOREIGN KEY (`event`) 
        REFERENCES `{$wpdb->prefix}wsc_events`(`ID`);";

    // Require dbDelta function
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Execute SQL statements with error handling
    foreach ($sql as $query) {
        try {
            dbDelta($query);
        } catch (Exception $e) {
            error_log('Wellness Studio Calendar - Database creation error: ' . $e->getMessage());
        }
    }
}

/**
 * Handle database version upgrades if needed
 */
function wsc_upgrade_database() {
    $db_version = get_option('wsc_db_version', '1.0.0');

    // If database version is less than current plugin version, upgrade
    if (version_compare($db_version, WSC_PLUGIN_VERSION, '<')) {
        wsc_create_database_tables();
        update_option('wsc_db_version', WSC_PLUGIN_VERSION);
    }
}
add_action('plugins_loaded', 'wsc_upgrade_database');