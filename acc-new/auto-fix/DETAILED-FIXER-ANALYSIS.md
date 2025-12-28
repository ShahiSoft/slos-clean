# Detailed Fixer Analysis by Category

This document provides a line-by-line analysis of each existing fixer, identifying strengths, weaknesses, and specific improvement opportunities.

---

## Image Fixers Analysis

### MissingAltTextFixer ⭐⭐⭐⭐

**Location:** `LinkAndImageFixers.php:25-89`

**Current Implementation:**
```php
public function fix($content) {
    // Uses generate_alt_text() from BaseFixer
    // Extracts filename from src
    // Adds alt="filename-derived-text"
}
```

**Strengths:**
- ✅ Correctly identifies images without alt
- ✅ Falls back to filename extraction
- ✅ Handles various src formats

**Weaknesses:**
- ❌ No AI-based alt text generation
- ❌ Doesn't consider image context
- ❌ Generic alt text from filename often unhelpful

**Improvement Suggestions:**
1. Integrate with AltTextGenerator service
2. Use surrounding text context
3. Check for figure/figcaption associations
4. Consider image dimensions (decorative if tiny)

**Recommended Changes:**
```php
protected function generate_contextual_alt(\DOMElement $img, \DOMXPath $xpath): string {
    // 1. Check for figcaption
    $figure = $xpath->query('ancestor::figure', $img)->item(0);
    if ($figure) {
        $caption = $xpath->query('.//figcaption', $figure)->item(0);
        if ($caption) {
            return trim($caption->textContent);
        }
    }
    
    // 2. Check for nearby text describing image
    $parent = $img->parentNode;
    if ($parent instanceof \DOMElement) {
        $nearby_text = $xpath->query(
            'following-sibling::text()[1] | preceding-sibling::text()[1]',
            $img
        );
        // Analyze for descriptive phrases
    }
    
    // 3. Use AI service if available
    if ($this->alt_generator) {
        return $this->alt_generator->generate($img->getAttribute('src'));
    }
    
    // 4. Fall back to filename
    return $this->generate_alt_text($img->getAttribute('src'));
}
```

---

### EmptyAltTextFixer ⭐⭐⭐⭐

**Location:** `LinkAndImageFixers.php:91-145`

**Current Implementation:**
- Finds `alt=""` images
- Generates alt from filename
- Skips if data-decorative="true"

**Strengths:**
- ✅ Distinguishes empty vs missing alt
- ✅ Respects decorative marking

**Weaknesses:**
- ❌ Doesn't verify if empty alt is intentional (decorative)
- ❌ May add alt to truly decorative images

**Improvement:**
```php
private function is_likely_decorative(\DOMElement $img): bool {
    // Small dimensions
    $width = $img->getAttribute('width');
    $height = $img->getAttribute('height');
    if ($width && $height && $width < 20 && $height < 20) {
        return true;
    }
    
    // Common decorative patterns
    $src = $img->getAttribute('src');
    $decorative_patterns = [
        '/spacer/', '/pixel/', '/dot/', '/line/',
        '/border/', '/shadow/', '/bg-/', '/pattern/',
    ];
    foreach ($decorative_patterns as $pattern) {
        if (preg_match($pattern, $src)) {
            return true;
        }
    }
    
    // Role presentation
    if ($img->getAttribute('role') === 'presentation') {
        return true;
    }
    
    return false;
}
```

---

### DecorativeImageFixer ⭐⭐⭐

**Location:** `LinkAndImageFixers.php:147-200`

**Current Implementation:**
- Adds `role="presentation"` to decorative images
- Sets `alt=""`

**Weaknesses:**
- ❌ Detection heuristics too simplistic
- ❌ Doesn't handle SVG decorative images
- ❌ May miss CSS background images used decoratively

