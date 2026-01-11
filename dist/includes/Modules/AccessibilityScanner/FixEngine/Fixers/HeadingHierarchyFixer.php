<?php
/**
 * Heading Hierarchy Fixer
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
 * Class HeadingHierarchyFixer
 *
 * Fixes skipped heading levels to maintain proper hierarchy.
 */
final class HeadingHierarchyFixer extends AbstractFixer {

	public function get_id(): string {
		return 'skipped-heading-level';
	}

	public function get_name(): string {
		return __( 'Heading Hierarchy', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Fixes skipped heading levels to maintain proper hierarchy (h1, h2, h3 without skipping).', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.1', '2.4.6' );
	}

	public function get_category(): string {
		return 'headings';
	}

	public function can_fix( string $content ): bool {
		return (bool) preg_match( '/<h[1-6][^>]*>/i', $content );
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Get all headings in document order
		$headings = $this->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );

		if ( count( $headings ) < 2 ) {
			return FixResult::skipped( $this->get_id(), 'Not enough headings to analyze hierarchy', $content );
		}

		$fixes_applied = 0;
		$details       = array();
		$last_level    = 0;

		foreach ( $headings as $heading ) {
			$current_level = (int) substr( $heading->nodeName, 1 );

			// First heading
			if ( $last_level === 0 ) {
				$last_level = $current_level;
				continue;
			}

			// Check for skipped level (going from h2 to h4, etc.)
			if ( $current_level > $last_level + 1 ) {
				$correct_level = $last_level + 1;
				$new_tag_name  = 'h' . $correct_level;

				// Create new heading element
				$new_heading = $this->doc->createElement( $new_tag_name );

				// Copy attributes
				foreach ( $heading->attributes as $attr ) {
					$new_heading->setAttribute( $attr->nodeName, $attr->nodeValue );
				}

				// Copy children
				while ( $heading->firstChild ) {
					$new_heading->appendChild( $heading->firstChild );
				}

				// Replace
				$heading->parentNode->replaceChild( $new_heading, $heading );

				++$fixes_applied;
				$details[] = array(
					'old_level' => $current_level,
					'new_level' => $correct_level,
					'text'      => substr( $new_heading->textContent, 0, 50 ),
				);

				$last_level = $correct_level;
			} else {
				$last_level = $current_level;
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'Heading hierarchy is correct', $content );
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
