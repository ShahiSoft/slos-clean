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

use ShahiLegalFlowSuite\Modules\Module;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;
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
		$this->scanner = new ScannerEngine();
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
		add_action( 'wp_ajax_slos_fix_single_issue', array( $this, 'ajax_fix_single_issue' ) );
		add_action( 'wp_ajax_slos_fix_all_issues', array( $this, 'ajax_fix_all_issues' ) );
		add_action( 'wp_ajax_slos_toggle_autofix', array( $this, 'ajax_toggle_autofix' ) );
		add_action( 'wp_ajax_slos_get_page_issues', array( $this, 'ajax_get_page_issues' ) );
		add_action( 'wp_ajax_slos_get_page_fixable_issues', array( $this, 'ajax_get_page_fixable_issues' ) );
		add_action( 'wp_ajax_slos_run_full_scan', array( $this, 'ajax_run_full_scan' ) );
		add_action( 'wp_ajax_slos_consolidate_scan_results', array( $this, 'ajax_consolidate_scan_results' ) );
		add_action( 'wp_ajax_slos_audit_media_library', array( $this, 'ajax_audit_media_library' ) );
		add_action( 'wp_ajax_slos_publish_statement', array( $this, 'ajax_publish_statement' ) );
		add_action( 'wp_ajax_slos_autofix_single', array( $this, 'ajax_autofix_single_fixer' ) );
		add_action( 'wp_ajax_slos_get_detailed_scan_report', array( $this, 'ajax_get_detailed_scan_report' ) );
		add_action( 'wp_ajax_slos_rollback_fixes', array( $this, 'ajax_rollback_fixes' ) );
		add_action( 'wp_ajax_slos_check_backup_exists', array( $this, 'ajax_check_backup_exists' ) );

		// Schedule cleanup cron job if not already scheduled
		if ( ! wp_next_scheduled( 'slos_cleanup_old_backups' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_cleanup_old_backups' );
		}
		add_action( 'slos_cleanup_old_backups', array( $this, 'cron_cleanup_old_backups' ) );
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

		$text = "ACCESSIBILITY STATEMENT\n\n";
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
		global $wpdb;
		$results = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
             WHERE post_type IN ('post', 'page') 
             AND post_status = 'publish' 
             ORDER BY post_title ASC",
			ARRAY_A
		);

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

		$post_id = intval( $_POST['post_id'] );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( 'Post not found' );
		}

		$result = $this->run_scan_for_post( $post_id, $post );

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
		if ( $existing ) {
			wp_send_json_success(
				array(
					'page_url'  => get_permalink( $existing->ID ),
					'edit_url'  => get_edit_post_link( $existing->ID, 'raw' ),
					'message'   => __( 'Accessibility Statement page already exists.', 'shahi-legalflowsuite' ),
					'exists'    => true,
				)
			);
			return;
		}

		// Generate statement content
		$org_name       = sanitize_text_field( wp_unslash( $_POST['org_name'] ?? '' ) );
		$contact_email  = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
		$wcag_target    = sanitize_text_field( wp_unslash( $_POST['wcag_target'] ?? 'WCAG 2.1 Level AA' ) );
		$statement_date = sanitize_text_field( wp_unslash( $_POST['statement_date'] ?? '' ) );
		$commitment     = sanitize_textarea_field( wp_unslash( $_POST['commitment'] ?? '' ) );

		if ( empty( $org_name ) ) {
			wp_send_json_error( __( 'Organization name is required.', 'shahi-legalflowsuite' ) );
		}

		// Build statement HTML
		$statement = $this->build_accessibility_statement(
			$org_name,
			$contact_email,
			$wcag_target,
			$statement_date,
			$commitment
		);

		// Create the page
		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Accessibility Statement', 'shahi-legalflowsuite' ),
				'post_name'    => 'accessibility-statement',
				'post_content' => $statement,
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
				'page_id'  => $page_id,
				'page_url' => get_permalink( $page_id ),
				'edit_url' => get_edit_post_link( $page_id, 'raw' ),
				'message'  => __( 'Accessibility Statement page created successfully.', 'shahi-legalflowsuite' ),
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

		$html = '<!-- wp:heading {"level":1} -->' . "\n";
		$html .= '<h1 class="wp-block-heading">' . __( 'Accessibility Statement', 'shahi-legalflowsuite' ) . '</h1>' . "\n";
		$html .= '<!-- /wp:heading -->' . "\n\n";

		$html .= '<!-- wp:paragraph -->' . "\n";
		$html .= '<p>' . sprintf(
			/* translators: 1: Organization name, 2: Site URL */
			__( '%1$s is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.', 'shahi-legalflowsuite' ),
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

		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
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

		return array(
			'post_id'        => $post_id,
			'title'          => $post->post_title,
			'edit_link'      => get_edit_post_link( $post_id ),
			'issues_count'   => $issues_count,
			'critical_count' => $critical_count,
			'issue_types'    => array_unique( $issue_types ), // Only unique types
			'scan_date'      => current_time( 'mysql' ),
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

		$consolidated    = array();
		$total_issues    = 0;
		$total_critical  = 0;
		$pages_scanned   = 0;
		$issues_by_type  = array(); // Track issues by checker type

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
			'id'             => $scan_id,
			'date'           => current_time( 'mysql' ),
			'timestamp'      => time(),
			'score'          => intval( $score ),
			'issues'         => intval( $total_issues ),
			'critical'       => intval( $total_critical ),
			'pages_scanned'  => intval( $pages_scanned ),
			'wcag_level'     => get_option( 'slos_wcag_level', 'AA' ),
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
			'missing-alt-text'    => MissingAltTextCheck::class,
			'empty-alt-text'      => EmptyAltTextCheck::class,
			'missing-h1'          => MissingH1Check::class,
			'skipped-heading'     => SkippedHeadingLevelCheck::class,
			'empty-link'          => EmptyLinkCheck::class,
			'generic-link'        => GenericLinkTextCheck::class,
			'missing-label'       => MissingFormLabelCheck::class,
			'redundant-alt'       => RedundantAltTextCheck::class,
			'empty-heading'       => EmptyHeadingCheck::class,
			'new-window'          => NewWindowLinkCheck::class,
			'positive-tabindex'   => PositiveTabIndexCheck::class,
			'image-map'           => ImageMapAltCheck::class,
			'iframe-title'        => IframeTitleCheck::class,
			'button-label'        => ButtonLabelCheck::class,
			'table-header'        => TableHeaderCheck::class,
			'alt-quality'         => AltTextQualityCheck::class,
			'decorative-image'    => DecorativeImageCheck::class,
			'complex-image'       => ComplexImageCheck::class,
			'svg-access'          => SvgAccessibilityCheck::class,
			'bg-image'            => BackgroundImageCheck::class,
			'logo-image'          => LogoImageCheck::class,
			'multiple-h1'         => MultipleH1Check::class,
			'heading-visual'      => HeadingVisualCheck::class,
			'heading-length'      => HeadingLengthCheck::class,
			'heading-unique'      => HeadingUniquenessCheck::class,
			'heading-nesting'     => HeadingNestingCheck::class,
			'fieldset-legend'     => FieldsetLegendCheck::class,
			'autocomplete'        => AutocompleteCheck::class,
			'input-type'          => InputTypeCheck::class,
			'placeholder-label'   => PlaceholderLabelCheck::class,
			'custom-control'      => CustomControlCheck::class,
			'orphaned-label'      => OrphanedLabelCheck::class,
			'required-attr'       => RequiredAttributeCheck::class,
			'error-message'       => ErrorMessageCheck::class,
			'form-aria'           => FormAriaCheck::class,
			'link-dest'           => LinkDestinationCheck::class,
			'skip-link'           => SkipLinkCheck::class,
			'download-link'       => DownloadLinkCheck::class,
			'external-link'       => ExternalLinkCheck::class,
			'contrast'            => TextColorContrastCheck::class,
			'focus-indicator'     => FocusIndicatorCheck::class,
			'color-reliance'      => ColorRelianceCheck::class,
			'complex-contrast'    => ComplexContrastCheck::class,
			'keyboard-trap'       => KeyboardTrapCheck::class,
			'focus-order'         => FocusOrderCheck::class,
			'interactive-element' => InteractiveElementCheck::class,
			'modal-access'        => ModalAccessibilityCheck::class,
			'widget-keyboard'     => CustomWidgetKeyboardCheck::class,
			'aria-role'           => AriaRoleCheck::class,
			'aria-attr'           => AriaAttributeCheck::class,
			'landmark-role'       => LandmarkRoleCheck::class,
			'redundant-aria'      => RedundantAriaCheck::class,
			'hidden-content'      => HiddenContentCheck::class,
			'semantic-html'       => SemanticHtmlCheck::class,
			'live-region'         => LiveRegionCheck::class,
			'aria-state'          => AriaStateCheck::class,
			'invalid-aria'        => InvalidAriaCombinationCheck::class,
			'page-structure'      => PageStructureCheck::class,
			'video-access'        => VideoAccessibilityCheck::class,
			'audio-access'        => AudioAccessibilityCheck::class,
			'media-alt'           => MediaAlternativeCheck::class,
			'table-caption'       => TableCaptionCheck::class,
			'complex-table'       => ComplexTableCheck::class,
			'layout-table'        => LayoutTableCheck::class,
			'empty-cell'          => EmptyTableCellCheck::class,
			'viewport'            => ViewportCheck::class,
			'touch-target'        => TouchTargetCheck::class,
			'touch-gesture'       => TouchGestureCheck::class,
			'language-change'     => LanguageChangeCheck::class,
			'animation-pause'     => AnimationPauseCheck::class,
			'timing-control'      => TimingControlCheck::class,
			'status-message'      => StatusMessageCheck::class,
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

		// Log for debugging (can be removed in production)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'SLOS Accessibility Scanner: Registered ' . count( $active_checkers ) . ' active checks out of ' . count( $check_mapping ) . ' available.' );
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
				echo '<li style="margin-bottom: 10px; border-left: 4px solid ' . $color . '; padding-left: 10px;">';
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
			$fixed_issues[] = array(
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
		$new_results    = get_option( 'slos_last_scan_results', array() );
		$new_page_data  = null;
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
			'success'              => true,
			'message'              => $this->build_fix_message( $fixed_count_total, count( $failed_issues ), count( $manual_fix_required ) ),
			'fixed_count'          => count( $fixed_issues ),
			'failed_count'         => count( $failed_issues ),
			'manual_required'      => count( $manual_fix_required ),
			'total_issues_fixed'   => $fixed_count_total,
			'content_changed'      => $content_changed,
			'new_issues_count'     => $new_issues_count,
			'new_score'            => $new_score,
			'fixed_details'        => $fixed_issues,
			'failed_details'       => $failed_issues,
			'manual_fix_guidance'  => $this->get_manual_fix_guidance( $manual_fix_required ),
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
			return sprintf( _n( '%d issue was automatically fixed!', '%d issues were automatically fixed!', $fixed, 'shahi-legalflowsuite' ), $fixed );
		} elseif ( $fixed > 0 && ( $failed > 0 || $manual > 0 ) ) {
			return sprintf( __( '%d issues fixed. %d issues require manual attention.', 'shahi-legalflowsuite' ), $fixed, $failed + $manual );
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
	 * Returns only the fixers that have issues on this page
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

		if ( empty( $scan_results ) ) {
			wp_send_json_success(
				array(
					'fixers' => array(),
					'message' => 'No scan results found. Please scan this page first.',
				)
			);
			return;
		}

		// Initialize fixer registry
		if ( ! class_exists( '\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Fixes\\FixerRegistry' ) ) {
			wp_send_json_error( array( 'message' => 'Fixer system not available' ) );
		}

		\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();

		// Map check IDs to fixer IDs (some checks have corresponding fixers)
		$check_to_fixer_map = $this->get_check_to_fixer_mapping();

		// Collect fixers that have issues (for UI highlighting)
		$fixers_with_issues = array();
		$seen_fixers        = array();

		foreach ( $scan_results as $check_id => $check_result ) {
			// Check if this check has fixable issues
			if ( empty( $check_result['issues'] ) ) {
				continue;
			}

			// Check if there's a corresponding fixer for this check
			if ( isset( $check_to_fixer_map[ $check_id ] ) ) {
				$fixer_id = $check_to_fixer_map[ $check_id ];

				// Avoid duplicates
				if ( in_array( $fixer_id, $seen_fixers, true ) ) {
					continue;
				}

				$fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $fixer_id );

				if ( $fixer ) {
					$seen_fixers[]        = $fixer_id;
					$fixers_with_issues[] = array(
						'id'          => $fixer->get_id(),
						'name'        => $this->get_fixer_name_from_id( $fixer->get_id() ),
						'description' => $fixer->get_description(),
						'status'      => 'pending',
						'count'       => 0,
						'message'     => '',
					);
				}
			}
		}

		// Always instruct frontend to load ALL registered fixers, but also return the ones with detected issues
		wp_send_json_success(
			array(
				'fixers'         => $fixers_with_issues, // for highlighting/ordering
				'use_all_fixers' => true,                 // tell UI to run all available fixers
				'issue_count'    => count( $fixers_with_issues ),
				'message'        => count( $fixers_with_issues ) > 0
					? sprintf( 'Found %d fixer(s) with issues', count( $fixers_with_issues ) )
					: 'No fixable issues found',
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
		// Return EXACT mapping based on what scan results use as keys
		// and what FixerRegistry actually has registered
		return array(
			// Image-related checks (scan result keys => fixer IDs)
			'missing-alt-text'    => 'missing-alt-text',
			'empty-alt-text'      => 'empty-alt-text',
			'redundant-alt'       => 'redundant-alt',
			'alt-quality'         => 'alt-quality',
			'decorative-image'    => 'decorative-image',
			'complex-image'       => 'complex-image',
			'svg-access'          => 'svg-access',
			'bg-image'            => 'bg-image',
			'logo-image'          => 'logo-image',
			'image-map'           => 'image-map',

			// Heading-related checks
			'missing-h1'          => 'missing-h1',
			'multiple-h1'         => 'multiple-h1',
			'skipped-heading'     => 'skipped-heading',
			'empty-heading'       => 'empty-heading',
			'heading-length'      => 'heading-length',
			'heading-unique'      => 'heading-unique',
			'heading-visual'      => 'heading-visual',
			'heading-nesting'     => 'heading-nesting',

			// Form-related checks
			'missing-label'       => 'missing-label',
			'placeholder-label'   => 'placeholder-label',
			'orphaned-label'      => 'orphaned-label',
			'fieldset-legend'     => 'fieldset-legend',
			'autocomplete'        => 'autocomplete',
			'input-type'          => 'input-type',
			'required-attr'       => 'required-attr',
			'error-message'       => 'error-message',
			'form-aria'           => 'form-aria',
			'custom-control'      => 'custom-control',

			// Link-related checks
			'empty-link'          => 'empty-link',
			'generic-link'        => 'generic-link',
			'new-window'          => 'new-window',
			'download-link'       => 'download-link',
			'external-link'       => 'external-link',
			'link-dest'           => 'link-dest',
			'skip-link'           => 'skip-link',

			// ARIA-related checks
			'aria-role'           => 'aria-role',
			'aria-attr'           => 'aria-attr',
			'aria-state'          => 'aria-state',
			'redundant-aria'      => 'redundant-aria',
			'invalid-aria'        => 'invalid-aria',
			'landmark-role'       => 'landmark-role',
			'hidden-content'      => 'hidden-content',
			'live-region'         => 'live-region',

			// Table-related checks
			'table-header'        => 'table-header',
			'table-caption'       => 'table-caption',
			'complex-table'       => 'complex-table',
			'layout-table'        => 'layout-table',
			'empty-cell'          => 'empty-cell',

			// Keyboard & Interaction
			'positive-tabindex'   => 'positive-tabindex',
			'keyboard-trap'       => 'keyboard-trap',
			'focus-order'         => 'focus-order',
			'focus-indicator'     => 'focus-indicator',
			'interactive-element' => 'interactive-element',
			'modal-access'        => 'modal-access',
			'widget-keyboard'     => 'widget-keyboard',

			// Color & Contrast
			'contrast'            => 'contrast',
			'color-reliance'      => 'color-reliance',
			'complex-contrast'    => 'complex-contrast',

			// Mobile & Viewport
			'touch-target'        => 'touch-target',
			'touch-gesture'       => 'touch-gesture',
			'viewport'            => 'viewport',

			// Semantic & Structure
			'semantic-html'       => 'semantic-html',
			'page-structure'      => 'page-structure',

			// Media & Other
			'button-label'        => 'button-label',
			'iframe-title'        => 'iframe-title',
			'video-access'        => 'video-access',
			'audio-access'        => 'audio-access',
			'media-alt'           => 'media-alt',

			// Advanced
			'language-change'     => 'language-change',
			'animation-pause'     => 'animation-pause',
			'timing-control'      => 'timing-control',
			'status-message'      => 'status-message',
			'error-identification' => 'error-identification',

			// Handle scan results that might use longer forms
			'video-accessibility' => 'video-access',
			'audio-accessibility' => 'audio-access',
			'media-alternative'   => 'media-alt',
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

		// Try new FixEngine first (v3.3.0+)
		$fix_engine_bootstrap = SHAHI_LEGALFLOWSUITE_PLUGIN_PATH . 'includes/Modules/AccessibilityScanner/FixEngine/Bootstrap.php';
		
		if ( file_exists( $fix_engine_bootstrap ) ) {
			require_once $fix_engine_bootstrap;
			
			if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap' ) ) {
				$engine = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap::get_engine();
				$engine->initialize();
				
				$fixer = $engine->get_fixer( $fixer_id );
				
				if ( $fixer ) {
					$this->handle_fix_engine_request( $engine, $fixer, $fixer_id, $page_id, $content );
					return;
				}
			}
		}

		// Fallback to old FixerRegistry
		if ( ! class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
			wp_send_json(
				array(
					'skipped' => true,
					'message' => 'Fixer system not available',
				)
			);
			return;
		}

		\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();

		// Get the fixer
		$fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $fixer_id );

		if ( ! $fixer ) {
			wp_send_json(
				array(
					'skipped' => true,
					'message' => 'Fixer not found',
				)
			);
			return;
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

			// Run the fixer
			$result = $fixer->fix( $content );

			// Check result
			if ( is_array( $result ) && isset( $result['content'] ) ) {
				// Support both fixed_count and fixes_applied for backward compatibility
				$fixed_count    = $result['fixed_count'] ?? $result['fixes_applied'] ?? 0;
				$fixed_content  = $result['content'];
				$content_changed = ( $fixed_content !== $content );

				// Guardrail: Ensure we don't mark success if no fixes and no content change
				if ( $fixed_count === 0 && ! $content_changed ) {
					wp_send_json(
						array(
							'skipped' => true,
							'message' => 'No issues found',
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
						$updated_post = get_post( $page_id );
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
			} else {
				// Fixer returned unexpected format - guardrail
				wp_send_json(
					array(
						'skipped' => true,
						'message' => 'No fixes applied',
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
		$scan_date    = get_post_meta( $post_id, '_slos_last_scan_date', true );
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
					$issue_count  = count( $result['issues'] );
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
			'missing-alt-text'      => 'Add descriptive alt attributes to all images. Describe what the image shows, not just "image" or "photo".',
			'button-label'          => 'Ensure all buttons have visible text or aria-label attributes that describe their purpose.',
			'table-header'          => 'Add <th> elements with scope attributes to table rows to identify headers.',
			'video-accessibility'   => 'Provide captions for videos using <track> elements with WebVTT files.',
			'media-alternative'     => 'Add text transcripts for audio content and alternative descriptions for media.',
			'form-label'            => 'Associate every form input with a <label> element using the for/id attributes.',
			'heading-structure'     => 'Use heading levels (h1-h6) in proper order without skipping levels.',
			'link-text'             => 'Use descriptive link text instead of "click here" or "read more". Describe the destination.',
			'color-contrast'        => 'Ensure text has sufficient contrast ratio: 4.5:1 for normal text, 3:1 for large text.',
			'aria-labels'           => 'Add appropriate ARIA labels and roles to custom interactive elements.',
			'keyboard-access'       => 'Ensure all interactive elements are keyboard accessible using Tab and Enter keys.',
			'table-caption'         => 'Add <caption> elements to tables to describe their purpose.',
			'empty-heading'         => 'Remove empty headings or add meaningful content to them.',
			'empty-link'            => 'Add descriptive text to links or remove empty link elements.',
			'skip-link'             => 'Add a "Skip to main content" link at the top of the page for keyboard users.',
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
			$fixed_count    = $result->get_fixes_applied();
			$fixed_content  = $result->get_fixed_content();
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
					$updated_post = get_post( $page_id );
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
	 * @param int    $post_id Post ID
	 * @param string $content Original content
	 * @return bool True on success, false on failure
	 */
	private function save_content_backup( $post_id, $content ) {
		$backup_key = '_slos_accessibility_content_backup';
		
		// Save backup with timestamp
		$backup = array(
			'content'    => $content,
			'timestamp'  => current_time( 'timestamp' ),
			'created_at' => current_time( 'mysql' ),
		);

		return update_post_meta( $post_id, $backup_key, $backup );
	}

	/**
	 * Get content backup for rollback
	 *
	 * @since 3.1.1
	 * @param int $post_id Post ID
	 * @return array|false Backup data or false if not found
	 */
	private function get_content_backup( $post_id ) {
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
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
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
	 * @param int $ttl_days Number of days to keep backups (default 7)
	 * @return int Number of backups cleaned up
	 */
	private function cleanup_old_backups( $ttl_days = 7 ) {
		global $wpdb;

		$count = 0;
		$ttl_timestamp = current_time( 'timestamp' ) - ( $ttl_days * DAY_IN_SECONDS );

		// Get all posts with backups
		$meta_key = '_slos_accessibility_content_backup';
		$posts_with_backups = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
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

		return $count;
	}

	/**
	 * Rollback post content to backup
	 *
	 * @since 3.1.1
	 * @param int $post_id Post ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	private function rollback_content( $post_id ) {
		$backup = $this->get_content_backup( $post_id );

		if ( ! $backup ) {
			return new \WP_Error( 'no_backup', 'No backup found for this post' );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_Error( 'post_not_found', 'Post not found' );
		}

		// Restore content
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $backup['content'],
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Re-scan after rollback
		if ( isset( $this->scanner ) && method_exists( $this->scanner, 'scan' ) ) {
			$restored_post = get_post( $post_id );
			$scan_results = $this->scanner->scan( $restored_post->post_content );
			update_post_meta( $post_id, '_slos_accessibility_scan_results', $scan_results );
			update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );

			if ( method_exists( $this, 'consolidate_scan_results' ) ) {
				$this->consolidate_scan_results();
			}
		}

		// Delete backup after successful rollback
		$this->delete_content_backup( $post_id );

		// Log rollback to history
		global $wpdb;
		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
			$wpdb->insert(
				$table_name,
				array(
					'post_id'    => $post_id,
					'fixer_id'   => 'rollback',
					'fixed_count' => 0,
					'user_id'    => get_current_user_id(),
					'action'     => 'rollback',
					'created_at' => current_time( 'mysql' ),
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
		check_ajax_referer( 'slos_autofix_nonce', 'nonce' );

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

		$backup = $this->get_content_backup( $post_id );

		wp_send_json_success(
			array(
				'has_backup' => ! empty( $backup ),
				'backup_date' => ! empty( $backup ) ? ( $backup['date'] ?? '' ) : '',
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
		$hours_since_scan = $last_scan_timestamp > 0 
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
			'total_issues'    => $total_issues,
			'critical_issues' => $critical_issues,
			'warning_issues'  => $warning_issues,
			'notice_issues'   => $notice_issues,
			'pages_scanned'   => $pages_scanned,
			'accessibility_score' => $score,
			'pass_rate'       => (float) $pass_rate,
			'last_scan_time'  => $last_scan,
			'hours_since_scan' => $hours_since_scan,
			'scan_freshness'  => $freshness,
			'by_severity'     => $by_severity,
		);
	}
}

