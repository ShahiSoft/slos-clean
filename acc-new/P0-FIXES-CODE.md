# Priority 0 Fixes - Implementation Code

This file contains ready-to-implement code fixes for the critical P0 issues.

---

## 1. SkipLinkCheck.php - FIXED VERSION

Replace the entire file content with:

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for skip navigation links
 * WCAG 2.4.1 - Bypass Blocks (Level A)
 */
class SkipLinkCheck extends AbstractCheck {

    public function get_id() {
        return 'skip-link';
    }

    public function get_description() {
        return 'Pages should have a "Skip to Content" link for keyboard users.';
    }

    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '2.4.1';
    }

    public function get_wcag_level() {
        return 'A';
    }

    public function get_remediation_hint() {
        return 'Add a skip link as the first focusable element: <a href="#main-content" class="skip-link">Skip to content</a>';
    }

    public function get_confidence() {
        return 'likely'; // May not find if in theme header
    }

    public function check( $content ) {
        $issues = array();
        
        // Skip check for very short content (likely a fragment, not full page)
        // Skip links are typically in the theme header, not post content
        if ( strlen( $content ) < 500 ) {
            return $issues;
        }
        
        $dom   = $this->get_dom( $content );
        $links = $dom->getElementsByTagName( 'a' );

        $hasSkipLink = false;
        $skipLinkPatterns = array(
            'skip to content',
            'skip to main',
            'skip navigation',
            'skip to nav',
            'jump to content',
            'jump to main',
            'skip link',
            'go to content',
            'go to main',
        );

        foreach ( $links as $link ) {
            $href = $link->getAttribute( 'href' );
            $text = strtolower( trim( $link->textContent ) );
            $ariaLabel = strtolower( $link->getAttribute( 'aria-label' ) );

            // Check if it's an internal anchor link
            if ( strpos( $href, '#' ) !== 0 ) {
                continue;
            }

            // Check link text or aria-label
            foreach ( $skipLinkPatterns as $pattern ) {
                if ( strpos( $text, $pattern ) !== false || strpos( $ariaLabel, $pattern ) !== false ) {
                    $hasSkipLink = true;
                    
                    // Verify the target exists
                    $targetId = ltrim( $href, '#' );
                    if ( ! empty( $targetId ) ) {
                        $xpath = new \DOMXPath( $dom );
                        $target = $xpath->query( "//*[@id='$targetId']" );
                        
                        if ( $target->length === 0 ) {
                            $issues[] = array(
                                'element' => 'a',
                                'context' => $dom->saveHTML( $link ),
                                'message' => "Skip link target '#$targetId' does not exist in the document.",
                            );
                        }
                    }
                    break 2;
                }
            }
        }

        // Check if this appears to be a full page (has <html> or <body>)
        $isFullPage = $dom->getElementsByTagName( 'html' )->length > 0 
                   || $dom->getElementsByTagName( 'body' )->length > 0;

        if ( ! $hasSkipLink && $isFullPage ) {
            $issues[] = array(
                'element' => 'page',
                'context' => 'Document structure',
                'message' => 'No "Skip to Content" link found. Add a skip link at the beginning of the page for keyboard users to bypass repetitive navigation.',
            );
        }

        return $issues;
    }
}
```

---

## 2. TextColorContrastCheck.php - ENHANCED VERSION

Replace the entire file content with:

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for text color contrast
 * WCAG 1.4.3 - Contrast (Minimum) (Level AA)
 */
class TextColorContrastCheck extends AbstractCheck {

    /**
     * Named CSS colors mapped to RGB values
     */
    private $named_colors = array(
        'black'   => array( 0, 0, 0 ),
        'white'   => array( 255, 255, 255 ),
        'red'     => array( 255, 0, 0 ),
        'green'   => array( 0, 128, 0 ),
        'blue'    => array( 0, 0, 255 ),
        'yellow'  => array( 255, 255, 0 ),
        'orange'  => array( 255, 165, 0 ),
        'purple'  => array( 128, 0, 128 ),
        'gray'    => array( 128, 128, 128 ),
        'grey'    => array( 128, 128, 128 ),
        'navy'    => array( 0, 0, 128 ),
        'teal'    => array( 0, 128, 128 ),
        'maroon'  => array( 128, 0, 0 ),
        'silver'  => array( 192, 192, 192 ),
        'lime'    => array( 0, 255, 0 ),
        'aqua'    => array( 0, 255, 255 ),
        'fuchsia' => array( 255, 0, 255 ),
        'olive'   => array( 128, 128, 0 ),
        // Add more as needed
    );

    public function get_id() {
        return 'text-color-contrast';
    }

    public function get_description() {
        return 'Text must have sufficient contrast against its background (AA: 4.5:1 for normal text, 3:1 for large text).';
    }

    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '1.4.3';
    }

    public function get_wcag_level() {
        return 'AA';
    }

    public function get_remediation_hint() {
        return 'Increase the contrast between text color and background color. Use a contrast checker tool to verify ratios.';
    }

    public function check( $content ) {
        $issues = array();
        $dom    = $this->get_dom( $content );
        $xpath  = new \DOMXPath( $dom );

        // 1. Check elements with inline styles
        $this->check_inline_styles( $xpath, $issues );

        // 2. Check <style> tags for low contrast combinations
        $this->check_style_tags( $dom, $issues );

        return $issues;
    }

    /**
     * Check inline styles for contrast issues
     */
    private function check_inline_styles( $xpath, &$issues ) {
        $elements = $xpath->query( '//*[@style]' );

        foreach ( $elements as $element ) {
            $style    = $element->getAttribute( 'style' );
            $color    = $this->extract_color( $style, 'color' );
            $bg_color = $this->extract_color( $style, 'background-color' );

            // Also check shorthand 'background'
            if ( ! $bg_color ) {
                $bg_color = $this->extract_color( $style, 'background' );
            }

            if ( $color && $bg_color ) {
                $ratio    = $this->calculate_contrast_ratio( $color, $bg_color );
                $is_large = $this->is_large_text( $element, $style );
                $required = $is_large ? 3.0 : 4.5;

                if ( $ratio < $required ) {
                    $issues[] = array(
                        'element' => $element->tagName,
                        'context' => $this->get_element_html( $element ),
                        'message' => sprintf(
                            'Insufficient contrast ratio (%.2f:1). Required: %.1f:1 for %s text.',
                            $ratio,
                            $required,
                            $is_large ? 'large' : 'normal'
                        ),
                    );
                }
            }
        }
    }

    /**
     * Check <style> tags for common low-contrast patterns
     */
    private function check_style_tags( $dom, &$issues ) {
        $styles = $dom->getElementsByTagName( 'style' );

        foreach ( $styles as $style ) {
            $css = $style->textContent;

            // Extract color declarations from CSS
            // This is a simplified check - full CSS parsing would require a library
            preg_match_all( '/([^{}]+)\{([^}]+)\}/s', $css, $matches, PREG_SET_ORDER );

            foreach ( $matches as $match ) {
                $selector = trim( $match[1] );
                $rules    = $match[2];

                $color    = $this->extract_color( $rules, 'color' );
                $bg_color = $this->extract_color( $rules, 'background-color' );

                if ( ! $bg_color ) {
                    $bg_color = $this->extract_color( $rules, 'background' );
                }

                if ( $color && $bg_color ) {
                    $ratio = $this->calculate_contrast_ratio( $color, $bg_color );

                    // Use 4.5:1 as default (normal text)
                    if ( $ratio < 4.5 ) {
                        $issues[] = array(
                            'element' => 'style',
                            'context' => "CSS selector: $selector",
                            'message' => sprintf(
                                'Potential low contrast in CSS (%.2f:1). Verify this meets WCAG requirements.',
                                $ratio
                            ),
                            'confidence' => 'potential',
                        );
                    }
                }
            }
        }
    }

    /**
     * Extract color value from style string
     */
    private function extract_color( $style, $property ) {
        // Match property with various color formats
        $pattern = '/' . preg_quote( $property ) . '\s*:\s*([^;]+)/i';
        if ( preg_match( $pattern, $style, $matches ) ) {
            return $this->parse_color( trim( $matches[1] ) );
        }
        return null;
    }

    /**
     * Parse color string to RGB array
     */
    private function parse_color( $color_str ) {
        $color_str = strtolower( trim( $color_str ) );

        // Named color
        if ( isset( $this->named_colors[ $color_str ] ) ) {
            return $this->named_colors[ $color_str ];
        }

        // 6-digit hex (#RRGGBB)
        if ( preg_match( '/^#([a-f0-9]{6})$/i', $color_str, $matches ) ) {
            $hex = $matches[1];
            return array(
                hexdec( substr( $hex, 0, 2 ) ),
                hexdec( substr( $hex, 2, 2 ) ),
                hexdec( substr( $hex, 4, 2 ) ),
            );
        }

        // 3-digit hex (#RGB)
        if ( preg_match( '/^#([a-f0-9]{3})$/i', $color_str, $matches ) ) {
            $hex = $matches[1];
            return array(
                hexdec( $hex[0] . $hex[0] ),
                hexdec( $hex[1] . $hex[1] ),
                hexdec( $hex[2] . $hex[2] ),
            );
        }

        // 8-digit hex (#RRGGBBAA)
        if ( preg_match( '/^#([a-f0-9]{8})$/i', $color_str, $matches ) ) {
            $hex = $matches[1];
            return array(
                hexdec( substr( $hex, 0, 2 ) ),
                hexdec( substr( $hex, 2, 2 ) ),
                hexdec( substr( $hex, 4, 2 ) ),
            );
        }

        // RGB/RGBA
        if ( preg_match( '/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $color_str, $matches ) ) {
            return array(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3],
            );
        }

        // HSL/HSLA
        if ( preg_match( '/hsla?\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%/i', $color_str, $matches ) ) {
            return $this->hsl_to_rgb(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            );
        }

        return null;
    }

    /**
     * Convert HSL to RGB
     */
    private function hsl_to_rgb( $h, $s, $l ) {
        $h = $h / 360;
        $s = $s / 100;
        $l = $l / 100;

        if ( $s == 0 ) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hue_to_rgb( $p, $q, $h + 1 / 3 );
            $g = $this->hue_to_rgb( $p, $q, $h );
            $b = $this->hue_to_rgb( $p, $q, $h - 1 / 3 );
        }

        return array(
            round( $r * 255 ),
            round( $g * 255 ),
            round( $b * 255 ),
        );
    }

    private function hue_to_rgb( $p, $q, $t ) {
        if ( $t < 0 ) $t += 1;
        if ( $t > 1 ) $t -= 1;
        if ( $t < 1 / 6 ) return $p + ( $q - $p ) * 6 * $t;
        if ( $t < 1 / 2 ) return $q;
        if ( $t < 2 / 3 ) return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
        return $p;
    }

    /**
     * Calculate contrast ratio between two colors
     */
    private function calculate_contrast_ratio( $c1, $c2 ) {
        $l1 = $this->get_luminance( $c1 );
        $l2 = $this->get_luminance( $c2 );

        $lighter = max( $l1, $l2 );
        $darker  = min( $l1, $l2 );

        return round( ( $lighter + 0.05 ) / ( $darker + 0.05 ), 2 );
    }

    /**
     * Get relative luminance of a color
     */
    private function get_luminance( $rgb ) {
        $r = $rgb[0] / 255;
        $g = $rgb[1] / 255;
        $b = $rgb[2] / 255;

        $r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
        $g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
        $b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Check if text is "large" per WCAG definition
     * Large = 18pt (24px) or 14pt (18.67px) bold
     */
    private function is_large_text( $element, $style ) {
        $font_size   = 16; // Default
        $font_weight = 400; // Default

        // Extract font-size
        if ( preg_match( '/font-size\s*:\s*([\d.]+)(px|pt|em|rem)/i', $style, $matches ) ) {
            $value = (float) $matches[1];
            $unit  = strtolower( $matches[2] );

            switch ( $unit ) {
                case 'pt':
                    $font_size = $value * 1.333; // pt to px
                    break;
                case 'em':
                case 'rem':
                    $font_size = $value * 16; // Assume 16px base
                    break;
                default:
                    $font_size = $value;
            }
        }

        // Extract font-weight
        if ( preg_match( '/font-weight\s*:\s*(\d+|bold|bolder)/i', $style, $matches ) ) {
            $weight = strtolower( $matches[1] );
            if ( $weight === 'bold' || $weight === 'bolder' ) {
                $font_weight = 700;
            } elseif ( is_numeric( $weight ) ) {
                $font_weight = (int) $weight;
            }
        }

        // Check heading tags (typically large/bold)
        $tag = strtolower( $element->tagName );
        if ( in_array( $tag, array( 'h1', 'h2', 'h3' ) ) ) {
            return true;
        }

        // Large text: >= 24px OR >= 18.67px bold
        if ( $font_size >= 24 ) {
            return true;
        }
        if ( $font_size >= 18.67 && $font_weight >= 700 ) {
            return true;
        }

        return false;
    }

    private function get_element_html( $node ) {
        $html = $node->ownerDocument->saveHTML( $node );
        return strlen( $html ) > 200 ? substr( $html, 0, 200 ) . '...' : $html;
    }
}
```

