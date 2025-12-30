<?php
/**
 * Tests for Geo Presets Feature (Phase 1.2)
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.1.1
 */

require_once __DIR__ . '/../includes/Services/Geo_Rule_Matcher.php';
require_once __DIR__ . '/../includes/Services/Compliance_Score_Calculator.php';

use ShahiLegalFlowSuite\Services\Geo_Rule_Matcher;
use ShahiLegalFlowSuite\Services\Compliance_Score_Calculator;

/**
 * Test suite for geo presets functionality
 */
class Test_Geo_Presets {

	private $matcher;
	private $original_rules;

	public function __construct() {
		$this->matcher = new Geo_Rule_Matcher();
		// Backup original rules
		$this->original_rules = get_option( 'slos_geo_rules', array() );
	}

	/**
	 * Run all tests
	 */
	public function run_all_tests() {
		$results = array();

		echo "\n=== Geo Presets Test Suite (Phase 1.2) ===\n\n";

		$results[] = $this->test_preset_config_loaded();
		$results[] = $this->test_preset_structure_valid();
		$results[] = $this->test_apply_eu_preset();
		$results[] = $this->test_apply_uk_preset();
		$results[] = $this->test_apply_us_ca_preset();
		$results[] = $this->test_apply_br_preset();
		$results[] = $this->test_apply_row_preset();
		$results[] = $this->test_preset_reapplication();
		$results[] = $this->test_preset_status_detection();
		$results[] = $this->test_legal_docs_binding();
		$results[] = $this->test_compliance_score_integration();
		$results[] = $this->test_backward_compatibility();

		// Restore original rules
		update_option( 'slos_geo_rules', $this->original_rules );

		// Summary
		$passed = count( array_filter( $results ) );
		$failed = count( $results ) - $passed;

		echo "\n=== Test Summary ===\n";
		echo "Total: " . count( $results ) . "\n";
		echo "Passed: {$passed}\n";
		echo "Failed: {$failed}\n";

		return $failed === 0;
	}

