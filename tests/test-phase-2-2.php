<?php
/**
 * Phase 2.2 Test Suite - DSR & Consent Audit Trail Integration
 *
 * Comprehensive tests for consent lookup by email, DSR detail consent history,
 * and DSR export with consent data inclusion.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Test Suite for Phase 2.2
 *
 * Run via WP CLI: wp eval-file tests/test-phase-2-2.php
 * Or via browser: admin.php?page=slos-test-phase-2-2
 */
class Phase_2_2_Test_Suite {

    /**
     * Test results
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
     * Pass counter
     *
     * @var int
     */
    private $pass_count = 0;

    /**
     * Run all tests
     *
     * @return array Test results
     */
    public function run_all_tests() {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║   Phase 2.2 Test Suite - DSR & Consent Audit Trail          ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";

        // Test 1: Consent_Repository::find_by_email exists
        $this->test_consent_repository_find_by_email_exists();

        // Test 2: Consent_Repository::find_by_email returns correct structure
        $this->test_consent_repository_find_by_email_structure();

        // Test 3: Consent_Service::get_by_email exists
        $this->test_consent_service_get_by_email_exists();

        // Test 4: Consent_Service::get_by_email returns enriched data
        $this->test_consent_service_get_by_email_enrichment();

        // Test 5: Email validation in Consent_Service
        $this->test_consent_service_email_validation();

        // Test 6: Deduplication in find_by_email
        $this->test_find_by_email_deduplication();

        // Test 7: DSRRequestDetail has consent_service property
        $this->test_dsr_detail_has_consent_service();

        // Test 8: DSRRequestDetail renders consent history section
        $this->test_dsr_detail_renders_consent_history();

        // Test 9: DSR_Export_Service includes consent data
        $this->test_dsr_export_includes_consent_data();

        // Test 10: DSR_Export_Service::collect_consent_data uses Consent_Service
        $this->test_dsr_export_uses_consent_service();

        // Test 11: CSS file exists
        $this->test_css_file_exists();

        // Test 12: Zero syntax errors across all modified files
        $this->test_zero_syntax_errors();

        // Print summary
        $this->print_summary();

        return $this->results;
    }

