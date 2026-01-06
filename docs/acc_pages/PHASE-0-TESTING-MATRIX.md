# PHASE 0: COMPREHENSIVE TESTING MATRIX
## Accessibility Scanner Module - All Test Scenarios

**Document Version:** 1.0  
**Created:** January 6, 2026  
**Purpose:** Master testing matrix for all reorganization phases  
**Status:** Foundation & Preparation Phase

---

## 🎯 TESTING OVERVIEW

### Test Coverage Summary

| Test Category | Test Cases | Priority | Phase |
|---------------|------------|----------|-------|
| Navigation Tests | 15 | P0 | All |
| AJAX Handler Tests | 140+ | P0 | All |
| Modal System Tests | 48 | P0 | All |
| Cross-Browser Tests | 30 | P1 | Phase 5 |
| Accessibility Tests | 25 | P1 | Phase 5 |
| Performance Tests | 20 | P2 | Phase 5 |
| **TOTAL** | **278+** | - | - |

### Testing Schedule

| Phase | Testing Type | When | Duration |
|-------|--------------|------|----------|
| Phase 0 | Manual Baseline | Before Phase 1 | 3-4 hours |
| Phase 1 | Regression | After implementation | 2 hours |
| Phase 2 | Regression + New Features | After implementation | 2 hours |
| Phase 3 | Regression + New Features | After implementation | 2 hours |
| Phase 4 | Regression + New Features | After implementation | 2 hours |
| Phase 5 | Full Suite | Before deployment | 6-8 hours |

---

## 📋 NAVIGATION TESTS

### Tab Navigation (5 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| NAV-01 | Click "Tools & Scanner" tab | Tools tab loads, URL updates to `&tab=tools` | 🔄 |
| NAV-02 | Click "Dashboard & Reports" tab | Dashboard loads, URL updates to `&tab=dashboard` | 🔄 |
| NAV-03 | Click "Settings" tab | Settings loads, URL updates to `&tab=settings` | 🔄 |
| NAV-04 | Browser back button after tab change | Returns to previous tab correctly | 🔄 |
| NAV-05 | Browser forward button | Moves to next tab correctly | 🔄 |

### State Preservation (5 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| NAV-06 | Switch tabs and return | Tab content preserved (no reload) | 🔄 |
| NAV-07 | Bookmark Tools tab URL | URL loads Tools tab directly | 🔄 |
| NAV-08 | Bookmark Dashboard tab URL | URL loads Dashboard tab directly | 🔄 |
| NAV-09 | Reload page while on specific tab | Same tab active after reload | 🔄 |
| NAV-10 | Invalid tab parameter in URL | Defaults to Tools tab | 🔄 |

### Cross-Tab Links (5 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| NAV-11 | Click "Run First Scan" in Dashboard | Navigates to Tools tab | 🔄 |
| NAV-12 | Click "View All" link in Dashboard | Navigates to Tools tab | 🔄 |
| NAV-13 | After scan completes in Tools | Optional redirect to Dashboard | 🔄 |
| NAV-14 | Click "Back to Dashboard" in Settings | Navigates to Dashboard tab | 🔄 |
| NAV-15 | Module card link in Dashboard | Navigates to Settings tab | 🔄 |

**Total Navigation Tests:** 15  
**Estimated Time:** 30 minutes

---

## 📋 AJAX HANDLER TESTS

### Test Template (Applied to all 20 handlers)

For each AJAX handler, verify:

1. **Security:** Nonce validation works
2. **Permissions:** Non-admin users blocked
3. **Functionality:** Handler performs intended action
4. **Error Handling:** Invalid data returns proper error
5. **Response Format:** JSON structure correct
6. **Performance:** Response time acceptable
7. **State Changes:** Database updated correctly

### Handler Test Matrix

| Handler | Security | Permissions | Functionality | Error Handling | Response | Performance | State | Total |
|---------|----------|-------------|---------------|----------------|----------|-------------|-------|-------|
| slos_get_posts_to_scan | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_scan_single_post | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_run_full_scan | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_generate_alt_text | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_generate_statement | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_publish_statement | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_fix_single_issue | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_fix_all_issues | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_autofix_single | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_rollback_fixes | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_check_backup_exists | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_toggle_autofix | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_get_page_issues | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_get_page_fixable_issues | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_consolidate_scan_results | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_audit_media_library | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_get_detailed_scan_report | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_save_scanner_config | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_schedule_email_report | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |
| slos_toggle_widget | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 0/7 |

