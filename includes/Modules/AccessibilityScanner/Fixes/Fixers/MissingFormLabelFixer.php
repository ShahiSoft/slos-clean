<?php
/**
 * Missing Form Label Fixer
 *
 * Associates labels with form inputs.
 * WCAG 1.3.1 Info and Relationships (Level A), 3.3.2 Labels or Instructions (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MissingFormLabelFixer Class
 *
 * Associates labels with form inputs.
 */
class MissingFormLabelFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'form-label';
	}

	/**
	 * Get fixer name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Form Labels';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Associate labels with form inputs';
	}

	/**
	 * Fix form labels in content
	 *
	 * @param string $content Post content
	 * @return array Fix result with content and count
	 */
	public function fix( $content ) {
		$dom           = $this->get_dom( $content );
		$xpath         = new \DOMXPath( $dom );
		$fixes_applied = 0;

		// Find form inputs without labels or aria-label..
		$inputs = $xpath->query( '//input[@type!="hidden" and @type!="submit" and @type!="button" and @type!="reset"][not(@aria-label) and not(@aria-labelledby)]' );

		foreach ( $inputs as $input ) {
			// Check if there's already a label for this input..
			$input_id = $input->getAttribute( 'id' );
			if ( ! empty( $input_id ) ) {
				$existing_label = $xpath->query( "//label[@for='$input_id']" );
				if ( $existing_label->length > 0 ) {
					continue;
				}
			}

			// Generate label from context..
			$label_text = $this->generate_label_text( $input, $xpath );

			if ( ! empty( $label_text ) ) {
				$input->setAttribute( 'aria-label', $label_text );
				++$fixes_applied;
			}
		}

		return $this->return_result( $this->dom_to_html( $dom ), $fixes_applied );
	}

	/**
	 * Generate label text from input context
	 *
	 * @param \DOMElement $input Input element
	 * @param \DOMXPath   $xpath XPath object
	 * @return string Generated label text
	 */
	private function generate_label_text( $input, $xpath ) {
		// Check for placeholder..
		if ( $input->hasAttribute( 'placeholder' ) ) {
			return trim( $input->getAttribute( 'placeholder' ) );
		}

		// Check for name attribute..
		if ( $input->hasAttribute( 'name' ) ) {
			$name = $input->getAttribute( 'name' );
			return ucwords( str_replace( array( '_', '-' ), ' ', $name ) );
		}

		// Default based on input type..
		$type = $input->getAttribute( 'type' );
		return ucfirst( $type ? $type : 'Input' );
	}
}
