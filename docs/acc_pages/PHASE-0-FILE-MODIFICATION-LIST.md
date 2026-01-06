# PHASE 0: FILE MODIFICATION LIST
## Accessibility Scanner Module - Complete File Inventory

**Document Version:** 1.0  
**Created:** January 6, 2026  
**Purpose:** Complete list of all files that will be modified during reorganization  
**Status:** Foundation & Preparation Phase

---

## 📋 FILE MODIFICATION SUMMARY

### Phase Overview

| Phase | Files to Modify | Files to Create | Total Changes |
|-------|-----------------|-----------------|---------------|
| Phase 0 | 0 | 11 (documentation) | 11 |
| Phase 1 | 2 | 2 | 4 |
| Phase 2 | 2 | 0 | 2 |
| Phase 3 | 2 | 0 | 2 |
| Phase 4 | 2 | 1 | 3 |
| **TOTAL** | **8** | **14** | **22** |

---

## 📁 PHASE 0: FOUNDATION & PREPARATION

### Documentation Files (CREATE)

| File Path | Purpose | Status |
|-----------|---------|--------|
| `docs/acc_pages/PHASED-STRATEGIC-IMPLEMENTATION-PLAN.md` | Master implementation plan | ✅ Created |
| `docs/acc_pages/PHASE-0-BASELINE-FUNCTIONALITY.md` | Current functionality baseline | ✅ Created |
| `docs/acc_pages/PHASE-0-BACKUP-PROCEDURES.md` | Backup and rollback procedures | ✅ Created |
| `docs/acc_pages/PHASE-0-AJAX-TESTING-CHECKLIST.md` | AJAX handlers testing checklist | ✅ Created |
| `docs/acc_pages/PHASE-0-MODAL-DOCUMENTATION.md` | Modal systems documentation | ✅ Created |
| `docs/acc_pages/PHASE-0-FILE-MODIFICATION-LIST.md` | This file | ✅ Created |
| `docs/acc_pages/PHASE-0-TESTING-MATRIX.md` | Comprehensive testing matrix | 🔄 Creating |
| `docs/acc_pages/PHASE-0-STUB-FILES.md` | Stub files documentation | 🔄 Creating |

### Git Operations (COMPLETE)

| Operation | Command | Status |
|-----------|---------|--------|
| Commit baseline | `git commit -m "Pre-reorganization stable state..."` | ✅ Done |
| Create tag | `git tag -a pre-reorganization-stable` | ✅ Done |
| Create dev branch | `git checkout -b feature/accessibility-reorganization` | 🔄 Pending |

---

## 📁 PHASE 1: PAGES REQUIRING ATTENTION

**Priority:** P0 - Critical  
**Goal:** Move action-oriented content from Dashboard to Tools

### Files to CREATE

#### 1. PagesRequiringAttention.php
**Path:** `dist/includes/Modules/AccessibilityScanner/Admin/PagesRequiringAttention.php`  
**Type:** New Component Class  
**Purpose:** Render "Pages Requiring Attention" section in Tools tab  
**Lines:** ~150 (estimated)  
**Dependencies:**
- `AccessibilityScanner.php` for data
- `accessibility-pages-attention.php` template

**Key Methods:**
```php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin;

class PagesRequiringAttention {
    public function render() { }
    private function get_pages_with_issues() { }
    private function calculate_priority($page) { }
}
```

**Status:** 🔄 To be created in Phase 1

---

#### 2. accessibility-pages-attention.php
**Path:** `templates/admin/accessibility-pages-attention.php`  
**Type:** New Template File  
**Purpose:** HTML template for Pages Requiring Attention section  
**Lines:** ~250 (estimated)  
**Dependencies:**
- V3 Mac Slate Liquid CSS theme
- jQuery for interactions
- AJAX handlers from AccessibilityScanner.php

**Content:**
- Table with page list
- Issue counts per page
- Priority badges
- Action buttons (Fix All, Rollback, View Details)
- Auto-fix toggles
- Batch selection

**Status:** 🔄 To be created in Phase 1

---

### Files to MODIFY

#### 3. ScannerPage.php (dist/)
**Path:** `dist/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php`  
**Current Lines:** 1939  
**Type:** Modification  
**Purpose:** Add new "Pages Requiring Attention" component

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~900 | INSERT | Add PagesRequiringAttention component after Statement Generator |
| ~50 | MODIFY | Add `use` statement for new class |

**Code to Add (after line 900):**
```php
<!-- Card 5: Pages Requiring Attention -->
<?php
$pages_attention = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\PagesRequiringAttention();
$pages_attention->render();
?>
```

