<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for animations that can be paused
 * WCAG 2.2.2 - Pause, Stop, Hide (Level A)
 *
 * @since 3.1.2
 */
class AnimationPauseCheck extends AbstractCheck {

	/**
	 * Common carousel/slider class patterns
	 *
	 * @var array
	 */
	private $carousel_patterns = array(
		'carousel',
		'slider',
		'slideshow',
		'slick',
		'swiper',
		'owl-carousel',
		'glide',
		'splide',
		'flickity',
		'bxslider',
		'flexslider',
		'cycle',
		'nivoslider',
		'revolution-slider',
		'revslider',
	);

	/**
	 * Get check ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'animation-pause';
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Automatically moving, blinking, or scrolling content lasting more than 5 seconds must have pause/stop controls.';
	}

	/**
	 * Get severity level
	 *
	 * @return string
	 */
	public function get_severity() {
		return 'warning';
	}

	/**
	 * Get WCAG criteria
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '2.2.2';
	}

	/**
	 * Run the check
	 *
	 * @param string $content HTML content to check.
	 * @return array Array of issues found.
	 */
	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check <style> tags for animations
		$this->check_style_animations( $dom, $issues );

		// 2. Check inline animation styles
		$this->check_inline_animations( $xpath, $issues );

		// 3. Check for autoplay video/audio
		$this->check_autoplay_media( $xpath, $issues );

		// 4. Check for carousel/slider patterns
		$this->check_carousel_patterns( $xpath, $issues );

		// 5. Check for marquee elements (deprecated but still used)
		$this->check_marquee( $dom, $issues );

		// 6. Check for blink element (deprecated)
		$this->check_blink( $dom, $issues );

		// 7. Check for GIF images that might be animated
		$this->check_animated_images( $xpath, $issues );

		// 8. Check for prefers-reduced-motion support
		$this->check_reduced_motion_support( $dom, $issues );

