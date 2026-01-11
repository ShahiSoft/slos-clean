<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Form Label Fixer
 */
class MissingFormLabelFixer extends BaseFixer {
	public function get_id() {
		return 'missing-form-label'; }
	public function get_description() {
		return 'Add labels to form inputs'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Get all inputs that might need labels
		$inputs       = $dom->getElementsByTagName( 'input' );
		$inputs_array = array();
		foreach ( $inputs as $input ) {
			$inputs_array[] = $input;
		}

		foreach ( $inputs_array as $input ) {
			$type = strtolower( $input->getAttribute( 'type' ) ?: 'text' );

			// Skip hidden, submit, button, checkbox, radio (unless missing label)
			if ( in_array( $type, array( 'hidden', 'submit', 'button', 'reset', 'image' ), true ) ) {
				continue;
			}

			// Check if already has aria-label or aria-labelledby
			if ( $input->hasAttribute( 'aria-label' ) || $input->hasAttribute( 'aria-labelledby' ) ) {
				continue;
			}

			// Check if has associated label element
			$input_id = $input->getAttribute( 'id' );
			if ( $input_id ) {
				$labels = $xpath->query( "//label[@for='" . $input_id . "']" );
				if ( $labels->length > 0 ) {
					continue;
				}
			}

			// Check if wrapped in a label
			$parent = $input->parentNode;
			while ( $parent && $parent->nodeName !== 'body' ) {
				if ( strtolower( $parent->nodeName ) === 'label' ) {
					continue 2; // Skip this input, it's wrapped in label
				}
				$parent = $parent->parentNode;
			}

			// Need to add aria-label
			$name = $input->getAttribute( 'name' ) ?: $input->getAttribute( 'placeholder' ) ?: $input->getAttribute( 'id' );
			if ( $name ) {
				$label = ucfirst( str_replace( array( '_', '-' ), ' ', $name ) );
				$input->setAttribute( 'aria-label', $label );
				++$fixed_count;
			}
		}

		// Handle textareas
		$textareas       = $dom->getElementsByTagName( 'textarea' );
		$textareas_array = array();
		foreach ( $textareas as $textarea ) {
			$textareas_array[] = $textarea;
		}

		foreach ( $textareas_array as $textarea ) {
			if ( $textarea->hasAttribute( 'aria-label' ) || $textarea->hasAttribute( 'aria-labelledby' ) ) {
				continue;
			}

			$textarea_id = $textarea->getAttribute( 'id' );
			if ( $textarea_id ) {
				$labels = $xpath->query( "//label[@for='" . $textarea_id . "']" );
				if ( $labels->length > 0 ) {
					continue;
				}
			}

			$name = $textarea->getAttribute( 'name' ) ?: $textarea->getAttribute( 'placeholder' ) ?: $textarea->getAttribute( 'id' );
			if ( $name ) {
				$label = ucfirst( str_replace( array( '_', '-' ), ' ', $name ) );
				$textarea->setAttribute( 'aria-label', $label );
				++$fixed_count;
			}
		}

		// Handle select elements
		$selects       = $dom->getElementsByTagName( 'select' );
		$selects_array = array();
		foreach ( $selects as $select ) {
			$selects_array[] = $select;
		}

		foreach ( $selects_array as $select ) {
			if ( $select->hasAttribute( 'aria-label' ) || $select->hasAttribute( 'aria-labelledby' ) ) {
				continue;
			}

			$select_id = $select->getAttribute( 'id' );
			if ( $select_id ) {
				$labels = $xpath->query( "//label[@for='" . $select_id . "']" );
				if ( $labels->length > 0 ) {
					continue;
				}
			}

			$name = $select->getAttribute( 'name' ) ?: $select->getAttribute( 'id' );
			if ( $name ) {
				$label = ucfirst( str_replace( array( '_', '-' ), ' ', $name ) );
				$select->setAttribute( 'aria-label', $label );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Fieldset Legend Fixer
 */
class FieldsetLegendFixer extends BaseFixer {
	public function get_id() {
		return 'fieldset-legend'; }
	public function get_description() {
		return 'Add legend to fieldsets'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$fieldsets   = $dom->getElementsByTagName( 'fieldset' );
		$fixed_count = 0;

		foreach ( $fieldsets as $fieldset ) {
			$legend = $fieldset->getElementsByTagName( 'legend' )->item( 0 );
			if ( ! $legend ) {
				$legend              = $dom->createElement( 'legend' );
				$legend->textContent = 'Options';

				if ( $fieldset->firstChild ) {
					$fieldset->insertBefore( $legend, $fieldset->firstChild );
				} else {
					$fieldset->appendChild( $legend );
				}
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Required Attribute Fixer
 */
class RequiredAttributeFixer extends BaseFixer {
	public function get_id() {
		return 'required-attribute'; }
	public function get_description() {
		return 'Add required attribute indicators'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		foreach ( $inputs as $input ) {
			if ( $input->hasAttribute( 'required' ) ) {
				if ( ! $input->hasAttribute( 'aria-required' ) ) {
					$input->setAttribute( 'aria-required', 'true' );
					++$fixed_count;
				}
			}
		}

		$textareas = $dom->getElementsByTagName( 'textarea' );
		foreach ( $textareas as $textarea ) {
			if ( $textarea->hasAttribute( 'required' ) ) {
				if ( ! $textarea->hasAttribute( 'aria-required' ) ) {
					$textarea->setAttribute( 'aria-required', 'true' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Error Message Fixer
 */
class ErrorMessageFixer extends BaseFixer {
	public function get_id() {
		return 'error-message'; }
	public function get_description() {
		return 'Associate error messages with inputs'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		foreach ( $inputs as $input ) {
			$id = $input->getAttribute( 'id' );
			if ( $id ) {
				// Look for error message nearby
				$parent = $input->parentNode;
				if ( $parent && $parent->nextSibling ) {
					$next = $parent->nextSibling;
					$text = trim( $next->textContent );
					if ( preg_match( '/(error|invalid|required)/i', $text ) ) {
						$input->setAttribute( 'aria-describedby', $id . '-error' );
						++$fixed_count;
					}
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Autocomplete Fixer
 */
class AutocompleteFixer extends BaseFixer {
	public function get_id() {
		return 'autocomplete'; }
	public function get_description() {
		return 'Add autocomplete attributes'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		$autocomplete_map = array(
			'email'    => 'email',
			'username' => 'username',
			'password' => 'current-password',
			'phone'    => 'tel',
			'url'      => 'url',
			'postal'   => 'postal-code',
			'zip'      => 'postal-code',
			'address'  => 'street-address',
			'city'     => 'address-level2',
			'state'    => 'address-level1',
		);

		foreach ( $inputs as $input ) {
			if ( ! $input->hasAttribute( 'autocomplete' ) ) {
				$name     = strtolower( $input->getAttribute( 'name' ) ?: '' );
				$id       = strtolower( $input->getAttribute( 'id' ) ?: '' );
				$searchIn = $name . ' ' . $id;

				foreach ( $autocomplete_map as $key => $value ) {
					if ( strpos( $searchIn, $key ) !== false ) {
						$input->setAttribute( 'autocomplete', $value );
						++$fixed_count;
						break;
					}
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Input Type Fixer
 */
class InputTypeFixer extends BaseFixer {
	public function get_id() {
		return 'input-type'; }
	public function get_description() {
		return 'Use semantic input types'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		$type_map = array(
			'email'    => 'email',
			'phone'    => 'tel',
			'url'      => 'url',
			'date'     => 'date',
			'number'   => 'number',
			'search'   => 'search',
			'password' => 'password',
		);

		foreach ( $inputs as $input ) {
			$name         = strtolower( $input->getAttribute( 'name' ) ?: '' );
			$id           = strtolower( $input->getAttribute( 'id' ) ?: '' );
			$searchIn     = $name . ' ' . $id;
			$current_type = strtolower( $input->getAttribute( 'type' ) ?: 'text' );

			if ( $current_type === 'text' ) {
				foreach ( $type_map as $key => $type ) {
					if ( strpos( $searchIn, $key ) !== false ) {
						$input->setAttribute( 'type', $type );
						++$fixed_count;
						break;
					}
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Placeholder Label Fixer
 */
class PlaceholderLabelFixer extends BaseFixer {
	public function get_id() {
		return 'placeholder-label'; }
	public function get_description() {
		return 'Add labels for inputs with only placeholder'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		foreach ( $inputs as $input ) {
			if ( $input->hasAttribute( 'placeholder' ) && ! $input->hasAttribute( 'aria-label' ) ) {
				$placeholder = $input->getAttribute( 'placeholder' );
				$input->setAttribute( 'aria-label', $placeholder );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Custom Control Fixer
 */
class CustomControlFixer extends BaseFixer {
	public function get_id() {
		return 'custom-control'; }
	public function get_description() {
		return 'Add ARIA to custom controls'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		// Find elements with click handlers (custom buttons/controls)
		foreach ( $divs as $div ) {
			$class   = $div->getAttribute( 'class' );
			$onclick = $div->getAttribute( 'onclick' );

			if ( ! empty( $onclick ) && empty( $div->getAttribute( 'role' ) ) ) {
				$div->setAttribute( 'role', 'button' );
				$div->setAttribute( 'tabindex', '0' );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Button Label Fixer
 */
class ButtonLabelFixer extends BaseFixer {
	public function get_id() {
		return 'button-label'; }
	public function get_description() {
		return 'Add labels to buttons'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$buttons     = $dom->getElementsByTagName( 'button' );
		$fixed_count = 0;

		foreach ( $buttons as $button ) {
			$text = trim( $button->textContent );
			if ( $text === '' && ! $button->hasAttribute( 'aria-label' ) ) {
				$button->setAttribute( 'aria-label', 'Button' );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Orphaned Label Fixer
 */
class OrphanedLabelFixer extends BaseFixer {
	public function get_id() {
		return 'orphaned-label'; }
	public function get_description() {
		return 'Associate orphaned labels with inputs'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$labels      = $dom->getElementsByTagName( 'label' );
		$fixed_count = 0;

		foreach ( $labels as $label ) {
			if ( ! $label->hasAttribute( 'for' ) ) {
				// Find next input sibling
				$next = $label->nextSibling;
				while ( $next ) {
					if ( $next->nodeType === XML_ELEMENT_NODE ) {
						if ( $next->nodeName === 'input' && $next->hasAttribute( 'id' ) ) {
							$label->setAttribute( 'for', $next->getAttribute( 'id' ) );
							++$fixed_count;
							break;
						}
						break;
					}
					$next = $next->nextSibling;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Form ARIA Fixer
 */
class FormAriaFixer extends BaseFixer {
	public function get_id() {
		return 'form-aria'; }
	public function get_description() {
		return 'Add ARIA roles to forms'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$forms       = $dom->getElementsByTagName( 'form' );
		$fixed_count = 0;

		foreach ( $forms as $form ) {
			if ( ! $form->hasAttribute( 'role' ) ) {
				$form->setAttribute( 'role', 'form' );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}
