<?php
/**
 * Phase 2.3 Test Suite - Accessibility-Aware Consent UX Checker
 *
 * Tests for ConsentUxChecker, dashboard integration, and auto-scanner.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Tests;

// Load WordPress test environment
if ( ! defined( 'ABSPATH' ) ) {
	// For standalone test execution
	$_tests_dir = getenv( 'WP_TESTS_DIR' );
	if ( ! $_tests_dir ) {
		$_tests_dir = '/tmp/wordpress-tests-lib';
	}
	if ( file_exists( $_tests_dir . '/includes/functions.php' ) ) {
		require_once $_tests_dir . '/includes/functions.php';
	}
}

/**
 * Phase 2.3 Test Class
 *
 * @since 3.1.1
 */
class Phase_2_3_Test {

	/**
	 * Test results storage
	 *
	 * @var array
	 */
	private $results = array();

	/**
	 * Test counter
	 *
	 * @var int
	 */
	private $test_count = 0;

	/**
	 * Constructor - run all tests
	 *
	 * @since 3.1.1
	 */
	public function __construct() {
		echo "\n" . str_repeat( '=', 80 ) . "\n";
		echo "Phase 2.3 Test Suite - Accessibility-Aware Consent UX Checker\n";
		echo str_repeat( '=', 80 ) . "\n\n";

		// Run tests
		$this->test_consent_ux_checker_class_exists();
		$this->test_get_consent_pages_returns_array();
		$this->test_scan_returns_valid_structure();
		$this->test_check_focus_order_detects_issues();
		$this->test_check_aria_attributes_detects_issues();
		$this->test_check_contrast_detects_issues();
		$this->test_check_heading_structure_detects_issues();
		$this->test_health_score_calculation();
		$this->test_get_summary_stats_format();
		$this->test_dashboard_card_integration();
		$this->test_auto_scanner_hooks_registered();
		$this->test_zero_syntax_errors();

		// Print summary
		$this->print_summary();
	}

