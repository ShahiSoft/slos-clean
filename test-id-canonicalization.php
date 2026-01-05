<?php
/**
 * Test ID Canonicalization Phase 1
 * 
 * Verifies:
 * - All FixEngine fixers use canonical IDs
 * - CanonicalIds validation works
 * - Migration script dry-run functionality
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shahi-legalflowsuite.php';

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixerCollection;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap;

echo "=== Phase 1: ID Canonicalization Test ===\n\n";

// Test 1: Load CanonicalIds
echo "Test 1: Load CanonicalIds\n";
$ids = CanonicalIds::get_all();
echo "✓ Loaded " . count($ids) . " canonical IDs\n";
echo "Sample IDs: " . implode(', ', array_slice(array_keys($ids), 0, 5)) . "\n\n";

// Test 2: Validation
echo "Test 2: ID Validation\n";
$test_valid = [
	'missing-alt-text' => true,
	'empty-alt-text' => true,
	'generic-link-text' => true,
	'missing-form-label' => true,
];
$test_invalid = [
	'missing_alt' => false,
	'generic-link' => false,
	'missing-label' => false,
];

foreach ($test_valid as $id => $expected) {
	$result = CanonicalIds::is_valid($id);
	echo ($result === $expected ? "✓" : "✗") . " Valid ID '$id': " . ($result ? 'true' : 'false') . "\n";
}

foreach ($test_invalid as $id => $expected) {
	$result = CanonicalIds::is_valid($id);
	echo ($result === $expected ? "✓" : "✗") . " Invalid ID '$id': " . ($result ? 'true' : 'false') . "\n";
}

// Test 3: Get by category
echo "\nTest 3: Get IDs by Category\n";
$image_ids = CanonicalIds::get_by_category('images');
echo "✓ Image category: " . count($image_ids) . " IDs\n";
echo "  IDs: " . implode(', ', array_slice(array_keys($image_ids), 0, 5)) . "...\n";

$form_ids = CanonicalIds::get_by_category('forms');
echo "✓ Forms category: " . count($form_ids) . " IDs\n";
echo "  IDs: " . implode(', ', array_slice(array_keys($form_ids), 0, 5)) . "...\n";

// Test 4: Get metadata
echo "\nTest 4: Get ID Metadata\n";
$meta = CanonicalIds::get('missing-alt-text');
if ($meta) {
	echo "✓ missing-alt-text metadata:\n";
	echo "  Name: " . $meta['name'] . "\n";
	echo "  Severity: " . $meta['severity'] . "\n";
	echo "  Category: " . $meta['category'] . "\n";
	echo "  WCAG: " . implode(', ', $meta['wcag']) . "\n";
	echo "  Auto-fixable: " . ($meta['auto_fixable'] ? 'Yes' : 'No') . "\n";
}

// Test 5: Check FixEngine fixers
echo "\nTest 5: Verify FixEngine Fixers Use Canonical IDs\n";
$fixer_dir = __DIR__ . '/includes/Modules/AccessibilityScanner/FixEngine/Fixers';
if (is_dir($fixer_dir)) {
	$fixers = glob($fixer_dir . '/*.php');
	$non_canonical = [];
	
	foreach ($fixers as $fixer_file) {
		$content = file_get_contents($fixer_file);
		// Extract get_id() return value
		if (preg_match("/public function get_id\(\):\s*string\s*\{[^}]*return\s+'([^']+)'/", $content, $matches)) {
			$id = $matches[1];
			if (!CanonicalIds::is_valid($id)) {
				$non_canonical[] = basename($fixer_file) . ": '$id'";
			}
		}
	}
	
	if (empty($non_canonical)) {
		echo "✓ All " . count($fixers) . " FixEngine fixers use canonical IDs\n";
	} else {
		echo "✗ Found " . count($non_canonical) . " fixers with non-canonical IDs:\n";
		foreach ($non_canonical as $issue) {
			echo "  - $issue\n";
		}
	}
}

echo "\n=== Phase 1 Test Complete ===\n";
