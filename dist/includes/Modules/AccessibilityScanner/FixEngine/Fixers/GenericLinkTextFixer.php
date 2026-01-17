<?php
/**
 * Generic Link Text Fixer
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
 * Class GenericLinkTextFixer
 *
 * Fixes links with generic text like "click here", "read more", etc.
 */
final class GenericLinkTextFixer extends AbstractFixer {

	/** @var array Generic link texts to fix */
	private const GENERIC_TEXTS = array(
		'click here',
		'click',
		'here',
		'read more',
		'more',
		'learn more',
		'see more',
		'view more',
		'details',
		'more details',
		'link',
		'this link',
		'continue',
		'continue reading',
		'go',
		'info',
		'more info',
		'more information',
	);

	public function get_id(): string {
		return 'generic-link-text';
	}

	public function get_name(): string {
		return __( 'Generic Link Text', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Enhances links with generic text (like "click here" or "read more") with more descriptive accessible names.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '2.4.4', '2.4.9' );
	}

	public function get_category(): string {
		return 'links';
	}

	public function can_fix( string $content ): bool {
		$pattern = '/\b(' . implode( '|', array_map( 'preg_quote', self::GENERIC_TEXTS ) ) . ')\b/i';
		return (bool) preg_match( $pattern, $content );
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$links         = $this->query( '//a[@href]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $links as $link ) {
			$link_text = strtolower( trim( $link->textContent ) );

			if ( ! $this->is_generic_text( $link_text ) ) {
				continue;
			}

			// Already has aria-label..
			if ( ! empty( $link->getAttribute( 'aria-label' ) ) ) {
				continue;
			}

			$descriptive_text = $this->get_descriptive_text( $link, $link_text );

			if ( ! empty( $descriptive_text ) && strtolower( $descriptive_text ) !== $link_text ) {
				$link->setAttribute( 'aria-label', $descriptive_text );
				++$fixes_applied;

				$details[] = array(
					'original_text'    => $link_text,
					'descriptive_text' => $descriptive_text,
					'href'             => $link->getAttribute( 'href' ),
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No generic link text found', $content );
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
	 * Check if text is generic
	 *
	 * @param string $text
	 * @return bool
	 */
	private function is_generic_text( string $text ): bool {
		$text = strtolower( trim( $text ) );

		// Remove punctuation for comparison..
		$text = preg_replace( '/[^\w\s]/', '', $text );

		foreach ( self::GENERIC_TEXTS as $generic ) {
			if ( $text === $generic ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get descriptive text from context
	 *
	 * @param \DOMElement $link
	 * @param string      $original_text
	 * @return string
	 */
	private function get_descriptive_text( \DOMElement $link, string $original_text ): string {
		// Check title attribute..
		$title = $link->getAttribute( 'title' );
		if ( ! empty( $title ) ) {
			return $title;
		}

		// Look at parent context..
		$parent = $link->parentNode;

		// Check for nearby heading..
		$heading_text = $this->find_nearby_heading( $link );
		if ( ! empty( $heading_text ) ) {
			return $this->combine_with_original( $original_text, $heading_text );
		}

		// Check parent paragraph/list item..
		if ( $parent ) {
			$parent_text = $this->get_context_from_parent( $parent, $link );
			if ( ! empty( $parent_text ) ) {
				return $this->combine_with_original( $original_text, $parent_text );
			}
		}

		// Try to extract from URL..
		$href        = $link->getAttribute( 'href' );
		$url_context = $this->get_context_from_url( $href );
		if ( ! empty( $url_context ) ) {
			return $this->combine_with_original( $original_text, $url_context );
		}

		return '';
	}

	/**
	 * Find nearby heading
	 *
	 * @param \DOMElement $link
	 * @return string
	 */
	private function find_nearby_heading( \DOMElement $link ): string {
		// Look in ancestors..
		$node  = $link->parentNode;
		$depth = 0;

		while ( $node && $depth < 5 ) {
			// Check for previous sibling headings..
			$sibling = $node->previousSibling;
			while ( $sibling ) {
				if ( $sibling instanceof \DOMElement && preg_match( '/^h[1-6]$/i', $sibling->nodeName ) ) {
					return trim( $sibling->textContent );
				}
				$sibling = $sibling->previousSibling;
			}

			// Check if inside article/section with heading..
			if ( $node instanceof \DOMElement && in_array( $node->nodeName, array( 'article', 'section', 'div' ) ) ) {
				$headings = $node->getElementsByTagName( 'h1' );
				if ( $headings->length === 0 ) {
					$headings = $node->getElementsByTagName( 'h2' );
				}
				if ( $headings->length === 0 ) {
					$headings = $node->getElementsByTagName( 'h3' );
				}
				if ( $headings->length > 0 ) {
					return trim( $headings->item( 0 )->textContent );
				}
			}

			$node = $node->parentNode;
			++$depth;
		}

		return '';
	}

	/**
	 * Get context from parent element
	 *
	 * @param \DOMNode    $parent
	 * @param \DOMElement $link
	 * @return string
	 */
	private function get_context_from_parent( \DOMNode $parent, \DOMElement $link ): string {
		if ( ! $parent instanceof \DOMElement ) {
			return '';
		}

		// Get text before the link in the same element..
		$full_text = $parent->textContent;
		$link_text = $link->textContent;

		// Find text before the link..
		$pos = strpos( $full_text, $link_text );
		if ( $pos !== false && $pos > 10 ) {
			$before_text = substr( $full_text, 0, $pos );
			// Get last sentence/phrase..
			if ( preg_match( '/([^.!?\n]+)[\s]*$/', $before_text, $matches ) ) {
				$context = trim( $matches[1] );
				if ( strlen( $context ) > 10 && strlen( $context ) < 100 ) {
					return $context;
				}
			}
		}

		return '';
	}

	/**
	 * Get context from URL
	 *
	 * @param string $href
	 * @return string
	 */
	private function get_context_from_url( string $href ): string {
		$parsed = wp_parse_url( $href );

		if ( ! empty( $parsed['path'] ) ) {
			$path     = $parsed['path'];
			$segments = array_filter( explode( '/', $path ) );

			if ( ! empty( $segments ) ) {
				$last_segment = end( $segments );
				// Remove extension and clean..
				$clean = preg_replace( '/\.[^.]+$/', '', $last_segment );
				$clean = str_replace( array( '-', '_' ), ' ', $clean );

				if ( strlen( $clean ) > 3 && strlen( $clean ) < 50 ) {
					return ucwords( $clean );
				}
			}
		}

		return '';
	}

	/**
	 * Combine original text with context
	 *
	 * @param string $original
	 * @param string $context
	 * @return string
	 */
	private function combine_with_original( string $original, string $context ): string {
		$original = ucfirst( strtolower( $original ) );
		$context  = ucfirst( trim( $context ) );

		// Don't repeat if context already contains action word..
		$action_words = array( 'read', 'learn', 'view', 'see', 'click', 'go to', 'visit' );
		foreach ( $action_words as $word ) {
			if ( stripos( $context, $word ) === 0 ) {
				return $context;
			}
		}

		// Format based on original text type..
		if ( in_array( strtolower( $original ), array( 'read more', 'learn more', 'see more', 'view more' ) ) ) {
			$action = explode( ' ', $original )[0];
			return sprintf( '%s about %s', ucfirst( $action ), $context );
		}

		if ( in_array( strtolower( $original ), array( 'continue', 'continue reading' ) ) ) {
			return sprintf( 'Continue reading %s', $context );
		}

		return sprintf( '%s: %s', $original, $context );
	}
}
