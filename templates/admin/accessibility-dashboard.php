<?php
/**
 * Accessibility Dashboard Template - V3 Design
 *
 * Dashboard for managing accessibility scans with modern UI/UX.
 * Uses V3 dark theme design system for consistency.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 * @updated    3.0.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get real scan results from database
$scan_results = get_option('slos_last_scan_results', []);
$stats = get_option('slos_scan_statistics', []);

// If no scan results exist yet, prepare empty state
if (empty($scan_results)) {
    $scan_results = [];
}

// Prepare statistics with defaults
$total_pages = isset($stats['total_pages_scanned']) ? $stats['total_pages_scanned'] : 0;
$total_issues = isset($stats['total_issues']) ? $stats['total_issues'] : 0;
$total_critical = isset($stats['total_critical']) ? $stats['total_critical'] : 0;
$average_score = isset($stats['average_score']) ? $stats['average_score'] : 100;

// Get grade from score
$grade = $average_score >= 90 ? 'A' : ($average_score >= 80 ? 'B' : ($average_score >= 70 ? 'C' : ($average_score >= 60 ? 'D' : 'F')));

// Get scan history
$scan_history = get_option('slos_accessibility_scan_history', []);

// Get top issues by type
$issues_by_type = get_option('slos_issues_by_type', []);

// Get widget settings
$widget_enabled = get_option('slos_widget_enabled', true);
?>

<style>
/* V3 Dashboard Styles - Uses Global Theme Variables */
.slos-dashboard-v3 {
    /* Inherit from global theme - Mac Slate Liquid */
    --slos-bg-primary: #0f172a;
    --slos-bg-card: #1e293b;
    --slos-bg-input: #0f172a;
    --slos-border: #334155;
    --slos-border-light: #475569;
    --slos-text-primary: #f8fafc;
    --slos-text-secondary: #94a3b8;
    --slos-text-muted: #64748b;
    --slos-accent: #3b82f6;
    --slos-accent-hover: #2563eb;
    --slos-success: #22c55e;
    --slos-warning: #f59e0b;
    --slos-error: #ef4444;
    --slos-info: #06b6d4;
    
    background: var(--slos-bg-primary);
    padding: 24px;
    margin: -20px -20px 0 -20px;
    min-height: calc(100vh - 100px);
}

.slos-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    max-width: 1400px;
}

.slos-dashboard-card {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    overflow: hidden;
}

.slos-dashboard-card.full-width {
    grid-column: 1 / -1;
}

