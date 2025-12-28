<?php
/**
 * Document Generator Service
 *
 * Core service for generating legal documents from templates and profile data.
 * Handles template loading, placeholder resolution, and document saving.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\Company_Profile_Repository;
use ShahiLegalFlowSuite\Database\Repositories\Legal_Doc_Repository;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Document_Generator
 *
 * Generates legal documents from templates using company profile data.
 *
 * @since 4.1.0
 */
class Document_Generator extends Base_Service {

	/**
	 * Profile validator instance
	 *
	 * @var Profile_Validator
	 */
	protected $validator;

	/**
	 * Placeholder mapper instance
	 *
	 * @var Placeholder_Mapper
	 */
	protected $mapper;

	/**
	 * Legal doc repository instance
	 *
	 * @var Legal_Doc_Repository
	 */
	protected $doc_repository;

	/**
	 * Company profile repository instance
	 *
	 * @var Company_Profile_Repository
	 */
	protected $profile_repository;

	/**
	 * Available document types
	 *
	 * @var array
	 */
	const DOCUMENT_TYPES = array(
		// Core Legal Documents
		'privacy-policy'           => 'Privacy Policy',
		'terms-of-service'         => 'Terms of Service',
		'cookie-policy'            => 'Cookie Policy',
		'disclaimer'               => 'Disclaimer',
		'acceptable-use-policy'    => 'Acceptable Use Policy',
		'copyright-dmca-policy'    => 'Copyright & DMCA Policy',
		'accessibility-statement'  => 'Accessibility Statement',
		
		// Business Operations
		'contact-imprint'          => 'Contact Information / Imprint',
		'data-processing-agreement' => 'Data Processing Agreement',
		'security-policy'          => 'Security Policy',
		'anti-spam-policy'         => 'Anti-Spam Policy',
		
		// User Conduct
		'terms-of-use'             => 'Terms of Use',
		'community-guidelines'     => 'Community Guidelines',
		'age-verification-policy'  => 'Age Verification Policy',
		
		// E-Commerce
		'refund-policy'            => 'Refund & Return Policy',
		'shipping-policy'          => 'Shipping Policy',
		'warranty-policy'          => 'Warranty Policy',
		'cancellation-policy'      => 'Cancellation Policy',
		'subscription-agreement'   => 'Subscription Agreement',
		'affiliate-terms'          => 'Affiliate Program Terms',
		
		// Software & API
		'eula'                     => 'End User License Agreement',
		'api-terms'                => 'API Terms of Service',
		'content-licensing'        => 'Content Licensing Agreement',
		
		// Specialized
		'nda'                      => 'Non-Disclosure Agreement',
		'mobile-app-terms'         => 'Mobile App Terms',
	);

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 * @param Profile_Validator|null          $validator          Validator instance.
	 * @param Placeholder_Mapper|null         $mapper             Mapper instance.
	 * @param Legal_Doc_Repository|null       $doc_repository     Doc repository instance.
	 * @param Company_Profile_Repository|null $profile_repository Profile repository instance.
	 */
	public function __construct(
		Profile_Validator $validator = null,
		Placeholder_Mapper $mapper = null,
		Legal_Doc_Repository $doc_repository = null,
		Company_Profile_Repository $profile_repository = null
	) {
		parent::__construct();

		$this->validator          = $validator ?? new Profile_Validator();
		$this->mapper             = $mapper ?? new Placeholder_Mapper();
		$this->doc_repository     = $doc_repository ?? new Legal_Doc_Repository();
		$this->profile_repository = $profile_repository ?? Company_Profile_Repository::get_instance();
	}

