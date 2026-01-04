<?php
/**
 * Module Dashboard Template - Modern Design
 *
 * Ultra-modern module management interface with card-based layout,
 * enhanced visualizations, and smooth interactions.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      3.0.1
 * @version    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalflowsuite-admin shahi-modules">
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         TOP BAR
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-topbar">
        <div class="shahi-topbar-left">
            <div class="shahi-brand">
                <span class="shahi-brand-icon">🧩</span>
                <div class="shahi-brand-text">
                    <span class="shahi-brand-name">Modules</span>
                    <span class="shahi-brand-tag"><?php echo esc_html($stats['active']); ?>/<?php echo esc_html($stats['total']); ?> Active</span>
                </div>
            </div>
            <div class="shahi-breadcrumb">
                <span class="dashicons dashicons-admin-home"></span>
                <span class="shahi-breadcrumb-text"><?php echo esc_html__('Module Management', 'shahi-legalflowsuite'); ?></span>
            </div>
        </div>
        <div class="shahi-topbar-right">
            <button type="button" class="shahi-btn-icon shahi-bulk-enable" data-action="enable" title="<?php echo esc_attr__('Enable All', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-yes-alt"></span>
            </button>
            <button type="button" class="shahi-btn-icon shahi-bulk-disable" data-action="disable" title="<?php echo esc_attr__('Disable All', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-dismiss"></span>
            </button>
            <button type="button" class="shahi-btn-icon" data-action="refresh" title="<?php echo esc_attr__('Refresh', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-hero">
        <div class="shahi-hero-bg"></div>
        <div class="shahi-hero-content">
            <div class="shahi-hero-main">
                <h1 class="shahi-hero-title">
                    <?php echo esc_html__('Manage Your Modules', 'shahi-legalflowsuite'); ?>
                </h1>
                <p class="shahi-hero-subtitle">
                    <?php echo esc_html__('Enable, disable, and configure plugin modules to customize your experience.', 'shahi-legalflowsuite'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         STATS ROW
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-stats-row shahi-stats-container shahi-stats-inline">
        <div class="shahi-stat-card">
            <div class="shahi-stat-icon-wrap">
                <div class="shahi-stat-icon">
                    <span class="dashicons dashicons-screenoptions"></span>
                </div>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-label"><?php echo esc_html__('Total Modules', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html($stats['total']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-stat-card">
            <div class="shahi-stat-icon-wrap">
                <div class="shahi-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="shahi-stat-badge shahi-trend-up">
                    <?php echo esc_html($stats['activation_rate']); ?>%
                </div>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-label"><?php echo esc_html__('Active Modules', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html($stats['active']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-stat-card">
            <div class="shahi-stat-icon-wrap">
                <div class="shahi-stat-icon">
                    <span class="dashicons dashicons-marker"></span>
                </div>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-label"><?php echo esc_html__('Inactive Modules', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html($stats['inactive']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-stat-card">
            <div class="shahi-stat-icon-wrap">
                <div class="shahi-stat-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-label"><?php echo esc_html__('Avg Performance', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html($stats['avg_performance']); ?></span>
                    <span class="shahi-stat-suffix">%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         FILTERS & SEARCH
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-controls shahi-controls-bar">
        <div class="shahi-search-wrapper">
            <span class="dashicons dashicons-search"></span>
            <input type="text" 
                   id="shahi-module-search" 
                   class="shahi-search-input" 
                   placeholder="<?php echo esc_attr__('Search modules...', 'shahi-legalflowsuite'); ?>"
                   autocomplete="off">
            <span class="shahi-search-clear dashicons dashicons-no-alt" style="display: none;"></span>
        </div>
        
        <div class="shahi-filter-group">
            <button class="shahi-filter-btn active" data-filter="all">
                <?php echo esc_html__('All', 'shahi-legalflowsuite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($stats['total']); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="active">
                <?php echo esc_html__('Active', 'shahi-legalflowsuite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($stats['active']); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="inactive">
                <?php echo esc_html__('Inactive', 'shahi-legalflowsuite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($stats['inactive']); ?></span>
            </button>
        </div>

        <div class="shahi-view-toggle">
            <button class="shahi-view-btn active" data-view="grid" title="<?php echo esc_attr__('Grid View', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-grid-view"></span>
            </button>
            <button class="shahi-view-btn" data-view="list" title="<?php echo esc_attr__('List View', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-list-view"></span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         MODULES GRID
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-modules-container">
        <div class="shahi-modules-grid shahi-modules-grid-premium" data-view="grid">
            <?php foreach ($modules as $module): ?>
                    <div class="shahi-module-card shahi-module-card-premium <?php echo $module['enabled'] ? 'active' : 'inactive'; ?>" 
                         data-module="<?php echo esc_attr($module['slug']); ?>"
                         data-status="<?php echo $module['enabled'] ? 'active' : 'inactive'; ?>"
                         data-category="<?php echo esc_attr($module['category']); ?>">

                        <div class="shahi-card-status-border"></div>
                        <div class="shahi-card-bg-effect"></div>
                        <div class="shahi-card-glow"></div>

                        <div class="shahi-module-card-header">
                            <div class="shahi-module-icon-wrapper">
                                <span class="dashicons dashicons-admin-plugins"></span>
                                <span class="shahi-icon-pulse"></span>
                            </div>
                            <div class="shahi-module-meta">
                                <span class="shahi-module-category"><?php echo esc_html($module['category']); ?></span>
                                <span class="shahi-module-status-badge">
                                    <span class="<?php echo $module['enabled'] ? 'shahi-status-active' : 'shahi-status-inactive'; ?>">
                                        <span class="shahi-status-dot"></span>
                                        <?php echo $module['enabled'] ? esc_html__('Active', 'shahi-legalflowsuite') : esc_html__('Inactive', 'shahi-legalflowsuite'); ?>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="shahi-module-card-body">
                            <h3 class="shahi-module-title"><?php echo esc_html($module['name']); ?></h3>
                            <p class="shahi-module-description"><?php echo esc_html($module['description']); ?></p>

                            <div class="shahi-module-stats">
                                <div class="shahi-stat-item">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    <div>
                                        <div class="shahi-stat-value"><?php echo esc_html($module['usage_count']); ?></div>
                                        <div class="shahi-stat-text"><?php echo esc_html__('uses', 'shahi-legalflowsuite'); ?></div>
                                    </div>
                                </div>
                                <div class="shahi-stat-item">
                                    <span class="dashicons dashicons-performance"></span>
                                    <div>
                                        <div class="shahi-stat-value"><?php echo esc_html($module['performance_score']); ?>%</div>
                                        <div class="shahi-stat-text"><?php echo esc_html__('performance', 'shahi-legalflowsuite'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($module['dependencies'])): ?>
                                <div class="shahi-module-dependencies shahi-module-deps">
                                    <div class="shahi-dependencies-label">
                                        <span class="dashicons dashicons-networking"></span>
                                        <?php echo esc_html__('Requires:', 'shahi-legalflowsuite'); ?>
                                    </div>
                                    <div class="shahi-dependencies-list shahi-deps-list">
                                        <?php foreach ($module['dependencies'] as $dep): ?>
                                            <span class="shahi-dependency-tag shahi-dep-tag"><?php echo esc_html($dep); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="shahi-module-card-footer">
                            <label class="shahi-toggle-switch shahi-toggle-switch-premium">
                                <input type="checkbox" 
                                       class="shahi-module-toggle-input"
                                       data-module="<?php echo esc_attr($module['slug']); ?>"
                                       <?php checked($module['enabled']); ?>>
                                <span class="shahi-toggle-slider">
                                    <span class="shahi-toggle-icon shahi-toggle-icon-off">
                                        <span class="dashicons dashicons-no"></span>
                                    </span>
                                    <span class="shahi-toggle-icon shahi-toggle-icon-on">
                                        <span class="dashicons dashicons-yes"></span>
                                    </span>
                                    <span class="shahi-toggle-knob"></span>
                                </span>
                                <span class="shahi-toggle-label">
                                    <?php echo $module['enabled'] ? esc_html__('Enabled', 'shahi-legalflowsuite') : esc_html__('Disabled', 'shahi-legalflowsuite'); ?>
                                </span>
                            </label>

                            <div class="shahi-module-actions">
                                <?php
                                $settings_url = isset($module['settings_url']) ? $module['settings_url'] : '';
                                if (empty($settings_url) && ($module['slug'] ?? '') === 'accessibility-scanner') {
                                    $settings_url = admin_url('admin.php?page=slos-accessibility-settings');
                                }
                                ?>
                                <?php if (!empty($settings_url) && $module['enabled']): ?>
                                    <a href="<?php echo esc_url($settings_url); ?>" class="shahi-action-btn shahi-settings-btn" title="<?php echo esc_attr__('Settings', 'shahi-legalflowsuite'); ?>">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="shahi-action-btn shahi-info-btn" data-module-slug="<?php echo esc_attr($module['slug']); ?>" title="<?php echo esc_attr__('Info', 'shahi-legalflowsuite'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </button>
                            </div>
                        </div>

                    </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div class="shahi-empty-state" style="display: none;">
            <span class="dashicons dashicons-search"></span>
            <h3><?php echo esc_html__('No modules found', 'shahi-legalflowsuite'); ?></h3>
            <p><?php echo esc_html__('Try adjusting your search or filter criteria', 'shahi-legalflowsuite'); ?></p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="shahi-loading-overlay" style="display: none;">
        <div class="shahi-spinner">
            <div class="shahi-spinner-ring"></div>
            <div class="shahi-spinner-ring"></div>
            <div class="shahi-spinner-ring"></div>
        </div>
    </div>

</div>

<script type="text/javascript">
/**
 * Module Info Button Handler - Inline fallback
 * Ensures info buttons work even if main JS has issues
 */