**Improvement:**
```php
private function detect_decorative(\DOMElement $img, \DOMXPath $xpath): bool {
    // 1. Explicit markers
    if ($img->hasAttribute('data-decorative')) return true;
    if ($img->getAttribute('aria-hidden') === 'true') return true;
    
    // 2. Size-based detection
    $style = $img->getAttribute('style');
    if (preg_match('/width\s*:\s*(\d+)px/', $style, $m) && $m[1] < 10) {
        return true;
    }
    
    // 3. Context detection - in CSS-heavy container
    $parent = $img->parentNode;
    if ($parent instanceof \DOMElement) {
        $class = $parent->getAttribute('class');
        if (preg_match('/bg|background|decor|ornament/', $class)) {
            return true;
        }
    }
    
    // 4. Filename patterns
    $src = strtolower($img->getAttribute('src'));
    $decorative_keywords = [
        'spacer', 'blank', 'pixel', 'transparent',
        'divider', 'separator', 'bullet', 'arrow',
        'icon-', 'bg-', 'pattern-', 'texture-',
    ];
    foreach ($decorative_keywords as $keyword) {
        if (strpos($src, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}
```

---

## Link Fixers Analysis

### GenericLinkTextFixer ⭐⭐⭐

**Location:** `LinkAndImageFixers.php:350-420`

**Current Implementation:**
```php
private const GENERIC_TEXTS = [
    'click here', 'here', 'read more', 'more', 'learn more',
    'details', 'link', 'this', 'this link', 'continue',
];

public function fix($content) {
    // Replaces generic text with URL-derived text
}
```

**Weaknesses:**
- ❌ URL-derived text often poor ("example-com")
- ❌ Doesn't use page context
- ❌ Doesn't check for aria-label alternative

**Major Improvement Needed:**
```php
public function fix($content) {
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    $fixed_count = 0;

    foreach ($xpath->query('//a[@href]') as $link) {
        $text = strtolower(trim($link->textContent));
        
        if (!$this->is_generic($text)) {
            continue;
        }
        
        // Already has descriptive label?
        if ($link->hasAttribute('aria-label') || 
            $link->hasAttribute('aria-labelledby')) {
            continue;
        }
        
        // Strategy 1: Use title attribute
        if ($link->hasAttribute('title')) {
            $link->setAttribute('aria-label', $link->getAttribute('title'));
            ++$fixed_count;
            continue;
        }
        
        // Strategy 2: Context from surrounding text
        $context = $this->extract_context($link, $xpath);
        if ($context) {
            $link->setAttribute('aria-label', $context);
            ++$fixed_count;
            continue;
        }
        
        // Strategy 3: Derive from URL path
        $href = $link->getAttribute('href');
        $descriptive = $this->derive_from_url($href);
        if ($descriptive) {
            $link->setAttribute('aria-label', $text . ' about ' . $descriptive);
            ++$fixed_count;
        }
    }

    return [
        'fixed_count' => $fixed_count,
        'content' => $this->dom_to_html($dom),
    ];
}

private function extract_context(\DOMElement $link, \DOMXPath $xpath): ?string {
    // Check preceding heading
    $heading = $xpath->query(
        'preceding::*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6][1]',
        $link
    )->item(0);
    
    if ($heading) {
        $heading_text = trim($heading->textContent);
        if (strlen($heading_text) < 60) {
            return $heading_text;
        }
    }
    
    // Check parent paragraph topic
    $parent = $link->parentNode;
    if ($parent instanceof \DOMElement && strtolower($parent->tagName) === 'p') {
        // Extract first meaningful phrase
        $para_text = $parent->textContent;
        if (preg_match('/^([^.!?]{10,50})[.!?]/', $para_text, $m)) {
            return 'about ' . trim($m[1]);
        }
    }
    
    return null;
}

private function derive_from_url(string $url): ?string {
    $parsed = parse_url($url);
    $path = $parsed['path'] ?? '';
    
    // Clean path
    $path = preg_replace('/\.[a-z]+$/', '', $path); // Remove extension
    $path = str_replace(['/', '-', '_'], ' ', $path);
    $path = trim($path);
    
    // Skip if too generic
    if (strlen($path) < 3 || preg_match('/^(index|page|post|article)$/i', $path)) {
        return null;
    }
    
    return ucwords($path);
}
```

