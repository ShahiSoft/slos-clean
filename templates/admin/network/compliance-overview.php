<?php
/**
 * Network Compliance Overview Template
 *
 * Multisite network view of compliance across all sites.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin/Network
 * @since      3.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ShahiLegalFlowSuite\Admin\Network_Compliance_Dashboard;
?>

<div class="wrap slos-network-compliance">
	
	<!-- Header -->
	<div class="slos-header">
		<div>
			<h1>
				<span class="dashicons dashicons-shield-alt"></span>
				<?php esc_html_e( 'Network Compliance Overview', 'shahi-legalflowsuite' ); ?>
			</h1>
			<p class="description">
				<?php esc_html_e( 'Compliance status across all sites in your network', 'shahi-legalflowsuite' ); ?>
			</p>
		</div>
		<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=slos-network-compliance&refresh=1' ) ); ?>" class="button">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Refresh Data', 'shahi-legalflowsuite' ); ?>
		</a>
	</div>

	<!-- Network Summary Cards -->
	<div class="slos-stats-grid">
		
		<!-- Total Sites -->
		<div class="slos-stat-card">
			<div class="slos-stat-icon slos-icon-info">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="slos-stat-content">
				<div class="slos-stat-value"><?php echo esc_html( $network_stats['total_sites'] ); ?></div>
				<div class="slos-stat-label"><?php esc_html_e( 'Total Sites', 'shahi-legalflowsuite' ); ?></div>
			</div>
		</div>

		<!-- Average Score -->
		<div class="slos-stat-card">
			<div class="slos-stat-icon slos-icon-<?php echo esc_attr( Network_Compliance_Dashboard::get_score_color( $network_stats['avg_score'] ) ); ?>">
				<span class="dashicons dashicons-chart-area"></span>
			</div>
			<div class="slos-stat-content">
				<div class="slos-stat-value"><?php echo esc_html( $network_stats['avg_score'] ); ?>%</div>
				<div class="slos-stat-label"><?php esc_html_e( 'Average Readiness Score', 'shahi-legalflowsuite' ); ?></div>
			</div>
		</div>

		<!-- Critical Sites -->
		<div class="slos-stat-card">
			<div class="slos-stat-icon slos-icon-error">
				<span class="dashicons dashicons-warning"></span>
			</div>
			<div class="slos-stat-content">
				<div class="slos-stat-value"><?php echo esc_html( $network_stats['critical_sites'] ); ?></div>
				<div class="slos-stat-label"><?php esc_html_e( 'Critical Sites (Score < 40)', 'shahi-legalflowsuite' ); ?></div>
			</div>
		</div>

		<!-- Low Score Sites -->
		<div class="slos-stat-card">
			<div class="slos-stat-icon slos-icon-warning">
				<span class="dashicons dashicons-flag"></span>
			</div>
			<div class="slos-stat-content">
				<div class="slos-stat-value"><?php echo esc_html( $network_stats['low_score_sites'] ); ?></div>
				<div class="slos-stat-label"><?php esc_html_e( 'Sites Need Attention (Score < 60)', 'shahi-legalflowsuite' ); ?></div>
			</div>
		</div>

	</div>

	<!-- Issues Breakdown -->
	<div class="slos-card">
		<h2><?php esc_html_e( 'Common Issues Across Sites', 'shahi-legalflowsuite' ); ?></h2>
		<div class="slos-issues-grid">
			<?php foreach ( $network_stats['issues_by_type'] as $issue_type => $count ) : ?>
				<div class="slos-issue-item">
					<div class="slos-issue-icon">
						<?php
						$icons = array(
							'banner'        => 'format-image',
							'geo_rules'     => 'admin-site-alt3',
							'legal_docs'    => 'media-document',
							'accessibility' => 'universal-access-alt',
							'dsr'           => 'privacy',
						);
						?>
						<span class="dashicons dashicons-<?php echo esc_attr( $icons[ $issue_type ] ?? 'admin-generic' ); ?>"></span>
					</div>
					<div class="slos-issue-count"><?php echo esc_html( $count ); ?></div>
					<div class="slos-issue-label">
						<?php
						$labels = array(
							'banner'        => __( 'Banner Config', 'shahi-legalflowsuite' ),
							'geo_rules'     => __( 'Geo Rules', 'shahi-legalflowsuite' ),
							'legal_docs'    => __( 'Legal Docs', 'shahi-legalflowsuite' ),
							'accessibility' => __( 'Accessibility', 'shahi-legalflowsuite' ),
							'dsr'           => __( 'DSR Settings', 'shahi-legalflowsuite' ),
						);
						echo esc_html( $labels[ $issue_type ] ?? ucfirst( $issue_type ) );
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Sites Table -->
	<div class="slos-card">
		<div class="slos-card-header">
			<h2><?php esc_html_e( 'Sites Compliance Status', 'shahi-legalflowsuite' ); ?></h2>
			<div class="slos-actions">
				<select id="slos-filter-grade" class="slos-filter">
					<option value=""><?php esc_html_e( 'All Grades', 'shahi-legalflowsuite' ); ?></option>
					<option value="A"><?php esc_html_e( 'Grade A', 'shahi-legalflowsuite' ); ?></option>
					<option value="B"><?php esc_html_e( 'Grade B', 'shahi-legalflowsuite' ); ?></option>
					<option value="C"><?php esc_html_e( 'Grade C', 'shahi-legalflowsuite' ); ?></option>
					<option value="D"><?php esc_html_e( 'Grade D', 'shahi-legalflowsuite' ); ?></option>
					<option value="F"><?php esc_html_e( 'Grade F', 'shahi-legalflowsuite' ); ?></option>
				</select>
			</div>
		</div>

		<table class="wp-list-table widefat fixed striped slos-sites-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Site', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'URL', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Readiness Score', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Grade', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Issues', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Last Scan', 'shahi-legalflowsuite' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $sites ) ) : ?>
					<tr>
						<td colspan="7" class="slos-empty">
							<?php esc_html_e( 'No sites found.', 'shahi-legalflowsuite' ); ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $sites as $site ) : ?>
						<tr data-grade="<?php echo esc_attr( $site['grade'] ); ?>">
							<td>
								<strong><?php echo esc_html( $site['name'] ); ?></strong>
								<br>
								<small class="slos-muted"><?php /* translators: %d: site ID (blog_id) */ echo esc_html( sprintf( __( 'Site ID: %d', 'shahi-legalflowsuite' ), $site['blog_id'] ) ); ?></small>
							</td>
							<td>
								<a href="<?php echo esc_url( $site['url'] ); ?>" target="_blank" rel="noopener">
									<?php echo esc_html( $site['url'] ); ?>
									<span class="dashicons dashicons-external"></span>
								</a>
							</td>
							<td>
								<div class="slos-score slos-score-<?php echo esc_attr( Network_Compliance_Dashboard::get_score_color( $site['score'] ) ); ?>">
									<div class="slos-score-circle">
										<span><?php echo esc_html( $site['score'] ); ?>%</span>
									</div>
								</div>
							</td>
							<td>
								<span class="slos-grade slos-grade-<?php echo esc_attr( Network_Compliance_Dashboard::get_grade_color( $site['grade'] ) ); ?>">
									<?php echo esc_html( $site['grade'] ); ?>
								</span>
							</td>
							<td>
								<?php if ( $site['issues_count'] > 0 ) : ?>
									<span class="slos-issues-badge slos-badge-warning">
									<?php echo esc_html( sprintf( /* translators: %d: number of issues found */ _n( '%d issue', '%d issues', $site['issues_count'], 'shahi-legalflowsuite' ), $site['issues_count'] ) ); ?>
									</span>
								<?php else : ?>
									<span class="slos-issues-badge slos-badge-success">
										<?php esc_html_e( 'No issues', 'shahi-legalflowsuite' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<small class="slos-muted"><?php echo esc_html( $site['last_scan'] ); ?></small>
							</td>
							<td>
								<a href="<?php echo esc_url( $site['admin_url'] ); ?>" class="button button-small" target="_blank">
									<?php esc_html_e( 'View Dashboard', 'shahi-legalflowsuite' ); ?>
									<span class="dashicons dashicons-external"></span>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</div>

<style>
/* Network Compliance Styles */
.slos-network-compliance {
	max-width: 1400px;
	margin: 20px;
}

.slos-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 24px;
}

