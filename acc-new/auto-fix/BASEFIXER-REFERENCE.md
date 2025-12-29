# BaseFixer Class Reference

This document provides comprehensive documentation of the `BaseFixer` abstract class methods and their usage patterns.

## Class Overview

**Namespace:** `ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers`  
**File:** `includes/Modules/AccessibilityScanner/Fixes/Fixers/BaseFixer.php`  
**Type:** Abstract Class

All content-aware fixers extend `BaseFixer` and must implement the abstract methods.

---

## Abstract Methods (Required)

### `get_id(): string`

Returns the unique identifier for this fixer. Must match the corresponding checker ID in `FixerRegistry`.

```php
public function get_id(): string {
    return 'focus-indicator';
}
```

**Registry Match:** The ID returned must match a key in `FixerRegistry::$registry` or `FixerRegistry::$aliases`.

---

### `get_description(): string`

Returns a human-readable description of what this fixer does.

```php
public function get_description(): string {
    return 'Adds visible focus indicators to interactive elements';
}
```

---

### `fix($content): array`

The main fix method. Takes HTML content and returns an array with:
- `fixed_count` (int): Number of fixes applied
- `content` (string): Modified HTML content

```php
public function fix($content): array {
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    $fixed_count = 0;
    
    // ... perform fixes ...
    
    return [
        'fixed_count' => $fixed_count,
        'content' => $this->dom_to_html($dom),
    ];
}
```

---

## Protected Helper Methods

### `get_dom(string $content): \DOMDocument`

Converts HTML content to a DOMDocument for manipulation.

**Parameters:**
- `$content` (string): Raw HTML content

**Returns:** `\DOMDocument` instance

**Behavior:**
- Wraps content in full HTML document structure
- Suppresses XML parsing warnings for malformed HTML
- Ensures body element exists
- Uses UTF-8 encoding

**Usage:**
```php
$dom = $this->get_dom($content);
$xpath = new \DOMXPath($dom);

// Find elements
$elements = $xpath->query('//button[@style[contains(., "outline")]]');

foreach ($elements as $element) {
    // Modify element
    $element->setAttribute('data-fixed', 'true');
}
```

**Note:** The document is wrapped in `<!DOCTYPE html><html><head>...</head><body>...</body></html>`. The `dom_to_html()` method extracts only the body content.

---

### `dom_to_html(\DOMDocument $dom): string`

Converts a DOMDocument back to HTML string, extracting only the body content.

**Parameters:**
- `$dom` (\DOMDocument): The modified DOM document

**Returns:** `string` - HTML content (body children only)

**Usage:**
```php
$dom = $this->get_dom($content);
// ... make modifications ...
return $this->dom_to_html($dom);
```

---

### `generate_alt_text(string $image_src): string`

Generates basic alt text from an image filename.

**Parameters:**
- `$image_src` (string): Image source URL/path

**Returns:** `string` - Generated alt text

**Behavior:**
1. Extracts basename from path
2. Removes file extension
3. Replaces `-` and `_` with spaces
4. Capitalizes first letter

**Example:**
```php
$alt = $this->generate_alt_text('/images/hero-banner.jpg');
// Returns: "Hero banner"
```

**Note:** This is a fallback method. Enhanced fixers should use context-aware alt text generation.

---

## Common DOM Manipulation Patterns

### Pattern 1: Finding Elements by XPath

```php
$dom = $this->get_dom($content);
$xpath = new \DOMXPath($dom);

// Find all buttons
$buttons = $xpath->query('//button');

// Find elements with specific attribute
$elements = $xpath->query('//*[@style]');

// Find elements with attribute containing value
$outlineNone = $xpath->query('//*[@style[contains(., "outline")]]');

// Find by class (partial match)
$toggles = $xpath->query('//*[contains(@class, "toggle")]');
```

### Pattern 2: Modifying Attributes

```php
foreach ($elements as $element) {
    // Set attribute
    $element->setAttribute('aria-pressed', 'false');
    
    // Get attribute
    $currentClass = $element->getAttribute('class');
    
    // Check if attribute exists
    if ($element->hasAttribute('aria-expanded')) {
        // ...
    }
    
    // Remove attribute
    $element->removeAttribute('tabindex');
    
    // Append to class
    $element->setAttribute('class', trim($currentClass . ' slos-fixed'));
}
```

