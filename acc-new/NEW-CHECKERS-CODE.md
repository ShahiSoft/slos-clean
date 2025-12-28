# New Checker Templates

Ready-to-use templates for new accessibility checkers.

---

## 1. LanguageChangeCheck.php

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for language changes marked with lang attribute
 * WCAG 3.1.2 - Language of Parts (Level AA)
 */
class LanguageChangeCheck extends AbstractCheck {

    /**
     * Common foreign phrases by language
     */
    private $foreign_phrases = array(
        'fr' => array(
            'c\'est la vie',
            'je ne sais quoi',
            'déjà vu',
            'bon appétit',
            'raison d\'être',
            'faux pas',
            'carte blanche',
            'cul-de-sac',
            'vis-à-vis',
            'laissez-faire',
        ),
        'la' => array(
            'et cetera',
            'vice versa',
            'ad hoc',
            'per se',
            'status quo',
            'de facto',
            'bona fide',
            'curriculum vitae',
            'modus operandi',
            'quid pro quo',
        ),
        'es' => array(
            'hasta la vista',
            'gracias',
            'por favor',
            'buenos días',
            'señor',
            'señora',
            'amigo',
        ),
        'de' => array(
            'gesundheit',
            'kindergarten',
            'zeitgeist',
            'wanderlust',
            'schadenfreude',
            'doppelgänger',
        ),
        'it' => array(
            'ciao',
            'arrivederci',
            'buongiorno',
            'grazie',
            'cappuccino',
            'piazza',
        ),
        'ja' => array(
            'karaoke',
            'tsunami',
            'origami',
            'samurai',
            'sensei',
        ),
    );

    public function get_id() {
        return 'language-change';
    }

