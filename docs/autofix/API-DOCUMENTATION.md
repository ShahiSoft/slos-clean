# FixEngine API Documentation

**Version:** 3.3.0  
**Status:** Production Ready  
**Last Updated:** <?php echo date('Y-m-d'); ?>

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Core Components](#core-components)
- [Usage Examples](#usage-examples)
- [API Reference](#api-reference)
- [Migration Guide](#migration-guide)
- [Performance](#performance)

## Overview

The FixEngine is a modern, SOLID-compliant system for automatically fixing accessibility issues in WordPress content. It replaces the legacy `Fixes/` system with a more maintainable, testable, and extensible architecture.

### Key Features

- **🎯 Canonical IDs**: Single source of truth for all fixer identifiers
- **⚡ Dynamic Discovery**: Automatic fixer registration via filesystem scanning
- **📊 Session Tracking**: Complete audit trail of all fixes applied
- **🔒 Type Safety**: Full PHP type hints and strict typing
- **🧪 Testable**: Comprehensive test suite with >90% coverage
- **📈 Performance**: Optimized for large-scale content processing
- **🔄 Rollback**: Built-in rollback capabilities for safe migrations

## Architecture

### SOLID Principles

```
FixEngine follows SOLID design:
├── Single Responsibility: Each fixer handles one specific issue
├── Open/Closed: Extend via new fixers, not modifications
├── Liskov Substitution: All fixers implement FixerInterface
├── Interface Segregation: Minimal, focused interfaces
└── Dependency Inversion: Depends on abstractions, not concretions
```

### Component Structure

```
FixEngine/
├── Core/
│   ├── FixEngine.php           # Main orchestrator
│   ├── FixerCollection.php     # Registry with auto-discovery
│   ├── FixSession.php          # Session tracking
│   └── FixResult.php           # Result object
├── Contracts/
│   ├── FixerInterface.php      # Fixer contract
│   └── AbstractFixer.php       # Base implementation
├── Infrastructure/
│   ├── CanonicalIds.php        # ID registry (75 IDs)
│   ├── FeatureFlags.php        # Feature toggles
│   ├── Logger.php              # Structured logging
│   └── FixHistoryRepository.php # Persistence
├── Fixers/
│   ├── MissingAltFixer.php     # 31 concrete fixers
│   ├── EmptyAltFixer.php       # All extending AbstractFixer
│   └── ...
└── Migrations/
    ├── IdCanonicalizationMigration.php
    └── DatabaseSchemaMigration.php
```

## Core Components

### 1. FixEngine

Main entry point for all fixing operations.

```php
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixEngine;

$engine = new FixEngine();
$engine->initialize();

// Fix with single fixer
$result = $engine->fix_with_fixer($content, 'missing-alt-text');

// Fix with multiple fixers
$session = $engine->fix_content($content, ['missing-alt-text', 'empty-link']);

// Fix with all available fixers
$session = $engine->fix_content($content);
```

### 2. CanonicalIds

Single source of truth for fixer/checker IDs.

```php
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\CanonicalIds;

// Validate ID
if (CanonicalIds::is_valid('missing-alt-text')) {
    // ID is canonical
}

// Get metadata
$meta = CanonicalIds::get('missing-alt-text');
echo $meta['name'];      // "Missing Alt Text"
echo $meta['severity'];  // "critical"
echo $meta['category'];  // "images"

// Get by category
$image_fixers = CanonicalIds::get_by_category('images');
```

### 3. FixerCollection

Manages fixer registration with auto-discovery.

```php
$collection = new FixerCollection();

// Auto-discover all fixers from Fixers/ directory
$count = $collection->auto_discover();

// Manual registration
$collection->register(new CustomFixer());

// Retrieve
$fixer = $collection->get('missing-alt-text');
$all_fixers = $collection->all();
$image_fixers = $collection->by_category('images');
```

### 4. FixSession

Tracks fixing operations with full audit trail.

```php
$session = $engine->fix_content($content, $fixer_ids, [
    'post_id' => 123,
    'user_id' => 1,
]);

echo $session->get_session_id();
echo $session->get_content();
echo $session->get_total_changes();
echo $session->get_duration();

$fixes = $session->get_fixes_applied();
foreach ($fixes as $fixer_id => $count) {
    echo "{$fixer_id}: {$count} fixes\n";
}
```

### 5. FixResult

Represents the outcome of a single fixer application.

```php
$result = $engine->fix_with_fixer($content, 'missing-alt-text');

if ($result->is_success()) {
    $fixed = $result->get_content();
    $changes = $result->get_changes_made();
    $message = $result->get_message();
} else {
    $error = $result->get_error();
}
```

## Usage Examples

### Basic Fixing

```php
// Initialize
$engine = new FixEngine();
$engine->initialize();

// Fix missing alt text
$html = '<img src="logo.png">';
$result = $engine->fix_with_fixer($html, 'missing-alt-text');
echo $result->get_content();
// Output: <img src="logo.png" alt="logo">
```

### Batch Fixing

```php
// Fix multiple issues at once
$html = '
    <img src="logo.png">
    <a href="#">click here</a>
    <h3>Skipped H2</h3>
';

$session = $engine->fix_content($html, [
    'missing-alt-text',
    'generic-link-text',
    'skipped-heading-level',
]);

echo $session->get_total_changes(); // 3
```

### WordPress Post Integration

```php
// Fix post content
$post_id = 123;
$post = get_post($post_id);

$session = $engine->fix_content(
    $post->post_content,
    [],  // Empty = all fixers
    ['post_id' => $post_id]
);

// Update post if changes made
if ($session->get_total_changes() > 0) {
    wp_update_post([
        'ID' => $post_id,
        'post_content' => $session->get_content(),
    ]);
}
```

### Custom Fixer Development

```php
namespace YourPlugin\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

final class CustomFixer extends AbstractFixer {
    
    public function get_id(): string {
        return 'custom-issue'; // Must be in CanonicalIds.php
    }
    
    public function get_name(): string {
        return 'Custom Issue Fixer';
    }
    
    public function get_description(): string {
        return 'Fixes custom accessibility issue';
    }
    
    public function get_wcag_criteria(): array {
        return ['1.1.1', '4.1.2'];
    }
    
    public function get_category(): string {
        return 'custom';
    }
    
    public function can_fix(string $content): bool {
        return strpos($content, '<custom-element>') !== false;
    }
    
    protected function apply_fix(string $content, array $options = []): FixResult {
        // Your fix logic here
        $fixed = str_replace(
            '<custom-element>',
            '<custom-element role="presentation">',
            $content
        );
        
        if ($fixed !== $content) {
            return FixResult::success($this->get_id(), $fixed, 1, 'Added role attribute');
        }
        
        return FixResult::no_changes($this->get_id(), $content);
    }
}
```

### Register Custom Fixer

```php
// Via filter
add_action('slos_fix_engine_register_fixers', function($collection) {
    $collection->register(new CustomFixer());
});

// Or add to Fixers/ directory for auto-discovery
```

## API Reference

### FixEngine Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `initialize()` | - | self | Initialize engine and load fixers |
| `fix_with_fixer()` | content, fixer_id, options | FixResult | Apply single fixer |
| `fix_content()` | content, fixer_ids[], options | FixSession | Apply multiple fixers |
| `get_fixer()` | fixer_id | FixerInterface\|null | Get specific fixer |
| `get_fixers()` | - | FixerCollection | Get all fixers |
| `get_fixer_count()` | - | int | Count registered fixers |

### CanonicalIds Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `is_valid()` | id | bool | Check if ID is canonical |
| `get()` | id | array\|null | Get ID metadata |
| `get_all()` | - | array | Get all IDs |
| `get_by_category()` | category | array | Filter by category |
| `get_categories()` | - | array | List all categories |

### FeatureFlags Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `is_fixengine_enabled()` | bool | Check if FixEngine is enabled |
| `enable_legacy_fallback()` | bool | Check if legacy fallback allowed |
| `is_migration_locked()` | bool | Check if migration in progress |

## Migration Guide

### From Legacy Fixes/ to FixEngine

```php
// OLD: Legacy system
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry;

FixerRegistry::init();
$fixer = FixerRegistry::get_fixer('generic-link'); // Alias
$result = $fixer->fix($content);

// NEW: FixEngine (single canonical pipeline)
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap;

$engine = Bootstrap::get_engine();
$result = $engine->fix_with_fixer($content, 'generic-link-text'); // Canonical
```

### Run ID Migration

```bash
# WP-CLI
wp slos fixengine migrate-ids --dry-run

# Verify
wp slos fixengine migration-report

# Apply
wp slos fixengine migrate-ids
```

## Performance

### Benchmarks

| Operation | Avg Time | Memory |
|-----------|----------|--------|
| Single Fixer | ~5-10ms | ~50KB |
| Multiple Fixers (5) | ~25-50ms | ~200KB |
| Full Scan (all fixers) | ~100-200ms | ~500KB |
| Large Content (10KB) | ~50-100ms | ~1MB |

### Optimization Tips

1. **Use specific fixers** instead of running all
2. **Batch operations** when fixing multiple posts
3. **Cache results** for identical content
4. **Monitor performance** with PerformanceProfiler
5. **Enable OpCache** in PHP for 30-50% improvement

### Performance Monitoring

```php
$profiler = new PerformanceProfiler();

// Profile specific fixer
$profile = $profiler->profile_fixer('missing-alt-text', $test_content, 10);
echo "Avg time: " . $profile['avg_time'] . "s\n";

// Profile all fixers
$profiles = $profiler->profile_all_fixers();
$profiler->save_report($profiles);
```

## Support

- **Issues**: GitHub Issue Tracker
- **Docs**: `/docs/autofix/`
- **Tests**: `/tests/FixEngine/`
- **Logs**: WP-CLI `wp slos fixengine logs`
