<?php
/**
 * REST API Loader
 *
 * Requires all API controller files and registers their routes
 * on the rest_api_init hook.
 *
 * @package WellnessStudioCalendar
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-wsc-events-api.php';
require_once __DIR__ . '/class-wsc-activities-api.php';
require_once __DIR__ . '/class-wsc-instructors-api.php';
require_once __DIR__ . '/class-wsc-reservations-api.php';

add_action( 'rest_api_init', function () {
    ( new Wellness_Studio_Calendar\API\WSC_Events_API() )->register_routes();
    ( new Wellness_Studio_Calendar\API\WSC_Activities_API() )->register_routes();
    ( new Wellness_Studio_Calendar\API\WSC_Instructors_API() )->register_routes();
    ( new Wellness_Studio_Calendar\API\WSC_Reservations_API() )->register_routes();
} );
