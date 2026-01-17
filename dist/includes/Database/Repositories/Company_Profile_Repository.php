<?php
/**
 * Company Profile Repository
 *
 * Handles storage, retrieval, and management of company profile data
 * for the Document Hub system. Profile data is used to populate
 * placeholders in legal document templates.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Repositories
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Database\Repositories;

use ShahiLegalFlowSuite\Database\Migrations\Migration_Company_Profile;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Company_Profile_Repository
 *
 * Provides CRUD operations for company profile data.
 * Implements singleton pattern as there's only one profile per installation.
 *
 * @since 4.1.0
 */
class Company_Profile_Repository extends Base_Repository {

	/**
	 * Singleton instance
	 *
	 * @var Company_Profile_Repository|null
	 */
	private static $instance = null;

	/**
	 * Cached profile data
	 *
	 * @var array|null
	 */
	private $cached_profile = null;

	/**
	 * Get singleton instance
	 *
	 * @since 4.1.0
	 * @return Company_Profile_Repository
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get table name without prefix
	 *
	 * @since 4.1.0
	 * @return string
	 */
	protected function get_table_name(): string {
		return 'slos_company_profile';
	}

	/**
	 * Get the current profile
	 *
	 * Returns the profile data, creating a default one if none exists.
	 *
	 * @since 4.1.0
	 * @param bool $bypass_cache Whether to bypass the cache.
	 * @return array Profile data array
	 */
	public function get_profile( bool $bypass_cache = false ): array {
		if ( ! $bypass_cache && null !== $this->cached_profile ) {
			return $this->cached_profile;
		}

		// Ensure table exists before querying to avoid fatal DB errors...
		if ( ! Migration_Company_Profile::is_applied() ) {
			Migration_Company_Profile::up();
		}

		// If still missing, return safe default to prevent recursion...
		if ( ! Migration_Company_Profile::is_applied() ) {
			$this->cached_profile = Migration_Company_Profile::get_default_profile_structure();
			return $this->cached_profile;
		}

		$row = $this->wpdb->get_row(
			"SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1"
		);

		if ( ! $row ) {
			// No profile exists, create default. If creation fails, return default to stop recursion...
			$created = $this->create_default_profile();
			if ( ! $created ) {
				$this->cached_profile = Migration_Company_Profile::get_default_profile_structure();
				return $this->cached_profile;
			}
			return $this->get_profile( true );
		}

		$profile_data = $this->decode_json( $row->profile_data );

		// Merge with default structure to ensure all keys exist...
		$default              = Migration_Company_Profile::get_default_profile_structure();
		$merged               = $this->merge_recursive( $default, $profile_data );
		$this->cached_profile = $merged;

		return $this->cached_profile;
	}

	/**
	 * Get profile metadata (id, version, completion, timestamps)
	 *
	 * @since 4.1.0
	 * @return array|null Profile metadata or null if not found
	 */
	public function get_profile_meta(): ?array {
		$row = $this->wpdb->get_row(
			"SELECT id, completion_percentage, version, created_at, updated_at, updated_by 
			 FROM {$this->table} ORDER BY id DESC LIMIT 1"
		);

		if ( ! $row ) {
			return null;
		}

		return array(
			'id'                    => (int) $row->id,
			'completion_percentage' => (int) $row->completion_percentage,
			'version'               => (int) $row->version,
			'created_at'            => $row->created_at,
			'updated_at'            => $row->updated_at,
			'updated_by'            => $row->updated_by ? (int) $row->updated_by : null,
		);
	}

