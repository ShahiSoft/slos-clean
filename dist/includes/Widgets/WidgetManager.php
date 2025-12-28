<?php
/**
 * Widget Manager
 *
 * Central manager for registering and managing WordPress widgets.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Widgets
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Widgets;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WidgetManager
 *
 * Manages registration and coordination of WordPress widgets.
 *
 * @since 1.0.0
 */
class WidgetManager {

	/**
	 * Registered widgets
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $widgets = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init_widgets();
		$this->register_hooks();
	}

	/**
	 * Initialize widgets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_widgets() {
		// Register widget classes (keeping only QuickActionsWidget for legal operations dashboard)
		$this->widgets = array(
			'ShahiLegalFlowSuite\Widgets\QuickActionsWidget',
		);
	}

	/**
	 * Register WordPress hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_widget_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Register all widgets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_widgets() {
		foreach ( $this->widgets as $widget_class ) {
			if ( class_exists( $widget_class ) ) {
				register_widget( $widget_class );
			}
		}
	}

	/**
	 * Enqueue widget admin assets
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_widget_assets( $hook ) {
		// Only load on widgets page
		if ( $hook !== 'widgets.php' ) {
			return;
		}

		// Add inline CSS for widget admin
		$css = "
        .shahi-widget-field {
            margin-bottom: 15px;
        }
        .shahi-widget-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .shahi-widget-field input[type='text'],
        .shahi-widget-field input[type='number'],
        .shahi-widget-field select {
            width: 100%;
        }
        .shahi-widget-field .description {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
            font-style: italic;
        }
        .shahi-widget-preview {
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-top: 10px;
        }
        ";

		wp_add_inline_style( 'widgets', $css );
	}

	/**
	 * Enqueue frontend assets for widgets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		// Check if any ShahiLegalFlowSuite widgets are active
		if ( ! is_active_widget( false, false, 'shahi_stats_widget' ) &&
			! is_active_widget( false, false, 'shahi_quick_actions_widget' ) &&
			! is_active_widget( false, false, 'shahi_recent_activity_widget' ) ) {
			return;
		}

		// Add inline CSS for frontend widgets
		$css = '
        /* ShahiLegalFlowSuite Widgets Styling */
        .shahi-widget {
            padding: 20px;
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .shahi-widget-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #0073aa;
        }
        .shahi-widget-content {
            line-height: 1.6;
        }
        
        /* Stats Widget */
        .shahi-stats-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .shahi-stats-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .shahi-stats-list li:last-child {
            border-bottom: none;
        }
        .shahi-stat-label {
            font-weight: 500;
            color: #333;
        }
        .shahi-stat-value {
            font-size: 20px;
            font-weight: 600;
            color: #0073aa;
        }
        
        /* Quick Actions Widget */
        .shahi-actions-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .shahi-actions-list li {
            margin-bottom: 10px;
        }
        .shahi-action-button {
            display: block;
            padding: 10px 15px;
            background: #0073aa;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 3px;
            transition: background 0.3s;
        }
        .shahi-action-button:hover {
            background: #005a87;
            color: #fff;
        }
        
        /* Recent Activity Widget */
        .shahi-activity-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .shahi-activity-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .shahi-activity-list li:last-child {
            border-bottom: none;
        }
        .shahi-activity-time {
            font-size: 12px;
            color: #999;
            display: block;
            margin-top: 5px;
        }
        ';

		wp_add_inline_style( 'wp-block-library', $css );
	}

	/**
	 * Get registered widgets
	 *
	 * @since 1.0.0
	 * @return array Registered widgets.
	 */
	public function get_widgets() {
		return $this->widgets;
	}
}

