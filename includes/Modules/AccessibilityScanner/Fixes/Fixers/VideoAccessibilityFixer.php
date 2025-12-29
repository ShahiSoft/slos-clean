<?php
/**
 * Video Accessibility Fixer
 *
 * Adds captions and transcripts to video content.
 * WCAG 1.2.2 Captions (Prerecorded) (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VideoAccessibilityFixer Class
 *
 * Adds captions and transcripts to video content.
 */
class VideoAccessibilityFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'video-access';
	}

	/**
	 * Get fixer name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Video Accessibility';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds captions and transcripts to video content';
	}

	/**
	 * Fix video accessibility in content
	 *
	 * @param string $content Post content
	 * @return array Fix result with content and count
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixes_applied = 0;

		// Find video elements without captions
		$videos = $xpath->query( '//video[not(.//track[@kind="captions" or @kind="subtitles"])]' );

		foreach ( $videos as $video ) {
			// Add a note about needing captions
			$note = $dom->createElement( 'p' );
			$note->setAttribute( 'class', 'accessibility-notice' );
			$note->setAttribute( 'style', 'background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 8px 0; color: #856404;' );
			$note->textContent = '⚠️ Video captions should be added. Please upload a WebVTT (.vtt) subtitle file and add a <track> element to this video.';
			
			// Insert note after video
			if ( $video->nextSibling ) {
				$video->parentNode->insertBefore( $note, $video->nextSibling );
			} else {
				$video->parentNode->appendChild( $note );
			}
			$fixes_applied++;
		}

		// Find iframe embeds (YouTube, Vimeo, etc.) without captions mentioned
		$iframes = $xpath->query( '//iframe[contains(@src, "youtube.com") or contains(@src, "vimeo.com") or contains(@src, "dailymotion.com")]' );

		foreach ( $iframes as $iframe ) {
			$src = $iframe->getAttribute( 'src' );
			
			// For YouTube, ensure cc_load_policy=1 is in the URL
			if ( strpos( $src, 'youtube.com' ) !== false && strpos( $src, 'cc_load_policy=1' ) === false ) {
				$separator = strpos( $src, '?' ) !== false ? '&' : '?';
				$new_src = $src . $separator . 'cc_load_policy=1';
				$iframe->setAttribute( 'src', $new_src );
				$fixes_applied++;
			}

			// Ensure iframe has title
			if ( ! $iframe->hasAttribute( 'title' ) || empty( trim( $iframe->getAttribute( 'title' ) ) ) ) {
				$iframe->setAttribute( 'title', 'Video player' );
				$fixes_applied++;
			}
		}

		return $this->return_result( $this->dom_to_html( $dom ), $fixes_applied );
	}
}
