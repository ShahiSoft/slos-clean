<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BackgroundImageCheck extends AbstractCheck {

	public function get_id() {
		return 'background-image';
	}

	public function get_description() {
		return 'Inline background images should not convey essential information.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Find elements with style attribute containing 'background' or 'background-image'
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );
			if ( preg_match( '/background(-image)?\s*:/i', $style ) && preg_match( '/url\(/i', $style ) ) {
				// Check if element has text content or aria-label
				$text            = trim( $element->textContent );
				$aria_label      = $element->getAttribute( 'aria-label' );
				$aria_labelledby = $element->getAttribute( 'aria-labelledby' );

				if ( empty( $text ) && empty( $aria_label ) && empty( $aria_labelledby ) ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => 'Element has background image but no text content or accessible name. Ensure background image is decorative only.',
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

