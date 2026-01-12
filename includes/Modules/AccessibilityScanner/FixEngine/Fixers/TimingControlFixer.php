<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TimingControlFixer extends AbstractFixer {

	public function get_id(): string {
		return 'timing-control';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		// Inline the core behavior from the legacy TimingControlFixer...
		$dom   = $this->parse_html( $content );
		$xpath = new \DOMXPath( $dom );
		$fixed = 0;

		// For now, approximate the legacy behavior by focusing on..
		// meta refresh tags; additional patterns can be ported later..
		// without changing the external FixEngine API...
		$metas = $dom->getElementsByTagName( 'meta' );
		foreach ( $metas as $meta ) {
			$http_equiv = strtolower( $meta->getAttribute( 'http-equiv' ) );
			if ( 'refresh' !== $http_equiv ) {
				continue;
			}

			$content_attr = $meta->getAttribute( 'content' );
			if ( preg_match( '/^(\d+)(?:\s*;\s*url=(.+))?/i', $content_attr, $matches ) ) {
				$seconds = (int) $matches[1];
				$url     = isset( $matches[2] ) ? $matches[2] : null;

				if ( 0 === $seconds ) {
					continue;
				}

				$warning = $dom->createElement( 'div' );
				$warning->setAttribute( 'class', 'slos-timing-warning' );
				$warning->setAttribute( 'role', 'alert' );
				$warning->setAttribute( 'aria-live', 'polite' );
				$warning->setAttribute( 'data-slos-timing-control', 'true' );
				$warning->setAttribute( 'data-slos-refresh-seconds', (string) $seconds );
				if ( $url ) {
					$warning->setAttribute( 'data-slos-refresh-url', $url );
				}

				$action  = $url ? 'redirect to another page' : 'refresh';
				$message = sprintf(
					'This page will %s in %d seconds. ',
					$action,
					$seconds
				);

				$text = $dom->createTextNode( $message );
				$warning->appendChild( $text );

				$extend_btn = $dom->createElement( 'button' );
				$extend_btn->setAttribute( 'type', 'button' );
				$extend_btn->setAttribute( 'class', 'slos-timing-extend' );
				$extend_btn->appendChild( $dom->createTextNode( 'Stay on this page' ) );
				$warning->appendChild( $extend_btn );

				$body = $dom->getElementsByTagName( 'body' )->item( 0 );
				if ( $body ) {
					$body->insertBefore( $warning, $body->firstChild );
					++$fixed;
				}
			}
		}

		$fixed_content = $this->get_html();

		if ( $fixed <= 0 || $fixed_content === '' || $fixed_content === $content ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixed,
			$content,
			$fixed_content,
			array()
		);
	}
}
