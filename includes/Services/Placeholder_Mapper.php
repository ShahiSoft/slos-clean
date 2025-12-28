<?php
/**
 * Placeholder Mapper Service
 *
 * Maps company profile data to template placeholders and handles
 * placeholder resolution, conditionals, loops, and default values.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Placeholder_Mapper
 *
 * Handles mapping of profile data to template placeholders.
 *
 * @since 4.1.0
 */
class Placeholder_Mapper extends Base_Service {

	/**
	 * Profile data cache
	 *
	 * @var array
	 */
	protected $profile = array();

	/**
	 * Resolved placeholders cache
	 *
	 * @var array
	 */
	protected $resolved = array();

	/**
	 * Placeholder pattern (matches {{field_name}}, {{field.nested}}, etc.)
	 *
	 * @var string
	 */
	const PLACEHOLDER_PATTERN = '/\{\{\s*([a-zA-Z0-9_\.\@]+)(\|[^}]+)?\s*\}\}/';

	/**
	 * Conditional pattern (matches {{if field}}...{{/if}})
	 *
	 * @var string
	 */
	const CONDITIONAL_PATTERN = '/\{\{if\s+([a-zA-Z0-9_\.]+)\}\}(.*?)\{\{\/if\}\}/s';

	/**
	 * Foreach loop pattern (matches {{foreach items as item}}...{{/foreach}})
	 *
	 * @var string
	 */
	const FOREACH_PATTERN = '/\{\{foreach\s+([a-zA-Z0-9_\.]+)\s+as\s+([a-zA-Z0-9_]+)\}\}(.*?)\{\{\/foreach\}\}/s';

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Parse template and replace all placeholders
	 *
	 * Main entry point for template processing.
	 *
	 * @since 4.1.0
	 * @param string $template Template content with placeholders.
	 * @param array  $profile  Company profile data.
	 * @return string Processed content
	 */
	public function parse_template( string $template, array $profile ): string {
		$this->profile  = $profile;
		$this->resolved = $this->build_placeholder_map( $profile );

		// Process in order: conditionals, loops, then simple placeholders
		$content = $this->process_conditionals( $template );
		$content = $this->process_loops( $content );
		$content = $this->process_placeholders( $content );

		// Clean up any unresolved placeholders
		$content = $this->clean_unresolved( $content );

		return $content;
	}

