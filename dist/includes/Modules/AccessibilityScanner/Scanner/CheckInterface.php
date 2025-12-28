<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @return string 'critical', 'warning', 'notice'
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
}

