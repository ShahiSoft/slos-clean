<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for adequate touch target sizes
 * WCAG 2.5.5 - Target Size (Level AAA) / 2.5.8 - Target Size Minimum (Level AA)
 */
class TouchTargetCheck extends AbstractCheck {

	/**
	 * Minimum touch target size per WCAG 2.5.5 (AAA) and 2.5.8 (AA)
	 */
	private $min_size_aaa = 44; // 44x44px for AAA
	private $min_size_aa  = 24; // 24x24px for AA (2.5.8)

	public function get_id() {
		return 'touch-target';
	}

	public function get_description() {
		return 'Touch targets should be at least 44x44 CSS pixels (AAA) or 24x24 CSS pixels (AA minimum).';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.5.5';
	}

	public function get_wcag_level() {
		return 'AAA';
	}

	public function get_remediation_hint() {
		return 'Increase the size of interactive elements to at least 44x44 pixels, or use padding to expand the touch area.';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check all interactive elements (expanded query)
		$elements = $xpath->query( '//a[@style] | //button[@style] | //input[@style] | //*[@onclick and @style] | //*[@role="button" and @style] | //*[@role="link" and @style]' );

		foreach ( $elements as $element ) {
			$size = $this->get_element_size( $element );

			if ( $size !== null ) {
				$width  = $size['width'];
				$height = $size['height'];

				// Check against AAA standard (44px)
				if ( $width < $this->min_size_aaa || $height < $this->min_size_aaa ) {
					// Determine severity based on size
					if ( $width < $this->min_size_aa || $height < $this->min_size_aa ) {
						// Below AA minimum - more severe
						$issues[] = array(
							'element'    => $element->tagName,
							'context'    => $this->get_element_html( $element ),
							'message'    => sprintf(
								'Touch target size (%dpx × %dpx) is below AA minimum (24px × 24px). This may cause difficulty for users with motor impairments.',
								$width,
								$height
							),
							'severity'   => 'warning',
							'confidence' => 'high',
						);
					} else {
						// Between AA and AAA
						$issues[] = array(
							'element'    => $element->tagName,
							'context'    => $this->get_element_html( $element ),
							'message'    => sprintf(
								'Touch target size (%dpx × %dpx) meets AA but is below AAA recommended minimum (44px × 44px).',
								$width,
								$height
							),
							'severity'   => 'notice',
							'confidence' => 'high',
						);
					}
				}
			}
		}

		// Check for small icon buttons without explicit size
		$this->check_icon_buttons( $xpath, $issues );

		return $issues;
	}

	/**
	 * Get element size from inline styles
	 */
	private function get_element_size( $element ) {
		$style = $element->getAttribute( 'style' );
		if ( empty( $style ) ) {
			return null;
		}

		$width      = $this->extract_dimension( $style, 'width' );
		$height     = $this->extract_dimension( $style, 'height' );
		$min_width  = $this->extract_dimension( $style, 'min-width' );
		$min_height = $this->extract_dimension( $style, 'min-height' );
		$padding    = $this->extract_padding( $style );

		// Use the larger of width or min-width
		$effective_width  = max( $width ?? 0, $min_width ?? 0 );
		$effective_height = max( $height ?? 0, $min_height ?? 0 );

		// Add padding to effective size (padding expands clickable area)
		if ( $padding !== null ) {
			$effective_width  += $padding['left'] + $padding['right'];
			$effective_height += $padding['top'] + $padding['bottom'];
		}

		if ( $effective_width === 0 && $effective_height === 0 ) {
			return null;
		}

		return array(
			'width'  => $effective_width > 0 ? $effective_width : $this->min_size_aaa,
			'height' => $effective_height > 0 ? $effective_height : $this->min_size_aaa,
		);
	}

	/**
	 * Extract dimension from style string (supports px, em, rem)
	 */
	private function extract_dimension( $style, $prop ) {
		// Match property with various units
		if ( preg_match( '/' . preg_quote( $prop, '/' ) . '\s*:\s*([\d.]+)(px|em|rem|pt)/i', $style, $matches ) ) {
			$value = (float) $matches[1];
			$unit  = strtolower( $matches[2] );

			// Convert to pixels (approximate)
			switch ( $unit ) {
				case 'em':
				case 'rem':
					return round( $value * 16 ); // Assume 16px base
				case 'pt':
					return round( $value * 1.333 ); // pt to px
				default:
					return round( $value );
			}
		}
		return null;
	}

	/**
	 * Extract padding values from style string
	 */
	private function extract_padding( $style ) {
		// Simple padding extraction (shorthand or individual)
		$padding = array(
			'top'    => 0,
			'right'  => 0,
			'bottom' => 0,
			'left'   => 0,
		);

		// Check individual padding properties
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$value = $this->extract_dimension( $style, "padding-{$side}" );
			if ( $value !== null ) {
				$padding[ $side ] = $value;
			}
		}

		// Check shorthand padding (simplified - single value only)
		if ( preg_match( '/padding\s*:\s*([\d.]+)(px|em|rem)/i', $style, $matches ) ) {
			$value = $this->convert_to_pixels( (float) $matches[1], $matches[2] );
			$padding = array(
				'top'    => $value,
				'right'  => $value,
				'bottom' => $value,
				'left'   => $value,
			);
		}

		// Return null if all zeros
		if ( array_sum( $padding ) === 0 ) {
			return null;
		}

		return $padding;
	}

	/**
	 * Convert value to pixels
	 */
	private function convert_to_pixels( $value, $unit ) {
		switch ( strtolower( $unit ) ) {
			case 'em':
			case 'rem':
				return round( $value * 16 );
			case 'pt':
				return round( $value * 1.333 );
			default:
				return round( $value );
		}
	}

	/**
	 * Check for icon buttons that might be too small
	 */
	private function check_icon_buttons( $xpath, &$issues ) {
		// Find buttons with only icon content (no text)
		$icon_buttons = $xpath->query( '//button[.//i or .//svg or .//img] | //a[.//i or .//svg]' );

		foreach ( $icon_buttons as $button ) {
			// Check if button has meaningful text content
			$text = trim( preg_replace( '/\s+/', ' ', $button->textContent ) );

			if ( strlen( $text ) <= 2 ) {
				// Icon-only button - check for aria-label
				$has_label = $button->hasAttribute( 'aria-label' ) || $button->hasAttribute( 'title' );

				if ( ! $has_label ) {
					$issues[] = array(
						'element'    => $button->tagName,
						'context'    => $this->get_element_html( $button ),
						'message'    => 'Icon-only interactive element detected. Ensure it has adequate touch target size (44x44px) and an accessible label.',
						'severity'   => 'notice',
						'confidence' => 'medium',
					);
				}
			}
		}
	}
}

