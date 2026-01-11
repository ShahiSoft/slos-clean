<?php
/**
 * Template Manager
 *
 * Manages legal document templates, placeholder resolution, auto-detection,
 * and compliance validation. Provides extensible template system with
 * integration-aware placeholder prefilling.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     4.0.0
 * @since       4.0.0
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Template_Manager
 *
 * Handles template storage, placeholder management, and compliance validation.
 *
 * @since 4.0.0
 */
class Template_Manager {

	/**
	 * Base templates storage
	 *
	 * @var array
	 */
	protected $templates = array();

	/**
	 * Constructor - initializes base templates
	 */
	public function __construct() {
		$this->initialize_templates();
	}

	/**
	 * Get template content by type and locale
	 *
	 * @param string $type   Document type
	 * @param string $locale Locale code (default: en_US)
	 * @return string Template content
	 */
	public function get_template( string $type, string $locale = 'en_US' ): string {
		// Try locale-specific template first
		if ( isset( $this->templates[ $type ][ $locale ] ) ) {
			return $this->templates[ $type ][ $locale ];
		}

		// Fallback to en_US
		if ( isset( $this->templates[ $type ]['en_US'] ) ) {
			return $this->templates[ $type ]['en_US'];
		}

		return '';
	}

	/**
	 * List all required placeholders for a template type
	 *
	 * @param string $type Template type
	 * @return array List of placeholder keys
	 */
	public function list_required_placeholders( string $type ): array {
		$template = $this->get_template( $type );
		return $this->extract_placeholders( $template );
	}

	/**
	 * Extract placeholders from content
	 *
	 * @param string $content Content to parse
	 * @return array List of unique placeholder keys
	 */
	public function extract_placeholders( string $content ): array {
		$matches = array();
		preg_match_all( '/\{\{\s*([a-zA-Z0-9_\-\.]+)\s*\}\}/', $content, $matches );
		$keys = isset( $matches[1] ) ? array_unique( $matches[1] ) : array();
		sort( $keys );
		return $keys;
	}

	/**
	 * Validate placeholders - check for missing values
	 *
	 * @param string $content  Content with placeholders
	 * @param array  $values   Placeholder values
	 * @return array List of missing placeholder keys
	 */
	public function validate_placeholders( string $content, array $values ): array {
		$required = $this->extract_placeholders( $content );
		$missing  = array();

		foreach ( $required as $key ) {
			if ( ! isset( $values[ $key ] ) || '' === $values[ $key ] ) {
				$missing[] = $key;
			}
		}

		return $missing;
	}

	/**
	 * Resolve placeholders in content with provided values
	 *
	 * @param string $content Content with placeholders
	 * @param array  $values  Placeholder values
	 * @return string Content with placeholders replaced
	 */
	public function resolve_placeholders( string $content, array $values ): string {
		foreach ( $values as $key => $value ) {
			$placeholder = '{{' . $key . '}}';
			$content     = str_replace( $placeholder, (string) $value, $content );
		}
		return $content;
	}

	/**
	 * Get all available templates
	 *
	 * @since 4.0.0
	 * @return array Associative array of template_type => template_data
	 */
	public function get_all_templates(): array {
		$template_types = array(
			'privacy-policy' => array(
				'name'        => __( 'Privacy Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Standard privacy policy document for GDPR/CCPA compliance.', 'shahi-legalflowsuite' ),
				'icon'        => 'shield',
			),
			'terms'          => array(
				'name'        => __( 'Terms of Service', 'shahi-legalflowsuite' ),
				'description' => __( 'Terms and conditions for website/service usage.', 'shahi-legalflowsuite' ),
				'icon'        => 'document',
			),
			'cookie-policy'  => array(
				'name'        => __( 'Cookie Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Cookie usage disclosure for compliance.', 'shahi-legalflowsuite' ),
				'icon'        => 'cookies',
			),
			'gdpr-addendum'  => array(
				'name'        => __( 'GDPR Addendum', 'shahi-legalflowsuite' ),
				'description' => __( 'GDPR-specific addendum for data processing.', 'shahi-legalflowsuite' ),
				'icon'        => 'flag',
			),
			'ccpa-notice'    => array(
				'name'        => __( 'CCPA Notice', 'shahi-legalflowsuite' ),
				'description' => __( 'California Consumer Privacy Act notice.', 'shahi-legalflowsuite' ),
				'icon'        => 'info',
			),
			'dpa'            => array(
				'name'        => __( 'Data Processing Agreement', 'shahi-legalflowsuite' ),
				'description' => __( 'Agreement for third-party data processors.', 'shahi-legalflowsuite' ),
				'icon'        => 'handshake',
			),
		);

		return apply_filters( 'slos_legal_document_templates', $template_types );
	}

