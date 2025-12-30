<?php
/**
 * Consent UX Checker
 *
 * Mini-scanner for consent and legal pages that runs a subset of accessibility checks
 * focused on UX elements critical for informed consent: focus order, ARIA labels,
 * color contrast, and heading structure.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\FocusOrderCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TextColorContrastCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaAttributeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaRoleCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingH1Check;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\SkippedHeadingLevelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MultipleH1Check;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingNestingCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingFormLabelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ButtonLabelCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\PositiveTabIndexCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\InteractiveElementCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent UX Checker Class
 *
 * Performs focused accessibility checks on consent and legal pages.
 *
 * @since 3.1.1
 */
class ConsentUxChecker {

	/**
	 * Issue counter
	 *
	 * @var array
	 */
	private $issue_counts = array(
		'critical' => 0,
		'warning'  => 0,
		'notice'   => 0,
	);

	/**
	 * All detected issues
	 *
	 * @var array
	 */
	private $all_issues = array();

	/**
	 * Scan results cache key
	 *
	 * @var string
	 */
	const CACHE_KEY = 'slos_consent_ux_scan_results';

	/**
	 * Cache expiration (24 hours)
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = 86400;

	/**
	 * Get consent and legal pages to scan
	 *
	 * @since 3.1.1
	 * @return array Array of page IDs
	 */
	public function get_consent_pages(): array {
		$pages = array();

		// Get legal pages from option
		$legal_pages = get_option( 'slos_legal_pages', array() );

		// Add consent/legal page types
		$page_types = array(
			'privacy-policy',
			'cookie-policy',
			'accessibility-statement',
			'terms-of-service',
			'consent-preferences',
		);

		foreach ( $page_types as $type ) {
			if ( isset( $legal_pages[ $type ]['page_id'] ) && ! empty( $legal_pages[ $type ]['page_id'] ) ) {
				$pages[] = absint( $legal_pages[ $type ]['page_id'] );
			}
		}

		// Also check for WordPress privacy policy page
		$wp_privacy_page = get_option( 'wp_page_for_privacy_policy' );
		if ( $wp_privacy_page && ! in_array( $wp_privacy_page, $pages, true ) ) {
			$pages[] = absint( $wp_privacy_page );
		}

		// Allow filtering
		$pages = apply_filters( 'slos_consent_ux_pages', $pages );

		// Remove duplicates and ensure valid page IDs
		$pages = array_unique( array_filter( array_map( 'absint', $pages ) ) );

		return $pages;
	}

