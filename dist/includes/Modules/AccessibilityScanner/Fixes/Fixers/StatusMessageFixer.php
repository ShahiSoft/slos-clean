<?php
/**
 * Status Message Fixer
 *
 * Ensures status messages are announced to screen readers.
 * WCAG 4.1.3 Status Messages (Level AA)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * StatusMessageFixer Class
 *
 * Adds appropriate ARIA live regions and roles to status messages,
 * alerts, notifications, and other dynamic content.
 */
class StatusMessageFixer extends BaseFixer {

	/**
	 * Patterns indicating status/notification types by keyword.
	 *
	 * @var array<string, array<string>>
	 */
	private const STATUS_PATTERNS = array(
		'success'  => array( 'success', 'completed', 'saved', 'done', 'confirmed', 'approved', 'passed', 'accepted' ),
		'error'    => array( 'error', 'fail', 'invalid', 'problem', 'issue', 'wrong', 'denied', 'rejected' ),
		'warning'  => array( 'warning', 'caution', 'attention', 'notice', 'careful', 'alert' ),
		'info'     => array( 'info', 'information', 'note', 'tip', 'update', 'hint', 'help' ),
		'loading'  => array( 'loading', 'processing', 'please wait', 'working', 'submitting' ),
		'progress' => array( 'progress', 'step', 'stage', 'uploading', 'downloading', 'syncing' ),
	);

