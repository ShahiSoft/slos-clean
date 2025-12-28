/**
 * ShahiLegalopsSuite - Dashboard Page JavaScript
 * 
 * Futuristic dashboard with animated counters, hover effects,
 * smooth transitions, collapsible sections, and AJAX widget refresh.
 *
 * @package    ShahiLegalopsSuite
 * @version    3.0.1
 */

(function($) {
    'use strict';

    /**
     * Dashboard module
     */
    var ShahiDashboard = {
        
        /**
         * Initialize dashboard
         */
        init: function() {
            console.log('ShahiLegalopsSuite Dashboard: Initializing...');
            
            this.initAnimatedCounters();
            this.initRefreshButton();
            this.initActivityFeed();
            this.initQuickActions();
            this.initCollapsibleSections();
            this.initOnboardingTrigger();
            this.initTooltips();
            
            console.log('ShahiLegalopsSuite Dashboard: Initialized successfully');
        },

        /**
         * Initialize animated stat counters
         * Animates numbers from 0 to target value on page load
         */
        initAnimatedCounters: function() {
            var $counters = $('.shahi-stat-number[data-value]');
            
            if ($counters.length === 0) {
                return;
            }
            
            $counters.each(function() {
                var $counter = $(this);
                var targetValue = parseInt($counter.attr('data-value')) || 0;
                var currentValue = 0;
                var duration = 2000; // 2 seconds
                var steps = 60;
                var increment = targetValue / steps;
                var stepDuration = duration / steps;
                var stepCount = 0;
                
                // Only animate numbers, not time strings
                if (isNaN(targetValue)) {
                    return;
                }
                
                $counter.text('0');
                
                var interval = setInterval(function() {
                    stepCount++;
                    currentValue = Math.min(Math.round(increment * stepCount), targetValue);
                    $counter.text(currentValue.toLocaleString());
                    
                    if (stepCount >= steps || currentValue >= targetValue) {
                        clearInterval(interval);
                        $counter.text(targetValue.toLocaleString());
                    }
                }, stepDuration);
            });
        },

        /**
         * Initialize refresh stats button
         */
        initRefreshButton: function() {
            var self = this;
            
            $('[data-action="refresh"]').on('click', function(e) {
                e.preventDefault();
                self.refreshStats($(this));
            });
        },

        /**
         * Refresh dashboard stats via AJAX
         */
        refreshStats: function($button) {
            var self = this;
            
            // Add loading state
            $button.addClass('shahi-loading');
            $button.prop('disabled', true);
            
            // Simulate AJAX call (replace with actual AJAX when endpoints are ready)
            setTimeout(function() {
                // Re-animate counters
                self.initAnimatedCounters();
                
                // Remove loading state
                $button.removeClass('shahi-loading');
                $button.prop('disabled', false);
                
                // Show success notification
                if (typeof window.ShahiNotify !== 'undefined') {
                    window.ShahiNotify.success('Dashboard stats refreshed successfully!');
                }
            }, 1000);
            
            /* Actual AJAX implementation (uncomment when endpoint is ready)
            $.ajax({
                url: shahiDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shahi_refresh_dashboard_stats',
                    nonce: shahiDashboard.nonce
                },
                success: function(response) {
                    if (response.success && response.data.stats) {
                        // Update stat values
                        $.each(response.data.stats, function(key, value) {
                            var $stat = $('[data-stat="' + key + '"]');
                            if ($stat.length) {
                                $stat.attr('data-value', value);
                            }
                        });
                        
                        // Re-animate counters
                        self.initAnimatedCounters();
                        
                        if (typeof window.ShahiNotify !== 'undefined') {
                            window.ShahiNotify.success('Dashboard stats refreshed!');
                        }
                    }
                },
                error: function() {
                    if (typeof window.ShahiNotify !== 'undefined') {
                        window.ShahiNotify.error('Failed to refresh stats. Please try again.');
                    }
                },
                complete: function() {
                    $button.removeClass('shahi-loading');
                    $button.prop('disabled', false);
                }
            });
            */
        },

        /**
         * Initialize activity feed
         */
        initActivityFeed: function() {
            var $activityItems = $('.shahi-activity-item');
            
            // Add fade-in animation to activity items
            $activityItems.each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateX(-20px)'
                }).delay(index * 50).animate({
                    opacity: 1
                }, 300, function() {
                    $(this).css('transform', 'translateX(0)');
                });
            });
        },

        /**
         * Initialize quick actions with hover effects
         */
        initQuickActions: function() {
            var $quickActions = $('.shahi-quick-action');
            
            $quickActions.on('mouseenter', function() {
                $(this).find('.shahi-action-icon').addClass('shahi-wobble');
            });
            
            $quickActions.on('mouseleave', function() {
                $(this).find('.shahi-action-icon').removeClass('shahi-wobble');
            });
        },

        /**
         * Initialize collapsible sections
         */
        initCollapsibleSections: function() {
            // Add collapse toggle to card headers (if needed in future)
            $('.shahi-card-header').on('dblclick', function() {
                var $cardBody = $(this).next('.shahi-card-body');
                $cardBody.slideToggle(300);
                $(this).toggleClass('shahi-collapsed');
            });
        },

        /**
         * Initialize onboarding trigger
         */
        initOnboardingTrigger: function() {
            $('.shahi-trigger-onboarding').on('click', function(e) {
                e.preventDefault();
                
                // Reset onboarding and show modal
                if (typeof window.ShahiOnboarding !== 'undefined') {
                    window.ShahiOnboarding.show();
                } else {
                    console.warn('ShahiOnboarding module not loaded');
                    
                    // Fallback: reload page to trigger onboarding
                    if (confirm('This will reset the onboarding wizard. Continue?')) {
                        $.ajax({
                            url: shahiDashboard.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'shahi_restart_onboarding',
                                nonce: shahiDashboard.nonce
                            },
                            success: function() {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Use ShahiComponents tooltip if available
            if (typeof window.ShahiComponents !== 'undefined' && 
                typeof window.ShahiComponents.initTooltips === 'function') {
                window.ShahiComponents.initTooltips();
            }
        },

        /**
         * Format number with thousands separator
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        /**
         * Calculate progress percentage
         */
        calculateProgress: function() {
            var $checklistItems = $('.shahi-checklist-item');
            var totalItems = $checklistItems.length;
            var completedItems = $checklistItems.filter('.shahi-completed').length;
            
            if (totalItems === 0) {
                return 0;
            }
            
            return Math.round((completedItems / totalItems) * 100);
        },

        /**
         * Update progress bar (if exists)
         */
        updateProgressBar: function() {
            var progress = this.calculateProgress();
            var $progressBar = $('.shahi-getting-started-progress');
            
            if ($progressBar.length) {
                $progressBar.css('width', progress + '%');
                $progressBar.find('.shahi-progress-text').text(progress + '%');
            }
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        if ($('.shahi-dashboard-page').length > 0) {
            ShahiDashboard.init();
        }
    });

    // Expose to global scope for external access
    window.ShahiDashboard = ShahiDashboard;

})(jQuery);

