<?php
/**
 * Geo Rules Tab - V3 Design
 *
 * Region-specific consent rules management.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin/Compliance
 * @since      3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load geo rules from database - only show real saved rules, no auto-initialization
$geo_rules = get_option( 'slos_geo_rules', array() );

// Convert to indexed array for template iteration
$geo_rules = array_values( $geo_rules );

// Comprehensive list of countries with ISO codes - organized by region
$available_countries = array(
	// Region Groups (for quick selection)
	'EU-ALL'  => '🇪🇺 All EU Countries',
	'EEA-ALL' => '🌍 All EEA Countries',
	
	// Europe - EU Members
	'AT' => 'Austria',
	'BE' => 'Belgium',
	'BG' => 'Bulgaria',
	'HR' => 'Croatia',
	'CY' => 'Cyprus',
	'CZ' => 'Czech Republic',
	'DK' => 'Denmark',
	'EE' => 'Estonia',
	'FI' => 'Finland',
	'FR' => 'France',
	'DE' => 'Germany',
	'GR' => 'Greece',
	'HU' => 'Hungary',
	'IE' => 'Ireland',
	'IT' => 'Italy',
	'LV' => 'Latvia',
	'LT' => 'Lithuania',
	'LU' => 'Luxembourg',
	'MT' => 'Malta',
	'NL' => 'Netherlands',
	'PL' => 'Poland',
	'PT' => 'Portugal',
	'RO' => 'Romania',
	'SK' => 'Slovakia',
	'SI' => 'Slovenia',
	'ES' => 'Spain',
	'SE' => 'Sweden',
	
	// Europe - Non-EU
	'GB' => 'United Kingdom',
	'CH' => 'Switzerland',
	'NO' => 'Norway',
	'IS' => 'Iceland',
	'LI' => 'Liechtenstein',
	'AD' => 'Andorra',
	'MC' => 'Monaco',
	'SM' => 'San Marino',
	'VA' => 'Vatican City',
	'AL' => 'Albania',
	'BA' => 'Bosnia and Herzegovina',
	'ME' => 'Montenegro',
	'MK' => 'North Macedonia',
	'RS' => 'Serbia',
	'UA' => 'Ukraine',
	'MD' => 'Moldova',
	'BY' => 'Belarus',
	'RU' => 'Russia',
	'TR' => 'Turkey',
	
	// Americas - North
	'US'    => 'United States (Federal)',
	'US-CA' => '  └ California',
	'US-VA' => '  └ Virginia',
	'US-CO' => '  └ Colorado',
	'US-CT' => '  └ Connecticut',
	'US-UT' => '  └ Utah',
	'US-TX' => '  └ Texas',
	'US-FL' => '  └ Florida',
	'US-NY' => '  └ New York',
	'US-OR' => '  └ Oregon',
	'US-MT' => '  └ Montana',
	'US-IA' => '  └ Iowa',
	'US-DE' => '  └ Delaware',
	'US-TN' => '  └ Tennessee',
	'CA'    => 'Canada',
	'CA-QC' => '  └ Quebec',
	'CA-BC' => '  └ British Columbia',
	'CA-AB' => '  └ Alberta',
	'MX'    => 'Mexico',
	
	// Americas - Central & Caribbean
	'CR' => 'Costa Rica',
	'PA' => 'Panama',
	'GT' => 'Guatemala',
	'PR' => 'Puerto Rico',
	'JM' => 'Jamaica',
	'DO' => 'Dominican Republic',
	'CU' => 'Cuba',
	'TT' => 'Trinidad and Tobago',
	
	// Americas - South
	'BR' => 'Brazil',
	'AR' => 'Argentina',
	'CL' => 'Chile',
	'CO' => 'Colombia',
	'PE' => 'Peru',
	'VE' => 'Venezuela',
	'EC' => 'Ecuador',
	'UY' => 'Uruguay',
	'PY' => 'Paraguay',
	'BO' => 'Bolivia',
	
	// Asia - East
	'CN' => 'China',
	'JP' => 'Japan',
	'KR' => 'South Korea',
	'KP' => 'North Korea',
	'HK' => 'Hong Kong',
	'MO' => 'Macau',
	'TW' => 'Taiwan',
	'MN' => 'Mongolia',
	
	// Asia - Southeast
	'SG' => 'Singapore',
	'MY' => 'Malaysia',
	'TH' => 'Thailand',
	'ID' => 'Indonesia',
	'PH' => 'Philippines',
	'VN' => 'Vietnam',
	'MM' => 'Myanmar',
	'KH' => 'Cambodia',
	'LA' => 'Laos',
	'BN' => 'Brunei',
	
	// Asia - South
	'IN' => 'India',
	'PK' => 'Pakistan',
	'BD' => 'Bangladesh',
	'LK' => 'Sri Lanka',
	'NP' => 'Nepal',
	'BT' => 'Bhutan',
	'MV' => 'Maldives',
	
	// Asia - Central
	'KZ' => 'Kazakhstan',
	'UZ' => 'Uzbekistan',
	'AF' => 'Afghanistan',
	'TM' => 'Turkmenistan',
	'KG' => 'Kyrgyzstan',
	'TJ' => 'Tajikistan',
	
	// Middle East
	'AE' => 'United Arab Emirates',
	'SA' => 'Saudi Arabia',
	'IL' => 'Israel',
	'PS' => 'Palestine',
	'JO' => 'Jordan',
	'LB' => 'Lebanon',
	'SY' => 'Syria',
	'IQ' => 'Iraq',
	'IR' => 'Iran',
	'KW' => 'Kuwait',
	'QA' => 'Qatar',
	'BH' => 'Bahrain',
	'OM' => 'Oman',
	'YE' => 'Yemen',
	
	// Africa - North
	'EG' => 'Egypt',
	'MA' => 'Morocco',
	'DZ' => 'Algeria',
	'TN' => 'Tunisia',
	'LY' => 'Libya',
	'SD' => 'Sudan',
	
	// Africa - West
	'NG' => 'Nigeria',
	'GH' => 'Ghana',
	'CI' => 'Ivory Coast',
	'SN' => 'Senegal',
	'CM' => 'Cameroon',
	'ML' => 'Mali',
	'NE' => 'Niger',
	'BF' => 'Burkina Faso',
	
	// Africa - East
	'KE' => 'Kenya',
	'ET' => 'Ethiopia',
	'TZ' => 'Tanzania',
	'UG' => 'Uganda',
	'RW' => 'Rwanda',
	'MU' => 'Mauritius',
	
	// Africa - South
	'ZA' => 'South Africa',
	'ZW' => 'Zimbabwe',
	'BW' => 'Botswana',
	'NA' => 'Namibia',
	'MZ' => 'Mozambique',
	'AO' => 'Angola',
	'ZM' => 'Zambia',
	
	// Oceania
	'AU' => 'Australia',
	'NZ' => 'New Zealand',
	'FJ' => 'Fiji',
	'PG' => 'Papua New Guinea',
	'NC' => 'New Caledonia',
	'WS' => 'Samoa',
);

// EU country codes for region group selection
$eu_countries = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE');
$eea_countries = array_merge($eu_countries, array('IS', 'LI', 'NO'));
?>

<style>
/* Geo Rules specific styles */
.slos-geo-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.slos-geo-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.slos-geo-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 8px;
    font-size: 13px;
    color: var(--slos-accent);
}

