<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DownloadLinkCheck extends AbstractCheck {

	public function get_id() {
		return 'download-link';
	}

	public function get_description() {
		return 'Download links should indicate file type and size.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '3.2.4'; // Consistent Identification
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$links  = $dom->getElementsByTagName( 'a' );

		$extensions = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar' );

		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );
			$ext  = pathinfo( $href, PATHINFO_EXTENSION );

			if ( in_array( strtolower( $ext ), $extensions ) ) {
				$text = $link->textContent;
				// Check if text contains extension..
				if ( stripos( $text, $ext ) === false ) {
					$issues[] = array(
						'element' => 'a',
						'context' => $this->get_element_html( $link ),
						'message' => "Link to .$ext file should indicate file type in text (e.g. 'Report (PDF)').",
					);
				}
			}
		}

		return $issues;
	}
}
