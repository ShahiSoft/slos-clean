<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ViewportCheck extends AbstractCheck {

	public function get_id() {
		return 'viewport-check';
	}

	public function get_description() {
		return 'Viewport meta tag should allow user scaling.';
	}

	public function get_severity() {
		return 'critical';
	}

	public function get_wcag_criteria() {
		return '1.4.4';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$metas = $xpath->query( '//meta[@name="viewport"]' );

		foreach ( $metas as $meta ) {
			$contentAttr = $meta->getAttribute( 'content' );

			if ( preg_match( '/user-scalable\s*=\s*no/i', $contentAttr ) || preg_match( '/maximum-scale\s*=\s*1/i', $contentAttr ) ) {
				$issues[] = array(
					'element' => 'meta',
					'context' => $this->get_element_html( $meta ),
					'message' => 'Viewport meta tag prevents zooming (user-scalable=no or maximum-scale=1). Users must be able to resize text.',
				);
			}
		}

		return $issues;
	}
}

