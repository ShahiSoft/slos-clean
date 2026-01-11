<?php
/**
 * Simple Hooks Validation Test
 *
 * @package ShahiLegalFlowSuite
 */

echo "=== WordPress Hooks Validation ===\n\n";

// Load Hooks class directly
require_once __DIR__ . '/Hooks.php';

use ShahiLegalFlowSuite\Core\Hooks;

// Test 1: Class exists
echo '1. Hooks class loaded: ';
echo class_exists( 'ShahiLegalFlowSuite\Core\Hooks' ) ? "✓ PASS\n" : "✗ FAIL\n";

// Test 2: Get action hooks
echo '2. Action hooks method: ';
$actions = Hooks::get_action_hooks();
echo is_array( $actions ) ? '✓ PASS (' . count( $actions ) . " hooks)\n" : "✗ FAIL\n";

// Test 3: Get filter hooks
echo '3. Filter hooks method: ';
$filters = Hooks::get_filter_hooks();
echo is_array( $filters ) ? '✓ PASS (' . count( $filters ) . " hooks)\n" : "✗ FAIL\n";

// Test 4: Category method
echo '4. Category filtering: ';
$consent_hooks = Hooks::get_hooks_by_category( 'consent' );
echo is_array( $consent_hooks ) ? '✓ PASS (' . count( $consent_hooks ) . " hooks)\n" : "✗ FAIL\n";

// Test 5: Documentation generation
echo '5. Documentation generator: ';
$docs = Hooks::generate_documentation();
echo ( is_string( $docs ) && strlen( $docs ) > 100 ) ? '✓ PASS (' . number_format( strlen( $docs ) ) . " bytes)\n" : "✗ FAIL\n";

// List all action hooks
echo "\n=== Action Hooks (10 total) ===\n";
foreach ( $actions as $name => $data ) {
	echo "  ✓ $name\n";
}

// List all filter hooks
echo "\n=== Filter Hooks (13 total) ===\n";
foreach ( $filters as $name => $data ) {
	echo "  ✓ $name\n";
}

echo "\n=== Validation Complete ===\n";
echo 'Total Hooks: ' . ( count( $actions ) + count( $filters ) ) . "\n";
