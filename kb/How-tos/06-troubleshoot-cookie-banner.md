# Troubleshoot Cookie Banner Issues

## Common Cookie Banner Problems & Solutions

### Banner Not Displaying

#### Problem: Cookie banner doesn't appear on website

**Symptoms:**
- Banner completely missing from all pages
- No consent prompt shown to visitors
- Console errors related to banner scripts

**Solutions:**

1. **Check Module Status**
   ```
   SLOS → Settings → Modules
   ```
   Ensure "Consent Management" is enabled

2. **Verify Banner Configuration**
   ```
   SLOS → Consent Management → Banner Settings
   ```
   - Check if banner is set to "Enabled"
   - Verify display conditions are met
   - Confirm banner position is set

3. **Check Theme Conflicts**
   - Disable other consent plugins temporarily
   - Switch to default WordPress theme
   - Check for JavaScript errors in browser console

4. **Clear Caches**
   - Clear WordPress cache (if using caching plugin)
   - Clear browser cache and cookies
   - Hard refresh the page (Ctrl+F5)

#### Problem: Banner displays but doesn't work properly

**Symptoms:**
- Banner appears but buttons don't respond
- Consent choices aren't saved
- Banner reappears on every page load

**Solutions:**

1. **Check JavaScript Errors**
   Open browser developer tools (F12) and check Console tab for errors

2. **Verify Cookie Settings**
   ```
   SLOS → Consent Management → Cookie Settings
   ```
   - Ensure cookies are properly configured
   - Check cookie expiration settings
   - Verify cookie domain settings

3. **Test Consent Storage**
   - Accept cookies and check if they're stored
   - Use browser dev tools → Application → Cookies
   - Look for `slos_consent_*` cookies

### Consent Not Being Saved

#### Problem: User consent choices aren't remembered

**Symptoms:**
- Banner reappears on every visit
- Consent preferences reset
- Cookies blocked despite consent

**Solutions:**

1. **Check Cookie Domain**
   ```
   SLOS → Consent Management → Advanced Settings
   ```
   - Ensure cookie domain matches your site
   - For subdomains, use `.domain.com`
   - Check for www vs non-www issues

2. **Verify SSL Configuration**
   - Consent cookies require HTTPS in most browsers
   - Check SSL certificate validity
   - Ensure site loads over HTTPS

3. **Browser Privacy Settings**
   - Some browsers block third-party cookies
   - Check browser privacy settings
   - Test in incognito/private mode

4. **Cookie Expiration**
   - Check cookie lifetime settings
   - Default is usually 1 year
   - Adjust based on your needs

### Banner Styling Issues

#### Problem: Banner looks wrong or overlaps content

**Symptoms:**
- Banner covers important content
- Styling conflicts with theme
- Banner appears in wrong position
- Colors don't match site design

**Solutions:**

1. **Adjust Banner Position**
   ```
   SLOS → Consent Management → Banner Design
   ```
   - Try different positions: top, bottom, corner
   - Adjust z-index if overlapping
   - Test on mobile devices

2. **Custom CSS Fixes**
   ```css
   /* Add to theme custom CSS */
   .slos-consent-banner {
     z-index: 999999 !important;
   }

   .slos-consent-banner .banner-content {
     max-width: 1200px;
     margin: 0 auto;
   }
   ```

3. **Theme Integration**
   - Use theme hooks to position banner
   - Check for theme-specific CSS conflicts
   - Test with different themes

### Geo-Targeting Problems

#### Problem: Wrong banner shown for location

**Symptoms:**
- Wrong language or regulations shown
- Banner not appearing in certain countries
- Incorrect geo-detection

**Solutions:**

1. **Check Geo-Detection**
   ```
   SLOS → Consent Management → Geo-Targeting
   ```
   - Verify IP geolocation service is working
   - Check API key if using paid service
   - Test with different IP addresses

2. **Update Geo-Rules**
   - Review country-specific rules
   - Check for EU/EEA country list updates
   - Verify regional compliance settings

3. **Fallback Settings**
   - Configure default banner for unknown locations
   - Set global fallback rules
   - Test with VPN or proxy

### Mobile Display Issues

#### Problem: Banner doesn't work properly on mobile

**Symptoms:**
- Banner too small on mobile
- Buttons hard to tap
- Banner covers mobile navigation
- Text not readable on small screens

**Solutions:**

1. **Mobile Optimization**
   ```
   SLOS → Consent Management → Banner Design
   ```
   - Enable mobile-specific settings
   - Adjust banner size for mobile
   - Test touch targets (minimum 44px)

2. **Responsive Design**
   - Check banner responsiveness
   - Test on various screen sizes
   - Verify mobile browser compatibility

