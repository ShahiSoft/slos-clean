<?php
/**
 * Video Accessibility Fixer
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
 * Class VideoAccessibilityFixer
 * 
 * Adds accessibility attributes to video elements.
 */
final class VideoAccessibilityFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-video-caption';
	}

	public function get_name(): string {
		return __( 'Video Accessibility', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Ensures video elements have proper accessibility attributes, captions tracks, and autoplay handling.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.2.1', '1.2.2', '1.2.3', '1.4.2' ];
	}

	public function get_category(): string {
		return 'media';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<video' ) !== false || 
			   strpos( $content, '<iframe' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details = [];

		// Fix native video elements
		$videos = $this->query( '//video' );
		foreach ( $videos as $video ) {
			$fixed = $this->fix_video_element( $video );
			if ( $fixed ) {
				$fixes_applied++;
				$details[] = $fixed;
			}
		}

		// Fix video iframes (YouTube, Vimeo, etc.)
		$iframes = $this->query( '//iframe[contains(@src, "youtube") or contains(@src, "vimeo") or contains(@src, "dailymotion") or contains(@src, "video")]' );
		foreach ( $iframes as $iframe ) {
			$fixed = $this->fix_video_iframe( $iframe );
			if ( $fixed ) {
				$fixes_applied++;
				$details[] = $fixed;
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No video accessibility issues found', $content );
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
	 * Fix native video element
	 *
	 * @param \DOMElement $video
	 * @return array|null
	 */
	private function fix_video_element( \DOMElement $video ): ?array {
		$modified = false;
		$fix_detail = [ 'type' => 'video' ];

		// Handle autoplay - mute if autoplaying
		if ( $video->hasAttribute( 'autoplay' ) && ! $video->hasAttribute( 'muted' ) ) {
			$video->setAttribute( 'muted', '' );
			$modified = true;
			$fix_detail['muted_autoplay'] = true;
		}

		// Add controls if missing
		if ( ! $video->hasAttribute( 'controls' ) ) {
			$video->setAttribute( 'controls', '' );
			$modified = true;
			$fix_detail['controls_added'] = true;
		}

		// Add aria-label if missing
		if ( ! $video->hasAttribute( 'aria-label' ) && ! $video->hasAttribute( 'aria-labelledby' ) ) {
			$label = $this->derive_video_label( $video );
			$video->setAttribute( 'aria-label', $label );
			$modified = true;
			$fix_detail['aria_label'] = $label;
		}

		// Check for captions track
		$tracks = $video->getElementsByTagName( 'track' );
		$has_captions = false;
		
		foreach ( $tracks as $track ) {
			$kind = $track->getAttribute( 'kind' );
			if ( in_array( $kind, [ 'captions', 'subtitles' ] ) ) {
				$has_captions = true;
				break;
			}
		}

		if ( ! $has_captions ) {
			// Add placeholder comment for captions
			$comment = $this->doc->createComment( ' TODO: Add captions track for accessibility compliance (WCAG 1.2.2) ' );
			$video->appendChild( $comment );
			$fix_detail['captions_reminder'] = true;
		}

		// Add preload="metadata" if no preload set
		if ( ! $video->hasAttribute( 'preload' ) ) {
			$video->setAttribute( 'preload', 'metadata' );
			$modified = true;
		}

		return $modified ? $fix_detail : null;
	}

	/**
	 * Fix video iframe (YouTube, Vimeo, etc.)
	 *
	 * @param \DOMElement $iframe
	 * @return array|null
	 */
	private function fix_video_iframe( \DOMElement $iframe ): ?array {
		$modified = false;
		$fix_detail = [ 'type' => 'iframe' ];
		$src = $iframe->getAttribute( 'src' );

		// Add title if missing
		if ( empty( $iframe->getAttribute( 'title' ) ) ) {
			$title = $this->derive_iframe_title( $src );
			$iframe->setAttribute( 'title', $title );
			$modified = true;
			$fix_detail['title_added'] = $title;
		}

		// Add allow for keyboard focus
		$allow = $iframe->getAttribute( 'allow' ) ?: '';
		if ( strpos( $allow, 'fullscreen' ) === false ) {
			$allow = trim( $allow . '; fullscreen' );
			$iframe->setAttribute( 'allow', $allow );
			$modified = true;
		}

		// For YouTube, ensure captions are enabled by default
		if ( strpos( $src, 'youtube' ) !== false ) {
			$parsed = parse_url( $src );
			parse_str( $parsed['query'] ?? '', $params );
			
			if ( ! isset( $params['cc_load_policy'] ) ) {
				$params['cc_load_policy'] = '1';
				$new_query = http_build_query( $params );
				$new_src = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'] . '?' . $new_query;
				$iframe->setAttribute( 'src', $new_src );
				$modified = true;
				$fix_detail['youtube_captions_enabled'] = true;
			}
		}

		return $modified ? $fix_detail : null;
	}

	/**
	 * Derive label for video element
	 *
	 * @param \DOMElement $video
	 * @return string
	 */
	private function derive_video_label( \DOMElement $video ): string {
		// Check for poster image name
		$poster = $video->getAttribute( 'poster' );
		if ( ! empty( $poster ) ) {
			$filename = basename( parse_url( $poster, PHP_URL_PATH ) );
			$name = preg_replace( '/\.[^.]+$/', '', $filename );
			$name = str_replace( [ '-', '_' ], ' ', $name );
			if ( strlen( $name ) > 3 ) {
				return ucwords( $name ) . ' video';
			}
		}

		// Check for source file name
		$sources = $video->getElementsByTagName( 'source' );
		if ( $sources->length > 0 ) {
			$src = $sources->item( 0 )->getAttribute( 'src' );
			$filename = basename( parse_url( $src, PHP_URL_PATH ) );
			$name = preg_replace( '/\.[^.]+$/', '', $filename );
			$name = str_replace( [ '-', '_' ], ' ', $name );
			if ( strlen( $name ) > 3 ) {
				return ucwords( $name ) . ' video';
			}
		}

		return __( 'Video content', 'shahi-legalflowsuite' );
	}

	/**
	 * Derive title for video iframe
	 *
	 * @param string $src
	 * @return string
	 */
	private function derive_iframe_title( string $src ): string {
		// YouTube
		if ( strpos( $src, 'youtube' ) !== false || strpos( $src, 'youtu.be' ) !== false ) {
			return __( 'YouTube video player', 'shahi-legalflowsuite' );
		}

		// Vimeo
		if ( strpos( $src, 'vimeo' ) !== false ) {
			return __( 'Vimeo video player', 'shahi-legalflowsuite' );
		}

		// Dailymotion
		if ( strpos( $src, 'dailymotion' ) !== false ) {
			return __( 'Dailymotion video player', 'shahi-legalflowsuite' );
		}

		return __( 'Video player', 'shahi-legalflowsuite' );
	}
}
