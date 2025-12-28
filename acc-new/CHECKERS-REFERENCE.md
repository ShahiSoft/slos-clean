# Accessibility Checkers - Quick Reference

## Current Checkers Status (68 Total)

### ✅ Working Well (Minor Improvements)
| Checker | WCAG | Severity | Notes |
|---------|------|----------|-------|
| MissingAltTextCheck | 1.1.1 | Critical | Works correctly |
| EmptyAltTextCheck | 1.1.1 | Warning | Works correctly |
| MissingH1Check | 1.3.1 | Serious | Works correctly |
| SkippedHeadingLevelCheck | 1.3.1 | Warning | Works correctly |
| EmptyLinkCheck | 2.4.4 | Critical | Works correctly |
| EmptyHeadingCheck | 1.3.1 | Warning | Works correctly |
| NewWindowLinkCheck | 2.4.4 | Warning | Works correctly |
| PositiveTabIndexCheck | 2.4.3 | Warning | Works correctly |
| ImageMapAltCheck | 1.1.1 | Critical | Works correctly |
| IframeTitleCheck | 4.1.2 | Serious | Works correctly |
| ButtonLabelCheck | 4.1.2 | Serious | Works correctly |
| TableHeaderCheck | 1.3.1 | Serious | Works correctly |
| MultipleH1Check | 1.3.1 | Warning | Works correctly |
| FieldsetLegendCheck | 1.3.1 | Warning | Works correctly |
| InputTypeCheck | 1.3.5 | Warning | Works correctly |
| PlaceholderLabelCheck | 3.3.2 | Warning | Works correctly |
| OrphanedLabelCheck | 1.3.1 | Warning | Works correctly |
| RequiredAttributeCheck | 3.3.2 | Warning | Works correctly |
| DownloadLinkCheck | 2.4.4 | Notice | Works correctly |
| ExternalLinkCheck | 2.4.4 | Notice | Works correctly |
| TableCaptionCheck | 1.3.1 | Warning | Works correctly |
| EmptyTableCellCheck | 1.3.1 | Warning | Works correctly |
| LayoutTableCheck | 1.3.1 | Warning | Works correctly |

### ⚠️ Needs Enhancement (P2)
| Checker | WCAG | Issue | Priority |
|---------|------|-------|----------|
| AltTextQualityCheck | 1.1.1 | Missing filename detection | P2 |
| DecorativeImageCheck | 1.1.1 | Basic heuristics only | P2 |
| ComplexImageCheck | 1.1.1 | Limited detection | P2 |
| SvgAccessibilityCheck | 1.1.1 | Basic checks only | P2 |
| BackgroundImageCheck | 1.1.1 | Cannot check CSS classes | P2 |
| LogoImageCheck | 1.1.1 | Heuristic only | P2 |
| HeadingVisualCheck | 1.3.1 | Cannot check CSS | P2 |
| HeadingLengthCheck | 2.4.6 | Arbitrary limits | P2 |
| HeadingUniquenessCheck | 2.4.6 | Basic check | P2 |
| HeadingNestingCheck | 1.3.1 | Works correctly | P2 |
| AutocompleteCheck | 1.3.5 | Limited field detection | P2 |
| CustomControlCheck | 4.1.2 | Heuristic | P2 |
| ErrorMessageCheck | 3.3.1 | Basic check | P2 |
| FormAriaCheck | 4.1.2 | Basic check | P2 |
| LinkDestinationCheck | 2.4.4 | Works well | P2 |
| GenericLinkTextCheck | 2.4.4 | Limited dictionary | P2 |
| ComplexContrastCheck | 1.4.3 | Too many false positives | P2 |
| FocusOrderCheck | 2.4.3 | Cannot fully verify | P2 |
| InteractiveElementCheck | 2.1.1 | Works well | P2 |
| ModalAccessibilityCheck | 4.1.2 | Works well | P2 |
| CustomWidgetKeyboardCheck | 2.1.1 | Heuristic | P2 |
| AriaRoleCheck | 4.1.2 | Missing ARIA 1.2/1.3 roles | P2 |
| AriaAttributeCheck | 4.1.2 | Incomplete required attrs | P2 |
| RedundantAriaCheck | 4.1.2 | Basic check | P2 |
| HiddenContentCheck | 4.1.2 | Works well | P2 |
| SemanticHtmlCheck | 1.3.1 | Missing patterns | P2 |
| LiveRegionCheck | 4.1.3 | Basic validation | P2 |
| AriaStateCheck | 4.1.2 | Works well | P2 |
| InvalidAriaCombinationCheck | 4.1.2 | Limited combinations | P2 |
| PageStructureCheck | 3.1.1 | Works well | P2 |
| VideoAccessibilityCheck | 1.2.2 | Missing embedded videos | P2 |
| AudioAccessibilityCheck | 1.2.1 | Basic check | P2 |
| MediaAlternativeCheck | 1.2.3 | Basic check | P2 |
| ComplexTableCheck | 1.3.1 | Works well | P2 |
| ViewportCheck | 1.4.4 | Works well | P2 |
| TouchGestureCheck | 2.5.1 | Heuristic | P2 |
| ColorRelianceCheck | 1.4.1 | Limited patterns | P2 |

