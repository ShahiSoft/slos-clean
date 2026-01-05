<?php
/**
 * Phase 4: Legacy System Deprecation Wrapper
 * 
 * Gradual routing from legacy Fixes/ to FixEngine/
 * with fallback and monitoring
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner;

if (!defined('ABSPATH')) {
	exit;
}

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixEngine;
use ShahiLegalFlowSuite\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\FixEngine\FeatureFlags;
use ShahiLegalFlowSuite\FixEngine\Logger;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry;

/**
 * Unified Fixer Router
 * 
 * Routes fix requests to FixEngine or legacy system based on:
 * - Feature flags
 * - Fixer availability
 * - Performance metrics
 */
class UnifiedFixerRouter {
	
	/** @var FixEngine */
	private $fix_engine;
	
	/** @var array Performance metrics */
	private $metrics = [];
	
	public function __construct() {
		$this->fix_engine = new FixEngine();
		$this->fix_engine->initialize();
	}
	
	/**
	 * Route fix request to appropriate system
	 * 
	 * @param string $content HTML content
	 * @param string|array $fixer_ids Single ID or array of IDs
	 * @param array $options
	 * @return array Fix result
	 */
	public function fix($content, $fixer_ids, $options = []) {
		// Feature flag check
		if (!FeatureFlags::is_fixengine_enabled()) {
			Logger::debug('FixEngine disabled, using legacy system');
			return $this->route_to_legacy($content, $fixer_ids, $options);
		}
		
		// Normalize to array
		$ids = is_array($fixer_ids) ? $fixer_ids : [$fixer_ids];
		
		// Check fixer availability
		$route_map = $this->determine_routing($ids);
		
		// If all can go to FixEngine
		if (empty($route_map['legacy'])) {
			Logger::info('Routing to FixEngine', ['fixers' => $route_map['fixengine']]);
			return $this->route_to_fixengine($content, $route_map['fixengine'], $options);
		}
		
		// If all must go to legacy
		if (empty($route_map['fixengine'])) {
			Logger::info('Routing to legacy system', ['fixers' => $route_map['legacy']]);
			return $this->route_to_legacy($content, $route_map['legacy'], $options);
		}
		
		// Hybrid: some fixers in each system
		Logger::warning('Hybrid routing required', $route_map);
		
		if (FeatureFlags::enable_legacy_fallback()) {
			// Run FixEngine first, then legacy for remaining
			$fixengine_result = $this->route_to_fixengine($content, $route_map['fixengine'], $options);
			$content = $fixengine_result['content'] ?? $content;
			$legacy_result = $this->route_to_legacy($content, $route_map['legacy'], $options);
			
			// Merge results
			return $this->merge_results($fixengine_result, $legacy_result);
		} else {
			// Legacy fallback disabled, only run FixEngine
			return $this->route_to_fixengine($content, $route_map['fixengine'], $options);
		}
	}
	
	/**
	 * Determine which system handles which fixers
	 */
	private function determine_routing($fixer_ids) {
		$route_map = [
			'fixengine' => [],
			'legacy' => [],
		];
		
		foreach ($fixer_ids as $id) {
			$canonical_id = CanonicalIds::canonicalize($id) ?? $id;
			// Check if FixEngine has this fixer
			if ($this->fix_engine->get_fixer($canonical_id)) {
				$route_map['fixengine'][] = $canonical_id;
			} else {
				// Check if legacy has it
				FixerRegistry::init();
				if (FixerRegistry::has_fixer($canonical_id)) {
					$route_map['legacy'][] = $canonical_id;
				} else {
					Logger::error('Fixer not found in either system', ['fixer_id' => $canonical_id]);
				}
			}
		}
		
		return $route_map;
	}
	
