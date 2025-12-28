<?php
/**
 * Onboarding Modal Template
 *
 * Multi-step onboarding wizard for first-time setup.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$onboarding = new \ShahiLegalFlowSuite\Admin\Onboarding();
$steps = $onboarding->get_steps();
$purpose_options = $onboarding->get_purpose_options();
$available_modules = $onboarding->get_available_modules();
?>

<div id="shahi-onboarding-overlay" class="shahi-onboarding-overlay">
    <div class="shahi-onboarding-modal">
        
        <!-- Close Button -->
        <button type="button" class="shahi-onboarding-close" id="shahi-onboarding-skip" title="<?php echo esc_attr__('Skip onboarding', 'shahi-legalflowsuite'); ?>">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
        
        <!-- Progress Indicator -->
        <div class="shahi-onboarding-progress">
            <div class="shahi-progress-bar">
                <div class="shahi-progress-fill" id="shahi-progress-fill"></div>
            </div>
            <div class="shahi-progress-text">
                <span id="shahi-current-step">1</span> of <span id="shahi-total-steps">5</span>
            </div>
        </div>
        
        <!-- Step 1: Welcome -->
        <div class="shahi-onboarding-step active" data-step="1">
            <div class="shahi-step-icon">
                <span class="dashicons dashicons-welcome-learn-more"></span>
            </div>
            <h2 class="shahi-step-title"><?php echo esc_html($steps['welcome']['title']); ?></h2>
            <p class="shahi-step-subtitle"><?php echo esc_html($steps['welcome']['subtitle']); ?></p>
            
            <div class="shahi-step-content">
                <div class="shahi-welcome-content">
                    <div class="shahi-welcome-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <div>
                            <h4><?php echo esc_html__('Dark Futuristic Interface', 'shahi-legalflowsuite'); ?></h4>
                            <p><?php echo esc_html__('Modern, beautiful admin design', 'shahi-legalflowsuite'); ?></p>
                        </div>
                    </div>
                    <div class="shahi-welcome-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <div>
                            <h4><?php echo esc_html__('Modular Architecture', 'shahi-legalflowsuite'); ?></h4>
                            <p><?php echo esc_html__('Enable only the features you need', 'shahi-legalflowsuite'); ?></p>
                        </div>
                    </div>
                    <div class="shahi-welcome-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <div>
                            <h4><?php echo esc_html__('Analytics & Insights', 'shahi-legalflowsuite'); ?></h4>
                            <p><?php echo esc_html__('Track performance and user behavior', 'shahi-legalflowsuite'); ?></p>
                        </div>
                    </div>
                    <div class="shahi-welcome-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <div>
                            <h4><?php echo esc_html__('Enterprise Ready', 'shahi-legalflowsuite'); ?></h4>
                            <p><?php echo esc_html__('Built with best practices and security', 'shahi-legalflowsuite'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Purpose -->
        <div class="shahi-onboarding-step" data-step="2">
            <div class="shahi-step-icon">
                <span class="dashicons dashicons-admin-tools"></span>
            </div>
            <h2 class="shahi-step-title"><?php echo esc_html($steps['purpose']['title']); ?></h2>
            <p class="shahi-step-subtitle"><?php echo esc_html($steps['purpose']['subtitle']); ?></p>
            
            <div class="shahi-step-content">
                <div class="shahi-purpose-grid">
                    <?php foreach ($purpose_options as $key => $option): ?>
                        <label class="shahi-purpose-option">
                            <input type="radio" name="purpose" value="<?php echo esc_attr($key); ?>" <?php checked($key, 'business'); ?>>
                            <div class="shahi-purpose-card">
                                <span class="dashicons <?php echo esc_attr($option['icon']); ?>"></span>
                                <h4><?php echo esc_html($option['label']); ?></h4>
                                <p><?php echo esc_html($option['description']); ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Step 3: Features -->
        <div class="shahi-onboarding-step" data-step="3">
            <div class="shahi-step-icon">
                <span class="dashicons dashicons-admin-plugins"></span>
            </div>
            <h2 class="shahi-step-title"><?php echo esc_html($steps['features']['title']); ?></h2>
            <p class="shahi-step-subtitle"><?php echo esc_html($steps['features']['subtitle']); ?></p>
            
            <div class="shahi-step-content">
                <div class="shahi-onboarding-modules-grid">
                    <?php foreach ($available_modules as $key => $module): ?>
                        <div class="shahi-onboarding-module-card <?php echo in_array($key, ['analytics']) ? 'active' : ''; ?>" 
                             data-module="<?php echo esc_attr($key); ?>">
                            
                            <!-- Card Background Effects -->
                            <div class="shahi-card-bg-effect"></div>
                            <div class="shahi-card-glow"></div>
                            
                            <!-- Module Header -->
                            <div class="shahi-module-card-header">
                                <div class="shahi-module-icon-wrapper">
                                    <span class="dashicons <?php echo esc_attr($module['icon']); ?>"></span>
                                    <div class="shahi-icon-pulse"></div>
                                </div>
                            </div>

                            <!-- Module Content -->
                            <div class="shahi-module-card-body">
                                <h3 class="shahi-module-title"><?php echo esc_html($module['name']); ?></h3>
                                <p class="shahi-module-description"><?php echo esc_html($module['description']); ?></p>
                            </div>

                            <!-- Module Footer with Toggle -->
                            <div class="shahi-module-card-footer">
                                <!-- Toggle Switch -->
                                <label class="shahi-toggle-switch-premium">
                                    <input type="checkbox" 
                                           class="shahi-onboarding-module-toggle"
                                           name="modules[]"
                                           value="<?php echo esc_attr($key); ?>"
                                           data-module="<?php echo esc_attr($key); ?>"
                                           <?php checked(in_array($key, ['analytics'])); ?>>
                                    <span class="shahi-toggle-slider">
                                        <span class="shahi-toggle-icon shahi-toggle-icon-on">
                                            <span class="dashicons dashicons-yes"></span>
                                        </span>
                                        <span class="shahi-toggle-icon shahi-toggle-icon-off">
                                            <span class="dashicons dashicons-no"></span>
                                        </span>
                                    </span>
                                </label>
                                
                                <div class="shahi-module-status-badge">
                                    <span class="shahi-status-active">
                                        <span class="shahi-status-dot"></span>
                                        <?php echo esc_html__('Selected', 'shahi-legalflowsuite'); ?>
                                    </span>
                                    <span class="shahi-status-inactive">
                                        <span class="shahi-status-dot"></span>
                                        <?php echo esc_html__('Not Selected', 'shahi-legalflowsuite'); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Status Border -->
                            <div class="shahi-card-status-border"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Step 4: Configuration -->
        <div class="shahi-onboarding-step" data-step="4">
            <div class="shahi-step-icon">
                <span class="dashicons dashicons-admin-settings"></span>
            </div>
            <h2 class="shahi-step-title"><?php echo esc_html($steps['configuration']['title']); ?></h2>
            <p class="shahi-step-subtitle"><?php echo esc_html($steps['configuration']['subtitle']); ?></p>
            
            <div class="shahi-step-content">
                <div class="shahi-config-form">
                    <div class="shahi-config-row">
                        <label class="shahi-config-label">
                            <input type="checkbox" name="settings[enable_analytics]" value="1" checked>
                            <div class="shahi-config-details">
                                <h4><?php echo esc_html__('Enable Analytics Tracking', 'shahi-legalflowsuite'); ?></h4>
                                <p><?php echo esc_html__('Track plugin events and user behavior for insights', 'shahi-legalflowsuite'); ?></p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="shahi-config-row">
                        <label class="shahi-config-label">
                            <input type="checkbox" name="settings[enable_notifications]" value="1">
                            <div class="shahi-config-details">
                                <h4><?php echo esc_html__('Enable Email Notifications', 'shahi-legalflowsuite'); ?></h4>
                                <p><?php echo esc_html__('Receive email alerts for important events', 'shahi-legalflowsuite'); ?></p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="shahi-config-info">
                        <span class="dashicons dashicons-info"></span>
                        <p><?php echo esc_html__('You can change these settings later from the Settings page.', 'shahi-legalflowsuite'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Step 5: Complete -->
        <div class="shahi-onboarding-step" data-step="5">
            <div class="shahi-step-icon shahi-icon-success">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2 class="shahi-step-title"><?php echo esc_html($steps['complete']['title']); ?></h2>
            <p class="shahi-step-subtitle"><?php echo esc_html($steps['complete']['subtitle']); ?></p>
            
            <div class="shahi-step-content">
                <div class="shahi-completion-content">
                    <div class="shahi-confetti" id="shahi-confetti"></div>
                    
                    <div class="shahi-completion-message">
                        <p><?php echo esc_html__('ShahiLegalFlowSuite has been configured based on your preferences. You can now start exploring the features!', 'shahi-legalflowsuite'); ?></p>
                    </div>
                    
                    <div class="shahi-quick-links">
                        <h4><?php echo esc_html__('Quick Links:', 'shahi-legalflowsuite'); ?></h4>
                        <div class="shahi-links-grid">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalflowsuite')); ?>" class="shahi-quick-link">
                                <span class="dashicons dashicons-dashboard"></span>
                                <span><?php echo esc_html__('View Dashboard', 'shahi-legalflowsuite'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalflowsuite-module-dashboard')); ?>" class="shahi-quick-link">
                                <span class="dashicons dashicons-admin-plugins"></span>
                                <span><?php echo esc_html__('Manage Modules', 'shahi-legalflowsuite'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalflowsuite-settings')); ?>" class="shahi-quick-link">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <span><?php echo esc_html__('Configure Settings', 'shahi-legalflowsuite'); ?></span>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalflowsuite-support')); ?>" class="shahi-quick-link">
                                <span class="dashicons dashicons-sos"></span>
                                <span><?php echo esc_html__('Get Support', 'shahi-legalflowsuite'); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="shahi-onboarding-nav">
            <button type="button" class="shahi-btn shahi-btn-secondary" id="shahi-prev-btn" disabled>
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php echo esc_html__('Previous', 'shahi-legalflowsuite'); ?>
            </button>
            
            <div class="shahi-nav-spacer"></div>
            
            <button type="button" class="shahi-btn shahi-btn-primary" id="shahi-next-btn">
                <?php echo esc_html__('Next', 'shahi-legalflowsuite'); ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
            
            <button type="button" class="shahi-btn shahi-btn-primary" id="shahi-finish-btn" style="display: none;">
                <?php echo esc_html__('Get Started', 'shahi-legalflowsuite'); ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
        </div>
        
    </div>
</div>

<script type="text/javascript">
// Inline initialization script
console.log('SHAHI ONBOARDING: Template script loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('SHAHI ONBOARDING: DOM ready');
    console.log('SHAHI ONBOARDING: Modal element exists:', document.getElementById('shahi-onboarding-overlay') !== null);
    if (typeof ShahiOnboarding !== 'undefined') {
        console.log('SHAHI ONBOARDING: ShahiOnboarding object found, calling init()');
        ShahiOnboarding.init();
    } else {
        console.error('SHAHI ONBOARDING: ShahiOnboarding object NOT FOUND - Script not loaded!');
    }
});
</script>

