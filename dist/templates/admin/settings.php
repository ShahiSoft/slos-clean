<?php
/**
 * Settings Page Template
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalflowsuite-admin shahi-settings-page">
    <style>
        /* Settings Page - Glowing White Header */
        .shahi-settings-page .shahi-page-title {
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

        .shahi-settings-page .shahi-page-title .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
            color: #3b82f6 !important;
        }

        /* Settings Page - Mac Slate Liquid Theme */
        .shahi-settings-page .shahi-tabs-nav {
            background: #0f172a !important;
            padding: 20px !important;
            border-radius: 12px !important;
            margin-bottom: 20px !important;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .shahi-settings-page a.shahi-tab-link {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 14px 24px !important;
            color: #f8fafc !important;
            background: #1e293b !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            border: 2px solid #334155 !important;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .shahi-settings-page a.shahi-tab-link .dashicons {
            color: #3b82f6 !important;
            font-size: 20px !important;
            width: 20px;
            height: 20px;
        }

        .shahi-settings-page a.shahi-tab-link:hover {
            background: #334155 !important;
            color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25) !important;
            transform: translateY(-2px);
        }

        .shahi-settings-page a.shahi-tab-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%) !important;
            color: #1e3a5f !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 197, 253, 0.3) !important;
            transform: translateY(-2px);
        }

        .shahi-settings-page a.shahi-tab-link.active .dashicons {
            color: #1e3a5f !important;
        }
    </style>
    
    <!-- Page Header -->
    <div class="shahi-page-header">
        <div class="shahi-header-content">
            <h1 class="shahi-page-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php echo esc_html__('Settings', 'shahi-legalflowsuite'); ?>
            </h1>
            <p class="shahi-page-description">
                <?php echo esc_html__('Configure plugin behavior and preferences', 'shahi-legalflowsuite'); ?>
            </p>
        </div>
    </div>

    <!-- Settings Messages -->
    <?php settings_errors('shahi_settings'); ?>

    <!-- Settings Tabs -->
    <div class="shahi-settings-tabs">
        <nav class="shahi-tabs-nav">
            <?php foreach ($tabs as $tab_key => $tab): ?>
                <a href="?page=shahi-legalflowsuite-settings&tab=<?php echo esc_attr($tab_key); ?>" 
                   class="shahi-tab-link <?php echo $active_tab === $tab_key ? 'active' : ''; ?>">
                    <span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span>
                    <?php echo esc_html($tab['title']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Settings Form -->
    <form method="post" action="">
        <?php wp_nonce_field('shahi_save_settings', 'shahi_settings_nonce'); ?>

        <div class="shahi-settings-content">
            <?php if ($active_tab === 'privacy'): ?>
                <!-- Privacy Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Privacy Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Configure geolocation, consent banner, cookie scanner, and retention. These settings are critical for GDPR, CCPA, and LGPD compliance.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">

                            <!-- Geolocation -->
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Geolocation Detection', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_geolocation_detection" value="1" <?php checked($settings['enable_geolocation_detection']); ?>>
                                        <span><?php echo esc_html__('Enable geolocation-based privacy behavior', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Automatically detect visitor location to apply region-specific privacy rules.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Shows GDPR banner for EU, CCPA for California, etc.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="geolocation_provider" class="shahi-setting-label">
                                    <?php echo esc_html__('Geolocation Provider', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <select id="geolocation_provider" name="geolocation_provider" class="shahi-select">
                                        <option value="ipapi" <?php selected($settings['geolocation_provider'], 'ipapi'); ?>>ipapi</option>
                                        <option value="ipinfo" <?php selected($settings['geolocation_provider'], 'ipinfo'); ?>>ipinfo</option>
                                        <option value="ip-api" <?php selected($settings['geolocation_provider'], 'ip-api'); ?>>ip-api</option>
                                    </select>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Choose your IP geolocation API provider.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('ipapi is free up to 1000 requests/day. ipinfo offers higher accuracy.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="geolocation_cache_ttl" class="shahi-setting-label">
                                    <?php echo esc_html__('Geolocation Cache TTL', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="geolocation_cache_ttl" name="geolocation_cache_ttl" value="<?php echo esc_attr($settings['geolocation_cache_ttl']); ?>" min="60" max="604800" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('seconds', 'shahi-legalflowsuite'); ?></span>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How long to cache geolocation results per IP address.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('86400 (1 day) reduces API calls. Lower values for VPN-heavy audiences.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="geolocation_override_region" class="shahi-setting-label">
                                    <?php echo esc_html__('Override Region', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <select id="geolocation_override_region" name="geolocation_override_region" class="shahi-select">
                                        <option value="" <?php selected($settings['geolocation_override_region'], ''); ?>><?php echo esc_html__('Auto-detect', 'shahi-legalflowsuite'); ?></option>
                                        <option value="EU" <?php selected($settings['geolocation_override_region'], 'EU'); ?>>EU</option>
                                        <option value="US-CA" <?php selected($settings['geolocation_override_region'], 'US-CA'); ?>>US-CA</option>
                                        <option value="US" <?php selected($settings['geolocation_override_region'], 'US'); ?>>US</option>
                                        <option value="BR" <?php selected($settings['geolocation_override_region'], 'BR'); ?>>BR</option>
                                        <option value="GLOBAL" <?php selected($settings['geolocation_override_region'], 'GLOBAL'); ?>>GLOBAL</option>
                                    </select>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Force a specific region for all visitors (useful for testing).', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Set to "Auto-detect" for production use.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Privacy Controls -->
                            <div class="shahi-divider"></div>
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label"><?php echo esc_html__('Global Privacy Controls', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="respect_dnt" value="1" <?php checked($settings['respect_dnt']); ?>>
                                        <span><?php echo esc_html__('Respect Do Not Track (DNT) / Global Privacy Control', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Automatically honor browser privacy signals.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Required by CCPA for California residents using GPC-enabled browsers.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label"><?php echo esc_html__('Preferences Visibility', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="show_preferences_link" value="1" <?php checked($settings['show_preferences_link']); ?>>
                                        <span><?php echo esc_html__('Show “Cookie Settings” link globally', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <div class="shahi-inline-group" style="margin-top:8px;">
                                        <label class="shahi-checkbox-label">
                                            <input type="checkbox" name="preferences_show_history" value="1" <?php checked($settings['preferences_show_history']); ?>>
                                            <span><?php echo esc_html__('Enable consent history on preferences UI', 'shahi-legalflowsuite'); ?></span>
                                        </label>
                                        <label class="shahi-checkbox-label">
                                            <input type="checkbox" name="preferences_show_download" value="1" <?php checked($settings['preferences_show_download']); ?>>
                                            <span><?php echo esc_html__('Enable “Download My Data” on preferences UI', 'shahi-legalflowsuite'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label"><?php echo esc_html__('Strict Consent Mode', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="block_scripts_until_consent" value="1" <?php checked($settings['block_scripts_until_consent']); ?>>
                                        <span><?php echo esc_html__('Block marketing/analytics scripts until consent is granted', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="banner_region_scope" class="shahi-setting-label"><?php echo esc_html__('Banner Region Scope', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <select id="banner_region_scope" name="banner_region_scope" class="shahi-select">
                                        <option value="GLOBAL" <?php selected($settings['banner_region_scope'], 'GLOBAL'); ?>><?php echo esc_html__('Global (all regions)', 'shahi-legalflowsuite'); ?></option>
                                        <option value="EU" <?php selected($settings['banner_region_scope'], 'EU'); ?>>EU</option>
                                        <option value="US-CA" <?php selected($settings['banner_region_scope'], 'US-CA'); ?>>US-CA</option>
                                        <option value="US" <?php selected($settings['banner_region_scope'], 'US'); ?>>US</option>
                                        <option value="BR" <?php selected($settings['banner_region_scope'], 'BR'); ?>>BR</option>
                                        <option value="" <?php selected($settings['banner_region_scope'], ''); ?>><?php echo esc_html__('Auto (match geolocation)', 'shahi-legalflowsuite'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="reprompt_interval_days" class="shahi-setting-label"><?php echo esc_html__('Re-prompt Interval', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="reprompt_interval_days" name="reprompt_interval_days" value="<?php echo esc_attr($settings['reprompt_interval_days']); ?>" min="7" max="1095" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('days', 'shahi-legalflowsuite'); ?></span>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label"><?php echo esc_html__('Bots & Crawlers', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="hide_banner_for_bots" value="1" <?php checked($settings['hide_banner_for_bots']); ?>>
                                        <span><?php echo esc_html__('Hide banner for known bots/crawlers', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Cookie Scanner -->
                            <div class="shahi-divider"></div>
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label"><?php echo esc_html__('Cookie Scanner', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="cookie_scanner_auto_scan" value="1" <?php checked($settings['cookie_scanner_auto_scan']); ?>>
                                        <span><?php echo esc_html__('Auto-scan cookies on schedule', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Automatically detects and categorizes cookies on your site.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Helps maintain accurate cookie disclosures required by ePrivacy Directive.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="cookie_scanner_frequency" class="shahi-setting-label"><?php echo esc_html__('Scan Frequency', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <select id="cookie_scanner_frequency" name="cookie_scanner_frequency" class="shahi-select">
                                        <option value="daily" <?php selected($settings['cookie_scanner_frequency'], 'daily'); ?>><?php echo esc_html__('Daily', 'shahi-legalflowsuite'); ?></option>
                                        <option value="weekly" <?php selected($settings['cookie_scanner_frequency'], 'weekly'); ?>><?php echo esc_html__('Weekly', 'shahi-legalflowsuite'); ?></option>
                                        <option value="monthly" <?php selected($settings['cookie_scanner_frequency'], 'monthly'); ?>><?php echo esc_html__('Monthly', 'shahi-legalflowsuite'); ?></option>
                                    </select>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How often to scan for new or changed cookies.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Daily recommended for sites with frequent updates or third-party integrations.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Data Retention -->
                            <div class="shahi-divider"></div>
                            <div class="shahi-setting-row">
                                <label for="consent_retention_days" class="shahi-setting-label"><?php echo esc_html__('Consent Retention', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="consent_retention_days" name="consent_retention_days" value="<?php echo esc_attr($settings['consent_retention_days']); ?>" min="30" max="1095" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('days', 'shahi-legalflowsuite'); ?></span>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How long to store consent records before deletion.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('GDPR recommends 12-24 months. Keep records to prove compliance if audited.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="consent_log_retention_days" class="shahi-setting-label"><?php echo esc_html__('Consent Log Retention', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="consent_log_retention_days" name="consent_log_retention_days" value="<?php echo esc_attr($settings['consent_log_retention_days']); ?>" min="30" max="1095" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('days', 'shahi-legalflowsuite'); ?></span>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How long to keep detailed consent interaction logs.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Logs include timestamps, IP hashes, and category selections for audit trails.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label"><?php echo esc_html__('Auto-delete Expired Records', 'shahi-legalflowsuite'); ?></label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="auto_delete_expired" value="1" <?php checked($settings['auto_delete_expired']); ?>>
                                        <span><?php echo esc_html__('Automatically remove expired consent records', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Runs daily cleanup via WP-Cron to delete records past retention period.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Supports GDPR data minimization principle (Article 5).', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if ($active_tab === 'general'): ?>
                <!-- General Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('General Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Configure basic plugin behavior and branding options. These settings affect how the plugin appears and operates across your WordPress admin.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Debug Mode', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_debug" value="1" 
                                               <?php checked($settings['enable_debug']); ?>>
                                        <span><?php echo esc_html__('Enable debug logging', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Log detailed information for troubleshooting. Disable in production.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Debug logs are stored in wp-content/debug.log and help identify issues with consent tracking or API calls.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Uninstall Options', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="delete_data_on_uninstall" value="1" 
                                               <?php checked($settings['delete_data_on_uninstall']); ?>>
                                        <span><?php echo esc_html__('Delete all plugin data on uninstall', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description shahi-text-warning">
                                        <?php echo esc_html__('Warning: This will permanently delete all plugin data when the plugin is uninstalled.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Includes consent records, DSR requests, legal documents, and all analytics data. Use the Uninstall tab for granular control.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'notifications'): ?>
                <!-- Notification Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Notification Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Configure email alerts for important compliance events. Stay informed about DSR requests, errors, and module changes.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Email Notifications', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_email_notifications" value="1" 
                                               <?php checked($settings['enable_email_notifications']); ?>>
                                        <span><?php echo esc_html__('Enable email notifications', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Master switch for all email notifications from this plugin.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Disabling this prevents all automated emails including DSR and error alerts.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="notification_email" class="shahi-setting-label">
                                    <?php echo esc_html__('Notification Email', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="email" id="notification_email" name="notification_email" 
                                           value="<?php echo esc_attr($settings['notification_email']); ?>" 
                                           class="shahi-input">
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Where to send notification emails.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Defaults to WordPress admin email. Use a monitored inbox for compliance alerts.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Notification Events', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="notify_on_error" value="1" 
                                               <?php checked($settings['notify_on_error']); ?>>
                                        <span><?php echo esc_html__('Notify on errors', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Receive alerts for API failures, database errors, and consent processing issues.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                    <label class="shahi-checkbox-label" style="margin-top: 12px;">
                                        <input type="checkbox" name="notify_on_module_change" value="1" 
                                               <?php checked($settings['notify_on_module_change']); ?>>
                                        <span><?php echo esc_html__('Notify on module changes', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Get notified when modules are enabled or disabled by any admin user.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'performance'): ?>
                <!-- Performance Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Performance Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Optimize plugin performance with caching and asset loading options. Reduce database queries and page load times.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Caching', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_caching" value="1" 
                                               <?php checked($settings['enable_caching']); ?>>
                                        <span><?php echo esc_html__('Enable query result caching', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Caches database queries for consent stats, module states, and dashboard widgets.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Uses WordPress object cache or transients if no persistent cache exists.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="cache_duration" class="shahi-setting-label">
                                    <?php echo esc_html__('Cache Duration', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="cache_duration" name="cache_duration" 
                                           value="<?php echo esc_attr($settings['cache_duration']); ?>" 
                                           min="60" max="86400" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('seconds', 'shahi-legalflowsuite'); ?></span>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How long to cache query results (60-86400 seconds).', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('3600 seconds (1 hour) is recommended. Lower values for high-traffic sites with frequent consent changes.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Asset Optimization', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_minification" value="1" 
                                               <?php checked($settings['enable_minification']); ?>>
                                        <span><?php echo esc_html__('Load minified CSS/JS files', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Loads compressed .min.css and .min.js files instead of full versions.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Reduces file sizes by ~40%. Disable for debugging front-end issues.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                    <label class="shahi-checkbox-label" style="margin-top: 12px;">
                                        <input type="checkbox" name="lazy_load_assets" value="1" 
                                               <?php checked($settings['lazy_load_assets']); ?>>
                                        <span><?php echo esc_html__('Lazy load page-specific assets', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Only loads CSS/JS on pages where they are needed.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Improves page speed scores. Consent banner assets always load on frontend.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'advanced'): ?>
                <!-- Advanced Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Advanced Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Configure REST API access, rate limiting, and developer tools. These settings are for advanced integrations and debugging.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('REST API', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="api_enabled" value="1" 
                                               <?php checked($settings['api_enabled']); ?>>
                                        <span><?php echo esc_html__('Enable REST API endpoints', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Exposes slos/v1/* endpoints for external integrations.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Required for headless setups, mobile apps, or third-party CMP integrations.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="api_key" class="shahi-setting-label">
                                    <?php echo esc_html__('API Key', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <div class="shahi-input-group">
                                        <input type="text" id="api_key" name="api_key" 
                                               value="<?php echo esc_attr($settings['api_key']); ?>" 
                                               class="shahi-input" readonly>
                                        <button type="button" class="shahi-btn shahi-btn-secondary" 
                                                onclick="document.getElementById('api_key').value='<?php echo \ShahiLegalFlowSuite\Admin\Settings::generate_api_key(); ?>';">
                                            <?php echo esc_html__('Generate New', 'shahi-legalflowsuite'); ?>
                                        </button>
                                    </div>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Use this key to authenticate API requests.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Pass as X-SLOS-API-Key header or api_key query parameter. Regenerate if compromised.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Rate Limiting', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="rate_limit_enabled" value="1" 
                                               <?php checked($settings['rate_limit_enabled']); ?>>
                                        <span><?php echo esc_html__('Enable rate limiting for API requests', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Protects API endpoints from abuse and excessive requests.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Returns HTTP 429 when limit exceeded. Recommended for public-facing APIs.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Rate Limit Configuration', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <div class="shahi-inline-group">
                                        <input type="number" name="rate_limit_requests" 
                                               value="<?php echo esc_attr($settings['rate_limit_requests']); ?>" 
                                               min="1" max="1000" class="shahi-input shahi-input-sm">
                                        <span><?php echo esc_html__('requests per', 'shahi-legalflowsuite'); ?></span>
                                        <input type="number" name="rate_limit_window" 
                                               value="<?php echo esc_attr($settings['rate_limit_window']); ?>" 
                                               min="1" max="3600" class="shahi-input shahi-input-sm">
                                        <span><?php echo esc_html__('seconds', 'shahi-legalflowsuite'); ?></span>
                                    </div>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Default is 100 requests per 60 seconds per IP/API key.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Adjust based on expected traffic. Higher for bulk operations.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row shahi-setting-row-highlight">
                                <label class="shahi-setting-label">
                                    <strong class="shahi-text-danger"><?php echo esc_html__('Onboarding Wizard', 'shahi-legalflowsuite'); ?></strong>
                                </label>
                                <div class="shahi-setting-control">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalflowsuite-debug-onboarding')); ?>" class="shahi-btn shahi-btn-danger" target="_blank">
                                            <span class="dashicons dashicons-external"></span>
                                            <?php echo esc_html__('Open Debug Onboarding (Force Delete & Restart)', 'shahi-legalflowsuite'); ?>
                                        </a>
                                        <p class="shahi-setting-description">
                                            <?php echo esc_html__('Opens the Debug Onboarding page where you can force delete onboarding options and flush caches.', 'shahi-legalflowsuite'); ?>
                                            <?php echo esc_html__('Use this if you need to re-run the setup wizard or if onboarding state is stuck.', 'shahi-legalflowsuite'); ?>
                                        </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'security'): ?>
                <!-- Security Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Security Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Configure security features to protect your site and comply with data protection requirements. These settings affect form submissions and admin access.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Enable Rate Limiting', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_rate_limiting" value="1" 
                                               <?php checked($settings['enable_rate_limiting']); ?>>
                                        <span><?php echo esc_html__('Limit the number of requests from a single IP address', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Helps prevent brute force attacks and DDoS attempts.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Blocks IPs that exceed 60 requests/minute to DSR portal and consent endpoints.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="ip_blacklist" class="shahi-setting-label">
                                    <?php echo esc_html__('IP Blacklist', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <textarea id="ip_blacklist" name="ip_blacklist" 
                                              class="shahi-textarea" rows="5" 
                                              placeholder="<?php echo esc_attr__('192.168.1.1&#10;10.0.0.1&#10;172.16.0.1', 'shahi-legalflowsuite'); ?>"><?php echo esc_textarea($settings['ip_blacklist']); ?></textarea>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Enter one IP address per line. These IPs will be blocked from accessing your site.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Supports IPv4 addresses and CIDR notation (e.g., 192.168.1.0/24). Use for known bad actors.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('File Upload Restrictions', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="file_upload_restrictions" value="1" 
                                               <?php checked($settings['file_upload_restrictions']); ?>>
                                        <span><?php echo esc_html__('Restrict file upload types to safe formats only', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Only allows images, PDFs, and common document formats.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Protects against malicious file uploads via DSR forms.', 'shahi-legalflowsuite'); ?>
                                        <span class="shahi-badge shahi-badge-secondary"><?php echo esc_html__('Coming Soon', 'shahi-legalflowsuite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Two-Factor Authentication', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="two_factor_auth" value="1" 
                                               <?php checked($settings['two_factor_auth']); ?>>
                                        <span><?php echo esc_html__('Require 2FA for admin login', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Adds an extra layer of security to admin accounts.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Requires TOTP authenticator app (Google Authenticator, Authy, etc).', 'shahi-legalflowsuite'); ?>
                                        <span class="shahi-badge shahi-badge-secondary"><?php echo esc_html__('Coming Soon', 'shahi-legalflowsuite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Activity Logging', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="activity_logging" value="1" 
                                               <?php checked($settings['activity_logging']); ?>>
                                        <span><?php echo esc_html__('Log all admin actions for security audits', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Records DSR processing, consent changes, and settings modifications.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Essential for GDPR Article 30 compliance and audit trails.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'import_export'): ?>
                <!-- Import/Export Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Import/Export Settings', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Backup and restore your plugin settings. Use these tools to migrate settings between sites or create backups before major changes.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Export Settings', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <button type="button" id="shahi-export-settings" class="shahi-btn shahi-btn-secondary">
                                        <span class="dashicons dashicons-download"></span>
                                        <?php echo esc_html__('Download Settings (JSON)', 'shahi-legalflowsuite'); ?>
                                    </button>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Export all your settings as a JSON file for backup.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Includes all tabs: General, Privacy, Security, Advanced, and Uninstall options.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="shahi-import-file" class="shahi-setting-label">
                                    <?php echo esc_html__('Import Settings', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="file" id="shahi-import-file" accept=".json" class="shahi-file-input">
                                    <button type="button" id="shahi-import-settings" class="shahi-btn shahi-btn-secondary">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php echo esc_html__('Import Settings (JSON)', 'shahi-legalflowsuite'); ?>
                                    </button>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Import settings from a previously exported JSON file.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Validates JSON structure and only imports known settings keys. Unknown keys are ignored.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Reset to Defaults', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <button type="button" id="shahi-reset-settings" class="shahi-btn shahi-btn-danger">
                                        <span class="dashicons dashicons-update"></span>
                                        <?php echo esc_html__('Reset All Settings', 'shahi-legalflowsuite'); ?>
                                    </button>
                                    <p class="shahi-setting-description shahi-text-danger">
                                        <?php echo esc_html__('Warning: This will reset all settings to their default values. This action cannot be undone!', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Export your settings first if you may want to restore them later.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'uninstall'): ?>
                <!-- Uninstall Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Uninstall Options', 'shahi-legalflowsuite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Control data retention when uninstalling. These settings help you maintain compliance with data retention policies while ensuring smooth plugin removal or reinstallation.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-alert shahi-alert-info">
                                <span class="dashicons dashicons-info"></span>
                                <p><?php echo esc_html__('These settings determine what happens to your data when you uninstall the plugin. By default, all data will be removed. Check any boxes below to preserve specific data types.', 'shahi-legalflowsuite'); ?></p>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve Landing Pages', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_landing_pages" value="1" 
                                               <?php checked($settings['preserve_landing_pages']); ?>>
                                        <span><?php echo esc_html__('Keep all landing pages created with this plugin', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Preserves Privacy Policy, Terms of Service, and other legal document landing pages.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Recommended if you want to maintain your legal compliance pages after uninstallation.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve Analytics Data', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_analytics_data" value="1" 
                                               <?php checked($settings['preserve_analytics_data']); ?>>
                                        <span><?php echo esc_html__('Keep all analytics and tracking data', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Retains consent logs, DSR request history, and compliance metrics.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Required for audit trails and demonstrating historical compliance under GDPR/CCPA.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve Settings', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_settings" value="1" 
                                               <?php checked($settings['preserve_settings']); ?>>
                                        <span><?php echo esc_html__('Keep plugin settings for future reinstallation', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Saves all configuration options in wp_options for quick restoration.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Useful for temporary deactivation or when updating to a new version.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve User Capabilities', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_user_capabilities" value="1" 
                                               <?php checked($settings['preserve_user_capabilities']); ?>>
                                        <span><?php echo esc_html__('Keep custom user roles and capabilities', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Maintains manage_shahi_template, slos_manage_dsr, and other custom capabilities.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Prevents role configuration loss if you plan to reinstall the plugin.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Complete Cleanup', 'shahi-legalflowsuite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="complete_cleanup" value="1" 
                                               <?php checked($settings['complete_cleanup']); ?>>
                                        <span><?php echo esc_html__('Remove ALL plugin data on uninstall (overrides preservation options)', 'shahi-legalflowsuite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description shahi-text-danger">
                                        <?php echo esc_html__('Warning: Enabling this will permanently delete all plugin data, regardless of other preservation settings.', 'shahi-legalflowsuite'); ?>
                                        <?php echo esc_html__('Use only when permanently removing the plugin and you have no data retention obligations.', 'shahi-legalflowsuite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Form Footer -->
        <div class="shahi-form-footer">
            <button type="submit" name="shahi_save_settings" class="shahi-btn shahi-btn-primary shahi-btn-lg">
                <span class="dashicons dashicons-saved"></span>
                <?php echo esc_html__('Save Settings', 'shahi-legalflowsuite'); ?>
            </button>
        </div>

    </form>

</div>

