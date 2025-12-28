# Auto-Fix Implementation Checklist

Use this checklist to track implementation of the audit recommendations.

---

## Phase 1: Critical Enhancements (Week 1-2)

### Focus Indicator Fixes
- [ ] Update `FocusIndicatorFixer` with CSS injection
- [ ] Remove `outline:none` from inline styles
- [ ] Add `.slos-focus-visible` class to interactive elements
- [ ] Inject `:focus-visible` CSS rules
- [ ] Test with keyboard navigation

### Touch Target Fixes
- [ ] Update `TouchTargetFixer` with min-size enforcement
- [ ] Add wrapper styling for checkboxes/radios
- [ ] Fix icon-only buttons
- [ ] Inject touch target CSS
- [ ] Test on mobile devices

### Contrast Fixes
- [ ] Implement actual color adjustment in `TextColorContrastFixer`
- [ ] Add luminance calculation functions
- [ ] Handle both hex and rgb colors
- [ ] Test with various color combinations

### Viewport Fixes
- [ ] Update `ViewportFixer` with meta tag modification
- [ ] Remove `user-scalable=no`
- [ ] Remove restrictive `maximum-scale`
- [ ] Test zoom functionality on mobile

---

## Phase 2: New P3 Checkers (Week 3-4)

### LanguageChangeFixer
- [ ] Create `Fixers/LanguageChangeFixer.php`
- [ ] Implement language detection patterns
- [ ] Add `lang` attribute to foreign text
- [ ] Register in `FixerRegistry.php`
- [ ] Test with multilingual content

### StatusMessageFixer
- [ ] Create `Fixers/StatusMessageFixer.php`
- [ ] Add `role="status"` to status elements
- [ ] Add `aria-live` attributes
- [ ] Fix form validation messages
- [ ] Fix loading indicators
- [ ] Register in `FixerRegistry.php`
- [ ] Test with screen reader

### AnimationPauseFixer
- [ ] Create `Fixers/AnimationPauseFixer.php`
- [ ] Add pause controls to animated GIFs
- [ ] Fix CSS animations
- [ ] Fix carousels/sliders
- [ ] Replace marquee elements
- [ ] Register in `FixerRegistry.php`
- [ ] Add JavaScript support
- [ ] Test pause functionality

### TimingControlFixer
- [ ] Create `Fixers/TimingControlFixer.php`
- [ ] Fix meta refresh warnings
- [ ] Add timer controls
- [ ] Fix session warnings
- [ ] Fix auto-dismiss notifications
- [ ] Register in `FixerRegistry.php`
- [ ] Test time extension

### ErrorIdentificationFixer
- [ ] Create `Fixers/ErrorIdentificationFixer.php`
- [ ] Fix error-input associations
- [ ] Add `aria-invalid` states
- [ ] Fix error summaries
- [ ] Add error icons (not just color)
- [ ] Register in `FixerRegistry.php`
- [ ] Test with form submissions

---

## Phase 3: Keyboard & Interaction (Week 5-6)

### Keyboard Trap Fixes
- [ ] Update `KeyboardTrapFixer` with escape handlers
- [ ] Add close buttons to modals
- [ ] Add skip links for iframes
- [ ] Inject keyboard escape script
- [ ] Test focus trapping

### Color Reliance Fixes
- [ ] Update `ColorRelianceFixer` with indicators
- [ ] Add icons to status colors
- [ ] Fix required field indicators
- [ ] Ensure links have underlines
- [ ] Test with grayscale filter

### ARIA State Fixes
- [ ] Update `AriaStateFixer` with default states
- [ ] Add `aria-pressed` to toggle buttons
- [ ] Add `aria-expanded` to expandables
- [ ] Test state changes

### Page Structure Fixes
- [ ] Update `PageStructureFixer` with landmark injection
- [ ] Add `<main>` if missing
- [ ] Add skip links
- [ ] Add landmark roles
- [ ] Test navigation

---

## Phase 4: Quality Improvements (Week 7-8)

### Alt Text Quality
- [ ] Enhance `AltTextQualityFixer`
- [ ] Use filename analysis
- [ ] Check for redundant text
- [ ] Suggest improvements
- [ ] Test with various images

### Generic Link Text
- [ ] Enhance `GenericLinkTextFixer`
- [ ] Use URL analysis
- [ ] Use surrounding context
- [ ] Test with "click here" links

### Documentation
- [ ] Update fixer documentation
- [ ] Add JSDoc comments
- [ ] Create usage examples
- [ ] Update CHANGELOG

### Testing
- [ ] Unit tests for all new fixers
- [ ] Integration tests
- [ ] Screen reader testing
- [ ] Mobile testing
- [ ] Performance benchmarks

---

## Files to Create

```
includes/Modules/AccessibilityScanner/Fixes/Fixers/
├── LanguageChangeFixer.php      ← NEW
├── StatusMessageFixer.php       ← NEW
├── AnimationPauseFixer.php      ← NEW
├── TimingControlFixer.php       ← NEW
├── ErrorIdentificationFixer.php ← NEW
└── [existing files updated]
```

## Files to Modify

```
includes/Modules/AccessibilityScanner/Fixes/
├── FixerRegistry.php            ← Add 5 new registrations
├── InteractivityFixers.php      ← Enhance 4 fixers
├── AriaAndSemanticFixers.php    ← Enhance 2 fixers
├── LinkAndImageFixers.php       ← Enhance 1 fixer
└── AccessibilityFixer.php       ← Add new JS injections

assets/js/
├── accessibility-fixes.js       ← Add animation/timer controls
└── accessibility-scanner-frontend.js ← Update

assets/css/
└── accessibility-scanner/
    └── a11y-fixes.css           ← Add focus/touch/contrast styles
```

---

## Testing Checklist

### Automated Tests
- [ ] PHPUnit tests for each fixer
- [ ] Test `fix()` returns correct structure
- [ ] Test DOM manipulation correctness
- [ ] Test no false positives

### Manual Tests
- [ ] Keyboard navigation
- [ ] Screen reader (NVDA/JAWS/VoiceOver)
- [ ] Mobile touch targets
- [ ] Zoom functionality (up to 400%)
- [ ] High contrast mode
- [ ] Reduced motion preference

### Browser Tests
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile Safari
- [ ] Chrome Android

---

## Success Metrics

| Metric | Before | Target | Actual |
|--------|--------|--------|--------|
| Implemented Fixers | 35/56 | 51/56 | |
| Fixers returning 0 | 12 | 3 | |
| Auto-fix coverage | ~45% | 75% | |
| False fix rate | Unknown | <5% | |
| Test coverage | Unknown | 90% | |

---

## Notes

- All fixes should be reversible
- Log all fixes made for audit trail
- Consider performance impact
- Test with real WordPress content
- Follow WordPress coding standards

---

*Last updated: December 28, 2025*
