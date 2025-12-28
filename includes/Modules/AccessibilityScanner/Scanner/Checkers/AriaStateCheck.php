<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AriaStateCheck extends AbstractCheck {

	public function get_id() {
		return 'aria-state';
	}

	public function get_description() {
		return 'ARIA state attributes must have valid values.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '4.1.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$boolean_states = array( 'aria-hidden', 'aria-expanded', 'aria-pressed', 'aria-checked', 'aria-disabled', 'aria-readonly', 'aria-required', 'aria-selected', 'aria-invalid' );

		foreach ( $boolean_states as $state ) {
			$elements = $xpath->query( "//*[@$state]" );
			foreach ( $elements as $element ) {
				$value = $element->getAttribute( $state );
				// aria-checked and aria-pressed can be 'mixed'
				if ( ( $state === 'aria-checked' || $state === 'aria-pressed' ) && $value === 'mixed' ) {
					continue;
				}

				if ( $value !== 'true' && $value !== 'false' ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => "Invalid value '$value' for attribute '$state'. Must be 'true' or 'false'.",
					);
				}
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

