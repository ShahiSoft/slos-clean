<?php
/**
 * Empty Link Fixer
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
 * Class EmptyLinkFixer
 *
 * Fixes links that have no accessible text content.
 */
final class EmptyLinkFixer extends AbstractFixer {

	public function get_id(): string {
		return 'empty-link';
	}

	public function get_name(): string {
		return __( 'Empty Links', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds accessible text to links that have no text content, using context clues and URL analysis.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '2.4.4', '4.1.2' );
	}

	public function get_category(): string {
		return 'links';
	}

	public function can_fix( string $content ): bool {
		// Check for potentially empty links..
		return strpos( $content, '<a' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$links         = $this->query( '//a[@href]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $links as $link ) {
			if ( ! $this->is_empty_link( $link ) ) {
				continue;
			}

			$href            = $link->getAttribute( 'href' );
			$accessible_name = $this->generate_accessible_name( $link, $href );

			if ( ! empty( $accessible_name ) ) {
				// Check if link has only image child..
				$images = $link->getElementsByTagName( 'img' );
				if ( $images->length === 1 && trim( $link->textContent ) === '' ) {
					// Add alt to image instead..
					$img = $images->item( 0 );
					if ( empty( $img->getAttribute( 'alt' ) ) ) {
						$img->setAttribute( 'alt', $accessible_name );
					}
				} else {
					// Add visually hidden span..
					$span = $this->doc->createElement( 'span' );
					$span->setAttribute( 'class', 'screen-reader-text sr-only visually-hidden' );
					$span->textContent = $accessible_name;
					$link->appendChild( $span );
				}

				// Also add aria-label as backup..
				if ( ! $link->hasAttribute( 'aria-label' ) && ! $link->hasAttribute( 'aria-labelledby' ) ) {
					$link->setAttribute( 'aria-label', $accessible_name );
				}

				++$fixes_applied;
				$details[] = array(
					'href'            => $href,
					'accessible_name' => $accessible_name,
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No empty links found', $content );
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
	 * Check if link is effectively empty
	 *
	 * @param \DOMElement $link
	 * @return bool
	 */
	private function is_empty_link( \DOMElement $link ): bool {
		// Has aria-label..
		if ( ! empty( $link->getAttribute( 'aria-label' ) ) ) {
			return false;
		}

		// Has aria-labelledby..
		if ( ! empty( $link->getAttribute( 'aria-labelledby' ) ) ) {
			return false;
		}

		// Has text content..
		$text = trim( $link->textContent );
		if ( ! empty( $text ) ) {
			return false;
		}

		// Has image with alt..
		$images = $link->getElementsByTagName( 'img' );
		foreach ( $images as $img ) {
			if ( ! empty( $img->getAttribute( 'alt' ) ) ) {
				return false;
			}
		}

		// Has title (not ideal but provides some accessibility)..
		if ( ! empty( $link->getAttribute( 'title' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate accessible name for link
	 *
	 * @param \DOMElement $link
	 * @param string      $href
	 * @return string
	 */
	private function generate_accessible_name( \DOMElement $link, string $href ): string {
		// Check title attribute..
		$title = $link->getAttribute( 'title' );
		if ( ! empty( $title ) ) {
			return $title;
		}

		// Check for common social media patterns..
		$social_name = $this->detect_social_link( $href );
		if ( $social_name ) {
			return $social_name;
		}

		// Check for common action patterns..
		$action_name = $this->detect_action_link( $href, $link );
		if ( $action_name ) {
			return $action_name;
		}

		// Parse URL for context..
		return $this->generate_from_url( $href );
	}

	/**
	 * Detect social media links
	 *
	 * @param string $href
	 * @return string|null
	 */
	private function detect_social_link( string $href ): ?string {
		$social_patterns = array(
			'facebook.com'  => __( 'Visit our Facebook page', 'shahi-legalflowsuite' ),
			'twitter.com'   => __( 'Visit our Twitter profile', 'shahi-legalflowsuite' ),
			'x.com'         => __( 'Visit our X profile', 'shahi-legalflowsuite' ),
			'instagram.com' => __( 'Visit our Instagram profile', 'shahi-legalflowsuite' ),
			'linkedin.com'  => __( 'Visit our LinkedIn page', 'shahi-legalflowsuite' ),
			'youtube.com'   => __( 'Visit our YouTube channel', 'shahi-legalflowsuite' ),
			'pinterest.com' => __( 'Visit our Pinterest page', 'shahi-legalflowsuite' ),
			'tiktok.com'    => __( 'Visit our TikTok profile', 'shahi-legalflowsuite' ),
			'github.com'    => __( 'Visit our GitHub repository', 'shahi-legalflowsuite' ),
		);

		foreach ( $social_patterns as $domain => $label ) {
			if ( strpos( $href, $domain ) !== false ) {
				return $label;
			}
		}

		return null;
	}

	/**
	 * Detect action links (mailto, tel, etc.)
	 *
	 * @param string      $href
	 * @param \DOMElement $link
	 * @return string|null
	 */
	private function detect_action_link( string $href, \DOMElement $link ): ?string {
		if ( strpos( $href, 'mailto:' ) === 0 ) {
			$email = str_replace( 'mailto:', '', $href );
			$email = explode( '?', $email )[0]; // Remove query params
			return sprintf( __( /* translators: %s: email address */  'Send email to %s', 'shahi-legalflowsuite' ), $email );
		}

		if ( strpos( $href, 'tel:' ) === 0 ) {
			$phone = str_replace( 'tel:', '', $href );
			return sprintf( __( /* translators: %s: phone number */  'Call %s', 'shahi-legalflowsuite' ), $phone );
		}

		if ( strpos( $href, '#' ) === 0 ) {
			$anchor = substr( $href, 1 );
			if ( ! empty( $anchor ) ) {
				$readable = str_replace( array( '-', '_' ), ' ', $anchor );
				return sprintf( __( /* translators: %s: anchor name */  'Jump to %s', 'shahi-legalflowsuite' ), ucwords( $readable ) );
			}
		}

		// Check for download links..
		$class = $link->getAttribute( 'class' );
		if ( strpos( $class, 'download' ) !== false || $link->hasAttribute( 'download' ) ) {
			return __( 'Download file', 'shahi-legalflowsuite' );
		}

		return null;
	}

	/**
	 * Generate label from URL
	 *
	 * @param string $href
	 * @return string
	 */
	private function generate_from_url( string $href ): string {
		$parsed = wp_parse_url( $href );

		if ( ! empty( $parsed['host'] ) ) {
			// External link..
			$domain = preg_replace( '/^www\./', '', $parsed['host'] );
			return sprintf( __( /* translators: %s: domain name */  'Visit %s', 'shahi-legalflowsuite' ), $domain );
		}

		if ( ! empty( $parsed['path'] ) ) {
			$path     = basename( $parsed['path'] );
			$readable = str_replace( array( '-', '_' ), ' ', preg_replace( '/\.[^.]+$/', '', $path ) );
			if ( ! empty( $readable ) ) {
				return ucwords( $readable );
			}
		}

		return __( 'Link', 'shahi-legalflowsuite' );
	}
}
