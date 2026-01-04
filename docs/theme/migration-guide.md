# SLOS Migration Guide: Glassmorphism to Olive Theme

**From Version:** 3.1.0 (Blue Glassmorphism)  
**To Version:** 3.1.1+ (Dark Olive Corporate)  
**Migration Date:** January 2026

---

## Overview

This guide helps you migrate from the old glassmorphism theme (blue, translucent, blur effects) to the new dark olive corporate theme (solid colors, proper shadows, professional design).

**Why Migrate:**
- ❌ **Glassmorphism Removed:** Poor accessibility, browser compatibility issues
- ❌ **Blur Effects Removed:** Performance issues, Safari bugs
- ❌ **Translucent Backgrounds Removed:** Text contrast failures (WCAG)
- ✅ **Solid Colors Added:** Better accessibility (WCAG 2.1 AA compliant)
- ✅ **Olive Theme Added:** Professional corporate appearance
- ✅ **Proper Shadows Added:** Better depth perception

---

## Breaking Changes

### 1. CSS Custom Properties Renamed

**Old (v3.1.0):**
```css
:root {
    --shahi-primary: #3b82f6;          /* Blue */
    --shahi-bg-glass: rgba(255, 255, 255, 0.1);
    --shahi-bg-card: rgba(255, 255, 255, 0.15);
    --shahi-text: #e5e7eb;
}
```

**New (v3.1.1+):**
```css
:root {
    --slos-accent-primary: #6b8e4e;    /* Olive green */
    --slos-bg-primary: #1a1f1a;        /* Dark olive */
    --slos-bg-secondary: #242b24;      /* Card background (solid) */
    --slos-text-primary: #f8fafc;      /* White text */
}
```

**Migration:**

| Old Variable | New Variable | Notes |
|--------------|--------------|-------|
| `--shahi-primary` | `--slos-accent-primary` | Color changed: Blue → Olive |
| `--shahi-bg-glass` | `--slos-bg-secondary` | Now solid (not translucent) |
| `--shahi-bg-card` | `--slos-bg-secondary` | Same as above |
| `--shahi-text` | `--slos-text-primary` | Slightly brighter for contrast |
| `--shahi-text-dim` | `--slos-text-secondary` | Renamed |

### 2. CSS Classes Renamed

**Card Classes:**

| Old Class | New Class | Changes |
|-----------|-----------|---------|
| `.shahi-card-glass` | `.slos-card` | Removed blur, added solid bg |
| `.shahi-card-elevated` | `.slos-card-elevated` | Same name, different styles |
| `.shahi-stat-card` | `.slos-stat-card` | Prefix changed |

**Button Classes:**

| Old Class | New Class | Changes |
|-----------|-----------|---------|
| `.shahi-btn-primary` | `.slos-btn-primary` | Blue → Olive gradient |
| `.shahi-btn-glass` | `.slos-btn-secondary` | Removed glass effect |
| `.shahi-btn-blue` | `.slos-btn-primary` | Renamed |

**Form Classes:**

| Old Class | New Class | Changes |
|-----------|-----------|---------|
| `.shahi-input-glass` | `.slos-input` | Removed glass effect |
| `.shahi-textarea` | `.slos-textarea` | Prefix changed |
| `.shahi-select` | `.slos-select` | Prefix changed |

### 3. Removed CSS Properties

**Glassmorphism Effects (REMOVED):**

```css
/* ❌ DO NOT USE - These no longer exist */
.old-card {
    backdrop-filter: blur(10px);                    /* REMOVED */
    -webkit-backdrop-filter: blur(10px);            /* REMOVED */
    background: rgba(255, 255, 255, 0.1);          /* REMOVED */
    background: rgba(59, 130, 246, 0.2);           /* REMOVED (blue) */
}
```

**New Solid Styles:**

```css
/* ✅ USE THESE INSTEAD */
.new-card {
    background: var(--slos-bg-secondary);          /* Solid #242b24 */
    border: 1px solid var(--slos-border-default);  /* Olive border */
    box-shadow: var(--slos-shadow-md);             /* Proper shadow */
}
```

