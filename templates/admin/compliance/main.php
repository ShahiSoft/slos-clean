<?php
/**
 * Compliance Main Page Template - V3 Design
 *
 * Command center for consent and compliance management.
 * Uses V3 dark theme design system for consistency.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
/* V3 Compliance Dashboard Styles - Uses Global Theme Variables */
.slos-compliance-v3 {
	/* Inherit from global theme - Mac Slate Liquid */
	--slos-bg-primary: #0f172a;
	--slos-bg-card: #1e293b;
	--slos-bg-input: #0f172a;
	--slos-bg-elevated: #475569;
	--slos-border: #334155;
	--slos-border-light: #475569;
	--slos-text-primary: #f8fafc;
	--slos-text-secondary: #94a3b8;
	--slos-text-muted: #64748b;
	--slos-accent: #3b82f6;
	--slos-accent-hover: #2563eb;
	--slos-accent-light: #93c5fd;
	--slos-success: #22c55e;
	--slos-warning: #f59e0b;
	--slos-error: #ef4444;
	--slos-info: #06b6d4;
	--slos-purple: #93c5fd;
	
	background: var(--slos-bg-primary);
	padding: 0;
	margin: -20px -20px 0 -20px;
	min-height: calc(100vh - 32px);
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Command Bar - Sticky Header */
.slos-command-bar {
	position: sticky;
	top: 32px;
	z-index: 100;
	background: rgba(17, 17, 17, 0.95);
	backdrop-filter: blur(12px);
	border-bottom: 1px solid var(--slos-border);
	padding: 16px 24px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 24px;
}

.slos-command-left {
	display: flex;
	align-items: center;
	gap: 16px;
}

.slos-command-title {
	display: flex;
	align-items: center;
	gap: 12px;
}

.slos-command-title .icon {
	width: 40px;
	height: 40px;
	background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%);
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.slos-command-title .icon .dashicons {
	color: white;
	font-size: 22px;
	width: 22px;
	height: 22px;
}

.slos-command-title h1 {
	margin: 0;
	font-size: 28px;
	font-weight: 700;
	color: #ffffff;
	text-shadow: 
		0 0 10px rgba(255, 255, 255, 0.8),
		0 0 20px rgba(255, 255, 255, 0.6),
		0 0 40px rgba(255, 255, 255, 0.4),
		0 0 60px rgba(147, 197, 253, 0.3);
	letter-spacing: 0.5px;
}

.slos-command-title .subtitle {
	font-size: 13px;
	color: var(--slos-text-muted);
	margin-top: 2px;
}

/* Search Box */
.slos-command-search {
	flex: 1;
	max-width: 400px;
	position: relative;
}

.slos-command-search input {
	width: 100%;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 8px;
	padding: 10px 16px 10px 42px;
	color: var(--slos-text-primary);
	font-size: 14px;
	transition: all 0.2s;
}

.slos-command-search input:focus {
	outline: none;
	border-color: var(--slos-accent);
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.slos-command-search input::placeholder {
	color: var(--slos-text-muted);
}

.slos-command-search .search-icon {
	position: absolute;
	left: 14px;
	top: 50%;
	transform: translateY(-50%);
	color: var(--slos-text-muted);
	font-size: 16px;
}

.slos-command-search .shortcut {
	position: absolute;
	right: 12px;
	top: 50%;
	transform: translateY(-50%);
	background: var(--slos-bg-elevated);
	border: 1px solid var(--slos-border);
	border-radius: 4px;
	padding: 2px 6px;
	font-size: 11px;
	color: var(--slos-text-muted);
}

/* Command Actions */
.slos-command-actions {
	display: flex;
	align-items: center;
	gap: 8px;
}

.slos-cmd-btn {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 8px 14px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	color: var(--slos-text-secondary);
	font-size: 13px;
	cursor: pointer;
	transition: all 0.2s;
	text-decoration: none;
}

.slos-cmd-btn:hover {
	border-color: var(--slos-accent);
	color: var(--slos-accent);
}

.slos-cmd-btn.primary {
	background: linear-gradient(135deg, var(--slos-accent), var(--slos-accent-hover));
	border-color: transparent;
	color: white;
}

.slos-cmd-btn.primary:hover {
	transform: translateY(-1px);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.slos-cmd-btn .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
}

.slos-cmd-divider {
	width: 1px;
	height: 24px;
	background: var(--slos-border);
	margin: 0 4px;
}

/* Status Indicator */
.slos-status-indicator {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 6px 12px;
	background: rgba(34, 197, 94, 0.1);
	border: 1px solid rgba(34, 197, 94, 0.2);
	border-radius: 20px;
	font-size: 12px;
	color: var(--slos-success);
}

.slos-status-indicator .dot {
	width: 8px;
	height: 8px;
	background: var(--slos-success);
	border-radius: 50%;
	animation: pulse 2s infinite;
}

@keyframes pulse {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.5; }
}

/* Tab Navigation - Modern Pills Style with Blue/White Gradient */
.slos-tab-nav {
	background: var(--slos-bg-card);
	border-bottom: 1px solid var(--slos-border);
	padding: 16px 24px;
	display: flex;
	align-items: center;
	gap: 8px;
	overflow-x: auto;
	flex-wrap: wrap;
}

.slos-tab-nav::-webkit-scrollbar {
	display: none;
}

.slos-tab-item {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 12px 20px;
	color: var(--slos-text-primary);
	background: var(--slos-bg-input);
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
	border: 2px solid var(--slos-border);
	border-radius: 8px;
	transition: all 0.2s ease;
	white-space: nowrap;
}

.slos-tab-item:hover {
	background: var(--slos-bg-elevated);
	color: var(--slos-accent);
	border-color: var(--slos-accent);
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.slos-tab-item.active {
	background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%);
	color: #1e3a5f;
	font-weight: 700;
	border-color: #3b82f6;
	box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 197, 253, 0.3);
	transform: translateY(-2px);
}

