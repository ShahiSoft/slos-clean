# Auto-Fix System Implementation Plan (Plan Two)

_Date: 2025-12-29_
_Goal: Reach reliable, fully functional auto-fix coverage across all registered fixers, grounded in current code._

## Objectives (grounded in code)
- Complete fixers that currently return zero fixes or are stubs (seen in `Fixes/Fixers/InteractivityFixers.php` and related files).
- Ensure every fixer that claims support in `FixerRegistry.php` has working logic and reports accurate `fixed_count`.
- Persist fix results and issue deltas reliably so dashboard counts stay in sync without manual re-scans.
- Add safety (undo/backup) and observability (history/logging) without breaking existing flows.
- Validate through automated checks using real HTML fixtures (see `acc-new/TEST-FIXTURES.md`).

## Scope & Constraints
- Work only against actual code under `includes/Modules/AccessibilityScanner/Fixes/` and related assets (JS/CSS/PHP).
- No duplicate registry keys; keep `FixerRegistry::init()` mappings one-to-one and maintain aliases.
- Maintain backward compatibility of AJAX endpoints in `AccessibilityScanner.php` and UI in `assets/js/slos-autofix-progress.js`.

## Workstreams & Tasks

### P0: Make every fixer actually fix ✅
- Implement real logic for zero-return fixers in `InteractivityFixers.php`:
  - `TextColorContrastFixer`, `ComplexContrastFixer`, `ColorRelianceFixer` (verify current logic), `FocusIndicatorFixer`, `KeyboardTrapFixer`, `FocusOrderFixer`, `TouchTargetFixer`, `TouchGestureFixer`, `ViewportFixer`, `AriaStateFixer`, `InvalidAriaCombinationFixer`, `PageStructureFixer`.
- Verify new Phase 3 fixers (`AnimationPauseFixer.php`, `StatusMessageFixer.php`, `TimingControlFixer.php`, `LanguageChangeFixer.php`, `ErrorIdentificationFixer.php`) for non-zero `fixed_count` and content changes.
- Standardize fixer returns to include both `fixed_count` and `content`; avoid mixed `fixes_applied` keys.

### P0: Accuracy of persistence and counts ✅
- After each fixer run (AJAX single and bulk), re-scan and persist:
  - `update_post_meta(_slos_accessibility_scan_results)` and date are already called in `AccessibilityScanner::ajax_autofix_single_fixer`; ensure the same after bulk flows (`ajax_fix_all_issues`).
- Add guardrails so skipped/no-content cases do not mark success.

### P1: Fix history & undo safety ✅
- Add lightweight history table (or post meta fallback) capturing: post_id, fixer_id, fixed_count, before/after hashes or issue counts, user_id, timestamp.
- Before mutating content, save original to a reversible backup (post meta) with TTL to limit bloat; expose one-click rollback in admin (optional toggle to keep scope tight).

### P1: Batch efficiency and status coherence ✅
- Optimize bulk flow in `ajax_fix_all_issues` to avoid duplicate fixer runs per issue type; ensure unique fixers only (already partially done) and short-circuit when `fixed_count === 0`.
- Improve frontend status mapping in `slos-autofix-progress.js` to reflect skipped/error states distinctly and prevent false “fixed” tallies.

### P1: Validation & tests (grounded in fixtures) ✅
- Create PHP unit-style harness (or lightweight integration script) using fixtures from `acc-new/TEST-FIXTURES.md` to assert:
  - Each fixer modifies content when applicable and leaves unchanged content untouched.
  - `fixed_count` > 0 when changes occur; 0 when none.
  - Post-content roundtrip through `DOMDocument` preserves structure (no tag loss).
- Add regression checks for recent fixers: LanguageChange, StatusMessage, ErrorIdentification, AnimationPause, TimingControl.

### P2: UX and resilience ✅
- Progress UI: surface element-level errors and final rescan stats; prevent closing modal while processing unless explicitly confirmed.
- Timeouts/retries for AJAX per fixer; cancel should abort outstanding requests.

## Deliverables
- Updated fixer implementations (PHP) with real logic and consistent return format.
- History/backup storage (table or meta) with minimal schema and hooks.
- Adjusted AJAX flows to guarantee rescan + persistence after successful fixes.
- Test harness + fixtures-driven assertions; documented run steps.
- Minor UI/UX polish in progress modal to reflect accurate states.

## Sequencing
1) Implement zero-return fixers with tests (P0).
2) Normalize fixer return formats; ensure registry integrity (P0).
3) Wire reliable rescan/persistence paths in AJAX flows (P0).
4) Add history + optional undo safety (P1).
5) Optimize batch flow + UI statuses (P1).
6) Broaden tests across new fixers and fixtures (P1).
7) Optional UX polish and retry/cancel hardening (P2).

## Risks & Mitigations
- **Over-fixing / false positives:** constrain selectors, add targeted XPath/CSS checks; gate risky fixes behind settings flags.
- **Content corruption:** keep pre-fix backups and DOM roundtrip checks in tests; fail closed if DOM parse fails.
- **Duplicate registry entries:** run a registry uniqueness check in CI/lint step before deploy.

## Acceptance Criteria
- All registered fixers produce accurate `fixed_count` and real content changes when applicable.
- Post meta and consolidated counts stay in sync after auto-fix without manual rescan.
- History/backup exists for each applied fix; optional rollback path available.
- Tests pass on fixtures demonstrating fixes for the previously zero-return classes.
- UI shows truthful fixed/error/skipped counts and disallows silent failures.
