<?php
/**
 * Document Hub Dashboard Template
 *
 * Main template for the Legal Document Hub dashboard.
 * Displays profile banner, document cards, and quick actions.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Templates/Admin/Documents
 * @since       4.2.0
 *
 * @var array $data Template data from controller.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract data for convenience.
$cards      = $data['cards'] ?? array();
$categories = $data['categories'] ?? array();
$profile    = $data['profile'] ?? array();
$statistics = $data['statistics'] ?? array();
$outdated   = $data['outdated'] ?? array();
$nonce      = $data['nonce'] ?? '';
?>

<style>
/* Legal Documents Hub - Glowing White Header */
.slos-hub-wrap .slos-hub-title {
	display: flex;
	align-items: center;
	gap: 12px;
	font-size: 28px !important;
	font-weight: 700 !important;
	color: #ffffff !important;
	margin: 0 0 8px;
	text-shadow: 
		0 0 10px rgba(255, 255, 255, 0.8),
		0 0 20px rgba(255, 255, 255, 0.6),
		0 0 40px rgba(255, 255, 255, 0.4),
		0 0 60px rgba(147, 197, 253, 0.3) !important;
	letter-spacing: 0.5px;
}

.slos-hub-wrap .slos-hub-title .dashicons {
	font-size: 32px;
	width: 32px;
	height: 32px;
	color: #3b82f6 !important;
}

/* Legal Documents Hub - Blue/White Gradient Tabs */
.slos-hub-wrap .slos-hub-filters__tabs {
	display: flex;
	gap: 12px;
}

.slos-hub-wrap .slos-hub-filter-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 12px 20px;
	font-size: 14px;
	font-weight: 500;
	color: #f8fafc !important;
	background: #1e293b !important;
	border: 2px solid #334155 !important;
	border-radius: 8px;
	cursor: pointer;
	transition: all 0.2s ease;
}

.slos-hub-wrap .slos-hub-filter-btn:hover {
	background: #475569 !important;
	color: #3b82f6 !important;
	border-color: #3b82f6 !important;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.slos-hub-wrap .slos-hub-filter-btn--active {
	background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%) !important;
	color: #1e3a5f !important;
	font-weight: 700 !important;
	border-color: #3b82f6 !important;
	box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 197, 253, 0.3) !important;
	transform: translateY(-2px);
}
</style>

