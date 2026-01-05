<?php
/**
 * Phase 5: Performance Profiler
 * 
 * Analyzes fixer performance and identifies bottlenecks
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if (!defined('ABSPATH')) {
	exit;
}

class PerformanceProfiler {
	
	private $profiles = [];
	private $thresholds = [
		'fast' => 0.01,      // < 10ms
		'acceptable' => 0.05, // < 50ms
		'slow' => 0.1,        // < 100ms
		// > 100ms = very slow
	];
	
	/**
	 * Profile a fixer's performance
	 */
	public function profile_fixer($fixer_id, $content, $iterations = 10) {
		$engine = new FixEngine();
		$engine->initialize();
		
		$times = [];
		$memory_usage = [];
		
		for ($i = 0; $i < $iterations; $i++) {
			$mem_start = memory_get_usage();
			$time_start = microtime(true);
			
			$result = $engine->fix_with_fixer($content, $fixer_id);
			
			$time_end = microtime(true);
			$mem_end = memory_get_usage();
			
			$times[] = $time_end - $time_start;
			$memory_usage[] = $mem_end - $mem_start;
		}
		
		$profile = [
			'fixer_id' => $fixer_id,
			'iterations' => $iterations,
			'avg_time' => array_sum($times) / count($times),
			'min_time' => min($times),
			'max_time' => max($times),
			'avg_memory' => array_sum($memory_usage) / count($memory_usage),
			'rating' => $this->rate_performance(array_sum($times) / count($times)),
		];
		
		$this->profiles[$fixer_id] = $profile;
		
		return $profile;
	}
	
	/**
	 * Rate performance based on thresholds
	 */
	private function rate_performance($avg_time) {
		if ($avg_time < $this->thresholds['fast']) {
			return 'fast';
		} elseif ($avg_time < $this->thresholds['acceptable']) {
			return 'acceptable';
		} elseif ($avg_time < $this->thresholds['slow']) {
			return 'slow';
		} else {
			return 'very_slow';
		}
	}
	
	/**
	 * Profile all fixers
	 */
	public function profile_all_fixers($test_content = null, $iterations = 5) {
		$engine = new FixEngine();
		$engine->initialize();
		
		$fixers = $engine->get_fixers()->all();
		
		// Default test content
		if (!$test_content) {
			$test_content = $this->generate_test_content();
		}
		
		$results = [];
		
		foreach ($fixers as $id => $fixer) {
			echo "Profiling {$id}... ";
			$profile = $this->profile_fixer($id, $test_content, $iterations);
			echo "{$profile['avg_time']}s ({$profile['rating']})\n";
			$results[] = $profile;
		}
		
		// Sort by avg_time descending (slowest first)
		usort($results, function($a, $b) {
			return $b['avg_time'] <=> $a['avg_time'];
		});
		
		return $results;
	}
	
	/**
	 * Generate comprehensive test content
	 */
	private function generate_test_content() {
		return '
			<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<title>Test Page</title>
			</head>
			<body>
				<h1>Main Heading</h1>
				<nav>
					<a href="#">Link 1</a>
					<a href="#">click here</a>
					<a href="#" target="_blank">External</a>
				</nav>
				
				<main>
					<h2>Section</h2>
					<p>Content with <a href="#">more info</a></p>
					
					<img src="image1.jpg">
					<img src="image2.jpg" alt="">
					<img src="logo.jpg" alt="Logo image">
					
					<form>
						<input type="text" name="name">
						<input type="email" name="email">
						<select name="country">
							<option>Select...</option>
						</select>
						<button>Submit</button>
					</form>
					
					<table>
						<tr>
							<td>Header 1</td>
							<td>Header 2</td>
						</tr>
						<tr>
							<td>Data 1</td>
							<td>Data 2</td>
						</tr>
					</table>
					
					<video src="video.mp4"></video>
					<audio src="audio.mp3"></audio>
					<iframe src="external.html"></iframe>
				</main>
				
				<footer>
					<p>&copy; 2024</p>
				</footer>
			</body>
			</html>
		';
	}
	
	/**
	 * Generate performance report
	 */
	public function generate_report($profiles) {
		$report = "# Phase 5: Performance Profile Report\n\n";
		$report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
		
		// Summary
		$by_rating = [
			'fast' => [],
			'acceptable' => [],
			'slow' => [],
			'very_slow' => [],
		];
		
		foreach ($profiles as $profile) {
			$by_rating[$profile['rating']][] = $profile;
		}
		
		$report .= "## Summary\n\n";
		$report .= "- ⚡ Fast (< 10ms): " . count($by_rating['fast']) . "\n";
		$report .= "- ✓ Acceptable (< 50ms): " . count($by_rating['acceptable']) . "\n";
		$report .= "- ⚠ Slow (< 100ms): " . count($by_rating['slow']) . "\n";
		$report .= "- ❌ Very Slow (> 100ms): " . count($by_rating['very_slow']) . "\n\n";
		
		// Slowest fixers (top priority for optimization)
		if (!empty($by_rating['very_slow'])) {
			$report .= "## Very Slow Fixers (Priority Optimization)\n\n";
			foreach ($by_rating['very_slow'] as $profile) {
				$report .= sprintf(
					"- `%s`: %.4fs (%.2f KB memory)\n",
					$profile['fixer_id'],
					$profile['avg_time'],
					$profile['avg_memory'] / 1024
				);
			}
			$report .= "\n";
		}
		
		if (!empty($by_rating['slow'])) {
			$report .= "## Slow Fixers (Should Optimize)\n\n";
			foreach ($by_rating['slow'] as $profile) {
				$report .= sprintf(
					"- `%s`: %.4fs (%.2f KB memory)\n",
					$profile['fixer_id'],
					$profile['avg_time'],
					$profile['avg_memory'] / 1024
				);
			}
			$report .= "\n";
		}
		
		// Full list
		$report .= "## All Fixers (Sorted by Performance)\n\n";
		$report .= "| Fixer ID | Avg Time | Rating | Memory |\n";
		$report .= "|----------|----------|--------|--------|\n";
		
		foreach ($profiles as $profile) {
			$report .= sprintf(
				"| `%s` | %.4fs | %s | %.2f KB |\n",
				$profile['fixer_id'],
				$profile['avg_time'],
				$profile['rating'],
				$profile['avg_memory'] / 1024
			);
		}
		
		return $report;
	}
	
	/**
	 * Save report to file
	 */
	public function save_report($profiles, $filename = 'PERFORMANCE-PROFILE.md') {
		$report = $this->generate_report($profiles);
		$docs_dir = dirname(dirname(dirname(__DIR__))) . '/docs/autofix';
		
		if (!is_dir($docs_dir)) {
			mkdir($docs_dir, 0755, true);
		}
		
		$filepath = $docs_dir . '/' . $filename;
		file_put_contents($filepath, $report);
		
		return $filepath;
	}
}

// CLI execution
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	require_once __DIR__ . '/../../../vendor/autoload.php';
	
	echo "=== Phase 5: Performance Profiling ===\n\n";
	
	$profiler = new PerformanceProfiler();
	$profiles = $profiler->profile_all_fixers(null, 5);
	
	echo "\n=== Profiling Complete ===\n";
	echo "Total fixers: " . count($profiles) . "\n\n";
	
	$filepath = $profiler->save_report($profiles);
	echo "✓ Report saved: $filepath\n";
}
