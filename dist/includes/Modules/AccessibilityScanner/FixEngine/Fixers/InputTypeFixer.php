<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class InputTypeFixer extends AbstractFixer {

	public function get_id(): string {
		return 'input-type';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom         = $this->parse_html( $content );
		$inputs      = $dom->getElementsByTagName( 'input' );
		$fixed_count = 0;

		$type_map = array(
			'email'    => 'email',
			'phone'    => 'tel',
			'url'      => 'url',
			'date'     => 'date',
			'number'   => 'number',
			'search'   => 'search',
			'password' => 'password',
		);

		foreach ( $inputs as $input ) {
			$name         = strtolower( $input->getAttribute( 'name' ) ?: '' );
			$id           = strtolower( $input->getAttribute( 'id' ) ?: '' );
			$search_in    = $name . ' ' . $id;
			$current_type = strtolower( $input->getAttribute( 'type' ) ?: 'text' );

			if ( $current_type === 'text' ) {
				foreach ( $type_map as $key => $type ) {
					if ( strpos( $search_in, $key ) !== false ) {
						$input->setAttribute( 'type', $type );
						++$fixed_count;
						break;
					}
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
