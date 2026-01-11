<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmptyHeadingCheck extends AbstractCheck {

	public function get_id() {
		return 'empty-heading';
	}

	public function get_description() {
		return 'Headings should not be empty.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues   = array();
		$dom      = $this->get_dom( $content );
		$xpath    = new \DOMXPath( $dom );
		$headings = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );

		foreach ( $headings as $heading ) {
			$text = trim( $heading->textContent );

			// Check if it has images with alt text
			$hasAccessibleContent = false;
			if ( $text !== '' ) {
				$hasAccessibleContent = true;
			} else {
				$images = $heading->getElementsByTagName( 'img' );
				foreach ( $images as $img ) {
					if ( $img->hasAttribute( 'alt' ) && trim( $img->getAttribute( 'alt' ) ) !== '' ) {
						$hasAccessibleContent = true;
						break;
					}
				}
			}

			if ( ! $hasAccessibleContent ) {
				$issues[] = array(
					'message' => "Empty heading tag <{$heading->tagName}> found.",
					'element' => $dom->saveHTML( $heading ),
					'context' => '',
				);
			}
		}

		return $issues;
	}
}
