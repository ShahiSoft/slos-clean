<?php
/**
 * Tab Index Fixer
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
 * Class TabIndexFixer
 *
 * Fixes problematic tabindex values.
 */
final class TabIndexFixer extends AbstractFixer {

	public function get_id(): string {
		return 'invalid-tabindex';
	}

	public function get_name(): string {
		return __( 'Tab Index', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Removes positive tabindex values that disrupt natural tab order and ensures interactive elements are focusable.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '2.4.3' );
	}

	public function get_category(): string {
		return 'interactive';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, 'tabindex' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find elements with positive tabindex
		$positive_tabindex = $this->query( '//*[@tabindex > 0]' );
		$fixes_applied     = 0;
		$details           = array();

		foreach ( $positive_tabindex as $element ) {
			$old_value = $element->getAttribute( 'tabindex' );

			// Check if this is a naturally focusable element
			$tag                 = strtolower( $element->nodeName );
			$naturally_focusable = in_array( $tag, array( 'a', 'button', 'input', 'select', 'textarea' ) );

			if ( $naturally_focusable ) {
				// Remove tabindex entirely - natural focus order is fine
				$element->removeAttribute( 'tabindex' );
			} else {
				// Set to 0 to include in natural tab order
				$element->setAttribute( 'tabindex', '0' );
			}

			++$fixes_applied;
			$details[] = array(
				'element'   => $tag,
				'class'     => $element->getAttribute( 'class' ),
				'old_value' => $old_value,
				'new_value' => $naturally_focusable ? 'removed' : '0',
			);
		}

		// Also check for interactive elements with tabindex="-1" that shouldn't have it
		$negative_tabindex = $this->query( '//a[@href and @tabindex="-1"] | //button[@tabindex="-1"] | //input[not(@type="hidden") and @tabindex="-1"]' );

		foreach ( $negative_tabindex as $element ) {
			// Check if it's not intentionally hidden from tab order
			$class = strtolower( $element->getAttribute( 'class' ) );
			$role  = $element->getAttribute( 'role' );

			// Skip if it seems intentional
			if ( strpos( $class, 'skip' ) !== false ||
				strpos( $class, 'hidden' ) !== false ||
				$role === 'presentation' ) {
				continue;
			}

			// Remove the negative tabindex
			$element->removeAttribute( 'tabindex' );
			++$fixes_applied;
			$details[] = array(
				'element'   => $element->nodeName,
				'class'     => $element->getAttribute( 'class' ),
				'old_value' => '-1',
				'new_value' => 'removed',
				'reason'    => 'interactive element should be focusable',
			);
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No tabindex issues found', $content );
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
