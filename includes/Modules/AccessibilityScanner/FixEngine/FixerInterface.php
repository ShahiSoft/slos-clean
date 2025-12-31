<?php
/**
 * Fixer Interface - Single Responsibility: Define fixer contract
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface FixerInterface
 * 
 * All fixers must implement this interface.
 * Follows Interface Segregation Principle.
 */
interface FixerInterface {

	/**
	 * Get unique fixer identifier
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Get human-readable name
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * Get WCAG criteria this fixer addresses
	 *
	 * @return array
	 */
	public function get_wcag_criteria(): array;

	/**
	 * Get fixer category (image, heading, link, form, aria, table, media, keyboard, color)
	 *
	 * @return string
	 */
	public function get_category(): string;

	/**
	 * Check if this fixer can fix the given content
	 *
	 * @param string $content HTML content to check
	 * @return bool
	 */
	public function can_fix( string $content ): bool;

	/**
	 * Apply fixes to content
	 *
	 * @param string $content HTML content to fix
	 * @return FixResult
	 */
	public function fix( string $content ): FixResult;
}
