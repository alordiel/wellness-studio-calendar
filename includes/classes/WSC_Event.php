<?php
/**
 * Class for managing event data
 *
 * @package WellnessStudioCalendar
 * @since 1.0.0
 */
namespace Wellness_Studio_Calendar;
// Exit if accessed directly
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Event Model Class
 *
 * Handles CRUD operations for wellness studio events
 */
class WSC_Event {

    /**
     * Event table name
     *
     * @var string
     */
    private string $table_name;

    /**
     * Practice class table name
     *
     * @var string
     */
    private string $practice_class_table;

    /**
     * Instructor table name
     *
     * @var string
     */
    private string $instructor_table;

    /**
     * Location table name
     *
     * @var string
     */
    private string $location_table;

    /**
     * Initialize the class
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wsc_events';
        $this->practice_class_table = $wpdb->prefix . 'wsc_practice_class';
        $this->instructor_table = $wpdb->prefix . 'wsc_instructors';
        $this->location_table = $wpdb->prefix . 'wsc_locations';
    }

	/**
	 * Create a new event
	 *
	 * @param array $data Event data
	 *
	 * @return int Event ID if successful, false otherwise
	 * @throws Exception
	 */
    public function create( array $data ): int {
        global $wpdb;

        // Ensure required fields are present
        $practice_class = isset( $data['practice_class'] ) ? absint( $data['practice_class'] ) : 0;
        $instructor = isset( $data['instructor'] ) ? absint( $data['instructor'] ) : 0;
        $location = isset( $data['location'] ) ? absint( $data['location'] ) : 0;
        $room = isset( $data['room'] ) ? absint( $data['room'] ) : 0;
        $color = isset( $data['color'] ) ? sanitize_hex_color( $data['color'] ) : '';
        $week_day = isset( $data['week_day'] ) ? sanitize_text_field( $data['week_day'] ) : '';
        $start_time = isset( $data['start_time'] ) ? sanitize_text_field( $data['start_time'] ) : '';
        $end_time = isset( $data['end_time'] ) ? sanitize_text_field( $data['end_time'] ) : '';

        // Validate required fields
        if ( !$practice_class || !$instructor || !$location || !$room || empty($week_day) || empty($start_time) || empty($end_time) ) {
			throw new Exception(__('Missing some fields data', 'wellness-studio-calendar'));
        }

        // Validate week_day
        $valid_days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        if ( !in_array(strtolower($week_day), $valid_days) ) {
			throw new Exception(__('Unknown week day', 'wellness-studio-calendar'));
        }

        // Validate time format (24h format with hh:mm)
        if ( !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $start_time) || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $end_time) ) {
            throw new Exception(__('Wrong time format. Please use 24h format as hh:mm', 'wellness-studio-calendar'));
        }

        // Prepare data for insertion
        $event_data = array(
            'practice_class' => $practice_class,
            'instructor'     => $instructor,
            'location'       => $location,
            'room'           => $room,
            'color'          => $color,
            'week_day'       => $week_day,
            'start_time'     => $start_time,
            'end_time'       => $end_time,
        );

        // Insert the event
        $result = $wpdb->insert(
            $this->table_name,
            $event_data,
            array( '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

		if (is_wp_error($result)) {
			throw new Exception(__('Error while executing you query', 'wellness-studio-calendar'));
		}

        throw new Exception(__('Failed to insert entry', 'wellness-studio-calendar'));;
    }

    /**
     * Read an event by ID with related data
     *
     * @param int $event_id Event ID
     * @return object|false Event data if found, false otherwise
     */
    public function get_event( int $event_id ) {
        global $wpdb;

        $event_id = absint( $event_id );
        if ( ! $event_id ) {
            return false;
        }

        $query = $wpdb->prepare(
            "SELECT event.name, event.start_time, event.end_time, event.week_day, 
                practice.name AS practice_class_name, 
                instructor.name AS instructor_name, 
                location.name AS location_name,
            	room.name AS room_name,
            FROM {$this->table_name} event
            LEFT JOIN {$this->practice_class_table} practice ON event.practice_class = practice.ID
            LEFT JOIN {$this->instructor_table} instructor ON event.instructor = instructor.ID
            LEFT JOIN {$this->location_table} location ON event.location = location.ID
            LEFT JOIN {$this->location_table} room ON event.room = room.ID
            WHERE event.ID = %d",
            $event_id
        );

        return $wpdb->get_row( $query );
    }

    /**
     * Update an existing event
     *
     * @param int   $event_id Event ID
     * @param array $data     Updated event data
     * @return bool True if successful, false otherwise
     */
    public function update( int $event_id, array $data ): bool {
        global $wpdb;

        $event_id = absint( $event_id );
        if ( ! $event_id ) {
            return false;
        }

        // Check if event exists
        $existing = $this->get_event_by_id( $event_id );
        if ( ! $existing ) {
            return false;
        }

        // Prepare update data
        $update_data = array();
        $formats = array();

        // Update only provided fields
        if ( isset( $data['practice_class'] ) ) {
            $update_data['practice_class'] = absint( $data['practice_class'] );
            $formats[] = '%d';
        }

        if ( isset( $data['instructor'] ) ) {
            $update_data['instructor'] = absint( $data['instructor'] );
            $formats[] = '%d';
        }

        if ( isset( $data['location'] ) ) {
            $update_data['location'] = absint( $data['location'] );
            $formats[] = '%d';
        }

        if ( isset( $data['room'] ) ) {
            $update_data['room'] = absint( $data['room'] );
            $formats[] = '%d';
        }

        if ( isset( $data['color'] ) ) {
            $update_data['color'] = sanitize_hex_color( $data['color'] );
            $formats[] = '%s';
        }

        if ( isset( $data['week_day'] ) ) {
            $week_day = sanitize_text_field( $data['week_day'] );
            $valid_days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
            if ( in_array(strtolower($week_day), $valid_days) ) {
                $update_data['week_day'] = $week_day;
                $formats[] = '%s';
            }
        }

        if ( isset( $data['start_time'] ) ) {
            $start_time = sanitize_text_field( $data['start_time'] );
            if ( preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $start_time) ) {
                $update_data['start_time'] = $start_time;
                $formats[] = '%s';
            }
        }

        if ( isset( $data['end_time'] ) ) {
            $end_time = sanitize_text_field( $data['end_time'] );
            if ( preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $end_time) ) {
                $update_data['end_time'] = $end_time;
                $formats[] = '%s';
            }
        }

        // If no fields to update, return early
        if ( empty( $update_data ) ) {
            return true;
        }

        // Update the event
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'ID' => $event_id ),
            $formats,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete an event
     *
     * @param int $event_id Event ID
     * @return bool True if successful, false otherwise
     */
    public function delete( int $event_id ): bool {
        global $wpdb;

        $event_id = absint( $event_id );
        if ( ! $event_id ) {
            return false;
        }

        // Check if event exists
        $existing = $this->get_event_by_id( $event_id );
        if ( ! $existing ) {
            return false;
        }

        // Delete the event
        $result = $wpdb->delete(
            $this->table_name,
            array( 'ID' => $event_id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get all events with related data
     *
     * @param array $args Query arguments
     * @return array Array of events
     */
    public function get_all( array $args = [] ): array {
        global $wpdb;

        // Default arguments
        $defaults = array(
            'orderby'       => 'week_day',
            'order'         => 'ASC',
            'limit'         => -1,
            'offset'        => 0,
            'practice_class'=> 0,
            'instructor'    => 0,
            'location'      => 0,
            'week_day'      => '',
        );

        $args = wp_parse_args( $args, $defaults );

        // Base query
        $query = "SELECT e.*, 
                pc.name AS practice_class_name, 
                i.name AS instructor_name, 
                l.name AS location_name
            FROM {$this->table_name} e
            LEFT JOIN {$this->practice_class_table} pc ON e.practice_class = pc.ID
            LEFT JOIN {$this->instructor_table} i ON e.instructor = i.ID
            LEFT JOIN {$this->location_table} l ON e.location = l.ID";

        // Where clauses
        $where = [];
        $prepare_args = [];

        if ( $args['practice_class'] > 0 ) {
            $where[] = 'e.practice_class = %d';
            $prepare_args[] = $args['practice_class'];
        }

        if ( $args['instructor'] > 0 ) {
            $where[] = 'e.instructor = %d';
            $prepare_args[] = $args['instructor'];
        }

        if ( $args['location'] > 0 ) {
            $where[] = 'e.location = %d';
            $prepare_args[] = $args['location'];
        }

        if ( !empty($args['week_day']) ) {
            $where[] = 'e.week_day = %s';
            $prepare_args[] = $args['week_day'];
        }

        // Add WHERE clauses if any
        if ( !empty($where) ) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        // Order clause
        $valid_order_columns = array('ID', 'practice_class', 'instructor', 'location', 'room', 'week_day', 'start_time', 'end_time');
        $orderby = in_array($args['orderby'], $valid_order_columns) ? $args['orderby'] : 'week_day';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

        $query .= " ORDER BY e.{$orderby} {$order}";

        // Limit clause
        if ( $args['limit'] > 0 ) {
            $query .= $wpdb->prepare(" LIMIT %d, %d", $args['offset'], $args['limit']);
        }

        // Prepare the full query if we have args to prepare
        if (!empty($prepare_args)) {
            $query = $wpdb->prepare($query, $prepare_args);
        }

        // Get all events
	    return $wpdb->get_results($query);
    }

    /**
     * Count total events
     *
     * @return int Total number of events
     */
    public function count(): int {
        global $wpdb;
        // Base query
        $query = "SELECT COUNT(*) FROM {$this->table_name}";

        return (int) $wpdb->get_var($query);
    }

    /**
     * Get an event by ID (without joins)
     *
     * @param int $event_id Event ID
     * @return array|object|\stdClass|null Event data if found, false otherwise
     */
    private function get_event_by_id( int $event_id ) {
        global $wpdb;

        return $wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE ID = $event_id");
    }

    /**
     * Get events by day of week
     *
     * @param string $week_day Day of the week (monday, tuesday, etc.)
     *
     * @return array Array of events for the specified day
     */
    public function get_by_day( string $week_day ): array {
        return $this->get_all( array( 'week_day' => $week_day, 'orderby' => 'start_time' ) );
    }

    /**
     * Get events by instructor
     *
     * @param int $instructor_id Instructor ID
     *
     * @return array Array of events for the specified instructor
     */
    public function get_by_instructor( int $instructor_id ): array {
        return $this->get_all( array( 'instructor' => absint( $instructor_id ) ) );
    }

    /**
     * Get events by practice class
     *
     * @param int $practice_class_id Practice class ID
     *
     * @return array Array of events for the specified practice class
     */
    public function get_by_practice_class( int $practice_class_id ): array {
        return $this->get_all( array( 'practice_class' => absint( $practice_class_id ) ) );
    }

    /**
     * Get events by location
     *
     * @param int $location_id Location ID
     *
     * @return array Array of events for the specified location
     */
    public function get_by_location( int $location_id ): array {
        return $this->get_all( array( 'location' => absint( $location_id ) ) );
    }

}