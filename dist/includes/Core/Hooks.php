<?php
/**
 * WordPress Hooks Documentation
 *
 * Complete reference for all action and filter hooks available in the
 * Shahi LegalFlowSuite plugin for consent management and compliance.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Core
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Hooks
 *
 * Central registry and documentation for all WordPress hooks.
 *
 * @since 3.0.1
 */
class Hooks {

	/**
	 * Initialize hooks
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public static function init() {
		// Hook documentation is provided via docblocks
		// Actual hooks are fired from Service and Controller classes
	}

	/**
	 * Get all available action hooks
	 *
	 * @since 3.0.1
	 * @return array List of action hooks with descriptions
	 */
	public static function get_action_hooks(): array {
		return array(
			// Consent Management Hooks
			'slos_consent_recorded'       => array(
				'description' => 'Fires after a consent record is successfully created',
				'params'      => array(
					'$consent_id'   => '(int) The consent record ID',
					'$consent_data' => '(array) The consent data that was saved',
				),
				'example'     => "add_action('slos_consent_recorded', function(\$consent_id, \$consent_data) {\n    error_log('Consent ' . \$consent_id . ' recorded for user ' . \$consent_data['user_id']);\n}, 10, 2);",
			),
			'slos_consent_updated'        => array(
				'description' => 'Fires after a consent record is updated',
				'params'      => array(
					'$consent_id'  => '(int) The consent record ID',
					'$update_data' => '(array) The data that was updated',
				),
				'example'     => "add_action('slos_consent_updated', function(\$consent_id, \$update_data) {\n    // Custom logic after consent update\n}, 10, 2);",
			),
			'slos_consent_withdrawn'      => array(
				'description' => 'Fires after a consent is withdrawn by a user',
				'params'      => array(
					'$consent_id' => '(int) The consent record ID that was withdrawn',
				),
				'example'     => "add_action('slos_consent_withdrawn', function(\$consent_id) {\n    // Notify admin of consent withdrawal\n}, 10, 1);",
			),
			'slos_consent_deleted'        => array(
				'description' => 'Fires after a consent record is deleted (admin only)',
				'params'      => array(
					'$consent_id' => '(int) The consent record ID that was deleted',
				),
				'example'     => "add_action('slos_consent_deleted', function(\$consent_id) {\n    // Log deletion for audit trail\n}, 10, 1);",
			),
			'slos_bulk_consent_withdrawn' => array(
				'description' => 'Fires after bulk consent withdrawal operation',
				'params'      => array(
					'$user_id'         => '(int) The user ID whose consents were withdrawn',
					'$withdrawn_count' => '(int) Number of consents withdrawn',
					'$type'            => '(string) Consent type filter (empty for all types)',
				),
				'example'     => "add_action('slos_bulk_consent_withdrawn', function(\$user_id, \$withdrawn_count, \$type) {\n    error_log('Bulk withdrawal: ' . \$withdrawn_count . ' consents for user ' . \$user_id);\n}, 10, 3);",
			),

			// Plugin Lifecycle Hooks
			'slos_plugin_activated'       => array(
				'description' => 'Fires when the plugin is activated',
				'params'      => array(),
				'example'     => "add_action('slos_plugin_activated', function() {\n    // Custom activation logic\n});",
			),
			'slos_plugin_deactivated'     => array(
				'description' => 'Fires when the plugin is deactivated',
				'params'      => array(),
				'example'     => "add_action('slos_plugin_deactivated', function() {\n    // Custom deactivation logic\n});",
			),
			'slos_migrations_completed'   => array(
				'description' => 'Fires after all database migrations are completed',
				'params'      => array(
					'$migrated_tables' => '(array) List of tables that were migrated',
				),
				'example'     => "add_action('slos_migrations_completed', function(\$migrated_tables) {\n    // Post-migration setup\n}, 10, 1);",
			),

			// API Hooks
			'slos_rest_api_init'          => array(
				'description' => 'Fires when REST API routes are being registered',
				'params'      => array(),
				'example'     => "add_action('slos_rest_api_init', function() {\n    // Register custom REST routes\n});",
			),
			'slos_rest_authentication'    => array(
				'description' => 'Fires during REST API authentication checks',
				'params'      => array(
					'$user_id' => '(int) The authenticated user ID (0 if not authenticated)',
				),
				'example'     => "add_action('slos_rest_authentication', function(\$user_id) {\n    // Custom authentication logging\n}, 10, 1);",
			),
		);
	}