**Total AJAX Tests:** 140 (20 handlers × 7 tests)  
**Estimated Time:** 4-5 hours

---

## 📋 MODAL SYSTEM TESTS

### Modal 1: Scan Progress (12 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| MOD1-01 | Click "Start Full Scan" | Modal opens | 🔄 |
| MOD1-02 | Progress bar updates | Percentage increases | 🔄 |
| MOD1-03 | Page status updates | Each page shows status | 🔄 |
| MOD1-04 | Click "Cancel" during scan | Scan stops, modal closes | 🔄 |
| MOD1-05 | Scan completes successfully | Modal auto-closes after 2s | 🔄 |
| MOD1-06 | Scan encounters error | Error displayed, can retry | 🔄 |
| MOD1-07 | Close button (×) works | Modal closes immediately | 🔄 |
| MOD1-08 | ESC key pressed | Modal closes | 🔄 |
| MOD1-09 | Background click | Modal closes | 🔄 |
| MOD1-10 | Multiple scans attempted | Second scan prevented | 🔄 |
| MOD1-11 | Long scan (50+ pages) | No timeout, all pages scanned | 🔄 |
| MOD1-12 | Console errors | No errors during scan | 🔄 |

### Modal 2: Auto-Fix Progress (12 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| MOD2-01 | Click "Fix All" button | Modal opens | 🔄 |
| MOD2-02 | Backup creation shown | "Creating backup..." displayed | 🔄 |
| MOD2-03 | Fixer names displayed | Each fixer shown during execution | 🔄 |
| MOD2-04 | Progress percentage updates | 0% → 100% smooth animation | 🔄 |
| MOD2-05 | Results breakdown shown | Fixed/Failed/Manual counts | 🔄 |
| MOD2-06 | onComplete callback fires | Custom callback executed | 🔄 |
| MOD2-07 | ESC key during fix | Disabled (cannot interrupt) | 🔄 |
| MOD2-08 | Background click during fix | Disabled (cannot interrupt) | 🔄 |
| MOD2-09 | Close button after completion | Modal closes | 🔄 |
| MOD2-10 | Fix with no backup | Backup created automatically | 🔄 |
| MOD2-11 | All fixes fail | Error message shown | 🔄 |
| MOD2-12 | Console errors | No errors during execution | 🔄 |

### Modal 3: Scan Details (8 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| MOD3-01 | Click "View Details" | Modal opens with loading | 🔄 |
| MOD3-02 | Issues load correctly | Issues grouped by severity | 🔄 |
| MOD3-03 | Issue types displayed | Type, guideline, message shown | 🔄 |
| MOD3-04 | Recommendations shown | Fix recommendations present | 🔄 |
| MOD3-05 | Empty state (no issues) | Congratulatory message | 🔄 |
| MOD3-06 | Long issue list | Content scrollable | 🔄 |
| MOD3-07 | Edit link works | Opens post editor in new tab | 🔄 |
| MOD3-08 | Close mechanisms | All 3 close methods work | 🔄 |

### Modal 4: Fix Results (8 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| MOD4-01 | Modal appears after fix | Opens automatically | 🔄 |
| MOD4-02 | Fixed issues section | Green, shows fixed items | 🔄 |
| MOD4-03 | Failed fixes section | Red, shows errors | 🔄 |
| MOD4-04 | Manual guidance section | Yellow, shows steps | 🔄 |
| MOD4-05 | Color-coded correctly | Success=green, error=red | 🔄 |
| MOD4-06 | Close button works | Modal closes and removes from DOM | 🔄 |
| MOD4-07 | Can overlap with Modal 3 | Both visible simultaneously | 🔄 |
| MOD4-08 | No console errors | Clean execution | 🔄 |

### Modal 5: History Comparison (8 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| MOD5-01 | Click "Compare" button | Modal opens with data | 🔄 |
| MOD5-02 | Scan 1 data displayed | Date, score, issues shown | 🔄 |
| MOD5-03 | Scan 2 data displayed | Date, score, issues shown | 🔄 |
| MOD5-04 | Score trend calculated | ↑↓→ indicator correct | 🔄 |
| MOD5-05 | Issues trend calculated | Fewer/more/same correct | 🔄 |
| MOD5-06 | Improvement shown green | Positive changes highlighted | 🔄 |
| MOD5-07 | Regression shown red | Negative changes highlighted | 🔄 |
| MOD5-08 | Close mechanisms | All 3 close methods work | 🔄 |