	/**
	 * Get template by Document Hub type key
	 *
	 * Maps Document Hub type keys (e.g., 'privacy_policy') to template keys
	 * (e.g., 'privacy-policy') and retrieves the template content.
	 *
	 * @since 4.1.0
	 * @param string $hub_type_key Document Hub type key.
	 * @param string $locale       Locale code (default: en_US).
	 * @return string Template content or empty string
	 */
	public function get_template_by_hub_type( string $hub_type_key, string $locale = 'en_US' ): string {
		$type_mapping = array(
			'privacy_policy'   => 'privacy-policy',
			'terms_of_service' => 'terms',
			'cookie_policy'    => 'cookie-policy',
			'gdpr_addendum'    => 'gdpr-addendum',
			'ccpa_notice'      => 'ccpa-notice',
			'dpa'              => 'dpa',
		);

		$type_mapping = apply_filters( 'slos_hub_type_to_template_map', $type_mapping );

		$template_key = $type_mapping[ $hub_type_key ] ?? null;

		if ( ! $template_key ) {
			return '';
		}

		return $this->get_template( $template_key, $locale );
	}

	/**
	 * Check if a template exists for a given type
	 *
	 * @since 4.1.0
	 * @param string $type   Template type or hub type key.
	 * @param string $locale Locale code.
	 * @return bool Whether template exists
	 */
	public function template_exists( string $type, string $locale = 'en_US' ): bool {
		// Try direct lookup
		if ( ! empty( $this->get_template( $type, $locale ) ) ) {
			return true;
		}

		// Try hub type lookup
		if ( ! empty( $this->get_template_by_hub_type( $type, $locale ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get default placeholder values from site settings and auto-detection
	 *
	 * @param array $overrides Optional overrides (e.g., from questionnaire)
	 * @return array Placeholder key => value pairs
	 */
	public function get_default_values( array $overrides = array() ): array {
		$site_name   = get_bloginfo( 'name' );
		$site_url    = get_bloginfo( 'url' );
		$admin_email = get_bloginfo( 'admin_email' );
		$site_desc   = get_bloginfo( 'description' );

		// Auto-detect integrations
		$detected = $this->auto_detect_integrations();

		$defaults = array(
			'business_name'       => $site_name,
			'site_url'            => $site_url,
			'contact_email'       => $admin_email,
			'legal_contact'       => $admin_email,
			'dpa_contact'         => $admin_email,
			'site_description'    => $site_desc,
			'jurisdiction'        => $this->detect_jurisdiction(),
			'payment_processors'  => $this->detect_payment_processors( $detected ),
			'analytics_providers' => $this->detect_analytics_providers( $detected ),
			'form_plugins'        => $this->detect_form_plugins( $detected ),
			'ads_pixels'          => $this->detect_ads_pixels( $detected ),
			'data_retention'      => '2 years',
			'cookie_duration'     => '12 months',
			'company_address'     => '',
			'dpo_name'            => '',
			'dpo_email'           => $admin_email,
		);

		// Apply extensibility filter
		$defaults = apply_filters( 'slos_legal_doc_placeholders', $defaults, $detected );

		// Merge with overrides (questionnaire answers or manual inputs)
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Auto-detect active integrations
	 *
	 * @return array Detected integration flags
	 */
	public function auto_detect_integrations(): array {
		return array(
			'woocommerce'      => class_exists( 'WooCommerce' ),
			'edd'              => class_exists( 'Easy_Digital_Downloads' ),
			'contact_form_7'   => class_exists( 'WPCF7' ) || function_exists( 'wpcf7' ),
			'wpforms'          => class_exists( 'WPForms' ),
			'gravity_forms'    => class_exists( 'GFAPI' ) || class_exists( 'GFForms' ),
			'ninja_forms'      => class_exists( 'Ninja_Forms' ),
			'forminator'       => class_exists( 'Forminator' ),
			'google_analytics' => $this->detect_google_analytics(),
			'matomo'           => class_exists( 'WP_Matomo' ) || function_exists( 'matomo_get_tracker_url' ),
			'mailchimp'        => class_exists( 'MC4WP_MailChimp' ) || class_exists( 'Mailchimp' ),
			'activecampaign'   => class_exists( 'ACTIVECAMPAIGN_PLUGIN' ),
			'convertkit'       => class_exists( 'ConvertKit_Plugin' ),
			'facebook_pixel'   => $this->detect_facebook_pixel(),
			'google_ads'       => $this->detect_google_ads(),
			'stripe'           => class_exists( 'Stripe' ) || function_exists( 'stripe_get_secret_key' ),
			'paypal'           => $this->detect_paypal(),
		);
	}

	/**
	 * Validate compliance sections for specified frameworks
	 *
	 * @param string $content    Document content
	 * @param array  $frameworks Frameworks to check (GDPR, CCPA, LGPD, etc.)
	 * @return array Validation results per framework
	 */
	public function validate_compliance( string $content, array $frameworks = array() ): array {
		if ( empty( $frameworks ) ) {
			$frameworks = array( 'GDPR', 'CCPA', 'LGPD', 'UK-GDPR', 'PIPEDA', 'POPIA' );
		}

		$results = array();

		foreach ( $frameworks as $framework ) {
			$required_sections = $this->get_required_sections( $framework );
			$missing           = array();

			foreach ( $required_sections as $section ) {
				if ( false === stripos( $content, $section ) ) {
					$missing[] = $section;
				}
			}

			$results[ $framework ] = array(
				'valid'            => empty( $missing ),
				'missing_sections' => $missing,
				'checked_at'       => current_time( 'mysql' ),
			);
		}

		return $results;
	}

	/**
	 * Get required sections for a compliance framework
	 *
	 * @param string $framework Framework name
	 * @return array Required section keywords
	 */
	protected function get_required_sections( string $framework ): array {
		$sections = array(
			'GDPR'    => array(
				'legal basis',
				'data subject rights',
				'data controller',
				'data processor',
				'data retention',
				'international transfers',
			),
			'CCPA'    => array(
				'categories of personal information',
				'right to know',
				'right to delete',
				'right to opt-out',
				'non-discrimination',
			),
			'LGPD'    => array(
				'legal basis',
				'data subject rights',
				'data controller',
				'data protection officer',
				'data retention',
			),
			'UK-GDPR' => array(
				'legal basis',
				'data subject rights',
				'data controller',
				'data retention',
				'international transfers',
			),
			'PIPEDA'  => array(
				'consent',
				'purpose',
				'limiting collection',
				'accuracy',
				'safeguards',
				'openness',
			),
			'POPIA'   => array(
				'lawful processing',
				'data subject rights',
				'information officer',
				'security safeguards',
				'cross-border transfers',
			),
		);

		return isset( $sections[ $framework ] ) ? $sections[ $framework ] : array();
	}

	/**
	 * Initialize base templates for each document type
	 */
	protected function initialize_templates(): void {
		$this->templates = array(
			'privacy-policy' => array(
				'en_US' => $this->get_privacy_policy_template(),
			),
			'terms'          => array(
				'en_US' => $this->get_terms_template(),
			),
			'cookie-policy'  => array(
				'en_US' => $this->get_cookie_policy_template(),
			),
			'gdpr-addendum'  => array(
				'en_US' => $this->get_gdpr_addendum_template(),
			),
			'ccpa-notice'    => array(
				'en_US' => $this->get_ccpa_notice_template(),
			),
			'dpa'            => array(
				'en_US' => $this->get_dpa_template(),
			),
		);
	}

	/**
	 * Privacy Policy template
	 */
	protected function get_privacy_policy_template(): string {
		return <<<'HTML'
<h1>Privacy Policy</h1>

<p><strong>Effective Date:</strong> {{effective_date}}</p>

<h2>1. Introduction</h2>
<p>{{business_name}} ("we," "us," or "our") operates {{site_url}}. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>

<h2>2. Data Controller</h2>
<p>The data controller responsible for your personal data is:</p>
<ul>
<li><strong>Company:</strong> {{business_name}}</li>
<li><strong>Address:</strong> {{company_address}}</li>
<li><strong>Contact:</strong> {{legal_contact}}</li>
<li><strong>DPO:</strong> {{dpo_name}} ({{dpo_email}})</li>
</ul>

<h2>3. Information We Collect</h2>
<p>We may collect information about you in a variety of ways, including:</p>
<ul>
<li><strong>Personal Data:</strong> Name, email address, contact information</li>
<li><strong>Usage Data:</strong> IP address, browser type, pages visited</li>
<li><strong>Cookies:</strong> We use cookies to enhance user experience</li>
</ul>

<h2>4. Legal Basis for Processing</h2>
<p>Under GDPR, we process your personal data based on the following legal basis:</p>
<ul>
<li>Consent for marketing communications</li>
<li>Contractual necessity for order processing</li>
<li>Legitimate interest for analytics and security</li>
<li>Legal obligation for tax and compliance</li>
</ul>

<h2>5. Third-Party Services</h2>
<p>We work with the following third-party services:</p>
<ul>
<li><strong>Payment Processors:</strong> {{payment_processors}}</li>
<li><strong>Analytics:</strong> {{analytics_providers}}</li>
<li><strong>Form Plugins:</strong> {{form_plugins}}</li>
<li><strong>Advertising:</strong> {{ads_pixels}}</li>
</ul>

<h2>6. Data Subject Rights</h2>
<p>Under GDPR, CCPA, and other privacy laws, you have the following rights:</p>
<ul>
<li>Right to access your personal data</li>
<li>Right to rectification (correction)</li>
<li>Right to erasure ("right to be forgotten")</li>
<li>Right to restrict processing</li>
<li>Right to data portability</li>
<li>Right to object to processing</li>
<li>Right to opt-out of sale (CCPA)</li>
<li>Right to non-discrimination (CCPA)</li>
</ul>

<h2>7. Data Retention</h2>
<p>We retain your personal data for {{data_retention}} or as long as necessary to fulfill the purposes outlined in this policy.</p>

<h2>8. International Transfers</h2>
<p>Your data may be transferred to and processed in countries outside of {{jurisdiction}}. We ensure appropriate safeguards are in place.</p>

<h2>9. Security</h2>
<p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>

<h2>10. Contact Information</h2>
<p>For questions about this Privacy Policy or to exercise your data subject rights, contact us at:</p>
<ul>
<li><strong>Email:</strong> {{contact_email}}</li>
<li><strong>DPA Contact:</strong> {{dpa_contact}}</li>
</ul>

<h2>11. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated effective date.</p>
HTML;
	}

	/**
	 * Terms of Service template
	 */
	protected function get_terms_template(): string {
		return <<<'HTML'
<h1>Terms of Service</h1>

<p><strong>Effective Date:</strong> {{effective_date}}</p>

<h2>1. Agreement to Terms</h2>
<p>By accessing {{site_url}}, you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>

<h2>2. Company Information</h2>
<ul>
<li><strong>Business Name:</strong> {{business_name}}</li>
<li><strong>Jurisdiction:</strong> {{jurisdiction}}</li>
<li><strong>Contact:</strong> {{legal_contact}}</li>
</ul>

<h2>3. Use License</h2>
<p>Permission is granted to temporarily access the materials on {{business_name}}'s website for personal, non-commercial transitory viewing only.</p>

<h2>4. Disclaimer</h2>
<p>The materials on {{site_url}} are provided on an 'as is' basis. {{business_name}} makes no warranties, expressed or implied.</p>

<h2>5. Limitations</h2>
<p>In no event shall {{business_name}} or its suppliers be liable for any damages arising out of the use or inability to use the materials on {{site_url}}.</p>

<h2>6. Payment Terms</h2>
<p>Payment processing is handled by: {{payment_processors}}</p>

<h2>7. Contact</h2>
<p>For questions about these Terms, contact us at {{contact_email}}</p>
HTML;
	}

	/**
	 * Cookie Policy template
	 */
	protected function get_cookie_policy_template(): string {
		return <<<'HTML'
<h1>Cookie Policy</h1>

<p><strong>Effective Date:</strong> {{effective_date}}</p>

<h2>1. What Are Cookies</h2>
<p>Cookies are small text files that are placed on your device when you visit {{site_url}}.</p>

<h2>2. How We Use Cookies</h2>
<p>{{business_name}} uses cookies for:</p>
<ul>
<li>Essential website functionality</li>
<li>Analytics and performance monitoring via {{analytics_providers}}</li>
<li>Marketing and advertising via {{ads_pixels}}</li>
<li>Form functionality via {{form_plugins}}</li>
</ul>

<h2>3. Cookie Duration</h2>
<p>Cookies are stored for up to {{cookie_duration}}.</p>

<h2>4. Your Choices</h2>
<p>You can control cookies through your browser settings or our consent banner.</p>

<h2>5. Contact</h2>
<p>For questions about cookies, contact {{contact_email}}</p>
HTML;
	}

	/**
	 * GDPR Addendum template
	 */
	protected function get_gdpr_addendum_template(): string {
		return <<<'HTML'
<h1>GDPR Data Processing Addendum</h1>

<p><strong>Effective Date:</strong> {{effective_date}}</p>

<h2>1. Definitions</h2>
<p>This addendum supplements our main Privacy Policy with GDPR-specific terms.</p>

<h2>2. Data Controller</h2>
<p>{{business_name}} acts as the data controller for personal data collected via {{site_url}}.</p>

<h2>3. Legal Basis</h2>
<p>We process data under consent, contract, legitimate interest, and legal obligation.</p>

<h2>4. Data Subject Rights</h2>
<p>You have comprehensive rights including access, rectification, erasure, restriction, portability, and objection.</p>

<h2>5. Data Protection Officer</h2>
<p>Our DPO is {{dpo_name}}, contactable at {{dpo_email}}</p>

<h2>6. International Transfers</h2>
<p>Data transfers outside the EEA are protected by Standard Contractual Clauses.</p>

<h2>7. Contact</h2>
<p>For GDPR inquiries: {{dpa_contact}}</p>
HTML;
	}

	/**
	 * CCPA Notice template
	 */
	protected function get_ccpa_notice_template(): string {
		return <<<'HTML'
<h1>California Consumer Privacy Act (CCPA) Notice</h1>

<p><strong>Effective Date:</strong> {{effective_date}}</p>

<h2>1. Categories of Personal Information</h2>
<p>{{business_name}} collects the following categories:</p>
<ul>
<li>Identifiers (name, email, IP address)</li>
<li>Commercial information (purchase history)</li>
<li>Internet activity (browsing history via {{analytics_providers}})</li>
</ul>

<h2>2. Right to Know</h2>
<p>You have the right to request disclosure of personal information we collect, use, and disclose.</p>

<h2>3. Right to Delete</h2>
<p>You have the right to request deletion of your personal information.</p>

<h2>4. Right to Opt-Out</h2>
<p>We do not sell personal information. If we did, you could opt-out at {{site_url}}/ccpa-opt-out</p>

<h2>5. Non-Discrimination</h2>
<p>We will not discriminate against you for exercising your CCPA rights.</p>

<h2>6. Contact</h2>
<p>To exercise your CCPA rights, contact {{contact_email}}</p>
HTML;
	}

	/**
	 * Data Processing Agreement template
	 */
	protected function get_dpa_template(): string {
		return <<<'HTML'
<h1>Data Processing Agreement</h1>

<p><strong>Effective Date:</strong> {{effective_date}}</p>

<h2>1. Parties</h2>
<p>This Agreement is between you (Data Controller) and {{business_name}} (Data Processor).</p>

<h2>2. Scope and Purpose</h2>
<p>This DPA governs the processing of personal data in connection with services provided via {{site_url}}.</p>

<h2>3. Data Processor Obligations</h2>
<ul>
<li>Process data only on documented instructions</li>
<li>Ensure confidentiality of personnel</li>
<li>Implement appropriate security measures</li>
<li>Assist with data subject rights requests</li>
<li>Notify of personal data breaches</li>
</ul>

<h2>4. Sub-Processors</h2>
<p>Current sub-processors include: {{payment_processors}}, {{analytics_providers}}</p>

<h2>5. Data Retention</h2>
<p>Personal data will be retained for {{data_retention}} unless otherwise instructed.</p>

<h2>6. International Transfers</h2>
<p>Transfers outside {{jurisdiction}} are protected by Standard Contractual Clauses.</p>

<h2>7. Contact</h2>
<p>DPA inquiries: {{dpa_contact}}</p>
HTML;
	}

	/*
	============================= */
	/*
	Detection Helpers             */
	/* ============================= */

	/**
	 * Detect jurisdiction from site locale/settings
	 */
	protected function detect_jurisdiction(): string {
		$locale = get_locale();

		$jurisdictions = array(
			'en_US' => 'United States',
			'en_GB' => 'United Kingdom',
			'de_DE' => 'Germany',
			'fr_FR' => 'France',
			'es_ES' => 'Spain',
			'it_IT' => 'Italy',
			'pt_BR' => 'Brazil',
			'en_CA' => 'Canada',
			'en_AU' => 'Australia',
		);

		return isset( $jurisdictions[ $locale ] ) ? $jurisdictions[ $locale ] : 'your jurisdiction';
	}

	/**
	 * Detect payment processors
	 */
	protected function detect_payment_processors( array $detected ): string {
		$processors = array();

		if ( ! empty( $detected['woocommerce'] ) ) {
			$processors[] = 'WooCommerce';
		}
		if ( ! empty( $detected['edd'] ) ) {
			$processors[] = 'Easy Digital Downloads';
		}
		if ( ! empty( $detected['stripe'] ) ) {
			$processors[] = 'Stripe';
		}
		if ( ! empty( $detected['paypal'] ) ) {
			$processors[] = 'PayPal';
		}

		return ! empty( $processors ) ? implode( ', ', $processors ) : 'None';
	}

	/**
	 * Detect analytics providers
	 */
	protected function detect_analytics_providers( array $detected ): string {
		$providers = array();

		if ( ! empty( $detected['google_analytics'] ) ) {
			$providers[] = 'Google Analytics';
		}
		if ( ! empty( $detected['matomo'] ) ) {
			$providers[] = 'Matomo';
		}

		return ! empty( $providers ) ? implode( ', ', $providers ) : 'None';
	}

	/**
	 * Detect form plugins
	 */
	protected function detect_form_plugins( array $detected ): string {
		$forms = array();

		if ( ! empty( $detected['contact_form_7'] ) ) {
			$forms[] = 'Contact Form 7';
		}
		if ( ! empty( $detected['wpforms'] ) ) {
			$forms[] = 'WPForms';
		}
		if ( ! empty( $detected['gravity_forms'] ) ) {
			$forms[] = 'Gravity Forms';
		}
		if ( ! empty( $detected['ninja_forms'] ) ) {
			$forms[] = 'Ninja Forms';
		}
		if ( ! empty( $detected['forminator'] ) ) {
			$forms[] = 'Forminator';
		}

		return ! empty( $forms ) ? implode( ', ', $forms ) : 'None';
	}

	/**
	 * Detect advertising pixels
	 */
	protected function detect_ads_pixels( array $detected ): string {
		$pixels = array();

		if ( ! empty( $detected['facebook_pixel'] ) ) {
			$pixels[] = 'Facebook Pixel';
		}
		if ( ! empty( $detected['google_ads'] ) ) {
			$pixels[] = 'Google Ads';
		}

		return ! empty( $pixels ) ? implode( ', ', $pixels ) : 'None';
	}

	/**
	 * Detect Google Analytics
	 */
	protected function detect_google_analytics(): bool {
		// Check for common GA plugins
		if ( class_exists( 'MonsterInsights' ) || class_exists( 'Ga_Helper' ) ) {
			return true;
		}

		// Check if GA code is present in footer/header scripts
		$header_scripts = get_option( 'header_scripts', '' );
		$footer_scripts = get_option( 'footer_scripts', '' );

		return ( stripos( $header_scripts, 'google-analytics.com' ) !== false ||
				stripos( $footer_scripts, 'google-analytics.com' ) !== false ||
				stripos( $header_scripts, 'googletagmanager.com' ) !== false ||
				stripos( $footer_scripts, 'googletagmanager.com' ) !== false );
	}

	/**
	 * Detect Facebook Pixel
	 */
	protected function detect_facebook_pixel(): bool {
		if ( class_exists( 'Facebook_Pixel_Plugin' ) || function_exists( 'facebook_pixel_code' ) ) {
			return true;
		}

		$header_scripts = get_option( 'header_scripts', '' );
		return stripos( $header_scripts, 'facebook.com/tr?' ) !== false || stripos( $header_scripts, 'fbq(' ) !== false;
	}

	/**
	 * Detect Google Ads
	 */
	protected function detect_google_ads(): bool {
		$header_scripts = get_option( 'header_scripts', '' );
		$footer_scripts = get_option( 'footer_scripts', '' );

		return ( stripos( $header_scripts, 'googleadservices.com' ) !== false ||
				stripos( $footer_scripts, 'googleadservices.com' ) !== false ||
				stripos( $header_scripts, 'google.com/pagead' ) !== false );
	}

	/**
	 * Detect PayPal
	 */
	protected function detect_paypal(): bool {
		// Check WooCommerce gateways if available
		if ( class_exists( 'WC_Gateway_Paypal' ) ) {
			return true;
		}

		return class_exists( 'PayPal' ) || function_exists( 'paypal_checkout' );
	}
}
