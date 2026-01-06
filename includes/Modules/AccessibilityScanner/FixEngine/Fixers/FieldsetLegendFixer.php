<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FieldsetLegendFixer extends AbstractFixer {

	public function get_id(): string {
		return 'fieldset-legend';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom         = $this->parse_html( $content );
		$fieldsets   = $dom->getElementsByTagName( 'fieldset' );
		$fixed_count = 0;

		foreach ( $fieldsets as $fieldset ) {
			$legend = $fieldset->getElementsByTagName( 'legend' )->item( 0 );
			if ( ! $legend ) {
				$legend              = $dom->createElement( 'legend' );
				$legend->textContent = 'Options';

				if ( $fieldset->firstChild ) {
					$fieldset->insertBefore( $legend, $fieldset->firstChild );
				} else {
					$fieldset->appendChild( $legend );
				}
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
