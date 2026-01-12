<?php
/**
 * Compliance Score Calculator Service
 *
 * Calculates multi-dimensional compliance readiness score based on
 * configuration completeness across six dimensions.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.1.0
 * @since       3.1.0
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\Consent_Repository;
use ShahiLegalFlowSuite\Admin\Settings;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load constants..
require_once SHAHI_LEGALFLOWSUITE_PATH . 'config/compliance-constants.php';

/**
 * Compliance_Score_Calculator Class
 *
 * Computes per-dimension scores and weighted aggregate compliance score.
 *
 * @since 3.1.0
 */
class Compliance_Score_Calculator extends Base_Service {

	/**
	 * Consent Repository instance
	 *
	 * @var Consent_Repository
	 */
	private $consent_repository;

	/**
	 * Cached calculation results
	 *
	 * @var array|null
	 */
	private $cached_result = null;

	/**
	 * Constructor
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->consent_repository = new Consent_Repository();
	}

	/**
	 * Calculate all dimension scores and aggregate
	 *
	 * @since 3.1.0
	 * @param bool $use_cache Whether to use cached results
	 * @return array {
	 *     @type int    $score       Aggregate readiness score (0-100)
	 *     @type string $grade       Letter grade (A-F)
	 *     @type string $grade_class CSS class for grade
	 *     @type string $label       Human-readable label
	 *     @type array  $dimensions  Per-dimension scores and metadata
	 * }
	 */
	public function calculate( bool $use_cache = true ): array {
		if ( $use_cache && null !== $this->cached_result ) {
			return $this->cached_result;
		}

		$dimensions = array();

		// Calculate each dimension..
		$dimensions[ SLOS_DIMENSION_COOKIES ]            = $this->calculate_cookies_dimension();
		$dimensions[ SLOS_DIMENSION_LEGAL_DOCS ]         = $this->calculate_legal_docs_dimension();
		$dimensions[ SLOS_DIMENSION_GEO_RULES ]          = $this->calculate_geo_rules_dimension();
		$dimensions[ SLOS_DIMENSION_CONSENT_METADATA ]   = $this->calculate_consent_metadata_dimension();
		$dimensions[ SLOS_DIMENSION_SCANNING_FRESHNESS ] = $this->calculate_scanning_freshness_dimension();
		$dimensions[ SLOS_DIMENSION_BANNER_CONFIG ]      = $this->calculate_banner_config_dimension();

		// Calculate weighted aggregate..
		$weights   = slos_get_dimension_weights();
		$aggregate = 0;

		foreach ( $dimensions as $dimension => $data ) {
			if ( isset( $weights[ $dimension ] ) ) {
				$aggregate += $data['score'] * $weights[ $dimension ];
			}
		}

		$readiness_score = round( $aggregate );

		// Map to grade..
		$grade_info = $this->map_score_to_grade( $readiness_score );

		$result = array(
			'score'       => $readiness_score,
			'grade'       => $grade_info['grade'],
			'grade_class' => $grade_info['class'],
			'label'       => $grade_info['label'],
			'dimensions'  => $dimensions,
		);

		$this->cached_result = $result;

		return $result;
	}

