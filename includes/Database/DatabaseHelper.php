<?php
/**
 * Database Helper Utilities
 *
 * Provides helper methods for common database operations.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Database
 * @license    GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Helper Class
 *
 * Provides convenient methods for database operations.
 *
 * @since 1.0.0
 */
class DatabaseHelper {

	/**
	 * Get table name with prefix
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @return string Full table name with prefix.
	 */
	public static function get_table_name( $table ) {
		global $wpdb;
		return $wpdb->prefix . 'shahi_' . $table;
	}

	/**
	 * Check if table exists
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @return bool True if table exists, false otherwise.
	 */
	public static function table_exists( $table ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		return $wpdb->get_var( $query ) === $table_name;
	}

	/**
	 * Get table row count
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @param array  $where Optional. WHERE conditions.
	 * @return int Number of rows.
	 */
	public static function get_row_count( $table, $where = array() ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		if ( empty( $where ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table_name ) );
		}

		$where_clause = self::build_where_clause( $where );
		$sql          = $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ', $table_name ) . $where_clause;

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Insert record
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @param array  $data Data to insert.
	 * @param array  $format Optional. Data format.
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function insert( $table, $data, $format = null ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		$result = $wpdb->insert( $table_name, $data, $format );

		if ( $result === false ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update records
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @param array  $data Data to update.
	 * @param array  $where WHERE conditions.
	 * @param array  $format Optional. Data format.
	 * @param array  $where_format Optional. WHERE format.
	 * @return int|false Number of rows affected, false on failure.
	 */
	public static function update( $table, $data, $where, $format = null, $where_format = null ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		return $wpdb->update( $table_name, $data, $where, $format, $where_format );
	}

	/**
	 * Delete records
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @param array  $where WHERE conditions.
	 * @param array  $where_format Optional. WHERE format.
	 * @return int|false Number of rows affected, false on failure.
	 */
	public static function delete( $table, $where, $where_format = null ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		return $wpdb->delete( $table_name, $where, $where_format );
	}

	/**
	 * Get single row
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @param array  $where WHERE conditions.
	 * @param string $output Optional. Output type (OBJECT, ARRAY_A, ARRAY_N).
	 * @param array  $columns Optional. Specific columns to select (default: all).
	 * @return mixed|null Row data or null if not found.
	 */
	public static function get_row( $table, $where, $output = OBJECT, $columns = array() ) {
		global $wpdb;
		$table_name   = self::get_table_name( $table );
		$where_clause = self::build_where_clause( $where );

		$select_clause = ! empty( $columns ) ? implode( ', ', $columns ) : '*';
		$sql           = "SELECT {$select_clause} FROM {$table_name} WHERE {$where_clause} LIMIT 1";

		return $wpdb->get_row( $sql, $output );
	}

	/**
	 * Get multiple rows
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @param array  $args Query arguments.
	 * @param array  $columns Optional. Specific columns to select (default: all).
	 * @return array Array of row objects.
	 */
	public static function get_rows( $table, $args = array(), $columns = array() ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		$defaults = array(
			'where'    => array(),
			'order_by' => 'id',
			'order'    => 'DESC',
			'limit'    => 100,
			'offset'   => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$select_clause = ! empty( $columns ) ? implode( ', ', $columns ) : '*';
		$sql           = "SELECT {$select_clause} FROM {$table_name}";

		if ( ! empty( $args['where'] ) ) {
			$where_clause = self::build_where_clause( $args['where'] );
			$sql         .= " WHERE {$where_clause}";
		}

		if ( ! empty( $args['order_by'] ) ) {
			$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
			$sql  .= " ORDER BY {$args['order_by']} {$order}";
		}

		if ( ! empty( $args['limit'] ) ) {
			$sql .= " LIMIT {$args['limit']}";

			if ( ! empty( $args['offset'] ) ) {
				$sql .= " OFFSET {$args['offset']}";
			}
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Truncate table
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @return bool True on success, false on failure.
	 */
	public static function truncate( $table ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		return $wpdb->query( "TRUNCATE TABLE {$table_name}" ) !== false;
	}

	/**
	 * Optimize table
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix.
	 * @return bool True on success, false on failure.
	 */
	public static function optimize( $table ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );

		return $wpdb->query( "OPTIMIZE TABLE {$table_name}" ) !== false;
	}

	/**
	 * Build WHERE clause from array
	 *
	 * @since 1.0.0
	 * @param array $where WHERE conditions.
	 * @return string WHERE clause.
	 */
	private static function build_where_clause( $where ) {
		global $wpdb;
		$conditions = array();

		foreach ( $where as $column => $value ) {
			if ( is_null( $value ) ) {
				$conditions[] = "{$column} IS NULL";
			} elseif ( is_array( $value ) ) {
				// IN clause..
				$placeholders = implode( ', ', array_fill( 0, count( $value ), '%s' ) );
				$conditions[] = $wpdb->prepare( "{$column} IN ({$placeholders})", $value );
			} else {
				$conditions[] = $wpdb->prepare( "{$column} = %s", $value );
			}
		}

		return implode( ' AND ', $conditions );
	}

	/**
	 * Get analytics data for date range
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date (Y-m-d H:i:s).
	 * @param string $end_date End date (Y-m-d H:i:s).
	 * @param string $event_type Optional. Filter by event type.
	 * @return array Array of analytics records.
	 */
	public static function get_analytics( $start_date, $end_date, $event_type = null ) {
		global $wpdb;
		$table_name = self::get_table_name( 'analytics' );

		if ( $event_type ) {
			$sql = $wpdb->prepare(
				"SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM {$table_name} 
                WHERE created_at BETWEEN %s AND %s 
                AND event_type = %s 
                ORDER BY created_at DESC",
				$start_date,
				$end_date,
				$event_type
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM {$table_name} 
                WHERE created_at BETWEEN %s AND %s 
                ORDER BY created_at DESC",
				$start_date,
				$end_date
			);
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Track analytics event
	 *
	 * @since 1.0.0
	 * @param string $event_type Event type.
	 * @param array  $event_data Event data.
	 * @param int    $user_id Optional. User ID.
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function track_event( $event_type, $event_data = array(), $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$data = array(
			'event_type' => sanitize_text_field( $event_type ),
			'event_data' => wp_json_encode( $event_data ),
			'user_id'    => $user_id ? absint( $user_id ) : null,
			'ip_address' => self::get_user_ip(),
			'user_agent' => self::get_user_agent(),
		);

		return self::insert( 'analytics', $data, array( '%s', '%s', '%d', '%s', '%s' ) );
	}

	/**
	 * Get module settings
	 *
	 * @since 1.0.0
	 * @param string $module_key Module key.
	 * @return array|null Module settings or null if not found.
	 */
	public static function get_module_settings( $module_key ) {
		$row = self::get_row( 'modules', array( 'module_key' => $module_key ), OBJECT, array( 'module_key', 'is_enabled', 'settings', 'last_updated' ) );

		if ( ! $row ) {
			return null;
		}

		return array(
			'is_enabled'   => (bool) $row->is_enabled,
			'settings'     => json_decode( $row->settings, true ),
			'last_updated' => $row->last_updated,
		);
	}

	/**
	 * Update module settings
	 *
	 * @since 1.0.0
	 * @param string $module_key Module key.
	 * @param bool   $is_enabled Whether module is enabled.
	 * @param array  $settings Module settings.
	 * @return bool True on success, false on failure.
	 */
	public static function update_module_settings( $module_key, $is_enabled, $settings = array() ) {
		$existing = self::get_row( 'modules', array( 'module_key' => $module_key ), OBJECT, array( 'module_key' ) );

		$data = array(
			'is_enabled' => $is_enabled ? 1 : 0,
			'settings'   => wp_json_encode( $settings ),
		);

		if ( $existing ) {
			return self::update(
				'modules',
				$data,
				array( 'module_key' => $module_key ),
				array( '%d', '%s' ),
				array( '%s' )
			) !== false;
		} else {
			$data['module_key'] = $module_key;
			return self::insert(
				'modules',
				$data,
				array( '%s', '%d', '%s' )
			) !== false;
		}
	}

	/**
	 * Get user IP address
	 *
	 * @since 1.0.0
	 * @return string User IP address.
	 */
	private static function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return sanitize_text_field( $ip );
	}

	/**
	 * Get user agent
	 *
	 * @since 1.0.0
	 * @return string User agent string.
	 */
	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] )
			? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] )
			: '';
	}

	/**
	 * Clean old analytics data
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to keep.
	 * @return int|false Number of rows deleted, false on failure.
	 */
	public static function clean_old_analytics( $days = 90 ) {
		global $wpdb;
		$table_name = self::get_table_name( 'analytics' );
		$date       = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE created_at < %s",
				$date
			)
		);
	}

	/**
	 * Get database statistics
	 *
	 * @since 1.0.0
	 * @return array Database statistics.
	 */
	public static function get_statistics() {
		return array(
			'analytics_count'         => self::get_row_count( 'analytics' ),
			'modules_count'           => self::get_row_count( 'modules' ),
			'onboarding_count'        => self::get_row_count( 'onboarding' ),
			'analytics_enabled_count' => self::get_row_count( 'modules', array( 'is_enabled' => 1 ) ),
		);
	}
}
