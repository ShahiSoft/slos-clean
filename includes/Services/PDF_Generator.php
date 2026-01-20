<?php
/**
 * PDF Generator Service
 *
 * Handles PDF generation for legal documents using DOMPDF library.
 * Provides HTML-to-PDF conversion, caching, branding, and table of contents.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @since      3.0.0
 * @version    1.0.0
 */

namespace ShahiLegalFlowSuite\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

// Prevent direct access..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF Generator Service Class
 *
 * @since 3.0.0
 */
class PDF_Generator {

	/**
	 * DOMPDF instance
	 *
	 * @var Dompdf
	 */
	private $dompdf;

	/**
	 * PDF options
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Cache group for PDF files
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'slos_pdf';

	/**
	 * Cache expiration (30 days)
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = 2592000;

	/**
	 * Minimum word count for table of contents
	 *
	 * @var int
	 */
	const TOC_MIN_WORDS = 500;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->initialize_dompdf();
	}

	/**
	 * Initialize DOMPDF with options
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private function initialize_dompdf() {
		$this->options = new Options();
		$this->options->set( 'isRemoteEnabled', true );
		$this->options->set( 'isHtml5ParserEnabled', true );
		$this->options->set( 'isFontSubsettingEnabled', true );
		$this->options->set( 'defaultFont', 'DejaVu Sans' );
		$this->options->set( 'chroot', ABSPATH );

		// Apply filters for custom options..
		$custom_options = apply_filters( 'slos_pdf_dompdf_options', array() );
		foreach ( $custom_options as $key => $value ) {
			$this->options->set( $key, $value );
		}

		$this->dompdf = new Dompdf( $this->options );
	}

	/**
	 * Generate PDF from document data
	 *
	 * @since 3.0.0
	 * @param array $document Document data with content, metadata.
	 * @param array $options  Optional generation options.
	 * @return string|WP_Error PDF binary data or error.
	 */
	public function generate_pdf( $document, $options = array() ) {
		// Validate input..
		if ( empty( $document['content'] ) ) {
			return new \WP_Error(
				'empty_content',
				__( 'Document content is empty', 'shahi-legalflowsuite' )
			);
		}

		// Check cache first..
		$cache_key = $this->get_cache_key( $document );
		if ( ! isset( $options['skip_cache'] ) || ! $options['skip_cache'] ) {
			$cached_pdf = $this->get_cached_pdf( $cache_key );
			if ( $cached_pdf ) {
				return $cached_pdf;
			}
		}

		try {
			// Generate HTML with branding and styling..
			$html = $this->prepare_html( $document, $options );

			// Load HTML into DOMPDF..
			$this->dompdf->loadHtml( $html );

			// Set paper size and orientation..
			$paper_size  = isset( $options['paper_size'] ) ? $options['paper_size'] : 'A4';
			$orientation = isset( $options['orientation'] ) ? $options['orientation'] : 'portrait';
			$this->dompdf->setPaper( $paper_size, $orientation );

			// Render PDF..
			$this->dompdf->render();

			// Get PDF output..
			$pdf_output = $this->dompdf->output();

			// Cache the PDF..
			if ( ! isset( $options['skip_cache'] ) || ! $options['skip_cache'] ) {
				$this->cache_pdf( $cache_key, $pdf_output );
			}

			return $pdf_output;

		} catch ( \Exception $e ) {
			return new \WP_Error(
				'pdf_generation_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'PDF generation failed: %s', 'shahi-legalflowsuite' ),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Prepare HTML content for PDF generation
	 *
	 * @since 3.0.0
	 * @param array $document Document data.
	 * @param array $options  Generation options.
	 * @return string Complete HTML with styling.
	 */
	private function prepare_html( $document, $options = array() ) {
		$content  = $document['content'];
		$metadata = isset( $document['metadata'] ) ? $document['metadata'] : array();
		$title    = isset( $document['title'] ) ? $document['title'] : __( 'Legal Document', 'shahi-legalflowsuite' );

		// Get CSS styles..
		$css = $this->get_pdf_styles();

		// Build header..
		$header = $this->build_header( $document, $metadata );

		// Check if we need table of contents..
		$include_toc = isset( $options['include_toc'] ) ? $options['include_toc'] : $this->should_include_toc( $content );
		$toc         = $include_toc ? $this->generate_toc( $content ) : '';

		// Build footer..
		$footer = $this->build_footer( $document, $metadata );

		// Assemble complete HTML..
		$html = sprintf(
			'<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>%s</title>
	<style>%s</style>
</head>
<body>
	<div class="pdf-wrapper">
		%s
		%s
		<div class="pdf-content">
			%s
		</div>
		%s
	</div>
</body>
</html>',
			esc_html( $title ),
			$css,
			$header,
			$toc,
			$content,
			$footer
		);

		return apply_filters( 'slos_pdf_html', $html, $document, $options );
	}

	/**
	 * Build PDF header with branding
	 *
	 * @since 3.0.0
	 * @param array $document Document data.
	 * @param array $metadata Document metadata.
	 * @return string Header HTML.
	 */
	private function build_header( $document, $metadata ) {
		$site_name = get_bloginfo( 'name' );
		$site_url  = get_site_url();
		$logo_url  = get_custom_logo() ? wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) : '';
		$title     = isset( $document['title'] ) ? $document['title'] : '';
		$doc_type  = isset( $metadata['doc_type'] ) ? ucfirst( $metadata['doc_type'] ) : '';
		$version   = isset( $document['version'] ) ? $document['version'] : '1';
		$date      = isset( $document['updated_at'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $document['updated_at'] ) ) : '';

		$header = '<div class="pdf-header">';

		// Logo if available..
		if ( $logo_url ) {
			$header .= sprintf( '<div class="pdf-logo"><img src="%s" alt="%s" /></div>', esc_url( $logo_url ), esc_attr( $site_name ) );
		}

		// Site name..
		$header .= sprintf( '<div class="pdf-site-name">%s</div>', esc_html( $site_name ) );

		// Document info..
		$header .= '<div class="pdf-doc-info">';
		if ( $title ) {
			$header .= sprintf( '<h1 class="pdf-title">%s</h1>', esc_html( $title ) );
		}
		if ( $doc_type ) {
			$header .= sprintf( '<div class="pdf-doc-type">%s</div>', esc_html( $doc_type ) );
		}
		$header .= sprintf(
			'<div class="pdf-meta">%s | %s %s</div>',
			esc_html( $date ),
			esc_html__( 'Version', 'shahi-legalflowsuite' ),
			esc_html( $version )
		);
		$header .= '</div>'; // .pdf-doc-info

		$header .= '</div>'; // .pdf-header

		return apply_filters( 'slos_pdf_header', $header, $document, $metadata );
	}

	/**
	 * Build PDF footer
	 *
	 * @since 3.0.0
	 * @param array $document Document data.
	 * @param array $metadata Document metadata.
	 * @return string Footer HTML.
	 */
	private function build_footer( $document, $metadata ) {
		$site_name = get_bloginfo( 'name' );
		$site_url  = get_site_url();
		$date      = current_time( 'Y-m-d H:i:s' );

		$footer  = '<div class="pdf-footer">';
		$footer .= sprintf(
			'<div class="pdf-footer-left">%s</div>',
			esc_html( $site_name )
		);
		$footer .= sprintf(
			'<div class="pdf-footer-center">%s: %s</div>',
			esc_html__( 'Generated on', 'shahi-legalflowsuite' ),
			esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date ) ) )
		);
		$footer .= '<div class="pdf-footer-right"><span class="pagenum"></span></div>';
		$footer .= '</div>'; // .pdf-footer

		return apply_filters( 'slos_pdf_footer', $footer, $document, $metadata );
	}

	/**
	 * Generate table of contents from content
	 *
	 * @since 3.0.0
	 * @param string $content HTML content.
	 * @return string TOC HTML.
	 */
	private function generate_toc( $content ) {
		// Parse headings from content..
		$headings = array();
		preg_match_all( '/<h([2-4])[^>]*>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return '';
		}

		$toc  = '<div class="pdf-toc">';
		$toc .= sprintf( '<h2>%s</h2>', esc_html__( 'Table of Contents', 'shahi-legalflowsuite' ) );
		$toc .= '<ul class="toc-list">';

		foreach ( $matches as $index => $match ) {
			$level   = intval( $match[1] );
			$heading = wp_strip_all_tags( $match[2] );
			$id      = 'toc-' . $index;

			// Add ID to heading in content (this would require modifying content)..
			// For simplicity, just list headings..
			$indent_class = 'toc-level-' . $level;
			$toc         .= sprintf(
				'<li class="%s">%s</li>',
				esc_attr( $indent_class ),
				esc_html( $heading )
			);
		}

		$toc .= '</ul></div>';

		return apply_filters( 'slos_pdf_toc', $toc, $headings );
	}

	/**
	 * Check if content should include table of contents
	 *
	 * @since 3.0.0
	 * @param string $content HTML content.
	 * @return bool True if TOC should be included.
	 */
	private function should_include_toc( $content ) {
		// Count words..
		$text       = wp_strip_all_tags( $content );
		$word_count = str_word_count( $text );

		// Count headings..
		$heading_count = preg_match_all( '/<h[2-4][^>]*>/i', $content );

		// Include TOC if content is long and has multiple sections..
		return ( $word_count >= self::TOC_MIN_WORDS && $heading_count >= 3 );
	}

	/**
	 * Get PDF CSS styles
	 *
	 * @since 3.0.0
	 * @return string CSS styles
	 */
	private function get_pdf_styles() {
		$css_file = plugin_dir_path( dirname( __DIR__ ) ) . 'assets/css/pdf-styles.css';

		if ( file_exists( $css_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local CSS file, not remote.
			$css = file_get_contents( $css_file );
		} else {
			// Fallback inline styles..
			$css = $this->get_default_styles();
		}

		return apply_filters( 'slos_pdf_styles', $css );
	}

	/**
	 * Get default CSS styles for PDF
	 *
	 * @since 3.0.0
	 * @return string Default CSS
	 */
	private function get_default_styles() {
		return '
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}
			body {
				font-family: "DejaVu Sans", Arial, sans-serif;
				font-size: 11pt;
				line-height: 1.6;
				color: #333;
			}
			.pdf-wrapper {
				width: 100%;
				padding: 20px;
			}
			.pdf-header {
				border-bottom: 2px solid #0073aa;
				padding-bottom: 15px;
				margin-bottom: 25px;
			}
			.pdf-logo img {
				max-width: 150px;
				max-height: 60px;
			}
			.pdf-site-name {
				font-size: 14pt;
				font-weight: bold;
				color: #0073aa;
				margin: 5px 0;
			}
			.pdf-title {
				font-size: 18pt;
				font-weight: bold;
				margin: 10px 0;
				color: #23282d;
			}
			.pdf-doc-type {
				font-size: 12pt;
				color: #666;
				margin: 5px 0;
			}
			.pdf-meta {
				font-size: 9pt;
				color: #999;
				margin: 5px 0;
			}
			.pdf-toc {
				background: #f5f5f5;
				border: 1px solid #ddd;
				padding: 15px;
				margin: 20px 0;
				page-break-inside: avoid;
			}
			.pdf-toc h2 {
				font-size: 14pt;
				margin-bottom: 10px;
				color: #23282d;
			}
			.toc-list {
				list-style: none;
				padding-left: 0;
			}
			.toc-list li {
				padding: 3px 0;
			}
			.toc-level-2 { padding-left: 0; }
			.toc-level-3 { padding-left: 20px; }
			.toc-level-4 { padding-left: 40px; }
			.pdf-content {
				margin: 20px 0;
			}
			.pdf-content h1 {
				font-size: 16pt;
				margin: 20px 0 10px;
				color: #23282d;
			}
			.pdf-content h2 {
				font-size: 14pt;
				margin: 18px 0 8px;
				color: #23282d;
			}
			.pdf-content h3 {
				font-size: 12pt;
				margin: 16px 0 8px;
				color: #23282d;
			}
			.pdf-content p {
				margin: 10px 0;
				text-align: justify;
			}
			.pdf-content ul, .pdf-content ol {
				margin: 10px 0 10px 20px;
			}
			.pdf-content li {
				margin: 5px 0;
			}
			.pdf-content table {
				width: 100%;
				border-collapse: collapse;
				margin: 15px 0;
			}
			.pdf-content table th {
				background: #0073aa;
				color: white;
				padding: 8px;
				text-align: left;
				font-weight: bold;
			}
			.pdf-content table td {
				border: 1px solid #ddd;
				padding: 8px;
			}
			.pdf-content table tr:nth-child(even) {
				background: #f9f9f9;
			}
			.pdf-footer {
				position: fixed;
				bottom: 0;
				left: 0;
				right: 0;
				height: 40px;
				border-top: 1px solid #ddd;
				padding: 10px 20px;
				font-size: 8pt;
				color: #666;
				display: table;
				width: 100%;
			}
			.pdf-footer-left, .pdf-footer-center, .pdf-footer-right {
				display: table-cell;
				vertical-align: middle;
			}
			.pdf-footer-left {
				text-align: left;
				width: 33%;
			}
			.pdf-footer-center {
				text-align: center;
				width: 34%;
			}
			.pdf-footer-right {
				text-align: right;
				width: 33%;
			}
			@page {
				margin: 80px 50px 60px 50px;
			}
		';
	}

	/**
	 * Get cache key for document PDF
	 *
	 * @since 3.0.0
	 * @param array $document Document data.
	 * @return string Cache key.
	 */
	private function get_cache_key( $document ) {
		$doc_id  = isset( $document['id'] ) ? $document['id'] : 0;
		$version = isset( $document['version'] ) ? $document['version'] : 1;
		return sprintf( 'pdf_%d_v%d', $doc_id, $version );
	}

	/**
	 * Get cached PDF
	 *
	 * @since 3.0.0
	 * @param string $cache_key Cache key.
	 * @return string|false PDF data or false if not cached.
	 */
	private function get_cached_pdf( $cache_key ) {
		// Try object cache first..
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( $cached ) {
			return $cached;
		}

		// Try transient..
		$cached = get_transient( self::CACHE_GROUP . '_' . $cache_key );
		return $cached ? $cached : false;
	}

	/**
	 * Cache PDF data
	 *
	 * @since 3.0.0
	 * @param string $cache_key Cache key.
	 * @param string $pdf_data  PDF binary data.
	 * @return bool Success.
	 */
	private function cache_pdf( $cache_key, $pdf_data ) {
		// Store in object cache..
		wp_cache_set( $cache_key, $pdf_data, self::CACHE_GROUP, self::CACHE_EXPIRATION );

		// Store in transient as fallback..
		return set_transient( self::CACHE_GROUP . '_' . $cache_key, $pdf_data, self::CACHE_EXPIRATION );
	}

	/**
	 * Clear cached PDF for document
	 *
	 * @since 3.0.0
	 * @param int $document_id Document ID.
	 * @param int $version     Optional specific version, or null for all versions.
	 * @return bool Success.
	 */
	public function clear_cache( $document_id, $version = null ) {
		if ( null !== $version ) {
			// Clear specific version..
			$cache_key = sprintf( 'pdf_%d_v%d', $document_id, $version );
			wp_cache_delete( $cache_key, self::CACHE_GROUP );
			delete_transient( self::CACHE_GROUP . '_' . $cache_key );
		} else {
			// Clear all versions - would need to track all version numbers..
			// For now, just clear common version range..
			for ( $v = 1; $v <= 100; $v++ ) {
				$cache_key = sprintf( 'pdf_%d_v%d', $document_id, $v );
				wp_cache_delete( $cache_key, self::CACHE_GROUP );
				delete_transient( self::CACHE_GROUP . '_' . $cache_key );
			}
		}

		return true;
	}

	/**
	 * Stream PDF to browser
	 *
	 * @since 3.0.0
	 * @param string $pdf_data  PDF binary data.
	 * @param string $filename  Filename for download.
	 * @param bool   $attachment Whether to force download (true) or inline display (false).
	 * @return void
	 */
	public function stream_pdf( $pdf_data, $filename = 'document.pdf', $attachment = true ) {
		// Clear any output buffers..
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		// Set headers..
		header( 'Content-Type: application/pdf' );
		header( 'Content-Length: ' . strlen( (string) $pdf_data ) );
		header( 'Cache-Control: private, max-age=0, must-revalidate' );
		header( 'Pragma: public' );

		$disposition = $attachment ? 'attachment' : 'inline';
		if ( $attachment ) {
			header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		} else {
			header( 'Content-Disposition: inline; filename="' . sanitize_file_name( $filename ) . '"' );
		}

		// Output PDF..
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary PDF data cannot be escaped.
		echo $pdf_data; // Binary PDF data.
		exit;
	}

	/**
	 * Generate filename for document PDF
	 *
	 * @since 3.0.0
	 * @param array $document Document data.
	 * @return string Filename.
	 */
	public function generate_filename( $document ) {
		$title   = isset( $document['title'] ) ? $document['title'] : 'document';
		$version = isset( $document['version'] ) ? $document['version'] : '1';
		$date    = gmdate( 'Y-m-d' );

		// Sanitize title for filename..
		$title = sanitize_file_name( $title );
		$title = str_replace( ' ', '-', $title );
		$title = strtolower( $title );

		return sprintf( '%s-v%s-%s.pdf', $title, $version, $date );
	}
}