    public function get_description() {
        return 'Language changes within content should be marked with the lang attribute.';
    }

    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '3.1.2';
    }

    public function get_wcag_level() {
        return 'AA';
    }

    public function get_remediation_hint() {
        return 'Wrap foreign language text in a span with the appropriate lang attribute: <span lang="fr">bon appétit</span>';
    }

    public function get_confidence() {
        return 'potential';
    }

    public function check( $content ) {
        $issues = array();
        $dom    = $this->get_dom( $content );
        $xpath  = new \DOMXPath( $dom );

        // Get document language
        $html_lang = '';
        $html      = $dom->getElementsByTagName( 'html' );
        if ( $html->length > 0 ) {
            $html_lang = strtolower( $html->item( 0 )->getAttribute( 'lang' ) );
        }

        // Get all text nodes
        $text_nodes = $xpath->query( '//text()[normalize-space()]' );

        foreach ( $text_nodes as $text_node ) {
            $text   = strtolower( $text_node->textContent );
            $parent = $text_node->parentNode;

            // Skip if parent or ancestor has lang attribute
            if ( $this->has_lang_attribute( $parent ) ) {
                continue;
            }

            // Check for foreign phrases
            foreach ( $this->foreign_phrases as $lang_code => $phrases ) {
                // Skip if same as document language
                if ( strpos( $html_lang, $lang_code ) === 0 ) {
                    continue;
                }

                foreach ( $phrases as $phrase ) {
                    if ( strpos( $text, $phrase ) !== false ) {
                        $issues[] = array(
                            'element'    => $parent->tagName ?? 'text',
                            'context'    => substr( $text_node->textContent, 0, 100 ),
                            'message'    => sprintf(
                                'Foreign phrase detected ("%s"). Consider wrapping in <span lang="%s">.',
                                $phrase,
                                $lang_code
                            ),
                            'confidence' => 'potential',
                        );
                        break 2; // One issue per text node
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Check if element or ancestor has lang attribute
     */
    private function has_lang_attribute( $element ) {
        while ( $element && $element instanceof \DOMElement ) {
            if ( $element->hasAttribute( 'lang' ) ) {
                return true;
            }
            $element = $element->parentNode;
        }
        return false;
    }
}
```

---

## 2. AnimationPauseCheck.php

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for animations that can be paused
 * WCAG 2.2.2 - Pause, Stop, Hide (Level A)
 */
class AnimationPauseCheck extends AbstractCheck {

    public function get_id() {
        return 'animation-pause';
    }

    public function get_description() {
        return 'Automatically moving, blinking, or scrolling content lasting more than 5 seconds must have pause/stop controls.';
    }

    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '2.2.2';
    }

    public function get_wcag_level() {
        return 'A';
    }

    public function get_remediation_hint() {
        return 'Provide a mechanism to pause, stop, or hide automatically playing content. Consider respecting prefers-reduced-motion media query.';
    }

    public function check( $content ) {
        $issues = array();
        $dom    = $this->get_dom( $content );
        $xpath  = new \DOMXPath( $dom );

        // 1. Check <style> tags for animations
        $this->check_style_animations( $dom, $issues );

        // 2. Check inline animation styles
        $this->check_inline_animations( $xpath, $issues );

        // 3. Check for auto-playing elements
        $this->check_autoplay_elements( $xpath, $issues );

        // 4. Check for carousel/slider patterns
        $this->check_carousel_patterns( $xpath, $issues );

        // 5. Check for marquee elements
        $this->check_marquee( $dom, $issues );

        return $issues;
    }

    private function check_style_animations( $dom, &$issues ) {
        $styles = $dom->getElementsByTagName( 'style' );

        foreach ( $styles as $style ) {
            $css = $style->textContent;

            // Check for infinite animations
            if ( preg_match( '/animation[^:]*:[^;]*infinite/i', $css ) ||
                 preg_match( '/animation-iteration-count\s*:\s*infinite/i', $css ) ) {
                $issues[] = array(
                    'element' => 'style',
                    'context' => 'CSS animation',
                    'message' => 'Infinite animation detected in CSS. Provide a mechanism to pause or use prefers-reduced-motion media query.',
                );
            }

            // Check for long animations (> 5s)
            if ( preg_match( '/animation-duration\s*:\s*(\d+)s/i', $css, $matches ) ) {
                if ( (int) $matches[1] > 5 ) {
                    $issues[] = array(
                        'element' => 'style',
                        'context' => 'CSS animation',
                        'message' => sprintf(
                            'Animation duration (%ds) exceeds 5 seconds. Provide pause/stop controls.',
                            (int) $matches[1]
                        ),
                    );
                }
            }

            // Check for transition durations
            if ( preg_match( '/transition[^:]*:\s*[^;]*(\d+)s/i', $css, $matches ) ) {
                if ( (int) $matches[1] > 5 ) {
                    $issues[] = array(
                        'element'  => 'style',
                        'context'  => 'CSS transition',
                        'message'  => 'Long transition detected. Consider if users need control over this.',
                        'severity' => 'notice',
                    );
                }
            }
        }
    }

    private function check_inline_animations( $xpath, &$issues ) {
        $elements = $xpath->query( '//*[@style]' );

        foreach ( $elements as $element ) {
            $style = $element->getAttribute( 'style' );

            if ( preg_match( '/animation[^:]*:[^;]*infinite/i', $style ) ) {
                $issues[] = array(
                    'element' => $element->tagName,
                    'context' => $this->get_element_html( $element ),
                    'message' => 'Element has infinite animation via inline style. Ensure users can pause it.',
                );
            }
        }
    }

    private function check_autoplay_elements( $xpath, &$issues ) {
        // Video autoplay
        $videos = $xpath->query( '//video[@autoplay]' );
        foreach ( $videos as $video ) {
            $issues[] = array(
                'element' => 'video',
                'context' => $this->get_element_html( $video ),
                'message' => 'Video with autoplay detected. Ensure users can pause/stop playback.',
            );
        }

        // Audio autoplay
        $audios = $xpath->query( '//audio[@autoplay]' );
        foreach ( $audios as $audio ) {
            $issues[] = array(
                'element' => 'audio',
                'context' => $this->get_element_html( $audio ),
                'message' => 'Audio with autoplay detected. Ensure users can pause/stop playback.',
            );
        }
    }

    private function check_carousel_patterns( $xpath, &$issues ) {
        $carousel_classes = array(
            'carousel',
            'slider',
            'slideshow',
            'slick',
            'swiper',
            'owl-carousel',
            'glide',
            'splide',
        );

        foreach ( $carousel_classes as $class ) {
            $elements = $xpath->query( "//*[contains(@class, '$class')]" );

            foreach ( $elements as $element ) {
                $issues[] = array(
                    'element'  => $element->tagName,
                    'context'  => $this->get_element_html( $element ),
                    'message'  => "Carousel/slider detected (.$class). If auto-rotating, provide pause controls and respect prefers-reduced-motion.",
                    'severity' => 'notice',
                );
                break; // One notice per pattern
            }
        }
    }

    private function check_marquee( $dom, &$issues ) {
        $marquees = $dom->getElementsByTagName( 'marquee' );

        foreach ( $marquees as $marquee ) {
            $issues[] = array(
                'element' => 'marquee',
                'context' => $this->get_element_html( $marquee ),
                'message' => '<marquee> element detected. This element is deprecated and automatically scrolling content violates WCAG 2.2.2.',
            );
        }
    }

    private function get_element_html( $node ) {
        $html = $node->ownerDocument->saveHTML( $node );
        return strlen( $html ) > 150 ? substr( $html, 0, 150 ) . '...' : $html;
    }
}
```

---

## 3. StatusMessageCheck.php

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for status messages with proper ARIA
 * WCAG 4.1.3 - Status Messages (Level AA)
 */
class StatusMessageCheck extends AbstractCheck {

    /**
     * Common status message class patterns
     */
    private $status_patterns = array(
        'alert',
        'notice',
        'notification',
        'message',
        'toast',
        'snackbar',
        'success',
        'error',
        'warning',
        'info',
        'flash',
        'feedback',
        'status',
        'update',
        'banner',
    );

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

    public function get_wcag_level() {
        return 'AA';
    }

    public function get_remediation_hint() {
        return 'Use role="alert" for important messages, role="status" for non-urgent updates, or aria-live="polite"/"assertive" for dynamic content.';
    }

    public function check( $content ) {
        $issues = array();
        $dom    = $this->get_dom( $content );
        $xpath  = new \DOMXPath( $dom );

        // Check each status pattern
        foreach ( $this->status_patterns as $pattern ) {
            $elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

            foreach ( $elements as $element ) {
                // Skip if it has appropriate ARIA
                if ( $this->has_live_region_semantics( $element ) ) {
                    continue;
                }

                // Skip if it's a static element (not likely dynamic)
                // Heuristic: empty or has data attributes suggesting JS handling
                $has_dynamic_hints = $element->hasAttribute( 'data-message' ) ||
                                     $element->hasAttribute( 'data-alert' ) ||
                                     $element->hasAttribute( 'data-notification' );

                $is_empty = trim( $element->textContent ) === '';

                if ( $has_dynamic_hints || $is_empty ) {
                    $issues[] = array(
                        'element' => $element->tagName,
                        'context' => $this->get_element_html( $element ),
                        'message' => sprintf(
                            'Element appears to be a status message container (class contains "%s") but lacks role="status/alert" or aria-live.',
                            $pattern
                        ),
                    );
                } else {
                    // Static content - lower severity
                    $issues[] = array(
                        'element'  => $element->tagName,
                        'context'  => $this->get_element_html( $element ),
                        'message'  => sprintf(
                            'Element may display status messages (class contains "%s"). If content updates dynamically, add role="status" or aria-live.',
                            $pattern
                        ),
                        'severity' => 'notice',
                    );
                }
            }
        }

        // Check for elements with incorrect live region usage
        $this->check_live_region_usage( $xpath, $issues );

        return $issues;
    }

    /**
     * Check if element has live region semantics
     */
    private function has_live_region_semantics( $element ) {
        // Explicit aria-live
        $aria_live = $element->getAttribute( 'aria-live' );
        if ( in_array( $aria_live, array( 'polite', 'assertive' ) ) ) {
            return true;
        }

        // Roles with implicit live region semantics
        $role = $element->getAttribute( 'role' );
        if ( in_array( $role, array( 'alert', 'status', 'log', 'marquee', 'timer' ) ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check for common live region mistakes
     */
    private function check_live_region_usage( $xpath, &$issues ) {
        // Check for aria-live="off" on alert containers
        $elements = $xpath->query( "//*[@aria-live='off'][contains(@class, 'alert') or contains(@class, 'error')]" );

        foreach ( $elements as $element ) {
            $issues[] = array(
                'element' => $element->tagName,
                'context' => $this->get_element_html( $element ),
                'message' => 'Alert/error container has aria-live="off" which prevents screen reader announcement.',
            );
        }

        // Check for role="alert" used for non-error messages
        $alerts = $xpath->query( "//*[@role='alert'][contains(@class, 'success') or contains(@class, 'info')]" );

        foreach ( $alerts as $alert ) {
            $issues[] = array(
                'element'  => $alert->tagName,
                'context'  => $this->get_element_html( $alert ),
                'message'  => 'Using role="alert" for non-error messages. Consider role="status" for success/info messages to avoid interrupting users.',
                'severity' => 'notice',
            );
        }
    }

    private function get_element_html( $node ) {
        $html = $node->ownerDocument->saveHTML( $node );
        return strlen( $html ) > 150 ? substr( $html, 0, 150 ) . '...' : $html;
    }
}
```

---

## 4. ErrorIdentificationCheck.php

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for proper error identification in forms
 * WCAG 3.3.1 - Error Identification (Level A)
 */
class ErrorIdentificationCheck extends AbstractCheck {

    public function get_id() {
        return 'error-identification';
    }

    public function get_description() {
        return 'When input errors are detected, the item in error must be identified and the error described in text.';
    }

    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '3.3.1';
    }

    public function get_wcag_level() {
        return 'A';
    }

    public function get_remediation_hint() {
        return 'Use aria-describedby or aria-errormessage to link form fields to their error messages. Mark invalid fields with aria-invalid="true".';
    }

    public function check( $content ) {
        $issues = array();
        $dom    = $this->get_dom( $content );
        $xpath  = new \DOMXPath( $dom );

        // 1. Check required fields for error handling
        $this->check_required_fields( $xpath, $issues );

        // 2. Check aria-invalid usage
        $this->check_aria_invalid( $xpath, $dom, $issues );

        // 3. Check error containers
        $this->check_error_containers( $xpath, $issues );

        // 4. Check for color-only error indication
        $this->check_color_only_errors( $xpath, $issues );

        return $issues;
    }

    /**
     * Check required fields have error handling setup
     */
    private function check_required_fields( $xpath, &$issues ) {
        $required = $xpath->query( '//input[@required] | //select[@required] | //textarea[@required]' );

        foreach ( $required as $field ) {
            $has_error_link = $field->hasAttribute( 'aria-describedby' ) ||
                              $field->hasAttribute( 'aria-errormessage' );

            if ( ! $has_error_link ) {
                $issues[] = array(
                    'element'  => $field->tagName,
                    'context'  => $this->get_element_html( $field ),
                    'message'  => 'Required field should have aria-describedby or aria-errormessage to link to error message element.',
                    'severity' => 'notice',
                );
            }
        }
    }

    /**
     * Check aria-invalid has associated error message
     */
    private function check_aria_invalid( $xpath, $dom, &$issues ) {
        $invalid = $xpath->query( '//*[@aria-invalid="true"]' );

        foreach ( $invalid as $field ) {
            // Must have error message linked
            $described_by   = $field->getAttribute( 'aria-describedby' );
            $error_message  = $field->getAttribute( 'aria-errormessage' );

            if ( empty( $described_by ) && empty( $error_message ) ) {
                $issues[] = array(
                    'element' => $field->tagName,
                    'context' => $this->get_element_html( $field ),
                    'message' => 'Field marked as invalid (aria-invalid="true") must have an associated error message via aria-describedby or aria-errormessage.',
                );
            } else {
                // Verify the referenced element exists
                $ref_id = ! empty( $error_message ) ? $error_message : $described_by;
                $ref_id = trim( explode( ' ', $ref_id )[0] ); // First ID if multiple

                if ( ! empty( $ref_id ) ) {
                    $ref_element = $xpath->query( "//*[@id='$ref_id']" );

                    if ( $ref_element->length === 0 ) {
                        $issues[] = array(
                            'element' => $field->tagName,
                            'context' => $this->get_element_html( $field ),
                            'message' => "Error message reference '#$ref_id' does not exist in the document.",
                        );
                    } elseif ( trim( $ref_element->item( 0 )->textContent ) === '' ) {
                        $issues[] = array(
                            'element' => $field->tagName,
                            'context' => $this->get_element_html( $field ),
                            'message' => "Error message element '#$ref_id' is empty. Provide descriptive error text.",
                        );
                    }
                }
            }
        }
    }

    /**
     * Check error containers have text content
     */
    private function check_error_containers( $xpath, &$issues ) {
        $error_patterns = array( 'error', 'invalid', 'validation-error', 'field-error' );

        foreach ( $error_patterns as $pattern ) {
            $elements = $xpath->query( "//*[contains(@class, '$pattern')]" );

            foreach ( $elements as $element ) {
                // Skip if hidden
                $style = $element->getAttribute( 'style' );
                if ( preg_match( '/display\s*:\s*none|visibility\s*:\s*hidden/i', $style ) ) {
                    continue;
                }

                // Check for empty error containers
                $text = trim( $element->textContent );
                if ( empty( $text ) ) {
                    // Check if it has aria-hidden (intentionally hidden)
                    if ( $element->getAttribute( 'aria-hidden' ) === 'true' ) {
                        continue;
                    }

                    $issues[] = array(
                        'element'  => $element->tagName,
                        'context'  => $this->get_element_html( $element ),
                        'message'  => "Error container (.$pattern) is visible but empty. Error messages must be provided in text.",
                        'severity' => 'notice',
                    );
                }
            }
        }
    }

    /**
     * Check for potential color-only error indication
     */
    private function check_color_only_errors( $xpath, &$issues ) {
        // Look for inputs with red border but no error text nearby
        $inputs = $xpath->query( '//input[@style] | //select[@style] | //textarea[@style]' );

        foreach ( $inputs as $input ) {
            $style = $input->getAttribute( 'style' );

            // Check for red/error colors in border
            if ( preg_match( '/border[^:]*:\s*[^;]*(red|#f00|#ff0000|#dc3545|#d32f2f)/i', $style ) ) {
                // Check if there's an error message linked
                if ( ! $input->hasAttribute( 'aria-describedby' ) && 
                     ! $input->hasAttribute( 'aria-errormessage' ) ) {
                    $issues[] = array(
                        'element' => $input->tagName,
                        'context' => $this->get_element_html( $input ),
                        'message' => 'Field appears to indicate error using color only (red border). Errors must also be described in text.',
                    );
                }
            }
        }
    }

    private function get_element_html( $node ) {
        $html = $node->ownerDocument->saveHTML( $node );
        return strlen( $html ) > 150 ? substr( $html, 0, 150 ) . '...' : $html;
    }
}
```

---

## 5. TimingControlCheck.php

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for time limits with user control
 * WCAG 2.2.1 - Timing Adjustable (Level A)
 */
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

    public function get_wcag_level() {
        return 'A';
    }

    public function get_remediation_hint() {
        return 'Provide a way to turn off, adjust, or extend time limits. For auto-refresh, consider using a manual refresh button instead.';
    }

    public function check( $content ) {
        $issues = array();
        $dom    = $this->get_dom( $content );
        $xpath  = new \DOMXPath( $dom );

        // 1. Check meta refresh tags
        $this->check_meta_refresh( $xpath, $issues );

        // 2. Check for session timeout indicators
        $this->check_session_timeout( $xpath, $issues );

        // 3. Check for countdown timers
        $this->check_countdown_timers( $xpath, $issues );

        return $issues;
    }

    /**
     * Check for meta refresh/redirect
     */
    private function check_meta_refresh( $xpath, &$issues ) {
        $metas = $xpath->query( '//meta[@http-equiv="refresh"]' );

        foreach ( $metas as $meta ) {
            $content_attr = $meta->getAttribute( 'content' );

            if ( preg_match( '/^(\d+)/', $content_attr, $matches ) ) {
                $seconds = (int) $matches[1];

                // 0 is immediate redirect (usually OK)
                // > 72000 (20 hours) is considered essentially unlimited
                if ( $seconds > 0 && $seconds < 72000 ) {
                    $has_url = strpos( $content_attr, 'url=' ) !== false;

                    if ( $has_url ) {
                        $issues[] = array(
                            'element' => 'meta',
                            'context' => $this->get_element_html( $meta ),
                            'message' => sprintf(
                                'Auto-redirect after %d seconds. Users must be able to extend or disable this time limit.',
                                $seconds
                            ),
                        );
                    } else {
                        $issues[] = array(
                            'element' => 'meta',
                            'context' => $this->get_element_html( $meta ),
                            'message' => sprintf(
                                'Auto-refresh every %d seconds. Consider using a manual refresh button instead.',
                                $seconds
                            ),
                        );
                    }
                }
            }
        }
    }

    /**
     * Check for session timeout UI patterns
     */
    private function check_session_timeout( $xpath, &$issues ) {
        $timeout_patterns = array(
            'session-timeout',
            'timeout-warning',
            'session-expire',
            'inactivity',
        );

        foreach ( $timeout_patterns as $pattern ) {
            $elements = $xpath->query( "//*[contains(@class, '$pattern') or contains(@id, '$pattern')]" );

            foreach ( $elements as $element ) {
                $issues[] = array(
                    'element'  => $element->tagName,
                    'context'  => $this->get_element_html( $element ),
                    'message'  => 'Session timeout indicator detected. Ensure users can extend the session before timeout.',
                    'severity' => 'notice',
                );
                break; // One notice per pattern
            }
        }
    }

    /**
     * Check for countdown timer patterns
     */
    private function check_countdown_timers( $xpath, &$issues ) {
        $timer_patterns = array(
            'countdown',
            'timer',
            'time-left',
            'expires-in',
        );

        foreach ( $timer_patterns as $pattern ) {
            $elements = $xpath->query( "//*[contains(@class, '$pattern') or contains(@id, '$pattern')]" );

            foreach ( $elements as $element ) {
                // Check if it has role="timer"
                if ( $element->getAttribute( 'role' ) !== 'timer' ) {
                    $issues[] = array(
                        'element'  => $element->tagName,
                        'context'  => $this->get_element_html( $element ),
                        'message'  => 'Countdown timer detected. Add role="timer" and ensure users can extend the time limit.',
                        'severity' => 'warning',
                    );
                } else {
                    $issues[] = array(
                        'element'  => $element->tagName,
                        'context'  => $this->get_element_html( $element ),
                        'message'  => 'Countdown timer detected. Ensure users can extend or disable the time limit.',
                        'severity' => 'notice',
                    );
                }
                break;
            }
        }
    }

    private function get_element_html( $node ) {
        $html = $node->ownerDocument->saveHTML( $node );
        return strlen( $html ) > 150 ? substr( $html, 0, 150 ) . '...' : $html;
    }
}
```

---

## Registration

Add these to `AccessibilityScanner.php` in the `register_checks()` method:

```php
// In register_checks() method, add:
$this->scanner->register_check( new LanguageChangeCheck() );
$this->scanner->register_check( new AnimationPauseCheck() );
$this->scanner->register_check( new StatusMessageCheck() );
$this->scanner->register_check( new ErrorIdentificationCheck() );
$this->scanner->register_check( new TimingControlCheck() );
```

And add the use statements at the top:

```php
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\LanguageChangeCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\AnimationPauseCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\StatusMessageCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\ErrorIdentificationCheck;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers\TimingControlCheck;
```
