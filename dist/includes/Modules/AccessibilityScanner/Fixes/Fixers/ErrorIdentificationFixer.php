<?php
/**
 * Error Identification Fixer
 *
 * Improves form error identification and accessibility.
 * WCAG 3.3.1 Error Identification (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ErrorIdentificationFixer Class
 *
 * Associates error messages with form inputs, adds aria-invalid states,
 * and ensures errors are not conveyed by color alone.
 */
class ErrorIdentificationFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'error-identification';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Improves form error identification and accessibility';
	}

	/**
	 * Apply error identification fixes to content
	 *
	 * @param string $content HTML content to fix.
	 * @return array{fixed_count: int, content: string}
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixed = 0;

		// Fix error messages without proper association...
		$fixed += $this->fix_error_associations( $dom, $xpath );

		// Fix invalid fields without aria-invalid...
		$fixed += $this->fix_invalid_states( $xpath );

		// Fix error summary at form level...
		$fixed += $this->fix_error_summaries( $dom, $xpath );

		// Fix inline error messages...
		$fixed += $this->fix_inline_errors( $dom, $xpath );

		// Fix color-only error indication...
		$fixed += $this->fix_color_only_errors( $dom, $xpath );

		// Fix required field indicators...
		$fixed += $this->fix_required_indicators( $dom, $xpath );

		return array(
			'fixed_count' => $fixed,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix error messages by associating them with inputs
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_error_associations( $dom, $xpath ) {
		$fixed = 0;

		// Find error messages not associated with inputs...
		$errors = $xpath->query(
			'//*[contains(@class, "error") or contains(@class, "invalid") or ' .
			'contains(@class, "field-error") or contains(@class, "error-message") or ' .
			'contains(@class, "validation-error")]'
		);

		foreach ( $errors as $error ) {
			// Generate ID if missing...
			if ( ! $error->hasAttribute( 'id' ) ) {
				$error_id = 'slos-error-' . uniqid();
				$error->setAttribute( 'id', $error_id );
			} else {
				$error_id = $error->getAttribute( 'id' );
			}

			// Add role="alert" if not present...
			if ( ! $error->hasAttribute( 'role' ) ) {
				$error->setAttribute( 'role', 'alert' );
			}

			// Find associated input...
			$input = $this->find_associated_input( $xpath, $error );

			if ( $input ) {
				// Add aria-describedby to input if not already pointing to this error...
				$existing = $input->getAttribute( 'aria-describedby' );
				if ( $existing ) {
					if ( strpos( $existing, $error_id ) === false ) {
						$input->setAttribute( 'aria-describedby', $existing . ' ' . $error_id );
					}
				} else {
					$input->setAttribute( 'aria-describedby', $error_id );
				}

				// Add aria-invalid to input...
				$input->setAttribute( 'aria-invalid', 'true' );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix invalid states on inputs
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_invalid_states( $xpath ) {
		$fixed = 0;

		// Find inputs with error classes but no aria-invalid...
		$invalid_inputs = $xpath->query(
			'//input[contains(@class, "error") or contains(@class, "invalid") or ' .
			'contains(@class, "has-error") or contains(@class, "is-invalid")]' .
			'[not(@aria-invalid)]'
		);

		foreach ( $invalid_inputs as $input ) {
			$input->setAttribute( 'aria-invalid', 'true' );
			++$fixed;
		}

		// Also check select and textarea elements...
		$invalid_selects = $xpath->query(
			'//select[contains(@class, "error") or contains(@class, "invalid") or ' .
			'contains(@class, "has-error") or contains(@class, "is-invalid")]' .
			'[not(@aria-invalid)]'
		);

		foreach ( $invalid_selects as $select ) {
			$select->setAttribute( 'aria-invalid', 'true' );
			++$fixed;
		}

		$invalid_textareas = $xpath->query(
			'//textarea[contains(@class, "error") or contains(@class, "invalid") or ' .
			'contains(@class, "has-error") or contains(@class, "is-invalid")]' .
			'[not(@aria-invalid)]'
		);

		foreach ( $invalid_textareas as $textarea ) {
			$textarea->setAttribute( 'aria-invalid', 'true' );
			++$fixed;
		}

		// Check for parent wrapper with error class...
		$wrapper_errors = $xpath->query(
			'//*[contains(@class, "has-error") or contains(@class, "error-field") or ' .
			'contains(@class, "form-group") and contains(@class, "error")]' .
			'//input[not(@aria-invalid)] | ' .
			'//*[contains(@class, "has-error") or contains(@class, "error-field")]' .
			'//select[not(@aria-invalid)] | ' .
			'//*[contains(@class, "has-error") or contains(@class, "error-field")]' .
			'//textarea[not(@aria-invalid)]'
		);

		foreach ( $wrapper_errors as $input ) {
			$input->setAttribute( 'aria-invalid', 'true' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix error summaries at form level
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_error_summaries( $dom, $xpath ) {
		$fixed = 0;

		// Find error summary containers...
		$summaries = $xpath->query(
			'//*[contains(@class, "error-summary") or contains(@class, "validation-summary") or ' .
			'contains(@class, "form-errors") or contains(@class, "alert-error") or ' .
			'contains(@class, "errors-list")]'
		);

		foreach ( $summaries as $summary ) {
			$modified = false;

			// Add proper ARIA...
			if ( ! $summary->hasAttribute( 'role' ) ) {
				$summary->setAttribute( 'role', 'alert' );
				$modified = true;
			}
			if ( ! $summary->hasAttribute( 'aria-live' ) ) {
				$summary->setAttribute( 'aria-live', 'assertive' );
				$modified = true;
			}
			if ( ! $summary->hasAttribute( 'aria-labelledby' ) ) {
				// Look for heading within...
				$heading = $xpath->query( './/h1|.//h2|.//h3|.//h4|.//h5|.//h6', $summary )->item( 0 );
				if ( $heading ) {
					if ( ! $heading->hasAttribute( 'id' ) ) {
						$heading->setAttribute( 'id', 'error-summary-title-' . uniqid() );
					}
					$summary->setAttribute( 'aria-labelledby', $heading->getAttribute( 'id' ) );
					$modified = true;
				}
			}

			// Ensure error list items are properly structured with links to fields...
			$list = $xpath->query( './/ul|.//ol', $summary )->item( 0 );
			if ( $list ) {
				$items = $xpath->query( './/li', $list );
				foreach ( $items as $item ) {
					// Check if item already has a link...
					$existing_link = $xpath->query( './/a', $item );
					if ( 0 === $existing_link->length ) {
						// Try to create link to field...
						$text       = $item->textContent;
						$field_name = $this->extract_field_name( $text );
						if ( $field_name ) {
							$field = $xpath->query(
								"//input[@name='{$field_name}' or @id='{$field_name}'] | " .
								"//select[@name='{$field_name}' or @id='{$field_name}'] | " .
								"//textarea[@name='{$field_name}' or @id='{$field_name}']"
							)->item( 0 );

							if ( $field ) {
								$field_id = $field->getAttribute( 'id' );
								if ( ! $field_id ) {
									$field_id = 'slos-field-' . $field_name;
									$field->setAttribute( 'id', $field_id );
								}

								$link = $dom->createElement( 'a' );
								$link->setAttribute( 'href', '#' . $field_id );
								$link->textContent = $text;

								// Clear item and add link...
								while ( $item->firstChild ) {
									$item->removeChild( $item->firstChild );
								}
								$item->appendChild( $link );
								$modified = true;
							}
						}
					}
				}
			}

			if ( $modified ) {
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix inline error messages
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_inline_errors( $dom, $xpath ) {
		$fixed = 0;

		// Find span/div errors near form fields...
		$inline_errors = $xpath->query(
			'//span[contains(@class, "error-message") or contains(@class, "field-error") or ' .
			'(contains(@class, "help-block") and contains(@class, "error"))] | ' .
			'//div[contains(@class, "error-message") or contains(@class, "field-error") or ' .
			'contains(@class, "invalid-feedback")]'
		);

		foreach ( $inline_errors as $error ) {
			$modified = false;
			$text     = $error->textContent;

			// Ensure error has visual indicator (not just color)...
			if ( strpos( $text, '⚠' ) === false &&
				strpos( $text, '❌' ) === false &&
				strpos( $text, '!' ) === false &&
				strpos( $text, '✕' ) === false ) {
				// Add error icon...
				$icon = $dom->createElement( 'span' );
				$icon->setAttribute( 'aria-hidden', 'true' );
				$icon->setAttribute( 'class', 'slos-error-icon' );
				$icon->textContent = '⚠ ';
				$error->insertBefore( $icon, $error->firstChild );
				$modified = true;
			}

			// Ensure role="alert"...
			if ( ! $error->hasAttribute( 'role' ) ) {
				$error->setAttribute( 'role', 'alert' );
				$modified = true;
			}

			if ( $modified ) {
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix color-only error indication
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_color_only_errors( $dom, $xpath ) {
		$fixed = 0;

		// Find inputs with red border but no other error indication...
		$red_border_inputs = $xpath->query(
			'//input[@style[contains(., "border") and (contains(., "red") or ' .
			"contains(., '#f00') or contains(., '#ff0000') or contains(., '#e74c3c') or " .
			"contains(., '#dc3545') or contains(., 'rgb(255'))]]"
		);

		foreach ( $red_border_inputs as $input ) {
			// Check if there's an associated error message...
			$describedby = $input->getAttribute( 'aria-describedby' );

			if ( ! $describedby ) {
				// Add visible error indicator...
				$indicator = $dom->createElement( 'span' );
				$indicator->setAttribute( 'class', 'slos-error-indicator' );
				$indicator->setAttribute( 'aria-hidden', 'true' );
				$indicator->textContent = ' ⚠';

				$parent = $input->parentNode;
				if ( $parent ) {
					$parent->insertBefore( $indicator, $input->nextSibling );
					++$fixed;
				}
			}
		}

		// Find form groups with only color indication...
		$color_only_groups = $xpath->query(
			'//*[contains(@class, "form-group") or contains(@class, "field-wrapper")]' .
			'[@style[contains(., "border") and contains(., "red")]]'
		);

		foreach ( $color_only_groups as $group ) {
			// Check if there's already an error message...
			$has_error_msg = $xpath->query( './/*[contains(@class, "error")]', $group )->length > 0;

			if ( ! $has_error_msg ) {
				// Add screen reader text...
				$sr_text = $dom->createElement( 'span' );
				$sr_text->setAttribute( 'class', 'screen-reader-text slos-sr-only' );
				$sr_text->textContent = 'This field has an error';
				$group->appendChild( $sr_text );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix required field indicators
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_required_indicators( $dom, $xpath ) {
		$fixed = 0;

		// Find required fields with only asterisk (which may be styled with color)...
		$required_labels = $xpath->query(
			'//label[contains(., "*") and not(.//*[contains(@class, "required")])]'
		);

		foreach ( $required_labels as $label ) {
			$text = $label->textContent;

			// Check if asterisk is the only indicator...
			if ( preg_match( '/\*/', $text ) && strpos( $text, 'required' ) === false ) {
				// Find the asterisk and ensure it has proper markup...
				// This is a simplified check - full implementation would walk DOM nodes...

				// Add screen reader text for required indicator...
				$sr_text = $dom->createElement( 'span' );
				$sr_text->setAttribute( 'class', 'screen-reader-text slos-sr-only' );
				$sr_text->textContent = ' (required)';
				$label->appendChild( $sr_text );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Find the input associated with an error message
	 *
	 * @param \DOMXPath   $xpath XPath instance.
	 * @param \DOMElement $error Error element.
	 * @return \DOMElement|null Associated input or null.
	 */
	private function find_associated_input( $xpath, $error ) {
		// Strategy 1: Check parent for input...
		$parent = $error->parentNode;
		if ( $parent instanceof \DOMElement ) {
			$input = $xpath->query( './/input|.//select|.//textarea', $parent )->item( 0 );
			if ( $input ) {
				return $input;
			}
		}

		// Strategy 2: Error follows input (previous sibling)...
		$prev = $error->previousSibling;
		while ( $prev ) {
			if ( $prev instanceof \DOMElement &&
				in_array( strtolower( $prev->tagName ), array( 'input', 'select', 'textarea' ), true ) ) {
				return $prev;
			}
			$prev = $prev->previousSibling;
		}

		// Strategy 3: Check for data-for or data-input attribute...
		if ( $error->hasAttribute( 'data-for' ) ) {
			$for_id = $error->getAttribute( 'data-for' );
			$input  = $xpath->query( "//*[@id='{$for_id}']" )->item( 0 );
			if ( $input ) {
				return $input;
			}
		}

		// Strategy 4: Check for name/id pattern in error text...
		$text       = $error->textContent;
		$field_name = $this->extract_field_name( $text );
		if ( $field_name ) {
			$input = $xpath->query(
				"//input[@name='{$field_name}' or @id='{$field_name}'] | " .
				"//select[@name='{$field_name}' or @id='{$field_name}'] | " .
				"//textarea[@name='{$field_name}' or @id='{$field_name}']"
			)->item( 0 );
			if ( $input ) {
				return $input;
			}
		}

		return null;
	}

	/**
	 * Extract field name from error message text
	 *
	 * @param string $text Error message text.
	 * @return string|null Extracted field name or null.
	 */
	private function extract_field_name( $text ) {
		// Look for field name patterns...
		$patterns = array(
			'/(?:field|input)\s+"([^"]+)"/i',
			'/(?:the\s+)?(?:field\s+)?["\']([^"\']+)["\']\s+(?:is|field)/i',
			'/(?:enter|provide|specify)\s+(?:a|an|your)?\s*(\w+)/i',
			'/(\w+)\s+(?:is\s+)?(?:required|invalid|empty|missing)/i',
			'/(?:please\s+)?(?:fill\s+)?(?:in\s+)?(?:the\s+)?(\w+)\s+field/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $text, $matches ) ) {
				$name = strtolower( $matches[1] );
				// Filter out common words that aren't field names...
				$common_words = array( 'this', 'the', 'a', 'an', 'is', 'are', 'was', 'be', 'field' );
				if ( ! in_array( $name, $common_words, true ) ) {
					return $name;
				}
			}
		}

		return null;
	}
}
