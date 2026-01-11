<?php
/**
 * Focus Visible Fixer
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
 * Class FocusVisibleFixer
 *
 * Ensures focus indicators are visible.
 */
final class FocusVisibleFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-focus-indicator';
	}

	public function get_name(): string {
		return __( 'Focus Indicators', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds CSS to ensure focus indicators are visible on interactive elements, especially when outline:none is used.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '2.4.7' );
	}

	public function get_category(): string {
		return 'interactive';
	}

	public function can_fix( string $content ): bool {
		// Check for outline:none or outline:0 in inline styles
		return strpos( $content, 'outline:' ) !== false ||
				strpos( $content, 'outline: none' ) !== false ||
				strpos( $content, 'outline:0' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details       = array();

		// Find elements with outline:none or outline:0 in inline styles
		$focusable_elements = $this->query( '//a[@style] | //button[@style] | //input[@style] | //select[@style] | //textarea[@style] | //*[@tabindex and @style]' );

		foreach ( $focusable_elements as $element ) {
			$style = $element->getAttribute( 'style' );

			// Check for outline removal
			if ( preg_match( '/outline\s*:\s*(none|0)/i', $style ) ) {
				// Remove outline:none and add focus-visible alternative
				$new_style = preg_replace( '/outline\s*:\s*(none|0)\s*;?\s*/i', '', $style );

				// Add class for focus styling
				$existing_class = $element->getAttribute( 'class' );
				$element->setAttribute( 'class', trim( $existing_class . ' slos-focus-visible' ) );
				$element->setAttribute( 'style', trim( $new_style ) ?: null );

				if ( empty( trim( $new_style ) ) ) {
					$element->removeAttribute( 'style' );
				}

				++$fixes_applied;
				$details[] = array(
					'element' => $element->nodeName,
					'action'  => 'removed_outline_none',
				);
			}
		}

		// Check for style tags with problematic focus rules
		$styles = $this->query( '//style' );

		foreach ( $styles as $style_tag ) {
			$css = $style_tag->textContent;

			// Add focus-visible fallback styles
			if ( preg_match( '/(:focus\s*\{[^}]*outline\s*:\s*(none|0))/i', $css ) ) {
				$focus_css              = '
/* Added by SLOS - Focus visible fallback */
.slos-focus-visible:focus {
	outline: 2px solid #005fcc !important;
	outline-offset: 2px !important;
}
.slos-focus-visible:focus:not(:focus-visible) {
	outline: none !important;
}
.slos-focus-visible:focus-visible {
	outline: 2px solid #005fcc !important;
	outline-offset: 2px !important;
}
';
				$style_tag->textContent = $css . $focus_css;
				++$fixes_applied;
				$details[] = array(
					'action' => 'added_focus_visible_css',
				);
			}
		}

		// If no style tag exists but we added classes, add the CSS
		if ( $fixes_applied > 0 && count( $styles ) === 0 ) {
			$head = $this->query( '//head' );

			if ( count( $head ) > 0 ) {
				$style_tag              = $this->doc->createElement( 'style' );
				$style_tag->textContent = '
/* SLOS Focus Visible Fallback */
.slos-focus-visible:focus {
	outline: 2px solid #005fcc !important;
	outline-offset: 2px !important;
}
.slos-focus-visible:focus:not(:focus-visible) {
	outline: none !important;
}
.slos-focus-visible:focus-visible {
	outline: 2px solid #005fcc !important;
	outline-offset: 2px !important;
}
';
				$head[0]->appendChild( $style_tag );
				$details[] = array(
					'action' => 'created_focus_visible_styles',
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No focus indicator issues found', $content );
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
