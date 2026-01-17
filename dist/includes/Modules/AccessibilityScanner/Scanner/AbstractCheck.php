<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract base class for accessibility checks
 *
 * Provides default implementations for the CheckInterface methods
 * and utility methods for DOM manipulation.
 *
 * @since 1.0.0
 * @since 3.1.2 Added new interface methods and DOM caching
 */
abstract class AbstractCheck implements CheckInterface {

	/**
	 * Cached DOM document for performance
	 *
	 * @since 3.1.2
	 * @var \DOMDocument|null
	 */
	protected $cached_dom = null;

	/**
	 * WCAG criteria to URL slug mapping
	 *
	 * @since 3.1.2
	 * @var array
	 */
	private static $wcag_slugs = array(
		'1.1.1'  => 'non-text-content',
		'1.2.1'  => 'audio-only-and-video-only-prerecorded',
		'1.2.2'  => 'captions-prerecorded',
		'1.2.3'  => 'audio-description-or-media-alternative-prerecorded',
		'1.2.5'  => 'audio-description-prerecorded',
		'1.3.1'  => 'info-and-relationships',
		'1.3.2'  => 'meaningful-sequence',
		'1.3.3'  => 'sensory-characteristics',
		'1.3.4'  => 'orientation',
		'1.3.5'  => 'identify-input-purpose',
		'1.4.1'  => 'use-of-color',
		'1.4.2'  => 'audio-control',
		'1.4.3'  => 'contrast-minimum',
		'1.4.4'  => 'resize-text',
		'1.4.5'  => 'images-of-text',
		'1.4.10' => 'reflow',
		'1.4.11' => 'non-text-contrast',
		'1.4.12' => 'text-spacing',
		'1.4.13' => 'content-on-hover-or-focus',
		'2.1.1'  => 'keyboard',
		'2.1.2'  => 'no-keyboard-trap',
		'2.1.4'  => 'character-key-shortcuts',
		'2.2.1'  => 'timing-adjustable',
		'2.2.2'  => 'pause-stop-hide',
		'2.3.1'  => 'three-flashes-or-below-threshold',
		'2.4.1'  => 'bypass-blocks',
		'2.4.2'  => 'page-titled',
		'2.4.3'  => 'focus-order',
		'2.4.4'  => 'link-purpose-in-context',
		'2.4.5'  => 'multiple-ways',
		'2.4.6'  => 'headings-and-labels',
		'2.4.7'  => 'focus-visible',
		'2.5.1'  => 'pointer-gestures',
		'2.5.2'  => 'pointer-cancellation',
		'2.5.3'  => 'label-in-name',
		'2.5.4'  => 'motion-actuation',
		'2.5.5'  => 'target-size-enhanced',
		'2.5.8'  => 'target-size-minimum',
		'3.1.1'  => 'language-of-page',
		'3.1.2'  => 'language-of-parts',
		'3.2.1'  => 'on-focus',
		'3.2.2'  => 'on-input',
		'3.2.3'  => 'consistent-navigation',
		'3.2.4'  => 'consistent-identification',
		'3.3.1'  => 'error-identification',
		'3.3.2'  => 'labels-or-instructions',
		'3.3.3'  => 'error-suggestion',
		'3.3.4'  => 'error-prevention-legal-financial-data',
		'4.1.1'  => 'parsing',
		'4.1.2'  => 'name-role-value',
		'4.1.3'  => 'status-messages',
	);

	/**
	 * Get WCAG Success Criteria
	 * Default implementation returns empty string
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '';
	}

	/**
	 * Get WCAG conformance level
	 *
	 * Override in subclasses to specify A, AA, or AAA level.
	 * Default is AA as most checks target Level AA compliance.
	 *
	 * @since 3.1.2
	 * @return string 'A', 'AA', or 'AAA'
	 */
	public function get_wcag_level() {
		return 'AA';
	}

	/**
	 * Get remediation hint for fixing the issue
	 *
	 * Override in subclasses to provide specific fix suggestions.
	 *
	 * @since 3.1.2
	 * @return string Human-readable fix suggestion
	 */
	public function get_remediation_hint() {
		return '';
	}

