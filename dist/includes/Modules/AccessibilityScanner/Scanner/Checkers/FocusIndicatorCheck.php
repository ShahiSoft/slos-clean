<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for focus indicator visibility
 * WCAG 2.4.7 - Focus Visible (Level AA)
 */
class FocusIndicatorCheck extends AbstractCheck {

	public function get_id() {
		return 'focus-indicator';
	}

	public function get_description() {
		return 'Focus indicators should not be removed (outline: none) without a visible replacement.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.7';
	}

	public function get_wcag_level() {
		return 'AA';
	}

	public function get_remediation_hint() {
		return 'If removing outline, provide an alternative focus indicator using border, box-shadow, or background-color change.';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check inline styles for outline removal..
		$this->check_inline_focus_removal( $xpath, $issues );

		// 2. Check <style> tags for focus removal patterns..
		$this->check_style_tags_for_focus_removal( $dom, $issues );

		// 3. Check custom focusable elements (tabindex)..
		$this->check_custom_focusable_elements( $xpath, $issues );

		return $issues;
	}

	/**
	 * Check inline styles for outline: 0/none
	 */
	private function check_inline_focus_removal( $xpath, &$issues ) {
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );

			if ( preg_match( '/outline\s*:\s*(0|none)/i', $style ) ) {
				// Check if there is a replacement style..
				$has_replacement = preg_match( '/(border|background|box-shadow)/i', $style );

				if ( ! $has_replacement ) {
					$issues[] = array(
						'element'    => $element->tagName,
						'context'    => $this->get_element_html( $element ),
						'message'    => 'Element removes focus outline (outline: 0/none) via inline style without providing a visible replacement.',
						'confidence' => 'high',
					);
				}
			}
		}
	}

	/**
	 * Check <style> tags for :focus { outline: none } patterns
	 */
	private function check_style_tags_for_focus_removal( $dom, &$issues ) {
		$styles = $dom->getElementsByTagName( 'style' );

		foreach ( $styles as $style ) {
			$css = $style->textContent;

			// Detect *:focus (global focus removal) - most severe..
			if ( preg_match( '/\*\s*:focus\s*\{[^}]*outline\s*:\s*(none|0)/i', $css ) ) {
				$issues[] = array(
					'element'    => 'style',
					'context'    => 'CSS: *:focus { outline: none/0 }',
					'message'    => 'Global focus indicator removed (*:focus { outline: none }). This affects ALL focusable elements and severely impacts keyboard navigation.',
					'severity'   => 'critical',
					'confidence' => 'high',
				);
				continue; // Don't double-report for global
			}

			// Detect :focus { outline: none/0 } patterns..
			if ( preg_match_all( '/([^{,]+):focus\s*\{([^}]+)\}/i', $css, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$selector = trim( $match[1] );
					$rules    = $match[2];

					// Check if outline is removed..
					if ( preg_match( '/outline\s*:\s*(none|0)/i', $rules ) ) {
						// Check if there's a replacement style..
						$has_replacement = preg_match( '/(box-shadow|border|background)/i', $rules );

						if ( ! $has_replacement ) {
							$issues[] = array(
								'element'    => 'style',
								'context'    => "CSS selector: {$selector}:focus",
								'message'    => "Focus indicator removed via CSS ({$selector}:focus { outline: none }) without visible replacement.",
								'confidence' => 'high',
							);
						}
					}
				}
			}

			// Detect :focus-visible removal (modern browsers)..
			if ( preg_match( '/:focus-visible\s*\{[^}]*outline\s*:\s*(none|0)/i', $css ) ) {
				if ( ! preg_match( '/:focus-visible\s*\{[^}]*(box-shadow|border|background)/i', $css ) ) {
					$issues[] = array(
						'element'    => 'style',
						'context'    => 'CSS: :focus-visible { outline: none/0 }',
						'message'    => 'Focus-visible indicator removed via CSS without visible replacement.',
						'confidence' => 'high',
					);
				}
			}
		}
	}

	/**
	 * Check custom focusable elements (tabindex) for focus visibility
	 */
	private function check_custom_focusable_elements( $xpath, &$issues ) {
		// Elements with positive tabindex that might need custom focus styles..
		$elements = $xpath->query( '//*[@tabindex]' );

		foreach ( $elements as $element ) {
			$tabindex = $element->getAttribute( 'tabindex' );

			// Skip tabindex="-1" (programmatically focusable only)..
			if ( $tabindex === '-1' ) {
				continue;
			}

			$tag = strtolower( $element->tagName );

			// Skip natively focusable elements (they have browser default focus)..
			if ( in_array( $tag, array( 'a', 'button', 'input', 'select', 'textarea', 'area' ), true ) ) {
				continue;
			}

			// Custom focusable element - warn about focus visibility..
			$issues[] = array(
				'element'    => $tag,
				'context'    => $this->get_element_html( $element ),
				'message'    => "Custom focusable element (<{$tag} tabindex=\"{$tabindex}\">). Ensure it has a visible focus indicator.",
				'severity'   => 'notice',
				'confidence' => 'medium',
			);
		}
	}
}