		return $issues;
	}

	/**
	 * Check style tags for animation properties
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @param array        $issues Issues array by reference.
	 */
	private function check_style_animations( $dom, &$issues ) {
		$styles = $dom->getElementsByTagName( 'style' );

		foreach ( $styles as $style ) {
			$css = $style->textContent;

			// Check for infinite animations
			if ( preg_match( '/animation[^:]*:[^;]*infinite/i', $css ) ||
				 preg_match( '/animation-iteration-count\s*:\s*infinite/i', $css ) ) {

				// Check if there's a prefers-reduced-motion media query
				if ( ! $this->has_reduced_motion_query( $css ) ) {
					$issues[] = array(
						'element' => 'style',
						'context' => 'CSS animation rules',
						'message' => 'Infinite animation detected in CSS. Provide a pause mechanism or respect prefers-reduced-motion media query.',
					);
				}
			}

			// Check for long animation durations (> 5s)
			if ( preg_match_all( '/animation-duration\s*:\s*(\d+(?:\.\d+)?)(s|ms)/i', $css, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$duration = (float) $match[1];
					if ( strtolower( $match[2] ) === 'ms' ) {
						$duration = $duration / 1000;
					}

					if ( $duration > 5 ) {
						$issues[] = array(
							'element' => 'style',
							'context' => 'CSS animation rules',
							'message' => sprintf(
								'Animation duration (%.1fs) exceeds 5 seconds. Provide pause/stop controls.',
								$duration
							),
						);
					}
				}
			}

			// Check for @keyframes with long implied durations
			if ( preg_match( '/@keyframes\s+\w+/i', $css ) ) {
				// Just flag if infinite is used
				if ( strpos( $css, 'infinite' ) !== false ) {
					// Already handled above
					continue;
				}
			}
		}
	}

	/**
	 * Check for prefers-reduced-motion media query in CSS
	 *
	 * @param string $css CSS content.
	 * @return bool True if query exists.
	 */
	private function has_reduced_motion_query( $css ) {
		return preg_match( '/@media[^{]*prefers-reduced-motion/i', $css ) === 1;
	}

	/**
	 * Check inline animation styles
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_inline_animations( $xpath, &$issues ) {
		$elements = $xpath->query( '//*[@style]' );

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );

			// Check for infinite animations
			if ( preg_match( '/animation[^:]*:[^;]*infinite/i', $style ) ) {
				$issues[] = array(
					'element' => $element->tagName,
					'context' => $this->get_element_html( $element ),
					'message' => 'Element has infinite animation via inline style. Ensure users can pause it.',
				);
			}

			// Check for long duration
			if ( preg_match( '/animation-duration\s*:\s*(\d+(?:\.\d+)?)(s|ms)/i', $style, $match ) ) {
				$duration = (float) $match[1];
				if ( strtolower( $match[2] ) === 'ms' ) {
					$duration = $duration / 1000;
				}

				if ( $duration > 5 ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => sprintf( 'Element has long animation (%.1fs). Provide pause controls.', $duration ),
					);
				}
			}
		}
	}

	/**
	 * Check for autoplay video and audio
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_autoplay_media( $xpath, &$issues ) {
		// Video autoplay
		$videos = $xpath->query( '//video[@autoplay]' );
		foreach ( $videos as $video ) {
			// Check if it has controls
			$has_controls = $video->hasAttribute( 'controls' );
			$is_muted     = $video->hasAttribute( 'muted' );

			if ( ! $has_controls ) {
				$issues[] = array(
					'element' => 'video',
					'context' => $this->get_element_html( $video ),
					'message' => 'Video with autoplay detected without controls attribute. Users must be able to pause playback.',
				);
			} elseif ( ! $is_muted ) {
				$issues[] = array(
					'element'  => 'video',
					'context'  => $this->get_element_html( $video ),
					'message'  => 'Autoplay video with audio may be disruptive. Consider adding muted attribute.',
					'severity' => 'notice',
				);
			}
		}

		// Audio autoplay
		$audios = $xpath->query( '//audio[@autoplay]' );
		foreach ( $audios as $audio ) {
			$has_controls = $audio->hasAttribute( 'controls' );

			if ( ! $has_controls ) {
				$issues[] = array(
					'element' => 'audio',
					'context' => $this->get_element_html( $audio ),
					'message' => 'Audio with autoplay detected without controls. Users must be able to pause playback.',
				);
			} else {
				$issues[] = array(
					'element'  => 'audio',
					'context'  => $this->get_element_html( $audio ),
					'message'  => 'Autoplay audio may be disruptive for users. Consider requiring user interaction to start.',
					'severity' => 'notice',
				);
			}
		}
	}

	/**
	 * Check for carousel/slider patterns
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_carousel_patterns( $xpath, &$issues ) {
		$found_patterns = array();

		foreach ( $this->carousel_patterns as $pattern ) {
			$elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

			if ( $elements->length > 0 && ! in_array( $pattern, $found_patterns, true ) ) {
				$element          = $elements->item( 0 );
				$found_patterns[] = $pattern;

				// Check for data attributes suggesting auto-play
				$autoplay_attrs = array( 'data-autoplay', 'data-auto', 'data-interval', 'data-cycle' );
				$has_autoplay   = false;

				foreach ( $autoplay_attrs as $attr ) {
					if ( $element->hasAttribute( $attr ) ) {
						$value = $element->getAttribute( $attr );
						if ( $value !== 'false' && $value !== '0' ) {
							$has_autoplay = true;
							break;
						}
					}
				}

				if ( $has_autoplay ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => sprintf(
							'Auto-rotating carousel detected (class: %s). Provide visible pause/play controls and respect prefers-reduced-motion.',
							$pattern
						),
					);
				} else {
					$issues[] = array(
						'element'  => $element->tagName,
						'context'  => $this->get_element_html( $element ),
						'message'  => sprintf(
							'Carousel/slider detected (class: %s). If auto-rotating, ensure pause controls are available.',
							$pattern
						),
						'severity' => 'notice',
					);
				}
			}
		}
	}

	/**
	 * Check for marquee elements
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @param array        $issues Issues array by reference.
	 */
	private function check_marquee( $dom, &$issues ) {
		$marquees = $dom->getElementsByTagName( 'marquee' );

		foreach ( $marquees as $marquee ) {
			$issues[] = array(
				'element' => 'marquee',
				'context' => $this->get_element_html( $marquee ),
				'message' => '<marquee> element detected. This deprecated element creates automatically scrolling content that cannot be paused, violating WCAG 2.2.2.',
			);
		}
	}

	/**
	 * Check for blink elements
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @param array        $issues Issues array by reference.
	 */
	private function check_blink( $dom, &$issues ) {
		$blinks = $dom->getElementsByTagName( 'blink' );

		foreach ( $blinks as $blink ) {
			$issues[] = array(
				'element' => 'blink',
				'context' => $this->get_element_html( $blink ),
				'message' => '<blink> element detected. Blinking content cannot be paused and may cause seizures. Remove this element.',
			);
		}
	}

	/**
	 * Check for potentially animated GIF images
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_animated_images( $xpath, &$issues ) {
		$images = $xpath->query( '//img[contains(@src, ".gif")]' );

		foreach ( $images as $img ) {
			$src = $img->getAttribute( 'src' );
			$alt = $img->getAttribute( 'alt' );

			// Only flag if alt text suggests animation or decorative
			$animation_keywords = array( 'animate', 'loading', 'spinner', 'moving', 'gif' );
			$is_likely_animated = false;

			foreach ( $animation_keywords as $keyword ) {
				if ( stripos( $src, $keyword ) !== false || stripos( $alt, $keyword ) !== false ) {
					$is_likely_animated = true;
					break;
				}
			}

			if ( $is_likely_animated ) {
				$issues[] = array(
					'element'  => 'img',
					'context'  => $this->get_element_html( $img ),
					'message'  => 'Potentially animated GIF detected. If animation exceeds 5 seconds, provide a static alternative or pause mechanism.',
					'severity' => 'notice',
				);
			}
		}
	}

	/**
	 * Check if stylesheet has prefers-reduced-motion support
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @param array        $issues Issues array by reference.
	 */
	private function check_reduced_motion_support( $dom, &$issues ) {
		$styles        = $dom->getElementsByTagName( 'style' );
		$has_animation = false;
		$has_motion    = false;

		foreach ( $styles as $style ) {
			$css = $style->textContent;

			if ( preg_match( '/animation|transition|@keyframes/i', $css ) ) {
				$has_animation = true;
			}

			if ( $this->has_reduced_motion_query( $css ) ) {
				$has_motion = true;
			}
		}

		// Only report if there are animations but no reduced motion support
		if ( $has_animation && ! $has_motion ) {
			$issues[] = array(
				'element'  => 'style',
				'context'  => 'Page CSS',
				'message'  => 'Page has animations but no @media (prefers-reduced-motion) query. Consider adding motion-reduced alternatives for users who prefer reduced motion.',
				'severity' => 'notice',
			);
		}
	}
}
