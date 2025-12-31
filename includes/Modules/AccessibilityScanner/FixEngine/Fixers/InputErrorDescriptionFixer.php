<?php
/**
 * Input Error Description Fixer
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
 * Class InputErrorDescriptionFixer
 * 
 * Adds aria-describedby to form inputs that have associated error messages.
 */
final class InputErrorDescriptionFixer extends AbstractFixer {

	public function get_id(): string {
		return 'input_error_description';
	}

	public function get_name(): string {
		return __( 'Input Error Description', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Links form inputs to their error messages using aria-describedby.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '3.3.1', '3.3.3' ];
	}

	public function get_category(): string {
		return 'forms';
	}

	public function can_fix( string $content ): bool {
		// Look for common error class patterns
		return (bool) preg_match( '/class\s*=\s*["\'][^"\']*(?:error|invalid|validation|feedback)[^"\']*["\']/', $content );
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find error message elements
		$error_patterns = [
			'//span[contains(@class, "error")]',
			'//div[contains(@class, "error")]',
			'//p[contains(@class, "error")]',
			'//span[contains(@class, "invalid")]',
			'//div[contains(@class, "invalid")]',
			'//span[contains(@class, "validation")]',
			'//div[contains(@class, "feedback") and contains(@class, "invalid")]',
			'//*[contains(@class, "form-error")]',
			'//*[contains(@class, "field-error")]',
		];

		$fixes_applied = 0;
		$details = [];
		$error_id_counter = 0;

		foreach ( $error_patterns as $pattern ) {
			$error_elements = $this->query( $pattern );

			foreach ( $error_elements as $error ) {
				// Find the associated input (likely a sibling or nearby)
				$input = $this->find_associated_input( $error );
				
				if ( ! $input ) {
					continue;
				}

				// Skip if input already has aria-describedby pointing to this error
				$existing_describedby = $input->getAttribute( 'aria-describedby' );
				
				// Ensure error element has an ID
				$error_id = $error->getAttribute( 'id' );
				if ( ! $error_id ) {
					$error_id_counter++;
					$error_id = 'slos-error-desc-' . $error_id_counter;
					$error->setAttribute( 'id', $error_id );
				}

				// Check if already linked
				if ( $existing_describedby && strpos( $existing_describedby, $error_id ) !== false ) {
					continue;
				}

				// Add or append to aria-describedby
				$new_describedby = $existing_describedby 
					? $existing_describedby . ' ' . $error_id 
					: $error_id;
				
				$input->setAttribute( 'aria-describedby', $new_describedby );
				
				// Also add role="alert" to error for screen readers
				if ( ! $error->getAttribute( 'role' ) ) {
					$error->setAttribute( 'role', 'alert' );
				}

				// Add aria-live for dynamic errors
				if ( ! $error->getAttribute( 'aria-live' ) ) {
					$error->setAttribute( 'aria-live', 'polite' );
				}

				$fixes_applied++;
				$details[] = [
					'input_name' => $input->getAttribute( 'name' ) ?: $input->getAttribute( 'id' ),
					'error_id'   => $error_id,
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No unlinked error messages found', $content );
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
	 * Find the form input associated with an error message
	 */
	private function find_associated_input( \DOMElement $error ): ?\DOMElement {
		// First, check for explicit "for" reference in error's data attributes
		$for_id = $error->getAttribute( 'data-for' ) ?: $error->getAttribute( 'for' );
		if ( $for_id ) {
			$inputs = $this->query( '//*[@id="' . $for_id . '"]' );
			if ( count( $inputs ) > 0 ) {
				return $inputs[0];
			}
		}

		// Check previous siblings
		$previous = $error->previousSibling;
		while ( $previous ) {
			if ( $previous instanceof \DOMElement ) {
				if ( in_array( $previous->nodeName, [ 'input', 'select', 'textarea' ], true ) ) {
					return $previous;
				}
				// Check inside wrapper divs
				$inputs = $this->query( './/input | .//select | .//textarea', $previous );
				if ( count( $inputs ) > 0 ) {
					return $inputs[ count( $inputs ) - 1 ]; // Last input
				}
			}
			$previous = $previous->previousSibling;
		}

		// Check parent form-group or field wrapper
		$parent = $error->parentNode;
		if ( $parent instanceof \DOMElement ) {
			$parent_class = $parent->getAttribute( 'class' );
			if ( preg_match( '/form[-_]?(group|field|row|control)/i', $parent_class ) ) {
				$inputs = $this->query( './/input | .//select | .//textarea', $parent );
				if ( count( $inputs ) > 0 ) {
					return $inputs[0];
				}
			}
		}

		return null;
	}
}