**Total Modal Tests:** 48  
**Estimated Time:** 1.5-2 hours

---

## 📋 CROSS-BROWSER TESTS

### Browser Compatibility (6 browsers × 5 tests = 30 tests)

| Test | Chrome | Firefox | Safari | Edge | Mobile Chrome | Mobile Safari |
|------|--------|---------|--------|------|---------------|---------------|
| Page loads correctly | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| Tab navigation works | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| AJAX requests succeed | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| Modals open/close | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| CSS renders correctly | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |

**Browser Versions to Test:**
- Chrome: Latest stable (Windows/Mac/Android)
- Firefox: Latest stable (Windows/Mac)
- Safari: Latest stable (Mac/iOS)
- Edge: Latest stable (Windows)
- Mobile browsers: Latest on iOS 15+ and Android 11+

**Total Cross-Browser Tests:** 30  
**Estimated Time:** 2-3 hours

---

## 📋 ACCESSIBILITY TESTS (Admin Interface)

### Keyboard Navigation (10 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| A11Y-01 | Tab through tab navigation | All tabs focusable | 🔄 |
| A11Y-02 | Tab through form fields | All fields reachable | 🔄 |
| A11Y-03 | Tab through buttons | All buttons focusable | 🔄 |
| A11Y-04 | Enter key on tab | Activates tab | 🔄 |
| A11Y-05 | Enter key on button | Triggers action | 🔄 |
| A11Y-06 | ESC key in modal | Closes modal | 🔄 |
| A11Y-07 | Focus trap in modal | Focus stays inside | 🔄 |
| A11Y-08 | Focus indicator visible | Blue outline visible | 🔄 |
| A11Y-09 | Skip link present | "Skip to content" available | 🔄 |
| A11Y-10 | No keyboard trap | Can exit all components | 🔄 |

### Screen Reader Compatibility (8 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| A11Y-11 | Tab labels announced | "Tools and Scanner", etc. | 🔄 |
| A11Y-12 | Button labels announced | "Start Full Scan", etc. | 🔄 |
| A11Y-13 | Form labels announced | All inputs have labels | 🔄 |
| A11Y-14 | ARIA roles present | Proper roles on elements | 🔄 |
| A11Y-15 | ARIA labels present | aria-label where needed | 🔄 |
| A11Y-16 | Status messages announced | aria-live regions work | 🔄 |
| A11Y-17 | Modal title announced | Modal purpose clear | 🔄 |
| A11Y-18 | Error messages announced | Validation errors spoken | 🔄 |

### Visual Accessibility (7 tests)

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| A11Y-19 | Color contrast (text) | Meets WCAG AA (4.5:1) | 🔄 |
| A11Y-20 | Color contrast (large text) | Meets WCAG AA (3:1) | 🔄 |
| A11Y-21 | Color contrast (icons) | Meets WCAG AA (3:1) | 🔄 |
| A11Y-22 | Text zoom to 200% | Content still readable | 🔄 |
| A11Y-23 | Focus indicators | Visible on all elements | 🔄 |
| A11Y-24 | Color not sole indicator | Icons + color for status | 🔄 |
| A11Y-25 | Target size | Buttons ≥44×44px touch | 🔄 |

**Total Accessibility Tests:** 25  
**Estimated Time:** 2-3 hours

---

## 📋 PERFORMANCE TESTS

### Page Load Performance (5 tests)

| Test ID | Test Case | Target | Status |
|---------|-----------|--------|--------|
| PERF-01 | Tools tab initial load | < 2 seconds | 🔄 |
| PERF-02 | Dashboard tab initial load | < 2 seconds | 🔄 |
| PERF-03 | Settings tab initial load | < 2 seconds | 🔄 |
| PERF-04 | Tab switch (cached) | < 500ms | 🔄 |
| PERF-05 | Assets size (JS+CSS) | < 300KB | 🔄 |

### AJAX Performance (8 tests)

