<?php
/**
 * Scanning Service
 *
 * Handles scanning operations for accessibility checks.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Services
 * @since      3.2.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;

/**
 * Service class for scanning operations
 *
 * @since 3.2.0
 */
class ScanningService {

	/**
	 * Scanner engine instance
	 *
	 * @var ScannerEngine
	 */
	private $scanner;

	/**
	 * Constructor
	 *
	 * @since 3.2.0
	 *
	 * @param ScannerEngine $scanner Scanner engine instance.
	 */
	public function __construct( ScannerEngine $scanner ) {
		$this->scanner = $scanner;
	}

	/**
	 * Get scanner engine instance
	 *
	 * @since 3.2.0
	 *
	 * @return ScannerEngine Scanner engine instance.
	 */
	public function get_scanner() {
		return $this->scanner;
	}
}
