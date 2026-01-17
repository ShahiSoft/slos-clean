<?php
/**
 * Missing Alt Text Fixer
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
 * Class MissingAltFixer
 *
 * Adds alt attributes to images that are missing them.
 */
final class MissingAltFixer extends AbstractFixer {

	public function get_id(): string {
		return 'missing-alt-text';
	}

	public function get_name(): string {
		return __( 'Missing Alt Text', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds alt attributes to images that are missing them, using filename or attachment metadata when available.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.1.1' );
	}

	public function get_category(): string {
		return 'images';
	}

	public function can_fix( string $content ): bool {
		// Check for img tags without alt attribute..
		return (bool) preg_match( '/<img(?![^>]*\balt\s*=)[^>]*>/i', $content );
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$images        = $this->query( '//img[not(@alt)]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $images as $img ) {
			$src      = $img->getAttribute( 'src' );
			$alt_text = $this->generate_alt_text( $src, $options );

			$img->setAttribute( 'alt', $alt_text );
			++$fixes_applied;

			$details[] = array(
				'src'      => $src,
				'alt_text' => $alt_text,
				'method'   => $alt_text ? 'generated' : 'empty',
			);
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No images missing alt attribute', $content );
		}

		$fixed_content = $this->get_html();

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$fixed_content,
			$details
		);
	}

	/**
	 * Generate alt text from image source
	 *
	 * @param string $src Image source URL
	 * @param array  $options
	 * @return string
	 */
	private function generate_alt_text( string $src, array $options = array() ): string {
		// Try to get alt from WordPress media library..
		$attachment_id = $this->get_attachment_id_from_url( $src );

		if ( $attachment_id ) {
			// Check for existing alt text in media library..
			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			if ( ! empty( $alt ) ) {
				return $alt;
			}

			// Fall back to attachment title..
			$attachment = get_post( $attachment_id );
			if ( $attachment && ! empty( $attachment->post_title ) ) {
				return $this->humanize_filename( $attachment->post_title );
			}
		}

		// Generate from filename..
		$filename = basename( wp_parse_url( $src, PHP_URL_PATH ) ?: '' );

		if ( empty( $filename ) ) {
			return '';
		}

		return $this->humanize_filename( $filename );
	}

	/**
	 * Get attachment ID from URL
	 *
	 * @param string $url
	 * @return int
	 */
	private function get_attachment_id_from_url( string $url ): int {
		global $wpdb;

		// Remove image size suffix for lookup..
		$url = preg_replace( '/-\d+x\d+\.(jpg|jpeg|png|gif|webp)$/i', '.$1', $url );

		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . ' WHERE guid = %s',
				$url
			)
		);

		if ( ! $attachment_id ) {
			// Try by filename..
			$filename      = basename( $url );
			$attachment_id = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT post_id FROM ' . $wpdb->postmeta . " WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
					'%' . $wpdb->esc_like( $filename )
				)
			);
		}

		return (int) $attachment_id;
	}

	/**
	 * Convert filename to human-readable text
	 *
	 * @param string $filename
	 * @return string
	 */
	private function humanize_filename( string $filename ): string {
		// Remove extension..
		$name = preg_replace( '/\.[^.]+$/', '', $filename );

		// Replace separators with spaces..
		$name = str_replace( array( '-', '_' ), ' ', $name );

		// Remove common prefixes like IMG_, DSC_, etc...
		$name = preg_replace( '/^(IMG|DSC|DCIM|Photo|Image|Picture|Screen Shot|Screenshot)[\s_-]*/i', '', $name );

		// Remove size suffixes..
		$name = preg_replace( '/-?\d+x\d+$/', '', $name );

		// Remove trailing numbers..
		$name = preg_replace( '/[\s_-]+\d+$/', '', $name );

		// Clean up multiple spaces..
		$name = preg_replace( '/\s+/', ' ', trim( $name ) );

		// Capitalize words..
		$name = ucwords( strtolower( $name ) );

		return $name;
	}
}