| Test ID | Test Case | Target | Status |
|---------|-----------|--------|--------|
| PERF-06 | Single post scan | < 2 seconds | 🔄 |
| PERF-07 | Full scan (50 posts) | < 60 seconds | 🔄 |
| PERF-08 | Fix all issues (1 page) | < 5 seconds | 🔄 |
| PERF-09 | Get page issues | < 1 second | 🔄 |
| PERF-10 | Media library audit (1000 images) | < 3 seconds | 🔄 |
| PERF-11 | Consolidate results | < 2 seconds | 🔄 |
| PERF-12 | Generate statement | < 2 seconds | 🔄 |
| PERF-13 | Save configuration | < 1 second | 🔄 |

### Database Performance (7 tests)

| Test ID | Test Case | Target | Status |
|---------|-----------|--------|--------|
| PERF-14 | Query count (Tools tab) | < 20 queries | 🔄 |
| PERF-15 | Query count (Dashboard) | < 35 queries | 🔄 |
| PERF-16 | Query time (Tools) | < 100ms | 🔄 |
| PERF-17 | Query time (Dashboard) | < 150ms | 🔄 |
| PERF-18 | Option updates | < 50ms | 🔄 |
| PERF-19 | Post meta updates | < 50ms | 🔄 |
| PERF-20 | No N+1 queries | Confirmed | 🔄 |

**Total Performance Tests:** 20  
**Estimated Time:** 2-3 hours

---

## 📊 TESTING SUMMARY

### Phase 0 Baseline Testing (Pre-Implementation)

**Must Complete Before Phase 1:**
- [ ] All navigation tests (15)
- [ ] All AJAX handler tests (140)
- [ ] All modal system tests (48)
- [ ] Document baseline performance metrics
- [ ] Identify any existing issues

### Regression Testing (After Each Phase)

**After Phase 1:**
- [ ] Navigation tests (verify Dashboard/Tools split)
- [ ] AJAX handlers (verify no breaks)
- [ ] Modal systems (verify Fix All modal still works)
- [ ] Pages Requiring Attention component tests (new)

**After Phase 2:**
- [ ] All Phase 1 regression tests
- [ ] Color contrast checker tests (new)
- [ ] Readability checker tests (new)
- [ ] Link validator tests (new)

**After Phase 3:**
- [ ] All Phase 1-2 regression tests
- [ ] Scanner configuration tests (new)
- [ ] Active checker toggle tests (new)

**After Phase 4:**
- [ ] All Phase 1-3 regression tests
- [ ] Widget configuration tests (new)
- [ ] Export feature tests (new)
- [ ] Email scheduling tests (new)

### Final Testing (Phase 5)

**Before Deployment:**
- [ ] Complete test suite (all 278+ tests)
- [ ] Cross-browser testing (30 tests)
- [ ] Accessibility audit (25 tests)
- [ ] Performance benchmarking (20 tests)
- [ ] User acceptance testing
- [ ] Production deployment test

---

## 🧪 TEST EXECUTION INSTRUCTIONS

### Manual Testing Process

1. **Open Browser Console** (F12)
2. **Navigate to Accessibility Scanner Module**
3. **Execute Test Cases** one by one
4. **Record Results** in this document
5. **Document Failures** with screenshots
6. **Create Bug Reports** for failures

### AJAX Testing Template

```javascript
// Copy to browser console and modify as needed
jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    data: {
        action: 'slos_HANDLER_NAME',
        nonce: slosScanner.nonce,
        // Add handler-specific parameters
    },
    success: function(response) {
        console.log('✅ PASS:', response);
    },
    error: function(xhr) {
        console.error('❌ FAIL:', xhr.responseText);
    }
});
```

### Performance Testing Tools

- **Browser DevTools:** Network tab, Performance tab
- **Query Monitor Plugin:** Database query analysis
- **GTmetrix/PageSpeed:** Overall page performance
- **Lighthouse:** Accessibility & performance audit

---

## ✅ TESTING CHECKLIST

### Phase 0 Completion Criteria

- [ ] Navigation tests documented (15)
- [ ] AJAX handler tests documented (140)
- [ ] Modal system tests documented (48)
- [ ] Cross-browser tests documented (30)
- [ ] Accessibility tests documented (25)
- [ ] Performance tests documented (20)
- [ ] Testing instructions provided
- [ ] Test execution templates created
- [ ] Baseline testing completed (optional for Phase 0, required before Phase 1)

---

**Document Status:** ✅ COMPLETE  
**Total Test Cases:** 278+  
**Estimated Total Testing Time:** 20-25 hours (across all phases)  
**Last Updated:** January 6, 2026
