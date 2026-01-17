<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmptyAltTextCheck extends AbstractCheck {

	public function get_id() {
		return 'empty-alt-text';
	}

	public function get_description() {
		return 'Non-decorative images should not have empty alt text.';
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
			if ( $img->hasAttribute( 'alt' ) ) {
				$alt = $img->getAttribute( 'alt' );
				if ( trim( $alt ) === '' ) {
					// Check if it's marked as decorative (role="presentation" or aria-hidden="true")..
					$role       = $img->getAttribute( 'role' );
					$ariaHidden = $img->getAttribute( 'aria-hidden' );

					if ( $role !== 'presentation' && $ariaHidden !== 'true' ) {
						$src      = $img->getAttribute( 'src' );
						$issues[] = array(
							'message' => 'Image has empty alt text but is not marked as decorative',
							'element' => '<img src="' . $src . '">',
							'context' => $src,
						);
					}
				}
			}
		}

		return $issues;
	}
}
