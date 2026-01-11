<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LogoImageFixer extends AbstractFixer {

	public function get_id(): string {
		return 'logo-image';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			if ( ! $img instanceof \DOMElement ) {
				continue;
			}

			$class = $img->getAttribute( 'class' );
			$src   = $img->getAttribute( 'src' );

			if ( preg_match( '/(logo|brand)/i', $class ) || preg_match( '/(logo|brand)/i', $src ) ) {
				$alt = $img->getAttribute( 'alt' );
				if ( trim( $alt ) === '' ) {
					$img->setAttribute( 'alt', 'Site Logo' );
					++$fixed_count;
				}
			}
		}

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No fixes applied', $content );
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
