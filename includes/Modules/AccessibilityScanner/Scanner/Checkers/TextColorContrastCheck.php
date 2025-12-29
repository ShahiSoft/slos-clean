<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for text color contrast issues
 * WCAG 1.4.3 - Contrast (Minimum) (Level AA)
 */
class TextColorContrastCheck extends AbstractCheck {

	/**
	 * Named colors mapping to RGB values
	 */
	private $named_colors = array(
		'black'       => array( 0, 0, 0 ),
		'white'       => array( 255, 255, 255 ),
		'red'         => array( 255, 0, 0 ),
		'green'       => array( 0, 128, 0 ),
		'blue'        => array( 0, 0, 255 ),
		'yellow'      => array( 255, 255, 0 ),
		'orange'      => array( 255, 165, 0 ),
		'purple'      => array( 128, 0, 128 ),
		'pink'        => array( 255, 192, 203 ),
		'gray'        => array( 128, 128, 128 ),
		'grey'        => array( 128, 128, 128 ),
		'lightgray'   => array( 211, 211, 211 ),
		'lightgrey'   => array( 211, 211, 211 ),
		'darkgray'    => array( 169, 169, 169 ),
		'darkgrey'    => array( 169, 169, 169 ),
		'silver'      => array( 192, 192, 192 ),
		'navy'        => array( 0, 0, 128 ),
		'teal'        => array( 0, 128, 128 ),
		'aqua'        => array( 0, 255, 255 ),
		'cyan'        => array( 0, 255, 255 ),
		'magenta'     => array( 255, 0, 255 ),
		'fuchsia'     => array( 255, 0, 255 ),
		'lime'        => array( 0, 255, 0 ),
		'olive'       => array( 128, 128, 0 ),
		'maroon'      => array( 128, 0, 0 ),
		'brown'       => array( 165, 42, 42 ),
		'transparent' => null, // Special case
	);

	public function get_id() {
		return 'text-color-contrast';
	}

	public function get_description() {
		return 'Text must have sufficient contrast against its background (AA: 4.5:1 for normal text, 3:1 for large text).';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.4.3';
	}

	public function get_wcag_level() {
		return 'AA';
	}

	public function get_remediation_hint() {
		return 'Ensure text has contrast ratio of at least 4.5:1 (normal text) or 3:1 (large text, 18pt+ or 14pt+ bold).';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check elements with inline styles
		$this->check_inline_styles( $xpath, $issues );

		// 2. Check <style> tags for low-contrast patterns
		$this->check_style_tags( $dom, $issues );

		return $issues;
	}