	/**
	 * Build complete placeholder mapping from profile
	 *
	 * Creates a flat array of all available placeholders from nested profile data.
	 *
	 * @since 4.1.0
	 * @param array $profile Company profile data.
	 * @return array Placeholder map (key => value)
	 */
	public function build_placeholder_map( array $profile ): array {
		$map = array();

		// Company fields (Step 1)
		$company = $profile['company'] ?? array();
		$map['company_legal_name']   = $company['legal_name'] ?? '';
		$map['company_trading_name'] = $company['trading_name'] ?? $company['legal_name'] ?? '';
		$map['company_registration'] = $company['registration_number'] ?? '';
		$map['company_vat_number']   = $company['vat_number'] ?? '';
		$map['business_type']        = $this->format_business_type( $company['business_type'] ?? '' );
		$map['industry']             = $company['industry'] ?? '';

		// Address
		$address               = $company['address'] ?? array();
		$map['company_street'] = $address['street'] ?? '';
		$map['company_city']   = $address['city'] ?? '';
		$map['company_state']  = $address['state'] ?? '';
		$map['company_postal'] = $address['postal_code'] ?? '';
		$map['company_country'] = $this->get_country_name( $address['country'] ?? '' );
		$map['company_country_code'] = $address['country'] ?? '';
		$map['company_address'] = $this->format_address( $address );

		// Contacts (Step 2)
		$contacts = $profile['contacts'] ?? array();
		$map['legal_contact_email'] = $contacts['legal_email'] ?? '';
		$map['support_email']       = $contacts['support_email'] ?? $contacts['legal_email'] ?? '';
		$map['contact_phone']       = $contacts['phone'] ?? '';

		// DPO
		$dpo               = $contacts['dpo'] ?? array();
		$map['dpo_name']   = $dpo['name'] ?? __( 'Data Protection Officer', 'shahi-legalflowsuite' );
		$map['dpo_email']  = $dpo['email'] ?? '';
		$map['dpo_phone']  = $dpo['phone'] ?? '';
		$map['dpo_address'] = $dpo['address'] ?? $map['company_address'];

		// Website (Step 3)
		$website = $profile['website'] ?? array();
		$map['site_url']             = $website['url'] ?? get_site_url();
		$map['app_name']             = $website['app_name'] ?? get_bloginfo( 'name' );
		$map['service_description']  = $website['service_description'] ?? '';
		$map['target_audience']      = $website['target_audience'] ?? '';

		// Data Collection (Step 4)
		$data_collection = $profile['data_collection'] ?? array();
		$map['data_types']       = $this->format_list( $data_collection['personal_data_types'] ?? array() );
		$map['data_types_array'] = $data_collection['personal_data_types'] ?? array();
		$map['data_purposes']    = $this->format_list( $data_collection['purposes'] ?? array() );
		$map['data_purposes_array'] = $data_collection['purposes'] ?? array();
		$map['lawful_bases']     = $this->format_list( $data_collection['lawful_bases'] ?? array() );
		$map['lawful_bases_array'] = $data_collection['lawful_bases'] ?? array();
		$map['special_categories'] = $data_collection['special_categories'] ?? false;
		$map['children_data']    = $data_collection['children_data'] ?? false;
		$map['minimum_age']      = $data_collection['minimum_age'] ?? 16;

		// Third Parties (Step 5)
		$third_parties = $profile['third_parties'] ?? array();
		$map['third_party_processors'] = $third_parties['processors'] ?? array();
		$map['third_party_partners']   = $third_parties['partners'] ?? array();
		$map['has_third_parties']      = ! empty( $third_parties['processors'] ) || ! empty( $third_parties['partners'] );

		// Cookies (Step 6)
		$cookies = $profile['cookies'] ?? array();
		$map['essential_cookies']  = $cookies['essential'] ?? array();
		$map['analytics_cookies']  = $cookies['analytics'] ?? array();
		$map['marketing_cookies']  = $cookies['marketing'] ?? array();
		$map['functional_cookies'] = $cookies['functional'] ?? array();
		$map['has_analytics_cookies'] = ! empty( $cookies['analytics'] );
		$map['has_marketing_cookies'] = ! empty( $cookies['marketing'] );
		$map['has_functional_cookies'] = ! empty( $cookies['functional'] );

		// Legal (Step 7)
		$legal = $profile['legal'] ?? array();
		$map['jurisdiction']      = $this->get_country_name( $legal['primary_jurisdiction'] ?? '' );
		$map['jurisdiction_code'] = $legal['primary_jurisdiction'] ?? '';
		$map['gdpr_applies']      = $legal['gdpr_applies'] ?? false;
		$map['ccpa_applies']      = $legal['ccpa_applies'] ?? false;
		$map['lgpd_applies']      = $legal['lgpd_applies'] ?? false;
		$map['supervisory_authority'] = $legal['supervisory_authority'] ?? '';

		// EU Representative
		$eu_rep = $legal['representative_eu'] ?? array();
		$map['eu_rep_name']    = $eu_rep['name'] ?? '';
		$map['eu_rep_email']   = $eu_rep['email'] ?? '';
		$map['eu_rep_address'] = $eu_rep['address'] ?? '';
		$map['has_eu_rep']     = ! empty( $eu_rep['name'] );

		// UK Representative
		$uk_rep = $legal['representative_uk'] ?? array();
		$map['uk_rep_name']    = $uk_rep['name'] ?? '';
		$map['uk_rep_email']   = $uk_rep['email'] ?? '';
		$map['uk_rep_address'] = $uk_rep['address'] ?? '';
		$map['has_uk_rep']     = ! empty( $uk_rep['name'] );

		// Retention (Step 8)
		$retention = $profile['retention'] ?? array();
		$map['retention_period']   = $this->format_retention_period( $retention['default_period'] ?? '' );
		$map['retention_by_category'] = $retention['by_category'] ?? array();
		$map['deletion_policy']    = $retention['deletion_policy'] ?? '';
		$map['backup_retention']   = $retention['backup_retention'] ?? '';

		// Security
		$security = $profile['security'] ?? array();
		$map['security_measures'] = $this->format_list( $security['measures'] ?? array() );
		$map['security_measures_array'] = $security['measures'] ?? array();
		$map['certifications']    = $this->format_list( $security['certifications'] ?? array() );
		$map['breach_procedure']  = $security['breach_procedure'] ?? '';

		// User Rights
		$user_rights = $profile['user_rights'] ?? array();
		$map['response_timeframe'] = $user_rights['response_timeframe'] ?? 30;

		// System/WordPress variables
		$map['site_name']    = get_bloginfo( 'name' );
		$map['admin_email']  = get_option( 'admin_email' );
		$map['today']        = wp_date( 'F j, Y' );
		$map['year']         = wp_date( 'Y' );
		$map['current_date'] = wp_date( get_option( 'date_format' ) );

		/**
		 * Filter placeholder map before use
		 *
		 * @since 4.1.0
		 * @param array $map     Placeholder map.
		 * @param array $profile Company profile data.
		 */
		return apply_filters( 'slos_placeholder_map', $map, $profile );
	}

