<?php
/**
 * Empty Heading Fixer
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
 * Class EmptyHeadingFixer
 *
 * Removes or fixes empty heading elements.
 */
final class EmptyHeadingFixer extends AbstractFixer {

	public function get_id(): string {
		return 'empty-heading';
	}

	public function get_name(): string {
		return __( 'Empty Headings', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Removes empty heading elements or converts them to paragraphs if they contain meaningful non-text content.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.1', '2.4.6' );
	}

	public function get_category(): string {
		return 'headings';
	}

	public function can_fix( string $content ): bool {
		return (bool) preg_match( '/<h[1-6][^>]*>\s*<\/h[1-6]>/i', $content );
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied      = 0;
		$details            = array();
		$headings_to_remove = array();

		for ( $level = 1; $level <= 6; $level++ ) {
			$headings = $this->query( "//h{$level}" );

			foreach ( $headings as $heading ) {
				$text_content = trim( $heading->textContent );

				// Check if heading is truly empty..
				if ( ! empty( $text_content ) ) {
					continue;
				}

				// Check for meaningful child elements (images with alt)..
				$has_meaningful_content = false;
				$images                 = $heading->getElementsByTagName( 'img' );

				foreach ( $images as $img ) {
					if ( ! empty( $img->getAttribute( 'alt' ) ) ) {
						$has_meaningful_content = true;
						break;
					}
				}

				if ( $has_meaningful_content ) {
					// Convert to paragraph to preserve content without structural impact..
					$paragraph = $this->doc->createElement( 'p' );

					// Copy children..
					while ( $heading->firstChild ) {
						$paragraph->appendChild( $heading->firstChild );
					}

					// Copy class if any..
					if ( $heading->hasAttribute( 'class' ) ) {
						$paragraph->setAttribute( 'class', $heading->getAttribute( 'class' ) );
					}

					$heading->parentNode->replaceChild( $paragraph, $heading );

					$details[] = array(
						'level'  => $level,
						'action' => 'converted_to_paragraph',
					);
				} else {
					// Mark for removal (don't remove during iteration)..
					$headings_to_remove[] = $heading;

					$details[] = array(
						'level'  => $level,
						'action' => 'removed',
					);
				}

				++$fixes_applied;
			}
		}

		// Remove empty headings..
		foreach ( $headings_to_remove as $heading ) {
			if ( $heading->parentNode ) {
				$heading->parentNode->removeChild( $heading );
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No empty headings found', $content );
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
