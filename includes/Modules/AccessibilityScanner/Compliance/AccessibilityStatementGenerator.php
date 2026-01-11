<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Compliance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AccessibilityStatementGenerator {

	/**
	 * Generate Accessibility Statement Page
	 *
	 * @param array $data Company/Site details
	 * @return int|WP_Error Post ID or Error
	 */
	public function generate( $data = array() ) {
		$defaults = array(
			'site_name'     => get_bloginfo( 'name' ),
			'contact_email' => get_option( 'admin_email' ),
			'standard'      => 'WCAG 2.1 Level AA',
			'date'          => date( 'F j, Y' ),
		);

		$data = wp_parse_args( $data, $defaults );

		$content = $this->get_template( $data );

		$post_data = array(
			'post_title'   => 'Accessibility Statement',
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		);

		// Check if page already exists
		$existing_page = get_page_by_title( 'Accessibility Statement' );
		if ( $existing_page ) {
			$post_data['ID'] = $existing_page->ID;
			return wp_update_post( $post_data );
		}

		return wp_insert_post( $post_data );
	}

	/**
	 * Get Statement Template
	 *
	 * @param array $data
	 * @return string HTML Content
	 */
	private function get_template( $data ) {
		return <<<HTML
<!-- wp:heading -->
<h2>Accessibility Statement for {$data['site_name']}</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is an accessibility statement from {$data['site_name']}.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Measures to support accessibility</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{$data['site_name']} takes the following measures to ensure accessibility of {$data['site_name']}:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Include accessibility as part of our mission statement.</li>
<li>Include accessibility throughout our internal policies.</li>
<li>Integrate accessibility into our procurement practices.</li>
<li>Provide continual accessibility training for our staff.</li>
<li>Assign clear accessibility goals and responsibilities.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>Conformance status</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The <a href="https://www.w3.org/WAI/standards-guidelines/wcag/">Web Content Accessibility Guidelines (WCAG)</a> defines requirements for designers and developers to improve accessibility for people with disabilities. It defines three levels of conformance: Level A, Level AA, and Level AAA. {$data['site_name']} is partially conformant with {$data['standard']}. Partially conformant means that some parts of the content do not fully conform to the accessibility standard.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Feedback</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We welcome your feedback on the accessibility of {$data['site_name']}. Please let us know if you encounter accessibility barriers on {$data['site_name']}:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>E-mail: <a href="mailto:{$data['contact_email']}">{$data['contact_email']}</a></li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>We try to respond to feedback within 2 business days.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>This statement was created on {$data['date']} using the Shahi LegalFlowSuite Accessibility Scanner.</p>
<!-- /wp:paragraph -->
HTML;
	}
}
