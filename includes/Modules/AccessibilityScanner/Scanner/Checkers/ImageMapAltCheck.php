<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImageMapAltCheck extends AbstractCheck {

	public function get_id() {
		return 'image-map-alt';
	}

	public function get_description() {
		return 'Image map areas must have alternate text.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function check( $content ) {
		$issues = array();
		$areas  = $this->get_elements( $content, 'area' );

		foreach ( $areas as $area ) {
			$alt  = $area->getAttribute( 'alt' );
			$href = $area->getAttribute( 'href' );

			if ( $href && empty( $alt ) ) {
				$issues[] = array(
					'element' => 'area',
					'context' => $this->get_element_html( $area ),
					'message' => 'Image map area with href is missing alt text.',
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

