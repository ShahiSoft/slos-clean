# Compliance Module – Strategic Implementation Plan

_Created: 2025-12-30_
_Based on: compliance-module-audit.md_

---

## Overview

This plan translates the audit's recommended enhancements into concrete, sequenced work packages. Each phase builds on the previous one, ensuring foundational correctness before adding features.

**Guiding Principles**

1. **Honesty over vanity metrics** – the compliance score must reflect real configuration completeness, not just consent acceptance.
2. **Incremental value** – each phase should ship something usable and testable.
3. **Backward compatibility** – existing consent data, logs, and settings must remain intact.
4. **Accessibility-first** – any new UI (admin or frontend) must meet WCAG 2.1 AA.

---

## Phase 0 – Truthful Compliance & Data Foundations

**Goal:** Replace the misleading single-metric "Compliance Health Score" with a multi-dimensional "Readiness Score" and tighten consent/log integrity.

### 0.1 Multi-Dimensional Readiness Score

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| ✅ 0.1.1 | Define score dimensions enum/constants | `config/compliance-constants.php` (new) | Constants for dimensions: `COOKIES`, `LEGAL_DOCS`, `GEO_RULES`, `CONSENT_METADATA`, `SCANNING_FRESHNESS`, `BANNER_CONFIG` |
| ✅ 0.1.2 | Create `Compliance_Score_Calculator` service | `includes/Services/Compliance_Score_Calculator.php` (new) | Service computes per-dimension scores (0–100) and weighted aggregate |
| ✅ 0.1.3 | Integrate calculator into `ComplianceMainPage::get_dashboard_stats` | `includes/Admin/ComplianceMainPage.php` | Dashboard stats array includes `dimensions[]` with individual scores |
| ✅ 0.1.4 | Update dashboard template to render multi-dimension gauge | `templates/admin/compliance/tabs/dashboard.php` | Dimension breakdown grid with color-coded cards showing all 6 dimensions |
| ✅ 0.1.5 | Add microcopy disclaimer | Same template | Visible note: "This score reflects configuration completeness, not legal certification." |

**Reference:** See [COMPLIANCE-SCORE-REFERENCE.md](COMPLIANCE-SCORE-REFERENCE.md) for dimension definitions and weight rationale.

**Implementation Notes:**
- All 6 dimensions implemented: COOKIES (25%), LEGAL_DOCS (25%), GEO_RULES (15%), CONSENT_METADATA (15%), SCANNING_FRESHNESS (10%), BANNER_CONFIG (10%)
- Weighted aggregate calculation with grade mapping (A-F)
- Dashboard displays overall score plus individual dimension cards
- Logic tests created and passing: `acc-new/tests/test-compliance-logic.php`
- Completed: 2024

### 0.2 Consent & Log Versioning

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| ✅ 0.2.1 | Add `banner_version` and `policy_version` columns to consents table | Migration script / `Database/Migrations/` | Columns nullable; existing rows default to `NULL` |
| ✅ 0.2.2 | Populate version fields on new consent records | `Consent_Service::record_consent` | Pull current banner config version from settings; attach to `metadata` and dedicated columns |
| ✅ 0.2.3 | Extend `Consent_Audit_Logger` with method taxonomy | `Consent_Audit_Logger::log` | New `method` values: `banner`, `preferences_center`, `admin_manual`, `api`, `import` |
| ✅ 0.2.4 | Surface version info in consent detail modal | Admin JS + template | When viewing a consent record, show banner/policy version at time of consent |

**Implementation Notes:**
- Migration file: `migration_2025_12_30_add_version_columns_to_consent.php` adds nullable VARCHAR(50) columns
- Helper methods: `get_banner_version()` creates hash-based version, `get_policy_version()` reads from legal pages option
- Version info stored in both dedicated columns and metadata for audit trail
- Method taxonomy expanded: `banner`, `preferences_center`, `admin_manual`, `api`, `import` (plus legacy `website`, `admin`)
- Validation logic ensures invalid methods fall back to 'website' with debug logging
- UI updates: Dashboard modal shows version info box, Records alert includes versions
- All tests passing: `acc-new/tests/test-consent-versioning.php`
- Backward compatible: Nullable columns, fallback defaults, legacy method support
- Completed: 2024

