<?php
/**
 * Form Label Fixer
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
 * Class FormLabelFixer
 *
 * Adds labels to form inputs that are missing them.
 */
final class FormLabelFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-form-label';
	}

	public function get_name(): string {
		return __( 'Form Labels', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds accessible labels to form inputs that are missing them, using placeholder text, nearby text, or generated labels.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.1', '3.3.2', '4.1.2' );
	}

	public function get_category(): string {
		return 'forms';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<input' ) !== false ||
				strpos( $content, '<select' ) !== false ||
				strpos( $content, '<textarea' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details       = array();

		// Process inputs (excluding hidden, submit, button, image, reset)..
		$inputs = $this->query( '//input[not(@type="hidden") and not(@type="submit") and not(@type="button") and not(@type="image") and not(@type="reset")]' );

		foreach ( $inputs as $input ) {
			if ( $this->has_accessible_label( $input ) ) {
				continue;
			}

			$fixed = $this->add_label_to_input( $input );
			if ( $fixed ) {
				++$fixes_applied;
				$details[] = $fixed;
			}
		}

		// Process selects..
		$selects = $this->query( '//select' );

		foreach ( $selects as $select ) {
			if ( $this->has_accessible_label( $select ) ) {
				continue;
			}

			$fixed = $this->add_label_to_input( $select );
			if ( $fixed ) {
				++$fixes_applied;
				$details[] = $fixed;
			}
		}

		// Process textareas..
		$textareas = $this->query( '//textarea' );

		foreach ( $textareas as $textarea ) {
			if ( $this->has_accessible_label( $textarea ) ) {
				continue;
			}

			$fixed = $this->add_label_to_input( $textarea );
			if ( $fixed ) {
				++$fixes_applied;
				$details[] = $fixed;
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No form inputs missing labels', $content );
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
	 * Check if input has accessible label
	 *
	 * @param \DOMElement $input
	 * @return bool
	 */
	private function has_accessible_label( \DOMElement $input ): bool {
		// Has aria-label..
		if ( ! empty( $input->getAttribute( 'aria-label' ) ) ) {
			return true;
		}

		// Has aria-labelledby..
		if ( ! empty( $input->getAttribute( 'aria-labelledby' ) ) ) {
			return true;
		}

		// Check for associated label..
		$id = $input->getAttribute( 'id' );
		if ( ! empty( $id ) ) {
			$labels = $this->query( "//label[@for='{$id}']" );
			if ( count( $labels ) > 0 ) {
				return true;
			}
		}

		// Check if wrapped in label..
		$parent = $input->parentNode;
		while ( $parent ) {
			if ( $parent instanceof \DOMElement && $parent->nodeName === 'label' ) {
				// Check label has text..
				$label_text = trim( str_replace( $input->textContent, '', $parent->textContent ) );
				if ( ! empty( $label_text ) ) {
					return true;
				}
			}
			$parent = $parent->parentNode;
		}

		// Has title (acceptable for accessibility)..
		if ( ! empty( $input->getAttribute( 'title' ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add label to input
	 *
	 * @param \DOMElement $input
	 * @return array|null
	 */
	private function add_label_to_input( \DOMElement $input ): ?array {
		$label_text = $this->derive_label_text( $input );

		if ( empty( $label_text ) ) {
			return null;
		}

		// Ensure input has ID..
		$id = $input->getAttribute( 'id' );
		if ( empty( $id ) ) {
			$id = 'slos-input-' . wp_generate_uuid4();
			$input->setAttribute( 'id', $id );
		}

		// Add aria-label (safest approach that doesn't alter layout)..
		$input->setAttribute( 'aria-label', $label_text );

		return array(
			'element'    => $input->nodeName,
			'type'       => $input->getAttribute( 'type' ) ?: 'text',
			'name'       => $input->getAttribute( 'name' ),
			'label_text' => $label_text,
			'method'     => 'aria-label',
		);
	}

	/**
	 * Derive label text from context
	 *
	 * @param \DOMElement $input
	 * @return string
	 */
	private function derive_label_text( \DOMElement $input ): string {
		// Check placeholder..
		$placeholder = $input->getAttribute( 'placeholder' );
		if ( ! empty( $placeholder ) ) {
			return $placeholder;
		}

		// Check name attribute..
		$name = $input->getAttribute( 'name' );
		if ( ! empty( $name ) ) {
			$readable = $this->humanize_field_name( $name );
			if ( strlen( $readable ) >= 2 ) {
				return $readable;
			}
		}

		// Check type for common patterns..
		$type = $input->getAttribute( 'type' ) ?: 'text';

		$type_labels = array(
			'email'    => __( 'Email address', 'shahi-legalflowsuite' ),
			'tel'      => __( 'Phone number', 'shahi-legalflowsuite' ),
			'password' => __( 'Password', 'shahi-legalflowsuite' ),
			'search'   => __( 'Search', 'shahi-legalflowsuite' ),
			'url'      => __( 'Website URL', 'shahi-legalflowsuite' ),
			'date'     => __( 'Date', 'shahi-legalflowsuite' ),
			'time'     => __( 'Time', 'shahi-legalflowsuite' ),
			'number'   => __( 'Number', 'shahi-legalflowsuite' ),
			'file'     => __( 'Choose file', 'shahi-legalflowsuite' ),
		);

		if ( isset( $type_labels[ $type ] ) ) {
			return $type_labels[ $type ];
		}

		// Check nearby text..
		$nearby_text = $this->find_nearby_label_text( $input );
		if ( ! empty( $nearby_text ) ) {
			return $nearby_text;
		}

		// For select, check first option..
		if ( $input->nodeName === 'select' ) {
			$options = $input->getElementsByTagName( 'option' );
			if ( $options->length > 0 ) {
				$first_option = $options->item( 0 );
				$first_text   = trim( $first_option->textContent );
				// Common placeholder patterns..
				if ( preg_match( '/^(select|choose|pick)/i', $first_text ) ) {
					return $first_text;
				}
			}
		}

		// Fallback..
		if ( $input->nodeName === 'textarea' ) {
			return __( 'Text input', 'shahi-legalflowsuite' );
		}

		return __( 'Input field', 'shahi-legalflowsuite' );
	}

	/**
	 * Humanize field name
	 *
	 * @param string $name
	 * @return string
	 */
	private function humanize_field_name( string $name ): string {
		// Remove common prefixes..
		$name = preg_replace( '/^(input_|field_|form_|txt_|txt|fld_|fld)/i', '', $name );

		// Remove array notation..
		$name = preg_replace( '/\[\d*\]/', '', $name );

		// Convert separators to spaces..
		$name = str_replace( array( '_', '-', '[', ']' ), ' ', $name );

		// Handle camelCase..
		$name = preg_replace( '/([a-z])([A-Z])/', '$1 $2', $name );

		// Clean up..
		$name = preg_replace( '/\s+/', ' ', trim( $name ) );

		return ucwords( strtolower( $name ) );
	}

	/**
	 * Find nearby text that could be a label
	 *
	 * @param \DOMElement $input
	 * @return string
	 */
	private function find_nearby_label_text( \DOMElement $input ): string {
		$parent = $input->parentNode;

		if ( ! $parent instanceof \DOMElement ) {
			return '';
		}

		// Check previous sibling text..
		$prev = $input->previousSibling;
		while ( $prev ) {
			if ( $prev instanceof \DOMText ) {
				$text = trim( $prev->textContent );
				if ( strlen( $text ) > 2 && strlen( $text ) < 50 ) {
					return rtrim( $text, ':*' );
				}
			} elseif ( $prev instanceof \DOMElement ) {
				// Check for span/strong/em with text..
				if ( in_array( $prev->nodeName, array( 'span', 'strong', 'em', 'b' ) ) ) {
					$text = trim( $prev->textContent );
					if ( strlen( $text ) > 2 && strlen( $text ) < 50 ) {
						return rtrim( $text, ':*' );
					}
				}
				break; // Stop if we hit another element
			}
			$prev = $prev->previousSibling;
		}

		return '';
	}
}
