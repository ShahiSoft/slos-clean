<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for accessibility checks
 *
 * All accessibility checkers must implement this interface.
 *
 * @since 1.0.0
 * @since 3.1.2 Added get_wcag_level(), get_remediation_hint(), get_wcag_url(), get_confidence(), check_dom()
 */
interface CheckInterface {
	/**
	 * Get unique check ID
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get human-readable description
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get severity level
	 *
	 * @return string 'critical', 'serious', 'warning', 'notice'
	 */
	public function get_severity();

	/**
	 * Get WCAG Success Criteria
	 *
	 * @return string e.g. '1.1.1'
	 */
	public function get_wcag_criteria();

	/**
	 * Run the check on the content
	 *
	 * @param string $content HTML content to check
	 * @return array Array of issues found. Each issue should be an associative array with details.
	 */
	public function check( $content );

	/**
	 * Get WCAG conformance level
	 *
	 * @since 3.1.2
	 * @return string 'A', 'AA', or 'AAA'
	 */
	public function get_wcag_level();

	/**
	 * Get remediation hint for fixing the issue
	 *
	 * @since 3.1.2
	 * @return string Human-readable fix suggestion
	 */
	public function get_remediation_hint();

	/**
	 * Get link to WCAG documentation
	 *
	 * @since 3.1.2
	 * @return string URL to WCAG understanding document
	 */
	public function get_wcag_url();

	/**
	 * Get issue confidence level
	 *
	 * - 'definite': Issue is certain (e.g., missing alt text)
	 * - 'likely': Issue is probable but may have edge cases
	 * - 'potential': Issue needs human verification
	 *
	 * @since 3.1.2
	 * @return string 'definite', 'likely', or 'potential'
	 */
	public function get_confidence();

	/**
	 * Check with pre-parsed DOM for performance optimization
	 *
	 * When the scanner engine parses the DOM once and passes it to all checkers,
	 * this method should be used instead of check() to avoid re-parsing.
	 *
	 * @since 3.1.2
	 * @param \DOMDocument $dom Pre-parsed DOM document
	 * @param string       $content Original HTML content (for fallback/reference)
	 * @return array Array of issues found
	 */
	public function check_dom( \DOMDocument $dom, $content );
}

