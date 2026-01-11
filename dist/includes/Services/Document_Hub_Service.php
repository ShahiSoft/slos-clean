<?php
/**
 * Document Hub Service
 *
 * Service layer for the Document Hub controller.
 * Handles data retrieval and business logic for the hub dashboard.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\Legal_Doc_Repository;
use ShahiLegalFlowSuite\Database\Repositories\Company_Profile_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document Hub Service Class
 *
 * @since 3.0.1
 */
class Document_Hub_Service {


	/**
	 * Document Repository
	 *
	 * @var Legal_Doc_Repository
	 */
	private $doc_repository;

	/**
	 * Profile Repository
	 *
	 * @var Company_Profile_Repository
	 */
	private $profile_repository;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @updated 3.1.1 - Added cookie staleness hook
	 */
	public function __construct() {
		$this->doc_repository     = new Legal_Doc_Repository();
		$this->profile_repository = Company_Profile_Repository::get_instance();

		// Hook into cookie updates to mark documents stale
		add_action( 'slos_cookies_updated', array( $this, 'mark_cookie_dependent_docs_stale' ) );
	}

	/**
	 * Get document cards data
	 *
	 * Returns configuration and status for all available document types.
	 *
	 * @since 3.0.1
	 * @return array Array of document card data
	 */
	public function get_document_cards() {
		$types = $this->get_document_types();
		$cards = array();

		foreach ( $types as $type_key => $config ) {
			// Filter out dormant documents if active list is defined
			if ( defined( 'SLOS_ACTIVE_LEGAL_DOCS' ) && is_array( SLOS_ACTIVE_LEGAL_DOCS ) ) {
				if ( ! in_array( $type_key, SLOS_ACTIVE_LEGAL_DOCS, true ) ) {
					continue; // Skip dormant documents
				}
			}

			$existing = $this->doc_repository->find_by_type( $type_key );

			$cards[] = array(
				'id'          => $type_key,
				'title'       => $config['title'],
				'description' => $config['description'],
				'icon'        => $config['icon'],
				'status'      => $existing ? $existing->status : 'not_generated',
				'updated_at'  => $existing ? $existing->updated_at : null,
				'version'     => $existing ? $existing->version : null,
				'doc_id'      => $existing ? $existing->id : 0,
				'category'    => $config['category'],
			);
		}

		return $cards;
	}