	/**
	 * Process conditional blocks
	 *
	 * Handles {{if field}}...{{/if}} syntax.
	 *
	 * @since 4.1.0
	 * @param string $content Template content.
	 * @return string Processed content
	 */
	protected function process_conditionals( string $content ): string {
		return preg_replace_callback(
			self::CONDITIONAL_PATTERN,
			function ( $matches ) {
				$field      = $matches[1];
				$inner      = $matches[2];
				$value      = $this->resolve_value( $field );

				// Check if value is truthy
				if ( $this->is_truthy( $value ) ) {
					// Recursively process inner content
					return $this->process_conditionals( $inner );
				}

				return '';
			},
			$content
		);
	}

	/**
	 * Process foreach loops
	 *
	 * Handles {{foreach items as item}}...{{/foreach}} syntax.
	 *
	 * @since 4.1.0
	 * @param string $content Template content.
	 * @return string Processed content
	 */
	protected function process_loops( string $content ): string {
		return preg_replace_callback(
			self::FOREACH_PATTERN,
			function ( $matches ) {
				$array_field = $matches[1];
				$item_var    = $matches[2];
				$inner       = $matches[3];

				$array = $this->resolve_value( $array_field );

				if ( ! is_array( $array ) || empty( $array ) ) {
					return '';
				}

				$output = '';
				foreach ( $array as $index => $item ) {
					$item_content = $inner;

					// Replace item references
					if ( is_array( $item ) ) {
						// Array item with properties
						foreach ( $item as $key => $val ) {
							$placeholder = '{{' . $item_var . '.' . $key . '}}';
							$item_content = str_replace( $placeholder, esc_html( $val ), $item_content );
						}
					} else {
						// Simple value
						$item_content = str_replace( '{{' . $item_var . '}}', esc_html( $item ), $item_content );
					}

					// Replace index placeholder
					$item_content = str_replace( '{{@index}}', $index, $item_content );
					$item_content = str_replace( '{{@position}}', $index + 1, $item_content );

					$output .= $item_content;
				}

				return $output;
			},
			$content
		);
	}

	/**
	 * Process simple placeholders
	 *
	 * Handles {{field}}, {{field|default:"value"}} syntax.
	 *
	 * @since 4.1.0
	 * @param string $content Template content.
	 * @return string Processed content
	 */
	protected function process_placeholders( string $content ): string {
		return preg_replace_callback(
			self::PLACEHOLDER_PATTERN,
			function ( $matches ) {
				$field   = $matches[1];
				$filters = isset( $matches[2] ) ? trim( $matches[2], '|' ) : '';

				$value = $this->resolve_value( $field );

				// Apply filters
				if ( ! empty( $filters ) ) {
					$value = $this->apply_filters( $value, $filters );
				}

				// Convert arrays to formatted strings
				if ( is_array( $value ) ) {
					$value = $this->format_list( $value );
				}

				// Convert booleans
				if ( is_bool( $value ) ) {
					$value = $value ? __( 'Yes', 'shahi-legalflowsuite' ) : __( 'No', 'shahi-legalflowsuite' );
				}

				// Escape output for security
				return wp_kses_post( (string) $value );
			},
			$content
		);
	}