### 0.3 Scanner Clarity & Refresh UX

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| ✅ 0.3.1 | Store scan metadata (type, duration, coverage) | `slos_cookie_scan_meta` option or dedicated table | JSON with `scan_type`, `started_at`, `completed_at`, `pages_scanned`, `coverage_level` |
| ✅ 0.3.2 | Display last-scan summary card in Cookie Scanner tab | `templates/admin/compliance/tabs/cookie-scanner.php` | Card shows: last scan date, type, pages scanned, duration, "Rescan Now" button |
| ✅ 0.3.3 | Implement AJAX "Rescan Now" with progress | `AccessibilityScanner.php` or new `CookieScanner` AJAX handler | Progress bar, step messages, post-scan summary modal |
| ✅ 0.3.4 | Factor uncategorized cookies into readiness score | `Compliance_Score_Calculator` | `COOKIES` dimension penalizes uncategorized cookies proportionally |

**Implementation Notes:**
- **Metadata Storage:** Added `slos_cookie_scan_meta` option via new methods in `Cookie_Scanner_Service`: `start_scan()`, `complete_scan()`, `get_scan_metadata()`, `clear_scan_metadata()`
- **Metadata Schema:** Stores `scan_type` (manual/auto/scheduled), `started_at`, `completed_at`, `status`, `pages_scanned`, `coverage_level`, `duration`, `cookies_found`, `errors`, and full `options` array
- **REST Integration:** `Cookie_REST_Controller::trigger_scan()` now calls `start_scan()` before scanning and `complete_scan()` after with results
- **Last-Scan Card:** Beautiful gradient card in cookie scanner template showing scan date, cookies found, pages scanned, duration, scan type, and coverage level
- **No Scan State:** Special warning-styled card displayed when no scan has been completed yet
- **Rescan Now Button:** Prominent button in summary card that triggers same scan endpoint as main "Scan Website" button
- **Enhanced Progress:** 5-stage progress tracking with descriptive labels: "Initializing", "Requesting website", "Detecting cookies", "Classifying cookies", "Finalizing"
- **Score Calculation:** `Compliance_Score_Calculator::calculate_cookies_dimension()` now checks both `slos_cookie_inventory` and `slos_detected_cookies`, tracks `$uncategorized` counter, applies 30-point proportional penalty for uncategorized cookies
- **Backward Compatible:** Still updates `slos_cookie_scan_time` option, fallbacks to `slos_detected_cookies` when `slos_cookie_inventory` is empty
- **Test Coverage:** All 8 tests passing: metadata methods, REST integration, UI components, progress stages, score calculation, syntax validation, backward compatibility, integration points
- Completed: 2024

### Phase 0 Deliverables

- ✅ `Compliance_Score_Calculator` service with tests.
- ✅ Updated dashboard with multi-dimension score and disclaimer.
- ✅ Versioning columns + migration.
- ✅ Scanner metadata storage and "Rescan Now" UX.

---

## Phase 1 – Feature Parity with Leading CMPs

**Goal:** Add legal document generators, richer geo rules, time-series reporting, and an enhanced consent UI.

### 1.1 Legal Document Suite Integration

> **Note:** The plugin already has a comprehensive Legal Documents module (`LegalDocuments`, `Document_Hub_Controller`, `Document_Generator`, `Template_Manager`). This phase focuses on **integrating** the Compliance module with the existing LegalDocs infrastructure, not rebuilding it.

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| ✅ 1.1.1 | Add cookie-data binding to existing doc templates | `Services/Placeholder_Mapper`, `Services/Document_Generator` | Cookie Policy template pulls from `slos_cookie_inventory` option; placeholders like `{{cookie_table_analytics}}`, `{{cookie_count}}`, `{{last_cookie_scan_date}}` auto-populate |
| ✅ 1.1.2 | Expose LegalDocs status in Compliance dashboard | `Admin/ComplianceMainPage::get_dashboard_stats`, `Services/Compliance_Score_Calculator` | Dashboard shows: Cookie Policy (Published/Draft/Missing), Privacy Policy, Accessibility Statement with staleness flags; score calculator checks published/stale status |
| ✅ 1.1.3 | Add "Legal Docs" quick-link card in Compliance dashboard | `templates/admin/compliance/tabs/dashboard.php` | Card linking to Document Hub (`slos-document-hub`) with status summary and "Outdated" badges for stale docs; no duplicate UI |
| ✅ 1.1.4 | Create cookie shortcode for embedding in existing docs | `includes/Shortcodes/Cookie_Table_Shortcode.php` (new), registered in `ShortcodeManager` | `[slos_cookie_table category="analytics" title="yes" style="default"]` renders detected cookies; usable inside Document Hub templates |
| ✅ 1.1.5 | Hook scanner updates to trigger doc staleness flag | `Services/Cookie_Scanner_Service`, `Services/Document_Hub_Service` | When cookies change, `slos_cookies_updated` action fires; Document_Hub_Service marks Cookie Policy and Privacy Policy as "needs regeneration"; dashboard shows warning |

