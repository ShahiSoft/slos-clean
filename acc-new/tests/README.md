# Accessibility Scanner Test Suite

Comprehensive testing framework for the Accessibility Scanner module.

## Directory Structure

```
tests/
├── fixtures/
│   ├── passing/           # HTML that should pass all checks
│   │   ├── complete-accessible-page.html
│   │   ├── accessible-form.html
│   │   ├── accessible-navigation.html
│   │   ├── accessible-media.html
│   │   └── accessible-tables.html
│   │
│   ├── failing/           # HTML that should trigger specific issues
│   │   ├── missing-alt.html
│   │   ├── low-contrast.html
│   │   ├── missing-skip-link.html
│   │   ├── keyboard-issues.html
│   │   ├── form-labels.html
│   │   ├── heading-structure.html
│   │   ├── generic-links.html
│   │   ├── landmarks.html
│   │   ├── aria-issues.html
│   │   ├── modal-issues.html
│   │   ├── video-issues.html
│   │   ├── table-issues.html
│   │   ├── focus-indicator.html
│   │   ├── viewport-scaling.html
│   │   ├── status-messages.html
│   │   ├── animation-issues.html
│   │   └── timing-issues.html
│   │
│   └── edge-cases/        # Complex scenarios for testing edge cases
│       ├── decorative-images.html
│       ├── nested-landmarks.html
│       ├── implicit-labels.html
│       ├── large-text-contrast.html
│       ├── color-formats.html
│       ├── language-changes.html
│       └── touch-targets.html
│
├── run-tests.php          # Main test runner script
├── CheckerTestCase.php    # Unit test framework
└── README.md              # This file
```

## Running Tests

### Basic Test Run

```bash
cd acc-new/tests
php run-tests.php
```

### With Options

```bash
# Verbose output
php run-tests.php --verbose

# Filter by name
php run-tests.php --filter=contrast

# List all registered checkers
php run-tests.php --list-checks

# Summary only
php run-tests.php --summary-only

# Help
php run-tests.php --help
```

### Unit Tests

```bash
php CheckerTestCase.php
```

## Test Categories

### Passing Tests (`fixtures/passing/`)

These HTML files should trigger **zero** accessibility issues. They demonstrate proper implementation of accessibility features:

- **complete-accessible-page.html** - Full page with all major accessibility features
- **accessible-form.html** - Forms with proper labels, fieldsets, and ARIA
- **accessible-navigation.html** - Navigation with skip links, landmarks, and labels
- **accessible-media.html** - Images, videos, and audio with proper alternatives
- **accessible-tables.html** - Data tables with headers, scopes, and captions

### Failing Tests (`fixtures/failing/`)

These HTML files should trigger **specific** accessibility issues. Each file tests one or more related checkers:

| Fixture | Expected Checkers |
|---------|-------------------|
| missing-alt.html | `missing-alt-text`, `alt-text-quality`, `empty-alt-text` |
| low-contrast.html | `text-color-contrast` |
| missing-skip-link.html | `skip-link` |
| keyboard-issues.html | `interactive-element`, `keyboard-trap`, `positive-tabindex` |
| form-labels.html | `missing-form-label` |
| heading-structure.html | `skipped-heading-level`, `empty-heading`, `multiple-h1` |
| generic-links.html | `generic-link-text` |
| landmarks.html | `landmark-role` |
| aria-issues.html | `aria-role`, `aria-attribute`, `aria-state` |
| modal-issues.html | `modal-accessibility` |
| video-issues.html | `video-accessibility`, `iframe-title` |
| table-issues.html | `table-header`, `table-caption` |
| focus-indicator.html | `focus-indicator` |
| viewport-scaling.html | `viewport` |
| status-messages.html | `status-message`, `live-region` |
| animation-issues.html | `animation-pause` |
| timing-issues.html | `timing-control` |

### Edge Cases (`fixtures/edge-cases/`)

These files test complex scenarios where the correct behavior may be nuanced:

- **decorative-images.html** - When empty alt is appropriate vs. required
- **nested-landmarks.html** - Header/footer inside article (not landmarks)
- **implicit-labels.html** - Various valid labeling methods
- **large-text-contrast.html** - Different contrast requirements for large text
- **color-formats.html** - Various CSS color format parsing
- **language-changes.html** - Marking language changes properly
- **touch-targets.html** - Minimum touch target sizes and exceptions

## Writing New Tests

### Adding a Passing Test

1. Create a new HTML file in `fixtures/passing/`
2. Ensure it follows all accessibility best practices
3. Run tests to verify it passes all checkers

### Adding a Failing Test

1. Create a new HTML file in `fixtures/failing/`
2. Include HTML that should trigger specific issues
3. Add a mapping in `run-tests.php`:

```php
$this->checkerMapping['failing/your-file.html'] = ['checker-id-1', 'checker-id-2'];
```

4. Run tests to verify it fails as expected

### Adding Unit Tests

1. Open `CheckerTestCase.php`
2. Create a new test class extending `CheckerTestCase`:

```php
class YourCheckerTest extends CheckerTestCase {
    protected function setUp(): void {
        $class = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\Checkers\\YourCheck';
        $this->checker = new $class();
    }
    
    public function testYourScenario(): void {
        $html = $this->html('<div>Your test HTML</div>');
        $this->assertHasIssues($html, 'Should detect issue');
    }
}
```

3. Add the class to the `$testClasses` array at the bottom of the file

## Interpreting Results

### Status Icons

- ✅ **PASS** - Test behaved as expected
- ❌ **FAIL** - Test did not behave as expected
- 💥 **ERROR** - Test threw an exception
- ℹ️ **INFO** - Informational (edge cases)

### Exit Codes

- `0` - All tests passed
- `1` - One or more tests failed

## Integration with CI/CD

Add to your CI pipeline:

```yaml
- name: Run Accessibility Tests
  run: |
    cd wp-content/plugins/Shahi\ LegalOps\ Suite\ -\ 3.1.1/acc-new/tests
    php run-tests.php --summary-only
```

## Performance Benchmarks

Target performance metrics:

| Metric | Target |
|--------|--------|
| Scan 100 posts | < 30 seconds |
| Single page scan | < 500ms |
| Memory usage | < 50MB |

Run performance tests:

```bash
php run-tests.php --verbose 2>&1 | grep "duration"
```

## Contributing

When adding new checkers:

1. Add passing examples to `fixtures/passing/` that demonstrate proper usage
2. Add failing examples to `fixtures/failing/` that trigger the checker
3. Add edge cases if the checker has complex logic
4. Update the checker mapping in `run-tests.php`
5. Write unit tests in `CheckerTestCase.php`
6. Run full test suite before submitting

## Resources

- [WCAG 2.1 Quick Reference](https://www.w3.org/WAI/WCAG21/quickref/)
- [WCAG 2.2 Guidelines](https://www.w3.org/TR/WCAG22/)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [axe-core Rules](https://github.com/dequelabs/axe-core/blob/develop/doc/rule-descriptions.md)
