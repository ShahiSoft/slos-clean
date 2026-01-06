<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RedundantAriaFixer extends AbstractFixer {

	public function get_id(): string {
		return 'redundant-aria';
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		$semantic_roles = array(
			'button' => 'button',
			'a'      => 'link',
			'img'    => 'img',
			'h1'     => 'heading',
			'h2'     => 'heading',
			'h3'     => 'heading',
			'h4'     => 'heading',
			'h5'     => 'heading',
			'h6'     => 'heading',
			'header' => 'banner',
			'nav'    => 'navigation',
			'main'   => 'main',
			'footer' => 'contentinfo',
		);

		foreach ( $elements as $element ) {
			if ( ! $element instanceof \DOMElement ) {
				continue;
			}

			$tag = strtolower( $element->tagName );
			if ( isset( $semantic_roles[ $tag ] ) ) {
				$role = $element->getAttribute( 'role' );
				if ( $role === $semantic_roles[ $tag ] ) {
					$element->removeAttribute( 'role' );
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
			[]
		);
	}
}