.slos-header h1 {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 8px;
}

.slos-header .description {
	margin: 0;
}

/* Stats Grid */
.slos-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-bottom: 24px;
}

.slos-stat-card {
	background: white;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	display: flex;
	align-items: center;
	gap: 16px;
}

.slos-stat-icon {
	width: 48px;
	height: 48px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24px;
}

.slos-stat-icon.slos-icon-success { background: #d4edda; color: #155724; }
.slos-stat-icon.slos-icon-info { background: #d1ecf1; color: #0c5460; }
.slos-stat-icon.slos-icon-warning { background: #fff3cd; color: #856404; }
.slos-stat-icon.slos-icon-error { background: #f8d7da; color: #721c24; }

.slos-stat-content {
	flex: 1;
}

.slos-stat-value {
	font-size: 32px;
	font-weight: 700;
	line-height: 1;
	margin-bottom: 4px;
}

.slos-stat-label {
	font-size: 13px;
	color: #666;
}

/* Card */
.slos-card {
	background: white;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 24px;
	margin-bottom: 24px;
}

.slos-card h2 {
	margin: 0 0 16px;
	font-size: 18px;
	font-weight: 600;
}

.slos-card-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 16px;
}

/* Issues Grid */
.slos-issues-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
	gap: 16px;
}

.slos-issue-item {
	text-align: center;
	padding: 16px;
	background: #f9f9f9;
	border-radius: 6px;
}

.slos-issue-icon {
	font-size: 32px;
	color: #666;
	margin-bottom: 8px;
}

.slos-issue-count {
	font-size: 24px;
	font-weight: 700;
	color: #333;
	margin-bottom: 4px;
}

.slos-issue-label {
	font-size: 12px;
	color: #666;
}

/* Sites Table */
.slos-sites-table {
	margin-top: 0;
}

.slos-sites-table td {
	vertical-align: middle;
}

.slos-sites-table .slos-muted {
	color: #666;
	font-size: 12px;
}

.slos-score {
	display: inline-block;
}

.slos-score-circle {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 14px;
}

.slos-score-success .slos-score-circle { background: #d4edda; color: #155724; }
.slos-score-info .slos-score-circle { background: #d1ecf1; color: #0c5460; }
.slos-score-warning .slos-score-circle { background: #fff3cd; color: #856404; }
.slos-score-error .slos-score-circle { background: #f8d7da; color: #721c24; }

.slos-grade {
	display: inline-block;
	width: 32px;
	height: 32px;
	line-height: 32px;
	text-align: center;
	font-weight: 700;
	border-radius: 4px;
}

.slos-grade-success { background: #d4edda; color: #155724; }
.slos-grade-info { background: #d1ecf1; color: #0c5460; }
.slos-grade-warning { background: #fff3cd; color: #856404; }
.slos-grade-error { background: #f8d7da; color: #721c24; }

.slos-issues-badge {
	display: inline-block;
	padding: 4px 8px;
	border-radius: 4px;
	font-size: 12px;
	font-weight: 500;
}

.slos-badge-success { background: #d4edda; color: #155724; }
.slos-badge-warning { background: #fff3cd; color: #856404; }

.slos-empty {
	text-align: center;
	padding: 32px !important;
	color: #666;
}

/* Filter */
.slos-filter {
	padding: 6px 12px;
	border-radius: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Grade filter
	$('#slos-filter-grade').on('change', function() {
		const selectedGrade = $(this).val();
		
		$('.slos-sites-table tbody tr').each(function() {
			const rowGrade = $(this).data('grade');
			
			if (!selectedGrade || rowGrade === selectedGrade) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});
});
</script>
