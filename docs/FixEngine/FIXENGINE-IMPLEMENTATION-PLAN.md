# FixEngine & Autofix Implementation Plan

## Goals

- Use **one** FixEngine pipeline as the source of truth for autofixing.
- Make all implemented fixers (≈72+ canonical IDs) available to that engine.
- Ensure the Auto‑Fix modal:
  - Runs **only** fixers that are relevant for the page (with an explicit, rare “run all” mode if needed).
  - Reports status in a way that clearly matches real behavior (no misleading “No issues found”).

---

## Phase 1 – Stabilize current Autofix selection + modal

1. [x] **Backend: stop always forcing “all fixers”**
   - In `ajax_get_page_fixable_issues()` (AccessibilityScanner.php):
     - Change the JSON payload to:
       - `fixers` = `$fixers_with_issues` (unchanged).
       - `use_all_fixers` = `false` (or remove it entirely unless explicitly needed).
   - This makes the response “page specific” instead of “page specific + run everything”.

2. [x] **Frontend: prefer page‑specific fixers, fall back only when asked**
   - In `fetchPageScanResults()` (slos-autofix-progress.js), change the selection logic to:
     - If `data.fixers && data.fixers.length > 0 && data.use_all_fixers !== true` → `callback(data.fixers)`.
     - Else if `data.use_all_fixers === true` → `callback(self.getDefaultFixers())`.
     - Else → `callback([])` (no fixable issues).
   - Result: the modal’s “Processing X of Y fixers…” will use **only** fixers mapped from scan results by default.

3. [x] **Clarify “skipped” wording**
   - In `renderFixerList()` (slos-autofix-progress.js), change skipped text from `"No issues found"` to something like `"Not applicable on this page"` when `fixer.status === 'skipped'`.
   - Keep `"No issues found"` only for the overall “no fixable issues for this page” state (`showNoIssuesMessage()`).

4. [x] **Align FixerRegistry “count” to reality**
   - In `FixerRegistry::get_fixer_count()` (FixerRegistry.php), change implementation to:
     - Iterate `self::$registry` and count only IDs where `self::get_fixer($id)` returns an instance.
   - This ensures any logged/diagnostic “fixer count” matches what the modal can actually run.

**Acceptance:**
- Modal shows a smaller number that corresponds to page‑relevant fixers.
- Most lines do not say “No issues found”; only truly non‑applicable fixers do.
- Fixer count in logs/diagnostics matches the modal.

---

## Phase 2 – Make Canonical FixEngine the single source of truth

5. [x] **Decide the canonical namespace + base class**
   - Use the SOLID FixEngine as the **only** engine:
     - Canonical IDs & metadata: `FixEngine/CanonicalIds.php`.
     - Base interface / abstract class: `FixerInterface` / `AbstractFixer` in FixEngine.
     - Discovery: `FixerCollection::auto_discover()` in `FixEngine/FixerCollection.php`.

6. [x] **Inventory and align all implemented fixers**
   - Used `CanonicalIds::get_auto_fixable()` to list all canonical IDs marked `'auto_fixable' => true`.
   - Ran `wp slos fixengine inventory` (FixEngineCommand) to generate a read‑only report that:
     - Counts canonical auto‑fixable IDs vs implemented FixEngine fixers.
     - Lists any canonical IDs that are missing a FixEngine implementation.
   - For each previously missing ID, added a corresponding **FixEngine** fixer class under `FixEngine/Fixers` that:
     - Implements `FixerInterface` / extends `AbstractFixer`.
     - Returns the same canonical ID from `get_id()` as in CanonicalIds.
   - For IDs that only existed in legacy `FixerRegistry` classes (BaseFixer in `Fixes/Fixers`):
     - Implemented thin adapter classes in FixEngine that internally delegate to the existing BaseFixer logic, to be gradually refactored later.

7. [x] **Ensure 1:1 mapping between CanonicalIds and FixEngine fixers**
   - Added a dev‑only WP‑CLI diagnostic `wp slos fixengine validate-mapping` that:
     - Reflects into `AccessibilityScanner::get_check_to_fixer_mapping()`.
     - Runs each mapped fixer ID through `CanonicalIds::canonicalize()`.
     - Reports any checker→fixer mappings that do **not** resolve to a valid canonical FixEngine ID, failing the command in that case.
   - This provides a concrete guardrail to keep the checker→fixer mapping and CanonicalIds in sync as new fixers/checkers are added.

**Acceptance:**
- For every auto‑fixable canonical ID, there is a FixEngine fixer class that implements it (either native or via a legacy adapter).
- `FixEngineCommand::inventory` now reports `Missing implementations: 0` with canonical auto‑fixable IDs equalling the number of implemented FixEngine fixers (within expected tolerance for non‑auto‑fixable helpers).

---

## Phase 3 – Route Autofix execution through FixEngine only

8. [x] **Wire `ajax_autofix_single_fixer()` to FixEngine**
   - In `AccessibilityScanner.php`:
     - Replace the “TEMPORARY: FixEngine disabled…” block that uses FixerRegistry with:
       - Instantiate FixEngine (e.g., via a shared service or `new FixEngine(new FixHistoryRepository())`).
       - `$fixer = $engine->get_fixer($fixer_id);` using canonical ID.
       - If `$fixer` is null, optionally fall back to FixerRegistry **only during migration**, and log a warning.
       - Call `handle_fix_engine_request($engine, $fixer, $fixer_id, $page_id, $content)` instead of the legacy logic.
   - Keep the same JSON success/error shapes so the JS doesn’t need to change.

