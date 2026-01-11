<?php
/**
 * DSR Form Shortcode
 *
 * Renders public-facing DSR (Data Subject Request) submission form.
 * Supports all 7 GDPR rights with email verification, SLA notices, and accessibility.
 *
 * Shortcode: [slos_dsr_form]
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Shortcodes
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Shortcodes;

use ShahiLegalFlowSuite\Core\I18n;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Form_Shortcode Class
 *
 * Handles [slos_dsr_form] shortcode rendering and asset management.
 *
 * @since 3.0.1
 */
class DSR_Form_Shortcode {

	/**
	 * Text domain constant
	 *
	 * @since 3.0.1
	 * @var string
	 */
	const TEXT_DOMAIN = I18n::TEXT_DOMAIN;

	/**
	 * Initialize shortcode
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function init() {
		add_shortcode( 'slos_dsr_form', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode output
	 *
	 * Enqueues necessary assets and returns DSR form HTML.
	 *
	 * @since 3.0.1
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render( $atts = array() ) {
		// Load DSR settings for defaults
		$settings = $this->get_dsr_settings();

		// Parse attributes
		$atts = shortcode_atts(
			array(
				'show_upload'        => ! empty( $settings['allow_identity_upload'] ) ? 'true' : 'false',
				'theme'              => 'light',
				'compact'            => 'false',
				'privacy_link'       => ! empty( $settings['privacy_policy_url'] ) ? $settings['privacy_policy_url'] : get_privacy_policy_url(),
				'default_regulation' => ! empty( $settings['default_regulation'] ) ? $settings['default_regulation'] : 'GDPR',
			),
			$atts,
			'slos_dsr_form'
		);

		// Enqueue assets
		$this->enqueue_assets( $atts );

		// Build container classes
		$classes = array( 'slos-dsr-form-wrapper' );
		if ( 'dark' === $atts['theme'] ) {
			$classes[] = 'slos-theme-dark';
		}
		if ( 'true' === $atts['compact'] ) {
			$classes[] = 'slos-compact';
		}

		// Get request types with labels
		$request_types = $this->get_request_types();

		// Get regulations with labels
		$regulations = $this->get_regulations();

		// Build form HTML
		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" 
			data-api-url="<?php echo esc_url( rest_url( 'slos/v1/dsr' ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
			data-show-upload="<?php echo esc_attr( $atts['show_upload'] ); ?>"
			data-default-regulation="<?php echo esc_attr( $atts['default_regulation'] ); ?>"
			data-sla-gdpr="<?php echo esc_attr( $settings['sla_gdpr'] ?? 30 ); ?>"
			data-sla-ccpa="<?php echo esc_attr( $settings['sla_ccpa'] ?? 45 ); ?>"
			data-sla-lgpd="<?php echo esc_attr( $settings['sla_lgpd'] ?? 15 ); ?>">

			<div class="slos-dsr-form-container">
				<!-- Success Message (hidden by default) -->
				<div class="slos-dsr-success" style="display: none;" role="alert" aria-live="polite">
					<div class="slos-dsr-success-icon">✓</div>
					<div class="slos-dsr-success-content">
						<h3><?php esc_html_e( 'Request Submitted Successfully', 'shahi-legalflowsuite' ); ?></h3>
						<p><?php esc_html_e( 'Please check your email to verify your request. We\'ve sent a verification link to your email address.', 'shahi-legalflowsuite' ); ?></p>
						<p class="slos-dsr-sla-notice"></p>
					</div>
				</div>

				<!-- Error Message (hidden by default) -->
				<div class="slos-dsr-error" style="display: none;" role="alert" aria-live="assertive">
					<div class="slos-dsr-error-icon">✗</div>
					<div class="slos-dsr-error-content">
						<h3><?php esc_html_e( 'Submission Error', 'shahi-legalflowsuite' ); ?></h3>
						<p class="slos-dsr-error-message"></p>
					</div>
				</div>

				<!-- Form Header -->
				<div class="slos-dsr-form-header">
					<h2><?php esc_html_e( 'Data Subject Request Form', 'shahi-legalflowsuite' ); ?></h2>
					<p class="slos-dsr-form-description">
						<?php esc_html_e( 'Submit a request to access, correct, delete, or manage your personal data. All requests are processed in accordance with applicable privacy regulations.', 'shahi-legalflowsuite' ); ?>
					</p>
				</div>

				<!-- Form -->
				<form id="slos-dsr-form" class="slos-dsr-form" novalidate>
					<!-- Full Name -->
					<div class="slos-form-field">
						<label for="slos-dsr-name" class="slos-form-label">
							<?php esc_html_e( 'Full Name', 'shahi-legalflowsuite' ); ?>
							<span class="slos-required" aria-label="<?php esc_attr_e( 'Required', 'shahi-legalflowsuite' ); ?>">*</span>
						</label>
						<input 
							type="text" 
							id="slos-dsr-name" 
							name="name" 
							class="slos-form-input"
							required
							aria-required="true"
							aria-describedby="slos-dsr-name-error"
							placeholder="<?php esc_attr_e( 'Enter your full name', 'shahi-legalflowsuite' ); ?>">
						<div id="slos-dsr-name-error" class="slos-field-error" role="alert"></div>
					</div>

					<!-- Email Address -->
					<div class="slos-form-field">
						<label for="slos-dsr-email" class="slos-form-label">
							<?php esc_html_e( 'Email Address', 'shahi-legalflowsuite' ); ?>
							<span class="slos-required" aria-label="<?php esc_attr_e( 'Required', 'shahi-legalflowsuite' ); ?>">*</span>
						</label>
						<input 
							type="email" 
							id="slos-dsr-email" 
							name="email" 
							class="slos-form-input"
							required
							aria-required="true"
							aria-describedby="slos-dsr-email-error slos-dsr-email-help"
							placeholder="<?php esc_attr_e( 'your.email@example.com', 'shahi-legalflowsuite' ); ?>">
						<p id="slos-dsr-email-help" class="slos-field-help">
							<?php esc_html_e( 'We will send a verification link to this email address.', 'shahi-legalflowsuite' ); ?>
						</p>
						<div id="slos-dsr-email-error" class="slos-field-error" role="alert"></div>
					</div>

					<!-- Request Type -->
					<div class="slos-form-field">
						<label for="slos-dsr-type" class="slos-form-label">
							<?php esc_html_e( 'Request Type', 'shahi-legalflowsuite' ); ?>
							<span class="slos-required" aria-label="<?php esc_attr_e( 'Required', 'shahi-legalflowsuite' ); ?>">*</span>
						</label>
						<select 
							id="slos-dsr-type" 
							name="request_type" 
							class="slos-form-select"
							required
							aria-required="true"
							aria-describedby="slos-dsr-type-error slos-dsr-type-help">
							<option value=""><?php esc_html_e( 'Select a request type...', 'shahi-legalflowsuite' ); ?></option>
							<?php foreach ( $request_types as $type_key => $type_data ) : ?>
								<option value="<?php echo esc_attr( $type_key ); ?>">
									<?php echo esc_html( $type_data['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p id="slos-dsr-type-help" class="slos-field-help slos-request-type-description"></p>
						<div id="slos-dsr-type-error" class="slos-field-error" role="alert"></div>
					</div>

					<!-- Regulation -->
					<div class="slos-form-field">
						<label for="slos-dsr-regulation" class="slos-form-label">
							<?php esc_html_e( 'Applicable Regulation', 'shahi-legalflowsuite' ); ?>
						</label>
						<select 
							id="slos-dsr-regulation" 
							name="regulation" 
							class="slos-form-select"
							aria-describedby="slos-dsr-regulation-help">
							<?php foreach ( $regulations as $reg_key => $reg_data ) : ?>
								<option value="<?php echo esc_attr( $reg_key ); ?>" <?php selected( $reg_key, $atts['default_regulation'] ); ?>>
									<?php echo esc_html( $reg_data['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p id="slos-dsr-regulation-help" class="slos-field-help">
							<?php esc_html_e( 'Select the privacy regulation that applies to you. This determines the processing timeline.', 'shahi-legalflowsuite' ); ?>
						</p>
					</div>

					<!-- Additional Details -->
					<div class="slos-form-field">
						<label for="slos-dsr-details" class="slos-form-label">
							<?php esc_html_e( 'Additional Details', 'shahi-legalflowsuite' ); ?>
							<span class="slos-optional"><?php esc_html_e( '(Optional)', 'shahi-legalflowsuite' ); ?></span>
						</label>
						<textarea 
							id="slos-dsr-details" 
							name="details" 
							class="slos-form-textarea"
							rows="4"
							maxlength="5000"
							aria-describedby="slos-dsr-details-help"
							placeholder="<?php esc_attr_e( 'Provide any additional information that will help us process your request...', 'shahi-legalflowsuite' ); ?>"></textarea>
						<p id="slos-dsr-details-help" class="slos-field-help">
							<?php esc_html_e( 'You may provide additional context or specific details about your request (maximum 5000 characters).', 'shahi-legalflowsuite' ); ?>
						</p>
						<div class="slos-character-count">
							<span class="slos-char-current">0</span> / 5000
						</div>
					</div>

					<!-- Identity Upload (conditional) -->
					<?php if ( 'true' === $atts['show_upload'] ) : ?>
					<div class="slos-form-field">
						<label for="slos-dsr-identity" class="slos-form-label">
							<?php esc_html_e( 'Identity Verification Document', 'shahi-legalflowsuite' ); ?>
							<span class="slos-optional"><?php esc_html_e( '(Optional)', 'shahi-legalflowsuite' ); ?></span>
						</label>
						<input 
							type="file" 
							id="slos-dsr-identity" 
							name="identity_document" 
							class="slos-form-file"
							accept=".pdf,.jpg,.jpeg,.png"
							aria-describedby="slos-dsr-identity-help">
						<p id="slos-dsr-identity-help" class="slos-field-help">
							<?php esc_html_e( 'Upload a government-issued ID or proof of identity (PDF, JPG, PNG, max 5MB). This helps us verify your identity faster.', 'shahi-legalflowsuite' ); ?>
						</p>
					</div>
					<?php endif; ?>

					<!-- Identity Attestation -->
					<div class="slos-form-field slos-form-checkbox-field">
						<label class="slos-checkbox-label">
							<input 
								type="checkbox" 
								id="slos-dsr-attestation" 
								name="attestation" 
								class="slos-form-checkbox"
								required
								aria-required="true"
								aria-describedby="slos-dsr-attestation-error">
							<span class="slos-checkbox-text">
								<?php
								printf(
									/* translators: %s: Privacy policy link */
									esc_html__( 'I attest that I am the data subject or authorized representative, and I have read the %s.', 'shahi-legalflowsuite' ),
									sprintf(
										'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
										esc_url( $atts['privacy_link'] ),
										esc_html__( 'Privacy Policy', 'shahi-legalflowsuite' )
									)
								);
								?>
								<span class="slos-required" aria-label="<?php esc_attr_e( 'Required', 'shahi-legalflowsuite' ); ?>">*</span>
							</span>
						</label>
						<div id="slos-dsr-attestation-error" class="slos-field-error" role="alert"></div>
					</div>

					<!-- SLA Notice -->
					<div class="slos-dsr-sla-info">
						<div class="slos-dsr-sla-icon">ℹ</div>
						<div class="slos-dsr-sla-text">
							<?php esc_html_e( 'Your request will be processed according to applicable data protection regulations. Expected response time: ', 'shahi-legalflowsuite' ); ?>
							<strong class="slos-sla-days"></strong>
						</div>
					</div>

					<!-- Submit Button -->
					<div class="slos-form-actions">
						<button 
							type="submit" 
							id="slos-dsr-submit" 
							class="slos-btn slos-btn-primary"
							aria-busy="false">
							<span class="slos-btn-text"><?php esc_html_e( 'Submit Request', 'shahi-legalflowsuite' ); ?></span>
							<span class="slos-btn-spinner" style="display: none;">
								<span class="slos-spinner"></span>
								<?php esc_html_e( 'Submitting...', 'shahi-legalflowsuite' ); ?>
							</span>
						</button>
					</div>

					<!-- Security Notice -->
					<div class="slos-dsr-security-notice">
						<p>
							<?php esc_html_e( 'Your request is encrypted and will be processed securely. Your IP address is hashed for security purposes.', 'shahi-legalflowsuite' ); ?>
						</p>
					</div>
				</form>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue shortcode assets
	 *
	 * @since 3.0.1
	 * @param array $atts Shortcode attributes
	 * @return void
	 */
	private function enqueue_assets( $atts ) {
		$version = defined( 'SLOS_VERSION' ) ? SLOS_VERSION : '3.0.1';

		// Enqueue CSS
		wp_enqueue_style(
			'slos-dsr-form',
			plugins_url( 'assets/css/dsr-form.css', dirname( __DIR__ ) ),
			array(),
			$version
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'slos-dsr-form',
			plugins_url( 'assets/js/dsr-form.js', dirname( __DIR__ ) ),
			array( 'jquery' ),
			$version,
			true
		);

		// Localize script with translations and settings
		wp_localize_script(
			'slos-dsr-form',
			'slosDsrForm',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'restUrl'      => rest_url( 'slos/v1/dsr' ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'requestTypes' => $this->get_request_types(),
				'regulations'  => $this->get_regulations(),
				'i18n'         => array(
					'nameRequired'        => __( 'Please enter your full name.', 'shahi-legalflowsuite' ),
					'emailRequired'       => __( 'Please enter your email address.', 'shahi-legalflowsuite' ),
					'emailInvalid'        => __( 'Please enter a valid email address.', 'shahi-legalflowsuite' ),
					'typeRequired'        => __( 'Please select a request type.', 'shahi-legalflowsuite' ),
					'attestationRequired' => __( 'You must attest that you are the data subject or authorized representative.', 'shahi-legalflowsuite' ),
					'submitting'          => __( 'Submitting...', 'shahi-legalflowsuite' ),
					'submitButton'        => __( 'Submit Request', 'shahi-legalflowsuite' ),
					'successTitle'        => __( 'Request Submitted Successfully', 'shahi-legalflowsuite' ),
					'successMessage'      => __( 'Please check your email to verify your request.', 'shahi-legalflowsuite' ),
					'errorTitle'          => __( 'Submission Error', 'shahi-legalflowsuite' ),
					'errorGeneric'        => __( 'An error occurred while submitting your request. Please try again.', 'shahi-legalflowsuite' ),
					'rateLimitError'      => __( 'You have submitted too many requests. Please try again later.', 'shahi-legalflowsuite' ),
					'networkError'        => __( 'Network error. Please check your connection and try again.', 'shahi-legalflowsuite' ),
					/* translators: %s: number of business days for response */
					'slaNotice'           => __( 'We will respond within %s business days.', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Get DSR settings
	 *
	 * @since 3.0.1
	 * @return array DSR settings
	 */
	private function get_dsr_settings() {
		$defaults = array(
			'allow_identity_upload' => false,
			'privacy_policy_url'    => get_privacy_policy_url(),
			'default_regulation'    => 'GDPR',
			'sla_gdpr'              => 30,
			'sla_ccpa'              => 45,
			'sla_lgpd'              => 15,
			'sla_uk_gdpr'           => 30,
			'sla_pipeda'            => 30,
			'sla_popia'             => 30,
		);

		// Attempt to load from options
		$settings = get_option( 'slos_dsr_settings', array() );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get request types with labels and descriptions
	 *
	 * @since 3.0.1
	 * @return array Request types
	 */
	private function get_request_types() {
		return array(
			'access'             => array(
				'label'       => __( 'Access My Data', 'shahi-legalflowsuite' ),
				'description' => __( 'Request a copy of the personal data we hold about you (GDPR Article 15).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 15',
			),
			'rectification'      => array(
				'label'       => __( 'Correct My Data', 'shahi-legalflowsuite' ),
				'description' => __( 'Request correction of inaccurate or incomplete personal data (GDPR Article 16).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 16',
			),
			'erasure'            => array(
				'label'       => __( 'Delete My Data', 'shahi-legalflowsuite' ),
				'description' => __( 'Request deletion of your personal data (Right to be Forgotten, GDPR Article 17).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 17',
			),
			'portability'        => array(
				'label'       => __( 'Export My Data', 'shahi-legalflowsuite' ),
				'description' => __( 'Request your data in a portable, machine-readable format (GDPR Article 20).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 20',
			),
			'restriction'        => array(
				'label'       => __( 'Restrict Processing', 'shahi-legalflowsuite' ),
				'description' => __( 'Request restriction of how we process your personal data (GDPR Article 18).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 18',
			),
			'object'             => array(
				'label'       => __( 'Object to Processing', 'shahi-legalflowsuite' ),
				'description' => __( 'Object to the processing of your personal data (GDPR Article 21).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 21',
			),
			'automated_decision' => array(
				'label'       => __( 'Review Automated Decision', 'shahi-legalflowsuite' ),
				'description' => __( 'Request human review of automated decisions affecting you (GDPR Article 22).', 'shahi-legalflowsuite' ),
				'article'     => 'GDPR Article 22',
			),
		);
	}

	/**
	 * Get regulations with labels and SLA
	 *
	 * @since 3.0.1
	 * @return array Regulations
	 */
	private function get_regulations() {
		$settings = $this->get_dsr_settings();

		return array(
			'GDPR'    => array(
				'label'    => __( 'GDPR (European Union)', 'shahi-legalflowsuite' ),
				'sla_days' => $settings['sla_gdpr'],
			),
			'CCPA'    => array(
				'label'    => __( 'CCPA (California)', 'shahi-legalflowsuite' ),
				'sla_days' => $settings['sla_ccpa'],
			),
			'LGPD'    => array(
				'label'    => __( 'LGPD (Brazil)', 'shahi-legalflowsuite' ),
				'sla_days' => $settings['sla_lgpd'],
			),
			'UK-GDPR' => array(
				'label'    => __( 'UK-GDPR (United Kingdom)', 'shahi-legalflowsuite' ),
				'sla_days' => $settings['sla_uk_gdpr'],
			),
			'PIPEDA'  => array(
				'label'    => __( 'PIPEDA (Canada)', 'shahi-legalflowsuite' ),
				'sla_days' => $settings['sla_pipeda'],
			),
			'POPIA'   => array(
				'label'    => __( 'POPIA (South Africa)', 'shahi-legalflowsuite' ),
				'sla_days' => $settings['sla_popia'],
			),
		);
	}
}
