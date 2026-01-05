<?php
/**
 * Required Field Indicator Fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RequiredFieldFixer
 * 
 * Adds aria-required attribute to required form fields.
 */
final class RequiredFieldFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-required-attribute';
	}

	public function get_name(): string {
		return __( 'Required Field Indicator', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds aria-required="true" to form fields with required attribute for better screen reader support.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '3.3.2' ];
	}

	public function get_category(): string {
		return 'forms';
	}

	public function can_fix( string $content ): bool {
		return (bool) preg_match( '/<(?:input|select|textarea)[^>]*required/i', $content );
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find all required fields without aria-required
		$required_fields = $this->query( 
			'//input[@required and not(@aria-required)] | ' .
			'//select[@required and not(@aria-required)] | ' .
			'//textarea[@required and not(@aria-required)]'
		);

		$fixes_applied = 0;
		$details = [];

		foreach ( $required_fields as $field ) {
			$field->setAttribute( 'aria-required', 'true' );
			
			$fixes_applied++;
			$details[] = [
				'tag'  => $field->nodeName,
				'name' => $field->getAttribute( 'name' ) ?: $field->getAttribute( 'id' ),
			];
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All required fields already have aria-required', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}
}
