<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DecorativeImageCheck extends AbstractCheck {

	public function get_id() {
		return 'decorative-image';
	}

	public function get_description() {
		return 'Decorative images should have empty alt text.';
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
			$src = $img->getAttribute( 'src' );
			$alt = $img->getAttribute( 'alt' );

			// Heuristic: filename contains "decorative", "spacer", "line", "divider"
			if ( preg_match( '/(decorative|spacer|line|divider|separator|bg|background)/i', $src ) ) {
				if ( ! empty( $alt ) ) {
					$issues[] = array(
						'element' => 'img',
						'context' => $this->get_element_html( $img ),
						'message' => 'Image appears to be decorative based on filename but has non-empty alt text.',
					);
				}
			}

			// Check for role="presentation" or role="none"
			$role = $img->getAttribute( 'role' );
			if ( ( $role === 'presentation' || $role === 'none' ) && ! empty( $alt ) ) {
				$issues[] = array(
					'element' => 'img',
					'context' => $this->get_element_html( $img ),
					'message' => 'Image has presentation role but has non-empty alt text.',
				);
			}
		}

		return $issues;
	}

	protected function get_element_html( $node, $max_length = 200 ) {
		$html = $node->ownerDocument->saveHTML( $node );
		return strlen( $html ) > $max_length ? substr( $html, 0, $max_length ) . '...' : $html;
	}
}

