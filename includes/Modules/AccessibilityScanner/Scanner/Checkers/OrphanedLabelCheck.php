<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OrphanedLabelCheck extends AbstractCheck {

	public function get_id() {
		return 'orphaned-label';
	}

	public function get_description() {
		return 'Labels with "for" attribute must match the ID of a form control.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$labels = $dom->getElementsByTagName( 'label' );

		foreach ( $labels as $label ) {
			if ( $label->hasAttribute( 'for' ) ) {
				$for     = $label->getAttribute( 'for' );
				$element = $dom->getElementById( $for );

				if ( ! $element ) {
					$issues[] = array(
						'element' => 'label',
						'context' => $this->get_element_html( $label ),
						'message' => "Label points to ID '$for' which does not exist in the document.",
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

