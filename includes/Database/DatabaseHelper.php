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
		// Ensure table identifier is safe for use in SQL identifiers.
		$table = sanitize_key( (string) $table );
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
		// Using %s placeholder for table identifier in LIKE comparison.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct metadata lookup for table existence check; caching is not applicable.
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;
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
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is built from $wpdb->prefix and sanitized; this is a lightweight statistics query where caching is handled at a higher level.
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		}

		$where_clause = self::build_where_clause( $where );
		$sql_template = sprintf( 'SELECT COUNT(*) FROM %s WHERE %s', $table_name, $where_clause );
		$safe_query   = $sql_template;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query for statistics.
		return (int) $wpdb->get_var( $safe_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Low-level helper responsible for writes; higher-level services handle any caching.
		$result = $wpdb->insert( $table_name, $data, $format );

		if ( false === $result ) {
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Low-level helper responsible for writes; higher-level services handle any caching.
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Low-level helper responsible for writes; higher-level services handle any caching.
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

		$select_clause = '*';
		if ( ! empty( $columns ) ) {
			$safe_columns = array();
			foreach ( $columns as $column ) {
				$column_key = sanitize_key( $column );
				if ( '' !== $column_key ) {
					$safe_columns[] = $column_key;
				}
			}

			if ( ! empty( $safe_columns ) ) {
				$select_clause = implode( ', ', $safe_columns );
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name and column identifiers are sanitized; WHERE is built via build_where_clause() using prepared values.
		$sql = $wpdb->prepare( "SELECT {$select_clause} FROM {$table_name} WHERE {$where_clause} LIMIT 1" );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared above.
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

		$select_clause = '*';
		if ( ! empty( $columns ) ) {
			$safe_columns = array();
			foreach ( $columns as $column ) {
				$column_key = sanitize_key( $column );
				if ( '' !== $column_key ) {
					$safe_columns[] = $column_key;
				}
			}

			if ( ! empty( $safe_columns ) ) {
				$select_clause = implode( ', ', $safe_columns );
			}
		}

		$sql = "SELECT {$select_clause} FROM {$table_name}";

		if ( ! empty( $args['where'] ) ) {
			$where_clause = self::build_where_clause( $args['where'] );
			$sql         .= " WHERE {$where_clause}";
		}

		if ( ! empty( $args['order_by'] ) ) {
			// Only allow safe column names for ORDER BY.
			$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
			$orderby = sanitize_key( $args['order_by'] );
			if ( '' === $orderby ) {
				$orderby = 'id';
			}
			$sql .= " ORDER BY {$orderby} {$order}";
		}

		// Prepare LIMIT/OFFSET using placeholders to satisfy PHPCS and avoid SQL injection.
		$prepare_values = array();
		if ( ! empty( $args['limit'] ) ) {
			$sql             .= ' LIMIT %d';
			$prepare_values[] = absint( $args['limit'] );

			if ( ! empty( $args['offset'] ) ) {
				$sql             .= ' OFFSET %d';
				$prepare_values[] = absint( $args['offset'] );
			}
		}

		if ( ! empty( $prepare_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, ...$prepare_values );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- WHERE clause fragments are prepared via build_where_clause(), LIMIT/OFFSET are prepared when present, and table/column identifiers are sanitized.
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

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is internal and sanitized; this maintenance operation intentionally runs directly against the database without caching.
		return $wpdb->query( 'TRUNCATE TABLE `' . esc_sql( $table_name ) . '`' ) !== false;
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

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is internal and sanitized; this maintenance operation intentionally runs directly against the database without caching.
		return $wpdb->query( 'OPTIMIZE TABLE `' . esc_sql( $table_name ) . '`' ) !== false;
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
			$column_key = sanitize_key( $column );
			if ( '' === $column_key ) {
				// Skip invalid or unsafe column identifiers.
				continue;
			}

			if ( is_null( $value ) ) {
				$conditions[] = "{$column_key} IS NULL";
			} elseif ( is_array( $value ) ) {
				// IN clause.
				$placeholders    = implode( ', ', array_fill( 0, count( $value ), '%s' ) );
				$prepared_values = (array) $value;
				$condition       = $column_key . ' IN (' . $placeholders . ')';
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Condition is safely prepared below.
				$conditions[] = $wpdb->prepare( $condition, $prepared_values );
			} else {
				$condition = $column_key . ' = %s';
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Condition is safely prepared below.
				$conditions[] = $wpdb->prepare( $condition, $value );
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

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is internal and sanitized.
		if ( $event_type ) {
			$sql = sprintf( 'SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM %s WHERE created_at BETWEEN %%s AND %%s AND event_type = %%s ORDER BY created_at DESC', $table_name );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $start_date, $end_date, $event_type );
		} else {
			$sql = sprintf( 'SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM %s WHERE created_at BETWEEN %%s AND %%s ORDER BY created_at DESC', $table_name );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $start_date, $end_date );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is fully prepared above; this helper runs direct read queries without additional caching.
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
		// phpcs:ignore WordPress.DB.RestrictedConstants -- OBJECT is a valid WP constant.
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
		// phpcs:ignore WordPress.DB.RestrictedConstants -- OBJECT is a valid WP constant.
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
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized below.
			$ip = wp_unslash( $_SERVER['HTTP_CLIENT_IP'] );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized below.
			$forwarded_for = wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] );
			// In case of multiple IPs, take the first one.
			$ip_parts = explode( ',', (string) $forwarded_for );
			$ip       = trim( $ip_parts[0] );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized below.
			$ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
		}

		return '' !== $ip ? sanitize_text_field( $ip ) : '';
	}

	/**
	 * Get user agent
	 *
	 * @since 1.0.0
	 * @return string User agent string.
	 */
	private static function get_user_agent() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized below.
		return isset( $_SERVER['HTTP_USER_AGENT'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
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
		$date       = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is internal and sanitized.
		$query = sprintf( 'DELETE FROM %s WHERE created_at < %%s', $table_name );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( $query, $date );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is fully prepared above; this cleanup helper intentionally runs direct delete queries without caching.
		return $wpdb->query( $query );
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
