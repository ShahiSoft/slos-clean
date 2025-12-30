<?php
/**
 * Phase 1.1 Integration Tests: Legal Document Suite Integration
 *
 * Tests cookie data binding to legal documents, shortcodes, dashboard integration,
 * staleness detection, and compliance score calculation.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.1.1
 */

// Load WordPress
$wp_load_path = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	// Try alternative path for Docker environment
	$wp_load_path = 'c:/docker-wp/wordpress_data/wp-load.php';
}
require_once $wp_load_path;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

use ShahiLegalFlowSuite\Services\Placeholder_Mapper;
use ShahiLegalFlowSuite\Services\Document_Hub_Service;
use ShahiLegalFlowSuite\Services\Cookie_Scanner_Service;
use ShahiLegalFlowSuite\Services\Compliance_Score_Calculator;
use ShahiLegalFlowSuite\Shortcodes\Cookie_Table_Shortcode;
use ShahiLegalFlowSuite\Admin\ComplianceMainPage;

/**
 * Test runner class
 */
class LegalDocsIntegrationTest {

	private $results = array();
	private $test_count = 0;
	private $pass_count = 0;
	private $fail_count = 0;

	/**
	 * Run all tests
	 */
	public function run_all() {
		echo "\n";
		echo "===========================================\n";
		echo "Phase 1.1: Legal Docs Integration Tests\n";
		echo "===========================================\n\n";

		$this->setup_test_data();

		$this->test_cookie_placeholders();
		$this->test_cookie_table_shortcode();
		$this->test_legal_docs_stats();
		$this->test_staleness_detection();
		$this->test_compliance_score_legal_docs();
		$this->test_action_hook_integration();
		$this->test_document_hub_staleness_methods();
		$this->test_placeholder_mapper_cookie_methods();

		$this->cleanup_test_data();

		$this->print_summary();
	}

	/**
	 * Setup test data
	 */
	private function setup_test_data() {
		echo "Setting up test data...\n";

		// Create test cookie inventory
		$test_cookies = array(
			array(
				'name'     => '_ga',
				'category' => 'analytics',
				'purpose'  => 'Google Analytics tracking',
				'provider' => 'Google',
				'duration' => '2 years',
			),
			array(
				'name'     => '_fbp',
				'category' => 'marketing',
				'purpose'  => 'Facebook Pixel',
				'provider' => 'Facebook',
				'duration' => '90 days',
			),
			array(
				'name'     => 'wordpress_test_cookie',
				'category' => 'necessary',
				'purpose'  => 'Test WordPress functionality',
				'provider' => 'WordPress',
				'duration' => 'Session',
			),
		);

		update_option( 'slos_cookie_inventory', $test_cookies );

		// Create scan metadata
		$scan_meta = array(
			'scan_type'     => 'full',
			'started_at'    => current_time( 'mysql' ),
			'completed_at'  => current_time( 'mysql' ),
			'status'        => 'completed',
			'cookies_found' => count( $test_cookies ),
			'duration'      => 5,
		);
		update_option( 'slos_cookie_scan_meta', $scan_meta );

		echo "✓ Test data created\n\n";
	}