	/**
	 * Save profile data
	 *
	 * Updates the existing profile or creates a new version.
	 *
	 * @since 4.1.0
	 * @param array $profile_data Full or partial profile data to save.
	 * @param bool  $increment_version Whether to increment version number.
	 * @return bool True on success, false on failure
	 */
	public function save_profile( array $profile_data, bool $increment_version = false ): bool {
		$current = $this->get_profile();
		$meta    = $this->get_profile_meta();

		// Merge new data with existing...
		$merged = $this->merge_recursive( $current, $profile_data );

		// Calculate completion percentage...
		$completion = $this->calculate_completion( $merged );

		// Update meta...
		$merged['_meta']['completion'] = $completion;
		if ( $increment_version ) {
			$merged['_meta']['version'] = ( $merged['_meta']['version'] ?? 1 ) + 1;
		}

		$new_version = $increment_version ? ( ( $meta['version'] ?? 1 ) + 1 ) : ( $meta['version'] ?? 1 );

		$update_data = array(
			'profile_data'          => wp_json_encode( $merged ),
			'completion_percentage' => $completion,
			'version'               => $new_version,
			'updated_at'            => current_time( 'mysql' ),
			'updated_by'            => get_current_user_id() ?: null,
		);

		if ( $meta && isset( $meta['id'] ) ) {
			// Update existing...
			$result = $this->wpdb->update(
				$this->table,
				$update_data,
				array( 'id' => $meta['id'] ),
				array( '%s', '%d', '%d', '%s', '%d' ),
				array( '%d' )
			);
		} else {
			// Insert new...
			$update_data['created_at'] = current_time( 'mysql' );
			$result                    = $this->wpdb->insert(
				$this->table,
				$update_data,
				array( '%s', '%d', '%d', '%s', '%d', '%s' )
			);
		}

		// Clear cache...
		$this->cached_profile = null;

		// Update profile version tracking for Generate Documents tab (Phase 0K)..
		// This allows documents to detect when profile data has changed since generation...
		if ( false !== $result ) {
			update_option( 'slos_profile_last_updated', current_time( 'mysql' ) );
			update_option( 'slos_profile_version', $new_version );
		}

		return false !== $result;
	}

	/**
	 * Update a specific section of the profile
	 *
	 * @since 4.1.0
	 * @param string $section Section key (e.g., 'company', 'contacts').
	 * @param array  $data    Section data.
	 * @return bool True on success
	 */
	public function update_section( string $section, array $data ): bool {
		$profile = $this->get_profile();

		if ( ! isset( $profile[ $section ] ) ) {
			return false;
		}

		$profile[ $section ] = $this->merge_recursive( $profile[ $section ], $data );

		// Mark step as completed...
		$step_map = $this->get_step_section_map();
		foreach ( $step_map as $step => $sections ) {
			if ( in_array( $section, $sections, true ) ) {
				if ( ! in_array( $step, $profile['_meta']['completed_steps'], true ) ) {
					$profile['_meta']['completed_steps'][] = $step;
				}
				$profile['_meta']['last_step'] = max( $profile['_meta']['last_step'], $step );
				break;
			}
		}

		return $this->save_profile( $profile );
	}

	/**
	 * Get a specific section of the profile
	 *
	 * @since 4.1.0
	 * @param string $section Section key.
	 * @return array|null Section data or null if not found
	 */
	public function get_section( string $section ): ?array {
		$profile = $this->get_profile();
		return $profile[ $section ] ?? null;
	}

	/**
	 * Get a specific field value using dot notation
	 *
	 * @since 4.1.0
	 * @param string $path    Dot notation path (e.g., 'company.legal_name').
	 * @param mixed  $default Default value if not found.
	 * @return mixed Field value or default
	 */
	public function get_field( string $path, $default = null ) {
		$profile = $this->get_profile();
		$keys    = explode( '.', $path );
		$value   = $profile;

		foreach ( $keys as $key ) {
			if ( ! is_array( $value ) || ! isset( $value[ $key ] ) ) {
				return $default;
			}
			$value = $value[ $key ];
		}

		return $value;
	}

	/**
	 * Set a specific field value using dot notation
	 *
	 * @since 4.1.0
	 * @param string $path  Dot notation path (e.g., 'company.legal_name').
	 * @param mixed  $value Value to set.
	 * @return bool True on success
	 */
	public function set_field( string $path, $value ): bool {
		$profile = $this->get_profile();
		$keys    = explode( '.', $path );
		$ref     = &$profile;

		foreach ( $keys as $i => $key ) {
			if ( $i === count( $keys ) - 1 ) {
				$ref[ $key ] = $value;
			} else {
				if ( ! isset( $ref[ $key ] ) || ! is_array( $ref[ $key ] ) ) {
					$ref[ $key ] = array();
				}
				$ref = &$ref[ $key ];
			}
		}

		return $this->save_profile( $profile );
	}

	/**
	 * Get profile completion percentage
	 *
	 * @since 4.1.0
	 * @return int Percentage (0-100)
	 */
	public function get_completion_percentage(): int {
		$meta = $this->get_profile_meta();
		return $meta['completion_percentage'] ?? 0;
	}

	/**
	 * Get missing required fields
	 *
	 * @since 4.1.0
	 * @return array Array of missing field paths
	 */
	public function get_missing_fields(): array {
		$profile         = $this->get_profile();
		$required_fields = $this->get_required_fields();
		$missing         = array();

		foreach ( $required_fields as $path => $label ) {
			$value = $this->get_field( $path );
			if ( $this->is_empty_value( $value ) ) {
				$missing[ $path ] = $label;
			}
		}

		return $missing;
	}

