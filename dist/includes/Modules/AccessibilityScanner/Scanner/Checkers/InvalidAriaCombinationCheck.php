<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InvalidAriaCombinationCheck extends AbstractCheck {

	public function get_id() {
		return 'invalid-aria-combination';
	}

	public function get_description() {
		return 'ARIA attributes must be compatible with the element role.';
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

		// Example: aria-checked on something that isn't a checkbox/radio/switch
		$elements = $xpath->query( '//*[@aria-checked]' );
		foreach ( $elements as $element ) {
			$role = $element->getAttribute( 'role' );
			$tag  = $element->tagName;

			$valid_roles = array( 'checkbox', 'radio', 'switch', 'menuitemcheckbox', 'menuitemradio', 'treeitem', 'option' );
			$valid_tags  = array( 'input' ); // input type=checkbox/radio

			if ( ! in_array( $role, $valid_roles ) && ! in_array( $tag, $valid_tags ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => "Attribute 'aria-checked' is not allowed on this element (role='$role').",
				);
			}
		}

		return $issues;
	}
}
