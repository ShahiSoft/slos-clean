<?php
/**
 * Table Caption Fixer
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
 * Class TableCaptionFixer
 * 
 * Adds caption elements to data tables.
 */
final class TableCaptionFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-table-caption';
	}

	public function get_name(): string {
		return __( 'Table Caption', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds caption elements to tables for better screen reader context.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.3.1' ];
	}

	public function get_category(): string {
		return 'tables';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<table' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find tables without caption (but have th elements, indicating data table)
		$tables = $this->query( '//table[not(caption) and .//th]' );
		$fixes_applied = 0;
		$details = [];

		foreach ( $tables as $table ) {
			// Skip layout tables (role="presentation" or "none")
			$role = $table->getAttribute( 'role' );
			if ( $role === 'presentation' || $role === 'none' ) {
				continue;
			}

			// Skip if already has aria-label or aria-labelledby
			if ( $table->hasAttribute( 'aria-label' ) || $table->hasAttribute( 'aria-labelledby' ) ) {
				continue;
			}

			// Generate caption from first th row or aria-label
			$caption_text = $this->generate_caption( $table );
			
			$caption = $this->doc->createElement( 'caption' );
			$caption->textContent = $caption_text;
			
			// Insert caption as first child
			if ( $table->firstChild ) {
				$table->insertBefore( $caption, $table->firstChild );
			} else {
				$table->appendChild( $caption );
			}

			$fixes_applied++;
			$details[] = [
				'caption' => $caption_text,
			];
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All data tables have captions', $content );
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
	 * Generate a caption based on table content
	 */
	private function generate_caption( \DOMElement $table ): string {
		// Try to use summary attribute if present
		$summary = $table->getAttribute( 'summary' );
		if ( ! empty( $summary ) ) {
			return $summary;
		}

		// Try to derive from first header row
		$ths = $this->query( './/tr[1]/th', $table );
		if ( count( $ths ) > 0 ) {
			$headers = [];
			foreach ( $ths as $th ) {
				$text = trim( $th->textContent );
				if ( ! empty( $text ) && strlen( $text ) < 30 ) {
					$headers[] = $text;
				}
			}
			if ( count( $headers ) >= 2 && count( $headers ) <= 4 ) {
				return sprintf( 
					__( 'Table showing %s', 'shahi-legalflowsuite' ), 
					implode( ', ', array_slice( $headers, 0, 3 ) ) 
				);
			}
		}

		// Check for nearby heading
		$preceding = $this->query( 'preceding-sibling::*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6][1]', $table );
		if ( count( $preceding ) > 0 ) {
			$heading_text = trim( $preceding[0]->textContent );
			if ( ! empty( $heading_text ) ) {
				return sprintf( __( 'Table: %s', 'shahi-legalflowsuite' ), $heading_text );
			}
		}

		// Fallback
		return __( 'Data table', 'shahi-legalflowsuite' );
	}
}