### Pattern 3: Modifying Inline Styles

```php
foreach ($elements as $element) {
    $style = $element->getAttribute('style');
    
    // Replace style property
    $style = preg_replace(
        '/outline\s*:\s*(none|0)[^;]*(;|$)/i',
        '',
        $style
    );
    
    // Add style property
    $style = trim($style, '; ') . '; min-width: 44px; min-height: 44px;';
    
    $element->setAttribute('style', $style);
}
```

### Pattern 4: Creating New Elements

```php
$dom = $this->get_dom($content);

// Create element
$span = $dom->createElement('span');
$span->setAttribute('class', 'screen-reader-text');
$span->textContent = ' (opens in new window)';

// Append to parent
$link->appendChild($span);

// Insert before
$parent->insertBefore($newElement, $existingElement);

// Replace element
$parent->replaceChild($newElement, $oldElement);
```

### Pattern 5: Finding Parent/Ancestor Elements

```php
$xpath = new \DOMXPath($dom);

// Get parent
$parent = $element->parentNode;

// Find ancestor by tag
$ancestor = $xpath->query('ancestor::form', $element)->item(0);

// Find ancestor by role
$landmark = $xpath->query('ancestor::*[@role="main"]', $element)->item(0);
```

### Pattern 6: Working with Style Tags

```php
$styles = $dom->getElementsByTagName('style');

foreach ($styles as $styleTag) {
    $css = $styleTag->textContent;
    
    // Modify CSS
    $css = preg_replace(
        '/:focus\s*\{[^}]*outline\s*:\s*(none|0)[^}]*/i',
        ':focus { outline: 2px solid #005fcc; }',
        $css
    );
    
    $styleTag->textContent = $css;
}
```

### Pattern 7: Injecting New Style Tag

```php
$head = $dom->getElementsByTagName('head')->item(0);
if ($head) {
    $style = $dom->createElement('style');
    $style->setAttribute('data-slos-injected', 'true');
    $style->textContent = '
        .slos-focus-visible:focus {
            outline: 2px solid #005fcc !important;
            outline-offset: 2px;
        }
    ';
    $head->appendChild($style);
}
```

---

## Return Value Structure

All `fix()` methods must return an array with this structure:

```php
return [
    'fixed_count' => int,    // Number of fixes applied (0 if none)
    'content'     => string, // Modified HTML content
];
```

**Important:** 
- Return `fixed_count: 0` if no fixes were needed or possible
- Always return the content, even if unmodified
- The `content` should be the result of `dom_to_html($dom)`

---

## Error Handling

Fixers should be defensive and never throw exceptions:

```php
public function fix($content): array {
    try {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;
        
        // ... fix logic ...
        
        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    } catch (\Exception $e) {
        // Log error but don't break
        error_log('SLOS Fixer Error [' . $this->get_id() . ']: ' . $e->getMessage());
        
        // Return original content unchanged
        return [
            'fixed_count' => 0,
            'content' => $content,
        ];
    }
}
```

---

## Best Practices

1. **Be Conservative:** Only fix issues you're confident about
2. **Add Data Attributes:** Mark fixed elements with `data-slos-fixed="true"` for debugging
3. **Preserve Original:** Store original values in `data-original-*` attributes when replacing
4. **Validate Fixes:** Check that your fix won't break other accessibility
5. **Test Edge Cases:** Empty content, malformed HTML, deeply nested elements
6. **Performance:** Minimize DOM traversals, batch similar operations

---

## Example: Complete Fixer Implementation

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class ExampleFixer extends BaseFixer {
    
    public function get_id(): string {
        return 'example-issue';
    }
    
    public function get_description(): string {
        return 'Fixes example accessibility issues';
    }
    
    public function fix($content): array {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;
        
        // Find problematic elements
        $elements = $xpath->query('//div[@data-problem="true"]');
        
        foreach ($elements as $element) {
            // Apply fix
            $element->setAttribute('aria-label', 'Fixed label');
            $element->setAttribute('data-slos-fixed', 'true');
            ++$fixed_count;
        }
        
        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }
}
```

---

*Last updated: December 29, 2025*
