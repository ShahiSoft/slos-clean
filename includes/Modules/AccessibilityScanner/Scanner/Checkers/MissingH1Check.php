<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MissingH1Check extends AbstractCheck {

	public function get_id() {
		return 'missing-h1';
	}

	public function get_description() {
		return 'Page should have exactly one H1 heading.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$h1s    = $dom->getElementsByTagName( 'h1' );

		if ( $h1s->length === 0 ) {
			$issues[] = array(
				'message' => 'No H1 heading found on the page.',
				'element' => '',
				'context' => '',
			);
		} elseif ( $h1s->length > 1 ) {
			$issues[] = array(
				'message' => 'Multiple H1 headings found. There should be only one.',
				'element' => '',
				'context' => 'Found ' . $h1s->length . ' H1 tags.',
			);
		}

		return $issues;
	}
}
