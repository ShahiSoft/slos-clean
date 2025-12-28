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

<div class="wrap shahi-legalflowsuite-admin shahi-modules-v3">
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         TOP BAR
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-topbar">
        <div class="shahi-v3-topbar-left">
            <div class="shahi-v3-brand">
                <span class="shahi-v3-brand-icon">🧩</span>
                <div class="shahi-v3-brand-text">
                    <span class="shahi-v3-brand-name">Modules</span>
                    <span class="shahi-v3-brand-tag"><?php echo esc_html($stats['active']); ?>/<?php echo esc_html($stats['total']); ?> Active</span>
                </div>
            </div>
            <div class="shahi-v3-breadcrumb">
                <span class="dashicons dashicons-admin-home"></span>
                <span class="shahi-v3-breadcrumb-text"><?php echo esc_html__('Module Management', 'shahi-legalflowsuite'); ?></span>
            </div>
        </div>
        <div class="shahi-v3-topbar-right">
            <button type="button" class="shahi-v3-btn-icon shahi-bulk-enable" data-action="enable" title="<?php echo esc_attr__('Enable All', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-yes-alt"></span>
            </button>
            <button type="button" class="shahi-v3-btn-icon shahi-bulk-disable" data-action="disable" title="<?php echo esc_attr__('Disable All', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-dismiss"></span>
            </button>
            <button type="button" class="shahi-v3-btn-icon" data-action="refresh" title="<?php echo esc_attr__('Refresh', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-hero">
        <div class="shahi-v3-hero-bg"></div>
        <div class="shahi-v3-hero-content">
            <div class="shahi-v3-hero-main">
                <h1 class="shahi-v3-hero-title">
                    <?php echo esc_html__('Manage Your Modules', 'shahi-legalflowsuite'); ?>
                </h1>
                <p class="shahi-v3-hero-subtitle">
                    <?php echo esc_html__('Enable, disable, and configure plugin modules to customize your experience.', 'shahi-legalflowsuite'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         STATS ROW
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-section-intro">
        <p class="shahi-v3-section-desc"><?php echo esc_html__('Overview of your module activation status and system performance. Track active modules and monitor overall health.', 'shahi-legalflowsuite'); ?></p>
    </div>
    <div class="shahi-v3-stats-row">
        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-screenoptions"></span>
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Total Modules', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['total']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="shahi-v3-stat-badge shahi-v3-trend-up">
                    <?php echo esc_html($stats['activation_rate']); ?>%
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Active Modules', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['active']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-marker"></span>
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Inactive Modules', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['inactive']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Avg Performance', 'shahi-legalflowsuite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['avg_performance']); ?></span>
                    <span class="shahi-v3-stat-suffix">%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         FILTERS & SEARCH
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-section-intro shahi-v3-section-intro-controls">
        <p class="shahi-v3-section-desc"><?php echo esc_html__('Search by name or description, filter by status, and switch between grid or list view. Use bulk actions to manage multiple modules at once.', 'shahi-legalflowsuite'); ?></p>
    </div>
    <div class="shahi-v3-controls">
        <div class="shahi-v3-search-wrapper">
            <span class="dashicons dashicons-search"></span>
            <input type="text" 
                   id="shahi-module-search" 
                   class="shahi-v3-search-input" 
                   placeholder="<?php echo esc_attr__('Search modules...', 'shahi-legalflowsuite'); ?>"
                   autocomplete="off">
            <span class="shahi-search-clear dashicons dashicons-no-alt" style="display: none;"></span>
        </div>
        
        <div class="shahi-v3-filter-group">
            <button class="shahi-v3-filter-btn active" data-filter="all">
                <?php echo esc_html__('All', 'shahi-legalflowsuite'); ?>
                <span class="shahi-v3-filter-count"><?php echo esc_html($stats['total']); ?></span>
            </button>
            <button class="shahi-v3-filter-btn" data-filter="active">
                <?php echo esc_html__('Active', 'shahi-legalflowsuite'); ?>
                <span class="shahi-v3-filter-count"><?php echo esc_html($stats['active']); ?></span>
            </button>
            <button class="shahi-v3-filter-btn" data-filter="inactive">
                <?php echo esc_html__('Inactive', 'shahi-legalflowsuite'); ?>
                <span class="shahi-v3-filter-count"><?php echo esc_html($stats['inactive']); ?></span>
            </button>
        </div>

        <div class="shahi-v3-view-toggle">
            <button class="shahi-v3-view-btn active" data-view="grid" title="<?php echo esc_attr__('Grid View', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-grid-view"></span>
            </button>
            <button class="shahi-v3-view-btn" data-view="list" title="<?php echo esc_attr__('List View', 'shahi-legalflowsuite'); ?>">
                <span class="dashicons dashicons-list-view"></span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         MODULES GRID
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-modules-container">
        <div class="shahi-v3-section-intro shahi-v3-section-intro-grid">
            <p class="shahi-v3-section-desc"><?php echo esc_html__('Toggle individual modules on or off using the switch. Click the settings icon to configure, or the info icon to view module details. Hover over cards for 3D effects.', 'shahi-legalflowsuite'); ?></p>
        </div>
        <div class="shahi-v3-modules-grid" data-view="grid">
            <?php foreach ($modules as $module): ?>
                <div class="shahi-v3-module-card <?php echo $module['enabled'] ? 'active' : 'inactive'; ?>" 
                     data-module="<?php echo esc_attr($module['slug']); ?>"
                     data-status="<?php echo $module['enabled'] ? 'active' : 'inactive'; ?>"
                     data-category="<?php echo esc_attr($module['category']); ?>">
                    
                    <!-- Status Indicator -->
                    <div class="shahi-v3-module-status-bar"></div>
                    
                    <!-- Card Content -->
                    <div class="shahi-v3-module-content">
                        
                        <!-- Header -->
                        <div class="shahi-v3-module-header">
                            <div class="shahi-v3-module-icon-wrap">
                                <span class="dashicons dashicons-admin-plugins"></span>
                            </div>
                            <div class="shahi-v3-module-badges">
                                <span class="shahi-v3-module-category"><?php echo esc_html($module['category']); ?></span>
                                <?php if ($module['enabled']): ?>
                                    <span class="shahi-v3-module-status-badge active">
                                        <span class="shahi-v3-status-dot"></span>
                                        <?php echo esc_html__('Active', 'shahi-legalflowsuite'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="shahi-v3-module-status-badge inactive">
                                        <span class="shahi-v3-status-dot"></span>
                                        <?php echo esc_html__('Inactive', 'shahi-legalflowsuite'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="shahi-v3-module-body">
                            <h3 class="shahi-v3-module-title"><?php echo esc_html($module['name']); ?></h3>
                            <p class="shahi-v3-module-desc"><?php echo esc_html($module['description']); ?></p>
                            
                            <!-- Mini Stats -->
                            <div class="shahi-v3-module-mini-stats">
                                <div class="shahi-v3-mini-stat-item">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    <span class="shahi-v3-mini-stat-value"><?php echo esc_html($module['usage_count']); ?></span>
                                    <span class="shahi-v3-mini-stat-label"><?php echo esc_html__('uses', 'shahi-legalflowsuite'); ?></span>
                                </div>
                                <div class="shahi-v3-mini-stat-divider"></div>
                                <div class="shahi-v3-mini-stat-item">
                                    <span class="dashicons dashicons-performance"></span>
                                    <span class="shahi-v3-mini-stat-value"><?php echo esc_html($module['performance_score']); ?>%</span>
                                    <span class="shahi-v3-mini-stat-label"><?php echo esc_html__('performance', 'shahi-legalflowsuite'); ?></span>
                                </div>
                            </div>

                            <!-- Dependencies -->
                            <?php if (!empty($module['dependencies'])): ?>
                                <div class="shahi-v3-module-deps">
                                    <span class="shahi-v3-deps-label">
                                        <span class="dashicons dashicons-networking"></span>
                                        <?php echo esc_html__('Requires:', 'shahi-legalflowsuite'); ?>
                                    </span>
                                    <div class="shahi-v3-deps-list">
                                        <?php foreach ($module['dependencies'] as $dep): ?>
                                            <span class="shahi-v3-dep-tag"><?php echo esc_html($dep); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer / Actions -->
                        <div class="shahi-v3-module-footer">
                            <!-- Toggle Switch -->
                            <label class="shahi-v3-toggle-switch">
                                <input type="checkbox" 
                                       class="shahi-module-toggle-input"
                                       data-module="<?php echo esc_attr($module['slug']); ?>"
                                       <?php checked($module['enabled']); ?>>
                                <span class="shahi-v3-toggle-slider">
                                    <span class="shahi-v3-toggle-knob"></span>
                                </span>
                                <span class="shahi-v3-toggle-label">
                                    <?php echo $module['enabled'] ? esc_html__('Enabled', 'shahi-legalflowsuite') : esc_html__('Disabled', 'shahi-legalflowsuite'); ?>
                                </span>
                            </label>
                            
                            <!-- Action Buttons -->
                            <div class="shahi-v3-module-actions">
                                <?php
                                $settings_url = isset($module['settings_url']) ? $module['settings_url'] : '';
                                if (empty($settings_url) && ($module['slug'] ?? '') === 'accessibility-scanner') {
                                    $settings_url = admin_url('admin.php?page=slos-accessibility-settings');
                                }
                                ?>
                                <?php if (!empty($settings_url) && $module['enabled']): ?>
                                    <a href="<?php echo esc_url($settings_url); ?>" class="shahi-v3-action-btn shahi-v3-settings-btn" title="<?php echo esc_attr__('Settings', 'shahi-legalflowsuite'); ?>">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="shahi-v3-action-btn shahi-v3-info-btn" data-module-slug="<?php echo esc_attr($module['slug']); ?>" title="<?php echo esc_attr__('Info', 'shahi-legalflowsuite'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div class="shahi-v3-empty-state" style="display: none;">
            <span class="dashicons dashicons-search"></span>
            <h3><?php echo esc_html__('No modules found', 'shahi-legalflowsuite'); ?></h3>
            <p><?php echo esc_html__('Try adjusting your search or filter criteria', 'shahi-legalflowsuite'); ?></p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="shahi-loading-overlay" style="display: none;">
        <div class="shahi-v3-spinner">
            <div class="shahi-v3-spinner-ring"></div>
            <div class="shahi-v3-spinner-ring"></div>
            <div class="shahi-v3-spinner-ring"></div>
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
        var $card = $btn.closest('.shahi-v3-module-card');
        var moduleSlug = $btn.data('module-slug') || $card.data('module');
        var moduleName = $card.find('.shahi-v3-module-title').text();
        var moduleCategory = $card.data('category') || 'compliance';
        var moduleStatus = $card.data('status');
        var usageCount = $card.find('.shahi-v3-mini-stat-value').first().text() || '0';
        var perfScore = $card.find('.shahi-v3-mini-stat-value').last().text() || '0%';
        
        var moduleInfo = moduleDescriptions[moduleSlug] || {
            title: moduleName,
            fullDesc: $card.find('.shahi-v3-module-desc').text(),
            features: ['Module functionality as described above']
        };
        
        var featuresHtml = '';
        for (var i = 0; i < moduleInfo.features.length; i++) {
            featuresHtml += '<li><span class="dashicons dashicons-yes-alt"></span>' + moduleInfo.features[i] + '</li>';
        }
        
        var modalHtml = '<div class="shahi-v3-info-modal-overlay">' +
            '<div class="shahi-v3-info-modal shahi-v3-info-modal-detailed">' +
                '<div class="shahi-v3-info-modal-header">' +
                    '<div class="shahi-v3-info-modal-title-wrap">' +
                        '<span class="shahi-v3-info-modal-icon dashicons dashicons-admin-plugins"></span>' +
                        '<div>' +
                            '<h3>' + moduleInfo.title + '</h3>' +
                            '<span class="shahi-v3-info-modal-category">' + moduleCategory + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="shahi-v3-info-modal-close">&times;</button>' +
                '</div>' +
                '<div class="shahi-v3-info-modal-body">' +
                    '<div class="shahi-v3-info-section">' +
                        '<h4>Overview</h4>' +
                        '<p class="shahi-v3-info-full-desc">' + moduleInfo.fullDesc + '</p>' +
                    '</div>' +
                    '<div class="shahi-v3-info-section">' +
                        '<h4>Key Features</h4>' +
                        '<ul class="shahi-v3-info-features">' + featuresHtml + '</ul>' +
                    '</div>' +
                    '<div class="shahi-v3-info-stats-grid">' +
                        '<div class="shahi-v3-info-stat-box">' +
                            '<span class="shahi-v3-info-stat-icon dashicons dashicons-chart-bar"></span>' +
                            '<div class="shahi-v3-info-stat-content">' +
                                '<span class="shahi-v3-info-stat-value">' + usageCount + '</span>' +
                                '<span class="shahi-v3-info-stat-label">Total Uses</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="shahi-v3-info-stat-box">' +
                            '<span class="shahi-v3-info-stat-icon dashicons dashicons-performance"></span>' +
                            '<div class="shahi-v3-info-stat-content">' +
                                '<span class="shahi-v3-info-stat-value">' + perfScore + '</span>' +
                                '<span class="shahi-v3-info-stat-label">Performance</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="shahi-v3-info-stat-box">' +
                            '<span class="shahi-v3-info-stat-icon dashicons dashicons-flag"></span>' +
                            '<div class="shahi-v3-info-stat-content">' +
                                '<span class="shahi-v3-info-stat-value status-' + moduleStatus + '">' + (moduleStatus === 'active' ? 'Active' : 'Inactive') + '</span>' +
                                '<span class="shahi-v3-info-stat-label">Status</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="shahi-v3-info-stat-box">' +
                            '<span class="shahi-v3-info-stat-icon dashicons dashicons-category"></span>' +
                            '<div class="shahi-v3-info-stat-content">' +
                                '<span class="shahi-v3-info-stat-value">' + moduleCategory + '</span>' +
                                '<span class="shahi-v3-info-stat-label">Category</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="shahi-v3-info-modal-footer">' +
                    '<button type="button" class="shahi-v3-btn shahi-v3-btn-secondary shahi-v3-info-modal-close-btn">Close</button>' +
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
        
        $modal.find('.shahi-v3-info-modal-close, .shahi-v3-info-modal-close-btn').on('click', closeModal);
        $modal.on('click', function(evt) {
            if ($(evt.target).hasClass('shahi-v3-info-modal-overlay')) {
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
        $(document).on('click', '.shahi-v3-info-btn', showModuleInfo);
    });
    
})(jQuery);
</script>