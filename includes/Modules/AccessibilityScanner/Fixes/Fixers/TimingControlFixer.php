<?php
/**
 * Timing Control Fixer
 *
 * Adds controls for time-limited content.
 * WCAG 2.2.1 Timing Adjustable (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TimingControlFixer Class
 *
 * Handles meta refresh tags, countdown timers, session timeouts,
 * and auto-dismissing notifications by adding user controls.
 */
class TimingControlFixer extends BaseFixer {

	/**
	 * Maximum seconds before we consider the refresh/timeout problematic.
	 * 20 hours in seconds (beyond this is likely intentional daily refresh).
	 *
	 * @var int
	 */
	private const MAX_PROBLEMATIC_SECONDS = 72000;

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'timing-control';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds controls for time-limited content';
	}

	/**
	 * Apply timing control fixes to content
	 *
	 * @param string $content HTML content to fix.
	 * @return array{fixed_count: int, content: string}
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixed = 0;

		// Fix auto-refresh meta tags.
		$fixed += $this->fix_meta_refresh( $dom );

		// Fix countdown timers.
		$fixed += $this->fix_countdown_timers( $dom, $xpath );

		// Fix session timeout warnings.
		$fixed += $this->fix_session_warnings( $dom, $xpath );

		// Fix auto-advancing content (wizards, steps).
		$fixed += $this->fix_auto_advance( $dom, $xpath );

		// Fix auto-dismissing notifications.
		$fixed += $this->fix_auto_dismiss( $dom, $xpath );

		// Inject timing control styles if any fixes were made.
		if ( $fixed > 0 ) {
			$this->inject_timing_styles( $dom );
		}

		return array(
			'fixed_count' => $fixed,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix auto-refresh meta tags
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @return int Number of fixes.
	 */
	private function fix_meta_refresh( $dom ) {
		$fixed = 0;
		$metas = $dom->getElementsByTagName( 'meta' );

		foreach ( $metas as $meta ) {
			$http_equiv = strtolower( $meta->getAttribute( 'http-equiv' ) );
			if ( 'refresh' !== $http_equiv ) {
				continue;
			}

			$content = $meta->getAttribute( 'content' );

			// Extract time and optional URL.
			if ( preg_match( '/^(\d+)(?:\s*;\s*url=(.+))?/i', $content, $matches ) ) {
				$seconds = (int) $matches[1];
				$url     = isset( $matches[2] ) ? $matches[2] : null;

				// Skip if time is 0 (immediate redirect) or very long.
				if ( 0 === $seconds || $seconds >= self::MAX_PROBLEMATIC_SECONDS ) {
					continue;
				}

				// Create warning element.
				$warning = $dom->createElement( 'div' );
				$warning->setAttribute( 'class', 'slos-timing-warning' );
				$warning->setAttribute( 'role', 'alert' );
				$warning->setAttribute( 'aria-live', 'polite' );
				$warning->setAttribute( 'data-slos-timing-control', 'true' );
				$warning->setAttribute( 'data-slos-refresh-seconds', (string) $seconds );
				if ( $url ) {
					$warning->setAttribute( 'data-slos-refresh-url', $url );
				}

				// Create warning text.
				$action  = $url ? 'redirect to another page' : 'refresh';
				$message = sprintf(
					'This page will %s in %d seconds. ',
					$action,
					$seconds
				);

				$text = $dom->createTextNode( $message );
				$warning->appendChild( $text );

				// Add extend time button.
				$extend_btn = $dom->createElement( 'button' );
				$extend_btn->setAttribute( 'type', 'button' );
				$extend_btn->setAttribute( 'class', 'slos-extend-time' );
				$extend_btn->setAttribute( 'data-slos-extend-seconds', '300' );
				$extend_btn->textContent = 'Extend time by 5 minutes';
				$warning->appendChild( $extend_btn );

				// Add cancel button.
				$cancel_btn = $dom->createElement( 'button' );
				$cancel_btn->setAttribute( 'type', 'button' );
				$cancel_btn->setAttribute( 'class', 'slos-cancel-refresh' );
				$cancel_btn->textContent = 'Cancel ' . ( $url ? 'redirect' : 'refresh' );
				$warning->appendChild( $cancel_btn );

				// Insert at top of body.
				$body = $dom->getElementsByTagName( 'body' )->item( 0 );
				if ( $body && $body->firstChild ) {
					$body->insertBefore( $warning, $body->firstChild );
					++$fixed;
				}
			}
		}

		return $fixed;
	}

	/**
	 * Fix countdown timers
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_countdown_timers( $dom, $xpath ) {
		$fixed = 0;

		// Find countdown elements by class or ID patterns.
		$timers = $xpath->query(
			'//*[contains(@class, "countdown") or contains(@class, "timer") or ' .
			'contains(@id, "countdown") or contains(@id, "timer")]' .
			'[not(@data-slos-timing-control)]'
		);

		foreach ( $timers as $timer ) {
			$timer->setAttribute( 'data-slos-timing-control', 'true' );
			$timer->setAttribute( 'role', 'timer' );
			$timer->setAttribute( 'aria-live', 'polite' );
			$timer->setAttribute( 'aria-atomic', 'true' );

			// Add control buttons if not present.
			$this->add_timer_controls( $dom, $timer );

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix session timeout warnings
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_session_warnings( $dom, $xpath ) {
		$fixed = 0;

		// Find session timeout elements.
		$sessions = $xpath->query(
			'//*[contains(@class, "session") and (contains(@class, "timeout") or ' .
			'contains(@class, "warning") or contains(@class, "expir"))]' .
			'[not(@data-slos-timing-control)] | ' .
			'//*[contains(@id, "session") and (contains(@id, "timeout") or ' .
			'contains(@id, "warning") or contains(@id, "expir"))]' .
			'[not(@data-slos-timing-control)]'
		);

		foreach ( $sessions as $session ) {
			$session->setAttribute( 'data-slos-timing-control', 'true' );
			$session->setAttribute( 'role', 'alertdialog' );
			$session->setAttribute( 'aria-modal', 'true' );

			// Generate ID for aria-labelledby if needed.
			$title_id = 'slos-session-warning-title-' . uniqid();

			// Check if there's a heading or title.
			$heading = $xpath->query( './/h1|.//h2|.//h3|.//h4|.//h5|.//h6|.//*[contains(@class, "title")]', $session )->item( 0 );
			if ( $heading ) {
				if ( ! $heading->hasAttribute( 'id' ) ) {
					$heading->setAttribute( 'id', $title_id );
				} else {
					$title_id = $heading->getAttribute( 'id' );
				}
				$session->setAttribute( 'aria-labelledby', $title_id );
			}

			// Ensure there's an extend option.
			$extend = $xpath->query(
				'.//*[contains(@class, "extend") or contains(text(), "extend") or ' .
				'contains(text(), "Extend") or contains(@class, "renew")]',
				$session
			);

			if ( 0 === $extend->length ) {
				$button = $dom->createElement( 'button' );
				$button->setAttribute( 'type', 'button' );
				$button->setAttribute( 'class', 'slos-extend-session' );
				$button->textContent = 'Extend Session';
				$session->appendChild( $button );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix auto-advancing content like wizards and step forms
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_auto_advance( $dom, $xpath ) {
		$fixed = 0;

		// Find auto-advancing wizards/steps.
		$wizards = $xpath->query(
			'//*[@data-auto-advance or @data-autoadvance or ' .
			'(contains(@class, "wizard") and contains(@class, "auto")) or ' .
			'(contains(@class, "stepper") and contains(@class, "auto"))]' .
			'[not(@data-slos-timing-control)]'
		);

		foreach ( $wizards as $wizard ) {
			$wizard->setAttribute( 'data-slos-timing-control', 'true' );

			// Add manual advance controls.
			$controls = $dom->createElement( 'div' );
			$controls->setAttribute( 'class', 'slos-timing-controls' );
			$controls->setAttribute( 'role', 'group' );
			$controls->setAttribute( 'aria-label', 'Timing controls' );

			$pause = $dom->createElement( 'button' );
			$pause->setAttribute( 'type', 'button' );
			$pause->setAttribute( 'class', 'slos-pause-advance' );
			$pause->setAttribute( 'aria-pressed', 'false' );
			$pause->textContent = '⏸ Pause auto-advance';
			$controls->appendChild( $pause );

			$wizard->insertBefore( $controls, $wizard->firstChild );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix auto-dismissing notifications
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_auto_dismiss( $dom, $xpath ) {
		$fixed = 0;

		// Find auto-dismissing notifications.
		$notifications = $xpath->query(
			'//*[@data-auto-dismiss or @data-timeout or @data-dismiss-after or ' .
			'@data-autodismiss or @data-auto-close or @data-autoclose]' .
			'[not(@data-slos-timing-control)]'
		);

		foreach ( $notifications as $notification ) {
			$notification->setAttribute( 'data-slos-timing-control', 'true' );

			// Add aria-live if not present.
			if ( ! $notification->hasAttribute( 'aria-live' ) ) {
				$notification->setAttribute( 'aria-live', 'polite' );
			}

			// Check for close button.
			$close = $xpath->query(
				'.//*[contains(@class, "close") or @aria-label="Close" or ' .
				'contains(@class, "dismiss")]',
				$notification
			);

			if ( 0 === $close->length ) {
				// Add close button.
				$close_btn = $dom->createElement( 'button' );
				$close_btn->setAttribute( 'type', 'button' );
				$close_btn->setAttribute( 'class', 'slos-notification-close' );
				$close_btn->setAttribute( 'aria-label', 'Close notification' );
				$close_btn->textContent = '×';
				$notification->appendChild( $close_btn );
			}

			// Add "keep visible" option.
			$keep_btn = $dom->createElement( 'button' );
			$keep_btn->setAttribute( 'type', 'button' );
			$keep_btn->setAttribute( 'class', 'slos-keep-visible' );
			$keep_btn->textContent = 'Keep visible';
			$notification->appendChild( $keep_btn );

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Add timer controls to a timer element
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMElement  $timer Timer element.
	 * @return void
	 */
	private function add_timer_controls( $dom, $timer ) {
		$controls = $dom->createElement( 'div' );
		$controls->setAttribute( 'class', 'slos-timer-controls' );
		$controls->setAttribute( 'role', 'group' );
		$controls->setAttribute( 'aria-label', 'Timer controls' );

		// Pause button.
		$pause = $dom->createElement( 'button' );
		$pause->setAttribute( 'type', 'button' );
		$pause->setAttribute( 'class', 'slos-timer-pause' );
		$pause->setAttribute( 'aria-pressed', 'false' );
		$pause->textContent = '⏸ Pause';
		$controls->appendChild( $pause );

		// Add time button.
		$add = $dom->createElement( 'button' );
		$add->setAttribute( 'type', 'button' );
		$add->setAttribute( 'class', 'slos-timer-add' );
		$add->setAttribute( 'data-slos-add-seconds', '60' );
		$add->textContent = '+1 min';
		$controls->appendChild( $add );

		// Add more time button.
		$add_more = $dom->createElement( 'button' );
		$add_more->setAttribute( 'type', 'button' );
		$add_more->setAttribute( 'class', 'slos-timer-add' );
		$add_more->setAttribute( 'data-slos-add-seconds', '300' );
		$add_more->textContent = '+5 min';
		$controls->appendChild( $add_more );

		$parent = $timer->parentNode;
		if ( $parent ) {
			$parent->insertBefore( $controls, $timer->nextSibling );
		}
	}

	/**
	 * Inject CSS styles for timing controls
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @return void
	 */
	private function inject_timing_styles( $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-timing-styles]' );

		if ( $existing->length > 0 ) {
			return;
		}

		$style = $dom->createElement( 'style' );
		$style->setAttribute( 'data-slos-timing-styles', 'true' );
		$style->textContent = '