---

## Step-by-Step Migration

### Step 1: Update CSS File Imports

**Old:**
```php
wp_enqueue_style( 'shahi-admin', 'assets/css/admin-dashboard.css' );
wp_enqueue_style( 'shahi-components', 'assets/css/components.css' );
```

**New:**
```php
// Load design system FIRST (required for CSS variables)
wp_enqueue_style( 'slos-design-system', 'assets/css/slos-design-system.css' );

// Then load components (depends on design system)
wp_enqueue_style( 'slos-components', 'assets/css/slos-components.css', ['slos-design-system'] );

// Then load page-specific CSS
wp_enqueue_style( 'slos-admin-dashboard', 'assets/css/admin-dashboard.css', ['slos-design-system'] );
```

### Step 2: Replace HTML Class Names

**Old HTML:**
```html
<div class="shahi-card-glass">
    <div class="shahi-card-header">
        <h3 class="shahi-card-title">Card Title</h3>
    </div>
    <div class="shahi-card-body">
        Content here
    </div>
</div>
```

**New HTML:**
```html
<div class="slos-card">
    <div class="slos-card-header">
        <h3 class="slos-card-title">Card Title</h3>
    </div>
    <div class="slos-card-body">
        Content here
    </div>
</div>
```

**Search & Replace Commands:**

```bash
# Find and replace in all PHP/HTML files
find . -type f \( -name "*.php" -o -name "*.html" \) -exec sed -i 's/shahi-card-glass/slos-card/g' {} +
find . -type f \( -name "*.php" -o -name "*.html" \) -exec sed -i 's/shahi-btn-primary/slos-btn-primary/g' {} +
find . -type f \( -name "*.php" -o -name "*.html" \) -exec sed -i 's/shahi-input-glass/slos-input/g' {} +
```

### Step 3: Update CSS Variable References

**Old Custom CSS:**
```css
.my-custom-component {
    background: var(--shahi-bg-glass);
    color: var(--shahi-text);
    border: 1px solid var(--shahi-primary);
}
```

**New Custom CSS:**
```css
.my-custom-component {
    background: var(--slos-bg-secondary);
    color: var(--slos-text-primary);
    border: 1px solid var(--slos-accent-primary);
}
```

### Step 4: Remove Backdrop Filters

**Old CSS:**
```css
.my-modal {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    background: rgba(0, 0, 0, 0.5);
}
```

**New CSS:**
```css
.my-modal {
    /* Remove backdrop-filter completely */
    background: rgba(0, 0, 0, 0.85);  /* Solid overlay */
}
```

### Step 5: Update Color Values

**Old Color Scheme:**
- Primary: `#3b82f6` (Blue)
- Accent: `#60a5fa` (Light blue)
- Background: `rgba(255, 255, 255, 0.1)` (Translucent white)

**New Color Scheme:**
- Primary: `#6b8e4e` (Olive green)
- Accent: `#7a9d5d` (Light olive)
- Background: `#242b24` (Dark olive solid)

**CSS Migration:**

```css
/* Before */
.old-button {
    background: #3b82f6;
    border: 1px solid #60a5fa;
}

/* After */
.new-button {
    background: var(--slos-accent-primary);  /* #6b8e4e */
    border: 1px solid var(--slos-border-default);
}
```

---

## Common Migration Issues

### Issue 1: Translucent Backgrounds Look Different

**Problem:**
Old glassmorphism had translucent backgrounds (rgba with alpha channel).

**Old:**
```css
.card {
    background: rgba(255, 255, 255, 0.1);
}
```

**Solution:**
Use solid colors from design system.

**New:**
```css
.card {
    background: var(--slos-bg-secondary);  /* Solid #242b24 */
}
```

### Issue 2: Blur Effects No Longer Work

**Problem:**
`backdrop-filter: blur(10px)` removed for performance and accessibility.

**Old:**
```css
.modal-overlay {
    backdrop-filter: blur(10px);
}
```

