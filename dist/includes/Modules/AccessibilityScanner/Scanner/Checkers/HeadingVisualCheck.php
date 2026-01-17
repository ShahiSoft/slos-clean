<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HeadingVisualCheck extends AbstractCheck {

	public function get_id() {
		return 'heading-visual';
	}

	public function get_description() {
		return 'Visual headings should use heading tags (h1-h6), not just styling.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Find elements with class names suggesting headings but are not headings..
		$elements = $xpath->query( '//*[contains(@class, "heading") or contains(@class, "title") or contains(@class, "h1") or contains(@class, "h2") or contains(@class, "h3")]' );

		foreach ( $elements as $element ) {
			// Skip if it is actually a heading..
			if ( preg_match( '/^h[1-6]$/', $element->tagName ) ) {
				continue;
			}

			// Skip if it has role="heading"..
			if ( $element->getAttribute( 'role' ) === 'heading' ) {
				continue;
			}

			$issues[] = array(
				'element' => $element->tagName,
				'context' => $this->get_element_html( $element ),
				'message' => 'Element appears to be a visual heading (based on class) but is not using a heading tag or role.',
			);
		}

		// Check for <p><b>...</b></p> pattern which is often a fake heading..
		$paras = $dom->getElementsByTagName( 'p' );
		foreach ( $paras as $p ) {
			if ( $p->childNodes->length === 1 ) {
				$child = $p->firstChild;
				if ( $child->nodeType === XML_ELEMENT_NODE && ( $child->tagName === 'b' || $child->tagName === 'strong' ) ) {
					// Check if text length is short enough to be a heading (< 100 chars)..
					if ( strlen( trim( $child->textContent ) ) < 100 && strlen( trim( $child->textContent ) ) > 0 ) {
						$issues[] = array(
							'element' => 'p',
							'context' => $this->get_element_html( $p ),
							'message' => 'Bold text in a paragraph may be a visual heading. Consider using a heading tag.',
						);
					}
				}
			}
		}

		return $issues;
	}
}
