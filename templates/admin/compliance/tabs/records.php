<?php
/**
 * Consent Records Tab - V3 Design
 *
 * Full consent records table with advanced filtering and bulk actions.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get filter values from URL
$filter_type     = isset( $_GET['consent_type'] ) ? sanitize_text_field( $_GET['consent_type'] ) : '';
$filter_status   = isset( $_GET['consent_status'] ) ? sanitize_text_field( $_GET['consent_status'] ) : '';
$filter_date     = isset( $_GET['date_range'] ) ? sanitize_text_field( $_GET['date_range'] ) : '30d';
$filter_geo_rule = isset( $_GET['geo_rule'] ) ? sanitize_text_field( $_GET['geo_rule'] ) : '';
$filter_region   = isset( $_GET['region'] ) ? sanitize_text_field( $_GET['region'] ) : '';
$search_query    = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

// Get geo rules for filter dropdown
$geo_rules = get_option( 'slos_geo_rules', array() );
if ( ! is_array( $geo_rules ) ) {
	$geo_rules = array();
}

// Available regions
$available_regions = array(
	'EU'     => __( 'European Union', 'shahi-legalops-suite' ),
	'EEA'    => __( 'European Economic Area', 'shahi-legalops-suite' ),
	'NA'     => __( 'North America', 'shahi-legalops-suite' ),
	'LATAM'  => __( 'Latin America', 'shahi-legalops-suite' ),
	'APAC'   => __( 'Asia Pacific', 'shahi-legalops-suite' ),
	'GLOBAL' => __( 'Global (Other)', 'shahi-legalops-suite' ),
);
?>

<style>
/* Records-specific styles */
.slos-records-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.slos-filters-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.slos-filter-select {
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    padding: 10px 36px 10px 14px;
    color: var(--slos-text-primary);
    font-size: 14px;
    min-width: 140px;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2371717a' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.slos-filter-select:focus {
    outline: none;
    border-color: var(--slos-accent);
}

.slos-search-input {
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    padding: 10px 14px 10px 40px;
    color: var(--slos-text-primary);
    font-size: 14px;
    min-width: 280px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 14px center;
}

.slos-search-input:focus {
    outline: none;
    border-color: var(--slos-accent);
}

.slos-search-input::placeholder {
    color: var(--slos-text-muted);
}

.slos-bulk-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.slos-selected-count {
    font-size: 13px;
    color: var(--slos-text-muted);
}

.slos-selected-count strong {
    color: var(--slos-accent);
}

/* Enhanced Data Table */
.slos-records-table-wrapper {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    overflow: hidden;
}

.slos-records-table {
    width: 100%;
    border-collapse: collapse;
}

.slos-records-table th {
    text-align: left;
    padding: 14px 16px;
    font-size: 12px;
    font-weight: 600;
    color: var(--slos-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--slos-border);
    background: var(--slos-bg-input);
    position: sticky;
    top: 0;
    z-index: 10;
}

.slos-records-table th.sortable {
    cursor: pointer;
    user-select: none;
}

.slos-records-table th.sortable:hover {
    color: var(--slos-accent);
}

.slos-records-table th .sort-icon {
    margin-left: 4px;
    opacity: 0.5;
}

.slos-records-table th.sorted .sort-icon {
    opacity: 1;
    color: var(--slos-accent);
}

.slos-records-table td {
    padding: 16px;
    font-size: 14px;
    color: var(--slos-text-secondary);
    border-bottom: 1px solid var(--slos-border);
    vertical-align: middle;
}

.slos-records-table tbody tr {
    transition: background 0.15s;
}

.slos-records-table tbody tr:hover {
    background: rgba(59, 130, 246, 0.05);
}

.slos-records-table tbody tr.selected {
    background: rgba(59, 130, 246, 0.1);
}

.slos-records-table tbody tr:last-child td {
    border-bottom: none;
}

/* Checkbox Column */
.slos-checkbox-cell {
    width: 40px;
}

.slos-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid var(--slos-border);
    border-radius: 4px;
    cursor: pointer;
    appearance: none;
    background: transparent;
    transition: all 0.15s;
}

.slos-checkbox:checked {
    background: var(--slos-accent);
    border-color: var(--slos-accent);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
}

/* User Cell */
.slos-user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.slos-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--slos-bg-elevated);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--slos-accent);
}