	/**
	 * Get all available filter hooks
	 *
	 * @since 3.0.1
	 * @return array List of filter hooks with descriptions
	 */
	public static function get_filter_hooks(): array {
		return array(
			// Consent Data Filters
			'slos_consent_data_before_save'  => array(
				'description' => 'Filter consent data before saving to database',
				'params'      => array(
					'$consent_data' => '(array) The consent data to be saved',
					'$context'      => '(string) Context: "create" or "update"',
				),
				'return'      => '(array) Modified consent data',
				'example'     => "add_filter('slos_consent_data_before_save', function(\$consent_data, \$context) {\n    // Modify consent data before save\n    \$consent_data['custom_field'] = 'custom_value';\n    return \$consent_data;\n}, 10, 2);",
			),
			'slos_consent_metadata'          => array(
				'description' => 'Filter consent metadata before saving',
				'params'      => array(
					'$metadata'     => '(array) Metadata array',
					'$consent_type' => '(string) Type of consent',
				),
				'return'      => '(array) Modified metadata',
				'example'     => "add_filter('slos_consent_metadata', function(\$metadata, \$consent_type) {\n    \$metadata['additional_info'] = 'value';\n    return \$metadata;\n}, 10, 2);",
			),
			'slos_allowed_consent_types'     => array(
				'description' => 'Filter the allowed consent types',
				'params'      => array(
					'$types' => '(array) Default allowed consent types',
				),
				'return'      => '(array) Modified list of consent types',
				'example'     => "add_filter('slos_allowed_consent_types', function(\$types) {\n    \$types[] = 'custom_type';\n    return \$types;\n});",
			),
			'slos_allowed_consent_statuses'  => array(
				'description' => 'Filter the allowed consent statuses',
				'params'      => array(
					'$statuses' => '(array) Default allowed consent statuses',
				),
				'return'      => '(array) Modified list of consent statuses',
				'example'     => "add_filter('slos_allowed_consent_statuses', function(\$statuses) {\n    \$statuses[] = 'pending';\n    return \$statuses;\n});",
			),

			// Validation Filters
			'slos_validate_consent_data'     => array(
				'description' => 'Filter validation result for consent data',
				'params'      => array(
					'$is_valid'     => '(bool) Whether data is valid',
					'$consent_data' => '(array) The consent data being validated',
					'$errors'       => '(array) Validation errors',
				),
				'return'      => '(bool) Modified validation result',
				'example'     => "add_filter('slos_validate_consent_data', function(\$is_valid, \$consent_data, \$errors) {\n    // Custom validation logic\n    return \$is_valid;\n}, 10, 3);",
			),

			// Query Filters
			'slos_consent_query_args'        => array(
				'description' => 'Filter query arguments for consent retrieval',
				'params'      => array(
					'$args'    => '(array) Query arguments',
					'$context' => '(string) Query context',
				),
				'return'      => '(array) Modified query arguments',
				'example'     => "add_filter('slos_consent_query_args', function(\$args, \$context) {\n    \$args['limit'] = 50;\n    return \$args;\n}, 10, 2);",
			),
			'slos_consent_statistics'        => array(
				'description' => 'Filter consent statistics before returning',
				'params'      => array(
					'$stats' => '(array) Statistics data',
				),
				'return'      => '(array) Modified statistics',
				'example'     => "add_filter('slos_consent_statistics', function(\$stats) {\n    // Add custom statistics\n    return \$stats;\n});",
			),

			// REST API Filters
			'slos_rest_consent_response'     => array(
				'description' => 'Filter consent data before REST API response',
				'params'      => array(
					'$consent_data' => '(array) Prepared consent data',
					'$consent'      => '(object) Raw consent object',
				),
				'return'      => '(array) Modified consent data for response',
				'example'     => "add_filter('slos_rest_consent_response', function(\$consent_data, \$consent) {\n    unset(\$consent_data['ip_hash']); // Remove sensitive data\n    return \$consent_data;\n}, 10, 2);",
			),
			'slos_rest_error_response'       => array(
				'description' => 'Filter REST API error response',
				'params'      => array(
					'$error_data' => '(array) Error response data',
					'$error_code' => '(string) Error code',
				),
				'return'      => '(array) Modified error response',
				'example'     => "add_filter('slos_rest_error_response', function(\$error_data, \$error_code) {\n    // Customize error messages\n    return \$error_data;\n}, 10, 2);",
			),

			// Privacy & Security Filters
			'slos_ip_hash_algorithm'         => array(
				'description' => 'Filter the hashing algorithm for IP addresses',
				'params'      => array(
					'$algorithm' => '(string) Hash algorithm (default: sha256)',
				),
				'return'      => '(string) Modified hash algorithm',
				'example'     => "add_filter('slos_ip_hash_algorithm', function(\$algorithm) {\n    return 'sha512'; // Use stronger hashing\n});",
			),
			'slos_anonymize_consent_data'    => array(
				'description' => 'Filter consent data anonymization',
				'params'      => array(
					'$anonymized_data' => '(array) Anonymized consent data',
					'$original_data'   => '(array) Original consent data',
				),
				'return'      => '(array) Modified anonymized data',
				'example'     => "add_filter('slos_anonymize_consent_data', function(\$anonymized_data, \$original_data) {\n    // Custom anonymization logic\n    return \$anonymized_data;\n}, 10, 2);",
			),

			// Permission Filters
			'slos_user_can_withdraw_consent' => array(
				'description' => 'Filter whether a user can withdraw consent',
				'params'      => array(
					'$can_withdraw' => '(bool) Whether user can withdraw',
					'$consent_id'   => '(int) Consent ID',
					'$user_id'      => '(int) User ID',
				),
				'return'      => '(bool) Modified permission',
				'example'     => "add_filter('slos_user_can_withdraw_consent', function(\$can_withdraw, \$consent_id, \$user_id) {\n    // Custom permission logic\n    return \$can_withdraw;\n}, 10, 3);",
			),
			'slos_user_can_view_consent'     => array(
				'description' => 'Filter whether a user can view consent details',
				'params'      => array(
					'$can_view'   => '(bool) Whether user can view',
					'$consent_id' => '(int) Consent ID',
					'$user_id'    => '(int) User ID',
				),
				'return'      => '(bool) Modified permission',
				'example'     => "add_filter('slos_user_can_view_consent', function(\$can_view, \$consent_id, \$user_id) {\n    // Custom permission logic\n    return \$can_view;\n}, 10, 3);",
			),
		);
	}

