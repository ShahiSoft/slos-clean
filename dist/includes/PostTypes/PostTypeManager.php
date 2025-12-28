<?php
/**
 * Post Type Manager
 *
 * Central manager for registering and managing custom post types.
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
 * Class PostTypeManager
 *
 * Manages registration and coordination of custom post types.
 *
 * @since 1.0.0
 */
class PostTypeManager {

	/**
	 * Registered post types
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $post_types = array();

	/**
	 * Metaboxes instance
	 *
	 * @since 1.0.0
	 * @var Metaboxes
	 */
	private $metaboxes;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->metaboxes = new Metaboxes();
		$this->init_post_types();
		$this->register_hooks();
	}

	/**
	 * Initialize post types
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_post_types() {
		// Initialize Template Item post type
		$this->post_types['template_item'] = new TemplateItem();
	}

	/**
	 * Register WordPress hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_hooks() {
		// Register post types
		add_action( 'init', array( $this, 'register_post_types' ) );

		// Register taxonomies
		add_action( 'init', array( $this, 'register_taxonomies' ) );

		// Admin columns
		foreach ( $this->post_types as $post_type ) {
			if ( method_exists( $post_type, 'get_post_type_key' ) ) {
				$post_type_key = $post_type->get_post_type_key();

				add_filter( "manage_{$post_type_key}_posts_columns", array( $this, 'add_admin_columns' ), 10, 1 );
				add_action( "manage_{$post_type_key}_posts_custom_column", array( $this, 'render_admin_columns' ), 10, 2 );
				add_filter( "manage_edit-{$post_type_key}_sortable_columns", array( $this, 'sortable_columns' ) );
			}
		}

		// Quick edit support
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_fields' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_quick_edit' ), 10, 2 );

		// Bulk actions
		add_filter( 'bulk_actions-edit-shahi_legalflowsuite_item', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-shahi_legalflowsuite_item', array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'bulk_action_notices' ) );
	}

	/**
	 * Register all post types
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_post_types() {
		foreach ( $this->post_types as $post_type ) {
			if ( method_exists( $post_type, 'register' ) ) {
				$post_type->register();
			}
		}
	}

	/**
	 * Register all taxonomies
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_taxonomies() {
		foreach ( $this->post_types as $post_type ) {
			if ( method_exists( $post_type, 'register_taxonomies' ) ) {
				$post_type->register_taxonomies();
			}
		}
	}

	/**
	 * Add custom admin columns
	 *
	 * @since 1.0.0
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( $columns ) {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return $columns;
		}

		// Get the post type object
		$post_type_key = $screen->post_type;
		$post_type     = $this->get_post_type_by_key( $post_type_key );

		if ( $post_type && method_exists( $post_type, 'get_admin_columns' ) ) {
			$custom_columns = $post_type->get_admin_columns();

			// Insert custom columns before date
			$new_columns = array();
			foreach ( $columns as $key => $label ) {
				if ( $key === 'date' ) {
					$new_columns = array_merge( $new_columns, $custom_columns );
				}
				$new_columns[ $key ] = $label;
			}

			return $new_columns;
		}

		return $columns;
	}

	/**
	 * Render custom admin column content
	 *
	 * @since 1.0.0
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_admin_columns( $column, $post_id ) {
		$post_type_key = get_post_type( $post_id );
		$post_type     = $this->get_post_type_by_key( $post_type_key );

		if ( $post_type && method_exists( $post_type, 'render_admin_column' ) ) {
			$post_type->render_admin_column( $column, $post_id );
		}
	}

	/**
	 * Make columns sortable
	 *
	 * @since 1.0.0
	 * @param array $columns Sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function sortable_columns( $columns ) {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return $columns;
		}

		$post_type_key = $screen->post_type;
		$post_type     = $this->get_post_type_by_key( $post_type_key );

		if ( $post_type && method_exists( $post_type, 'get_sortable_columns' ) ) {
			$sortable = $post_type->get_sortable_columns();
			return array_merge( $columns, $sortable );
		}

		return $columns;
	}

	/**
	 * Add quick edit fields
	 *
	 * @since 1.0.0
	 * @param string $column_name Column name.
	 * @param string $post_type   Post type.
	 * @return void
	 */
	public function quick_edit_fields( $column_name, $post_type ) {
		$post_type_obj = $this->get_post_type_by_key( $post_type );

		if ( $post_type_obj && method_exists( $post_type_obj, 'render_quick_edit' ) ) {
			$post_type_obj->render_quick_edit( $column_name );
		}
	}

	/**
	 * Save quick edit data
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 * @return void
	 */
	public function save_quick_edit( $post_id, $post ) {
		// Skip autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$post_type_obj = $this->get_post_type_by_key( $post->post_type );

		if ( $post_type_obj && method_exists( $post_type_obj, 'save_quick_edit' ) ) {
			$post_type_obj->save_quick_edit( $post_id );
		}
	}

	/**
	 * Register bulk actions
	 *
	 * @since 1.0.0
	 * @param array $bulk_actions Existing bulk actions.
	 * @return array Modified bulk actions.
	 */
	public function register_bulk_actions( $bulk_actions ) {
		$bulk_actions['mark_featured']   = __( 'Mark as Featured', 'shahi-legalflowsuite' );
		$bulk_actions['unmark_featured'] = __( 'Remove Featured', 'shahi-legalflowsuite' );
		$bulk_actions['duplicate']       = __( 'Duplicate', 'shahi-legalflowsuite' );

		return $bulk_actions;
	}

	/**
	 * Handle bulk actions
	 *
	 * @since 1.0.0
	 * @param string $redirect_to Redirect URL.
	 * @param string $action      Bulk action being taken.
	 * @param array  $post_ids    Array of post IDs.
	 * @return string Modified redirect URL.
	 */
	public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( ! in_array( $action, array( 'mark_featured', 'unmark_featured', 'duplicate' ) ) ) {
			return $redirect_to;
		}

		$count = 0;

		foreach ( $post_ids as $post_id ) {
			switch ( $action ) {
				case 'mark_featured':
					update_post_meta( $post_id, '_shahi_featured', '1' );
					++$count;
					break;

				case 'unmark_featured':
					delete_post_meta( $post_id, '_shahi_featured' );
					++$count;
					break;

				case 'duplicate':
					$this->duplicate_post( $post_id );
					++$count;
					break;
			}
		}

		$redirect_to = add_query_arg(
			array(
				'bulk_action' => $action,
				'bulk_count'  => $count,
			),
			$redirect_to
		);

		return $redirect_to;
	}

	/**
	 * Display bulk action notices
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bulk_action_notices() {
		if ( ! isset( $_REQUEST['bulk_action'] ) || ! isset( $_REQUEST['bulk_count'] ) ) {
			return;
		}

		$action = sanitize_key( $_REQUEST['bulk_action'] );
		$count  = intval( $_REQUEST['bulk_count'] );

		$messages = array(
			'mark_featured'   => sprintf( _n( '%d item marked as featured.', '%d items marked as featured.', $count, 'shahi-legalflowsuite' ), $count ),
			'unmark_featured' => sprintf( _n( '%d item unmarked as featured.', '%d items unmarked as featured.', $count, 'shahi-legalflowsuite' ), $count ),
			'duplicate'       => sprintf( _n( '%d item duplicated.', '%d items duplicated.', $count, 'shahi-legalflowsuite' ), $count ),
		);

		if ( isset( $messages[ $action ] ) ) {
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $messages[ $action ] ) );
		}
	}

	/**
	 * Duplicate a post
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID to duplicate.
	 * @return int|WP_Error New post ID or error.
	 */
	private function duplicate_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'invalid_post', 'Invalid post ID' );
		}

		// Create new post
		$new_post = array(
			'post_title'   => $post->post_title . ' (Copy)',
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_status'  => 'draft',
			'post_type'    => $post->post_type,
			'post_author'  => get_current_user_id(),
		);

		$new_post_id = wp_insert_post( $new_post );

		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}

		// Copy post meta
		$post_meta = get_post_meta( $post_id );
		foreach ( $post_meta as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
			}
		}

		// Copy taxonomies
		$taxonomies = get_object_taxonomies( $post->post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			wp_set_object_terms( $new_post_id, $terms, $taxonomy );
		}

		return $new_post_id;
	}

	/**
	 * Get post type object by key
	 *
	 * @since 1.0.0
	 * @param string $post_type_key Post type key.
	 * @return object|null Post type object or null.
	 */
	private function get_post_type_by_key( $post_type_key ) {
		foreach ( $this->post_types as $post_type ) {
			if ( method_exists( $post_type, 'get_post_type_key' ) ) {
				if ( $post_type->get_post_type_key() === $post_type_key ) {
					return $post_type;
				}
			}
		}

		return null;
	}

	/**
	 * Get registered post types
	 *
	 * @since 1.0.0
	 * @return array Registered post types.
	 */
	public function get_post_types() {
		return $this->post_types;
	}
}