	/**
	 * Test 1: Preset config file loads successfully
	 */
	private function test_preset_config_loaded() {
		echo "Test 1: Preset config file loads... ";

		$presets = $this->matcher->get_all_presets();

		if ( ! is_array( $presets ) || empty( $presets ) ) {
			echo "❌ FAILED (config not loaded)\n";
			return false;
		}

		$expected_keys = array( 'EU', 'UK', 'US-CA', 'BR', 'ROW' );
		$actual_keys   = array_keys( $presets );

		if ( array_diff( $expected_keys, $actual_keys ) ) {
			echo "❌ FAILED (missing preset keys)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 2: All presets have required fields
	 */
	private function test_preset_structure_valid() {
		echo "Test 2: Preset structure validation... ";

		$presets        = $this->matcher->get_all_presets();
		$required_fields = array( 'label', 'countries', 'consent_model', 'framework', 'template', 'legal_docs', 'description', 'priority', 'config' );

		foreach ( $presets as $key => $preset ) {
			foreach ( $required_fields as $field ) {
				if ( ! isset( $preset[ $field ] ) ) {
					echo "❌ FAILED (missing field '{$field}' in {$key})\n";
					return false;
				}
			}

			// Validate config sub-fields
			$config_fields = array( 'show_banner', 'show_reject', 'require_explicit', 'record_proof', 'allow_withdraw' );
			foreach ( $config_fields as $field ) {
				if ( ! isset( $preset['config'][ $field ] ) ) {
					echo "❌ FAILED (missing config field '{$field}' in {$key})\n";
					return false;
				}
			}
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 3: Apply EU preset
	 */
	private function test_apply_eu_preset() {
		echo "Test 3: Apply EU preset... ";

		// Clear rules first
		update_option( 'slos_geo_rules', array() );

		$result = $this->matcher->apply_preset( 'EU' );

		if ( is_wp_error( $result ) ) {
			echo "❌ FAILED (WP_Error: {$result->get_error_message()})\n";
			return false;
		}

		// Validate rule structure
		if ( ! isset( $result['id'], $result['countries'], $result['preset_key'] ) ) {
			echo "❌ FAILED (invalid rule structure)\n";
			return false;
		}

		if ( $result['preset_key'] !== 'EU' ) {
			echo "❌ FAILED (preset_key mismatch)\n";
			return false;
		}

		if ( count( $result['countries'] ) !== 30 ) {
			echo "❌ FAILED (expected 30 EEA countries, got " . count( $result['countries'] ) . ")\n";
			return false;
		}

		if ( $result['framework'] !== 'GDPR' ) {
			echo "❌ FAILED (framework should be GDPR)\n";
			return false;
		}

		if ( $result['consent_mode'] !== 'opt-in' ) {
			echo "❌ FAILED (consent_mode should be opt-in)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 4: Apply UK preset
	 */
	private function test_apply_uk_preset() {
		echo "Test 4: Apply UK preset... ";

		$result = $this->matcher->apply_preset( 'UK' );

		if ( is_wp_error( $result ) ) {
			echo "❌ FAILED (WP_Error)\n";
			return false;
		}

		if ( $result['countries'] !== array( 'GB' ) ) {
			echo "❌ FAILED (should only target GB)\n";
			return false;
		}

		if ( $result['framework'] !== 'UK-GDPR' ) {
			echo "❌ FAILED (framework should be UK-GDPR)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 5: Apply US-CA preset
	 */
	private function test_apply_us_ca_preset() {
		echo "Test 5: Apply US-CA preset... ";

		$result = $this->matcher->apply_preset( 'US-CA' );

		if ( is_wp_error( $result ) ) {
			echo "❌ FAILED (WP_Error)\n";
			return false;
		}

		if ( $result['countries'] !== array( 'US' ) ) {
			echo "❌ FAILED (should target US)\n";
			return false;
		}

		if ( ! isset( $result['states'] ) || $result['states'] !== array( 'CA' ) ) {
			echo "❌ FAILED (should target CA state)\n";
			return false;
		}

		if ( $result['framework'] !== 'CCPA' ) {
			echo "❌ FAILED (framework should be CCPA)\n";
			return false;
		}

		if ( $result['consent_mode'] !== 'opt-out' ) {
			echo "❌ FAILED (consent_mode should be opt-out)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 6: Apply Brazil preset
	 */
	private function test_apply_br_preset() {
		echo "Test 6: Apply Brazil (LGPD) preset... ";

		$result = $this->matcher->apply_preset( 'BR' );

		if ( is_wp_error( $result ) ) {
			echo "❌ FAILED (WP_Error)\n";
			return false;
		}

		if ( $result['countries'] !== array( 'BR' ) ) {
			echo "❌ FAILED (should target BR)\n";
			return false;
		}

		if ( $result['framework'] !== 'LGPD' ) {
			echo "❌ FAILED (framework should be LGPD)\n";
			return false;
		}

		if ( $result['template'] !== 'advanced' ) {
			echo "❌ FAILED (template should be advanced)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 7: Apply Rest of World preset
	 */
	private function test_apply_row_preset() {
		echo "Test 7: Apply Rest of World preset... ";

		$result = $this->matcher->apply_preset( 'ROW' );

		if ( is_wp_error( $result ) ) {
			echo "❌ FAILED (WP_Error)\n";
			return false;
		}

		if ( $result['countries'] !== array( '*' ) ) {
			echo "❌ FAILED (should use wildcard '*')\n";
			return false;
		}

		if ( $result['consent_mode'] !== 'notice-only' ) {
			echo "❌ FAILED (consent_mode should be notice-only)\n";
			return false;
		}

		if ( $result['template'] !== 'simple' ) {
			echo "❌ FAILED (template should be simple)\n";
			return false;
		}

		if ( $result['require_explicit'] !== false ) {
			echo "❌ FAILED (should not require explicit consent)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 8: Preset reapplication (update existing rule)
	 */
	private function test_preset_reapplication() {
		echo "Test 8: Preset reapplication updates existing rule... ";

		// Apply EU preset first time
		$first = $this->matcher->apply_preset( 'EU' );
		$first_id = $first['id'];

		// Apply EU preset second time
		$second = $this->matcher->apply_preset( 'EU' );
		$second_id = $second['id'];

		if ( $first_id !== $second_id ) {
			echo "❌ FAILED (should reuse same rule ID)\n";
			return false;
		}

		// Verify only one EU rule exists
		$rules = get_option( 'slos_geo_rules', array() );
		$eu_rules = array_filter( $rules, function( $rule ) {
			return isset( $rule['preset_key'] ) && $rule['preset_key'] === 'EU';
		});

		if ( count( $eu_rules ) !== 1 ) {
			echo "❌ FAILED (duplicate EU rules created)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 9: Preset status detection
	 */
	private function test_preset_status_detection() {
		echo "Test 9: Preset status detection... ";

		// Apply EU preset
		$this->matcher->apply_preset( 'EU' );

		if ( ! $this->matcher->is_preset_applied( 'EU' ) ) {
			echo "❌ FAILED (EU should be detected as applied)\n";
			return false;
		}

		if ( $this->matcher->is_preset_applied( 'UK' ) ) {
			echo "❌ FAILED (UK should NOT be detected as applied)\n";
			return false;
		}

		// Check stats
		$stats = $this->matcher->get_preset_stats();
		if ( ! isset( $stats['total'], $stats['applied'] ) ) {
			echo "❌ FAILED (invalid stats structure)\n";
			return false;
		}

		if ( $stats['total'] !== 5 ) {
			echo "❌ FAILED (should have 5 total presets)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 10: Legal docs binding
	 */
	private function test_legal_docs_binding() {
		echo "Test 10: Legal documents binding... ";

		$eu_preset = $this->matcher->apply_preset( 'EU' );

		if ( ! isset( $eu_preset['legal_docs'] ) || ! is_array( $eu_preset['legal_docs'] ) ) {
			echo "❌ FAILED (legal_docs field missing)\n";
			return false;
		}

		if ( ! in_array( 'privacy-policy', $eu_preset['legal_docs'], true ) ) {
			echo "❌ FAILED (privacy-policy should be bound)\n";
			return false;
		}

		if ( ! in_array( 'cookie-policy', $eu_preset['legal_docs'], true ) ) {
			echo "❌ FAILED (cookie-policy should be bound)\n";
			return false;
		}

		// US-CA should only have privacy-policy
		$us_preset = $this->matcher->apply_preset( 'US-CA' );
		if ( count( $us_preset['legal_docs'] ) !== 1 || $us_preset['legal_docs'][0] !== 'privacy-policy' ) {
			echo "❌ FAILED (US-CA should only bind privacy-policy)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 11: Compliance score integration
	 */
	private function test_compliance_score_integration() {
		echo "Test 11: Compliance score GEO_RULES dimension... ";

		// Clear rules and apply EU preset
		update_option( 'slos_geo_rules', array() );
		$this->matcher->apply_preset( 'EU' );

		// Calculate score (reflection to access private method)
		$calculator = new Compliance_Score_Calculator();
		$reflection = new ReflectionClass( $calculator );
		$method     = $reflection->getMethod( 'calculate_geo_rules_dimension' );
		$method->setAccessible( true );

		$result = $method->invoke( $calculator );

		if ( ! isset( $result['score'], $result['details'] ) ) {
			echo "❌ FAILED (invalid score structure)\n";
			return false;
		}

		// Check for legal_docs_bound and legal_docs_total in details
		if ( ! isset( $result['details']['legal_docs_bound'] ) ) {
			echo "❌ FAILED (legal_docs_bound missing from details)\n";
			return false;
		}

		if ( ! isset( $result['details']['legal_docs_total'] ) ) {
			echo "❌ FAILED (legal_docs_total missing from details)\n";
			return false;
		}

		if ( $result['details']['legal_docs_total'] !== 2 ) {
			echo "❌ FAILED (EU preset should have 2 legal docs)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}

	/**
	 * Test 12: Backward compatibility with old rules
	 */
	private function test_backward_compatibility() {
		echo "Test 12: Backward compatibility... ";

		// Create old-style rule without preset_key and legal_docs
		$old_rule = array(
			'id'               => 999,
			'name'             => 'Legacy Rule',
			'countries'        => array( 'FR', 'DE' ),
			'active'           => true,
			'show_banner'      => true,
			'consent_mode'     => 'opt-in',
			'show_reject'      => true,
			'require_explicit' => true,
			'record_proof'     => true,
			'allow_withdraw'   => true,
			'framework'        => 'GDPR',
		);

		update_option( 'slos_geo_rules', array( $old_rule ) );

		// Should not crash when getting active rules
		$rules = $this->matcher->get_active_rules();

		if ( count( $rules ) !== 1 ) {
			echo "❌ FAILED (should load legacy rule)\n";
			return false;
		}

		if ( $rules[0]['name'] !== 'Legacy Rule' ) {
			echo "❌ FAILED (legacy rule data corrupted)\n";
			return false;
		}

		// Apply preset alongside legacy rule
		$new_rule = $this->matcher->apply_preset( 'UK' );

		if ( is_wp_error( $new_rule ) ) {
			echo "❌ FAILED (cannot apply preset with legacy rule present)\n";
			return false;
		}

		$all_rules = get_option( 'slos_geo_rules', array() );
		if ( count( $all_rules ) !== 2 ) {
			echo "❌ FAILED (should have both legacy and new rule)\n";
			return false;
		}

		echo "✅ PASSED\n";
		return true;
	}
}

// Run tests if executed directly
if ( defined( 'WP_CLI' ) || ( defined( 'ABSPATH' ) && isset( $_GET['run_geo_preset_tests'] ) && current_user_can( 'manage_options' ) ) ) {
	$test = new Test_Geo_Presets();
	$success = $test->run_all_tests();
	exit( $success ? 0 : 1 );
}
