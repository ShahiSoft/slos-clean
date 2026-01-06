<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AnimationPauseFixer extends AbstractFixer {

	public function get_id(): string {
		return 'animation-pause';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		$fixed_count += $this->fix_animated_gifs( $dom, $xpath );
		$fixed_count += $this->fix_css_animations( $dom, $xpath );
		$fixed_count += $this->fix_carousels( $dom, $xpath );
		$fixed_count += $this->fix_marquees( $dom, $xpath );
		$fixed_count += $this->fix_auto_scroll( $xpath );
		$fixed_count += $this->fix_video_autoplay( $xpath );

		if ( $fixed_count > 0 ) {
			$this->inject_animation_styles( $dom );
		}

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No animation-pause fixes applied', $content );
		}

		$fixed_content = $this->get_html( $dom );

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			[]
		);
	}

	private function fix_animated_gifs( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$gifs = $xpath->query( '//img[contains(@src, ".gif") and not(ancestor::*[@data-slos-pause-control])]' );
		if ( ! $gifs instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $gifs as $gif ) {
			if ( ! $gif instanceof \DOMElement ) {
				continue;
			}

			$width = $gif->getAttribute( 'width' );
			if ( $width && (int) $width < 50 ) {
				continue;
			}

			$wrapper = $dom->createElement( 'div' );
			$wrapper->setAttribute( 'class', 'slos-animation-container' );
			$wrapper->setAttribute( 'data-slos-pause-control', 'true' );
			$wrapper->setAttribute( 'data-animation-type', 'gif' );

			$button = $dom->createElement( 'button' );
			$button->setAttribute( 'type', 'button' );
			$button->setAttribute( 'class', 'slos-pause-animation' );
			$button->setAttribute( 'aria-label', 'Pause animation' );
			$button->setAttribute( 'aria-pressed', 'false' );
			$button->textContent = '⏸';

			$parent    = $gif->parentNode;
			$gif_clone = $gif->cloneNode( true );
			if ( ! $gif_clone instanceof \DOMElement || ! $parent ) {
				continue;
			}
			$gif_clone->setAttribute( 'data-slos-animated', 'true' );
			$gif_clone->setAttribute( 'data-slos-original-src', $gif->getAttribute( 'src' ) );

			$wrapper->appendChild( $gif_clone );
			$wrapper->appendChild( $button );
			$parent->replaceChild( $wrapper, $gif );
			++$fixed;
		}

		return $fixed;
	}

	private function fix_css_animations( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$animated = $xpath->query( '//*[@style[contains(., "animation")]][not(@data-slos-pause-control)]' );
		if ( $animated instanceof \DOMNodeList ) {
			foreach ( $animated as $element ) {
				if ( ! $element instanceof \DOMElement ) {
					continue;
				}
				$element->setAttribute( 'data-slos-pause-control', 'true' );
				$element->setAttribute( 'data-slos-animation-state', 'running' );

				if ( ! $this->is_interactive( $element ) ) {
					$this->add_pause_button( $dom, $element );
				}

				++$fixed;
			}
		}

		$animation_classes = [ 'animate', 'animated', 'animation', 'motion', 'moving', 'pulse', 'blink', 'bounce', 'shake', 'spin', 'rotate', 'fade' ];
		foreach ( $animation_classes as $class ) {
			$elements = $xpath->query( "//*[contains(@class, '{$class}') and not(@data-slos-pause-control)]" );
			if ( ! $elements instanceof \DOMNodeList ) {
				continue;
			}
			foreach ( $elements as $element ) {
				if ( ! $element instanceof \DOMElement ) {
					continue;
				}
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

	private function fix_carousels( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$carousel_selectors = [
			"contains(@class, 'carousel')",
			"contains(@class, 'slider')",
			"contains(@class, 'slideshow')",
			"contains(@class, 'swiper')",
			"contains(@class, 'slick')",
			"contains(@class, 'owl-')",
			"contains(@class, 'flickity')",
			"contains(@class, 'glide')",
			"contains(@class, 'splide')",
		];

		$query     = '//*[(' . implode( ' or ', $carousel_selectors ) . ') and not(@data-slos-pause-control)]';
		$carousels = $xpath->query( $query );
		if ( ! $carousels instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $carousels as $carousel ) {
			if ( ! $carousel instanceof \DOMElement ) {
				continue;
			}
			$carousel->setAttribute( 'data-slos-pause-control', 'true' );
			$carousel->setAttribute( 'aria-roledescription', 'carousel' );

			$existing_pause = $xpath->query(
				'.//*[contains(@class, "pause") or @aria-label[contains(., "pause")] or @aria-label[contains(., "Pause")]]',
				$carousel
			);

			if ( ! ( $existing_pause instanceof \DOMNodeList ) || 0 === $existing_pause->length ) {
				$this->add_carousel_controls( $dom, $carousel );
			}

			++$fixed;
		}

		return $fixed;
	}

	private function fix_marquees( \DOMDocument $dom, \DOMXPath $xpath ): int {
		$fixed = 0;

		$marquees = $xpath->query( '//marquee' );
		if ( ! $marquees instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $marquees as $marquee ) {
			if ( ! $marquee instanceof \DOMElement ) {
				continue;
			}
			$div = $dom->createElement( 'div' );
			$div->setAttribute( 'class', 'slos-accessible-marquee' );
			$div->setAttribute( 'role', 'marquee' );
			$div->setAttribute( 'aria-live', 'off' );
			$div->setAttribute( 'data-slos-pause-control', 'true' );

			while ( $marquee->firstChild ) {
				$div->appendChild( $marquee->firstChild );
			}

			$button = $dom->createElement( 'button' );
			$button->setAttribute( 'type', 'button' );
			$button->setAttribute( 'class', 'slos-pause-marquee' );
			$button->setAttribute( 'aria-label', 'Pause scrolling text' );
			$button->setAttribute( 'aria-pressed', 'false' );
			$button->textContent = '⏸';
			$div->insertBefore( $button, $div->firstChild );

			$parent = $marquee->parentNode;
			if ( $parent ) {
				$parent->replaceChild( $div, $marquee );
				++$fixed;
			}
		}

		return $fixed;
	}

	private function fix_auto_scroll( \DOMXPath $xpath ): int {
		$fixed = 0;

		$scrollers = $xpath->query(
			'//*[contains(@class, "auto-scroll") or contains(@class, "ticker") or '
			. 'contains(@class, "news-feed") or contains(@class, "news-ticker") or '
			. 'contains(@class, "scrolling-text")]'
			. '[not(@data-slos-pause-control)]'
		);
		if ( ! $scrollers instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $scrollers as $scroller ) {
			if ( ! $scroller instanceof \DOMElement ) {
				continue;
			}
			$scroller->setAttribute( 'data-slos-pause-control', 'true' );
			$scroller->setAttribute( 'data-slos-animation-state', 'running' );
			++$fixed;
		}

		return $fixed;
	}

	private function fix_video_autoplay( \DOMXPath $xpath ): int {
		$fixed = 0;

		$videos = $xpath->query( '//video[@autoplay and not(@data-slos-pause-control)]' );
		if ( ! $videos instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $videos as $video ) {
			if ( ! $video instanceof \DOMElement ) {
				continue;
			}
			$video->setAttribute( 'data-slos-pause-control', 'true' );

			if ( ! $video->hasAttribute( 'controls' ) ) {
				$video->setAttribute( 'controls', 'controls' );
			}

			++$fixed;
		}

		return $fixed;
	}

	private function is_interactive( \DOMElement $element ): bool {
		$tag = strtolower( $element->tagName );
		return in_array( $tag, [ 'a', 'button', 'input', 'select', 'textarea' ], true );
	}

	private function add_pause_button( \DOMDocument $dom, \DOMElement $element ): void {
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

	private function add_carousel_controls( \DOMDocument $dom, \DOMElement $carousel ): void {
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

	private function inject_animation_styles( \DOMDocument $dom ): void {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-animation-styles]' );

		if ( $existing instanceof \DOMNodeList && $existing->length > 0 ) {
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
		if ( $head instanceof \DOMElement ) {
			$head->appendChild( $style );
		}
	}
}
