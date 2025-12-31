# Performance Optimization Guide

## Optimize for Speed

Shahi LegalFlowSuite is built for performance, but you can optimize further.

## Banner Performance

### 1. Lazy Load Banner

The banner auto-loads deferred (non-blocking). Additional optimization:

1. Go to **Consent Management** → **Settings**
2. Set **Display Delay** to 2000ms (2 seconds)
3. Allows page to render first
4. Banner loads after content

### 2. Disable Animations

If animations slow things down:

1. Go to **Consent Management** → **Appearance**
2. Uncheck **"Enable Animations"**
3. Banner displays instantly
4. No transition effects
5. Faster perceived performance

### 3. Minimize Banner Size

1. Go to **Consent Management** → **Settings**
2. Change **Banner Size** to "Compact"
3. Reduces banner height
4. Less DOM to render
5. Improves CLS score

### 4. Simplify Banner Template

- Simple template fastest
- GDPR template more complex
- Use Simple if possible
- Advanced template slowest

## Accessibility Scanner Performance

### 1. Reduce Scan Scope

1. Go to **Accessibility Scanner** → **Settings**
2. **Max URLs to Scan** - Set lower (e.g., 50 instead of 100)
3. **Deep Scan** - Disable if not needed
4. Scans complete faster

### 2. Disable Auto-Scan

1. Go to **Settings** → **Scanning Schedule**
2. Uncheck **"Enable Scheduled Scans"**
3. Run scans manually during low-traffic
4. Prevents peak-hour slowdown

### 3. Scan During Off-Hours

1. If keeping scheduled scans enabled:
2. Set **Scan Time** to 2:00 AM
3. Runs when site has least traffic
4. Minimal performance impact

### 4. Limit Fixer Concurrency

1. Go to **Accessibility Scanner** → **Advanced**
2. **Max Concurrent Fixers** - Lower number
3. Default: 5, Set to 2-3
4. Less resource intensive
5. Scans take longer but use less CPU

## Database Optimization

### 1. Enable Query Caching

1. Go to **SLOS** → **Settings** → **Advanced**
2. Check **"Enable Query Caching"**
3. Caches frequent queries
4. Reduces database load
5. Improves response time

### 2. Optimize Database Tables

1. Go to **SLOS** → **Settings** → **Advanced**
2. Click **"Optimize Database"**
3. Runs on next admin page load
4. Defragments tables
5. Improves query speed

### 3. Archive Old Records

1. Go to **SLOS** → **Settings** → **Data Management**
2. **Auto-Archive Records** - Enable
3. **Archive After (days)** - Set to 365
4. Keeps database lean
5. Maintains history in archive

### 4. Purge Logs Periodically

1. Go to **SLOS** → **Settings** → **Logging**
2. **Log Retention (days)** - Set to 90
3. Old logs automatically deleted
4. Reduces database size
5. Improves query performance

## Consent Data Optimization

### 1. Batch Process Requests

DSR and consent processing:

1. Go to **SLOS** → **Settings** → **Processing**
2. **Batch Size** - Set to 50
3. **Batch Delay** - Set to 100ms
4. Processes without overwhelming server
5. Better stability on shared hosting

### 2. Limit Audit Log Detail

1. Go to **SLOS** → **Settings** → **Audit Logging**
2. **Log Level** - Set to "Important Events"
3. Vs. "All Events" (verbose)
4. Reduces log size
5. Improves performance

### 3. Compress Old Data

1. Go to **SLOS** → **Settings** → **Data Management**
2. Check **"Compress Old Data"**
3. Older records compressed
4. Saves database space
5. Minimal speed impact

## Frontend Asset Optimization

### 1. Minify & Combine CSS/JS

Enable from **Settings** → **Performance**:
1. Check **"Minify CSS"**
2. Check **"Minify JavaScript"**
3. CSS from 45KB → 12KB
4. JS from 78KB → 22KB
5. Reduces bandwidth

### 2. Enable Asset Caching

1. Go to **Settings** → **Performance**
2. Check **"Asset Caching"**
3. Sets browser cache headers
4. Assets cached 30 days
5. Faster repeat visits

### 3. Defer Non-Critical CSS

1. Go to **Settings** → **Performance**
2. Check **"Defer Non-Critical CSS"**
3. Moves non-essential CSS
4. Improves First Contentful Paint
5. Better performance metrics

### 4. Defer JavaScript

Already enabled by default:
- Consent banner JS deferred
- Doesn't block page rendering
- Loads after DOM ready
- No render blocking

