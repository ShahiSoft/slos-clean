<?php
/**
 * Accessibility Scanner Test Runner
 *
 * Comprehensive test suite for accessibility checkers.
 * Run from command line: php run-tests.php [--verbose] [--filter=<pattern>]
 *
 * @package ShahiLegalOps
 * @since 3.1.2
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Parse command line arguments
$options = getopt('', ['verbose', 'filter:', 'help', 'list-checks', 'summary-only']);

if (isset($options['help'])) {
    echo <<<HELP
Accessibility Scanner Test Runner

Usage: php run-tests.php [options]

Options:
  --verbose       Show detailed output for each test
  --filter=NAME   Only run tests matching NAME pattern
  --list-checks   List all available checkers
  --summary-only  Only show final summary
  --help          Show this help message

Examples:
  php run-tests.php
  php run-tests.php --verbose
  php run-tests.php --filter=contrast
  php run-tests.php --list-checks

HELP;
    exit(0);
}

// Define ABSPATH for WordPress compatibility
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 4) . '/');
}

// Autoloader for scanner classes
spl_autoload_register(function ($class) {
    $prefix = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\';
    $base_dir = dirname(__DIR__, 2) . '/includes/Modules/AccessibilityScanner/Scanner/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    
    // Handle Checkers subdirectory
    if (strpos($relative_class, 'Checkers\\') === 0) {
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    } else {
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    }

    if (file_exists($file)) {
        require $file;
    }
});

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\CheckInterface;

/**
 * Test Runner Class
 */
class AccessibilityTestRunner {
    
    /** @var ScannerEngine */
    private $scanner;
    
    /** @var array */
    private $results = [];
    
    /** @var array */
    private $stats = [
        'passed' => 0,
        'failed' => 0,
        'errors' => 0,
        'skipped' => 0,
    ];
    
    /** @var bool */
    private $verbose = false;
    
    /** @var string|null */
    private $filter = null;
    
    /** @var bool */
    private $summaryOnly = false;
    
    /** @var string */
    private $fixturesDir;
    
    /** @var array */
    private $checkerMapping = [];
    
    /**
     * Constructor
     */
    public function __construct(array $options = []) {
        $this->verbose = isset($options['verbose']);
        $this->filter = $options['filter'] ?? null;
        $this->summaryOnly = isset($options['summary-only']);
        $this->fixturesDir = __DIR__ . '/fixtures';
        
        $this->scanner = new ScannerEngine();
        $this->registerAllChecks();
        $this->buildCheckerMapping();
    }
    
    /**
     * Register all available checkers
     */
    private function registerAllChecks(): void {
        $checkersDir = dirname(__DIR__, 2) . '/includes/Modules/AccessibilityScanner/Scanner/Checkers/';
        $files = glob($checkersDir . '*Check.php');
        
        foreach ($files as $file) {
            $className = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\Checkers\\' 
                       . basename($file, '.php');
            
            if (class_exists($className)) {
                try {
                    $check = new $className();
                    if ($check instanceof CheckInterface) {
                        $this->scanner->register_check($check);
                    }
                } catch (\Throwable $e) {
                    $this->output("Warning: Could not load {$className}: " . $e->getMessage(), 'yellow');
                }
            }
        }
    }
    
    /**
     * Build mapping of fixture files to expected checkers
     */
    private function buildCheckerMapping(): void {
        $this->checkerMapping = [
            // Failing fixtures -> expected checkers to trigger
            'failing/missing-alt.html' => ['missing-alt-text', 'alt-text-quality', 'empty-alt-text'],
            'failing/low-contrast.html' => ['text-color-contrast'],
            'failing/missing-skip-link.html' => ['skip-link'],
            'failing/keyboard-issues.html' => ['interactive-element', 'keyboard-trap', 'positive-tabindex'],
            'failing/form-labels.html' => ['missing-form-label'],
            'failing/heading-structure.html' => ['skipped-heading-level', 'empty-heading', 'multiple-h1'],
            'failing/generic-links.html' => ['generic-link-text'],
            'failing/landmarks.html' => ['landmark-role'],
            'failing/aria-issues.html' => ['aria-role', 'aria-attribute', 'aria-state'],
            'failing/modal-issues.html' => ['modal-accessibility'],
            'failing/video-issues.html' => ['video-accessibility', 'iframe-title'],
            'failing/table-issues.html' => ['table-header', 'table-caption'],
            'failing/focus-indicator.html' => ['focus-indicator'],
            'failing/viewport-scaling.html' => ['viewport'],
            'failing/status-messages.html' => ['status-message', 'live-region'],
            'failing/animation-issues.html' => ['animation-pause'],
            'failing/timing-issues.html' => ['timing-control'],
        ];
    }
    
