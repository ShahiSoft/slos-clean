# Troubleshooting Common Issues

## Plugin Won't Activate

### Symptoms
- "Error activating plugin" message
- Plugin remains inactive
- No SLOS menu in admin

### Causes & Solutions

**Cause: PHP Version Too Old**
- Requirement: PHP 7.4+
- Solution: Upgrade PHP version with hosting provider
- Check: **SLOS** → **Settings** → **System Info** to verify

**Cause: WordPress Version Incompatible**
- Requirement: WordPress 6.0+
- Solution: Upgrade WordPress to latest version
- Check: Dashboard → Updates

**Cause: Memory Limit Too Low**
- Requirement: 256MB minimum (512MB recommended)
- Solution: Add to wp-config.php:
  ```php
  define('WP_MEMORY_LIMIT', '256M');
  define('WP_MAX_MEMORY_LIMIT', '512M');
  ```

**Cause: File Permissions**
- Issue: Plugin can't write to directories
- Solution: Fix permissions:
  ```bash
  chmod -R 755 wp-content/plugins/shahi-legalflowsuite
  ```

**Cause: Conflicting Plugin**
- Issue: Another plugin prevents activation
- Solution: 
  1. Deactivate all other plugins
  2. Try activating SLOS
  3. Re-enable plugins one by one
  4. Identify conflicting plugin

### Debug Steps

