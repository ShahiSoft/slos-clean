<?php
/**
 * Config Sync Tab - Multi-Site Configuration Management
 *
 * Modern card-based design for export/import compliance configuration.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get available exports
$exports = array();
if ( class_exists( '\ShahiLegalFlowSuite\Services\Config_Sync_Service' ) ) {
	$sync_service = new \ShahiLegalFlowSuite\Services\Config_Sync_Service();
	$exports      = $sync_service->get_available_exports();
}
?>

<style>
.slos-config-sync-page {
	max-width: 1400px;
	margin: 0 auto;
	padding: 24px 0;
}

/* Header */
.slos-sync-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 32px;
	flex-wrap: wrap;
	gap: 20px;
}

.slos-sync-header-content {
	display: flex;
	align-items: center;
	gap: 16px;
}

.slos-sync-header-icon {
	width: 56px;
	height: 56px;
	background: linear-gradient(135deg, var(--slos-primary) 0%, var(--slos-accent) 100%);
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.slos-sync-header-icon .dashicons {
	color: white;
	font-size: 28px;
	width: 28px;
	height: 28px;
}

.slos-sync-header-text h2 {
	margin: 0 0 8px 0;
	font-size: 24px;
	font-weight: 700;
	color: var(--slos-text-primary);
}

.slos-sync-header-text p {
	margin: 0;
	font-size: 14px;
	color: var(--slos-text-muted);
}

/* Info Banner */
.slos-sync-intro {
	background: linear-gradient(135deg, rgba(79, 172, 254, 0.08) 0%, rgba(34, 197, 94, 0.08) 100%);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	padding: 20px 24px;
	margin-bottom: 32px;
	display: flex;
	gap: 16px;
}

.slos-sync-intro-icon {
	font-size: 20px;
	width: 20px;
	height: 20px;
	color: var(--slos-primary);
	flex-shrink: 0;
	margin-top: 2px;
}

.slos-sync-intro-text h3 {
	margin: 0 0 8px 0;
	font-size: 15px;
	font-weight: 600;
	color: var(--slos-text-primary);
}

.slos-sync-intro-text p {
	margin: 0;
	font-size: 14px;
	line-height: 1.6;
	color: var(--slos-text-muted);
}

/* Grid Layout - 2 cards per row */
.slos-sync-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(520px, 1fr));
	gap: 24px;
	margin-bottom: 32px;
}

@media (max-width: 1200px) {
	.slos-sync-grid {
		grid-template-columns: 1fr;
	}
}

/* Card Styles */
.slos-sync-card {
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	overflow: hidden;
	display: flex;
	flex-direction: column;
	transition: all 0.3s ease;
	height: 100%;
}

.slos-sync-card:hover {
	border-color: var(--slos-accent);
	box-shadow: 0 4px 12px rgba(79, 172, 254, 0.15);
}

.slos-sync-card-header {
	background: var(--slos-bg-input);
	padding: 20px 24px;
	border-bottom: 1px solid var(--slos-border);
	display: flex;
	align-items: center;
	gap: 12px;
}

.slos-sync-card-header-icon {
	width: 40px;
	height: 40px;
	background: linear-gradient(135deg, var(--slos-primary-dark) 0%, var(--slos-primary) 100%);
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.slos-sync-card-header-icon .dashicons {
	color: white;
	font-size: 20px;
	width: 20px;
	height: 20px;
}

.slos-sync-card-header-text h3 {
	margin: 0 0 4px 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--slos-text-primary);
}

.slos-sync-card-header-text p {
	margin: 0;
	font-size: 13px;
	color: var(--slos-text-muted);
}

.slos-sync-card-body {
	padding: 16px;
	flex-grow: 1;
	display: flex;
	flex-direction: column;
	overflow: visible;
}

/* Form Groups */
.slos-form-group {
	margin-bottom: 12px;
}

.slos-form-group:last-child {
	margin-bottom: 0;
}

.slos-form-group label {
	display: block;
	font-size: 13px;
	font-weight: 500;
	color: var(--slos-text-primary);
	margin-bottom: 6px;
}

.slos-form-group label .required {
	color: var(--slos-error);
}

