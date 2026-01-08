# Legal Document Shortcodes - Quick Reference

## Active Document Shortcodes

The following 5 legal documents can be displayed using shortcodes:

### 1. Privacy Policy
```
[slos_legal_doc type="privacy-policy"]
```

### 2. Terms of Service
```
[slos_legal_doc type="terms-of-service"]
```

### 3. Cookie Policy
```
[slos_legal_doc type="cookie-policy"]
```

### 4. Refund & Return Policy
```
[slos_legal_doc type="refund-policy"]
```

### 5. Shipping Policy
```
[slos_legal_doc type="shipping-policy"]
```

## Common Usage Examples

### Full Display (Default)
All metadata included - title, version, last updated date, and content.
```
[slos_legal_doc type="privacy-policy"]
```

### Hide Title (for pages with their own title)
```
[slos_legal_doc type="terms-of-service" title="no"]
```

### Content Only
```
[slos_legal_doc type="cookie-policy" title="no" version="no" updated="no"]
```

### With Custom CSS Class
```
[slos_legal_doc type="refund-policy" class="my-custom-legal-doc"]
```

## Available Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `type` | See document types above | *(required)* | Which document to display |
| `title` | `yes` or `no` | `yes` | Show/hide document title |
| `version` | `yes` or `no` | `yes` | Show/hide version number |
| `updated` | `yes` or `no` | `yes` | Show/hide last updated date |
| `class` | Any CSS class name | *(empty)* | Add custom CSS classes |

## Prerequisites

Before using a shortcode:

1. ✅ **Generate the document** in Legal Documents Hub
2. ✅ **Publish the document** (only published docs display)
3. ✅ **Use one of the 5 active types** (others are dormant)

## CSS Classes

The shortcode generates semantic HTML with these classes for styling:

- `.slos-legal-document` - Main container
- `.slos-legal-document__title` - Document title
- `.slos-legal-document__meta` - Metadata container
- `.slos-legal-document__version` - Version text
- `.slos-legal-document__updated` - Last updated text
- `.slos-legal-document__content` - Document content area

## Where to Use

- ✅ WordPress Pages
- ✅ WordPress Posts
- ✅ Text Widgets
- ✅ Page Builders (Gutenberg, Elementor, etc.)
- ✅ Custom Templates

## Troubleshooting

**Shortcode shows as text?**
- Use square brackets: `[slos_legal_doc]` not `{slos_legal_doc}`

**Nothing displays?**
- Check document is published in Legal Documents Hub
- Verify you're using a valid active document type
- Log in as admin to see error messages

**Styling issues?**
- Check browser console for CSS file loading
- Add custom CSS to your theme if needed

---

For detailed documentation, see [SHORTCODE-USAGE.md](SHORTCODE-USAGE.md)
