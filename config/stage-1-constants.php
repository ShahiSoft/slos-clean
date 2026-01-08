<?php
/**
 * Stage 1 - Foundation Hub MVP Constants
 *
 * Configuration constants and feature flags for Stage 1 Document Generator.
 * These constants control feature availability and system behavior.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Config
 * @version     4.1.0
 * @since       4.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Stage 1 Version
|--------------------------------------------------------------------------
*/

if ( ! defined( 'SLOS_STAGE1_VERSION' ) ) {
	define( 'SLOS_STAGE1_VERSION', '1.0.0' );
}

/*
|--------------------------------------------------------------------------
| Feature Flags - Stage 1 MVP
|--------------------------------------------------------------------------
|
| Core Stage 1 features enabled by default.
| These can be overridden in wp-config.php or via filter.
|
*/

// Company Profile 8-Step Wizard
if ( ! defined( 'SLOS_FEATURE_COMPANY_WIZARD' ) ) {
	define( 'SLOS_FEATURE_COMPANY_WIZARD', true );
}

// Basic Document Generation (Privacy, ToS, Cookie)
if ( ! defined( 'SLOS_FEATURE_DOC_GENERATOR' ) ) {
	define( 'SLOS_FEATURE_DOC_GENERATOR', true );
}

// Draft/Publish Workflow
if ( ! defined( 'SLOS_FEATURE_DRAFT_WORKFLOW' ) ) {
	define( 'SLOS_FEATURE_DRAFT_WORKFLOW', true );
}

// Version History (basic, up to 5 versions)
if ( ! defined( 'SLOS_FEATURE_VERSION_HISTORY' ) ) {
	define( 'SLOS_FEATURE_VERSION_HISTORY', true );
}

// Profile Validation with Warnings
if ( ! defined( 'SLOS_FEATURE_PROFILE_VALIDATION' ) ) {
	define( 'SLOS_FEATURE_PROFILE_VALIDATION', true );
}

/*
|--------------------------------------------------------------------------
| Dormant Modules Configuration
|--------------------------------------------------------------------------
|
| Modules temporarily disabled but data preserved for future reactivation.
| To reactivate: remove module key from this array.
|
*/

if ( ! defined( 'SLOS_DORMANT_MODULES' ) ) {
	define( 'SLOS_DORMANT_MODULES', array(
		'dsr-portal',      // DSR Portal Module - temporarily dormant
		'security',        // Enhanced Security Module - temporarily dormant
	) );
}

/*
|--------------------------------------------------------------------------
| Feature Flags - Stage 2 (Disabled in Stage 1)
|--------------------------------------------------------------------------
*/

// AI-Enhanced Content (Stage 2)
if ( ! defined( 'SLOS_FEATURE_AI_ENHANCE' ) ) {
	define( 'SLOS_FEATURE_AI_ENHANCE', false );
}

// Multiple Templates/Themes (Stage 2)
if ( ! defined( 'SLOS_FEATURE_DOC_THEMES' ) ) {
	define( 'SLOS_FEATURE_DOC_THEMES', false );
}

// Custom Clauses (Stage 2)
if ( ! defined( 'SLOS_FEATURE_CUSTOM_CLAUSES' ) ) {
	define( 'SLOS_FEATURE_CUSTOM_CLAUSES', false );
}

// Advanced Editor (Stage 2)
if ( ! defined( 'SLOS_FEATURE_ADVANCED_EDITOR' ) ) {
	define( 'SLOS_FEATURE_ADVANCED_EDITOR', false );
}

// Regulation Auto-Detect (Stage 2)
if ( ! defined( 'SLOS_FEATURE_REGULATION_DETECT' ) ) {
	define( 'SLOS_FEATURE_REGULATION_DETECT', false );
}

/*
|--------------------------------------------------------------------------
| Feature Flags - Stage 3 (Disabled in Stage 1)
|--------------------------------------------------------------------------
*/

