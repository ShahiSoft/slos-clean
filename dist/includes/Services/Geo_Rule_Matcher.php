<?php
/**
 * Geo Rule Matcher Service
 *
 * Matches visitor's detected country/region to appropriate geo rules.
 * Handles priority-based matching and fallback logic.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.3
 * @since       3.0.3
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Geo_Rule_Matcher
 *
 * Finds matching geo rules based on visitor location.
 *
 * @since 3.0.3
 */
class Geo_Rule_Matcher {

	/**
	 * EU country codes (27 members)
	 *
	 * @var array
	 */
	private static $eu_countries = array(
		'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
		'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
		'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
	);

	/**
	 * EEA country codes (EU + 3)
	 *
	 * @var array
	 */
	private static $eea_countries = array(
		'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
		'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
		'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'IS', 'LI', 'NO',
	);

	/**
	 * Cached geo rules
	 *
	 * @var array|null
	 */
	private $cached_rules = null;

	/**
	 * Find the best matching geo rule for a country code
	 *
	 * Matching priority:
	 * 1. Exact country match (e.g., US-CA for California)
	 * 2. Parent country match (e.g., US matches US-CA visitors)
	 * 3. Region group match (EU-ALL, EEA-ALL)
	 * 4. Default rule (if exists)
	 *
	 * @since 3.0.3
	 * @param string $country_code ISO 2-letter country code (e.g., 'DE').
	 * @param string $state_code   Optional state/region code (e.g., 'CA' for California).
	 * @return array|null Matching geo rule or null if no match.
	 */
	public function find_matching_rule( string $country_code, string $state_code = '' ): ?array {
		$rules = $this->get_active_rules();

		if ( empty( $rules ) ) {
			return null;
		}

		$country_code = strtoupper( trim( $country_code ) );
		$state_code   = strtoupper( trim( $state_code ) );

		// Build the full code for US states (e.g., US-CA).
		$full_code = $country_code;
		if ( 'US' === $country_code && ! empty( $state_code ) ) {
			$full_code = 'US-' . $state_code;
		}

		$exact_match   = null;
		$parent_match  = null;
		$region_match  = null;
		$default_match = null;

		foreach ( $rules as $rule ) {
			if ( empty( $rule['countries'] ) || ! is_array( $rule['countries'] ) ) {
				continue;
			}

			$countries = array_map( 'strtoupper', $rule['countries'] );

			// Priority 1: Exact match (e.g., US-CA matches US-CA).
			if ( in_array( $full_code, $countries, true ) ) {
				$exact_match = $rule;
				break; // Exact match found, stop searching.
			}

			// Priority 2: Parent country match (e.g., US matches when visitor is US-CA).
			if ( null === $parent_match && in_array( $country_code, $countries, true ) ) {
				$parent_match = $rule;
			}

			// Priority 3: Region group match (EU-ALL, EEA-ALL).
			if ( null === $region_match ) {
				if ( in_array( 'EU-ALL', $countries, true ) && $this->is_eu_country( $country_code ) ) {
					$region_match = $rule;
				} elseif ( in_array( 'EEA-ALL', $countries, true ) && $this->is_eea_country( $country_code ) ) {
					$region_match = $rule;
				}
			}

			// Priority 4: Check for default/global rule.
			if ( null === $default_match ) {
				if ( in_array( 'GLOBAL', $countries, true ) || in_array( '*', $countries, true ) ) {
					$default_match = $rule;
				}
			}
		}

		// Return based on priority.
		if ( $exact_match ) {
			return $exact_match;
		}
		if ( $parent_match ) {
			return $parent_match;
		}
		if ( $region_match ) {
			return $region_match;
		}
		if ( $default_match ) {
			return $default_match;
		}

		return null;
	}

	/**
	 * Get all active geo rules sorted by specificity
	 *
	 * @since 3.0.3
	 * @return array Active geo rules.
	 */
	public function get_active_rules(): array {
		if ( null !== $this->cached_rules ) {
			return $this->cached_rules;
		}

		$all_rules = get_option( 'slos_geo_rules', array() );

		if ( empty( $all_rules ) || ! is_array( $all_rules ) ) {
			$this->cached_rules = array();
			return $this->cached_rules;
		}

		// Filter to only active rules.
		$active_rules = array_filter(
			$all_rules,
			function ( $rule ) {
				return ! empty( $rule['active'] ) || ! isset( $rule['active'] );
			}
		);

		// Sort by specificity (rules with fewer, more specific countries first).
		usort(
			$active_rules,
			function ( $a, $b ) {
				$a_count = count( $a['countries'] ?? array() );
				$b_count = count( $b['countries'] ?? array() );

				// Fewer countries = more specific = higher priority.
				return $a_count - $b_count;
			}
		);

		$this->cached_rules = array_values( $active_rules );
		return $this->cached_rules;
	}

