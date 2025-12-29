<?php
/**
 * Test Runner Script
 * 
 * Execute from command line:
 *   php acc-new/tests/run-tests.php
 */

require_once __DIR__ . '/FixerTestHarness.php';

$harness = new FixerTestHarness();
$success = $harness->run_all_tests();

exit($success ? 0 : 1);
