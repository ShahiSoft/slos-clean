# SLOS Component Library

**Version:** 3.2.0 (V3.2 Enhanced)  
**CSS File:** `assets/css/slos-components.css` (38.41 KB, 1571 lines)  
**Minified:** `assets/css/slos-components.min.css` (24.55 KB, 36.08% reduction)  
**Dependencies:** `slos-design-system.css` (MUST be loaded first)

**V3.2 Additions:**
- Enhanced stat cards with gradient accent bars
- Progress ring SVG components
- Chart components (bar, sparkline) - see `slos-charts.css`
- Gradient button variants
- Glow border utilities
- Live indicator with pulse animation
- Shimmer loading skeletons

---

## Overview

Complete reusable component system with ZERO glassmorphism effects. All components use solid colors and olive accent theme from the design system.

**Key Features:**
- ✅ **Solid backgrounds** - no backdrop-filter or blur effects
- ✅ **Olive accent theme** - professional corporate design
- ✅ **Design system integration** - uses CSS variables
- ✅ **Hover/focus states** - proper interactive feedback
- ✅ **WCAG 2.1 AA compliant** - accessible by default
- ✅ **Responsive** - mobile-friendly components

---

## Cards

Primary building block for all admin interfaces. Cards use solid backgrounds with proper shadows.

### Base Card

```html
<div class="slos-card">
    <div class="slos-card-header">
        <h3 class="slos-card-title">Card Title</h3>
        <p class="slos-card-subtitle">Optional subtitle</p>
    </div>
    <div class="slos-card-body">
        Card content goes here.
    </div>
    <div class="slos-card-footer">
        <button class="slos-btn-secondary">Cancel</button>
        <button class="slos-btn-primary">Save</button>
    </div>
</div>
```

**CSS Classes:**
- `.slos-card` - Base card with `--slos-bg-secondary` background
- `.slos-card-header` - Header with bottom border
- `.slos-card-title` - Card heading (20px, semibold)
- `.slos-card-subtitle` - Secondary text (14px, tertiary color)
- `.slos-card-body` - Main content area
- `.slos-card-footer` - Footer with top border and action buttons

