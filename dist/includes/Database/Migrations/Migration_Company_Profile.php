<?php
/**
 * Migration: Company Profile Table
 *
 * Creates the wp_slos_company_profile table for storing company/organization
 * data used in legal document generation.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_Company_Profile
 *
 * Handles creation and management of the company profile table.
 *
 * @since 4.1.0
 */
class Migration_Company_Profile {

	/**
	 * Table name without prefix
	 */
	const TABLE_NAME = 'slos_company_profile';

	/**
	 * Migration version
	 */
	const VERSION = '4.1.0';

	/**
	 * Run the migration (create table)
	 *
	 * @since 4.1.0
	 * @return bool True on success, false on failure
	 */
	public static function up(): bool {
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			profile_data LONGTEXT NOT NULL,
			completion_percentage INT(3) UNSIGNED NOT NULL DEFAULT 0,
			version INT(10) UNSIGNED NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			updated_by BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY (id),
			KEY idx_version (version),
			KEY idx_updated_at (updated_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Verify table was created
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		) === $table_name;

		if ( $table_exists ) {
			update_option( 'slos_migration_company_profile_version', self::VERSION );
		}

		return $table_exists;
	}

	/**
	 * Reverse the migration (drop table)
	 *
	 * @since 4.1.0
	 * @return bool True on success
	 */
	public static function down(): bool {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

		delete_option( 'slos_migration_company_profile_version' );

		return true;
	}

	/**
	 * Check if migration has been applied
	 *
	 * @since 4.1.0
	 * @return bool True if table exists
	 */
	public static function is_applied(): bool {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		) === $table_name;

