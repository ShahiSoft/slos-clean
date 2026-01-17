<?php
/**
 * Theme Manager
 *
 * Handles color theme management and switching for the admin interface.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Includes/Admin
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Manager Class
 *
 * Manages color themes for the admin interface.
 *
 * @since 1.0.0
 */
class ThemeManager {

	/**
	 * Available themes configuration
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $themes = array();

	/**
	 * Current active theme
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $current_theme;

	/**
	 * Option name for storing current theme
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const THEME_OPTION = 'shahi_legalflowsuite_theme';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_themes();
		$this->current_theme = get_option( self::THEME_OPTION, 'neon-aether' );

		add_action( 'admin_enqueue_scripts', array( $this, 'inject_theme_variables' ) );
		add_action( 'wp_ajax_shahi_switch_theme', array( $this, 'ajax_switch_theme' ) );
	}

	/**
	 * Load available themes from configuration
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_themes() {
		$themes_file = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'config/themes.php';

		if ( file_exists( $themes_file ) ) {
			$this->themes = require $themes_file;
		} else {
			// Fallback to default theme..
			$this->themes = array(
				'neon-aether' => array(
					'name'           => 'Neon Aether',
					'description'    => 'Default electric cyan and violet theme',
					'preview_colors' => array( '#00d4ff', '#7c3aed', '#0a0e27' ),
					'variables'      => array(),
				),
			);
		}
	}

	/**
	 * Get all available themes
	 *
	 * @since 1.0.0
	 * @return array Available themes
	 */
	public function get_themes() {
		return $this->themes;
	}

	/**
	 * Get current active theme
	 *
	 * @since 1.0.0
	 * @return string Theme key
	 */
	public function get_current_theme() {
		return $this->current_theme;
	}

	/**
	 * Get theme data
	 *
	 * @since 1.0.0
	 * @param string $theme_key Theme key
	 * @return array|null Theme data or null if not found
	 */
	public function get_theme( $theme_key ) {
		return $this->themes[ $theme_key ] ?? null;
	}

	/**
	 * Switch to a different theme
	 *
	 * @since 1.0.0
	 * @param string $theme_key Theme key to switch to
	 * @return bool Success status
	 */
	public function switch_theme( $theme_key ) {
		if ( ! isset( $this->themes[ $theme_key ] ) ) {
			return false;
		}

		update_option( self::THEME_OPTION, $theme_key );
		$this->current_theme = $theme_key;

		return true;
	}

	/**
	 * Inject theme CSS variables into admin pages
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook
	 * @return void
	 */
	public function inject_theme_variables( $hook ) {
		// Only inject on plugin pages..
		if ( strpos( $hook, 'shahi-legalflowsuite' ) === false && strpos( $hook, 'shahitemplate' ) === false ) {
			return;
		}

		$theme = $this->get_theme( $this->current_theme );
		if ( ! $theme || empty( $theme['variables'] ) ) {
			return;
		}

		// Build inline CSS with theme variables..
		$css = ":root {\n";
		foreach ( $theme['variables'] as $var => $value ) {
			$css .= "    $var: $value;\n";
		}
		$css .= "}\n";

		wp_add_inline_style( 'shahi-admin-global', $css );
	}