	/**
	 * Run mini-scanner on consent/legal pages
	 *
	 * @since 3.1.1
	 * @param bool $force_refresh Force refresh instead of using cache
	 * @return array Scan results
	 */
	public function scan( bool $force_refresh = false ): array {
		// Check cache first
		if ( ! $force_refresh ) {
			$cached = get_transient( self::CACHE_KEY );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Reset counters
		$this->issue_counts = array(
			'critical' => 0,
			'warning'  => 0,
			'notice'   => 0,
		);
		$this->all_issues = array();

		// Get pages to scan
		$pages = $this->get_consent_pages();

		if ( empty( $pages ) ) {
			$results = $this->build_results( array(), 'No consent or legal pages configured' );
			set_transient( self::CACHE_KEY, $results, self::CACHE_EXPIRATION );
			return $results;
		}

		// Scan each page
		$page_results = array();
		foreach ( $pages as $page_id ) {
			$page_results[ $page_id ] = $this->scan_page( $page_id );
		}

		// Build final results
		$results = $this->build_results( $page_results );

		// Cache results
		set_transient( self::CACHE_KEY, $results, self::CACHE_EXPIRATION );

		// Fire action for other modules to react
		do_action( 'slos_consent_ux_scan_completed', $results );

		return $results;
	}

	/**
	 * Scan a single page
	 *
	 * @since 3.1.1
	 * @param int $page_id Page ID to scan
	 * @return array Page scan results
	 */
	private function scan_page( int $page_id ): array {
		$page = get_post( $page_id );

		if ( ! $page ) {
			return array(
				'page_id'    => $page_id,
				'page_title' => 'Unknown Page',
				'page_url'   => '',
				'status'     => 'error',
				'message'    => 'Page not found',
				'issues'     => array(),
			);
		}

		$issues = array();

		// Get page HTML
		$html = $this->get_page_html( $page );

		if ( empty( $html ) ) {
			return array(
				'page_id'    => $page_id,
				'page_title' => $page->post_title,
				'page_url'   => get_permalink( $page_id ),
				'status'     => 'error',
				'message'    => 'Unable to retrieve page content',
				'issues'     => array(),
			);
		}

		// Run checks
		$issues = array_merge( $issues, $this->check_focus_order( $html, $page_id ) );
		$issues = array_merge( $issues, $this->check_aria_attributes( $html, $page_id ) );
		$issues = array_merge( $issues, $this->check_contrast( $html, $page_id ) );
		$issues = array_merge( $issues, $this->check_heading_structure( $html, $page_id ) );

		// Count issues by severity
		foreach ( $issues as $issue ) {
			$severity = $issue['severity'] ?? 'notice';
			if ( isset( $this->issue_counts[ $severity ] ) ) {
				$this->issue_counts[ $severity ]++;
			}
			$this->all_issues[] = $issue;
		}

		return array(
			'page_id'    => $page_id,
			'page_title' => $page->post_title,
			'page_url'   => get_permalink( $page_id ),
			'status'     => 'success',
			'message'    => sprintf( 'Found %d issues', count( $issues ) ),
			'issues'     => $issues,
		);
	}

	/**
	 * Get page HTML for scanning
	 *
	 * @since 3.1.1
	 * @param \WP_Post $page Page object
	 * @return string Page HTML
	 */
	private function get_page_html( $page ): string {
		// Apply content filters (shortcodes, blocks, etc.)
		$content = apply_filters( 'the_content', $page->post_content );

		// Wrap in basic HTML structure for DOM parsing
		$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>';
		$html .= '<main id="main-content">';
		$html .= '<h1>' . esc_html( $page->post_title ) . '</h1>';
		$html .= $content;
		$html .= '</main></body></html>';

		return $html;
	}

	/**
	 * Check focus order
	 *
	 * @since 3.1.1
	 * @param string $html Page HTML
	 * @param int    $page_id Page ID
	 * @return array Issues found
	 */
	private function check_focus_order( string $html, int $page_id ): array {
		$issues = array();

		// Use existing checkers
		$focus_order_checker = new FocusOrderCheck();
		$positive_tab_checker = new PositiveTabIndexCheck();
		$interactive_checker = new InteractiveElementCheck();

		// Parse HTML
		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		// Check for positive tabindex (anti-pattern)
		$xpath = new \DOMXPath( $dom );
		$positive_tabindex = $xpath->query( '//*[@tabindex > 0]' );
		if ( $positive_tabindex->length > 0 ) {
			$issues[] = array(
				'type'        => 'positive_tabindex',
				'severity'    => 'warning',
				'message'     => sprintf( 'Found %d elements with positive tabindex values (anti-pattern)', $positive_tabindex->length ),
				'description' => 'Positive tabindex values disrupt natural focus order. Remove tabindex or use tabindex="0" for keyboard accessibility.',
				'wcag'        => '2.4.3',
				'page_id'     => $page_id,
				'count'       => $positive_tabindex->length,
			);
		}

		// Check for interactive elements without proper keyboard access
		$interactive_elements = $xpath->query( '//div[@onclick] | //span[@onclick] | //*[@role="button" and not(@tabindex)]' );
		if ( $interactive_elements->length > 0 ) {
			$issues[] = array(
				'type'        => 'keyboard_inaccessible',
				'severity'    => 'critical',
				'message'     => sprintf( 'Found %d interactive elements not keyboard accessible', $interactive_elements->length ),
				'description' => 'Interactive elements must be keyboard accessible. Use semantic HTML (button, a) or add tabindex="0" and keyboard event handlers.',
				'wcag'        => '2.1.1',
				'page_id'     => $page_id,
				'count'       => $interactive_elements->length,
			);
		}

		return $issues;
	}

	/**
	 * Check ARIA attributes
	 *
	 * @since 3.1.1
	 * @param string $html Page HTML
	 * @param int    $page_id Page ID
	 * @return array Issues found
	 */
	private function check_aria_attributes( string $html, int $page_id ): array {
		$issues = array();

		// Parse HTML
		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );

		// Check for buttons without accessible labels
		$unlabeled_buttons = $xpath->query( '//button[not(text()) and not(@aria-label) and not(@aria-labelledby)]' );
		if ( $unlabeled_buttons->length > 0 ) {
			$issues[] = array(
				'type'        => 'missing_button_label',
				'severity'    => 'critical',
				'message'     => sprintf( 'Found %d buttons without accessible labels', $unlabeled_buttons->length ),
				'description' => 'All buttons must have text content, aria-label, or aria-labelledby attribute.',
				'wcag'        => '4.1.2',
				'page_id'     => $page_id,
				'count'       => $unlabeled_buttons->length,
			);
		}

		// Check for form inputs without labels
		$unlabeled_inputs = $xpath->query( '//input[@type!="hidden" and not(@aria-label) and not(@aria-labelledby) and not(@id)] | //input[@id and not(//label[@for=@id])]' );
		if ( $unlabeled_inputs->length > 0 ) {
			$issues[] = array(
				'type'        => 'missing_form_label',
				'severity'    => 'critical',
				'message'     => sprintf( 'Found %d form inputs without proper labels', $unlabeled_inputs->length ),
				'description' => 'Form inputs must have associated labels via <label for="id">, aria-label, or aria-labelledby.',
				'wcag'        => '3.3.2',
				'page_id'     => $page_id,
				'count'       => $unlabeled_inputs->length,
			);
		}

		// Check for invalid ARIA roles
		$elements_with_role = $xpath->query( '//*[@role]' );
		$invalid_roles = 0;
		$valid_roles = array(
			'alert', 'alertdialog', 'application', 'article', 'banner', 'button', 'cell', 'checkbox',
			'columnheader', 'combobox', 'complementary', 'contentinfo', 'definition', 'dialog',
			'directory', 'document', 'feed', 'figure', 'form', 'grid', 'gridcell', 'group',
			'heading', 'img', 'link', 'list', 'listbox', 'listitem', 'log', 'main', 'marquee',
			'math', 'menu', 'menubar', 'menuitem', 'menuitemcheckbox', 'menuitemradio', 'navigation',
			'none', 'note', 'option', 'presentation', 'progressbar', 'radio', 'radiogroup', 'region',
			'row', 'rowgroup', 'rowheader', 'scrollbar', 'search', 'searchbox', 'separator',
			'slider', 'spinbutton', 'status', 'switch', 'tab', 'table', 'tablist', 'tabpanel',
			'term', 'textbox', 'timer', 'toolbar', 'tooltip', 'tree', 'treegrid', 'treeitem',
		);

		foreach ( $elements_with_role as $element ) {
			$role = $element->getAttribute( 'role' );
			if ( ! in_array( $role, $valid_roles, true ) ) {
				$invalid_roles++;
			}
		}

		if ( $invalid_roles > 0 ) {
			$issues[] = array(
				'type'        => 'invalid_aria_role',
				'severity'    => 'warning',
				'message'     => sprintf( 'Found %d elements with invalid ARIA roles', $invalid_roles ),
				'description' => 'Only use valid ARIA roles from the WAI-ARIA specification.',
				'wcag'        => '4.1.2',
				'page_id'     => $page_id,
				'count'       => $invalid_roles,
			);
		}

		return $issues;
	}

