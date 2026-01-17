<?php
/**
 * Table Header Fixer
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
 * Class TableHeaderFixer
 *
 * Adds proper table header markup to tables.
 */
final class TableHeaderFixer extends AbstractFixer {

	public function get_id(): string {
		return 'table-header';
	}

	public function get_name(): string {
		return __( 'Table Headers', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Converts first row cells to proper th elements and adds scope attributes for better screen reader navigation.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.1' );
	}

	public function get_category(): string {
		return 'tables';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<table' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$tables        = $this->query( '//table' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $tables as $table ) {
			// Skip layout tables..
			if ( $this->is_layout_table( $table ) ) {
				continue;
			}

			$fixed = $this->fix_table_headers( $table );

			if ( $fixed > 0 ) {
				$fixes_applied += $fixed;
				$details[]      = array(
					'table_id'      => $table->getAttribute( 'id' ) ?: 'unnamed',
					'headers_added' => $fixed,
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No tables needing header fixes', $content );
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
	 * Check if table appears to be a layout table
	 *
	 * @param \DOMElement $table
	 * @return bool
	 */
	private function is_layout_table( \DOMElement $table ): bool {
		// Check for role="presentation" or role="none"..
		$role = $table->getAttribute( 'role' );
		if ( in_array( $role, array( 'presentation', 'none' ) ) ) {
			return true;
		}

		// Check for layout-related classes..
		$class          = strtolower( $table->getAttribute( 'class' ) );
		$layout_classes = array( 'layout', 'grid', 'container', 'wrapper', 'structure' );

		foreach ( $layout_classes as $layout_class ) {
			if ( strpos( $class, $layout_class ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Fix table headers
	 *
	 * @param \DOMElement $table
	 * @return int Number of fixes
	 */
	private function fix_table_headers( \DOMElement $table ): int {
		$fixes = 0;

		// Check if table already has th elements..
		$existing_ths = $table->getElementsByTagName( 'th' );

		if ( $existing_ths->length > 0 ) {
			// Add scope to existing headers without it..
			foreach ( $existing_ths as $th ) {
				if ( ! $th->hasAttribute( 'scope' ) ) {
					$scope = $this->determine_scope( $th, $table );
					$th->setAttribute( 'scope', $scope );
					++$fixes;
				}
			}
			return $fixes;
		}

		// No th elements - try to convert first row..
		$rows = $table->getElementsByTagName( 'tr' );

		if ( $rows->length === 0 ) {
			return 0;
		}

		// Look for thead or first row..
		$thead      = $table->getElementsByTagName( 'thead' )->item( 0 );
		$header_row = null;

		if ( $thead ) {
			$thead_rows = $thead->getElementsByTagName( 'tr' );
			if ( $thead_rows->length > 0 ) {
				$header_row = $thead_rows->item( 0 );
			}
		} else {
			$header_row = $rows->item( 0 );
		}

		if ( ! $header_row ) {
			return 0;
		}

		// Convert td to th in header row..
		$cells            = $header_row->getElementsByTagName( 'td' );
		$cells_to_convert = array();

		// Collect cells first (can't modify during iteration)..
		foreach ( $cells as $cell ) {
			$cells_to_convert[] = $cell;
		}

		foreach ( $cells_to_convert as $td ) {
			$th = $this->doc->createElement( 'th' );
			$th->setAttribute( 'scope', 'col' );

			// Copy attributes..
			foreach ( $td->attributes as $attr ) {
				$th->setAttribute( $attr->nodeName, $attr->nodeValue );
			}

			// Copy children..
			while ( $td->firstChild ) {
				$th->appendChild( $td->firstChild );
			}

			$td->parentNode->replaceChild( $th, $td );
			++$fixes;
		}

		// If no thead, create one and move header row..
		if ( ! $thead && $fixes > 0 ) {
			$thead = $this->doc->createElement( 'thead' );
			$tbody = $table->getElementsByTagName( 'tbody' )->item( 0 );

			if ( $tbody ) {
				// Move row from tbody to thead..
				$thead->appendChild( $header_row->cloneNode( true ) );
				$header_row->parentNode->removeChild( $header_row );
				$table->insertBefore( $thead, $tbody );
			} else {
				// Wrap remaining rows in tbody..
				$tbody        = $this->doc->createElement( 'tbody' );
				$rows_to_move = array();

				foreach ( $table->getElementsByTagName( 'tr' ) as $row ) {
					if ( $row !== $header_row ) {
						$rows_to_move[] = $row;
					}
				}

				$thead->appendChild( $header_row );

				foreach ( $rows_to_move as $row ) {
					$tbody->appendChild( $row );
				}

				$table->appendChild( $thead );
				$table->appendChild( $tbody );
			}
		}

		return $fixes;
	}

	/**
	 * Determine appropriate scope for header
	 *
	 * @param \DOMElement $th
	 * @param \DOMElement $table
	 * @return string
	 */
	private function determine_scope( \DOMElement $th, \DOMElement $table ): string {
		// Check if in thead..
		$parent = $th->parentNode;
		while ( $parent && $parent !== $table ) {
			if ( $parent->nodeName === 'thead' ) {
				return 'col';
			}
			$parent = $parent->parentNode;
		}

		// Check position in row..
		$row = $th->parentNode;
		if ( $row && $row->nodeName === 'tr' ) {
			$first_cell = $row->getElementsByTagName( 'th' )->item( 0 );
			if ( $first_cell === $th || $row->getElementsByTagName( 'td' )->item( 0 ) === null ) {
				// First cell or only th in row - likely row header..
				$all_th = $row->getElementsByTagName( 'th' );
				if ( $all_th->length === 1 ) {
					return 'row';
				}
			}
		}

		return 'col';
	}
}
