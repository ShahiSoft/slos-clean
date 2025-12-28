<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PositiveTabIndexCheck extends AbstractCheck {

	public function get_id() {
		return 'positive-tabindex';
	}

	public function get_description() {
		return 'Avoid using positive tabindex values.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.3';
	}

	public function check( $content ) {
		$issues   = array();
		$dom      = $this->get_dom( $content );
		$xpath    = new \DOMXPath( $dom );
		$elements = $xpath->query( '//*[@tabindex]' );

		foreach ( $elements as $element ) {
			$tabindex = $element->getAttribute( 'tabindex' );
			if ( is_numeric( $tabindex ) && intval( $tabindex ) > 0 ) {
				$issues[] = array(
					'message' => "Element has a positive tabindex ($tabindex). This disrupts the natural tab order.",
					'element' => $dom->saveHTML( $element ),
					'context' => $element->tagName,
				);
			}
		}

		return $issues;
	}
}