	/**
	 * Check elements with inline styles
	 */
	private function check_inline_styles( $xpath, &$issues ) {
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style    = $element->getAttribute( 'style' );
			$color    = $this->extract_color( $style, 'color' );
			$bg_color = $this->extract_color( $style, 'background-color' );

			if ( ! $bg_color ) {
				$bg_color = $this->extract_color( $style, 'background' );
			}

			if ( $color && $bg_color ) {
				$ratio      = $this->calculate_contrast_ratio( $color, $bg_color );
				$is_large   = $this->is_large_text( $element, $style );
				$min_ratio  = $is_large ? 3.0 : 4.5;
				$ratio_type = $is_large ? 'large text' : 'normal text';

				if ( $ratio < $min_ratio ) {
					$issues[] = array(
						'element'    => $element->tagName,
						'context'    => $this->get_element_html( $element ),
						'message'    => sprintf(
							'Insufficient contrast ratio (%.2f:1). Expected at least %.1f:1 for %s.',
							$ratio,
							$min_ratio,
							$ratio_type
						),
						'confidence' => 'high',
					);
				}
			}
		}
	}

	/**
	 * Check <style> tags for common low-contrast patterns
	 */
	private function check_style_tags( $dom, &$issues ) {
		$styles = $dom->getElementsByTagName( 'style' );

		foreach ( $styles as $style ) {
			$css = $style->textContent;

			// Extract color declarations from CSS
			preg_match_all( '/([^{}]+)\{([^}]+)\}/s', $css, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				$selector = trim( $match[1] );
				$rules    = $match[2];

				$color    = $this->extract_color( $rules, 'color' );
				$bg_color = $this->extract_color( $rules, 'background-color' );

				if ( ! $bg_color ) {
					$bg_color = $this->extract_color( $rules, 'background' );
				}

				if ( $color && $bg_color ) {
					$ratio = $this->calculate_contrast_ratio( $color, $bg_color );

					// Use 4.5:1 as default (normal text)
					if ( $ratio < 4.5 ) {
						$issues[] = array(
							'element'    => 'style',
							'context'    => "CSS selector: $selector",
							'message'    => sprintf(
								'Potential low contrast in CSS (%.2f:1). Verify this meets WCAG requirements.',
								$ratio
							),
							'confidence' => 'potential',
						);
					}
				}
			}
		}
	}

	/**
	 * Extract color value from style string
	 */
	private function extract_color( $style, $property ) {
		// Match property with various color formats
		$pattern = '/' . preg_quote( $property, '/' ) . '\s*:\s*([^;]+)/i';
		if ( preg_match( $pattern, $style, $matches ) ) {
			return $this->parse_color( trim( $matches[1] ) );
		}
		return null;
	}

	/**
	 * Parse color string to RGB array
	 */
	private function parse_color( $color_str ) {
		$color_str = strtolower( trim( $color_str ) );

		// Named color
		if ( isset( $this->named_colors[ $color_str ] ) ) {
			return $this->named_colors[ $color_str ];
		}

		// 6-digit hex (#RRGGBB)
		if ( preg_match( '/^#([a-f0-9]{6})$/i', $color_str, $matches ) ) {
			$hex = $matches[1];
			return array(
				hexdec( substr( $hex, 0, 2 ) ),
				hexdec( substr( $hex, 2, 2 ) ),
				hexdec( substr( $hex, 4, 2 ) ),
			);
		}

		// 3-digit hex (#RGB)
		if ( preg_match( '/^#([a-f0-9]{3})$/i', $color_str, $matches ) ) {
			$hex = $matches[1];
			return array(
				hexdec( $hex[0] . $hex[0] ),
				hexdec( $hex[1] . $hex[1] ),
				hexdec( $hex[2] . $hex[2] ),
			);
		}

		// 8-digit hex (#RRGGBBAA)
		if ( preg_match( '/^#([a-f0-9]{8})$/i', $color_str, $matches ) ) {
			$hex = $matches[1];
			return array(
				hexdec( substr( $hex, 0, 2 ) ),
				hexdec( substr( $hex, 2, 2 ) ),
				hexdec( substr( $hex, 4, 2 ) ),
			);
		}

		// RGB/RGBA
		if ( preg_match( '/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $color_str, $matches ) ) {
			return array(
				(int) $matches[1],
				(int) $matches[2],
				(int) $matches[3],
			);
		}

		// HSL/HSLA
		if ( preg_match( '/hsla?\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%/i', $color_str, $matches ) ) {
			return $this->hsl_to_rgb(
				(int) $matches[1],
				(int) $matches[2],
				(int) $matches[3]
			);
		}

		return null;
	}

	/**
	 * Convert HSL to RGB
	 */
	private function hsl_to_rgb( $h, $s, $l ) {
		$h = $h / 360;
		$s = $s / 100;
		$l = $l / 100;

		if ( $s == 0 ) {
			$r = $g = $b = $l;
		} else {
			$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
			$p = 2 * $l - $q;
			$r = $this->hue_to_rgb( $p, $q, $h + 1 / 3 );
			$g = $this->hue_to_rgb( $p, $q, $h );
			$b = $this->hue_to_rgb( $p, $q, $h - 1 / 3 );
		}

		return array(
			round( $r * 255 ),
			round( $g * 255 ),
			round( $b * 255 ),
		);
	}

	private function hue_to_rgb( $p, $q, $t ) {
		if ( $t < 0 ) {
			$t += 1;
		}
		if ( $t > 1 ) {
			$t -= 1;
		}
		if ( $t < 1 / 6 ) {
			return $p + ( $q - $p ) * 6 * $t;
		}
		if ( $t < 1 / 2 ) {
			return $q;
		}
		if ( $t < 2 / 3 ) {
			return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
		}
		return $p;
	}

	/**
	 * Calculate contrast ratio between two colors
	 */
	private function calculate_contrast_ratio( $c1, $c2 ) {
		$l1 = $this->get_luminance( $c1 );
		$l2 = $this->get_luminance( $c2 );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return round( ( $lighter + 0.05 ) / ( $darker + 0.05 ), 2 );
	}

	/**
	 * Get relative luminance of a color
	 */
	private function get_luminance( $rgb ) {
		$r = $rgb[0] / 255;
		$g = $rgb[1] / 255;
		$b = $rgb[2] / 255;

		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Check if text is "large" per WCAG definition
	 * Large = 18pt (24px) or 14pt (18.67px) bold
	 */
	private function is_large_text( $element, $style ) {
		$font_size   = 16; // Default
		$font_weight = 400; // Default

		// Extract font-size
		if ( preg_match( '/font-size\s*:\s*([\d.]+)(px|pt|em|rem)/i', $style, $matches ) ) {
			$value = (float) $matches[1];
			$unit  = strtolower( $matches[2] );

			switch ( $unit ) {
				case 'pt':
					$font_size = $value * 1.333; // pt to px
					break;
				case 'em':
				case 'rem':
					$font_size = $value * 16; // Assume 16px base
					break;
				default:
					$font_size = $value;
			}
		}

		// Extract font-weight
		if ( preg_match( '/font-weight\s*:\s*(\d+|bold|bolder)/i', $style, $matches ) ) {
			$weight = strtolower( $matches[1] );
			if ( $weight === 'bold' || $weight === 'bolder' ) {
				$font_weight = 700;
			} elseif ( is_numeric( $weight ) ) {
				$font_weight = (int) $weight;
			}
		}

		// Check heading tags (typically large/bold)
		$tag = strtolower( $element->tagName );
		if ( in_array( $tag, array( 'h1', 'h2', 'h3' ), true ) ) {
			return true;
		}

		// Large text: >= 24px OR >= 18.67px bold
		if ( $font_size >= 24 ) {
			return true;
		}
		if ( $font_size >= 18.67 && $font_weight >= 700 ) {
			return true;
		}

		return false;
	}
}

