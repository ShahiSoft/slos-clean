<?php
/**
 * Base Fixer Class
 *
 * Provides common functionality for all content-aware fixers
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class BaseFixer {

	/**
	 * Get fixer ID (matches checker ID)
	 */
	abstract public function get_id();

	/**
	 * Get fixer description
	 */
	abstract public function get_description();

	/**
	 * Apply fix to content
	 */
	abstract public function fix( $content );

	/**
	 * Get DOM from HTML content
	 */
	protected function get_dom( $content ) {
		$dom = new \DOMDocument( '1.0', 'UTF-8' );

		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );

		// Always provide a full document wrapper so body-level insertions work
		$wrapped  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>';
		$wrapped .= $content;
		$wrapped .= '</body></html>';

		// Load as HTML fragment but keep our wrapper intact
		$dom->loadHTML( $wrapped, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED );
		libxml_clear_errors();

		// Ensure body exists
		if ( ! $dom->getElementsByTagName( 'body' )->item( 0 ) ) {
			$body = $dom->createElement( 'body' );
			$dom->appendChild( $body );
		}

		return $dom;
	}

	/**
	 * Convert DOM to HTML
	 */
	protected function dom_to_html( $dom ) {
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( ! $body ) {
			return '';
		}

		$html = '';
		foreach ( $body->childNodes as $child ) {
			$html .= $dom->saveHTML( $child );
		}

		return trim( $html );
	}

	/**
	 * Generate alt text using AI/API (placeholder for now)
	 */
	protected function generate_alt_text( $image_src ) {
		// Extract filename as fallback
		$filename = basename( $image_src );
		$filename = preg_replace( '/\.[^.]+$/', '', $filename ); // Remove extension
		$filename = str_replace( array( '-', '_' ), ' ', $filename );
		return ucfirst( $filename );
	}

	/**
	 * Return fix result in standard format
	 *
	 * @param string $content Fixed content
	 * @param int $fixes_applied Number of fixes applied
	 * @return array Standard result format
	 */
	protected function return_result( $content, $fixes_applied = 0 ) {
		return array(
			'content'        => $content,
			'fixes_applied'  => $fixes_applied,
		);
	}
}

