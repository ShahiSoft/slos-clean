<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LogoImageCheck extends AbstractCheck {

	public function get_id() {
		return 'logo-image';
	}

	public function get_description() {
		return 'Logo images must have descriptive alt text.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$images = $this->get_elements( $content, 'img' );

		foreach ( $images as $img ) {
			$src   = strtolower( $img->getAttribute( 'src' ) );
			$class = strtolower( $img->getAttribute( 'class' ) );
			$id    = strtolower( $img->getAttribute( 'id' ) );
			$alt   = trim( $img->getAttribute( 'alt' ) );

			// Heuristic: Check if it's a logo
			if ( strpos( $src, 'logo' ) !== false || strpos( $class, 'logo' ) !== false || strpos( $id, 'logo' ) !== false ) {
				if ( empty( $alt ) ) {
					$issues[] = array(
						'element' => 'img',
						'context' => $this->get_element_html( $img ),
						'message' => 'Image identified as a logo is missing alt text.',
					);
				}
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

