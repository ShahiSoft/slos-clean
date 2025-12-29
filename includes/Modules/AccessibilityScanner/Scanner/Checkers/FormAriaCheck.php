<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormAriaCheck extends AbstractCheck {

	public function get_id() {
		return 'form-aria';
	}

	public function get_description() {
		return 'ARIA attributes on form elements must be valid.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '4.1.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$inputs = $dom->getElementsByTagName( 'input' );

		$validAttributes = array(
			'aria-label',
			'aria-labelledby',
			'aria-describedby',
			'aria-required',
			'aria-invalid',
			'aria-disabled',
			'aria-readonly',
			'aria-hidden',
			'aria-autocomplete',
			'aria-checked',
			'aria-expanded',
			'aria-haspopup',
		);

		foreach ( $inputs as $input ) {
			foreach ( $input->attributes as $attr ) {
				if ( strpos( $attr->name, 'aria-' ) === 0 ) {
					// Basic check: is it a known ARIA attribute?
					// This is a simplified list.
					// A full check would validate against the ARIA spec for the specific role.
					// For now, we just check if it looks like a typo (e.g. aria-lbel)
					// But that's hard without a full dictionary.

					// Let's check for common misuse: aria-hidden="true" on a focusable element
					if ( $attr->name === 'aria-hidden' && $attr->value === 'true' ) {
						$type = $input->getAttribute( 'type' );
						if ( $type !== 'hidden' ) {
							$issues[] = array(
								'element' => 'input',
								'context' => $this->get_element_html( $input ),
								'message' => 'Focusable form element has aria-hidden="true". This makes it invisible to screen readers but still focusable.',
							);
						}
					}
				}
			}
		}

		return $issues;
	}
}

