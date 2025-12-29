<?php
/**
 * Missing Alt Text Fixer
 *
 * Adds alt attributes to images that are missing them.
 * WCAG 1.1.1 Non-text Content (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MissingAltTextFixer Class
 *
 * Adds alt attributes to images missing them.
 */
class MissingAltTextFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'missing-alt-text';
	}

	/**
	 * Get fixer name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Missing Alt Text';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds alternative text to images for screen readers';
	}

	/**
	 * Fix missing alt text in content
	 *
	 * @param string $content Post content
	 * @return array Fix result with content and count
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );

		// Find all img elements without alt attribute
		$images = $xpath->query( '//img[not(@alt)]' );
		$fixes_applied = 0;

		foreach ( $images as $img ) {
			$src = $img->getAttribute( 'src' );
			
			// Try to get meaningful alt text from various sources
			$alt_text = $this->generate_alt_from_context( $img, $src );
			
			$img->setAttribute( 'alt', $alt_text );
			$fixes_applied++;
		}

		return $this->return_result( $this->dom_to_html( $dom ), $fixes_applied );
	}

	/**
	 * Generate alt text from image context
	 *
	 * @param \DOMElement $img Image element
	 * @param string $src Image source
	 * @return string Generated alt text
	 */
	private function generate_alt_from_context( $img, $src ) {
		// Check for title attribute
		if ( $img->hasAttribute( 'title' ) && ! empty( trim( $img->getAttribute( 'title' ) ) ) ) {
			return trim( $img->getAttribute( 'title' ) );
		}

		// Check for data-alt or similar attributes
		foreach ( array( 'data-alt', 'data-caption', 'data-description' ) as $attr ) {
			if ( $img->hasAttribute( $attr ) && ! empty( trim( $img->getAttribute( $attr ) ) ) ) {
				return trim( $img->getAttribute( $attr ) );
			}
		}

		// Check parent figure caption
		$parent = $img->parentNode;
		while ( $parent && $parent->nodeType === XML_ELEMENT_NODE ) {
			if ( $parent->nodeName === 'figure' ) {
				$captions = $parent->getElementsByTagName( 'figcaption' );
				if ( $captions->length > 0 ) {
					$caption_text = trim( $captions->item( 0 )->textContent );
					if ( ! empty( $caption_text ) ) {
						return $caption_text;
					}
				}
			}
			$parent = $parent->parentNode;
		}

		// Generate from filename as last resort
		return $this->generate_alt_text( $src );
	}
}
