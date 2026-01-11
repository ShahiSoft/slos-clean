<?php
/**
 * Color Contrast Fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ColorContrastFixer
 *
 * Adds data attributes to elements with potential contrast issues for CSS-based fixing.
 */
final class ColorContrastFixer extends AbstractFixer {

	public function get_id(): string {
		return 'text-color-contrast';
	}

	public function get_name(): string {
		return __( 'Color Contrast', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Marks elements with inline color styles for accessibility review and adds high-contrast mode support.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.4.3', '1.4.6' );
	}

	public function get_category(): string {
		return 'visual';
	}

	public function can_fix( string $content ): bool {
		// Look for inline styles with color definitions
		return (bool) preg_match( '/style\s*=\s*["\'][^"\']*(?:color|background):/i', $content );
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find elements with inline color styles
		$elements      = $this->query( '//*[@style]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );

			// Check if style has color-related properties
			if ( ! preg_match( '/(^|;)\s*(color|background(-color)?)\s*:/i', $style ) ) {
				continue;
			}

			// Add data attribute for CSS-based high contrast mode override
			$element->setAttribute( 'data-slos-contrast-check', 'true' );

			// Add class for potential high-contrast mode
			$existing_class = $element->getAttribute( 'class' );
			$new_class      = trim( $existing_class . ' slos-contrast-target' );
			$element->setAttribute( 'class', $new_class );

			++$fixes_applied;
			$details[] = array(
				'tag'   => $element->nodeName,
				'style' => substr( $style, 0, 100 ),
			);
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No elements with inline color styles found', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}
}