.slos-form-group input[type="text"],
.slos-form-group textarea,
.slos-form-group select {
	width: 100%;
	padding: 8px 10px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	font-size: 13px;
	color: var(--slos-text-primary);
	font-family: inherit;
	transition: all 0.2s ease;
}

.slos-form-group input:focus,
.slos-form-group textarea:focus,
.slos-form-group select:focus {
	outline: none;
	border-color: var(--slos-accent);
	box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
}

.slos-form-group textarea {
	resize: none;
	height: 80px;
	overflow-y: auto;
}

.slos-form-group textarea.slos-code-editor {
	height: 100px;
	font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
	font-size: 12px;
	line-height: 1.5;
}

/* Checkbox Group */
.slos-checkbox-group {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 6px;
}

.slos-checkbox {
	display: flex;
	align-items: flex-start;
	gap: 8px;
	cursor: pointer;
	padding: 6px 8px;
	border-radius: 6px;
	transition: all 0.2s ease;
	font-size: 13px;
}

.slos-checkbox:hover {
	background: rgba(79, 172, 254, 0.08);
}

.slos-checkbox input[type="checkbox"] {
	width: 16px;
	height: 16px;
	margin-top: 1px;
	cursor: pointer;
	accent-color: var(--slos-primary);
	flex-shrink: 0;
}

.slos-checkbox span {
	flex: 1;
	display: block;
}

.slos-checkbox span strong {
	display: block;
	font-weight: 500;
	color: var(--slos-text-primary);
	margin-bottom: 2px;
}

.slos-checkbox small {
	display: block;
	font-size: 12px;
	color: var(--slos-text-muted);
}

/* Select All / None */
.slos-checkbox-actions {
	display: flex;
	align-items: center;
	gap: 6px;
	margin-top: 8px;
	padding-top: 8px;
	border-top: 1px solid var(--slos-border);
	font-size: 12px;
}

.slos-link-btn {
	background: none;
	border: none;
	color: var(--slos-accent);
	cursor: pointer;
	font-size: 12px;
	font-weight: 500;
	padding: 2px 6px;
	border-radius: 4px;
	transition: all 0.2s ease;
	outline: none;
}

.slos-link-btn:hover {
	background: rgba(79, 172, 254, 0.1);
	color: var(--slos-primary);
}

.slos-link-btn:focus-visible {
	outline: 2px solid var(--slos-primary);
	outline-offset: 1px;
}

/* Buttons */
.slos-button-group {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 8px;
	margin-top: 12px;
}

.slos-button-group.single {
	grid-template-columns: 1fr;
}

.slos-button-group.three {
	grid-template-columns: repeat(3, 1fr);
}

.slos-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	padding: 8px 12px;
	border: none;
	border-radius: 6px;
	font-size: 13px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s ease;
	text-decoration: none;
	white-space: nowrap;
	line-height: 1;
	outline: none;
}

.slos-btn:focus-visible {
	outline: 2px solid var(--slos-primary);
	outline-offset: 2px;
}

.slos-btn:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.slos-btn-primary {
	background: linear-gradient(135deg, var(--slos-primary-dark) 0%, var(--slos-primary) 100%);
	color: white;
}

.slos-btn-primary:hover:not(:disabled) {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
}

.slos-btn-secondary {
	background: var(--slos-bg-input);
	color: var(--slos-text-primary);
	border: 1px solid var(--slos-border);
}

.slos-btn-secondary:hover:not(:disabled) {
	border-color: var(--slos-accent);
	background: rgba(79, 172, 254, 0.08);
}

.slos-btn-ghost {
	background: transparent;
	color: var(--slos-text-muted);
	border: 1px solid var(--slos-border);
}

.slos-btn-ghost:hover:not(:disabled) {
	color: var(--slos-accent);
	border-color: var(--slos-accent);
}

.slos-btn .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
	margin: 0;
}

/* File Upload */
.slos-file-upload {
	position: relative;
	border: 2px dashed var(--slos-border);
	border-radius: 8px;
	padding: 16px 12px;
	text-align: center;
	transition: all 0.2s ease;
	cursor: pointer;
}

.slos-file-upload:hover,
.slos-file-upload.dragover {
	border-color: var(--slos-accent);
	background: rgba(79, 172, 254, 0.08);
}

.slos-upload-placeholder {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8px;
	color: var(--slos-text-muted);
}

