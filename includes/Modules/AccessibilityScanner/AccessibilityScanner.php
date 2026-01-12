<?php
/**
 * Accessibility Scanner Module
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use ShahiLegalFlowSuite\Modules\Module;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\BackupService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingAltTextCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyAltTextCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingH1Check;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\SkippedHeadingLevelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyLinkCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\GenericLinkTextCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingFormLabelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\RedundantAltTextCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyHeadingCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\NewWindowLinkCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\PositiveTabIndexCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ImageMapAltCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\IframeTitleCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ButtonLabelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TableHeaderCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AltTextQualityCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\DecorativeImageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ComplexImageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\SvgAccessibilityCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\BackgroundImageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LogoImageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MultipleH1Check;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingVisualCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingLengthCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingUniquenessCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingNestingCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\FieldsetLegendCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AutocompleteCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\InputTypeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\PlaceholderLabelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\CustomControlCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\OrphanedLabelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\RequiredAttributeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ErrorMessageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\FormAriaCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LinkDestinationCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\SkipLinkCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\DownloadLinkCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ExternalLinkCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TextColorContrastCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\FocusIndicatorCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ColorRelianceCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ComplexContrastCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\KeyboardTrapCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\FocusOrderCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\InteractiveElementCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ModalAccessibilityCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\CustomWidgetKeyboardCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaRoleCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaAttributeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LandmarkRoleCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\RedundantAriaCheck; // Note: RedundantAltTextCheck exists, this is RedundantAriaCheck
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\HiddenContentCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\SemanticHtmlCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LiveRegionCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaStateCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\InvalidAriaCombinationCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\PageStructureCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\VideoAccessibilityCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AudioAccessibilityCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MediaAlternativeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TableCaptionCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ComplexTableCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LayoutTableCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyTableCellCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ViewportCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TouchTargetCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TouchGestureCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LanguageChangeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AnimationPauseCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TimingControlCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\StatusMessageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ErrorIdentificationCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Widget\AccessibilityWidget;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\ScannerPage;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilityDashboard;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\AltTextGenerator;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\AccessibilityFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting\AccessibilityReporter;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Compliance\AccessibilityStatementGenerator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Scanner Module Class
 *
 * @since 1.0.0
 */
class AccessibilityScanner extends Module {

	/**
	 * Scanner Engine Instance
	 *
	 * @var ScannerEngine
	 */
	private $scanner;

	/**
	 * Backup Service Instance
	 *
	 * @var BackupService
	 */
	private $backup_service;

	/**
	 * Get module unique key
	 *
	 * @since 1.0.0
	 * @return string Module key
	 */
	public function get_key() {
		return 'accessibility-scanner';
	}

	/**
	 * Get module name
	 *
	 * @since 1.0.0
	 * @return string Module name
	 */
	public function get_name() {
		return 'Accessibility Scanner Pro';
	}

	/**
	 * Get module description
	 *
	 * @since 1.0.0
	 * @return string Module description
	 */
	public function get_description() {
		return 'Automated accessibility scanning engine with real-time checks and compliance reporting.';
	}

	/**
	 * Get module icon
	 *
	 * @since 1.0.0
	 * @return string Icon class
	 */
	public function get_icon() {
		return 'dashicons-universal-access';
	}

	/**
	 * Get module category
	 *
	 * @since 1.0.0
	 * @return string Category
	 */
	public function get_category() {
		return 'compliance';
	}

	/**
	 * Get module settings URL
	 *
	 * Returns the admin URL for Accessibility Scanner settings page.
	 *
	 * @since 3.1.1
	 * @return string Settings URL
	 */
	public function get_settings_url() {
		return admin_url( 'admin.php?page=slos-accessibility-settings' );
	}

	/**
	 * Initialize module
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->scanner        = new ScannerEngine();
		$this->backup_service = new BackupService();
		$this->register_checks();

		// Initialize Widget
		$widget = new AccessibilityWidget();
		$widget->init();

		// Initialize Fixer
		$fixer = new AccessibilityFixer();

		// Initialize Reporter
		$reporter = new AccessibilityReporter();

		// Initialize Admin Pages
		if ( is_admin() ) {
			// Note: ScannerPage, Dashboard, and Settings no longer register menus individually
			// All registration happens via register_admin_menus() method below

			$settings = new AccessibilitySettings();
			$settings->init();

			// Register menus with priority 20 to ensure parent menu exists first
			add_action( 'admin_menu', array( $this, 'register_admin_menus' ), 20 );
		}

		add_action( 'save_post', array( $this, 'run_scan_on_save' ), 10, 3 );
		add_action( 'add_meta_boxes', array( $this, 'add_scan_meta_box' ) );

		// AJAX Handlers
		add_action( 'wp_ajax_slos_get_posts_to_scan', array( $this, 'ajax_get_posts_to_scan' ) );
		add_action( 'wp_ajax_slos_scan_single_post', array( $this, 'ajax_scan_single_post' ) );
		add_action( 'wp_ajax_slos_generate_alt_text', array( $this, 'ajax_generate_alt_text' ) );
		add_action( 'wp_ajax_slos_generate_statement', array( $this, 'ajax_generate_statement' ) );

		// Autofix AJAX handlers - only register if autofix is not dormant
		if ( ! ( defined( 'SLOS_DORMANT_AUTOFIX' ) && SLOS_DORMANT_AUTOFIX ) ) {
			add_action( 'wp_ajax_slos_fix_single_issue', array( $this, 'ajax_fix_single_issue' ) );
			add_action( 'wp_ajax_slos_fix_all_issues', array( $this, 'ajax_fix_all_issues' ) );
			add_action( 'wp_ajax_slos_toggle_autofix', array( $this, 'ajax_toggle_autofix' ) );
		}
		add_action( 'wp_ajax_slos_get_page_issues', array( $this, 'ajax_get_page_issues' ) );
		add_action( 'wp_ajax_slos_get_page_fixable_issues', array( $this, 'ajax_get_page_fixable_issues' ) );
		add_action( 'wp_ajax_slos_run_full_scan', array( $this, 'ajax_run_full_scan' ) );
		add_action( 'wp_ajax_slos_consolidate_scan_results', array( $this, 'ajax_consolidate_scan_results' ) );
		add_action( 'wp_ajax_slos_audit_media_library', array( $this, 'ajax_audit_media_library' ) );
		add_action( 'wp_ajax_slos_publish_statement', array( $this, 'ajax_publish_statement' ) );

		// Autofix and rollback handlers - only register if autofix is not dormant
		if ( ! ( defined( 'SLOS_DORMANT_AUTOFIX' ) && SLOS_DORMANT_AUTOFIX ) ) {
			add_action( 'wp_ajax_slos_autofix_single', array( $this, 'ajax_autofix_single_fixer' ) );
			add_action( 'wp_ajax_slos_rollback_fixes', array( $this, 'ajax_rollback_fixes' ) );
			add_action( 'wp_ajax_slos_check_backup_exists', array( $this, 'ajax_check_backup_exists' ) );
		}

		add_action( 'wp_ajax_slos_get_detailed_scan_report', array( $this, 'ajax_get_detailed_scan_report' ) );
		add_action( 'wp_ajax_slos_save_scanner_config', array( $this, 'ajax_save_scanner_config' ) );
		add_action( 'wp_ajax_slos_schedule_email_report', array( $this, 'ajax_schedule_email_report' ) );
		add_action( 'wp_ajax_slos_toggle_widget', array( $this, 'ajax_toggle_widget' ) );
		add_action( 'wp_ajax_slos_save_widget_config', array( $this, 'ajax_save_widget_config' ) );
		add_action( 'wp_ajax_slos_check_color_contrast', array( $this, 'ajax_check_color_contrast' ) );
		add_action( 'wp_ajax_slos_check_readability', array( $this, 'ajax_check_readability' ) );
		add_action( 'wp_ajax_slos_check_link_text', array( $this, 'ajax_check_link_text' ) );

		// Schedule cleanup cron job if not already scheduled
		if ( ! wp_next_scheduled( 'slos_cleanup_old_backups' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_cleanup_old_backups' );
		}
		add_action( 'slos_cleanup_old_backups', array( $this, 'cron_cleanup_old_backups' ) );

		// Schedule periodic accessibility email reports (daily driver, frequency handled in callback)
		if ( ! wp_next_scheduled( 'slos_send_accessibility_report' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_send_accessibility_report' );
		}
		add_action( 'slos_send_accessibility_report', array( $this, 'cron_send_accessibility_report' ) );
	}

	/**
	 * Check if user has permission to manage accessibility
	 *
	 * @return bool
	 */
	public function user_can_manage_accessibility() {
		// Default to administrator, but allow filtering for custom roles
		$capability = apply_filters( 'slos_accessibility_capability', 'manage_options' );
		return current_user_can( $capability );
	}

	/**
	 * AJAX: Generate Accessibility Statement (preview mode)
	 */
	public function ajax_generate_statement() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Get form data
		$org_name       = sanitize_text_field( wp_unslash( $_POST['org_name'] ?? '' ) );
		$contact_email  = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
		$wcag_target    = sanitize_text_field( wp_unslash( $_POST['wcag_target'] ?? 'WCAG 2.1 Level AA' ) );
		$statement_date = sanitize_text_field( wp_unslash( $_POST['statement_date'] ?? '' ) );
		$commitment     = sanitize_textarea_field( wp_unslash( $_POST['commitment'] ?? '' ) );

		if ( empty( $org_name ) ) {
			wp_send_json_error( __( 'Organization name is required.', 'shahi-legalflowsuite' ) );
		}

		// Build statement HTML (preview)
		$statement_html = $this->build_accessibility_statement_preview(
			$org_name,
			$contact_email,
			$wcag_target,
			$statement_date,
			$commitment
		);

		// Build raw statement (for copying)
		$statement_raw = $this->build_accessibility_statement_raw(
			$org_name,
			$contact_email,
			$wcag_target,
			$statement_date,
			$commitment
		);