/* SLOS Timing Control Styles */
.slos-timing-warning {
	position: relative;
	padding: 16px 20px;
	margin: 0 0 16px;
	background: #fff3cd;
	border: 1px solid #ffc107;
	border-left: 4px solid #ffc107;
	border-radius: 4px;
	font-size: 14px;
	line-height: 1.5;
}

.slos-timing-warning button {
	margin-left: 12px;
	padding: 8px 16px;
	min-height: 44px;
	background: #0073aa;
	color: white;
	border: none;
	border-radius: 4px;
	cursor: pointer;
	font-size: 14px;
}

.slos-timing-warning button:hover {
	background: #005a87;
}

.slos-timing-warning button:focus {
	outline: 2px solid #005fcc;
	outline-offset: 2px;
}

.slos-timing-warning .slos-cancel-refresh {
	background: #dc3545;
}

.slos-timing-warning .slos-cancel-refresh:hover {
	background: #c82333;
}

.slos-timer-controls,
.slos-timing-controls {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
	margin: 8px 0;
}

.slos-timer-controls button,
.slos-timing-controls button {
	padding: 8px 16px;
	min-width: 44px;
	min-height: 44px;
	background: #f0f0f0;
	border: 1px solid #ccc;
	border-radius: 4px;
	cursor: pointer;
	font-size: 14px;
}