.slos-upload-placeholder .dashicons {
	font-size: 28px;
	width: 28px;
	height: 28px;
	color: var(--slos-text-muted);
}

.slos-upload-placeholder p {
	margin: 0;
	font-size: 13px;
}

.slos-upload-placeholder small {
	display: block;
	font-size: 12px;
	color: var(--slos-text-muted);
	margin-top: 2px;
}

.slos-file-info {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 16px;
	background: var(--slos-bg-input);
	border-radius: 8px;
	gap: 12px;
}

.slos-file-info .dashicons {
	font-size: 20px;
	width: 20px;
	height: 20px;
	color: var(--slos-accent);
	flex-shrink: 0;
}

.slos-file-info .filename {
	flex: 1;
	font-size: 14px;
	color: var(--slos-text-primary);
	word-break: break-all;
}

.slos-remove-file {
	background: none;
	border: none;
	color: var(--slos-error);
	cursor: pointer;
	font-size: 24px;
	width: 24px;
	height: 24px;
	padding: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.2s ease;
}

.slos-remove-file:hover {
	background: rgba(239, 68, 68, 0.1);
	border-radius: 4px;
}

/* Divider */
.slos-divider {
	display: flex;
	align-items: center;
	gap: 10px;
	margin: 10px 0;
	color: var(--slos-text-muted);
	font-size: 12px;
	font-weight: 500;
	text-transform: uppercase;
}

.slos-divider::before,
.slos-divider::after {
	content: '';
	flex: 1;
	height: 1px;
	background: var(--slos-border);
}

/* Status Messages */
.slos-status-message {
	padding: 10px 12px;
	border-radius: 6px;
	font-size: 13px;
	margin-top: 10px;
	animation: slideDown 0.3s ease;
}

