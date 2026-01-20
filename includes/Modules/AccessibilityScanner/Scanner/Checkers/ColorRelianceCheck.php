<?php
/**
 * Color Reliance Accessibility Check
 *
 * Checks for content that relies solely on color to convey information,
 * which violates WCAG 1.4.1 (Use of Color).
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers
 * @since 3.0.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Reliance Check
 *
 * Checks for accessibility violations where content relies solely on color
 * to convey information, which can be problematic for color-blind users.
 *
 * @since 3.0.0
 */
class ColorRelianceCheck extends AbstractCheck {

	/**
	 * Get the unique identifier for this check.
	 *
	 * @since 3.0.0
	 * @return string The check identifier.
	 */
	public function get_id() {
		return 'color-reliance';
	}

	/**
	 * Get the description of this accessibility check.
	 *
	 * @since 3.0.0
	 * @return string The check description.
	 */
	public function get_description() {
		return 'Instructions should not rely solely on color (e.g. "click the red button").';
	}

	/**
	 * Get the severity level of this check.
	 *
	 * @since 3.0.0
	 * @return string The severity level.
	 */
	public function get_severity() {
		return 'warning';
	}

	/**
	 * Get the WCAG criteria this check addresses.
	 *
	 * @since 3.0.0
	 * @return string The WCAG criteria identifier.
	 */
	public function get_wcag_criteria() {
		return '1.4.1';
	}

	/**
	 * Perform the accessibility check on the given content.
	 *
	 * @since 3.0.0
	 * @param string $content The content to check.
	 * @return array Array of issues found.
	 */
	public function check( $content ) {
		$issues = array();
		// Strip tags to check text content..
		$text = wp_strip_all_tags( $content );

		// Phrases that imply color reliance..
		$patterns = array(
			'/click the (red|green|blue|yellow|orange|purple|black|white) button/i',
			'/items in (red|green|blue)/i',
			'/marked in (red|green|blue)/i',
			'/colored (red|green|blue)/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $text, $matches ) ) {
				$issues[] = array(
					'element' => 'text',
					'context' => $matches[0],
					'message' => 'Content appears to rely on color to convey information ("' . $matches[0] . '"). Ensure information is also available through text or structure.',
				);
			}
		}

		return $issues;
	}
}
