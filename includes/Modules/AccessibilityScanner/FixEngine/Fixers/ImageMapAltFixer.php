<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ImageMapAltFixer extends AbstractFixer {

	public function get_id(): string {
		return 'image-map-alt';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom         = $this->parse_html( $content );
		$maps        = $dom->getElementsByTagName( 'map' );
		$fixed_count = 0;

		foreach ( $maps as $map ) {
			$areas = $map->getElementsByTagName( 'area' );
			foreach ( $areas as $area ) {
				if ( ! $area->hasAttribute( 'alt' ) || trim( $area->getAttribute( 'alt' ) ) === '' ) {
					$area->setAttribute( 'alt', 'Map region' );
					++$fixed_count;
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
			[]
		);
	}
}
