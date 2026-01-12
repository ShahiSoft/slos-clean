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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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
		$event   = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['event'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( <?php
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

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

POST['details'] ) ) : '';

		// Here we would save to DB or send to GA4..
		// For now, we acknowledge the request..
		wp_send_json_success();
	}

	/**
	 * Enqueue widget assets
	 */
	public function enqueue_assets() {
		// Only load widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
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

		wp_localize_script(
			'slos-accessibility-widget',
			'slosWidget',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'position' => get_option( 'slos_widget_position', 'bottom-right' ),
				'color'    => get_option( 'slos_widget_color', 'blue' ),
			)
		);
	}

	/**
	 * Render the widget HTML
	 */
	public function render_widget() {
		// Only render widget if module is enabled..
		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		$widget_enabled = get_option( 'slos_widget_enabled', true );
		if ( ! $widget_enabled ) {
			return;
		}

		$feature_defaults = array(
			'profiles',
			'content_adjustments',
			'text_spacing',
			'color_contrast',
			'navigation_interaction',
			'content_readability',
			'reset',
		);
		$features         = get_option( 'slos_widget_features', $feature_defaults );
		if ( ! is_array( $features ) || empty( $features ) ) {
			$features = $feature_defaults;
		}

		$position       = get_option( 'slos_widget_position', 'bottom-right' );
		$color          = get_option( 'slos_widget_color', 'blue' );
		$position_class = 'slos-aw-pos-' . sanitize_html_class( $position );
		$color_class    = 'slos-aw-theme-' . sanitize_html_class( $color );

		?>
		<div id="slos-accessibility-widget" class="slos-aw-widget <?php echo esc_attr( $position_class . ' ' . $color_class ); ?>" role="region" aria-label="Accessibility Tools">
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
					<?php if ( in_array( 'profiles', $features, true ) ) : ?>
					<div class="slos-aw-group slos-aw-profiles">
						<h4>Accessibility Profiles</h4>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-epilepsy">
							<span class="dashicons dashicons-warning"></span> Epilepsy Safe
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-visually-impaired">
							<span class="dashicons dashicons-visibility"></span> Visually Impaired
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-cognitive">
							<span class="dashicons dashicons-lightbulb"></span> Cognitive Disability
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-adhd">
							<span class="dashicons dashicons-dismiss"></span> ADHD Friendly
						</button>
						<button class="slos-aw-btn slos-aw-profile-btn" data-action="profile-blind">
							<span class="dashicons dashicons-hidden"></span> Blind Users
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'content_adjustments', $features, true ) ) : ?>
					<div class="slos-aw-group">
						<h4>Content Adjustments</h4>
						<button class="slos-aw-btn" data-action="increase-text">
							<span class="dashicons dashicons-editor-textcolor"></span> Increase Text
						</button>
						<button class="slos-aw-btn" data-action="decrease-text">
							<span class="dashicons dashicons-editor-shrinktext"></span> Decrease Text
						</button>
						<button class="slos-aw-btn" data-action="readable-font">
							<span class="dashicons dashicons-editor-font"></span> Readable Font
						</button>
						<button class="slos-aw-btn" data-action="highlight-links">
							<span class="dashicons dashicons-admin-links"></span> Highlight Links
						</button>
						<button class="slos-aw-btn" data-action="underline-links">
							<span class="dashicons dashicons-editor-underline"></span> Underline Links
						</button>
						<button class="slos-aw-btn" data-action="big-cursor">
							<span class="dashicons dashicons-arrow-up-alt"></span> Big Cursor
						</button>
						<button class="slos-aw-btn" data-action="stop-animations">
							<span class="dashicons dashicons-controls-pause"></span> Stop Animations
						</button>
						<button class="slos-aw-btn" data-action="highlight-headings">
							<span class="dashicons dashicons-heading"></span> Highlight Headings
						</button>
						<button class="slos-aw-btn" data-action="hide-images">
							<span class="dashicons dashicons-hidden"></span> Hide Images
						</button>
						<button class="slos-aw-btn" data-action="reading-guide">
							<span class="dashicons dashicons-minus"></span> Reading Guide
						</button>
					</div>
					<?php endif; ?>

					<?php if ( in_array( 'text_spacing', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'color_contrast', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'navigation_interaction', $features, true ) ) : ?>
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
					<?php endif; ?>

					<?php if ( in_array( 'content_readability', $features, true ) ) : ?>
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
					<?php endif; ?>
					
					<?php if ( in_array( 'reset', $features, true ) ) : ?>
					<div class="slos-aw-footer">
						<button id="slos-aw-reset" class="slos-aw-reset">
							<span class="dashicons dashicons-undo"></span> Reset All
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

