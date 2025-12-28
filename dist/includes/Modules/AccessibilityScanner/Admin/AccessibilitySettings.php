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
		register_setting( 'slos_accessibility_settings', 'slos_widget_enabled', array(
			'type'    => 'boolean',
			'default' => true,
		) );
		register_setting( 'slos_accessibility_settings', 'slos_widget_position', array(
			'type'    => 'string',
			'default' => 'bottom-right',
		) );
		register_setting( 'slos_accessibility_settings', 'slos_widget_features', array(
			'type'    => 'array',
			'default' => array(),
		) );
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

		// Get widget settings
		$widget_enabled  = get_option( 'slos_widget_enabled', true );
		$widget_position = get_option( 'slos_widget_position', 'bottom-right' );
		$widget_features = get_option( 'slos_widget_features', array() );

		// Render inline settings (embedded in tab)
		$this->render_settings_content( $checkers, $fixes, $active_checkers, $active_fixes, $widget_enabled, $widget_position, $widget_features );
	}

	/**
	 * Render settings content for embedding
	 *
	 * @param array $checkers Available checkers
	 * @param array $fixes Available fixes
	 * @param array $active_checkers Active checkers
	 * @param array $active_fixes Active fixes
	 * @param bool $widget_enabled Widget enabled status
	 * @param string $widget_position Widget position
	 * @param array $widget_features Widget features
	 * @return void
	 */
	private function render_settings_content( $checkers, $fixes, $active_checkers, $active_fixes, $widget_enabled = true, $widget_position = 'bottom-right', $widget_features = array() ) {
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
				</div>

				<!-- Frontend Widget Settings Card -->
				<div class="slos-settings-card" style="margin-top: 24px;">
					<div class="slos-card-header">
						<div>
							<h3>
								<span class="dashicons dashicons-universal-access"></span>
								<?php esc_html_e( 'Frontend Widget Settings', 'shahi-legalflowsuite' ); ?>
							</h3>
							<p><?php esc_html_e( 'Configure the accessibility widget displayed on your website frontend.', 'shahi-legalflowsuite' ); ?></p>
						</div>
					</div>
					<div class="slos-card-body">
						<!-- Enable/Disable Widget -->
						<div style="margin-bottom: 24px; padding: 16px; background: rgba(255,255,255,0.02); border-radius: 8px;">
							<label class="slos-checkbox-item" style="cursor: pointer;">
								<input type="checkbox" name="slos_widget_enabled" value="1" 
									<?php checked( $widget_enabled ); ?>
									style="width: 20px; height: 20px;">
								<span style="font-size: 14px; font-weight: 600; color: var(--slos-text-primary);">
									<?php esc_html_e( 'Enable Frontend Accessibility Widget', 'shahi-legalflowsuite' ); ?>
								</span>
							</label>
							<p style="margin: 8px 0 0 30px; color: var(--slos-text-muted); font-size: 13px;">
								<?php esc_html_e( 'Display the accessibility tools icon on your website for visitors to adjust their experience.', 'shahi-legalflowsuite' ); ?>
							</p>
						</div>

						<!-- Widget Position -->
						<div style="margin-bottom: 24px;">
							<h4 style="color: var(--slos-text-primary); margin-bottom: 12px; font-size: 14px;">
								<?php esc_html_e( 'Widget Position', 'shahi-legalflowsuite' ); ?>
							</h4>
							<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
								<?php
								$positions = array(
									'bottom-right' => __( 'Bottom Right (Recommended)', 'shahi-legalflowsuite' ),
									'bottom-left'  => __( 'Bottom Left', 'shahi-legalflowsuite' ),
									'top-right'    => __( 'Top Right', 'shahi-legalflowsuite' ),
									'top-left'     => __( 'Top Left', 'shahi-legalflowsuite' ),
								);
								foreach ( $positions as $pos_key => $pos_label ) :
									?>
									<label class="slos-checkbox-item" style="cursor: pointer; background: rgba(255,255,255,0.02);">
										<input type="radio" name="slos_widget_position" value="<?php echo esc_attr( $pos_key ); ?>" 
											<?php checked( $widget_position, $pos_key ); ?>
											style="width: 18px; height: 18px;">
										<span style="font-size: 13px;"><?php echo esc_html( $pos_label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Widget Features -->
						<div>
							<h4 style="color: var(--slos-text-primary); margin-bottom: 12px; font-size: 14px;">
								<?php esc_html_e( 'Available Features', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p style="color: var(--slos-text-muted); font-size: 13px; margin-bottom: 16px;">
								<?php esc_html_e( 'Select which accessibility features to include in the widget. Unchecked features will be hidden from users.', 'shahi-legalflowsuite' ); ?>
							</p>
							
							<?php
							$widget_features_list = $this->get_available_widget_features();
							foreach ( $widget_features_list as $category_key => $category ) :
								?>
								<div style="margin-bottom: 20px; border: 1px solid var(--slos-border); border-radius: 8px; padding: 16px; background: rgba(255,255,255,0.01);">
									<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
										<h5 style="color: var(--slos-text-primary); margin: 0; font-size: 13px; font-weight: 600;">
											<?php echo esc_html( $category['label'] ); ?>
										</h5>
										<div class="slos-btn-group">
											<button type="button" class="slos-btn-sm slos-select-all" data-target="category-<?php echo esc_attr( $category_key ); ?>">
												<?php esc_html_e( 'All', 'shahi-legalflowsuite' ); ?>
											</button>
											<button type="button" class="slos-btn-sm slos-deselect-all" data-target="category-<?php echo esc_attr( $category_key ); ?>">
												<?php esc_html_e( 'None', 'shahi-legalflowsuite' ); ?>
											</button>
										</div>
									</div>
									<div class="slos-checkbox-grid">
										<?php foreach ( $category['features'] as $feature_key => $feature_label ) : ?>
											<label class="slos-checkbox-item category-<?php echo esc_attr( $category_key ); ?>">
												<input type="checkbox" name="slos_widget_features[]" value="<?php echo esc_attr( $feature_key ); ?>" 
													<?php checked( in_array( $feature_key, $widget_features, true ) || empty( $widget_features ) ); ?>>
												<span><?php echo esc_html( $feature_label ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
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
				if (target.startsWith('category-')) {
					// Select all in specific category
					$('.slos-checkbox-item.' + target + ' input[type="checkbox"]').prop('checked', true);
				} else {
					// Select all in settings group
					$('input[name="' + target + '[]"]').prop('checked', true);
				}
			});
			
			$('.slos-deselect-all').on('click', function() {
				var target = $(this).data('target');
				if (target.startsWith('category-')) {
					// Deselect all in specific category
					$('.slos-checkbox-item.' + target + ' input[type="checkbox"]').prop('checked', false);
				} else {
					// Deselect all in settings group
					$('input[name="' + target + '[]"]').prop('checked', false);
				}
			});
		});
			});
		});
		</script>
		<?php
	}

	private function get_available_checkers() {
		// Core essential accessibility checks - reduced from 70 to 35
		return array(
			'missing-alt-text'    => 'Missing Alt Text',
			'empty-alt-text'      => 'Empty Alt Text',
			'missing-h1'          => 'Missing H1 Heading',
			'skipped-heading'     => 'Skipped Heading Levels',
			'empty-link'          => 'Empty Links',
			'generic-link'        => 'Generic Link Text',
			'missing-label'       => 'Missing Form Labels',
			'empty-heading'       => 'Empty Headings',
			'iframe-title'        => 'Iframe Titles',
			'button-label'        => 'Button Labels',
			'table-header'        => 'Table Headers',
			'multiple-h1'         => 'Multiple H1 Headings',
			'placeholder-label'   => 'Placeholder as Label',
			'orphaned-label'      => 'Orphaned Labels',
			'required-attr'       => 'Required Attributes',
			'error-message'       => 'Error Messages',
			'form-aria'           => 'Form ARIA',
			'skip-link'           => 'Skip Links',
			'contrast'            => 'Color Contrast',
			'focus-indicator'     => 'Focus Indicators',
			'keyboard-trap'       => 'Keyboard Traps',
			'focus-order'         => 'Focus Order',
			'interactive-element' => 'Interactive Elements',
			'modal-access'        => 'Modal Accessibility',
			'aria-role'           => 'ARIA Roles',
			'aria-attr'           => 'ARIA Attributes',
			'landmark-role'       => 'Landmark Roles',
			'hidden-content'      => 'Hidden Content',
			'semantic-html'       => 'Semantic HTML',
			'page-structure'      => 'Page Structure',
			'video-access'        => 'Video Accessibility',
			'audio-access'        => 'Audio Accessibility',
			'table-caption'       => 'Table Captions',
			'viewport'            => 'Viewport Configuration',
			'touch-target'        => 'Touch Targets',
		);
	}

	private function get_available_fixes() {
		// Automated fixes have been removed to prevent unintended modifications
		return array();
	}

	/**
	 * Get available widget features organized by category
	 *
	 * @return array Widget features grouped by category
	 */
	private function get_available_widget_features() {
		return array(
			'profiles' => array(
				'label' => 'Accessibility Profiles',
				'features' => array(
					'profile-epilepsy' => 'Epilepsy Safe',
					'profile-visually-impaired' => 'Visually Impaired',
					'profile-cognitive' => 'Cognitive Disability',
					'profile-adhd' => 'ADHD Friendly',
					'profile-blind' => 'Blind Users',
				),
			),
			'content' => array(
				'label' => 'Content Adjustments',
				'features' => array(
					'increase-text' => 'Increase Text',
					'decrease-text' => 'Decrease Text',
					'readable-font' => 'Readable Font',
					'highlight-links' => 'Highlight Links',
					'underline-links' => 'Underline Links',
					'big-cursor' => 'Big Cursor',
					'stop-animations' => 'Stop Animations',
					'highlight-headings' => 'Highlight Headings',
					'hide-images' => 'Hide Images',
					'reading-guide' => 'Reading Guide',
				),
			),
			'spacing' => array(
				'label' => 'Text Spacing & Alignment',
				'features' => array(
					'increase-line-height' => 'Increase Line Height',
					'increase-letter-spacing' => 'Increase Letter Spacing',
					'align-left' => 'Align Left',
					'align-center' => 'Align Center',
					'align-right' => 'Align Right',
				),
			),
			'color' => array(
				'label' => 'Color & Contrast',
				'features' => array(
					'grayscale' => 'Grayscale',
					'monochrome' => 'Monochrome',
					'low-saturation' => 'Low Saturation',
					'dark-mode' => 'Dark Mode',
					'blue-light-filter' => 'Blue Light Filter',
					'protanopia' => 'Protanopia (Color Blindness)',
					'deuteranopia' => 'Deuteranopia (Color Blindness)',
					'tritanopia' => 'Tritanopia (Color Blindness)',
					'high-contrast' => 'High Contrast',
					'negative-contrast' => 'Negative Contrast',
					'light-background' => 'Light Background',
					'smart-contrast' => 'Smart Contrast',
				),
			),
			'navigation' => array(
				'label' => 'Navigation & Interaction',
				'features' => array(
					'reading-mask' => 'Reading Mask',
					'text-to-speech' => 'Text to Speech',
					'tooltip-hover' => 'Tooltip on Hover',
					'virtual-keyboard' => 'Virtual Keyboard',
				),
			),
			'readability' => array(
				'label' => 'Content & Readability',
				'features' => array(
					'page-structure' => 'Page Structure',
					'dictionary' => 'Dictionary',
					'reader-mode' => 'Reader Mode',
					'translate' => 'Translate',
				),
			),
		);
	}
}
