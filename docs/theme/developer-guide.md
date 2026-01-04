# SLOS Developer Guidelines

**Version:** 1.1.0 (V3.2 Enhanced)  
**Last Updated:** January 2026  
**Plugin:** ShahiLegalFlowSuite v3.1.1+  
**V3.2 Additions:** JavaScript animation API, chart components, gradient utilities, enhanced asset loading

---

## Overview

Comprehensive development guidelines for maintaining and extending the ShahiLegalFlowSuite plugin frontend. This guide covers file structure, naming conventions, coding standards, workflow, V3.2 enhancements, and best practices.

**Target Audience:**
- Frontend developers
- WordPress developers
- Theme maintainers
- Contributors

**V3.2 New Topics:**
- JavaScript Animation API (SLOSCounter class)
- Chart component integration
- Gradient rendering best practices
- Enhanced asset loading order

---

## Table of Contents

1. [File Structure](#file-structure)
2. [Naming Conventions](#naming-conventions)
3. [CSS Architecture](#css-architecture)
4. [Development Workflow](#development-workflow)
5. [Code Standards](#code-standards)
6. [Testing Guidelines](#testing-guidelines)
7. [Performance Best Practices](#performance-best-practices)
8. [Accessibility Requirements](#accessibility-requirements)
9. [Git Workflow](#git-workflow)
10. [Common Tasks](#common-tasks)

---

## File Structure

### Plugin Directory Layout

```
Shahi LegalOps Suite - 3.1.1/
├── assets/
│   ├── css/
│   │   ├── slos-design-system.css          # LOAD FIRST (CSS variables)
│   │   ├── slos-design-system.min.css
│   │   ├── slos-components.css             # Component library
│   │   ├── slos-components.min.css
│   │   ├── slos-browser-a11y-enhancements.css  # Accessibility
│   │   ├── admin-dashboard.css             # Page-specific CSS
│   │   ├── admin-modules.css
│   │   ├── consent-banner.css
│   │   └── ... (37 CSS files total)
│   ├── js/
│   │   ├── admin-dashboard.js
│   │   ├── admin-modules.js
│   │   └── ...
│   └── images/
│       └── ...
├── includes/
│   ├── Core/
│   │   ├── Assets.php                      # Asset loading system
│   │   └── ...
│   ├── Admin/
│   │   ├── Dashboard.php                   # Admin pages
│   │   └── ...
│   └── ...
├── templates/
│   ├── admin/
│   │   ├── dashboard.php                   # Admin page templates
│   │   ├── modules.php
│   │   └── ...
│   └── frontend/
│       └── ...
├── docs/
│   └── theme/
│       ├── design-system.md                # THIS DOCUMENTATION
│       ├── component-guide.md
│       ├── accessibility.md
│       ├── performance.md
│       ├── migration-guide.md
│       └── developer-guide.md              # YOU ARE HERE
└── ...
```

### CSS File Loading Order

**Critical Order (DO NOT CHANGE):**

1. **slos-design-system.css** (MUST BE FIRST)
   - Contains all CSS variables
   - Required by all other stylesheets
   - Version: 4.1.0 (V3.2)
   - Size: 38.58 KB → 21.74 KB minified

2. **slos-components.css** (SECOND)
   - Depends on design system variables
   - Provides reusable components
   - Version: 3.2.0 (V3.2 Enhanced)
   - Size: 38.41 KB → 24.55 KB minified

3. **slos-charts.css** (THIRD - V3.2 NEW)
   - Chart and visualization components
   - Depends on design system variables
   - Version: 3.2.0
   - Size: 4.65 KB → 2.14 KB minified

4. **Page-specific CSS** (FOURTH)
   - admin-dashboard.css
   - admin-modules.css
   - etc.

5. **Accessibility enhancements** (LAST)
   - slos-browser-a11y-enhancements.css
   - Overrides for a11y compliance

**JavaScript Loading:**

1. **slos-animations.js** (V3.2 NEW)
   - Counter animations, scroll effects
   - Version: 3.2.0
   - Size: 5.38 KB → 1.52 KB minified
   - Load in footer (after DOM ready)

**Example in Assets.php:**

```php
// ✅ Correct order (V3.2)
$this->enqueue_style( 'design-system', 'assets/css/slos-design-system.css' );
$this->enqueue_style( 'components', 'assets/css/slos-components.css', ['slos-design-system'] );
$this->enqueue_style( 'charts', 'assets/css/slos-charts.css', ['slos-design-system'] );
$this->enqueue_style( 'dashboard', 'assets/css/admin-dashboard.css', ['slos-design-system'] );
$this->enqueue_style( 'a11y', 'assets/css/slos-browser-a11y-enhancements.css', 
    ['slos-design-system', 'slos-components'] );

// JavaScript
$this->enqueue_script( 'animations', 'assets/js/slos-animations.js', [], true );  // true = footer

// ❌ Wrong order (components before design system)
$this->enqueue_style( 'components', 'assets/css/slos-components.css' );
$this->enqueue_style( 'design-system', 'assets/css/slos-design-system.css' );
```

**V3.2 Total Payload:**
- CSS: 49.95 KB minified (3 core files)
- JS: 1.52 KB minified (1 file)
- **Total: 51.47 KB**

---

## V3.2 ENHANCEMENT: JavaScript Animation API (Phase 6.1)

### SLOSCounter Class

Smooth counter animation from 0 to target value with easing and formatting.

**Basic Usage:**

```javascript
// HTML
<div class="slos-stat-value" data-target="1234">0</div>

// JavaScript
const element = document.querySelector('.slos-stat-value');
const counter = new SLOSCounter(element, {
    duration: 2000,     // Animation duration in ms
    decimals: 0,        // Number of decimal places
    prefix: '',         // String before value
    suffix: '',         // String after value
    separator: ','      // Thousand separator
});
counter.start();
```

**Configuration Options:**

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `duration` | number | 2000 | Animation duration in milliseconds |
| `decimals` | number | 0 | Number of decimal places (0-2 recommended) |
| `prefix` | string | '' | String to prepend to value (e.g., '$', '€') |
| `suffix` | string | '' | String to append to value (e.g., '%', 'K', 'M') |
| `separator` | string | ',' | Thousand separator character |

**Advanced Examples:**

```javascript
// Currency counter
const revenue = new SLOSCounter(element, {
    duration: 2500,
    decimals: 2,
    prefix: '$',
    separator: ','
});
revenue.start();  // Result: $12,345.67

// Percentage counter
const conversion = new SLOSCounter(element, {
    duration: 1500,
    decimals: 1,
    suffix: '%'
});
conversion.start();  // Result: 87.5%

// Large number counter (K/M)
const users = new SLOSCounter(element, {
    duration: 2000,
    decimals: 1,
    suffix: 'K'
});
users.start();  // Result: 1,234.5K
```

**Auto-Initialization:**

Counters automatically initialize on page load with scroll detection:

```javascript
// Automatic detection (built into slos-animations.js)
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('[data-target]');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                const counter = new SLOSCounter(entry.target);
                counter.start();
                entry.target.classList.add('animated');
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer.observe(counter));
});
```

**Performance Notes:**

- Uses `requestAnimationFrame` for 60fps smooth animation
- Easing function: Ease-out-cubic (fast start, gradual slowdown)
- No layout thrashing (text-only updates)
- Respects `prefers-reduced-motion` (instant display)
- Memory: ~10KB per counter instance
- CPU: <2% during animation

**Accessibility:**

```html
<!-- Screen reader gets final value from data-target -->
<div class="slos-stat-value" 
     data-target="1234"
     aria-label="Total users: 1,234">
    0
</div>
```

**Best Practices:**

1. **Use data-target attribute:** Stores final value for screen readers
2. **Limit decimals:** 0-2 decimals max (performance + readability)
3. **Batch animations:** Multiple counters can animate simultaneously
4. **Scroll detection:** Only animate when visible (Intersection Observer)
5. **Single run:** Counters animate once, not on every scroll

---

## V3.2 ENHANCEMENT: Chart Component Usage (Phase 5)

### Gradient Bar Chart

**HTML Structure:**

```html
<div class="slos-chart-container">
    <div class="slos-bar-chart">
        <div class="slos-bar" style="height: 60%;" data-value="60">
            <span class="slos-bar-label">60%</span>
        </div>
        <div class="slos-bar" style="height: 85%;" data-value="85">
            <span class="slos-bar-label">85%</span>
        </div>
        <div class="slos-bar" style="height: 45%;" data-value="45">
            <span class="slos-bar-label">45%</span>
        </div>
        <div class="slos-bar" style="height: 95%;" data-value="95">
            <span class="slos-bar-label">95%</span>
        </div>
    </div>
</div>
```

**Dynamic Chart Generation:**

```javascript
function createBarChart(container, data) {
    const chart = document.createElement('div');
    chart.className = 'slos-bar-chart';
    
    data.forEach(value => {
        const bar = document.createElement('div');
        bar.className = 'slos-bar';
        bar.style.height = `${value}%`;
        bar.dataset.value = value;
        
        const label = document.createElement('span');
        label.className = 'slos-bar-label';
        label.textContent = `${value}%`;
        bar.appendChild(label);
        
        chart.appendChild(bar);
    });
    
    container.innerHTML = '';
    container.appendChild(chart);
}

// Usage
const data = [60, 85, 45, 95, 70];
createBarChart(document.querySelector('.slos-chart-container'), data);
```

**Bar Variants:**

```html
<!-- Success bars (green gradient) -->
<div class="slos-bar slos-bar-success" style="height: 90%;"></div>

<!-- Warning bars (orange gradient) -->
<div class="slos-bar slos-bar-warning" style="height: 60%;"></div>

<!-- Error bars (red gradient) -->
<div class="slos-bar slos-bar-error" style="height: 25%;"></div>
```

### Sparkline Chart

**SVG Structure:**

```html
<svg class="slos-sparkline" viewBox="0 0 200 50" width="200" height="50">
    <defs>
        <linearGradient id="sparkline-gradient-<?php echo $unique_id; ?>" 
                        x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#10d9a0"/>
            <stop offset="100%" stop-color="#6b8e4e"/>
        </linearGradient>
    </defs>
    <polyline points="0,40 50,30 100,10 150,25 200,5" 
              fill="none" 
              stroke="url(#sparkline-gradient-<?php echo $unique_id; ?>)" 
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"/>
</svg>
```

**Dynamic Sparkline:**

```javascript
function createSparkline(container, data, options = {}) {
    const { width = 200, height = 50, strokeWidth = 2 } = options;
    
    // Calculate points
    const max = Math.max(...data);
    const min = Math.min(...data);
    const range = max - min;
    
    const points = data.map((value, index) => {
        const x = (index / (data.length - 1)) * width;
        const y = height - ((value - min) / range) * height;
        return `${x},${y}`;
    }).join(' ');
    
    // Create SVG
    const svg = `
        <svg class="slos-sparkline" viewBox="0 0 ${width} ${height}" 
             width="${width}" height="${height}">
            <defs>
                <linearGradient id="sparkline-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#10d9a0"/>
                    <stop offset="100%" stop-color="#6b8e4e"/>
                </linearGradient>
            </defs>
            <polyline points="${points}" 
                      fill="none" 
                      stroke="url(#sparkline-gradient)" 
                      stroke-width="${strokeWidth}"
                      stroke-linecap="round"
                      stroke-linejoin="round"/>
        </svg>
    `;
    
    container.innerHTML = svg;
}

// Usage
const data = [20, 35, 25, 45, 40, 50, 35, 55];
createSparkline(document.querySelector('.sparkline-container'), data);
```

---

## V3.2 ENHANCEMENT: CSS Gradient Best Practices (Phase 1-5)

### Using Gradient Variables

**Available Gradients:**

```css
/* From design system */
--slos-gradient-primary: linear-gradient(135deg, #10d9a0 0%, #6b8e4e 100%);
--slos-gradient-stat: linear-gradient(90deg, #10d9a0 0%, #0ec28e 100%);
--slos-gradient-bar: linear-gradient(180deg, #10d9a0 0%, #0ec28e 100%);
--slos-gradient-glow: linear-gradient(135deg, rgba(16,217,160,0.2) 0%, rgba(107,142,78,0.2) 100%);
```

**Usage Examples:**

```css
/* Button with gradient background */
.my-button {
    background: var(--slos-gradient-primary);
    color: #ffffff;
    border: none;
}

/* Stat card accent bar */
.my-stat-card::before {
    content: '';
    display: block;
    height: 4px;
    background: var(--slos-gradient-stat);
}

/* Chart bar with gradient */
.my-chart-bar {
    background: var(--slos-gradient-bar);
    box-shadow: 0 -4px 12px rgba(16, 217, 160, 0.2);
}
```

### Creating Custom Gradients

**Best Practices:**

1. **Limit color stops:** 2-4 stops maximum (performance)
2. **Use standard angles:** 0°, 45°, 90°, 135°, 180° (optimized)
3. **Static gradients:** No animated gradients (expensive)
4. **GPU-accelerated:** Gradients render on GPU
5. **Reuse patterns:** Define in CSS variables

**Good Examples:**

```css
/* ✅ Good: 2-stop linear gradient */
.gradient-good {
    background: linear-gradient(135deg, #10d9a0 0%, #6b8e4e 100%);
}

/* ✅ Good: Subtle radial for pattern */
.pattern-good {
    background-image: radial-gradient(
        circle at 1px 1px,
        rgba(16, 217, 160, 0.05) 1px,
        transparent 1px
    );
    background-size: 40px 40px;
}

/* ✅ Good: Text gradient (webkit) */
.text-gradient {
    background: linear-gradient(135deg, #10d9a0 0%, #6b8e4e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
```

**Bad Examples:**

```css
/* ❌ Bad: Too many stops (10+) */
.gradient-bad {
    background: linear-gradient(
        135deg,
        #color1 0%, #color2 10%, #color3 20%, /* ... 10+ stops */
    );
    /* Render time: 5-10ms, causes jank */
}

/* ❌ Bad: Animated gradient (very expensive) */
@keyframes gradient-animation {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.gradient-animated {
    background: linear-gradient(...);
    background-size: 200% 200%;
    animation: gradient-animation 3s infinite;  /* CPU-intensive! */
}

/* ❌ Bad: Multiple overlapping radials */
.pattern-bad {
    background-image: 
        radial-gradient(...),
        radial-gradient(...),
        radial-gradient(...),
        radial-gradient(...);  /* 4 layers, expensive! */
}
```

### Gradient Performance Checklist

- [ ] **2-4 color stops maximum**
- [ ] **Static gradients (no animation)**
- [ ] **Simple angles (0°, 45°, 90°, 135°, 180°)**
- [ ] **Reuse via CSS variables**
- [ ] **Test on low-end devices**
- [ ] **Avoid multiple overlapping gradients**
- [ ] **Use SVG gradients for complex charts**

---

## Naming Conventions

### CSS Class Names

**Prefix:** All custom classes use `slos-` prefix to avoid conflicts.

**BEM-like Structure:**

```css
/* Block */
.slos-card {}

/* Element (use single dash) */
.slos-card-header {}
.slos-card-title {}
.slos-card-body {}

/* Modifier (use double dash) */
.slos-card--elevated {}
.slos-card--accent {}

/* State (use single dash) */
.slos-btn-primary {}
.slos-btn-secondary {}
.slos-btn-disabled {}
```

**Examples:**

```html
<!-- Base component -->
<div class="slos-card">
    <div class="slos-card-header">
        <h3 class="slos-card-title">Title</h3>
    </div>
    <div class="slos-card-body">Content</div>
</div>

<!-- Component with modifier -->
<div class="slos-card slos-card-elevated">
    <!-- Elevated card with more shadow -->
</div>

<!-- Button states -->
<button class="slos-btn-primary">Default</button>
<button class="slos-btn-primary slos-btn-sm">Small</button>
<button class="slos-btn-primary" disabled>Disabled</button>
```

### CSS Variable Names

**Pattern:** `--{prefix}-{category}-{property}[-{variant}]`

**Examples:**

```css
/* Color variables */
--slos-accent-primary      /* Base olive */
--slos-accent-hover        /* Hover state */
--slos-accent-active       /* Active state */

/* Background variables */
--slos-bg-primary          /* Darkest background */
--slos-bg-secondary        /* Card background */
--slos-bg-tertiary         /* Elevated elements */

/* Text variables */
--slos-text-primary        /* Primary text (white) */
--slos-text-secondary      /* Secondary text (gray) */
--slos-text-tertiary       /* Tertiary text (dim) */

/* Spacing variables */
--slos-space-xs            /* 8px */
--slos-space-md            /* 16px */
--slos-space-lg            /* 24px */

/* Shadow variables */
--slos-shadow-sm           /* Small shadow */
--slos-shadow-md           /* Medium shadow */
--slos-shadow-lg           /* Large shadow */
```

### File Names

**CSS Files:**
- `slos-{component}.css` - Component library files
- `admin-{page}.css` - Admin page-specific styles
- `{feature}.css` - Feature-specific styles

**Examples:**
- `slos-design-system.css` (system-wide variables)
- `slos-components.css` (component library)
- `admin-dashboard.css` (dashboard page)
- `consent-banner.css` (consent feature)

**JavaScript Files:**
- `admin-{page}.js` - Admin page scripts
- `{feature}.js` - Feature scripts

**Examples:**
- `admin-dashboard.js`
- `consent-banner.js`

### PHP Class Names

**PSR-4 Autoloading:**

```php
// Namespace: ShahiLegalFlowSuite\{Category}\{Class}
namespace ShahiLegalFlowSuite\Core;

class Assets {
    // Asset loading
}
```

**File Structure:**
```
includes/
├── Core/
│   └── Assets.php       → ShahiLegalFlowSuite\Core\Assets
├── Admin/
│   └── Dashboard.php    → ShahiLegalFlowSuite\Admin\Dashboard
└── Modules/
    └── Consent.php      → ShahiLegalFlowSuite\Modules\Consent
```

---

## CSS Architecture

### Design System First

**Always use CSS variables from design system:**

```css
/* ❌ BAD: Hardcoded values */
.my-card {
    background: #242b24;
    padding: 24px;
    border-radius: 12px;
    color: #f8fafc;
}

/* ✅ GOOD: CSS variables */
.my-card {
    background: var(--slos-bg-secondary);
    padding: var(--slos-space-lg);
    border-radius: var(--slos-radius-lg);
    color: var(--slos-text-primary);
}
```

### Utility-First for Spacing

Use design system spacing utilities when possible:

```html
<!-- Utility classes for common spacing -->
<div class="slos-p-lg slos-mb-md">
    <!-- padding: 24px, margin-bottom: 16px -->
</div>
```

### Component-Based Structure

**Each component should be:**
- Self-contained
- Reusable
- Documented
- Themeable (uses CSS variables)

**Example: Button Component**

```css
/**
 * Button Component
 * 
 * Usage:
 *   <button class="slos-btn-primary">Click</button>
 *   <button class="slos-btn-secondary">Cancel</button>
 */

.slos-btn-primary {
    background: var(--slos-accent-primary);
    color: var(--slos-text-primary);
    padding: var(--slos-space-md) var(--slos-space-lg);
    border-radius: var(--slos-radius-md);
    border: none;
    cursor: pointer;
    transition: all var(--slos-transition-base);
}

.slos-btn-primary:hover {
    background: var(--slos-accent-hover);
    transform: translateY(-2px);
    box-shadow: var(--slos-shadow-lg);
}

.slos-btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
```

### Responsive Design

**Mobile-First Approach:**

```css
/* Base styles (mobile) */
.slos-card {
    padding: var(--slos-space-md);
    margin-bottom: var(--slos-space-md);
}

/* Tablet (768px+) */
@media (min-width: 768px) {
    .slos-card {
        padding: var(--slos-space-lg);
        margin-bottom: var(--slos-space-lg);
    }
}

/* Desktop (1024px+) */
@media (min-width: 1024px) {
    .slos-card {
        padding: var(--slos-space-xl);
    }
}
```

**Breakpoints:**
- Mobile: 0-767px (default)
- Tablet: 768px-1023px
- Desktop: 1024px+

---

## Development Workflow

### 1. Set Up Development Environment

**Enable WordPress debugging:**

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );  // Load unminified CSS/JS
```

**Install development tools:**

```bash
# Node.js tools (optional)
npm install -g csslint
npm install -g stylelint
npm install -g prettier

# Accessibility testing
npm install -g @axe-core/cli
npm install -g pa11y
```

### 2. Create New Component

**Step-by-step:**

1. **Add CSS to component library:**

```css
/* assets/css/slos-components.css */

/* ============================================================================
   ALERT COMPONENT
   Status messages with icons and dismiss buttons
   ========================================================================= */

.slos-alert {
    display: flex;
    align-items: flex-start;
    gap: var(--slos-space-md);
    padding: var(--slos-space-md);
    border-radius: var(--slos-radius-md);
    border: 1px solid transparent;
}

.slos-alert-success {
    background: var(--slos-success-bg);
    border-color: var(--slos-success-border);
    color: var(--slos-success);
}

/* Add error, warning, info variants... */
```

2. **Create HTML template:**

```php
<!-- templates/admin/components/alert.php -->
<div class="slos-alert slos-alert-<?php echo esc_attr( $type ); ?>" role="alert">
    <div class="slos-alert-icon">
        <?php echo $icon; ?>
    </div>
    <div class="slos-alert-content">
        <?php echo esc_html( $message ); ?>
    </div>
    <button class="slos-alert-dismiss" aria-label="Dismiss">&times;</button>
</div>
```

3. **Minify CSS:**

```powershell
# PowerShell
$file = "assets/css/slos-components.css"
$minFile = $file -replace '\.css$', '.min.css'

$content = Get-Content $file -Raw
$content = $content -replace '/\*[\s\S]*?\*/', ''
$content = $content -replace '\s+', ' '
$content = $content -replace '\s*{\s*', '{'
$content = $content -replace '\s*}\s*', '}'
$content = $content -replace '\s*:\s*', ':'
$content = $content -replace ';\s*}', '}'
$content = $content.Trim()

Set-Content $minFile $content -NoNewline
```

4. **Document component:**

Add to `docs/theme/component-guide.md`:

```markdown
### Alert Component

Status messages with icons and dismiss buttons.

**Usage:**
```html
<div class="slos-alert slos-alert-success" role="alert">
    <span class="slos-alert-message">Success!</span>
</div>
```

**Variants:**
- `.slos-alert-success` - Green
- `.slos-alert-error` - Red
- `.slos-alert-warning` - Orange
- `.slos-alert-info` - Blue
```

5. **Test component:**

- Visual test in all browsers
- Keyboard navigation test
- Screen reader test (NVDA/VoiceOver)
- Color contrast test (4.5:1 minimum)

### 3. Modify Existing Component

**Workflow:**

1. Find component in `slos-components.css`
2. Make changes to full CSS (not minified)
3. Re-minify CSS
4. Test in browser
5. Update documentation if API changes

**Example:**

```css
/* Before */
.slos-card {
    padding: var(--slos-space-lg);
}

/* After (add hover state) */
.slos-card {
    padding: var(--slos-space-lg);
    transition: all var(--slos-transition-base);
}

.slos-card:hover {
    box-shadow: var(--slos-shadow-lg);
    transform: translateY(-2px);
}
```

### 4. Add New Admin Page

**Steps:**

1. **Create PHP controller:**

```php
// includes/Admin/MyNewPage.php
namespace ShahiLegalFlowSuite\Admin;

class MyNewPage {
    public function render() {
        $this->enqueue_assets();
        include plugin_dir_path( __DIR__ ) . '../templates/admin/my-new-page.php';
    }
    
    private function enqueue_assets() {
        $assets = new \ShahiLegalFlowSuite\Core\Assets();
        $assets->enqueue_style( 'design-system', 'assets/css/slos-design-system.css' );
        $assets->enqueue_style( 'components', 'assets/css/slos-components.css', ['slos-design-system'] );
        $assets->enqueue_style( 'my-new-page', 'assets/css/admin-my-new-page.css', ['slos-design-system'] );
    }
}
```

2. **Create CSS file:**

```css
/* assets/css/admin-my-new-page.css */

/**
 * My New Page Styles
 * Depends on: slos-design-system.css
 */

.slos-mynewpage-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--slos-space-xl);
}

.slos-mynewpage-header {
    margin-bottom: var(--slos-space-lg);
}
```

3. **Create template:**

```php
<!-- templates/admin/my-new-page.php -->
<div class="wrap slos-mynewpage-container">
    <header class="slos-mynewpage-header">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    </header>
    
    <div class="slos-card">
        <div class="slos-card-body">
            Page content here
        </div>
    </div>
</div>
```

4. **Register admin menu:**

```php
// includes/Admin/AdminMenu.php
add_menu_page(
    'My New Page',
    'My New Page',
    'manage_options',
    'slos-my-new-page',
    [ new MyNewPage(), 'render' ],
    'dashicons-admin-generic',
    30
);
```

---

## Code Standards

### CSS Formatting

**Use Prettier or manual formatting:**

```css
/* ✅ GOOD: Consistent formatting */
.slos-card {
    background: var(--slos-bg-secondary);
    padding: var(--slos-space-lg);
    border-radius: var(--slos-radius-lg);
    box-shadow: var(--slos-shadow-md);
}

/* ❌ BAD: Inconsistent formatting */
.slos-card{background:var(--slos-bg-secondary);padding:var(--slos-space-lg);
    border-radius: var(--slos-radius-lg);box-shadow: var(--slos-shadow-md);}
```

**Indentation:** 4 spaces (not tabs)

**Order of properties:**

1. Display & positioning
2. Box model (width, height, padding, margin)
3. Typography
4. Visual (background, border, shadow)
5. Misc (cursor, pointer-events)

```css
.slos-element {
    /* 1. Display & positioning */
    display: flex;
    position: relative;
    z-index: 10;
    
    /* 2. Box model */
    width: 100%;
    padding: var(--slos-space-md);
    margin-bottom: var(--slos-space-lg);
    
    /* 3. Typography */
    font-size: var(--slos-text-base);
    color: var(--slos-text-primary);
    
    /* 4. Visual */
    background: var(--slos-bg-secondary);
    border: 1px solid var(--slos-border-default);
    border-radius: var(--slos-radius-md);
    box-shadow: var(--slos-shadow-md);
    
    /* 5. Misc */
    cursor: pointer;
    transition: all var(--slos-transition-base);
}
```

### PHP Coding Standards

**Follow WordPress Coding Standards:**

```php
// ✅ GOOD
public function enqueue_style( $handle, $src, $deps = [], $media = 'all' ) {
    $file_path = $this->plugin_path . $src;
    
    if ( $this->use_minified ) {
        $minified_path = str_replace( '.css', '.min.css', $file_path );
        if ( file_exists( $minified_path ) ) {
            $src = str_replace( '.css', '.min.css', $src );
        }
    }
    
    wp_enqueue_style(
        'slos-' . $handle,
        $this->plugin_url . $src,
        $deps,
        $this->get_asset_version( $file_path ),
        $media
    );
}

// ❌ BAD
public function enqueue_style($handle,$src,$deps=[],$media='all'){
$file_path=$this->plugin_path.$src;
if($this->use_minified){
$minified_path=str_replace('.css','.min.css',$file_path);
if(file_exists($minified_path)){
$src=str_replace('.css','.min.css',$src);
}
}
wp_enqueue_style('slos-'.$handle,$this->plugin_url.$src,$deps,$this->get_asset_version($file_path),$media);
}
```

**Key Rules:**
- Indentation: 4 spaces (or 1 tab)
- Spaces around operators: `$a = $b + $c` (not `$a=$b+$c`)
- Spaces after commas: `array( $a, $b, $c )`
- Braces on same line: `if ( $condition ) {`
- Always escape output: `echo esc_html( $text );`

### JavaScript Standards

**ES6+ with WordPress compatibility:**

```javascript
// ✅ GOOD: jQuery with namespace
(function( $ ) {
    'use strict';
    
    const SLOS = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            $( '.slos-modal-close' ).on( 'click', this.closeModal );
        },
        
        closeModal: function( e ) {
            e.preventDefault();
            $( '.slos-modal-overlay' ).removeClass( 'slos-show' );
        }
    };
    
    $( document ).ready( function() {
        SLOS.init();
    });
    
})( jQuery );
```

---

## Testing Guidelines

### Manual Testing Checklist

**Before committing code:**

- [ ] Visual test in Chrome
- [ ] Visual test in Firefox
- [ ] Visual test in Safari (if available)
- [ ] Visual test in Edge
- [ ] Mobile test (Chrome DevTools responsive mode)
- [ ] Keyboard navigation test (Tab, Enter, Esc)
- [ ] Screen reader test (NVDA on Windows / VoiceOver on Mac)
- [ ] Color contrast test (4.5:1 minimum)
- [ ] Zoom test (200% and 400%)
- [ ] Print test (if applicable)

### Automated Testing

**Run Lighthouse audit:**

```bash
# Chrome DevTools > Lighthouse
# Target scores:
# - Performance: 90+
# - Accessibility: 95+
# - Best Practices: 95+
# - SEO: 90+
```

**Run axe accessibility scan:**

```bash
npm install -g @axe-core/cli
axe http://localhost:8080/wp-admin/admin.php?page=slos-dashboard
```

**Run CSS linting:**

```bash
npm install -g stylelint stylelint-config-standard
stylelint "assets/css/**/*.css"
```

### Browser Compatibility Matrix

| Browser | Minimum Version | Test Priority |
|---------|----------------|---------------|
| Chrome | 90+ | ⭐⭐⭐ High |
| Firefox | 88+ | ⭐⭐⭐ High |
| Safari | 14+ | ⭐⭐ Medium |
| Edge | 90+ | ⭐⭐ Medium |
| Mobile Chrome | 90+ | ⭐⭐⭐ High |
| Mobile Safari | iOS 13+ | ⭐⭐ Medium |

---

## Performance Best Practices

### 1. Always Minify CSS

```powershell
# After editing CSS, immediately minify
$file = "assets/css/slos-components.css"
$content = Get-Content $file -Raw
$content = $content -replace '/\*[\s\S]*?\*/', ''
$content = $content -replace '\s+', ' '
$content = $content.Trim()
Set-Content ($file -replace '\.css$', '.min.css') $content -NoNewline
```

### 2. Use CSS Variables (Faster Than Repeated Values)

```css
/* ✅ GOOD: Variable reuse */
.card-a { background: var(--slos-bg-secondary); }
.card-b { background: var(--slos-bg-secondary); }
.card-c { background: var(--slos-bg-secondary); }

/* ❌ BAD: Repeated hardcoded value */
.card-a { background: #242b24; }
.card-b { background: #242b24; }
.card-c { background: #242b24; }
```

### 3. Avoid !important

```css
/* ❌ BAD: !important override */
.slos-card {
    background: red !important;
}

/* ✅ GOOD: Specificity or better selector */
.slos-dashboard .slos-card {
    background: red;
}
```

### 4. Optimize Animations (Use transform/opacity)

```css
/* ✅ GOOD: Hardware-accelerated */
.slos-card:hover {
    transform: translateY(-2px);  /* GPU-accelerated */
    opacity: 0.9;                 /* GPU-accelerated */
}

/* ❌ BAD: CPU-heavy repaints */
.slos-card:hover {
    top: -2px;        /* Causes reflow */
    width: 102%;      /* Causes reflow */
}
```

---

## Accessibility Requirements

### WCAG 2.1 Level AA Compliance

**All code must meet:**

1. **Color contrast:** 4.5:1 minimum (text), 3:1 (UI components)
2. **Keyboard navigation:** All interactive elements focusable
3. **Focus indicators:** Visible (3px olive outline)
4. **ARIA labels:** All icon buttons have labels
5. **Touch targets:** 44x44px minimum
6. **Screen reader support:** Proper semantic HTML + ARIA

### Accessibility Checklist

**Before committing:**

- [ ] All images have `alt` attributes
- [ ] Icon buttons have `aria-label` attributes
- [ ] Forms have `<label>` elements with `for` attributes
- [ ] Modals have `role="dialog"` and `aria-labelledby`
- [ ] Alerts have `role="alert"` or `aria-live="polite"`
- [ ] Heading hierarchy is logical (h1 → h2 → h3)
- [ ] Color is not the only indicator (use icons + text)
- [ ] Focus is visible on all interactive elements
- [ ] Keyboard navigation works (Tab, Enter, Esc)

**Example:**

```html
<!-- ✅ GOOD: Accessible alert -->
<div class="slos-alert slos-alert-success" role="alert" aria-live="polite">
    <svg aria-hidden="true"><!-- checkmark icon --></svg>
    <span>Settings saved successfully.</span>
</div>

<!-- ❌ BAD: Missing accessibility features -->
<div class="slos-alert slos-alert-success">
    <svg><!-- icon --></svg>
    <span>Settings saved successfully.</span>
</div>
```

---

## Git Workflow

### Branch Strategy

**Branches:**
- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Urgent production fixes

**Example:**

```bash
# Create feature branch
git checkout develop
git pull origin develop
git checkout -b feature/new-alert-component

# Make changes, commit
git add assets/css/slos-components.css
git commit -m "feat: Add alert component with success/error variants"

# Push and create pull request
git push origin feature/new-alert-component
```

### Commit Messages

**Format:** `<type>(<scope>): <subject>`

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - CSS/formatting changes (no logic change)
- `refactor` - Code refactoring
- `perf` - Performance improvement
- `test` - Adding tests
- `chore` - Build/tooling changes

**Examples:**

```bash
feat(components): Add alert component with dismiss button
fix(dashboard): Fix card hover state on mobile
docs(theme): Update component guide with alert examples
style(admin): Format CSS with consistent spacing
refactor(assets): Extract minification logic to helper
perf(css): Minify all CSS files (32% reduction)
test(a11y): Add keyboard navigation tests
chore(build): Update PowerShell minification script
```

---

## Common Tasks

### Task: Add New CSS Variable

**Step-by-step:**

1. **Add to design system:**

```css
/* assets/css/slos-design-system.css */
:root {
    /* New variable */
    --slos-my-new-color: #123456;
}
```

2. **Minify design system:**

```powershell
$file = "assets/css/slos-design-system.css"
# ... minification script ...
```

3. **Use in component:**

```css
.my-component {
    background: var(--slos-my-new-color);
}
```

4. **Document in design-system.md:**

```markdown
### My New Color

- `--slos-my-new-color: #123456` - Description here
```

### Task: Fix Color Contrast Issue

**Problem:** Text on background fails WCAG AA (contrast < 4.5:1)

**Solution:**

1. **Check current contrast:**

```javascript
// Chrome DevTools > Inspect element > Computed > Contrast ratio
// Should show: 4.52 ✓ (AA) or 2.8 ✗ (Fail)
```

2. **Adjust color:**

```css
/* Before (fails) */
.my-text {
    color: #94a3b8;  /* Light gray */
    background: #242b24;
    /* Contrast: 3.2:1 - FAILS */
}

/* After (passes) */
.my-text {
    color: #cbd5e1;  /* Brighter gray */
    background: #242b24;
    /* Contrast: 9.1:1 - PASSES AAA */
}
```

3. **Use design system colors:**

```css
.my-text {
    color: var(--slos-text-secondary);  /* Guaranteed 9.1:1 */
    background: var(--slos-bg-secondary);
}
```

### Task: Update Component Styling

**Example: Change button padding**

1. **Edit full CSS:**

```css
/* assets/css/slos-components.css */
.slos-btn-primary {
    padding: 14px 28px;  /* Changed from 12px 24px */
}
```

2. **Re-minify:**

```powershell
$file = "assets/css/slos-components.css"
$content = Get-Content $file -Raw
$content = $content -replace '/\*[\s\S]*?\*/', ''
$content = $content -replace '\s+', ' '
$content = $content.Trim()
Set-Content "assets/css/slos-components.min.css" $content -NoNewline
```

3. **Test in browser:**

- Clear cache (Ctrl+Shift+Del)
- Reload page
- Check button appearance
- Test hover/active states

4. **Commit:**

```bash
git add assets/css/slos-components.css assets/css/slos-components.min.css
git commit -m "style(components): Increase button padding for better touch targets"
```

---

## Resources

**Documentation:**
- [Design System Guide](design-system.md) - CSS variables, colors, typography
- [Component Guide](component-guide.md) - Component library reference
- [Accessibility Guide](accessibility.md) - WCAG compliance guide
- [Performance Guide](performance.md) - Optimization strategies
- [Migration Guide](migration-guide.md) - Upgrade from v3.1.0

**External Resources:**
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [MDN Web Docs](https://developer.mozilla.org/)
- [Can I Use](https://caniuse.com/) - Browser compatibility
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

**Tools:**
- [Lighthouse](https://developers.google.com/web/tools/lighthouse) - Performance audit
- [axe DevTools](https://www.deque.com/axe/devtools/) - Accessibility testing
- [WAVE](https://wave.webaim.org/) - Accessibility evaluation
- [Stylelint](https://stylelint.io/) - CSS linting

---

**Last Updated:** January 2026  
**Maintainer:** ShahiLegalFlowSuite Development Team
