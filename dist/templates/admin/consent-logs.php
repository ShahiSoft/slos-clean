<!-- Consent Logs Admin Template -->
<div class="wrap slos-consent-logs">
	<h1><?php esc_html_e( 'Consent Audit Logs', 'shahi-legalflowsuite' ); ?></h1>

	<div class="slos-logs-filters">
		<form method="GET" action="" id="slos-logs-filter-form">
			<input type="hidden" name="page" value="slos-consent-logs">
			
			<div class="slos-filter-row">
				<!-- Date Range Filter -->
				<div class="slos-filter-group">
					<label for="date_from"><?php esc_html_e( 'From Date:', 'shahi-legalflowsuite' ); ?></label>
					<input type="date" 
						id="date_from" 
						name="date_from" 
						value="<?php echo esc_attr( $_GET['date_from'] ?? '' ); ?>">
				</div>

				<div class="slos-filter-group">
					<label for="date_to"><?php esc_html_e( 'To Date:', 'shahi-legalflowsuite' ); ?></label>
					<input type="date" 
						id="date_to" 
						name="date_to" 
						value="<?php echo esc_attr( $_GET['date_to'] ?? '' ); ?>">
				</div>

				<!-- Action Filter -->
				<div class="slos-filter-group">
					<label for="action_filter"><?php esc_html_e( 'Action:', 'shahi-legalflowsuite' ); ?></label>
					<select id="action_filter" name="action">
						<option value=""><?php esc_html_e( 'All Actions', 'shahi-legalflowsuite' ); ?></option>
						<option value="grant" <?php selected( $_GET['action'] ?? '', 'grant' ); ?>><?php esc_html_e( 'Grant', 'shahi-legalflowsuite' ); ?></option>
						<option value="withdraw" <?php selected( $_GET['action'] ?? '', 'withdraw' ); ?>><?php esc_html_e( 'Withdraw', 'shahi-legalflowsuite' ); ?></option>
						<option value="update" <?php selected( $_GET['action'] ?? '', 'update' ); ?>><?php esc_html_e( 'Update', 'shahi-legalflowsuite' ); ?></option>
						<option value="import" <?php selected( $_GET['action'] ?? '', 'import' ); ?>><?php esc_html_e( 'Import', 'shahi-legalflowsuite' ); ?></option>
						<option value="export" <?php selected( $_GET['action'] ?? '', 'export' ); ?>><?php esc_html_e( 'Export', 'shahi-legalflowsuite' ); ?></option>
					</select>
				</div>

				<!-- Purpose Filter -->
				<div class="slos-filter-group">
					<label for="purpose_filter"><?php esc_html_e( 'Purpose:', 'shahi-legalflowsuite' ); ?></label>
					<select id="purpose_filter" name="purpose">
						<option value=""><?php esc_html_e( 'All Purposes', 'shahi-legalflowsuite' ); ?></option>
						<option value="necessary" <?php selected( $_GET['purpose'] ?? '', 'necessary' ); ?>><?php esc_html_e( 'Necessary', 'shahi-legalflowsuite' ); ?></option>
						<option value="analytics" <?php selected( $_GET['purpose'] ?? '', 'analytics' ); ?>><?php esc_html_e( 'Analytics', 'shahi-legalflowsuite' ); ?></option>
						<option value="marketing" <?php selected( $_GET['purpose'] ?? '', 'marketing' ); ?>><?php esc_html_e( 'Marketing', 'shahi-legalflowsuite' ); ?></option>
						<option value="preferences" <?php selected( $_GET['purpose'] ?? '', 'preferences' ); ?>><?php esc_html_e( 'Preferences', 'shahi-legalflowsuite' ); ?></option>
					</select>
				</div>

				<!-- User ID Filter -->
				<div class="slos-filter-group">
					<label for="user_id_filter"><?php esc_html_e( 'User ID:', 'shahi-legalflowsuite' ); ?></label>
					<input type="number" 
						id="user_id_filter" 
						name="user_id" 
						value="<?php echo esc_attr( $_GET['user_id'] ?? '' ); ?>" 
						placeholder="<?php esc_attr_e( 'User ID', 'shahi-legalflowsuite' ); ?>">
				</div>

				<!-- Buttons -->
				<div class="slos-filter-actions">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Filter', 'shahi-legalflowsuite' ); ?>
					</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-consent-logs' ) ); ?>" class="button">
						<?php esc_html_e( 'Clear Filters', 'shahi-legalflowsuite' ); ?>
					</a>
				</div>
			</div>
		</form>
	</div>

	<!-- Logs Table Container -->
	<div id="slos-logs-container" class="slos-logs-table-container">
		<div class="slos-loading" id="slos-logs-loading">
			<span class="spinner is-active"></span>
			<p><?php esc_html_e( 'Loading consent logs...', 'shahi-legalflowsuite' ); ?></p>
		</div>

		<table class="wp-list-table widefat fixed striped" id="slos-logs-table" style="display:none;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Date/Time', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'User ID', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Purpose', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Action', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Method', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'IP Address', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?></th>
				</tr>
			</thead>
			<tbody id="slos-logs-tbody">
				<!-- Dynamic content loaded via JavaScript -->
			</tbody>
		</table>

		<!-- Pagination -->
		<div class="tablenav bottom" id="slos-logs-pagination" style="display:none;">
			<div class="tablenav-pages">
				<span class="displaying-num" id="slos-logs-count"></span>
				<span class="pagination-links" id="slos-logs-nav">
					<!-- Dynamic pagination loaded via JavaScript -->
				</span>
			</div>
		</div>
	</div>

	<!-- Log Details Modal -->
	<div id="slos-log-details-modal" class="slos-modal" style="display:none;">
		<div class="slos-modal-content">
			<div class="slos-modal-header">
				<h2><?php esc_html_e( 'Log Details', 'shahi-legalflowsuite' ); ?></h2>
				<button type="button" class="slos-modal-close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
			<div class="slos-modal-body" id="slos-log-details-content">
				<!-- Dynamic content loaded via JavaScript -->
			</div>
		</div>
	</div>
