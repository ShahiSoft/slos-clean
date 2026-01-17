<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SkippedHeadingLevelCheck extends AbstractCheck {

	public function get_id() {
		return 'skipped-heading-level';
	}

	public function get_description() {
		return 'Heading levels should not be skipped (e.g. H2 to H4).';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );
		// Select all heading elements in document order..
		$headings = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );

		$previousLevel = 0;

		foreach ( $headings as $heading ) {
			$currentLevel = intval( substr( $heading->tagName, 1 ) );

			// If current > previous + 1, it's a skip...
			// We ignore the first heading (previousLevel == 0) for this check..
			// as MissingH1Check handles the start...
			if ( $previousLevel > 0 && $currentLevel > $previousLevel + 1 ) {
				$issues[] = array(
					'message' => 'Skipped heading level: ' . strtoupper( $heading->tagName ) . " follows H$previousLevel.",
					'element' => $dom->saveHTML( $heading ),
					'context' => substr( strip_tags( $heading->textContent ), 0, 50 ),
				);
			}

			$previousLevel = $currentLevel;
		}

		return $issues;
	}
}
