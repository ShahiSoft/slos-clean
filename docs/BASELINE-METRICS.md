# Phase 4 Environment Setup - Baseline Performance Metrics

**Document Created**: January 2026  
**Plugin Version**: 3.1.1  
**Branch**: slos-newfix

## Purpose
This document tracks baseline performance metrics before Phase 4 refactoring begins. These metrics will be used to verify that the refactoring maintains or improves performance.

## Collection Instructions

### 1. Automatic Metrics Collection
Add this code to `functions.php` temporarily (REMOVE after 7 days):

```php
// SLOS Phase 4 Baseline Metrics Collection
// TODO: REMOVE THIS CODE AFTER 7 DAYS OF DATA COLLECTION
add_action('wp_footer', function() {
    if (is_admin() && current_user_can('manage_options')) {
        global $wpdb;
        $start_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $end_time = microtime(true);
        $memory = memory_get_peak_usage(true) / 1024 / 1024;
        
        error_log(sprintf(
            'SLOS_BASELINE: Page=%s, Time=%.3fs, Memory=%.2fMB, Queries=%d',
            $_SERVER['REQUEST_URI'],
            $end_time - $start_time,
            $memory,
            $wpdb->num_queries
        ));
    }
});
```

### 2. Manual Testing Checklist
Perform these actions and record results:

#### Accessibility Scanner Operations
- [ ] Scan single page (< 100 elements)
- [ ] Scan single page (100-500 elements)
- [ ] Scan single page (> 500 elements)
- [ ] Auto-fix single page (< 10 issues)
- [ ] Auto-fix single page (10-50 issues)
- [ ] Auto-fix single page (> 50 issues)
- [ ] Bulk scan (5 pages)
- [ ] Bulk scan (20 pages)

#### Dashboard Operations
- [ ] Load accessibility dashboard
- [ ] View statistics page
- [ ] Generate accessibility report
- [ ] Export data

## Baseline Metrics (TO BE FILLED)

### Scan Performance
| Operation | Elements | Time (avg) | Memory (peak) | DB Queries |
|-----------|----------|------------|---------------|------------|
| Single Scan (small) | < 100 | ___ seconds | ___ MB | ___ |
| Single Scan (medium) | 100-500 | ___ seconds | ___ MB | ___ |
| Single Scan (large) | > 500 | ___ seconds | ___ MB | ___ |
| Bulk Scan (5 pages) | - | ___ seconds | ___ MB | ___ |
| Bulk Scan (20 pages) | - | ___ seconds | ___ MB | ___ |

### Auto-Fix Performance
| Operation | Issues | Time (avg) | Memory (peak) | DB Queries |
|-----------|--------|------------|---------------|------------|
| Auto-fix (small) | < 10 | ___ seconds | ___ MB | ___ |
| Auto-fix (medium) | 10-50 | ___ seconds | ___ MB | ___ |
| Auto-fix (large) | > 50 | ___ seconds | ___ MB | ___ |

### Dashboard Performance
| Operation | Time (avg) | Memory (peak) | DB Queries |
|-----------|------------|---------------|------------|
| Load dashboard | ___ seconds | ___ MB | ___ |
| View statistics | ___ seconds | ___ MB | ___ |
| Generate report | ___ seconds | ___ MB | ___ |
| Export data | ___ seconds | ___ MB | ___ |

### Error Rates (7-day period)
| Error Type | Count | Percentage |
|------------|-------|------------|
| JavaScript errors | ___ | ___% |
| PHP errors | ___ | ___% |
| Database errors | ___ | ___% |
| Timeout errors | ___ | ___% |

## Data Collection Period
**Start Date**: _______________  
**End Date**: _______________  
**Total Days**: 7

## Analysis Tools

### Parse Logs
```bash
# Extract SLOS baseline metrics from error log
grep "SLOS_BASELINE" /path/to/error.log > slos-baseline-metrics.txt

# Calculate averages
cat slos-baseline-metrics.txt | grep "accessibility" | awk -F'Time=' '{print $2}' | awk -F's' '{sum+=$1; count++} END {print "Average Time:", sum/count, "seconds"}'
```

### Monitor Database
```sql
-- Check table sizes before refactoring
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'wordpress_db'
AND table_name LIKE '%slos_%'
ORDER BY (data_length + index_length) DESC;

-- Check slow query log
SELECT * FROM mysql.slow_log 
WHERE sql_text LIKE '%slos_%' 
ORDER BY query_time DESC 
LIMIT 20;
```

## Success Criteria for Phase 4

After Phase 4 implementation, the refactored code must meet these targets:

### Performance Targets
- [ ] Scan time: ≤ baseline (no regression)
- [ ] Auto-fix time: ≤ baseline (no regression)
- [ ] Memory usage: ≤ baseline + 10% (acceptable increase for better architecture)
- [ ] Database queries: ≤ baseline (no additional queries)

### Code Quality Targets
- [ ] Test coverage: ≥ 80%
- [ ] No new errors in logs
- [ ] All unit tests passing
- [ ] All integration tests passing

### Architecture Targets
- [ ] AccessibilityScanner.php: < 500 lines (currently 2792 lines)
- [ ] Service classes: < 300 lines each
- [ ] Controller classes: < 200 lines each
- [ ] 100% SOLID compliance
- [ ] Zero code duplication

## Post-Refactoring Comparison

**Note**: This section will be filled after Phase 4 implementation is complete.

### Performance Comparison
| Metric | Baseline | Post-Refactor | Change |
|--------|----------|---------------|--------|
| Avg scan time | ___ | ___ | ___% |
| Avg fix time | ___ | ___ | ___% |
| Peak memory | ___ | ___ | ___% |
| DB queries | ___ | ___ | ___% |

### Code Metrics Comparison
| Metric | Baseline | Post-Refactor | Change |
|--------|----------|---------------|--------|
| Total lines | 2792 | ___ | ___% |
| Cyclomatic complexity | ___ | ___ | ___% |
| Test coverage | 0% | ___% | +___% |
| Duplication | ___% | ___% | ___% |

## Notes
- Collection period should be during normal production usage
- Include both peak and off-peak hours
- Document any anomalies or unusual events during collection period
- Keep this document updated as metrics are collected
