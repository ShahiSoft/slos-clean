# Auto-Fixers by Category

## Overview

The Accessibility Scanner includes 96 automated fixers organized into 10 categories, providing comprehensive WCAG 2.1 AA compliance coverage.

## 1. Focus Management (8 Fixers)

Ensures proper keyboard navigation and visual focus indicators.

### Focus Indicator Fixer
**Purpose:** Adds visible focus indicators to interactive elements
**WCAG:** 2.4.7 Focus Visible
**Implementation:** Adds CSS outline or border to focused elements
**Elements:** Links, buttons, form controls, custom widgets

### Focus Trap Fixer
**Purpose:** Manages focus within modal dialogs and overlays
**WCAG:** 2.4.3 Focus Order
**Implementation:** Prevents focus from escaping modal boundaries
**Elements:** Modal dialogs, popups, lightboxes

### Focus Restoration Fixer
**Purpose:** Returns focus to trigger element after modal closes
**WCAG:** 2.4.3 Focus Order
**Implementation:** Saves and restores focus position
**Elements:** Modal triggers, expandable content

### Focus Order Fixer
**Purpose:** Corrects illogical tab order
**WCAG:** 2.4.3 Focus Order
**Implementation:** Adjusts tabindex attributes
**Elements:** Complex layouts, custom components

### Focus Visible Fixer
**Purpose:** Adds :focus-visible support for modern browsers
**WCAG:** 2.4.7 Focus Visible
**Implementation:** Polyfills focus-visible behavior
**Elements:** All focusable elements

### Keyboard Navigation Fixer
**Purpose:** Ensures all functionality is keyboard accessible
**WCAG:** 2.1.1 Keyboard
**Implementation:** Adds keyboard event handlers
**Elements:** Custom interactive components

### Keyboard Trap Escape Fixer
**Purpose:** Provides escape key functionality for focus traps
**WCAG:** 2.1.2 No Keyboard Trap
**Implementation:** Adds Escape key event listeners
**Elements:** Modals, menus, custom overlays

### Focus Management Events Fixer
**Purpose:** Adds proper focus management for dynamic content
**WCAG:** 2.4.3 Focus Order
**Implementation:** Manages focus for AJAX content
**Elements:** Dynamic content areas

## 2. Vision & Color (12 Fixers)

Improves visual accessibility and removes color dependencies.

### Color Contrast Fixer
**Purpose:** Ensures sufficient color contrast ratios
**WCAG:** 1.4.3 Contrast (Minimum), 1.4.6 Contrast (Enhanced)
**Implementation:** Adjusts text/background colors
**Elements:** Text elements, UI components

### Color Reliance Fixer
**Purpose:** Adds icons/text to color-only information
**WCAG:** 1.4.1 Use of Color
**Implementation:** Adds visual indicators alongside colors
**Elements:** Status indicators, form validation, charts

### Link Underline Fixer
**Purpose:** Underlines links for better visibility
**WCAG:** 1.4.1 Use of Color
**Implementation:** Adds text-decoration: underline
**Elements:** Text links, navigation links

### Text Spacing Fixer
**Purpose:** Ensures adequate text spacing
**WCAG:** 1.4.12 Text Spacing
**Implementation:** Adds CSS letter-spacing, word-spacing, line-height
**Elements:** Body text, headings, UI text

### Visual Focus Fixer
**Purpose:** Enhances focus indicators for low vision users
**WCAG:** 2.4.7 Focus Visible
**Implementation:** Adds high-contrast focus styles
**Elements:** All focusable elements

### Color Coding Fixer
**Purpose:** Removes color-only coding in information
**WCAG:** 1.4.1 Use of Color
**Implementation:** Adds text labels or patterns
**Elements:** Data tables, status messages, legends

### Blur Effect Fixer
**Purpose:** Removes or reduces problematic blur effects
**WCAG:** 1.4.8 Visual Presentation
**Implementation:** Adjusts CSS filter: blur values
**Elements:** Background images, overlay effects

### Icon Visibility Fixer
**Purpose:** Ensures icons have sufficient contrast
**WCAG:** 1.4.11 Non-text Contrast
**Implementation:** Adjusts icon colors or adds backgrounds
**Elements:** UI icons, decorative images

