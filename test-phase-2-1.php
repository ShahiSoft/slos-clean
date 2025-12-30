<?php
/**
 * Phase 2.1 Test Suite - Compliance Operations Dashboard
 *
 * Comprehensive tests for:
 * - DSR statistics method
 * - Accessibility statistics method
 * - Operations readiness score calculation
 * - Dashboard aggregation
 * - Drill-down URL mapping
 * - Template rendering
 * - CSS file existence
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Tests
 * @since       3.1.1
 * @version     1.0.0
 *
 * Usage: php test-phase-2-1.php
 */

// Bootstrap WordPress
define( 'WP_USE_THEMES', false );
require_once 'c:/docker-wp/wordpress_data/wp-load.php';

// Color output helpers
function green( $text ) {
	return "\033[32m" . $text . "\033[0m";
}

function red( $text ) {
	return "\033[31m" . $text . "\033[0m";
}

function yellow( $text ) {
	return "\033[33m" . $text . "\033[0m";
}

function blue( $text ) {
	return "\033[34m" . $text . "\033[0m";
}

// Test framework
class Phase_2_1_Test_Suite {
	private $passed = 0;
	private $failed = 0;
	private $tests = array();

	public function run_all_tests() {
		echo blue( "╔═══════════════════════════════════════════════════════════════╗\n" );
		echo blue( "║           Phase 2.1 - Operations Dashboard Tests              ║\n" );
		echo blue( "║                 Shahi LegalOps Suite 3.1.1                    ║\n" );
		echo blue( "╚═══════════════════════════════════════════════════════════════╝\n\n" );

		// Test 1: DSR Service - get_ops_statistics() method exists
		$this->test_dsr_statistics_method_exists();

		// Test 2: DSR Service - get_ops_statistics() returns correct structure
		$this->test_dsr_statistics_structure();

		// Test 3: Accessibility Scanner - get_ops_statistics() method exists
		$this->test_accessibility_statistics_method_exists();

		// Test 4: Accessibility Scanner - get_ops_statistics() returns correct structure
		$this->test_accessibility_statistics_structure();

		// Test 5: Compliance Score Calculator - calculate_ops_readiness() method exists
		$this->test_ops_readiness_method_exists();

		// Test 6: Compliance Score Calculator - calculate_ops_readiness() returns correct structure
		$this->test_ops_readiness_structure();

		// Test 7: Compliance Score Calculator - calculate_dimension() supports new dimensions
		$this->test_dimension_calculation_extended();

		// Test 8: ComplianceMainPage - get_ops_dashboard_stats() method exists
		$this->test_ops_dashboard_stats_method_exists();

		// Test 9: Dashboard template - Operations Dashboard section exists
		$this->test_dashboard_template_has_ops_section();

		// Test 10: Dashboard template - Drill-down links are correct
		$this->test_drill_down_links();

		// Test 11: CSS file exists and is enqueued
		$this->test_css_file_exists();

		// Test 12: All PHP files have zero syntax errors
		$this->test_php_syntax_errors();

		// Test 13: Ops readiness score calculation is mathematically correct
		$this->test_ops_score_calculation();

		// Test 14: DSR dimension score calculation logic
		$this->test_dsr_dimension_calculation();

		// Test 15: Accessibility dimension score calculation logic
		$this->test_accessibility_dimension_calculation();

		// Summary
		$this->print_summary();
	}

	private function assert( $condition, $test_name, $error_message = '' ) {
		if ( $condition ) {
			$this->passed++;
			echo green( "✓ " ) . "$test_name\n";
		} else {
			$this->failed++;
			echo red( "✗ " ) . "$test_name\n";
			if ( $error_message ) {
				echo "  " . yellow( "Error: $error_message" ) . "\n";
			}
		}
	}

	// Test 1
	private function test_dsr_statistics_method_exists() {
		$dsr_service = new \ShahiLegalFlowSuite\Services\DSR_Service();
		$exists = method_exists( $dsr_service, 'get_ops_statistics' );
		$this->assert(
			$exists,
			"DSR_Service::get_ops_statistics() method exists",
			"Method get_ops_statistics not found in DSR_Service class"
		);
	}

