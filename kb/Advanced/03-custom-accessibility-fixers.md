# Custom Accessibility Fixers Development

## Overview

Create custom accessibility fixers to extend the 96 built-in fixers with your own accessibility solutions.

## Fixer Architecture

All fixers inherit from `BaseFixer` and implement the fixer interface.

## Create a Custom Fixer

### Step 1: Create Fixer Class

```php
<?php
namespace YourNamespace\Accessibility\Fixers;

use Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers\BaseFixer;

class CustomButtonLabelFixer extends BaseFixer
{
    /**
     * Get fixer identifier
     */
    public function get_id(): string
    {
        return 'custom-button-label';
    }

    /**
     * Get fixer description
     */
    public function get_description(): string
    {
        return 'Adds accessible labels to buttons without text';
    }

    /**
     * Get WCAG criterion reference
     */
    public function get_wcag_criterion(): string
    {
        return '2.5.3'; // Level AAA
    }

    /**
     * Get severity level
     */
    public function get_severity(): string
    {
        return 'major'; // critical, major, minor
    }

    /**
     * Check if issue exists
     */
    public function check_element($element): bool
    {
        // Check if button needs a label
        if ($element->tagName !== 'button') {
            return false;
        }

        $text = trim($element->textContent);
        $aria_label = $element->getAttribute('aria-label');
        
        // Return true if button has no text and no aria-label
        return empty($text) && empty($aria_label);
    }

    /**
     * Fix the accessibility issue
     */
    public function fix($content): string
    {
        // Use DOMDocument to parse
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML(
            '<?xml encoding="UTF-8">' . $content,
            LIBXML_HTML_NOXMLATT
        );

        if (!$dom->documentElement) {
            return $content;
        }

        $xpath = new \DOMXPath($dom);
        
        // Find all buttons without text or aria-label
        $buttons = $xpath->query('//button');
        
        foreach ($buttons as $button) {
            if ($this->check_element($button)) {
                // Try to infer label from context
                $label = $this->infer_button_label($button);
                
                if (!empty($label)) {
                    $button->setAttribute('aria-label', $label);
                }
            }
        }

        // Return fixed HTML
        $fixed = '';
        if ($dom->documentElement) {
            foreach ($dom->documentElement->childNodes as $node) {
                $fixed .= $dom->saveHTML($node);
            }
        }
        
        return !empty($fixed) ? $fixed : $content;
    }

    /**
     * Infer button label from context
     */
    private function infer_button_label($button): string
    {
        // Check for icon content
        $icon = $button->querySelector('i, svg, img');
        if ($icon) {
            $classes = $icon->getAttribute('class');
            if (strpos($classes, 'fa-close') !== false) {
                return 'Close';
            }
            if (strpos($classes, 'fa-menu') !== false) {
                return 'Menu';
            }
            if (strpos($classes, 'fa-search') !== false) {
                return 'Search';
            }
        }

        // Check button position/context
        $parent = $button->parentNode;
        while ($parent) {
            $parent_id = $parent->getAttribute('id');
            $parent_class = $parent->getAttribute('class');
            
            if (strpos($parent_class, 'modal-header') !== false) {
                return 'Close Dialog';
            }
            
            if (strpos($parent_class, 'search-form') !== false) {
                return 'Submit Search';
            }
            
            $parent = $parent->parentNode;
        }

        return 'Button'; // Fallback
    }

    /**
     * Get test cases for this fixer
     */
    public static function get_test_cases(): array
    {
        return [
            [
                'input' => '<button><i class="fa-close"></i></button>',
                'expected' => 'Close',
                'description' => 'Close button with icon'
            ],
            [
                'input' => '<button><svg></svg></button>',
                'expected' => 'Button',
                'description' => 'Button with SVG icon'
            ],
            [
                'input' => '<button aria-label="Submit">Search</button>',
                'expected' => false, // Don't fix if already has label
                'description' => 'Button already has aria-label'
            ]
        ];
    }
}
```

### Step 2: Register Your Fixer

```php
// In your plugin initialization
add_filter('slos_accessibility_fixers', function($fixers) {
    $fixers['custom-button-label'] = 'YourNamespace\Accessibility\Fixers\CustomButtonLabelFixer';
    return $fixers;
});
```

