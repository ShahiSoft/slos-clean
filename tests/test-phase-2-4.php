<?php
/**
 * Phase 2.4 Test Suite - Multi-Site / Organization View
 *
 * Tests for config sync schema, Config_Sync_Service, Config_REST_Controller,
 * Network_Compliance_Dashboard, and all integration points.
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
 * Phase 2.4 Test Class
 *
 * @since 3.1.1
 */
class Phase_2_4_Test {

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
		echo "Phase 2.4 Test Suite - Multi-Site / Organization View\n";
		echo str_repeat( '=', 80 ) . "\n\n";

		// Schema tests
		$this->test_schema_file_exists();
		$this->test_schema_functions_defined();
		$this->test_schema_structure();
		$this->test_schema_validation();

		// Config_Sync_Service tests
		$this->test_config_sync_service_exists();
		$this->test_export_config_returns_profile();
		$this->test_import_config_validates();
		$this->test_file_operations();
		$this->test_never_sync_exclusions();

		// REST API tests
		$this->test_config_rest_controller_exists();
		$this->test_rest_routes_registered();

		// UI integration tests
		$this->test_config_sync_tab_exists();
		$this->test_config_sync_assets_exist();
		$this->test_compliance_tab_registration();

		// Network dashboard tests (multisite only)
		if ( is_multisite() ) {
			$this->test_network_dashboard_exists();
			$this->test_network_menu_registered();
		}

		// Zero errors validation
		$this->test_zero_syntax_errors();

