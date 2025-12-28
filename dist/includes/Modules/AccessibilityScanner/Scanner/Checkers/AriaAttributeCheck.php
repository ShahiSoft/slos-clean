<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AriaAttributeCheck extends AbstractCheck {

	public function get_id() {
		return 'aria-attribute';
	}

	public function get_description() {
		return 'Elements with ARIA roles must have required attributes.';
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

		$required_attributes = array(
			'checkbox'   => array( 'aria-checked' ),
			'combobox'   => array( 'aria-expanded', 'aria-controls' ),
			'scrollbar'  => array( 'aria-controls', 'aria-valuenow', 'aria-valuemax', 'aria-valuemin' ),
			'slider'     => array( 'aria-valuenow', 'aria-valuemax', 'aria-valuemin' ),
			'spinbutton' => array( 'aria-valuenow', 'aria-valuemax', 'aria-valuemin' ),
			'switch'     => array( 'aria-checked' ),
			'tab'        => array( 'aria-selected' ),
			'treeitem'   => array( 'aria-selected' ),
		);

		foreach ( $required_attributes as $role => $attrs ) {
			$elements = $xpath->query( "//*[@role='$role']" );
			foreach ( $elements as $element ) {
				foreach ( $attrs as $attr ) {
					if ( ! $element->hasAttribute( $attr ) ) {
						$issues[] = array(
							'element' => $element->tagName,
							'context' => $this->get_element_html( $element ),
							'message' => "Element with role '$role' is missing required attribute '$attr'.",
						);
					}
				}
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

