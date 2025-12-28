<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ComplexImageCheck extends AbstractCheck {

	public function get_id() {
		return 'complex-image';
	}

	public function get_description() {
		return 'Complex images (charts, graphs) should have long descriptions.';
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
			$alt = strtolower( $img->getAttribute( 'alt' ) );

			// Heuristic: alt text indicates complex data
			if ( preg_match( '/(chart|graph|diagram|map|infographic|statistics)/i', $alt ) ) {
				$has_longdesc         = $img->hasAttribute( 'longdesc' );
				$has_aria_describedby = $img->hasAttribute( 'aria-describedby' );
				$has_aria_details     = $img->hasAttribute( 'aria-details' );

				if ( ! $has_longdesc && ! $has_aria_describedby && ! $has_aria_details ) {
					$issues[] = array(
						'element' => 'img',
						'context' => $this->get_element_html( $img ),
						'message' => 'Complex image (chart/graph) may require a long description or aria-describedby.',
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