@keyframes slideDown {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.slos-status-message.slos-success {
	background: rgba(34, 197, 94, 0.1);
	color: #059669;
	border: 1px solid rgba(34, 197, 94, 0.2);
}

.slos-status-message.slos-error {
	background: rgba(239, 68, 68, 0.1);
	color: #dc2626;
	border: 1px solid rgba(239, 68, 68, 0.2);
}

.slos-status-message.slos-warning {
	background: rgba(217, 119, 6, 0.1);
	color: #b45309;
	border: 1px solid rgba(217, 119, 6, 0.2);
}

.slos-status-message.slos-info {
	background: rgba(79, 172, 254, 0.1);
	color: #0284c7;
	border: 1px solid rgba(79, 172, 254, 0.2);
}

/* Results Box */
.slos-results-box {
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	padding: 12px;
	margin-top: 10px;
	max-height: 200px;
	overflow-y: auto;
	animation: slideDown 0.3s ease;
	font-size: 12px;
}

.slos-results-box h4 {
	margin: 0 0 16px 0;
	font-size: 14px;
	font-weight: 600;
	color: var(--slos-text-primary);
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.slos-results-box h5 {
	margin: 16px 0 12px 0;
	font-size: 13px;
	font-weight: 600;
	color: var(--slos-text-primary);
}

.slos-results-box h5:first-child {
	margin-top: 0;
}

.slos-results-content {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.slos-result-item {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	padding: 8px 12px;
	border-radius: 6px;
	font-size: 13px;
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
}

.slos-result-item .dashicons {
	font-size: 14px;
	width: 14px;
	height: 14px;
	margin-top: 2px;
	flex-shrink: 0;
}

.slos-result-item.slos-success {
	background: rgba(34, 197, 94, 0.08);
	border-color: rgba(34, 197, 94, 0.2);
	color: #059669;
}

.slos-result-item.slos-error {
	background: rgba(239, 68, 68, 0.08);
	border-color: rgba(239, 68, 68, 0.2);
	color: #dc2626;
}

.slos-result-item.slos-warning {
	background: rgba(217, 119, 6, 0.08);
	border-color: rgba(217, 119, 6, 0.2);
	color: #b45309;
}

.slos-result-item.slos-added {
	background: rgba(34, 197, 94, 0.08);
	border-color: rgba(34, 197, 94, 0.2);
	color: #059669;
}

.slos-result-item.slos-removed {
	background: rgba(239, 68, 68, 0.08);
	border-color: rgba(239, 68, 68, 0.2);
	color: #dc2626;
}

.slos-result-item.slos-changed {
	background: rgba(217, 119, 6, 0.08);
	border-color: rgba(217, 119, 6, 0.2);
	color: #b45309;
}

.slos-result-item.slos-muted {
	background: rgba(113, 113, 122, 0.08);
	border-color: rgba(113, 113, 122, 0.2);
	color: var(--slos-text-muted);
}

/* Summary Stats */
.slos-comparison-summary,
.slos-import-summary {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
	gap: 12px;
	margin-bottom: 16px;
	padding-bottom: 16px;
	border-bottom: 1px solid var(--slos-border);
}

.slos-stat {
	text-align: center;
	padding: 12px;
	background: var(--slos-bg-card);
	border-radius: 6px;
	border: 1px solid var(--slos-border);
}

.slos-stat strong {
	display: block;
	font-size: 18px;
	color: var(--slos-primary);
	margin-bottom: 4px;
}

.slos-stat.slos-success strong {
	color: #059669;
}

.slos-stat.slos-error strong {
	color: #dc2626;
}

.slos-stat.slos-warning strong {
	color: #b45309;
}

/* Exports Table */
.slos-exports-section {
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	overflow: hidden;
}

.slos-exports-header {
	background: var(--slos-bg-input);
	padding: 20px 24px;
	border-bottom: 1px solid var(--slos-border);
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 16px;
}

.slos-exports-header h3 {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--slos-text-primary);
	display: flex;
	align-items: center;
	gap: 10px;
}

.slos-exports-header h3 .dashicons {
	font-size: 20px;
	width: 20px;
	height: 20px;
	color: var(--slos-primary);
}

.slos-exports-body {
	padding: 24px;
}

.slos-empty-state {
	text-align: center;
	padding: 60px 20px;
	color: var(--slos-text-muted);
}

.slos-empty-state .dashicons {
	font-size: 48px;
	width: 48px;
	height: 48px;
	margin: 0 auto 16px;
	opacity: 0.5;
}

.slos-empty-state p {
	margin: 0 0 8px 0;
	font-size: 14px;
}

/* Exports Table */
.slos-exports-table-wrapper {
	overflow-x: auto;
}

.slos-exports-table {
	width: 100%;
	border-collapse: collapse;
	font-size: 14px;
}

.slos-exports-table thead {
	background: var(--slos-bg-input);
	border-bottom: 2px solid var(--slos-border);
}

.slos-exports-table th {
	padding: 12px 16px;
	text-align: left;
	font-weight: 600;
	color: var(--slos-text-primary);
	font-size: 13px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.slos-exports-table td {
	padding: 12px 16px;
	border-bottom: 1px solid var(--slos-border);
}

.slos-exports-table tbody tr:hover {
	background: var(--slos-bg-input);
}

.slos-exports-table strong {
	color: var(--slos-text-primary);
}

.slos-exports-table small {
	display: block;
	color: var(--slos-text-muted);
	margin-top: 4px;
}

.slos-action-buttons {
	display: flex;
	align-items: center;
	gap: 8px;
}

.slos-action-btn {
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	cursor: pointer;
	transition: all 0.2s ease;
	color: var(--slos-text-muted);
}

.slos-action-btn:hover {
	border-color: var(--slos-accent);
	background: rgba(79, 172, 254, 0.1);
	color: var(--slos-accent);
}

.slos-action-btn .dashicons {
	font-size: 14px;
	width: 14px;
	height: 14px;
}

/* Spinning icon animation */
.slos-spin {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	to {
		transform: rotate(360deg);
	}
}

/* Code Editor */
.slos-code-editor {
	font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
	font-size: 12px;
	line-height: 1.6;
	background: var(--slos-bg-card);
	color: var(--slos-text-primary);
}

/* Screen Reader Text */
.screen-reader-text {
	position: absolute;
	left: -10000px;
	width: 1px;
	height: 1px;
	overflow: hidden;
}

/* Responsive */
@media (max-width: 768px) {
	.slos-sync-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.slos-sync-grid {
		grid-template-columns: 1fr;
	}

	.slos-button-group {
		grid-template-columns: 1fr;
	}

	.slos-checkbox-group {
		grid-template-columns: repeat(2, 1fr);
	}

	.slos-exports-table {
		font-size: 13px;
	}

	.slos-exports-table th,
	.slos-exports-table td {
		padding: 8px 12px;
	}
}
</style>

<div class="slos-config-sync-page">
	<!-- Header -->
	<div class="slos-sync-header">
		<div class="slos-sync-header-content">
			<div class="slos-sync-header-icon">
				<span class="dashicons dashicons-cloud-sync"></span>
			</div>
			<div class="slos-sync-header-text">
				<h2><?php esc_html_e( 'Configuration Sync', 'shahi-legalflowsuite' ); ?></h2>
				<p><?php esc_html_e( 'Export and import compliance settings across sites', 'shahi-legalflowsuite' ); ?></p>
			</div>
		</div>
		<?php if ( is_multisite() ) : ?>
			<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=slos-network-compliance' ) ); ?>" class="slos-btn slos-btn-secondary">
				<span class="dashicons dashicons-admin-multisite"></span>
				<?php esc_html_e( 'Network Overview', 'shahi-legalflowsuite' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<!-- Intro Banner -->
	<div class="slos-sync-intro">
		<div class="slos-sync-intro-icon">
			<span class="dashicons dashicons-info"></span>
		</div>
		<div class="slos-sync-intro-text">
			<h3><?php esc_html_e( 'About Configuration Sync', 'shahi-legalflowsuite' ); ?></h3>
			<p><?php esc_html_e( 'Export your compliance settings (banner configuration, geo rules, legal pages, etc.) to share across multiple sites. Configuration profiles never include personal data like consent records or DSR requests.', 'shahi-legalflowsuite' ); ?></p>
		</div>
	</div>

	<!-- Main Grid - 2 Cards per Row -->
	<div class="slos-sync-grid">
		<!-- Export Card -->
		<div class="slos-sync-card">
			<div class="slos-sync-card-header">
				<div class="slos-sync-card-header-icon">
					<span class="dashicons dashicons-upload"></span>
				</div>
				<div class="slos-sync-card-header-text">
					<h3><?php esc_html_e( 'Export Configuration', 'shahi-legalflowsuite' ); ?></h3>
					<p><?php esc_html_e( 'Save your current settings', 'shahi-legalflowsuite' ); ?></p>
				</div>
			</div>
			<div class="slos-sync-card-body">
				<form id="slos-export-form" role="form" aria-label="<?php esc_attr_e( 'Export Configuration Form', 'shahi-legalflowsuite' ); ?>">
					<!-- Profile Name -->
					<div class="slos-form-group">
						<label for="slos-export-name">
							<?php esc_html_e( 'Profile Name', 'shahi-legalflowsuite' ); ?>
							<span class="required">*</span>
						</label>
						<input 
							type="text" 
							id="slos-export-name" 
							name="name" 
							placeholder="<?php esc_attr_e( 'e.g., Production Config 2025-01', 'shahi-legalflowsuite' ); ?>"
							required
							aria-required="true"
							aria-describedby="slos-export-name-desc"
						>
						<span id="slos-export-name-desc" class="screen-reader-text"><?php esc_html_e( 'Enter a descriptive name for this configuration profile', 'shahi-legalflowsuite' ); ?></span>
					</div>

					<!-- Description -->
					<div class="slos-form-group">
						<label for="slos-export-description">
							<?php esc_html_e( 'Description (Optional)', 'shahi-legalflowsuite' ); ?>
						</label>
						<textarea 
							id="slos-export-description" 
							name="description" 
							rows="3"
							placeholder="<?php esc_attr_e( 'Notes about this configuration...', 'shahi-legalflowsuite' ); ?>"
						></textarea>
					</div>

					<!-- Settings to Export -->
					<div class="slos-form-group">
						<label id="slos-export-options-label"><?php esc_html_e( 'Settings to Export', 'shahi-legalflowsuite' ); ?></label>
						<div class="slos-checkbox-group" role="group" aria-labelledby="slos-export-options-label">
							<label class="slos-checkbox">
								<input type="checkbox" name="options[]" value="slos_banner_settings" checked aria-label="<?php esc_attr_e( 'Banner Configuration', 'shahi-legalflowsuite' ); ?>">
								<span>
									<strong><?php esc_html_e( 'Banner', 'shahi-legalflowsuite' ); ?></strong>
									<small><?php esc_html_e( 'Configuration', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
							<label class="slos-checkbox">
								<input type="checkbox" name="options[]" value="slos_geo_rules" checked aria-label="<?php esc_attr_e( 'Geo Rules', 'shahi-legalflowsuite' ); ?>">
								<span>
									<strong><?php esc_html_e( 'Geo Rules', 'shahi-legalflowsuite' ); ?></strong>
									<small><?php esc_html_e( 'Geographic targeting', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
							<label class="slos-checkbox">
								<input type="checkbox" name="options[]" value="slos_legal_pages" checked aria-label="<?php esc_attr_e( 'Legal Pages', 'shahi-legalflowsuite' ); ?>">
								<span>
									<strong><?php esc_html_e( 'Legal Pages', 'shahi-legalflowsuite' ); ?></strong>
									<small><?php esc_html_e( 'Document assignments', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
							<label class="slos-checkbox">
								<input type="checkbox" name="options[]" value="slos_accessibility_settings" checked aria-label="<?php esc_attr_e( 'Accessibility Settings', 'shahi-legalflowsuite' ); ?>">
								<span>
									<strong><?php esc_html_e( 'Accessibility', 'shahi-legalflowsuite' ); ?></strong>
									<small><?php esc_html_e( 'Settings', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
							<label class="slos-checkbox">
								<input type="checkbox" name="options[]" value="slos_dsr_settings" checked aria-label="<?php esc_attr_e( 'DSR Settings', 'shahi-legalflowsuite' ); ?>">
								<span>
									<strong><?php esc_html_e( 'DSR Settings', 'shahi-legalflowsuite' ); ?></strong>
									<small><?php esc_html_e( 'Data subject requests', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
							<label class="slos-checkbox">
								<input type="checkbox" name="options[]" value="slos_cookie_inventory" aria-label="<?php esc_attr_e( 'Cookie Inventory', 'shahi-legalflowsuite' ); ?>">
								<span>
									<strong><?php esc_html_e( 'Cookies', 'shahi-legalflowsuite' ); ?></strong>
									<small><?php esc_html_e( 'Inventory (optional)', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
						</div>
						<div class="slos-checkbox-actions">
							<button type="button" id="slos-select-all" class="slos-link-btn" aria-label="<?php esc_attr_e( 'Select all options', 'shahi-legalflowsuite' ); ?>">
								<?php esc_html_e( 'All', 'shahi-legalflowsuite' ); ?>
							</button>
							<span>|</span>
							<button type="button" id="slos-select-none" class="slos-link-btn" aria-label="<?php esc_attr_e( 'Deselect all options', 'shahi-legalflowsuite' ); ?>">
								<?php esc_html_e( 'None', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>

					<!-- Action Buttons -->
					<div class="slos-button-group">
						<button type="submit" name="action" value="export_file" class="slos-btn slos-btn-primary" id="slos-export-file-btn">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export', 'shahi-legalflowsuite' ); ?>
						</button>
						<button type="submit" name="action" value="export_json" class="slos-btn slos-btn-secondary" id="slos-export-json-btn">
							<span class="dashicons dashicons-editor-code"></span>
							<?php esc_html_e( 'JSON', 'shahi-legalflowsuite' ); ?>
						</button>
					</div>

					<!-- Status Message -->
					<div id="slos-export-status" class="slos-status-message" style="display:none;" role="alert" aria-live="polite"></div>
				</form>
			</div>
		</div>

		<!-- Import Card -->
		<div class="slos-sync-card">
			<div class="slos-sync-card-header">
				<div class="slos-sync-card-header-icon">
					<span class="dashicons dashicons-download"></span>
				</div>
				<div class="slos-sync-card-header-text">
					<h3><?php esc_html_e( 'Import Configuration', 'shahi-legalflowsuite' ); ?></h3>
					<p><?php esc_html_e( 'Apply saved settings', 'shahi-legalflowsuite' ); ?></p>
				</div>
			</div>
			<div class="slos-sync-card-body">
				<form id="slos-import-form" role="form" aria-label="<?php esc_attr_e( 'Import Configuration Form', 'shahi-legalflowsuite' ); ?>">
					<!-- File Upload -->
					<div class="slos-form-group">
						<label><?php esc_html_e( 'Select Configuration File', 'shahi-legalflowsuite' ); ?></label>
						<div class="slos-file-upload" id="slos-file-dropzone">
							<input type="file" id="slos-import-file" name="file" accept=".json" style="display:none;">
							<div class="slos-upload-placeholder">
								<span class="dashicons dashicons-upload"></span>
								<p>
									<?php esc_html_e( 'Drop file here or', 'shahi-legalflowsuite' ); ?>
									<button type="button" class="slos-link-btn" id="slos-browse-btn">
										<?php esc_html_e( 'browse', 'shahi-legalflowsuite' ); ?>
									</button>
								</p>
								<small><?php esc_html_e( 'JSON files only', 'shahi-legalflowsuite' ); ?></small>
							</div>
							<div class="slos-file-info" id="slos-file-info" style="display:none;">
								<span class="dashicons dashicons-media-document"></span>
								<span class="filename"></span>
								<button type="button" class="slos-remove-file" id="slos-remove-file">×</button>
							</div>
						</div>
					</div>

					<!-- OR Divider -->
					<div class="slos-divider">
						<span><?php esc_html_e( 'OR', 'shahi-legalflowsuite' ); ?></span>
					</div>

					<!-- JSON Paste -->
					<div class="slos-form-group">
						<label for="slos-import-json">
							<?php esc_html_e( 'Paste JSON Configuration', 'shahi-legalflowsuite' ); ?>
						</label>
						<textarea 
							id="slos-import-json" 
							name="json" 
							rows="6"
							class="slos-code-editor"
							placeholder='{"schema_version":"1.0.0","profile":{...},"settings":{...}}'
						></textarea>
					</div>

					<!-- Import Options -->
					<div class="slos-form-group">
						<label id="slos-import-options-label"><?php esc_html_e( 'Import Options', 'shahi-legalflowsuite' ); ?></label>
						<div class="slos-checkbox-group" role="group" aria-labelledby="slos-import-options-label">
							<label class="slos-checkbox">
								<input type="checkbox" name="merge" id="slos-merge-option" aria-describedby="slos-merge-desc">
								<span>
									<strong><?php esc_html_e( 'Merge with existing', 'shahi-legalflowsuite' ); ?></strong>
									<small id="slos-merge-desc"><?php esc_html_e( 'Combine values (arrays only)', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
							<label class="slos-checkbox">
								<input type="checkbox" name="dry_run" id="slos-dry-run-option" aria-describedby="slos-dry-run-desc">
								<span>
									<strong><?php esc_html_e( 'Dry run', 'shahi-legalflowsuite' ); ?></strong>
									<small id="slos-dry-run-desc"><?php esc_html_e( 'Preview without changes', 'shahi-legalflowsuite' ); ?></small>
								</span>
							</label>
						</div>
					</div>

					<!-- Action Buttons -->
					<div class="slos-button-group three">
						<button type="button" id="slos-validate-btn" class="slos-btn slos-btn-secondary" aria-label="<?php esc_attr_e( 'Validate configuration', 'shahi-legalflowsuite' ); ?>">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Validate', 'shahi-legalflowsuite' ); ?>
						</button>
						<button type="button" id="slos-compare-btn" class="slos-btn slos-btn-secondary" aria-label="<?php esc_attr_e( 'Compare with current settings', 'shahi-legalflowsuite' ); ?>">
							<span class="dashicons dashicons-code-standards"></span>
							<?php esc_html_e( 'Compare', 'shahi-legalflowsuite' ); ?>
						</button>
						<button type="submit" id="slos-import-btn" class="slos-btn slos-btn-primary" disabled aria-label="<?php esc_attr_e( 'Import configuration', 'shahi-legalflowsuite' ); ?>">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Import', 'shahi-legalflowsuite' ); ?>
						</button>
					</div>

					<!-- Status Message -->
					<div id="slos-import-status" class="slos-status-message" style="display:none;" role="alert" aria-live="polite"></div>

					<!-- Results -->
					<div id="slos-validation-results" class="slos-results-box" style="display:none;">
						<h4><?php esc_html_e( 'Validation Results', 'shahi-legalflowsuite' ); ?></h4>
						<div class="slos-results-content"></div>
					</div>

					<div id="slos-comparison-results" class="slos-results-box" style="display:none;">
						<h4><?php esc_html_e( 'Configuration Comparison', 'shahi-legalflowsuite' ); ?></h4>
						<div class="slos-results-content"></div>
					</div>

					<div id="slos-import-results" class="slos-results-box" style="display:none;">
						<h4><?php esc_html_e( 'Import Results', 'shahi-legalflowsuite' ); ?></h4>
						<div class="slos-results-content"></div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Available Exports Section -->
	<div class="slos-exports-section">
		<div class="slos-exports-header">
			<h3>
				<span class="dashicons dashicons-archive"></span>
				<?php esc_html_e( 'Available Exports', 'shahi-legalflowsuite' ); ?>
			</h3>
			<button type="button" id="slos-refresh-exports" class="slos-btn slos-btn-ghost">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Refresh', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
		<div class="slos-exports-body">
			<?php if ( empty( $exports ) ) : ?>
				<div class="slos-empty-state">
					<span class="dashicons dashicons-cloud"></span>
					<p><?php esc_html_e( 'No configuration exports yet.', 'shahi-legalflowsuite' ); ?></p>
					<p><?php esc_html_e( 'Export your first configuration profile above.', 'shahi-legalflowsuite' ); ?></p>
				</div>
			<?php else : ?>
				<div class="slos-exports-table-wrapper">
					<table class="slos-exports-table" id="slos-exports-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Profile Name', 'shahi-legalflowsuite' ); ?></th>
								<th><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></th>
								<th><?php esc_html_e( 'Exported', 'shahi-legalflowsuite' ); ?></th>
								<th><?php esc_html_e( 'Source Site', 'shahi-legalflowsuite' ); ?></th>
								<th><?php esc_html_e( 'Size', 'shahi-legalflowsuite' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $exports as $export ) : ?>
								<tr data-filename="<?php echo esc_attr( $export['file'] ); ?>">
									<td>
										<strong><?php echo esc_html( $export['name'] ?? __( 'Untitled', 'shahi-legalflowsuite' ) ); ?></strong>
									</td>
									<td>
										<span class="slos-description">
											<?php echo esc_html( $export['description'] ?? '—' ); ?>
										</span>
									</td>
									<td>
										<?php echo esc_html( $export['exported_at'] ?? '—' ); ?>
										<small><?php echo esc_html( sprintf( __( 'by %s', 'shahi-legalflowsuite' ), $export['exported_by'] ?? 'Unknown' ) ); ?></small>
									</td>
									<td>
										<small><?php echo esc_html( $export['site_url'] ?? '—' ); ?></small>
									</td>
									<td>
										<?php echo esc_html( size_format( $export['size'] ?? 0 ) ); ?>
									</td>
									<td>
										<div class="slos-action-buttons">
											<button 
												type="button" 
												class="slos-action-btn slos-download-export" 
												data-filename="<?php echo esc_attr( $export['file'] ); ?>"
												title="<?php esc_attr_e( 'Download', 'shahi-legalflowsuite' ); ?>"
											>
												<span class="dashicons dashicons-download"></span>
											</button>
											<button 
												type="button" 
												class="slos-action-btn slos-import-export" 
												data-filename="<?php echo esc_attr( $export['file'] ); ?>"
												title="<?php esc_attr_e( 'Import', 'shahi-legalflowsuite' ); ?>"
											>
												<span class="dashicons dashicons-upload"></span>
											</button>
											<button 
												type="button" 
												class="slos-action-btn slos-compare-export" 
												data-filename="<?php echo esc_attr( $export['file'] ); ?>"
												title="<?php esc_attr_e( 'Compare', 'shahi-legalflowsuite' ); ?>"
											>
												<span class="dashicons dashicons-code-standards"></span>
											</button>
											<button 
												type="button" 
												class="slos-action-btn slos-delete-export" 
												data-filename="<?php echo esc_attr( $export['file'] ); ?>"
												title="<?php esc_attr_e( 'Delete', 'shahi-legalflowsuite' ); ?>"
											>
												<span class="dashicons dashicons-trash"></span>
											</button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
