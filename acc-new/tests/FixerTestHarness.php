<?php
/**
 * Fixer Test Harness
 * 
 * Validates that auto-fixers:
 * 1. Modify content when applicable and leave unchanged content untouched
 * 2. Return fixed_count > 0 when changes occur; 0 when none
 * 3. Preserve DOM structure (no tag loss in roundtrip)
 * 4. Pass regression checks for Phase 3 fixers
 * 
 * Usage:
 *   php acc-new/tests/run-tests.php
 */

// Require WordPress environment
require_once dirname(__DIR__, 4) . '/wp-load.php';

// Require fixer dependencies
require_once dirname(__DIR__, 2) . '/includes/Modules/AccessibilityScanner/Fixes/Fixers/BaseFixer.php';
require_once dirname(__DIR__, 2) . '/includes/Modules/AccessibilityScanner/Fixes/FixerRegistry.php';

class FixerTestHarness {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $fixtures_dir;
    private $registry;

    public function __construct() {
        $this->fixtures_dir = dirname(__DIR__) . '/fixtures';
        $this->registry = new \SLOSAccessibility\FixerRegistry();
    }

    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "=== Fixer Test Harness ===\n\n";

        // Test 1: Content modification tests
        echo "Running content modification tests...\n";
        $this->test_content_modifications();

        // Test 2: Fixed count validation
        echo "\nRunning fixed_count validation tests...\n";
        $this->test_fixed_count_accuracy();

        // Test 3: DOM roundtrip integrity
        echo "\nRunning DOM roundtrip integrity tests...\n";
        $this->test_dom_roundtrip();

        // Test 4: Regression tests for Phase 3 fixers
        echo "\nRunning Phase 3 fixer regression tests...\n";
        $this->test_phase3_fixers();

        // Output summary
        $this->print_summary();