</div>

<style>
/* Consent Logs - Mac Slate Liquid Theme */
.slos-consent-logs {
	max-width: 1400px;
}

.slos-logs-filters {
	background: #1e293b;
	padding: 20px;
	margin: 20px 0;
	border: 1px solid #334155;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.slos-filter-row {
	display: flex;
	flex-wrap: wrap;
	gap: 15px;
	align-items: flex-end;
}

.slos-filter-group {
	display: flex;
	flex-direction: column;
	gap: 5px;
}

.slos-filter-group label {
	font-weight: 600;
	font-size: 13px;
	color: #f8fafc;
}

.slos-filter-group input,
.slos-filter-group select {
	min-width: 150px;
	background: #0f172a;
	border: 1px solid #334155;
	color: #f8fafc;
	border-radius: 6px;
	padding: 8px 12px;
}

.slos-filter-actions {
	display: flex;
	gap: 10px;
}

.slos-logs-table-container {
	background: #1e293b;
	padding: 20px;
	border: 1px solid #334155;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.slos-loading {
	text-align: center;
	padding: 40px;
	color: #94a3b8;
}

.slos-loading .spinner {
	float: none;
	margin: 0 auto 10px;
}

.slos-action-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
}

.slos-action-badge.grant {
	background: rgba(34, 197, 94, 0.2);
	color: #22c55e;
}

.slos-action-badge.withdraw {
	background: rgba(239, 68, 68, 0.2);
	color: #ef4444;
}

.slos-action-badge.update {
	background: rgba(59, 130, 246, 0.2);
	color: #3b82f6;
}

.slos-action-badge.import,
.slos-action-badge.export {
	background: rgba(245, 158, 11, 0.2);
	color: #f59e0b;
}

.slos-modal {
	position: fixed;
	z-index: 100000;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	overflow: auto;
	background-color: rgba(0,0,0,0.7);
}

.slos-modal-content {
	background-color: #1e293b;
	margin: 5% auto;
	padding: 0;
	border: 1px solid #334155;
	width: 80%;
	max-width: 800px;
	border-radius: 12px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}

.slos-modal-header {
	padding: 20px;
	background: #0f172a;
	border-bottom: 1px solid #334155;
	border-radius: 12px 12px 0 0;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.slos-modal-header h2 {
	margin: 0;
	color: #f8fafc;
}

.slos-modal-close {
	background: none;
	border: none;
	cursor: pointer;
	font-size: 24px;
	padding: 0;
	color: #94a3b8;
}

.slos-modal-close:hover {
	color: #ef4444;
}

.slos-modal-body {
	padding: 20px;
	max-height: 70vh;
	overflow-y: auto;
	color: #f8fafc;
}

.slos-log-details-table {
	width: 100%;
	border-collapse: collapse;
}

.slos-log-details-table th,
.slos-log-details-table td {
	padding: 10px;
	border-bottom: 1px solid #334155;
	text-align: left;
	color: #f8fafc;
}

.slos-log-details-table th {
	font-weight: 600;
	width: 30%;
	background: #0f172a;
	color: #94a3b8;
}

.slos-log-details-json {
	background: #0f172a;
	padding: 10px;
	border-radius: 4px;
	font-family: monospace;
	font-size: 12px;
	max-height: 300px;
	overflow-y: auto;
	color: #f8fafc;
	border: 1px solid #334155;
}
</style>
