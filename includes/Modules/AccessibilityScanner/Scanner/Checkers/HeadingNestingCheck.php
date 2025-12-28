<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HeadingNestingCheck extends AbstractCheck {

	public function get_id() {
		return 'heading-nesting';
	}

	public function get_description() {
		return 'Sectioning elements (section, article) should contain a heading.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Check sections
		$sections = $dom->getElementsByTagName( 'section' );
		foreach ( $sections as $section ) {
			if ( ! $this->has_heading( $section ) ) {
				$issues[] = array(
					'element' => 'section',
					'context' => 'Section element',
					'message' => 'Section element does not contain a heading.',
				);
			}
		}

		// Check articles
		$articles = $dom->getElementsByTagName( 'article' );
		foreach ( $articles as $article ) {
			if ( ! $this->has_heading( $article ) ) {
				$issues[] = array(
					'element' => 'article',
					'context' => 'Article element',
					'message' => 'Article element does not contain a heading.',
				);
			}
		}

		return $issues;
	}

	private function has_heading( $element ) {
		// Check direct children or descendants for h1-h6
		// This is a simplified check. A robust one would check if the heading belongs to this section scope.
		// For now, we check if there is ANY heading inside.
		$headings = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		foreach ( $headings as $h ) {
			if ( $element->getElementsByTagName( $h )->length > 0 ) {
				return true;
			}
		}
		return false;
	}
}

