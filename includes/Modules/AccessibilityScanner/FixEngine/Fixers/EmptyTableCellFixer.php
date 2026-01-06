<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class EmptyTableCellFixer extends AbstractFixer {

	public function get_id(): string {
		return 'empty-table-cell';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom         = $this->parse_html( $content );
		$tables      = $dom->getElementsByTagName( 'table' );
		$fixed_count = 0;

		foreach ( $tables as $table ) {
			$rows = $table->getElementsByTagName( 'tr' );
			foreach ( $rows as $row ) {
				$cells = $row->getElementsByTagName( 'td' );
				foreach ( $cells as $cell ) {
					$text = trim( $cell->textContent );
					if ( $text === '' && ! $cell->hasAttribute( 'aria-label' ) ) {
						$cell->setAttribute( 'aria-label', 'Empty cell' );
						++$fixed_count;
					}
				}
			}
		}

		$fixed_content = $this->get_html();

		if ( $fixed_count <= 0 || $fixed_content === '' || $fixed_content === $content ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			[]
		);
	}
}
