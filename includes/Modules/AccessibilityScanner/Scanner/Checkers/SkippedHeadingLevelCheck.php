<?php
/**
 * Skipped Heading Level Check
 *
 * Checks for skipped heading levels in content.
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers
 * @since 3.3.0
 */

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Allow DOM properties like tagName, textContent

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skipped Heading Level Check Class
 *
 * Checks for skipped heading levels (e.g., H2 to H4).
 */
class SkippedHeadingLevelCheck extends AbstractCheck {

	/**
	 * Get the check ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'skipped-heading-level';
	}

	/**
	 * Get the check description.
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Heading levels should not be skipped (e.g. H2 to H4).';
	}

	/**
	 * Get the check severity.
	 *
	 * @return string
	 */
	public function get_severity() {
		return 'warning';
	}

	/**
	 * Get the WCAG criteria this check addresses.
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '1.3.1';
	}

	/**
	 * Check the content for skipped heading levels.
	 *
	 * @param string $content The content to check.
	 * @return array
	 */
	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );
		// Select all heading elements in document order..
		$headings = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );

		$previous_level = 0;

		foreach ( $headings as $heading ) {
			$current_level = intval( substr( $heading->tagName, 1 ) );

			// If current > previous + 1, it's a skip...
			// We ignore the first heading (previousLevel == 0) for this check..
			// as MissingH1Check handles the start...
			if ( $previous_level > 0 && $current_level > $previous_level + 1 ) {
				$issues[] = array(
					'message' => 'Skipped heading level: ' . strtoupper( $heading->tagName ) . " follows H$previous_level.",
					'element' => $dom->saveHTML( $heading ),
					'context' => substr( wp_strip_all_tags( $heading->textContent ), 0, 50 ),
				);
			}

			$previous_level = $current_level;
		}

		return $issues;
	}
}
