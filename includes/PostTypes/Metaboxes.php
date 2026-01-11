<?php
/**
 * Metaboxes Framework
 *
 * Centralized metabox management for custom post types.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  PostTypes
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\PostTypes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Metaboxes
 *
 * Framework for registering and rendering metaboxes.
 *
 * @since 1.0.0
 */
class Metaboxes {

	/**
	 * Registered metaboxes
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $metaboxes = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->register_hooks();
		$this->init_metaboxes();
	}

	/**
	 * Register WordPress hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_metaboxes' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_metabox_assets' ) );
	}

	/**
	 * Initialize metaboxes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_metaboxes() {
		// Template Item Details metabox
		$this->metaboxes['template_item_details'] = array(
			'id'        => 'shahi_item_details',
			'title'     => __( 'Item Details', 'shahi-legalflowsuite' ),
			'post_type' => 'shahi_legalflowsuite_item',
			'context'   => 'normal',
			'priority'  => 'high',
			'callback'  => array( $this, 'render_item_details_metabox' ),
			'fields'    => array(
				'item_type' => array(
					'label'       => __( 'Item Type', 'shahi-legalflowsuite' ),
					'type'        => 'select',
					'options'     => array(
						'standard' => __( 'Standard', 'shahi-legalflowsuite' ),
						'premium'  => __( 'Premium', 'shahi-legalflowsuite' ),
						'featured' => __( 'Featured', 'shahi-legalflowsuite' ),
					),
					'description' => __( 'Select the type of item', 'shahi-legalflowsuite' ),
				),
				'status'    => array(
					'label'       => __( 'Status', 'shahi-legalflowsuite' ),
					'type'        => 'select',
					'options'     => array(
						'active'   => __( 'Active', 'shahi-legalflowsuite' ),
						'inactive' => __( 'Inactive', 'shahi-legalflowsuite' ),
						'pending'  => __( 'Pending', 'shahi-legalflowsuite' ),
					),
					'description' => __( 'Current status of the item', 'shahi-legalflowsuite' ),
				),
				'featured'  => array(
					'label'       => __( 'Featured Item', 'shahi-legalflowsuite' ),
					'type'        => 'checkbox',
					'description' => __( 'Mark this item as featured', 'shahi-legalflowsuite' ),
				),
				'views'     => array(
					'label'       => __( 'View Count', 'shahi-legalflowsuite' ),
					'type'        => 'number',
					'description' => __( 'Number of times this item has been viewed', 'shahi-legalflowsuite' ),
					'readonly'    => true,
				),
			),
		);

		// Additional Settings metabox
		$this->metaboxes['template_item_settings'] = array(
			'id'        => 'shahi_item_settings',
			'title'     => __( 'Additional Settings', 'shahi-legalflowsuite' ),
			'post_type' => 'shahi_legalflowsuite_item',
			'context'   => 'side',
			'priority'  => 'default',
			'callback'  => array( $this, 'render_item_settings_metabox' ),
			'fields'    => array(
				'enable_comments' => array(
					'label'       => __( 'Enable Comments', 'shahi-legalflowsuite' ),
					'type'        => 'checkbox',
					'description' => __( 'Allow comments on this item', 'shahi-legalflowsuite' ),
				),
				'enable_sharing'  => array(
					'label'       => __( 'Enable Sharing', 'shahi-legalflowsuite' ),
					'type'        => 'checkbox',
					'description' => __( 'Show social sharing buttons', 'shahi-legalflowsuite' ),
				),
				'custom_css'      => array(
					'label'       => __( 'Custom CSS Class', 'shahi-legalflowsuite' ),
					'type'        => 'text',
					'description' => __( 'Add custom CSS class to this item', 'shahi-legalflowsuite' ),
				),
			),
		);
	}

	/**
	 * Add metaboxes to post edit screen
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_metaboxes() {
		foreach ( $this->metaboxes as $metabox ) {
			add_meta_box(
				$metabox['id'],
				$metabox['title'],
				$metabox['callback'],
				$metabox['post_type'],
				$metabox['context'],
				$metabox['priority'],
				$metabox
			);
		}
	}

	/**
	 * Render item details metabox
	 *
	 * @since 1.0.0
	 * @param object $post    Post object.
	 * @param array  $metabox Metabox configuration.
	 * @return void
	 */
	public function render_item_details_metabox( $post, $metabox ) {
		// Add nonce for security
		wp_nonce_field( 'shahi_item_details_nonce', 'shahi_item_details_nonce' );

		echo '<div class="shahi-metabox-wrapper">';

		foreach ( $metabox['args']['fields'] as $field_id => $field ) {
			$meta_key = '_shahi_' . $field_id;
			$value    = get_post_meta( $post->ID, $meta_key, true );

			echo '<div class="shahi-metabox-field" style="margin-bottom: 20px;">';
			echo '<label style="display:block;font-weight:600;margin-bottom:5px;">' . esc_html( $field['label'] ) . '</label>';

			$this->render_field( $field_id, $field, $value );

			if ( ! empty( $field['description'] ) ) {
				echo '<p class="description" style="margin-top:5px;">' . esc_html( $field['description'] ) . '</p>';
			}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render item settings metabox
	 *
	 * @since 1.0.0
	 * @param object $post    Post object.
	 * @param array  $metabox Metabox configuration.
	 * @return void
	 */
	public function render_item_settings_metabox( $post, $metabox ) {
		// Add nonce for security
		wp_nonce_field( 'shahi_item_settings_nonce', 'shahi_item_settings_nonce' );

		echo '<div class="shahi-metabox-wrapper">';

		foreach ( $metabox['args']['fields'] as $field_id => $field ) {
			$meta_key = '_shahi_' . $field_id;
			$value    = get_post_meta( $post->ID, $meta_key, true );

			echo '<div class="shahi-metabox-field" style="margin-bottom: 15px;">';

			$this->render_field( $field_id, $field, $value );

			if ( ! empty( $field['description'] ) ) {
				echo '<p class="description" style="margin-top:5px;font-size:12px;">' . esc_html( $field['description'] ) . '</p>';
			}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render individual field
	 *
	 * @since 1.0.0
	 * @param string $field_id Field ID.
	 * @param array  $field    Field configuration.
	 * @param mixed  $value    Current value.
	 * @return void
	 */
	private function render_field( $field_id, $field, $value ) {
		$name     = 'shahi_' . $field_id;
		$readonly = ! empty( $field['readonly'] ) ? 'readonly' : '';

		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="widefat" ' . $readonly . '>';
				break;

			case 'number':
				$value = $value ? intval( $value ) : 0;
				echo '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="small-text" ' . $readonly . '>';
				break;

			case 'textarea':
				echo '<textarea name="' . esc_attr( $name ) . '" rows="4" class="widefat" ' . $readonly . '>' . esc_textarea( $value ) . '</textarea>';
				break;

			case 'select':
				echo '<select name="' . esc_attr( $name ) . '" class="widefat">';
				foreach ( $field['options'] as $option_value => $option_label ) {
					$selected = selected( $value, $option_value, false );
					echo '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . esc_html( $option_label ) . '</option>';
				}
				echo '</select>';
				break;

			case 'checkbox':
				$checked = checked( $value, '1', false );
				echo '<label style="display:inline-block;">';
				echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . $checked . '> ';
				echo esc_html( $field['label'] );
				echo '</label>';
				break;

			case 'radio':
				foreach ( $field['options'] as $option_value => $option_label ) {
					$checked = checked( $value, $option_value, false );
					echo '<label style="display:block;margin-bottom:5px;">';
					echo '<input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $option_value ) . '" ' . $checked . '> ';
					echo esc_html( $option_label );
					echo '</label>';
				}
				break;
		}
	}

	/**
	 * Save metabox data
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 * @return void
	 */
	public function save_metaboxes( $post_id, $post ) {
		// Skip autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type
		if ( $post->post_type !== 'shahi_legalflowsuite_item' ) {
			return;
		}

		// Save item details
		if ( isset( $_POST['shahi_item_details_nonce'] ) && wp_verify_nonce( $_POST['shahi_item_details_nonce'], 'shahi_item_details_nonce' ) ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			$fields = $this->metaboxes['template_item_details']['fields'];
			foreach ( $fields as $field_id => $field ) {
				$post_key = 'shahi_' . $field_id;
				$meta_key = '_shahi_' . $field_id;

				if ( isset( $_POST[ $post_key ] ) ) {
					// Sanitize based on field type
					switch ( $field['type'] ) {
						case 'number':
							$value = intval( wp_unslash( $_POST[ $post_key ] ) );
							break;
						case 'checkbox':
							$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) === '1' ? '1' : '';
							break;
						default:
							$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
					}

					update_post_meta( $post_id, $meta_key, $value );
				} elseif ( $field['type'] === 'checkbox' ) {
					// Unchecked checkboxes don't submit
					delete_post_meta( $post_id, $meta_key );
				}
			}
		}

		// Save item settings
		if ( isset( $_POST['shahi_item_settings_nonce'] ) && wp_verify_nonce( $_POST['shahi_item_settings_nonce'], 'shahi_item_settings_nonce' ) ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			$fields = $this->metaboxes['template_item_settings']['fields'];
			foreach ( $fields as $field_id => $field ) {
				$post_key = 'shahi_' . $field_id;
				$meta_key = '_shahi_' . $field_id;

				if ( isset( $_POST[ $post_key ] ) ) {
					// Sanitize based on field type
					switch ( $field['type'] ) {
						case 'number':
							$value = intval( wp_unslash( $_POST[ $post_key ] ) );
							break;
						case 'checkbox':
							$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) === '1' ? '1' : '';
							break;
						default:
							$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
					}

					update_post_meta( $post_id, $meta_key, $value );
				} elseif ( $field['type'] === 'checkbox' ) {
					// Unchecked checkboxes don't submit
					delete_post_meta( $post_id, $meta_key );
				}
			}
		}
	}

	/**
	 * Enqueue metabox assets
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_metabox_assets( $hook ) {
		// Only load on post edit pages
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		global $post;
		if ( ! $post || $post->post_type !== 'shahi_legalflowsuite_item' ) {
			return;
		}

		// Add inline CSS for metabox styling
		$css = "
        .shahi-metabox-wrapper {
            padding: 10px 0;
        }
        .shahi-metabox-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .shahi-metabox-field input[type='text'],
        .shahi-metabox-field input[type='number'],
        .shahi-metabox-field select,
        .shahi-metabox-field textarea {
            width: 100%;
            max-width: 100%;
        }
        .shahi-metabox-field .description {
            color: #666;
            font-size: 12px;
            font-style: italic;
            margin-top: 5px;
        }
        ";

		wp_add_inline_style( 'wp-admin', $css );
	}

	/**
	 * Register a custom metabox
	 *
	 * @since 1.0.0
	 * @param string $id        Metabox ID.
	 * @param array  $metabox   Metabox configuration.
	 * @return void
	 */
	public function register_metabox( $id, $metabox ) {
		$this->metaboxes[ $id ] = $metabox;
	}

	/**
	 * Get registered metaboxes
	 *
	 * @since 1.0.0
	 * @return array Registered metaboxes.
	 */
	public function get_metaboxes() {
		return $this->metaboxes;
	}
}
