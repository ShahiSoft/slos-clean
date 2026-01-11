<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ErrorIdentificationFixer extends AbstractFixer {

	public function get_id(): string {
		return 'error-identification';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		$fixed_count += $this->fix_error_associations( $dom, $xpath );
		$fixed_count += $this->fix_invalid_states( $xpath );
		$fixed_count += $this->fix_error_summaries( $dom, $xpath );
		$fixed_count += $this->fix_inline_errors( $dom, $xpath );
		$fixed_count += $this->fix_color_only_errors( $dom, $xpath );
		$fixed_count += $this->fix_required_indicators( $dom, $xpath );

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No error-identification fixes applied', $content );
		}

		$fixed_content = $this->get_html( $dom );

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			array()
		);
	}

	private function fix_error_associations( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$errors = $xpath->query(
			'//*[contains(@class, "error") or contains(@class, "invalid") or '
			. 'contains(@class, "field-error") or contains(@class, "error-message") or '
			. 'contains(@class, "validation-error")]'
		);

		if ( ! $errors instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $errors as $error ) {
			if ( ! $error instanceof \DOMElement ) {
				continue;
			}

			if ( ! $error->hasAttribute( 'id' ) ) {
				$error_id = 'slos-error-' . uniqid();
				$error->setAttribute( 'id', $error_id );
			} else {
				$error_id = $error->getAttribute( 'id' );
			}

			if ( ! $error->hasAttribute( 'role' ) ) {
				$error->setAttribute( 'role', 'alert' );
			}

			$input = $this->find_associated_input( $xpath, $error );

			if ( $input ) {
				$existing = $input->getAttribute( 'aria-describedby' );
				if ( $existing ) {
					if ( strpos( $existing, $error_id ) === false ) {
						$input->setAttribute( 'aria-describedby', $existing . ' ' . $error_id );
					}
				} else {
					$input->setAttribute( 'aria-describedby', $error_id );
				}

				$input->setAttribute( 'aria-invalid', 'true' );
				++$fixed;
			}
		}

		return $fixed;
	}

	private function fix_invalid_states( \DOMXPath $xpath ): int {
		$fixed = 0;

		$invalid_inputs = $xpath->query(
			'//input[contains(@class, "error") or contains(@class, "invalid") or '
			. 'contains(@class, "has-error") or contains(@class, "is-invalid")]'
			. '[not(@aria-invalid)]'
		);
		if ( $invalid_inputs instanceof \DOMNodeList ) {
			foreach ( $invalid_inputs as $input ) {
				if ( ! $input instanceof \DOMElement ) {
					continue;
				}
				$input->setAttribute( 'aria-invalid', 'true' );
				++$fixed;
			}
		}

		$invalid_selects = $xpath->query(
			'//select[contains(@class, "error") or contains(@class, "invalid") or '
			. 'contains(@class, "has-error") or contains(@class, "is-invalid")]'
			. '[not(@aria-invalid)]'
		);
		if ( $invalid_selects instanceof \DOMNodeList ) {
			foreach ( $invalid_selects as $select ) {
				if ( ! $select instanceof \DOMElement ) {
					continue;
				}
				$select->setAttribute( 'aria-invalid', 'true' );
				++$fixed;
			}
		}

		$invalid_textareas = $xpath->query(
			'//textarea[contains(@class, "error") or contains(@class, "invalid") or '
			. 'contains(@class, "has-error") or contains(@class, "is-invalid")]'
			. '[not(@aria-invalid)]'
		);
		if ( $invalid_textareas instanceof \DOMNodeList ) {
			foreach ( $invalid_textareas as $textarea ) {
				if ( ! $textarea instanceof \DOMElement ) {
					continue;
				}
				$textarea->setAttribute( 'aria-invalid', 'true' );
				++$fixed;
			}
		}

		$wrapper_errors = $xpath->query(
			'//*[contains(@class, "has-error") or contains(@class, "error-field") or '
			. 'contains(@class, "form-group") and contains(@class, "error")]'
			. '//input[not(@aria-invalid)] | '
			. '//*[contains(@class, "has-error") or contains(@class, "error-field")]'
			. '//select[not(@aria-invalid)] | '
			. '//*[contains(@class, "has-error") or contains(@class, "error-field")]'
			. '//textarea[not(@aria-invalid)]'
		);
		if ( $wrapper_errors instanceof \DOMNodeList ) {
			foreach ( $wrapper_errors as $input ) {
				if ( ! $input instanceof \DOMElement ) {
					continue;
				}
				$input->setAttribute( 'aria-invalid', 'true' );
				++$fixed;
			}
		}

		return $fixed;
	}

	private function fix_error_summaries( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$summaries = $xpath->query(
			'//*[contains(@class, "error-summary") or contains(@class, "validation-summary") or '
			. 'contains(@class, "form-errors") or contains(@class, "alert-error") or '
			. 'contains(@class, "errors-list")]'
		);
		if ( ! $summaries instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $summaries as $summary ) {
			if ( ! $summary instanceof \DOMElement ) {
				continue;
			}
			$modified = false;

			if ( ! $summary->hasAttribute( 'role' ) ) {
				$summary->setAttribute( 'role', 'alert' );
				$modified = true;
			}
			if ( ! $summary->hasAttribute( 'aria-live' ) ) {
				$summary->setAttribute( 'aria-live', 'assertive' );
				$modified = true;
			}
			if ( ! $summary->hasAttribute( 'aria-labelledby' ) ) {
				$heading = $xpath->query( './/h1|.//h2|.//h3|.//h4|.//h5|.//h6', $summary )->item( 0 );
				if ( $heading instanceof \DOMElement ) {
					if ( ! $heading->hasAttribute( 'id' ) ) {
						$heading->setAttribute( 'id', 'error-summary-title-' . uniqid() );
					}
					$summary->setAttribute( 'aria-labelledby', $heading->getAttribute( 'id' ) );
					$modified = true;
				}
			}

			$list = $xpath->query( './/ul|.//ol', $summary )->item( 0 );
			if ( $list instanceof \DOMElement ) {
				$items = $xpath->query( './/li', $list );
				if ( $items instanceof \DOMNodeList ) {
					foreach ( $items as $item ) {
						if ( ! $item instanceof \DOMElement ) {
							continue;
						}
						$existing_link = $xpath->query( './/a', $item );
						if ( $existing_link instanceof \DOMNodeList && 0 === $existing_link->length ) {
							$text       = $item->textContent;
							$field_name = $this->extract_field_name( $text );
							if ( $field_name ) {
								$field = $xpath->query(
									"//input[@name='{$field_name}' or @id='{$field_name}'] | " .
									"//select[@name='{$field_name}' or @id='{$field_name}'] | " .
									"//textarea[@name='{$field_name}' or @id='{$field_name}']"
								)->item( 0 );

								if ( $field instanceof \DOMElement ) {
									$field_id = $field->getAttribute( 'id' );
									if ( ! $field_id ) {
										$field_id = 'slos-field-' . $field_name;
										$field->setAttribute( 'id', $field_id );
									}

									$link = $dom->createElement( 'a' );
									$link->setAttribute( 'href', '#' . $field_id );
									$link->textContent = $text;

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
			}

			if ( $modified ) {
				++$fixed;
			}
		}

		return $fixed;
	}

	private function fix_inline_errors( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$inline_errors = $xpath->query(
			'//span[contains(@class, "error-message") or contains(@class, "field-error") or '
			. '(contains(@class, "help-block") and contains(@class, "error"))] | '
			. '//div[contains(@class, "error-message") or contains(@class, "field-error") or '
			. 'contains(@class, "invalid-feedback")]'
		);
		if ( ! $inline_errors instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $inline_errors as $error ) {
			if ( ! $error instanceof \DOMElement ) {
				continue;
			}
			$modified = false;
			$text     = $error->textContent;

			if ( strpos( $text, '⚠' ) === false &&
				strpos( $text, '❌' ) === false &&
				strpos( $text, '!' ) === false &&
				strpos( $text, '✕' ) === false ) {
				$icon = $dom->createElement( 'span' );
				$icon->setAttribute( 'aria-hidden', 'true' );
				$icon->setAttribute( 'class', 'slos-error-icon' );
				$icon->textContent = '⚠ ';
				$error->insertBefore( $icon, $error->firstChild );
				$modified = true;
			}

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

	private function fix_color_only_errors( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$red_border_inputs = $xpath->query(
			'//input[@style[contains(., "border") and (contains(., "red") or '
			. "contains(., '#f00') or contains(., '#ff0000') or contains(., '#e74c3c') or "
			. "contains(., '#dc3545') or contains(., 'rgb(255'))]]"
		);
		if ( $red_border_inputs instanceof \DOMNodeList ) {
			foreach ( $red_border_inputs as $input ) {
				if ( ! $input instanceof \DOMElement ) {
					continue;
				}
				$describedby = $input->getAttribute( 'aria-describedby' );

				if ( ! $describedby ) {
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
		}

		$color_only_groups = $xpath->query(
			'//*[contains(@class, "form-group") or contains(@class, "field-wrapper")]'
			. '[@style[contains(., "border") and contains(., "red")]]'
		);
		if ( $color_only_groups instanceof \DOMNodeList ) {
			foreach ( $color_only_groups as $group ) {
				if ( ! $group instanceof \DOMElement ) {
					continue;
				}
				$has_error_msg = $xpath->query( './/*[contains(@class, "error")]', $group );
				if ( $has_error_msg instanceof \DOMNodeList && $has_error_msg->length > 0 ) {
					continue;
				}

				$sr_text = $dom->createElement( 'span' );
				$sr_text->setAttribute( 'class', 'screen-reader-text slos-sr-only' );
				$sr_text->textContent = 'This field has an error';
				$group->appendChild( $sr_text );
				++$fixed;
			}
		}

		return $fixed;
	}

	private function fix_required_indicators( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$required_labels = $xpath->query(
			'//label[contains(., "*") and not(.//*[contains(@class, "required")])]'
		);
		if ( ! $required_labels instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $required_labels as $label ) {
			if ( ! $label instanceof \DOMElement ) {
				continue;
			}
			$text = $label->textContent;

			if ( preg_match( '/\*/', $text ) && strpos( $text, 'required' ) === false ) {
				$sr_text = $dom->createElement( 'span' );
				$sr_text->setAttribute( 'class', 'screen-reader-text slos-sr-only' );
				$sr_text->textContent = ' (required)';
				$label->appendChild( $sr_text );
				++$fixed;
			}
		}

		return $fixed;
	}

	private function find_associated_input( \DOMXPath $xpath, \DOMElement $error ): ?\DOMElement {
		$parent = $error->parentNode;
		if ( $parent instanceof \DOMElement ) {
			$input = $xpath->query( './/input|.//select|.//textarea', $parent )->item( 0 );
			if ( $input instanceof \DOMElement ) {
				return $input;
			}
		}

		$prev = $error->previousSibling;
		while ( $prev ) {
			if ( $prev instanceof \DOMElement && in_array( strtolower( $prev->tagName ), array( 'input', 'select', 'textarea' ), true ) ) {
				return $prev;
			}
			$prev = $prev->previousSibling;
		}

		if ( $error->hasAttribute( 'data-for' ) ) {
			$for_id = $error->getAttribute( 'data-for' );
			$input  = $xpath->query( "//*[@id='{$for_id}']" )->item( 0 );
			if ( $input instanceof \DOMElement ) {
				return $input;
			}
		}

		$text       = $error->textContent;
		$field_name = $this->extract_field_name( $text );
		if ( $field_name ) {
			$input = $xpath->query(
				"//input[@name='{$field_name}' or @id='{$field_name}'] | " .
				"//select[@name='{$field_name}' or @id='{$field_name}'] | " .
				"//textarea[@name='{$field_name}' or @id='{$field_name}']"
			)->item( 0 );
			if ( $input instanceof \DOMElement ) {
				return $input;
			}
		}

		return null;
	}

	private function extract_field_name( string $text ): ?string {
		$patterns = array(
			'/(?:field|input)\s+"([^"]+)"/i',
			'/(?:the\s+)?(?:field\s+)?["\']([^"\']+)["\']\s+(?:is|field)/i',
			'/(?:enter|provide|specify)\s+(?:a|an|your)?\s*(\w+)/i',
			'/(\w+)\s+(?:is\s+)?(?:required|invalid|empty|missing)/i',
			'/(?:please\s+)?(?:fill\s+)?(?:in\s+)?(?:the\s+)?(\w+)\s+field/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $text, $matches ) ) {
				$name         = strtolower( $matches[1] );
				$common_words = array( 'this', 'the', 'a', 'an', 'is', 'are', 'was', 'be', 'field' );
				if ( ! in_array( $name, $common_words, true ) ) {
					return $name;
				}
			}
		}

		return null;
	}
}
