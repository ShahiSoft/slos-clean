<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SemanticHtmlCheck extends AbstractCheck {

	public function get_id() {
		return 'semantic-html';
	}

	public function get_description() {
		return 'Use semantic HTML elements instead of generic divs/spans where appropriate.';
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

		// Check for divs with onclick (should be button) - Covered by InteractiveElementCheck
		// Check for divs with class "heading" or "title" (should be h1-h6)

		$elements = $xpath->query( '//div[contains(@class, "heading") or contains(@class, "title") or contains(@class, "header")]' );

		foreach ( $elements as $element ) {
			// If it's not a heading tag
			if ( ! preg_match( '/^h[1-6]$/', $element->tagName ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'Element appears to be a heading (based on class name) but uses a generic <div>. Use semantic heading tags (<h1>-<h6>) instead.',
				);
			}
		}

		// Check for divs with class "btn" or "button"
		$buttons = $xpath->query( '//div[contains(@class, "btn") or contains(@class, "button")] | //span[contains(@class, "btn") or contains(@class, "button")]' );

		foreach ( $buttons as $button ) {
			if ( $button->tagName !== 'button' && $button->tagName !== 'a' && $button->getAttribute( 'role' ) !== 'button' ) {
				$issues[] = array(
					'element' => $button->tagName,
					'context' => $this->get_element_html( $button ),
					'message' => 'Element appears to be a button (based on class name) but uses a generic tag. Use <button> or role="button".',
				);
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

