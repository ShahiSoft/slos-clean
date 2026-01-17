<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skipped Heading Level Fixer
 */
class SkippedHeadingLevelFixer extends BaseFixer {
	public function get_id() {
		return 'skipped-heading-level'; }
	public function get_description() {
		return 'Fix skipped heading levels'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$headings    = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );
		$fixed_count = 0;
		$last_level  = 0;

		$headings_array = array();
		foreach ( $headings as $h ) {
			$headings_array[] = $h;
		}

		foreach ( $headings_array as $heading ) {
			$level = intval( $heading->tagName[1] );

			if ( $last_level > 0 && $level > $last_level + 1 ) {
				// Skipped level, convert to appropriate level..
				$new_level = $last_level + 1;
				$new_tag   = "h$new_level";

				$new_heading              = $dom->createElement( $new_tag );
				$new_heading->textContent = $heading->textContent;
				$heading->parentNode->replaceChild( $new_heading, $heading );
				$heading = $new_heading;
				++$fixed_count;
			}

			$last_level = intval( $heading->tagName[1] );
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Heading Nesting Fixer
 */
class HeadingNestingFixer extends BaseFixer {
	public function get_id() {
		return 'heading-nesting'; }
	public function get_description() {
		return 'Fix heading structure'; }

	public function fix( $content ) {
		// This is complex structural fix - delegate to content modification..
		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Heading Length Fixer
 */
class HeadingLengthFixer extends BaseFixer {
	public function get_id() {
		return 'heading-length'; }
	public function get_description() {
		return 'Optimize heading length'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$headings    = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );
		$fixed_count = 0;

		foreach ( $headings as $heading ) {
			$text = $heading->textContent;
			if ( strlen( $text ) > 100 ) {
				// Truncate with ellipsis..
				$heading->textContent = substr( $text, 0, 97 ) . '...';
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Heading Uniqueness Fixer
 */
class HeadingUniquenessFixer extends BaseFixer {
	public function get_id() {
		return 'heading-uniqueness'; }
	public function get_description() {
		return 'Ensure unique headings'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$headings    = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );
		$fixed_count = 0;
		$seen_texts  = array();

		foreach ( $headings as $heading ) {
			$text = trim( $heading->textContent );
			if ( isset( $seen_texts[ $text ] ) ) {
				// Add number to make unique..
				$heading->textContent = $text . ' (' . ( $seen_texts[ $text ] + 1 ) . ')';
				++$fixed_count;
			}
			$seen_texts[ $text ] = ( $seen_texts[ $text ] ?? 0 ) + 1;
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Heading Visual Check Fixer
 */
class HeadingVisualFixer extends BaseFixer {
	public function get_id() {
		return 'heading-visual'; }
	public function get_description() {
		return 'Mark visually styled headings'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$divs        = $dom->getElementsByTagName( 'div' );
		$spans       = $dom->getElementsByTagName( 'span' );
		$fixed_count = 0;

		// Find large styled text that might be headings..
		foreach ( $divs as $div ) {
			$class = $div->getAttribute( 'class' );
			$style = $div->getAttribute( 'style' );

			if ( preg_match( '/(title|heading|header|large)/i', $class ) ||
				preg_match( '/(font-size:\s*(24|28|32|36)px)/i', $style ) ) {
				if ( ! $div->hasAttribute( 'role' ) ) {
					$div->setAttribute( 'role', 'heading' );
					$div->setAttribute( 'aria-level', '2' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}
