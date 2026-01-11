<?php
/**
 * Support Page Template
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap shahi-legalflowsuite-admin shahi-support-page">
	
	<!-- Page Header -->
	<div class="shahi-page-header">
		<div class="shahi-header-content">
			<h1 class="shahi-page-title">
				<span class="dashicons dashicons-sos"></span>
				<?php echo esc_html__( 'Support & Documentation', 'shahi-legalflowsuite' ); ?>
			</h1>
			<p class="shahi-page-description">
				<?php echo esc_html__( 'Get help, browse documentation, and access support resources', 'shahi-legalflowsuite' ); ?>
			</p>
		</div>
	</div>

	<!-- Support Resources Grid -->
	<div class="shahi-support-grid">
		<?php foreach ( $resources as $resource ) : ?>
			<a href="<?php echo esc_url( $resource['url'] ); ?>" 
				class="shahi-support-card shahi-card-<?php echo esc_attr( $resource['type'] ); ?>" 
				target="_blank">
				<div class="shahi-support-icon">
					<span class="dashicons <?php echo esc_attr( $resource['icon'] ); ?>"></span>
				</div>
				<h3 class="shahi-support-title"><?php echo esc_html( $resource['title'] ); ?></h3>
				<p class="shahi-support-description"><?php echo esc_html( $resource['description'] ); ?></p>
				<span class="shahi-support-arrow">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>

	<!-- FAQs Section -->
	<div class="shahi-section">
		<div class="shahi-card">
			<div class="shahi-card-header">
				<h2 class="shahi-card-title">
					<span class="dashicons dashicons-editor-help"></span>
					<?php echo esc_html__( 'Frequently Asked Questions', 'shahi-legalflowsuite' ); ?>
				</h2>
			</div>
			<div class="shahi-card-body">
				<div class="shahi-faqs">
					<?php foreach ( $faqs as $index => $faq ) : ?>
						<div class="shahi-faq-item">
							<div class="shahi-faq-question" data-faq-toggle="<?php echo esc_attr( $index ); ?>">
								<h4><?php echo esc_html( $faq['question'] ); ?></h4>
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							</div>
							<div class="shahi-faq-answer" data-faq-content="<?php echo esc_attr( $index ); ?>">
								<p><?php echo esc_html( $faq['answer'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- System Information -->
	<div class="shahi-section">
		<div class="shahi-card">
			<div class="shahi-card-header">
				<h2 class="shahi-card-title">
					<span class="dashicons dashicons-info"></span>
					<?php echo esc_html__( 'System Information', 'shahi-legalflowsuite' ); ?>
				</h2>
				<button type="button" class="shahi-btn shahi-btn-sm shahi-btn-secondary" 
						onclick="navigator.clipboard.writeText(document.getElementById('system-info-text').textContent); ShahiLegalFlowSuite.showNotice('System info copied to clipboard', 'success', 2000);">
					<span class="dashicons dashicons-clipboard"></span>
					<?php echo esc_html__( 'Copy Info', 'shahi-legalflowsuite' ); ?>
				</button>
			</div>
			<div class="shahi-card-body">
				<div class="shahi-system-info" id="system-info-text">
					<table class="shahi-table">
						<tbody>
							<?php foreach ( $system_info as $info ) : ?>
								<tr>
									<td class="shahi-info-label"><strong><?php echo esc_html( $info['label'] ); ?></strong></td>
									<td class="shahi-info-value"><?php echo esc_html( $info['value'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Changelog -->
	<div class="shahi-section" id="changelog">
		<div class="shahi-card">
			<div class="shahi-card-header">
				<h2 class="shahi-card-title">
					<span class="dashicons dashicons-list-view"></span>
					<?php echo esc_html__( 'Changelog', 'shahi-legalflowsuite' ); ?>
				</h2>
			</div>
			<div class="shahi-card-body">
				<div class="shahi-changelog">
					<?php foreach ( $changelog as $version_info ) : ?>
						<div class="shahi-changelog-version">
							<div class="shahi-version-header">
								<h3 class="shahi-version-number">
									<?php echo esc_html__( 'Version', 'shahi-legalflowsuite' ) . ' ' . esc_html( $version_info['version'] ); ?>
								</h3>
								<span class="shahi-version-date"><?php echo esc_html( $version_info['date'] ); ?></span>
							</div>
							
							<?php if ( ! empty( $version_info['changes']['added'] ) ) : ?>
								<div class="shahi-changelog-section">
									<h4 class="shahi-changelog-heading shahi-added">
										<span class="dashicons dashicons-plus-alt"></span>
										<?php echo esc_html__( 'Added', 'shahi-legalflowsuite' ); ?>
									</h4>
									<ul class="shahi-changelog-list">
										<?php foreach ( $version_info['changes']['added'] as $change ) : ?>
											<li><?php echo esc_html( $change ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							
							<?php if ( ! empty( $version_info['changes']['changed'] ) ) : ?>
								<div class="shahi-changelog-section">
									<h4 class="shahi-changelog-heading shahi-changed">
										<span class="dashicons dashicons-update"></span>
										<?php echo esc_html__( 'Changed', 'shahi-legalflowsuite' ); ?>
									</h4>
									<ul class="shahi-changelog-list">
										<?php foreach ( $version_info['changes']['changed'] as $change ) : ?>
											<li><?php echo esc_html( $change ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							
							<?php if ( ! empty( $version_info['changes']['fixed'] ) ) : ?>
								<div class="shahi-changelog-section">
									<h4 class="shahi-changelog-heading shahi-fixed">
										<span class="dashicons dashicons-yes-alt"></span>
										<?php echo esc_html__( 'Fixed', 'shahi-legalflowsuite' ); ?>
									</h4>
									<ul class="shahi-changelog-list">
										<?php foreach ( $version_info['changes']['fixed'] as $change ) : ?>
											<li><?php echo esc_html( $change ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							
							<?php if ( ! empty( $version_info['changes']['removed'] ) ) : ?>
								<div class="shahi-changelog-section">
									<h4 class="shahi-changelog-heading shahi-removed">
										<span class="dashicons dashicons-dismiss"></span>
										<?php echo esc_html__( 'Removed', 'shahi-legalflowsuite' ); ?>
									</h4>
									<ul class="shahi-changelog-list">
										<?php foreach ( $version_info['changes']['removed'] as $change ) : ?>
											<li><?php echo esc_html( $change ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

</div>

<script>
// FAQ Toggle functionality
document.addEventListener('DOMContentLoaded', function() {
	const faqItems = document.querySelectorAll('.shahi-faq-question');
	
	faqItems.forEach(function(item) {
		item.addEventListener('click', function() {
			const index = this.getAttribute('data-faq-toggle');
			const answer = document.querySelector('[data-faq-content="' + index + '"]');
			const arrow = this.querySelector('.dashicons');
			
			// Toggle active class
			this.parentElement.classList.toggle('active');
			
			// Toggle answer visibility
			if (answer.style.display === 'block') {
				answer.style.display = 'none';
				arrow.classList.remove('dashicons-arrow-up-alt2');
				arrow.classList.add('dashicons-arrow-down-alt2');
			} else {
				answer.style.display = 'block';
				arrow.classList.remove('dashicons-arrow-down-alt2');
				arrow.classList.add('dashicons-arrow-up-alt2');
			}
		});
	});
});
</script>