	/**
	 * Get all available document types configuration
	 *
	 * @since 3.0.1
	 * @return array Array of document type configurations
	 */
	public function get_document_types() {
		return array(
			// ========================================
			// CORE LEGAL DOCUMENTS
			// ========================================
			'privacy-policy'            => array(
				'title'       => __( 'Privacy Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Mandatory for websites collecting user data. Covers GDPR, CCPA, and other privacy laws.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-shield',
				'category'    => 'compliance',
			),
			'terms-of-service'          => array(
				'title'       => __( 'Terms of Service', 'shahi-legalflowsuite' ),
				'description' => __( 'Establishes the rules and regulations for using your website and services.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-book',
				'category'    => 'legal',
			),
			'cookie-policy'             => array(
				'title'       => __( 'Cookie Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Explains how your website uses cookies and similar tracking technologies.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-visibility',
				'category'    => 'compliance',
			),
			'disclaimer'                => array(
				'title'       => __( 'Disclaimer', 'shahi-legalflowsuite' ),
				'description' => __( 'Limits your liability for the content published on your site.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-warning',
				'category'    => 'legal',
			),
			'acceptable-use-policy'     => array(
				'title'       => __( 'Acceptable Use Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Defines acceptable behavior for users on your website, preventing misuse and illegal activities.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-yes-alt',
				'category'    => 'legal',
			),
			'copyright-dmca-policy'     => array(
				'title'       => __( 'Copyright & DMCA Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Protects your content and outlines procedures for handling copyright infringement claims.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-admin-page',
				'category'    => 'legal',
			),
			'accessibility-statement'   => array(
				'title'       => __( 'Accessibility Statement', 'shahi-legalflowsuite' ),
				'description' => __( 'Explains your website\'s commitment to accessibility (WCAG compliance) and how users can report issues.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-universal-access',
				'category'    => 'compliance',
			),

			// ========================================
			// BUSINESS OPERATIONS
			// ========================================
			'contact-imprint'           => array(
				'title'       => __( 'Contact / Imprint', 'shahi-legalflowsuite' ),
				'description' => __( 'Legal contact details, business registration info, and responsible parties. Required in EU.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-id-alt',
				'category'    => 'business',
			),
			'data-processing-agreement' => array(
				'title'       => __( 'Data Processing Agreement', 'shahi-legalflowsuite' ),
				'description' => __( 'GDPR Article 28 compliant agreement for third-party data processors.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-database',
				'category'    => 'compliance',
			),
			'security-policy'           => array(
				'title'       => __( 'Security Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Outlines data security measures and breach notification procedures.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-lock',
				'category'    => 'business',
			),
			'anti-spam-policy'          => array(
				'title'       => __( 'Anti-Spam Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Prohibits spam and outlines consequences for violations.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-email-alt2',
				'category'    => 'business',
			),

			// ========================================
			// USER CONDUCT
			// ========================================
			'terms-of-use'              => array(
				'title'       => __( 'Terms of Use', 'shahi-legalflowsuite' ),
				'description' => __( 'Simplified usage rules focusing on user conduct and site usage guidelines.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-editor-ol',
				'category'    => 'legal',
			),
			'community-guidelines'      => array(
				'title'       => __( 'Community Guidelines', 'shahi-legalflowsuite' ),
				'description' => __( 'Behavioral standards for user interactions in forums, comments, and social features.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-groups',
				'category'    => 'community',
			),
			'age-verification-policy'   => array(
				'title'       => __( 'Age Verification Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Ensures compliance with age restrictions for content or services.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-id',
				'category'    => 'compliance',
			),

			// ========================================
			// E-COMMERCE
			// ========================================
			'refund-policy'             => array(
				'title'       => __( 'Refund & Return Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Policy regarding refunds and returns for products and services.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-money-alt',
				'category'    => 'ecommerce',
			),
			'shipping-policy'           => array(
				'title'       => __( 'Shipping Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Details delivery terms, costs, timelines, and shipping regions.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-car',
				'category'    => 'ecommerce',
			),
			'warranty-policy'           => array(
				'title'       => __( 'Warranty Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Covers product warranties, guarantees, and warranty claim procedures.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-awards',
				'category'    => 'ecommerce',
			),
			'cancellation-policy'       => array(
				'title'       => __( 'Cancellation Policy', 'shahi-legalflowsuite' ),
				'description' => __( 'Explains how users can cancel services or subscriptions.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-dismiss',
				'category'    => 'ecommerce',
			),
			'subscription-agreement'    => array(
				'title'       => __( 'Subscription Agreement', 'shahi-legalflowsuite' ),
				'description' => __( 'Governs recurring payments, billing cycles, and subscription terms.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-calendar-alt',
				'category'    => 'ecommerce',
			),
			'affiliate-terms'           => array(
				'title'       => __( 'Affiliate Program Terms', 'shahi-legalflowsuite' ),
				'description' => __( 'Defines rules for affiliate partnerships, commissions, and payouts.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-networking',
				'category'    => 'ecommerce',
			),

			// ========================================
			// SOFTWARE & API
			// ========================================
			'eula'                      => array(
				'title'       => __( 'End User License Agreement', 'shahi-legalflowsuite' ),
				'description' => __( 'Grants users permission to use software, apps, or digital products.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-media-code',
				'category'    => 'software',
			),
			'api-terms'                 => array(
				'title'       => __( 'API Terms of Service', 'shahi-legalflowsuite' ),
				'description' => __( 'Governs access to and use of your website\'s API for developers.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-rest-api',
				'category'    => 'software',
			),
			'content-licensing'         => array(
				'title'       => __( 'Content Licensing Agreement', 'shahi-legalflowsuite' ),
				'description' => __( 'Grants permissions for using third-party content or licensing your content.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-format-aside',
				'category'    => 'software',
			),

			// ========================================
			// SPECIALIZED
			// ========================================
			'nda'                       => array(
				'title'       => __( 'Non-Disclosure Agreement', 'shahi-legalflowsuite' ),
				'description' => __( 'Protects confidential information shared between parties.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-hidden',
				'category'    => 'business',
			),
			'mobile-app-terms'          => array(
				'title'       => __( 'Mobile App Terms', 'shahi-legalflowsuite' ),
				'description' => __( 'Specific terms for mobile applications, covering app store rules and device-specific issues.', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-smartphone',
				'category'    => 'software',
			),
		);
	}

	/**
	 * Get single document type configuration
	 *
	 * @since 3.0.1
	 * @param string $type Document type key.
	 * @return array|null Configuration array or null if not found
	 */
	public function get_document_type( $type ) {
		$types = $this->get_document_types();
		return isset( $types[ $type ] ) ? $types[ $type ] : null;
	}

	/**
	 * Get existing document by type
	 *
	 * @since 3.0.1
	 * @param string $type Document type key.
	 * @return object|null Document object
	 */
	public function get_document_by_type( $type ) {
		return $this->doc_repository->find_by_type( $type );
	}

	/**
	 * Get categories for filter
	 *
	 * @since 3.0.1
	 * @return array Array of categories
	 */
	public function get_categories() {
		return array(
			'all'        => __( 'All Documents', 'shahi-legalflowsuite' ),
			'compliance' => __( 'Compliance', 'shahi-legalflowsuite' ),
			'legal'      => __( 'Legal Agreements', 'shahi-legalflowsuite' ),
			'business'   => __( 'Business Operations', 'shahi-legalflowsuite' ),
			'community'  => __( 'Community & Content', 'shahi-legalflowsuite' ),
			'ecommerce'  => __( 'E-Commerce', 'shahi-legalflowsuite' ),
			'software'   => __( 'Software & API', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get profile summary for the banner
	 *
	 * @since 3.0.1
	 * @return array Profile summary data
	 */
	public function get_profile_summary() {
		$profile = $this->profile_repository->get_profile();

		if ( ! $profile || empty( $profile['company']['legal_name'] ) ) {
			return array(
				'exists'       => false,
				'completeness' => 0,
				'name'         => '',
				'updated_at'   => '',
			);
		}

		// Use Profile_Validator for accurate completion calculation
		$validator    = new Profile_Validator();
		$completeness = $validator->calculate_completion( $profile );

		// Get updated timestamp from profile meta
		$meta = $this->profile_repository->get_profile_meta();

		return array(
			'exists'       => true,
			'completeness' => $completeness,
			'name'         => $profile['company']['legal_name'] ?? $profile['company']['trading_name'] ?? '',
			'updated_at'   => $meta['updated_at'] ?? '',
		);
	}

	/**
	 * Get hub statistics
	 *
	 * @since 3.0.1
	 * @return array Statistics data
	 */
	public function get_statistics() {
		global $wpdb;
		$docs_table = $wpdb->prefix . 'slos_documents';

		// Count total generated documents (excluding not_generated status)
		$total_generated = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$docs_table} WHERE status != 'not_generated'"
		);

		// Count documents that need attention (outdated)
		$outdated_docs   = $this->get_outdated_documents();
		$needs_attention = count( $outdated_docs );

		// Count up-to-date published documents
		$up_to_date = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$docs_table} WHERE status = 'published'"
		) - $needs_attention;

		if ( $up_to_date < 0 ) {
			$up_to_date = 0;
		}

		return array(
			'total_generated' => (int) $total_generated,
			'needs_attention' => (int) $needs_attention,
			'up_to_date'      => (int) $up_to_date,
		);
	}

	/**
	 * Get outdated documents
	 *
	 * @since 3.0.1
	 * @return array Array of outdated documents
	 */
	public function get_outdated_documents() {
		global $wpdb;
		$docs_table = $wpdb->prefix . 'slos_documents';

		// Get profile last updated timestamp
		$profile = $this->profile_repository->get_profile();
		if ( ! $profile || empty( $profile['updated_at'] ) ) {
			return array();
		}

		$profile_updated = $profile['updated_at'];

		// Find documents generated before profile was last updated
		$outdated = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, doc_type, title, updated_at 
             FROM {$docs_table} 
             WHERE status != 'not_generated' 
             AND (updated_at < %s OR updated_at IS NULL)
             ORDER BY updated_at ASC",
				$profile_updated
			)
		);

		return $outdated ? $outdated : array();
	}

	/**
	 * Mark cookie-dependent documents as stale
	 *
	 * Called when cookie data is updated via 'slos_cookies_updated' action.
	 *
	 * @since 3.1.1
	 * @param array $cookies Updated cookie data.
	 * @return void
	 */
	public function mark_cookie_dependent_docs_stale( $cookies = array() ) {
		// Documents that depend on cookie data
		$cookie_dependent = array( 'cookie-policy', 'privacy-policy' );

		foreach ( $cookie_dependent as $doc_type ) {
			$doc = $this->doc_repository->find_by_type( $doc_type );
			if ( $doc && $doc->id ) {
				update_post_meta( $doc->id, '_slos_needs_regeneration', true );
				update_post_meta( $doc->id, '_slos_stale_reason', 'cookie_data_changed' );
				update_post_meta( $doc->id, '_slos_stale_timestamp', time() );
			}
		}
	}

	/**
	 * Clear staleness flag for a document
	 *
	 * Call after regenerating a document to mark it as fresh.
	 *
	 * @since 3.1.1
	 * @param int $doc_id Document post ID.
	 * @return void
	 */
	public function clear_staleness( $doc_id ) {
		delete_post_meta( $doc_id, '_slos_needs_regeneration' );
		delete_post_meta( $doc_id, '_slos_stale_reason' );
		delete_post_meta( $doc_id, '_slos_stale_timestamp' );
	}

	/**
	 * Check if document is stale
	 *
	 * @since 3.1.1
	 * @param int $doc_id Document post ID.
	 * @return bool True if document needs regeneration
	 */
	public function is_document_stale( $doc_id ) {
		return (bool) get_post_meta( $doc_id, '_slos_needs_regeneration', true );
	}

	/**
	 * Get staleness reason for document
	 *
	 * @since 3.1.1
	 * @param int $doc_id Document post ID.
	 * @return string Staleness reason or empty string
	 */
	public function get_staleness_reason( $doc_id ) {
		return get_post_meta( $doc_id, '_slos_stale_reason', true );
	}
}
