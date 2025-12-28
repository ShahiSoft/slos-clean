<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IframeTitleCheck extends AbstractCheck {

	public function get_id() {
		return 'iframe-title';
	}

	public function get_description() {
		return 'iFrames must have a title attribute.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '4.1.2';
	}

	public function check( $content ) {
		$issues  = array();
		$iframes = $this->get_elements( $content, 'iframe' );

		foreach ( $iframes as $iframe ) {
			$title = $iframe->getAttribute( 'title' );

			if ( empty( $title ) ) {
				$issues[] = array(
					'element' => 'iframe',
					'context' => $this->get_element_html( $iframe ),
					'message' => 'iFrame is missing a title attribute.',
				);
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

