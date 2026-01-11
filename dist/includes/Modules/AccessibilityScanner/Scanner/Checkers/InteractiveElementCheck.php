<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InteractiveElementCheck extends AbstractCheck {

	public function get_id() {
		return 'interactive-element';
	}

	public function get_description() {
		return 'Interactive elements (onclick) must be keyboard accessible.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '2.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Find elements with onclick but not a native interactive element
		$elements = $xpath->query( '//*[@onclick]' );

		$interactiveTags = array( 'a', 'button', 'input', 'select', 'textarea', 'details', 'summary' );

		foreach ( $elements as $element ) {
			if ( in_array( $element->tagName, $interactiveTags ) ) {
				continue;
			}

			// Check if it has tabindex and role
			$hasTabindex   = $element->hasAttribute( 'tabindex' );
			$hasRole       = $element->hasAttribute( 'role' );
			$hasKeyHandler = $element->hasAttribute( 'onkeydown' ) || $element->hasAttribute( 'onkeypress' ) || $element->hasAttribute( 'onkeyup' );

			if ( ! $hasTabindex || ! $hasRole || ! $hasKeyHandler ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'Element has onclick handler but is not natively interactive. It requires tabindex, a valid role, and keyboard event handlers (onkeydown/onkeyup).',
				);
			}
		}

		return $issues;
	}
}