/* Rule Cards */
.slos-rules-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.slos-rule-card {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    padding: 24px;
    transition: all 0.2s;
}

.slos-rule-card:hover {
    border-color: var(--slos-accent);
}

.slos-rule-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 16px;
}

.slos-rule-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.slos-rule-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.slos-rule-icon.gdpr {
    background: rgba(59, 130, 246, 0.15);
    color: var(--slos-accent);
}

.slos-rule-icon.ccpa {
    background: rgba(245, 158, 11, 0.15);
    color: var(--slos-warning);
}

.slos-rule-icon.lgpd {
    background: rgba(34, 197, 94, 0.15);
    color: var(--slos-success);
}

.slos-rule-icon.default {
    background: rgba(147, 197, 253, 0.15);
    color: var(--slos-accent-light, #93c5fd);
}

.slos-rule-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--slos-text-primary);
}

.slos-rule-regulation {
    font-size: 12px;
    color: var(--slos-text-muted);
    margin-top: 2px;
}

.slos-rule-menu {
    position: relative;
}

.slos-menu-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: var(--slos-text-muted);
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.15s;
}

.slos-menu-btn:hover {
    background: var(--slos-bg-input);
    color: var(--slos-text-primary);
}

/* Rule Settings */
.slos-rule-settings {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.slos-rule-setting {
    padding: 12px;
    background: var(--slos-bg-input);
    border-radius: 8px;
}

.slos-setting-label {
    font-size: 11px;
    color: var(--slos-text-muted);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.slos-setting-value {
    font-size: 13px;
    color: var(--slos-text-primary);
    font-weight: 500;
}

.slos-setting-value.opt-in {
    color: var(--slos-success);
}

.slos-setting-value.opt-out {
    color: var(--slos-warning);
}

/* Country Tags */
.slos-country-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}

.slos-country-tag {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    font-size: 11px;
    color: var(--slos-text-secondary);
}

.slos-country-tag.more {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.2);
    color: var(--slos-accent);
    cursor: pointer;
}

