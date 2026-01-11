<?php
/**
 * Cookie Table Shortcode
 *
 * Renders a table of detected cookies by category for embedding in legal documents.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Shortcodes
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Table Shortcode Class
 *
 * Usage: [slos_cookie_table category="all" title="yes"]
 *
 * @since 3.1.1
 */
class Cookie_Table_Shortcode {

	/**
	 * Register shortcode
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function register() {
		add_shortcode( 'slos_cookie_table', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode
	 *
	 * @since 3.1.1
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string Rendered HTML.
	 */
	public function render( $atts, $content = null ) {
		// Avoid unused content parameter.
		unset( $content );

		// Parse attributes.
		$atts = shortcode_atts(
			array(
				'category' => 'all',        // all, necessary, analytics, marketing, functional.
				'title'    => 'yes',        // Show category title.
				'style'    => 'default',    // default, minimal, compact.
			),
			$atts,
			'slos_cookie_table'
		);

		// Get cookie inventory.
		$inventory = get_option( 'slos_cookie_inventory', array() );

		// Fallback to legacy format if needed.
		if ( empty( $inventory ) ) {
			$legacy_cookies = get_option( 'slos_detected_cookies', array() );
			$inventory      = $this->convert_legacy_cookies( $legacy_cookies );
		}

		// Filter by category if specified.
		if ( 'all' !== $atts['category'] ) {
			$inventory = array_filter(
				$inventory,
				function ( $cookie ) use ( $atts ) {
					$category = $cookie['category'] ?? 'uncategorized';
					return $category === $atts['category'];
				}
			);
		}

		// No cookies found.
		if ( empty( $inventory ) ) {
			return '<p class="slos-no-cookies"><em>' . esc_html__( 'No cookies detected in this category.', 'shahi-legalflowsuite' ) . '</em></p>';
		}

		// Build HTML output.
		$html = '<div class="slos-cookie-table-wrapper slos-style-' . esc_attr( $atts['style'] ) . '">';

		// Add title if enabled.
		if ( 'yes' === $atts['title'] ) {
			$title = $this->get_category_title( $atts['category'] );
			$html .= '<h3 class="slos-cookie-table-title">' . esc_html( $title ) . '</h3>';
		}

		// Render table.
		$html .= $this->render_table( $inventory, $atts['style'] );

		// Add last updated timestamp.
		$scan_meta = get_option( 'slos_cookie_scan_meta', array() );
		if ( ! empty( $scan_meta['completed_at'] ) ) {
			$last_scan = wp_date( get_option( 'date_format' ), $scan_meta['completed_at'] );
			$html     .= '<p class="slos-cookie-table-meta"><em>'
				. sprintf(
					/* translators: %s: date of last scan */
					esc_html__( 'Last updated: %s', 'shahi-legalflowsuite' ),
					esc_html( $last_scan )
				)
				. '</em></p>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render cookie table HTML
	 *
	 * @since 3.1.1
	 * @param array  $cookies Cookie data.
	 * @param string $style   Table style.
	 * @return string HTML table.
	 */
	protected function render_table( array $cookies, string $style ): string {
		$class = 'slos-cookie-table slos-style-' . esc_attr( $style );

		$html = '<table class="' . $class . '">';

		// Table header.
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . esc_html__( 'Cookie Name', 'shahi-legalflowsuite' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Purpose', 'shahi-legalflowsuite' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Provider', 'shahi-legalflowsuite' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Duration', 'shahi-legalflowsuite' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		// Table body.
		$html .= '<tbody>';
		foreach ( $cookies as $cookie ) {
			$name     = $cookie['name'] ?? __( 'Unknown', 'shahi-legalflowsuite' );
			$purpose  = $cookie['purpose'] ?? $cookie['description'] ?? __( 'Not specified', 'shahi-legalflowsuite' );
			$provider = $cookie['provider'] ?? $cookie['domain'] ?? __( 'Unknown', 'shahi-legalflowsuite' );
			$duration = $cookie['duration'] ?? $cookie['expiry'] ?? __( 'Session', 'shahi-legalflowsuite' );

			$html .= '<tr>';
			$html .= '<td class="cookie-name"><strong>' . esc_html( $name ) . '</strong></td>';
			$html .= '<td class="cookie-purpose">' . esc_html( $purpose ) . '</td>';
			$html .= '<td class="cookie-provider">' . esc_html( $provider ) . '</td>';
			$html .= '<td class="cookie-duration">' . esc_html( $duration ) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';

		$html .= '</table>';

		return $html;
	}

	/**
	 * Get category title
	 *
	 * @since 3.1.1
	 * @param string $category Category key.
	 * @return string Category title
	 */
	protected function get_category_title( string $category ): string {
		$titles = array(
			'all'        => __( 'All Cookies', 'shahi-legalflowsuite' ),
			'necessary'  => __( 'Strictly Necessary Cookies', 'shahi-legalflowsuite' ),
			'analytics'  => __( 'Analytics Cookies', 'shahi-legalflowsuite' ),
			'marketing'  => __( 'Marketing Cookies', 'shahi-legalflowsuite' ),
			'functional' => __( 'Functional Cookies', 'shahi-legalflowsuite' ),
		);

		return $titles[ $category ] ?? ucfirst( $category ) . ' ' . __( 'Cookies', 'shahi-legalflowsuite' );
	}

	/**
	 * Convert legacy cookie format to inventory format
	 *
	 * @since 3.1.1
	 * @param array $legacy_cookies Legacy cookies array.
	 * @return array Inventory format
	 */
	protected function convert_legacy_cookies( array $legacy_cookies ): array {
		$inventory = array();

		foreach ( $legacy_cookies as $cookie_name => $data ) {
			$inventory[] = array(
				'name'     => $cookie_name,
				'category' => $data['category'] ?? 'uncategorized',
				'purpose'  => $data['purpose'] ?? '',
				'provider' => $data['domain'] ?? '',
				'duration' => $data['expiry'] ?? 'Session',
			);
		}

		return $inventory;
	}
}
