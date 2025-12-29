<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MediaAlternativeCheck extends AbstractCheck {

	public function get_id() {
		return 'media-alternative';
	}

	public function get_description() {
		return 'Media elements should have fallback content or transcripts.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.2.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$medias = $xpath->query( '//video | //audio' );

		foreach ( $medias as $media ) {
			// Check for fallback content inside the tag
			if ( trim( $media->textContent ) === '' ) {
				$issues[] = array(
					'element' => $media->tagName,
					'context' => $this->get_element_html( $media ),
					'message' => "<{$media->tagName}> element should contain fallback text for browsers that do not support the element.",
				);
			}

			// Heuristic: Check for "transcript" link nearby?
			// Hard to do reliably in DOM without context, but we can check if there's a link with "transcript" text in the whole content?
			// No, that's too broad.
			// We'll stick to the fallback content check for now.
		}

		return $issues;
	}
}

