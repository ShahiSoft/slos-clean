<?php
/**
 * Banner Config Tab - V3 Design
 *
 * Cookie consent banner configuration with live preview.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current banner settings
$banner_settings = array(
	'position'        => 'bottom',
	'layout'          => 'bar',
	'theme'           => 'dark',
	'primary_color'   => '#3b82f6',
	'text_color'      => '#f8fafc',
	'bg_color'        => '#0f172a',
	'show_toggle'     => true,
	'show_categories' => true,
	'animation'       => 'slide',
	'title'           => __( 'We value your privacy', 'shahi-legalflowsuite' ),
	'message'         => __( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.', 'shahi-legalflowsuite' ),
	'accept_text'     => __( 'Accept All', 'shahi-legalflowsuite' ),
	'reject_text'     => __( 'Reject All', 'shahi-legalflowsuite' ),
	'settings_text'   => __( 'Cookie Settings', 'shahi-legalflowsuite' ),
);
?>

<style>
/* Banner Config specific styles */
.slos-config-layout {
	display: grid;
	grid-template-columns: 400px 1fr;
	gap: 24px;
}

.slos-config-panel {
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	padding: 24px;
	max-height: calc(100vh - 300px);
	overflow-y: auto;
}

.slos-config-section {
	margin-bottom: 24px;
	padding-bottom: 24px;
	border-bottom: 1px solid var(--slos-border);
}

.slos-config-section:last-child {
	margin-bottom: 0;
	padding-bottom: 0;
	border-bottom: none;
}

.slos-config-title {
	font-size: 14px;
	font-weight: 600;
	color: var(--slos-text-primary);
	margin-bottom: 16px;
	display: flex;
	align-items: center;
	gap: 8px;
}

.slos-config-title .dashicons {
	color: var(--slos-accent);
}

/* Form Controls */
.slos-form-group {
	margin-bottom: 16px;
}

.slos-form-group:last-child {
	margin-bottom: 0;
}

.slos-form-label {
	display: block;
	font-size: 12px;
	color: var(--slos-text-secondary);
	margin-bottom: 6px;
}

.slos-form-input {
	width: 100%;
	padding: 10px 12px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 8px;
	color: var(--slos-text-primary);
	font-size: 13px;
}

.slos-form-input:focus {
	outline: none;
	border-color: var(--slos-accent);
}

.slos-form-textarea {
	width: 100%;
	min-height: 100px;
	padding: 12px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 8px;
	color: var(--slos-text-primary);
	font-size: 13px;
	resize: vertical;
}

.slos-form-textarea:focus {
	outline: none;
	border-color: var(--slos-accent);
}

/* Radio Group */
.slos-radio-group {
	display: flex;
	gap: 12px;
	flex-wrap: wrap;
}

.slos-radio-card {
	flex: 1;
	min-width: 100px;
	padding: 12px;
	background: var(--slos-bg-input);
	border: 2px solid var(--slos-border);
	border-radius: 8px;
	cursor: pointer;
	text-align: center;
	transition: all 0.15s;
}

.slos-radio-card:hover {
	border-color: rgba(59, 130, 246, 0.5);
}

.slos-radio-card.active {
	border-color: var(--slos-accent);
	background: rgba(59, 130, 246, 0.05);
}

.slos-radio-card input {
	display: none;
}

.slos-radio-icon {
	width: 40px;
	height: 40px;
	margin: 0 auto 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(59, 130, 246, 0.1);
	border-radius: 8px;
	color: var(--slos-accent);
}

.slos-radio-label {
	font-size: 12px;
	color: var(--slos-text-primary);
	font-weight: 500;
}

/* Phase 2.1.1: Template Card Grid */
.slos-template-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 12px;
}

.slos-template-card {
	position: relative;
	padding: 16px;
	border: 2px solid var(--slos-border);
	border-radius: 8px;
	cursor: pointer;
	transition: all 0.15s;
	background: var(--slos-bg-card);
}

.slos-template-card:hover {
	border-color: rgba(59, 130, 246, 0.5);
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.slos-template-card.active {
	border-color: var(--slos-accent);
	background: rgba(59, 130, 246, 0.05);
}

.slos-template-card input {
	display: none;
}

.slos-template-header {
	display: flex;
	align-items: center;
	gap: 10px;
	margin-bottom: 10px;
}

.slos-template-icon {
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(59, 130, 246, 0.1);
	border-radius: 6px;
	color: var(--slos-accent);
	flex-shrink: 0;
}

.slos-template-name {
	font-size: 13px;
	font-weight: 600;
	color: var(--slos-text-primary);
	line-height: 1.3;
}

.slos-template-description {
	font-size: 11px;
	color: var(--slos-text-secondary);
	line-height: 1.5;
	margin: 0;
}

.slos-template-badge {
	display: inline-block;
	padding: 2px 6px;
	background: rgba(59, 130, 246, 0.15);
	color: var(--slos-accent);
	border-radius: 4px;
	font-size: 10px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-top: 8px;
}

/* Color Picker */
.slos-color-picker {
	display: flex;
	gap: 12px;
	align-items: center;
}

.slos-color-input {
	width: 48px;
	height: 48px;
	border: 2px solid var(--slos-border);
	border-radius: 8px;
	cursor: pointer;
	padding: 0;
}

.slos-color-hex {
	flex: 1;
	padding: 10px 12px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 8px;
	color: var(--slos-text-primary);
	font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
	font-size: 13px;
}

/* Toggle Switch */
.slos-toggle-row {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 0;
	border-bottom: 1px solid var(--slos-border);
}

.slos-toggle-row:last-child {
	border-bottom: none;
}

.slos-toggle-label {
	font-size: 13px;
	color: var(--slos-text-primary);
}

.slos-toggle-desc {
	font-size: 12px;
	color: var(--slos-text-muted);
	margin-top: 2px;
}

.slos-toggle {
	position: relative;
	width: 44px;
	height: 24px;
	background: var(--slos-bg-input);
	border: 2px solid var(--slos-border);
	border-radius: 12px;
	cursor: pointer;
	transition: all 0.2s;
}

.slos-toggle.active {
	background: var(--slos-accent);
	border-color: var(--slos-accent);
}

.slos-toggle-handle {
	position: absolute;
	top: 2px;
	left: 2px;
	width: 16px;
	height: 16px;
	background: white;
	border-radius: 50%;
	transition: transform 0.2s;
}

.slos-toggle.active .slos-toggle-handle {
	transform: translateX(20px);
}

/* Preview Panel */
.slos-preview-panel {
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 12px;
	overflow: hidden;
}

.slos-preview-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px 20px;
	border-bottom: 1px solid var(--slos-border);
}

.slos-preview-title {
	font-size: 14px;
	font-weight: 600;
	color: var(--slos-text-primary);
}

.slos-preview-actions {
	display: flex;
	gap: 8px;
}

.slos-preview-btn {
	padding: 6px 12px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	font-size: 12px;
	color: var(--slos-text-secondary);
	cursor: pointer;
	transition: all 0.15s;
}

.slos-preview-btn:hover {
	border-color: var(--slos-accent);
	color: var(--slos-accent);
}

.slos-preview-btn.active {
	background: var(--slos-accent);
	border-color: var(--slos-accent);
	color: white;
}

/* Phase 2.4: Geo Preview Selector */
.slos-preview-controls {
	display: flex;
	align-items: center;
	gap: 16px;
}

.slos-geo-selector {
	display: flex;
	align-items: center;
	gap: 8px;
}

.slos-geo-selector-label {
	font-size: 12px;
	font-weight: 500;
	color: var(--slos-text-secondary);
	white-space: nowrap;
}

.slos-geo-selector select {
	padding: 6px 28px 6px 10px;
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	font-size: 12px;
	color: var(--slos-text-primary);
	cursor: pointer;
	transition: all 0.15s;
	appearance: none;
	background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"%3e%3cpath fill="%23666" d="M6 8L2 4h8z"/%3e%3c/svg%3e');
	background-repeat: no-repeat;
	background-position: right 8px center;
	min-width: 120px;
}

.slos-geo-selector select:hover {
	border-color: var(--slos-accent);
}