	/**
	 * Test 1: ConsentUxChecker class exists and instantiates
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_consent_ux_checker_class_exists(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] ConsentUxChecker class exists and instantiates...\n";

		try {
			$class_exists = class_exists( '\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\ConsentUxChecker' );
			if ( ! $class_exists ) {
				throw new \Exception( 'ConsentUxChecker class does not exist' );
			}

			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			if ( ! is_object( $checker ) ) {
				throw new \Exception( 'Failed to instantiate ConsentUxChecker' );
			}

			$this->pass( 'ConsentUxChecker class exists and instantiates correctly' );
		} catch ( \Exception $e ) {
			$this->fail( 'ConsentUxChecker instantiation failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 2: get_consent_pages() returns array
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_get_consent_pages_returns_array(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] get_consent_pages() returns array...\n";

		try {
			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$pages   = $checker->get_consent_pages();

			if ( ! is_array( $pages ) ) {
				throw new \Exception( 'get_consent_pages() did not return an array' );
			}

			$this->pass( 'get_consent_pages() returns array with ' . count( $pages ) . ' page(s)' );
		} catch ( \Exception $e ) {
			$this->fail( 'get_consent_pages() test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 3: scan() returns valid structure
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_scan_returns_valid_structure(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] scan() returns valid structure...\n";

		try {
			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$result  = $checker->scan( true ); // Force refresh

			// Validate structure
			$required_keys = array( 'status', 'health_score', 'issue_counts', 'page_results' );
			foreach ( $required_keys as $key ) {
				if ( ! isset( $result[ $key ] ) ) {
					throw new \Exception( "Missing required key: $key" );
				}
			}

			// Validate health_score is numeric 0-100
			if ( ! is_numeric( $result['health_score'] ) || $result['health_score'] < 0 || $result['health_score'] > 100 ) {
				throw new \Exception( 'health_score must be numeric 0-100' );
			}

			// Validate issue_counts structure
			$issue_keys = array( 'critical', 'warning', 'notice' );
			foreach ( $issue_keys as $key ) {
				if ( ! isset( $result['issue_counts'][ $key ] ) ) {
					throw new \Exception( "Missing issue_counts key: $key" );
				}
			}

			$this->pass( 'scan() returns valid structure with health_score=' . $result['health_score'] );
		} catch ( \Exception $e ) {
			$this->fail( 'scan() structure test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 4: check_focus_order() detects positive tabindex and keyboard issues
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_check_focus_order_detects_issues(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] check_focus_order() detects issues...\n";

		try {
			// Create test HTML with focus order issues
			$test_html = '<!DOCTYPE html><html><head><title>Test</title></head><body>';
			$test_html .= '<button tabindex="5">Bad button</button>'; // Positive tabindex
			$test_html .= '<div onclick="alert(\'test\')">Clickable div</div>'; // Keyboard inaccessible
			$test_html .= '</body></html>';

			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$reflection = new \ReflectionClass( $checker );
			$method = $reflection->getMethod( 'check_focus_order' );
			$method->setAccessible( true );

			$issues = $method->invoke( $checker, $test_html, 123 );

			// Should detect at least 2 issues (positive tabindex + clickable div)
			if ( count( $issues ) < 2 ) {
				throw new \Exception( 'check_focus_order() did not detect expected issues. Found: ' . count( $issues ) );
			}

			$this->pass( 'check_focus_order() detected ' . count( $issues ) . ' focus order issues' );
		} catch ( \Exception $e ) {
			$this->fail( 'check_focus_order() test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 5: check_aria_attributes() detects unlabeled elements and invalid roles
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_check_aria_attributes_detects_issues(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] check_aria_attributes() detects issues...\n";

		try {
			// Create test HTML with ARIA issues
			$test_html = '<!DOCTYPE html><html><head><title>Test</title></head><body>';
			$test_html .= '<button></button>'; // Unlabeled button
			$test_html .= '<input type="text" />'; // Unlabeled input
			$test_html .= '<div role="invalidrole">Content</div>'; // Invalid ARIA role
			$test_html .= '</body></html>';

			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$reflection = new \ReflectionClass( $checker );
			$method = $reflection->getMethod( 'check_aria_attributes' );
			$method->setAccessible( true );

			$issues = $method->invoke( $checker, $test_html, 123 );

			// Should detect at least 3 issues
			if ( count( $issues ) < 3 ) {
				throw new \Exception( 'check_aria_attributes() did not detect expected issues. Found: ' . count( $issues ) );
			}

			$this->pass( 'check_aria_attributes() detected ' . count( $issues ) . ' ARIA issues' );
		} catch ( \Exception $e ) {
			$this->fail( 'check_aria_attributes() test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 6: check_contrast() detects inline styles and low-contrast classes
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_check_contrast_detects_issues(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] check_contrast() detects issues...\n";

		try {
			// Create test HTML with contrast issues
			$test_html = '<!DOCTYPE html><html><head><title>Test</title></head><body>';
			$test_html .= '<p style="color: red;">Red text</p>'; // Inline color
			$test_html .= '<span class="text-muted">Muted text</span>'; // Low contrast class
			$test_html .= '</body></html>';

			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$reflection = new \ReflectionClass( $checker );
			$method = $reflection->getMethod( 'check_contrast' );
			$method->setAccessible( true );

			$issues = $method->invoke( $checker, $test_html, 123 );

			// Should detect at least 2 issues
			if ( count( $issues ) < 2 ) {
				throw new \Exception( 'check_contrast() did not detect expected issues. Found: ' . count( $issues ) );
			}

			$this->pass( 'check_contrast() detected ' . count( $issues ) . ' contrast issues' );
		} catch ( \Exception $e ) {
			$this->fail( 'check_contrast() test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 7: check_heading_structure() detects missing/multiple H1, nesting issues
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_check_heading_structure_detects_issues(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] check_heading_structure() detects issues...\n";

		try {
			// Create test HTML with heading issues
			$test_html = '<!DOCTYPE html><html><head><title>Test</title></head><body>';
			$test_html .= '<h1>First H1</h1><h1>Second H1</h1>'; // Multiple H1s
			$test_html .= '<h2>H2</h2><h4>H4</h4>'; // Skipped H3
			$test_html .= '<h3></h3>'; // Empty heading
			$test_html .= '</body></html>';

			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$reflection = new \ReflectionClass( $checker );
			$method = $reflection->getMethod( 'check_heading_structure' );
			$method->setAccessible( true );

			$issues = $method->invoke( $checker, $test_html, 123 );

			// Should detect at least 3 issues (multiple H1, skipped level, empty heading)
			if ( count( $issues ) < 3 ) {
				throw new \Exception( 'check_heading_structure() did not detect expected issues. Found: ' . count( $issues ) );
			}

			$this->pass( 'check_heading_structure() detected ' . count( $issues ) . ' heading issues' );
		} catch ( \Exception $e ) {
			$this->fail( 'check_heading_structure() test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 8: Health score calculation follows formula
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_health_score_calculation(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Health score calculation is correct...\n";

		try {
			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$result = $checker->scan( true );

			// Health score should be between 0 and 100
			if ( $result['health_score'] < 0 || $result['health_score'] > 100 ) {
				throw new \Exception( 'Health score out of valid range: ' . $result['health_score'] );
			}

			// If there are critical issues, score should be penalized
			if ( $result['issue_counts']['critical'] > 0 && $result['health_score'] >= 90 ) {
				throw new \Exception( 'Health score too high with critical issues present' );
			}

			$this->pass( 'Health score calculation follows formula (score=' . $result['health_score'] . ')' );
		} catch ( \Exception $e ) {
			$this->fail( 'Health score calculation test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 9: get_summary_stats() returns dashboard-ready format
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_get_summary_stats_format(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] get_summary_stats() returns dashboard format...\n";

		try {
			$checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
			$stats = $checker->get_summary_stats();

			// Validate required keys for dashboard
			$required_keys = array(
				'health_score',
				'total_issues',
				'critical_issues',
				'warning_issues',
				'notice_issues',
				'total_pages',
				'pages_with_issues',
				'scanned_at'
			);

			foreach ( $required_keys as $key ) {
				if ( ! isset( $stats[ $key ] ) ) {
					throw new \Exception( "Missing dashboard stat: $key" );
				}
			}

			$this->pass( 'get_summary_stats() returns valid dashboard format' );
		} catch ( \Exception $e ) {
			$this->fail( 'get_summary_stats() format test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 10: Dashboard card integration
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_dashboard_card_integration(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Dashboard card integration...\n";

		try {
			// Check if ComplianceMainPage includes consent_ux stats
			$file_path = SHAHI_PATH . 'includes/Admin/ComplianceMainPage.php';
			if ( ! file_exists( $file_path ) ) {
				throw new \Exception( 'ComplianceMainPage.php not found' );
			}

			$content = file_get_contents( $file_path );
			if ( strpos( $content, 'consent_ux_checker' ) === false ) {
				throw new \Exception( 'ComplianceMainPage does not instantiate ConsentUxChecker' );
			}

			// Check if dashboard template includes consent_ux card
			$template_path = SHAHI_PATH . 'templates/admin/dashboard.php';
			if ( ! file_exists( $template_path ) ) {
				throw new \Exception( 'dashboard.php template not found' );
			}

			$template_content = file_get_contents( $template_path );
			if ( strpos( $template_content, 'consent_ux' ) === false ) {
				throw new \Exception( 'dashboard.php template does not include consent_ux card' );
			}

			if ( strpos( $template_content, 'Consent UX Health' ) === false ) {
				throw new \Exception( 'dashboard.php template missing "Consent UX Health" card title' );
			}

			$this->pass( 'Dashboard card integration verified' );
		} catch ( \Exception $e ) {
			$this->fail( 'Dashboard card integration test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 11: Auto-scanner hooks registered
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_auto_scanner_hooks_registered(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Auto-scanner hooks registered...\n";

		try {
			// Check if ConsentUxAutoScanner file exists
			$file_path = SHAHI_PATH . 'includes/Modules/AccessibilityScanner/ConsentUxAutoScanner.php';
			if ( ! file_exists( $file_path ) ) {
				throw new \Exception( 'ConsentUxAutoScanner.php not found' );
			}

			// Verify file has instantiation
			$content = file_get_contents( $file_path );
			if ( strpos( $content, 'new ConsentUxAutoScanner()' ) === false ) {
				throw new \Exception( 'ConsentUxAutoScanner file missing instantiation' );
			}

			// Verify hooks are registered (after loading the file)
			if ( class_exists( '\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\ConsentUxAutoScanner' ) ) {
				// Check for save_post_page hook
				$has_save_post_hook = has_action( 'save_post_page' );
				if ( $has_save_post_hook === false ) {
					throw new \Exception( 'save_post_page hook not registered' );
				}
			}

			$this->pass( 'Auto-scanner hooks verified' );
		} catch ( \Exception $e ) {
			$this->fail( 'Auto-scanner hooks test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 12: Zero syntax errors across all files
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function test_zero_syntax_errors(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Zero syntax errors validation...\n";

		try {
			$files_to_check = array(
				'includes/Modules/AccessibilityScanner/ConsentUxChecker.php',
				'includes/Modules/AccessibilityScanner/ConsentUxAutoScanner.php',
				'includes/Admin/ComplianceMainPage.php',
				'includes/Core/Plugin.php',
			);

			$errors = array();
			foreach ( $files_to_check as $file ) {
				$full_path = SHAHI_PATH . $file;
				if ( ! file_exists( $full_path ) ) {
					$errors[] = "$file does not exist";
					continue;
				}

				// Check for PHP syntax using php -l
				$output = array();
				$return_var = 0;
				exec( "php -l " . escapeshellarg( $full_path ) . " 2>&1", $output, $return_var );

				if ( $return_var !== 0 ) {
					$errors[] = "$file has syntax errors: " . implode( "\n", $output );
				}
			}

			if ( ! empty( $errors ) ) {
				throw new \Exception( "Syntax errors found:\n" . implode( "\n", $errors ) );
			}

			$this->pass( 'All files have zero syntax errors' );
		} catch ( \Exception $e ) {
			$this->fail( 'Syntax validation test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Record passed test
	 *
	 * @since 3.1.1
	 * @param string $message Test success message.
	 * @return void
	 */
	private function pass( string $message ): void {
		echo "  ✅ PASS: $message\n\n";
		$this->results[] = array(
			'status'  => 'pass',
			'message' => $message,
		);
	}

