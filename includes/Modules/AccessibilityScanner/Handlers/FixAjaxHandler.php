<?php
/**
 * Fix AJAX Handler
 *
 * Handles AJAX requests for fix operations.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Handlers
 * @since      3.2.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\BackupService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\FixService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\ScanningService;

/**
 * AJAX Handler for fix operations
 *
 * @since 3.2.0
 */
class FixAjaxHandler {

	/**
	 * Scanner engine instance
	 *
	 * @var ScannerEngine
	 */
	private $scanner;

	/**
	 * Scanning service instance
	 *
	 * @var ScanningService
	 */
	private $scanning_service;

	/**
	 * Fix service instance
	 *
	 * @var FixService
	 */
	private $fix_service;

	/**
	 * Backup service instance
	 *
	 * @var BackupService
	 */
	private $backup_service;

	/**
	 * Constructor
	 *
	 * @since 3.2.0
	 *
	 * @param ScannerEngine   $scanner          Scanner engine instance.
	 * @param ScanningService $scanning_service Scanning service instance.
	 * @param FixService      $fix_service      Fix service instance.
	 * @param BackupService   $backup_service   Backup service instance.
	 */
	public function __construct( ScannerEngine $scanner, ScanningService $scanning_service, FixService $fix_service, BackupService $backup_service ) {
		$this->scanner          = $scanner;
		$this->scanning_service = $scanning_service;
		$this->fix_service      = $fix_service;
		$this->backup_service   = $backup_service;
	}

	/**
	 * AJAX handler for fixing single issue
	 *
	 * @since 3.2.0
	 */
	public function ajax_fix_single_issue() {
		// TODO: Implement.
		wp_send_json_error( array( 'message' => 'Not implemented yet' ) );
	}

	/**
	 * AJAX handler for fixing all issues
	 *
	 * @since 3.2.0
	 */
	public function ajax_fix_all_issues() {
		// TODO: Implement.
		wp_send_json_error( array( 'message' => 'Not implemented yet' ) );
	}

	/**
	 * AJAX handler for toggling autofix
	 *
	 * @since 3.2.0
	 */
	public function ajax_toggle_autofix() {
		// TODO: Implement.
		wp_send_json_error( array( 'message' => 'Not implemented yet' ) );
	}

	/**
	 * AJAX handler for autofix single fixer
	 *
	 * @since 3.2.0
	 */
	public function ajax_autofix_single_fixer() {
		// TODO: Implement.
		wp_send_json_error( array( 'message' => 'Not implemented yet' ) );
	}

	/**
	 * AJAX handler for getting page fixable issues
	 *
	 * @since 3.2.0
	 */
	public function ajax_get_page_fixable_issues() {
		// TODO: Implement.
		wp_send_json_error( array( 'message' => 'Not implemented yet' ) );
	}
}