**Implementation Notes:**
- **Cookie Placeholders (1.1.1):** Added `get_cookie_placeholders()` method to `Placeholder_Mapper` returning 11 placeholders: `cookie_count`, `cookie_categories`, `last_cookie_scan_date`, 5 category-specific HTML tables (`cookie_table_*`), 5 category counts
- **Cookie Table Rendering:** New `render_cookie_table()` method generates fully-styled HTML tables with cookie name, purpose, provider, duration columns
- **Legacy Support:** `convert_legacy_cookies()` method converts old `slos_detected_cookies` format to new inventory format for backward compatibility
- **Dashboard Integration (1.1.2):** `ComplianceMainPage::get_legal_docs_stats()` uses `Document_Hub_Service::get_document_cards()` to fetch status of cookie-policy, privacy-policy, accessibility-statement
- **Stats Structure:** Returns `total`, `published`, `stale`, `pending`, `percentage`, `docs` array with per-document status and staleness flags
- **Dashboard Card (1.1.3):** Beautiful "Legal Documents" card in sidebar showing progress bar, document list with icons (✓/⚠/−), stale warning box, and "Manage" link to Document Hub
- **Shortcode (1.1.4):** Full-featured `Cookie_Table_Shortcode` with category filtering (all/necessary/analytics/marketing/functional), title toggle, style options, automatic last-updated timestamp
- **Registered in ShortcodeManager:** Added to `$this->shortcodes` array for auto-registration
- **Staleness System (1.1.5):** `Document_Hub_Service` now hooks `slos_cookies_updated` action in constructor, implements 4 staleness methods: `mark_cookie_dependent_docs_stale()`, `clear_staleness()`, `is_document_stale()`, `get_staleness_reason()`
- **Action Hook:** `Cookie_Scanner_Service::complete_scan()` fires `do_action('slos_cookies_updated', $cookies)` after successful scan completion
- **Metadata Storage:** Staleness tracked via post meta: `_slos_needs_regeneration` (bool), `_slos_stale_reason` (string), `_slos_stale_timestamp` (int)
- **Score Calculator (1.1.2):** `Compliance_Score_Calculator::calculate_legal_docs_dimension()` now uses `Document_Hub_Service` to check published status AND staleness; applies -10% penalty per stale doc
- **Test Coverage:** Comprehensive test suite in `test-legal-docs-integration.php` with 8 tests covering placeholders, shortcode, dashboard integration, staleness detection, score calculation, action hooks, all methods
- **Zero Errors:** All modified files validated with zero syntax errors, zero linter warnings
- **Backward Compatible:** Fallback to legacy `slos_detected_cookies` option, maintains existing option names, non-breaking changes only
- Completed: 2024

**Reference:** See [LEGAL-DOCS-INTEGRATION.md](LEGAL-DOCS-INTEGRATION.md) for integration points and shortcode specs.

### 1.2 Richer Geo Rules & Regional Presets

> **Note:** The plugin already has `Geo_Service.php` (IP detection, region mapping) and `Geo_Rule_Matcher.php` (priority-based matching, EU/EEA country lists, framework-to-template mapping). Rules are stored in the `slos_geo_rules` option. This phase focuses on **adding preset support and improving the admin UI**, not rebuilding the geo system.