	/**
	 * Calculate Operations Readiness Score
	 *
	 * Extends the compliance readiness score with DSR and Accessibility dimensions
	 * for a comprehensive operational health score across 8 dimensions.
	 *
	 * @since 3.1.1 Phase 2.1
	 * @param bool $use_cache Whether to use cached results
	 * @return array {
	 *     @type int    $score       Aggregate ops readiness score (0-100)
	 *     @type string $grade       Letter grade (A-F)
	 *     @type string $grade_class CSS class for grade
	 *     @type string $label       Human-readable label
	 *     @type array  $dimensions  Per-dimension scores and metadata
	 * }
	 */
	public function calculate_ops_readiness( bool $use_cache = true ): array {
		// Get existing 6 compliance dimensions..
		$compliance_result = $this->calculate( $use_cache );
		$dimensions        = $compliance_result['dimensions'];

		// Add DSR dimension..
		$dimensions['dsr'] = $this->calculate_dsr_dimension();

		// Add Accessibility dimension..
		$dimensions['accessibility'] = $this->calculate_accessibility_dimension();

		// Define weights for 8 dimensions (total = 1.0)..
		$ops_weights = array(
			SLOS_DIMENSION_COOKIES            => 0.20, // 20% - Cookie compliance
			SLOS_DIMENSION_LEGAL_DOCS         => 0.20, // 20% - Legal documentation
			SLOS_DIMENSION_GEO_RULES          => 0.10, // 10% - Geographic rules
			SLOS_DIMENSION_CONSENT_METADATA   => 0.10, // 10% - Consent tracking
			SLOS_DIMENSION_SCANNING_FRESHNESS => 0.05, // 5%  - Scan freshness
			SLOS_DIMENSION_BANNER_CONFIG      => 0.05, // 5%  - Banner setup
			'dsr'                             => 0.15, // 15% - DSR operations
			'accessibility'                   => 0.15, // 15% - Accessibility compliance
		);

		// Calculate weighted aggregate..
		$aggregate = 0;

		foreach ( $dimensions as $dimension => $data ) {
			if ( isset( $ops_weights[ $dimension ] ) ) {
				$aggregate += $data['score'] * $ops_weights[ $dimension ];
			}
		}

		$ops_readiness_score = round( $aggregate );

		// Map to grade..
		$grade_info = $this->map_score_to_grade( $ops_readiness_score );

		return array(
			'score'       => $ops_readiness_score,
			'grade'       => $grade_info['grade'],
			'grade_class' => $grade_info['class'],
			'label'       => $grade_info['label'],
			'dimensions'  => $dimensions,
		);
	}

	/**
	 * Calculate DSR (Data Subject Rights) dimension
	 *
	 * Evaluates DSR operational health based on:
	 * - SLA compliance rate (70%)
	 * - Overdue request penalty (30%)
	 *
	 * @since 3.1.1 Phase 2.1
	 * @return array {
	 *     @type int   $score   Dimension score (0-100)
	 *     @type array $details Detailed breakdown
	 * }
	 */
	private function calculate_dsr_dimension(): array {
		// Get DSR statistics..
		$dsr_service = new DSR_Service();
		$stats       = $dsr_service->get_ops_statistics();

		$score = 0;

		// If no requests exist, give baseline score of 80 (ready but unproven)..
		if ( $stats['total_requests'] === 0 ) {
			$score = 80;
		} else {
			// Base score from SLA compliance (70% weight)..
			$sla_score = $stats['sla_compliance_rate'];

			// Calculate overdue penalty (30% weight)..
			// Each overdue request reduces score..
			$overdue_penalty = 0;
			if ( $stats['overdue_requests'] > 0 ) {
				// Penalty scales with percentage of open requests that are overdue..
				$overdue_rate    = $stats['open_requests'] > 0
					? ( $stats['overdue_requests'] / $stats['open_requests'] )
					: 0;
				$overdue_penalty = round( $overdue_rate * 30 ); // Max 30 point penalty
			}

			// Combine SLA score and overdue penalty..
			$score = round( ( $sla_score * 0.70 ) + ( ( 100 - $overdue_penalty ) * 0.30 ) );
			$score = max( 0, min( 100, $score ) ); // Clamp to 0-100
		}

		return array(
			'score'   => $score,
			'details' => array(
				'total_requests'      => $stats['total_requests'],
				'open_requests'       => $stats['open_requests'],
				'overdue_requests'    => $stats['overdue_requests'],
				'sla_compliance_rate' => $stats['sla_compliance_rate'],
				'completed_requests'  => $stats['completed_requests'],
				'by_status'           => $stats['by_status'],
				'by_type'             => $stats['by_type'],
			),
		);
	}

