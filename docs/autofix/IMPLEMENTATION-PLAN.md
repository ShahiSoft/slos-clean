# Autofix System Implementation Plan (AI-Executable)

**Date:** 2026-01-04  
**Author:** Senior WordPress Plugin Architect  
**Scope:** Execute all audit recommendations with SOLID compliance, zero known errors left behind.  
**Target Systems:** Legacy Fixes, New FixEngine, UI, DB, AJAX, Tests, Ops

---

## Guiding Principles
- Prefer the NEW FixEngine as the single future architecture (unless explicitly switched). 
- Maintain backward compatibility during migration; no broken endpoints. 
- Enforce SOLID: Single responsibility for fixers, interfaces for contracts, dependency inversion for services, clear separation of orchestration vs. execution. 
- Fail closed: validate inputs, guard all dynamic loads, log and surface errors. 
- Idempotence: Fix operations must be repeatable without drift. 
- Observability: Measure execution time, counts, errors; store structured telemetry. 

---

## Phase 0 – Readiness & Safety (Day 0-1)
- [ ] Create feature flag: `slos_fixengine_enabled` (option) with per-site toggle. Default: ON only in staging. 
- [ ] Add maintenance mode flag for migrations: `slos_fixengine_migration_lock`. 
- [ ] Backup: DB (wp_slos_fix_history, postmeta, options) and Git tag `pre-fixengine-migration`. 
- [ ] Add error logging channel: `error_log` + optional WP_CLI echo. Include context (post_id, fixer_id, session_id). 
- [ ] Establish coding standards: PSR-12, strict types where possible, WordPress esc/nonce/caps. 
- [ ] Set up CI job to run unit/integration tests and phpcs.

Exit criteria: Flags exist; backups done; CI/standards configured.

---

## Phase 1 – ID Canonicalization (Day 1-2)
- [ ] Define single canonical fixer/checker ID list (source of truth file). 
- [ ] Update FixEngine fixers to use canonical IDs. 
- [ ] Update scanner checkers and any JS/DB references to canonical IDs. 
- [ ] Remove alias indirections; add validation error when unknown ID requested. 
- [ ] Write migration script to update stored IDs in options/postmeta/history. 

Exit criteria: One-to-one ID mapping; aliases removed; migration script tested on sample data.

---

## Phase 2 – Fixer Parity & Porting (Day 3-15)
- [ ] Inventory all legacy fixers (75+) vs FixEngine (31). 
- [ ] Create porting checklist; sort by severity/usage. 
- [ ] For each missing fixer: implement `AbstractFixer` with SOLID, can_fix guards, structured details. 
- [ ] Ensure deterministic behavior and XPath safety; add unit tests per fixer. 
- [ ] Add fixtures covering malformed HTML and large documents. 
- [ ] Update `FixEngine::auto_register_fixers` to dynamic discovery (filesystem scan) + allow filter hook for third-party fixers. 

Breakdown:
- [ ] Sub-phase 2.1: Coverage matrix (legacy vs new), severity/usage sort, prioritize criticals.
- [ ] Sub-phase 2.2: Implement dynamic discovery + filter hook; smoke test registration.
- [ ] Sub-phase 2.3: Port critical fixers batch (aria/link/image); ship with unit tests.
- [ ] Sub-phase 2.4: Port remaining fixers batch (forms/headings/tables/etc.); unit tests.
- [ ] Sub-phase 2.5: Determinism checks (same input -> same output); large-doc and malformed fixtures.
- [ ] Sub-phase 2.6: Parity verification report: 100% coverage achieved.

Exit criteria: 100% fixer parity in FixEngine; all new fixers unit-tested; dynamic registration passing.

---

## Phase 3 – Validation, Caching, and Performance (Day 10-17)
- [ ] Add class_exists validation before instantiation (legacy and new). 
- [ ] Introduce fixer instance pool (singleton per ID) in registry/collection. 
- [ ] Implement optional result cache keyed by (fixer_id, content hash) with TTL; flag-controlled. 
- [ ] Remove `the_content` runtime filtering; replace with on-demand fix-and-save flow plus preview. 
- [ ] Add execution time metrics to legacy path (until removed). 

Breakdown:
- [ ] Sub-phase 3.1: Validation guards + structured errors for missing classes/IDs.
- [ ] Sub-phase 3.2: Fixer instance pool; measure instantiation reduction.
- [ ] Sub-phase 3.3: Result cache with flag, TTL, metrics (hit/miss); opt-out path.
- [ ] Sub-phase 3.4: Remove runtime filter; implement fix-and-save + preview; content hash idempotence.
- [ ] Sub-phase 3.5: Perf benchmarks on sample posts; record before/after.

Exit criteria: No uncached instantiation loops; runtime filter removed; measurable perf gains on sample content.

---

## Phase 4 – AJAX/API Convergence (Day 15-20)
- [ ] Map all OLD endpoints to NEW FixEngine handlers behind feature flag. 
- [ ] Maintain backward-compatible response shape or provide versioned endpoints. 
- [ ] Add strict nonce/capability checks and sanitize all inputs. 
- [ ] Centralize JSON error format with error codes and user messages. 
- [ ] Timeouts and retries tuned; server-side safeguards for batch sizes.

Breakdown:
- [ ] Sub-phase 4.1: Flagged routing of old endpoints to FixEngine; smoke tests.
- [ ] Sub-phase 4.2: Response contract/versioning; document payloads.
- [ ] Sub-phase 4.3: Security hardening (nonce/caps/sanitize) + fuzz tests.
- [ ] Sub-phase 4.4: Batch limits, timeouts, retry semantics; load-test.
- [ ] Sub-phase 4.5: Emergency legacy toggle validation.