### Animation Pause Fixer
**Purpose:** Adds controls for animations over 5 seconds
**WCAG:** 2.2.2 Pause, Stop, Hide
**Implementation:** Adds pause/play controls
**Elements:** Carousels, sliders, animated content

### Flashing Content Fixer
**Purpose:** Prevents content flashing more than 3 times/second
**WCAG:** 2.3.1 Three Flashes or Below Threshold
**Implementation:** Removes or slows flashing content
**Elements:** Animated graphics, video content

### Opacity Fixer
**Purpose:** Improves opacity contrast for text
**WCAG:** 1.4.11 Non-text Contrast
**Implementation:** Adjusts background opacity
**Elements:** Semi-transparent overlays, badges

### Background Contrast Fixer
**Purpose:** Ensures background contrast meets requirements
**WCAG:** 1.4.11 Non-text Contrast
**Implementation:** Adjusts background colors
**Elements:** UI components, form elements

## 3. Touch & Motor (8 Fixers)

Makes interactive elements accessible to users with motor disabilities.

### Touch Target Fixer
**Purpose:** Ensures touch targets are at least 44x44px
**WCAG:** 2.5.5 Target Size
**Implementation:** Increases clickable area sizes
**Elements:** Buttons, links, form controls

### Button Size Fixer
**Purpose:** Ensures buttons meet minimum size requirements
**WCAG:** 2.5.5 Target Size
**Implementation:** Adjusts button dimensions
**Elements:** Submit buttons, action buttons

### Click Area Fixer
**Purpose:** Expands clickable areas for better accessibility
**WCAG:** 2.5.5 Target Size
**Implementation:** Adds padding or pseudo-elements
**Elements:** Small links, icon buttons

### Spacing Fixer
**Purpose:** Adds adequate spacing between interactive elements
**WCAG:** 2.4.1 Bypass Blocks
**Implementation:** Adjusts margins and padding
**Elements:** Navigation menus, button groups

### Motion Activation Fixer
**Purpose:** Provides alternatives to motion-activated content
**WCAG:** 2.5.4 Motion Actuation
**Implementation:** Adds manual controls
**Elements:** Motion sensors, gesture controls

### Gesture Alternative Fixer
**Purpose:** Provides keyboard alternatives to gestures
**WCAG:** 2.5.1 Pointer Gestures
**Implementation:** Adds keyboard shortcuts
**Elements:** Swipe gestures, multi-touch actions

### Pointer Target Fixer
**Purpose:** Ensures pointer targets meet size requirements
**WCAG:** 2.5.8 Target Size (Minimum)
**Implementation:** Adjusts target sizes
**Elements:** Hover menus, dropdown items

### Draggable Alternative Fixer
**Purpose:** Provides alternatives to drag operations
**WCAG:** 2.5.7 Dragging Movements
**Implementation:** Adds button-based alternatives
**Elements:** Drag-and-drop interfaces, sliders

## 4. Language & Structure (12 Fixers)

Adds semantic structure and proper language identification.

### Language Change Fixer
**Purpose:** Identifies text in different languages
**WCAG:** 3.1.2 Language of Parts
**Implementation:** Adds lang attributes
**Elements:** Foreign language text, quotes

### Page Structure Fixer
**Purpose:** Adds semantic landmarks to page structure
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds header, nav, main, aside, footer
**Elements:** Page layouts, content sections

### Heading Structure Fixer
**Purpose:** Corrects heading hierarchy (h1-h6)
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adjusts heading levels
**Elements:** Content headings, section headers

### List Structure Fixer
**Purpose:** Converts improper lists to semantic lists
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds ul, ol, dl elements
**Elements:** Navigation menus, feature lists

### Table Header Fixer
**Purpose:** Adds proper table headers
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds th elements and scope attributes
**Elements:** Data tables, comparison tables

### Table Structure Fixer
**Purpose:** Corrects table markup and relationships
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds thead, tbody, caption
**Elements:** Complex tables, data grids

### Landmark Fixer
**Purpose:** Adds ARIA landmark roles
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds role="banner", "navigation", etc.
**Elements:** Site sections, content areas

