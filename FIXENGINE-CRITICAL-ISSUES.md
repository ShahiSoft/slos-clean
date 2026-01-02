# FixEngine Critical Issues - Requires Complete Refactoring

**Date**: January 2, 2026  
**Status**: ⛔ BLOCKED - Cannot activate until fixed  
**Severity**: CRITICAL  
**Impact**: All 31 fixers are non-functional

---

## PROBLEM SUMMARY

The FixEngine has a fundamental architectural mismatch between the AbstractFixer base class and all 31 concrete fixer implementations.

**Root Cause**: Template Method Pattern implementation mismatch

---

## DETAILED ANALYSIS

### 1. AbstractFixer Design (CORRECT)

**File**: `includes/Modules/AccessibilityScanner/FixEngine/AbstractFixer.php`

**Template Method Pattern** (lines 58-106):
```php
final public function fix( string $content ): FixResult {
    // 1. Parse HTML once
    $this->dom = $this->parse_html( $content );
    $this->xpath = new \DOMXPath( $this->dom );
    
    // 2. Call child's apply_fix() - expects array
    $fix_details = $this->apply_fix();  // <-- Returns array
    
    // 3. Extract fixed HTML once
    $fixed_content = $this->get_html();
    
    // 4. Wrap in FixResult
    return FixResult::success(...);
}

// Child classes must implement this
abstract protected function apply_fix(): array;  // <-- Returns array
```

**Expected Child Behavior**:
- Work with `$this->dom` and `$this->xpath` (already parsed)
- Return `['count' => int, 'items' => array]`
- Do NOT parse HTML
- Do NOT return FixResult

### 2. All Fixers Implementation (WRONG)

**All 31 fixers** have this pattern:

```php
protected function apply_fix( string $content, array $options = [] ): FixResult {
    // ❌ WRONG: Takes parameters (should take none)
    // ❌ WRONG: Returns FixResult (should return array)
    
    $doc = $this->parse_html( $content );  // ❌ WRONG: Parses HTML again
    
    // Fix logic...
    
    return FixResult::success(...);  // ❌ WRONG: Returns FixResult
}
```

**Signature Mismatches**:
1. ❌ Takes parameters: `string $content, array $options = []`
2. ❌ Returns FixResult instead of array
3. ❌ Parses HTML internally (should use `$this->dom`)

---

## AFFECTED FILES (31 Total)

### Images (5)
- ✅ MissingAltFixer.php
- ✅ EmptyAltFixer.php
- ✅ DecorativeImageFixer.php
- ✅ ComplexImageFixer.php
- ✅ SvgAccessibilityFixer.php

### Links (3)
- ✅ EmptyLinkFixer.php
- ✅ GenericLinkTextFixer.php
- ✅ NewWindowWarningFixer.php

### Headings (2)
- ✅ EmptyHeadingFixer.php
- ✅ HeadingHierarchyFixer.php

### Forms (4)
- ✅ FormLabelFixer.php
- ✅ RequiredFieldFixer.php
- ✅ InputErrorDescriptionFixer.php
- ✅ AutocompleteFixer.php

### Tables (3)
- ✅ TableCaptionFixer.php
- ✅ TableHeaderFixer.php
- ✅ TableScopeFixer.php

### Structure (2)
- ✅ LandmarkFixer.php
- ✅ SkipLinkFixer.php

### Interactive (3)
- ✅ TabIndexFixer.php
- ✅ FocusVisibleFixer.php
- ✅ ButtonTypeFixer.php

### ARIA (1)
- ✅ AriaLabelFixer.php

### Media (3)
- ✅ VideoAccessibilityFixer.php
- ✅ AudioAccessibilityFixer.php
- ✅ IframeAccessibilityFixer.php

### Document (4)
- ✅ DocumentTitleFixer.php
- ✅ LanguageAttributeFixer.php
- ✅ PageStructureFixer.php
- ✅ FigureCaptionFixer.php

### Visual (1)
- ✅ ColorContrastFixer.php

---

## ERROR MANIFESTATION

### PHP Fatal Error
```
PHP Fatal error: Declaration of ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers\AriaLabelFixer::apply_fix(string $content, array $options = []): ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult must be compatible with ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer::apply_fix(): array
```

### AJAX Response
```
POST http://localhost:8080/wp-admin/admin-ajax.php 500 (Internal Server Error)
```

### Console Errors
```
SLOSAutoFixProgress: Retrying fixer missing-alt-text (attempt 1)
SLOSAutoFixProgress: Retrying fixer missing-alt-text (attempt 2)
(repeating failures)
```

---

## REFACTORING REQUIREMENTS

### For Each Fixer (31 files):

#### 1. Change Method Signature
**FROM**:
```php
protected function apply_fix( string $content, array $options = [] ): FixResult
```

**TO**:
```php
protected function apply_fix(): array
```

#### 2. Remove HTML Parsing
**REMOVE**:
```php
$doc = $this->parse_html( $content );
```

**USE INSTEAD**:
```php
// $this->dom and $this->xpath are already available
```

#### 3. Change Return Statement
**FROM**:
```php
return FixResult::success( $this->get_id(), $count, $content, $fixed_content, $details );
```

**TO**:
```php
return [
    'count' => $count,
    'items' => $details,
];
```

#### 4. Remove FixResult Creation
**REMOVE**:
```php
return FixResult::error( ... );
return FixResult::skipped( ... );
```

**USE INSTEAD**:
```php
return [ 'count' => 0, 'items' => [] ];  // For skipped
throw new \Exception( 'Error message' );  // For errors
```

---

## EXAMPLE REFACTORING