	/**
	 * Generate a legal document from profile data
	 *
	 * Main entry point for document generation.
	 *
	 * @since 4.1.0
	 * @param string   $document_type Type of document to generate (e.g., 'privacy-policy').
	 * @param int|null $profile_id    Profile ID (null for current site default).
	 * @param array    $options       Generation options.
	 * @return array|\WP_Error Generated document data or error
	 */
	public function generate( string $document_type, int $profile_id = null, array $options = array() ) {
		// Validate document type
		if ( ! $this->is_valid_type( $document_type ) ) {
			return new \WP_Error(
				'invalid_document_type',
				/* translators: %s: document type */
				sprintf( __( 'Invalid document type: %s', 'shahi-legalflowsuite' ), $document_type )
			);
		}

		// Get company profile
		$profile = $this->get_profile( $profile_id );
		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		// Validate profile is ready for generation
		$validation = $this->validate_generation_ready( $profile, $document_type );
		if ( ! $validation['ready'] ) {
			return new \WP_Error(
				'profile_incomplete',
				__( 'Company profile is incomplete.', 'shahi-legalflowsuite' ),
				array( 'missing_fields' => $validation['missing'] )
			);
		}

		// Load template
		$template = $this->load_template( $document_type, $options );
		if ( is_wp_error( $template ) ) {
			return $template;
		}

		// Resolve placeholders
		$content = $this->resolve_placeholders( $template, $profile );

		// Add legal disclaimer
		$content = $this->add_legal_disclaimer( $content, $document_type );

		// Build document data
		$document = array(
			'type'        => $document_type,
			'title'       => $this->get_document_title( $document_type ),
			'content'     => $content,
			'profile_id'  => $profile_id ?? 0,
			'status'      => 'draft',
			'metadata'    => array(
				'generated_at'    => current_time( 'mysql' ),
				'generator_version' => defined( 'SLOS_GEN_VERSION' ) ? SLOS_GEN_VERSION : '4.1.0',
				'template_version'  => $options['template_version'] ?? '1.0',
				'profile_hash'    => $this->hash_profile( $profile ),
				'locale'          => get_locale(),
			),
		);

		/**
		 * Filter generated document before saving
		 *
		 * @since 4.1.0
		 * @param array  $document      Document data.
		 * @param string $document_type Document type.
		 * @param array  $profile       Profile data.
		 * @param array  $options       Generation options.
		 */
		$document = apply_filters( 'slos_document_generated', $document, $document_type, $profile, $options );

		// Auto-save if requested
		if ( ! empty( $options['auto_save'] ) ) {
			$saved = $this->save_as_draft( $document );
			if ( is_wp_error( $saved ) ) {
				return $saved;
			}
			$document['id'] = $saved;
		}

		return $document;
	}

	/**
	 * Generate all available documents
	 *
	 * @since 4.1.0
	 * @param int|null $profile_id Profile ID.
	 * @param array    $options    Generation options.
	 * @return array Results array with document types as keys
	 */
	public function generate_all( int $profile_id = null, array $options = array() ): array {
		$results = array();

		foreach ( array_keys( self::DOCUMENT_TYPES ) as $type ) {
			$results[ $type ] = $this->generate( $type, $profile_id, $options );
		}

		return $results;
	}

	/**
	 * Validate profile is ready for document generation
	 *
	 * @since 4.1.0
	 * @param array  $profile       Profile data.
	 * @param string $document_type Document type.
	 * @return array Validation result with 'ready' and 'missing' keys
	 */
	public function validate_generation_ready( array $profile, string $document_type ) {
		$validation_result = $this->validator->validate_for_generation( $profile );

		// validate_for_generation returns true|\WP_Error
		$is_ready = ( true === $validation_result );
		$missing_fields = array();

		if ( is_wp_error( $validation_result ) ) {
			$error_data = $validation_result->get_error_data();
			$missing_fields = $error_data['missing'] ?? array();
			// Convert to simple labels for UI
			$missing_fields = array_map( function( $field ) {
				return $field['label'] ?? $field['field'] ?? '';
			}, $missing_fields );
		}

		// Document-specific validation
		switch ( $document_type ) {
			case 'cookie-policy':
				// Ensure cookie data exists
				$cookies = $profile['cookies'] ?? array();
				if ( empty( $cookies['essential'] ) ) {
					$is_ready = false;
					$missing_fields[] = __( 'Essential Cookies', 'shahi-legalflowsuite' );
				}
				break;

			case 'terms-of-service':
				// Ensure service description exists
				$website = $profile['website'] ?? array();
				if ( empty( $website['service_description'] ) ) {
					$is_ready = false;
					$missing_fields[] = __( 'Service Description', 'shahi-legalflowsuite' );
				}
				break;
		}

		/**
		 * Filter document-specific validation
		 *
		 * @since 4.1.0
		 * @param array  $result        Validation result with 'ready' and 'missing' keys.
		 * @param array  $profile       Profile data.
		 * @param string $document_type Document type.
		 */
		$result = array(
			'ready'   => $is_ready,
			'missing' => $missing_fields,
		);

		return apply_filters( 'slos_validate_document_generation', $result, $profile, $document_type );
	}

