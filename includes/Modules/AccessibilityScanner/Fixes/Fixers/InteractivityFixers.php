<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text Color Contrast Fixer
 */
class TextColorContrastFixer extends BaseFixer {
	public function get_id() {
		return 'text-color-contrast'; }
	public function get_description() {
		return 'Improve color contrast'; }

	public function fix( $content ) {
		// Contrast fixes typically require visual inspection
		// Can add aria-label as workaround
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Color Reliance Fixer
 */
class ColorRelianceFixer extends BaseFixer {
	public function get_id() {
		return 'color-reliance'; }
	public function get_description() {
		return 'Add text labels for color-only indicators'; }

	public function fix( $content ) {
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Complex Contrast Fixer
 */
class ComplexContrastFixer extends BaseFixer {
	public function get_id() {
		return 'complex-contrast'; }
	public function get_description() {
		return 'Fix complex contrast issues'; }

	public function fix( $content ) {
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Focus Indicator Fixer
 */
class FocusIndicatorFixer extends BaseFixer {
	public function get_id() {
		return 'focus-indicator'; }
	public function get_description() {
		return 'Ensure visible focus indicators'; }

	public function fix( $content ) {
		// Focus indicators are CSS-level, not content-level
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Positive Tab Index Fixer
 */
class PositiveTabIndexFixer extends BaseFixer {
	public function get_id() {
		return 'positive-tabindex'; }
	public function get_description() {
		return 'Remove positive tabindex'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		foreach ( $elements as $element ) {
			if ( $element->hasAttribute( 'tabindex' ) ) {
				$tabindex = intval( $element->getAttribute( 'tabindex' ) );
				if ( $tabindex > 0 ) {
					// Remove positive tabindex, use logical order instead
					$element->removeAttribute( 'tabindex' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Keyboard Trap Fixer
 */
class KeyboardTrapFixer extends BaseFixer {
	public function get_id() {
		return 'keyboard-trap'; }
	public function get_description() {
		return 'Fix keyboard traps'; }

	public function fix( $content ) {
		// Keyboard traps are typically JavaScript behavior issues
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Focus Order Fixer
 */
class FocusOrderFixer extends BaseFixer {
	public function get_id() {
		return 'focus-order'; }
	public function get_description() {
		return 'Fix focus order'; }

	public function fix( $content ) {
		// Focus order is determined by DOM order
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Interactive Element Fixer
 */
class InteractiveElementFixer extends BaseFixer {
	public function get_id() {
		return 'interactive-element'; }
	public function get_description() {
		return 'Make interactive elements keyboard accessible'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		foreach ( $divs as $div ) {
			$onclick = $div->getAttribute( 'onclick' );
			if ( ! empty( $onclick ) && empty( $div->getAttribute( 'tabindex' ) ) ) {
				$div->setAttribute( 'tabindex', '0' );
				if ( ! $div->hasAttribute( 'role' ) ) {
					$div->setAttribute( 'role', 'button' );
				}
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Modal Accessibility Fixer
 */
class ModalAccessibilityFixer extends BaseFixer {
	public function get_id() {
		return 'modal-accessibility'; }
	public function get_description() {
		return 'Add ARIA to modals'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		foreach ( $divs as $div ) {
			$class = $div->getAttribute( 'class' );
			if ( preg_match( '/(modal|popup|dialog|overlay)/i', $class ) ) {
				if ( ! $div->hasAttribute( 'role' ) ) {
					$div->setAttribute( 'role', 'dialog' );
					$div->setAttribute( 'aria-modal', 'true' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Custom Widget Keyboard Fixer
 */
class CustomWidgetKeyboardFixer extends BaseFixer {
	public function get_id() {
		return 'custom-widget-keyboard'; }
	public function get_description() {
		return 'Make custom widgets keyboard accessible'; }

	public function fix( $content ) {
		// This requires JavaScript implementation
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Touch Target Fixer
 */
class TouchTargetFixer extends BaseFixer {
	public function get_id() {
		return 'touch-target'; }
	public function get_description() {
		return 'Ensure adequate touch target size'; }

	public function fix( $content ) {
		// Touch target sizes are CSS-level
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Touch Gesture Fixer
 */
class TouchGestureFixer extends BaseFixer {
	public function get_id() {
		return 'touch-gesture'; }
	public function get_description() {
		return 'Provide alternatives to complex gestures'; }

	public function fix( $content ) {
		// Gesture alternatives require JavaScript
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Viewport Fixer
 */
class ViewportFixer extends BaseFixer {
	public function get_id() {
		return 'viewport'; }
	public function get_description() {
		return 'Add scalable viewport meta'; }

	public function fix( $content ) {
		// Viewport is handled via hooks in AccessibilityFixer
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

