<?php
/**
 * List Structure Fixer
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
 * Class ListStructureFixer
 *
 * Fixes improperly structured lists (li elements outside ul/ol).
 */
final class ListStructureFixer extends AbstractFixer {

	public function get_id(): string {
		return 'improper-list-structure';
	}

	public function get_name(): string {
		return __( 'List Structure', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Ensures list items are properly wrapped in ul or ol elements.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.1' );
	}

	public function get_category(): string {
		return 'structure';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<li' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find li elements not inside ul, ol, or menu..
		$orphan_lis = $this->query( '//li[not(parent::ul) and not(parent::ol) and not(parent::menu)]' );

		if ( count( $orphan_lis ) === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All list items are properly nested', $content );
		}

		$fixes_applied = 0;
		$details       = array();

		// Group consecutive orphan li elements..
		$groups        = array();
		$current_group = array();

		foreach ( $orphan_lis as $li ) {
			if ( empty( $current_group ) ) {
				$current_group[] = $li;
			} else {
				// Check if this li is a sibling of the last one..
				$last_li = end( $current_group );
				$next    = $last_li->nextSibling;

				// Skip whitespace text nodes..
				while ( $next && $next->nodeType === XML_TEXT_NODE && trim( $next->nodeValue ) === '' ) {
					$next = $next->nextSibling;
				}

				if ( $next === $li ) {
					$current_group[] = $li;
				} else {
					$groups[]      = $current_group;
					$current_group = array( $li );
				}
			}
		}

		if ( ! empty( $current_group ) ) {
			$groups[] = $current_group;
		}

		// Wrap each group in a ul..
		foreach ( $groups as $group ) {
			if ( empty( $group ) ) {
				continue;
			}

			$ul       = $this->doc->createElement( 'ul' );
			$first_li = $group[0];
			$parent   = $first_li->parentNode;

			// Insert ul before first li..
			$parent->insertBefore( $ul, $first_li );

			// Move all lis into ul..
			foreach ( $group as $li ) {
				$ul->appendChild( $li );
			}

			++$fixes_applied;
			$details[] = array(
				'items_wrapped' => count( $group ),
			);
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}
}
