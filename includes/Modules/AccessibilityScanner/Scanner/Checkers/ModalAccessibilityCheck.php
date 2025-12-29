<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ModalAccessibilityCheck extends AbstractCheck {

	public function get_id() {
		return 'modal-accessibility';
	}

	public function get_description() {
		return 'Modals must have proper ARIA roles and attributes.';
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

		// Find elements with role="dialog" or role="alertdialog"
		$modals = $xpath->query( '//*[@role="dialog"] | //*[@role="alertdialog"]' );

		foreach ( $modals as $modal ) {
			// Check for aria-modal="true"
			if ( ! $modal->hasAttribute( 'aria-modal' ) || $modal->getAttribute( 'aria-modal' ) !== 'true' ) {
				$issues[] = array(
					'element' => $modal->tagName,
					'context' => $this->get_element_html( $modal ),
					'message' => 'Modal dialog (role="dialog") should have aria-modal="true".',
				);
			}

			// Check for accessible name
			$hasName = $modal->hasAttribute( 'aria-label' ) || $modal->hasAttribute( 'aria-labelledby' );
			if ( ! $hasName ) {
				$issues[] = array(
					'element' => $modal->tagName,
					'context' => $this->get_element_html( $modal ),
					'message' => 'Modal dialog must have an accessible name via aria-label or aria-labelledby.',
				);
			}
		}

		return $issues;
	}
}

