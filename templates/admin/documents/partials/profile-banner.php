<?php
/**
 * Profile Completion Banner Partial
 *
 * Displays the company profile completion status banner.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Templates/Admin/Documents/Partials
 * @since       4.2.0
 *
 * @var array $profile Profile summary data from parent template.
 * @var array $data    Full template data from parent template.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$completeness = $profile['completeness'] ?? 0;
$company_name = $profile['name'] ?? '';
$updated_at   = $profile['updated_at'] ?? '';
$profile_url  = $data['profile_url'] ?? admin_url( 'admin.php?page=slos-company-profile' );

// Determine banner state.
$is_complete   = $completeness >= 100;
$is_sufficient = $completeness >= 70; // Enough for generation.
$banner_class  = $is_complete ? 'slos-hub-banner--success' : ( $is_sufficient ? 'slos-hub-banner--info' : 'slos-hub-banner--warning' );
?>

<p class="slos-widget-description" style="margin-bottom: 16px;">
	<?php esc_html_e( 'Company Profile completion determines document generation quality. 100% recommended but 70% sufficient for basic generation. Profile changes trigger "Outdated" warnings on existing documents.', 'shahi-legalflowsuite' ); ?>
</p>

<div class="slos-hub-banner <?php echo esc_attr( $banner_class ); ?>">
	<div class="slos-hub-banner__progress">
		<div class="slos-hub-banner__progress-ring">
			<svg viewBox="0 0 36 36" class="slos-hub-banner__progress-svg">
				<path class="slos-hub-banner__progress-bg"
					d="M18 2.0845
						a 15.9155 15.9155 0 0 1 0 31.831
						a 15.9155 15.9155 0 0 1 0 -31.831"
					fill="none"
					stroke-width="3"
				/>
				<path class="slos-hub-banner__progress-fill"
					d="M18 2.0845
						a 15.9155 15.9155 0 0 1 0 31.831
						a 15.9155 15.9155 0 0 1 0 -31.831"
					fill="none"
					stroke-width="3"
					stroke-dasharray="<?php echo esc_attr( $completeness ); ?>, 100"
				/>
			</svg>
			<span class="slos-hub-banner__progress-text"><?php echo esc_html( $completeness ); ?>%</span>
		</div>
	</div>

	<div class="slos-hub-banner__content">
		<?php if ( $is_complete ) : ?>
			<h3 class="slos-hub-banner__title">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'Profile Complete!', 'shahi-legalflowsuite' ); ?>
			</h3>
			<p class="slos-hub-banner__text">
				<?php
				if ( $company_name ) {
					printf(
						/* translators: %s: company name */
						esc_html__( 'Shahi LegalFlowSuite is ready to generate Legal Documents for %s.', 'shahi-legalflowsuite' ),
						'<strong>' . esc_html( $company_name ) . '</strong>'
					);
				} else {
					esc_html_e( 'Your company profile is complete. You can now generate legal documents.', 'shahi-legalflowsuite' );
				}
				?>
			</p>
		<?php elseif ( $is_sufficient ) : ?>
			<h3 class="slos-hub-banner__title">
				<span class="dashicons dashicons-info-outline"></span>
				<?php esc_html_e( 'Profile Almost Complete', 'shahi-legalflowsuite' ); ?>
			</h3>
			<p class="slos-hub-banner__text">
				<?php
				printf(
					/* translators: %d: completion percentage */
					esc_html__( 'Your profile is %d%% complete. You can generate documents, but completing all fields is recommended for best results.', 'shahi-legalflowsuite' ),
					absint( $completeness )
				);
				?>
			</p>
		<?php else : ?>
			<h3 class="slos-hub-banner__title">
				<span class="dashicons dashicons-warning"></span>
				<?php esc_html_e( 'Complete Your Profile', 'shahi-legalflowsuite' ); ?>
			</h3>
			<p class="slos-hub-banner__text">
				<?php
				printf(
					/* translators: %d: completion percentage */
					esc_html__( 'Your profile is only %d%% complete. Complete the required fields to enable document generation.', 'shahi-legalflowsuite' ),
					absint( $completeness )
				);
				?>
			</p>
		<?php endif; ?>

		<?php if ( $updated_at ) : ?>
			<p class="slos-hub-banner__meta">
				<span class="dashicons dashicons-clock"></span>
				<?php
				printf(
					/* translators: %s: date */
					esc_html__( 'Last updated: %s', 'shahi-legalflowsuite' ),
					esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $updated_at ) ) )
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<div class="slos-hub-banner__actions">
		<?php if ( ! $is_complete ) : ?>
			<a href="<?php echo esc_url( $profile_url ); ?>" class="slos-btn slos-btn--primary">
				<span class="dashicons dashicons-edit"></span>
				<?php esc_html_e( 'Continue Setup', 'shahi-legalflowsuite' ); ?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( $profile_url ); ?>" class="slos-btn slos-btn--secondary">
				<span class="dashicons dashicons-admin-generic"></span>
				<?php esc_html_e( 'Edit Profile', 'shahi-legalflowsuite' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
