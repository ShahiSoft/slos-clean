<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scanner Engine - Core scanning functionality
 *
 * Manages registered checks and executes scans on content.
 *
 * @since 1.0.0
 * @since 3.1.2 Added context-aware scanning and enhanced metadata
 */
class ScannerEngine {

	/**
	 * Registered checks
	 *
	 * @var CheckInterface[]
	 */
	private $checks = array();

	/**
	 * Scanning context
	 *
	 * @since 3.1.2
	 * @var array
	 */
	private $context = array();

	/**
	 * WCAG level hierarchy for filtering
	 *
	 * @since 3.1.2
	 * @var array
	 */
	private static $wcag_level_values = array(
		'A'   => 1,
		'AA'  => 2,
		'AAA' => 3,
	);

	/**
	 * Register a check
	 *
	 * @param CheckInterface $check Check instance to register
	 */
	public function register_check( CheckInterface $check ) {
		// Skip registration if checker is dormant
		if ( defined( 'SLOS_DORMANT_CHECKERS' ) && in_array( $check->get_id(), SLOS_DORMANT_CHECKERS, true ) ) {
			return;
		}
		$this->checks[ $check->get_id() ] = $check;
	}

	/**
	 * Unregister a check by ID
	 *
	 * @since 3.1.2
	 * @param string $check_id Check ID to unregister
	 * @return bool True if check was unregistered, false if not found
	 */
	public function unregister_check( $check_id ) {
		if ( isset( $this->checks[ $check_id ] ) ) {
			unset( $this->checks[ $check_id ] );
			return true;
		}
		return false;
	}

	/**
	 * Get all registered checks
	 *
	 * @since 3.1.2
	 * @return CheckInterface[] Array of registered checks
	 */
	public function get_checks() {
		return $this->checks;
	}

	/**
	 * Get a specific check by ID
	 *
	 * @since 3.1.2
	 * @param string $check_id Check ID
	 * @return CheckInterface|null Check instance or null if not found
	 */
	public function get_check( $check_id ) {
		return $this->checks[ $check_id ] ?? null;
	}

	/**
	 * Set scanning context
	 *
	 * Context affects which checks are run and how results are reported.
	 *
	 * @since 3.1.2
	 * @param array $context {
	 *     Scanning context options.
	 *
	 *     @type bool   $is_full_page Whether scanning a full page vs. content fragment
	 *     @type string $post_type    WordPress post type being scanned
	 *     @type string $wcag_level   Target WCAG level: 'A', 'AA', or 'AAA'
	 *     @type string $scan_mode    Scan intensity: 'quick', 'full', or 'deep'
	 *     @type bool   $include_notices Whether to include notice-level issues
	 * }
	 */
	public function set_context( array $context ) {
		$this->context = array_merge(
			array(
				'is_full_page'    => false,
				'post_type'       => 'post',
				'wcag_level'      => 'AA',
				'scan_mode'       => 'full',
				'include_notices' => true,
			),
			$context
		);
	}

	/**
	 * Get current scanning context
	 *
	 * @since 3.1.2
	 * @return array Current context settings
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Run all checks on content
	 *
	 * @param string $content HTML content to scan
	 * @param array  $context Optional scanning context
	 * @return array Scan results with enhanced metadata
	 */
	public function scan( $content, array $context = array() ) {
		$results = array();

		if ( empty( $content ) ) {
			return $results;
		}

		// Set context if provided
		if ( ! empty( $context ) ) {
			$this->set_context( $context );
		}

		// Parse DOM once and share among checkers (huge performance boost)
		$dom = new \DOMDocument();
		@$dom->loadHTML(
			'<?xml encoding="UTF-8">' . $content,
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
		);

		foreach ( $this->checks as $check ) {
			// Filter by WCAG level
			if ( ! $this->should_run_check( $check ) ) {
				continue;
			}

			try {
				// Pass both content and pre-parsed DOM to checkers
				$issues = method_exists( $check, 'check_dom' )
					? $check->check_dom( $dom, $content )
					: $check->check( $content );

				if ( ! empty( $issues ) ) {
					// Filter notices if not included
					if ( ! ( $this->context['include_notices'] ?? true ) ) {
						$issues = array_filter(
							$issues,
							function ( $issue ) {
								return ( $issue['severity'] ?? 'warning' ) !== 'notice';
							}
						);

						if ( empty( $issues ) ) {
							continue;
						}
					}

					// Add enhanced metadata to each issue
					$issues = $this->enhance_issues( $issues, $check );

					$results[ $check->get_id() ] = array(
						'id'              => $check->get_id(),
						'description'     => $check->get_description(),
						'severity'        => $check->get_severity(),
						'wcag_criteria'   => $check->get_wcag_criteria(),
						'wcag_level'      => $check->get_wcag_level(),
						'wcag_url'        => $check->get_wcag_url(),
						'remediation'     => $check->get_remediation_hint(),
						'confidence'      => $check->get_confidence(),
						'issues'          => $issues,
						'issue_count'     => count( $issues ),
					);
				}
			} catch ( \Exception $e ) {
				// Log error but continue scanning
				error_log( 'Accessibility Scanner Error in check ' . $check->get_id() . ': ' . $e->getMessage() );
			}
		}

		return $results;
	}

