<?php
/**
 * Animation Pause Fixer
 *
 * Adds pause controls to animated content.
 * WCAG 2.2.2 Pause, Stop, Hide (Level A)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnimationPauseFixer Class
 *
 * Detects animated content (GIFs, CSS animations, carousels) and adds
 * pause controls to allow users to stop motion.
 */
class AnimationPauseFixer extends BaseFixer {

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'animation-pause';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds pause controls to animated content';
	}

	/**
	 * Apply animation pause fixes to content
	 *
	 * @param string $content HTML content to fix.
	 * @return array{fixed_count: int, content: string}
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixed = 0;

		// Fix animated GIFs...
		$fixed += $this->fix_animated_gifs( $dom, $xpath );

		// Fix CSS animation elements...
		$fixed += $this->fix_css_animations( $dom, $xpath );

		// Fix auto-playing carousels/sliders...
		$fixed += $this->fix_carousels( $dom, $xpath );

		// Fix marquee elements (deprecated but still used)...
		$fixed += $this->fix_marquees( $dom, $xpath );

		// Fix auto-scrolling content...
		$fixed += $this->fix_auto_scroll( $xpath );

		// Fix video autoplay...
		$fixed += $this->fix_video_autoplay( $xpath );

		// Inject CSS for pause controls if any fixes were made...
		if ( $fixed > 0 ) {
			$this->inject_animation_styles( $dom );
		}

		return array(
			'fixed_count' => $fixed,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix animated GIFs by wrapping with pause control
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_animated_gifs( $dom, $xpath ) {
		$fixed = 0;

		// Find GIF images without pause control...
		$gifs = $xpath->query( '//img[contains(@src, ".gif") and not(ancestor::*[@data-slos-pause-control])]' );

		foreach ( $gifs as $gif ) {
			// Skip small icons (likely not distracting)...
			$width = $gif->getAttribute( 'width' );
			if ( $width && (int) $width < 50 ) {
				continue;
			}

			// Create wrapper container...
			$wrapper = $dom->createElement( 'div' );
			$wrapper->setAttribute( 'class', 'slos-animation-container' );
			$wrapper->setAttribute( 'data-slos-pause-control', 'true' );
			$wrapper->setAttribute( 'data-animation-type', 'gif' );

			// Create pause button...
			$button = $dom->createElement( 'button' );
			$button->setAttribute( 'type', 'button' );
			$button->setAttribute( 'class', 'slos-pause-animation' );
			$button->setAttribute( 'aria-label', 'Pause animation' );
			$button->setAttribute( 'aria-pressed', 'false' );
			$button->textContent = '⏸';

			// Clone and modify GIF...
			$parent    = $gif->parentNode;
			$gif_clone = $gif->cloneNode( true );
			$gif_clone->setAttribute( 'data-slos-animated', 'true' );

			// Store original src for pause/play functionality...
			$gif_clone->setAttribute( 'data-slos-original-src', $gif->getAttribute( 'src' ) );

			$wrapper->appendChild( $gif_clone );
			$wrapper->appendChild( $button );
			$parent->replaceChild( $wrapper, $gif );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix elements with CSS animations
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_css_animations( $dom, $xpath ) {
		$fixed = 0;

		// Find elements with animation in inline style...
		$animated = $xpath->query( '//*[@style[contains(., "animation")]][not(@data-slos-pause-control)]' );

		foreach ( $animated as $element ) {
			$element->setAttribute( 'data-slos-pause-control', 'true' );
			$element->setAttribute( 'data-slos-animation-state', 'running' );

			// Add pause button if element is not interactive...
			if ( ! $this->is_interactive( $element ) ) {
				$this->add_pause_button( $dom, $element );
			}

			++$fixed;
		}

		// Find elements with common animation classes...
		$animation_classes = array( 'animate', 'animated', 'animation', 'motion', 'moving', 'pulse', 'blink', 'bounce', 'shake', 'spin', 'rotate', 'fade' );
		foreach ( $animation_classes as $class ) {
			$elements = $xpath->query( "//*[contains(@class, '{$class}') and not(@data-slos-pause-control)]" );
			foreach ( $elements as $element ) {
				// Skip very small elements...
				$style = $element->getAttribute( 'style' );
				if ( preg_match( '/width\s*:\s*(\d+)px/i', $style, $m ) && (int) $m[1] < 20 ) {
					continue;
				}

				$element->setAttribute( 'data-slos-pause-control', 'true' );
				$element->setAttribute( 'data-slos-animation-state', 'running' );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix auto-playing carousels and sliders
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_carousels( $dom, $xpath ) {
		$fixed = 0;

		// Common carousel class patterns...
		$carousel_selectors = array(
			"contains(@class, 'carousel')",
			"contains(@class, 'slider')",
			"contains(@class, 'slideshow')",
			"contains(@class, 'swiper')",
			"contains(@class, 'slick')",
			"contains(@class, 'owl-')",
			"contains(@class, 'flickity')",
			"contains(@class, 'glide')",
			"contains(@class, 'splide')",
		);

		$query     = '//*[(' . implode( ' or ', $carousel_selectors ) . ') and not(@data-slos-pause-control)]';
		$carousels = $xpath->query( $query );

		foreach ( $carousels as $carousel ) {
			$carousel->setAttribute( 'data-slos-pause-control', 'true' );
			$carousel->setAttribute( 'aria-roledescription', 'carousel' );

			// Check if pause button exists...
			$existing_pause = $xpath->query(
				'.//*[contains(@class, "pause") or @aria-label[contains(., "pause")] or @aria-label[contains(., "Pause")]]',
				$carousel
			);

			if ( 0 === $existing_pause->length ) {
				$this->add_carousel_controls( $dom, $carousel );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix deprecated marquee elements
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_marquees( $dom, $xpath ) {
		$fixed = 0;

		$marquees = $xpath->query( '//marquee' );

		foreach ( $marquees as $marquee ) {
			// Replace with accessible alternative...
			$div = $dom->createElement( 'div' );
			$div->setAttribute( 'class', 'slos-accessible-marquee' );
			$div->setAttribute( 'role', 'marquee' );
			$div->setAttribute( 'aria-live', 'off' );
			$div->setAttribute( 'data-slos-pause-control', 'true' );

			// Copy content...
			while ( $marquee->firstChild ) {
				$div->appendChild( $marquee->firstChild );
			}

			// Add pause button at the start...
			$button = $dom->createElement( 'button' );
			$button->setAttribute( 'type', 'button' );
			$button->setAttribute( 'class', 'slos-pause-marquee' );
			$button->setAttribute( 'aria-label', 'Pause scrolling text' );
			$button->setAttribute( 'aria-pressed', 'false' );
			$button->textContent = '⏸';
			$div->insertBefore( $button, $div->firstChild );

			$marquee->parentNode->replaceChild( $div, $marquee );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix auto-scrolling content
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_auto_scroll( $xpath ) {
		$fixed = 0;

		// Find elements with auto-scroll classes or attributes...
		$scrollers = $xpath->query(
			'//*[contains(@class, "auto-scroll") or contains(@class, "ticker") or ' .
			'contains(@class, "news-feed") or contains(@class, "news-ticker") or ' .
			'contains(@class, "scrolling-text")]' .
			'[not(@data-slos-pause-control)]'
		);

		foreach ( $scrollers as $scroller ) {
			$scroller->setAttribute( 'data-slos-pause-control', 'true' );
			$scroller->setAttribute( 'data-slos-animation-state', 'running' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix autoplaying videos
	 *
	 * @param \DOMXPath $xpath XPath instance.
	 * @return int Number of fixes.
	 */
	private function fix_video_autoplay( $xpath ) {
		$fixed = 0;

		// Find videos with autoplay...
		$videos = $xpath->query( '//video[@autoplay and not(@data-slos-pause-control)]' );

		foreach ( $videos as $video ) {
			$video->setAttribute( 'data-slos-pause-control', 'true' );

			// Ensure controls are visible...
			if ( ! $video->hasAttribute( 'controls' ) ) {
				$video->setAttribute( 'controls', 'controls' );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Check if element is interactive
	 *
	 * @param \DOMElement $element Element to check.
	 * @return bool True if interactive.
	 */
	private function is_interactive( $element ) {
		$tag = strtolower( $element->tagName );
		return in_array( $tag, array( 'a', 'button', 'input', 'select', 'textarea' ), true );
	}

	/**
	 * Add pause button next to an element
	 *
	 * @param \DOMDocument $dom     DOM document.
	 * @param \DOMElement  $element Element to add button to.
	 * @return void
	 */
	private function add_pause_button( $dom, $element ) {
		$button = $dom->createElement( 'button' );
		$button->setAttribute( 'type', 'button' );
		$button->setAttribute( 'class', 'slos-pause-animation' );
		$button->setAttribute( 'aria-label', 'Pause animation' );
		$button->setAttribute( 'aria-pressed', 'false' );
		$button->textContent = '⏸';

		$parent = $element->parentNode;
		if ( $parent ) {
			$parent->insertBefore( $button, $element->nextSibling );
		}
	}

	/**
	 * Add carousel controls
	 *
	 * @param \DOMDocument $dom      DOM document.
	 * @param \DOMElement  $carousel Carousel element.
	 * @return void
	 */
	private function add_carousel_controls( $dom, $carousel ) {
		$controls = $dom->createElement( 'div' );
		$controls->setAttribute( 'class', 'slos-carousel-controls' );
		$controls->setAttribute( 'role', 'group' );
		$controls->setAttribute( 'aria-label', 'Carousel controls' );

		$pause_btn = $dom->createElement( 'button' );
		$pause_btn->setAttribute( 'type', 'button' );
		$pause_btn->setAttribute( 'class', 'slos-carousel-pause' );
		$pause_btn->setAttribute( 'aria-label', 'Pause carousel' );
		$pause_btn->setAttribute( 'aria-pressed', 'false' );
		$pause_btn->textContent = '⏸ Pause';

		$controls->appendChild( $pause_btn );
		$carousel->insertBefore( $controls, $carousel->firstChild );
	}

	/**
	 * Inject CSS styles for animation controls
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @return void
	 */
	private function inject_animation_styles( $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-animation-styles]' );

		if ( $existing->length > 0 ) {
			return;
		}

		$style = $dom->createElement( 'style' );
		$style->setAttribute( 'data-slos-animation-styles', 'true' );
		$style->textContent = '
/* SLOS Animation Pause Styles */
.slos-animation-container {
	position: relative;
	display: inline-block;
}

.slos-pause-animation,
.slos-pause-marquee,
.slos-carousel-pause {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 10;
	padding: 8px 12px;
	min-width: 44px;
	min-height: 44px;
	background: rgba(0, 0, 0, 0.7);
	color: white;
	border: 2px solid white;
	border-radius: 4px;
	cursor: pointer;
	font-size: 16px;
	line-height: 1;
}

.slos-pause-animation:hover,
.slos-pause-marquee:hover,
.slos-carousel-pause:hover {
	background: rgba(0, 0, 0, 0.9);
}

.slos-pause-animation:focus,
.slos-pause-marquee:focus,
.slos-carousel-pause:focus {
	outline: 2px solid #005fcc;
	outline-offset: 2px;
}

.slos-pause-animation[aria-pressed="true"]::after,
.slos-pause-marquee[aria-pressed="true"]::after,
.slos-carousel-pause[aria-pressed="true"]::after {
	content: " (paused)";
	position: absolute;
	left: -9999px;
}

.slos-carousel-controls {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 100;
}

.slos-accessible-marquee {
	position: relative;
	overflow: hidden;
	white-space: nowrap;
}

.slos-accessible-marquee .slos-pause-marquee {
	position: relative;
	display: inline-block;
	margin-right: 8px;
}

/* Respect prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
	[data-slos-pause-control] {
		animation: none !important;
		transition: none !important;
	}
	
	.slos-pause-animation,
	.slos-pause-marquee,
	.slos-carousel-pause {
		display: none;
	}
}
';

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$head->appendChild( $style );
		}
	}
}