9. [x] **Have `slos_get_page_fixable_issues` depend on CanonicalIds/FixEngine**
   - In `ajax_get_page_fixable_issues()`:
     - Instead of initializing FixerRegistry, use CanonicalIds + FixEngine:
       - Build a checker→canonical fixer map using canonical IDs.
       - Optionally validate each target fixer exists via FixEngine for safety.
     - Construct `$fixers_with_issues` using FixEngine’s fixer instances (`get_name()`, `get_description()`, etc.).

10. [x] **Unify check‑ID mapping**
  - Ensure `get_check_to_fixer_mapping()` maps **scan result IDs → canonical fixer IDs** as defined in CanonicalIds, relying on `CanonicalIds::canonicalize()` for any legacy aliases.
  - Current mapping uses canonical IDs on the right‑hand side; aliases such as `'alt-quality'`, `'image-map'`, etc. are normalized centrally in `CanonicalIds::$aliases`, keeping a single source of truth for ID normalization.

**Acceptance:**
- All autofix AJAX paths (`slos_autofix_single`, `slos_get_page_fixable_issues`) go through FixEngine first.
- FixerRegistry is only used behind an optional, logged fallback (or not at all).

---

## Phase 4 – Make the modal reflect FixEngine accurately

11. [x] **Use FixEngine’s metadata for names and descriptions**
   - Ensure each FixEngine fixer implements:
     - `get_name()` – human‑friendly name.
     - `get_description()` – “what this fixer does”.
   - In `localize_autofix_progress_script()` (Core/Assets.php), change from FixerRegistry to FixEngine:
     - Resolve the shared FixEngine instance, call `Bootstrap::get_scanner_data()['fixers']`, and pass that into `slosautoFixConfig.fixers` so the JS catalog is the same one FixEngine uses internally.

12. [x] **Differentiate “no issues here” vs “fixer not applicable”**
   - Backend now includes a structured `reason` field for skipped cases in `ajax_autofix_single_fixer()`:
     - `reason = 'no-issues'` when there were no applicable issues.
     - `reason = 'no-content'` when the page has no content to process.
     - `reason = 'fixer-unavailable'` when a legacy fixer cannot be found.
   - In JS (`renderFixerList()` in slos-autofix-progress.js), skipped fixers branch on `fixer.reason` to render clear messages for each case, defaulting to “Not applicable on this page” when no reason is provided.

13. [x] **Optional: Group and order fixers**
   - Use canonical categories (`CAT_IMAGES`, `CAT_FORMS`, etc.) from CanonicalIds in FixEngine’s `to_array()` to:
     - Group fixers in the modal.
     - Prioritize fixers that have issues on the page at the top (using `$fixers_with_issues` as a “highlight” list).
   - Implement grouping purely in `slos-autofix-progress.js` by:
     - Building a canonical fixer catalog from `slosFixEngine.fixers` (falling back to `slosautoFixConfig.fixers` when needed).
     - Grouping and sorting fixers by category key, rendering readable category headers, and avoiding any duplicate catalogs or hard‑coded fixer lists in JS.

**Acceptance:**
- All names/descriptions in the modal come from the same FixEngine fixers that actually run.
- Users can see clearly which fixers changed something, which were not applicable, and any that errored.

---

## Phase 5 – Clean‑up, tests, and documentation

14. [x] **Remove / deprecate legacy paths**
  - Retired the UnifiedFixerRouter hybrid wrapper so there is no separate public routing layer; FixEngine is the only canonical execution pipeline.
  - Removed all FixEngine dependencies on FixerRegistry: every FixEngine fixer now either contains its own DOM logic or, for very complex behaviors, delegates directly to a single legacy fixer class without going through the registry.
  - Verified via `wp slos fixengine inventory` that:
    - Canonical auto-fixable IDs: 58
    - Implemented FixEngine fixers: 61
    - Missing implementations: 0
  - FixerRegistry and BaseFixer are no longer used by any FixEngine fixers; they remain only as legacy/internal implementation details for older code paths outside the FixEngine pipeline.

15. [ ] **Add regression tests**
   - Unit tests:
     - For `ajax_get_page_fixable_issues()` (with synthetic scan meta) to confirm it returns only the expected fixers and does not set `use_all_fixers` unless configured.
     - For `ajax_autofix_single_fixer()` to verify correct JSON responses for:
       - Fix applied.
       - No applicable issues.
       - Missing fixer.
   - JS integration / browser tests to assert:
     - “Fix All” shows only relevant fixers.
     - Counts and statuses update correctly as fixes complete.

16. [x] **Update internal docs**
   - In FixEngine docs and /docs/autofix:
     - Document the single FixEngine pipeline and the fact that
       FixerRegistry is no longer used for live autofix flows, with
       an optional `SLOS_ENABLE_LEGACY_FIXERS` constant available for
       emergency, opt-in legacy execution on misconfigured sites.
     - List how to add a new fixer:
       - Add canonical entry in CanonicalIds.
       - Implement FixEngine fixer class.
       - Update checker→fixer mapping if needed.
     - Clarify that the Autofix modal uses this engine exclusively.

---

This document is the working plan for migrating to a unified FixEngine and fixing the Autofix modal behavior and UX.