/* Rule Actions */
.slos-rule-actions {
    display: flex;
    gap: 8px;
    padding-top: 16px;
    border-top: 1px solid var(--slos-border);
}

.slos-rule-btn {
    flex: 1;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    font-size: 13px;
    color: var(--slos-text-secondary);
    cursor: pointer;
    transition: all 0.15s;
}

.slos-rule-btn:hover {
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

/* Status Toggle */
.slos-status-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slos-status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--slos-success);
}

.slos-status-indicator.inactive {
    background: var(--slos-text-muted);
}

/* Add Rule Modal */
.slos-modal-overlay {
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

.slos-modal-overlay.active {
    display: flex;
}

.slos-rule-modal {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 16px;
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.slos-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--slos-border);
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
    padding: 24px;
}

.slos-form-group {
    margin-bottom: 20px;
}

.slos-form-group:last-child {
    margin-bottom: 0;
}

.slos-form-label {
    display: block;
    font-size: 12px;
    color: var(--slos-text-secondary);
    margin-bottom: 8px;
    font-weight: 500;
}

.slos-form-input {
    width: 100%;
    padding: 12px 14px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    color: var(--slos-text-primary);
    font-size: 14px;
}

.slos-form-input:focus {
    outline: none;
    border-color: var(--slos-accent);
}

.slos-form-select {
    width: 100%;
    padding: 12px 14px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    color: var(--slos-text-primary);
    font-size: 14px;
    cursor: pointer;
}

/* Country Multi-select */
.slos-country-select {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
    background: var(--slos-bg-input);
    border: 1px solid var(--slos-border);
    border-radius: 8px;
    max-height: 200px;
    overflow-y: auto;
}

.slos-country-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 6px;
    font-size: 13px;
    color: var(--slos-text-secondary);
    cursor: pointer;
    transition: all 0.15s;
}

.slos-country-option:hover {
    border-color: var(--slos-accent);
}

.slos-country-option.selected {
    background: rgba(59, 130, 246, 0.1);
    border-color: var(--slos-accent);
    color: var(--slos-accent);
}

.slos-country-option input {
    display: none;
}

.slos-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid var(--slos-border);
}

/* Quick Stats */
.slos-geo-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.slos-geo-stat {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 10px;
    padding: 16px 20px;
}

.slos-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--slos-text-primary);
    margin-bottom: 4px;
}

.slos-stat-label {
    font-size: 12px;
    color: var(--slos-text-muted);
}

/* Map placeholder */
.slos-map-preview {
    background: var(--slos-bg-card);
    border: 1px solid var(--slos-border);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    text-align: center;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.slos-map-placeholder {
    color: var(--slos-text-muted);
}

.slos-map-placeholder .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 12px;
    color: var(--slos-text-muted);
}
</style>

