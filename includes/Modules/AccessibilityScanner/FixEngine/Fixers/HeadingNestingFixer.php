<?php
/**
 * Heading Nesting Fixer (Legacy Adapter)
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class HeadingNestingFixer extends AbstractFixer {

	public function get_id(): string {
		return 'heading-nesting';
	}

	public function get_name(): string {
		return __( 'Heading Nesting', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Fixes invalid heading level jumps using the existing legacy nesting fixer.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '1.3.1', '2.4.6' ];
	}

	public function get_category(): string {
		return 'headings';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<h' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		// The legacy HeadingNestingFixer currently performs no automatic
		// structural changes and always returns the original content.
		// Reflect that behavior here directly.
		return FixResult::skipped( $this->get_id(), 'No automatic fix available for heading nesting', $content );
	}
}