(function($) {
    'use strict';
    
    // Module descriptions data
    var moduleDescriptions = {
        'consent-management': {
            title: 'Consent Management',
            fullDesc: 'A comprehensive consent management platform that helps your website comply with GDPR, CCPA, and other privacy regulations. This module provides a customizable cookie consent banner that appears to visitors, allowing them to choose which types of cookies to accept.',
            features: [
                'Customizable consent banner with multiple templates and themes',
                'Granular cookie categories (Necessary, Analytics, Marketing, Preferences)',
                'Automatic script blocking until consent is given',
                'Consent audit logging with timestamps and IP addresses',
                'User preference center for managing consent choices',
                'Geolocation-based banner display rules',
                'Integration with Google Tag Manager and analytics platforms',
                'Consent export for compliance audits'
            ]
        },
        'dsr-portal': {
            title: 'DSR Portal (Data Subject Requests)',
            fullDesc: 'The DSR Portal module provides a complete workflow system for managing Data Subject Access Requests (DSARs) as required by GDPR Article 15-22. It enables visitors to submit requests for accessing, correcting, or deleting their personal data.',
            features: [
                'Public-facing request submission form with customizable fields',
                'Identity verification workflow before processing requests',
                'Automated email notifications at each stage of the process',
                'Request tracking dashboard with status management',
                'Configurable SLA timers (default 30 days per GDPR)',
                'Data export in portable formats (JSON, CSV)',
                'Request type support: Access, Deletion, Rectification, Portability',
                'Audit trail for all request activities'
            ]
        },
        'legal-docs': {
            title: 'Legal Documents Generator',
            fullDesc: 'The Legal Documents module helps you create professional, legally-compliant documents for your website. Using your Company Profile data, it generates customized Privacy Policies, Terms of Service, Cookie Policies, and other legal documents.',
            features: [
                'Auto-generated Privacy Policy based on your data practices',
                'Terms of Service document with customizable clauses',
                'Cookie Policy synchronized with Consent Management settings',
                'GDPR-specific disclosures and rights information',
                'Document versioning and change history',
                'PDF export for record-keeping',
                'Automatic placeholders filled from Company Profile',
                'Multi-language document support with WPML integration'
            ]
        },
        'accessibility-scanner': {
            title: 'Accessibility Scanner',
            fullDesc: 'A comprehensive accessibility auditing tool that scans your WordPress content for WCAG 2.1 compliance issues. It helps identify barriers that may prevent users with disabilities from accessing your website content.',
            features: [
                'Automated scanning of pages, posts, and custom post types',
                'WCAG 2.1 Level A, AA, and AAA checks',
                'Issue categorization by severity (Error, Warning, Notice)',
                'Detailed remediation guidance for each issue',
                'Alt text quality analysis for images',
                'Heading structure and semantic HTML validation',
                'Color contrast checking for text readability',
                'Form accessibility validation (labels, ARIA)',
                'Accessibility statement generator',
                'Scheduled automatic scans'
            ]
        }
    };
    
    function showModuleInfo(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $btn = $(e.currentTarget);
        var $card = $btn.closest('.shahi-module-card');
        var moduleSlug = $btn.data('module-slug') || $card.data('module');
        var moduleName = $card.find('.shahi-module-title').text();
        var moduleCategory = $card.data('category') || 'compliance';
        var moduleStatus = $card.data('status');
        var usageCount = $card.find('.shahi-mini-stat-value').first().text() || '0';
        var perfScore = $card.find('.shahi-mini-stat-value').last().text() || '0%';
        
        var moduleInfo = moduleDescriptions[moduleSlug] || {
            title: moduleName,
            fullDesc: $card.find('.shahi-module-desc').text(),
            features: ['Module functionality as described above']
        };
        
        var featuresHtml = '';
        for (var i = 0; i < moduleInfo.features.length; i++) {
            featuresHtml += '<li><span class="dashicons dashicons-yes-alt"></span>' + moduleInfo.features[i] + '</li>';
        }
        
        var modalHtml = '<div class="shahi-info-modal-overlay">' +
            '<div class="shahi-info-modal shahi-info-modal-detailed">' +
                '<div class="shahi-info-modal-header">' +
                    '<div class="shahi-info-modal-title-wrap">' +
                        '<span class="shahi-info-modal-icon dashicons dashicons-admin-plugins"></span>' +
                        '<div>' +
                            '<h3>' + moduleInfo.title + '</h3>' +
                            '<span class="shahi-info-modal-category">' + moduleCategory + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="shahi-info-modal-close">&times;</button>' +
                '</div>' +
                '<div class="shahi-info-modal-body">' +
                    '<div class="shahi-info-section">' +
                        '<h4>Overview</h4>' +
                        '<p class="shahi-info-full-desc">' + moduleInfo.fullDesc + '</p>' +
                    '</div>' +
                    '<div class="shahi-info-section">' +
                        '<h4>Key Features</h4>' +
                        '<ul class="shahi-info-features">' + featuresHtml + '</ul>' +
                    '</div>' +
                    '<div class="shahi-info-stats-grid">' +
                        '<div class="shahi-info-stat-box">' +
                            '<span class="shahi-info-stat-icon dashicons dashicons-chart-bar"></span>' +
                            '<div class="shahi-info-stat-content">' +
                                '<span class="shahi-info-stat-value">' + usageCount + '</span>' +
                                '<span class="shahi-info-stat-label">Total Uses</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="shahi-info-stat-box">' +
                            '<span class="shahi-info-stat-icon dashicons dashicons-performance"></span>' +
                            '<div class="shahi-info-stat-content">' +
                                '<span class="shahi-info-stat-value">' + perfScore + '</span>' +
                                '<span class="shahi-info-stat-label">Performance</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="shahi-info-stat-box">' +
                            '<span class="shahi-info-stat-icon dashicons dashicons-flag"></span>' +
                            '<div class="shahi-info-stat-content">' +
                                '<span class="shahi-info-stat-value status-' + moduleStatus + '">' + (moduleStatus === 'active' ? 'Active' : 'Inactive') + '</span>' +
                                '<span class="shahi-info-stat-label">Status</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="shahi-info-stat-box">' +
                            '<span class="shahi-info-stat-icon dashicons dashicons-category"></span>' +
                            '<div class="shahi-info-stat-content">' +
                                '<span class="shahi-info-stat-value">' + moduleCategory + '</span>' +
                                '<span class="shahi-info-stat-label">Category</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="shahi-info-modal-footer">' +
                    '<button type="button" class="shahi-btn shahi-btn-secondary shahi-info-modal-close-btn">Close</button>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        var $modal = $(modalHtml);
        $('body').append($modal);
        
        setTimeout(function() { $modal.addClass('show'); }, 10);
        
        function closeModal() {
            $modal.removeClass('show');
            setTimeout(function() { $modal.remove(); }, 300);
            $(document).off('keydown.shahiInfoModal');
        }
        
        $modal.find('.shahi-info-modal-close, .shahi-info-modal-close-btn').on('click', closeModal);
        $modal.on('click', function(evt) {
            if ($(evt.target).hasClass('shahi-info-modal-overlay')) {
                closeModal();
            }
        });
        
        $(document).on('keydown.shahiInfoModal', function(evt) {
            if (evt.key === 'Escape') {
                closeModal();
            }
        });
    }
    
    // Bind on document ready
    $(document).ready(function() {
        // Use event delegation for maximum reliability
        $(document).on('click', '.shahi-info-btn', showModuleInfo);
    });
    
})(jQuery);
</script>