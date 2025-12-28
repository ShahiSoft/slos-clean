<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AltTextQualityCheck extends AbstractCheck {

	public function get_id() {
		return 'alt-text-quality';
	}

	public function get_description() {
		return 'Alt text should be concise and not contain redundant phrases like "image of".';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$images = $this->get_elements( $content, 'img' );

		foreach ( $images as $img ) {
			if ( ! $img->hasAttribute( 'alt' ) ) {
				continue;
			}

			$alt = trim( $img->getAttribute( 'alt' ) );
			if ( empty( $alt ) ) {
				continue;
			}

			// Check for "image of", "picture of"
			if ( preg_match( '/^(image|picture|photo|graphic) of/i', $alt ) ) {
				$issues[] = array(
					'element' => 'img',
					'context' => $this->get_element_html( $img ),
					'message' => 'Alt text contains redundant phrase "image of" or similar.',
				);
			}

			// Check length (> 125 chars)
			if ( strlen( $alt ) > 125 ) {
				$issues[] = array(
					'element' => 'img',
					'context' => $this->get_element_html( $img ),
					'message' => 'Alt text is too long (> 125 characters). Consider using a long description.',
				);
			}

			// Check for placeholder text
			$placeholders = array( 'img', 'image', 'test', 'pic', 'picture', 'placeholder' );
			if ( in_array( strtolower( $alt ), $placeholders ) ) {
				$issues[] = array(
					'element' => 'img',
					'context' => $this->get_element_html( $img ),
					'message' => 'Alt text appears to be a placeholder.',
				);
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

