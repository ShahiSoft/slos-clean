<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AriaRoleCheck extends AbstractCheck {

	public function get_id() {
		return 'aria-role';
	}

	public function get_description() {
		return 'ARIA roles must be valid and abstract roles should not be used.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '4.1.2';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		$valid_roles = array(
			'alert',
			'alertdialog',
			'application',
			'article',
			'banner',
			'button',
			'cell',
			'checkbox',
			'columnheader',
			'combobox',
			'complementary',
			'contentinfo',
			'definition',
			'dialog',
			'directory',
			'document',
			'feed',
			'figure',
			'form',
			'grid',
			'gridcell',
			'group',
			'heading',
			'img',
			'link',
			'list',
			'listbox',
			'listitem',
			'log',
			'main',
			'marquee',
			'math',
			'menu',
			'menubar',
			'menuitem',
			'menuitemcheckbox',
			'menuitemradio',
			'navigation',
			'none',
			'note',
			'option',
			'presentation',
			'progressbar',
			'radio',
			'radiogroup',
			'region',
			'row',
			'rowgroup',
			'rowheader',
			'scrollbar',
			'search',
			'searchbox',
			'separator',
			'slider',
			'spinbutton',
			'status',
			'switch',
			'tab',
			'table',
			'tablist',
			'tabpanel',
			'term',
			'textbox',
			'timer',
			'toolbar',
			'tooltip',
			'tree',
			'treegrid',
			'treeitem',
		);

		$abstract_roles = array( 'command', 'composite', 'input', 'landmark', 'range', 'roletype', 'section', 'sectionhead', 'select', 'structure', 'widget', 'window' );

		$elements = $xpath->query( '//*[@role]' );

		foreach ( $elements as $element ) {
			$role = strtolower( trim( $element->getAttribute( 'role' ) ) );

			if ( ! in_array( $role, $valid_roles ) ) {
				if ( in_array( $role, $abstract_roles ) ) {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => "The role '$role' is an abstract role and should not be used directly.",
					);
				} else {
					$issues[] = array(
						'element' => $element->tagName,
						'context' => $this->get_element_html( $element ),
						'message' => "The role '$role' is not a valid WAI-ARIA role.",
					);
				}
			}
		}

		return $issues;
	}

	private function get_element_html( $node ) {
		return $node->ownerDocument->saveHTML( $node );
	}
}

