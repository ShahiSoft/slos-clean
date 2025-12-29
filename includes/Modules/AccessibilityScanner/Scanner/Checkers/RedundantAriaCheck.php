<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RedundantAriaCheck extends AbstractCheck {

	public function get_id() {
		return 'redundant-aria';
	}

	public function get_description() {
		return 'Avoid using ARIA roles that are implicit for the element.';
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
		$xpath  = new \DOMXPath( $dom );

		$redundant = array(
			'button'  => 'button',
			'header'  => 'banner',
			'footer'  => 'contentinfo',
			'main'    => 'main',
			'nav'     => 'navigation',
			'aside'   => 'complementary',
			'article' => 'article',
			'section' => 'region', // Only if it has a label, but simple check here
			'form'    => 'form',
			'img'     => 'img',
			'table'   => 'table',
		);

		foreach ( $redundant as $tag => $role ) {
			$elements = $xpath->query( '//' . $tag . "[@role='" . $role . "']" );
			foreach ( $elements as $element ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => "The role '$role' is redundant on the <$tag> element as it is the implicit role.",
				);
			}
		}

		return $issues;
	}
}