### Step 3: Register Test Fixtures

```php
add_action('slos_fixer_tests', function($test_runner) {
    $test_runner->register_tests(
        'CustomButtonLabelFixer',
        CustomButtonLabelFixer::get_test_cases()
    );
});
```

## BaseFixer Methods

### Required Methods

```php
public function get_id(): string
    // Return unique fixer identifier

public function get_description(): string
    // Return human-readable description

public function fix($content): string
    // Return fixed HTML content

public function check_element($element): bool
    // Return true if element has issue
```

### Optional Methods

```php
public function get_wcag_criterion(): string
    // Return WCAG reference (e.g., '2.5.3')
    // Default: Not specified

public function get_severity(): string
    // Return 'critical', 'major', or 'minor'
    // Default: 'major'

public function requires_javascript(): bool
    // Return true if needs JavaScript fix
    // Default: false

public function get_performance_impact(): string
    // Return 'low', 'medium', 'high'
    // Default: 'low'

public static function get_test_cases(): array
    // Return array of test cases
    // Default: empty array
```

## Fixer Patterns

### Pattern 1: Simple Attribute Addition

```php
public function fix($content): string
{
    $dom = new \DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);

    $xpath = new \DOMXPath($dom);
    $elements = $xpath->query('//div[@role="alert"]');

    foreach ($elements as $element) {
        if (!$element->hasAttribute('aria-live')) {
            $element->setAttribute('aria-live', 'polite');
            $element->setAttribute('aria-atomic', 'true');
        }
    }

    return $this->get_fixed_html($dom, $content);
}
```

### Pattern 2: Element Wrapping

```php
public function fix($content): string
{
    $dom = new \DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);

    $xpath = new \DOMXPath($dom);
    $buttons = $xpath->query('//button[not(@aria-label)]');

    foreach ($buttons as $button) {
        $wrapper = $dom->createElement('span');
        $wrapper->setAttribute('class', 'button-accessible');
        
        $button->parentNode->insertBefore($wrapper, $button);
        $wrapper->appendChild($button);
    }

    return $this->get_fixed_html($dom, $content);
}
```

### Pattern 3: Attribute Modification

```php
public function fix($content): string
{
    $dom = new \DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);

    $xpath = new \DOMXPath($dom);
    $images = $xpath->query('//img[not(@alt)]');

    foreach ($images as $image) {
        $alt = $this->generate_alt_text($image);
        $image->setAttribute('alt', $alt);
    }

    return $this->get_fixed_html($dom, $content);
}

private function generate_alt_text($image): string
{
    $src = $image->getAttribute('src');
    $filename = basename($src, pathinfo($src, PATHINFO_EXTENSION));
    return str_replace(['-', '_'], ' ', $filename);
}
```

### Pattern 4: CSS Injection

```php
public function requires_css(): bool
{
    return true;
}

public function get_css(): string
{
    return '
    /* High contrast mode support */
    .focus-indicator:focus {
        outline: 3px solid;
        outline-offset: 2px;
    }
    
    /* Ensure visible focus indicator */
    @media (prefers-reduced-motion: no-preference) {
        .focus-indicator:focus {
            box-shadow: 0 0 0 3px rgba(0,0,0,0.3);
        }
    }
    ';
}

public function fix($content): string
{
    // Add CSS to head if not present
    if (strpos($content, 'focus-indicator-css') === false) {
        $css = '<style id="focus-indicator-css">' . $this->get_css() . '</style>';
        $content = str_replace('</head>', $css . '</head>', $content);
    }

    return $content;
}
```

### Pattern 5: JavaScript Enhancement

```php
public function requires_javascript(): bool
{
    return true;
}

public function get_javascript(): string
{
    return '
    document.addEventListener("DOMContentLoaded", function() {
        // Focus management
        document.querySelectorAll("[data-focus-trap]").forEach(el => {
            const focusableElements = el.querySelectorAll(
                "button, [href], input, select, textarea, [tabindex]:not([tabindex=\"-1\"])"
            );
            if (focusableElements.length > 0) {
                el.addEventListener("keydown", (e) => {
                    if (e.key === "Tab") {
                        const first = focusableElements[0];
                        const last = focusableElements[focusableElements.length - 1];
                        if (e.shiftKey && document.activeElement === first) {
                            last.focus();
                            e.preventDefault();
                        } else if (!e.shiftKey && document.activeElement === last) {
                            first.focus();
                            e.preventDefault();
                        }
                    }
                });
            }
        });
    });
    ';
}
```

