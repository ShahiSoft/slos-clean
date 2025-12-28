<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InputTypeCheck extends AbstractCheck {

	public function get_id() {
		return 'input-type';
	}

	public function get_description() {
		return 'Input types must be valid.';
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

		$validTypes = array(
			'button',
			'checkbox',
			'color',
			'date',
			'datetime-local',
			'email',
			'file',
			'hidden',
			'image',
			'month',
			'number',
			'password',
			'radio',
			'range',
			'reset',
			'search',
			'submit',
			'tel',
			'text',
			'time',
			'url',
			'week',
		);

		foreach ( $inputs as $input ) {
			if ( $input->hasAttribute( 'type' ) ) {
				$type = strtolower( $input->getAttribute( 'type' ) );
				if ( ! in_array( $type, $validTypes ) ) {
					$issues[] = array(
						'element' => 'input',
						'context' => $this->get_element_html( $input ),
						'message' => "Invalid input type '$type'.",
					);
				}
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

