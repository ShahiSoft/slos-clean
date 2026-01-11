<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for time limits with user control
 * WCAG 2.2.1 - Timing Adjustable (Level A)
 *
 * @since 3.1.2
 */
class TimingControlCheck extends AbstractCheck {

	/**
	 * Session timeout UI patterns
	 *
	 * @var array
	 */
	private $timeout_patterns = array(
		'session-timeout',
		'timeout-warning',
		'session-expire',
		'session-expiry',
		'inactivity',
		'idle-timeout',
		'auto-logout',
		'session-timer',
	);

	/**
	 * Countdown timer patterns
	 *
	 * @var array
	 */
	private $timer_patterns = array(
		'countdown',
		'timer',
		'time-left',
		'time-remaining',
		'expires-in',
		'expiring',
		'clock',
	);

	/**
	 * Get check ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'timing-control';
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Time limits must be adjustable, extendable, or able to be turned off.';
	}

	/**
	 * Get severity level
	 *
	 * @return string
	 */
	public function get_severity() {
		return 'serious';
	}

	/**
	 * Get WCAG criteria
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '2.2.1';
	}

	/**
	 * Run the check
	 *
	 * @param string $content HTML content to check.
	 * @return array Array of issues found.
	 */
	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check meta refresh tags
		$this->check_meta_refresh( $xpath, $issues );

		// 2. Check for session timeout UI patterns
		$this->check_session_timeout_patterns( $xpath, $issues );

		// 3. Check for countdown timers
		$this->check_countdown_timers( $xpath, $issues );

		// 4. Check for JavaScript timing patterns
		$this->check_script_timing( $dom, $issues );

		// 5. Check form submission timeouts
		$this->check_form_timeouts( $xpath, $issues );

