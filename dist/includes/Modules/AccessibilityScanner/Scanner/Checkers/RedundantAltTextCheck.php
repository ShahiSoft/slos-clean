<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RedundantAltTextCheck extends AbstractCheck {

	public function get_id() {
		return 'redundant-alt-text';
	}

	public function get_description() {
		return 'Alt text should not be the same as the filename.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$images = $dom->getElementsByTagName( 'img' );

		foreach ( $images as $img ) {
			if ( $img->hasAttribute( 'alt' ) && $img->hasAttribute( 'src' ) ) {
				$alt = trim( $img->getAttribute( 'alt' ) );
				$src = trim( $img->getAttribute( 'src' ) );

				if ( $alt === '' ) {
					continue;
				}

				// Get filename from src..
				$filename = basename( $src );

				// Check if alt equals filename (with or without extension)..
				if ( $alt === $filename || $alt === pathinfo( $filename, PATHINFO_FILENAME ) ) {
					$issues[] = array(
						'message' => 'Alt text is the same as the filename. It should describe the image content.',
						'element' => $dom->saveHTML( $img ),
						'context' => $alt,
					);
				}
			}
		}

		return $issues;
	}
}
