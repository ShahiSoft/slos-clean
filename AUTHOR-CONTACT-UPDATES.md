# Author & Contact Information Updates

**Date:** 2025-01-XX  
**Purpose:** Updated all author and support contact information across the plugin to unify branding under shahisoft.store domain

## New Contact Information

- **Author:** ShahiSoft
- **Email:** legalflowsuite@shahisoft.store
- **Plugin Page:** https://shahisoft.store/index.php/shahilandin/complyflow/
- **Documentation/KB:** https://shahisoft.store/index.php/knowledge-base/?product=shahi-legalflowsuite
- **Support/Tickets:** https://shahisoft.store/index.php/my-tickets/

## Files Updated

### 1. shahi-legalflowsuite.php (Main Plugin File)
**Lines Changed:** Plugin header (lines 3-8)

**Changes:**
- ✅ Plugin URI: `shahisoft.com/shahi-legalflowsuite` → `shahisoft.store/index.php/shahilandin/complyflow/`
- ✅ Author URI: `shahisoft.com` → `shahisoft.store`

### 2. composer.json (Package Metadata)
**Lines Changed:** Authors section (lines 6-10)

**Changes:**
- ✅ Author name: `Shahi Team` → `ShahiSoft`
- ✅ Author email: `support@shahilegalops.com` → `legalflowsuite@shahisoft.store`

### 3. includes/Admin/Dashboard.php (Admin Dashboard)
**Lines Changed:** Plugin info method (lines 96-100) and support links (lines 172-214)

**Changes in get_plugin_info() method:**
- ✅ Author: `Shahi Digital` → `ShahiSoft`
- ✅ Author URL: `shahidigital.com` → `shahisoft.store`
- ✅ Plugin URL: `shahidigital.com/plugins/legalflowsuite` → `shahisoft.store/index.php/shahilandin/complyflow/`

**Changes in get_support_links() method:**
- ✅ Documentation: Changed from internal admin page to external KB: `shahisoft.store/index.php/knowledge-base/?product=shahi-legalflowsuite`
- ✅ Knowledge Base: Placeholder `#` → `shahisoft.store/index.php/knowledge-base/?product=shahi-legalflowsuite`
- ✅ Video Tutorials: Placeholder `#` → `shahisoft.store/index.php/knowledge-base/?product=shahi-legalflowsuite`
- ✅ Get Support: Internal admin page → `shahisoft.store/index.php/my-tickets/`
- ✅ Feature Request: Placeholder `#` → `shahisoft.store/index.php/my-tickets/`
- ℹ️ Changelog: Kept as internal link (admin.php?page=shahi-legalflowsuite-support#changelog)

## Old URLs Replaced

The following domains were systematically replaced:
- ❌ `shahisoft.com` → ✅ `shahisoft.store`
- ❌ `shahidigital.com` → ✅ `shahisoft.store`
- ❌ `support@shahilegalops.com` → ✅ `legalflowsuite@shahisoft.store`

## No Changes Required

The following files already had correct information:
- **readme.txt:** `Contributors: shahisoft` (lowercase username - correct format for WordPress.org)
- **dist/readme.txt:** Same as above (distribution copy)

## Dashboard Support Section

The Support section in the Dashboard right column now displays 6 working links:

1. **Documentation** → Knowledge Base (external)
2. **Knowledge Base** → Product-specific KB page (external)  
3. **Video Tutorials** → Knowledge Base (external)
4. **Get Support** → Ticket system (external)
5. **Feature Request** → Ticket system (external)
6. **Changelog** → Internal admin page (internal)

All external links now point to the correct shahisoft.store domain with proper URLs.

## Verification

All updates were verified by:
1. Reading back the modified files to confirm changes
2. Checking grep search results for remaining old URLs
3. Confirming all placeholder `#` URLs have been replaced with actual links

## Next Steps

1. ✅ All author/contact information updated
2. ⏭️ Regenerate submission package ZIP with updated information
3. ⏭️ Test plugin installation from new ZIP
4. ⏭️ Submit to WordPress.org with updated metadata