		return $issues;
	}

	/**
	 * Check for meta refresh/redirect tags
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_meta_refresh( $xpath, &$issues ) {
		$metas = $xpath->query( '//meta[@http-equiv="refresh"]' );

		foreach ( $metas as $meta ) {
			$content_attr = $meta->getAttribute( 'content' );

			if ( preg_match( '/^(\d+)/', $content_attr, $matches ) ) {
				$seconds = (int) $matches[1];

				// 0 is immediate redirect (usually acceptable for redirects)
				// > 72000 (20 hours) is considered essentially unlimited
				if ( $seconds > 0 && $seconds < 72000 ) {
					$has_url = stripos( $content_attr, 'url=' ) !== false;

					if ( $has_url ) {
						$message = sprintf(
							'Auto-redirect after %s. Users must be able to extend or disable this time limit.',
							$this->format_duration( $seconds )
						);
					} else {
						$message = sprintf(
							'Auto-refresh every %s. Consider using a manual refresh button instead.',
							$this->format_duration( $seconds )
						);
					}

					$severity = $seconds < 60 ? 'serious' : 'warning';

					$issues[] = array(
						'element'  => 'meta',
						'context'  => $this->get_element_html( $meta ),
						'message'  => $message,
						'severity' => $severity,
					);
				}
			}
		}
	}

	/**
	 * Check for session timeout UI patterns
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_session_timeout_patterns( $xpath, &$issues ) {
		foreach ( $this->timeout_patterns as $pattern ) {
			// Check class and id attributes
			$elements = $xpath->query( "//*[contains(@class, '$pattern') or contains(@id, '$pattern')]" );

			foreach ( $elements as $element ) {
				// Check if there's an extend/continue button nearby
				$has_extend_button = $this->has_extend_mechanism( $element );

				if ( $has_extend_button ) {
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => 'Session timeout dialog detected with extend option. Ensure the warning appears at least 20 seconds before expiry.',
						'severity' => 'notice',
					);
				} else {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => 'Session timeout indicator detected. Ensure users can extend the session before timeout (at least 10x the original limit).',
					);
				}
				break; // One issue per pattern type
			}
		}
	}

	/**
	 * Check if element contains an extend/continue mechanism
	 *
	 * @param \DOMElement $element Element to check.
	 * @return bool True if extend mechanism found.
	 */
	private function has_extend_mechanism( $element ) {
		$html  = strtolower( $element->ownerDocument->saveHTML( $element ) );
		$terms = array( 'extend', 'continue', 'stay', 'keep', 'more time', 'renew' );

		foreach ( $terms as $term ) {
			if ( strpos( $html, $term ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check for countdown timer patterns
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_countdown_timers( $xpath, &$issues ) {
		foreach ( $this->timer_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern') or contains(@id, '$pattern')]" );

			foreach ( $elements as $element ) {
				$has_timer_role = $element->getAttribute( 'role' ) === 'timer';

				if ( ! $has_timer_role ) {
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => 'Countdown timer detected without role="timer". Add this role for assistive technology and ensure users can extend the time limit.',
						'severity' => 'warning',
					);
				} else {
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => 'Countdown timer detected. Ensure users can extend or disable the time limit.',
						'severity' => 'notice',
					);
				}
				break; // One issue per pattern type
			}
		}
	}

	/**
	 * Check for JavaScript timing patterns
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @param array        $issues Issues array by reference.
	 */
	private function check_script_timing( $dom, &$issues ) {
		$scripts = $dom->getElementsByTagName( 'script' );

		foreach ( $scripts as $script ) {
			$js = $script->textContent;

			// Check for setTimeout with redirect/location change
			if ( preg_match( '/setTimeout\s*\([^,]*(?:location|redirect|href|navigate)[^,]*,\s*(\d+)\s*\)/i', $js, $matches ) ) {
				$ms      = (int) $matches[1];
				$seconds = $ms / 1000;

				if ( $seconds > 0 && $seconds < 7200 ) { // Less than 2 hours
					$issues[] = array(
						'element' => 'script',
						'context' => 'JavaScript',
						'message' => sprintf(
							'Timed redirect detected (after %s). Ensure users can extend or disable this time limit.',
							$this->format_duration( $seconds )
						),
					);
				}
			}

			// Check for countdown patterns in JS
			$countdown_patterns = array(
				'/setInterval\s*\([^,]*(?:countdown|timer)/i',
				'/(?:countdown|remaining|expires?)\s*=\s*\d+/i',
				'/\.countdown\s*\(/i',
			);

			foreach ( $countdown_patterns as $regex_pattern ) {
				if ( preg_match( $regex_pattern, $js ) ) {
					$issues[] = array(
						'element'  => 'script',
						'context'  => 'JavaScript countdown',
						'message'  => 'JavaScript countdown/timer detected. Ensure users can pause, extend, or disable the time limit.',
						'severity' => 'warning',
					);
					break;
				}
			}
		}
	}

	/**
	 * Check for forms with potential time limits
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_form_timeouts( $xpath, &$issues ) {
		// Check for quiz/exam forms that might be timed
		$timed_form_patterns = array(
			'quiz',
			'exam',
			'test',
			'assessment',
			'timed-form',
		);

		foreach ( $timed_form_patterns as $pattern ) {
			$forms = $xpath->query( "//form[contains(@class, '$pattern') or contains(@id, '$pattern')]" );

			foreach ( $forms as $form ) {
				$issues[] = array(
					'element'  => 'form',
					'context'  => $this->get_element_html( $form ),
					'message'  => sprintf(
						'Potentially timed form detected (%s). If time-limited, ensure users can request more time (at least 10x the original limit).',
						$pattern
					),
					'severity' => 'notice',
				);
				break;
			}
		}

		// Check for CAPTCHA with time limits
		$captchas = $xpath->query( "//*[contains(@class, 'captcha') or contains(@id, 'captcha')]" );

		foreach ( $captchas as $captcha ) {
			$issues[] = array(
				'element'  => $captcha->tagName,
				'context'  => $this->get_element_html( $captcha ),
				'message'  => 'CAPTCHA detected. If time-limited, ensure users can request more time or provide an alternative verification method.',
				'severity' => 'notice',
			);
			break;
		}
	}

	/**
	 * Format duration in human-readable form
	 *
	 * @param int $seconds Duration in seconds.
	 * @return string Formatted duration.
	 */
	private function format_duration( $seconds ) {
		if ( $seconds < 60 ) {
			return $seconds . ' second' . ( $seconds !== 1 ? 's' : '' );
		} elseif ( $seconds < 3600 ) {
			$minutes = round( $seconds / 60 );
			return $minutes . ' minute' . ( $minutes !== 1 ? 's' : '' );
		} else {
			$hours = round( $seconds / 3600, 1 );
			return $hours . ' hour' . ( $hours !== 1 ? 's' : '' );
		}
	}
}