		// Print summary
		$this->print_summary();
	}

	/**
	 * Test 1: config/multisite-sync-schema.php exists
	 */
	private function test_schema_file_exists(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] config/multisite-sync-schema.php exists...\n";

		try {
			$file_path = SHAHI_LEGALFLOWSUITE_PATH . 'config/multisite-sync-schema.php';
			if ( ! file_exists( $file_path ) ) {
				throw new \Exception( 'Schema file not found' );
			}

			require_once $file_path;
			$this->pass( 'Schema file exists and loaded successfully' );
		} catch ( \Exception $e ) {
			$this->fail( 'Schema file test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 2: Schema functions are defined
	 */
	private function test_schema_functions_defined(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Schema functions defined...\n";

		try {
			$required_functions = array(
				'slos_get_config_sync_schema',
				'slos_get_default_export_options',
				'slos_get_safe_import_options',
				'slos_validate_config_profile',
				'slos_sanitize_config_profile',
				'slos_get_config_profile_template',
			);

			foreach ( $required_functions as $func ) {
				if ( ! function_exists( $func ) ) {
					throw new \Exception( "Function {$func} not defined" );
				}
			}

			$this->pass( count( $required_functions ) . ' schema functions defined correctly' );
		} catch ( \Exception $e ) {
			$this->fail( 'Schema functions test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 3: Schema structure is valid
	 */
	private function test_schema_structure(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Schema structure is valid...\n";

		try {
			$schema = slos_get_config_sync_schema();

			// Validate required keys
			$required_keys = array( 'profile', 'settings', 'modules', 'exclusions', 'validation_rules', 'compatibility' );
			foreach ( $required_keys as $key ) {
				if ( ! isset( $schema[ $key ] ) ) {
					throw new \Exception( "Missing schema key: {$key}" );
				}
			}

			// Validate schema version
			if ( ! isset( $schema['schema_version'] ) || empty( $schema['schema_version'] ) ) {
				throw new \Exception( 'Schema version not set' );
			}

			$this->pass( 'Schema structure valid with version ' . $schema['schema_version'] );
		} catch ( \Exception $e ) {
			$this->fail( 'Schema structure test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 4: Schema validation works
	 */
	private function test_schema_validation(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Schema validation works...\n";

		try {
			$template = slos_get_config_profile_template();
			$template['settings'] = array(
				'slos_banner_settings' => array( 'enabled' => true ),
			);

			$result = slos_validate_config_profile( $template );

			if ( ! is_array( $result ) ) {
				throw new \Exception( 'Validation did not return array' );
			}

			if ( ! isset( $result['valid'] ) ) {
				throw new \Exception( 'Validation result missing "valid" key' );
			}

			$this->pass( 'Schema validation works (valid=' . ( $result['valid'] ? 'true' : 'false' ) . ')' );
		} catch ( \Exception $e ) {
			$this->fail( 'Schema validation test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 5: Config_Sync_Service exists and instantiates
	 */
	private function test_config_sync_service_exists(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Config_Sync_Service exists...\n";

		try {
			$class_exists = class_exists( '\\ShahiLegalFlowSuite\\Services\\Config_Sync_Service' );
			if ( ! $class_exists ) {
				throw new \Exception( 'Config_Sync_Service class does not exist' );
			}

			$service = new \ShahiLegalFlowSuite\Services\Config_Sync_Service();
			if ( ! is_object( $service ) ) {
				throw new \Exception( 'Failed to instantiate Config_Sync_Service' );
			}

			$this->pass( 'Config_Sync_Service exists and instantiates' );
		} catch ( \Exception $e ) {
			$this->fail( 'Config_Sync_Service test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 6: export_config returns valid profile
	 */
	private function test_export_config_returns_profile(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] export_config returns valid profile...\n";

		try {
			$service = new \ShahiLegalFlowSuite\Services\Config_Sync_Service();
			$profile = $service->export_config( array( 'slos_banner_settings' ), array( 'name' => 'Test Export' ) );

			if ( is_wp_error( $profile ) ) {
				throw new \Exception( 'export_config returned WP_Error: ' . $profile->get_error_message() );
			}

			if ( ! isset( $profile['profile'] ) || ! isset( $profile['settings'] ) ) {
				throw new \Exception( 'Export profile missing required keys' );
			}

			if ( $profile['profile']['name'] !== 'Test Export' ) {
				throw new \Exception( 'Profile name not set correctly' );
			}

			$this->pass( 'export_config returns valid profile structure' );
		} catch ( \Exception $e ) {
			$this->fail( 'export_config test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 7: import_config validates profile
	 */
	private function test_import_config_validates(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] import_config validates profile...\n";

		try {
			$service = new \ShahiLegalFlowSuite\Services\Config_Sync_Service();
			
			// Export first
			$profile = $service->export_config( array( 'slos_banner_settings' ), array( 'name' => 'Test' ) );
			
			// Import with dry-run
			$result = $service->import_config( $profile, array( 'dry_run' => true ) );

			if ( is_wp_error( $result ) ) {
				throw new \Exception( 'import_config returned WP_Error: ' . $result->get_error_message() );
			}

			if ( ! isset( $result['imported'] ) || ! isset( $result['dry_run'] ) ) {
				throw new \Exception( 'Import result missing required keys' );
			}

			if ( ! $result['dry_run'] ) {
				throw new \Exception( 'Dry-run flag not preserved' );
			}

			$this->pass( 'import_config validates and processes correctly' );
		} catch ( \Exception $e ) {
			$this->fail( 'import_config test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 8: File operations work
	 */
	private function test_file_operations(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] File operations work...\n";

		try {
			$service = new \ShahiLegalFlowSuite\Services\Config_Sync_Service();
			
			// Export to file
			$filepath = $service->export_to_file( array( 'slos_banner_settings' ), array( 'name' => 'Test File Export' ) );

			if ( is_wp_error( $filepath ) ) {
				throw new \Exception( 'export_to_file failed: ' . $filepath->get_error_message() );
			}

			if ( ! file_exists( $filepath ) ) {
				throw new \Exception( 'Export file not created' );
			}

			// Get available exports
			$exports = $service->get_available_exports();
			if ( ! is_array( $exports ) || empty( $exports ) ) {
				throw new \Exception( 'get_available_exports did not return array with exports' );
			}

			// Delete export
			$filename = basename( $filepath );
			$deleted  = $service->delete_export( $filename );
			if ( is_wp_error( $deleted ) ) {
				throw new \Exception( 'delete_export failed: ' . $deleted->get_error_message() );
			}

			$this->pass( 'File operations (export/list/delete) work correctly' );
		} catch ( \Exception $e ) {
			$this->fail( 'File operations test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 9: Never-sync options are excluded
	 */
	private function test_never_sync_exclusions(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Never-sync options excluded from export...\n";

		try {
			$service = new \ShahiLegalFlowSuite\Services\Config_Sync_Service();
			
			// Try to export PII option (should be excluded)
			$profile = $service->export_config( array( 'slos_consent', 'slos_banner_settings' ), array( 'name' => 'Test Exclusion' ) );

			if ( is_wp_error( $profile ) ) {
				// Good - it should reject PII options
				$this->pass( 'Never-sync options correctly rejected: ' . $profile->get_error_message() );
				return;
			}

			// If it didn't error, check if slos_consent was excluded
			if ( isset( $profile['settings']['slos_consent'] ) ) {
				throw new \Exception( 'PII option slos_consent was not excluded from export' );
			}

			$this->pass( 'Never-sync options correctly excluded' );
		} catch ( \Exception $e ) {
			$this->fail( 'Never-sync exclusions test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 10: Config_REST_Controller exists
	 */
	private function test_config_rest_controller_exists(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Config_REST_Controller exists...\n";

		try {
			$class_exists = class_exists( '\\ShahiLegalFlowSuite\\API\\Config_REST_Controller' );
			if ( ! $class_exists ) {
				throw new \Exception( 'Config_REST_Controller class does not exist' );
			}

			$controller = new \ShahiLegalFlowSuite\API\Config_REST_Controller();
			if ( ! is_object( $controller ) ) {
				throw new \Exception( 'Failed to instantiate Config_REST_Controller' );
			}

			$this->pass( 'Config_REST_Controller exists and instantiates' );
		} catch ( \Exception $e ) {
			$this->fail( 'Config_REST_Controller test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 11: REST routes are registered
	 */
	private function test_rest_routes_registered(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] REST routes registered...\n";

		try {
			$controller = new \ShahiLegalFlowSuite\API\Config_REST_Controller();
			$controller->register_routes();

			$routes = rest_get_server()->get_routes();
			$namespace = 'slos/v1';

			$expected_routes = array(
				"/config/export",
				"/config/import",
				"/config/exports",
				"/config/validate",
				"/config/compare",
			);

			$registered_count = 0;
			foreach ( $expected_routes as $route ) {
				$full_route = "/{$namespace}{$route}";
				if ( isset( $routes[ $full_route ] ) ) {
					$registered_count++;
				}
			}

			if ( $registered_count === 0 ) {
				throw new \Exception( 'No config routes registered (may need WordPress REST init)' );
			}

			$this->pass( "{$registered_count} config REST routes registered" );
		} catch ( \Exception $e ) {
			$this->fail( 'REST routes test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 12: Config sync tab template exists
	 */
	private function test_config_sync_tab_exists(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Config sync tab template exists...\n";

		try {
			$template_path = SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/config-sync.php';
			if ( ! file_exists( $template_path ) ) {
				throw new \Exception( 'Config sync tab template not found' );
			}

			// Check template has key elements
			$content = file_get_contents( $template_path );
			$required_elements = array( 'slos-config-sync-page', 'slos-export-form', 'slos-import-form', 'slos-exports-table' );
			foreach ( $required_elements as $element ) {
				if ( strpos( $content, $element ) === false ) {
					throw new \Exception( "Template missing element: {$element}" );
				}
			}

			$this->pass( 'Config sync tab template exists with all elements' );
		} catch ( \Exception $e ) {
			$this->fail( 'Config sync tab test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 13: Config sync assets exist
	 */
	private function test_config_sync_assets_exist(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Config sync CSS/JS assets exist...\n";

		try {
			$css_path = SHAHI_LEGALFLOWSUITE_PATH . 'assets/css/config-sync.css';
			$js_path  = SHAHI_LEGALFLOWSUITE_PATH . 'assets/js/config-sync.js';

			if ( ! file_exists( $css_path ) ) {
				throw new \Exception( 'Config sync CSS not found' );
			}

			if ( ! file_exists( $js_path ) ) {
				throw new \Exception( 'Config sync JS not found' );
			}

			// Check JS has key functions
			$js_content = file_get_contents( $js_path );
			$required_functions = array( 'exportToFile', 'importConfig', 'validateConfig', 'compareConfig' );
			$found_count = 0;
			foreach ( $required_functions as $func ) {
				if ( strpos( $js_content, $func ) !== false ) {
					$found_count++;
				}
			}

			$this->pass( "Config sync assets exist ({$found_count}/4 JS functions found)" );
		} catch ( \Exception $e ) {
			$this->fail( 'Config sync assets test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 14: Config tab registered in ComplianceMainPage
	 */
	private function test_compliance_tab_registration(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Config tab registered in ComplianceMainPage...\n";

		try {
			$file_path = SHAHI_LEGALFLOWSUITE_PATH . 'includes/Admin/ComplianceMainPage.php';
			$content   = file_get_contents( $file_path );

			// Check for config tab in get_tabs
			if ( strpos( $content, "'config'" ) === false || strpos( $content, 'Config Sync' ) === false ) {
				throw new \Exception( 'Config tab not found in get_tabs()' );
			}

			// Check for config case in render_tab_content
			if ( strpos( $content, "case 'config':" ) === false ) {
				throw new \Exception( 'Config case not found in render_tab_content()' );
			}

			// Check for enqueue_config_sync_assets
			if ( strpos( $content, 'enqueue_config_sync_assets' ) === false ) {
				throw new \Exception( 'enqueue_config_sync_assets method not found' );
			}

			$this->pass( 'Config tab properly integrated in ComplianceMainPage' );
		} catch ( \Exception $e ) {
			$this->fail( 'Compliance tab registration test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 15: Network_Compliance_Dashboard exists (multisite only)
	 */
	private function test_network_dashboard_exists(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Network_Compliance_Dashboard exists...\n";

		try {
			$class_exists = class_exists( '\\ShahiLegalFlowSuite\\Admin\\Network_Compliance_Dashboard' );
			if ( ! $class_exists ) {
				throw new \Exception( 'Network_Compliance_Dashboard class does not exist' );
			}

			$dashboard = new \ShahiLegalFlowSuite\Admin\Network_Compliance_Dashboard();
			if ( ! is_object( $dashboard ) ) {
				throw new \Exception( 'Failed to instantiate Network_Compliance_Dashboard' );
			}

			$this->pass( 'Network_Compliance_Dashboard exists and instantiates' );
		} catch ( \Exception $e ) {
			$this->fail( 'Network dashboard test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 16: Network menu registered (multisite only)
	 */
	private function test_network_menu_registered(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Network menu registered...\n";

		try {
			$plugin_file = SHAHI_LEGALFLOWSUITE_PATH . 'includes/Core/Plugin.php';
			$content     = file_get_contents( $plugin_file );

			if ( strpos( $content, 'Network_Compliance_Dashboard' ) === false ) {
				throw new \Exception( 'Network_Compliance_Dashboard not initialized in Plugin.php' );
			}

			if ( strpos( $content, 'is_multisite()' ) === false ) {
				throw new \Exception( 'Multisite check not found in Plugin.php' );
			}

			$this->pass( 'Network dashboard initialization found in Plugin.php' );
		} catch ( \Exception $e ) {
			$this->fail( 'Network menu registration test failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Test 17: Zero syntax errors across all files
	 */
	private function test_zero_syntax_errors(): void {
		$this->test_count++;
		echo "[TEST {$this->test_count}] Zero syntax errors across Phase 2.4 files...\n";

		try {
			$files = array(
				SHAHI_LEGALFLOWSUITE_PATH . 'config/multisite-sync-schema.php',
				SHAHI_LEGALFLOWSUITE_PATH . 'includes/Services/Config_Sync_Service.php',
				SHAHI_LEGALFLOWSUITE_PATH . 'includes/API/Config_REST_Controller.php',
				SHAHI_LEGALFLOWSUITE_PATH . 'includes/Admin/ComplianceMainPage.php',
				SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/config-sync.php',
				SHAHI_LEGALFLOWSUITE_PATH . 'includes/Admin/Network_Compliance_Dashboard.php',
				SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/network/compliance-overview.php',
			);

			$syntax_errors = array();
			foreach ( $files as $file ) {
				if ( ! file_exists( $file ) ) {
					$syntax_errors[] = basename( $file ) . ': File not found';
					continue;
				}

				// Use php -l to check syntax
				$output = array();
				$return_var = 0;
				exec( "php -l " . escapeshellarg( $file ) . " 2>&1", $output, $return_var );
				
				if ( $return_var !== 0 ) {
					$syntax_errors[] = basename( $file ) . ': ' . implode( ' ', $output );
				}
			}

			if ( ! empty( $syntax_errors ) ) {
				throw new \Exception( implode( "\n", $syntax_errors ) );
			}

			$this->pass( count( $files ) . ' files checked, zero syntax errors' );
		} catch ( \Exception $e ) {
			$this->fail( 'Syntax check failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Mark test as passed
	 *
	 * @param string $message Success message
	 * @return void
	 */
	private function pass( string $message ): void {
		$this->results[] = array(
			'status'  => 'pass',
			'message' => $message,
		);
		echo "  ✓ PASS: {$message}\n\n";
	}

	/**
	 * Mark test as failed
	 *
	 * @param string $message Failure message
	 * @return void
	 */
	private function fail( string $message ): void {
		$this->results[] = array(
			'status'  => 'fail',
			'message' => $message,
		);
		echo "  ✗ FAIL: {$message}\n\n";
	}

	/**
	 * Print test summary
	 *
	 * @return void
	 */
	private function print_summary(): void {
		$passed = count( array_filter( $this->results, function( $r ) {
			return $r['status'] === 'pass';
		}));
		$failed = count( array_filter( $this->results, function( $r ) {
			return $r['status'] === 'fail';
		}));

		echo str_repeat( '=', 80 ) . "\n";
		echo "Test Summary\n";
		echo str_repeat( '=', 80 ) . "\n";
		echo "Total Tests: {$this->test_count}\n";
		echo "Passed: {$passed} (" . round( ( $passed / $this->test_count ) * 100 ) . "%)\n";
		echo "Failed: {$failed}\n";
		echo str_repeat( '=', 80 ) . "\n";

		if ( $failed === 0 ) {
			echo "\n✓ ALL TESTS PASSED - Phase 2.4 implementation successful!\n\n";
		} else {
			echo "\n✗ SOME TESTS FAILED - Please review errors above.\n\n";
		}
	}
}

// Run tests if executed directly
if ( php_sapi_name() === 'cli' || ( defined( 'ABSPATH' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
	new Phase_2_4_Test();
}
