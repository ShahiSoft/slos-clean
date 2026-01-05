<?php
/**
 * Decorative Image Fixer
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
 * Class DecorativeImageFixer
 * 
 * Properly marks decorative images with empty alt and role="presentation".
 */
final class DecorativeImageFixer extends AbstractFixer {

	public function get_id(): string {
		return 'decorative-image';
	}

	public function get_name(): string {
		return __( 'Decorative Images', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Marks images that appear to be decorative with empty alt and role="presentation" for screen readers.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.1.1' ];
	}

	public function get_category(): string {
		return 'images';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<img' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$images = $this->query( '//img' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $images as $img ) {
			if ( ! $this->is_decorative_image( $img ) ) {
				continue;
			}

			// Already properly marked
			if ( $img->getAttribute( 'role' ) === 'presentation' && $img->getAttribute( 'alt' ) === '' ) {
				continue;
			}

			$modified = false;

			// Set empty alt
			if ( ! $img->hasAttribute( 'alt' ) || $img->getAttribute( 'alt' ) !== '' ) {
				$img->setAttribute( 'alt', '' );
				$modified = true;
			}

			// Add role="presentation"
			if ( $img->getAttribute( 'role' ) !== 'presentation' ) {
				$img->setAttribute( 'role', 'presentation' );
				$modified = true;
			}

			// Add aria-hidden="true" for extra safety
			if ( ! $img->hasAttribute( 'aria-hidden' ) ) {
				$img->setAttribute( 'aria-hidden', 'true' );
				$modified = true;
			}

			if ( $modified ) {
				$fixes_applied++;
				$details[] = [
					'src'    => $img->getAttribute( 'src' ),
					'action' => 'marked_decorative',
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No decorative images need fixing', $content );
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
	private function is_decorative_image( \DOMElement $img ): bool {
		$src = strtolower( $img->getAttribute( 'src' ) );
		$class = strtolower( $img->getAttribute( 'class' ) );
		$width = (int) $img->getAttribute( 'width' );
		$height = (int) $img->getAttribute( 'height' );

		// Common decorative filename patterns
		$decorative_patterns = [
			'spacer',
			'blank',
			'pixel',
			'transparent',
			'divider',
			'border',
			'separator',
			'line',
			'bullet',
			'decoration',
			'ornament',
			'bg-',
			'background',
			'pattern',
			'texture',
		];

		foreach ( $decorative_patterns as $pattern ) {
			if ( strpos( $src, $pattern ) !== false || strpos( $class, $pattern ) !== false ) {
				return true;
			}
		}

		// Very small images (spacers, bullets)
		if ( ( $width > 0 && $width < 5 ) || ( $height > 0 && $height < 5 ) ) {
			return true;
		}

		// 1x1 tracking pixels
		if ( $width === 1 && $height === 1 ) {
			return true;
		}

		// Check for decorative classes
		$decorative_classes = [ 'decorative', 'bg-image', 'ornamental', 'divider-img' ];
		foreach ( $decorative_classes as $dec_class ) {
			if ( strpos( $class, $dec_class ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
