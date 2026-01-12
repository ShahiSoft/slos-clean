<?php
/**
 * Config Sync Service
 *
 * Handles export and import of compliance configuration across WordPress sites.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load schema functions..
require_once SHAHI_LEGALFLOWSUITE_PATH . 'config/multisite-sync-schema.php';

/**
 * Config Sync Service Class
 *
 * @since 3.1.1
 */
class Config_Sync_Service {

	/**
	 * Export configuration to JSON
	 *
	 * @since 3.1.1
	 * @param array $options Options to export (option keys)
	 * @param array $metadata Profile metadata (name, description, etc.)
	 * @return array|WP_Error Export data or error
	 */
	public function export_config( $options = array(), $metadata = array() ) {
		// Use default options if none specified..
		if ( empty( $options ) ) {
			$options = slos_get_default_export_options();
		}

		// Validate options against exclusion list..
		$excluded = slos_get_config_sync_schema()['exclusions']['never_sync'];
		$options  = array_diff( $options, $excluded );

		// Build profile..
		$profile = slos_get_config_profile_template();

		// Merge custom metadata..
		if ( ! empty( $metadata['name'] ) ) {
			$profile['profile']['name'] = sanitize_text_field( $metadata['name'] );
		}
		if ( ! empty( $metadata['description'] ) ) {
			$profile['profile']['description'] = sanitize_textarea_field( $metadata['description'] );
		}

		// Export settings..
		$settings = array();
		foreach ( $options as $option_key ) {
			$value = get_option( $option_key, null );
			if ( null !== $value ) {
				$settings[ $option_key ] = $value;
			}
		}

		$profile['settings'] = $settings;

		// Detect active modules..
		$profile['modules'] = $this->detect_active_modules( $options );

		// Validate profile..
		$validation = slos_validate_config_profile( $profile );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		/**
		 * Filter config profile before export
		 *
		 * @since 3.1.1
		 * @param array $profile Configuration profile
		 * @param array $options Option keys being exported
		 */
		$profile = apply_filters( 'slos_config_export_profile', $profile, $options );

		return $profile;
	}

	/**
	 * Import configuration from JSON
	 *
	 * @since 3.1.1
	 * @param array $profile Configuration profile to import
	 * @param array $options Options controlling import (merge, overwrite, etc.)
	 * @return array|WP_Error Import results or error
	 */
	public function import_config( $profile, $options = array() ) {
		$default_options = array(
			'merge'             => false,  // Merge with existing or overwrite
			'skip_validation'   => false,  // Skip validation (dangerous!)
			'dry_run'           => false,  // Test without making changes
			'selected_settings' => array(), // Import only specific settings
		);

		$options = wp_parse_args( $options, $default_options );

		// Validate profile..
		if ( ! $options['skip_validation'] ) {
			$validation = slos_validate_config_profile( $profile );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
		}

		// Sanitize profile..
		$profile = slos_sanitize_config_profile( $profile );

		// Determine which settings to import..
		$settings_to_import = ! empty( $options['selected_settings'] )
			? array_intersect_key( $profile['settings'], array_flip( $options['selected_settings'] ) )
			: $profile['settings'];

		// Filter against safe import list..
		$safe_options       = slos_get_safe_import_options();
		$settings_to_import = array_intersect_key( $settings_to_import, array_flip( $safe_options ) );

		$results = array(
			'imported' => array(),
			'skipped'  => array(),
			'errors'   => array(),
			'warnings' => array(),
			'dry_run'  => $options['dry_run'],
		);

		// Import each setting..
		foreach ( $settings_to_import as $option_key => $value ) {
			try {
				$result = $this->import_option( $option_key, $value, $options );

				if ( is_wp_error( $result ) ) {
					$results['errors'][ $option_key ] = $result->get_error_message();
				} elseif ( $result['imported'] ) {
					$results['imported'][ $option_key ] = $result['message'];
				} else {
					$results['skipped'][ $option_key ] = $result['message'];
				}

				if ( ! empty( $result['warnings'] ) ) {
					$results['warnings'][ $option_key ] = $result['warnings'];
				}
			} catch ( \Exception $e ) {
				$results['errors'][ $option_key ] = $e->getMessage();
			}
		}

		/**
		 * Fires after configuration import
		 *
		 * @since 3.1.1
		 * @param array $results Import results
		 * @param array $profile Imported configuration profile
		 * @param array $options Import options
		 */
		do_action( 'slos_config_imported', $results, $profile, $options );

		return $results;
	}

