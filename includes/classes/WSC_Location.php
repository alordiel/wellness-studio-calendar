<?php
/**
 * Class for managing location data
 *
 * @package WellnessStudioCalendar
 * @since 1.0.0
 */

namespace Wellness_Studio_Calendar;
use Exception;
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Location Model Class
 *
 * Handles CRUD operations for wellness studio locations
 */
class WSC_Location {
	/**
     * Table name
     *
     * @var string
     */
    private string $table_name;

    /**
     * Initialize the class
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wsc_locations';
    }

    /**
     * Create a new location
     *
     * @param array $data Location data
     * @return int|false Location ID if successful, false otherwise
     */
    public function create( array $data ) {
        global $wpdb;

        // Ensure required fields are present
        $type = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : '';
        $name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';

        // Validate required fields
        if ( empty( $name ) || empty( $type ) ) {
            return false;
        }

        // Validate location type
        $valid_types = array( 'address', 'hall name' );
        if ( ! in_array( $type, $valid_types ) ) {
            return false;
        }

        // Prepare data for insertion
        $location_data = array(
            'type' => $type,
            'name' => $name,
        );

        // Insert the location
        $result = $wpdb->insert(
            $this->table_name,
            $location_data,
            array( '%s', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update an existing location
     *
     * @param int   $location_id Location ID
     * @param array $data        Updated location data
     * @return bool True if successful, false otherwise
     */
    public function update( int $location_id,array $data ): bool {
        global $wpdb;

        $location_id = absint( $location_id );
        if ( ! $location_id ) {
            return false;
        }

        // Check if location exists
        $existing = $this->get_location_by_id( $location_id );
        if ( ! $existing ) {
            return false;
        }

        // Prepare update data
        $update_data = array();
        $formats = array();

        // Update only provided fields
        if ( isset( $data['type'] ) ) {
            $type = sanitize_text_field( $data['type'] );
            $valid_types = array( 'address', 'hall name' );
            if ( in_array( $type, $valid_types ) ) {
                $update_data['type'] = $type;
                $formats[] = '%s';
            }
        }

        if ( isset( $data['name'] ) ) {
            $name = sanitize_text_field( $data['name'] );
            if ( ! empty( $name ) ) {
                $update_data['name'] = $name;
                $formats[] = '%s';
            }
        }

        // If no fields to update, return early
        if ( empty( $update_data ) ) {
            return true;
        }

        // Update the location
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'ID' => $location_id ),
            $formats,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete a location
     *
     * @param int $location_id Location ID
     * @return bool True if successful, false otherwise
     */
    public function delete( int $location_id ): bool {
        global $wpdb;

        $location_id = absint( $location_id );
        if ( ! $location_id ) {
            return false;
        }

        // Check if location exists
        $existing = $this->get_location_by_id( $location_id );
        if ( ! $existing ) {
            return false;
        }

        // Delete the location
        $result = $wpdb->delete(
            $this->table_name,
            array( 'ID' => $location_id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get a location by ID
     *
     * @param int $location_id Location ID
     * @return array|object|\stdClass|null Location data if found, false otherwise
     */
    public function get_location_by_id( int $location_id ) {
        global $wpdb;

	    return $wpdb->get_row("SELECT * FROM {$this->table_name} WHERE ID = $location_id" );
    }

    /**
     * Get all locations
     *
     * @param array $args Query arguments
     * @return array Array of locations
     */
    public function get_all_locations( array $args = [] ): array {
        global $wpdb;

        // Default arguments
        $defaults = array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'type'    => '',
        );

        $args = wp_parse_args( $args, $defaults );

        // Build the query
        $query = "SELECT * FROM {$this->table_name}";
        $where = [];
        $prepare_args = [];

        // Filter by type if provided
        if ( ! empty( $args['type'] ) ) {
            $type = sanitize_text_field( $args['type'] );
            $where[] = 'type = %s';
            $prepare_args[] = $type;
        }

        // Add WHERE clauses if any
        if ( ! empty( $where ) ) {
            $query .= ' WHERE ' . implode( ' AND ', $where );
        }

        // Add ORDER BY clause
        $valid_orderby = array( 'ID', 'name', 'type' );
        $orderby = in_array( $args['orderby'], $valid_orderby ) ? $args['orderby'] : 'name';
        $order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';
        $query .= " ORDER BY {$orderby} {$order}";

        // Prepare the query if needed
        if ( ! empty( $prepare_args ) ) {
            $query = $wpdb->prepare( $query, $prepare_args );
        }

        // Get all locations
        $locations = $wpdb->get_results( $query );

        return $locations;
    }
}