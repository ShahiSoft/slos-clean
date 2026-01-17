<?php
/**
 * Iframe Accessibility Fixer
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
 * Class IframeAccessibilityFixer
 *
 * Adds title attributes and lazy loading to iframes.
 */
final class IframeAccessibilityFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-iframe-title';
	}

	public function get_name(): string {
		return __( 'Iframe Accessibility', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds descriptive titles to iframes for screen reader users.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '2.4.1', '4.1.2' );
	}

	public function get_category(): string {
		return 'media';
	}

	public function can_fix( string $content ): bool {
		return stripos( $content, '<iframe' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		// Find iframes without title..
		$iframes       = $this->query( '//iframe[not(@title) or @title=""]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $iframes as $iframe ) {
			$src   = $iframe->getAttribute( 'src' ) ?: '';
			$title = $this->generate_iframe_title( $src );

			$iframe->setAttribute( 'title', $title );

			// Also add lazy loading if not present..
			if ( ! $iframe->hasAttribute( 'loading' ) ) {
				$iframe->setAttribute( 'loading', 'lazy' );
			}

			++$fixes_applied;
			$details[] = array(
				'src'   => substr( $src, 0, 100 ),
				'title' => $title,
			);
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'All iframes have titles', $content );
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
	 * Generate a descriptive title based on iframe source
	 */
	private function generate_iframe_title( string $src ): string {
		// Common embed patterns..
		$patterns = array(
			'youtube.com'     => __( 'YouTube video player', 'shahi-legalflowsuite' ),
			'youtu.be'        => __( 'YouTube video player', 'shahi-legalflowsuite' ),
			'vimeo.com'       => __( 'Vimeo video player', 'shahi-legalflowsuite' ),
			'dailymotion'     => __( 'Dailymotion video player', 'shahi-legalflowsuite' ),
			'spotify.com'     => __( 'Spotify music player', 'shahi-legalflowsuite' ),
			'soundcloud.com'  => __( 'SoundCloud audio player', 'shahi-legalflowsuite' ),
			'google.com/maps' => __( 'Google Maps embed', 'shahi-legalflowsuite' ),
			'maps.google'     => __( 'Google Maps embed', 'shahi-legalflowsuite' ),
			'twitter.com'     => __( 'Twitter embed', 'shahi-legalflowsuite' ),
			'x.com'           => __( 'X (Twitter) embed', 'shahi-legalflowsuite' ),
			'facebook.com'    => __( 'Facebook embed', 'shahi-legalflowsuite' ),
			'instagram.com'   => __( 'Instagram embed', 'shahi-legalflowsuite' ),
			'linkedin.com'    => __( 'LinkedIn embed', 'shahi-legalflowsuite' ),
			'slideshare.net'  => __( 'SlideShare presentation', 'shahi-legalflowsuite' ),
			'codepen.io'      => __( 'CodePen embed', 'shahi-legalflowsuite' ),
			'jsfiddle.net'    => __( 'JSFiddle embed', 'shahi-legalflowsuite' ),
			'typeform.com'    => __( 'Typeform survey', 'shahi-legalflowsuite' ),
			'calendly.com'    => __( 'Calendly scheduling widget', 'shahi-legalflowsuite' ),
			'stripe.com'      => __( 'Stripe payment form', 'shahi-legalflowsuite' ),
			'paypal.com'      => __( 'PayPal payment form', 'shahi-legalflowsuite' ),
			'recaptcha'       => __( 'reCAPTCHA verification', 'shahi-legalflowsuite' ),
		);

		foreach ( $patterns as $pattern => $title ) {
			if ( stripos( $src, $pattern ) !== false ) {
				return $title;
			}
		}

		// Try to extract domain name for generic title..
		$parsed = wp_parse_url( $src );
		if ( ! empty( $parsed['host'] ) ) {
			$host = preg_replace( '/^www\./', '', $parsed['host'] );
			return sprintf(
				/* translators: %s: host or domain name (e.g. example.com) */
				__( 'Embedded content from %s', 'shahi-legalflowsuite' ),
				$host
			);
		}

		return __( 'Embedded content', 'shahi-legalflowsuite' );
	}
}