---

## 3. LandmarkRoleCheck.php - ENHANCED VERSION

Replace the entire file content with:

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check for proper landmark role usage
 * WCAG 1.3.1 - Info and Relationships (Level A)
 */
class LandmarkRoleCheck extends AbstractCheck {

    /**
     * ARIA landmark roles
     */
    private $landmark_roles = array(
        'banner',
        'complementary',
        'contentinfo',
        'form',
        'main',
        'navigation',
        'region',
        'search',
    );

    /**
     * HTML5 elements with implicit landmark roles
     */
    private $implicit_landmarks = array(
        'main'    => 'main',
        'nav'     => 'navigation',
        'aside'   => 'complementary',
        'header'  => 'banner',      // Only when not nested
        'footer'  => 'contentinfo', // Only when not nested
        'form'    => 'form',        // Only with accessible name
        'section' => 'region',      // Only with accessible name
    );

    public function get_id() {
        return 'landmark-role';
    }

    public function get_description() {
        return 'Ensure landmark roles are used correctly, not duplicated without labels, and HTML5 sectioning elements are properly used.';
    }

    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '1.3.1';
    }

    public function get_wcag_level() {
        return 'A';
    }

    public function get_remediation_hint() {
        return 'Use HTML5 semantic elements (main, nav, header, footer, aside) or ARIA landmark roles. When multiple landmarks of same type exist, use aria-label to distinguish them.';
    }

    public function check( $content ) {
        $issues          = array();
        $dom             = $this->get_dom( $content );
        $xpath           = new \DOMXPath( $dom );
        $landmark_counts = array();

        // 1. Check explicit ARIA landmark roles
        $this->check_explicit_landmarks( $xpath, $landmark_counts, $issues );

        // 2. Check HTML5 implicit landmarks
        $this->check_implicit_landmarks( $xpath, $dom, $landmark_counts, $issues );

        // 3. Check for multiple main landmarks
        $this->check_multiple_main( $landmark_counts, $issues );

        // 4. Check for missing main landmark (full page only)
        $this->check_missing_main( $dom, $landmark_counts, $issues );

        return $issues;
    }

    /**
     * Check explicit role="landmark" attributes
     */
    private function check_explicit_landmarks( $xpath, &$counts, &$issues ) {
        foreach ( $this->landmark_roles as $landmark ) {
            $elements           = $xpath->query( "//*[@role='$landmark']" );
            $counts[ $landmark ] = ( $counts[ $landmark ] ?? 0 ) + $elements->length;

            // Check duplicates
            if ( $elements->length > 1 ) {
                foreach ( $elements as $element ) {
                    if ( ! $this->has_accessible_name( $element ) ) {
                        $issues[] = array(
                            'element' => $element->tagName,
                            'context' => $this->get_element_html( $element ),
                            'message' => "Multiple '$landmark' landmarks found. Use aria-label or aria-labelledby to distinguish them.",
                        );
                    }
                }
            }
        }
    }

    /**
     * Check HTML5 elements with implicit landmark semantics
     */
    private function check_implicit_landmarks( $xpath, $dom, &$counts, &$issues ) {
        foreach ( $this->implicit_landmarks as $tag => $role ) {
            // Find elements without explicit role (to avoid double counting)
            $elements = $xpath->query( "//{$tag}[not(@role)]" );

            foreach ( $elements as $element ) {
                // header/footer only count as landmarks when not nested in sectioning content
                if ( in_array( $tag, array( 'header', 'footer' ) ) ) {
                    if ( $this->is_nested_in_sectioning( $element ) ) {
                        continue; // Not a landmark when nested
                    }
                }

                // form/section need accessible name to be landmarks
                if ( in_array( $tag, array( 'form', 'section' ) ) ) {
                    if ( ! $this->has_accessible_name( $element ) ) {
                        if ( $tag === 'section' ) {
                            // Suggest adding accessible name to section
                            $issues[] = array(
                                'element'    => $tag,
                                'context'    => $this->get_element_html( $element ),
                                'message'    => '<section> without aria-label/aria-labelledby is not exposed as a landmark region. Consider adding a label or using a heading.',
                                'severity'   => 'notice',
                            );
                        }
                        continue;
                    }
                }

                $counts[ $role ] = ( $counts[ $role ] ?? 0 ) + 1;
            }

            // Check for duplicates of implicit landmarks
            $total = $counts[ $role ] ?? 0;
            if ( $total > 1 ) {
                // Re-check elements for missing labels
                $all_elements = $xpath->query( "//{$tag}[not(@role)]" );
                $unlabeled    = 0;

                foreach ( $all_elements as $element ) {
                    if ( in_array( $tag, array( 'header', 'footer' ) ) && $this->is_nested_in_sectioning( $element ) ) {
                        continue;
                    }
                    if ( ! $this->has_accessible_name( $element ) ) {
                        $unlabeled++;
                    }
                }

                if ( $unlabeled > 1 ) {
                    $issues[] = array(
                        'element' => $tag,
                        'context' => "Multiple <$tag> elements",
                        'message' => "Multiple '$role' landmarks found via <$tag> elements. Use aria-label to distinguish them.",
                    );
                }
            }
        }
    }

    /**
     * Check if element is nested inside sectioning content
     * (article, section, aside, nav)
     */
    private function is_nested_in_sectioning( $element ) {
        $sectioning_elements = array( 'article', 'section', 'aside', 'nav' );
        $parent              = $element->parentNode;

        while ( $parent && $parent instanceof \DOMElement ) {
            if ( in_array( strtolower( $parent->tagName ), $sectioning_elements ) ) {
                return true;
            }
            $parent = $parent->parentNode;
        }

        return false;
    }

    /**
     * Check if element has an accessible name
     */
    private function has_accessible_name( $element ) {
        // aria-label
        if ( $element->hasAttribute( 'aria-label' ) && trim( $element->getAttribute( 'aria-label' ) ) !== '' ) {
            return true;
        }

        // aria-labelledby
        if ( $element->hasAttribute( 'aria-labelledby' ) && trim( $element->getAttribute( 'aria-labelledby' ) ) !== '' ) {
            return true;
        }

        // title (fallback)
        if ( $element->hasAttribute( 'title' ) && trim( $element->getAttribute( 'title' ) ) !== '' ) {
            return true;
        }

        return false;
    }

    /**
     * Check for multiple main landmarks
     */
    private function check_multiple_main( $counts, &$issues ) {
        $main_count = $counts['main'] ?? 0;

        if ( $main_count > 1 ) {
            $issues[] = array(
                'element' => 'multiple',
                'context' => "Found $main_count main landmarks",
                'message' => 'A document should not have more than one visible main landmark. Use aria-hidden or hide duplicates visually.',
            );
        }
    }

    /**
     * Check if full page is missing main landmark
     */
    private function check_missing_main( $dom, $counts, &$issues ) {
        // Only check if this appears to be a full page
        $html = $dom->getElementsByTagName( 'html' );
        $body = $dom->getElementsByTagName( 'body' );

        if ( $html->length > 0 || $body->length > 0 ) {
            $main_count = $counts['main'] ?? 0;

            if ( $main_count === 0 ) {
                $issues[] = array(
                    'element'  => 'page',
                    'context'  => 'Document structure',
                    'message'  => 'No main landmark found. Use <main> or role="main" to identify the primary content area.',
                    'severity' => 'notice',
                );
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

## Usage Instructions

1. **Backup existing files** before replacing
2. Replace the contents of each file with the code above
3. Clear any opcode cache (if using)
4. Test the scanner on sample content

## Testing Commands

```bash
# Test SkipLinkCheck
# HTML with skip link (should pass)
<a href="#main">Skip to content</a>
<main id="main">Content</main>

# HTML without skip link (should fail)
<nav>Navigation</nav>
<main>Content</main>

# Test TextColorContrastCheck
# Low contrast (should fail)
<p style="color: #777; background-color: #fff;">Low contrast text</p>

# Good contrast (should pass)
<p style="color: #000; background-color: #fff;">High contrast text</p>

# Test LandmarkRoleCheck
# Good HTML5 landmarks (should pass)
<header>...</header>
<nav aria-label="Main">...</nav>
<main>...</main>
<footer>...</footer>

# Missing labels on duplicates (should fail)
<nav>Main Nav</nav>
<nav>Footer Nav</nav>
```
