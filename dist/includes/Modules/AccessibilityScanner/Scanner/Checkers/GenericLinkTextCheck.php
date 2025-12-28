<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GenericLinkTextCheck extends AbstractCheck {

	private $genericPhrases = array(
		'click here',
		'read more',
		'learn more',
		'more',
		'here',
		'link',
		'go',
		'continue reading',
	);

	public function get_id() {
		return 'generic-link-text';
	}

	public function get_description() {
		return 'Links should have descriptive text, avoiding generic phrases like "click here".';
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

		foreach ( $links as $link ) {
			$text      = trim( $link->textContent );
			$cleanText = strtolower( preg_replace( '/\s+/', ' ', $text ) );

			if ( in_array( $cleanText, $this->genericPhrases ) ) {
				// Check if aria-label provides more context
				if ( $link->hasAttribute( 'aria-label' ) && trim( $link->getAttribute( 'aria-label' ) ) !== '' ) {
					continue;
				}

				$issues[] = array(
					'message' => "Generic link text found: \"$text\".",
					'element' => $dom->saveHTML( $link ),
					'context' => $link->getAttribute( 'href' ),
				);
			}
		}

		return $issues;
	}
}

