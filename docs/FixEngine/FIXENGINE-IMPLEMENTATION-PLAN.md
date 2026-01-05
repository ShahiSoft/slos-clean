# FixEngine & Autofix Implementation Plan

## Goals

- Use **one** FixEngine pipeline as the source of truth for autofixing.
- Make all implemented fixers (≈72+ canonical IDs) available to that engine.
- Ensure the Auto‑Fix modal:
  - Runs **only** fixers that are relevant for the page (with an explicit, rare “run all” mode if needed).
  - Reports status in a way that clearly matches real behavior (no misleading “No issues found”).

---

## Phase 1 – Stabilize current Autofix selection + modal

1. **Backend: stop always forcing “all fixers”**
   - In `ajax_get_page_fixable_issues()` (AccessibilityScanner.php):
     - Change the JSON payload to:
       - `fixers` = `$fixers_with_issues` (unchanged).
       - `use_all_fixers` = `false` (or remove it entirely unless explicitly needed).
   - This makes the response “page specific” instead of “page specific + run everything”.

2. **Frontend: prefer page‑specific fixers, fall back only when asked**
   - In `fetchPageScanResults()` (slos-autofix-progress.js), change the selection logic to:
     - If `data.fixers && data.fixers.length > 0 && data.use_all_fixers !== true` → `callback(data.fixers)`.
     - Else if `data.use_all_fixers === true` → `callback(self.getDefaultFixers())`.
     - Else → `callback([])` (no fixable issues).
   - Result: the modal’s “Processing X of Y fixers…” will use **only** fixers mapped from scan results by default.

3. **Clarify “skipped” wording**
   - In `renderFixerList()` (slos-autofix-progress.js), change skipped text from `"No issues found"` to something like `"Not applicable on this page"` when `fixer.status === 'skipped'`.
   - Keep `"No issues found"` only for the overall “no fixable issues for this page” state (`showNoIssuesMessage()`).

4. **Align FixerRegistry “count” to reality**
   - In `FixerRegistry::get_fixer_count()` (FixerRegistry.php), change implementation to:
     - Iterate `self::$registry` and count only IDs where `self::get_fixer($id)` returns an instance.
   - This ensures any logged/diagnostic “fixer count” matches what the modal can actually run.

**Acceptance:**
- Modal shows a smaller number that corresponds to page‑relevant fixers.
- Most lines do not say “No issues found”; only truly non‑applicable fixers do.
- Fixer count in logs/diagnostics matches the modal.

---

## Phase 2 – Make Canonical FixEngine the single source of truth

5. **Decide the canonical namespace + base class**
   - Use the SOLID FixEngine as the **only** engine:
     - Canonical IDs & metadata: `FixEngine/CanonicalIds.php`.
     - Base interface / abstract class: `FixerInterface` / `AbstractFixer` in FixEngine.
     - Discovery: `FixerCollection::auto_discover()` in `FixEngine/FixerCollection.php`.

6. **Inventory and align all implemented fixers**
   - List all canonical IDs marked `'auto_fixable' => true` in CanonicalIds.
   - For each ID, verify there is a corresponding **FixEngine** fixer class (in `FixEngine/Fixers`) that:
     - Implements `FixerInterface` / extends `AbstractFixer`.
     - Returns the same canonical ID from `get_id()` as in CanonicalIds.
   - For IDs that only exist in legacy `FixerRegistry` classes (BaseFixer in `Fixes/Fixers`):
     - Either:
       - **Option A (preferred)**: Port logic into new classes under the FixEngine namespace, using the canonical ID, or
       - **Option B (interim)**: Create thin adapter classes in FixEngine that internally delegate to the existing BaseFixer logic, then gradually refactor.

7. **Ensure 1:1 mapping between CanonicalIds and FixEngine fixers**
   - Add a dev‑only diagnostic (CLI or admin debug page) that:
     - Loops over `CanonicalIds::get_auto_fixable()` and checks `$engine->get_fixer($id)` is non‑null.
     - Flags any canonical ID without an implemented fixer class or any fixer class whose `get_id()` is not in CanonicalIds.
   - Goal: canonical list and actual classes always stay in sync.

**Acceptance:**
- For every auto‑fixable canonical ID, there is exactly one FixEngine fixer class that implements it.
- `FixerCollection::count()` roughly matches `count(CanonicalIds::get_auto_fixable())`.

---

## Phase 3 – Route Autofix execution through FixEngine only