.slos-geo-selector select:focus {
	outline: none;
	border-color: var(--slos-accent);
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.slos-geo-help {
	margin-top: 8px;
	padding: 12px;
	background: rgba(59, 130, 246, 0.05);
	border-left: 3px solid var(--slos-accent);
	border-radius: 4px;
}

.slos-geo-help-text {
	font-size: 12px;
	line-height: 1.5;
	color: var(--slos-text-secondary);
	margin: 0;
}

.slos-geo-help-text strong {
	color: var(--slos-text-primary);
	font-weight: 600;
}

.slos-preview-window {
	background: #f5f5f5;
	min-height: 400px;
	position: relative;
	overflow: hidden;
}

/* Preview content mockup */
.slos-preview-content {
	padding: 40px;
	text-align: center;
	color: #666;
}

.slos-preview-lines {
	max-width: 600px;
	margin: 0 auto;
}

.slos-preview-line {
	height: 12px;
	background: #ddd;
	border-radius: 6px;
	margin-bottom: 12px;
}

.slos-preview-line.short {
	width: 60%;
}

.slos-preview-line.medium {
	width: 80%;
}

/* Banner Preview - Modern Compact Style */
.slos-banner-preview {
	position: absolute;
	left: 16px;
	right: auto;
	max-width: 360px;
	padding: 16px 20px;
	border-radius: 12px;
	display: flex;
	flex-direction: column;
	gap: 12px;
	animation: slideInUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
	border: 1px solid rgba(0, 0, 0, 0.1);
}

.slos-banner-preview.bottom {
	bottom: 16px;
}

.slos-banner-preview.top {
	top: 16px;
	bottom: auto;
	animation: slideInDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideInUp {
	from { transform: translateY(20px); opacity: 0; }
	to { transform: translateY(0); opacity: 1; }
}

@keyframes slideInDown {
	from { transform: translateY(-20px); opacity: 0; }
	to { transform: translateY(0); opacity: 1; }
}

.slos-banner-text {
	flex: 1;
}

.slos-banner-title {
	font-weight: 600;
	margin-bottom: 6px;
	font-size: 14px;
}

.slos-banner-message {
	font-size: 12px;
	opacity: 0.85;
	line-height: 1.5;
}

.slos-banner-buttons {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
}

.slos-banner-btn {
	padding: 8px 14px;
	border-radius: 8px;
	font-size: 12px;
	font-weight: 500;
	cursor: pointer;
	border: none;
	transition: all 0.15s ease;
	flex: 1;
	min-width: 70px;
	text-align: center;
}

.slos-banner-btn.primary {
	background: #10b981;
	color: white;
}

.slos-banner-btn.primary:hover {
	background: #059669;
}

.slos-banner-btn.secondary {
	background: transparent;
	border: 1px solid rgba(128, 128, 128, 0.3);
	color: inherit;
	opacity: 0.8;
}

.slos-banner-btn.secondary:hover {
	border-color: #ef4444;
	color: #ef4444;
	opacity: 1;
}

.slos-banner-btn.link {
	background: transparent;
	color: #3b82f6;
	text-decoration: none;
	font-size: 11px;
	flex: 0;
	min-width: auto;
	padding: 4px 8px;
}

.slos-banner-btn.link:hover {
	text-decoration: underline;
}

/* Phase 2.2: Category Details Section */
.slos-category-details {
	margin-top: 16px;
}

.slos-category-item {
	background: var(--slos-bg-input);
	border: 1px solid var(--slos-border);
	border-radius: 8px;
	margin-bottom: 12px;
	overflow: hidden;
}

.slos-category-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px 16px;
	cursor: pointer;
	transition: background 0.15s;
}

.slos-category-header:hover {
	background: rgba(0, 0, 0, 0.03);
}

.slos-category-header-content {
	display: flex;
	align-items: center;
	gap: 10px;
	flex: 1;
}

.slos-category-icon {
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(59, 130, 246, 0.1);
	border-radius: 6px;
	color: var(--slos-accent);
	font-size: 14px;
}

.slos-category-name {
	font-size: 13px;
	font-weight: 600;
	color: var(--slos-text-primary);
}

.slos-category-expand-icon {
	color: var(--slos-text-secondary);
	transition: transform 0.2s;
}

.slos-category-item.expanded .slos-category-expand-icon {
	transform: rotate(180deg);
}

.slos-category-body {
	display: none;
	padding: 16px;
	border-top: 1px solid var(--slos-border);
}

.slos-category-item.expanded .slos-category-body {
	display: block;
}

.slos-category-description-label {
	display: block;
	font-size: 12px;
	color: var(--slos-text-secondary);
	margin-bottom: 8px;
	font-weight: 500;
}

.slos-category-description-input {
	width: 100%;
	min-height: 60px;
	padding: 10px 12px;
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	color: var(--slos-text-primary);
	font-size: 12px;
	line-height: 1.5;
	resize: vertical;
	font-family: inherit;
}

.slos-category-description-input:focus {
	outline: 2px solid rgba(59, 130, 246, 0.2);
	border-color: var(--slos-accent);
}

/* Vendor Repeater */
.slos-vendor-section {
	margin-top: 16px;
}

.slos-vendor-list {
	margin-top: 12px;
}

.slos-vendor-item {
	display: flex;
	gap: 8px;
	margin-bottom: 8px;
	align-items: flex-start;
}

.slos-vendor-input {
	flex: 1;
	padding: 8px 10px;
	background: var(--slos-bg-card);
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	color: var(--slos-text-primary);
	font-size: 12px;
}

.slos-vendor-input:focus {
	outline: 2px solid rgba(59, 130, 246, 0.2);
	border-color: var(--slos-accent);
}

.slos-vendor-input.vendor-name {
	flex: 0 0 200px;
}

.slos-vendor-input.vendor-purpose {
	flex: 1;
}

.slos-vendor-remove {
	padding: 8px 10px;
	background: transparent;
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	color: #ef4444;
	cursor: pointer;
	transition: all 0.15s;
	font-size: 14px;
	line-height: 1;
}

.slos-vendor-remove:hover {
	background: rgba(239, 68, 68, 0.1);
	border-color: #ef4444;
}

.slos-vendor-add {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 8px 12px;
	background: transparent;
	border: 1px dashed var(--slos-border);
	border-radius: 6px;
	color: var(--slos-accent);
	cursor: pointer;
	transition: all 0.15s;
	font-size: 12px;
	margin-top: 8px;
}

.slos-vendor-add:hover {
	background: rgba(59, 130, 246, 0.05);
	border-color: var(--slos-accent);
}

.slos-vendor-add .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
}

/* Phase 2.3: Privacy & Consent Expiry Settings */
.slos-privacy-settings .slos-form-group {
	margin-bottom: 20px;
}

.slos-privacy-settings .slos-form-label {
	display: block;
	margin-bottom: 8px;
	font-weight: 500;
	font-size: 13px;
	color: var(--slos-text);
}

.slos-privacy-settings .slos-form-help {
	display: block;
	margin-top: 6px;
	font-size: 12px;
	color: var(--slos-muted);
	line-height: 1.5;
}

.slos-privacy-settings input[type="url"],
.slos-privacy-settings input[type="text"],
.slos-privacy-settings input[type="number"] {
	width: 100%;
	padding: 10px 12px;
	border: 1px solid var(--slos-border);
	border-radius: 6px;
	font-size: 13px;
	transition: all 0.15s;
	background: var(--shahi-bg-card);
	color: var(--slos-text);
}

