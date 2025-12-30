<?php
/**
 * Cookie Scanner Tab - V3 Design
 *
 * Cookie detection, categorization, and management.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get scanner service instance
$scanner_service = new \ShahiLegalFlowSuite\Services\Cookie_Scanner_Service();

// Get detected cookies from database
$detected_cookies = get_option( 'slos_detected_cookies', array() );

// Get last scan time
$last_scan = get_option( 'slos_cookie_scan_time', null );

// Get scan metadata
$scan_meta = $scanner_service->get_scan_metadata();

// Calculate statistics from actual data
$cookie_stats = array(
	'total'         => count( $detected_cookies ),
	'categorized'   => 0,
	'uncategorized' => 0,
	'necessary'     => 0,
	'analytics'     => 0,
	'marketing'     => 0,
	'functional'    => 0,
);

foreach ( $detected_cookies as $cookie ) {
	$category = $cookie['category'] ?? 'unknown';
	$status   = $cookie['status'] ?? 'uncategorized';
	
	if ( $status === 'categorized' || $category !== 'unknown' ) {
		$cookie_stats['categorized']++;
	} else {
		$cookie_stats['uncategorized']++;
	}
	
	if ( isset( $cookie_stats[ $category ] ) ) {
		$cookie_stats[ $category ]++;
	}
}
?>

<style>
/* Cookie Scanner specific styles */
.slos-scanner-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

/* Last Scan Summary Card */
.slos-scan-summary-card {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 20px;
    align-items: center;
}

.slos-scan-summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--slos-primary), var(--slos-accent));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.slos-scan-summary-content {
    display: grid;
    gap: 4px;
}

.slos-scan-summary-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--slos-text-primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.slos-scan-summary-stats {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.slos-scan-summary-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--slos-text-secondary);
}

.slos-scan-summary-stat .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: var(--slos-primary);
}

.slos-scan-summary-stat strong {
    color: var(--slos-text-primary);
    font-weight: 600;
}

.slos-scan-summary-actions {
    display: flex;
    gap: 12px;
}

.slos-scan-summary-card.no-scan {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%);
    border-color: rgba(245, 158, 11, 0.2);
}

.slos-scan-summary-card.no-scan .slos-scan-summary-icon {
    background: linear-gradient(135deg, var(--slos-warning), var(--slos-error));
}

.slos-scanner-stats {
    display: flex;
    gap: 16px;
}

.slos-scanner-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    font-size: 13px;
    color: var(--slos-text-secondary);
}

.slos-scanner-stat .count {
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-scanner-stat.warning {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.2);
}

.slos-scanner-stat.warning .count {
    color: var(--slos-warning);
}

/* Category Cards */
.slos-category-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.slos-category-card {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
    cursor: pointer;
}

.slos-category-card:hover {
    border-color: var(--slos-accent);
    transform: translateY(-2px);
}

.slos-category-card.active {
    border-color: var(--slos-accent);
    background: rgba(59, 130, 246, 0.05);
}

.slos-category-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.slos-category-icon.necessary {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-category-icon.analytics {
    background: rgba(59, 130, 246, 0.15);
    color: var(--slos-accent);
}

.slos-category-icon.marketing {
    background: rgba(147, 197, 253, 0.15);
    color: var(--slos-accent-light, #93c5fd);
}

.slos-category-icon.preferences {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-category-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--slos-text-primary);
    margin-bottom: 4px;
}

.slos-category-count {
    font-size: 13px;
    color: var(--slos-text-muted);
}

/* Scanner Actions Bar */
.slos-scanner-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.slos-scan-progress {
    display: none;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 8px;
    flex: 1;
}

.slos-scan-progress.active {
    display: flex;
}

.slos-scan-progress .spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.slos-progress-bar {
    flex: 1;
    height: 6px;
    background: var(--slos-bg-input);
    border-radius: 3px;
    overflow: hidden;
}

.slos-progress-bar-fill {
    height: 100%;
    background: var(--slos-accent);
    border-radius: 3px;
    width: 0%;
    transition: width 0.3s;
}

/* Cookie Table */
.slos-cookie-table {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    overflow: hidden;
}

.slos-cookie-header {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) 120px 150px 100px 100px 100px 120px;
    gap: 16px;
    padding: 16px 20px;
    background: var(--slos-bg-input);
    border-bottom: 1px solid var(--slos-border);
    font-size: 11px;
    text-transform: uppercase;
    color: var(--slos-text-muted);
    font-weight: 600;
}

