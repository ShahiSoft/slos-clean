<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for proper error identification in forms
 * WCAG 3.3.1 - Error Identification (Level A)
 *
 * @since 3.1.2
 */
class ErrorIdentificationCheck extends AbstractCheck {

	/**
	 * Error container class patterns
	 *
	 * @var array
	 */
	private $error_patterns = array(
		'error',
		'invalid',
		'validation-error',
		'field-error',
		'form-error',
		'input-error',
		'has-error',
		'is-invalid',
	);

	/**
	 * Common error color values (red variants)
	 *
	 * @var array
	 */
	private $error_colors = array(
		'red',
		'#f00',
		'#ff0000',
		'#dc3545',   // Bootstrap danger
		'#d32f2f',   // Material red
		'#e53935',   // Material red 600
		'#c62828',   // Material red 800
		'#b71c1c',   // Material red 900
		'#f44336',   // Material red 500
		'#ef5350',   // Material red 400
		'rgb(220, 53, 69)',   // Bootstrap danger
		'rgb(211, 47, 47)',   // Material red
	);

	/**
	 * Get check ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'error-identification';
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'When input errors are detected, the item in error must be identified and the error described in text.';
	}

	/**
	 * Get severity level
	 *
	 * @return string
	 */
	public function get_severity() {
		return 'serious';
	}

	/**
	 * Get WCAG criteria
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '3.3.1';
	}

	/**
	 * Run the check
	 *
	 * @param string $content HTML content to check.
	 * @return array Array of issues found.
	 */
	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check required fields have error handling setup
		$this->check_required_fields( $xpath, $issues );

		// 2. Check aria-invalid usage has associated error message
		$this->check_aria_invalid( $xpath, $issues );

		// 3. Check error containers have text content
		$this->check_error_containers( $xpath, $issues );

		// 4. Check for color-only error indication
		$this->check_color_only_errors( $xpath, $issues );

		// 5. Check error summary/list accessibility
		$this->check_error_summary( $xpath, $issues );

		// 6. Check aria-errormessage references
		$this->check_errormessage_references( $xpath, $issues );

