<?php
/**
 * Table Header Fixer
 *
 * Adds proper headers to tables.
 * WCAG 1.3.1 Info and Relationships (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TableHeaderFixer Class
 *
 * Adds proper headers and structure to tables.
 */
class TableHeaderFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'table-header';
	}

	/**
	 * Get fixer name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Table Headers';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds proper headers and structure to tables';
	}

	/**
	 * Fix table headers in content
	 *
	 * @param string $content Post content
	 * @return array Fix result with content and count
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixes_applied = 0;

		// Find tables without thead
		$tables = $xpath->query( '//table[not(thead)]' );

		foreach ( $tables as $table ) {
			// Check if first row looks like headers
			$first_row = $xpath->query( './/tr[1]', $table )->item( 0 );
			
			if ( $first_row && $this->looks_like_header_row( $first_row ) ) {
				// Create thead and move first row into it
				$thead = $dom->createElement( 'thead' );
				$first_row->parentNode->removeChild( $first_row );
				$thead->appendChild( $first_row );
				
				// Insert thead at beginning of table
				$tbody = $table->getElementsByTagName( 'tbody' )->item( 0 );
				if ( $tbody ) {
					$table->insertBefore( $thead, $tbody );
				} else {
					$table->insertBefore( $thead, $table->firstChild );
				}

				// Convert td to th in header row
				$cells = $xpath->query( './/td', $first_row );
				foreach ( $cells as $td ) {
					$th = $dom->createElement( 'th' );
					$th->setAttribute( 'scope', 'col' );
					
					// Copy attributes
					foreach ( $td->attributes as $attr ) {
						$th->setAttribute( $attr->name, $attr->value );
					}
					
					// Copy content
					while ( $td->firstChild ) {
						$th->appendChild( $td->firstChild );
					}
					
					$td->parentNode->replaceChild( $th, $td );
					$fixes_applied++;
				}
			}
		}

		// Find th elements without scope
		$headers = $xpath->query( '//th[not(@scope)]' );
		foreach ( $headers as $th ) {
			// Determine if it's a column or row header
			$parent_row = $th->parentNode;
			$parent_section = $parent_row->parentNode;
			
			if ( $parent_section->nodeName === 'thead' ) {
				$th->setAttribute( 'scope', 'col' );
			} else {
				// If it's the first cell in the row, it's probably a row header
				$first_cell = $xpath->query( './*[1]', $parent_row )->item( 0 );
				if ( $first_cell === $th ) {
					$th->setAttribute( 'scope', 'row' );
				} else {
					$th->setAttribute( 'scope', 'col' );
				}
			}
			$fixes_applied++;
		}

		return $this->return_result( $this->dom_to_html( $dom ), $fixes_applied );
	}

	/**
	 * Determine if a row looks like a header row
	 *
	 * @param \DOMElement $row Table row element
	 * @return bool True if it looks like a header row
	 */
	private function looks_like_header_row( $row ) {
		$cells = $row->getElementsByTagName( 'td' );
		if ( $cells->length === 0 ) {
			return false;
		}

		$bold_count = 0;
		$short_count = 0;
		
		foreach ( $cells as $cell ) {
			$content = trim( $cell->textContent );
			
			// Check if content is short (likely a header)
			if ( strlen( $content ) < 50 ) {
				$short_count++;
			}

			// Check for bold or strong styling
			if ( strpos( $cell->getAttribute( 'style' ), 'bold' ) !== false ||
			     $cell->getElementsByTagName( 'strong' )->length > 0 ||
			     $cell->getElementsByTagName( 'b' )->length > 0 ) {
				$bold_count++;
			}
		}

		// If most cells are short and/or bold, it's probably a header row
		return ( $short_count >= $cells->length * 0.7 ) || ( $bold_count >= $cells->length * 0.5 );
	}
}