<div class="slos-geo-container">
    <!-- Header -->
    <div class="slos-geo-header">
        <div class="slos-geo-info">
            <h2 style="margin: 0; font-size: 18px; color: var(--slos-text-primary);">
                <?php esc_html_e( 'Geographic Consent Rules', 'shahi-legalops-suite' ); ?>
            </h2>
            <div class="slos-geo-badge">
                <span class="dashicons dashicons-admin-site-alt3"></span>
                <?php echo count( $geo_rules ); ?> <?php esc_html_e( 'Rules', 'shahi-legalops-suite' ); ?>
            </div>
        </div>
        <button class="slos-btn slos-btn-primary" id="add-rule">
            <span class="dashicons dashicons-plus-alt2"></span>
            <?php esc_html_e( 'Add Rule', 'shahi-legalops-suite' ); ?>
        </button>
    </div>
    
    <p class="slos-section-description">
        <?php esc_html_e( 'Define region-specific consent requirements to comply with global privacy laws. Rules automatically detect visitor location and enforce appropriate consent mode (opt-in for GDPR/strict, opt-out for permissive). Configure frameworks like GDPR (EU), CCPA (California), LGPD (Brazil), and custom rules. Each rule can target countries or US states.', 'shahi-legalops-suite' ); ?>
    </p>

    <?php if ( empty( $geo_rules ) ) : ?>
    <!-- Empty State - No Rules Configured -->
    <div class="slos-card" style="margin-top: 24px; padding: 60px 40px; text-align: center;">
        <div style="max-width: 500px; margin: 0 auto;">
            <span class="dashicons dashicons-admin-site-alt3" style="font-size: 64px; width: 64px; height: 64px; color: var(--slos-text-muted); margin-bottom: 20px;"></span>
            <h3 style="margin: 0 0 12px 0; color: var(--slos-text-primary); font-size: 20px;">
                <?php esc_html_e( 'No Geographic Rules Configured', 'shahi-legalops-suite' ); ?>
            </h3>
            <p style="margin: 0 0 24px 0; color: var(--slos-text-muted); line-height: 1.6;">
                <?php esc_html_e( 'Create geographic consent rules to comply with regional privacy regulations like GDPR (EU), CCPA (California), LGPD (Brazil), and more. Each rule defines how consent is handled for visitors from specific regions.', 'shahi-legalops-suite' ); ?>
            </p>
            <button class="slos-btn slos-btn-primary" id="add-first-rule" style="padding: 12px 24px;">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e( 'Create Your First Rule', 'shahi-legalops-suite' ); ?>
            </button>
        </div>
    </div>
    <?php else : ?>

    <?php
    // Calculate statistics from actual rules
    $total_countries = 0;
    $regulations = array();
    $opt_in_regions = 0;
    $opt_out_regions = 0;
    
    foreach ( $geo_rules as $rule ) {
        $total_countries += count( $rule['countries'] ?? array() );
        $reg = $rule['regulation'] ?? $rule['framework'] ?? 'Default';
        if ( ! in_array( $reg, $regulations, true ) && $reg !== 'Default' ) {
            $regulations[] = $reg;
        }
        $consent_mode = $rule['default_consent'] ?? $rule['consent_mode'] ?? 'opt-out';
        if ( $consent_mode === 'opt-in' ) {
            $opt_in_regions++;
        } else {
            $opt_out_regions++;
        }
    }
    ?>
    
    <!-- Quick Stats -->
    <div class="slos-geo-stats">
        <div class="slos-geo-stat">
            <div class="slos-stat-value"><?php echo esc_html( $total_countries ); ?></div>
            <div class="slos-stat-label"><?php esc_html_e( 'Countries Covered', 'shahi-legalops-suite' ); ?></div>
        </div>
        <div class="slos-geo-stat">
            <div class="slos-stat-value"><?php echo esc_html( count( $regulations ) ); ?></div>
            <div class="slos-stat-label"><?php esc_html_e( 'Regulations', 'shahi-legalops-suite' ); ?></div>
        </div>
        <div class="slos-geo-stat">
            <div class="slos-stat-value"><?php echo esc_html( $opt_in_regions ); ?></div>
            <div class="slos-stat-label"><?php esc_html_e( 'Opt-In Regions', 'shahi-legalops-suite' ); ?></div>
        </div>
        <div class="slos-geo-stat">
            <div class="slos-stat-value"><?php echo esc_html( $opt_out_regions ); ?></div>
            <div class="slos-stat-label"><?php esc_html_e( 'Opt-Out Regions', 'shahi-legalops-suite' ); ?></div>
        </div>
    </div>

    <!-- World Map Placeholder -->
    <div class="slos-map-preview">
        <div class="slos-map-placeholder">
            <span class="dashicons dashicons-location-alt"></span>
            <p><?php esc_html_e( 'Interactive map visualization coming soon', 'shahi-legalops-suite' ); ?></p>
            <small><?php esc_html_e( 'Visual overview of your consent rules by region', 'shahi-legalops-suite' ); ?></small>
        </div>
    </div>

    <!-- Rules Grid -->
    <div class="slos-rules-grid">
        <?php foreach ( $geo_rules as $rule ) : 
            // Support both field naming conventions
            $regulation = $rule['regulation'] ?? $rule['framework'] ?? 'Default';
            $consent_mode = $rule['default_consent'] ?? $rule['consent_mode'] ?? 'opt-out';
            $region_name = $rule['region'] ?? $rule['name'] ?? '';
            $is_active = ( $rule['status'] ?? 'active' ) === 'active' || ( $rule['active'] ?? true );
            
            $icon_class = 'default';
            if ( $regulation === 'GDPR' ) $icon_class = 'gdpr';
            elseif ( $regulation === 'CCPA/CPRA' || $regulation === 'ccpa' ) $icon_class = 'ccpa';
            elseif ( $regulation === 'LGPD' || $regulation === 'lgpd' ) $icon_class = 'lgpd';
        ?>
        <div class="slos-rule-card" data-id="<?php echo esc_attr( $rule['id'] ); ?>">
            <div class="slos-rule-header">
                <div class="slos-rule-title">
                    <div class="slos-rule-icon <?php echo esc_attr( $icon_class ); ?>">
                        <span class="dashicons dashicons-admin-site"></span>
                    </div>
                    <div>
                        <div class="slos-rule-name"><?php echo esc_html( $region_name ); ?></div>
                        <div class="slos-rule-regulation"><?php echo esc_html( $regulation ); ?></div>
                    </div>
                </div>
                <div class="slos-status-toggle">
                    <span class="slos-status-indicator <?php echo $is_active ? '' : 'inactive'; ?>"></span>
                    <span style="font-size: 12px; color: var(--slos-text-muted);">
                        <?php echo $is_active ? esc_html__( 'Active', 'shahi-legalops-suite' ) : esc_html__( 'Inactive', 'shahi-legalops-suite' ); ?>
                    </span>
                </div>
            </div>

            <div class="slos-rule-settings">
                <div class="slos-rule-setting">
                    <div class="slos-setting-label"><?php esc_html_e( 'Default Consent', 'shahi-legalops-suite' ); ?></div>
                    <div class="slos-setting-value <?php echo esc_attr( $consent_mode ); ?>">
                        <?php echo $consent_mode === 'opt-in' ? esc_html__( 'Opt-In Required', 'shahi-legalops-suite' ) : esc_html__( 'Opt-Out Available', 'shahi-legalops-suite' ); ?>
                    </div>
                </div>
                <div class="slos-rule-setting">
                    <div class="slos-setting-label"><?php esc_html_e( 'Banner', 'shahi-legalops-suite' ); ?></div>
                    <div class="slos-setting-value">
                        <?php echo ( $rule['show_banner'] ?? true ) ? esc_html__( 'Shown', 'shahi-legalops-suite' ) : esc_html__( 'Hidden', 'shahi-legalops-suite' ); ?>
                    </div>
                </div>
            </div>

            <div class="slos-country-tags">
                <?php 
                $display_count = 3;
                $countries = $rule['countries'] ?? array();
                $total = count( $countries );
                
                if ( $total === 0 ) : ?>
                    <span class="slos-country-tag"><?php esc_html_e( 'All other countries', 'shahi-legalops-suite' ); ?></span>
                <?php else :
                    $shown = array_slice( $countries, 0, $display_count );
                    foreach ( $shown as $code ) : ?>
                        <span class="slos-country-tag"><?php echo esc_html( $code ); ?></span>
                    <?php endforeach;
                    
                    if ( $total > $display_count ) : ?>
                        <span class="slos-country-tag more">+<?php echo esc_html( $total - $display_count ); ?> <?php esc_html_e( 'more', 'shahi-legalops-suite' ); ?></span>
                    <?php endif;
                endif; ?>
            </div>

            <div class="slos-rule-actions">
                <button class="slos-rule-btn edit-rule" data-id="<?php echo esc_attr( $rule['id'] ); ?>">
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e( 'Edit', 'shahi-legalops-suite' ); ?>
                </button>
                <button class="slos-rule-btn duplicate-rule" data-id="<?php echo esc_attr( $rule['id'] ); ?>">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php esc_html_e( 'Duplicate', 'shahi-legalops-suite' ); ?>
                </button>
                <?php if ( $rule['regulation'] !== 'Default' ) : ?>
                <button class="slos-rule-btn delete-rule" data-id="<?php echo esc_attr( $rule['id'] ); ?>" style="color: var(--slos-error);">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Rule Modal -->
