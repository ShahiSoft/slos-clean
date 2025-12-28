<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TextColorContrastCheck extends AbstractCheck {

	public function get_id() {
		return 'text-color-contrast';
	}

	public function get_description() {
		return 'Text must have sufficient contrast against its background (AA: 4.5:1).';
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

		// Find elements with inline styles containing color and background-color
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style    = $element->getAttribute( 'style' );
			$color    = $this->extract_color( $style, 'color' );
			$bg_color = $this->extract_color( $style, 'background-color' );

			// If background is not set inline, we can't check (it might be in CSS class)
			// But if both are set inline, we can check.
			if ( $color && $bg_color ) {
				$ratio = $this->calculate_contrast_ratio( $color, $bg_color );

				if ( $ratio < 4.5 ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => "Insufficient contrast ratio ($ratio:1). Expected at least 4.5:1 for normal text.",
					);
				}
			}
		}

		return $issues;
	}

	private function extract_color( $style, $property ) {
		if ( preg_match( '/' . $property . '\s*:\s*([^;]+)/i', $style, $matches ) ) {
			return $this->parse_color( $matches[1] );
		}
		return null;
	}

	private function parse_color( $color_str ) {
		$color_str = trim( $color_str );

		// Hex
		if ( preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color_str, $matches ) ) {
			$hex = $matches[1];
			if ( strlen( $hex ) === 3 ) {
				$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			}
			return array(
				hexdec( substr( $hex, 0, 2 ) ),
				hexdec( substr( $hex, 2, 2 ) ),
				hexdec( substr( $hex, 4, 2 ) ),
			);
		}

		// RGB
		if ( preg_match( '/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $color_str, $matches ) ) {
			return array( $matches[1], $matches[2], $matches[3] );
		}

		return null;
	}

	private function calculate_contrast_ratio( $c1, $c2 ) {
		$l1 = $this->get_luminance( $c1 );
		$l2 = $this->get_luminance( $c2 );

		if ( $l1 > $l2 ) {
			return round( ( $l1 + 0.05 ) / ( $l2 + 0.05 ), 2 );
		} else {
			return round( ( $l2 + 0.05 ) / ( $l1 + 0.05 ), 2 );
		}
	}

	private function get_luminance( $rgb ) {
		$r = $rgb[0] / 255;
		$g = $rgb[1] / 255;
		$b = $rgb[2] / 255;

		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

