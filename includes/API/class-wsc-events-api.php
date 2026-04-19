<?php
/**
 * Events REST API Controller
 *
 * Handles CRUD for recurring and single-time events, plus exceptions.
 * Route: /wsc/v1/events
 *
 * @package WellnessStudioCalendar
 * @since   1.0.0
 */

namespace Wellness_Studio_Calendar\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WSC_Events_API {

    const NAMESPACE = 'wsc/v1';

    private string $events_table;
    private string $activity_table;
    private string $instructor_table;
    private string $exceptions_table;

    public function __construct() {
        global $wpdb;
        $this->events_table     = $wpdb->prefix . 'wsc_events';
        $this->activity_table   = $wpdb->prefix . 'wsc_activity';
        $this->instructor_table = $wpdb->prefix . 'wsc_instructors';
        $this->exceptions_table = $wpdb->prefix . 'wsc_exceptions';
    }

    public function register_routes(): void {
        // Collection
        register_rest_route( self::NAMESPACE, '/events', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_events' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => [
                    'type' => [
                        'type'    => 'string',
                        'enum'    => [ 'recurring', 'single' ],
                        'default' => null,
                    ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_event' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => $this->event_schema(),
            ],
        ] );

        // Single item
        register_rest_route( self::NAMESPACE, '/events/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_event' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_event' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => $this->event_schema( required: false ),
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_event' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
        ] );

        // Exceptions sub-resource
        register_rest_route( self::NAMESPACE, '/events/(?P<id>\d+)/exceptions', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_exceptions' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_exception' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => $this->exception_schema(),
            ],
        ] );

        // Delete a single exception
        register_rest_route( self::NAMESPACE, '/exceptions/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_exception' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
        ] );
    }

    // -------------------------------------------------------------------------
    // Events handlers
    // -------------------------------------------------------------------------

    public function get_events( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $type  = $request->get_param( 'type' );
        $where = '';

        if ( $type === 'recurring' ) {
            $where = 'WHERE e.is_recurring = 1';
        } elseif ( $type === 'single' ) {
            $where = 'WHERE e.is_recurring = 0';
        }

        $results = $wpdb->get_results(
            "SELECT e.*,
                    a.name  AS activity_name,
                    a.color AS activity_color,
                    i.name  AS instructor_name
             FROM {$this->events_table} e
             LEFT JOIN {$this->activity_table}   a ON e.activity_id   = a.ID
             LEFT JOIN {$this->instructor_table} i ON e.instructor_id  = i.ID
             {$where}
             ORDER BY e.is_recurring DESC, e.week_day ASC, e.start_time ASC"
        );

        return new WP_REST_Response( $results ?: [], 200 );
    }

    public function get_event( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        $event = $wpdb->get_row( $wpdb->prepare(
            "SELECT e.*,
                    a.name  AS activity_name,
                    a.color AS activity_color,
                    i.name  AS instructor_name
             FROM {$this->events_table} e
             LEFT JOIN {$this->activity_table}   a ON e.activity_id   = a.ID
             LEFT JOIN {$this->instructor_table} i ON e.instructor_id  = i.ID
             WHERE e.ID = %d",
            $id
        ) );

        if ( ! $event ) {
            return new WP_Error( 'event_not_found', __( 'Event not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        return new WP_REST_Response( $event, 200 );
    }

    public function create_event( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $data = $this->sanitize_event_data( $request );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $result = $wpdb->insert( $this->events_table, $data['row'], $data['formats'] );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not create event.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return $this->get_event( new WP_REST_Request( 'GET', '', [ 'id' => $wpdb->insert_id ] ) );
    }

    public function update_event( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->event_exists( $id ) ) {
            return new WP_Error( 'event_not_found', __( 'Event not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $data = $this->sanitize_event_data( $request, false );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( empty( $data['row'] ) ) {
            return new WP_REST_Response( [ 'message' => 'Nothing to update.' ], 200 );
        }

        $result = $wpdb->update(
            $this->events_table,
            $data['row'],
            [ 'ID' => $id ],
            $data['formats'],
            [ '%d' ]
        );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Could not update event.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        $get_request        = new WP_REST_Request( 'GET' );
        $get_request['id']  = $id;
        return $this->get_event( $get_request );
    }

    public function delete_event( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->event_exists( $id ) ) {
            return new WP_Error( 'event_not_found', __( 'Event not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        // Remove linked exceptions first (no FK cascade in InnoDB unless configured)
        $wpdb->delete( $this->exceptions_table, [ 'event_id' => $id ], [ '%d' ] );

        $result = $wpdb->delete( $this->events_table, [ 'ID' => $id ], [ '%d' ] );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not delete event.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'deleted' => true, 'id' => $id ], 200 );
    }

    // -------------------------------------------------------------------------
    // Exceptions handlers
    // -------------------------------------------------------------------------

    public function get_exceptions( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $event_id = absint( $request['id'] );

        if ( ! $this->event_exists( $event_id ) ) {
            return new WP_Error( 'event_not_found', __( 'Event not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->exceptions_table} WHERE event_id = %d ORDER BY exception_date ASC",
            $event_id
        ) );

        return new WP_REST_Response( $rows ?: [], 200 );
    }

    public function create_exception( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $event_id = absint( $request['id'] );

        if ( ! $this->event_exists( $event_id ) ) {
            return new WP_Error( 'event_not_found', __( 'Event not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $type           = sanitize_text_field( $request->get_param( 'type' ) ?? '' );
        $exception_date = sanitize_text_field( $request->get_param( 'exception_date' ) ?? '' );

        $valid_types = [ 'cancelled', 'rescheduled', 'modified' ];
        if ( ! in_array( $type, $valid_types, true ) ) {
            return new WP_Error( 'invalid_type', __( 'Exception type must be cancelled, rescheduled, or modified.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        if ( ! $this->is_valid_date( $exception_date ) ) {
            return new WP_Error( 'invalid_date', __( 'exception_date must be a valid date (YYYY-MM-DD).', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        $row = [
            'event_id'       => $event_id,
            'type'           => $type,
            'exception_date' => $exception_date,
            'new_date'       => '',
            'new_start_time' => '',
            'new_end_time'   => '',
        ];
        $formats = [ '%d', '%s', '%s', '%s', '%s', '%s' ];

        if ( $type !== 'cancelled' ) {
            $new_date       = sanitize_text_field( $request->get_param( 'new_date' ) ?? '' );
            $new_start_time = sanitize_text_field( $request->get_param( 'new_start_time' ) ?? '' );
            $new_end_time   = sanitize_text_field( $request->get_param( 'new_end_time' ) ?? '' );

            if ( ! $this->is_valid_date( $new_date ) ) {
                return new WP_Error( 'invalid_new_date', __( 'new_date must be a valid date (YYYY-MM-DD).', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }

            if ( ! $this->is_valid_time( $new_start_time ) || ! $this->is_valid_time( $new_end_time ) ) {
                return new WP_Error( 'invalid_time', __( 'Times must be in HH:MM (24h) format.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }

            $row['new_date']       = $new_date;
            $row['new_start_time'] = $new_start_time;
            $row['new_end_time']   = $new_end_time;
        }

        $result = $wpdb->insert( $this->exceptions_table, $row, $formats );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not create exception.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        $inserted = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->exceptions_table} WHERE ID = %d",
            $wpdb->insert_id
        ) );

        return new WP_REST_Response( $inserted, 201 );
    }

    public function delete_exception( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->exceptions_table} WHERE ID = %d",
            $id
        ) );

        if ( ! $existing ) {
            return new WP_Error( 'exception_not_found', __( 'Exception not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $result = $wpdb->delete( $this->exceptions_table, [ 'ID' => $id ], [ '%d' ] );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not delete exception.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'deleted' => true, 'id' => $id ], 200 );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function admin_permission(): bool {
        return current_user_can( 'manage_options' );
    }

    private function event_exists( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT ID FROM {$this->events_table} WHERE ID = %d",
            $id
        ) );
    }

    private function sanitize_event_data( WP_REST_Request $request, bool $required = true ): array|WP_Error {
        $row     = [];
        $formats = [];

        $activity_id   = $request->get_param( 'activity_id' );
        $instructor_id = $request->get_param( 'instructor_id' );
        $is_recurring  = $request->get_param( 'is_recurring' );
        $week_day      = $request->get_param( 'week_day' );
        $date          = $request->get_param( 'date' );
        $start_time    = $request->get_param( 'start_time' );
        $end_time      = $request->get_param( 'end_time' );
        $places        = $request->get_param( 'places' );

        if ( $activity_id !== null ) {
            $row['activity_id'] = absint( $activity_id );
            $formats[]          = '%d';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'activity_id is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        if ( $instructor_id !== null ) {
            $row['instructor_id'] = absint( $instructor_id );
            $formats[]            = '%d';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'instructor_id is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        if ( $is_recurring !== null ) {
            $row['is_recurring'] = (int) (bool) $is_recurring;
            $formats[]           = '%d';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'is_recurring is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        // week_day required for recurring; date required for single
        $recurring_value = $row['is_recurring'] ?? null;
        if ( $recurring_value === 1 || ( $required && $recurring_value === null ) ) {
            if ( $week_day !== null ) {
                $valid_days = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
                $week_day   = sanitize_text_field( $week_day );
                if ( ! in_array( $week_day, $valid_days, true ) ) {
                    return new WP_Error( 'invalid_week_day', __( 'Invalid week_day value.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
                }
                $row['week_day'] = $week_day;
                $formats[]       = '%s';
            }
        }

        if ( $date !== null ) {
            $date = sanitize_text_field( $date );
            if ( ! $this->is_valid_date( $date ) ) {
                return new WP_Error( 'invalid_date', __( 'date must be in YYYY-MM-DD format.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['date'] = $date;
            $formats[]   = '%s';
        }

        if ( $start_time !== null ) {
            $start_time = sanitize_text_field( $start_time );
            if ( ! $this->is_valid_time( $start_time ) ) {
                return new WP_Error( 'invalid_time', __( 'start_time must be in HH:MM (24h) format.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['start_time'] = $start_time;
            $formats[]         = '%s';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'start_time is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        if ( $end_time !== null ) {
            $end_time = sanitize_text_field( $end_time );
            if ( ! $this->is_valid_time( $end_time ) ) {
                return new WP_Error( 'invalid_time', __( 'end_time must be in HH:MM (24h) format.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            if ( isset( $row['start_time'] ) && $end_time <= $row['start_time'] ) {
                return new WP_Error( 'invalid_time', __( 'end_time must be after start_time.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['end_time'] = $end_time;
            $formats[]       = '%s';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'end_time is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        if ( $places !== null ) {
            $places = absint( $places );
            if ( $places < 1 ) {
                return new WP_Error( 'invalid_places', __( 'places must be at least 1.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['places'] = $places;
            $formats[]     = '%d';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'places is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        return [ 'row' => $row, 'formats' => $formats ];
    }

    private function event_schema( bool $required = true ): array {
        $req = $required;
        return [
            'activity_id'   => [ 'type' => 'integer', 'required' => $req ],
            'instructor_id' => [ 'type' => 'integer', 'required' => $req ],
            'is_recurring'  => [ 'type' => 'boolean', 'required' => $req ],
            'week_day'      => [ 'type' => 'string'  ],
            'date'          => [ 'type' => 'string'  ],
            'start_time'    => [ 'type' => 'string', 'required' => $req ],
            'end_time'      => [ 'type' => 'string', 'required' => $req ],
            'places'        => [ 'type' => 'integer', 'required' => $req, 'minimum' => 1 ],
        ];
    }

    private function exception_schema(): array {
        return [
            'type'           => [ 'type' => 'string', 'required' => true, 'enum' => [ 'cancelled', 'rescheduled', 'modified' ] ],
            'exception_date' => [ 'type' => 'string', 'required' => true ],
            'new_date'       => [ 'type' => 'string' ],
            'new_start_time' => [ 'type' => 'string' ],
            'new_end_time'   => [ 'type' => 'string' ],
        ];
    }

    private function is_valid_date( string $date ): bool {
        return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) &&
               strtotime( $date ) !== false;
    }

    private function is_valid_time( string $time ): bool {
        return (bool) preg_match( '/^([01]\d|2[0-3]):([0-5]\d)$/', $time );
    }
}