	/**
	 * Import single option
	 *
	 * @since 3.1.1
	 * @param string $option_key Option key
	 * @param mixed  $value Option value
	 * @param array  $options Import options
	 * @return array|WP_Error Import result
	 */
	private function import_option( $option_key, $value, $options ) {
		$result = array(
			'imported' => false,
			'message'  => '',
			'warnings' => array(),
		);

		// Check if option exists..
		$current_value = get_option( $option_key, null );
		$option_exists = null !== $current_value;

		// Handle merge mode..
		if ( $options['merge'] && $option_exists && is_array( $current_value ) && is_array( $value ) ) {
			$value                = array_merge( $current_value, $value );
			$result['warnings'][] = __( 'Merged with existing data', 'shahi-legalflowsuite' );
		}

		// Validate value using schema..
		$validated_value = $this->validate_option_value( $option_key, $value );
		if ( is_wp_error( $validated_value ) ) {
			return $validated_value;
		}

		// Dry run: don't actually update..
		if ( $options['dry_run'] ) {
			$result['imported'] = true;
			$result['message']  = __( 'Would be imported (dry run)', 'shahi-legalflowsuite' );
			return $result;
		}

		// Update option..
		$updated = update_option( $option_key, $validated_value, false );

		if ( $updated || get_option( $option_key ) === $validated_value ) {
			$result['imported'] = true;
			$result['message']  = $option_exists
				? __( 'Updated successfully', 'shahi-legalflowsuite' )
				: __( 'Created successfully', 'shahi-legalflowsuite' );
		} else {
			return new WP_Error(
				'import_failed',
				sprintf(
					/* translators: %s: option key */
					__( 'Failed to import option: %s', 'shahi-legalflowsuite' ),
					$option_key
				)
			);
		}

		return $result;
	}

	/**
	 * Validate option value against schema
	 *
	 * @since 3.1.1
	 * @param string $option_key Option key
	 * @param mixed  $value Option value
	 * @return mixed|WP_Error Validated value or error
	 */
	private function validate_option_value( $option_key, $value ) {
		$schema = slos_get_config_sync_schema();

		// Banner settings validation..
		if ( 'slos_banner_settings' === $option_key ) {
			return $this->validate_banner_settings( $value );
		}

		// Geo rules validation..
		if ( 'slos_geo_rules' === $option_key ) {
			return $this->validate_geo_rules( $value );
		}

		// Generic array/object validation..
		if ( is_array( $value ) || is_object( $value ) ) {
			return $value;
		}

		return $value;
	}