1. Enable WordPress debugging:
   ```php
   // In wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. Check error log:
   - Location: `/wp-content/debug.log`
   - Look for SLOS errors
   - Note exact error message

3. Contact support with:
   - Error message
   - PHP version
   - WordPress version
   - Active plugins list

## Dashboard Won't Load

### Symptoms
- Blank page when clicking SLOS menu
- "Nonce verification failed" message
- 500 Internal Server Error

### Solutions

**Clear WordPress Cache**
- Via plugin (W3 Total Cache, WP Rocket, etc.)
- Or manually via FTP/SSH:
  ```bash
  rm -rf wp-content/cache/
  ```

**Check User Permissions**
- Verify user has `manage_options` capability
- Ensure user is Administrator role
- Go to **Users** → Select user → Check "Administrator"

**Disable Plugins**
1. Deactivate all plugins except SLOS
2. Try accessing dashboard
3. Reactivate plugins one by one
4. Identify culprit

**Increase PHP Resources**
- In wp-config.php:
  ```php
  define('WP_MEMORY_LIMIT', '512M');
  set_time_limit(300);
  ```

**Check Browser Console**
- Open DevTools (F12)
- Go to Console tab
- Look for JavaScript errors
- Check Network tab for failed requests

## Database Tables Not Created

### Symptoms
- "Table doesn't exist" errors
- Settings won't save
- Blank dashboard metrics

### Solutions

**Reinstall Database**
1. Go to **SLOS** → **Settings** → **Advanced**
2. Click **Reinstall Database Tables**
3. Wait for completion
4. Check if tables created

**Manual Check via phpMyAdmin**
1. Open phpMyAdmin
2. Select your database
3. Look for tables starting with `slos_`
4. If empty, reinstall tables

**Check Database Permissions**
- User must have CREATE TABLE permission
- User must have ALTER TABLE permission
- User must have INSERT, SELECT, UPDATE, DELETE permissions

**Check Disk Space**
- Verify adequate disk space on server
- Database creation requires space
- Ask hosting provider if needed

**Review Error Log**
- Check `/wp-content/debug.log`
- Look for database errors
- May show permission issues or MySQL version problems

## Consent Banner Not Showing

### Symptoms
- Banner doesn't appear on frontend
- Users can interact without consenting
- Cookies set immediately

### Solutions

**Verify Module Enabled**
1. Go to **SLOS** → **Settings** → **Modules**
2. Check "Consent Management" is enabled
3. Click Save if changed

**Check Frontend Setting**
1. Go to **Consent Management** → **Settings**
2. Verify **"Enable Banner on Frontend"** is checked
3. Verify **"Auto-Load Banner"** is checked
4. Save settings

**Clear Cache Completely**
- Browser cache
- WordPress cache plugin
- CloudFlare cache
- CDN cache
- Memcached/Redis cache

**Check JavaScript Errors**
1. Open page in browser
2. Press F12 to open DevTools
3. Go to Console tab
4. Look for red error messages
5. Check Network tab for failed assets

**Verify No CSS Conflicts**
- Banner CSS might be hidden
- Try banner in incognito mode
- Test with all plugins disabled except SLOS

**Check Banner Position**
- Is banner behind other elements?
- Check z-index conflicts
- Try different position (top vs bottom)

## Cookies Not Being Tracked

### Symptoms
- Consent records empty
- Cookie inventory shows zero cookies
- Analytics not recording

### Solutions

**Verify Scanner Enabled**
1. Go to **Consent Management** → **Settings**
2. Check **"Enable Cookie Scanner"** is enabled
3. Click **"Run Cookie Scan"** manually
4. Wait for completion

**Check for JavaScript Errors**
1. DevTools Console (F12)
2. Look for errors in consent code
3. Check Network tab for failed loads

**Browser Settings Blocking Cookies**
- Test in private/incognito mode
- Check browser cookie settings
- Try different browser
- Test on different device

**Test Cookie Setting**
1. Accept consent in banner
2. Check browser Storage:
   - DevTools → Application → Cookies
   - Look for cookie with site domain
   - Verify consent value stored

## Accessibility Scan Not Running

### Symptoms
- Scan button doesn't work
- Progress popup doesn't appear
- Scan seems to hang

### Solutions

**Check Module Enabled**
1. Go to **SLOS** → **Settings** → **Modules**
2. Verify "Accessibility Scanner" is enabled
3. Enable if disabled

**Increase PHP Timeout**
- In wp-config.php:
  ```php
  define('WP_MEMORY_LIMIT', '512M');
  set_time_limit(600); // 10 minutes
  ```

**Increase Server Timeout**
- Edit `.htaccess` (if using Apache):
  ```
  php_value max_execution_time 600
  php_value memory_limit 512M
  ```

**Check URL Accessibility**
- Scan must reach your pages
- Test URL in browser
- If password protected, disable authentication for scan
- Verify no IP blocking

**Run Single Page Scan**
- Instead of site-wide scan
- May help identify problematic pages
- Try scanning homepage first

**Check Error Log**
- Location: `/wp-content/debug.log`
- Look for accessibility scanner errors
- May indicate resource issues

## DSR Requests Not Sending Emails

### Symptoms
- Users don't receive verification emails
- Admin doesn't get notifications
- Emails not appearing anywhere

### Solutions

**Verify SMTP Setup**
1. Go to **SLOS** → **Settings** → **Email**
2. Click **"Send Test Email"**
3. Check admin email for test message
4. If doesn't arrive, SMTP is misconfigured

**Check WordPress Mail Configuration**
- Go to **SLOS** → **System Info**
- Look for "Mail Setup" section
- Verify mail function not disabled
- Check mail server logs

**Whitelist SLOS Emails**
- Some hosts filter plugin emails
- Add SLOS emails to whitelist
- Check spam/trash folder
- Ask hosting provider

**Test Mail Plugin**
- Use "Test Mail" in Settings
- Check admin email address
- Verify it's configured correctly
- Check spam filters

**Review Error Log**
- Location: `/wp-content/debug.log`
- Look for mail() function errors
- May show SMTP connection issues

## Document Generation Failing

### Symptoms
- "Error generating document" message
- Export fails
- PDF won't create

### Solutions

**Verify Company Profile Complete**
1. Go to **Legal Documents** → **Company Profile**
2. Fill in all required fields
3. Click Save
4. Try generating document again

**Check File Permissions**
- Documents need write permission
- Location: `/wp-content/uploads/slos/`
- Fix permissions:
  ```bash
  chmod -R 755 wp-content/uploads/slos/
  ```

**Increase PHP Memory**
- Document generation memory intensive
- In wp-config.php:
  ```php
  define('WP_MEMORY_LIMIT', '512M');
  ```

**Check Disk Space**
- Verify adequate space in uploads folder
- Ask hosting provider if full
- Clean up old documents if needed

**Try Different Format**
- If PDF fails, try HTML export
- If HTML fails, try JSON export
- Helps identify specific issue

**Test with Simple Template**
- Use "Standard" template
- Simpler templates need less resources
- Try more complex after success

## Modules Interfering with Each Other

### Symptoms
- Enabling one module breaks another
- Features stop working
- Unexpected behavior

### Solutions

**Check Execution Order**
- Some modules depend on others
- Module Manager controls order
- Go to **SLOS** → **Settings** → **Module Order**
- Re-order if needed

**Disable & Re-enable**
1. Disable problematic module
2. Click Save
3. Wait 30 seconds
4. Re-enable module
5. Click Save

**Clear All Caches**
- WordPress cache
- Browser cache
- CDN cache
- Database cache (Redis/Memcached)

**Check for Conflicts**
1. Disable all modules
2. Enable one module
3. Test it works
4. Enable next module
5. Test
6. Repeat to find conflict

**Reset Module Settings**
1. Go to module settings
2. Click **"Reset to Defaults"**
3. Reconfigure module
4. Test functionality

## Performance Issues After Activation

### Symptoms
- Site slow after plugin activation
- Page load time increased
- Server CPU usage high

### Solutions

**Disable Modules Temporarily**
1. Keep only essential modules
2. Enable others one by one
3. Identify which causes slowdown
4. Configure that module for performance

**Check Scanning Schedule**
- Accessibility scans can be intensive
- Disable automatic scheduled scans
- Run manually during off-peak
- Go to **Settings** → **Scanning Schedule**

**Optimize Database Queries**
- Go to **Settings** → **Advanced**
- Check **"Query Optimization"**
- Runs periodic optimization
- Helps with large data volumes

**Reduce Cookie Scan Frequency**
- Scanner can impact performance
- Go to **Consent Management** → **Settings**
- Change scan frequency from daily to weekly
- Run manually as needed

**Disable Debug Logging**
- Debug logging adds overhead
- Go to **Settings** → **Advanced**
- Uncheck **"Debug Logging"**
- Only enable when troubleshooting

**Check Hosting Resources**
- Ask hosting provider about:
  - PHP memory available
  - CPU limits
  - Database performance
  - Disk I/O speed

## Getting Help

### Gather Information

Before contacting support:
1. Go to **SLOS** → **System Info**
2. Copy entire system information
3. Note exact error messages
4. Check `/wp-content/debug.log`
5. List active plugins
6. Note WordPress & PHP versions

### Contact Support

Include:
- System info from above
- Error messages (exact text)
- Steps to reproduce issue
- What you've tried so far
- Hosting provider info

### Post in Forums

WordPress.org plugin forums:
- Go to plugin page
- Click "Support" tab
- Search for similar issues first
- Provide all information above

## Getting More Help

Next steps if issue persists:
1. Disable all plugins except SLOS
2. Switch to default WordPress theme
3. Try in fresh browser/private mode
4. Test on different device
5. Test on different network
6. Review detailed logs carefully
7. Contact hosting provider
8. Reach out to plugin developers