<div class="wrap slos-hub-wrap">
	<div class="slos-hub-header">
		<h1 class="slos-hub-title">
			<span class="dashicons dashicons-media-document"></span>
			<?php esc_html_e( 'Legal Document Hub', 'shahi-legalflowsuite' ); ?>
		</h1>
		<p class="slos-hub-subtitle">
			<?php esc_html_e( 'Generate and manage your legal documents from your company profile.', 'shahi-legalflowsuite' ); ?>
		</p>
		<p class="slos-section-description">
			<?php esc_html_e( 'Auto-generate professional legal documents (Privacy Policy, Terms & Conditions, Cookie Policy, etc.) pre-filled with your company information. Documents sync with your Company Profile—when you update profile details, outdated documents show a warning prompting regeneration. Each document supports versioning, shortcodes for frontend display, and manual editing after generation.', 'shahi-legalflowsuite' ); ?>
		</p>
	</div>

	<?php
	// Profile completion banner.
	if ( isset( $profile['exists'] ) && $profile['exists'] ) {
		include SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'templates/admin/documents/partials/profile-banner.php';
	} else {
		// No profile exists - show setup prompt.
		?>
		<div class="slos-hub-banner slos-hub-banner--setup">
			<div class="slos-hub-banner__icon">
				<span class="dashicons dashicons-info-outline"></span>
			</div>
			<div class="slos-hub-banner__content">
				<h3 class="slos-hub-banner__title">
					<?php esc_html_e( 'Get Started with Your Legal Documents', 'shahi-legalflowsuite' ); ?>
				</h3>
				<p class="slos-hub-banner__text">
					<?php esc_html_e( 'Complete your company profile to auto-generate professional legal documents. It only takes about 15 minutes.', 'shahi-legalflowsuite' ); ?>
				</p>
			</div>
			<div class="slos-hub-banner__actions">
				<a href="<?php echo esc_url( $data['profile_url'] ); ?>" class="slos-btn slos-btn--primary">
					<span class="dashicons dashicons-edit"></span>
					<?php esc_html_e( 'Start Profile Setup', 'shahi-legalflowsuite' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
	?>

	<?php if ( ! empty( $outdated ) ) : ?>
	<div class="slos-hub-alert slos-hub-alert--warning">
		<span class="dashicons dashicons-warning"></span>
		<span>
			<?php
			printf(
				/* translators: %d: number of outdated documents */
				esc_html(
					_n(
						'%d document is outdated. Your profile has changed since it was generated.',
						'%d documents are outdated. Your profile has changed since they were generated.',
						count( $outdated ),
						'shahi-legalflowsuite'
					)
				),
				count( $outdated )
			);
			?>
		</span>
		<button type="button" class="slos-btn slos-btn--sm slos-btn--warning slos-hub-regenerate-all" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Regenerate All', 'shahi-legalflowsuite' ); ?>
		</button>
	</div>
	<?php endif; ?>

	<!-- Category Filters -->
	<p class="slos-widget-description" style="margin: 20px 0 16px;">
		<?php esc_html_e( 'Filter documents by category (All, Privacy, Terms, Compliance). Categories organize documents by legal purpose. Export All downloads all generated documents as ZIP archive.', 'shahi-legalflowsuite' ); ?>
	</p>
	<div class="slos-hub-filters">
		<div class="slos-hub-filters__tabs">
			<?php foreach ( $categories as $key => $label ) : ?>
				<button type="button" 
						class="slos-hub-filter-btn <?php echo 'all' === $key ? 'slos-hub-filter-btn--active' : ''; ?>" 
						data-filter="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="slos-hub-filters__actions">
			<button type="button" class="slos-btn slos-btn--secondary slos-btn--sm slos-hub-export-all" 
					title="<?php esc_attr_e( 'Export all documents', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Export All', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
	</div>

	<!-- Quick Stats Panel -->
	<div class="slos-hub-stats" style="margin: 24px 0;">
		<div class="slos-hub-stats__item">
			<span class="slos-hub-stats__value"><?php echo esc_html( $statistics['total_generated'] ?? 0 ); ?></span>
			<span class="slos-hub-stats__label"><?php esc_html_e( 'Documents Generated', 'shahi-legalflowsuite' ); ?></span>
			<span class="slos-hub-stats__desc"><?php esc_html_e( 'Total number of legal documents created from your profile. Includes drafts and published documents.', 'shahi-legalflowsuite' ); ?></span>
		</div>
		<div class="slos-hub-stats__item">
			<span class="slos-hub-stats__value"><?php echo esc_html( $profile['completeness'] ?? 0 ); ?>%</span>
			<span class="slos-hub-stats__label"><?php esc_html_e( 'Profile Complete', 'shahi-legalflowsuite' ); ?></span>
			<span class="slos-hub-stats__desc"><?php esc_html_e( 'Your Company Profile completion percentage. 70%+ enables generation, 100% recommended for best quality.', 'shahi-legalflowsuite' ); ?></span>
		</div>
		<div class="slos-hub-stats__item">
			<span class="slos-hub-stats__value <?php echo ( $statistics['needs_attention'] ?? 0 ) > 0 ? 'slos-hub-stats__value--warning' : ''; ?>">
				<?php echo esc_html( $statistics['needs_attention'] ?? 0 ); ?>
			</span>
			<span class="slos-hub-stats__label"><?php esc_html_e( 'Needs Attention', 'shahi-legalflowsuite' ); ?></span>
			<span class="slos-hub-stats__desc"><?php esc_html_e( 'Documents marked as outdated due to profile changes. Click Regenerate to update with current data.', 'shahi-legalflowsuite' ); ?></span>
		</div>
	</div>

	<!-- Document Cards Grid -->
	<p class="slos-widget-description" style="margin: 20px 0 16px;">
		<?php esc_html_e( 'Each card represents a legal document type. Not Generated = no document created yet. Draft = generated but not published. Published = live document. Outdated = profile changed since generation. Use Generate/Regenerate buttons to create or update documents. View opens preview modal, Edit opens WordPress editor.', 'shahi-legalflowsuite' ); ?>
	</p>
	<div class="slos-hub-grid">
		<?php if ( ! empty( $cards ) ) : ?>
			<?php foreach ( $cards as $card ) : ?>
				<?php
				// Pass card data to partial.
				include SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'templates/admin/documents/partials/document-card.php';
				?>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="slos-hub-empty">
				<span class="dashicons dashicons-media-document"></span>
				<p><?php esc_html_e( 'No document types configured.', 'shahi-legalflowsuite' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Shortcode Reference -->
	<p class="slos-widget-description" style="margin: 20px 0 16px;">
		<?php esc_html_e( 'Shortcodes display documents on any page or post. Copy shortcode from card action buttons or use examples below. Format: [slos_legal_doc type="document_type"]. Documents must be generated and published before shortcodes display content.', 'shahi-legalflowsuite' ); ?>
	</p>
	<div class="slos-hub-shortcodes">
		<h3 class="slos-hub-shortcodes__title">
			<span class="dashicons dashicons-shortcode"></span>
			<?php esc_html_e( 'Display Documents on Your Site', 'shahi-legalflowsuite' ); ?>
		</h3>
		<p class="slos-hub-shortcodes__desc">
			<?php esc_html_e( 'Use these shortcodes to display your legal documents on any page or post:', 'shahi-legalflowsuite' ); ?>
		</p>
		<div class="slos-hub-shortcodes__list">
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="privacy-policy"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="privacy-policy"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="terms-of-service"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="terms-of-service"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="cookie-policy"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="cookie-policy"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="refund-policy"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="refund-policy"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="shipping-policy"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="shipping-policy"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalflowsuite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Review & Generate Modal -->
<div id="slos-hub-modal" class="slos-modal" role="dialog" aria-modal="true" aria-labelledby="slos-hub-modal-title">
	<div class="slos-modal__backdrop"></div>
	<div class="slos-modal__container">
		<div class="slos-modal__header">
			<h2 id="slos-hub-modal-title" class="slos-modal__title"><?php esc_html_e( 'Generate Document', 'shahi-legalflowsuite' ); ?></h2>
			<button type="button" class="slos-modal__close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="slos-modal__body">
			<div class="slos-modal__loading">
				<span class="slos-spinner"></span>
				<span><?php esc_html_e( 'Loading...', 'shahi-legalflowsuite' ); ?></span>
			</div>
		</div>
		<div class="slos-modal__footer">
			<button type="button" class="slos-btn slos-btn--secondary slos-modal__cancel">
				<?php esc_html_e( 'Cancel', 'shahi-legalflowsuite' ); ?>
			</button>
			<button type="button" class="slos-btn slos-btn--primary slos-hub-confirm-generate" disabled>
				<span class="dashicons dashicons-yes"></span>
				<?php esc_html_e( 'Generate Document', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
	</div>
</div>

<!-- View Document Modal -->
<div id="slos-hub-view-modal" class="slos-modal slos-modal--large" role="dialog" aria-modal="true" aria-labelledby="slos-hub-view-title">
	<div class="slos-modal__backdrop"></div>
	<div class="slos-modal__container">
		<div class="slos-modal__header">
			<h2 id="slos-hub-view-title" class="slos-modal__title"><?php esc_html_e( 'Document Preview', 'shahi-legalflowsuite' ); ?></h2>
			<button type="button" class="slos-modal__close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="slos-modal__body">
			<div class="slos-hub-preview-content"></div>
		</div>
		<div class="slos-modal__footer">
			<button type="button" class="slos-btn slos-btn--secondary slos-modal__cancel">
				<?php esc_html_e( 'Close', 'shahi-legalflowsuite' ); ?>
			</button>
			<a href="#" class="slos-btn slos-btn--primary slos-hub-edit-doc" target="_blank">
				<span class="dashicons dashicons-edit"></span>
				<?php esc_html_e( 'Edit Document', 'shahi-legalflowsuite' ); ?>
			</a>
		</div>
	</div>
</div>

<!-- Version History Modal -->
<div id="slos-hub-history-modal" class="slos-modal" role="dialog" aria-modal="true" aria-labelledby="slos-hub-history-title">
	<div class="slos-modal__backdrop"></div>
	<div class="slos-modal__container">
		<div class="slos-modal__header">
			<h2 id="slos-hub-history-title" class="slos-modal__title"><?php esc_html_e( 'Version History', 'shahi-legalflowsuite' ); ?></h2>
			<button type="button" class="slos-modal__close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalflowsuite' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="slos-modal__body">
			<div class="slos-hub-history-list"></div>
		</div>
		<div class="slos-modal__footer">
			<button type="button" class="slos-btn slos-btn--secondary slos-modal__cancel">
				<?php esc_html_e( 'Close', 'shahi-legalflowsuite' ); ?>
			</button>
		</div>
	</div>
</div>

<input type="hidden" id="slos-hub-nonce" value="<?php echo esc_attr( $nonce ); ?>" />