    /**
     * Run all tests
     */
    public function run(): int {
        $this->output("\n=== Accessibility Scanner Test Suite ===\n", 'cyan');
        $this->output("Scanner Engine initialized with " . count($this->scanner->get_checks()) . " checkers\n");
        
        $startTime = microtime(true);
        
        // Run passing tests
        $this->output("\n--- Passing Test Fixtures (should have NO issues) ---\n", 'white');
        $this->runPassingTests();
        
        // Run failing tests
        $this->output("\n--- Failing Test Fixtures (should detect issues) ---\n", 'white');
        $this->runFailingTests();
        
        // Run edge case tests
        $this->output("\n--- Edge Case Tests ---\n", 'white');
        $this->runEdgeCaseTests();
        
        $duration = round(microtime(true) - $startTime, 2);
        
        // Output summary
        $this->outputSummary($duration);
        
        // Return exit code
        return ($this->stats['failed'] + $this->stats['errors']) > 0 ? 1 : 0;
    }
    
    /**
     * Run passing test fixtures
     */
    private function runPassingTests(): void {
        $files = glob($this->fixturesDir . '/passing/*.html');
        
        foreach ($files as $file) {
            if ($this->shouldSkip($file)) continue;
            
            $filename = basename($file);
            $content = file_get_contents($file);
            
            try {
                $issues = $this->scanner->scan($content);
                $issueCount = $this->countIssues($issues);
                
                if ($issueCount === 0) {
                    $this->recordResult($filename, 'pass', 'No issues detected (expected)');
                } else {
                    $this->recordResult($filename, 'fail', "Expected 0 issues, found {$issueCount}", $issues);
                }
            } catch (\Throwable $e) {
                $this->recordResult($filename, 'error', $e->getMessage());
            }
        }
    }
    
    /**
     * Run failing test fixtures
     */
    private function runFailingTests(): void {
        $files = glob($this->fixturesDir . '/failing/*.html');
        
        foreach ($files as $file) {
            if ($this->shouldSkip($file)) continue;
            
            $filename = basename($file);
            $relativePath = 'failing/' . $filename;
            $content = file_get_contents($file);
            $expectedChecks = $this->checkerMapping[$relativePath] ?? [];
            
            try {
                $issues = $this->scanner->scan($content);
                $issueCount = $this->countIssues($issues);
                $triggeredChecks = array_keys(array_filter($issues, function($check) {
                    return !empty($check['issues']);
                }));
                
                if ($issueCount === 0) {
                    $this->recordResult($filename, 'fail', 'Expected issues but found none', null, $expectedChecks);
                } else {
                    // Check if expected checkers triggered
                    $matched = array_intersect($expectedChecks, $triggeredChecks);
                    if (!empty($matched) || empty($expectedChecks)) {
                        $this->recordResult($filename, 'pass', "{$issueCount} issues detected", $issues);
                    } else {
                        $this->recordResult(
                            $filename, 
                            'fail', 
                            "Issues detected but not from expected checkers",
                            $issues,
                            $expectedChecks
                        );
                    }
                }
            } catch (\Throwable $e) {
                $this->recordResult($filename, 'error', $e->getMessage());
            }
        }
    }
    
    /**
     * Run edge case tests
     */
    private function runEdgeCaseTests(): void {
        $files = glob($this->fixturesDir . '/edge-cases/*.html');
        
        foreach ($files as $file) {
            if ($this->shouldSkip($file)) continue;
            
            $filename = basename($file);
            $content = file_get_contents($file);
            
            try {
                $issues = $this->scanner->scan($content);
                $issueCount = $this->countIssues($issues);
                
                // Edge cases are informational - we just record what was found
                $this->recordResult(
                    $filename, 
                    'info', 
                    "{$issueCount} issues detected (edge case)", 
                    $issues
                );
            } catch (\Throwable $e) {
                $this->recordResult($filename, 'error', $e->getMessage());
            }
        }
    }
    
    /**
     * Count total issues from scan result
     */
    private function countIssues(array $results): int {
        $count = 0;
        foreach ($results as $checkResult) {
            if (isset($checkResult['issues'])) {
                $count += count($checkResult['issues']);
            }
        }
        return $count;
    }
    
    /**
     * Check if test should be skipped based on filter
     */
    private function shouldSkip(string $file): bool {
        if ($this->filter === null) {
            return false;
        }
        return stripos(basename($file), $this->filter) === false;
    }
    
    /**
     * Record test result
     */
    private function recordResult(
        string $filename, 
        string $status, 
        string $message, 
        ?array $issues = null,
        ?array $expectedChecks = null
    ): void {
        $result = [
            'file' => $filename,
            'status' => $status,
            'message' => $message,
            'issues' => $issues,
            'expected' => $expectedChecks,
        ];
        
        $this->results[] = $result;
        
        // Update stats
        switch ($status) {
            case 'pass':
                $this->stats['passed']++;
                $icon = '✅';
                $color = 'green';
                break;
            case 'fail':
                $this->stats['failed']++;
                $icon = '❌';
                $color = 'red';
                break;
            case 'error':
                $this->stats['errors']++;
                $icon = '💥';
                $color = 'red';
                break;
            case 'info':
                $this->stats['skipped']++;
                $icon = 'ℹ️';
                $color = 'blue';
                break;
            default:
                $icon = '❓';
                $color = 'white';
        }
        
        if (!$this->summaryOnly) {
            $this->output("{$icon} {$filename}: {$message}\n", $color);
            
            if ($this->verbose && $issues) {
                $this->outputIssueDetails($issues);
            }
            
            if ($this->verbose && $expectedChecks && $status === 'fail') {
                $this->output("   Expected checks: " . implode(', ', $expectedChecks) . "\n", 'yellow');
            }
        }
    }
    
