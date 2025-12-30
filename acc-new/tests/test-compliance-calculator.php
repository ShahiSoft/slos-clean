<?php
/**
 * Test Compliance Score Calculator
 * 
 * This script tests the Compliance_Score_Calculator service with various data scenarios.
 * Run from plugin root: php acc-new/tests/test-compliance-calculator.php
 */

// Load WordPress
require_once dirname(__DIR__, 2) . '/../../../wp-load.php';

// Load compliance constants
require_once dirname(__DIR__, 2) . '/config/compliance-constants.php';

use ShahiLegalFlowSuite\Services\Compliance_Score_Calculator;

echo "========================================\n";
echo "Compliance Score Calculator Test\n";
echo "========================================\n\n";

// Initialize calculator
$calculator = new Compliance_Score_Calculator();

// Test Scenario 1: Empty/Minimal Data
echo "TEST 1: Empty/Minimal Data\n";
echo "----------------------------\n";
delete_option('slos_cookie_inventory');
delete_option('slos_geo_rules');
delete_option('slos_legal_pages');
delete_option('slos_cookie_scan_time');
delete_option('shahi_legalflowsuite_settings');

$calculator->clear_cache();
$result1 = $calculator->calculate();
echo "Overall Score: {$result1['score']} ({$result1['grade']})\n";
echo "Dimensions:\n";
foreach ($result1['dimensions'] as $key => $dim) {
    $label = slos_get_dimension_labels()[$key] ?? $key;
    echo "  - {$label}: {$dim['score']} ({$dim['grade']})\n";
}
echo "\n";

// Test Scenario 2: Partial Data
echo "TEST 2: Partial Data\n";
echo "----------------------------\n";

// Add some cookies (50% categorized)
update_option('slos_cookie_inventory', [
    ['name' => 'cookie1', 'category' => 'essential'],
    ['name' => 'cookie2', 'category' => 'essential'],
    ['name' => 'cookie3', 'category' => ''],
    ['name' => 'cookie4', 'category' => ''],
]);

// Add 2 legal docs published
update_option('slos_legal_pages', [
    'privacy_policy' => ['page_id' => 123, 'status' => 'published'],
    'cookie_policy' => ['page_id' => 124, 'status' => 'draft'],
    'accessibility_statement' => ['page_id' => 125, 'status' => 'published'],
]);

// Add recent scan (1 day ago)
update_option('slos_cookie_scan_time', strtotime('-1 day'));

// Add partial banner config
update_option('shahi_legalflowsuite_settings', [
    'consent_banner' => [
        'position' => 'bottom',
        'primary_message' => 'We use cookies',
        'accept_button_text' => 'Accept',
        'reject_button_text' => '',
        'banner_bg_color' => '#000000',
        'text_color' => '',
    ]
]);

$calculator->clear_cache();
$result2 = $calculator->calculate();
echo "Overall Score: {$result2['score']} ({$result2['grade']})\n";
echo "Dimensions:\n";
foreach ($result2['dimensions'] as $key => $dim) {
    $label = slos_get_dimension_labels()[$key] ?? $key;
    echo "  - {$label}: {$dim['score']} ({$dim['grade']})\n";
}
echo "\n";

// Test Scenario 3: Complete Data
echo "TEST 3: Complete/Optimal Data\n";
echo "----------------------------\n";

// All cookies categorized
update_option('slos_cookie_inventory', [
    ['name' => 'cookie1', 'category' => 'essential'],
    ['name' => 'cookie2', 'category' => 'analytics'],
    ['name' => 'cookie3', 'category' => 'marketing'],
    ['name' => 'cookie4', 'category' => 'preferences'],
]);

// All legal docs published
update_option('slos_legal_pages', [
    'privacy_policy' => ['page_id' => 123, 'status' => 'published'],
    'cookie_policy' => ['page_id' => 124, 'status' => 'published'],
    'accessibility_statement' => ['page_id' => 125, 'status' => 'published'],
]);

// Add geo rules covering detected traffic
update_option('slos_geo_rules', [
    ['region' => 'EU', 'enabled' => true],
    ['region' => 'US-CA', 'enabled' => true],
    ['region' => 'US-VA', 'enabled' => true],
]);

// Recent scan (same day)
update_option('slos_cookie_scan_time', time());

// Complete banner config
update_option('shahi_legalflowsuite_settings', [
    'consent_banner' => [
        'position' => 'bottom',
        'primary_message' => 'We use cookies to improve your experience',
        'accept_button_text' => 'Accept All',
        'reject_button_text' => 'Reject All',
        'banner_bg_color' => '#000000',
        'text_color' => '#ffffff',
    ]
]);

$calculator->clear_cache();
$result3 = $calculator->calculate();
echo "Overall Score: {$result3['score']} ({$result3['grade']})\n";
echo "Dimensions:\n";
foreach ($result3['dimensions'] as $key => $dim) {
    $label = slos_get_dimension_labels()[$key] ?? $key;
    echo "  - {$label}: {$dim['score']} ({$dim['grade']})\n";
}
echo "\n";

// Test Scenario 4: Grade Mapping Verification
echo "TEST 4: Grade Mapping\n";
echo "----------------------------\n";
$grades = slos_get_grade_mappings();
foreach ($grades as $grade => $threshold) {
    echo "{$grade}: {$threshold}+\n";
}
echo "\n";

// Test Scenario 5: Weight Verification
echo "TEST 5: Dimension Weights (should total 100)\n";
echo "----------------------------\n";
$weights = slos_get_dimension_weights();
$total = 0;
foreach ($weights as $dimension => $weight) {
    $label = slos_get_dimension_labels()[$dimension] ?? $dimension;
    echo "{$label}: {$weight}%\n";
    $total += $weight;
}
echo "Total: {$total}%\n\n";

if ($total !== 100) {
    echo "⚠ WARNING: Weights don't total 100%!\n\n";
}

echo "========================================\n";
echo "Tests Complete\n";
echo "========================================\n";