	/**
	 * Load document template
	 *
	 * @since 4.1.0
	 * @param string $document_type Document type.
	 * @param array  $options       Options including template_version.
	 * @return string|\WP_Error Template content or error
	 */
	protected function load_template( string $document_type, array $options = array() ) {
		$template_dir = $this->get_template_directory();
		$template_file = $template_dir . '/' . $document_type . '.html';

		// Debug logging
		error_log( 'SLOS Template dir: ' . $template_dir );
		error_log( 'SLOS Template file: ' . $template_file );
		error_log( 'SLOS File exists: ' . ( file_exists( $template_file ) ? 'yes' : 'no' ) );

		// Check for custom template first
		$custom_template = $this->get_custom_template( $document_type );
		if ( $custom_template ) {
			$template_file = $custom_template;
		}

		/**
		 * Filter template file path
		 *
		 * @since 4.1.0
		 * @param string $template_file Template file path.
		 * @param string $document_type Document type.
		 * @param array  $options       Options.
		 */
		$template_file = apply_filters( 'slos_document_template_file', $template_file, $document_type, $options );

		if ( ! file_exists( $template_file ) ) {
			error_log( 'SLOS Template NOT FOUND: ' . $template_file );
			return new \WP_Error(
				'template_not_found',
				/* translators: %s: document type */
				sprintf( __( 'Template not found for document type: %s', 'shahi-legalflowsuite' ), $document_type ),
				array( 'path' => $template_file )
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $template_file );

		if ( false === $content ) {
			return new \WP_Error(
				'template_read_error',
				__( 'Failed to read template file.', 'shahi-legalflowsuite' )
			);
		}

		return $content;
	}

	/**
	 * Resolve placeholders in template
	 *
	 * @since 4.1.0
	 * @param string $template Template content.
	 * @param array  $profile  Profile data.
	 * @return string Resolved content
	 */
	public function resolve_placeholders( string $template, array $profile ): string {
		return $this->mapper->parse_template( $template, $profile );
	}

	/**
	 * Add legal disclaimer to document
	 *
	 * @since 4.1.0
	 * @param string $content       Document content.
	 * @param string $document_type Document type.
	 * @return string Content with disclaimer
	 */
	public function add_legal_disclaimer( string $content, string $document_type ): string {
		$disclaimer = $this->get_disclaimer( $document_type );

		if ( empty( $disclaimer ) ) {
			return $content;
		}

		// Add disclaimer as footer
		$content .= "\n\n" . '<div class="slos-legal-disclaimer">' . wp_kses_post( $disclaimer ) . '</div>';

		return $content;
	}

	/**
	 * Get disclaimer for document type
	 *
	 * @since 4.1.0
	 * @param string $document_type Document type.
	 * @return string Disclaimer text
	 */
	protected function get_disclaimer( string $document_type ): string {
		$disclaimer = __( 'This document was generated automatically. We recommend reviewing it with a legal professional to ensure compliance with applicable laws in your jurisdiction.', 'shahi-legalflowsuite' );

		/**
		 * Filter document disclaimer
		 *
		 * @since 4.1.0
		 * @param string $disclaimer    Disclaimer text.
		 * @param string $document_type Document type.
		 */
		return apply_filters( 'slos_document_disclaimer', $disclaimer, $document_type );
	}

	/**
	 * Save document as draft
	 *
	 * @since 4.1.0
	 * @param array $document Document data.
	 * @return int|\WP_Error Document ID or error
	 */
	public function save_as_draft( array $document ) {
		$doc_data = array(
			'doc_type'      => $document['type'] ?? $document['doc_type'] ?? '',
			'title'         => $document['title'] ?? '',
			'content'       => $document['content'] ?? '',
			'status'        => 'draft',
			'profile_id'    => $document['profile_id'] ?? 0,
			'metadata'      => wp_json_encode( $document['metadata'] ?? array() ),
		);

		// Debug log
		error_log( 'SLOS save_as_draft doc_data: ' . print_r( $doc_data, true ) );

		$result = $this->doc_repository->save( $doc_data );

		error_log( 'SLOS save_as_draft result: ' . var_export( $result, true ) );

		if ( ! $result ) {
			global $wpdb;
			error_log( 'SLOS save_as_draft DB error: ' . $wpdb->last_error );
			return new \WP_Error(
				'save_failed',
				__( 'Failed to save document.', 'shahi-legalflowsuite' ),
				array( 'db_error' => $wpdb->last_error )
			);
		}

		// Create version history entry
		if ( is_numeric( $result ) ) {
			$this->doc_repository->create_version(
				$result,
				array(
					'content'       => $document['content'] ?? '',
					'change_reason' => __( 'Initial generation', 'shahi-legalflowsuite' ),
					'metadata'      => $document['metadata'] ?? array(),
				)
			);
		}

		/**
		 * Fires after document is saved as draft
		 *
		 * @since 4.1.0
		 * @param int   $result   Document ID.
		 * @param array $document Document data.
		 */
		do_action( 'slos_document_saved', $result, $document );

		return $result;
	}

	/**
	 * Publish a draft document
	 *
	 * @since 4.1.0
	 * @param int $document_id Document ID.
	 * @return bool|\WP_Error Success or error
	 */
	public function publish( int $document_id ) {
		$document = $this->normalize_document_data( $this->doc_repository->find_by_id( $document_id ) );

		if ( ! $document ) {
			return new \WP_Error(
				'document_not_found',
				__( 'Document not found.', 'shahi-legalflowsuite' )
			);
		}

		$result = $this->doc_repository->save(
			array(
				'id'           => $document_id,
				'status'       => 'published',
				'published_at' => current_time( 'mysql' ),
			)
		);

		if ( ! $result ) {
			return new \WP_Error(
				'publish_failed',
				__( 'Failed to publish document.', 'shahi-legalflowsuite' )
			);
		}

		/**
		 * Fires after document is published
		 *
		 * @since 4.1.0
		 * @param int   $document_id Document ID.
		 * @param array $document    Document data.
		 */
		do_action( 'slos_document_published', $document_id, $document );

		return true;
	}

	/**
	 * Regenerate an existing document
	 *
	 * @since 4.1.0
	 * @param int   $document_id Document ID.
	 * @param array $options     Generation options.
	 * @return array|\WP_Error Updated document or error
	 */
	public function regenerate( int $document_id, array $options = array() ) {
		$existing = $this->normalize_document_data( $this->doc_repository->find_by_id( $document_id ) );

		if ( ! $existing ) {
			return new \WP_Error(
				'document_not_found',
				__( 'Document not found.', 'shahi-legalflowsuite' )
			);
		}

		// Generate fresh content
		$profile_id = $existing['profile_id'] ?? null;
		$new_document = $this->generate( $existing['doc_type'], $profile_id, $options );

		if ( is_wp_error( $new_document ) ) {
			return $new_document;
		}

		// Create version of old content before updating
		$this->doc_repository->create_version(
			$document_id,
			array(
				'content'       => $existing['content'],
				'change_reason' => __( 'Pre-regeneration backup', 'shahi-legalflowsuite' ),
			)
		);

		// Update existing document
		$update_data = array(
			'id'           => $document_id,
			'content'      => $new_document['content'],
			'metadata'     => wp_json_encode( $new_document['metadata'] ),
			'updated_at'   => current_time( 'mysql' ),
		);

		$result = $this->doc_repository->save( $update_data );

		if ( ! $result ) {
			return new \WP_Error(
				'regenerate_failed',
				__( 'Failed to regenerate document.', 'shahi-legalflowsuite' )
			);
		}

		// Create new version entry
		$this->doc_repository->create_version(
			$document_id,
			array(
				'content'       => $new_document['content'],
				'change_reason' => __( 'Regenerated from profile', 'shahi-legalflowsuite' ),
			)
		);

		$new_document['id'] = $document_id;

		/**
		 * Fires after document is regenerated
		 *
		 * @since 4.1.0
		 * @param int   $document_id  Document ID.
		 * @param array $new_document New document data.
		 * @param array $existing     Previous document data.
		 */
		do_action( 'slos_document_regenerated', $document_id, $new_document, $existing );

		return $new_document;
	}

	/**
	 * Preview document without saving
	 *
	 * @since 4.1.0
	 * @param string   $document_type Document type.
	 * @param int|null $profile_id    Profile ID.
	 * @return array|\WP_Error Preview data or error
	 */
	public function preview( string $document_type, int $profile_id = null ) {
		$options = array( 'auto_save' => false );
		return $this->generate( $document_type, $profile_id, $options );
	}

	/**
	 * Check if profile has changed since last generation
	 *
	 * @since 4.1.0
	 * @param int $document_id Document ID.
	 * @return bool True if profile changed
	 */
	public function has_profile_changed( int $document_id ): bool {
		$document = $this->normalize_document_data( $this->doc_repository->find_by_id( $document_id ) );

		if ( ! $document ) {
			return false;
		}

		// Normalize repository result for consistent access
		if ( is_object( $document ) ) {
			$document = get_object_vars( $document );
		}

		if ( isset( $document['metadata'] ) && is_object( $document['metadata'] ) ) {
			$document['metadata'] = get_object_vars( $document['metadata'] );
		}

		$metadata = json_decode( $document['metadata'] ?? '{}', true );
		$stored_hash = $metadata['profile_hash'] ?? '';

		if ( empty( $stored_hash ) ) {
			return true;
		}

		// Get current profile
		$profile_id = $document['profile_id'] ?? null;
		$profile = $this->get_profile( $profile_id );

		if ( is_wp_error( $profile ) ) {
			return true;
		}

		$current_hash = $this->hash_profile( $profile );

		return $stored_hash !== $current_hash;
	}

	/**
	 * Get company profile
	 *
	 * @since 4.1.0
	 * @param int|null $profile_id Profile ID.
	 * @return array|\WP_Error Profile data or error
	 */
	protected function get_profile( int $profile_id = null ) {
		$profile = $this->profile_repository->get_profile();

		if ( ! $profile || empty( $profile['company']['legal_name'] ) ) {
			return new \WP_Error(
				'profile_not_found',
				__( 'Company profile not found.', 'shahi-legalflowsuite' )
			);
		}

		return $profile;
	}

	/**
	 * Hash profile for change detection
	 *
	 * @since 4.1.0
	 * @param array $profile Profile data.
	 * @return string Hash string
	 */
	protected function hash_profile( array $profile ): string {
		// Remove volatile fields
		unset( $profile['created_at'], $profile['updated_at'] );
		return md5( wp_json_encode( $profile ) );
	}

	/**
	 * Normalize repository document results to associative array.
	 *
	 * @since 4.2.0
	 * @param mixed $document Raw document object/array/null.
	 * @return array Normalized document array.
	 */
	private function normalize_document_data( $document ): array {
		if ( ! $document ) {
			return array();
		}

		if ( is_object( $document ) ) {
			$document = get_object_vars( $document );
		}

		if ( isset( $document['metadata'] ) && is_object( $document['metadata'] ) ) {
			$document['metadata'] = get_object_vars( $document['metadata'] );
		}

		return $document;
	}

	/**
	 * Get template directory path
	 *
	 * @since 4.1.0
	 * @return string Directory path
	 */
	protected function get_template_directory(): string {
		// Use plugin constant for reliable path
		$default = defined( 'SHAHI_LEGALFLOWSUITE_PLUGIN_DIR' )
			? SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'templates/legaldocs'
			: dirname( __DIR__, 2 ) . '/templates/legaldocs';

		/**
		 * Filter template directory
		 *
		 * @since 4.1.0
		 * @param string $default Default directory path.
		 */
		return apply_filters( 'slos_legaldocs_template_dir', $default );
	}

	/**
	 * Get custom template path from theme
	 *
	 * @since 4.1.0
	 * @param string $document_type Document type.
	 * @return string|false Custom template path or false
	 */
	protected function get_custom_template( string $document_type ) {
		$theme_template = get_stylesheet_directory() . '/shahi-legalflowsuite/legaldocs/' . $document_type . '.html';

		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}

		return false;
	}

