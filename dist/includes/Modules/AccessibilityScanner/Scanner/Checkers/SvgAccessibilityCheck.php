<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SvgAccessibilityCheck extends AbstractCheck {

	public function get_id() {
		return 'svg-accessibility';
	}

	public function get_description() {
		return 'SVGs must have a title, desc, or aria-label.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$svgs   = $this->get_elements( $content, 'svg' );

		foreach ( $svgs as $svg ) {
			// Skip if role="presentation" or role="none" or aria-hidden="true"
			$role        = $svg->getAttribute( 'role' );
			$aria_hidden = $svg->getAttribute( 'aria-hidden' );

			if ( $role === 'presentation' || $role === 'none' || $aria_hidden === 'true' ) {
				continue;
			}

			$has_title           = $svg->getElementsByTagName( 'title' )->length > 0;
			$has_desc            = $svg->getElementsByTagName( 'desc' )->length > 0;
			$has_aria_label      = $svg->hasAttribute( 'aria-label' );
			$has_aria_labelledby = $svg->hasAttribute( 'aria-labelledby' );

			if ( ! $has_title && ! $has_desc && ! $has_aria_label && ! $has_aria_labelledby ) {
				$issues[] = array(
					'element' => 'svg',
					'context' => 'SVG Element',
					'message' => 'SVG element is missing accessible name (title, desc, or aria-label).',
				);
			}
		}

		return $issues;
	}
}

