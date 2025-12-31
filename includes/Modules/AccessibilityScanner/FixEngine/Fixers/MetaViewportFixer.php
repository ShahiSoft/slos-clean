<?php
/**
 * Meta Viewport Fixer
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
 * Class MetaViewportFixer
 * 
 * Ensures viewport meta tag allows user scaling.
 */
final class MetaViewportFixer extends AbstractFixer {

	public function get_id(): string {
		return 'meta_viewport';
	}

	public function get_name(): string {
		return __( 'Meta Viewport', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Ensures viewport meta tag allows users to zoom and scale content.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.4.4', '1.4.10' ];
	}

	public function get_category(): string {
		return 'document';
	}

	public function can_fix( string $content ): bool {
		return (bool) preg_match( '/name\s*=\s*["\']viewport["\']/i', $content );
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$viewports = $this->query( '//meta[@name="viewport"]' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $viewports as $viewport ) {
			$content_attr = $viewport->getAttribute( 'content' );
			$original_content = $content_attr;
			$needs_fix = false;

			// Check for user-scalable=no
			if ( preg_match( '/user-scalable\s*=\s*no/i', $content_attr ) ) {
				$content_attr = preg_replace( '/user-scalable\s*=\s*no/i', 'user-scalable=yes', $content_attr );
				$needs_fix = true;
			}

			// Check for maximum-scale=1.0 or similar restrictive values
			if ( preg_match( '/maximum-scale\s*=\s*1(\.0)?/i', $content_attr ) ) {
				$content_attr = preg_replace( '/maximum-scale\s*=\s*1(\.0)?/i', 'maximum-scale=5.0', $content_attr );
				$needs_fix = true;
			}

			// Check for minimum-scale too high
			if ( preg_match( '/minimum-scale\s*=\s*([0-9.]+)/i', $content_attr, $matches ) ) {
				if ( (float) $matches[1] > 0.5 ) {
					$content_attr = preg_replace( '/minimum-scale\s*=\s*[0-9.]+/i', 'minimum-scale=0.5', $content_attr );
					$needs_fix = true;
				}
			}

			if ( $needs_fix ) {
				$viewport->setAttribute( 'content', $content_attr );
				$fixes_applied++;
				$details[] = [
					'old_content' => $original_content,
					'new_content' => $content_attr,
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'Viewport meta tag allows scaling', $content );
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