---

### NewWindowLinkFixer ⭐⭐⭐⭐⭐

**Location:** `LinkAndImageFixers.php:422-480`

**Current Implementation:**
- Detects `target="_blank"`
- Adds "(opens in new window)" to link text
- Adds `rel="noopener noreferrer"`

**Strengths:**
- ✅ Comprehensive detection
- ✅ Proper security attributes
- ✅ Clear notification text

**Minor Improvements:**
```php
// Add screen-reader-only notification option
private function add_new_window_indicator(\DOMElement $link): void {
    // Check if already indicated
    $text = $link->textContent;
    if (stripos($text, 'new window') !== false || 
        stripos($text, 'new tab') !== false) {
        return;
    }
    
    // Option 1: Visible text (current)
    // Option 2: Screen reader only
    $indicator = $link->ownerDocument->createElement('span');
    $indicator->setAttribute('class', 'screen-reader-text');
    $indicator->textContent = ' (opens in new window)';
    $link->appendChild($indicator);
    
    // Also add visual icon
    $icon = $link->ownerDocument->createElement('span');
    $icon->setAttribute('class', 'slos-new-window-icon');
    $icon->setAttribute('aria-hidden', 'true');
    $icon->textContent = ' ↗';
    $link->appendChild($icon);
}
```

---

## Form Fixers Analysis

### MissingFormLabelFixer ⭐⭐⭐⭐

**Location:** `FormFixers.php:25-120`

**Current Implementation:**
- Finds inputs without associated labels
- Adds `aria-label` from placeholder or name
- Creates visible labels if possible

**Strengths:**
- ✅ Multiple fallback strategies
- ✅ Handles various input types
- ✅ Respects existing associations

**Weaknesses:**
- ❌ aria-label from name often poor ("first_name" → "first name")
- ❌ Doesn't create actual `<label>` elements
- ❌ Placeholder-derived labels not ideal

**Improvement:**
```php
private function generate_label_text(string $name): string {
    // Convert technical names to human readable
    $label = str_replace(['_', '-', '.'], ' ', $name);
    
    // Handle camelCase
    $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $label);
    
    // Common abbreviation expansions
    $expansions = [
        'fname' => 'First Name',
        'lname' => 'Last Name',
        'dob' => 'Date of Birth',
        'tel' => 'Telephone',
        'addr' => 'Address',
        'apt' => 'Apartment',
        'num' => 'Number',
        'qty' => 'Quantity',
        'pwd' => 'Password',
        'pw' => 'Password',
        'cc' => 'Credit Card',
        'cvv' => 'Security Code',
        'exp' => 'Expiration',
        'msg' => 'Message',
        'desc' => 'Description',
    ];
    
    foreach ($expansions as $abbr => $full) {
        $label = preg_replace('/\b' . preg_quote($abbr, '/') . '\b/i', $full, $label);
    }
    
    return ucwords(trim($label));
}

// Create actual visible label when possible
private function create_label(\DOMElement $input, \DOMDocument $doc): \DOMElement {
    $label = $doc->createElement('label');
    
    // Ensure input has ID
    $id = $input->getAttribute('id');
    if (!$id) {
        $id = 'slos-field-' . uniqid();
        $input->setAttribute('id', $id);
    }
    
    $label->setAttribute('for', $id);
    $label->textContent = $this->generate_label_text(
        $input->getAttribute('name') ?: $input->getAttribute('placeholder') ?: 'Field'
    );
    
    // Add required indicator if needed
    if ($input->hasAttribute('required')) {
        $required = $doc->createElement('span');
        $required->setAttribute('aria-hidden', 'true');
        $required->textContent = ' *';
        $label->appendChild($required);
        
        $sr = $doc->createElement('span');
        $sr->setAttribute('class', 'screen-reader-text');
        $sr->textContent = ' (required)';
        $label->appendChild($sr);
    }
    
    return $label;
}
```

---

### AutocompleteFixer ⭐⭐⭐⭐

