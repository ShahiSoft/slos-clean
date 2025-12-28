<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MissingFormLabelCheck extends AbstractCheck {

	public function get_id() {
		return 'missing-form-label';
	}

	public function get_description() {
		return 'Form controls must have labels.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '3.3.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );

		// Collect all labels with 'for' attribute
		$labels      = $dom->getElementsByTagName( 'label' );
		$labelForIds = array();
		foreach ( $labels as $label ) {
			if ( $label->hasAttribute( 'for' ) ) {
				$labelForIds[] = $label->getAttribute( 'for' );
			}
		}

		$inputs    = $dom->getElementsByTagName( 'input' );
		$textareas = $dom->getElementsByTagName( 'textarea' );
		$selects   = $dom->getElementsByTagName( 'select' );

		$elements = array();
		foreach ( $inputs as $el ) {
			$elements[] = $el;
		}
		foreach ( $textareas as $el ) {
			$elements[] = $el;
		}
		foreach ( $selects as $el ) {
			$elements[] = $el;
		}

		foreach ( $elements as $element ) {
			$tagName = $element->tagName;
			$type    = $element->getAttribute( 'type' );

			// Skip inputs that don't need labels
			if ( $tagName === 'input' && in_array( $type, array( 'hidden', 'submit', 'button', 'image', 'reset' ) ) ) {
				continue;
			}

			// Check 1: aria-label or aria-labelledby
			if ( $element->hasAttribute( 'aria-label' ) && trim( $element->getAttribute( 'aria-label' ) ) !== '' ) {
				continue;
			}
			if ( $element->hasAttribute( 'aria-labelledby' ) && trim( $element->getAttribute( 'aria-labelledby' ) ) !== '' ) {
				continue;
			}

			// Check 2: title attribute (fallback)
			if ( $element->hasAttribute( 'title' ) && trim( $element->getAttribute( 'title' ) ) !== '' ) {
				continue;
			}

			// Check 3: Nested inside label
			$parent   = $element->parentNode;
			$isNested = false;
			while ( $parent && $parent instanceof \DOMElement ) {
				if ( $parent->tagName === 'label' ) {
					$isNested = true;
					break;
				}
				$parent = $parent->parentNode;
			}
			if ( $isNested ) {
				continue;
			}

			// Check 4: Label with 'for' attribute matching ID
			if ( $element->hasAttribute( 'id' ) ) {
				$id = $element->getAttribute( 'id' );
				if ( in_array( $id, $labelForIds ) ) {
					continue;
				}
			}

			$issues[] = array(
				'message' => "Form control <$tagName> is missing a label.",
				'element' => $dom->saveHTML( $element ),
				'context' => $element->getAttribute( 'name' ) ?: $element->getAttribute( 'id' ),
			);
		}

		return $issues;
	}
}