### Before (Incorrect)
```php
protected function apply_fix( string $content, array $options = [] ): FixResult {
    $doc = $this->parse_html( $content );
    
    if ( ! $doc ) {
        return FixResult::error( $this->get_id(), 'Parse failed', $content );
    }

    $images = $this->query( '//img[not(@alt)]' );
    $fixes_applied = 0;
    $details = [];

    foreach ( $images as $img ) {
        $src = $img->getAttribute( 'src' );
        $alt = $this->generate_alt_from_src( $src );
        $img->setAttribute( 'alt', $alt );
        $fixes_applied++;
        $details[] = [ 'src' => $src, 'alt' => $alt ];
    }

    if ( $fixes_applied === 0 ) {
        return FixResult::skipped( $this->get_id(), 'No fixes', $content );
    }

    $fixed_content = $this->get_html();
    return FixResult::success( $this->get_id(), $fixes_applied, $content, $fixed_content, $details );
}
```

### After (Correct)
```php
protected function apply_fix(): array {
    // Use $this->dom and $this->xpath (already parsed by AbstractFixer)
    $images = $this->query( '//img[not(@alt)]' );
    $details = [];

    foreach ( $images as $img ) {
        $src = $img->getAttribute( 'src' );
        $alt = $this->generate_alt_from_src( $src );
        $img->setAttribute( 'alt', $alt );
        $details[] = [ 'src' => $src, 'alt' => $alt ];
    }

    // Return simple array (AbstractFixer handles FixResult creation)
    return [
        'count' => count( $details ),
        'items' => $details,
    ];
}
```

---

## WORKAROUND (Current Solution)

### Disabled FixEngine, Using FixerRegistry

**File**: `includes/Modules/AccessibilityScanner/AccessibilityScanner.php` (line 2068)

```php
// TEMPORARY: FixEngine disabled due to method signature mismatch
// All 31 fixers have apply_fix(string $content, array $options): FixResult
// But AbstractFixer expects apply_fix(): array
// This requires refactoring all fixer files
// For now, using stable FixerRegistry

$fixer = null;

// Use FixerRegistry (stable, tested system)
if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
    \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
    $fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $fixer_id );
}
```

**Status**: ✅ Working - Using FixerRegistry (96 fixers with aliases)

---

## IMPACT ASSESSMENT

### Cannot Use FixEngine Because:
1. ❌ PHP fatal errors on every fixer call
2. ❌ All AJAX requests return 500
3. ❌ No fixers can execute
4. ❌ User experience broken

### Current Mitigation:
1. ✅ FixerRegistry active (legacy system)
2. ✅ 96 fixers available (72 unique + 24 aliases)
3. ✅ Backup/restore working
4. ✅ Phase 4 Day 5 testing can proceed

### Future Work Required:
1. ⏭️ Refactor all 31 fixer files
2. ⏭️ Update method signatures
3. ⏭️ Remove duplicate HTML parsing
4. ⏭️ Fix return types
5. ⏭️ Test each fixer individually
6. ⏭️ Integration testing
7. ⏭️ Re-activate FixEngine

**Estimated Effort**: 8-16 hours (30 minutes per fixer + testing)

---

## VERIFICATION CHECKLIST

When refactoring is complete:

### For Each Fixer:
- [ ] Method signature: `protected function apply_fix(): array`
- [ ] No parameters taken
- [ ] Uses `$this->dom` and `$this->xpath`
- [ ] Does NOT call `$this->parse_html()`
- [ ] Does NOT call `$this->get_html()`
- [ ] Returns `['count' => int, 'items' => array]`
- [ ] Does NOT return FixResult
- [ ] Throws exceptions for errors
- [ ] PHP syntax check passes
- [ ] No fatal errors in logs

### Integration:
- [ ] All 31 fixers load without errors
- [ ] AJAX requests return 200
- [ ] Fixers execute successfully
- [ ] FixResult objects created correctly
- [ ] Backup/restore works
- [ ] UI displays correctly
- [ ] No console errors

---

## LESSONS LEARNED

### What Went Wrong:
1. **Inconsistent Implementation**: Fixers were coded without following AbstractFixer's template pattern
2. **No Type Checking**: PHP didn't catch the mismatch until runtime
3. **Insufficient Testing**: Fixers were never actually executed before activation
4. **Architecture Drift**: Design intention (Template Method) not followed in implementation

### Prevention for Future:
1. ✅ Run PHP syntax checks before activation
2. ✅ Execute sample fixer calls in test scripts
3. ✅ Verify method signatures match base class
4. ✅ Document template patterns clearly
5. ✅ Test in development environment first
6. ✅ Gradual rollout (one fixer at a time)

---

## RELATED DOCUMENTS

- [FixEngine README](includes/Modules/AccessibilityScanner/FixEngine/README.md) - Design documentation
- [FIXENGINE-ACTIVATION-PLAN.md](FIXENGINE-ACTIVATION-PLAN.md) - Original activation plan
- [FIXENGINE-ACTIVATION-SUMMARY.md](FIXENGINE-ACTIVATION-SUMMARY.md) - Activation attempt summary
- [Phase 4 Guide](acc-new/autofix-new/PHASE-4-DETAILED-GUIDE.md) - Service 0.5 section

---

## STATUS

**FixEngine**: ⛔ DISABLED (requires refactoring)  
**FixerRegistry**: ✅ ACTIVE (stable, tested)  
**Phase 4 Day 5**: ✅ CAN PROCEED (using FixerRegistry)  
**User Impact**: ✅ NONE (transparent fallback)

---

**Next Action**: Complete Phase 4 Day 5 testing with FixerRegistry, schedule FixEngine refactoring as separate task (estimated 2-3 days of work).
