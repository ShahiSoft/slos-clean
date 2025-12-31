# Accessibility Scanner & Auto-Fixers

## What is Accessibility Scanner?

The Accessibility Scanner is an intelligent system that detects and automatically repairs WCAG 2.1 AA accessibility issues on your website. With 96 automated fixers, it can repair 91% of common accessibility problems.

## Key Statistics

- **96 Total Fixers** - Comprehensive WCAG coverage
- **91% Coverage** - Can auto-fix most issues
- **Real-Time Scanning** - On-demand page scanning
- **Automatic Repair** - Fixes issues without manual work
- **Zero Dependencies** - No external services required

## 96 Auto-Fixers

### Focus Management (8 fixers)
- Focus Indicator Fixer - Adds visible focus indicators
- Focus Trap Fixer - Manages focus in modals
- Focus Restoration - Returns focus after actions
- Focus Order Fixer - Corrects tab order
- Focus Visible Fixer - Adds focus-visible support
- Keyboard Navigation - Ensures keyboard access
- Keyboard Trap Escape - Escape key handling
- Focus Management Events - Custom focus handling

### Vision & Color (12 fixers)
- Color Contrast Fixer - Improves text contrast
- Color Reliance Fixer - Adds icons to color-only info
- Link Underline Fixer - Underlines links
- Text Spacing Fixer - Adds text spacing
- Visual Focus Fixer - Highlights focused elements
- Color Coding Fixer - Removes color-only coding
- Blur Effect Fixer - Removes problematic blur
- Icon Visibility Fixer - Ensures icons are visible
- Animation Pause Fixer - Adds animation controls
- Flashing Content Fixer - Fixes rapid flashing
- Opacity Fixer - Improves opacity levels
- Background Contrast - Improves background contrast

### Touch & Motor (8 fixers)
- Touch Target Fixer - Resizes small clickables (44x44px)
- Button Size Fixer - Ensures large enough buttons
- Click Area Fixer - Expands clickable areas
- Spacing Fixer - Adds spacing between controls
- Motion Activation - Allows non-motion alternatives
- Gesture Alternative - Provides keyboard alternatives
- Pointer Target - Ensures accessible targets
- Draggable Alternative - Provides non-drag options

### Language & Structure (12 fixers)
- Language Change Fixer - Detects foreign language text
- Page Structure Fixer - Adds semantic landmarks
- Heading Structure Fixer - Corrects heading hierarchy
- List Structure Fixer - Fixes improper lists
- Table Header Fixer - Adds table headers
- Table Structure Fixer - Corrects table markup
- Landmark Fixer - Adds main/nav/aside landmarks
- Skip Link Fixer - Adds skip navigation
- Frame Title Fixer - Titles iframes
- Form Label Fixer - Associates labels with inputs
- Section Heading Fixer - Adds headings to sections
- Content Structure - Organizes content hierarchy

### ARIA & Roles (14 fixers)
- Aria State Fixer - Adds aria-pressed, aria-selected
- Aria Live Fixer - Adds aria-live regions
- Aria Label Fixer - Adds aria-labels
- Role Fixer - Applies correct ARIA roles
- Aria Expanded - Adds aria-expanded attributes
- Aria Hidden - Manages aria-hidden
- Aria Invalid - Adds aria-invalid for errors
- Aria Disabled - Marks disabled elements
- Aria Required - Marks required fields
- Status Message Fixer - Adds status messages
- Error Identification - Associates errors
- Alert Role - Adds alert semantics
- Description Fixer - Adds aria-describedby
- Tooltip Fixer - Adds accessible tooltips

### Forms & Input (10 fixers)
- Form Label Fixer - Associates labels
- Placeholder Fixer - Replaces placeholder-only labels
- Input Type Fixer - Sets correct input types
- Error Message Fixer - Associates error messages
- Required Field Fixer - Marks required fields
- Form Instructions - Adds form instructions
- Input Help Text - Associates help text
- Form Grouping - Groups related fields
- Form Submit Button - Labels submit buttons
- Form Reset Prevention - Prevents accidental resets