		return $issues;
	}

	/**
	 * Check required fields have error handling setup
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_required_fields( $xpath, &$issues ) {
		$required = $xpath->query( '//input[@required] | //select[@required] | //textarea[@required]' );

		foreach ( $required as $field ) {
			// Check for error message linking
			$has_error_link = $field->hasAttribute( 'aria-describedby' ) ||
								$field->hasAttribute( 'aria-errormessage' );

			// Check if field is inside a form with validation handling
			$in_validated_form = $this->is_in_validated_form( $field );

			if ( ! $has_error_link && ! $in_validated_form ) {
				$field_name = $this->get_field_identifier( $field );

				$issues[] = array(
					'element'  => $field->tagName,
					'context'  => $this->get_element_html( $field ),
					'message'  => sprintf(
						'Required field%s should have aria-describedby or aria-errormessage attribute to link to error message element for when validation fails.',
						$field_name ? " ($field_name)" : ''
					),
					'severity' => 'notice',
				);
			}
		}
	}

	/**
	 * Check if field is inside a form with validation indicators
	 *
	 * @param \DOMElement $field Field element.
	 * @return bool True if in validated form.
	 */
	private function is_in_validated_form( $field ) {
		$parent = $field->parentNode;

		while ( $parent && $parent instanceof \DOMElement ) {
			if ( strtolower( $parent->tagName ) === 'form' ) {
				// Check for validation attributes
				if ( $parent->hasAttribute( 'novalidate' ) ) {
					// Using custom validation
					return true;
				}
				$class = $parent->getAttribute( 'class' );
				if ( preg_match( '/validated|validation|needs-validation/i', $class ) ) {
					return true;
				}
			}
			$parent = $parent->parentNode;
		}

		return false;
	}

	/**
	 * Get field identifier for error messages
	 *
	 * @param \DOMElement $field Field element.
	 * @return string Field identifier.
	 */
	private function get_field_identifier( $field ) {
		$name = $field->getAttribute( 'name' );
		if ( ! empty( $name ) ) {
			return $name;
		}

		$id = $field->getAttribute( 'id' );
		if ( ! empty( $id ) ) {
			return $id;
		}

		$placeholder = $field->getAttribute( 'placeholder' );
		if ( ! empty( $placeholder ) ) {
			return $placeholder;
		}

		return '';
	}

	/**
	 * Check aria-invalid has associated error message
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_aria_invalid( $xpath, &$issues ) {
		$invalid = $xpath->query( '//*[@aria-invalid="true"]' );

		foreach ( $invalid as $field ) {
			$described_by  = trim( $field->getAttribute( 'aria-describedby' ) );
			$error_message = trim( $field->getAttribute( 'aria-errormessage' ) );

			if ( empty( $described_by ) && empty( $error_message ) ) {
				$issues[] = array(
					'element' => $field->tagName,
					'context' => $this->get_element_html( $field ),
					'message' => 'Field marked as invalid (aria-invalid="true") must have an associated error message via aria-describedby or aria-errormessage.',
				);
			} else {
				// Verify the referenced element exists and has content
				$ref_id = ! empty( $error_message ) ? $error_message : explode( ' ', $described_by )[0];

				if ( ! empty( $ref_id ) ) {
					$ref_element = $xpath->query( "//*[@id='$ref_id']" );

					if ( $ref_element->length === 0 ) {
						$issues[] = array(
							'element' => $field->tagName,
							'context' => $this->get_element_html( $field ),
							'message' => "Error message reference '#$ref_id' does not exist in the document.",
						);
					} elseif ( trim( $ref_element->item( 0 )->textContent ) === '' ) {
						// Check if it's hidden (might be filled dynamically)
						$ref_style   = $ref_element->item( 0 )->getAttribute( 'style' );
						$ref_hidden  = $ref_element->item( 0 )->getAttribute( 'hidden' );
						$aria_hidden = $ref_element->item( 0 )->getAttribute( 'aria-hidden' );

						if ( ! preg_match( '/display\s*:\s*none/i', $ref_style ) &&
							empty( $ref_hidden ) &&
							$aria_hidden !== 'true' ) {
							$issues[] = array(
								'element'  => $field->tagName,
								'context'  => $this->get_element_html( $field ),
								'message'  => "Error message element '#$ref_id' appears to be empty. Ensure error text is provided when field is invalid.",
								'severity' => 'warning',
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Check error containers have text content
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_error_containers( $xpath, &$issues ) {
		foreach ( $this->error_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

			foreach ( $elements as $element ) {
				// Skip if hidden
				if ( $this->is_hidden( $element ) ) {
					continue;
				}

				// Check for empty error containers that are visible
				$text = trim( $element->textContent );

				if ( empty( $text ) ) {
					// Check if it has aria-hidden (intentionally hidden for now)
					if ( $element->getAttribute( 'aria-hidden' ) === 'true' ) {
						continue;
					}

					// Check if it contains only icons (aria-label might describe error)
					$aria_label = $element->getAttribute( 'aria-label' );
					if ( ! empty( $aria_label ) ) {
						continue;
					}

					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => sprintf(
							'Error container (class "%s") appears visible but empty. Error messages must describe the error in text, not just indicate it with color/icon.',
							$pattern
						),
						'severity' => 'warning',
					);
				}
			}
		}
	}

	/**
	 * Check if element is hidden
	 *
	 * @param \DOMElement $element Element to check.
	 * @return bool True if hidden.
	 */
	private function element_is_hidden( $element ) {
		$style = $element->getAttribute( 'style' );

		if ( preg_match( '/display\s*:\s*none|visibility\s*:\s*hidden/i', $style ) ) {
			return true;
		}

		if ( $element->hasAttribute( 'hidden' ) ) {
			return true;
		}

		// Check for common hidden classes
		$class = $element->getAttribute( 'class' );
		if ( preg_match( '/\b(?:hidden|d-none|hide|invisible)\b/i', $class ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for potential color-only error indication
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_color_only_errors( $xpath, &$issues ) {
		// Look for inputs with red border/outline via inline style
		$inputs = $xpath->query( '//input[@style] | //select[@style] | //textarea[@style]' );

		foreach ( $inputs as $input ) {
			$style = strtolower( $input->getAttribute( 'style' ) );

			// Check for red/error colors in border or outline
			$has_error_color = false;
			foreach ( $this->error_colors as $color ) {
				if ( strpos( $style, strtolower( $color ) ) !== false ) {
					// Check if it's in border/outline context
					if ( preg_match( '/(?:border|outline)[^:]*:[^;]*' . preg_quote( strtolower( $color ), '/' ) . '/i', $style ) ) {
						$has_error_color = true;
						break;
					}
				}
			}

			if ( $has_error_color ) {
				// Check if there's an error message linked
				$has_error_message = $input->hasAttribute( 'aria-describedby' ) ||
									$input->hasAttribute( 'aria-errormessage' ) ||
									$input->getAttribute( 'aria-invalid' ) === 'true';

				if ( ! $has_error_message ) {
					$issues[] = array(
						'element' => $input->tagName,
						'context' => $this->get_element_html( $input ),
						'message' => 'Field appears to indicate error using color only (red border/outline). Errors must also be described in text and the field should have aria-invalid="true".',
					);
				}
			}
		}

		// Also check for class-based error styling without proper ARIA
		$error_inputs = $xpath->query( '//input[contains(@class, "error") or contains(@class, "invalid")] | //select[contains(@class, "error") or contains(@class, "invalid")] | //textarea[contains(@class, "error") or contains(@class, "invalid")]' );

		foreach ( $error_inputs as $input ) {
			$has_invalid = $input->getAttribute( 'aria-invalid' ) === 'true';

			if ( ! $has_invalid ) {
				$issues[] = array(
					'element'  => $input->tagName,
					'context'  => $this->get_element_html( $input ),
					'message'  => 'Field has error/invalid class but no aria-invalid="true". Add this attribute to programmatically indicate the error state to assistive technology.',
					'severity' => 'warning',
				);
			}
		}
	}

	/**
	 * Check error summary/list accessibility
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_error_summary( $xpath, &$issues ) {
		$summary_patterns = array( 'error-summary', 'validation-summary', 'form-errors', 'error-list' );

		foreach ( $summary_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern') or contains(@id, '$pattern')]" );

			foreach ( $elements as $element ) {
				if ( $this->element_is_hidden( $element ) ) {
					continue;
				}

				// Check for role or aria-live for announcement
				$has_announcement = $element->hasAttribute( 'role' ) ||
									$element->getAttribute( 'aria-live' ) === 'polite' ||
									$element->getAttribute( 'aria-live' ) === 'assertive';

				if ( ! $has_announcement ) {
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => sprintf(
							'Error summary container (%s) should have role="alert" or aria-live="assertive" to announce errors to screen reader users when they appear.',
							$pattern
						),
						'severity' => 'warning',
					);
				}

				// Check if it's a proper list
				$has_list = $element->getElementsByTagName( 'ul' )->length > 0 ||
							$element->getElementsByTagName( 'ol' )->length > 0;

				if ( ! $has_list && substr_count( $element->textContent, "\n" ) > 1 ) {
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => 'Error summary with multiple errors should use a list (<ul> or <ol>) for better screen reader navigation.',
						'severity' => 'notice',
					);
				}

				break;
			}
		}
	}

	/**
	 * Check aria-errormessage references are valid
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_errormessage_references( $xpath, &$issues ) {
		$with_errormessage = $xpath->query( '//*[@aria-errormessage]' );

		foreach ( $with_errormessage as $element ) {
			$ref_id = trim( $element->getAttribute( 'aria-errormessage' ) );

			if ( empty( $ref_id ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'aria-errormessage attribute is empty. It must reference the ID of the error message element.',
				);
				continue;
			}

			// Multiple IDs are not supported for aria-errormessage
			if ( strpos( $ref_id, ' ' ) !== false ) {
				$issues[] = array(
					'element'  => $element->tagName,
					'context'  => $this->get_element_html( $element ),
					'message'  => 'aria-errormessage should reference a single ID, not multiple. Use aria-describedby for multiple error descriptions.',
					'severity' => 'warning',
				);
			}

			// Check if aria-invalid is also set
			$is_invalid = $element->getAttribute( 'aria-invalid' );

			if ( $is_invalid !== 'true' ) {
				$issues[] = array(
					'element'  => $element->tagName,
					'context'  => $this->get_element_html( $element ),
					'message'  => 'aria-errormessage requires aria-invalid="true" to be set for the error message to be exposed to assistive technology.',
					'severity' => 'warning',
				);
			}
		}
	}
}
