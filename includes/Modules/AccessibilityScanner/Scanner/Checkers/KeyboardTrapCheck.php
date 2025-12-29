<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for keyboard traps
 * WCAG 2.1.2 - No Keyboard Trap (Level A)
 */
class KeyboardTrapCheck extends AbstractCheck {

	public function get_id() {
		return 'keyboard-trap';
	}

	public function get_description() {
		return 'Ensure keyboard focus can be moved away from any component using standard keyboard navigation.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.1.2';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Ensure all interactive elements can be exited using Tab, Shift+Tab, or Escape key. For modals, provide a clear close mechanism.';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check for event handlers on non-interactive elements
		$this->check_event_handlers( $xpath, $issues );

		// 2. Check for tabindex patterns that may cause traps
		$this->check_tabindex_patterns( $xpath, $issues );

		// 3. Check for modals/dialogs with limited focusable elements
		$this->check_single_focusable_containers( $xpath, $issues );

		// 4. Check for inert attribute usage
		$this->check_inert_attribute( $xpath, $issues );

		// 5. Check for focus management attributes
		$this->check_focus_management( $xpath, $issues );

		return $issues;
	}

	/**
	 * Check for event handlers on non-interactive elements
	 */
	private function check_event_handlers( $xpath, &$issues ) {
		$elements = $xpath->query( '//*[@onkeydown or @onkeypress or @onfocus or @onblur]' );

		foreach ( $elements as $element ) {
			$tag = strtolower( $element->tagName );

			// Standard interactive elements are usually fine
			if ( in_array( $tag, array( 'input', 'select', 'textarea', 'button', 'a' ), true ) ) {
				continue;
			}

			// Check if it has tabindex (intentionally focusable)
			$tabindex = $element->getAttribute( 'tabindex' );

			$issues[] = array(
				'element'    => $tag,
				'context'    => $this->get_element_html( $element ),
				'message'    => 'Non-interactive element has keyboard event handlers. Ensure focus is not trapped and can be moved away using standard keyboard navigation.',
				'confidence' => 'medium',
			);
		}
	}

	/**
	 * Check tabindex patterns that may indicate traps
	 */
	private function check_tabindex_patterns( $xpath, &$issues ) {
		// Containers with tabindex=-1 containing focusable elements
		$containers = $xpath->query( '//*[@tabindex="-1"]' );

		foreach ( $containers as $container ) {
			// Skip if it's a simple element
			$tag = strtolower( $container->tagName );
			if ( in_array( $tag, array( 'input', 'button', 'a', 'select', 'textarea' ), true ) ) {
				continue;
			}

			// Check if it contains focusable children
			$focusable = $xpath->query(
				'.//a[@href] | .//button | .//input[not(@type="hidden")] | .//select | .//textarea | .//*[@tabindex and @tabindex != "-1"]',
				$container
			);

			if ( $focusable->length > 0 && $focusable->length < 3 ) {
				$issues[] = array(
					'element'    => $tag,
					'context'    => $this->get_element_html( $container ),
					'message'    => sprintf(
						'Container with tabindex="-1" contains %d focusable element(s). Verify focus can escape this region.',
						$focusable->length
					),
					'severity'   => 'warning',
					'confidence' => 'medium',
				);
			}
		}

		// Check for positive tabindex (creates unexpected tab order)
		$positive_tabindex = $xpath->query( '//*[@tabindex > 0]' );

		if ( $positive_tabindex->length > 0 ) {
			$issues[] = array(
				'element'    => 'multiple',
				'context'    => sprintf( 'Found %d elements with positive tabindex', $positive_tabindex->length ),
				'message'    => 'Positive tabindex values create unexpected tab order and can confuse keyboard users. Use tabindex="0" instead.',
				'severity'   => 'notice',
				'confidence' => 'high',
			);
		}
	}

