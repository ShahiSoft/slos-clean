<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ColorRelianceCheck extends AbstractCheck {

	public function get_id() {
		return 'color-reliance';
	}

	public function get_description() {
		return 'Instructions should not rely solely on color (e.g. "click the red button").';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.4.1';
	}

	public function check( $content ) {
		$issues = array();
		// Strip tags to check text content..
		$text = strip_tags( $content );

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
