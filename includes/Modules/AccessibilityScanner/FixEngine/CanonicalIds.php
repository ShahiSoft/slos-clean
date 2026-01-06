<?php
/**
 * Canonical Fixer/Checker ID Map
 * 
 * Single source of truth for all fixer and checker IDs.
 * Eliminates aliases and establishes one-to-one mapping.
 * 
 * @package ShahiLegalFlowSuite
 * @subpackage FixEngine
 * @since 3.1.2
 */

namespace ShahiLegalFlowSuite\FixEngine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Canonical ID Registry
 * 
 * Maps canonical IDs to metadata (name, severity, category, WCAG).
 */
class CanonicalIds {
    
    /**
     * Severity levels
     */
    const SEVERITY_CRITICAL = 'critical';
    const SEVERITY_SERIOUS = 'serious';
    const SEVERITY_MODERATE = 'moderate';
    const SEVERITY_MINOR = 'minor';
    
    /**
     * Categories
     */
    const CAT_IMAGES = 'images';
    const CAT_HEADINGS = 'headings';
    const CAT_LINKS = 'links';
    const CAT_FORMS = 'forms';
    const CAT_TABLES = 'tables';
    const CAT_MEDIA = 'media';
    const CAT_INTERACTIVITY = 'interactivity';
    const CAT_COLOR_CONTRAST = 'color-contrast';
    const CAT_TOUCH_VIEWPORT = 'touch-viewport';
    const CAT_ARIA = 'aria';
    const CAT_STRUCTURE = 'structure';
    const CAT_CONTENT = 'content';
    