.slos-tab-item.active .dashicons {
	color: #1e3a5f;
}

.slos-tab-item .dashicons {
	font-size: 18px;
	width: 18px;
	height: 18px;
	color: var(--slos-accent);
}

.slos-tab-item .badge {
	background: var(--slos-error);
	color: white;
	padding: 2px 6px;
	border-radius: 10px;
	font-size: 10px;
	font-weight: 600;
}

/* Main Content Area */
.slos-compliance-content {
	padding: 24px;
	max-width: 1600px;
}

/* Two-Column Layout */
.slos-two-col-grid {
	display: grid;
	grid-template-columns: 1fr 380px;
	gap: 24px;
}

@media (max-width: 1200px) {
	.slos-two-col-grid {
		grid-template-columns: 1fr;
	}
}

/* Card Component */
.slos-card {
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	overflow: hidden;
}

.slos-card-header {
	padding: 20px 24px;
	border-bottom: 1px solid var(--slos-border);
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.slos-card-header h3 {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--slos-text-primary);
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
	padding: 3px 10px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
}

.slos-card-body {
	padding: 24px;
}

.slos-card.full-width {
	grid-column: 1 / -1;
}

/* Stats Grid */
.slos-compliance-v3 .slos-stats-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 16px;
	margin-bottom: 24px;
}

@media (max-width: 900px) {
	.slos-compliance-v3 .slos-stats-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}

.slos-compliance-v3 .slos-stat-card {
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	padding: 20px;
	position: relative;
	overflow: hidden;
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.slos-compliance-v3 .slos-stat-card::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	height: 3px;
}

.slos-compliance-v3 .slos-stat-card.accent::before { background: var(--slos-accent); }
.slos-compliance-v3 .slos-stat-card.success::before { background: var(--slos-success); }
.slos-compliance-v3 .slos-stat-card.warning::before { background: var(--slos-warning); }
.slos-compliance-v3 .slos-stat-card.danger::before { background: var(--slos-error); }

.slos-compliance-v3 .slos-stat-label {
	font-size: 13px;
	color: var(--slos-text-muted);
	margin-bottom: 8px;
	display: flex;
	align-items: center;
	gap: 6px;
}

.slos-compliance-v3 .slos-stat-value {
	font-size: 32px;
	font-weight: 700;
	color: var(--slos-text-primary);
	line-height: 1;
	margin-bottom: 8px;
}

.slos-compliance-v3 .slos-stat-meta {
	font-size: 12px;
	color: var(--slos-text-muted);
	display: flex;
	align-items: center;
	gap: 6px;
}

.slos-compliance-v3 .slos-stat-meta .trend {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 2px 6px;
	border-radius: 4px;
	font-weight: 500;
}

