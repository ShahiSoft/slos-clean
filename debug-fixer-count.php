<?php
/**
 * Debug script to check actual fixer count being passed to JavaScript
 */

require_once(__DIR__ . '/../../../wp-load.php');

echo "=== FIXER COUNT DEBUG ===\n\n";

// Test FixerRegistry
if (class_exists('\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Fixes\\FixerRegistry')) {
    \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
    $ids = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_all_fixer_ids();
    echo "FixerRegistry Total IDs: " . count($ids) . "\n";
    echo "First 10 IDs: " . implode(', ', array_slice($ids, 0, 10)) . "\n\n";
}

// Test ScannerPage method
if (class_exists('\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Admin\\ScannerPage')) {
    // Create instance via reflection to avoid constructor dependencies
    $reflection = new ReflectionClass('\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Admin\\ScannerPage');
    $method = $reflection->getMethod('get_fixer_list_for_js');
    $method->setAccessible(true);
    
    $instance = $reflection->newInstanceWithoutConstructor();
    $fixers = $method->invoke($instance);
    
    echo "ScannerPage::get_fixer_list_for_js() returns: " . count($fixers) . " fixers\n";
    echo "First 5 fixers:\n";
    foreach (array_slice($fixers, 0, 5) as $fixer) {
        echo "  - {$fixer['id']}: {$fixer['name']}\n";
    }
}
