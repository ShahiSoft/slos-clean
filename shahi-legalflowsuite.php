<?php
/**
 * Plugin Name: Shahi LegalFlowSuite
 * Plugin URI: https://shahisoft.com/shahi-legalflowsuite
 * Description: Professional legal operations and compliance management toolkit for WordPress. Manage GDPR/CCPA compliance, generate legal documents, handle data subject requests, and scan for accessibility issues.
 * Version: 3.3.0
 * Author: ShahiSoft
 * Author URI: https://shahisoft.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: shahi-legalflowsuite
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 7.4
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Plugin Constants
 */
define('SHAHI_LEGALFLOWSUITE_VERSION', '3.3.0');
define('SHAHI_LEGALFLOWSUITE_PATH', plugin_dir_path(__FILE__));
define('SHAHI_LEGALFLOWSUITE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHAHI_LEGALFLOWSUITE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SHAHI_LEGALFLOWSUITE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SHAHI_LEGALFLOWSUITE_PLUGIN_FILE', __FILE__);

/**
 * Document Generator Constants
 */
if (!defined('SLOS_TEMPLATE_DIR')) {
	define('SLOS_TEMPLATE_DIR', plugin_dir_path(__FILE__) . 'templates/legaldocs/');
}

if (!defined('SLOS_GEN_VERSION')) {
	define('SLOS_GEN_VERSION', '1.0.0');
}

/**
 * Stage 1 - Foundation Hub Constants
 * Feature flags and configuration for Document Generator MVP
 */
require_once plugin_dir_path(__FILE__) . 'config/stage-1-constants.php';

/**
 * PSR-4 Autoloader
 */
require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Core/Autoloader.php';
ShahiLegalFlowSuite\Core\Autoloader::register();

// Load translations at init hook to avoid just-in-time load notices
add_action(
	'init',
	function () {
		load_plugin_textdomain(
			'shahi-legalflowsuite',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages/'
		);
	},
	1
);

/**
 * Plugin Activation Hook
 */
function activate_shahi_template()
{
	ShahiLegalFlowSuite\Core\Activator::activate();
}
register_activation_hook(__FILE__, 'activate_shahi_template');

/**
 * Plugin Deactivation Hook
 */
