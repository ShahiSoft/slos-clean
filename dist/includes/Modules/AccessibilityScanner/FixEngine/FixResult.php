<?php
/**
 * Fix Result Value Object - Immutable result from a fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FixResult
 *
 * Immutable value object representing the result of a fix operation.
 * Single Responsibility: Encapsulate fix results.
 */
final class FixResult {

	/** @var string */
	private $fixer_id;

	/** @var string */
	private $original_content;

	/** @var string */
	private $fixed_content;

	/** @var int */
	private $fixes_applied;

	/** @var array */
	private $details;

	/** @var bool */
	private $success;

	/** @var string */
	private $error_message;

	/** @var float */
	private $execution_time;

	/**
	 * Private constructor - use static factory methods
	 */
	private function __construct() {}

	/**
	 * Create a successful result
	 *
	 * @param string $fixer_id
	 * @param string $original_content
	 * @param string $fixed_content
	 * @param int    $fixes_applied
	 * @param array  $details Optional details about what was fixed
	 * @param float  $execution_time Time in seconds
	 * @return self
	 */
	public static function success(
		string $fixer_id,
		string $original_content,
		string $fixed_content,
		int $fixes_applied,
		array $details = array(),
		float $execution_time = 0.0
	): self {
		$result                   = new self();
		$result->fixer_id         = $fixer_id;
		$result->original_content = $original_content;
		$result->fixed_content    = $fixed_content;
		$result->fixes_applied    = $fixes_applied;
		$result->details          = $details;
		$result->success          = true;
		$result->error_message    = '';
		$result->execution_time   = $execution_time;
		return $result;
	}

	/**
	 * Create a skipped result (no issues found)
	 *
	 * @param string $fixer_id
	 * @param string $content
	 * @param string $reason
	 * @return self
	 */
	public static function skipped( string $fixer_id, string $content, string $reason = 'No issues found' ): self {
		$result                   = new self();
		$result->fixer_id         = $fixer_id;
		$result->original_content = $content;
		$result->fixed_content    = $content;
		$result->fixes_applied    = 0;
		$result->details          = array( 'reason' => $reason );
		$result->success          = true;
		$result->error_message    = '';
		$result->execution_time   = 0.0;
		return $result;
	}

	/**
	 * Create a failed result
	 *
	 * @param string $fixer_id
	 * @param string $content
	 * @param string $error_message
	 * @return self
	 */
	public static function error( string $fixer_id, string $content, string $error_message ): self {
		$result                   = new self();
		$result->fixer_id         = $fixer_id;
		$result->original_content = $content;
		$result->fixed_content    = $content;
		$result->fixes_applied    = 0;
		$result->details          = array();
		$result->success          = false;
		$result->error_message    = $error_message;
		$result->execution_time   = 0.0;
		return $result;
	}

	// Getters (immutable)

	public function get_fixer_id(): string {
		return $this->fixer_id;
	}

	public function get_original_content(): string {
		return $this->original_content;
	}

	public function get_fixed_content(): string {
		return $this->fixed_content;
	}

	public function get_fixes_applied(): int {
		return $this->fixes_applied;
	}

	public function get_details(): array {
		return $this->details;
	}

	public function is_success(): bool {
		return $this->success;
	}

	public function is_skipped(): bool {
		return $this->success && $this->fixes_applied === 0;
	}

	public function has_changes(): bool {
		return $this->original_content !== $this->fixed_content;
	}

	public function get_error_message(): string {
		return $this->error_message;
	}

	public function get_execution_time(): float {
		return $this->execution_time;
	}

	/**
	 * Convert to array for JSON serialization
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'fixer_id'       => $this->fixer_id,
			'success'        => $this->success,
			'skipped'        => $this->is_skipped(),
			'fixes_applied'  => $this->fixes_applied,
			'has_changes'    => $this->has_changes(),
			'details'        => $this->details,
			'error_message'  => $this->error_message,
			'execution_time' => round( $this->execution_time, 4 ),
		);
	}
}