### Skip Link Fixer
**Purpose:** Adds skip navigation links
**WCAG:** 2.4.1 Bypass Blocks
**Implementation:** Adds "Skip to main content" links
**Elements:** Page templates, themes

### Frame Title Fixer
**Purpose:** Adds descriptive titles to iframes
**WCAG:** 2.4.1 Bypass Blocks
**Implementation:** Adds title attributes
**Elements:** Embedded content, video iframes

### Form Label Fixer
**Purpose:** Associates labels with form controls
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds label elements and for/id attributes
**Elements:** Input fields, select elements

### Section Heading Fixer
**Purpose:** Adds headings to content sections
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds h2-h6 elements
**Elements:** Content sections, widget areas

### Content Structure Fixer
**Purpose:** Organizes content with proper semantic structure
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds article, section, div with roles
**Elements:** Blog posts, content blocks

## 5. ARIA & Roles (14 Fixers)

Implements proper ARIA attributes and semantic roles.

### Aria State Fixer
**Purpose:** Adds aria-pressed, aria-selected, aria-expanded
**WCAG:** 4.1.2 Name, Role, Value
**Implementation:** Adds state attributes to interactive elements
**Elements:** Toggle buttons, tabs, accordions

### Aria Live Fixer
**Purpose:** Adds aria-live regions for dynamic content
**WCAG:** 4.1.3 Status Messages
**Implementation:** Adds aria-live="polite" or "assertive"
**Elements:** Status messages, notifications

### Aria Label Fixer
**Purpose:** Adds aria-label for unlabeled elements
**WCAG:** 2.4.6 Headings and Labels
**Implementation:** Adds descriptive labels
**Elements:** Icon buttons, custom controls

### Role Fixer
**Purpose:** Applies correct ARIA roles
**WCAG:** 4.1.2 Name, Role, Value
**Implementation:** Adds role attributes
**Elements:** Custom widgets, navigation

### Aria Expanded Fixer
**Purpose:** Adds aria-expanded to expandable elements
**WCAG:** 4.1.2 Name, Role, Value
**Implementation:** Manages expanded/collapsed states
**Elements:** Accordions, menus, disclosures

### Aria Hidden Fixer
**Purpose:** Manages aria-hidden for screen readers
**WCAG:** 4.1.2 Name, Role, Value
**Implementation:** Hides decorative/offscreen content
**Elements:** Hidden content, overlays

### Aria Invalid Fixer
**Purpose:** Adds aria-invalid for form validation
**WCAG:** 3.3.1 Error Identification
**Implementation:** Marks invalid form fields
**Elements:** Form inputs with validation errors

### Aria Disabled Fixer
**Purpose:** Marks disabled elements for screen readers
**WCAG:** 4.1.2 Name, Role, Value
**Implementation:** Adds aria-disabled="true"
**Elements:** Disabled form controls, buttons

### Aria Required Fixer
**Purpose:** Marks required form fields
**WCAG:** 3.3.2 Labels or Instructions
**Implementation:** Adds aria-required="true"
**Elements:** Required input fields

### Status Message Fixer
**Purpose:** Provides status messages for screen readers
**WCAG:** 4.1.3 Status Messages
**Implementation:** Adds aria-live status regions
**Elements:** Form submissions, loading states

### Error Identification Fixer
**Purpose:** Associates errors with form fields
**WCAG:** 3.3.1 Error Identification
**Implementation:** Links errors to inputs via aria-describedby
**Elements:** Form validation errors

### Alert Role Fixer
**Purpose:** Adds alert semantics for important messages
**WCAG:** 4.1.3 Status Messages
**Implementation:** Adds role="alert"
**Elements:** Error messages, success notifications

### Description Fixer
**Purpose:** Adds aria-describedby for additional information
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Links descriptions to elements
**Elements:** Complex form fields, instructions

### Tooltip Fixer
**Purpose:** Makes tooltips accessible to screen readers
**WCAG:** 4.1.2 Name, Role, Value
**Implementation:** Adds aria-describedby for tooltips
**Elements:** Help text, additional information

## 6. Forms & Input (10 Fixers)