	/**
	 * Check if document type is valid
	 *
	 * @since 4.1.0
	 * @param string $type Document type.
	 * @return bool True if valid
	 */
	public function is_valid_type( string $type ): bool {
		return array_key_exists( $type, self::DOCUMENT_TYPES );
	}

	/**
	 * Get document title by type
	 *
	 * @since 4.1.0
	 * @param string $type Document type.
	 * @return string Title
	 */
	public function get_document_title( string $type ): string {
		return self::DOCUMENT_TYPES[ $type ] ?? ucwords( str_replace( '-', ' ', $type ) );
	}

	/**
	 * Get all available document types
	 *
	 * @since 4.1.0
	 * @return array Document types
	 */
	public function get_document_types(): array {
		/**
		 * Filter available document types
		 *
		 * @since 4.1.0
		 * @param array $types Document types.
		 */
		return apply_filters( 'slos_document_types', self::DOCUMENT_TYPES );
	}

	/**
	 * Get generation context for pre-generation review modal
	 *
	 * Returns profile data, validation status, and field information for
	 * the pre-generation review UI.
	 *
	 * @since 4.2.0
	 * @param string $document_type Document type to generate.
	 * @return array Context data including profile, validation, and fields
	 */
	public function get_generation_context( string $document_type ): array {
		// Validate document type
		if ( ! $this->is_valid_type( $document_type ) ) {
			return array(
				'is_valid'       => false,
				'error'          => __( 'Invalid document type.', 'shahi-legalflowsuite' ),
				'missing_fields' => array(),
			);
		}

		// Get current profile
		$profile_data = $this->get_profile();

		if ( is_wp_error( $profile_data ) ) {
			return array(
				'is_valid'       => false,
				'error'          => $profile_data->get_error_message(),
				'missing_fields' => array(),
			);
		}

		// Validate profile for this document type
		$validation = $this->validate_generation_ready( $profile_data, $document_type );

		// Build field map for UI
		$field_map = $this->build_context_fields( $profile_data, $document_type );

		// Check for existing document
		$existing_doc = $this->doc_repository->find_by_type( $document_type );
		$is_outdated  = false;

		if ( $existing_doc ) {
			$is_outdated = $this->has_profile_changed( $existing_doc->id ?? $existing_doc['id'] ?? 0 );
		}

		return array(
			'is_valid'        => $validation['ready'],
			'missing_fields'  => $validation['missing'] ?? array(),
			'profile'         => $profile_data,
			'fields'          => $field_map,
			'document_type'   => $document_type,
			'document_title'  => $this->get_document_title( $document_type ),
			'existing_doc_id' => $existing_doc ? ( $existing_doc->id ?? $existing_doc['id'] ?? 0 ) : 0,
			'is_outdated'     => $is_outdated,
			'can_generate'    => $validation['ready'],
		);
	}