	/**
	 * Check modals/dialogs with single or no focusable elements
	 */
	private function check_single_focusable_containers( $xpath, &$issues ) {
		// Find modal/dialog patterns
		$modals = $xpath->query( '//*[@role="dialog"] | //*[@role="alertdialog"] | //*[contains(@class, "modal")] | //*[contains(@class, "dialog")] | //*[contains(@class, "popup")]' );

		foreach ( $modals as $modal ) {
			// Count focusable elements within
			$focusable = $xpath->query(
				'.//a[@href] | .//button | .//input[not(@type="hidden")] | .//select | .//textarea | .//*[@tabindex="0"]',
				$modal
			);

			if ( $focusable->length === 0 ) {
				$issues[] = array(
					'element'    => 'dialog',
					'context'    => $this->get_element_html( $modal ),
					'message'    => 'Modal/dialog has no focusable elements. Users cannot interact with or close it via keyboard.',
					'severity'   => 'warning',
					'confidence' => 'high',
				);
			} elseif ( $focusable->length === 1 ) {
				// Check if the single element is a close button
				$single = $focusable->item( 0 );
				$is_close_button = $this->is_close_button( $single );

				if ( ! $is_close_button ) {
					$issues[] = array(
						'element'    => 'dialog',
						'context'    => $this->get_element_html( $modal ),
						'message'    => 'Modal/dialog has only one focusable element. Ensure users can close or navigate away.',
						'severity'   => 'notice',
						'confidence' => 'medium',
					);
				}
			}

			// Check for close button in modal
			$close_buttons = $xpath->query(
				'.//button[contains(@class, "close")] | .//button[contains(@aria-label, "close")] | .//*[@aria-label="Close"] | .//*[@aria-label="close"]',
				$modal
			);

			if ( $close_buttons->length === 0 ) {
				$issues[] = array(
					'element'    => 'dialog',
					'context'    => $this->get_element_html( $modal ),
					'message'    => 'Modal/dialog may not have a visible close button. Ensure Escape key or a close button is available.',
					'severity'   => 'notice',
					'confidence' => 'low',
				);
			}
		}
	}

	/**
	 * Check if element appears to be a close button
	 */
	private function is_close_button( $element ) {
		$text       = strtolower( trim( $element->textContent ) );
		$aria_label = strtolower( $element->getAttribute( 'aria-label' ) );
		$class      = strtolower( $element->getAttribute( 'class' ) );

		$close_indicators = array( 'close', 'dismiss', 'cancel', '×', 'x' );

		foreach ( $close_indicators as $indicator ) {
			if (
				strpos( $text, $indicator ) !== false ||
				strpos( $aria_label, $indicator ) !== false ||
				strpos( $class, $indicator ) !== false
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for inert attribute usage
	 */
	private function check_inert_attribute( $xpath, &$issues ) {
		$inert_elements = $xpath->query( '//*[@inert]' );

		foreach ( $inert_elements as $element ) {
			$tag = $element->tagName;

			// Count children to assess impact
			$child_count = $element->childNodes->length;

			$issues[] = array(
				'element'    => $tag,
				'context'    => $this->get_element_html( $element ),
				'message'    => 'Element uses "inert" attribute, making it and its children non-interactive. Ensure this is intentional and content is accessible when needed.',
				'severity'   => 'notice',
				'confidence' => 'medium',
			);
		}
	}

	/**
	 * Check for focus management patterns
	 */
	private function check_focus_management( $xpath, &$issues ) {
		// Check for aria-hidden on potentially interactive containers
		$aria_hidden = $xpath->query( '//*[@aria-hidden="true"]' );

		foreach ( $aria_hidden as $element ) {
			// Check if it contains focusable elements
			$focusable = $xpath->query(
				'.//a[@href] | .//button | .//input | .//select | .//textarea',
				$element
			);

			if ( $focusable->length > 0 ) {
				$issues[] = array(
					'element'    => $element->tagName,
					'context'    => $this->get_element_html( $element ),
					'message'    => sprintf(
						'Element with aria-hidden="true" contains %d focusable element(s). These remain keyboard-focusable but hidden from screen readers.',
						$focusable->length
					),
					'severity'   => 'warning',
					'confidence' => 'high',
				);
			}
		}
	}
}