		return $table_exists;
	}

	/**
	 * Get the default profile structure
	 *
	 * This defines all available fields for the company profile.
	 *
	 * @since 4.1.0
	 * @return array Default profile structure
	 */
	public static function get_default_profile_structure(): array {
		return array(
			// Step 1: Company Information
			'company'         => array(
				'legal_name'          => '',
				'trading_name'        => '',
				'registration_number' => '',
				'vat_number'          => '',
				'address'             => array(
					'street'      => '',
					'city'        => '',
					'state'       => '',
					'postal_code' => '',
					'country'     => '',
				),
				'business_type'       => '',
				'industry'            => '',
			),
			// Step 2: Contact Information
			'contacts'        => array(
				'legal_email'   => '',
				'support_email' => '',
				'phone'         => '',
				'dpo'           => array(
					'name'    => '',
					'email'   => '',
					'phone'   => '',
					'address' => '',
				),
			),
			// Step 3: Website Information
			'website'         => array(
				'url'                 => '',
				'app_name'            => '',
				'service_description' => '',
				'target_audience'     => '',
			),
			// Step 4: Data Collection
			'data_collection' => array(
				'personal_data_types' => array(),
				'purposes'            => array(),
				'lawful_bases'        => array(),
				'special_categories'  => false,
				'children_data'       => false,
				'minimum_age'         => 16,
			),
			// Step 5: Third Parties
			'third_parties'   => array(
				'processors' => array(),
				'partners'   => array(),
			),
			// Step 6: Cookies
			'cookies'         => array(
				'essential'  => array(),
				'analytics'  => array(),
				'marketing'  => array(),
				'functional' => array(),
			),
			// Step 7: Legal Framework
			'legal'           => array(
				'primary_jurisdiction'  => '',
				'gdpr_applies'          => false,
				'ccpa_applies'          => false,
				'lgpd_applies'          => false,
				'supervisory_authority' => '',
				'representative_eu'     => array(
					'name'    => '',
					'email'   => '',
					'address' => '',
				),
				'representative_uk'     => array(
					'name'    => '',
					'email'   => '',
					'address' => '',
				),
			),
			// Step 8: Data Retention & Security
			'retention'       => array(
				'default_period'   => '',
				'by_category'      => array(),
				'deletion_policy'  => '',
				'backup_retention' => '',
			),
			'security'        => array(
				'measures'         => array(),
				'certifications'   => array(),
				'breach_procedure' => '',
				'dpia_required'    => false,
			),
			'user_rights'     => array(
				'response_timeframe'    => 30,
				'identity_verification' => '',
				'appeal_process'        => '',
			),
			// Metadata
			'_meta'           => array(
				'version'         => 1,
				'completion'      => 0,
				'completed_steps' => array(),
				'last_step'       => 1,
				'created_at'      => '',
				'updated_at'      => '',
			),
		);
	}

	/**
	 * Get available personal data types
	 *
	 * @since 4.1.0
	 * @return array Data types with labels
	 */
	public static function get_personal_data_types(): array {
		return array(
			'name'             => __( 'Full Name', 'shahi-legalflowsuite' ),
			'email'            => __( 'Email Address', 'shahi-legalflowsuite' ),
			'phone'            => __( 'Phone Number', 'shahi-legalflowsuite' ),
			'address'          => __( 'Physical Address', 'shahi-legalflowsuite' ),
			'ip_address'       => __( 'IP Address', 'shahi-legalflowsuite' ),
			'device_id'        => __( 'Device Identifiers', 'shahi-legalflowsuite' ),
			'location'         => __( 'Location Data', 'shahi-legalflowsuite' ),
			'payment'          => __( 'Payment Information', 'shahi-legalflowsuite' ),
			'browsing_history' => __( 'Browsing History', 'shahi-legalflowsuite' ),
			'purchase_history' => __( 'Purchase History', 'shahi-legalflowsuite' ),
			'account_data'     => __( 'Account Credentials', 'shahi-legalflowsuite' ),
			'social_profiles'  => __( 'Social Media Profiles', 'shahi-legalflowsuite' ),
			'photos'           => __( 'Photos/Images', 'shahi-legalflowsuite' ),
			'communications'   => __( 'Communications Content', 'shahi-legalflowsuite' ),
			'employment'       => __( 'Employment Information', 'shahi-legalflowsuite' ),
			'education'        => __( 'Education History', 'shahi-legalflowsuite' ),
			'health'           => __( 'Health Data', 'shahi-legalflowsuite' ),
			'biometric'        => __( 'Biometric Data', 'shahi-legalflowsuite' ),
			'financial'        => __( 'Financial Information', 'shahi-legalflowsuite' ),
			'preferences'      => __( 'User Preferences', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get available data processing purposes
	 *
	 * @since 4.1.0
	 * @return array Purposes with labels
	 */
	public static function get_processing_purposes(): array {
		return array(
			'service_delivery'   => __( 'Service Delivery', 'shahi-legalflowsuite' ),
			'account_management' => __( 'Account Management', 'shahi-legalflowsuite' ),
			'customer_support'   => __( 'Customer Support', 'shahi-legalflowsuite' ),
			'communication'      => __( 'Communication with Users', 'shahi-legalflowsuite' ),
			'marketing'          => __( 'Marketing & Promotions', 'shahi-legalflowsuite' ),
			'analytics'          => __( 'Analytics & Improvement', 'shahi-legalflowsuite' ),
			'personalization'    => __( 'Personalization', 'shahi-legalflowsuite' ),
			'security'           => __( 'Security & Fraud Prevention', 'shahi-legalflowsuite' ),
			'legal_compliance'   => __( 'Legal Compliance', 'shahi-legalflowsuite' ),
			'research'           => __( 'Research & Development', 'shahi-legalflowsuite' ),
			'advertising'        => __( 'Targeted Advertising', 'shahi-legalflowsuite' ),
			'transactions'       => __( 'Processing Transactions', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get available lawful bases (GDPR Article 6)
	 *
	 * @since 4.1.0
	 * @return array Lawful bases with labels
	 */
	public static function get_lawful_bases(): array {
		return array(
			'consent'             => __( 'Consent', 'shahi-legalflowsuite' ),
			'contract'            => __( 'Contractual Necessity', 'shahi-legalflowsuite' ),
			'legal_obligation'    => __( 'Legal Obligation', 'shahi-legalflowsuite' ),
			'vital_interests'     => __( 'Vital Interests', 'shahi-legalflowsuite' ),
			'public_task'         => __( 'Public Task', 'shahi-legalflowsuite' ),
			'legitimate_interest' => __( 'Legitimate Interest', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get available retention periods
	 *
	 * @since 4.1.0
	 * @return array Retention periods with labels
	 */
	public static function get_retention_periods(): array {
		return array(
			'session'       => __( 'Session Only', 'shahi-legalflowsuite' ),
			'30_days'       => __( '30 Days', 'shahi-legalflowsuite' ),
			'90_days'       => __( '90 Days', 'shahi-legalflowsuite' ),
			'6_months'      => __( '6 Months', 'shahi-legalflowsuite' ),
			'1_year'        => __( '1 Year', 'shahi-legalflowsuite' ),
			'2_years'       => __( '2 Years', 'shahi-legalflowsuite' ),
			'3_years'       => __( '3 Years', 'shahi-legalflowsuite' ),
			'5_years'       => __( '5 Years', 'shahi-legalflowsuite' ),
			'7_years'       => __( '7 Years', 'shahi-legalflowsuite' ),
			'10_years'      => __( '10 Years', 'shahi-legalflowsuite' ),
			'indefinite'    => __( 'Indefinite (Until Deletion Request)', 'shahi-legalflowsuite' ),
			'legal_minimum' => __( 'As Required by Law', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get list of countries
	 *
	 * @since 4.1.0
	 * @return array Country codes with names
	 */
	public static function get_countries(): array {
		return array(
			''   => __( 'Select Country...', 'shahi-legalflowsuite' ),
			'US' => __( 'United States', 'shahi-legalflowsuite' ),
			'GB' => __( 'United Kingdom', 'shahi-legalflowsuite' ),
			'CA' => __( 'Canada', 'shahi-legalflowsuite' ),
			'AU' => __( 'Australia', 'shahi-legalflowsuite' ),
			'DE' => __( 'Germany', 'shahi-legalflowsuite' ),
			'FR' => __( 'France', 'shahi-legalflowsuite' ),
			'ES' => __( 'Spain', 'shahi-legalflowsuite' ),
			'IT' => __( 'Italy', 'shahi-legalflowsuite' ),
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
			'CZ' => __( 'Czech Republic', 'shahi-legalflowsuite' ),
			'HU' => __( 'Hungary', 'shahi-legalflowsuite' ),
			'RO' => __( 'Romania', 'shahi-legalflowsuite' ),
			'BG' => __( 'Bulgaria', 'shahi-legalflowsuite' ),
			'GR' => __( 'Greece', 'shahi-legalflowsuite' ),
			'NZ' => __( 'New Zealand', 'shahi-legalflowsuite' ),
			'SG' => __( 'Singapore', 'shahi-legalflowsuite' ),
			'JP' => __( 'Japan', 'shahi-legalflowsuite' ),
			'KR' => __( 'South Korea', 'shahi-legalflowsuite' ),
			'IN' => __( 'India', 'shahi-legalflowsuite' ),
			'BR' => __( 'Brazil', 'shahi-legalflowsuite' ),
			'MX' => __( 'Mexico', 'shahi-legalflowsuite' ),
			'AR' => __( 'Argentina', 'shahi-legalflowsuite' ),
			'ZA' => __( 'South Africa', 'shahi-legalflowsuite' ),
			'AE' => __( 'United Arab Emirates', 'shahi-legalflowsuite' ),
			'IL' => __( 'Israel', 'shahi-legalflowsuite' ),
			'HK' => __( 'Hong Kong', 'shahi-legalflowsuite' ),
			'TW' => __( 'Taiwan', 'shahi-legalflowsuite' ),
			'MY' => __( 'Malaysia', 'shahi-legalflowsuite' ),
			'TH' => __( 'Thailand', 'shahi-legalflowsuite' ),
			'PH' => __( 'Philippines', 'shahi-legalflowsuite' ),
			'ID' => __( 'Indonesia', 'shahi-legalflowsuite' ),
			'VN' => __( 'Vietnam', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get EU/EEA country codes (for GDPR applicability)
	 *
	 * @since 4.1.0
	 * @return array EU/EEA country codes
	 */
	public static function get_eu_countries(): array {
		return array(
			'AT',
			'BE',
			'BG',
			'HR',
			'CY',
			'CZ',
			'DK',
			'EE',
			'FI',
			'FR',
			'DE',
			'GR',
			'HU',
			'IE',
			'IT',
			'LV',
			'LT',
			'LU',
			'MT',
			'NL',
			'PL',
			'PT',
			'RO',
			'SK',
			'SI',
			'ES',
			'SE',
			// EEA countries
			'IS',
			'LI',
			'NO',
		);
	}

	/**
	 * Check if a country is in EU/EEA
	 *
	 * @since 4.1.0
	 * @param string $country_code Country code.
	 * @return bool True if EU/EEA country
	 */
	public static function is_eu_country( string $country_code ): bool {
		return in_array( strtoupper( $country_code ), self::get_eu_countries(), true );
	}
}