	/**
	 * Build context fields for pre-generation review
	 *
	 * Creates a structured field list for the UI showing what data
	 * will be used in document generation.
	 *
	 * @since 4.2.0
	 * @param array  $profile       Profile data.
	 * @param string $document_type Document type.
	 * @return array Structured fields for UI
	 */
	protected function build_context_fields( array $profile, string $document_type ): array {
		$fields = array();

		// Company section
		$company = $profile['company'] ?? array();
		$fields['company'] = array(
			'label'  => __( 'Company Information', 'shahi-legalflowsuite' ),
			'fields' => array(
				array(
					'key'      => 'company.legal_name',
					'label'    => __( 'Legal Name', 'shahi-legalflowsuite' ),
					'value'    => $company['legal_name'] ?? '',
					'required' => true,
					'editable' => true,
				),
				array(
					'key'      => 'company.trading_name',
					'label'    => __( 'Trading Name', 'shahi-legalflowsuite' ),
					'value'    => $company['trading_name'] ?? '',
					'required' => false,
					'editable' => true,
				),
				array(
					'key'      => 'company.address',
					'label'    => __( 'Address', 'shahi-legalflowsuite' ),
					'value'    => $this->format_address_for_display( $company['address'] ?? array() ),
					'required' => true,
					'editable' => false, // Complex field
				),
				array(
					'key'      => 'company.business_type',
					'label'    => __( 'Business Type', 'shahi-legalflowsuite' ),
					'value'    => $company['business_type'] ?? '',
					'required' => true,
					'editable' => true,
				),
			),
		);

		// Contacts section
		$contacts = $profile['contacts'] ?? array();
		$dpo      = $contacts['dpo'] ?? array();
		$fields['contacts'] = array(
			'label'  => __( 'Contact Information', 'shahi-legalflowsuite' ),
			'fields' => array(
				array(
					'key'      => 'contacts.legal_email',
					'label'    => __( 'Legal Contact Email', 'shahi-legalflowsuite' ),
					'value'    => $contacts['legal_email'] ?? '',
					'required' => true,
					'editable' => true,
				),
				array(
					'key'      => 'contacts.dpo.email',
					'label'    => __( 'DPO Email', 'shahi-legalflowsuite' ),
					'value'    => $dpo['email'] ?? '',
					'required' => true,
					'editable' => true,
				),
				array(
					'key'      => 'contacts.dpo.name',
					'label'    => __( 'DPO Name', 'shahi-legalflowsuite' ),
					'value'    => $dpo['name'] ?? '',
					'required' => false,
					'editable' => true,
				),
			),
		);

		// Website section
		$website = $profile['website'] ?? array();
		$fields['website'] = array(
			'label'  => __( 'Website Information', 'shahi-legalflowsuite' ),
			'fields' => array(
				array(
					'key'      => 'website.url',
					'label'    => __( 'Website URL', 'shahi-legalflowsuite' ),
					'value'    => $website['url'] ?? get_site_url(),
					'required' => true,
					'editable' => true,
				),
				array(
					'key'      => 'website.service_description',
					'label'    => __( 'Service Description', 'shahi-legalflowsuite' ),
					'value'    => $website['service_description'] ?? '',
					'required' => true,
					'editable' => true,
				),
			),
		);

		// Data Collection section (especially for Privacy Policy)
		if ( 'privacy-policy' === $document_type ) {
			$data_collection = $profile['data_collection'] ?? array();
			$fields['data_collection'] = array(
				'label'  => __( 'Data Collection', 'shahi-legalflowsuite' ),
				'fields' => array(
					array(
						'key'      => 'data_collection.personal_data_types',
						'label'    => __( 'Personal Data Types', 'shahi-legalflowsuite' ),
						'value'    => implode( ', ', $data_collection['personal_data_types'] ?? array() ),
						'required' => true,
						'editable' => false, // Array field
					),
					array(
						'key'      => 'data_collection.purposes',
						'label'    => __( 'Processing Purposes', 'shahi-legalflowsuite' ),
						'value'    => implode( ', ', $data_collection['purposes'] ?? array() ),
						'required' => true,
						'editable' => false, // Array field
					),
				),
			);
		}

		// Cookie section (for Cookie Policy)
		if ( 'cookie-policy' === $document_type ) {
			$cookies = $profile['cookies'] ?? array();
			$fields['cookies'] = array(
				'label'  => __( 'Cookie Information', 'shahi-legalflowsuite' ),
				'fields' => array(
					array(
						'key'      => 'cookies.essential',
						'label'    => __( 'Essential Cookies', 'shahi-legalflowsuite' ),
						'value'    => count( $cookies['essential'] ?? array() ) . ' ' . __( 'cookies defined', 'shahi-legalflowsuite' ),
						'required' => true,
						'editable' => false,
					),
				),
			);
		}

		// Legal section
		$legal = $profile['legal'] ?? array();
		$fields['legal'] = array(
			'label'  => __( 'Legal Settings', 'shahi-legalflowsuite' ),
			'fields' => array(
				array(
					'key'      => 'legal.primary_jurisdiction',
					'label'    => __( 'Primary Jurisdiction', 'shahi-legalflowsuite' ),
					'value'    => $legal['primary_jurisdiction'] ?? '',
					'required' => true,
					'editable' => true,
				),
				array(
					'key'      => 'legal.gdpr_applies',
					'label'    => __( 'GDPR Applies', 'shahi-legalflowsuite' ),
					'value'    => ( $legal['gdpr_applies'] ?? false ) ? __( 'Yes', 'shahi-legalflowsuite' ) : __( 'No', 'shahi-legalflowsuite' ),
					'required' => false,
					'editable' => false,
				),
			),
		);

		// Retention section
		$retention = $profile['retention'] ?? array();
		$fields['retention'] = array(
			'label'  => __( 'Data Retention', 'shahi-legalflowsuite' ),
			'fields' => array(
				array(
					'key'      => 'retention.default_period',
					'label'    => __( 'Default Retention Period', 'shahi-legalflowsuite' ),
					'value'    => $retention['default_period'] ?? '',
					'required' => true,
					'editable' => true,
				),
			),
		);

		/**
		 * Filter context fields for generation review
		 *
		 * @since 4.2.0
		 * @param array  $fields        Structured fields.
		 * @param array  $profile       Profile data.
		 * @param string $document_type Document type.
		 */
		return apply_filters( 'slos_generation_context_fields', $fields, $profile, $document_type );
	}

