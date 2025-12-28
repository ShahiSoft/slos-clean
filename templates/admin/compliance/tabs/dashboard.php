<?php
/**
 * Compliance Dashboard Tab - V3 Design
 *
 * Main dashboard with metrics, charts, and compliance health score.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Calculate percentages for consent breakdown
$type_percentages = array();
$total_by_type = array_sum( $stats['by_type'] );
foreach ( $stats['by_type'] as $type => $count ) {
	$type_percentages[ $type ] = $total_by_type > 0 ? round( ( $count / $total_by_type ) * 100 ) : 0;
}

// Prepare grade class
$grade_class = 'grade-' . strtolower( $stats['grade'] );
$circumference = 2 * M_PI * 65;
$offset = $circumference - ( $stats['compliance_score'] / 100 ) * $circumference;
?>

<!-- Section Description -->
<p class="slos-section-description">
	<?php esc_html_e( 'Real-time overview of your privacy compliance status. Monitor consent metrics, review activity, and track compliance health across all supported regulations.', 'shahi-legalops-suite' ); ?>
</p>

<!-- Stats Grid -->
<div class="slos-stats-grid">
    <div class="slos-stat-card accent">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-groups"></span>
            <?php esc_html_e( 'Total Consents', 'shahi-legalops-suite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['total'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php if ( $stats['total'] > 0 ) : ?>
                <span class="trend up">
                    <span class="dashicons dashicons-chart-bar"></span>
                </span>
                <?php esc_html_e( 'all time', 'shahi-legalops-suite' ); ?>
            <?php else : ?>
                <?php esc_html_e( 'No data yet', 'shahi-legalops-suite' ); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="slos-stat-card success">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e( 'Accepted', 'shahi-legalops-suite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['accepted'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php echo esc_html( $stats['acceptance_rate'] ); ?>% <?php esc_html_e( 'acceptance rate', 'shahi-legalops-suite' ); ?>
        </div>
    </div>

    <div class="slos-stat-card danger">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-dismiss"></span>
            <?php esc_html_e( 'Rejected', 'shahi-legalops-suite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['rejected'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php echo esc_html( $stats['rejection_rate'] ); ?>% <?php esc_html_e( 'rejection rate', 'shahi-legalops-suite' ); ?>
        </div>
    </div>

    <div class="slos-stat-card warning">
        <div class="slos-stat-label">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e( 'Withdrawn', 'shahi-legalops-suite' ); ?>
        </div>
        <div class="slos-stat-value"><?php echo esc_html( number_format( $stats['withdrawn'] ) ); ?></div>
        <div class="slos-stat-meta">
            <?php esc_html_e( 'Requires follow-up', 'shahi-legalops-suite' ); ?>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="slos-two-col-grid">
    <!-- Main Column -->
    <div class="slos-main-column">
        <!-- Compliance Health Score -->
        <div class="slos-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-heart"></span>
                    <?php esc_html_e( 'Compliance Health Score', 'shahi-legalops-suite' ); ?>
                </h3>
                <span class="badge"><?php esc_html_e( 'Live', 'shahi-legalops-suite' ); ?></span>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Your overall privacy compliance rating based on consent acceptance rates. Score of 90%+ is excellent (Grade A). The gauge shows real-time compliance with GDPR, CCPA, LGPD, and ePrivacy requirements.', 'shahi-legalops-suite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-score-section">
                    <div class="slos-score-gauge <?php echo esc_attr( $grade_class ); ?>">
                        <svg viewBox="0 0 160 160">
                            <circle class="bg-circle" cx="80" cy="80" r="65"/>
                            <circle class="score-circle" cx="80" cy="80" r="65"
                                stroke-dasharray="<?php echo esc_attr( $circumference ); ?>"
                                stroke-dashoffset="<?php echo esc_attr( $offset ); ?>"/>
                        </svg>
                        <div class="slos-score-center">
                            <div class="slos-score-number"><?php echo esc_html( $stats['compliance_score'] ); ?></div>
                            <div class="slos-score-label"><?php esc_html_e( 'Score', 'shahi-legalops-suite' ); ?></div>
                        </div>
                    </div>

                    <div class="slos-score-details">
                        <div class="slos-grade-display">
                            <div class="slos-grade-badge <?php echo esc_attr( $grade_class ); ?>">
                                <?php echo esc_html( $stats['grade'] ); ?>
                            </div>
                            <div class="slos-grade-text">
                                <div class="grade-title"><?php echo esc_html( $stats['grade_text'] ); ?></div>
                                <div class="grade-subtitle"><?php esc_html_e( 'Privacy Compliance Status', 'shahi-legalops-suite' ); ?></div>
                            </div>
                        </div>

                        <div class="slos-compliance-badges">
                            <span class="slos-compliance-badge active">
                                <span class="dashicons dashicons-yes"></span>
                                GDPR
                            </span>
                            <span class="slos-compliance-badge active">
                                <span class="dashicons dashicons-yes"></span>
                                CCPA
                            </span>
                            <span class="slos-compliance-badge active">
                                <span class="dashicons dashicons-yes"></span>
                                LGPD
                            </span>
                            <span class="slos-compliance-badge">
                                <span class="dashicons dashicons-minus"></span>
                                ePrivacy
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e( 'Recent Activity', 'shahi-legalops-suite' ); ?>
                </h3>
                <div style="display: flex; gap: 8px;">
                    <button class="slos-btn-ghost" data-limit="10">10</button>
                    <button class="slos-btn-ghost" data-limit="25">25</button>
                    <button class="slos-btn-ghost" data-limit="50">50</button>
                </div>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Latest consent interactions from your visitors. Click the view button to see full consent details. Use the number buttons to adjust how many records appear. For full history, visit Consent Records tab.', 'shahi-legalops-suite' ); ?>
            </p>
            <div class="slos-card-body" style="padding: 0;">
                <?php if ( ! empty( $recent_activity ) ) : ?>
                    <table class="slos-data-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'ID', 'shahi-legalops-suite' ); ?></th>
                                <th><?php esc_html_e( 'User', 'shahi-legalops-suite' ); ?></th>
                                <th><?php esc_html_e( 'Type', 'shahi-legalops-suite' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'shahi-legalops-suite' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'shahi-legalops-suite' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'shahi-legalops-suite' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_activity as $consent ) : 
                                $consent_arr = (array) $consent;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html( $consent_arr['id'] ?? 0 ); ?></strong></td>
                                    <td>
                                        <?php 
                                        $user_id = $consent_arr['user_id'] ?? 0;
                                        if ( $user_id > 0 ) {
                                            $user = get_userdata( $user_id );
                                            echo esc_html( $user ? $user->display_name : "User #$user_id" );
                                        } else {
                                            echo '<span style="color: var(--slos-text-muted);">' . esc_html__( 'Guest', 'shahi-legalops-suite' ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="slos-type-badge"><?php echo esc_html( ucfirst( $consent_arr['type'] ?? 'unknown' ) ); ?></span></td>
                                    <td>
                                        <span class="slos-status-badge <?php echo esc_attr( $consent_arr['status'] ?? '' ); ?>">
                                            <?php echo esc_html( ucfirst( $consent_arr['status'] ?? 'unknown' ) ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $date = $consent_arr['created_at'] ?? '';
                                        echo $date ? esc_html( human_time_diff( strtotime( $date ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalops-suite' ) ) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <button class="slos-btn-ghost view-consent-btn" data-action="view" data-id="<?php echo esc_attr( $consent_arr['id'] ?? 0 ); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="slos-empty-state">
                        <span class="dashicons dashicons-privacy"></span>
                        <p><?php esc_html_e( 'No consent activity recorded yet. Data will appear as users interact with your consent banner.', 'shahi-legalops-suite' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $recent_activity ) ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'records', admin_url( 'admin.php?page=slos-compliance' ) ) ); ?>" class="slos-view-all">
                    <?php esc_html_e( 'View All Records', 'shahi-legalops-suite' ); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="slos-sidebar-column">
        <!-- Quick Actions -->
        <div class="slos-card">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-superhero"></span>
                    <?php esc_html_e( 'Quick Actions', 'shahi-legalops-suite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Common compliance tasks at your fingertips. Scan for cookies, customize your consent banner, export data for audits, or email reports to stakeholders.', 'shahi-legalops-suite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-quick-actions">
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'cookies', admin_url( 'admin.php?page=slos-compliance' ) ) ); ?>" class="slos-quick-action">
                        <span class="dashicons dashicons-search"></span>
                        <span><?php esc_html_e( 'Scan Cookies', 'shahi-legalops-suite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </a>
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'banner', admin_url( 'admin.php?page=slos-compliance' ) ) ); ?>" class="slos-quick-action">
                        <span class="dashicons dashicons-admin-customizer"></span>
                        <span><?php esc_html_e( 'Customize Banner', 'shahi-legalops-suite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </a>
                    <button class="slos-quick-action" id="slos-export-csv" type="button">
                        <span class="dashicons dashicons-download"></span>
                        <span><?php esc_html_e( 'Export to CSV', 'shahi-legalops-suite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </button>
                    <button class="slos-quick-action" id="slos-send-report" type="button">
                        <span class="dashicons dashicons-email-alt"></span>
                        <span><?php esc_html_e( 'Email Report', 'shahi-legalops-suite' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2 arrow"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Consent Breakdown -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-chart-pie"></span>
                    <?php esc_html_e( 'Consent Breakdown', 'shahi-legalops-suite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Consent rates by category type. Necessary cookies are always 100% (required). Analytics, Marketing, and Preferences reflect actual user choices on your site.', 'shahi-legalops-suite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-consent-breakdown">
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Necessary', 'shahi-legalops-suite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill necessary" style="width: 100%;"></div>
                        </div>
                        <span class="slos-breakdown-value">100%</span>
                    </div>
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Analytics', 'shahi-legalops-suite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill analytics" style="width: <?php echo esc_attr( $type_percentages['analytics'] ?? 0 ); ?>%;"></div>
                        </div>
                        <span class="slos-breakdown-value"><?php echo esc_html( $type_percentages['analytics'] ?? 0 ); ?>%</span>
                    </div>
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Marketing', 'shahi-legalops-suite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill marketing" style="width: <?php echo esc_attr( $type_percentages['marketing'] ?? 0 ); ?>%;"></div>
                        </div>
                        <span class="slos-breakdown-value"><?php echo esc_html( $type_percentages['marketing'] ?? 0 ); ?>%</span>
                    </div>
                    <div class="slos-breakdown-item">
                        <span class="slos-breakdown-label"><?php esc_html_e( 'Preferences', 'shahi-legalops-suite' ); ?></span>
                        <div class="slos-breakdown-bar">
                            <div class="slos-breakdown-fill preferences" style="width: <?php echo esc_attr( $type_percentages['preferences'] ?? 0 ); ?>%;"></div>
                        </div>
                        <span class="slos-breakdown-value"><?php echo esc_html( $type_percentages['preferences'] ?? 0 ); ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Geo Rules -->
        <?php
        // Get geo rules from database
        $geo_rules = get_option( 'slos_geo_rules', array() );
        $active_rules = array_filter( $geo_rules, function( $rule ) {
            // Support both 'active' field and 'status' field
            $is_active = ! empty( $rule['active'] ) || ( isset( $rule['status'] ) && $rule['status'] === 'active' );
            return $is_active;
        });
        
        // Flag mapping for regions
        $region_flags = array(
            'EU' => '🇪🇺', 'DE' => '🇩🇪', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'ES' => '🇪🇸', 'NL' => '🇳🇱', 'BE' => '🇧🇪', 'AT' => '🇦🇹', 'PL' => '🇵🇱', 'SE' => '🇸🇪',
            'US' => '🇺🇸', 'US-CA' => '🇺🇸', 'CA' => '🇨🇦', 'BR' => '🇧🇷', 'UK' => '🇬🇧', 'GB' => '🇬🇧', 'AU' => '🇦🇺', 'JP' => '🇯🇵', 'KR' => '🇰🇷', 'IN' => '🇮🇳', 'CN' => '🇨🇳',
            'GLOBAL' => '🌍', 'DEFAULT' => '🌍', 'REST' => '🌍'
        );
        ?>
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-admin-site-alt3"></span>
                    <?php esc_html_e( 'Active Geo Rules', 'shahi-legalops-suite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Region-specific consent rules currently active. Configure rules in the Geo Rules tab to show appropriate consent banners based on visitor location.', 'shahi-legalops-suite' ); ?>
            </p>
            <div class="slos-card-body">
                <?php if ( ! empty( $active_rules ) ) : ?>
                <div class="slos-region-list">
                    <?php foreach ( array_slice( $active_rules, 0, 4 ) as $rule ) : 
                        $rule_name = $rule['name'] ?? $rule['region'] ?? 'Unknown';
                        $framework = $rule['framework'] ?? $rule['regulation'] ?? '';
                        // Determine flag from countries or name
                        $countries = $rule['countries'] ?? array();
                        $first_country = ! empty( $countries ) ? $countries[0] : strtoupper( substr( $rule_name, 0, 2 ) );
                        $flag = $region_flags[ $first_country ] ?? $region_flags['GLOBAL'];
                        if ( stripos( $rule_name, 'EU' ) !== false || stripos( $rule_name, 'Europe' ) !== false ) {
                            $flag = '🇪🇺';
                        } elseif ( stripos( $rule_name, 'California' ) !== false ) {
                            $flag = '🇺🇸';
                        } elseif ( stripos( $rule_name, 'Brazil' ) !== false ) {
                            $flag = '🇧🇷';
                        } elseif ( stripos( $rule_name, 'Rest' ) !== false || stripos( $rule_name, 'World' ) !== false || stripos( $rule_name, 'Default' ) !== false ) {
                            $flag = '🌍';
                        }
                    ?>
                    <div class="slos-region-item">
                        <span class="slos-region-flag"><?php echo esc_html( $flag ); ?></span>
                        <div class="slos-region-info">
                            <div class="slos-region-name"><?php echo esc_html( $rule_name ); ?></div>
                            <div class="slos-region-framework"><?php echo esc_html( $framework ); ?></div>
                        </div>
                        <span class="slos-region-percent" style="color: var(--slos-success);"><span class="dashicons dashicons-yes-alt"></span></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="slos-empty-state" style="padding: 20px; text-align: center;">
                    <span class="dashicons dashicons-admin-site-alt3" style="font-size: 32px; color: var(--slos-text-muted);"></span>
                    <p style="color: var(--slos-text-muted); margin-top: 8px;"><?php esc_html_e( 'No geo rules configured. Visit the Geo Rules tab to set up regional compliance.', 'shahi-legalops-suite' ); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alerts -->
        <div class="slos-card" style="margin-top: 24px;">
            <div class="slos-card-header">
                <h3>
                    <span class="dashicons dashicons-bell"></span>
                    <?php esc_html_e( 'Alerts', 'shahi-legalops-suite' ); ?>
                </h3>
            </div>
            <p class="slos-widget-description">
                <?php esc_html_e( 'Compliance warnings requiring attention. Address alerts promptly to maintain full regulatory compliance. Yellow = action recommended, Red = urgent action required.', 'shahi-legalops-suite' ); ?>
            </p>
            <div class="slos-card-body">
                <div class="slos-alerts-list">
                    <?php if ( $stats['withdrawn'] > 0 ) : ?>
                        <div class="slos-alert-item warning">
                            <span class="dashicons dashicons-warning slos-alert-icon"></span>
                            <span class="slos-alert-text">
                                <?php 
                                printf( 
                                    esc_html__( '%d withdrawn consents require follow-up action.', 'shahi-legalops-suite' ),
                                    $stats['withdrawn']
                                );
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Check for uncategorized cookies from actual data
                    $detected_cookies = get_option( 'slos_detected_cookies', array() );
                    $uncategorized = array_filter( $detected_cookies, function( $cookie ) {
                        $category = $cookie['category'] ?? $cookie['type'] ?? '';
                        return empty( $category ) || $category === 'unknown' || $category === 'uncategorized';
                    });
                    $uncategorized_count = count( $uncategorized );
                    if ( $uncategorized_count > 0 ) : ?>
                    <div class="slos-alert-item info">
                        <span class="dashicons dashicons-info slos-alert-icon"></span>
                        <span class="slos-alert-text">
                            <?php 
                            printf(
                                esc_html__( 'Cookie scanner detected %d cookies that need categorization.', 'shahi-legalops-suite' ),
                                $uncategorized_count
                            );
                            ?>
                        </span>
                    </div>
                    <?php elseif ( empty( $detected_cookies ) ) : ?>
                    <div class="slos-alert-item info">
                        <span class="dashicons dashicons-info slos-alert-icon"></span>
                        <span class="slos-alert-text">
                            <?php esc_html_e( 'No cookies scanned yet. Run a cookie scan to detect cookies on your site.', 'shahi-legalops-suite' ); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ( $stats['compliance_score'] < 70 ) : ?>
                        <div class="slos-alert-item error">
                            <span class="dashicons dashicons-dismiss slos-alert-icon"></span>
                            <span class="slos-alert-text">
                                <?php esc_html_e( 'Compliance score is below threshold. Review your consent settings.', 'shahi-legalops-suite' ); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Consent Detail Modal -->
<div class="slos-modal-overlay" id="consent-detail-modal" style="display: none;">
    <div class="slos-modal-content" style="background: var(--slos-bg-card); border: 1px solid var(--slos-border); border-radius: 16px; width: 100%; max-width: 500px; padding: 24px;">
        <div class="slos-modal-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: var(--slos-text-primary);"><?php esc_html_e( 'Consent Details', 'shahi-legalops-suite' ); ?></h3>
            <button class="slos-modal-close" id="close-consent-modal" type="button" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: transparent; border: 1px solid var(--slos-border); border-radius: 8px; color: var(--slos-text-muted); cursor: pointer;">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="slos-modal-body" id="consent-detail-content">
            <p style="color: var(--slos-text-muted);"><?php esc_html_e( 'Loading...', 'shahi-legalops-suite' ); ?></p>
        </div>
    </div>
</div>

<style>
.slos-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    
    // Activity limit buttons
    $('.slos-btn-ghost[data-limit]').on('click', function() {
        const limit = $(this).data('limit');
        window.location.href = '<?php echo esc_js( admin_url( 'admin.php?page=slos-compliance&tab=dashboard' ) ); ?>&limit=' + limit;
    });
    
    // View consent detail
    $('.view-consent-btn').on('click', function() {
        const id = $(this).data('id');
        showConsentDetail(id);
    });
    
    function showConsentDetail(id) {
        $('#consent-detail-content').html('<p style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Loading...', 'shahi-legalops-suite' ) ); ?></p>');
        $('#consent-detail-modal').show();
        
        $.ajax({
            url: API_BASE + '/consents/' + id,
            method: 'GET',
            headers: { 'X-WP-Nonce': NONCE },
            success: function(response) {
                const data = response.data || response;
                let html = '<div style="display: grid; gap: 12px;">';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;">ID</div><div style="color: var(--slos-text-primary);">#' + (data.id || id) + '</div></div>';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;"><?php echo esc_js( __( 'Type', 'shahi-legalops-suite' ) ); ?></div><div style="color: var(--slos-text-primary);">' + (data.type || 'N/A') + '</div></div>';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;"><?php echo esc_js( __( 'Status', 'shahi-legalops-suite' ) ); ?></div><div style="color: var(--slos-text-primary);">' + (data.status || 'N/A') + '</div></div>';
                html += '<div style="padding: 12px; background: var(--slos-bg-input); border-radius: 8px;"><div style="font-size: 11px; color: var(--slos-text-muted); text-transform: uppercase;"><?php echo esc_js( __( 'Created', 'shahi-legalops-suite' ) ); ?></div><div style="color: var(--slos-text-primary);">' + (data.created_at || 'N/A') + '</div></div>';
                html += '</div>';
                $('#consent-detail-content').html(html);
            },
            error: function() {
                $('#consent-detail-content').html('<p style="color: var(--slos-error);"><?php echo esc_js( __( 'Error loading consent details.', 'shahi-legalops-suite' ) ); ?></p>');
            }
        });
    }
    
    // Close modal
    $('#close-consent-modal, #consent-detail-modal').on('click', function(e) {
        if (e.target === this) {
            $('#consent-detail-modal').hide();
        }
    });
    
    // Export to CSV
    $('#slos-export-csv').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).find('span:first').removeClass('dashicons-download').addClass('dashicons-update');
        
        // Trigger download
        window.location.href = API_BASE + '/consents/export/download?format=csv&_wpnonce=' + NONCE;
        
        setTimeout(function() {
            $btn.prop('disabled', false).find('span:first').removeClass('dashicons-update').addClass('dashicons-download');
        }, 2000);
    });
    
    // Email Report
    $('#slos-send-report').on('click', function() {
        const email = prompt('<?php echo esc_js( __( 'Enter email address for the report:', 'shahi-legalops-suite' ) ); ?>', '<?php echo esc_js( get_option( 'admin_email' ) ); ?>');
        
        if (email) {
            const $btn = $(this);
            $btn.prop('disabled', true);
            
            $.ajax({
                url: API_BASE + '/consents/export',
                method: 'GET',
                data: { format: 'email', email: email },
                headers: { 'X-WP-Nonce': NONCE },
                success: function() {
                    alert('<?php echo esc_js( __( 'Report sent successfully!', 'shahi-legalops-suite' ) ); ?>');
                },
                error: function() {
                    alert('<?php echo esc_js( __( 'Error sending report. Please try again.', 'shahi-legalops-suite' ) ); ?>');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }
    });
});
</script>