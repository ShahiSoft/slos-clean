<?php
/**
 * Profile Validator Service
 *
 * Validates company profile data for completeness and correctness
 * before allowing document generation. Enforces mandatory field
 * requirements and provides detailed validation feedback.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\Company_Profile_Repository;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Profile_Validator
 *
 * Validates profile data for document generation readiness.
 *
 * @since 4.1.0
 */
class Profile_Validator extends Base_Service {

	/**
	 * Mandatory fields for document generation
	 *
	 * These fields MUST have values before any document can be generated.
	 *
	 * @var array
	 */
	protected $mandatory_fields = array(
		'company.legal_name',
		'company.address.street',
		'company.address.city',
		'company.address.country',
		'company.business_type',
		'contacts.legal_email',
		'contacts.dpo.email',
		'website.url',
		'website.service_description',
		'data_collection.personal_data_types',
		'data_collection.purposes',
		'cookies.essential',
		'legal.primary_jurisdiction',
		'retention.default_period',
	);

	/**
	 * Field labels for user-friendly messages
	 *
	 * @var array
	 */
	protected $field_labels = array();

	/**
	 * Field to wizard step mapping
	 *
	 * @var array
	 */
	protected $field_steps = array(
		'company'         => 1,
		'contacts'        => 2,
		'website'         => 3,
		'data_collection' => 4,
		'third_parties'   => 5,
		'cookies'         => 6,
		'legal'           => 7,
		'retention'       => 8,
		'security'        => 8,
		'user_rights'     => 8,
		'ecommerce'       => 9,
		'software'        => 10,
		'community'       => 11,
	);

	/**
	 * Profile repository
	 *
	 * @var Company_Profile_Repository
	 */
	protected $repository;

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->repository = Company_Profile_Repository::get_instance();

