<?php
/**
 * Fix Session - Tracks a complete fix operation across multiple fixers
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FixSession
 *
 * Tracks the state of a fix operation.
 * Single Responsibility: Session state management.
 */
final class FixSession {

	/** @var string */
	private $session_id;

	/** @var int */
	private $post_id;

	/** @var int */
	private $user_id;

	/** @var string */
	private $original_content;

	/** @var string */
	private $current_content;

	/** @var array<FixResult> */
	private $results = array();

	/** @var string */
	private $status = 'pending'; // pending, running, completed, cancelled, error

	/** @var int */
	private $total_fixers;

	/** @var int */
	private $processed_fixers = 0;

	/** @var int */
	private $total_fixes = 0;

	/** @var int */
	private $total_errors = 0;

	/** @var int */
	private $total_skipped = 0;

	/** @var float */
	private $started_at;

	/** @var float|null */
	private $completed_at;

	/** @var string */
	private $error_message = '';

	/**
	 * Constructor
	 *
	 * @param int    $post_id
	 * @param string $content
	 * @param int    $total_fixers
	 */
	public function __construct( int $post_id, string $content = '', int $total_fixers = 0 ) {
		$this->session_id       = wp_generate_uuid4();
		$this->post_id          = $post_id;
		$this->user_id          = get_current_user_id();
		$this->original_content = $content;
		$this->current_content  = $content;
		$this->total_fixers     = $total_fixers;
		$this->started_at       = microtime( true );
	}

	/**
	 * Get session ID
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->session_id;
	}

	/**
	 * Get post ID
	 *
	 * @return int
	 */
	public function get_post_id(): int {
		return $this->post_id;
	}

	/**
	 * Get current content
	 *
	 * @return string
	 */
	public function get_content(): string {
		return $this->current_content;
	}

	/**
	 * Start the session
	 */
	public function start(): void {
		$this->status = 'running';
	}

	/**
	 * Add a fix result
	 *
	 * @param FixResult $result
	 */
	public function add_result( FixResult $result ): void {
		$this->results[] = $result;
		++$this->processed_fixers;

		if ( $result->is_success() ) {
			if ( $result->is_skipped() ) {
				++$this->total_skipped;
			} else {
				$this->total_fixes += $result->get_fixes_applied();
				// Update content with fixed version
				if ( $result->has_changes() ) {
					$this->current_content = $result->get_fixed_content();
				}
			}
		} else {
			++$this->total_errors;
		}
	}

	/**
	 * Mark session as completed
	 */
	public function complete(): void {
		$this->status       = 'completed';
		$this->completed_at = microtime( true );
	}

	/**
	 * Mark session as cancelled
	 */
	public function cancel(): void {
		$this->status       = 'cancelled';
		$this->completed_at = microtime( true );
	}

	/**
	 * Mark session as error
	 *
	 * @param string $message
	 */
	public function error( string $message ): void {
		$this->status        = 'error';
		$this->error_message = $message;
		$this->completed_at  = microtime( true );
	}

	/**
	 * Get session status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Check if session is running
	 *
	 * @return bool
	 */
	public function is_running(): bool {
		return $this->status === 'running';
	}

	/**
	 * Check if session is completed
	 *
	 * @return bool
	 */
	public function is_completed(): bool {
		return in_array( $this->status, array( 'completed', 'cancelled', 'error' ), true );
	}

	/**
	 * Get progress percentage
	 *
	 * @return int
	 */
	public function get_progress(): int {
		if ( $this->total_fixers === 0 ) {
			return 100;
		}
		return (int) round( ( $this->processed_fixers / $this->total_fixers ) * 100 );
	}

	/**
	 * Get total fixes applied
	 *
	 * @return int
	 */
	public function get_total_fixes(): int {
		return $this->total_fixes;
	}

	/**
	 * Get total errors
	 *
	 * @return int
	 */
	public function get_total_errors(): int {
		return $this->total_errors;
	}

	/**
	 * Get total skipped
	 *
	 * @return int
	 */
	public function get_total_skipped(): int {
		return $this->total_skipped;
	}

	/**
	 * Get processed count
	 *
	 * @return int
	 */
	public function get_processed_count(): int {
		return $this->processed_fixers;
	}

	/**
	 * Get total fixer count
	 *
	 * @return int
	 */
	public function get_total_count(): int {
		return $this->total_fixers;
	}

	/**
	 * Get all results
	 *
	 * @return array<FixResult>
	 */
	public function get_results(): array {
		return $this->results;
	}

	/**
	 * Get execution time in seconds
	 *
	 * @return float
	 */
	public function get_execution_time(): float {
		$end = $this->completed_at ?? microtime( true );
		return $end - $this->started_at;
	}

	/**
	 * Check if content was modified
	 *
	 * @return bool
	 */
	public function has_changes(): bool {
		return $this->original_content !== $this->current_content;
	}

	/**
	 * Get the current/final content
	 *
	 * @return string
	 */
	public function get_final_content(): string {
		return $this->current_content;
	}

	/**
	 * Set the final content
	 *
	 * @param string $content
	 */
	public function set_final_content( string $content ): void {
		$this->current_content = $content;
	}

	/**
	 * Get original content
	 *
	 * @return string
	 */
	public function get_original_content(): string {
		return $this->original_content;
	}

	/**
	 * Get stats summary
	 *
	 * @return array
	 */
	public function get_stats(): array {
		return array(
			'total'       => $this->total_fixers,
			'processed'   => $this->processed_fixers,
			'successful'  => $this->processed_fixers - $this->total_errors - $this->total_skipped,
			'skipped'     => $this->total_skipped,
			'errors'      => $this->total_errors,
			'total_fixes' => $this->total_fixes,
		);
	}

	/**
	 * Convert to array for JSON/database
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'session_id'       => $this->session_id,
			'post_id'          => $this->post_id,
			'user_id'          => $this->user_id,
			'status'           => $this->status,
			'progress'         => $this->get_progress(),
			'total_fixers'     => $this->total_fixers,
			'processed_fixers' => $this->processed_fixers,
			'total_fixes'      => $this->total_fixes,
			'total_errors'     => $this->total_errors,
			'total_skipped'    => $this->total_skipped,
			'has_changes'      => $this->has_changes(),
			'execution_time'   => round( $this->get_execution_time(), 2 ),
			'error_message'    => $this->error_message,
			'results'          => array_map( fn( $r ) => $r->to_array(), $this->results ),
		);
	}
}