.slos-user-info .name {
    color: var(--slos-text-primary);
    font-weight: 500;
}

.slos-user-info .email {
    font-size: 12px;
    color: var(--slos-text-muted);
}

/* Actions Cell */
.slos-actions-cell {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slos-action-icon {
    background: transparent;
    border: none;
    padding: 6px;
    border-radius: 6px;
    cursor: pointer;
    color: var(--slos-text-muted);
    transition: all 0.15s;
}

.slos-action-icon:hover {
    background: var(--slos-bg-elevated);
    color: var(--slos-accent);
}

.slos-action-icon .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Pagination */
.slos-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--slos-bg-input);
    border-top: 1px solid var(--slos-border);
}

.slos-pagination-info {
    font-size: 13px;
    color: var(--slos-text-muted);
}

.slos-pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slos-page-btn {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--slos-text-secondary);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}

.slos-page-btn:hover:not(:disabled) {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

.slos-page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.slos-page-btn.active {
    background: var(--slos-accent);
    border-color: var(--slos-accent);
    color: white;
}

.slos-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slos-per-page label {
    font-size: 13px;
    color: var(--slos-text-muted);
}

.slos-per-page select {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    padding: 6px 10px;
    color: var(--slos-text-primary);
    font-size: 13px;
}

/* Metadata Badge */
.slos-meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: var(--slos-bg-elevated);
    border-radius: 4px;
    font-size: 11px;
    color: var(--slos-text-muted);
}

/* Source Icon */
.slos-source-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--slos-bg-elevated);
}

