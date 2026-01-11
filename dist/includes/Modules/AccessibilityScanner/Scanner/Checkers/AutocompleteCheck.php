<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AutocompleteCheck extends AbstractCheck {

	public function get_id() {
		return 'autocomplete-attribute';
	}

	public function get_description() {
		return 'Input fields collecting personal data should have an autocomplete attribute.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.5';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$inputs = $dom->getElementsByTagName( 'input' );

		$typesToCheck = array( 'text', 'email', 'tel', 'password', 'search' );
		// Heuristic: check name or id for common personal fields
		$personalFields = array( 'name', 'email', 'phone', 'tel', 'address', 'city', 'state', 'zip', 'postal', 'country', 'cc', 'card' );

		foreach ( $inputs as $input ) {
			$type = $input->getAttribute( 'type' );
			if ( empty( $type ) ) {
				$type = 'text';
			}

			if ( in_array( $type, $typesToCheck ) ) {
				$name = strtolower( $input->getAttribute( 'name' ) );
				$id   = strtolower( $input->getAttribute( 'id' ) );

				$isPersonal = false;
				foreach ( $personalFields as $field ) {
					if ( strpos( $name, $field ) !== false || strpos( $id, $field ) !== false ) {
						$isPersonal = true;
						break;
					}
				}

				if ( $isPersonal && ! $input->hasAttribute( 'autocomplete' ) ) {
					$issues[] = array(
						'element' => 'input',
						'context' => $this->get_element_html( $input ),
						'message' => 'Input field appears to collect personal data but is missing the autocomplete attribute.',
					);
				}
			}
		}

		return $issues;
	}
}
