<?php
/**
 * Consent Preferences Shortcode
 *
 * Renders user-facing consent preferences interface where visitors can:
 * - View current consent choices
 * - Update consent preferences
 * - View consent history
 * - Download consent data (GDPR Article 15)
 *
 * Shortcode: [slos_consent_preferences]
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Shortcodes
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Shortcodes;

use ShahiLegalFlowSuite\Core\I18n;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Preferences_Shortcode Class
 *
 * Handles [slos_consent_preferences] shortcode rendering and asset management.
 *
 * @since 3.0.1
 */
class Consent_Preferences_Shortcode {

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
		add_shortcode( 'slos_consent_preferences', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode output
	 *
	 * Enqueues necessary assets and returns container div for JS app.
	 *
	 * @since 3.0.1
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output
	 */
	public function render( $atts = array() ) {
		// Load plugin settings for defaults...
		$settings_defaults = array(
			'preferences_show_history'  => true,
			'preferences_show_download' => true,
		);
		if ( class_exists( '\\ShahiLegalFlowSuite\\Admin\\Settings' ) ) {
			$settings_instance                              = new \ShahiLegalFlowSuite\Admin\Settings();
			$plugin_settings                                = method_exists( $settings_instance, 'get_settings' ) ? $settings_instance->get_settings() : array();
			$settings_defaults['preferences_show_history']  = ! empty( $plugin_settings['preferences_show_history'] );
			$settings_defaults['preferences_show_download'] = ! empty( $plugin_settings['preferences_show_download'] );
		}

		// Parse attributes with settings-aware defaults...
		$atts = shortcode_atts(
			array(
				'show_history'  => $settings_defaults['preferences_show_history'] ? 'true' : 'false',
				'show_download' => $settings_defaults['preferences_show_download'] ? 'true' : 'false',
				'theme'         => 'light',
				'compact'       => 'false',
			),
			$atts,
			'slos_consent_preferences'
		);

		// Enqueue assets...
		$this->enqueue_assets( $atts );

		// Get current user...
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			// Use session ID for non-logged-in users...
			if ( ! session_id() ) {
				session_start();
			}
			$session_id = session_id();
		}

		// Build container classes...
		$classes = array( 'slos-consent-preferences-wrapper' );
		if ( 'dark' === $atts['theme'] ) {
			$classes[] = 'slos-theme-dark';
		}
		if ( 'true' === $atts['compact'] ) {
			$classes[] = 'slos-compact';
		}

		// Build data attributes for JS app...
		$data_attrs = array(
			'data-user-id'       => $user_id ? $user_id : 0,
			'data-session-id'    => $user_id ? '' : ( $session_id ?? '' ),
			'data-show-history'  => $atts['show_history'],
			'data-show-download' => $atts['show_download'],
		);

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" <?php echo $this->render_data_attributes( $data_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div id="slos-consent-preferences"></div>
			<div class="slos-consent-loading">
				<span class="slos-spinner"></span>
				<p><?php esc_html_e( 'Loading your privacy preferences...', 'shahi-legalflowsuite' ); ?></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue shortcode assets
	 *
	 * @since 3.0.1
	 * @param array $atts Shortcode attributes.
	 * @return void
	 */
	private function enqueue_assets( $atts ) {
		// Avoid unused parameter warning...
		unset( $atts );
		$version = defined( 'SLOS_VERSION' ) ? SLOS_VERSION : '3.0.1';

		// Enqueue scripts...
		wp_enqueue_script(
			'slos-consent-preferences',
			plugins_url( 'assets/js/consent-preferences.js', dirname( __DIR__ ) ),
			array(),
			$version,
			true
		);

		// Enqueue styles...
		wp_enqueue_style(
			'slos-consent-preferences',
			plugins_url( 'assets/css/consent-preferences.css', dirname( __DIR__ ) ),
			array(),
			$version
		);

		// Enqueue RTL styles if needed...
		if ( is_rtl() ) {
			wp_enqueue_style(
				'slos-consent-preferences-rtl',
				plugins_url( 'assets/css/consent-preferences-rtl.css', dirname( __DIR__ ) ),
				array( 'slos-consent-preferences' ),
				$version
			);
		}

		// Localize script...
		$this->localize_script( $atts );
	}

	/**
	 * Localize script with translations and config
	 *
	 * @since 3.0.1
	 * @param array $atts Shortcode attributes.
	 * @return void
	 */
	private function localize_script( $atts ) {
		$user_id = get_current_user_id();

		wp_localize_script(
			'slos-consent-preferences',
			'slosConsentPrefs',
			array(
				'apiUrl'     => rest_url( 'slos/v1' ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'userId'     => $user_id,
				'sessionId'  => $user_id ? '' : ( session_id() ?? '' ),
				'isLoggedIn' => $user_id > 0,
				'i18n'       => $this->get_translations(),
				'config'     => array(
					'showHistory'      => 'true' === $atts['show_history'],
					'showDownload'     => 'true' === $atts['show_download'],
					'enableAnimations' => true,
				),
			)
		);
	}

	/**
	 * Get translation strings
	 *
	 * @since 3.0.1
	 * @return array Translation strings.
	 */
	private function get_translations() {
		return array(
			// Headers...
			'privacyChoices'      => __( 'Your Privacy Choices', 'shahi-legalflowsuite' ),
			'manageConsent'       => __( 'Manage your consent preferences below.', 'shahi-legalflowsuite' ),
			'consentHistory'      => __( 'Consent History', 'shahi-legalflowsuite' ),

			// Purpose labels...
			'functional'          => __( 'Functional', 'shahi-legalflowsuite' ),
			'analytics'           => __( 'Analytics', 'shahi-legalflowsuite' ),
			'marketing'           => __( 'Marketing', 'shahi-legalflowsuite' ),
			'advertising'         => __( 'Advertising', 'shahi-legalflowsuite' ),
			'personalization'     => __( 'Personalization', 'shahi-legalflowsuite' ),
			'necessary'           => __( 'Necessary', 'shahi-legalflowsuite' ),

			// Purpose descriptions...
			'functionalDesc'      => __( 'Required for site operation', 'shahi-legalflowsuite' ),
			'analyticsDesc'       => __( 'Helps us improve the site', 'shahi-legalflowsuite' ),
			'marketingDesc'       => __( 'Used for marketing communications', 'shahi-legalflowsuite' ),
			'advertisingDesc'     => __( 'Used for personalized ads', 'shahi-legalflowsuite' ),
			'personalizationDesc' => __( 'Remembers your preferences', 'shahi-legalflowsuite' ),
			'necessaryDesc'       => __( 'Essential cookies for basic functionality', 'shahi-legalflowsuite' ),

			// Status labels...
			'required'            => __( 'Required', 'shahi-legalflowsuite' ),
			'enabled'             => __( 'Enabled', 'shahi-legalflowsuite' ),
			'disabled'            => __( 'Disabled', 'shahi-legalflowsuite' ),

			// Actions...
			'savePreferences'     => __( 'Save Preferences', 'shahi-legalflowsuite' ),
			'downloadData'        => __( 'Download My Data', 'shahi-legalflowsuite' ),
			'viewHistory'         => __( 'View History', 'shahi-legalflowsuite' ),
			'hideHistory'         => __( 'Hide History', 'shahi-legalflowsuite' ),
			'acceptAll'           => __( 'Accept All', 'shahi-legalflowsuite' ),
			'rejectAll'           => __( 'Reject All', 'shahi-legalflowsuite' ),

			// Messages...
			'loading'             => __( 'Loading your privacy preferences...', 'shahi-legalflowsuite' ),
			'saving'              => __( 'Saving preferences...', 'shahi-legalflowsuite' ),
			'saved'               => __( 'Preferences saved successfully!', 'shahi-legalflowsuite' ),
			'downloadReady'       => __( 'Your data is ready for download.', 'shahi-legalflowsuite' ),
			'noHistory'           => __( 'No consent history found.', 'shahi-legalflowsuite' ),
			'loginRequired'       => __( 'Please log in to view your consent history.', 'shahi-legalflowsuite' ),

			// Errors...
			'errorLoading'        => __( 'Failed to load preferences. Please try again.', 'shahi-legalflowsuite' ),
			'errorSaving'         => __( 'Failed to save preferences. Please try again.', 'shahi-legalflowsuite' ),
			'errorDownload'       => __( 'Failed to download data. Please try again.', 'shahi-legalflowsuite' ),
			'errorNetwork'        => __( 'Network error. Please check your connection.', 'shahi-legalflowsuite' ),

			// History...
			'historyGranted'      => __( 'Granted', 'shahi-legalflowsuite' ),
			'historyWithdrawn'    => __( 'Withdrawn', 'shahi-legalflowsuite' ),
			'historyUpdated'      => __( 'Updated', 'shahi-legalflowsuite' ),
			'historyDate'         => __( 'Date', 'shahi-legalflowsuite' ),
			'historyAction'       => __( 'Action', 'shahi-legalflowsuite' ),
			'historyPurpose'      => __( 'Purpose', 'shahi-legalflowsuite' ),

			// GDPR...
			'gdprNotice'          => __( 'You have the right to access, modify, and delete your personal data under GDPR.', 'shahi-legalflowsuite' ),
			'dataPortability'     => __( 'Download your consent data in machine-readable format.', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Render data attributes
	 *
	 * @since 3.0.1
	 * @param array $data_attrs Data attributes array.
	 * @return string Rendered attributes.
	 */
	private function render_data_attributes( $data_attrs ) {
		$output = '';
		foreach ( $data_attrs as $key => $value ) {
			if ( '' !== $value ) {
				$output .= sprintf( '%s="%s" ', esc_attr( $key ), esc_attr( $value ) );
			}
		}
		return trim( $output );
	}
}

