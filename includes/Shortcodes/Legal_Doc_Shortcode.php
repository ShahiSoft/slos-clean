<?php
/**
 * Legal Document Shortcode
 *
 * Renders legal documents for embedding in pages/posts.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Shortcodes
 * @since      3.5.0
 */

namespace ShahiLegalFlowSuite\Shortcodes;

use ShahiLegalFlowSuite\Services\Document_Hub_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legal Document Shortcode Class
 *
 * Usage: [slos_legal_doc type="privacy-policy"]
 *
 * @since 3.5.0
 */
class Legal_Doc_Shortcode {

	/**
	 * Document Hub Service
	 *
	 * @var Document_Hub_Service
	 */
	private $hub_service;

	/**
	 * Constructor
	 *
	 * @since 3.5.0
	 */
	public function __construct() {
		$this->hub_service = new Document_Hub_Service();
	}

	/**
	 * Register shortcode
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function register() {
		add_shortcode( 'slos_legal_doc', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode
	 *
	 * @since 3.5.0
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content (not used).
	 * @return string Rendered HTML
	 */
	public function render( $atts, $content = null ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'type'      => '',          // Document type (e.g., privacy-policy, terms-of-service)
				'title'     => 'yes',       // Show document title (yes/no)
				'version'   => 'yes',       // Show version info (yes/no)
				'updated'   => 'yes',       // Show last updated date (yes/no)
				'class'     => '',          // Additional CSS classes
			),
			$atts,
			'slos_legal_doc'
		);

		// Validate required type parameter
		if ( empty( $atts['type'] ) ) {
			return $this->render_error( __( 'Document type is required. Usage: [slos_legal_doc type="privacy-policy"]', 'shahi-legalflowsuite' ) );
		}

		// Normalize document type (convert underscores to hyphens)
		$doc_type = str_replace( '_', '-', sanitize_key( $atts['type'] ) );

		// Check if document type is active (not dormant)
		if ( defined( 'SLOS_ACTIVE_LEGAL_DOCS' ) && is_array( SLOS_ACTIVE_LEGAL_DOCS ) ) {
			if ( ! in_array( $doc_type, SLOS_ACTIVE_LEGAL_DOCS, true ) ) {
				return $this->render_error( __( 'This document type is not available.', 'shahi-legalflowsuite' ) );
			}
		}

		// Get document from database
		$document = $this->hub_service->get_document_by_type( $doc_type );

		// Check if document exists
		if ( ! $document ) {
			return $this->render_error( 
				sprintf(
					/* translators: %s: document type */
					__( 'Document "%s" has not been generated yet. Please generate it from the Legal Documents Hub.', 'shahi-legalflowsuite' ),
					esc_html( $doc_type )
				)
			);
		}

		// Check if document is published
		if ( 'published' !== $document->status ) {
			return $this->render_error( __( 'This document is not published yet.', 'shahi-legalflowsuite' ) );
		}

		// Build HTML output
		return $this->render_document( $document, $atts );
	}

	/**
	 * Render the document HTML
	 *
	 * @since 3.5.0
	 * @param object $document Document object from database.
	 * @param array  $atts     Shortcode attributes.
	 * @return string Rendered HTML
	 */
	private function render_document( $document, $atts ) {
		$html = '<div class="slos-legal-document ' . esc_attr( $atts['class'] ) . '" data-doc-type="' . esc_attr( $document->doc_type ) . '">';

		// Document title
		if ( 'yes' === $atts['title'] && ! empty( $document->title ) ) {
			$html .= '<h2 class="slos-legal-document__title">' . esc_html( $document->title ) . '</h2>';
		}

		// Document metadata (version and updated date)
		if ( 'yes' === $atts['version'] || 'yes' === $atts['updated'] ) {
			$html .= '<div class="slos-legal-document__meta">';

			if ( 'yes' === $atts['version'] && ! empty( $document->version ) ) {
				$html .= '<span class="slos-legal-document__version">';
				$html .= sprintf(
					/* translators: %s: version number */
					esc_html__( 'Version: %s', 'shahi-legalflowsuite' ),
					esc_html( $document->version )
				);
				$html .= '</span>';
			}

			if ( 'yes' === $atts['updated'] && ! empty( $document->updated_at ) ) {
				if ( 'yes' === $atts['version'] ) {
					$html .= ' <span class="slos-legal-document__separator">|</span> ';
				}
				$html .= '<span class="slos-legal-document__updated">';
				$html .= sprintf(
					/* translators: %s: date */
					esc_html__( 'Last Updated: %s', 'shahi-legalflowsuite' ),
					esc_html( wp_date( get_option( 'date_format' ), strtotime( $document->updated_at ) ) )
				);
				$html .= '</span>';
			}

			$html .= '</div>';
		}

		// Document content
		$html .= '<div class="slos-legal-document__content">';
		$html .= wp_kses_post( $document->content );
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render error message
	 *
	 * @since 3.5.0
	 * @param string $message Error message.
	 * @return string Rendered HTML
	 */
	private function render_error( $message ) {
		// Only show errors to logged-in users with appropriate capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return ''; // Don't show errors to regular visitors
		}

		return '<div class="slos-legal-document-error" style="padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; margin: 20px 0;">' 
			. '<p style="margin: 0;"><strong>' . esc_html__( 'Legal Document Error:', 'shahi-legalflowsuite' ) . '</strong> ' 
			. esc_html( $message ) . '</p>'
			. '</div>';
	}
}