	/**
	 * Calculate Accessibility dimension
	 *
	 * Evaluates accessibility compliance based on:
	 * - Accessibility score from scanner (60%)
	 * - Issue severity distribution (40%)
	 * - Scan freshness penalty
	 *
	 * @since 3.1.1 Phase 2.1
	 * @return array {
	 *     @type int   $score   Dimension score (0-100)
	 *     @type array $details Detailed breakdown
	 * }
	 */
	private function calculate_accessibility_dimension(): array {
		// Get Accessibility statistics..
		$scanner = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner();
		$stats   = $scanner->get_ops_statistics();

		$score = 0;

		// If never scanned, score is 0..
		if ( $stats['scan_freshness'] === 'never' ) {
			$score = 0;
		} else {
			// Base score from accessibility scanner score (60% weight)..
			$base_score = $stats['accessibility_score'];

			// Calculate issue severity penalty (40% weight)..
			$severity_score = 100;
			if ( $stats['total_issues'] > 0 ) {
				// Critical issues have highest impact..
				$critical_weight = 0.60; // 60% of severity weight
				$warning_weight  = 0.30; // 30% of severity weight
				$notice_weight   = 0.10; // 10% of severity weight

				// Calculate penalty based on issue distribution..
				$critical_penalty = min( 100, $stats['critical_issues'] * 5 ); // 5 points per critical
				$warning_penalty  = min( 50, $stats['warning_issues'] * 2 );   // 2 points per warning
				$notice_penalty   = min( 20, $stats['notice_issues'] * 1 );    // 1 point per notice

				$severity_score = 100 - (
					( $critical_penalty * $critical_weight ) +
					( $warning_penalty * $warning_weight ) +
					( $notice_penalty * $notice_weight )
				);
				$severity_score = max( 0, $severity_score );
			}

			// Combine base score and severity score..
			$score = round( ( $base_score * 0.60 ) + ( $severity_score * 0.40 ) );

			// Apply scan freshness penalty..
			switch ( $stats['scan_freshness'] ) {
				case 'stale':
					$score = round( $score * 0.85 ); // 15% penalty for stale scans
					break;
				case 'recent':
					// No penalty..
					break;
				case 'fresh':
					// No penalty, best case..
					break;
			}

			$score = max( 0, min( 100, $score ) ); // Clamp to 0-100
		}

		return array(
			'score'   => $score,
			'details' => array(
				'total_issues'        => $stats['total_issues'],
				'critical_issues'     => $stats['critical_issues'],
				'warning_issues'      => $stats['warning_issues'],
				'notice_issues'       => $stats['notice_issues'],
				'pages_scanned'       => $stats['pages_scanned'],
				'accessibility_score' => $stats['accessibility_score'],
				'pass_rate'           => $stats['pass_rate'],
				'last_scan_time'      => $stats['last_scan_time'],
				'scan_freshness'      => $stats['scan_freshness'],
				'hours_since_scan'    => $stats['hours_since_scan'],
				'by_severity'         => $stats['by_severity'],
			),
		);
	}

	/**
	 * Get score for a single dimension
	 *
	 * @since 3.1.0
	 * @param string $dimension Dimension constant
	 * @return array {
	 *     @type int    $score   Dimension score (0-100)
	 *     @type array  $details Detailed breakdown
	 * }
	 */
	public function calculate_dimension( string $dimension ): array {
		switch ( $dimension ) {
			case SLOS_DIMENSION_COOKIES:
				return $this->calculate_cookies_dimension();

			case SLOS_DIMENSION_LEGAL_DOCS:
				return $this->calculate_legal_docs_dimension();

			case SLOS_DIMENSION_GEO_RULES:
				return $this->calculate_geo_rules_dimension();

			case SLOS_DIMENSION_CONSENT_METADATA:
				return $this->calculate_consent_metadata_dimension();

			case SLOS_DIMENSION_SCANNING_FRESHNESS:
				return $this->calculate_scanning_freshness_dimension();

			case SLOS_DIMENSION_BANNER_CONFIG:
				return $this->calculate_banner_config_dimension();

			case 'dsr':
				return $this->calculate_dsr_dimension();

			case 'accessibility':
				return $this->calculate_accessibility_dimension();

			default:
				return array(
					'score'   => 0,
					'details' => array( 'error' => 'Invalid dimension' ),
				);
		}
	}

