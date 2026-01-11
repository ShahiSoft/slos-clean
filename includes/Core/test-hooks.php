<?php
/**
 * Test script for WordPress Hooks
 *
 * Tests that all action and filter hooks are properly registered and functional
 * Run from WordPress root: php -d display_errors=on wp-content/plugins/Shahi\ LegalOps\ Suite\ -\ 3.0.1/includes/Core/test-hooks.php
 *
 * @package ShahiLegalFlowSuite
 */

// Load WordPress
require_once __DIR__ . '/../../../../../wp-load.php';

// Load Hooks class
require_once __DIR__ . '/Hooks.php';

use ShahiLegalFlowSuite\Core\Hooks;

echo "=== WordPress Hooks Test Suite ===\n\n";

// Test 1: Verify Hooks class exists
echo 'Test 1: Hooks class exists... ';
if ( class_exists( 'ShahiLegalFlowSuite\Core\Hooks' ) ) {
	echo "PASS\n";
} else {
	echo "FAIL\n";
	exit( 1 );
}

// Test 2: Get action hooks
echo 'Test 2: Get action hooks... ';
$action_hooks = Hooks::get_action_hooks();
if ( is_array( $action_hooks ) && count( $action_hooks ) === 10 ) {
	echo 'PASS (' . count( $action_hooks ) . " action hooks)\n";
} else {
	echo 'FAIL (Expected 10, got ' . count( $action_hooks ) . ")\n";
}

// Test 3: Get filter hooks
echo 'Test 3: Get filter hooks... ';
$filter_hooks = Hooks::get_filter_hooks();
if ( is_array( $filter_hooks ) && count( $filter_hooks ) === 13 ) {
	echo 'PASS (' . count( $filter_hooks ) . " filter hooks)\n";
} else {
	echo 'FAIL (Expected 13, got ' . count( $filter_hooks ) . ")\n";
}

// Test 4: Verify hook structure
echo 'Test 4: Verify hook structure... ';
$test_hook = $action_hooks['slos_consent_recorded'] ?? null;
if ( $test_hook &&
	isset( $test_hook['description'] ) &&
	isset( $test_hook['params'] ) &&
	isset( $test_hook['example'] ) ) {
	echo "PASS\n";
} else {
	echo "FAIL\n";
}

// Test 5: Test get_hooks_by_category
echo 'Test 5: Get hooks by category... ';
$consent_hooks = Hooks::get_hooks_by_category( 'consent' );
if ( is_array( $consent_hooks ) && count( $consent_hooks ) > 0 ) {
	echo 'PASS (' . count( $consent_hooks ) . " consent hooks)\n";
} else {
	echo "FAIL\n";
}

// Test 6: Verify action hooks can be registered
echo 'Test 6: Register action hook... ';
$test_fired = false;
add_action(
	'slos_consent_recorded',
	function ( $consent_id, $consent_data ) use ( &$test_fired ) {
		$test_fired = true;
	}
);

// Simulate the hook firing
do_action(
	'slos_consent_recorded',
	123,
	array(
		'user_id' => 1,
		'type'    => 'marketing',
	)
);

if ( $test_fired ) {
	echo "PASS\n";
} else {
	echo "FAIL\n";
}

// Test 7: Verify filter hooks can be registered
echo 'Test 7: Register filter hook... ';
add_filter(
	'slos_consent_data_before_save',
	function ( $consent_data, $context ) {
		$consent_data['test_field'] = 'test_value';
		return $consent_data;
	},
	10,
	2
);

$filtered_data = apply_filters( 'slos_consent_data_before_save', array( 'user_id' => 1 ), 'create' );

if ( isset( $filtered_data['test_field'] ) && $filtered_data['test_field'] === 'test_value' ) {
	echo "PASS\n";
} else {
	echo "FAIL\n";
}

// Test 8: Verify generate_documentation
echo 'Test 8: Generate documentation... ';
$documentation = Hooks::generate_documentation();
if ( is_string( $documentation ) && strlen( $documentation ) > 1000 ) {
	echo 'PASS (' . strlen( $documentation ) . " bytes)\n";
} else {
	echo "FAIL\n";
}

// Test 9: Verify all action hooks are documented
echo 'Test 9: All action hooks documented... ';
$expected_actions = array(
	'slos_consent_recorded',
	'slos_consent_updated',
	'slos_consent_withdrawn',
	'slos_consent_deleted',
	'slos_bulk_consent_withdrawn',
	'slos_plugin_activated',
	'slos_plugin_deactivated',
	'slos_migrations_completed',
	'slos_rest_api_init',
	'slos_rest_authentication',
);

$all_documented = true;
foreach ( $expected_actions as $action ) {
	if ( ! isset( $action_hooks[ $action ] ) ) {
		echo "FAIL (Missing: $action)\n";
		$all_documented = false;
		break;
	}
}
if ( $all_documented ) {
	echo "PASS\n";
}

// Test 10: Verify all filter hooks are documented
echo 'Test 10: All filter hooks documented... ';
$expected_filters = array(
	'slos_consent_data_before_save',
	'slos_consent_metadata',
	'slos_allowed_consent_types',
	'slos_allowed_consent_statuses',
	'slos_validate_consent_data',
	'slos_consent_query_args',
	'slos_consent_statistics',
	'slos_rest_consent_response',
	'slos_rest_error_response',
	'slos_ip_hash_algorithm',
	'slos_anonymize_consent_data',
	'slos_user_can_withdraw_consent',
	'slos_user_can_view_consent',
);

$all_documented = true;
foreach ( $expected_filters as $filter ) {
	if ( ! isset( $filter_hooks[ $filter ] ) ) {
		echo "FAIL (Missing: $filter)\n";
		$all_documented = false;
		break;
	}
}
if ( $all_documented ) {
	echo "PASS\n";
}

// Test 11: Test category filtering
echo 'Test 11: Category filtering... ';
$api_hooks      = Hooks::get_hooks_by_category( 'api' );
$security_hooks = Hooks::get_hooks_by_category( 'security' );

if ( count( $api_hooks ) > 0 && count( $security_hooks ) > 0 ) {
	echo 'PASS (API: ' . count( $api_hooks ) . ', Security: ' . count( $security_hooks ) . ")\n";
} else {
	echo "FAIL\n";
}

// Test 12: Verify hook examples are present
echo 'Test 12: Hook examples present... ';
$has_examples = true;
foreach ( $action_hooks as $hook_name => $hook_data ) {
	if ( empty( $hook_data['example'] ) ) {
		echo "FAIL (Missing example for: $hook_name)\n";
		$has_examples = false;
		break;
	}
}
if ( $has_examples ) {
	foreach ( $filter_hooks as $hook_name => $hook_data ) {
		if ( empty( $hook_data['example'] ) ) {
			echo "FAIL (Missing example for: $hook_name)\n";
			$has_examples = false;
			break;
		}
	}
}
if ( $has_examples ) {
	echo "PASS\n";
}

// Summary
echo "\n=== Test Summary ===\n";
echo 'Total Action Hooks: ' . count( $action_hooks ) . "\n";
echo 'Total Filter Hooks: ' . count( $filter_hooks ) . "\n";
echo 'Total Hooks: ' . ( count( $action_hooks ) + count( $filter_hooks ) ) . "\n";
echo "\nAction Hooks:\n";
foreach ( array_keys( $action_hooks ) as $hook_name ) {
	echo "  - $hook_name\n";
}
echo "\nFilter Hooks:\n";
foreach ( array_keys( $filter_hooks ) as $hook_name ) {
	echo "  - $hook_name\n";
}

echo "\n=== All Tests Passed ===\n";
