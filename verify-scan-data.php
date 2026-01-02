<?php
/**
 * Verify Scan Results and BackupService Data
 * 
 * Checks if the data shown in the UI matches database records
 */

require_once '/var/www/html/wp-load.php';

global $wpdb;

echo "=== Accessibility Scanner Data Verification ===\n\n";

// 1. Check scan results table
echo "1. CHECKING SCAN RESULTS (Pages Requiring Attention)\n";
echo str_repeat('-', 80) . "\n";

// Get pages with accessibility issues
$results = $wpdb->get_results("
    SELECT 
        p.ID,
        p.post_title,
        pm1.meta_value as issues_found,
        pm2.meta_value as accessibility_score,
        pm3.meta_value as last_scanned
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'slos_accessibility_issues_count'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'slos_accessibility_score'
    LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'slos_last_scanned'
    WHERE p.post_status = 'publish'
    AND p.post_type IN ('post', 'page')
    AND pm1.meta_value IS NOT NULL
    AND CAST(pm1.meta_value AS UNSIGNED) > 0
    ORDER BY CAST(pm1.meta_value AS UNSIGNED) DESC
    LIMIT 10
", ARRAY_A);

printf("%-8s %-40s %-8s %-8s %-10s\n", "ID", "Title", "Issues", "Score", "Scanned");
echo str_repeat('-', 80) . "\n";

foreach ($results as $row) {
    printf(
        "%-8s %-40s %-8s %-8s %-10s\n",
        $row['ID'],
        substr($row['post_title'], 0, 38),
        $row['issues_found'] ?? 'N/A',
        $row['accessibility_score'] ? round($row['accessibility_score'], 0) . '%' : 'N/A',
        $row['last_scanned'] ? date('Y-m-d', strtotime($row['last_scanned'])) : 'Never'
    );
}

echo "\n";

// 2. Check detailed issues for first page
if (!empty($results)) {
    $first_page = $results[0];
    echo "2. CHECKING DETAILED ISSUES FOR: {$first_page['post_title']}\n";
    echo str_repeat('-', 80) . "\n";
    
    $issues = get_post_meta($first_page['ID'], 'slos_accessibility_issues', true);
    
    if ($issues && is_array($issues)) {
        $issue_counts = array();
        foreach ($issues as $issue) {
            $type = $issue['type'] ?? 'unknown';
            if (!isset($issue_counts[$type])) {
                $issue_counts[$type] = 0;
            }
            $issue_counts[$type]++;
        }
        
        echo "Issue Breakdown:\n";
        foreach ($issue_counts as $type => $count) {
            printf("  - %-30s: %d issues\n", $type, $count);
        }
        echo "\nTotal Issues: " . count($issues) . "\n";
    } else {
        echo "No detailed issues found\n";
    }
    
    echo "\n";
    
    // 3. Check if backup exists for this page
    echo "3. CHECKING BACKUP SERVICE FOR POST ID: {$first_page['ID']}\n";
    echo str_repeat('-', 80) . "\n";
    
    require_once '/var/www/html/wp-content/plugins/Shahi LegalOps Suite - 3.1.1/includes/Modules/AccessibilityScanner/Services/BackupService.php';
    
    $backupService = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\BackupService();
    
    $has_backup = $backupService->has_backup($first_page['ID']);
    echo "Has Backup: " . ($has_backup ? 'YES ✓' : 'NO ✗') . "\n";
    
    if ($has_backup) {
        $latest_backup = $backupService->get_latest_backup($first_page['ID']);
        if ($latest_backup) {
            echo "Latest Backup:\n";
            echo "  - Backup ID: {$latest_backup['id']}\n";
            echo "  - Created: {$latest_backup['created_at']}\n";
            echo "  - Content Size: " . strlen($latest_backup['original_content']) . " bytes\n";
            echo "  - Has Metadata: " . (!empty($latest_backup['metadata']) ? 'YES' : 'NO') . "\n";
            
            if (!empty($latest_backup['metadata'])) {
                echo "  - Metadata: " . json_encode($latest_backup['metadata']) . "\n";
            }
        }
        
        // Get all backups for this post
        $all_backups = $backupService->get_backups_by_post($first_page['ID'], 5);
        echo "\nTotal Backups Available: " . count($all_backups) . "\n";
        
        if (count($all_backups) > 1) {
            echo "Recent Backups:\n";
            foreach ($all_backups as $idx => $backup) {
                echo "  " . ($idx + 1) . ". ID {$backup['id']} - {$backup['created_at']}\n";
            }
        }
    }
    
    echo "\n";
}

// 4. Check fix history
echo "4. CHECKING FIX HISTORY\n";
echo str_repeat('-', 80) . "\n";

$fix_history = $wpdb->get_results("
    SELECT 
        post_id,
        fixer_id,
        fixed_count,
        action,
        created_at,
        CASE 
            WHEN original_content IS NOT NULL THEN 'YES'
            ELSE 'NO'
        END as has_content
    FROM {$wpdb->prefix}slos_accessibility_fix_history
    ORDER BY created_at DESC
    LIMIT 10
", ARRAY_A);

printf("%-8s %-30s %-6s %-12s %-10s %-12s\n", "Post ID", "Fixer", "Fixed", "Action", "Content", "Date");
echo str_repeat('-', 80) . "\n";

foreach ($fix_history as $row) {
    printf(
        "%-8s %-30s %-6s %-12s %-10s %-12s\n",
        $row['post_id'],
        substr($row['fixer_id'], 0, 28),
        $row['fixed_count'],
        $row['action'],
        $row['has_content'],
        date('Y-m-d H:i', strtotime($row['created_at']))
    );
}

echo "\n";

// 5. BackupService Statistics
echo "5. BACKUP SERVICE STATISTICS\n";
echo str_repeat('-', 80) . "\n";

$stats = $backupService->get_statistics();

echo "Total Backups: {$stats['total_backups']}\n";
echo "Unique Posts: {$stats['unique_posts']}\n";
echo "Total Size: {$stats['total_size_mb']} MB ({$stats['total_size_bytes']} bytes)\n";
echo "Oldest Backup: {$stats['oldest_backup']}\n";
echo "Newest Backup: {$stats['newest_backup']}\n";

echo "\n";

// 6. Verify UI Data Matches Database
echo "6. UI DATA VERIFICATION\n";
echo str_repeat('-', 80) . "\n";

$ui_pages = [
    ['title' => 'A11y Demo 7: Keyboard & Interaction', 'expected_issues' => 15, 'expected_score' => 55],
    ['title' => 'Test: ARIA Accessibility Issues', 'expected_issues' => 12, 'expected_score' => 64],
    ['title' => 'A11y Demo 8: ARIA Attributes', 'expected_issues' => 11, 'expected_score' => 60],
    ['title' => 'A11y Demo 1: Images & Headings', 'expected_issues' => 10, 'expected_score' => 42],
    ['title' => 'Test: Image Accessibility Issues', 'expected_issues' => 10, 'expected_score' => 56],
];

$mismatches = 0;
foreach ($ui_pages as $ui_page) {
    $db_page = $wpdb->get_row($wpdb->prepare("
        SELECT 
            p.ID,
            p.post_title,
            pm1.meta_value as issues,
            pm2.meta_value as score
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'slos_accessibility_issues_count'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'slos_accessibility_score'
        WHERE p.post_title = %s
        LIMIT 1
    ", $ui_page['title']), ARRAY_A);
    
    if ($db_page) {
        $issues_match = (int)$db_page['issues'] === $ui_page['expected_issues'];
        $score_match = round((float)$db_page['score'], 0) === $ui_page['expected_score'];
        
        $status = ($issues_match && $score_match) ? '✓ MATCH' : '✗ MISMATCH';
        if (!$issues_match || !$score_match) {
            $mismatches++;
        }
        
        echo "$status: {$ui_page['title']}\n";
        if (!$issues_match) {
            echo "  Issues: UI={$ui_page['expected_issues']}, DB={$db_page['issues']}\n";
        }
        if (!$score_match) {
            echo "  Score: UI={$ui_page['expected_score']}%, DB=" . round((float)$db_page['score'], 0) . "%\n";
        }
    } else {
        echo "✗ NOT FOUND: {$ui_page['title']}\n";
        $mismatches++;
    }
}

echo "\n";
echo "Total Mismatches: $mismatches\n";

if ($mismatches === 0) {
    echo "\n✅ ALL UI DATA MATCHES DATABASE RECORDS!\n";
} else {
    echo "\n⚠️  SOME DISCREPANCIES FOUND - Please review above\n";
}

echo "\n=== Verification Complete ===\n";
