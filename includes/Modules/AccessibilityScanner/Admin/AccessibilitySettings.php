<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin;

use ShahiLegalFlowSuite\Core\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AccessibilitySettings {

	private $security;

	public function __construct() {
		$this->security = new Security();
	}

	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'slos_accessibility_settings', 'slos_active_checkers' );
		register_setting( 'slos_accessibility_settings', 'slos_active_fixes' );
		register_setting( 'slos_accessibility_settings', 'slos_widget_features' );
		register_setting( 'slos_accessibility_settings', 'slos_widget_position' );
		register_setting( 'slos_accessibility_settings', 'slos_widget_color' );
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Check if module is enabled
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			wp_die(
				__( 'The Accessibility Scanner module is currently disabled. Please enable it from the Module Dashboard.', 'shahi-legalflowsuite' ),
				__( 'Module Disabled', 'shahi-legalflowsuite' ),
				array( 'back_link' => true )
			);
		}

		// Get available checkers and fixes
		$checkers = $this->get_available_checkers();
		$fixes    = $this->get_available_fixes();

		$active_checkers = get_option( 'slos_active_checkers', array() );
		$active_fixes    = get_option( 'slos_active_fixes', array() );

		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/accessibility-settings.php';
	}

	/**
	 * Render content (for tabbed interface integration)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		// Get available checkers and fixes
		$checkers = $this->get_available_checkers();
		$fixes    = $this->get_available_fixes();

		$active_checkers = get_option( 'slos_active_checkers', array() );
		$active_fixes    = get_option( 'slos_active_fixes', array() );

		// Render inline settings (embedded in tab)
		$this->render_settings_content( $checkers, $fixes, $active_checkers, $active_fixes );
	}

	/**
	 * Render settings content for embedding
	 *
	 * @param array $checkers Available checkers
	 * @param array $fixes Available fixes
	 * @param array $active_checkers Active checkers
	 * @param array $active_fixes Active fixes
	 * @return void
	 */
	private function render_settings_content( $checkers, $fixes, $active_checkers, $active_fixes ) {
		$widget_elements = $this->get_widget_elements();
		$widget_features = get_option( 'slos_widget_features', array_keys( $widget_elements ) );
		if ( ! is_array( $widget_features ) || empty( $widget_features ) ) {
			$widget_features = array_keys( $widget_elements );
		}
		$widget_position = get_option( 'slos_widget_position', 'bottom-right' );
		$widget_color    = get_option( 'slos_widget_color', 'blue' );

		?>
		<style>
		.slos-settings-v3 {
			--slos-bg: #0f172a;
			--slos-card-bg: #1e293b;
			--slos-border: #334155;
			--slos-text-primary: #f8fafc;
			--slos-text-secondary: #94a3b8;
			--slos-text-muted: #64748b;
			--slos-accent: #3b82f6;
			--slos-accent-hover: #2563eb;
			--slos-success: #22c55e;
			--slos-warning: #f59e0b;
			--slos-error: #ef4444;
		}
		.slos-settings-v3 {
			background: var(--slos-bg);
			padding: 24px;
			margin: -10px -20px;
		}
		.slos-settings-v3 .slos-settings-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 24px;
			margin-bottom: 24px;
		}
		.slos-settings-v3 .slos-settings-card {
			background: var(--slos-card-bg);
			border-radius: 12px;
			border: 1px solid var(--slos-border);
			overflow: hidden;
		}
		.slos-settings-v3 .slos-card-header {
			padding: 16px 20px;
			border-bottom: 1px solid var(--slos-border);
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.slos-settings-v3 .slos-card-header h3 {
			margin: 0;
			color: var(--slos-text-primary);
			font-size: 16px;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.slos-settings-v3 .slos-card-header h3 .dashicons {
			color: var(--slos-accent);
		}
		.slos-settings-v3 .slos-card-header p {
			margin: 4px 0 0;
			color: var(--slos-text-muted);
			font-size: 13px;
		}
		.slos-settings-v3 .slos-card-body {
			padding: 20px;
		}
		.slos-settings-v3 .slos-checkbox-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
			gap: 12px;
		}
		.slos-settings-v3 .slos-checkbox-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 10px 12px;
			background: rgba(255,255,255,0.02);
			border-radius: 8px;
			cursor: pointer;
			transition: all 0.2s;
		}
		.slos-settings-v3 .slos-checkbox-item:hover {
			background: rgba(255,255,255,0.05);
		}
		.slos-settings-v3 .slos-checkbox-item input[type="checkbox"] {
			width: 18px;
			height: 18px;
			accent-color: var(--slos-accent);
		}
		.slos-settings-v3 .slos-checkbox-item span {
			color: var(--slos-text-secondary);
			font-size: 13px;
		}
		.slos-settings-v3 .slos-btn-group {
			display: flex;
			gap: 8px;
		}
		.slos-settings-v3 .slos-btn-sm {
			padding: 6px 12px;
			font-size: 12px;
			border-radius: 6px;
			border: 1px solid var(--slos-border);
			background: transparent;
			color: var(--slos-text-secondary);
			cursor: pointer;
			transition: all 0.2s;
		}
		.slos-settings-v3 .slos-btn-sm:hover {
			border-color: var(--slos-accent);
			color: var(--slos-accent);
		}
		.slos-settings-v3 .slos-form-actions {
			text-align: right;
			padding-top: 16px;
		}
		.slos-settings-v3 .slos-btn-primary {
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
		.slos-settings-v3 .slos-btn-primary:hover {
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
		}
		.slos-settings-v3 .slos-widget-select {
			width: 100%;
			padding: 8px 12px;
			background: var(--slos-card-bg);
			border: 1px solid var(--slos-border);
			border-radius: 6px;
			color: var(--slos-text-primary);
			font-size: 13px;
		}
		.slos-settings-v3 .slos-widget-select:focus {
			outline: none;
			border-color: var(--slos-accent);
		}
		@media (max-width: 900px) {
			.slos-settings-v3 .slos-settings-grid {
				grid-template-columns: 1fr;
			}
		}
		</style>

		<div class="slos-settings-v3">
			<form method="post" action="options.php">
				<?php settings_fields( 'slos_accessibility_settings' ); ?>
				
				<div class="slos-settings-grid">
					<!-- Automated Checks Card -->
					<div class="slos-settings-card">
						<div class="slos-card-header">
							<div>
								<h3>
									<span class="dashicons dashicons-search"></span>
									<?php esc_html_e( 'Automated Checks', 'shahi-legalflowsuite' ); ?>
								</h3>
								<p><?php esc_html_e( 'Select which accessibility issues to scan for.', 'shahi-legalflowsuite' ); ?></p>
							</div>
							<div class="slos-btn-group">
								<button type="button" class="slos-btn-sm slos-select-all" data-target="slos_active_checkers">
									<?php esc_html_e( 'All', 'shahi-legalflowsuite' ); ?>
								</button>
								<button type="button" class="slos-btn-sm slos-deselect-all" data-target="slos_active_checkers">
									<?php esc_html_e( 'None', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
						</div>
						<div class="slos-card-body">
							<div class="slos-checkbox-grid">
								<?php foreach ( $checkers as $key => $label ) : ?>
									<label class="slos-checkbox-item">
										<input type="checkbox" name="slos_active_checkers[]" value="<?php echo esc_attr( $key ); ?>" 
											<?php checked( in_array( $key, $active_checkers, true ) ); ?>>
										<span><?php echo esc_html( $label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<!-- Automated Fixes Card -->
					<div class="slos-settings-card">
						<div class="slos-card-header">
							<div>
								<h3>
									<span class="dashicons dashicons-admin-tools"></span>
									<?php esc_html_e( 'Automated Fixes', 'shahi-legalflowsuite' ); ?>
								</h3>
								<p><?php esc_html_e( 'Select which issues to automatically attempt to fix.', 'shahi-legalflowsuite' ); ?></p>
							</div>
							<div class="slos-btn-group">
								<button type="button" class="slos-btn-sm slos-select-all" data-target="slos_active_fixes">
									<?php esc_html_e( 'All', 'shahi-legalflowsuite' ); ?>
								</button>
								<button type="button" class="slos-btn-sm slos-deselect-all" data-target="slos_active_fixes">
									<?php esc_html_e( 'None', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
						</div>
						<div class="slos-card-body">
							<div class="slos-checkbox-grid">
								<?php foreach ( $fixes as $key => $label ) : ?>
									<label class="slos-checkbox-item">
										<input type="checkbox" name="slos_active_fixes[]" value="<?php echo esc_attr( $key ); ?>" 
											<?php checked( in_array( $key, $active_fixes, true ) ); ?>>
										<span><?php echo esc_html( $label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<!-- Accessibility Widget Card -->
					<div class="slos-settings-card">
						<div class="slos-card-header">
							<div>
								<h3>
									<span class="dashicons dashicons-visibility"></span>
									<?php esc_html_e( 'Accessibility Widget', 'shahi-legalflowsuite' ); ?>
								</h3>
								<p><?php esc_html_e( 'Choose which controls are available in the frontend accessibility widget and how it appears.', 'shahi-legalflowsuite' ); ?></p>
							</div>
						</div>
						<div class="slos-card-body">
							<div class="slos-checkbox-grid">
								<?php foreach ( $widget_elements as $key => $label ) : ?>
									<label class="slos-checkbox-item">
										<input type="checkbox" name="slos_widget_features[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $widget_features, true ) ); ?>>
										<span><?php echo esc_html( $label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
							<div style="margin-top:16px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
								<div>
									<label for="slos_widget_position" style="display:block; font-size:13px; font-weight:500; color:var(--slos-text-secondary); margin-bottom:6px;">
										<?php esc_html_e( 'Widget Position', 'shahi-legalflowsuite' ); ?>
									</label>
									<select id="slos_widget_position" name="slos_widget_position" class="slos-widget-select">
										<option value="bottom-right" <?php selected( $widget_position, 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'shahi-legalflowsuite' ); ?></option>
										<option value="bottom-left" <?php selected( $widget_position, 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'shahi-legalflowsuite' ); ?></option>
										<option value="top-right" <?php selected( $widget_position, 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'shahi-legalflowsuite' ); ?></option>
										<option value="top-left" <?php selected( $widget_position, 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'shahi-legalflowsuite' ); ?></option>
									</select>
								</div>
								<div>
									<label for="slos_widget_color" style="display:block; font-size:13px; font-weight:500; color:var(--slos-text-secondary); margin-bottom:6px;">
										<?php esc_html_e( 'Color Theme', 'shahi-legalflowsuite' ); ?>
									</label>
									<select id="slos_widget_color" name="slos_widget_color" class="slos-widget-select">
										<option value="blue" <?php selected( $widget_color, 'blue' ); ?>><?php esc_html_e( 'Blue', 'shahi-legalflowsuite' ); ?></option>
										<option value="green" <?php selected( $widget_color, 'green' ); ?>><?php esc_html_e( 'Green', 'shahi-legalflowsuite' ); ?></option>
										<option value="purple" <?php selected( $widget_color, 'purple' ); ?>><?php esc_html_e( 'Purple', 'shahi-legalflowsuite' ); ?></option>
										<option value="orange" <?php selected( $widget_color, 'orange' ); ?>><?php esc_html_e( 'Orange', 'shahi-legalflowsuite' ); ?></option>
										<option value="dark" <?php selected( $widget_color, 'dark' ); ?>><?php esc_html_e( 'Dark', 'shahi-legalflowsuite' ); ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="slos-form-actions">
					<button type="submit" class="slos-btn-primary">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Settings', 'shahi-legalflowsuite' ); ?>
					</button>
				</div>
			</form>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.slos-select-all').on('click', function() {
				var target = $(this).data('target');
				$('input[name="' + target + '[]"]').prop('checked', true);
			});
			
			$('.slos-deselect-all').on('click', function() {
				var target = $(this).data('target');
				$('input[name="' + target + '[]"]').prop('checked', false);
			});
		});
		</script>
		<?php
	}

	private function get_available_checkers() {
		// This should ideally come from the ScannerEngine, but hardcoding for now based on implementation
		return array(
			'missing-alt-text'    => 'Missing Alt Text',
			'empty-alt-text'      => 'Empty Alt Text',
			'missing-h1'          => 'Missing H1 Heading',
			'skipped-heading'     => 'Skipped Heading Levels',
			'empty-link'          => 'Empty Links',
			'generic-link'        => 'Generic Link Text',
			'missing-label'       => 'Missing Form Labels',
			'redundant-alt'       => 'Redundant Alt Text',
			'empty-heading'       => 'Empty Headings',
			'new-window'          => 'New Window Links',
			'positive-tabindex'   => 'Positive TabIndex',
			'image-map'           => 'Image Map Alt Text',
			'iframe-title'        => 'Iframe Titles',
			'button-label'        => 'Button Labels',
			'table-header'        => 'Table Headers',
			'alt-quality'         => 'Alt Text Quality',
			'decorative-image'    => 'Decorative Images',
			'complex-image'       => 'Complex Images',
			'svg-access'          => 'SVG Accessibility',
			'bg-image'            => 'Background Images',
			'logo-image'          => 'Logo Images',
			'multiple-h1'         => 'Multiple H1 Headings',
			'heading-visual'      => 'Visual Headings',
			'heading-length'      => 'Heading Length',
			'heading-unique'      => 'Unique Headings',
			'heading-nesting'     => 'Heading Nesting',
			'fieldset-legend'     => 'Fieldset Legends',
			'autocomplete'        => 'Autocomplete Attributes',
			'input-type'          => 'Input Types',
			'placeholder-label'   => 'Placeholder as Label',
			'custom-control'      => 'Custom Controls',
			'orphaned-label'      => 'Orphaned Labels',
			'required-attr'       => 'Required Attributes',
			'error-message'       => 'Error Messages',
			'form-aria'           => 'Form ARIA',
			'link-dest'           => 'Link Destinations',
			'skip-link'           => 'Skip Links',
			'download-link'       => 'Download Links',
			'external-link'       => 'External Links',
			'contrast'            => 'Color Contrast',
			'focus-indicator'     => 'Focus Indicators',
			'color-reliance'      => 'Color Reliance',
			'complex-contrast'    => 'Complex Contrast',
			'keyboard-trap'       => 'Keyboard Traps',
			'focus-order'         => 'Focus Order',
			'interactive-element' => 'Interactive Elements',
			'modal-access'        => 'Modal Accessibility',
			'widget-keyboard'     => 'Widget Keyboard Access',
			'aria-role'           => 'ARIA Roles',
			'aria-attr'           => 'ARIA Attributes',
			'landmark-role'       => 'Landmark Roles',
			'redundant-aria'      => 'Redundant ARIA',
			'hidden-content'      => 'Hidden Content',
			'semantic-html'       => 'Semantic HTML',
			'live-region'         => 'Live Regions',
			'aria-state'          => 'ARIA States',
			'invalid-aria'        => 'Invalid ARIA Combinations',
			'page-structure'      => 'Page Structure',
			'video-access'        => 'Video Accessibility',
			'audio-access'        => 'Audio Accessibility',
			'media-alt'           => 'Media Alternatives',
			'table-caption'       => 'Table Captions',
			'complex-table'       => 'Complex Tables',
			'layout-table'        => 'Layout Tables',
			'empty-cell'          => 'Empty Table Cells',
			'viewport'            => 'Viewport Configuration',
			'touch-target'        => 'Touch Targets',
			'touch-gesture'       => 'Touch Gestures',
			// Phase 3: New Checkers (WCAG Coverage Enhancement)
			'language-change'      => 'Language Changes',
			'animation-pause'      => 'Animation Pause Controls',
			'timing-control'       => 'Timing Controls',
			'status-message'       => 'Status Messages',
			'error-identification' => 'Error Identification',
		);
	}

	private function get_available_fixes() {
		return array(
			'add_skip_links'          => 'Add Skip Links',
			'fix_focus_outlines'      => 'Fix Focus Outlines',
			'fix_link_underlines'     => 'Force Link Underlines',
			'block_new_window'        => 'Block New Window Links',
			'fix_language_attributes' => 'Add Language Attributes',
			'fix_viewport_meta'       => 'Fix Viewport Meta',
			'label_search_fields'     => 'Label Search Fields',
			'label_comment_fields'    => 'Label Comment Fields',
			'add_page_titles'         => 'Add Page Titles',
			'fix_tab_index'           => 'Fix Tab Index',
			'remove_title_attributes' => 'Remove Title Attributes',
			'add_alt_placeholders'    => 'Add Alt Text Placeholders',
			'add_aria_landmarks'      => 'Add ARIA Landmarks',
			'fix_empty_links'         => 'Fix Empty Links',
			'add_heading_structure'   => 'Add Heading Structure',
			'add_table_headers'       => 'Add Table Headers',
			'add_form_labels'         => 'Add Form Labels',
			'fix_color_contrast'      => 'Fix Color Contrast',
			'fix_link_warnings'       => 'Add Link Warnings',
			'fix_image_maps'          => 'Fix Image Maps',
			'add_button_labels'       => 'Add Button Labels',
			'fix_list_semantics'      => 'Fix List Semantics',
			'add_live_regions'        => 'Add Live Regions',
			'fix_modal_dialogs'       => 'Fix Modal Dialogs',
			'generate_transcripts'    => 'Generate Transcripts',
			// Phase 3: New Auto-Fixers (WCAG Coverage Enhancement)
			'fix_language_changes'     => 'Fix Language Changes',
			'fix_animation_controls'   => 'Fix Animation Controls',
			'fix_timing_controls'      => 'Fix Timing Controls',
			'fix_status_messages'      => 'Fix Status Messages',
			'fix_error_identification' => 'Fix Error Identification',
		);
	}

	/**
	 * Get available accessibility widget feature groups
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_widget_elements() {
		return array(
			'profiles'               => __( 'Accessibility Profiles panel', 'shahi-legalflowsuite' ),
			'content_adjustments'    => __( 'Content adjustments (text size, cursor, highlights)', 'shahi-legalflowsuite' ),
			'text_spacing'           => __( 'Text spacing & alignment controls', 'shahi-legalflowsuite' ),
			'color_contrast'         => __( 'Color & contrast and color-blindness tools', 'shahi-legalflowsuite' ),
			'navigation_interaction' => __( 'Navigation & interaction helpers', 'shahi-legalflowsuite' ),
			'content_readability'    => __( 'Content & readability tools', 'shahi-legalflowsuite' ),
			'reset'                  => __( '"Reset all" button', 'shahi-legalflowsuite' ),
		);
	}
}

