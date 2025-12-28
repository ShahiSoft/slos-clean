<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RequiredAttributeCheck extends AbstractCheck {

	public function get_id() {
		return 'required-attribute';
	}

	public function get_description() {
		return 'Required fields should use the required attribute or aria-required.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '3.3.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$inputs = $dom->getElementsByTagName( 'input' );

		foreach ( $inputs as $input ) {
			$hasRequired     = $input->hasAttribute( 'required' );
			$hasAriaRequired = $input->hasAttribute( 'aria-required' );

			// If one is present, check consistency?
			// Or check if label indicates required (*) but attribute is missing?

			// Heuristic: Check label for asterisk
			$label = $this->get_associated_label( $input, $dom );
			if ( $label && strpos( $label, '*' ) !== false ) {
				if ( ! $hasRequired && ! $hasAriaRequired ) {
					$issues[] = array(
						'element' => 'input',
						'context' => $this->get_element_html( $input ),
						'message' => 'Label contains asterisk (*) implying required, but input is missing required attribute or aria-required.',
					);
				}
			}
		}

		return $issues;
	}

	private function get_associated_label( $input, $dom ) {
		if ( $input->hasAttribute( 'id' ) ) {
			$id     = $input->getAttribute( 'id' );
			$xpath  = new \DOMXPath( $dom );
			$labels = $xpath->query( "//label[@for='$id']" );
			if ( $labels->length > 0 ) {
				return trim( $labels->item( 0 )->textContent );
			}
		}
		return null;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