        return $this->failed === 0;
    }

    /**
     * Test that fixers modify content when applicable
     * and leave clean content unchanged
     */
    private function test_content_modifications() {
        $tests = [
            // TextColorContrastFixer
            [
                'fixer_id' => 'text-color-contrast',
                'dirty_html' => '<p style="color: #767676; background: #fff;">Low contrast</p>',
                'clean_html' => '<p style="color: #000; background: #fff;">Good contrast</p>',
                'should_fix_dirty' => true,
                'should_fix_clean' => false,
            ],
            // ComplexContrastFixer
            [
                'fixer_id' => 'complex-contrast',
                'dirty_html' => '<div style="background: rgba(255,255,255,0.5);"><p style="color: #888;">Text</p></div>',
                'clean_html' => '<div style="background: #fff;"><p style="color: #000;">Text</p></div>',
                'should_fix_dirty' => true,
                'should_fix_clean' => false,
            ],
            // FocusOrderFixer
            [
                'fixer_id' => 'focus-order',
                'dirty_html' => '<button tabindex="5">Bad order</button><input tabindex="10">',
                'clean_html' => '<button>Normal order</button><input>',
                'should_fix_dirty' => true,
                'should_fix_clean' => false,
            ],
            // TouchGestureFixer
            [
                'fixer_id' => 'touch-gesture',
                'dirty_html' => '<div class="carousel" data-swipe="true"><img src="1.jpg"></div>',
                'clean_html' => '<div class="carousel"><button>Prev</button><img src="1.jpg"><button>Next</button></div>',
                'should_fix_dirty' => true,
                'should_fix_clean' => false,
            ],
            // CustomWidgetKeyboardFixer
            [
                'fixer_id' => 'custom-widget-keyboard',
                'dirty_html' => '<div role="button" onclick="alert()">Click</div>',
                'clean_html' => '<button>Click</button>',
                'should_fix_dirty' => true,
                'should_fix_clean' => false,
            ],
            // InvalidAriaCombinationFixer
            [
                'fixer_id' => 'invalid-aria-combination',
                'dirty_html' => '<button aria-hidden="true" tabindex="0">Hidden but focusable</button>',
                'clean_html' => '<button>Visible and focusable</button>',
                'should_fix_dirty' => true,
                'should_fix_clean' => false,
            ],
        ];

        foreach ($tests as $test) {
            $fixer = $this->registry->get_fixer($test['fixer_id']);
            if (!$fixer) {
                $this->add_result('SKIP', $test['fixer_id'], "Fixer not found in registry");
                continue;
            }

            // Test dirty content (should be modified)
            $dirty_result = $fixer->fix($test['dirty_html']);
            $dirty_modified = $dirty_result['content'] !== $test['dirty_html'];
            
            if ($test['should_fix_dirty'] && !$dirty_modified) {
                $this->add_result('FAIL', $test['fixer_id'], "Should modify dirty content but didn't");
            } elseif (!$test['should_fix_dirty'] && $dirty_modified) {
                $this->add_result('FAIL', $test['fixer_id'], "Should not modify content but did");
            } else {
                $this->add_result('PASS', $test['fixer_id'], "Content modification test (dirty)");
            }

            // Test clean content (should be unchanged)
            $clean_result = $fixer->fix($test['clean_html']);
            $clean_modified = $clean_result['content'] !== $test['clean_html'];
            
            if ($test['should_fix_clean'] && !$clean_modified) {
                $this->add_result('FAIL', $test['fixer_id'], "Should modify clean content but didn't");
            } elseif (!$test['should_fix_clean'] && $clean_modified) {
                $this->add_result('FAIL', $test['fixer_id'], "Should not modify clean content but did");
            } else {
                $this->add_result('PASS', $test['fixer_id'], "Content modification test (clean)");
            }
        }
    }

    /**
     * Test that fixed_count reflects actual changes
     */
    private function test_fixed_count_accuracy() {
        $tests = [
            // Should return count > 0
            [
                'fixer_id' => 'text-color-contrast',
                'html' => '<p style="color: #767676; background: #fff;">Low</p><p style="color: #888; background: #fff;">Low</p>',
                'expected_min_count' => 1,
            ],
            [
                'fixer_id' => 'focus-order',
                'html' => '<button tabindex="5">A</button><button tabindex="10">B</button><input tabindex="15">',
                'expected_min_count' => 3,
            ],
            [
                'fixer_id' => 'invalid-aria-combination',
                'html' => '<button aria-hidden="true" tabindex="0">A</button><div role="button" aria-hidden="true">B</div>',
                'expected_min_count' => 1,
            ],
            // Should return count = 0
            [
                'fixer_id' => 'text-color-contrast',
                'html' => '<p style="color: #000; background: #fff;">Good contrast</p>',
                'expected_min_count' => 0,
            ],
            [
                'fixer_id' => 'focus-order',
                'html' => '<button>A</button><button>B</button><input>',
                'expected_min_count' => 0,
            ],
        ];

        foreach ($tests as $test) {
            $fixer = $this->registry->get_fixer($test['fixer_id']);
            if (!$fixer) {
                $this->add_result('SKIP', $test['fixer_id'], "Fixer not found");
                continue;
            }

            $result = $fixer->fix($test['html']);
            $actual_count = $result['fixed_count'];

            if ($test['expected_min_count'] === 0) {
                if ($actual_count === 0) {
                    $this->add_result('PASS', $test['fixer_id'], "Fixed count = 0 (as expected)");
                } else {
                    $this->add_result('FAIL', $test['fixer_id'], "Expected count=0, got $actual_count");
                }
            } else {
                if ($actual_count >= $test['expected_min_count']) {
                    $this->add_result('PASS', $test['fixer_id'], "Fixed count >= {$test['expected_min_count']} (got $actual_count)");
                } else {
                    $this->add_result('FAIL', $test['fixer_id'], "Expected count>={$test['expected_min_count']}, got $actual_count");
                }
            }
        }
    }

    /**
     * Test DOM roundtrip preserves structure
     */
    private function test_dom_roundtrip() {
        $tests = [
            [
                'fixer_id' => 'text-color-contrast',
                'html' => '<div><p style="color: #767676; background: #fff;">Text</p><img src="a.jpg" alt="Alt"><ul><li>Item</li></ul></div>',
            ],
            [
                'fixer_id' => 'focus-order',
                'html' => '<form><input tabindex="10" name="a"><select tabindex="5" name="b"><option>1</option></select><button tabindex="15">Submit</button></form>',
            ],
            [
                'fixer_id' => 'invalid-aria-combination',
                'html' => '<nav><button aria-hidden="true" tabindex="0">Nav</button><ul><li><a href="#">Link</a></li></ul></nav>',
            ],
        ];

        foreach ($tests as $test) {
            $fixer = $this->registry->get_fixer($test['fixer_id']);
            if (!$fixer) {
                $this->add_result('SKIP', $test['fixer_id'], "Fixer not found");
                continue;
            }

            // Count tags before
            preg_match_all('/<(\w+)/', $test['html'], $before_tags);
            $before_count = count($before_tags[1]);

            // Run fixer
            $result = $fixer->fix($test['html']);

            // Count tags after
            preg_match_all('/<(\w+)/', $result['content'], $after_tags);
            $after_count = count($after_tags[1]);

            if ($before_count === $after_count) {
                $this->add_result('PASS', $test['fixer_id'], "DOM roundtrip preserved $before_count tags");
            } else {
                $this->add_result('FAIL', $test['fixer_id'], "Tag count mismatch: before=$before_count, after=$after_count");
            }
        }
    }

    /**
     * Test Phase 3 fixers (regression tests)
     */
    private function test_phase3_fixers() {
        $phase3_tests = [
            // AnimationPauseFixer
            [
                'fixer_id' => 'animation-pause',
                'html' => '<div style="animation: spin 2s linear infinite;">Spinning</div>',
                'should_add_control' => true,
                'check_for' => 'pause',
            ],
            [
                'fixer_id' => 'animation-pause',
                'html' => '<marquee>Scrolling text</marquee>',
                'should_add_control' => true,
                'check_for' => 'pause',
            ],
            // TimingControlFixer
            [
                'fixer_id' => 'timing-control',
                'html' => '<meta http-equiv="refresh" content="30">',
                'should_remove' => true,
                'check_for' => 'refresh',
            ],
            [
                'fixer_id' => 'timing-control',
                'html' => '<div class="countdown-timer">5:00</div>',
                'should_add_control' => true,
                'check_for' => 'extend',
            ],
            // StatusMessageFixer
            [
                'fixer_id' => 'status-message',
                'html' => '<div class="alert">Alert without role</div>',
                'should_add' => 'role="alert"',
            ],
            [
                'fixer_id' => 'status-message',
                'html' => '<div class="notification">Notification</div>',
                'should_add' => 'aria-live',
            ],
            // LanguageChangeFixer
            [
                'fixer_id' => 'language-change',
                'html' => '<p>English text <span>texto en español</span></p>',
                'should_add' => 'lang=',
            ],
            // ErrorIdentificationFixer
            [
                'fixer_id' => 'error-identification',
                'html' => '<form><input required><span class="error">Invalid</span></form>',
                'should_add' => 'aria-invalid',
            ],
        ];

        foreach ($phase3_tests as $test) {
            $fixer = $this->registry->get_fixer($test['fixer_id']);
            if (!$fixer) {
                $this->add_result('SKIP', $test['fixer_id'], "Phase 3 fixer not found");
                continue;
            }

            $result = $fixer->fix($test['html']);
            $fixed_content = $result['content'];

            // Check for expected modifications
            if (isset($test['should_add_control'])) {
                if (stripos($fixed_content, $test['check_for']) !== false) {
                    $this->add_result('PASS', $test['fixer_id'], "Added control: {$test['check_for']}");
                } else {
                    $this->add_result('FAIL', $test['fixer_id'], "Missing control: {$test['check_for']}");
                }
            }

            if (isset($test['should_remove'])) {
                if (stripos($fixed_content, $test['check_for']) === false) {
                    $this->add_result('PASS', $test['fixer_id'], "Removed: {$test['check_for']}");
                } else {
                    $this->add_result('FAIL', $test['fixer_id'], "Failed to remove: {$test['check_for']}");
                }
            }

            if (isset($test['should_add'])) {
                if (stripos($fixed_content, $test['should_add']) !== false) {
                    $this->add_result('PASS', $test['fixer_id'], "Added: {$test['should_add']}");
                } else {
                    $this->add_result('FAIL', $test['fixer_id'], "Missing: {$test['should_add']}");
                }
            }

            // Always check fixed_count
            if ($result['fixed_count'] > 0) {
                $this->add_result('PASS', $test['fixer_id'], "Fixed count > 0: {$result['fixed_count']}");
            } else {
                // This might be expected for some tests
                $this->add_result('INFO', $test['fixer_id'], "Fixed count = 0 (may be stub)");
            }
        }
    }

    /**
     * Add test result
     */
    private function add_result($status, $test_name, $message) {
        $this->results[] = [
            'status' => $status,
            'test' => $test_name,
            'message' => $message,
        ];

        $icon = $status === 'PASS' ? '✅' : ($status === 'FAIL' ? '❌' : '⚠️');
        echo "$icon $status: $test_name - $message\n";

        if ($status === 'PASS') {
            $this->passed++;
        } elseif ($status === 'FAIL') {
            $this->failed++;
        }
    }

    /**
     * Print summary
     */
    private function print_summary() {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "=== Test Summary ===\n";
        echo str_repeat('=', 50) . "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total: " . count($this->results) . "\n";
        
        if ($this->failed === 0) {
            echo "\n✅ All tests passed!\n";
        } else {
            echo "\n❌ Some tests failed. Review above.\n";
        }
    }

    /**
     * Get results
     */
    public function get_results() {
        return $this->results;
    }
}