// PDF Export (Stage 3)
if ( ! defined( 'SLOS_FEATURE_PDF_EXPORT' ) ) {
	define( 'SLOS_FEATURE_PDF_EXPORT', false );
}

// Multi-language Support (Stage 3)
if ( ! defined( 'SLOS_FEATURE_MULTILINGUAL' ) ) {
	define( 'SLOS_FEATURE_MULTILINGUAL', false );
}

// Legal Updates API (Stage 3)
if ( ! defined( 'SLOS_FEATURE_LEGAL_UPDATES' ) ) {
	define( 'SLOS_FEATURE_LEGAL_UPDATES', false );
}

// Compliance Dashboard (Stage 3)
if ( ! defined( 'SLOS_FEATURE_COMPLIANCE_DASH' ) ) {
	define( 'SLOS_FEATURE_COMPLIANCE_DASH', false );
}

/*
|--------------------------------------------------------------------------
| Document Generation Limits
|--------------------------------------------------------------------------
*/

// Maximum document versions to keep
if ( ! defined( 'SLOS_MAX_DOC_VERSIONS' ) ) {
	define( 'SLOS_MAX_DOC_VERSIONS', 5 );
}

// Auto-prune old versions (days)
if ( ! defined( 'SLOS_VERSION_PRUNE_DAYS' ) ) {
	define( 'SLOS_VERSION_PRUNE_DAYS', 90 );
}

// Document draft expiry (days, 0 = never expire)
if ( ! defined( 'SLOS_DRAFT_EXPIRY_DAYS' ) ) {
	define( 'SLOS_DRAFT_EXPIRY_DAYS', 0 );
}

/*
|--------------------------------------------------------------------------
| Profile Validation
|--------------------------------------------------------------------------
*/

// Minimum completion percentage to generate
if ( ! defined( 'SLOS_MIN_PROFILE_COMPLETION' ) ) {
	define( 'SLOS_MIN_PROFILE_COMPLETION', 70 );
}

// Show warnings for non-critical missing fields
if ( ! defined( 'SLOS_SHOW_PROFILE_WARNINGS' ) ) {
	define( 'SLOS_SHOW_PROFILE_WARNINGS', true );
}

/*
|--------------------------------------------------------------------------
| Placeholder Handling
|--------------------------------------------------------------------------
*/

// How to handle missing placeholders: 'marker', 'blank', 'default'
if ( ! defined( 'SLOS_MISSING_PLACEHOLDER_BEHAVIOR' ) ) {
	define( 'SLOS_MISSING_PLACEHOLDER_BEHAVIOR', 'marker' );
}

// Default value for missing placeholders when behavior is 'default'
if ( ! defined( 'SLOS_PLACEHOLDER_DEFAULT' ) ) {
	define( 'SLOS_PLACEHOLDER_DEFAULT', '[Not Specified]' );
}

/*
|--------------------------------------------------------------------------
| Template Settings
|--------------------------------------------------------------------------
*/

// Default template version
if ( ! defined( 'SLOS_TEMPLATE_VERSION' ) ) {
	define( 'SLOS_TEMPLATE_VERSION', '1.0' );
}

// Allow custom templates from theme
if ( ! defined( 'SLOS_ALLOW_THEME_TEMPLATES' ) ) {
	define( 'SLOS_ALLOW_THEME_TEMPLATES', true );
}

// Theme template path (relative to theme root)
if ( ! defined( 'SLOS_THEME_TEMPLATE_PATH' ) ) {
	define( 'SLOS_THEME_TEMPLATE_PATH', 'shahi-legalflowsuite/legaldocs' );
}

/*
|--------------------------------------------------------------------------
| Document Types - Stage 1
|--------------------------------------------------------------------------
*/

if ( ! defined( 'SLOS_DOC_TYPES_ENABLED' ) ) {
	define(
		'SLOS_DOC_TYPES_ENABLED',
		array(
			'privacy-policy',
			'terms-of-service',
			'cookie-policy',
		)
	);
}

/*
|--------------------------------------------------------------------------
| Cache Settings
|--------------------------------------------------------------------------
*/

