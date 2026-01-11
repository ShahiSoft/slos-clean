<?php
/**
 * Accessibility Settings Template
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap shahi-module-dashboard shahi-legalflowsuite-admin">
	
	<!-- Header -->
	<div class="shahi-dashboard-header">
		<div class="shahi-header-content">
			<div class="shahi-header-text">
				<h1 class="shahi-page-title">
					<span class="shahi-icon-badge">
						<span class="dashicons dashicons-admin-settings"></span>
					</span>
					<?php echo esc_html__( 'Accessibility Settings', 'shahi-legalflowsuite' ); ?>
				</h1>
				<p class="shahi-page-subtitle">
					<?php echo esc_html__( 'Configure automated checks and fixes for your website.', 'shahi-legalflowsuite' ); ?>
				</p>
			</div>
			<div class="shahi-header-actions">
				<a href="<?php echo admin_url( 'admin.php?page=slos-accessibility-dashboard' ); ?>" class="shahi-btn shahi-btn-outline">
					<span class="dashicons dashicons-arrow-left-alt"></span>
					<?php echo esc_html__( 'Back to Dashboard', 'shahi-legalflowsuite' ); ?>
				</a>
			</div>
		</div>
	</div>

	<form method="post" action="options.php">
		<?php settings_fields( 'slos_accessibility_settings' ); ?>
		
		<div class="shahi-dashboard-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
			
			<!-- Automated Checks -->
			<div class="shahi-card">
				<div class="shahi-card-header">
					<div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
						<div>
							<h3><?php echo esc_html__( 'Automated Checks', 'shahi-legalflowsuite' ); ?></h3>
							<p class="description"><?php echo esc_html__( 'Select which accessibility issues to scan for.', 'shahi-legalflowsuite' ); ?></p>
						</div>
						<div class="shahi-toggle-actions">
							<button type="button" class="shahi-btn shahi-btn-sm shahi-btn-outline slos-select-all" data-target="slos_active_checkers">
								<?php echo esc_html__( 'Select All', 'shahi-legalflowsuite' ); ?>
							</button>
							<button type="button" class="shahi-btn shahi-btn-sm shahi-btn-outline slos-deselect-all" data-target="slos_active_checkers">
								<?php echo esc_html__( 'Deselect All', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="shahi-card-body">
					<div class="shahi-checkbox-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
						<?php foreach ( $checkers as $key => $label ) : ?>
							<label class="shahi-checkbox-label" style="display: flex; align-items: center; gap: 10px;">
								<input type="checkbox" name="slos_active_checkers[]" value="<?php echo esc_attr( $key ); ?>" 
									<?php checked( in_array( $key, $active_checkers ) ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Automated Fixes -->
			<div class="shahi-card">
				<div class="shahi-card-header">
					<div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
						<div>
							<h3><?php echo esc_html__( 'Automated Fixes', 'shahi-legalflowsuite' ); ?></h3>
							<p class="description"><?php echo esc_html__( 'Select which issues to automatically attempt to fix.', 'shahi-legalflowsuite' ); ?></p>
						</div>
						<div class="shahi-toggle-actions">
							<button type="button" class="shahi-btn shahi-btn-sm shahi-btn-outline slos-select-all" data-target="slos_active_fixes">
								<?php echo esc_html__( 'Select All', 'shahi-legalflowsuite' ); ?>
							</button>
							<button type="button" class="shahi-btn shahi-btn-sm shahi-btn-outline slos-deselect-all" data-target="slos_active_fixes">
								<?php echo esc_html__( 'Deselect All', 'shahi-legalflowsuite' ); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="shahi-card-body">
					<div class="shahi-checkbox-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
						<?php foreach ( $fixes as $key => $label ) : ?>
							<label class="shahi-checkbox-label" style="display: flex; align-items: center; gap: 10px;">
								<input type="checkbox" name="slos_active_fixes[]" value="<?php echo esc_attr( $key ); ?>" 
									<?php checked( in_array( $key, $active_fixes ) ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>

		<div class="shahi-form-actions" style="margin-top: 30px; text-align: right;">
			<button type="submit" class="shahi-btn shahi-btn-primary shahi-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php echo esc_html__( 'Save Settings', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
	</form>

</div>

<script>
jQuery(document).ready(function($) {
	$('.slos-select-all').on('click', function() {
		var target = $(this).data('target');
		$('input[name="' + target + '[]"]').prop('checked', true);
	});
	
	$('.slos-deselect-all').on('click', function() {
		var target = $(this).data('target');
		$('input[name="' + target + '[]"]').prop('checked', false);
	});
});
</script>

