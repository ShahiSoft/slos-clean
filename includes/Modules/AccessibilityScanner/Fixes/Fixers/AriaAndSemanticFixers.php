<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ARIA Role Fixer
 */
class AriaRoleFixer extends BaseFixer {
	public function get_id() {
		return 'aria-role'; }
	public function get_description() {
		return 'Fix invalid or missing ARIA roles'; }

	// Valid ARIA roles per WAI-ARIA spec
	private static $valid_roles = array(
		'alert', 'alertdialog', 'application', 'article', 'banner', 'button', 'cell', 'checkbox',
		'columnheader', 'combobox', 'complementary', 'contentinfo', 'definition', 'dialog', 'directory',
		'document', 'feed', 'figure', 'form', 'grid', 'gridcell', 'group', 'heading', 'img', 'link',
		'list', 'listbox', 'listitem', 'log', 'main', 'marquee', 'math', 'menu', 'menubar', 'menuitem',
		'menuitemcheckbox', 'menuitemradio', 'navigation', 'none', 'note', 'option', 'presentation',
		'progressbar', 'radio', 'radiogroup', 'region', 'row', 'rowgroup', 'rowheader', 'scrollbar',
		'search', 'searchbox', 'separator', 'slider', 'spinbutton', 'status', 'switch', 'tab',
		'table', 'tablist', 'tabpanel', 'term', 'textbox', 'timer', 'toolbar', 'tooltip', 'tree',
		'treegrid', 'treeitem'
	);

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Find all elements with role attribute
		$elements_with_role = $xpath->query( '//*[@role]' );
		foreach ( $elements_with_role as $element ) {
			$role = strtolower( trim( $element->getAttribute( 'role' ) ) );
			
			// Check if role is invalid
			if ( ! in_array( $role, self::$valid_roles, true ) ) {
				// Remove invalid role - better than leaving an invalid one
				$element->removeAttribute( 'role' );
				++$fixed_count;
			}
		}

		// Add role="navigation" to nav elements without role
		$navs = $dom->getElementsByTagName( 'nav' );
		foreach ( $navs as $nav ) {
			if ( ! $nav->hasAttribute( 'role' ) ) {
				$nav->setAttribute( 'role', 'navigation' );
				++$fixed_count;
			}
		}