	/**
	 * Check color contrast
	 *
	 * @since 3.1.1
	 * @param string $html Page HTML
	 * @param int    $page_id Page ID
	 * @return array Issues found
	 */
	private function check_contrast( string $html, int $page_id ): array {
		$issues = array();

		// Note: Full contrast checking requires CSS and rendering, which is complex
		// Here we do basic checks for common patterns

		// Parse HTML
		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );

		// Check for inline styles with color values that might have contrast issues
		$inline_colors = $xpath->query( '//*[@style and contains(@style, "color")]' );
		if ( $inline_colors->length > 0 ) {
			$issues[] = array(
				'type'        => 'inline_color_styles',
				'severity'    => 'notice',
				'message'     => sprintf( 'Found %d elements with inline color styles', $inline_colors->length ),
				'description' => 'Inline color styles detected. Ensure sufficient color contrast ratio (4.5:1 for normal text, 3:1 for large text).',
				'wcag'        => '1.4.3',
				'page_id'     => $page_id,
				'count'       => $inline_colors->length,
			);
		}

		// Check for common low-contrast combinations in class names
		$low_contrast_classes = $xpath->query( '//*[contains(@class, "text-gray") or contains(@class, "text-muted") or contains(@class, "text-light")]' );
		if ( $low_contrast_classes->length > 0 ) {
			$issues[] = array(
				'type'        => 'potential_contrast_issue',
				'severity'    => 'warning',
				'message'     => sprintf( 'Found %d elements with potentially low-contrast classes', $low_contrast_classes->length ),
				'description' => 'Elements with gray/muted/light text classes may have insufficient contrast. Verify WCAG AA compliance (4.5:1 ratio).',
				'wcag'        => '1.4.3',
				'page_id'     => $page_id,
				'count'       => $low_contrast_classes->length,
			);
		}