	/**
	 * Get link to WCAG documentation
	 *
	 * Automatically generates URL based on WCAG criteria.
	 *
	 * @since 3.1.2
	 * @return string URL to WCAG understanding document
	 */
	public function get_wcag_url() {
		$criteria = $this->get_wcag_criteria();

		if ( empty( $criteria ) ) {
			return '';
		}

		$slug = self::$wcag_slugs[ $criteria ] ?? '';

		if ( empty( $slug ) ) {
			return '';
		}

		return "https://www.w3.org/WAI/WCAG21/Understanding/{$slug}";
	}

	/**
	 * Get issue confidence level
	 *
	 * Override in subclasses for heuristic checks that may produce
	 * false positives.
	 *
	 * @since 3.1.2
	 * @return string 'definite', 'likely', or 'potential'
	 */
	public function get_confidence() {
		return 'definite';
	}

	/**
	 * Check with pre-parsed DOM for performance optimization
	 *
	 * Stores the pre-parsed DOM for use by get_dom() method.
	 *
	 * @since 3.1.2
	 * @param \DOMDocument $dom Pre-parsed DOM document
	 * @param string       $content Original HTML content
	 * @return array Array of issues found
	 */
	public function check_dom( \DOMDocument $dom, $content ) {
		$this->cached_dom = $dom;
		$result           = $this->check( $content );
		$this->cached_dom = null; // Clear cache after use

		return $result;
	}

	/**
	 * Helper to find elements in HTML content
	 *
	 * @param string $content HTML content
	 * @param string $tag Tag name
	 * @return \DOMNodeList
	 */
	protected function get_elements( $content, $tag ) {
		$dom = $this->get_dom( $content );
		return $dom->getElementsByTagName( $tag );
	}

	/**
	 * Get DOMDocument from content
	 *
	 * Uses cached DOM if available (from check_dom call),
	 * otherwise parses the content.
	 *
	 * @param string $content HTML content
	 * @return \DOMDocument
	 */
	protected function get_dom( $content ) {
		if ( $this->cached_dom !== null ) {
			return $this->cached_dom;
		}

		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		return $dom;
	}

	/**
	 * Get element HTML for issue context
	 *
	 * Truncates long HTML to prevent overly verbose output.
	 *
	 * @since 3.1.2
	 * @param \DOMNode $node       DOM node to convert to HTML
	 * @param int      $max_length Maximum length before truncation
	 * @return string HTML representation
	 */
	protected function get_element_html( $node, $max_length = 200 ) {
		$html = $node->ownerDocument->saveHTML( $node );

		if ( strlen( $html ) > $max_length ) {
			return substr( $html, 0, $max_length ) . '...';
		}

		return $html;
	}

	/**
	 * Check if element has accessible name
	 *
	 * Checks for aria-label, aria-labelledby, or title attributes.
	 *
	 * @since 3.1.2
	 * @param \DOMElement $element Element to check
	 * @return bool True if element has accessible name
	 */
	protected function has_accessible_name( $element ) {
		// Check aria-label..
		if ( $element->hasAttribute( 'aria-label' ) ) {
			$label = trim( $element->getAttribute( 'aria-label' ) );
			if ( ! empty( $label ) ) {
				return true;
			}
		}

		// Check aria-labelledby..
		if ( $element->hasAttribute( 'aria-labelledby' ) ) {
			$labelledby = trim( $element->getAttribute( 'aria-labelledby' ) );
			if ( ! empty( $labelledby ) ) {
				return true;
			}
		}

		// Check title..
		if ( $element->hasAttribute( 'title' ) ) {
			$title = trim( $element->getAttribute( 'title' ) );
			if ( ! empty( $title ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if element is hidden from accessibility tree
	 *
	 * @since 3.1.2
	 * @param \DOMElement $element Element to check
	 * @return bool True if element is hidden
	 */
	protected function is_hidden( $element ) {
		// Check aria-hidden..
		if ( $element->getAttribute( 'aria-hidden' ) === 'true' ) {
			return true;
		}

		// Check hidden attribute..
		if ( $element->hasAttribute( 'hidden' ) ) {
			return true;
		}

		// Check inline display:none or visibility:hidden..
		$style = $element->getAttribute( 'style' );
		if ( preg_match( '/display\s*:\s*none|visibility\s*:\s*hidden/i', $style ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Normalize text content for comparison
	 *
	 * Removes extra whitespace and normalizes case.
	 *
	 * @since 3.1.2
	 * @param string $text Text to normalize
	 * @return string Normalized text
	 */
	protected function normalize_text( $text ) {
		return strtolower( trim( preg_replace( '/\s+/', ' ', $text ) ) );
	}
}
