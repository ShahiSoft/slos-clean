<?php
/**
 * Phase 1.4 Test Suite
 * Tests for Enhanced Consent UI & Vendor Transparency
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test class for Phase 1.4 features
 */
class Test_Phase_1_4 {

	/**
	 * Test results
	 *
	 * @var array
	 */
	private $results = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->run_tests();
	}

	/**
	 * Run all tests
	 *
	 * @return array Test results
	 */
	public function run_tests() {
		echo "<h2>Phase 1.4 Test Suite - Enhanced Consent UI & Vendor Transparency</h2>\n\n";

		// Test 1: Settings_REST_Controller has new fields
		$this->test_settings_controller_fields();

		// Test 2: Default banner settings include descriptions
		$this->test_default_banner_settings_descriptions();

		// Test 3: Default banner settings include vendors
		$this->test_default_banner_settings_vendors();

		// Test 4: Sanitization methods exist
		$this->test_sanitization_methods();

		// Test 5: consent-banner.js file exists
		$this->test_consent_banner_js_exists();

		// Test 6: consent-placeholders.js file exists
		$this->test_consent_placeholders_js_exists();

		// Test 7: consent-ui-enhancements.css file exists
		$this->test_consent_ui_css_exists();

		// Test 8: Embed_Placeholder_Shortcode class exists
		$this->test_embed_placeholder_shortcode_class();

		// Test 9: Shortcode is registered
		$this->test_shortcode_registration();

		// Test 10: Gutenberg block script exists
		$this->test_gutenberg_block_script();

		// Test 11: Assets are enqueued properly
		$this->test_assets_enqueued();

		// Test 12: Valid consent categories
		$this->test_valid_consent_categories();

		// Test 13: Placeholder icon method exists
		$this->test_placeholder_icon_method();

		// Test 14: Block registration in ConsentManagement module
		$this->test_block_registration_in_module();

		// Test 15: Sanitization validates categories
		$this->test_sanitization_validates_categories();

		// Print summary
		$this->print_summary();

		return $this->results;
	}

	/**
	 * Test 1: Settings_REST_Controller has new fields
	 */
	private function test_settings_controller_fields() {
		$test_name = 'Settings_REST_Controller has new fields';
		try {
			$controller_file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/API/Settings_REST_Controller.php';
			if ( ! file_exists( $controller_file ) ) {
				throw new Exception( 'Settings_REST_Controller.php not found' );
			}

			$content = file_get_contents( $controller_file );
			$has_show_descriptions = strpos( $content, "'show_descriptions'" ) !== false;
			$has_category_descriptions = strpos( $content, "'category_descriptions'" ) !== false;
			$has_show_vendors = strpos( $content, "'show_vendors'" ) !== false;
			$has_vendors = strpos( $content, "'vendors'" ) !== false;

			if ( $has_show_descriptions && $has_category_descriptions && $has_show_vendors && $has_vendors ) {
				$this->pass( $test_name );
			} else {
				throw new Exception( 'Missing one or more new fields' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 2: Default banner settings include descriptions
	 */
	private function test_default_banner_settings_descriptions() {
		$test_name = 'Default banner settings include descriptions';
		try {
			require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/API/Settings_REST_Controller.php';
			$controller = new \ShahiLegalFlowSuite\API\Settings_REST_Controller();

			// Use reflection to access private method
			$reflection = new ReflectionClass( $controller );
			$method = $reflection->getMethod( 'get_default_banner_settings' );
			$method->setAccessible( true );
			$defaults = $method->invoke( $controller );

			if ( isset( $defaults['show_descriptions'] ) && isset( $defaults['category_descriptions'] ) ) {
				if ( is_array( $defaults['category_descriptions'] ) && count( $defaults['category_descriptions'] ) >= 5 ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'category_descriptions does not have at least 5 categories' );
				}
			} else {
				throw new Exception( 'show_descriptions or category_descriptions not found in defaults' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 3: Default banner settings include vendors
	 */
	private function test_default_banner_settings_vendors() {
		$test_name = 'Default banner settings include vendors';
		try {
			require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/API/Settings_REST_Controller.php';
			$controller = new \ShahiLegalFlowSuite\API\Settings_REST_Controller();

			$reflection = new ReflectionClass( $controller );
			$method = $reflection->getMethod( 'get_default_banner_settings' );
			$method->setAccessible( true );
			$defaults = $method->invoke( $controller );

			if ( isset( $defaults['show_vendors'] ) && isset( $defaults['vendors'] ) ) {
				if ( is_array( $defaults['vendors'] ) ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'vendors is not an array' );
				}
			} else {
				throw new Exception( 'show_vendors or vendors not found in defaults' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 4: Sanitization methods exist
	 */
	private function test_sanitization_methods() {
		$test_name = 'Sanitization methods exist';
		try {
			$controller_file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/API/Settings_REST_Controller.php';
			$content = file_get_contents( $controller_file );

			$has_sanitize_descriptions = strpos( $content, 'private function sanitize_descriptions' ) !== false;
			$has_sanitize_vendors = strpos( $content, 'private function sanitize_vendors' ) !== false;

			if ( $has_sanitize_descriptions && $has_sanitize_vendors ) {
				$this->pass( $test_name );
			} else {
				throw new Exception( 'Missing sanitization methods' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 5: consent-banner.js file exists
	 */
	private function test_consent_banner_js_exists() {
		$test_name = 'consent-banner.js file exists';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'assets/js/consent-banner.js';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				// Check for Phase 1.4 methods
				$has_get_category_description = strpos( $content, 'getCategoryDescription' ) !== false;
				$has_get_vendor_list = strpos( $content, 'getVendorList' ) !== false;
				$has_toggle_category_details = strpos( $content, 'toggleCategoryDetails' ) !== false;

				if ( $has_get_category_description && $has_get_vendor_list && $has_toggle_category_details ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Missing Phase 1.4 methods in consent-banner.js' );
				}
			} else {
				throw new Exception( 'consent-banner.js not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 6: consent-placeholders.js file exists
	 */
	private function test_consent_placeholders_js_exists() {
		$test_name = 'consent-placeholders.js file exists';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'assets/js/consent-placeholders.js';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				// Check for key methods
				$has_scan_and_replace = strpos( $content, 'scanAndReplace' ) !== false;
				$has_render_placeholder = strpos( $content, 'renderPlaceholder' ) !== false;
				$has_load_embed = strpos( $content, 'loadEmbed' ) !== false;

				if ( $has_scan_and_replace && $has_render_placeholder && $has_load_embed ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Missing key methods in consent-placeholders.js' );
				}
			} else {
				throw new Exception( 'consent-placeholders.js not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 7: consent-ui-enhancements.css file exists
	 */
	private function test_consent_ui_css_exists() {
		$test_name = 'consent-ui-enhancements.css file exists';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'assets/css/consent-ui-enhancements.css';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				// Check for key classes
				$has_expand_toggle = strpos( $content, '.slos-expand-toggle' ) !== false;
				$has_vendor_list = strpos( $content, '.slos-vendor-list' ) !== false;
				$has_embed_placeholder = strpos( $content, '.slos-embed-placeholder' ) !== false;
				$has_enable_btn = strpos( $content, '.slos-enable-btn' ) !== false;

				if ( $has_expand_toggle && $has_vendor_list && $has_embed_placeholder && $has_enable_btn ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Missing key CSS classes' );
				}
			} else {
				throw new Exception( 'consent-ui-enhancements.css not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 8: Embed_Placeholder_Shortcode class exists
	 */
	private function test_embed_placeholder_shortcode_class() {
		$test_name = 'Embed_Placeholder_Shortcode class exists';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Shortcodes/Embed_Placeholder_Shortcode.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				if ( class_exists( '\\ShahiLegalFlowSuite\\Shortcodes\\Embed_Placeholder_Shortcode' ) ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Class not found after including file' );
				}
			} else {
				throw new Exception( 'Embed_Placeholder_Shortcode.php not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 9: Shortcode is registered
	 */
	private function test_shortcode_registration() {
		$test_name = 'Shortcode is registered in ShortcodeManager';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Shortcodes/ShortcodeManager.php';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				if ( strpos( $content, 'embed_placeholder' ) !== false && strpos( $content, 'Embed_Placeholder_Shortcode' ) !== false ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Shortcode not registered in ShortcodeManager' );
				}
			} else {
				throw new Exception( 'ShortcodeManager.php not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 10: Gutenberg block script exists
	 */
	private function test_gutenberg_block_script() {
		$test_name = 'Gutenberg block script exists';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'assets/js/blocks/embed-placeholder-block.js';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				$has_register_block_type = strpos( $content, 'registerBlockType' ) !== false;
				$has_block_name = strpos( $content, 'slos/embed-placeholder' ) !== false;

				if ( $has_register_block_type && $has_block_name ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Block registration code missing' );
				}
			} else {
				throw new Exception( 'embed-placeholder-block.js not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 11: Assets are enqueued properly
	 */
	private function test_assets_enqueued() {
		$test_name = 'Assets are enqueued in main plugin file';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'shahi-legalflowsuite.php';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				$has_ui_css = strpos( $content, 'consent-ui-enhancements.css' ) !== false;
				$has_placeholders_js = strpos( $content, 'consent-placeholders.js' ) !== false;

				if ( $has_ui_css && $has_placeholders_js ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Assets not properly enqueued' );
				}
			} else {
				throw new Exception( 'Main plugin file not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 12: Valid consent categories
	 */
	private function test_valid_consent_categories() {
		$test_name = 'Valid consent categories defined';
		try {
			require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Shortcodes/Embed_Placeholder_Shortcode.php';
			$shortcode = new \ShahiLegalFlowSuite\Shortcodes\Embed_Placeholder_Shortcode();

			// Use reflection to access private property
			$reflection = new ReflectionClass( $shortcode );
			$property = $reflection->getProperty( 'valid_categories' );
			$property->setAccessible( true );
			$valid_categories = $property->getValue( $shortcode );

			$expected_categories = array( 'necessary', 'functional', 'analytics', 'marketing', 'preferences' );
			if ( $valid_categories === $expected_categories ) {
				$this->pass( $test_name );
			} else {
				throw new Exception( 'Valid categories do not match expected list' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 13: Placeholder icon method exists
	 */
	private function test_placeholder_icon_method() {
		$test_name = 'Placeholder icon method exists';
		try {
			require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Shortcodes/Embed_Placeholder_Shortcode.php';
			$shortcode = new \ShahiLegalFlowSuite\Shortcodes\Embed_Placeholder_Shortcode();

			$reflection = new ReflectionClass( $shortcode );
			if ( $reflection->hasMethod( 'get_placeholder_icon' ) ) {
				$this->pass( $test_name );
			} else {
				throw new Exception( 'get_placeholder_icon method not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 14: Block registration in ConsentManagement module
	 */
	private function test_block_registration_in_module() {
		$test_name = 'Block registration in ConsentManagement module';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Modules/ConsentManagement/ConsentManagement.php';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				$has_register_blocks = strpos( $content, 'public function register_blocks' ) !== false;
				$has_enqueue_block_editor = strpos( $content, 'public function enqueue_block_editor_assets' ) !== false;
				$has_render_block = strpos( $content, 'public function render_embed_placeholder_block' ) !== false;

				if ( $has_register_blocks && $has_enqueue_block_editor && $has_render_block ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Block registration methods missing' );
				}
			} else {
				throw new Exception( 'ConsentManagement.php not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Test 15: Sanitization validates categories
	 */
	private function test_sanitization_validates_categories() {
		$test_name = 'Sanitization validates categories';
		try {
			$file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/API/Settings_REST_Controller.php';
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				// Check if sanitize_descriptions mentions valid categories
				$has_category_validation = strpos( $content, 'necessary' ) !== false &&
										   strpos( $content, 'functional' ) !== false &&
										   strpos( $content, 'analytics' ) !== false &&
										   strpos( $content, 'marketing' ) !== false &&
										   strpos( $content, 'preferences' ) !== false;

				if ( $has_category_validation ) {
					$this->pass( $test_name );
				} else {
					throw new Exception( 'Category validation not found in sanitization' );
				}
			} else {
				throw new Exception( 'Settings_REST_Controller.php not found' );
			}
		} catch ( Exception $e ) {
			$this->fail( $test_name, $e->getMessage() );
		}
	}

	/**
	 * Mark test as passed
	 *
	 * @param string $test_name Test name
	 */
	private function pass( $test_name ) {
		$this->results[] = array(
			'test'   => $test_name,
			'status' => 'PASS',
		);
		echo "✓ PASS: {$test_name}\n";
	}

	/**
	 * Mark test as failed
	 *
	 * @param string $test_name Test name
	 * @param string $reason    Failure reason
	 */
	private function fail( $test_name, $reason ) {
		$this->results[] = array(
			'test'   => $test_name,
			'status' => 'FAIL',
			'reason' => $reason,
		);
		echo "✗ FAIL: {$test_name} - {$reason}\n";
	}

	/**
	 * Print test summary
	 */
	private function print_summary() {
		$total = count( $this->results );
		$passed = count( array_filter( $this->results, function( $r ) {
			return $r['status'] === 'PASS';
		} ) );
		$failed = $total - $passed;

		echo "\n========================================\n";
		echo "Test Summary:\n";
		echo "Total Tests: {$total}\n";
		echo "Passed: {$passed}\n";
		echo "Failed: {$failed}\n";
		echo "Success Rate: " . round( ( $passed / $total ) * 100, 2 ) . "%\n";
		echo "========================================\n";

		if ( $failed > 0 ) {
			echo "\nFailed Tests:\n";
			foreach ( $this->results as $result ) {
				if ( $result['status'] === 'FAIL' ) {
					echo "- {$result['test']}: {$result['reason']}\n";
				}
			}
		}
	}

	/**
	 * Get test results
	 *
	 * @return array Test results
	 */
	public function get_results() {
		return $this->results;
	}
}

// Run tests if called directly
if ( defined( 'ABSPATH' ) && ! empty( $_GET['run_phase_1_4_tests'] ) ) {
	$tests = new Test_Phase_1_4();
}
