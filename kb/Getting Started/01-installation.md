# Installation Guide

## Quick Install

### Automatic Installation
1. Log in to your WordPress dashboard
2. Navigate to **Plugins** → **Add New**
3. Search for **"Shahi LegalFlowSuite"**
4. Click **Install Now** and then **Activate**

### Manual Installation
1. Download the plugin ZIP file
2. Log in to your WordPress dashboard
3. Navigate to **Plugins** → **Add New** → **Upload Plugin**
4. Select the downloaded ZIP file
5. Click **Install Now** and then **Activate**

### Requirements
- **WordPress:** 6.0 or higher (tested up to 6.7)
- **PHP:** 7.4 or higher
- **Database:** MySQL 5.7+ or MariaDB 10.2+
- **Disk Space:** ~15MB for plugin files

## What Gets Installed

During activation, the plugin:
- Creates database tables for consents, DSR requests, accessibility scans, and more
- Sets up default configuration options
- Generates necessary directories for documents and exports
- Registers custom post types and taxonomies
- Creates default pages (Privacy Policy, Cookie Policy, etc.) - optional

## Post-Installation

After successful installation and activation:
1. The plugin dashboard appears in the WordPress admin menu as **"SLOS"**
2. A setup wizard runs on first visit
3. Navigate to **SLOS** → **Settings** to configure modules
4. No additional configuration required for basic features

## Troubleshooting Installation

**Plugin won't activate?**
- Check PHP version (minimum 7.4)
- Verify WordPress version (minimum 6.0)
- Check for conflicting plugins
- Review error logs in `/wp-content/debug.log`

**Database tables not created?**
- Click **SLOS** → **Settings** → **Reinstall Database**
- Check database user permissions
- Ensure MySQL/MariaDB version is compatible

**Missing admin menu?**
- Deactivate and reactivate the plugin
- Clear WordPress transients cache
- Verify user has `manage_options` capability

## Next Steps

After installation:
1. Complete the onboarding wizard
2. Configure which modules to use
3. Set up your company profile
4. Review module-specific settings
5. Start using the features!
