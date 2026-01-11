/**
 * SLOS Scan Progress Popup Module
 *
 * Provides a professional modal popup for displaying scan progress
 * with real-time updates, page status, and accessibility features.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner
 * @version    1.0.0
 * @since      3.2.0
 *
 * WCAG 2.1 AA Compliance:
 * - 2.1.2 No Keyboard Trap - Escape key closes modal
 * - 2.4.3 Focus Order - Focus trapped within modal
 * - 2.4.7 Focus Visible - Clear focus indicators
 * - 4.1.3 Status Messages - ARIA live region announcements
 */

(function($) {
    'use strict';

    // Get configuration from localized script or fallback to globals
    const config = window.slosScanConfig || {};
    const ajaxUrl = config.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php';
    const nonce = config.nonce || (window.slosScanner && window.slosScanner.nonce) || '';

    /**
     * SLOS Scan Progress Module
     */
    window.SLOSScanProgress = {

        /**
         * Configuration
         */
        config: {
            animationDuration: 300,
            scrollBehavior: 'smooth',
            maxVisiblePages: 10,
            pollingInterval: 100,
            announceDelay: 500,
            ajaxTimeout: 30000, // 30 seconds per page
            maxRetries: 2,
            retryDelay: 1000,
            batchSize: 3, // Parallel requests
        },

        /**
         * State management
         */
        state: {
            isOpen: false,
            isScanning: false,
            isCancelled: false,
            totalPages: 0,
            scannedPages: 0,
            totalIssues: 0,
            criticalIssues: 0,
            pagesWithIssues: 0,
            pages: [],
            activeRequests: [],
            triggerElement: null,
            onComplete: null,
            scanResults: [],
        },

        /**
         * DOM element references
         */
        elements: {
            overlay: null,
            modal: null,
            closeBtn: null,
            progressBar: null,
            progressPercent: null,
            progressStatus: null,
            pageList: null,
            summaryScanned: null,
            summaryIssues: null,
            summaryCritical: null,
            summaryClean: null,
            cancelBtn: null,
            closeActionBtn: null,
            viewResultsBtn: null,
            liveRegion: null,
        },

        /**
         * Initialize the module
         */
        init: function() {
            console.log('SLOSScanProgress: Initializing');
            this.createModal();
            this.bindEvents();
            this.createLiveRegion();
            console.log('SLOSScanProgress: Initialized successfully');
        },

        /**
         * Create the modal DOM structure
         */
        createModal: function() {
            // Check if modal already exists
            if ($('#slos-scan-progress-overlay').length) {
                this.cacheElements();
                return;
            }

            const modalHTML = `
                <div id="slos-scan-progress-overlay" class="slos-scan-overlay" role="dialog" aria-modal="true" aria-labelledby="slos-scan-title" aria-describedby="slos-scan-desc">
                    <div class="slos-scan-modal">
                        <!-- Header -->
                        <div class="slos-scan-header">
                            <div class="slos-scan-header-title">
                                <div class="slos-scan-icon">
                                    <span class="dashicons dashicons-search"></span>
                                </div>
                                <div>
                                    <h2 id="slos-scan-title">Accessibility Scan Progress</h2>
                                    <div class="slos-scan-header-subtitle" id="slos-scan-desc">Scanning your site for accessibility issues</div>
                                </div>
                            </div>
                            <button type="button" class="slos-scan-close" aria-label="Close dialog">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>

                        <!-- Progress Section -->
                        <div class="slos-scan-progress-section">
                            <div class="slos-scan-progress-wrap processing">
                                <div class="slos-scan-progress-bar" style="width: 0%"></div>
                            </div>
                            <div class="slos-scan-progress-text">
                                <span class="slos-scan-progress-percent">0%</span>
                                <span class="slos-scan-progress-status">Initializing...</span>
                            </div>
                        </div>

                        <!-- Page List Section -->
                        <div class="slos-scan-page-section">
                            <div class="slos-scan-page-header">Page Status</div>
                            <div class="slos-scan-page-list" role="list" aria-label="List of pages being scanned"></div>
                        </div>

                        <!-- Summary Section -->
                        <div class="slos-scan-summary">
                            <div class="slos-scan-summary-grid">
                                <div class="slos-scan-stat scanned">
                                    <div class="slos-scan-stat-value" data-stat="scanned">0</div>
                                    <div class="slos-scan-stat-label">Scanned</div>
                                </div>
                                <div class="slos-scan-stat issues">
                                    <div class="slos-scan-stat-value" data-stat="issues">0</div>
                                    <div class="slos-scan-stat-label">Total Issues</div>
                                </div>
                                <div class="slos-scan-stat critical">
                                    <div class="slos-scan-stat-value" data-stat="critical">0</div>
                                    <div class="slos-scan-stat-label">Critical</div>
                                </div>
                                <div class="slos-scan-stat clean">
                                    <div class="slos-scan-stat-value" data-stat="clean">0</div>
                                    <div class="slos-scan-stat-label">Clean Pages</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Section -->
                        <div class="slos-scan-actions">
                            <button type="button" class="slos-scan-btn slos-scan-btn-cancel">
                                <span class="dashicons dashicons-no"></span>
                                Cancel Scan
                            </button>
                            <button type="button" class="slos-scan-btn slos-scan-btn-secondary slos-scan-close-action" style="display: none;">
                                <span class="dashicons dashicons-dismiss"></span>
                                Close
                            </button>
                            <button type="button" class="slos-scan-btn slos-scan-btn-primary slos-scan-view-results" style="display: none;">
                                <span class="dashicons dashicons-visibility"></span>
                                View Results
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHTML);
            this.cacheElements();
        },

        /**
         * Cache DOM element references
         */
        cacheElements: function() {
            const $overlay = $('#slos-scan-progress-overlay');
            this.elements.overlay = $overlay;
            this.elements.modal = $overlay.find('.slos-scan-modal');
            this.elements.closeBtn = $overlay.find('.slos-scan-close');
            this.elements.progressBar = $overlay.find('.slos-scan-progress-bar');
            this.elements.progressWrap = $overlay.find('.slos-scan-progress-wrap');
            this.elements.progressPercent = $overlay.find('.slos-scan-progress-percent');
            this.elements.progressStatus = $overlay.find('.slos-scan-progress-status');
            this.elements.pageList = $overlay.find('.slos-scan-page-list');
            this.elements.summaryScanned = $overlay.find('[data-stat="scanned"]');
            this.elements.summaryIssues = $overlay.find('[data-stat="issues"]');
            this.elements.summaryCritical = $overlay.find('[data-stat="critical"]');
            this.elements.summaryClean = $overlay.find('[data-stat="clean"]');
            this.elements.cancelBtn = $overlay.find('.slos-scan-btn-cancel');
            this.elements.closeActionBtn = $overlay.find('.slos-scan-close-action');
            this.elements.viewResultsBtn = $overlay.find('.slos-scan-view-results');
        },

        /**
         * Create ARIA live region for announcements
         */
        createLiveRegion: function() {
            if ($('#slos-scan-live-region').length) {
                this.elements.liveRegion = $('#slos-scan-live-region');
                return;
            }

            const liveRegion = $('<div>', {
                id: 'slos-scan-live-region',
                class: 'sr-only',
                'aria-live': 'polite',
                'aria-atomic': 'true'
            });

            $('body').append(liveRegion);
            this.elements.liveRegion = liveRegion;
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Close button
            $(document).on('click', '.slos-scan-close', function() {
                self.close();
            });

            // Close action button
            $(document).on('click', '.slos-scan-close-action', function() {
                self.close();
            });

            // Cancel button
            $(document).on('click', '.slos-scan-btn-cancel', function() {
                self.cancel();
            });

            // View results button
            $(document).on('click', '.slos-scan-view-results', function() {
                self.viewResults();
            });

            // Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.state.isOpen) {
                    if (self.state.isScanning) {
                        self.cancel();
                    } else {
                        self.close();
                    }
                }
            });

            // Overlay click (close)
            $(document).on('click', '.slos-scan-overlay', function(e) {
                if ($(e.target).hasClass('slos-scan-overlay') && !self.state.isScanning) {
                    self.close();
                }
            });

            // Focus trap
            $(document).on('keydown', '.slos-scan-modal', function(e) {
                if (e.key === 'Tab') {
                    self.trapFocus(e);
                }
            });
        },

        /**
         * Start a full site scan
         *
         * @param {Object} options - Scan options
         */
        start: function(options = {}) {
            const self = this;

            // Reset state
            this.state = $.extend(this.state, {
                isScanning: true,
                isCancelled: false,
                totalPages: 0,
                scannedPages: 0,
                totalIssues: 0,
                criticalIssues: 0,
                pagesWithIssues: 0,
                pages: [],
                activeRequests: [],
                scanResults: [],
                onComplete: options.onComplete || null,
            });

            // Open modal
            this.open();

            // Update UI
            this.updateProgress(0, 'Fetching pages to scan...');
            this.announce('Starting full site scan');

            // Fetch pages to scan
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slos_get_posts_to_scan',
                    nonce: nonce
                },
                success: function(response) {
                    if (!response.success || !response.data || response.data.length === 0) {
                        self.updateProgress(0, 'No pages found to scan');
                        self.complete();
                        return;
                    }

                    self.state.pages = response.data.map(function(page) {
                        return {
                            id: page.id,
                            title: page.title,
                            status: 'pending',
                            issues: 0,
                            critical: 0,
                            error: null
                        };
                    });

                    self.state.totalPages = self.state.pages.length;
                    self.renderPageList();
                    self.updateProgress(0, 'Starting scan of ' + self.state.totalPages + ' pages...');
                    self.announce('Scanning ' + self.state.totalPages + ' pages');

                    // Start scanning
                    self.scanPages();
                },
                error: function() {
                    self.updateProgress(0, 'Error fetching pages. Please try again.');
                    self.complete();
                }
            });
        },

        /**
         * Scan all pages in batches
         */
        scanPages: function() {
            const self = this;
            const queue = this.state.pages.slice();
            let activeCount = 0;

            function processNext() {
                while (activeCount < self.config.batchSize && queue.length > 0) {
                    const page = queue.shift();
                    activeCount++;
                    self.scanPage(page, function() {
                        activeCount--;
                        if (self.state.scannedPages >= self.state.totalPages || self.state.isCancelled) {
                            self.finishScan();
                        } else {
                            processNext();
                        }
                    });
                }
            }

            processNext();
        },

        /**
         * Scan a single page
         *
         * @param {Object} page - Page to scan
         * @param {Function} callback - Completion callback
         */
        scanPage: function(page, callback) {
            const self = this;

            // Update page status
            page.status = 'scanning';
            this.updatePageItem(page);

            const xhr = $.ajax({
                url: ajaxUrl,
                type: 'POST',
                timeout: this.config.ajaxTimeout,
                data: {
                    action: 'slos_scan_single_post',
                    nonce: nonce,
                    post_id: page.id
                },
                success: function(response) {
                    if (response.success && response.data) {
                        page.issues = response.data.issues_count || 0;
                        page.critical = response.data.critical_count || 0;
                        page.status = page.issues > 0 ? (page.critical > 0 ? 'warning' : 'success') : 'success';

                        // Update totals
                        self.state.totalIssues += page.issues;
                        self.state.criticalIssues += page.critical;
                        if (page.issues > 0) {
                            self.state.pagesWithIssues++;
                        }

                        // Store results
                        self.state.scanResults.push({
                            post_id: page.id,
                            title: page.title,
                            issues: page.issues,
                            critical: page.critical,
                            score: response.data.score || 100
                        });
                    } else {
                        page.status = 'error';
                        page.error = 'Scan failed';
                    }
                },
                error: function() {
                    page.status = 'error';
                    page.error = 'Connection error';
                },
                complete: function() {
                    self.state.activeRequests = self.state.activeRequests.filter(function(r) {
                        return r !== xhr;
                    });

                    self.state.scannedPages++;
                    self.updatePageItem(page);
                    self.updateProgress(
                        Math.round((self.state.scannedPages / self.state.totalPages) * 100),
                        'Scanning ' + self.state.scannedPages + ' of ' + self.state.totalPages + ' pages...'
                    );
                    self.updateSummary();

                    callback();
                }
            });

            this.state.activeRequests.push(xhr);
        },

        /**
         * Finish scan and consolidate results
         */
        finishScan: function() {
            const self = this;

            if (this.state.isCancelled) {
                this.updateProgress(
                    Math.round((this.state.scannedPages / this.state.totalPages) * 100),
                    'Scan cancelled. Scanned ' + this.state.scannedPages + ' of ' + this.state.totalPages + ' pages.'
                );
                this.announce('Scan cancelled');
                this.complete();
                return;
            }

            this.updateProgress(100, 'Consolidating results...');

            // Consolidate results on server
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slos_consolidate_scan_results',
                    nonce: nonce
                },
                complete: function() {
                    const cleanPages = self.state.totalPages - self.state.pagesWithIssues;
                    self.updateProgress(
                        100,
                        'Scan complete! Found ' + self.state.totalIssues + ' issues across ' + self.state.pagesWithIssues + ' pages.'
                    );
                    self.announce('Scan complete. Found ' + self.state.totalIssues + ' accessibility issues');
                    self.complete();
                }
            });
        },

        /**
         * Render the page list
         */
        renderPageList: function() {
            const self = this;
            this.elements.pageList.empty();

            this.state.pages.slice(0, this.config.maxVisiblePages).forEach(function(page) {
                self.createPageItem(page);
            });

            if (this.state.pages.length > this.config.maxVisiblePages) {
                const remaining = this.state.pages.length - this.config.maxVisiblePages;
                this.elements.pageList.append(
                    '<div class="slos-scan-page-item" style="justify-content: center; opacity: 0.6;">' +
                    '<span class="slos-scan-page-title">+ ' + remaining + ' more pages</span>' +
                    '</div>'
                );
            }
        },

        /**
         * Create a page item element
         *
         * @param {Object} page - Page data
         */
        createPageItem: function(page) {
            const statusIcon = this.getStatusIcon(page.status);
            const issuesBadge = page.issues > 0 ? 
                '<span class="slos-scan-issue-badge total">' +
                '<span class="dashicons dashicons-warning"></span>' +
                page.issues +
                '</span>' : '';
            const criticalBadge = page.critical > 0 ?
                '<span class="slos-scan-issue-badge critical">' +
                '<span class="dashicons dashicons-dismiss"></span>' +
                page.critical + ' critical' +
                '</span>' : '';

            const item = $('<div>', {
                class: 'slos-scan-page-item',
                'data-page-id': page.id,
                role: 'listitem'
            }).html(
                '<div class="slos-scan-page-status ' + page.status + '">' + statusIcon + '</div>' +
                '<div class="slos-scan-page-info">' +
                '<div class="slos-scan-page-title">' + this.escapeHtml(page.title) + '</div>' +
                '</div>' +
                '<div class="slos-scan-page-issues">' + issuesBadge + criticalBadge + '</div>'
            );

            this.elements.pageList.append(item);
        },

        /**
         * Update a page item
         *
         * @param {Object} page - Page data
         */
        updatePageItem: function(page) {
            const $item = this.elements.pageList.find('[data-page-id="' + page.id + '"]');
            if (!$item.length) return;

            const statusIcon = this.getStatusIcon(page.status);
            const issuesBadge = page.issues > 0 ? 
                '<span class="slos-scan-issue-badge total">' +
                '<span class="dashicons dashicons-warning"></span>' +
                page.issues +
                '</span>' : '';
            const criticalBadge = page.critical > 0 ?
                '<span class="slos-scan-issue-badge critical">' +
                '<span class="dashicons dashicons-dismiss"></span>' +
                page.critical + ' critical' +
                '</span>' : '';

            $item.find('.slos-scan-page-status')
                .removeClass('pending scanning success warning error')
                .addClass(page.status)
                .html(statusIcon);

            $item.find('.slos-scan-page-issues')
                .html(issuesBadge + criticalBadge);
        },

        /**
         * Get status icon HTML
         *
         * @param {string} status - Status type
         * @return {string} Icon HTML
         */
        getStatusIcon: function(status) {
            const icons = {
                pending: '<span class="dashicons dashicons-minus"></span>',
                scanning: '<span class="dashicons dashicons-update"></span>',
                success: '<span class="dashicons dashicons-yes"></span>',
                warning: '<span class="dashicons dashicons-warning"></span>',
                error: '<span class="dashicons dashicons-dismiss"></span>'
            };
            return icons[status] || icons.pending;
        },

        /**
         * Update progress bar and status
         *
         * @param {number} percent - Progress percentage
         * @param {string} status - Status message
         */
        updateProgress: function(percent, status) {
            this.elements.progressBar.css('width', percent + '%');
            this.elements.progressPercent.text(percent + '%');
            if (status) {
                this.elements.progressStatus.text(status);
            }
        },

        /**
         * Update summary statistics
         */
        updateSummary: function() {
            const cleanPages = this.state.scannedPages - this.state.pagesWithIssues;
            this.elements.summaryScanned.text(this.state.scannedPages);
            this.elements.summaryIssues.text(this.state.totalIssues);
            this.elements.summaryCritical.text(this.state.criticalIssues);
            this.elements.summaryClean.text(cleanPages);
        },

        /**
         * Complete the scan
         */
        complete: function() {
            this.state.isScanning = false;
            this.elements.progressWrap.removeClass('processing');
            this.elements.cancelBtn.hide();
            this.elements.closeActionBtn.show();
            this.elements.viewResultsBtn.show();

            // Call completion callback
            if (typeof this.state.onComplete === 'function') {
                this.state.onComplete(this.state.scanResults);
            }
        },

        /**
         * Cancel the scan
         */
        cancel: function() {
            const confirmMsg = (config.i18n && config.i18n.confirmCancel) || 'Are you sure you want to cancel the scan?';
            if (!confirm(confirmMsg)) {
                return;
            }

            this.state.isCancelled = true;
            this.state.isScanning = false;

            // Abort all active requests
            this.state.activeRequests.forEach(function(xhr) {
                xhr.abort();
            });
            this.state.activeRequests = [];

            this.announce('Scan cancelled');
            this.finishScan();
        },

        /**
         * View scan results
         */
        viewResults: function() {
            // Reload the page to show results
            window.location.reload();
        },

        /**
         * Open the modal
         */
        open: function() {
            this.state.isOpen = true;
            this.elements.overlay.addClass('show');
            this.trapFocus();
            this.announce('Scan progress dialog opened');

            // Store trigger element for focus restoration
            this.state.triggerElement = document.activeElement;

            // Set initial focus to close button
            setTimeout(() => {
                this.elements.closeBtn.focus();
            }, this.config.animationDuration);
        },

        /**
         * Close the modal
         */
        close: function() {
            if (this.state.isScanning) {
                this.cancel();
                return;
            }

            this.state.isOpen = false;
            this.elements.overlay.removeClass('show');
            this.announce('Scan progress dialog closed');

            // Restore focus to trigger element
            if (this.state.triggerElement) {
                this.state.triggerElement.focus();
            }
        },

        /**
         * Trap focus within modal
         *
         * @param {Event} e - Keyboard event
         */
        trapFocus: function(e) {
            const focusableElements = this.elements.modal.find(
                'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );

            if (focusableElements.length === 0) return;

            const firstElement = focusableElements.first();
            const lastElement = focusableElements.last();

            if (e && e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement[0]) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement[0]) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        },

        /**
         * Announce to screen readers
         *
         * @param {string} message - Message to announce
         */
        announce: function(message) {
            const self = this;
            if (!this.elements.liveRegion) return;

            setTimeout(function() {
                self.elements.liveRegion.text(message);

                setTimeout(function() {
                    self.elements.liveRegion.text('');
                }, 1000);
            }, this.config.announceDelay);
        },

        /**
         * Escape HTML
         *
         * @param {string} text - Text to escape
         * @return {string} Escaped text
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        SLOSScanProgress.init();
    });

})(jQuery);
