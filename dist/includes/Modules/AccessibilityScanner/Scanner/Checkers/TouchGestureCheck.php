<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TouchGestureCheck extends AbstractCheck {

	public function get_id() {
		return 'touch-gesture';
	}

	public function get_description() {
		return 'Ensure complex gestures have simple alternatives.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.5.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for touch-specific events
		$elements = $xpath->query( '//*[@ontouchstart or @ontouchmove or @ontouchend]' );

		foreach ( $elements as $element ) {
			$issues[] = array(
				'element' => $element->tagName,
				'context' => $this->get_element_html( $element ),
				'message' => 'Element uses touch-specific event handlers. Ensure there are alternative input methods (mouse, keyboard) and that complex gestures are not required.',
			);
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

