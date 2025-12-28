<?php
/**
 * Document Card Partial
 *
 * Displays a single document card with status, actions, and metadata.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Templates/Admin/Documents/Partials
 * @since       4.2.0
 *
 * @var array $card Card data passed from parent template.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine status class and badge.
$status       = $card['status'] ?? 'not_generated';
$is_outdated  = $card['is_outdated'] ?? false;
$doc_id       = $card['doc_id'] ?? 0;
$doc_type     = $card['id'] ?? '';
$version      = $card['version'] ?? '';
$updated_at   = $card['updated_at'] ?? '';
$category     = $card['category'] ?? 'legal';

// Status display configuration.
$status_config = array(
	'not_generated' => array(
		'class'   => 'slos-card--not-generated',
		'badge'   => 'slos-badge--gray',
		'label'   => __( 'Not Generated', 'shahi-legalflowsuite' ),
		'icon'    => 'dashicons-media-document',
	),
	'draft'         => array(
		'class'   => 'slos-card--draft',
		'badge'   => 'slos-badge--blue',
		'label'   => __( 'Draft', 'shahi-legalflowsuite' ),
		'icon'    => 'dashicons-edit',
	),
	'published'     => array(
		'class'   => 'slos-card--published',
		'badge'   => 'slos-badge--green',
		'label'   => __( 'Published', 'shahi-legalflowsuite' ),
		'icon'    => 'dashicons-yes-alt',
	),
);

// Override for outdated status.
if ( $is_outdated && 'not_generated' !== $status ) {
	$status_config[ $status ] = array(
		'class'   => 'slos-card--outdated',
		'badge'   => 'slos-badge--orange',
		'label'   => __( 'Outdated', 'shahi-legalflowsuite' ),
		'icon'    => 'dashicons-warning',
	);
}

$current_status = $status_config[ $status ] ?? $status_config['not_generated'];
?>

<div class="slos-card <?php echo esc_attr( $current_status['class'] ); ?>" 
     data-category="<?php echo esc_attr( $category ); ?>" 
     data-type="<?php echo esc_attr( $doc_type ); ?>"
     data-doc-id="<?php echo esc_attr( $doc_id ); ?>">
	
	<!-- Card Header -->
	<div class="slos-card__header">
		<div class="slos-card__icon">
			<span class="dashicons <?php echo esc_attr( $card['icon'] ?? 'dashicons-media-document' ); ?>"></span>
		</div>
		<div class="slos-card__badge <?php echo esc_attr( $current_status['badge'] ); ?>">
			<span class="dashicons <?php echo esc_attr( $current_status['icon'] ); ?>"></span>
			<?php echo esc_html( $current_status['label'] ); ?>
		</div>
	</div>

	<!-- Card Body -->
	<div class="slos-card__body">
		<h3 class="slos-card__title"><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
		<p class="slos-card__desc"><?php echo esc_html( $card['description'] ?? '' ); ?></p>
		
		<?php if ( 'not_generated' === $status ) : ?>
			<p class="slos-widget-description" style="margin-top: 12px; font-size: 12px;">
				<?php esc_html_e( 'Generate this document from your Company Profile. Requires at least 70% profile completion. Document will include your company details, contact info, legal jurisdiction, and privacy practices.', 'shahi-legalflowsuite' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( 'not_generated' !== $status ) : ?>
			<div class="slos-card__meta">
				<?php if ( $version ) : ?>
					<span class="slos-card__meta-item">
						<span class="dashicons dashicons-info-outline"></span>
						<?php
						printf(
							/* translators: %s: version number */
							esc_html__( 'v%s', 'shahi-legalflowsuite' ),
							esc_html( $version )
						);
						?>
					</span>
				<?php endif; ?>
				
				<?php if ( $updated_at ) : ?>
					<span class="slos-card__meta-item">
						<span class="dashicons dashicons-calendar-alt"></span>
						<?php
						printf(
							/* translators: %s: date */
							esc_html__( 'Updated %s', 'shahi-legalflowsuite' ),
							esc_html( date_i18n( get_option( 'date_format' ), strtotime( $updated_at ) ) )
						);
						?>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( $is_outdated ) : ?>
				<div class="slos-card__warning">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Profile changed since generation. Regenerate to update.', 'shahi-legalflowsuite' ); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- Card Footer / Actions -->
	<div class="slos-card__footer">
		<?php if ( 'not_generated' === $status ) : ?>
			<!-- Not Generated: Show Generate Button -->
			<button type="button" 
					class="slos-btn slos-btn--primary slos-btn--block slos-hub-generate-btn" 
					data-type="<?php echo esc_attr( $doc_type ); ?>">
				<span class="dashicons dashicons-controls-play"></span>
				<?php esc_html_e( 'Generate Now', 'shahi-legalflowsuite' ); ?>
			</button>
		<?php else : ?>
			<!-- Generated: Show Action Buttons -->
			<div class="slos-card__actions">
				<!-- Primary Actions Row -->
				<div class="slos-card__actions-primary">
					<button type="button" 
							class="slos-btn slos-btn--secondary slos-btn--sm slos-hub-view-btn" 
							data-doc-id="<?php echo esc_attr( $doc_id ); ?>"
							title="<?php esc_attr_e( 'View Document', 'shahi-legalflowsuite' ); ?>">
						<span class="dashicons dashicons-visibility"></span>
						<?php esc_html_e( 'View', 'shahi-legalflowsuite' ); ?>
					</button>
					
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-edit-document&id=' . $doc_id ) ); ?>" 
					   class="slos-btn slos-btn--secondary slos-btn--sm"
					   title="<?php esc_attr_e( 'Edit Document', 'shahi-legalflowsuite' ); ?>">
						<span class="dashicons dashicons-edit"></span>
						<?php esc_html_e( 'Edit', 'shahi-legalflowsuite' ); ?>
					</a>
				</div>

				<!-- Secondary Actions Row -->
				<div class="slos-card__actions-secondary">
					<button type="button" 
							class="slos-btn slos-btn--icon slos-hub-regenerate-btn <?php echo $is_outdated ? 'slos-btn--highlight' : ''; ?>" 
							data-type="<?php echo esc_attr( $doc_type ); ?>"
							data-doc-id="<?php echo esc_attr( $doc_id ); ?>"
							title="<?php esc_attr_e( 'Regenerate from Profile', 'shahi-legalflowsuite' ); ?>">
						<span class="dashicons dashicons-update"></span>
					</button>
					
					<button type="button" 
							class="slos-btn slos-btn--icon slos-btn--disabled" 
							data-doc-id="<?php echo esc_attr( $doc_id ); ?>"
							title="<?php esc_attr_e( 'Download disabled in this stage', 'shahi-legalflowsuite' ); ?>"
							disabled aria-disabled="true">
						<span class="dashicons dashicons-download"></span>
					</button>
				
					<button type="button" 
							class="slos-btn slos-btn--icon slos-btn--disabled" 
							data-doc-id="<?php echo esc_attr( $doc_id ); ?>"
							title="<?php esc_attr_e( 'Version history unavailable in this stage', 'shahi-legalflowsuite' ); ?>"
							disabled aria-disabled="true">
						<span class="dashicons dashicons-backup"></span>
					</button>
					
					<button type="button" 
							class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
							data-shortcode="[slos_legal_doc type=&quot;<?php echo esc_attr( $doc_type ); ?>&quot;]"
							title="<?php esc_attr_e( 'Copy Shortcode', 'shahi-legalflowsuite' ); ?>">
						<span class="dashicons dashicons-clipboard"></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>