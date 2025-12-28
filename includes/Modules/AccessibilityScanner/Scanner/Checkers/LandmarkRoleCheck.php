<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LandmarkRoleCheck extends AbstractCheck {

	public function get_id() {
		return 'landmark-role';
	}

	public function get_description() {
		return 'Ensure landmark roles are used correctly and not duplicated without labels.';
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

		$landmarks = array( 'banner', 'complementary', 'contentinfo', 'form', 'main', 'navigation', 'region', 'search' );
		$counts    = array();

		foreach ( $landmarks as $landmark ) {
			$elements            = $xpath->query( "//*[@role='$landmark']" );
			$counts[ $landmark ] = $elements->length;

			if ( $elements->length > 1 ) {
				foreach ( $elements as $element ) {
					if ( ! $element->hasAttribute( 'aria-label' ) && ! $element->hasAttribute( 'aria-labelledby' ) ) {
						$issues[] = array(
							'element' => $element->tagName,
							'context' => $this->get_element_html( $element ),
							'message' => "Multiple '$landmark' landmarks found. Use aria-label or aria-labelledby to distinguish them.",
						);
					}
				}
			}
		}

		// Check for multiple main landmarks (only one allowed usually, unless hidden)
		if ( isset( $counts['main'] ) && $counts['main'] > 1 ) {
			$issues[] = array(
				'element' => 'multiple',
				'context' => 'Multiple role="main" found',
				'message' => 'A document should not have more than one visible main landmark.',
			);
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