.slos-timer-controls button:hover,
.slos-timing-controls button:hover {
	background: #e0e0e0;
}

.slos-timer-controls button:focus,
.slos-timing-controls button:focus {
	outline: 2px solid #005fcc;
	outline-offset: 2px;
}

.slos-timer-pause[aria-pressed="true"],
.slos-pause-advance[aria-pressed="true"] {
	background: #4caf50;
	color: white;
}

.slos-notification-close {
	position: absolute;
	top: 8px;
	right: 8px;
	padding: 4px 8px;
	min-width: 44px;
	min-height: 44px;
	background: transparent;
	border: none;
	font-size: 20px;
	cursor: pointer;
	line-height: 1;
}

.slos-notification-close:hover {
	background: rgba(0, 0, 0, 0.1);
}

.slos-notification-close:focus {
	outline: 2px solid #005fcc;
	outline-offset: 2px;
}

.slos-keep-visible {
	margin-top: 8px;
	padding: 6px 12px;
	min-height: 44px;
	background: transparent;
	border: 1px solid currentColor;
	border-radius: 4px;
	cursor: pointer;
	font-size: 12px;
}

.slos-keep-visible:hover {
	background: rgba(0, 0, 0, 0.05);
}

.slos-extend-session {
	padding: 10px 20px;
	min-height: 44px;
	background: #28a745;
	color: white;
	border: none;
	border-radius: 4px;
	cursor: pointer;
	font-size: 16px;
}

.slos-extend-session:hover {
	background: #218838;
}

.slos-extend-session:focus {
	outline: 2px solid #005fcc;
	outline-offset: 2px;
}

/* High contrast mode */
@media (prefers-contrast: high) {
	.slos-timing-warning {
		border: 2px solid currentColor;
	}
	
	.slos-timer-controls button,
	.slos-timing-controls button {
		border: 2px solid currentColor;
	}
}
';

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$head->appendChild( $style );
		}
	}
}