**Status:** 🔄 To be modified in Phase 1

---

#### 4. accessibility-dashboard.php
**Path:** `templates/admin/accessibility-dashboard.php`  
**Current Lines:** 2965  
**Type:** Modification  
**Purpose:** Remove "Scan Results Overview" section (move to Tools)

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~2700-2850 | DELETE | Remove "Scan Results Overview" card |
| ~2700 | INSERT | Add redirect message to Tools tab |

**Code to Remove:**
- Entire "Scan Results Overview" section
- Action buttons (Fix All, Rollback)
- Auto-fix toggles
- Edit links

**Code to Add:**
```php
<div class="slos-dashboard-notice">
    <p>
        <span class="dashicons dashicons-info"></span>
        <strong>Looking for pages to fix?</strong>
        Visit the <a href="<?php echo admin_url('admin.php?page=slos-accessibility&tab=tools'); ?>">Tools & Scanner</a> tab to view and fix accessibility issues.
    </p>
</div>
```

**Status:** 🔄 To be modified in Phase 1

---

## 📁 PHASE 2: QUICK ACCESSIBILITY CHECKS

**Priority:** P1 - High  
**Goal:** Complete interactive quick check tools

### Files to MODIFY

#### 5. AccessibilityScanner.php
**Path:** `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`  
**Current Lines:** 3040  
**Type:** Modification  
**Purpose:** Add 3 new AJAX handlers for quick checks

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~230 | INSERT | Add AJAX action registrations (3 handlers) |
| ~1600 | INSERT | Add `ajax_check_color_contrast()` method (~100 lines) |
| ~1700 | INSERT | Add `ajax_check_readability()` method (~80 lines) |
| ~1780 | INSERT | Add `ajax_validate_link_text()` method (~60 lines) |

**New AJAX Handlers:**
1. `slos_check_color_contrast` - WCAG contrast ratio calculator
2. `slos_check_readability` - Flesch-Kincaid readability score
3. `slos_validate_link_text` - Generic link text validator

**Status:** 🔄 To be modified in Phase 2

---

#### 6. slos-scanner-admin.js
**Path:** `assets/js/slos-scanner-admin.js`  
**Current Lines:** 800+  
**Type:** Modification  
**Purpose:** Add frontend handlers for quick checks

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~750 | INSERT | Add color contrast checker handler (~80 lines) |
| ~830 | INSERT | Add readability checker handler (~60 lines) |
| ~890 | INSERT | Add link validator handler (~50 lines) |

**New Event Handlers:**
1. `#slos-check-contrast.click` - Contrast checker trigger
2. `#slos-check-readability.click` - Readability checker trigger
3. `#slos-validate-links.click` - Link validator trigger

**Status:** 🔄 To be modified in Phase 2

---

#### 7. slos-scanner-admin.css
**Path:** `assets/css/slos-scanner-admin.css`  
**Current Lines:** ~1500  
**Type:** Modification  
**Purpose:** Add styles for quick check results

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~1400 | INSERT | Add `.wcag-badge` styles (~30 lines) |
| ~1430 | INSERT | Add `.readability-score` styles (~20 lines) |
| ~1450 | INSERT | Add `.link-validation-result` styles (~20 lines) |

**New CSS Classes:**
- `.wcag-badge.pass` / `.wcag-badge.fail`
- `.readability-score.good` / `.fair` / `.poor`
- `.link-validation-result.valid` / `.invalid`

**Status:** 🔄 To be modified in Phase 2

---

## 📁 PHASE 3: SCANNER CONFIGURATION

**Priority:** P1 - High  
**Goal:** Consolidate scanner settings into Tools tab

### Files to MODIFY

#### 8. ScannerPage.php (dist/)
**Path:** `dist/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php`  
**Current Lines:** 1939  
**Type:** Modification (2nd time)  
**Purpose:** Add Scanner Configuration card

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~850 | INSERT | Add Scanner Configuration card (~300 lines) |

**New Sections:**
- WCAG Conformance Level selector
- Scan Frequency settings
- Post Types to Scan checkboxes
- Active Checkers toggles (organized by category)
- Configuration save/reset buttons

**Status:** 🔄 To be modified in Phase 3

---

#### 9. AccessibilityScanner.php
**Path:** `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`  
**Current Lines:** 3040  
**Type:** Modification (2nd time)  
**Purpose:** Enhance `ajax_save_scanner_config()` handler

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~1300-1400 | MODIFY | Enhance existing handler with new config options |
| ~1400 | INSERT | Add `register_checks()` method for dynamic checker registration |