		return $issues;
	}

	/**
	 * Check heading structure
	 *
	 * @since 3.1.1
	 * @param string $html Page HTML
	 * @param int    $page_id Page ID
	 * @return array Issues found
	 */
	private function check_heading_structure( string $html, int $page_id ): array {
		$issues = array();

		// Parse HTML
		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath = new \DOMXPath( $dom );

		// Check for H1
		$h1_count = $xpath->query( '//h1' )->length;
		if ( $h1_count === 0 ) {
			$issues[] = array(
				'type'        => 'missing_h1',
				'severity'    => 'critical',
				'message'     => 'Page is missing an H1 heading',
				'description' => 'Every page should have exactly one H1 heading that describes the main content.',
				'wcag'        => '2.4.6',
				'page_id'     => $page_id,
			);
		} elseif ( $h1_count > 1 ) {
			$issues[] = array(
				'type'        => 'multiple_h1',
				'severity'    => 'warning',
				'message'     => sprintf( 'Page has %d H1 headings (should have exactly 1)', $h1_count ),
				'description' => 'Multiple H1 headings can confuse screen reader users. Use only one H1 per page.',
				'wcag'        => '2.4.6',
				'page_id'     => $page_id,
				'count'       => $h1_count,
			);
		}

		// Check for skipped heading levels
		$all_headings = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );
		$heading_levels = array();
		foreach ( $all_headings as $heading ) {
			$level = intval( substr( $heading->nodeName, 1 ) );
			$heading_levels[] = $level;
		}

		if ( ! empty( $heading_levels ) ) {
			$previous_level = 1;
			$skipped = false;
			foreach ( $heading_levels as $level ) {
				if ( $level > $previous_level + 1 ) {
					$skipped = true;
					break;
				}
				$previous_level = $level;
			}

			if ( $skipped ) {
				$issues[] = array(
					'type'        => 'skipped_heading_level',
					'severity'    => 'warning',
					'message'     => 'Heading levels are not properly nested',
					'description' => 'Heading levels should increase by one. Skipping levels (e.g., H2 to H4) disrupts document outline.',
					'wcag'        => '1.3.1',
					'page_id'     => $page_id,
				);
			}
		}

		// Check for empty headings
		$empty_headings = $xpath->query( '//h1[not(normalize-space(text()))] | //h2[not(normalize-space(text()))] | //h3[not(normalize-space(text()))] | //h4[not(normalize-space(text()))] | //h5[not(normalize-space(text()))] | //h6[not(normalize-space(text()))]' );
		if ( $empty_headings->length > 0 ) {
			$issues[] = array(
				'type'        => 'empty_heading',
				'severity'    => 'critical',
				'message'     => sprintf( 'Found %d empty headings', $empty_headings->length ),
				'description' => 'Headings must contain text. Empty headings confuse screen reader users.',
				'wcag'        => '2.4.6',
				'page_id'     => $page_id,
				'count'       => $empty_headings->length,
			);
		}

		return $issues;
	}

	/**
	 * Build final results array
	 *
	 * @since 3.1.1
	 * @param array  $page_results Per-page scan results
	 * @param string $error_message Optional error message
	 * @return array Final results
	 */
	private function build_results( array $page_results, string $error_message = '' ): array {
		$total_pages = count( $page_results );
		$pages_with_issues = 0;
		$total_issues = count( $this->all_issues );

		foreach ( $page_results as $result ) {
			if ( ! empty( $result['issues'] ) ) {
				$pages_with_issues++;
			}
		}

		// Calculate health score (0-100)
		$health_score = 100;
		if ( $total_pages > 0 ) {
			$health_score = max( 0, 100 - ( ( $pages_with_issues / $total_pages ) * 50 ) );
			$health_score -= min( 50, $this->issue_counts['critical'] * 5 );
			$health_score -= min( 30, $this->issue_counts['warning'] * 2 );
			$health_score -= min( 20, $this->issue_counts['notice'] * 0.5 );
			$health_score = max( 0, round( $health_score ) );
		}

		return array(
			'status'            => empty( $error_message ) ? 'success' : 'error',
			'message'           => $error_message ?: 'Scan completed successfully',
			'scanned_at'        => current_time( 'mysql' ),
			'total_pages'       => $total_pages,
			'pages_with_issues' => $pages_with_issues,
			'health_score'      => $health_score,
			'issue_counts'      => $this->issue_counts,
			'total_issues'      => $total_issues,
			'page_results'      => $page_results,
			'all_issues'        => $this->all_issues,
		);
	}

	/**
	 * Get cached scan results
	 *
	 * @since 3.1.1
	 * @return array|false Cached results or false
	 */
	public function get_cached_results() {
		return get_transient( self::CACHE_KEY );
	}

	/**
	 * Clear scan results cache
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function clear_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Get summary statistics for dashboard
	 *
	 * @since 3.1.1
	 * @return array Summary stats
	 */
	public function get_summary_stats(): array {
		$results = $this->get_cached_results();

		if ( false === $results ) {
			// No cached results, run scan
			$results = $this->scan();
		}

		return array(
			'health_score'      => $results['health_score'] ?? 0,
			'total_issues'      => $results['total_issues'] ?? 0,
			'critical_issues'   => $results['issue_counts']['critical'] ?? 0,
			'warning_issues'    => $results['issue_counts']['warning'] ?? 0,
			'notice_issues'     => $results['issue_counts']['notice'] ?? 0,
			'total_pages'       => $results['total_pages'] ?? 0,
			'pages_with_issues' => $results['pages_with_issues'] ?? 0,
			'scanned_at'        => $results['scanned_at'] ?? '',
		);
	}
}
