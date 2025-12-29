<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ComplexContrastCheck extends AbstractCheck {

	public function get_id() {
		return 'complex-contrast';
	}

	public function get_description() {
		return 'Check for complex contrast issues (gradients, background images, dark mode).';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.4.3';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for background images or gradients in inline styles
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );

			// Check for gradients
			if ( preg_match( '/gradient\(/i', $style ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'Element uses a gradient background. Ensure text contrast is sufficient across the entire gradient.',
				);
			}

			// Check for background images
			if ( preg_match( '/background-image\s*:/i', $style ) || preg_match( '/url\(/i', $style ) ) {
				// Only flag if there is text content inside
				if ( trim( $element->textContent ) !== '' ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => 'Element has a background image and contains text. Ensure text remains legible over the image.',
					);
				}
			}
		}

		return $issues;
	}
}

