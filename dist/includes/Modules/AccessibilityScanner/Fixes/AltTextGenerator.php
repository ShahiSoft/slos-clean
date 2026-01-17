<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\AI\AIService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AltTextGenerator {

	private $ai_service;

	public function __construct() {
		$this->ai_service = new AIService();
	}

	/**
	 * Generate and save alt text for an attachment
	 *
	 * @param int $attachment_id
	 * @return array|WP_Error
	 */
	public function generate_for_attachment( $attachment_id ) {
		$url = wp_get_attachment_url( $attachment_id );
		if ( ! $url ) {
			return new \WP_Error( 'invalid_attachment', 'Invalid attachment ID' );
		}

		$alt_text = $this->ai_service->generate_alt_text( $url );

		if ( is_wp_error( $alt_text ) ) {
			return $alt_text;
		}

		// Update the attachment metadata..
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );

		return array(
			'attachment_id' => $attachment_id,
			'alt_text'      => $alt_text,
			'message'       => 'Alt text generated and saved successfully.',
		);
	}
}