// Cache generated documents
if ( ! defined( 'SLOS_CACHE_DOCUMENTS' ) ) {
	define( 'SLOS_CACHE_DOCUMENTS', false );
}

// Document cache TTL (seconds)
if ( ! defined( 'SLOS_DOC_CACHE_TTL' ) ) {
	define( 'SLOS_DOC_CACHE_TTL', 3600 );
}

/*
|--------------------------------------------------------------------------
| Debug Settings
|--------------------------------------------------------------------------
*/

// Enable detailed logging for document generation
if ( ! defined( 'SLOS_DEBUG_GENERATION' ) ) {
	define( 'SLOS_DEBUG_GENERATION', false );
}

// Log unresolved placeholders
if ( ! defined( 'SLOS_LOG_MISSING_PLACEHOLDERS' ) ) {
	define( 'SLOS_LOG_MISSING_PLACEHOLDERS', WP_DEBUG );
}

/*
|--------------------------------------------------------------------------
| AJAX & REST API
|--------------------------------------------------------------------------
*/

// Enable REST API endpoints for document generation
if ( ! defined( 'SLOS_REST_ENABLED' ) ) {
	define( 'SLOS_REST_ENABLED', true );
}

// REST API namespace
if ( ! defined( 'SLOS_REST_NAMESPACE' ) ) {
	define( 'SLOS_REST_NAMESPACE', 'slos/v1' );
}

// Rate limit (requests per minute per user)
if ( ! defined( 'SLOS_RATE_LIMIT' ) ) {
	define( 'SLOS_RATE_LIMIT', 30 );
}

/*
|--------------------------------------------------------------------------
| Security
|--------------------------------------------------------------------------
*/

// Capability required to generate documents
if ( ! defined( 'SLOS_GENERATE_CAPABILITY' ) ) {
	define( 'SLOS_GENERATE_CAPABILITY', 'manage_options' );
}

// Capability required to publish documents
if ( ! defined( 'SLOS_PUBLISH_CAPABILITY' ) ) {
	define( 'SLOS_PUBLISH_CAPABILITY', 'manage_options' );
}

// Enable nonce verification for all AJAX actions
if ( ! defined( 'SLOS_VERIFY_NONCES' ) ) {
	define( 'SLOS_VERIFY_NONCES', true );
}

/*
|--------------------------------------------------------------------------
| Helper Function - Check Feature Enabled
|--------------------------------------------------------------------------
*/

if ( ! function_exists( 'slos_is_feature_enabled' ) ) {
	/**
	 * Check if a feature is enabled
	 *
	 * @since 4.1.0
	 * @param string $feature Feature name (without SLOS_FEATURE_ prefix).
	 * @return bool True if enabled
	 */
	function slos_is_feature_enabled( string $feature ): bool {
		$constant = 'SLOS_FEATURE_' . strtoupper( $feature );

		if ( defined( $constant ) ) {
			return (bool) constant( $constant );
		}

		/**
		 * Filter feature enabled status
		 *
		 * @since 4.1.0
		 * @param bool   $enabled Default enabled status.
		 * @param string $feature Feature name.
		 */
		return apply_filters( 'slos_feature_enabled', false, $feature );
	}
}

if ( ! function_exists( 'slos_get_config' ) ) {
	/**
	 * Get a configuration value
	 *
	 * @since 4.1.0
	 * @param string $key     Config key (without SLOS_ prefix).
	 * @param mixed  $default Default value if not defined.
	 * @return mixed Config value
	 */
	function slos_get_config( string $key, $default = null ) {
		$constant = 'SLOS_' . strtoupper( $key );

		if ( defined( $constant ) ) {
			return constant( $constant );
		}

		/**
		 * Filter configuration value
		 *
		 * @since 4.1.0
		 * @param mixed  $default Default value.
		 * @param string $key     Config key.
		 */
		return apply_filters( 'slos_config_' . strtolower( $key ), $default, $key );
	}
}
