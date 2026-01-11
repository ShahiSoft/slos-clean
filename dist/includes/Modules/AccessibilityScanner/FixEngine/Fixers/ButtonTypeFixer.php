<?php
/**
 * Button Type Fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ButtonTypeFixer
 *
 * Adds type attribute to button elements and ensures accessible names.
 */
final class ButtonTypeFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-button-type';
	}

	public function get_name(): string {
		return __( 'Button Accessibility', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds type attribute to buttons without explicit type and ensures all buttons have accessible names.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '4.1.2' );
	}

	public function get_category(): string {
		return 'interactive';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<button' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$buttons       = $this->query( '//button' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $buttons as $button ) {
			$modified    = false;
			$fix_details = array();

			// Add type if missing
			if ( ! $button->hasAttribute( 'type' ) ) {
				$button->setAttribute( 'type', 'button' );
				$modified                  = true;
				$fix_details['type_added'] = 'button';
			}

			// Check for accessible name
			if ( ! $this->has_accessible_name( $button ) ) {
				$name = $this->derive_button_name( $button );
				if ( ! empty( $name ) ) {
					$button->setAttribute( 'aria-label', $name );
					$modified                        = true;
					$fix_details['aria_label_added'] = $name;
				}
			}

			if ( $modified ) {
				++$fixes_applied;
				$details[] = array_merge(
					array( 'button_class' => $button->getAttribute( 'class' ) ),
					$fix_details
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No buttons needing fixes', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Check if button has accessible name
	 *
	 * @param \DOMElement $button
	 * @return bool
	 */
	protected function has_accessible_name( \DOMElement $button ): bool {
		// Has text content
		$text = trim( $button->textContent );
		if ( ! empty( $text ) ) {
			return true;
		}

		// Has aria-label
		if ( ! empty( $button->getAttribute( 'aria-label' ) ) ) {
			return true;
		}

		// Has aria-labelledby
		if ( ! empty( $button->getAttribute( 'aria-labelledby' ) ) ) {
			return true;
		}

		// Has title
		if ( ! empty( $button->getAttribute( 'title' ) ) ) {
			return true;
		}

		// Has image with alt
		$images = $button->getElementsByTagName( 'img' );
		foreach ( $images as $img ) {
			if ( ! empty( $img->getAttribute( 'alt' ) ) ) {
				return true;
			}
		}

		// Has SVG with title
		$svgs = $button->getElementsByTagName( 'svg' );
		foreach ( $svgs as $svg ) {
			$titles = $svg->getElementsByTagName( 'title' );
			if ( $titles->length > 0 && ! empty( trim( $titles->item( 0 )->textContent ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Derive button name from context
	 *
	 * @param \DOMElement $button
	 * @return string
	 */
	private function derive_button_name( \DOMElement $button ): string {
		// Check title
		$title = $button->getAttribute( 'title' );
		if ( ! empty( $title ) ) {
			return $title;
		}

		// Check for common icon classes
		$class = strtolower( $button->getAttribute( 'class' ) );

		$icon_mappings = array(
			'close'      => __( 'Close', 'shahi-legalflowsuite' ),
			'dismiss'    => __( 'Dismiss', 'shahi-legalflowsuite' ),
			'menu'       => __( 'Menu', 'shahi-legalflowsuite' ),
			'hamburger'  => __( 'Menu', 'shahi-legalflowsuite' ),
			'toggle'     => __( 'Toggle', 'shahi-legalflowsuite' ),
			'search'     => __( 'Search', 'shahi-legalflowsuite' ),
			'submit'     => __( 'Submit', 'shahi-legalflowsuite' ),
			'send'       => __( 'Send', 'shahi-legalflowsuite' ),
			'play'       => __( 'Play', 'shahi-legalflowsuite' ),
			'pause'      => __( 'Pause', 'shahi-legalflowsuite' ),
			'prev'       => __( 'Previous', 'shahi-legalflowsuite' ),
			'next'       => __( 'Next', 'shahi-legalflowsuite' ),
			'back'       => __( 'Back', 'shahi-legalflowsuite' ),
			'forward'    => __( 'Forward', 'shahi-legalflowsuite' ),
			'refresh'    => __( 'Refresh', 'shahi-legalflowsuite' ),
			'reload'     => __( 'Reload', 'shahi-legalflowsuite' ),
			'delete'     => __( 'Delete', 'shahi-legalflowsuite' ),
			'remove'     => __( 'Remove', 'shahi-legalflowsuite' ),
			'add'        => __( 'Add', 'shahi-legalflowsuite' ),
			'plus'       => __( 'Add', 'shahi-legalflowsuite' ),
			'minus'      => __( 'Remove', 'shahi-legalflowsuite' ),
			'edit'       => __( 'Edit', 'shahi-legalflowsuite' ),
			'settings'   => __( 'Settings', 'shahi-legalflowsuite' ),
			'config'     => __( 'Configure', 'shahi-legalflowsuite' ),
			'expand'     => __( 'Expand', 'shahi-legalflowsuite' ),
			'collapse'   => __( 'Collapse', 'shahi-legalflowsuite' ),
			'fullscreen' => __( 'Fullscreen', 'shahi-legalflowsuite' ),
			'download'   => __( 'Download', 'shahi-legalflowsuite' ),
			'upload'     => __( 'Upload', 'shahi-legalflowsuite' ),
			'share'      => __( 'Share', 'shahi-legalflowsuite' ),
			'copy'       => __( 'Copy', 'shahi-legalflowsuite' ),
			'print'      => __( 'Print', 'shahi-legalflowsuite' ),
			'help'       => __( 'Help', 'shahi-legalflowsuite' ),
			'info'       => __( 'Information', 'shahi-legalflowsuite' ),
			'cart'       => __( 'Shopping cart', 'shahi-legalflowsuite' ),
			'favorite'   => __( 'Favorite', 'shahi-legalflowsuite' ),
			'like'       => __( 'Like', 'shahi-legalflowsuite' ),
			'heart'      => __( 'Favorite', 'shahi-legalflowsuite' ),
			'star'       => __( 'Star', 'shahi-legalflowsuite' ),
			'bookmark'   => __( 'Bookmark', 'shahi-legalflowsuite' ),
			'save'       => __( 'Save', 'shahi-legalflowsuite' ),
		);

		foreach ( $icon_mappings as $pattern => $label ) {
			if ( strpos( $class, $pattern ) !== false ) {
				return $label;
			}
		}

		// Check for Font Awesome icons
		if ( preg_match( '/fa-(\w+)/', $class, $matches ) ) {
			$icon_name = str_replace( '-', ' ', $matches[1] );
			return ucwords( $icon_name );
		}

		return __( 'Button', 'shahi-legalflowsuite' );
	}
}
