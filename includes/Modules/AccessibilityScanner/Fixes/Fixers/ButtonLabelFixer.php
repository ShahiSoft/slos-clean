<?php
/**
 * Button Label Fixer
 *
 * Adds accessible labels to buttons and interactive elements.
 * WCAG 2.5.3 Label in Name (Level A) & 4.1.2 Name, Role, Value (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ButtonLabelFixer Class
 *
 * Adds accessible labels to buttons without text content.
 */
class ButtonLabelFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'button-label';
	}

	/**
	 * Get fixer name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Button Labels';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds accessible labels to buttons and interactive elements';
	}

	/**
	 * Fix button labels in content
	 *
	 * @param string $content Post content
	 * @return array Fix result with content and count
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixes_applied = 0;

		// Find buttons without accessible text
		$buttons = $xpath->query( '//button[not(normalize-space(text())) and not(@aria-label) and not(@aria-labelledby) and not(@title)]' );

		foreach ( $buttons as $button ) {
			$label = $this->generate_button_label( $button );
			$button->setAttribute( 'aria-label', $label );
			$fixes_applied++;
		}

		// Find input buttons without value or aria-label
		$input_buttons = $xpath->query( '//input[@type="button" or @type="submit" or @type="reset"][not(@value) and not(@aria-label)]' );

		foreach ( $input_buttons as $input ) {
			$type  = $input->getAttribute( 'type' );
			$label = ucfirst( $type );
			
			if ( $type === 'submit' ) {
				$label = 'Submit';
			} elseif ( $type === 'reset' ) {
				$label = 'Reset';
			} else {
				$label = 'Button';
			}

			$input->setAttribute( 'value', $label );
			$fixes_applied++;
		}

		// Find links that look like buttons without text
		$link_buttons = $xpath->query( '//a[contains(@class, "btn") or contains(@class, "button")][not(normalize-space(text())) and not(@aria-label)]' );

		foreach ( $link_buttons as $link ) {
			$label = $this->generate_button_label( $link );
			$link->setAttribute( 'aria-label', $label );
			$fixes_applied++;
		}

		return $this->return_result( $this->dom_to_html( $dom ), $fixes_applied );
	}

	/**
	 * Generate button label from context
	 *
	 * @param \DOMElement $button Button element
	 * @return string Generated label
	 */
	private function generate_button_label( $button ) {
		// Check for title attribute
		if ( $button->hasAttribute( 'title' ) ) {
			return trim( $button->getAttribute( 'title' ) );
		}

		// Check for icon classes that might indicate purpose
		$class = $button->getAttribute( 'class' );
		
		$icon_patterns = array(
			'close|dismiss|exit'  => 'Close',
			'menu|nav|hamburger'  => 'Menu',
			'search'              => 'Search',
			'play'                => 'Play',
			'pause'               => 'Pause',
			'stop'                => 'Stop',
			'next|forward'        => 'Next',
			'prev|previous|back'  => 'Previous',
			'edit|pencil'         => 'Edit',
			'delete|trash|remove' => 'Delete',
			'save|floppy'         => 'Save',
			'download'            => 'Download',
			'upload'              => 'Upload',
			'share'               => 'Share',
			'print'               => 'Print',
			'expand|plus'         => 'Expand',
			'collapse|minus'      => 'Collapse',
			'refresh|reload'      => 'Refresh',
			'home'                => 'Home',
			'cart|shopping'       => 'Shopping Cart',
			'user|profile'        => 'User Profile',
			'settings|config'     => 'Settings',
		);

		foreach ( $icon_patterns as $pattern => $label ) {
			if ( preg_match( "/$pattern/i", $class ) ) {
				return $label;
			}
		}

		// Check for child icons
		if ( $button->getElementsByTagName( 'i' )->length > 0 ||
		     $button->getElementsByTagName( 'svg' )->length > 0 ) {
			$icon = $button->getElementsByTagName( 'i' )->item( 0 );
			if ( $icon ) {
				$icon_class = $icon->getAttribute( 'class' );
				foreach ( $icon_patterns as $pattern => $label ) {
					if ( preg_match( "/$pattern/i", $icon_class ) ) {
						return $label;
					}
				}
			}
		}

		// Check data attributes
		foreach ( array( 'data-action', 'data-label', 'data-title' ) as $attr ) {
			if ( $button->hasAttribute( $attr ) ) {
				$value = trim( $button->getAttribute( $attr ) );
				if ( ! empty( $value ) ) {
					return ucfirst( str_replace( array( '-', '_' ), ' ', $value ) );
				}
			}
		}

		// Default fallback
		return 'Button';
	}
}