	/**
	 * Get hooks by category
	 *
	 * @since 3.0.1
	 * @param string $category Hook category
	 * @return array Hooks in the specified category
	 */
	public static function get_hooks_by_category( string $category ): array {
		$actions = self::get_action_hooks();
		$filters = self::get_filter_hooks();

		$categorized = array();

		switch ( $category ) {
			case 'consent':
				$categorized['actions'] = array_filter(
					$actions,
					function ( $key ) {
						return strpos( $key, 'consent' ) !== false;
					},
					ARRAY_FILTER_USE_KEY
				);
				$categorized['filters'] = array_filter(
					$filters,
					function ( $key ) {
						return strpos( $key, 'consent' ) !== false;
					},
					ARRAY_FILTER_USE_KEY
				);
				break;

			case 'api':
				$categorized['actions'] = array_filter(
					$actions,
					function ( $key ) {
						return strpos( $key, 'rest' ) !== false || strpos( $key, 'api' ) !== false;
					},
					ARRAY_FILTER_USE_KEY
				);
				$categorized['filters'] = array_filter(
					$filters,
					function ( $key ) {
						return strpos( $key, 'rest' ) !== false;
					},
					ARRAY_FILTER_USE_KEY
				);
				break;

			case 'security':
				$categorized['filters'] = array_filter(
					$filters,
					function ( $key ) {
						return strpos( $key, 'permission' ) !== false || strpos( $key, 'anonymize' ) !== false || strpos( $key, 'hash' ) !== false;
					},
					ARRAY_FILTER_USE_KEY
				);
				break;

			default:
				$categorized['actions'] = $actions;
				$categorized['filters'] = $filters;
				break;
		}

		return $categorized;
	}

	/**
	 * Generate hooks documentation
	 *
	 * @since 3.0.1
	 * @return string Markdown formatted documentation
	 */
	public static function generate_documentation(): string {
		$doc  = "# WordPress Hooks Reference\n\n";
		$doc .= "Complete reference for all action and filter hooks in Shahi LegalFlowSuite.\n\n";
		$doc .= "Version: 3.0.1\n\n";

		// Action Hooks
		$doc .= "## Action Hooks\n\n";
		$doc .= "Action hooks allow you to execute custom code at specific points in the plugin lifecycle.\n\n";

		$actions = self::get_action_hooks();
		foreach ( $actions as $hook_name => $hook_info ) {
			$doc .= "### `{$hook_name}`\n\n";
			$doc .= "{$hook_info['description']}\n\n";

			if ( ! empty( $hook_info['params'] ) ) {
				$doc .= "**Parameters:**\n\n";
				foreach ( $hook_info['params'] as $param => $description ) {
					$doc .= "- `{$param}` - {$description}\n";
				}
				$doc .= "\n";
			}

			$doc .= "**Example:**\n\n";
			$doc .= "```php\n{$hook_info['example']}\n```\n\n";
			$doc .= "---\n\n";
		}

		// Filter Hooks
		$doc .= "## Filter Hooks\n\n";
		$doc .= "Filter hooks allow you to modify data before it's used by the plugin.\n\n";

		$filters = self::get_filter_hooks();
		foreach ( $filters as $hook_name => $hook_info ) {
			$doc .= "### `{$hook_name}`\n\n";
			$doc .= "{$hook_info['description']}\n\n";

			if ( ! empty( $hook_info['params'] ) ) {
				$doc .= "**Parameters:**\n\n";
				foreach ( $hook_info['params'] as $param => $description ) {
					$doc .= "- `{$param}` - {$description}\n";
				}
				$doc .= "\n";
			}

			if ( ! empty( $hook_info['return'] ) ) {
				$doc .= "**Return:** {$hook_info['return']}\n\n";
			}

			$doc .= "**Example:**\n\n";
			$doc .= "```php\n{$hook_info['example']}\n```\n\n";
			$doc .= "---\n\n";
		}

		return $doc;
	}
}