8. **Wire `ajax_autofix_single_fixer()` to FixEngine**
   - In `AccessibilityScanner.php`:
     - Replace the “TEMPORARY: FixEngine disabled…” block that uses FixerRegistry with:
       - Instantiate FixEngine (e.g., via a shared service or `new FixEngine(new FixHistoryRepository())`).
       - `$fixer = $engine->get_fixer($fixer_id);` using canonical ID.
       - If `$fixer` is null, optionally fall back to FixerRegistry **only during migration**, and log a warning.
       - Call `handle_fix_engine_request($engine, $fixer, $fixer_id, $page_id, $content)` instead of the legacy logic.
   - Keep the same JSON success/error shapes so the JS doesn’t need to change.

9. **Have `slos_get_page_fixable_issues` depend on CanonicalIds/FixEngine**
   - In `ajax_get_page_fixable_issues()`:
     - Instead of initializing FixerRegistry, use CanonicalIds + FixEngine:
       - Build a checker→canonical fixer map using canonical IDs.
       - Optionally validate each target fixer exists via FixEngine for safety.
     - Construct `$fixers_with_issues` using FixEngine’s fixer instances (`get_name()`, `get_description()`, etc.).

10. **Unify check‑ID mapping**
    - Update `get_check_to_fixer_mapping()` to map **scan result IDs → canonical fixer IDs** exactly as they appear in CanonicalIds.
    - Remove or normalize any legacy IDs (`'alt-quality'`, `'image-map'`, etc.) that no longer exist canonically, or add canonical aliases into CanonicalIds if they’re still needed for backward compatibility.

**Acceptance:**
- All autofix AJAX paths (`slos_autofix_single`, `slos_get_page_fixable_issues`) go through FixEngine first.
- FixerRegistry is only used behind an optional, logged fallback (or not at all).

---

## Phase 4 – Make the modal reflect FixEngine accurately

11. **Use FixEngine’s metadata for names and descriptions**
   - Ensure each FixEngine fixer implements:
     - `get_name()` – human‑friendly name.
     - `get_description()` – “what this fixer does”.
   - In `localize_autofix_progress_script()` (Core/Assets.php), change from FixerRegistry to FixEngine:
     - Resolve the shared FixEngine instance, call `$engine->get_fixers()->to_array()` (or equivalent), and pass that into `slosautoFixConfig.fixers`.

12. **Differentiate “no issues here” vs “fixer not applicable”**
   - In JS, introduce clearer states:
     - `status = 'success'` + `count > 0` → “Fixed: N”.
     - `status = 'skipped'` because engine reports “no issues in this content” → “No applicable issues on this page”.
     - `status = 'skipped'` because fixer was missing (fallback case) → “Fixer unavailable” (ideally this shouldn’t happen after Phase 3).
   - If desired, add an optional `reason` field in the backend JSON for skipped cases and branch the message in `renderFixerList()`.

13. **Optional: Group and order fixers**
   - Use canonical categories (`CAT_IMAGES`, `CAT_FORMS`, etc.) from CanonicalIds in FixEngine’s `to_array()` to:
     - Group fixers in the modal.
     - Prioritize fixers that have issues on the page at the top (using `$fixers_with_issues` as a “highlight” list).

**Acceptance:**
- All names/descriptions in the modal come from the same FixEngine fixers that actually run.
- Users can see clearly which fixers changed something, which were not applicable, and any that errored.

---

## Phase 5 – Clean‑up, tests, and documentation

14. **Remove / deprecate legacy paths**
   - Once all fixers are migrated and FixEngine is stable:
     - Deprecate FixerRegistry and BaseFixer, or leave them as thin adapters around FixEngine if needed for BC.
     - Remove the “TEMPORARY: FixEngine disabled…” comments and any unused migration code.

15. **Add regression tests**
   - Unit tests:
     - For `ajax_get_page_fixable_issues()` (with synthetic scan meta) to confirm it returns only the expected fixers and does not set `use_all_fixers` unless configured.
     - For `ajax_autofix_single_fixer()` to verify correct JSON responses for:
       - Fix applied.
       - No applicable issues.
       - Missing fixer.
   - JS integration / browser tests to assert:
     - “Fix All” shows only relevant fixers.
     - Counts and statuses update correctly as fixes complete.

16. **Update internal docs**
   - In FixEngine docs and /docs/autofix:
     - Document the single FixEngine pipeline.
     - List how to add a new fixer:
       - Add canonical entry in CanonicalIds.
       - Implement FixEngine fixer class.
       - Update checker→fixer mapping if needed.
     - Clarify that the Autofix modal uses this engine exclusively.

---

This document is the working plan for migrating to a unified FixEngine and fixing the Autofix modal behavior and UX.