## Server Configuration

### Increase PHP Limits

In `wp-config.php`:

```php
// Increase memory for scanning/generation
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '1024M');

// Increase execution time for long operations
set_time_limit(600); // 10 minutes

// Increase upload size for PDF exports
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
```

### Optimize MySQL/MariaDB

Ask your hosting provider to configure:

```sql
-- Increase query cache
query_cache_size = 128M

-- Optimize join buffer
join_buffer_size = 4M

-- Optimize sort buffer
sort_buffer_size = 4M
```

### Enable OPcache

If available (usually is):

```php
// Php.ini configuration
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
```

## Caching Strategy

### 1. Page Caching

Compatible with:
- WP Super Cache
- W3 Total Cache
- WP Rocket
- LiteSpeed Cache

**Note:** Exclude SLOS admin pages from cache

### 2. Database Query Caching

Built-in query cache:
1. Go to **Settings** → **Caching**
2. Check **"Query Caching"**
3. Default: 1 hour TTL
4. Adjust TTL as needed

### 3. Object Caching

If available:
- Redis: Recommended
- Memcached: Good alternative
- WP Rocket: Handles automatically

### 4. Asset Caching

Browser cache headers:
```
CSS: 30 days
JavaScript: 30 days
Images: 60 days
```

## CDN Optimization

Using CloudFlare or similar:

1. **Cache Everything** - CDN caches HTML, CSS, JS
2. **Browser Cache TTL** - Set to 1 month
3. **Page Rules:**
   - SLOS admin: Don't cache
   - /wp-admin/: Don't cache
   - Other pages: Cache everything
4. **Minify** - Enable CloudFlare minification

## Monitor Performance

### Check Site Speed

Use free tools:
- **Google PageSpeed Insights** - pagespeed.web.dev
- **GTmetrix** - gtmetrix.com
- **WebPageTest** - webpagetest.org

### Key Metrics

Monitor:
- **CLS (Cumulative Layout Shift)** - Target: <0.1
- **LCP (Largest Contentful Paint)** - Target: <2.5s
- **FID (First Input Delay)** - Target: <100ms

### Shahi Performance Targets

- Banner adds <50ms to load time
- Scanner doesn't block page rendering
- Zero blocking JavaScript
- Total overhead: <100KB assets

## Common Performance Mistakes

### DON'T:
- ❌ Leave all modules enabled if not using them
- ❌ Run accessibility scans during peak traffic
- ❌ Keep debug logging enabled on production
- ❌ Use Advanced template if Simple works
- ❌ Skip asset minification
- ❌ Ignore database optimization

### DO:
- ✓ Enable only needed modules
- ✓ Schedule scans for off-hours
- ✓ Disable debug in production
- ✓ Use appropriate template complexity
- ✓ Enable minification
- ✓ Optimize database quarterly

## Performance Checklist

- ☐ Minify CSS/JavaScript enabled
- ☐ Asset caching enabled
- ☐ Query caching enabled
- ☐ Debug logging disabled
- ☐ Scheduled scans during off-hours
- ☐ Banner animations enabled (unless slow)
- ☐ Unnecessary modules disabled
- ☐ Database optimized
- ☐ PHP memory increased to 512M
- ☐ Execution time increased to 600s
- ☐ CloudFlare/CDN configured

## Benchmarks

### Typical Performance

| Operation | Time | Resource |
|-----------|------|----------|
| Banner render | 50-100ms | <2MB |
| Single page scan | 30-60s | 50-100MB |
| Full site scan | 5-15 min | 100-500MB |
| Document generation | 5-30s | 20-50MB |
| DSR export | 10-60s | 50-200MB |

### On Shared Hosting

May be 2-3x slower. Solutions:
- Upgrade to VPS/Cloud hosting
- Reduce scan scope
- Run scans during off-hours
- Enable caching
- Optimize database

## When to Contact Support

If after optimization:
- Banner still slow
- Scans timing out
- Site performance worse
- Database growing rapidly

Provide:
- Performance metrics
- Server specifications
- Plugin list
- SLOS settings
- Optimization steps taken

## Next Steps

1. Review Performance section in Settings
2. Enable all applicable optimizations
3. Monitor site speed with PageSpeed Insights
4. Adjust based on results
5. Re-check quarterly

## Related

- [Settings Guide](../Advanced/01-settings-reference.md)
- [Troubleshooting Guide](01-common-issues.md)