		wp_send_json_success(
			array(
				'statement'     => $statement_html,
				'statement_raw' => $statement_raw,
			)
		);
	}

	/**
	 * Build accessibility statement HTML for preview
	 *
	 * @param string $org_name Organization name.
	 * @param string $contact_email Contact email.
	 * @param string $wcag_target WCAG target level.
	 * @param string $statement_date Statement date.
	 * @param string $commitment Additional commitment text.
	 * @return string HTML statement for preview.
	 */
	private function build_accessibility_statement_preview( $org_name, $contact_email, $wcag_target, $statement_date, $commitment ) {
		$date_formatted = ! empty( $statement_date ) ? gmdate( 'F j, Y', strtotime( $statement_date ) ) : gmdate( 'F j, Y' );

		$html = '<h3>' . __( 'Accessibility Statement', 'shahi-legalflowsuite' ) . '</h3>';

		$html .= '<p>' . sprintf(
			/* translators: %s: Organization name */
			__( '%s is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.', 'shahi-legalflowsuite' ),
			esc_html( $org_name )
		) . '</p>';

		$html .= '<h4>' . __( 'Conformance Status', 'shahi-legalflowsuite' ) . '</h4>';
		$html .= '<p>' . sprintf(
			/* translators: %s: WCAG target level */
			__( 'We strive to conform to %s of the Web Content Accessibility Guidelines (WCAG).', 'shahi-legalflowsuite' ),
			esc_html( $wcag_target )
		) . '</p>';

		$html .= '<h4>' . __( 'Measures to Support Accessibility', 'shahi-legalflowsuite' ) . '</h4>';
		$html .= '<p>' . esc_html( $org_name ) . ' ' . __( 'takes the following measures:', 'shahi-legalflowsuite' ) . '</p>';
		$html .= '<ul>';
		$html .= '<li>' . __( 'Include accessibility in our mission statement', 'shahi-legalflowsuite' ) . '</li>';
		$html .= '<li>' . __( 'Integrate accessibility into procurement', 'shahi-legalflowsuite' ) . '</li>';
		$html .= '<li>' . __( 'Provide accessibility training for staff', 'shahi-legalflowsuite' ) . '</li>';
		$html .= '<li>' . __( 'Employ accessibility quality assurance methods', 'shahi-legalflowsuite' ) . '</li>';
		$html .= '</ul>';

		if ( ! empty( $commitment ) ) {
			$html .= '<h4>' . __( 'Our Commitment', 'shahi-legalflowsuite' ) . '</h4>';
			$html .= '<p>' . esc_html( $commitment ) . '</p>';
		}

		$html .= '<h4>' . __( 'Feedback', 'shahi-legalflowsuite' ) . '</h4>';
		$html .= '<p>' . __( 'We welcome your feedback on the accessibility of this website.', 'shahi-legalflowsuite' ) . '</p>';

		if ( ! empty( $contact_email ) ) {
			$html .= '<p>' . __( 'Email:', 'shahi-legalflowsuite' ) . ' <a href="mailto:' . esc_attr( $contact_email ) . '">' . esc_html( $contact_email ) . '</a></p>';
		}

		$html .= '<p><small>' . sprintf(
			/* translators: %s: Statement date */
			__( 'Last updated: %s', 'shahi-legalflowsuite' ),
			esc_html( $date_formatted )
		) . '</small></p>';

		return $html;
	}

	/**
	 * Build accessibility statement as plain text for copying
	 *
	 * @param string $org_name Organization name.
	 * @param string $contact_email Contact email.
	 * @param string $wcag_target WCAG target level.
	 * @param string $statement_date Statement date.
	 * @param string $commitment Additional commitment text.
	 * @return string Plain text statement.
	 */
	private function build_accessibility_statement_raw( $org_name, $contact_email, $wcag_target, $statement_date, $commitment ) {
		$date_formatted = ! empty( $statement_date ) ? gmdate( 'F j, Y', strtotime( $statement_date ) ) : gmdate( 'F j, Y' );

		$text  = "ACCESSIBILITY STATEMENT\n\n";
		$text .= $org_name . ' is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.' . "\n\n";
		$text .= "CONFORMANCE STATUS\n";
		$text .= 'We strive to conform to ' . $wcag_target . ' of the Web Content Accessibility Guidelines (WCAG).' . "\n\n";
		$text .= "MEASURES TO SUPPORT ACCESSIBILITY\n";
		$text .= $org_name . " takes the following measures:\n";
		$text .= "- Include accessibility in our mission statement\n";
		$text .= "- Integrate accessibility into procurement\n";
		$text .= "- Provide accessibility training for staff\n";
		$text .= "- Employ accessibility quality assurance methods\n\n";

		if ( ! empty( $commitment ) ) {
			$text .= "OUR COMMITMENT\n";
			$text .= $commitment . "\n\n";
		}

		$text .= "FEEDBACK\n";
		$text .= "We welcome your feedback on the accessibility of this website.\n";

		if ( ! empty( $contact_email ) ) {
			$text .= 'Email: ' . $contact_email . "\n";
		}

		$text .= "\nLast updated: " . $date_formatted;

		return $text;
	}

	/**
	 * AJAX: Generate Alt Text
	 */
	public function ajax_generate_alt_text() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$attachment_id = intval( $_POST['attachment_id'] );
		$generator     = new AltTextGenerator();
		$result        = $generator->generate_for_attachment( $attachment_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX: Get all posts to scan
	 */
	public function ajax_get_posts_to_scan() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Use lightweight query - only get IDs and titles, skip get_permalink (slow)
		// Allow optional limit for quick scan requests and prioritize recent content
		global $wpdb;
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 0;

		$sql = $limit > 0
			? $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- esc_sql() used for table name
				'SELECT ID, post_title FROM ' . esc_sql( $wpdb->posts ) . " WHERE post_type IN ('post', 'page') AND post_status = %s ORDER BY post_date DESC LIMIT %d",
				'publish',
				$limit
			)
		: $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- esc_sql() used for table name
			'SELECT ID, post_title FROM ' . esc_sql( $wpdb->posts ) . " WHERE post_type IN ('post', 'page') AND post_status = %s ORDER BY post_date DESC",
		);

		$results = $wpdb->get_results( $sql, ARRAY_A );

		$data = array();
		foreach ( $results as $row ) {
			$data[] = array(
				'id'    => (int) $row['ID'],
				'title' => $row['post_title'],
			);
		}

		wp_send_json_success( $data );
	}

	/**
	 * AJAX: Scan single post
	 */
	public function ajax_scan_single_post() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );
		$url     = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$post    = null;

		if ( $url && ! $post_id ) {
			$site_host   = wp_parse_url( home_url(), PHP_URL_HOST );
			$target_host = wp_parse_url( $url, PHP_URL_HOST );

			if ( $site_host && $target_host && $site_host !== $target_host ) {
				wp_send_json_error( __( 'Please scan URLs from this site only for security reasons.', 'shahi-legalflowsuite' ) );
			}

			$post_id = url_to_postid( $url );

			if ( $post_id ) {
				$post = get_post( $post_id );
			} else {
				$response = wp_remote_get( $url );
				if ( is_wp_error( $response ) ) {
					wp_send_json_error( $response->get_error_message() );
				}

				$body = wp_remote_retrieve_body( $response );

				if ( empty( $body ) ) {
					wp_send_json_error( __( 'Unable to retrieve the requested URL.', 'shahi-legalflowsuite' ) );
				}

				$scan_results   = $this->scanner->scan( $body );
				$issues_count   = 0;
				$critical_count = 0;
				$issue_types    = array();

				foreach ( $scan_results as $check ) {
					$check_issues = isset( $check['issues'] ) ? (array) $check['issues'] : array();
					$issue_count  = count( $check_issues );

					if ( $issue_count > 0 ) {
						$issues_count += $issue_count;
						$issue_types[] = $check['id'];

						if ( isset( $check['severity'] ) && 'critical' === $check['severity'] ) {
							$critical_count += $issue_count;
						}
					}
				}

				$score = $issues_count > 0 ? max( 0, 100 - ( $critical_count * 10 + ( $issues_count - $critical_count ) * 3 ) ) : 100;

				wp_send_json_success(
					array(
						'url'            => $url,
						'issues_count'   => $issues_count,
						'critical_count' => $critical_count,
						'issue_types'    => array_unique( $issue_types ),
						'score'          => $score,
						'scan_date'      => current_time( 'mysql' ),
					)
				);
			}
		}

		if ( $post_id && ! $post ) {
			$post = get_post( $post_id );
		}

		if ( ! $post ) {
			wp_send_json_error( 'Post not found' );
		}

		$result = $this->run_scan_for_post( $post_id, $post );

		if ( empty( $result['url'] ) ) {
			$result['url'] = get_permalink( $post_id );
		}

		// Note: Consolidation removed from here - should only run at end of full scan
		// Individual scans don't need to rebuild entire dashboard data

		wp_send_json_success( $result );
	}

	/**
	 * AJAX: Consolidate scan results (called after full scan completes)
	 */
	public function ajax_consolidate_scan_results() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$this->consolidate_scan_results();

		wp_send_json_success( array( 'message' => 'Results consolidated' ) );
	}

	/**
	 * AJAX: Audit Media Library for missing alt text
	 */
	public function ajax_audit_media_library() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'post_status'    => 'inherit',
			)
		);

		$total_images   = count( $images );
		$missing_alt    = 0;
		$with_alt       = 0;
		$missing_images = array();

		foreach ( $images as $image ) {
			$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );

			if ( empty( trim( $alt_text ) ) ) {
				++$missing_alt;
				$missing_images[] = array(
					'id'        => $image->ID,
					'title'     => $image->post_title ?: __( 'Untitled', 'shahi-legalflowsuite' ),
					'thumbnail' => wp_get_attachment_thumb_url( $image->ID ) ?: wp_get_attachment_url( $image->ID ),
					'edit_url'  => admin_url( 'post.php?post=' . $image->ID . '&action=edit' ),
				);
			} else {
				++$with_alt;
			}
		}

		// Sort missing images by title
		usort(
			$missing_images,
			function ( $a, $b ) {
				return strcasecmp( $a['title'], $b['title'] );
			}
		);

		wp_send_json_success(
			array(
				'total_images'   => $total_images,
				'missing_alt'    => $missing_alt,
				'with_alt'       => $with_alt,
				'missing_images' => $missing_images,
			)
		);
	}

	/**
	 * AJAX: Publish accessibility statement to a new page
	 */
	public function ajax_publish_statement() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Check if statement page already exists
		$existing = get_page_by_path( 'accessibility-statement' );

		// Check if pre-generated content was provided
		$statement_content = isset( $_POST['statement_content'] ) ? wp_kses_post( wp_unslash( $_POST['statement_content'] ) ) : '';

		// If no pre-generated content, generate from form data
		if ( empty( $statement_content ) ) {
			$org_name       = sanitize_text_field( wp_unslash( $_POST['org_name'] ?? '' ) );
			$contact_email  = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
			$wcag_target    = sanitize_text_field( wp_unslash( $_POST['wcag_target'] ?? 'WCAG 2.2 Level AA' ) );
			$statement_date = sanitize_text_field( wp_unslash( $_POST['statement_date'] ?? '' ) );
			$commitment     = sanitize_textarea_field( wp_unslash( $_POST['commitment'] ?? '' ) );

			if ( empty( $org_name ) ) {
				wp_send_json_error( __( 'Organization name is required or statement content must be provided.', 'shahi-legalflowsuite' ) );
			}

			// Build statement HTML
			$statement_content = $this->build_accessibility_statement(
				$org_name,
				$contact_email,
				$wcag_target,
				$statement_date,
				$commitment
			);
		}

		// If page exists, update it
		if ( $existing ) {
			$page_id = wp_update_post(
				array(
					'ID'           => $existing->ID,
					'post_content' => $statement_content,
					'post_author'  => get_current_user_id(),
				)
			);

			if ( is_wp_error( $page_id ) ) {
				wp_send_json_error( $page_id->get_error_message() );
			}

			wp_send_json_success(
				array(
					'page_id'   => $existing->ID,
					'view_link' => get_permalink( $existing->ID ),
					'edit_link' => get_edit_post_link( $existing->ID, 'raw' ),
					'message'   => __( 'Accessibility Statement page updated successfully.', 'shahi-legalflowsuite' ),
					'updated'   => true,
				)
			);
			return;
		}

		// Create new page
		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Accessibility Statement', 'shahi-legalflowsuite' ),
				'post_name'    => 'accessibility-statement',
				'post_content' => $statement_content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id(),
			)
		);

		if ( is_wp_error( $page_id ) ) {
			wp_send_json_error( $page_id->get_error_message() );
		}

		wp_send_json_success(
			array(
				'page_id'   => $page_id,
				'view_link' => get_permalink( $page_id ),
				'edit_link' => get_edit_post_link( $page_id, 'raw' ),
				'message'   => __( 'Accessibility Statement page created successfully.', 'shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Build accessibility statement HTML
	 *
	 * @param string $org_name Organization name.
	 * @param string $contact_email Contact email.
	 * @param string $wcag_target WCAG target level.
	 * @param string $statement_date Statement date.
	 * @param string $commitment Additional commitment text.
	 * @return string HTML statement.
	 */
	private function build_accessibility_statement( $org_name, $contact_email, $wcag_target, $statement_date, $commitment ) {
		$date_formatted = ! empty( $statement_date ) ? gmdate( 'F j, Y', strtotime( $statement_date ) ) : gmdate( 'F j, Y' );
		$site_url       = home_url();

		$html  = '<!-- wp:heading {"level":1} -->' . "\n";
		$html .= '<h1 class="wp-block-heading">' . __( 'Accessibility Statement', 'shahi-legalflowsuite' ) . '</h1>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . sprintf(
			/* translators: 1: Organization name, 2: Site URL */
			__( '%1$s is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards for %2$s.', 'shahi-legalflowsuite' ),
			esc_html( $org_name ),
			esc_url( $site_url )
		) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n\n";

		$html .= '<!-- wp:heading -->' . "\n";
		$html .= '<h2 class="wp-block-heading">' . __( 'Conformance Status', 'shahi-legalflowsuite' ) . '</h2>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . sprintf(
			/* translators: %s: WCAG target level */
			__( 'We strive to conform to %s of the Web Content Accessibility Guidelines (WCAG). These guidelines explain how to make web content more accessible for people with disabilities.', 'shahi-legalflowsuite' ),
			esc_html( $wcag_target )
		) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n\n";

		$html .= '<!-- wp:heading -->' . "\n";
		$html .= '<h2 class="wp-block-heading">' . __( 'Measures to Support Accessibility', 'shahi-legalflowsuite' ) . '</h2>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . esc_html( $org_name ) . ' ' . __( 'takes the following measures to ensure accessibility:', 'shahi-legalflowsuite' ) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n\n";

		$html .= '<!-- wp:list -->' . "\n";
		$html .= '<ul class="wp-block-list">' . "\n";
		$html .= '<li>' . __( 'Include accessibility as part of our mission statement.', 'shahi-legalflowsuite' ) . '</li>' . "\n";
		$html .= '<li>' . __( 'Integrate accessibility into our procurement practices.', 'shahi-legalflowsuite' ) . '</li>' . "\n";
		$html .= '<li>' . __( 'Provide continual accessibility training for our staff.', 'shahi-legalflowsuite' ) . '</li>' . "\n";
		$html .= '<li>' . __( 'Employ formal accessibility quality assurance methods.', 'shahi-legalflowsuite' ) . '</li>' . "\n";
		$html .= '</ul>' . "\n";
		$html .= '<!-- /wp:list -->' . "\n\n";

		$html .= '<!-- wp:heading -->' . "\n";
		$html .= '<h2 class="wp-block-heading">' . __( 'Compatibility with browsers and assistive technology', 'shahi-legalflowsuite' ) . '</h2>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . __( 'Our goal is to support the latest versions of major browsers and assistive technologies, including Chrome, Firefox, Safari, Edge, and modern screen readers.', 'shahi-legalflowsuite' ) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n\n";

		$html .= '<!-- wp:heading -->' . "\n";
		$html .= '<h2 class="wp-block-heading">' . __( 'Assessment approach', 'shahi-legalflowsuite' ) . '</h2>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . __( 'We assess the accessibility of this website through continuous automated scanning, manual reviews, and user feedback.', 'shahi-legalflowsuite' ) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n\n";

		if ( ! empty( $commitment ) ) {
			$html .= '<!-- wp:heading -->' . "\n";
			$html .= '<h2 class="wp-block-heading">' . __( 'Our Commitment', 'shahi-legalflowsuite' ) . '</h2>' . "\n";
			$html .= '<!-- /wp:heading -->' . "\n\n";

			$html .= '<!-- wp:paragraph -->' . "\n";
			$html .= '<p>' . esc_html( $commitment ) . '</p>' . "\n";
			$html .= '<!-- /wp:paragraph -->' . "\n\n";
		}

		$html .= '<!-- wp:heading -->' . "\n";
		$html .= '<h2 class="wp-block-heading">' . __( 'Feedback', 'shahi-legalflowsuite' ) . '</h2>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . __( 'We welcome your feedback on the accessibility of this website. Please let us know if you encounter accessibility barriers.', 'shahi-legalflowsuite' ) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->' . "\n\n";

		if ( ! empty( $contact_email ) ) {
			$html .= '<!-- wp:paragraph -->' . "\n";
			$html .= '<p>' . sprintf(
				/* translators: %s: Contact email */
				__( 'Email: %s', 'shahi-legalflowsuite' ),
				'<a href="mailto:' . esc_attr( $contact_email ) . '">' . esc_html( $contact_email ) . '</a>'
			) . '</p>' . "\n";
			$html .= '<!-- /wp:paragraph -->' . "\n\n";
		}

		$html .= '<!-- wp:paragraph {"fontSize":"small"} -->' . "\n";
		$html .= '<p class="has-small-font-size">' . sprintf(
			/* translators: %s: Statement date */
			__( 'This statement was last updated on %s.', 'shahi-legalflowsuite' ),
			esc_html( $date_formatted )
		) . '</p>' . "\n";
		$html .= '<!-- /wp:paragraph -->';

		return $html;
	}

	/**
	 * AJAX: Run full scan server-side in one request
	 */
	public function ajax_run_full_scan() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Use configured post types if available, otherwise default to posts and pages
		$post_types = get_option( 'slos_scan_post_types', array( 'post', 'page' ) );
		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		$posts = get_posts(
			array(
				'post_type'      => $post_types,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$summaries = array();
		foreach ( $posts as $post ) {
			$summaries[] = $this->run_scan_for_post( $post->ID, $post );
		}

		// Consolidate once after all scans
		$this->consolidate_scan_results();

		wp_send_json_success(
			array(
				'total_scanned' => count( $posts ),
				'results'       => $summaries,
			)
		);
	}

	/**
	 * Run a scan for a single post and persist results
	 *
	 * @param int          $post_id
	 * @param WP_Post|null $post
	 * @return array Summary of scan results for UI
	 */
	private function run_scan_for_post( $post_id, $post = null ) {
		$post_id = intval( $post_id );
		if ( ! $post ) {
			$post = get_post( $post_id );
		}

		if ( ! $post ) {
			return array(
				'post_id'        => $post_id,
				'title'          => '',
				'edit_link'      => '',
				'issues_count'   => 0,
				'critical_count' => 0,
				'all_issues'     => array(),
				'error'          => 'Post not found',
			);
		}

		$results = $this->scanner->scan( $post->post_content );

		// Save results to post meta
		update_post_meta( $post_id, '_slos_accessibility_scan_results', $results );
		update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

		$issues_count   = 0;
		$critical_count = 0;
		$issue_types    = array(); // Track unique issue types instead of full details

		foreach ( $results as $check ) {
			$check_issues = isset( $check['issues'] ) ? (array) $check['issues'] : array();
			$issue_count  = count( $check_issues );

			if ( $issue_count > 0 ) {
				$issues_count += $issue_count;
				$issue_types[] = $check['id'];

				if ( isset( $check['severity'] ) && $check['severity'] === 'critical' ) {
					$critical_count += $issue_count;
				}
			}
		}

		$score = 100;
		if ( $issues_count > 0 ) {
			$score = max( 0, 100 - ( $critical_count * 10 + ( $issues_count - $critical_count ) * 3 ) );
		}

		return array(
			'post_id'        => $post_id,
			'title'          => $post->post_title,
			'url'            => get_permalink( $post_id ),
			'edit_link'      => get_edit_post_link( $post_id ),
			'issues_count'   => $issues_count,
			'critical_count' => $critical_count,
			'issue_types'    => array_unique( $issue_types ), // Only unique types
			'scan_date'      => current_time( 'mysql' ),
			'score'          => $score,
		);
	}

	/**
	 * Consolidate scan results from all posts
	 * Aggregates post-level scans into a global consolidated view
	 * Also used for dashboard and fixing operations
	 */
	private function consolidate_scan_results() {
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);

		$consolidated   = array();
		$total_issues   = 0;
		$total_critical = 0;
		$pages_scanned  = 0;
		$issues_by_type = array(); // Track issues by checker type

		foreach ( $posts as $post_id ) {
			$post         = get_post( $post_id );
			$scan_results = get_post_meta( $post_id, '_slos_accessibility_scan_results', true );

			if ( empty( $scan_results ) ) {
				continue;
			}

			++$pages_scanned;
			$issues_count   = 0;
			$critical_count = 0;
			$page_issues    = array();
			$score          = 100;

			foreach ( $scan_results as $check ) {
				$check_issues  = count( $check['issues'] ?? array() );
				$issues_count += $check_issues;

				if ( $check_issues > 0 && isset( $check['severity'] ) && $check['severity'] === 'critical' ) {
					$critical_count += $check_issues;
				}

				// Track issues by type for dashboard chart
				if ( $check_issues > 0 ) {
					$check_id = $check['id'] ?? 'unknown';
					if ( ! isset( $issues_by_type[ $check_id ] ) ) {
						$issues_by_type[ $check_id ] = array(
							'id'          => $check_id,
							'name'        => $check['name'] ?? $check_id,
							'description' => $check['description'] ?? '',
							'severity'    => $check['severity'] ?? 'warning',
							'count'       => 0,
						);
					}
					$issues_by_type[ $check_id ]['count'] += $check_issues;
				}

				// Build issues list for this page
				foreach ( $check['issues'] ?? array() as $issue ) {
					$page_issues[] = array(
						'type'        => $check['id'],
						'checker_id'  => $check['id'],
						'severity'    => $check['severity'] ?? 'warning',
						'description' => $check['description'] ?? '',
						'message'     => $issue['message'],
						'element'     => $issue['element'] ?? '',
					);
				}
			}

			// Calculate accessibility score
			if ( $issues_count > 0 ) {
				$score = max( 0, 100 - ( $critical_count * 10 + ( $issues_count - $critical_count ) * 3 ) );
			}

			$total_issues   += $issues_count;
			$total_critical += $critical_count;

			$consolidated[] = array(
				'post_id'         => $post_id,
				'page'            => $post->post_title,
				'url'             => get_permalink( $post_id ),
				'score'           => $score,
				'issues'          => $page_issues,
				'issues_count'    => $issues_count,
				'critical_count'  => $critical_count,
				'status'          => $critical_count > 0 ? 'critical' : ( $issues_count > 0 ? 'warning' : 'passed' ),
				'last_scan'       => get_post_meta( $post_id, '_slos_accessibility_scan_date', true ),
				'autofix_enabled' => (bool) get_post_meta( $post_id, '_slos_accessibility_autofix', true ),
			);
		}

		// Sort by score (lowest first - most issues)
		usort(
			$consolidated,
			function ( $a, $b ) {
				return $a['score'] <=> $b['score'];
			}
		);

		// Calculate average score
		$average_score = $pages_scanned > 0 ? round( $total_issues > 0 ? ( 100 - ( $total_critical * 10 + ( $total_issues - $total_critical ) * 3 ) / $pages_scanned ) : 100 ) : 0;
		$average_score = max( 0, min( 100, $average_score ) ); // Clamp between 0-100

		// Save consolidated results
		update_option( 'slos_last_scan_results', $consolidated );
		update_option(
			'slos_scan_statistics',
			array(
				'total_pages_scanned' => $pages_scanned,
				'total_issues'        => $total_issues,
				'total_critical'      => $total_critical,
				'average_score'       => $average_score,
				'last_consolidated'   => current_time( 'mysql' ),
			)
		);

		// Sort issues by type by count (descending) and save
		usort(
			$issues_by_type,
			function ( $a, $b ) {
				return $b['count'] <=> $a['count'];
			}
		);
		update_option( 'slos_issues_by_type', array_values( $issues_by_type ) );

		// Save to scan history for trends chart
		$this->save_scan_to_history( $pages_scanned, $total_issues, $total_critical, $average_score );

		// Save last scan time
		update_option( 'slos_last_scan_time', current_time( 'mysql' ) );
	}

	/**
	 * Save scan results to history for trends tracking
	 *
	 * @param int $pages_scanned Number of pages scanned
	 * @param int $total_issues Total issues found
	 * @param int $total_critical Total critical issues
	 * @param int $score Overall accessibility score
	 */
	private function save_scan_to_history( $pages_scanned, $total_issues, $total_critical, $score ) {
		$history = get_option( 'slos_accessibility_scan_history', array() );

		// Generate unique ID for this scan
		$scan_id = wp_generate_uuid4();

		// Create history entry
		$entry = array(
			'id'            => $scan_id,
			'date'          => current_time( 'mysql' ),
			'timestamp'     => time(),
			'score'         => intval( $score ),
			'issues'        => intval( $total_issues ),
			'critical'      => intval( $total_critical ),
			'pages_scanned' => intval( $pages_scanned ),
			'wcag_level'    => get_option( 'slos_wcag_level', 'AA' ),
		);

		// Add to beginning of array (most recent first)
		array_unshift( $history, $entry );

		// Keep only last 100 scans to prevent database bloat
		$history = array_slice( $history, 0, 100 );

		update_option( 'slos_accessibility_scan_history', $history );
	}

	/**
	 * Get mapping of settings keys to check classes
	 *
	 * @return array Associative array of settings key => check class
	 */
	private function get_check_mapping() {
		return array(
			'missing-alt-text'     => MissingAltTextCheck::class,
			'empty-alt-text'       => EmptyAltTextCheck::class,
			'missing-h1'           => MissingH1Check::class,
			'skipped-heading'      => SkippedHeadingLevelCheck::class,
			'empty-link'           => EmptyLinkCheck::class,
			'generic-link'         => GenericLinkTextCheck::class,
			'missing-label'        => MissingFormLabelCheck::class,
			'redundant-alt'        => RedundantAltTextCheck::class,
			'empty-heading'        => EmptyHeadingCheck::class,
			'new-window'           => NewWindowLinkCheck::class,
			'positive-tabindex'    => PositiveTabIndexCheck::class,
			'image-map'            => ImageMapAltCheck::class,
			'iframe-title'         => IframeTitleCheck::class,
			'button-label'         => ButtonLabelCheck::class,
			'table-header'         => TableHeaderCheck::class,
			'alt-quality'          => AltTextQualityCheck::class,
			'decorative-image'     => DecorativeImageCheck::class,
			'complex-image'        => ComplexImageCheck::class,
			'svg-access'           => SvgAccessibilityCheck::class,
			'bg-image'             => BackgroundImageCheck::class,
			'logo-image'           => LogoImageCheck::class,
			'multiple-h1'          => MultipleH1Check::class,
			'heading-visual'       => HeadingVisualCheck::class,
			'heading-length'       => HeadingLengthCheck::class,
			'heading-unique'       => HeadingUniquenessCheck::class,
			'heading-nesting'      => HeadingNestingCheck::class,
			'fieldset-legend'      => FieldsetLegendCheck::class,
			'autocomplete'         => AutocompleteCheck::class,
			'input-type'           => InputTypeCheck::class,
			'placeholder-label'    => PlaceholderLabelCheck::class,
			'custom-control'       => CustomControlCheck::class,
			'orphaned-label'       => OrphanedLabelCheck::class,
			'required-attr'        => RequiredAttributeCheck::class,
			'error-message'        => ErrorMessageCheck::class,
			'form-aria'            => FormAriaCheck::class,
			'link-dest'            => LinkDestinationCheck::class,
			'skip-link'            => SkipLinkCheck::class,
			'download-link'        => DownloadLinkCheck::class,
			'external-link'        => ExternalLinkCheck::class,
			'contrast'             => TextColorContrastCheck::class,
			'focus-indicator'      => FocusIndicatorCheck::class,
			'color-reliance'       => ColorRelianceCheck::class,
			'complex-contrast'     => ComplexContrastCheck::class,
			'keyboard-trap'        => KeyboardTrapCheck::class,
			'focus-order'          => FocusOrderCheck::class,
			'interactive-element'  => InteractiveElementCheck::class,
			'modal-access'         => ModalAccessibilityCheck::class,
			'widget-keyboard'      => CustomWidgetKeyboardCheck::class,
			'aria-role'            => AriaRoleCheck::class,
			'aria-attr'            => AriaAttributeCheck::class,
			'landmark-role'        => LandmarkRoleCheck::class,
			'redundant-aria'       => RedundantAriaCheck::class,
			'hidden-content'       => HiddenContentCheck::class,
			'semantic-html'        => SemanticHtmlCheck::class,
			'live-region'          => LiveRegionCheck::class,
			'aria-state'           => AriaStateCheck::class,
			'invalid-aria'         => InvalidAriaCombinationCheck::class,
			'page-structure'       => PageStructureCheck::class,
			'video-access'         => VideoAccessibilityCheck::class,
			'audio-access'         => AudioAccessibilityCheck::class,
			'media-alt'            => MediaAlternativeCheck::class,
			'table-caption'        => TableCaptionCheck::class,
			'complex-table'        => ComplexTableCheck::class,
			'layout-table'         => LayoutTableCheck::class,
			'empty-cell'           => EmptyTableCellCheck::class,
			'viewport'             => ViewportCheck::class,
			'touch-target'         => TouchTargetCheck::class,
			'touch-gesture'        => TouchGestureCheck::class,
			'language-change'      => LanguageChangeCheck::class,
			'animation-pause'      => AnimationPauseCheck::class,
			'timing-control'       => TimingControlCheck::class,
			'status-message'       => StatusMessageCheck::class,
			'error-identification' => ErrorIdentificationCheck::class,
		);
	}

	/**
	 * Register scanner checks based on active settings
	 * Only registers checks that are enabled in the Settings page
	 */
	private function register_checks() {
		// Get active checkers from settings
		$active_checkers = get_option( 'slos_active_checkers', array() );

		// Get the mapping
		$check_mapping = $this->get_check_mapping();

		// If no checkers are set (first run or empty), enable all by default
		if ( empty( $active_checkers ) ) {
			$active_checkers = array_keys( $check_mapping );
			// Save default to database
			update_option( 'slos_active_checkers', $active_checkers );
		}

		// Register only active checks
		foreach ( $active_checkers as $checker_key ) {
			if ( isset( $check_mapping[ $checker_key ] ) ) {
				$check_class = $check_mapping[ $checker_key ];
				if ( class_exists( $check_class ) ) {
					$this->scanner->register_check( new $check_class() );
				}
			}
		}
	}

	/**
	 * Run scan when post is saved
	 */
	/**
	 * Register admin menus
	 */
	public function register_admin_menus() {
		// Only register menus if module is enabled
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		// Register single main page with tabbed interface
		add_submenu_page(
			'shahi-legalflowsuite',
			__( 'Accessibility Scanner', 'shahi-legalflowsuite' ),
			'♿ ' . __( 'Accessibility', 'shahi-legalflowsuite' ),
			'manage_options',
			'slos-accessibility',
			array( $this, 'render_main_page' )
		);

		// Hidden settings page (accessible via URL or Module Card)
		add_submenu_page(
			null,
			__( 'Accessibility Settings', 'shahi-legalflowsuite' ),
			__( 'Accessibility Settings', 'shahi-legalflowsuite' ),
			'manage_options',
			'slos-accessibility-settings',
			array( new AccessibilitySettings(), 'render' )
		);
	}

	/**
	 * Render main tabbed page
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_main_page() {
		$main_page = new \ShahiLegalFlowSuite\Admin\AccessibilityMainPage();
		$main_page->render();
	}

	/**
	 * Run scan on post save
	 *
	 * @since 1.0.0
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 * @return void
	 */
	public function run_scan_on_save( $post_id, $post, $update ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( $post->post_type === 'revision' ) {
			return;
		}

		$content = $post->post_content;
		$results = $this->scanner->scan( $content );

		update_post_meta( $post_id, '_slos_accessibility_scan_results', $results );
		update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );
	}

	/**
	 * Add meta box to post editor
	 */
	public function add_scan_meta_box() {
		add_meta_box(
			'slos_accessibility_scan_results',
			'Accessibility Scan Results',
			array( $this, 'render_scan_meta_box' ),
			array( 'post', 'page' ),
			'side',
			'high'
		);
	}

	/**
	 * Render meta box content
	 */
	public function render_scan_meta_box( $post ) {
		$results   = get_post_meta( $post->ID, '_slos_accessibility_scan_results', true );
		$last_scan = get_post_meta( $post->ID, '_slos_accessibility_scan_date', true );

		echo '<div class="slos-accessibility-results">';
		if ( $last_scan ) {
			echo '<p><strong>Last Scan:</strong> ' . esc_html( $last_scan ) . '</p>';
		}

		if ( empty( $results ) ) {
			echo '<p style="color: green;">No accessibility issues found!</p>';
		} else {
			echo '<ul style="list-style: none; padding: 0;">';
			foreach ( $results as $check_id => $result ) {
				$color = $result['severity'] === 'critical' ? '#d63638' : '#dba617';
				echo '<li style="margin-bottom: 10px; border-left: 4px solid ' . esc_attr( $color ) . '; padding-left: 10px;">';
				echo '<strong>' . esc_html( $result['description'] ) . '</strong>';
				echo '<ul style="margin-top: 5px; padding-left: 15px;">';
				foreach ( $result['issues'] as $issue ) {
					echo '<li>' . esc_html( $issue['message'] ) . '</li>';
				}
				echo '</ul>';
				echo '</li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}

	/**
	 * AJAX: Get page issues
	 */
	public function ajax_get_page_issues() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );
		if ( empty( $post_id ) ) {
			wp_send_json_error( 'Post not specified' );
		}

		// Get post by ID first
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( 'Post not found' );
		}

		// Get consolidated results
		$results   = get_option( 'slos_last_scan_results', array() );
		$page_data = null;

		// Find by post_id instead of page name
		foreach ( $results as $result ) {
			if ( isset( $result['post_id'] ) && $result['post_id'] === $post_id ) {
				$page_data = $result;
				break;
			}
		}

		if ( ! $page_data || empty( $page_data['issues'] ) ) {
			wp_send_json_success( array( 'issues' => array() ) );
		}

		wp_send_json_success(
			array(
				'issues'       => $page_data['issues'],
				'score'        => $page_data['score'] ?? 0,
				'issues_count' => $page_data['issues_count'] ?? 0,
				'status'       => $page_data['status'] ?? 'unknown',
			)
		);
	}

	/**
	 * AJAX: Fix single issue
	 */
	public function ajax_fix_single_issue() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$post_id    = intval( $_POST['post_id'] ?? 0 );
		$issue_type = sanitize_text_field( $_POST['issue_type'] ?? '' );

		if ( empty( $post_id ) || empty( $issue_type ) ) {
			wp_send_json_error( array( 'message' => 'Missing parameters' ) );
		}

		// Get post
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => 'Post not found' ) );
		}

		$original_content = $post->post_content;

		// Get the fixer instance
		$fixer = new AccessibilityFixer();

		// Apply fix based on issue type
		$result = $fixer->fix_issue( $post_id, $issue_type );

		if ( is_wp_error( $result ) ) {
			$error_code = $result->get_error_code();
			$error_msg  = $result->get_error_message();

			// Return helpful error with guidance
			wp_send_json_error(
				array(
					'message'  => $error_msg,
					'guidance' => $this->get_manual_fix_guidance(
						array(
							array(
								'type'   => $issue_type,
								'reason' => $error_msg,
							),
						)
					),
				)
			);
		}

		$fixed_count = $result['fixed_count'] ?? 0;

		// Check if content was actually modified
		$updated_post    = get_post( $post_id );
		$content_changed = ( $updated_post->post_content !== $original_content );

		if ( $fixed_count === 0 || ! $content_changed ) {
			wp_send_json_error(
				array(
					'message'  => 'Issue could not be automatically fixed',
					'guidance' => $this->get_manual_fix_guidance(
						array(
							array(
								'type'   => $issue_type,
								'reason' => 'Automatic fix not applicable',
							),
						)
					),
				)
			);
		}

		// Re-scan the post to get accurate results
		$new_scan_results = $this->scanner->scan( $updated_post->post_content );
		update_post_meta( $post_id, '_slos_accessibility_scan_results', $new_scan_results );
		update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

		// Reconsolidate all results
		$this->consolidate_scan_results();

		wp_send_json_success(
			array(
				/* translators: %d: number of issues fixed */
				'message'         => sprintf( __( '%d issue(s) fixed successfully!', 'shahi-legalflowsuite' ), $fixed_count ),
				'fixed_count'     => $fixed_count,
				'content_changed' => $content_changed,
			)
		);
	}

	/**
	 * AJAX: Fix all issues for a page
	 */
	public function ajax_fix_all_issues() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Post not specified' ) );
		}

		// Get post
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => 'Post not found' ) );
		}

		// Get all issues for this page from consolidated data
		$results   = get_option( 'slos_last_scan_results', array() );
		$page_data = null;

		foreach ( $results as $result ) {
			if ( isset( $result['post_id'] ) && $result['post_id'] === $post_id ) {
				$page_data = $result;
				break;
			}
		}

		if ( ! $page_data || empty( $page_data['issues'] ) ) {
			wp_send_json_error( array( 'message' => 'No issues found for this page' ) );
		}

		// Get unique issue types to avoid redundant fixes
		$unique_issue_types = array();
		foreach ( $page_data['issues'] as $issue ) {
			$issue_type = $issue['type'] ?? $issue['checker_id'] ?? '';
			if ( ! empty( $issue_type ) && ! in_array( $issue_type, $unique_issue_types ) ) {
				$unique_issue_types[] = $issue_type;
			}
		}

		if ( empty( $unique_issue_types ) ) {
			wp_send_json_error( array( 'message' => 'No fixable issues found' ) );
		}

		// Get the fixer instance
		$fixer = new AccessibilityFixer();

		// Get issue count before fixes
		$issues_before = count( $page_data['issues'] );

		// Save content backup before applying fixes
		$this->save_content_backup( $post_id, $post->post_content );

		// Track progress
		$fixed_issues        = array();
		$failed_issues       = array();
		$manual_fix_required = array();
		$fixed_count_total   = 0;
		$original_content    = $post->post_content;

		// Apply fixes for each unique issue type
		foreach ( $unique_issue_types as $issue_type ) {
			$result = $fixer->fix_issue( $post_id, $issue_type );

			if ( is_wp_error( $result ) ) {
				$error_code = $result->get_error_code();
				$error_msg  = $result->get_error_message();

				// Categorize the error
				if ( $error_code === 'fixer_not_found' ) {
					$manual_fix_required[] = array(
						'type'    => $issue_type,
						'reason'  => 'No automatic fix available',
						'message' => $error_msg,
					);
				} else {
					$failed_issues[] = array(
						'type'    => $issue_type,
						'reason'  => $error_msg,
						'message' => $error_msg,
					);
				}
				// Short-circuit: Skip to next issue type on error
				continue;
			}

			$fixed_count = $result['fixed_count'] ?? 0;

			// Short-circuit: If no fixes applied, skip history tracking and mark as manual
			if ( $fixed_count === 0 ) {
				$manual_fix_required[] = array(
					'type'    => $issue_type,
					'reason'  => 'Issue could not be automatically fixed',
					'message' => 'Manual intervention required',
				);
				continue;
			}

			// Only process successful fixes with fixed_count > 0
			$fixed_issues[]     = array(
				'type'        => $issue_type,
				'count'       => $fixed_count,
				'description' => $this->get_issue_description( $issue_type ),
			);
			$fixed_count_total += $fixed_count;

			// Save individual fix history (only when fixes were applied)
			$updated_post = get_post( $post_id );
			$this->save_fix_history(
				$post_id,
				$issue_type,
				$fixed_count,
				$original_content,
				$updated_post->post_content,
				null,
				null
			);
		}

		// Check if content was actually modified
		$updated_post    = get_post( $post_id );
		$content_changed = ( $updated_post->post_content !== $original_content );

		// If fixes were applied, re-scan the post to get accurate results
		if ( $content_changed && $fixed_count_total > 0 ) {
			// Re-scan the post
			$new_scan_results = $this->scanner->scan( $updated_post->post_content );

			// Calculate issues after fixes
			$issues_after = 0;
			if ( is_array( $new_scan_results ) ) {
				foreach ( $new_scan_results as $result ) {
					if ( isset( $result['issues'] ) && is_array( $result['issues'] ) ) {
						$issues_after += count( $result['issues'] );
					}
				}
			}

			// Save combined fix history for bulk operation
			$this->save_fix_history(
				$post_id,
				'bulk_fix_all',
				$fixed_count_total,
				$original_content,
				$updated_post->post_content,
				$issues_before,
				$issues_after
			);

			update_post_meta( $post_id, '_slos_accessibility_scan_results', $new_scan_results );
			update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

			// Reconsolidate all results
			$this->consolidate_scan_results();
		}

		// Get the new issue count for this page
		$new_results      = get_option( 'slos_last_scan_results', array() );
		$new_page_data    = null;
		$new_issues_count = 0;
		$new_score        = 100;

		foreach ( $new_results as $result ) {
			if ( isset( $result['post_id'] ) && $result['post_id'] === $post_id ) {
				$new_page_data    = $result;
				$new_issues_count = $result['issues_count'] ?? 0;
				$new_score        = $result['score'] ?? 100;
				break;
			}
		}

		// Build response
		$response = array(
			'success'             => true,
			'message'             => $this->build_fix_message( $fixed_count_total, count( $failed_issues ), count( $manual_fix_required ) ),
			'fixed_count'         => count( $fixed_issues ),
			'failed_count'        => count( $failed_issues ),
			'manual_required'     => count( $manual_fix_required ),
			'total_issues_fixed'  => $fixed_count_total,
			'content_changed'     => $content_changed,
			'new_issues_count'    => $new_issues_count,
			'new_score'           => $new_score,
			'fixed_details'       => $fixed_issues,
			'failed_details'      => $failed_issues,
			'manual_fix_guidance' => $this->get_manual_fix_guidance( $manual_fix_required ),
		);

		wp_send_json_success( $response );
	}

	/**
	 * Get issue description from type
	 */
	private function get_issue_description( $issue_type ) {
		$descriptions = array(
			'missing-alt-text'      => 'Images missing alt text',
			'empty-alt-text'        => 'Images with empty alt attributes',
			'empty-link'            => 'Links without text',
			'generic-link-text'     => 'Links with generic text like "click here"',
			'missing-form-label'    => 'Form fields without labels',
			'missing-h1'            => 'Page missing H1 heading',
			'skipped-heading-level' => 'Heading levels that skip (e.g., H2 to H4)',
			'empty-heading'         => 'Empty heading tags',
			'new-window-link'       => 'Links opening in new window without warning',
			'positive-tabindex'     => 'Elements with positive tabindex',
			'table-header'          => 'Tables missing header cells',
			'iframe-title'          => 'Iframes without titles',
			'button-label'          => 'Buttons without accessible names',
		);

		return isset( $descriptions[ $issue_type ] ) ? $descriptions[ $issue_type ] : ucwords( str_replace( '-', ' ', $issue_type ) );
	}

	/**
	 * Build user-friendly fix message
	 */
	private function build_fix_message( $fixed, $failed, $manual ) {
		if ( $fixed > 0 && $failed === 0 && $manual === 0 ) {
				/* translators: %d: number of issues fixed */
			return sprintf( _n( '%d issue was automatically fixed!', '%d issues were automatically fixed!', $fixed, 'shahi-legalflowsuite' ), $fixed );
		} elseif ( $fixed > 0 && ( $failed > 0 || $manual > 0 ) ) {
			return sprintf( /* translators: 1: number of issues fixed, 2: number of issues requiring manual attention */  __( '%1$d issues fixed. %2$d issues require manual attention.', 'shahi-legalflowsuite' ), $fixed, $failed + $manual );
		} elseif ( $fixed === 0 && ( $failed > 0 || $manual > 0 ) ) {
			return __( 'No issues could be automatically fixed. Manual intervention required.', 'shahi-legalflowsuite' );
		}
		return __( 'Fix process completed.', 'shahi-legalflowsuite' );
	}

	/**
	 * Get manual fix guidance for issues that couldn't be auto-fixed
	 */
	private function get_manual_fix_guidance( $manual_issues ) {
		$guidance = array();

		$fix_guides = array(
			'missing-alt-text'      => array(
				'title'       => 'Missing Alt Text',
				'description' => 'Images need descriptive alt text for screen readers.',
				'steps'       => array(
					'Go to Media Library and find the image',
					'Click on the image to edit',
					'Add a descriptive alt text that explains what the image shows',
					'Save changes and update the post',
				),
				'tip'         => 'Good alt text is concise but descriptive. Describe what the image shows, not how it looks.',
			),
			'empty-link'            => array(
				'title'       => 'Empty Links',
				'description' => 'Links must have text or an aria-label for accessibility.',
				'steps'       => array(
					'Find the link in your post editor',
					'Add descriptive text between the <a> tags',
					'Or add an aria-label attribute to the link',
				),
				'tip'         => 'Link text should describe where the link goes, not just "click here".',
			),
			'generic-link-text'     => array(
				'title'       => 'Generic Link Text',
				'description' => 'Links with text like "click here" or "read more" are not accessible.',
				'steps'       => array(
					'Find links with generic text',
					'Replace with descriptive text that explains the destination',
					'Example: Change "Click here" to "Download our accessibility guide"',
				),
				'tip'         => 'Screen reader users often navigate by links. Make each link text unique and descriptive.',
			),
			'missing-form-label'    => array(
				'title'       => 'Missing Form Labels',
				'description' => 'Form inputs need associated labels for screen readers.',
				'steps'       => array(
					'Add a <label> element for each form input',
					'Connect the label using the "for" attribute matching the input "id"',
					'Or wrap the input inside the label element',
				),
				'tip'         => 'Placeholders are not substitutes for labels.',
			),
			'skipped-heading-level' => array(
				'title'       => 'Skipped Heading Levels',
				'description' => 'Headings should follow a logical order (H1 → H2 → H3).',
				'steps'       => array(
					'Review your heading structure',
					'Ensure headings follow sequential order',
					'Don\'t skip from H2 to H4 without H3',
				),
				'tip'         => 'Think of headings as an outline. They help users navigate your content.',
			),
			'low-contrast'          => array(
				'title'       => 'Low Color Contrast',
				'description' => 'Text must have sufficient contrast against its background.',
				'steps'       => array(
					'Use a contrast checker tool',
					'Ensure at least 4.5:1 ratio for normal text',
					'Ensure at least 3:1 ratio for large text',
					'Adjust text or background colors',
				),
				'tip'         => 'WCAG requires 4.5:1 contrast for AA compliance.',
			),
		);

		foreach ( $manual_issues as $issue ) {
			$type = $issue['type'];
			if ( isset( $fix_guides[ $type ] ) ) {
				$guidance[] = array_merge( $fix_guides[ $type ], array( 'issue_type' => $type ) );
			} else {
				$guidance[] = array(
					'issue_type'  => $type,
					'title'       => ucwords( str_replace( '-', ' ', $type ) ),
					'description' => $issue['reason'] ?? 'This issue requires manual review.',
					'steps'       => array(
						'Review the flagged content in your post editor',
						'Make the necessary accessibility improvements',
						'Re-scan to verify the fix',
					),
					'tip'         => 'Consult WCAG guidelines for detailed requirements.',
				);
			}
		}

		return $guidance;
	}

	/**
	 * AJAX: Toggle autofix for a page
	 */
	public function ajax_toggle_autofix() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );
		$enabled = filter_var( $_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN );

		if ( empty( $post_id ) ) {
			wp_send_json_error( 'Post ID not specified' );
		}

		// Verify post exists
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( 'Post not found' );
		}

		// Update autofix setting in post meta (more reliable than options array)
		update_post_meta( $post_id, '_slos_accessibility_autofix', $enabled );

		// Update the consolidated scan results cache
		$results = get_option( 'slos_last_scan_results', array() );
		foreach ( $results as &$result ) {
			if ( $result['post_id'] === $post_id ) {
				$result['autofix_enabled'] = $enabled;
				break;
			}
		}
		update_option( 'slos_last_scan_results', $results );

		wp_send_json_success(
			array(
				'message' => $enabled ? 'Auto Fix enabled' : 'Auto Fix disabled',
				'enabled' => $enabled,
			)
		);
	}

	/**
	 * Update scan results after fixing an issue
	 */
	/**
	 * Update scan results after fixing an issue
	 */
	private function update_scan_results_after_fix( $post_id, $issue_type ) {
		$results = get_option( 'slos_last_scan_results', array() );

		foreach ( $results as &$result ) {
			if ( isset( $result['post_id'] ) && $result['post_id'] === $post_id ) {
				// Remove the fixed issue from the issues list
				$result['issues'] = array_filter(
					$result['issues'],
					function ( $issue ) use ( $issue_type ) {
						return $issue['type'] !== $issue_type;
					}
				);

				// Recalculate counts
				$result['issues']       = array_values( $result['issues'] ); // Re-index
				$result['issues_count'] = count( $result['issues'] );

				$critical_count = 0;
				foreach ( $result['issues'] as $issue ) {
					if ( $issue['severity'] === 'critical' ) {
						++$critical_count;
					}
				}
				$result['critical_count'] = $critical_count;
				$result['status']         = $critical_count > 0 ? 'critical' : ( count( $result['issues'] ) > 0 ? 'warning' : 'passed' );

				break;
			}
		}

		update_option( 'slos_last_scan_results', $results );
	}

	/**
	 * Recalculate page score after fixes
	 */
	private function recalculate_page_score( $post_id ) {
		$results = get_option( 'slos_last_scan_results', array() );

		foreach ( $results as &$result ) {
			if ( isset( $result['post_id'] ) && $result['post_id'] === $post_id ) {
				// Recalculate score based on remaining issues
				$issues_count   = count( $result['issues'] ?? array() );
				$critical_count = 0;

				foreach ( $result['issues'] ?? array() as $issue ) {
					if ( $issue['severity'] === 'critical' ) {
						++$critical_count;
					}
				}

				// Score calculation: 100 - (critical * 10) - (warnings * 3)
				$score                    = max( 0, 100 - ( $critical_count * 10 + ( $issues_count - $critical_count ) * 3 ) );
				$result['score']          = $score;
				$result['issues_count']   = $issues_count;
				$result['critical_count'] = $critical_count;

				// Determine status
				if ( $score >= 90 ) {
					$result['status'] = 'passed';
				} elseif ( $score >= 70 ) {
					$result['status'] = 'warning';
				} else {
					$result['status'] = 'critical';
				}

				break;
			}
		}

		update_option( 'slos_last_scan_results', $results );
	}

	/**
	 * Update global accessibility stats
	 */
	private function update_global_stats() {
		$results = get_option( 'slos_last_scan_results', array() );

		$total_critical = 0;
		$total_warning  = 0;
		$total_score    = 0;
		$pages_scanned  = count( $results );

		foreach ( $results as $result ) {
			$total_critical += $result['critical'];
			$total_warning  += $result['warning'];
			$total_score    += $result['score'];
		}

		update_option( 'slos_accessibility_issues_critical', $total_critical );
		update_option( 'slos_accessibility_issues_warning', $total_warning );
		update_option( 'slos_accessibility_issues_total', $total_critical + $total_warning );
		update_option( 'slos_accessibility_score', $pages_scanned > 0 ? round( $total_score / $pages_scanned ) : 0 );
		update_option( 'slos_accessibility_pages_scanned', $pages_scanned );
	}

	/**
	 * AJAX: Get fixable issues for a page
	 *
	 * Determines which fixers are relevant for the given page based on the
	 * stored scan results. The response includes only fixers that have
	 * detected issues on this page so the frontend can run a targeted set
	 * by default.
	 *
	 * @since 3.2.0
	 */
	public function ajax_get_page_fixable_issues() {
		check_ajax_referer( 'slos_autofix_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$page_id = intval( $_POST['page_id'] ?? 0 );

		if ( empty( $page_id ) ) {
			wp_send_json_error( array( 'message' => 'Missing page ID' ) );
		}

		// Get scan results for this page
		$scan_results = get_post_meta( $page_id, '_slos_accessibility_scan_results', true );

		// Count total issues in scan results
		$total_issues = 0;
		if ( ! empty( $scan_results ) && is_array( $scan_results ) ) {
			foreach ( $scan_results as $check_result ) {
				if ( ! empty( $check_result['issues'] ) && is_array( $check_result['issues'] ) ) {
					$total_issues += count( $check_result['issues'] );
				}
			}
		}

		// If scan results exist and have issues, signal frontend to use all fixers
		// This allows all fixers to attempt fixing regardless of scan detection accuracy
		if ( $total_issues > 0 ) {
			wp_send_json_success(
				array(
					'use_all_fixers' => true,
					'issue_count'    => $total_issues,
					'message'        => sprintf( 'Found %d issue(s) - running all fixers', $total_issues ),
				)
			);
			return;
		}

		// No scan results or no issues - return empty fixer list
		wp_send_json_success(
			array(
				'fixers'  => array(),
				'message' => $total_issues === 0 && ! empty( $scan_results )
					? 'No accessibility issues detected'
					: 'No scan results found. Please scan this page first.',
			)
		);
	}

	/**
	 * Get mapping of checker IDs to fixer IDs
	 * Not all checkers have corresponding fixers
	 *
	 * @since 3.2.0
	 * @return array Associative array of checker_id => fixer_id
	 */
	private function get_check_to_fixer_mapping() {
		// Map scan checker IDs to canonical FixEngine IDs.
		// Right-hand side values are canonical IDs defined in
		// FixEngine\CanonicalIds; any legacy aliases are handled via
		// CanonicalIds::canonicalize() inside FixEngine.
		return array(
			// Image-related checks (scan result keys => canonical/alias IDs)
			'missing-alt-text'     => 'missing-alt-text',
			'empty-alt-text'       => 'empty-alt-text',
			'redundant-alt'        => 'redundant-alt-text',
			'alt-quality'          => 'alt-text-quality',
			'decorative-image'     => 'decorative-image',
			'complex-image'        => 'complex-image',
			'svg-access'           => 'svg-accessibility',
			'bg-image'             => 'background-image',
			'logo-image'           => 'logo-image',
			'image-map'            => 'image-map-alt',

			// Heading-related checks
			'missing-h1'           => 'missing-h1',
			'multiple-h1'          => 'multiple-h1',
			'skipped-heading'      => 'skipped-heading-level',
			'empty-heading'        => 'empty-heading',
			'heading-length'       => 'heading-length',
			'heading-unique'       => 'heading-uniqueness',
			'heading-visual'       => 'heading-visual',
			'heading-nesting'      => 'heading-nesting',

			// Form-related checks
			'missing-label'        => 'missing-form-label',
			'placeholder-label'    => 'placeholder-label',
			'orphaned-label'       => 'orphaned-label',
			'fieldset-legend'      => 'fieldset-legend',
			'autocomplete'         => 'autocomplete-attribute',
			'input-type'           => 'input-type',
			'required-attr'        => 'required-attribute',
			'error-message'        => 'error-message',
			'form-aria'            => 'form-aria',
			'custom-control'       => 'custom-control',

			// Link-related checks
			'empty-link'           => 'empty-link',
			'generic-link'         => 'generic-link-text',
			'new-window'           => 'new-window-link',
			'download-link'        => 'download-link',
			'external-link'        => 'external-link',
			'link-dest'            => 'link-destination',
			'skip-link'            => 'skip-link',

			// ARIA-related checks
			'aria-role'            => 'aria-role',
			'aria-attr'            => 'aria-attribute',
			'aria-state'           => 'aria-state',
			'redundant-aria'       => 'redundant-aria',
			'invalid-aria'         => 'invalid-aria-combination',
			'landmark-role'        => 'landmark-role',
			'hidden-content'       => 'hidden-content',
			'live-region'          => 'live-region',

			// Table-related checks
			'table-header'         => 'table-header',
			'table-caption'        => 'table-caption',
			'complex-table'        => 'complex-table',
			'layout-table'         => 'layout-table',
			'empty-cell'           => 'empty-table-cell',

			// Keyboard & Interaction
			'positive-tabindex'    => 'positive-tabindex',
			'keyboard-trap'        => 'keyboard-trap',
			'focus-order'          => 'focus-order',
			'focus-indicator'      => 'focus-indicator',
			'interactive-element'  => 'interactive-element',
			'modal-access'         => 'modal-accessibility',
			'widget-keyboard'      => 'interactive-element',

			// Color & Contrast
			'contrast'             => 'text-color-contrast',
			'color-reliance'       => 'color-reliance',
			'complex-contrast'     => 'complex-contrast',

			// Mobile & Viewport
			'touch-target'         => 'touch-target',
			'touch-gesture'        => 'touch-gesture',
			'viewport'             => 'viewport-check',

			// Semantic & Structure
			'semantic-html'        => 'semantic-html',
			'page-structure'       => 'page-structure',

			// Media & Other
			'button-label'         => 'button-label',
			'iframe-title'         => 'iframe-title',
			'video-access'         => 'video-accessibility',
			'audio-access'         => 'audio-accessibility',
			'media-alt'            => 'media-alternative',

			// Advanced
			'language-change'      => 'language-change',
			'animation-pause'      => 'animation-pause',
			'timing-control'       => 'timing-control',
			'status-message'       => 'status-message',
			'error-identification' => 'error-identification',

			// Handle scan results that might use longer forms
			'video-accessibility'  => 'video-accessibility',
			'audio-accessibility'  => 'audio-accessibility',
			'media-alternative'    => 'media-alternative',
		);
	}

	/**
	 * Get user-friendly name from fixer ID
	 *
	 * @since 3.2.0
	 * @param string $fixer_id Fixer ID
	 * @return string User-friendly name
	 */
	private function get_fixer_name_from_id( $fixer_id ) {
		// Convert fixer ID to readable name
		$name = str_replace( array( '-', '_' ), ' ', $fixer_id );
		$name = ucwords( $name );
		return $name;
	}

	/**
	 * AJAX: Run a single fixer for the auto-fix progress popup
	 *
	 * @since 3.2.0
	 */
	public function ajax_autofix_single_fixer() {
		check_ajax_referer( 'slos_autofix_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$fixer_id = sanitize_text_field( $_POST['fixer_id'] ?? '' );
		$page_id  = intval( $_POST['page_id'] ?? 0 );
		$content  = wp_kses_post( $_POST['content'] ?? '' );

		if ( empty( $fixer_id ) ) {
			wp_send_json_error( array( 'message' => 'Missing fixer ID' ) );
		}

		try {
			// If page_id provided, get content from the post
			if ( $page_id > 0 ) {
				$post = get_post( $page_id );
				if ( $post ) {
					$content = $post->post_content;
				}
			}

			// If no content, skip
			if ( empty( $content ) ) {
				wp_send_json(
					array(
						'skipped' => true,
						'reason'  => 'no-content',
						'message' => 'No content to process',
					)
				);
				return;
			}

			// Get issue count before fix
			$issues_before = null;
			if ( $page_id > 0 ) {
				$scan_results_before = get_post_meta( $page_id, '_slos_accessibility_scan_results', true );
				if ( is_array( $scan_results_before ) ) {
					$issues_before = 0;
					foreach ( $scan_results_before as $result ) {
						if ( isset( $result['issues'] ) && is_array( $result['issues'] ) ) {
							$issues_before += count( $result['issues'] );
						}
					}
				}
			}

			// Save content backup before fixing
			if ( $page_id > 0 ) {
				$this->save_content_backup( $page_id, $content );
			}

			// Prefer the canonical FixEngine for executing the fixer when available
			$fixed_count     = 0;
			$fixed_content   = $content;
			$content_changed = false;

			if ( $page_id > 0 && class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap' ) ) {
				$engine = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap::get_engine();
				$engine->initialize();
				$session = $engine->fix_post( $page_id, array( $fixer_id ) );

				$stats           = $session->get_stats();
				$fixed_count     = isset( $stats['total_fixes'] ) ? (int) $stats['total_fixes'] : 0;
				$fixed_content   = $session->get_final_content();
				$content_changed = $session->has_changes();
			} else {
				// Strict single-pipeline mode: do not silently fall back to the legacy FixerRegistry.
				//
				// By default, if FixEngine is unavailable for any reason, we now treat the
				// request as skipped rather than proxying to the legacy system. This enforces
				// FixEngine as the single source of truth for all autofix execution.
				//
				// If a site explicitly opts in to legacy fallback (for example, for
				// emergency debugging on a misconfigured environment), it can define the
				// SLOS_ENABLE_LEGACY_FIXERS constant to true before this plugin loads.
				if ( defined( 'SLOS_ENABLE_LEGACY_FIXERS' ) && SLOS_ENABLE_LEGACY_FIXERS ) {
					$fixer = null;

					if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
						\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
						$fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $fixer_id );
					}

					// No fixer found in legacy registry
					if ( ! $fixer ) {
						wp_send_json(
							array(
								'skipped' => true,
								'reason'  => 'fixer-unavailable',
								'message' => 'Fixer not found: ' . esc_html( $fixer_id ),
							)
						);
						return;
					}

					$result = $fixer->fix( $content );

					if ( is_array( $result ) && isset( $result['content'] ) ) {
						$fixed_count     = $result['fixed_count'] ?? $result['fixes_applied'] ?? 0;
						$fixed_content   = $result['content'];
						$content_changed = ( $fixed_content !== $content );
					}
				} else {
					// Legacy fallback is disabled and FixEngine is unavailable: report a
					// skipped result so callers can surface a clear message to the user.
					wp_send_json(
						array(
							'skipped' => true,
							'reason'  => 'fixengine-unavailable',
							'message' => 'Autofix is temporarily unavailable because the FixEngine bootstrap could not be loaded.',
						)
					);
					return;
				}
			}

			// Guardrail: Ensure we don't mark success if no fixes and no content change
			if ( $fixed_count === 0 && ! $content_changed ) {
				wp_send_json(
					array(
						'skipped' => true,
						'reason'  => 'no-issues',
						'message' => 'No issues found',
					)
				);
				return;
			}

			// If page_id provided and content changed, save the post and re-scan
			if ( $page_id > 0 && $content_changed && $fixed_count > 0 ) {
				// When using FixEngine, the post content is already updated, but we
				// still rely on the updated content for re-scan and history.
				wp_update_post(
					array(
						'ID'           => $page_id,
						'post_content' => $fixed_content,
					)
				);

				// Re-scan the post to get accurate results
				if ( isset( $this->scanner ) && method_exists( $this->scanner, 'scan' ) ) {
					$updated_post     = get_post( $page_id );
					$new_scan_results = $this->scanner->scan( $updated_post->post_content );

					// Get issue count after fix
					$issues_after = 0;
					if ( is_array( $new_scan_results ) ) {
						foreach ( $new_scan_results as $result ) {
							if ( isset( $result['issues'] ) && is_array( $result['issues'] ) ) {
								$issues_after += count( $result['issues'] );
							}
						}
					}

					// Save fix history to database (keeps existing reporting in sync)
					$this->save_fix_history(
						$page_id,
						$fixer_id,
						$fixed_count,
						$content,
						$fixed_content,
						$issues_before,
						$issues_after
					);

					// Persist scan results and date to post meta
					update_post_meta( $page_id, '_slos_accessibility_scan_results', $new_scan_results );
					update_post_meta( $page_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

					// Reconsolidate all results to keep counts in sync
					if ( method_exists( $this, 'consolidate_scan_results' ) ) {
						$this->consolidate_scan_results();
					}
				}
			}

			if ( $fixed_count > 0 ) {
				wp_send_json_success(
					array(
						'fixed_count'     => $fixed_count,
						'content_changed' => $content_changed,
					)
				);
			} else {
				wp_send_json(
					array(
						'skipped' => true,
						'reason'  => 'no-issues',
						'message' => 'No issues found',
					)
				);
			}
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * AJAX: Get detailed scan report for a specific page
	 *
	 * @since 3.2.0
	 */
	public function ajax_get_detailed_scan_report() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Missing post ID' ) );
		}

		// Get scan results for this page
		$scan_results = get_post_meta( $post_id, '_slos_accessibility_scan_results', true );
		$scan_date    = get_post_meta( $post_id, '_slos_accessibility_scan_date', true );
		$post         = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( array( 'message' => 'Post not found' ) );
		}

		// Prepare response data
		$total_issues = 0;
		$issues       = array();

		if ( ! empty( $scan_results ) && is_array( $scan_results ) ) {
			foreach ( $scan_results as $check_id => $result ) {
				if ( ! empty( $result['issues'] ) && is_array( $result['issues'] ) ) {
					$issue_count   = count( $result['issues'] );
					$total_issues += $issue_count;

					$issues[] = array(
						'name'        => $result['name'] ?? ucwords( str_replace( array( '-', '_' ), ' ', $check_id ) ),
						'description' => $result['description'] ?? 'Accessibility issue detected',
						'severity'    => $result['severity'] ?? 'minor',
						'count'       => $issue_count,
						'fix_tip'     => $this->get_fix_tip( $check_id ),
					);
				}
			}
		}

		// Calculate score
		$score = $total_issues === 0 ? 100 : max( 0, 100 - ( $total_issues * 2 ) );

		wp_send_json_success(
			array(
				'page_title'   => $post->post_title,
				'score'        => $score,
				'total_issues' => $total_issues,
				'scan_date'    => $scan_date ? date_i18n( 'M j, Y g:i A', strtotime( $scan_date ) ) : 'Never scanned',
				'issues'       => $issues,
			)
		);
	}

	/**
	 * Get fix tip for a specific check
	 *
	 * @param string $check_id Check identifier
	 * @return string Fix tip
	 */
	private function get_fix_tip( $check_id ) {
		$tips = array(
			'missing-alt-text'    => 'Add descriptive alt attributes to all images. Describe what the image shows, not just "image" or "photo".',
			'button-label'        => 'Ensure all buttons have visible text or aria-label attributes that describe their purpose.',
			'table-header'        => 'Add <th> elements with scope attributes to table rows to identify headers.',
			'video-accessibility' => 'Provide captions for videos using <track> elements with WebVTT files.',
			'media-alternative'   => 'Add text transcripts for audio content and alternative descriptions for media.',
			'form-label'          => 'Associate every form input with a <label> element using the for/id attributes.',
			'heading-structure'   => 'Use heading levels (h1-h6) in proper order without skipping levels.',
			'link-text'           => 'Use descriptive link text instead of "click here" or "read more". Describe the destination.',
			'color-contrast'      => 'Ensure text has sufficient contrast ratio: 4.5:1 for normal text, 3:1 for large text.',
			'aria-labels'         => 'Add appropriate ARIA labels and roles to custom interactive elements.',
			'keyboard-access'     => 'Ensure all interactive elements are keyboard accessible using Tab and Enter keys.',
			'table-caption'       => 'Add <caption> elements to tables to describe their purpose.',
			'empty-heading'       => 'Remove empty headings or add meaningful content to them.',
			'empty-link'          => 'Add descriptive text to links or remove empty link elements.',
			'skip-link'           => 'Add a "Skip to main content" link at the top of the page for keyboard users.',
		);

		return $tips[ $check_id ] ?? 'Review the issue details and consult WCAG guidelines for proper implementation.';
	}

	/**
	 * Handle fix request using new FixEngine
	 *
	 * @since 3.3.0
	 * @param object $engine The FixEngine instance
	 * @param object $fixer The fixer instance
	 * @param string $fixer_id The fixer ID
	 * @param int    $page_id The page ID
	 * @param string $content The content to fix
	 */
	private function handle_fix_engine_request( $engine, $fixer, $fixer_id, $page_id, $content ) {
		try {
			// If page_id provided, get content from the post
			if ( $page_id > 0 ) {
				$post = get_post( $page_id );
				if ( $post ) {
					$content = $post->post_content;
				}
			}

			// If no content, skip
			if ( empty( $content ) ) {
				wp_send_json(
					array(
						'skipped' => true,
						'message' => 'No content to process',
					)
				);
				return;
			}

			// Get issue count before fix
			$issues_before = null;
			if ( $page_id > 0 ) {
				$scan_results_before = get_post_meta( $page_id, '_slos_accessibility_scan_results', true );
				if ( is_array( $scan_results_before ) ) {
					$issues_before = 0;
					foreach ( $scan_results_before as $result ) {
						if ( isset( $result['issues'] ) && is_array( $result['issues'] ) ) {
							$issues_before += count( $result['issues'] );
						}
					}
				}
			}

			// Save content backup before fixing
			if ( $page_id > 0 ) {
				$this->save_content_backup( $page_id, $content );
			}

			// Run the fixer using new FixEngine
			$result = $fixer->fix( $content, array( 'post_id' => $page_id ) );

			// Process result (FixResult object)
			$fixed_count     = $result->get_fixes_applied();
			$fixed_content   = $result->get_fixed_content();
			$content_changed = ( $fixed_content !== $content );

			// Guardrail: Ensure we don't mark success if no fixes and no content change
			if ( $fixed_count === 0 && ! $content_changed ) {
				wp_send_json(
					array(
						'skipped' => true,
						'message' => $result->get_message() ?: 'No issues found',
					)
				);
				return;
			}

			// If page_id provided and content changed, save the post
			if ( $page_id > 0 && $content_changed && $fixed_count > 0 ) {
				wp_update_post(
					array(
						'ID'           => $page_id,
						'post_content' => $fixed_content,
					)
				);

				// Re-scan the post to get accurate results
				if ( isset( $this->scanner ) && method_exists( $this->scanner, 'scan' ) ) {
					$updated_post     = get_post( $page_id );
					$new_scan_results = $this->scanner->scan( $updated_post->post_content );

					// Get issue count after fix
					$issues_after = 0;
					if ( is_array( $new_scan_results ) ) {
						foreach ( $new_scan_results as $scan_result ) {
							if ( isset( $scan_result['issues'] ) && is_array( $scan_result['issues'] ) ) {
								$issues_after += count( $scan_result['issues'] );
							}
						}
					}

					// Save fix history to database
					$this->save_fix_history(
						$page_id,
						$fixer_id,
						$fixed_count,
						$content,
						$fixed_content,
						$issues_before,
						$issues_after
					);

					// Persist scan results and date to post meta
					update_post_meta( $page_id, '_slos_accessibility_scan_results', $new_scan_results );
					update_post_meta( $page_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

					// Reconsolidate all results to keep counts in sync
					if ( method_exists( $this, 'consolidate_scan_results' ) ) {
						$this->consolidate_scan_results();
					}
				}
			}

			if ( $fixed_count > 0 ) {
				wp_send_json_success(
					array(
						'fixed_count'     => $fixed_count,
						'content_changed' => $content_changed,
						'fixer_name'      => $fixer->get_name(),
						'details'         => $result->get_details(),
					)
				);
			} else {
				wp_send_json(
					array(
						'skipped' => true,
						'message' => 'No issues found',
					)
				);
			}
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Save content backup before applying fixes
	 *
	 * @since 3.1.1
	 * @deprecated 3.2.0 Use BackupService::save_backup() instead
	 * @param int    $post_id Post ID
	 * @param string $content Original content
	 * @return bool True on success, false on failure
	 */
	private function save_content_backup( $post_id, $content ) {
		_deprecated_function( __METHOD__, '3.2.0', 'BackupService::save_backup()' );

		// Use BackupService for new backup system
		$backup_id = $this->backup_service->save_backup( $post_id, $content );

		// Also maintain old post meta for backward compatibility during transition
		$backup_key = '_slos_accessibility_content_backup';
		$backup     = array(
			'content'    => $content,
			'timestamp'  => current_time( 'timestamp' ),
			'created_at' => current_time( 'mysql' ),
		);
		update_post_meta( $post_id, $backup_key, $backup );

		return (bool) $backup_id;
	}

	/**
	 * Get content backup for rollback
	 *
	 * @since 3.1.1
	 * @deprecated 3.2.0 Use BackupService::get_latest_backup() instead
	 * @param int $post_id Post ID
	 * @return array|false Backup data or false if not found
	 */
	private function get_content_backup( $post_id ) {
		_deprecated_function( __METHOD__, '3.2.0', 'BackupService::get_latest_backup()' );

		// Try BackupService first (new system)
		$backup = $this->backup_service->get_latest_backup( $post_id );
		if ( $backup ) {
			return array(
				'content'    => $backup['original_content'],
				'timestamp'  => strtotime( $backup['created_at'] ),
				'created_at' => $backup['created_at'],
			);
		}

		// Fallback to post meta (old system)
		$backup = get_post_meta( $post_id, '_slos_accessibility_content_backup', true );

		if ( empty( $backup ) || ! is_array( $backup ) ) {
			return false;
		}

		return $backup;
	}

	/**
	 * Delete content backup (after successful fix or TTL expiration)
	 *
	 * @since 3.1.1
	 * @param int $post_id Post ID
	 * @return bool True on success, false on failure
	 */
	private function delete_content_backup( $post_id ) {
		return delete_post_meta( $post_id, '_slos_accessibility_content_backup' );
	}

	/**
	 * Save fix history to database
	 *
	 * @since 3.1.1
	 * @param int    $post_id       Post ID
	 * @param string $fixer_id      Fixer identifier
	 * @param int    $fixed_count   Number of fixes applied
	 * @param string $content_before Original content
	 * @param string $content_after  Fixed content
	 * @param int    $issues_before  Issue count before fix
	 * @param int    $issues_after   Issue count after fix
	 * @return int|false Insert ID on success, false on failure
	 */
	private function save_fix_history( $post_id, $fixer_id, $fixed_count, $content_before, $content_after, $issues_before = null, $issues_after = null ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';

		// Check if table exists
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			return false;
		}

		$data = array(
			'post_id'             => $post_id,
			'fixer_id'            => $fixer_id,
			'fixed_count'         => $fixed_count,
			'content_hash_before' => md5( $content_before ),
			'content_hash_after'  => md5( $content_after ),
			'issues_before'       => $issues_before,
			'issues_after'        => $issues_after,
			'user_id'             => get_current_user_id(),
			'action'              => 'auto_fix',
			'created_at'          => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( $table_name, $data );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Cleanup old backups (TTL mechanism)
	 *
	 * @since 3.1.1
	 * @deprecated 3.2.0 Use BackupService::cleanup_old_backups() instead
	 * @param int $ttl_days Number of days to keep backups (default 7)
	 * @return int Number of backups cleaned up
	 */
	private function cleanup_old_backups( $ttl_days = 7 ) {
		_deprecated_function( __METHOD__, '3.2.0', 'BackupService::cleanup_old_backups()' );

		// Use BackupService for database cleanup
		$deleted = $this->backup_service->cleanup_old_backups( $ttl_days );

		// Also clean up old post meta backups for backward compatibility
		global $wpdb;
		$count         = 0;
		$ttl_timestamp = current_time( 'timestamp' ) - ( $ttl_days * DAY_IN_SECONDS );

		// Get all posts with backups
		$meta_key           = '_slos_accessibility_content_backup';
		$posts_with_backups = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- esc_sql() used for table name
				'SELECT post_id, meta_value FROM ' . esc_sql( $wpdb->postmeta ) . ' WHERE meta_key = %s',
				$meta_key
			)
		);

		foreach ( $posts_with_backups as $row ) {
			$backup = maybe_unserialize( $row->meta_value );

			if ( is_array( $backup ) && isset( $backup['timestamp'] ) ) {
				if ( $backup['timestamp'] < $ttl_timestamp ) {
					delete_post_meta( $row->post_id, $meta_key );
					++$count;
				}
			}
		}

		return $deleted + $count;
	}

	/**
	 * Rollback post content to backup
	 *
	 * @since 3.1.1
	 * @deprecated 3.2.0 Use BackupService::restore_backup() instead
	 * @param int $post_id Post ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	private function rollback_content( $post_id ) {
		_deprecated_function( __METHOD__, '3.2.0', 'BackupService::restore_backup()' );

		// Use BackupService to restore
		$result = $this->backup_service->restore_backup( $post_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_Error( 'post_not_found', 'Post not found' );
		}

		// Re-scan after rollback
		if ( isset( $this->scanner ) && method_exists( $this->scanner, 'scan' ) ) {
			$restored_post = get_post( $post_id );
			$scan_results  = $this->scanner->scan( $restored_post->post_content );
			update_post_meta( $post_id, '_slos_accessibility_scan_results', $scan_results );
			update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

			if ( method_exists( $this, 'consolidate_scan_results' ) ) {
				$this->consolidate_scan_results();
			}
		}

		// Log rollback to history (BackupService already handles backup tracking)
		global $wpdb;
		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
			$wpdb->insert(
				$table_name,
				array(
					'post_id'     => $post_id,
					'fixer_id'    => 'rollback',
					'fixed_count' => 0,
					'user_id'     => get_current_user_id(),
					'action'      => 'rollback',
					'created_at'  => current_time( 'mysql' ),
				)
			);
		}

		return true;
	}

	/**
	 * AJAX: Rollback accessibility fixes
	 *
	 * @since 3.1.1
	 */
	public function ajax_rollback_fixes() {
		// Accept either the original autofix nonce or the scanner nonce for compatibility
		if ( ! check_ajax_referer( 'slos_autofix_nonce', 'nonce', false ) && ! check_ajax_referer( 'slos_scanner_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		}

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Missing post ID' ) );
		}

		$result = $this->rollback_content( $post_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => 'Content successfully rolled back to previous version',
			)
		);
	}

	/**
	 * AJAX handler to check if backup exists for a post
	 *
	 * @since 3.1.1
	 */
	public function ajax_check_backup_exists() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => 'Invalid post ID' ) );
		}

		// Use BackupService directly
		$has_backup = $this->backup_service->has_backup( $post_id );
		$backup     = $has_backup ? $this->backup_service->get_latest_backup( $post_id ) : null;

		wp_send_json_success(
			array(
				'has_backup'  => $has_backup,
				'backup_date' => $backup ? $backup['created_at'] : '',
			)
		);
	}

	/**
	 * AJAX: Save scanner configuration from Tools page
	 *
	 * Mirrors the Accessibility Settings page but scoped to the Tools UI.
	 */
	public function ajax_save_scanner_config() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Sanitize WCAG level
		$wcag_level     = isset( $_POST['wcag_level'] ) ? sanitize_text_field( wp_unslash( $_POST['wcag_level'] ) ) : 'AA';
		$allowed_levels = array( 'A', 'AA', 'AAA' );
		if ( ! in_array( $wcag_level, $allowed_levels, true ) ) {
			$wcag_level = 'AA';
		}

		// Sanitize post types
		$scan_post_types = isset( $_POST['scan_post_types'] ) ? (array) $_POST['scan_post_types'] : array( 'post', 'page' );
		$scan_post_types = array_map( 'sanitize_key', $scan_post_types );

		// Only keep valid public post types
		$public_types    = get_post_types( array( 'public' => true ), 'names' );
		$scan_post_types = array_values( array_intersect( $scan_post_types, $public_types ) );
		if ( empty( $scan_post_types ) ) {
			$scan_post_types = array( 'post', 'page' );
		}

		// Sanitize active checkers; reuse same keys as settings page
		$raw_checkers       = isset( $_POST['active_checkers'] ) ? (array) $_POST['active_checkers'] : array();
		$raw_checkers       = array_map( 'sanitize_key', $raw_checkers );
		$available_checkers = array_keys( $this->get_check_mapping() );
		$active_checkers    = array_values( array_intersect( $raw_checkers, $available_checkers ) );
		if ( empty( $active_checkers ) ) {
			$active_checkers = $available_checkers;
		}

		update_option( 'slos_wcag_level', $wcag_level );
		update_option( 'slos_scan_post_types', $scan_post_types );
		update_option( 'slos_active_checkers', $active_checkers );

		wp_send_json_success(
			array(
				'wcag_level'      => $wcag_level,
				'scan_post_types' => $scan_post_types,
				'active_checkers' => $active_checkers,
			)
		);
	}

	/**
	 * AJAX: Schedule accessibility email reports
	 *
	 * Stores report settings and relies on cron_send_accessibility_report()
	 * to send periodic summaries.
	 */
	public function ajax_schedule_email_report() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$email     = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
		$frequency = isset( $_POST['frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['frequency'] ) ) : 'weekly';
		$template  = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'executive';

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide at least one email address.', 'shahi-legalflowsuite' ) ) );
		}

		// Validate email addresses (support multiple emails separated by commas)
		$emails       = array_map( 'trim', explode( ',', $email ) );
		$valid_emails = array();

		foreach ( $emails as $single_email ) {
			if ( is_email( $single_email ) ) {
				$valid_emails[] = $single_email;
			}
		}

		if ( empty( $valid_emails ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide at least one valid email address.', 'shahi-legalflowsuite' ) ) );
		}

		$allowed_frequencies = array( 'daily', 'weekly', 'monthly' );
		if ( ! in_array( $frequency, $allowed_frequencies, true ) ) {
			$frequency = 'weekly';
		}

		$allowed_templates = array( 'executive', 'technical', 'combined' );
		if ( ! in_array( $template, $allowed_templates, true ) ) {
			$template = 'executive';
		}

		$settings = array(
			'email'     => implode( ', ', $valid_emails ),
			'frequency' => $frequency,
			'template'  => $template,
			'last_sent' => '',
		);

		update_option( 'slos_accessibility_report_settings', $settings );

		wp_send_json_success(
			array(
				'message'  => sprintf(
					/* translators: %d: number of email addresses */
					_n(
						'Email report scheduled successfully to %d recipient.',
						'Email report scheduled successfully to %d recipients.',
						count( $valid_emails ),
						'shahi-legalflowsuite'
					),
					count( $valid_emails )
				),
				'settings' => $settings,
			)
		);
	}

	/**
	 * Cron callback: send periodic accessibility email report.
	 *
	 * Runs daily but honours the configured daily/weekly/monthly cadence.
	 */
	public function cron_send_accessibility_report() {
		$settings  = get_option( 'slos_accessibility_report_settings', array() );
		$email     = isset( $settings['email'] ) ? $settings['email'] : '';
		$frequency = isset( $settings['frequency'] ) ? $settings['frequency'] : 'weekly';
		$template  = isset( $settings['template'] ) ? $settings['template'] : 'executive';

		if ( empty( $email ) ) {
			return;
		}

		// Parse multiple emails
		$emails       = array_map( 'trim', explode( ',', $email ) );
		$valid_emails = array_filter( $emails, 'is_email' );

		if ( empty( $valid_emails ) ) {
			return;
		}

		$now       = current_time( 'timestamp' );
		$last_sent = ! empty( $settings['last_sent'] ) ? strtotime( $settings['last_sent'] ) : 0;

		// Determine interval based on frequency
		$interval_days = 7; // default weekly
		switch ( $frequency ) {
			case 'daily':
				$interval_days = 1;
				break;
			case 'monthly':
				$interval_days = 30;
				break;
		}

		// Check if enough time has passed since last report
		if ( $last_sent && ( $now - $last_sent ) < ( DAY_IN_SECONDS * $interval_days ) ) {
			return;
		}

		// Get reporter instance
		if ( ! class_exists( 'ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting\AccessibilityReporter' ) ) {
			return;
		}

		$reporter = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting\AccessibilityReporter();

		// Send to all recipients
		$sent_count = 0;
		foreach ( $valid_emails as $recipient ) {
			if ( $reporter->send_email_report( $recipient, $template ) ) {
				++$sent_count;
			}
		}

		// Update last sent time if at least one email was sent successfully
		if ( $sent_count > 0 ) {
			$settings['last_sent'] = current_time( 'mysql' );
			update_option( 'slos_accessibility_report_settings', $settings );
		}
	}

	/**
	 * AJAX: Toggle accessibility widget enabled/disabled
	 *
	 * @since 3.1.1
	 */
	public function ajax_toggle_widget() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$enabled = ! empty( $_POST['enabled'] ) && 'true' === $_POST['enabled'];
		update_option( 'slos_widget_enabled', $enabled );

		wp_send_json_success( array( 'enabled' => $enabled ) );
	}

	/**
	 * AJAX: Save comprehensive widget configuration
	 *
	 * @since 3.1.1
	 */
	public function ajax_save_widget_config() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'shahi-legalflowsuite' ) ) );
		}

		// Sanitize and validate widget enabled
		$enabled = ! empty( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

		// Sanitize and validate widget position
		$position        = isset( $_POST['position'] ) ? sanitize_text_field( $_POST['position'] ) : 'bottom-right';
		$valid_positions = array( 'top-left', 'top-right', 'bottom-left', 'bottom-right' );
		if ( ! in_array( $position, $valid_positions, true ) ) {
			$position = 'bottom-right';
		}

		// Sanitize and validate widget color
		$color        = isset( $_POST['color'] ) ? sanitize_text_field( $_POST['color'] ) : 'blue';
		$valid_colors = array( 'blue', 'green', 'purple', 'orange', 'red', 'teal' );
		if ( ! in_array( $color, $valid_colors, true ) ) {
			$color = 'blue';
		}

		// Update widget options (removed features - all features enabled by default)
		update_option( 'slos_widget_enabled', $enabled );
		update_option( 'slos_widget_position', $position );
		update_option( 'slos_widget_color', $color );

		wp_send_json_success(
			array(
				'message'  => __( 'Widget configuration saved successfully.', 'shahi-legalflowsuite' ),
				'enabled'  => $enabled,
				'position' => $position,
				'color'    => $color,
			)
		);
	}

	/**
	 * Cron job to cleanup old backups (TTL 7 days)
	 *
	 * @since 3.1.1
	 */
	public function cron_cleanup_old_backups() {
		$count = $this->cleanup_old_backups( 7 );

		// Log cleanup activity
		if ( $count > 0 ) {
			error_log( sprintf( 'SLOS: Cleaned up %d old accessibility fix backups', $count ) );
		}
	}

	/**
	 * Get accessibility statistics for Ops Dashboard
	 *
	 * Returns summary stats: total issues, issue breakdown by severity, pages scanned, last scan time.
	 *
	 * @since 3.1.1 (Phase 2.1)
	 * @return array Accessibility statistics
	 */
	public function get_ops_statistics(): array {
		$total_issues    = (int) get_option( 'slos_accessibility_issues_total', 0 );
		$critical_issues = (int) get_option( 'slos_accessibility_issues_critical', 0 );
		$warning_issues  = (int) get_option( 'slos_accessibility_issues_warning', 0 );
		$notice_issues   = (int) get_option( 'slos_accessibility_issues_notice', 0 );
		$pages_scanned   = (int) get_option( 'slos_accessibility_pages_scanned', 0 );
		$score           = (int) get_option( 'slos_accessibility_score', 0 );
		$last_scan       = get_option( 'slos_last_scan_time', '' );

		// Get scan statistics for more detailed breakdown
		$scan_stats = get_option( 'slos_scan_statistics', array() );

		// Calculate pass rate
		$pass_rate = $total_issues > 0 && ! empty( $scan_stats['total_checks'] )
			? round( ( ( $scan_stats['total_checks'] - $total_issues ) / $scan_stats['total_checks'] ) * 100, 1 )
			: 100;

		// Issue severity breakdown
		$by_severity = array(
			'critical' => $critical_issues,
			'warning'  => $warning_issues,
			'notice'   => $notice_issues,
		);

		// Calculate time since last scan
		$last_scan_timestamp = ! empty( $last_scan ) ? strtotime( $last_scan ) : 0;
		$hours_since_scan    = $last_scan_timestamp > 0
			? round( ( time() - $last_scan_timestamp ) / 3600, 1 )
			: null;

		// Determine freshness status
		$freshness = 'never';
		if ( $hours_since_scan !== null ) {
			if ( $hours_since_scan < 24 ) {
				$freshness = 'fresh';
			} elseif ( $hours_since_scan < 168 ) { // 7 days
				$freshness = 'recent';
			} else {
				$freshness = 'stale';
			}
		}

		return array(
			'total_issues'        => $total_issues,
			'critical_issues'     => $critical_issues,
			'warning_issues'      => $warning_issues,
			'notice_issues'       => $notice_issues,
			'pages_scanned'       => $pages_scanned,
			'accessibility_score' => $score,
			'pass_rate'           => (float) $pass_rate,
			'last_scan_time'      => $last_scan,
			'hours_since_scan'    => $hours_since_scan,
			'scan_freshness'      => $freshness,
			'by_severity'         => $by_severity,
		);
	}

	/**
	 * AJAX: Check Color Contrast
	 *
	 * Calculates the contrast ratio between foreground and background colors
	 * and checks against WCAG 2.2 standards.
	 *
	 * @since 3.1.2
	 * @return void Sends JSON response
	 */
	public function ajax_check_color_contrast() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$fg_color = sanitize_text_field( wp_unslash( $_POST['fg_color'] ?? '' ) );
		$bg_color = sanitize_text_field( wp_unslash( $_POST['bg_color'] ?? '' ) );

		if ( empty( $fg_color ) || empty( $bg_color ) ) {
			wp_send_json_error( __( 'Both colors are required.', 'shahi-legalflowsuite' ) );
		}

		// Calculate contrast ratio
		$ratio = $this->calculate_contrast_ratio( $fg_color, $bg_color );

		// WCAG 2.2 standards
		$wcag_aa_normal  = $ratio >= 4.5;
		$wcag_aa_large   = $ratio >= 3.0;
		$wcag_aaa_normal = $ratio >= 7.0;
		$wcag_aaa_large  = $ratio >= 4.5;

		wp_send_json_success(
			array(
				'ratio'           => round( $ratio, 2 ),
				'wcag_aa_normal'  => $wcag_aa_normal,
				'wcag_aa_large'   => $wcag_aa_large,
				'wcag_aaa_normal' => $wcag_aaa_normal,
				'wcag_aaa_large'  => $wcag_aaa_large,
				'passes_aa'       => $wcag_aa_normal,
				'recommendation'  => $this->get_contrast_recommendation( $ratio ),
			)
		);
	}

	/**
	 * Calculate contrast ratio between two colors
	 *
	 * Uses WCAG 2.2 formula: (L1 + 0.05) / (L2 + 0.05)
	 * where L1 is the lighter color and L2 is the darker color.
	 *
	 * @since 3.1.2
	 * @param string $fg Foreground color (hex format)
	 * @param string $bg Background color (hex format)
	 * @return float Contrast ratio
	 */
	private function calculate_contrast_ratio( $fg, $bg ) {
		$fg_luminance = $this->get_relative_luminance( $fg );
		$bg_luminance = $this->get_relative_luminance( $bg );

		$lighter = max( $fg_luminance, $bg_luminance );
		$darker  = min( $fg_luminance, $bg_luminance );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Get relative luminance of a color
	 *
	 * Implements the WCAG 2.2 relative luminance formula.
	 *
	 * @since 3.1.2
	 * @param string $hex Color in hex format (#RRGGBB or RRGGBB)
	 * @return float Relative luminance (0-1)
	 */
	private function get_relative_luminance( $hex ) {
		// Remove # if present
		$hex = ltrim( $hex, '#' );

		// Convert hex to RGB (0-255)
		$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

		// Apply sRGB to linear RGB conversion
		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		// Calculate relative luminance
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Get contrast recommendation based on ratio
	 *
	 * Provides human-readable guidance on WCAG compliance.
	 *
	 * @since 3.1.2
	 * @param float $ratio Contrast ratio
	 * @return string Recommendation message
	 */
	private function get_contrast_recommendation( $ratio ) {
		if ( $ratio >= 7.0 ) {
			return __( 'Excellent! Passes WCAG AAA for all text sizes.', 'shahi-legalflowsuite' );
		} elseif ( $ratio >= 4.5 ) {
			return __( 'Good! Passes WCAG AA for normal text.', 'shahi-legalflowsuite' );
		} elseif ( $ratio >= 3.0 ) {
			return __( 'Acceptable for large text only (18pt+).', 'shahi-legalflowsuite' );
		} else {
			return __( 'Fails WCAG standards. Increase contrast.', 'shahi-legalflowsuite' );
		}
	}

	/**
	 * AJAX Handler: Check Readability Score (Flesch-Kincaid)
	 *
	 * Analyzes text readability using Flesch-Kincaid Grade Level formula.
	 * WCAG recommends lower secondary education level (grade 7-9) for accessibility.
	 *
	 * @since 3.1.2
	 * @return void
	 */
	public function ajax_check_readability() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( __( 'Unauthorized', 'shahi-legalflowsuite' ) );
		}

		$text = isset( $_POST['text'] ) ? wp_unslash( $_POST['text'] ) : '';

		if ( empty( $text ) ) {
			wp_send_json_error( __( 'Text is required.', 'shahi-legalflowsuite' ) );
		}

		// Calculate readability metrics
		$words     = str_word_count( $text );
		$sentences = $this->count_sentences( $text );
		$syllables = $this->count_syllables( $text );

		if ( $words === 0 || $sentences === 0 ) {
			wp_send_json_error( __( 'Please provide valid text with complete sentences.', 'shahi-legalflowsuite' ) );
		}

		// Calculate Flesch-Kincaid Grade Level
		$grade_level = $this->calculate_flesch_kincaid( $words, $sentences, $syllables );

		// Calculate Flesch Reading Ease Score
		// Formula: 206.835 - 1.015 × (words/sentences) - 84.6 × (syllables/words)
		$asl          = $words / $sentences;
		$asw          = $syllables / $words;
		$flesch_score = 206.835 - ( 1.015 * $asl ) - ( 84.6 * $asw );
		$flesch_score = max( 0, min( 100, $flesch_score ) ); // Clamp between 0-100

		// Get interpretation
		$interpretation = $this->get_readability_interpretation( $grade_level );

		wp_send_json_success(
			array(
				'grade_level'    => $grade_level,
				'word_count'     => $words,
				'sentence_count' => $sentences,
				'syllable_count' => $syllables,
				'flesch_score'   => $flesch_score,
				'interpretation' => $interpretation['text'],
				'level_name'     => $interpretation['level'],
				'passes_wcag'    => $interpretation['passes'],
				'recommendation' => $interpretation['recommendation'],
			)
		);
	}

	/**
	 * Count sentences in text
	 *
	 * Splits text by sentence-ending punctuation (.!?) followed by
	 * uppercase letter or end of string to avoid abbreviations.
	 *
	 * @since 3.1.2
	 * @param string $text Text to analyze
	 * @return int Number of sentences
	 */
	private function count_sentences( $text ) {
		// Remove extra whitespace
		$text = trim( $text );

		// Count sentences ending with . ! ? (but not abbreviations like Dr. or Mr.)
		$sentences = preg_split( '/[.!?]+(?=\s+[A-Z]|$)/', $text, -1, PREG_SPLIT_NO_EMPTY );

		return max( 1, count( $sentences ) );
	}

	/**
	 * Count syllables in text (Flesch-Kincaid method)
	 *
	 * Uses vowel group counting algorithm consistent with standard
	 * Flesch-Kincaid readability calculations.
	 *
	 * @since 3.1.2
	 * @param string $text Text to analyze
	 * @return int Number of syllables
	 */
	private function count_syllables( $text ) {
		// Convert to lowercase and remove non-alphabetic characters
		$text = strtolower( $text );
		$text = preg_replace( '/[^a-z\s]/', ' ', $text );

		// Split into words
		$words = preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );

		$syllable_count = 0;

		foreach ( $words as $word ) {
			// Remove trailing 'e' and 'es' if not sole vowels
			$word = preg_replace( '/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word );

			// Remove leading 'y'
			$word = preg_replace( '/^y/', '', $word );

			// Count vowel groups (consecutive vowels count as one)
			preg_match_all( '/[aeiouy]{1,2}/', $word, $matches );
			$syllables = count( $matches[0] );

			// Every word has at least one syllable
			$syllable_count += max( 1, $syllables );
		}

		return max( 1, $syllable_count );
	}

	/**
	 * Calculate Flesch-Kincaid Grade Level
	 *
	 * Formula: 0.39 × (words/sentences) + 11.8 × (syllables/words) - 15.59
	 *
	 * @since 3.1.2
	 * @param int $words     Number of words
	 * @param int $sentences Number of sentences
	 * @param int $syllables Number of syllables
	 * @return float Grade level (0-18+)
	 */
	private function calculate_flesch_kincaid( $words, $sentences, $syllables ) {
		// Flesch-Kincaid Grade Level formula:
		// 0.39 × (words / sentences) + 11.8 × (syllables / words) - 15.59
		$asl = $words / $sentences; // Average Sentence Length
		$asw = $syllables / $words; // Average Syllables per Word

		$grade = ( 0.39 * $asl ) + ( 11.8 * $asw ) - 15.59;

		// Round to 1 decimal place and ensure non-negative
		return max( 0, round( $grade, 1 ) );
	}

	/**
	 * Get readability interpretation based on grade level
	 *
	 * Returns user-friendly message with WCAG compliance status.
	 * Grade 7-9 (lower secondary) recommended for broad accessibility.
	 *
	 * @since 3.1.2
	 * @param float $grade_level Flesch-Kincaid grade level
	 * @return array Interpretation details
	 */
	private function get_readability_interpretation( $grade_level ) {
		if ( $grade_level <= 6 ) {
			return array(
				'level'          => __( 'Easy (Elementary)', 'shahi-legalflowsuite' ),
				'text'           => __( 'Very easy to read. Easily understood by 11-12 year olds.', 'shahi-legalflowsuite' ),
				'passes'         => true,
				'recommendation' => __( 'Excellent! This text is highly accessible.', 'shahi-legalflowsuite' ),
			);
		} elseif ( $grade_level <= 8 ) {
			return array(
				'level'          => __( 'Standard (Middle School)', 'shahi-legalflowsuite' ),
				'text'           => __( 'Easy to read. Conversational English for consumers.', 'shahi-legalflowsuite' ),
				'passes'         => true,
				'recommendation' => __( 'Good! Meets WCAG recommendation for broad accessibility.', 'shahi-legalflowsuite' ),
			);
		} elseif ( $grade_level <= 10 ) {
			return array(
				'level'          => __( 'Fairly Difficult (Early High School)', 'shahi-legalflowsuite' ),
				'text'           => __( 'Fairly difficult to read. Requires some secondary education.', 'shahi-legalflowsuite' ),
				'passes'         => false,
				'recommendation' => __( 'Consider simplifying for better accessibility.', 'shahi-legalflowsuite' ),
			);
		} elseif ( $grade_level <= 12 ) {
			return array(
				'level'          => __( 'Difficult (High School)', 'shahi-legalflowsuite' ),
				'text'           => __( 'Difficult to read. Requires high school level education.', 'shahi-legalflowsuite' ),
				'passes'         => false,
				'recommendation' => __( 'Simplify content for better accessibility.', 'shahi-legalflowsuite' ),
			);
		} else {
			return array(
				'level'          => __( 'Very Difficult (College+)', 'shahi-legalflowsuite' ),
				'text'           => __( 'Very difficult to read. Best understood by college graduates.', 'shahi-legalflowsuite' ),
				'passes'         => false,
				'recommendation' => __( 'Strongly recommend simplifying for accessibility.', 'shahi-legalflowsuite' ),
			);
		}
	}

	/**
	 * AJAX Handler: Check Link Text Validator
	 *
	 * Validates link text against generic phrases and provides suggestions.
	 * WCAG 2.4.4 requires link purpose identifiable from link text alone.
	 *
	 * @since 3.1.2
	 * @return void
	 */
	public function ajax_check_link_text() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! $this->user_can_manage_accessibility() ) {
			wp_send_json_error( __( 'Unauthorized', 'shahi-legalflowsuite' ) );
		}

		$link_text = isset( $_POST['link_text'] ) ? sanitize_text_field( wp_unslash( $_POST['link_text'] ) ) : '';

		if ( empty( $link_text ) ) {
			wp_send_json_error( __( 'Link text is required.', 'shahi-legalflowsuite' ) );
		}

		// Check against generic patterns
		$is_generic    = $this->is_generic_link_text( $link_text );
		$pattern_match = $this->get_matched_generic_pattern( $link_text );
		$suggestions   = $this->get_link_text_suggestions( $link_text, $pattern_match );

		wp_send_json_success(
			array(
				'link_text'      => $link_text,
				'is_descriptive' => ! $is_generic,
				'is_generic'     => $is_generic,
				'pattern_match'  => $pattern_match,
				'suggestions'    => $suggestions,
				'passes_wcag'    => ! $is_generic,
			)
		);
	}

	/**
	 * Check if link text is generic
	 *
	 * Tests against common non-descriptive link text patterns.
	 *
	 * @since 3.1.2
	 * @param string $link_text Link text to check
	 * @return bool True if generic
	 */
	private function is_generic_link_text( $link_text ) {
		$text = strtolower( trim( $link_text ) );

		// Too short
		if ( strlen( $text ) < 4 ) {
			return true;
		}

		// Check against generic patterns
		$generic_patterns = $this->get_generic_link_patterns();

		foreach ( $generic_patterns as $pattern ) {
			if ( $text === $pattern || strpos( $text, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get matched generic pattern
	 *
	 * Returns the specific generic pattern that matched.
	 *
	 * @since 3.1.2
	 * @param string $link_text Link text to check
	 * @return string|null Matched pattern or null
	 */
	private function get_matched_generic_pattern( $link_text ) {
		$text = strtolower( trim( $link_text ) );

		if ( strlen( $text ) < 4 ) {
			return 'too_short';
		}

		$generic_patterns = $this->get_generic_link_patterns();

		foreach ( $generic_patterns as $pattern ) {
			if ( $text === $pattern || strpos( $text, $pattern ) !== false ) {
				return $pattern;
			}
		}

		return null;
	}

	/**
	 * Get list of generic link text patterns
	 *
	 * Common non-descriptive link phrases that fail WCAG 2.4.4.
	 *
	 * @since 3.1.2
	 * @return array Generic patterns
	 */
	private function get_generic_link_patterns() {
		return array(
			'click here',
			'click',
			'here',
			'read more',
			'more',
			'learn more',
			'link',
			'this link',
			'this page',
			'this website',
			'go',
			'go here',
			'see more',
			'view more',
			'details',
			'more details',
			'info',
			'more info',
			'information',
			'continue',
			'next',
			'download',
			'file',
			'page',
			'website',
			'site',
		);
	}

	/**
	 * Get link text suggestions
	 *
	 * Provides context-aware recommendations for better link text.
	 *
	 * @since 3.1.2
	 * @param string      $link_text     Original link text
	 * @param string|null $pattern_match Matched pattern
	 * @return array Suggestions
	 */
	private function get_link_text_suggestions( $link_text, $pattern_match ) {
		$suggestions = array();

		if ( $pattern_match === 'too_short' ) {
			$suggestions[] = __( 'Use at least 4 characters to describe the link destination.', 'shahi-legalflowsuite' );
			$suggestions[] = __( 'Example: Instead of "Go", use "Go to contact page"', 'shahi-legalflowsuite' );
		} elseif ( in_array( $pattern_match, array( 'click here', 'click' ), true ) ) {
			$suggestions[] = __( 'Replace "click here" with a description of the destination.', 'shahi-legalflowsuite' );
			$suggestions[] = __( 'Example: "View our accessibility policy" or "Download annual report"', 'shahi-legalflowsuite' );
		} elseif ( in_array( $pattern_match, array( 'read more', 'more', 'learn more' ), true ) ) {
			$suggestions[] = __( 'Include what the user will read more about.', 'shahi-legalflowsuite' );
			$suggestions[] = __( 'Example: "Read more about our services" or "Learn more about WCAG 2.2"', 'shahi-legalflowsuite' );
		} elseif ( in_array( $pattern_match, array( 'here', 'link', 'this link' ), true ) ) {
			$suggestions[] = __( 'Describe the link destination instead of using directional words.', 'shahi-legalflowsuite' );
			$suggestions[] = __( 'Example: "Contact support team" or "Product documentation"', 'shahi-legalflowsuite' );
		} elseif ( in_array( $pattern_match, array( 'details', 'info', 'information' ), true ) ) {
			$suggestions[] = __( 'Specify what details or information the link provides.', 'shahi-legalflowsuite' );
			$suggestions[] = __( 'Example: "Pricing details" or "Technical specifications"', 'shahi-legalflowsuite' );
		} else {
			$suggestions[] = __( 'Make link text descriptive of the destination or action.', 'shahi-legalflowsuite' );
			$suggestions[] = __( 'Link text should make sense when read out of context.', 'shahi-legalflowsuite' );
		}

		return $suggestions;
	}
}
