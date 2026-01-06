<?php
/**
 * Missing H1 Fixer (Legacy Adapter)
 *
 * Thin adapter that delegates to the existing legacy MissingH1Fixer
 * implementation in the Fixes/ system, while exposing the canonical
 * FixEngine ID and FixResult contract.
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MissingH1Fixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-h1';
	}

	public function get_name(): string {
		return __( 'Missing H1', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Ensures the page has a top-level H1 heading, delegating to the stable legacy fixer logic.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '2.4.6' ];
	}

	public function get_category(): string {
		return 'headings';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<h1' ) === false && stripos( $content, '<h' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$h1s = $dom->getElementsByTagName( 'h1' );
		if ( $h1s->length === 0 ) {
			$h2s = $dom->getElementsByTagName( 'h2' );
			if ( $h2s->length > 0 ) {
				$first_h2 = $h2s->item( 0 );
				if ( $first_h2 instanceof \DOMElement ) {
					$h1              = $dom->createElement( 'h1' );
					$h1->textContent = $first_h2->textContent;

					foreach ( $first_h2->attributes as $attr ) {
						$h1->setAttribute( $attr->nodeName, $attr->nodeValue );
					}

					if ( $first_h2->parentNode ) {
						$first_h2->parentNode->replaceChild( $h1, $first_h2 );
					}

					$fixed_content = $this->get_html( $dom );

					return FixResult::success(
						$this->get_id(),
						1,
						$content,
						$fixed_content,
						[]
					);
				}
			}

			$body = $dom->getElementsByTagName( 'body' )->item( 0 );
			if ( $body instanceof \DOMElement ) {
				$h1              = $dom->createElement( 'h1' );
				$title           = function_exists( 'get_the_title' ) ? get_the_title() : '';
				$site_name       = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'name' ) : '';
				$h1->textContent = $title ?: $site_name;

				if ( $body->firstChild ) {
					$body->insertBefore( $h1, $body->firstChild );
				} else {
					$body->appendChild( $h1 );
				}

				$fixed_content = $this->get_html( $dom );

				return FixResult::success(
					$this->get_id(),
					1,
					$content,
					$fixed_content,
					[]
				);
			}
		}

		return FixResult::skipped( $this->get_id(), 'No H1 fixes applied', $content );
	}
}
