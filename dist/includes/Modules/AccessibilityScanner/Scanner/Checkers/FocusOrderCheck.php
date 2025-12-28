<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FocusOrderCheck extends AbstractCheck {

	public function get_id() {
		return 'focus-order';
	}

	public function get_description() {
		return 'Focus order should be logical and preserved.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.3';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for positive tabindex values which disrupt natural tab order
		$elements = $xpath->query( '//*[@tabindex]' );

		foreach ( $elements as $element ) {
			$tabindex = (int) $element->getAttribute( 'tabindex' );

			if ( $tabindex > 0 ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => "Element has positive tabindex ($tabindex). Avoid using positive tabindex values as they disrupt the natural focus order.",
				);
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

