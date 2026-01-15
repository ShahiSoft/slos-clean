<?php
/**
 * Template: Pages Requiring Attention
 *
 * Displays table of pages with accessibility issues in the Tools tab.
 * Includes action buttons, priority indicators, and batch operations.
 *
 * @package ShahiLegalFlowSuite
 * @subpackage Templates\Admin
 * @since 3.1.2
 * @version 1.0.0
 *
 * STATUS: IMPLEMENTED - Phase 1.1 Complete
 * Created: January 6, 2026
 * Updated: January 6, 2026
 * Phase: Phase 1 - Critical Path
 * Priority: P0 - Blocking
 *
 * Template Variables.
 * @var array $pages_with_issues Array of pages with accessibility issues!.
 * Each page contains:
 *   - post_id: int
 *   - title: string
 *   - post_type: string
 *   - issues_count: int
 *   - critical_count: int
 *   - score: int (0-100)
 *   - priority: string (high/medium/low)
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- V3 Styled section matching Tools tab design -->
<div class="slos-tools-card full-width">
	<div class="slos-card-header">
		<h3>
			<span class="dashicons dashicons-warning"></span>
			<?php esc_html_e( 'Pages Requiring Attention', 'shahi-legalflowsuite' ); ?>
		</h3>
		<div class="slos-batch-actions">
			<button type="button" class="slos-btn-secondary slos-fix-all-pages" id="slos-fix-all-pages">
				<span class="dashicons dashicons-admin-tools"></span>
				<?php esc_html_e( 'Fix All Pages', 'shahi-legalflowsuite' ); ?>
			</button>
			<select class="slos-priority-filter">
				<option value="all"><?php esc_html_e( 'All Priorities', 'shahi-legalflowsuite' ); ?></option>
				<option value="high"><?php esc_html_e( 'High Priority', 'shahi-legalflowsuite' ); ?></option>
				<option value="medium"><?php esc_html_e( 'Medium Priority', 'shahi-legalflowsuite' ); ?></option>
				<option value="low"><?php esc_html_e( 'Low Priority', 'shahi-legalflowsuite' ); ?></option>
			</select>
		</div>
	</div>
	<div class="slos-card-body" style="padding: 0;">
		<?php if ( empty( $pages_with_issues ) ) : ?>
		<div style="text-align: center; padding: 60px 20px;">
			<span class="dashicons dashicons-yes-alt" style="font-size: 64px; color: var(--slos-success); margin-bottom: 16px; display: block;"></span>
			<h4 style="color: var(--slos-text-primary); margin: 0 0 8px;">
				<?php esc_html_e( 'All Clear!', 'shahi-legalflowsuite' ); ?>
			</h4>
			<p style="color: var(--slos-text-muted);">
				<?php esc_html_e( 'No accessibility issues found. Run a scan to check your content.', 'shahi-legalflowsuite' ); ?>
			</p>
		</div>
		<?php else : ?>
		<div class="slos-pages-table-wrapper" style="overflow-x: auto;">
			<table class="slos-pages-table" style="width: 100%; border-collapse: collapse; background: var(--slos-bg-card); border-radius: 6px;">
				<thead>
					<tr style="background: var(--slos-bg-input); border-bottom: 2px solid var(--slos-border);">
						<th class="col-select" style="width: 40px; padding: 12px 16px; text-align: center; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<input type="checkbox" class="slos-select-all" style="cursor: pointer; width: 16px; height: 16px;">
						</th>
						<th class="col-page" style="width: 25%; padding: 12px 16px; text-align: left; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-admin-page" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Page Name', 'shahi-legalflowsuite' ); ?>
						</th>
						<th class="col-issues" style="width: 8%; padding: 12px 16px; text-align: center; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-warning" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Issues', 'shahi-legalflowsuite' ); ?>
						</th>
						<th class="col-critical" style="width: 8%; padding: 12px 16px; text-align: center; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-shield" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Critical', 'shahi-legalflowsuite' ); ?>
						</th>
						<th class="col-score" style="width: 8%; padding: 12px 16px; text-align: center; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-chart-bar" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Score', 'shahi-legalflowsuite' ); ?>
						</th>
						<th class="col-priority" style="width: 10%; padding: 12px 16px; text-align: center; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-flag" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Priority', 'shahi-legalflowsuite' ); ?>
						</th>
						<th class="col-autofix" style="width: 8%; padding: 12px 16px; text-align: center; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-admin-tools" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Auto-Fix', 'shahi-legalflowsuite' ); ?>
						</th>
						<th class="col-actions" style="width: auto; padding: 12px 16px; text-align: right; font-weight: 600; color: var(--slos-text-muted); font-size: 12px; text-transform: uppercase;">
							<span class="dashicons dashicons-admin-generic" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>
							<?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $pages_with_issues as $page_item ) :
						$page_post_id   = isset( $page_item['post_id'] ) ? intval( $page_item['post_id'] ) : 0;
						$issues          = isset( $page_item['issues_count'] ) ? intval( $page_item['issues_count'] ) : 0;
						$critical        = isset( $page_item['critical_count'] ) ? intval( $page_item['critical_count'] ) : 0;
						$score           = isset( $page_item['score'] ) ? intval( $page_item['score'] ) : 100;
						$priority        = isset( $page_item['priority'] ) ? esc_attr( $page_item['priority'] ) : 'low';
						$priority_label  = array(
							'high'   => 'High',
							'medium' => 'Medium',
							'low'    => 'Low',
						)[ $priority ] ?? 'Low';
						$has_backup      = get_post_meta( $page_post_id, '_slos_backup_exists', true );
						$autofix_enabled = get_post_meta( $page_post_id, '_slos_autofix_enabled', true );
						$page_title      = isset( $page_item['title'] ) ? $page_item['title'] : 'Untitled';
						$page_post_type  = isset( $page_item['post_type'] ) ? $page_item['post_type'] : 'post';
						$issues_bg       = $issues > 15 ? 'rgba(239, 68, 68, 0.15)' : 'rgba(251, 191, 36, 0.15)';
						$issues_color    = $issues > 15 ? 'var(--slos-error)' : 'var(--slos-warning)';
						$score_bg        = $score >= 80 ? 'rgba(34, 197, 94, 0.15)' : ( $score >= 60 ? 'rgba(251, 191, 36, 0.15)' : 'rgba(239, 68, 68, 0.15)' );
						$score_bar_color = $score >= 80 ? 'var(--slos-success)' : ( $score >= 60 ? 'var(--slos-warning)' : 'var(--slos-error)' );
						$score_value     = max( 0, min( 100, $score ) );
						$priority_style  = 'background: rgba(59, 130, 246, 0.15); color: var(--slos-accent); border: 1px solid rgba(59, 130, 246, 0.3);';
						if ( 'high' === $priority ) {
							$priority_style = 'background: rgba(239, 68, 68, 0.15); color: var(--slos-error); border: 1px solid rgba(239, 68, 68, 0.3);';
						} elseif ( 'medium' === $priority ) {
							$priority_style = 'background: rgba(251, 191, 36, 0.15); color: var(--slos-warning); border: 1px solid rgba(251, 191, 36, 0.3);';
						}
						$priority_icon = 'high' === $priority ? 'arrow-up-alt' : ( 'medium' === $priority ? 'minus' : 'arrow-down-alt' );
						?>
					<tr class="slos-page-row" data-page-id="<?php echo esc_attr( $post_id ); ?>" data-priority="<?php echo esc_attr( $priority ); ?>" style="border-bottom: 1px solid var(--slos-border); transition: background-color 0.2s ease;">
						<td style="padding: 16px; text-align: center;">
							<input type="checkbox" class="slos-page-select" value="<?php echo esc_attr( $post_id ); ?>" style="cursor: pointer; width: 16px; height: 16px;">
						</td>
						<td class="page-title" style="padding: 16px;">
							<div style="display: flex; align-items: center; gap: 12px;">
								<div style="width: 36px; height: 36px; border-radius: 6px; background: var(--slos-bg-input); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
									<span class="dashicons dashicons-admin-post" style="font-size: 18px; color: var(--slos-accent);"></span>
								</div>
								<div style="flex: 1; min-width: 0;">
									<div style="font-weight: 600; color: var(--slos-text-primary); font-size: 14px; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo esc_html( $page_title ); ?></div>
									<div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--slos-text-muted);">
										<span class="dashicons dashicons-category" style="font-size: 12px;"></span>
										<span><?php echo esc_html( ucfirst( $page_post_type ) ); ?></span>
									</div>
								</div>
							</div>
						</td>
						<td class="issues-count" style="padding: 16px; text-align: center;">
							<div style="display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 32px; padding: 0 12px; background: <?php echo esc_attr( $issues_bg ); ?>; border-radius: 6px; font-weight: 600; font-size: 14px; color: <?php echo esc_attr( $issues_color ); ?>;">
								<?php echo esc_html( $issues ); ?>
							</div>
						</td>
						<td class="critical-count" style="padding: 16px; text-align: center;">
							<?php if ( $critical > 0 ) : ?>
							<div style="display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 32px; padding: 0 12px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; font-weight: 700; font-size: 13px; color: var(--slos-error);">
								<?php echo esc_html( $critical ); ?>
							</div>
							<?php else : ?>
							<span style="color: var(--slos-text-muted); font-size: 18px;">—</span>
							<?php endif; ?>
						</td>
						<td class="score-cell" style="padding: 16px; text-align: center;">
							<div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; background: <?php echo esc_attr( $score_bg ); ?>; border-radius: 6px;">
								<div style="width: 40px; height: 6px; background: var(--slos-bg-input); border-radius: 3px; position: relative; overflow: hidden;">
									<div style="position: absolute; left: 0; top: 0; height: 100%; width: <?php echo esc_attr( $score_value ); ?>%; background: <?php echo esc_attr( $score_bar_color ); ?>; border-radius: 3px; transition: width 0.3s ease;"></div>
								</div>
								<span style="font-weight: 700; font-size: 13px; color: <?php echo esc_attr( $score_bar_color ); ?>; min-width: 38px;">
									<?php echo esc_html( $score_value ); ?>%
								</span>
							</div>
						</td>
						<td class="priority-cell" style="padding: 16px; text-align: center;">
							<span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; text-transform: uppercase; <?php echo esc_attr( $priority_style ); ?>">
								<span class="dashicons dashicons-<?php echo esc_attr( $priority_icon ); ?>" style="font-size: 14px;"></span>
								<?php echo esc_html( $priority_label ); ?>
							</span>
						</td>
						<td class="autofix-cell" style="padding: 16px; text-align: center;">
							<label class="slos-autofix-toggle" style="display: inline-block; position: relative; width: 48px; height: 24px; cursor: pointer;">
								<input type="checkbox" class="slos-autofix-checkbox" data-post-id="<?php echo esc_attr( $post_id ); ?>" <?php checked( $autofix_enabled ); ?> style="position: absolute; opacity: 0; width: 0; height: 0;">
								<span class="slos-autofix-slider" style="position: absolute; inset: 0; background: var(--slos-bg-input); border: 2px solid var(--slos-border); border-radius: 24px; transition: all 0.3s ease;"></span>
							</label>
						</td>
						<td class="actions-cell" style="padding: 16px; text-align: right;">
							<div class="slos-page-actions" style="display: flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: wrap;">
								<button type="button" class="slos-view-details-btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'View Details', 'shahi-legalflowsuite' ); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--slos-bg-input); border: 1px solid var(--slos-border); border-radius: 6px; color: var(--slos-text-primary); font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; white-space: nowrap;">
									<span class="dashicons dashicons-visibility" style="font-size: 14px;"></span>
									<span><?php esc_html_e( 'Details', 'shahi-legalflowsuite' ); ?></span>
								</button>
								<button type="button" class="slos-fix-all-btn" data-page-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'Fix All Issues', 'shahi-legalflowsuite' ); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 6px; color: var(--slos-accent); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; white-space: nowrap;">
									<span class="dashicons dashicons-admin-tools" style="font-size: 14px;"></span>
									<span><?php esc_html_e( 'Fix All', 'shahi-legalflowsuite' ); ?></span>
								</button>
								<?php if ( $has_backup ) : ?>
								<button type="button" class="slos-rollback-btn" data-post-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'Rollback Fixes', 'shahi-legalflowsuite' ); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 6px; color: var(--slos-warning); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; white-space: nowrap;">
									<span class="dashicons dashicons-undo" style="font-size: 14px;"></span>
									<span><?php esc_html_e( 'Rollback', 'shahi-legalflowsuite' ); ?></span>
								</button>
								<?php endif; ?>
								<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" class="slos-edit-link" target="_blank" title="<?php esc_attr_e( 'Edit in WordPress', 'shahi-legalflowsuite' ); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--slos-bg-input); border: 1px solid var(--slos-border); border-radius: 6px; color: var(--slos-text-primary); font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; text-decoration: none; white-space: nowrap;">
									<span class="dashicons dashicons-edit" style="font-size: 14px;"></span>
									<span><?php esc_html_e( 'Edit', 'shahi-legalflowsuite' ); ?></span>
								</a>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<style>
			/* Enhanced Table Styling */
			.slos-pages-table-wrapper {
				border-radius: 6px;
				overflow: hidden;
			}
			
			.slos-page-row:hover {
				background: rgba(59, 130, 246, 0.05) !important;
			}
			
			.slos-page-row:last-child {
				border-bottom: none !important;
			}
			
			/* Auto-fix Toggle Animations */
			.slos-autofix-checkbox:checked + .slos-autofix-slider {
				background: var(--slos-accent) !important;
				border-color: var(--slos-accent) !important;
			}
			
			.slos-autofix-slider::after {
				content: '';
				position: absolute;
				top: 2px;
				left: 2px;
				width: 16px;
				height: 16px;
				background: white;
				border-radius: 50%;
				transition: transform 0.3s ease;
			}
			
			.slos-autofix-checkbox:checked + .slos-autofix-slider::after {
				transform: translateX(24px);
			}
			
			/* Button Hover Effects */
			.slos-view-details-btn:hover,
			.slos-edit-link:hover {
				background: var(--slos-accent) !important;
				color: white !important;
				border-color: var(--slos-accent) !important;
			}
			
			.slos-fix-all-btn:hover {
				background: var(--slos-accent) !important;
				color: white !important;
				border-color: var(--slos-accent) !important;
			}
			
			.slos-rollback-btn:hover {
				background: var(--slos-warning) !important;
				color: white !important;
				border-color: var(--slos-warning) !important;
			}
			
			/* Responsive Design */
			@media (max-width: 1400px) {
				.slos-page-actions {
					flex-direction: column;
					align-items: stretch !important;
				}
				
				.slos-page-actions button,
				.slos-page-actions a {
					width: 100%;
					justify-content: center;
				}
			}
			
			@media (max-width: 1200px) {
				.col-page { width: 20% !important; }
				.col-actions { width: auto !important; }
			}
			
			@media (max-width: 768px) {
				.slos-pages-table th,
				.slos-pages-table td {
					padding: 12px 8px !important;
					font-size: 12px !important;
				}
				
				.col-critical,
				.col-priority {
					display: none !important;
				}
				
				.slos-page-actions {
					gap: 4px !important;
				}
				
				.slos-page-actions button span:not(.dashicons),
				.slos-page-actions a span:not(.dashicons) {
					display: none;
				}
				
				.slos-page-actions button,
				.slos-page-actions a {
					padding: 8px !important;
					min-width: 36px;
					justify-content: center;
				}
			}
		</style>
		<?php endif; ?>
	</div>
</div>
?>
