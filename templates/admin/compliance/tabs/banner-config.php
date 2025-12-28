<?php
/**
 * Banner Config Tab - V3 Design
 *
 * Cookie consent banner configuration with live preview.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current banner settings
$banner_settings = array(
    'position'        => 'bottom',
    'layout'          => 'bar',
    'theme'           => 'dark',
    'primary_color'   => '#3b82f6',
    'text_color'      => '#f8fafc',
    'bg_color'        => '#0f172a',
    'show_toggle'     => true,
    'show_categories' => true,
    'animation'       => 'slide',
    'title'           => __( 'We value your privacy', 'shahi-legalops-suite' ),
    'message'         => __( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.', 'shahi-legalops-suite' ),
    'accept_text'     => __( 'Accept All', 'shahi-legalops-suite' ),
    'reject_text'     => __( 'Reject All', 'shahi-legalops-suite' ),
    'settings_text'   => __( 'Cookie Settings', 'shahi-legalops-suite' ),
);
?>

<style>
/* Banner Config specific styles */
.slos-config-layout {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 24px;
}

.slos-config-panel {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    padding: 24px;
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

.slos-config-section {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--slos-border);
}

.slos-config-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.slos-config-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--slos-text-primary);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.slos-config-title .dashicons {
    color: var(--slos-accent);
}

/* Form Controls */
.slos-form-group {
    margin-bottom: 16px;
}

.slos-form-group:last-child {
    margin-bottom: 0;
}

.slos-form-label {
    display: block;
    font-size: 12px;
    color: var(--slos-text-secondary);
    margin-bottom: 6px;
}

.slos-form-input {
    width: 100%;
    padding: 10px 12px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    color: var(--slos-text-primary);
    font-size: 13px;
}

.slos-form-input:focus {
    outline: none;
    border-color: var(--slos-accent);
}

.slos-form-textarea {
    width: 100%;
    min-height: 100px;
    padding: 12px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    color: var(--slos-text-primary);
    font-size: 13px;
    resize: vertical;
}

.slos-form-textarea:focus {
    outline: none;
    border-color: var(--slos-accent);
}

/* Radio Group */
.slos-radio-group {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.slos-radio-card {
    flex: 1;
    min-width: 100px;
    padding: 12px;
    background: var(--slos-bg-input);
    border: 2px solid var(--slos-border);
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: all 0.15s;
}

.slos-radio-card:hover {
    border-color: rgba(59, 130, 246, 0.5);
}

.slos-radio-card.active {
    border-color: var(--slos-accent);
    background: rgba(59, 130, 246, 0.05);
}

.slos-radio-card input {
    display: none;
}

.slos-radio-icon {
    width: 40px;
    height: 40px;
    margin: 0 auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 8px;
    color: var(--slos-accent);
}

.slos-radio-label {
    font-size: 12px;
    color: var(--slos-text-primary);
    font-weight: 500;
}

/* Color Picker */
.slos-color-picker {
    display: flex;
    gap: 12px;
    align-items: center;
}

.slos-color-input {
    width: 48px;
    height: 48px;
    border: 2px solid var(--slos-border);
    border-radius: 8px;
    cursor: pointer;
    padding: 0;
}

.slos-color-hex {
    flex: 1;
    padding: 10px 12px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    color: var(--slos-text-primary);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 13px;
}

/* Toggle Switch */
.slos-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--slos-border);
}

.slos-toggle-row:last-child {
    border-bottom: none;
}

.slos-toggle-label {
    font-size: 13px;
    color: var(--slos-text-primary);
}

.slos-toggle-desc {
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 2px;
}

.slos-toggle {
    position: relative;
    width: 44px;
    height: 24px;
    background: var(--slos-bg-input);
    border: 2px solid var(--slos-border);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.slos-toggle.active {
    background: var(--slos-accent);
    border-color: var(--slos-accent);
}

.slos-toggle-handle {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s;
}

.slos-toggle.active .slos-toggle-handle {
    transform: translateX(20px);
}

/* Preview Panel */
.slos-preview-panel {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    overflow: hidden;
}

.slos-preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--slos-border);
}

