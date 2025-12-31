<?php
/**
 * Config Sync Tab - Multi-Site Configuration Management
 *
 * Export/import compliance configuration across sites.
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

<div class="slos-config-sync-page">
	
	<!-- Header Section -->
	<div class="slos-section-header">
		<div class="slos-header-content">
			<div class="slos-header-icon">
				<span class="dashicons dashicons-cloud"></span>
			</div>
			<div>
				<h2><?php esc_html_e( 'Configuration Sync', 'shahi-legalflowsuite' ); ?></h2>
				<p class="slos-subtitle"><?php esc_html_e( 'Export and import compliance settings across sites', 'shahi-legalflowsuite' ); ?></p>
			</div>
		</div>
		<?php if ( is_multisite() ) : ?>
			<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=slos-network-compliance' ) ); ?>" class="slos-btn slos-btn-secondary">
				<span class="dashicons dashicons-admin-multisite"></span>
				<?php esc_html_e( 'Network Overview', 'shahi-legalflowsuite' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<!-- Info Banner -->
	<div class="slos-info-banner">
		<span class="dashicons dashicons-info"></span>
		<div>
			<strong><?php esc_html_e( 'About Configuration Sync', 'shahi-legalflowsuite' ); ?></strong>
			<p><?php esc_html_e( 'Export your compliance settings (banner configuration, geo rules, legal pages, etc.) to share across multiple sites. Configuration profiles never include personal data like consent records or DSR requests.', 'shahi-legalflowsuite' ); ?></p>
		</div>
	</div>

	<!-- Two Column Layout -->
	<div class="slos-two-column">
		
		<!-- Left Column: Export -->
		<div class="slos-column">
			<div class="slos-card">
				<div class="slos-card-header">
					<h3>
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Export Configuration', 'shahi-legalflowsuite' ); ?>
					</h3>
				</div>
				<div class="slos-card-body">
					<form id="slos-export-form">
						
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
								class="slos-input" 
								placeholder="<?php esc_attr_e( 'e.g., Production Config 2025-01', 'shahi-legalflowsuite' ); ?>"
								required
							>
						</div>

						<!-- Profile Description -->
						<div class="slos-form-group">
							<label for="slos-export-description">
								<?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?>
							</label>
							<textarea 
								id="slos-export-description" 
								name="description" 
								class="slos-textarea" 
								rows="3"
								placeholder="<?php esc_attr_e( 'Optional description of this configuration', 'shahi-legalflowsuite' ); ?>"
							></textarea>
						</div>

						<!-- Settings to Export -->
						<div class="slos-form-group">
							<label><?php esc_html_e( 'Settings to Export', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-checkbox-group">
								<label class="slos-checkbox">
									<input type="checkbox" name="options[]" value="slos_banner_settings" checked>
									<span><?php esc_html_e( 'Banner Settings', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Consent banner configuration', 'shahi-legalflowsuite' ); ?></small>
								</label>
								<label class="slos-checkbox">
									<input type="checkbox" name="options[]" value="slos_geo_rules" checked>
									<span><?php esc_html_e( 'Geo Rules', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Geographic targeting rules', 'shahi-legalflowsuite' ); ?></small>
								</label>
								<label class="slos-checkbox">
									<input type="checkbox" name="options[]" value="slos_legal_pages" checked>
									<span><?php esc_html_e( 'Legal Pages', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Legal document assignments', 'shahi-legalflowsuite' ); ?></small>
								</label>
								<label class="slos-checkbox">
									<input type="checkbox" name="options[]" value="slos_accessibility_settings" checked>
									<span><?php esc_html_e( 'Accessibility Settings', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Accessibility scanner configuration', 'shahi-legalflowsuite' ); ?></small>
								</label>
								<label class="slos-checkbox">
									<input type="checkbox" name="options[]" value="slos_dsr_settings" checked>
									<span><?php esc_html_e( 'DSR Settings', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Data Subject Request settings', 'shahi-legalflowsuite' ); ?></small>
								</label>
								<label class="slos-checkbox">
									<input type="checkbox" name="options[]" value="slos_cookie_inventory">
									<span><?php esc_html_e( 'Cookie Inventory', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Detected cookies (optional)', 'shahi-legalflowsuite' ); ?></small>
								</label>
							</div>
						</div>

						<!-- Select All / None -->
						<div class="slos-checkbox-actions">
							<button type="button" id="slos-select-all" class="slos-link-btn">
								<?php esc_html_e( 'Select All', 'shahi-legalflowsuite' ); ?>
							</button>
							<span>|</span>
							<button type="button" id="slos-select-none" class="slos-link-btn">
								<?php esc_html_e( 'Select None', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>

						<!-- Export Buttons -->
						<div class="slos-button-group">
							<button type="submit" name="action" value="export_file" class="slos-btn slos-btn-primary" id="slos-export-file-btn">
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Export to File', 'shahi-legalflowsuite' ); ?>
							</button>
							<button type="submit" name="action" value="export_json" class="slos-btn slos-btn-secondary" id="slos-export-json-btn">
								<span class="dashicons dashicons-editor-code"></span>
								<?php esc_html_e( 'Copy JSON', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>

						<!-- Export Status -->
						<div id="slos-export-status" class="slos-status-message" style="display:none;"></div>
					</form>
				</div>
			</div>
		</div>

		<!-- Right Column: Import -->
		<div class="slos-column">
			<div class="slos-card">
				<div class="slos-card-header">
					<h3>
						<span class="dashicons dashicons-download"></span>
						<?php esc_html_e( 'Import Configuration', 'shahi-legalflowsuite' ); ?>
					</h3>
				</div>
				<div class="slos-card-body">
					<form id="slos-import-form">
						
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
								class="slos-textarea slos-code-editor" 
								rows="8"
								placeholder='{"schema_version":"1.0.0","profile":{...},"settings":{...}}'
							></textarea>
						</div>

						<!-- Import Options -->
						<div class="slos-form-group">
							<label><?php esc_html_e( 'Import Options', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-checkbox-group">
								<label class="slos-checkbox">
									<input type="checkbox" name="merge" id="slos-merge-option">
									<span><?php esc_html_e( 'Merge with existing settings', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Combine imported settings with current values (arrays only)', 'shahi-legalflowsuite' ); ?></small>
								</label>
								<label class="slos-checkbox">
									<input type="checkbox" name="dry_run" id="slos-dry-run-option">
									<span><?php esc_html_e( 'Dry run (preview only)', 'shahi-legalflowsuite' ); ?></span>
									<small><?php esc_html_e( 'Show what would be imported without making changes', 'shahi-legalflowsuite' ); ?></small>
								</label>
							</div>
						</div>

						<!-- Import Buttons -->
						<div class="slos-button-group">
							<button type="button" id="slos-validate-btn" class="slos-btn slos-btn-secondary">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Validate', 'shahi-legalflowsuite' ); ?>
							</button>
							<button type="button" id="slos-compare-btn" class="slos-btn slos-btn-secondary">
								<span class="dashicons dashicons-code-standards"></span>
								<?php esc_html_e( 'Compare', 'shahi-legalflowsuite' ); ?>
							</button>
							<button type="submit" id="slos-import-btn" class="slos-btn slos-btn-primary" disabled>
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Import', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>

						<!-- Import Status -->
						<div id="slos-import-status" class="slos-status-message" style="display:none;"></div>
					</form>

					<!-- Validation Results -->
					<div id="slos-validation-results" class="slos-results-box" style="display:none;">
						<h4><?php esc_html_e( 'Validation Results', 'shahi-legalflowsuite' ); ?></h4>
						<div class="slos-results-content"></div>
					</div>

					<!-- Comparison Results -->
					<div id="slos-comparison-results" class="slos-results-box" style="display:none;">
						<h4><?php esc_html_e( 'Configuration Comparison', 'shahi-legalflowsuite' ); ?></h4>
						<div class="slos-results-content"></div>
					</div>

					<!-- Import Results -->
					<div id="slos-import-results" class="slos-results-box" style="display:none;">
						<h4><?php esc_html_e( 'Import Results', 'shahi-legalflowsuite' ); ?></h4>
						<div class="slos-results-content"></div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<!-- Available Exports Section -->
	<div class="slos-card slos-exports-card">
		<div class="slos-card-header">
			<h3>
				<span class="dashicons dashicons-archive"></span>
				<?php esc_html_e( 'Available Exports', 'shahi-legalflowsuite' ); ?>
			</h3>
			<button type="button" id="slos-refresh-exports" class="slos-btn slos-btn-ghost">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Refresh', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
		<div class="slos-card-body">
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
										<span class="slos-date"><?php echo esc_html( $export['exported_at'] ?? '—' ); ?></span>
										<br>
										<small><?php echo esc_html( sprintf( __( 'by %s', 'shahi-legalflowsuite' ), $export['exported_by'] ?? 'Unknown' ) ); ?></small>
									</td>
									<td>
										<small><?php echo esc_html( $export['site_url'] ?? '—' ); ?></small>
									</td>
									<td>
										<span class="slos-filesize"><?php echo esc_html( size_format( $export['size'] ?? 0 ) ); ?></span>
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
