<?php
/**
 * Shortcode Manager
 *
 * Central manager for registering and managing WordPress shortcodes.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Shortcodes
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Shortcodes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ShortcodeManager
 *
 * Manages registration and coordination of WordPress shortcodes.
 *
 * @since 1.0.0
 */
class ShortcodeManager {

	/**
	 * Registered shortcodes
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $shortcodes = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init_shortcodes();
		$this->register_hooks();
	}

	/**
	 * Initialize shortcodes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_shortcodes() {
		// Register shortcode instances (keeping only module-related shortcodes for legal operations)
        $this->shortcodes = array(
            'module'       => new ModuleShortcode(),
            'dsr_form'     => new DSR_Form_Shortcode(),
            'dsr_verify'   => new DSR_Verify_Shortcode(),
            'dsr_status'   => new DSR_Status_Shortcode(),
            'cookie_table' => new Cookie_Table_Shortcode(),
        );
		// Consent shortcodes are now registered by the ConsentManagement module
	}

	/**
	 * Register WordPress hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_shortcode_assets' ) );
		add_filter( 'widget_text', 'do_shortcode' );
	}

	/**
	 * Register all shortcodes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_shortcodes() {
		foreach ( $this->shortcodes as $key => $shortcode ) {
			if ( method_exists( $shortcode, 'register' ) ) {
				$shortcode->register();
			} elseif ( method_exists( $shortcode, 'init' ) ) {
				$shortcode->init();
			}
		}
	}

	/**
	 * Enqueue shortcode assets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_shortcode_assets() {
		// Check if any ShahiLegalFlowSuite shortcodes are present in the content
		global $post;

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		// Check if shortcodes are present
		$has_shortcodes = false;
		$shortcode_tags = array( 'shahi_stats', 'shahi_module', 'shahi_button' );

		foreach ( $shortcode_tags as $tag ) {
			if ( has_shortcode( $post->post_content, $tag ) ) {
				$has_shortcodes = true;
				break;
			}
		}

		if ( ! $has_shortcodes ) {
			return;
		}

		// Add inline CSS for shortcodes
		$css = '
        /* ShahiLegalFlowSuite Shortcodes Styling */
        .shahi-shortcode {
            display: inline-block;
            margin: 10px 0;
        }
        
        /* Stats Shortcode */
        .shahi-stats-shortcode {
            padding: 20px;
            background: #f9f9f9;
            border: 2px solid #0073aa;
            border-radius: 8px;
            text-align: center;
        }
        .shahi-stats-shortcode.inline {
            padding: 5px 15px;
            display: inline-block;
            margin: 0 5px;
        }
        .shahi-stat-label {
            display: block;
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .shahi-stat-value {
            display: block;
            font-size: 32px;
            font-weight: 700;
            color: #0073aa;
            line-height: 1.2;
        }
        .shahi-stats-shortcode.inline .shahi-stat-label {
            font-size: 12px;
            margin-bottom: 2px;
        }
        .shahi-stats-shortcode.inline .shahi-stat-value {
            font-size: 20px;
        }
        
        /* Module Shortcode */
        .shahi-module-shortcode {
            padding: 15px 20px;
            background: #fff;
            border-left: 4px solid #0073aa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 15px 0;
        }
        .shahi-module-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .shahi-module-status {
            display: inline-block;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .shahi-module-status.enabled {
            background: #46b450;
            color: #fff;
        }
        .shahi-module-status.disabled {
            background: #dc3232;
            color: #fff;
        }
        .shahi-module-description {
            margin-top: 10px;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* Button Shortcode */
        .shahi-button-shortcode {
            display: inline-block;
            padding: 12px 24px;
            background: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .shahi-button-shortcode:hover {
            background: #005a87;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .shahi-button-shortcode:active {
            transform: translateY(0);
        }
        .shahi-button-shortcode.size-small {
            padding: 8px 16px;
            font-size: 14px;
        }
        .shahi-button-shortcode.size-large {
            padding: 16px 32px;
            font-size: 18px;
        }
        .shahi-button-shortcode.style-secondary {
            background: #6c757d;
        }
        .shahi-button-shortcode.style-secondary:hover {
            background: #5a6268;
        }
        .shahi-button-shortcode.style-success {
            background: #46b450;
        }
        .shahi-button-shortcode.style-success:hover {
            background: #2e7d32;
        }
        .shahi-button-shortcode.style-danger {
            background: #dc3232;
        }
        .shahi-button-shortcode.style-danger:hover {
            background: #a00;
        }
        .shahi-button-shortcode .dashicons {
            margin-right: 5px;
            line-height: inherit;
            font-size: inherit;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .shahi-stats-shortcode {
                padding: 15px;
            }
            .shahi-stat-value {
                font-size: 24px;
            }
            .shahi-button-shortcode {
                display: block;
                text-align: center;
                margin: 10px 0;
            }
        }
        ';

		wp_add_inline_style( 'wp-block-library', $css );

		// Enqueue dashicons for button icons
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Get registered shortcodes
	 *
	 * @since 1.0.0
	 * @return array Registered shortcodes.
	 */
	public function get_shortcodes() {
		return $this->shortcodes;
	}
}

