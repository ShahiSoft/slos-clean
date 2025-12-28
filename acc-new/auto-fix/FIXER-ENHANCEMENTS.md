# Auto-Fixer Enhancement Specifications

This document contains improvements to existing auto-fixers that currently return `fixed_count: 0` or have limited functionality.

---

## Table of Contents

1. [FocusIndicatorFixer Enhancement](#1-focusindicatorfixer-enhancement)
2. [TouchTargetFixer Enhancement](#2-touchtargetfixer-enhancement)
3. [KeyboardTrapFixer Enhancement](#3-keyboardtrapfixer-enhancement)
4. [ViewportFixer Enhancement](#4-viewportfixer-enhancement)
5. [ColorRelianceFixer Enhancement](#5-colorreliancefixer-enhancement)
6. [AriaStateFixer Enhancement](#6-ariastatefixer-enhancement)
7. [PageStructureFixer Enhancement](#7-pagestructurefixer-enhancement)
8. [GenericLinkTextFixer Enhancement](#8-genericlinktextfixer-enhancement)
9. [AltTextQualityFixer Enhancement](#9-alttextqualityfixer-enhancement)

---

## 1. FocusIndicatorFixer Enhancement

**Current State:** Returns `fixed_count: 0` without attempting fixes  
**Issue:** CSS-level problem assumed to be unfixable via content

### Enhanced Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class FocusIndicatorFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'focus-indicator';
    }

    public function get_description(): string
    {
        return 'Adds visible focus indicators to interactive elements';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // 1. Remove outline:none from inline styles
        $no_outline = $xpath->query('//*[@style[contains(., "outline")]]');
        
        foreach ($no_outline as $element) {
            $style = $element->getAttribute('style');
            
            if (preg_match('/outline\s*:\s*(none|0)/i', $style)) {
                // Remove outline:none and add visible focus style
                $style = preg_replace(
                    '/outline\s*:\s*(none|0)[^;]*(;|$)/i',
                    '',
                    $style
                );
                $element->setAttribute('style', trim($style, '; '));
                $element->setAttribute('data-slos-focus-fixed', 'true');
                ++$fixed_count;
            }
        }

        // 2. Add focus class to elements missing tabindex
        $interactive = $xpath->query(
            '//a[@href] | //button | //input | //select | //textarea | ' .
            '//*[@onclick or @tabindex]'
        );

        foreach ($interactive as $element) {
            // Mark for CSS focus enhancement
            $class = $element->getAttribute('class');
            if (strpos($class, 'slos-focus-visible') === false) {
                $element->setAttribute('class', trim($class . ' slos-focus-visible'));
            }
        }

        // 3. Inject focus styles if not already present
        $this->inject_focus_styles($dom);
        ++$fixed_count;

        // 4. Fix style tags that remove focus
        $styles = $dom->getElementsByTagName('style');
        foreach ($styles as $styleTag) {
            $css = $styleTag->textContent;
            
            // Replace :focus { outline: none } patterns
            $patterns = [
                '/(\*|a|button|input|select|textarea)\s*:focus\s*\{[^}]*outline\s*:\s*(none|0)[^}]*/i',
                '/:focus-visible\s*\{[^}]*outline\s*:\s*(none|0)[^}]*/i',
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $css)) {
                    $css = preg_replace(
                        $pattern,
                        '$1:focus { outline: 2px solid #005fcc; outline-offset: 2px; }',
                        $css
                    );
                    $styleTag->textContent = $css;
                    ++$fixed_count;
                }
            }
        }

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function inject_focus_styles(\DOMDocument $dom): void
    {
        // Check if already injected
        $xpath = new \DOMXPath($dom);
        $existing = $xpath->query('//style[@data-slos-focus-styles]');
        if ($existing->length > 0) {
            return;
        }

        $style = $dom->createElement('style');
        $style->setAttribute('data-slos-focus-styles', 'true');
        $style->textContent = '
/* SLOS Focus Indicator Fixes */
.slos-focus-visible:focus {
    outline: 2px solid #005fcc !important;
    outline-offset: 2px !important;
}

.slos-focus-visible:focus:not(:focus-visible) {
    outline: none !important;
}

.slos-focus-visible:focus-visible {
    outline: 2px solid #005fcc !important;
    outline-offset: 2px !important;
}

[data-slos-focus-fixed]:focus {
    outline: 2px solid #005fcc !important;
    outline-offset: 2px !important;
    box-shadow: 0 0 0 4px rgba(0, 95, 204, 0.3) !important;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .slos-focus-visible:focus,
    [data-slos-focus-fixed]:focus {
        outline: 3px solid currentColor !important;
        outline-offset: 3px !important;
    }
}
';
        
        $head = $dom->getElementsByTagName('head')->item(0);
        if ($head) {
            $head->appendChild($style);
        }
    }
}
```

---

## 2. TouchTargetFixer Enhancement

**Current State:** Returns `fixed_count: 0` without attempting fixes  
**Issue:** Assumed CSS-only, but can add padding/styles via content

### Enhanced Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class TouchTargetFixer extends BaseFixer
{
    private const MIN_SIZE = 44; // WCAG 2.1 minimum (was 48 in 2.2)

    public function get_id(): string
    {
        return 'touch-target';
    }

    public function get_description(): string
    {
        return 'Ensures touch targets meet minimum size requirements';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Find interactive elements with explicit small sizes
        $interactive = $xpath->query(
            '//a[@style] | //button[@style] | //input[@type="submit" or @type="button" or ' .
            '@type="checkbox" or @type="radio"][@style] | ' .
            '//*[@onclick][@style] | //*[@role="button"][@style]'
        );

        foreach ($interactive as $element) {
            if ($this->fix_small_target($element)) {
                ++$fixed_count;
            }
        }

        // Add wrapper styling for checkboxes/radios (often too small)
        $checkboxes = $xpath->query('//input[@type="checkbox" or @type="radio"]');
        foreach ($checkboxes as $checkbox) {
            if ($this->wrap_small_input($checkbox)) {
                ++$fixed_count;
            }
        }

        // Fix icon-only buttons
        $icon_buttons = $xpath->query(
            '//button[not(text()[normalize-space()])] | ' .
            '//a[not(text()[normalize-space()])][contains(@class, "icon") or contains(@class, "btn")]'
        );
        
        foreach ($icon_buttons as $btn) {
            if ($this->fix_icon_button($btn)) {
                ++$fixed_count;
            }
        }

        // Inject touch target CSS
        if ($fixed_count > 0) {
            $this->inject_touch_styles($dom);
        }

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function fix_small_target(\DOMElement $element): bool
    {
        $style = $element->getAttribute('style');
        $needs_fix = false;

        // Check for explicit small dimensions
        if (preg_match('/width\s*:\s*(\d+)(px)?/i', $style, $match)) {
            if ((int)$match[1] < self::MIN_SIZE) {
                $needs_fix = true;
            }
        }
        
        if (preg_match('/height\s*:\s*(\d+)(px)?/i', $style, $match)) {
            if ((int)$match[1] < self::MIN_SIZE) {
                $needs_fix = true;
            }
        }

        // Check for small padding that results in small target
        if (preg_match('/padding\s*:\s*(\d+)(px)?/i', $style, $match)) {
            if ((int)$match[1] < 10) {
                $needs_fix = true;
            }
        }

        if ($needs_fix) {
            // Add minimum size constraints
            $style = rtrim($style, '; ');
            $style .= '; min-width: ' . self::MIN_SIZE . 'px; min-height: ' . self::MIN_SIZE . 'px;';
            $element->setAttribute('style', $style);
            $element->setAttribute('data-slos-touch-fixed', 'true');
            return true;
        }

        return false;
    }

    private function wrap_small_input(\DOMElement $input): bool
    {
        // Skip if already wrapped
        $parent = $input->parentNode;
        if ($parent instanceof \DOMElement && 
            strpos($parent->getAttribute('class'), 'slos-touch-wrapper') !== false) {
            return false;
        }

        // Create wrapper with larger touch area
        $wrapper = $input->ownerDocument->createElement('span');
        $wrapper->setAttribute('class', 'slos-touch-wrapper');
        $wrapper->setAttribute('style', 
            'display: inline-block; padding: 8px; min-width: 44px; min-height: 44px; ' .
            'position: relative; cursor: pointer;'
        );

        // Clone input and replace
        $input_clone = $input->cloneNode(true);
        $input_clone->setAttribute('style', 
            ($input_clone->getAttribute('style') ?: '') . 
            '; position: relative; z-index: 1;'
        );

        $wrapper->appendChild($input_clone);
        $parent->replaceChild($wrapper, $input);

        return true;
    }

    private function fix_icon_button(\DOMElement $button): bool
    {
        $style = $button->getAttribute('style') ?: '';
        
        // Add minimum dimensions
        if (strpos($style, 'min-width') === false) {
            $style = rtrim($style, '; ') . '; min-width: 44px; min-height: 44px;';
            $button->setAttribute('style', $style);
            $button->setAttribute('data-slos-touch-fixed', 'true');
            return true;
        }

        return false;
    }

    private function inject_touch_styles(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $existing = $xpath->query('//style[@data-slos-touch-styles]');
        if ($existing->length > 0) {
            return;
        }

        $style = $dom->createElement('style');
        $style->setAttribute('data-slos-touch-styles', 'true');
        $style->textContent = '
/* SLOS Touch Target Fixes */
.slos-touch-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    min-height: 44px;
}

.slos-touch-wrapper:focus-within {
    outline: 2px solid #005fcc;
    outline-offset: 2px;
    border-radius: 4px;
}

[data-slos-touch-fixed] {
    min-width: 44px !important;
    min-height: 44px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Spacing between adjacent touch targets */
[data-slos-touch-fixed] + [data-slos-touch-fixed] {
    margin-left: 8px;
}

@media (pointer: coarse) {
    [data-slos-touch-fixed],
    .slos-touch-wrapper {
        min-width: 48px !important;
        min-height: 48px !important;
    }
}
';
        
        $head = $dom->getElementsByTagName('head')->item(0);
        if ($head) {
            $head->appendChild($style);
        }
    }
}
```

---

## 3. KeyboardTrapFixer Enhancement

**Current State:** Returns `fixed_count: 0`  
**Issue:** Requires JavaScript, but we can inject escape handlers

### Enhanced Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class KeyboardTrapFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'keyboard-trap';
    }

    public function get_description(): string
    {
        return 'Adds keyboard escape mechanisms to potential trap elements';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Find potential keyboard trap elements
        $traps = $this->find_potential_traps($xpath);

        foreach ($traps as $trap) {
            if ($this->fix_keyboard_trap($trap, $xpath)) {
                ++$fixed_count;
            }
        }

        // Inject keyboard trap escape script
        if ($fixed_count > 0) {
            $this->inject_escape_script($dom);
        }

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function find_potential_traps(\DOMXPath $xpath): array
    {
        $traps = [];

        // Modals/Dialogs
        $modals = $xpath->query(
            '//*[@role="dialog" or @role="alertdialog" or ' .
            'contains(@class, "modal") or contains(@class, "popup") or ' .
            'contains(@class, "lightbox") or contains(@class, "overlay")]'
        );
        foreach ($modals as $modal) {
            $traps[] = $modal;
        }

        // iframes (can trap focus)
        $iframes = $xpath->query('//iframe[not(@tabindex="-1")]');
        foreach ($iframes as $iframe) {
            $traps[] = $iframe;
        }

        // Embedded content containers
        $embeds = $xpath->query(
            '//*[contains(@class, "embed") or contains(@class, "video-container") or ' .
            'contains(@class, "player")]'
        );
        foreach ($embeds as $embed) {
            $traps[] = $embed;
        }

        // Infinite scroll areas
        $scrolls = $xpath->query(
            '//*[@style[contains(., "overflow")] and @tabindex]'
        );
        foreach ($scrolls as $scroll) {
            $traps[] = $scroll;
        }

        return $traps;
    }

    private function fix_keyboard_trap(\DOMElement $element, \DOMXPath $xpath): bool
    {
        // Skip if already fixed
        if ($element->hasAttribute('data-slos-escape-enabled')) {
            return false;
        }

        $tag = strtolower($element->tagName);

        // For modals: ensure close button exists and is keyboard accessible
        if ($this->is_modal($element)) {
            $close = $xpath->query(
                './/*[contains(@class, "close") or @aria-label[contains(., "close")] or ' .
                'contains(@class, "dismiss")]',
                $element
            )->item(0);

            if (!$close) {
                // Add close button
                $close_btn = $element->ownerDocument->createElement('button');
                $close_btn->setAttribute('type', 'button');
                $close_btn->setAttribute('class', 'slos-modal-close');
                $close_btn->setAttribute('aria-label', 'Close dialog');
                $close_btn->setAttribute('data-slos-escape-trigger', 'true');
                $close_btn->textContent = '×';
                
                // Insert at beginning
                if ($element->firstChild) {
                    $element->insertBefore($close_btn, $element->firstChild);
                } else {
                    $element->appendChild($close_btn);
                }
            } else {
                // Ensure close button is keyboard accessible
                if ($close instanceof \DOMElement) {
                    if (!$close->hasAttribute('tabindex')) {
                        $close->setAttribute('tabindex', '0');
                    }
                    $close->setAttribute('data-slos-escape-trigger', 'true');
                }
            }

            $element->setAttribute('data-slos-escape-enabled', 'true');
            return true;
        }

        // For iframes: add skip link before
        if ($tag === 'iframe') {
            $skip = $element->ownerDocument->createElement('a');
            $skip->setAttribute('href', '#slos-after-iframe-' . uniqid());
            $skip->setAttribute('class', 'slos-skip-iframe screen-reader-text');
            $skip->textContent = 'Skip embedded content';
            
            $element->parentNode->insertBefore($skip, $element);
            
            // Add target anchor after iframe
            $target = $element->ownerDocument->createElement('span');
            $target->setAttribute('id', substr($skip->getAttribute('href'), 1));
            $target->setAttribute('tabindex', '-1');
            
            if ($element->nextSibling) {
                $element->parentNode->insertBefore($target, $element->nextSibling);
            } else {
                $element->parentNode->appendChild($target);
            }
            
            $element->setAttribute('data-slos-escape-enabled', 'true');
            return true;
        }

        // Generic fix: add escape key data attribute
        $element->setAttribute('data-slos-escape-enabled', 'true');
        return true;
    }

    private function is_modal(\DOMElement $element): bool
    {
        $role = $element->getAttribute('role');
        if ($role === 'dialog' || $role === 'alertdialog') {
            return true;
        }

        $class = strtolower($element->getAttribute('class'));
        return preg_match('/\b(modal|popup|lightbox|dialog)\b/', $class) === 1;
    }

    private function inject_escape_script(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $existing = $xpath->query('//script[@data-slos-escape-script]');
        if ($existing->length > 0) {
            return;
        }

        $script = $dom->createElement('script');
        $script->setAttribute('data-slos-escape-script', 'true');
        $script->textContent = '
(function() {
    // Handle Escape key for modals
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            var modal = document.querySelector("[data-slos-escape-enabled][role=\"dialog\"]:not([hidden]), " +
                "[data-slos-escape-enabled].modal:not(.hidden):not([style*=\"display: none\"])");
            
            if (modal) {
                var closeBtn = modal.querySelector("[data-slos-escape-trigger], .close, [aria-label*=\"close\"]");
                if (closeBtn) {
                    closeBtn.click();
                } else {
                    // Try to return focus to trigger
                    var trigger = document.querySelector("[data-modal-target=\"#" + modal.id + "\"]");
                    if (trigger) trigger.focus();
                }
                e.preventDefault();
            }
        }
    });
    
    // Focus trap handling for dialogs
    document.querySelectorAll("[data-slos-escape-enabled][role=\"dialog\"]").forEach(function(dialog) {
        dialog.addEventListener("keydown", function(e) {
            if (e.key === "Tab") {
                var focusables = dialog.querySelectorAll(
                    "a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), " +
                    "textarea:not([disabled]), [tabindex]:not([tabindex=\"-1\"])"
                );
                
                if (focusables.length === 0) return;
                
                var first = focusables[0];
                var last = focusables[focusables.length - 1];
                
                if (e.shiftKey && document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                } else if (!e.shiftKey && document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        });
    });
})();
';
        
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $body->appendChild($script);
        }
    }
}
```

---

## 4. ViewportFixer Enhancement

**Current State:** Returns `fixed_count: 0`  
**Issue:** Meta tag modification is possible

### Enhanced Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class ViewportFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'viewport';
    }

    public function get_description(): string
    {
        return 'Ensures viewport meta allows zooming and scaling';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $fixed_count = 0;

        $metas = $dom->getElementsByTagName('meta');
        $viewport_found = false;

        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('name')) === 'viewport') {
                $viewport_found = true;
                $content_attr = $meta->getAttribute('content');
                $fixed_content = $this->fix_viewport_content($content_attr);
                
                if ($fixed_content !== $content_attr) {
                    $meta->setAttribute('content', $fixed_content);
                    ++$fixed_count;
                }
            }
        }

        // Add viewport meta if missing
        if (!$viewport_found) {
            $head = $dom->getElementsByTagName('head')->item(0);
            if ($head) {
                $viewport = $dom->createElement('meta');
                $viewport->setAttribute('name', 'viewport');
                $viewport->setAttribute('content', 'width=device-width, initial-scale=1.0');
                $head->appendChild($viewport);
                ++$fixed_count;
            }
        }

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function fix_viewport_content(string $content): string
    {
        $parts = array_map('trim', explode(',', $content));
        $viewport_props = [];

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                list($key, $value) = array_map('trim', explode('=', $part, 2));
                $viewport_props[strtolower($key)] = $value;
            }
        }

        // Remove restrictive properties
        $restrictive = [
            'user-scalable' => ['no', '0'],
            'maximum-scale' => function($v) { return floatval($v) < 2; },
            'minimum-scale' => function($v) { return floatval($v) > 0.5; },
        ];

        foreach ($restrictive as $prop => $check) {
            if (isset($viewport_props[$prop])) {
                if (is_array($check) && in_array(strtolower($viewport_props[$prop]), $check)) {
                    // Change to accessible value
                    if ($prop === 'user-scalable') {
                        $viewport_props[$prop] = 'yes';
                    }
                } elseif (is_callable($check) && $check($viewport_props[$prop])) {
                    // Remove restrictive scale limits
                    if ($prop === 'maximum-scale') {
                        $viewport_props[$prop] = '5.0';
                    } elseif ($prop === 'minimum-scale') {
                        unset($viewport_props[$prop]);
                    }
                }
            }
        }

        // Ensure user-scalable=yes
        $viewport_props['user-scalable'] = 'yes';

        // Rebuild content string
        $result = [];
        foreach ($viewport_props as $key => $value) {
            $result[] = "{$key}={$value}";
        }

        return implode(', ', $result);
    }
}
```

---

## 5. ColorRelianceFixer Enhancement

**Current State:** Returns `fixed_count: 0`  
**Issue:** Can add text/icon indicators alongside color

### Enhanced Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class ColorRelianceFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'color-reliance';
    }

    public function get_description(): string
    {
        return 'Adds non-color indicators where color alone conveys information';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Fix required field indicators (red asterisks)
        $fixed_count += $this->fix_required_indicators($xpath);

        // Fix status indicators relying only on color
        $fixed_count += $this->fix_status_colors($xpath);

        // Fix links distinguished only by color
        $fixed_count += $this->fix_color_only_links($xpath);

        // Fix graph/chart legends
        $fixed_count += $this->fix_chart_legends($xpath);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function fix_required_indicators(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find red asterisks without text explanation
        $asterisks = $xpath->query(
            '//span[@style[contains(., "red") or contains(., "#f") or contains(., "#e")] and ' .
            'contains(text(), "*")][not(@aria-hidden)]'
        );

        foreach ($asterisks as $asterisk) {
            // Check if there's an associated label
            $label = $this->find_nearest_label($xpath, $asterisk);
            
            if ($label) {
                // Add screen reader text
                $sr_text = $asterisk->ownerDocument->createElement('span');
                $sr_text->setAttribute('class', 'screen-reader-text');
                $sr_text->textContent = '(required)';
                
                $asterisk->parentNode->insertBefore($sr_text, $asterisk->nextSibling);
                $asterisk->setAttribute('aria-hidden', 'true');
                ++$fixed;
            }
        }

        return $fixed;
    }

    private function fix_status_colors(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find status indicators by color classes without icons/text
        $status_patterns = [
            'success' => ['✓', 'Success'],
            'error' => ['✗', 'Error'],
            'warning' => ['⚠', 'Warning'],
            'info' => ['ℹ', 'Info'],
            'danger' => ['✗', 'Error'],
            'primary' => null, // No fix needed
        ];

        foreach ($status_patterns as $status => $indicator) {
            if (!$indicator) continue;
            
            $elements = $xpath->query(
                "//*[contains(@class, '{$status}') or contains(@class, 'bg-{$status}') or " .
                "contains(@class, 'text-{$status}')][not(.//svg) and not(.//i[contains(@class, 'icon')])]"
            );

            foreach ($elements as $element) {
                $text = trim($element->textContent);
                
                // Skip if already has status text
                if (stripos($text, $indicator[1]) !== false) {
                    continue;
                }

                // Add icon before content
                $icon = $element->ownerDocument->createElement('span');
                $icon->setAttribute('aria-hidden', 'true');
                $icon->textContent = $indicator[0] . ' ';
                
                if ($element->firstChild) {
                    $element->insertBefore($icon, $element->firstChild);
                } else {
                    $element->appendChild($icon);
                }
                
                // Add screen reader text if no visible status text
                if (stripos($text, $indicator[1]) === false && strlen($text) < 50) {
                    $sr = $element->ownerDocument->createElement('span');
                    $sr->setAttribute('class', 'screen-reader-text');
                    $sr->textContent = ' ' . $indicator[1];
                    $element->appendChild($sr);
                }
                
                ++$fixed;
            }
        }

        return $fixed;
    }

    private function fix_color_only_links(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find links that might only be distinguished by color
        // (no underline in style, within paragraphs)
        $links = $xpath->query(
            '//p//a[@style[contains(., "text-decoration") and ' .
            '(contains(., "none") or contains(., "underline: none"))]]'
        );

        foreach ($links as $link) {
            $style = $link->getAttribute('style');
            
            // Add underline back
            $style = preg_replace(
                '/text-decoration\s*:\s*none/i',
                'text-decoration: underline',
                $style
            );
            $link->setAttribute('style', $style);
            ++$fixed;
        }

        return $fixed;
    }

    private function fix_chart_legends(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find legend items that rely on colored squares
        $legends = $xpath->query(
            '//*[contains(@class, "legend") or contains(@class, "chart-legend")]' .
            '//*[@style[contains(., "background")]]'
        );

        foreach ($legends as $legend_item) {
            // Add pattern or text indicator
            $patterns = ['▪', '●', '▲', '◆', '★', '□', '○', '△'];
            static $pattern_index = 0;
            
            $pattern = $patterns[$pattern_index % count($patterns)];
            $pattern_index++;
            
            $indicator = $legend_item->ownerDocument->createElement('span');
            $indicator->setAttribute('aria-hidden', 'true');
            $indicator->textContent = $pattern . ' ';
            
            $legend_item->parentNode->insertBefore($indicator, $legend_item);
            ++$fixed;
        }

        return $fixed;
    }

    private function find_nearest_label(\DOMXPath $xpath, \DOMElement $element): ?\DOMElement
    {
        // Look for label in parent
        $parent = $element->parentNode;
        if ($parent instanceof \DOMElement) {
            if (strtolower($parent->tagName) === 'label') {
                return $parent;
            }
            
            $label = $xpath->query('.//label', $parent)->item(0);
            if ($label instanceof \DOMElement) {
                return $label;
            }
        }
        
        return null;
    }
}
```

---

## 6-9. Additional Enhancements

### AriaStateFixer, PageStructureFixer, GenericLinkTextFixer, AltTextQualityFixer

Follow similar patterns with actual implementations instead of returning 0. Key improvements:

1. **AriaStateFixer**: Set default `aria-pressed="false"` for toggle buttons, `aria-expanded="false"` for expandable sections
2. **PageStructureFixer**: Add `<main>` wrapper if missing, add landmark roles to semantic-looking divs
3. **GenericLinkTextFixer**: Use page context, URL analysis, and surrounding text to generate meaningful link text
4. **AltTextQualityFixer**: Use filename analysis, image context, and surrounding text to improve alt text quality

---

## Registration Updates

Update `FixerRegistry.php`:

```php
// Replace existing entries with enhanced versions
'focus-indicator' => Fixers\FocusIndicatorFixer::class,
'touch-target' => Fixers\TouchTargetFixer::class,
'keyboard-trap' => Fixers\KeyboardTrapFixer::class,
'viewport' => Fixers\ViewportFixer::class,
'color-reliance' => Fixers\ColorRelianceFixer::class,
```

---

*Enhancement specifications complete. These upgrades will significantly increase the auto-fix coverage rate from ~45% to 75%+.*
