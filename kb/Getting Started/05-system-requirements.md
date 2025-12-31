# System Requirements & Compatibility

## Minimum Requirements

### WordPress
- **Minimum Version:** 6.0
- **Tested Up To:** 6.7
- **Recommendation:** Use latest WordPress version for security

### PHP
- **Minimum Version:** 7.4
- **Recommended Version:** 8.1 or higher
- **Support:** 8.0, 8.1, 8.2, 8.3

### Database
- **MySQL:** 5.7 or higher
- **MariaDB:** 10.2 or higher
- **PostreSQL:** Not officially supported (may work)

### Server
- **Disk Space:** 20MB minimum (50MB recommended)
- **Memory:** 256MB minimum (512MB recommended)
- **Web Server:** Apache, Nginx, or compatible
- **SSL Certificate:** Highly recommended (required for GDPR)

## Compatibility

### Theme Compatibility
✓ Works with all WordPress themes
✓ No theme-specific dependencies
✓ Fully responsive design

### Plugin Compatibility

**Fully Compatible:**
- Elementor
- WooCommerce
- WP Rocket
- Cloudflare
- Jetpack
- Wordfence

**Known Issues:**
- Some GDPR plugins may conflict (disable one)
- JavaScript minification must preserve our code
- Some page builders may need custom CSS

**Incompatible:**
- Other cookie consent plugins (disable them)
- Conflicting GDPR plugins

### Multisite Compatibility
✓ Fully supported
✓ Per-site module configuration
✓ Network-wide settings available
- Network activation recommended

## Performance Requirements

Typical resource usage:

| Component | CPU | Memory | Disk |
|-----------|-----|--------|------|
| Consent Banner | <1% | <2MB | 50KB |
| Accessibility Scanner | 5-10% (during scan) | 5-20MB | 200KB |
| Legal Documents | 2-5% (generation) | 10MB | 500KB |
| DSR Portal | <1% | <5MB | 100KB |

## CDN Compatibility

- ✓ Works with CloudFlare
- ✓ Works with MaxCDN
- ✓ Works with CloudFront
- ✓ Works with BunnyCDN

## Caching Compatibility

**Compatible with:**
- WP Super Cache
- W3 Total Cache
- WP Rocket
- Cache Enabler
- LiteSpeed Cache

**Note:** Disable caching for SLOS admin pages

## SSL/HTTPS

- ✓ Works with HTTP (not recommended)
- ✓ Self-signed certificates supported
- ✓ Let's Encrypt compatible
- ✓ Subdomain SSL supported

## Browser Compatibility

**Desktop:**
- Chrome 36+
- Firefox 36+
- Safari 9.1+
- Edge 15+

**Mobile:**
- iOS Safari 9.1+
- Android Chrome 36+
- Samsung Internet 4+

**Admin Panel:**
- Modern browsers only
- IE 11: Not supported

## Backup Compatibility

Recommended backup plugins:
- ✓ UpdraftPlus
- ✓ BackWPup
- ✓ All-in-One WP Migration
- ✓ Duplicator

## Email Delivery

For DSR notifications:
- Uses WordPress `wp_mail()` function
- Requires working SMTP or mail server
- Verify mail delivery in Settings

## Before Installing

Check compatibility:
```
1. WordPress version via Dashboard
2. PHP version via SLOS → System Info
3. Available disk space
4. Active plugins for conflicts
5. Theme compatibility
```

## After Installing

Verify everything works:
1. Dashboard loads without errors
2. All modules accessible
3. Database tables created
4. No fatal PHP errors
5. Frontend banner works (if enabled)

## Getting Help

If you encounter compatibility issues:
1. Check **SLOS** → **System Info** for environment details
2. Review error log at `/wp-content/debug.log`
3. Disable conflicting plugins to test
4. Contact support with system info
