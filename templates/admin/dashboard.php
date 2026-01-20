<?php
/**
 * Dashboard V3 Template - Ultra Modern Design
 *
 * Features: Card-based layout, visual metrics, mini charts, modern gradients,
 * advanced animations, and responsive grid system.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      3.0.1
 * @version    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Calculate metrics.
$completed_steps = 0;
foreach ( $getting_started as $item ) {
	if ( $item['completed'] ) {
		++$completed_steps;
	}
}
$setup_progress = count( $getting_started ) > 0 ? round( ( $completed_steps / count( $getting_started ) ) * 100 ) : 0;

// Count active modules.
$active_modules = 0;
$total_modules  = count( $modules_status );
foreach ( $modules_status as $module ) {
	if ( $module['enabled'] ) {
		++$active_modules;
	}
}
?>

<div class="wrap shahi-legalflowsuite-admin shahi-dashboard-page">
	
	<!-- ═══════════════════════════════════════════════════════════════════════
		TOP BAR - Compact Header with Actions
		═══════════════════════════════════════════════════════════════════════ -->
	<div class="shahi-topbar">
		<div class="shahi-topbar-left">
			<div class="shahi-brand">
				<span class="shahi-brand-icon">⚖️</span>
				<div class="shahi-brand-text">
					<span class="shahi-brand-name">Shahi LegalFlowSuite</span>
					<span class="shahi-brand-tag">v<?php echo esc_html( $plugin_info['version'] ); ?></span>
				</div>
			</div>
			<div class="shahi-breadcrumb">
				<span class="dashicons dashicons-admin-home"></span>
				<span class="shahi-breadcrumb-text"><?php echo esc_html__( 'Dashboard', 'shahi-legalflowsuite' ); ?></span>
			</div>
		</div>
		<div class="shahi-topbar-right">
			<button type="button" class="shahi-btn-icon shahi-trigger-onboarding" title="<?php echo esc_attr__( 'Quick Start', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-welcome-learn-more"></span>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalflowsuite-settings' ) ); ?>" class="shahi-btn-icon" title="<?php echo esc_attr__( 'Settings', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-admin-settings"></span>
			</a>
			<button type="button" class="shahi-btn-icon" data-action="refresh" title="<?php echo esc_attr__( 'Refresh', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-update"></span>
			</button>
		</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════════════════════
		HERO BANNER - Compact Welcome Section
		═══════════════════════════════════════════════════════════════════════ -->
	<div class="shahi-hero">
		<div class="shahi-hero-bg"></div>
		<div class="shahi-hero-content">
			<div class="shahi-hero-main">
				<h1 class="shahi-hero-title">
					<?php echo esc_html__( 'Welcome back!', 'shahi-legalflowsuite' ); ?>
					<span aria-hidden="true">❤️</span>
				</h1>
				<p class="shahi-hero-subtitle">
					<?php echo esc_html__( 'Here\'s what\'s happening with your legal compliance system today.', 'shahi-legalflowsuite' ); ?>
				</p>
			</div>
			<div class="shahi-hero-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalflowsuite-modules' ) ); ?>" class="shahi-btn shahi-btn-primary">
					<span class="dashicons dashicons-admin-plugins"></span>
					<?php echo esc_html__( 'Manage Modules', 'shahi-legalflowsuite' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════════════════════
		STATS GRID - 4 Metric Cards
		═══════════════════════════════════════════════════════════════════════ -->
	<div class="shahi-section-intro">
		<p class="shahi-section-desc">
			<?php echo esc_html__( 'Monitor your legal compliance system at a glance. These metrics help you track active modules, system events, performance health, and recent activity across your WordPress site.', 'shahi-legalflowsuite' ); ?>
		</p>
	</div>
	<div class="shahi-stats-row">
		<?php foreach ( $stats as $index => $stat ) : ?>
			<div class="shahi-stat-card shahi-stat-<?php echo esc_attr( $stat['color'] ); ?>">
				<div class="shahi-stat-icon-wrap">
					<div class="shahi-stat-icon">
						<span class="dashicons <?php echo esc_attr( $stat['icon'] ); ?>"></span>
					</div>
					<?php if ( isset( $stat['trend'] ) && $stat['trend'] ) : ?>
						<div class="shahi-stat-badge shahi-trend-up">
							<span class="dashicons dashicons-arrow-up-alt"></span>
							<?php echo esc_html( $stat['trend'] ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="shahi-stat-content">
					<h3 class="shahi-stat-label"><?php echo esc_html( $stat['title'] ); ?></h3>
					<div class="shahi-stat-value">
						<?php if ( isset( $stat['is_time'] ) && $stat['is_time'] ) : ?>
							<?php echo esc_html( $stat['value'] ); ?>
						<?php else : ?>
							<span class="shahi-stat-number"><?php echo esc_html( $stat['value'] ); ?></span>
							<?php if ( isset( $stat['suffix'] ) ) : ?>
								<span class="shahi-stat-suffix"><?php echo esc_html( $stat['suffix'] ); ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<?php if ( isset( $stat['description'] ) && $stat['description'] ) : ?>
						<p class="shahi-stat-desc"><?php echo esc_html( $stat['description'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- ═══════════════════════════════════════════════════════════════════════
		MAIN GRID - 2 Column Layout
		═══════════════════════════════════════════════════════════════════════ -->
	<div class="shahi-main-grid">
		
		<!-- LEFT COLUMN -->
		<div class="shahi-col-left">
			
			<!-- Modules Overview -->
			<div class="shahi-card shahi-modules-card">
				<div class="shahi-card-header">
					<div class="shahi-card-header-left">
						<span class="shahi-card-icon">
							<span class="dashicons dashicons-screenoptions"></span>
						</span>
						<h2 class="shahi-card-title"><?php echo esc_html__( 'Modules', 'shahi-legalflowsuite' ); ?></h2>
						<span class="shahi-badge"><?php echo esc_html( $active_modules ); ?>/<?php echo esc_html( $total_modules ); ?> <?php echo esc_html__( 'Active', 'shahi-legalflowsuite' ); ?></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalflowsuite-modules' ) ); ?>" class="shahi-card-link">
						<?php echo esc_html__( 'View All', 'shahi-legalflowsuite' ); ?>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</a>
				</div>
				<div class="shahi-card-body">
					<p class="shahi-card-intro">
						<?php echo esc_html__( 'Enable the modules you need for GDPR, CCPA, and accessibility compliance. Each module handles a specific aspect of legal requirements for your website.', 'shahi-legalflowsuite' ); ?>
					</p>
					<div class="shahi-modules-grid">
						<?php foreach ( $modules_status as $module ) : ?>
							<div class="shahi-module-item <?php echo esc_attr( $module['enabled'] ? 'active' : 'inactive' ); ?>">
								<div class="shahi-module-icon">
									<span class="shahi-module-emoji"><?php echo esc_html( $module['icon'] ); ?></span>
								</div>
								<div class="shahi-module-info">
									<h4 class="shahi-module-name"><?php echo esc_html( $module['name'] ); ?></h4>
									<p class="shahi-module-desc"><?php echo esc_html( $module['description'] ); ?></p>
								</div>
								<div class="shahi-module-status">
									<?php if ( $module['enabled'] ) : ?>
										<span class="shahi-status-dot active"></span>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $module['page'] ) ); ?>" class="shahi-btn-sm shahi-btn-ghost">
											<?php echo esc_html__( 'Open', 'shahi-legalflowsuite' ); ?>
										</a>
									<?php else : ?>
										<span class="shahi-status-dot inactive"></span>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalflowsuite-modules' ) ); ?>" class="shahi-btn-sm shahi-btn-outline">
											<?php echo esc_html__( 'Enable', 'shahi-legalflowsuite' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Quick Actions Grid -->
			<div class="shahi-card shahi-actions-card">
				<div class="shahi-card-header">
					<div class="shahi-card-header-left">
						<span class="shahi-card-icon">
							<span class="dashicons dashicons-superhero-alt"></span>
						</span>
						<h2 class="shahi-card-title"><?php echo esc_html__( 'Quick Actions', 'shahi-legalflowsuite' ); ?></h2>
					</div>
				</div>
				<div class="shahi-card-body">
					<p class="shahi-card-intro">
						<?php echo esc_html__( 'Jump directly to common tasks. Manage data subject requests, configure consent banners, generate legal documents, or adjust plugin settings with one click.', 'shahi-legalflowsuite' ); ?>
					</p>
					<div class="shahi-actions-grid">
						<?php foreach ( $quick_actions as $quick_action ) : ?>
							<a href="<?php echo esc_url( $quick_action['url'] ); ?>" class="shahi-action-tile">
								<div class="shahi-action-icon-wrap">
									<span class="dashicons <?php echo esc_attr( $quick_action['icon'] ); ?>"></span>
								</div>
								<span class="shahi-action-title"><?php echo esc_html( $quick_action['title'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>

		<!-- RIGHT COLUMN -->
		<div class="shahi-col-right">
			
			<!-- Setup Progress -->
			<div class="shahi-card shahi-setup-card">
				<div class="shahi-card-header">
					<div class="shahi-card-header-left">
						<span class="shahi-card-icon">
							<span class="dashicons dashicons-flag"></span>
						</span>
						<h2 class="shahi-card-title"><?php echo esc_html__( 'Setup Progress', 'shahi-legalflowsuite' ); ?></h2>
						<span class="shahi-badge shahi-badge-success"><?php echo esc_html( $setup_progress ); ?>%</span>
					</div>
				</div>
				<div class="shahi-card-body">
					<p class="shahi-card-intro">
						<?php echo esc_html__( 'Complete these steps to fully configure your legal compliance system. Each step ensures your website meets privacy regulations and accessibility standards.', 'shahi-legalflowsuite' ); ?>
					</p>
					<!-- Circular Progress -->
					<div class="shahi-progress-circle-wrap">
						<svg class="shahi-progress-circle" viewBox="0 0 120 120">
							<circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="8"/>
							<circle cx="60" cy="60" r="54" fill="none" stroke="url(#gradient)" stroke-width="8" 
									stroke-dasharray="339.292" 
									stroke-dashoffset="<?php echo esc_attr( 339.292 - ( 339.292 * $setup_progress / 100 ) ); ?>"
									stroke-linecap="round"
									transform="rotate(-90 60 60)"/>
							<defs>
								<linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
									<stop offset="0%" style="stop-color:#6b8e4e;stop-opacity:1" />
									<stop offset="100%" style="stop-color:#8ba972;stop-opacity:1" />
								</linearGradient>
							</defs>
						</svg>
						<div class="shahi-progress-circle-text">
							<span class="shahi-progress-percent"><?php echo esc_html( $setup_progress ); ?>%</span>
							<span class="shahi-progress-label"><?php echo esc_html__( 'Complete', 'shahi-legalflowsuite' ); ?></span>
						</div>
					</div>
					<!-- Checklist -->
					<div class="shahi-checklist">
						<?php foreach ( $getting_started as $item ) : ?>
							<div class="shahi-checklist-item <?php echo esc_attr( $item['completed'] ? 'completed' : '' ); ?>">
								<div class="shahi-check-icon">
									<?php if ( $item['completed'] ) : ?>
										<span class="dashicons dashicons-yes-alt"></span>
									<?php else : ?>
										<span class="dashicons dashicons-marker"></span>
									<?php endif; ?>
								</div>
								<span class="shahi-check-text"><?php echo esc_html( $item['title'] ); ?></span>
								<a href="<?php echo esc_url( $item['action_url'] ); ?>" class="shahi-check-action <?php echo ! empty( $item['action_class'] ) ? esc_attr( $item['action_class'] ) : ''; ?>">
									<span class="dashicons dashicons-arrow-right-alt2"></span>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<?php
			/*
			Recent Activity section - DORMANT (temporarily hidden)
			<div class="shahi-card shahi-activity-card">
				<div class="shahi-card-header">
					<div class="shahi-card-header-left">
						<span class="shahi-card-icon">
							<span class="dashicons dashicons-clock"></span>
						</span>
						<h2 class="shahi-card-title"><?php echo esc_html__('Recent Activity', 'shahi-legalflowsuite'); ?></h2>
					</div>
				</div>
				<div class="shahi-card-body">
					<p class="shahi-card-intro">
						<?php echo esc_html__('Track compliance events including consent records, DSR submissions, document generations, and configuration changes for audit purposes.', 'shahi-legalflowsuite'); ?>
					</p>
					<?php if (!empty($recent_activity)): ?>
						<div class="shahi-activity-list">
							<?php foreach (array_slice($recent_activity, 0, 6) as $activity): ?>
								<div class="shahi-activity-item">
									<div class="shahi-activity-icon">
										<span class="dashicons <?php echo esc_attr($activity['icon']); ?>"></span>
									</div>
									<div class="shahi-activity-content">
										<h4 class="shahi-activity-title"><?php echo esc_html($activity['title']); ?></h4>
										<p class="shahi-activity-desc"><?php echo esc_html($activity['description']); ?></p>
									</div>
									<span class="shahi-activity-time"><?php echo esc_html($activity['time']); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="shahi-empty-state">
							<span class="dashicons dashicons-calendar-alt"></span>
							<p><?php echo esc_html__('No recent activity', 'shahi-legalflowsuite'); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
			*/
			?>

			<!-- Support Resources -->
			<div class="shahi-card shahi-support-card">
				<div class="shahi-card-header">
					<div class="shahi-card-header-left">
						<span class="shahi-card-icon">
							<span class="dashicons dashicons-sos"></span>
						</span>
						<h2 class="shahi-card-title"><?php echo esc_html__( 'Support', 'shahi-legalflowsuite' ); ?></h2>
					</div>
				</div>
				<div class="shahi-card-body">
					<p class="shahi-card-intro">
						<?php echo esc_html__( 'Get help with setup, learn best practices for compliance, or contact our support team for assistance with complex configurations.', 'shahi-legalflowsuite' ); ?>
					</p>
					<div class="shahi-support-list">
						<?php foreach ( array_slice( $support_links, 0, 4 ) as $support_link ) : ?>
							<a href="<?php echo esc_url( $support_link['url'] ); ?>" 
								class="shahi-support-item"
								<?php echo $support_link['external'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
								<span class="dashicons <?php echo esc_attr( $support_link['icon'] ); ?>"></span>
								<span class="shahi-support-text"><?php echo esc_html( $support_link['title'] ); ?></span>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>

	</div>

	<!-- Footer -->
	<div class="shahi-footer">
		<div class="shahi-footer-content">
			<span class="shahi-footer-text">
				<?php echo esc_html( $plugin_info['name'] ); ?> v<?php echo esc_html( $plugin_info['version'] ); ?> • 
				<?php echo esc_html__( 'Made with', 'shahi-legalflowsuite' ); ?> ❤️ <?php echo esc_html__( 'by', 'shahi-legalflowsuite' ); ?> <?php echo esc_html( $plugin_info['author'] ); ?>
			</span>
		</div>
	</div>

</div>
