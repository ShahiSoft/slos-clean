<?php
/**
 * Media Alternative Fixer
 *
 * Adds alternative text and descriptions to media elements.
 * WCAG 1.1.1 Non-text Content (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaAlternativeFixer Class
 *
 * Adds alternative text and descriptions to media.
 */
class MediaAlternativeFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'media-alt';
	}

	/**
	 * Get fixer name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Media Alternatives';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds alternative text and descriptions to media elements';
	}

	/**
	 * Fix media alternatives in content
	 *
	 * @param string $content Post content
	 * @return array Fix result with content and count
	 */
	public function fix( $content ) {
		$dom           = $this->get_dom( $content );
		$xpath         = new \DOMXPath( $dom );
		$fixes_applied = 0;

		// Fix audio elements without transcripts
		$audio_elements = $xpath->query( '//audio' );
		foreach ( $audio_elements as $audio ) {
			// Check if there's already a transcript nearby
			$has_transcript = false;
			$next           = $audio->nextSibling;
			while ( $next && $next->nodeType === XML_ELEMENT_NODE && in_array( $next->nodeName, array( 'p', 'div' ), true ) ) {
				if ( stripos( $next->textContent, 'transcript' ) !== false ) {
					$has_transcript = true;
					break;
				}
				$next = $next->nextSibling;
			}

			if ( ! $has_transcript ) {
				$note = $dom->createElement( 'p' );
				$note->setAttribute( 'class', 'accessibility-notice' );
				$note->setAttribute( 'style', 'background: #e8f5e9; border-left: 4px solid #4caf50; padding: 12px; margin: 8px 0; color: #2e7d32;' );
				$note->textContent = 'ℹ️ Audio transcript: Please provide a text transcript of this audio content for accessibility.';

				if ( $audio->nextSibling ) {
					$audio->parentNode->insertBefore( $note, $audio->nextSibling );
				} else {
					$audio->parentNode->appendChild( $note );
				}
				++$fixes_applied;
			}
		}

		// Fix object/embed elements without alternative text
		$objects = $xpath->query( '//object[not(.//param[@name="alt" or @name="description"])] | //embed[not(@alt)]' );
		foreach ( $objects as $object ) {
			$note = $dom->createElement( 'p' );
			$note->setAttribute( 'class', 'accessibility-notice' );
			$note->setAttribute( 'style', 'background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 8px 0; color: #856404;' );
			$note->textContent = '⚠️ Embedded media should include alternative text or a detailed description for accessibility.';

			if ( $object->nextSibling ) {
				$object->parentNode->insertBefore( $note, $object->nextSibling );
			} else {
				$object->parentNode->appendChild( $note );
			}
			++$fixes_applied;
		}

		// Fix canvas elements without alternative content
		$canvases = $xpath->query( '//canvas[not(normalize-space(text()))]' );
		foreach ( $canvases as $canvas ) {
			$fallback              = $dom->createElement( 'p' );
			$fallback->textContent = 'Your browser does not support the canvas element. This canvas displays: [Description needed]';
			$canvas->appendChild( $fallback );
			++$fixes_applied;
		}

		return $this->return_result( $this->dom_to_html( $dom ), $fixes_applied );
	}
}
