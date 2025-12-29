<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ButtonLabelCheck extends AbstractCheck {

	public function get_id() {
		return 'button-label';
	}

	public function get_description() {
		return 'Buttons must have discernible text.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '4.1.2';
	}

	public function check( $content ) {
		$issues  = array();
		$buttons = $this->get_elements( $content, 'button' );

		foreach ( $buttons as $button ) {
			$text            = trim( $button->textContent );
			$aria_label      = $button->getAttribute( 'aria-label' );
			$aria_labelledby = $button->getAttribute( 'aria-labelledby' );

			if ( empty( $text ) && empty( $aria_label ) && empty( $aria_labelledby ) ) {
				$issues[] = array(
					'element' => 'button',
					'context' => $this->get_element_html( $button ),
					'message' => 'Button is empty and has no aria-label.',
				);
			}
		}

		return $issues;
	}

	protected function get_element_html( $node, $max_length = 200 ) {
		$html = $node->ownerDocument->saveHTML( $node );
		return strlen( $html ) > $max_length ? substr( $html, 0, $max_length ) . '...' : $html;
	}
}

