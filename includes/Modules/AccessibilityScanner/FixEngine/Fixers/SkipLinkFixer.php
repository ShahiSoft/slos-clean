<?php
/**
 * Skip Link Fixer
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
 * Class SkipLinkFixer
 * 
 * Adds skip navigation link to page content.
 */
final class SkipLinkFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-skip-link';
	}

	public function get_name(): string {
		return __( 'Skip Navigation Link', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds a skip navigation link to allow keyboard users to bypass repetitive content.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '2.4.1' ];
	}

	public function get_category(): string {
		return 'content';
	}

	public function can_fix( string $content ): bool {
		// Check if skip link already exists
		return stripos( $content, 'skip' ) === false || 
			   stripos( $content, '#main' ) === false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details = [];

		// Check for existing skip link
		$skip_links = $this->query( '//a[contains(@class, "skip") or contains(@href, "#main") or contains(@href, "#content")]' );
		
		if ( count( $skip_links ) > 0 ) {
			return FixResult::skipped( $this->get_id(), 'Skip link already exists', $content );
		}

		// Find main content area
		$main = $this->query( '//main' );
		$main_id = 'main-content';

		if ( count( $main ) > 0 ) {
			$main_element = $main[0];
			$existing_id = $main_element->getAttribute( 'id' );
			
			if ( empty( $existing_id ) ) {
				$main_element->setAttribute( 'id', $main_id );
				$fixes_applied++;
			} else {
				$main_id = $existing_id;
			}
		} else {
			// Look for content div
			$content_divs = $this->query( '//div[@id="content" or @id="main" or contains(@class, "content") or contains(@class, "main")]' );
			
			if ( count( $content_divs ) > 0 ) {
				$main_element = $content_divs[0];
				$existing_id = $main_element->getAttribute( 'id' );
				
				if ( empty( $existing_id ) ) {
					$main_element->setAttribute( 'id', $main_id );
					$fixes_applied++;
				} else {
					$main_id = $existing_id;
				}
			}
		}

		// Add skip link at the start of body
		$body = $this->query( '//body' );
		
		if ( count( $body ) > 0 ) {
			$body_element = $body[0];
			
			// Create skip link
			$skip_link = $this->doc->createElement( 'a' );
			$skip_link->setAttribute( 'href', '#' . $main_id );
			$skip_link->setAttribute( 'class', 'skip-link screen-reader-text' );
			$skip_link->textContent = __( 'Skip to main content', 'shahi-legalflowsuite' );

			// Insert at the beginning of body
			if ( $body_element->firstChild ) {
				$body_element->insertBefore( $skip_link, $body_element->firstChild );
			} else {
				$body_element->appendChild( $skip_link );
			}

			$fixes_applied++;
			$details[] = [
				'action'    => 'added_skip_link',
				'target_id' => $main_id,
			];
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'Could not add skip link', $content );
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
