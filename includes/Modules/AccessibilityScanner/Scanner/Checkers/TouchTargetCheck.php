<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TouchTargetCheck extends AbstractCheck {

	public function get_id() {
		return 'touch-target';
	}

	public function get_description() {
		return 'Touch targets should be at least 44x44 CSS pixels.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.5.5';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check inline styles for small width/height on interactive elements
		$elements = $xpath->query( '//a[@style] | //button[@style] | //input[@style]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );

			$width  = $this->extract_dimension( $style, 'width' );
			$height = $this->extract_dimension( $style, 'height' );

			if ( ( $width !== null && $width < 44 ) || ( $height !== null && $height < 44 ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'Interactive element has a size less than 44px (defined in inline style). Ensure touch targets are large enough.',
				);
			}
		}

		return $issues;
	}

	private function extract_dimension( $style, $prop ) {
		if ( preg_match( '/' . $prop . '\s*:\s*(\d+)px/i', $style, $matches ) ) {
			return (int) $matches[1];
		}
		return null;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