	/**
	 * Get profile version
	 *
	 * @since 4.1.0
	 * @return int Version number
	 */
	public function get_version(): int {
		$meta = $this->get_profile_meta();
		return $meta['version'] ?? 1;
	}

	/**
	 * Check if profile has been updated since a given version
	 *
	 * @since 4.1.0
	 * @param int $version Version to compare against.
	 * @return bool True if profile is newer
	 */
	public function is_newer_than( int $version ): bool {
		return $this->get_version() > $version;
	}

	/**
	 * Get all placeholder values from profile
	 *
	 * Maps profile data to placeholder keys used in templates.
	 *
	 * @since 4.1.0
	 * @return array Placeholder key => value pairs
	 */
	public function get_placeholder_values(): array {
		$profile = $this->get_profile();

		$placeholders = array(
			// Company...
			'business_name'         => $profile['company']['legal_name'] ?? '',
			'trading_name'          => $profile['company']['trading_name'] ?? '',
			'company_registration'  => $profile['company']['registration_number'] ?? '',
			'vat_number'            => $profile['company']['vat_number'] ?? '',
			'company_address'       => $this->format_address( $profile['company']['address'] ?? array() ),
			'company_country'       => $profile['company']['address']['country'] ?? '',
			'business_type'         => $profile['company']['business_type'] ?? '',
			'industry'              => $profile['company']['industry'] ?? '',

			// Contacts...
			'legal_contact'         => $profile['contacts']['legal_email'] ?? '',
			'support_email'         => $profile['contacts']['support_email'] ?? '',
			'company_phone'         => $profile['contacts']['phone'] ?? '',
			'dpo_name'              => $profile['contacts']['dpo']['name'] ?? '',
			'dpo_email'             => $profile['contacts']['dpo']['email'] ?? '',
			'dpo_phone'             => $profile['contacts']['dpo']['phone'] ?? '',
			'dpo_address'           => $profile['contacts']['dpo']['address'] ?? '',

			// Website...
			'site_url'              => $profile['website']['url'] ?? get_bloginfo( 'url' ),
			'site_name'             => $profile['website']['app_name'] ?? get_bloginfo( 'name' ),
			'service_description'   => $profile['website']['service_description'] ?? '',

			// Legal...
			'jurisdiction'          => $profile['legal']['primary_jurisdiction'] ?? '',
			'supervisory_authority' => $profile['legal']['supervisory_authority'] ?? '',
			'eu_representative'     => $this->format_representative( $profile['legal']['representative_eu'] ?? array() ),
			'uk_representative'     => $this->format_representative( $profile['legal']['representative_uk'] ?? array() ),

			// Retention...
			'retention_period'      => $profile['retention']['default_period'] ?? '',
			'deletion_policy'       => $profile['retention']['deletion_policy'] ?? '',

			// User Rights...
			'response_timeframe'    => $profile['user_rights']['response_timeframe'] ?? 30,

			// Third Parties - formatted lists...
			'analytics_providers'   => $this->format_list( $profile['third_parties']['analytics'] ?? array() ),
			'payment_processors'    => $this->format_list( $profile['third_parties']['payment'] ?? array() ),
			'marketing_providers'   => $this->format_list( $profile['third_parties']['marketing'] ?? array() ),
			'hosting_providers'     => $this->format_list( $profile['third_parties']['hosting'] ?? array() ),

			// Cookies - formatted lists...
			'essential_cookies'     => $this->format_cookie_list( $profile['cookies']['essential'] ?? array() ),
			'analytics_cookies'     => $this->format_cookie_list( $profile['cookies']['analytics'] ?? array() ),
			'marketing_cookies'     => $this->format_cookie_list( $profile['cookies']['marketing'] ?? array() ),
			'preference_cookies'    => $this->format_cookie_list( $profile['cookies']['preferences'] ?? array() ),

			// Data Collection...
			'personal_data_types'   => $this->format_list( $profile['data_collection']['personal_data_types'] ?? array() ),
			'data_purposes'         => $this->format_list( $profile['data_collection']['purposes'] ?? array() ),
			'minimum_age'           => $profile['data_collection']['minimum_age'] ?? 16,

			// Dynamic...
			'effective_date'        => current_time( 'Y-m-d' ),
			'last_updated'          => current_time( 'F j, Y' ),
		);

		return apply_filters( 'slos_profile_placeholder_values', $placeholders, $profile );
	}

