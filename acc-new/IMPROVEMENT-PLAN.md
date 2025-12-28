# Accessibility Scanner Improvement Plan

**Version**: 3.2.0 Target  
**Created**: December 28, 2025  
**Status**: Planning Phase  

---

## Executive Summary

This document outlines a comprehensive improvement plan for the Accessibility Scanner module based on a deep audit of all 68 existing checkers. The plan addresses critical gaps, enhances detection accuracy, and adds WCAG 2.2 compliance capabilities.

---

## Table of Contents

1. [Phase 1: Critical Fixes (P0)](#phase-1-critical-fixes-p0)
2. [Phase 2: High Priority Improvements (P1)](#phase-2-high-priority-improvements-p1)
3. [Phase 3: Moderate Improvements (P2)](#phase-3-moderate-improvements-p2)
4. [Phase 4: New Checkers (P3)](#phase-4-new-checkers-p3)
5. [Phase 5: Architecture Enhancements](#phase-5-architecture-enhancements)
6. [Implementation Timeline](#implementation-timeline)
7. [Testing Strategy](#testing-strategy)

---

## Phase 1: Critical Fixes (P0)

**Timeline**: Week 1-2  
**Priority**: CRITICAL - Must fix before next release

### 1.1 Fix SkipLinkCheck (BROKEN)

**File**: `Scanner/Checkers/SkipLinkCheck.php`

**Current Issue**: The check is completely disabled - issues array is never populated.

**Required Changes**:
```php
// BEFORE (broken):
// $issues[] = [...] // Commented out!
return $issues;

// AFTER (fixed):
if (!$hasSkipLink) {
    $issues[] = [
        'element' => 'page',
        'context' => 'Document structure',
        'message' => 'No "Skip to Content" link found. Add a skip link at the beginning of the page for keyboard users.',
    ];
}
return $issues;
```

**Additional Improvements**:
- Add option to configure minimum content length threshold
- Check for skip link visibility (not `display:none` or `visibility:hidden` by default)
- Verify skip link target exists (`#main`, `#content`, etc.)

---

### 1.2 Enhance TextColorContrastCheck

**File**: `Scanner/Checkers/TextColorContrastCheck.php`

**Current Issues**:
- Only detects inline styles (misses 95%+ of real-world issues)
- Missing color format support (RGBA, HSLA, CSS variables)
- No large text detection (different ratio requirements)

**Required Changes**:

```php
// Add color format support
private function parse_color($color_str) {
    $color_str = trim(strtolower($color_str));
    
    // Hex (3, 4, 6, 8 digit)
    if (preg_match('/^#([a-f0-9]{3,8})$/i', $color_str, $matches)) {
        return $this->hex_to_rgb($matches[1]);
    }
    
    // RGB/RGBA
    if (preg_match('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $color_str, $matches)) {
        return [(int)$matches[1], (int)$matches[2], (int)$matches[3]];
    }
    
    // HSL/HSLA
    if (preg_match('/hsla?\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%/', $color_str, $matches)) {
        return $this->hsl_to_rgb($matches[1], $matches[2], $matches[3]);
    }
    
    // Named colors
    return $this->named_color_to_rgb($color_str);
}

// Add large text detection
private function is_large_text($element) {
    $style = $element->getAttribute('style');
    $font_size = $this->extract_font_size($style);
    $font_weight = $this->extract_font_weight($style);
    
    // Large text: 18pt+ OR 14pt+ bold
    if ($font_size >= 24) return true; // 18pt = 24px
    if ($font_size >= 18.67 && $font_weight >= 700) return true; // 14pt bold
    
    return false;
}

// Update ratio check
$required_ratio = $this->is_large_text($element) ? 3.0 : 4.5;
if ($ratio < $required_ratio) {
    // Report issue
}
```

**New Features to Add**:
- Extract colors from `<style>` tags within HTML
- Support CSS named colors (140+ standard colors)
- Add AAA compliance option (7:1 / 4.5:1)
- Report actual vs required ratio in message

---

### 1.3 Fix LandmarkRoleCheck - Add HTML5 Implicit Landmarks

**File**: `Scanner/Checkers/LandmarkRoleCheck.php`

**Current Issue**: Only checks explicit `role` attributes, missing HTML5 semantic elements.

**Required Changes**:

```php
public function check($content) {
    $issues = [];
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    
    // Map of HTML5 elements to implicit landmark roles
    $implicit_landmarks = [
        'main'   => 'main',
        'nav'    => 'navigation',
        'aside'  => 'complementary',
        'header' => 'banner',      // Only when not nested in article/section
        'footer' => 'contentinfo', // Only when not nested in article/section
        'form'   => 'form',        // Only when has accessible name
        'section'=> 'region',      // Only when has accessible name
    ];
    
    $landmark_counts = [];
    
    // Check explicit roles
    foreach ($this->landmarks as $landmark) {
        $elements = $xpath->query("//*[@role='$landmark']");
        $landmark_counts[$landmark] = ($landmark_counts[$landmark] ?? 0) + $elements->length;
        $this->check_duplicate_landmarks($elements, $landmark, $issues);
    }
    
    // Check implicit landmarks (HTML5 elements)
    foreach ($implicit_landmarks as $tag => $role) {
        $elements = $xpath->query("//$tag[not(@role)]");
        
        foreach ($elements as $element) {
            // Skip header/footer nested in article/section
            if (in_array($tag, ['header', 'footer'])) {
                if ($this->is_nested_in_sectioning($element)) {
                    continue;
                }
            }
            
            // form/section need accessible name to be landmarks
            if (in_array($tag, ['form', 'section'])) {
                if (!$this->has_accessible_name($element)) {
                    continue;
                }
            }
            
            $landmark_counts[$role] = ($landmark_counts[$role] ?? 0) + 1;
        }
    }
    
    // Check for multiple main landmarks
    if (($landmark_counts['main'] ?? 0) > 1) {
        $issues[] = [
            'element' => 'multiple',
            'context' => 'Document structure',
            'message' => 'Multiple main landmarks found. A page should have only one visible main landmark.',
        ];
    }
    
    return $issues;
}

private function is_nested_in_sectioning($element) {
    $parent = $element->parentNode;
    while ($parent && $parent instanceof \DOMElement) {
        if (in_array($parent->tagName, ['article', 'section', 'aside', 'nav'])) {
            return true;
        }
        $parent = $parent->parentNode;
    }
    return false;
}

private function has_accessible_name($element) {
    return $element->hasAttribute('aria-label') 
        || $element->hasAttribute('aria-labelledby')
        || $element->hasAttribute('title');
}
```

---

## Phase 2: High Priority Improvements (P1)

**Timeline**: Week 3-4  
**Priority**: HIGH - Significant accessibility gaps

### 2.1 Enhance FocusIndicatorCheck

**File**: `Scanner/Checkers/FocusIndicatorCheck.php`

**Improvements**:

```php
public function check($content) {
    $issues = [];
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    
    // 1. Check inline styles
    $this->check_inline_focus_removal($xpath, $issues);
    
    // 2. Check <style> tags for focus removal
    $this->check_style_tags_for_focus_removal($dom, $issues);
    
    // 3. Check for tabindex without visible focus indication
    $this->check_custom_focusable_elements($xpath, $issues);
    
    return $issues;
}

private function check_style_tags_for_focus_removal($dom, &$issues) {
    $styles = $dom->getElementsByTagName('style');
    
    foreach ($styles as $style) {
        $css = $style->textContent;
        
        // Detect :focus { outline: none/0 } patterns
        if (preg_match('/:focus\s*\{[^}]*outline\s*:\s*(none|0)[^}]*\}/i', $css)) {
            // Check if there's a replacement style
            if (!preg_match('/:focus\s*\{[^}]*(box-shadow|border|background)[^}]*\}/i', $css)) {
                $issues[] = [
                    'element' => 'style',
                    'context' => 'CSS in <style> tag',
                    'message' => 'Focus indicator removed via CSS (:focus { outline: none }) without visible replacement.',
                ];
            }
        }
        
        // Detect *:focus or global focus removal
        if (preg_match('/\*:focus\s*\{[^}]*outline\s*:\s*(none|0)/i', $css)) {
            $issues[] = [
                'element' => 'style',
                'context' => 'CSS in <style> tag',
                'message' => 'Global focus indicator removed (*:focus { outline: none }). This affects all focusable elements.',
            ];
        }
    }
}

private function check_custom_focusable_elements($xpath, &$issues) {
    // Elements with tabindex that might need custom focus styles
    $elements = $xpath->query('//*[@tabindex and @tabindex != "-1"]');
    
    foreach ($elements as $element) {
        $tag = $element->tagName;
        
        // Skip natively focusable elements (they have browser default focus)
        if (in_array($tag, ['a', 'button', 'input', 'select', 'textarea'])) {
            continue;
        }
        
        // Custom focusable element - warn about focus visibility
        $issues[] = [
            'element' => $tag,
            'context' => $this->get_element_html($element),
            'message' => 'Custom focusable element (tabindex). Ensure it has a visible focus indicator.',
            'severity' => 'notice', // Lower severity - just a reminder
        ];
    }
}
```

---

### 2.2 Enhance TouchTargetCheck

**File**: `Scanner/Checkers/TouchTargetCheck.php`

**Improvements**:

```php
public function check($content) {
    $issues = [];
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    
    // Check all interactive elements
    $elements = $xpath->query('//a | //button | //input | //*[@onclick] | //*[@role="button"]');
    
    foreach ($elements as $element) {
        $size = $this->get_element_size($element);
        
        if ($size !== null) {
            $min_size = 44; // WCAG 2.5.5 minimum
            
            if ($size['width'] < $min_size || $size['height'] < $min_size) {
                $issues[] = [
                    'element' => $element->tagName,
                    'context' => $this->get_element_html($element),
                    'message' => sprintf(
                        'Touch target size (%dpx × %dpx) is below recommended minimum (44px × 44px).',
                        $size['width'],
                        $size['height']
                    ),
                ];
            }
        }
    }
    
    return $issues;
}

private function get_element_size($element) {
    $style = $element->getAttribute('style');
    if (empty($style)) {
        return null; // Can't determine from static HTML
    }
    
    $width = $this->extract_dimension($style, 'width');
    $height = $this->extract_dimension($style, 'height');
    
    // Also check min-width/min-height
    $min_width = $this->extract_dimension($style, 'min-width');
    $min_height = $this->extract_dimension($style, 'min-height');
    
    // Use the larger of width or min-width
    $width = max($width ?? 0, $min_width ?? 0) ?: null;
    $height = max($height ?? 0, $min_height ?? 0) ?: null;
    
    if ($width === null && $height === null) {
        return null;
    }
    
    return [
        'width' => $width ?? 44, // Assume compliant if not specified
        'height' => $height ?? 44,
    ];
}

private function extract_dimension($style, $prop) {
    // Support px, em, rem units
    if (preg_match('/' . $prop . '\s*:\s*([\d.]+)(px|em|rem)/i', $style, $matches)) {
        $value = (float)$matches[1];
        $unit = strtolower($matches[2]);
        
        // Convert to pixels (approximate)
        switch ($unit) {
            case 'em':
            case 'rem':
                return $value * 16; // Assume 16px base
            default:
                return $value;
        }
    }
    return null;
}
```

---

### 2.3 Enhance KeyboardTrapCheck

**File**: `Scanner/Checkers/KeyboardTrapCheck.php`

**Improvements**:

```php
public function check($content) {
    $issues = [];
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    
    // 1. Check for event handlers on non-interactive elements
    $this->check_event_handlers($xpath, $issues);
    
    // 2. Check for tabindex patterns that may cause traps
    $this->check_tabindex_patterns($xpath, $issues);
    
    // 3. Check for potential infinite loops (single focusable in container)
    $this->check_single_focusable_containers($xpath, $issues);
    
    // 4. Check for inert attribute misuse
    $this->check_inert_attribute($xpath, $issues);
    
    return $issues;
}

private function check_tabindex_patterns($xpath, &$issues) {
    // Find containers with tabindex=-1 that contain focusable elements
    $containers = $xpath->query('//*[@tabindex="-1"]');
    
    foreach ($containers as $container) {
        // Check if it contains focusable children
        $focusable = $xpath->query('.//a | .//button | .//input | .//select | .//textarea | .//*[@tabindex]', $container);
        
        if ($focusable->length > 0 && $focusable->length < 3) {
            $issues[] = [
                'element' => $container->tagName,
                'context' => $this->get_element_html($container),
                'message' => 'Container with tabindex="-1" contains focusable elements. Verify focus can escape this region.',
                'severity' => 'warning',
            ];
        }
    }
}

private function check_single_focusable_containers($xpath, &$issues) {
    // Modals/dialogs with only one focusable element
    $modals = $xpath->query('//*[@role="dialog"] | //*[@role="alertdialog"] | //*[contains(@class, "modal")]');
    
    foreach ($modals as $modal) {
        $focusable = $xpath->query('.//a | .//button | .//input | .//select | .//textarea', $modal);
        
        if ($focusable->length === 1) {
            $issues[] = [
                'element' => 'dialog',
                'context' => $this->get_element_html($modal),
                'message' => 'Modal/dialog has only one focusable element. Ensure users can close or navigate away.',
                'severity' => 'warning',
            ];
        }
    }
}

private function check_inert_attribute($xpath, &$issues) {
    $inert_elements = $xpath->query('//*[@inert]');
    
    foreach ($inert_elements as $element) {
        // Warn about inert usage
        $issues[] = [
            'element' => $element->tagName,
            'context' => $this->get_element_html($element),
            'message' => 'Element uses "inert" attribute. Ensure this is intentional and content is accessible when needed.',
            'severity' => 'notice',
        ];
    }
}
```

---

## Phase 3: Moderate Improvements (P2)

**Timeline**: Week 5-6  
**Priority**: MODERATE - Improve detection accuracy

### 3.1 Enhance AltTextQualityCheck

**File**: `Scanner/Checkers/AltTextQualityCheck.php`

**Add detection for**:
- File names as alt text (IMG_1234.jpg, DSC_0001.png)
- CamelCase patterns (myImage, productPhoto)
- URL-like alt text
- Alt text matching nearby caption

```php
// File name patterns
private $filename_patterns = [
    '/^IMG_\d+/i',
    '/^DSC_?\d+/i',
    '/^DCIM_?\d+/i',
    '/^photo_?\d+/i',
    '/^image_?\d+/i',
    '/\.(jpg|jpeg|png|gif|webp|svg)$/i',
    '/^[a-f0-9]{8,}$/i', // Hash-like names
];

// Check for filename-like alt
foreach ($this->filename_patterns as $pattern) {
    if (preg_match($pattern, $alt)) {
        $issues[] = [
            'element' => 'img',
            'context' => $this->get_element_html($img),
            'message' => 'Alt text appears to be a filename. Provide a meaningful description.',
        ];
        break;
    }
}

// Check for URL in alt
if (preg_match('/^https?:\/\//i', $alt)) {
    $issues[] = [
        'element' => 'img',
        'context' => $this->get_element_html($img),
        'message' => 'Alt text contains a URL. Provide a text description instead.',
    ];
}
```

---

### 3.2 Update AriaRoleCheck with ARIA 1.2/1.3 Roles

**File**: `Scanner/Checkers/AriaRoleCheck.php`

**Add new roles**:
```php
$valid_roles = array(
    // ... existing roles ...
    
    // ARIA 1.2 additions
    'blockquote',
    'caption',
    'code',
    'deletion',
    'emphasis',
    'insertion',
    'mark',
    'meter',
    'paragraph',
    'strong',
    'subscript',
    'superscript',
    'time',
    'generic',
    
    // ARIA 1.3 additions (draft)
    'comment',
    'suggestion',
);
```

---

### 3.3 Enhance VideoAccessibilityCheck

**File**: `Scanner/Checkers/VideoAccessibilityCheck.php`

**Add embedded video detection**:
```php
public function check($content) {
    $issues = [];
    $dom = $this->get_dom($content);
    $xpath = new \DOMXPath($dom);
    
    // 1. Check native <video> elements
    $this->check_native_videos($xpath, $issues);
    
    // 2. Check embedded videos (iframes)
    $this->check_embedded_videos($xpath, $issues);
    
    // 3. Check for audio descriptions
    $this->check_audio_descriptions($xpath, $issues);
    
    return $issues;
}

private function check_embedded_videos($xpath, &$issues) {
    $iframes = $xpath->query('//iframe');
    
    $video_domains = [
        'youtube.com', 'youtu.be',
        'vimeo.com',
        'dailymotion.com',
        'wistia.com',
        'vidyard.com',
    ];
    
    foreach ($iframes as $iframe) {
        $src = $iframe->getAttribute('src');
        
        foreach ($video_domains as $domain) {
            if (strpos($src, $domain) !== false) {
                // Check for title
                if (!$iframe->hasAttribute('title') || empty(trim($iframe->getAttribute('title')))) {
                    $issues[] = [
                        'element' => 'iframe',
                        'context' => $this->get_element_html($iframe),
                        'message' => "Embedded video ($domain) iframe is missing a descriptive title attribute.",
                    ];
                }
                
                // Note about captions (can't verify programmatically)
                $issues[] = [
                    'element' => 'iframe',
                    'context' => $this->get_element_html($iframe),
                    'message' => "Embedded video detected. Ensure captions/subtitles are enabled on the video platform.",
                    'severity' => 'notice',
                ];
                
                break;
            }
        }
    }
}

private function check_audio_descriptions($xpath, &$issues) {
    $videos = $xpath->query('//video');
    
    foreach ($videos as $video) {
        $tracks = $video->getElementsByTagName('track');
        $hasDescriptions = false;
        
        foreach ($tracks as $track) {
            if ($track->getAttribute('kind') === 'descriptions') {
                $hasDescriptions = true;
                break;
            }
        }
        
        // This is AAA level, so make it a notice
        if (!$hasDescriptions) {
            $issues[] = [
                'element' => 'video',
                'context' => $this->get_element_html($video),
                'message' => 'Video does not have audio descriptions (<track kind="descriptions">). Consider adding for WCAG AAA compliance.',
                'severity' => 'notice',
            ];
        }
    }
}
```

---

### 3.4 Expand GenericLinkTextCheck

**File**: `Scanner/Checkers/GenericLinkTextCheck.php`

```php
private $genericPhrases = [
    // Original
    'click here', 'read more', 'learn more', 'more', 'here', 'link', 'go', 'continue reading',
    
    // Additions
    'click', 'this link', 'this page', 'click this',
    'download', 'see more', 'view more', 'view all',
    'info', 'information', 'details', 'more details',
    'open', 'start', 'begin', 'continue',
    'tap here', 'press here', 'select',
    'find out more', 'discover more',
    'full article', 'full story',
    '>>', '»', '...', '→',
];

// Also check for very short links (1-2 characters)
if (strlen($cleanText) <= 2 && !preg_match('/^\d+$/', $cleanText)) {
    $issues[] = [
        'message' => "Link text is too short: \"$text\". Provide more descriptive text.",
        'element' => $dom->saveHTML($link),
        'context' => $link->getAttribute('href'),
    ];
}
```

---

## Phase 4: New Checkers (P3)

**Timeline**: Week 7-10  
**Priority**: Enhancement - WCAG 2.2 compliance

### 4.1 LanguageChangeCheck (WCAG 3.1.2)

**New File**: `Scanner/Checkers/LanguageChangeCheck.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

class LanguageChangeCheck extends AbstractCheck {

    public function get_id() {
        return 'language-change';
    }

    public function get_description() {
        return 'Language changes within content should be marked with lang attribute.';
    }

    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '3.1.2';
    }

    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);

        // Common foreign phrases that should be marked
        $foreign_phrases = [
            'french' => ['c\'est la vie', 'je ne sais quoi', 'déjà vu', 'bon appétit', 'raison d\'être'],
            'latin' => ['et cetera', 'vice versa', 'ad hoc', 'per se', 'status quo', 'de facto'],
            'spanish' => ['hasta la vista', 'gracias', 'por favor', 'buenos días'],
            'german' => ['gesundheit', 'kindergarten', 'zeitgeist', 'wanderlust'],
            'italian' => ['ciao', 'arrivederci', 'buongiorno', 'grazie'],
        ];

        // Get all text nodes
        $textNodes = $xpath->query('//text()[normalize-space()]');
        
        foreach ($textNodes as $textNode) {
            $text = strtolower($textNode->textContent);
            $parent = $textNode->parentNode;
            
            // Skip if parent has lang attribute
            if ($this->has_lang_attribute($parent)) {
                continue;
            }
            
            foreach ($foreign_phrases as $lang => $phrases) {
                foreach ($phrases as $phrase) {
                    if (strpos($text, $phrase) !== false) {
                        $issues[] = [
                            'element' => $parent->tagName ?? 'text',
                            'context' => substr($textNode->textContent, 0, 100),
                            'message' => "Foreign phrase detected (\"$phrase\"). Consider wrapping in <span lang=\"{$this->get_lang_code($lang)}\">.",
                        ];
                        break 2; // One issue per text node
                    }
                }
            }
        }

        return $issues;
    }

    private function has_lang_attribute($element) {
        while ($element && $element instanceof \DOMElement) {
            if ($element->hasAttribute('lang')) {
                return true;
            }
            $element = $element->parentNode;
        }
        return false;
    }

    private function get_lang_code($language) {
        $codes = [
            'french' => 'fr',
            'latin' => 'la',
            'spanish' => 'es',
            'german' => 'de',
            'italian' => 'it',
        ];
        return $codes[$language] ?? 'und';
    }
}
```

---

### 4.2 AnimationPauseCheck (WCAG 2.2.2)

**New File**: `Scanner/Checkers/AnimationPauseCheck.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

class AnimationPauseCheck extends AbstractCheck {

    public function get_id() {
        return 'animation-pause';
    }

    public function get_description() {
        return 'Animations lasting more than 5 seconds must be pausable.';
    }

    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '2.2.2';
    }

    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        
        // Check <style> tags for long animations
        $styles = $dom->getElementsByTagName('style');
        
        foreach ($styles as $style) {
            $css = $style->textContent;
            
            // Look for animation-duration > 5s or animation-iteration-count: infinite
            if (preg_match('/animation[^:]*:\s*[^;]*infinite/i', $css) ||
                preg_match('/animation-iteration-count\s*:\s*infinite/i', $css)) {
                $issues[] = [
                    'element' => 'style',
                    'context' => 'CSS animation',
                    'message' => 'Infinite animation detected. Provide a mechanism to pause, stop, or hide the animation.',
                ];
            }
            
            // Check for long durations
            if (preg_match('/animation-duration\s*:\s*(\d+)s/i', $css, $matches)) {
                if ((int)$matches[1] > 5) {
                    $issues[] = [
                        'element' => 'style',
                        'context' => 'CSS animation',
                        'message' => "Animation duration ({$matches[1]}s) exceeds 5 seconds. Provide pause/stop controls.",
                    ];
                }
            }
        }
        
        // Check for inline animation styles
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*[@style]');
        
        foreach ($elements as $element) {
            $style = $element->getAttribute('style');
            
            if (preg_match('/animation[^:]*:\s*[^;]*infinite/i', $style)) {
                $issues[] = [
                    'element' => $element->tagName,
                    'context' => $this->get_element_html($element),
                    'message' => 'Element has infinite animation. Provide a pause mechanism.',
                ];
            }
        }
        
        // Check for auto-playing carousels/sliders (heuristic)
        $carousels = $xpath->query('//*[contains(@class, "carousel") or contains(@class, "slider") or contains(@class, "slideshow")]');
        
        foreach ($carousels as $carousel) {
            $issues[] = [
                'element' => $carousel->tagName,
                'context' => $this->get_element_html($carousel),
                'message' => 'Carousel/slider detected. If auto-playing, ensure users can pause it.',
                'severity' => 'notice',
            ];
        }

        return $issues;
    }

    private function get_element_html($node) {
        return $node->ownerDocument->saveHTML($node);
    }
}
```

---

### 4.3 TimingControlCheck (WCAG 2.2.1)

**New File**: `Scanner/Checkers/TimingControlCheck.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

class TimingControlCheck extends AbstractCheck {

    public function get_id() {
        return 'timing-control';
    }

    public function get_description() {
        return 'Time limits must be adjustable, extendable, or able to be turned off.';
    }

    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '2.2.1';
    }

    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);

        // Check for meta refresh
        $metas = $xpath->query('//meta[@http-equiv="refresh"]');
        
        foreach ($metas as $meta) {
            $content_attr = $meta->getAttribute('content');
            
            // Extract time value
            if (preg_match('/^(\d+)/', $content_attr, $matches)) {
                $seconds = (int)$matches[1];
                
                if ($seconds > 0 && $seconds < 72000) { // Less than 20 hours
                    $issues[] = [
                        'element' => 'meta',
                        'context' => $this->get_element_html($meta),
                        'message' => "Auto-refresh/redirect in {$seconds} seconds. Provide users control over timing or extend the limit.",
                    ];
                }
            }
        }

        // Check for setTimeout/setInterval patterns in scripts
        $scripts = $dom->getElementsByTagName('script');
        
        foreach ($scripts as $script) {
            $js = $script->textContent;
            
            // Look for redirect after timeout
            if (preg_match('/setTimeout\s*\([^,]+,\s*(\d+)\s*\)/i', $js, $matches)) {
                $ms = (int)$matches[1];
                if ($ms > 0 && $ms < 300000) { // Less than 5 minutes
                    $issues[] = [
                        'element' => 'script',
                        'context' => 'JavaScript setTimeout',
                        'message' => 'Timed action detected. Ensure users can adjust or extend the time limit.',
                        'severity' => 'warning',
                    ];
                }
            }
        }

        return $issues;
    }

    private function get_element_html($node) {
        return $node->ownerDocument->saveHTML($node);
    }
}
```

---

### 4.4 StatusMessageCheck (WCAG 4.1.3)

**New File**: `Scanner/Checkers/StatusMessageCheck.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

class StatusMessageCheck extends AbstractCheck {

    public function get_id() {
        return 'status-message';
    }

    public function get_description() {
        return 'Status messages must be announced to assistive technology without receiving focus.';
    }

    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '4.1.3';
    }

    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);

        // Common patterns for status messages
        $status_patterns = [
            'alert', 'notice', 'notification', 'message', 'toast',
            'success', 'error', 'warning', 'info',
            'flash', 'feedback', 'status', 'update',
        ];

        foreach ($status_patterns as $pattern) {
            $elements = $xpath->query("//*[contains(@class, '$pattern')]");
            
            foreach ($elements as $element) {
                // Check if it has appropriate ARIA
                $role = $element->getAttribute('role');
                $ariaLive = $element->getAttribute('aria-live');
                
                $hasAppropriateAria = in_array($role, ['alert', 'status', 'log']) || 
                                       in_array($ariaLive, ['polite', 'assertive']);
                
                if (!$hasAppropriateAria) {
                    $issues[] = [
                        'element' => $element->tagName,
                        'context' => $this->get_element_html($element),
                        'message' => "Element appears to be a status message (class contains '$pattern') but lacks role=\"status/alert\" or aria-live.",
                    ];
                }
            }
        }

        return $issues;
    }

    private function get_element_html($node) {
        return $node->ownerDocument->saveHTML($node);
    }
}
```

---

### 4.5 ErrorIdentificationCheck (WCAG 3.3.1)

**New File**: `Scanner/Checkers/ErrorIdentificationCheck.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

class ErrorIdentificationCheck extends AbstractCheck {

    public function get_id() {
        return 'error-identification';
    }

    public function get_description() {
        return 'Form input errors must be identified and described to users in text.';
    }

    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '3.3.1';
    }

    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);

        // Check required fields
        $required = $xpath->query('//input[@required] | //select[@required] | //textarea[@required]');
        
        foreach ($required as $field) {
            // Check if aria-describedby exists for error messaging
            if (!$field->hasAttribute('aria-describedby') && !$field->hasAttribute('aria-errormessage')) {
                $issues[] = [
                    'element' => $field->tagName,
                    'context' => $this->get_element_html($field),
                    'message' => 'Required field should have aria-describedby or aria-errormessage to link to error instructions.',
                    'severity' => 'notice',
                ];
            }
        }

        // Check for aria-invalid usage
        $invalid = $xpath->query('//*[@aria-invalid="true"]');
        
        foreach ($invalid as $field) {
            // Must have error message linked
            if (!$field->hasAttribute('aria-describedby') && !$field->hasAttribute('aria-errormessage')) {
                $issues[] = [
                    'element' => $field->tagName,
                    'context' => $this->get_element_html($field),
                    'message' => 'Field marked as invalid (aria-invalid="true") must have associated error message via aria-describedby or aria-errormessage.',
                ];
            }
        }

        // Check for error containers that may only use color
        $error_containers = $xpath->query('//*[contains(@class, "error") or contains(@class, "invalid")]');
        
        foreach ($error_containers as $container) {
            $text = trim($container->textContent);
            if (empty($text)) {
                $issues[] = [
                    'element' => $container->tagName,
                    'context' => $this->get_element_html($container),
                    'message' => 'Error container appears empty. Errors must be described in text, not just indicated by color/icon.',
                ];
            }
        }

        return $issues;
    }

    private function get_element_html($node) {
        return $node->ownerDocument->saveHTML($node);
    }
}
```

---

## Phase 5: Architecture Enhancements

**Timeline**: Week 11-12  
**Priority**: Technical debt and infrastructure

### 5.1 Enhanced CheckInterface

**File**: `Scanner/CheckInterface.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

interface CheckInterface {
    public function get_id();
    public function get_description();
    public function get_severity();
    public function get_wcag_criteria();
    public function check($content);
    
    // NEW METHODS
    
    /**
     * Get WCAG conformance level (A, AA, AAA)
     */
    public function get_wcag_level();
    
    /**
     * Get remediation hint for fixing the issue
     */
    public function get_remediation_hint();
    
    /**
     * Get link to WCAG documentation
     */
    public function get_wcag_url();
    
    /**
     * Check with pre-parsed DOM (performance optimization)
     */
    public function check_dom(\DOMDocument $dom, $content);
    
    /**
     * Get issue confidence level
     * @return string 'definite' | 'likely' | 'potential'
     */
    public function get_confidence();
}
```

---

### 5.2 Enhanced AbstractCheck

**File**: `Scanner/AbstractCheck.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

abstract class AbstractCheck implements CheckInterface {
    
    /**
     * Cached DOM document
     */
    protected $cached_dom = null;
    
    public function get_wcag_level() {
        return 'AA'; // Default to AA, override in subclasses
    }
    
    public function get_remediation_hint() {
        return ''; // Override in subclasses
    }
    
    public function get_wcag_url() {
        $criteria = $this->get_wcag_criteria();
        if (empty($criteria)) {
            return '';
        }
        return "https://www.w3.org/WAI/WCAG21/Understanding/{$this->criteria_to_slug($criteria)}";
    }
    
    public function get_confidence() {
        return 'definite'; // Override for heuristic checks
    }
    
    public function check_dom(\DOMDocument $dom, $content) {
        $this->cached_dom = $dom;
        return $this->check($content);
    }
    
    protected function get_dom($content) {
        if ($this->cached_dom !== null) {
            return $this->cached_dom;
        }
        
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        return $dom;
    }
    
    protected function get_element_html($node, $max_length = 200) {
        $html = $node->ownerDocument->saveHTML($node);
        if (strlen($html) > $max_length) {
            return substr($html, 0, $max_length) . '...';
        }
        return $html;
    }
    
    private function criteria_to_slug($criteria) {
        // Convert "1.1.1" to WCAG URL format
        $parts = explode('.', $criteria);
        $slugs = [
            '1.1.1' => 'non-text-content',
            '1.2.1' => 'audio-only-and-video-only-prerecorded',
            '1.2.2' => 'captions-prerecorded',
            '1.3.1' => 'info-and-relationships',
            '1.3.5' => 'identify-input-purpose',
            '1.4.1' => 'use-of-color',
            '1.4.3' => 'contrast-minimum',
            '1.4.4' => 'resize-text',
            '2.1.1' => 'keyboard',
            '2.1.2' => 'no-keyboard-trap',
            '2.2.1' => 'timing-adjustable',
            '2.2.2' => 'pause-stop-hide',
            '2.4.1' => 'bypass-blocks',
            '2.4.4' => 'link-purpose-in-context',
            '2.4.7' => 'focus-visible',
            '2.5.5' => 'target-size-enhanced',
            '3.1.1' => 'language-of-page',
            '3.1.2' => 'language-of-parts',
            '3.2.3' => 'consistent-navigation',
            '3.3.1' => 'error-identification',
            '3.3.2' => 'labels-or-instructions',
            '3.3.3' => 'error-suggestion',
            '4.1.2' => 'name-role-value',
            '4.1.3' => 'status-messages',
        ];
        
        return $slugs[$criteria] ?? '';
    }
}
```

---

### 5.3 Context-Aware ScannerEngine

**File**: `Scanner/ScannerEngine.php`

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

class ScannerEngine {
    
    private $checks = [];
    private $context = [];
    
    /**
     * Set scanning context
     * 
     * @param array $context [
     *   'is_full_page' => bool,
     *   'post_type' => string,
     *   'wcag_level' => 'A' | 'AA' | 'AAA',
     *   'scan_mode' => 'quick' | 'full' | 'deep',
     * ]
     */
    public function set_context(array $context) {
        $this->context = array_merge([
            'is_full_page' => false,
            'post_type' => 'post',
            'wcag_level' => 'AA',
            'scan_mode' => 'full',
        ], $context);
    }
    
    public function scan($content, array $context = []) {
        if (!empty($context)) {
            $this->set_context($context);
        }
        
        $results = [];
        
        if (empty($content)) {
            return $results;
        }
        
        // Parse DOM once
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $content, 
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        
        foreach ($this->checks as $check) {
            // Filter by WCAG level
            if (!$this->should_run_check($check)) {
                continue;
            }
            
            try {
                $issues = method_exists($check, 'check_dom')
                    ? $check->check_dom($dom, $content)
                    : $check->check($content);
                
                if (!empty($issues)) {
                    // Add metadata to each issue
                    $issues = array_map(function($issue) use ($check) {
                        return array_merge($issue, [
                            'wcag_criteria' => $check->get_wcag_criteria(),
                            'wcag_level' => $check->get_wcag_level(),
                            'wcag_url' => $check->get_wcag_url(),
                            'confidence' => $issue['confidence'] ?? $check->get_confidence(),
                            'remediation' => $check->get_remediation_hint(),
                        ]);
                    }, $issues);
                    
                    $results[$check->get_id()] = [
                        'id' => $check->get_id(),
                        'description' => $check->get_description(),
                        'severity' => $check->get_severity(),
                        'wcag_level' => $check->get_wcag_level(),
                        'issues' => $issues,
                    ];
                }
            } catch (\Exception $e) {
                error_log('Accessibility Scanner Error in check ' . $check->get_id() . ': ' . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    private function should_run_check(CheckInterface $check) {
        $target_level = $this->context['wcag_level'] ?? 'AA';
        $check_level = $check->get_wcag_level();
        
        $levels = ['A' => 1, 'AA' => 2, 'AAA' => 3];
        
        return ($levels[$check_level] ?? 2) <= ($levels[$target_level] ?? 2);
    }
}
```

---

## Implementation Timeline

| Week | Phase | Focus | Deliverables |
|------|-------|-------|--------------|
| 1-2 | P0 | Critical Fixes | SkipLinkCheck, TextColorContrastCheck, LandmarkRoleCheck |
| 3-4 | P1 | High Priority | FocusIndicatorCheck, TouchTargetCheck, KeyboardTrapCheck |
| 5-6 | P2 | Moderate | AltTextQualityCheck, AriaRoleCheck, VideoAccessibilityCheck, GenericLinkTextCheck |
| 7-8 | P3a | New Checkers | LanguageChangeCheck, AnimationPauseCheck, TimingControlCheck |
| 9-10 | P3b | New Checkers | StatusMessageCheck, ErrorIdentificationCheck |
| 11-12 | P5 | Architecture | Enhanced interfaces, context-aware scanning |

---

## Testing Strategy

### Unit Tests

Create test cases for each checker with:
1. **Positive cases** - HTML that should trigger issues
2. **Negative cases** - HTML that should pass
3. **Edge cases** - Unusual but valid markup

### Test HTML Fixtures

Create `/tests/fixtures/` directory with:
- `missing-alt.html`
- `low-contrast.html`
- `keyboard-trap.html`
- `proper-accessibility.html` (passes all checks)

### Integration Tests

1. Scan real WordPress sites
2. Compare results with axe-core / WAVE
3. Measure false positive/negative rates

### Performance Benchmarks

- Scan 100 posts in < 30 seconds
- Single page scan < 500ms
- Memory usage < 50MB

---

## Success Metrics

| Metric | Current | Target |
|--------|---------|--------|
| WCAG criteria covered | ~60% | 90%+ |
| False positive rate | Unknown | < 5% |
| False negative rate | High (color contrast) | < 10% |
| Scan speed (per page) | ~200ms | < 300ms |
| New checkers | 0 | 11 |

---

## Resources

- [WCAG 2.1 Quick Reference](https://www.w3.org/WAI/WCAG21/quickref/)
- [WCAG 2.2 Changes](https://www.w3.org/TR/WCAG22/)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [axe-core Rules](https://github.com/dequelabs/axe-core/blob/develop/doc/rule-descriptions.md)

---

*Document prepared for Shahi LegalOps Suite development team.*