**Location:** `FormFixers.php:180-260`

**Current Implementation:**
- Pattern matches field names to autocomplete values
- Adds appropriate `autocomplete` attribute

**Strengths:**
- ✅ Comprehensive pattern matching
- ✅ Covers common field types
- ✅ WCAG 1.3.5 compliance

**Add More Patterns:**
```php
private const AUTOCOMPLETE_PATTERNS = [
    // Personal
    '/^(full[_-]?)?name$/i' => 'name',
    '/^(first[_-]?name|fname|given[_-]?name)$/i' => 'given-name',
    '/^(last[_-]?name|lname|family[_-]?name|surname)$/i' => 'family-name',
    '/^(middle[_-]?name|mname)$/i' => 'additional-name',
    '/^(nick[_-]?name|alias)$/i' => 'nickname',
    '/^(honorific|prefix|title)$/i' => 'honorific-prefix',
    '/^(suffix)$/i' => 'honorific-suffix',
    
    // Contact
    '/^(email|e[_-]?mail)$/i' => 'email',
    '/^(phone|tel|telephone|mobile|cell)$/i' => 'tel',
    '/^(fax)$/i' => 'tel',
    
    // Address  
    '/^(address|street|addr)[_-]?(1|line[_-]?1)?$/i' => 'address-line1',
    '/^(address|street|addr)[_-]?(2|line[_-]?2)$/i' => 'address-line2',
    '/^(city|town|locality)$/i' => 'address-level2',
    '/^(state|province|region)$/i' => 'address-level1',
    '/^(zip|postal|postcode|zip[_-]?code)$/i' => 'postal-code',
    '/^(country)$/i' => 'country-name',
    
    // Account
    '/^(user[_-]?name|login|user[_-]?id|account)$/i' => 'username',
    '/^(password|pwd|pass)$/i' => 'current-password',
    '/^(new[_-]?password|new[_-]?pwd)$/i' => 'new-password',
    
    // Payment
    '/^(cc[_-]?(num|number)?|card[_-]?number)$/i' => 'cc-number',
    '/^(cc[_-]?name|card[_-]?holder|cardholder)$/i' => 'cc-name',
    '/^(cc[_-]?exp|expir|card[_-]?exp)$/i' => 'cc-exp',
    '/^(cc[_-]?exp[_-]?(month|mm))$/i' => 'cc-exp-month',
    '/^(cc[_-]?exp[_-]?(year|yy|yyyy))$/i' => 'cc-exp-year',
    '/^(cvv|cvc|csc|security[_-]?code)$/i' => 'cc-csc',
    '/^(cc[_-]?type|card[_-]?type)$/i' => 'cc-type',
    
    // Personal info
    '/^(bday|birthday|dob|birth[_-]?date)$/i' => 'bday',
    '/^(bday[_-]?day|birth[_-]?day)$/i' => 'bday-day',
    '/^(bday[_-]?month|birth[_-]?month)$/i' => 'bday-month',
    '/^(bday[_-]?year|birth[_-]?year)$/i' => 'bday-year',
    '/^(sex|gender)$/i' => 'sex',
    '/^(url|website|homepage|web)$/i' => 'url',
    '/^(photo|avatar|picture|image)$/i' => 'photo',
    
    // Organization
    '/^(org|organization|company|employer)$/i' => 'organization',
    '/^(job[_-]?title|position|title)$/i' => 'organization-title',
    
    // Language/Locale
    '/^(language|lang|locale)$/i' => 'language',
    
    // One-time codes
    '/^(otp|code|verification|token)$/i' => 'one-time-code',
];
```

---

## Heading Fixers Analysis

### SkippedHeadingLevelFixer ⭐⭐⭐⭐

**Location:** `HeadingFixers.php:25-90`

**Current Implementation:**
- Detects h1 → h3 skips
- Adjusts heading levels

**Strengths:**
- ✅ Correct DOM traversal
- ✅ Maintains hierarchy

**Weaknesses:**
- ❌ May break intentional design
- ❌ Doesn't consider sectioning elements
- ❌ May need to adjust multiple headings

