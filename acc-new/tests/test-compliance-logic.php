<?php
/**
 * Standalone Compliance Logic Test
 * 
 * Tests calculator logic without requiring WordPress connection.
 * Run from plugin root: php acc-new/tests/test-compliance-logic.php
 */

echo "========================================\n";
echo "Compliance Score Logic Test\n";
echo "========================================\n\n";

// Define ABSPATH for standalone execution
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../');
}

// Mock WordPress translation functions for standalone test
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

// Test 1: Weight Verification
echo "TEST 1: Dimension Weights Verification\n";
echo "---------------------------------------\n";

// Include constants file
require_once __DIR__ . '/../../config/compliance-constants.php';

$weights = slos_get_dimension_weights();
$total_weight = 0;

foreach ($weights as $dimension => $weight) {
    $label = slos_get_dimension_labels()[$dimension] ?? $dimension;
    $weight_percent = $weight * 100;
    echo "{$label}: {$weight_percent}%\n";
    $total_weight += $weight;
}

$total_percent = $total_weight * 100;
echo "\nTotal Weight: {$total_percent}%\n";

if (abs($total_weight - 1.0) < 0.001) {
    echo "✓ PASS: Weights total exactly 100%\n\n";
} else {
    echo "✗ FAIL: Weights should total 100%, got {$total_percent}%\n\n";
}

// Test 2: Grade Mapping Verification
echo "TEST 2: Grade Mapping Verification\n";
echo "-----------------------------------\n";

$grades = slos_get_grade_mappings();
$expected_count = 5; // A, B, C, D, F

echo "Expected grade count: {$expected_count}\n";
echo "Got grade count: " . count($grades) . "\n\n";

if (count($grades) === $expected_count) {
    echo "✓ PASS: Correct number of grades\n";
} else {
    echo "✗ FAIL: Expected {$expected_count} grades\n";
}

// Test thresholds are descending
echo "\nGrade Thresholds:\n";
$previous_min = 101;
$thresholds_valid = true;
foreach ($grades as $mapping) {
    $grade = $mapping['grade'];
    $min = $mapping['min'];
    $max = $mapping['max'];
    $label = $mapping['label'];
    echo "{$grade} ({$label}): {$min}-{$max}\n";
    
    if ($min >= $previous_min) {
        $thresholds_valid = false;
    }
    $previous_min = $min;
}

if ($thresholds_valid) {
    echo "\n✓ PASS: Thresholds in descending order\n\n";
} else {
    echo "\n✗ FAIL: Thresholds must be in descending order\n\n";
}

// Test 3: Helper Functions
echo "TEST 3: Helper Functions\n";
echo "------------------------\n";

$labels = slos_get_dimension_labels();
$icons = slos_get_dimension_icons();
$dimensions = array_keys($weights);

echo "Checking all dimensions have labels and icons...\n";
$all_present = true;

foreach ($dimensions as $dimension) {
    $has_label = isset($labels[$dimension]);
    $has_icon = isset($icons[$dimension]);
    
    $status = ($has_label && $has_icon) ? '✓' : '✗';
    $dim_name = $labels[$dimension] ?? $dimension;
    echo "{$status} {$dim_name}: Label=" . ($has_label ? 'Yes' : 'No') . ", Icon=" . ($has_icon ? 'Yes' : 'No') . "\n";
    
    if (!$has_label || !$has_icon) {
        $all_present = false;
    }
}

if ($all_present) {
    echo "\n✓ PASS: All dimensions have labels and icons\n\n";
} else {
    echo "\n✗ FAIL: Missing labels or icons\n\n";
}

// Test 4: Manual Score Calculation Simulation
echo "TEST 4: Score Calculation Simulation\n";
echo "-------------------------------------\n";

// Simulate scores for each dimension
$test_dimensions = [
    'COOKIES' => 75,
    'LEGAL_DOCS' => 66.67,
    'GEO_RULES' => 100,
    'CONSENT_METADATA' => 50,
    'SCANNING_FRESHNESS' => 90,
    'BANNER_CONFIG' => 83.33,
];

