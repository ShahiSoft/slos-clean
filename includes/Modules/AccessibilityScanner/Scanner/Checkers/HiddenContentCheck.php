<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HiddenContentCheck extends AbstractCheck {

	public function get_id() {
		return 'hidden-content';
	}

	public function get_description() {
		return 'Focusable elements should not be inside hidden containers.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '2.4.3';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for aria-hidden="true"
		$hiddenElements = $xpath->query( '//*[@aria-hidden="true"]' );

		$focusableTags = array( 'a', 'button', 'input', 'select', 'textarea' );

		foreach ( $hiddenElements as $element ) {
			foreach ( $focusableTags as $tag ) {
				$focusable = $element->getElementsByTagName( $tag );
				if ( $focusable->length > 0 ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => "Element has aria-hidden='true' but contains focusable <$tag> elements. This creates a keyboard trap for screen reader users.",
					);
				}
			}
		}

		return $issues;
	}
}
