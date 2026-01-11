<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TableHeaderCheck extends AbstractCheck {

	public function get_id() {
		return 'table-header';
	}

	public function get_description() {
		return 'Data tables must have header cells (th).';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$tables = $this->get_elements( $content, 'table' );

		foreach ( $tables as $table ) {
			// Check if table has role="presentation" or role="none", if so skip
			$role = $table->getAttribute( 'role' );
			if ( $role === 'presentation' || $role === 'none' ) {
				continue;
			}

			$ths = $table->getElementsByTagName( 'th' );

			if ( $ths->length === 0 ) {
				$issues[] = array(
					'element' => 'table',
					'context' => 'Table with ' . $table->getElementsByTagName( 'tr' )->length . ' rows',
					'message' => 'Data table is missing header cells (th).',
				);
			}
		}

		return $issues;
	}
}
