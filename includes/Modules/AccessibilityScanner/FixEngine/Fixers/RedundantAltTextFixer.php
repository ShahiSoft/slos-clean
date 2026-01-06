<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RedundantAltTextFixer extends AbstractFixer {

	public function get_id(): string {
		return 'redundant-alt-text';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom         = $this->parse_html( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			if ( $img->hasAttribute( 'alt' ) ) {
				$alt = trim( $img->getAttribute( 'alt' ) );
				// Remove "image of", "picture of", "photo of" prefix
				$alt = preg_replace( '/^(image|picture|photo) of /i', '', $alt );
				// Remove redundant image extensions
				$alt = preg_replace( '/\.(jpg|jpeg|png|gif|webp)$/i', '', $alt );
				$img->setAttribute( 'alt', $alt );
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
