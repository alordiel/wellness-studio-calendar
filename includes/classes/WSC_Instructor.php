<?php

namespace Wellness_Studio_Calendar;
/**
 * Class for managing instructor data
 *
 * @package WellnessStudioCalendar
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Instructor Model Class
 *
 * Handles CRUD operations for wellness studio instructors
 */
class WSC_Instructor {
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
        $this->table_name = $wpdb->prefix . 'wsc_instructors';
    }

    /**
     * Create a new instructor
     *
     * @param array $data Instructor data
     *
     * @return int|false Instructor ID if successful, false otherwise
     */
    public function create( array $data ) {
        global $wpdb;

        // Ensure required fields are present
        $name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
        $description = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';
        $photo = isset( $data['photo'] ) ? esc_url_raw( $data['photo'] ) : '';
        $link = isset( $data['link'] ) ? esc_url_raw( $data['link'] ) : '';

        // Validate required fields
        if ( empty( $name ) ) {
            return false;
        }

        // Prepare data for insertion
        $instructor_data = array(
            'name'        => $name,
            'description' => $description,
            'photo'       => $photo,
            'link'        => $link,
        );

        // Insert the instructor
        $result = $wpdb->insert(
            $this->table_name,
            $instructor_data,
            array( '%s', '%s', '%s', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Read an instructor by ID
     *
     * @param int $instructor_id Instructor ID
     * @return object|false Instructor data if found, false otherwise
     */
    public function get_instructor( int $instructor_id ) {
        global $wpdb;

        $instructor_id = absint( $instructor_id );
        if ( ! $instructor_id ) {
            return false;
        }

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE ID = %d",
                $instructor_id
            )
        );
    }

    /**
     * Update an existing instructor
     *
     * @param int   $instructor_id Instructor ID
     * @param array $data          Updated instructor data
     * @return bool True if successful, false otherwise
     */
    public function update( int $instructor_id, array $data ):bool {
        global $wpdb;

        $instructor_id = absint( $instructor_id );
        if ( ! $instructor_id ) {
            return false;
        }

        // Check if instructor exists
        $existing = $this->read( $instructor_id );
        if ( ! $existing ) {
            return false;
        }

        // Prepare update data
        $update_data = array();
        $formats = array();

        // Update only provided fields
        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
            $formats[] = '%s';
        }

        if ( isset( $data['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $data['description'] );
            $formats[] = '%s';
        }

        if ( isset( $data['photo'] ) ) {
            $update_data['photo'] = esc_url_raw( $data['photo'] );
            $formats[] = '%s';
        }

        if ( isset( $data['link'] ) ) {
            $update_data['link'] = esc_url_raw( $data['link'] );
            $formats[] = '%s';
        }

        // If no fields to update, return early
        if ( empty( $update_data ) ) {
            return true;
        }

        // Update the instructor
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'ID' => $instructor_id ),
            $formats,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete an instructor
     *
     * @param int $instructor_id Instructor ID
     * @return bool True if successful, false otherwise
     */
    public function delete( int $instructor_id ): bool {
        global $wpdb;

        $instructor_id = absint( $instructor_id );
        if ( ! $instructor_id ) {
            return false;
        }

        // Check if instructor exists
        $existing = $this->read( $instructor_id );
        if ( ! $existing ) {
            return false;
        }

        // Delete the instructor
        $result = $wpdb->delete(
            $this->table_name,
            array( 'ID' => $instructor_id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get all instructors
     *
     * @param array $args Query arguments
     * @return array Array of instructors
     */
    public function get_all( array $args = [] ): array {
        global $wpdb;

        // Default arguments
        $defaults = array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'limit'   => -1,
            'offset'  => 0,
        );

        $args = wp_parse_args( $args, $defaults );
        $limit_clause = '';

        // Sanitize order and orderby
        $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
        if ( ! $orderby ) {
            $orderby = 'name ASC';
        }

        // Add limit clause if specified
        if ( $args['limit'] > 0 ) {
            $limit_clause = $wpdb->prepare( ' LIMIT %d, %d', $args['offset'], $args['limit'] );
        }

        // Get all instructors
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY {$orderby}{$limit_clause}"
        );

    }

    /**
     * Count total instructors
     *
     * @return int Total number of instructors
     */
    public function count(): int {
        global $wpdb;

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }

    /**
     * Search instructors
     *
     * @param string $search Search term
     * @return array Array of instructors matching the search term
     */
    public function search( string $search ): array {
        global $wpdb;

        $search = sanitize_text_field( $search );
        if ( empty( $search ) ) {
            return array();
        }

        $search_term = '%' . $wpdb->esc_like( $search ) . '%';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE name LIKE %s OR description LIKE %s ORDER BY name ASC",
                $search_term,
                $search_term
            )
        );

    }
}