<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DownloadLinkFixer extends AbstractFixer {

	public function get_id(): string {
		return 'download-link';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom         = $this->parse_html( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;

		$download_extensions = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'ppt', 'pptx', 'csv', 'txt', 'rtf', 'exe', 'dmg', 'pkg', 'rar', '7z', 'tar', 'gz' );

		// Convert to array to avoid issues with modifying during iteration..
		$links_array = array();
		foreach ( $links as $link ) {
			$links_array[] = $link;
		}

		foreach ( $links_array as $link ) {
			$href = $link->getAttribute( 'href' );

			// Skip if no href..
			if ( empty( $href ) ) {
				continue;
			}

			$is_download = false;
			$file_ext    = '';
			$href_lower  = strtolower( $href );

			// Check file extension..
			foreach ( $download_extensions as $ext ) {
				if ( preg_match( '/\.' . preg_quote( $ext, '/' ) . '(\?|#|$)/i', $href_lower ) ) {
					$is_download = true;
					$file_ext    = strtoupper( $ext );
					break;
				}
			}

			// Also check for download attribute..
			if ( ! $is_download && $link->hasAttribute( 'download' ) ) {
				$is_download = true;
				$path        = wp_parse_url( $href, PHP_URL_PATH );
				if ( $path ) {
					$path_ext = pathinfo( $path, PATHINFO_EXTENSION );
					$file_ext = $path_ext ? strtoupper( $path_ext ) : 'FILE';
				} else {
					$file_ext = 'FILE';
				}
			}

			if ( $is_download ) {
				$text = trim( $link->textContent );
				// Check if already has file type indicator (PDF), (2.5MB), etc...
				if ( ! preg_match( '/\([A-Z]{2,4}(\s*,?\s*[\d.]\s*(KB|MB|GB))?\)/i', $text ) ) {
					// Clear existing content and set new..
					while ( $link->firstChild ) {
						$link->removeChild( $link->firstChild );
					}
					$link->appendChild( $dom->createTextNode( $text . ' (' . $file_ext . ')' ) );
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
			array()
		);
	}
}
