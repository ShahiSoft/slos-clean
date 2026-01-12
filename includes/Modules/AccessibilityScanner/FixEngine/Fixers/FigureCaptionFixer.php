<?php
/**
 * Figure Caption Fixer
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
 * Class FigureCaptionFixer
 *
 * Ensures figure elements have proper figcaption elements.
 */
final class FigureCaptionFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-figure-caption';
	}

	public function get_name(): string {
		return __( 'Figure Caption', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds figcaption elements to figure elements containing images.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.1.1' );
	}

	public function get_category(): string {
		return 'images';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<figure' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find figures with images but without figcaption..
		$figures       = $this->query( '//figure[.//img and not(figcaption)]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $figures as $figure ) {
			// Get the image inside..
			$images = $this->query( './/img', $figure );

			if ( count( $images ) === 0 ) {
				continue;
			}

			$img   = $images[0];
			$alt   = $img->getAttribute( 'alt' );
			$title = $img->getAttribute( 'title' );

			// Only add figcaption if we have content for it..
			$caption_text = '';

			if ( ! empty( $title ) ) {
				$caption_text = $title;
			} elseif ( ! empty( $alt ) && strlen( $alt ) > 10 ) {
				// Use alt if it's descriptive (more than just a filename)..
				$caption_text = $alt;
			} else {
				// Try to extract from src filename..
				$src = $img->getAttribute( 'src' );
				if ( $src ) {
					$filename = basename( parse_url( $src, PHP_URL_PATH ) ?: $src );
					$name     = pathinfo( $filename, PATHINFO_FILENAME );
					$name     = str_replace( array( '-', '_' ), ' ', $name );
					// Only use if it looks like a proper name..
					if ( ! preg_match( '/^[a-f0-9]{8,}$/i', $name ) ) {
						$caption_text = ucwords( $name );
					}
				}
			}

			// Skip if we couldn't determine a good caption..
			if ( empty( $caption_text ) ) {
				continue;
			}

			$figcaption              = $this->doc->createElement( 'figcaption' );
			$figcaption->textContent = $caption_text;

			// Add figcaption at the end of figure..
			$figure->appendChild( $figcaption );

			++$fixes_applied;
			$details[] = array(
				'caption' => $caption_text,
			);
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All figures have captions or no suitable caption text found', $content );
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
