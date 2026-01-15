<?php
/**
 * Scanner Page Admin Interface
 *
 * @package ShahiLegalFlowSuite
 * @subpackage AccessibilityScanner
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Scanner Tools Page Class
 *
 * Renders the accessibility scanner tools interface including:
 * - Content Scanner
 * - Accessibility Statement Generator
 * - Color Contrast Checker
 * - Readability Checker
 * - Link Text Validator
 *
 * @since 3.0.2
 */
class ScannerPage {

	/**
	 * Render content (for tabbed interface integration)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		?>
		<div class="slos-scanner-tools">
			<?php $this->render_content_scanner(); ?>
			<?php $this->render_pages_requiring_attention(); ?>
			<?php $this->render_statement_generator(); ?>
			<?php $this->render_contrast_checker(); ?>
			<?php $this->render_readability_checker(); ?>
			<?php $this->render_link_validator(); ?>
		</div>
		<?php
	}

	/**
	 * Render Content Scanner Tool
	 *
	 * @return void
	 */
	private function render_content_scanner() {
		?>
		<div class="slos-tool-card">
			<div class="slos-tool-header">
				<h3><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Content Scanner', 'shahi-legalflowsuite' ); ?></h3>
				<p><?php esc_html_e( 'Scan all published pages and posts for accessibility issues', 'shahi-legalflowsuite' ); ?></p>
			</div>
			<div class="slos-tool-content">
				<button id="slos-start-scan" class="button button-primary button-hero">
					<span class="dashicons dashicons-search"></span>
					<?php esc_html_e( 'Start Full Scan', 'shahi-legalflowsuite' ); ?>
				</button>
				
				<div id="slos-scan-status" style="display:none; margin-top: 20px;">
					<p><?php esc_html_e( 'Scanning...', 'shahi-legalflowsuite' ); ?> <span id="slos-scan-progress">0</span>%</p>
				</div>
				
				<div id="slos-progress-bar-wrapper" style="display:none; margin-top: 10px;">
					<div class="slos-progress-bar">
						<div id="slos-progress-bar" class="slos-progress-bar-fill" style="width: 0%;"></div>
					</div>
				</div>
				
				<div id="slos-scan-results" style="margin-top: 20px;"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Accessibility Statement Generator Tool
	 *
	 * @return void
	 */
	private function render_statement_generator() {
		?>
		<div class="slos-tool-card">
			<div class="slos-tool-header">
				<h3><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Accessibility Statement Generator', 'shahi-legalflowsuite' ); ?></h3>
				<p><?php esc_html_e( 'Generate a compliant accessibility statement for your website', 'shahi-legalflowsuite' ); ?></p>
			</div>
			<div class="slos-tool-content">
				<div class="slos-form-row">
					<label for="slos-org-name"><?php esc_html_e( 'Organization Name', 'shahi-legalflowsuite' ); ?> <span style="color: red;">*</span></label>
					<input type="text" id="slos-org-name" class="regular-text" placeholder="<?php esc_attr_e( 'Your Organization', 'shahi-legalflowsuite' ); ?>" required>
				</div>
				
				<div class="slos-form-row">
					<label for="slos-contact-email"><?php esc_html_e( 'Contact Email', 'shahi-legalflowsuite' ); ?></label>
					<input type="email" id="slos-contact-email" class="regular-text" placeholder="<?php esc_attr_e( 'accessibility@example.com', 'shahi-legalflowsuite' ); ?>">
				</div>
				
				<div class="slos-form-row">
					<label for="slos-wcag-target"><?php esc_html_e( 'WCAG Target Level', 'shahi-legalflowsuite' ); ?></label>
					<select id="slos-wcag-target">
						<option value="WCAG 2.1 Level A">WCAG 2.1 Level A</option>
						<option value="WCAG 2.1 Level AA" selected>WCAG 2.1 Level AA</option>
						<option value="WCAG 2.1 Level AAA">WCAG 2.1 Level AAA</option>
						<option value="WCAG 2.2 Level AA">WCAG 2.2 Level AA</option>
					</select>
				</div>
				
				<div class="slos-form-row">
					<label for="slos-statement-date"><?php esc_html_e( 'Statement Date', 'shahi-legalflowsuite' ); ?></label>
					<input type="date" id="slos-statement-date" value="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
				</div>
				
				<div class="slos-form-row">
					<label for="slos-commitment"><?php esc_html_e( 'Additional Commitment (Optional)', 'shahi-legalflowsuite' ); ?></label>
					<textarea id="slos-commitment" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Describe your ongoing accessibility commitment...', 'shahi-legalflowsuite' ); ?>"></textarea>
				</div>
				
				<div class="slos-button-group">
					<button id="slos-generate-statement" class="button button-primary">
						<span class="dashicons dashicons-visibility"></span>
						<?php esc_html_e( 'Generate Preview', 'shahi-legalflowsuite' ); ?>
					</button>
					<button id="slos-publish-statement" class="button button-secondary" disabled>
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Publish as Page', 'shahi-legalflowsuite' ); ?>
					</button>
				</div>
				
				<div id="slos-statement-preview-anchor" style="margin-top: 20px;"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Color Contrast Checker Tool
	 *
	 * @return void
	 */
	private function render_contrast_checker() {
		?>
		<div class="slos-tool-card">
			<div class="slos-tool-header">
				<h3><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Color Contrast Checker', 'shahi-legalflowsuite' ); ?></h3>
				<p><?php esc_html_e( 'Check if color combinations meet WCAG contrast requirements', 'shahi-legalflowsuite' ); ?></p>
			</div>
			<div class="slos-tool-content">
				<div class="slos-contrast-inputs">
					<div class="slos-form-row">
						<label for="slos-fg-color"><?php esc_html_e( 'Foreground Color (Text)', 'shahi-legalflowsuite' ); ?></label>
						<input type="color" id="slos-fg-color" value="#000000">
						<input type="text" id="slos-fg-color-hex" class="slos-color-hex" value="#000000" readonly>
					</div>
					
					<div class="slos-form-row">
						<label for="slos-bg-color"><?php esc_html_e( 'Background Color', 'shahi-legalflowsuite' ); ?></label>
						<input type="color" id="slos-bg-color" value="#FFFFFF">
						<input type="text" id="slos-bg-color-hex" class="slos-color-hex" value="#FFFFFF" readonly>
					</div>
				</div>
				
				<button id="slos-check-contrast" class="button button-primary">
					<span class="dashicons dashicons-chart-area"></span>
					<?php esc_html_e( 'Check Contrast', 'shahi-legalflowsuite' ); ?>
				</button>
				
				<div id="slos-contrast-result" class="slos-tool-result"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Readability Checker Tool
	 *
	 * @return void
	 */
	private function render_readability_checker() {
		?>
		<div class="slos-tool-card">
			<div class="slos-tool-header">
				<h3><span class="dashicons dashicons-book-alt"></span> <?php esc_html_e( 'Readability Checker', 'shahi-legalflowsuite' ); ?></h3>
				<p><?php esc_html_e( 'Check if content meets recommended readability standards', 'shahi-legalflowsuite' ); ?></p>
			</div>
			<div class="slos-tool-content">
				<div class="slos-form-row">
					<label for="slos-readability-text"><?php esc_html_e( 'Text to Analyze', 'shahi-legalflowsuite' ); ?></label>
					<textarea id="slos-readability-text" rows="6" class="large-text" placeholder="<?php esc_attr_e( 'Paste your content here...', 'shahi-legalflowsuite' ); ?>"></textarea>
				</div>
				
				<button id="slos-check-readability" class="button button-primary">
					<span class="dashicons dashicons-analytics"></span>
					<?php esc_html_e( 'Check Readability', 'shahi-legalflowsuite' ); ?>
				</button>
				
				<div id="slos-readability-result" class="slos-tool-result"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Link Text Validator Tool
	 *
	 * @return void
	 */
	private function render_link_validator() {
		?>
		<div class="slos-tool-card">
			<div class="slos-tool-header">
				<h3><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Link Text Validator', 'shahi-legalflowsuite' ); ?></h3>
				<p><?php esc_html_e( 'Check if link text is descriptive and accessible', 'shahi-legalflowsuite' ); ?></p>
			</div>
			<div class="slos-tool-content">
				<div class="slos-form-row">
					<label for="slos-link-text"><?php esc_html_e( 'Link Text', 'shahi-legalflowsuite' ); ?></label>
					<input type="text" id="slos-link-text" class="regular-text" placeholder="<?php esc_attr_e( 'Enter link text...', 'shahi-legalflowsuite' ); ?>">
				</div>
				
				<button id="slos-check-link" class="button button-primary">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Validate Link Text', 'shahi-legalflowsuite' ); ?>
				</button>
				
				<div id="slos-link-result" class="slos-tool-result"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Pages Requiring Attention section
	 *
	 * @return void
	 */
	private function render_pages_requiring_attention() {
		// Get scan results.
		$scan_results = get_option( 'slos_last_scan_results', array() );

		?>
		<div class="slos-tool-card slos-pages-attention-card">
			<div class="slos-tool-header">
				<h3><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Pages Requiring Attention', 'shahi-legalflowsuite' ); ?></h3>
				<p><?php esc_html_e( 'Pages with accessibility issues that need to be addressed', 'shahi-legalflowsuite' ); ?></p>
			</div>
			<div class="slos-tool-content">
				<?php if ( empty( $scan_results ) ) : ?>
					<div class="slos-empty-state">
						<span class="dashicons dashicons-search" style="font-size: 48px; color: #94a3b8; margin-bottom: 16px;"></span>
						<p><?php esc_html_e( 'No scan results available yet. Run a content scan to see pages that need attention.', 'shahi-legalflowsuite' ); ?></p>
						<button id="slos-start-scan" class="button button-primary">
							<span class="dashicons dashicons-search"></span>
							<?php esc_html_e( 'Start Scan', 'shahi-legalflowsuite' ); ?>
						</button>
					</div>
				<?php else : ?>
					<div class="slos-pages-table-wrapper">
						<table class="slos-pages-table widefat">
							<thead>
								<tr>
									<th class="slos-col-page"><?php esc_html_e( 'Page/Post', 'shahi-legalflowsuite' ); ?></th>
									<th class="slos-col-score"><?php esc_html_e( 'Score', 'shahi-legalflowsuite' ); ?></th>
									<th class="slos-col-issues"><?php esc_html_e( 'Issues', 'shahi-legalflowsuite' ); ?></th>
									<th class="slos-col-status"><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></th>
									<th class="slos-col-actions"><?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$page_count = 0;
								foreach ( $scan_results as $post_id => $result ) :
									// Skip if no issues.
									if ( empty( $result['issues'] ) || 0 === count( $result['issues'] ) ) {
										continue;
									}

									++$page_count;
									$post = get_post( $post_id );
									if ( ! $post ) {
										continue;
									}

									$score          = isset( $result['score'] ) ? intval( $result['score'] ) : 0;
									$issues_count   = isset( $result['issues'] ) ? count( $result['issues'] ) : 0;
									$critical_count = 0;
									$autofix_count  = 0;

									// Count critical and auto-fixable.
									if ( ! empty( $result['issues'] ) ) {
										foreach ( $result['issues'] as $issue ) {
											if ( isset( $issue['severity'] ) && 'error' === $issue['severity'] ) {
												++$critical_count;
											}
											if ( isset( $issue['fixable'] ) && $issue['fixable'] ) {
												++$autofix_count;
											}
										}
									}

									$score_class  = $score >= 90 ? 'excellent' : ( $score >= 70 ? 'good' : ( $score >= 50 ? 'fair' : 'poor' ) );
									$status_class = $critical_count > 0 ? 'critical' : ( $issues_count > 5 ? 'warning' : 'info' );
									$status_text  = $critical_count > 0 ? __( 'Critical', 'shahi-legalflowsuite' ) : ( $issues_count > 5 ? __( 'Needs Work', 'shahi-legalflowsuite' ) : __( 'Minor Issues', 'shahi-legalflowsuite' ) );
									?>
									<tr data-post-id="<?php echo esc_attr( $post_id ); ?>">
										<td class="slos-col-page">
											<div class="slos-page-title">
												<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" target="_blank">
													<?php echo esc_html( get_the_title( $post_id ) ); ?>
												</a>
												<span class="slos-page-type"><?php echo esc_html( ucfirst( $post->post_type ) ); ?></span>
											</div>
										</td>
										<td class="slos-col-score">
											<span class="slos-score-badge <?php echo esc_attr( $score_class ); ?>">
												<?php echo esc_html( $score ); ?>/100
											</span>
										</td>
										<td class="slos-col-issues">
											<div class="slos-issues-summary">
												<span class="slos-total-issues"><?php echo esc_html( $issues_count ); ?> <?php esc_html_e( 'total', 'shahi-legalflowsuite' ); ?></span>
												<?php if ( $critical_count > 0 ) : ?>
													<span class="slos-critical-badge"><?php echo esc_html( $critical_count ); ?> <?php esc_html_e( 'critical', 'shahi-legalflowsuite' ); ?></span>
												<?php endif; ?>
												<?php if ( $autofix_count > 0 ) : ?>
													<span class="slos-autofix-badge"><?php echo esc_html( $autofix_count ); ?> <?php esc_html_e( 'fixable', 'shahi-legalflowsuite' ); ?></span>
												<?php endif; ?>
											</div>
										</td>
										<td class="slos-col-status">
											<span class="slos-status-badge <?php echo esc_attr( $status_class ); ?>">
												<?php echo esc_html( $status_text ); ?>
											</span>
										</td>
										<td class="slos-col-actions">
											<div class="slos-page-actions">
												<button type="button" class="button button-small slos-view-issues-btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'View Issues', 'shahi-legalflowsuite' ); ?>">
													<span class="dashicons dashicons-visibility"></span>
													<?php esc_html_e( 'View', 'shahi-legalflowsuite' ); ?>
												</button>
												<?php if ( $autofix_count > 0 && ! ( defined( 'SLOS_DORMANT_AUTOFIX' ) && SLOS_DORMANT_AUTOFIX ) ) : ?>
													<button type="button" class="button button-small button-primary slos-fix-page-btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'Auto-Fix Issues', 'shahi-legalflowsuite' ); ?>">
														<span class="dashicons dashicons-admin-tools"></span>
														<?php esc_html_e( 'Fix', 'shahi-legalflowsuite' ); ?>
													</button>
												<?php endif; ?>
												<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" class="button button-small" target="_blank" title="<?php esc_attr_e( 'Edit Page', 'shahi-legalflowsuite' ); ?>">
													<span class="dashicons dashicons-edit"></span>
													<?php esc_html_e( 'Edit', 'shahi-legalflowsuite' ); ?>
												</a>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
								
								<?php if ( 0 === $page_count ) : ?>
									<tr>
										<td colspan="5" class="slos-no-issues">
											<div class="slos-empty-state">
												<span class="dashicons dashicons-yes-alt" style="font-size: 48px; color: #22c55e; margin-bottom: 16px;"></span>
												<p><?php esc_html_e( 'Great news! No pages with accessibility issues found.', 'shahi-legalflowsuite' ); ?></p>
											</div>
										</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
					
					<?php if ( $page_count > 0 ) : ?>
						<div class="slos-pages-footer">
							<div class="slos-pages-summary">
								<?php
								/* translators: %d: number of pages with issues */
								printf( esc_html__( 'Showing %d page(s) with accessibility issues', 'shahi-legalflowsuite' ), absint( $page_count ) );
								?>
							</div>
							<?php if ( ! ( defined( 'SLOS_DORMANT_AUTOFIX' ) && SLOS_DORMANT_AUTOFIX ) ) : ?>
								<button type="button" id="slos-fix-all-pages" class="button button-primary button-hero">
									<span class="dashicons dashicons-admin-tools"></span>
									<?php esc_html_e( 'Fix All Auto-Fixable Issues', 'shahi-legalflowsuite' ); ?>
								</button>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
