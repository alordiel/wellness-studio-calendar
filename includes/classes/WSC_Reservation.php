<?php
/**
 * Class for managing reservation data
 *
 * @package WellnessStudioCalendar
 * @since 1.0.0
 */

namespace Wellness_Studio_Calendar;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Exception;

/**
 * Reservation Model Class
 *
 * Handles CRUD operations for wellness studio reservations
 */
class WSC_Reservation {
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
        $this->table_name = $wpdb->prefix . 'wsc_reservations';
    }

    /**
     * Create a new reservation
     *
     * @param array $data Reservation data
     * @return int Reservation ID
     * @throws Exception If validation fails
     */
    public function create(array $data): int {
        global $wpdb;

        // Validate required fields
        $event_id = isset($data['event']) ? absint($data['event']) : 0;
        if (!$event_id) {
            throw new Exception('Event ID is required');
        }

        $user_name = isset($data['user_name']) ? sanitize_text_field($data['user_name']) : '';
        if (empty($user_name)) {
            throw new Exception('User name is required');
        }

        $email = isset($data['email']) ? sanitize_email($data['email']) : '';
        if (empty($email) || !is_email($email)) {
            throw new Exception('Valid email is required');
        }

        $payment = isset($data['payment']) ? sanitize_text_field($data['payment']) : '';
        $available_payment_methods = get_option('wsc_available_payment_methods', []);
        if (empty($payment) || (!empty($available_payment_methods) && !in_array($payment, $available_payment_methods))) {
            throw new Exception('Valid payment method is required');
        }

        // Set default values and sanitize optional fields
        $phone = isset($data['phone']) ? sanitize_text_field($data['phone']) : '';
        $user_notes = isset($data['user_notes']) ? sanitize_textarea_field($data['user_notes']) : '';

        // Always set status to 'approved' on creation and admin_notes to empty
        $status = 'approved';
        $admin_notes = '';

        // Generate cancellation hash
        $cancellation_hash = wp_generate_password(64, false);

        // Prepare data for insertion
        $reservation_data = [
            'event'            => $event_id,
            'status'           => $status,
            'user_name'        => $user_name,
            'email'            => $email,
            'phone'            => $phone,
            'payment'          => $payment,
            'user_notes'       => $user_notes,
            'admin_notes'      => $admin_notes,
            'cancellation_hash'=> $cancellation_hash,
        ];

        // Insert the reservation
        $result = $wpdb->insert(
            $this->table_name,
            $reservation_data,
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if (!$result) {
            throw new Exception('Failed to create reservation: ' . $wpdb->last_error);
        }

        return $wpdb->insert_id;
    }

    /**
     * Update an existing reservation
     *
     * @param int   $reservation_id Reservation ID
     * @param array $data           Updated reservation data
     * @return bool True if successful
     * @throws Exception If validation fails
     */
    public function update(int $reservation_id, array $data): bool {
        global $wpdb;

        if ($reservation_id <= 0) {
            throw new Exception('Invalid reservation ID');
        }

        // Check if reservation exists
        $existing = $this->get_reservation_by_id($reservation_id);
        if (!$existing) {
            throw new Exception('Reservation not found');
        }

        // Prepare update data
        $update_data = [];
        $formats = [];
		$valid_statuses = ['approved', 'cancelled', 'no-show'];
		$available_payment_methods = get_option('wsc_available_payment_methods', []);

        // Update only provided fields with validation
        if (!empty($data['event'])) {
            $event_id = absint($data['event']);
            $update_data['event'] = $event_id;
            $formats[] = '%d';
        } else {
			throw new Exception('Invalid event ID');
        }

        if (!empty($data['status']) && in_array($data['status'], $valid_statuses)) {
            $status = sanitize_text_field($data['status']);
            $update_data['status'] = $status;
            $formats[] = '%s';
        } else {
			throw new Exception('Invalid status. Must be: approved, cancelled, or no-show');
        }

        if (!empty($data['user_name'])) {
            $user_name = sanitize_text_field($data['user_name']);
            $update_data['user_name'] = $user_name;
            $formats[] = '%s';
        } else {
			throw new Exception('User name cannot be empty');
        }

        if (!empty($data['email']) || !is_email($data['email'])) {
            $email = sanitize_email($data['email']);
            $update_data['email'] = $email;
            $formats[] = '%s';
        } else {
			throw new Exception('Valid email is required');
        }

        if (!empty($data['phone'])) {
            $update_data['phone'] = sanitize_text_field($data['phone']);
            $formats[] = '%s';
        }

        if (!empty($data['payment']) && !in_array($data['payment'], $available_payment_methods)) {
            $update_data['payment'] = $data['payment'];
            $formats[] = '%s';
        }

        if (!empty($data['user_notes'])) {
            $update_data['user_notes'] = sanitize_textarea_field($data['user_notes']);
            $formats[] = '%s';
        }

        if (!empty($data['admin_notes'])) {
            $update_data['admin_notes'] = sanitize_textarea_field($data['admin_notes']);
            $formats[] = '%s';
        }

        // Update the reservation
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            ['ID' => $reservation_id],
            $formats,
            ['%d']
        );

        if ($result === false) {
            throw new Exception('Failed to update reservation: ' . $wpdb->last_error);
        }

        return true;
    }

    /**
     * Delete a reservation
     *
     * @param int $reservation_id Reservation ID
     * @return bool True if successful
     * @throws Exception If validation fails
     */
    public function delete(int $reservation_id): bool {
        global $wpdb;

        if ($reservation_id <= 0) {
            throw new Exception('Invalid reservation ID');
        }

        // Check if reservation exists
        $existing = $this->get_reservation_by_id($reservation_id);
        if (!$existing) {
            throw new Exception('Reservation not found');
        }

        // Delete the reservation
        $result = $wpdb->delete(
            $this->table_name,
            ['ID' => $reservation_id],
            ['%d']
        );

        if (!$result) {
            throw new Exception('Failed to delete reservation: ' . $wpdb->last_error);
        }

        return true;
    }

    /**
     * Get a reservation by ID
     *
     * @param int $reservation_id Reservation ID
     * @return object|null Reservation data if found, null otherwise
     */
    public function get_reservation_by_id(int $reservation_id): ?object {
        global $wpdb;

        if ($reservation_id <= 0) {
            return null;
        }

        $reservation = $wpdb->get_row("SELECT * FROM {$this->table_name} WHERE ID = $reservation_id",);

        return $reservation ?: null;
    }

    /**
     * Get all reservations
     *
     * @param array $args Query arguments
     * @return array Array of reservations
     */
    public function get_all_reservations(array $args = []): array {
        global $wpdb;

        // Default arguments
        $defaults = [
            'event'    => 0,
            'status'   => '',
            'orderby'  => 'ID',
            'order'    => 'DESC',
            'limit'    => -1,
            'offset'   => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        // Build the query
        $query = "SELECT * FROM {$this->table_name}";
        $where = [];
        $prepare_args = [];

        // Filter by event if specified
        if (!empty($args['event'])) {
            $event_id = absint($args['event']);
            if ($event_id > 0) {
                $where[] = 'event = %d';
                $prepare_args[] = $event_id;
            }
        }

        // Filter by status if specified
        if (!empty($args['status'])) {
            $status = sanitize_text_field($args['status']);
            $where[] = 'status = %s';
            $prepare_args[] = $status;
        }

        // Add WHERE clauses if any
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        // Add ORDER BY clause
        $valid_orderby = ['ID', 'event', 'status', 'user_name', 'email'];
        $orderby = in_array($args['orderby'], $valid_orderby) ? $args['orderby'] : 'ID';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

        $query .= " ORDER BY {$orderby} {$order}";

        // Add LIMIT clause if specified
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d, %d", $args['offset'], $args['limit']);
        }

        // Prepare the full query if we have args to prepare
        if (!empty($prepare_args)) {
            $query = $wpdb->prepare($query, $prepare_args);
        }

        // Get all reservations
        $reservations = $wpdb->get_results($query);

        return $reservations ?: [];
    }

    /**
     * Get reservation by cancellation hash
     *
     * @param string $hash Cancellation hash
     * @return object|null Reservation data if found, null otherwise
     */
    public function get_reservation_by_hash(string $hash): ?object {
        global $wpdb;

        if (empty($hash)) {
            return null;
        }

        $reservation = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE cancellation_hash = %s",
                $hash
            )
        );

        return $reservation ?: null;
    }

    /**
     * Add admin note to a reservation
     *
     * @param int    $reservation_id Reservation ID
     * @param string $message        Note message
     * @return bool True if successful
     * @throws Exception If validation fails
     */
    public function add_admin_note(int $reservation_id, string $message): bool {
        global $wpdb;

        if ($reservation_id <= 0) {
            throw new Exception('Invalid reservation ID');
        }

        // Check if reservation exists
        $existing = $this->get_reservation_by_id($reservation_id);
        if (!$existing) {
            throw new Exception('Reservation not found');
        }

        $message = sanitize_textarea_field($message);

        // Append new note to existing notes
        $current_notes = $existing->admin_notes;
        $date = current_time('mysql');

        $updated_notes = $current_notes;
        if (!empty($updated_notes)) {
            $updated_notes .= "\n\n";
        }
        $updated_notes .= "[{$date}] {$message}";

        // Update the admin_notes field
        $result = $wpdb->update(
            $this->table_name,
            ['admin_notes' => $updated_notes],
            ['ID' => $reservation_id],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            throw new Exception('Failed to add admin note: ' . $wpdb->last_error);
        }

        return true;
    }
}