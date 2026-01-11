<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MultipleH1Check extends AbstractCheck {

	public function get_id() {
		return 'multiple-h1';
	}

	public function get_description() {
		return 'A page should have exactly one H1 heading.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$h1s    = $this->get_elements( $content, 'h1' );

		if ( $h1s->length > 1 ) {
			$issues[] = array(
				'element' => 'h1',
				'context' => 'Found ' . $h1s->length . ' H1 tags',
				'message' => 'Page contains multiple H1 headings. Best practice is to have only one main heading.',
			);
		}

		return $issues;
	}
}
