<?php
/**
 * Phase 0.3 Scanner Clarity & Refresh UX - Test Suite
 *
 * Tests all Phase 0.3 implementations:
 * - Scan metadata storage
 * - Last-scan summary card
 * - AJAX Rescan Now with progress
 * - Uncategorized cookies in readiness score
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Tests
 * @since       3.1.1
 * @usage       php acc-new/tests/test-scanner-refresh.php
 */

// Bootstrap WordPress if running standalone
if ( ! defined( 'ABSPATH' ) ) {
    echo "\n⚠️  This test file requires WordPress context.\n";
    echo "Running content-based validation instead...\n\n";
}

// Test counter
$tests_run    = 0;
$tests_passed = 0;
$tests_failed = 0;

function run_test( $name, $callback ) {
    global $tests_run, $tests_passed, $tests_failed;
    $tests_run++;
    
    echo "TEST {$tests_run}: {$name}\n";
    
    try {
        $result = $callback();
        if ( $result ) {
            $tests_passed++;
            echo "  ✅ PASS\n";
        } else {
            $tests_failed++;
            echo "  ❌ FAIL\n";
        }
    } catch ( Exception $e ) {
        $tests_failed++;
        echo "  ❌ FAIL: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// ====================
// TEST 1: Cookie_Scanner_Service Metadata Methods
// ====================
run_test( 'Cookie_Scanner_Service has scan metadata methods', function() {
    $file = __DIR__ . '/../../includes/Services/Cookie_Scanner_Service.php';
    
    if ( ! file_exists( $file ) ) {
        echo "    ❌ Cookie_Scanner_Service.php not found\n";
        return false;
    }
    
    $content = file_get_contents( $file );
    
    // Check for option_key_scan_meta property
    if ( strpos( $content, '$option_key_scan_meta' ) === false ) {
        echo "    ❌ Missing \$option_key_scan_meta property\n";
        return false;
    }
    echo "    ✓ Has \$option_key_scan_meta property\n";
    
    // Check for start_scan method
    if ( strpos( $content, 'public function start_scan' ) === false ) {
        echo "    ❌ Missing start_scan() method\n";
        return false;
    }
    echo "    ✓ Has start_scan() method\n";
    
    // Check for complete_scan method
    if ( strpos( $content, 'public function complete_scan' ) === false ) {
        echo "    ❌ Missing complete_scan() method\n";
        return false;
    }
    echo "    ✓ Has complete_scan() method\n";
    
    // Check for get_scan_metadata method
    if ( strpos( $content, 'public function get_scan_metadata' ) === false ) {
        echo "    ❌ Missing get_scan_metadata() method\n";
        return false;
    }
    echo "    ✓ Has get_scan_metadata() method\n";
    
    // Check for clear_scan_metadata method
    if ( strpos( $content, 'public function clear_scan_metadata' ) === false ) {
        echo "    ❌ Missing clear_scan_metadata() method\n";
        return false;
    }
    echo "    ✓ Has clear_scan_metadata() method\n";
    
    // Check that start_scan records metadata
    if ( strpos( $content, "'scan_type'" ) === false || 
         strpos( $content, "'started_at'" ) === false ||
         strpos( $content, "'status'" ) === false ||
         strpos( $content, "'coverage_level'" ) === false ) {
        echo "    ❌ start_scan() doesn't record required metadata fields\n";
        return false;
    }
    echo "    ✓ start_scan() records metadata fields\n";
    
    // Check that complete_scan updates metadata
    if ( strpos( $content, "'completed_at'" ) === false || 
         strpos( $content, "'duration'" ) === false ) {
        echo "    ❌ complete_scan() doesn't record completion metadata\n";
        return false;
    }
    echo "    ✓ complete_scan() records completion metadata\n";
    
    return true;
} );

// ====================
// TEST 2: Cookie_REST_Controller Uses Metadata
// ====================
run_test( 'Cookie_REST_Controller calls scan metadata methods', function() {
    $file = __DIR__ . '/../../includes/API/Cookie_REST_Controller.php';
    
    if ( ! file_exists( $file ) ) {
        echo "    ❌ Cookie_REST_Controller.php not found\n";
        return false;
    }
    
    $content = file_get_contents( $file );
    
    // Check for start_scan call
    if ( strpos( $content, '$this->service->start_scan' ) === false ) {
        echo "    ❌ trigger_scan() doesn't call start_scan()\n";
        return false;
    }
    echo "    ✓ trigger_scan() calls start_scan()\n";
    
    // Check for complete_scan call
    if ( strpos( $content, '$this->service->complete_scan' ) === false ) {
        echo "    ❌ trigger_scan() doesn't call complete_scan()\n";
        return false;
    }
    echo "    ✓ trigger_scan() calls complete_scan()\n";
    
    // Check for errors array initialization
    if ( strpos( $content, '$errors' ) === false ) {
        echo "    ❌ trigger_scan() doesn't track errors\n";
        return false;
    }
    echo "    ✓ trigger_scan() tracks errors\n";
    
    return true;
} );

// ====================
// TEST 3: Cookie Scanner Template Has Summary Card
// ====================
run_test( 'Cookie scanner template includes last-scan summary card', function() {
    $file = __DIR__ . '/../../templates/admin/compliance/tabs/cookie-scanner.php';
    
    if ( ! file_exists( $file ) ) {
        echo "    ❌ cookie-scanner.php template not found\n";
        return false;
    }
    
    $content = file_get_contents( $file );
    
    // Check for scanner service instantiation
    if ( strpos( $content, 'Cookie_Scanner_Service' ) === false ) {
        echo "    ❌ Template doesn't instantiate Cookie_Scanner_Service\n";
        return false;
    }
    echo "    ✓ Template instantiates Cookie_Scanner_Service\n";
    
    // Check for scan_meta retrieval
    if ( strpos( $content, 'get_scan_metadata' ) === false ) {
        echo "    ❌ Template doesn't call get_scan_metadata()\n";
        return false;
    }
    echo "    ✓ Template calls get_scan_metadata()\n";
    
    // Check for scan summary card CSS
    if ( strpos( $content, 'slos-scan-summary-card' ) === false ) {
        echo "    ❌ Missing scan summary card CSS class\n";
        return false;
    }
    echo "    ✓ Has scan summary card CSS class\n";
    
    // Check for scan summary content elements
    if ( strpos( $content, 'slos-scan-summary-icon' ) === false ||
         strpos( $content, 'slos-scan-summary-content' ) === false ||
         strpos( $content, 'slos-scan-summary-stats' ) === false ) {
        echo "    ❌ Missing scan summary card HTML structure\n";
        return false;
    }
    echo "    ✓ Has scan summary card HTML structure\n";
    
    // Check for metadata display
    if ( strpos( $content, 'completed_at' ) === false ||
         strpos( $content, 'duration' ) === false ||
         strpos( $content, 'pages_scanned' ) === false ||
         strpos( $content, 'cookies_found' ) === false ) {
        echo "    ❌ Missing metadata display fields\n";
        return false;
    }
    echo "    ✓ Displays all metadata fields\n";
    
    // Check for "no scan" state
    if ( strpos( $content, 'no-scan' ) === false ) {
        echo "    ❌ Missing 'no scan' state handling\n";
        return false;
    }
    echo "    ✓ Handles 'no scan' state\n";
    
    return true;
} );

// ====================
// TEST 4: Rescan Now Button Implementation
// ====================
run_test( 'Rescan Now button and handler implemented', function() {
    $file = __DIR__ . '/../../templates/admin/compliance/tabs/cookie-scanner.php';
    
    if ( ! file_exists( $file ) ) {
        echo "    ❌ cookie-scanner.php template not found\n";
        return false;
    }
    
    $content = file_get_contents( $file );
    
    // Check for rescan-now button
    if ( strpos( $content, 'id="rescan-now"' ) === false ) {
        echo "    ❌ Missing #rescan-now button\n";
        return false;
    }
    echo "    ✓ Has #rescan-now button\n";
    
    // Check for JavaScript handler
    if ( strpos( $content, "$('#run-scan, #rescan-now')" ) === false ) {
        echo "    ❌ JavaScript doesn't handle #rescan-now button\n";
        return false;
    }
    echo "    ✓ JavaScript handles #rescan-now button\n";
    
    // Check for enhanced progress stages
    if ( strpos( $content, 'stages = [' ) === false ) {
        echo "    ❌ Missing enhanced progress stages\n";
        return false;
    }
    echo "    ✓ Has enhanced progress stages\n";
    
    // Verify stage labels exist
    if ( strpos( $content, 'Initializing scan' ) === false ||
         strpos( $content, 'Detecting cookies' ) === false ||
         strpos( $content, 'Classifying cookies' ) === false ) {
        echo "    ❌ Missing stage labels\n";
        return false;
    }
    echo "    ✓ Has stage labels for progress\n";
    
    return true;
} );

// ====================
// TEST 5: Compliance Score Calculator Updates
// ====================
run_test( 'Compliance score factors in uncategorized cookies', function() {
    $file = __DIR__ . '/../../includes/Services/Compliance_Score_Calculator.php';
    
    if ( ! file_exists( $file ) ) {
        echo "    ❌ Compliance_Score_Calculator.php not found\n";
        return false;
    }
    
    $content = file_get_contents( $file );
    
    // Check for detected_cookies option usage
    if ( strpos( $content, "get_option( 'slos_detected_cookies'" ) === false ) {
        echo "    ❌ Doesn't check slos_detected_cookies option\n";
        return false;
    }
    echo "    ✓ Checks slos_detected_cookies option\n";
    
    // Check for uncategorized counter
    if ( strpos( $content, '$uncategorized' ) === false ) {
        echo "    ❌ Doesn't track uncategorized cookies\n";
        return false;
    }
    echo "    ✓ Tracks uncategorized cookies\n";
    
    // Check for uncategorized status check
    if ( strpos( $content, "'uncategorized' !== \$status" ) === false &&
         strpos( $content, "'uncategorized' !== strtolower( \$status )" ) === false ) {
        echo "    ❌ Doesn't check for uncategorized status\n";
        return false;
    }
    echo "    ✓ Checks for uncategorized status\n";
    
    // Check for uncategorized penalty
    if ( strpos( $content, 'uncategorized_penalty' ) === false ) {
        echo "    ❌ Doesn't apply uncategorized penalty\n";
        return false;
    }
    echo "    ✓ Applies uncategorized penalty\n";
    
    // Check for uncategorized in details
    if ( strpos( $content, "'uncategorized_cookies'" ) === false ) {
        echo "    ❌ Doesn't include uncategorized count in details\n";
        return false;
    }
    echo "    ✓ Includes uncategorized count in details\n";
    
    return true;
} );

// ====================
// TEST 6: File Syntax Validation
// ====================
run_test( 'All modified files pass PHP syntax check', function() {
    $files = [
        __DIR__ . '/../../includes/Services/Cookie_Scanner_Service.php',
        __DIR__ . '/../../includes/API/Cookie_REST_Controller.php',
        __DIR__ . '/../../includes/Services/Compliance_Score_Calculator.php',
        __DIR__ . '/../../templates/admin/compliance/tabs/cookie-scanner.php',
    ];
    
    $all_valid = true;
    
    foreach ( $files as $file ) {
        if ( ! file_exists( $file ) ) {
            echo "    ❌ File not found: " . basename( $file ) . "\n";
            $all_valid = false;
            continue;
        }
        
        $output = [];
        $return_var = 0;
        exec( "php -l " . escapeshellarg( $file ) . " 2>&1", $output, $return_var );
        
        if ( $return_var !== 0 ) {
            echo "    ❌ Syntax error in " . basename( $file ) . "\n";
            echo "       " . implode( "\n       ", $output ) . "\n";
            $all_valid = false;
        } else {
            echo "    ✓ " . basename( $file ) . " - No syntax errors\n";
        }
    }
    
    return $all_valid;
} );

// ====================
// TEST 7: Backward Compatibility
// ====================
run_test( 'Backward compatibility maintained', function() {
    $scanner_file = __DIR__ . '/../../includes/Services/Cookie_Scanner_Service.php';
    $calculator_file = __DIR__ . '/../../includes/Services/Compliance_Score_Calculator.php';
    
    $scanner_content = file_get_contents( $scanner_file );
    $calculator_content = file_get_contents( $calculator_file );
    
    // Check that slos_cookie_scan_time is still updated
    if ( strpos( $scanner_content, "update_option( 'slos_cookie_scan_time'" ) === false ) {
        echo "    ❌ Cookie scanner doesn't update slos_cookie_scan_time for backward compatibility\n";
        return false;
    }
    echo "    ✓ Cookie scanner updates slos_cookie_scan_time\n";
    
    // Check that old inventory is still supported
    if ( strpos( $calculator_content, "get_option( 'slos_cookie_inventory'" ) === false ) {
        echo "    ❌ Calculator doesn't check slos_cookie_inventory\n";
        return false;
    }
    echo "    ✓ Calculator checks slos_cookie_inventory\n";
    
    // Check fallback to detected_cookies
    if ( strpos( $calculator_content, "\$inventory ) ? \$inventory : \$detected_cookies" ) === false &&
         strpos( $calculator_content, "empty( \$inventory ) ? \$detected_cookies" ) === false ) {
        echo "    ❌ Calculator doesn't fallback to detected_cookies\n";
        return false;
    }
    echo "    ✓ Calculator fallbacks to detected_cookies\n";
    
    return true;
} );

// ====================
// TEST 8: Integration Points
// ====================
run_test( 'All integration points properly connected', function() {
    // Check that Cookie_REST_Controller properly integrates with Cookie_Scanner_Service
    $controller_file = __DIR__ . '/../../includes/API/Cookie_REST_Controller.php';
    $controller_content = file_get_contents( $controller_file );
    
    if ( strpos( $controller_content, "new Cookie_Scanner_Service()" ) === false ) {
        echo "    ❌ Controller doesn't instantiate Cookie_Scanner_Service\n";
        return false;
    }
    echo "    ✓ Controller instantiates Cookie_Scanner_Service\n";
    
    // Check that template uses service instance
    $template_file = __DIR__ . '/../../templates/admin/compliance/tabs/cookie-scanner.php';
    $template_content = file_get_contents( $template_file );
    
    if ( strpos( $template_content, '$scanner_service->get_scan_metadata()' ) === false ) {
        echo "    ❌ Template doesn't call service method\n";
        return false;
    }
    echo "    ✓ Template calls service method\n";
    
    // Check that AJAX calls correct endpoint
    if ( strpos( $template_content, "url: API_BASE + '/cookies/scan'" ) === false ) {
        echo "    ❌ JavaScript doesn't call correct endpoint\n";
        return false;
    }
    echo "    ✓ JavaScript calls correct endpoint\n";
    
    return true;
} );

// ====================
// SUMMARY
// ====================
echo "=====================================\n";
echo "Phase 0.3 Test Summary\n";
echo "=====================================\n";
echo "Total Tests:  {$tests_run}\n";
echo "Passed:       {$tests_passed} ✅\n";
echo "Failed:       {$tests_failed} ❌\n";
echo "Success Rate: " . round( ( $tests_passed / $tests_run ) * 100, 1 ) . "%\n";
echo "=====================================\n";

if ( $tests_failed === 0 ) {
    echo "\n🎉 All Phase 0.3 tests passed!\n";
    echo "\nPhase 0.3 'Scanner Clarity & Refresh UX' implementation complete:\n";
    echo "  ✅ Scan metadata storage (type, duration, coverage)\n";
    echo "  ✅ Last-scan summary card with detailed info\n";
    echo "  ✅ AJAX Rescan Now with enhanced progress stages\n";
    echo "  ✅ Uncategorized cookies factored into readiness score\n";
    echo "  ✅ All files pass syntax validation\n";
    echo "  ✅ Backward compatibility maintained\n\n";
    exit( 0 );
} else {
    echo "\n⚠️  Some tests failed. Please review the output above.\n\n";
    exit( 1 );
}
