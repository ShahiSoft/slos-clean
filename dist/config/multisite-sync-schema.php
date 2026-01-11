<?php
/**
 * Multi-Site Config Sync Schema
 *
 * Defines the structure and validation rules for compliance configuration
 * export/import across WordPress sites.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Config
 * @since      3.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get config sync schema definition
 *
 * @since 3.1.1
 * @return array Schema definition with validation rules
 */
function slos_get_config_sync_schema() {
	return array(
		'schema_version'   => '1.0.0',
		'profile'          => array(
			'name'           => array(
				'type'        => 'string',
				'required'    => true,
				'max_length'  => 255,
				'description' => 'Profile name for this configuration bundle',
			),
			'description'    => array(
				'type'        => 'string',
				'required'    => false,
				'max_length'  => 1000,
				'description' => 'Optional description of the configuration',
			),
			'exported_at'    => array(
				'type'        => 'datetime',
				'required'    => true,
				'description' => 'Export timestamp',
			),
			'exported_by'    => array(
				'type'        => 'string',
				'required'    => true,
				'description' => 'User who exported the configuration',
			),
			'site_url'       => array(
				'type'        => 'string',
				'required'    => true,
				'description' => 'Source site URL',
			),
			'plugin_version' => array(
				'type'        => 'string',
				'required'    => true,
				'description' => 'Plugin version at export time',
			),
		),
		'settings'         => array(
			'banner_settings'        => array(
				'option_key'  => 'slos_banner_settings',
				'type'        => 'object',
				'required'    => true,
				'fields'      => array(
					'enabled',
					'position',
					'theme',
					'show_logo',
					'logo_url',
					'headline',
					'message',
					'accept_label',
					'reject_label',
					'settings_label',
					'show_reject_button',
					'show_settings_button',
					'show_descriptions',
					'category_descriptions',
					'show_vendors',
					'vendors',
				),
				'description' => 'Consent banner configuration',
			),
			'geo_rules'              => array(
				'option_key'  => 'slos_geo_rules',
				'type'        => 'array',
				'required'    => false,
				'description' => 'Geo-targeting rules for regional compliance',
			),
			'cookie_inventory'       => array(
				'option_key'  => 'slos_cookie_inventory',
				'type'        => 'array',
				'required'    => false,
				'description' => 'Detected and categorized cookies (read-only, not typically synced)',
			),
			'legal_pages'            => array(
				'option_key'  => 'slos_legal_pages',
				'type'        => 'object',
				'required'    => false,
				'description' => 'Legal page assignments (privacy-policy, cookie-policy, etc.)',
			),
			'accessibility_settings' => array(
				'option_key'  => 'slos_accessibility_settings',
				'type'        => 'object',
				'required'    => false,
				'description' => 'Accessibility scanner configuration',
			),
			'dsr_settings'           => array(
				'option_key'  => 'slos_dsr_settings',
				'type'        => 'object',
				'required'    => false,
				'description' => 'Data Subject Request module settings',
			),
		),
		'modules'          => array(
			'consent_management' => array(
				'enabled' => true,
				'options' => array( 'slos_banner_settings' ),
			),
			'cookie_scanner'     => array(
				'enabled' => true,
				'options' => array( 'slos_cookie_inventory', 'slos_cookie_scan_meta' ),
			),
			'geo_rules'          => array(
				'enabled' => true,
				'options' => array( 'slos_geo_rules' ),
			),
			'legal_docs'         => array(
				'enabled' => true,
				'options' => array( 'slos_legal_pages' ),
			),
			'accessibility'      => array(
				'enabled' => true,
				'options' => array( 'slos_accessibility_settings' ),
			),
			'dsr'                => array(
				'enabled' => true,
				'options' => array( 'slos_dsr_settings' ),
			),
		),
		'exclusions'       => array(
			'never_sync'  => array(
				'slos_consent',              // Consent records (PII)
				'slos_consent_logs',         // Consent audit logs (PII)
				'slos_dsr_requests',         // DSR requests (PII)
				'slos_dsr_audit_logs',       // DSR audit logs (PII)
				'slos_cookie_scan_time',     // Site-specific scan timestamp
				'slos_cookie_scan_meta',     // Site-specific scan metadata
				'slos_consent_ux_scan_results', // Site-specific scan results
			),
			'description' => 'Options that should never be synced due to PII or site-specific nature',
		),
		'validation_rules' => array(
			'banner_settings' => array(
				'enabled'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'position' => array(
					'type'    => 'enum',
					'values'  => array( 'top', 'bottom', 'center', 'popup' ),
					'default' => 'bottom',
				),
				'theme'    => array(
					'type'    => 'enum',
					'values'  => array( 'light', 'dark', 'auto' ),
					'default' => 'light',
				),
			),
			'geo_rules'       => array(
				'priority'      => array(
					'type' => 'integer',
					'min'  => 1,
					'max'  => 100,
				),
				'countries'     => array(
					'type' => 'array',
				),
				'consent_model' => array(
					'type'   => 'enum',
					'values' => array( 'opt-in', 'opt-out', 'notice' ),
				),
			),
		),
		'compatibility'    => array(
			'min_plugin_version' => '3.0.0',
			'max_plugin_version' => null, // null = any version
			'min_wp_version'     => '5.8.0',
			'multisite_only'     => false, // Can be used on single sites too
		),
	);
}

/**
 * Get default exportable options
 *
 * Returns a list of option keys that should be included by default
 * in a configuration export.
 *
 * @since 3.1.1
 * @return array Default option keys to export
 */
function slos_get_default_export_options() {
	return array(
		'slos_banner_settings',
		'slos_geo_rules',
		'slos_legal_pages',
		'slos_accessibility_settings',
		'slos_dsr_settings',
	);
}

