<?php
/**
 * Activities REST API Controller
 *
 * Handles CRUD for studio activities (practice classes).
 * Route: /wsc/v1/activities
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

class WSC_Activities_API {

    const NAMESPACE = 'wsc/v1';

    private string $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'wsc_activity';
    }

    public function register_routes(): void {
        register_rest_route( self::NAMESPACE, '/activities', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_activities' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_activity' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => $this->activity_schema(),
            ],
        ] );

        register_rest_route( self::NAMESPACE, '/activities/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_activity' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_activity' ],
                'permission_callback' => [ $this, 'admin_permission' ],
                'args'                => $this->activity_schema( required: false ),
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_activity' ],
                'permission_callback' => [ $this, 'admin_permission' ],
            ],
        ] );
    }

    // -------------------------------------------------------------------------
    // Handlers
    // -------------------------------------------------------------------------

    public function get_activities( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT * FROM {$this->table} ORDER BY name ASC"
        );

        return new WP_REST_Response( $rows ?: [], 200 );
    }

    public function get_activity( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $row = $this->find( absint( $request['id'] ) );

        if ( ! $row ) {
            return new WP_Error( 'activity_not_found', __( 'Activity not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        return new WP_REST_Response( $row, 200 );
    }

    public function create_activity( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $data = $this->sanitize_data( $request );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $result = $wpdb->insert( $this->table, $data['row'], $data['formats'] );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not create activity.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        $created = $this->find( $wpdb->insert_id );
        return new WP_REST_Response( $created, 201 );
    }

    public function update_activity( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->find( $id ) ) {
            return new WP_Error( 'activity_not_found', __( 'Activity not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $data = $this->sanitize_data( $request, false );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        if ( empty( $data['row'] ) ) {
            return new WP_REST_Response( $this->find( $id ), 200 );
        }

        $result = $wpdb->update(
            $this->table,
            $data['row'],
            [ 'ID' => $id ],
            $data['formats'],
            [ '%d' ]
        );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Could not update activity.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( $this->find( $id ), 200 );
    }

    public function delete_activity( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;

        $id = absint( $request['id'] );

        if ( ! $this->find( $id ) ) {
            return new WP_Error( 'activity_not_found', __( 'Activity not found.', 'wellness-studio-calendar' ), [ 'status' => 404 ] );
        }

        $result = $wpdb->delete( $this->table, [ 'ID' => $id ], [ '%d' ] );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'Could not delete activity.', 'wellness-studio-calendar' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'deleted' => true, 'id' => $id ], 200 );
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

    private function sanitize_data( WP_REST_Request $request, bool $required = true ): array|WP_Error {
        $row     = [];
        $formats = [];

        $name        = $request->get_param( 'name' );
        $description = $request->get_param( 'description' );
        $color       = $request->get_param( 'color' );
        $link        = $request->get_param( 'link' );

        if ( $name !== null ) {
            $name = sanitize_text_field( $name );
            if ( empty( $name ) ) {
                return new WP_Error( 'invalid_name', __( 'name cannot be empty.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['name'] = $name;
            $formats[]   = '%s';
        } elseif ( $required ) {
            return new WP_Error( 'missing_field', __( 'name is required.', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
        }

        if ( $description !== null ) {
            $row['description'] = sanitize_textarea_field( $description );
            $formats[]          = '%s';
        }

        if ( $color !== null ) {
            $color = sanitize_hex_color( $color );
            if ( ! $color ) {
                return new WP_Error( 'invalid_color', __( 'color must be a valid hex color (e.g. #ff0000).', 'wellness-studio-calendar' ), [ 'status' => 422 ] );
            }
            $row['color'] = $color;
            $formats[]    = '%s';
        }

        if ( $link !== null ) {
            $row['link'] = esc_url_raw( $link );
            $formats[]   = '%s';
        }

        return [ 'row' => $row, 'formats' => $formats ];
    }

    private function activity_schema( bool $required = true ): array {
        return [
            'name'        => [ 'type' => 'string', 'required' => $required ],
            'description' => [ 'type' => 'string' ],
            'color'       => [ 'type' => 'string' ],
            'link'        => [ 'type' => 'string', 'format' => 'uri' ],
        ];
    }
}