	/**
	 * Class patterns that indicate status elements.
	 *
	 * @var array<string>
	 */
	private const STATUS_CLASSES = array(
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
	);

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'status-message';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds appropriate ARIA live regions to status messages';
	}

	/**
	 * Apply status message fixes to content
	 *
	 * @param string $content HTML content to fix.
	 * @return array{fixed_count: int, content: string}
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixed = 0;

		// Find potential status elements by class...
		$status_elements = $this->find_status_elements( $xpath );
		foreach ( $status_elements as $element ) {
			if ( $this->fix_status_element( $element ) ) {
				++$fixed;
			}
		}

		// Fix form validation messages...
		$fixed += $this->fix_form_messages( $xpath );

		// Fix loading indicators...
		$fixed += $this->fix_loading_indicators( $dom, $xpath );

		// Fix search result counters...
		$fixed += $this->fix_result_counters( $xpath );

		// Fix cart/checkout messages (WooCommerce patterns)...
		$fixed += $this->fix_cart_messages( $xpath );

		// Add screen reader announcer region if status elements found...
		if ( $fixed > 0 ) {
			$this->ensure_live_region_exists( $dom );
		}

		return array(
			'fixed_count' => $fixed,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Find elements that appear to be status messages
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return array<\DOMElement>
	 */
	private function find_status_elements( $xpath ) {
		$elements = array();

		// Build class selector from known status classes...
		$class_conditions = array();
		foreach ( self::STATUS_CLASSES as $class ) {
			$class_conditions[] = "contains(@class, '{$class}')";
		}

		$query = '//*[(' . implode( ' or ', $class_conditions ) . ') and not(@role) and not(@aria-live)]';
		$nodes = $xpath->query( $query );

		foreach ( $nodes as $node ) {
			$elements[] = $node;
		}

		return $elements;
	}

	/**
	 * Fix a single status element
	 *
	 * @param \DOMElement $element Element to fix.
	 * @return bool True if fixed.
	 */
	private function fix_status_element( $element ) {
		$class = strtolower( $element->getAttribute( 'class' ) );
		$text  = strtolower( $element->textContent );

		// Determine type based on class/content...
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
	 * Determine the type of status message
	 *
	 * @param string $class Element class attribute.
	 * @param string $text  Element text content.
	 * @return string|null Status type or null.
	 */
	private function determine_status_type( $class, $text ) {
		foreach ( self::STATUS_PATTERNS as $type => $patterns ) {
			foreach ( $patterns as $pattern ) {
				if ( stripos( $class, $pattern ) !== false ||
					stripos( $text, $pattern ) !== false ) {
					return $type;
				}
			}
		}

		// Check for common Bootstrap/UI framework classes...
		if ( preg_match( '/\b(alert|notice|message)-(success|error|warning|info|danger|primary|secondary)\b/', $class, $m ) ) {
			if ( 'danger' === $m[2] ) {
				return 'error';
			}
			if ( in_array( $m[2], array( 'success', 'error', 'warning', 'info' ), true ) ) {
				return $m[2];
			}
			return 'info'; // Default for primary/secondary.
		}

		return null;
	}

	/**
	 * Fix form validation messages
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_form_messages( $xpath ) {
		$fixed = 0;

		// Find form validation messages...
		$messages = $xpath->query(
			'//*[contains(@class, "validation") or contains(@class, "form-error") or ' .
			'contains(@class, "field-error") or contains(@class, "help-block") or ' .
			'contains(@class, "invalid-feedback") or contains(@class, "error-message")]' .
			'[not(@role) and not(@aria-live)]'
		);

		foreach ( $messages as $msg ) {
			$msg->setAttribute( 'role', 'alert' );
			$msg->setAttribute( 'aria-live', 'assertive' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix loading indicators
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_loading_indicators( $dom, $xpath ) {
		$fixed = 0;

		// Find loading spinners/indicators...
		$loaders = $xpath->query(
			'//*[contains(@class, "loading") or contains(@class, "spinner") or ' .
			'contains(@class, "loader") or contains(@class, "progress") or ' .
			'contains(@class, "processing")]' .
			'[not(@role) and not(@aria-live)]'
		);

		foreach ( $loaders as $loader ) {
			$loader->setAttribute( 'role', 'status' );
			$loader->setAttribute( 'aria-live', 'polite' );

			// If no text content, add screen reader text...
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
	 * Fix search result counters
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_result_counters( $xpath ) {
		$fixed = 0;

		// Find search result counters...
		$counters = $xpath->query(
			'//*[(contains(@class, "result") and contains(@class, "count")) or ' .
			'contains(@class, "search-results-count") or contains(@class, "found-posts")]' .
			'[not(@role) and not(@aria-live)]'
		);

		foreach ( $counters as $counter ) {
			$counter->setAttribute( 'role', 'status' );
			$counter->setAttribute( 'aria-live', 'polite' );
			$counter->setAttribute( 'aria-atomic', 'true' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix cart and checkout messages (WooCommerce patterns)
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_cart_messages( $xpath ) {
		$fixed = 0;

		// Find WooCommerce and cart-related messages...
		$cart_messages = $xpath->query(
			'//*[contains(@class, "cart-message") or contains(@class, "checkout-message") or ' .
			'contains(@class, "woocommerce-message") or contains(@class, "woocommerce-error") or ' .
			'contains(@class, "woocommerce-info") or contains(@class, "added-to-cart") or ' .
			'contains(@class, "updated-cart")]' .
			'[not(@role) and not(@aria-live)]'
		);

		foreach ( $cart_messages as $msg ) {
			$class = strtolower( $msg->getAttribute( 'class' ) );

			// Determine if error or success...
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
	 * Ensure a live region exists for dynamic announcements
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @return void
	 */
	private function ensure_live_region_exists( $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//*[@data-slos-announcer]' );

		if ( $existing->length > 0 ) {
			return;
		}

		// Create announcer region for JavaScript to use...
		$announcer = $dom->createElement( 'div' );
		$announcer->setAttribute( 'data-slos-announcer', 'true' );
		$announcer->setAttribute( 'role', 'status' );
		$announcer->setAttribute( 'aria-live', 'polite' );
		$announcer->setAttribute( 'aria-atomic', 'true' );
		$announcer->setAttribute( 'class', 'slos-sr-only screen-reader-text' );
		$announcer->setAttribute( 'style', 'position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;' );

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( $body ) {
			$body->appendChild( $announcer );
		}
	}
}
