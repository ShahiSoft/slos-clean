<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ModalAccessibilityFixer extends AbstractFixer {

	public function get_id(): string {
		return 'modal-accessibility';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		foreach ( $divs as $div ) {
			if ( ! $div instanceof \DOMElement ) {
				continue;
			}

			$class = $div->getAttribute( 'class' );
			if ( preg_match( '/(modal|popup|dialog|overlay)/i', $class ) ) {
				if ( ! $div->hasAttribute( 'role' ) ) {
					$div->setAttribute( 'role', 'dialog' );
					$div->setAttribute( 'aria-modal', 'true' );
					++$fixed_count;
				}
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