.slos-cookie-row {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) 120px 150px 100px 100px 100px 120px;
    gap: 16px;
    padding: 16px 20px;
    align-items: center;
    border-bottom: 1px solid var(--slos-border);
    transition: background 0.15s;
}

.slos-cookie-row:hover {
    background: rgba(59, 130, 246, 0.03);
}

.slos-cookie-row:last-child {
    border-bottom: none;
}

.slos-cookie-name {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 13px;
    color: var(--slos-text-primary);
    font-weight: 500;
}

.slos-cookie-domain {
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 4px;
    word-break: break-all;
}

.slos-cookie-category {
    font-size: 12px;
}

.slos-cookie-category select {
    width: 100%;
    padding: 6px 8px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    color: var(--slos-text-primary);
    font-size: 12px;
    cursor: pointer;
}

.slos-cookie-provider {
    font-size: 13px;
    color: var(--slos-text-secondary);
}

.slos-cookie-duration,
.slos-cookie-type {
    font-size: 12px;
    color: var(--slos-text-muted);
}

/* Status badges */
.slos-cookie-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.slos-cookie-status.categorized {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-cookie-status.uncategorized {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-cookie-actions {
    display: flex;
    gap: 8px;
}

.slos-cookie-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    color: var(--slos-text-muted);
    cursor: pointer;
    transition: all 0.15s;
}

.slos-cookie-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

.slos-cookie-btn.delete:hover {
    border-color: var(--slos-error);
    color: var(--slos-error);
}

/* Info Modal Styles */
.slos-cookie-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    align-items: center;
    justify-content: center;
}

.slos-cookie-modal.active {
    display: flex;
}

.slos-modal-content {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    padding: 24px;
}

.slos-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.slos-modal-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-modal-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    color: var(--slos-text-muted);
    cursor: pointer;
}

.slos-modal-close:hover {
    border-color: var(--slos-error);
    color: var(--slos-error);
}

.slos-modal-body {
    display: grid;
    gap: 16px;
}

.slos-modal-field {
    padding: 12px;
    background: var(--slos-bg-input);
    border-radius: 8px;
}

.slos-modal-label {
    font-size: 11px;
    color: var(--slos-text-muted);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.slos-modal-value {
    font-size: 14px;
    color: var(--slos-text-primary);
    word-break: break-all;
}

.slos-modal-value.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}
</style>