		// Add role="main" to main elements without role
		$mains = $dom->getElementsByTagName( 'main' );
		foreach ( $mains as $main ) {
			if ( ! $main->hasAttribute( 'role' ) ) {
				$main->setAttribute( 'role', 'main' );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * ARIA Attribute Fixer
 */
class AriaAttributeFixer extends BaseFixer {
	public function get_id() {
		return 'aria-attribute'; }
	public function get_description() {
		return 'Fix invalid ARIA attributes'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		$valid_aria = array( 'aria-label', 'aria-labelledby', 'aria-hidden', 'aria-live', 'aria-modal', 'aria-required', 'aria-disabled', 'aria-expanded' );

		foreach ( $elements as $element ) {
			// Check for misspelled aria attributes
			$attributes = array();
			foreach ( $element->attributes as $attr ) {
				$attributes[ $attr->name ] = $attr->value;
			}

			foreach ( $attributes as $name => $value ) {
				if ( stripos( $name, 'aria-' ) === 0 && ! in_array( $name, $valid_aria ) ) {
					// Check if it's a typo of a valid aria attribute
					$similar = $this->find_similar( $name, $valid_aria );
					if ( $similar && levenshtein( $name, $similar ) <= 2 ) {
						$element->removeAttribute( $name );
						$element->setAttribute( $similar, $value );
						++$fixed_count;
					}
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	private function find_similar( $str, $arr ) {
		$closest = null;
		$min     = 999;

		foreach ( $arr as $item ) {
			$distance = levenshtein( $str, $item );
			if ( $distance < $min ) {
				$closest = $item;
				$min     = $distance;
			}
		}

		return $closest;
	}
}

/**
 * ARIA State Fixer
 */
class AriaStateFixer extends BaseFixer {
	public function get_id() {
		return 'aria-state'; }
	public function get_description() {
		return 'Update ARIA state attributes'; }

	public function fix( $content ) {
		// ARIA state attributes are typically updated by JavaScript
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Landmark Role Fixer
 */
class LandmarkRoleFixer extends BaseFixer {
	public function get_id() {
		return 'landmark-role'; }
	public function get_description() {
		return 'Add landmark roles'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$fixed_count = 0;

		// Add landmark roles to semantic elements
		$landmarks = array(
			'header'  => 'banner',
			'nav'     => 'navigation',
			'main'    => 'main',
			'aside'   => 'complementary',
			'footer'  => 'contentinfo',
			'article' => 'article',
		);

		foreach ( $landmarks as $tag => $role ) {
			$elements = $dom->getElementsByTagName( $tag );
			foreach ( $elements as $element ) {
				if ( ! $element->hasAttribute( 'role' ) ) {
					$element->setAttribute( 'role', $role );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Redundant ARIA Fixer
 */
class RedundantAriaFixer extends BaseFixer {
	public function get_id() {
		return 'redundant-aria'; }
	public function get_description() {
		return 'Remove redundant ARIA'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		$semantic_roles = array(
			'button' => 'button',
			'a'      => 'link',
			'img'    => 'img',
			'h1'     => 'heading',
			'h2'     => 'heading',
			'h3'     => 'heading',
			'h4'     => 'heading',
			'h5'     => 'heading',
			'h6'     => 'heading',
			'header' => 'banner',
			'nav'    => 'navigation',
			'main'   => 'main',
			'footer' => 'contentinfo',
		);

		foreach ( $elements as $element ) {
			$tag = strtolower( $element->tagName );
			if ( isset( $semantic_roles[ $tag ] ) ) {
				$role = $element->getAttribute( 'role' );
				if ( $role === $semantic_roles[ $tag ] ) {
					$element->removeAttribute( 'role' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Invalid ARIA Combination Fixer
 */
class InvalidAriaCombinationFixer extends BaseFixer {
	public function get_id() {
		return 'invalid-aria-combination'; }
	public function get_description() {
		return 'Fix invalid ARIA combinations'; }

	public function fix( $content ) {
		// Complex ARIA validation - delegate to content check
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Hidden Content Fixer
 */
class HiddenContentFixer extends BaseFixer {
	public function get_id() {
		return 'hidden-content'; }
	public function get_description() {
		return 'Fix hidden content accessibility'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );
			$class = $element->getAttribute( 'class' );

			// Detect visually hidden but semantically important content
			if ( preg_match( '/(display:\s*none|visibility:\s*hidden)/i', $style ) ||
				preg_match( '/(hidden|display-none|screen-reader)/i', $class ) ) {

				if ( ! $element->hasAttribute( 'role' ) ) {
					// If contains important text, ensure it's accessible
					$text = trim( $element->textContent );
					if ( ! empty( $text ) ) {
						$element->setAttribute( 'aria-hidden', 'false' );
						++$fixed_count;
					}
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Semantic HTML Fixer
 */
class SemanticHtmlFixer extends BaseFixer {
	public function get_id() {
		return 'semantic-html'; }
	public function get_description() {
		return 'Use semantic HTML'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		// Convert div with role to semantic element
		$div_array = array();
		foreach ( $divs as $d ) {
			$div_array[] = $d;
		}

		foreach ( $div_array as $div ) {
			$role = $div->getAttribute( 'role' );

			$role_to_tag = array(
				'main'          => 'main',
				'banner'        => 'header',
				'navigation'    => 'nav',
				'contentinfo'   => 'footer',
				'complementary' => 'aside',
			);

			if ( $role && isset( $role_to_tag[ $role ] ) ) {
				$new_tag                  = $role_to_tag[ $role ];
				$new_element              = $dom->createElement( $new_tag );
				$new_element->textContent = $div->textContent;

				// Copy attributes
				foreach ( $div->attributes as $attr ) {
					if ( $attr->name !== 'role' ) {
						$new_element->setAttribute( $attr->name, $attr->value );
					}
				}

				$div->parentNode->replaceChild( $new_element, $div );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Live Region Fixer
 */
class LiveRegionFixer extends BaseFixer {
	public function get_id() {
		return 'live-region'; }
	public function get_description() {
		return 'Add ARIA live regions'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		foreach ( $divs as $div ) {
			$class = $div->getAttribute( 'class' );
			if ( preg_match( '/(alert|notice|message|notification|status)/i', $class ) ) {
				if ( ! $div->hasAttribute( 'aria-live' ) ) {
					$div->setAttribute( 'aria-live', 'polite' );
					$div->setAttribute( 'aria-atomic', 'true' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Page Structure Fixer
 */
class PageStructureFixer extends BaseFixer {
	public function get_id() {
		return 'page-structure'; }
	public function get_description() {
		return 'Improve page structure'; }

	public function fix( $content ) {
		// Page structure improvements require significant refactoring
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Video Accessibility Fixer
 */
class VideoAccessibilityFixer extends BaseFixer {
	public function get_id() {
		return 'video-accessibility'; }
	public function get_description() {
		return 'Add captions to videos'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$videos      = $dom->getElementsByTagName( 'video' );
		$fixed_count = 0;

		foreach ( $videos as $video ) {
			if ( ! $video->hasAttribute( 'aria-label' ) ) {
				$video->setAttribute( 'aria-label', 'Video' );
				++$fixed_count;
			}

			$tracks = $video->getElementsByTagName( 'track' );
			if ( $tracks->length === 0 ) {
				// No captions, add note
				$p              = $dom->createElement( 'p' );
				$p->textContent = 'Captions are required for video accessibility.';
				$video->parentNode->insertBefore( $p, $video->nextSibling );
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Audio Accessibility Fixer
 */
class AudioAccessibilityFixer extends BaseFixer {
	public function get_id() {
		return 'audio-accessibility'; }
	public function get_description() {
		return 'Add transcripts to audio'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$audios      = $dom->getElementsByTagName( 'audio' );
		$fixed_count = 0;

		foreach ( $audios as $audio ) {
			if ( ! $audio->hasAttribute( 'aria-label' ) ) {
				$audio->setAttribute( 'aria-label', 'Audio' );
				++$fixed_count;
			}

			// Add note about transcript
			$p              = $dom->createElement( 'p' );
			$p->textContent = 'Transcript: Required for audio accessibility.';
			$audio->parentNode->insertBefore( $p, $audio->nextSibling );
			++$fixed_count;
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Media Alternative Fixer
 */
class MediaAlternativeFixer extends BaseFixer {
	public function get_id() {
		return 'media-alternative'; }
	public function get_description() {
		return 'Add alternatives to media'; }

	public function fix( $content ) {
		// Handled by other media fixers
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