<div class="slos-modal-overlay" id="rule-modal">
    <div class="slos-rule-modal">
        <div class="slos-modal-header">
            <h3 class="slos-modal-title" id="modal-title"><?php esc_html_e( 'Add New Rule', 'shahi-legalops-suite' ); ?></h3>
            <button class="slos-modal-close" id="close-rule-modal">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="slos-modal-body">
            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Region Name', 'shahi-legalops-suite' ); ?></label>
                <input type="text" class="slos-form-input" id="rule-name" placeholder="<?php esc_attr_e( 'e.g., European Union, California, Brazil', 'shahi-legalops-suite' ); ?>">
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Regulation', 'shahi-legalops-suite' ); ?></label>
                <select class="slos-form-select" id="rule-regulation">
                    <option value="GDPR"><?php esc_html_e( 'GDPR (European Union)', 'shahi-legalops-suite' ); ?></option>
                    <option value="CCPA/CPRA"><?php esc_html_e( 'CCPA/CPRA (California)', 'shahi-legalops-suite' ); ?></option>
                    <option value="LGPD"><?php esc_html_e( 'LGPD (Brazil)', 'shahi-legalops-suite' ); ?></option>
                    <option value="POPIA"><?php esc_html_e( 'POPIA (South Africa)', 'shahi-legalops-suite' ); ?></option>
                    <option value="PDPA"><?php esc_html_e( 'PDPA (Singapore)', 'shahi-legalops-suite' ); ?></option>
                    <option value="Custom"><?php esc_html_e( 'Custom', 'shahi-legalops-suite' ); ?></option>
                </select>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Countries / Regions', 'shahi-legalops-suite' ); ?></label>
                <div class="slos-country-select">
                    <?php foreach ( $available_countries as $code => $name ) : ?>
                    <label class="slos-country-option" data-code="<?php echo esc_attr( $code ); ?>">
                        <input type="checkbox" name="countries[]" value="<?php echo esc_attr( $code ); ?>">
                        <span><?php echo esc_html( $code ); ?></span>
                        <span style="color: var(--slos-text-muted);"><?php echo esc_html( $name ); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Default Consent Mode', 'shahi-legalops-suite' ); ?></label>
                <select class="slos-form-select" id="rule-consent-mode">
                    <option value="opt-in"><?php esc_html_e( 'Opt-In Required (GDPR-style)', 'shahi-legalops-suite' ); ?></option>
                    <option value="opt-out"><?php esc_html_e( 'Opt-Out Available (CCPA-style)', 'shahi-legalops-suite' ); ?></option>
                </select>
            </div>

            <div class="slos-form-group">
                <label class="slos-form-label"><?php esc_html_e( 'Banner Behavior', 'shahi-legalops-suite' ); ?></label>
                <select class="slos-form-select" id="rule-banner">
                    <option value="show"><?php esc_html_e( 'Show consent banner', 'shahi-legalops-suite' ); ?></option>
                    <option value="hide"><?php esc_html_e( 'Hide consent banner', 'shahi-legalops-suite' ); ?></option>
                </select>
            </div>
        </div>
        <div class="slos-modal-footer">
            <button class="slos-btn slos-btn-secondary" id="cancel-rule">
                <?php esc_html_e( 'Cancel', 'shahi-legalops-suite' ); ?>
            </button>
            <button class="slos-btn slos-btn-primary" id="save-rule">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Rule', 'shahi-legalops-suite' ); ?>
            </button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const API_BASE = '<?php echo esc_js( rest_url( 'slos/v1' ) ); ?>';
    const NONCE = '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
    let editingRuleId = null;
    
    // Open add modal
    $('#add-rule, #add-first-rule').on('click', function() {
        editingRuleId = null;
        resetModal();
        $('#modal-title').text('<?php echo esc_js( __( 'Add New Rule', 'shahi-legalops-suite' ) ); ?>');
        $('#rule-modal').addClass('active');
    });

    // Close modal
    $('#close-rule-modal, #cancel-rule').on('click', function() {
        $('#rule-modal').removeClass('active');
    });

    // Click outside to close
    $('#rule-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });
    
    // ESC to close
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#rule-modal').hasClass('active')) {
            $('#rule-modal').removeClass('active');
        }
    });

    // Country selection - prevent default checkbox behavior and handle manually
    const euCountries = <?php echo wp_json_encode( $eu_countries ); ?>;
    const eeaCountries = <?php echo wp_json_encode( $eea_countries ); ?>;
    
    $(document).on('click', '.slos-country-option', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $option = $(this);
        const $checkbox = $option.find('input');
        const code = $option.data('code');
        const isSelected = $option.hasClass('selected');
        
        // Handle region groups (EU-ALL, EEA-ALL)
        if (code === 'EU-ALL') {
            if (isSelected) {
                // Deselect all EU countries
                euCountries.forEach(function(countryCode) {
                    const $countryOpt = $('.slos-country-option[data-code="' + countryCode + '"]');
                    $countryOpt.removeClass('selected');
                    $countryOpt.find('input').prop('checked', false);
                });
                $option.removeClass('selected');
                $checkbox.prop('checked', false);
            } else {
                // Select all EU countries
                euCountries.forEach(function(countryCode) {
                    const $countryOpt = $('.slos-country-option[data-code="' + countryCode + '"]');
                    $countryOpt.addClass('selected');
                    $countryOpt.find('input').prop('checked', true);
                });
                $option.addClass('selected');
                $checkbox.prop('checked', true);
            }
        } else if (code === 'EEA-ALL') {
            if (isSelected) {
                // Deselect all EEA countries
                eeaCountries.forEach(function(countryCode) {
                    const $countryOpt = $('.slos-country-option[data-code="' + countryCode + '"]');
                    $countryOpt.removeClass('selected');
                    $countryOpt.find('input').prop('checked', false);
                });
                $option.removeClass('selected');
                $checkbox.prop('checked', false);
            } else {
                // Select all EEA countries
                eeaCountries.forEach(function(countryCode) {
                    const $countryOpt = $('.slos-country-option[data-code="' + countryCode + '"]');
                    $countryOpt.addClass('selected');
                    $countryOpt.find('input').prop('checked', true);
                });
                $option.addClass('selected');
                $checkbox.prop('checked', true);
            }
        } else {
            // Standard country toggle
            if (isSelected) {
                $option.removeClass('selected');
                $checkbox.prop('checked', false);
            } else {
                $option.addClass('selected');
                $checkbox.prop('checked', true);
            }
        }
        
        updateSelectedCountries();
    });
    
    function updateSelectedCountries() {
        const count = $('.slos-country-option.selected').length;
        $('#selected-countries-count').text(count + ' <?php echo esc_js( __( 'selected', 'shahi-legalops-suite' ) ); ?>');
    }
    
    function resetModal() {
        $('#rule-name').val('');
        $('#rule-regulation').val('GDPR');
        $('#rule-consent-mode').val('opt-in');
        $('#rule-banner').val('show');
        $('.slos-country-option').removeClass('selected');
        $('.slos-country-option input').prop('checked', false);
        updateSelectedCountries();
    }
    
    function gatherRuleData() {
        const countries = [];
        $('.slos-country-option.selected').each(function() {
            const code = $(this).data('code');
            // Exclude group selectors from saved countries
            if (code !== 'EU-ALL' && code !== 'EEA-ALL') {
                countries.push(code);
            }
        });
        
        return {
            name: $('#rule-name').val().trim(),
            framework: $('#rule-regulation').val(),
            consent_mode: $('#rule-consent-mode').val(),
            countries: countries,
            show_banner: $('#rule-banner').val() === 'show',
            require_explicit: $('#rule-consent-mode').val() === 'opt-in',
            show_reject: true,
            record_proof: true,
            allow_withdraw: true
        };
    }
    
    function populateModal(rule) {
        $('#rule-name').val(rule.name || '');
        $('#rule-regulation').val(rule.framework || 'GDPR');
        $('#rule-consent-mode').val(rule.consent_mode || 'opt-in');
        $('#rule-banner').val(rule.show_banner ? 'show' : 'hide');
        
        // Clear and set countries
        $('.slos-country-option').removeClass('selected');
        $('.slos-country-option input').prop('checked', false);
        
        if (rule.countries && Array.isArray(rule.countries)) {
            rule.countries.forEach(function(code) {
                const $opt = $('.slos-country-option[data-code="' + code + '"]');
                $opt.addClass('selected');
                $opt.find('input').prop('checked', true);
            });
        }
        
        updateSelectedCountries();
    }

    // Edit rule
    $(document).on('click', '.edit-rule', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        editingRuleId = id;
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: API_BASE + '/geo/rules/' + id,
            method: 'GET',
            headers: { 'X-WP-Nonce': NONCE },
            success: function(response) {
                $btn.prop('disabled', false);
                const rule = response.data || response;
                resetModal();
                populateModal(rule);
                $('#modal-title').text('<?php echo esc_js( __( 'Edit Rule', 'shahi-legalops-suite' ) ); ?>');
                $('#rule-modal').addClass('active');
            },
            error: function() {
                $btn.prop('disabled', false);
                alert('<?php echo esc_js( __( 'Failed to load rule data.', 'shahi-legalops-suite' ) ); ?>');
            }
        });
    });

    // Duplicate rule
    $(document).on('click', '.duplicate-rule', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        
        if (!confirm('<?php echo esc_js( __( 'Create a copy of this rule?', 'shahi-legalops-suite' ) ); ?>')) {
            return;
        }
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: API_BASE + '/geo/rules/' + id + '/duplicate',
            method: 'POST',
            headers: { 'X-WP-Nonce': NONCE },
            success: function(response) {
                location.reload();
            },
            error: function() {
                $btn.prop('disabled', false);
                alert('<?php echo esc_js( __( 'Failed to duplicate rule.', 'shahi-legalops-suite' ) ); ?>');
            }
        });
    });

    // Delete rule
    $(document).on('click', '.delete-rule', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const $row = $btn.closest('.slos-rule-card');
        const ruleName = $row.find('.slos-rule-title').text();
        
        if (!confirm('<?php echo esc_js( __( 'Are you sure you want to delete this rule?', 'shahi-legalops-suite' ) ); ?>\n\n' + ruleName)) {
            return;
        }
        
        $btn.prop('disabled', true);
        $row.css('opacity', '0.5');
        
        $.ajax({
            url: API_BASE + '/geo/rules/' + id,
            method: 'DELETE',
            headers: { 'X-WP-Nonce': NONCE },
            success: function() {
                $row.slideUp(300, function() {
                    $row.remove();
                    updateRuleCount();
                });
            },
            error: function() {
                $btn.prop('disabled', false);
                $row.css('opacity', '1');
                alert('<?php echo esc_js( __( 'Failed to delete rule.', 'shahi-legalops-suite' ) ); ?>');
            }
        });
    });
    
    function updateRuleCount() {
        const count = $('.slos-rule-card').length;
        $('#total-rules-count').text(count);
    }

    // Save rule
    $('#save-rule').on('click', function() {
        const $btn = $(this);
        const data = gatherRuleData();
        
        // Validation
        if (!data.name.trim()) {
            alert('<?php echo esc_js( __( 'Please enter a rule name.', 'shahi-legalops-suite' ) ); ?>');
            $('#rule-name').focus();
            return;
        }
        
        if (data.countries.length === 0) {
            alert('<?php echo esc_js( __( 'Please select at least one country.', 'shahi-legalops-suite' ) ); ?>');
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php echo esc_js( __( 'Saving...', 'shahi-legalops-suite' ) ); ?>');
        
        const url = editingRuleId ? API_BASE + '/geo/rules/' + editingRuleId : API_BASE + '/geo/rules';
        const method = editingRuleId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            headers: { 
                'X-WP-Nonce': NONCE,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: function(response) {
                $('#rule-modal').removeClass('active');
                location.reload();
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> <?php echo esc_js( __( 'Save Rule', 'shahi-legalops-suite' ) ); ?>');
                const errMsg = xhr.responseJSON?.message || '<?php echo esc_js( __( 'Failed to save rule.', 'shahi-legalops-suite' ) ); ?>';
                alert(errMsg);
            }
        });
    });
    
    // Toggle rule active state
    $(document).on('click', '.toggle-rule', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const $card = $btn.closest('.slos-rule-card');
        const isActive = $card.hasClass('active');
        
        $.ajax({
            url: API_BASE + '/geo/rules/' + id + '/toggle',
            method: 'POST',
            headers: { 'X-WP-Nonce': NONCE },
            data: { active: !isActive },
            success: function() {
                $card.toggleClass('active');
                $btn.find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
            },
            error: function() {
                alert('<?php echo esc_js( __( 'Failed to toggle rule status.', 'shahi-legalops-suite' ) ); ?>');
            }
        });
    });
    
    // Modal toggle switches
    $(document).on('click', '.slos-modal-toggle', function() {
        $(this).toggleClass('active');
    });
});
</script>
