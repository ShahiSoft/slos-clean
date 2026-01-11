<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HeadingLengthCheck extends AbstractCheck {

	public function get_id() {
		return 'heading-length';
	}

	public function get_description() {
		return 'Headings should be concise.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.6';
	}

	public function check( $content ) {
		$issues   = array();
		$dom      = $this->get_dom( $content );
		$xpath    = new \DOMXPath( $dom );
		$headings = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );

		foreach ( $headings as $heading ) {
			$text = trim( $heading->textContent );
			if ( strlen( $text ) > 150 ) {
				$issues[] = array(
					'element' => $heading->tagName,
					'context' => substr( $text, 0, 50 ) . '...',
					'message' => 'Heading text is very long (> 150 characters). Headings should be concise labels.',
				);
			}
		}

		return $issues;
	}
}