		// Initialize field labels with translations
		$this->field_labels = array(
			'company.legal_name'                  => __( 'Company Legal Name', 'shahi-legalflowsuite' ),
			'company.trading_name'                => __( 'Trading Name', 'shahi-legalflowsuite' ),
			'company.registration_number'         => __( 'Registration Number', 'shahi-legalflowsuite' ),
			'company.vat_number'                  => __( 'VAT Number', 'shahi-legalflowsuite' ),
			'company.address.street'              => __( 'Street Address', 'shahi-legalflowsuite' ),
			'company.address.city'                => __( 'City', 'shahi-legalflowsuite' ),
			'company.address.state'               => __( 'State/Province', 'shahi-legalflowsuite' ),
			'company.address.postal_code'         => __( 'Postal Code', 'shahi-legalflowsuite' ),
			'company.address.country'             => __( 'Country', 'shahi-legalflowsuite' ),
			'company.business_type'               => __( 'Business Type', 'shahi-legalflowsuite' ),
			'company.industry'                    => __( 'Industry', 'shahi-legalflowsuite' ),
			'contacts.legal_email'                => __( 'Legal Contact Email', 'shahi-legalflowsuite' ),
			'contacts.support_email'              => __( 'Support Email', 'shahi-legalflowsuite' ),
			'contacts.phone'                      => __( 'Phone Number', 'shahi-legalflowsuite' ),
			'contacts.dpo.name'                   => __( 'DPO Name', 'shahi-legalflowsuite' ),
			'contacts.dpo.email'                  => __( 'DPO Email', 'shahi-legalflowsuite' ),
			'contacts.dpo.phone'                  => __( 'DPO Phone', 'shahi-legalflowsuite' ),
			'contacts.dpo.address'                => __( 'DPO Address', 'shahi-legalflowsuite' ),
			'website.url'                         => __( 'Website URL', 'shahi-legalflowsuite' ),
			'website.app_name'                    => __( 'Application Name', 'shahi-legalflowsuite' ),
			'website.service_description'         => __( 'Service Description', 'shahi-legalflowsuite' ),
			'website.target_audience'             => __( 'Target Audience', 'shahi-legalflowsuite' ),
			'data_collection.personal_data_types' => __( 'Personal Data Types Collected', 'shahi-legalflowsuite' ),
			'data_collection.purposes'            => __( 'Data Processing Purposes', 'shahi-legalflowsuite' ),
			'data_collection.lawful_bases'        => __( 'Lawful Bases for Processing', 'shahi-legalflowsuite' ),
			'data_collection.special_categories'  => __( 'Special Category Data', 'shahi-legalflowsuite' ),
			'data_collection.children_data'       => __( 'Children\'s Data', 'shahi-legalflowsuite' ),
			'data_collection.minimum_age'         => __( 'Minimum Age', 'shahi-legalflowsuite' ),
			'third_parties.processors'            => __( 'Third-Party Processors', 'shahi-legalflowsuite' ),
			'third_parties.partners'              => __( 'Partners', 'shahi-legalflowsuite' ),
			'cookies.essential'                   => __( 'Essential Cookies', 'shahi-legalflowsuite' ),
			'cookies.analytics'                   => __( 'Analytics Cookies', 'shahi-legalflowsuite' ),
			'cookies.marketing'                   => __( 'Marketing Cookies', 'shahi-legalflowsuite' ),
			'cookies.functional'                  => __( 'Functional Cookies', 'shahi-legalflowsuite' ),
			'legal.primary_jurisdiction'          => __( 'Primary Jurisdiction', 'shahi-legalflowsuite' ),
			'legal.gdpr_applies'                  => __( 'GDPR Applicability', 'shahi-legalflowsuite' ),
			'legal.ccpa_applies'                  => __( 'CCPA Applicability', 'shahi-legalflowsuite' ),
			'legal.lgpd_applies'                  => __( 'LGPD Applicability', 'shahi-legalflowsuite' ),
			'legal.supervisory_authority'         => __( 'Supervisory Authority', 'shahi-legalflowsuite' ),
			'retention.default_period'            => __( 'Default Retention Period', 'shahi-legalflowsuite' ),
			'retention.deletion_policy'           => __( 'Deletion Policy', 'shahi-legalflowsuite' ),
			'retention.backup_retention'          => __( 'Backup Retention', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Validate profile for document generation
	 *
	 * Checks all mandatory fields and returns validation result.
	 *
	 * @since 4.1.0
	 * @param array|null $profile Profile data (uses current profile if null).
	 * @return true|\WP_Error True if valid, WP_Error with missing fields if invalid
	 */
	public function validate_for_generation( ?array $profile = null ) {
		if ( null === $profile ) {
			$profile = $this->repository->get_profile();
		}

		$missing = array();

		foreach ( $this->mandatory_fields as $field ) {
			$value = $this->get_nested_value( $profile, $field );

			if ( ! $this->has_value( $value ) ) {
				$missing[] = array(
					'field' => $field,
					'label' => $this->get_field_label( $field ),
					'step'  => $this->get_field_step( $field ),
				);
			}
		}

		if ( ! empty( $missing ) ) {
			return new \WP_Error(
				'missing_fields',
				__( 'Required fields are missing for document generation.', 'shahi-legalflowsuite' ),
				array(
					'missing'       => $missing,
					'missing_count' => count( $missing ),
				)
			);
		}

		return true;
	}

	/**
	 * Validate a specific wizard step
	 *
	 * @since 4.1.0
	 * @param int        $step    Step number (1-8).
	 * @param array      $data    Step data to validate.
	 * @param array|null $profile Full profile for context.
	 * @return true|\WP_Error True if valid, WP_Error if invalid
	 */
	public function validate_step( int $step, array $data, ?array $profile = null ) {
		$errors = array();

		// Get required fields for this step..
		$step_fields = $this->get_step_mandatory_fields( $step );

		foreach ( $step_fields as $field ) {
			// Get relative field path (remove step prefix)..
			$relative_field = $this->get_relative_field_path( $field, $step );
			$value          = $this->get_nested_value( $data, $relative_field );

			if ( ! $this->has_value( $value ) ) {
				$errors[] = array(
					'field'   => $field,
					'label'   => $this->get_field_label( $field ),
					'message' => sprintf(
						/* translators: %s: field label */
						__( '%s is required.', 'shahi-legalflowsuite' ),
						$this->get_field_label( $field )
					),
				);
			} else {
				// Additional validation based on field type..
				$validation_error = $this->validate_field_value( $field, $value );
				if ( $validation_error ) {
					$errors[] = $validation_error;
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return new \WP_Error(
				'step_validation_failed',
				__( 'Step validation failed.', 'shahi-legalflowsuite' ),
				array(
					'step'   => $step,
					'errors' => $errors,
				)
			);
		}

		return true;
	}

	/**
	 * Validate individual field value
	 *
	 * @since 4.1.0
	 * @param string $field Field path.
	 * @param mixed  $value Field value.
	 * @return array|null Error array or null if valid
	 */
	protected function validate_field_value( string $field, $value ): ?array {
		// Email validation..
		if ( strpos( $field, 'email' ) !== false && ! empty( $value ) ) {
			if ( ! is_email( $value ) ) {
				return array(
					'field'   => $field,
					'label'   => $this->get_field_label( $field ),
					'message' => sprintf(
						/* translators: %s: field label */
						__( '%s must be a valid email address.', 'shahi-legalflowsuite' ),
						$this->get_field_label( $field )
					),
				);
			}
		}

		// URL validation..
		if ( 'website.url' === $field && ! empty( $value ) ) {
			if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
				return array(
					'field'   => $field,
					'label'   => $this->get_field_label( $field ),
					'message' => __( 'Website URL must be a valid URL.', 'shahi-legalflowsuite' ),
				);
			}
		}

		// Phone validation (basic)..
		if ( strpos( $field, 'phone' ) !== false && ! empty( $value ) ) {
			$cleaned = preg_replace( '/[^0-9+\-\s()]/', '', $value );
			if ( strlen( $cleaned ) < 7 ) {
				return array(
					'field'   => $field,
					'label'   => $this->get_field_label( $field ),
					'message' => sprintf(
						/* translators: %s: field label */
						__( '%s must be a valid phone number.', 'shahi-legalflowsuite' ),
						$this->get_field_label( $field )
					),
				);
			}
		}

		return null;
	}

	/**
	 * Get mandatory fields for a specific step
	 *
	 * @since 4.1.0
	 * @param int $step Step number.
	 * @return array Mandatory fields for this step
	 */
	public function get_step_mandatory_fields( int $step ): array {
		$step_fields = array();

		foreach ( $this->mandatory_fields as $field ) {
			if ( $this->get_field_step( $field ) === $step ) {
				$step_fields[] = $field;
			}
		}

		return $step_fields;
	}

	/**
	 * Calculate profile completion percentage
	 *
	 * @since 4.1.0
	 * @param array|null $profile Profile data.
	 * @return int Completion percentage (0-100)
	 */
	public function calculate_completion( ?array $profile = null ): int {
		if ( null === $profile ) {
			$profile = $this->repository->get_profile();
		}

		$total_mandatory = count( $this->mandatory_fields );
		$completed       = 0;

		foreach ( $this->mandatory_fields as $field ) {
			$value = $this->get_nested_value( $profile, $field );
			if ( $this->has_value( $value ) ) {
				++$completed;
			}
		}

		if ( 0 === $total_mandatory ) {
			return 100;
		}

		return (int) round( ( $completed / $total_mandatory ) * 100 );
	}

	/**
	 * Get detailed completion status
	 *
	 * @since 4.1.0
	 * @param array|null $profile Profile data.
	 * @return array Completion status by step
	 */
	public function get_completion_details( ?array $profile = null ): array {
		if ( null === $profile ) {
			$profile = $this->repository->get_profile();
		}

		$details = array();

		for ( $step = 1; $step <= 11; $step++ ) {
			$step_fields    = $this->get_step_mandatory_fields( $step );
			$total          = count( $step_fields );
			$completed      = 0;
			$missing_fields = array();

			foreach ( $step_fields as $field ) {
				$value = $this->get_nested_value( $profile, $field );
				if ( $this->has_value( $value ) ) {
					++$completed;
				} else {
					$missing_fields[] = array(
						'field' => $field,
						'label' => $this->get_field_label( $field ),
					);
				}
			}

			$details[ $step ] = array(
				'step'        => $step,
				'total'       => $total,
				'completed'   => $completed,
				'percentage'  => $total > 0 ? (int) round( ( $completed / $total ) * 100 ) : 100,
				'missing'     => $missing_fields,
				'is_complete' => $completed >= $total,
			);
		}

		return $details;
	}

	/**
	 * Get missing fields summary
	 *
	 * @since 4.1.0
	 * @param array|null $profile Profile data.
	 * @return array Missing fields grouped by step
	 */
	public function get_missing_fields( ?array $profile = null ): array {
		if ( null === $profile ) {
			$profile = $this->repository->get_profile();
		}

		$missing = array();

		foreach ( $this->mandatory_fields as $field ) {
			$value = $this->get_nested_value( $profile, $field );

			if ( ! $this->has_value( $value ) ) {
				$step = $this->get_field_step( $field );

				if ( ! isset( $missing[ $step ] ) ) {
					$missing[ $step ] = array();
				}

				$missing[ $step ][] = array(
					'field' => $field,
					'label' => $this->get_field_label( $field ),
				);
			}
		}

		return $missing;
	}

	/**
	 * Check if a value is considered "filled"
	 *
	 * @since 4.1.0
	 * @param mixed $value Value to check.
	 * @return bool True if value is present
	 */
	protected function has_value( $value ): bool {
		if ( null === $value ) {
			return false;
		}

		if ( is_string( $value ) ) {
			return '' !== trim( $value );
		}

		if ( is_array( $value ) ) {
			return ! empty( $value );
		}

		if ( is_bool( $value ) ) {
			return true; // Boolean fields are always "filled"
		}

		return ! empty( $value );
	}

	/**
	 * Get nested value from array using dot notation
	 *
	 * @since 4.1.0
	 * @param array  $array Array to search.
	 * @param string $path  Dot-notation path (e.g., 'company.address.city').
	 * @return mixed Value or null if not found
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
	 * Get field label
	 *
	 * @since 4.1.0
	 * @param string $field Field path.
	 * @return string Human-readable label
	 */
	public function get_field_label( string $field ): string {
		if ( isset( $this->field_labels[ $field ] ) ) {
			return $this->field_labels[ $field ];
		}

		// Generate label from field name..
		$parts = explode( '.', $field );
		$label = end( $parts );
		$label = str_replace( '_', ' ', $label );
		$label = ucwords( $label );

		return $label;
	}

	/**
	 * Get wizard step for a field
	 *
	 * @since 4.1.0
	 * @param string $field Field path.
	 * @return int Step number (1-8)
	 */
	public function get_field_step( string $field ): int {
		$parts   = explode( '.', $field );
		$section = $parts[0] ?? '';

		return $this->field_steps[ $section ] ?? 1;
	}

	/**
	 * Get relative field path (remove section prefix)
	 *
	 * @since 4.1.0
	 * @param string $field Full field path.
	 * @param int    $step  Step number.
	 * @return string Relative path within step data
	 */
	protected function get_relative_field_path( string $field, int $step ): string {
		// Find section for this step..
		$section = array_search( $step, $this->field_steps, true );

		if ( $section && strpos( $field, $section . '.' ) === 0 ) {
			return substr( $field, strlen( $section ) + 1 );
		}

		return $field;
	}

	/**
	 * Get all mandatory fields
	 *
	 * @since 4.1.0
	 * @return array Mandatory field paths
	 */
	public function get_mandatory_fields(): array {
		return $this->mandatory_fields;
	}

	/**
	 * Check if a specific field is mandatory
	 *
	 * @since 4.1.0
	 * @param string $field Field path.
	 * @return bool True if mandatory
	 */
	public function is_mandatory( string $field ): bool {
		return in_array( $field, $this->mandatory_fields, true );
	}
}