/**
 * Get safe import options
 *
 * Returns a list of option keys that are safe to import without
 * overwriting critical site-specific data.
 *
 * @since 3.1.1
 * @return array Safe option keys for import
 */
function slos_get_safe_import_options() {
	return array(
		'slos_banner_settings',
		'slos_geo_rules',
		'slos_legal_pages',
		'slos_accessibility_settings',
		'slos_dsr_settings',
	);
}

/**
 * Validate config profile
 *
 * Validates a configuration profile against the schema.
 *
 * @since 3.1.1
 * @param array $profile Configuration profile to validate
 * @return array|WP_Error Array of validation results or WP_Error on failure
 */
function slos_validate_config_profile( $profile ) {
	$schema = slos_get_config_sync_schema();
	$errors = array();

	// Check schema version
	if ( empty( $profile['schema_version'] ) ) {
		$errors[] = __( 'Missing schema version', 'shahi-legalflowsuite' );
	} elseif ( version_compare( $profile['schema_version'], $schema['schema_version'], '>' ) ) {
		$errors[] = sprintf(
			/* translators: 1: imported version, 2: current version */
			__( 'Schema version mismatch: imported %1$s, current %2$s', 'shahi-legalflowsuite' ),
			$profile['schema_version'],
			$schema['schema_version']
		);
	}

	// Check required profile fields
	foreach ( $schema['profile'] as $field => $rules ) {
		if ( ! empty( $rules['required'] ) && empty( $profile['profile'][ $field ] ) ) {
			$errors[] = sprintf(
				/* translators: %s: field name */
				__( 'Missing required profile field: %s', 'shahi-legalflowsuite' ),
				$field
			);
		}
	}

	// Check plugin compatibility
	if ( ! empty( $profile['profile']['plugin_version'] ) ) {
		$min_version = $schema['compatibility']['min_plugin_version'];
		$max_version = $schema['compatibility']['max_plugin_version'];

		if ( version_compare( $profile['profile']['plugin_version'], $min_version, '<' ) ) {
			$errors[] = sprintf(
				/* translators: 1: imported version, 2: minimum version */
				__( 'Plugin version too old: %1$s (minimum: %2$s)', 'shahi-legalflowsuite' ),
				$profile['profile']['plugin_version'],
				$min_version
			);
		}

		if ( $max_version && version_compare( $profile['profile']['plugin_version'], $max_version, '>' ) ) {
			$errors[] = sprintf(
				/* translators: 1: imported version, 2: maximum version */
				__( 'Plugin version too new: %1$s (maximum: %2$s)', 'shahi-legalflowsuite' ),
				$profile['profile']['plugin_version'],
				$max_version
			);
		}
	}

	// Check for excluded options
	if ( ! empty( $profile['settings'] ) ) {
		$excluded = $schema['exclusions']['never_sync'];
		foreach ( array_keys( $profile['settings'] ) as $option_key ) {
			if ( in_array( $option_key, $excluded, true ) ) {
				$errors[] = sprintf(
					/* translators: %s: option key */
					__( 'Excluded option detected: %s should not be synced', 'shahi-legalflowsuite' ),
					$option_key
				);
			}
		}
	}

	if ( ! empty( $errors ) ) {
		return new WP_Error( 'config_validation_failed', __( 'Configuration validation failed', 'shahi-legalflowsuite' ), $errors );
	}

	return array(
		'valid'    => true,
		'warnings' => array(),
		'profile'  => $profile,
	);
}

/**
 * Sanitize config profile
 *
 * Sanitizes a configuration profile before import.
 *
 * @since 3.1.1
 * @param array $profile Configuration profile to sanitize
 * @return array Sanitized profile
 */
function slos_sanitize_config_profile( $profile ) {
	$sanitized = array();

	// Sanitize profile metadata
	if ( ! empty( $profile['profile'] ) ) {
		$sanitized['profile'] = array(
			'name'           => sanitize_text_field( $profile['profile']['name'] ?? '' ),
			'description'    => sanitize_textarea_field( $profile['profile']['description'] ?? '' ),
			'exported_at'    => sanitize_text_field( $profile['profile']['exported_at'] ?? '' ),
			'exported_by'    => sanitize_text_field( $profile['profile']['exported_by'] ?? '' ),
			'site_url'       => esc_url_raw( $profile['profile']['site_url'] ?? '' ),
			'plugin_version' => sanitize_text_field( $profile['profile']['plugin_version'] ?? '' ),
		);
	}

	// Keep schema version
	$sanitized['schema_version'] = sanitize_text_field( $profile['schema_version'] ?? '1.0.0' );

	// Settings are sanitized by individual import handlers
	if ( ! empty( $profile['settings'] ) && is_array( $profile['settings'] ) ) {
		$sanitized['settings'] = $profile['settings'];
	}

	return $sanitized;
}

/**
 * Get config profile template
 *
 * Returns an empty template for creating a new configuration profile.
 *
 * @since 3.1.1
 * @return array Template array
 */
function slos_get_config_profile_template() {
	$current_user = wp_get_current_user();

	return array(
		'schema_version' => '1.0.0',
		'profile'        => array(
			'name'           => '',
			'description'    => '',
			'exported_at'    => current_time( 'mysql' ),
			'exported_by'    => $current_user->display_name,
			'site_url'       => get_site_url(),
			'plugin_version' => defined( 'SHAHI_LEGALFLOWSUITE_VERSION' ) ? SHAHI_LEGALFLOWSUITE_VERSION : '3.1.1',
		),
		'settings'       => array(),
		'modules'        => array(),
	);
}
