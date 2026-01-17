<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KeyboardTrapFixer extends AbstractFixer {

	public function get_id(): string {
		return 'keyboard-trap';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom         = $this->parse_html( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		$traps = array();

		// Modals/Dialogs...
		$modals = $xpath->query(
			'//*[@role="dialog" or @role="alertdialog" or ' .
			'contains(@class, "modal") or contains(@class, "popup") or ' .
			'contains(@class, "lightbox") or contains(@class, "overlay")]'
		);
		foreach ( $modals as $modal ) {
			$traps[] = $modal;
		}

		// iframes (can trap focus)...
		$iframes = $xpath->query( '//iframe[not(@tabindex="-1")]' );
		foreach ( $iframes as $iframe ) {
			$traps[] = $iframe;
		}

		// Embedded content containers...
		$embeds = $xpath->query(
			'//*[contains(@class, "embed") or contains(@class, "video-container") or ' .
			'contains(@class, "player")]'
		);
		foreach ( $embeds as $embed ) {
			$traps[] = $embed;
		}

		foreach ( $traps as $trap ) {
			// Mark trap with data attribute if not already marked...
			if ( ! $trap->hasAttribute( 'data-slos-keyboard-trap' ) ) {
				$trap->setAttribute( 'data-slos-keyboard-trap', 'true' );
				++$fixed_count;
			}
		}

		// Inject escape script container if fixes were made...
		if ( $fixed_count > 0 ) {
			$body = $dom->getElementsByTagName( 'body' )->item( 0 );
			if ( $body ) {
				$script = $dom->createElement( 'script' );
				$script->setAttribute( 'type', 'text/javascript' );
				$script->setAttribute( 'data-slos-keyboard-trap-script', 'true' );
				$script->appendChild( $dom->createTextNode( '' ) );
				$body->appendChild( $script );
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
			array()
		);
	}
}