**Solution:**
Use solid overlay with higher opacity.

**New:**
```css
.modal-overlay {
    background: rgba(0, 0, 0, 0.85);  /* No blur, solid overlay */
}
```

### Issue 3: Blue Color Theme Persists

**Problem:**
Hardcoded blue colors `#3b82f6` in custom CSS.

**Old:**
```css
.my-button {
    background: #3b82f6;  /* Hardcoded blue */
}
```

**Solution:**
Use CSS variables.

**New:**
```css
.my-button {
    background: var(--slos-accent-primary);  /* Olive, themeable */
}
```

### Issue 4: Contrast Ratios Fail WCAG

**Problem:**
Old theme had low contrast ratios (e.g., light blue text on translucent background).

**Old:**
```css
.text {
    color: #93c5fd;  /* Light blue */
    background: rgba(255, 255, 255, 0.1);  /* Translucent */
    /* Contrast ratio: 2.8:1 - FAILS WCAG */
}
```

**Solution:**
Use design system colors with guaranteed contrast.

**New:**
```css
.text {
    color: var(--slos-text-primary);    /* #f8fafc */
    background: var(--slos-bg-secondary);  /* #242b24 */
    /* Contrast ratio: 12.8:1 - PASSES WCAG AAA */
}
```

---

## Component Migration Examples

### Card Component

**Before (v3.1.0):**
```html
<div class="shahi-card-glass" style="
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(59, 130, 246, 0.3);
">
    <h3 style="color: #e5e7eb;">Title</h3>
    <p style="color: #9ca3af;">Content</p>
</div>
```

**After (v3.1.1+):**
```html
<div class="slos-card">
    <div class="slos-card-header">
        <h3 class="slos-card-title">Title</h3>
    </div>
    <div class="slos-card-body">
        <p>Content</p>
    </div>
</div>
```

### Button Component

**Before:**
```html
<button class="shahi-btn-primary" style="
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    backdrop-filter: blur(5px);
">
    Click Me
</button>
```

**After:**
```html
<button class="slos-btn-primary">
    Click Me
</button>
```

### Modal Component

**Before:**
```html
<div class="shahi-modal-overlay" style="
    backdrop-filter: blur(10px);
    background: rgba(0, 0, 0, 0.5);
">
    <div class="shahi-modal-glass" style="
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.3);
    ">
        Modal content
    </div>
</div>
```

**After:**
```html
<div class="slos-modal-overlay">
    <div class="slos-modal">
        <div class="slos-modal-header">
            <h3 class="slos-modal-title">Modal Title</h3>
            <button class="slos-modal-close">&times;</button>
        </div>
        <div class="slos-modal-body">
            Modal content
        </div>
    </div>
</div>
```

---

## Automated Migration Script

### PowerShell Script

```powershell
# Migrate class names in all PHP files
$files = Get-ChildItem -Path "." -Filter "*.php" -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Replace CSS classes
    $content = $content -replace 'shahi-card-glass', 'slos-card'
    $content = $content -replace 'shahi-btn-primary', 'slos-btn-primary'
    $content = $content -replace 'shahi-btn-glass', 'slos-btn-secondary'
    $content = $content -replace 'shahi-input-glass', 'slos-input'
    $content = $content -replace 'shahi-modal-glass', 'slos-modal'
    
    # Replace CSS variables
    $content = $content -replace '--shahi-primary', '--slos-accent-primary'
    $content = $content -replace '--shahi-bg-glass', '--slos-bg-secondary'
    $content = $content -replace '--shahi-bg-card', '--slos-bg-secondary'
    $content = $content -replace '--shahi-text', '--slos-text-primary'
    
    Set-Content $file.FullName $content -NoNewline
    
    Write-Host "Migrated: $($file.Name)"
}

Write-Host "Migration complete!"
```

### Bash Script (Linux/Mac)

