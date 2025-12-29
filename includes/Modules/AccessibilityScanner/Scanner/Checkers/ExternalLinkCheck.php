<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ExternalLinkCheck extends AbstractCheck {

	public function get_id() {
		return 'external-link';
	}

	public function get_description() {
		return 'External links should be identified.';
	}

	public function get_severity() {
		return 'notice';
	}

	public function get_wcag_criteria() {
		return '3.2.4';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		$site_url = get_site_url();
		$host     = parse_url( $site_url, PHP_URL_HOST );

		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );
			if ( empty( $href ) || strpos( $href, '#' ) === 0 || strpos( $href, '/' ) === 0 ) {
				continue;
			}

			$link_host = parse_url( $href, PHP_URL_HOST );

			if ( $link_host && $link_host !== $host ) {
				// It's external. Check if it has indication.
				// Heuristic: check for "external" class, or aria-label containing "external", or icon
				$class = $link->getAttribute( 'class' );
				$aria  = $link->getAttribute( 'aria-label' );
				$text  = $link->textContent;

				$hasIndication = strpos( $class, 'external' ) !== false ||
								strpos( $aria, 'external' ) !== false ||
								strpos( $text, 'external' ) !== false;

				if ( ! $hasIndication ) {
					$issues[] = array(
						'element' => 'a',
						'context' => $this->get_element_html( $link ),
						'message' => 'External link may need visual indication or screen reader text (e.g. "opens in new window" or "external link").',
					);
				}
			}
		}

		return $issues;
	}
}

