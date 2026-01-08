# Legal Document Shortcodes

This document explains how to use the `[slos_legal_doc]` shortcode to display legal documents on your WordPress site.

## Overview

The `[slos_legal_doc]` shortcode allows you to embed generated legal documents directly into your WordPress pages and posts. This is useful for creating dedicated legal pages like Privacy Policy, Terms of Service, Cookie Policy, etc.

## Prerequisites

Before using the shortcode:

1. **Generate the document** in the Legal Documents Hub (Compliance > Legal Documents)
2. **Publish the document** - Only published documents will display via shortcode
3. **Ensure the document is active** - Only the 5 active document types can be displayed:
   - `privacy-policy`
   - `terms-of-service`
   - `cookie-policy`
   - `refund-policy`
   - `shipping-policy`

## Basic Usage

```
[slos_legal_doc type="privacy-policy"]
```

This will display:
- Document title
- Version number and last updated date
- Full document content with proper formatting

## Shortcode Attributes

| Attribute | Default | Options | Description |
|-----------|---------|---------|-------------|
| `type` | *(required)* | `privacy-policy`, `terms-of-service`, `cookie-policy`, `refund-policy`, `shipping-policy` | The document type to display |
| `title` | `yes` | `yes`, `no` | Show/hide the document title |
| `version` | `yes` | `yes`, `no` | Show/hide the version number |
| `updated` | `yes` | `yes`, `no` | Show/hide the last updated date |
| `class` | *(empty)* | Any valid CSS class | Add custom CSS classes for styling |

## Examples

### Display Privacy Policy with all metadata
```
[slos_legal_doc type="privacy-policy"]
```

### Display Terms without title (if you have a page title)
```
[slos_legal_doc type="terms-of-service" title="no"]
```

### Display Cookie Policy without version and updated info
```
[slos_legal_doc type="cookie-policy" version="no" updated="no"]
```

### Add custom CSS class for styling
```
[slos_legal_doc type="refund-policy" class="my-custom-style"]
```

### Minimal display (content only)
```
[slos_legal_doc type="shipping-policy" title="no" version="no" updated="no"]
```

## Document Type Reference

Use these exact values for the `type` attribute:

- **Privacy Policy**: `privacy-policy`
- **Terms of Service**: `terms-of-service`
- **Cookie Policy**: `cookie-policy`
- **Refund & Return Policy**: `refund-policy`
- **Shipping Policy**: `shipping-policy`

*Note: You can use underscores or hyphens - both `privacy-policy` and `privacy_policy` work.*

## Creating Legal Pages

### Recommended Approach

1. **Create a new page** in WordPress (Pages > Add New)
2. **Set the title** (e.g., "Privacy Policy")
3. **Add the shortcode** in the content area:
   ```
   [slos_legal_doc type="privacy-policy" title="no"]
   ```
   *Use `title="no"` since the page already has a title*
4. **Publish the page**
5. **Add to menu** (Appearance > Menus) - typically in footer menu

### Example Legal Page Structure

**Page Title:** Privacy Policy  
**Content:**
```
[slos_legal_doc type="privacy-policy" title="no"]
```

**Page Title:** Terms of Service  
**Content:**
```
[slos_legal_doc type="terms-of-service" title="no"]
```

**Page Title:** Cookie Policy  
**Content:**
```
[slos_legal_doc type="cookie-policy" title="no"]
```

## Styling

The shortcode outputs semantic HTML with CSS classes for easy styling:

```html
<div class="slos-legal-document" data-doc-type="privacy-policy">
    <h2 class="slos-legal-document__title">Privacy Policy</h2>
    <div class="slos-legal-document__meta">
        <span class="slos-legal-document__version">Version: 1.0</span>
        <span class="slos-legal-document__separator">|</span>
        <span class="slos-legal-document__updated">Last Updated: March 15, 2024</span>
    </div>
    <div class="slos-legal-document__content">
        <!-- Document content here -->
    </div>
</div>
```

### Custom Styling

Add custom CSS to your theme's stylesheet or use the Customizer:

```css
/* Change title color */
.slos-legal-document__title {
    color: #0066cc;
}

/* Adjust spacing */
.slos-legal-document {
    padding: 30px;
}

/* Style metadata section */
.slos-legal-document__meta {
    background: #f0f0f0;
    border-left-color: #0066cc;
}
```

## Error Handling

### Document Not Generated
If a document hasn't been generated yet, administrators will see:
```
Legal Document Error: Document "privacy-policy" has not been generated yet. 
Please generate it from the Legal Documents Hub.
```
Regular visitors won't see any output.

### Document Not Published
If a document is in draft status:
```
Legal Document Error: This document is not published yet.
```

### Document Type Not Available
If you try to display a dormant document:
```
Legal Document Error: This document type is not available.
```

### Missing Type Attribute
```
Legal Document Error: Document type is required. 
Usage: [slos_legal_doc type="privacy-policy"]
```

## Frequently Asked Questions

**Q: Can I use multiple shortcodes on the same page?**  
A: Yes! You can display multiple documents on one page.

**Q: Will updates to the document automatically appear on my page?**  
A: Yes! The shortcode always displays the latest published version. Just regenerate the document in the hub.

**Q: Can I edit the document content after displaying it?**  
A: Yes, edit the document in the Legal Documents Hub, then it updates automatically wherever the shortcode is used.

**Q: What if I want to display a document that's not in the active list?**  
A: Only the 5 active document types can be displayed. Other documents are dormant and cannot be accessed via shortcode.

**Q: Can I use this shortcode in widgets?**  
A: Yes! WordPress supports shortcodes in text widgets by default.

**Q: Does the shortcode work with page builders?**  
A: Yes! It works with any page builder that supports WordPress shortcodes (Gutenberg, Elementor, Beaver Builder, etc.).

## Troubleshooting

### Shortcode appears as text instead of rendering
- Make sure you're using square brackets: `[slos_legal_doc]` not `{slos_legal_doc}`
- Check that the shortcode is properly closed
- Verify the plugin is active

### Nothing displays on the page
- Verify the document is **published** in the Legal Documents Hub
- Check that you're using a valid `type` value
- Ensure the document type is one of the 5 active types
- Log in as an administrator to see error messages

### Content looks unstyled
- Check that your theme doesn't have conflicting CSS
- Verify the CSS file is loading (check browser developer tools)
- Try adding `!important` to your custom styles if needed

## Support

For additional help:
1. Check the Legal Documents Hub for document status
2. Review generated documents in Compliance > Legal Documents
3. Contact your plugin administrator for site-specific issues

---

**Version:** 3.5.0  
**Last Updated:** March 2024  
**Shortcode:** `[slos_legal_doc]`
