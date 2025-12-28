<?php
/**
 * Profile Wizard Admin Controller
 *
 * Handles the 8-step company profile wizard UI, AJAX actions,
 * step navigation, auto-save, and profile completion tracking.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Admin
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Services\Company_Profile_Service;
use ShahiLegalFlowSuite\Services\Profile_Validator;
use ShahiLegalFlowSuite\Database\Repositories\Company_Profile_Repository;
use ShahiLegalFlowSuite\Database\Migrations\Migration_Company_Profile;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Profile_Wizard
 *
 * Manages the company profile wizard interface.
 *
 * @since 4.1.0
 */
class Profile_Wizard {

	/**
	 * Company profile service
	 *
	 * @var Company_Profile_Service
	 */
	protected $profile_service;

	/**
	 * Profile validator
	 *
	 * @var Profile_Validator
	 */
	protected $validator;

	/**
	 * Profile repository
	 *
	 * @var Company_Profile_Repository
	 */
	protected $repository;

	/**
	 * Wizard step definitions
	 *
	 * @var array
	 */
	protected $steps = array();

	/**
	 * Current step
	 *
	 * @var int
	 */
	protected $current_step = 1;

	/**
	 * Page hook suffix
	 *
	 * @var string
	 */
	protected $page_hook;

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		$this->profile_service = new Company_Profile_Service();
		$this->validator       = new Profile_Validator();
		$this->repository      = Company_Profile_Repository::get_instance();
	}

	/**
	 * Initialize the wizard
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function init(): void {
		// Check if feature is enabled
		if ( ! slos_is_feature_enabled( 'company_wizard' ) ) {
			return;
		}

		// Register admin menu
		add_action( 'admin_menu', array( $this, 'register_menu' ), 25 );

		// Register AJAX handlers
		add_action( 'wp_ajax_slos_profile_get', array( $this, 'ajax_get_profile' ) );
		add_action( 'wp_ajax_slos_profile_save_step', array( $this, 'ajax_save_step' ) );
		add_action( 'wp_ajax_slos_profile_get_step', array( $this, 'ajax_get_step' ) );
		add_action( 'wp_ajax_slos_profile_validate', array( $this, 'ajax_validate_profile' ) );
		add_action( 'wp_ajax_slos_profile_get_completion', array( $this, 'ajax_get_completion' ) );
		add_action( 'wp_ajax_slos_profile_reset', array( $this, 'ajax_reset_profile' ) );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin menu page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function register_menu(): void {
		$this->page_hook = add_submenu_page(
			'shahi-legalflowsuite',
			__( 'Company Profile', 'shahi-legalflowsuite' ),
			'🏢 ' . __( 'Company Profile', 'shahi-legalflowsuite' ),
			'manage_options',
			'slos-company-profile',
			array( $this, 'render_wizard_page' )
		);
	}

	/**
	 * Enqueue wizard assets
	 *
	 * @since 4.1.0
	 * @param string $hook Page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		// Only load on wizard page
		if ( $this->page_hook !== $hook ) {
			return;
		}

		$plugin_url = SHAHI_LEGALFLOWSUITE_PLUGIN_URL;
		$version    = SHAHI_LEGALFLOWSUITE_VERSION;

		// Styles
		wp_enqueue_style(
			'slos-profile-wizard',
			$plugin_url . 'assets/css/profile-wizard.css',
			array(),
			$version
		);

		// Scripts
		wp_enqueue_script(
			'slos-profile-wizard',
			$plugin_url . 'assets/js/profile-wizard.js',
			array( 'jquery', 'wp-util' ),
			$version,
			true
		);

		// Get steps for JavaScript
		$steps     = $this->profile_service->get_steps();
		$step_data = array();
		foreach ( $steps as $num => $step ) {
			$step_data[ $num ] = array(
				'key'         => $step['key'],
				'title'       => $step['title'],
				'description' => $step['description'],
				'icon'        => $step['icon'],
			);
		}

		// Localize script data
		wp_localize_script(
			'slos-profile-wizard',
			'slosProfileWizard',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'slos_profile_wizard' ),
				'steps'          => $step_data,
				'totalSteps'     => count( $steps ),
				'currentStep'    => $this->get_current_step(),
				'hubUrl'         => admin_url( 'admin.php?page=slos-documents' ),
				'documentHubUrl' => admin_url( 'admin.php?page=slos-documents' ),
				'i18n'           => array(
					'saving'               => __( 'Saving...', 'shahi-legalflowsuite' ),
					'saved'                => __( 'Saved', 'shahi-legalflowsuite' ),
					'saveError'            => __( 'Error saving. Please try again.', 'shahi-legalflowsuite' ),
					'validating'           => __( 'Validating...', 'shahi-legalflowsuite' ),
					'requiredField'        => __( 'This field is required', 'shahi-legalflowsuite' ),
					'invalidEmail'         => __( 'Please enter a valid email address', 'shahi-legalflowsuite' ),
					'invalidUrl'           => __( 'Please enter a valid URL', 'shahi-legalflowsuite' ),
					'unsavedChanges'       => __( 'You have unsaved changes. Are you sure you want to leave?', 'shahi-legalflowsuite' ),
					'stepComplete'         => __( 'Step complete!', 'shahi-legalflowsuite' ),
					'profileComplete'      => __( 'Profile saved successfully! Redirecting to Document Hub...', 'shahi-legalflowsuite' ),
					'incompleteProfileSaved' => __( 'Profile saved with {count} optional fields remaining. Redirecting...', 'shahi-legalflowsuite' ),
					'complete'             => __( 'Complete', 'shahi-legalflowsuite' ),
					'generateDocs'         => __( 'Generate Documents', 'shahi-legalflowsuite' ),
					'nextStep'             => __( 'Next Step', 'shahi-legalflowsuite' ),
					'previousStep'         => __( 'Previous', 'shahi-legalflowsuite' ),
					'finish'               => __( 'Finish Wizard', 'shahi-legalflowsuite' ),
					'confirmReset'         => __( 'Are you sure you want to reset the profile? This cannot be undone.', 'shahi-legalflowsuite' ),
					'addCookie'            => __( 'Add Cookie', 'shahi-legalflowsuite' ),
					'removeCookie'         => __( 'Remove', 'shahi-legalflowsuite' ),
					'cookieName'           => __( 'Cookie Name', 'shahi-legalflowsuite' ),
					'cookiePurpose'        => __( 'Purpose', 'shahi-legalflowsuite' ),
					'cookieDuration'       => __( 'Duration', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Render the wizard page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function render_wizard_page(): void {
		// Security check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shahi-legalflowsuite' ) );
		}

		$steps        = $this->profile_service->get_steps();
		$profile      = $this->repository->get_profile();
		$completion   = $this->build_completion_data( $profile );
		$current_step = $this->get_current_step();

		include SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'templates/admin/profile/wizard.php';
	}

	/**
	 * Render step fields
	 *
	 * @since 4.1.0
	 * @param array      $step    Step definition.
	 * @param array|null $profile Profile data.
	 * @return void
	 */
	public function render_step_fields( array $step, ?array $profile ): void {
		if ( empty( $step['fields'] ) ) {
			return;
		}

		foreach ( $step['fields'] as $field_path => $field ) {
			$value = $this->get_nested_value( $profile ?? array(), $field_path );

			// Apply default if empty
			if ( empty( $value ) && isset( $field['default'] ) ) {
				$value = $field['default'];
			}

			$field_id   = 'slos-field-' . str_replace( '.', '-', $field_path );
			$field_name = $field_path;
			$required   = ! empty( $field['required'] );

			// Check conditional display
			if ( isset( $field['condition'] ) ) {
				$condition_met = $this->check_field_condition( $field['condition'], $profile );
				if ( ! $condition_met ) {
					continue;
				}
			}

			echo '<div class="slos-field-group" data-field="' . esc_attr( $field_path ) . '">';

			// Label
			echo '<label for="' . esc_attr( $field_id ) . '" class="slos-field-label">';
			echo esc_html( $field['label'] );
			if ( $required ) {
				echo '<span class="slos-required">*</span>';
			}
			echo '</label>';

			// Field input based on type
			$this->render_field_input( $field, $field_id, $field_name, $value );

			// Help text
			if ( ! empty( $field['help'] ) ) {
				echo '<p class="slos-field-help">' . esc_html( $field['help'] ) . '</p>';
			}

			// Error message placeholder
			echo '<span class="slos-field-error" id="' . esc_attr( $field_id ) . '-error"></span>';

			echo '</div>';
		}
	}

	/**
	 * Render field input by type
	 *
	 * @since 4.1.0
	 * @param array  $field      Field definition.
	 * @param string $field_id   Field ID.
	 * @param string $field_name Field name.
	 * @param mixed  $value      Current value.
	 * @return void
	 */
	protected function render_field_input( array $field, string $field_id, string $field_name, $value ): void {
		$type        = $field['type'] ?? 'text';
		$placeholder = $field['placeholder'] ?? '';
		$required    = ! empty( $field['required'] ) ? 'required' : '';

		switch ( $type ) {
			case 'text':
			case 'email':
			case 'url':
			case 'tel':
			case 'number':
				echo '<input type="' . esc_attr( $type ) . '" ';
				echo 'id="' . esc_attr( $field_id ) . '" ';
				echo 'name="' . esc_attr( $field_name ) . '" ';
				echo 'value="' . esc_attr( $value ?? '' ) . '" ';
				echo 'placeholder="' . esc_attr( $placeholder ) . '" ';
				echo 'class="slos-field-input" ';
				if ( $required ) {
					echo 'required ';
				}
				if ( 'number' === $type && isset( $field['min'] ) ) {
					echo 'min="' . esc_attr( $field['min'] ) . '" ';
				}
				if ( 'number' === $type && isset( $field['max'] ) ) {
					echo 'max="' . esc_attr( $field['max'] ) . '" ';
				}
				echo '>';
				break;

			case 'textarea':
				$rows = $field['rows'] ?? 4;
				echo '<textarea id="' . esc_attr( $field_id ) . '" ';
				echo 'name="' . esc_attr( $field_name ) . '" ';
				echo 'placeholder="' . esc_attr( $placeholder ) . '" ';
				echo 'rows="' . esc_attr( $rows ) . '" ';
				echo 'class="slos-field-textarea" ';
				if ( $required ) {
					echo 'required ';
				}
				echo '>' . esc_textarea( $value ?? '' ) . '</textarea>';
				break;

			case 'select':
				$options = $field['options'] ?? array();
				if ( 'countries' === $options ) {
					$options = Migration_Company_Profile::get_countries();
				}
				echo '<select id="' . esc_attr( $field_id ) . '" ';
				echo 'name="' . esc_attr( $field_name ) . '" ';
				echo 'class="slos-field-select" ';
				if ( $required ) {
					echo 'required ';
				}
				echo '>';
				foreach ( $options as $opt_value => $opt_label ) {
					$selected = selected( $value, $opt_value, false );
					echo '<option value="' . esc_attr( $opt_value ) . '"' . $selected . '>';
					echo esc_html( $opt_label );
					echo '</option>';
				}
				echo '</select>';
				break;

			case 'radio':
				$options = $field['options'] ?? array();
				echo '<div class="slos-radio-group">';
				foreach ( $options as $opt_value => $opt_label ) {
					$checked  = checked( $value, $opt_value, false );
					$radio_id = $field_id . '-' . sanitize_key( $opt_value );
					echo '<label class="slos-radio-label" for="' . esc_attr( $radio_id ) . '">';
					echo '<input type="radio" id="' . esc_attr( $radio_id ) . '" ';
					echo 'name="' . esc_attr( $field_name ) . '" ';
					echo 'value="' . esc_attr( $opt_value ) . '"' . $checked . ' ';
					echo 'class="slos-field-radio">';
					echo '<span class="slos-radio-text">' . esc_html( $opt_label ) . '</span>';
					echo '</label>';
				}
				echo '</div>';
				break;

			case 'checkbox_group':
				$options = $field['options'] ?? array();
				$value   = is_array( $value ) ? $value : array();
				echo '<div class="slos-checkbox-group">';
				foreach ( $options as $opt_value => $opt_label ) {
					$checked  = in_array( $opt_value, $value, true ) ? 'checked' : '';
					$check_id = $field_id . '-' . sanitize_key( $opt_value );
					echo '<label class="slos-checkbox-label" for="' . esc_attr( $check_id ) . '">';
					echo '<input type="checkbox" id="' . esc_attr( $check_id ) . '" ';
					echo 'name="' . esc_attr( $field_name ) . '[]" ';
					echo 'value="' . esc_attr( $opt_value ) . '" ' . $checked . ' ';
					echo 'class="slos-field-checkbox">';
					echo '<span class="slos-checkbox-text">' . esc_html( $opt_label ) . '</span>';
					echo '</label>';
				}
				echo '</div>';
				break;

			case 'tags':
				$suggestions = $field['suggestions'] ?? array();
				$value       = is_array( $value ) ? $value : array();
				echo '<div class="slos-tags-input" data-suggestions=\'' . wp_json_encode( $suggestions ) . '\'>';
				echo '<div class="slos-tags-container" id="' . esc_attr( $field_id ) . '-tags">';
				foreach ( $value as $tag ) {
					echo '<span class="slos-tag">' . esc_html( $tag );
					echo '<button type="button" class="slos-tag-remove">&times;</button></span>';
				}
				echo '</div>';
				echo '<input type="text" class="slos-tags-input-field" id="' . esc_attr( $field_id ) . '" ';
				echo 'placeholder="' . esc_attr( $placeholder ) . '" autocomplete="off">';
				echo '<input type="hidden" name="' . esc_attr( $field_name ) . '" ';
				echo 'id="' . esc_attr( $field_id ) . '-value" ';
				echo 'value="' . esc_attr( implode( ',', $value ) ) . '">';
				echo '<div class="slos-tags-suggestions"></div>';
				echo '</div>';
				break;

			case 'cookie_list':
				$value   = is_array( $value ) ? $value : array();
				$default = $field['default'] ?? array();
				if ( empty( $value ) && ! empty( $default ) ) {
					$value = $default;
				}
				echo '<div class="slos-cookie-list" id="' . esc_attr( $field_id ) . '-list" ';
				echo 'data-field="' . esc_attr( $field_name ) . '">';
				echo '<table class="slos-cookie-table">';
				echo '<thead><tr>';
				echo '<th>' . esc_html__( 'Cookie Name', 'shahi-legalflowsuite' ) . '</th>';
				echo '<th>' . esc_html__( 'Purpose', 'shahi-legalflowsuite' ) . '</th>';
				echo '<th>' . esc_html__( 'Duration', 'shahi-legalflowsuite' ) . '</th>';
				echo '<th></th>';
				echo '</tr></thead>';
				echo '<tbody>';
				foreach ( $value as $idx => $cookie ) {
					$this->render_cookie_row( $field_name, $idx, $cookie );
				}
				echo '</tbody>';
				echo '</table>';
				echo '<button type="button" class="slos-btn slos-btn-sm slos-btn-secondary slos-add-cookie">';
				echo '<span class="dashicons dashicons-plus-alt"></span> ';
				echo esc_html__( 'Add Cookie', 'shahi-legalflowsuite' );
				echo '</button>';
				echo '</div>';
				break;

			default:
				echo '<input type="text" id="' . esc_attr( $field_id ) . '" ';
				echo 'name="' . esc_attr( $field_name ) . '" ';
				echo 'value="' . esc_attr( $value ?? '' ) . '" ';
				echo 'placeholder="' . esc_attr( $placeholder ) . '" ';
				echo 'class="slos-field-input" ';
				if ( $required ) {
					echo 'required ';
				}
				echo '>';
		}
	}

	/**
	 * Render a cookie row
	 *
	 * @since 4.1.0
	 * @param string $field_name Field name.
	 * @param int    $index      Row index.
	 * @param array  $cookie     Cookie data.
	 * @return void
	 */
	protected function render_cookie_row( string $field_name, int $index, array $cookie ): void {
		echo '<tr class="slos-cookie-row" data-index="' . esc_attr( $index ) . '">';
		echo '<td><input type="text" name="' . esc_attr( $field_name ) . '[' . esc_attr( $index ) . '][name]" ';
		echo 'value="' . esc_attr( $cookie['name'] ?? '' ) . '" ';
		echo 'class="slos-field-input" placeholder="cookie_name"></td>';
		echo '<td><input type="text" name="' . esc_attr( $field_name ) . '[' . esc_attr( $index ) . '][purpose]" ';
		echo 'value="' . esc_attr( $cookie['purpose'] ?? '' ) . '" ';
		echo 'class="slos-field-input" placeholder="' . esc_attr__( 'Purpose description', 'shahi-legalflowsuite' ) . '"></td>';
		echo '<td><input type="text" name="' . esc_attr( $field_name ) . '[' . esc_attr( $index ) . '][duration]" ';
		echo 'value="' . esc_attr( $cookie['duration'] ?? '' ) . '" ';
		echo 'class="slos-field-input" placeholder="' . esc_attr__( 'Session, 1 year, etc.', 'shahi-legalflowsuite' ) . '"></td>';
		echo '<td><button type="button" class="slos-btn slos-btn-sm slos-btn-danger slos-remove-cookie">';
		echo '<span class="dashicons dashicons-trash"></span></button></td>';
		echo '</tr>';
	}

	/**
	 * Check field condition
	 *
	 * @since 4.1.0
	 * @param array      $condition Condition definition.
	 * @param array|null $profile   Profile data.
	 * @return bool Whether condition is met
	 */
	protected function check_field_condition( array $condition, ?array $profile ): bool {
		if ( ! $profile ) {
			return true;
		}

		foreach ( $condition as $field_path => $expected_value ) {
			$actual_value = $this->get_nested_value( $profile, $field_path );
			if ( $actual_value != $expected_value ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Build completion data array for template
	 *
	 * @since 4.1.0
	 * @param array|null $profile Profile data.
	 * @return array Completion data with percentage and steps
	 */
	protected function build_completion_data( ?array $profile ): array {
		$percentage      = $this->validator->calculate_completion( $profile );
		$completion_data = $this->validator->get_completion_details( $profile );

		$steps = array();
		foreach ( $completion_data as $step_num => $step_data ) {
			$steps[ $step_num ] = array(
				'is_valid'   => ! empty( $step_data['is_complete'] ),
				'percentage' => $step_data['percentage'] ?? 0,
				'completed'  => $step_data['completed'] ?? 0,
				'total'      => $step_data['total'] ?? 0,
			);
		}

		return array(
			'percentage' => $percentage,
			'steps'      => $steps,
		);
	}

	/**
	 * Get current step from URL or saved position
	 *
	 * @since 4.1.0
	 * @return int Current step number
	 */
	protected function get_current_step(): int {
		// Check URL parameter first
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['step'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$step = absint( $_GET['step'] );
			if ( $step >= 1 && $step <= 11 ) {
				return $step;
			}
		}

		// Fall back to saved position or step 1
		$profile = $this->repository->get_profile();
		if ( $profile && ! empty( $profile['_wizard_step'] ) ) {
			return absint( $profile['_wizard_step'] );
		}

		return 1;
	}

	/**
	 * AJAX: Get full profile data
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_get_profile(): void {
		check_ajax_referer( 'slos_profile_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalflowsuite' ) ) );
		}

		$profile    = $this->repository->get_profile();
		$completion = $this->validator->calculate_completion( $profile );
		$validation = $this->validator->validate_for_generation( $profile );

		wp_send_json_success(
			array(
				'profile'    => $profile,
				'completion' => $completion,
				'validation' => $validation,
				'steps'      => $this->get_steps_status( $profile ),
			)
		);
	}

	/**
	 * AJAX: Save a wizard step
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_save_step(): void {
		check_ajax_referer( 'slos_profile_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalflowsuite' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$step_number = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$step_data = isset( $_POST['data'] ) ? $this->sanitize_step_data( wp_unslash( $_POST['data'] ) ) : array();

		if ( $step_number < 1 || $step_number > 11 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid step number.', 'shahi-legalflowsuite' ) ) );
		}

		// Get current profile
		$profile = $this->repository->get_profile();
		if ( ! $profile ) {
			$profile = Migration_Company_Profile::get_default_profile_structure();
		}

		// Get step definition
		$steps    = $this->profile_service->get_steps();
		$step_def = $steps[ $step_number ] ?? null;

		if ( ! $step_def ) {
			wp_send_json_error( array( 'message' => __( 'Step not found.', 'shahi-legalflowsuite' ) ) );
		}

		// Validate step data
		$validation_result = $this->validator->validate_step( $step_number, $step_data, $profile );
		$is_valid          = ( true === $validation_result );
		$errors            = array();
		if ( is_wp_error( $validation_result ) ) {
			$errors = $validation_result->get_error_data()['errors'] ?? array();
		}

		// Merge step data into profile
		$profile = $this->merge_step_data( $profile, $step_data, $step_def['key'] );

		// Update wizard position
		$profile['_wizard_step'] = $step_number + 1;

		// Save profile
		$result = $this->repository->save_profile( $profile, true );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to save profile.', 'shahi-legalflowsuite' ) ) );
		}

		// Recalculate completion
		$completion_percentage = $this->validator->calculate_completion( $profile );

		// Increment profile version
		$version = (int) get_option( 'slos_profile_version', 0 );
		update_option( 'slos_profile_version', $version + 1 );
		update_option( 'slos_profile_last_updated', current_time( 'mysql' ) );

		wp_send_json_success(
			array(
				'message'    => __( 'Step saved successfully.', 'shahi-legalflowsuite' ),
				'completion' => array(
					'percentage' => $completion_percentage,
				),
				'step_valid' => $is_valid,
				'errors'     => $errors,
				'next_step'  => min( $step_number + 1, 11 ),
			)
		);
	}

	/**
	 * AJAX: Get specific step data
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_get_step(): void {
		check_ajax_referer( 'slos_profile_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalflowsuite' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$step_number = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;

		$steps    = $this->profile_service->get_steps();
		$step_def = $steps[ $step_number ] ?? null;

		if ( ! $step_def ) {
			wp_send_json_error( array( 'message' => __( 'Step not found.', 'shahi-legalflowsuite' ) ) );
		}

		$profile   = $this->repository->get_profile();
		$step_data = $this->extract_step_data( $profile, $step_def['key'] );

		wp_send_json_success(
			array(
				'step'   => $step_def,
				'data'   => $step_data,
				'fields' => $this->prepare_fields_for_js( $step_def['fields'] ),
			)
		);
	}

	/**
	 * AJAX: Validate entire profile
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_validate_profile(): void {
		check_ajax_referer( 'slos_profile_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalflowsuite' ) ) );
		}

		$profile    = $this->repository->get_profile();
		$validation = $this->validator->validate_for_generation( $profile );
		$completion = $this->validator->calculate_completion( $profile );

		$is_valid       = ( true === $validation );
		$missing_fields = array();

		if ( is_wp_error( $validation ) ) {
			$missing_fields = $validation->get_error_data()['missing'] ?? array();
		}

		wp_send_json_success(
			array(
				'is_valid'       => $is_valid,
				'missing_fields' => $missing_fields,
				'completion'     => $completion,
			)
		);
	}

	/**
	 * AJAX: Get completion percentage
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_get_completion(): void {
		check_ajax_referer( 'slos_profile_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalflowsuite' ) ) );
		}

		$profile    = $this->repository->get_profile();
		$completion = $this->validator->calculate_completion( $profile );

		wp_send_json_success(
			array(
				'completion' => $completion,
				'steps'      => $this->get_steps_status( $profile ),
			)
		);
	}

	/**
	 * AJAX: Reset profile to defaults
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_reset_profile(): void {
		check_ajax_referer( 'slos_profile_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalflowsuite' ) ) );
		}

		$default_profile = Migration_Company_Profile::get_default_profile_structure();
		$result          = $this->repository->save_profile( $default_profile, true );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to reset profile.', 'shahi-legalflowsuite' ) ) );
		}

		// Increment version
		$version = (int) get_option( 'slos_profile_version', 0 );
		update_option( 'slos_profile_version', $version + 1 );

		wp_send_json_success(
			array(
				'message' => __( 'Profile reset to defaults.', 'shahi-legalflowsuite' ),
				'profile' => $default_profile,
			)
		);
	}

	/**
	 * Sanitize step data
	 *
	 * @since 4.1.0
	 * @param array $data Raw step data.
	 * @return array Sanitized data
	 */
	protected function sanitize_step_data( $data ): array {
		if ( ! is_array( $data ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $data as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_step_data( $value );
			} elseif ( is_string( $value ) ) {
				// Check for specific field types
				if ( strpos( $key, 'email' ) !== false ) {
					$sanitized[ $key ] = sanitize_email( $value );
				} elseif ( strpos( $key, 'url' ) !== false ) {
					$sanitized[ $key ] = esc_url_raw( $value );
				} elseif ( strpos( $key, 'description' ) !== false || strpos( $key, 'policy' ) !== false ) {
					$sanitized[ $key ] = wp_kses_post( $value );
				} else {
					$sanitized[ $key ] = sanitize_text_field( $value );
				}
			} elseif ( is_numeric( $value ) ) {
				$sanitized[ $key ] = is_float( $value ) ? (float) $value : (int) $value;
			} elseif ( is_bool( $value ) ) {
				$sanitized[ $key ] = (bool) $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Merge step data into profile
	 *
	 * @since 4.1.0
	 * @param array  $profile  Current profile.
	 * @param array  $data     Step data.
	 * @param string $step_key Step key (e.g., 'company', 'contacts').
	 * @return array Updated profile
	 */
	protected function merge_step_data( array $profile, array $data, string $step_key ): array {
		// Handle nested data structure
		foreach ( $data as $field_path => $value ) {
			$this->set_nested_value( $profile, $field_path, $value );
		}

		return $profile;
	}

	/**
	 * Set nested value in array using dot notation
	 *
	 * @since 4.1.0
	 * @param array  $array Array to modify.
	 * @param string $path  Dot notation path.
	 * @param mixed  $value Value to set.
	 * @return void
	 */
	protected function set_nested_value( array &$array, string $path, $value ): void {
		$keys = explode( '.', $path );
		$temp = &$array;

		foreach ( $keys as $key ) {
			if ( ! isset( $temp[ $key ] ) || ! is_array( $temp[ $key ] ) ) {
				$temp[ $key ] = array();
			}
			$temp = &$temp[ $key ];
		}

		$temp = $value;
	}

	/**
	 * Extract step data from profile
	 *
	 * @since 4.1.0
	 * @param array  $profile  Profile data.
	 * @param string $step_key Step key.
	 * @return array Step data
	 */
	protected function extract_step_data( ?array $profile, string $step_key ): array {
		if ( ! $profile ) {
			return array();
		}

		// Get step definition to know which fields to extract
		$steps = $this->profile_service->get_steps();
		foreach ( $steps as $step ) {
			if ( $step['key'] === $step_key ) {
				$data = array();
				foreach ( array_keys( $step['fields'] ) as $field_path ) {
					$data[ $field_path ] = $this->get_nested_value( $profile, $field_path );
				}
				return $data;
			}
		}

		return $profile[ $step_key ] ?? array();
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
		$keys = explode( '.', $path );

		foreach ( $keys as $key ) {
			if ( ! is_array( $array ) || ! isset( $array[ $key ] ) ) {
				return null;
			}
			$array = $array[ $key ];
		}

		return $array;
	}

	/**
	 * Get steps completion status
	 *
	 * @since 4.1.0
	 * @param array|null $profile Profile data.
	 * @return array Steps status
	 */
	protected function get_steps_status( ?array $profile ): array {
		$steps           = $this->profile_service->get_steps();
		$completion_data = $this->validator->get_completion_details( $profile );
		$steps_status    = array();

		foreach ( $steps as $num => $step ) {
			$step_completion      = $completion_data[ $num ] ?? array();
			$steps_status[ $num ] = array(
				'key'        => $step['key'],
				'title'      => $step['title'],
				'is_valid'   => ! empty( $step_completion['is_complete'] ),
				'completion' => $step_completion['percentage'] ?? 0,
				'errors'     => $step_completion['missing'] ?? array(),
			);
		}

		return $steps_status;
	}

	/**
	 * Prepare field definitions for JavaScript
	 *
	 * @since 4.1.0
	 * @param array $fields Field definitions.
	 * @return array Prepared fields
	 */
	protected function prepare_fields_for_js( array $fields ): array {
		$prepared = array();

		foreach ( $fields as $path => $field ) {
			$field['path'] = $path;

			// Handle dynamic options
			if ( isset( $field['options'] ) && 'countries' === $field['options'] ) {
				$field['options'] = Migration_Company_Profile::get_countries();
			}

			$prepared[ $path ] = $field;
		}

		return $prepared;
	}
}