Makes form elements accessible with proper labels and instructions.

### Form Label Fixer
**Purpose:** Associates labels with form controls
**WCAG:** 2.4.6 Headings and Labels
**Implementation:** Adds label elements
**Elements:** Text inputs, checkboxes, radio buttons

### Placeholder Fixer
**Purpose:** Replaces placeholder-only labels
**WCAG:** 3.3.2 Labels or Instructions
**Implementation:** Adds visible labels
**Elements:** Input fields with placeholder text

### Input Type Fixer
**Purpose:** Sets correct input types
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Changes type attributes
**Elements:** Email inputs, phone inputs, URLs

### Error Message Fixer
**Purpose:** Associates error messages with fields
**WCAG:** 3.3.1 Error Identification
**Implementation:** Adds aria-describedby
**Elements:** Form validation errors

### Required Field Fixer
**Purpose:** Marks required fields visually and semantically
**WCAG:** 3.3.2 Labels or Instructions
**Implementation:** Adds required indicators and aria-required
**Elements:** Mandatory form fields

### Form Instructions Fixer
**Purpose:** Adds form instructions and help text
**WCAG:** 3.3.2 Labels or Instructions
**Implementation:** Adds field descriptions
**Elements:** Complex form fields

### Input Help Text Fixer
**Purpose:** Associates help text with inputs
**WCAG:** 3.3.2 Labels or Instructions
**Implementation:** Links help text via aria-describedby
**Elements:** Form fields with additional guidance

### Form Grouping Fixer
**Purpose:** Groups related form fields
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds fieldset and legend
**Elements:** Radio button groups, related inputs

### Form Submit Button Fixer
**Purpose:** Labels submit buttons descriptively
**WCAG:** 2.4.6 Headings and Labels
**Implementation:** Adds descriptive button text
**Elements:** Form submit buttons

### Form Reset Prevention Fixer
**Purpose:** Prevents accidental form resets
**WCAG:** 3.3.4 Error Prevention (Legal, Financial, Data)
**Implementation:** Adds confirmation dialogs
**Elements:** Form reset buttons

## 7. Media & Alternative Text (10 Fixers)

Adds captions, transcripts, and alternative text for media.

### Alt Text Fixer
**Purpose:** Adds alternative text to images
**WCAG:** 1.1.1 Non-text Content
**Implementation:** Adds alt attributes
**Elements:** Content images, decorative images

### Video Caption Fixer
**Purpose:** Adds captions to videos
**WCAG:** 1.2.2 Captions (Prerecorded)
**Implementation:** Adds track elements or overlays
**Elements:** Video elements, embedded videos

### Audio Transcript Fixer
**Purpose:** Provides transcripts for audio content
**WCAG:** 1.2.1 Audio-only and Video-only (Prerecorded)
**Implementation:** Adds transcript links or text
**Elements:** Audio files, podcasts

### Image Description Fixer
**Purpose:** Adds extended descriptions for complex images
**WCAG:** 1.1.1 Non-text Content
**Implementation:** Adds aria-describedby with long descriptions
**Elements:** Charts, diagrams, complex graphics

### Decorative Image Fixer
**Purpose:** Hides decorative images from screen readers
**WCAG:** 1.1.1 Non-text Content
**Implementation:** Adds alt="" or aria-hidden
**Elements:** Background images, decorative icons

### SVG Alternative Fixer
**Purpose:** Adds descriptions to SVG graphics
**WCAG:** 1.1.1 Non-text Content
**Implementation:** Adds title and desc elements
**Elements:** SVG icons, illustrations

### Canvas Alternative Fixer
**Purpose:** Provides text alternatives for canvas content
**WCAG:** 1.1.1 Non-text Content
**Implementation:** Adds fallback content
**Elements:** Canvas elements, dynamic graphics

### Media Controller Fixer
**Purpose:** Ensures media controls are accessible
**WCAG:** 2.1.1 Keyboard
**Implementation:** Adds keyboard support to custom controls
**Elements:** Custom media players

### Audio Description Fixer
**Purpose:** Adds audio descriptions for video content
**WCAG:** 1.2.5 Audio Description (Prerecorded)
**Implementation:** Provides description tracks
**Elements:** Training videos, complex content