3. **Mobile-Specific CSS**
   ```css
   @media (max-width: 768px) {
     .slos-consent-banner {
       position: fixed !important;
       bottom: 0 !important;
       width: 100% !important;
     }
   }
   ```

### Integration Conflicts

#### Problem: Banner conflicts with other plugins

**Symptoms:**
- Multiple consent banners appearing
- JavaScript errors from conflicts
- Features not working together
- Performance issues

**Solutions:**

1. **Plugin Conflict Resolution**
   - Temporarily disable other consent plugins
   - Check plugin compatibility lists
   - Update all plugins to latest versions
   - Use plugin conflict detection tools

2. **JavaScript Conflicts**
   - Check for duplicate jQuery loading
   - Resolve script loading order issues
   - Use script defer/async attributes
   - Minify and combine scripts

3. **Theme Conflicts**
   - Test with default WordPress theme
   - Check theme JavaScript conflicts
   - Use child theme for customizations
   - Contact theme developer for compatibility

### Performance Issues

#### Problem: Banner slows down website

**Symptoms:**
- Slow page loading times
- High CPU usage
- Large JavaScript bundle size
- Poor Core Web Vitals scores

**Solutions:**

1. **Optimize Loading**
   ```
   SLOS → Consent Management → Performance
   ```
   - Enable lazy loading
   - Use CDN for assets
   - Minify CSS and JavaScript
   - Enable browser caching

2. **Reduce Bundle Size**
   - Disable unused features
   - Use tree shaking for JavaScript
   - Optimize images and assets
   - Enable compression

3. **Caching Strategies**
   - Implement proper caching headers
   - Use service worker for caching
   - Enable browser caching
   - Use CDN for global delivery

### Cookie Scanner Issues

#### Problem: Cookie scanner not finding cookies

**Symptoms:**
- Empty cookie inventory
- Missing third-party cookies
- Scanner not detecting dynamic cookies

**Solutions:**

1. **Scanner Configuration**
   ```
   SLOS → Consent Management → Cookie Scanner
   ```
   - Enable all scanner options
   - Configure scan frequency
   - Set up automated scanning
   - Check scanner permissions

2. **Manual Cookie Detection**
   - Use browser dev tools to inspect cookies
   - Check network tab for cookie setting
   - Manually add undetected cookies
   - Update cookie database

3. **Dynamic Content Scanning**
   - Enable JavaScript execution during scans
   - Configure user interaction simulation
   - Set up authenticated scanning
   - Use headless browser scanning

### Analytics Integration Problems

#### Problem: Consent events not tracking properly

**Symptoms:**
- Missing consent events in analytics
- Incorrect consent status tracking
- Analytics platform connection issues

**Solutions:**

1. **Platform Connection**
   ```
   SLOS → Analytics Integration → Platform Settings
   ```
   - Verify API keys and credentials
   - Check platform connectivity
   - Test authentication
   - Review error logs

2. **Event Configuration**
   - Verify event mapping
   - Check consent status passing
   - Test event firing
   - Validate event parameters

3. **Debug Analytics**
   - Use platform debug tools
   - Check browser developer console
   - Verify event queue
   - Test with different scenarios

### Legal Compliance Issues

#### Problem: Banner not meeting legal requirements

**Symptoms:**
- Non-compliant with GDPR/CCPA
- Missing required information
- Incorrect consent mechanisms

**Solutions:**

1. **Compliance Check**
   ```
   SLOS → Consent Management → Compliance
   ```
   - Run compliance audit
   - Check regulation requirements
   - Update banner content
   - Verify consent mechanisms

2. **Legal Review**
   - Consult with legal counsel
   - Update privacy policies
   - Review consent wording
   - Document compliance decisions

3. **Regulation Updates**
   - Stay updated on regulation changes
   - Update banner for new requirements
   - Review geo-targeting rules
   - Monitor compliance deadlines

## Advanced Troubleshooting

### Debug Mode

#### Enable Debug Logging
```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SLOS_DEBUG', true);
```

#### Check Debug Logs
```
wp-content/debug.log
```

### System Diagnostics

#### Run Diagnostics
```
SLOS → Settings → Diagnostics
```
- Check system requirements
- Verify file permissions
- Test database connections
- Review configuration settings

### Support Resources

#### Get Help
1. Check this troubleshooting guide
2. Review error logs
3. Test in staging environment
4. Contact support with diagnostic information

#### Diagnostic Information
- WordPress version
- PHP version
- Plugin version
- Browser console errors
- Server error logs
- Configuration export

## Prevention Tips

### Regular Maintenance
- Update plugin regularly
- Monitor error logs
- Test banner functionality
- Review compliance status

### Best Practices
- Use staging environment for testing
- Document customizations
- Keep backups of configurations
- Monitor performance metrics

### Proactive Monitoring
- Set up uptime monitoring
- Monitor consent rates
- Track error rates
- Review analytics data