**Enhanced Functionality:**
- Save all config options atomically
- Validate post types
- Validate checker names
- Re-register checkers after config change
- Return detailed success/error messages

**Status:** 🔄 To be modified in Phase 3

---

## 📁 PHASE 4: WIDGET & EXPORT ENHANCEMENTS

**Priority:** P2 - Medium  
**Goal:** Complete widget configuration and export features

### Files to MODIFY

#### 10. ScannerPage.php (dist/)
**Path:** `dist/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php`  
**Current Lines:** 1939  
**Type:** Modification (3rd time)  
**Purpose:** Add Widget Configuration and Export cards

**Changes:**
| Line Range | Change Type | Description |
|------------|-------------|-------------|
| ~1150 | INSERT | Add Widget Configuration card (~200 lines) |
| ~1350 | INSERT | Add Export & Reporting card (~150 lines) |

**New Sections:**
1. Widget Configuration:
   - Enable/disable toggle
   - Position selection (bottom-left/right)
   - Color scheme selector
   - Feature toggles (contrast, font size, keyboard nav)
   - Live preview

2. Export & Reporting:
   - PDF report generation
   - CSV export (enhanced)
   - JSON export (enhanced)
   - Schedule automated reports
   - Email distribution settings

**Status:** 🔄 To be modified in Phase 4

---

#### 11. AccessibilityScanner.php
**Path:** `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`  
**Current Lines:** 3040  
**Type:** Modification (3rd time)  
**Purpose:** Complete `ajax_schedule_email_report()` handler

**Changes:**
| Line Range | Change Type | Description |
|----------||-------------|-------------|
| ~1400-1500 | MODIFY | Complete skeleton handler with WP-Cron integration |
| ~1500 | INSERT | Add `send_scheduled_report()` method (~100 lines) |
| ~1600 | INSERT | Add WP-Cron hook registration |

**New Functionality:**
- WP-Cron event scheduling
- Email template generation
- PDF attachment creation (if selected)
- Recipient validation
- Frequency options (daily/weekly/monthly)

**Status:** 🔄 To be modified in Phase 4

---

### Files to CREATE

#### 12. AccessibilityReporter.php
**Path:** `includes/Modules/AccessibilityScanner/Reporting/AccessibilityReporter.php`  
**Type:** New Service Class  
**Purpose:** Handle report generation (PDF, CSV, JSON)  
**Lines:** ~300 (estimated)  
**Dependencies:**
- DOMPDF or TCPDF library (for PDF generation)
- WordPress export functions

**Key Methods:**
```php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting;

class AccessibilityReporter {
    public function generate_pdf_report($data) { }
    public function generate_csv_export($data) { }
    public function generate_json_export($data) { }
    private function format_report_data($data) { }
    private function create_pdf_template($data) { }
}
```

**Status:** 🔄 To be created in Phase 4

---

## 📁 PHASE 5: POLISHING & QA

**Priority:** Documentation & Deployment  
**Goal:** Finalize documentation and deploy

### Files to UPDATE

#### 13. README.md (if exists)
**Path:** `readme.txt` or `README.md`  
**Type:** Documentation Update  
**Purpose:** Document new features and structure

**Changes:**
- Update feature list
- Add new tab descriptions
- Document new AJAX handlers
- Update screenshots (if applicable)

**Status:** 🔄 To be updated in Phase 5

---

#### 14. CHANGELOG.md
**Path:** `CHANGELOG.md` or `changelog.txt`  
**Type:** Documentation Update  
**Purpose:** Document all changes in v3.1.2

**Changes:**
- Add Phase 1-4 changes
- List new features
- Document bug fixes
- Note breaking changes (if any)

**Status:** 🔄 To be updated in Phase 5

---

## 📊 FILE CHANGE IMPACT ANALYSIS

### High Impact Files (Core Functionality)

| File | Impact Level | Risk | Testing Priority |
|------|--------------|------|------------------|
| `AccessibilityScanner.php` | **CRITICAL** | Medium | P0 - Test all 20+ AJAX handlers |
| `ScannerPage.php` | **HIGH** | Low | P0 - Test all UI sections |
| `accessibility-dashboard.php` | **HIGH** | Low | P1 - Verify analytics only |
| `slos-scanner-admin.js` | **HIGH** | Medium | P0 - Test all event handlers |

### Medium Impact Files

| File | Impact Level | Risk | Testing Priority |
|------|--------------|------|------------------|
| `slos-scanner-admin.css` | **MEDIUM** | Very Low | P2 - Visual QA only |
| `PagesRequiringAttention.php` | **MEDIUM** | Low | P1 - New component |
| `AccessibilityReporter.php` | **MEDIUM** | Low | P2 - New feature |

