<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AriaRoleFixer extends AbstractFixer {

	public function get_id(): string {
		return 'aria-role';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

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

		$elements_with_role = $xpath->query( '//*[@role]' );
		if ( $elements_with_role instanceof \DOMNodeList ) {
			foreach ( $elements_with_role as $element ) {
				if ( ! $element instanceof \DOMElement ) {
					continue;
				}

				$role = strtolower( trim( $element->getAttribute( 'role' ) ) );

				if ( $role !== '' && ! in_array( $role, $valid_roles, true ) ) {
					$element->removeAttribute( 'role' );
					++$fixed_count;
				}
			}
		}

		$navs = $dom->getElementsByTagName( 'nav' );
		foreach ( $navs as $nav ) {
			if ( $nav instanceof \DOMElement && ! $nav->hasAttribute( 'role' ) ) {
				$nav->setAttribute( 'role', 'navigation' );
				++$fixed_count;
			}
		}

		$mains = $dom->getElementsByTagName( 'main' );
		foreach ( $mains as $main ) {
			if ( $main instanceof \DOMElement && ! $main->hasAttribute( 'role' ) ) {
				$main->setAttribute( 'role', 'main' );
				++$fixed_count;
			}
		}

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
		}

		$fixed_content = $this->get_html( $dom );

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			array()
		);
	}
}
