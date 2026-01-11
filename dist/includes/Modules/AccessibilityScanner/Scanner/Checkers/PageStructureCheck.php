<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PageStructureCheck extends AbstractCheck {

	public function get_id() {
		return 'page-structure';
	}

	public function get_description() {
		return 'Full page documents must have language attribute and title.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '3.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check if <html> tag exists
		$html = $xpath->query( '//html' );

		if ( $html->length > 0 ) {
			$element = $html->item( 0 );
			if ( ! $element->hasAttribute( 'lang' ) ) {
				$issues[] = array(
					'element' => 'html',
					'context' => '<html>',
					'message' => 'The <html> element is missing the "lang" attribute.',
				);
			}

			// Check for <title> in <head>
			$title = $xpath->query( '//head/title' );
			if ( $title->length === 0 || trim( $title->item( 0 )->textContent ) === '' ) {
				$issues[] = array(
					'element' => 'title',
					'context' => '<head>',
					'message' => 'The document is missing a non-empty <title> element.',
				);
			}
		}

		return $issues;
	}
}