.slos-privacy-settings input[type="url"]:focus,
.slos-privacy-settings input[type="text"]:focus,
.slos-privacy-settings input[type="number"]:focus {
	outline: none;
	border-color: var(--slos-accent);
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.slos-privacy-settings input[type="number"] {
	width: 150px;
}

.slos-privacy-settings .slos-input-suffix {
	display: inline-block;
	margin-left: 8px;
	font-size: 13px;
	color: var(--slos-muted);
}

/* Save Actions */
.slos-save-bar {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 24px;
	padding-top: 24px;
	border-top: 1px solid var(--slos-border);
}

/* Toast Notifications (Phase 0.5.3) */
.slos-toast {
	position: fixed;
	bottom: 24px;
	right: 24px;
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px 20px;
	background: var(--slos-text-primary);
	color: #ffffff;
	border-radius: 8px;
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
	font-size: 14px;
	z-index: 100200;
	animation: slos-toast-in 0.3s ease;
}

.slos-toast--success {
	background: #10b981;
}

.slos-toast--error {
	background: #ef4444;
}

.slos-toast--info {
	background: #3b82f6;
}

@keyframes slos-toast-in {
	from {
		opacity: 0;
		transform: translateY(20px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* Spinning animation for loading icon (Phase 0.5.2) */
@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

/* Validation Error States (Phase 0.6) */
.slos-form-input.error,
.slos-color-hex.error {
	border-color: #ef4444;
	background: rgba(239, 68, 68, 0.05);
}

.slos-form-input.error:focus,
.slos-color-hex.error:focus {
	border-color: #ef4444;
	outline: 2px solid rgba(239, 68, 68, 0.2);
}

.slos-error-message {
	display: none;
	margin-top: 6px;
	font-size: 12px;
	color: #ef4444;
	line-height: 1.4;
}

.slos-error-message.visible {
	display: block;
}
</style>

<div class="slos-config-layout">
	<!-- Section Description -->
	<p class="slos-section-description" style="grid-column: 1 / -1;">
		<?php esc_html_e( 'Design your cookie consent banner with live preview. Customize colors, text, and layout to match your brand. GDPR requires clear Accept/Reject options and links to privacy policy. The banner appears to visitors on first visit and can be reopened via the preferences link.', 'shahi-legalflowsuite' ); ?>
	</p>
	
	<!-- Configuration Panel -->
	<div class="slos-config-panel">
		<?php
		// Check if Banner Templates feature is dormant
		$show_banner_templates = ! ( defined( 'SLOS_DORMANT_BANNER_FEATURES' ) &&
			is_array( SLOS_DORMANT_BANNER_FEATURES ) &&
			in_array( 'templates', SLOS_DORMANT_BANNER_FEATURES, true ) );

		if ( $show_banner_templates ) :
			?>
		<!-- Phase 2.1.1: Template Selector -->
		<div class="slos-config-section">
			<div class="slos-config-title">
				<span class="dashicons dashicons-admin-page"></span>
				<?php esc_html_e( 'Banner Template', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Choose a template optimized for different regulatory frameworks. EU/GDPR requires granular consent, CCPA focuses on opt-out, Simple is for basic notice, and Advanced offers full control.', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-form-group">
				<div class="slos-template-grid">
					<!-- EU/GDPR Template -->
					<label class="slos-template-card <?php echo ( ! isset( $banner_settings['template'] ) || $banner_settings['template'] === 'eu' || $banner_settings['template'] === 'gdpr' ) ? 'active' : ''; ?>">
						<input type="radio" name="banner_template" value="eu" <?php checked( $banner_settings['template'] ?? 'eu', 'eu' ); ?>>
						<div class="slos-template-header">
							<div class="slos-template-icon">
								<span class="dashicons dashicons-shield-alt"></span>
							</div>
							<div class="slos-template-name"><?php esc_html_e( 'EU/GDPR', 'shahi-legalflowsuite' ); ?></div>
						</div>
						<p class="slos-template-description">
							<?php esc_html_e( 'Granular opt-in consent with category toggles. Meets GDPR Article 7 requirements with explicit, freely given consent for each purpose.', 'shahi-legalflowsuite' ); ?>
						</p>
						<span class="slos-template-badge"><?php esc_html_e( 'Recommended', 'shahi-legalflowsuite' ); ?></span>
					</label>

					<!-- CCPA Template -->
					<label class="slos-template-card <?php echo ( isset( $banner_settings['template'] ) && $banner_settings['template'] === 'ccpa' ) ? 'active' : ''; ?>">
						<input type="radio" name="banner_template" value="ccpa" <?php checked( $banner_settings['template'] ?? 'eu', 'ccpa' ); ?>>
						<div class="slos-template-header">
							<div class="slos-template-icon">
								<span class="dashicons dashicons-privacy"></span>
							</div>
							<div class="slos-template-name"><?php esc_html_e( 'CCPA', 'shahi-legalflowsuite' ); ?></div>
						</div>
						<p class="slos-template-description">
							<?php esc_html_e( 'California-focused opt-out model with "Do Not Sell My Personal Information" option. Complies with CCPA/CPRA notice requirements.', 'shahi-legalflowsuite' ); ?>
						</p>
					</label>

					<!-- Simple Template -->
					<label class="slos-template-card <?php echo ( isset( $banner_settings['template'] ) && $banner_settings['template'] === 'simple' ) ? 'active' : ''; ?>">
						<input type="radio" name="banner_template" value="simple" <?php checked( $banner_settings['template'] ?? 'eu', 'simple' ); ?>>
						<div class="slos-template-header">
							<div class="slos-template-icon">
								<span class="dashicons dashicons-yes-alt"></span>
							</div>
							<div class="slos-template-name"><?php esc_html_e( 'Simple Notice', 'shahi-legalflowsuite' ); ?></div>
						</div>
						<p class="slos-template-description">
							<?php esc_html_e( 'Basic cookie notice with accept button. Suitable for informational websites with minimal tracking. Not GDPR compliant for EU visitors.', 'shahi-legalflowsuite' ); ?>
						</p>
					</label>

					<!-- Advanced Template -->
					<label class="slos-template-card <?php echo ( isset( $banner_settings['template'] ) && $banner_settings['template'] === 'advanced' ) ? 'active' : ''; ?>">
						<input type="radio" name="banner_template" value="advanced" <?php checked( $banner_settings['template'] ?? 'eu', 'advanced' ); ?>>
						<div class="slos-template-header">
							<div class="slos-template-icon">
								<span class="dashicons dashicons-admin-settings"></span>
							</div>
							<div class="slos-template-name"><?php esc_html_e( 'Advanced', 'shahi-legalflowsuite' ); ?></div>
						</div>
						<p class="slos-template-description">
							<?php esc_html_e( 'Full-featured template with category descriptions, vendor lists, and consent receipts. Maximum transparency and control for privacy-focused sites.', 'shahi-legalflowsuite' ); ?>
						</p>
					</label>
				</div>
			</div>
		</div>
		<?php endif; // End Banner Templates dormant check ?>

		<!-- Position & Layout -->
		<div class="slos-config-section">
			<div class="slos-config-title">
				<span class="dashicons dashicons-layout"></span>
				<?php esc_html_e( 'Position & Layout', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Choose where and how the banner appears. Bottom-left box is recommended for minimal intrusion while maintaining visibility.', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Banner Position', 'shahi-legalflowsuite' ); ?></label>
				<div class="slos-radio-group">
					<label class="slos-radio-card <?php echo $banner_settings['position'] === 'bottom' ? 'active' : ''; ?>">
						<input type="radio" name="banner_position" value="bottom" <?php checked( $banner_settings['position'], 'bottom' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-arrow-down-alt"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Bottom', 'shahi-legalflowsuite' ); ?></div>
					</label>
					<label class="slos-radio-card <?php echo $banner_settings['position'] === 'top' ? 'active' : ''; ?>">
						<input type="radio" name="banner_position" value="top" <?php checked( $banner_settings['position'], 'top' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-arrow-up-alt"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Top', 'shahi-legalflowsuite' ); ?></div>
					</label>
					<label class="slos-radio-card <?php echo $banner_settings['position'] === 'center' ? 'active' : ''; ?>">
						<input type="radio" name="banner_position" value="center" <?php checked( $banner_settings['position'], 'center' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-align-center"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Center', 'shahi-legalflowsuite' ); ?></div>
					</label>
				</div>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Layout Style', 'shahi-legalflowsuite' ); ?></label>
				<div class="slos-radio-group">
					<label class="slos-radio-card <?php echo $banner_settings['layout'] === 'bar' ? 'active' : ''; ?>">
						<input type="radio" name="banner_layout" value="bar" <?php checked( $banner_settings['layout'], 'bar' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-minus"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Bar', 'shahi-legalflowsuite' ); ?></div>
					</label>
					<label class="slos-radio-card <?php echo $banner_settings['layout'] === 'box' ? 'active' : ''; ?>">
						<input type="radio" name="banner_layout" value="box" <?php checked( $banner_settings['layout'], 'box' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-admin-comments"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Box', 'shahi-legalflowsuite' ); ?></div>
					</label>
					<label class="slos-radio-card <?php echo $banner_settings['layout'] === 'popup' ? 'active' : ''; ?>">
						<input type="radio" name="banner_layout" value="popup" <?php checked( $banner_settings['layout'], 'popup' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-editor-expand"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Popup', 'shahi-legalflowsuite' ); ?></div>
					</label>
				</div>
			</div>
		</div>

		<!-- Colors & Theme -->
		<div class="slos-config-section">
			<div class="slos-config-title">
				<span class="dashicons dashicons-art"></span>
				<?php esc_html_e( 'Colors & Theme', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Match your brand colors. Ensure text color has sufficient contrast against background for accessibility (WCAG AA minimum 4.5:1 ratio).', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Primary Color', 'shahi-legalflowsuite' ); ?></label>
				<div class="slos-color-picker">
					<input type="color" class="slos-color-input" id="primary-color" value="<?php echo esc_attr( $banner_settings['primary_color'] ); ?>">
					<input type="text" class="slos-color-hex" id="primary-color-hex" value="<?php echo esc_attr( $banner_settings['primary_color'] ); ?>" data-field="primary-color">
				</div>
				<div class="slos-error-message" id="primary-color-error"></div>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Background Color', 'shahi-legalflowsuite' ); ?></label>
				<div class="slos-color-picker">
					<input type="color" class="slos-color-input" id="bg-color" value="<?php echo esc_attr( $banner_settings['bg_color'] ); ?>">
					<input type="text" class="slos-color-hex" id="bg-color-hex" value="<?php echo esc_attr( $banner_settings['bg_color'] ); ?>" data-field="bg-color">
				</div>
				<div class="slos-error-message" id="bg-color-error"></div>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Text Color', 'shahi-legalflowsuite' ); ?></label>
				<div class="slos-color-picker">
					<input type="color" class="slos-color-input" id="text-color" value="<?php echo esc_attr( $banner_settings['text_color'] ); ?>">
					<input type="text" class="slos-color-hex" id="text-color-hex" value="<?php echo esc_attr( $banner_settings['text_color'] ); ?>" data-field="text-color">
				</div>
				<div class="slos-error-message" id="text-color-error"></div>
			</div>
		</div>

		<!-- Banner Text -->
		<div class="slos-config-section">
			<div class="slos-config-title">
				<span class="dashicons dashicons-edit"></span>
				<?php esc_html_e( 'Banner Text', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Write clear, non-technical language. Title should convey purpose. Message explains what cookies do. Button text must be actionable (Accept All, Reject All, Customize).', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Title', 'shahi-legalflowsuite' ); ?></label>
				<input type="text" class="slos-form-input" id="banner-title" value="<?php echo esc_attr( $banner_settings['title'] ); ?>">
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Message', 'shahi-legalflowsuite' ); ?></label>
				<textarea class="slos-form-textarea" id="banner-message"><?php echo esc_textarea( $banner_settings['message'] ); ?></textarea>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Accept Button Text', 'shahi-legalflowsuite' ); ?></label>
				<input type="text" class="slos-form-input" id="accept-text" value="<?php echo esc_attr( $banner_settings['accept_text'] ); ?>" required>
				<div class="slos-error-message" id="accept-text-error"></div>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Reject Button Text', 'shahi-legalflowsuite' ); ?></label>
				<input type="text" class="slos-form-input" id="reject-text" value="<?php echo esc_attr( $banner_settings['reject_text'] ); ?>" required>
				<div class="slos-error-message" id="reject-text-error"></div>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Settings Link Text', 'shahi-legalflowsuite' ); ?></label>
				<input type="text" class="slos-form-input" id="settings-text" value="<?php echo esc_attr( $banner_settings['settings_text'] ); ?>">
			</div>
		</div>

		<!-- Behavior Options -->
		<div class="slos-config-section">
			<div class="slos-config-title">
				<span class="dashicons dashicons-admin-generic"></span>
				<?php esc_html_e( 'Behavior', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Configure user interaction options. Show Cookie Toggle adds a persistent icon for users to change preferences. Show Categories enables granular control per cookie type.', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-toggle-row">
				<div>
					<div class="slos-toggle-label"><?php esc_html_e( 'Show Cookie Toggle', 'shahi-legalflowsuite' ); ?></div>
					<div class="slos-toggle-desc"><?php esc_html_e( 'Allow users to reopen settings', 'shahi-legalflowsuite' ); ?></div>
				</div>
				<div class="slos-toggle <?php echo $banner_settings['show_toggle'] ? 'active' : ''; ?>" data-setting="show_toggle">
					<div class="slos-toggle-handle"></div>
				</div>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label"><?php esc_html_e( 'Cookie Icon Position', 'shahi-legalflowsuite' ); ?></label>
				<div class="slos-radio-group">
					<label class="slos-radio-card <?php echo ( ! isset( $banner_settings['icon_position'] ) || $banner_settings['icon_position'] === 'left' ) ? 'active' : ''; ?>">
						<input type="radio" name="icon_position" value="left" <?php checked( $banner_settings['icon_position'] ?? 'left', 'left' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-arrow-left-alt"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Left', 'shahi-legalflowsuite' ); ?></div>
					</label>
					<label class="slos-radio-card <?php echo ( isset( $banner_settings['icon_position'] ) && $banner_settings['icon_position'] === 'right' ) ? 'active' : ''; ?>">
						<input type="radio" name="icon_position" value="right" <?php checked( $banner_settings['icon_position'] ?? 'left', 'right' ); ?>>
						<div class="slos-radio-icon">
							<span class="dashicons dashicons-arrow-right-alt"></span>
						</div>
						<div class="slos-radio-label"><?php esc_html_e( 'Right', 'shahi-legalflowsuite' ); ?></div>
					</label>
				</div>
			</div>

			<div class="slos-toggle-row">
				<div>
					<div class="slos-toggle-label"><?php esc_html_e( 'Show Categories', 'shahi-legalflowsuite' ); ?></div>
					<div class="slos-toggle-desc"><?php esc_html_e( 'Let users select individual categories', 'shahi-legalflowsuite' ); ?></div>
				</div>
				<div class="slos-toggle <?php echo $banner_settings['show_categories'] ? 'active' : ''; ?>" data-setting="show_categories">
					<div class="slos-toggle-handle"></div>
				</div>
			</div>
		</div>

		<!-- Phase 2.2.1, 2.2.2, 2.2.3: Category Details Section -->
		<div class="slos-config-section">
			<div class="slos-config-title">
				<span class="dashicons dashicons-category"></span>
				<?php esc_html_e( 'Category Details', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Customize descriptions and vendor lists for each cookie category. These appear when users expand category details in the consent banner. Clear descriptions help users make informed decisions.', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-category-details">
				<!-- Necessary Category -->
				<div class="slos-category-item" data-category="necessary">
					<div class="slos-category-header">
						<div class="slos-category-header-content">
							<div class="slos-category-icon">
								<span class="dashicons dashicons-shield-alt"></span>
							</div>
							<span class="slos-category-name"><?php esc_html_e( 'Necessary', 'shahi-legalflowsuite' ); ?></span>
						</div>
						<span class="slos-category-expand-icon dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="slos-category-body">
						<label class="slos-category-description-label"><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></label>
						<textarea class="slos-category-description-input" 
									data-category="necessary" 
									placeholder="<?php esc_attr_e( 'Describe what necessary cookies do...', 'shahi-legalflowsuite' ); ?>"></textarea>
						
						<div class="slos-vendor-section">
							<label class="slos-category-description-label"><?php esc_html_e( 'Vendors & Services', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-vendor-list" data-category="necessary">
								<!-- Vendors will be added here dynamically -->
							</div>
							<button type="button" class="slos-vendor-add" data-category="necessary">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Vendor', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>

				<!-- Functional Category -->
				<div class="slos-category-item" data-category="functional">
					<div class="slos-category-header">
						<div class="slos-category-header-content">
							<div class="slos-category-icon">
								<span class="dashicons dashicons-admin-tools"></span>
							</div>
							<span class="slos-category-name"><?php esc_html_e( 'Functional', 'shahi-legalflowsuite' ); ?></span>
						</div>
						<span class="slos-category-expand-icon dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="slos-category-body">
						<label class="slos-category-description-label"><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></label>
						<textarea class="slos-category-description-input" 
									data-category="functional" 
									placeholder="<?php esc_attr_e( 'Describe what functional cookies do...', 'shahi-legalflowsuite' ); ?>"></textarea>
						
						<div class="slos-vendor-section">
							<label class="slos-category-description-label"><?php esc_html_e( 'Vendors & Services', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-vendor-list" data-category="functional">
								<!-- Vendors will be added here dynamically -->
							</div>
							<button type="button" class="slos-vendor-add" data-category="functional">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Vendor', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>

				<!-- Analytics Category -->
				<div class="slos-category-item" data-category="analytics">
					<div class="slos-category-header">
						<div class="slos-category-header-content">
							<div class="slos-category-icon">
								<span class="dashicons dashicons-chart-bar"></span>
							</div>
							<span class="slos-category-name"><?php esc_html_e( 'Analytics', 'shahi-legalflowsuite' ); ?></span>
						</div>
						<span class="slos-category-expand-icon dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="slos-category-body">
						<label class="slos-category-description-label"><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></label>
						<textarea class="slos-category-description-input" 
									data-category="analytics" 
									placeholder="<?php esc_attr_e( 'Describe what analytics cookies do...', 'shahi-legalflowsuite' ); ?>"></textarea>
						
						<div class="slos-vendor-section">
							<label class="slos-category-description-label"><?php esc_html_e( 'Vendors & Services', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-vendor-list" data-category="analytics">
								<!-- Vendors will be added here dynamically -->
							</div>
							<button type="button" class="slos-vendor-add" data-category="analytics">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Vendor', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>

				<!-- Marketing Category -->
				<div class="slos-category-item" data-category="marketing">
					<div class="slos-category-header">
						<div class="slos-category-header-content">
							<div class="slos-category-icon">
								<span class="dashicons dashicons-megaphone"></span>
							</div>
							<span class="slos-category-name"><?php esc_html_e( 'Marketing', 'shahi-legalflowsuite' ); ?></span>
						</div>
						<span class="slos-category-expand-icon dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="slos-category-body">
						<label class="slos-category-description-label"><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></label>
						<textarea class="slos-category-description-input" 
									data-category="marketing" 
									placeholder="<?php esc_attr_e( 'Describe what marketing cookies do...', 'shahi-legalflowsuite' ); ?>"></textarea>
						
						<div class="slos-vendor-section">
							<label class="slos-category-description-label"><?php esc_html_e( 'Vendors & Services', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-vendor-list" data-category="marketing">
								<!-- Vendors will be added here dynamically -->
							</div>
							<button type="button" class="slos-vendor-add" data-category="marketing">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Vendor', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>

				<!-- Preferences Category -->
				<div class="slos-category-item" data-category="preferences">
					<div class="slos-category-header">
						<div class="slos-category-header-content">
							<div class="slos-category-icon">
								<span class="dashicons dashicons-admin-generic"></span>
							</div>
							<span class="slos-category-name"><?php esc_html_e( 'Preferences', 'shahi-legalflowsuite' ); ?></span>
						</div>
						<span class="slos-category-expand-icon dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="slos-category-body">
						<label class="slos-category-description-label"><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></label>
						<textarea class="slos-category-description-input" 
									data-category="preferences" 
									placeholder="<?php esc_attr_e( 'Describe what preference cookies do...', 'shahi-legalflowsuite' ); ?>"></textarea>
						
						<div class="slos-vendor-section">
							<label class="slos-category-description-label"><?php esc_html_e( 'Vendors & Services', 'shahi-legalflowsuite' ); ?></label>
							<div class="slos-vendor-list" data-category="preferences">
								<!-- Vendors will be added here dynamically -->
							</div>
							<button type="button" class="slos-vendor-add" data-category="preferences">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Vendor', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Phase 2.3.1, 2.3.2, 2.3.3: Privacy & Consent Expiry Settings -->
		<div class="slos-config-section slos-privacy-settings">
			<div class="slos-config-title">
				<span class="dashicons dashicons-admin-links"></span>
				<?php esc_html_e( 'Privacy & Consent Expiry', 'shahi-legalflowsuite' ); ?>
			</div>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Configure privacy policy link and consent expiration settings. The privacy URL appears in the banner to help users learn more about your data practices.', 'shahi-legalflowsuite' ); ?>
			</p>

			<div class="slos-form-group">
				<label class="slos-form-label" for="privacy-url">
					<?php esc_html_e( 'Privacy Policy URL', 'shahi-legalflowsuite' ); ?>
				</label>
				<input type="url" 
						id="privacy-url" 
						name="privacy_url" 
						value="<?php echo esc_attr( $banner_settings['privacy_url'] ?? '' ); ?>" 
						placeholder="<?php esc_attr_e( 'https://yoursite.com/privacy-policy', 'shahi-legalflowsuite' ); ?>">
				<span class="slos-form-help">
					<?php esc_html_e( 'Full URL to your privacy policy page. This link appears in the consent banner with the "Learn more" text.', 'shahi-legalflowsuite' ); ?>
				</span>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label" for="learn-more-text">
					<?php esc_html_e( 'Learn More Link Text', 'shahi-legalflowsuite' ); ?>
				</label>
				<input type="text" 
						id="learn-more-text" 
						name="learn_more_text" 
						value="<?php echo esc_attr( $banner_settings['learn_more_text'] ?? '' ); ?>" 
						placeholder="<?php esc_attr_e( 'Learn more', 'shahi-legalflowsuite' ); ?>">
				<span class="slos-form-help">
					<?php esc_html_e( 'Text displayed for the privacy policy link. Leave empty to use the default "Learn more" text.', 'shahi-legalflowsuite' ); ?>
				</span>
			</div>

			<div class="slos-form-group">
				<label class="slos-form-label" for="consent-expiry-days">
					<?php esc_html_e( 'Consent Expiry Days', 'shahi-legalflowsuite' ); ?>
				</label>
				<div>
					<input type="number" 
							id="consent-expiry-days" 
							name="consent_expiry_days" 
							value="<?php echo esc_attr( $banner_settings['consent_expiry_days'] ?? 30 ); ?>" 
							min="1" 
							max="365" 
							step="1">
					<span class="slos-input-suffix"><?php esc_html_e( 'days', 'shahi-legalflowsuite' ); ?></span>
				</div>
				<span class="slos-form-help">
					<?php esc_html_e( 'Number of days before consent expires and users must re-consent. Range: 1-365 days. Default is 30 days (recommended for GDPR compliance).', 'shahi-legalflowsuite' ); ?>
				</span>
			</div>

			<!-- Phase 3.4.1: Grace Period Configuration -->
			<div class="slos-form-group">
				<label class="slos-form-label" for="grace-period-days">
					<?php esc_html_e( 'Re-consent Grace Period', 'shahi-legalflowsuite' ); ?>
				</label>
				<select id="grace-period-days" name="grace_period_days">
					<option value="0" <?php selected( $banner_settings['grace_period_days'] ?? 0, 0 ); ?>>
						<?php esc_html_e( 'Immediate (No Grace Period)', 'shahi-legalflowsuite' ); ?>
					</option>
					<option value="7" <?php selected( $banner_settings['grace_period_days'] ?? 0, 7 ); ?>>
						<?php esc_html_e( '7 Days Grace Period', 'shahi-legalflowsuite' ); ?>
					</option>
					<option value="30" <?php selected( $banner_settings['grace_period_days'] ?? 0, 30 ); ?>>
						<?php esc_html_e( '30 Days Grace Period', 'shahi-legalflowsuite' ); ?>
					</option>
				</select>
				<span class="slos-form-help">
					<?php esc_html_e( 'Grace period before showing re-consent banner when policy or configuration changes. "Immediate" shows the banner right away. With a grace period, users can continue using the site with their existing consent before being re-prompted.', 'shahi-legalflowsuite' ); ?>
				</span>
			</div>
		</div>

		<!-- Save Actions -->
		<div class="slos-save-bar">
			<button class="slos-btn slos-btn-secondary" id="reset-defaults">
				<span class="dashicons dashicons-image-rotate"></span>
				<?php esc_html_e( 'Reset to Defaults', 'shahi-legalflowsuite' ); ?>
			</button>
			<button class="slos-btn slos-btn-primary" id="save-banner">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Changes', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
	</div>

	<!-- Preview Panel -->
	<div class="slos-preview-panel">
		<div class="slos-preview-header">
			<div class="slos-preview-title"><?php esc_html_e( 'Live Preview', 'shahi-legalflowsuite' ); ?></div>
			<div class="slos-preview-controls">
				<!-- Phase 2.4.1: Geo Preview Selector -->
				<div class="slos-geo-selector">
					<span class="slos-geo-selector-label"><?php esc_html_e( 'Region:', 'shahi-legalflowsuite' ); ?></span>
					<select id="preview-region">
						<option value="default"><?php esc_html_e( 'Default', 'shahi-legalflowsuite' ); ?></option>
						<option value="EU"><?php esc_html_e( 'EU (GDPR)', 'shahi-legalflowsuite' ); ?></option>
						<option value="US-CA"><?php esc_html_e( 'California (CCPA)', 'shahi-legalflowsuite' ); ?></option>
						<option value="BR"><?php esc_html_e( 'Brazil (LGPD)', 'shahi-legalflowsuite' ); ?></option>
						<option value="UK"><?php esc_html_e( 'United Kingdom', 'shahi-legalflowsuite' ); ?></option>
						<option value="ROW"><?php esc_html_e( 'Rest of World', 'shahi-legalflowsuite' ); ?></option>
					</select>
				</div>
				
				<div class="slos-preview-actions">
					<button class="slos-preview-btn active" data-device="desktop">
						<span class="dashicons dashicons-desktop"></span>
					</button>
					<button class="slos-preview-btn" data-device="tablet">
						<span class="dashicons dashicons-tablet"></span>
					</button>
					<button class="slos-preview-btn" data-device="mobile">
						<span class="dashicons dashicons-smartphone"></span>
					</button>
				</div>
			</div>
		</div>

		<!-- Phase 2.4.3: Geo behavior help text -->
		<div class="slos-geo-help">
			<p class="slos-geo-help-text">
				<strong><?php esc_html_e( 'Preview Tip:', 'shahi-legalflowsuite' ); ?></strong>
				<?php esc_html_e( 'Select a region to preview how the banner appears to visitors from different locations. The system automatically suggests templates based on visitor region (EU shows GDPR, California shows CCPA, etc.). Your admin template selection overrides geo-suggestions for all visitors.', 'shahi-legalflowsuite' ); ?>
			</p>
		</div>

		<div class="slos-preview-window" id="preview-window">
			<!-- Content mockup -->
			<div class="slos-preview-content">
				<div class="slos-preview-lines">
					<div class="slos-preview-line medium"></div>
					<div class="slos-preview-line"></div>
					<div class="slos-preview-line short"></div>
					<br><br>
					<div class="slos-preview-line"></div>
					<div class="slos-preview-line medium"></div>
					<div class="slos-preview-line"></div>
					<div class="slos-preview-line short"></div>
				</div>
			</div>

			<!-- Banner Preview - Modern Compact Style -->
			<div class="slos-banner-preview bottom" id="banner-preview" style="background: <?php echo esc_attr( $banner_settings['bg_color'] ); ?>; color: <?php echo esc_attr( $banner_settings['text_color'] ); ?>;">
				<div class="slos-banner-text">
					<div class="slos-banner-title" id="preview-title"><?php echo esc_html( $banner_settings['title'] ); ?></div>
					<div class="slos-banner-message" id="preview-message"><?php echo esc_html( $banner_settings['message'] ); ?></div>
				</div>
				<div class="slos-banner-buttons">
					<button class="slos-banner-btn primary" id="preview-accept"><?php echo esc_html( $banner_settings['accept_text'] ); ?></button>
					<button class="slos-banner-btn secondary" id="preview-reject"><?php echo esc_html( $banner_settings['reject_text'] ); ?></button>
					<button class="slos-banner-btn link" id="preview-settings"><?php echo esc_html( $banner_settings['settings_text'] ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
	const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
	let hasUnsavedChanges = false;
	
	// Phase 0.5.3: Toast notification utility
	function showToast(message, type) {
		type = type || 'info';
		
		// Remove existing toasts
		$('.slos-toast').remove();
		
		const $toast = $('<div class="slos-toast slos-toast--' + type + '">' + message + '</div>');
		$('body').append($toast);
		
		// Auto remove after 4 seconds
		setTimeout(function() {
			$toast.fadeOut(300, function() {
				$(this).remove();
			});
		}, 4000);
	}
	
	// Phase 0.6.1: Validate hex color format
	function validateHexColor(value) {
		return /^#[0-9A-Fa-f]{6}$/.test(value);
	}
	
	// Phase 0.6: Show validation error
	function showError($field, message) {
		const fieldId = $field.attr('id');
		const $error = $('#' + fieldId + '-error');
		
		$field.addClass('error');
		$error.text(message).addClass('visible');
	}
	
	// Phase 0.6: Clear validation error
	function clearError($field) {
		const fieldId = $field.attr('id');
		const $error = $('#' + fieldId + '-error');
		
		$field.removeClass('error');
		$error.text('').removeClass('visible');
	}
	
	// Phase 0.6: Validate all form inputs
	function validateForm() {
		let isValid = true;
		
		// Phase 0.6.1: Validate color inputs
		$('.slos-color-hex').each(function() {
			const $field = $(this);
			const value = $field.val();
			
			clearError($field);
			
			if (!validateHexColor(value)) {
				showError($field, '<?php echo esc_js( __( 'Invalid hex color format. Use #RRGGBB (e.g., #3b82f6)', 'shahi-legalflowsuite' ) ); ?>');
				isValid = false;
			}
		});
		
		// Phase 0.6.2: Validate required text fields
		const $acceptText = $('#accept-text');
		const $rejectText = $('#reject-text');
		
		clearError($acceptText);
		clearError($rejectText);
		
		if (!$acceptText.val().trim()) {
			showError($acceptText, '<?php echo esc_js( __( 'Accept button text is required', 'shahi-legalflowsuite' ) ); ?>');
			isValid = false;
		}
		
		if (!$rejectText.val().trim()) {
			showError($rejectText, '<?php echo esc_js( __( 'Reject button text is required', 'shahi-legalflowsuite' ) ); ?>');
			isValid = false;
		}
		
		return isValid;
	}
	
	// Phase 2.2: Category Details Management
	// Phase 2.2.1: Toggle category expansion
	$('.slos-category-header').on('click', function() {
		$(this).closest('.slos-category-item').toggleClass('expanded');
	});
	
	// Phase 2.2.3: Add vendor row
	function addVendorRow(category, vendorData) {
		const name = vendorData?.name || '';
		const purpose = vendorData?.purpose || '';
		
		const vendorHtml = `
			<div class="slos-vendor-item">
				<input type="text" 
						class="slos-vendor-input vendor-name" 
						placeholder="<?php esc_attr_e( 'Vendor name (e.g., Google Analytics)', 'shahi-legalflowsuite' ); ?>" 
						value="${$('<div>').text(name).html()}"
						data-category="${category}">
				<input type="text" 
						class="slos-vendor-input vendor-purpose" 
						placeholder="<?php esc_attr_e( 'Purpose (e.g., Website analytics)', 'shahi-legalflowsuite' ); ?>" 
						value="${$('<div>').text(purpose).html()}"
						data-category="${category}">
				<button type="button" class="slos-vendor-remove" title="<?php esc_attr_e( 'Remove vendor', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
		`;
		
		$(`.slos-vendor-list[data-category="${category}"]`).append(vendorHtml);
	}
	
	// Phase 2.2.3: Add vendor button click
	$('.slos-vendor-add').on('click', function() {
		const category = $(this).data('category');
		addVendorRow(category, {});
		markChanged();
	});
	
	// Phase 2.2.3: Remove vendor button click (delegated)
	$(document).on('click', '.slos-vendor-remove', function() {
		$(this).closest('.slos-vendor-item').fadeOut(200, function() {
			$(this).remove();
			markChanged();
		});
	});
	
	// Phase 2.2.2 & 2.2.3: Mark changes on category inputs
	$(document).on('input', '.slos-category-description-input, .slos-vendor-input', function() {
		markChanged();
	});
	
	// Phase 2.2.4: Load category descriptions and vendors from settings
	function loadCategoryData(settings) {
		// Load descriptions
		if (settings.category_descriptions) {
			$.each(settings.category_descriptions, function(category, description) {
				$(`.slos-category-description-input[data-category="${category}"]`).val(description);
			});
		}
		
		// Load vendors
		if (settings.vendors) {
			$.each(settings.vendors, function(category, vendorList) {
				const $list = $(`.slos-vendor-list[data-category="${category}"]`);
				$list.empty(); // Clear existing
				
				if (Array.isArray(vendorList) && vendorList.length > 0) {
					vendorList.forEach(function(vendor) {
						addVendorRow(category, vendor);
					});
				}
			});
		}
		
		// Phase 2.3.4: Load privacy & consent expiry settings
		if (settings.privacy_url) {
			$('#privacy-url').val(settings.privacy_url);
		}
		if (settings.learn_more_text) {
			$('#learn-more-text').val(settings.learn_more_text);
		}
		if (settings.consent_expiry_days) {
			$('#consent-expiry-days').val(settings.consent_expiry_days);
		}
	}
	
	// Track changes
	function markChanged() {
		hasUnsavedChanges = true;
		$('#save-banner').addClass('has-changes');
	}
	
	// Radio cards
	$('.slos-radio-card').on('click', function() {
		const group = $(this).closest('.slos-radio-group');
		group.find('.slos-radio-card').removeClass('active');
		$(this).addClass('active');
		group.find('input[type="radio"]').prop('checked', false);
		$(this).find('input[type="radio"]').prop('checked', true);
		updatePreview();
		markChanged();
	});

	// Toggle switches
	$('.slos-toggle').on('click', function() {
		$(this).toggleClass('active');
		markChanged();
	});

	// Color pickers
	$('.slos-color-input').on('input', function() {
		const hex = $(this).val();
		$(this).siblings('.slos-color-hex').val(hex);
		updatePreview();
		markChanged();
	});

	// Phase 0.6.1: Color hex input with validation
	$('.slos-color-hex').on('input', function() {
		const $field = $(this);
		let hex = $field.val();
		
		// Auto-prepend # if missing
		if (!/^#/.test(hex) && hex.length > 0) {
			hex = '#' + hex;
			$field.val(hex);
		}
		
		// Validate and update
		if (validateHexColor(hex)) {
			clearError($field);
			$field.siblings('.slos-color-input').val(hex);
			updatePreview();
		} else if (hex.length >= 7) {
			// Only show error after user has typed enough
			showError($field, '<?php echo esc_js( __( 'Invalid hex color format. Use #RRGGBB', 'shahi-legalflowsuite' ) ); ?>');
		}
		
		markChanged();
	});

	// Text inputs
	$('#banner-title, #banner-message, #accept-text, #reject-text, #settings-text').on('input', function() {
		updatePreview();
		markChanged();
	});

	// Device preview
	$('.slos-preview-btn').on('click', function() {
		$('.slos-preview-btn').removeClass('active');
		$(this).addClass('active');
		const device = $(this).data('device');
		const window = $('#preview-window');
		
		if (device === 'mobile') {
			window.css({ 'max-width': '375px', 'margin': '0 auto' });
		} else if (device === 'tablet') {
			window.css({ 'max-width': '768px', 'margin': '0 auto' });
		} else {
			window.css({ 'max-width': 'none', 'margin': '0' });
		}
	});

	// Phase 2.4.2: Geo region preview selector
	$('#preview-region').on('change', function() {
		const region = $(this).val();
		updateGeoPreview(region);
	});

	function updatePreview() {
		// Update position
		const position = $('input[name="banner_position"]:checked').val() || 'bottom';
		const banner = $('#banner-preview');
		banner.removeClass('top bottom center').addClass(position);

		// Phase 2.5.1 & 2.5.2: Update colors via CSS variables for consistency with frontend
		updatePreviewColors();

		// Phase 2.1.4: Update template-specific preview content
		const template = $('input[name="banner_template"]:checked').val() || 'eu';
		updateTemplatePreview(template);

		// Update text
		$('#preview-title').text($('#banner-title').val() || '<?php echo esc_js( __( 'Cookie Consent', 'shahi-legalflowsuite' ) ); ?>');
		$('#preview-message').text($('#banner-message').val() || '<?php echo esc_js( __( 'We use cookies to enhance your experience.', 'shahi-legalflowsuite' ) ); ?>');
		$('#preview-accept').text($('#accept-text').val() || '<?php echo esc_js( __( 'Accept All', 'shahi-legalflowsuite' ) ); ?>');
		$('#preview-reject').text($('#reject-text').val() || '<?php echo esc_js( __( 'Reject All', 'shahi-legalflowsuite' ) ); ?>');
		$('#preview-settings').text($('#settings-text').val() || '<?php echo esc_js( __( 'Customize', 'shahi-legalflowsuite' ) ); ?>');
	}
	
	// Phase 2.1.4: Update preview based on template selection
	function updateTemplatePreview(template) {
		const $banner = $('#banner-preview');
		const $buttons = $('.slos-banner-buttons');
		
		// Remove all template classes
		$banner.removeClass('template-eu template-ccpa template-simple template-advanced');
		
		// Add current template class
		$banner.addClass('template-' + template);
		
		// Update button visibility and text based on template
		switch(template) {
			case 'eu':
			case 'gdpr':
				// EU: Accept All, Reject All, Customize (all visible)
				$('#preview-accept').show().text($('#accept-text').val() || '<?php echo esc_js( __( 'Accept All', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-reject').show().text($('#reject-text').val() || '<?php echo esc_js( __( 'Reject All', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-settings').show().text($('#settings-text').val() || '<?php echo esc_js( __( 'Customize', 'shahi-legalflowsuite' ) ); ?>');
				break;
			
			case 'ccpa':
				// CCPA: Accept, Do Not Sell, Customize
				$('#preview-accept').show().text($('#accept-text').val() || '<?php echo esc_js( __( 'Accept', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-reject').show().text('<?php echo esc_js( __( 'Do Not Sell', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-settings').show().text($('#settings-text').val() || '<?php echo esc_js( __( 'Preferences', 'shahi-legalflowsuite' ) ); ?>');
				break;
			
			case 'simple':
				// Simple: Only Accept button visible
				$('#preview-accept').show().text($('#accept-text').val() || '<?php echo esc_js( __( 'Got It', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-reject').hide();
				$('#preview-settings').hide();
				break;
			
			case 'advanced':
				// Advanced: Accept All, Reject All, Customize (same as EU but may have more features)
				$('#preview-accept').show().text($('#accept-text').val() || '<?php echo esc_js( __( 'Accept All', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-reject').show().text($('#reject-text').val() || '<?php echo esc_js( __( 'Reject All', 'shahi-legalflowsuite' ) ); ?>');
				$('#preview-settings').show().text($('#settings-text').val() || '<?php echo esc_js( __( 'Customize', 'shahi-legalflowsuite' ) ); ?>');
				break;
			
			default:
				// Default to EU template
				$('#preview-accept').show();
				$('#preview-reject').show();
				$('#preview-settings').show();
		}
	}
	
	// Phase 2.4.2: Update preview based on selected geo region
	function updateGeoPreview(region) {
		// Get the admin-selected template (this takes precedence)
		const adminTemplate = $('input[name="banner_template"]:checked').val() || 'eu';
		
		// Determine suggested template based on region
		let suggestedTemplate = adminTemplate;
		let previewMessage = '';
		
		switch(region) {
			case 'EU':
				suggestedTemplate = 'eu';
				previewMessage = '<?php echo esc_js( __( 'We use cookies to enhance your browsing experience. You can accept all cookies or customize your preferences below.', 'shahi-legalflowsuite' ) ); ?>';
				break;
			case 'US-CA':
				suggestedTemplate = 'ccpa';
				previewMessage = '<?php echo esc_js( __( 'We use cookies to personalize content and ads. California residents have the right to opt-out of the sale of personal information.', 'shahi-legalflowsuite' ) ); ?>';
				break;
			case 'BR':
				suggestedTemplate = 'eu'; // Brazil LGPD uses EU-style consent
				previewMessage = '<?php echo esc_js( __( 'We use cookies to improve your experience. Click "Accept All" to consent or customize your preferences.', 'shahi-legalflowsuite' ) ); ?>';
				break;
			case 'UK':
				suggestedTemplate = 'eu'; // UK uses GDPR-style consent
				previewMessage = '<?php echo esc_js( __( 'We use cookies to enhance your experience. Accept all cookies or manage your preferences below.', 'shahi-legalflowsuite' ) ); ?>';
				break;
			case 'ROW':
				suggestedTemplate = 'simple';
				previewMessage = '<?php echo esc_js( __( 'This website uses cookies to improve your experience. By continuing to use this site, you agree to our use of cookies.', 'shahi-legalflowsuite' ) ); ?>';
				break;
			case 'default':
			default:
				// Use admin-selected template
				previewMessage = $('#banner-message').val() || '<?php echo esc_js( __( 'We use cookies to enhance your experience.', 'shahi-legalflowsuite' ) ); ?>';
				break;
		}
		
		// If admin has selected a template, show that (admin override)
		// Otherwise show the region-suggested template
		const displayTemplate = (region === 'default') ? adminTemplate : suggestedTemplate;
		
		// Update preview message to show region-specific wording
		if (region !== 'default' && !$('#banner-message').val()) {
			$('#preview-message').text(previewMessage);
		} else {
			// Use admin's custom message if provided
			$('#preview-message').text($('#banner-message').val() || previewMessage);
		}
		
		// Update template preview
		updateTemplatePreview(displayTemplate);
		
		// Add visual indicator if admin template differs from geo suggestion
		const $banner = $('#banner-preview');
		if (region !== 'default' && adminTemplate !== suggestedTemplate) {
			$banner.addClass('admin-override');
			// Could add a badge or indicator here
		} else {
			$banner.removeClass('admin-override');
		}
	}
	
	// Phase 2.5.1 & 2.5.2: Update preview colors using CSS variables
	function updatePreviewColors() {
		const primaryColor = $('#primary-color').val() || '#10b981';
		const bgColor = $('#bg-color').val() || '#ffffff';
		const textColor = $('#text-color').val() || '#111827';
		
		// Remove any existing inline <style> for preview
		$('#slos-preview-colors').remove();
		
		// Phase 2.5.2: Generate inline <style> block with CSS variables
		const previewStyles = `
			<style id="slos-preview-colors">
				/* Phase 2.5.2: Dynamic CSS variables for admin preview */
				#banner-preview {
					--slos-banner-bg: ${bgColor};
					--slos-banner-text: ${textColor};
					--slos-banner-success: ${primaryColor};
					--slos-banner-success-hover: ${primaryColor};
					background: var(--slos-banner-bg) !important;
					color: var(--slos-banner-text) !important;
				}
				#banner-preview .slos-banner-btn.primary,
				#preview-accept {
					background: var(--slos-banner-success) !important;
					border-color: var(--slos-banner-success) !important;
					color: #ffffff !important;
				}
				#banner-preview .slos-banner-btn.primary:hover,
				#preview-accept:hover {
					background: var(--slos-banner-success-hover) !important;
					opacity: 0.9;
				}
			</style>
		`;
		
		// Inject styles into preview container parent
		$('#banner-preview').before(previewStyles);
	}
	
	function gatherSettings() {
		// Phase 2.2.4: Gather category descriptions
		const categoryDescriptions = {};
		$('.slos-category-description-input').each(function() {
			const category = $(this).data('category');
			const description = $(this).val().trim();
			if (description) {
				categoryDescriptions[category] = description;
			}
		});
		
		// Phase 2.2.4: Gather vendors
		const vendors = {};
		const categories = ['necessary', 'functional', 'analytics', 'marketing', 'preferences'];
		categories.forEach(function(category) {
			vendors[category] = [];
			$(`.slos-vendor-list[data-category="${category}"] .slos-vendor-item`).each(function() {
				const name = $(this).find('.vendor-name').val().trim();
				const purpose = $(this).find('.vendor-purpose').val().trim();
				if (name || purpose) {
					vendors[category].push({
						name: name,
						purpose: purpose
					});
				}
			});
		});
		
		return {
			template: $('input[name="banner_template"]:checked').val() || 'eu', // Phase 2.1.2: Template selection
			position: $('input[name="banner_position"]:checked').val() || 'bottom',
			layout: $('input[name="banner_layout"]:checked').val() || 'bar',
			bg_color: $('#bg-color').val(),
			text_color: $('#text-color').val(),
			primary_color: $('#primary-color').val(),
			title: $('#banner-title').val(),
			message: $('#banner-message').val(),
			accept_text: $('#accept-text').val(),
			reject_text: $('#reject-text').val(),
			settings_text: $('#settings-text').val(),
			icon_position: $('input[name="icon_position"]:checked').val() || 'left', // Phase 1.4.3: Icon position config
			show_reject: $('.slos-toggle[data-option="show_reject"]').hasClass('active'),
			show_settings: $('.slos-toggle[data-option="show_settings"]').hasClass('active'),
			auto_hide: $('.slos-toggle[data-option="auto_hide"]').hasClass('active'),
			blur_background: $('.slos-toggle[data-option="blur_background"]').hasClass('active'),
			category_descriptions: categoryDescriptions, // Phase 2.2.4: Category descriptions
			vendors: vendors, // Phase 2.2.4: Vendor lists
			privacy_url: $('#privacy-url').val().trim(), // Phase 2.3.1: Privacy policy URL
			learn_more_text: $('#learn-more-text').val().trim(), // Phase 2.3.2: Learn more link text
			consent_expiry_days: parseInt($('#consent-expiry-days').val()) || 30, // Phase 2.3.3: Consent expiry days
			grace_period_days: parseInt($('#grace-period-days').val()) || 0 // Phase 3.4.1: Grace period days
		};
	}

	// Phase 0.5: Save settings with fetch API
	$('#save-banner').on('click', async function() {
		// Phase 0.6: Validate form before saving
		if (!validateForm()) {
			showToast('<?php echo esc_js( __( 'Please fix validation errors before saving', 'shahi-legalflowsuite' ) ); ?>', 'error');
			return;
		}
		
		const $btn = $(this);
		const originalText = $btn.text();
		
		// Phase 0.5.2: Enhanced loading state
		$btn.prop('disabled', true)
			.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; margin-right: 6px;"></span> <?php echo esc_js( __( 'Saving...', 'shahi-legalflowsuite' ) ); ?>');
		
		const settings = gatherSettings();
		
		try {
			// Phase 0.5.1: Fetch API call to REST endpoint
			const response = await fetch(API_BASE + '/settings/banner', {
				method: 'POST',
				headers: {
					'X-WP-Nonce': NONCE,
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(settings)
			});
			
			const data = await response.json();
			
			if (response.ok && data.success) {
				// Success state
				$btn.prop('disabled', false)
					.removeClass('has-changes')
					.html('<span class="dashicons dashicons-yes" style="margin-right: 6px;"></span> <?php echo esc_js( __( 'Saved!', 'shahi-legalflowsuite' ) ); ?>');
				
				// Phase 0.5.4: Clear unsaved changes flag
				hasUnsavedChanges = false;
				
				// Phase 0.5.3: Show success toast
				showToast(data.message || '<?php echo esc_js( __( 'Banner settings saved successfully.', 'shahi-legalflowsuite' ) ); ?>', 'success');
				
				// Reset button text after 2 seconds
				setTimeout(function() {
					$btn.text(originalText);
				}, 2000);
			} else {
				// Error from API
				throw new Error(data.message || '<?php echo esc_js( __( 'Failed to save settings.', 'shahi-legalflowsuite' ) ); ?>');
			}
		} catch (error) {
			// Phase 0.5.3: Show error toast
			showToast(error.message || '<?php echo esc_js( __( 'Failed to save settings. Please try again.', 'shahi-legalflowsuite' ) ); ?>', 'error');
			
			// Reset button state
			$btn.prop('disabled', false).text(originalText);
		}
	});

	// Phase 0.5.1 ✓ Fetch API integration
	// Phase 0.5.2 ✓ Loading state with spinner
	// Phase 0.5.3 ✓ Toast notifications
	// Phase 0.5.4 ✓ hasUnsavedChanges flag cleared
	
	// Phase 0.6.1 ✓ Hex color validation with regex
	// Phase 0.6.2 ✓ Required text field validation
	// Phase 0.6.3 ✓ Reset confirmation dialog (already implemented)
	
	// Reset defaults
	$('#reset-defaults').on('click', function() {
		if (confirm('<?php echo esc_js( __( 'Are you sure you want to reset to default settings? This cannot be undone.', 'shahi-legalflowsuite' ) ); ?>')) {
			const $btn = $(this);
			$btn.prop('disabled', true);
			
			$.ajax({
				url: API_BASE + '/settings/banner/reset',
				method: 'POST',
				headers: { 'X-WP-Nonce': NONCE },
				success: function() {
					location.reload();
				},
				error: function() {
					$btn.prop('disabled', false);
					// Just reload if endpoint doesn't exist
					location.reload();
				}
			});
		}
	});
	
	// Warn on unsaved changes
	$(window).on('beforeunload', function() {
		if (hasUnsavedChanges) {
			return '<?php echo esc_js( __( 'You have unsaved changes. Are you sure you want to leave?', 'shahi-legalflowsuite' ) ); ?>';
		}
	});
	
	// Phase 2.2.4: Load banner settings on page load
	async function loadBannerSettings() {
		try {
			const response = await fetch(API_BASE + '/settings/banner', {
				method: 'GET',
				headers: {
					'X-WP-Nonce': NONCE,
					'Content-Type': 'application/json'
				}
			});
			
			const result = await response.json();
			
			if (result.success && result.data) {
				loadCategoryData(result.data);
			}
		} catch (error) {
			console.error('Failed to load banner settings:', error);
		}
	}
	
	// Load settings on page load
	loadBannerSettings();
	
	// Initialize preview
	updatePreview();
});
</script>
