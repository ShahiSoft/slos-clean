<?php
/**
 * PHPUnit Bootstrap File for WordPress Testing
 *
 * Loads WordPress test environment and plugin dependencies.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Tests
 * @since      3.2.0
 */

// Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// PHPUnit Polyfills for cross-version compatibility
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
    define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', __DIR__ . '/../vendor/yoast/phpunit-polyfills' );
}

// WordPress tests directory
if ( ! defined( 'WP_TESTS_DIR' ) ) {
    $_tests_dir = getenv( 'WP_TESTS_DIR' );
    if ( ! $_tests_dir ) {
        $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
    }
    define( 'WP_TESTS_DIR', $_tests_dir );
}

// WordPress core directory
if ( ! defined( 'WP_CORE_DIR' ) ) {
    $_core_dir = getenv( 'WP_CORE_DIR' );
    if ( ! $_core_dir ) {
        $_core_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress';
    }
    define( 'WP_CORE_DIR', $_core_dir );
}

// Plugin constants
define( 'SLOS_PLUGIN_DIR', dirname( __DIR__ ) );
define( 'SLOS_PLUGIN_FILE', SLOS_PLUGIN_DIR . '/shahi-legalflowsuite.php' );
define( 'SLOS_TEST_MODE', true );

// Disable WordPress external HTTP requests
if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) ) {
    define( 'WP_HTTP_BLOCK_EXTERNAL', true );
}

// Load WordPress test suite
if ( file_exists( WP_TESTS_DIR . '/includes/functions.php' ) ) {
    require_once WP_TESTS_DIR . '/includes/functions.php';
    
    /**
     * Manually load the plugin for testing
     */
    function _manually_load_plugin() {
        require SLOS_PLUGIN_FILE;
    }
    tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
    
    // Start WordPress test environment
    require WP_TESTS_DIR . '/includes/bootstrap.php';
} else {
    // Fallback: Load Brain Monkey for mocking WordPress functions
    \Brain\Monkey\setUp();
    
    // Register shutdown function to tearDown Brain Monkey
    register_shutdown_function( function() {
        \Brain\Monkey\tearDown();
    } );
    
    // Load plugin file
    if ( file_exists( SLOS_PLUGIN_FILE ) ) {
        require_once SLOS_PLUGIN_FILE;
    }
    
    echo "\n";
    echo "Warning: WordPress test suite not found. Using Brain Monkey for mocking.\n";
    echo "To install WordPress tests, run:\n";
    echo "  bash bin/install-wp-tests.sh wordpress_test root '' localhost latest\n";
    echo "\n";
}

// Load test helpers
if ( file_exists( __DIR__ . '/helpers/TestHelper.php' ) ) {
    require_once __DIR__ . '/helpers/TestHelper.php';
}

// Initialize Mockery
if ( class_exists( '\Mockery' ) ) {
    // Close Mockery after each test
    register_shutdown_function( function() {
        if ( class_exists( '\Mockery' ) ) {
            \Mockery::close();
        }
    } );
}

// Test environment info
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    echo "\n";
    echo "Test Environment:\n";
    echo "  PHP Version: " . PHP_VERSION . "\n";
    echo "  PHPUnit Version: " . \PHPUnit\Runner\Version::id() . "\n";
    echo "  Plugin Dir: " . SLOS_PLUGIN_DIR . "\n";
    
    if ( function_exists( 'get_bloginfo' ) ) {
        echo "  WordPress Version: " . get_bloginfo( 'version' ) . "\n";
        echo "  WordPress Test Suite: Loaded\n";
    } else {
        echo "  WordPress Test Suite: Not loaded (using Brain Monkey)\n";
    }
    echo "\n";
}
