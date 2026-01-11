<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PlaceholderLabelFixer extends AbstractFixer {

	public function get_id(): string {
		return 'placeholder-label';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom         = $this->parse_html( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		foreach ( $inputs as $input ) {
			if ( $input->hasAttribute( 'placeholder' ) && ! $input->hasAttribute( 'aria-label' ) ) {
				$placeholder = $input->getAttribute( 'placeholder' );
				$input->setAttribute( 'aria-label', $placeholder );
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
			array()
		);
	}
}