| Task | Description | Files Affected | Status | AC |
|------|-------------|----------------|--------|-----|
| 1.2.1 | Define region presets data structure | `config/geo-presets.php` (new) | ✅ **DONE** | Array of presets: `EU`, `UK`, `US-CA`, `BR`, `ROW` with default consent model, banner template, required docs |
| 1.2.2 | Add preset application method to `Geo_Rule_Matcher` | `includes/Services/Geo_Rule_Matcher.php` | ✅ **DONE** | New `apply_preset($key)` method that populates `slos_geo_rules` option with preset data |
| 1.2.3 | Extend rule schema for legal_docs binding | `Geo_Rule_Matcher.php`, `Compliance_Score_Calculator.php` | ✅ **DONE** | Rules gain optional `legal_docs` and `preset_key` fields; backward compatible |
| 1.2.4 | Build preset selection UI in Geo Rules tab | `templates/admin/compliance/tabs/geo-rules.php` | ✅ **DONE** | Buttons: "Apply EU Preset", "Apply US-CA Preset", etc.; visual feedback on which preset is active |
| 1.2.5 | Surface geo rule coverage in Compliance Score | `Compliance_Score_Calculator` | ✅ **DONE** | `GEO_RULES` dimension uses existing `Geo_Rule_Matcher::get_active_rules()` to check coverage |

**Implementation Summary:**
- ✅ Created `config/geo-presets.php` with 5 regional presets (EU, UK, US-CA, BR, ROW)
- ✅ Added `apply_preset()`, `get_all_presets()`, `is_preset_applied()`, `get_preset_stats()` methods to Geo_Rule_Matcher
- ✅ Extended rule schema with `preset_key`, `legal_docs`, `states` fields (backward compatible)
- ✅ Enhanced GEO_RULES dimension in Compliance_Score_Calculator to check legal_docs binding (+10 bonus points)
- ✅ Built preset selection UI with emoji icons, active state indicators, and hover animations
- ✅ Added REST API endpoints: `POST /geo/presets/{key}/apply`, `GET /geo/presets`
- ✅ Implemented AJAX handler with success notifications and page reload on preset application
- ✅ Created comprehensive test suite: `tests/test-geo-presets.php` with 12 tests covering all preset operations

**Reference:** See [GEO-RULES-REFERENCE.md](GEO-RULES-REFERENCE.md) for preset definitions and rule schema.

### 1.3 Time-Series Insights & Exports

| Task | Description | Files Affected | AC | Status |
|------|-------------|----------------|-----|--------|
| 1.3.1 | Add `get_time_series()` method to `Consent_Service` | `Consent_Service.php` | Returns daily/weekly/monthly aggregates by status, type, region | ✅ |
| 1.3.2 | Integrate Chart.js (or similar) into dashboard | `assets/js/compliance-charts.js` (new), enqueue in `shahi-legalflowsuite.php` | Line/bar chart of consent decisions over time | ✅ |
| 1.3.3 | Build export endpoints (CSV, PDF) | `includes/Ajax/Compliance_Export_Ajax.php` (new) | CSV of consents, audit logs; PDF summary report | ✅ |
| 1.3.4 | Add "Export" quick action to dashboard | Dashboard template | Buttons: "Export CSV", "Download PDF Report", "Export Audit Log" | ✅ |

**Status:** ✅ **COMPLETE** - All tasks implemented and verified.

**Implementation Summary:**
- ✅ Added `get_time_series()` method to `Consent_Service` with daily/weekly/monthly aggregation, group_by options (status/type/region), and Chart.js-compatible format
- ✅ Created `Compliance_Export_Ajax.php` with 4 AJAX endpoints: CSV export, PDF report, audit log export, and time-series data fetching
- ✅ Integrated Chart.js 4.4.1 via CDN with custom `compliance-charts.js` for interactive trend visualization
- ✅ Added consent trends chart to dashboard with time range selector (7/30/90 days) and group_by selector
- ✅ Updated Quick Actions section with 3 export buttons: Export CSV, Export PDF Report, Export Audit Log
- ✅ Registered AJAX handlers and enqueued assets in `shahi-legalflowsuite.php`
- ✅ Created comprehensive test suite: `tests/test-phase-1-3.php` with 12 tests covering all time-series and export operations
- ✅ Zero syntax errors verified across all modified files

**Reference:** Export handlers use dompdf for PDF generation, stream CSV files with proper headers, and support filtering by date range, status, and type.