	/**
	 * Check if a country matches a specific rule
	 *
	 * @since 3.0.3
	 * @param string $country_code ISO country code.
	 * @param array  $rule         Geo rule to check against.
	 * @return bool True if country matches rule.
	 */
	public function country_matches_rule( string $country_code, array $rule ): bool {
		if ( empty( $rule['countries'] ) || ! is_array( $rule['countries'] ) ) {
			return false;
		}

		$country_code = strtoupper( trim( $country_code ) );
		$countries    = array_map( 'strtoupper', $rule['countries'] );

		// Direct match.
		if ( in_array( $country_code, $countries, true ) ) {
			return true;
		}

		// EU-ALL group match.
		if ( in_array( 'EU-ALL', $countries, true ) && $this->is_eu_country( $country_code ) ) {
			return true;
		}

		// EEA-ALL group match.
		if ( in_array( 'EEA-ALL', $countries, true ) && $this->is_eea_country( $country_code ) ) {
			return true;
		}

		// Global/wildcard match.
		if ( in_array( 'GLOBAL', $countries, true ) || in_array( '*', $countries, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if country is in EU
	 *
	 * @since 3.0.3
	 * @param string $country_code ISO country code.
	 * @return bool
	 */
	public function is_eu_country( string $country_code ): bool {
		return in_array( strtoupper( $country_code ), self::$eu_countries, true );
	}

	/**
	 * Check if country is in EEA
	 *
	 * @since 3.0.3
	 * @param string $country_code ISO country code.
	 * @return bool
	 */
	public function is_eea_country( string $country_code ): bool {
		return in_array( strtoupper( $country_code ), self::$eea_countries, true );
	}

	/**
	 * Get rule by ID
	 *
	 * @since 3.0.3
	 * @param int $rule_id Rule ID.
	 * @return array|null Rule or null if not found.
	 */
	public function get_rule_by_id( int $rule_id ): ?array {
		$all_rules = get_option( 'slos_geo_rules', array() );

		if ( isset( $all_rules[ $rule_id ] ) ) {
			return $all_rules[ $rule_id ];
		}

		// Also check by 'id' field in array values.
		foreach ( $all_rules as $rule ) {
			if ( isset( $rule['id'] ) && (int) $rule['id'] === $rule_id ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * Get banner configuration from matching rule
	 *
	 * @since 3.0.3
	 * @param array|null $rule Matching geo rule.
	 * @return array Banner configuration.
	 */
	public function get_banner_config_from_rule( ?array $rule ): array {
		$defaults = array(
			'show_banner'      => true,
			'consent_mode'     => 'opt-in',
			'show_reject'      => true,
			'require_explicit' => true,
			'record_proof'     => true,
			'allow_withdraw'   => true,
			'framework'        => 'none',
			'geo_rule_id'      => null,
			'geo_rule_name'    => null,
		);

		if ( empty( $rule ) ) {
			return $defaults;
		}

		return array(
			'show_banner'      => $rule['show_banner'] ?? true,
			'consent_mode'     => $rule['consent_mode'] ?? 'opt-in',
			'show_reject'      => $rule['show_reject'] ?? true,
			'require_explicit' => $rule['require_explicit'] ?? true,
			'record_proof'     => $rule['record_proof'] ?? true,
			'allow_withdraw'   => $rule['allow_withdraw'] ?? true,
			'framework'        => $rule['framework'] ?? 'none',
			'geo_rule_id'      => $rule['id'] ?? null,
			'geo_rule_name'    => $rule['name'] ?? null,
		);
	}

	/**
	 * Map framework to banner template
	 *
	 * @since 3.0.3
	 * @param string $framework Framework name (GDPR, CCPA, LGPD, etc.).
	 * @return string Banner template (eu, ccpa, simple, advanced).
	 */
	public function map_framework_to_template( string $framework ): string {
		$framework = strtoupper( $framework );

		switch ( $framework ) {
			case 'GDPR':
			case 'UK-GDPR':
			case 'EUDPR':
				return 'eu';

			case 'CCPA':
			case 'CPRA':
				return 'ccpa';

			case 'LGPD':
			case 'POPIA':
			case 'PDPA':
			case 'DPDP':
				return 'advanced';

			default:
				return 'simple';
		}
	}

	/**
	 * Clear cached rules (call after rules are updated)
	 *
	 * @since 3.0.3
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cached_rules = null;
	}
}
