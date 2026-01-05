<?php
/**
 * Document Title Fixer
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
 * Class DocumentTitleFixer
 * 
 * Ensures document has a proper title element.
 */
final class DocumentTitleFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-page-title';
	}

	public function get_name(): string {
		return __( 'Document Title', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds or fixes the document title element for screen readers and SEO.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '2.4.2' ];
	}

	public function get_category(): string {
		return 'document';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<head' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$titles = $this->query( '//head/title' );
		$fixes_applied = 0;
		$details = [];

		if ( count( $titles ) === 0 ) {
			// No title element - need to add one
			$heads = $this->query( '//head' );
			
			if ( count( $heads ) === 0 ) {
				return FixResult::skipped( $this->get_id(), 'No head element found', $content );
			}

			$head = $heads[0];
			$title = $this->doc->createElement( 'title' );
			
			// Try to generate a meaningful title from h1 or post data
			$title_text = $this->generate_title();
			$title->textContent = $title_text;
			
			// Insert title as first child of head
			if ( $head->firstChild ) {
				$head->insertBefore( $title, $head->firstChild );
			} else {
				$head->appendChild( $title );
			}

			$fixes_applied++;
			$details[] = [
				'action' => 'created',
				'title'  => $title_text,
			];
		} else {
			$title = $titles[0];
			$title_text = trim( $title->textContent );

			// Check if title is empty or just whitespace
			if ( empty( $title_text ) ) {
				$title->textContent = $this->generate_title();
				$fixes_applied++;
				$details[] = [
					'action'    => 'fixed_empty',
					'new_title' => $title->textContent,
				];
			}
			// Check if title is just "Untitled" or similar
			elseif ( preg_match( '/^(untitled|no title|document|page)$/i', $title_text ) ) {
				$title->textContent = $this->generate_title();
				$fixes_applied++;
				$details[] = [
					'action'    => 'improved',
					'old_title' => $title_text,
					'new_title' => $title->textContent,
				];
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'Document has proper title', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Generate a title from page content
	 */
	private function generate_title(): string {
		// Try to use h1
		$h1s = $this->query( '//h1' );
		if ( count( $h1s ) > 0 ) {
			$h1_text = trim( $h1s[0]->textContent );
			if ( ! empty( $h1_text ) ) {
				return $h1_text;
			}
		}

		// Try first h2
		$h2s = $this->query( '//h2' );
		if ( count( $h2s ) > 0 ) {
			$h2_text = trim( $h2s[0]->textContent );
			if ( ! empty( $h2_text ) ) {
				return $h2_text;
			}
		}

		// Try og:title meta
		$og_titles = $this->query( '//meta[@property="og:title"]/@content' );
		if ( count( $og_titles ) > 0 ) {
			$og_text = trim( $og_titles[0]->nodeValue );
			if ( ! empty( $og_text ) ) {
				return $og_text;
			}
		}

		// Fallback
		return __( 'Page Content', 'shahi-legalflowsuite' );
	}
}
