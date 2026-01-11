<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SemanticHtmlFixer extends AbstractFixer {

	public function get_id(): string {
		return 'semantic-html';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$divs        = $dom->getElementsByTagName( 'div' );
		$fixed_count = 0;

		$div_array = array();
		foreach ( $divs as $d ) {
			$div_array[] = $d;
		}

		foreach ( $div_array as $div ) {
			if ( ! $div instanceof \DOMElement ) {
				continue;
			}

			$role = $div->getAttribute( 'role' );

			$role_to_tag = array(
				'main'          => 'main',
				'banner'        => 'header',
				'navigation'    => 'nav',
				'contentinfo'   => 'footer',
				'complementary' => 'aside',
			);

			if ( $role && isset( $role_to_tag[ $role ] ) ) {
				$new_tag                  = $role_to_tag[ $role ];
				$new_element              = $dom->createElement( $new_tag );
				$new_element->textContent = $div->textContent;

				foreach ( $div->attributes as $attr ) {
					if ( $attr->name !== 'role' ) {
						$new_element->setAttribute( $attr->name, $attr->value );
					}
				}

				if ( $div->parentNode ) {
					$div->parentNode->replaceChild( $new_element, $div );
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
