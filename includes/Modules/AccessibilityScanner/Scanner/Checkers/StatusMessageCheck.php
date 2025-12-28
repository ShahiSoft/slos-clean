<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for status messages with proper ARIA
 * WCAG 4.1.3 - Status Messages (Level AA)
 *
 * @since 3.1.2
 */
class StatusMessageCheck extends AbstractCheck {

	/**
	 * Common status message class patterns
	 *
	 * @var array
	 */
	private $status_patterns = array(
		'alert',
		'notice',
		'notification',
		'message',
		'toast',
		'snackbar',
		'success',
		'error',
		'warning',
		'info',
		'flash',
		'feedback',
		'status',
		'update',
		'banner',
		'callout',
		'announcement',
	);

	/**
	 * Valid live region roles
	 *
	 * @var array
	 */
	private $live_region_roles = array(
		'alert',
		'status',
		'log',
		'marquee',
		'timer',
		'progressbar',
	);

	/**
	 * Get check ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'status-message';
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Status messages must be announced to assistive technology without receiving focus.';
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
		return '4.1.3';
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

		// 1. Check status message containers without ARIA
		$this->check_status_containers( $xpath, $issues );

		// 2. Check for common live region mistakes
		$this->check_live_region_usage( $xpath, $issues );

		// 3. Check output elements
		$this->check_output_elements( $dom, $issues );

		// 4. Check for progress indicators
		$this->check_progress_indicators( $xpath, $issues );

		// 5. Check for form validation messages
		$this->check_validation_messages( $xpath, $issues );

		return $issues;
	}

	/**
	 * Check status message containers for proper ARIA
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_status_containers( $xpath, &$issues ) {
		foreach ( $this->status_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

			foreach ( $elements as $element ) {
				// Skip if it has appropriate ARIA
				if ( $this->has_live_region_semantics( $element ) ) {
					continue;
				}

				// Check for indicators that suggest dynamic content
				$has_dynamic_hints = $this->has_dynamic_hints( $element );
				$is_empty          = trim( $element->textContent ) === '';

				if ( $has_dynamic_hints || $is_empty ) {
					// Likely a dynamic container
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => sprintf(
							'Status message container (class "%s") lacks role="status", role="alert", or aria-live attribute. Dynamic updates won\'t be announced to screen readers.',
							$pattern
						),
					);
				} else {
					// Static content - lower severity
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => sprintf(
							'Element may display status messages (class "%s"). If content updates dynamically, add role="status" (for non-urgent) or role="alert" (for urgent messages).',
							$pattern
						),
						'severity' => 'notice',
					);
				}
			}
		}
	}

	/**
	 * Check if element has live region semantics
	 *
	 * @param \DOMElement $element Element to check.
	 * @return bool True if has live region semantics.
	 */
	private function has_live_region_semantics( $element ) {
		// Check explicit aria-live
		$aria_live = $element->getAttribute( 'aria-live' );
		if ( in_array( $aria_live, array( 'polite', 'assertive' ), true ) ) {
			return true;
		}

		// Check roles with implicit live region semantics
		$role = $element->getAttribute( 'role' );
		if ( in_array( $role, $this->live_region_roles, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for hints that element is dynamically updated
	 *
	 * @param \DOMElement $element Element to check.
	 * @return bool True if dynamic hints found.
	 */
	private function has_dynamic_hints( $element ) {
		$dynamic_attrs = array(
			'data-message',
			'data-alert',
			'data-notification',
			'data-bind',
			'data-ng-',
			'v-if',
			'v-show',
			'ng-show',
			'ng-if',
		);

		foreach ( $dynamic_attrs as $attr ) {
			if ( strpos( $attr, '-' ) === strlen( $attr ) - 1 ) {
				// Prefix match (e.g., 'data-ng-')
				foreach ( $element->attributes as $node_attr ) {
					if ( strpos( $node_attr->name, rtrim( $attr, '-' ) ) === 0 ) {
						return true;
					}
				}
			} elseif ( $element->hasAttribute( $attr ) ) {
				return true;
			}
		}

		// Check for JavaScript event handlers
		$js_events = array( 'onclick', 'onload', 'onchange' );
		foreach ( $js_events as $event ) {
			if ( $element->hasAttribute( $event ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for common live region mistakes
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_live_region_usage( $xpath, &$issues ) {
		// Check for aria-live="off" on alert containers
		$off_alerts = $xpath->query( "//*[@aria-live='off'][contains(@class, 'alert') or contains(@class, 'error') or contains(@class, 'message')]" );

		foreach ( $off_alerts as $element ) {
			$issues[] = array(
				'element' => $element->tagName,
				'context' => $this->get_element_html( $element ),
				'message' => 'Alert/message container has aria-live="off" which prevents screen reader announcement. Remove this attribute or set to "polite" or "assertive".',
			);
		}

		// Check for role="alert" used for non-error messages
		$success_alerts = $xpath->query( "//*[@role='alert'][contains(@class, 'success') or contains(@class, 'info')]" );

		foreach ( $success_alerts as $element ) {
			$issues[] = array(
				'element'  => $element->tagName,
				'context'  => $this->get_element_html( $element ),
				'message'  => 'Using role="alert" for non-error messages may be too assertive. Consider role="status" for success/info messages to avoid interrupting users.',
				'severity' => 'notice',
			);
		}

		// Check for aria-atomic usage without aria-live
		$atomic_no_live = $xpath->query( "//*[@aria-atomic='true'][not(@aria-live)][not(@role='alert')][not(@role='status')]" );

		foreach ( $atomic_no_live as $element ) {
			$issues[] = array(
				'element'  => $element->tagName,
				'context'  => $this->get_element_html( $element ),
				'message'  => 'Element has aria-atomic but no aria-live or live region role. Add aria-live="polite" or appropriate role for the atomic announcement to work.',
				'severity' => 'warning',
			);
		}

		// Check for aria-relevant without aria-live
		$relevant_no_live = $xpath->query( "//*[@aria-relevant][not(@aria-live)][not(@role='alert')][not(@role='status')][not(@role='log')]" );

		foreach ( $relevant_no_live as $element ) {
			$issues[] = array(
				'element'  => $element->tagName,
				'context'  => $this->get_element_html( $element ),
				'message'  => 'Element has aria-relevant but no aria-live or live region role. Add aria-live for aria-relevant to have effect.',
				'severity' => 'warning',
			);
		}
	}

	/**
	 * Check output elements have proper structure
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @param array        $issues Issues array by reference.
	 */
	private function check_output_elements( $dom, &$issues ) {
		$outputs = $dom->getElementsByTagName( 'output' );

		foreach ( $outputs as $output ) {
			// Output has implicit role="status", which is good
			// But check if it has proper association
			$for_attr = $output->getAttribute( 'for' );
			$form_attr = $output->getAttribute( 'form' );

			if ( empty( $for_attr ) && empty( $form_attr ) ) {
				$issues[] = array(
					'element'  => 'output',
					'context'  => $this->get_element_html( $output ),
					'message'  => '<output> element should have a "for" attribute referencing the IDs of controls that affect it, for proper accessibility.',
					'severity' => 'notice',
				);
			}
		}
	}

	/**
	 * Check progress indicators for proper accessibility
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_progress_indicators( $xpath, &$issues ) {
		// Check custom progress indicators (not using progress element)
		$progress_patterns = array( 'progress', 'loading', 'spinner', 'loader' );

		foreach ( $progress_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

			foreach ( $elements as $element ) {
				$tag = strtolower( $element->tagName );

				// Skip actual progress elements
				if ( $tag === 'progress' ) {
					continue;
				}

				$role = $element->getAttribute( 'role' );

				if ( $role !== 'progressbar' && $role !== 'status' ) {
					$has_aria_busy = $element->getAttribute( 'aria-busy' ) === 'true';

					if ( ! $has_aria_busy ) {
						$issues[] = array(
							'element'  => $element->tagName,
							'context'  => $this->get_element_html( $element ),
							'message'  => sprintf(
								'Progress/loading indicator (class "%s") should use role="progressbar" (for determinate) or role="status" with aria-busy="true" (for indeterminate).',
								$pattern
							),
							'severity' => 'warning',
						);
					}
				}
				break; // One issue per pattern
			}
		}

		// Check native progress elements
		$progresses = $xpath->query( '//progress' );

		foreach ( $progresses as $progress ) {
			// Check for accessible name
			$has_label = $this->has_accessible_name( $progress, $xpath );

			if ( ! $has_label ) {
				$issues[] = array(
					'element'  => 'progress',
					'context'  => $this->get_element_html( $progress ),
					'message'  => 'Progress element should have an accessible name via aria-label, aria-labelledby, or associated label.',
					'severity' => 'warning',
				);
			}
		}
	}

	/**
	 * Check if element has accessible name
	 *
	 * @param \DOMElement $element Element to check.
	 * @param \DOMXPath   $xpath XPath object.
	 * @return bool True if has accessible name.
	 */
	private function has_accessible_name( $element, $xpath ) {
		// Check aria-label
		if ( $element->hasAttribute( 'aria-label' ) && trim( $element->getAttribute( 'aria-label' ) ) !== '' ) {
			return true;
		}

		// Check aria-labelledby
		$labelledby = $element->getAttribute( 'aria-labelledby' );
		if ( ! empty( $labelledby ) ) {
			$ref = $xpath->query( "//*[@id='$labelledby']" );
			if ( $ref->length > 0 && trim( $ref->item( 0 )->textContent ) !== '' ) {
				return true;
			}
		}

		// Check title
		if ( $element->hasAttribute( 'title' ) && trim( $element->getAttribute( 'title' ) ) !== '' ) {
			return true;
		}

		// Check associated label
		$id = $element->getAttribute( 'id' );
		if ( ! empty( $id ) ) {
			$labels = $xpath->query( "//label[@for='$id']" );
			if ( $labels->length > 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check form validation messages
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_validation_messages( $xpath, &$issues ) {
		$validation_patterns = array(
			'validation',
			'form-error',
			'field-error',
			'invalid-feedback',
			'help-block',
		);

		foreach ( $validation_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

			foreach ( $elements as $element ) {
				// Check if it's properly linked to a form field
				$id         = $element->getAttribute( 'id' );
				$is_linked  = false;

				if ( ! empty( $id ) ) {
					// Check if any form field references this via aria-describedby or aria-errormessage
					$linked = $xpath->query( "//*[contains(@aria-describedby, '$id') or @aria-errormessage='$id']" );
					$is_linked = $linked->length > 0;
				}

				if ( ! $is_linked ) {
					// Check for aria-live on the message itself
					if ( ! $this->has_live_region_semantics( $element ) ) {
						$issues[] = array(
							'element'  => $element->tagName,
							'context'  => $this->get_element_html( $element ),
							'message'  => sprintf(
								'Validation message (class "%s") should either be linked to form field via aria-describedby/aria-errormessage, or have aria-live="polite" to announce changes.',
								$pattern
							),
							'severity' => 'warning',
						);
					}
				}
				break; // One issue per pattern
			}
		}
	}

	/**
	 * Get element HTML for context
	 *
	 * @param \DOMNode $node DOM node.
	 * @return string HTML string.
	 */
	private function get_element_html( $node ) {
		$html = $node->ownerDocument->saveHTML( $node );
		return strlen( $html ) > 150 ? substr( $html, 0, 150 ) . '...' : $html;
	}
}
