<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NewWindowLinkCheck extends AbstractCheck {

	public function get_id() {
		return 'new-window-link';
	}

	public function get_description() {
		return 'Links opening in a new window should warn the user.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '3.2.5';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			if ( $link->hasAttribute( 'target' ) && $link->getAttribute( 'target' ) === '_blank' ) {
				$text = $link->textContent;
				// Check for warning text or aria-label
				$hasWarning = false;

				// Check text content for keywords
				if ( stripos( $text, 'new window' ) !== false || stripos( $text, 'new tab' ) !== false || stripos( $text, 'external' ) !== false ) {
					$hasWarning = true;
				}

				// Check aria-label
				if ( $link->hasAttribute( 'aria-label' ) ) {
					$ariaLabel = $link->getAttribute( 'aria-label' );
					if ( stripos( $ariaLabel, 'new window' ) !== false || stripos( $ariaLabel, 'new tab' ) !== false ) {
						$hasWarning = true;
					}
				}

				// Check for screen reader text span
				// This is a bit heuristic, looking for a child span with "screen-reader-text" or similar class
				// For now, we'll stick to the basic text/aria check to avoid false positives on complex structures

				if ( ! $hasWarning ) {
					$issues[] = array(
						'message' => 'Link opens in a new window (target="_blank") without a warning in text or aria-label.',
						'element' => $dom->saveHTML( $link ),
						'context' => $link->getAttribute( 'href' ),
					);
				}
			}
		}

		return $issues;
	}
}