<div class="slos-scanner-container">
    <!-- Section Description -->
    <p class="slos-section-description">
        <?php esc_html_e( 'Automatically detect and categorize cookies used on your website. GDPR and ePrivacy Directive require disclosure of all cookies in your consent banner. Uncategorized cookies need immediate attention—assign them to Necessary, Analytics, Marketing, or Preferences categories.', 'shahi-legalflowsuite' ); ?>
    </p>

    <!-- Last Scan Summary Card -->
    <?php if ( ! empty( $scan_meta ) && isset( $scan_meta['completed_at'] ) ) : ?>
        <?php
        $duration_text  = isset( $scan_meta['duration'] ) ? sprintf( __( '%d seconds', 'shahi-legalflowsuite' ), $scan_meta['duration'] ) : __( 'N/A', 'shahi-legalflowsuite' );
        $pages_scanned  = $scan_meta['pages_scanned'] ?? 1;
        $cookies_found  = $scan_meta['cookies_found'] ?? count( $detected_cookies );
        $scan_type_text = ucfirst( $scan_meta['scan_type'] ?? 'manual' );
        $coverage_text  = ucfirst( $scan_meta['coverage_level'] ?? 'basic' );
        $scan_date      = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $scan_meta['completed_at'] ) );
        ?>
        <div class="slos-scan-summary-card">
            <div class="slos-scan-summary-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="slos-scan-summary-content">
                <div class="slos-scan-summary-title">
                    <?php esc_html_e( 'Last Scan Summary', 'shahi-legalflowsuite' ); ?>
                </div>
                <div class="slos-scan-summary-stats">
                    <span class="slos-scan-summary-stat">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <strong><?php echo esc_html( $scan_date ); ?></strong>
                    </span>
                    <span class="slos-scan-summary-stat">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php echo esc_html( sprintf( __( '%d cookies found', 'shahi-legalflowsuite' ), $cookies_found ) ); ?>
                    </span>
                    <span class="slos-scan-summary-stat">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php echo esc_html( sprintf( _n( '%d page scanned', '%d pages scanned', $pages_scanned, 'shahi-legalflowsuite' ), $pages_scanned ) ); ?>
                    </span>
                    <span class="slos-scan-summary-stat">
                        <span class="dashicons dashicons-clock"></span>
                        <?php echo esc_html( $duration_text ); ?>
                    </span>
                    <span class="slos-scan-summary-stat">
                        <span class="dashicons dashicons-networking"></span>
                        <?php echo esc_html( sprintf( __( 'Type: %s', 'shahi-legalflowsuite' ), $scan_type_text ) ); ?>
                    </span>
                    <span class="slos-scan-summary-stat">
                        <span class="dashicons dashicons-chart-area"></span>
                        <?php echo esc_html( sprintf( __( 'Coverage: %s', 'shahi-legalflowsuite' ), $coverage_text ) ); ?>
                    </span>
                </div>
            </div>
            <div class="slos-scan-summary-actions">
                <button class="slos-btn slos-btn-primary" id="rescan-now">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e( 'Rescan Now', 'shahi-legalflowsuite' ); ?>
                </button>
            </div>
        </div>
    <?php else : ?>
        <div class="slos-scan-summary-card no-scan">
            <div class="slos-scan-summary-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="slos-scan-summary-content">
                <div class="slos-scan-summary-title">
                    <?php esc_html_e( 'No Scan Completed Yet', 'shahi-legalflowsuite' ); ?>
                </div>
                <div class="slos-scan-summary-stats">
                    <span class="slos-scan-summary-stat">
                        <?php esc_html_e( 'Run your first cookie scan to detect cookies used on your website. This helps ensure GDPR compliance by identifying all tracking technologies.', 'shahi-legalflowsuite' ); ?>
                    </span>
                </div>
            </div>
            <div class="slos-scan-summary-actions">
                <button class="slos-btn slos-btn-primary" id="rescan-now">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e( 'Run First Scan', 'shahi-legalflowsuite' ); ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="slos-scanner-header">
        <div class="slos-scanner-stats">
            <div class="slos-scanner-stat">
                <span class="dashicons dashicons-admin-settings"></span>
                <span>Total Cookies: <span class="count"><?php echo count( $detected_cookies ); ?></span></span>
            </div>
            <div class="slos-scanner-stat warning">
                <span class="dashicons dashicons-warning"></span>
                <span>Uncategorized: <span class="count">1</span></span>
            </div>
            <div class="slos-scanner-stat">
                <span class="dashicons dashicons-update"></span>
                <span>Last Scan: <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' g:i A' ) ); ?></span>
            </div>
        </div>
        <button class="slos-btn slos-btn-primary" id="run-scan">
            <span class="dashicons dashicons-search"></span>
            <?php esc_html_e( 'Scan Website', 'shahi-legalflowsuite' ); ?>
        </button>
    </div>

    <!-- Category Cards -->
    <div class="slos-category-grid">
        <div class="slos-category-card active" data-category="all">
            <div class="slos-category-icon necessary">
                <span class="dashicons dashicons-screenoptions"></span>
            </div>
            <div class="slos-category-name"><?php esc_html_e( 'All Cookies', 'shahi-legalflowsuite' ); ?></div>
            <div class="slos-category-count"><?php echo count( $detected_cookies ); ?> <?php esc_html_e( 'cookies', 'shahi-legalflowsuite' ); ?></div>
        </div>
        <div class="slos-category-card" data-category="necessary">
            <div class="slos-category-icon necessary">
                <span class="dashicons dashicons-lock"></span>
            </div>
            <div class="slos-category-name"><?php esc_html_e( 'Necessary', 'shahi-legalflowsuite' ); ?></div>
            <div class="slos-category-count">1 <?php esc_html_e( 'cookie', 'shahi-legalflowsuite' ); ?></div>
        </div>
        <div class="slos-category-card" data-category="analytics">
            <div class="slos-category-icon analytics">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="slos-category-name"><?php esc_html_e( 'Analytics', 'shahi-legalflowsuite' ); ?></div>
            <div class="slos-category-count">2 <?php esc_html_e( 'cookies', 'shahi-legalflowsuite' ); ?></div>
        </div>
        <div class="slos-category-card" data-category="marketing">
            <div class="slos-category-icon marketing">
                <span class="dashicons dashicons-megaphone"></span>
            </div>
            <div class="slos-category-name"><?php esc_html_e( 'Marketing', 'shahi-legalflowsuite' ); ?></div>
            <div class="slos-category-count">1 <?php esc_html_e( 'cookie', 'shahi-legalflowsuite' ); ?></div>
        </div>
    </div>

    <!-- Scan Progress -->
    <div class="slos-scan-progress" id="scan-progress">
        <span class="dashicons dashicons-update spinner"></span>
        <span><?php esc_html_e( 'Scanning your website for cookies...', 'shahi-legalflowsuite' ); ?></span>
        <div class="slos-progress-bar">
            <div class="slos-progress-bar-fill" style="width: 0%;"></div>
        </div>
    </div>

    <!-- Scanner Actions -->
    <div class="slos-scanner-actions">
        <input type="search" 
               class="slos-search-input" 
               placeholder="<?php esc_attr_e( 'Search cookies by name, domain, or provider...', 'shahi-legalflowsuite' ); ?>"
               id="cookie-search"
               style="flex: 1;">
        <select class="slos-filter-select" id="status-filter">
            <option value=""><?php esc_html_e( 'All Statuses', 'shahi-legalflowsuite' ); ?></option>
            <option value="categorized"><?php esc_html_e( 'Categorized', 'shahi-legalflowsuite' ); ?></option>
            <option value="uncategorized"><?php esc_html_e( 'Uncategorized', 'shahi-legalflowsuite' ); ?></option>
        </select>
        <button class="slos-btn slos-btn-secondary" id="export-cookies">
            <span class="dashicons dashicons-download"></span>
            <?php esc_html_e( 'Export List', 'shahi-legalflowsuite' ); ?>
        </button>
    </div>

    <!-- Cookie Table -->
    <div class="slos-cookie-table">
        <div class="slos-cookie-header">
            <span><?php esc_html_e( 'Cookie Name', 'shahi-legalflowsuite' ); ?></span>
            <span><?php esc_html_e( 'Category', 'shahi-legalflowsuite' ); ?></span>
            <span><?php esc_html_e( 'Provider', 'shahi-legalflowsuite' ); ?></span>
            <span><?php esc_html_e( 'Duration', 'shahi-legalflowsuite' ); ?></span>
            <span><?php esc_html_e( 'Type', 'shahi-legalflowsuite' ); ?></span>
            <span><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></span>
            <span><?php esc_html_e( 'Actions', 'shahi-legalflowsuite' ); ?></span>
        </div>
        <div class="slos-cookie-list" id="cookie-list">
            <?php foreach ( $detected_cookies as $cookie ) : ?>
            <div class="slos-cookie-row" data-category="<?php echo esc_attr( $cookie['category'] ); ?>">
                <div>
                    <div class="slos-cookie-name"><?php echo esc_html( $cookie['name'] ); ?></div>
                    <div class="slos-cookie-domain"><?php echo esc_html( $cookie['domain'] ); ?></div>
                </div>
                <div class="slos-cookie-category">
                    <select class="cookie-category-select" data-cookie="<?php echo esc_attr( $cookie['name'] ); ?>">
                        <option value="necessary" <?php selected( $cookie['category'], 'necessary' ); ?>><?php esc_html_e( 'Necessary', 'shahi-legalflowsuite' ); ?></option>
                        <option value="analytics" <?php selected( $cookie['category'], 'analytics' ); ?>><?php esc_html_e( 'Analytics', 'shahi-legalflowsuite' ); ?></option>
                        <option value="marketing" <?php selected( $cookie['category'], 'marketing' ); ?>><?php esc_html_e( 'Marketing', 'shahi-legalflowsuite' ); ?></option>
                        <option value="preferences" <?php selected( $cookie['category'], 'preferences' ); ?>><?php esc_html_e( 'Preferences', 'shahi-legalflowsuite' ); ?></option>
                        <option value="unknown" <?php selected( $cookie['category'], 'unknown' ); ?>><?php esc_html_e( 'Unknown', 'shahi-legalflowsuite' ); ?></option>
                    </select>
                </div>
                <div class="slos-cookie-provider"><?php echo esc_html( $cookie['provider'] ?? __( 'Unknown', 'shahi-legalflowsuite' ) ); ?></div>
                <div class="slos-cookie-duration"><?php echo esc_html( $cookie['duration'] ?? __( 'Unknown', 'shahi-legalflowsuite' ) ); ?></div>
                <div class="slos-cookie-type"><?php echo esc_html( $cookie['type'] ?? __( 'Unknown', 'shahi-legalflowsuite' ) ); ?></div>
                <div>
                    <span class="slos-cookie-status <?php echo esc_attr( $cookie['status'] ); ?>">
                        <?php echo esc_html( ucfirst( $cookie['status'] ) ); ?>
                    </span>
                </div>
                <div class="slos-cookie-actions">
                    <button class="slos-cookie-btn info-btn" title="<?php esc_attr_e( 'View Details', 'shahi-legalflowsuite' ); ?>">
                        <span class="dashicons dashicons-info"></span>
                    </button>
                    <button class="slos-cookie-btn delete" title="<?php esc_attr_e( 'Remove', 'shahi-legalflowsuite' ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Cookie Details Modal -->
