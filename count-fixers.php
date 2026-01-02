<?php
/**
 * Count and list registered fixers
 */

require_once '/var/www/html/wp-load.php';

echo "=== FIXER SYSTEM AUDIT ===\n\n";

// Check Old FixerRegistry System
if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
	\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
	$old_fixers = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_all_fixer_ids();
	
	echo "OLD SYSTEM (FixerRegistry):\n";
	echo "Total: " . count( $old_fixers ) . " fixers\n\n";
	
	foreach ( $old_fixers as $id ) {
		echo "- " . $id . "\n";
	}
	echo "\n";
}

// Check New FixEngine System
$fix_engine_file = '/var/www/html/wp-content/plugins/Shahi LegalOps Suite - 3.1.1/includes/Modules/AccessibilityScanner/FixEngine/Bootstrap.php';
if ( file_exists( $fix_engine_file ) ) {
	require_once $fix_engine_file;
	
	if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap' ) ) {
		$data = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap::get_scanner_data();
		$new_fixers = $data['fixers'] ?? array();
		
		echo "NEW SYSTEM (FixEngine):\n";
		echo "Total: " . count( $new_fixers ) . " fixers\n\n";
		
		foreach ( $new_fixers as $fixer ) {
			echo "- " . $fixer['id'] . " (" . $fixer['name'] . ")\n";
		}
		echo "\n";
	}
}

// Check which system is active for autofix
echo "ACTIVE SYSTEM CHECK:\n";
echo "Checking ScannerPage.php get_fixer_list_for_js() priority...\n";
$scanner_page_file = '/var/www/html/wp-content/plugins/Shahi LegalOps Suite - 3.1.1/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php';
$content = file_get_contents( $scanner_page_file );
if ( strpos( $content, 'Try new FixEngine first' ) !== false ) {
	echo "✓ Code prioritizes NEW FixEngine system\n";
} else {
	echo "✓ Code uses OLD FixerRegistry system\n";
}

echo "\n=== END AUDIT ===\n";
