<?php
/**
 * Base Repository Class
 *
 * Provides CRUD operations and query building for database tables.
 * All database access should go through repository classes.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Repositories
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Database\Repositories;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Base_Repository Class
 *
 * Provides reusable database operations following the Repository Pattern.
 *
 * @since 3.0.1
 */
abstract class Base_Repository {
	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared

	/**
	 * WordPress database object.
	 *
	 * @since 3.0.1
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Table name (with prefix).
	 *
	 * @since 3.0.1
	 * @var string
	 */
	protected $table;

	/**
	 * Primary key column.
	 *
	 * @since 3.0.1
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * Constructor.
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . $this->get_table_name();
	}

	/**
	 * Get table name (without prefix).
	 *
	 * Must be implemented by child classes.
	 *
	 * @since 3.0.1
	 * @return string Table name without WordPress prefix
	 */
	abstract protected function get_table_name(): string;

	/**
	 * Create new record.
	 *
	 * @since 3.0.1
	 * @param array $data Data to insert.
	 * @return int|false Insert ID or false on failure.
	 */
	public function create( array $data ) {
		// Add timestamps if columns exist and not provided..
		if ( ! isset( $data['created_at'] ) ) {
			$data['created_at'] = current_time( 'mysql' );
		}
		if ( ! isset( $data['updated_at'] ) ) {
			$data['updated_at'] = current_time( 'mysql' );
		}

		$result = $this->wpdb->insert(
			$this->table,
			$data,
			$this->get_format( $data )
		);

		if ( false === $result ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'Database insert failed in %s: %s', get_class( $this ), $this->wpdb->last_error ) );
			}
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Find record by ID.
	 *
	 * @since 3.0.1
	 * @param int $id Record ID.
	 * @return object|null Record object or null if not found.
	 */
	public function find( int $id ) {
		$table       = esc_sql( $this->table );
		$primary_key = esc_sql( $this->primary_key );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				' SELECT * FROM ' . $table . ' WHERE ' . $primary_key . ' = %d',
				$id
			)
		);
	}

	/**
	 * Find all records.
	 *
	 * @since 3.0.1
	 * @param array $args Query arguments (limit, offset, order_by, order).
	 * @return array Array of record objects.
	 */
	public function find_all( array $args = array() ): array {
		$defaults = array(
			'limit'    => 100,
			'offset'   => 0,
			'order_by' => $this->primary_key,
			'order'    => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Sanitize order_by and order to prevent SQL injection..
		$allowed_orders = array( 'ASC', 'DESC' );
		$order          = in_array( strtoupper( $args['order'] ), $allowed_orders, true ) ? strtoupper( $args['order'] ) : 'DESC';
		$order_by       = preg_replace( '/[^a-zA-Z0-9_]/', '', $args['order_by'] );
		$order_by       = $order_by ? $order_by : $this->primary_key;
		$table          = esc_sql( $this->table );
		$order_by_safe  = esc_sql( $order_by );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			' SELECT * FROM ' . $table . ' ORDER BY ' . $order_by_safe . " {$order} LIMIT %d OFFSET %d",
			$args['limit'],
			$args['offset']
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Find records by column value.
	 *
	 * @since 3.0.1
	 * @param string $column Column name.
	 * @param mixed  $value  Value to search for.
	 * @param array  $args   Query arguments.
	 * @return array Array of record objects.
	 */
	public function find_by( string $column, $value, array $args = array() ): array {
		$defaults = array(
			'limit'    => 100,
			'offset'   => 0,
			'order_by' => $this->primary_key,
			'order'    => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Sanitize order..
		$allowed_orders = array( 'ASC', 'DESC' );
		$order          = in_array( strtoupper( $args['order'] ), $allowed_orders, true ) ? strtoupper( $args['order'] ) : 'DESC';
		$order_by       = preg_replace( '/[^a-zA-Z0-9_]/', '', $args['order_by'] );
		$order_by       = $order_by ? $order_by : $this->primary_key;
		$column_name    = preg_replace( '/[^a-zA-Z0-9_]/', '', $column );
		$column_name    = $column_name ? $column_name : $this->primary_key;
		$table          = esc_sql( $this->table );
		$order_by_safe  = esc_sql( $order_by );
		$column_safe    = esc_sql( $column_name );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			' SELECT * FROM ' . $table . ' WHERE ' . $column_safe . ' = %s ORDER BY ' . $order_by_safe . " {$order} LIMIT %d OFFSET %d",
			$value,
			$args['limit'],
			$args['offset']
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Update record by ID.
	 *
	 * @since 3.0.1
	 * @param int   $id   Record ID.
	 * @param array $data Data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update( int $id, array $data ): bool {
		// Update timestamp if not provided..
		if ( ! isset( $data['updated_at'] ) ) {
			$data['updated_at'] = current_time( 'mysql' );
		}

		$result = $this->wpdb->update(
			$this->table,
			$data,
			array( $this->primary_key => $id ),
			$this->get_format( $data ),
			array( '%d' )
		);

		if ( false === $result ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'Database update failed in %s: %s', get_class( $this ), $this->wpdb->last_error ) );
			}
			return false;
		}

		return true;
	}

	/**
	 * Delete record by ID.
	 *
	 * @since 3.0.1
	 * @param int $id Record ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $id ): bool {
		$result = $this->wpdb->delete(
			$this->table,
			array( $this->primary_key => $id ),
			array( '%d' )
		);

		if ( false === $result ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'Database delete failed in %s: %s', get_class( $this ), $this->wpdb->last_error ) );
			}
			return false;
		}

		return true;
	}

	/**
	 * Count total records.
	 *
	 * @since 3.0.1
	 * @param array $where Where conditions (column => value pairs).
	 * @return int Total count.
	 */
	public function count( array $where = array() ): int {
		$table = esc_sql( $this->table );
		$query = ' SELECT COUNT(*) FROM ' . $table;

		if ( ! empty( $where ) ) {
			$conditions = array();
			$values     = array();

			foreach ( $where as $column => $value ) {
				$clean_column = preg_replace( '/[^a-zA-Z0-9_]/', '', $column );
				$clean_column = $clean_column ? $clean_column : $this->primary_key;
				$conditions[] = esc_sql( $clean_column ) . ' = %s';
				$values[]     = $value;
			}

			$query .= ' WHERE ' . implode( ' AND ', $conditions );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $this->wpdb->prepare( $query, $values );
		}

		return (int) $this->wpdb->get_var( $query );
	}

	/**
	 * Check if record exists.
	 *
	 * @since 3.0.1
	 * @param int $id Record ID.
	 * @return bool True if exists, false otherwise.
	 */
	public function exists( int $id ): bool {
		$table       = esc_sql( $this->table );
		$primary_key = esc_sql( $this->primary_key );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				' SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $primary_key . ' = %d',
				$id
			)
		);

		return $count > 0;
	}

	/**
	 * Get data format array for wpdb methods.
	 *
	 * Determines appropriate format (%s, %d, %f) for each value.
	 *
	 * @since 3.0.1
	 * @param array $data Data array.
	 * @return array Format array.
	 */
	protected function get_format( array $data ): array {
		$format = array();

		foreach ( $data as $key => $value ) {
			if ( is_int( $value ) ) {
				$format[] = '%d';
			} elseif ( is_float( $value ) ) {
				$format[] = '%f';
			} else {
				$format[] = '%s';
			}
		}

		return $format;
	}

	/**
	 * Get last database error.
	 *
	 * @since 3.0.1
	 * @return string Error message.
	 */
	public function get_last_error(): string {
		return $this->wpdb->last_error;
	}

	/**
	 * Begin transaction.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function begin_transaction(): void {
		$this->wpdb->query( 'START TRANSACTION' );
	}

	/**
	 * Commit transaction.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function commit(): void {
		$this->wpdb->query( 'COMMIT' );
	}

	/**
	 * Rollback transaction.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function rollback(): void {
		$this->wpdb->query( 'ROLLBACK' );
	}

	/**
	 * Get table name with prefix.
	 *
	 * @since 3.0.1
	 * @return string Full table name with prefix.
	 */
	public function get_table(): string {
		return $this->table;
	}
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
}
