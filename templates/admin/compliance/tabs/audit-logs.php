<?php
/**
 * Audit Logs Tab - V3 Design
 *
 * Immutable audit trail with forensic search capabilities.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
/* Audit Logs specific styles */
.slos-audit-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.slos-audit-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.slos-audit-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-radius: 8px;
    font-size: 13px;
    color: var(--slos-success);
}

.slos-audit-badge .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.slos-audit-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.slos-date-range {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    padding: 4px 12px;
}

.slos-date-range input[type="date"] {
    background: transparent;
    border: none;
    color: var(--slos-text-primary);
    font-size: 13px;
    padding: 6px 0;
}

.slos-date-range input[type="date"]:focus {
    outline: none;
}

.slos-date-range span {
    color: var(--slos-text-muted);
    font-size: 13px;
}

/* Log Entry Styles */
.slos-log-card {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    overflow: hidden;
}

.slos-log-list {
    max-height: 600px;
    overflow-y: auto;
}

.slos-log-entry {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid var(--slos-border);
    transition: background 0.15s;
}

.slos-log-entry:hover {
    background: rgba(59, 130, 246, 0.03);
}

.slos-log-entry:last-child {
    border-bottom: none;
}

.slos-log-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.slos-log-icon.grant {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-log-icon.withdraw {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-log-icon.update {
    background: rgba(59, 130, 246, 0.15);
    color: var(--slos-accent);
}

.slos-log-icon.export {
    background: rgba(147, 197, 253, 0.15);
    color: var(--slos-accent-light, #93c5fd);
}

.slos-log-icon.import {
    background: rgba(6, 182, 212, 0.15);
    color: var(--slos-info);
}

.slos-log-content {
    flex: 1;
    min-width: 0;
}

.slos-log-title {
    font-size: 14px;
    color: var(--slos-text-primary);
    font-weight: 500;
    margin-bottom: 4px;
}

.slos-log-title strong {
    color: var(--slos-accent);
}

.slos-log-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 8px;
}

.slos-log-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.slos-log-meta-item .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.slos-log-time {
    text-align: right;
    flex-shrink: 0;
}

.slos-log-time .time {
    font-size: 13px;
    color: var(--slos-text-secondary);
}

.slos-log-time .date {
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 2px;
}

/* Action Tags */
.slos-action-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.slos-action-tag.grant {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-action-tag.withdraw {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-action-tag.update {
    background: rgba(59, 130, 246, 0.15);
    color: var(--slos-accent);
}

.slos-action-tag.export {
    background: rgba(147, 197, 253, 0.15);
    color: var(--slos-accent-light, #93c5fd);
}

.slos-action-tag.import {
    background: rgba(6, 182, 212, 0.15);
    color: var(--slos-info);
}

/* Expand Button */
.slos-log-expand {
    background: transparent;
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    padding: 6px;
    cursor: pointer;
    color: var(--slos-text-muted);
    transition: all 0.15s;
}

.slos-log-expand:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

/* Log Details Panel */
.slos-log-details {
    display: none;
    background: var(--slos-bg-input);
    padding: 16px 24px;
    border-top: 1px solid var(--slos-border);
    margin: -1px -24px 0;
    margin-left: 56px;
}

.slos-log-entry.expanded .slos-log-details {
    display: block;
}

.slos-details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.slos-detail-item {
    padding: 12px;
    background: var(--slos-bg-card);
    border-radius: 8px;
}

.slos-detail-label {
    font-size: 11px;
    color: var(--slos-text-muted);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.slos-detail-value {
    font-size: 13px;
    color: var(--slos-text-primary);
    word-break: break-all;
}

/* State Changes */
.slos-state-change {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 16px;
    padding: 12px;
    background: var(--slos-bg-card);
    border-radius: 8px;
}

.slos-state-box {
    flex: 1;
    padding: 12px;
    border-radius: 6px;
    font-size: 12px;
}

.slos-state-box.previous {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.slos-state-box.new {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.slos-state-box .label {
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--slos-text-secondary);
}

.slos-state-arrow {
    color: var(--slos-text-muted);
    font-size: 20px;
}
</style>

<div class="slos-audit-container">
    <!-- Section Description -->
    <p class="slos-section-description">
        <?php esc_html_e( 'Tamper-proof audit trail of all consent-related actions. Each entry is timestamped and includes user agent, IP (hashed), and previous state for forensic analysis. Required by GDPR Article 30 for Records of Processing Activities. Logs cannot be modified or deleted to maintain integrity.', 'shahi-legalflowsuite' ); ?>
    </p>
    
    <!-- Header -->
    <div class="slos-audit-header">
        <div class="slos-audit-info">
            <h2 style="margin: 0; font-size: 18px; color: var(--slos-text-primary);">
                <?php esc_html_e( 'Consent Audit Trail', 'shahi-legalflowsuite' ); ?>
            </h2>
            <div class="slos-audit-badge">
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e( 'Immutable Log', 'shahi-legalflowsuite' ); ?>
            </div>
        </div>
        <div style="display: flex; gap: 12px;">
            <button class="slos-btn slos-btn-secondary" id="export-logs">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export Logs', 'shahi-legalflowsuite' ); ?>
            </button>
            <button class="slos-btn slos-btn-primary" id="refresh-logs">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e( 'Refresh', 'shahi-legalflowsuite' ); ?>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="slos-audit-filters">
        <div class="slos-date-range">
            <input type="date" id="log-date-from" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>">
            <span><?php esc_html_e( 'to', 'shahi-legalflowsuite' ); ?></span>
            <input type="date" id="log-date-to" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
        </div>

        <select class="slos-filter-select" id="log-action-filter">
            <option value=""><?php esc_html_e( 'All Actions', 'shahi-legalflowsuite' ); ?></option>
            <option value="grant"><?php esc_html_e( 'Grant', 'shahi-legalflowsuite' ); ?></option>
            <option value="withdraw"><?php esc_html_e( 'Withdraw', 'shahi-legalflowsuite' ); ?></option>
            <option value="update"><?php esc_html_e( 'Update', 'shahi-legalflowsuite' ); ?></option>
            <option value="export"><?php esc_html_e( 'Export', 'shahi-legalflowsuite' ); ?></option>
            <option value="import"><?php esc_html_e( 'Import', 'shahi-legalflowsuite' ); ?></option>
        </select>

        <select class="slos-filter-select" id="log-purpose-filter">
            <option value=""><?php esc_html_e( 'All Purposes', 'shahi-legalflowsuite' ); ?></option>
            <option value="necessary"><?php esc_html_e( 'Necessary', 'shahi-legalflowsuite' ); ?></option>
            <option value="analytics"><?php esc_html_e( 'Analytics', 'shahi-legalflowsuite' ); ?></option>
            <option value="marketing"><?php esc_html_e( 'Marketing', 'shahi-legalflowsuite' ); ?></option>
            <option value="preferences"><?php esc_html_e( 'Preferences', 'shahi-legalflowsuite' ); ?></option>
        </select>

        <input type="search" 
               class="slos-search-input" 
               placeholder="<?php esc_attr_e( 'Search by user ID, IP, or consent ID...', 'shahi-legalflowsuite' ); ?>"
               id="log-search"
               style="min-width: 300px;">
    </div>

    <!-- Log List -->
    <div class="slos-log-card">
        <div class="slos-log-list" id="audit-log-list">
            <?php
            // Load audit logs from database
            global $wpdb;
            $logs_table = $wpdb->prefix . 'slos_consent_logs';
            
            // Check if table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $logs_table ) );
            
            $audit_logs = array();
            if ( $table_exists ) {
                $audit_logs = $wpdb->get_results(
                    "SELECT * FROM {$logs_table} ORDER BY created_at DESC LIMIT 25",
                    ARRAY_A
                );
            }
            
            if ( ! empty( $audit_logs ) ) :
                foreach ( $audit_logs as $log ) :
                    $action = $log['action'] ?? 'grant';
                    $icon_class = 'grant';
                    $icon = 'yes-alt';
                    
                    if ( $action === 'withdraw' || $action === 'reject' ) {
                        $icon_class = 'withdraw';
                        $icon = 'minus';
                    } elseif ( $action === 'update' ) {
                        $icon_class = 'update';
                        $icon = 'update';
                    } elseif ( $action === 'export' ) {
                        $icon_class = 'export';
                        $icon = 'download';
                    }
                    
                    $user_id = $log['user_id'] ?? 0;
                    $user_display = $user_id > 0 ? sprintf( 'User #%d', $user_id ) : __( 'Guest', 'shahi-legalflowsuite' );
                    $purpose = ucfirst( $log['purpose'] ?? 'consent' );
                    $ip_hash = isset( $log['ip_address'] ) ? substr( $log['ip_address'], 0, -3 ) . '***' : '***';
                    $consent_id = $log['consent_id'] ?? $log['id'] ?? 0;
                    $method = $log['method'] ?? 'banner';
                    $created_at = $log['created_at'] ?? current_time( 'mysql' );
                    ?>
                    <div class="slos-log-entry" data-id="<?php echo esc_attr( $log['id'] ?? 0 ); ?>">
                        <div class="slos-log-icon <?php echo esc_attr( $icon_class ); ?>">
                            <span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
                        </div>
                        <div class="slos-log-content">
                            <div class="slos-log-title">
                                <strong><?php echo esc_html( $user_display ); ?></strong> 
                                <?php
                                if ( $action === 'grant' ) {
                                    esc_html_e( 'granted consent for', 'shahi-legalflowsuite' );
                                } elseif ( $action === 'withdraw' || $action === 'reject' ) {
                                    esc_html_e( 'withdrew consent for', 'shahi-legalflowsuite' );
                                } elseif ( $action === 'update' ) {
                                    esc_html_e( 'updated preferences for', 'shahi-legalflowsuite' );
                                } else {
                                    echo esc_html( $action );
                                }
                                ?>
                                <span class="slos-action-tag <?php echo esc_attr( $icon_class ); ?>"><?php echo esc_html( $purpose ); ?></span>
                            </div>
                            <div class="slos-log-meta">
                                <span class="slos-log-meta-item">
                                    <span class="dashicons dashicons-admin-post"></span>
                                    <?php echo esc_html( ucfirst( $method ) ); ?>
                                </span>
                                <span class="slos-log-meta-item">
                                    <span class="dashicons dashicons-admin-site"></span>
                                    <?php echo esc_html( $ip_hash ); ?>
                                </span>
                                <span class="slos-log-meta-item">
                                    <span class="dashicons dashicons-id"></span>
                                    #CON-<?php echo esc_html( $consent_id ); ?>
                                </span>
                            </div>
                        </div>
                        <div class="slos-log-time">
                            <div class="time"><?php echo esc_html( date_i18n( 'g:i A', strtotime( $created_at ) ) ); ?></div>
                            <div class="date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $created_at ) ) ); ?></div>
                        </div>
                        <button class="slos-log-expand" aria-label="<?php esc_attr_e( 'View details', 'shahi-legalflowsuite' ); ?>">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                <?php endforeach;
            else : ?>
                <!-- Empty state -->
                <div class="slos-empty-state" id="logs-empty">
                    <span class="dashicons dashicons-visibility"></span>
                    <h3><?php esc_html_e( 'No Audit Logs Yet', 'shahi-legalflowsuite' ); ?></h3>
                    <p><?php esc_html_e( 'Audit logs will appear here as users interact with your consent banner and privacy settings.', 'shahi-legalflowsuite' ); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $audit_logs ) ) : ?>
        <!-- Pagination -->
        <div class="slos-pagination">
            <div class="slos-pagination-info">
                <?php
                $total_logs = $table_exists ? $wpdb->get_var( "SELECT COUNT(*) FROM {$logs_table}" ) : 0;
                printf(
                    esc_html__( 'Showing 1 - %1$d of %2$s log entries', 'shahi-legalflowsuite' ),
                    min( 25, count( $audit_logs ) ),
                    number_format_i18n( $total_logs )
                );
                ?>
            </div>
            <div class="slos-pagination-controls">
                <button class="slos-page-btn" disabled>
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <button class="slos-page-btn active">1</button>
                <?php if ( $total_logs > 25 ) : ?>
                <button class="slos-page-btn">2</button>
                <?php endif; ?>
                <?php if ( $total_logs > 50 ) : ?>
                <button class="slos-page-btn">3</button>
                <span style="color: var(--slos-text-muted);">...</span>
                <button class="slos-page-btn"><?php echo ceil( $total_logs / 25 ); ?></button>
                <?php endif; ?>
                <button class="slos-page-btn" <?php echo $total_logs <= 25 ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    let currentPage = 1;
    let isLoading = false;
    
    // Expand/collapse log details
    $(document).on('click', '.slos-log-expand', function() {
        const entry = $(this).closest('.slos-log-entry');
        entry.toggleClass('expanded');
        
        const icon = $(this).find('.dashicons');
        if (entry.hasClass('expanded')) {
            icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        } else {
            icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        }
    });

    // Filter changes
    $('#log-action-filter, #log-purpose-filter').on('change', function() {
        currentPage = 1;
        loadAuditLogs();
    });

    // Date range change
    $('#log-date-from, #log-date-to').on('change', function() {
        currentPage = 1;
        loadAuditLogs();
    });

    // Search with debounce
    let searchTimeout;
    $('#log-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadAuditLogs();
        }, 500);
    });

    function getFilters() {
        return {
            action: $('#log-action-filter').val(),
            purpose: $('#log-purpose-filter').val(),
            date_from: $('#log-date-from').val(),
            date_to: $('#log-date-to').val(),
            search: $('#log-search').val()
        };
    }

    function loadAuditLogs() {
        if (isLoading) return;
        isLoading = true;
        
        const filters = getFilters();
        const $logList = $('.slos-log-list');
        
        $logList.html('<div class="slos-log-loading" style="text-align: center; padding: 60px;"><span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; font-size: 32px; color: var(--slos-accent);"></span><p style="margin-top: 16px; color: var(--slos-text-muted);"><?php echo esc_js( __( 'Loading audit logs...', 'shahi-legalflowsuite' ) ); ?></p></div>');
        
        $.ajax({
            url: API_BASE + '/consents/logs',
            method: 'GET',
            headers: { 'X-WP-Nonce': NONCE },
            data: {
                action: filters.action,
                purpose: filters.purpose,
                date_from: filters.date_from,
                date_to: filters.date_to,
                search: filters.search,
                page: currentPage,
                per_page: 50
            },
            success: function(response) {
                isLoading = false;
                const logs = response.data || response || [];
                const total = response.total || logs.length;
                
                if (logs.length === 0) {
                    $logList.html('<div class="slos-log-empty" style="text-align: center; padding: 60px;"><span class="dashicons dashicons-search" style="font-size: 48px; color: var(--slos-text-muted); opacity: 0.5;"></span><p style="margin-top: 16px; color: var(--slos-text-muted);"><?php echo esc_js( __( 'No audit logs found matching your filters.', 'shahi-legalflowsuite' ) ); ?></p></div>');
                    updateLogStats(0);
                    return;
                }
                
                let html = '';
                logs.forEach(function(log) {
                    const actionClass = getActionClass(log.action);
                    const actionIcon = getActionIcon(log.action);
                    const userInitials = (log.user_name || 'SY').substring(0, 2).toUpperCase();
                    const timestamp = log.created_at || 'N/A';
                    
                    html += '<div class="slos-log-entry" data-id="' + log.id + '">';
                    html += '<div class="slos-log-icon ' + actionClass + '"><span class="dashicons dashicons-' + actionIcon + '"></span></div>';
                    html += '<div class="slos-log-content">';
                    html += '<div class="slos-log-main">';
                    html += '<div class="slos-log-title">' + escapeHtml(log.action || 'Unknown Action') + '</div>';
                    html += '<div class="slos-log-meta">';
                    html += '<span class="slos-log-user"><span class="dashicons dashicons-admin-users"></span> ' + escapeHtml(log.user_name || 'System') + '</span>';
                    html += '<span class="slos-log-time"><span class="dashicons dashicons-clock"></span> ' + escapeHtml(timestamp) + '</span>';
                    if (log.ip_address) {
                        html += '<span class="slos-log-ip"><span class="dashicons dashicons-admin-site"></span> ' + escapeHtml(log.ip_address) + '</span>';
                    }
                    html += '</div></div>';
                    html += '<button class="slos-log-expand" type="button"><span class="dashicons dashicons-arrow-down-alt2"></span></button>';
                    html += '</div>';
                    html += '<div class="slos-log-details">';
                    if (log.old_value || log.new_value) {
                        html += '<div class="slos-log-detail-row"><span class="label"><?php echo esc_js( __( 'Old Value:', 'shahi-legalflowsuite' ) ); ?></span><span class="value">' + escapeHtml(log.old_value || 'N/A') + '</span></div>';
                        html += '<div class="slos-log-detail-row"><span class="label"><?php echo esc_js( __( 'New Value:', 'shahi-legalflowsuite' ) ); ?></span><span class="value">' + escapeHtml(log.new_value || 'N/A') + '</span></div>';
                    }
                    html += '<div class="slos-log-detail-row"><span class="label"><?php echo esc_js( __( 'Log ID:', 'shahi-legalflowsuite' ) ); ?></span><span class="value">#' + log.id + '</span></div>';
                    if (log.consent_id) {
                        html += '<div class="slos-log-detail-row"><span class="label"><?php echo esc_js( __( 'Consent ID:', 'shahi-legalflowsuite' ) ); ?></span><span class="value">#' + log.consent_id + '</span></div>';
                    }
                    html += '</div></div>';
                });
                
                $logList.html(html);
                updateLogStats(total);
            },
            error: function() {
                isLoading = false;
                $logList.html('<div class="slos-log-error" style="text-align: center; padding: 60px;"><span class="dashicons dashicons-warning" style="font-size: 48px; color: var(--slos-danger);"></span><p style="margin-top: 16px; color: var(--slos-text-muted);"><?php echo esc_js( __( 'Error loading audit logs. Please try again.', 'shahi-legalflowsuite' ) ); ?></p></div>');
            }
        });
    }
    
    function getActionClass(action) {
        action = (action || '').toLowerCase();
        if (action.includes('granted') || action.includes('accepted') || action.includes('created')) return 'success';
        if (action.includes('revoked') || action.includes('rejected') || action.includes('deleted')) return 'danger';
        if (action.includes('updated') || action.includes('changed')) return 'warning';
        return 'info';
    }
    
    function getActionIcon(action) {
        action = (action || '').toLowerCase();
        if (action.includes('granted') || action.includes('accepted')) return 'yes-alt';
        if (action.includes('revoked') || action.includes('rejected')) return 'dismiss';
        if (action.includes('updated') || action.includes('changed')) return 'edit';
        if (action.includes('deleted')) return 'trash';
        if (action.includes('created')) return 'plus-alt2';
        return 'info';
    }
    
    function updateLogStats(total) {
        const $statValue = $('.slos-audit-stat .value');
        if ($statValue.length) {
            $statValue.first().text(total.toLocaleString());
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Export logs
    $('#export-logs').on('click', function() {
        const filters = getFilters();
        let url = API_BASE + '/consents/export/download?format=csv&export_type=logs&_wpnonce=' + NONCE;
        if (filters.action) url += '&action=' + encodeURIComponent(filters.action);
        if (filters.purpose) url += '&purpose=' + encodeURIComponent(filters.purpose);
        if (filters.date_from) url += '&date_from=' + filters.date_from;
        if (filters.date_to) url += '&date_to=' + filters.date_to;
        if (filters.search) url += '&search=' + encodeURIComponent(filters.search);
        
        window.location.href = url;
    });

    // Refresh
    $('#refresh-logs').on('click', function() {
        const $btn = $(this);
        const $icon = $btn.find('.dashicons');
        $icon.css('animation', 'spin 1s linear infinite');
        
        loadAuditLogs();
        
        setTimeout(function() {
            $icon.css('animation', '');
        }, 1000);
    });
    
    // Load more on scroll
    $('.slos-log-list').on('scroll', function() {
        const $this = $(this);
        if ($this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 100) {
            if (!isLoading) {
                currentPage++;
                // Could implement pagination append here
            }
        }
    });
});
</script>
