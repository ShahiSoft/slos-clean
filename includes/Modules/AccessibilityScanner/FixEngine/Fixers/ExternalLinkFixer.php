<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExternalLinkFixer extends AbstractFixer {

	public function get_id(): string {
		return 'external-link';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom         = $this->parse_html( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;

		// Get home URL safely (replicate legacy behavior).
		$home_url  = function_exists( 'home_url' ) ? home_url() : ( isset( $_SERVER['HTTP_HOST'] ) ? '//' . $_SERVER['HTTP_HOST'] : '' );
		$home_host = parse_url( $home_url, PHP_URL_HOST ) ?: '';

		$links_array = [];
		foreach ( $links as $link ) {
			$links_array[] = $link;
		}

		foreach ( $links_array as $link ) {
			$href = $link->getAttribute( 'href' );

			// Skip empty hrefs
			if ( empty( $href ) ) {
				continue;
			}

			// Skip internal links (relative, anchors, mailto, tel, javascript)
			if ( preg_match( '/^(\/(?!\/)|#|mailto:|tel:|javascript:)/i', $href ) ) {
				continue;
			}

			$is_external = false;
			if ( preg_match( '/^https?:\/\//i', $href ) ) {
				$link_host = parse_url( $href, PHP_URL_HOST ) ?: '';
				if ( $link_host && $link_host !== $home_host ) {
					$is_external = true;
				}
			}

			if ( $is_external ) {
				$text = trim( $link->textContent );
				// Check if already marked as external
				if ( strpos( $text, '(external' ) === false &&
				     strpos( $text, '(opens' ) === false &&
				     ! $link->hasAttribute( 'aria-label' ) ) {
					$link->setAttribute( 'aria-label', $text . ' (external link, opens in new window)' );
					++$fixed_count;
				}
			}
		}

		$fixed_content = $this->get_html();

		if ( $fixed_count <= 0 || $fixed_content === '' || $fixed_content === $content ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			[]
		);
	}
}
