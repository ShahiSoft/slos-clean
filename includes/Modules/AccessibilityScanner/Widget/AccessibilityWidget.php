<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AccessibilityWidget {

	/**
	 * Initialize the widget
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_widget' ) );
		add_action( 'wp_ajax_slos_track_widget_event', array( $this, 'track_event' ) );
		add_action( 'wp_ajax_nopriv_slos_track_widget_event', array( $this, 'track_event' ) );
	}

	/**
	 * Track widget usage event
	 */
	public function track_event() {
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( $_POST['event'] ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( $_POST['details'] ) : '';

		// Here we would save to DB or send to GA4
		// For now, we acknowledge the request
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		// Check if widget is enabled in settings
		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		wp_enqueue_style(
			'slos-accessibility-widget',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/slos-accessibility-widget.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		wp_enqueue_script(
			'slos-accessibility-widget',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/slos-accessibility-widget.js',
			array( 'jquery' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);

		// Get widget position
		$widget_position = get_option( 'slos_widget_position', 'bottom-right' );

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => $widget_position,
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		// Check if widget is enabled in settings
		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		// Get widget settings
		$widget_position = get_option( 'slos_widget_position', 'bottom-right' );
		$widget_features = get_option( 'slos_widget_features', array() );

		// If no features selected, show all (default behavior)
		$show_all = empty( $widget_features );

		// Define feature categories for conditional rendering
		$categories = array(
			'profiles'   => array( 'profile-epilepsy', 'profile-visually-impaired', 'profile-cognitive', 'profile-adhd', 'profile-blind' ),
			'content'    => array( 'increase-text', 'decrease-text', 'readable-font', 'highlight-links', 'underline-links', 'big-cursor', 'stop-animations', 'highlight-headings', 'hide-images', 'reading-guide' ),
			'spacing'    => array( 'increase-line-height', 'increase-letter-spacing', 'align-left', 'align-center', 'align-right' ),
			'color'      => array( 'grayscale', 'monochrome', 'low-saturation', 'dark-mode', 'blue-light-filter', 'protanopia', 'deuteranopia', 'tritanopia', 'high-contrast', 'negative-contrast', 'light-background', 'smart-contrast' ),
			'navigation' => array( 'reading-mask', 'text-to-speech', 'tooltip-hover', 'virtual-keyboard' ),
			'readability' => array( 'page-structure', 'dictionary', 'reader-mode', 'translate' ),
		);

		// Check which categories have enabled features
		$show_category = array();
		if ( $show_all ) {
			// Show all categories if no features selected
			foreach ( array_keys( $categories ) as $cat ) {
				$show_category[ $cat ] = true;
			}
		} else {
			// Only show categories that have at least one enabled feature
			foreach ( $categories as $cat_key => $cat_features ) {
				$show_category[ $cat_key ] = ! empty( array_intersect( $cat_features, $widget_features ) );
			}
		}

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget slos-aw-<?php echo esc_attr( $widget_position ); ?>" role="region" aria-label="Accessibility Tools">
			<button id="slos-aw-toggle" class="slos-aw-toggle" aria-label="Open Accessibility Tools" aria-expanded="false">
				<span class="dashicons dashicons-universal-access"></span>
			</button>
			
			<div id="slos-aw-panel" class="slos-aw-panel" aria-hidden="true">
				<div class="slos-aw-header">
					<h3>Accessibility Tools</h3>
					<button id="slos-aw-close" class="slos-aw-close" aria-label="Close Accessibility Tools">
						<span class="dashicons dashicons-no-alt"></span>
					</button>
				</div>
				
				<div class="slos-aw-body">
					<?php if ( ! empty( $show_category['profiles'] ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<?php if ( $this->should_show_feature( 'profile-epilepsy', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'profile-visually-impaired', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'profile-cognitive', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'profile-adhd', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'profile-blind', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<?php if ( ! empty( $show_category['content'] ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<?php if ( $this->should_show_feature( 'increase-text', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'decrease-text', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'readable-font', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'highlight-links', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'underline-links', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'big-cursor', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'stop-animations', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'highlight-headings', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'hide-images', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<?php endif; ?>
						<?php if ( $this->should_show_feature( 'reading-guide', $show_all, $widget_features ) ) : ?>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<div class="slos-aw-group">
						<h4>Text Spacing & Alignment</h4>
						<button class="slos-aw-btn" data-action="increase-line-height">
							<span class="dashicons dashicons-editor-alignleft"></span> Increase Line Height
						</button>
						<button class="slos-aw-btn" data-action="increase-letter-spacing">
							<span class="dashicons dashicons-editor-expand"></span> Increase Letter Spacing
						</button>
						<button class="slos-aw-btn" data-action="align-left">
							<span class="dashicons dashicons-editor-alignleft"></span> Align Left
						</button>
						<button class="slos-aw-btn" data-action="align-center">
							<span class="dashicons dashicons-editor-aligncenter"></span> Align Center
						</button>
						<button class="slos-aw-btn" data-action="align-right">
							<span class="dashicons dashicons-editor-alignright"></span> Align Right
						</button>
					</div>

					<div class="slos-aw-group">
						<h4>Color & Contrast</h4>
						<button class="slos-aw-btn" data-action="grayscale">
							<span class="dashicons dashicons-image-filter"></span> Grayscale
						</button>
						<button class="slos-aw-btn" data-action="monochrome">
							<span class="dashicons dashicons-art"></span> Monochrome
						</button>
						<button class="slos-aw-btn" data-action="low-saturation">
							<span class="dashicons dashicons-admin-customizer"></span> Low Saturation
						</button>
						<button class="slos-aw-btn" data-action="dark-mode">
							<span class="dashicons dashicons-moon"></span> Dark Mode
						</button>
						<button class="slos-aw-btn" data-action="blue-light-filter">
							<span class="dashicons dashicons-visibility"></span> Blue Light Filter
						</button>
						<div class="slos-aw-subgroup">
							<h5>Color Blindness</h5>
							<button class="slos-aw-btn" data-action="protanopia">Protanopia</button>
							<button class="slos-aw-btn" data-action="deuteranopia">Deuteranopia</button>
							<button class="slos-aw-btn" data-action="tritanopia">Tritanopia</button>
						</div>
						<button class="slos-aw-btn" data-action="high-contrast">
							<span class="dashicons dashicons-lightbulb"></span> High Contrast
						</button>
						<button class="slos-aw-btn" data-action="negative-contrast">
							<span class="dashicons dashicons-admin-appearance"></span> Negative Contrast
						</button>
						<button class="slos-aw-btn" data-action="light-background">
							<span class="dashicons dashicons-visibility"></span> Light Background
						</button>
						<button class="slos-aw-btn" data-action="smart-contrast">
							<span class="dashicons dashicons-performance"></span> Smart Contrast
						</button>
					</div>

					<div class="slos-aw-group">
						<h4>Navigation & Interaction</h4>
						<button class="slos-aw-btn" data-action="reading-mask">
							<span class="dashicons dashicons-align-center"></span> Reading Mask
						</button>
						<button class="slos-aw-btn" data-action="text-to-speech">
							<span class="dashicons dashicons-megaphone"></span> Text to Speech
						</button>
						<button class="slos-aw-btn" data-action="tooltip-hover">
							<span class="dashicons dashicons-info"></span> Tooltip on Hover
						</button>
						<button class="slos-aw-btn" data-action="virtual-keyboard">
							<span class="dashicons dashicons-keyboard-layout"></span> Virtual Keyboard
						</button>
					</div>

					<div class="slos-aw-group">
						<h4>Content & Readability</h4>
						<button class="slos-aw-btn" data-action="page-structure">
							<span class="dashicons dashicons-list-view"></span> Page Structure
						</button>
						<button class="slos-aw-btn" data-action="dictionary">
							<span class="dashicons dashicons-book"></span> Dictionary
						</button>
						<button class="slos-aw-btn" data-action="reader-mode">
							<span class="dashicons dashicons-text-page"></span> Reader Mode
						</button>
						<button class="slos-aw-btn" data-action="translate">
							<span class="dashicons dashicons-translation"></span> Translate
						</button>
					</div>
					
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if any features in a category are enabled
	 *
	 * @param array  $features Enabled features array
	 * @param string $prefix   Category prefix to check
	 * @return bool
	 */
	private function has_feature_in_category( $features, $prefix ) {
		foreach ( $features as $feature ) {
			if ( strpos( $feature, $prefix ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a feature should be shown
	 *
	 * @param string $feature_key Feature key to check
	 * @param bool   $show_all    Whether to show all features
	 * @param array  $features    Enabled features array
	 * @return bool
	 */
	private function should_show_feature( $feature_key, $show_all, $features ) {
		return $show_all || in_array( $feature_key, $features, true );
	}
}
