<?php
/**
 * Reservations REST API Controller
 *
 * Handles read, status updates, admin notes, and deletion of reservations.
 * Route: /wsc/v1/reservations
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

class WSC_Reservations_API {

    const NAMESPACE = 'wsc/v1';

    const VALID_STATUSES = [ 'approved', 'cancelled', 'no-show' ];

    private string $table;
    private string $notes_table;
    private string $events_table;

    public function __construct() {
        global $wpdb;
        $this->table        = $wpdb->prefix . 'wsc_reservations';
        $this->notes_table  = $wpdb->prefix . 'wsc_reservation_notes';
        $this->events_table = $wpdb->prefix . 'wsc_events';
    }

    public function register_routes(): void {
        // Collection
        register_rest_route( self::NAMESPACE, '/reservations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_reservations' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => [
                    'event_id' => [ 'type' => 'integer', 'default' => 0 ],
                    'status'   => [ 'type' => 'string', 'enum' => self::VALID_STATUSES ],
                ],
            ],
        ] );

        // Single item
        register_rest_route( self::NAMESPACE, '/reservations/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_reservation' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_reservation' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => [
                    'status'         => [ 'type' => 'string', 'enum' => self::VALID_STATUSES ],
                    'payment_method' => [ 'type' => 'string' ],
                    'user_name'      => [ 'type' => 'string' ],
                    'email'          => [ 'type' => 'string', 'format' => 'email' ],
                    'phone'          => [ 'type' => 'string' ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_reservation' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
        ] );

        // Admin notes sub-resource
        register_rest_route( self::NAMESPACE, '/reservations/(?P<id>\d+)/notes', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_notes' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'add_note' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => [
                    'note'      => [ 'type' => 'string', 'required' => true ],
                    'note_type' => [ 'type' => 'string', 'enum' => [ 'admin', 'system' ], 'default' => 'admin' ],
                ],
            ],
        ] );
    }

    // -------------------------------------------------------------------------
    // Handlers
    // -------------------------------------------------------------------------

    public function get_reservations( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $event_id = absint( $request->get_param( 'event_id' ) );
        $status   = sanitize_text_field( $request->get_param( 'status' ) ?? '' );

        $where  = [];
        $params = [];

        if ( $event_id > 0 ) {
            $where[]  = 'r.event_id = %d';
            $params[] = $event_id;
        }

        if ( ! empty( $status ) ) {
            $where[]  = 'r.status = %s';
            $params[] = $status;
        }

        $where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

        $query = "SELECT r.*,
                         e.start_time  AS event_start_time,
                         e.end_time    AS event_end_time,
                         e.week_day    AS event_week_day,
                         e.is_recurring AS event_is_recurring
                  FROM {$this->table} r
                  LEFT JOIN {$this->events_table} e ON r.event_id = e.ID
                  {$where_sql}
                  ORDER BY r.ID DESC";

        if ( ! empty( $params ) ) {
            $query = $wpdb->prepare( $query, $params );
        }

        return new WP_REST_Response( $wpdb->get_results( $query ) ?: [], 200 );
    }

    public function get_reservation( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $row = $this->find( absint( $request['id'] ) );

        if ( ! $row ) {
            return new WP_Error( 'reservation_not_found', __( 'Reservation not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        return new WP_REST_Response( $row, 200 );
    }

    public function update_reservation( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->find( $id ) ) {
            return new WP_Error( 'reservation_not_found', __( 'Reservation not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $row     = [];
        $formats = [];

        $status = $request->get_param( 'status' );
        if ( $status !== null ) {
            if ( ! in_array( $status, self::VALID_STATUSES, true ) ) {
                return new WP_Error( 'invalid_status', __( 'status must be approved, cancelled, or no-show.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['status'] = sanitize_text_field( $status );
            $formats[]     = '%s';

            if ( $status === 'cancelled' ) {
                $row['cancelled_by'] = 'admin';
                $formats[]           = '%s';
            }
        }

        $user_name = $request->get_param( 'user_name' );
        if ( $user_name !== null ) {
            $user_name = sanitize_text_field( $user_name );
            if ( empty( $user_name ) ) {
                return new WP_Error( 'invalid_user_name', __( 'user_name cannot be empty.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['user_name'] = $user_name;
            $formats[]        = '%s';
        }

        $email = $request->get_param( 'email' );
        if ( $email !== null ) {
            $email = sanitize_email( $email );
            if ( ! is_email( $email ) ) {
                return new WP_Error( 'invalid_email', __( 'A valid email is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['email'] = $email;
            $formats[]    = '%s';
        }

        $phone = $request->get_param( 'phone' );
        if ( $phone !== null ) {
            $row['phone'] = sanitize_text_field( $phone );
            $formats[]    = '%s';
        }

        $payment_method = $request->get_param( 'payment_method' );
        if ( $payment_method !== null ) {
            $available = get_option( 'wsc_available_payment_methods', [] );
            $payment_method = sanitize_text_field( $payment_method );
            if ( ! empty( $available ) && ! in_array( $payment_method, $available, true ) ) {
                return new WP_Error( 'invalid_payment_method', __( 'Invalid payment method.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['payment_method'] = $payment_method;
            $formats[]             = '%s';
        }

        if ( empty( $row ) ) {
            return new WP_REST_Response( $this->find( $id ), 200 );
        }

        $result = $wpdb->update(
            $this->table,
            $row,
            [ 'ID' => $id ],
            $formats,
            [ '%d' ]
        );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Could not update reservation.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( $this->find( $id ), 200 );
    }

    public function delete_reservation( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->find( $id ) ) {
            return new WP_Error( 'reservation_not_found', __( 'Reservation not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        // Remove linked notes first
        $wpdb->delete( $this->notes_table, [ 'reservation_id' => $id ], [ '%d' ] );

        $result = $wpdb->delete( $this->table, [ 'ID' => $id ], [ '%d' ] );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not delete reservation.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'deleted' => true, 'id' => $id ], 200 );
    }

    // -------------------------------------------------------------------------
    // Notes handlers
    // -------------------------------------------------------------------------

    public function get_notes( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->find( $id ) ) {
            return new WP_Error( 'reservation_not_found', __( 'Reservation not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $notes = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->notes_table} WHERE reservation_id = %d ORDER BY created_at ASC",
            $id
        ) );

        return new WP_REST_Response( $notes ?: [], 200 );
    }

    public function add_note( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->find( $id ) ) {
            return new WP_Error( 'reservation_not_found', __( 'Reservation not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $note      = sanitize_textarea_field( $request->get_param( 'note' ) ?? '' );
        $note_type = sanitize_text_field( $request->get_param( 'note_type' ) ?? 'admin' );

        if ( empty( $note ) ) {
            return new WP_Error( 'empty_note', __( 'note cannot be empty.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        $current_user_id = get_current_user_id();

        $result = $wpdb->insert(
            $this->notes_table,
            [
                'reservation_id' => $id,
                'note_type'      => $note_type,
                'note'           => $note,
                'author_id'      => $current_user_id,
                'created_at'     => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%d', '%s' ]
        );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not save note.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        $inserted = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->notes_table} WHERE ID = %d",
            $wpdb->insert_id
        ) );

        return new WP_REST_Response( $inserted, 201 );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function admin_permission(): bool {
        return current_user_can( 'manage_options' );
    }

    private function find( int $id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE ID = %d",
            $id
        ) ) ?: null;
    }
}