### 1.4 Enhanced Consent UI & Vendor Transparency

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| 1.4.1 | Extend preferences center with category descriptions | `consent-banner.js`, new config fields | Each category toggle has expandable description |
| 1.4.2 | Add optional vendor/service breakdown | `consent-banner.js`, admin settings | If enabled, list services (e.g., "Google Analytics") under each category |
| 1.4.3 | Implement embed placeholders for blocked content | `assets/js/consent-placeholders.js` (new), CSS | Placeholder div with message + "Enable [Category]" button; replaces iframes until consent |
| 1.4.4 | Document placeholder shortcode/block | User docs / `readme.txt` | `[slos_embed_placeholder category="marketing"]` or Gutenberg block |

### Phase 1 Deliverables

- Legal Docs tab with Cookie Policy and Privacy Policy generators.
- Geo presets and multi-region rule support.
- Time-series charts and CSV/PDF exports.
- Enhanced preferences center with vendor transparency and embed placeholders.

---

## Phase 2 – Differentiated "LegalOps + Accessibility" Positioning

**Goal:** Elevate the module into a true Compliance Operations hub by integrating DSR workflows, accessibility checks, and multi-site tooling.

### 2.1 Compliance Operations Dashboard

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| 2.1.1 | Create unified "Ops Dashboard" view | New template or dashboard redesign | Single view aggregating: Consent stats, Cookie scanner status, Accessibility issues count, DSR queue summary |
| 2.1.2 | Introduce overall "Ops Readiness Score" | `Compliance_Score_Calculator` extended | Weighted composite of Consent, Cookies, DSR, Accessibility dimensions |
| 2.1.3 | Add drill-down links from each dimension card | Dashboard template | Click "Cookies" card → Cookie Scanner tab; click "DSR" → DSR admin page |

### 2.2 DSR & Consent Audit Trail Integration

> **Note:** The plugin already has a full DSR module (`DSR_Service`, `DSR_Audit_Service`, `DSR_Export_Service`, etc.) with 7 GDPR rights, SLA tracking, and reporting. This phase focuses on **linking** consent records to DSR workflows.

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| 2.2.1 | Add consent lookup by email to `Consent_Service` | `Consent_Service.php`, `Consent_Repository.php` | New method `get_by_email($email)` returns all consents for a given email (hashed or direct match) |
| 2.2.2 | Display consent history in DSR detail view | `DSRRequestDetail.php`, `templates/admin/dsr/detail.php` | Collapsible "Consent History" section showing user's consent timeline fetched via 2.2.1 |
| 2.2.3 | Include consent data in DSR export packages | `DSR_Export_Service.php` | When exporting user data for access/portability requests, include consent records and audit logs |
| 2.2.4 | Surface DSR stats in Compliance Ops Dashboard | `Compliance_Score_Calculator`, `DSR_Service` | Dashboard shows: Open DSR requests, SLA compliance %, link to DSR module |

### 2.3 Accessibility-Aware Consent UX Checker

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| 2.3.1 | Create mini-scanner for consent/legal pages | `includes/Modules/AccessibilityScanner/ConsentUxChecker.php` (new) | Runs subset of checks: focus order, ARIA, contrast, heading structure |
| 2.3.2 | Surface results in Compliance dashboard | Dashboard template | "Consent UX Health" card with issue count and link to full scanner |
| 2.3.3 | Auto-scan consent and legal pages on publish/update | Hook into `save_post` for designated pages | Re-run mini-scanner when privacy/cookie/accessibility pages are saved |

### 2.4 Multi-Site / Organization View (Optional / Long-Term)

| Task | Description | Files Affected | AC |
|------|-------------|----------------|-----|
| 2.4.1 | Design config sync profile schema | `config/multisite-sync-schema.php` | JSON schema for exportable/importable settings bundle |
| 2.4.2 | Implement export/import of compliance config | New admin UI section | "Export Config" → JSON file; "Import Config" → applies settings |
| 2.4.3 | Network admin rollup view (multisite only) | `includes/Admin/Network_Compliance_Dashboard.php` | Aggregate stats across sites; highlight sites with low readiness scores |

### Phase 2 Deliverables

- Unified Ops Dashboard with cross-module aggregation.
- DSR ↔ Consent log linkage and case package exports.
- Mini accessibility scanner for consent/legal pages.
- (Optional) Multi-site config sync and network dashboard.

---

## Sequencing & Dependencies

