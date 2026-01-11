<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FieldsetLegendCheck extends AbstractCheck {

	public function get_id() {
		return 'fieldset-legend';
	}

	public function get_description() {
		return 'Groups of related form controls (checkboxes/radios) should be grouped with fieldset and legend.';
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
		$xpath  = new \DOMXPath( $dom );

		// Find all radio and checkbox inputs
		$inputs = $xpath->query( '//input[@type="radio"] | //input[@type="checkbox"]' );

		$groups = array();
		foreach ( $inputs as $input ) {
			$name = $input->getAttribute( 'name' );
			if ( $name ) {
				if ( ! isset( $groups[ $name ] ) ) {
					$groups[ $name ] = array();
				}
				$groups[ $name ][] = $input;
			}
		}

		foreach ( $groups as $name => $groupInputs ) {
			if ( count( $groupInputs ) > 1 ) {
				// Check if they share a common fieldset parent
				$parent      = $groupInputs[0]->parentNode;
				$hasFieldset = false;

				// Traverse up to find fieldset
				while ( $parent && $parent instanceof \DOMElement ) {
					if ( $parent->tagName === 'fieldset' ) {
						// Check if fieldset has legend
						$legends = $parent->getElementsByTagName( 'legend' );
						if ( $legends->length > 0 && ! empty( trim( $legends->item( 0 )->textContent ) ) ) {
							$hasFieldset = true;
						}
						break;
					}
					$parent = $parent->parentNode;
				}

				if ( ! $hasFieldset ) {
					$issues[] = array(
						'element' => 'input',
						'context' => 'Group: ' . $name,
						'message' => "Radio/Checkbox group '$name' should be wrapped in a fieldset with a legend.",
					);
				}
			}
		}

		return $issues;
	}
}