<div class="slos-cookie-modal" id="cookie-modal">
    <div class="slos-modal-content">
        <div class="slos-modal-header">
            <h3 class="slos-modal-title"><?php esc_html_e( 'Cookie Details', 'shahi-legalflowsuite' ); ?></h3>
            <button class="slos-modal-close" id="close-modal">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="slos-modal-body">
            <div class="slos-modal-field">
                <div class="slos-modal-label"><?php esc_html_e( 'Cookie Name', 'shahi-legalflowsuite' ); ?></div>
                <div class="slos-modal-value mono" id="modal-name"></div>
            </div>
            <div class="slos-modal-field">
                <div class="slos-modal-label"><?php esc_html_e( 'Domain', 'shahi-legalflowsuite' ); ?></div>
                <div class="slos-modal-value" id="modal-domain"></div>
            </div>
            <div class="slos-modal-field">
                <div class="slos-modal-label"><?php esc_html_e( 'Provider', 'shahi-legalflowsuite' ); ?></div>
                <div class="slos-modal-value" id="modal-provider"></div>
            </div>
            <div class="slos-modal-field">
                <div class="slos-modal-label"><?php esc_html_e( 'First Detected', 'shahi-legalflowsuite' ); ?></div>
                <div class="slos-modal-value" id="modal-first-seen"></div>
            </div>
            <div class="slos-modal-field">
                <div class="slos-modal-label"><?php esc_html_e( 'Description', 'shahi-legalflowsuite' ); ?></div>
                <div class="slos-modal-value" id="modal-description">
                    <?php esc_html_e( 'This cookie is used to track user interactions and behavior.', 'shahi-legalflowsuite' ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    
    // Category filter
    $('.slos-category-card').on('click', function() {
        $('.slos-category-card').removeClass('active');
        $(this).addClass('active');
        
        const category = $(this).data('category');
        if (category === 'all') {
            $('.slos-cookie-row').show();
        } else {
            $('.slos-cookie-row').hide();
            $('.slos-cookie-row[data-category="' + category + '"]').show();
        }
        updateVisibleCount();
    });

    // Search filter
    let searchTimeout;
    $('#cookie-search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().toLowerCase();
        searchTimeout = setTimeout(function() {
            $('.slos-cookie-row').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
            updateVisibleCount();
        }, 300);
    });

    // Status filter
    $('#status-filter').on('change', function() {
        const status = $(this).val();
        if (!status) {
            $('.slos-cookie-row').show();
        } else {
            $('.slos-cookie-row').each(function() {
                const rowStatus = $(this).find('.slos-cookie-status').hasClass(status);
                $(this).toggle(rowStatus);
            });
        }
        updateVisibleCount();
    });
    
    function updateVisibleCount() {
        const visible = $('.slos-cookie-row:visible').length;
        const total = $('.slos-cookie-row').length;
        $('#visible-count').text(visible + ' <?php echo esc_js( __( 'of', 'shahi-legalflowsuite' ) ); ?> ' + total);
    }

    // Run scan (works for both #run-scan and #rescan-now buttons)
    $('#run-scan, #rescan-now').on('click', function() {
        const $btn = $(this);
        const $progress = $('#scan-progress');
        
        $btn.prop('disabled', true);
        $progress.addClass('active');
        
        // Enhanced progress tracking with stages
        let progress = 0;
        let stage = 0;
        const stages = [
            { end: 20, label: '<?php echo esc_js( __( 'Initializing scan...', 'shahi-legalflowsuite' ) ); ?>' },
            { end: 40, label: '<?php echo esc_js( __( 'Requesting website...', 'shahi-legalflowsuite' ) ); ?>' },
            { end: 60, label: '<?php echo esc_js( __( 'Detecting cookies...', 'shahi-legalflowsuite' ) ); ?>' },
            { end: 80, label: '<?php echo esc_js( __( 'Classifying cookies...', 'shahi-legalflowsuite' ) ); ?>' },
            { end: 95, label: '<?php echo esc_js( __( 'Finalizing...', 'shahi-legalflowsuite' ) ); ?>' }
        ];
        
        const progressInterval = setInterval(function() {
            const currentStage = stages[stage];
            progress += Math.random() * 3;
            
            if (progress >= currentStage.end && stage < stages.length - 1) {
                stage++;
                $progress.find('span').text(stages[stage].label);
            }
            
            if (progress > 95) progress = 95;
            $progress.find('.slos-progress-bar-fill').css('width', progress + '%');
        }, 150);
        
        // Update initial stage message
        $progress.find('span').text(stages[0].label);
        
        $.ajax({
            url: API_BASE + '/cookies/scan',
            method: 'POST',
            headers: { 'X-WP-Nonce': NONCE },
            data: { site_url: window.location.origin },
            timeout: 60000,
            success: function(response) {
                clearInterval(progressInterval);
                $progress.find('.slos-progress-bar-fill').css('width', '100%');
                $progress.find('span').text('<?php echo esc_js( __( 'Scan complete!', 'shahi-legalflowsuite' ) ); ?>');
                
                setTimeout(function() {
                    $progress.removeClass('active');
                    $btn.prop('disabled', false);
                    
                    const cookies = response.data || response || [];
                    if (cookies.length > 0) {
                        // Show success message with cookie count
                        const message = '<?php echo esc_js( __( 'Scan complete!', 'shahi-legalflowsuite' ) ); ?> ' + 
                                      cookies.length + ' <?php echo esc_js( __( 'cookies found.', 'shahi-legalflowsuite' ) ); ?>';
                        alert(message);
                        location.reload();
                    } else {
                        alert('<?php echo esc_js( __( 'Scan complete. No new cookies detected.', 'shahi-legalflowsuite' ) ); ?>');
                    }
                }, 500);
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                $progress.removeClass('active');
                $btn.prop('disabled', false);
                
                const errMsg = xhr.responseJSON?.message || '<?php echo esc_js( __( 'Scan failed. Please try again.', 'shahi-legalflowsuite' ) ); ?>';
                alert(errMsg);
            }
        });
    });

    // Cookie info modal
    $(document).on('click', '.info-btn', function() {
        const row = $(this).closest('.slos-cookie-row');
        const cookieId = row.data('id');
        
        $('#modal-name').text(row.find('.slos-cookie-name').text());
        $('#modal-domain').text(row.find('.slos-cookie-domain').text());
        $('#modal-provider').text(row.find('.slos-cookie-provider').text());
        
        // Load additional details from API if needed
        if (cookieId) {
            $.ajax({
                url: API_BASE + '/cookies/inventory/' + cookieId,
                method: 'GET',
                headers: { 'X-WP-Nonce': NONCE },
                success: function(response) {
                    const data = response.data || response;
                    if (data.description) {
                        $('#modal-description').text(data.description);
                    }
                    if (data.duration) {
                        $('#modal-duration').text(data.duration);
                    }
                }
            });
        }
        
        $('#cookie-modal').addClass('active');
    });

    // Close modal
    $('#close-modal, #cookie-modal').on('click', function(e) {
        if (e.target === this) {
            $('#cookie-modal').removeClass('active');
        }
    });
    
    // ESC key to close modal
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#cookie-modal').hasClass('active')) {
            $('#cookie-modal').removeClass('active');
        }
    });

    // Category change - save via REST API
    $(document).on('change', '.cookie-category-select', function() {
        const $select = $(this);
        const cookieId = $select.data('cookie') || $select.closest('.slos-cookie-row').data('id');
        const category = $select.val();
        
        $select.prop('disabled', true);
        
        $.ajax({
            url: API_BASE + '/cookies/inventory/' + cookieId,
            method: 'PUT',
            headers: { 
                'X-WP-Nonce': NONCE,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ category: category }),
            success: function() {
                $select.prop('disabled', false);
                
                // Update row styling
                const $row = $select.closest('.slos-cookie-row');
                $row.attr('data-category', category);
                
                // Flash success
                $select.css('border-color', 'var(--slos-success)');
                setTimeout(function() {
                    $select.css('border-color', '');
                }, 1500);
                
                // Update category counts
                updateCategoryCounts();
            },
            error: function() {
                $select.prop('disabled', false);
                alert('<?php echo esc_js( __( 'Failed to update cookie category. Please try again.', 'shahi-legalflowsuite' ) ); ?>');
            }
        });
    });
    
    // Delete cookie
    $(document).on('click', '.delete-btn', function() {
        const $btn = $(this);
        const $row = $btn.closest('.slos-cookie-row');
        const cookieId = $row.data('id');
        const cookieName = $row.find('.slos-cookie-name').text();
        
        if (!confirm('<?php echo esc_js( __( 'Are you sure you want to delete this cookie?', 'shahi-legalflowsuite' ) ); ?>\n\n' + cookieName)) {
            return;
        }
        
        $btn.prop('disabled', true);
        $row.css('opacity', '0.5');
        
        $.ajax({
            url: API_BASE + '/cookies/inventory/' + cookieId,
            method: 'DELETE',
            headers: { 'X-WP-Nonce': NONCE },
            success: function() {
                $row.slideUp(300, function() {
                    $row.remove();
                    updateCategoryCounts();
                    updateVisibleCount();
                });
            },
            error: function() {
                $btn.prop('disabled', false);
                $row.css('opacity', '1');
                alert('<?php echo esc_js( __( 'Failed to delete cookie. Please try again.', 'shahi-legalflowsuite' ) ); ?>');
            }
        });
    });
    
    function updateCategoryCounts() {
        const categories = ['necessary', 'functional', 'analytics', 'marketing'];
        let total = 0;
        
        categories.forEach(function(cat) {
            const count = $('.slos-cookie-row[data-category="' + cat + '"]').length;
            total += count;
            const $card = $('.slos-category-card[data-category="' + cat + '"]');
            $card.find('.slos-category-count').text(count);
        });
        
        $('.slos-category-card[data-category="all"] .slos-category-count').text(total);
    }
    
    // Export cookies
    $('#export-cookies').on('click', function() {
        window.location.href = API_BASE + '/cookies/export?format=csv&_wpnonce=' + NONCE;
    });
});
</script>