	/**
	 * Check if a check should run based on context
	 *
	 * @since 3.1.2
	 * @param CheckInterface $check Check to evaluate
	 * @return bool True if check should run
	 */
	private function should_run_check( CheckInterface $check ) {
		// Filter by WCAG level
		$target_level = $this->context['wcag_level'] ?? 'AA';
		$check_level  = $check->get_wcag_level();

		$target_value = self::$wcag_level_values[ $target_level ] ?? 2;
		$check_value  = self::$wcag_level_values[ $check_level ] ?? 2;

		// Only run checks at or below target level
		// e.g., if target is AA (2), run A (1) and AA (2) but not AAA (3)
		if ( $check_value > $target_value ) {
			return false;
		}

		return true;
	}

	/**
	 * Enhance issues with metadata from the check
	 *
	 * @since 3.1.2
	 * @param array          $issues Array of issues
	 * @param CheckInterface $check  The check that generated the issues
	 * @return array Enhanced issues
	 */
	private function enhance_issues( array $issues, CheckInterface $check ) {
		return array_map(
			function ( $issue ) use ( $check ) {
				return array_merge(
					$issue,
					array(
						'check_id'      => $check->get_id(),
						'wcag_criteria' => $check->get_wcag_criteria(),
						'wcag_level'    => $check->get_wcag_level(),
						'wcag_url'      => $check->get_wcag_url(),
						'confidence'    => $issue['confidence'] ?? $check->get_confidence(),
						'remediation'   => $check->get_remediation_hint(),
					)
				);
			},
			$issues
		);
	}

	/**
	 * Get summary statistics for scan results
	 *
	 * @since 3.1.2
	 * @param array $results Scan results from scan() method
	 * @return array Summary statistics
	 */
	public function get_summary( array $results ) {
		$summary = array(
			'total_issues'   => 0,
			'by_severity'    => array(
				'critical' => 0,
				'serious'  => 0,
				'warning'  => 0,
				'notice'   => 0,
			),
			'by_wcag_level'  => array(
				'A'   => 0,
				'AA'  => 0,
				'AAA' => 0,
			),
			'by_confidence'  => array(
				'definite'  => 0,
				'likely'    => 0,
				'potential' => 0,
			),
			'checks_passed'  => 0,
			'checks_failed'  => count( $results ),
			'checks_total'   => count( $this->checks ),
		);

		$summary['checks_passed'] = $summary['checks_total'] - $summary['checks_failed'];

		foreach ( $results as $result ) {
			$issue_count = $result['issue_count'] ?? count( $result['issues'] ?? array() );
			$summary['total_issues'] += $issue_count;

			// Count by severity
			$severity = $result['severity'] ?? 'warning';
			if ( isset( $summary['by_severity'][ $severity ] ) ) {
				$summary['by_severity'][ $severity ] += $issue_count;
			}

			// Count by WCAG level
			$level = $result['wcag_level'] ?? 'AA';
			if ( isset( $summary['by_wcag_level'][ $level ] ) ) {
				$summary['by_wcag_level'][ $level ] += $issue_count;
			}

			// Count by confidence
			$confidence = $result['confidence'] ?? 'definite';
			if ( isset( $summary['by_confidence'][ $confidence ] ) ) {
				$summary['by_confidence'][ $confidence ] += $issue_count;
			}
		}

		return $summary;
	}
}

