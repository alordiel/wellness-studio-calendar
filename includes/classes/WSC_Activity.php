<?php
/**
 * Class for managing practice class data
 *
 * @package WellnessStudioCalendar
 * @since 1.0.0
 */
namespace Wellness_Studio_Calendar;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Practice Class Model
 *
 * Handles CRUD operations for wellness studio practice classes
 */
class WSC_Activity {
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
        $this->table_name = $wpdb->prefix . 'wsc_practice_class';
    }

    /**
     * Create a new practice class
     *
     * @param array $data Practice class data
     * @return int|false Practice class ID if successful, false otherwise
     */
    public function create(array $data ) {
        global $wpdb;

        // Ensure required fields are present
        $name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
        $description = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';
        $link = isset( $data['link'] ) ? esc_url_raw( $data['link'] ) : '';

        // Validate required fields
        if ( empty( $name ) ) {
            return false;
        }

        // Prepare data for insertion
        $practice_data = array(
            'name'        => $name,
            'description' => $description,
            'link'        => $link,
        );

        // Insert the practice class
        $result = $wpdb->insert(
            $this->table_name,
            $practice_data,
            array( '%s', '%s', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update an existing practice class
     *
     * @param int   $practice_id Practice class ID
     * @param array $data        Updated practice class data
     * @return bool True if successful, false otherwise
     */
    public function update( int $practice_id,array $data ): bool {
        global $wpdb;

        $practice_id = absint( $practice_id );
        if ( ! $practice_id ) {
            return false;
        }

        // Check if practice class exists
        $existing = $this->get_practice_by_id( $practice_id );
        if ( ! $existing ) {
            return false;
        }

        // Prepare update data
        $update_data = [];
        $formats = [];

        // Update only provided fields
        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
            $formats[] = '%s';
        }

        if ( isset( $data['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $data['description'] );
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

        // Update the practice class
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'ID' => $practice_id ),
            $formats,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete a practice class
     *
     * @param int $practice_id Practice class ID
     * @return bool True if successful, false otherwise
     */
    public function delete( $practice_id ) {
        global $wpdb;

        $practice_id = absint( $practice_id );
        if ( ! $practice_id ) {
            return false;
        }

        // Check if practice class exists
        $existing = $this->get_practice_by_id( $practice_id );
        if ( ! $existing ) {
            return false;
        }

        // Delete the practice class
        $result = $wpdb->delete(
            $this->table_name,
            array( 'ID' => $practice_id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get a practice class by ID
     *
     * @param int $practice_id Practice class ID
     * @return object|false Practice class data if found, false otherwise
     */
    public function get_practice_by_id( $practice_id ) {
        global $wpdb;

        $practice_id = absint( $practice_id );
        if ( ! $practice_id ) {
            return false;
        }

        $practice = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE ID = %d",
                $practice_id
            )
        );

        return $practice;
    }

    /**
     * Get all practice classes
     *
     * @param array $args Query arguments
     * @return array Array of practice classes
     */
    public function get_all_practices( $args = array() ) {
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
        $valid_orderby = array( 'ID', 'name', 'spaces' );
        $orderby = in_array( $args['orderby'], $valid_orderby ) ? $args['orderby'] : 'name';
        $order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        // Add limit clause if specified
        if ( $args['limit'] > 0 ) {
            $limit_clause = $wpdb->prepare( ' LIMIT %d, %d', $args['offset'], $args['limit'] );
        }

        // Get all practice classes
        $practices = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY {$orderby} {$order}{$limit_clause}"
        );

        return $practices;
    }
}