<?php
/**
 * Fix Engine AJAX Handler
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FixEngineAjaxHandler
 *
 * Handles all AJAX requests for the FixEngine.
 * Single Responsibility: AJAX request/response handling.
 */
final class FixEngineAjaxHandler {

	/** @var FixEngine */
	private $engine;

	/** @var string AJAX action prefix */
	private const ACTION_PREFIX = 'slos_fixengine_';

	/**
	 * Constructor
	 *
	 * @param FixEngine|null $engine
	 */
	public function __construct( ?FixEngine $engine = null ) {
		$this->engine = $engine ?? new FixEngine();
	}

	/**
	 * Register AJAX handlers
	 */
	public function register(): void {
		$actions = array(
			'get_fixers'     => 'handle_get_fixers',
			'fix_single'     => 'handle_fix_single',
			'fix_batch'      => 'handle_fix_batch',
			'fix_post'       => 'handle_fix_post',
			'preview'        => 'handle_preview',
			'get_history'    => 'handle_get_history',
			'get_statistics' => 'handle_get_statistics',
		);

		foreach ( $actions as $action => $method ) {
			add_action( 'wp_ajax_' . self::ACTION_PREFIX . $action, array( $this, $method ) );
		}
	}

	/**
	 * Verify nonce and capabilities
	 *
	 * @return bool
	 */
	private function verify_request(): bool {
		if ( ! check_ajax_referer( 'slos_fixengine_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'shahi-legalflowsuite' ),
					'code'    => 'invalid_nonce',
				),
				403
			);
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'shahi-legalflowsuite' ),
					'code'    => 'insufficient_permissions',
				),
				403
			);
			return false;
		}

		return true;
	}

	/**
	 * Handle get fixers request
	 */
	public function handle_get_fixers(): void {
		$this->verify_request();

		$grouped = isset( $_GET['grouped'] ) && $_GET['grouped'] === 'true';

		if ( $grouped ) {
			$data = $this->engine->get_fixers_by_category();
		} else {
			$data = $this->engine->get_fixers_array();
		}

		wp_send_json_success(
			array(
				'fixers'     => $data,
				'count'      => $this->engine->get_fixer_count(),
				'categories' => $this->engine->get_categories(),
			)
		);
	}

	/**
	 * Handle single fixer execution
	 */
	public function handle_fix_single(): void {
		$this->verify_request();

		$post_id  = absint( $_POST['post_id'] ?? 0 );
		$fixer_id = sanitize_text_field( $_POST['fixer_id'] ?? '' );

		if ( ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid post ID.', 'shahi-legalflowsuite' ),
					'code'    => 'invalid_post_id',
				),
				400
			);
			return;
		}

		if ( empty( $fixer_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No fixer specified.', 'shahi-legalflowsuite' ),
					'code'    => 'no_fixer',
				),
				400
			);
			return;
		}

		// Check post exists and user can edit..
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error(
				array(
					'message' => __( 'Post not found.', 'shahi-legalflowsuite' ),
					'code'    => 'post_not_found',
				),
				404
			);
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You cannot edit this post.', 'shahi-legalflowsuite' ),
					'code'    => 'cannot_edit',
				),
				403
			);
			return;
		}

		// Execute single fixer..
		$session = $this->engine->fix_post( $post_id, array( $fixer_id ) );

		$results = $session->get_results();
		$result  = ! empty( $results ) ? $results[0] : null;

		if ( $result ) {
			wp_send_json_success(
				array(
					'session_id'     => $session->get_id(),
					'fixer_id'       => $fixer_id,
					'fixer_name'     => $this->engine->get_fixer( $fixer_id )?->get_name() ?? $fixer_id,
					'success'        => $result->is_success(),
					'skipped'        => $result->is_skipped(),
					'fixes_applied'  => $result->get_fixes_applied(),
					'message'        => $result->get_message(),
					'details'        => $result->get_details(),
					'execution_time' => $result->get_execution_time(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Fixer execution failed.', 'shahi-legalflowsuite' ),
					'code'    => 'execution_failed',
				),
				500
			);
		}
	}

	/**
	 * Handle batch fix execution
	 */
	public function handle_fix_batch(): void {
		$this->verify_request();

		$post_id   = absint( $_POST['post_id'] ?? 0 );
		$fixer_ids = isset( $_POST['fixer_ids'] ) ? array_map( 'sanitize_text_field', (array) $_POST['fixer_ids'] ) : array();

		if ( ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid post ID.', 'shahi-legalflowsuite' ),
					'code'    => 'invalid_post_id',
				),
				400
			);
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Cannot access this post.', 'shahi-legalflowsuite' ),
					'code'    => 'access_denied',
				),
				403
			);
			return;
		}

		// Execute fixers..
		$session = $this->engine->fix_post( $post_id, $fixer_ids );

		wp_send_json_success(
			array(
				'session_id' => $session->get_id(),
				'status'     => $session->get_status(),
				'stats'      => $session->get_stats(),
				'progress'   => $session->get_progress(),
				'results'    => array_map(
					function ( $r ) {
						return array(
							'fixer_id'      => $r->get_fixer_id(),
							'success'       => $r->is_success(),
							'skipped'       => $r->is_skipped(),
							'fixes_applied' => $r->get_fixes_applied(),
							'message'       => $r->get_message(),
						);
					},
					$session->get_results()
				),
			)
		);
	}

	/**
	 * Handle full post fix (all fixers)
	 */
	public function handle_fix_post(): void {
		$this->verify_request();

		$post_id = absint( $_POST['post_id'] ?? 0 );

		if ( ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid post ID.', 'shahi-legalflowsuite' ),
					'code'    => 'invalid_post_id',
				),
				400
			);
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Cannot access this post.', 'shahi-legalflowsuite' ),
					'code'    => 'access_denied',
				),
				403
			);
			return;
		}

		// Execute all fixers..
		$session = $this->engine->fix_post( $post_id );

		wp_send_json_success(
			array(
				'session_id'  => $session->get_id(),
				'status'      => $session->get_status(),
				'stats'       => $session->get_stats(),
				'total_fixes' => $session->get_stats()['total_fixes'],
				'results'     => array_map( fn( $r ) => $r->to_array(), $session->get_results() ),
			)
		);
	}

	/**
	 * Handle preview request (no save)
	 */
	public function handle_preview(): void {
		$this->verify_request();

		$post_id   = absint( $_POST['post_id'] ?? 0 );
		$fixer_ids = isset( $_POST['fixer_ids'] ) ? array_map( 'sanitize_text_field', (array) $_POST['fixer_ids'] ) : array();

		if ( ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid post ID.', 'shahi-legalflowsuite' ),
					'code'    => 'invalid_post_id',
				),
				400
			);
			return;
		}

		$preview = $this->engine->preview_fixes( $post_id, $fixer_ids );

		if ( $preview['success'] ) {
			wp_send_json_success( $preview );
		} else {
			wp_send_json_error( $preview );
		}
	}

	/**
	 * Handle get history request
	 */
	public function handle_get_history(): void {
		$this->verify_request();

		$post_id = absint( $_GET['post_id'] ?? 0 );
		$limit   = absint( $_GET['limit'] ?? 50 );

		if ( ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid post ID.', 'shahi-legalflowsuite' ),
					'code'    => 'invalid_post_id',
				),
				400
			);
			return;
		}

		$history = $this->engine->get_history( $post_id, $limit );

		wp_send_json_success(
			array(
				'post_id' => $post_id,
				'history' => $history,
			)
		);
	}

	/**
	 * Handle get statistics request
	 */
	public function handle_get_statistics(): void {
		$this->verify_request();

		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null;

		$stats = $this->engine->get_statistics( $post_id );

		wp_send_json_success(
			array(
				'statistics' => $stats,
			)
		);
	}

	/**
	 * Get script localization data for frontend
	 *
	 * @return array
	 */
	public function get_localize_data(): array {
		return array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'slos_fixengine_nonce' ),
			'actions'     => array(
				'get_fixers'     => self::ACTION_PREFIX . 'get_fixers',
				'fix_single'     => self::ACTION_PREFIX . 'fix_single',
				'fix_batch'      => self::ACTION_PREFIX . 'fix_batch',
				'fix_post'       => self::ACTION_PREFIX . 'fix_post',
				'preview'        => self::ACTION_PREFIX . 'preview',
				'get_history'    => self::ACTION_PREFIX . 'get_history',
				'get_statistics' => self::ACTION_PREFIX . 'get_statistics',
			),
			'i18n'        => array(
				'fixing'          => __( 'Fixing...', 'shahi-legalflowsuite' ),
				'complete'        => __( 'Complete', 'shahi-legalflowsuite' ),
				'error'           => __( 'Error', 'shahi-legalflowsuite' ),
				'skipped'         => __( 'Skipped', 'shahi-legalflowsuite' ),
				'no_issues'       => __( 'No issues to fix', 'shahi-legalflowsuite' ),
				'no_issues'       => __( 'No issues to fix', 'shahi-legalflowsuite' ),
				/* translators: %d: number of fixes applied */
				'fixes_applied'   => __( '%d fixes applied', 'shahi-legalflowsuite' ),
				/* translators: %s: content title or identifier */
				'processing'      => __( 'Processing %s...', 'shahi-legalflowsuite' ),
				'confirm_fix_all' => __( 'Apply all automatic fixes to this content?', 'shahi-legalflowsuite' ),
				/* translators: %d: number of fixes applied */
				'success_message' => __( 'Successfully applied %d fixes.', 'shahi-legalflowsuite' ),
				'error_message'   => __( 'An error occurred while fixing.', 'shahi-legalflowsuite' ),
			),
			'fixer_count' => $this->engine->get_fixer_count(),
		);
	}
}
