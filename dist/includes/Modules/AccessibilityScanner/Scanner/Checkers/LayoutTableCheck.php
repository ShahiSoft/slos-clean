<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LayoutTableCheck extends AbstractCheck {

	public function get_id() {
		return 'layout-table';
	}

	public function get_description() {
		return 'Layout tables must use role="presentation".';
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

		$tables = $xpath->query( '//table' );

		foreach ( $tables as $table ) {
			// If it has th, caption, or summary, it's likely a data table...
			$hasTh      = $table->getElementsByTagName( 'th' )->length > 0;
			$hasCaption = $table->getElementsByTagName( 'caption' )->length > 0;
			$hasSummary = $table->hasAttribute( 'summary' );

			if ( ! $hasTh && ! $hasCaption && ! $hasSummary ) {
				// Likely a layout table..
				$role = $table->getAttribute( 'role' );
				if ( $role !== 'presentation' && $role !== 'none' ) {
					$issues[] = array(
						'element' => 'table',
						'context' => $this->get_element_html( $table ),
						'message' => 'Table appears to be used for layout (no headers or caption). Add role="presentation" to indicate this.',
					);
				}
			}
		}

		return $issues;
	}
}