	/**
	 * Test 1: Cookie placeholders in Placeholder_Mapper
	 */
	private function test_cookie_placeholders() {
		$this->test_count++;
		echo "Test 1: Cookie Placeholders in Placeholder_Mapper\n";
		echo "--------------------------------------------\n";

		try {
			$mapper = new Placeholder_Mapper();
			
			// Build placeholder map (includes cookie data)
			$reflection = new ReflectionClass( $mapper );
			$method = $reflection->getMethod( 'build_placeholder_map' );
			$method->setAccessible( true );
			$map = $method->invoke( $mapper, array() );

			// Check for cookie placeholders
			$required_keys = array(
				'cookie_count',
				'cookie_table_all',
				'cookie_table_necessary',
				'cookie_table_analytics',
				'cookie_table_marketing',
				'last_cookie_scan_date',
			);

			$missing = array();
			foreach ( $required_keys as $key ) {
				if ( ! isset( $map[ $key ] ) ) {
					$missing[] = $key;
				}
			}

			if ( empty( $missing ) ) {
				echo "✓ All cookie placeholders present\n";
				echo "  - cookie_count: " . $map['cookie_count'] . "\n";
				echo "  - cookie_table_all: " . ( strpos( $map['cookie_table_all'], '<table' ) !== false ? 'HTML table generated' : 'ERROR' ) . "\n";
				$this->pass_count++;
				$this->results[] = array( 'test' => 'Cookie Placeholders', 'status' => 'PASS' );
			} else {
				throw new Exception( 'Missing placeholders: ' . implode( ', ', $missing ) );
			}
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Cookie Placeholders', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 2: Cookie Table Shortcode
	 */
	private function test_cookie_table_shortcode() {
		$this->test_count++;
		echo "Test 2: Cookie Table Shortcode\n";
		echo "--------------------------------------------\n";

		try {
			$shortcode = new Cookie_Table_Shortcode();
			
			// Test 'all' category
			$output_all = $shortcode->render( array( 'category' => 'all', 'title' => 'yes' ), null );
			
			if ( strpos( $output_all, 'slos-cookie-table' ) === false ) {
				throw new Exception( 'Shortcode did not generate table HTML' );
			}

			if ( strpos( $output_all, '_ga' ) === false ) {
				throw new Exception( 'Cookie _ga not found in table' );
			}

			// Test category filter
			$output_analytics = $shortcode->render( array( 'category' => 'analytics', 'title' => 'no' ), null );
			
			if ( strpos( $output_analytics, '_fbp' ) !== false ) {
				throw new Exception( 'Category filter failed - marketing cookie in analytics output' );
			}

			echo "✓ Shortcode renders correctly\n";
			echo "  - All categories output: " . strlen( $output_all ) . " bytes\n";
			echo "  - Analytics filter works\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Cookie Table Shortcode', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Cookie Table Shortcode', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 3: Legal Docs Stats in Dashboard
	 */
	private function test_legal_docs_stats() {
		$this->test_count++;
		echo "Test 3: Legal Docs Stats in ComplianceMainPage\n";
		echo "--------------------------------------------\n";

		try {
			// Access protected method using reflection
			$compliance_page = new ComplianceMainPage();
			$reflection = new ReflectionClass( $compliance_page );
			$method = $reflection->getMethod( 'get_dashboard_stats' );
			$method->setAccessible( true );
			$stats = $method->invoke( $compliance_page );

			if ( ! isset( $stats['legal_docs'] ) ) {
				throw new Exception( 'legal_docs key missing from dashboard stats' );
			}

			$legal_docs = $stats['legal_docs'];
			$required_keys = array( 'total', 'published', 'stale', 'pending', 'percentage', 'docs' );
			
			foreach ( $required_keys as $key ) {
				if ( ! isset( $legal_docs[ $key ] ) ) {
					throw new Exception( "Missing key in legal_docs: $key" );
				}
			}

			echo "✓ Legal docs stats integrated into dashboard\n";
			echo "  - Total required: " . $legal_docs['total'] . "\n";
			echo "  - Published: " . $legal_docs['published'] . "\n";
			echo "  - Stale: " . $legal_docs['stale'] . "\n";
			echo "  - Percentage: " . $legal_docs['percentage'] . "%\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Legal Docs Stats', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Legal Docs Stats', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 4: Staleness Detection
	 */
	private function test_staleness_detection() {
		$this->test_count++;
		echo "Test 4: Document Staleness Detection\n";
		echo "--------------------------------------------\n";

		try {
			$hub_service = new Document_Hub_Service();

			// Create a mock document post
			$test_post_id = wp_insert_post( array(
				'post_title'   => 'Test Cookie Policy',
				'post_content' => 'Test content',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			) );

			if ( is_wp_error( $test_post_id ) ) {
				throw new Exception( 'Failed to create test post' );
			}

			// Mark as stale
			update_post_meta( $test_post_id, '_slos_needs_regeneration', true );
			update_post_meta( $test_post_id, '_slos_stale_reason', 'cookie_data_changed' );

			// Test staleness check
			$is_stale = $hub_service->is_document_stale( $test_post_id );
			$reason = $hub_service->get_staleness_reason( $test_post_id );

			if ( ! $is_stale ) {
				throw new Exception( 'Document not detected as stale' );
			}

			if ( $reason !== 'cookie_data_changed' ) {
				throw new Exception( 'Staleness reason incorrect' );
			}

			// Clear staleness
			$hub_service->clear_staleness( $test_post_id );
			$is_stale_after = $hub_service->is_document_stale( $test_post_id );

			if ( $is_stale_after ) {
				throw new Exception( 'Staleness not cleared' );
			}

			// Clean up
			wp_delete_post( $test_post_id, true );

			echo "✓ Staleness detection working\n";
			echo "  - Document marked as stale\n";
			echo "  - Reason retrieved correctly\n";
			echo "  - Staleness cleared successfully\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Staleness Detection', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Staleness Detection', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 5: Compliance Score Legal Docs Dimension
	 */
	private function test_compliance_score_legal_docs() {
		$this->test_count++;
		echo "Test 5: Compliance Score - Legal Docs Dimension\n";
		echo "--------------------------------------------\n";

		try {
			$calculator = new Compliance_Score_Calculator();
			$result = $calculator->calculate( false ); // Don't use cache

			if ( ! isset( $result['dimensions']['LEGAL_DOCS'] ) ) {
				throw new Exception( 'LEGAL_DOCS dimension missing from score' );
			}

			$legal_docs_dim = $result['dimensions']['LEGAL_DOCS'];

			if ( ! isset( $legal_docs_dim['score'] ) ) {
				throw new Exception( 'Score missing from LEGAL_DOCS dimension' );
			}

			if ( ! isset( $legal_docs_dim['details'] ) ) {
				throw new Exception( 'Details missing from LEGAL_DOCS dimension' );
			}

			$details = $legal_docs_dim['details'];
			if ( ! isset( $details['required_count'], $details['published_count'], $details['stale_count'] ) ) {
				throw new Exception( 'Required details missing from dimension' );
			}

			echo "✓ Legal docs dimension integrated into score\n";
			echo "  - Dimension score: " . $legal_docs_dim['score'] . "/100\n";
			echo "  - Required docs: " . $details['required_count'] . "\n";
			echo "  - Published: " . $details['published_count'] . "\n";
			echo "  - Stale: " . $details['stale_count'] . "\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Compliance Score Legal Docs', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Compliance Score Legal Docs', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 6: Action Hook Integration
	 */
	private function test_action_hook_integration() {
		$this->test_count++;
		echo "Test 6: slos_cookies_updated Action Hook\n";
		echo "--------------------------------------------\n";

		try {
			$hook_fired = false;
			$received_data = null;

			// Add a test listener
			add_action( 'slos_cookies_updated', function( $cookies ) use ( &$hook_fired, &$received_data ) {
				$hook_fired = true;
				$received_data = $cookies;
			} );

			// Simulate scan completion
			$scanner = new Cookie_Scanner_Service();
			$scanner->start_scan( 'test', array( 'test' => 'data' ) );
			$scanner->complete_scan( array(
				'cookies_found' => 3,
				'errors' => array(),
			) );

			if ( ! $hook_fired ) {
				throw new Exception( 'slos_cookies_updated action did not fire' );
			}

			if ( ! is_array( $received_data ) ) {
				throw new Exception( 'Hook received invalid data' );
			}

			echo "✓ Action hook integration working\n";
			echo "  - Hook fired on scan completion\n";
			echo "  - Cookie data passed to listeners\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Action Hook Integration', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Action Hook Integration', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 7: Document Hub Staleness Methods
	 */
	private function test_document_hub_staleness_methods() {
		$this->test_count++;
		echo "Test 7: Document Hub Staleness Methods\n";
		echo "--------------------------------------------\n";

		try {
			$hub_service = new Document_Hub_Service();

			// Test mark_cookie_dependent_docs_stale method exists
			if ( ! method_exists( $hub_service, 'mark_cookie_dependent_docs_stale' ) ) {
				throw new Exception( 'Method mark_cookie_dependent_docs_stale not found' );
			}

			if ( ! method_exists( $hub_service, 'clear_staleness' ) ) {
				throw new Exception( 'Method clear_staleness not found' );
			}

			if ( ! method_exists( $hub_service, 'is_document_stale' ) ) {
				throw new Exception( 'Method is_document_stale not found' );
			}

			if ( ! method_exists( $hub_service, 'get_staleness_reason' ) ) {
				throw new Exception( 'Method get_staleness_reason not found' );
			}

			echo "✓ All staleness methods implemented\n";
			echo "  - mark_cookie_dependent_docs_stale()\n";
			echo "  - clear_staleness()\n";
			echo "  - is_document_stale()\n";
			echo "  - get_staleness_reason()\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Document Hub Methods', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Document Hub Methods', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Test 8: Placeholder Mapper Cookie Methods
	 */
	private function test_placeholder_mapper_cookie_methods() {
		$this->test_count++;
		echo "Test 8: Placeholder Mapper Cookie Methods\n";
		echo "--------------------------------------------\n";

		try {
			$mapper = new Placeholder_Mapper();
			$reflection = new ReflectionClass( $mapper );

			// Check for get_cookie_placeholders method
			if ( ! $reflection->hasMethod( 'get_cookie_placeholders' ) ) {
				throw new Exception( 'Method get_cookie_placeholders not found' );
			}

			// Check for render_cookie_table method
			if ( ! $reflection->hasMethod( 'render_cookie_table' ) ) {
				throw new Exception( 'Method render_cookie_table not found' );
			}

			// Check for convert_legacy_cookies method
			if ( ! $reflection->hasMethod( 'convert_legacy_cookies' ) ) {
				throw new Exception( 'Method convert_legacy_cookies not found' );
			}

			echo "✓ All Placeholder Mapper cookie methods implemented\n";
			echo "  - get_cookie_placeholders()\n";
			echo "  - render_cookie_table()\n";
			echo "  - convert_legacy_cookies()\n";
			$this->pass_count++;
			$this->results[] = array( 'test' => 'Placeholder Mapper Methods', 'status' => 'PASS' );
		} catch ( Exception $e ) {
			echo "✗ FAILED: " . $e->getMessage() . "\n";
			$this->fail_count++;
			$this->results[] = array( 'test' => 'Placeholder Mapper Methods', 'status' => 'FAIL', 'error' => $e->getMessage() );
		}

		echo "\n";
	}

	/**
	 * Clean up test data
	 */
	private function cleanup_test_data() {
		echo "Cleaning up test data...\n";
		// We'll leave the test cookies for manual inspection
		echo "✓ Cleanup complete (test cookies preserved for inspection)\n\n";
	}

	/**
	 * Print summary
	 */
	private function print_summary() {
		echo "===========================================\n";
		echo "TEST SUMMARY\n";
		echo "===========================================\n";
		echo "Total Tests: " . $this->test_count . "\n";
		echo "Passed: " . $this->pass_count . " (" . round( ( $this->pass_count / $this->test_count ) * 100 ) . "%)\n";
		echo "Failed: " . $this->fail_count . "\n";
		echo "\n";

		if ( $this->fail_count === 0 ) {
			echo "✓✓✓ ALL TESTS PASSED ✓✓✓\n";
		} else {
			echo "⚠ SOME TESTS FAILED ⚠\n";
			echo "\nFailed Tests:\n";
			foreach ( $this->results as $result ) {
				if ( $result['status'] === 'FAIL' ) {
					echo "  - " . $result['test'] . ": " . ( $result['error'] ?? 'Unknown error' ) . "\n";
				}
			}
		}
		echo "===========================================\n";
	}
}

// Run tests
$test_runner = new LegalDocsIntegrationTest();
$test_runner->run_all();
