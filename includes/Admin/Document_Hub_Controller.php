<?php
/**
 * Document Hub Controller
 *
 * Admin controller for the Document Hub interface.
 * Handles page rendering, AJAX requests, and document actions.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @since      4.1.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Services\Document_Hub_Service;
use ShahiLegalopsSuite\Services\Document_Generator;
use ShahiLegalopsSuite\Services\Export_Manager;
use ShahiLegalopsSuite\Database\Repositories\Company_Profile_Repository;
use ShahiLegalopsSuite\Database\Repositories\Legal_Doc_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document Hub Controller Class
 *
 * @since 4.1.0
 */
class Document_Hub_Controller {


	/**
	 * Hub service instance
	 *
	 * @var Document_Hub_Service
	 */
	private $hub_service;

	/**
	 * Document generator instance
	 *
	 * @var Document_Generator
	 */
	private $generator;

	/**
	 * Profile repository instance
	 *
	 * @var Company_Profile_Repository
	 */
	private $profile_repository;

	/**
	 * Document repository instance
	 *
	 * @var Legal_Doc_Repository
	 */
	private $doc_repository;

	/**
	 * Export manager instance
	 *
	 * @var Export_Manager
	 */
	private $export_manager;

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		$this->hub_service        = new Document_Hub_Service();
		$this->generator          = new Document_Generator();
		$this->profile_repository = Company_Profile_Repository::get_instance();
		$this->doc_repository     = new Legal_Doc_Repository();
		$this->export_manager     = new Export_Manager( $this->doc_repository );
	}

	/**
	 * Initialize hooks
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function init() {
		// Register AJAX handlers
		add_action( 'wp_ajax_slos_hub_generate_document', array( $this, 'ajax_generate_document' ) );
		add_action( 'wp_ajax_slos_hub_publish_document', array( $this, 'ajax_publish_document' ) );
		add_action( 'wp_ajax_slos_hub_regenerate_document', array( $this, 'ajax_regenerate_document' ) );
		add_action( 'wp_ajax_slos_hub_delete_document', array( $this, 'ajax_delete_document' ) );
		add_action( 'wp_ajax_slos_hub_bulk_action', array( $this, 'ajax_bulk_action' ) );
		add_action( 'wp_ajax_slos_hub_get_document_preview', array( $this, 'ajax_get_document_preview' ) );

		// Export AJAX handlers
		add_action( 'wp_ajax_slos_export_document', array( $this, 'ajax_export_document' ) );
		add_action( 'wp_ajax_slos_export_bulk', array( $this, 'ajax_export_bulk' ) );

		// Generate tab AJAX handlers (Phase 1E)
		add_action( 'wp_ajax_slos_gen_get_context', array( $this, 'ajax_get_generation_context' ) );
		add_action( 'wp_ajax_slos_gen_preview', array( $this, 'ajax_generate_preview' ) );
		add_action( 'wp_ajax_slos_gen_generate', array( $this, 'ajax_generate_from_profile' ) );
		add_action( 'wp_ajax_slos_gen_history', array( $this, 'ajax_get_version_history' ) );
		add_action( 'wp_ajax_slos_gen_restore', array( $this, 'ajax_restore_version' ) );
		add_action( 'wp_ajax_slos_gen_view_document', array( $this, 'ajax_view_document' ) );
		add_action( 'wp_ajax_slos_gen_compare_versions', array( $this, 'ajax_compare_versions' ) );
		add_action( 'wp_ajax_slos_gen_clear_drafts', array( $this, 'ajax_clear_drafts' ) );

		// Document editor AJAX handler
		add_action( 'wp_ajax_slos_save_document_edit', array( $this, 'ajax_save_document_edit' ) );
	}

	/**
	 * Render the Document Hub page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function render() {
		$data = $this->prepare_hub_data();

		// Load the template - Updated for Phase 4 hub template
		$template_path = SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/documents/hub.php';

		// Fallback to legacy template if new one doesn't exist
		if ( ! file_exists( $template_path ) ) {
			$template_path = SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/document-hub.php';
		}

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<div class="wrap"><p>' . esc_html__( 'Template not found.', 'shahi-legalops-suite' ) . '</p></div>';
		}
	}

	/**
	 * Prepare data for hub template
	 *
	 * @since 4.1.0
	 * @return array Template data
	 */
	private function prepare_hub_data() {
		return array(
			'cards'       => $this->hub_service->get_document_cards(),
			'categories'  => $this->hub_service->get_categories(),
			'profile'     => $this->hub_service->get_profile_summary(),
			'statistics'  => $this->hub_service->get_statistics(),
			'outdated'    => $this->hub_service->get_outdated_documents(),
			'profile_url' => admin_url( 'admin.php?page=slos-company-profile' ),
			'create_url'  => admin_url( 'admin.php?page=slos-documents' ),
			'edit_url'    => admin_url( 'admin.php?page=slos-edit-document' ),
			'nonce'       => wp_create_nonce( 'slos_hub_nonce' ),
		);
	}

	/**
	 * Enqueue hub assets
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function enqueue_assets() {
		$data = $this->prepare_hub_data();

		// CSS
		wp_enqueue_style(
			'slos-document-hub',
			SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/document-hub.css',
			array(),
			SHAHI_LEGALOPS_SUITE_VERSION
		);

		// JavaScript
		wp_enqueue_script(
			'slos-document-hub',
			SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/js/document-hub.js',
			array( 'jquery' ),
			SHAHI_LEGALOPS_SUITE_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'slos-document-hub',
			'slosHub',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => $data['nonce'],
				'profileUrl' => $data['profile_url'],
				'createUrl'  => $data['create_url'],
				'editUrl'    => $data['edit_url'],
				'strings'    => array(
					'generating'            => __( 'Generating document...', 'shahi-legalops-suite' ),
					'generated'             => __( 'Document generated successfully!', 'shahi-legalops-suite' ),
					'generateError'         => __( 'Error generating document. Please try again.', 'shahi-legalops-suite' ),
					'publishing'            => __( 'Publishing document...', 'shahi-legalops-suite' ),
					'published'             => __( 'Document published successfully!', 'shahi-legalops-suite' ),
					'publishError'          => __( 'Error publishing document. Please try again.', 'shahi-legalops-suite' ),
					'regenerating'          => __( 'Regenerating document...', 'shahi-legalops-suite' ),
					'regenerated'           => __( 'Document regenerated successfully!', 'shahi-legalops-suite' ),
					'regenerateError'       => __( 'Error regenerating document. Please try again.', 'shahi-legalops-suite' ),
					'confirmRegenerate'     => __( 'This will regenerate the document from your current profile. Any manual edits will be saved in version history. Continue?', 'shahi-legalops-suite' ),
					'confirmDelete'         => __( 'Are you sure you want to delete this document? This action cannot be undone.', 'shahi-legalops-suite' ),
					'confirmBulkRegenerate' => __( 'Regenerate all outdated documents? This will update them with your current profile data.', 'shahi-legalops-suite' ),
					'profileIncomplete'     => __( 'Your company profile is incomplete. Complete it first for better document generation.', 'shahi-legalops-suite' ),
					'profileComplete'       => __( 'Your profile is complete and ready for document generation.', 'shahi-legalops-suite' ),
					'missingFields'         => __( 'This document requires the following profile fields:', 'shahi-legalops-suite' ),
					'goToProfile'           => __( 'Go to Profile', 'shahi-legalops-suite' ),
					'generateAnyway'        => __( 'Generate Anyway', 'shahi-legalops-suite' ),
					'completeProfile'       => __( 'Complete Profile', 'shahi-legalops-suite' ),
					'regenerateWarning'     => __( 'This will create a new version. The previous version will be saved in history.', 'shahi-legalops-suite' ),
					'loading'               => __( 'Loading...', 'shahi-legalops-suite' ),
					'close'                 => __( 'Close', 'shahi-legalops-suite' ),
					'error'                 => __( 'An error occurred. Please try again.', 'shahi-legalops-suite' ),
					'documentPreview'       => __( 'Document Preview', 'shahi-legalops-suite' ),
					'noVersions'            => __( 'No version history available.', 'shahi-legalops-suite' ),
					'noReason'              => __( 'No description provided', 'shahi-legalops-suite' ),
					'shortcodeCopied'       => __( 'Shortcode copied to clipboard!', 'shahi-legalops-suite' ),
					'downloadStarted'       => __( 'Download started...', 'shahi-legalops-suite' ),
					'downloadError'         => __( 'Failed to download document. Please try again.', 'shahi-legalops-suite' ),
					'noDocsToExport'        => __( 'No documents available to export.', 'shahi-legalops-suite' ),
					'exportSuccess'         => __( 'Documents exported successfully!', 'shahi-legalops-suite' ),
					'exportError'           => __( 'Failed to export documents. Please try again.', 'shahi-legalops-suite' ),
				),
			)
		);
	}

	/**
	 * AJAX: Generate document
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_generate_document() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_type = isset( $_POST['doc_type'] ) ? sanitize_key( $_POST['doc_type'] ) : '';
		$force    = isset( $_POST['force'] ) && 'true' === $_POST['force'];

		if ( empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document type.', 'shahi-legalops-suite' ) ) );
		}

		// Get type configuration
		$type_config = $this->hub_service->get_document_type( $doc_type );
		if ( ! $type_config ) {
			wp_send_json_error( array( 'message' => __( 'Unknown document type.', 'shahi-legalops-suite' ) ) );
		}

		// Generate the document
		$result = $this->generator->generate(
			$doc_type,
			null,
			array(
				'force' => $force,
			)
		);

		if ( ! $result['success'] ) {
			// Check if profile is incomplete
			if ( ! empty( $result['requires_profile'] ) ) {
				wp_send_json_error(
					array(
						'message'          => $result['message'],
						'requires_profile' => true,
						'missing_fields'   => $result['missing_fields'] ?? array(),
						'completion'       => $result['completion'] ?? 0,
					)
				);
			}
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}

		// Success - return with card refresh data
		wp_send_json_success(
			array(
				'message'  => $result['message'],
				'doc_id'   => $result['doc_id'],
				'doc_type' => $doc_type,
				'status'   => $result['status'],
				'warnings' => $result['warnings'] ?? array(),
				'refresh'  => true,
			)
		);
	}

	/**
	 * AJAX: Publish document
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_publish_document() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_id   = isset( $_POST['doc_id'] ) ? absint( $_POST['doc_id'] ) : 0;
		$doc_type = isset( $_POST['doc_type'] ) ? sanitize_key( $_POST['doc_type'] ) : '';

		if ( empty( $doc_id ) && empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document.', 'shahi-legalops-suite' ) ) );
		}

		// Get document
		$document = null;
		if ( $doc_id ) {
			$document = $this->doc_repository->find( $doc_id );
		} elseif ( $doc_type ) {
			$document = $this->hub_service->get_document_by_type( $doc_type );
		}

		if ( ! $document ) {
			wp_send_json_error( array( 'message' => __( 'Document not found.', 'shahi-legalops-suite' ) ) );
		}

		// Update status to published
		$metadata = is_string( $document->metadata )
			? json_decode( $document->metadata, true )
			: (array) $document->metadata;

		$metadata['status']       = 'published';
		$metadata['published_at'] = current_time( 'mysql' );
		$metadata['published_by'] = get_current_user_id();

		$updated = $this->doc_repository->update(
			$document->id,
			array(
				'metadata'     => wp_json_encode( $metadata ),
				'published_at' => current_time( 'mysql' ),
			)
		);

		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => __( 'Failed to publish document.', 'shahi-legalops-suite' ) ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Document published successfully!', 'shahi-legalops-suite' ),
				'doc_id'  => $document->id,
				'status'  => 'published',
				'refresh' => true,
			)
		);
	}

	/**
	 * AJAX: Regenerate document
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_regenerate_document() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_type = isset( $_POST['doc_type'] ) ? sanitize_key( $_POST['doc_type'] ) : '';

		if ( empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document type.', 'shahi-legalops-suite' ) ) );
		}

		// Regenerate the document
		$result = $this->generator->regenerate( $doc_type );

		if ( ! $result['success'] ) {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}

		wp_send_json_success(
			array(
				'message'     => $result['message'],
				'doc_id'      => $result['doc_id'],
				'doc_type'    => $doc_type,
				'status'      => $result['status'],
				'new_version' => $result['new_version'] ?? null,
				'warnings'    => $result['warnings'] ?? array(),
				'refresh'     => true,
			)
		);
	}

	/**
	 * AJAX: Delete document
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_delete_document() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_id   = isset( $_POST['doc_id'] ) ? absint( $_POST['doc_id'] ) : 0;
		$doc_type = isset( $_POST['doc_type'] ) ? sanitize_key( $_POST['doc_type'] ) : '';

		// Get document
		$document = null;
		if ( $doc_id ) {
			$document = $this->doc_repository->find( $doc_id );
		} elseif ( $doc_type ) {
			$document = $this->hub_service->get_document_by_type( $doc_type );
		}

		if ( ! $document ) {
			wp_send_json_error( array( 'message' => __( 'Document not found.', 'shahi-legalops-suite' ) ) );
		}

		// Perform delete (or archive)
		$deleted = $this->doc_repository->delete( $document->id );

		if ( ! $deleted ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete document.', 'shahi-legalops-suite' ) ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Document deleted successfully!', 'shahi-legalops-suite' ),
				'refresh' => true,
			)
		);
	}

	/**
	 * AJAX: Bulk action
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_bulk_action() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$action = isset( $_POST['bulk_action'] ) ? sanitize_key( $_POST['bulk_action'] ) : '';

		switch ( $action ) {
			case 'regenerate_outdated':
				// Get outdated documents
				$outdated = $this->generator->get_outdated_documents();

				if ( empty( $outdated ) ) {
					wp_send_json_success(
						array(
							'message' => __( 'No outdated documents to regenerate.', 'shahi-legalops-suite' ),
							'refresh' => false,
						)
					);
				}

				// Bulk regenerate
				$result = $this->generator->bulk_regenerate( $outdated );

				$message = sprintf(
					/* translators: %1$d: success count, %2$d: total count */
					__( 'Regenerated %1$d of %2$d documents.', 'shahi-legalops-suite' ),
					$result['success'],
					$result['total']
				);

				if ( $result['failed'] > 0 ) {
					$message .= ' ' . sprintf(
						/* translators: %d: failed count */
						__( '%d failed.', 'shahi-legalops-suite' ),
						$result['failed']
					);
				}

				wp_send_json_success(
					array(
						'message' => $message,
						'results' => $result,
						'refresh' => true,
					)
				);
				break;

			case 'export_all':
				// Get all documents for bulk export
				$documents = $this->hub_service->get_all_documents();

				if ( empty( $documents ) ) {
					wp_send_json_error( array( 'message' => __( 'No documents to export.', 'shahi-legalops-suite' ) ) );
				}

				$doc_ids = array();
				foreach ( $documents as $doc ) {
					if ( isset( $doc->id ) ) {
						$doc_ids[] = intval( $doc->id );
					}
				}

				if ( empty( $doc_ids ) ) {
					wp_send_json_error( array( 'message' => __( 'No valid documents to export.', 'shahi-legalops-suite' ) ) );
				}

				$result = $this->export_manager->export_zip(
					$doc_ids,
					array(
						'include_branding' => true,
						'include_manifest' => true,
						'include_readme'   => true,
					)
				);

				if ( ! $result['success'] ) {
					wp_send_json_error( array( 'message' => $result['message'] ?? __( 'Export failed.', 'shahi-legalops-suite' ) ) );
				}

				wp_send_json_success(
					array(
						'message'      => sprintf(
							/* translators: %d: document count */
							__( 'Successfully exported %d documents.', 'shahi-legalops-suite' ),
							count( $doc_ids )
						),
						'download_url' => $result['download_url'],
						'filename'     => $result['filename'],
						'refresh'      => false,
					)
				);
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Invalid action.', 'shahi-legalops-suite' ) ) );
		}
	}

	/**
	 * AJAX: Get document preview
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_get_document_preview() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_type     = isset( $_POST['doc_type'] ) ? sanitize_key( $_POST['doc_type'] ) : '';
		$preview_mode = isset( $_POST['preview_mode'] ) ? sanitize_key( $_POST['preview_mode'] ) : 'existing';

		if ( empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document type.', 'shahi-legalops-suite' ) ) );
		}

		// If preview mode is 'generated', show what would be generated
		if ( 'generated' === $preview_mode ) {
			$preview = $this->generator->preview( $doc_type );

			if ( ! $preview['success'] ) {
				wp_send_json_error( array( 'message' => $preview['message'] ?? __( 'Preview unavailable.', 'shahi-legalops-suite' ) ) );
			}

			$type_config = $this->hub_service->get_document_type( $doc_type );

			wp_send_json_success(
				array(
					'content'  => wp_kses_post( $preview['content'] ),
					'title'    => esc_html( $type_config['title'] ?? $doc_type ),
					'version'  => __( 'Preview', 'shahi-legalops-suite' ),
					'warnings' => $preview['warnings'] ?? array(),
				)
			);
		}

		// Otherwise show existing document
		$document = $this->hub_service->get_document_by_type( $doc_type );

		if ( ! $document ) {
			// Try to show preview instead
			$preview = $this->generator->preview( $doc_type );

			if ( $preview['success'] ) {
				$type_config = $this->hub_service->get_document_type( $doc_type );

				wp_send_json_success(
					array(
						'content'    => wp_kses_post( $preview['content'] ),
						'title'      => esc_html( $type_config['title'] ?? $doc_type ),
						'version'    => __( 'Preview (not generated)', 'shahi-legalops-suite' ),
						'is_preview' => true,
					)
				);
			}

			wp_send_json_error( array( 'message' => __( 'Document not found.', 'shahi-legalops-suite' ) ) );
		}

		wp_send_json_success(
			array(
				'content' => wp_kses_post( $document->content ?? '' ),
				'title'   => esc_html( $document->title ?? $document->type ?? '' ),
				'version' => $document->version ?? '1.0',
			)
		);
	}

	/**
	 * Get hub service instance
	 *
	 * @since 4.1.0
	 * @return Document_Hub_Service
	 */
	public function get_service() {
		return $this->hub_service;
	}

	/**
	 * Render document card
	 *
	 * @since 4.1.0
	 * @param array $card Card data.
	 * @return void
	 */
	public function render_card( $card ) {
		$template_path = SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/hub-parts/document-card.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}

	/**
	 * Render profile banner
	 *
	 * @since 4.1.0
	 * @param array $profile Profile summary data.
	 * @return void
	 */
	public function render_profile_banner( $profile ) {
		$template_path = SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/hub-parts/profile-banner.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}

	/**
	 * AJAX: Export single document
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_export_document() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_id = isset( $_POST['doc_id'] ) ? intval( $_POST['doc_id'] ) : 0;
		$format = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : Export_Manager::FORMAT_PDF;

		if ( ! $doc_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document ID.', 'shahi-legalops-suite' ) ) );
		}

		// Validate format
		$valid_formats = array(
			Export_Manager::FORMAT_PDF,
			Export_Manager::FORMAT_HTML,
		);

		if ( ! in_array( $format, $valid_formats, true ) ) {
			$format = Export_Manager::FORMAT_PDF;
		}

		// Get branding options from request
		$options = array(
			'include_branding' => isset( $_POST['include_branding'] ) ? (bool) $_POST['include_branding'] : true,
			'include_toc'      => isset( $_POST['include_toc'] ) ? (bool) $_POST['include_toc'] : false,
		);

		// Export based on format
		if ( Export_Manager::FORMAT_PDF === $format ) {
			$result = $this->export_manager->export_pdf( $doc_id, $options );
		} else {
			$result = $this->export_manager->export_html( $doc_id, $options );
		}

		if ( ! $result['success'] ) {
			wp_send_json_error(
				array(
					'message' => $result['message'] ?? __( 'Export failed.', 'shahi-legalops-suite' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Document exported successfully.', 'shahi-legalops-suite' ),
				'download_url' => $result['download_url'],
				'filename'     => $result['filename'],
				'format'       => $format,
			)
		);
	}

	/**
	 * AJAX: Export multiple documents as ZIP
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_export_bulk() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		// Get document IDs
		$doc_ids = isset( $_POST['doc_ids'] ) ? array_map( 'intval', (array) $_POST['doc_ids'] ) : array();
		$doc_ids = array_filter( $doc_ids ); // Remove zeros

		if ( empty( $doc_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No documents selected.', 'shahi-legalops-suite' ) ) );
		}

		// Get export options from request
		$options = array(
			'include_branding' => isset( $_POST['include_branding'] ) ? (bool) $_POST['include_branding'] : true,
			'include_manifest' => isset( $_POST['include_manifest'] ) ? (bool) $_POST['include_manifest'] : true,
			'include_readme'   => isset( $_POST['include_readme'] ) ? (bool) $_POST['include_readme'] : true,
			'format'           => isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : Export_Manager::FORMAT_HTML,
		);

		// Validate format for bulk export
		if ( ! in_array( $options['format'], array( Export_Manager::FORMAT_PDF, Export_Manager::FORMAT_HTML ), true ) ) {
			$options['format'] = Export_Manager::FORMAT_HTML;
		}

		$result = $this->export_manager->export_zip( $doc_ids, $options );

		if ( ! $result['success'] ) {
			wp_send_json_error(
				array(
					'message' => $result['message'] ?? __( 'Bulk export failed.', 'shahi-legalops-suite' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'message'        => sprintf(
					/* translators: %d: document count */
					__( 'Successfully exported %d documents as ZIP.', 'shahi-legalops-suite' ),
					count( $doc_ids )
				),
				'download_url'   => $result['download_url'],
				'filename'       => $result['filename'],
				'document_count' => count( $doc_ids ),
			)
		);
	}

	/**
	 * AJAX: Get generation context for review modal (Generate tab)
	 *
	 * Returns profile, validation status, and missing fields.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_get_generation_context() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$doc_type = isset( $_POST['doc_type'] ) ? sanitize_text_field( $_POST['doc_type'] ) : '';

		if ( empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Document type required', 'shahi-legalops-suite' ) ), 400 );
		}

		try {
			$context = $this->generator->get_generation_context( $doc_type );

			// Check for actual error vs incomplete profile
			// An incomplete profile should still show in modal with missing fields
			if ( isset( $context['error'] ) && ! empty( $context['error'] ) ) {
				wp_send_json_error(
					array(
						'message'        => $context['error'],
						'missing_fields' => $context['missing_fields'] ?? array(),
					),
					422
				);
			}

			// Always return success - modal will show missing fields if not valid
			wp_send_json_success( $context );
		} catch ( \Exception $e ) {
			error_log( 'SLOS get_generation_context error: ' . $e->getMessage() );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
					'file'    => $e->getFile(),
					'line'    => $e->getLine(),
				),
				500
			);
		} catch ( \Error $e ) {
			error_log( 'SLOS get_generation_context fatal: ' . $e->getMessage() );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
					'file'    => $e->getFile(),
					'line'    => $e->getLine(),
				),
				500
			);
		}
	}

	/**
	 * AJAX: Generate preview (no save) (Generate tab)
	 *
	 * Generates document HTML for preview only. Does not save to database.
	 * Has 15 second timeout.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_generate_preview() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$doc_type  = isset( $_POST['doc_type'] ) ? sanitize_text_field( $_POST['doc_type'] ) : '';
		$overrides = isset( $_POST['overrides'] ) ? json_decode( stripslashes( $_POST['overrides'] ), true ) : array();

		if ( empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Document type required', 'shahi-legalops-suite' ) ), 400 );
		}

		$preview = $this->generator->generate_preview( $doc_type, $overrides );

		if ( is_wp_error( $preview ) ) {
			wp_send_json_error(
				array(
					'message' => $preview->get_error_message(),
					'code'    => $preview->get_error_code(),
				),
				500
			);
		}

		wp_send_json_success(
			array(
				'html'       => $preview,
				'word_count' => str_word_count( wp_strip_all_tags( $preview ) ),
			)
		);
	}

	/**
	 * AJAX: Generate and save document as DRAFT (Generate tab)
	 *
	 * Generates document from profile and saves as draft.
	 * Returns document ID and edit URL.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_generate_from_profile() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$doc_type      = isset( $_POST['doc_type'] ) ? sanitize_text_field( $_POST['doc_type'] ) : '';
		$overrides     = isset( $_POST['overrides'] ) ? json_decode( stripslashes( $_POST['overrides'] ), true ) : array();
		$change_reason = isset( $_POST['change_reason'] ) ? sanitize_text_field( $_POST['change_reason'] ) : __( 'Generated from profile', 'shahi-legalops-suite' );

		if ( empty( $doc_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Document type required', 'shahi-legalops-suite' ) ), 400 );
		}

		try {
			// Add change reason to overrides
			$overrides['change_reason'] = $change_reason;

			$doc_id = $this->generator->generate_from_profile( $doc_type, $overrides, get_current_user_id() );

			if ( is_wp_error( $doc_id ) ) {
				wp_send_json_error(
					array(
						'message' => $doc_id->get_error_message(),
						'code'    => $doc_id->get_error_code(),
						'data'    => $doc_id->get_error_data(),
					),
					500
				);
			}

			wp_send_json_success(
				array(
					'doc_id'   => $doc_id,
					'edit_url' => admin_url( 'admin.php?page=slos-edit-document&id=' . $doc_id ),
					'message'  => __( 'Document generated as draft. Review and publish when ready.', 'shahi-legalops-suite' ),
				)
			);
		} catch ( \Exception $e ) {
			error_log( 'SLOS generate_from_profile error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
					'file'    => basename( $e->getFile() ),
					'line'    => $e->getLine(),
				),
				500
			);
		} catch ( \Error $e ) {
			error_log( 'SLOS generate_from_profile fatal: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
					'file'    => basename( $e->getFile() ),
					'line'    => $e->getLine(),
				),
				500
			);
		}
	}

	/**
	 * AJAX: Get version history for document (Generate tab)
	 *
	 * Returns latest 20 versions with timestamps and authors.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_get_version_history() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$doc_id = isset( $_POST['doc_id'] ) ? intval( $_POST['doc_id'] ) : 0;

		if ( $doc_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document ID', 'shahi-legalops-suite' ) ), 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'slos_legal_doc_versions';

		$versions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, change_reason, created_by, created_at
            FROM {$table}
            WHERE doc_id = %d
            ORDER BY created_at DESC
            LIMIT 20",
				$doc_id
			)
		);

		// Enrich with user data and add version numbers (descending)
		$count = count( $versions );
		foreach ( $versions as $index => $version ) {
			$user                 = get_userdata( $version->created_by );
			$version->author_name = $user ? $user->display_name : __( 'Unknown', 'shahi-legalops-suite' );
			// Highest number is most recent (timeline is already DESC)
			$version->version_num = $count - $index;
		}

		wp_send_json_success(
			array(
				'versions' => $versions,
				'count'    => count( $versions ),
			)
		);
	}

	/**
	 * AJAX: Restore an old version as new draft (Generate tab)
	 *
	 * Creates a new draft document from an old version.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_restore_version() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$version_id = isset( $_POST['version_id'] ) ? intval( $_POST['version_id'] ) : 0;
		$doc_id     = isset( $_POST['doc_id'] ) ? intval( $_POST['doc_id'] ) : 0;

		if ( $version_id <= 0 || $doc_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'shahi-legalops-suite' ) ), 400 );
		}

		global $wpdb;
		$versions_table = $wpdb->prefix . 'slos_legal_doc_versions';
		$docs_table     = $wpdb->prefix . 'slos_documents';

		// Get old version content
		$old_version = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT content FROM {$versions_table} WHERE id = %d AND doc_id = %d",
				$version_id,
				$doc_id
			)
		);

		if ( ! $old_version ) {
			wp_send_json_error( array( 'message' => __( 'Version not found', 'shahi-legalops-suite' ) ), 404 );
		}

		// Get original document info
		$doc = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT title, type, locale FROM {$docs_table} WHERE id = %d",
				$doc_id
			)
		);

		if ( ! $doc ) {
			wp_send_json_error( array( 'message' => __( 'Document not found', 'shahi-legalops-suite' ) ), 404 );
		}

		// Create new draft with restored content
		$new_doc_id = $this->doc_repository->create(
			array(
				'title'      => $doc->title . ' ' . __( '(Restored)', 'shahi-legalops-suite' ),
				'content'    => $old_version->content,
				'type'       => $doc->type,
				'status'     => 'draft',
				'locale'     => $doc->locale,
				'version'    => '1.0.0',
				'created_by' => get_current_user_id(),
				'updated_by' => get_current_user_id(),
			)
		);

		if ( is_wp_error( $new_doc_id ) ) {
			wp_send_json_error(
				array(
					'message' => $new_doc_id->get_error_message(),
				),
				500
			);
		}

		wp_send_json_success(
			array(
				'doc_id'   => $new_doc_id,
				'edit_url' => admin_url( 'admin.php?page=slos-edit-document&id=' . $new_doc_id ),
				'message'  => __( 'Version restored as new draft', 'shahi-legalops-suite' ),
			)
		);
	}

	/**
	 * AJAX: View document content for preview modal (Generate tab)
	 *
	 * Returns the document HTML content for display in the View modal.
	 *
	 * @since 4.2.0
	 * @return void
	 */
	public function ajax_view_document() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$doc_id = isset( $_POST['doc_id'] ) ? intval( $_POST['doc_id'] ) : 0;

		if ( $doc_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document ID', 'shahi-legalops-suite' ) ), 400 );
		}

		global $wpdb;
		$docs_table = $wpdb->prefix . 'slos_documents';

		// Get document content
		$doc = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT title, content, status, version FROM {$docs_table} WHERE id = %d",
				$doc_id
			)
		);

		if ( ! $doc ) {
			wp_send_json_error( array( 'message' => __( 'Document not found', 'shahi-legalops-suite' ) ), 404 );
		}

		// Process content similar to how WordPress displays posts
		// Apply shortcodes, autop, and other filters
		$html_content = $doc->content;

		// Apply the_content filters (shortcodes, autop, etc.)
		$html_content = apply_filters( 'the_content', $html_content );

		// Additional formatting for better display
		$html_content = wpautop( $html_content ); // Convert line breaks to paragraphs
		$html_content = do_shortcode( $html_content ); // Process shortcodes

		// Sanitize for security while preserving formatting
		$html_content = wp_kses_post( $html_content );

		// Calculate word count
		$word_count = str_word_count( wp_strip_all_tags( $doc->content ) );

		wp_send_json_success(
			array(
				'html'       => $html_content,
				'title'      => $doc->title,
				'status'     => $doc->status,
				'version'    => $doc->version,
				'word_count' => $word_count,
			)
		);
	}

	/**
	 * AJAX: Compare two document versions (Generate tab)
	 *
	 * Returns HTML diff of two version contents.
	 *
	 * @since 4.2.0
	 * @return void
	 */
	public function ajax_compare_versions() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		$version_id = isset( $_POST['version_id'] ) ? intval( $_POST['version_id'] ) : 0;
		$compare_id = isset( $_POST['compare_id'] ) ? intval( $_POST['compare_id'] ) : 0;

		if ( $version_id <= 0 || $compare_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid version IDs', 'shahi-legalops-suite' ) ), 400 );
		}

		global $wpdb;
		$versions_table = $wpdb->prefix . 'slos_legal_doc_versions';

		// Get both versions
		$version1 = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, content, change_reason, created_at FROM {$versions_table} WHERE id = %d",
				$version_id
			)
		);

		$version2 = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, content, change_reason, created_at FROM {$versions_table} WHERE id = %d",
				$compare_id
			)
		);

		if ( ! $version1 || ! $version2 ) {
			wp_send_json_error( array( 'message' => __( 'Version not found', 'shahi-legalops-suite' ) ), 404 );
		}

		// Simple line-by-line comparison
		$lines1 = explode( "\n", wp_strip_all_tags( $version1->content ) );
		$lines2 = explode( "\n", wp_strip_all_tags( $version2->content ) );

		$diff_html = '<div class="slos-gen-diff">';

		// Use WordPress text diff if available
		if ( class_exists( 'WP_Text_Diff_Renderer_Table' ) ) {
			$text_diff = wp_text_diff(
				$version2->content,
				$version1->content,
				array(
					'title'       => '',
					'title_left'  => __( 'Previous', 'shahi-legalops-suite' ),
					'title_right' => __( 'Current', 'shahi-legalops-suite' ),
				)
			);

			if ( $text_diff ) {
				$diff_html .= $text_diff;
			} else {
				$diff_html .= '<p>' . __( 'No differences found.', 'shahi-legalops-suite' ) . '</p>';
			}
		} else {
			// Fallback: simple side-by-side
			$diff_html .= '<div class="slos-gen-diff-simple">';
			$diff_html .= '<div class="slos-gen-diff-left"><h4>' . esc_html__( 'Previous Version', 'shahi-legalops-suite' ) . '</h4>';
			$diff_html .= '<pre>' . esc_html( $version2->content ) . '</pre></div>';
			$diff_html .= '<div class="slos-gen-diff-right"><h4>' . esc_html__( 'Current Version', 'shahi-legalops-suite' ) . '</h4>';
			$diff_html .= '<pre>' . esc_html( $version1->content ) . '</pre></div>';
			$diff_html .= '</div>';
		}

		$diff_html .= '</div>';

		wp_send_json_success(
			array(
				'diff_html'   => $diff_html,
				'version1_at' => $version1->created_at,
				'version2_at' => $version2->created_at,
			)
		);
	}

	/**
	 * AJAX: Clear all draft documents (Generate tab)
	 *
	 * Deletes all documents with status 'draft'.
	 *
	 * @since 4.2.0
	 * @return void
	 */
	public function ajax_clear_drafts() {
		check_ajax_referer( 'slos_hub_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'shahi-legalops-suite' ) ), 403 );
		}

		global $wpdb;
		$docs_table = $wpdb->prefix . 'slos_documents';

		// Get count of drafts
		$draft_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$docs_table} WHERE status = 'draft'"
		);

		if ( $draft_count == 0 ) {
			wp_send_json_success(
				array(
					'message' => __( 'No draft documents to clear.', 'shahi-legalops-suite' ),
					'deleted' => 0,
				)
			);
		}

		// Delete all draft documents
		$deleted = $wpdb->delete( $docs_table, array( 'status' => 'draft' ), array( '%s' ) );

		if ( $deleted === false ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to delete draft documents.', 'shahi-legalops-suite' ),
				),
				500
			);
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of deleted drafts */
					_n(
						'%d draft document deleted.',
						'%d draft documents deleted.',
						$deleted,
						'shahi-legalops-suite'
					),
					$deleted
				),
				'deleted' => $deleted,
			)
		);
	}

	/**
	 * AJAX: Save document edit
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function ajax_save_document_edit() {
		check_ajax_referer( 'slos_editor_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shahi-legalops-suite' ) ) );
		}

		$doc_id  = isset( $_POST['doc_id'] ) ? absint( $_POST['doc_id'] ) : 0;
		$title   = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$content = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';

		if ( ! $doc_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid document ID.', 'shahi-legalops-suite' ) ) );
		}

		// Update document
		$result = $this->doc_repository->save(
			array(
				'id'         => $doc_id,
				'title'      => $title,
				'content'    => $content,
				'updated_at' => current_time( 'mysql' ),
			)
		);

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to save document.', 'shahi-legalops-suite' ) ) );
		}

		// Create version entry
		$this->doc_repository->create_version(
			$doc_id,
			array(
				'content'       => $content,
				'change_reason' => __( 'Manual edit', 'shahi-legalops-suite' ),
			)
		);

		wp_send_json_success(
			array(
				'message' => __( 'Document saved successfully.', 'shahi-legalops-suite' ),
			)
		);
	}
}
