<?php
/**
 * Color Utilities Class
 *
 * Provides color manipulation and contrast calculation utilities
 * for WCAG 2.2 compliance checking.
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner
 * @since 1.0.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Utilities
 *
 * Handles color conversions, luminance calculations, and contrast ratio
 * computations according to WCAG 2.2 specifications.
 */
class ColorUtils {

	/**
	 * Convert hex color to RGB array
	 *
	 * @param string $hex Hexadecimal color code (#RRGGBB or #RGB)
	 * @return array RGB values [r, g, b] (0-255)
	 */
	public static function hex_to_rgb( $hex ) {
		$hex = ltrim( $hex, '#' );

		// Handle 3-character hex codes
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return array(
			'r' => hexdec( substr( $hex, 0, 2 ) ),
			'g' => hexdec( substr( $hex, 2, 2 ) ),
			'b' => hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * Convert RGB to hex color
	 *
	 * @param int $r Red value (0-255)
	 * @param int $g Green value (0-255)
	 * @param int $b Blue value (0-255)
	 * @return string Hexadecimal color code
	 */
	public static function rgb_to_hex( $r, $g, $b ) {
		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Calculate relative luminance of a color
	 *
	 * According to WCAG 2.2 formula:
	 * https://www.w3.org/TR/WCAG22/#dfn-relative-luminance
	 *
	 * @param array $rgb RGB values ['r' => int, 'g' => int, 'b' => int]
	 * @return float Relative luminance (0-1)
	 */
	public static function get_relative_luminance( $rgb ) {
		$r = $rgb['r'] / 255;
		$g = $rgb['g'] / 255;
		$b = $rgb['b'] / 255;

		// Apply gamma correction
		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		// Calculate luminance
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Calculate contrast ratio between two colors
	 *
	 * According to WCAG 2.2 formula:
	 * https://www.w3.org/TR/WCAG22/#dfn-contrast-ratio
	 *
	 * @param string|array $color1 First color (hex or RGB array)
	 * @param string|array $color2 Second color (hex or RGB array)
	 * @return float Contrast ratio (1-21)
	 */
	public static function get_contrast_ratio( $color1, $color2 ) {
		// Convert to RGB if needed
		if ( is_string( $color1 ) ) {
			$color1 = self::hex_to_rgb( $color1 );
		}
		if ( is_string( $color2 ) ) {
			$color2 = self::hex_to_rgb( $color2 );
		}

		$l1 = self::get_relative_luminance( $color1 );
		$l2 = self::get_relative_luminance( $color2 );

		// Ensure lighter color is L1
		if ( $l2 > $l1 ) {
			list($l1, $l2) = array( $l2, $l1 );
		}

		return ( $l1 + 0.05 ) / ( $l2 + 0.05 );
	}

	/**
	 * Check if contrast ratio meets WCAG AA standard
	 *
	 * @param float $ratio Contrast ratio
	 * @param bool  $is_large_text Whether text is large (18pt+ or 14pt+ bold)
	 * @return bool True if meets AA standard
	 */
	public static function meets_wcag_aa( $ratio, $is_large_text = false ) {
		$minimum = $is_large_text ? 3.0 : 4.5;
		return $ratio >= $minimum;
	}

	/**
	 * Check if contrast ratio meets WCAG AAA standard
	 *
	 * @param float $ratio Contrast ratio
	 * @param bool  $is_large_text Whether text is large (18pt+ or 14pt+ bold)
	 * @return bool True if meets AAA standard
	 */
	public static function meets_wcag_aaa( $ratio, $is_large_text = false ) {
		$minimum = $is_large_text ? 4.5 : 7.0;
		return $ratio >= $minimum;
	}

	/**
	 * Check if contrast ratio meets UI component standard (WCAG 2.2 1.4.11)
	 *
	 * @param float $ratio Contrast ratio
	 * @return bool True if meets standard (3:1 minimum)
	 */
	public static function meets_ui_component_contrast( $ratio ) {
		return $ratio >= 3.0;
	}

	/**
	 * Parse CSS color value to RGB
	 *
	 * Supports: hex, rgb(), rgba(), named colors
	 *
	 * @param string $color CSS color value
	 * @return array|null RGB array or null if invalid
	 */
	public static function parse_css_color( $color ) {
		$color = trim( $color );

		// Hex color
		if ( strpos( $color, '#' ) === 0 ) {
			return self::hex_to_rgb( $color );
		}

		// RGB/RGBA
		if ( preg_match( '/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $color, $matches ) ) {
			return array(
				'r' => (int) $matches[1],
				'g' => (int) $matches[2],
				'b' => (int) $matches[3],
			);
		}

		// Named colors (basic set)
		$named_colors = array(
			'black'  => '#000000',
			'white'  => '#ffffff',
			'red'    => '#ff0000',
			'green'  => '#008000',
			'blue'   => '#0000ff',
			'yellow' => '#ffff00',
			'gray'   => '#808080',
			'grey'   => '#808080',
		);

		if ( isset( $named_colors[ strtolower( $color ) ] ) ) {
			return self::hex_to_rgb( $named_colors[ strtolower( $color ) ] );
		}

		return null;
	}

	/**
	 * Suggest accessible color alternatives
	 *
	 * @param string $foreground Foreground color (hex)
	 * @param string $background Background color (hex)
	 * @param string $target_level 'AA' or 'AAA'
	 * @param bool   $is_large_text Whether text is large
	 * @return array Suggested colors ['foreground' => string, 'background' => string, 'ratio' => float]
	 */
	public static function suggest_accessible_colors( $foreground, $background, $target_level = 'AA', $is_large_text = false ) {
		$fg_rgb = self::hex_to_rgb( $foreground );
		$bg_rgb = self::hex_to_rgb( $background );

		$current_ratio = self::get_contrast_ratio( $fg_rgb, $bg_rgb );
		$target_ratio  = $target_level === 'AAA'
			? ( $is_large_text ? 4.5 : 7.0 )
			: ( $is_large_text ? 3.0 : 4.5 );

		if ( $current_ratio >= $target_ratio ) {
			return array(
				'foreground'   => $foreground,
				'background'   => $background,
				'ratio'        => $current_ratio,
				'meets_target' => true,
			);
		}

		// Try darkening foreground
		$adjusted_fg = self::adjust_color_for_contrast( $fg_rgb, $bg_rgb, $target_ratio, 'darken' );
		if ( $adjusted_fg ) {
			return array(
				'foreground'   => self::rgb_to_hex( $adjusted_fg['r'], $adjusted_fg['g'], $adjusted_fg['b'] ),
				'background'   => $background,
				'ratio'        => self::get_contrast_ratio( $adjusted_fg, $bg_rgb ),
				'meets_target' => true,
			);
		}

		// Fallback to black/white
		$use_black = self::get_relative_luminance( $bg_rgb ) > 0.5;
		return array(
			'foreground'   => $use_black ? '#000000' : '#ffffff',
			'background'   => $background,
			'ratio'        => self::get_contrast_ratio(
				$use_black ? array(
					'r' => 0,
					'g' => 0,
					'b' => 0,
				) : array(
					'r' => 255,
					'g' => 255,
					'b' => 255,
				),
				$bg_rgb
			),
			'meets_target' => true,
		);
	}

	/**
	 * Adjust color to meet contrast requirement
	 *
	 * @param array  $color RGB array to adjust
	 * @param array  $background Background RGB array
	 * @param float  $target_ratio Target contrast ratio
	 * @param string $direction 'darken' or 'lighten'
	 * @return array|null Adjusted RGB array or null if not achievable
	 */
	private static function adjust_color_for_contrast( $color, $background, $target_ratio, $direction = 'darken' ) {
		$step     = $direction === 'darken' ? -10 : 10;
		$adjusted = $color;

		for ( $i = 0; $i < 25; $i++ ) {
			$adjusted['r'] = max( 0, min( 255, $adjusted['r'] + $step ) );
			$adjusted['g'] = max( 0, min( 255, $adjusted['g'] + $step ) );
			$adjusted['b'] = max( 0, min( 255, $adjusted['b'] + $step ) );

			$ratio = self::get_contrast_ratio( $adjusted, $background );
			if ( $ratio >= $target_ratio ) {
				return $adjusted;
			}
		}

		return null;
	}
}