	/**
	 * Calculate COOKIES dimension (25% weight)
	 *
	 * @since 3.1.0
	 * @return array
	 */
	private function calculate_cookies_dimension(): array {
		$inventory        = get_option( 'slos_cookie_inventory', array() );
		$detected_cookies = get_option( 'slos_detected_cookies', array() );
		$scan_time        = get_option( 'slos_cookie_scan_time', null );

		// Use detected_cookies if inventory is empty (backward compatibility)..
		$cookies = ! empty( $inventory ) ? $inventory : $detected_cookies;

		$total         = count( $cookies );
		$score         = 0;
		$categorized   = 0;
		$uncategorized = 0;
		$unknown       = 0;

		if ( 0 === $total && null === $scan_time ) {
			// Never scanned..
			$score = 0;
		} elseif ( 0 === $total && null !== $scan_time ) {
			// Scan completed, no cookies found..
			$score = 100;
		} else {
			// Count categorized and uncategorized cookies..
			foreach ( $cookies as $cookie ) {
				$category = isset( $cookie['category'] ) ? strtolower( $cookie['category'] ) : 'unknown';
				$status   = isset( $cookie['status'] ) ? strtolower( $cookie['status'] ) : '';

				// Check if cookie is categorized..
				if ( ! empty( $category ) && 'unknown' !== $category && 'uncategorized' !== $status ) {
					++$categorized;
				} else {
					++$uncategorized;
				}

				// Count unknown providers..
				if ( empty( $cookie['provider'] ) || 'unknown' === strtolower( $cookie['provider'] ?? '' ) || 'unknown' === strtolower( $cookie['vendor'] ?? '' ) ) {
					++$unknown;
				}
			}

			// Calculate base score from categorization..
			$score = $total > 0 ? round( ( $categorized / $total ) * 100 ) : 0;

			// Apply penalty for unknown providers (5 points per unknown, less aggressive than before)..
			$provider_penalty = $unknown * 5;

			// Apply penalty for uncategorized cookies (proportional - more severe)..
			$uncategorized_penalty = $uncategorized > 0 ? round( ( $uncategorized / $total ) * 30 ) : 0;

			$score = max( 0, $score - $provider_penalty - $uncategorized_penalty );
		}

		return array(
			'score'   => $score,
			'details' => array(
				'total_cookies'         => $total,
				'categorized_cookies'   => $categorized,
				'uncategorized_cookies' => $uncategorized,
				'unknown_providers'     => $unknown,
				'scan_time'             => $scan_time,
			),
		);
	}

	/**
	 * Calculate LEGAL_DOCS dimension (25% weight)
	 *
	 * Uses Document_Hub_Service to check status of core compliance documents.
	 *
	 * @since 3.1.0
	 * @updated 3.1.1 - Now uses Document_Hub_Service and checks for staleness
	 * @return array
	 */
	private function calculate_legal_docs_dimension(): array {
		$hub_service = new Document_Hub_Service();
		$cards       = $hub_service->get_document_cards();

		// Focus on core compliance documents..
		$required_docs = array( 'cookie-policy', 'privacy-policy', 'accessibility-statement' );
		$total         = count( $required_docs );
		$published     = 0;
		$stale         = 0;
		$doc_status    = array();

		foreach ( $cards as $card ) {
			if ( in_array( $card['id'], $required_docs, true ) ) {
				$is_published = ( $card['status'] ?? 'not_generated' ) === 'published';
				$is_stale     = false;

				if ( $is_published && ! empty( $card['doc_id'] ) ) {
					$is_stale = $hub_service->is_document_stale( $card['doc_id'] );
					if ( ! $is_stale ) {
						++$published;
					}
				}

				if ( $is_stale ) {
					++$stale;
				}

				$doc_status[ $card['id'] ] = array(
					'status' => $card['status'],
					'stale'  => $is_stale,
				);
			}
		}

		// Base score: percentage of published, non-stale docs..
		$score = $total > 0 ? round( ( $published / $total ) * 100 ) : 100;

		// Penalty for stale documents (-10% per stale doc)..
		$staleness_penalty = $stale * 10;
		$score             = max( 0, $score - $staleness_penalty );

		return array(
			'score'   => $score,
			'details' => array(
				'required_count'    => $total,
				'published_count'   => $published,
				'stale_count'       => $stale,
				'doc_status'        => $doc_status,
				'staleness_penalty' => $staleness_penalty,
			),
		);
	}