**Improvement:**
```php
public function fix($content) {
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    $fixed_count = 0;

    // Build heading map with sectioning context
    $headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');
    
    $expected_level = 1;
    $section_stack = [1]; // Track nested sections

    foreach ($headings as $heading) {
        $current_level = (int) substr($heading->tagName, 1);
        
        // Check sectioning context
        $section = $this->get_sectioning_ancestor($heading, $xpath);
        if ($section) {
            // Reset expected based on section depth
            $section_depth = $this->get_section_depth($section, $xpath);
            $expected_level = min($section_depth + 1, 6);
        }
        
        // Check for skip
        if ($current_level > $expected_level + 1) {
            // Calculate correct level
            $correct_level = $expected_level + 1;
            
            // Create new heading element
            $new_heading = $dom->createElement('h' . $correct_level);
            
            // Copy attributes and content
            foreach ($heading->attributes as $attr) {
                $new_heading->setAttribute($attr->name, $attr->value);
            }
            
            while ($heading->firstChild) {
                $new_heading->appendChild($heading->firstChild);
            }
            
            // Add data attribute noting original level
            $new_heading->setAttribute('data-original-level', $current_level);
            
            $heading->parentNode->replaceChild($new_heading, $heading);
            ++$fixed_count;
            
            $expected_level = $correct_level;
        } else {
            $expected_level = $current_level;
        }
    }

    return [
        'fixed_count' => $fixed_count,
        'content' => $this->dom_to_html($dom),
    ];
}

private function get_sectioning_ancestor(\DOMElement $el, \DOMXPath $xpath): ?\DOMElement {
    $sectioning = ['article', 'aside', 'nav', 'section'];
    
    $parent = $el->parentNode;
    while ($parent instanceof \DOMElement) {
        if (in_array(strtolower($parent->tagName), $sectioning)) {
            return $parent;
        }
        $parent = $parent->parentNode;
    }
    
    return null;
}
```

---

## ARIA Fixers Analysis

### AriaRoleFixer ⭐⭐⭐⭐

**Location:** `AriaAndSemanticFixers.php:25-100`

**Current Implementation:**
- Validates role values
- Removes invalid roles
- Adds implicit roles to semantic elements

**Strengths:**
- ✅ Comprehensive role validation
- ✅ Handles custom elements

**Weaknesses:**
- ❌ Doesn't fix role conflicts
- ❌ Missing some newer ARIA 1.2 roles

**Add Role Conflicts Detection:**
```php
private const ROLE_CONFLICTS = [
    // Elements that should never have certain roles
    'a' => ['button'], // Links shouldn't be buttons
    'button' => ['link'], // Buttons shouldn't be links
    'input' => ['button', 'link'], // Form inputs have implicit roles
    'img' => ['presentation'], // Only if alt="" or decorative
    'table' => ['presentation'], // Only if truly layout
];

private const ARIA_1_2_ROLES = [
    // Document structure
    'document', 'article', 'comment', 'definition', 'deletion',
    'emphasis', 'figure', 'generic', 'group', 'heading', 'img',
    'insertion', 'list', 'listitem', 'mark', 'math', 'meter',
    'note', 'paragraph', 'presentation', 'strong', 'subscript',
    'superscript', 'term', 'time', 'tooltip',
    
    // Widgets
    'button', 'checkbox', 'combobox', 'grid', 'gridcell', 'link',
    'listbox', 'menu', 'menubar', 'menuitem', 'menuitemcheckbox',
    'menuitemradio', 'option', 'progressbar', 'radio', 'radiogroup',
    'scrollbar', 'searchbox', 'separator', 'slider', 'spinbutton',
    'switch', 'tab', 'tablist', 'tabpanel', 'textbox', 'tree',
    'treegrid', 'treeitem',
    
    // Landmarks
    'banner', 'complementary', 'contentinfo', 'form', 'main',
    'navigation', 'region', 'search',
    
    // Live regions
    'alert', 'alertdialog', 'dialog', 'log', 'marquee', 'status', 'timer',
    
    // Window
    'alertdialog', 'dialog',
];
```

