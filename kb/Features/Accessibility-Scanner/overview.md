# Accessibility Scanner Overview

## What is Accessibility Scanner?

The Accessibility Scanner is an intelligent system that detects and automatically repairs WCAG 2.1 AA accessibility issues on your website. With 96 automated fixers, it can repair 91% of common accessibility problems.

## Key Statistics

- **96 Total Fixers** - Comprehensive WCAG coverage
- **91% Coverage** - Can auto-fix most issues
- **Real-Time Scanning** - On-demand page scanning
- **Automatic Repair** - Fixes issues without manual work
- **Zero Dependencies** - No external services required

## How It Works

### Scanning Process

1. **Page Analysis** - Scanner examines HTML, CSS, and JavaScript
2. **Issue Detection** - Identifies WCAG 2.1 AA violations
3. **Fixer Matching** - Matches issues to appropriate auto-fixers
4. **Automatic Repair** - Applies fixes without manual intervention
5. **Validation** - Verifies fixes don't break functionality
6. **Reporting** - Generates detailed accessibility reports

### Fix Categories

The scanner addresses 10 major accessibility categories:

#### 1. Focus Management (8 fixers)
Ensures keyboard navigation and focus indicators work properly

#### 2. Vision & Color (12 fixers)
Improves color contrast, removes color-only information, adds visual cues

#### 3. Touch & Motor (8 fixers)
Makes interactive elements accessible to users with motor disabilities

#### 4. Language & Structure (12 fixers)
Adds semantic structure, proper headings, and language identification

#### 5. ARIA & Roles (14 fixers)
Implements proper ARIA attributes and semantic roles

#### 6. Forms & Input (10 fixers)
Makes form elements accessible with proper labels and instructions

#### 7. Media & Alternative Text (10 fixers)
Adds captions, transcripts, and alternative text for media

#### 8. Timing & Animation (6 fixers)
Controls animations and provides timing alternatives

#### 9. Navigation & Structure (4 fixers)
Improves site navigation and page structure

#### 10. Error Prevention (2 fixers)
Prevents common user errors and provides clear feedback

## Module Status

Access via: **SLOS** → **Accessibility Scanner**

Shows:
- ✓ Total pages scanned
- ✓ Issues found and fixed
- ✓ Accessibility score improvement
- ✓ Last scan date
- ✓ Active fixers count

## WCAG 2.1 AA Compliance

### Supported Guidelines

**Perceivable**
- Text Alternatives (1.1)
- Time-based Media (1.2)
- Adaptable (1.3)
- Distinguishable (1.4)

**Operable**
- Keyboard Accessible (2.1)
- Enough Time (2.2)
- Seizures and Physical Reactions (2.3)
- Navigable (2.4)

**Understandable**
- Readable (3.1)
- Predictable (3.2)
- Input Assistance (3.3)

**Robust**
- Compatible (4.1)

## Performance Impact

### Resource Usage

**Scanning Performance:**
- Page scan: <2 seconds
- Full site scan: <5 minutes (100 pages)
- Memory usage: <50MB
- CPU impact: Minimal

**Fixer Performance:**
- DOM manipulation: Efficient
- CSS injection: Lightweight
- JavaScript addition: Minimal
- Page load impact: <100ms

### Optimization Features

**Smart Scanning:**
- Incremental scanning
- Change detection
- Prioritized fixes
- Background processing

**Caching:**
- Scan result caching
- Fixer rule caching
- Performance monitoring

## Integration Features

### WordPress Integration

**Theme Compatibility:**
- Works with all themes
- No theme modifications needed
- CSS isolation
- JavaScript namespacing

**Plugin Compatibility:**
- Compatible with page builders
- Works with caching plugins
- SEO plugin integration
- Performance plugin support

### External Tools

**Browser Extensions:**
- WAVE accessibility toolbar
- axe DevTools integration
- Lighthouse accessibility audit

**Testing Tools:**
- Screen reader testing
- Keyboard navigation testing
- Color contrast analyzers

## Security & Privacy

### Data Handling

**No External Data:**
- All scanning local
- No data sent externally
- Privacy-preserving
- GDPR compliant

**Secure Processing:**
- Input sanitization
- XSS prevention
- Content Security Policy
- Safe DOM manipulation

## Reporting & Analytics

### Scan Reports

**Comprehensive Reports:**
- Issue breakdown by category
- Before/after comparison
- Fix success rates
- Performance metrics

**Export Options:**
- PDF accessibility reports
- CSV issue lists
- JSON API data
- HTML dashboards

### Progress Tracking

**Score Improvement:**
- WCAG compliance scoring
- Trend analysis
- Goal tracking
- Benchmark comparison

## Related Features

- **Consent Management** - Ensure accessible consent banners
- **Legal Documents** - Generate accessibility statements
- **Analytics Integration** - Track accessibility improvements

## Next Steps

1. Enable Accessibility Scanner module
2. Run initial site scan
3. Review auto-fixer results
4. Configure scanning schedule
5. Monitor accessibility improvements

## Support

For detailed guides, see:
- [Auto-Fixers by Category](auto-fixers-by-category.md)
- [Scanning Process](scanning-process.md)
- [Configuration Guide](configuration.md)
- [Run Accessibility Scans](../../../How-tos/03-run-accessibility-scan.md)