	/**
	 * Format address for display in context review
	 *
	 * @since 4.2.0
	 * @param array $address Address data.
	 * @return string Formatted address
	 */
	protected function format_address_for_display( array $address ): string {
		$parts = array_filter( array(
			$address['street'] ?? '',
			$address['city'] ?? '',
			$address['state'] ?? '',
			$address['postal_code'] ?? '',
			$address['country'] ?? '',
		) );

		return implode( ', ', $parts );
	}

	/**
	 * Generate document preview without saving
	 *
	 * Creates a document preview with optional field overrides.
	 * Does not save to database.
	 *
	 * @since 4.2.0
	 * @param string $document_type Document type.
	 * @param array  $overrides     Field overrides from UI.
	 * @return string|\WP_Error HTML content or error
	 */
	public function generate_preview( string $document_type, array $overrides = array() ) {
		// Validate document type
		if ( ! $this->is_valid_type( $document_type ) ) {
			return new \WP_Error(
				'invalid_document_type',
				__( 'Invalid document type.', 'shahi-legalflowsuite' )
			);
		}

		// Get profile and apply overrides
		$profile = $this->get_profile();

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		// Apply any field overrides from the UI
		if ( ! empty( $overrides ) ) {
			$profile = $this->apply_overrides( $profile, $overrides );
		}

		// Load template
		$template = $this->load_template( $document_type, array( 'preview' => true ) );

		if ( is_wp_error( $template ) ) {
			return $template;
		}

		// Resolve placeholders
		$content = $this->resolve_placeholders( $template, $profile );

		// Add legal disclaimer
		$content = $this->add_legal_disclaimer( $content, $document_type );

		/**
		 * Filter preview content before returning
		 *
		 * @since 4.2.0
		 * @param string $content       Preview HTML content.
		 * @param string $document_type Document type.
		 * @param array  $profile       Profile data used.
		 */
		return apply_filters( 'slos_document_preview_content', $content, $document_type, $profile );
	}

