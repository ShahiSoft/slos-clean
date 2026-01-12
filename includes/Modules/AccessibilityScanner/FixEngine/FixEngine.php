<?php
/**
 * Fix Engine - Main orchestrator for the fixing system
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

use ShahiLegalFlowSuite\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\FixEngine\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FixEngine
 *
 * Main orchestrator that coordinates all fixing operations.
 * Open/Closed: New fixers can be added without modifying this class.
 * Dependency Inversion: Depends on abstractions (FixerInterface).
 */
final class FixEngine {

	/**
	 * Collection of available fixers.
	 *
	 * @var FixerCollection
	 */
	private $fixers;

	/**
	 * Repository for persisting fix history.
	 *
	 * @var FixHistoryRepository
	 */
	private $repository;

	/**
	 * Currently active fix session.
	 *
	 * @var FixSession|null
	 */
	private $current_session = null;

	/**
	 * Whether the engine has been initialized.
	 *
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * Callbacks for progress updates.
	 *
	 * @var array
	 */
	private $progress_callbacks = array();

	/**
	 * Constructor
	 *
	 * @param FixHistoryRepository|null $repository Optional repository instance for persistence.
	 */
	public function __construct( ?FixHistoryRepository $repository = null ) {
		$this->fixers     = new FixerCollection();
		$this->repository = $repository ?? new FixHistoryRepository();
	}

	/**
	 * Initialize the engine - load and register all fixers
	 *
	 * @return self
	 */
	public function initialize(): self {
		if ( $this->initialized ) {
			return $this;
		}

		// Create database table if needed..
		if ( ! $this->repository->table_exists() ) {
			$this->repository->create_table();
		}

		// Load all fixer classes..
		$this->load_fixers();

		// Allow plugins to register additional fixers..
		do_action( 'slos_fix_engine_register_fixers', $this->fixers );

		$this->initialized = true;

		return $this;
	}

	/**
	 * Load all built-in fixers
	 * Phase 2.2: Uses dynamic discovery instead of manual registration
	 */
	private function load_fixers(): void {
		// Use FixerCollection's auto-discovery (Phase 2.2)..
		$count = $this->fixers->auto_discover();

		Logger::info(
			'FixEngine initialized',
			array(
				'fixers_count' => $count,
				'categories'   => count( $this->fixers->categories() ),
			)
		);
	}

	/**
	 * Register a progress callback
	 *
	 * @param callable $callback Callback to receive progress updates.
	 * @return self
	 */
	public function on_progress( callable $callback ): self {
		$this->progress_callbacks[] = $callback;
		return $this;
	}

	/**
	 * Emit progress update
	 *
	 * @param array $data Progress payload to emit.
	 */
	private function emit_progress( array $data ): void {
		foreach ( $this->progress_callbacks as $callback ) {
			call_user_func( $callback, $data );
		}
	}

	/**
	 * Get all registered fixers
	 *
	 * @return FixerCollection
	 */
	public function get_fixers(): FixerCollection {
		$this->initialize();
		return $this->fixers;
	}

	/**
	 * Get a specific fixer by ID
	 *
	 * @param string $fixer_id Fixer identifier.
	 * @return FixerInterface|null
	 */
	public function get_fixer( string $fixer_id ): ?FixerInterface {
		$this->initialize();
		$canonical_id = CanonicalIds::canonicalize( $fixer_id ) ?? $fixer_id;
		return $this->fixers->get( $canonical_id );
	}

	/**
	 * Fix content with a single fixer
	 *
	 * @param string $content HTML content.
	 * @param string $fixer_id Fixer ID.
	 * @param array  $options Optional configuration.
	 * @return FixResult
	 */
	public function fix_with_fixer( string $content, string $fixer_id, array $options = array() ): FixResult {
		$this->initialize();

		$fixer = $this->fixers->get( $fixer_id );

		if ( ! $fixer ) {
			return FixResult::error( $fixer_id, "Unknown fixer: {$fixer_id}", $content );
		}

		return $fixer->fix( $content, $options );
	}

	/**
	 * Fix content with multiple fixers
	 *
	 * @param string $content HTML content.
	 * @param array  $fixer_ids Array of fixer IDs (empty = all fixers).
	 * @param array  $options Optional configuration.
	 * @return FixSession
	 */
	public function fix_content( string $content, array $fixer_ids = array(), array $options = array() ): FixSession {
		$this->initialize();

		$post_id = $options['post_id'] ?? 0;
		$session = new FixSession( $post_id );
		$session->start();

		$this->current_session = $session;

		// Determine which fixers to run..
		$fixers_to_run = array();
		if ( empty( $fixer_ids ) ) {
			$fixers_to_run = iterator_to_array( $this->fixers );
		} else {
			foreach ( $fixer_ids as $id ) {
				$canonical_id = CanonicalIds::canonicalize( $id ) ?? $id;
				$fixer        = $this->fixers->get( $canonical_id );
				if ( $fixer ) {
					$fixers_to_run[] = $fixer;
				}
			}
		}

		$total           = count( $fixers_to_run );
		$current         = 0;
		$current_content = $content;

		foreach ( $fixers_to_run as $fixer ) {
			++$current;

			$this->emit_progress(
				array(
					'session_id' => $session->get_id(),
					'current'    => $current,
					'total'      => $total,
					'fixer_id'   => $fixer->get_id(),
					'fixer_name' => $fixer->get_name(),
					'status'     => 'processing',
					'percent'    => round( ( $current / $total ) * 100 ),
				)
			);

			try {
				$result = $fixer->fix( $current_content, $options );
				$session->add_result( $result );

				// Update content for next fixer..
				if ( $result->is_success() && $result->get_fixes_applied() > 0 ) {
					$current_content = $result->get_fixed_content();
				}
			} catch ( \Exception $e ) {
				$error_result = FixResult::error(
					$fixer->get_id(),
					$e->getMessage(),
					$current_content
				);
				$session->add_result( $error_result );

				if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log(
						sprintf(
							'[FixEngine] Fixer %s threw exception: %s',
							$fixer->get_id(),
							$e->getMessage()
						)
					);
				}
			}
		}

