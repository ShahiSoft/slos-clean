<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class InvalidAriaCombinationFixer extends AbstractFixer {

	public function get_id(): string {
		return 'invalid-aria-combination';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		// The legacy implementation does not perform automatic fixes and
		// always returns the original content. Reflect that here directly.
		return FixResult::skipped( $this->get_id(), 'No automatic fix available for invalid ARIA combinations', $content );
	}
}
