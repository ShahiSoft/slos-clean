<?php
/**
 * Compliance Dashboard Tab - V3 Design
 *
 * Main dashboard with metrics, charts, and compliance health score.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Calculate percentages for consent breakdown
$type_percentages = array();
$total_by_type = array_sum( $stats['by_type'] );
foreach ( $stats['by_type'] as $type => $count ) {
	$type_percentages[ $type ] = $total_by_type > 0 ? round( ( $count / $total_by_type ) * 100 ) : 0;
}

// Prepare grade class
$grade_class = $stats['grade_class'] ?? 'grade-' . strtolower( $stats['grade'] );
$circumference = 2 * M_PI * 65;
$offset = $circumference - ( $stats['compliance_score'] / 100 ) * $circumference;

// Get dimension labels and icons
require_once SHAHI_LEGALFLOWSUITE_PATH . 'config/compliance-constants.php';
$dimension_labels = slos_get_dimension_labels();
$dimension_icons = slos_get_dimension_icons();
?>

<!-- Section Description -->
<p class="slos-section-description">
	<?php esc_html_e( 'Real-time overview of your privacy compliance status. Monitor consent metrics, review activity, and track compliance health across all supported regulations.', 'shahi-legalflowsuite' ); ?>
</p>

<!-- Operations Dashboard (Phase 2.1) -->
<?php if ( ! empty( $ops_stats ) ) : ?>
<div class="slos-ops-dashboard" style="margin-bottom: 32px;">
	<div class="slos-card">
		<div class="slos-card-header">
			<h3>
				<span class="dashicons dashicons-dashboard"></span>
				<?php esc_html_e( 'Operations Dashboard', 'shahi-legalflowsuite' ); ?>
			</h3>
			<span class="badge badge-primary"><?php esc_html_e( 'Live', 'shahi-legalflowsuite' ); ?></span>
		</div>
		<p class="slos-widget-description">
			<?php esc_html_e( 'Unified operations view aggregating metrics from all compliance modules: Consent Management, Cookie Scanner, Data Subject Rights (DSR), and Accessibility Compliance. Click any card to drill down into detailed module views.', 'shahi-legalflowsuite' ); ?>
		</p>
		<div class="slos-card-body">
			<!-- Overall Ops Readiness Score -->
			<div class="slos-ops-score-banner" style="padding: 20px; background: linear-gradient(135deg, var(--slos-primary-dark) 0%, var(--slos-primary) 100%); border-radius: 12px; margin-bottom: 24px;">
				<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
					<div style="flex: 1; min-width: 200px;">
						<div style="font-size: 14px; color: rgba(255,255,255,0.9); font-weight: 500; margin-bottom: 8px;">
							<?php esc_html_e( 'Overall Operations Readiness', 'shahi-legalflowsuite' ); ?>
						</div>
						<div style="font-size: 36px; font-weight: 700; color: white; line-height: 1; margin-bottom: 8px;">
							<?php echo esc_html( $ops_stats['ops_score'] ); ?><span style="font-size: 20px; opacity: 0.8;">/100</span>
						</div>
						<div style="font-size: 13px; color: rgba(255,255,255,0.8);">
							<?php 
							/* translators: %s: Grade letter and label */
							printf( esc_html__( 'Grade: %s - %s', 'shahi-legalflowsuite' ), 
								'<strong>' . esc_html( $ops_stats['ops_grade'] ) . '</strong>',
								esc_html( $ops_stats['ops_label'] )
							);
							?>
						</div>
					</div>
					<div style="display: flex; gap: 12px; flex-wrap: wrap;">
						<?php 
						$ops_dimensions = $ops_stats['dimensions'] ?? array();
						$ops_dimension_count = count( $ops_dimensions );
						?>
						<div class="slos-ops-metric">
							<div style="font-size: 12px; color: rgba(255,255,255,0.8); margin-bottom: 4px;">
								<?php esc_html_e( 'Dimensions', 'shahi-legalflowsuite' ); ?>
							</div>
							<div style="font-size: 24px; font-weight: 600; color: white;">
								<?php echo esc_html( $ops_dimension_count ); ?>
							</div>
						</div>
						<div class="slos-ops-metric">
							<div style="font-size: 12px; color: rgba(255,255,255,0.8); margin-bottom: 4px;">
								<?php esc_html_e( 'Modules Active', 'shahi-legalflowsuite' ); ?>
							</div>
							<div style="font-size: 24px; font-weight: 600; color: white;">
								4
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Module Cards Grid -->
			<div class="slos-ops-modules-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
				<!-- Consent Module Card -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-compliance&tab=records' ) ); ?>" class="slos-ops-module-card" style="text-decoration: none; display: block;">
					<div style="padding: 20px; background: var(--slos-bg-input); border-radius: 12px; border: 2px solid transparent; transition: all 0.3s ease; height: 100%;">
						<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
							<div style="width: 40px; height: 40px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
								<span class="dashicons dashicons-shield-alt" style="color: white; font-size: 20px;"></span>
							</div>
							<div style="flex: 1;">
								<h4 style="margin: 0; font-size: 16px; color: var(--slos-text-primary); font-weight: 600;">
									<?php esc_html_e( 'Consent Management', 'shahi-legalflowsuite' ); ?>
								</h4>
								<div style="font-size: 11px; color: var(--slos-text-muted); margin-top: 2px;">
									<?php esc_html_e( 'User consent tracking', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
							<div>
								<div style="font-size: 24px; font-weight: 700; color: var(--slos-text-primary);">
									<?php echo esc_html( number_format( $ops_stats['consent']['total_consents'] ?? 0 ) ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Total Consents', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
							<div>
								<div style="font-size: 24px; font-weight: 700; color: var(--slos-success);">
									<?php echo esc_html( $ops_stats['consent']['acceptance_rate'] ?? 0 ); ?>%
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Acceptance Rate', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border); font-size: 12px; color: var(--slos-primary); display: flex; align-items: center; gap: 4px;">
							<?php esc_html_e( 'View Details', 'shahi-legalflowsuite' ); ?>
							<span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px;"></span>
						</div>
					</div>
				</a>

				<!-- Cookie Scanner Card -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-compliance&tab=cookie-scanner' ) ); ?>" class="slos-ops-module-card" style="text-decoration: none; display: block;">
					<div style="padding: 20px; background: var(--slos-bg-input); border-radius: 12px; border: 2px solid transparent; transition: all 0.3s ease; height: 100%;">
						<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
							<div style="width: 40px; height: 40px; background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
								<span class="dashicons dashicons-food" style="color: white; font-size: 20px;"></span>
							</div>
							<div style="flex: 1;">
								<h4 style="margin: 0; font-size: 16px; color: var(--slos-text-primary); font-weight: 600;">
									<?php esc_html_e( 'Cookie Scanner', 'shahi-legalflowsuite' ); ?>
								</h4>
								<div style="font-size: 11px; color: var(--slos-text-muted); margin-top: 2px;">
									<?php esc_html_e( 'Cookie inventory', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
							<div>
								<div style="font-size: 24px; font-weight: 700; color: var(--slos-text-primary);">
									<?php echo esc_html( $ops_stats['cookies']['total_cookies'] ?? 0 ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Total Cookies', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
							<div>
								<div style="font-size: 24px; font-weight: 700; color: var(--slos-success);">
									<?php echo esc_html( $ops_stats['cookies']['categorization_rate'] ?? 0 ); ?>%
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Categorized', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border); font-size: 12px; color: var(--slos-primary); display: flex; align-items: center; gap: 4px;">
							<?php esc_html_e( 'View Scanner', 'shahi-legalflowsuite' ); ?>
							<span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px;"></span>
						</div>
					</div>
				</a>

				<!-- DSR Module Card -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-dsr-requests' ) ); ?>" class="slos-ops-module-card" style="text-decoration: none; display: block;">
					<div style="padding: 20px; background: var(--slos-bg-input); border-radius: 12px; border: 2px solid transparent; transition: all 0.3s ease; height: 100%;">
						<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
							<div style="width: 40px; height: 40px; background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
								<span class="dashicons dashicons-admin-users" style="color: white; font-size: 20px;"></span>
							</div>
							<div style="flex: 1;">
								<h4 style="margin: 0; font-size: 16px; color: var(--slos-text-primary); font-weight: 600;">
									<?php esc_html_e( 'Data Subject Rights', 'shahi-legalflowsuite' ); ?>
								</h4>
								<div style="font-size: 11px; color: var(--slos-text-muted); margin-top: 2px;">
									<?php esc_html_e( 'GDPR request queue', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
							<div>
								<div style="font-size: 24px; font-weight: 700; color: var(--slos-text-primary);">
									<?php echo esc_html( $ops_stats['dsr']['open_requests'] ?? 0 ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Open Requests', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
							<div>
								<?php 
								$overdue = $ops_stats['dsr']['overdue_requests'] ?? 0;
								$overdue_color = $overdue > 0 ? 'var(--slos-error)' : 'var(--slos-success)';
								?>
								<div style="font-size: 24px; font-weight: 700; color: <?php echo esc_attr( $overdue_color ); ?>;">
									<?php echo esc_html( $overdue ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Overdue', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border);">
							<div style="font-size: 11px; color: var(--slos-text-muted); margin-bottom: 4px;">
								<?php esc_html_e( 'SLA Compliance', 'shahi-legalflowsuite' ); ?>
							</div>
							<div style="display: flex; align-items: center; gap: 8px;">
								<div style="flex: 1; height: 6px; background: var(--slos-bg-secondary); border-radius: 3px; overflow: hidden;">
									<div style="width: <?php echo esc_attr( $ops_stats['dsr']['sla_compliance_rate'] ?? 0 ); ?>%; height: 100%; background: var(--slos-success); transition: width 0.3s ease;"></div>
								</div>
								<div style="font-size: 13px; font-weight: 600; color: var(--slos-text-primary);">
									<?php echo esc_html( round( $ops_stats['dsr']['sla_compliance_rate'] ?? 0 ) ); ?>%
								</div>
							</div>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border); font-size: 12px; color: var(--slos-primary); display: flex; align-items: center; gap: 4px;">
							<?php esc_html_e( 'View Queue', 'shahi-legalflowsuite' ); ?>
							<span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px;"></span>
						</div>
					</div>
				</a>

				<!-- Accessibility Module Card -->
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-accessibility' ) ); ?>" class="slos-ops-module-card" style="text-decoration: none; display: block;">
					<div style="padding: 20px; background: var(--slos-bg-input); border-radius: 12px; border: 2px solid transparent; transition: all 0.3s ease; height: 100%;">
						<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
							<div style="width: 40px; height: 40px; background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
								<span class="dashicons dashicons-universal-access-alt" style="color: white; font-size: 20px;"></span>
							</div>
							<div style="flex: 1;">
								<h4 style="margin: 0; font-size: 16px; color: var(--slos-text-primary); font-weight: 600;">
									<?php esc_html_e( 'Accessibility Scanner', 'shahi-legalflowsuite' ); ?>
								</h4>
								<div style="font-size: 11px; color: var(--slos-text-muted); margin-top: 2px;">
									<?php esc_html_e( 'WCAG compliance', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
							<div>
								<?php 
								$total_issues = $ops_stats['accessibility']['total_issues'] ?? 0;
								$issue_color = $total_issues > 0 ? 'var(--slos-error)' : 'var(--slos-success)';
								?>
								<div style="font-size: 24px; font-weight: 700; color: <?php echo esc_attr( $issue_color ); ?>;">
									<?php echo esc_html( $total_issues ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Total Issues', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
							<div>
								<div style="font-size: 24px; font-weight: 700; color: var(--slos-text-primary);">
									<?php echo esc_html( $ops_stats['accessibility']['pages_scanned'] ?? 0 ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Pages Scanned', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border);">
							<?php 
							$critical_issues = $ops_stats['accessibility']['critical_issues'] ?? 0;
							if ( $critical_issues > 0 ) :
							?>
								<div style="padding: 6px 10px; background: rgba(244, 67, 54, 0.1); border-radius: 6px; font-size: 12px; color: var(--slos-error); display: flex; align-items: center; gap: 6px;">
									<span class="dashicons dashicons-warning" style="font-size: 14px;"></span>
									<?php 
									/* translators: %d: Number of critical issues */
									printf( esc_html__( '%d Critical Issues', 'shahi-legalflowsuite' ), $critical_issues );
									?>
								</div>
							<?php else : ?>
								<div style="padding: 6px 10px; background: rgba(76, 175, 80, 0.1); border-radius: 6px; font-size: 12px; color: var(--slos-success); display: flex; align-items: center; gap: 6px;">
									<span class="dashicons dashicons-yes-alt" style="font-size: 14px;"></span>
									<?php esc_html_e( 'No Critical Issues', 'shahi-legalflowsuite' ); ?>
								</div>
							<?php endif; ?>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border); font-size: 12px; color: var(--slos-primary); display: flex; align-items: center; gap: 4px;">
							<?php esc_html_e( 'View Scanner', 'shahi-legalflowsuite' ); ?>
							<span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px;"></span>
						</div>
					</div>
				</a>

				<!-- Consent UX Health Card (Phase 2.3) -->
				<?php if ( ! empty( $ops_stats['consent_ux'] ) ) : ?>
				<div class="slos-ops-module-card" style="display: block;">
					<div style="padding: 20px; background: var(--slos-bg-input); border: 2px solid var(--slos-border); border-radius: 12px; height: 100%;">
						<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
							<div style="width: 40px; height: 40px; background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
								<span class="dashicons dashicons-visibility" style="color: white; font-size: 20px;"></span>
							</div>
							<div style="flex: 1;">
								<h4 style="margin: 0; font-size: 16px; color: var(--slos-text-primary); font-weight: 600;">
									<?php esc_html_e( 'Consent UX Health', 'shahi-legalflowsuite' ); ?>
								</h4>
								<div style="font-size: 11px; color: var(--slos-text-muted); margin-top: 2px;">
									<?php esc_html_e( 'Legal pages accessibility', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
							<div>
								<?php 
								$health_score = $ops_stats['consent_ux']['health_score'] ?? 0;
								$score_color = $health_score >= 80 ? 'var(--slos-success)' : ( $health_score >= 50 ? 'var(--slos-warning)' : 'var(--slos-error)' );
								?>
								<div style="font-size: 24px; font-weight: 700; color: <?php echo esc_attr( $score_color ); ?>;">
									<?php echo esc_html( $health_score ); ?><span style="font-size: 16px; opacity: 0.7;">/100</span>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'Health Score', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
							<div>
								<?php 
								$ux_issues = $ops_stats['consent_ux']['total_issues'] ?? 0;
								$ux_color = $ux_issues > 0 ? 'var(--slos-error)' : 'var(--slos-success)';
								?>
								<div style="font-size: 24px; font-weight: 700; color: <?php echo esc_attr( $ux_color ); ?>;">
									<?php echo esc_html( $ux_issues ); ?>
								</div>
								<div style="font-size: 11px; color: var(--slos-text-muted);">
									<?php esc_html_e( 'UX Issues', 'shahi-legalflowsuite' ); ?>
								</div>
							</div>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border);">
							<?php 
							$ux_critical = $ops_stats['consent_ux']['critical_issues'] ?? 0;
							$ux_pages = $ops_stats['consent_ux']['total_pages'] ?? 0;
							if ( $ux_critical > 0 ) :
							?>
								<div style="padding: 6px 10px; background: rgba(244, 67, 54, 0.1); border-radius: 6px; font-size: 12px; color: var(--slos-error); display: flex; align-items: center; gap: 6px;">
									<span class="dashicons dashicons-warning" style="font-size: 14px;"></span>
									<?php 
									/* translators: %d: Number of critical UX issues */
									printf( esc_html__( '%d Critical UX Issues', 'shahi-legalflowsuite' ), $ux_critical );
									?>
								</div>
							<?php else : ?>
								<div style="padding: 6px 10px; background: rgba(76, 175, 80, 0.1); border-radius: 6px; font-size: 12px; color: var(--slos-success); display: flex; align-items: center; gap: 6px;">
									<span class="dashicons dashicons-yes-alt" style="font-size: 14px;"></span>
									<?php 
									/* translators: %d: Number of pages scanned */
									printf( esc_html__( '%d Pages Scanned', 'shahi-legalflowsuite' ), $ux_pages );
									?>
								</div>
							<?php endif; ?>
						</div>
						<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--slos-border); font-size: 12px; color: var(--slos-text-muted); display: flex; align-items: center; gap: 4px;">
							<span class="dashicons dashicons-info-outline" style="font-size: 14px;"></span>
							<?php esc_html_e( 'Auto-scans on page save', 'shahi-legalflowsuite' ); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="slos-stats-grid">
    <div class="slos-stat-card accent">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-groups"></span>
            <?php esc_html_e( 'Total Consents', 'shahi-legalflowsuite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['total'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php if ( $stats['total'] > 0 ) : ?>
                <span class="trend up">
                    <span class="dashicons dashicons-chart-bar"></span>
                </span>
                <?php esc_html_e( 'all time', 'shahi-legalflowsuite' ); ?>
            <?php else : ?>
                <?php esc_html_e( 'No data yet', 'shahi-legalflowsuite' ); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="slos-stat-card success">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e( 'Accepted', 'shahi-legalflowsuite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['accepted'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php echo esc_html( $stats['acceptance_rate'] ); ?>% <?php esc_html_e( 'acceptance rate', 'shahi-legalflowsuite' ); ?>
        </div>
    </div>

    <div class="slos-stat-card danger">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-dismiss"></span>
            <?php esc_html_e( 'Rejected', 'shahi-legalflowsuite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['rejected'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php echo esc_html( $stats['rejection_rate'] ); ?>% <?php esc_html_e( 'rejection rate', 'shahi-legalflowsuite' ); ?>
        </div>
    </div>

    <div class="slos-stat-card warning">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e( 'Withdrawn', 'shahi-legalflowsuite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['withdrawn'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php esc_html_e( 'Requires follow-up', 'shahi-legalflowsuite' ); ?>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="slos-two-col-grid">
    <!-- Main Column -->
    <div class="slos-main-column">
        <!-- Compliance Readiness Score -->
        <div class="slos-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-heart"></span>
                    <?php esc_html_e( 'Compliance Readiness Score', 'shahi-legalflowsuite' ); ?>
                </h3>
                <span class="badge"><?php esc_html_e( 'Live', 'shahi-legalflowsuite' ); ?></span>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Multi-dimensional compliance configuration assessment. This score reflects how well your site is configured for privacy compliance across six key dimensions: cookies, legal docs, geo rules, consent data, scan freshness, and banner configuration.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-score-section">
                    <div class="slos-score-gauge <?php echo esc_attr( $grade_class ); ?>">
                        <svg viewBox="0 0 160 160">
                            <circle class="bg-circle" cx="80" cy="80" r="65"/>
                            <circle class="score-circle" cx="80" cy="80" r="65"
                                stroke-dasharray="<?php echo esc_attr( $circumference ); ?>"
                                stroke-dashoffset="<?php echo esc_attr( $offset ); ?>"/>
                        </svg>
                        <div class="slos-score-center">
                            <div class="slos-score-number"><?php echo esc_html( $stats['compliance_score'] ); ?></div>
                            <div class="slos-score-label"><?php esc_html_e( 'Score', 'shahi-legalflowsuite' ); ?></div>
                        </div>
                    </div>

                    <div class="slos-score-details">
                        <div class="slos-grade-display">
                            <div class="slos-grade-badge <?php echo esc_attr( $grade_class ); ?>">
                                <?php echo esc_html( $stats['grade'] ); ?>
                            </div>
                            <div class="slos-grade-text">
                                <div class="grade-title"><?php echo esc_html( $stats['grade_text'] ); ?></div>
                                <div class="grade-subtitle"><?php esc_html_e( 'Readiness Assessment', 'shahi-legalflowsuite' ); ?></div>
                            </div>
                        </div>

                        <div class="slos-compliance-badges">
                            <span class="slos-compliance-badge active">
                                <span class="dashicons dashicons-yes"></span>
                                GDPR
                            </span>
                            <span class="slos-compliance-badge active">
                                <span class="dashicons dashicons-yes"></span>
                                CCPA
                            </span>
                            <span class="slos-compliance-badge active">
                                <span class="dashicons dashicons-yes"></span>
                                LGPD
                            </span>
                            <span class="slos-compliance-badge">
                                <span class="dashicons dashicons-minus"></span>
                                ePrivacy
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dimension Breakdown -->
                <div class="slos-dimensions-grid" style="margin-top: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <?php if ( ! empty( $stats['dimensions'] ) ) : ?>
                        <?php foreach ( $stats['dimensions'] as $dimension_key => $dimension_data ) : 
                            $dimension_score = $dimension_data['score'];
                            $dimension_label = $dimension_labels[ $dimension_key ] ?? $dimension_key;
                            $dimension_icon = $dimension_icons[ $dimension_key ] ?? 'dashicons-admin-generic';
                            
                            // Color based on score
                            if ( $dimension_score >= 80 ) {
                                $dim_color = 'var(--slos-success)';
                            } elseif ( $dimension_score >= 60 ) {
                                $dim_color = 'var(--slos-warning)';
                            } else {
                                $dim_color = 'var(--slos-error)';
                            }
                        ?>
                            <div class="slos-dimension-card" style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px; text-align: center;">
                                <div style="font-size: 11px; color: var(--slos-text-muted); margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    <span class="dashicons <?php echo esc_attr( $dimension_icon ); ?>" style="font-size: 12px;"></span>
                                    <?php echo esc_html( $dimension_label ); ?>
                                </div>
                                <div style="font-size: 24px; font-weight: 700; color: <?php echo esc_attr( $dim_color ); ?>;">
                                    <?php echo esc_html( $dimension_score ); ?>%
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Disclaimer -->
                <div class="slos-disclaimer" style="margin-top: 20px; padding: 12px; background: rgba(255, 193, 7, 0.1); border-left: 3px solid var(--slos-warning); border-radius: 4px;">
                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                        <span class="dashicons dashicons-info" style="color: var(--slos-warning); margin-top: 2px;"></span>
                        <div style="flex: 1; font-size: 13px; color: var(--slos-text-secondary);">
                            <strong><?php esc_html_e( 'Note:', 'shahi-legalflowsuite' ); ?></strong>
                            <?php esc_html_e( 'This Readiness Score reflects configuration completeness and is not a guarantee of legal compliance. Please consult with a legal professional for compliance advice.', 'shahi-legalflowsuite' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consent Trends Chart -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-line"></span>
                    <?php esc_html_e( 'Consent Trends', 'shahi-legalflowsuite' ); ?>
                </h3>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <select id="slos-chart-range" class="slos-select-small" style="font-size: 13px;">
                        <option value="7"><?php esc_html_e( 'Last 7 Days', 'shahi-legalflowsuite' ); ?></option>
                        <option value="30" selected><?php esc_html_e( 'Last 30 Days', 'shahi-legalflowsuite' ); ?></option>
                        <option value="90"><?php esc_html_e( 'Last 90 Days', 'shahi-legalflowsuite' ); ?></option>
                    </select>
                    <select id="slos-chart-groupby" class="slos-select-small" style="font-size: 13px;">
                        <option value="none"><?php esc_html_e( 'All Consents', 'shahi-legalflowsuite' ); ?></option>
                        <option value="status"><?php esc_html_e( 'By Status', 'shahi-legalflowsuite' ); ?></option>
                        <option value="type"><?php esc_html_e( 'By Type', 'shahi-legalflowsuite' ); ?></option>
                        <option value="region"><?php esc_html_e( 'By Region', 'shahi-legalflowsuite' ); ?></option>
                    </select>
                </div>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Time-series visualization of consent activity. Track daily patterns, compare acceptance rates, and analyze trends across different time periods and consent categories.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <?php if ( $stats['total'] > 0 ) : ?>
                    <div style="position: relative; height: 320px; margin-bottom: 16px;">
                        <canvas id="slos-consent-trends-chart"></canvas>
                    </div>
                    <div class="slos-chart-meta" style="display: flex; gap: 24px; padding: 12px; background: var(--slos-bg-input); border-radius: 6px; font-size: 13px;">
                        <div>
                            <span style="color: var(--slos-text-muted);"><?php esc_html_e( 'Total:', 'shahi-legalflowsuite' ); ?></span>
                            <strong id="slos-chart-total"><?php echo esc_html( number_format( $stats['total'] ) ); ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--slos-text-muted);"><?php esc_html_e( 'Avg/Day:', 'shahi-legalflowsuite' ); ?></span>
                            <strong id="slos-chart-average">-</strong>
                        </div>
                        <div>
                            <span style="color: var(--slos-text-muted);"><?php esc_html_e( 'Interval:', 'shahi-legalflowsuite' ); ?></span>
                            <strong id="slos-chart-interval"><?php esc_html_e( 'Daily', 'shahi-legalflowsuite' ); ?></strong>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="slos-empty-state">
                        <span class="dashicons dashicons-chart-line"></span>
                        <p><?php esc_html_e( 'No consent data available yet. Chart will populate as users interact with your consent banner.', 'shahi-legalflowsuite' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e( 'Recent Activity', 'shahi-legalflowsuite' ); ?>
                </h3>
                <div style="display: flex; gap: 8px;">
                    <button class="slos-btn-ghost" data-limit="10">10</button>
                    <button class="slos-btn-ghost" data-limit="25">25</button>
                    <button class="slos-btn-ghost" data-limit="50">50</button>
                </div>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Latest consent interactions from your visitors. Click the view button to see full consent details. Use the number buttons to adjust how many records appear. For full history, visit Consent Records tab.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body" style="padding: 0;">
                <?php if ( ! empty( $recent_activity ) ) : ?>
                    <table class="slos-data-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'ID', 'shahi-legalflowsuite' ); ?></th>
                                <th><?php esc_html_e( 'User', 'shahi-legalflowsuite' ); ?></th>
                                <th><?php esc_html_e( 'Type', 'shahi-legalflowsuite' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'shahi-legalflowsuite' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_activity as $consent ) : 
                                $consent_arr = (array) $consent;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html( $consent_arr['id'] ?? 0 ); ?></strong></td>
                                    <td>
                                        <?php 
                                        $user_id = $consent_arr['user_id'] ?? 0;
                                        if ( $user_id > 0 ) {
                                            $user = get_userdata( $user_id );
                                            echo esc_html( $user ? $user->display_name : "User #$user_id" );
                                        } else {
                                            echo '<span style="color: var(--slos-text-muted);">' . esc_html__( 'Guest', 'shahi-legalflowsuite' ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="slos-type-badge"><?php echo esc_html( ucfirst( $consent_arr['type'] ?? 'unknown' ) ); ?></span></td>
                                    <td>
                                        <span class="slos-status-badge <?php echo esc_attr( $consent_arr['status'] ?? '' ); ?>">
                                            <?php echo esc_html( ucfirst( $consent_arr['status'] ?? 'unknown' ) ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $date = $consent_arr['created_at'] ?? '';
                                        echo $date ? esc_html( human_time_diff( strtotime( $date ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalflowsuite' ) ) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <button class="slos-btn-ghost view-consent-btn" data-action="view" data-id="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="slos-empty-state">
                        <span class="dashicons dashicons-privacy"></span>
                        <p><?php esc_html_e( 'No consent activity recorded yet. Data will appear as users interact with your consent banner.', 'shahi-legalflowsuite' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $recent_activity ) ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'records', admin_url( 'admin.php?page=slos-compliance' ) ) ); ?>" class="slos-view-all">
                    <?php esc_html_e( 'View All Records', 'shahi-legalflowsuite' ); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="slos-sidebar-column">
        <!-- Quick Actions -->
        <div class="slos-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-superhero"></span>
                    <?php esc_html_e( 'Quick Actions', 'shahi-legalflowsuite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Common compliance tasks at your fingertips. Scan for cookies, customize your consent banner, export data for audits, or email reports to stakeholders.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-quick-actions">
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'cookies', admin_url( 'admin.php?page=slos-compliance' ) ) ); ?>" class="slos-quick-action">
                        <span class="dashicons dashicons-search"></span>
                        <span><?php esc_html_e( 'Scan Cookies', 'shahi-legalflowsuite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </a>
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'banner', admin_url( 'admin.php?page=slos-compliance' ) ) ); ?>" class="slos-quick-action">
                        <span class="dashicons dashicons-admin-customizer"></span>
                        <span><?php esc_html_e( 'Customize Banner', 'shahi-legalflowsuite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </a>
                    <button class="slos-quick-action" id="slos-export-csv-btn" type="button">
                        <span class="dashicons dashicons-download"></span>
                        <span><?php esc_html_e( 'Export CSV', 'shahi-legalflowsuite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </button>
                    <button class="slos-quick-action" id="slos-export-pdf-btn" type="button">
                        <span class="dashicons dashicons-media-document"></span>
                        <span><?php esc_html_e( 'Export PDF Report', 'shahi-legalflowsuite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </button>
                    <button class="slos-quick-action" id="slos-export-audit-btn" type="button">
                        <span class="dashicons dashicons-list-view"></span>
                        <span><?php esc_html_e( 'Export Audit Log', 'shahi-legalflowsuite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Consent Breakdown -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-pie"></span>
                    <?php esc_html_e( 'Consent Breakdown', 'shahi-legalflowsuite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Consent rates by category type. Necessary cookies are always 100% (required). Analytics, Marketing, and Preferences reflect actual user choices on your site.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-consent-breakdown">
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Necessary', 'shahi-legalflowsuite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill necessary" style="width: 100%;"></div>
                        </div>
                        <span class="slos-breakdown-value">100%</span>
                    </div>
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Analytics', 'shahi-legalflowsuite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill analytics" style="width: <?php echo esc_attr( $type_percentages['analytics'] ?? 0 ); ?>%;"></div>
                        </div>
                        <span class="slos-breakdown-value"><?php echo esc_html( $type_percentages['analytics'] ?? 0 ); ?>%</span>
                    </div>
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Marketing', 'shahi-legalflowsuite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill marketing" style="width: <?php echo esc_attr( $type_percentages['marketing'] ?? 0 ); ?>%;"></div>
                        </div>
                        <span class="slos-breakdown-value"><?php echo esc_html( $type_percentages['marketing'] ?? 0 ); ?>%</span>
                    </div>
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Preferences', 'shahi-legalflowsuite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill preferences" style="width: <?php echo esc_attr( $type_percentages['preferences'] ?? 0 ); ?>%;"></div>
                        </div>
                        <span class="slos-breakdown-value"><?php echo esc_html( $type_percentages['preferences'] ?? 0 ); ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Geo Rules -->
        <?php
        // Get geo rules from database
        $geo_rules = get_option( 'slos_geo_rules', array() );
        $active_rules = array_filter( $geo_rules, function( $rule ) {
            // Support both 'active' field and 'status' field
            $is_active = ! empty( $rule['active'] ) || ( isset( $rule['status'] ) && $rule['status'] === 'active' );
            return $is_active;
        });
        
        // Flag mapping for regions
        $region_flags = array(
            'EU' => '🇪🇺', 'DE' => '🇩🇪', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'ES' => '🇪🇸', 'NL' => '🇳🇱', 'BE' => '🇧🇪', 'AT' => '🇦🇹', 'PL' => '🇵🇱', 'SE' => '🇸🇪',
            'US' => '🇺🇸', 'US-CA' => '🇺🇸', 'CA' => '🇨🇦', 'BR' => '🇧🇷', 'UK' => '🇬🇧', 'GB' => '🇬🇧', 'AU' => '🇦🇺', 'JP' => '🇯🇵', 'KR' => '🇰🇷', 'IN' => '🇮🇳', 'CN' => '🇨🇳',
            'GLOBAL' => '🌍', 'DEFAULT' => '🌍', 'REST' => '🌍'
        );
        ?>
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-admin-site-alt3"></span>
                    <?php esc_html_e( 'Active Geo Rules', 'shahi-legalflowsuite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Region-specific consent rules currently active. Configure rules in the Geo Rules tab to show appropriate consent banners based on visitor location.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <?php if ( ! empty( $active_rules ) ) : ?>
                <div class="slos-region-list">
                    <?php foreach ( array_slice( $active_rules, 0, 4 ) as $rule ) : 
                        $rule_name = $rule['name'] ?? $rule['region'] ?? 'Unknown';
                        $framework = $rule['framework'] ?? $rule['regulation'] ?? '';
                        // Determine flag from countries or name
                        $countries = $rule['countries'] ?? array();
                        $first_country = ! empty( $countries ) ? $countries[0] : strtoupper( substr( $rule_name, 0, 2 ) );
                        $flag = $region_flags[ $first_country ] ?? $region_flags['GLOBAL'];
                        if ( stripos( $rule_name, 'EU' ) !== false || stripos( $rule_name, 'Europe' ) !== false ) {
                            $flag = '🇪🇺';
                        } elseif ( stripos( $rule_name, 'California' ) !== false ) {
                            $flag = '🇺🇸';
                        } elseif ( stripos( $rule_name, 'Brazil' ) !== false ) {
                            $flag = '🇧🇷';
                        } elseif ( stripos( $rule_name, 'Rest' ) !== false || stripos( $rule_name, 'World' ) !== false || stripos( $rule_name, 'Default' ) !== false ) {
                            $flag = '🌍';
                        }
                    ?>
                    <div class="slos-region-item">
                        <span class="slos-region-flag"><?php echo esc_html( $flag ); ?></span>
                        <div class="slos-region-info">
                            <div class="slos-region-name"><?php echo esc_html( $rule_name ); ?></div>
                            <div class="slos-region-framework"><?php echo esc_html( $framework ); ?></div>
                        </div>
                        <span class="slos-region-percent" style="color: var(--slos-success);"><span class="dashicons dashicons-yes-alt"></span></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="slos-empty-state" style="padding: 20px; text-align: center;">
                    <span class="dashicons dashicons-admin-site-alt3" style="font-size: 32px; color: var(--slos-text-muted);"></span>
                    <p style="color: var(--slos-text-muted); margin-top: 8px;"><?php esc_html_e( 'No geo rules configured. Visit the Geo Rules tab to set up regional compliance.', 'shahi-legalflowsuite' ); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Legal Documents Status -->
        <?php if ( ! empty( $stats['legal_docs'] ) ) : 
            $legal_docs = $stats['legal_docs'];
            $doc_percentage = $legal_docs['percentage'] ?? 0;
            
            // Determine status color
            if ( $doc_percentage >= 100 ) {
                $doc_status_color = 'var(--slos-success)';
                $doc_status_text = __( 'Complete', 'shahi-legalflowsuite' );
            } elseif ( $doc_percentage >= 66 ) {
                $doc_status_color = 'var(--slos-warning)';
                $doc_status_text = __( 'Partial', 'shahi-legalflowsuite' );
            } else {
                $doc_status_color = 'var(--slos-error)';
                $doc_status_text = __( 'Incomplete', 'shahi-legalflowsuite' );
            }
        ?>
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-media-document"></span>
                    <?php esc_html_e( 'Legal Documents', 'shahi-legalflowsuite' ); ?>
                </h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-document-hub' ) ); ?>" class="slos-btn-ghost" style="font-size: 12px;">
                    <?php esc_html_e( 'Manage', 'shahi-legalflowsuite' ); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </a>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Status of core compliance documents. Keep Cookie Policy, Privacy Policy, and Accessibility Statement published and up-to-date with current site data.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <!-- Progress Summary -->
                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: var(--slos-text-secondary);">
                            <?php 
                            printf( 
                                esc_html__( '%d of %d documents published', 'shahi-legalflowsuite' ),
                                $legal_docs['published'] ?? 0,
                                $legal_docs['total'] ?? 3
                            );
                            ?>
                        </span>
                        <span style="font-weight: 600; color: <?php echo esc_attr( $doc_status_color ); ?>;">
                            <?php echo esc_html( $doc_status_text ); ?>
                        </span>
                    </div>
                    <div class="slos-breakdown-bar">
                        <div class="slos-breakdown-fill success" style="width: <?php echo esc_attr( $doc_percentage ); ?>%; background: <?php echo esc_attr( $doc_status_color ); ?>;"></div>
                    </div>
                </div>

                <!-- Document List -->
                <div class="slos-document-list">
                    <?php if ( ! empty( $legal_docs['docs'] ) ) : ?>
                        <?php foreach ( $legal_docs['docs'] as $doc_id => $doc ) : 
                            $is_published = ( $doc['status'] ?? '' ) === 'published';
                            $is_stale = $doc['stale'] ?? false;
                            
                            if ( $is_published && ! $is_stale ) {
                                $icon_class = 'dashicons-yes-alt';
                                $icon_color = 'var(--slos-success)';
                                $status_label = __( 'Published', 'shahi-legalflowsuite' );
                            } elseif ( $is_published && $is_stale ) {
                                $icon_class = 'dashicons-warning';
                                $icon_color = 'var(--slos-warning)';
                                $status_label = __( 'Needs Update', 'shahi-legalflowsuite' );
                            } else {
                                $icon_class = 'dashicons-minus';
                                $icon_color = 'var(--slos-text-muted)';
                                $status_label = __( 'Not Generated', 'shahi-legalflowsuite' );
                            }
                        ?>
                        <div class="slos-document-item" style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--slos-border);">
                            <span class="dashicons <?php echo esc_attr( $icon_class ); ?>" style="color: <?php echo esc_attr( $icon_color ); ?>; margin-right: 8px;"></span>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; font-size: 13px;"><?php echo esc_html( $doc['title'] ?? ucfirst( str_replace( '-', ' ', $doc_id ) ) ); ?></div>
                                <div style="font-size: 11px; color: var(--slos-text-muted);"><?php echo esc_html( $status_label ); ?></div>
                            </div>
                            <?php if ( $is_stale ) : ?>
                                <span class="badge" style="background: var(--slos-warning); color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                                    <?php esc_html_e( 'Outdated', 'shahi-legalflowsuite' ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Stale Warning -->
                <?php if ( ! empty( $legal_docs['stale'] ) ) : ?>
                    <div class="slos-disclaimer" style="margin-top: 12px; padding: 10px; background: rgba(255, 193, 7, 0.1); border-left: 3px solid var(--slos-warning); border-radius: 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <span class="dashicons dashicons-info" style="color: var(--slos-warning); margin-top: 1px; font-size: 14px;"></span>
                            <div style="flex: 1; font-size: 12px; color: var(--slos-text-secondary);">
                                <?php 
                                printf(
                                    esc_html__( '%d document(s) need regeneration because cookie data has changed.', 'shahi-legalflowsuite' ),
                                    $legal_docs['stale']
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alerts -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-bell"></span>
                    <?php esc_html_e( 'Alerts', 'shahi-legalflowsuite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Compliance warnings requiring attention. Address alerts promptly to maintain full regulatory compliance. Yellow = action recommended, Red = urgent action required.', 'shahi-legalflowsuite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-alerts-list">
                    <?php if ( $stats['withdrawn'] > 0 ) : ?>
                        <div class="slos-alert-item warning">
                            <span class="dashicons dashicons-warning slos-alert-icon"></span>
                            <span class="slos-alert-text">
                                <?php 
                                printf( 
                                    esc_html__( '%d withdrawn consents require follow-up action.', 'shahi-legalflowsuite' ),
                                    $stats['withdrawn']
                                );
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Check for uncategorized cookies from actual data
                    $detected_cookies = get_option( 'slos_detected_cookies', array() );
                    $uncategorized = array_filter( $detected_cookies, function( $cookie ) {
                        $category = $cookie['category'] ?? $cookie['type'] ?? '';
                        return empty( $category ) || $category === 'unknown' || $category === 'uncategorized';
                    });
                    $uncategorized_count = count( $uncategorized );
                    if ( $uncategorized_count > 0 ) : ?>
                    <div class="slos-alert-item info">
                        <span class="dashicons dashicons-info slos-alert-icon"></span>
                        <span class="slos-alert-text">
                            <?php 
                            printf(
                                esc_html__( 'Cookie scanner detected %d cookies that need categorization.', 'shahi-legalflowsuite' ),
                                $uncategorized_count
                            );
                            ?>
                        </span>
                    </div>
                    <?php elseif ( empty( $detected_cookies ) ) : ?>
                    <div class="slos-alert-item info">
                        <span class="dashicons dashicons-info slos-alert-icon"></span>
                        <span class="slos-alert-text">
                            <?php esc_html_e( 'No cookies scanned yet. Run a cookie scan to detect cookies on your site.', 'shahi-legalflowsuite' ); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ( $stats['compliance_score'] < 70 ) : ?>
                        <div class="slos-alert-item error">
                            <span class="dashicons dashicons-dismiss slos-alert-icon"></span>
                            <span class="slos-alert-text">
                                <?php esc_html_e( 'Compliance score is below threshold. Review your consent settings.', 'shahi-legalflowsuite' ); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Consent Detail Modal -->
<div class="slos-modal-overlay" id="consent-detail-modal" style="display: none;">
    <div class="slos-modal-content" style="background: var(--slos-bg-card); border: 1px solid var(--slos-border); border-radius: 16px; width: 100%; max-width: 500px; padding: 24px;">
        <div class="slos-modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: var(--slos-text-primary);"><?php esc_html_e( 'Consent Details', 'shahi-legalflowsuite' ); ?></h3>
            <button class="slos-modal-close" id="close-consent-modal" type="button" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: transparent; border: 1px solid var(--slos-border); border-radius: 8px; color: var(--slos-text-muted); cursor: pointer;">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="slos-modal-body" id="consent-detail-content">
            <p style="color: var(--slos-text-muted);"><?php esc_html_e( 'Loading...', 'shahi-legalflowsuite' ); ?></p>
        </div>
    </div>
</div>

<style>
.slos-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    
    // Activity limit buttons
    $('.slos-btn-ghost[data-limit]').on('click', function() {
        const limit = $(this).data('limit');
        window.location.href = '<?php echo esc_js( admin_url( 'admin.php?page=slos-compliance&tab=dashboard' ) ); ?>&limit=' + limit;
    });
    
    // View consent detail
    $('.view-consent-btn').on('click', function() {
        const id = $(this).data('id');
        showConsentDetail(id);
    });
    
    function showConsentDetail(id) {
        $('#consent-detail-content').html('<p style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Loading...', 'shahi-legalflowsuite' ) ); ?></p>');
        $('#consent-detail-modal').show();
        
        $.ajax({
            url: API_BASE + '/consents/' + id,
            method: 'GET',
            headers: { 'X-WP-Nonce': NONCE },
            success: function(response) {
                const data = response.data || response;
                let html = '<div style="display: grid; gap: 12px;">';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;">ID</div><div style="color: var(--slos-text-primary);">#' + (data.id || id) + '</div></div>';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;"><?php echo esc_js( __( 'Type', 'shahi-legalflowsuite' ) ); ?></div><div style="color: var(--slos-text-primary);">' + (data.type || 'N/A') + '</div></div>';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;"><?php echo esc_js( __( 'Status', 'shahi-legalflowsuite' ) ); ?></div><div style="color: var(--slos-text-primary);">' + (data.status || 'N/A') + '</div></div>';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;"><?php echo esc_js( __( 'Created', 'shahi-legalflowsuite' ) ); ?></div><div style="color: var(--slos-text-primary);">' + (data.created_at || 'N/A') + '</div></div>';
                
                // Version information (added in 3.1.1)
                if (data.banner_version || data.policy_version) {
                    html += '<div style="grid-column: 1 / -1; padding: 12px; background: var(--slos-bg-input); border-radius: 8px; border-left: 3px solid var(--slos-primary);">';
                    html += '<div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase; margin-bottom: 8px;"><?php echo esc_js( __( 'Version Information', 'shahi-legalflowsuite' ) ); ?></div>';
                    if (data.banner_version) {
                        html += '<div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Banner Version:', 'shahi-legalflowsuite' ) ); ?></span><span style="color: var(--slos-text-primary); font-family: monospace;">' + data.banner_version + '</span></div>';
                    }
                    if (data.policy_version) {
                        html += '<div style="display: flex; justify-content: space-between;"><span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Policy Version:', 'shahi-legalflowsuite' ) ); ?></span><span style="color: var(--slos-text-primary); font-family: monospace;">' + data.policy_version + '</span></div>';
                    }
                    html += '</div>';
                }
                
                html += '</div>';
                $('#consent-detail-content').html(html);
            },
            error: function() {
                $('#consent-detail-content').html('<p style="color: var(--slos-error);"><?php echo esc_js( __( 'Error loading consent details.', 'shahi-legalflowsuite' ) ); ?></p>');
            }
        });
    }
    
    // Close modal
    $('#close-consent-modal, #consent-detail-modal').on('click', function(e) {
        if (e.target === this) {
            $('#consent-detail-modal').hide();
        }
    });
    
    // Export to CSV
    $('#slos-export-csv').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).find('span:first').removeClass('dashicons-download').addClass('dashicons-update');
        
        // Trigger download
        window.location.href = API_BASE + '/consents/export/download?format=csv&_wpnonce=' + NONCE;
        
        setTimeout(function() {
            $btn.prop('disabled', false).find('span:first').removeClass('dashicons-update').addClass('dashicons-download');
        }, 2000);
    });
    
    // Email Report
    $('#slos-send-report').on('click', function() {
        const email = prompt('<?php echo esc_js( __( 'Enter email address for the report:', 'shahi-legalflowsuite' ) ); ?>', '<?php echo esc_js( get_option( 'admin_email' ) ); ?>');
        
        if (email) {
            const $btn = $(this);
            $btn.prop('disabled', true);
            
            $.ajax({
                url: API_BASE + '/consents/export',
                method: 'GET',
                data: { format: 'email', email: email },
                headers: { 'X-WP-Nonce': NONCE },
                success: function() {
                    alert('<?php echo esc_js( __( 'Report sent successfully!', 'shahi-legalflowsuite' ) ); ?>');
                },
                error: function() {
                    alert('<?php echo esc_js( __( 'Error sending report. Please try again.', 'shahi-legalflowsuite' ) ); ?>');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }
    });
});
</script>