<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmptyTableCellCheck extends AbstractCheck {

	public function get_id() {
		return 'empty-table-cell';
	}

	public function get_description() {
		return 'Avoid empty table headers.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check for empty TH
		$emptyThs = $xpath->query( '//th[not(node()) or normalize-space(.) = ""]' );

		foreach ( $emptyThs as $th ) {
			// Sometimes empty top-left cell is intentional, but generally discouraged
			$issues[] = array(
				'element' => 'th',
				'context' => $this->get_element_html( $th ),
				'message' => 'Table header cell is empty. Provide a description for the column/row.',
			);
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