function deactivate_shahi_template()
{
	ShahiLegalFlowSuite\Core\Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_shahi_template');

/**
 * Initialize the plugin
 */
function run_shahi_template()
{
	$plugin = new ShahiLegalFlowSuite\Core\Plugin();
	$plugin->run();
}
// Wait for plugins_loaded at priority 10 to ensure WordPress is fully initialized
add_action('plugins_loaded', 'run_shahi_template', 10);

/**
 * Enqueue Consent Banner Scripts and Styles
 */
function enqueue_slos_consent_banner()
{
	// Get current user ID
	$user_id = get_current_user_id();

	// Geolocation: detect region and suggest template
	$geo_service = new ShahiLegalFlowSuite\Services\Geo_Service();
	$rule_matcher = new ShahiLegalFlowSuite\Services\Geo_Rule_Matcher();
	$geo = array();

	// Respect admin settings
	$settings = get_option('shahi_legalflowsuite_settings', array());
	$enabled_geo = isset($settings['enable_geolocation_detection']) ? (bool) $settings['enable_geolocation_detection'] : true;
	$override = isset($settings['geolocation_override_region']) ? (string) $settings['geolocation_override_region'] : '';

	// Initialize geo data and matching rule
	$matching_rule = null;
	$country_code = '';
	$state_code = '';

	try {
		if (!empty($override)) {
			$geo = array('region' => strtoupper($override), 'source' => 'override');
		} elseif ($enabled_geo) {
			$geo = $geo_service->get_region_for_request();
			$country_code = $geo['country_code'] ?? '';
			$state_code = $geo['state'] ?? '';

			// Find matching geo rule for this visitor
			if (!empty($country_code)) {
				$matching_rule = $rule_matcher->find_matching_rule($country_code, $state_code);
			}
		} else {
			$geo = array('region' => 'GLOBAL', 'source' => 'disabled');
		}
	} catch (\Throwable $e) {
		$geo = array('region' => 'GLOBAL', 'source' => 'error');
	}

	$detected_region = isset($geo['region']) ? $geo['region'] : 'GLOBAL';

	// Get banner config from matching rule, or fall back to region-based template
	if ($matching_rule) {
		$banner_config = $rule_matcher->get_banner_config_from_rule($matching_rule);
		$suggested_tpl = $banner_config['template'];
		$consent_mode = $banner_config['consent_mode'];
		$geo_rule_id = $matching_rule['id'] ?? null;
	} else {
		$suggested_tpl = $geo_service->map_region_to_template($detected_region);
		$consent_mode = 'opt-in';
		$geo_rule_id = null;
	}

	// Enqueue consent banner stylesheet
	wp_enqueue_style(
		'slos-consent-banner',
		SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/consent-banner.css',
		array(),
		SHAHI_LEGALFLOWSUITE_VERSION,
		'all'
	);

	// Enqueue consent banner script
	wp_enqueue_script(
		'slos-consent-banner',
		SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/consent-banner.js',
		array(),
		SHAHI_LEGALFLOWSUITE_VERSION,
		true
	);

	// Localize banner configuration
	wp_localize_script(
		'slos-consent-banner',
		'slosConsentConfig',
		array(
			'apiUrl' => rest_url('slos/v1/consents'),
			'routes' => array(
				'geo' => rest_url('slos/v1/geo/region'),
			),
			'userId' => $user_id,
			'region' => $detected_region,
			'countryCode' => $country_code,
			'geoRuleId' => $geo_rule_id,
			'consentMode' => apply_filters('slos_consent_mode', $consent_mode), // Options: 'opt-in', 'opt-out', 'notice'
			'template' => apply_filters('slos_consent_template', $suggested_tpl), // Options: 'eu', 'ccpa', 'simple', 'advanced'
			'position' => apply_filters('slos_consent_position', 'bottom'), // Options: 'top', 'bottom'
			'theme' => apply_filters('slos_consent_theme', 'light'), // Options: 'light', 'dark'
			'reloadOnConsent' => apply_filters('slos_reload_on_consent', false),
			'privacyLink' => apply_filters('slos_privacy_policy_url', home_url('/privacy-policy')),
			'matchingRule' => $matching_rule ? array(
				'id' => $matching_rule['id'] ?? null,
				'name' => $matching_rule['name'] ?? '',
				'framework' => $matching_rule['framework'] ?? '',
			) : null,
		)
	);

	// Localize translations
	wp_localize_script(
		'slos-consent-banner',
		'slosConsentI18n',
		array(
			// Banner titles
			'euTitle' => __('Your Consent Preferences', 'shahi-legalflowsuite'),
			'euMessage' => __('We use cookies and similar tracking technologies to enhance your experience. Please select your preferences below.', 'shahi-legalflowsuite'),
			'ccpaMessage' => __('We respect your privacy. You have the right to opt-out of the sale or sharing of your personal information.', 'shahi-legalflowsuite'),
			'simpleMessage' => __('We use cookies to enhance your experience. Do you accept?', 'shahi-legalflowsuite'),

			// Buttons
			'acceptAll' => __('Accept All', 'shahi-legalflowsuite'),
			'acceptSelected' => __('Accept Selected', 'shahi-legalflowsuite'),
			'rejectAll' => __('Reject All', 'shahi-legalflowsuite'),
			'reject' => __('Reject', 'shahi-legalflowsuite'),
			'accept' => __('Accept', 'shahi-legalflowsuite'),
			'decline' => __('Decline', 'shahi-legalflowsuite'),
			'doNotSell' => __('Do Not Sell My Info', 'shahi-legalflowsuite'),
			'save' => __('Save Preferences', 'shahi-legalflowsuite'),

			// Links
			'privacyPolicy' => __('Privacy Policy', 'shahi-legalflowsuite'),
			'learnMore' => __('Learn More', 'shahi-legalflowsuite'),

			// Purpose labels
			'purposeNecessary' => __('Necessary Cookies', 'shahi-legalflowsuite'),
			'purposeFunctional' => __('Functional Cookies', 'shahi-legalflowsuite'),
			'purposeAnalytics' => __('Analytics', 'shahi-legalflowsuite'),
			'purposeMarketing' => __('Marketing & Advertising', 'shahi-legalflowsuite'),
			'purposePreferences' => __('Preference Management', 'shahi-legalflowsuite'),
			'purposePersonalization' => __('Personalization', 'shahi-legalflowsuite'),

			// Purpose descriptions
			'descNecessary' => __('Essential for basic functionality and security.', 'shahi-legalflowsuite'),
			'descFunctional' => __('Enables enhanced features and user experience.', 'shahi-legalflowsuite'),
			'descAnalytics' => __('Helps us understand how you use our site.', 'shahi-legalflowsuite'),
			'descMarketing' => __('Allows us to show relevant content and offers.', 'shahi-legalflowsuite'),
			'descPreferences' => __('Remembers your user preferences.', 'shahi-legalflowsuite'),
			'descPersonalization' => __('Personalizes content for better experience.', 'shahi-legalflowsuite'),

			// Other
			'required' => __('Required', 'shahi-legalflowsuite'),
			'bannerTitle' => __('Manage Consent', 'shahi-legalflowsuite'),
		)
	);
}
add_action('wp_enqueue_scripts', 'enqueue_slos_consent_banner', 100);

/**
 * Enqueue Cookie Scanner Script
 */
function enqueue_slos_cookie_scanner()
{
	wp_enqueue_script(
		'slos-cookie-scanner',
		SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/cookie-scanner.js',
		array(),
		SHAHI_LEGALFLOWSUITE_VERSION,
		true
	);
}
add_action('wp_enqueue_scripts', 'enqueue_slos_cookie_scanner', 110);

/**
 * Initialize Script Blocker
 */
function init_slos_script_blocker()
{
	try {
		$blocker = new ShahiLegalFlowSuite\Services\Script_Blocker_Service();
		$blocker->init();
	} catch (\Throwable $e) {
		// Fail-safe: do not break site if blocker fails
		error_log('SLOS Script Blocker init error: ' . $e->getMessage());
	}
}
add_action('init', 'init_slos_script_blocker', 0);
