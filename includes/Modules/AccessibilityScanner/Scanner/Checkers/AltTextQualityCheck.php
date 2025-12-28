<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for alt text quality issues
 * WCAG 1.1.1 - Non-text Content (Level A)
 */
class AltTextQualityCheck extends AbstractCheck {

	/**
	 * File name patterns that indicate placeholder alt text
	 */
	private $filename_patterns = array(
		'/^IMG_\d+/i',           // IMG_1234
		'/^DSC_?\d+/i',          // DSC_0001, DSC0001
		'/^DCIM_?\d+/i',         // DCIM_0001
		'/^photo_?\d+/i',        // photo_123, photo123
		'/^image_?\d+/i',        // image_123, image123
		'/^pic_?\d+/i',          // pic_123, pic123
		'/^screenshot_?\d*/i',   // screenshot_123, screenshot
		'/\.(jpg|jpeg|png|gif|webp|svg|bmp|tiff?)$/i', // file.jpg
		'/^[a-f0-9]{8,}$/i',     // Hash-like names (abc123def456)
		'/^\d{8,}$/i',           // Numeric IDs
		'/^IMG-\d{8}-WA\d+/i',   // WhatsApp images
		'/^PXL_\d+/i',           // Google Pixel photos
	);

	/**
	 * Placeholder words that indicate poor alt text
	 */
	private $placeholder_words = array(
		'img',
		'image',
		'test',
		'pic',
		'picture',
		'placeholder',
		'photo',
		'graphic',
		'untitled',
		'unnamed',
		'temp',
		'asdf',
		'todo',
		'fixme',
	);

	public function get_id() {
		return 'alt-text-quality';
	}

	public function get_description() {
		return 'Alt text should be meaningful, concise, and not contain filenames, URLs, or redundant phrases.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.1.1';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Write alt text that describes the image content or function. Avoid filenames, URLs, and phrases like "image of".';
	}

	public function check( $content ) {
		$issues = array();
		$images = $this->get_elements( $content, 'img' );

		foreach ( $images as $img ) {
			if ( ! $img->hasAttribute( 'alt' ) ) {
				continue;
			}

			$alt = trim( $img->getAttribute( 'alt' ) );
			if ( empty( $alt ) ) {
				continue;
			}

			// Check for redundant "image of" phrases
			$this->check_redundant_phrases( $img, $alt, $issues );

			// Check for filename patterns
			$this->check_filename_patterns( $img, $alt, $issues );

			// Check for URL in alt text
			$this->check_url_in_alt( $img, $alt, $issues );

			// Check for placeholder text
			$this->check_placeholder_text( $img, $alt, $issues );

			// Check for excessive length
			$this->check_alt_length( $img, $alt, $issues );

			// Check for all caps (accessibility issue for screen readers)
			$this->check_all_caps( $img, $alt, $issues );

			// Check for special characters only
			$this->check_special_chars_only( $img, $alt, $issues );
		}

		return $issues;
	}

	/**
	 * Check for redundant phrases like "image of", "picture of"
	 */
	private function check_redundant_phrases( $img, $alt, &$issues ) {
		$redundant_patterns = array(
			'/^(image|picture|photo|graphic|icon|logo|banner|button) of\s/i',
			'/^(an? )(image|picture|photo|graphic|icon) (of|showing|displaying)\s/i',
			'/^this (is )?(an? )?(image|picture|photo) of\s/i',
		);

		foreach ( $redundant_patterns as $pattern ) {
			if ( preg_match( $pattern, $alt ) ) {
				$issues[] = array(
					'element'    => 'img',
					'context'    => $this->get_element_html( $img ),
					'message'    => 'Alt text contains redundant phrase like "image of". Screen readers already announce it as an image.',
					'confidence' => 'high',
				);
				break;
			}
		}
	}

	/**
	 * Check for filename patterns in alt text
	 */
	private function check_filename_patterns( $img, $alt, &$issues ) {
		foreach ( $this->filename_patterns as $pattern ) {
			if ( preg_match( $pattern, $alt ) ) {
				$issues[] = array(
					'element'    => 'img',
					'context'    => $this->get_element_html( $img ),
					'message'    => "Alt text appears to be a filename: \"$alt\". Provide a meaningful description of the image.",
					'confidence' => 'high',
				);
				return; // Only report once
			}
		}
	}

	/**
	 * Check for URL in alt text
	 */
	private function check_url_in_alt( $img, $alt, &$issues ) {
		if ( preg_match( '/^https?:\/\//i', $alt ) || preg_match( '/^www\./i', $alt ) ) {
			$issues[] = array(
				'element'    => 'img',
				'context'    => $this->get_element_html( $img ),
				'message'    => 'Alt text contains a URL. Provide a text description instead.',
				'confidence' => 'high',
			);
		}
	}

	/**
	 * Check for placeholder text
	 */
	private function check_placeholder_text( $img, $alt, &$issues ) {
		$alt_lower = strtolower( $alt );

		// Exact match with placeholder words
		if ( in_array( $alt_lower, $this->placeholder_words, true ) ) {
			$issues[] = array(
				'element'    => 'img',
				'context'    => $this->get_element_html( $img ),
				'message'    => "Alt text \"$alt\" appears to be a placeholder. Provide a meaningful description.",
				'confidence' => 'high',
			);
		}
	}

	/**
	 * Check for excessive alt text length
	 */
	private function check_alt_length( $img, $alt, &$issues ) {
		$length = strlen( $alt );

		if ( $length > 125 ) {
			$severity = $length > 250 ? 'warning' : 'notice';
			$issues[] = array(
				'element'    => 'img',
				'context'    => $this->get_element_html( $img ),
				'message'    => sprintf(
					'Alt text is very long (%d characters). Consider using a shorter description or longdesc for complex images.',
					$length
				),
				'severity'   => $severity,
				'confidence' => 'high',
			);
		}
	}

	/**
	 * Check for all caps alt text (hard for screen readers)
	 */
	private function check_all_caps( $img, $alt, &$issues ) {
		// Only check if more than 3 words and all caps
		$words = preg_split( '/\s+/', $alt );
		if ( count( $words ) > 3 && $alt === strtoupper( $alt ) && preg_match( '/[A-Z]/', $alt ) ) {
			$issues[] = array(
				'element'    => 'img',
				'context'    => $this->get_element_html( $img ),
				'message'    => 'Alt text is in ALL CAPS. Some screen readers may spell out each letter. Use sentence case.',
				'severity'   => 'notice',
				'confidence' => 'medium',
			);
		}
	}

	/**
	 * Check for special characters only
	 */
	private function check_special_chars_only( $img, $alt, &$issues ) {
		// Alt text that's only special characters or numbers
		if ( preg_match( '/^[\d\s\-_.,!@#$%^&*()+=\[\]{}|\\:";\'<>?,./]+$/', $alt ) ) {
			$issues[] = array(
				'element'    => 'img',
				'context'    => $this->get_element_html( $img ),
				'message'    => "Alt text \"$alt\" contains only numbers/special characters. Provide a meaningful text description.",
				'confidence' => 'high',
			);
		}
	}

	private function get_element_html( $node ) {
		$html = $node->ownerDocument->saveHTML( $node );
		return strlen( $html ) > 200 ? substr( $html, 0, 200 ) . '...' : $html;
	}
}