	/**
	 * Apply field overrides to profile data
	 *
	 * @since 4.2.0
	 * @param array $profile   Original profile data.
	 * @param array $overrides Field overrides (dot notation keys).
	 * @return array Modified profile data
	 */
	protected function apply_overrides( array $profile, array $overrides ): array {
		foreach ( $overrides as $key => $value ) {
			// Support dot notation: company.legal_name
			$keys    = explode( '.', $key );
			$current = &$profile;

			foreach ( $keys as $i => $k ) {
				if ( $i === count( $keys ) - 1 ) {
					$current[ $k ] = $value;
				} else {
					if ( ! isset( $current[ $k ] ) || ! is_array( $current[ $k ] ) ) {
						$current[ $k ] = array();
					}
					$current = &$current[ $k ];
				}
			}
		}

		return $profile;
	}

	/**
	 * Generate document from profile and save as draft
	 *
	 * Main method for the generation flow. Creates a new document
	 * from profile data and saves it as a draft for review.
	 *
	 * @since 4.2.0
	 * @param string $document_type Document type to generate.
	 * @param array  $overrides     Field overrides from UI.
	 * @param int    $user_id       User ID performing generation.
	 * @return int|\WP_Error Document ID or error
	 */
	public function generate_from_profile( string $document_type, array $overrides = array(), int $user_id = 0 ) {
		// Validate document type
		if ( ! $this->is_valid_type( $document_type ) ) {
			return new \WP_Error(
				'invalid_document_type',
				__( 'Invalid document type.', 'shahi-legalflowsuite' )
			);
		}

		// Get current profile
		$profile = $this->get_profile();

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		// Validate profile is ready
		$validation = $this->validate_generation_ready( $profile, $document_type );

		if ( ! $validation['ready'] ) {
			return new \WP_Error(
				'profile_incomplete',
				__( 'Profile is missing required fields for this document.', 'shahi-legalflowsuite' ),
				array( 'missing_fields' => $validation['missing'] )
			);
		}

		// Apply overrides if any
		if ( ! empty( $overrides ) && isset( $overrides['change_reason'] ) ) {
			$change_reason = sanitize_text_field( $overrides['change_reason'] );
			unset( $overrides['change_reason'] );
		} else {
			$change_reason = __( 'Generated from profile', 'shahi-legalflowsuite' );
		}

		if ( ! empty( $overrides ) ) {
			$profile = $this->apply_overrides( $profile, $overrides );
		}

		// Set user ID
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Check if document already exists
		$existing = $this->normalize_document_data( $this->doc_repository->find_by_type( $document_type ) );

		if ( $existing ) {
			// Regenerate existing document
			$doc_id = $existing['id'] ?? 0;

			// Store old version before regenerating
			$this->doc_repository->create_version(
				$doc_id,
				array(
					'content'       => $existing['content'] ?? '',
					'change_reason' => __( 'Pre-regeneration backup', 'shahi-legalflowsuite' ),
				)
			);

			// Generate fresh content
			$options = array( 'auto_save' => false );
			$result  = $this->generate( $document_type, null, $options );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			// Update existing document
			$update_data = array(
				'id'         => $doc_id,
				'content'    => $result['content'],
				'metadata'   => wp_json_encode( array_merge(
					$result['metadata'] ?? array(),
					array(
						'regenerated_by' => $user_id,
						'regenerated_at' => current_time( 'mysql' ),
						'change_reason'  => $change_reason,
					)
				) ),
				'updated_at' => current_time( 'mysql' ),
				'updated_by' => $user_id,
			);

			$updated = $this->doc_repository->save( $update_data );

			if ( ! $updated ) {
				return new \WP_Error(
					'save_failed',
					__( 'Failed to update document.', 'shahi-legalflowsuite' )
				);
			}

			// Create new version entry
			$this->doc_repository->create_version(
				$doc_id,
				array(
					'content'       => $result['content'],
					'change_reason' => $change_reason,
				)
			);

			/**
			 * Fires after document is regenerated from profile
			 *
			 * @since 4.2.0
			 * @param int    $doc_id        Document ID.
			 * @param string $document_type Document type.
			 * @param array  $profile       Profile data used.
			 * @param int    $user_id       User who generated.
			 */
			do_action( 'slos_document_regenerated_from_profile', $doc_id, $document_type, $profile, $user_id );

			return $doc_id;
		}

		// Generate new document
		$options = array( 'auto_save' => false );
		$result  = $this->generate( $document_type, null, $options );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Prepare document data for saving
		$document_data = array(
			'title'      => $this->get_document_title( $document_type ),
			'content'    => $result['content'],
			'doc_type'   => $document_type,
			'status'     => 'draft',
			'locale'     => get_locale(),
			'version'    => '1.0',
			'metadata'   => wp_json_encode( array_merge(
				$result['metadata'] ?? array(),
				array(
					'generated_by' => $user_id,
					'generated_at' => current_time( 'mysql' ),
					'change_reason' => $change_reason,
				)
			) ),
			'created_by' => $user_id,
			'updated_by' => $user_id,
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		);

		// Save to database
		$doc_id = $this->save_as_draft( $document_data );

		if ( is_wp_error( $doc_id ) ) {
			return $doc_id;
		}

		// Create initial version entry
		$this->doc_repository->create_version(
			$doc_id,
			array(
				'content'       => $result['content'],
				'change_reason' => $change_reason,
			)
		);

		/**
		 * Fires after document is generated from profile
		 *
		 * @since 4.2.0
		 * @param int    $doc_id        Document ID.
		 * @param string $document_type Document type.
		 * @param array  $profile       Profile data used.
		 * @param int    $user_id       User who generated.
		 */
		do_action( 'slos_document_generated_from_profile', $doc_id, $document_type, $profile, $user_id );

		return $doc_id;
	}
}
