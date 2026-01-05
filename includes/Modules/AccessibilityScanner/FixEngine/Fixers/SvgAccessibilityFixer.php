<?php
/**
 * SVG Accessibility Fixer
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
 * Class SvgAccessibilityFixer
 * 
 * Adds accessibility attributes to inline SVG elements.
 */
final class SvgAccessibilityFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-svg-title';
	}

	public function get_name(): string {
		return __( 'SVG Accessibility', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds proper ARIA attributes, titles, and descriptions to inline SVG elements.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.1.1', '4.1.2' ];
	}

	public function get_category(): string {
		return 'images';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<svg' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$svgs = $this->query( '//svg' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $svgs as $svg ) {
			$modified = false;
			$fix_detail = [];

			// Check if SVG is decorative
			$is_decorative = $this->is_decorative_svg( $svg );

			if ( $is_decorative ) {
				// Mark as decorative
				if ( ! $svg->hasAttribute( 'aria-hidden' ) ) {
					$svg->setAttribute( 'aria-hidden', 'true' );
					$modified = true;
					$fix_detail['action'] = 'marked_decorative';
				}
				if ( $svg->hasAttribute( 'role' ) && $svg->getAttribute( 'role' ) !== 'presentation' ) {
					$svg->setAttribute( 'role', 'presentation' );
					$modified = true;
				}
			} else {
				// Make accessible
				if ( ! $svg->hasAttribute( 'role' ) ) {
					$svg->setAttribute( 'role', 'img' );
					$modified = true;
				}

				// Ensure focusable="false" for better IE/Edge handling
				if ( ! $svg->hasAttribute( 'focusable' ) ) {
					$svg->setAttribute( 'focusable', 'false' );
					$modified = true;
				}

				// Add title if missing
				$title = $this->get_svg_title( $svg );
				if ( ! $title ) {
					$title_text = $this->generate_svg_title( $svg );
					if ( $title_text ) {
						$title_id = 'svg-title-' . wp_generate_uuid4();
						
						$title_element = $this->doc->createElementNS( 'http://www.w3.org/2000/svg', 'title' );
						$title_element->setAttribute( 'id', $title_id );
						$title_element->textContent = $title_text;
						
						// Insert as first child
						if ( $svg->firstChild ) {
							$svg->insertBefore( $title_element, $svg->firstChild );
						} else {
							$svg->appendChild( $title_element );
						}
						
						// Update aria-labelledby
						$svg->setAttribute( 'aria-labelledby', $title_id );
						$modified = true;
						$fix_detail['title_added'] = $title_text;
					}
				} else {
					// Ensure title has ID and is referenced
					$title_id = $title->getAttribute( 'id' );
					if ( empty( $title_id ) ) {
						$title_id = 'svg-title-' . wp_generate_uuid4();
						$title->setAttribute( 'id', $title_id );
						$modified = true;
					}

					if ( ! $svg->hasAttribute( 'aria-labelledby' ) ) {
						$svg->setAttribute( 'aria-labelledby', $title_id );
						$modified = true;
					}
				}
			}

			if ( $modified ) {
				$fixes_applied++;
				$details[] = array_merge( [
					'svg_class' => $svg->getAttribute( 'class' ),
				], $fix_detail );
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No SVG accessibility issues found', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Check if SVG appears decorative
	 *
	 * @param \DOMElement $svg
	 * @return bool
	 */
	private function is_decorative_svg( \DOMElement $svg ): bool {
		// Check explicit decorative markers
		if ( $svg->getAttribute( 'aria-hidden' ) === 'true' ) {
			return true;
		}
		if ( $svg->getAttribute( 'role' ) === 'presentation' ) {
			return true;
		}

		// Check class for decorative patterns
		$class = strtolower( $svg->getAttribute( 'class' ) );
		$decorative_patterns = [ 'icon', 'decorative', 'ornament', 'divider', 'separator' ];
		
		foreach ( $decorative_patterns as $pattern ) {
			if ( strpos( $class, $pattern ) !== false ) {
				return true;
			}
		}

		// Check if it's very small (likely an icon)
		$width = $svg->getAttribute( 'width' );
		$height = $svg->getAttribute( 'height' );
		
		if ( ( is_numeric( $width ) && (float) $width < 32 ) || 
			 ( is_numeric( $height ) && (float) $height < 32 ) ) {
			// Small SVGs are often icons - only decorative if no meaningful content
			if ( ! $this->get_svg_title( $svg ) && empty( trim( $svg->textContent ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get SVG title element
	 *
	 * @param \DOMElement $svg
	 * @return \DOMElement|null
	 */
	private function get_svg_title( \DOMElement $svg ): ?\DOMElement {
		$titles = $svg->getElementsByTagName( 'title' );
		
		if ( $titles->length > 0 ) {
			$title = $titles->item( 0 );
			if ( ! empty( trim( $title->textContent ) ) ) {
				return $title;
			}
		}

		return null;
	}

	/**
	 * Generate title for SVG
	 *
	 * @param \DOMElement $svg
	 * @return string
	 */
	private function generate_svg_title( \DOMElement $svg ): string {
		// Check for aria-label
		$aria_label = $svg->getAttribute( 'aria-label' );
		if ( ! empty( $aria_label ) ) {
			return $aria_label;
		}

		// Check parent for context
		$parent = $svg->parentNode;
		if ( $parent instanceof \DOMElement ) {
			// If inside button or link, use their label
			if ( in_array( $parent->nodeName, [ 'button', 'a' ] ) ) {
				$parent_label = $parent->getAttribute( 'aria-label' );
				if ( ! empty( $parent_label ) ) {
					return $parent_label;
				}
				
				$parent_text = trim( $parent->textContent );
				if ( ! empty( $parent_text ) ) {
					return $parent_text;
				}
			}
		}

		// Try to derive from class
		$class = $svg->getAttribute( 'class' );
		if ( ! empty( $class ) ) {
			// Extract meaningful part from class
			$class = preg_replace( '/^(svg-|icon-|fa-|bi-|feather-)/', '', $class );
			$class = str_replace( [ '-', '_' ], ' ', $class );
			$class = trim( $class );
			
			if ( strlen( $class ) > 2 && strlen( $class ) < 50 ) {
				return ucwords( $class ) . ' icon';
			}
		}

		return __( 'Icon', 'shahi-legalflowsuite' );
	}
}