Exit criteria: Single execution path under flag; OLD path removable without breaking clients.

---

## Phase 5 – Persistence & Schema Optimization (Day 18-22)
- [ ] Change `wp_slos_fix_history.details` to `TEXT` or move to detail table; migrate data. 
- [ ] Add transactions for batch save (FixHistoryRepository). 
- [ ] Add indexes verified for query patterns; analyze with EXPLAIN. 
- [ ] Add content hash/version to detect drift; store before/after hashes. 
- [ ] Add repository read caching (transients) for dashboard queries. 

Breakdown:
- [ ] Sub-phase 5.1: Schema migration + rollback script; dry-run on staging snapshot.
- [ ] Sub-phase 5.2: Transactional batch saves in repository; unit + integration tests.
- [ ] Sub-phase 5.3: Index review/EXPLAIN and adjustments; document query plans.
- [ ] Sub-phase 5.4: Drift detection via content hashes; guarded writes.
- [ ] Sub-phase 5.5: Read caching for dashboards with cache-bust on write.

Exit criteria: Migrations applied; queries performant; no data loss; integrity validated.

---

## Phase 6 – UI/UX Alignment (Day 20-24)
- [ ] Update JS to use canonical IDs and new endpoints. 
- [ ] Ensure error surfaces include server error codes/messages. 
- [ ] Preserve WCAG behaviors; add loading/error states for each fixer row. 
- [ ] Add metrics display: success rate, avg execution time, top errors. 
- [ ] Remove references to deprecated legacy flows; keep flag-based fallback during rollout.

Breakdown:
- [ ] Sub-phase 6.1: Endpoint/ID switch to FixEngine; flag-driven rollout.
- [ ] Sub-phase 6.2: Error and status surfacing per fixer row; retry UX where safe.
- [ ] Sub-phase 6.3: WCAG audit (focus, live regions, keyboard trap, visible focus).
- [ ] Sub-phase 6.4: Metrics UI (success rate, exec time, errors) from repository APIs.
- [ ] Sub-phase 6.5: Remove legacy coupling; verify fallback flag behavior.

Exit criteria: UI fully driven by FixEngine; accessible; metrics visible; no legacy coupling.

---

## Phase 7 – Testing & Quality Gates (Day 10-24, continuous)
- [ ] Unit tests for every fixer (happy, skipped, error, malformed HTML, large doc). 
- [ ] Integration tests: fix_single, fix_batch, fix_post, rollback (if exists). 
- [ ] Performance tests on large posts; set budgets (e.g., <200ms per fixer median). 
- [ ] Regression tests for ID mapping and migration scripts. 
- [ ] Lint/PHPCS, static analysis (Psalm/PHPStan at max reasonable level). 

Breakdown:
- [ ] Sub-phase 7.1: Fixer unit tests in batches; ensure malformed/large fixtures covered.
- [ ] Sub-phase 7.2: Integration tests (single/batch/post/preview/rollback) with fixtures.
- [ ] Sub-phase 7.3: Performance suite with budgets; capture baselines and deltas.
- [ ] Sub-phase 7.4: Regression snapshots for ID mapping/migration outputs and API payloads.
- [ ] Sub-phase 7.5: Static analysis/lint gating in CI; fail on warnings.

Exit criteria: CI green; coverage targets met; perf budgets respected; no known regressions.

---

## Phase 8 – Migration & Cutover (Day 24-27)
- [ ] Dry-run migration on staging with full dataset; compare scan/fix outputs. 
- [ ] Enable FixEngine flag in staging; monitor logs/metrics. 
- [ ] Production rollout: enable flag, keep legacy endpoints behind emergency toggle. 
- [ ] Observe error budgets; rollback plan documented. 
- [ ] Announce deprecation timeline for legacy Fixes system.

Exit criteria: Production on FixEngine; no elevated error rates; rollback tested.

---

## Phase 9 – Legacy Decommission (Day 28-30)
- [ ] Remove `the_content` filter and legacy autofix runtime code. 
- [ ] Delete or archive legacy Fixes/ classes after confirming parity. 
- [ ] Remove alias tables and unused options/meta. 
- [ ] Final cleanup: dead code, unused hooks, unused scripts/styles.

Exit criteria: Single system (FixEngine) remains; codebase slimmed; no orphaned data.

---

## Governance & Checkpoints
- Weekly checkpoint: progress vs. phases, blocker review. 
- Definition of Done per phase includes: code + tests + docs + monitoring. 
- Observability: log errors, counts, execution time; add dashboard for top fixers and failures. 
- Security: nonces, caps, sanitization, escaping everywhere; validate IDs strictly. 
- Rollback: each migration guarded by backup and reversible scripts. 

---

## Deliverables by Role (AI Agent Execution)
- Code changes (PHP/JS) with SOLID adherence and interfaces enforced. 
- Migration scripts (PHP/CLI) with dry-run and report. 
- Tests (unit/integration/perf) wired into CI. 
- Telemetry hooks (logging, metrics). 
- Deployment toggles and rollback scripts. 
- Documentation updates (developer notes, ops runbooks, deprecation notice).

---

## Success Criteria
- One canonical autofix architecture (FixEngine) live. 
- 100% fixer parity with legacy; zero missing fixer coverage. 
- Canonical IDs everywhere; aliases removed. 
- No runtime content filtering; fix-and-save model only. 
- Validated class existence, caching in place, and measurable perf gains. 
- Error handling structured; no silent failures; logs clean. 
- Tests green; perf budgets met; WCAG conformance preserved. 
- Legacy system decommissioned without regressions.
