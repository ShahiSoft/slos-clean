<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StatusMessageFixer extends AbstractFixer {

	/**
	 * Patterns indicating status/notification types by keyword.
	 *
	 * @var array<string, array<string>>
	 */
	private const STATUS_PATTERNS = [
		'success'  => [ 'success', 'completed', 'saved', 'done', 'confirmed', 'approved', 'passed', 'accepted' ],
		'error'    => [ 'error', 'fail', 'invalid', 'problem', 'issue', 'wrong', 'denied', 'rejected' ],
		'warning'  => [ 'warning', 'caution', 'attention', 'notice', 'careful', 'alert' ],
		'info'     => [ 'info', 'information', 'note', 'tip', 'update', 'hint', 'help' ],
		'loading'  => [ 'loading', 'processing', 'please wait', 'working', 'submitting' ],
		'progress' => [ 'progress', 'step', 'stage', 'uploading', 'downloading', 'syncing' ],
	];

	/**
	 * Class patterns that indicate status elements.
	 *
	 * @var array<string>
	 */
	private const STATUS_CLASSES = [
		'alert',
		'notice',
		'message',
		'notification',
		'toast',
		'flash',
		'status',
		'banner',
		'feedback',
		'snackbar',
		'callout',
		'announcement',
		'info-box',
		'message-box',
		'woocommerce-message',
		'woocommerce-error',
		'woocommerce-info',
		'wp-notice',
	];

	public function get_id(): string {
		return 'status-message';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		$status_elements = $this->find_status_elements( $xpath );
		foreach ( $status_elements as $element ) {
			if ( $this->fix_status_element( $element ) ) {
				++$fixed_count;
			}
		}

		$fixed_count += $this->fix_form_messages( $xpath );
		$fixed_count += $this->fix_loading_indicators( $dom, $xpath );
		$fixed_count += $this->fix_result_counters( $xpath );
		$fixed_count += $this->fix_cart_messages( $xpath );

		if ( $fixed_count > 0 ) {
			$this->ensure_live_region_exists( $dom );
		}

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No status-message fixes applied', $content );
		}

		$fixed_content = $this->get_html( $dom );

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			[]
		);
	}

	/**
	 * Find elements that appear to be status messages.
	 *
	 * @return array<\DOMElement>
	 */
	private function find_status_elements( \DOMXPath $xpath ): array {
		$elements = [];

		$class_conditions = [];
		foreach ( self::STATUS_CLASSES as $class ) {
			$class_conditions[] = "contains(@class, '{$class}')";
		}

		$query = '//*[(' . implode( ' or ', $class_conditions ) . ') and not(@role) and not(@aria-live)]';
		$nodes = $xpath->query( $query );

		if ( $nodes instanceof \DOMNodeList ) {
			foreach ( $nodes as $node ) {
				if ( $node instanceof \DOMElement ) {
					$elements[] = $node;
				}
			}
		}

		return $elements;
	}

	/**
	 * Fix a single status element.
	 */
	private function fix_status_element( \DOMElement $element ): bool {
		$class = strtolower( $element->getAttribute( 'class' ) );
		$text  = strtolower( $element->textContent );

		$type = $this->determine_status_type( $class, $text );

		if ( ! $type ) {
			return false;
		}

		switch ( $type ) {
			case 'error':
				$element->setAttribute( 'role', 'alert' );
				$element->setAttribute( 'aria-live', 'assertive' );
				break;

			case 'success':
			case 'info':
			case 'warning':
				$element->setAttribute( 'role', 'status' );
				$element->setAttribute( 'aria-live', 'polite' );
				break;

			case 'loading':
			case 'progress':
				$element->setAttribute( 'role', 'status' );
				$element->setAttribute( 'aria-live', 'polite' );
				$element->setAttribute( 'aria-busy', 'true' );
				break;
		}

		return true;
	}

	/**
	 * Determine the type of status message.
	 */
	private function determine_status_type( string $class, string $text ): ?string {
		foreach ( self::STATUS_PATTERNS as $type => $patterns ) {
			foreach ( $patterns as $pattern ) {
				if ( stripos( $class, $pattern ) !== false || stripos( $text, $pattern ) !== false ) {
					return $type;
				}
			}
		}

		if ( preg_match( '/\b(alert|notice|message)-(success|error|warning|info|danger|primary|secondary)\b/', $class, $m ) ) {
			if ( 'danger' === $m[2] ) {
				return 'error';
			}
			if ( in_array( $m[2], [ 'success', 'error', 'warning', 'info' ], true ) ) {
				return $m[2];
			}
			return 'info';
		}

		return null;
	}

	/**
	 * Fix form validation messages.
	 */
	private function fix_form_messages( \DOMXPath $xpath ): int {
		$fixed = 0;

		$messages = $xpath->query(
			'//*[contains(@class, "validation") or contains(@class, "form-error") or '
			. 'contains(@class, "field-error") or contains(@class, "help-block") or '
			. 'contains(@class, "invalid-feedback") or contains(@class, "error-message")]'
			. '[not(@role) and not(@aria-live)]'
		);

		if ( ! $messages instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $messages as $msg ) {
			if ( ! $msg instanceof \DOMElement ) {
				continue;
			}
			$msg->setAttribute( 'role', 'alert' );
			$msg->setAttribute( 'aria-live', 'assertive' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix loading indicators.
	 */
	private function fix_loading_indicators( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$loaders = $xpath->query(
			'//*[contains(@class, "loading") or contains(@class, "spinner") or '
			. 'contains(@class, "loader") or contains(@class, "progress") or '
			. 'contains(@class, "processing")]'
			. '[not(@role) and not(@aria-live)]'
		);

		if ( ! $loaders instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $loaders as $loader ) {
			if ( ! $loader instanceof \DOMElement ) {
				continue;
			}
			$loader->setAttribute( 'role', 'status' );
			$loader->setAttribute( 'aria-live', 'polite' );

			if ( '' === trim( $loader->textContent ) ) {
				$sr_text = $dom->createElement( 'span' );
				$sr_text->setAttribute( 'class', 'screen-reader-text slos-sr-only' );
				$sr_text->textContent = 'Loading...';
				$loader->appendChild( $sr_text );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix search result counters.
	 */
	private function fix_result_counters( \DOMXPath $xpath ): int {
		$fixed = 0;

		$counters = $xpath->query(
			'//*[(contains(@class, "result") and contains(@class, "count")) or '
			. 'contains(@class, "search-results-count") or contains(@class, "found-posts")]'
			. '[not(@role) and not(@aria-live)]'
		);

		if ( ! $counters instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $counters as $counter ) {
			if ( ! $counter instanceof \DOMElement ) {
				continue;
			}
			$counter->setAttribute( 'role', 'status' );
			$counter->setAttribute( 'aria-live', 'polite' );
			$counter->setAttribute( 'aria-atomic', 'true' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix cart and checkout messages (WooCommerce patterns).
	 */
	private function fix_cart_messages( \DOMXPath $xpath ): int {
		$fixed = 0;

		$cart_messages = $xpath->query(
			'//*[contains(@class, "cart-message") or contains(@class, "checkout-message") or '
			. 'contains(@class, "woocommerce-message") or contains(@class, "woocommerce-error") or '
			. 'contains(@class, "woocommerce-info") or contains(@class, "added-to-cart") or '
			. 'contains(@class, "updated-cart")]'
			. '[not(@role) and not(@aria-live)]'
		);

		if ( ! $cart_messages instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $cart_messages as $msg ) {
			if ( ! $msg instanceof \DOMElement ) {
				continue;
			}
			$class = strtolower( $msg->getAttribute( 'class' ) );

			if ( stripos( $class, 'error' ) !== false ) {
				$msg->setAttribute( 'role', 'alert' );
				$msg->setAttribute( 'aria-live', 'assertive' );
			} else {
				$msg->setAttribute( 'role', 'status' );
				$msg->setAttribute( 'aria-live', 'polite' );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Ensure a live region exists for dynamic announcements.
	 */
	private function ensure_live_region_exists( \DOMDocument $dom ): void {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//*[@data-slos-announcer]' );

		if ( $existing instanceof \DOMNodeList && $existing->length > 0 ) {
			return;
		}

		$announcer = $dom->createElement( 'div' );
		$announcer->setAttribute( 'data-slos-announcer', 'true' );
		$announcer->setAttribute( 'role', 'status' );
		$announcer->setAttribute( 'aria-live', 'polite' );
		$announcer->setAttribute( 'aria-atomic', 'true' );
		$announcer->setAttribute( 'class', 'slos-sr-only screen-reader-text' );
		$announcer->setAttribute( 'style', 'position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;' );

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( $body instanceof \DOMElement ) {
			$body->appendChild( $announcer );
		}
	}
}
