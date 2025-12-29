<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for valid ARIA roles
 * WCAG 4.1.2 - Name, Role, Value (Level A)
 */
class AriaRoleCheck extends AbstractCheck {

	/**
	 * Valid WAI-ARIA roles (ARIA 1.2 complete list with 1.3 additions)
	 */
	private $valid_roles = array(
		// Widget roles
		'alert',
		'alertdialog',
		'button',
		'checkbox',
		'combobox',
		'dialog',
		'gridcell',
		'link',
		'log',
		'marquee',
		'menuitem',
		'menuitemcheckbox',
		'menuitemradio',
		'option',
		'progressbar',
		'radio',
		'scrollbar',
		'searchbox',
		'slider',
		'spinbutton',
		'status',
		'switch',
		'tab',
		'tabpanel',
		'textbox',
		'timer',
		'tooltip',
		'treeitem',

		// Composite widget roles
		'grid',
		'listbox',
		'menu',
		'menubar',
		'radiogroup',
		'tablist',
		'tree',
		'treegrid',

		// Document structure roles
		'application',
		'article',
		'blockquote',     // ARIA 1.2
		'caption',        // ARIA 1.2
		'cell',
		'code',           // ARIA 1.2
		'columnheader',
		'definition',
		'deletion',       // ARIA 1.2
		'directory',
		'document',
		'emphasis',       // ARIA 1.2
		'feed',
		'figure',
		'generic',        // ARIA 1.2
		'group',
		'heading',
		'img',
		'insertion',      // ARIA 1.2
		'list',
		'listitem',
		'mark',           // ARIA 1.2
		'math',
		'meter',          // ARIA 1.2
		'none',
		'note',
		'paragraph',      // ARIA 1.2
		'presentation',
		'row',
		'rowgroup',
		'rowheader',
		'separator',
		'strong',         // ARIA 1.2
		'subscript',      // ARIA 1.2
		'superscript',    // ARIA 1.2
		'table',
		'term',
		'time',           // ARIA 1.2
		'toolbar',

		// Landmark roles
		'banner',
		'complementary',
		'contentinfo',
		'form',
		'main',
		'navigation',
		'region',
		'search',

		// Live region roles
		'status',
		'timer',

		// Window roles
		'alertdialog',
		'dialog',

		// ARIA 1.3 additions (draft - but in use)
		'comment',        // ARIA 1.3
		'suggestion',     // ARIA 1.3
	);

	/**
	 * Abstract roles (should not be used directly)
	 */
	private $abstract_roles = array(
		'command',
		'composite',
		'input',
		'landmark',
		'range',
		'roletype',
		'section',
		'sectionhead',
		'select',
		'structure',
		'widget',
		'window',
	);

	/**
	 * Deprecated roles (still valid but discouraged)
	 */
	private $deprecated_roles = array(
		'directory' => 'Use role="list" instead.',
	);

	public function get_id() {
		return 'aria-role';
	}

	public function get_description() {
		return 'ARIA roles must be valid WAI-ARIA 1.2/1.3 roles. Abstract roles should not be used directly.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '4.1.2';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Use valid ARIA roles from the WAI-ARIA specification. Avoid abstract roles like "widget" or "landmark".';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$elements = $xpath->query( '//*[@role]' );

		foreach ( $elements as $element ) {
			$role_attr = $element->getAttribute( 'role' );

			// Handle multiple roles (space-separated)
			$roles = preg_split( '/\s+/', strtolower( trim( $role_attr ) ) );

			foreach ( $roles as $role ) {
				if ( empty( $role ) ) {
					continue;
				}

				// Check for abstract roles
				if ( in_array( $role, $this->abstract_roles, true ) ) {
					$issues[] = array(
						'element'    => $element->tagName,
						'context'    => $this->get_element_html( $element ),
						'message'    => "The role '$role' is an abstract role and should not be used directly.",
						'confidence' => 'high',
					);
					continue;
				}

				// Check for deprecated roles
				if ( isset( $this->deprecated_roles[ $role ] ) ) {
					$issues[] = array(
						'element'    => $element->tagName,
						'context'    => $this->get_element_html( $element ),
						'message'    => "The role '$role' is deprecated. " . $this->deprecated_roles[ $role ],
						'severity'   => 'notice',
						'confidence' => 'high',
					);
					continue;
				}

				// Check for invalid roles
				if ( ! in_array( $role, $this->valid_roles, true ) ) {
					// Check for common typos
					$suggestion = $this->get_role_suggestion( $role );
					$message    = "The role '$role' is not a valid WAI-ARIA role.";

					if ( $suggestion ) {
						$message .= " Did you mean '$suggestion'?";
					}

					$issues[] = array(
						'element'    => $element->tagName,
						'context'    => $this->get_element_html( $element ),
						'message'    => $message,
						'confidence' => 'high',
					);
				}
			}

			// Check for redundant roles on native elements
			$this->check_redundant_roles( $element, $roles, $issues );
		}

		return $issues;
	}

	/**
	 * Get suggestion for misspelled role
	 */
	private function get_role_suggestion( $role ) {
		// Common typos and suggestions
		$common_typos = array(
			'buttn'      => 'button',
			'buton'      => 'button',
			'navagation' => 'navigation',
			'navigaton'  => 'navigation',
			'naviagtion' => 'navigation',
			'artical'    => 'article',
			'articel'    => 'article',
			'diaglog'    => 'dialog',
			'dailog'     => 'dialog',
			'chekbox'    => 'checkbox',
			'chekcbox'   => 'checkbox',
			'textbok'    => 'textbox',
			'serach'     => 'search',
			'seach'      => 'search',
			'menue'      => 'menu',
			'manu'       => 'menu',
			'tabel'      => 'table',
			'progresbar' => 'progressbar',
		);

		if ( isset( $common_typos[ $role ] ) ) {
			return $common_typos[ $role ];
		}

		// Levenshtein distance check for close matches
		foreach ( $this->valid_roles as $valid_role ) {
			if ( levenshtein( $role, $valid_role ) <= 2 ) {
				return $valid_role;
			}
		}

		return null;
	}

	/**
	 * Check for redundant roles on native elements
	 */
	private function check_redundant_roles( $element, $roles, &$issues ) {
		$tag = strtolower( $element->tagName );

		// Native element to implicit role mapping
		$implicit_roles = array(
			'button'   => 'button',
			'a'        => 'link',
			'nav'      => 'navigation',
			'main'     => 'main',
			'header'   => 'banner',
			'footer'   => 'contentinfo',
			'aside'    => 'complementary',
			'article'  => 'article',
			'form'     => 'form',
			'table'    => 'table',
			'ul'       => 'list',
			'ol'       => 'list',
			'li'       => 'listitem',
			'img'      => 'img',
			'h1'       => 'heading',
			'h2'       => 'heading',
			'h3'       => 'heading',
			'h4'       => 'heading',
			'h5'       => 'heading',
			'h6'       => 'heading',
		);

		if ( isset( $implicit_roles[ $tag ] ) ) {
			$implicit_role = $implicit_roles[ $tag ];

			if ( in_array( $implicit_role, $roles, true ) ) {
				$issues[] = array(
					'element'    => $tag,
					'context'    => $this->get_element_html( $element ),
					'message'    => "Redundant role='$implicit_role' on <$tag> element. Native HTML elements have implicit ARIA roles.",
					'severity'   => 'notice',
					'confidence' => 'high',
				);
			}
		}
	}
}

