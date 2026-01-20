<?php
/**
 * Company Profile Wizard Template
 *
 * Main wizard template with step navigation, progress bar, and form rendering.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Templates/Admin
 * @version     4.1.0
 * @since       4.1.0
 *
 * @var array $steps       Step definitions from Company_Profile_Service.
 * @var array $profile     Current profile data.
 * @var array $completion  Completion data with percentage and missing fields.
 * @var int   $current_step Current step number.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap slos-profile-wizard-wrap">
	<div class="slos-wizard-container">
		
		<!-- Header -->
		<header class="slos-wizard-header">
			<div class="slos-wizard-header-content">
				<h1 class="slos-wizard-title">
					<span class="dashicons dashicons-building"></span>
					<?php esc_html_e( 'Company Profile Setup', 'shahi-legalflowsuite' ); ?>
				</h1>
				<p class="slos-wizard-subtitle">
					<?php esc_html_e( 'Complete your company profile to generate professional legal documents.', 'shahi-legalflowsuite' ); ?>
				</p>
			</div>
			<div class="slos-wizard-header-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-documents' ) ); ?>" class="slos-btn slos-btn-secondary">
					<span class="dashicons dashicons-arrow-left-alt"></span>
					<?php esc_html_e( 'Back to Document Hub', 'shahi-legalflowsuite' ); ?>
				</a>
			</div>
		</header>

		<!-- Progress Bar -->
		<div class="slos-wizard-progress">
			<div class="slos-progress-bar">
				<div class="slos-progress-fill" style="width: <?php echo esc_attr( $completion['percentage'] ); ?>%;"></div>
			</div>
			<div class="slos-progress-text">
				<span class="slos-progress-percentage"><?php echo esc_html( $completion['percentage'] ); ?>%</span>
				<span class="slos-progress-label"><?php esc_html_e( 'Complete', 'shahi-legalflowsuite' ); ?></span>
			</div>
		</div>

		<!-- Main Content -->
		<div class="slos-wizard-main">
			
			<!-- Step Navigation (Sidebar) -->
			<nav class="slos-wizard-nav">
				<ul class="slos-step-list">
					<?php
					foreach ( $steps as $step_num => $step ) :
						$step_validation = isset( $completion['steps'][ $step_num ] ) ? $completion['steps'][ $step_num ] : array();
						$is_current      = ( $step_num === $current_step );
						$is_complete     = ! empty( $step_validation['is_valid'] );
						$step_class      = 'slos-step-item';
						if ( $is_current ) {
							$step_class .= ' slos-step-active';
						}
						if ( $is_complete ) {
							$step_class .= ' slos-step-complete';
						}
						?>
					<li class="<?php echo esc_attr( $step_class ); ?>" data-step="<?php echo esc_attr( $step_num ); ?>">
						<button type="button" class="slos-step-button">
							<span class="slos-step-number">
								<?php if ( $is_complete ) : ?>
									<span class="dashicons dashicons-yes"></span>
								<?php else : ?>
									<?php echo esc_html( $step_num ); ?>
								<?php endif; ?>
							</span>
							<span class="slos-step-info">
								<span class="slos-step-title"><?php echo esc_html( $step['title'] ); ?></span>
								<span class="slos-step-status">
									<?php if ( $is_complete ) : ?>
										<?php esc_html_e( 'Complete', 'shahi-legalflowsuite' ); ?>
									<?php elseif ( $is_current ) : ?>
										<?php esc_html_e( 'In Progress', 'shahi-legalflowsuite' ); ?>
									<?php else : ?>
										<?php esc_html_e( 'Pending', 'shahi-legalflowsuite' ); ?>
									<?php endif; ?>
								</span>
							</span>
						</button>
					</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<!-- Step Content -->
			<div class="slos-wizard-content">
				<form id="slos-wizard-form" class="slos-wizard-form" novalidate>
					<?php wp_nonce_field( 'slos_profile_wizard', 'slos_wizard_nonce' ); ?>
					<input type="hidden" name="current_step" id="slos-current-step" value="<?php echo esc_attr( $current_step ); ?>">

					<!-- Step Panels -->
					<?php foreach ( $steps as $step_num => $step ) : ?>
					<div class="slos-step-panel <?php echo $step_num === $current_step ? 'slos-step-active' : ''; ?>" 
						data-step="<?php echo esc_attr( $step_num ); ?>" 
						id="slos-step-<?php echo esc_attr( $step_num ); ?>">
						
						<div class="slos-step-header">
							<div class="slos-step-icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $step['icon'] ); ?>"></span>
							</div>
							<div class="slos-step-meta">
								<span class="slos-step-badge">
									<?php
									/* translators: 1: current step number, 2: total number of steps */
									printf( esc_html__( 'Step %1$d of %2$d', 'shahi-legalflowsuite' ), intval( $step_num ), count( $steps ) );
									?>
								</span>
								<h2 class="slos-step-heading"><?php echo esc_html( $step['title'] ); ?></h2>
								<p class="slos-step-description"><?php echo esc_html( $step['description'] ); ?></p>
							</div>
						</div>

						<div class="slos-step-fields">
							<?php $this->render_step_fields( $step, $profile ); ?>
						</div>
					</div>
					<?php endforeach; ?>

					<!-- Navigation Buttons -->
					<div class="slos-wizard-actions">
						<button type="button" class="slos-btn slos-btn-secondary slos-btn-prev" id="slos-prev-btn" <?php echo $current_step <= 1 ? 'disabled' : ''; ?>>
							<span class="dashicons dashicons-arrow-left-alt"></span>
							<?php esc_html_e( 'Previous', 'shahi-legalflowsuite' ); ?>
						</button>
						
						<div class="slos-wizard-actions-right">
							<span class="slos-save-status" id="slos-save-status"></span>
							
							<button type="button" class="slos-btn slos-btn-secondary" id="slos-save-btn">
								<span class="dashicons dashicons-saved"></span>
								<?php esc_html_e( 'Save Progress', 'shahi-legalflowsuite' ); ?>
							</button>

							<?php if ( $current_step < count( $steps ) ) : ?>
							<button type="button" class="slos-btn slos-btn-primary slos-btn-next" id="slos-next-btn">
								<?php esc_html_e( 'Continue', 'shahi-legalflowsuite' ); ?>
								<span class="dashicons dashicons-arrow-right-alt"></span>
							</button>
							<?php else : ?>
							<button type="button" class="slos-btn slos-btn-success" id="slos-finish-btn">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Finish Wizard', 'shahi-legalflowsuite' ); ?>
							</button>
							<?php endif; ?>
						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Completion Modal -->
		<div id="slos-completion-modal" class="slos-modal">
			<div class="slos-modal-content">
				<div class="slos-modal-header">
					<span class="slos-modal-icon slos-modal-icon-success">
						<span class="dashicons dashicons-yes-alt"></span>
					</span>
					<h3><?php esc_html_e( 'Profile Setup Complete!', 'shahi-legalflowsuite' ); ?></h3>
				</div>
				<div class="slos-modal-body">
					<p><?php esc_html_e( 'Your company profile is now ready. You can generate professional legal documents based on your profile data.', 'shahi-legalflowsuite' ); ?></p>
				</div>
				<div class="slos-modal-footer">
					<button type="button" class="slos-btn slos-btn-secondary slos-modal-close">
						<?php esc_html_e( 'Stay Here', 'shahi-legalflowsuite' ); ?>
					</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-documents' ) ); ?>" class="slos-btn slos-btn-primary">
						<span class="dashicons dashicons-media-document"></span>
						<?php esc_html_e( 'Generate Documents', 'shahi-legalflowsuite' ); ?>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>