	/**
	 * Validate banner settings
	 *
	 * @since 3.1.1
	 * @param array $settings Banner settings
	 * @return array|WP_Error Validated settings or error
	 */
	private function validate_banner_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return new WP_Error( 'invalid_settings', __( 'Banner settings must be an array', 'shahi-legalflowsuite' ) );
		}

		$schema = slos_get_config_sync_schema()['validation_rules']['banner_settings'];

		// Validate position..
		if ( ! empty( $settings['position'] ) && ! in_array( $settings['position'], $schema['position']['values'], true ) ) {
			$settings['position'] = $schema['position']['default'];
		}

		// Validate theme..
		if ( ! empty( $settings['theme'] ) && ! in_array( $settings['theme'], $schema['theme']['values'], true ) ) {
			$settings['theme'] = $schema['theme']['default'];
		}

		// Ensure enabled is boolean..
		if ( isset( $settings['enabled'] ) ) {
			$settings['enabled'] = (bool) $settings['enabled'];
		}

		return $settings;
	}

	/**
	 * Validate geo rules
	 *
	 * @since 3.1.1
	 * @param array $rules Geo rules
	 * @return array|WP_Error Validated rules or error
	 */
	private function validate_geo_rules( $rules ) {
		if ( ! is_array( $rules ) ) {
			return new WP_Error( 'invalid_rules', __( 'Geo rules must be an array', 'shahi-legalflowsuite' ) );
		}

		$schema          = slos_get_config_sync_schema()['validation_rules']['geo_rules'];
		$valid_models    = $schema['consent_model']['values'];
		$validated_rules = array();

		foreach ( $rules as $index => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			// Validate priority..
			if ( isset( $rule['priority'] ) ) {
				$rule['priority'] = max( $schema['priority']['min'], min( $schema['priority']['max'], (int) $rule['priority'] ) );
			}

			// Validate consent model..
			if ( ! empty( $rule['consent_model'] ) && ! in_array( $rule['consent_model'], $valid_models, true ) ) {
				$rule['consent_model'] = 'opt-in'; // Safe default
			}

			// Validate countries is array..
			if ( ! empty( $rule['countries'] ) && ! is_array( $rule['countries'] ) ) {
				$rule['countries'] = array();
			}

			$validated_rules[] = $rule;
		}

		return $validated_rules;
	}

	/**
	 * Detect active modules from exported options
	 *
	 * @since 3.1.1
	 * @param array $options Exported option keys
	 * @return array Active modules
	 */
	private function detect_active_modules( $options ) {
		$schema  = slos_get_config_sync_schema();
		$modules = array();

		foreach ( $schema['modules'] as $module_key => $module_config ) {
			$module_options = $module_config['options'];
			$has_options    = ! empty( array_intersect( $options, $module_options ) );

			$modules[ $module_key ] = array(
				'enabled'      => $module_config['enabled'] && $has_options,
				'options_used' => array_intersect( $options, $module_options ),
			);
		}

		return $modules;
	}

	/**
	 * Export configuration to JSON file
	 *
	 * @since 3.1.1
	 * @param array  $options Options to export
	 * @param array  $metadata Profile metadata
	 * @param string $filename Output filename (without .json extension)
	 * @return string|WP_Error File path or error
	 */
	public function export_to_file( $options = array(), $metadata = array(), $filename = 'slos-config' ) {
		$profile = $this->export_config( $options, $metadata );

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		// Create uploads directory if needed..
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports';

		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );
		}

		// Generate filename..
		$filename  = sanitize_file_name( $filename );
		$timestamp = gmdate( 'Y-m-d-His' );
		$filepath  = $export_dir . '/' . $filename . '-' . $timestamp . '.json';

		// Write JSON file..
		$json = wp_json_encode( $profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		if ( false === $json ) {
			return new WP_Error( 'json_encode_failed', __( 'Failed to encode configuration as JSON', 'shahi-legalflowsuite' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$written = file_put_contents( $filepath, $json );

		if ( false === $written ) {
			return new WP_Error( 'file_write_failed', __( 'Failed to write configuration file', 'shahi-legalflowsuite' ) );
		}

		/**
		 * Fires after config file export
		 *
		 * @since 3.1.1
		 * @param string $filepath File path
		 * @param array  $profile Configuration profile
		 */
		do_action( 'slos_config_exported_to_file', $filepath, $profile );

		return $filepath;
	}

	/**
	 * Import configuration from JSON file
	 *
	 * @since 3.1.1
	 * @param string $filepath Path to JSON file
	 * @param array  $options Import options
	 * @return array|WP_Error Import results or error
	 */
	public function import_from_file( $filepath, $options = array() ) {
		// Validate file exists..
		if ( ! file_exists( $filepath ) ) {
			return new WP_Error( 'file_not_found', __( 'Configuration file not found', 'shahi-legalflowsuite' ) );
		}

		// Read file..
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json = file_get_contents( $filepath );

		if ( false === $json ) {
			return new WP_Error( 'file_read_failed', __( 'Failed to read configuration file', 'shahi-legalflowsuite' ) );
		}

		// Parse JSON..
		$profile = json_decode( $json, true );

		if ( null === $profile || JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error( 'json_parse_failed', __( 'Failed to parse configuration file', 'shahi-legalflowsuite' ) );
		}

		return $this->import_config( $profile, $options );
	}

	/**
	 * Get list of available config exports
	 *
	 * @since 3.1.1
	 * @return array List of export files
	 */
	public function get_available_exports() {
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports';

		if ( ! file_exists( $export_dir ) ) {
			return array();
		}

		$files = glob( $export_dir . '/*.json' );

		if ( false === $files ) {
			return array();
		}

		$exports = array();

		foreach ( $files as $filepath ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$json = file_get_contents( $filepath );

			if ( false === $json ) {
				continue;
			}

			$profile = json_decode( $json, true );

			if ( null === $profile ) {
				continue;
			}

			$exports[] = array(
				'file'           => basename( $filepath ),
				'path'           => $filepath,
				'name'           => $profile['profile']['name'] ?? basename( $filepath, '.json' ),
				'description'    => $profile['profile']['description'] ?? '',
				'exported_at'    => $profile['profile']['exported_at'] ?? '',
				'exported_by'    => $profile['profile']['exported_by'] ?? '',
				'site_url'       => $profile['profile']['site_url'] ?? '',
				'plugin_version' => $profile['profile']['plugin_version'] ?? '',
				'size'           => filesize( $filepath ),
				'modified'       => filemtime( $filepath ),
			);
		}

		// Sort by modified time (newest first)..
		usort(
			$exports,
			function ( $a, $b ) {
				return $b['modified'] - $a['modified'];
			}
		);

		return $exports;
	}

	/**
	 * Delete export file
	 *
	 * @since 3.1.1
	 * @param string $filename Filename to delete
	 * @return bool|WP_Error True on success, error on failure
	 */
	public function delete_export( $filename ) {
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports';
		$filepath   = $export_dir . '/' . basename( $filename );

		if ( ! file_exists( $filepath ) ) {
			return new WP_Error( 'file_not_found', __( 'Export file not found', 'shahi-legalflowsuite' ) );
		}

		// Security check: ensure it's in the exports directory..
		if ( 0 !== strpos( realpath( $filepath ), realpath( $export_dir ) ) ) {
			return new WP_Error( 'invalid_path', __( 'Invalid file path', 'shahi-legalflowsuite' ) );
		}

		$deleted = wp_delete_file( $filepath );

		if ( ! $deleted ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete export file', 'shahi-legalflowsuite' ) );
		}

		/**
		 * Fires after export file deletion
		 *
		 * @since 3.1.1
		 * @param string $filepath Deleted file path
		 */
		do_action( 'slos_config_export_deleted', $filepath );

		return true;
	}

	/**
	 * Compare two configurations
	 *
	 * @since 3.1.1
	 * @param array $profile1 First configuration profile
	 * @param array $profile2 Second configuration profile
	 * @return array Comparison results
	 */
	public function compare_configs( $profile1, $profile2 ) {
		$diff = array(
			'added'     => array(),
			'removed'   => array(),
			'changed'   => array(),
			'unchanged' => array(),
		);

		$keys1 = array_keys( $profile1['settings'] ?? array() );
		$keys2 = array_keys( $profile2['settings'] ?? array() );

		// Find added keys..
		$diff['added'] = array_diff( $keys2, $keys1 );

		// Find removed keys..
		$diff['removed'] = array_diff( $keys1, $keys2 );

		// Find changed/unchanged..
		$common_keys = array_intersect( $keys1, $keys2 );

		foreach ( $common_keys as $key ) {
			$value1 = $profile1['settings'][ $key ];
			$value2 = $profile2['settings'][ $key ];

			if ( $value1 === $value2 ) {
				$diff['unchanged'][] = $key;
			} else {
				$diff['changed'][ $key ] = array(
					'old' => $value1,
					'new' => $value2,
				);
			}
		}

		return $diff;
	}
}
