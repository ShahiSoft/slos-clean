<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ComplexTableCheck extends AbstractCheck {

	public function get_id() {
		return 'complex-table';
	}

	public function get_description() {
		return 'Complex tables must use headers/id attributes for cell association.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$tables = $xpath->query( '//table' );

		foreach ( $tables as $table ) {
			// Skip layout tables
			$role = $table->getAttribute( 'role' );
			if ( $role === 'presentation' || $role === 'none' ) {
				continue;
			}

			// Check for colspan or rowspan > 1
			$complexCells = $xpath->query( './/td[@colspan > 1] | .//td[@rowspan > 1] | .//th[@colspan > 1] | .//th[@rowspan > 1]', $table );

			if ( $complexCells->length > 0 ) {
				// If complex, check if headers attribute is used on data cells
				$dataCellsWithHeaders = $xpath->query( './/td[@headers]', $table );

				// This is a heuristic. If there are merged cells, we expect some explicit association if it's very complex.
				// But simple merged headers might be fine with scope.
				// Let's check if scope is missing on headers in a complex table.

				$headersWithoutScope = $xpath->query( './/th[not(@scope) and not(@id)]', $table );

				if ( $headersWithoutScope->length > 0 ) {
					$issues[] = array(
						'element' => 'table',
						'context' => $this->get_element_html( $table ),
						'message' => 'Complex table (using colspan/rowspan) detected. Ensure <th> elements have "scope" attribute or use "headers/id" for data cells.',
					);
				}
			}
		}

		return $issues;
	}
}

