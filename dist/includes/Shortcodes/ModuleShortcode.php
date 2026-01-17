<?php
/**
 * Module Shortcode
 *
 * Displays module information via [shahi_module] shortcode.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Shortcodes
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Shortcodes;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleShortcode
 *
 * Handles [shahi_module] shortcode rendering.
 *
 * @since 1.0.0
 */
class ModuleShortcode {

	/**
	 * Shortcode tag
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $tag = 'shahi_module';

	/**
	 * Register shortcode
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		add_shortcode( $this->tag, array( $this, 'render' ) );
	}

	/**
	 * Render shortcode
	 *
	 * Usage:
	 * [shahi_module name="dashboard"]
	 * [shahi_module name="dashboard" display="inline"]
	 * [shahi_module name="user-management" show_description="yes"]
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function render( $atts ) {
		// Parse attributes...
		$atts = shortcode_atts(
			array(
				'name'             => '',        // Module ID/name (required).
				'display'          => 'card',    // card, inline, badge.
				'show_status'      => 'yes',     // yes, no.
				'show_description' => 'no',      // yes, no.
				'show_link'        => 'no',      // yes, no.
			),
			$atts,
			$this->tag
		);

		// Sanitize attributes...
		$module_name      = sanitize_key( $atts['name'] );
		$display          = sanitize_key( $atts['display'] );
		$show_status      = sanitize_key( $atts['show_status'] ) === 'yes';
		$show_description = sanitize_key( $atts['show_description'] ) === 'yes';
		$show_link        = sanitize_key( $atts['show_link'] ) === 'yes';

		// Validate module name...
		if ( empty( $module_name ) ) {
			return '<p class="shahi-error">' . esc_html__( 'Module name is required.', 'shahi-legalflowsuite' ) . '</p>';
		}

		// Get module data...
		$module = $this->get_module_data( $module_name );

		if ( ! $module ) {
			return '<p class="shahi-error">' . sprintf(
				/* translators: %s: module name */
				esc_html__( 'Module "%s" not found.', 'shahi-legalflowsuite' ),
				esc_html( $module_name )
			) . '</p>';
		}

		// Build CSS classes...
		$classes = array(
			'shahi-shortcode',
			'shahi-module-shortcode',
			'display-' . sanitize_html_class( $display ),
			'module-' . sanitize_html_class( $module_name ),
		);

		// Build output...
		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<div class="shahi-module-header">
				<span class="shahi-module-name"><?php echo esc_html( $module['title'] ); ?></span>
				<?php if ( $show_status ) : ?>
					<span class="shahi-module-status <?php echo esc_attr( $module['enabled'] ? 'enabled' : 'disabled' ); ?>">
						<?php echo esc_html( $module['enabled'] ? __( 'Enabled', 'shahi-legalflowsuite' ) : __( 'Disabled', 'shahi-legalflowsuite' ) ); ?>
					</span>
				<?php endif; ?>
			</div>
			
			<?php if ( $show_description && ! empty( $module['description'] ) ) : ?>
				<div class="shahi-module-description">
					<?php echo wp_kses_post( $module['description'] ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_link ) : ?>
				<div class="shahi-module-link">
					<a href="<?php echo esc_url( $module['link'] ); ?>" class="shahi-module-link-button">
						<?php esc_html_e( 'Configure Module', 'shahi-legalflowsuite' ); ?> &rarr;
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get module data
	 *
	 * @since 1.0.0
	 * @param string $module_name Module ID/name.
	 * @return array|false Module data or false if not found.
	 */
	private function get_module_data( $module_name ) {
		// Get all modules...
		$modules = get_option( 'shahi_modules', array() );

		if ( ! is_array( $modules ) ) {
			$modules = array();
		}

		// Check if module exists...
		if ( isset( $modules[ $module_name ] ) ) {
			$module = $modules[ $module_name ];

			// Add link to module settings...
			$module['link'] = admin_url( 'admin.php?page=shahi-modules&module=' . $module_name );

			return $module;
		}

		// Module not found...
		return false;
	}

	/**
	 * Get all available modules
	 *
	 * @since 1.0.0
	 * @return array All modules.
	 */
	public function get_all_modules() {
		$modules = get_option( 'shahi_modules', array() );

		if ( ! is_array( $modules ) ) {
			return array();
		}

		return $modules;
	}
}