### 🔴 Critical Issues (P0-P1)
| Checker | WCAG | Issue | Priority |
|---------|------|-------|----------|
| SkipLinkCheck | 2.4.1 | **BROKEN** - commented out | P0 |
| TextColorContrastCheck | 1.4.3 | Inline styles only | P0 |
| LandmarkRoleCheck | 1.3.1 | Missing HTML5 elements | P0 |
| FocusIndicatorCheck | 2.4.7 | Inline styles only | P1 |
| TouchTargetCheck | 2.5.5 | px units only | P1 |
| KeyboardTrapCheck | 2.1.2 | Limited heuristics | P1 |
| MissingFormLabelCheck | 3.3.2 | Missing validation | P1 |

---

## New Checkers Needed

### WCAG 2.1 Gaps
| Checker | WCAG | Level | Description |
|---------|------|-------|-------------|
| LanguageChangeCheck | 3.1.2 | AA | Inline language changes |
| StatusMessageCheck | 4.1.3 | AA | Live region for status |
| AnimationPauseCheck | 2.2.2 | A | Pause for animations |
| TimingControlCheck | 2.2.1 | A | Adjustable time limits |

### WCAG 2.2 New Criteria
| Checker | WCAG | Level | Description |
|---------|------|-------|-------------|
| FocusNotObscuredCheck | 2.4.11 | AA | Focus not hidden |
| FocusAppearanceCheck | 2.4.13 | AAA | Enhanced focus |
| DragOperationCheck | 2.5.7 | A | Drag alternatives |
| TargetSizeMinimumCheck | 2.5.8 | AA | 24px minimum |
| ConsistentHelpCheck | 3.2.6 | A | Help location |
| RedundantEntryCheck | 3.3.7 | A | Avoid re-entry |
| AccessibleAuthCheck | 3.3.8 | AA | CAPTCHA alternatives |

### Additional Recommended
| Checker | WCAG | Description |
|---------|------|-------------|
| FlashingContentCheck | 2.3.1 | Seizure prevention |
| ConsistentNavigationCheck | 3.2.3 | Nav consistency |
| ErrorSuggestionCheck | 3.3.3 | Error suggestions |
| ErrorPreventionCheck | 3.3.4 | Legal/financial |

---

## Files to Modify

### Priority 0 (Week 1-2)
```
includes/Modules/AccessibilityScanner/Scanner/Checkers/
├── SkipLinkCheck.php          [FIX BROKEN]
├── TextColorContrastCheck.php [MAJOR REWRITE]
└── LandmarkRoleCheck.php      [ADD HTML5]
```

### Priority 1 (Week 3-4)
```
includes/Modules/AccessibilityScanner/Scanner/Checkers/
├── FocusIndicatorCheck.php    [ENHANCE]
├── TouchTargetCheck.php       [ENHANCE]
├── KeyboardTrapCheck.php      [ENHANCE]
└── MissingFormLabelCheck.php  [ENHANCE]
```

### New Files (Week 7-10)
```
includes/Modules/AccessibilityScanner/Scanner/Checkers/
├── LanguageChangeCheck.php    [NEW]
├── AnimationPauseCheck.php    [NEW]
├── TimingControlCheck.php     [NEW]
├── StatusMessageCheck.php     [NEW]
├── ErrorIdentificationCheck.php [NEW]
├── FocusNotObscuredCheck.php  [NEW]
├── DragOperationCheck.php     [NEW]
├── TargetSizeMinimumCheck.php [NEW]
└── AccessibleAuthCheck.php    [NEW]
```

### Architecture (Week 11-12)
```
includes/Modules/AccessibilityScanner/Scanner/
├── CheckInterface.php         [ENHANCE]
├── AbstractCheck.php          [ENHANCE]
└── ScannerEngine.php          [ENHANCE]
```

---

## Color Contrast Implementation Notes

### Current Limitations
1. Only inline `style` attribute
2. Only `#hex` and `rgb()` formats
3. No large text detection
4. No AAA option

### Required Color Support
```php
// Formats to support:
#RGB           // 3-digit hex
#RRGGBB        // 6-digit hex
#RGBA          // 4-digit hex with alpha
#RRGGBBAA      // 8-digit hex with alpha
rgb(r, g, b)   // RGB function
rgba(r,g,b,a)  // RGBA function
hsl(h, s%, l%) // HSL function
hsla(h,s%,l%,a)// HSLA function
named colors   // 140+ CSS color names

// Cannot support (external CSS):
CSS classes
CSS variables (--custom-color)
calc() expressions
currentColor
inherit/initial
```

### Large Text Rules (WCAG)
- **Normal text**: 4.5:1 ratio minimum
- **Large text**: 3:1 ratio minimum
  - 18pt (24px) or larger
  - 14pt (18.67px) bold or larger

---

## Testing Checklist

### For Each Modified Checker
- [ ] Unit test with positive case (should find issue)
- [ ] Unit test with negative case (should pass)
- [ ] Unit test with edge cases
- [ ] Performance test (< 50ms per check)
- [ ] No PHP warnings/errors
- [ ] Correct WCAG criteria reference

### Integration Testing
- [ ] Full site scan completes without error
- [ ] Results match manual audit (80%+)
- [ ] Compare with axe-core results
- [ ] Compare with WAVE results

### Browser/Environment
- [ ] PHP 7.4 compatible
- [ ] PHP 8.0+ compatible
- [ ] WordPress 5.9+ compatible
- [ ] Works with block editor content
- [ ] Works with classic editor content
