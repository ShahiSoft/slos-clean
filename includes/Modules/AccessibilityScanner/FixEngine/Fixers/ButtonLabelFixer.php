<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ButtonLabelFixer extends AbstractFixer {

	public function get_id(): string {
		return 'button-label';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom         = $this->parse_html( $content );
		$buttons     = $dom->getElementsByTagName( 'button' );
		$fixed_count = 0;

		foreach ( $buttons as $button ) {
			$text = trim( $button->textContent );
			if ( $text === '' && ! $button->hasAttribute( 'aria-label' ) ) {
				$button->setAttribute( 'aria-label', 'Button' );
				++$fixed_count;
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
			[]
		);
	}
}