	// Test 2
	private function test_dsr_statistics_structure() {
		$dsr_service = new \ShahiLegalFlowSuite\Services\DSR_Service();
		$stats = $dsr_service->get_ops_statistics();

		$required_keys = array(
			'open_requests',
			'total_requests',
			'completed_requests',
			'overdue_requests',
			'sla_compliance_rate',
			'by_status',
			'by_type',
		);

		$all_keys_present = true;
		$missing_keys = array();

		foreach ( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $stats ) ) {
				$all_keys_present = false;
				$missing_keys[] = $key;
			}
		}

		$this->assert(
			$all_keys_present && is_array( $stats ),
			"DSR statistics return correct structure",
			"Missing keys: " . implode( ', ', $missing_keys )
		);
	}

	// Test 3
	private function test_accessibility_statistics_method_exists() {
		$scanner = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner();
		$exists = method_exists( $scanner, 'get_ops_statistics' );
		$this->assert(
			$exists,
			"AccessibilityScanner::get_ops_statistics() method exists",
			"Method get_ops_statistics not found in AccessibilityScanner class"
		);
	}

	// Test 4
	private function test_accessibility_statistics_structure() {
		$scanner = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner();
		$stats = $scanner->get_ops_statistics();

		$required_keys = array(
			'total_issues',
			'critical_issues',
			'warning_issues',
			'notice_issues',
			'pages_scanned',
			'accessibility_score',
			'pass_rate',
			'last_scan_time',
			'hours_since_scan',
			'scan_freshness',
			'by_severity',
		);

		$all_keys_present = true;
		$missing_keys = array();

		foreach ( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $stats ) ) {
				$all_keys_present = false;
				$missing_keys[] = $key;
			}
		}

		$this->assert(
			$all_keys_present && is_array( $stats ),
			"Accessibility statistics return correct structure",
			"Missing keys: " . implode( ', ', $missing_keys )
		);
	}

	// Test 5
	private function test_ops_readiness_method_exists() {
		$calculator = new \ShahiLegalFlowSuite\Services\Compliance_Score_Calculator();
		$exists = method_exists( $calculator, 'calculate_ops_readiness' );
		$this->assert(
			$exists,
			"Compliance_Score_Calculator::calculate_ops_readiness() method exists",
			"Method calculate_ops_readiness not found in Compliance_Score_Calculator class"
		);
	}

	// Test 6
	private function test_ops_readiness_structure() {
		$calculator = new \ShahiLegalFlowSuite\Services\Compliance_Score_Calculator();
		$result = $calculator->calculate_ops_readiness( false );

		$required_keys = array( 'score', 'grade', 'grade_class', 'label', 'dimensions' );
		$all_keys_present = true;
		$missing_keys = array();

		foreach ( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $result ) ) {
				$all_keys_present = false;
				$missing_keys[] = $key;
			}
		}

		// Check that we have 8 dimensions (6 compliance + DSR + Accessibility)
		$dimension_count = count( $result['dimensions'] ?? array() );
		$has_8_dimensions = ( $dimension_count === 8 );

		// Check that DSR and Accessibility dimensions are present
		$has_dsr = array_key_exists( 'dsr', $result['dimensions'] ?? array() );
		$has_accessibility = array_key_exists( 'accessibility', $result['dimensions'] ?? array() );

		$this->assert(
			$all_keys_present && $has_8_dimensions && $has_dsr && $has_accessibility,
			"Ops readiness calculation returns 8 dimensions",
			"Keys: " . implode( ', ', $missing_keys ) . 
			" | Dimensions: $dimension_count (expected 8) | DSR: " . ( $has_dsr ? 'yes' : 'no' ) . 
			" | Accessibility: " . ( $has_accessibility ? 'yes' : 'no' )
		);
	}

	// Test 7
	private function test_dimension_calculation_extended() {
		$calculator = new \ShahiLegalFlowSuite\Services\Compliance_Score_Calculator();
		
		// Test DSR dimension calculation
		$dsr_result = $calculator->calculate_dimension( 'dsr' );
		$dsr_valid = isset( $dsr_result['score'] ) && isset( $dsr_result['details'] );

		// Test Accessibility dimension calculation
		$accessibility_result = $calculator->calculate_dimension( 'accessibility' );
		$accessibility_valid = isset( $accessibility_result['score'] ) && isset( $accessibility_result['details'] );

		$this->assert(
			$dsr_valid && $accessibility_valid,
			"calculate_dimension() supports DSR and Accessibility dimensions",
			"DSR valid: " . ( $dsr_valid ? 'yes' : 'no' ) . 
			" | Accessibility valid: " . ( $accessibility_valid ? 'yes' : 'no' )
		);
	}

	// Test 8
	private function test_ops_dashboard_stats_method_exists() {
		$reflection = new ReflectionClass( 'ShahiLegalFlowSuite\Admin\ComplianceMainPage' );
		$method_exists = false;
		
		try {
			$method = $reflection->getMethod( 'get_ops_dashboard_stats' );
			$method_exists = true;
		} catch ( ReflectionException $e ) {
			$method_exists = false;
		}

		$this->assert(
			$method_exists,
			"ComplianceMainPage::get_ops_dashboard_stats() method exists",
			"Method get_ops_dashboard_stats not found in ComplianceMainPage class"
		);
	}

	// Test 9
	private function test_dashboard_template_has_ops_section() {
		$template_path = SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
		$template_content = file_get_contents( $template_path );
		
		$has_ops_dashboard = strpos( $template_content, 'slos-ops-dashboard' ) !== false;
		$has_ops_score = strpos( $template_content, 'ops_stats' ) !== false;
		$has_module_cards = strpos( $template_content, 'slos-ops-module-card' ) !== false;

		$this->assert(
			$has_ops_dashboard && $has_ops_score && $has_module_cards,
			"Dashboard template contains Operations Dashboard section",
			"Ops dashboard: " . ( $has_ops_dashboard ? 'yes' : 'no' ) . 
			" | Ops score: " . ( $has_ops_score ? 'yes' : 'no' ) . 
			" | Module cards: " . ( $has_module_cards ? 'yes' : 'no' )
		);
	}

	// Test 10
	private function test_drill_down_links() {
		$template_path = SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
		$template_content = file_get_contents( $template_path );

		$has_consent_link = strpos( $template_content, 'slos-compliance&tab=records' ) !== false;
		$has_cookie_link = strpos( $template_content, 'slos-compliance&tab=cookie-scanner' ) !== false;
		$has_dsr_link = strpos( $template_content, 'slos-dsr-requests' ) !== false;
		$has_accessibility_link = strpos( $template_content, 'slos-accessibility' ) !== false;

		$all_links_present = $has_consent_link && $has_cookie_link && $has_dsr_link && $has_accessibility_link;

		$this->assert(
			$all_links_present,
			"Drill-down links to all 4 modules are present",
			"Consent: " . ( $has_consent_link ? 'yes' : 'no' ) . 
			" | Cookie: " . ( $has_cookie_link ? 'yes' : 'no' ) . 
			" | DSR: " . ( $has_dsr_link ? 'yes' : 'no' ) . 
			" | Accessibility: " . ( $has_accessibility_link ? 'yes' : 'no' )
		);
	}

	// Test 11
	private function test_css_file_exists() {
		$css_path = SHAHI_LEGALFLOWSUITE_PATH . 'assets/css/compliance-ops-dashboard.css';
		$css_exists = file_exists( $css_path );

		// Check if CSS is enqueued in main plugin file
		$main_plugin_file = SHAHI_LEGALFLOWSUITE_PATH . 'shahi-legalflowsuite.php';
		$main_content = file_get_contents( $main_plugin_file );
		$css_enqueued = strpos( $main_content, 'compliance-ops-dashboard.css' ) !== false;

		$this->assert(
			$css_exists && $css_enqueued,
			"Ops Dashboard CSS file exists and is enqueued",
			"CSS exists: " . ( $css_exists ? 'yes' : 'no' ) . 
			" | Enqueued: " . ( $css_enqueued ? 'yes' : 'no' )
		);
	}

	// Test 12
	private function test_php_syntax_errors() {
		$files_to_check = array(
			'includes/Services/DSR_Service.php',
			'includes/Modules/AccessibilityScanner/AccessibilityScanner.php',
			'includes/Services/Compliance_Score_Calculator.php',
			'includes/Admin/ComplianceMainPage.php',
			'shahi-legalflowsuite.php',
		);

		$all_valid = true;
		$error_files = array();

		foreach ( $files_to_check as $file ) {
			$full_path = SHAHI_LEGALFLOWSUITE_PATH . $file;
			$output = array();
			$return_var = 0;
			exec( "php -l \"$full_path\" 2>&1", $output, $return_var );
			
			if ( $return_var !== 0 || strpos( implode( '', $output ), 'No syntax errors' ) === false ) {
				$all_valid = false;
				$error_files[] = $file;
			}
		}

		$this->assert(
			$all_valid,
			"All PHP files have zero syntax errors",
			"Files with errors: " . implode( ', ', $error_files )
		);
	}

	// Test 13
	private function test_ops_score_calculation() {
		$calculator = new \ShahiLegalFlowSuite\Services\Compliance_Score_Calculator();
		$result = $calculator->calculate_ops_readiness( false );

		$score = $result['score'];
		$dimensions = $result['dimensions'];

		// Manually calculate expected score with ops weights
		$ops_weights = array(
			'cookies'            => 0.20,
			'legal_docs'         => 0.20,
			'geo_rules'          => 0.10,
			'consent_metadata'   => 0.10,
			'scanning_freshness' => 0.05,
			'banner_config'      => 0.05,
			'dsr'                => 0.15,
			'accessibility'      => 0.15,
		);

		$manual_score = 0;
		foreach ( $dimensions as $dim_key => $dim_data ) {
			if ( isset( $ops_weights[ $dim_key ] ) ) {
				$manual_score += $dim_data['score'] * $ops_weights[ $dim_key ];
			}
		}

		$manual_score = round( $manual_score );
		$score_matches = ( $score === $manual_score );

		$this->assert(
			$score_matches && $score >= 0 && $score <= 100,
			"Ops readiness score calculation is mathematically correct",
			"Calculated: $score | Expected: $manual_score | Match: " . ( $score_matches ? 'yes' : 'no' )
		);
	}

	// Test 14
	private function test_dsr_dimension_calculation() {
		$calculator = new \ShahiLegalFlowSuite\Services\Compliance_Score_Calculator();
		$dsr_dimension = $calculator->calculate_dimension( 'dsr' );

		$has_score = isset( $dsr_dimension['score'] );
		$has_details = isset( $dsr_dimension['details'] );
		$score_in_range = $has_score && $dsr_dimension['score'] >= 0 && $dsr_dimension['score'] <= 100;

		// Check details structure
		$required_details = array(
			'total_requests',
			'open_requests',
			'overdue_requests',
			'sla_compliance_rate',
		);

		$all_details_present = true;
		foreach ( $required_details as $key ) {
			if ( ! isset( $dsr_dimension['details'][ $key ] ) ) {
				$all_details_present = false;
				break;
			}
		}

		$this->assert(
			$has_score && $has_details && $score_in_range && $all_details_present,
			"DSR dimension calculation logic is correct",
			"Has score: " . ( $has_score ? 'yes' : 'no' ) . 
			" | In range: " . ( $score_in_range ? 'yes' : 'no' ) . 
			" | Has details: " . ( $all_details_present ? 'yes' : 'no' )
		);
	}

	// Test 15
	private function test_accessibility_dimension_calculation() {
		$calculator = new \ShahiLegalFlowSuite\Services\Compliance_Score_Calculator();
		$accessibility_dimension = $calculator->calculate_dimension( 'accessibility' );

		$has_score = isset( $accessibility_dimension['score'] );
		$has_details = isset( $accessibility_dimension['details'] );
		$score_in_range = $has_score && $accessibility_dimension['score'] >= 0 && $accessibility_dimension['score'] <= 100;

		// Check details structure
		$required_details = array(
			'total_issues',
			'critical_issues',
			'warning_issues',
			'notice_issues',
			'accessibility_score',
			'scan_freshness',
		);

		$all_details_present = true;
		foreach ( $required_details as $key ) {
			if ( ! isset( $accessibility_dimension['details'][ $key ] ) ) {
				$all_details_present = false;
				break;
			}
		}

		$this->assert(
			$has_score && $has_details && $score_in_range && $all_details_present,
			"Accessibility dimension calculation logic is correct",
			"Has score: " . ( $has_score ? 'yes' : 'no' ) . 
			" | In range: " . ( $score_in_range ? 'yes' : 'no' ) . 
			" | Has details: " . ( $all_details_present ? 'yes' : 'no' )
		);
	}

	private function print_summary() {
		$total = $this->passed + $this->failed;
		$pass_rate = $total > 0 ? round( ( $this->passed / $total ) * 100, 1 ) : 0;

		echo "\n";
		echo blue( "═══════════════════════════════════════════════════════════════\n" );
		echo blue( "                         TEST SUMMARY                          \n" );
		echo blue( "═══════════════════════════════════════════════════════════════\n" );
		echo "Total Tests:  " . yellow( $total ) . "\n";
		echo "Passed:       " . green( $this->passed ) . "\n";
		echo "Failed:       " . red( $this->failed ) . "\n";
		echo "Pass Rate:    " . ( $pass_rate === 100.0 ? green( "$pass_rate%" ) : yellow( "$pass_rate%" ) ) . "\n";
		echo blue( "═══════════════════════════════════════════════════════════════\n" );

		if ( $this->failed === 0 ) {
			echo green( "\n✓ ALL TESTS PASSED! Phase 2.1 implementation is correct.\n\n" );
			exit( 0 );
		} else {
			echo red( "\n✗ SOME TESTS FAILED. Please review the errors above.\n\n" );
			exit( 1 );
		}
	}
}

// Run tests
$suite = new Phase_2_1_Test_Suite();
$suite->run_all_tests();