## Testing Fixers

### Create Test Class

```php
class CustomButtonLabelFixerTest extends \WP_UnitTestCase
{
    public function test_adds_aria_label_to_icon_button()
    {
        $fixer = new CustomButtonLabelFixer();
        
        $input = '<button><i class="fa-close"></i></button>';
        $output = $fixer->fix($input);
        
        $this->assertStringContainsString('aria-label="Close"', $output);
    }

    public function test_skips_button_with_text()
    {
        $fixer = new CustomButtonLabelFixer();
        
        $input = '<button>Close</button>';
        $output = $fixer->fix($input);
        
        $this->assertStringNotContainsString('aria-label', $output);
    }

    public function test_skips_existing_aria_label()
    {
        $fixer = new CustomButtonLabelFixer();
        
        $input = '<button aria-label="Custom"><i></i></button>';
        $original = $input;
        $output = $fixer->fix($input);
        
        // Should not change existing aria-label
        $this->assertStringContainsString('aria-label="Custom"', $output);
    }
}
```

### Run Tests

```bash
cd your-plugin/tests
phpunit --bootstrap=bootstrap.php CustomButtonLabelFixerTest.php
```

## Performance Optimization

### Best Practices

1. **Minimize DOM Traversal**
   - Use XPath efficiently
   - Query only what's needed
   - Cache results if possible

2. **Batch Operations**
   - Group similar fixes
   - Modify multiple elements in one pass

3. **Avoid Unnecessary Parsing**
   - Check for issue before fixing
   - Use regex for simple replacements

4. **Limit Scope**
   - Target specific elements
   - Skip processed elements

### Example Optimization

```php
public function fix($content): string
{
    // First check if any issues exist with regex
    if (preg_match('/<button[^>]*>(?!.*<\/button>)/s', $content) === 0) {
        // No empty buttons, skip DOM parsing
        return $content;
    }

    // Only parse if likely to find issues
    return $this->parse_and_fix($content);
}
```

## Debugging

### Enable Debug Mode

```php
define('SLOS_FIXER_DEBUG', true);
```

### Add Debug Output

```php
public function fix($content): string
{
    if (defined('SLOS_FIXER_DEBUG') && SLOS_FIXER_DEBUG) {
        error_log('Fixing with: ' . $this->get_id());
        error_log('Content length: ' . strlen($content));
    }

    // ... fix logic

    if (defined('SLOS_FIXER_DEBUG') && SLOS_FIXER_DEBUG) {
        error_log('Fixed content length: ' . strlen($fixed_content));
    }

    return $fixed_content;
}
```

## Distribution

### Package as Plugin

1. Create plugin folder: `my-slos-fixers/`
2. Create plugin file: `my-slos-fixers.php`
3. Include fixer classes
4. Register in initialization
5. Package as ZIP
6. Distribute via GitHub/WordPress.org

### Example Plugin File

```php
<?php
/**
 * Plugin Name: Custom SLOS Fixers
 * Plugin URI: https://github.com/yourname/custom-slos-fixers
 * Description: Custom accessibility fixers for SLOS
 * Version: 1.0.0
 * Author: Your Name
 */

require_once plugin_dir_path(__FILE__) . 'fixers/CustomButtonLabelFixer.php';

add_filter('slos_accessibility_fixers', function($fixers) {
    $fixers['custom-button-label'] = 'YourNamespace\Accessibility\Fixers\CustomButtonLabelFixer';
    return $fixers;
});
```

## Examples

See the official fixers in:
`includes/Modules/AccessibilityScanner/Fixes/Fixers/`

## Next Steps

1. Identify accessibility issues on your site
2. Create custom fixer for that issue
3. Test thoroughly
4. Register with SLOS
5. Monitor effectiveness
6. Share with community!

## Related

- [REST API Documentation](01-rest-api.md)
- [Hooks & Filters](02-hooks-filters.md)
- [Developer Guide](03-developer-guide.md)
