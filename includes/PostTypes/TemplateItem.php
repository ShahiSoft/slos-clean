<?php
/**
 * Template Item Post Type
 *
 * Example custom post type implementation for template items.
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
 * Class TemplateItem
 *
 * Registers and manages the Template Item custom post type.
 *
 * @since 1.0.0
 */
class TemplateItem {

	/**
	 * Post type key
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $post_type = 'slos_template_item';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Constructor intentionally empty - registration happens via PostTypeManager
	}

	/**
	 * Get post type key
	 *
	 * @since 1.0.0
	 * @return string Post type key.
	 */
	public function get_post_type_key() {
		return $this->post_type;
	}

	/**
	 * Register the post type
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Template Items', 'Post Type General Name', 'shahi-legalflowsuite' ),
			'singular_name'         => _x( 'Template Item', 'Post Type Singular Name', 'shahi-legalflowsuite' ),
			'menu_name'             => __( 'Template Items', 'shahi-legalflowsuite' ),
			'name_admin_bar'        => __( 'Template Item', 'shahi-legalflowsuite' ),
			'archives'              => __( 'Item Archives', 'shahi-legalflowsuite' ),
			'attributes'            => __( 'Item Attributes', 'shahi-legalflowsuite' ),
			'parent_item_colon'     => __( 'Parent Item:', 'shahi-legalflowsuite' ),
			'all_items'             => __( 'All Items', 'shahi-legalflowsuite' ),
			'add_new_item'          => __( 'Add New Item', 'shahi-legalflowsuite' ),
			'add_new'               => __( 'Add New', 'shahi-legalflowsuite' ),
			'new_item'              => __( 'New Item', 'shahi-legalflowsuite' ),
			'edit_item'             => __( 'Edit Item', 'shahi-legalflowsuite' ),
			'update_item'           => __( 'Update Item', 'shahi-legalflowsuite' ),
			'view_item'             => __( 'View Item', 'shahi-legalflowsuite' ),
			'view_items'            => __( 'View Items', 'shahi-legalflowsuite' ),
			'search_items'          => __( 'Search Item', 'shahi-legalflowsuite' ),
			'not_found'             => __( 'Not found', 'shahi-legalflowsuite' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'shahi-legalflowsuite' ),
			'featured_image'        => __( 'Featured Image', 'shahi-legalflowsuite' ),
			'set_featured_image'    => __( 'Set featured image', 'shahi-legalflowsuite' ),
			'remove_featured_image' => __( 'Remove featured image', 'shahi-legalflowsuite' ),
			'use_featured_image'    => __( 'Use as featured image', 'shahi-legalflowsuite' ),
			'insert_into_item'      => __( 'Insert into item', 'shahi-legalflowsuite' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'shahi-legalflowsuite' ),
			'items_list'            => __( 'Items list', 'shahi-legalflowsuite' ),
			'items_list_navigation' => __( 'Items list navigation', 'shahi-legalflowsuite' ),
			'filter_items_list'     => __( 'Filter items list', 'shahi-legalflowsuite' ),
		);

		$args = array(
			'label'                 => __( 'Template Item', 'shahi-legalflowsuite' ),
			'description'           => __( 'Template items for ShahiLegalFlowSuite plugin', 'shahi-legalflowsuite' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => false, // Hidden from admin menu to keep clean UI
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-layout',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'template-items',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'               => array(
				'slug'       => 'template-items',
				'with_front' => false,
				'pages'      => true,
				'feeds'      => true,
			),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Register custom taxonomies
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_taxonomies() {
		// Register Category taxonomy
		$category_labels = array(
			'name'                       => _x( 'Item Categories', 'Taxonomy General Name', 'shahi-legalflowsuite' ),
			'singular_name'              => _x( 'Item Category', 'Taxonomy Singular Name', 'shahi-legalflowsuite' ),
			'menu_name'                  => __( 'Categories', 'shahi-legalflowsuite' ),
			'all_items'                  => __( 'All Categories', 'shahi-legalflowsuite' ),
			'parent_item'                => __( 'Parent Category', 'shahi-legalflowsuite' ),
			'parent_item_colon'          => __( 'Parent Category:', 'shahi-legalflowsuite' ),
			'new_item_name'              => __( 'New Category Name', 'shahi-legalflowsuite' ),
			'add_new_item'               => __( 'Add New Category', 'shahi-legalflowsuite' ),
			'edit_item'                  => __( 'Edit Category', 'shahi-legalflowsuite' ),
			'update_item'                => __( 'Update Category', 'shahi-legalflowsuite' ),
			'view_item'                  => __( 'View Category', 'shahi-legalflowsuite' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'shahi-legalflowsuite' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'shahi-legalflowsuite' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'shahi-legalflowsuite' ),
			'popular_items'              => __( 'Popular Categories', 'shahi-legalflowsuite' ),
			'search_items'               => __( 'Search Categories', 'shahi-legalflowsuite' ),
			'not_found'                  => __( 'Not Found', 'shahi-legalflowsuite' ),
			'no_terms'                   => __( 'No categories', 'shahi-legalflowsuite' ),
			'items_list'                 => __( 'Categories list', 'shahi-legalflowsuite' ),
			'items_list_navigation'      => __( 'Categories list navigation', 'shahi-legalflowsuite' ),
		);

		$category_args = array(
			'labels'                => $category_labels,
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_nav_menus'     => true,
			'show_tagcloud'         => true,
			'show_in_rest'          => true,
			'rest_base'             => 'item-categories',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'rewrite'               => array(
				'slug'       => 'item-category',
				'with_front' => false,
			),
		);

		register_taxonomy( 'shahi_item_category', array( $this->post_type ), $category_args );

		// Register Tag taxonomy
		$tag_labels = array(
			'name'                       => _x( 'Item Tags', 'Taxonomy General Name', 'shahi-legalflowsuite' ),
			'singular_name'              => _x( 'Item Tag', 'Taxonomy Singular Name', 'shahi-legalflowsuite' ),
			'menu_name'                  => __( 'Tags', 'shahi-legalflowsuite' ),
			'all_items'                  => __( 'All Tags', 'shahi-legalflowsuite' ),
			'new_item_name'              => __( 'New Tag Name', 'shahi-legalflowsuite' ),
			'add_new_item'               => __( 'Add New Tag', 'shahi-legalflowsuite' ),
			'edit_item'                  => __( 'Edit Tag', 'shahi-legalflowsuite' ),
			'update_item'                => __( 'Update Tag', 'shahi-legalflowsuite' ),
			'view_item'                  => __( 'View Tag', 'shahi-legalflowsuite' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'shahi-legalflowsuite' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'shahi-legalflowsuite' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'shahi-legalflowsuite' ),
			'popular_items'              => __( 'Popular Tags', 'shahi-legalflowsuite' ),
			'search_items'               => __( 'Search Tags', 'shahi-legalflowsuite' ),
			'not_found'                  => __( 'Not Found', 'shahi-legalflowsuite' ),
			'no_terms'                   => __( 'No tags', 'shahi-legalflowsuite' ),
			'items_list'                 => __( 'Tags list', 'shahi-legalflowsuite' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'shahi-legalflowsuite' ),
		);

		$tag_args = array(
			'labels'                => $tag_labels,
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_nav_menus'     => true,
			'show_tagcloud'         => true,
			'show_in_rest'          => true,
			'rest_base'             => 'item-tags',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'rewrite'               => array(
				'slug'       => 'item-tag',
				'with_front' => false,
			),
		);

		register_taxonomy( 'shahi_item_tag', array( $this->post_type ), $tag_args );
	}

	/**
	 * Get custom admin columns
	 *
	 * @since 1.0.0
	 * @return array Custom columns.
	 */
	public function get_admin_columns() {
		return array(
			'featured'     => __( 'Featured', 'shahi-legalflowsuite' ),
			'status_badge' => __( 'Status', 'shahi-legalflowsuite' ),
			'item_type'    => __( 'Type', 'shahi-legalflowsuite' ),
			'views'        => __( 'Views', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Render admin column content
	 *
	 * @since 1.0.0
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_admin_column( $column, $post_id ) {
		switch ( $column ) {
			case 'featured':
				$is_featured = get_post_meta( $post_id, '_shahi_featured', true );
				if ( $is_featured ) {
					echo '<span class="dashicons dashicons-star-filled" style="color: #f0b429;" title="' . esc_attr__( 'Featured', 'shahi-legalflowsuite' ) . '"></span>';
				} else {
					echo '<span class="dashicons dashicons-star-empty" style="color: #ccc;" title="' . esc_attr__( 'Not Featured', 'shahi-legalflowsuite' ) . '"></span>';
				}
				break;

			case 'status_badge':
				$status       = get_post_meta( $post_id, '_shahi_status', true );
				$status       = $status ? $status : 'active';
				$badge_colors = array(
					'active'   => '#46b450',
					'inactive' => '#dc3232',
					'pending'  => '#ffb900',
				);
				$color        = isset( $badge_colors[ $status ] ) ? $badge_colors[ $status ] : '#666';
				echo '<span style="display:inline-block;padding:3px 8px;background:' . esc_attr( $color ) . ';color:#fff;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">' . esc_html( $status ) . '</span>';
				break;

			case 'item_type':
				$type = get_post_meta( $post_id, '_shahi_item_type', true );
				echo $type ? esc_html( $type ) : '<span style="color:#999;">—</span>';
				break;

			case 'views':
				$views = get_post_meta( $post_id, '_shahi_views', true );
				echo $views ? esc_html( number_format( $views ) ) : '0';
				break;
		}
	}

	/**
	 * Get sortable columns
	 *
	 * @since 1.0.0
	 * @return array Sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'views'        => 'views',
			'status_badge' => 'status',
		);
	}

	/**
	 * Render quick edit fields
	 *
	 * @since 1.0.0
	 * @param string $column_name Column name.
	 * @return void
	 */
	public function render_quick_edit( $column_name ) {
		// Only show once
		static $printed = false;
		if ( $printed ) {
			return;
		}
		$printed = true;

		?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Featured', 'shahi-legalflowsuite' ); ?></span>
					<select name="shahi_featured">
						<option value="">— <?php esc_html_e( 'No Change', 'shahi-legalflowsuite' ); ?> —</option>
						<option value="1"><?php esc_html_e( 'Yes', 'shahi-legalflowsuite' ); ?></option>
						<option value="0"><?php esc_html_e( 'No', 'shahi-legalflowsuite' ); ?></option>
					</select>
				</label>
				
				<label>
					<span class="title"><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></span>
					<select name="shahi_status">
						<option value="">— <?php esc_html_e( 'No Change', 'shahi-legalflowsuite' ); ?> —</option>
						<option value="active"><?php esc_html_e( 'Active', 'shahi-legalflowsuite' ); ?></option>
						<option value="inactive"><?php esc_html_e( 'Inactive', 'shahi-legalflowsuite' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'shahi-legalflowsuite' ); ?></option>
					</select>
				</label>
				
				<label>
					<span class="title"><?php esc_html_e( 'Item Type', 'shahi-legalflowsuite' ); ?></span>
					<input type="text" name="shahi_item_type" value="" placeholder="<?php esc_attr_e( 'Enter type...', 'shahi-legalflowsuite' ); ?>">
				</label>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Save quick edit data
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_quick_edit( $post_id ) {
		// Featured
		if ( isset( $_POST['shahi_featured'] ) && $_POST['shahi_featured'] !== '' ) {
			$featured = $_POST['shahi_featured'] === '1' ? '1' : '';
			if ( $featured ) {
				update_post_meta( $post_id, '_shahi_featured', $featured );
			} else {
				delete_post_meta( $post_id, '_shahi_featured' );
			}
		}

		// Status
		if ( isset( $_POST['shahi_status'] ) && $_POST['shahi_status'] !== '' ) {
			$status = sanitize_text_field( $_POST['shahi_status'] );
			update_post_meta( $post_id, '_shahi_status', $status );
		}

		// Item Type
		if ( isset( $_POST['shahi_item_type'] ) ) {
			$type = sanitize_text_field( $_POST['shahi_item_type'] );
			if ( $type ) {
				update_post_meta( $post_id, '_shahi_item_type', $type );
			}
		}
	}
}

