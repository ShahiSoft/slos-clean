<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text Color Contrast Fixer
 */
class TextColorContrastFixer extends BaseFixer {
	public function get_id() {
		return 'text-color-contrast'; }
	public function get_description() {
		return 'Improve color contrast'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Fix elements with light text colors on light backgrounds
		$elements = $xpath->query( '//*[@style[contains(., "color")]]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );

			// Find color: #xxx or color: rgb() patterns
			if ( preg_match( '/color\s*:\s*(#([a-f0-9]{3,6})|rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\))/i', $style, $matches ) ) {
				$is_light = false;

				if ( isset( $matches[2] ) ) {
					// Hex color
					$hex = $matches[2];
					if ( strlen( $hex ) === 3 ) {
						$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
					}
					$r = hexdec( substr( $hex, 0, 2 ) );
					$g = hexdec( substr( $hex, 2, 2 ) );
					$b = hexdec( substr( $hex, 4, 2 ) );
				} elseif ( isset( $matches[3] ) ) {
					// RGB color
					$r = (int) $matches[3];
					$g = (int) $matches[4];
					$b = (int) $matches[5];
				} else {
					continue;
				}

				// Calculate relative luminance (simplified)
				$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

				// If luminance > 0.6 (light color), darken it
				if ( $luminance > 0.6 ) {
					$is_light = true;
					$style    = preg_replace(
						'/color\s*:\s*[^;]+/',
						'color: #333333',
						$style
					);
				}
				// If luminance < 0.2 (very dark), might be on dark background - add to light
				elseif ( $luminance < 0.2 ) {
					// Check parent background if available
					$parent_style = $element->parentNode instanceof \DOMElement ? $element->parentNode->getAttribute( 'style' ) : '';
					if ( preg_match( '/background(-color)?\s*:\s*#([a-f0-9]{3,6})/i', $parent_style, $bg_match ) ) {
						$bg_hex = $bg_match[2];
						if ( strlen( $bg_hex ) === 3 ) {
							$bg_hex = $bg_hex[0] . $bg_hex[0] . $bg_hex[1] . $bg_hex[1] . $bg_hex[2] . $bg_hex[2];
						}
						$bg_r          = hexdec( substr( $bg_hex, 0, 2 ) );
						$bg_g          = hexdec( substr( $bg_hex, 2, 2 ) );
						$bg_b          = hexdec( substr( $bg_hex, 4, 2 ) );
						$bg_luminance  = ( 0.299 * $bg_r + 0.587 * $bg_g + 0.114 * $bg_b ) / 255;

						// Dark text on dark background - lighten text
						if ( $bg_luminance < 0.3 ) {
							$is_light = true;
							$style    = preg_replace(
								'/color\s*:\s*[^;]+/',
								'color: #ffffff',
								$style
							);
						}
					}
				}

				if ( $is_light ) {
					$element->setAttribute( 'style', $style );
					$element->setAttribute( 'data-slos-contrast-fixed', 'true' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Color Reliance Fixer
 * Adds non-color indicators where color alone conveys information (WCAG 1.4.1)
 */
class ColorRelianceFixer extends BaseFixer {
	public function get_id() {
		return 'color-reliance';
	}

	public function get_description() {
		return 'Adds non-color indicators where color alone conveys information';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Fix required field indicators (red asterisks).
		$fixed_count += $this->fix_required_indicators( $xpath, $dom );

		// Fix status indicators relying only on color.
		$fixed_count += $this->fix_status_colors( $xpath, $dom );

		// Fix links distinguished only by color.
		$fixed_count += $this->fix_color_only_links( $xpath );

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix required field indicators that only use color.
	 *
	 * @param \DOMXPath    $xpath The XPath instance.
	 * @param \DOMDocument $dom   The DOM document.
	 * @return int Number of fixes applied.
	 */
	private function fix_required_indicators( \DOMXPath $xpath, \DOMDocument $dom ) {
		$fixed = 0;

		// Find red asterisks without text explanation.
		$asterisks = $xpath->query(
			'//span[@style[contains(., "red") or contains(., "#f") or contains(., "#e") or contains(., "rgb")]][contains(text(), "*")][not(@aria-hidden)]'
		);

		foreach ( $asterisks as $asterisk ) {
			// Check if already has screen reader text nearby.
			$parent     = $asterisk->parentNode;
			$parent_txt = $parent instanceof \DOMElement ? $parent->textContent : '';

			if ( stripos( $parent_txt, 'required' ) !== false ) {
				continue;
			}

			// Add screen reader text.
			$sr_text = $dom->createElement( 'span' );
			$sr_text->setAttribute( 'class', 'screen-reader-text' );
			$sr_text->textContent = ' (required)';

			$asterisk->parentNode->insertBefore( $sr_text, $asterisk->nextSibling );
			$asterisk->setAttribute( 'aria-hidden', 'true' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix status indicators that rely only on color.
	 *
	 * @param \DOMXPath    $xpath The XPath instance.
	 * @param \DOMDocument $dom   The DOM document.
	 * @return int Number of fixes applied.
	 */
	private function fix_status_colors( \DOMXPath $xpath, \DOMDocument $dom ) {
		$fixed = 0;

		// Status class patterns and their indicators.
		$status_patterns = array(
			'success' => array( '✓', 'Success' ),
			'error'   => array( '✗', 'Error' ),
			'warning' => array( '⚠', 'Warning' ),
			'info'    => array( 'ℹ', 'Info' ),
			'danger'  => array( '✗', 'Error' ),
			'alert'   => array( '⚠', 'Alert' ),
		);

		foreach ( $status_patterns as $status => $indicator ) {
			$elements = $xpath->query(
				"//*[contains(@class, '{$status}') or contains(@class, 'bg-{$status}') or " .
				"contains(@class, 'text-{$status}')][not(.//*[name()='svg']) and not(.//i[contains(@class, 'icon')])]"
			);

			foreach ( $elements as $element ) {
				// Skip if already has data attribute.
				if ( $element->hasAttribute( 'data-slos-status-fixed' ) ) {
					continue;
				}

				$text = trim( $element->textContent );

				// Skip if already has status text.
				if ( stripos( $text, $indicator[1] ) !== false ) {
					continue;
				}

				// Add icon before content.
				$icon = $dom->createElement( 'span' );
				$icon->setAttribute( 'aria-hidden', 'true' );
				$icon->setAttribute( 'class', 'slos-status-icon' );
				$icon->textContent = $indicator[0] . ' ';

				if ( $element->firstChild ) {
					$element->insertBefore( $icon, $element->firstChild );
				} else {
					$element->appendChild( $icon );
				}

				// Add screen reader text if content is short.
				if ( strlen( $text ) < 50 && stripos( $text, $indicator[1] ) === false ) {
					$sr = $dom->createElement( 'span' );
					$sr->setAttribute( 'class', 'screen-reader-text' );
					$sr->textContent = ' ' . $indicator[1];
					$element->appendChild( $sr );
				}

				$element->setAttribute( 'data-slos-status-fixed', 'true' );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix links that are distinguished only by color.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function fix_color_only_links( \DOMXPath $xpath ) {
		$fixed = 0;

		// Find links that might only be distinguished by color
		// (no underline in style, within paragraphs).
		$links = $xpath->query(
			'//p//a[@style[contains(., "text-decoration")]]'
		);

		foreach ( $links as $link ) {
			$style = $link->getAttribute( 'style' );

			// Check if text-decoration is none.
			if ( preg_match( '/text-decoration\s*:\s*none/i', $style ) ) {
				// Add underline back.
				$style = preg_replace(
					'/text-decoration\s*:\s*none/i',
					'text-decoration: underline',
					$style
				);
				$link->setAttribute( 'style', $style );
				++$fixed;
			}
		}

		return $fixed;
	}
}

/**
 * Complex Contrast Fixer
 */
class ComplexContrastFixer extends BaseFixer {
	public function get_id() {
		return 'complex-contrast'; }
	public function get_description() {
		return 'Fix complex contrast issues'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Fix overlays with insufficient opacity
		$overlays = $xpath->query(
			'//*[contains(@class, "overlay") or contains(@class, "backdrop")][@style]'
		);

		foreach ( $overlays as $overlay ) {
			$style = $overlay->getAttribute( 'style' );

			// Check for opacity < 0.75 on overlays with text
			if ( preg_match( '/opacity\s*:\s*(0?\.\d+|0)/i', $style, $match ) ) {
				$opacity = (float) $match[1];
				if ( $opacity < 0.75 && ! empty( trim( $overlay->textContent ) ) ) {
					$style = preg_replace(
						'/opacity\s*:\s*[^;]+/',
						'opacity: 0.9',
						$style
					);
					$overlay->setAttribute( 'style', $style );
					++$fixed_count;
				}
			}
		}

		// Fix gradient backgrounds with light text
		$gradients = $xpath->query(
			'//*[@style[contains(., "gradient")]]'
		);

		foreach ( $gradients as $element ) {
			$style = $element->getAttribute( 'style' );
			$text  = trim( $element->textContent );

			// If has gradient and text but no explicit color, add dark text
			if ( ! empty( $text ) && preg_match( '/gradient/i', $style ) && ! preg_match( '/color\s*:/i', $style ) ) {
				$style = rtrim( $style, '; ' ) . '; color: #333333; text-shadow: 0 0 4px rgba(255,255,255,0.8);';
				$element->setAttribute( 'style', $style );
				++$fixed_count;
			}
		}

		// Fix transparent backgrounds with no fallback color
		$transparent = $xpath->query(
			'//*[@style[contains(., "transparent") or contains(., "rgba")]]'
		);

		foreach ( $transparent as $element ) {
			$style = $element->getAttribute( 'style' );

			// If background is transparent/rgba but no color is set
			if ( preg_match( '/background(-color)?\s*:\s*(transparent|rgba\([^)]+\))/i', $style ) ) {
				if ( ! preg_match( '/(?<!background-)color\s*:/i', $style ) ) {
					$style = rtrim( $style, '; ' ) . '; color: #000000;';
					$element->setAttribute( 'style', $style );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Focus Indicator Fixer
 * Adds visible focus indicators to interactive elements (WCAG 2.4.7)
 */
class FocusIndicatorFixer extends BaseFixer {
	public function get_id() {
		return 'focus-indicator';
	}

	public function get_description() {
		return 'Adds visible focus indicators to interactive elements';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// 1. Remove outline:none from inline styles
		$no_outline = $xpath->query( '//*[@style]' );

		foreach ( $no_outline as $element ) {
			$style = $element->getAttribute( 'style' );

			if ( preg_match( '/outline\s*:\s*(none|0)/i', $style ) ) {
				// Remove outline:none and add visible focus style.
				$style = preg_replace(
					'/outline\s*:\s*(none|0)[^;]*(;|$)/i',
					'',
					$style
				);
				$element->setAttribute( 'style', trim( $style, '; ' ) );
				$element->setAttribute( 'data-slos-focus-fixed', 'true' );
				++$fixed_count;
			}
		}

		// 2. Add focus class to interactive elements.
		$interactive = $xpath->query(
			'//a[@href] | //button | //input | //select | //textarea | ' .
			'//*[@onclick] | //*[@tabindex and @tabindex!= "-1"]'
		);

		foreach ( $interactive as $element ) {
			$class = $element->getAttribute( 'class' );
			if ( strpos( $class, 'slos-focus-visible' ) === false ) {
				$element->setAttribute( 'class', trim( $class . ' slos-focus-visible' ) );
			}
		}

		// 3. Fix style tags that remove focus.
		$styles = $dom->getElementsByTagName( 'style' );
		foreach ( $styles as $styleTag ) {
			$css = $styleTag->textContent;

			// Replace :focus { outline: none } patterns.
			$patterns = array(
				'/(\*|a|button|input|select|textarea)\s*:focus\s*\{[^}]*outline\s*:\s*(none|0)[^}]*/i',
				'/:focus-visible\s*\{[^}]*outline\s*:\s*(none|0)[^}]*/i',
				'/:focus\s*\{[^}]*outline\s*:\s*(none|0)[^}]*/i',
			);

			$modified = false;
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $css ) ) {
					$css = preg_replace(
						$pattern,
						'$1:focus { outline: 2px solid #005fcc; outline-offset: 2px; }',
						$css
					);
					$modified = true;
				}
			}

			if ( $modified ) {
				$styleTag->textContent = $css;
				++$fixed_count;
			}
		}

		// 4. Inject focus styles if any fixes were made.
		if ( $fixed_count > 0 ) {
			$this->inject_focus_styles( $dom );
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Inject focus indicator CSS styles into the document.
	 *
	 * @param \DOMDocument $dom The DOM document.
	 */
	private function inject_focus_styles( \DOMDocument $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-focus-styles]' );
		if ( $existing->length > 0 ) {
			return;
		}

		$style = $dom->createElement( 'style' );
		$style->setAttribute( 'data-slos-focus-styles', 'true' );
		$style->textContent = '
/* SLOS Focus Indicator Fixes */
.slos-focus-visible:focus {
    outline: 2px solid #005fcc !important;
    outline-offset: 2px !important;
}

.slos-focus-visible:focus:not(:focus-visible) {
    outline: none !important;
}

.slos-focus-visible:focus-visible {
    outline: 2px solid #005fcc !important;
    outline-offset: 2px !important;
}

[data-slos-focus-fixed]:focus {
    outline: 2px solid #005fcc !important;
    outline-offset: 2px !important;
    box-shadow: 0 0 0 4px rgba(0, 95, 204, 0.3) !important;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .slos-focus-visible:focus,
    .slos-focus-visible:focus-visible,
    [data-slos-focus-fixed]:focus {
        outline: 3px solid currentColor !important;
        outline-offset: 3px !important;
    }
}
';

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$head->appendChild( $style );
		}
	}
}

/**
 * Positive Tab Index Fixer
 */
class PositiveTabIndexFixer extends BaseFixer {
	public function get_id() {
		return 'positive-tabindex'; }
	public function get_description() {
		return 'Remove positive tabindex'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		foreach ( $elements as $element ) {
			if ( $element->hasAttribute( 'tabindex' ) ) {
				$tabindex = intval( $element->getAttribute( 'tabindex' ) );
				if ( $tabindex > 0 ) {
					// Remove positive tabindex, use logical order instead
					$element->removeAttribute( 'tabindex' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Keyboard Trap Fixer
 * Adds keyboard escape mechanisms to potential trap elements (WCAG 2.1.2)
 */
class KeyboardTrapFixer extends BaseFixer {
	public function get_id() {
		return 'keyboard-trap';
	}

	public function get_description() {
		return 'Adds keyboard escape mechanisms to potential trap elements';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Find potential keyboard trap elements.
		$traps = $this->find_potential_traps( $xpath );

		foreach ( $traps as $trap ) {
			if ( $this->fix_keyboard_trap( $trap, $xpath, $dom ) ) {
				++$fixed_count;
			}
		}

		// Inject keyboard trap escape script if fixes were made.
		if ( $fixed_count > 0 ) {
			$this->inject_escape_script( $dom );
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Find potential keyboard trap elements.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return array Array of potential trap elements.
	 */
	private function find_potential_traps( \DOMXPath $xpath ) {
		$traps = array();

		// Modals/Dialogs.
		$modals = $xpath->query(
			'//*[@role="dialog" or @role="alertdialog" or ' .
			'contains(@class, "modal") or contains(@class, "popup") or ' .
			'contains(@class, "lightbox") or contains(@class, "overlay")]'
		);
		foreach ( $modals as $modal ) {
			$traps[] = $modal;
		}

		// iframes (can trap focus).
		$iframes = $xpath->query( '//iframe[not(@tabindex="-1")]' );
		foreach ( $iframes as $iframe ) {
			$traps[] = $iframe;
		}

		// Embedded content containers.
		$embeds = $xpath->query(
			'//*[contains(@class, "embed") or contains(@class, "video-container") or ' .
			'contains(@class, "player")]'
		);
		foreach ( $embeds as $embed ) {
			$traps[] = $embed;
		}

		return $traps;
	}

	/**
	 * Fix a keyboard trap element.
	 *
	 * @param \DOMElement  $element The element to fix.
	 * @param \DOMXPath    $xpath   The XPath instance.
	 * @param \DOMDocument $dom     The DOM document.
	 * @return bool Whether a fix was applied.
	 */
	private function fix_keyboard_trap( \DOMElement $element, \DOMXPath $xpath, \DOMDocument $dom ) {
		// Skip if already fixed.
		if ( $element->hasAttribute( 'data-slos-escape-enabled' ) ) {
			return false;
		}

		$tag = strtolower( $element->tagName );

		// For modals: ensure close button exists and is keyboard accessible.
		if ( $this->is_modal( $element ) ) {
			$close = $xpath->query(
				'.//*[contains(@class, "close") or contains(@aria-label, "close") or ' .
				'contains(@class, "dismiss")]',
				$element
			)->item( 0 );

			if ( ! $close ) {
				// Add close button.
				$close_btn = $dom->createElement( 'button' );
				$close_btn->setAttribute( 'type', 'button' );
				$close_btn->setAttribute( 'class', 'slos-modal-close' );
				$close_btn->setAttribute( 'aria-label', 'Close dialog' );
				$close_btn->setAttribute( 'data-slos-escape-trigger', 'true' );
				$close_btn->textContent = '×';

				// Insert at beginning.
				if ( $element->firstChild ) {
					$element->insertBefore( $close_btn, $element->firstChild );
				} else {
					$element->appendChild( $close_btn );
				}
			} else {
				// Ensure close button is keyboard accessible.
				if ( $close instanceof \DOMElement ) {
					if ( ! $close->hasAttribute( 'tabindex' ) ) {
						$close->setAttribute( 'tabindex', '0' );
					}
					$close->setAttribute( 'data-slos-escape-trigger', 'true' );
				}
			}

			$element->setAttribute( 'data-slos-escape-enabled', 'true' );
			return true;
		}

		// For iframes: add skip link before.
		if ( $tag === 'iframe' ) {
			$iframe_id = 'slos-after-iframe-' . uniqid();

			$skip = $dom->createElement( 'a' );
			$skip->setAttribute( 'href', '#' . $iframe_id );
			$skip->setAttribute( 'class', 'slos-skip-iframe screen-reader-text' );
			$skip->textContent = 'Skip embedded content';

			$element->parentNode->insertBefore( $skip, $element );

			// Add target anchor after iframe.
			$target = $dom->createElement( 'span' );
			$target->setAttribute( 'id', $iframe_id );
			$target->setAttribute( 'tabindex', '-1' );

			if ( $element->nextSibling ) {
				$element->parentNode->insertBefore( $target, $element->nextSibling );
			} else {
				$element->parentNode->appendChild( $target );
			}

			$element->setAttribute( 'data-slos-escape-enabled', 'true' );
			return true;
		}

		// Generic fix: add escape key data attribute.
		$element->setAttribute( 'data-slos-escape-enabled', 'true' );
		return true;
	}

	/**
	 * Check if element is a modal dialog.
	 *
	 * @param \DOMElement $element The element to check.
	 * @return bool Whether element is a modal.
	 */
	private function is_modal( \DOMElement $element ) {
		$role = $element->getAttribute( 'role' );
		if ( $role === 'dialog' || $role === 'alertdialog' ) {
			return true;
		}

		$class = strtolower( $element->getAttribute( 'class' ) );
		return preg_match( '/\b(modal|popup|lightbox|dialog)\b/', $class ) === 1;
	}

	/**
	 * Inject keyboard escape script into the document.
	 *
	 * @param \DOMDocument $dom The DOM document.
	 */
	private function inject_escape_script( \DOMDocument $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//script[@data-slos-escape-script]' );
		if ( $existing->length > 0 ) {
			return;
		}

		$script = $dom->createElement( 'script' );
		$script->setAttribute( 'data-slos-escape-script', 'true' );
		$script->textContent = '
(function() {
    "use strict";
    
    // Handle Escape key for modals
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            var modal = document.querySelector(
                "[data-slos-escape-enabled][role=\'dialog\']:not([hidden])," +
                "[data-slos-escape-enabled].modal:not(.hidden):not([style*=\'display: none\'])"
            );
            
            if (modal) {
                var closeBtn = modal.querySelector(
                    "[data-slos-escape-trigger], .close, [aria-label*=\'close\']"
                );
                if (closeBtn) {
                    closeBtn.click();
                }
                e.preventDefault();
            }
        }
    });
    
    // Focus trap handling for dialogs
    document.querySelectorAll("[data-slos-escape-enabled][role=\'dialog\']").forEach(function(dialog) {
        dialog.addEventListener("keydown", function(e) {
            if (e.key === "Tab") {
                var focusables = dialog.querySelectorAll(
                    "a[href], button:not([disabled]), input:not([disabled]), " +
                    "select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])"
                );
                
                if (focusables.length === 0) return;
                
                var first = focusables[0];
                var last = focusables[focusables.length - 1];
                
                if (e.shiftKey && document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                } else if (!e.shiftKey && document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        });
    });
})();
';

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( $body ) {
			$body->appendChild( $script );
		}
	}
}

/**
 * Focus Order Fixer
 */
class FocusOrderFixer extends BaseFixer {
	public function get_id() {
		return 'focus-order'; }
	public function get_description() {
		return 'Fix focus order'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Remove positive tabindex values (they disrupt natural order)
		$positive_tabindex = $xpath->query( '//*[@tabindex]' );

		foreach ( $positive_tabindex as $element ) {
			$tabindex = $element->getAttribute( 'tabindex' );
			if ( is_numeric( $tabindex ) && (int) $tabindex > 0 ) {
				// Remove positive tabindex
				$element->removeAttribute( 'tabindex' );
				++$fixed_count;
			}
		}

		// Fix elements that should be keyboard-accessible but aren't
		$interactive_no_tab = $xpath->query(
			'//*[@onclick or @onkeypress][not(@tabindex)][not(self::a or self::button or self::input or self::select or self::textarea)]'
		);

		foreach ( $interactive_no_tab as $element ) {
			$element->setAttribute( 'tabindex', '0' );
			++$fixed_count;
		}

		// Remove tabindex from non-interactive elements
		$non_interactive_tab = $xpath->query(
			'//*[@tabindex][@tabindex!="-1"][not(self::a or self::button or self::input or self::select or self::textarea)]' .
			'[not(@onclick or @onkeypress or @role="button" or @role="link" or @role="tab")]'
		);

		foreach ( $non_interactive_tab as $element ) {
			$element->removeAttribute( 'tabindex' );
			++$fixed_count;
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Interactive Element Fixer
 */
class InteractiveElementFixer extends BaseFixer {
	public function get_id() {
		return 'interactive-element'; }
	public function get_description() {
		return 'Make interactive elements keyboard accessible'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		foreach ( $divs as $div ) {
			$onclick = $div->getAttribute( 'onclick' );
			if ( ! empty( $onclick ) && empty( $div->getAttribute( 'tabindex' ) ) ) {
				$div->setAttribute( 'tabindex', '0' );
				if ( ! $div->hasAttribute( 'role' ) ) {
					$div->setAttribute( 'role', 'button' );
				}
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Modal Accessibility Fixer
 */
class ModalAccessibilityFixer extends BaseFixer {
	public function get_id() {
		return 'modal-accessibility'; }
	public function get_description() {
		return 'Add ARIA to modals'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		foreach ( $divs as $div ) {
			$class = $div->getAttribute( 'class' );
			if ( preg_match( '/(modal|popup|dialog|overlay)/i', $class ) ) {
				if ( ! $div->hasAttribute( 'role' ) ) {
					$div->setAttribute( 'role', 'dialog' );
					$div->setAttribute( 'aria-modal', 'true' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Custom Widget Keyboard Fixer
 */
class CustomWidgetKeyboardFixer extends BaseFixer {
	public function get_id() {
		return 'custom-widget-keyboard'; }
	public function get_description() {
		return 'Make custom widgets keyboard accessible'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Find custom widgets without keyboard support
		$widgets = $xpath->query(
			'//*[@role="slider" or @role="spinbutton" or @role="listbox" or ' .
			'@role="menu" or @role="menubar" or @role="tree" or @role="grid" or ' .
			'@role="tablist" or @role="combobox"]' .
			'[not(@tabindex) or @tabindex="-1"]'
		);

		foreach ( $widgets as $widget ) {
			$role = $widget->getAttribute( 'role' );

			// Make widget keyboard accessible
			$widget->setAttribute( 'tabindex', '0' );

			// Add data attribute for script enhancement
			$widget->setAttribute( 'data-slos-keyboard-widget', $role );

			++$fixed_count;
		}

		// Find interactive divs/spans with roles but no tabindex
		$custom_controls = $xpath->query(
			'//*[(self::div or self::span)][@role="button" or @role="link" or ' .
			'@role="checkbox" or @role="radio" or @role="switch"]' .
			'[not(@tabindex)]'
		);

		foreach ( $custom_controls as $control ) {
			$control->setAttribute( 'tabindex', '0' );
			$control->setAttribute( 'data-slos-keyboard-control', 'true' );
			++$fixed_count;
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Touch Target Fixer
 * Ensures touch targets meet minimum size requirements (WCAG 2.5.5)
 */
class TouchTargetFixer extends BaseFixer {
	/**
	 * WCAG 2.1 minimum touch target size (44x44px)
	 */
	private const MIN_SIZE = 44;

	public function get_id() {
		return 'touch-target';
	}

	public function get_description() {
		return 'Ensures touch targets meet minimum size requirements (44x44px)';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Find interactive elements with explicit small sizes.
		$interactive = $xpath->query(
			'//a[@style] | //button[@style] | //input[@type="submit" or @type="button"][@style] | ' .
			'//*[@onclick][@style] | //*[@role="button"][@style]'
		);

		foreach ( $interactive as $element ) {
			if ( $this->fix_small_target( $element ) ) {
				++$fixed_count;
			}
		}

		// Add wrapper styling for checkboxes/radios (often too small).
		$checkboxes = $xpath->query( '//input[@type="checkbox" or @type="radio"]' );
		foreach ( $checkboxes as $checkbox ) {
			if ( $this->fix_checkbox_radio( $checkbox, $xpath ) ) {
				++$fixed_count;
			}
		}

		// Fix icon-only buttons.
		$icon_buttons = $xpath->query(
			'//button[not(normalize-space(text()))] | ' .
			'//a[not(normalize-space(text()))][contains(@class, "icon") or contains(@class, "btn")]'
		);

		foreach ( $icon_buttons as $btn ) {
			if ( $this->fix_icon_button( $btn ) ) {
				++$fixed_count;
			}
		}

		// Inject touch target CSS if fixes were made.
		if ( $fixed_count > 0 ) {
			$this->inject_touch_styles( $dom );
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix elements with explicit small dimensions.
	 *
	 * @param \DOMElement $element The element to fix.
	 * @return bool Whether a fix was applied.
	 */
	private function fix_small_target( \DOMElement $element ) {
		$style     = $element->getAttribute( 'style' );
		$needs_fix = false;

		// Check for explicit small width.
		if ( preg_match( '/width\s*:\s*(\d+)(px)?/i', $style, $match ) ) {
			if ( (int) $match[1] < self::MIN_SIZE ) {
				$needs_fix = true;
			}
		}

		// Check for explicit small height.
		if ( preg_match( '/height\s*:\s*(\d+)(px)?/i', $style, $match ) ) {
			if ( (int) $match[1] < self::MIN_SIZE ) {
				$needs_fix = true;
			}
		}

		// Check for small padding that results in small target.
		if ( preg_match( '/padding\s*:\s*(\d+)(px)?/i', $style, $match ) ) {
			if ( (int) $match[1] < 10 ) {
				$needs_fix = true;
			}
		}

		if ( $needs_fix ) {
			// Add minimum size constraints.
			$style = rtrim( $style, '; ' );
			$style .= '; min-width: ' . self::MIN_SIZE . 'px; min-height: ' . self::MIN_SIZE . 'px;';
			$element->setAttribute( 'style', $style );
			$element->setAttribute( 'data-slos-touch-fixed', 'true' );
			return true;
		}

		return false;
	}

	/**
	 * Fix checkbox/radio inputs by adding touch-friendly class.
	 *
	 * @param \DOMElement $input The input element.
	 * @param \DOMXPath   $xpath The XPath instance.
	 * @return bool Whether a fix was applied.
	 */
	private function fix_checkbox_radio( \DOMElement $input, \DOMXPath $xpath ) {
		// Skip if already wrapped or has adequate styling.
		$parent = $input->parentNode;
		if ( $parent instanceof \DOMElement ) {
			$parent_class = $parent->getAttribute( 'class' );
			$parent_style = $parent->getAttribute( 'style' );
			$parent_tag   = strtolower( $parent->tagName );

			// Skip if parent is a wrapper with touch styles.
			if ( strpos( $parent_class, 'slos-touch-wrapper' ) !== false ) {
				return false;
			}

			// Skip if parent label already has adequate min-width/min-height.
			if ( $parent_tag === 'label' ) {
				if ( preg_match( '/min-(width|height)\s*:\s*(\d+)(px)?/i', $parent_style, $match ) ) {
					if ( (int) $match[2] >= self::MIN_SIZE ) {
						return false;
					}
				}
				// Skip if label has adequate padding.
				if ( preg_match( '/padding\s*:\s*(\d+)(px)?/i', $parent_style, $match ) ) {
					if ( (int) $match[1] >= 8 ) {
						return false;
					}
				}
			}
		}

		// Skip if already has adequate styling.
		if ( $input->hasAttribute( 'data-slos-touch-fixed' ) ) {
			return false;
		}

		// Add touch-friendly class to input.
		$class = $input->getAttribute( 'class' );
		$input->setAttribute( 'class', trim( $class . ' slos-touch-input' ) );
		$input->setAttribute( 'data-slos-touch-fixed', 'true' );

		return true;
	}

	/**
	 * Fix icon-only buttons with minimum dimensions.
	 *
	 * @param \DOMElement $button The button element.
	 * @return bool Whether a fix was applied.
	 */
	private function fix_icon_button( \DOMElement $button ) {
		if ( $button->hasAttribute( 'data-slos-touch-fixed' ) ) {
			return false;
		}

		$style = $button->getAttribute( 'style' ) ?: '';

		// Add minimum dimensions.
		if ( strpos( $style, 'min-width' ) === false ) {
			$style = rtrim( $style, '; ' );
			if ( ! empty( $style ) ) {
				$style .= '; ';
			}
			$style .= 'min-width: ' . self::MIN_SIZE . 'px; min-height: ' . self::MIN_SIZE . 'px;';
			$button->setAttribute( 'style', $style );
			$button->setAttribute( 'data-slos-touch-fixed', 'true' );
			return true;
		}

		return false;
	}

	/**
	 * Inject touch target CSS styles into the document.
	 *
	 * @param \DOMDocument $dom The DOM document.
	 */
	private function inject_touch_styles( \DOMDocument $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-touch-styles]' );
		if ( $existing->length > 0 ) {
			return;
		}

		$style = $dom->createElement( 'style' );
		$style->setAttribute( 'data-slos-touch-styles', 'true' );
		$style->textContent = '
/* SLOS Touch Target Fixes */
[data-slos-touch-fixed] {
    min-width: 44px !important;
    min-height: 44px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.slos-touch-input {
    min-width: 24px;
    min-height: 24px;
    cursor: pointer;
}

.slos-touch-input + label,
label:has(.slos-touch-input) {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    padding: 8px;
    cursor: pointer;
}

/* Spacing between adjacent touch targets */
[data-slos-touch-fixed] + [data-slos-touch-fixed] {
    margin-left: 8px;
}

/* Larger targets on touch devices */
@media (pointer: coarse) {
    [data-slos-touch-fixed] {
        min-width: 48px !important;
        min-height: 48px !important;
    }
    
    .slos-touch-input {
        min-width: 28px;
        min-height: 28px;
    }
}
';

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$head->appendChild( $style );
		}
	}
}

/**
 * Touch Gesture Fixer
 */
class TouchGestureFixer extends BaseFixer {
	public function get_id() {
		return 'touch-gesture'; }
	public function get_description() {
		return 'Provide alternatives to complex gestures'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Find swipeable/slideable containers
		$swipeable = $xpath->query(
			'//*[contains(@class, "swipe") or contains(@class, "slider") or ' .
			'contains(@class, "carousel") or contains(@class, "gallery")]' .
			'[not(descendant::button[contains(@class, "prev") or contains(@class, "next")])]'
		);

		foreach ( $swipeable as $container ) {
			if ( $this->add_navigation_buttons( $container, $dom ) ) {
				++$fixed_count;
			}
		}

		// Find pinch-zoom images and add zoom controls
		$zoomable = $xpath->query(
			'//img[contains(@class, "zoom") or contains(@data-action, "zoom")]' .
			'[not(ancestor::*[@data-slos-zoom-controls])]'
		);

		foreach ( $zoomable as $img ) {
			if ( $this->add_zoom_controls( $img, $dom ) ) {
				++$fixed_count;
			}
		}

		// Inject gesture alternative CSS if fixes were made
		if ( $fixed_count > 0 ) {
			$this->inject_gesture_styles( $dom );
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Add navigation buttons to swipeable container
	 *
	 * @param \DOMElement  $container The container element.
	 * @param \DOMDocument $dom       The DOM document.
	 * @return bool Whether buttons were added.
	 */
	private function add_navigation_buttons( \DOMElement $container, \DOMDocument $dom ) {
		if ( $container->hasAttribute( 'data-slos-gesture-fixed' ) ) {
			return false;
		}

		// Create previous button
		$prev_btn = $dom->createElement( 'button' );
		$prev_btn->setAttribute( 'type', 'button' );
		$prev_btn->setAttribute( 'class', 'slos-swipe-prev' );
		$prev_btn->setAttribute( 'aria-label', 'Previous' );
		$prev_btn->setAttribute( 'data-slos-swipe-action', 'prev' );
		$prev_btn->textContent = '‹';

		// Create next button
		$next_btn = $dom->createElement( 'button' );
		$next_btn->setAttribute( 'type', 'button' );
		$next_btn->setAttribute( 'class', 'slos-swipe-next' );
		$next_btn->setAttribute( 'aria-label', 'Next' );
		$next_btn->setAttribute( 'data-slos-swipe-action', 'next' );
		$next_btn->textContent = '›';

		// Prepend/append buttons
		if ( $container->firstChild ) {
			$container->insertBefore( $prev_btn, $container->firstChild );
		} else {
			$container->appendChild( $prev_btn );
		}
		$container->appendChild( $next_btn );

		$container->setAttribute( 'data-slos-gesture-fixed', 'true' );
		return true;
	}

	/**
	 * Add zoom controls to image
	 *
	 * @param \DOMElement  $img The image element.
	 * @param \DOMDocument $dom The DOM document.
	 * @return bool Whether controls were added.
	 */
	private function add_zoom_controls( \DOMElement $img, \DOMDocument $dom ) {
		$parent = $img->parentNode;
		if ( ! $parent instanceof \DOMElement ) {
			return false;
		}

		// Create wrapper
		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'slos-zoom-wrapper' );
		$wrapper->setAttribute( 'data-slos-zoom-controls', 'true' );

		// Clone image
		$img_clone = $img->cloneNode( true );
		$wrapper->appendChild( $img_clone );

		// Create zoom buttons
		$zoom_in = $dom->createElement( 'button' );
		$zoom_in->setAttribute( 'type', 'button' );
		$zoom_in->setAttribute( 'class', 'slos-zoom-in' );
		$zoom_in->setAttribute( 'aria-label', 'Zoom in' );
		$zoom_in->textContent = '+';

		$zoom_out = $dom->createElement( 'button' );
		$zoom_out->setAttribute( 'type', 'button' );
		$zoom_out->setAttribute( 'class', 'slos-zoom-out' );
		$zoom_out->setAttribute( 'aria-label', 'Zoom out' );
		$zoom_out->textContent = '−';

		$wrapper->appendChild( $zoom_in );
		$wrapper->appendChild( $zoom_out );

		// Replace original image
		$parent->replaceChild( $wrapper, $img );

		return true;
	}

	/**
	 * Inject CSS styles for gesture controls
	 *
	 * @param \DOMDocument $dom The DOM document.
	 */
	private function inject_gesture_styles( \DOMDocument $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-gesture-styles]' );
		if ( $existing->length > 0 ) {
			return;
		}

		$style = $dom->createElement( 'style' );
		$style->setAttribute( 'data-slos-gesture-styles', 'true' );
		$style->textContent = '
/* SLOS Gesture Alternative Styles */
.slos-swipe-prev, .slos-swipe-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    width: 44px;
    height: 44px;
    font-size: 24px;
    cursor: pointer;
    z-index: 10;
}

.slos-swipe-prev:hover, .slos-swipe-next:hover {
    background: rgba(0,0,0,0.7);
}

.slos-swipe-prev { left: 10px; }
.slos-swipe-next { right: 10px; }

.slos-zoom-wrapper {
    position: relative;
    display: inline-block;
}

.slos-zoom-in, .slos-zoom-out {
    position: absolute;
    bottom: 10px;
    background: rgba(255,255,255,0.9);
    border: 1px solid #ccc;
    width: 36px;
    height: 36px;
    font-size: 20px;
    cursor: pointer;
}

.slos-zoom-in { right: 50px; }
.slos-zoom-out { right: 10px; }
';

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$head->appendChild( $style );
		}
	}
}

/**
 * Viewport Fixer
 * Ensures viewport meta allows zooming and scaling (WCAG 1.4.4)
 */
class ViewportFixer extends BaseFixer {
	public function get_id() {
		return 'viewport';
	}

	public function get_description() {
		return 'Ensures viewport meta allows zooming and scaling';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$fixed_count = 0;

		$metas          = $dom->getElementsByTagName( 'meta' );
		$viewport_found = false;

		foreach ( $metas as $meta ) {
			if ( strtolower( $meta->getAttribute( 'name' ) ) === 'viewport' ) {
				$viewport_found = true;
				$content_attr   = $meta->getAttribute( 'content' );
				$fixed_content  = $this->fix_viewport_content( $content_attr );

				if ( $fixed_content !== $content_attr ) {
					$meta->setAttribute( 'content', $fixed_content );
					$meta->setAttribute( 'data-slos-viewport-fixed', 'true' );
					++$fixed_count;
				}
			}
		}

		// Add viewport meta if missing
		if ( ! $viewport_found ) {
			$head = $dom->getElementsByTagName( 'head' )->item( 0 );
			if ( $head ) {
				$viewport = $dom->createElement( 'meta' );
				$viewport->setAttribute( 'name', 'viewport' );
				$viewport->setAttribute( 'content', 'width=device-width, initial-scale=1.0, user-scalable=yes' );
				$viewport->setAttribute( 'data-slos-viewport-fixed', 'true' );
				$head->appendChild( $viewport );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix viewport content to allow zooming
	 *
	 * @param string $content Current viewport content.
	 * @return string Fixed viewport content.
	 */
	private function fix_viewport_content( $content ) {
		$parts          = array_map( 'trim', explode( ',', $content ) );
		$viewport_props = array();

		foreach ( $parts as $part ) {
			if ( strpos( $part, '=' ) !== false ) {
				list( $key, $value ) = array_map( 'trim', explode( '=', $part, 2 ) );
				$viewport_props[ strtolower( $key ) ] = $value;
			}
		}

		$needs_fix = false;

		// Check for user-scalable=no or 0
		if ( isset( $viewport_props['user-scalable'] ) ) {
			$val = strtolower( $viewport_props['user-scalable'] );
			if ( $val === 'no' || $val === '0' ) {
				$viewport_props['user-scalable'] = 'yes';
				$needs_fix                       = true;
			}
		} else {
			// Ensure user-scalable=yes is present
			$viewport_props['user-scalable'] = 'yes';
			$needs_fix                       = true;
		}

		// Check for maximum-scale restrictions (< 2.0 is too restrictive)
		if ( isset( $viewport_props['maximum-scale'] ) ) {
			$max_scale = floatval( $viewport_props['maximum-scale'] );
			if ( $max_scale < 2.0 ) {
				$viewport_props['maximum-scale'] = '5.0';
				$needs_fix                       = true;
			}
		}

		// Check for minimum-scale that's too high (> 0.5 restricts zoom out)
		if ( isset( $viewport_props['minimum-scale'] ) ) {
			$min_scale = floatval( $viewport_props['minimum-scale'] );
			if ( $min_scale > 0.5 ) {
				unset( $viewport_props['minimum-scale'] );
				$needs_fix = true;
			}
		}

		if ( ! $needs_fix ) {
			return $content;
		}

		// Rebuild content string
		$result = array();
		foreach ( $viewport_props as $key => $value ) {
			$result[] = "{$key}={$value}";
		}

		return implode( ', ', $result );
	}
}

