<?php
/**
 * Link Target Blank Fixer
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
 * Class LinkTargetBlankFixer
 * 
 * Adds warning indicators for links that open in new windows/tabs.
 */
final class LinkTargetBlankFixer extends AbstractFixer {

	public function get_id(): string {
		return 'link_target_blank';
	}

	public function get_name(): string {
		return __( 'New Window Links', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds accessible warnings to links that open in new windows/tabs, and includes security attributes.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '3.2.5' ];
	}

	public function get_category(): string {
		return 'links';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, 'target="_blank"' ) !== false || strpos( $content, "target='_blank'" ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$links = $this->query( '//a[@target="_blank"]' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $links as $link ) {
			$modified = false;

			// Add security attributes
			$rel = $link->getAttribute( 'rel' ) ?: '';
			$rel_parts = array_filter( array_map( 'trim', explode( ' ', $rel ) ) );

			if ( ! in_array( 'noopener', $rel_parts ) ) {
				$rel_parts[] = 'noopener';
				$modified = true;
			}

			if ( ! in_array( 'noreferrer', $rel_parts ) ) {
				$rel_parts[] = 'noreferrer';
				$modified = true;
			}

			if ( $modified ) {
				$link->setAttribute( 'rel', implode( ' ', $rel_parts ) );
			}

			// Add warning to aria-label if not already present
			$aria_label = $link->getAttribute( 'aria-label' );
			$link_text = trim( $link->textContent );
			$current_label = ! empty( $aria_label ) ? $aria_label : $link_text;

			$new_window_warning = __( '(opens in new window)', 'shahi-legalflowsuite' );

			if ( ! empty( $current_label ) && stripos( $current_label, 'new window' ) === false && stripos( $current_label, 'new tab' ) === false ) {
				$new_aria = rtrim( $current_label, '.' ) . ' ' . $new_window_warning;
				$link->setAttribute( 'aria-label', $new_aria );
				$modified = true;
			}

			// Add visually hidden text for screen readers if no aria-label was added
			if ( empty( $current_label ) ) {
				$span = $this->doc->createElement( 'span' );
				$span->setAttribute( 'class', 'screen-reader-text sr-only visually-hidden' );
				$span->textContent = $new_window_warning;
				$link->appendChild( $span );
				$modified = true;
			}

			if ( $modified ) {
				$fixes_applied++;
				$details[] = [
					'href'       => $link->getAttribute( 'href' ),
					'text'       => $link_text,
					'aria_label' => $link->getAttribute( 'aria-label' ),
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No target="_blank" links found to fix', $content );
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
