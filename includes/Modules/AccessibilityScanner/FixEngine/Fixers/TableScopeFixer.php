<?php
/**
 * Table Scope Fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TableScopeFixer
 * 
 * Adds scope attributes to table headers.
 */
final class TableScopeFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-table-scope';
	}

	public function get_name(): string {
		return __( 'Table Header Scope', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds scope attributes to th elements to clarify row/column headers.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.3.1' ];
	}

	public function get_category(): string {
		return 'tables';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<th' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find th elements without scope
		$ths = $this->query( '//th[not(@scope)]' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $ths as $th ) {
			$scope = $this->determine_scope( $th );
			
			if ( $scope ) {
				$th->setAttribute( 'scope', $scope );
				$fixes_applied++;
				$details[] = [
					'text'  => substr( $th->textContent, 0, 30 ),
					'scope' => $scope,
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All table headers have scope attributes', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Determine the appropriate scope for a th element
	 */
	private function determine_scope( \DOMElement $th ): ?string {
		$parent_row = $th->parentNode;
		
		if ( ! $parent_row || $parent_row->nodeName !== 'tr' ) {
			return null;
		}

		// Check if th is in first row (likely column header)
		$table = $this->find_ancestor_table( $th );
		if ( ! $table ) {
			return null;
		}

		// Skip layout tables
		$role = $table->getAttribute( 'role' );
		if ( $role === 'presentation' || $role === 'none' ) {
			return null;
		}

		$rows = $this->query( './/tr', $table );
		$is_first_row = ( count( $rows ) > 0 && $rows[0] === $parent_row );

		// Check if th is in thead
		$parent_section = $parent_row->parentNode;
		$is_in_thead = ( $parent_section && $parent_section->nodeName === 'thead' );

		// Check if th is first cell in row (likely row header)
		$is_first_cell = ( $parent_row->firstChild === $th ) || 
						  ( $parent_row->getElementsByTagName( '*' )->item( 0 ) === $th );

		// Count ths in this row
		$ths_in_row = $this->query( './th', $parent_row );
		$tds_in_row = $this->query( './td', $parent_row );
		
		// If row is all th elements, likely column headers
		if ( count( $tds_in_row ) === 0 && count( $ths_in_row ) > 1 ) {
			return 'col';
		}

		// If in thead or first row with multiple ths
		if ( $is_in_thead || $is_first_row ) {
			return 'col';
		}

		// If first cell with td siblings, likely row header
		if ( $is_first_cell && count( $tds_in_row ) > 0 ) {
			return 'row';
		}

		// Default to col for ambiguous cases
		return 'col';
	}

	/**
	 * Find the ancestor table element
	 */
	private function find_ancestor_table( \DOMElement $element ): ?\DOMElement {
		$parent = $element->parentNode;
		
		while ( $parent ) {
			if ( $parent instanceof \DOMElement && $parent->nodeName === 'table' ) {
				return $parent;
			}
			$parent = $parent->parentNode;
		}

		return null;
	}
}
