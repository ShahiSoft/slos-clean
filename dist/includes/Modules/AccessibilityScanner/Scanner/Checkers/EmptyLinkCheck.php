<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmptyLinkCheck extends AbstractCheck {

	public function get_id() {
		return 'empty-link';
	}

	public function get_description() {
		return 'Links must have discernible text.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '2.4.4';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			// Check for aria-label
			if ( $link->hasAttribute( 'aria-label' ) && trim( $link->getAttribute( 'aria-label' ) ) !== '' ) {
				continue;
			}

			// Check for aria-labelledby
			if ( $link->hasAttribute( 'aria-labelledby' ) && trim( $link->getAttribute( 'aria-labelledby' ) ) !== '' ) {
				continue;
			}

			$text = trim( $link->textContent );

			if ( $text === '' ) {
				// Check for images with alt text
				$hasAccessibleImage = false;
				$images             = $link->getElementsByTagName( 'img' );
				foreach ( $images as $img ) {
					if ( $img->hasAttribute( 'alt' ) && trim( $img->getAttribute( 'alt' ) ) !== '' ) {
						$hasAccessibleImage = true;
						break;
					}
				}

				if ( ! $hasAccessibleImage ) {
					$href     = $link->getAttribute( 'href' );
					$issues[] = array(
						'message' => 'Link contains no text and no image with alt text.',
						'element' => $dom->saveHTML( $link ),
						'context' => $href ? $href : 'No href',
					);
				}
			}
		}

		return $issues;
	}
}