### Media & Alternative Text (10 fixers)
- Alt Text Fixer - Adds alt text to images
- Video Caption Fixer - Adds captions to videos
- Audio Transcript - Provides transcripts
- Image Description - Adds extended descriptions
- Decorative Image - Hides decorative images
- SVG Alternative - Adds SVG descriptions
- Canvas Alternative - Provides canvas fallbacks
- Video Audio Track - Adds audio descriptions
- Media Controls - Ensures media controls accessible
- Captions & Subtitles - Adds captions

### Timing & Animation (8 fixers)
- Animation Pause Fixer - Adds pause controls
- Carousel Control - Adds carousel pause
- GIF Pause Fixer - Pauses animated GIFs
- Timing Control Fixer - Adds timing extensions
- Meta Refresh Fixer - Removes auto-refresh
- Timeout Extension - Extends session timeouts
- Blink Fixer - Removes blinking content
- Scroll Animation - Pauses on-scroll animations

### Viewport & Mobile (6 fixers)
- Viewport Fixer - Sets viewport meta tag
- Zoom Fixer - Allows user zoom
- Text Scaling - Preserves text scaling
- Rotation Lock - Allows both orientations
- Touch Feedback - Adds touch feedback
- Mobile Navigation - Accessible mobile menu

### Target Size & Spacing (8 fixers)
- Touch Target Fixer - 44x44px minimum
- Pointer Target - Large enough for cursor
- Spacing Fixer - Spacing between targets
- Click Area Expansion - Expands clickable areas
- Button Padding - Adds button padding
- Link Padding - Adds link padding
- Target Boundary - Prevents overlap
- Target Margin - Adds safe margins

## Scanning Process

### 1. Initiate Scan
- Click **SLOS** → **Accessibility Scanner** → **Scan Page**
- Or **Scan All Pages** for site-wide scanning
- Displays real-time progress popup

### 2. Detection Phase
- Scans HTML for accessibility issues
- Detects WCAG 2.1 AA violations
- Categorizes by severity
- Reports coverage percentage

### 3. Auto-Fix Phase
- Runs applicable fixers
- Real-time status updates
- Tracks fixer results
- Shows fix count

### 4. Report Generation
- Creates accessibility report
- Shows before/after metrics
- Lists manual fixes needed
- Provides remediation guidance

## Auto-Fix Progress Popup

During scanning:
- Real-time fixer execution
- Progress bar showing completion
- List of active fixers
- Success/failure indicators
- Estimated time remaining
- Cancel option

## Dashboard Integration

**Accessibility Dashboard** shows:
- Overall accessibility score
- WCAG compliance level
- Issue breakdown by category
- Auto-fixable vs manual issues
- Scan history timeline
- Recommended fixes

## Fix History

Every auto-fix operation is tracked:
- Date and time
- Pages affected
- Fixers applied
- Before/after metrics
- Fixer success rates
- User who ran scan

## Mobile Accessibility

Ensures:
- Touch targets ≥44x44px
- Text scalable up to 200%
- Keyboard navigation works
- No scroll/rotation lock
- Orientation independent
- Mobile menu accessible

## PDF Accessibility

Fixers also improve:
- PDF text extraction
- PDF navigation
- PDF form accessibility
- PDF heading structure
- PDF link accessibility

## Performance Impact

Running auto-fixers:
- CPU: 5-10% during scan
- Memory: 10-20MB
- Database: ~500KB per scan record
- Execution: 30-60 seconds for full scan

## Limitations

Manual fixes still needed for:
- Content quality issues
- Image descriptions
- Video transcript accuracy
- Complex form logic
- Custom component accessibility

## Best Practices

1. **Regular Scanning** - Scan weekly
2. **Post-Update Scans** - Scan after content changes
3. **Test After Fixes** - Verify accessibility improvements
4. **Manual Review** - Review non-fixable issues
5. **Team Training** - Educate on accessibility

## Reports

Generate PDF reports showing:
- Overall accessibility score
- WCAG 2.1 AA compliance level
- Issues found and fixed
- Page-by-page breakdown
- Recommendations for improvement
- Before/after metrics

## Next Steps

1. Enable Accessibility Scanner
2. Run initial site-wide scan
3. Review auto-fix results
4. Configure scan schedule
5. Train team on scanner

## Support

For detailed guides:
- [Running Accessibility Scans](../How-tos/04-run-accessibility-scan.md)
- [Understanding Scanner Reports](../How-tos/05-interpret-reports.md)
- [Manual Accessibility Fixes](../How-tos/06-manual-fixes.md)