.slos-preview-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-preview-actions {
    display: flex;
    gap: 8px;
}

.slos-preview-btn {
    padding: 6px 12px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    font-size: 12px;
    color: var(--slos-text-secondary);
    cursor: pointer;
    transition: all 0.15s;
}

.slos-preview-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

.slos-preview-btn.active {
    background: var(--slos-accent);
    border-color: var(--slos-accent);
    color: white;
}

.slos-preview-window {
    background: #f5f5f5;
    min-height: 400px;
    position: relative;
    overflow: hidden;
}

/* Preview content mockup */
.slos-preview-content {
    padding: 40px;
    text-align: center;
    color: #666;
}

.slos-preview-lines {
    max-width: 600px;
    margin: 0 auto;
}

.slos-preview-line {
    height: 12px;
    background: #ddd;
    border-radius: 6px;
    margin-bottom: 12px;
}

.slos-preview-line.short {
    width: 60%;
}

.slos-preview-line.medium {
    width: 80%;
}

/* Banner Preview - Modern Compact Style */
.slos-banner-preview {
    position: absolute;
    left: 16px;
    right: auto;
    max-width: 360px;
    padding: 16px 20px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: slideInUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.slos-banner-preview.bottom {
    bottom: 16px;
}

.slos-banner-preview.top {
    top: 16px;
    bottom: auto;
    animation: slideInDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideInUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideInDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.slos-banner-text {
    flex: 1;
}

.slos-banner-title {
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 14px;
}

.slos-banner-message {
    font-size: 12px;
    opacity: 0.85;
    line-height: 1.5;
}

.slos-banner-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.slos-banner-btn {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease;
    flex: 1;
    min-width: 70px;
    text-align: center;
}

.slos-banner-btn.primary {
    background: #10b981;
    color: white;
}

.slos-banner-btn.primary:hover {
    background: #059669;
}

.slos-banner-btn.secondary {
    background: transparent;
    border: 1px solid rgba(128, 128, 128, 0.3);
    color: inherit;
    opacity: 0.8;
}

.slos-banner-btn.secondary:hover {
    border-color: #ef4444;
    color: #ef4444;
    opacity: 1;
}

.slos-banner-btn.link {
    background: transparent;
    color: #3b82f6;
    text-decoration: none;
    font-size: 11px;
    flex: 0;
    min-width: auto;
    padding: 4px 8px;
}

.slos-banner-btn.link:hover {
    text-decoration: underline;
}

/* Save Actions */
.slos-save-bar {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--slos-border);
}
</style>

<div class="slos-config-layout">
    <!-- Section Description -->
    <p class="slos-section-description" style="grid-column: 1 / -1;">
        <?php esc_html_e( 'Design your cookie consent banner with live preview. Customize colors, text, and layout to match your brand. GDPR requires clear Accept/Reject options and links to privacy policy. The banner appears to visitors on first visit and can be reopened via the preferences link.', 'shahi-legalops-suite' ); ?>
    </p>
    
    <!-- Configuration Panel -->
    <div class="slos-config-panel">
        <!-- Position & Layout -->
        <div class="slos-config-section">
            <div class="slos-config-title">
                <span class="dashicons dashicons-layout"></span>
                <?php esc_html_e( 'Position & Layout', 'shahi-legalops-suite' ); ?>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Choose where and how the banner appears. Bottom-left box is recommended for minimal intrusion while maintaining visibility.', 'shahi-legalops-suite' ); ?>
            </p>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Banner Position', 'shahi-legalops-suite' ); ?></label>
                <div class="slos-radio-group">
                    <label class="slos-radio-card <?php echo $banner_settings['position'] === 'bottom' ? 'active' : ''; ?>">
                        <input type="radio" name="banner_position" value="bottom" <?php checked( $banner_settings['position'], 'bottom' ); ?>>
                        <div class="slos-radio-icon">
                            <span class="dashicons dashicons-arrow-down-alt"></span>
                        </div>
                        <div class="slos-radio-label"><?php esc_html_e( 'Bottom', 'shahi-legalops-suite' ); ?></div>
                    </label>
                    <label class="slos-radio-card <?php echo $banner_settings['position'] === 'top' ? 'active' : ''; ?>">
                        <input type="radio" name="banner_position" value="top" <?php checked( $banner_settings['position'], 'top' ); ?>>
                        <div class="slos-radio-icon">
                            <span class="dashicons dashicons-arrow-up-alt"></span>
                        </div>
                        <div class="slos-radio-label"><?php esc_html_e( 'Top', 'shahi-legalops-suite' ); ?></div>
                    </label>
                    <label class="slos-radio-card <?php echo $banner_settings['position'] === 'center' ? 'active' : ''; ?>">
                        <input type="radio" name="banner_position" value="center" <?php checked( $banner_settings['position'], 'center' ); ?>>
                        <div class="slos-radio-icon">
                            <span class="dashicons dashicons-align-center"></span>
                        </div>
                        <div class="slos-radio-label"><?php esc_html_e( 'Center', 'shahi-legalops-suite' ); ?></div>
                    </label>
                </div>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Layout Style', 'shahi-legalops-suite' ); ?></label>
                <div class="slos-radio-group">
                    <label class="slos-radio-card <?php echo $banner_settings['layout'] === 'bar' ? 'active' : ''; ?>">
                        <input type="radio" name="banner_layout" value="bar" <?php checked( $banner_settings['layout'], 'bar' ); ?>>
                        <div class="slos-radio-icon">
                            <span class="dashicons dashicons-minus"></span>
                        </div>
                        <div class="slos-radio-label"><?php esc_html_e( 'Bar', 'shahi-legalops-suite' ); ?></div>
                    </label>
                    <label class="slos-radio-card <?php echo $banner_settings['layout'] === 'box' ? 'active' : ''; ?>">
                        <input type="radio" name="banner_layout" value="box" <?php checked( $banner_settings['layout'], 'box' ); ?>>
                        <div class="slos-radio-icon">
                            <span class="dashicons dashicons-admin-comments"></span>
                        </div>
                        <div class="slos-radio-label"><?php esc_html_e( 'Box', 'shahi-legalops-suite' ); ?></div>
                    </label>
                    <label class="slos-radio-card <?php echo $banner_settings['layout'] === 'popup' ? 'active' : ''; ?>">
                        <input type="radio" name="banner_layout" value="popup" <?php checked( $banner_settings['layout'], 'popup' ); ?>>
                        <div class="slos-radio-icon">
                            <span class="dashicons dashicons-editor-expand"></span>
                        </div>
                        <div class="slos-radio-label"><?php esc_html_e( 'Popup', 'shahi-legalops-suite' ); ?></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Colors & Theme -->
        <div class="slos-config-section">
            <div class="slos-config-title">
                <span class="dashicons dashicons-art"></span>
                <?php esc_html_e( 'Colors & Theme', 'shahi-legalops-suite' ); ?>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Match your brand colors. Ensure text color has sufficient contrast against background for accessibility (WCAG AA minimum 4.5:1 ratio).', 'shahi-legalops-suite' ); ?>
            </p>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Primary Color', 'shahi-legalops-suite' ); ?></label>
                <div class="slos-color-picker">
                    <input type="color" class="slos-color-input" id="primary-color" value="<?php echo esc_attr( $banner_settings['primary_color'] ); ?>">
                    <input type="text" class="slos-color-hex" id="primary-color-hex" value="<?php echo esc_attr( $banner_settings['primary_color'] ); ?>">
                </div>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Background Color', 'shahi-legalops-suite' ); ?></label>
                <div class="slos-color-picker">
                    <input type="color" class="slos-color-input" id="bg-color" value="<?php echo esc_attr( $banner_settings['bg_color'] ); ?>">
                    <input type="text" class="slos-color-hex" id="bg-color-hex" value="<?php echo esc_attr( $banner_settings['bg_color'] ); ?>">
                </div>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Text Color', 'shahi-legalops-suite' ); ?></label>
                <div class="slos-color-picker">
                    <input type="color" class="slos-color-input" id="text-color" value="<?php echo esc_attr( $banner_settings['text_color'] ); ?>">
                    <input type="text" class="slos-color-hex" id="text-color-hex" value="<?php echo esc_attr( $banner_settings['text_color'] ); ?>">
                </div>
            </div>
        </div>

        <!-- Banner Text -->
        <div class="slos-config-section">
            <div class="slos-config-title">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e( 'Banner Text', 'shahi-legalops-suite' ); ?>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Write clear, non-technical language. Title should convey purpose. Message explains what cookies do. Button text must be actionable (Accept All, Reject All, Customize).', 'shahi-legalops-suite' ); ?>
            </p>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Title', 'shahi-legalops-suite' ); ?></label>
                <input type="text" class="slos-form-input" id="banner-title" value="<?php echo esc_attr( $banner_settings['title'] ); ?>">
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Message', 'shahi-legalops-suite' ); ?></label>
                <textarea class="slos-form-textarea" id="banner-message"><?php echo esc_textarea( $banner_settings['message'] ); ?></textarea>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Accept Button Text', 'shahi-legalops-suite' ); ?></label>
                <input type="text" class="slos-form-input" id="accept-text" value="<?php echo esc_attr( $banner_settings['accept_text'] ); ?>">
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Reject Button Text', 'shahi-legalops-suite' ); ?></label>
                <input type="text" class="slos-form-input" id="reject-text" value="<?php echo esc_attr( $banner_settings['reject_text'] ); ?>">
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Settings Link Text', 'shahi-legalops-suite' ); ?></label>
                <input type="text" class="slos-form-input" id="settings-text" value="<?php echo esc_attr( $banner_settings['settings_text'] ); ?>">
            </div>
        </div>

        <!-- Behavior Options -->
        <div class="slos-config-section">
            <div class="slos-config-title">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e( 'Behavior', 'shahi-legalops-suite' ); ?>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Configure user interaction options. Show Cookie Toggle adds a persistent icon for users to change preferences. Show Categories enables granular control per cookie type.', 'shahi-legalops-suite' ); ?>
            </p>

            <div class="slos-toggle-row">
                <div>
                    <div class="slos-toggle-label"><?php esc_html_e( 'Show Cookie Toggle', 'shahi-legalops-suite' ); ?></div>
                    <div class="slos-toggle-desc"><?php esc_html_e( 'Allow users to reopen settings', 'shahi-legalops-suite' ); ?></div>
                </div>
                <div class="slos-toggle <?php echo $banner_settings['show_toggle'] ? 'active' : ''; ?>" data-setting="show_toggle">
                    <div class="slos-toggle-handle"></div>
                </div>
            </div>

            <div class="slos-toggle-row">
                <div>
                    <div class="slos-toggle-label"><?php esc_html_e( 'Show Categories', 'shahi-legalops-suite' ); ?></div>
                    <div class="slos-toggle-desc"><?php esc_html_e( 'Let users select individual categories', 'shahi-legalops-suite' ); ?></div>
                </div>
                <div class="slos-toggle <?php echo $banner_settings['show_categories'] ? 'active' : ''; ?>" data-setting="show_categories">
                    <div class="slos-toggle-handle"></div>
                </div>
            </div>
        </div>

        <!-- Save Actions -->
        <div class="slos-save-bar">
            <button class="slos-btn slos-btn-secondary" id="reset-defaults">
                <span class="dashicons dashicons-image-rotate"></span>
                <?php esc_html_e( 'Reset to Defaults', 'shahi-legalops-suite' ); ?>
            </button>
            <button class="slos-btn slos-btn-primary" id="save-banner">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Changes', 'shahi-legalops-suite' ); ?>
            </button>
        </div>
    </div>

    <!-- Preview Panel -->
    <div class="slos-preview-panel">
        <div class="slos-preview-header">
            <div class="slos-preview-title"><?php esc_html_e( 'Live Preview', 'shahi-legalops-suite' ); ?></div>
            <div class="slos-preview-actions">
                <button class="slos-preview-btn active" data-device="desktop">
                    <span class="dashicons dashicons-desktop"></span>
                </button>
                <button class="slos-preview-btn" data-device="tablet">
                    <span class="dashicons dashicons-tablet"></span>
                </button>
                <button class="slos-preview-btn" data-device="mobile">
                    <span class="dashicons dashicons-smartphone"></span>
                </button>
            </div>
        </div>

        <div class="slos-preview-window" id="preview-window">
            <!-- Content mockup -->
            <div class="slos-preview-content">
                <div class="slos-preview-lines">
                    <div class="slos-preview-line medium"></div>
                    <div class="slos-preview-line"></div>
                    <div class="slos-preview-line short"></div>
                    <br><br>
                    <div class="slos-preview-line"></div>
                    <div class="slos-preview-line medium"></div>
                    <div class="slos-preview-line"></div>
                    <div class="slos-preview-line short"></div>
                </div>
            </div>

            <!-- Banner Preview - Modern Compact Style -->
            <div class="slos-banner-preview bottom" id="banner-preview" style="background: <?php echo esc_attr( $banner_settings['bg_color'] ); ?>; color: <?php echo esc_attr( $banner_settings['text_color'] ); ?>;">
                <div class="slos-banner-text">
                    <div class="slos-banner-title" id="preview-title"><?php echo esc_html( $banner_settings['title'] ); ?></div>
                    <div class="slos-banner-message" id="preview-message"><?php echo esc_html( $banner_settings['message'] ); ?></div>
                </div>
                <div class="slos-banner-buttons">
                    <button class="slos-banner-btn primary" id="preview-accept"><?php echo esc_html( $banner_settings['accept_text'] ); ?></button>
                    <button class="slos-banner-btn secondary" id="preview-reject"><?php echo esc_html( $banner_settings['reject_text'] ); ?></button>
                    <button class="slos-banner-btn link" id="preview-settings"><?php echo esc_html( $banner_settings['settings_text'] ); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    let hasUnsavedChanges = false;
    
    // Track changes
    function markChanged() {
        hasUnsavedChanges = true;
        $('#save-banner').addClass('has-changes');
    }
    
    // Radio cards
    $('.slos-radio-card').on('click', function() {
        const group = $(this).closest('.slos-radio-group');
        group.find('.slos-radio-card').removeClass('active');
        $(this).addClass('active');
        group.find('input[type="radio"]').prop('checked', false);
        $(this).find('input[type="radio"]').prop('checked', true);
        updatePreview();
        markChanged();
    });

    // Toggle switches
    $('.slos-toggle').on('click', function() {
        $(this).toggleClass('active');
        markChanged();
    });

    // Color pickers
    $('.slos-color-input').on('input', function() {
        const hex = $(this).val();
        $(this).siblings('.slos-color-hex').val(hex);
        updatePreview();
        markChanged();
    });

    $('.slos-color-hex').on('input', function() {
        let hex = $(this).val();
        if (!/^#/.test(hex)) hex = '#' + hex;
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            $(this).siblings('.slos-color-input').val(hex);
            updatePreview();
        }
        markChanged();
    });

    // Text inputs
    $('#banner-title, #banner-message, #accept-text, #reject-text, #settings-text').on('input', function() {
        updatePreview();
        markChanged();
    });

    // Device preview
    $('.slos-preview-btn').on('click', function() {
        $('.slos-preview-btn').removeClass('active');
        $(this).addClass('active');
        const device = $(this).data('device');
        const window = $('#preview-window');
        
        if (device === 'mobile') {
            window.css({ 'max-width': '375px', 'margin': '0 auto' });
        } else if (device === 'tablet') {
            window.css({ 'max-width': '768px', 'margin': '0 auto' });
        } else {
            window.css({ 'max-width': 'none', 'margin': '0' });
        }
    });

    function updatePreview() {
        // Update position
        const position = $('input[name="banner_position"]:checked').val() || 'bottom';
        const banner = $('#banner-preview');
        banner.removeClass('top bottom center').addClass(position);

        // Update colors
        banner.css({
            'background': $('#bg-color').val(),
            'color': $('#text-color').val()
        });
        $('#preview-accept').css('background', $('#primary-color').val());

        // Update text
        $('#preview-title').text($('#banner-title').val() || '<?php echo esc_js( __( 'Cookie Consent', 'shahi-legalops-suite' ) ); ?>');
        $('#preview-message').text($('#banner-message').val() || '<?php echo esc_js( __( 'We use cookies to enhance your experience.', 'shahi-legalops-suite' ) ); ?>');
        $('#preview-accept').text($('#accept-text').val() || '<?php echo esc_js( __( 'Accept All', 'shahi-legalops-suite' ) ); ?>');
        $('#preview-reject').text($('#reject-text').val() || '<?php echo esc_js( __( 'Reject All', 'shahi-legalops-suite' ) ); ?>');
        $('#preview-settings').text($('#settings-text').val() || '<?php echo esc_js( __( 'Customize', 'shahi-legalops-suite' ) ); ?>');
    }
    
    function gatherSettings() {
        return {
            position: $('input[name="banner_position"]:checked').val() || 'bottom',
            layout: $('input[name="banner_layout"]:checked').val() || 'bar',
            bg_color: $('#bg-color').val(),
            text_color: $('#text-color').val(),
            primary_color: $('#primary-color').val(),
            title: $('#banner-title').val(),
            message: $('#banner-message').val(),
            accept_text: $('#accept-text').val(),
            reject_text: $('#reject-text').val(),
            settings_text: $('#settings-text').val(),
            show_reject: $('.slos-toggle[data-option="show_reject"]').hasClass('active'),
            show_settings: $('.slos-toggle[data-option="show_settings"]').hasClass('active'),
            auto_hide: $('.slos-toggle[data-option="auto_hide"]').hasClass('active'),
            blur_background: $('.slos-toggle[data-option="blur_background"]').hasClass('active')
        };
    }

    // Save settings
    $('#save-banner').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; margin-right: 6px;"></span> <?php echo esc_js( __( 'Saving...', 'shahi-legalops-suite' ) ); ?>');
        
        const settings = gatherSettings();
        
        $.ajax({
            url: API_BASE + '/settings/banner',
            method: 'POST',
            headers: { 
                'X-WP-Nonce': NONCE,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(settings),
            success: function(response) {
                $btn.prop('disabled', false).removeClass('has-changes').html('<span class="dashicons dashicons-yes" style="margin-right: 6px;"></span> <?php echo esc_js( __( 'Saved!', 'shahi-legalops-suite' ) ); ?>');
                hasUnsavedChanges = false;
                
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text(originalText);
                const errMsg = xhr.responseJSON?.message || '<?php echo esc_js( __( 'Failed to save settings. Please try again.', 'shahi-legalops-suite' ) ); ?>';
                alert(errMsg);
            }
        });
    });

    // Reset defaults
    $('#reset-defaults').on('click', function() {
        if (confirm('<?php echo esc_js( __( 'Are you sure you want to reset to default settings? This cannot be undone.', 'shahi-legalops-suite' ) ); ?>')) {
            const $btn = $(this);
            $btn.prop('disabled', true);
            
            $.ajax({
                url: API_BASE + '/settings/banner/reset',
                method: 'POST',
                headers: { 'X-WP-Nonce': NONCE },
                success: function() {
                    location.reload();
                },
                error: function() {
                    $btn.prop('disabled', false);
                    // Just reload if endpoint doesn't exist
                    location.reload();
                }
            });
        }
    });
    
    // Warn on unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return '<?php echo esc_js( __( 'You have unsaved changes. Are you sure you want to leave?', 'shahi-legalops-suite' ) ); ?>';
        }
    });
    
    // Initialize preview
    updatePreview();
});
</script>