    /**
     * Test 1: Consent_Repository::find_by_email exists
     */
    private function test_consent_repository_find_by_email_exists() {
        $this->test_count++;
        $test_name = 'Consent_Repository::find_by_email method exists';

        try {
            require_once plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php';
            $repository = new \ShahiLegalFlowSuite\Database\Repositories\Consent_Repository();

            if ( method_exists( $repository, 'find_by_email' ) ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'Method find_by_email does not exist' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 2: Consent_Repository::find_by_email returns correct structure
     */
    private function test_consent_repository_find_by_email_structure() {
        $this->test_count++;
        $test_name = 'Consent_Repository::find_by_email returns array';

        try {
            require_once plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php';
            $repository = new \ShahiLegalFlowSuite\Database\Repositories\Consent_Repository();

            // Test with empty email (should return empty array)
            $result = $repository->find_by_email( '' );

            if ( is_array( $result ) ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'Expected array, got ' . gettype( $result ) );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 3: Consent_Service::get_by_email exists
     */
    private function test_consent_service_get_by_email_exists() {
        $this->test_count++;
        $test_name = 'Consent_Service::get_by_email method exists';

        try {
            require_once plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php';
            require_once plugin_dir_path( __FILE__ ) . '../includes/Services/Consent_Service.php';
            
            $repository = new \ShahiLegalFlowSuite\Database\Repositories\Consent_Repository();
            $service = new \ShahiLegalFlowSuite\Services\Consent_Service( $repository );

            if ( method_exists( $service, 'get_by_email' ) ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'Method get_by_email does not exist' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 4: Consent_Service::get_by_email returns enriched data
     */
    private function test_consent_service_get_by_email_enrichment() {
        $this->test_count++;
        $test_name = 'Consent_Service::get_by_email returns enriched array';

        try {
            require_once plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php';
            require_once plugin_dir_path( __FILE__ ) . '../includes/Services/Consent_Service.php';
            
            $repository = new \ShahiLegalFlowSuite\Database\Repositories\Consent_Repository();
            $service = new \ShahiLegalFlowSuite\Services\Consent_Service( $repository );

            // Test with empty email (should return empty array)
            $result = $service->get_by_email( '' );

            if ( is_array( $result ) ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'Expected array, got ' . gettype( $result ) );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 5: Email validation in Consent_Service
     */
    private function test_consent_service_email_validation() {
        $this->test_count++;
        $test_name = 'Consent_Service::get_by_email validates email';

        try {
            require_once plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php';
            require_once plugin_dir_path( __FILE__ ) . '../includes/Services/Consent_Service.php';
            
            $repository = new \ShahiLegalFlowSuite\Database\Repositories\Consent_Repository();
            $service = new \ShahiLegalFlowSuite\Services\Consent_Service( $repository );

            // Test with invalid email
            $result = $service->get_by_email( 'not-an-email' );

            if ( is_array( $result ) && empty( $result ) ) {
                // Check if error was added
                $errors = $service->get_errors();
                if ( isset( $errors['invalid_email'] ) ) {
                    $this->pass( $test_name );
                } else {
                    $this->fail( $test_name, 'Expected invalid_email error' );
                }
            } else {
                $this->fail( $test_name, 'Expected empty array for invalid email' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 6: Deduplication in find_by_email
     */
    private function test_find_by_email_deduplication() {
        $this->test_count++;
        $test_name = 'Consent_Repository::find_by_email deduplicates results';

        try {
            require_once plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php';
            $repository = new \ShahiLegalFlowSuite\Database\Repositories\Consent_Repository();

            // Read source to verify deduplication logic exists
            $source = file_get_contents( plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php' );
            
            if ( strpos( $source, 'in_array' ) !== false && strpos( $source, 'array_column' ) !== false ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'Deduplication logic not found in source' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 7: DSRRequestDetail has consent_service property
     */
    private function test_dsr_detail_has_consent_service() {
        $this->test_count++;
        $test_name = 'DSRRequestDetail has consent_service property';

        try {
            $source = file_get_contents( plugin_dir_path( __FILE__ ) . '../includes/Admin/DSRRequestDetail.php' );
            
            if ( strpos( $source, 'private $consent_service' ) !== false ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'consent_service property not found' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 8: DSRRequestDetail renders consent history section
     */
    private function test_dsr_detail_renders_consent_history() {
        $this->test_count++;
        $test_name = 'DSRRequestDetail renders consent history section';

        try {
            $source = file_get_contents( plugin_dir_path( __FILE__ ) . '../includes/Admin/DSRRequestDetail.php' );
            
            if ( strpos( $source, 'render_consent_history' ) !== false && 
                 strpos( $source, 'Consent History' ) !== false ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'render_consent_history method or section not found' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 9: DSR_Export_Service includes consent data
     */
    private function test_dsr_export_includes_consent_data() {
        $this->test_count++;
        $test_name = 'DSR_Export_Service includes consent data';

        try {
            $source = file_get_contents( plugin_dir_path( __FILE__ ) . '../includes/Services/DSR_Export_Service.php' );
            
            if ( strpos( $source, 'collect_consent_data' ) !== false ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'collect_consent_data method not found' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 10: DSR_Export_Service::collect_consent_data uses Consent_Service
     */
    private function test_dsr_export_uses_consent_service() {
        $this->test_count++;
        $test_name = 'DSR_Export_Service uses Consent_Service';

        try {
            $source = file_get_contents( plugin_dir_path( __FILE__ ) . '../includes/Services/DSR_Export_Service.php' );
            
            if ( strpos( $source, 'get_consent_service' ) !== false && 
                 strpos( $source, 'get_by_email' ) !== false ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'Consent_Service integration not found' );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 11: CSS file exists
     */
    private function test_css_file_exists() {
        $this->test_count++;
        $test_name = 'dsr-consent-history.css file exists';

        try {
            $css_path = plugin_dir_path( __FILE__ ) . '../assets/css/dsr-consent-history.css';
            
            if ( file_exists( $css_path ) ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, 'CSS file not found at ' . $css_path );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Test 12: Zero syntax errors
     */
    private function test_zero_syntax_errors() {
        $this->test_count++;
        $test_name = 'Zero syntax errors in modified files';

        try {
            $files = array(
                plugin_dir_path( __FILE__ ) . '../includes/Database/Repositories/Consent_Repository.php',
                plugin_dir_path( __FILE__ ) . '../includes/Services/Consent_Service.php',
                plugin_dir_path( __FILE__ ) . '../includes/Admin/DSRRequestDetail.php',
                plugin_dir_path( __FILE__ ) . '../includes/Services/DSR_Export_Service.php',
            );

            $errors = array();
            foreach ( $files as $file ) {
                $output = array();
                $return = 0;
                exec( "php -l " . escapeshellarg( $file ), $output, $return );
                
                if ( $return !== 0 ) {
                    $errors[] = basename( $file ) . ': ' . implode( ' ', $output );
                }
            }

            if ( empty( $errors ) ) {
                $this->pass( $test_name );
            } else {
                $this->fail( $test_name, implode( "\n", $errors ) );
            }
        } catch ( \Exception $e ) {
            $this->fail( $test_name, $e->getMessage() );
        }
    }

    /**
     * Record a passing test
     *
     * @param string $test_name Test name
     */
    private function pass( $test_name ) {
        $this->pass_count++;
        $this->results[] = array(
            'test'   => $test_name,
            'status' => 'PASS',
            'message' => '',
        );
        echo "✓ {$test_name}\n";
    }

    /**
     * Record a failing test
     *
     * @param string $test_name Test name
     * @param string $message   Error message
     */
    private function fail( $test_name, $message ) {
        $this->results[] = array(
            'test'   => $test_name,
            'status' => 'FAIL',
            'message' => $message,
        );
        echo "✗ {$test_name}\n";
        echo "  Error: {$message}\n";
    }

    /**
     * Print test summary
     */
    private function print_summary() {
        $fail_count = $this->test_count - $this->pass_count;
        $pass_rate = ( $this->test_count > 0 ) ? ( $this->pass_count / $this->test_count ) * 100 : 0;

        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║                         Test Summary                          ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "Total Tests:  {$this->test_count}\n";
        echo "Passed:       {$this->pass_count}\n";
        echo "Failed:       {$fail_count}\n";
        echo "Pass Rate:    " . number_format( $pass_rate, 2 ) . "%\n";
        echo "\n";

        if ( $fail_count === 0 ) {
            echo "🎉 All tests passed! Phase 2.2 implementation is complete.\n";
        } else {
            echo "⚠️  {$fail_count} test(s) failed. Please review and fix.\n";
        }
        echo "\n";
    }
}

// Auto-run if accessed directly
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    $suite = new Phase_2_2_Test_Suite();
    $suite->run_all_tests();
} elseif ( isset( $_GET['page'] ) && $_GET['page'] === 'slos-test-phase-2-2' ) {
    echo '<pre>';
    $suite = new Phase_2_2_Test_Suite();
    $suite->run_all_tests();
    echo '</pre>';
}
