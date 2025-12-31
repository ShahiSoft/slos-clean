# FixEngine v3.3.0 - SOLID Accessibility Auto-Fix System

## Overview

The FixEngine is a complete rewrite of the accessibility auto-fix system, built from scratch using SOLID principles. It provides a robust, extensible, and testable architecture for automatically fixing accessibility issues.

## Architecture

### Core Components

| Component | Responsibility |
|-----------|---------------|
| `FixerInterface` | Contract defining what a fixer must implement |
| `AbstractFixer` | Base class with Template Method pattern and DOM helpers |
| `FixResult` | Immutable value object for fix operation results |
| `FixerCollection` | Container for registered fixers |
| `FixSession` | Tracks state during multi-fixer operations |
| `FixHistoryRepository` | Database persistence layer |
| `FixEngine` | Main orchestrator |
| `Bootstrap` | Initialization and autoloading |
| `FixEngineAjaxHandler` | AJAX request handling |

### SOLID Principles Applied

1. **Single Responsibility**: Each class has one job
   - `FixResult` only represents result data
   - `FixerCollection` only manages fixer registration
   - `FixHistoryRepository` only handles database operations

2. **Open/Closed**: Open for extension, closed for modification
   - New fixers extend `AbstractFixer` without modifying the engine
   - Plugin developers can register custom fixers via hook

3. **Liskov Substitution**: All fixers are interchangeable
   - Any `FixerInterface` implementation works with the engine
   
4. **Interface Segregation**: Small, focused interfaces
   - `FixerInterface` only requires essential methods
   
5. **Dependency Inversion**: High-level modules don't depend on low-level
   - `FixEngine` depends on `FixerInterface`, not concrete fixers

## Directory Structure

```
FixEngine/
├── FixerInterface.php          # Contract for all fixers
├── FixResult.php               # Immutable result value object
├── AbstractFixer.php           # Base class with Template Method
├── FixerCollection.php         # Fixer registry container
├── FixSession.php              # Session state tracking
├── FixHistoryRepository.php    # Database persistence
├── FixEngine.php               # Main orchestrator
├── FixEngineAjaxHandler.php    # AJAX handlers
├── Bootstrap.php               # Initialization
├── README.md                   # This file
└── Fixers/                     # Concrete fixer implementations
    ├── MissingAltFixer.php
    ├── EmptyAltFixer.php
    ├── DecorativeImageFixer.php
    ├── ... (31 total fixers)
```

## Fixer Categories & Count

| Category | Count | Fixers |
|----------|-------|--------|
| Images | 5 | MissingAlt, EmptyAlt, DecorativeImage, SvgAccessibility, FigureCaption |
| Links | 3 | EmptyLink, GenericLinkText, LinkTargetBlank |
| Headings | 2 | EmptyHeading, HeadingHierarchy |
| Forms | 4 | FormLabel, Autocomplete, RequiredField, InputErrorDescription |
| Tables | 3 | TableHeader, TableCaption, TableScope |
| Structure | 2 | Landmark, ListStructure |
| Interactive | 3 | ButtonType, FocusVisible, TabIndex |
| ARIA | 1 | AriaLabel |
| Media | 3 | AudioAccessibility, VideoAccessibility, IframeAccessibility |
| Document | 4 | LanguageAttribute, DocumentTitle, MetaViewport, SkipLink |
| Visual | 1 | ColorContrast |
| **Total** | **31** | |

## Creating a New Fixer

```php
<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

final class MyNewFixer extends AbstractFixer {

    public function get_id(): string {
        return 'my_new_fixer';
    }

    public function get_name(): string {
        return __( 'My New Fixer', 'shahi-legalflowsuite' );
    }

    public function get_description(): string {
        return __( 'Fixes X accessibility issues.', 'shahi-legalflowsuite' );
    }

    public function get_wcag_criteria(): array {
        return [ '1.1.1' ]; // WCAG criteria this fixes
    }

    public function get_category(): string {
        return 'images'; // Category for grouping
    }

    public function can_fix( string $content ): bool {
        // Quick check if content has fixable elements
        return stripos( $content, '<img' ) !== false;
    }

    protected function apply_fix( string $content, array $options = [] ): FixResult {
        $doc = $this->parse_html( $content );
        
        if ( ! $doc ) {
            return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
        }

        // Use DOM helpers: $this->query(), $this->doc, etc.
        $elements = $this->query( '//img[not(@alt)]' );
        $fixes_applied = 0;

        foreach ( $elements as $element ) {
            $element->setAttribute( 'alt', 'Description' );
            $fixes_applied++;
        }

        if ( $fixes_applied === 0 ) {
            return FixResult::skipped( $this->get_id(), 'No issues found', $content );
        }

        return FixResult::success(
            $this->get_id(),
            $fixes_applied,
            $content,
            $this->get_html(),
            [ 'details' => 'Optional metadata' ]
        );
    }
}
```

## Registration

Fixers are auto-registered by `Bootstrap.php`. To add a custom fixer:

```php
add_action( 'slos_fix_engine_register_fixers', function( $collection ) {
    $collection->register( new MyCustomFixer() );
});
```

## AJAX Endpoints

| Action | Purpose |
|--------|---------|
| `slos_fix_engine_start` | Start a new fix session |
| `slos_fix_engine_apply` | Apply a single fixer |
| `slos_fix_engine_complete` | Complete and save session |
| `slos_fix_engine_status` | Get current session status |
| `slos_fix_engine_cancel` | Cancel active session |

## Database Tables

### wp_slos_fix_sessions
Stores fix session data:
- `id` - Primary key
- `post_id` - WordPress post ID
- `total_fixers` - Number of fixers in session
- `completed_fixers` - Number completed
- `fixes_applied` - Total fixes made
- `status` - pending/running/completed/cancelled
- `started_at` / `completed_at` - Timestamps
- `original_content` / `fixed_content` - Content before/after

### wp_slos_fix_history
Stores individual fix results:
- `id` - Primary key
- `session_id` - Links to session
- `post_id` - WordPress post ID
- `fixer_id` - Which fixer ran
- `status` - success/skipped/error
- `fixes_applied` - Number of fixes
- `details` - JSON metadata
- `created_at` - Timestamp

## Integration

The FixEngine integrates with:

1. **ScannerPage.php** - `get_fixer_list_for_js()` uses FixEngine for fixer list
2. **AccessibilityScanner.php** - `ajax_autofix_single_fixer()` tries FixEngine first
3. **slos-autofix-progress.js** - Frontend modal shows fixers and progress

## Testing

Run fixers manually:

```php
$engine = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap::get_engine();
$engine->initialize();

// Single fixer
$result = $engine->fix_with_fixer( $content, 'missing_alt' );

// Multiple fixers
$session = $engine->fix_with_multiple( $content, $post_id, [ 'missing_alt', 'empty_heading' ] );
```

## Changelog

### v3.3.0 (Current)
- Complete rewrite using SOLID principles
- 31 concrete fixer implementations
- Database history tracking
- Session management
- Proper error handling and logging
- Template Method pattern for consistent behavior
- Immutable value objects for results
