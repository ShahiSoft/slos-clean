<?php
/**
 * Audio Accessibility Fixer
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
 * Class AudioAccessibilityFixer
 * 
 * Adds accessibility features to audio elements.
 */
final class AudioAccessibilityFixer extends AbstractFixer {

	public function get_id(): string {
		return 'audio_accessibility';
	}

	public function get_name(): string {
		return __( 'Audio Accessibility', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Ensures audio elements have controls and proper accessibility attributes.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.2.1', '1.4.2' ];
	}

	public function get_category(): string {
		return 'media';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<audio' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$audios = $this->query( '//audio' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $audios as $audio ) {
			$fixed_this = false;
			$fix_details = [ 'src' => $audio->getAttribute( 'src' ) ];

			// Ensure controls attribute is present
			if ( ! $audio->hasAttribute( 'controls' ) ) {
				$audio->setAttribute( 'controls', '' );
				$fix_details['added_controls'] = true;
				$fixed_this = true;
			}

			// Prevent autoplay for accessibility
			if ( $audio->hasAttribute( 'autoplay' ) ) {
				$audio->removeAttribute( 'autoplay' );
				$fix_details['removed_autoplay'] = true;
				$fixed_this = true;
			}

			// Add aria-label if no accessible name
			$has_accessible_name = $audio->hasAttribute( 'aria-label' ) || 
								   $audio->hasAttribute( 'aria-labelledby' ) ||
								   $audio->hasAttribute( 'title' );

			if ( ! $has_accessible_name ) {
				$src = $audio->getAttribute( 'src' );
				if ( ! $src ) {
					// Check for source element
					$sources = $this->query( './/source[@src]', $audio );
					if ( count( $sources ) > 0 ) {
						$src = $sources[0]->getAttribute( 'src' );
					}
				}

				// Generate label from filename
				if ( $src ) {
					$filename = basename( parse_url( $src, PHP_URL_PATH ) ?: $src );
					$label = pathinfo( $filename, PATHINFO_FILENAME );
					$label = str_replace( [ '-', '_' ], ' ', $label );
					$label = ucwords( $label );
					$audio->setAttribute( 'aria-label', sprintf( __( 'Audio: %s', 'shahi-legalflowsuite' ), $label ) );
				} else {
					$audio->setAttribute( 'aria-label', __( 'Audio player', 'shahi-legalflowsuite' ) );
				}
				$fix_details['added_label'] = true;
				$fixed_this = true;
			}

			// Add preload="metadata" for performance and accessibility
			if ( ! $audio->hasAttribute( 'preload' ) ) {
				$audio->setAttribute( 'preload', 'metadata' );
				$fix_details['added_preload'] = true;
				$fixed_this = true;
			}

			if ( $fixed_this ) {
				$fixes_applied++;
				$details[] = $fix_details;
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All audio elements are accessible', $content );
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
