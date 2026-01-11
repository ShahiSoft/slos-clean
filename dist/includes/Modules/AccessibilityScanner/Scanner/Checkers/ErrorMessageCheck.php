<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ErrorMessageCheck extends AbstractCheck {

	public function get_id() {
		return 'error-message';
	}

	public function get_description() {
		return 'Form fields with errors must have associated error messages.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '3.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Find inputs with aria-invalid="true"
		$inputs = $xpath->query( '//*[@aria-invalid="true"]' );

		foreach ( $inputs as $input ) {
			$hasErrormessage = $input->hasAttribute( 'aria-errormessage' );
			$hasDescribedby  = $input->hasAttribute( 'aria-describedby' );

			if ( ! $hasErrormessage && ! $hasDescribedby ) {
				$issues[] = array(
					'element' => $input->tagName,
					'context' => $this->get_element_html( $input ),
					'message' => 'Input marked as invalid (aria-invalid="true") is missing aria-errormessage or aria-describedby to associate the error text.',
				);
			}
		}

		return $issues;
	}
}