**Features:**
- Background: `var(--slos-bg-secondary)` (#242b24)
- Border: 1px solid olive (20% opacity)
- Border radius: 12px (`--slos-radius-lg`)
- Shadow: `--slos-shadow-md`
- Hover: Elevates with `--slos-shadow-lg` and translateY(-2px)

### Card Variants

**Elevated Card:**
```html
<div class="slos-card slos-card-elevated">
    <!-- More prominent elevation -->
</div>
```
- Background: `var(--slos-bg-tertiary)` (#2d342d - lighter)
- Shadow: `--slos-shadow-lg` (default), `--slos-shadow-xl` (hover)
- Hover lift: translateY(-3px)

**Accent Card:**
```html
<div class="slos-card slos-card-accent">
    <!-- Highlighted with olive border -->
</div>
```
- Border: 2px solid olive primary
- Glow: 4px olive shadow (rgba(107, 142, 78, 0.1))

**Flat Card:**
```html
<div class="slos-card slos-card-flat">
    <!-- No shadow, minimal elevation -->
</div>
```
- Shadow: none
- Hover: Border color change only (no elevation)

### V3.2 ENHANCEMENT: Stat Cards with Gradient Accent (Phase 4.1)

Special card with top gradient accent bar for dashboard statistics. Now with animated counters and enhanced visual hierarchy.

**Basic Stat Card:**
```html
<div class="slos-stat-card">
    <div class="slos-stat-value" data-target="1234">0</div>
    <div class="slos-stat-label">Total Users</div>
    <div class="slos-stat-change slos-stat-change-positive">+12%</div>
</div>
```

**Features:**
- Top accent bar: 4px gradient (`--slos-gradient-stat`)
- Animated counter: Counts from 0 to target (requires `slos-animations.js`)
- Enhanced padding: 24px (increased from 16px)
- Larger value font: `--slos-text-metric-md` (48px)
- Extends `.slos-card` base styles

**Stat Change Variants:**
```html
<!-- Positive change (green) -->
<div class="slos-stat-change slos-stat-change-positive">+12% ↑</div>

<!-- Negative change (red) -->
<div class="slos-stat-change slos-stat-change-negative">-5% ↓</div>

<!-- Neutral change (gray) -->
<div class="slos-stat-change">No change</div>
```

**Stat Card with Icon:**
```html
<div class="slos-stat-card">
    <div class="slos-stat-header">
        <div class="slos-stat-icon">
            <svg><!-- icon --></svg>
        </div>
        <div class="slos-stat-value" data-target="1234">0</div>
    </div>
    <div class="slos-stat-label">Total Users</div>
    <div class="slos-stat-change slos-stat-change-positive">+12%</div>
</div>
```

**JavaScript Integration:**
```javascript
// Auto-initializes on page load
// Or manually trigger:
const statValue = document.querySelector('.slos-stat-value');
const counter = new SLOSCounter(statValue, { duration: 2000 });
counter.start();
```

### V3.2 ADDITION: Progress Ring Component (Phase 4.2)

Circular SVG progress indicator with gradient stroke.

```html
<div class="slos-progress-ring" data-progress="75">
    <svg viewBox="0 0 120 120">
        <!-- Background circle -->
        <circle class="slos-progress-ring-bg"
                cx="60" cy="60" r="52"
                fill="none" stroke-width="8"></circle>
        <!-- Progress circle with gradient -->
        <circle class="slos-progress-ring-fill"
                cx="60" cy="60" r="52"
                fill="none" stroke-width="8"
                stroke-dasharray="326.73"
                stroke-dashoffset="81.68"></circle>
    </svg>
    <div class="slos-progress-ring-text">
        <span class="slos-progress-ring-value">75</span>
        <span class="slos-progress-ring-unit">%</span>
    </div>
</div>
```

**Features:**
- Size: 120x120px default (customizable)
- Stroke: 8px gradient line
- Background: rgba(16, 217, 160, 0.1) - subtle mint
- Progress: Mint gradient stroke
- Text overlay: Centered percentage
- Animated: Stroke animates from 0 to target

**Size Variants:**
```html
<div class="slos-progress-ring slos-progress-ring-sm"><!-- 80px --></div>
<div class="slos-progress-ring"><!-- Default: 120px --></div>
<div class="slos-progress-ring slos-progress-ring-lg"><!-- 160px --></div>
```

**Color Variants:**
```html
<!-- Success (green) -->
<div class="slos-progress-ring slos-progress-ring-success" data-progress="90">...</div>

<!-- Warning (orange) -->
<div class="slos-progress-ring slos-progress-ring-warning" data-progress="60">...</div>

<!-- Error (red) -->
<div class="slos-progress-ring slos-progress-ring-error" data-progress="25">...</div>
```

**Usage with Counter:**
```javascript
// Animate ring and counter simultaneously
const ring = document.querySelector('.slos-progress-ring');
const value = parseInt(ring.dataset.progress);
const circumference = 326.73;
const offset = circumference - (value / 100) * circumference;

ring.querySelector('.slos-progress-ring-fill').style.strokeDashoffset = offset;
```

---

## Buttons

Complete button system with dual-accent gradient theme (V3.2 Enhanced).

### Button Types

**V3.2 ENHANCEMENT: Primary Button (Mint Gradient):**
```html
<button class="slos-btn-primary">Primary Action</button>
```
- Background: Mint gradient (`--slos-gradient-primary`)
- Shadow: `--slos-shadow-glow-md` (shadow + mint glow)
- Hover: translateY(-2px) elevation + enhanced glow
- Active: translateY(0) press effect
- Text: White (#ffffff) for maximum contrast

**Secondary Button (Solid Tertiary):**
```html
<button class="slos-btn-secondary">Secondary Action</button>
```
- Background: `var(--slos-bg-tertiary)`
- Border: 1px solid default border
- Hover: Border changes to mint, text changes to mint

**Outline Button:**
```html
<button class="slos-btn-outline">Outline Action</button>
```
- Background: Transparent
- Border: 2px solid olive primary
- Hover: Fills with olive background

**Ghost Button:**
```html
<button class="slos-btn-ghost">Ghost Action</button>
```
- Background: Transparent
- No border
- Hover: rgba(107, 142, 78, 0.1) background

**Danger Button:**
```html
<button class="slos-btn-danger">Delete</button>
```
- Background: `var(--slos-error)` (#ef4444)
- Hover: Darker red (#dc2626)
- Use for destructive actions

### Button Sizes

```html
<button class="slos-btn-primary slos-btn-sm">Small</button>
<button class="slos-btn-primary">Default</button>
<button class="slos-btn-primary slos-btn-lg">Large</button>
```

- `.slos-btn-sm`: 8px/16px padding, 12px font
- Default: 12px/24px padding, 14px font
- `.slos-btn-lg`: 16px/32px padding, 16px font

### Button States

**Disabled:**
```html
<button class="slos-btn-primary" disabled>Disabled</button>
```
- Opacity: 0.5
- Cursor: not-allowed
- No hover effects

### Button with Icon

```html
<button class="slos-btn-primary slos-btn-icon">
    <svg class="slos-btn-icon-left"><!-- icon --></svg>
    <span>Button Text</span>
</button>
```

- `.slos-btn-icon`: Flexbox with align-items center
- `.slos-btn-icon-left`: 8px right margin
- `.slos-btn-icon-right`: 8px left margin

### Button Group

```html
<div class="slos-btn-group">
    <button class="slos-btn-secondary">Left</button>
    <button class="slos-btn-secondary">Middle</button>
    <button class="slos-btn-secondary">Right</button>
</div>
```

- Horizontal flexbox
- Buttons share borders (middle buttons lose left/right radius)
- Gap: 0 (buttons touch)

---

## Modals

Corporate modal design with olive accents. Solid overlays with NO backdrop-filter.

### Basic Modal

```html
<div class="slos-modal-overlay">
    <div class="slos-modal">
        <div class="slos-modal-header">
            <h3 class="slos-modal-title">Modal Title</h3>
            <button class="slos-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="slos-modal-body">
            Modal content goes here.
        </div>
        <div class="slos-modal-footer">
            <button class="slos-btn-secondary">Cancel</button>
            <button class="slos-btn-primary">Confirm</button>
        </div>
    </div>
</div>
```

**CSS Classes:**
- `.slos-modal-overlay` - Full-screen overlay (rgba(0, 0, 0, 0.85))
- `.slos-modal` - Modal container (max-width: 600px)
- `.slos-modal-header` - Header with title and close button
- `.slos-modal-title` - Modal heading (20px, semibold)
- `.slos-modal-close` - Close button (×)
- `.slos-modal-body` - Scrollable content area
- `.slos-modal-footer` - Footer with action buttons

**Show/Hide:**
```javascript
// Add 'slos-show' class to display modal
document.querySelector('.slos-modal-overlay').classList.add('slos-show');
```

- Add `.slos-show` class to overlay for fade-in animation
- Opacity: 0 → 1 (300ms ease-out)
- Modal: Scale 0.9 → 1 + fade-in

### Modal Sizes

```html
<div class="slos-modal slos-modal-sm"><!-- 400px --></div>
<div class="slos-modal"><!-- Default: 600px --></div>
<div class="slos-modal slos-modal-lg"><!-- 800px --></div>
<div class="slos-modal slos-modal-xl"><!-- 1000px --></div>
<div class="slos-modal slos-modal-full"><!-- 95vw --></div>
```

**Features:**
- Modal header/footer: `var(--slos-bg-tertiary)` background
- Modal body: `var(--slos-bg-secondary)` background
- Border radius: 12px
- Shadow: `--slos-shadow-2xl` (maximum elevation)
- Z-index: 1050 (above backdrop at 1040)

---

## Progress Bars

Animated progress indicators with shimmer effect (NO BLUR).

### Basic Progress Bar

```html
<div class="slos-progress-wrapper">
    <div class="slos-progress-text">
        <span class="slos-progress-label">Uploading...</span>
        <span class="slos-progress-percent">65%</span>
    </div>
    <div class="slos-progress">
        <div class="slos-progress-bar" style="width: 65%;"></div>
    </div>
</div>
```

**CSS Classes:**
- `.slos-progress-wrapper` - Container for label + progress
- `.slos-progress-text` - Flexbox row for label and percentage
- `.slos-progress-label` - Text label (primary color, medium weight)
- `.slos-progress-percent` - Percentage (olive accent, semibold)
- `.slos-progress` - Progress track (10px height)
- `.slos-progress-bar` - Animated bar with gradient

**Features:**
- Track background: rgba(107, 142, 78, 0.15)
- Bar gradient: Olive primary → light
- Shimmer effect: White gradient sliding across (2s animation)
- Glow: 12px olive shadow
- Transition: Width changes animate over 400ms

### Progress Variants

**Success Progress:**
```html
<div class="slos-progress slos-progress-success">
    <div class="slos-progress-bar" style="width: 100%;"></div>
</div>
```
- Color: Green (#10b981 → #34d399)
- Glow: Green shadow

**Warning Progress:**
```html
<div class="slos-progress slos-progress-warning">
    <div class="slos-progress-bar" style="width: 75%;"></div>
</div>
```
- Color: Orange (#f59e0b → #fbbf24)
- Glow: Orange shadow

**Error Progress:**
```html
<div class="slos-progress slos-progress-error">
    <div class="slos-progress-bar" style="width: 25%;"></div>
</div>
```
- Color: Red (#ef4444 → #f87171)
- Glow: Red shadow

### Progress Sizes

```html
<div class="slos-progress slos-progress-sm"><!-- 6px height --></div>
<div class="slos-progress"><!-- Default: 10px --></div>
<div class="slos-progress slos-progress-lg"><!-- 16px height --></div>
```

---

## Tables

Clean table styling with olive accents.

### Basic Table

```html
<table class="slos-table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
            <th>Column 3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data 1</td>
            <td>Data 2</td>
            <td>Data 3</td>
        </tr>
        <tr>
            <td>Data 4</td>
            <td>Data 5</td>
            <td>Data 6</td>
        </tr>
    </tbody>
</table>
```

**Features:**
- Background: `var(--slos-bg-secondary)`
- Border radius: 12px (with overflow hidden)
- Header background: `var(--slos-bg-tertiary)`
- Row hover: `var(--slos-bg-elevated)` background
- Header text: Uppercase, 0.5px letter-spacing, semibold

### Table Variants

**Striped Table:**
```html
<table class="slos-table slos-table-striped">
    <!-- Even rows have rgba(107, 142, 78, 0.05) background -->
</table>
```

**Bordered Table:**
```html
<table class="slos-table slos-table-bordered">
    <!-- All cells have visible borders -->
</table>
```

**Compact Table:**
```html
<table class="slos-table slos-table-compact">
    <!-- Smaller padding (8px instead of 16px) -->
</table>
```

---

## Form Elements

Corporate form controls with olive accents.

### Text Input

```html
<div class="slos-form-group">
    <label class="slos-label">
        Username
        <span class="slos-label-required">*</span>
    </label>
    <input type="text" class="slos-input" placeholder="Enter username">
    <span class="slos-help-text">Your unique username</span>
</div>
```

**CSS Classes:**
- `.slos-form-group` - Container with bottom margin (24px)
- `.slos-label` - Label text (14px, medium weight, primary color)
- `.slos-label-required` - Red asterisk for required fields
- `.slos-input` - Text input field
- `.slos-help-text` - Help text below input (12px, tertiary color)

**Input Features:**
- Background: `var(--slos-bg-tertiary)`
- Border: 1px solid default border
- Border radius: 8px
- Padding: 12px 16px
- Focus: Olive border + 4px olive glow
- Placeholder: Tertiary color

### Textarea

```html
<textarea class="slos-textarea" rows="4" placeholder="Enter description"></textarea>
```

- Same styling as `.slos-input`
- Vertical resize only: `resize: vertical`
- Min-height: 100px

### Select

```html
<select class="slos-select">
    <option value="">Choose an option</option>
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
</select>
```

- Same styling as `.slos-input`
- Custom dropdown arrow: Chevron SVG background
- Padding right: 40px (space for arrow)

### Error State

```html
<div class="slos-form-group">
    <label class="slos-label">Email</label>
    <input type="email" class="slos-input slos-input-error" value="invalid">
    <span class="slos-error-text">Invalid email format</span>
</div>
```

**Error Classes:**
- `.slos-input-error`, `.slos-textarea-error`, `.slos-select-error`
- Border color: Red
- Focus glow: Red (rgba(239, 68, 68, 0.2))
- `.slos-error-text`: Red error message (12px)

### Checkbox

```html
<div class="slos-form-check">
    <input type="checkbox" id="agree" class="slos-checkbox">
    <label for="agree" class="slos-form-check-label">I agree to the terms</label>
</div>
```

**Features:**
- Size: 20x20px
- Background: `var(--slos-bg-tertiary)`
- Border: 2px solid default border
- Border radius: 4px
- Checked: Olive background with white checkmark (CSS ::after)
- Hover: Border color changes to olive

### Radio Button

```html
<div class="slos-form-check">
    <input type="radio" id="option1" name="choice" class="slos-radio">
    <label for="option1" class="slos-form-check-label">Option 1</label>
</div>
```

**Features:**
- Size: 20x20px
- Border radius: 50% (circular)
- Checked: Olive border + 8px white dot (CSS ::after)
- Same hover/disabled states as checkbox

### Toggle Switch

```html
<div class="slos-form-check">
    <input type="checkbox" id="enable" class="slos-toggle">
    <label for="enable" class="slos-form-check-label">Enable feature</label>
</div>
```

**Features:**
- Size: 48x24px
- Background: `var(--slos-bg-elevated)`
- Border: 2px solid default border
- Knob: 16x16px white circle (CSS ::after)
- Checked: Olive background, knob translates 24px right
- Smooth transition: 200ms ease

---

## Badges & Pills

Compact status and label components.

### Basic Badge

```html
<span class="slos-badge slos-badge-primary">Primary</span>
<span class="slos-badge slos-badge-success">Success</span>
<span class="slos-badge slos-badge-warning">Warning</span>
<span class="slos-badge slos-badge-error">Error</span>
<span class="slos-badge slos-badge-info">Info</span>
```

**Features:**
- Padding: 4px 12px
- Font size: 12px (semibold)
- Border radius: Full (pill shape)
- Line height: 1
- White-space: nowrap

**Badge Variants:**
- `.slos-badge-primary`: Olive background (20% opacity), light olive text
- `.slos-badge-success`: Green background (15% opacity), green text
- `.slos-badge-warning`: Orange background (15% opacity), orange text
- `.slos-badge-error`: Red background (15% opacity), red text
- `.slos-badge-info`: Blue background (15% opacity), blue text

### Badge with Icon

```html
<span class="slos-badge slos-badge-success">
    <svg width="12" height="12"><!-- checkmark icon --></svg>
    <span>Active</span>
</span>
```

- Gap: 4px between icon and text
- Icon size: 12x12px recommended

---

## V3.2 ADDITION: Glow Border Utilities (Phase 4.4)

Utility classes for adding mint glow effects to any element.

### Basic Glow Border

```html
<div class="slos-card slos-glow-border">
    <!-- Card with mint glow border -->
</div>
```

**Glow Intensity Variants:**
```html
<!-- Small glow (8px spread) -->
<div class="slos-glow-border-sm">Content</div>

<!-- Medium glow (16px spread) -->
<div class="slos-glow-border">Content</div>

<!-- Large glow (24px spread) -->
<div class="slos-glow-border-lg">Content</div>

<!-- Extra large glow (32px spread) -->
<div class="slos-glow-border-xl">Content</div>
```

**Usage:**
- Add to cards for premium/highlighted sections
- Use on active/selected states
- Combine with hover states for emphasis
- Works with any element that has a border

**CSS:**
```css
.slos-glow-border {
    box-shadow: 0 0 16px rgba(16, 217, 160, 0.4);
}

.slos-glow-border:hover {
    box-shadow: 0 0 24px rgba(16, 217, 160, 0.6);
}
```

**Example - Premium Card:**
```html
<div class="slos-card slos-card-accent slos-glow-border-lg">
    <div class="slos-card-header">
        <h3 class="slos-card-title">Premium Feature</h3>
        <span class="slos-badge slos-badge-primary">PRO</span>
    </div>
    <div class="slos-card-body">
        Enhanced functionality with mint glow border.
    </div>
</div>
```

---

## V3.2 ADDITION: Live Indicator with Pulse (Phase 6.2)

Animated badge for live/active status with pulsing glow effect.

```html
<span class="slos-live-indicator">
    <span class="slos-live-dot"></span>
    <span class="slos-live-text">Live</span>
</span>
```

**Features:**
- Pulsing mint dot with expanding glow
- Animation: 2s infinite pulse
- Dot size: 8px with 0-12px glow expansion
- Respects `prefers-reduced-motion`

**Variants:**
```html
<!-- Success/Active (mint - default) -->
<span class="slos-live-indicator">Live</span>

<!-- Recording (red) -->
<span class="slos-live-indicator slos-live-recording">Recording</span>

<!-- Processing (orange) -->
<span class="slos-live-indicator slos-live-processing">Processing</span>

<!-- Idle (gray) -->
<span class="slos-live-indicator slos-live-idle">Idle</span>
```

**Size Variants:**
```html
<span class="slos-live-indicator slos-live-sm">Live</span>  <!-- 6px dot -->
<span class="slos-live-indicator">Live</span>              <!-- 8px dot -->
<span class="slos-live-indicator slos-live-lg">Live</span>  <!-- 12px dot -->
```

**Usage in Cards:**
```html
<div class="slos-card">
    <div class="slos-card-header">
        <h3 class="slos-card-title">Server Status</h3>
        <span class="slos-live-indicator">Live</span>
    </div>
    <div class="slos-card-body">
        Server is running normally.
    </div>
</div>
```

**CSS:**
```css
@keyframes slos-pulse {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(16, 217, 160, 0.7); 
    }
    50% { 
        box-shadow: 0 0 0 12px rgba(16, 217, 160, 0); 
    }
}

.slos-live-dot {
    animation: slos-pulse 2s infinite;
}
```

---

## V3.2 ADDITION: Shimmer Loading Skeleton (Phase 6.3)

Enhanced skeleton loaders with shimmer animation for better loading states.

### Basic Skeleton

```html
<div class="slos-skeleton slos-skeleton-shimmer"></div>
```

**Features:**
- Background: `var(--slos-bg-elevated)`
- Shimmer: White gradient sliding across (2s animation)
- Height: 20px (default)
- Border radius: 8px
- No flash/flicker

**Skeleton Variants:**
```html
<!-- Text line -->
<div class="slos-skeleton slos-skeleton-text slos-skeleton-shimmer"></div>

<!-- Heading -->
<div class="slos-skeleton slos-skeleton-heading slos-skeleton-shimmer"></div>

<!-- Circle (avatar) -->
<div class="slos-skeleton slos-skeleton-circle slos-skeleton-shimmer"></div>

<!-- Card -->
<div class="slos-skeleton slos-skeleton-card slos-skeleton-shimmer"></div>
```

**Complete Skeleton Card:**
```html
<div class="slos-card">
    <div class="slos-card-header">
        <div class="slos-skeleton slos-skeleton-heading slos-skeleton-shimmer" 
             style="width: 60%;"></div>
    </div>
    <div class="slos-card-body">
        <div class="slos-skeleton slos-skeleton-text slos-skeleton-shimmer" 
             style="width: 100%;"></div>
        <div class="slos-skeleton slos-skeleton-text slos-skeleton-shimmer" 
             style="width: 85%;"></div>
        <div class="slos-skeleton slos-skeleton-text slos-skeleton-shimmer" 
             style="width: 70%;"></div>
    </div>
</div>
```

**Shimmer Animation:**
```css
@keyframes slos-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.slos-skeleton-shimmer {
    background: linear-gradient(
        90deg,
        var(--slos-bg-elevated) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        var(--slos-bg-elevated) 100%
    );
    background-size: 200% 100%;
    animation: slos-shimmer 2s infinite;
}
```

**Accessibility:**
```html
<div class="slos-skeleton slos-skeleton-shimmer" 
     role="status" 
     aria-label="Loading content">
    <span class="sr-only">Loading...</span>
</div>
```

---

## V3.2 ADDITION: Chart Components (Phase 5)

For detailed chart documentation, see `docs/theme/chart-components.md` and `assets/css/slos-charts.css`.

### Gradient Bar Chart (Quick Reference)

```html
<div class="slos-chart-container">
    <div class="slos-bar-chart">
        <div class="slos-bar" style="height: 60%;"></div>
        <div class="slos-bar" style="height: 85%;"></div>
        <div class="slos-bar" style="height: 45%;"></div>
        <div class="slos-bar" style="height: 95%;"></div>
    </div>
</div>
```

**Features:**
- Gradient fills: `--slos-gradient-bar`
- Upward glow: Mint shadow from top
- Hover: Brightness increase + glow expansion
- Responsive: Flexbox layout

### Sparkline (Quick Reference)

```html
<svg class="slos-sparkline" viewBox="0 0 200 50">
    <defs>
        <linearGradient id="sparkline-gradient">
            <stop offset="0%" stop-color="#10d9a0"/>
            <stop offset="100%" stop-color="#6b8e4e"/>
        </linearGradient>
    </defs>
    <polyline points="0,40 50,30 100,10 150,25 200,5" 
              fill="none" 
              stroke="url(#sparkline-gradient)" 
              stroke-width="2"/>
</svg>
```

**Features:**
- Gradient stroke
- Smooth curves
- Lightweight SVG
- Inline display

---

## Alerts & Notifications

Status messages with icons and dismiss buttons.

### Basic Alert

```html
<div class="slos-alert slos-alert-success">
    <div class="slos-alert-icon">
        <svg><!-- icon --></svg>
    </div>
    <div class="slos-alert-content">
        <div class="slos-alert-title">Success</div>
        <div class="slos-alert-message">Operation completed successfully.</div>
    </div>
    <button class="slos-alert-dismiss" aria-label="Dismiss">&times;</button>
</div>
```

**CSS Classes:**
- `.slos-alert` - Alert container
- `.slos-alert-icon` - Icon container (left side)
- `.slos-alert-content` - Text content area
- `.slos-alert-title` - Alert heading (semibold)
- `.slos-alert-message` - Alert message text
- `.slos-alert-dismiss` - Close button (×)

**Alert Variants:**
- `.slos-alert-success`: Green border/background
- `.slos-alert-warning`: Orange border/background
- `.slos-alert-error`: Red border/background
- `.slos-alert-info`: Blue border/background

**Features:**
- Border: 1px solid status color (30% opacity)
- Background: Status color (15% opacity)
- Border radius: 8px
- Padding: 16px
- Flexbox layout: icon | content | dismiss

---

## Loading States

### Spinner

```html
<div class="slos-spinner"></div>
<div class="slos-spinner slos-spinner-lg"></div>
```

**Features:**
- Default: 24x24px
- Large: 48x48px
- Olive color border (3px)
- Top border: Transparent
- Animation: 1s infinite linear rotation

### Skeleton Loader

```html
<div class="slos-skeleton"></div>
<div class="slos-skeleton slos-skeleton-text"></div>
<div class="slos-skeleton slos-skeleton-circle"></div>
```

**Features:**
- Background: `var(--slos-bg-elevated)`
- Shimmer animation: Gradient slides across
- Height: 20px (default), 16px (text), 40px (circle)
- Border radius: 8px (default), 4px (text), 50% (circle)

---

## Tooltips & Popovers

### Tooltip

```html
<button class="slos-btn-primary" data-tooltip="Tooltip text">
    Hover me
</button>
```

**CSS (JavaScript required for positioning):**
- Background: `var(--slos-bg-elevated)`
- Color: `var(--slos-text-primary)`
- Padding: 8px 12px
- Border radius: 6px
- Font size: 12px
- Shadow: `--slos-shadow-lg`
- Z-index: 1070 (highest)

### Popover

```html
<div class="slos-popover">
    <div class="slos-popover-arrow"></div>
    <div class="slos-popover-header">
        <h4 class="slos-popover-title">Popover Title</h4>
    </div>
    <div class="slos-popover-body">
        Popover content goes here.
    </div>
</div>
```

**Features:**
- Background: `var(--slos-bg-secondary)`
- Border: 1px solid default border
- Border radius: 12px
- Shadow: `--slos-shadow-xl`
- Max-width: 300px
- Arrow: 8px triangle pointing to trigger

---

## Navigation

### Tabs

```html
<div class="slos-tabs">
    <button class="slos-tab slos-tab-active">Tab 1</button>
    <button class="slos-tab">Tab 2</button>
    <button class="slos-tab">Tab 3</button>
</div>
```

**Features:**
- Background: `var(--slos-bg-secondary)`
- Active tab: Olive bottom border (3px)
- Active text: Olive color
- Hover: Background changes to elevated
- Border bottom: Default border (inactive tabs)

### Breadcrumbs

```html
<nav class="slos-breadcrumb">
    <a href="#" class="slos-breadcrumb-item">Home</a>
    <span class="slos-breadcrumb-separator">/</span>
    <a href="#" class="slos-breadcrumb-item">Library</a>
    <span class="slos-breadcrumb-separator">/</span>
    <span class="slos-breadcrumb-item slos-breadcrumb-active">Data</span>
</nav>
```

**Features:**
- Flexbox layout with 8px gap
- Links: Secondary color, hover changes to olive
- Active: Primary color, not a link
- Separator: Tertiary color

### Pagination

```html
<div class="slos-pagination">
    <button class="slos-pagination-btn" disabled>&laquo;</button>
    <button class="slos-pagination-btn slos-pagination-active">1</button>
    <button class="slos-pagination-btn">2</button>
    <button class="slos-pagination-btn">3</button>
    <button class="slos-pagination-btn">&raquo;</button>
</div>
```

**Features:**
- Flexbox layout with 4px gap
- Buttons: 32x32px, rounded (6px)
- Active: Olive background
- Hover: Elevated background
- Disabled: Opacity 0.5, no pointer

---

## Usage Guidelines

### Loading Components in WordPress

```php
// In your PHP file
$assets->enqueue_style(
    'slos-design-system',
    'assets/css/slos-design-system.css',
    [],
    true // Load minified version
);

$assets->enqueue_style(
    'slos-components',
    'assets/css/slos-components.css',
    ['slos-design-system'], // Dependency
    true
);
```

### Combining Components

```html
<!-- Card with form and buttons -->
<div class="slos-card">
    <div class="slos-card-header">
        <h3 class="slos-card-title">User Settings</h3>
    </div>
    <div class="slos-card-body">
        <div class="slos-form-group">
            <label class="slos-label">Email</label>
            <input type="email" class="slos-input" placeholder="email@example.com">
        </div>
        <div class="slos-form-check">
            <input type="checkbox" id="notifications" class="slos-toggle">
            <label for="notifications" class="slos-form-check-label">Enable notifications</label>
        </div>
    </div>
    <div class="slos-card-footer">
        <button class="slos-btn-secondary">Cancel</button>
        <button class="slos-btn-primary">Save Changes</button>
    </div>
</div>
```

### Accessibility Best Practices

1. **Always include labels:**
   ```html
   <label for="username" class="slos-label">Username</label>
   <input type="text" id="username" class="slos-input">
   ```

2. **Use semantic HTML:**
   - `<button>` for actions (not `<div>` with click handlers)
   - `<a>` for navigation (not `<button>`)
   - Proper heading hierarchy (h1 → h2 → h3)

3. **Add ARIA labels:**
   ```html
   <button class="slos-modal-close" aria-label="Close modal">&times;</button>
   ```

4. **Keyboard navigation:**
   - All interactive elements focusable (tabindex="0")
   - Focus states visible (olive outline)
   - Enter/Space triggers actions

5. **Color contrast:**
   - All text meets WCAG 2.1 AA (4.5:1 minimum)
   - Don't rely on color alone (use icons + text)

---

## Browser Compatibility

- ✅ **Chrome 90+**
- ✅ **Firefox 88+**
- ✅ **Safari 14+**
- ✅ **Edge 90+**
- ⚠️ **IE11** - Requires CSS variable polyfill

---

## Performance

- **File size:** 38.41 KB uncompressed, 24.55 KB minified (36.08% savings)
- **V3.2 Additions:** Chart components in separate file (4.65 KB \u2192 2.14 KB minified)
- **CSS Grid:** Used for layouts (with Flexbox fallbacks)
- **Animations:** Hardware-accelerated (transform, opacity)
- **Repaints:** Minimized (avoid width/height animations)
- **Shimmer effect:** GPU-accelerated gradient animation
- **Counter animations:** RequestAnimationFrame for smooth 60fps

---

## Migration Notes

**From v3.1.1 to V3.2:**

| Old | New | Changes |
|-----|-----|---------|
| `.slos-btn-primary` | Same | Now uses mint gradient instead of olive |
| `.slos-stat-card` | Enhanced | Added gradient accent bar + animated counter |
| N/A | `.slos-progress-ring` | **NEW** - SVG circular progress |
| N/A | `.slos-glow-border-*` | **NEW** - Mint glow utilities |
| N/A | `.slos-live-indicator` | **NEW** - Pulsing live badge |
| `.slos-skeleton` | Enhanced | Added shimmer animation variant |
| N/A | Chart components | **NEW** - See `slos-charts.css` |

**From Glassmorphism (v3.1.0) to Olive Theme (v3.1.1+):**

| Old Class | New Class | Changes |
|-----------|-----------|---------|
| `.shahi-card-glass` | `.slos-card` | Removed backdrop-filter, added solid bg |
| `.shahi-btn-blue` | `.slos-btn-primary` | Changed to olive gradient |
| `.shahi-modal-glass` | `.slos-modal` | Removed blur, added solid overlay |
| `.shahi-badge-blue` | `.slos-badge-primary` | Changed to olive color |

**Removed CSS:**
- ❌ `backdrop-filter: blur(10px)`
- ❌ `background: rgba(255, 255, 255, 0.1)`
- ❌ Blue color scheme (#3b82f6)

**Added CSS:**
- ✅ Solid backgrounds (`--slos-bg-*`)
- ✅ Proper shadows (`--slos-shadow-*`)
- ✅ Olive color scheme (#6b8e4e)

---

**Last Updated:** January 2026  
**Maintainer:** ShahiLegalFlowSuite Development Team