### Sign Language Fixer
**Purpose:** Provides sign language interpretation
**WCAG:** 1.2.6 Sign Language (Prerecorded)
**Implementation:** Adds sign language video tracks
**Elements:** Educational content, important videos

## 8. Timing & Animation (6 Fixers)

Controls animations and provides timing alternatives.

### Animation Control Fixer
**Purpose:** Adds controls for animations
**WCAG:** 2.2.2 Pause, Stop, Hide
**Implementation:** Adds pause/play buttons
**Elements:** Animated content, carousels

### Timing Adjustable Fixer
**Purpose:** Allows users to adjust time limits
**WCAG:** 2.2.1 Timing Adjustable
**Implementation:** Adds time extension options
**Elements:** Forms with timeouts, quizzes

### Auto Update Pause Fixer
**Purpose:** Allows users to pause auto-updating content
**WCAG:** 2.2.2 Pause, Stop, Hide
**Implementation:** Adds pause controls
**Elements:** Live feeds, auto-refresh content

### Moving Content Control Fixer
**Purpose:** Allows users to pause moving content
**WCAG:** 2.2.2 Pause, Stop, Hide
**Implementation:** Adds pause buttons
**Elements:** Marquees, scrolling text

### Session Timeout Warning Fixer
**Purpose:** Warns users before session timeout
**WCAG:** 2.2.1 Timing Adjustable
**Implementation:** Adds timeout warnings
**Elements:** Login sessions, shopping carts

### Focus Timeout Fixer
**Purpose:** Prevents focus timeouts
**WCAG:** 2.2.1 Timing Adjustable
**Implementation:** Extends focus-based timeouts
**Elements:** Keyboard navigation timeouts

## 9. Navigation & Structure (4 Fixers)

Improves site navigation and page structure.

### Navigation Landmark Fixer
**Purpose:** Adds navigation landmarks
**WCAG:** 1.3.1 Info and Relationships
**Implementation:** Adds role="navigation"
**Elements:** Site navigation, menus

### Breadcrumb Fixer
**Purpose:** Adds breadcrumb navigation
**WCAG:** 2.4.8 Location
**Implementation:** Adds breadcrumb trails
**Elements:** Multi-level pages, content hierarchies

### Page Title Fixer
**Purpose:** Ensures descriptive page titles
**WCAG:** 2.4.2 Page Titled
**Implementation:** Improves title elements
**Elements:** Page head titles

### Consistent Navigation Fixer
**Purpose:** Ensures consistent navigation across pages
**WCAG:** 3.2.3 Consistent Navigation
**Implementation:** Standardizes navigation patterns
**Elements:** Site-wide navigation

## 10. Error Prevention (2 Fixers)

Prevents common user errors and provides clear feedback.

### Error Suggestion Fixer
**Purpose:** Provides suggestions for error correction
**WCAG:** 3.3.3 Error Suggestion
**Implementation:** Adds helpful error messages
**Elements:** Form validation, input errors

### Error Prevention Fixer
**Purpose:** Prevents errors through design
**WCAG:** 3.3.4 Error Prevention (All)
**Implementation:** Adds confirmation steps
**Elements:** Data deletion, form submissions

## Implementation Notes

### Fixer Priority

Fixers are applied in order of impact and safety:
1. **High Priority:** Critical accessibility issues
2. **Medium Priority:** Important usability improvements
3. **Low Priority:** Minor enhancements

### Safety Checks

Each fixer includes:
- **Validation:** Ensures fixes don't break functionality
- **Rollback:** Ability to undo changes
- **Testing:** Automated validation of fixes
- **Performance:** Minimal impact on page loading

### Customization

**Selective Application:**
- Enable/disable specific fixers
- Configure fixer behavior
- Set custom rules
- Exclude elements

**Advanced Options:**
- Custom CSS injection
- JavaScript enhancements
- Attribute modifications
- Content adjustments

## Related Documentation

- [Accessibility Scanner Overview](overview.md)
- [Scanning Process](scanning-process.md)
- [Configuration Guide](configuration.md)
- [Run Accessibility Scans](../../../How-tos/03-run-accessibility-scan.md)