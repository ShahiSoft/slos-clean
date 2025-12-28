<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for skip navigation links
 * WCAG 2.4.1 - Bypass Blocks (Level A)
 */
class SkipLinkCheck extends AbstractCheck {

	/**
	 * Common skip link text patterns (case-insensitive)
	 */
	private $skip_patterns = array(
		'skip',
		'jump to',
		'go to main',
		'go to content',
		'bypass',
		'saltar',          // Spanish
		'aller au contenu', // French
		'zum inhalt',      // German
	);

	public function get_id() {
		return 'skip-link';
	}

	public function get_description() {
		return 'Pages should have a "Skip to Content" link to bypass repeated navigation blocks.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.1';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Add a skip link as the first focusable element: <a href="#main-content" class="skip-link">Skip to content</a>';
	}

	public function get_confidence() {
		return 'high';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );

		// Only check if this appears to be a full page (has nav/header)
		if ( ! $this->appears_to_be_full_page( $dom ) ) {
			return $issues;
		}

		$links     = $dom->getElementsByTagName( 'a' );
		$skip_link = $this->find_skip_link( $links );

		if ( ! $skip_link ) {
			$issues[] = array(
				'element'    => 'page',
				'context'    => 'Page structure',
				'message'    => 'No "Skip to Content" link found. Add one as the first focusable element to allow keyboard users to bypass navigation.',
				'confidence' => 'high',
			);
		} else {
			// Validate skip link target exists
			$target_issue = $this->validate_skip_link_target( $dom, $skip_link );
			if ( $target_issue ) {
				$issues[] = $target_issue;
			}

			// Check if skip link is early in the DOM (should be first or second focusable)
			$position_issue = $this->check_skip_link_position( $dom, $skip_link );
			if ( $position_issue ) {
				$issues[] = $position_issue;
			}
		}

		return $issues;
	}

	/**
	 * Check if content appears to be a full page with navigation
	 */
	private function appears_to_be_full_page( $dom ) {
		$nav_elements    = $dom->getElementsByTagName( 'nav' );
		$header_elements = $dom->getElementsByTagName( 'header' );
		$html_element    = $dom->getElementsByTagName( 'html' );
		$body_element    = $dom->getElementsByTagName( 'body' );

		// Has structural elements suggesting full page
		if ( $html_element->length > 0 || $body_element->length > 0 ) {
			return true;
		}

		// Has navigation that would need to be skipped
		if ( $nav_elements->length > 0 || $header_elements->length > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Find skip link in the page
	 */
	private function find_skip_link( $links ) {
		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );

			// Must be an anchor link
			if ( strpos( $href, '#' ) !== 0 || $href === '#' ) {
				continue;
			}

			// Check link text
			$text = strtolower( trim( $link->textContent ) );

			// Check aria-label as well
			$aria_label = strtolower( trim( $link->getAttribute( 'aria-label' ) ) );

			$text_to_check = $text . ' ' . $aria_label;

			foreach ( $this->skip_patterns as $pattern ) {
				if ( strpos( $text_to_check, $pattern ) !== false ) {
					return $link;
				}
			}
		}

		return null;
	}

	/**
	 * Validate that the skip link target exists
	 */
	private function validate_skip_link_target( $dom, $skip_link ) {
		$href      = $skip_link->getAttribute( 'href' );
		$target_id = substr( $href, 1 ); // Remove #

		if ( empty( $target_id ) ) {
			return null;
		}

		// Find element with this ID
		$xpath   = new \DOMXPath( $dom );
		$targets = $xpath->query( "//*[@id='$target_id']" );

		if ( $targets->length === 0 ) {
			return array(
				'element'    => 'a',
				'context'    => $this->get_element_html( $skip_link ),
				'message'    => "Skip link target '#$target_id' not found. Ensure the target element exists.",
				'confidence' => 'high',
			);
		}

		return null;
	}

	/**
	 * Check if skip link is positioned early in the document
	 */
	private function check_skip_link_position( $dom, $skip_link ) {
		$xpath             = new \DOMXPath( $dom );
		$focusable_query   = '//a[@href] | //button | //input | //select | //textarea | //*[@tabindex >= "0"]';
		$focusable_elements = $xpath->query( $focusable_query );

		$position = 0;
		foreach ( $focusable_elements as $index => $element ) {
			if ( $element->isSameNode( $skip_link ) ) {
				$position = $index + 1;
				break;
			}
		}

		// Skip link should be within first 3 focusable elements
		if ( $position > 3 ) {
			return array(
				'element'    => 'a',
				'context'    => $this->get_element_html( $skip_link ),
				'message'    => "Skip link found but it's the #{$position} focusable element. It should be one of the first focusable elements.",
				'confidence' => 'medium',
				'severity'   => 'notice',
			);
		}

		return null;
	}

	private function get_element_html( $node ) {
		$html = $node->ownerDocument->saveHTML( $node );
		return strlen( $html ) > 150 ? substr( $html, 0, 150 ) . '...' : $html;
	}
}