	/**
	 * Resolve a placeholder value
	 *
	 * @since 4.1.0
	 * @param string $field Field name or path.
	 * @return mixed Resolved value
	 */
	protected function resolve_value( string $field ) {
		// Check direct mapping first
		if ( isset( $this->resolved[ $field ] ) ) {
			return $this->resolved[ $field ];
		}

		// Try nested path in profile
		if ( strpos( $field, '.' ) !== false ) {
			return $this->get_nested_value( $this->profile, $field );
		}

		return null;
	}

	/**
	 * Apply filters to value
	 *
	 * Supports: default, upper, lower, capitalize, escape, date
	 *
	 * @since 4.1.0
	 * @param mixed  $value   Value to filter.
	 * @param string $filters Filter string (e.g., 'default:"fallback"').
	 * @return mixed Filtered value
	 */
	protected function apply_filters( $value, string $filters ) {
		// Parse filter and arguments
		if ( preg_match( '/^default:"([^"]*)"/', $filters, $match ) ) {
			if ( empty( $value ) || '' === $value ) {
				return $match[1];
			}
		}

		if ( strpos( $filters, 'upper' ) !== false ) {
			$value = strtoupper( (string) $value );
		}

		if ( strpos( $filters, 'lower' ) !== false ) {
			$value = strtolower( (string) $value );
		}

		if ( strpos( $filters, 'capitalize' ) !== false ) {
			$value = ucwords( strtolower( (string) $value ) );
		}

		if ( strpos( $filters, 'escape:html' ) !== false ) {
			$value = esc_html( (string) $value );
		}

		if ( strpos( $filters, 'escape:attr' ) !== false ) {
			$value = esc_attr( (string) $value );
		}

		if ( preg_match( '/date:"([^"]+)"/', $filters, $match ) ) {
			$value = wp_date( $match[1], strtotime( (string) $value ) );
		}

		return $value;
	}

