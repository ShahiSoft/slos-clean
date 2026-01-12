<?php
/**
 * Consent UX Auto-Scanner Hook
 *
 * Automatically triggers consent UX scan when legal/consent pages are saved.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-Scanner Hook Class
 *
 * Hooks into WordPress save_post action to automatically scan consent/legal pages.
 *
 * @since 3.1.1
 */
class ConsentUxAutoScanner {

	/**
	 * ConsentUxChecker instance
	 *
	 * @var ConsentUxChecker
	 */
	private $checker;

	/**
	 * Constructor
	 *
	 * @since 3.1.1
	 */
	public function __construct() {
		$this->checker = new ConsentUxChecker();
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function init_hooks(): void {
		// Hook into save_post for pages..
		add_action( 'save_post_page', array( $this, 'maybe_scan_page' ), 10, 3 );

		// Hook into page publish transitions..
		add_action( 'transition_post_status', array( $this, 'on_page_publish' ), 10, 3 );

		// Add admin notice after scan..
		add_action( 'admin_notices', array( $this, 'show_scan_notice' ) );
	}

	/**
	 * Check if page should trigger scan
	 *
	 * @since 3.1.1
	 * @param int      $post_id Post ID
	 * @param \WP_Post $post    Post object
	 * @param bool     $update  Whether this is an update
	 * @return void
	 */
	public function maybe_scan_page( int $post_id, $post, bool $update ): void {
		// Bail if this is an autosave..
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail if this is a revision..
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Bail if user doesn't have permission..
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if this page is a consent/legal page..
		if ( ! $this->is_consent_page( $post_id ) ) {
			return;
		}

		// Only scan if post is published..
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// Trigger scan..
		$this->trigger_scan( $post_id, $post );
	}

	/**
	 * Handle page publish transitions
	 *
	 * @since 3.1.1
	 * @param string   $new_status New post status
	 * @param string   $old_status Old post status
	 * @param \WP_Post $post       Post object
	 * @return void
	 */
	public function on_page_publish( string $new_status, string $old_status, $post ): void {
		// Only care about pages..
		if ( 'page' !== $post->post_type ) {
			return;
		}

		// Only care about transitions to published..
		if ( 'publish' !== $new_status ) {
			return;
		}

		// Skip if already published (handled by save_post)..
		if ( 'publish' === $old_status ) {
			return;
		}

		// Check if this is a consent/legal page..
		if ( ! $this->is_consent_page( $post->ID ) ) {
			return;
		}

		// Trigger scan..
		$this->trigger_scan( $post->ID, $post );
	}

	/**
	 * Check if page is a consent or legal page
	 *
	 * @since 3.1.1
	 * @param int $post_id Post ID
	 * @return bool True if consent/legal page
	 */
	private function is_consent_page( int $post_id ): bool {
		// Get consent pages from checker..
		$consent_pages = $this->checker->get_consent_pages();

		return in_array( $post_id, $consent_pages, true );
	}

	/**
	 * Trigger consent UX scan
	 *
	 * @since 3.1.1
	 * @param int      $post_id Post ID
	 * @param \WP_Post $post    Post object
	 * @return void
	 */
	private function trigger_scan( int $post_id, $post ): void {
		// Run scan with force refresh..
		$results = $this->checker->scan( true );

		// Store last scan info in transient for admin notice..
		set_transient(
			'slos_consent_ux_last_scan',
			array(
				'post_id'      => $post_id,
				'post_title'   => $post->post_title,
				'scanned_at'   => current_time( 'mysql' ),
				'total_issues' => $results['total_issues'] ?? 0,
				'critical'     => $results['issue_counts']['critical'] ?? 0,
				'warning'      => $results['issue_counts']['warning'] ?? 0,
				'health_score' => $results['health_score'] ?? 0,
			),
			60 // Show notice for 60 seconds
		);

		// Log action..
		do_action( 'slos_consent_ux_auto_scan_completed', $post_id, $results );
	}

	/**
	 * Show admin notice after auto-scan
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function show_scan_notice(): void {
		// Check if on edit page..
		$screen = get_current_screen();
		if ( ! $screen || 'page' !== $screen->post_type ) {
			return;
		}

		// Get last scan info..
		$last_scan = get_transient( 'slos_consent_ux_last_scan' );
		if ( ! $last_scan ) {
			return;
		}

		$post_title   = esc_html( $last_scan['post_title'] );
		$issues       = absint( $last_scan['total_issues'] );
		$health_score = absint( $last_scan['health_score'] );

		// Determine notice class based on issues..
		if ( $last_scan['critical'] > 0 ) {
			$notice_class = 'notice-error';
			$icon         = 'warning';
			/* translators: 1: Page title, 2: Number of issues, 3: Health score */
			$message = sprintf(
				__( 'Consent UX Scan completed for "%1$s": Found %2$d issues (including %3$d critical). Health Score: %4$d/100. Please review accessibility issues.', 'shahi-legalflowsuite' ),
				$post_title,
				$issues,
				$last_scan['critical'],
				$health_score
			);
		} elseif ( $issues > 0 ) {
			$notice_class = 'notice-warning';
			$icon         = 'info';
			/* translators: 1: Page title, 2: Number of issues, 3: Health score */
			$message = sprintf(
				__( 'Consent UX Scan completed for "%1$s": Found %2$d accessibility issues. Health Score: %3$d/100.', 'shahi-legalflowsuite' ),
				$post_title,
				$issues,
				$health_score
			);
		} else {
			$notice_class = 'notice-success';
			$icon         = 'yes-alt';
			/* translators: 1: Page title, 2: Health score */
			$message = sprintf(
				__( 'Consent UX Scan completed for "%1$s": No accessibility issues found. Health Score: %2$d/100.', 'shahi-legalflowsuite' ),
				$post_title,
				$health_score
			);
		}

		?>
		<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
			<p>
				<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" style="vertical-align: middle;"></span>
				<strong><?php esc_html_e( 'Consent UX Auto-Scan:', 'shahi-legalflowsuite' ); ?></strong>
				<?php echo esc_html( $message ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-compliance&tab=dashboard' ) ); ?>" class="button button-small">
					<?php esc_html_e( 'View Compliance Dashboard', 'shahi-legalflowsuite' ); ?>
				</a>
			</p>
		</div>
		<?php

		// Delete transient so notice only shows once..
		delete_transient( 'slos_consent_ux_last_scan' );
	}
}

// Initialize auto-scanner..
new ConsentUxAutoScanner();