---

### SemanticHtmlFixer ⭐⭐⭐

**Location:** `AriaAndSemanticFixers.php:200-280`

**Current Implementation:**
- Converts `<div role="navigation">` to `<nav>`
- Converts other role'd divs to semantic elements

**Weaknesses:**
- ❌ Doesn't handle all semantic mappings
- ❌ May break CSS selectors
- ❌ Doesn't preserve custom attributes

**Complete Semantic Mapping:**
```php
private const SEMANTIC_MAPPINGS = [
    // Role → Element
    'navigation' => 'nav',
    'banner' => 'header',
    'contentinfo' => 'footer',
    'main' => 'main',
    'complementary' => 'aside',
    'article' => 'article',
    'region' => 'section',
    'search' => 'search', // HTML5.2
    'form' => 'form',
    'button' => 'button',
    'link' => 'a',
    'list' => 'ul',
    'listitem' => 'li',
    'heading' => null, // Requires aria-level
    'figure' => 'figure',
    'img' => 'img',
    'table' => 'table',
    'row' => 'tr',
    'cell' => 'td',
    'columnheader' => 'th',
    'rowheader' => 'th',
];

public function fix($content) {
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    $fixed_count = 0;

    foreach (self::SEMANTIC_MAPPINGS as $role => $element) {
        if (!$element) continue;
        
        $divs = $xpath->query("//div[@role='{$role}']|//span[@role='{$role}']");
        
        foreach ($divs as $div) {
            // Skip if conversion would be problematic
            if ($this->should_skip_conversion($div, $element)) {
                continue;
            }
            
            $semantic = $this->convert_element($div, $element);
            
            // Remove redundant role
            $semantic->removeAttribute('role');
            
            $div->parentNode->replaceChild($semantic, $div);
            ++$fixed_count;
        }
    }

    return [
        'fixed_count' => $fixed_count,
        'content' => $this->dom_to_html($dom),
    ];
}

private function should_skip_conversion(\DOMElement $el, string $target): bool {
    // Don't convert if it has complex styling that might break
    $class = $el->getAttribute('class');
    
    // Check for grid/flex layouts that might depend on div
    if (preg_match('/\b(grid|flex|col-|row-)\b/', $class)) {
        // Still convert, but preserve the class
        return false;
    }
    
    // Don't convert nested landmarks
    if (in_array($target, ['nav', 'main', 'header', 'footer', 'aside'])) {
        $parent = $el->parentNode;
        while ($parent instanceof \DOMElement) {
            if (in_array(strtolower($parent->tagName), ['nav', 'main', 'header', 'footer', 'aside'])) {
                return true; // Skip nested landmarks
            }
            $parent = $parent->parentNode;
        }
    }
    
    return false;
}

private function convert_element(\DOMElement $old, string $new_tag): \DOMElement {
    $new = $old->ownerDocument->createElement($new_tag);
    
    // Copy all attributes
    foreach ($old->attributes as $attr) {
        $new->setAttribute($attr->name, $attr->value);
    }
    
    // Copy all children
    while ($old->firstChild) {
        $new->appendChild($old->firstChild);
    }
    
    return $new;
}
```

---

## Summary Statistics

| Category | Total Fixers | Fully Working | Needs Enhancement | Returns 0 |
|----------|-------------|---------------|-------------------|-----------|
| Image | 10 | 7 | 2 | 1 |
| Link | 7 | 5 | 2 | 0 |
| Heading | 5 | 4 | 1 | 0 |
| Form | 12 | 10 | 2 | 0 |
| Table | 5 | 5 | 0 | 0 |
| ARIA | 9 | 6 | 2 | 1 |
| Interactivity | 11 | 3 | 3 | 5 |
| **TOTAL** | **59** | **40** | **12** | **7** |

---

*Detailed analysis complete. Priority for enhancement: Interactivity fixers, then ARIA fixers, then Image fixers.*