```
Phase 0 ──────────────────────────────────────────────────────────────►
  0.1 Readiness Score
  0.2 Versioning
  0.3 Scanner Clarity
                        Phase 1 ──────────────────────────────────────►
                          1.1 Legal Docs (depends on 0.3 for cookie data)
                          1.2 Geo Rules
                          1.3 Time-Series (depends on 0.2 for richer data)
                          1.4 Consent UI
                                              Phase 2 ─────────────────►
                                                2.1 Ops Dashboard (depends on 0.1, 1.3)
                                                2.2 DSR Integration
                                                2.3 A11y Checker
                                                2.4 Multi-Site (optional)
```

**Critical Path:**

1. Phase 0.1 (Readiness Score) unblocks accurate dashboard messaging.
2. Phase 0.3 (Scanner Clarity) unblocks Phase 1.1 (Legal Docs need cookie data).
3. Phase 1.3 (Time-Series) benefits from Phase 0.2 (Versioning) for richer breakdowns.
4. Phase 2.1 (Ops Dashboard) aggregates outputs from Phases 0 and 1.

---

## Effort Estimates (T-Shirt Sizing)

| Phase | Effort | Notes |
|-------|--------|-------|
| 0.1 | M | New service + dashboard changes |
| 0.2 | S | DB migration + minor service edits |
| 0.3 | M | AJAX handler + template + option storage |
| 1.1 | L | Multiple generators, shortcodes, new tab |
| 1.2 | M | Presets config + model + UI |
| 1.3 | M | Charting library integration + export endpoints |
| 1.4 | M | JS enhancements + placeholder system |
| 2.1 | M | Dashboard redesign + aggregation logic |
| 2.2 | M | Cross-module linkage + export |
| 2.3 | S | Subset of existing scanner logic |
| 2.4 | L | Network admin, sync schema (optional) |

**Total:** ~3–4 weeks for Phase 0, ~4–6 weeks for Phase 1, ~3–5 weeks for Phase 2 (excluding 2.4).

---

## Risk Mitigation

| Risk | Mitigation |
|------|------------|
| Breaking existing consent data | Migration scripts are additive (new columns nullable); existing data untouched |
| Performance of time-series queries | Add indexes on `created_at`, `status`, `type`; consider summary table for large sites |
| Chart library bloat | Use lightweight charting (Chart.js minimal bundle or Alpine-based) |
| Legal doc accuracy | Clearly label generated docs as templates requiring legal review; add disclaimers |
| Multi-site complexity | Phase 2.4 is optional; start with single-site export/import for agencies |

---

## Acceptance Criteria (Phase Gates)

### Phase 0 Complete When:

- [✅] Dashboard shows multi-dimension readiness score with per-dimension breakdown.
- [✅] Disclaimer microcopy is visible below score.
- [✅] New consents include `banner_version` and `policy_version`.
- [✅] Cookie Scanner tab shows last-scan metadata and "Rescan Now" works.
- [✅] Uncategorized cookies negatively impact the COOKIES dimension score.

### Phase 1 Complete When:

- [ ] "Legal Docs" tab lists Cookie Policy and Privacy Policy with generate/update actions.
- [ ] `[slos_cookie_policy]` shortcode renders detected cookies by category.
- [ ] At least 3 geo presets (EU, US-CA, ROW) are selectable and affect banner behavior.
- [ ] Dashboard includes a time-series chart of consent decisions.
- [ ] CSV and PDF exports are downloadable from dashboard.
- [ ] Preferences center shows category descriptions and optional vendor list.
- [ ] Embed placeholders block iframes until relevant category is consented.

### Phase 2 Complete When:

- [ ] Ops Dashboard aggregates Consent, Cookies, DSR, Accessibility stats.
- [ ] DSR detail view shows linked consent history.
- [ ] Case package ZIP is downloadable for a DSR request.
- [ ] Mini accessibility scanner runs on consent/legal pages and surfaces issues.
- [ ] (Optional) Config export/import works; network rollup view functional on multisite.

---

## Next Steps

1. Review this plan with stakeholders; confirm priorities.
2. Create feature branches: `feature/phase-0-readiness-score`, etc.
3. Begin Phase 0.1 (Readiness Score) immediately; it unblocks dashboard honesty.
4. Schedule design review for Phase 1.1 (Legal Docs) to finalize template structure.
5. Document user-facing changes in `CHANGELOG.md` as each phase ships.

---

_Maintained by: Shahi LegalOps Suite Team_
