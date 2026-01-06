<?php
/**
 * Integration tests for Accessibility Scanner AJAX endpoints.
 *
 * @package ShahiLegalFlowSuite
 * @subpackage Tests\Integration
 */

namespace ShahiLegalFlowSuite\Tests\Integration;

use WP_Ajax_UnitTestCase;

/**
 * Class AccessibilityScannerAjaxTest
 *
 * Exercises the unified FixEngine-powered AJAX endpoints:
 * - slos_get_page_fixable_issues
 * - slos_autofix_single
 */
class AccessibilityScannerAjaxTest extends WP_Ajax_UnitTestCase {

	/**
	 * Administrator user ID.
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Create and set current user as administrator to pass capability checks.
		$this->admin_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);

		wp_set_current_user( $this->admin_id );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		// Reset current user.
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * Helper to perform an AJAX request and decode the JSON response.
	 *
	 * @param string $action AJAX action name without the "wp_ajax_" prefix.
	 * @return array Decoded JSON response.
	 */
	protected function perform_ajax_request( string $action ): array {
		try {
			$this->_handleAjax( $action );
		} catch ( \WPAjaxDieStopException $e ) {
			// Expected: wp_send_json_* ends execution via wp_die().
		}

		$this->assertNotEmpty( $this->_last_response, 'AJAX response should not be empty.' );

		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, 'AJAX response should be valid JSON.' );

		return $response;
	}

	/**
	 * ajax_get_page_fixable_issues should return only fixers that have issues
	 * according to the stored scan results for the given page.
	 */
	public function test_get_page_fixable_issues_returns_only_fixers_with_issues() {
		// Create a test post.
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Accessibility Test Page',
				'post_content' => '<p>Test content</p>',
				'post_status'  => 'publish',
			)
		);

		// Synthetic scan results: some checks with issues, some without, and one
		// non-fixable check ID that should be ignored by the mapping.
		$scan_results = array(
			array(
				'id'     => 'missing-alt-text',
				'issues' => array(
					array( 'message' => 'Image missing alt text.' ),
				),
			),
			array(
				'id'     => 'empty-link',
				'issues' => array(
					array( 'message' => 'Empty link.' ),
				),
			),
			array(
				'id'     => 'contrast',
				'issues' => array(), // No issues -> should be ignored.
			),
			array(
				'id'     => 'non-fixable-check',
				'issues' => array(
					array( 'message' => 'Some issue' ),
				), // Not mapped -> should be ignored.
			),
		);

		update_post_meta( $post_id, '_slos_accessibility_scan_results', $scan_results );

		// Prepare AJAX request.
		$_POST['nonce']   = wp_create_nonce( 'slos_autofix_nonce' );
		$_POST['page_id'] = $post_id;

		$response = $this->perform_ajax_request( 'slos_get_page_fixable_issues' );

		$this->assertArrayHasKey( 'success', $response );
		$this->assertTrue( $response['success'], 'Expected success response.' );

		$this->assertArrayHasKey( 'data', $response );
		$data = $response['data'];

		$this->assertArrayHasKey( 'fixers', $data );
		$fixers = $data['fixers'];

		// Only the mapped checks with non-empty issues should appear.
		$this->assertCount( 2, $fixers, 'Expected two fixers with issues.' );

		$fixer_ids = wp_list_pluck( $fixers, 'id' );
		$this->assertContains( 'missing-alt-text', $fixer_ids );
		$this->assertContains( 'empty-link', $fixer_ids );

		// issue_count should match the number of fixers returned.
		$this->assertArrayHasKey( 'issue_count', $data );
		$this->assertSame( 2, $data['issue_count'] );
	}

	/**
	 * ajax_get_page_fixable_issues should return an empty list and a friendly
	 * message when no scan results are available for the page.
	 */
	public function test_get_page_fixable_issues_handles_missing_scan_results() {
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'No Scan Results Page',
				'post_content' => '<p>Content without scan results.</p>',
				'post_status'  => 'publish',
			)
		);

		// Ensure there are no scan results.
		delete_post_meta( $post_id, '_slos_accessibility_scan_results' );

		$_POST['nonce']   = wp_create_nonce( 'slos_autofix_nonce' );
		$_POST['page_id'] = $post_id;

		$response = $this->perform_ajax_request( 'slos_get_page_fixable_issues' );

		$this->assertArrayHasKey( 'success', $response );
		$this->assertTrue( $response['success'], 'Expected success response.' );

		$data = $response['data'];
		$this->assertArrayHasKey( 'fixers', $data );
		$this->assertIsArray( $data['fixers'] );
		$this->assertCount( 0, $data['fixers'], 'Expected no fixers when scan results are missing.' );

		$this->assertArrayHasKey( 'message', $data );
		$this->assertStringContainsString( 'No scan results found', $data['message'] );
	}

	/**
	 * ajax_autofix_single_fixer should apply the requested FixEngine fixer
	 * (when available) and report a successful result when content changes.
	 */
	public function test_autofix_single_fixer_applies_fixengine_fixer() {
		// Post with an <img> missing alt text so MissingAltFixer can apply.
		$content = '<p><img src="https://example.com/image-for-test.jpg"></p>';

		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Fixable Page',
				'post_content' => $content,
				'post_status'  => 'publish',
			)
		);

		$_POST['nonce']    = wp_create_nonce( 'slos_autofix_nonce' );
		$_POST['page_id']  = $post_id;
		$_POST['fixer_id'] = 'missing-alt-text';

		$response = $this->perform_ajax_request( 'slos_autofix_single' );

		$this->assertArrayHasKey( 'success', $response );
		$this->assertTrue( $response['success'], 'Expected success response from autofix endpoint.' );

		$data = $response['data'];

		$this->assertArrayHasKey( 'fixed_count', $data );
		$this->assertGreaterThan( 0, $data['fixed_count'], 'Expected at least one fix to be applied.' );

		$this->assertArrayHasKey( 'content_changed', $data );
		$this->assertTrue( $data['content_changed'], 'Content should be reported as changed after fixing.' );

		// Verify that the stored post content now has an alt attribute.
		$updated_post = get_post( $post_id );
		$this->assertStringContainsString( 'alt="', $updated_post->post_content );
	}

	/**
	 * ajax_autofix_single_fixer should skip when there are no applicable
	 * issues for the requested fixer.
	 */
	public function test_autofix_single_fixer_skips_when_no_issues() {
		// Post without any images, so MissingAltFixer has nothing to change.
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'No Issues Page',
				'post_content' => '<p>No images here.</p>',
				'post_status'  => 'publish',
			)
		);

		$_POST['nonce']    = wp_create_nonce( 'slos_autofix_nonce' );
		$_POST['page_id']  = $post_id;
		$_POST['fixer_id'] = 'missing-alt-text';

		$response = $this->perform_ajax_request( 'slos_autofix_single' );

		// In the "no issues" path the handler returns a non-success JSON
		// structure with a "skipped" flag instead of wp_send_json_success().
		$this->assertArrayHasKey( 'success', $response );
		$this->assertFalse( $response['success'], 'Expected non-success response when no issues are found.' );

		$this->assertArrayHasKey( 'data', $response );
		$data = $response['data'];

		$this->assertArrayHasKey( 'skipped', $data );
		$this->assertTrue( $data['skipped'], 'Fixer should report skipped when there are no issues.' );

		$this->assertArrayHasKey( 'reason', $data );
		$this->assertSame( 'no-issues', $data['reason'] );
	}
}
