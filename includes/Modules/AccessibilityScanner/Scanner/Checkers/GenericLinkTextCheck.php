<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for generic link text
 * WCAG 2.4.4 - Link Purpose (In Context) (Level A)
 */
class GenericLinkTextCheck extends AbstractCheck {

	/**
	 * Generic phrases that don't describe the link purpose
	 */
	private $generic_phrases = array(
		// Original
		'click here',
		'read more',
		'learn more',
		'more',
		'here',
		'link',
		'go',
		'continue reading',

		// Action words without context
		'click',
		'click this',
		'click me',
		'tap here',
		'press here',
		'select',
		'start',
		'begin',
		'continue',
		'open',

		// Vague references
		'this link',
		'this page',
		'this',
		'that',

		// Read/view variations
		'see more',
		'view more',
		'view all',
		'see all',
		'show more',
		'show all',
		'read',
		'view',

		// Download without context
		'download',
		'download here',
		'download now',
		'get it',
		'get it here',

		// Information references
		'info',
		'information',
		'details',
		'more details',
		'more info',
		'more information',
		'find out',
		'find out more',
		'discover',
		'discover more',

		// Article references
		'full article',
		'full story',
		'full post',
		'article',
		'story',
		'post',

		// Symbols and arrows
		'>>',
		'»',
		'...',
		'→',
		'>',
		'«',
		'←',
		'<<<',
		'>>>',
	);

	/**
	 * Minimum meaningful link text length
	 */
	private $min_text_length = 3;

	public function get_id() {
		return 'generic-link-text';
	}

	public function get_description() {
		return 'Links should have descriptive text that explains their purpose, avoiding generic phrases like "click here".';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '2.4.4';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Replace generic link text with descriptive text that tells users where the link goes. E.g., "Download annual report" instead of "Click here".';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );

			// Skip anchor links and javascript
			if ( empty( $href ) || strpos( $href, '#' ) === 0 || strpos( $href, 'javascript:' ) === 0 ) {
				continue;
			}

			$text       = trim( $link->textContent );
			$clean_text = strtolower( preg_replace( '/\s+/', ' ', $text ) );

			// Check for generic phrases
			if ( in_array( $clean_text, $this->generic_phrases, true ) ) {
				// Check if aria-label provides context
				if ( $this->has_accessible_context( $link ) ) {
					continue;
				}

				$issues[] = array(
					'element'    => 'a',
					'context'    => $this->get_element_html( $link ),
					'message'    => "Generic link text: \"$text\". Use descriptive text that explains the link destination.",
					'href'       => $href,
					'confidence' => 'high',
				);
				continue;
			}

			// Check for very short links (1-2 characters, excluding numbers)
			$text_length = mb_strlen( trim( $text ) );
			if ( $text_length > 0 && $text_length < $this->min_text_length ) {
				// Allow single numbers (pagination)
				if ( preg_match( '/^\d+$/', $text ) ) {
					continue;
				}

				// Allow if has aria-label
				if ( $this->has_accessible_context( $link ) ) {
					continue;
				}

				$issues[] = array(
					'element'    => 'a',
					'context'    => $this->get_element_html( $link ),
					'message'    => "Link text is too short: \"$text\". Provide more descriptive text.",
					'href'       => $href,
					'severity'   => 'notice',
					'confidence' => 'medium',
				);
				continue;
			}

			// Check for URL as link text
			if ( preg_match( '/^https?:\/\//i', $text ) || preg_match( '/^www\./i', $text ) ) {
				$issues[] = array(
					'element'    => 'a',
					'context'    => $this->get_element_html( $link ),
					'message'    => 'Link text is a URL. Use descriptive text instead of showing the raw URL.',
					'href'       => $href,
					'severity'   => 'notice',
					'confidence' => 'high',
				);
			}

			// Check for identical link text with different destinations
			// (This is tracked per-scan, would need session state to implement fully)
		}

		return $issues;
	}

	/**
	 * Check if link has accessible context via aria-label or aria-labelledby
	 */
	private function has_accessible_context( $link ) {
		// Check aria-label
		$aria_label = trim( $link->getAttribute( 'aria-label' ) );
		if ( ! empty( $aria_label ) && strlen( $aria_label ) >= $this->min_text_length ) {
			return true;
		}

		// Check aria-labelledby
		$aria_labelledby = trim( $link->getAttribute( 'aria-labelledby' ) );
		if ( ! empty( $aria_labelledby ) ) {
			return true;
		}

		// Check title (fallback, less preferred)
		$title = trim( $link->getAttribute( 'title' ) );
		if ( ! empty( $title ) && strlen( $title ) >= $this->min_text_length ) {
			return true;
		}

		return false;
	}

	protected function get_element_html( $node, $max_length = 200 ) {
		$html = $node->ownerDocument->saveHTML( $node );
		return strlen( $html ) > $max_length ? substr( $html, 0, $max_length ) . '...' : $html;
	}
}