	/**
	 * Calculate GEO_RULES dimension (15% weight)
	 *
	 * Evaluates geo targeting rules based on:
	 * - Active rule coverage for detected traffic regions
	 * - Legal document bindings (privacy-policy, cookie-policy)
	 * - Framework compliance (GDPR, CCPA, LGPD)
	 *
	 * @since 3.1.0
	 * @return array
	 */
	private function calculate_geo_rules_dimension(): array {
		// Use Geo_Rule_Matcher for rule retrieval...
		require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Services/Geo_Rule_Matcher.php';
		$matcher   = new \ShahiLegalFlowSuite\Services\Geo_Rule_Matcher();
		$geo_rules = $matcher->get_active_rules();

		$score = 0;

		// Get configured regions...
		$configured_regions = array();
		$legal_doc_coverage = 0;
		$legal_doc_total    = 0;

		foreach ( $geo_rules as $rule ) {
			$countries          = $rule['countries'] ?? array();
			$configured_regions = array_merge( $configured_regions, $countries );

			// Check legal document bindings...
			if ( ! empty( $rule['legal_docs'] ) ) {
				foreach ( $rule['legal_docs'] as $doc_key ) {
					++$legal_doc_total;
					// Check if document is published...
					$doc_status = $this->get_legal_doc_status( $doc_key );
					if ( 'published' === $doc_status || 'active' === $doc_status ) {
						++$legal_doc_coverage;
					}
				}
			}
		}

		$configured_regions = array_unique( $configured_regions );

		// Get detected traffic regions (last 30 days)...
		$detected_regions = $this->get_distinct_consent_regions( 30 );

		if ( empty( $detected_regions ) ) {
			// No traffic data yet - check if at least 1 rule is configured...
			$base_score = count( $configured_regions ) > 0 ? 50 : 0;
		} else {
			// Calculate coverage...
			$covered   = array();
			$wildcards = array( '*', 'GLOBAL' );

			foreach ( $detected_regions as $detected ) {
				// Check for direct match...
				if ( in_array( $detected, $configured_regions, true ) ) {
					$covered[] = $detected;
					continue;
				}

				// Check for wildcard match...
				foreach ( $wildcards as $wildcard ) {
					if ( in_array( $wildcard, $configured_regions, true ) ) {
						$covered[] = $detected;
						break;
					}
				}

				// Check for EU-ALL or EEA-ALL...
				if ( in_array( 'EU-ALL', $configured_regions, true ) || in_array( 'EEA-ALL', $configured_regions, true ) ) {
					if ( $matcher->is_eea_country( $detected ) ) {
						$covered[] = $detected;
					}
				}
			}

			$covered    = array_unique( $covered );
			$coverage   = count( $detected_regions ) > 0 ? count( $covered ) / count( $detected_regions ) : 0;
			$base_score = round( $coverage * 100 );
		}

		// Apply legal document bonus (up to 10 points)...
		$legal_doc_bonus = 0;
		if ( $legal_doc_total > 0 ) {
			$legal_doc_ratio = $legal_doc_coverage / $legal_doc_total;
			$legal_doc_bonus = round( $legal_doc_ratio * 10 );
		}

		$score = min( 100, $base_score + $legal_doc_bonus );

		return array(
			'score'   => $score,
			'details' => array(
				'configured_regions' => $configured_regions,
				'detected_regions'   => $detected_regions,
				'coverage'           => $base_score / 100,
				'legal_docs_bound'   => $legal_doc_coverage,
				'legal_docs_total'   => $legal_doc_total,
				'legal_doc_bonus'    => $legal_doc_bonus,
			),
		);
	}

	/**
	 * Get legal document status
	 *
	 * @since 3.1.1
	 * @param string $doc_key Document key (privacy-policy, cookie-policy, etc.).
	 * @return string Status (published, draft, missing).
	 */
	private function get_legal_doc_status( string $doc_key ): string {
		$legal_docs = get_option( 'slos_legal_documents', array() );

		foreach ( $legal_docs as $doc ) {
			if ( $doc['key'] === $doc_key ) {
				return $doc['status'] ?? 'draft';
			}
		}

		return 'missing';
	}

	/**
	 * Calculate CONSENT_METADATA dimension (15% weight)
	 *
	 * @since 3.1.0
	 * @return array
	 */
	private function calculate_consent_metadata_dimension(): array {
		$recent_consents = $this->get_recent_consents( 30, 1000 );
		$score           = 100; // Default if no consents

		if ( ! empty( $recent_consents ) ) {
			$complete = 0;
			$total    = count( $recent_consents );

			foreach ( $recent_consents as $consent ) {
				$has_country  = ! empty( $consent->country_code );
				$has_language = ! empty( $consent->language );

				// Check for banner_version in metadata..
				$metadata = $consent->metadata ?? array();
				if ( is_string( $metadata ) ) {
					$metadata = json_decode( $metadata, true );
				}
				$has_version = ! empty( $metadata['banner_version'] ) || ! empty( $consent->banner_version );

				if ( $has_country && $has_language && $has_version ) {
					++$complete;
				}
			}

			$score = $total > 0 ? round( ( $complete / $total ) * 100 ) : 100;
		}

		return array(
			'score'   => $score,
			'details' => array(
				'total_consents'    => count( $recent_consents ),
				'complete_consents' => $complete ?? 0,
			),
		);
	}

