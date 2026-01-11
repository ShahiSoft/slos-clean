<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LiveRegionCheck extends AbstractCheck {

	public function get_id() {
		return 'live-region';
	}

	public function get_description() {
		return 'Live regions must be used correctly for dynamic content.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '4.1.3';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for aria-live usage
		$elements = $xpath->query( '//*[@aria-live]' );

		foreach ( $elements as $element ) {
			$value = $element->getAttribute( 'aria-live' );
			if ( ! in_array( $value, array( 'off', 'polite', 'assertive' ) ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => "Invalid aria-live value '$value'. Must be 'off', 'polite', or 'assertive'.",
				);
			}

			// Check if it has relevant roles (status, alert, log, marquee, timer)
			// This is just informational/best practice check
		}

		return $issues;
	}
}
