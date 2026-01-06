<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AriaStateFixer extends AbstractFixer {

	public function get_id(): string {
		return 'aria-state';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		$fixed_count += $this->fix_toggle_buttons( $xpath );
		$fixed_count += $this->fix_expandables( $xpath );
		$fixed_count += $this->fix_tabs( $xpath );
		$fixed_count += $this->fix_checkboxes( $xpath );

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
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

	/**
	 * Fix toggle buttons by adding aria-pressed.
	 */
	private function fix_toggle_buttons( \DOMXPath $xpath ): int {
		$fixed = 0;

		$toggles = $xpath->query(
			'//button[contains(@class, "toggle") or contains(@class, "switch") or '
			. 'contains(@class, "btn-toggle")][not(@aria-pressed)]'
		);

		if ( ! $toggles instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $toggles as $toggle ) {
			if ( ! $toggle instanceof \DOMElement ) {
				continue;
			}

			$class      = $toggle->getAttribute( 'class' );
			$is_pressed = (
				preg_match( '/\\b(active|on|pressed|selected)\\b/i', $class )
				|| $toggle->getAttribute( 'aria-checked' ) === 'true'
			);

			$toggle->setAttribute( 'aria-pressed', $is_pressed ? 'true' : 'false' );
			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix expandable elements by adding aria-expanded.
	 */
	private function fix_expandables( \DOMXPath $xpath ): int {
		$fixed = 0;

		$triggers = $xpath->query(
			'//*[contains(@class, "accordion") or contains(@class, "collapse") or '
			. 'contains(@class, "expandable") or contains(@class, "dropdown") or '
			. 'contains(@data-toggle, "collapse") or contains(@data-bs-toggle, "collapse")]'
			. '//button[not(@aria-expanded)] | '
			. '//*[contains(@class, "accordion") or contains(@class, "collapse")]'
			. '//a[not(@aria-expanded)]'
		);

		if ( $triggers instanceof \DOMNodeList ) {
			foreach ( $triggers as $trigger ) {
				if ( ! $trigger instanceof \DOMElement ) {
					continue;
				}

				$class       = $trigger->getAttribute( 'class' );
				$is_expanded = preg_match( '/\\b(open|expanded|show|active)\\b/i', $class );

				$trigger->setAttribute( 'aria-expanded', $is_expanded ? 'true' : 'false' );

				if ( ! $trigger->hasAttribute( 'role' ) ) {
					$trigger->setAttribute( 'role', 'button' );
				}

				++$fixed;
			}
		}

		$collapse_btns = $xpath->query(
			'//button[@data-toggle="collapse" or @data-bs-toggle="collapse"][not(@aria-expanded)]'
		);

		if ( $collapse_btns instanceof \DOMNodeList ) {
			foreach ( $collapse_btns as $btn ) {
				if ( ! $btn instanceof \DOMElement ) {
					continue;
				}
				$btn->setAttribute( 'aria-expanded', 'false' );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix tab elements by adding aria-selected.
	 */
	private function fix_tabs( \DOMXPath $xpath ): int {
		$fixed = 0;

		$tabs = $xpath->query(
			'//*[@role="tab"][not(@aria-selected)] | '
			. '//*[@role="tablist"]//*[contains(@class, "tab")][not(@aria-selected)]'
		);

		if ( ! $tabs instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $tabs as $tab ) {
			if ( ! $tab instanceof \DOMElement ) {
				continue;
			}

			$class     = $tab->getAttribute( 'class' );
			$is_active = preg_match( '/\\b(active|selected|current)\\b/i', $class );

			$tab->setAttribute( 'aria-selected', $is_active ? 'true' : 'false' );

			if ( ! $tab->hasAttribute( 'role' ) ) {
				$tab->setAttribute( 'role', 'tab' );
			}

			++$fixed;
		}

		return $fixed;
	}

	/**
	 * Fix checkbox-like custom elements (role=checkbox/switch).
	 */
	private function fix_checkboxes( \DOMXPath $xpath ): int {
		$fixed = 0;

		$checkboxes = $xpath->query(
			'//*[@role="checkbox"][not(@aria-checked)] | '
			. '//*[@role="switch"][not(@aria-checked)]'
		);

		if ( ! $checkboxes instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $checkboxes as $checkbox ) {
			if ( ! $checkbox instanceof \DOMElement ) {
				continue;
			}

			$class      = $checkbox->getAttribute( 'class' );
			$is_checked = preg_match( '/\\b(checked|selected|active|on)\\b/i', $class );

			$checkbox->setAttribute( 'aria-checked', $is_checked ? 'true' : 'false' );
			++$fixed;
		}

		return $fixed;
	}
}
