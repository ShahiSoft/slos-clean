<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KeyboardTrapCheck extends AbstractCheck {

	public function get_id() {
		return 'keyboard-trap';
	}

	public function get_description() {
		return 'Avoid keyboard traps where focus cannot be moved away.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.1.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for elements with event handlers that might trap focus
		// This is very heuristic as we can't execute JS.
		// We look for onkeydown/onkeypress/onfocus without corresponding exit logic (impossible to check fully)
		// So we flag usage of these handlers on non-standard elements as potential risks.

		$elements = $xpath->query( '//*[@onkeydown or @onkeypress or @onfocus or @onblur]' );

		foreach ( $elements as $element ) {
			// If it's a standard input, it's usually fine.
			if ( in_array( $element->tagName, array( 'input', 'select', 'textarea', 'button', 'a' ) ) ) {
				continue;
			}

			$issues[] = array(
				'element' => $element->tagName,
				'context' => $this->get_element_html( $element ),
				'message' => 'Element has keyboard event handlers. Ensure focus is not trapped and can be moved away using standard keyboard navigation.',
			);
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