.slos-compliance-v3 .slos-stat-meta .trend.up {
	background: rgba(34, 197, 94, 0.15);
	color: var(--slos-success);
}

.slos-compliance-v3 .slos-stat-meta .trend.down {
	background: rgba(239, 68, 68, 0.15);
	color: var(--slos-error);
}

/* Score Gauge */
.slos-score-section {
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

/* Compliance Badges */
.slos-compliance-badges {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	margin-top: 16px;
}

.slos-compliance-badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 6px 12px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	font-size: 12px;
	color: var(--slos-text-muted);
}

.slos-compliance-badge.active {
	background: rgba(34, 197, 94, 0.1);
	border-color: rgba(34, 197, 94, 0.3);
	color: var(--slos-success);
}

.slos-compliance-badge .dashicons {
	font-size: 14px;
	width: 14px;
	height: 14px;
}

/* Data Table */
.slos-data-table {
	width: 100%;
	border-collapse: collapse;
}

.slos-data-table th {
	text-align: left;
	padding: 12px 16px;
	font-size: 12px;
	font-weight: 600;
	color: var(--slos-text-muted);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	border-bottom: 1px solid var(--slos-border);
	background: var(--slos-bg-input);
}

.slos-data-table td {
	padding: 14px 16px;
	font-size: 14px;
	color: var(--slos-text-secondary);
	border-bottom: 1px solid var(--slos-border);
}

.slos-data-table tbody tr:hover {
	background: rgba(59, 130, 246, 0.05);
}

.slos-data-table tbody tr:last-child td {
	border-bottom: none;
}

/* Status Badge */
.slos-status-badge {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 4px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 500;
}

.slos-status-badge.accepted {
	background: rgba(34, 197, 94, 0.15);
	color: var(--slos-success);
}

.slos-status-badge.rejected {
	background: rgba(239, 68, 68, 0.15);
	color: var(--slos-error);
}

.slos-status-badge.withdrawn {
	background: rgba(245, 158, 11, 0.15);
	color: var(--slos-warning);
}

.slos-status-badge.pending {
	background: rgba(161, 161, 170, 0.15);
	color: var(--slos-text-muted);
}

/* Type Badge */
.slos-type-badge {
	display: inline-block;
	padding: 4px 10px;
	background: var(--slos-bg-input);
	border-radius: 6px;
	font-size: 12px;
	color: var(--slos-text-secondary);
}

