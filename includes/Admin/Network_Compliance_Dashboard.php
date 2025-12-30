<?php
/**
 * Network Compliance Dashboard (Multisite)
 *
 * Network admin view aggregating compliance stats across all subsites.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Services\Compliance_Score_Calculator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Network_Compliance_Dashboard Class
 *
 * Multisite network overview of compliance across all sites.
 *
 * @since 3.1.1
 */
class Network_Compliance_Dashboard {

	/**
	 * Register menu and hooks
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function init() {
		if ( ! is_multisite() ) {
			return;
		}

		add_action( 'network_admin_menu', array( $this, 'add_network_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add network admin menu
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function add_network_menu() {
		add_menu_page(
			__( 'Compliance Network', 'shahi-legalflowsuite' ),
			__( 'Compliance', 'shahi-legalflowsuite' ),
			'manage_network_options',
			'slos-network-compliance',
			array( $this, 'render' ),
			'dashicons-shield-alt',
			31
		);
	}

	/**
	 * Enqueue assets
	 *
	 * @since 3.1.1
	 * @param string $hook Current admin page hook
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_slos-network-compliance' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'slos-network-compliance',
			SHAHI_LEGALFLOWSUITE_URL . 'assets/css/admin-dashboard.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);
	}

	/**
	 * Render network dashboard
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		$network_stats = $this->get_network_stats();
		$sites         = $this->get_sites_data();

		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/network/compliance-overview.php';
	}

	/**
	 * Get network-wide statistics
	 *
	 * @since 3.1.1
	 * @return array Network stats
	 */
	private function get_network_stats() {
		$sites = get_sites( array( 'number' => 9999 ) );
		
		$total_sites      = count( $sites );
		$scores           = array();
		$critical_sites   = 0;
		$low_score_sites  = 0;
		$issues_by_type   = array(
			'banner'        => 0,
			'geo_rules'     => 0,
			'legal_docs'    => 0,
			'accessibility' => 0,
			'dsr'           => 0,
		);

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			// Get compliance score
			$calculator = new Compliance_Score_Calculator();
			$result     = $calculator->calculate();
			$score      = $result['score'] ?? 0;
			$scores[]   = $score;

			// Count critical sites (score < 40)
			if ( $score < 40 ) {
				$critical_sites++;
			}

			// Count low score sites (score < 60)
			if ( $score < 60 ) {
				$low_score_sites++;
			}

			// Count issues by dimension
			$dimensions = $result['dimensions'] ?? array();
			foreach ( $dimensions as $dimension ) {
				$dim_score = $dimension['score'] ?? 100;
				if ( $dim_score < 70 ) {
					// Map dimension constants to issue types
					$dim_name = strtolower( str_replace( array( 'CONSENT_', 'COOKIES' ), array( '', 'banner' ), $dimension['id'] ?? '' ) );
					if ( isset( $issues_by_type[ $dim_name ] ) ) {
						$issues_by_type[ $dim_name ]++;
					}
				}
			}

			restore_current_blog();
		}

		$avg_score = $total_sites > 0 ? round( array_sum( $scores ) / $total_sites ) : 0;

		return array(
			'total_sites'      => $total_sites,
			'avg_score'        => $avg_score,
			'critical_sites'   => $critical_sites,
			'low_score_sites'  => $low_score_sites,
			'issues_by_type'   => $issues_by_type,
			'scores'           => $scores,
		);
	}

	/**
	 * Get sites data with compliance stats
	 *
	 * @since 3.1.1
	 * @return array Sites data
	 */
	private function get_sites_data() {
		$sites      = get_sites( array( 'number' => 9999 ) );
		$sites_data = array();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$calculator = new Compliance_Score_Calculator();
			$result     = $calculator->calculate();
			$score      = $result['score'] ?? 0;
			$grade      = $result['grade'] ?? 'F';

			// Count issues (dimensions with score < 70)
			$issues_count = 0;
			$dimensions   = $result['dimensions'] ?? array();
			foreach ( $dimensions as $dimension ) {
				if ( ( $dimension['score'] ?? 100 ) < 70 ) {
					$issues_count++;
				}
			}

			// Get last scan time
			$last_scan = get_option( 'slos_cookie_scan_time', '' );
			if ( $last_scan ) {
				$last_scan = human_time_diff( strtotime( $last_scan ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalflowsuite' );
			} else {
				$last_scan = __( 'Never', 'shahi-legalflowsuite' );
			}

			$sites_data[] = array(
				'blog_id'      => $site->blog_id,
				'name'         => get_bloginfo( 'name' ),
				'url'          => get_home_url(),
				'score'        => $score,
				'grade'        => $grade,
				'issues_count' => $issues_count,
				'last_scan'    => $last_scan,
				'admin_url'    => admin_url( 'admin.php?page=slos-compliance' ),
			);

			restore_current_blog();
		}

		// Sort by score (lowest first)
		usort( $sites_data, function( $a, $b ) {
			return $a['score'] - $b['score'];
		});

		return $sites_data;
	}

	/**
	 * Get grade color class
	 *
	 * @since 3.1.1
	 * @param string $grade Grade letter
	 * @return string CSS class
	 */
	public static function get_grade_color( $grade ) {
		$colors = array(
			'A' => 'success',
			'B' => 'info',
			'C' => 'warning',
			'D' => 'warning',
			'F' => 'error',
		);

		return $colors[ $grade ] ?? 'muted';
	}

	/**
	 * Get score color class
	 *
	 * @since 3.1.1
	 * @param int $score Score value (0-100)
	 * @return string CSS class
	 */
	public static function get_score_color( $score ) {
		if ( $score >= 80 ) {
			return 'success';
		} elseif ( $score >= 60 ) {
			return 'info';
		} elseif ( $score >= 40 ) {
			return 'warning';
		} else {
			return 'error';
		}
	}
}
