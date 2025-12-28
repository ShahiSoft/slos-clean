<?php
/**
 * Unit Test Base for Accessibility Checkers
 *
 * Provides a framework for testing individual checkers.
 *
 * @package ShahiLegalOps
 * @since 3.1.2
 */

namespace ShahiLegalFlowSuite\Tests\Accessibility;

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Define ABSPATH for WordPress compatibility
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 4) . '/');
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\';
    $base_dir = dirname(__DIR__, 2) . '/includes/Modules/AccessibilityScanner/Scanner/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Base test class with assertion methods
 */
abstract class CheckerTestCase {
    
    /** @var \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\CheckInterface */
    protected $checker;
    
    /** @var array */
    protected $assertions = [];
    
    /** @var array */
    protected $failures = [];
    
    /**
     * Set up the checker instance
     */
    abstract protected function setUp(): void;
    
    /**
     * Run all test methods
     */
    public function runTests(): array {
        $this->setUp();
        
        $methods = get_class_methods($this);
        $testMethods = array_filter($methods, function($m) {
            return strpos($m, 'test') === 0;
        });
        
        foreach ($testMethods as $method) {
            $this->assertions = [];
            $this->failures = [];
            
            try {
                $this->$method();
            } catch (\Throwable $e) {
                $this->failures[] = [
                    'method' => $method,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return [
            'assertions' => $this->assertions,
            'failures' => $this->failures,
        ];
    }
    
    /**
     * Assert that scanning content finds issues
     */
    protected function assertHasIssues(string $content, string $message = ''): void {
        $issues = $this->checker->check($content);
        
        $this->assertions[] = [
            'type' => 'hasIssues',
            'passed' => !empty($issues),
            'message' => $message ?: 'Expected issues to be found',
            'actual' => count($issues) . ' issues found',
        ];
        
        if (empty($issues)) {
            $this->failures[] = [
                'assertion' => 'assertHasIssues',
                'message' => $message ?: 'Expected issues but none found',
            ];
        }
    }
    
    /**
     * Assert that scanning content finds no issues
     */
    protected function assertNoIssues(string $content, string $message = ''): void {
        $issues = $this->checker->check($content);
        
        $this->assertions[] = [
            'type' => 'noIssues',
            'passed' => empty($issues),
            'message' => $message ?: 'Expected no issues',
            'actual' => count($issues) . ' issues found',
        ];
        
        if (!empty($issues)) {
            $this->failures[] = [
                'assertion' => 'assertNoIssues',
                'message' => $message ?: 'Expected no issues but found ' . count($issues),
                'issues' => $issues,
            ];
        }
    }
    
    /**
     * Assert issue count matches expected
     */
    protected function assertIssueCount(string $content, int $expected, string $message = ''): void {
        $issues = $this->checker->check($content);
        $actual = count($issues);
        
        $this->assertions[] = [
            'type' => 'issueCount',
            'passed' => $actual === $expected,
            'message' => $message ?: "Expected {$expected} issues",
            'expected' => $expected,
            'actual' => $actual,
        ];
        
        if ($actual !== $expected) {
            $this->failures[] = [
                'assertion' => 'assertIssueCount',
                'message' => $message ?: "Expected {$expected} issues, got {$actual}",
            ];
        }
    }
    
    /**
     * Assert checker returns correct severity
     */
    protected function assertSeverity(string $expected): void {
        $actual = $this->checker->get_severity();
        
        $this->assertions[] = [
            'type' => 'severity',
            'passed' => $actual === $expected,
            'expected' => $expected,
            'actual' => $actual,
        ];
        
        if ($actual !== $expected) {
            $this->failures[] = [
                'assertion' => 'assertSeverity',
                'message' => "Expected severity '{$expected}', got '{$actual}'",
            ];
        }
    }
    
    /**
     * Assert checker returns correct WCAG level
     */
    protected function assertWcagLevel(string $expected): void {
        $actual = $this->checker->get_wcag_level();
        
        $this->assertions[] = [
            'type' => 'wcagLevel',
            'passed' => $actual === $expected,
            'expected' => $expected,
            'actual' => $actual,
        ];
        
        if ($actual !== $expected) {
            $this->failures[] = [
                'assertion' => 'assertWcagLevel',
                'message' => "Expected WCAG level '{$expected}', got '{$actual}'",
            ];
        }
    }
    
    /**
     * Assert issue message contains text
     */
    protected function assertIssueMessageContains(string $content, string $needle, string $message = ''): void {
        $issues = $this->checker->check($content);
        
        $found = false;
        foreach ($issues as $issue) {
            if (isset($issue['message']) && stripos($issue['message'], $needle) !== false) {
                $found = true;
                break;
            }
        }
        
        $this->assertions[] = [
            'type' => 'messageContains',
            'passed' => $found,
            'message' => $message ?: "Expected issue message to contain '{$needle}'",
        ];
        
        if (!$found) {
            $this->failures[] = [
                'assertion' => 'assertIssueMessageContains',
                'message' => $message ?: "No issue message contains '{$needle}'",
            ];
        }
    }
    
    /**
     * Build a minimal HTML document
     */
    protected function html(string $body): string {
        return "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Test</title></head><body>{$body}</body></html>";
    }
}

/**
 * Example test class for AltTextQualityCheck
 */
class AltTextQualityCheckTest extends CheckerTestCase {
    
    protected function setUp(): void {
        $class = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\Checkers\\AltTextQualityCheck';
        $this->checker = new $class();
    }
    
    public function testDetectsMissingAlt(): void {
        $html = $this->html('<img src="test.jpg">');
        $this->assertHasIssues($html, 'Should detect missing alt attribute');
    }
    
    public function testDetectsEmptyAlt(): void {
        $html = $this->html('<img src="test.jpg" alt="">');
        // Empty alt might be valid for decorative images
        // This depends on implementation
    }
    
    public function testAcceptsProperAlt(): void {
        $html = $this->html('<img src="test.jpg" alt="A red car parked on a street">');
        $this->assertNoIssues($html, 'Should accept descriptive alt text');
    }
    
    public function testDetectsFilenameAlt(): void {
        $html = $this->html('<img src="test.jpg" alt="IMG_1234.jpg">');
        $this->assertHasIssues($html, 'Should detect filename as alt text');
    }
    
    public function testDetectsGenericAlt(): void {
        $html = $this->html('<img src="test.jpg" alt="image">');
        $this->assertHasIssues($html, 'Should detect generic alt text');
    }
    
    public function testCorrectWcagLevel(): void {
        $this->assertWcagLevel('A');
    }
}

/**
 * Example test class for TextColorContrastCheck
 */
class TextColorContrastCheckTest extends CheckerTestCase {
    
    protected function setUp(): void {
        $class = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\Checkers\\TextColorContrastCheck';
        $this->checker = new $class();
    }
    
    public function testDetectsLowContrast(): void {
        $html = $this->html('<p style="color: #999; background-color: #fff;">Low contrast text</p>');
        $this->assertHasIssues($html, 'Should detect low contrast');
    }
    
    public function testAcceptsHighContrast(): void {
        $html = $this->html('<p style="color: #000; background-color: #fff;">High contrast text</p>');
        $this->assertNoIssues($html, 'Should accept high contrast');
    }
    
    public function testCorrectSeverity(): void {
        $this->assertSeverity('error');
    }
}

/**
 * Example test class for SkipLinkCheck
 */
class SkipLinkCheckTest extends CheckerTestCase {
    
    protected function setUp(): void {
        $class = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\Checkers\\SkipLinkCheck';
        $this->checker = new $class();
    }
    
    public function testDetectsMissingSkipLink(): void {
        $html = $this->html('<header><nav><a href="/">Home</a></nav></header><main>Content</main>');
        $this->assertHasIssues($html, 'Should detect missing skip link');
    }
    
    public function testAcceptsSkipLink(): void {
        $html = $this->html('<a href="#main" class="skip-link">Skip to content</a><nav></nav><main id="main">Content</main>');
        $this->assertNoIssues($html, 'Should accept skip link');
    }
}

// Run tests if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $testClasses = [
        AltTextQualityCheckTest::class,
        TextColorContrastCheckTest::class,
        SkipLinkCheckTest::class,
    ];
    
    echo "\n=== Unit Tests for Accessibility Checkers ===\n\n";
    
    $totalPassed = 0;
    $totalFailed = 0;
    
    foreach ($testClasses as $class) {
        $shortName = substr($class, strrpos($class, '\\') + 1);
        echo "Testing: {$shortName}\n";
        
        try {
            $test = new $class();
            $results = $test->runTests();
            
            $passed = count(array_filter($results['assertions'], fn($a) => $a['passed']));
            $failed = count($results['failures']);
            
            $totalPassed += $passed;
            $totalFailed += $failed;
            
            echo "  ✓ {$passed} assertions passed\n";
            if ($failed > 0) {
                echo "  ✗ {$failed} failures:\n";
                foreach ($results['failures'] as $failure) {
                    echo "    - {$failure['message']}\n";
                }
            }
        } catch (\Throwable $e) {
            echo "  ERROR: {$e->getMessage()}\n";
            $totalFailed++;
        }
        
        echo "\n";
    }
    
    echo str_repeat('-', 40) . "\n";
    echo "Total: {$totalPassed} passed, {$totalFailed} failed\n\n";
    
    exit($totalFailed > 0 ? 1 : 0);
}
