<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FocusIndicatorCheck extends AbstractCheck {

	public function get_id() {
		return 'focus-indicator';
	}

	public function get_description() {
		return 'Focus indicators should not be removed (outline: none) without replacement.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.7';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check inline styles for outline: 0 or outline: none
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );
			if ( preg_match( '/outline\s*:\s*(0|none)/i', $style ) ) {
				// Check if there is a replacement style (border, background, box-shadow)
				// This is a simple heuristic.
				$hasReplacement = preg_match( '/(border|background|box-shadow)/i', $style );

				if ( ! $hasReplacement ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => 'Element removes focus outline (outline: 0/none) via inline style without providing an obvious replacement.',
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

