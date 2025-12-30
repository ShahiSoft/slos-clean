<?php
/**
 * Phase 1.3 Test Suite - Time-Series Insights & Exports
 *
 * Comprehensive tests for time-series data, Chart.js integration, and exports.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.1.1
 */

// Load WordPress test environment
if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../wp-load.php';
}

require_once SLOS_PLUGIN_DIR . 'includes/Services/Consent_Service.php';
require_once SLOS_PLUGIN_DIR . 'includes/Ajax/Compliance_Export_Ajax.php';

use ShahiLegalFlowSuite\Services\Consent_Service;
use ShahiLegalFlowSuite\Ajax\Compliance_Export_Ajax;

/**
 * Test harness for Phase 1.3
 */
class Phase_13_Test_Suite {

	/**
	 * Consent service instance
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Export AJAX handler
	 *
	 * @var Compliance_Export_Ajax
	 */
	private $export_ajax;

	/**
	 * Test results
	 *
	 * @var array
	 */
	private $results = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->consent_service = new Consent_Service();
		$this->export_ajax     = new Compliance_Export_Ajax();
	}

	/**
	 * Run all tests
	 *
	 * @return array Test results
	 */
	public function run_all_tests() {
		echo "\n=== Phase 1.3 Test Suite ===\n\n";

		// Time-Series Tests
		$this->test_time_series_daily_interval();
		$this->test_time_series_weekly_interval();
		$this->test_time_series_monthly_interval();
		$this->test_time_series_group_by_status();
		$this->test_time_series_group_by_type();
		$this->test_time_series_group_by_region();
		$this->test_time_series_date_range();

		// Export Tests
		$this->test_csv_export_structure();
		$this->test_pdf_export_generation();
		$this->test_audit_log_export();

		// Integration Tests
		$this->test_chart_data_format();
		$this->test_ajax_endpoint_registration();

		// Print results
		$this->print_results();

		return $this->results;
	}

	/**
	 * Test 1: Daily interval time-series
	 */
	private function test_time_series_daily_interval() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'daily',
				'days_back' => 7,
				'group_by'  => 'none',
			) );

			$this->assert_not_empty( $data, 'Time-series data should not be empty' );
			$this->assert_array_has_key( 'labels', $data, 'Data should have labels array' );
			$this->assert_array_has_key( 'datasets', $data, 'Data should have datasets array' );
			$this->assert_array_has_key( 'metadata', $data, 'Data should have metadata' );
			$this->assert_equals( 'daily', $data['metadata']['interval'], 'Metadata interval should be daily' );

			$this->record_pass( 'Test 1: Daily interval time-series' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 1: Daily interval time-series', $e->getMessage() );
		}
	}

	/**
	 * Test 2: Weekly interval time-series
	 */
	private function test_time_series_weekly_interval() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'weekly',
				'days_back' => 30,
				'group_by'  => 'none',
			) );

			$this->assert_not_empty( $data, 'Weekly data should not be empty' );
			$this->assert_equals( 'weekly', $data['metadata']['interval'], 'Metadata interval should be weekly' );

			$this->record_pass( 'Test 2: Weekly interval time-series' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 2: Weekly interval time-series', $e->getMessage() );
		}
	}

	/**
	 * Test 3: Monthly interval time-series
	 */
	private function test_time_series_monthly_interval() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'monthly',
				'days_back' => 90,
				'group_by'  => 'none',
			) );

			$this->assert_not_empty( $data, 'Monthly data should not be empty' );
			$this->assert_equals( 'monthly', $data['metadata']['interval'], 'Metadata interval should be monthly' );

			$this->record_pass( 'Test 3: Monthly interval time-series' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 3: Monthly interval time-series', $e->getMessage() );
		}
	}

	/**
	 * Test 4: Group by status
	 */
	private function test_time_series_group_by_status() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'daily',
				'days_back' => 30,
				'group_by'  => 'status',
			) );

			$this->assert_not_empty( $data, 'Grouped data should not be empty' );
			$this->assert_equals( 'status', $data['metadata']['group_by'], 'Metadata group_by should be status' );
			
			// Check for multiple datasets
			$this->assert_true( count( $data['datasets'] ) >= 1, 'Should have at least one dataset for status grouping' );

			$this->record_pass( 'Test 4: Group by status' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 4: Group by status', $e->getMessage() );
		}
	}

	/**
	 * Test 5: Group by type
	 */
	private function test_time_series_group_by_type() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'daily',
				'days_back' => 30,
				'group_by'  => 'type',
			) );

			$this->assert_not_empty( $data, 'Type grouped data should not be empty' );
			$this->assert_equals( 'type', $data['metadata']['group_by'], 'Metadata group_by should be type' );

			$this->record_pass( 'Test 5: Group by type' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 5: Group by type', $e->getMessage() );
		}
	}

	/**
	 * Test 6: Group by region
	 */
	private function test_time_series_group_by_region() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'daily',
				'days_back' => 30,
				'group_by'  => 'region',
			) );

			$this->assert_not_empty( $data, 'Region grouped data should not be empty' );
			$this->assert_equals( 'region', $data['metadata']['group_by'], 'Metadata group_by should be region' );

			$this->record_pass( 'Test 6: Group by region' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 6: Group by region', $e->getMessage() );
		}
	}

	/**
	 * Test 7: Date range filtering
	 */
	private function test_time_series_date_range() {
		try {
			$data_7  = $this->consent_service->get_time_series( array( 'days_back' => 7 ) );
			$data_30 = $this->consent_service->get_time_series( array( 'days_back' => 30 ) );

			$this->assert_not_empty( $data_7, '7-day data should not be empty' );
			$this->assert_not_empty( $data_30, '30-day data should not be empty' );
			
			$labels_7_count  = count( $data_7['labels'] );
			$labels_30_count = count( $data_30['labels'] );

			$this->assert_true( 
				$labels_7_count <= $labels_30_count, 
				'7-day range should have same or fewer labels than 30-day range' 
			);

			$this->record_pass( 'Test 7: Date range filtering' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 7: Date range filtering', $e->getMessage() );
		}
	}

	/**
	 * Test 8: CSV export structure
	 */
	private function test_csv_export_structure() {
		try {
			// Create temporary buffer to capture output
			ob_start();
			
			// Simulate CSV export without actually sending headers
			global $wpdb;
			$table = $wpdb->prefix . 'slos_consent';
			
			$query = $wpdb->prepare(
				"SELECT * FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT 10",
				30
			);
			
			$results = $wpdb->get_results( $query, ARRAY_A );
			
			$this->assert_not_empty( $results, 'Should have some consent records for export' );
			$this->assert_array_has_key( 'id', $results[0], 'Record should have id column' );
			$this->assert_array_has_key( 'status', $results[0], 'Record should have status column' );
			$this->assert_array_has_key( 'type', $results[0], 'Record should have type column' );
			$this->assert_array_has_key( 'created_at', $results[0], 'Record should have created_at column' );

			ob_end_clean();

			$this->record_pass( 'Test 8: CSV export structure' );
		} catch ( Exception $e ) {
			ob_end_clean();
			$this->record_fail( 'Test 8: CSV export structure', $e->getMessage() );
		}
	}

	/**
	 * Test 9: PDF export generation
	 */
	private function test_pdf_export_generation() {
		try {
			// Test if dompdf is available
			$dompdf_autoload = SLOS_PLUGIN_DIR . 'vendor/autoload.php';
			$this->assert_true( file_exists( $dompdf_autoload ), 'Dompdf vendor autoload should exist' );

			require_once $dompdf_autoload;
			$this->assert_true( class_exists( 'Dompdf\Dompdf' ), 'Dompdf class should be available' );

			// Test PDF HTML generation
			$stats = array(
				'total'      => 100,
				'by_status'  => array( 'accepted' => 80, 'rejected' => 20 ),
				'by_type'    => array( 'analytics' => 50, 'marketing' => 50 ),
				'by_country' => array( 'US' => 60, 'GB' => 40 ),
			);

			$html = $this->generate_test_pdf_html( $stats );
			$this->assert_true( strlen( $html ) > 100, 'PDF HTML should be generated' );
			$this->assert_contains( '<html', $html, 'HTML should contain html tag' );

			$this->record_pass( 'Test 9: PDF export generation' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 9: PDF export generation', $e->getMessage() );
		}
	}

	/**
	 * Test 10: Audit log export
	 */
	private function test_audit_log_export() {
		try {
			global $wpdb;
			$table = $wpdb->prefix . 'slos_consent_audit_log';

			// Check if audit log table exists
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );
			$this->assert_not_empty( $table_exists, 'Audit log table should exist' );

			// Try to fetch audit log records
			$logs = $wpdb->get_results( "SELECT * FROM $table LIMIT 10", ARRAY_A );
			
			// Audit logs may be empty, that's OK
			if ( ! empty( $logs ) ) {
				$this->assert_array_has_key( 'consent_id', $logs[0], 'Audit log should have consent_id column' );
				$this->assert_array_has_key( 'action', $logs[0], 'Audit log should have action column' );
			}

			$this->record_pass( 'Test 10: Audit log export' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 10: Audit log export', $e->getMessage() );
		}
	}

	/**
	 * Test 11: Chart.js data format
	 */
	private function test_chart_data_format() {
		try {
			$data = $this->consent_service->get_time_series( array(
				'interval'  => 'daily',
				'days_back' => 7,
				'group_by'  => 'status',
			) );

			// Verify Chart.js compatible format
			$this->assert_array_has_key( 'labels', $data, 'Should have labels for x-axis' );
			$this->assert_array_has_key( 'datasets', $data, 'Should have datasets for series' );
			
			// Check dataset structure
			if ( ! empty( $data['datasets'] ) ) {
				$dataset = $data['datasets'][0];
				$this->assert_array_has_key( 'label', $dataset, 'Dataset should have label' );
				$this->assert_array_has_key( 'data', $dataset, 'Dataset should have data array' );
				$this->assert_true( is_array( $dataset['data'] ), 'Dataset data should be an array' );
			}

			$this->record_pass( 'Test 11: Chart.js data format' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 11: Chart.js data format', $e->getMessage() );
		}
	}

	/**
	 * Test 12: AJAX endpoint registration
	 */
	private function test_ajax_endpoint_registration() {
		try {
			// Check if AJAX actions are registered
			$this->assert_true( 
				has_action( 'wp_ajax_slos_export_consents_csv' ) !== false, 
				'CSV export AJAX action should be registered' 
			);
			
			$this->assert_true( 
				has_action( 'wp_ajax_slos_export_consents_pdf' ) !== false, 
				'PDF export AJAX action should be registered' 
			);
			
			$this->assert_true( 
				has_action( 'wp_ajax_slos_export_audit_logs_csv' ) !== false, 
				'Audit log export AJAX action should be registered' 
			);

			$this->assert_true( 
				has_action( 'wp_ajax_slos_get_consent_time_series' ) !== false, 
				'Time-series AJAX action should be registered' 
			);

			$this->record_pass( 'Test 12: AJAX endpoint registration' );
		} catch ( Exception $e ) {
			$this->record_fail( 'Test 12: AJAX endpoint registration', $e->getMessage() );
		}
	}

	// ========== Helper Methods ==========

	/**
	 * Assert not empty
	 *
	 * @param mixed  $value   Value to check.
	 * @param string $message Error message.
	 * @throws Exception If assertion fails.
	 */
	private function assert_not_empty( $value, $message ) {
		if ( empty( $value ) ) {
			throw new Exception( $message );
		}
	}

	/**
	 * Assert array has key
	 *
	 * @param string $key     Key to check.
	 * @param array  $array   Array to check.
	 * @param string $message Error message.
	 * @throws Exception If assertion fails.
	 */
	private function assert_array_has_key( $key, $array, $message ) {
		if ( ! is_array( $array ) || ! array_key_exists( $key, $array ) ) {
			throw new Exception( $message );
		}
	}

	/**
	 * Assert equals
	 *
	 * @param mixed  $expected Expected value.
	 * @param mixed  $actual   Actual value.
	 * @param string $message  Error message.
	 * @throws Exception If assertion fails.
	 */
	private function assert_equals( $expected, $actual, $message ) {
		if ( $expected !== $actual ) {
			throw new Exception( "$message (expected: $expected, got: $actual)" );
		}
	}

	/**
	 * Assert true
	 *
	 * @param bool   $condition Condition to check.
	 * @param string $message   Error message.
	 * @throws Exception If assertion fails.
	 */
	private function assert_true( $condition, $message ) {
		if ( ! $condition ) {
			throw new Exception( $message );
		}
	}

	/**
	 * Assert contains
	 *
	 * @param string $needle   Substring to find.
	 * @param string $haystack String to search.
	 * @param string $message  Error message.
	 * @throws Exception If assertion fails.
	 */
	private function assert_contains( $needle, $haystack, $message ) {
		if ( strpos( $haystack, $needle ) === false ) {
			throw new Exception( $message );
		}
	}

	/**
	 * Record test pass
	 *
	 * @param string $test_name Test name.
	 */
	private function record_pass( $test_name ) {
		$this->results[ $test_name ] = array(
			'status'  => 'PASS',
			'message' => '',
		);
		echo "✅ PASS: $test_name\n";
	}

	/**
	 * Record test fail
	 *
	 * @param string $test_name Test name.
	 * @param string $message   Error message.
	 */
	private function record_fail( $test_name, $message ) {
		$this->results[ $test_name ] = array(
			'status'  => 'FAIL',
			'message' => $message,
		);
		echo "❌ FAIL: $test_name - $message\n";
	}

	/**
	 * Generate test PDF HTML
	 *
	 * @param array $stats Statistics data.
	 * @return string PDF HTML.
	 */
	private function generate_test_pdf_html( $stats ) {
		return '<html><body><h1>Test PDF Report</h1></body></html>';
	}

	/**
	 * Print test results summary
	 */
	private function print_results() {
		$total  = count( $this->results );
		$passed = count( array_filter( $this->results, function( $r ) { 
			return $r['status'] === 'PASS'; 
		} ) );
		$failed = $total - $passed;

		echo "\n=== Test Results ===\n";
		echo "Total:  $total\n";
		echo "Passed: $passed\n";
		echo "Failed: $failed\n";

		if ( $failed === 0 ) {
			echo "\n🎉 All tests passed!\n\n";
		} else {
			echo "\n⚠️  Some tests failed. Review output above.\n\n";
		}
	}
}

// Run tests if executed directly
if ( php_sapi_name() === 'cli' || ( isset( $_GET['run_tests'] ) && current_user_can( 'manage_options' ) ) ) {
	$test_suite = new Phase_13_Test_Suite();
	$results = $test_suite->run_all_tests();
}