/* Quick Actions Sidebar */
.slos-quick-actions {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.slos-quick-action {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 14px 16px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 8px;
	color: var(--slos-text-secondary);
	text-decoration: none;
	transition: all 0.2s;
	cursor: pointer;
}

.slos-quick-action:hover {
	border-color: var(--slos-accent);
	color: var(--slos-accent);
	transform: translateX(4px);
}

.slos-quick-action .dashicons {
	font-size: 20px;
	width: 20px;
	height: 20px;
	color: var(--slos-accent);
}

.slos-quick-action span {
	flex: 1;
	font-size: 14px;
}

.slos-quick-action .arrow {
	color: var(--slos-text-muted);
}

/* Consent Breakdown */
.slos-consent-breakdown {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.slos-breakdown-item {
	display: flex;
	align-items: center;
	gap: 12px;
}

.slos-breakdown-label {
	width: 100px;
	font-size: 13px;
	color: var(--slos-text-secondary);
}

.slos-breakdown-bar {
	flex: 1;
	height: 8px;
	background: var(--slos-bg-input);
	border-radius: 4px;
	overflow: hidden;
}

.slos-breakdown-fill {
	height: 100%;
	border-radius: 4px;
	transition: width 0.5s ease;
}

.slos-breakdown-fill.necessary { background: var(--slos-success); }
.slos-breakdown-fill.analytics { background: var(--slos-accent); }
.slos-breakdown-fill.marketing { background: var(--slos-purple); }
.slos-breakdown-fill.preferences { background: var(--slos-info); }

.slos-breakdown-value {
	width: 50px;
	font-size: 13px;
	font-weight: 600;
	color: var(--slos-text-primary);
	text-align: right;
}

/* Activity Feed */
.slos-activity-feed {
	max-height: 400px;
	overflow-y: auto;
}

.slos-activity-item {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	padding: 14px 0;
	border-bottom: 1px solid var(--slos-border);
}

.slos-activity-item:last-child {
	border-bottom: none;
}

.slos-activity-icon {
	width: 32px;
	height: 32px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.slos-activity-icon.accepted {
	background: rgba(34, 197, 94, 0.15);
	color: var(--slos-success);
}

.slos-activity-icon.rejected {
	background: rgba(239, 68, 68, 0.15);
	color: var(--slos-error);
}

.slos-activity-icon.withdrawn {
	background: rgba(245, 158, 11, 0.15);
	color: var(--slos-warning);
}

.slos-activity-content {
	flex: 1;
	min-width: 0;
}

.slos-activity-title {
	font-size: 14px;
	color: var(--slos-text-primary);
	margin-bottom: 4px;
}

.slos-activity-meta {
	font-size: 12px;
	color: var(--slos-text-muted);
}

/* Alerts Widget */
.slos-alerts-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.slos-alert-item {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	padding: 12px;
	background: var(--slos-bg-input);
	border-radius: 8px;
	border-left: 3px solid;
}

.slos-alert-item.warning {
	border-left-color: var(--slos-warning);
}

.slos-alert-item.info {
	border-left-color: var(--slos-info);
}

.slos-alert-item.error {
	border-left-color: var(--slos-error);
}

.slos-alert-icon {
	flex-shrink: 0;
	font-size: 16px;
}

.slos-alert-item.warning .slos-alert-icon { color: var(--slos-warning); }
.slos-alert-item.info .slos-alert-icon { color: var(--slos-info); }
.slos-alert-item.error .slos-alert-icon { color: var(--slos-error); }

.slos-alert-text {
	font-size: 13px;
	color: var(--slos-text-secondary);
	line-height: 1.4;
}

/* Empty State */
.slos-empty-state {
	text-align: center;
	padding: 48px 24px;
	color: var(--slos-text-muted);
}

.slos-empty-state .dashicons {
	font-size: 48px;
	width: 48px;
	height: 48px;
	margin-bottom: 16px;
	opacity: 0.5;
}

.slos-empty-state p {
	font-size: 14px;
	margin: 0;
}

/* Buttons */
.slos-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 10px 18px;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s;
	text-decoration: none;
	border: none;
}

.slos-btn-primary {
	background: linear-gradient(135deg, var(--slos-accent), var(--slos-accent-hover));
	color: white;
}

.slos-btn-primary:hover {
	transform: translateY(-1px);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.slos-btn-secondary {
	background: transparent;
	border: 1px solid var(--slos-border);
	color: var(--slos-text-secondary);
}

.slos-btn-secondary:hover {
	border-color: var(--slos-accent);
	color: var(--slos-accent);
}

.slos-btn-ghost {
	background: transparent;
	color: var(--slos-text-muted);
	padding: 8px 12px;
}

.slos-btn-ghost:hover {
	color: var(--slos-accent);
}

/* Region Stats */
.slos-region-list {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.slos-region-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 10px 12px;
	background: var(--slos-bg-input);
	border-radius: 8px;
}

.slos-region-flag {
	font-size: 20px;
}

.slos-region-info {
	flex: 1;
}

.slos-region-name {
	font-size: 13px;
	color: var(--slos-text-primary);
	font-weight: 500;
}

.slos-region-framework {
	font-size: 11px;
	color: var(--slos-text-muted);
}

.slos-region-percent {
	font-size: 14px;
	font-weight: 600;
	color: var(--slos-accent);
}

/* View All Link */
.slos-view-all {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	padding: 12px;
	margin-top: 16px;
	background: var(--slos-bg-input);
	border-radius: 8px;
	color: var(--slos-accent);
	text-decoration: none;
	font-size: 13px;
	font-weight: 500;
	transition: all 0.2s;
}

.slos-view-all:hover {
	background: rgba(59, 130, 246, 0.1);
}
</style>

<div class="wrap slos-compliance-v3">
	<!-- Command Bar -->
	<div class="slos-command-bar">
		<div class="slos-command-left">
			<div class="slos-command-title">
				<div class="icon">
					<span class="dashicons dashicons-shield-alt"></span>
				</div>
				<div>
					<h1><?php esc_html_e( 'Compliance Center', 'shahi-legalflowsuite' ); ?></h1>
					<div class="subtitle"><?php esc_html_e( 'Consent & Privacy Management', 'shahi-legalflowsuite' ); ?></div>
				</div>
			</div>
		</div>

		<div class="slos-command-search">
			<span class="dashicons dashicons-search search-icon"></span>
			<input type="text" placeholder="<?php esc_attr_e( 'Search consents, users, logs...', 'shahi-legalflowsuite' ); ?>" id="slos-global-search">
			<span class="shortcut">âŒ˜K</span>
		</div>

		<div class="slos-command-actions">
			<div class="slos-status-indicator">
				<span class="dot"></span>
				<?php esc_html_e( 'All systems operational', 'shahi-legalflowsuite' ); ?>
			</div>
			
			<div class="slos-cmd-divider"></div>
			
			<button class="slos-cmd-btn" id="slos-export-btn">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Export', 'shahi-legalflowsuite' ); ?>
			</button>
			
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalflowsuite-settings' ) ); ?>" class="slos-cmd-btn">
				<span class="dashicons dashicons-admin-generic"></span>
			</a>
			
			<button class="slos-cmd-btn primary" id="slos-refresh-data">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Refresh', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
	</div>

	<!-- Tab Navigation -->
	<nav class="slos-tab-nav" aria-label="<?php esc_attr_e( 'Compliance sections', 'shahi-legalflowsuite' ); ?>">
		<?php foreach ( $tabs as $tab_key => $tab_config ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $tab_key, $current_url ) ); ?>" 
				class="slos-tab-item <?php echo $current_tab === $tab_key ? 'active' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $tab_config['icon'] ); ?>"></span>
				<?php echo esc_html( $tab_config['label'] ); ?>
				<?php if ( 'cookies' === $tab_key ) : ?>
					<span class="badge">3</span>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<!-- Main Content -->
	<div class="slos-compliance-content">
		<?php $this->render_tab_content( $stats, $recent_activity ); ?>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
	const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
	
	// Keyboard shortcut for search
	$(document).on('keydown', function(e) {
		if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
			e.preventDefault();
			$('#slos-global-search').focus();
		}
		// ESC to blur search
		if (e.key === 'Escape') {
			$('#slos-global-search').blur();
		}
	});
	
	// Global search functionality
	let searchTimeout;
	$('#slos-global-search').on('input', function() {
		const query = $(this).val().trim();
		clearTimeout(searchTimeout);
		
		if (query.length >= 2) {
			searchTimeout = setTimeout(function() {
				// Could implement global search across consents here
				console.log('Global search:', query);
			}, 500);
		}
	});

	// Refresh button
	$('#slos-refresh-data').on('click', function() {
		const $btn = $(this);
		const $icon = $btn.find('.dashicons');
		
		$icon.css('animation', 'spin 1s linear infinite');
		$btn.prop('disabled', true);
		
		// Brief delay to show animation then reload
		setTimeout(function() {
			location.reload();
		}, 500);
	});

	// Export button
	$('#slos-export-btn').on('click', function() {
		const $btn = $(this);
		$btn.prop('disabled', true);
		
		// Download all consents as CSV
		window.location.href = API_BASE + '/consents/export/download?format=csv&_wpnonce=' + NONCE;
		
		setTimeout(function() {
			$btn.prop('disabled', false);
		}, 2000);
	});
	
	// Tab navigation with URL hash
	function getActiveTabFromHash() {
		const hash = window.location.hash.replace('#', '');
		if (hash && $('.slos-tab-btn[data-tab="' + hash + '"]').length) {
			return hash;
		}
		return 'dashboard';
	}
	
	function activateTab(tabId) {
		// Update URL without page reload
		history.pushState(null, null, '#' + tabId);
		
		// Update tab buttons
		$('.slos-tab-btn').removeClass('active');
		$('.slos-tab-btn[data-tab="' + tabId + '"]').addClass('active');
		
		// Update tab content
		$('.slos-tab-content').removeClass('active').hide();
		$('#' + tabId + '-tab').addClass('active').fadeIn(200);
	}
	
	// Tab click handler
	$(document).on('click', '.slos-tab-btn', function(e) {
		e.preventDefault();
		const tabId = $(this).data('tab');
		if (tabId) {
			activateTab(tabId);
		}
	});
	
	// Handle browser back/forward
	$(window).on('popstate', function() {
		const tabId = getActiveTabFromHash();
		activateTab(tabId);
	});
	
	// Add spin animation keyframes if not already present
	if (!$('style#slos-spin-keyframes').length) {
		$('head').append('<style id="slos-spin-keyframes">@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>');
	}
});
</script>

