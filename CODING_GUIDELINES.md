# Coding Guidelines for Shahi LegalFlowSuite

## WordPress Standards

- Follow WordPress Coding Standards (WPCS).
- Use `esc_html()`, `esc_js()`, `absint()` for output escaping.
- Text domains: Use `'shahi-legalflowsuite'` as string literals.
- No direct DB queries; use `$wpdb` properly.
- Inline comments must end with punctuation.
- Use `gmdate()` instead of `date()` for timestamps.

## Codecanyon Guidelines

- GPL-3.0-or-later license.
- No hardcoded external URLs or links.
- No obfuscated code.
- Proper attribution and licensing for assets.
- Demo data should be optional and removable.

## General Rules

- Use tabs for indentation in PHP.
- Unix line endings (LF).
- No console.log in production JS.
- Run linters before committing.

## AI Agent Guidelines

- Generate code compliant with above standards.
- Reference this file for consistency.
- Use proper escaping and security practices.
