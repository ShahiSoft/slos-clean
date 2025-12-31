<?php
/**
 * Empty Alt Text Fixer
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
 * Class EmptyAltFixer
 * 
 * Handles images with empty alt attributes that should have descriptive text.
 */
final class EmptyAltFixer extends AbstractFixer {

	public function get_id(): string {
		return 'empty_alt';
	}

	public function get_name(): string {
		return __( 'Empty Alt Text', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Identifies images with empty alt attributes that appear to be meaningful content and adds descriptive text.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.1.1' ];
	}

	public function get_category(): string {
		return 'images';
	}

	public function can_fix( string $content ): bool {
		return (bool) preg_match( '/<img[^>]*\balt\s*=\s*["\']["\'][^>]*>/i', $content );
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find images with empty alt that are NOT decorative
		$images = $this->query( '//img[@alt="" and not(@role="presentation") and not(@aria-hidden="true")]' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $images as $img ) {
			// Skip if image appears decorative
			if ( $this->appears_decorative( $img ) ) {
				continue;
			}

			$src = $img->getAttribute( 'src' );
			$alt_text = $this->generate_alt_from_context( $img, $src );

			if ( ! empty( $alt_text ) ) {
				$img->setAttribute( 'alt', $alt_text );
				$fixes_applied++;

				$details[] = [
					'src'      => $src,
					'alt_text' => $alt_text,
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No fixable empty alt attributes found', $content );
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
	 * Check if image appears decorative
	 *
	 * @param \DOMElement $img
	 * @return bool
	 */
	private function appears_decorative( \DOMElement $img ): bool {
		$src = strtolower( $img->getAttribute( 'src' ) );
		$class = strtolower( $img->getAttribute( 'class' ) );

		// Common decorative patterns
		$decorative_patterns = [
			'spacer',
			'blank',
			'pixel',
			'divider',
			'border',
			'separator',
			'bullet',
			'icon-',
			'decoration',
			'ornament',
		];

		foreach ( $decorative_patterns as $pattern ) {
			if ( strpos( $src, $pattern ) !== false || strpos( $class, $pattern ) !== false ) {
				return true;
			}
		}

		// Check dimensions - very small images are often decorative
		$width = (int) $img->getAttribute( 'width' );
		$height = (int) $img->getAttribute( 'height' );

		if ( ( $width > 0 && $width < 10 ) || ( $height > 0 && $height < 10 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Generate alt text from context
	 *
	 * @param \DOMElement $img
	 * @param string      $src
	 * @return string
	 */
	private function generate_alt_from_context( \DOMElement $img, string $src ): string {
		// Check if image is inside a link with text
		$parent = $img->parentNode;
		if ( $parent && $parent->nodeName === 'a' ) {
			$link_text = trim( $parent->textContent );
			if ( ! empty( $link_text ) && $link_text !== $img->textContent ) {
				return $link_text;
			}
		}

		// Check for title attribute
		$title = $img->getAttribute( 'title' );
		if ( ! empty( $title ) ) {
			return $title;
		}

		// Check for nearby figcaption
		if ( $parent && $parent->nodeName === 'figure' ) {
			$figcaption = $parent->getElementsByTagName( 'figcaption' )->item( 0 );
			if ( $figcaption ) {
				$caption_text = trim( $figcaption->textContent );
				if ( ! empty( $caption_text ) ) {
					return $caption_text;
				}
			}
		}

		// Try to generate from filename
		return $this->humanize_filename( basename( parse_url( $src, PHP_URL_PATH ) ?: '' ) );
	}

	/**
	 * Convert filename to human-readable text
	 *
	 * @param string $filename
	 * @return string
	 */
	private function humanize_filename( string $filename ): string {
		$name = preg_replace( '/\.[^.]+$/', '', $filename );
		$name = str_replace( [ '-', '_' ], ' ', $name );
		$name = preg_replace( '/^(IMG|DSC|DCIM|Photo|Image|Picture|Screen Shot|Screenshot)[\s_-]*/i', '', $name );
		$name = preg_replace( '/-?\d+x\d+$/', '', $name );
		$name = preg_replace( '/[\s_-]+\d+$/', '', $name );
		$name = preg_replace( '/\s+/', ' ', trim( $name ) );
		return ucwords( strtolower( $name ) );
	}
}
