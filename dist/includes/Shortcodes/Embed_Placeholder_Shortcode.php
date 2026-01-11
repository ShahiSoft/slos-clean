<?php
/**
 * Embed Placeholder Shortcode
 *
 * Renders placeholders for embeds that require consent.
 * Blocks iframe/embed content until user provides necessary consent.
 *
 * Shortcode: [slos_embed_placeholder category="marketing" url="https://www.youtube.com/embed/VIDEO_ID"]
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Shortcodes
 * @version     3.1.1
 * @since       3.1.1
 */

namespace ShahiLegalFlowSuite\Shortcodes;

use ShahiLegalFlowSuite\Core\I18n;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embed_Placeholder_Shortcode Class
 *
 * Handles [slos_embed_placeholder] shortcode rendering.
 *
 * @since 3.1.1
 */
class Embed_Placeholder_Shortcode {

	/**
	 * Text domain constant
	 *
	 * @since 3.1.1
	 * @var string
	 */
	const TEXT_DOMAIN = I18n::TEXT_DOMAIN;

	/**
	 * Valid consent categories
	 *
	 * @since 3.1.1
	 * @var array
	 */
	private $valid_categories = array(
		'necessary',
		'functional',
		'analytics',
		'marketing',
		'preferences',
	);

	/**
	 * Category display labels
	 *
	 * @since 3.1.1
	 * @var array
	 */
	private $category_labels = array();

	/**
	 * Constructor
	 *
	 * @since 3.1.1
	 */
	public function __construct() {
		$this->category_labels = array(
			'necessary'   => __( 'Necessary', 'shahi-legalflowsuite' ),
			'functional'  => __( 'Functional', 'shahi-legalflowsuite' ),
			'analytics'   => __( 'Analytics', 'shahi-legalflowsuite' ),
			'marketing'   => __( 'Marketing', 'shahi-legalflowsuite' ),
			'preferences' => __( 'Preferences', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Initialize shortcode
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function init() {
		add_shortcode( 'slos_embed_placeholder', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode output
	 *
	 * @since 3.1.1
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render( $atts = array() ) {
		// Parse attributes with defaults
		$atts = shortcode_atts(
			array(
				'category' => 'marketing',
				'url'      => '',
				'title'    => '',
				'message'  => '',
				'width'    => '100%',
				'height'   => '400',
			),
			$atts,
			'slos_embed_placeholder'
		);

		// Sanitize inputs
		$category = sanitize_key( $atts['category'] );
		$url      = esc_url( $atts['url'] );
		$title    = sanitize_text_field( $atts['title'] );
		$message  = sanitize_text_field( $atts['message'] );
		$width    = sanitize_text_field( $atts['width'] );
		$height   = sanitize_text_field( $atts['height'] );

		// Validate category
		if ( ! in_array( $category, $this->valid_categories, true ) ) {
			$category = 'marketing';
		}

		// Get category label
		$category_label = $this->category_labels[ $category ] ?? ucfirst( $category );

		// Default title and message if not provided
		if ( empty( $title ) ) {
			$title = sprintf(
				/* translators: %s: category label */
				__( 'Content Blocked', 'shahi-legalflowsuite' ),
				$category_label
			);
		}

		if ( empty( $message ) ) {
			$message = sprintf(
				/* translators: %s: category label */
				__( 'This content requires %s cookies to be enabled.', 'shahi-legalflowsuite' ),
				'<strong>' . esc_html( $category_label ) . '</strong>'
			);
		}

		// Build inline style
		$style = array();
		if ( $width ) {
			$style[] = 'width: ' . esc_attr( $width ) . ( is_numeric( $width ) ? 'px' : '' );
		}
		if ( $height ) {
			$style[] = 'min-height: ' . esc_attr( $height ) . ( is_numeric( $height ) ? 'px' : '' );
		}
		$style_attr = ! empty( $style ) ? 'style="' . esc_attr( implode( '; ', $style ) ) . '"' : '';

		// Build data attributes
		$data_attrs = array(
			'data-slos-placeholder' => '1',
			'data-category'         => esc_attr( $category ),
		);
		if ( ! empty( $url ) ) {
			$data_attrs['data-embed-url'] = esc_url( $url );
		}

		ob_start();
		?>
		<div class="slos-embed-placeholder" <?php echo $this->render_data_attributes( $data_attrs ); ?> <?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="slos-placeholder-content">
				<div class="slos-placeholder-icon">
					<?php echo $this->get_placeholder_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<h3 class="slos-placeholder-title"><?php echo esc_html( $title ); ?></h3>
				<p class="slos-placeholder-message"><?php echo wp_kses_post( $message ); ?></p>
				<button type="button" class="slos-enable-btn" data-category="<?php echo esc_attr( $category ); ?>">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: category label */
							__( 'Enable %s Cookies', 'shahi-legalflowsuite' ),
							$category_label
						)
					);
					?>
				</button>
				<p class="slos-placeholder-privacy">
					<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" class="slos-privacy-link" target="_blank" rel="noopener">
						<?php esc_html_e( 'Learn about our privacy policy', 'shahi-legalflowsuite' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get placeholder icon SVG
	 *
	 * @since 3.1.1
	 * @return string SVG markup
	 */
	private function get_placeholder_icon() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48" aria-hidden="true">
			<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
			<path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z" opacity="0.3"/>
		</svg>';
	}

	/**
	 * Render data attributes as HTML string
	 *
	 * @since 3.1.1
	 * @param array $attrs Key-value pairs of data attributes
	 * @return string HTML attributes string
	 */
	private function render_data_attributes( $attrs ) {
		$output = array();
		foreach ( $attrs as $key => $value ) {
			$output[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}
		return implode( ' ', $output );
	}

	/**
	 * Register shortcode (alias for init)
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function register() {
		$this->init();
	}
}
