<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIService {

	private $api_key;
	private $api_endpoint = 'https://api.openai.com/v1/chat/completions';

	public function __construct() {
		// In a real scenario, this would come from plugin settings..
		$this->api_key = get_option( 'slos_openai_api_key', '' );
	}

	/**
	 * Generate Alt Text for an image
	 *
	 * @param string $image_url
	 * @return string|WP_Error
	 */
	public function generate_alt_text( $image_url ) {
		if ( empty( $this->api_key ) ) {
			return new \WP_Error( 'missing_api_key', 'OpenAI API Key is not configured.' );
		}

		$body = array(
			'model'      => 'gpt-4-vision-preview', // Or gpt-4o
			'messages'   => array(
				array(
					'role'    => 'user',
					'content' => array(
						array(
							'type' => 'text',
							'text' => 'Generate a concise, descriptive alt text for this image for accessibility purposes. Do not start with "Image of" or "Picture of".',
						),
						array(
							'type'      => 'image_url',
							'image_url' => array(
								'url' => $image_url,
							),
						),
					),
				),
			),
			'max_tokens' => 100,
		);

		$response = wp_remote_post(
			$this->api_endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			return trim( $body['choices'][0]['message']['content'] );
		}

		return new \WP_Error( 'invalid_response', 'Invalid response from AI API' );
	}
}
