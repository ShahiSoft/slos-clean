<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AudioAccessibilityCheck extends AbstractCheck {

	public function get_id() {
		return 'audio-accessibility';
	}

	public function get_description() {
		return 'Audio must have controls and no autoplay.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '1.4.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$audios = $xpath->query( '//audio' );

		foreach ( $audios as $audio ) {
			// Check for controls
			if ( ! $audio->hasAttribute( 'controls' ) ) {
				$issues[] = array(
					'element' => 'audio',
					'context' => $this->get_element_html( $audio ),
					'message' => '<audio> element is missing the "controls" attribute.',
				);
			}

			// Check for autoplay
			if ( $audio->hasAttribute( 'autoplay' ) ) {
				$issues[] = array(
					'element' => 'audio',
					'context' => $this->get_element_html( $audio ),
					'message' => '<audio> element has "autoplay" enabled.',
				);
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

