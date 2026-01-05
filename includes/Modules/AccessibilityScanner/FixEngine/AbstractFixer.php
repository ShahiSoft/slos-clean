<?php
/**
 * Abstract Base Fixer - Template Method Pattern
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

use ShahiLegalFlowSuite\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\FixEngine\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AbstractFixer
 * 
 * Base implementation for all fixers.
 * Uses Template Method pattern for consistent fix workflow.
 * Open/Closed Principle: Extend behavior without modifying base.
 */
abstract class AbstractFixer implements FixerInterface {

	/**
	 * @var \DOMDocument|null Cached DOM instance
	 */
	protected $dom;

	/**
	 * @var \DOMXPath|null Cached XPath instance
	 */
	protected $xpath;

	/**
	 * @var string Canonical fixer ID
	 */
	protected $canonical_id;
	
	/**
	 * Constructor - Validate ID against canonical registry
	 */
	public function __construct() {
		$id = $this->get_id();
		$this->canonical_id = CanonicalIds::canonicalize($id) ?? '';
		
		if (empty($this->canonical_id)) {
			Logger::error('Fixer ID not in canonical registry', [
				'fixer_id' => $id,
				'fixer_class' => get_class($this),
			]);
			
			throw new \InvalidArgumentException(
				sprintf('Fixer ID "%s" is not in the canonical registry. Check CanonicalIds class.', $id)
			);
		}
	}

	/**
	 * Canonical fixer ID accessor
	 */
	public function get_canonical_id(): string {
		return $this->canonical_id;
	}

	/**
	 * Get human-readable name (derived from ID by default)
	 *
	 * @return string
	 */
	public function get_name(): string {
		return ucwords( str_replace( [ '-', '_' ], ' ', $this->get_id() ) );
	}

	/**
	 * Get WCAG criteria (empty by default, override in subclasses)
	 *
	 * @return array
	 */
	public function get_wcag_criteria(): array {
		return [];
	}

	/**
	 * Template method: Apply fix with timing and error handling
	 *
	 * @param string $content
	 * @return FixResult
	 */
	final public function fix( string $content ): FixResult {
		$start_time = microtime( true );

		try {
			// Check if we can fix this content
			if ( ! $this->can_fix( $content ) ) {
				return FixResult::skipped( $this->canonical_id, $content, 'No fixable issues detected' );
			}

			// Parse HTML
			$this->dom = $this->parse_html( $content );
			$this->xpath = new \DOMXPath( $this->dom );

			// Apply the fix (implemented by subclasses)
			$fix_details = $this->apply_fix();

			// Get the fixed content
			$fixed_content = $this->get_html();

			// Clean up
			$this->dom = null;
			$this->xpath = null;

			$execution_time = microtime( true ) - $start_time;

			// Return appropriate result
			if ( $fix_details['count'] > 0 ) {
				return FixResult::success(
					$this->canonical_id,
					$content,
					$fixed_content,
					$fix_details['count'],
					$fix_details['items'] ?? [],
					$execution_time
				);
			}

			return FixResult::skipped( $this->canonical_id, $content );

		} catch ( \Throwable $e ) {
			$this->dom = null;
			$this->xpath = null;

			return FixResult::error( $this->canonical_id, $content, $e->getMessage() );
		}
	}

	/**
	 * Apply the actual fix - implemented by subclasses
	 *
	 * Returns an array with:
	 * - 'count' (int): Number of fixes applied
	 * - 'items' (array): Optional details about what was fixed
	 *
	 * @return array{count: int, items?: array}
	 */
	abstract protected function apply_fix(): array;

	/**
	 * Parse HTML content into DOMDocument
	 *
	 * @param string $content
	 * @return \DOMDocument
	 */
	protected function parse_html( string $content ): \DOMDocument {
		$dom = new \DOMDocument( '1.0', 'UTF-8' );

		libxml_use_internal_errors( true );

		// Wrap content in proper HTML structure
		$wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' 
		         . $content 
		         . '</body></html>';

		$dom->loadHTML( $wrapped, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED );
		libxml_clear_errors();

		return $dom;
	}

	/**
	 * Extract HTML from body
	 *
	 * @return string
	 */
	protected function get_html(): string {
		if ( ! $this->dom ) {
			return '';
		}

		$body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
		if ( ! $body ) {
			return '';
		}

		$html = '';
		foreach ( $body->childNodes as $child ) {
			$html .= $this->dom->saveHTML( $child );
		}

		return trim( $html );
	}

	/**
	 * Query DOM using XPath
	 *
	 * @param string $query XPath query
	 * @return \DOMNodeList
	 */
	protected function query( string $query ): \DOMNodeList {
		return $this->xpath->query( $query );
	}

	/**
	 * Helper: Generate alt text from image source
	 *
	 * @param string $src Image source URL
	 * @return string
	 */
	protected function generate_alt_from_src( string $src ): string {
		$filename = basename( parse_url( $src, PHP_URL_PATH ) ?? '' );
		$name = pathinfo( $filename, PATHINFO_FILENAME );
		$name = preg_replace( '/[-_]+/', ' ', $name );
		$name = preg_replace( '/\d+x\d+/', '', $name ); // Remove dimensions
		$name = trim( $name );
		return ucfirst( $name ) ?: 'Image';
	}

	/**
	 * Helper: Check if element has accessible name
	 *
	 * @param \DOMElement $element
	 * @return bool
	 */
	protected function has_accessible_name( \DOMElement $element ): bool {
		// Check text content
		if ( trim( $element->textContent ) !== '' ) {
			return true;
		}

		// Check aria-label
		if ( $element->hasAttribute( 'aria-label' ) && trim( $element->getAttribute( 'aria-label' ) ) !== '' ) {
			return true;
		}

		// Check aria-labelledby
		if ( $element->hasAttribute( 'aria-labelledby' ) ) {
			$labelledby = $element->getAttribute( 'aria-labelledby' );
			$label_element = $this->dom->getElementById( $labelledby );
			if ( $label_element && trim( $label_element->textContent ) !== '' ) {
				return true;
			}
		}

		// Check title attribute
		if ( $element->hasAttribute( 'title' ) && trim( $element->getAttribute( 'title' ) ) !== '' ) {
			return true;
		}

		return false;
	}

	/**
	 * Helper: Create new DOM element
	 *
	 * @param string $tag
	 * @param array  $attributes
	 * @param string $text
	 * @return \DOMElement
	 */
	protected function create_element( string $tag, array $attributes = [], string $text = '' ): \DOMElement {
		$element = $this->dom->createElement( $tag );
		
		foreach ( $attributes as $name => $value ) {
			$element->setAttribute( $name, $value );
		}

		if ( $text !== '' ) {
			$element->textContent = $text;
		}

		return $element;
	}
}
