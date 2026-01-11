<?php
/**
 * Multiple H1 Fixer (Legacy Adapter)
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MultipleH1Fixer extends AbstractFixer {

	public function get_id(): string {
		return 'multiple-h1';
	}

	public function get_name(): string {
		return __( 'Multiple H1', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Reduces multiple H1 headings to a single primary H1 using the legacy fixer behavior.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '2.4.6' );
	}

	public function get_category(): string {
		return 'headings';
	}

	public function can_fix( string $content ): bool {
		return substr_count( strtolower( $content ), '<h1' ) > 1;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$h1s         = $dom->getElementsByTagName( 'h1' );
		$fixed_count = 0;

		$h1_array = array();
		foreach ( $h1s as $h1 ) {
			$h1_array[] = $h1;
		}

		$h1_count = count( $h1_array );
		if ( $h1_count <= 1 ) {
			return FixResult::skipped( $this->get_id(), 'No Multiple H1 fixes applied', $content );
		}

		for ( $i = 1; $i < $h1_count; $i++ ) {
			$node = $h1_array[ $i ];
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}

			$h2              = $dom->createElement( 'h2' );
			$h2->textContent = $node->textContent;
			if ( $node->parentNode ) {
				$node->parentNode->replaceChild( $h2, $node );
				++$fixed_count;
			}
		}

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No Multiple H1 fixes applied', $content );
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
