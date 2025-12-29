<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TableCaptionCheck extends AbstractCheck {

	public function get_id() {
		return 'table-caption';
	}

	public function get_description() {
		return 'Data tables should have a caption or summary.';
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
			// Skip layout tables
			$role = $table->getAttribute( 'role' );
			if ( $role === 'presentation' || $role === 'none' ) {
				continue;
			}

			// Check for caption element
			$captions   = $table->getElementsByTagName( 'caption' );
			$hasCaption = $captions->length > 0 && trim( $captions->item( 0 )->textContent ) !== '';

			// Check for aria-label or aria-labelledby
			$hasAriaLabel = $table->hasAttribute( 'aria-label' ) || $table->hasAttribute( 'aria-labelledby' );

			// Check for summary attribute (obsolete but still used)
			$hasSummary = $table->hasAttribute( 'summary' );

			if ( ! $hasCaption && ! $hasAriaLabel && ! $hasSummary ) {
				$issues[] = array(
					'element' => 'table',
					'context' => $this->get_element_html( $table ),
					'message' => 'Data table is missing a caption, aria-label, or summary to identify its purpose.',
				);
			}
		}

		return $issues;
	}
}

