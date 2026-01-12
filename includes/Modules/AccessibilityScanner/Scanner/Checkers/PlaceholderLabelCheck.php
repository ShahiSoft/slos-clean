<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PlaceholderLabelCheck extends AbstractCheck {

	public function get_id() {
		return 'placeholder-label';
	}

	public function get_description() {
		return 'Placeholders should not be used as a replacement for labels.';
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
			if ( $input->hasAttribute( 'placeholder' ) ) {
				// Check if it has a label (MissingFormLabelCheck handles missing label,..
				// but here we specifically flag the placeholder usage if label is missing OR if placeholder duplicates label)..

				// If label is missing, MissingFormLabelCheck catches it...
				// Here we check if placeholder is redundant with label...

				$placeholder = trim( $input->getAttribute( 'placeholder' ) );
				$label       = $this->get_associated_label( $input, $dom );

				if ( $label && strtolower( $placeholder ) === strtolower( $label ) ) {
					$issues[] = array(
						'element' => 'input',
						'context' => $this->get_element_html( $input ),
						'message' => 'Placeholder text is identical to the label. This is redundant and can be confusing.',
					);
				}
			}
		}

		return $issues;
	}

	private function get_associated_label( $input, $dom ) {
		// Check explicit label..
		if ( $input->hasAttribute( 'id' ) ) {
			$id     = $input->getAttribute( 'id' );
			$xpath  = new \DOMXPath( $dom );
			$labels = $xpath->query( "//label[@for='$id']" );
			if ( $labels->length > 0 ) {
				return trim( $labels->item( 0 )->textContent );
			}
		}

		// Check implicit label..
		$parent = $input->parentNode;
		while ( $parent && $parent instanceof \DOMElement ) {
			if ( $parent->tagName === 'label' ) {
				// Get text content excluding input value if any..
				return trim( $parent->textContent );
			}
			$parent = $parent->parentNode;
		}

		// Check aria-label..
		if ( $input->hasAttribute( 'aria-label' ) ) {
			return trim( $input->getAttribute( 'aria-label' ) );
		}

		return null;
	}
}