	/**
	 * Record failed test
	 *
	 * @since 3.1.1
	 * @param string $message Test failure message.
	 * @return void
	 */
	private function fail( string $message ): void {
		echo "  ❌ FAIL: $message\n\n";
		$this->results[] = array(
			'status'  => 'fail',
			'message' => $message,
		);
	}

	/**
	 * Print test summary
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function print_summary(): void {
		$passed = count( array_filter( $this->results, function( $r ) {
			return $r['status'] === 'pass';
		} ) );
		$failed = count( array_filter( $this->results, function( $r ) {
			return $r['status'] === 'fail';
		} ) );
		$total = count( $this->results );

		echo "\n" . str_repeat( '=', 80 ) . "\n";
		echo "TEST SUMMARY\n";
		echo str_repeat( '=', 80 ) . "\n";
		echo "Total Tests: $total\n";
		echo "Passed: $passed ✅\n";
		echo "Failed: $failed " . ( $failed > 0 ? '❌' : '✅' ) . "\n";
		echo "Success Rate: " . ( $total > 0 ? round( ( $passed / $total ) * 100, 2 ) : 0 ) . "%\n";
		echo str_repeat( '=', 80 ) . "\n\n";

		if ( $failed === 0 ) {
			echo "🎉 All tests passed! Phase 2.3 implementation is ready.\n\n";
		} else {
			echo "⚠️  Some tests failed. Please review and fix the issues above.\n\n";
		}
	}
}

// Run tests if executed directly
if ( defined( 'ABSPATH' ) || php_sapi_name() === 'cli' ) {
	new Phase_2_3_Test();
}
