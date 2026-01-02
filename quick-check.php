<?php
// Quick check of scan data
require_once '/var/www/html/wp-load.php';
global $wpdb;

echo "Scan Results Check:\n";
$results = $wpdb->get_results("
    SELECT p.ID, p.post_title, 
           pm1.meta_value as issues,
           pm2.meta_value as score
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'slos_accessibility_issues_count'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'slos_accessibility_score'
    WHERE pm1.meta_value IS NOT NULL AND CAST(pm1.meta_value AS UNSIGNED) > 0
    ORDER BY CAST(pm1.meta_value AS UNSIGNED) DESC
    LIMIT 5
");

foreach ($results as $r) {
    echo "{$r->post_title}: {$r->issues} issues, " . round($r->score) . "% score\n";
}

echo "\nBackup Check:\n";
$backup_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}slos_accessibility_fix_history WHERE original_content IS NOT NULL");
echo "Pages with backups: $backup_count\n";