    /**
     * Output issue details in verbose mode
     */
    private function outputIssueDetails(array $issues): void {
        foreach ($issues as $checkId => $checkResult) {
            if (empty($checkResult['issues'])) continue;
            
            $count = count($checkResult['issues']);
            $this->output("   → {$checkId}: {$count} issue(s)\n", 'gray');
            
            if ($this->verbose) {
                foreach (array_slice($checkResult['issues'], 0, 3) as $issue) {
                    $msg = $issue['message'] ?? 'No message';
                    $this->output("      • " . substr($msg, 0, 70) . "...\n", 'gray');
                }
                if ($count > 3) {
                    $this->output("      ... and " . ($count - 3) . " more\n", 'gray');
                }
            }
        }
    }
    
    /**
     * Output final summary
     */
    private function outputSummary(float $duration): void {
        $total = $this->stats['passed'] + $this->stats['failed'] + $this->stats['errors'] + $this->stats['skipped'];
        
        $this->output("\n" . str_repeat('=', 50) . "\n", 'cyan');
        $this->output("TEST SUMMARY\n", 'white');
        $this->output(str_repeat('=', 50) . "\n", 'cyan');
        
        $this->output("✅ Passed:  {$this->stats['passed']}\n", 'green');
        $this->output("❌ Failed:  {$this->stats['failed']}\n", $this->stats['failed'] > 0 ? 'red' : 'white');
        $this->output("💥 Errors:  {$this->stats['errors']}\n", $this->stats['errors'] > 0 ? 'red' : 'white');
        $this->output("ℹ️  Info:    {$this->stats['skipped']}\n", 'blue');
        $this->output(str_repeat('-', 50) . "\n", 'gray');
        $this->output("📊 Total:   {$total} tests in {$duration}s\n", 'white');
        
        $passRate = $total > 0 ? round(($this->stats['passed'] / ($total - $this->stats['skipped'])) * 100, 1) : 0;
        
        if ($this->stats['failed'] === 0 && $this->stats['errors'] === 0) {
            $this->output("\n🎉 All tests passed!\n", 'green');
        } else {
            $this->output("\n⚠️  Some tests failed. Pass rate: {$passRate}%\n", 'yellow');
        }
        
        $this->output("\n");
    }
    
    /**
     * List all registered checkers
     */
    public function listCheckers(): void {
        $this->output("\n=== Registered Accessibility Checkers ===\n\n", 'cyan');
        
        $checks = $this->scanner->get_checks();
        
        $grouped = [
            'A' => [],
            'AA' => [],
            'AAA' => [],
            'Best Practice' => [],
        ];
        
        foreach ($checks as $id => $check) {
            $level = $check->get_wcag_level();
            if (!isset($grouped[$level])) {
                $grouped[$level] = [];
            }
            $grouped[$level][$id] = $check;
        }
        
        foreach ($grouped as $level => $levelChecks) {
            if (empty($levelChecks)) continue;
            
            $this->output("WCAG Level {$level} (" . count($levelChecks) . " checks)\n", 'white');
            $this->output(str_repeat('-', 40) . "\n", 'gray');
            
            foreach ($levelChecks as $id => $check) {
                $severity = $check->get_severity();
                $this->output("  • {$id} [{$severity}]\n", 'white');
            }
            $this->output("\n");
        }
        
        $this->output("Total: " . count($checks) . " checkers registered\n\n", 'cyan');
    }
    
    /**
     * Output colored text to console
     */
    private function output(string $text, string $color = 'white'): void {
        $colors = [
            'black' => '0;30',
            'red' => '0;31',
            'green' => '0;32',
            'yellow' => '0;33',
            'blue' => '0;34',
            'magenta' => '0;35',
            'cyan' => '0;36',
            'white' => '0;37',
            'gray' => '1;30',
        ];
        
        // Check if output supports colors
        $hasColors = (DIRECTORY_SEPARATOR === '/' && function_exists('posix_isatty') && posix_isatty(STDOUT))
                  || (getenv('ANSICON') !== false)
                  || (getenv('ConEmuANSI') === 'ON')
                  || (getenv('TERM') === 'xterm-256color');
        
        if ($hasColors && isset($colors[$color])) {
            echo "\033[" . $colors[$color] . "m" . $text . "\033[0m";
        } else {
            echo $text;
        }
    }
}

// Run the tests
try {
    $runner = new AccessibilityTestRunner($options);
    
    if (isset($options['list-checks'])) {
        $runner->listCheckers();
        exit(0);
    }
    
    $exitCode = $runner->run();
    exit($exitCode);
    
} catch (\Throwable $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    if (isset($options['verbose'])) {
        echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    }
    exit(1);
}