	/**
	 * AJAX handler for theme switching
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_switch_theme() {
		// Verify nonce using Security class (handles prefix automatically)..
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_theme_switch' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$theme_key = sanitize_text_field( $_POST['theme'] ?? '' );

		if ( empty( $theme_key ) ) {
			wp_send_json_error( array( 'message' => 'Theme key is required' ) );
		}

		if ( $this->switch_theme( $theme_key ) ) {
			wp_send_json_success(
				array(
					'message'    => 'Theme switched successfully',
					'theme'      => $theme_key,
					'theme_name' => $this->themes[ $theme_key ]['name'],
				)
			);
		} else {
			wp_send_json_error( array( 'message' => 'Invalid theme' ) );
		}
	}

	/**
	 * Render theme selector interface
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_theme_selector() {
		$current = $this->current_theme;
		?>
		<div class="shahi-theme-selector">
			<h3><?php esc_html_e( 'Admin Color Theme', 'shahi-legalflowsuite' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Choose your preferred color scheme for the admin interface.', 'shahi-legalflowsuite' ); ?>
			</p>
			
			<div class="shahi-theme-grid">
				<?php foreach ( $this->themes as $key => $theme ) : ?>
					<div class="shahi-theme-option <?php echo $key === $current ? 'active' : ''; ?>" 
						data-theme="<?php echo esc_attr( $key ); ?>">
						<div class="shahi-theme-preview">
							<?php foreach ( $theme['preview_colors'] as $color ) : ?>
								<span class="shahi-color-swatch" style="background-color: <?php echo esc_attr( $color ); ?>"></span>
							<?php endforeach; ?>
						</div>
						<h4><?php echo esc_html( $theme['name'] ); ?></h4>
						<p><?php echo esc_html( $theme['description'] ); ?></p>
						<?php if ( $key === $current ) : ?>
							<span class="shahi-theme-active-badge">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Active', 'shahi-legalflowsuite' ); ?>
							</span>
						<?php else : ?>
							<button type="button" 
									class="button button-secondary shahi-theme-switch-btn"
									data-theme="<?php echo esc_attr( $key ); ?>">
								<?php esc_html_e( 'Activate', 'shahi-legalflowsuite' ); ?>
							</button>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		
		<?php $theme_nonce = wp_create_nonce( 'shahi_theme_switch' ); ?>
		<script>
		jQuery(document).ready(function($) {
			$('.shahi-theme-switch-btn').on('click', function() {
				const theme = $(this).data('theme');
				const $btn = $(this);
				
				$btn.prop('disabled', true).text('<?php esc_html_e( 'Switching...', 'shahi-legalflowsuite' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'shahi_switch_theme',
						theme: theme,
						nonce: '<?php echo esc_js( $theme_nonce ); ?>'
					},
					success: function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert(response.data.message);
							$btn.prop('disabled', false).text('<?php esc_html_e( 'Activate', 'shahi-legalflowsuite' ); ?>');
						}
					},
					error: function() {
						alert('<?php esc_html_e( 'Failed to switch theme', 'shahi-legalflowsuite' ); ?>');
						$btn.prop('disabled', false).text('<?php esc_html_e( 'Activate', 'shahi-legalflowsuite' ); ?>');
					}
				});
			});
		});
		</script>
		
		<style>
		.shahi-theme-selector {
			background: var(--shahi-bg-secondary);
			padding: 25px;
			border-radius: 12px;
			margin: 20px 0;
		}
		
		.shahi-theme-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 20px;
			margin-top: 20px;
		}
		
		.shahi-theme-option {
			background: var(--shahi-bg-tertiary);
			border: 2px solid var(--shahi-border-color);
			border-radius: 12px;
			padding: 20px;
			text-align: center;
			transition: all 0.3s;
			position: relative;
		}
		
		.shahi-theme-option:hover {
			border-color: var(--shahi-accent-primary);
			transform: translateY(-3px);
			box-shadow: 0 8px 20px rgba(0, 212, 255, 0.2);
		}
		
		.shahi-theme-option.active {
			border-color: var(--shahi-accent-success);
			background: var(--shahi-bg-elevated);
		}
		
		.shahi-theme-preview {
			display: flex;
			gap: 8px;
			justify-content: center;
			margin-bottom: 15px;
		}
		
		.shahi-color-swatch {
			width: 50px;
			height: 50px;
			border-radius: 8px;
			border: 2px solid rgba(255, 255, 255, 0.1);
			transition: transform 0.2s;
		}
		
		.shahi-color-swatch:hover {
			transform: scale(1.1);
		}
		
		.shahi-theme-option h4 {
			color: var(--shahi-text-primary);
			margin: 10px 0;
			font-size: 18px;
		}
		
		.shahi-theme-option p {
			color: var(--shahi-text-secondary);
			font-size: 13px;
			margin-bottom: 15px;
		}
		
		.shahi-theme-active-badge {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			background: var(--shahi-accent-success);
			color: #ffffff;
			padding: 8px 16px;
			border-radius: 20px;
			font-size: 13px;
			font-weight: 600;
		}
		
		.shahi-theme-switch-btn {
			background: var(--shahi-accent-primary) !important;
			color: #0a0a12 !important;
			border: none !important;
			padding: 8px 20px !important;
			border-radius: 6px !important;
			font-weight: 600 !important;
			cursor: pointer !important;
			transition: all 0.3s !important;
		}
		
		.shahi-theme-switch-btn:hover {
			opacity: 0.9;
			transform: scale(1.05);
		}
		</style>
		<?php
	}
}