### Low Impact Files

| File | Impact Level | Risk | Testing Priority |
|------|--------------|------|------------------|
| Templates (new) | **LOW** | Very Low | P2 - UI verification |
| Documentation | **NONE** | None | P3 - Review only |

---

## 🔒 FILE SAFETY CHECKLIST

### Before Modifying Each File

- [ ] File backed up (Git commit + tag)
- [ ] Current version tested and working
- [ ] Dependencies identified
- [ ] Test cases prepared
- [ ] Rollback procedure documented

### During Modification

- [ ] Follow coding standards (WordPress, PSR-12)
- [ ] Add inline comments for complex logic
- [ ] Maintain existing function signatures
- [ ] Preserve backward compatibility
- [ ] Test incrementally (commit often)

### After Modification

- [ ] Syntax validated (PHP linter)
- [ ] Code tested in development environment
- [ ] AJAX handlers tested via browser console
- [ ] UI verified in browser
- [ ] No console errors
- [ ] Git commit with descriptive message
- [ ] Update testing checklist

---

## 📋 GIT COMMIT STRATEGY

### Commit Frequency

**Phase 1:**
- Commit 1: Create PagesRequiringAttention.php
- Commit 2: Create accessibility-pages-attention.php template
- Commit 3: Integrate into ScannerPage.php
- Commit 4: Remove from accessibility-dashboard.php
- Commit 5: Test and fix issues
- **Tag:** `phase-1-complete`

**Phase 2:**
- Commit 6: Add color contrast AJAX handler
- Commit 7: Add readability AJAX handler
- Commit 8: Add link validator AJAX handler
- Commit 9: Add frontend handlers (JS)
- Commit 10: Add CSS styles
- **Tag:** `phase-2-complete`

**Phase 3:**
- Commit 11: Add Scanner Configuration UI
- Commit 12: Enhance save_scanner_config AJAX handler
- Commit 13: Test and fix issues
- **Tag:** `phase-3-complete`

**Phase 4:**
- Commit 14: Add Widget Configuration UI
- Commit 15: Add Export & Reporting UI
- Commit 16: Create AccessibilityReporter class
- Commit 17: Complete schedule_email_report handler
- Commit 18: Test all export features
- **Tag:** `phase-4-complete`

**Phase 5:**
- Commit 19: Final QA fixes
- Commit 20: Update documentation
- Commit 21: Update changelog
- **Tag:** `v3.1.2-accessibility-reorganization`

### Commit Message Format

```
[Phase X] Brief description (50 chars max)

Detailed description:
- What changed
- Why it changed
- Any breaking changes
- Testing performed

Files modified:
- path/to/file1.php
- path/to/file2.js

Ref: PHASED-STRATEGIC-IMPLEMENTATION-PLAN.md Phase X
```

---

## ✅ FILE MODIFICATION CHECKLIST

### Phase 0 (Foundation)
- [x] Documentation files created (8 files)
- [x] Git baseline committed
- [x] Git tag created
- [ ] Development branch created

### Phase 1 (Pages Requiring Attention)
- [ ] PagesRequiringAttention.php created
- [ ] accessibility-pages-attention.php created
- [ ] ScannerPage.php modified (add component)
- [ ] accessibility-dashboard.php modified (remove section)
- [ ] All 4 files tested
- [ ] Phase 1 tag created

### Phase 2 (Quick Checks)
- [ ] AccessibilityScanner.php modified (3 AJAX handlers)
- [ ] slos-scanner-admin.js modified (3 frontend handlers)
- [ ] slos-scanner-admin.css modified (styles)
- [ ] All 3 files tested
- [ ] Phase 2 tag created

### Phase 3 (Configuration)
- [ ] ScannerPage.php modified (config UI)
- [ ] AccessibilityScanner.php modified (enhance handler)
- [ ] Both files tested
- [ ] Phase 3 tag created

### Phase 4 (Widget & Export)
- [ ] ScannerPage.php modified (widget/export UI)
- [ ] AccessibilityScanner.php modified (complete handler)
- [ ] AccessibilityReporter.php created
- [ ] All 3 files tested
- [ ] Phase 4 tag created

### Phase 5 (QA & Deploy)
- [ ] README updated
- [ ] CHANGELOG updated
- [ ] Final testing complete
- [ ] Release tag created

---

**Document Status:** ✅ COMPLETE  
**Total Files Tracked:** 14  
**Last Updated:** January 6, 2026
