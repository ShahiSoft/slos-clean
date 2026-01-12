<?php
/**
 * Document Editor Controller
 *
 * Admin controller for editing individual legal documents.
 * Handles document loading, editing, saving, and versioning.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @since      4.1.0
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Database\Repositories\Legal_Doc_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document Editor Controller Class
 *
 * @since 4.1.0
 */
class Document_Editor_Controller {


	/**
	 * Document repository instance
	 *
	 * @var Legal_Doc_Repository
	 */
	private $doc_repository;

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		$this->doc_repository = new Legal_Doc_Repository();
	}

	/**
	 * Render the document editor page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function render() {
		// Security check..
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shahi-legalflowsuite' ) );
		}

		$doc_id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$document = null;
		$error    = null;

		if ( $doc_id > 0 ) {
			$document = $this->doc_repository->find_by_id( $doc_id );
			if ( $document && is_object( $document ) ) {
				$document = get_object_vars( $document );
				if ( isset( $document['metadata'] ) && is_object( $document['metadata'] ) ) {
					$document['metadata'] = get_object_vars( $document['metadata'] );
				}
			}
			if ( ! $document ) {
				$error = __( 'Document not found.', 'shahi-legalflowsuite' );
			}
		} else {
			$error = __( 'No document ID specified.', 'shahi-legalflowsuite' );
		}

		// Enqueue editor assets..
		$this->enqueue_assets();

		// Render the template..
		$this->render_template( $document, $error );
	}

	/**
	 * Enqueue editor assets
	 *
	 * @since 4.1.0
	 * @return void
	 */
	private function enqueue_assets() {
		// Enqueue WordPress editor..
		wp_enqueue_editor();

		// Enqueue document hub styles for consistent styling..
		wp_enqueue_style(
			'slos-document-hub',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/document-hub.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		// Localize script data..
		wp_localize_script(
			'jquery',
			'slosEditor',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slos_editor_nonce' ),
				'strings' => array(
					'saving'    => __( 'Saving...', 'shahi-legalflowsuite' ),
					'saved'     => __( 'Document saved successfully!', 'shahi-legalflowsuite' ),
					'saveError' => __( 'Error saving document. Please try again.', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Render the editor template
	 *
	 * @since 4.1.0
	 * @param array|null  $document Document data.
	 * @param string|null $error Error message if any.
	 * @return void
	 */
	private function render_template( $document, $error ) {
		?>
		<div class="wrap slos-editor-wrap">
			<h1 class="wp-heading-inline">
				<span class="dashicons dashicons-edit"></span>
				<?php
				if ( $document ) {
					printf(
						/* translators: %s: document title */
						esc_html__( 'Edit: %s', 'shahi-legalflowsuite' ),
						esc_html( $document['title'] ?? __( 'Untitled Document', 'shahi-legalflowsuite' ) )
					);
				} else {
					esc_html_e( 'Edit Document', 'shahi-legalflowsuite' );
				}
				?>
			</h1>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-documents' ) ); ?>" class="page-title-action">
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php esc_html_e( 'Back to Document Hub', 'shahi-legalflowsuite' ); ?>
			</a>

			<hr class="wp-header-end">

			<?php if ( $error ) : ?>
				<div class="notice notice-error">
					<p><?php echo esc_html( $error ); ?></p>
				</div>
			<?php elseif ( $document ) : ?>
				<form id="slos-document-editor-form" method="post">
					<?php wp_nonce_field( 'slos_save_document', 'slos_editor_nonce' ); ?>
					<input type="hidden" name="doc_id" value="<?php echo esc_attr( $document['id'] ); ?>">

					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<!-- Main Content -->
							<div id="post-body-content">
								<div id="titlediv">
									<input type="text" name="title" id="title" 
											value="<?php echo esc_attr( $document['title'] ?? '' ); ?>" 
											placeholder="<?php esc_attr_e( 'Document Title', 'shahi-legalflowsuite' ); ?>"
											class="widefat">
								</div>

								<div id="postdivrich" class="postarea wp-editor-expand">
									<?php
									wp_editor(
										$document['content'] ?? '',
										'document_content',
										array(
											'textarea_name' => 'content',
											'media_buttons' => false,
											'textarea_rows' => 20,
											'teeny'     => false,
											'quicktags' => true,
											'tinymce'   => array(
												'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
												'toolbar2' => 'styleselect,formatselect,fontselect,fontsizeselect,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
											),
										)
									);
									?>
								</div>
							</div>

							<!-- Sidebar -->
							<div id="postbox-container-1" class="postbox-container">
								<div id="submitdiv" class="postbox">
									<h2 class="hndle"><span><?php esc_html_e( 'Document Status', 'shahi-legalflowsuite' ); ?></span></h2>
									<div class="inside">
										<div class="submitbox">
											<div id="misc-publishing-actions">
												<div class="misc-pub-section">
													<span class="dashicons dashicons-post-status"></span>
													<?php esc_html_e( 'Status:', 'shahi-legalflowsuite' ); ?>
													<strong><?php echo esc_html( ucfirst( $document['status'] ?? 'draft' ) ); ?></strong>
												</div>
												<div class="misc-pub-section">
													<span class="dashicons dashicons-calendar-alt"></span>
													<?php esc_html_e( 'Created:', 'shahi-legalflowsuite' ); ?>
													<strong><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $document['created_at'] ?? '' ) ) ); ?></strong>
												</div>
												<?php if ( ! empty( $document['updated_at'] ) ) : ?>
												<div class="misc-pub-section">
													<span class="dashicons dashicons-edit"></span>
													<?php esc_html_e( 'Updated:', 'shahi-legalflowsuite' ); ?>
													<strong><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $document['updated_at'] ) ) ); ?></strong>
												</div>
												<?php endif; ?>
											</div>

											<div id="major-publishing-actions">
												<div id="publishing-action">
													<button type="submit" name="save" class="button button-primary button-large">
														<span class="dashicons dashicons-saved"></span>
														<?php esc_html_e( 'Save Changes', 'shahi-legalflowsuite' ); ?>
													</button>
												</div>
												<div class="clear"></div>
											</div>
										</div>
									</div>
								</div>

								<div id="shortcodediv" class="postbox">
									<h2 class="hndle"><span><?php esc_html_e( 'Shortcode', 'shahi-legalflowsuite' ); ?></span></h2>
									<div class="inside">
										<p><?php esc_html_e( 'Use this shortcode to display the document:', 'shahi-legalflowsuite' ); ?></p>
										<code id="slos-shortcode">[slos_legal_doc type="<?php echo esc_attr( $document['doc_type'] ?? '' ); ?>"]</code>
										<button type="button" class="button button-small slos-copy-shortcode" style="margin-top: 10px;">
											<span class="dashicons dashicons-clipboard"></span>
											<?php esc_html_e( 'Copy', 'shahi-legalflowsuite' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>

				<script>
				jQuery(document).ready(function($) {
					// Copy shortcode functionality..
					$('.slos-copy-shortcode').on('click', function() {
						var shortcode = $('#slos-shortcode').text();
						navigator.clipboard.writeText(shortcode).then(function() {
							alert('<?php echo esc_js( __( 'Shortcode copied!', 'shahi-legalflowsuite' ) ); ?>');
						});
					});

					// Form submission via AJAX..
					$('#slos-document-editor-form').on('submit', function(e) {
						e.preventDefault();
						
						var $form = $(this);
						var $button = $form.find('button[name="save"]');
						var originalText = $button.html();
						
						// Update editor content..
						if (typeof tinymce !== 'undefined' && tinymce.get('document_content')) {
							tinymce.get('document_content').save();
						}
						
						$button.html('<span class="dashicons dashicons-update spin"></span> <?php echo esc_js( __( 'Saving...', 'shahi-legalflowsuite' ) ); ?>').prop('disabled', true);
						
						$.ajax({
							url: slosEditor.ajaxUrl,
							type: 'POST',
							data: {
								action: 'slos_save_document_edit',
								nonce: slosEditor.nonce,
								doc_id: $('input[name="doc_id"]').val(),
								title: $('input[name="title"]').val(),
								content: $('textarea[name="content"]').val()
							},
							success: function(response) {
								if (response.success) {
									$button.html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js( __( 'Saved!', 'shahi-legalflowsuite' ) ); ?>');
									setTimeout(function() {
										$button.html(originalText).prop('disabled', false);
									}, 2000);
								} else {
									alert(response.data.message || slosEditor.strings.saveError);
									$button.html(originalText).prop('disabled', false);
								}
							},
							error: function() {
								alert(slosEditor.strings.saveError);
								$button.html(originalText).prop('disabled', false);
							}
						});
					});
				});
				</script>

				<style>
				.slos-editor-wrap .wp-heading-inline .dashicons {
					font-size: 24px;
					vertical-align: middle;
					margin-right: 8px;
				}
				.slos-editor-wrap .page-title-action .dashicons {
					font-size: 16px;
					vertical-align: text-top;
				}
				.slos-editor-wrap #titlediv input {
					font-size: 1.7em;
					padding: 8px 12px;
					margin-bottom: 20px;
				}
				.slos-editor-wrap .submitbox .misc-pub-section .dashicons {
					color: #82878c;
					margin-right: 5px;
				}
				.slos-editor-wrap #shortcodediv code {
					display: block;
					padding: 10px;
					background: #f0f0f1;
					border-radius: 4px;
					word-break: break-all;
				}
				.slos-editor-wrap .dashicons.spin {
					animation: spin 1s linear infinite;
				}
				@keyframes spin {
					100% { transform: rotate(360deg); }
				}
				</style>
			<?php endif; ?>
		</div>
		<?php
	}
}