		$session->complete();
		$session->set_final_content( $current_content );

		// Save to database..
		if ( $post_id > 0 ) {
			$this->repository->save_session( $session );
		}

		$this->current_session = null;

		return $session;
	}

	/**
	 * Fix a WordPress post.
	 *
	 * @param int   $post_id   Post ID being processed.
	 * @param array $fixer_ids Array of fixer IDs (empty = all fixers).
	 * @param array $options   Optional configuration.
	 * @return FixSession
	 */
	public function fix_post( int $post_id, array $fixer_ids = array(), array $options = array() ): FixSession {
		$this->initialize();

		$post = get_post( $post_id );

		if ( ! $post ) {
			$session = new FixSession( $post_id );
			$session->start();
			$session->add_result( FixResult::error( 'engine', "Post not found: {$post_id}", '' ) );
			$session->complete();
			return $session;
		}

		$options['post_id']   = $post_id;
		$options['post_type'] = $post->post_type;

		$session = $this->fix_content( $post->post_content, $fixer_ids, $options );

		// Save the fixed content if there were successful fixes..
		if ( $session->get_stats()['successful'] > 0 && $session->get_final_content() ) {
			$save_content = $options['save_content'] ?? true;

			if ( $save_content ) {
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $session->get_final_content(),
					)
				);

				// Update post meta..
				update_post_meta( $post_id, '_slos_last_autofix_date', current_time( 'mysql' ) );
				update_post_meta( $post_id, '_slos_last_autofix_session', $session->get_id() );
			}
		}

		return $session;
	}

	/**
	 * Preview fixes without saving.
	 *
	 * @param int   $post_id   Post ID to preview.
	 * @param array $fixer_ids Fixer IDs to run (empty = all fixers).
	 * @return array Preview data.
	 */
	public function preview_fixes( int $post_id, array $fixer_ids = array() ): array {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return array(
				'success' => false,
				'error'   => 'Post not found',
			);
		}

		$session = $this->fix_content(
			$post->post_content,
			$fixer_ids,
			array(
				'post_id'      => $post_id,
				'save_content' => false,
			)
		);

		return array(
			'success'          => true,
			'original_content' => $post->post_content,
			'fixed_content'    => $session->get_final_content(),
			'stats'            => $session->get_stats(),
			'results'          => array_map( fn( $r ) => $r->to_array(), $session->get_results() ),
		);
	}

	/**
	 * Get fixer categories
	 *
	 * @return array
	 */
	public function get_categories(): array {
		$this->initialize();

		return array(
			'images'      => __( 'Images', 'shahi-legalflowsuite' ),
			'links'       => __( 'Links', 'shahi-legalflowsuite' ),
			'headings'    => __( 'Headings', 'shahi-legalflowsuite' ),
			'forms'       => __( 'Forms', 'shahi-legalflowsuite' ),
			'tables'      => __( 'Tables', 'shahi-legalflowsuite' ),
			'semantic'    => __( 'Semantic HTML', 'shahi-legalflowsuite' ),
			'interactive' => __( 'Interactive', 'shahi-legalflowsuite' ),
			'aria'        => __( 'ARIA', 'shahi-legalflowsuite' ),
			'media'       => __( 'Media', 'shahi-legalflowsuite' ),
			'content'     => __( 'Content', 'shahi-legalflowsuite' ),
			'color'       => __( 'Color & Contrast', 'shahi-legalflowsuite' ),
			'motion'      => __( 'Motion & Animation', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Get fixers grouped by category
	 *
	 * @return array
	 */
	public function get_fixers_by_category(): array {
		$this->initialize();

		$categories = $this->get_categories();
		$grouped    = array();

		foreach ( $categories as $cat_id => $cat_name ) {
			$fixers = $this->fixers->by_category( $cat_id );

			if ( count( $fixers ) > 0 ) {
				$grouped[ $cat_id ] = array(
					'name'   => $cat_name,
					'fixers' => array_map(
						fn( $f ) => array(
							'id'          => $f->get_id(),
							'name'        => $f->get_name(),
							'description' => $f->get_description(),
							'wcag'        => $f->get_wcag_criteria(),
						),
						iterator_to_array( $fixers )
					),
				);
			}
		}

		return $grouped;
	}

	/**
	 * Get all fixers as array for JSON
	 *
	 * @return array
	 */
	public function get_fixers_array(): array {
		$this->initialize();
		return $this->fixers->to_array();
	}

	/**
	 * Get fix history for a post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $limit   Maximum history items to return.
	 * @return array
	 */
	public function get_history( int $post_id, int $limit = 50 ): array {
		return $this->repository->get_history_for_post( $post_id, $limit );
	}

	/**
	 * Get fix statistics.
	 *
	 * @param int|null $post_id Optional filter by post.
	 * @return array
	 */
	public function get_statistics( ?int $post_id = null ): array {
		return $this->repository->get_statistics( $post_id );
	}

	/**
	 * Get current session if running
	 *
	 * @return FixSession|null
	 */
	public function get_current_session(): ?FixSession {
		return $this->current_session;
	}

	/**
	 * Check if engine is initialized
	 *
	 * @return bool
	 */
	public function is_initialized(): bool {
		return $this->initialized;
	}

	/**
	 * Get total fixer count
	 *
	 * @return int
	 */
	public function get_fixer_count(): int {
		$this->initialize();
		return count( $this->fixers );
	}
}