```bash
#!/bin/bash

# Migrate class names in all PHP files
find . -type f -name "*.php" | while read file; do
    # Replace CSS classes
    sed -i 's/shahi-card-glass/slos-card/g' "$file"
    sed -i 's/shahi-btn-primary/slos-btn-primary/g' "$file"
    sed -i 's/shahi-btn-glass/slos-btn-secondary/g' "$file"
    sed -i 's/shahi-input-glass/slos-input/g' "$file"
    sed -i 's/shahi-modal-glass/slos-modal/g' "$file"
    
    # Replace CSS variables
    sed -i 's/--shahi-primary/--slos-accent-primary/g' "$file"
    sed -i 's/--shahi-bg-glass/--slos-bg-secondary/g' "$file"
    sed -i 's/--shahi-bg-card/--slos-bg-secondary/g' "$file"
    sed -i 's/--shahi-text/--slos-text-primary/g' "$file"
    
    echo "Migrated: $file"
done

echo "Migration complete!"
```

---

## Testing After Migration

### Visual Testing

1. **Load each admin page:**
   - Dashboard
   - Modules
   - Settings
   - Accessibility Scanner
   - Document Hub

2. **Check for:**
   - ✅ No blue colors (should be olive green)
   - ✅ No translucent backgrounds (should be solid)
   - ✅ No blur effects
   - ✅ Proper shadows on cards/buttons
   - ✅ Consistent spacing

### Browser DevTools Testing

1. **Open Chrome DevTools (F12)**
2. **Check Console for errors:**
   - No "CSS variable not defined" errors
   - No "property not supported" warnings
3. **Check Network tab:**
   - All CSS files load successfully
   - Minified versions load in production

### Accessibility Testing

1. **Run axe DevTools:**
   ```bash
   npm install -g @axe-core/cli
   axe http://localhost:8080/wp-admin/admin.php?page=slos-dashboard
   ```

2. **Check color contrast:**
   - All text should meet WCAG 2.1 AA (4.5:1 minimum)
   - Use Chrome DevTools Lighthouse audit

3. **Keyboard navigation:**
   - Tab through all interactive elements
   - Focus indicators should be visible (olive outline)

---

## Rollback Procedure

If migration fails, you can rollback to v3.1.0:

### 1. Restore Old CSS Files

```bash
# Git rollback
git checkout v3.1.0 -- assets/css/

# Or restore from backup
cp -r backup/assets/css/ assets/css/
```

### 2. Revert Class Names

```powershell
# Reverse the migration script
$files = Get-ChildItem -Path "." -Filter "*.php" -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    $content = $content -replace 'slos-card', 'shahi-card-glass'
    $content = $content -replace 'slos-btn-primary', 'shahi-btn-primary'
    $content = $content -replace '--slos-accent-primary', '--shahi-primary'
    
    Set-Content $file.FullName $content -NoNewline
}
```

### 3. Clear Caches

```bash
# Clear WordPress object cache
wp cache flush

# Clear browser cache
# Chrome: Ctrl+Shift+Del → Clear browsing data
```

---

## FAQ

**Q: Can I use both old and new themes at the same time?**  
A: No. The old glassmorphism CSS has been removed. You must fully migrate to the new olive theme.

**Q: Will my custom CSS break?**  
A: If your custom CSS uses old class names (`shahi-*`) or old CSS variables (`--shahi-*`), it will break. Update to new class names (`slos-*`) and variables (`--slos-*`).

**Q: Can I keep the blue color scheme?**  
A: No. The blue color scheme has been replaced with olive green for better accessibility and corporate branding.

**Q: Do I need to update my templates?**  
A: Yes, if your templates use old class names. Run the migration script to automatically update all templates.

**Q: What if I find a bug after migrating?**  
A: Report it on GitHub or use the rollback procedure to revert to v3.1.0.

---

## Support

**Documentation:**
- [Design System Guide](design-system.md)
- [Component Guide](component-guide.md)
- [Accessibility Guide](accessibility.md)

**Contact:**
- GitHub Issues: [Report a bug](https://github.com/shahi/legalops-suite/issues)
- Email: support@shahilegalflowsuite.com

---

**Last Updated:** January 2026  
**Migration Status:** ✅ Complete
