<?php
/**
 * Quick Actions Widget
 *
 * Displays quick action links in a widget.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Widgets
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\Widgets;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class QuickActionsWidget
 *
 * Widget for displaying quick action links.
 *
 * @since 1.0.0
 */
class QuickActionsWidget extends \WP_Widget {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			'shahi_quick_actions_widget',
			__( 'ShahiLegalopsSuite Quick Actions', 'shahi-legalops-suite' ),
			array(
				'description' => __( 'Display quick action links', 'shahi-legalops-suite' ),
				'classname'   => 'shahi-quick-actions-widget',
			)
		);
	}

	/**
	 * Front-end display of widget
	 *
	 * @since 1.0.0
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Quick Actions', 'shahi-legalops-suite' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$show_dashboard = isset( $instance['show_dashboard'] ) ? (bool) $instance['show_dashboard'] : true;
		$show_settings  = isset( $instance['show_settings'] ) ? (bool) $instance['show_settings'] : true;
		$show_modules   = isset( $instance['show_modules'] ) ? (bool) $instance['show_modules'] : true;
		$show_add_item  = isset( $instance['show_add_item'] ) ? (bool) $instance['show_add_item'] : false;

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		echo '<div class="shahi-widget shahi-quick-actions-widget-content">';
		echo '<ul class="shahi-actions-list">';

		// Dashboard link
		if ( $show_dashboard ) {
			$dashboard_url = admin_url( 'admin.php?page=shahi-legalops-suite' );
			echo '<li>';
			echo '<a href="' . esc_url( $dashboard_url ) . '" class="shahi-action-button">';
			echo '<span class="dashicons dashicons-dashboard"></span> ';
			echo esc_html__( 'Dashboard', 'shahi-legalops-suite' );
			echo '</a>';
			echo '</li>';
		}

		// Settings link
		if ( $show_settings ) {
			$settings_url = admin_url( 'admin.php?page=shahi-legalops-suite-settings' );
			echo '<li>';
			echo '<a href="' . esc_url( $settings_url ) . '" class="shahi-action-button">';
			echo '<span class="dashicons dashicons-admin-settings"></span> ';
			echo esc_html__( 'Settings', 'shahi-legalops-suite' );
			echo '</a>';
			echo '</li>';
		}

		// Modules link
		if ( $show_modules ) {
			$modules_url = admin_url( 'admin.php?page=shahi-legalops-suite-modules' );
			echo '<li>';
			echo '<a href="' . esc_url( $modules_url ) . '" class="shahi-action-button">';
			echo '<span class="dashicons dashicons-admin-plugins"></span> ';
			echo esc_html__( 'Modules', 'shahi-legalops-suite' );
			echo '</a>';
			echo '</li>';
		}

		// Add template item link
		if ( $show_add_item ) {
			$add_item_url = admin_url( 'post-new.php?post_type=shahi_legalops_suite_item' );
			echo '<li>';
			echo '<a href="' . esc_url( $add_item_url ) . '" class="shahi-action-button">';
			echo '<span class="dashicons dashicons-plus-alt"></span> ';
			echo esc_html__( 'Add Template Item', 'shahi-legalops-suite' );
			echo '</a>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form
	 *
	 * @since 1.0.0
	 * @param array $instance Previously saved values from database.
	 * @return void
	 */
	public function form( $instance ) {
		$title          = isset( $instance['title'] ) ? $instance['title'] : __( 'Quick Actions', 'shahi-legalops-suite' );
		$show_dashboard = isset( $instance['show_dashboard'] ) ? (bool) $instance['show_dashboard'] : true;
		$show_settings  = isset( $instance['show_settings'] ) ? (bool) $instance['show_settings'] : true;
		$show_modules   = isset( $instance['show_modules'] ) ? (bool) $instance['show_modules'] : true;
		$show_add_item  = isset( $instance['show_add_item'] ) ? (bool) $instance['show_add_item'] : false;
		?>
		
		<div class="shahi-widget-field">
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'shahi-legalops-suite' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" 
					value="<?php echo esc_attr( $title ); ?>">
		</div>
		
		<div class="shahi-widget-field">
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_dashboard' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'show_dashboard' ) ); ?>" 
						value="1" <?php checked( $show_dashboard, true ); ?>>
				<?php esc_html_e( 'Show Dashboard Link', 'shahi-legalops-suite' ); ?>
			</label>
		</div>
		
		<div class="shahi-widget-field">
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_settings' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'show_settings' ) ); ?>" 
						value="1" <?php checked( $show_settings, true ); ?>>
				<?php esc_html_e( 'Show Settings Link', 'shahi-legalops-suite' ); ?>
			</label>
		</div>
		
		<div class="shahi-widget-field">
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_modules' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'show_modules' ) ); ?>" 
						value="1" <?php checked( $show_modules, true ); ?>>
				<?php esc_html_e( 'Show Modules Link', 'shahi-legalops-suite' ); ?>
			</label>
		</div>
		
		<div class="shahi-widget-field">
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_add_item' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'show_add_item' ) ); ?>" 
						value="1" <?php checked( $show_add_item, true ); ?>>
				<?php esc_html_e( 'Show Add Template Item Link', 'shahi-legalops-suite' ); ?>
			</label>
		</div>
		
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved
	 *
	 * @since 1.0.0
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ?
			sanitize_text_field( $new_instance['title'] ) : '';

		$instance['show_dashboard'] = isset( $new_instance['show_dashboard'] ) ?
			(bool) $new_instance['show_dashboard'] : false;

		$instance['show_settings'] = isset( $new_instance['show_settings'] ) ?
			(bool) $new_instance['show_settings'] : false;

		$instance['show_modules'] = isset( $new_instance['show_modules'] ) ?
			(bool) $new_instance['show_modules'] : false;

		$instance['show_add_item'] = isset( $new_instance['show_add_item'] ) ?
			(bool) $new_instance['show_add_item'] : false;

		return $instance;
	}
}