	/**
	 * Clean unresolved placeholders
	 *
	 * Replaces unresolved {{placeholders}} with [MISSING: fieldname] marker.
	 *
	 * @since 4.1.0
	 * @param string $content Content to clean.
	 * @return string Cleaned content
	 */
	protected function clean_unresolved( string $content ): string {
		return preg_replace_callback(
			self::PLACEHOLDER_PATTERN,
			function ( $matches ) {
				$field = $matches[1];
				// Log unresolved placeholder
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "[SLOS] Unresolved placeholder: {$field}" );
				}
				return '<span class="slos-missing-placeholder">[MISSING: ' . esc_html( $field ) . ']</span>';
			},
			$content
		);
	}

	/**
	 * Check if value is truthy
	 *
	 * @since 4.1.0
	 * @param mixed $value Value to check.
	 * @return bool True if truthy
	 */
	protected function is_truthy( $value ): bool {
		if ( null === $value ) {
			return false;
		}

		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			return '' !== trim( $value );
		}

		if ( is_array( $value ) ) {
			return ! empty( $value );
		}

		return (bool) $value;
	}

	/**
	 * Get nested value from array using dot notation
	 *
	 * @since 4.1.0
	 * @param array  $array Array to search.
	 * @param string $path  Dot notation path.
	 * @return mixed Value or null
	 */
	protected function get_nested_value( array $array, string $path ) {
		$keys  = explode( '.', $path );
		$value = $array;

		foreach ( $keys as $key ) {
			if ( ! is_array( $value ) || ! isset( $value[ $key ] ) ) {
				return null;
			}
			$value = $value[ $key ];
		}

		return $value;
	}

	/**
	 * Format array as comma-separated list
	 *
	 * @since 4.1.0
	 * @param array  $items Items to format.
	 * @param string $glue  Separator (default: ', ').
	 * @return string Formatted string
	 */
	public function format_list( array $items, string $glue = ', ' ): string {
		// Handle array of arrays (extract names/labels)
		$formatted = array();
		foreach ( $items as $item ) {
			if ( is_array( $item ) ) {
				$formatted[] = $item['name'] ?? $item['label'] ?? reset( $item );
			} else {
				$formatted[] = (string) $item;
			}
		}

		return implode( $glue, array_filter( array_map( 'esc_html', $formatted ) ) );
	}

	/**
	 * Format address from components
	 *
	 * @since 4.1.0
	 * @param array $address Address components.
	 * @return string Formatted address
	 */
	public function format_address( array $address ): string {
		$parts = array_filter(
			array(
				$address['street'] ?? '',
				$address['city'] ?? '',
				$address['state'] ?? '',
				$address['postal_code'] ?? '',
				$this->get_country_name( $address['country'] ?? '' ),
			)
		);

		return implode( ', ', $parts );
	}

	/**
	 * Get country name from code
	 *
	 * @since 4.1.0
	 * @param string $code Country code.
	 * @return string Country name
	 */
	public function get_country_name( string $code ): string {
		$countries = \ShahiLegalFlowSuite\Database\Migrations\Migration_Company_Profile::get_countries();
		return $countries[ strtoupper( $code ) ] ?? $code;
	}

	/**
	 * Format business type label
	 *
	 * @since 4.1.0
	 * @param string $type Business type code.
	 * @return string Human-readable label
	 */
	public function format_business_type( string $type ): string {
		$types = array(
			'sole_proprietor' => __( 'Sole Proprietorship', 'shahi-legalflowsuite' ),
			'partnership'     => __( 'Partnership', 'shahi-legalflowsuite' ),
			'llc'             => __( 'Limited Liability Company', 'shahi-legalflowsuite' ),
			'corporation'     => __( 'Corporation', 'shahi-legalflowsuite' ),
			'nonprofit'       => __( 'Non-Profit Organization', 'shahi-legalflowsuite' ),
			'government'      => __( 'Government Agency', 'shahi-legalflowsuite' ),
			'other'           => __( 'Other', 'shahi-legalflowsuite' ),
		);

		return $types[ $type ] ?? $type;
	}

	/**
	 * Format retention period label
	 *
	 * @since 4.1.0
	 * @param string $period Retention period code.
	 * @return string Human-readable label
	 */
	public function format_retention_period( string $period ): string {
		$periods = \ShahiLegalFlowSuite\Database\Migrations\Migration_Company_Profile::get_retention_periods();
		return $periods[ $period ] ?? $period;
	}

	/**
	 * Get all available placeholders with descriptions
	 *
	 * Useful for documentation and admin UI.
	 *
	 * @since 4.1.0
	 * @return array Placeholder info
	 */
	public function get_available_placeholders(): array {
		return array(
			// Company
			'company_legal_name'   => __( 'Company legal name', 'shahi-legalflowsuite' ),
			'company_trading_name' => __( 'Trading name (or legal name if not set)', 'shahi-legalflowsuite' ),
			'company_address'      => __( 'Full formatted company address', 'shahi-legalflowsuite' ),
			'company_country'      => __( 'Company country', 'shahi-legalflowsuite' ),
			'business_type'        => __( 'Type of business', 'shahi-legalflowsuite' ),

			// Contacts
			'legal_contact_email'  => __( 'Legal contact email', 'shahi-legalflowsuite' ),
			'support_email'        => __( 'Support email', 'shahi-legalflowsuite' ),
			'dpo_name'             => __( 'Data Protection Officer name', 'shahi-legalflowsuite' ),
			'dpo_email'            => __( 'DPO email address', 'shahi-legalflowsuite' ),

			// Website
			'site_url'             => __( 'Website URL', 'shahi-legalflowsuite' ),
			'app_name'             => __( 'Application/service name', 'shahi-legalflowsuite' ),
			'service_description'  => __( 'Service description', 'shahi-legalflowsuite' ),

			// Data
			'data_types'           => __( 'Types of personal data collected', 'shahi-legalflowsuite' ),
			'data_purposes'        => __( 'Purposes of data processing', 'shahi-legalflowsuite' ),
			'minimum_age'          => __( 'Minimum user age', 'shahi-legalflowsuite' ),

			// Legal
			'jurisdiction'         => __( 'Primary legal jurisdiction', 'shahi-legalflowsuite' ),
			'gdpr_applies'         => __( 'Whether GDPR applies', 'shahi-legalflowsuite' ),
			'retention_period'     => __( 'Default data retention period', 'shahi-legalflowsuite' ),

			// System
			'site_name'            => __( 'WordPress site name', 'shahi-legalflowsuite' ),
			'today'                => __( 'Current date', 'shahi-legalflowsuite' ),
			'year'                 => __( 'Current year', 'shahi-legalflowsuite' ),
		);
	}
}
