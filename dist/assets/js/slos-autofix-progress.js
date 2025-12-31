/**
 * SLOS Auto-Fix Progress Popup Module
 *
 * Provides a professional modal popup for displaying auto-fix progress
 * with real-time updates, fixer status, and accessibility features.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Fixes
 * @version    1.0.0
 * @since      3.2.0
 *
 * WCAG 2.1 AA Compliance:
 * - 2.1.2 No Keyboard Trap - Escape key closes modal
 * - 2.4.3 Focus Order - Focus trapped within modal
 * - 2.4.7 Focus Visible - Clear focus indicators
 * - 4.1.3 Status Messages - ARIA live region announcements
 */

(function() {
    'use strict';

    /**
     * SLOS Auto-Fix Progress Module
     */
    const SLOSAutoFixProgress = {

        /**
         * Configuration
         */
        config: {
            animationDuration: 300,
            scrollBehavior: 'smooth',
            maxVisibleFixers: 8,
            pollingInterval: 100,
            announceDelay: 500,
        },

        /**
         * State management
         */
        state: {
            isOpen: false,
            isProcessing: false,
            isCancelled: false,
            totalFixers: 0,
            completedFixers: 0,
            fixedCount: 0,
            errorCount: 0,
            skippedCount: 0,
            fixers: [],
            currentXHR: null,
            triggerElement: null,
            onComplete: null,
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
            fixerList: null,
            summaryFixed: null,
            summaryErrors: null,
            summarySkipped: null,
            summaryPending: null,
            cancelBtn: null,
            closeActionBtn: null,
            liveRegion: null,
        },

        /**
         * Initialize the module
         */
        init: function() {
            console.log('SLOSAutoFixProgress: Initializing');
            this.createModal();
            this.bindEvents();
            this.createLiveRegion();
            console.log('SLOSAutoFixProgress: Initialized successfully');
        },

        /**
         * Create the modal DOM structure
         */
        createModal: function() {
            // Check if modal already exists
            if (document.getElementById('slos-autofix-progress-overlay')) {
                this.cacheElements();
                return;
            }

            const modalHTML = `
                <div id="slos-autofix-progress-overlay" class="slos-autofix-overlay" role="dialog" aria-modal="true" aria-labelledby="slos-autofix-title" aria-describedby="slos-autofix-desc">
                    <div class="slos-autofix-modal">
                        <!-- Header -->
                        <div class="slos-autofix-header">
                            <div class="slos-autofix-header-title">
                                <div class="slos-autofix-icon">
                                    <span class="dashicons dashicons-admin-tools"></span>
                                </div>
                                <div>
                                    <h2 id="slos-autofix-title">Auto-Fix Progress</h2>
                                    <div class="slos-autofix-header-subtitle" id="slos-autofix-desc">Fixing accessibility issues automatically</div>
                                </div>
                            </div>
                            <button type="button" class="slos-autofix-close" aria-label="Close dialog">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>

                        <!-- Progress Section -->
                        <div class="slos-autofix-progress-section">
                            <div class="slos-autofix-progress-wrap processing">
                                <div class="slos-autofix-progress-bar" style="width: 0%"></div>
                            </div>
                            <div class="slos-autofix-progress-text">
                                <span class="slos-autofix-progress-percent">0%</span>
                                <span class="slos-autofix-progress-status">Initializing...</span>
                            </div>
                        </div>

                        <!-- Fixer List Section -->
                        <div class="slos-autofix-fixer-section">
                            <div class="slos-autofix-fixer-header">Fixer Status</div>
                            <div class="slos-autofix-fixer-list" role="list" aria-label="List of fixers and their status"></div>
                        </div>

                        <!-- Summary Section -->
                        <div class="slos-autofix-summary">
                            <div class="slos-autofix-summary-grid">
                                <div class="slos-autofix-stat fixed">
                                    <div class="slos-autofix-stat-value" data-stat="fixed">0</div>
                                    <div class="slos-autofix-stat-label">Fixed</div>
                                </div>
                                <div class="slos-autofix-stat errors">
                                    <div class="slos-autofix-stat-value" data-stat="errors">0</div>
                                    <div class="slos-autofix-stat-label">Errors</div>
                                </div>
                                <div class="slos-autofix-stat skipped">
                                    <div class="slos-autofix-stat-value" data-stat="skipped">0</div>
                                    <div class="slos-autofix-stat-label">Skipped</div>
                                </div>
                                <div class="slos-autofix-stat pending">
                                    <div class="slos-autofix-stat-value" data-stat="pending">0</div>
                                    <div class="slos-autofix-stat-label">Pending</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Section -->
                        <div class="slos-autofix-actions">
                            <button type="button" class="slos-autofix-btn slos-autofix-btn-cancel" style="display: none;">
                                Cancel
                            </button>
                            <button type="button" class="slos-autofix-btn slos-autofix-btn-secondary slos-autofix-close-action" style="display: none;">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);
            this.cacheElements();
        },

        /**
         * Cache DOM element references
         */
        cacheElements: function() {
            this.elements.overlay = document.getElementById('slos-autofix-progress-overlay');
            this.elements.modal = this.elements.overlay.querySelector('.slos-autofix-modal');
            this.elements.closeBtn = this.elements.overlay.querySelector('.slos-autofix-close');
            this.elements.progressBar = this.elements.overlay.querySelector('.slos-autofix-progress-bar');
            this.elements.progressWrap = this.elements.overlay.querySelector('.slos-autofix-progress-wrap');
            this.elements.progressPercent = this.elements.overlay.querySelector('.slos-autofix-progress-percent');
            this.elements.progressStatus = this.elements.overlay.querySelector('.slos-autofix-progress-status');
            this.elements.fixerList = this.elements.overlay.querySelector('.slos-autofix-fixer-list');
            this.elements.summaryFixed = this.elements.overlay.querySelector('[data-stat="fixed"]');
            this.elements.summaryErrors = this.elements.overlay.querySelector('[data-stat="errors"]');
            this.elements.summarySkipped = this.elements.overlay.querySelector('[data-stat="skipped"]');
            this.elements.summaryPending = this.elements.overlay.querySelector('[data-stat="pending"]');
            this.elements.cancelBtn = this.elements.overlay.querySelector('.slos-autofix-btn-cancel');
            this.elements.closeActionBtn = this.elements.overlay.querySelector('.slos-autofix-close-action');
        },

        /**
         * Create ARIA live region for announcements
         */
        createLiveRegion: function() {
            if (document.getElementById('slos-autofix-live-region')) {
                this.elements.liveRegion = document.getElementById('slos-autofix-live-region');
                return;
            }

            const liveRegion = document.createElement('div');
            liveRegion.id = 'slos-autofix-live-region';
            liveRegion.className = 'slos-autofix-live-region';
            liveRegion.setAttribute('role', 'status');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            document.body.appendChild(liveRegion);
            this.elements.liveRegion = liveRegion;
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Close button
            if (this.elements.closeBtn) {
                this.elements.closeBtn.addEventListener('click', function() {
                    self.handleClose();
                });
            }

            // Close action button
            if (this.elements.closeActionBtn) {
                this.elements.closeActionBtn.addEventListener('click', function() {
                    self.hide();
                });
            }

            // Cancel button
            if (this.elements.cancelBtn) {
                this.elements.cancelBtn.addEventListener('click', function() {
                    self.cancel();
                });
            }

            // Escape key
            document.addEventListener('keydown', function(e) {
                if ((e.key === 'Escape' || e.keyCode === 27) && self.state.isOpen) {
                    e.preventDefault();
                    self.handleClose();
                }
            });

            // Click outside modal to close (only when not processing)
            if (this.elements.overlay) {
                this.elements.overlay.addEventListener('click', function(e) {
                    if (e.target === self.elements.overlay && !self.state.isProcessing) {
                        self.hide();
                    }
                });
            }

            // Focus trap
            if (this.elements.modal) {
                this.elements.modal.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab' || e.keyCode === 9) {
                        self.handleTabKey(e);
                    }
                });
            }
        },

        /**
         * Handle close based on state
         */
        handleClose: function() {
            if (this.state.isProcessing) {
                // Confirm before closing during processing
                if (confirm('Auto-fix is still in progress. Are you sure you want to cancel?')) {
                    this.cancel();
                }
            } else {
                this.hide();
            }
        },

        /**
         * Handle tab key for focus trap
         */
        handleTabKey: function(e) {
            const focusableElements = this.elements.modal.querySelectorAll(
                'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        },

        /**
         * Show the modal and start processing
         * @param {Object} options - Configuration options
         */
        show: function(options = {}) {
            const self = this;

            // Reset state
            this.resetState();

            // Store options
            this.state.triggerElement = document.activeElement;
            this.state.onComplete = options.onComplete || null;

            // If specific fixers provided, use them
            if (options.fixers && options.fixers.length > 0) {
                this.showWithFixers(options.fixers, options);
                return;
            }

            // Otherwise, fetch scan results first to determine which fixers are needed
            const pageId = options.pageId || 0;
            if (pageId > 0) {
                this.fetchPageScanResults(pageId, function(fixersWithIssues) {
                    if (fixersWithIssues.length === 0) {
                        // No fixable issues found
                        self.showNoIssuesMessage();
                        return;
                    }
                    self.showWithFixers(fixersWithIssues, options);
                });
            } else {
                // No page ID and no fixers - use default (shouldn't normally happen)
                const fixers = this.getDefaultFixers();
                this.showWithFixers(fixers, options);
            }
        },

        /**
         * Show modal with specific fixers and start processing
         */
        showWithFixers: function(fixers, options) {
            const self = this;

            this.state.totalFixers = fixers.length;
            this.state.fixers = fixers;

            // Populate fixer list
            this.populateFixerList(fixers);

            // Update summary
            this.updateSummary();

            // Show modal
            this.elements.overlay.classList.add('show');
            this.state.isOpen = true;

            // Show cancel button, hide close
            this.elements.cancelBtn.style.display = 'inline-flex';
            this.elements.closeActionBtn.style.display = 'none';

            // Focus close button
            setTimeout(function() {
                self.elements.closeBtn.focus();
            }, this.config.animationDuration);

            // Announce
            this.announce('Auto-fix progress dialog opened. Processing ' + this.state.totalFixers + ' fixers.');

            // Start processing
            this.startProcessing(options);
        },

        /**
         * Fetch scan results for a page to determine which fixers are needed
         */
        fetchPageScanResults: function(pageId, callback) {
            const self = this;

            // Show loading state
            this.elements.overlay.classList.add('show');
            this.state.isOpen = true;
            this.elements.progressStatus.textContent = 'Analyzing page issues...';
            this.elements.fixerList.innerHTML = '<div style="padding: 20px; text-align: center; color: #94a3b8;">Checking for fixable issues...</div>';

            if (typeof jQuery === 'undefined') {
                // Fallback to default fixers if jQuery not available
                console.warn('SLOSAutoFixProgress: jQuery not available, using all fixers');
                callback(this.getDefaultFixers());
                return;
            }

            // Set a timeout to fallback to all fixers if request takes too long
            const timeoutId = setTimeout(function() {
                console.warn('SLOSAutoFixProgress: Timeout waiting for scan results, using all fixers');
                callback(self.getDefaultFixers());
            }, 5000);

            jQuery.ajax({
                url: ajaxurl || (typeof slosautoFixConfig !== 'undefined' ? slosautoFixConfig.ajaxUrl : '/wp-admin/admin-ajax.php'),
                type: 'POST',
                data: {
                    action: 'slos_get_page_fixable_issues',
                    nonce: typeof slosautoFixConfig !== 'undefined' ? slosautoFixConfig.nonce : '',
                    page_id: pageId
                },
                success: function(response) {
                    clearTimeout(timeoutId);
                    
                    if (response.success && response.data) {
                        const data = response.data;
                        
                        // Backend indicates to run all fixers; merge to show full list while keeping highlights
                        if (data.use_all_fixers === true) {
                            console.log('SLOSAutoFixProgress: Backend signaled to use all fixers (' + (data.issue_count || 0) + ' issues found)');
                            const merged = self.mergeFixers(self.getDefaultFixers(), data.fixers || []);
                            callback(merged);
                        }
                        // Specific fixers returned; merge with registry to display everything
                        else if (data.fixers && data.fixers.length > 0) {
                            console.log('SLOSAutoFixProgress: Using ' + data.fixers.length + ' specific fixers');
                            const merged = self.mergeFixers(self.getDefaultFixers(), data.fixers);
                            callback(merged);
                        }
                        // No fixers and no signal to use all = no issues
                        else {
                            console.log('SLOSAutoFixProgress: No fixable issues found');
                            callback([]);
                        }
                    } else {
                        // Error or no data - try all fixers as fallback
                        console.warn('SLOSAutoFixProgress: No scan results or error, using all fixers');
                        callback(self.getDefaultFixers());
                    }
                },
                error: function(xhr, status, error) {
                    clearTimeout(timeoutId);
                    // On error, fall back to showing all fixers
                    console.error('SLOSAutoFixProgress: AJAX error, using all fixers as fallback', error);
                    callback(self.getDefaultFixers());
                }
            });
        },

        /**
         * Show message when no fixable issues found
         */
        showNoIssuesMessage: function() {
            const self = this;

            // Show modal with message
            this.elements.overlay.classList.add('show');
            this.state.isOpen = true;
            this.state.isProcessing = false;

            // Update UI
            this.elements.progressBar.style.width = '100%';
            this.elements.progressPercent.textContent = '100%';
            this.elements.progressStatus.textContent = 'No fixable issues found';
            this.elements.progressWrap.classList.remove('processing');
            this.elements.progressWrap.classList.add('success');

            this.elements.fixerList.innerHTML = `
                <div style="padding: 40px 20px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 16px;">✓</div>
                    <div style="color: #f8fafc; font-size: 16px; font-weight: 600; margin-bottom: 8px;">No Issues to Fix</div>
                    <div style="color: #94a3b8; font-size: 14px;">This page has no accessibility issues that can be automatically fixed,<br>or all fixable issues have already been resolved.</div>
                </div>
            `;

            // Update summary
            this.elements.summaryFixed.textContent = '0';
            this.elements.summaryErrors.textContent = '0';
            this.elements.summarySkipped.textContent = '0';
            this.elements.summaryPending.textContent = '0';

            // Hide cancel, show close
            this.elements.cancelBtn.style.display = 'none';
            this.elements.closeActionBtn.style.display = 'inline-flex';

            // Focus close button
            setTimeout(function() {
                self.elements.closeBtn.focus();
            }, this.config.animationDuration);

            this.announce('No fixable issues found on this page.');
        },

        /**
         * Hide the modal
         */
        hide: function() {
            this.elements.overlay.classList.remove('show');
            this.state.isOpen = false;
            this.state.isProcessing = false;

            // Return focus to trigger element
            if (this.state.triggerElement) {
                this.state.triggerElement.focus();
            }

            // Announce
            this.announce('Auto-fix dialog closed.');

            // Call onComplete callback
            if (this.state.onComplete && !this.state.isCancelled) {
                this.state.onComplete({
                    fixed: this.state.fixedCount,
                    errors: this.state.errorCount,
                    skipped: this.state.skippedCount,
                    cancelled: this.state.isCancelled,
                });
            }
        },

        /**
         * Cancel processing
         */
        cancel: function() {
            this.state.isCancelled = true;
            this.state.isProcessing = false;

            // Abort current XHR if exists
            if (this.state.currentXHR) {
                this.state.currentXHR.abort();
            }

            // Update UI
            this.elements.progressStatus.textContent = 'Cancelled';
            this.elements.progressWrap.classList.remove('processing');
            this.elements.cancelBtn.style.display = 'none';
            this.elements.closeActionBtn.style.display = 'inline-flex';

            // Mark remaining as skipped
            this.state.fixers.forEach(function(fixer, index) {
                if (fixer.status === 'pending' || fixer.status === 'processing') {
                    fixer.status = 'skipped';
                    fixer.message = 'Cancelled';
                }
            });
            this.renderFixerList();
            this.updateSummary();

            // Announce
            this.announce('Auto-fix cancelled. ' + this.state.fixedCount + ' issues fixed before cancellation.');
        },

        /**
         * Reset state for new session
         */
        resetState: function() {
            this.state.isProcessing = false;
            this.state.isCancelled = false;
            this.state.totalFixers = 0;
            this.state.completedFixers = 0;
            this.state.fixedCount = 0;
            this.state.errorCount = 0;
            this.state.skippedCount = 0;
            this.state.fixers = [];
            this.state.currentXHR = null;

            // Reset UI
            this.elements.progressBar.style.width = '0%';
            this.elements.progressPercent.textContent = '0%';
            this.elements.progressStatus.textContent = 'Initializing...';
            this.elements.progressWrap.classList.add('processing');
            this.elements.fixerList.innerHTML = '';
        },

        /**
         * Get default fixers from localized data
         * Note: This returns ALL registered fixers. Use fetchPageScanResults() to get only relevant ones.
         */
        getDefaultFixers: function() {
            if (typeof slosautoFixConfig !== 'undefined' && slosautoFixConfig.fixers) {
                return slosautoFixConfig.fixers.map(function(fixer) {
                    return {
                        id: fixer.id,
                        name: fixer.name,
                        description: fixer.description || '',
                        status: 'pending',
                        count: 0,
                        message: '',
                    };
                });
            }

            // Fallback: Generate generic list
            return [];
        },

        /**
         * Merge backend-provided fixers (with issues) with the full registry list.
         * Keeps backend list order first, then appends remaining fixers.
         */
        mergeFixers: function(allFixers, fixersWithIssues) {
            if (!Array.isArray(allFixers)) allFixers = [];
            if (!Array.isArray(fixersWithIssues)) fixersWithIssues = [];

            const byId = {};
            const merged = [];

            // First, add the fixers that have issues (preserve any status fields they may carry)
            fixersWithIssues.forEach(function(fixer) {
                if (!fixer || !fixer.id) return;
                byId[fixer.id] = true;
                merged.push(Object.assign({ status: 'pending', count: 0, message: '' }, fixer));
            });

            // Then append the remaining fixers from the full list
            allFixers.forEach(function(fixer) {
                if (!fixer || !fixer.id) return;
                if (byId[fixer.id]) return; // avoid duplicates
                merged.push(Object.assign({ status: 'pending', count: 0, message: '' }, fixer));
            });

            return merged;
        },

        /**
         * Populate the fixer list
         */
        populateFixerList: function(fixers) {
            this.elements.fixerList.innerHTML = '';

            fixers.forEach(function(fixer, index) {
                const item = document.createElement('div');
                item.className = 'slos-autofix-fixer-item pending';
                item.setAttribute('role', 'listitem');
                item.setAttribute('data-fixer-id', fixer.id);
                item.innerHTML = `
                    <div class="slos-autofix-fixer-status pending" aria-hidden="true"></div>
                    <div class="slos-autofix-fixer-info">
                        <div class="slos-autofix-fixer-name">${this.escapeHtml(fixer.name)}</div>
                        <div class="slos-autofix-fixer-desc">${this.escapeHtml(fixer.description)}</div>
                    </div>
                    <span class="slos-autofix-fixer-count" style="display: none;"></span>
                `;
                this.elements.fixerList.appendChild(item);
            }.bind(this));
        },

        /**
         * Render/update the fixer list
         */
        renderFixerList: function() {
            const self = this;
            this.state.fixers.forEach(function(fixer) {
                const item = self.elements.fixerList.querySelector('[data-fixer-id="' + fixer.id + '"]');
                if (!item) return;

                // Update classes
                item.className = 'slos-autofix-fixer-item ' + fixer.status;
                if (fixer.status === 'processing') {
                    item.classList.add('active');
                }

                // Update status icon
                const statusEl = item.querySelector('.slos-autofix-fixer-status');
                statusEl.className = 'slos-autofix-fixer-status ' + fixer.status;

                // Update count badge
                const countEl = item.querySelector('.slos-autofix-fixer-count');
                if (fixer.count > 0 || fixer.message) {
                    countEl.style.display = 'inline-block';
                    countEl.className = 'slos-autofix-fixer-count ' + fixer.status;
                    if (fixer.status === 'success') {
                        countEl.textContent = 'Fixed: ' + fixer.count;
                    } else if (fixer.status === 'error') {
                        countEl.textContent = fixer.message || 'Error';
                    } else if (fixer.status === 'skipped') {
                        countEl.textContent = fixer.message || 'Skipped';
                    }
                } else {
                    countEl.style.display = 'none';
                }
            });
        },

        /**
         * Update a specific fixer's status
         */
        updateFixer: function(fixerId, status, data = {}) {
            const fixer = this.state.fixers.find(function(f) {
                return f.id === fixerId;
            });

            if (!fixer) return;

            fixer.status = status;
            fixer.count = data.count || 0;
            fixer.message = data.message || '';

            // Update counts
            if (status === 'success') {
                this.state.fixedCount += fixer.count;
            } else if (status === 'error') {
                this.state.errorCount++;
            } else if (status === 'skipped') {
                this.state.skippedCount++;
            }

            if (status !== 'pending' && status !== 'processing') {
                this.state.completedFixers++;
            }

            this.renderFixerList();
            this.updateProgress();
            this.updateSummary();
            this.scrollToActiveFixer();
        },

        /**
         * Update progress bar and text
         */
        updateProgress: function() {
            const percent = this.state.totalFixers > 0 
                ? Math.round((this.state.completedFixers / this.state.totalFixers) * 100) 
                : 0;

            this.elements.progressBar.style.width = percent + '%';
            this.elements.progressPercent.textContent = percent + '%';
            this.elements.progressStatus.textContent = 
                'Processing ' + this.state.completedFixers + ' of ' + this.state.totalFixers + ' fixers...';
        },

        /**
         * Update summary stats
         */
        updateSummary: function() {
            const pending = this.state.totalFixers - this.state.completedFixers - 
                            (this.state.isProcessing ? 1 : 0);

            this.elements.summaryFixed.textContent = this.state.fixedCount;
            this.elements.summaryErrors.textContent = this.state.errorCount;
            this.elements.summarySkipped.textContent = this.state.skippedCount;
            this.elements.summaryPending.textContent = Math.max(0, pending);
        },

        /**
         * Scroll to the currently active fixer
         */
        scrollToActiveFixer: function() {
            const activeItem = this.elements.fixerList.querySelector('.slos-autofix-fixer-item.active');
            if (activeItem) {
                activeItem.scrollIntoView({
                    behavior: this.config.scrollBehavior,
                    block: 'nearest',
                });
            }
        },

        /**
         * Start processing fixers
         */
        startProcessing: function(options) {
            const self = this;
            this.state.isProcessing = true;
            
            const pageId = options.pageId || 0;
            const content = options.content || '';
            let currentIndex = 0;

            function processNext() {
                if (self.state.isCancelled || currentIndex >= self.state.fixers.length) {
                    self.complete();
                    return;
                }

                const fixer = self.state.fixers[currentIndex];
                
                // Mark as processing
                fixer.status = 'processing';
                self.renderFixerList();
                self.scrollToActiveFixer();

                // Make AJAX request
                self.processFixer(fixer, pageId, content, function(result) {
                    if (self.state.isCancelled) return;

                    // Update fixer status based on result
                    if (result.success) {
                        self.updateFixer(fixer.id, 'success', {
                            count: result.data.fixed_count || 0,
                        });
                    } else if (result.skipped) {
                        self.updateFixer(fixer.id, 'skipped', {
                            message: result.message || 'No issues found',
                        });
                    } else {
                        self.updateFixer(fixer.id, 'error', {
                            message: result.message || 'Failed',
                        });
                    }

                    // Process next
                    currentIndex++;
                    setTimeout(processNext, self.config.pollingInterval);
                });
            }

            // Start processing
            processNext();
        },

        /**
         * Process a single fixer via AJAX
         */
        processFixer: function(fixer, pageId, content, callback) {
            const self = this;

            // Check if we have jQuery and the AJAX config
            if (typeof jQuery === 'undefined' || typeof slosautoFixConfig === 'undefined') {
                // Simulate processing for demo/testing
                setTimeout(function() {
                    const random = Math.random();
                    if (random > 0.15) {
                        callback({
                            success: true,
                            data: { fixed_count: Math.floor(Math.random() * 10) }
                        });
                    } else if (random > 0.05) {
                        callback({
                            skipped: true,
                            message: 'No issues found'
                        });
                    } else {
                        callback({
                            success: false,
                            message: 'Processing failed'
                        });
                    }
                }, 50 + Math.random() * 150);
                return;
            }

            // Real AJAX request
            this.state.currentXHR = jQuery.ajax({
                url: slosautoFixConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slos_autofix_single',
                    nonce: slosautoFixConfig.nonce,
                    fixer_id: fixer.id,
                    page_id: pageId,
                    content: content,
                },
                success: function(response) {
                    callback(response);
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        callback({
                            success: false,
                            message: error || 'Request failed'
                        });
                    }
                },
            });
        },

        /**
         * Mark processing as complete
         */
        complete: function() {
            this.state.isProcessing = false;
            this.elements.progressWrap.classList.remove('processing');

            // Update progress to 100%
            this.elements.progressBar.style.width = '100%';
            this.elements.progressPercent.textContent = '100%';

            // Update status text
            if (this.state.isCancelled) {
                this.elements.progressStatus.textContent = 'Cancelled';
            } else if (this.state.errorCount > 0) {
                this.elements.progressStatus.textContent = 'Completed with ' + this.state.errorCount + ' error(s)';
            } else {
                this.elements.progressStatus.textContent = 'Complete! Fixed ' + this.state.fixedCount + ' issue(s)';
            }

            // Show close button, hide cancel
            this.elements.cancelBtn.style.display = 'none';
            this.elements.closeActionBtn.style.display = 'inline-flex';

            // Announce completion
            let announcement = 'Auto-fix complete. ';
            announcement += this.state.fixedCount + ' issues fixed. ';
            if (this.state.errorCount > 0) {
                announcement += this.state.errorCount + ' errors occurred. ';
            }
            if (this.state.skippedCount > 0) {
                announcement += this.state.skippedCount + ' fixers skipped.';
            }
            this.announce(announcement);
        },

        /**
         * Announce message to screen readers
         */
        announce: function(message) {
            if (!this.elements.liveRegion) return;

            // Clear and set new message
            this.elements.liveRegion.textContent = '';
            
            setTimeout(function() {
                this.elements.liveRegion.textContent = message;
            }.bind(this), this.config.announceDelay);
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Public API: Check if modal is open
         */
        isOpen: function() {
            return this.state.isOpen;
        },

        /**
         * Public API: Check if processing
         */
        isProcessing: function() {
            return this.state.isProcessing;
        },

    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            SLOSAutoFixProgress.init();
        });
    } else {
        SLOSAutoFixProgress.init();
    }

    // Expose to global scope
    window.SLOSAutoFixProgress = SLOSAutoFixProgress;

})();
