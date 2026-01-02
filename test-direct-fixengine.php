<?php
/**
 * Simple FixEngine Test
 * Direct check without WordPress dependencies
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== Direct FixEngine Test ===\n\n";

// Test file paths
$base = dirname(__FILE__);
$fix_engine_path = $base . '/includes/Modules/AccessibilityScanner/FixEngine';

echo "Base path: $base\n";
echo "FixEngine path: $fix_engine_path\n\n";

// Check if directory exists
if (!is_dir($fix_engine_path)) {
	echo "❌ FixEngine directory not found\n";
	exit(1);
}

echo "✅ FixEngine directory exists\n\n";

// List files
echo "Files in FixEngine:\n";
$files = scandir($fix_engine_path);
foreach ($files as $file) {
	if ($file !== '.' && $file !== '..') {
		echo "  - $file\n";
	}
}

echo "\n";

// Try to load Bootstrap
$bootstrap_file = $fix_engine_path . '/Bootstrap.php';
echo "Loading Bootstrap from: $bootstrap_file\n";

if (!file_exists($bootstrap_file)) {
	echo "❌ Bootstrap.php not found\n";
	exit(1);
}

echo "✅ Bootstrap.php exists\n";

// Check file contents
$bootstrap_content = file_get_contents($bootstrap_file);
if (strpos($bootstrap_content, 'namespace ShahiLegalFlowSuite') !== false) {
	echo "✅ Bootstrap has correct namespace\n";
} else {
	echo "❌ Bootstrap namespace issue\n";
}

if (strpos($bootstrap_content, 'class Bootstrap') !== false) {
	echo "✅ Bootstrap class defined\n";
} else {
	echo "❌ Bootstrap class not found\n";
}

echo "\n";

// Try to require it
echo "Attempting to load Bootstrap...\n";
try {
	require_once $bootstrap_file;
	echo "✅ Bootstrap loaded\n";
} catch (Throwable $e) {
	echo "❌ Error loading Bootstrap: " . $e->getMessage() . "\n";
	echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
	exit(1);
}

echo "\n";

// Check class existence
if (class_exists('\ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap')) {
	echo "✅ Bootstrap class exists\n";
} else {
	echo "❌ Bootstrap class not found\n";
	echo "Declared classes:\n";
	$classes = get_declared_classes();
	foreach ($classes as $class) {
		if (strpos($class, 'Bootstrap') !== false) {
			echo "  - $class\n";
		}
	}
	exit(1);
}

echo "\n=== Test Complete ===\n";
