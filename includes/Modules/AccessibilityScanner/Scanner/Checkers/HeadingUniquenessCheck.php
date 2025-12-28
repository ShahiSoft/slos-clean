<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HeadingUniquenessCheck extends AbstractCheck {

	public function get_id() {
		return 'heading-uniqueness';
	}

	public function get_description() {
		return 'Headings should be unique to aid navigation.';
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

		$seen = array();

		foreach ( $headings as $heading ) {
			$text = trim( $heading->textContent );
			if ( empty( $text ) ) {
				continue;
			}

			if ( isset( $seen[ $text ] ) ) {
				$issues[] = array(
					'element' => $heading->tagName,
					'context' => $text,
					'message' => 'Duplicate heading text found. Headings should be unique.',
				);
			} else {
				$seen[ $text ] = true;
			}
		}

		return $issues;
	}
}

