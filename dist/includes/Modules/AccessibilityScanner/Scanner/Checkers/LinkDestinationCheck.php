<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LinkDestinationCheck extends AbstractCheck {

	public function get_id() {
		return 'link-destination';
	}

	public function get_description() {
		return 'Links with the same text should go to the same destination.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.4';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		$linkMap = array();

		foreach ( $links as $link ) {
			$text = trim( $link->textContent );
			$href = $link->getAttribute( 'href' );

			if ( empty( $text ) || empty( $href ) ) {
				continue;
			}

			if ( isset( $linkMap[ $text ] ) ) {
				if ( $linkMap[ $text ] !== $href ) {
					$issues[] = array(
						'element' => 'a',
						'context' => $text,
						'message' => "Link text '$text' is used for multiple different destinations. This can be confusing.",
					);
				}
			} else {
				$linkMap[ $text ] = $href;
			}
		}

		return $issues;
	}
}