.slos-source-icon.banner { background: rgba(59, 130, 246, 0.15); color: var(--slos-accent); }
.slos-source-icon.api { background: rgba(147, 197, 253, 0.15); color: var(--slos-accent-light, #93c5fd); }
.slos-source-icon.import { background: rgba(245, 158, 11, 0.15); color: var(--slos-warning); }

/* Geo Cell Styles */
.slos-geo-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.slos-country-flag {
    font-weight: 600;
    font-size: 12px;
    color: var(--slos-text-primary);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.slos-region-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 6px;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: var(--slos-accent);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.slos-region-badge.EU,
.slos-region-badge.EEA {
    background: rgba(34, 197, 94, 0.1);
    color: var(--slos-success);
}

.slos-region-badge.NA {
    background: rgba(59, 130, 246, 0.1);
    color: var(--slos-accent);
}

.slos-region-badge.LATAM {
    background: rgba(245, 158, 11, 0.1);
    color: var(--slos-warning);
}

.slos-region-badge.APAC {
    background: rgba(147, 197, 253, 0.1);
    color: var(--slos-accent-light, #93c5fd);
}
</style>

<div class="slos-records-container">
    <!-- Section Description -->
    <p class="slos-section-description">
        <?php esc_html_e( 'Complete audit trail of all consent interactions on your site. This data serves as proof of consent for GDPR Article 7 compliance. Use filters to narrow results or search by user identifier. Export records for compliance audits and regulatory inquiries.', 'shahi-legalops-suite' ); ?>
    </p>
    
    <!-- Toolbar -->
    <div class="slos-records-toolbar">
        <div class="slos-filters-row">
            <select class="slos-filter-select" id="filter-type">
                <option value=""><?php esc_html_e( 'All Types', 'shahi-legalops-suite' ); ?></option>
                <option value="necessary" <?php selected( $filter_type, 'necessary' ); ?>><?php esc_html_e( 'Necessary', 'shahi-legalops-suite' ); ?></option>
                <option value="analytics" <?php selected( $filter_type, 'analytics' ); ?>><?php esc_html_e( 'Analytics', 'shahi-legalops-suite' ); ?></option>
                <option value="marketing" <?php selected( $filter_type, 'marketing' ); ?>><?php esc_html_e( 'Marketing', 'shahi-legalops-suite' ); ?></option>
                <option value="preferences" <?php selected( $filter_type, 'preferences' ); ?>><?php esc_html_e( 'Preferences', 'shahi-legalops-suite' ); ?></option>
            </select>

            <select class="slos-filter-select" id="filter-status">
                <option value=""><?php esc_html_e( 'All Statuses', 'shahi-legalops-suite' ); ?></option>
                <option value="accepted" <?php selected( $filter_status, 'accepted' ); ?>><?php esc_html_e( 'Accepted', 'shahi-legalops-suite' ); ?></option>
                <option value="rejected" <?php selected( $filter_status, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'shahi-legalops-suite' ); ?></option>
                <option value="withdrawn" <?php selected( $filter_status, 'withdrawn' ); ?>><?php esc_html_e( 'Withdrawn', 'shahi-legalops-suite' ); ?></option>
            </select>

            <select class="slos-filter-select" id="filter-date">
                <option value="7d" <?php selected( $filter_date, '7d' ); ?>><?php esc_html_e( 'Last 7 days', 'shahi-legalops-suite' ); ?></option>
                <option value="30d" <?php selected( $filter_date, '30d' ); ?>><?php esc_html_e( 'Last 30 days', 'shahi-legalops-suite' ); ?></option>
                <option value="90d" <?php selected( $filter_date, '90d' ); ?>><?php esc_html_e( 'Last 90 days', 'shahi-legalops-suite' ); ?></option>
                <option value="all" <?php selected( $filter_date, 'all' ); ?>><?php esc_html_e( 'All time', 'shahi-legalops-suite' ); ?></option>
            </select>

            <?php if ( ! empty( $geo_rules ) ) : ?>
            <select class="slos-filter-select" id="filter-geo-rule">
                <option value=""><?php esc_html_e( 'All Geo Rules', 'shahi-legalops-suite' ); ?></option>
                <?php foreach ( $geo_rules as $rule ) : 
                    $rule_id = $rule['id'] ?? '';
                    $rule_name = $rule['name'] ?? __( 'Unnamed Rule', 'shahi-legalops-suite' );
                ?>
                <option value="<?php echo esc_attr( $rule_id ); ?>" <?php selected( $filter_geo_rule, $rule_id ); ?>>
                    <?php echo esc_html( $rule_name ); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select class="slos-filter-select" id="filter-region">
                <option value=""><?php esc_html_e( 'All Regions', 'shahi-legalops-suite' ); ?></option>
                <?php foreach ( $available_regions as $region_code => $region_name ) : ?>
                <option value="<?php echo esc_attr( $region_code ); ?>" <?php selected( $filter_region, $region_code ); ?>>
                    <?php echo esc_html( $region_name ); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <input type="search" 
                   class="slos-search-input" 
                   placeholder="<?php esc_attr_e( 'Search by user, email, or ID...', 'shahi-legalops-suite' ); ?>"
                   value="<?php echo esc_attr( $search_query ); ?>"
                   id="records-search">
        </div>

        <div class="slos-bulk-actions">
            <span class="slos-selected-count" id="selected-count" style="display: none;">
                <strong>0</strong> <?php esc_html_e( 'selected', 'shahi-legalops-suite' ); ?>
            </span>
            <button class="slos-btn slos-btn-secondary" id="bulk-export" style="display: none;">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export Selected', 'shahi-legalops-suite' ); ?>
            </button>
            <button class="slos-btn slos-btn-primary" id="export-all">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export All', 'shahi-legalops-suite' ); ?>
            </button>
        </div>
    </div>

    <!-- Records Table -->
    <div class="slos-records-table-wrapper">
        <table class="slos-records-table" id="consent-records-table">
            <thead>
                <tr>
                    <th class="slos-checkbox-cell">
                        <input type="checkbox" class="slos-checkbox" id="select-all">
                    </th>
                    <th class="sortable" data-sort="id">
                        <?php esc_html_e( 'ID', 'shahi-legalops-suite' ); ?>
                        <span class="dashicons dashicons-sort sort-icon"></span>
                    </th>
                    <th><?php esc_html_e( 'User', 'shahi-legalops-suite' ); ?></th>
                    <th class="sortable" data-sort="type">
                        <?php esc_html_e( 'Type', 'shahi-legalops-suite' ); ?>
                        <span class="dashicons dashicons-sort sort-icon"></span>
                    </th>
                    <th class="sortable" data-sort="status">
                        <?php esc_html_e( 'Status', 'shahi-legalops-suite' ); ?>
                        <span class="dashicons dashicons-sort sort-icon"></span>
                    </th>
                    <th class="sortable" data-sort="region">
                        <?php esc_html_e( 'Region', 'shahi-legalops-suite' ); ?>
                        <span class="dashicons dashicons-sort sort-icon"></span>
                    </th>
                    <th><?php esc_html_e( 'Source', 'shahi-legalops-suite' ); ?></th>
                    <th class="sortable sorted" data-sort="date">
                        <?php esc_html_e( 'Date', 'shahi-legalops-suite' ); ?>
                        <span class="dashicons dashicons-arrow-down sort-icon"></span>
                    </th>
                    <th><?php esc_html_e( 'Actions', 'shahi-legalops-suite' ); ?></th>
                </tr>
            </thead>
            <tbody id="records-tbody">
                <?php if ( ! empty( $recent_activity ) ) : ?>
                    <?php foreach ( $recent_activity as $consent ) : 
                        $consent_arr = (array) $consent;
                        $user_id = $consent_arr['user_id'] ?? 0;
                        $user = $user_id > 0 ? get_userdata( $user_id ) : null;
                        $initials = $user ? strtoupper( substr( $user->display_name, 0, 2 ) ) : 'G';
                    ?>
                        <tr data-id="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>">
                            <td class="slos-checkbox-cell">
                                <input type="checkbox" class="slos-checkbox row-checkbox" value="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>">
                            </td>
                            <td><strong>#<?php echo esc_html( $consent_arr['id'] ?? 0 ); ?></strong></td>
                            <td>
                                <div class="slos-user-cell">
                                    <div class="slos-user-avatar"><?php echo esc_html( $initials ); ?></div>
                                    <div class="slos-user-info">
                                        <div class="name"><?php echo esc_html( $user ? $user->display_name : __( 'Guest User', 'shahi-legalops-suite' ) ); ?></div>
                                        <div class="email"><?php echo esc_html( $user ? $user->user_email : __( 'Anonymous', 'shahi-legalops-suite' ) ); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="slos-type-badge"><?php echo esc_html( ucfirst( $consent_arr['type'] ?? 'unknown' ) ); ?></span></td>
                            <td>
                                <span class="slos-status-badge <?php echo esc_attr( $consent_arr['status'] ?? '' ); ?>">
                                    <?php echo esc_html( ucfirst( $consent_arr['status'] ?? 'unknown' ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $country_code = $consent_arr['country_code'] ?? '';
                                $region = $consent_arr['region'] ?? '';
                                $geo_rule_id = $consent_arr['geo_rule_id'] ?? null;
                                
                                // Find rule name if we have geo_rule_id
                                $rule_name = '';
                                if ( $geo_rule_id && ! empty( $geo_rules ) ) {
                                    foreach ( $geo_rules as $rule ) {
                                        if ( ( $rule['id'] ?? '' ) == $geo_rule_id ) {
                                            $rule_name = $rule['name'] ?? '';
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="slos-geo-cell">
                                    <?php if ( $country_code ) : ?>
                                        <span class="slos-country-flag" title="<?php echo esc_attr( $country_code ); ?>">
                                            <?php echo esc_html( $country_code ); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( $region ) : ?>
                                        <span class="slos-region-badge"><?php echo esc_html( $region ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $rule_name ) : ?>
                                        <span class="slos-meta-badge" title="<?php echo esc_attr( $rule_name ); ?>">
                                            <?php echo esc_html( $rule_name ); ?>
                                        </span>
                                    <?php elseif ( ! $country_code && ! $region ) : ?>
                                        <span class="slos-meta-badge">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="slos-source-icon banner" title="<?php esc_attr_e( 'Consent Banner', 'shahi-legalops-suite' ); ?>">
                                    <span class="dashicons dashicons-admin-post"></span>
                                </div>
                            </td>
                            <td>
                                <div><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $consent_arr['created_at'] ?? '' ) ) ); ?></div>
                                <span class="slos-meta-badge"><?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $consent_arr['created_at'] ?? '' ) ) ); ?></span>
                            </td>
                            <td>
                                <div class="slos-actions-cell">
                                    <button class="slos-action-icon" data-action="view" data-id="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>" title="<?php esc_attr_e( 'View Details', 'shahi-legalops-suite' ); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                    <button class="slos-action-icon" data-action="export" data-id="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>" title="<?php esc_attr_e( 'Export', 'shahi-legalops-suite' ); ?>">
                                        <span class="dashicons dashicons-download"></span>
                                    </button>
                                    <button class="slos-action-icon" data-action="history" data-id="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>" title="<?php esc_attr_e( 'View History', 'shahi-legalops-suite' ); ?>">
                                        <span class="dashicons dashicons-backup"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9">
                            <div class="slos-empty-state">
                                <span class="dashicons dashicons-list-view"></span>
                                <p><?php esc_html_e( 'No consent records found. Records will appear here as users interact with your consent banner.', 'shahi-legalops-suite' ); ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="slos-pagination">
            <div class="slos-pagination-info">
                <?php 
                $total = $stats['total'] ?? 0;
                printf( 
                    esc_html__( 'Showing %1$d - %2$d of %3$d records', 'shahi-legalops-suite' ),
                    1,
                    min( 25, $total ),
                    $total
                );
                ?>
            </div>
            <div class="slos-pagination-controls">
                <div class="slos-per-page">
                    <label><?php esc_html_e( 'Per page:', 'shahi-legalops-suite' ); ?></label>
                    <select id="per-page">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button class="slos-page-btn" disabled>
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <button class="slos-page-btn active">1</button>
                <button class="slos-page-btn">2</button>
                <button class="slos-page-btn">3</button>
                <span style="color: var(--slos-text-muted);">...</span>
                <button class="slos-page-btn">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    let currentPage = 1;
    let perPage = 25;
    
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    // Individual checkbox
    $(document).on('change', '.row-checkbox', function() {
        updateSelectedCount();
        // Update select-all state
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#select-all').prop('checked', total === checked);
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
    });

    function updateSelectedCount() {
        const count = $('.row-checkbox:checked').length;
        if (count > 0) {
            $('#selected-count').show().find('strong').text(count);
            $('#bulk-export').show();
        } else {
            $('#selected-count').hide();
            $('#bulk-export').hide();
        }
    }

    // Filter changes - apply filters via AJAX
    $('#filter-type, #filter-status, #filter-date, #filter-geo-rule, #filter-region').on('change', function() {
        currentPage = 1;
        loadRecords();
    });

    // Search with debounce
    let searchTimeout;
    $('#records-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadRecords();
        }, 500);
    });

    // Sortable columns
    $('.sortable').on('click', function() {
        const column = $(this).data('sort');
        const isAsc = $(this).hasClass('sorted-asc');
        
        $('.sortable').removeClass('sorted sorted-asc sorted-desc');
        $(this).addClass('sorted ' + (isAsc ? 'sorted-desc' : 'sorted-asc'));
        
        loadRecords({ orderby: column, order: isAsc ? 'desc' : 'asc' });
    });

    // Row actions
    $(document).on('click', '.slos-action-icon', function() {
        const action = $(this).data('action');
        const id = $(this).data('id');
        
        switch(action) {
            case 'view':
                viewConsentDetail(id);
                break;
            case 'export':
                exportSingleRecord(id);
                break;
            case 'history':
                viewConsentHistory(id);
                break;
        }
    });
    
    // View consent detail
    function viewConsentDetail(id) {
        $.ajax({
            url: API_BASE + '/consents/' + id,
            method: 'GET',
            headers: { 'X-WP-Nonce': NONCE },
            success: function(response) {
                const data = response.data || response;
                alert('<?php echo esc_js( __( 'Consent #', 'shahi-legalops-suite' ) ); ?>' + id + '\n<?php echo esc_js( __( 'Type:', 'shahi-legalops-suite' ) ); ?> ' + (data.type || 'N/A') + '\n<?php echo esc_js( __( 'Status:', 'shahi-legalops-suite' ) ); ?> ' + (data.status || 'N/A'));
            },
            error: function() {
                alert('<?php echo esc_js( __( 'Error loading consent details.', 'shahi-legalops-suite' ) ); ?>');
            }
        });
    }
    
    // Export single record
    function exportSingleRecord(id) {
        window.location.href = API_BASE + '/consents/export/download?ids=' + id + '&format=csv&_wpnonce=' + NONCE;
    }
    
    // View consent history
    function viewConsentHistory(id) {
        $.ajax({
            url: API_BASE + '/consents/logs',
            method: 'GET',
            data: { consent_id: id, per_page: 10 },
            headers: { 'X-WP-Nonce': NONCE },
            success: function(response) {
                const logs = response.data || response || [];
                if (logs.length > 0) {
                    let history = '<?php echo esc_js( __( 'History for Consent #', 'shahi-legalops-suite' ) ); ?>' + id + ':\n\n';
                    logs.forEach(function(log, i) {
                        history += (i + 1) + '. ' + (log.action || 'Action') + ' - ' + (log.created_at || 'Date') + '\n';
                    });
                    alert(history);
                } else {
                    alert('<?php echo esc_js( __( 'No history found for this consent.', 'shahi-legalops-suite' ) ); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js( __( 'Error loading consent history.', 'shahi-legalops-suite' ) ); ?>');
            }
        });
    }
    
    // Export all
    $('#export-all').on('click', function() {
        const filters = getFilters();
        let url = API_BASE + '/consents/export/download?format=csv&_wpnonce=' + NONCE;
        if (filters.type) url += '&type=' + filters.type;
        if (filters.status) url += '&status=' + filters.status;
        if (filters.date_range) url += '&date_range=' + filters.date_range;
        if (filters.search) url += '&search=' + encodeURIComponent(filters.search);
        window.location.href = url;
    });
    
    // Bulk export
    $('#bulk-export').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get().join(',');
        
        if (ids) {
            window.location.href = API_BASE + '/consents/export/download?ids=' + ids + '&format=csv&_wpnonce=' + NONCE;
        }
    });
    
    // Per page change
    $('#per-page').on('change', function() {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadRecords();
    });
    
    // Pagination
    $(document).on('click', '.slos-page-btn:not(:disabled)', function() {
        const $btn = $(this);
        if ($btn.find('.dashicons-arrow-left-alt2').length) {
            currentPage = Math.max(1, currentPage - 1);
        } else if ($btn.find('.dashicons-arrow-right-alt2').length) {
            currentPage++;
        } else {
            currentPage = parseInt($btn.text());
        }
        loadRecords();
    });
    
    function getFilters() {
        return {
            type: $('#filter-type').val(),
            status: $('#filter-status').val(),
            date_range: $('#filter-date').val(),
            geo_rule_id: $('#filter-geo-rule').val(),
            region: $('#filter-region').val(),
            search: $('#records-search').val()
        };
    }
    
    function loadRecords(sortOpts) {
        const filters = getFilters();
        const $tbody = $('#records-tbody');
        
        $tbody.html('<tr><td colspan="9" style="text-align: center; padding: 40px;"><span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php echo esc_js( __( 'Loading...', 'shahi-legalops-suite' ) ); ?></td></tr>');
        
        $.ajax({
            url: API_BASE + '/consents',
            method: 'GET',
            headers: { 'X-WP-Nonce': NONCE },
            data: {
                type: filters.type,
                status: filters.status,
                date_range: filters.date_range,
                geo_rule_id: filters.geo_rule_id,
                region: filters.region,
                search: filters.search,
                page: currentPage,
                per_page: perPage,
                orderby: sortOpts?.orderby || 'created_at',
                order: sortOpts?.order || 'desc'
            },
            success: function(response) {
                const records = response.data || response || [];
                const total = response.total || records.length;
                
                if (records.length === 0) {
                    $tbody.html('<tr><td colspan="9"><div class="slos-empty-state"><span class="dashicons dashicons-list-view"></span><p><?php echo esc_js( __( 'No records found matching your filters.', 'shahi-legalops-suite' ) ); ?></p></div></td></tr>');
                    updatePagination(0, 0);
                    return;
                }
                
                let html = '';
                records.forEach(function(record) {
                    const initials = (record.user_name || 'Guest').substring(0, 2).toUpperCase();
                    const countryCode = record.country_code || '';
                    const region = record.region || '';
                    
                    // Build geo cell
                    let geoCell = '<div class="slos-geo-cell">';
                    if (countryCode) {
                        geoCell += '<span class="slos-country-flag" title="' + countryCode + '">' + countryCode + '</span>';
                    }
                    if (region) {
                        geoCell += '<span class="slos-region-badge ' + region + '">' + region + '</span>';
                    }
                    if (!countryCode && !region) {
                        geoCell += '<span class="slos-meta-badge">—</span>';
                    }
                    geoCell += '</div>';
                    
                    html += '<tr data-id="' + record.id + '">';
                    html += '<td class="slos-checkbox-cell"><input type="checkbox" class="slos-checkbox row-checkbox" value="' + record.id + '"></td>';
                    html += '<td><strong>#' + record.id + '</strong></td>';
                    html += '<td><div class="slos-user-cell"><div class="slos-user-avatar">' + initials + '</div><div class="slos-user-info"><div class="name">' + (record.user_name || 'Guest') + '</div><div class="email">' + (record.user_email || 'Anonymous') + '</div></div></div></td>';
                    html += '<td><span class="slos-type-badge">' + (record.type || 'Unknown').charAt(0).toUpperCase() + (record.type || 'Unknown').slice(1) + '</span></td>';
                    html += '<td><span class="slos-status-badge ' + (record.status || '') + '">' + (record.status || 'Unknown').charAt(0).toUpperCase() + (record.status || 'Unknown').slice(1) + '</span></td>';
                    html += '<td>' + geoCell + '</td>';
                    html += '<td><div class="slos-source-icon banner"><span class="dashicons dashicons-admin-post"></span></div></td>';
                    html += '<td><div>' + (record.created_at || 'N/A') + '</div></td>';
                    html += '<td><div class="slos-actions-cell">';
                    html += '<button class="slos-action-icon" data-action="view" data-id="' + record.id + '" title="<?php echo esc_js( __( 'View Details', 'shahi-legalops-suite' ) ); ?>"><span class="dashicons dashicons-visibility"></span></button>';
                    html += '<button class="slos-action-icon" data-action="export" data-id="' + record.id + '" title="<?php echo esc_js( __( 'Export', 'shahi-legalops-suite' ) ); ?>"><span class="dashicons dashicons-download"></span></button>';
                    html += '<button class="slos-action-icon" data-action="history" data-id="' + record.id + '" title="<?php echo esc_js( __( 'View History', 'shahi-legalops-suite' ) ); ?>"><span class="dashicons dashicons-backup"></span></button>';
                    html += '</div></td></tr>';
                });
                
                $tbody.html(html);
                updatePagination(total, records.length);
            },
            error: function() {
                $tbody.html('<tr><td colspan="9"><div class="slos-empty-state"><span class="dashicons dashicons-warning"></span><p><?php echo esc_js( __( 'Error loading records. Please try again.', 'shahi-legalops-suite' ) ); ?></p></div></td></tr>');
            }
        });
    }
    
    function updatePagination(total, showing) {
        const start = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
        const end = Math.min(currentPage * perPage, total);
        const totalPages = Math.ceil(total / perPage);
        
        $('.slos-pagination-info').text('<?php echo esc_js( __( 'Showing', 'shahi-legalops-suite' ) ); ?> ' + start + ' - ' + end + ' <?php echo esc_js( __( 'of', 'shahi-legalops-suite' ) ); ?> ' + total + ' <?php echo esc_js( __( 'records', 'shahi-legalops-suite' ) ); ?>');
        
        let paginationHtml = '<div class="slos-per-page"><label><?php echo esc_js( __( 'Per page:', 'shahi-legalops-suite' ) ); ?></label><select id="per-page"><option value="25"' + (perPage === 25 ? ' selected' : '') + '>25</option><option value="50"' + (perPage === 50 ? ' selected' : '') + '>50</option><option value="100"' + (perPage === 100 ? ' selected' : '') + '>100</option></select></div>';
        paginationHtml += '<button class="slos-page-btn"' + (currentPage <= 1 ? ' disabled' : '') + '><span class="dashicons dashicons-arrow-left-alt2"></span></button>';
        
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);
        startPage = Math.max(1, endPage - maxButtons + 1);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += '<button class="slos-page-btn' + (i === currentPage ? ' active' : '') + '">' + i + '</button>';
        }
        
        if (endPage < totalPages) {
            paginationHtml += '<span style="color: var(--slos-text-muted);">...</span>';
            paginationHtml += '<button class="slos-page-btn">' + totalPages + '</button>';
        }
        
        paginationHtml += '<button class="slos-page-btn"' + (currentPage >= totalPages ? ' disabled' : '') + '><span class="dashicons dashicons-arrow-right-alt2"></span></button>';
        
        $('.slos-pagination-controls').html(paginationHtml);
    }
});
</script>