	/**
	 * Reset profile to default
	 *
	 * @since 4.1.0
	 * @return bool True on success
	 */
	public function reset_profile(): bool {
		$default = Migration_Company_Profile::get_default_profile_structure();

		// Keep some auto-detected values...
		$default['website']['url']            = get_bloginfo( 'url' );
		$default['website']['app_name']       = get_bloginfo( 'name' );
		$default['contacts']['support_email'] = get_bloginfo( 'admin_email' );

		$this->cached_profile = null;

		$meta = $this->get_profile_meta();
		if ( $meta && isset( $meta['id'] ) ) {
			return (bool) $this->wpdb->update(
				$this->table,
				array(
					'profile_data'          => wp_json_encode( $default ),
					'completion_percentage' => 0,
					'version'               => ( $meta['version'] ?? 1 ) + 1,
					'updated_at'            => current_time( 'mysql' ),
					'updated_by'            => get_current_user_id() ?: null,
				),
				array( 'id' => $meta['id'] ),
				array( '%s', '%d', '%d', '%s', '%d' ),
				array( '%d' )
			);
		}

		return $this->create_default_profile();
	}

	/*
	========================================= */
	/*
	Private Helper Methods                    */
	/* ========================================= */

	/**
	 * Create default profile record
	 *
	 * @since 4.1.0
	 * @return bool True on success
	 */
	private function create_default_profile(): bool {
		// Ensure migration is applied before attempting insert...
		if ( ! Migration_Company_Profile::is_applied() ) {
			Migration_Company_Profile::up();
			if ( ! Migration_Company_Profile::is_applied() ) {
				return false;
			}
		}

		$default = Migration_Company_Profile::get_default_profile_structure();

		// Pre-fill with detectable values...
		$default['website']['url']            = get_bloginfo( 'url' );
		$default['website']['app_name']       = get_bloginfo( 'name' );
		$default['contacts']['support_email'] = get_bloginfo( 'admin_email' );

		$result = $this->wpdb->insert(
			$this->table,
			array(
				'profile_data'          => wp_json_encode( $default ),
				'completion_percentage' => 0,
				'version'               => 1,
				'created_at'            => current_time( 'mysql' ),
				'updated_at'            => current_time( 'mysql' ),
				'updated_by'            => get_current_user_id() ?: null,
			),
			array( '%s', '%d', '%d', '%s', '%s', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Calculate profile completion percentage
	 *
	 * @since 4.1.0
	 * @param array $profile Profile data.
	 * @return int Percentage (0-100)
	 */
	private function calculate_completion( array $profile ): int {
		$required_fields = $this->get_required_fields();
		$total           = count( $required_fields );

		if ( 0 === $total ) {
			return 100;
		}

		$filled = 0;
		foreach ( $required_fields as $path => $label ) {
			$keys  = explode( '.', $path );
			$value = $profile;

			foreach ( $keys as $key ) {
				if ( ! is_array( $value ) || ! isset( $value[ $key ] ) ) {
					$value = null;
					break;
				}
				$value = $value[ $key ];
			}

			if ( ! $this->is_empty_value( $value ) ) {
				++$filled;
			}
		}

		return (int) round( ( $filled / $total ) * 100 );
	}

	/**
	 * Get required fields with labels
	 *
	 * @since 4.1.0
	 * @return array Field path => label
	 */
	private function get_required_fields(): array {
		return array(
			// Company (Step 1)...
			'company.legal_name'                  => __( 'Company Legal Name', 'shahi-legalflowsuite' ),
			'company.address.street'              => __( 'Company Street Address', 'shahi-legalflowsuite' ),
			'company.address.city'                => __( 'Company City', 'shahi-legalflowsuite' ),
			'company.address.country'             => __( 'Company Country', 'shahi-legalflowsuite' ),
			'company.business_type'               => __( 'Business Type', 'shahi-legalflowsuite' ),

			// Contacts (Step 2)...
			'contacts.legal_email'                => __( 'Legal Contact Email', 'shahi-legalflowsuite' ),
			'contacts.dpo.email'                  => __( 'DPO Email', 'shahi-legalflowsuite' ),

			// Website (Step 3)...
			'website.url'                         => __( 'Website URL', 'shahi-legalflowsuite' ),
			'website.service_description'         => __( 'Service Description', 'shahi-legalflowsuite' ),

			// Data Collection (Step 4)...
			'data_collection.personal_data_types' => __( 'Personal Data Types Collected', 'shahi-legalflowsuite' ),
			'data_collection.purposes'            => __( 'Data Processing Purposes', 'shahi-legalflowsuite' ),

			// Cookies (Step 6) - at least essential cookies...
			'cookies.essential'                   => __( 'Essential Cookies', 'shahi-legalflowsuite' ),

			// Legal (Step 7)...
			'legal.primary_jurisdiction'          => __( 'Primary Jurisdiction', 'shahi-legalflowsuite' ),

			// Retention (Step 8)...
			'retention.default_period'            => __( 'Default Retention Period', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get step to section mapping
	 *
	 * @since 4.1.0
	 * @return array Step number => section keys
	 */
	private function get_step_section_map(): array {
		return array(
			1 => array( 'company' ),
			2 => array( 'contacts' ),
			3 => array( 'website' ),
			4 => array( 'data_collection' ),
			5 => array( 'third_parties' ),
			6 => array( 'cookies' ),
			7 => array( 'legal' ),
			8 => array( 'retention', 'security', 'user_rights' ),
		);
	}

	/**
	 * Check if value is empty
	 *
	 * @since 4.1.0
	 * @param mixed $value Value to check.
	 * @return bool True if empty
	 */
	private function is_empty_value( $value ): bool {
		if ( null === $value ) {
			return true;
		}
		if ( is_string( $value ) && '' === trim( $value ) ) {
			return true;
		}
		if ( is_array( $value ) && empty( $value ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Decode JSON safely
	 *
	 * @since 4.1.0
	 * @param string $json JSON string.
	 * @return array Decoded array or empty array
	 */
	private function decode_json( $json ): array {
		if ( empty( $json ) ) {
			return array();
		}
		$data = json_decode( (string) $json, true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Recursively merge arrays
	 *
	 * @since 4.1.0
	 * @param array $base    Base array.
	 * @param array $overlay Overlay array.
	 * @return array Merged array
	 */
	private function merge_recursive( array $base, array $overlay ): array {
		foreach ( $overlay as $key => $value ) {
			if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) ) {
				// Check if it's a sequential array (list)...
				if ( array_keys( $value ) === range( 0, count( $value ) - 1 ) ) {
					// Replace lists entirely...
					$base[ $key ] = $value;
				} else {
					// Merge associative arrays...
					$base[ $key ] = $this->merge_recursive( $base[ $key ], $value );
				}
			} else {
				$base[ $key ] = $value;
			}
		}
		return $base;
	}

	/**
	 * Format address array to string
	 *
	 * @since 4.1.0
	 * @param array $address Address array.
	 * @return string Formatted address
	 */
	private function format_address( array $address ): string {
		$parts = array_filter(
			array(
				$address['street'] ?? '',
				$address['city'] ?? '',
				$address['state'] ?? '',
				$address['postal_code'] ?? '',
				$address['country'] ?? '',
			)
		);
		return implode( ', ', $parts );
	}

	/**
	 * Format representative array to string
	 *
	 * @since 4.1.0
	 * @param array $rep Representative array.
	 * @return string Formatted representative
	 */
	private function format_representative( array $rep ): string {
		if ( empty( $rep['name'] ) ) {
			return '';
		}
		$parts = array_filter(
			array(
				$rep['name'] ?? '',
				$rep['address'] ?? '',
				$rep['email'] ?? '',
			)
		);
		return implode( ', ', $parts );
	}

	/**
	 * Format array to HTML list
	 *
	 * @since 4.1.0
	 * @param array $items Items to format.
	 * @return string HTML list or comma-separated string
	 */
	private function format_list( array $items ): string {
		$items = array_filter( $items );
		if ( empty( $items ) ) {
			return '';
		}
		return implode( ', ', $items );
	}

	/**
	 * Format cookie array to descriptive list
	 *
	 * @since 4.1.0
	 * @param array $cookies Cookie definitions.
	 * @return string Formatted cookie descriptions
	 */
	private function format_cookie_list( array $cookies ): string {
		if ( empty( $cookies ) ) {
			return '';
		}

		$formatted = array();
		foreach ( $cookies as $cookie ) {
			if ( is_array( $cookie ) ) {
				$name     = $cookie['name'] ?? '';
				$purpose  = $cookie['purpose'] ?? '';
				$duration = $cookie['duration'] ?? '';
				if ( $name ) {
					$desc = $name;
					if ( $purpose ) {
						$desc .= ' - ' . $purpose;
					}
					if ( $duration ) {
						$desc .= ' (' . $duration . ')';
					}
					$formatted[] = $desc;
				}
			} elseif ( is_string( $cookie ) && ! empty( $cookie ) ) {
				$formatted[] = $cookie;
			}
		}

		return implode( '; ', $formatted );
	}
}
