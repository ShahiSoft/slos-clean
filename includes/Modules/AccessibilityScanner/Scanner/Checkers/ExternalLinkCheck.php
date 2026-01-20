<?php
/**
 * External Link Check
 *
 * Checks for external links that may need accessibility indicators.
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers
 * @since 3.3.0
 */

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Allow DOM properties like textContent

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External Link Check Class
 *
 * Checks external links for proper accessibility indicators.
 */
class ExternalLinkCheck extends AbstractCheck {

	/**
	 * Get the check ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'external-link';
	}

	/**
	 * Get the check description.
	 *
	 * @return string
	 */
	public function get_description() {
		return 'External links should be identified.';
	}

	/**
	 * Get the check severity.
	 *
	 * @return string
	 */
	public function get_severity() {
		return 'notice';
	}

	/**
	 * Get the WCAG criteria this check addresses.
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '3.2.4';
	}

	/**
	 * Check the content for external link accessibility issues.
	 *
	 * @param string $content The content to check.
	 * @return array
	 */
	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		$site_url = get_site_url();
		$host     = wp_parse_url( $site_url, PHP_URL_HOST );

		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );
			if ( empty( $href ) || strpos( $href, '#' ) === 0 || strpos( $href, '/' ) === 0 ) {
				continue;
			}

			$link_host = wp_parse_url( $href, PHP_URL_HOST );

			if ( $link_host && $link_host !== $host ) {
				// It's external. Check if it has indication...
				// Heuristic: check for "external" class, or aria-label containing "external", or icon..
				$class        = $link->getAttribute( 'class' );
				$aria         = $link->getAttribute( 'aria-label' );
				$text_content = $link->textContent;

				$has_indication = strpos( $class, 'external' ) !== false ||
								strpos( $aria, 'external' ) !== false ||
								strpos( $text_content, 'external' ) !== false;

				if ( ! $has_indication ) {
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
