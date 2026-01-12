<?php
/**
 * DSR Settings Page
 *
 * Admin interface for configuring DSR Portal behavior, SLA rules, notifications, and data sources.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Settings Class
 *
 * @since 3.0.1
 */
class DSR_Settings {

	/**
	 * Option key for DSR settings
	 *
	 * @var string
	 */
	private const OPTION_KEY = 'slos_dsr_settings';

	/**
	 * Capability required
	 *
	 * @var string
	 */
	private const CAPABILITY = 'manage_options';

	/**
	 * Flag to track if hooks have been registered
	 *
	 * @var bool
	 */
	private static bool $hooks_registered = false;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @since 3.0.2 Removed admin_menu registration - now part of DSRMainPage tabs
	 */
	public function __construct() {
		// Prevent registering hooks multiple times..
		if ( self::$hooks_registered ) {
			return;
		}
		self::$hooks_registered = true;

		// Menu registration removed - settings now in DSRMainPage "Settings" tab..
		// Page still accessible as hidden page if needed for backward compatibility..
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register settings page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_page(): void {
		add_submenu_page(
			'shahi-legalflowsuite',
			__( 'DSR Settings', 'shahi-legalflowsuite' ),
			__( 'DSR Settings', 'shahi-legalflowsuite' ),
			self::CAPABILITY,
			'slos-dsr-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings and sections
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'slos_dsr_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest'      => false,
			)
		);

		// SLA Settings Section..
		add_settings_section(
			'slos_dsr_sla',
			__( 'SLA Configuration (Business Days)', 'shahi-legalflowsuite' ),
			array( $this, 'render_sla_section' ),
			'slos_dsr_settings'
		);

		$regulations = array(
			'GDPR'    => __( 'GDPR', 'shahi-legalflowsuite' ),
			'UK-GDPR' => __( 'UK-GDPR', 'shahi-legalflowsuite' ),
			'CCPA'    => __( 'CCPA', 'shahi-legalflowsuite' ),
			'LGPD'    => __( 'LGPD (Brazil)', 'shahi-legalflowsuite' ),
			'PIPEDA'  => __( 'PIPEDA (Canada)', 'shahi-legalflowsuite' ),
			'POPIA'   => __( 'POPIA (South Africa)', 'shahi-legalflowsuite' ),
		);

		foreach ( $regulations as $code => $label ) {
			add_settings_field(
				'slos_dsr_sla_' . strtolower( $code ),
				$label,
				array( $this, 'render_sla_field' ),
				'slos_dsr_settings',
				'slos_dsr_sla',
				array( 'regulation' => $code )
			);
		}

		// Data Sources Section..
		add_settings_section(
			'slos_dsr_sources',
			__( 'Data Sources to Search', 'shahi-legalflowsuite' ),
			array( $this, 'render_sources_section' ),
			'slos_dsr_settings'
		);

		$sources = array(
			'posts'    => __( 'Posts & Pages', 'shahi-legalflowsuite' ),
			'users'    => __( 'User Accounts', 'shahi-legalflowsuite' ),
			'comments' => __( 'Comments', 'shahi-legalflowsuite' ),
			'forms'    => __( 'Form Submissions', 'shahi-legalflowsuite' ),
			'logs'     => __( 'Activity Logs', 'shahi-legalflowsuite' ),
		);

		foreach ( $sources as $key => $label ) {
			add_settings_field(
				'slos_dsr_source_' . $key,
				$label,
				array( $this, 'render_source_checkbox' ),
				'slos_dsr_settings',
				'slos_dsr_sources',
				array( 'source' => $key )
			);
		}

		// Notifications Section..
		add_settings_section(
			'slos_dsr_notifications',
			__( 'Notifications & Emails', 'shahi-legalflowsuite' ),
			array( $this, 'render_notifications_section' ),
			'slos_dsr_settings'
		);

		add_settings_field(
			'slos_dsr_notify_requester',
			__( 'Email Requester on Status Changes', 'shahi-legalflowsuite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_notifications',
			array( 'key' => 'notify_requester' )
		);

		add_settings_field(
			'slos_dsr_notify_admin',
			__( 'Email Admin on New Requests', 'shahi-legalflowsuite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_notifications',
			array( 'key' => 'notify_admin' )
		);

		add_settings_field(
			'slos_dsr_notify_overdue',
			__( 'Alert on SLA Breach (Admin)', 'shahi-legalflowsuite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_notifications',
			array( 'key' => 'notify_overdue' )
		);

		// Portal Appearance Section..
		add_settings_section(
			'slos_dsr_appearance',
			__( 'Portal Appearance', 'shahi-legalflowsuite' ),
			array( $this, 'render_appearance_section' ),
			'slos_dsr_settings'
		);

		add_settings_field(
			'slos_dsr_form_title',
			__( 'Form Page Title', 'shahi-legalflowsuite' ),
			array( $this, 'render_text_field' ),
			'slos_dsr_settings',
			'slos_dsr_appearance',
			array(
				'key'         => 'form_title',
				'default'     => __( 'Submit a Data Subject Request', 'shahi-legalflowsuite' ),
				'placeholder' => __( 'Submit a Data Subject Request', 'shahi-legalflowsuite' ),
			)
		);

		add_settings_field(
			'slos_dsr_form_description',
			__( 'Form Description', 'shahi-legalflowsuite' ),
			array( $this, 'render_textarea_field' ),
			'slos_dsr_settings',
			'slos_dsr_appearance',
			array(
				'key'         => 'form_description',
				'default'     => __( 'Exercise your data privacy rights. Submit a request and we\'ll respond within the applicable SLA.', 'shahi-legalflowsuite' ),
				'placeholder' => __( 'Description shown above form', 'shahi-legalflowsuite' ),
				'rows'        => 3,
			)
		);

		add_settings_field(
			'slos_dsr_privacy_policy_url',
			__( 'Privacy Policy URL (linked in form)', 'shahi-legalflowsuite' ),
			array( $this, 'render_text_field' ),
			'slos_dsr_settings',
			'slos_dsr_appearance',
			array(
				'key'         => 'privacy_policy_url',
				'type'        => 'url',
				'default'     => home_url( '/privacy-policy/' ),
				'placeholder' => home_url( '/privacy-policy/' ),
			)
		);

		// Advanced Section..
		add_settings_section(
			'slos_dsr_advanced',
			__( 'Advanced Options', 'shahi-legalflowsuite' ),
			array( $this, 'render_advanced_section' ),
			'slos_dsr_settings'
		);

		add_settings_field(
			'slos_dsr_require_identity_verification',
			__( 'Require Identity Verification (Upload ID)', 'shahi-legalflowsuite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_advanced',
			array( 'key' => 'require_identity_verification' )
		);

		add_settings_field(
			'slos_dsr_enable_encryption',
			__( 'Encrypt PII in Database', 'shahi-legalflowsuite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_advanced',
			array( 'key' => 'enable_encryption' )
		);

		add_settings_field(
			'slos_dsr_auto_delete_days',
			__( 'Auto-delete Completed Requests After (days)', 'shahi-legalflowsuite' ),
			array( $this, 'render_number_field' ),
			'slos_dsr_settings',
			'slos_dsr_advanced',
			array(
				'key'     => 'auto_delete_days',
				'default' => 365,
				'min'     => 0,
				'max'     => 3650,
			)
		);
	}

	/**
	 * Render settings page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized', 'shahi-legalflowsuite' ) );
		}

		?>
		<div class="wrap slos-dsr-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php $this->render_page_content(); ?>
		</div>
		<?php
	}

	/**
	 * Render just the page content (for use in tabbed interface)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_page_content(): void {
		$settings = $this->get_settings();
		$defaults = $this->get_sla_defaults();

		?>
		<div class="slos-settings-modern">
			<p class="slos-section-description">
				<?php esc_html_e( 'Configure DSR portal settings including SLA deadlines, data sources, and notification preferences. All changes take effect immediately after saving.', 'shahi-legalflowsuite' ); ?>
			</p>
			<form method="post" action="options.php" class="slos-settings-form">
				<?php settings_fields( 'slos_dsr_settings_group' ); ?>
				
				<div class="slos-settings-layout">
					<!-- Left Column - Main Settings -->
					<div class="slos-settings-main">
						<!-- General Settings Card -->
						<div class="slos-settings-card">
							<div class="slos-card-header">
								<h3>
									<span class="dashicons dashicons-admin-generic"></span>
									<?php esc_html_e( 'General Settings', 'shahi-legalflowsuite' ); ?>
								</h3>
								<p><?php esc_html_e( 'Configure basic DSR portal appearance and behavior', 'shahi-legalflowsuite' ); ?></p>
							</div>
							<p class="slos-widget-description"><?php esc_html_e( 'Customize the public-facing DSR submission form. These settings control what users see when requesting access to their data.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-card-body">
								<div class="slos-form-group">
									<label for="form_title" class="slos-form-label">
										<?php esc_html_e( 'Form Title', 'shahi-legalflowsuite' ); ?>
									</label>
									<input type="text" 
											id="form_title" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[form_title]" 
											value="<?php echo esc_attr( $settings['form_title'] ?? 'Submit a Data Subject Request' ); ?>"
											class="slos-input"
											placeholder="<?php esc_attr_e( 'Submit a Data Subject Request', 'shahi-legalflowsuite' ); ?>" />
								</div>
								
								<div class="slos-form-group">
									<label for="form_description" class="slos-form-label">
										<?php esc_html_e( 'Form Description', 'shahi-legalflowsuite' ); ?>
									</label>
									<textarea id="form_description" 
												name="<?php echo esc_attr( self::OPTION_KEY ); ?>[form_description]" 
												class="slos-textarea"
												rows="3"
												placeholder="<?php esc_attr_e( 'Description shown above the form', 'shahi-legalflowsuite' ); ?>"><?php echo esc_textarea( $settings['form_description'] ?? '' ); ?></textarea>
								</div>
								
								<div class="slos-form-group">
									<label for="privacy_policy_url" class="slos-form-label">
										<?php esc_html_e( 'Privacy Policy URL', 'shahi-legalflowsuite' ); ?>
									</label>
									<input type="url" 
											id="privacy_policy_url" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[privacy_policy_url]" 
											value="<?php echo esc_url( $settings['privacy_policy_url'] ?? home_url( '/privacy-policy/' ) ); ?>"
											class="slos-input"
											placeholder="<?php echo esc_attr( home_url( '/privacy-policy/' ) ); ?>" />
									<p class="slos-help-text">
										<?php esc_html_e( 'Link displayed in the DSR form footer', 'shahi-legalflowsuite' ); ?>
									</p>
								</div>
							</div>
						</div>

						<!-- SLA Configuration Card -->
						<div class="slos-settings-card">
							<div class="slos-card-header">
								<h3>
									<span class="dashicons dashicons-clock"></span>
									<?php esc_html_e( 'SLA Configuration', 'shahi-legalflowsuite' ); ?>
								</h3>
								<p><?php esc_html_e( 'Set response deadlines in business days for each regulation', 'shahi-legalflowsuite' ); ?></p>
							</div>
							<p class="slos-widget-description"><?php esc_html_e( 'Define regulatory compliance deadlines for each privacy framework. Requests approaching deadlines will trigger alerts and appear in the SLA Monitor.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-card-body">
								<div class="slos-sla-grid">
									<?php
									$regulations = array(
										'GDPR'    => array(
											'name'    => 'GDPR',
											'default' => 30,
											'flag'    => '🇪🇺',
										),
										'UK-GDPR' => array(
											'name'    => 'UK-GDPR',
											'default' => 30,
											'flag'    => '🇬🇧',
										),
										'CCPA'    => array(
											'name'    => 'CCPA',
											'default' => 45,
											'flag'    => '🇺🇸',
										),
										'LGPD'    => array(
											'name'    => 'LGPD',
											'default' => 15,
											'flag'    => '🇧🇷',
										),
										'PIPEDA'  => array(
											'name'    => 'PIPEDA',
											'default' => 30,
											'flag'    => '🇨🇦',
										),
										'POPIA'   => array(
											'name'    => 'POPIA',
											'default' => 30,
											'flag'    => '🇿🇦',
										),
									);

									foreach ( $regulations as $code => $info ) :
										$key   = 'sla_' . strtolower( str_replace( '-', '_', $code ) );
										$value = $settings[ $key ] ?? $info['default'];
										?>
										<div class="slos-sla-item">
											<div class="slos-sla-label">
												<span class="slos-flag"><?php echo esc_html( $info['flag'] ); ?></span>
												<span class="slos-reg-name"><?php echo esc_html( $info['name'] ); ?></span>
											</div>
											<div class="slos-sla-input">
												<input type="number" 
														name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" 
														value="<?php echo esc_attr( $value ); ?>"
														class="slos-input-number"
														min="1"
														max="365" />
												<span class="slos-input-suffix"><?php esc_html_e( 'days', 'shahi-legalflowsuite' ); ?></span>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<!-- Data Sources Card -->
						<div class="slos-settings-card">
							<div class="slos-card-header">
								<h3>
									<span class="dashicons dashicons-database"></span>
									<?php esc_html_e( 'Data Sources', 'shahi-legalflowsuite' ); ?>
								</h3>
								<p><?php esc_html_e( 'Select which data sources to search for DSR processing', 'shahi-legalflowsuite' ); ?></p>
							</div>
							<p class="slos-widget-description"><?php esc_html_e( 'Enable or disable data locations searched during request processing. Unchecked sources will be excluded from access, deletion, and portability operations.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-card-body">
								<div class="slos-checkbox-grid">
									<?php
									$sources = array(
										'posts'    => array(
											'label' => 'Posts & Pages',
											'icon'  => 'dashicons-admin-post',
										),
										'users'    => array(
											'label' => 'User Accounts',
											'icon'  => 'dashicons-admin-users',
										),
										'comments' => array(
											'label' => 'Comments',
											'icon'  => 'dashicons-admin-comments',
										),
										'forms'    => array(
											'label' => 'Form Submissions',
											'icon'  => 'dashicons-feedback',
										),
										'logs'     => array(
											'label' => 'Activity Logs',
											'icon'  => 'dashicons-list-view',
										),
									);

									foreach ( $sources as $key => $info ) :
										$checked = ! empty( $settings[ 'source_' . $key ] );
										?>
										<label class="slos-checkbox-card">
											<input type="checkbox" 
													name="<?php echo esc_attr( self::OPTION_KEY ); ?>[source_<?php echo esc_attr( $key ); ?>]" 
													value="1"
													<?php checked( $checked ); ?> />
											<div class="slos-checkbox-content">
												<span class="dashicons <?php echo esc_attr( $info['icon'] ); ?>"></span>
												<span class="slos-checkbox-label"><?php echo esc_html( $info['label'] ); ?></span>
											</div>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<!-- Right Column - Quick Settings Sidebar -->
					<div class="slos-settings-sidebar">
						<!-- Notifications Widget -->
						<div class="slos-settings-widget">
							<h4 class="slos-widget-title">
								<span class="dashicons dashicons-email"></span>
								<?php esc_html_e( 'Notifications', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p class="slos-widget-description"><?php esc_html_e( 'Control automated email alerts. Enable notifications to keep stakeholders informed of request progress and SLA concerns.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-widget-body">
								<label class="slos-toggle-item">
									<input type="checkbox" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[notify_requester]" 
											value="1"
											<?php checked( ! empty( $settings['notify_requester'] ) ); ?> />
									<span class="slos-toggle-slider"></span>
									<span class="slos-toggle-label"><?php esc_html_e( 'Email requester on status changes', 'shahi-legalflowsuite' ); ?></span>
								</label>
								
								<label class="slos-toggle-item">
									<input type="checkbox" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[notify_admin]" 
											value="1"
											<?php checked( ! empty( $settings['notify_admin'] ) ); ?> />
									<span class="slos-toggle-slider"></span>
									<span class="slos-toggle-label"><?php esc_html_e( 'Email admin on new requests', 'shahi-legalflowsuite' ); ?></span>
								</label>
								
								<label class="slos-toggle-item">
									<input type="checkbox" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[notify_overdue]" 
											value="1"
											<?php checked( ! empty( $settings['notify_overdue'] ) ); ?> />
									<span class="slos-toggle-slider"></span>
									<span class="slos-toggle-label"><?php esc_html_e( 'Alert on SLA breaches', 'shahi-legalflowsuite' ); ?></span>
								</label>
							</div>
						</div>

						<!-- Advanced Options Widget -->
						<div class="slos-settings-widget">
							<h4 class="slos-widget-title">
								<span class="dashicons dashicons-admin-tools"></span>
								<?php esc_html_e( 'Advanced', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p class="slos-widget-description"><?php esc_html_e( 'Security and data retention options. Use caution—encryption and auto-deletion affect data recovery.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-widget-body">
								<label class="slos-toggle-item">
									<input type="checkbox" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[require_identity_verification]" 
											value="1"
											<?php checked( ! empty( $settings['require_identity_verification'] ) ); ?> />
									<span class="slos-toggle-slider"></span>
									<span class="slos-toggle-label"><?php esc_html_e( 'Require ID verification', 'shahi-legalflowsuite' ); ?></span>
								</label>
								
								<label class="slos-toggle-item">
									<input type="checkbox" 
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enable_encryption]" 
											value="1"
											<?php checked( ! empty( $settings['enable_encryption'] ) ); ?> />
									<span class="slos-toggle-slider"></span>
									<span class="slos-toggle-label"><?php esc_html_e( 'Encrypt PII in database', 'shahi-legalflowsuite' ); ?></span>
								</label>
								
								<div class="slos-form-group-compact">
									<label for="auto_delete_days" class="slos-form-label-small">
										<?php esc_html_e( 'Auto-delete after', 'shahi-legalflowsuite' ); ?>
									</label>
									<div class="slos-input-with-suffix">
										<input type="number" 
												id="auto_delete_days" 
												name="<?php echo esc_attr( self::OPTION_KEY ); ?>[auto_delete_days]" 
												value="<?php echo esc_attr( $settings['auto_delete_days'] ?? 365 ); ?>"
												class="slos-input-sm"
												min="0"
												max="3650" />
										<span class="slos-suffix"><?php esc_html_e( 'days', 'shahi-legalflowsuite' ); ?></span>
									</div>
								</div>
							</div>
						</div>

						<!-- Save Button Widget -->
						<div class="slos-settings-widget slos-save-widget">
							<p class="slos-widget-description"><?php esc_html_e( 'Review all settings before saving. Changes apply to all new and existing requests immediately.', 'shahi-legalflowsuite' ); ?></p>
							<button type="submit" class="slos-btn-save">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Save All Settings', 'shahi-legalflowsuite' ); ?>
							</button>
							<p class="slos-save-help">
								<?php esc_html_e( 'Changes take effect immediately', 'shahi-legalflowsuite' ); ?>
							</p>
						</div>

						<!-- System Info Widget -->
						<div class="slos-settings-widget slos-info-widget">
							<h4 class="slos-widget-title">
								<span class="dashicons dashicons-info"></span>
								<?php esc_html_e( 'System Info', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p class="slos-widget-description"><?php esc_html_e( 'Current DSR portal status and plugin version. Reference this information when reporting issues.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-widget-body">
								<div class="slos-info-item">
									<span class="slos-info-label"><?php esc_html_e( 'Plugin Version', 'shahi-legalflowsuite' ); ?></span>
									<span class="slos-info-value">3.0.1</span>
								</div>
								<div class="slos-info-item">
									<span class="slos-info-label"><?php esc_html_e( 'Last Update', 'shahi-legalflowsuite' ); ?></span>
									<span class="slos-info-value"><?php echo esc_html( date_i18n( 'M j, Y', current_time( 'timestamp' ) ) ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render SLA section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_sla_section(): void {
		echo wp_kses_post( __( 'Set the Service Level Agreement (SLA) deadline in business days for each regulation. Defaults are regulatory minimums.', 'shahi-legalflowsuite' ) );
	}

	/**
	 * Render SLA input field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_sla_field( array $args ): void {
		$regulation = $args['regulation'] ?? '';
		$settings   = $this->get_settings();
		$defaults   = $this->get_sla_defaults();
		$value      = $settings[ 'sla_' . strtolower( $regulation ) ] ?? $defaults[ $regulation ];

		?>
		<input 
			type="number" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[sla_' . strtolower( $regulation ) . ']' ); ?>" 
			value="<?php echo esc_attr( $value ); ?>" 
			min="1" 
			max="365"
			class="small-text"
		/>
		<span class="description"><?php esc_html_e( 'business days', 'shahi-legalflowsuite' ); ?></span>
		<?php
	}

	/**
	 * Render sources section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_sources_section(): void {
		echo wp_kses_post( __( 'Select which data sources to search when processing DSR requests.', 'shahi-legalflowsuite' ) );
	}

	/**
	 * Render data source checkbox
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_source_checkbox( array $args ): void {
		$source   = $args['source'] ?? '';
		$settings = $this->get_settings();
		$checked  = isset( $settings['sources'][ $source ] ) ? (bool) $settings['sources'][ $source ] : true;

		?>
		<input 
			type="checkbox" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[sources][' . $source . ']' ); ?>" 
			value="1"
			<?php checked( $checked ); ?>
		/>
		<?php
	}

	/**
	 * Render notifications section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_notifications_section(): void {
		echo wp_kses_post( __( 'Configure email notifications for DSR lifecycle events.', 'shahi-legalflowsuite' ) );
	}

	/**
	 * Render appearance section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_appearance_section(): void {
		echo wp_kses_post( __( 'Customize the DSR form appearance and messaging.', 'shahi-legalflowsuite' ) );
	}

	/**
	 * Render advanced section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_advanced_section(): void {
		echo wp_kses_post( __( 'Advanced options for security and data handling.', 'shahi-legalflowsuite' ) );
	}

	/**
	 * Render generic checkbox field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_checkbox( array $args ): void {
		$key      = $args['key'] ?? '';
		$settings = $this->get_settings();
		$checked  = isset( $settings[ $key ] ) ? (bool) $settings[ $key ] : false;

		?>
		<input 
			type="checkbox" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>" 
			value="1"
			<?php checked( $checked ); ?>
		/>
		<?php
	}

	/**
	 * Render text field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_text_field( array $args ): void {
		$key         = $args['key'] ?? '';
		$type        = $args['type'] ?? 'text';
		$default     = $args['default'] ?? '';
		$placeholder = $args['placeholder'] ?? '';
		$settings    = $this->get_settings();
		$value       = $settings[ $key ] ?? $default;

		?>
		<input 
			type="<?php echo esc_attr( $type ); ?>" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>" 
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="regular-text"
		/>
		<?php
	}

	/**
	 * Render textarea field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_textarea_field( array $args ): void {
		$key         = $args['key'] ?? '';
		$rows        = $args['rows'] ?? 5;
		$default     = $args['default'] ?? '';
		$placeholder = $args['placeholder'] ?? '';
		$settings    = $this->get_settings();
		$value       = $settings[ $key ] ?? $default;

		?>
		<textarea 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>"
			rows="<?php echo absint( $rows ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="large-text"
		><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	/**
	 * Render number field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_number_field( array $args ): void {
		$key      = $args['key'] ?? '';
		$default  = $args['default'] ?? 0;
		$min      = $args['min'] ?? 0;
		$max      = $args['max'] ?? 9999;
		$settings = $this->get_settings();
		$value    = $settings[ $key ] ?? $default;

		?>
		<input 
			type="number" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>" 
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"
			class="small-text"
		/>
		<?php
	}

	/**
	 * Get all DSR settings
	 *
	 * @since 3.0.1
	 * @return array Settings array
	 */
	public function get_settings(): array {
		$settings = get_option( self::OPTION_KEY, array() );
		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Get default SLA days per regulation
	 *
	 * @since 3.0.1
	 * @return array Regulation => days
	 */
	private function get_sla_defaults(): array {
		return array(
			'GDPR'    => 30,
			'UK-GDPR' => 30,
			'CCPA'    => 45,
			'LGPD'    => 15,
			'PIPEDA'  => 30,
			'POPIA'   => 30,
		);
	}

	/**
	 * Sanitize and validate settings on save
	 *
	 * @since 3.0.1
	 * @param array $input Raw input from form.
	 * @return array Sanitized settings
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		// Sanitize SLA fields (must be positive integers)..
		foreach ( array( 'GDPR', 'UK-GDPR', 'CCPA', 'LGPD', 'PIPEDA', 'POPIA' ) as $reg ) {
			$key = 'sla_' . strtolower( $reg );
			if ( isset( $input[ $key ] ) ) {
				$sanitized[ $key ] = absint( $input[ $key ] );
				if ( $sanitized[ $key ] < 1 || $sanitized[ $key ] > 365 ) {
					$sanitized[ $key ] = $this->get_sla_defaults()[ $reg ];
				}
			}
		}

		// Sanitize checkboxes..
		$checkbox_fields = array(
			'notify_requester',
			'notify_admin',
			'notify_overdue',
			'require_identity_verification',
			'enable_encryption',
		);
		foreach ( $checkbox_fields as $field ) {
			$sanitized[ $field ] = isset( $input[ $field ] ) && $input[ $field ] ? true : false;
		}

		// Sanitize sources..
		if ( isset( $input['sources'] ) && is_array( $input['sources'] ) ) {
			$sanitized['sources'] = array();
			foreach ( array( 'posts', 'users', 'comments', 'forms', 'logs' ) as $source ) {
				$sanitized['sources'][ $source ] = isset( $input['sources'][ $source ] ) && $input['sources'][ $source ] ? true : false;
			}
		}

		// Sanitize text fields..
		$text_fields = array( 'form_title', 'form_description', 'privacy_policy_url' );
		foreach ( $text_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		// Sanitize auto-delete days..
		if ( isset( $input['auto_delete_days'] ) ) {
			$sanitized['auto_delete_days'] = absint( $input['auto_delete_days'] );
		}

		return $sanitized;
	}

	/**
	 * Enqueue assets for settings page
	 *
	 * @since 3.0.1
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( empty( $hook ) ) {
			return;
		}

		if ( $hook !== 'shahi-legalflowsuite_page_slos-dsr-settings' ) {
			return;
		}

		wp_enqueue_style(
			'slos-dsr-settings',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/admin-dsr-settings.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);
	}
}