    /**
     * Canonical ID map
     * 
     * Key: Canonical ID (used everywhere)
     * Value: Metadata array
     */
    private static $ids = [
        
        // ========== IMAGES ==========
        'missing-alt-text' => [
            'name' => 'Missing Alt Text',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'empty-alt-text' => [
            'name' => 'Empty Alt Text',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'redundant-alt-text' => [
            'name' => 'Redundant Alt Text',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'decorative-image' => [
            'name' => 'Decorative Image',
            'severity' => self::SEVERITY_MINOR,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'page-title' => [
            'name' => 'Page Title',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_STRUCTURE,
            'wcag' => ['2.4.2'],
            'auto_fixable' => true,
        ],
        'lang-attribute' => [
            'name' => 'Language Attribute',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_CONTENT,
            'wcag' => ['3.1.1', '3.1.2'],
            'auto_fixable' => true,
        ],
        'button-type' => [
            'name' => 'Button Type',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_FORMS,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'table-scope' => [
            'name' => 'Table Scope',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_TABLES,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'figure-caption' => [
            'name' => 'Figure Caption',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'list-structure' => [
            'name' => 'List Structure',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_STRUCTURE,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'complex-image' => [
            'name' => 'Complex Image',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => false,
        ],
        'svg-accessibility' => [
            'name' => 'SVG Accessibility',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'background-image' => [
            'name' => 'Background Image',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => false,
        ],
        'logo-image' => [
            'name' => 'Logo Image',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => true,
        ],
        'image-map-alt' => [
            'name' => 'Image Map Alt',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1', '2.4.4'],
            'auto_fixable' => true,
        ],
        'alt-text-quality' => [
            'name' => 'Alt Text Quality',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_IMAGES,
            'wcag' => ['1.1.1'],
            'auto_fixable' => false,
        ],
        
        // ========== HEADINGS ==========
        'missing-h1' => [
            'name' => 'Missing H1',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['2.4.6'],
            'auto_fixable' => true,
        ],
        'multiple-h1' => [
            'name' => 'Multiple H1',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['2.4.6'],
            'auto_fixable' => true,
        ],
        'empty-heading' => [
            'name' => 'Empty Heading',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['2.4.6'],
            'auto_fixable' => true,
        ],
        'skipped-heading-level' => [
            'name' => 'Skipped Heading Level',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['1.3.1', '2.4.6'],
            'auto_fixable' => true,
        ],
        'heading-nesting' => [
            'name' => 'Heading Nesting',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'heading-length' => [
            'name' => 'Heading Length',
            'severity' => self::SEVERITY_MINOR,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['2.4.6'],
            'auto_fixable' => false,
        ],
        'heading-uniqueness' => [
            'name' => 'Heading Uniqueness',
            'severity' => self::SEVERITY_MINOR,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['2.4.6'],
            'auto_fixable' => false,
        ],
        'heading-visual' => [
            'name' => 'Heading Visual',
            'severity' => self::SEVERITY_MINOR,
            'category' => self::CAT_HEADINGS,
            'wcag' => ['1.3.1'],
            'auto_fixable' => false,
        ],
        
        // ========== LINKS ==========
        'empty-link' => [
            'name' => 'Empty Link',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_LINKS,
            'wcag' => ['2.4.4', '4.1.2'],
            'auto_fixable' => true,
        ],
        'generic-link-text' => [
            'name' => 'Generic Link Text',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_LINKS,
            'wcag' => ['2.4.4', '2.4.9'],
            'auto_fixable' => true,
        ],
        'new-window-link' => [
            'name' => 'New Window Link',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_LINKS,
            'wcag' => ['3.2.5'],
            'auto_fixable' => true,
        ],
        'download-link' => [
            'name' => 'Download Link',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_LINKS,
            'wcag' => ['2.4.4'],
            'auto_fixable' => true,
        ],
        'external-link' => [
            'name' => 'External Link',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_LINKS,
            'wcag' => ['2.4.4'],
            'auto_fixable' => true,
        ],
        'link-destination' => [
            'name' => 'Link Destination',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_LINKS,
            'wcag' => ['2.4.4'],
            'auto_fixable' => false,
        ],
        'skip-link' => [
            'name' => 'Skip Link',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_LINKS,
            'wcag' => ['2.4.1'],
            'auto_fixable' => true,
        ],
        
        // ========== FORMS ==========
        'missing-form-label' => [
            'name' => 'Missing Form Label',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_FORMS,
            'wcag' => ['1.3.1', '3.3.2', '4.1.2'],
            'auto_fixable' => true,
        ],
        'fieldset-legend' => [
            'name' => 'Fieldset Legend',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_FORMS,
            'wcag' => ['1.3.1', '3.3.2'],
            'auto_fixable' => true,
        ],
        'required-attribute' => [
            'name' => 'Required Attribute',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_FORMS,
            'wcag' => ['3.3.2'],
            'auto_fixable' => true,
        ],
        'error-message' => [
            'name' => 'Error Message',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_FORMS,
            'wcag' => ['3.3.1', '3.3.3'],
            'auto_fixable' => true,
        ],
        'autocomplete-attribute' => [
            'name' => 'Autocomplete Attribute',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_FORMS,
            'wcag' => ['1.3.5'],
            'auto_fixable' => true,
        ],
        'input-type' => [
            'name' => 'Input Type',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_FORMS,
            'wcag' => ['1.3.5'],
            'auto_fixable' => true,
        ],
        'placeholder-label' => [
            'name' => 'Placeholder Label',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_FORMS,
            'wcag' => ['3.3.2'],
            'auto_fixable' => true,
        ],
        'custom-control' => [
            'name' => 'Custom Control',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_FORMS,
            'wcag' => ['4.1.2'],
            'auto_fixable' => false,
        ],
        'button-label' => [
            'name' => 'Button Label',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_FORMS,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'orphaned-label' => [
            'name' => 'Orphaned Label',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_FORMS,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'form-aria' => [
            'name' => 'Form ARIA',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_FORMS,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        
        // ========== TABLES ==========
        'table-header' => [
            'name' => 'Table Header',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_TABLES,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'table-caption' => [
            'name' => 'Table Caption',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_TABLES,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'complex-table' => [
            'name' => 'Complex Table',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_TABLES,
            'wcag' => ['1.3.1'],
            'auto_fixable' => false,
        ],
        'layout-table' => [
            'name' => 'Layout Table',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_TABLES,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'empty-table-cell' => [
            'name' => 'Empty Table Cell',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_TABLES,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        
        // ========== MEDIA ==========
        'iframe-title' => [
            'name' => 'Iframe Title',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_MEDIA,
            'wcag' => ['2.4.1', '4.1.2'],
            'auto_fixable' => true,
        ],
        'video-accessibility' => [
            'name' => 'Video Accessibility',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_MEDIA,
            'wcag' => ['1.2.1', '1.2.2', '1.2.3'],
            'auto_fixable' => false,
        ],
        'audio-accessibility' => [
            'name' => 'Audio Accessibility',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_MEDIA,
            'wcag' => ['1.2.1'],
            'auto_fixable' => false,
        ],
        'media-alternative' => [
            'name' => 'Media Alternative',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_MEDIA,
            'wcag' => ['1.2.8'],
            'auto_fixable' => false,
        ],
        
        // ========== INTERACTIVITY ==========
        'positive-tabindex' => [
            'name' => 'Positive Tabindex',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_INTERACTIVITY,
            'wcag' => ['2.4.3'],
            'auto_fixable' => true,
        ],
        'interactive-element' => [
            'name' => 'Interactive Element',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_INTERACTIVITY,
            'wcag' => ['2.1.1', '4.1.2'],
            'auto_fixable' => true,
        ],
        'modal-accessibility' => [
            'name' => 'Modal Accessibility',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_INTERACTIVITY,
            'wcag' => ['2.1.2', '2.4.3'],
            'auto_fixable' => true,
        ],
        'focus-indicator' => [
            'name' => 'Focus Indicator',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_INTERACTIVITY,
            'wcag' => ['2.4.7'],
            'auto_fixable' => true,
        ],
        'keyboard-trap' => [
            'name' => 'Keyboard Trap',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_INTERACTIVITY,
            'wcag' => ['2.1.2'],
            'auto_fixable' => true,
        ],
        'focus-order' => [
            'name' => 'Focus Order',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_INTERACTIVITY,
            'wcag' => ['2.4.3'],
            'auto_fixable' => false,
        ],
        
        // ========== COLOR/CONTRAST ==========
        'text-color-contrast' => [
            'name' => 'Text Color Contrast',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_COLOR_CONTRAST,
            'wcag' => ['1.4.3', '1.4.6'],
            'auto_fixable' => false,
        ],
        'color-reliance' => [
            'name' => 'Color Reliance',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_COLOR_CONTRAST,
            'wcag' => ['1.4.1'],
            'auto_fixable' => false,
        ],
        'complex-contrast' => [
            'name' => 'Complex Contrast',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_COLOR_CONTRAST,
            'wcag' => ['1.4.3'],
            'auto_fixable' => false,
        ],
        
        // ========== TOUCH/VIEWPORT ==========
        'touch-target' => [
            'name' => 'Touch Target',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_TOUCH_VIEWPORT,
            'wcag' => ['2.5.5'],
            'auto_fixable' => false,
        ],
        'touch-gesture' => [
            'name' => 'Touch Gesture',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_TOUCH_VIEWPORT,
            'wcag' => ['2.5.1'],
            'auto_fixable' => false,
        ],
        'viewport-check' => [
            'name' => 'Viewport Check',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_TOUCH_VIEWPORT,
            'wcag' => ['1.4.10'],
            'auto_fixable' => true,
        ],
        
        // ========== ARIA ==========
        'aria-role' => [
            'name' => 'ARIA Role',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_ARIA,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'aria-attribute' => [
            'name' => 'ARIA Attribute',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_ARIA,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'aria-state' => [
            'name' => 'ARIA State',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_ARIA,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'landmark-role' => [
            'name' => 'Landmark Role',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_ARIA,
            'wcag' => ['1.3.1', '2.4.1'],
            'auto_fixable' => true,
        ],
        'redundant-aria' => [
            'name' => 'Redundant ARIA',
            'severity' => self::SEVERITY_MINOR,
            'category' => self::CAT_ARIA,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'invalid-aria-combination' => [
            'name' => 'Invalid ARIA Combination',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_ARIA,
            'wcag' => ['4.1.2'],
            'auto_fixable' => true,
        ],
        'hidden-content' => [
            'name' => 'Hidden Content',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_ARIA,
            'wcag' => ['4.1.2'],
            'auto_fixable' => false,
        ],
        
        // ========== STRUCTURE/CONTENT ==========
        'semantic-html' => [
            'name' => 'Semantic HTML',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_STRUCTURE,
            'wcag' => ['1.3.1'],
            'auto_fixable' => true,
        ],
        'live-region' => [
            'name' => 'Live Region',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_STRUCTURE,
            'wcag' => ['4.1.3'],
            'auto_fixable' => true,
        ],
        'page-structure' => [
            'name' => 'Page Structure',
            'severity' => self::SEVERITY_MODERATE,
            'category' => self::CAT_STRUCTURE,
            'wcag' => ['1.3.1', '2.4.1'],
            'auto_fixable' => false,
        ],
        'language-change' => [
            'name' => 'Language Change',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_CONTENT,
            'wcag' => ['3.1.2'],
            'auto_fixable' => true,
        ],
        'status-message' => [
            'name' => 'Status Message',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_CONTENT,
            'wcag' => ['4.1.3'],
            'auto_fixable' => true,
        ],
        'error-identification' => [
            'name' => 'Error Identification',
            'severity' => self::SEVERITY_CRITICAL,
            'category' => self::CAT_CONTENT,
            'wcag' => ['3.3.1'],
            'auto_fixable' => true,
        ],
        'animation-pause' => [
            'name' => 'Animation Pause',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_CONTENT,
            'wcag' => ['2.2.2'],
            'auto_fixable' => true,
        ],
        'timing-control' => [
            'name' => 'Timing Control',
            'severity' => self::SEVERITY_SERIOUS,
            'category' => self::CAT_CONTENT,
            'wcag' => ['2.2.1'],
            'auto_fixable' => true,
        ],
    ];

    /**
     * Legacy/alias map -> canonical ID
     * Normalizes old IDs and variations (underscores, "missing-" prefixes, etc.).
     */
    private static $aliases = [
        'generic-link' => 'generic-link-text',
        'missing-label' => 'missing-form-label',
        'redundant-alt' => 'redundant-alt-text',
        'link-dest' => 'link-destination',
        'new-window' => 'new-window-link',
        'link-opens-new-window' => 'new-window-link',
        'image-map' => 'image-map-alt',
        'missing-image-map-alt' => 'image-map-alt',
        'alt-quality' => 'alt-text-quality',
        'svg-access' => 'svg-accessibility',
        'missing-svg-title' => 'svg-accessibility',
        'bg-image' => 'background-image',
        'heading-unique' => 'heading-uniqueness',
        'contrast' => 'text-color-contrast',
        'color_contrast' => 'text-color-contrast',
        'autocomplete' => 'autocomplete-attribute',
        'required-attr' => 'required-attribute',
        'missing-required-attribute' => 'required-attribute',
        'skipped-heading' => 'skipped-heading-level',
        'modal-access' => 'modal-accessibility',
        'widget-keyboard' => 'interactive-element',
        'aria-attr' => 'aria-attribute',
        'missing-aria-label' => 'aria-attribute',
        'invalid-aria' => 'invalid-aria-combination',
        'media-alt' => 'media-alternative',
        'video-access' => 'video-accessibility',
        'missing-video-caption' => 'video-accessibility',
        'audio-access' => 'audio-accessibility',
        'audio_accessibility' => 'audio-accessibility',
        'empty-cell' => 'empty-table-cell',
        'missing-table-scope' => 'table-scope',
        'viewport' => 'viewport-check',
        'improper-viewport' => 'viewport-check',
        'invalid-tabindex' => 'positive-tabindex',
        'missing-focus-indicator' => 'focus-indicator',
        'missing-iframe-title' => 'iframe-title',
        'missing-lang-attribute' => 'lang-attribute',
        'missing-page-title' => 'page-title',
        'missing-button-type' => 'button-type',
        'missing-figure-caption' => 'figure-caption',
        'improper-list-structure' => 'list-structure',
        'missing-fieldset-legend' => 'fieldset-legend',
        'missing-error-description' => 'error-message',
        'missing-skip-link' => 'skip-link',
    ];
    
    /**
     * Get all canonical IDs
     * 
     * @return array
     */
    public static function get_all(): array {
        return array_keys(self::$ids);
    }
    
    /**
     * Get metadata for a canonical ID
     * 
     * @param string $id
     * @return array|null
     */
    public static function get_metadata(string $id): ?array {
        $canonical = self::canonicalize($id);
        return $canonical ? self::$ids[$canonical] : null;
    }

    /**
     * Backwards-compatible getter used by some tools/CLI commands.
     *
     * @param string $id
     * @return array|null
     */
    public static function get(string $id): ?array {
        return self::get_metadata($id);
    }
    
    /**
     * Check if ID is valid
     * 
     * @param string $id
     * @return bool
     */
    public static function is_valid(string $id): bool {
        return self::canonicalize($id) !== null;
    }

    /**
     * Normalize to canonical ID (hyphenated, alias-resolved)
     */
    public static function canonicalize(string $id): ?string {
        $normalized = strtolower(str_replace('_', '-', trim($id)));

        if (isset(self::$aliases[$normalized])) {
            $normalized = self::$aliases[$normalized];
        }

        if (isset(self::$ids[$normalized])) {
            return $normalized;
        }

        return null;
    }
    
    /**
     * Get IDs by category
     * 
     * @param string $category
     * @return array
     */
    public static function get_by_category(string $category): array {
        $result = [];
        foreach (self::$ids as $id => $meta) {
            if ($meta['category'] === $category) {
                $result[] = $id;
            }
        }
        return $result;
    }
    
    /**
     * Get IDs by severity
     * 
     * @param string $severity
     * @return array
     */
    public static function get_by_severity(string $severity): array {
        $result = [];
        foreach (self::$ids as $id => $meta) {
            if ($meta['severity'] === $severity) {
                $result[] = $id;
            }
        }
        return $result;
    }
    
    /**
     * Get auto-fixable IDs
     * 
     * @return array
     */
    public static function get_auto_fixable(): array {
        $result = [];
        foreach (self::$ids as $id => $meta) {
            if ($meta['auto_fixable']) {
                $result[] = $id;
            }
        }
        return $result;
    }
    
    /**
     * Get all categories
     * 
     * @return array
     */
    public static function get_categories(): array {
        return [
            self::CAT_IMAGES => 'Images',
            self::CAT_HEADINGS => 'Headings',
            self::CAT_LINKS => 'Links',
            self::CAT_FORMS => 'Forms',
            self::CAT_TABLES => 'Tables',
            self::CAT_MEDIA => 'Media',
            self::CAT_INTERACTIVITY => 'Interactivity',
            self::CAT_COLOR_CONTRAST => 'Color & Contrast',
            self::CAT_TOUCH_VIEWPORT => 'Touch & Viewport',
            self::CAT_ARIA => 'ARIA',
            self::CAT_STRUCTURE => 'Structure',
            self::CAT_CONTENT => 'Content',
        ];
    }
    
    /**
     * Get count
     * 
     * @return int
     */
    public static function count(): int {
        return count(self::$ids);
    }
}
