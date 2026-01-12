<?php
/**
 * Company Profile Service
 *
 * Business logic for managing company profile data used in the Document Hub.
 * Handles validation, step completion tracking, smart detection, and
 * coordination between the wizard UI and the profile repository.
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
 * Class Company_Profile_Service
 *
 * Provides business logic for company profile management.
 *
 * @since 4.1.0
 */
class Company_Profile_Service extends Base_Service {

	/**
	 * Profile repository instance
	 *
	 * @var Company_Profile_Repository
	 */
	protected $repository;

	/**
	 * Step definitions
	 *
	 * @var array
	 */
	protected $steps;

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->repository = Company_Profile_Repository::get_instance();
		$this->steps      = $this->define_steps();
	}

	/**
	 * Define wizard steps
	 *
	 * @since 4.1.0
	 * @return array Step definitions
	 */
	protected function define_steps(): array {
		return array(
			1  => array(
				'key'         => 'company',
				'title'       => __( 'Company Information', 'shahi-legalflowsuite' ),
				'description' => __( 'Basic details about your company or organization.', 'shahi-legalflowsuite' ),
				'icon'        => 'building',
				'sections'    => array( 'company' ),
				'fields'      => array(
					'company.legal_name'          => array(
						'label'       => __( 'Legal Company Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => true,
						'placeholder' => __( 'Acme Corporation Ltd.', 'shahi-legalflowsuite' ),
						'help'        => __( 'The official registered name of your company.', 'shahi-legalflowsuite' ),
					),
					'company.trading_name'        => array(
						'label'       => __( 'Trading Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'Acme', 'shahi-legalflowsuite' ),
						'help'        => __( 'The name your business operates under (if different).', 'shahi-legalflowsuite' ),
					),
					'company.registration_number' => array(
						'label'       => __( 'Registration Number', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '12345678', 'shahi-legalflowsuite' ),
						'help'        => __( 'Company registration or incorporation number.', 'shahi-legalflowsuite' ),
					),
					'company.vat_number'          => array(
						'label'       => __( 'VAT/Tax Number', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'GB123456789', 'shahi-legalflowsuite' ),
						'help'        => __( 'VAT or tax identification number.', 'shahi-legalflowsuite' ),
					),
					'company.address.street'      => array(
						'label'       => __( 'Street Address', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => true,
						'placeholder' => __( '123 Business Street', 'shahi-legalflowsuite' ),
					),
					'company.address.city'        => array(
						'label'       => __( 'City', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => true,
						'placeholder' => __( 'London', 'shahi-legalflowsuite' ),
					),
					'company.address.state'       => array(
						'label'       => __( 'State/Province', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'Greater London', 'shahi-legalflowsuite' ),
					),
					'company.address.postal_code' => array(
						'label'       => __( 'Postal Code', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'SW1A 1AA', 'shahi-legalflowsuite' ),
					),
					'company.address.country'     => array(
						'label'    => __( 'Country', 'shahi-legalflowsuite' ),
						'type'     => 'select',
						'required' => true,
						'options'  => 'countries',
						'help'     => __( 'Country where your company is registered.', 'shahi-legalflowsuite' ),
					),
					'company.business_type'       => array(
						'label'    => __( 'Business Type', 'shahi-legalflowsuite' ),
						'type'     => 'select',
						'required' => true,
						'options'  => array(
							''                => __( 'Select...', 'shahi-legalflowsuite' ),
							'sole_proprietor' => __( 'Sole Proprietor', 'shahi-legalflowsuite' ),
							'partnership'     => __( 'Partnership', 'shahi-legalflowsuite' ),
							'llc'             => __( 'Limited Liability Company (LLC)', 'shahi-legalflowsuite' ),
							'corporation'     => __( 'Corporation', 'shahi-legalflowsuite' ),
							'nonprofit'       => __( 'Non-Profit Organization', 'shahi-legalflowsuite' ),
							'government'      => __( 'Government Agency', 'shahi-legalflowsuite' ),
							'other'           => __( 'Other', 'shahi-legalflowsuite' ),
						),
					),
					'company.industry'            => array(
						'label'    => __( 'Industry', 'shahi-legalflowsuite' ),
						'type'     => 'select',
						'required' => false,
						'options'  => array(
							''              => __( 'Select...', 'shahi-legalflowsuite' ),
							'technology'    => __( 'Technology / Software', 'shahi-legalflowsuite' ),
							'ecommerce'     => __( 'E-Commerce / Retail', 'shahi-legalflowsuite' ),
							'finance'       => __( 'Finance / Banking', 'shahi-legalflowsuite' ),
							'healthcare'    => __( 'Healthcare', 'shahi-legalflowsuite' ),
							'education'     => __( 'Education', 'shahi-legalflowsuite' ),
							'media'         => __( 'Media / Entertainment', 'shahi-legalflowsuite' ),
							'professional'  => __( 'Professional Services', 'shahi-legalflowsuite' ),
							'manufacturing' => __( 'Manufacturing', 'shahi-legalflowsuite' ),
							'hospitality'   => __( 'Hospitality / Travel', 'shahi-legalflowsuite' ),
							'real_estate'   => __( 'Real Estate', 'shahi-legalflowsuite' ),
							'other'         => __( 'Other', 'shahi-legalflowsuite' ),
						),
					),
				),
			),
			2  => array(
				'key'         => 'contacts',
				'title'       => __( 'Contact Details', 'shahi-legalflowsuite' ),
				'description' => __( 'Contact information for legal and privacy matters.', 'shahi-legalflowsuite' ),
				'icon'        => 'email',
				'sections'    => array( 'contacts' ),
				'fields'      => array(
					'contacts.legal_email'   => array(
						'label'       => __( 'Legal Contact Email', 'shahi-legalflowsuite' ),
						'type'        => 'email',
						'required'    => true,
						'placeholder' => __( 'legal@example.com', 'shahi-legalflowsuite' ),
						'help'        => __( 'Email for legal notices and inquiries.', 'shahi-legalflowsuite' ),
					),
					'contacts.support_email' => array(
						'label'       => __( 'Support Email', 'shahi-legalflowsuite' ),
						'type'        => 'email',
						'required'    => false,
						'placeholder' => __( 'support@example.com', 'shahi-legalflowsuite' ),
						'help'        => __( 'General customer support email.', 'shahi-legalflowsuite' ),
					),
					'contacts.phone'         => array(
						'label'       => __( 'Phone Number', 'shahi-legalflowsuite' ),
						'type'        => 'tel',
						'required'    => false,
						'placeholder' => __( '+1 (555) 123-4567', 'shahi-legalflowsuite' ),
					),
					'contacts.dpo.name'      => array(
						'label'       => __( 'Data Protection Officer Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'Jane Smith', 'shahi-legalflowsuite' ),
						'help'        => __( 'Required for GDPR if you process personal data at scale.', 'shahi-legalflowsuite' ),
					),
					'contacts.dpo.email'     => array(
						'label'       => __( 'DPO Email', 'shahi-legalflowsuite' ),
						'type'        => 'email',
						'required'    => true,
						'placeholder' => __( 'dpo@example.com', 'shahi-legalflowsuite' ),
						'help'        => __( 'Email for data protection inquiries and DSR requests.', 'shahi-legalflowsuite' ),
					),
					'contacts.dpo.phone'     => array(
						'label'       => __( 'DPO Phone', 'shahi-legalflowsuite' ),
						'type'        => 'tel',
						'required'    => false,
						'placeholder' => __( '+1 (555) 123-4568', 'shahi-legalflowsuite' ),
					),
					'contacts.dpo.address'   => array(
						'label'       => __( 'DPO Address', 'shahi-legalflowsuite' ),
						'type'        => 'textarea',
						'required'    => false,
						'placeholder' => __( 'DPO office address if different from company', 'shahi-legalflowsuite' ),
						'rows'        => 2,
					),
				),
			),
			3  => array(
				'key'         => 'website',
				'title'       => __( 'Website & Services', 'shahi-legalflowsuite' ),
				'description' => __( 'Information about your website and services.', 'shahi-legalflowsuite' ),
				'icon'        => 'admin-site',
				'sections'    => array( 'website' ),
				'fields'      => array(
					'website.url'                 => array(
						'label'       => __( 'Website URL', 'shahi-legalflowsuite' ),
						'type'        => 'url',
						'required'    => true,
						'placeholder' => __( 'https://example.com', 'shahi-legalflowsuite' ),
						'default'     => get_bloginfo( 'url' ),
					),
					'website.app_name'            => array(
						'label'       => __( 'Website/App Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'My Awesome App', 'shahi-legalflowsuite' ),
						'default'     => get_bloginfo( 'name' ),
						'help'        => __( 'The public-facing name of your website or application.', 'shahi-legalflowsuite' ),
					),
					'website.service_description' => array(
						'label'       => __( 'Service Description', 'shahi-legalflowsuite' ),
						'type'        => 'textarea',
						'required'    => true,
						'placeholder' => __( 'Describe what your website or service does...', 'shahi-legalflowsuite' ),
						'rows'        => 4,
						'help'        => __( 'Brief description of your services for use in legal documents.', 'shahi-legalflowsuite' ),
					),
					'website.target_audience'     => array(
						'label'       => __( 'Target Audience', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'Small business owners, developers, etc.', 'shahi-legalflowsuite' ),
					),
				),
			),
			4  => array(
				'key'         => 'data_collection',
				'title'       => __( 'Data Collection', 'shahi-legalflowsuite' ),
				'description' => __( 'What personal data you collect and why.', 'shahi-legalflowsuite' ),
				'icon'        => 'database',
				'sections'    => array( 'data_collection' ),
				'fields'      => array(
					'data_collection.personal_data_types' => array(
						'label'    => __( 'Types of Personal Data Collected', 'shahi-legalflowsuite' ),
						'type'     => 'checkbox_group',
						'required' => true,
						'options'  => array(
							'name'            => __( 'Name', 'shahi-legalflowsuite' ),
							'email'           => __( 'Email Address', 'shahi-legalflowsuite' ),
							'phone'           => __( 'Phone Number', 'shahi-legalflowsuite' ),
							'address'         => __( 'Physical Address', 'shahi-legalflowsuite' ),
							'ip_address'      => __( 'IP Address', 'shahi-legalflowsuite' ),
							'device_info'     => __( 'Device Information', 'shahi-legalflowsuite' ),
							'location'        => __( 'Location Data', 'shahi-legalflowsuite' ),
							'payment'         => __( 'Payment Information', 'shahi-legalflowsuite' ),
							'browsing'        => __( 'Browsing History/Behavior', 'shahi-legalflowsuite' ),
							'account_info'    => __( 'Account Credentials', 'shahi-legalflowsuite' ),
							'social_profiles' => __( 'Social Media Profiles', 'shahi-legalflowsuite' ),
							'employment'      => __( 'Employment Information', 'shahi-legalflowsuite' ),
							'other'           => __( 'Other', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'Select all types of personal data your website collects.', 'shahi-legalflowsuite' ),
					),
					'data_collection.purposes'            => array(
						'label'    => __( 'Purposes of Data Processing', 'shahi-legalflowsuite' ),
						'type'     => 'checkbox_group',
						'required' => true,
						'options'  => array(
							'service_delivery'   => __( 'Service Delivery', 'shahi-legalflowsuite' ),
							'account_management' => __( 'Account Management', 'shahi-legalflowsuite' ),
							'communication'      => __( 'Communication', 'shahi-legalflowsuite' ),
							'marketing'          => __( 'Marketing & Promotions', 'shahi-legalflowsuite' ),
							'analytics'          => __( 'Analytics & Improvement', 'shahi-legalflowsuite' ),
							'personalization'    => __( 'Personalization', 'shahi-legalflowsuite' ),
							'security'           => __( 'Security & Fraud Prevention', 'shahi-legalflowsuite' ),
							'legal_compliance'   => __( 'Legal Compliance', 'shahi-legalflowsuite' ),
							'research'           => __( 'Research & Development', 'shahi-legalflowsuite' ),
							'advertising'        => __( 'Targeted Advertising', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'Select all purposes for which you process personal data.', 'shahi-legalflowsuite' ),
					),
					'data_collection.special_categories'  => array(
						'label'    => __( 'Do you collect special category data?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'Special categories include: race, ethnicity, political opinions, religious beliefs, health data, sexual orientation, genetic/biometric data.', 'shahi-legalflowsuite' ),
					),
					'data_collection.children_data'       => array(
						'label'    => __( 'Do you knowingly collect data from children?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'If yes, additional compliance measures may be required (COPPA, etc.).', 'shahi-legalflowsuite' ),
					),
					'data_collection.minimum_age'         => array(
						'label'       => __( 'Minimum Age Requirement', 'shahi-legalflowsuite' ),
						'type'        => 'number',
						'required'    => false,
						'placeholder' => '16',
						'default'     => 16,
						'min'         => 13,
						'max'         => 21,
						'help'        => __( 'Minimum age to use your service (GDPR default: 16, COPPA: 13).', 'shahi-legalflowsuite' ),
					),
				),
			),
			5  => array(
				'key'         => 'third_parties',
				'title'       => __( 'Third-Party Services', 'shahi-legalflowsuite' ),
				'description' => __( 'External services that may process user data.', 'shahi-legalflowsuite' ),
				'icon'        => 'networking',
				'sections'    => array( 'third_parties' ),
				'fields'      => array(
					'third_parties.analytics' => array(
						'label'       => __( 'Analytics Services', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Google Analytics, Mixpanel, etc.', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'Google Analytics', 'Google Analytics 4', 'Mixpanel', 'Amplitude', 'Heap', 'Hotjar', 'Matomo', 'Plausible', 'Fathom' ),
						'help'        => __( 'Enter analytics tools used on your site.', 'shahi-legalflowsuite' ),
					),
					'third_parties.payment'   => array(
						'label'       => __( 'Payment Processors', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Stripe, PayPal, etc.', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'Stripe', 'PayPal', 'Square', 'Braintree', 'Authorize.net', 'WooCommerce Payments', 'Klarna', 'Afterpay' ),
					),
					'third_parties.marketing' => array(
						'label'       => __( 'Marketing & Email Services', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Mailchimp, HubSpot, etc.', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'Mailchimp', 'HubSpot', 'Constant Contact', 'SendGrid', 'Klaviyo', 'ActiveCampaign', 'ConvertKit', 'Drip' ),
					),
					'third_parties.hosting'   => array(
						'label'       => __( 'Hosting & Infrastructure', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'AWS, Cloudflare, etc.', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'AWS', 'Google Cloud', 'Azure', 'Cloudflare', 'DigitalOcean', 'WP Engine', 'Kinsta', 'SiteGround' ),
					),
					'third_parties.cdn'       => array(
						'label'       => __( 'CDN Services', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Cloudflare, BunnyCDN, etc.', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'Cloudflare', 'BunnyCDN', 'KeyCDN', 'StackPath', 'Fastly', 'Amazon CloudFront' ),
					),
					'third_parties.crm'       => array(
						'label'       => __( 'CRM & Customer Support', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Zendesk, Intercom, etc.', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'Zendesk', 'Intercom', 'Freshdesk', 'Help Scout', 'Drift', 'Crisp', 'LiveChat' ),
					),
					'third_parties.other'     => array(
						'label'       => __( 'Other Services', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Any other third-party services', 'shahi-legalflowsuite' ),
					),
				),
			),
			6  => array(
				'key'         => 'cookies',
				'title'       => __( 'Cookie Usage', 'shahi-legalflowsuite' ),
				'description' => __( 'Cookies and tracking technologies used on your site.', 'shahi-legalflowsuite' ),
				'icon'        => 'admin-settings',
				'sections'    => array( 'cookies' ),
				'fields'      => array(
					'cookies.essential'   => array(
						'label'    => __( 'Essential Cookies', 'shahi-legalflowsuite' ),
						'type'     => 'cookie_list',
						'required' => true,
						'help'     => __( 'Cookies required for the website to function (session, security, etc.).', 'shahi-legalflowsuite' ),
						'default'  => array(
							array(
								'name'     => 'wordpress_logged_in_*',
								'purpose'  => 'WordPress login session',
								'duration' => 'Session',
							),
							array(
								'name'     => 'wp-settings-*',
								'purpose'  => 'WordPress user settings',
								'duration' => '1 year',
							),
						),
					),
					'cookies.analytics'   => array(
						'label'    => __( 'Analytics Cookies', 'shahi-legalflowsuite' ),
						'type'     => 'cookie_list',
						'required' => false,
						'help'     => __( 'Cookies used for analytics and performance measurement.', 'shahi-legalflowsuite' ),
					),
					'cookies.marketing'   => array(
						'label'    => __( 'Marketing Cookies', 'shahi-legalflowsuite' ),
						'type'     => 'cookie_list',
						'required' => false,
						'help'     => __( 'Cookies used for advertising and retargeting.', 'shahi-legalflowsuite' ),
					),
					'cookies.preferences' => array(
						'label'    => __( 'Preference Cookies', 'shahi-legalflowsuite' ),
						'type'     => 'cookie_list',
						'required' => false,
						'help'     => __( 'Cookies that remember user preferences and settings.', 'shahi-legalflowsuite' ),
					),
					'cookies.social'      => array(
						'label'    => __( 'Social Media Cookies', 'shahi-legalflowsuite' ),
						'type'     => 'cookie_list',
						'required' => false,
						'help'     => __( 'Cookies set by social media platforms for sharing features.', 'shahi-legalflowsuite' ),
					),
				),
			),
			7  => array(
				'key'         => 'legal',
				'title'       => __( 'Legal & Jurisdiction', 'shahi-legalflowsuite' ),
				'description' => __( 'Legal frameworks and jurisdictions that apply to your business.', 'shahi-legalflowsuite' ),
				'icon'        => 'admin-site-alt3',
				'sections'    => array( 'legal' ),
				'fields'      => array(
					'legal.primary_jurisdiction'    => array(
						'label'    => __( 'Primary Jurisdiction', 'shahi-legalflowsuite' ),
						'type'     => 'select',
						'required' => true,
						'options'  => 'countries',
						'help'     => __( 'The main country/region whose laws govern your business.', 'shahi-legalflowsuite' ),
					),
					'legal.gdpr_applies'            => array(
						'label'    => __( 'Does GDPR apply to your business?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'GDPR applies if you have users in the EU/EEA or process EU citizens\' data.', 'shahi-legalflowsuite' ),
					),
					'legal.ccpa_applies'            => array(
						'label'    => __( 'Does CCPA/CPRA apply to your business?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'CCPA applies if you serve California residents and meet certain thresholds.', 'shahi-legalflowsuite' ),
					),
					'legal.lgpd_applies'            => array(
						'label'    => __( 'Does LGPD apply to your business?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'LGPD applies if you process data of individuals in Brazil.', 'shahi-legalflowsuite' ),
					),
					'legal.supervisory_authority'   => array(
						'label'       => __( 'Supervisory Authority', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'e.g., ICO (UK), CNIL (France)', 'shahi-legalflowsuite' ),
						'help'        => __( 'The data protection authority for your jurisdiction.', 'shahi-legalflowsuite' ),
					),
					'legal.representative_eu.name'  => array(
						'label'       => __( 'EU Representative Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'EU Representative Ltd.', 'shahi-legalflowsuite' ),
						'help'        => __( 'Required under GDPR Art. 27 if you\'re outside the EU but process EU data.', 'shahi-legalflowsuite' ),
						'condition'   => array( 'legal.gdpr_applies' => '1' ),
					),
					'legal.representative_eu.email' => array(
						'label'       => __( 'EU Representative Email', 'shahi-legalflowsuite' ),
						'type'        => 'email',
						'required'    => false,
						'placeholder' => __( 'eu-rep@example.com', 'shahi-legalflowsuite' ),
						'condition'   => array( 'legal.gdpr_applies' => '1' ),
					),
					'legal.representative_uk.name'  => array(
						'label'       => __( 'UK Representative Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'UK Representative Ltd.', 'shahi-legalflowsuite' ),
						'help'        => __( 'Required under UK GDPR if you\'re outside the UK but process UK data.', 'shahi-legalflowsuite' ),
						'condition'   => array( 'legal.gdpr_applies' => '1' ),
					),
				),
			),
			8  => array(
				'key'         => 'retention',
				'title'       => __( 'Data Retention & Security', 'shahi-legalflowsuite' ),
				'description' => __( 'How long you keep data and security measures in place.', 'shahi-legalflowsuite' ),
				'icon'        => 'shield',
				'sections'    => array( 'retention', 'security', 'user_rights' ),
				'fields'      => array(
					'retention.default_period'       => array(
						'label'    => __( 'Default Retention Period', 'shahi-legalflowsuite' ),
						'type'     => 'select',
						'required' => true,
						'options'  => array(
							''           => __( 'Select...', 'shahi-legalflowsuite' ),
							'30_days'    => __( '30 Days', 'shahi-legalflowsuite' ),
							'90_days'    => __( '90 Days', 'shahi-legalflowsuite' ),
							'1_year'     => __( '1 Year', 'shahi-legalflowsuite' ),
							'2_years'    => __( '2 Years', 'shahi-legalflowsuite' ),
							'3_years'    => __( '3 Years', 'shahi-legalflowsuite' ),
							'5_years'    => __( '5 Years', 'shahi-legalflowsuite' ),
							'7_years'    => __( '7 Years', 'shahi-legalflowsuite' ),
							'indefinite' => __( 'Indefinite (as long as necessary)', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'How long you typically retain personal data.', 'shahi-legalflowsuite' ),
					),
					'retention.deletion_policy'      => array(
						'label'       => __( 'Deletion Policy', 'shahi-legalflowsuite' ),
						'type'        => 'textarea',
						'required'    => false,
						'placeholder' => __( 'Describe your data deletion process...', 'shahi-legalflowsuite' ),
						'rows'        => 3,
						'help'        => __( 'How data is deleted when retention period expires or upon request.', 'shahi-legalflowsuite' ),
					),
					'retention.backup_retention'     => array(
						'label'    => __( 'Backup Retention Period', 'shahi-legalflowsuite' ),
						'type'     => 'select',
						'required' => false,
						'options'  => array(
							''        => __( 'Select...', 'shahi-legalflowsuite' ),
							'7_days'  => __( '7 Days', 'shahi-legalflowsuite' ),
							'30_days' => __( '30 Days', 'shahi-legalflowsuite' ),
							'90_days' => __( '90 Days', 'shahi-legalflowsuite' ),
							'1_year'  => __( '1 Year', 'shahi-legalflowsuite' ),
						),
					),
					'security.encryption_at_rest'    => array(
						'label'    => __( 'Do you encrypt data at rest?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'security.encryption_in_transit' => array(
						'label'    => __( 'Do you encrypt data in transit (HTTPS)?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'default'  => '1',
					),
					'security.measures'              => array(
						'label'    => __( 'Security Measures', 'shahi-legalflowsuite' ),
						'type'     => 'checkbox_group',
						'required' => false,
						'options'  => array(
							'ssl_tls'          => __( 'SSL/TLS Encryption', 'shahi-legalflowsuite' ),
							'firewall'         => __( 'Firewall Protection', 'shahi-legalflowsuite' ),
							'access_control'   => __( 'Access Control', 'shahi-legalflowsuite' ),
							'password_hashing' => __( 'Password Hashing', 'shahi-legalflowsuite' ),
							'backup'           => __( 'Regular Backups', 'shahi-legalflowsuite' ),
							'monitoring'       => __( 'Security Monitoring', 'shahi-legalflowsuite' ),
							'vulnerability'    => __( 'Vulnerability Scanning', 'shahi-legalflowsuite' ),
							'2fa'              => __( 'Two-Factor Authentication', 'shahi-legalflowsuite' ),
						),
					),
					'user_rights.response_timeframe' => array(
						'label'       => __( 'DSR Response Timeframe (days)', 'shahi-legalflowsuite' ),
						'type'        => 'number',
						'required'    => false,
						'placeholder' => '30',
						'default'     => 30,
						'min'         => 1,
						'max'         => 90,
						'help'        => __( 'How quickly you respond to data subject requests (GDPR: 30 days).', 'shahi-legalflowsuite' ),
					),
				),
			),
			9  => array(
				'key'         => 'ecommerce',
				'title'       => __( 'E-Commerce Settings', 'shahi-legalflowsuite' ),
				'description' => __( 'Configure settings for online sales, shipping, and returns.', 'shahi-legalflowsuite' ),
				'icon'        => 'cart',
				'sections'    => array( 'ecommerce' ),
				'fields'      => array(
					'ecommerce.enabled'              => array(
						'label'    => __( 'Do you sell products or services online?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => true,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'help'     => __( 'Enable this if your website sells products, services, or subscriptions.', 'shahi-legalflowsuite' ),
					),
					'ecommerce.sells_physical'       => array(
						'label'     => __( 'Do you sell physical products?', 'shahi-legalflowsuite' ),
						'type'      => 'radio',
						'required'  => false,
						'options'   => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.enabled' => '1' ),
					),
					'ecommerce.sells_digital'        => array(
						'label'     => __( 'Do you sell digital products or downloads?', 'shahi-legalflowsuite' ),
						'type'      => 'radio',
						'required'  => false,
						'options'   => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.enabled' => '1' ),
					),
					'ecommerce.sells_subscriptions'  => array(
						'label'     => __( 'Do you offer subscriptions or recurring billing?', 'shahi-legalflowsuite' ),
						'type'      => 'radio',
						'required'  => false,
						'options'   => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.enabled' => '1' ),
					),
					'ecommerce.sells_services'       => array(
						'label'     => __( 'Do you sell professional services?', 'shahi-legalflowsuite' ),
						'type'      => 'radio',
						'required'  => false,
						'options'   => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.enabled' => '1' ),
					),
					'ecommerce.shipping_regions'     => array(
						'label'       => __( 'Shipping Regions', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'United States, Canada, Europe...', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'United States', 'Canada', 'Europe', 'UK', 'Australia', 'Worldwide' ),
						'condition'   => array( 'ecommerce.sells_physical' => '1' ),
						'help'        => __( 'Regions where you ship physical products.', 'shahi-legalflowsuite' ),
					),
					'ecommerce.shipping_timeframe'   => array(
						'label'       => __( 'Standard Shipping Timeframe', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '5-7 business days', 'shahi-legalflowsuite' ),
						'condition'   => array( 'ecommerce.sells_physical' => '1' ),
					),
					'ecommerce.return_window'        => array(
						'label'     => __( 'Return Window', 'shahi-legalflowsuite' ),
						'type'      => 'select',
						'required'  => false,
						'options'   => array(
							''        => __( 'Select...', 'shahi-legalflowsuite' ),
							'14_days' => __( '14 Days', 'shahi-legalflowsuite' ),
							'30_days' => __( '30 Days', 'shahi-legalflowsuite' ),
							'60_days' => __( '60 Days', 'shahi-legalflowsuite' ),
							'90_days' => __( '90 Days', 'shahi-legalflowsuite' ),
							'none'    => __( 'No Returns Accepted', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.enabled' => '1' ),
						'help'      => __( 'Time period during which customers can return products.', 'shahi-legalflowsuite' ),
					),
					'ecommerce.refund_timeframe'     => array(
						'label'       => __( 'Refund Processing Time', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '7-10 business days', 'shahi-legalflowsuite' ),
						'condition'   => array( 'ecommerce.enabled' => '1' ),
					),
					'ecommerce.warranty_period'      => array(
						'label'     => __( 'Product Warranty Period', 'shahi-legalflowsuite' ),
						'type'      => 'select',
						'required'  => false,
						'options'   => array(
							''         => __( 'Select...', 'shahi-legalflowsuite' ),
							'none'     => __( 'No Warranty', 'shahi-legalflowsuite' ),
							'30_days'  => __( '30 Days', 'shahi-legalflowsuite' ),
							'90_days'  => __( '90 Days', 'shahi-legalflowsuite' ),
							'1_year'   => __( '1 Year', 'shahi-legalflowsuite' ),
							'2_years'  => __( '2 Years', 'shahi-legalflowsuite' ),
							'lifetime' => __( 'Lifetime', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.sells_physical' => '1' ),
					),
					'ecommerce.billing_cycle'        => array(
						'label'     => __( 'Subscription Billing Cycle', 'shahi-legalflowsuite' ),
						'type'      => 'select',
						'required'  => false,
						'options'   => array(
							''          => __( 'Select...', 'shahi-legalflowsuite' ),
							'weekly'    => __( 'Weekly', 'shahi-legalflowsuite' ),
							'monthly'   => __( 'Monthly', 'shahi-legalflowsuite' ),
							'quarterly' => __( 'Quarterly', 'shahi-legalflowsuite' ),
							'annually'  => __( 'Annually', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'ecommerce.sells_subscriptions' => '1' ),
					),
					'ecommerce.cancellation_notice'  => array(
						'label'       => __( 'Cancellation Notice Period', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '30 days before next billing', 'shahi-legalflowsuite' ),
						'condition'   => array( 'ecommerce.sells_subscriptions' => '1' ),
					),
					'ecommerce.has_affiliate'        => array(
						'label'    => __( 'Do you have an affiliate program?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'ecommerce.affiliate_commission' => array(
						'label'       => __( 'Affiliate Commission Rate', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '10% per sale', 'shahi-legalflowsuite' ),
						'condition'   => array( 'ecommerce.has_affiliate' => '1' ),
					),
					'ecommerce.affiliate_cookie'     => array(
						'label'       => __( 'Affiliate Cookie Duration', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '30 days', 'shahi-legalflowsuite' ),
						'condition'   => array( 'ecommerce.has_affiliate' => '1' ),
					),
				),
			),
			10 => array(
				'key'         => 'software',
				'title'       => __( 'Software & API', 'shahi-legalflowsuite' ),
				'description' => __( 'Settings for software, downloads, and API access.', 'shahi-legalflowsuite' ),
				'icon'        => 'editor-code',
				'sections'    => array( 'software' ),
				'fields'      => array(
					'software.has_downloadable'   => array(
						'label'    => __( 'Do you offer downloadable software?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'software.license_type'       => array(
						'label'     => __( 'License Type', 'shahi-legalflowsuite' ),
						'type'      => 'select',
						'required'  => false,
						'options'   => array(
							''            => __( 'Select...', 'shahi-legalflowsuite' ),
							'personal'    => __( 'Personal Use Only', 'shahi-legalflowsuite' ),
							'commercial'  => __( 'Commercial License', 'shahi-legalflowsuite' ),
							'enterprise'  => __( 'Enterprise License', 'shahi-legalflowsuite' ),
							'open_source' => __( 'Open Source', 'shahi-legalflowsuite' ),
							'saas'        => __( 'SaaS (Software as a Service)', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'software.has_downloadable' => '1' ),
					),
					'software.restrictions'       => array(
						'label'     => __( 'License Restrictions', 'shahi-legalflowsuite' ),
						'type'      => 'checkbox_group',
						'required'  => false,
						'options'   => array(
							'no_resale'       => __( 'No Resale/Redistribution', 'shahi-legalflowsuite' ),
							'no_modification' => __( 'No Modification', 'shahi-legalflowsuite' ),
							'no_reverse'      => __( 'No Reverse Engineering', 'shahi-legalflowsuite' ),
							'single_user'     => __( 'Single User Only', 'shahi-legalflowsuite' ),
							'single_site'     => __( 'Single Site/Installation', 'shahi-legalflowsuite' ),
							'no_sublicense'   => __( 'No Sub-licensing', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'software.has_downloadable' => '1' ),
					),
					'software.has_api'            => array(
						'label'    => __( 'Do you provide an API?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'software.api_rate_limit'     => array(
						'label'       => __( 'API Rate Limit', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( '1000 requests per hour', 'shahi-legalflowsuite' ),
						'condition'   => array( 'software.has_api' => '1' ),
					),
					'software.api_authentication' => array(
						'label'     => __( 'API Authentication Methods', 'shahi-legalflowsuite' ),
						'type'      => 'checkbox_group',
						'required'  => false,
						'options'   => array(
							'api_key' => __( 'API Key', 'shahi-legalflowsuite' ),
							'oauth2'  => __( 'OAuth 2.0', 'shahi-legalflowsuite' ),
							'jwt'     => __( 'JWT Tokens', 'shahi-legalflowsuite' ),
							'basic'   => __( 'Basic Auth', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'software.has_api' => '1' ),
					),
					'software.api_usage_limits'   => array(
						'label'       => __( 'API Usage Limits', 'shahi-legalflowsuite' ),
						'type'        => 'textarea',
						'required'    => false,
						'placeholder' => __( 'Describe any usage limitations or quotas...', 'shahi-legalflowsuite' ),
						'rows'        => 3,
						'condition'   => array( 'software.has_api' => '1' ),
					),
				),
			),
			11 => array(
				'key'         => 'community',
				'title'       => __( 'Community & Content', 'shahi-legalflowsuite' ),
				'description' => __( 'Settings for user-generated content, forums, and community features.', 'shahi-legalflowsuite' ),
				'icon'        => 'groups',
				'sections'    => array( 'community' ),
				'fields'      => array(
					'community.has_user_accounts'  => array(
						'label'    => __( 'Does your site have user accounts?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'community.has_forums'         => array(
						'label'     => __( 'Do you have forums or discussion boards?', 'shahi-legalflowsuite' ),
						'type'      => 'radio',
						'required'  => false,
						'options'   => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'community.has_user_accounts' => '1' ),
					),
					'community.has_comments'       => array(
						'label'    => __( 'Do you allow comments on content?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'community.has_ugc'            => array(
						'label'    => __( 'Can users submit content (posts, uploads, etc.)?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'community.content_moderation' => array(
						'label'     => __( 'Content Moderation Method', 'shahi-legalflowsuite' ),
						'type'      => 'select',
						'required'  => false,
						'options'   => array(
							''          => __( 'Select...', 'shahi-legalflowsuite' ),
							'pre'       => __( 'Pre-moderation (approve before publish)', 'shahi-legalflowsuite' ),
							'post'      => __( 'Post-moderation (review after publish)', 'shahi-legalflowsuite' ),
							'community' => __( 'Community Reporting', 'shahi-legalflowsuite' ),
							'automated' => __( 'Automated/AI Moderation', 'shahi-legalflowsuite' ),
							'none'      => __( 'No Moderation', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'community.has_ugc' => '1' ),
					),
					'community.age_restricted'     => array(
						'label'    => __( 'Is your content age-restricted?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'community.age_verification'   => array(
						'label'     => __( 'Age Verification Method', 'shahi-legalflowsuite' ),
						'type'      => 'select',
						'required'  => false,
						'options'   => array(
							''            => __( 'Select...', 'shahi-legalflowsuite' ),
							'checkbox'    => __( 'Self-declaration Checkbox', 'shahi-legalflowsuite' ),
							'dob'         => __( 'Date of Birth Entry', 'shahi-legalflowsuite' ),
							'id'          => __( 'ID Verification', 'shahi-legalflowsuite' ),
							'third_party' => __( 'Third-party Verification Service', 'shahi-legalflowsuite' ),
						),
						'condition' => array( 'community.age_restricted' => '1' ),
					),
					'community.dmca_agent_name'    => array(
						'label'       => __( 'DMCA Agent Name', 'shahi-legalflowsuite' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => __( 'Copyright Agent', 'shahi-legalflowsuite' ),
						'help'        => __( 'Designated agent for copyright infringement notices.', 'shahi-legalflowsuite' ),
					),
					'community.dmca_agent_email'   => array(
						'label'       => __( 'DMCA Agent Email', 'shahi-legalflowsuite' ),
						'type'        => 'email',
						'required'    => false,
						'placeholder' => __( 'dmca@example.com', 'shahi-legalflowsuite' ),
					),
					'community.dmca_agent_address' => array(
						'label'       => __( 'DMCA Agent Address', 'shahi-legalflowsuite' ),
						'type'        => 'textarea',
						'required'    => false,
						'placeholder' => __( 'Mailing address for DMCA notices', 'shahi-legalflowsuite' ),
						'rows'        => 2,
					),
					'community.has_mobile_app'     => array(
						'label'    => __( 'Do you have a mobile app?', 'shahi-legalflowsuite' ),
						'type'     => 'radio',
						'required' => false,
						'options'  => array(
							'0' => __( 'No', 'shahi-legalflowsuite' ),
							'1' => __( 'Yes', 'shahi-legalflowsuite' ),
						),
					),
					'community.app_stores'         => array(
						'label'       => __( 'App Store Links', 'shahi-legalflowsuite' ),
						'type'        => 'tags',
						'required'    => false,
						'placeholder' => __( 'Apple App Store, Google Play...', 'shahi-legalflowsuite' ),
						'suggestions' => array( 'Apple App Store', 'Google Play Store', 'Microsoft Store', 'Amazon Appstore' ),
						'condition'   => array( 'community.has_mobile_app' => '1' ),
					),
				),
			),
		);
	}

	/**
	 * Get all step definitions
	 *
	 * @since 4.1.0
	 * @return array
	 */
	public function get_steps(): array {
		return $this->steps;
	}

	/**
	 * Get a specific step
	 *
	 * @since 4.1.0
	 * @param int $step_number Step number (1-8).
	 * @return array|null Step definition or null
	 */
	public function get_step( int $step_number ): ?array {
		return $this->steps[ $step_number ] ?? null;
	}

	/**
	 * Get total number of steps
	 *
	 * @since 4.1.0
	 * @return int
	 */
	public function get_total_steps(): int {
		return count( $this->steps );
	}

	/**
	 * Get current profile data
	 *
	 * @since 4.1.0
	 * @return array
	 */
	public function get_profile(): array {
		return $this->repository->get_profile();
	}

	/**
	 * Get profile completion status
	 *
	 * @since 4.1.0
	 * @return array
	 */
	public function get_completion_status(): array {
		$profile = $this->repository->get_profile();
		$meta    = $this->repository->get_profile_meta();

		return array(
			'percentage'      => $meta['completion_percentage'] ?? 0,
			'version'         => $meta['version'] ?? 1,
			'completed_steps' => $profile['_meta']['completed_steps'] ?? array(),
			'last_step'       => $profile['_meta']['last_step'] ?? 1,
			'missing_fields'  => $this->repository->get_missing_fields(),
			'updated_at'      => $meta['updated_at'] ?? null,
		);
	}

	/**
	 * Save step data
	 *
	 * @since 4.1.0
	 * @param int   $step_number Step number.
	 * @param array $data        Step data.
	 * @return array Result with success status and errors
	 */
	public function save_step( int $step_number, array $data ): array {
		$step = $this->get_step( $step_number );
		if ( ! $step ) {
			return array(
				'success' => false,
				'errors'  => array( 'Invalid step number.' ),
			);
		}

		// Validate step data...
		$validation = $this->validate_step_data( $step_number, $data );
		if ( ! $validation['valid'] ) {
			return array(
				'success' => false,
				'errors'  => $validation['errors'],
			);
		}

		// Sanitize data...
		$sanitized = $this->sanitize_step_data( $step_number, $data );

		// Build profile update from flat fields to nested structure...
		$profile_update = $this->fields_to_profile_structure( $sanitized );

		// Save to repository...
		$saved = $this->repository->save_profile( $profile_update );

		if ( ! $saved ) {
			return array(
				'success' => false,
				'errors'  => array( 'Failed to save profile data.' ),
			);
		}

		// Mark step as completed...
		$this->mark_step_completed( $step_number );

		return array(
			'success'    => true,
			'errors'     => array(),
			'completion' => $this->get_completion_status(),
		);
	}

	/**
	 * Validate step data
	 *
	 * @since 4.1.0
	 * @param int   $step_number Step number.
	 * @param array $data        Data to validate.
	 * @return array Validation result
	 */
	public function validate_step_data( int $step_number, array $data ): array {
		$step   = $this->get_step( $step_number );
		$errors = array();

		if ( ! $step ) {
			return array(
				'valid'  => false,
				'errors' => array( 'Invalid step.' ),
			);
		}

		foreach ( $step['fields'] as $field_path => $field_config ) {
			$field_key = str_replace( '.', '_', $field_path );
			$value     = $data[ $field_key ] ?? null;

			// Check required...
			if ( ! empty( $field_config['required'] ) && $this->is_empty_value( $value ) ) {
				// Check condition...
				if ( isset( $field_config['condition'] ) ) {
					$condition_met = $this->check_field_condition( $field_config['condition'], $data );
					if ( ! $condition_met ) {
						continue; // Skip validation if condition not met.
					}
				}
				$errors[ $field_key ] = sprintf(
					/* translators: %s: field label */
					__( '%s is required.', 'shahi-legalflowsuite' ),
					$field_config['label']
				);
				continue;
			}

			// Skip further validation if empty and not required...
			if ( $this->is_empty_value( $value ) ) {
				continue;
			}

			// Type-specific validation...
			switch ( $field_config['type'] ) {
				case 'email':
					if ( ! is_email( $value ) ) {
						$errors[ $field_key ] = sprintf(
							/* translators: %s: field label */
							__( '%s must be a valid email address.', 'shahi-legalflowsuite' ),
							$field_config['label']
						);
					}
					break;

				case 'url':
					if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
						$errors[ $field_key ] = sprintf(
							/* translators: %s: field label */
							__( '%s must be a valid URL.', 'shahi-legalflowsuite' ),
							$field_config['label']
						);
					}
					break;

				case 'number':
					if ( ! is_numeric( $value ) ) {
						$errors[ $field_key ] = sprintf(
							/* translators: %s: field label */
							__( '%s must be a number.', 'shahi-legalflowsuite' ),
							$field_config['label']
						);
					} elseif ( isset( $field_config['min'] ) && $value < $field_config['min'] ) {
						$errors[ $field_key ] = sprintf(
							/* translators: 1: field label, 2: minimum value */
							__( '%1$s must be at least %2$s.', 'shahi-legalflowsuite' ),
							$field_config['label'],
							$field_config['min']
						);
					} elseif ( isset( $field_config['max'] ) && $value > $field_config['max'] ) {
						$errors[ $field_key ] = sprintf(
							/* translators: 1: field label, 2: maximum value */
							__( '%1$s must be at most %2$s.', 'shahi-legalflowsuite' ),
							$field_config['label'],
							$field_config['max']
						);
					}
					break;

				case 'checkbox_group':
					if ( ! is_array( $value ) || empty( $value ) ) {
						if ( ! empty( $field_config['required'] ) ) {
							$errors[ $field_key ] = sprintf(
								/* translators: %s: field label */
								__( 'Please select at least one option for %s.', 'shahi-legalflowsuite' ),
								$field_config['label']
							);
						}
					}
					break;
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	/**
	 * Sanitize step data
	 *
	 * @since 4.1.0
	 * @param int   $step_number Step number.
	 * @param array $data        Data to sanitize.
	 * @return array Sanitized data
	 */
	public function sanitize_step_data( int $step_number, array $data ): array {
		$step      = $this->get_step( $step_number );
		$sanitized = array();

		if ( ! $step ) {
			return $sanitized;
		}

		foreach ( $step['fields'] as $field_path => $field_config ) {
			$field_key = str_replace( '.', '_', $field_path );
			$value     = $data[ $field_key ] ?? null;

			if ( null === $value ) {
				continue;
			}

			switch ( $field_config['type'] ) {
				case 'email':
					$sanitized[ $field_path ] = sanitize_email( $value );
					break;

				case 'url':
					$sanitized[ $field_path ] = esc_url_raw( $value );
					break;

				case 'number':
					$sanitized[ $field_path ] = intval( $value );
					break;

				case 'textarea':
					$sanitized[ $field_path ] = sanitize_textarea_field( $value );
					break;

				case 'checkbox_group':
				case 'tags':
					if ( is_array( $value ) ) {
						$sanitized[ $field_path ] = array_map( 'sanitize_text_field', $value );
					} elseif ( is_string( $value ) ) {
						// Handle comma-separated values...
						$items                    = array_map( 'trim', explode( ',', $value ) );
						$sanitized[ $field_path ] = array_filter( array_map( 'sanitize_text_field', $items ) );
					}
					break;

				case 'cookie_list':
					if ( is_array( $value ) ) {
						$sanitized[ $field_path ] = array_map(
							function ( $cookie ) {
								return array(
									'name'     => sanitize_text_field( $cookie['name'] ?? '' ),
									'purpose'  => sanitize_text_field( $cookie['purpose'] ?? '' ),
									'duration' => sanitize_text_field( $cookie['duration'] ?? '' ),
								);
							},
							$value
						);
					}
					break;

				case 'radio':
					$sanitized[ $field_path ] = in_array( $value, array( '0', '1', 0, 1, true, false ), true )
						? (bool) $value
						: sanitize_text_field( $value );
					break;

				default:
					$sanitized[ $field_path ] = sanitize_text_field( $value );
					break;
			}
		}

		return $sanitized;
	}

	/**
	 * Convert flat field paths to nested profile structure
	 *
	 * @since 4.1.0
	 * @param array $fields Flat field path => value array.
	 * @return array Nested profile structure
	 */
	protected function fields_to_profile_structure( array $fields ): array {
		$profile = array();

		foreach ( $fields as $path => $value ) {
			$keys = explode( '.', $path );
			$ref  = &$profile;

			foreach ( $keys as $i => $key ) {
				if ( $i === count( $keys ) - 1 ) {
					$ref[ $key ] = $value;
				} else {
					if ( ! isset( $ref[ $key ] ) ) {
						$ref[ $key ] = array();
					}
					$ref = &$ref[ $key ];
				}
			}
		}

		return $profile;
	}

	/**
	 * Mark step as completed
	 *
	 * @since 4.1.0
	 * @param int $step_number Step number.
	 * @return void
	 */
	protected function mark_step_completed( int $step_number ): void {
		$profile = $this->repository->get_profile();

		if ( ! isset( $profile['_meta']['completed_steps'] ) ) {
			$profile['_meta']['completed_steps'] = array();
		}

		if ( ! in_array( $step_number, $profile['_meta']['completed_steps'], true ) ) {
			$profile['_meta']['completed_steps'][] = $step_number;
		}

		$profile['_meta']['last_step'] = max( $profile['_meta']['last_step'] ?? 1, $step_number );

		$this->repository->save_profile( array( '_meta' => $profile['_meta'] ) );
	}

	/**
	 * Check if a value is empty
	 *
	 * @since 4.1.0
	 * @param mixed $value Value to check.
	 * @return bool
	 */
	protected function is_empty_value( $value ): bool {
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
	 * Check field condition
	 *
	 * @since 4.1.0
	 * @param array $condition Condition array (field => expected_value).
	 * @param array $data      Current form data.
	 * @return bool
	 */
	protected function check_field_condition( array $condition, array $data ): bool {
		foreach ( $condition as $field_path => $expected ) {
			$field_key = str_replace( '.', '_', $field_path );
			$actual    = $data[ $field_key ] ?? null;

			if ( (string) $actual !== (string) $expected ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Auto-detect integrations and pre-fill profile
	 *
	 * @since 4.1.0
	 * @return array Detected values
	 */
	public function auto_detect(): array {
		$detected = array();

		// WordPress site info...
		$detected['website.url']            = get_bloginfo( 'url' );
		$detected['website.app_name']       = get_bloginfo( 'name' );
		$detected['contacts.support_email'] = get_bloginfo( 'admin_email' );

		// Check for HTTPS...
		$detected['security.encryption_in_transit'] = is_ssl();

		// Detect active plugins and services...
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_string  = implode( '|', $active_plugins );

		// Analytics...
		$analytics = array();
		if ( strpos( $plugin_string, 'google-site-kit' ) !== false || strpos( $plugin_string, 'google-analytics' ) !== false ) {
			$analytics[] = 'Google Analytics';
		}
		if ( strpos( $plugin_string, 'matomo' ) !== false ) {
			$analytics[] = 'Matomo';
		}
		if ( ! empty( $analytics ) ) {
			$detected['third_parties.analytics'] = $analytics;
		}

		// Payment processors...
		$payment = array();
		if ( strpos( $plugin_string, 'woocommerce' ) !== false ) {
			$payment[] = 'WooCommerce Payments';
		}
		if ( strpos( $plugin_string, 'stripe' ) !== false ) {
			$payment[] = 'Stripe';
		}
		if ( strpos( $plugin_string, 'paypal' ) !== false ) {
			$payment[] = 'PayPal';
		}
		if ( ! empty( $payment ) ) {
			$detected['third_parties.payment'] = $payment;
		}

		// Marketing/Email...
		$marketing = array();
		if ( strpos( $plugin_string, 'mailchimp' ) !== false ) {
			$marketing[] = 'Mailchimp';
		}
		if ( strpos( $plugin_string, 'hubspot' ) !== false ) {
			$marketing[] = 'HubSpot';
		}
		if ( ! empty( $marketing ) ) {
			$detected['third_parties.marketing'] = $marketing;
		}

		// Data collection (based on plugins)...
		$data_types = array( 'ip_address', 'device_info' );
		if ( strpos( $plugin_string, 'woocommerce' ) !== false ) {
			$data_types = array_merge( $data_types, array( 'name', 'email', 'address', 'phone', 'payment' ) );
		}
		if ( strpos( $plugin_string, 'contact-form' ) !== false || strpos( $plugin_string, 'wpforms' ) !== false || strpos( $plugin_string, 'gravityforms' ) !== false ) {
			$data_types = array_merge( $data_types, array( 'name', 'email' ) );
		}
		$detected['data_collection.personal_data_types'] = array_unique( $data_types );

		// Store detected values in profile meta...
		$profile                           = $this->repository->get_profile();
		$profile['_meta']['auto_detected'] = $detected;
		$this->repository->save_profile( array( '_meta' => $profile['_meta'] ) );

		return $detected;
	}

	/**
	 * Get countries list
	 *
	 * @since 4.1.0
	 * @return array
	 */
	public function get_countries(): array {
		return array(
			''   => __( 'Select Country...', 'shahi-legalflowsuite' ),
			'US' => __( 'United States', 'shahi-legalflowsuite' ),
			'GB' => __( 'United Kingdom', 'shahi-legalflowsuite' ),
			'CA' => __( 'Canada', 'shahi-legalflowsuite' ),
			'AU' => __( 'Australia', 'shahi-legalflowsuite' ),
			'DE' => __( 'Germany', 'shahi-legalflowsuite' ),
			'FR' => __( 'France', 'shahi-legalflowsuite' ),
			'IT' => __( 'Italy', 'shahi-legalflowsuite' ),
			'ES' => __( 'Spain', 'shahi-legalflowsuite' ),
			'NL' => __( 'Netherlands', 'shahi-legalflowsuite' ),
			'BE' => __( 'Belgium', 'shahi-legalflowsuite' ),
			'AT' => __( 'Austria', 'shahi-legalflowsuite' ),
			'CH' => __( 'Switzerland', 'shahi-legalflowsuite' ),
			'SE' => __( 'Sweden', 'shahi-legalflowsuite' ),
			'NO' => __( 'Norway', 'shahi-legalflowsuite' ),
			'DK' => __( 'Denmark', 'shahi-legalflowsuite' ),
			'FI' => __( 'Finland', 'shahi-legalflowsuite' ),
			'IE' => __( 'Ireland', 'shahi-legalflowsuite' ),
			'PT' => __( 'Portugal', 'shahi-legalflowsuite' ),
			'PL' => __( 'Poland', 'shahi-legalflowsuite' ),
			'BR' => __( 'Brazil', 'shahi-legalflowsuite' ),
			'MX' => __( 'Mexico', 'shahi-legalflowsuite' ),
			'IN' => __( 'India', 'shahi-legalflowsuite' ),
			'JP' => __( 'Japan', 'shahi-legalflowsuite' ),
			'CN' => __( 'China', 'shahi-legalflowsuite' ),
			'KR' => __( 'South Korea', 'shahi-legalflowsuite' ),
			'SG' => __( 'Singapore', 'shahi-legalflowsuite' ),
			'NZ' => __( 'New Zealand', 'shahi-legalflowsuite' ),
			'ZA' => __( 'South Africa', 'shahi-legalflowsuite' ),
			'AE' => __( 'United Arab Emirates', 'shahi-legalflowsuite' ),
			'IL' => __( 'Israel', 'shahi-legalflowsuite' ),
			'RU' => __( 'Russia', 'shahi-legalflowsuite' ),
			'TR' => __( 'Turkey', 'shahi-legalflowsuite' ),
			'AR' => __( 'Argentina', 'shahi-legalflowsuite' ),
			'CL' => __( 'Chile', 'shahi-legalflowsuite' ),
			'CO' => __( 'Colombia', 'shahi-legalflowsuite' ),
			'PH' => __( 'Philippines', 'shahi-legalflowsuite' ),
			'MY' => __( 'Malaysia', 'shahi-legalflowsuite' ),
			'TH' => __( 'Thailand', 'shahi-legalflowsuite' ),
			'ID' => __( 'Indonesia', 'shahi-legalflowsuite' ),
			'VN' => __( 'Vietnam', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get field options dynamically
	 *
	 * @since 4.1.0
	 * @param string $option_key Option key (e.g., 'countries').
	 * @return array
	 */
	public function get_field_options( string $option_key ): array {
		switch ( $option_key ) {
			case 'countries':
				return $this->get_countries();
			default:
				return array();
		}
	}

	/**
	 * Reset profile to defaults
	 *
	 * @since 4.1.0
	 * @return bool
	 */
	public function reset_profile(): bool {
		return $this->repository->reset_profile();
	}

	/**
	 * Get placeholder values for document generation
	 *
	 * @since 4.1.0
	 * @return array
	 */
	public function get_placeholder_values(): array {
		return $this->repository->get_placeholder_values();
	}
}
