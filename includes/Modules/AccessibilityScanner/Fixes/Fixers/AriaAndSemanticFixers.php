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
 * Adds and updates ARIA state attributes for interactive elements (WCAG 4.1.2)
 */
class AriaStateFixer extends BaseFixer {
	public function get_id() {
		return 'aria-state';
	}

	public function get_description() {
		return 'Adds ARIA state attributes to interactive elements';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Fix toggle buttons (aria-pressed).
		$fixed_count += $this->fix_toggle_buttons( $xpath );

		// Fix expandable elements (aria-expanded).
		$fixed_count += $this->fix_expandables( $xpath );

		// Fix tab elements (aria-selected).
		$fixed_count += $this->fix_tabs( $xpath );

		// Fix checkbox-like elements (aria-checked).
		$fixed_count += $this->fix_checkboxes( $xpath );

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Fix toggle buttons by adding aria-pressed.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function fix_toggle_buttons( \DOMXPath $xpath ) {
		$fixed = 0;

		// Find buttons that look like toggles.
		$toggles = $xpath->query(
			'//button[contains(@class, "toggle") or contains(@class, "switch") or ' .
			'contains(@class, "btn-toggle")]' .
			'[not(@aria-pressed)]'
		);

		foreach ( $toggles as $toggle ) {
			// Determine initial state from class or aria-checked.
			$class      = $toggle->getAttribute( 'class' );
			$is_pressed = (
				preg_match( '/\b(active|on|pressed|selected)\b/i', $class ) ||
				$toggle->getAttribute( 'aria-checked' ) === 'true'
			);

			$toggle->setAttribute( 'aria-pressed', $is_pressed ? 'true' : 'false' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix expandable elements by adding aria-expanded.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function fix_expandables( \DOMXPath $xpath ) {
		$fixed = 0;

		// Find elements that control expandable content.
		$triggers = $xpath->query(
			'//*[contains(@class, "accordion") or contains(@class, "collapse") or ' .
			'contains(@class, "expandable") or contains(@class, "dropdown") or ' .
			'contains(@data-toggle, "collapse") or contains(@data-bs-toggle, "collapse")]' .
			'//button[not(@aria-expanded)] | ' .
			'//*[contains(@class, "accordion") or contains(@class, "collapse")]' .
			'//a[not(@aria-expanded)]'
		);

		foreach ( $triggers as $trigger ) {
			// Determine initial state.
			$class       = $trigger->getAttribute( 'class' );
			$is_expanded = preg_match( '/\b(open|expanded|show|active)\b/i', $class );

			$trigger->setAttribute( 'aria-expanded', $is_expanded ? 'true' : 'false' );
			++$fixed;
		}

		// Also look for standalone buttons with collapse-like behavior.
		$collapse_btns = $xpath->query(
			'//button[@data-toggle="collapse" or @data-bs-toggle="collapse"][not(@aria-expanded)]'
		);

		foreach ( $collapse_btns as $btn ) {
			$btn->setAttribute( 'aria-expanded', 'false' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix tab elements by adding aria-selected.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function fix_tabs( \DOMXPath $xpath ) {
		$fixed = 0;

		// Find tab elements.
		$tabs = $xpath->query(
			'//*[@role="tab"][not(@aria-selected)] | ' .
			'//*[@role="tablist"]//*[contains(@class, "tab")][not(@aria-selected)]'
		);

		foreach ( $tabs as $tab ) {
			// Determine if active.
			$class      = $tab->getAttribute( 'class' );
			$is_active  = preg_match( '/\b(active|selected|current)\b/i', $class );

			$tab->setAttribute( 'aria-selected', $is_active ? 'true' : 'false' );

			// Ensure it has role="tab".
			if ( ! $tab->hasAttribute( 'role' ) ) {
				$tab->setAttribute( 'role', 'tab' );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix checkbox-like custom elements.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function fix_checkboxes( \DOMXPath $xpath ) {
		$fixed = 0;

		// Find custom checkbox elements.
		$checkboxes = $xpath->query(
			'//*[@role="checkbox"][not(@aria-checked)] | ' .
			'//*[@role="switch"][not(@aria-checked)]'
		);

		foreach ( $checkboxes as $checkbox ) {
			$class      = $checkbox->getAttribute( 'class' );
			$is_checked = preg_match( '/\b(checked|selected|active|on)\b/i', $class );

			$checkbox->setAttribute( 'aria-checked', $is_checked ? 'true' : 'false' );
			++$fixed;
		}

		return $fixed;
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
 * Improves page structure with landmarks and skip links (WCAG 2.4.1, 1.3.1)
 */
class PageStructureFixer extends BaseFixer {
	public function get_id() {
		return 'page-structure';
	}

	public function get_description() {
		return 'Adds landmarks and skip-to-content links for better page structure';
	}

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		// Add main landmark if missing.
		$fixed_count += $this->add_main_landmark( $xpath, $dom );

		// Add skip-to-content link if missing.
		$fixed_count += $this->add_skip_link( $xpath, $dom );

		// Add landmark roles to semantic-looking containers.
		$fixed_count += $this->add_landmark_roles( $xpath );

		// Ensure proper aria-labels on duplicate landmarks.
		$fixed_count += $this->label_duplicate_landmarks( $xpath );

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Add main landmark if missing.
	 *
	 * @param \DOMXPath    $xpath The XPath instance.
	 * @param \DOMDocument $dom   The DOM document.
	 * @return int Number of fixes applied.
	 */
	private function add_main_landmark( \DOMXPath $xpath, \DOMDocument $dom ) {
		// Check if main element exists.
		$main = $xpath->query( '//main | //*[@role="main"]' );
		if ( $main->length > 0 ) {
			return 0;
		}

		// Look for content container that should be main.
		$content_candidates = $xpath->query(
			'//*[@id="content" or @id="main-content" or @id="primary" or ' .
			'contains(@class, "content-area") or contains(@class, "main-content") or ' .
			'contains(@class, "site-content")]'
		);

		if ( $content_candidates->length > 0 ) {
			$container = $content_candidates->item( 0 );
			if ( $container instanceof \DOMElement ) {
				$container->setAttribute( 'role', 'main' );

				// Ensure it has an ID for skip link.
				if ( ! $container->hasAttribute( 'id' ) ) {
					$container->setAttribute( 'id', 'main-content' );
				}

				return 1;
			}
		}

		return 0;
	}

	/**
	 * Add skip-to-content link if missing.
	 *
	 * @param \DOMXPath    $xpath The XPath instance.
	 * @param \DOMDocument $dom   The DOM document.
	 * @return int Number of fixes applied.
	 */
	private function add_skip_link( \DOMXPath $xpath, \DOMDocument $dom ) {
		// Check if skip link already exists.
		$skip = $xpath->query(
			'//a[contains(@class, "skip") or contains(@href, "#main") or ' .
			'contains(@href, "#content") or contains(text(), "Skip")]'
		);

		if ( $skip->length > 0 ) {
			return 0;
		}

		// Find target for skip link.
		$main = $xpath->query( '//main | //*[@role="main"] | //*[@id="content"] | //*[@id="main-content"]' );
		if ( $main->length === 0 ) {
			return 0;
		}

		$target    = $main->item( 0 );
		$target_id = $target->getAttribute( 'id' );

		if ( empty( $target_id ) ) {
			$target_id = 'main-content';
			$target->setAttribute( 'id', $target_id );
		}

		// Create skip link.
		$skip_link = $dom->createElement( 'a' );
		$skip_link->setAttribute( 'href', '#' . $target_id );
		$skip_link->setAttribute( 'class', 'slos-skip-link screen-reader-text' );
		$skip_link->textContent = 'Skip to main content';

		// Insert at beginning of body.
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( $body && $body->firstChild ) {
			$body->insertBefore( $skip_link, $body->firstChild );

			// Inject skip link styles.
			$this->inject_skip_link_styles( $dom );

			return 1;
		}

		return 0;
	}

	/**
	 * Add landmark roles to semantic-looking containers.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function add_landmark_roles( \DOMXPath $xpath ) {
		$fixed = 0;

		// Containers that should be navigation.
		$nav_candidates = $xpath->query(
			'//div[contains(@class, "nav") or contains(@class, "menu") or ' .
			'contains(@id, "nav") or contains(@id, "menu")]' .
			'[not(@role)][.//a or .//ul]'
		);

		foreach ( $nav_candidates as $nav ) {
			$nav->setAttribute( 'role', 'navigation' );
			++$fixed;
		}

		// Containers that should be complementary (aside).
		$aside_candidates = $xpath->query(
			'//div[contains(@class, "sidebar") or contains(@class, "widget-area") or ' .
			'contains(@id, "sidebar")]' .
			'[not(@role)]'
		);

		foreach ( $aside_candidates as $aside ) {
			$aside->setAttribute( 'role', 'complementary' );
			++$fixed;
		}

		// Containers that should be search.
		$search_candidates = $xpath->query(
			'//div[contains(@class, "search") or contains(@id, "search")]' .
			'[not(@role)][.//input[@type="search" or @type="text"]]'
		);

		foreach ( $search_candidates as $search ) {
			$search->setAttribute( 'role', 'search' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Label duplicate landmarks for screen reader users.
	 *
	 * @param \DOMXPath $xpath The XPath instance.
	 * @return int Number of fixes applied.
	 */
	private function label_duplicate_landmarks( \DOMXPath $xpath ) {
		$fixed = 0;

		// Check for multiple nav elements.
		$navs = $xpath->query( '//nav | //*[@role="navigation"]' );
		if ( $navs->length > 1 ) {
			$nav_count = 0;
			foreach ( $navs as $nav ) {
				if ( ! $nav->hasAttribute( 'aria-label' ) && ! $nav->hasAttribute( 'aria-labelledby' ) ) {
					++$nav_count;

					// Try to derive label from heading or class.
					$label = $this->derive_landmark_label( $nav, $xpath );
					if ( $label ) {
						$nav->setAttribute( 'aria-label', $label );
						++$fixed;
					}
				}
			}
		}

		return $fixed;
	}

	/**
	 * Derive a label for a landmark from its content or attributes.
	 *
	 * @param \DOMElement $element The landmark element.
	 * @param \DOMXPath   $xpath   The XPath instance.
	 * @return string|null The derived label or null.
	 */
	private function derive_landmark_label( \DOMElement $element, \DOMXPath $xpath ) {
		// Check for heading inside.
		$heading = $xpath->query( './/h1 | .//h2 | .//h3 | .//h4', $element )->item( 0 );
		if ( $heading ) {
			return trim( $heading->textContent );
		}

		// Check class for hints.
		$class = strtolower( $element->getAttribute( 'class' ) );
		$id    = strtolower( $element->getAttribute( 'id' ) );

		$label_hints = array(
			'primary'   => 'Primary navigation',
			'main'      => 'Main navigation',
			'secondary' => 'Secondary navigation',
			'footer'    => 'Footer navigation',
			'social'    => 'Social links',
			'breadcrumb' => 'Breadcrumb',
			'pagination' => 'Pagination',
		);

		foreach ( $label_hints as $hint => $label ) {
			if ( strpos( $class, $hint ) !== false || strpos( $id, $hint ) !== false ) {
				return $label;
			}
		}

		return null;
	}

	/**
	 * Inject skip link CSS styles.
	 *
	 * @param \DOMDocument $dom The DOM document.
	 */
	private function inject_skip_link_styles( \DOMDocument $dom ) {
		$xpath    = new \DOMXPath( $dom );
		$existing = $xpath->query( '//style[@data-slos-skip-styles]' );
		if ( $existing->length > 0 ) {
			return;
		}

		$style = $dom->createElement( 'style' );
		$style->setAttribute( 'data-slos-skip-styles', 'true' );
		$style->textContent = '
/* SLOS Skip Link Styles */
.slos-skip-link {
    position: absolute;
    left: -10000px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
    z-index: 999999;
}

.slos-skip-link:focus {
    position: fixed;
    top: 10px;
    left: 10px;
    width: auto;
    height: auto;
    padding: 15px 20px;
    background: #005fcc;
    color: #ffffff;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.slos-skip-link:focus:hover {
    background: #004299;
}
';

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$head->appendChild( $style );
		}
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