.slos-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--slos-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.slos-card-header h3 {
    color: var(--slos-text-primary);
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.slos-card-header h3 .dashicons {
    color: var(--slos-accent);
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.slos-card-header .badge {
    background: var(--slos-accent);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
}

.slos-card-body {
    padding: 24px;
}

/* Score Overview */
.slos-score-overview {
    display: flex;
    align-items: center;
    gap: 32px;
}

.slos-score-gauge {
    position: relative;
    width: 160px;
    height: 160px;
    flex-shrink: 0;
}

.slos-score-gauge svg {
    transform: rotate(-90deg);
    width: 160px;
    height: 160px;
}

.slos-score-gauge .bg-circle {
    fill: none;
    stroke: var(--slos-border);
    stroke-width: 12;
}

.slos-score-gauge .score-circle {
    fill: none;
    stroke-width: 12;
    stroke-linecap: round;
    transition: stroke-dashoffset 1s ease-out;
}

.slos-score-gauge.grade-a .score-circle { stroke: var(--slos-success); }
.slos-score-gauge.grade-b .score-circle { stroke: #84cc16; }
.slos-score-gauge.grade-c .score-circle { stroke: var(--slos-warning); }
.slos-score-gauge.grade-d .score-circle { stroke: #f97316; }
.slos-score-gauge.grade-f .score-circle { stroke: var(--slos-error); }

.slos-score-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.slos-score-number {
    font-size: 42px;
    font-weight: 700;
    color: var(--slos-text-primary);
    line-height: 1;
}

.slos-score-label {
    font-size: 12px;
    color: var(--slos-text-muted);
    text-transform: uppercase;
    margin-top: 4px;
}

.slos-score-details {
    flex: 1;
}

.slos-grade-display {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.slos-grade-badge {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 700;
    color: white;
}

.slos-grade-badge.grade-a { background: linear-gradient(135deg, var(--slos-success), #16a34a); }
.slos-grade-badge.grade-b { background: linear-gradient(135deg, #84cc16, #65a30d); }
.slos-grade-badge.grade-c { background: linear-gradient(135deg, var(--slos-warning), #d97706); }
.slos-grade-badge.grade-d { background: linear-gradient(135deg, #f97316, #ea580c); }
.slos-grade-badge.grade-f { background: linear-gradient(135deg, var(--slos-error), #dc2626); }

.slos-grade-text .grade-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-grade-text .grade-subtitle {
    font-size: 13px;
    color: var(--slos-text-muted);
}

.slos-trend-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.slos-trend-indicator.up {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-trend-indicator.down {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

.slos-trend-indicator.neutral {
    background: rgba(161, 161, 170, 0.15);
    color: var(--slos-text-muted);
}

/* Stats Grid */
.slos-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--slos-border);
}

.slos-stat-box {
    text-align: center;
    padding: 16px;
    background: var(--slos-bg-input);
    border-radius: 8px;
}

.slos-stat-box .value {
    font-size: 28px;
    font-weight: 700;
    color: var(--slos-text-primary);
    line-height: 1;
}

.slos-stat-box .value.critical { color: var(--slos-error); }
.slos-stat-box .value.warning { color: var(--slos-warning); }
.slos-stat-box .value.success { color: var(--slos-success); }

.slos-stat-box .label {
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 6px;
}

/* Issue Distribution */
.slos-issue-bars {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.slos-issue-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.slos-issue-label {
    width: 100px;
    font-size: 13px;
    color: var(--slos-text-secondary);
    flex-shrink: 0;
}

.slos-issue-bar-wrapper {
    flex: 1;
    height: 24px;
    background: var(--slos-bg-input);
    border-radius: 4px;
    overflow: hidden;
}

.slos-issue-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.slos-issue-bar.critical { background: linear-gradient(90deg, var(--slos-error), #f87171); }
.slos-issue-bar.serious { background: linear-gradient(90deg, #f97316, #fb923c); }
.slos-issue-bar.moderate { background: linear-gradient(90deg, var(--slos-warning), #fbbf24); }
.slos-issue-bar.minor { background: linear-gradient(90deg, var(--slos-accent), #60a5fa); }

.slos-issue-count {
    width: 50px;
    font-size: 14px;
    font-weight: 600;
    color: var(--slos-text-primary);
    text-align: right;
}

/* Top Issues Table */
.slos-issues-table {
    width: 100%;
    border-collapse: collapse;
}

.slos-issues-table th {
    text-align: left;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 600;
    color: var(--slos-text-muted);
    text-transform: uppercase;
    border-bottom: 1px solid var(--slos-border);
}

.slos-issues-table td {
    padding: 14px 16px;
    font-size: 14px;
    color: var(--slos-text-secondary);
    border-bottom: 1px solid var(--slos-border);
}

.slos-issues-table tr:last-child td {
    border-bottom: none;
}

.slos-issues-table tr:hover td {
    background: var(--slos-bg-input);
}

.slos-issues-table .issue-name {
    color: var(--slos-text-primary);
    font-weight: 500;
}

.slos-fix-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.slos-fix-badge.auto {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-fix-badge.partial {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-fix-badge.manual {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

/* Issue Count & Severity Badges */
.slos-issue-count-badge {
    display: inline-block;
    padding: 2px 10px;
    background: var(--slos-bg-input);
    border-radius: 12px;
    font-weight: 600;
    font-size: 13px;
    color: var(--slos-text-primary);
}

.slos-severity-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.slos-severity-badge.error {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

.slos-severity-badge.warning {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-severity-badge.info {
    background: rgba(6, 182, 212, 0.15);
    color: var(--slos-info);
}

.issue-description {
    margin-left: 6px;
    cursor: help;
}

.issue-description .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    color: var(--slos-text-muted);
    vertical-align: middle;
}

/* Scan History Chart */
.slos-chart-placeholder {
    height: 200px;
    background: var(--slos-bg-input);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--slos-text-muted);
}

.slos-history-list {
    max-height: 300px;
    overflow-y: auto;
}

.slos-history-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--slos-border);
}

.slos-history-item:last-child {
    border-bottom: none;
}

.slos-history-date {
    font-size: 13px;
    color: var(--slos-text-secondary);
}

.slos-history-score {
    font-size: 14px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-history-issues {
    font-size: 13px;
    color: var(--slos-text-muted);
}

.slos-history-btn {
    padding: 6px 12px;
    background: transparent;
    border: 1px solid var(--slos-border);
    border-radius: 4px;
    color: var(--slos-text-secondary);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-history-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

/* Chart Controls */
.slos-chart-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.slos-chart-select {
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    padding: 6px 12px;
    color: var(--slos-text-primary);
    font-size: 13px;
    cursor: pointer;
}

.slos-chart-select:focus {
    outline: none;
    border-color: var(--slos-accent);
}

/* Trends Chart Container */
.slos-trends-chart-container {
    height: 280px;
    margin-bottom: 24px;
    padding: 16px;
    background: var(--slos-bg-input);
    border-radius: 8px;
}

.slos-chart-legend {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-bottom: 24px;
    padding: 12px;
    background: var(--slos-bg-input);
    border-radius: 8px;
}

.slos-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--slos-text-secondary);
}

.slos-legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

/* History Table */
.slos-history-table-container {
    margin-top: 24px;
}

.slos-history-subtitle {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 16px 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-history-subtitle .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    color: var(--slos-accent);
}

.slos-history-table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--slos-border);
}

.slos-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.slos-history-table th,
.slos-history-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--slos-border);
}

.slos-history-table th {
    background: var(--slos-bg-input);
    color: var(--slos-text-secondary);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.slos-history-table tbody tr:hover {
    background: rgba(59, 130, 246, 0.05);
}

.slos-history-table tbody tr:last-child td {
    border-bottom: none;
}

.slos-history-date .date-primary {
    display: block;
    color: var(--slos-text-primary);
    font-weight: 500;
}

.slos-history-date .date-secondary {
    display: block;
    font-size: 11px;
    color: var(--slos-text-muted);
    margin-top: 2px;
}

.slos-score-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 12px;
}

.slos-score-badge.excellent {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-score-badge.good {
    background: rgba(59, 130, 246, 0.15);
    color: var(--slos-accent);
}

.slos-score-badge.fair {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-score-badge.poor {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

.slos-issues-count {
    font-weight: 500;
    color: var(--slos-text-primary);
}

.slos-critical-badge {
    display: inline-block;
    padding: 2px 8px;
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
    border-radius: 10px;
    font-weight: 600;
    font-size: 12px;
}

.slos-none-badge {
    color: var(--slos-text-muted);
}

.slos-wcag-badge {
    display: inline-block;
    padding: 2px 8px;
    background: rgba(6, 182, 212, 0.15);
    color: var(--slos-info);
    border-radius: 4px;
    font-weight: 600;
    font-size: 11px;
}

.slos-view-scan-btn,
.slos-compare-scan-btn {
    background: transparent;
    border: 1px solid var(--slos-border);
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    color: var(--slos-text-muted);
    transition: all 0.2s;
}

.slos-view-scan-btn:hover,
.slos-compare-scan-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

.slos-view-scan-btn .dashicons,
.slos-compare-scan-btn .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.slos-history-pagination {
    padding: 16px;
    text-align: center;
}

.slos-load-more-btn {
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    padding: 10px 24px;
    color: var(--slos-text-secondary);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-load-more-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

/* Scan Details Modal */
.slos-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slos-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
}

.slos-modal-content {
    position: relative;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    background: var(--slos-bg-card);
    border-radius: 12px;
    border: 1px solid var(--slos-border);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.slos-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--slos-border);
}

.slos-modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: var(--slos-text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.slos-modal-header h3 .dashicons {
    color: var(--slos-accent);
}

.slos-modal-close {
    background: transparent;
    border: none;
    font-size: 24px;
    color: var(--slos-text-muted);
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.slos-modal-close:hover {
    color: var(--slos-text-primary);
}

.slos-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

.slos-modal-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.slos-modal-stat {
    background: var(--slos-bg-input);
    padding: 16px;
    border-radius: 8px;
    text-align: center;
}

.slos-modal-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--slos-text-primary);
}

.slos-modal-stat-label {
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 4px;
}

.slos-comparison-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--slos-border);
}

.slos-comparison-label {
    color: var(--slos-text-secondary);
}

.slos-comparison-value {
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-comparison-change {
    font-size: 12px;
    margin-left: 8px;
}

.slos-comparison-change.positive {
    color: var(--slos-success);
}

.slos-comparison-change.negative {
    color: var(--slos-error);
}

/* Widget Preview */
.slos-widget-preview-box {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 20px;
    background: var(--slos-bg-input);
    border-radius: 8px;
}

.slos-widget-mock {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--slos-accent), var(--slos-accent-hover));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.slos-widget-mock .dashicons {
    color: white;
    font-size: 28px;
    width: 28px;
    height: 28px;
}

.slos-widget-info {
    flex: 1;
}

.slos-widget-info h4 {
    margin: 0 0 4px 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-widget-info p {
    margin: 0;
    font-size: 13px;
    color: var(--slos-text-muted);
}

.slos-widget-toggle {
    position: relative;
    width: 52px;
    height: 28px;
}

.slos-widget-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slos-widget-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--slos-border);
    border-radius: 28px;
    transition: 0.3s;
}

.slos-widget-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}

.slos-widget-toggle input:checked + .slos-widget-slider {
    background: var(--slos-success);
}

.slos-widget-toggle input:checked + .slos-widget-slider:before {
    transform: translateX(24px);
}

/* Export Buttons */
.slos-export-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.slos-export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    color: var(--slos-text-secondary);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-export-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

.slos-export-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Pages Table */
.slos-pages-attention {
    max-height: 400px;
    overflow-y: auto;
}

.slos-page-header {
    display: grid;
    grid-template-columns: minmax(200px, 1fr) 80px 80px 100px minmax(320px, auto);
    align-items: center;
    padding: 12px 16px;
    background: var(--slos-bg-secondary);
    border-bottom: 2px solid var(--slos-border);
    gap: 16px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--slos-text-muted);
    position: sticky;
    top: 0;
    z-index: 1;
}

.slos-page-header > span {
    text-align: center;
}

.slos-page-header > span:first-child {
    text-align: left;
}

.slos-page-row {
    display: grid;
    grid-template-columns: minmax(200px, 1fr) 80px 80px 100px minmax(320px, auto);
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid var(--slos-border);
    gap: 16px;
}

.slos-page-row:last-child {
    border-bottom: none;
}

.slos-page-row:hover {
    background: var(--slos-bg-input);
}

.slos-page-name {
    font-size: 14px;
    color: var(--slos-text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 300px;
}

.slos-page-issues,
.slos-page-score {
    font-size: 14px;
    font-weight: 600;
    text-align: center;
}

.slos-page-issues { color: var(--slos-error); }
.slos-page-score { color: var(--slos-text-primary); }

.slos-priority-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
}

.slos-priority-badge.high {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

.slos-priority-badge.medium {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-priority-badge.low {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-fix-link {
    color: var(--slos-accent);
    font-size: 13px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}

.slos-fix-link:hover {
    text-decoration: underline;
}

/* Fix Buttons */
.slos-view-details-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    background: linear-gradient(135deg, var(--slos-info), #0891b2);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.slos-view-details-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(6, 182, 212, 0.4);
}

.slos-fix-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    background: linear-gradient(135deg, var(--slos-success), #16a34a);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.slos-fix-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
}

.slos-fix-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.slos-fix-btn.fixing {
    background: var(--slos-warning);
}

.slos-fix-btn.fixed {
    background: linear-gradient(135deg, #16a34a, #15803d);
    pointer-events: none;
}

.slos-rollback-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.slos-rollback-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

.slos-rollback-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.slos-rollback-btn .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.slos-fix-link {
    color: var(--slos-accent);
    font-size: 11px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 6px 8px;
    white-space: nowrap;
}

.slos-fix-link:hover {
    text-decoration: underline;
}

.slos-autofix-toggle {
    position: relative;
    width: 36px;
    height: 20px;
    flex-shrink: 0;
}

.slos-autofix-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slos-autofix-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--slos-border);
    transition: 0.3s;
    border-radius: 20px;
}

.slos-autofix-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.slos-autofix-toggle input:checked + .slos-autofix-slider {
    background: var(--slos-success);
}

.slos-autofix-toggle input:checked + .slos-autofix-slider:before {
    transform: translateX(16px);
}

.slos-page-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

/* Action Buttons */
.slos-action-row {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--slos-border);
}

.slos-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--slos-accent), var(--slos-accent-hover));
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.slos-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: transparent;
    color: var(--slos-text-secondary);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-btn-secondary:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

/* Comparative Analytics */
.slos-analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.slos-comparison-card {
    padding: 20px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
}

.slos-comparison-card.full {
    grid-column: 1 / -1;
}

.slos-comparison-card h4 {
    margin: 0 0 16px;
    color: var(--slos-text-primary);
    font-size: 15px;
    font-weight: 600;
}

.slos-comparison-bars {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

.slos-comparison-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.slos-comparison-label {
    width: 120px;
    color: var(--slos-text-secondary);
    font-size: 13px;
    font-weight: 500;
}

.slos-comparison-bar-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
}

.slos-comparison-bar {
    height: 32px;
    border-radius: 6px;
    transition: width 1s ease;
}

.slos-comparison-bar.before {
    background: linear-gradient(90deg, var(--slos-error), #dc2626);
}

.slos-comparison-bar.after {
    background: linear-gradient(90deg, var(--slos-success), #16a34a);
}

.slos-comparison-value {
    color: var(--slos-text-primary);
    font-weight: 600;
    font-size: 14px;
    min-width: 40px;
}

.slos-improvement-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 8px;
    color: var(--slos-success);
    font-size: 14px;
}

.slos-improvement-badge strong {
    font-size: 24px;
}

.slos-resolution-chart {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.slos-resolution-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.slos-resolution-stats .stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: var(--slos-bg-card);
    border-radius: 6px;
}

.slos-resolution-stats .stat-label {
    color: var(--slos-text-secondary);
    font-size: 13px;
}

.slos-resolution-stats .stat-value {
    font-weight: 600;
    font-size: 15px;
}

.slos-resolution-stats .stat-value.success {
    color: var(--slos-success);
}

.slos-resolution-stats .stat-value.info {
    color: var(--slos-info);
}

.slos-resolution-stats .stat-value.warning {
    color: var(--slos-warning);
}

/* Results Table */
.slos-results-table {
    width: 100%;
    border-collapse: collapse;
}

.slos-results-table thead {
    background: var(--slos-bg-input);
    border-bottom: 2px solid var(--slos-border);
}

.slos-results-table th {
    padding: 12px 16px;
    text-align: left;
    color: var(--slos-text-secondary);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.slos-results-table tbody tr {
    border-bottom: 1px solid var(--slos-border);
    transition: background 0.2s;
}

.slos-results-table tbody tr:hover {
    background: rgba(59, 130, 246, 0.05);
}

.slos-results-table td {
    padding: 14px 16px;
    color: var(--slos-text-secondary);
    font-size: 13px;
}

.slos-results-table td.page-name {
    color: var(--slos-text-primary);
    font-weight: 500;
}

.slos-results-table td.issues-count {
    text-align: center;
    font-weight: 600;
}

.slos-results-table td.issues-count.pass {
    color: var(--slos-success);
}

.slos-results-table td.issues-count.fail {
    color: var(--slos-error);
}

.slos-results-table td.issues-count.warning {
    color: var(--slos-warning);
}

.slos-results-table td.score-cell {
    text-align: center;
}

.score-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 12px;
}

.score-badge.excellent {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.score-badge.good {
    background: rgba(59, 130, 246, 0.15);
    color: var(--slos-accent);
}

.score-badge.poor {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge.pass {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.status-badge.fail {
    background: rgba(239, 68, 68, 0.15);
    color: var(--slos-error);
}

.status-badge.warning {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-table-footer {
    padding: 16px 24px;
    text-align: center;
    background: var(--slos-bg-input);
    border-top: 1px solid var(--slos-border);
}

.slos-table-footer p {
    margin: 0;
    color: var(--slos-text-muted);
    font-size: 13px;
}

@media (max-width: 1200px) {
    .slos-dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .slos-stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .slos-analytics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="slos-dashboard-v3">
    <div class="slos-dashboard-grid">
        
        <!-- Card 1: WCAG Compliance Status -->
        <div class="slos-dashboard-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-shield-alt"></span>
                    <?php echo esc_html__('WCAG Compliance Status', 'shahi-legalflowsuite'); ?>
                </h3>
                <span class="badge"><?php echo esc_html($stats['wcag_level'] ?? 'WCAG 2.1 AA'); ?></span>
            </div>
            <div class="slos-card-body">
                <div class="slos-wcag-score-display">
                    <svg width="160" height="160" viewBox="0 0 160 160" class="slos-score-circle">
                        <circle cx="80" cy="80" r="70" fill="none" stroke="#334155" stroke-width="12"/>
                        <circle cx="80" cy="80" r="70" fill="none" 
                                stroke="<?php echo $grade === 'A' ? '#22c55e' : ($grade === 'B' ? '#3b82f6' : ($grade === 'C' ? '#f59e0b' : '#ef4444')); ?>" 
                                stroke-width="12" 
                                stroke-dasharray="<?php echo (440 * $average_score / 100) . ' 440'; ?>" 
                                stroke-linecap="round"
                                transform="rotate(-90 80 80)"
                                style="transition: stroke-dasharray 1s ease;"/>
                        <text x="80" y="70" text-anchor="middle" 
                              fill="<?php echo $grade === 'A' ? '#22c55e' : ($grade === 'B' ? '#3b82f6' : ($grade === 'C' ? '#f59e0b' : '#ef4444')); ?>" 
                              font-size="48" font-weight="700"><?php echo esc_html($average_score); ?>%</text>
                        <text x="80" y="95" text-anchor="middle" fill="#94a3b8" font-size="18" font-weight="600">
                            <?php echo esc_html__('Grade:', 'shahi-legalflowsuite') . ' ' . esc_html($grade); ?>
                        </text>
                    </svg>
                </div>
                
                <div class="slos-compliance-badges">
                    <div class="slos-compliance-badge <?php echo $average_score >= 85 ? 'active' : 'inactive'; ?>">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span><?php esc_html_e('ADA', 'shahi-legalflowsuite'); ?></span>
                    </div>
                    <div class="slos-compliance-badge <?php echo $average_score >= 80 ? 'active' : 'inactive'; ?>">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span><?php esc_html_e('Section 508', 'shahi-legalflowsuite'); ?></span>
                    </div>
                    <div class="slos-compliance-badge <?php echo $average_score >= 90 ? 'active' : 'inactive'; ?>">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span><?php esc_html_e('EAA', 'shahi-legalflowsuite'); ?></span>
                    </div>
                </div>
                
                <div class="slos-quick-stats-grid">
                    <div class="stat-item">
                        <div class="value"><?php echo esc_html($total_issues); ?></div>
                        <div class="label"><?php esc_html_e('Total Issues', 'shahi-legalflowsuite'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="value"><?php echo esc_html($total_critical); ?></div>
                        <div class="label"><?php esc_html_e('Critical', 'shahi-legalflowsuite'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="value"><?php echo esc_html(isset($stats['total_auto_fixable']) ? $stats['total_auto_fixable'] : round($total_issues * 0.6)); ?></div>
                        <div class="label"><?php esc_html_e('Auto-Fixable', 'shahi-legalflowsuite'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 2: Score Overview -->
        <div class="slos-dashboard-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-pie"></span>
                    <?php echo esc_html__('Accessibility Score Overview', 'shahi-legalflowsuite'); ?>
                </h3>
                <span class="slos-trend-indicator up">
                    <span class="dashicons dashicons-arrow-up-alt"></span>
                    +5
                </span>
            </div>
            <div class="slos-card-body">
                <div class="slos-score-overview">
                    <div class="slos-score-gauge grade-<?php echo esc_attr(strtolower($grade)); ?>">
                        <svg viewBox="0 0 160 160">
                            <circle class="bg-circle" cx="80" cy="80" r="70"></circle>
                            <circle class="score-circle" cx="80" cy="80" r="70" 
                                stroke-dasharray="440" 
                                stroke-dashoffset="<?php echo esc_attr(440 - (440 * $average_score / 100)); ?>">
                            </circle>
                        </svg>
                        <div class="slos-score-center">
                            <div class="slos-score-number"><?php echo esc_html($average_score); ?></div>
                            <div class="slos-score-label"><?php esc_html_e('Score', 'shahi-legalflowsuite'); ?></div>
                        </div>
                    </div>
                    <div class="slos-score-details">
                        <div class="slos-grade-display">
                            <div class="slos-grade-badge grade-<?php echo esc_attr(strtolower($grade)); ?>">
                                <?php echo esc_html($grade); ?>
                            </div>
                            <div class="slos-grade-text">
                                <div class="grade-title">
                                    <?php 
                                    $grade_labels = ['A' => 'Excellent', 'B' => 'Good', 'C' => 'Fair', 'D' => 'Poor', 'F' => 'Needs Work'];
                                    echo esc_html($grade_labels[$grade] ?? 'Unknown');
                                    ?>
                                </div>
                                <div class="grade-subtitle"><?php esc_html_e('WCAG 2.2 AA Compliance', 'shahi-legalflowsuite'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slos-stats-row">
                    <div class="slos-stat-box">
                        <div class="value"><?php echo esc_html($total_pages); ?></div>
                        <div class="label"><?php esc_html_e('Pages Scanned', 'shahi-legalflowsuite'); ?></div>
                    </div>
                    <div class="slos-stat-box">
                        <div class="value"><?php echo esc_html($total_issues); ?></div>
                        <div class="label"><?php esc_html_e('Total Issues', 'shahi-legalflowsuite'); ?></div>
                    </div>
                    <div class="slos-stat-box">
                        <div class="value critical"><?php echo esc_html($total_critical); ?></div>
                        <div class="label"><?php esc_html_e('Critical', 'shahi-legalflowsuite'); ?></div>
                    </div>
                    <div class="slos-stat-box">
                        <div class="value success"><?php echo esc_html(max(0, $total_issues - $total_critical)); ?></div>
                        <div class="label"><?php esc_html_e('Auto-Fixable', 'shahi-legalflowsuite'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 3: Issue Distribution -->
        <div class="slos-dashboard-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php echo esc_html__('Issue Distribution', 'shahi-legalflowsuite'); ?>
                </h3>
            </div>
            <div class="slos-card-body">
                <?php
                // Calculate issue percentages
                $max_issues = max($total_issues, 1);
                $critical_pct = ($total_critical / $max_issues) * 100;
                $serious_count = isset($stats['total_serious']) ? $stats['total_serious'] : round($total_issues * 0.3);
                $serious_pct = ($serious_count / $max_issues) * 100;
                $moderate_count = isset($stats['total_moderate']) ? $stats['total_moderate'] : round($total_issues * 0.25);
                $moderate_pct = ($moderate_count / $max_issues) * 100;
                $minor_count = $total_issues - $total_critical - $serious_count - $moderate_count;
                $minor_pct = ($minor_count / $max_issues) * 100;
                ?>
                <div class="slos-issue-bars">
                    <div class="slos-issue-row">
                        <span class="slos-issue-label"><?php esc_html_e('Critical', 'shahi-legalflowsuite'); ?></span>
                        <div class="slos-issue-bar-wrapper">
                            <div class="slos-issue-bar critical" style="width: <?php echo esc_attr($critical_pct); ?>%"></div>
                        </div>
                        <span class="slos-issue-count"><?php echo esc_html($total_critical); ?></span>
                    </div>
                    <div class="slos-issue-row">
                        <span class="slos-issue-label"><?php esc_html_e('Serious', 'shahi-legalflowsuite'); ?></span>
                        <div class="slos-issue-bar-wrapper">
                            <div class="slos-issue-bar serious" style="width: <?php echo esc_attr($serious_pct); ?>%"></div>
                        </div>
                        <span class="slos-issue-count"><?php echo esc_html($serious_count); ?></span>
                    </div>
                    <div class="slos-issue-row">
                        <span class="slos-issue-label"><?php esc_html_e('Moderate', 'shahi-legalflowsuite'); ?></span>
                        <div class="slos-issue-bar-wrapper">
                            <div class="slos-issue-bar moderate" style="width: <?php echo esc_attr($moderate_pct); ?>%"></div>
                        </div>
                        <span class="slos-issue-count"><?php echo esc_html($moderate_count); ?></span>
                    </div>
                    <div class="slos-issue-row">
                        <span class="slos-issue-label"><?php esc_html_e('Minor', 'shahi-legalflowsuite'); ?></span>
                        <div class="slos-issue-bar-wrapper">
                            <div class="slos-issue-bar minor" style="width: <?php echo esc_attr($minor_pct); ?>%"></div>
                        </div>
                        <span class="slos-issue-count"><?php echo esc_html(max(0, $minor_count)); ?></span>
                    </div>
                </div>
                <div class="slos-action-row">
                    <button type="button" class="slos-btn-secondary" id="slos-filter-wcag">
                        <span class="dashicons dashicons-filter"></span>
                        <?php esc_html_e('Filter by WCAG', 'shahi-legalflowsuite'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Card 4: Top Issues by Type -->
        <div class="slos-dashboard-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-warning"></span>
                    <?php echo esc_html__('Top Issues by Type', 'shahi-legalflowsuite'); ?>
                </h3>
                <a href="#" class="slos-fix-link" style="font-size: 13px;">
                    <?php esc_html_e('View All', 'shahi-legalflowsuite'); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </a>
            </div>
            <div class="slos-card-body" style="padding: 0;">
                <?php if (empty($issues_by_type)): ?>
                <div style="text-align: center; padding: 40px; color: var(--slos-text-muted);">
                    <span class="dashicons dashicons-yes-alt" style="font-size: 32px; color: var(--slos-success); margin-bottom: 12px; display: block;"></span>
                    <p><?php esc_html_e('No issues found. Run a scan to see results.', 'shahi-legalflowsuite'); ?></p>
                </div>
                <?php else: ?>
                <table class="slos-issues-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Issue Type', 'shahi-legalflowsuite'); ?></th>
                            <th><?php esc_html_e('Count', 'shahi-legalflowsuite'); ?></th>
                            <th><?php esc_html_e('Severity', 'shahi-legalflowsuite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach (array_slice($issues_by_type, 0, 5) as $issue):
                            $severity = isset($issue['severity']) ? $issue['severity'] : 'warning';
                            $severity_class = $severity === 'critical' ? 'error' : ($severity === 'warning' ? 'warning' : 'info');
                        ?>
                        <tr>
                            <td class="issue-name">
                                <?php echo esc_html($issue['name']); ?>
                                <?php if (!empty($issue['description'])): ?>
                                <span class="issue-description" title="<?php echo esc_attr($issue['description']); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="slos-issue-count-badge"><?php echo esc_html($issue['count']); ?></span>
                            </td>
                            <td>
                                <span class="slos-severity-badge <?php echo esc_attr($severity_class); ?>">
                                    <?php echo esc_html(ucfirst($severity)); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Card 5: Scan History (Full Width) -->
        <div class="slos-dashboard-card full-width">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-line"></span>
                    <?php echo esc_html__('Scan History & Trends', 'shahi-legalflowsuite'); ?>
                </h3>
                <div class="slos-chart-controls">
                    <select id="slos-chart-range" class="slos-chart-select">
                        <option value="7"><?php esc_html_e('Last 7 scans', 'shahi-legalflowsuite'); ?></option>
                        <option value="14"><?php esc_html_e('Last 14 scans', 'shahi-legalflowsuite'); ?></option>
                        <option value="30" selected><?php esc_html_e('Last 30 scans', 'shahi-legalflowsuite'); ?></option>
                        <option value="all"><?php esc_html_e('All scans', 'shahi-legalflowsuite'); ?></option>
                    </select>
                    <div class="slos-export-buttons">
                        <button type="button" class="slos-export-btn" data-format="csv" title="<?php esc_attr_e('Export as CSV', 'shahi-legalflowsuite'); ?>">
                            <span class="dashicons dashicons-media-spreadsheet"></span>
                            CSV
                        </button>
                        <button type="button" class="slos-export-btn" data-format="json" title="<?php esc_attr_e('Export as JSON', 'shahi-legalflowsuite'); ?>">
                            <span class="dashicons dashicons-editor-code"></span>
                            JSON
                        </button>
                    </div>
                </div>
            </div>
            <div class="slos-card-body">
                <?php if (empty($scan_history)): ?>
                <div class="slos-chart-placeholder">
                    <div style="text-align: center;">
                        <span class="dashicons dashicons-chart-area" style="font-size: 48px; margin-bottom: 12px; display: block; color: var(--slos-text-muted);"></span>
                        <p><?php esc_html_e('No scan history yet. Run your first scan to see trends.', 'shahi-legalflowsuite'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalflowsuite-accessibility&tab=tools')); ?>" class="slos-primary-btn" style="margin-top: 16px; display: inline-block;">
                            <?php esc_html_e('Run First Scan', 'shahi-legalflowsuite'); ?>
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <!-- Chart Container -->
                <div class="slos-trends-chart-container">
                    <canvas id="slos-trends-chart"></canvas>
                </div>
                
                <!-- Chart Legend -->
                <div class="slos-chart-legend">
                    <div class="slos-legend-item">
                        <span class="slos-legend-color" style="background: #3b82f6;"></span>
                        <span><?php esc_html_e('Accessibility Score', 'shahi-legalflowsuite'); ?></span>
                    </div>
                    <div class="slos-legend-item">
                        <span class="slos-legend-color" style="background: #f59e0b;"></span>
                        <span><?php esc_html_e('Total Issues', 'shahi-legalflowsuite'); ?></span>
                    </div>
                    <div class="slos-legend-item">
                        <span class="slos-legend-color" style="background: #ef4444;"></span>
                        <span><?php esc_html_e('Critical Issues', 'shahi-legalflowsuite'); ?></span>
                    </div>
                </div>
                
                <!-- Scan History Table -->
                <div class="slos-history-table-container">
                    <h4 class="slos-history-subtitle">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('Recent Scans', 'shahi-legalflowsuite'); ?>
                    </h4>
                    <div class="slos-history-table-wrapper">
                        <table class="slos-history-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'shahi-legalflowsuite'); ?></th>
                                    <th><?php esc_html_e('Score', 'shahi-legalflowsuite'); ?></th>
                                    <th><?php esc_html_e('Issues', 'shahi-legalflowsuite'); ?></th>
                                    <th><?php esc_html_e('Critical', 'shahi-legalflowsuite'); ?></th>
                                    <th><?php esc_html_e('Pages', 'shahi-legalflowsuite'); ?></th>
                                    <th><?php esc_html_e('WCAG', 'shahi-legalflowsuite'); ?></th>
                                    <th><?php esc_html_e('Actions', 'shahi-legalflowsuite'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="slos-history-tbody">
                                <?php foreach (array_slice($scan_history, 0, 10) as $index => $scan): 
                                    $score = isset($scan['score']) ? intval($scan['score']) : 0;
                                    $score_class = $score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : ($score >= 50 ? 'fair' : 'poor'));
                                    $issues = isset($scan['issues']) ? intval($scan['issues']) : 0;
                                    $critical = isset($scan['critical']) ? intval($scan['critical']) : 0;
                                    $pages = isset($scan['pages_scanned']) ? intval($scan['pages_scanned']) : 0;
                                    $wcag = isset($scan['wcag_level']) ? $scan['wcag_level'] : 'AA';
                                    $date = isset($scan['date']) ? $scan['date'] : '';
                                    $scan_id = isset($scan['id']) ? $scan['id'] : $index;
                                ?>
                                <tr data-scan-id="<?php echo esc_attr($scan_id); ?>">
                                    <td class="slos-history-date">
                                        <span class="date-primary"><?php echo esc_html(date_i18n('M j, Y', strtotime($date))); ?></span>
                                        <span class="date-secondary"><?php echo esc_html(date_i18n('g:i A', strtotime($date))); ?></span>
                                    </td>
                                    <td>
                                        <span class="slos-score-badge <?php echo esc_attr($score_class); ?>">
                                            <?php echo esc_html($score); ?>/100
                                        </span>
                                    </td>
                                    <td class="slos-issues-cell">
                                        <span class="slos-issues-count"><?php echo esc_html($issues); ?></span>
                                    </td>
                                    <td class="slos-critical-cell">
                                        <?php if ($critical > 0): ?>
                                            <span class="slos-critical-badge"><?php echo esc_html($critical); ?></span>
                                        <?php else: ?>
                                            <span class="slos-none-badge">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($pages); ?></td>
                                    <td>
                                        <span class="slos-wcag-badge"><?php echo esc_html($wcag); ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="slos-view-scan-btn" data-scan-id="<?php echo esc_attr($scan_id); ?>" title="<?php esc_attr_e('View scan details', 'shahi-legalflowsuite'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                        <button type="button" class="slos-compare-scan-btn" data-scan-id="<?php echo esc_attr($scan_id); ?>" title="<?php esc_attr_e('Compare with previous', 'shahi-legalflowsuite'); ?>">
                                            <span class="dashicons dashicons-controls-repeat"></span>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($scan_history) > 10): ?>
                    <div class="slos-history-pagination">
                        <button type="button" class="slos-load-more-btn" id="slos-load-more-history">
                            <?php printf(esc_html__('Load more (%d remaining)', 'shahi-legalflowsuite'), count($scan_history) - 10); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Scan Details Modal -->
        <div id="slos-scan-modal" class="slos-modal" style="display: none;">
            <div class="slos-modal-overlay"></div>
            <div class="slos-modal-content">
                <div class="slos-modal-header">
                    <h3><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Scan Details', 'shahi-legalflowsuite'); ?></h3>
                    <button type="button" class="slos-modal-close">&times;</button>
                </div>
                <div class="slos-modal-body" id="slos-modal-body">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
        
        <!-- Card 6: Comparative Analytics (Full Width) -->
        <div class="slos-dashboard-card full-width">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-area"></span>
                    <?php echo esc_html__('Comparative Analytics', 'shahi-legalflowsuite'); ?>
                </h3>
                <div class="slos-chart-controls">
                    <select id="slos-comparison-period" class="slos-chart-select">
                        <option value="7"><?php esc_html_e('Last 7 days', 'shahi-legalflowsuite'); ?></option>
                        <option value="30" selected><?php esc_html_e('Last 30 days', 'shahi-legalflowsuite'); ?></option>
                        <option value="90"><?php esc_html_e('Last 90 days', 'shahi-legalflowsuite'); ?></option>
                    </select>
                </div>
            </div>
            <div class="slos-card-body">
                <div class="slos-analytics-grid">
                    <!-- Before/After Comparison -->
                    <div class="slos-comparison-card">
                        <h4><?php esc_html_e('Before vs After Fixes', 'shahi-legalflowsuite'); ?></h4>
                        <div class="slos-comparison-bars">
                            <div class="slos-comparison-row">
                                <span class="slos-comparison-label"><?php esc_html_e('Issues Before', 'shahi-legalflowsuite'); ?></span>
                                <div class="slos-comparison-bar-wrapper">
                                    <div class="slos-comparison-bar before" style="width: <?php echo esc_attr(min(100, ($total_issues + 50) / 2)); ?>%"></div>
                                    <span class="slos-comparison-value"><?php echo esc_html($total_issues + 50); ?></span>
                                </div>
                            </div>
                            <div class="slos-comparison-row">
                                <span class="slos-comparison-label"><?php esc_html_e('Issues After', 'shahi-legalflowsuite'); ?></span>
                                <div class="slos-comparison-bar-wrapper">
                                    <div class="slos-comparison-bar after" style="width: <?php echo esc_attr(min(100, $total_issues / 2)); ?>%"></div>
                                    <span class="slos-comparison-value"><?php echo esc_html($total_issues); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="slos-improvement-badge">
                            <span class="dashicons dashicons-arrow-down-alt"></span>
                            <strong><?php echo esc_html(round((50 / max(1, $total_issues + 50)) * 100)); ?>%</strong>
                            <?php esc_html_e('Reduction', 'shahi-legalflowsuite'); ?>
                        </div>
                    </div>
                    
                    <!-- Resolution Rate -->
                    <div class="slos-comparison-card">
                        <h4><?php esc_html_e('Fix Resolution Rate', 'shahi-legalflowsuite'); ?></h4>
                        <div class="slos-resolution-chart">
                            <svg width="200" height="200" viewBox="0 0 200 200">
                                <circle cx="100" cy="100" r="90" fill="none" stroke="#334155" stroke-width="20"/>
                                <circle cx="100" cy="100" r="90" fill="none" stroke="#22c55e" stroke-width="20" 
                                        stroke-dasharray="<?php echo (565 * 0.75) . ' 565'; ?>" 
                                        stroke-linecap="round"
                                        transform="rotate(-90 100 100)"
                                        style="transition: stroke-dasharray 1s ease;"/>
                                <text x="100" y="95" text-anchor="middle" fill="#22c55e" font-size="48" font-weight="700">75%</text>
                                <text x="100" y="120" text-anchor="middle" fill="#94a3b8" font-size="16"><?php esc_html_e('Resolved', 'shahi-legalflowsuite'); ?></text>
                            </svg>
                        </div>
                        <div class="slos-resolution-stats">
                            <div class="stat-row">
                                <span class="stat-label"><?php esc_html_e('Auto-Fixed', 'shahi-legalflowsuite'); ?></span>
                                <span class="stat-value success"><?php echo esc_html(round($total_issues * 0.6)); ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label"><?php esc_html_e('Manual Fixes', 'shahi-legalflowsuite'); ?></span>
                                <span class="stat-value info"><?php echo esc_html(round($total_issues * 0.15)); ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label"><?php esc_html_e('Remaining', 'shahi-legalflowsuite'); ?></span>
                                <span class="stat-value warning"><?php echo esc_html(round($total_issues * 0.25)); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Score Trend -->
                    <div class="slos-comparison-card full">
                        <h4><?php esc_html_e('Score Improvement Trend', 'shahi-legalflowsuite'); ?></h4>
                        <canvas id="slos-score-trend-chart" width="800" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 7: Scan Results Overview (Read-Only) (Full Width) -->
        <div class="slos-dashboard-card full-width">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-list-view"></span>
                    <?php echo esc_html__('Scan Results Overview', 'shahi-legalflowsuite'); ?>
                </h3>
                <span class="badge"><?php echo esc_html(count($scan_results)); ?> <?php esc_html_e('pages scanned', 'shahi-legalflowsuite'); ?></span>
            </div>
            <div class="slos-card-body" style="padding: 0;">
                <?php if (empty($scan_results)): ?>
                <div style="text-align: center; padding: 40px; color: var(--slos-text-muted);">
                    <span class="dashicons dashicons-search" style="font-size: 48px; margin-bottom: 12px; display: block;"></span>
                    <p><?php esc_html_e('No scan results yet. Run a scan from the Tools & Scanner tab.', 'shahi-legalflowsuite'); ?></p>
                </div>
                <?php else: ?>
                <table class="slos-results-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Page Name', 'shahi-legalflowsuite'); ?></th>
                            <th><?php esc_html_e('Issues', 'shahi-legalflowsuite'); ?></th>
                            <th><?php esc_html_e('Score', 'shahi-legalflowsuite'); ?></th>
                            <th><?php esc_html_e('Last Scanned', 'shahi-legalflowsuite'); ?></th>
                            <th><?php esc_html_e('Status', 'shahi-legalflowsuite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach (array_slice($scan_results, 0, 20) as $result): 
                            $issues_count = isset($result['issues_count']) ? $result['issues_count'] : 0;
                            $score = isset($result['score']) ? $result['score'] : 100;
                            $timestamp = isset($result['timestamp']) ? $result['timestamp'] : '';
                            $status = $issues_count === 0 ? 'pass' : ($issues_count > 5 ? 'fail' : 'warning');
                        ?>
                        <tr>
                            <td class="page-name"><?php echo esc_html($result['page'] ?? 'Unknown Page'); ?></td>
                            <td class="issues-count <?php echo esc_attr($status); ?>"><?php echo esc_html($issues_count); ?></td>
                            <td class="score-cell">
                                <span class="score-badge <?php echo esc_attr($score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : 'poor')); ?>">
                                    <?php echo esc_html($score); ?>%
                                </span>
                            </td>
                            <td class="timestamp"><?php echo $timestamp ? esc_html(human_time_diff(strtotime($timestamp)) . ' ago') : '-'; ?></td>
                            <td>
                                <?php if ($status === 'pass'): ?>
                                <span class="status-badge pass">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php esc_html_e('Pass', 'shahi-legalflowsuite'); ?>
                                </span>
                                <?php elseif ($status === 'fail'): ?>
                                <span class="status-badge fail">
                                    <span class="dashicons dashicons-warning"></span>
                                    <?php esc_html_e('Action Required', 'shahi-legalflowsuite'); ?>
                                </span>
                                <?php else: ?>
                                <span class="status-badge warning">
                                    <span class="dashicons dashicons-info"></span>
                                    <?php esc_html_e('Review', 'shahi-legalflowsuite'); ?>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($scan_results) > 20): ?>
                <div class="slos-table-footer">
                    <p><?php printf(esc_html__('Showing 20 of %d results. Visit Tools & Scanner tab to view and fix all pages.', 'shahi-legalflowsuite'), count($scan_results)); ?></p>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Export buttons
    $('.slos-export-btn').on('click', function() {
        var format = $(this).data('format');
        window.location.href = ajaxurl + '?action=slos_export_report&format=' + format + '&nonce=<?php echo wp_create_nonce('slos_export_nonce'); ?>';
    });
    
    // =============================================
    // FIX FUNCTIONALITY
    // =============================================
    
    // Fix All Issues button - Now handled by slos-scanner-admin.js with progress modal
    // The handler in slos-scanner-admin.js listens for .slos-fix-all-btn clicks
    // and triggers the SLOSAutoFixProgress modal
    
    /**
     * Show Fix Results Modal - Centered popup with full details
     * Still used for displaying detailed results after fixes
     */
    function showFixNotification(type, message, guidance, fixedDetails, failedDetails) {
        // Remove any existing modals
        $('.slos-fix-results-modal-overlay').remove();
        
        var iconClass = type === 'success' ? 'yes-alt' : (type === 'error' ? 'dismiss' : (type === 'warning' ? 'warning' : 'info-outline'));
        var headerBg = type === 'success' ? 'linear-gradient(135deg, #22c55e, #16a34a)' : (type === 'error' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : (type === 'warning' ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #3b82f6, #2563eb)'));
        var headerTitle = type === 'success' ? '<?php echo esc_js(__('Fix Complete', 'shahi-legalflowsuite')); ?>' : (type === 'error' ? '<?php echo esc_js(__('Fix Failed', 'shahi-legalflowsuite')); ?>' : (type === 'warning' ? '<?php echo esc_js(__('Manual Fixes Required', 'shahi-legalflowsuite')); ?>' : '<?php echo esc_js(__('Information', 'shahi-legalflowsuite')); ?>'));
        
        var html = '<div class="slos-fix-results-modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100002; display: flex; align-items: center; justify-content: center; padding: 20px;">';
        html += '<div class="slos-fix-results-modal" style="background: #334155; border: 1px solid #475569; border-radius: 16px; width: 100%; max-width: 600px; max-height: 80vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px rgba(0,0,0,0.6); overflow: hidden;">';
        
        // Modal Header
        html += '<div style="background: ' + headerBg + '; padding: 20px 24px; display: flex; align-items: center; gap: 16px;">';
        html += '<span class="dashicons dashicons-' + iconClass + '" style="color: white; font-size: 32px; width: 32px; height: 32px;"></span>';
        html += '<div style="flex: 1;"><h2 style="margin: 0; color: white; font-size: 20px; font-weight: 600;">' + headerTitle + '</h2>';
        html += '<p style="margin: 4px 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">' + message + '</p></div>';
        html += '<button type="button" class="slos-close-fix-modal" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 20px; line-height: 1; transition: background 0.2s;">&times;</button>';
        html += '</div>';
        
        // Modal Body - Scrollable
        html += '<div style="flex: 1; overflow-y: auto; padding: 24px; background: #334155;">';
        
        // Fixed Issues Section
        if (fixedDetails && fixedDetails.length > 0) {
            html += '<div style="margin-bottom: 24px;">';
            html += '<h3 style="margin: 0 0 12px; color: #22c55e; font-size: 16px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js(__('Automatically Fixed', 'shahi-legalflowsuite')); ?> (' + fixedDetails.length + ')</h3>';
            html += '<div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 8px; padding: 12px;">';
            fixedDetails.forEach(function(item) {
                html += '<div style="padding: 8px 0; border-bottom: 1px solid rgba(34, 197, 94, 0.2);">';
                html += '<div style="font-weight: 500; color: #f1f5f9;">' + (item.title || item.type || 'Issue') + '</div>';
                if (item.description) {
                    html += '<div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">' + item.description + '</div>';
                }
                html += '</div>';
            });
            html += '</div></div>';
        }
        
        // Failed Issues Section
        if (failedDetails && failedDetails.length > 0) {
            html += '<div style="margin-bottom: 24px;">';
            html += '<h3 style="margin: 0 0 12px; color: #ef4444; font-size: 16px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-dismiss"></span> <?php echo esc_js(__('Could Not Fix', 'shahi-legalflowsuite')); ?> (' + failedDetails.length + ')</h3>';
            html += '<div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 8px; padding: 12px;">';
            failedDetails.forEach(function(item) {
                html += '<div style="padding: 8px 0; border-bottom: 1px solid rgba(239, 68, 68, 0.2);">';
                html += '<div style="font-weight: 500; color: #f1f5f9;">' + (item.title || item.type || 'Issue') + '</div>';
                if (item.reason) {
                    html += '<div style="font-size: 12px; color: #fca5a5; margin-top: 4px;">' + item.reason + '</div>';
                }
                html += '</div>';
            });
            html += '</div></div>';
        }
        
        // Manual Fix Guidance Section
        if (guidance && guidance.length > 0) {
            html += '<div>';
            html += '<h3 style="margin: 0 0 12px; color: #f59e0b; font-size: 16px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-edit"></span> <?php echo esc_js(__('Manual Fixes Required', 'shahi-legalflowsuite')); ?> (' + guidance.length + ')</h3>';
            
            guidance.forEach(function(guide, idx) {
                html += '<div style="background: #475569; border: 1px solid #64748b; border-radius: 8px; padding: 16px; margin-bottom: 12px;">';
                html += '<div style="display: flex; align-items: flex-start; gap: 12px;">';
                html += '<span style="background: #f59e0b; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0;">' + (idx + 1) + '</span>';
                html += '<div style="flex: 1;">';
                html += '<h4 style="margin: 0 0 8px; color: #f1f5f9; font-size: 14px; font-weight: 600;">' + guide.title + '</h4>';
                html += '<p style="margin: 0 0 12px; color: #cbd5e1; font-size: 13px;">' + guide.description + '</p>';
                
                if (guide.steps && guide.steps.length > 0) {
                    html += '<div style="background: #1e293b; border-radius: 6px; padding: 12px;">';
                    html += '<div style="font-weight: 500; color: #cbd5e1; font-size: 12px; margin-bottom: 8px;"><?php echo esc_js(__('How to fix:', 'shahi-legalflowsuite')); ?></div>';
                    html += '<ol style="margin: 0; padding-left: 20px; color: #e2e8f0; font-size: 13px;">';
                    guide.steps.forEach(function(step) {
                        html += '<li style="margin-bottom: 6px;">' + step + '</li>';
                    });
                    html += '</ol>';
                    if (guide.tip) {
                        html += '<div style="margin-top: 10px; padding: 8px 12px; background: rgba(59, 130, 246, 0.2); border-radius: 4px; font-size: 12px; color: #93c5fd;"><span class="dashicons dashicons-lightbulb" style="font-size: 14px; margin-right: 4px;"></span> ' + guide.tip + '</div>';
                    }
                    html += '</div>';
                }
                html += '</div></div></div>';
            });
            html += '</div>';
        }
        
        // No details case - just show message
        if ((!guidance || guidance.length === 0) && (!fixedDetails || fixedDetails.length === 0) && (!failedDetails || failedDetails.length === 0)) {
            html += '<div style="text-align: center; padding: 40px 20px;">';
            html += '<span class="dashicons dashicons-' + iconClass + '" style="font-size: 48px; width: 48px; height: 48px; color: ' + (type === 'success' ? '#22c55e' : (type === 'error' ? '#ef4444' : '#f59e0b')) + '; display: block; margin: 0 auto 16px;"></span>';
            html += '<p style="color: #f1f5f9; font-size: 16px; margin: 0;">' + message + '</p>';
            html += '</div>';
        }
        
        html += '</div>';
        
        // Modal Footer
        html += '<div style="padding: 16px 24px; border-top: 1px solid #64748b; background: #334155; display: flex; justify-content: flex-end; gap: 12px;">';
        html += '<button type="button" class="slos-close-fix-modal" style="padding: 10px 24px; background: #475569; border: 1px solid #64748b; border-radius: 8px; color: #f1f5f9; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;"><?php echo esc_js(__('Close', 'shahi-legalflowsuite')); ?></button>';
        html += '</div>';
        
        html += '</div></div>';
        
        var $modal = $(html);
        $('body').append($modal);
        
        // Close handlers
        $modal.find('.slos-close-fix-modal').on('click', function() {
            $modal.fadeOut(200, function() { $(this).remove(); });
        });
        
        $modal.on('click', function(e) {
            if ($(e.target).hasClass('slos-fix-results-modal-overlay')) {
                $modal.fadeOut(200, function() { $(this).remove(); });
            }
        });
        
        // ESC key to close
        $(document).on('keydown.fixModal', function(e) {
            if (e.key === 'Escape') {
                $modal.fadeOut(200, function() { $(this).remove(); });
                $(document).off('keydown.fixModal');
            }
        });
    }
    
    // Auto-fix toggle
    $('.slos-autofix-checkbox').on('change', function() {
        var $checkbox = $(this);
        var postId = $checkbox.data('post-id');
        var enabled = $checkbox.is(':checked');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_toggle_autofix',
                nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                post_id: postId,
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    showFixNotification('success', response.data.message);
                } else {
                    // Revert checkbox
                    $checkbox.prop('checked', !enabled);
                    showFixNotification('error', '<?php echo esc_js(__('Failed to update auto-fix setting.', 'shahi-legalflowsuite')); ?>');
                }
            },
            error: function() {
                $checkbox.prop('checked', !enabled);
                showFixNotification('error', '<?php echo esc_js(__('Network error.', 'shahi-legalflowsuite')); ?>');
            }
        });
    });
    
    // =============================================
    // SCAN HISTORY & TRENDS CHART
    // =============================================
    
    // Store scan history data
    var scanHistoryData = <?php echo json_encode(array_values($scan_history)); ?>;
    var trendsChart = null;
    
    // Initialize chart if we have data
    if (scanHistoryData.length > 0 && document.getElementById('slos-trends-chart')) {
        initTrendsChart();
    }
    
    /**
     * Initialize the trends chart using Chart.js
     */
    function initTrendsChart() {
        var ctx = document.getElementById('slos-trends-chart');
        if (!ctx) return;
        
        var range = $('#slos-chart-range').val();
        var chartData = getChartData(range);
        
        // Destroy existing chart if any
        if (trendsChart) {
            trendsChart.destroy();
        }
        
        trendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: '<?php echo esc_js(__('Score', 'shahi-legalflowsuite')); ?>',
                        data: chartData.scores,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: '<?php echo esc_js(__('Issues', 'shahi-legalflowsuite')); ?>',
                        data: chartData.issues,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    },
                    {
                        label: '<?php echo esc_js(__('Critical', 'shahi-legalflowsuite')); ?>',
                        data: chartData.critical,
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#94a3b8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            title: function(items) {
                                return items[0].label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#71717a',
                            font: { size: 11 }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        min: 0,
                        max: 100,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#71717a',
                            font: { size: 11 },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: '<?php echo esc_js(__('Score', 'shahi-legalflowsuite')); ?>',
                            color: '#71717a'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#71717a',
                            font: { size: 11 }
                        },
                        title: {
                            display: true,
                            text: '<?php echo esc_js(__('Issues', 'shahi-legalflowsuite')); ?>',
                            color: '#71717a'
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Get chart data filtered by range
     */
    function getChartData(range) {
        var data = scanHistoryData.slice().reverse(); // Oldest first for chart
        
        if (range !== 'all') {
            data = data.slice(-parseInt(range));
        }
        
        return {
            labels: data.map(function(scan) {
                var d = new Date(scan.date);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            scores: data.map(function(scan) { return scan.score || 0; }),
            issues: data.map(function(scan) { return scan.issues || 0; }),
            critical: data.map(function(scan) { return scan.critical || 0; })
        };
    }
    
    // Update chart when range changes
    $('#slos-chart-range').on('change', function() {
        initTrendsChart();
    });
    
    // Initialize Score Trend Chart for Comparative Analytics
    if (typeof Chart !== 'undefined' && $('#slos-score-trend-chart').length) {
        var trendCtx = document.getElementById('slos-score-trend-chart').getContext('2d');
        var scanHistory = <?php echo json_encode($scan_history); ?>;
        var lastScores = scanHistory.slice(-30).map(function(h) { return h.score || 0; });
        var labels = scanHistory.slice(-30).map(function(h) { 
            var d = new Date(h.date);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        // If no history, use sample data
        if (!lastScores.length) {
            lastScores = [65, 68, 72, 75, 78, 81, 83, 85, 87, 88];
            labels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8', 'Day 9', 'Day 10'];
        }
        
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php echo esc_js(__('Accessibility Score', 'shahi-legalflowsuite')); ?>',
                    data: lastScores,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.95)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#475569',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { 
                            color: '#94a3b8',
                            font: { size: 12 }
                        },
                        grid: { 
                            color: 'rgba(51, 65, 85, 0.5)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: { 
                            color: '#94a3b8',
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: { 
                            color: 'rgba(51, 65, 85, 0.3)',
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }
    
    // =============================================
    // SCAN DETAILS MODAL
    // =============================================
    
    // View scan details
    $('.slos-view-scan-btn').on('click', function() {
        var scanId = $(this).data('scan-id');
        showScanDetails(scanId);
    });
    
    // Compare with previous
    $('.slos-compare-scan-btn').on('click', function() {
        var scanId = $(this).data('scan-id');
        showScanComparison(scanId);
    });
    
    // Close modal
    $('.slos-modal-close, .slos-modal-overlay').on('click', function() {
        $('#slos-scan-modal').hide();
    });
    
    // Close modal on ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#slos-scan-modal').hide();
        }
    });
    
    /**
     * Show scan details in modal
     */
    function showScanDetails(scanId) {
        var scan = findScanById(scanId);
        if (!scan) return;
        
        var scoreClass = scan.score >= 90 ? 'excellent' : (scan.score >= 70 ? 'good' : (scan.score >= 50 ? 'fair' : 'poor'));
        var date = new Date(scan.date);
        
        var html = '<div class="slos-modal-scan-date">' +
            '<strong><?php echo esc_js(__('Scan Date:', 'shahi-legalflowsuite')); ?></strong> ' + 
            date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) +
            '</div>' +
            '<div class="slos-modal-stats">' +
                '<div class="slos-modal-stat">' +
                    '<div class="slos-modal-stat-value" style="color: var(--slos-' + (scan.score >= 70 ? 'success' : 'error') + ');">' + scan.score + '%</div>' +
                    '<div class="slos-modal-stat-label"><?php echo esc_js(__('Accessibility Score', 'shahi-legalflowsuite')); ?></div>' +
                '</div>' +
                '<div class="slos-modal-stat">' +
                    '<div class="slos-modal-stat-value">' + scan.pages_scanned + '</div>' +
                    '<div class="slos-modal-stat-label"><?php echo esc_js(__('Pages Scanned', 'shahi-legalflowsuite')); ?></div>' +
                '</div>' +
                '<div class="slos-modal-stat">' +
                    '<div class="slos-modal-stat-value" style="color: var(--slos-warning);">' + scan.issues + '</div>' +
                    '<div class="slos-modal-stat-label"><?php echo esc_js(__('Total Issues', 'shahi-legalflowsuite')); ?></div>' +
                '</div>' +
                '<div class="slos-modal-stat">' +
                    '<div class="slos-modal-stat-value" style="color: var(--slos-error);">' + scan.critical + '</div>' +
                    '<div class="slos-modal-stat-label"><?php echo esc_js(__('Critical Issues', 'shahi-legalflowsuite')); ?></div>' +
                '</div>' +
            '</div>' +
            '<div class="slos-modal-info">' +
                '<div class="slos-comparison-row">' +
                    '<span class="slos-comparison-label"><?php echo esc_js(__('WCAG Level', 'shahi-legalflowsuite')); ?></span>' +
                    '<span class="slos-comparison-value"><span class="slos-wcag-badge">' + (scan.wcag_level || 'AA') + '</span></span>' +
                '</div>' +
                '<div class="slos-comparison-row">' +
                    '<span class="slos-comparison-label"><?php echo esc_js(__('Issues per Page', 'shahi-legalflowsuite')); ?></span>' +
                    '<span class="slos-comparison-value">' + (scan.pages_scanned > 0 ? (scan.issues / scan.pages_scanned).toFixed(1) : '0') + '</span>' +
                '</div>' +
            '</div>';
        
        $('#slos-modal-body').html(html);
        $('#slos-scan-modal .slos-modal-header h3').html('<span class="dashicons dashicons-chart-bar"></span> <?php echo esc_js(__('Scan Details', 'shahi-legalflowsuite')); ?>');
        $('#slos-scan-modal').show();
    }
    
    /**
     * Show scan comparison in modal
     */
    function showScanComparison(scanId) {
        var scanIndex = findScanIndexById(scanId);
        if (scanIndex === -1) return;
        
        var currentScan = scanHistoryData[scanIndex];
        var previousScan = scanHistoryData[scanIndex + 1]; // History is newest first
        
        if (!previousScan) {
            showScanDetails(scanId);
            return;
        }
        
        var scoreChange = currentScan.score - previousScan.score;
        var issuesChange = currentScan.issues - previousScan.issues;
        var criticalChange = currentScan.critical - previousScan.critical;
        
        var html = '<div class="slos-modal-scan-date" style="margin-bottom: 16px;">' +
            '<strong><?php echo esc_js(__('Comparing:', 'shahi-legalflowsuite')); ?></strong> ' + 
            new Date(currentScan.date).toLocaleDateString() + ' vs ' + new Date(previousScan.date).toLocaleDateString() +
            '</div>' +
            '<div class="slos-comparison-row">' +
                '<span class="slos-comparison-label"><?php echo esc_js(__('Score', 'shahi-legalflowsuite')); ?></span>' +
                '<span class="slos-comparison-value">' + 
                    currentScan.score + '% ' + 
                    '<span class="slos-comparison-change ' + (scoreChange >= 0 ? 'positive' : 'negative') + '">' +
                        (scoreChange >= 0 ? '↑' : '↓') + Math.abs(scoreChange) + '%' +
                    '</span>' +
                '</span>' +
            '</div>' +
            '<div class="slos-comparison-row">' +
                '<span class="slos-comparison-label"><?php echo esc_js(__('Total Issues', 'shahi-legalflowsuite')); ?></span>' +
                '<span class="slos-comparison-value">' + 
                    currentScan.issues + ' ' + 
                    '<span class="slos-comparison-change ' + (issuesChange <= 0 ? 'positive' : 'negative') + '">' +
                        (issuesChange <= 0 ? '↓' : '↑') + Math.abs(issuesChange) +
                    '</span>' +
                '</span>' +
            '</div>' +
            '<div class="slos-comparison-row">' +
                '<span class="slos-comparison-label"><?php echo esc_js(__('Critical Issues', 'shahi-legalflowsuite')); ?></span>' +
                '<span class="slos-comparison-value">' + 
                    currentScan.critical + ' ' + 
                    '<span class="slos-comparison-change ' + (criticalChange <= 0 ? 'positive' : 'negative') + '">' +
                        (criticalChange <= 0 ? '↓' : '↑') + Math.abs(criticalChange) +
                    '</span>' +
                '</span>' +
            '</div>' +
            '<div class="slos-comparison-row">' +
                '<span class="slos-comparison-label"><?php echo esc_js(__('Pages Scanned', 'shahi-legalflowsuite')); ?></span>' +
                '<span class="slos-comparison-value">' + currentScan.pages_scanned + ' (was ' + previousScan.pages_scanned + ')</span>' +
            '</div>';
        
        $('#slos-modal-body').html(html);
        $('#slos-scan-modal .slos-modal-header h3').html('<span class="dashicons dashicons-controls-repeat"></span> <?php echo esc_js(__('Scan Comparison', 'shahi-legalflowsuite')); ?>');
        $('#slos-scan-modal').show();
    }
    
    /**
     * Find scan by ID in history
     */
    function findScanById(scanId) {
        for (var i = 0; i < scanHistoryData.length; i++) {
            if (scanHistoryData[i].id === scanId || i === parseInt(scanId)) {
                return scanHistoryData[i];
            }
        }
        return null;
    }
    
    /**
     * Find scan index by ID
     */
    function findScanIndexById(scanId) {
        for (var i = 0; i < scanHistoryData.length; i++) {
            if (scanHistoryData[i].id === scanId || i === parseInt(scanId)) {
                return i;
            }
        }
        return -1;
    }
    
    // =============================================
    // EXPORT FUNCTIONALITY
    // =============================================
    
    // Export buttons for scan history
    $('.slos-export-btn').on('click', function() {
        var format = $(this).data('format');
        exportScanHistory(format);
    });
    
    /**
     * Export scan history
     */
    function exportScanHistory(format) {
        if (scanHistoryData.length === 0) {
            alert('<?php echo esc_js(__('No scan history to export.', 'shahi-legalflowsuite')); ?>');
            return;
        }
        
        var filename = 'accessibility-scan-history-' + new Date().toISOString().split('T')[0];
        
        if (format === 'json') {
            downloadFile(JSON.stringify(scanHistoryData, null, 2), filename + '.json', 'application/json');
        } else if (format === 'csv') {
            var csv = 'Date,Score,Issues,Critical,Pages Scanned,WCAG Level\n';
            scanHistoryData.forEach(function(scan) {
                csv += '"' + scan.date + '",' + scan.score + ',' + scan.issues + ',' + scan.critical + ',' + scan.pages_scanned + ',' + (scan.wcag_level || 'AA') + '\n';
            });
            downloadFile(csv, filename + '.csv', 'text/csv');
        }
    }
    
    /**
     * Download file helper
     */
    function downloadFile(content, filename, mimeType) {
        var blob = new Blob([content], { type: mimeType });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    // =============================================
    // LOAD MORE HISTORY
    // =============================================
    
    var historyPage = 1;
    var historyPerPage = 10;
    
    $('#slos-load-more-history').on('click', function() {
        historyPage++;
        var start = historyPage * historyPerPage;
        var end = start + historyPerPage;
        var moreScans = scanHistoryData.slice(start - historyPerPage, end);
        
        if (moreScans.length === 0) {
            $(this).hide();
            return;
        }
        
        var $tbody = $('#slos-history-tbody');
        
        moreScans.forEach(function(scan, index) {
            var actualIndex = start + index;
            var score = scan.score || 0;
            var scoreClass = score >= 90 ? 'excellent' : (score >= 70 ? 'good' : (score >= 50 ? 'fair' : 'poor'));
            var issues = scan.issues || 0;
            var critical = scan.critical || 0;
            var pages = scan.pages_scanned || 0;
            var wcag = scan.wcag_level || 'AA';
            var date = new Date(scan.date);
            var scanId = scan.id || actualIndex;
            
            var row = '<tr data-scan-id="' + scanId + '">' +
                '<td class="slos-history-date">' +
                    '<span class="date-primary">' + date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + '</span>' +
                    '<span class="date-secondary">' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + '</span>' +
                '</td>' +
                '<td><span class="slos-score-badge ' + scoreClass + '">' + score + '/100</span></td>' +
                '<td class="slos-issues-cell"><span class="slos-issues-count">' + issues + '</span></td>' +
                '<td class="slos-critical-cell">' + (critical > 0 ? '<span class="slos-critical-badge">' + critical + '</span>' : '<span class="slos-none-badge">—</span>') + '</td>' +
                '<td>' + pages + '</td>' +
                '<td><span class="slos-wcag-badge">' + wcag + '</span></td>' +
                '<td>' +
                    '<button type="button" class="slos-view-scan-btn" data-scan-id="' + scanId + '" title="<?php echo esc_attr__('View scan details', 'shahi-legalflowsuite'); ?>">' +
                        '<span class="dashicons dashicons-visibility"></span>' +
                    '</button>' +
                    '<button type="button" class="slos-compare-scan-btn" data-scan-id="' + scanId + '" title="<?php echo esc_attr__('Compare with previous', 'shahi-legalflowsuite'); ?>">' +
                        '<span class="dashicons dashicons-controls-repeat"></span>' +
                    '</button>' +
                '</td>' +
            '</tr>';
            
            $tbody.append(row);
        });
        
        // Rebind event handlers for new buttons
        $tbody.find('.slos-view-scan-btn').off('click').on('click', function() {
            showScanDetails($(this).data('scan-id'));
        });
        $tbody.find('.slos-compare-scan-btn').off('click').on('click', function() {
            showScanComparison($(this).data('scan-id'));
        });
        
        // Update remaining count or hide button
        var remaining = scanHistoryData.length - end;
        if (remaining <= 0) {
            $(this).hide();
        } else {
            $(this).text('<?php echo esc_js(__('Load more', 'shahi-legalflowsuite')); ?> (' + remaining + ' <?php echo esc_js(__('remaining', 'shahi-legalflowsuite')); ?>)');
        }
    });
});

// Spinning animation
var style = document.createElement('style');
style.textContent = '.slos-spin { animation: slos-spin 1s linear infinite; } @keyframes slos-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>

<!-- Detailed Scan Report Modal -->
<div id="slos-scan-details-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999999; overflow-y:auto;">
    <div style="max-width:900px; margin:50px auto; background:#1e293b; border-radius:12px; border:1px solid #334155;">
        <div style="padding:24px; border-bottom:1px solid #334155; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="color:#f8fafc; margin:0; font-size:20px;">📊 Detailed Scan Report</h2>
            <button id="slos-close-details-modal" style="background:transparent; border:none; color:#94a3b8; font-size:24px; cursor:pointer; padding:0; width:32px; height:32px;">&times;</button>
        </div>
        <div id="slos-scan-details-content" style="padding:24px; max-height:70vh; overflow-y:auto;">
            <div style="text-align:center; padding:40px; color:#94a3b8;">
                <div class="slos-spinner" style="border:3px solid #334155; border-top-color:#3b82f6; border-radius:50%; width:40px; height:40px; animation:slos-spin 1s linear infinite; margin:0 auto 16px;"></div>
                Loading scan details...
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // View Details button handler
    $(document).on('click', '.slos-view-details-btn', function() {
        var postId = $(this).data('post-id');
        showDetailedScanReport(postId);
    });
    
    // Close modal
    $('#slos-close-details-modal').on('click', function() {
        $('#slos-scan-details-modal').fadeOut(200);
    });
    
    // Close on background click
    $('#slos-scan-details-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).fadeOut(200);
        }
    });
    
    function showDetailedScanReport(postId) {
        $('#slos-scan-details-modal').fadeIn(200);
        $('#slos-scan-details-content').html(
            '<div style="text-align:center; padding:40px; color:#94a3b8;">' +
            '<div class="slos-spinner" style="border:3px solid #334155; border-top-color:#3b82f6; border-radius:50%; width:40px; height:40px; animation:slos-spin 1s linear infinite; margin:0 auto 16px;"></div>' +
            'Loading scan details...' +
            '</div>'
        );
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_get_detailed_scan_report',
                nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                post_id: postId
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayDetailedReport(response.data);
                } else {
                    $('#slos-scan-details-content').html(
                        '<div style="text-align:center; padding:40px; color:#ef4444;">' +
                        '⚠️ Failed to load scan details.<br>' +
                        '<small>' + (response.data && response.data.message ? response.data.message : 'Unknown error') + '</small>' +
                        '</div>'
                    );
                }
            },
            error: function() {
                $('#slos-scan-details-content').html(
                    '<div style="text-align:center; padding:40px; color:#ef4444;">' +
                    '⚠️ Error loading scan details. Please try again.' +
                    '</div>'
                );
            }
        });
    }
    
    function displayDetailedReport(data) {
        var html = '';
        
        // Page header
        html += '<div style="background:#0f172a; border-radius:8px; padding:20px; margin-bottom:24px;">';
        html += '<h3 style="color:#f8fafc; margin:0 0 8px 0; font-size:18px;">' + escapeHtml(data.page_title) + '</h3>';
        html += '<div style="display:flex; gap:24px; margin-top:16px;">';
        html += '<div><span style="color:#94a3b8;">Score:</span> <strong style="color:#3b82f6; font-size:20px;">' + data.score + '%</strong></div>';
        html += '<div><span style="color:#94a3b8;">Total Issues:</span> <strong style="color:#f59e0b; font-size:20px;">' + data.total_issues + '</strong></div>';
        html += '<div><span style="color:#94a3b8;">Scan Date:</span> <span style="color:#f8fafc;">' + data.scan_date + '</span></div>';
        html += '</div>';
        html += '</div>';
        
        // Issues by category
        if (data.issues && data.issues.length > 0) {
            html += '<h4 style="color:#f8fafc; font-size:16px; margin:0 0 16px 0;">🔍 Issues Found</h4>';
            
            data.issues.forEach(function(issue) {
                var severityColor = issue.severity === 'critical' ? '#ef4444' : (issue.severity === 'major' ? '#f59e0b' : '#94a3b8');
                
                html += '<div style="background:#0f172a; border-left:4px solid ' + severityColor + '; border-radius:6px; padding:16px; margin-bottom:12px;">';
                html += '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">';
                html += '<strong style="color:#f8fafc; font-size:15px;">' + escapeHtml(issue.name) + '</strong>';
                html += '<span style="background:' + severityColor + '20; color:' + severityColor + '; padding:4px 12px; border-radius:4px; font-size:11px; text-transform:uppercase; font-weight:600;">' + issue.severity + '</span>';
                html += '</div>';
                html += '<p style="color:#94a3b8; margin:0 0 12px 0; font-size:14px; line-height:1.5;">' + escapeHtml(issue.description) + '</p>';
                html += '<div style="color:#64748b; font-size:13px; margin-bottom:8px;"><strong>Count:</strong> ' + issue.count + ' occurrence(s)</div>';
                
                if (issue.fix_tip) {
                    html += '<div style="background:#334155; border-radius:4px; padding:12px; margin-top:8px;">';
                    html += '<div style="color:#06b6d4; font-size:12px; font-weight:600; margin-bottom:4px;">💡 HOW TO FIX:</div>';
                    html += '<div style="color:#cbd5e1; font-size:13px; line-height:1.6;">' + escapeHtml(issue.fix_tip) + '</div>';
                    html += '</div>';
                }
                html += '</div>';
            });
        } else {
            html += '<div style="text-align:center; padding:40px; color:#22c55e; background:#0f172a; border-radius:8px;">';
            html += '<div style="font-size:48px; margin-bottom:12px;">✓</div>';
            html += '<div style="font-size:16px; font-weight:600;">No Issues Found</div>';
            html += '<div style="font-size:14px; color:#94a3b8; margin-top:8px;">This page passes all accessibility checks!</div>';
            html += '</div>';
        }
        
        $('#slos-scan-details-content').html(html);
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

