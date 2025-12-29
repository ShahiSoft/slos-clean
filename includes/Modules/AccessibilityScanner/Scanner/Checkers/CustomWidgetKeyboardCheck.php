<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomWidgetKeyboardCheck extends AbstractCheck {

	public function get_id() {
		return 'custom-widget-keyboard';
	}

	public function get_description() {
		return 'Custom widgets must support keyboard interaction.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Find elements with mouse events but no keyboard events
		$elements = $xpath->query( '//*[@onmouseover or @onmouseout or @onmousedown or @onmouseup]' );

		foreach ( $elements as $element ) {
			$hasKeyboard = $element->hasAttribute( 'onfocus' ) ||
							$element->hasAttribute( 'onblur' ) ||
							$element->hasAttribute( 'onkeydown' ) ||
							$element->hasAttribute( 'onkeyup' ) ||
							$element->hasAttribute( 'onkeypress' );

			if ( ! $hasKeyboard ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'Element has mouse event handlers but no keyboard equivalents. Ensure functionality is accessible via keyboard.',
				);
			}
		}

		return $issues;
	}
}