	/**
	 * Route to FixEngine
	 */
	private function route_to_fixengine($content, $fixer_ids, $options) {
		$start = microtime(true);
		
		try {
			$session = $this->fix_engine->fix_content($content, $fixer_ids, $options);
			
			$elapsed = microtime(true) - $start;
			$this->track_metric('fixengine', $elapsed, $session->get_fixes_applied());
			
			return [
				'content' => $session->get_content(),
				'fixes_applied' => $session->get_fixes_applied(),
				'success' => $session->get_total_changes() > 0,
				'system' => 'fixengine',
				'elapsed' => $elapsed,
			];
		} catch (\Exception $e) {
			Logger::error('FixEngine routing failed', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			
			// Fallback to legacy if enabled
			if (FeatureFlags::enable_legacy_fallback()) {
				Logger::info('Falling back to legacy system');
				return $this->route_to_legacy($content, $fixer_ids, $options);
			}
			
			return [
				'content' => $content,
				'fixes_applied' => 0,
				'success' => false,
				'error' => $e->getMessage(),
				'system' => 'fixengine_failed',
			];
		}
	}
	
	/**
	 * Route to legacy system
	 */
	private function route_to_legacy($content, $fixer_ids, $options) {
		$start = microtime(true);
		
		FixerRegistry::init();
		
		$fixes_applied = 0;
		$ids = is_array($fixer_ids) ? $fixer_ids : [$fixer_ids];
		
		foreach ($ids as $id) {
			$fixer = FixerRegistry::get_fixer($id);
			if ($fixer && method_exists($fixer, 'fix')) {
				$result = $fixer->fix($content);
				if (isset($result['content'])) {
					$content = $result['content'];
					$fixes_applied++;
				}
			}
		}
		
		$elapsed = microtime(true) - $start;
		$this->track_metric('legacy', $elapsed, $fixes_applied);
		
		return [
			'content' => $content,
			'fixes_applied' => $fixes_applied,
			'success' => $fixes_applied > 0,
			'system' => 'legacy',
			'elapsed' => $elapsed,
		];
	}
	
	/**
	 * Merge results from hybrid routing
	 */
	private function merge_results($fixengine_result, $legacy_result) {
		return [
			'content' => $legacy_result['content'] ?? $fixengine_result['content'],
			'fixes_applied' => ($fixengine_result['fixes_applied'] ?? 0) + ($legacy_result['fixes_applied'] ?? 0),
			'success' => ($fixengine_result['success'] ?? false) || ($legacy_result['success'] ?? false),
			'system' => 'hybrid',
			'fixengine' => $fixengine_result,
			'legacy' => $legacy_result,
			'elapsed' => ($fixengine_result['elapsed'] ?? 0) + ($legacy_result['elapsed'] ?? 0),
		];
	}
	
	/**
	 * Track performance metrics
	 */
	private function track_metric($system, $elapsed, $fixes_applied) {
		if (!isset($this->metrics[$system])) {
			$this->metrics[$system] = [
				'calls' => 0,
				'total_time' => 0,
				'total_fixes' => 0,
			];
		}
		
		$this->metrics[$system]['calls']++;
		$this->metrics[$system]['total_time'] += $elapsed;
		$this->metrics[$system]['total_fixes'] += $fixes_applied;
	}
	
	/**
	 * Get performance metrics
	 */
	public function get_metrics() {
		$report = [];
		
		foreach ($this->metrics as $system => $data) {
			$report[$system] = [
				'calls' => $data['calls'],
				'avg_time' => $data['calls'] > 0 ? $data['total_time'] / $data['calls'] : 0,
				'total_fixes' => $data['total_fixes'],
				'avg_fixes_per_call' => $data['calls'] > 0 ? $data['total_fixes'] / $data['calls'] : 0,
			];
		}
		
		return $report;
	}
	
	/**
	 * Log metrics to database for monitoring
	 */
	public function save_metrics() {
		$metrics = $this->get_metrics();
		update_option('slos_fixer_router_metrics', $metrics, false);
		
		Logger::info('Fixer router metrics saved', $metrics);
	}
}
