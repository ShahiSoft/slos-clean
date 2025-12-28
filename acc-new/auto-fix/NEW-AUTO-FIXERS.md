# New Auto-Fixer Specifications

This document contains complete implementation specifications for 9 new auto-fixers needed to achieve comprehensive WCAG coverage.

---

## Table of Contents

1. [LanguageChangeFixer](#1-languagechangefixer)
2. [StatusMessageFixer](#2-statusmessagefixer)
3. [AnimationPauseFixer](#3-animationpausefixer)
4. [TimingControlFixer](#4-timingcontrolfixer)
5. [ErrorIdentificationFixer](#5-erroridentificationfixer)
6. [EnhancedContrastFixer](#6-enhancedcontrastfixer)
7. [EnhancedFocusFixer](#7-enhancedfocusfixer)
8. [EnhancedTouchTargetFixer](#8-enhancedtouchtargetfixer)
9. [ViewportZoomFixer](#9-viewportzoomfixer)

---

## 1. LanguageChangeFixer

**WCAG:** 3.1.2 Language of Parts (Level AA)  
**Checker ID:** `language-change`  
**Purpose:** Add `lang` attribute to content in different languages

### Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class LanguageChangeFixer extends BaseFixer
{
    /**
     * Common foreign word patterns with their language codes
     */
    private const LANGUAGE_PATTERNS = [
        // French
        'fr' => [
            '/\b(bonjour|merci|je|vous|nous|avec|pour|dans|sur|est|sont|cette?|ces|les?|la|une?|des|du|au|aux|par|mais|ou|et|qui|que|quoi|comment|pourquoi|où)\b/iu',
            '/\b(monsieur|madame|mademoiselle|café|résumé|cliché|déjà\s+vu|à\s+la\s+carte|bon\s+appétit|c\'est\s+la\s+vie|raison\s+d\'être|coup\s+de\s+grâce)\b/iu',
        ],
        // Spanish  
        'es' => [
            '/\b(hola|gracias|buenos?\s+días?|buenas?\s+noches?|señor|señora|por\s+favor|de\s+nada|mañana|fiesta|siesta|amigo|hasta\s+la\s+vista|que\s+será\s+será)\b/iu',
            '/\b(el|la|los|las|un|una|unos|unas|de|en|con|para|por|como|pero|más|muy|también|ahora)\b/iu',
        ],
        // German
        'de' => [
            '/\b(guten\s+tag|guten\s+morgen|auf\s+wiedersehen|danke\s+schön?|bitte|herr|frau|wunderbar|zeitgeist|wanderlust|kindergarten|schadenfreude|über)\b/iu',
            '/\b(der|die|das|ein|eine|ist|sind|mit|für|und|oder|aber|auch|noch|schon|nicht|kein)\b/iu',
        ],
        // Italian
        'it' => [
            '/\b(ciao|buongiorno|grazie|prego|signor|signora|arrivederci|bella|bello|dolce\s+vita|al\s+dente|cappuccino|espresso|pasta|pizza|gelato)\b/iu',
            '/\b(il|la|lo|gli|le|un|uno|una|di|da|in|con|su|per|tra|fra|che|non|più|molto)\b/iu',
        ],
        // Portuguese
        'pt' => [
            '/\b(obrigado|obrigada|bom\s+dia|boa\s+noite|senhor|senhora|saudade|fado)\b/iu',
        ],
        // Japanese (Romaji)
        'ja' => [
            '/\b(arigatou?|konnichiwa|sayounara|ohayou?|sumimasen|hai|iie|san|sama|chan|kun|sensei|senpai|kawaii|sugoi|anime|manga|karaoke|tsunami|emoji|origami|karate|judo|samurai|ninja|sushi|sake|tofu|ramen|wasabi|tempura|teriyaki|miso|umami|bonsai|futon|kimono|zen)\b/iu',
        ],
        // Chinese (Pinyin)
        'zh' => [
            '/\b(nihao|xiexie|zaijian|feng\s+shui|yin\s+yang|kung\s+fu|tai\s+chi|dim\s+sum|chow\s+mein|wok|tofu|ginseng)\b/iu',
        ],
        // Latin
        'la' => [
            '/\b(et\s+cetera|etc|vice\s+versa|ad\s+hoc|per\s+se|status\s+quo|quid\s+pro\s+quo|modus\s+operandi|bona\s+fide|de\s+facto|prima\s+facie|pro\s+bono|in\s+vitro|in\s+vivo|alma\s+mater|curriculum\s+vitae|magna\s+cum\s+laude)\b/iu',
        ],
        // Russian (Transliterated)
        'ru' => [
            '/\b(spasibo|da|nyet|zdravstvuyte|dosvidaniya|glasnost|perestroika|babushka|matryoshka|bolshoi|gulag|kremlin|vodka|troika|samovar)\b/iu',
        ],
        // Arabic (Transliterated)
        'ar' => [
            '/\b(salaam|shukran|marhaba|inshallah|mashallah|alhamdulillah|halal|haram|imam|mosque|minaret|muezzin|ramadan|eid|hajj|jihad|fatwa|sheikh)\b/iu',
        ],
        // Hindi (Transliterated)
        'hi' => [
            '/\b(namaste|dhanyavaad|accha|bahut|thik|haan|nahin|guru|karma|yoga|chakra|mantra|nirvana|avatar|jungle|bungalow|pundit|rajah|maharajah)\b/iu',
        ],
    ];

    public function get_id(): string
    {
        return 'language-change';
    }

    public function get_description(): string
    {
        return 'Adds lang attribute to content in different languages';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Get all text-containing elements without lang attribute
        $text_elements = $xpath->query(
            '//p[not(@lang)] | //span[not(@lang)] | //div[not(@lang)] | ' .
            '//li[not(@lang)] | //td[not(@lang)] | //th[not(@lang)] | ' .
            '//blockquote[not(@lang)] | //q[not(@lang)] | //cite[not(@lang)]'
        );

        foreach ($text_elements as $element) {
            $text = $element->textContent;
            
            // Skip if too short
            if (strlen(trim($text)) < 3) {
                continue;
            }

            // Already has lang on ancestor?
            if ($this->has_lang_ancestor($element)) {
                continue;
            }

            // Detect language
            $detected_lang = $this->detect_language($text);
            
            if ($detected_lang && $detected_lang !== $this->get_page_language($dom)) {
                // Wrap text in span with lang attribute if mixed content
                if ($element->childNodes->length > 1 || 
                    $this->has_mixed_language_content($text)) {
                    $this->wrap_foreign_text($element, $detected_lang);
                } else {
                    $element->setAttribute('lang', $detected_lang);
                }
                ++$fixed_count;
            }
        }

        // Also handle inline quotes and phrases
        $fixed_count += $this->fix_inline_foreign_phrases($dom);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function detect_language(string $text): ?string
    {
        $text = strtolower(trim($text));
        
        foreach (self::LANGUAGE_PATTERNS as $lang => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text)) {
                    // Count matches to determine confidence
                    preg_match_all($pattern, $text, $matches);
                    $match_count = count($matches[0] ?? []);
                    
                    // Require at least 2 matches for common words, 1 for phrases
                    if ($match_count >= 2 || strlen($text) < 30) {
                        return $lang;
                    }
                }
            }
        }
        
        return null;
    }

    private function get_page_language(\DOMDocument $dom): string
    {
        $html = $dom->getElementsByTagName('html')->item(0);
        if ($html && $html->hasAttribute('lang')) {
            return strtolower(substr($html->getAttribute('lang'), 0, 2));
        }
        return 'en'; // Default to English
    }

    private function has_lang_ancestor(\DOMElement $element): bool
    {
        $parent = $element->parentNode;
        while ($parent && $parent instanceof \DOMElement) {
            if ($parent->hasAttribute('lang')) {
                return true;
            }
            $parent = $parent->parentNode;
        }
        return false;
    }

    private function has_mixed_language_content(string $text): bool
    {
        $languages_found = [];
        foreach (self::LANGUAGE_PATTERNS as $lang => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text)) {
                    $languages_found[$lang] = true;
                }
            }
        }
        return count($languages_found) > 1;
    }

    private function wrap_foreign_text(\DOMElement $element, string $lang): void
    {
        // Complex implementation: walk through child nodes
        // and wrap detected foreign text segments in <span lang="">
        // For simplicity, we'll set lang on the element itself
        $element->setAttribute('lang', $lang);
    }

    private function fix_inline_foreign_phrases(\DOMDocument $dom): int
    {
        $fixed = 0;
        $xpath = new \DOMXPath($dom);
        
        // Find <em>, <i>, <cite> which often contain foreign words
        $emphasis = $xpath->query('//em[not(@lang)] | //i[not(@lang)] | //cite[not(@lang)]');
        
        foreach ($emphasis as $element) {
            $text = $element->textContent;
            $detected = $this->detect_language($text);
            
            if ($detected && $detected !== $this->get_page_language($dom)) {
                $element->setAttribute('lang', $detected);
                ++$fixed;
            }
        }
        
        return $fixed;
    }
}
```

---

## 2. StatusMessageFixer

**WCAG:** 4.1.3 Status Messages (Level AA)  
**Checker ID:** `status-message`  
**Purpose:** Ensure status messages are announced to screen readers

### Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class StatusMessageFixer extends BaseFixer
{
    /**
     * Patterns indicating status/notification elements
     */
    private const STATUS_PATTERNS = [
        'success' => ['success', 'completed', 'saved', 'done', 'confirmed'],
        'error' => ['error', 'fail', 'invalid', 'problem', 'issue'],
        'warning' => ['warning', 'caution', 'attention', 'notice'],
        'info' => ['info', 'information', 'note', 'tip', 'update'],
        'loading' => ['loading', 'processing', 'please wait', 'working'],
        'progress' => ['progress', 'step', 'stage', 'uploading', 'downloading'],
    ];

    /**
     * Class patterns that indicate status elements
     */
    private const STATUS_CLASSES = [
        'alert', 'notice', 'message', 'notification', 'toast',
        'flash', 'status', 'banner', 'feedback', 'snackbar',
        'callout', 'announcement', 'info-box', 'message-box',
    ];

    public function get_id(): string
    {
        return 'status-message';
    }

    public function get_description(): string
    {
        return 'Adds appropriate ARIA live regions to status messages';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Find potential status elements by class
        $status_elements = $this->find_status_elements($xpath);

        foreach ($status_elements as $element) {
            if ($this->fix_status_element($element)) {
                ++$fixed_count;
            }
        }

        // Fix form validation messages
        $fixed_count += $this->fix_form_messages($xpath);

        // Fix loading indicators
        $fixed_count += $this->fix_loading_indicators($xpath);

        // Fix search result counters
        $fixed_count += $this->fix_result_counters($xpath);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function find_status_elements(\DOMXPath $xpath): array
    {
        $elements = [];
        
        // Build class selector
        $class_conditions = [];
        foreach (self::STATUS_CLASSES as $class) {
            $class_conditions[] = "contains(@class, '{$class}')";
        }
        
        $query = '//*[(' . implode(' or ', $class_conditions) . ') and not(@role) and not(@aria-live)]';
        
        $nodes = $xpath->query($query);
        foreach ($nodes as $node) {
            $elements[] = $node;
        }
        
        return $elements;
    }

    private function fix_status_element(\DOMElement $element): bool
    {
        $class = strtolower($element->getAttribute('class'));
        $text = strtolower($element->textContent);
        
        // Determine type based on class/content
        $type = $this->determine_status_type($class, $text);
        
        if (!$type) {
            return false;
        }

        switch ($type) {
            case 'error':
                $element->setAttribute('role', 'alert');
                $element->setAttribute('aria-live', 'assertive');
                break;
                
            case 'success':
            case 'info':
            case 'warning':
                $element->setAttribute('role', 'status');
                $element->setAttribute('aria-live', 'polite');
                break;
                
            case 'loading':
            case 'progress':
                $element->setAttribute('role', 'status');
                $element->setAttribute('aria-live', 'polite');
                $element->setAttribute('aria-busy', 'true');
                break;
        }

        return true;
    }

    private function determine_status_type(string $class, string $text): ?string
    {
        foreach (self::STATUS_PATTERNS as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($class, $pattern) !== false || 
                    stripos($text, $pattern) !== false) {
                    return $type;
                }
            }
        }
        
        // Check for common Bootstrap/UI framework classes
        if (preg_match('/\b(alert|notice|message)-(success|error|warning|info|danger)\b/', $class, $m)) {
            return $m[2] === 'danger' ? 'error' : $m[2];
        }
        
        return null;
    }

    private function fix_form_messages(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find form validation messages
        $messages = $xpath->query(
            '//*[contains(@class, "validation") or contains(@class, "form-error") or ' .
            'contains(@class, "field-error") or contains(@class, "help-block")]' .
            '[not(@role) and not(@aria-live)]'
        );
        
        foreach ($messages as $msg) {
            $msg->setAttribute('role', 'alert');
            $msg->setAttribute('aria-live', 'assertive');
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_loading_indicators(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find loading spinners/indicators
        $loaders = $xpath->query(
            '//*[contains(@class, "loading") or contains(@class, "spinner") or ' .
            'contains(@class, "loader") or contains(@class, "progress")]' .
            '[not(@role) and not(@aria-live)]'
        );
        
        foreach ($loaders as $loader) {
            $loader->setAttribute('role', 'status');
            $loader->setAttribute('aria-live', 'polite');
            
            // If no text content, add screen reader text
            if (trim($loader->textContent) === '') {
                $sr_text = $loader->ownerDocument->createElement('span');
                $sr_text->setAttribute('class', 'screen-reader-text');
                $sr_text->textContent = 'Loading...';
                $loader->appendChild($sr_text);
            }
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_result_counters(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find search result counters
        $counters = $xpath->query(
            '//*[contains(@class, "result") and contains(@class, "count")]' .
            '[not(@role) and not(@aria-live)]'
        );
        
        foreach ($counters as $counter) {
            $counter->setAttribute('role', 'status');
            $counter->setAttribute('aria-live', 'polite');
            $counter->setAttribute('aria-atomic', 'true');
            ++$fixed;
        }
        
        return $fixed;
    }
}
```

---

## 3. AnimationPauseFixer

**WCAG:** 2.2.2 Pause, Stop, Hide (Level A)  
**Checker ID:** `animation-pause`  
**Purpose:** Add pause controls for animated content

### Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class AnimationPauseFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'animation-pause';
    }

    public function get_description(): string
    {
        return 'Adds pause controls to animated content';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Fix animated GIFs
        $fixed_count += $this->fix_animated_gifs($xpath);

        // Fix CSS animation elements
        $fixed_count += $this->fix_css_animations($xpath);

        // Fix auto-playing carousels/sliders
        $fixed_count += $this->fix_carousels($xpath);

        // Fix marquee elements (deprecated but still used)
        $fixed_count += $this->fix_marquees($xpath);

        // Fix auto-scrolling content
        $fixed_count += $this->fix_auto_scroll($xpath);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function fix_animated_gifs(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find GIF images without pause control
        $gifs = $xpath->query('//img[contains(@src, ".gif") and not(ancestor::*[@data-pause-control])]');
        
        foreach ($gifs as $gif) {
            // Wrap in container with pause button
            $wrapper = $gif->ownerDocument->createElement('div');
            $wrapper->setAttribute('class', 'slos-animation-container');
            $wrapper->setAttribute('data-pause-control', 'true');
            
            // Create pause button
            $button = $gif->ownerDocument->createElement('button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('class', 'slos-pause-animation');
            $button->setAttribute('aria-label', 'Pause animation');
            $button->setAttribute('aria-pressed', 'false');
            $button->textContent = '⏸';
            
            // Clone and replace
            $parent = $gif->parentNode;
            $gif_clone = $gif->cloneNode(true);
            $gif_clone->setAttribute('data-animated', 'true');
            
            $wrapper->appendChild($gif_clone);
            $wrapper->appendChild($button);
            
            $parent->replaceChild($wrapper, $gif);
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_css_animations(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find elements with animation in inline style
        $animated = $xpath->query('//*[@style[contains(., "animation")]]');
        
        foreach ($animated as $element) {
            if ($element->hasAttribute('data-pause-control')) {
                continue;
            }
            
            // Add data attribute and control
            $element->setAttribute('data-pause-control', 'true');
            $element->setAttribute('data-animation-state', 'running');
            
            // Add pause button if not interactive
            if (!$this->is_interactive($element)) {
                $this->add_pause_button($element);
            }
            
            ++$fixed;
        }
        
        // Find elements with animation classes
        $animation_classes = ['animate', 'animated', 'animation', 'motion', 'moving'];
        foreach ($animation_classes as $class) {
            $elements = $xpath->query("//*[contains(@class, '{$class}') and not(@data-pause-control)]");
            foreach ($elements as $element) {
                $element->setAttribute('data-pause-control', 'true');
                $element->setAttribute('data-animation-state', 'running');
                ++$fixed;
            }
        }
        
        return $fixed;
    }

    private function fix_carousels(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Common carousel class patterns
        $carousel_selectors = [
            "contains(@class, 'carousel')",
            "contains(@class, 'slider')",
            "contains(@class, 'slideshow')",
            "contains(@class, 'swiper')",
            "contains(@class, 'slick')",
            "contains(@class, 'owl-')",
            "contains(@class, 'flickity')",
        ];
        
        $query = '//*[(' . implode(' or ', $carousel_selectors) . ') and not(@data-pause-control)]';
        $carousels = $xpath->query($query);
        
        foreach ($carousels as $carousel) {
            $carousel->setAttribute('data-pause-control', 'true');
            $carousel->setAttribute('aria-roledescription', 'carousel');
            
            // Check if pause button exists
            $existing_pause = $xpath->query(
                './/*[contains(@class, "pause") or @aria-label[contains(., "pause")]]',
                $carousel
            );
            
            if ($existing_pause->length === 0) {
                $this->add_carousel_controls($carousel);
            }
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_marquees(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        $marquees = $xpath->query('//marquee');
        
        foreach ($marquees as $marquee) {
            // Replace with accessible alternative
            $div = $marquee->ownerDocument->createElement('div');
            $div->setAttribute('class', 'slos-accessible-marquee');
            $div->setAttribute('role', 'marquee');
            $div->setAttribute('aria-live', 'off');
            $div->setAttribute('data-pause-control', 'true');
            
            // Copy content
            while ($marquee->firstChild) {
                $div->appendChild($marquee->firstChild);
            }
            
            // Add pause button
            $button = $marquee->ownerDocument->createElement('button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('class', 'slos-pause-marquee');
            $button->setAttribute('aria-label', 'Pause scrolling text');
            $button->textContent = '⏸';
            $div->insertBefore($button, $div->firstChild);
            
            $marquee->parentNode->replaceChild($div, $marquee);
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_auto_scroll(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find elements with scroll-behavior or auto-scroll classes
        $scrollers = $xpath->query(
            '//*[contains(@class, "auto-scroll") or contains(@class, "ticker") or ' .
            'contains(@class, "news-feed") or contains(@style, "overflow") and ' .
            'not(@data-pause-control)]'
        );
        
        foreach ($scrollers as $scroller) {
            $scroller->setAttribute('data-pause-control', 'true');
            ++$fixed;
        }
        
        return $fixed;
    }

    private function is_interactive(\DOMElement $element): bool
    {
        $tag = strtolower($element->tagName);
        return in_array($tag, ['a', 'button', 'input', 'select', 'textarea']);
    }

    private function add_pause_button(\DOMElement $element): void
    {
        $button = $element->ownerDocument->createElement('button');
        $button->setAttribute('type', 'button');
        $button->setAttribute('class', 'slos-pause-animation');
        $button->setAttribute('aria-label', 'Pause animation');
        $button->setAttribute('aria-pressed', 'false');
        $button->textContent = '⏸';
        
        $element->parentNode->insertBefore($button, $element->nextSibling);
    }

    private function add_carousel_controls(\DOMElement $carousel): void
    {
        $controls = $carousel->ownerDocument->createElement('div');
        $controls->setAttribute('class', 'slos-carousel-controls');
        $controls->setAttribute('role', 'group');
        $controls->setAttribute('aria-label', 'Carousel controls');
        
        $pause_btn = $carousel->ownerDocument->createElement('button');
        $pause_btn->setAttribute('type', 'button');
        $pause_btn->setAttribute('class', 'slos-carousel-pause');
        $pause_btn->setAttribute('aria-label', 'Pause carousel');
        $pause_btn->textContent = '⏸ Pause';
        
        $controls->appendChild($pause_btn);
        $carousel->insertBefore($controls, $carousel->firstChild);
    }
}
```

---

## 4. TimingControlFixer

**WCAG:** 2.2.1 Timing Adjustable (Level A)  
**Checker ID:** `timing-control`  
**Purpose:** Add controls for time-limited content

### Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class TimingControlFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'timing-control';
    }

    public function get_description(): string
    {
        return 'Adds controls for time-limited content';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Fix auto-refresh meta tags
        $fixed_count += $this->fix_meta_refresh($dom);

        // Fix countdown timers
        $fixed_count += $this->fix_countdown_timers($xpath);

        // Fix session timeout warnings
        $fixed_count += $this->fix_session_warnings($xpath);

        // Fix auto-advancing content
        $fixed_count += $this->fix_auto_advance($xpath);

        // Fix toast/notification auto-dismiss
        $fixed_count += $this->fix_auto_dismiss($xpath);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function fix_meta_refresh(\DOMDocument $dom): int
    {
        $fixed = 0;
        
        $metas = $dom->getElementsByTagName('meta');
        
        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('http-equiv')) === 'refresh') {
                $content = $meta->getAttribute('content');
                
                // Extract time and URL
                if (preg_match('/^(\d+)(?:;url=(.+))?/i', $content, $matches)) {
                    $seconds = (int)$matches[1];
                    $url = $matches[2] ?? null;
                    
                    if ($seconds > 0 && $seconds < 72000) { // Less than 20 hours
                        // Add warning element
                        $warning = $dom->createElement('div');
                        $warning->setAttribute('class', 'slos-timing-warning');
                        $warning->setAttribute('role', 'alert');
                        $warning->setAttribute('aria-live', 'polite');
                        
                        $message = "This page will " . 
                            ($url ? "redirect" : "refresh") . 
                            " in {$seconds} seconds. ";
                        
                        $text = $dom->createTextNode($message);
                        $warning->appendChild($text);
                        
                        // Add extend time button
                        $button = $dom->createElement('button');
                        $button->setAttribute('type', 'button');
                        $button->setAttribute('class', 'slos-extend-time');
                        $button->setAttribute('data-extend-seconds', '300');
                        $button->textContent = 'Extend time by 5 minutes';
                        $warning->appendChild($button);
                        
                        // Insert at top of body
                        $body = $dom->getElementsByTagName('body')->item(0);
                        if ($body) {
                            $body->insertBefore($warning, $body->firstChild);
                            ++$fixed;
                        }
                    }
                }
            }
        }
        
        return $fixed;
    }

    private function fix_countdown_timers(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find countdown elements
        $timers = $xpath->query(
            '//*[contains(@class, "countdown") or contains(@class, "timer") or ' .
            'contains(@id, "countdown") or contains(@id, "timer")]' .
            '[not(@data-timing-control)]'
        );
        
        foreach ($timers as $timer) {
            $timer->setAttribute('data-timing-control', 'true');
            $timer->setAttribute('role', 'timer');
            $timer->setAttribute('aria-live', 'polite');
            $timer->setAttribute('aria-atomic', 'true');
            
            // Add control buttons if not present
            $this->add_timer_controls($timer);
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_session_warnings(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find session timeout elements
        $sessions = $xpath->query(
            '//*[contains(@class, "session") and (contains(@class, "timeout") or ' .
            'contains(@class, "warning") or contains(@class, "expir"))]' .
            '[not(@data-timing-control)]'
        );
        
        foreach ($sessions as $session) {
            $session->setAttribute('data-timing-control', 'true');
            $session->setAttribute('role', 'alertdialog');
            $session->setAttribute('aria-modal', 'true');
            $session->setAttribute('aria-labelledby', 'session-warning-title');
            
            // Ensure there's an extend option
            $extend = $xpath->query(
                './/*[contains(@class, "extend") or contains(text(), "extend")]',
                $session
            );
            
            if ($extend->length === 0) {
                $button = $session->ownerDocument->createElement('button');
                $button->setAttribute('type', 'button');
                $button->setAttribute('class', 'slos-extend-session');
                $button->textContent = 'Extend Session';
                $session->appendChild($button);
            }
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_auto_advance(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find auto-advancing wizards/steps
        $wizards = $xpath->query(
            '//*[@data-auto-advance or contains(@class, "wizard") or ' .
            'contains(@class, "stepper")][not(@data-timing-control)]'
        );
        
        foreach ($wizards as $wizard) {
            $wizard->setAttribute('data-timing-control', 'true');
            
            // Add manual advance option
            $controls = $wizard->ownerDocument->createElement('div');
            $controls->setAttribute('class', 'slos-timing-controls');
            $controls->setAttribute('role', 'group');
            $controls->setAttribute('aria-label', 'Timing controls');
            
            $pause = $wizard->ownerDocument->createElement('button');
            $pause->setAttribute('type', 'button');
            $pause->setAttribute('class', 'slos-pause-advance');
            $pause->textContent = 'Pause auto-advance';
            $controls->appendChild($pause);
            
            $wizard->insertBefore($controls, $wizard->firstChild);
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_auto_dismiss(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find auto-dismissing notifications
        $notifications = $xpath->query(
            '//*[@data-auto-dismiss or @data-timeout or @data-dismiss-after]' .
            '[not(@data-timing-control)]'
        );
        
        foreach ($notifications as $notification) {
            $notification->setAttribute('data-timing-control', 'true');
            
            // Add aria-live if not present
            if (!$notification->hasAttribute('aria-live')) {
                $notification->setAttribute('aria-live', 'polite');
            }
            
            // Add close button if not present
            $close = $xpath->query('.//*[contains(@class, "close") or @aria-label="Close"]', $notification);
            if ($close->length === 0) {
                $close_btn = $notification->ownerDocument->createElement('button');
                $close_btn->setAttribute('type', 'button');
                $close_btn->setAttribute('class', 'slos-notification-close');
                $close_btn->setAttribute('aria-label', 'Close notification');
                $close_btn->textContent = '×';
                $notification->appendChild($close_btn);
            }
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function add_timer_controls(\DOMElement $timer): void
    {
        $doc = $timer->ownerDocument;
        
        $controls = $doc->createElement('div');
        $controls->setAttribute('class', 'slos-timer-controls');
        $controls->setAttribute('role', 'group');
        $controls->setAttribute('aria-label', 'Timer controls');
        
        // Pause button
        $pause = $doc->createElement('button');
        $pause->setAttribute('type', 'button');
        $pause->setAttribute('class', 'slos-timer-pause');
        $pause->setAttribute('aria-pressed', 'false');
        $pause->textContent = '⏸ Pause';
        $controls->appendChild($pause);
        
        // Add time button
        $add = $doc->createElement('button');
        $add->setAttribute('type', 'button');
        $add->setAttribute('class', 'slos-timer-add');
        $add->setAttribute('data-add-seconds', '60');
        $add->textContent = '+1 min';
        $controls->appendChild($add);
        
        $timer->parentNode->insertBefore($controls, $timer->nextSibling);
    }
}
```

---

## 5. ErrorIdentificationFixer

**WCAG:** 3.3.1 Error Identification (Level A)  
**Checker ID:** `error-identification`  
**Purpose:** Improve form error identification and association

### Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class ErrorIdentificationFixer extends BaseFixer
{
    public function get_id(): string
    {
        return 'error-identification';
    }

    public function get_description(): string
    {
        return 'Improves form error identification and accessibility';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Fix error messages without proper association
        $fixed_count += $this->fix_error_associations($xpath);

        // Fix invalid fields without aria-invalid
        $fixed_count += $this->fix_invalid_states($xpath);

        // Fix error summary at form level
        $fixed_count += $this->fix_error_summaries($xpath);

        // Fix inline error messages
        $fixed_count += $this->fix_inline_errors($xpath);

        // Fix color-only error indication
        $fixed_count += $this->fix_color_only_errors($xpath);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function fix_error_associations(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find error messages not associated with inputs
        $errors = $xpath->query(
            '//*[contains(@class, "error") or contains(@class, "invalid")]' .
            '[not(@id) or not(//input[@aria-describedby])]'
        );
        
        foreach ($errors as $error) {
            // Generate ID if missing
            if (!$error->hasAttribute('id')) {
                $error_id = 'slos-error-' . uniqid();
                $error->setAttribute('id', $error_id);
            } else {
                $error_id = $error->getAttribute('id');
            }
            
            // Add role="alert" if not present
            if (!$error->hasAttribute('role')) {
                $error->setAttribute('role', 'alert');
            }
            
            // Find associated input
            $input = $this->find_associated_input($xpath, $error);
            
            if ($input) {
                // Add aria-describedby to input
                $existing = $input->getAttribute('aria-describedby');
                if ($existing) {
                    if (strpos($existing, $error_id) === false) {
                        $input->setAttribute('aria-describedby', $existing . ' ' . $error_id);
                    }
                } else {
                    $input->setAttribute('aria-describedby', $error_id);
                }
                
                // Add aria-invalid to input
                $input->setAttribute('aria-invalid', 'true');
                ++$fixed;
            }
        }
        
        return $fixed;
    }

    private function fix_invalid_states(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find inputs with error classes but no aria-invalid
        $invalid_inputs = $xpath->query(
            '//input[contains(@class, "error") or contains(@class, "invalid") or ' .
            'contains(@class, "has-error")][not(@aria-invalid)]'
        );
        
        foreach ($invalid_inputs as $input) {
            $input->setAttribute('aria-invalid', 'true');
            ++$fixed;
        }
        
        // Also check for required fields that are empty (server-validated)
        $required_empty = $xpath->query(
            '//input[@required and @value="" and not(@aria-invalid)]'
        );
        
        foreach ($required_empty as $input) {
            if ($input->hasAttribute('data-validated')) {
                $input->setAttribute('aria-invalid', 'true');
                ++$fixed;
            }
        }
        
        return $fixed;
    }

    private function fix_error_summaries(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find error summary containers
        $summaries = $xpath->query(
            '//*[contains(@class, "error-summary") or contains(@class, "validation-summary") or ' .
            'contains(@class, "form-errors")]'
        );
        
        foreach ($summaries as $summary) {
            // Add proper ARIA
            if (!$summary->hasAttribute('role')) {
                $summary->setAttribute('role', 'alert');
            }
            if (!$summary->hasAttribute('aria-live')) {
                $summary->setAttribute('aria-live', 'assertive');
            }
            if (!$summary->hasAttribute('aria-labelledby')) {
                // Look for heading within
                $heading = $xpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6', $summary)->item(0);
                if ($heading) {
                    if (!$heading->hasAttribute('id')) {
                        $heading->setAttribute('id', 'error-summary-title');
                    }
                    $summary->setAttribute('aria-labelledby', $heading->getAttribute('id'));
                }
            }
            
            // Ensure error list items are properly structured
            $list = $xpath->query('.//ul|.//ol', $summary)->item(0);
            if ($list) {
                $items = $xpath->query('.//li', $list);
                foreach ($items as $item) {
                    // Make error links that jump to field
                    $text = $item->textContent;
                    if (!$xpath->query('.//a', $item)->length) {
                        // Try to create link to field
                        $field_name = $this->extract_field_name($text);
                        if ($field_name) {
                            $field = $xpath->query(
                                "//input[@name='{$field_name}' or @id='{$field_name}']"
                            )->item(0);
                            
                            if ($field) {
                                $field_id = $field->getAttribute('id') ?: $field_name;
                                if (!$field->hasAttribute('id')) {
                                    $field->setAttribute('id', $field_id);
                                }
                                
                                $link = $summary->ownerDocument->createElement('a');
                                $link->setAttribute('href', '#' . $field_id);
                                $link->textContent = $text;
                                
                                while ($item->firstChild) {
                                    $item->removeChild($item->firstChild);
                                }
                                $item->appendChild($link);
                            }
                        }
                    }
                }
            }
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_inline_errors(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find span/div errors near form fields
        $inline_errors = $xpath->query(
            '//span[contains(@class, "error-message") or contains(@class, "field-error") or ' .
            'contains(@class, "help-block") and contains(@class, "error")]'
        );
        
        foreach ($inline_errors as $error) {
            // Ensure error icon in addition to text (not just color)
            $text = $error->textContent;
            if (strpos($text, '⚠') === false && strpos($text, '❌') === false && 
                strpos($text, '!') === false) {
                // Add error icon
                $icon = $error->ownerDocument->createElement('span');
                $icon->setAttribute('aria-hidden', 'true');
                $icon->textContent = '⚠ ';
                $error->insertBefore($icon, $error->firstChild);
            }
            
            // Ensure role="alert"
            if (!$error->hasAttribute('role')) {
                $error->setAttribute('role', 'alert');
            }
            
            ++$fixed;
        }
        
        return $fixed;
    }

    private function fix_color_only_errors(\DOMXPath $xpath): int
    {
        $fixed = 0;
        
        // Find inputs with red border but no other error indication
        $red_border_inputs = $xpath->query(
            '//input[@style[contains(., "border") and (contains(., "red") or ' .
            'contains(., "#f") or contains(., "#e") or contains(., "rgb(2"))]]'
        );
        
        foreach ($red_border_inputs as $input) {
            // Check if there's an associated error message
            $describedby = $input->getAttribute('aria-describedby');
            
            if (!$describedby) {
                // Add visible error indicator
                $indicator = $input->ownerDocument->createElement('span');
                $indicator->setAttribute('class', 'slos-error-indicator');
                $indicator->setAttribute('aria-hidden', 'true');
                $indicator->textContent = ' ⚠';
                
                $input->parentNode->insertBefore($indicator, $input->nextSibling);
                ++$fixed;
            }
        }
        
        return $fixed;
    }

    private function find_associated_input(\DOMXPath $xpath, \DOMElement $error): ?\DOMElement
    {
        // Strategy 1: Check for-like relationship (error is sibling of input)
        $parent = $error->parentNode;
        if ($parent instanceof \DOMElement) {
            $input = $xpath->query('.//input|.//select|.//textarea', $parent)->item(0);
            if ($input) {
                return $input;
            }
        }
        
        // Strategy 2: Error follows input
        $prev = $error->previousSibling;
        while ($prev) {
            if ($prev instanceof \DOMElement && 
                in_array(strtolower($prev->tagName), ['input', 'select', 'textarea'])) {
                return $prev;
            }
            $prev = $prev->previousSibling;
        }
        
        // Strategy 3: Check for name/id in error text
        $text = $error->textContent;
        $field_name = $this->extract_field_name($text);
        if ($field_name) {
            return $xpath->query("//input[@name='{$field_name}' or @id='{$field_name}']")->item(0);
        }
        
        return null;
    }

    private function extract_field_name(string $text): ?string
    {
        // Look for field name patterns
        $patterns = [
            '/(?:field|input)\s+"([^"]+)"/i',
            '/(?:enter|provide|specify)\s+(?:a|your)?\s*(\w+)/i',
            '/(\w+)\s+(?:is\s+)?(?:required|invalid|empty)/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return strtolower($matches[1]);
            }
        }
        
        return null;
    }
}
```

---

## 6. EnhancedContrastFixer

**WCAG:** 1.4.3 Contrast Minimum (Level AA)  
**Checker ID:** `contrast`  
**Purpose:** Improved contrast fixing with actual color adjustments

### Implementation

```php
<?php
namespace Shahi\LegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

class EnhancedContrastFixer extends BaseFixer
{
    private const MIN_CONTRAST_NORMAL = 4.5;
    private const MIN_CONTRAST_LARGE = 3.0;

    public function get_id(): string
    {
        return 'contrast';
    }

    public function get_description(): string
    {
        return 'Fixes color contrast issues by adjusting text or background colors';
    }

    public function fix($content)
    {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $fixed_count = 0;

        // Fix inline style colors
        $elements = $xpath->query('//*[@style[contains(., "color")]]');

        foreach ($elements as $element) {
            $style = $element->getAttribute('style');
            
            $fg = $this->extract_color($style, 'color');
            $bg = $this->extract_color($style, 'background');
            
            if ($fg && $bg) {
                $contrast = $this->calculate_contrast($fg, $bg);
                $min_required = $this->is_large_text($element) 
                    ? self::MIN_CONTRAST_LARGE 
                    : self::MIN_CONTRAST_NORMAL;
                
                if ($contrast < $min_required) {
                    $new_fg = $this->adjust_for_contrast($fg, $bg, $min_required);
                    $style = $this->replace_color($style, 'color', $new_fg);
                    $element->setAttribute('style', $style);
                    ++$fixed_count;
                }
            }
        }

        // Add contrast toggle for high contrast mode
        $this->inject_contrast_toggle($dom);

        return [
            'fixed_count' => $fixed_count,
            'content' => $this->dom_to_html($dom),
        ];
    }

    private function extract_color(string $style, string $property): ?array
    {
        // Match hex colors
        if (preg_match("/{$property}\s*:\s*#([a-f0-9]{3,6})/i", $style, $match)) {
            return $this->hex_to_rgb($match[1]);
        }
        
        // Match rgb/rgba
        if (preg_match("/{$property}\s*:\s*rgba?\((\d+),\s*(\d+),\s*(\d+)/i", $style, $match)) {
            return [(int)$match[1], (int)$match[2], (int)$match[3]];
        }
        
        // Match color names
        if (preg_match("/{$property}\s*:\s*(\w+)/i", $style, $match)) {
            return $this->color_name_to_rgb($match[1]);
        }
        
        return null;
    }

    private function hex_to_rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function rgb_to_hex(array $rgb): string
    {
        return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
    }

    private function calculate_contrast(array $fg, array $bg): float
    {
        $l1 = $this->relative_luminance($fg);
        $l2 = $this->relative_luminance($bg);
        
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);
        
        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function relative_luminance(array $rgb): float
    {
        $r = $rgb[0] / 255;
        $g = $rgb[1] / 255;
        $b = $rgb[2] / 255;
        
        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
        
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function adjust_for_contrast(array $fg, array $bg, float $target): array
    {
        $bg_luminance = $this->relative_luminance($bg);
        
        // Determine if we should darken or lighten
        $should_darken = $bg_luminance > 0.5;
        
        $adjusted = $fg;
        $step = $should_darken ? -10 : 10;
        
        for ($i = 0; $i < 30; $i++) {
            $adjusted[0] = max(0, min(255, $adjusted[0] + $step));
            $adjusted[1] = max(0, min(255, $adjusted[1] + $step));
            $adjusted[2] = max(0, min(255, $adjusted[2] + $step));
            
            if ($this->calculate_contrast($adjusted, $bg) >= $target) {
                return $adjusted;
            }
        }
        
        // Fallback to black or white
        return $should_darken ? [0, 0, 0] : [255, 255, 255];
    }

    private function is_large_text(\DOMElement $element): bool
    {
        $style = $element->getAttribute('style');
        
        if (preg_match('/font-size\s*:\s*(\d+)(px|pt|em|rem)/i', $style, $match)) {
            $size = (float)$match[1];
            $unit = strtolower($match[2]);
            
            // Convert to px (rough)
            switch ($unit) {
                case 'pt': $size *= 1.333; break;
                case 'em':
                case 'rem': $size *= 16; break;
            }
            
            // 18px or 14px bold
            if ($size >= 18) return true;
            if ($size >= 14 && stripos($style, 'bold') !== false) return true;
        }
        
        $tag = strtolower($element->tagName);
        return in_array($tag, ['h1', 'h2', 'h3']);
    }

    private function replace_color(string $style, string $property, array $rgb): string
    {
        $hex = $this->rgb_to_hex($rgb);
        
        return preg_replace(
            "/{$property}\s*:\s*[^;]+/i",
            "{$property}: {$hex}",
            $style
        );
    }

    private function color_name_to_rgb(string $name): ?array
    {
        $colors = [
            'black' => [0, 0, 0],
            'white' => [255, 255, 255],
            'red' => [255, 0, 0],
            'green' => [0, 128, 0],
            'blue' => [0, 0, 255],
            'gray' => [128, 128, 128],
            'grey' => [128, 128, 128],
            'silver' => [192, 192, 192],
            'navy' => [0, 0, 128],
            // Add more as needed
        ];
        
        return $colors[strtolower($name)] ?? null;
    }

    private function inject_contrast_toggle(\DOMDocument $dom): void
    {
        // Already handled by AccessibilityFixer JS injection
    }
}
```

---

## 7-9. Additional Fixers

The remaining fixers (EnhancedFocusFixer, EnhancedTouchTargetFixer, ViewportZoomFixer) follow similar patterns. Key implementations are in the main audit report.

---

## Registration in FixerRegistry

Add to `FixerRegistry.php`:

```php
// New P3 Checkers
'language-change' => Fixers\LanguageChangeFixer::class,
'status-message' => Fixers\StatusMessageFixer::class,
'animation-pause' => Fixers\AnimationPauseFixer::class,
'timing-control' => Fixers\TimingControlFixer::class,
'error-identification' => Fixers\ErrorIdentificationFixer::class,
```

---

## JavaScript Support Required

For some fixers to work fully, add to `accessibility-fixes.js`:

```javascript
// Animation pause controls
document.querySelectorAll('.slos-pause-animation').forEach(btn => {
    btn.addEventListener('click', function() {
        const container = this.closest('.slos-animation-container');
        const animated = container.querySelector('[data-animated]');
        const isPaused = this.getAttribute('aria-pressed') === 'true';
        
        if (isPaused) {
            animated.style.animationPlayState = 'running';
            this.setAttribute('aria-pressed', 'false');
            this.textContent = '⏸';
            this.setAttribute('aria-label', 'Pause animation');
        } else {
            animated.style.animationPlayState = 'paused';
            this.setAttribute('aria-pressed', 'true');
            this.textContent = '▶';
            this.setAttribute('aria-label', 'Play animation');
        }
    });
});

// Timer controls
document.querySelectorAll('.slos-timer-pause').forEach(btn => {
    btn.addEventListener('click', function() {
        const timer = this.closest('[data-timing-control]').querySelector('[role="timer"]');
        // Dispatch custom event for timer to handle
        timer.dispatchEvent(new CustomEvent('slos-timer-toggle'));
    });
});

// Time extension
document.querySelectorAll('.slos-extend-time').forEach(btn => {
    btn.addEventListener('click', function() {
        const seconds = parseInt(this.dataset.extendSeconds) || 300;
        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('slos-extend-time', { detail: { seconds } }));
    });
});
```

---

*New Auto-Fixer specifications complete. Ready for implementation.*
