<?php
/**
 * DSR Verify Shortcode
 *
 * Renders a verification result view for DSR email verification.
 *
 * Shortcode: [slos_dsr_verify]
 * Usage: Place on a page like /dsr-verify/ and pass token via URL: /dsr-verify/?token=...
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Shortcodes
 * @version     3.0.1
 * @since       3.0.1
 */

// phpcs:ignore WordPress.Files.FileName -- file naming follows existing project structure and renaming would break includes.

namespace ShahiLegalFlowSuite\Shortcodes;

use ShahiLegalFlowSuite\Core\I18n;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify nonce for token processing...
if ( isset( $_GET['token'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['token'] ) ), 'slos_dsr_verify_nonce' ) ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification happens here.
	die( esc_html__( 'Invalid token.', 'shahi-legalflowsuite' ) );
}

/**
 * Class DSR_Verify_Shortcode
 *
 * Handles [slos_dsr_verify] shortcode rendering by calling the public REST verify endpoint
 * and displaying a user-friendly message.
 *
 * @since 3.0.1
 */
class DSR_Verify_Shortcode {

	/**
	 * Text domain constant
	 *
	 * @since 3.0.1
	 * @var string
	 */
	const TEXT_DOMAIN = I18n::TEXT_DOMAIN;

	/**
	 * Initialize shortcode
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function init() {
		add_shortcode( 'slos_dsr_verify', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode output
	 *
	 * @since 3.0.1
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output
	 */
	public function render( array $atts = array() ): string {
		// Enqueue shared DSR form CSS for consistent styling...
		if ( defined( 'SHAHI_LEGALFLOWSUITE_PLUGIN_URL' ) && defined( 'SHAHI_LEGALFLOWSUITE_VERSION' ) ) {
			wp_enqueue_style(
				'slos-dsr-form',
				SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/dsr-form.css',
				array(),
				SHAHI_LEGALFLOWSUITE_VERSION
			);
		}

		// Token is provided via verification email links; nonce may not always be present...
		// phpcs:ignore WordPress.Security.NonceVerification -- verification token comes from email link, validated by remote API response below.
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		if ( empty( $token ) && isset( $atts['token'] ) ) {
			$token = sanitize_text_field( (string) $atts['token'] );
		}

		// Wrapper...
		ob_start();
		echo '<div class="slos-dsr-form-wrapper slos-compact">';
		echo '<div class="slos-dsr-form-container">';
		echo '<div class="slos-dsr-form-header">';
		echo '<h2>' . esc_html__( 'Verify Your Request', 'shahi-legalflowsuite' ) . '</h2>';
		echo '</div>';

		if ( empty( $token ) ) {
			$this->render_error( __( 'Missing verification token. Please use the link from your email.', 'shahi-legalflowsuite' ) );
			echo '</div></div>';
			return ob_get_clean();
		}

		// Call public REST endpoint: GET /slos/v1/dsr/verify?token=.....
		$url      = add_query_arg( array( 'token' => rawurlencode( $token ) ), rest_url( 'slos/v1/dsr/verify' ) );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			$this->render_error( __( 'Network error verifying token. Please try again later.', 'shahi-legalflowsuite' ) );
			echo '</div></div>';
			return ob_get_clean();
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$json = json_decode( (string) $body, true );

		if ( 200 !== $code || ! is_array( $json ) ) {
			$this->render_error( __( 'Invalid or expired verification token.', 'shahi-legalflowsuite' ) );
			echo '</div></div>';
			return ob_get_clean();
		}

		// Success...
		$this->render_success(
			__( 'Email verified successfully. Your request is now being processed.', 'shahi-legalflowsuite' ),
			isset( $json['request_id'] ) ? (int) $json['request_id'] : 0
		);

		echo '</div></div>';
		return ob_get_clean();
	}

	/**
	 * Render error message block
	 *
	 * @since 3.0.1
	 * @param string $message Error message.
	 * @return void
	 */
	private function render_error( string $message ): void {
		echo '<div class="slos-dsr-error" role="alert" aria-live="assertive">';
		echo '<div class="slos-dsr-error-icon" aria-hidden="true">!\u{FE0E}</div>';
		echo '<div class="slos-dsr-error-content">';
		echo '<h3>' . esc_html__( 'Verification Failed', 'shahi-legalflowsuite' ) . '</h3>';
		echo '<p>' . esc_html( $message ) . '</p>';
		echo '</div></div>';
	}

	/**
	 * Render success message block
	 *
	 * @since 3.0.1
	 * @param string $message Success message.
	 * @param int    $request_id Request ID (optional).
	 * @return void
	 */
	private function render_success( string $message, int $request_id = 0 ): void {
		echo '<div class="slos-dsr-success" role="status" aria-live="polite">';
		echo '<div class="slos-dsr-success-icon" aria-hidden="true">✓\u{FE0E}</div>';
		echo '<div class="slos-dsr-success-content">';
		echo '<h3>' . esc_html__( 'Verification Successful', 'shahi-legalflowsuite' ) . '</h3>';
		echo '<p>' . esc_html( $message ) . '</p>';
		if ( $request_id > 0 ) {
			/* translators: %d: data subject request ID number */
			echo '<p>' . esc_html( sprintf( __( 'Your request ID: %d', 'shahi-legalflowsuite' ), $request_id ) ) . '</p>';
		}
		echo '</div></div>';
	}
}
