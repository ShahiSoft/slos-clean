<?php
/**
 * Phase 2.1: Coverage Matrix Analysis
 *
 * Analyzes legacy Fixes/ vs FixEngine/ coverage
 * Identifies gaps and determines porting priorities
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI analysis output

namespace ShahiLegalFlowSuite\Docs\Analysis;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoverageMatrixAnalyzer {

	private $legacy_dir;
	private $fixengine_dir;
	private $results = array();

	public function __construct() {
		$base                = dirname( dirname( __DIR__ ) );
		$this->legacy_dir    = $base . '/includes/Modules/AccessibilityScanner/Fixes/Fixers';
		$this->fixengine_dir = $base . '/includes/Modules/AccessibilityScanner/FixEngine/Fixers';
	}

	/**
	 * Run coverage analysis
	 */
	public function analyze() {
		$legacy_fixers    = $this->scan_directory( $this->legacy_dir );
		$fixengine_fixers = $this->scan_directory( $this->fixengine_dir );

		$this->results = array(
			'legacy_count'    => count( $legacy_fixers ),
			'fixengine_count' => count( $fixengine_fixers ),
			'legacy_only'     => array(),
			'fixengine_only'  => array(),
			'both'            => array(),
			'categories'      => array(),
		);

		// Extract IDs from fixers..
		$legacy_ids    = $this->extract_fixer_ids( $legacy_fixers );
		$fixengine_ids = $this->extract_fixer_ids( $fixengine_fixers );

		// Compare coverage..
		$this->results['legacy_only']    = array_diff( $legacy_ids, $fixengine_ids );
		$this->results['fixengine_only'] = array_diff( $fixengine_ids, $legacy_ids );
		$this->results['both']           = array_intersect( $legacy_ids, $fixengine_ids );

		// Categorize missing fixers..
		$this->categorize_gaps();

		return $this->results;
	}

	/**
	 * Scan directory for PHP fixer files
	 */
	private function scan_directory( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return array();
		}

		$files = glob( $dir . '/*.php' );
		return array_map( 'basename', $files );
	}

	/**
	 * Extract fixer IDs from file contents
	 */
	private function extract_fixer_ids( $files ) {
		$ids = array();

		foreach ( $files as $file ) {
			$full_path = ( strpos( $file, '/' ) === false )
				? $this->legacy_dir . '/' . $file
				: $file;

			if ( ! file_exists( $full_path ) ) {
				$full_path = $this->fixengine_dir . '/' . basename( $file );
			}

			if ( file_exists( $full_path ) ) {
				$content = file_get_contents( $full_path );

				// Match get_id() or get_fixer_id() return value..
				if ( preg_match( "/function get_(?:fixer_)?id\(\)[^{]*\{[^}]*return\s+'([^']+)'/", $content, $matches ) ) {
					$ids[ basename( $file, '.php' ) ] = $matches[1];
				} elseif ( preg_match( '/public \$id\s*=\s*[\'"]([^\'"]+)/', $content, $matches ) ) {
					$ids[ basename( $file, '.php' ) ] = $matches[1];
				}
			}
		}

		return $ids;
	}

	/**
	 * Categorize missing fixers by priority
	 */
	private function categorize_gaps() {
		// High priority: Critical WCAG criteria..
		$high_priority = array(
			'missing-alt-text',
			'empty-alt-text',
			'missing-form-label',
			'generic-link-text',
			'missing-page-title',
			'missing-lang-attribute',
			'skipped-heading-level',
			'missing-table-headers',
		);

		// Medium priority: Serious issues..
		$medium_priority = array(
			'empty-heading',
			'empty-link',
			'link-opens-new-window',
			'missing-table-caption',
			'missing-iframe-title',
			'invalid-tabindex',
			'missing-focus-indicator',
		);

		$this->results['categories'] = array(
			'high_priority'   => array_intersect( $high_priority, $this->results['legacy_only'] ),
			'medium_priority' => array_intersect( $medium_priority, $this->results['legacy_only'] ),
			'low_priority'    => array_diff(
				$this->results['legacy_only'],
				array_merge( $high_priority, $medium_priority )
			),
		);
	}

	/**
	 * Generate markdown report
	 */
	public function generate_report() {
		$report  = "# Phase 2.1: Coverage Matrix Analysis\n\n";
		$report .= '**Generated:** ' . gmdate( 'Y-m-d H:i:s' ) . "\n\n";

		$report .= "## Summary\n\n";
		$report .= '- **Legacy Fixers:** ' . $this->results['legacy_count'] . "\n";
		$report .= '- **FixEngine Fixers:** ' . $this->results['fixengine_count'] . "\n";
		$report .= '- **Coverage Gap:** ' . count( $this->results['legacy_only'] ) . " fixers\n";
		$report .= '- **Parity:** ' . count( $this->results['both'] ) . " common fixers\n\n";

		$report .= "## Legacy-Only Fixers (Need Porting)\n\n";

		$report .= '### High Priority (' . count( $this->results['categories']['high_priority'] ) . ")\n";
		$report .= "Critical WCAG A/AA criteria\n\n";
		foreach ( $this->results['categories']['high_priority'] as $class => $id ) {
			$report .= "- [ ] `$id` ($class)\n";
		}

		$report .= "\n### Medium Priority (" . count( $this->results['categories']['medium_priority'] ) . ")\n";
		$report .= "Serious accessibility issues\n\n";
		foreach ( $this->results['categories']['medium_priority'] as $class => $id ) {
			$report .= "- [ ] `$id` ($class)\n";
		}

		$report .= "\n### Low Priority (" . count( $this->results['categories']['low_priority'] ) . ")\n";
		$report .= "Minor issues, warnings, manual checks\n\n";
		foreach ( $this->results['categories']['low_priority'] as $class => $id ) {
			$report .= "- [ ] `$id` ($class)\n";
		}

		$report .= "\n## FixEngine-Only Fixers\n\n";
		foreach ( $this->results['fixengine_only'] as $class => $id ) {
			$report .= "- `$id` ($class)\n";
		}

		$report .= "\n## Common Fixers (Parity Achieved)\n\n";
		foreach ( $this->results['both'] as $class => $id ) {
			$report .= "- ✓ `$id` ($class)\n";
		}

		return $report;
	}

	/**
	 * Save report to file
	 */
	public function save_report( $filename = 'COVERAGE-MATRIX.md' ) {
		$report   = $this->generate_report();
		$docs_dir = dirname( dirname( __DIR__ ) ) . '/docs/autofix';

		if ( ! is_dir( $docs_dir ) ) {
			wp_mkdir_p( $docs_dir );
		}

		$filepath = $docs_dir . '/' . $filename;
		file_put_contents( $filepath, $report );

		return $filepath;
	}
}

// Run analysis if executed directly..
if ( php_sapi_name() === 'cli' && basename( __FILE__ ) === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	require_once __DIR__ . '/../../vendor/autoload.php';

	echo "=== Phase 2.1: Coverage Matrix Analysis ===\n\n";

	$analyzer = new CoverageMatrixAnalyzer();
	$results  = $analyzer->analyze();

	echo 'Legacy Fixers: ' . $results['legacy_count'] . "\n";
	echo 'FixEngine Fixers: ' . $results['fixengine_count'] . "\n";
	echo 'Gap: ' . count( $results['legacy_only'] ) . " fixers need porting\n\n";

	echo "Priority Breakdown:\n";
	echo '- High: ' . count( $results['categories']['high_priority'] ) . "\n";
	echo '- Medium: ' . count( $results['categories']['medium_priority'] ) . "\n";
	echo '- Low: ' . count( $results['categories']['low_priority'] ) . "\n\n";

	$filepath = $analyzer->save_report();
	echo "✓ Report saved: $filepath\n";
}
