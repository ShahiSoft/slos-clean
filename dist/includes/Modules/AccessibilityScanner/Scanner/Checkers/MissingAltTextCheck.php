<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MissingAltTextCheck extends AbstractCheck {

	public function get_id() {
		return 'missing-alt-text';
	}

	public function get_description() {
		return 'Images must have an alt attribute.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$images = $dom->getElementsByTagName( 'img' );

		foreach ( $images as $img ) {
			if ( ! $img->hasAttribute( 'alt' ) ) {
				$src      = $img->getAttribute( 'src' );
				$issues[] = array(
					'message' => 'Image missing alt attribute',
					'element' => '<img src="' . $src . '">',
					'context' => $src,
				);
			}
		}

		return $issues;
	}
}

