<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class OrphanedLabelFixer extends AbstractFixer {

	public function get_id(): string {
		return 'orphaned-label';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom         = $this->parse_html( $content );
		$labels      = $dom->getElementsByTagName( 'label' );
		$fixed_count = 0;

		foreach ( $labels as $label ) {
			if ( ! $label->hasAttribute( 'for' ) ) {
				$next = $label->nextSibling;
				while ( $next ) {
					if ( $next->nodeType === XML_ELEMENT_NODE ) {
						if ( $next->nodeName === 'input' && $next->hasAttribute( 'id' ) ) {
							$label->setAttribute( 'for', $next->getAttribute( 'id' ) );
							++$fixed_count;
							break;
						}
						break;
					}
					$next = $next->nextSibling;
				}
			}
		}

		$fixed_content = $this->get_html();

		if ( $fixed_count <= 0 || $fixed_content === '' || $fixed_content === $content ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			array()
		);
	}
}