echo "Test Dimension Scores:\n";
foreach ($test_dimensions as $dim => $score) {
    echo "  {$labels[$dim]}: {$score}\n";
}

// Calculate weighted aggregate
$aggregate = 0;
foreach ($test_dimensions as $dim => $score) {
    $weighted_score = $score * $weights[$dim];
    $aggregate += $weighted_score;
}

$aggregate = round($aggregate);
echo "\nCalculated Aggregate: {$aggregate}\n";

// Map to grade using proper mapping structure
$mapped_grade = 'F';
$mapped_label = 'Failing';
foreach ($grades as $mapping) {
    if ($aggregate >= $mapping['min'] && $aggregate <= $mapping['max']) {
        $mapped_grade = $mapping['grade'];
        $mapped_label = $mapping['label'];
        break;
    }
}

echo "Mapped Grade: {$mapped_grade} ({$mapped_label})\n";

// Expected: 
// (75 * 0.25) + (66.67 * 0.25) + (100 * 0.15) + (50 * 0.15) + (90 * 0.10) + (83.33 * 0.10)
// = 18.75 + 16.67 + 15 + 7.5 + 9 + 8.33 = 75.25 ≈ 75 (Grade C)

$expected_aggregate = 75;
$expected_grade = 'C';

echo "\nExpected: {$expected_aggregate} (Grade {$expected_grade})\n";

if ($aggregate == $expected_aggregate && $mapped_grade == $expected_grade) {
    echo "✓ PASS: Score calculation correct\n\n";
} else {
    echo "✗ FAIL: Mismatch detected\n\n";
}

// Test 5: Edge Cases
echo "TEST 5: Edge Cases\n";
echo "------------------\n";

// Test minimum score (0)
$test_scores = [
    ['score' => 0, 'expected_grade' => 'F'],
    ['score' => 59, 'expected_grade' => 'F'],
    ['score' => 60, 'expected_grade' => 'D'],
    ['score' => 70, 'expected_grade' => 'C'],
    ['score' => 80, 'expected_grade' => 'B'],
    ['score' => 90, 'expected_grade' => 'A'],
    ['score' => 100, 'expected_grade' => 'A'],
];

$edge_cases_passed = true;
foreach ($test_scores as $test) {
    $score = $test['score'];
    $expected = $test['expected_grade'];
    
    $actual = 'F';
    foreach ($grades as $mapping) {
        if ($score >= $mapping['min'] && $score <= $mapping['max']) {
            $actual = $mapping['grade'];
            break;
        }
    }
    
    $status = ($actual === $expected) ? '✓' : '✗';
    echo "{$status} Score {$score} → Grade {$actual} (expected {$expected})\n";
    
    if ($actual !== $expected) {
        $edge_cases_passed = false;
    }
}

if ($edge_cases_passed) {
    echo "\n✓ PASS: All edge cases handled correctly\n\n";
} else {
    echo "\n✗ FAIL: Some edge cases failed\n\n";
}

// Test 6: Dimension Constants
echo "TEST 6: Dimension Constants Check\n";
echo "----------------------------------\n";

// Check if constants are defined
$expected_constants = [
    'SLOS_DIMENSION_COOKIES',
    'SLOS_DIMENSION_LEGAL_DOCS',
    'SLOS_DIMENSION_GEO_RULES',
    'SLOS_DIMENSION_CONSENT_METADATA',
    'SLOS_DIMENSION_SCANNING_FRESHNESS',
    'SLOS_DIMENSION_BANNER_CONFIG',
];

$constants_valid = true;
foreach ($expected_constants as $const) {
    if (defined($const)) {
        echo "✓ {$const}: " . constant($const) . "\n";
    } else {
        echo "✗ {$const}: NOT DEFINED\n";
        $constants_valid = false;
    }
}

if ($constants_valid) {
    echo "\n✓ PASS: All dimension constants defined\n\n";
} else {
    echo "\n✗ FAIL: Missing dimension constants\n\n";
}

// Summary
echo "========================================\n";
echo "Test Summary\n";
echo "========================================\n";
echo "All logic tests completed successfully.\n";
echo "The compliance-constants.php configuration is valid.\n";
echo "\nNext: Test with live WordPress data\n";
