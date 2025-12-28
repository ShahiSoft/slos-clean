<?php
/**
 * Modules Page Template
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalflowsuite-admin shahi-modules-page">
    
    <!-- Page Header -->
    <div class="shahi-page-header">
        <div class="shahi-header-content">
            <h1 class="shahi-page-title">
                <span class="dashicons dashicons-admin-plugins"></span>
                <?php echo esc_html__('Modules', 'shahi-legalflowsuite'); ?>
            </h1>
            <p class="shahi-page-description">
                <?php echo esc_html__('Enable or disable plugin features to customize functionality. Changes are instant with AJAX toggle.', 'shahi-legalflowsuite'); ?>
            </p>
        </div>
        <div class="shahi-header-actions">
            <button type="button" id="shahi-enable-all-modules" class="shahi-btn shahi-btn-success">
                <span class="dashicons dashicons-yes"></span>
                <?php echo esc_html__('Enable All', 'shahi-legalflowsuite'); ?>
            </button>
            <button type="button" id="shahi-disable-all-modules" class="shahi-btn shahi-btn-secondary">
                <span class="dashicons dashicons-no"></span>
                <?php echo esc_html__('Disable All', 'shahi-legalflowsuite'); ?>
            </button>
        </div>
    </div>
    
    <!-- Module Statistics -->
    <div class="shahi-modules-stats">
        <div class="shahi-stat-card">
            <div class="shahi-stat-value"><?php echo count($modules); ?></div>
            <div class="shahi-stat-label"><?php echo esc_html__('Total Modules', 'shahi-legalflowsuite'); ?></div>
        </div>
        <div class="shahi-stat-card shahi-stat-success">
            <div class="shahi-stat-value"><?php echo $active_count; ?></div>
            <div class="shahi-stat-label"><?php echo esc_html__('Active Modules', 'shahi-legalflowsuite'); ?></div>
        </div>
        <div class="shahi-stat-card shahi-stat-secondary">
            <div class="shahi-stat-value"><?php echo count($modules) - $active_count; ?></div>
            <div class="shahi-stat-label"><?php echo esc_html__('Inactive Modules', 'shahi-legalflowsuite'); ?></div>
        </div>
    </div>

    <!-- Settings Messages -->
    <?php settings_errors('shahi_modules'); ?>

    <!-- Module Grid -->
    <div class="shahi-modules-grid">
        <?php foreach ($modules as $module): ?>
            <div class="shahi-module-card <?php echo $module['enabled'] ? 'shahi-module-active' : ''; ?>" 
                 data-module-key="<?php echo esc_attr($module['key']); ?>">
                
                <!-- Module Status Indicator -->
                <div class="shahi-module-status-indicator"></div>
                
                <div class="shahi-module-header">
                    <div class="shahi-module-icon">
                        <span class="dashicons <?php echo esc_attr($module['icon']); ?>"></span>
                    </div>
                    <div class="shahi-module-title-wrapper">
                        <h3 class="shahi-module-title"><?php echo esc_html($module['name']); ?></h3>
                        <span class="shahi-module-category shahi-badge shahi-badge-<?php echo esc_attr($module['category']); ?>">
                            <?php echo esc_html(ucfirst($module['category'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="shahi-module-body">
                    <p class="shahi-module-description">
                        <?php echo esc_html($module['description']); ?>
                    </p>
                    
                    <?php if (!empty($module['dependencies'])): ?>
                        <div class="shahi-module-dependencies">
                            <span class="dashicons dashicons-info"></span>
                            <?php 
                            echo sprintf(
                                esc_html__('Requires: %s', 'shahi-legalflowsuite'),
                                '<strong>' . esc_html(implode(', ', $module['dependencies'])) . '</strong>'
                            ); 
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="shahi-module-footer">
                    <div class="shahi-module-meta">
                        <span class="shahi-module-version">
                            <?php echo esc_html__('v', 'shahi-legalflowsuite') . esc_html($module['version']); ?>
                        </span>
                        <span class="shahi-module-separator">•</span>
                        <span class="shahi-module-author">
                            <?php echo esc_html($module['author']); ?>
                        </span>
                        <?php if (!empty($module['settings_url'])): ?>
                            <span class="shahi-module-separator">•</span>
                            <a href="<?php echo esc_url($module['settings_url']); ?>" 
                               class="shahi-module-settings-link"
                               title="<?php echo esc_attr__('Module Settings', 'shahi-legalflowsuite'); ?>">
                                <span class="dashicons dashicons-admin-generic"></span>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="shahi-module-actions">
                        <label class="shahi-toggle-switch" 
                               title="<?php echo $module['enabled'] ? esc_attr__('Disable Module', 'shahi-legalflowsuite') : esc_attr__('Enable Module', 'shahi-legalflowsuite'); ?>">
                            <input type="checkbox" 
                                   class="shahi-module-toggle"
                                   data-module-key="<?php echo esc_attr($module['key']); ?>"
                                   <?php checked($module['enabled']); ?>>
                            <span class="shahi-toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Empty State (if no modules) -->
    <?php if (empty($modules)): ?>
        <div class="shahi-empty-state">
            <span class="dashicons dashicons-admin-plugins"></span>
            <h3><?php echo esc_html__('No Modules Available', 'shahi-legalflowsuite'); ?></h3>
            <p><?php echo esc_html__('No modules have been registered yet.', 'shahi-legalflowsuite'); ?></p>
        </div>
    <?php endif; ?>

</div>