	/**
	 * Calculate SCANNING_FRESHNESS dimension (10% weight)
	 *
	 * @since 3.1.0
	 * @return array
	 */
	private function calculate_scanning_freshness_dimension(): array {
		$last_scan = get_option( 'slos_cookie_scan_time', null );
		$score     = 0;

		if ( null === $last_scan ) {
			$score    = 0;
			$days_ago = null;
		} else {
			$last_scan_time  = strtotime( $last_scan );
			$current_time    = current_time( 'timestamp' );
			$days_since_scan = ( $current_time - $last_scan_time ) / DAY_IN_SECONDS;

			if ( $days_since_scan <= 7 ) {
				$score = 100;
			} elseif ( $days_since_scan <= 30 ) {
				$score = 70;
			} elseif ( $days_since_scan <= 90 ) {
				$score = 40;
			} else {
				$score = 10;
			}

			$days_ago = round( $days_since_scan, 1 );
		}

		return array(
			'score'   => $score,
			'details' => array(
				'last_scan'     => $last_scan,
				'days_ago'      => $days_ago,
				'never_scanned' => null === $last_scan,
			),
		);
	}

	/**
	 * Calculate BANNER_CONFIG dimension (10% weight)
	 *
	 * @since 3.1.0
	 * @return array
	 */
	private function calculate_banner_config_dimension(): array {
		$settings = get_option( 'shahi_legalflowsuite_settings', array() );

		$required_fields = array(
			'banner_enabled',
			'primary_button_text',
			'reject_button_enabled',
			'preferences_link_enabled',
			'privacy_policy_link',
		);

		$configured   = 0;
		$field_status = array();

		foreach ( $required_fields as $field ) {
			$is_set                 = isset( $settings[ $field ] ) && ! empty( $settings[ $field ] );
			$field_status[ $field ] = $is_set;
			if ( $is_set ) {
				++$configured;
			}
		}

		$score = count( $required_fields ) > 0 ? round( ( $configured / count( $required_fields ) ) * 100 ) : 0;

		return array(
			'score'   => $score,
			'details' => array(
				'required_fields'   => count( $required_fields ),
				'configured_fields' => $configured,
				'field_status'      => $field_status,
			),
		);
	}

	/**
	 * Map score to grade
	 *
	 * @since 3.1.0
	 * @param int $score Score value (0-100)
	 * @return array Grade info
	 */
	private function map_score_to_grade( int $score ): array {
		$mappings = slos_get_grade_mappings();

		foreach ( $mappings as $mapping ) {
			if ( $score >= $mapping['min'] && $score <= $mapping['max'] ) {
				return $mapping;
			}
		}

		// Fallback to F grade..
		return $mappings[ count( $mappings ) - 1 ];
	}

	/**
	 * Get legal page ID by slug
	 *
	 * @since 3.1.0
	 * @param string $slug Page slug
	 * @return int Page ID or 0
	 */
	private function get_legal_page_id( string $slug ): int {
		$legal_pages = get_option( 'slos_legal_pages', array() );
		return absint( $legal_pages[ $slug ] ?? 0 );
	}

	/**
	 * Get distinct regions from consent records
	 *
	 * @since 3.1.0
	 * @param int $days Number of days to look back
	 * @return array Array of unique country codes
	 */
	private function get_distinct_consent_regions( int $days ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'slos_consent';

		// Check if country_code column exists (migration may not have run yet)...
		$columns      = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table ) );
		$column_names = array_map(
			function ( $col ) {
				return $col->Field;
			},
			$columns
		);

		if ( ! in_array( 'country_code', $column_names, true ) ) {
			// Column doesn't exist yet, return empty array..
			return array();
		}

		$since = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT country_code FROM {$table} WHERE country_code IS NOT NULL AND country_code != '' AND created_at >= %s",
				$since
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Get recent consent records
	 *
	 * @since 3.1.0
	 * @param int $days  Number of days to look back
	 * @param int $limit Maximum number of records
	 * @return array Array of consent objects
	 */
	private function get_recent_consents( int $days, int $limit = 1000 ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'slos_consent';
		$since = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE created_at >= %s ORDER BY created_at DESC LIMIT %d",
				$since,
				$limit
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Clear cached calculation results
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cached_result = null;
	}
}
