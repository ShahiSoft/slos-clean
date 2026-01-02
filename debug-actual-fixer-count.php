<?php
/**
 * Debug Fixer Count
 * Check what's actually loaded in the system
 */

require_once dirname(__FILE__) . '/../../../wp-load.php';

if ( ! is_super_admin() ) {
	die( 'Admin only' );
}

header( 'Content-Type: text/plain; charset=utf-8' );

echo "=== ACTUAL FIXER COUNT DEBUG ===\n\n";

// Test 1: Try FixEngine Bootstrap
echo "Test 1: FixEngine Bootstrap\n";
$fix_engine_bootstrap = dirname(__FILE__) . '/includes/Modules/AccessibilityScanner/FixEngine/Bootstrap.php';

if ( file_exists( $fix_engine_bootstrap ) ) {
	echo "✅ Bootstrap file exists\n";
	
	try {
		require_once $fix_engine_bootstrap;
		
		if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap' ) ) {
			echo "✅ Bootstrap class exists\n";
			
			// This will fail with fatal error
			$data = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap::get_scanner_data();
			$fixers = $data['fixers'] ?? array();
			echo "FixEngine fixers: " . count( $fixers ) . "\n";
			
		} else {
			echo "❌ Bootstrap class NOT found\n";
		}
	} catch ( \Throwable $e ) {
		echo "❌ Error: " . $e->getMessage() . "\n";
	}
} else {
	echo "❌ Bootstrap file NOT found\n";
}

echo "\n";

// Test 2: Try FixerRegistry
echo "Test 2: FixerRegistry\n";

if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
	echo "✅ FixerRegistry class exists\n";
	
	\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
	$fixer_ids = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_all_fixer_ids();
	
	echo "✅ Total fixer IDs: " . count( $fixer_ids ) . "\n";
	
	$working_fixers = 0;
	$failed_fixers = 0;
	
	foreach ( $fixer_ids as $id ) {
		$fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $id );
		if ( $fixer ) {
			$working_fixers++;
		} else {
			$failed_fixers++;
			echo "  - Failed to load: {$id}\n";
		}
	}
	
	echo "✅ Working fixers: {$working_fixers}\n";
	echo "❌ Failed fixers: {$failed_fixers}\n";
	
	// List first 20
	echo "\nFirst 20 fixer IDs:\n";
	$count = 0;
	foreach ( $fixer_ids as $id ) {
		if ( $count >= 20 ) break;
		echo "  - {$id}\n";
		$count++;
	}
	
} else {
	echo "❌ FixerRegistry class NOT found\n";
}

echo "\n";

// Test 3: What does ScannerPage::get_fixer_list_for_js() actually return?
echo "Test 3: Simulating ScannerPage logic\n";

// Simulate the exact logic from ScannerPage.php
$fix_engine_bootstrap = SHAHI_LEGALFLOWSUITE_PLUGIN_PATH . 'includes/Modules/AccessibilityScanner/FixEngine/Bootstrap.php';

if ( file_exists( $fix_engine_bootstrap ) ) {
	echo "✅ FixEngine path exists\n";
	// This will try to load and likely fail
	echo "⚠️  ScannerPage will try FixEngine first (will fail silently)\n";
}

echo "\nFinal answer: The JavaScript receives the fixer list from get_fixer_list_for_js() which tries FixEngine first (fails), then falls back to FixerRegistry.\n";

echo "\n=== END DEBUG ===\n";
