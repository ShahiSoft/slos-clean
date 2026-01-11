<?php
/**
 * Phase 2.5: Determinism Checker
 *
 * Verifies fixers produce consistent results across multiple runs
 */

namespace ShahiLegalFlowSuite\Tests\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixEngine;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap;

class DeterminismChecker {

	private $engine;
	private $test_samples = array();
	private $results      = array();

	public function __construct() {
		Bootstrap::init();
		$this->engine = Bootstrap::get_engine();
		$this->engine->initialize();
		$this->load_test_samples();
	}

	/**
	 * Load test HTML samples
	 */
	private function load_test_samples() {
		$this->test_samples = array(
			'missing_alt'      => '<img src="test.jpg">',
			'empty_alt'        => '<img src="test.jpg" alt="">',
			'empty_link'       => '<a href="#">&nbsp;</a>',
			'generic_link'     => '<a href="#">click here</a>',
			'empty_heading'    => '<h2></h2>',
			'missing_label'    => '<input type="text" name="email">',
			'table_no_headers' => '<table><tr><td>Data</td></tr></table>',
			'complex_sample'   => '
				<div>
					<img src="logo.png">
					<h2></h2>
					<a href="#">read more</a>
					<form>
						<input type="email" name="email">
						<button>Submit</button>
					</form>
					<table>
						<tr><td>Col1</td><td>Col2</td></tr>
					</table>
				</div>
			',
		);
	}

	/**
	 * Run determinism test for a fixer
	 *
	 * @param string $fixer_id
	 * @param string $sample_name
	 * @param int    $iterations
	 * @return array Results with determinism flag
	 */
	public function test_fixer( $fixer_id, $sample_name, $iterations = 5 ) {
		$sample = $this->test_samples[ $sample_name ] ?? '';
		if ( empty( $sample ) ) {
			return array( 'error' => "Sample not found: $sample_name" );
		}

		$results = array();
		$hashes  = array();

		for ( $i = 0; $i < $iterations; $i++ ) {
			$result        = $this->engine->fix_with_fixer( $sample, $fixer_id );
			$fixed_content = $result->get_content();
			$hash          = md5( $fixed_content );

			$results[] = array(
				'iteration'    => $i + 1,
				'success'      => $result->is_success(),
				'content_hash' => $hash,
			);

			$hashes[] = $hash;
		}

		// Check if all hashes are identical
		$is_deterministic = count( array_unique( $hashes ) ) === 1;

		return array(
			'fixer_id'       => $fixer_id,
			'sample'         => $sample_name,
			'iterations'     => $iterations,
			'deterministic'  => $is_deterministic,
			'unique_outputs' => count( array_unique( $hashes ) ),
			'results'        => $results,
		);
	}

	/**
	 * Test all fixers for determinism
	 */
	public function test_all_fixers() {
		$fixers = $this->engine->get_fixers()->all();
		$report = array(
			'total_fixers'      => count( $fixers ),
			'tested'            => 0,
			'deterministic'     => 0,
			'non_deterministic' => 0,
			'errors'            => 0,
			'details'           => array(),
		);

		foreach ( $fixers as $id => $fixer ) {
			// Test with relevant sample
			$sample = $this->map_fixer_to_sample( $id );

			if ( ! $sample ) {
				continue;
			}

			$result = $this->test_fixer( $id, $sample, 5 );
			++$report['tested'];

			if ( isset( $result['error'] ) ) {
				++$report['errors'];
			} elseif ( $result['deterministic'] ) {
				++$report['deterministic'];
			} else {
				++$report['non_deterministic'];
			}

			$report['details'][ $id ] = $result;
		}

		$this->results = $report;
		return $report;
	}

	/**
	 * Map fixer ID to appropriate test sample
	 */
	private function map_fixer_to_sample( $fixer_id ) {
		$mapping = array(
			'missing-alt-text'      => 'missing_alt',
			'empty-alt-text'        => 'empty_alt',
			'empty-link'            => 'empty_link',
			'generic-link-text'     => 'generic_link',
			'empty-heading'         => 'empty_heading',
			'missing-form-label'    => 'missing_label',
			'missing-table-headers' => 'table_no_headers',
		);

		return $mapping[ $fixer_id ] ?? 'complex_sample';
	}

	/**
	 * Generate markdown report
	 */
	public function generate_report() {
		if ( empty( $this->results ) ) {
			return "No results available. Run test_all_fixers() first.\n";
		}

		$r = $this->results;

		$report  = "# Phase 2.5: Determinism Check Report\n\n";
		$report .= '**Generated:** ' . date( 'Y-m-d H:i:s' ) . "\n\n";

		$report .= "## Summary\n\n";
		$report .= "- Total Fixers: {$r['total_fixers']}\n";
		$report .= "- Tested: {$r['tested']}\n";
		$report .= "- ✓ Deterministic: {$r['deterministic']}\n";
		$report .= "- ✗ Non-Deterministic: {$r['non_deterministic']}\n";
		$report .= "- ⚠ Errors: {$r['errors']}\n\n";

		$determinism_rate = $r['tested'] > 0
			? round( ( $r['deterministic'] / $r['tested'] ) * 100, 1 )
			: 0;
		$report          .= "**Determinism Rate:** {$determinism_rate}%\n\n";

		// Non-deterministic fixers (priority issues)
		if ( $r['non_deterministic'] > 0 ) {
			$report .= "## Non-Deterministic Fixers (Require Investigation)\n\n";
			foreach ( $r['details'] as $id => $detail ) {
				if ( isset( $detail['deterministic'] ) && ! $detail['deterministic'] ) {
					$report .= "### `{$id}`\n";
					$report .= "- Unique outputs: {$detail['unique_outputs']} (expected 1)\n";
					$report .= "- Test sample: {$detail['sample']}\n";
					$report .= "- Iterations: {$detail['iterations']}\n\n";
				}
			}
		}

		// Deterministic fixers
		$report .= "## Deterministic Fixers\n\n";
		foreach ( $r['details'] as $id => $detail ) {
			if ( isset( $detail['deterministic'] ) && $detail['deterministic'] ) {
				$report .= "- ✓ `{$id}`\n";
			}
		}

		return $report;
	}

	/**
	 * Save report to file
	 */
	public function save_report( $filename = 'DETERMINISM-CHECK.md' ) {
		$report   = $this->generate_report();
		$docs_dir = dirname( dirname( dirname( __DIR__ ) ) ) . '/docs/autofix';

		if ( ! is_dir( $docs_dir ) ) {
			mkdir( $docs_dir, 0755, true );
		}

		$filepath = $docs_dir . '/' . $filename;
		file_put_contents( $filepath, $report );

		return $filepath;
	}
}

// Run if executed directly
if ( php_sapi_name() === 'cli' && basename( __FILE__ ) === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	require_once __DIR__ . '/../../../vendor/autoload.php';

	echo "=== Phase 2.5: Determinism Check ===\n\n";

	$checker = new DeterminismChecker();
	$results = $checker->test_all_fixers();

	echo "Tested: {$results['tested']} fixers\n";
	echo "Deterministic: {$results['deterministic']}\n";
	echo "Non-Deterministic: {$results['non_deterministic']}\n";
	echo "Errors: {$results['errors']}\n\n";

	$filepath = $checker->save_report();
	echo "✓ Report saved: $filepath\n";
}
