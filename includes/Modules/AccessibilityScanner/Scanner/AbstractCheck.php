<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractCheck implements CheckInterface {

	/**
	 * Get WCAG Success Criteria
	 * Default implementation returns empty string
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '';
	}

	/**
	 * Helper to find elements in HTML content
	 *
	 * @param string $content HTML content
	 * @param string $tag Tag name
	 * @return \DOMNodeList
	 */
	protected function get_elements( $content, $tag ) {
		$dom = new \DOMDocument();
		// Suppress warnings for invalid HTML
		libxml_use_internal_errors( true );
		// Load HTML with UTF-8 encoding hack
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		return $dom->getElementsByTagName( $tag );
	}

	/**
	 * Get DOMDocument from content
	 *
	 * @param string $content
	 * @return \DOMDocument
	 */
	protected function get_dom( $content ) {
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		return $dom;
	}
}

