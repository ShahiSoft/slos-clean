jQuery(document).ready(function($) {
    console.log('SLOS Scanner Admin JS Loaded');
    console.log('SLOSAutoFixProgress available:', typeof window.SLOSAutoFixProgress);
    console.log('slosautoFixConfig available:', typeof window.slosautoFixConfig);
    console.log('slosScanner available:', typeof window.slosScanner);
    console.log('.slos-fix-all-btn buttons found:', $('.slos-fix-all-btn').length);
    
    let postIds = [];
    let currentIdx = 0;
    let results = [];

    $('#slos-start-scan').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);

        // Prefer the new modal-based progress experience when available
        if (typeof window.SLOSScanProgress !== 'undefined' && typeof window.SLOSScanProgress.start === 'function') {
            // Initialize modal once if not already in DOM
            if (typeof window.SLOSScanProgress.init === 'function' && !$('#slos-scan-progress-overlay').length) {
                window.SLOSScanProgress.init();
            }

            $btn.prop('disabled', true).text('Scanning...');

            window.SLOSScanProgress.start({
                onComplete: function() {
                    $btn.prop('disabled', false).text('Start Full Scan');
                }
            });

            // Hide legacy inline progress UI to avoid double indicators
            $('#slos-scan-status, #slos-progress-bar-wrapper').hide();
            return;
        }

        // Fallback to legacy inline progress if modal module is missing
        $btn.prop('disabled', true);
        $('#slos-scan-status').show();
        $('#slos-progress-bar-wrapper').show();
        $('#slos-scan-results').html('<p>Initializing scan...</p>');
        
        // Step 1: Get all post IDs
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_get_posts_to_scan',
                nonce: slosScanner.nonce
            },
            success: function(response) {
                if (response.success) {
                    postIds = response.data;
                    currentIdx = 0;
                    results = [];
                    scanNextPost();
                } else {
                    alert('Error initializing scan: ' + response.data);
                    resetScan();
                }
            },
            error: function() {
                alert('Network error initializing scan.');
                resetScan();
            }
        });
    });

    function scanNextPost() {
        if (currentIdx >= postIds.length) {
            finishScan();
            return;
        }

        const progress = Math.round((currentIdx / postIds.length) * 100);
        $('#slos-scan-progress').text(progress);
        $('#slos-progress-bar').css('width', progress + '%');

        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_scan_single_post',
                post_id: postIds[currentIdx],
                nonce: slosScanner.nonce
            },
            success: function(response) {
                if (response.success && response.data.issues_count > 0) {
                    results.push(response.data);
                    updateResultsPreview();
                }
                currentIdx++;
                scanNextPost();
            },
            error: function() {
                // Skip on error
                currentIdx++;
                scanNextPost();
            }
        });
    }

    function finishScan() {
        $('#slos-scan-progress').text('100');
        $('#slos-progress-bar').css('width', '100%');
        $('#slos-start-scan').prop('disabled', false).text('Scan Complete');
        
        renderFinalResults();
    }

    function resetScan() {
        $('#slos-start-scan').prop('disabled', false);
        $('#slos-scan-status').hide();
        $('#slos-progress-bar-wrapper').hide();
    }

    function updateResultsPreview() {
        $('#slos-scan-results').html('<p>Found issues in ' + results.length + ' posts so far...</p>');
    }

    function renderFinalResults() {
        if (results.length === 0) {
            $('#slos-scan-results').html('<div class="notice notice-success inline"><p>Great job! No accessibility issues found.</p></div>');
            return;
        }

        let html = '<div class="slos-actions-bar" style="margin-bottom: 15px;">';
        html += '<button id="slos-export-csv" class="button">Export CSV Report</button>';
        html += '</div>';

        html += '<table class="wp-list-table widefat fixed striped">';
        html += '<thead><tr><th>Post Title</th><th>Issues Found</th><th>Actions</th></tr></thead><tbody>';
        
        results.forEach(function(item) {
            html += '<tr>';
            html += '<td><a href="' + item.edit_link + '" target="_blank">' + item.title + '</a></td>';
            html += '<td><span class="slos-issue-count">' + item.issues_count + ' Issues</span></td>';
            html += '<td>';
            html += '<a href="' + item.edit_link + '" class="button button-small">Edit & Fix</a> ';
            // Placeholder for future inline fixes
            // html += '<button class="button button-small slos-quick-fix" data-id="' + item.post_id + '">Quick Fix</button>';
            html += '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        $('#slos-scan-results').html(html);

        // Bind Export Button
        $('#slos-export-csv').on('click', function() {
            exportToCSV(results);
        });
    }

    function exportToCSV(data) {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Post ID,Title,Issues Count,Edit Link\n";
        
        data.forEach(function(row) {
            let rowStr = row.post_id + ',"' + row.title.replace(/"/g, '""') + '",' + row.issues_count + ',' + row.edit_link;
            csvContent += rowStr + "\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "accessibility_report_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Statement Generator - Generate Preview
    $(document).on('click', '#slos-generate-statement', function() {
        const $btn = $(this);
        const orgName = $('#slos-org-name').val().trim();
        const contactEmail = $('#slos-contact-email').val().trim();
        const wcagTarget = $('#slos-wcag-target').val();
        const statementDate = $('#slos-statement-date').val();
        const commitment = $('#slos-commitment').val().trim();
        
        // Validation
        if (!orgName) {
            alert('Please enter an organization name.');
            $('#slos-org-name').focus();
            return;
        }
        
        if (contactEmail && !isValidEmail(contactEmail)) {
            alert('Please enter a valid email address.');
            $('#slos-contact-email').focus();
            return;
        }
        
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Generating...');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_generate_statement',
                nonce: slosScanner.nonce,
                org_name: orgName,
                contact_email: contactEmail,
                wcag_target: 'WCAG 2.2 Level ' + wcagTarget,
                statement_date: statementDate,
                commitment: commitment
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    // Show preview in a modal or section
                    showStatementPreview(response.data.statement, response.data.statement_raw);
                    
                    // Enable publish button
                    $('#slos-publish-statement').prop('disabled', false);
                    
                    // Store statement data for publishing
                    window.slosStatementData = {
                        html: response.data.statement,
                        raw: response.data.statement_raw,
                        org_name: orgName,
                        contact_email: contactEmail,
                        wcag_target: wcagTarget,
                        statement_date: statementDate,
                        commitment: commitment
                    };
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html(originalText);
                console.error('Statement Generation Error:', error);
                alert('Network error. Please try again.');
            }
        });
    });
    
    // Statement Generator - Publish to Page
    $(document).on('click', '#slos-publish-statement', function() {
        const $btn = $(this);
        
        if (!window.slosStatementData) {
            alert('Please generate a statement first.');
            return;
        }
        
        if (!confirm('This will create/update a page titled "Accessibility Statement". Continue?')) {
            return;
        }
        
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Publishing...');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_publish_statement',
                nonce: slosScanner.nonce,
                statement_content: window.slosStatementData.html
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    const editLink = response.data.edit_link || '#';
                    const viewLink = response.data.view_link || '#';
                    
                    // Show success notification
                    const notification = '<div class="notice notice-success inline" style="margin-top: 16px; padding: 12px; background: rgba(34, 197, 94, 0.1); border-left: 3px solid var(--slos-success); border-radius: 6px;">' +
                        '<p style="margin: 0; color: var(--slos-text-primary);">' +
                        '<strong>Statement Published Successfully!</strong><br>' +
                        '<a href="' + editLink + '" target="_blank" style="color: var(--slos-accent); text-decoration: none; margin-right: 12px;">Edit Page</a> | ' +
                        '<a href="' + viewLink + '" target="_blank" style="color: var(--slos-accent); text-decoration: none;">View Page</a>' +
                        '</p></div>';
                    
                    // Append notification after the buttons
                    if ($('#slos-statement-notification').length) {
                        $('#slos-statement-notification').html(notification);
                    } else {
                        $('#slos-statement-preview-anchor').before('<div id="slos-statement-notification">' + notification + '</div>');
                    }
                } else {
                    alert('Error: ' + (response.data || 'Failed to publish statement'));
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html(originalText);
                console.error('Statement Publish Error:', error);
                alert('Network error. Please try again.');
            }
        });
    });
    
    // Helper function to validate email
    function isValidEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }
    
    // Helper function to show statement preview
    function showStatementPreview(html, raw) {
        // Create or update preview section
        if ($('#slos-statement-preview').length === 0) {
            $('#slos-statement-preview-anchor').html(
                '<div id="slos-statement-preview" style="margin-top: 16px; padding: 16px; background: var(--slos-bg-card); border: 1px solid var(--slos-border); border-radius: 8px;">' +
                '<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">' +
                '<h4 style="margin: 0; color: var(--slos-text-primary);">Preview</h4>' +
                '<div style="display: flex; gap: 8px; flex-wrap: wrap;">' +
                '<button type="button" class="slos-btn-secondary slos-copy-statement" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">' +
                '<span class="dashicons dashicons-clipboard" style="font-size: 14px;"></span> Copy Text' +
                '</button>' +
                '<button type="button" class="slos-btn-secondary slos-close-preview" style="padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">' +
                '<span class="dashicons dashicons-no-alt" style="font-size: 14px;"></span> Close' +
                '</button>' +
                '</div>' +
                '</div>' +
                '<div id="slos-statement-preview-content" style="background: white; padding: 16px; border-radius: 6px; max-height: 420px; overflow-y: auto; border: 1px solid var(--slos-border);"></div>' +
                '</div>'
            );
        }
        
        $('#slos-statement-preview-content').html(html);
        $('#slos-statement-preview').show();
        
        // Handle copy button
        $('.slos-copy-statement').off('click').on('click', function() {
            const $btn = $(this);
            const textToCopy = raw || $('#slos-statement-preview-content').text();
            
            // Copy to clipboard
            navigator.clipboard.writeText(textToCopy).then(function() {
                const originalHtml = $btn.html();
                $btn.html('<span class="dashicons dashicons-yes" style="font-size: 14px;"></span> Copied!');
                setTimeout(function() {
                    $btn.html(originalHtml);
                }, 2000);
            }).catch(function(err) {
                console.error('Copy failed:', err);
                alert('Failed to copy to clipboard');
            });
        });

        // Handle close preview
        $('.slos-close-preview').off('click').on('click', function() {
            $('#slos-statement-preview').hide();
        });
    }

    // Export Handlers
    $('.slos-export-btn').on('click', function(e) {
        e.preventDefault();
        const format = $(this).data('format');
        
        // Create a hidden form to submit the request (to trigger download)
        const form = $('<form>', {
            action: slosScanner.ajax_url,
            method: 'POST',
            target: '_blank'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: 'slos_export_report'
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'nonce',
            value: slosScanner.nonce
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'format',
            value: format
        }));

        $('body').append(form);
        form.submit();
        form.remove();
    });

    function submitReportDownload(action, extraFields = {}) {
        const form = $('<form>', {
            action: slosScanner.ajax_url,
            method: 'POST',
            target: '_blank'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: action
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'nonce',
            value: slosScanner.nonce
        }));

        $.each(extraFields, function(key, value) {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: value
            }));
        });

        $('body').append(form);
        form.trigger('submit');
        setTimeout(function() {
            form.remove();
        }, 0);
    }

    function showReportNotification(type, message) {
        if (typeof showNotification === 'function') {
            showNotification(type, message);
            return;
        }

        let $status = $('#slos-report-status');
        if (!$status.length) {
            const $container = $('#slos-schedule-report').closest('.slos-tool-content');
            $status = $('<div id="slos-report-status"></div>').css({
                marginTop: '10px',
                padding: '10px 12px',
                borderRadius: '6px',
                border: '1px solid var(--slos-border)',
                background: 'var(--slos-bg-card)',
                color: 'var(--slos-text-primary)',
                fontSize: '13px'
            });

            if ($container.length) {
                $container.append($status);
            } else {
                $('body').append($status);
            }
        }

        const isError = type === 'error';
        $status
            .css({
                borderColor: isError ? 'rgba(239,68,68,0.6)' : 'rgba(16,185,129,0.6)',
                background: isError ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.12)'
            })
            .text(message)
            .show();

        setTimeout(function() {
            $status.fadeOut(200);
        }, 4000);
    }

    function handleDownloadButton($btn, action, extraFields) {
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Preparing...');

        submitReportDownload(action, extraFields);

        setTimeout(function() {
            $btn.prop('disabled', false).html(originalHtml);
        }, 1200);
    }

    $(document).on('click', '.slos-export-pdf', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const template = $btn.data('template') || 'executive';
        handleDownloadButton($btn, 'slos_export_pdf', { template: template });
    });

    $(document).on('click', '.slos-export-csv', function(e) {
        e.preventDefault();
        handleDownloadButton($(this), 'slos_export_csv');
    });

    $(document).on('click', '.slos-export-json', function(e) {
        e.preventDefault();
        handleDownloadButton($(this), 'slos_export_json');
    });

    $(document).on('click', '#slos-schedule-report', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const emailRaw = $('#slos-report-email').val().trim();
        const frequency = $('#slos-report-frequency').val();
        const template = $('#slos-report-template').val() || 'executive';
        const emails = emailRaw.split(',').map(function(email) { return email.trim(); }).filter(Boolean);

        if (emails.length === 0 || !emails.every(isValidEmail)) {
            showReportNotification('error', 'Please enter valid email address(es). Separate multiple emails with commas.');
            return;
        }

        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Scheduling...');

        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_schedule_email_report',
                nonce: slosScanner.nonce,
                email: emailRaw,
                frequency: frequency,
                template: template
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalHtml);

                if (response && response.success) {
                    showReportNotification('success', (response.data && response.data.message) || 'Report schedule saved.');
                } else {
                    showReportNotification('error', (response && response.data && response.data.message) || 'Failed to schedule report.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalHtml);
                showReportNotification('error', 'Network error while scheduling report.');
            }
        });
    });

    $(document).on('click', '#slos-send-test-report', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const email = $('#slos-report-email').val().trim();
        const template = $('#slos-report-template').val() || 'executive';

        if (!email || !isValidEmail(email)) {
            showReportNotification('error', 'Please enter a valid email address to send the test report.');
            return;
        }

        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-email-alt"></span> Sending...');

        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_send_test_report',
                nonce: slosScanner.nonce,
                email: email,
                template: template
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalHtml);

                if (response && response.success) {
                    showReportNotification('success', (response.data && response.data.message) || 'Test report sent successfully.');
                } else {
                    showReportNotification('error', (response && response.data && response.data.message) || 'Failed to send test report.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalHtml);
                showReportNotification('error', 'Network error while sending test report.');
            }
        });
    });

    // ==========================================================================
    // AUTO-FIX PROGRESS POPUP INTEGRATION
    // ==========================================================================

    /**
     * Initialize Auto-Fix button handlers
     * Supports multiple button types:
     * - .slos-autofix-trigger: Generic trigger class
     * - #slos-autofix-all-btn: Global auto-fix all button
     * - .slos-fix-all-btn: Per-page "Fix All" button in dashboard
     * - .slos-autofix-post-btn: Individual post fix button
     */
    function initAutoFixHandlers() {
        console.log('SLOS: Initializing Auto-Fix handlers');
        
        // "Auto Fix All" / "Fix All" button handler - uses new progress modal
        $(document).on('click', '.slos-autofix-trigger, #slos-autofix-all-btn, .slos-fix-all-btn, .slos-autofix-post-btn', function(e) {
            console.log('SLOS: Fix All button clicked', this);
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            // Support both data-page-id and data-post-id attributes
            const pageId = $btn.data('page-id') || $btn.data('post-id') || 0;
            const $row = $btn.closest('.slos-page-row, tr');
            
            if (!pageId) {
                alert('No page/post ID specified');
                return;
            }
            
            // Check if progress module is available
            if (typeof window.SLOSAutoFixProgress === 'undefined') {
                console.error('SLOSAutoFixProgress module not loaded');
                alert('Auto-fix progress module not loaded. Please refresh the page.');
                return;
            }
            
            // Disable button while processing
            $btn.prop('disabled', true);
            
            // Show the progress popup
            window.SLOSAutoFixProgress.show({
                pageId: pageId,
                onComplete: function(results) {
                    // Re-enable button
                    $btn.prop('disabled', false);
                    
                    // Update UI based on results
                    if (results.fixed > 0) {
                        // Update button state - KEEP IT FIXED, don't revert
                        $btn.addClass('fixed').html('<span class="dashicons dashicons-yes"></span> Fixed!');
                        
                        // Update row if exists (dashboard page rows)
                        if ($row.length) {
                            // Try to update issues count
                            const $issuesSpan = $row.find('.slos-page-issues, .slos-issue-count');
                            if ($issuesSpan.length) {
                                const currentCount = parseInt($issuesSpan.text()) || 0;
                                const newCount = Math.max(0, currentCount - results.fixed);
                                $issuesSpan.text(newCount);
                                
                                // Update priority badge if present
                                const $badge = $row.find('.slos-priority-badge');
                                if ($badge.length) {
                                    if (newCount === 0) {
                                        $badge.removeClass('high medium').addClass('low').text('Fixed');
                                    } else if (newCount <= 2) {
                                        $badge.removeClass('high medium').addClass('low').text('Low');
                                    } else if (newCount <= 5) {
                                        $badge.removeClass('high low').addClass('medium').text('Medium');
                                    }
                                }
                            }
                        }
                        
                        // No longer reset button - it stays as "Fixed!"
                        // User can refresh page to rescan if needed
                        
                    } else if (results.skipped > 0 && results.fixed === 0) {
                        // No issues found to fix
                        $btn.html('<span class="dashicons dashicons-yes"></span> No Issues');
                    }
                    
                    // Automatically rescan after fixes are applied
                    if (results.fixed > 0) {
                        setTimeout(function() {
                            // Backend already rescanned and consolidated results
                            alert('✓ Auto-Fix Complete!\n\n' + 
                                  'Fixed: ' + results.fixed + ' issue(s)\n' +
                                  (results.errors > 0 ? 'Errors: ' + results.errors + '\n' : '') +
                                  (results.skipped > 0 ? 'Skipped: ' + results.skipped + '\n' : '') +
                                  '\nRefreshing page to show updated results...');
                            
                            // Reload to show fresh scan results (backend already updated)
                            location.reload();
                        }, 500);
                    } else if (results.skipped > 0 && results.fixed === 0) {
                        // No issues were actually fixed
                        setTimeout(function() {
                            alert('No fixable issues found on this page.\n\n' +
                                  'The page may have issues that require manual fixing.');
                        }, 300);
                    }
                }
            });
        });
    }
    
    // Initialize auto-fix handlers
    initAutoFixHandlers();

    // Centralized Autofix toggle handler for all pages
    $(document).on('change', '.slos-autofix-checkbox', function() {
        const $checkbox = $(this);
        const postId = $checkbox.data('post-id');
        const enabled = $checkbox.is(':checked');

        if (!postId) {
            return;
        }

        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_toggle_autofix',
                nonce: slosScanner.nonce,
                post_id: postId,
                enabled: enabled
            },
            success: function(response) {
                if (!response || !response.success) {
                    // Revert checkbox on failure
                    $checkbox.prop('checked', !enabled);
                    alert('Failed to update auto-fix setting.' + (response && response.data ? '\n' + response.data : ''));
                }
            },
            error: function() {
                $checkbox.prop('checked', !enabled);
                alert('Network error while updating auto-fix setting.');
            }
        });
    });

    /**
     * Initialize Rollback button handlers
     * Allows users to undo recent fixes and restore previous content
     */
    function initRollbackHandlers() {
        console.log('SLOS: Initializing Rollback handlers');
        
        // Rollback button handler
        $(document).on('click', '.slos-rollback-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const postId = $btn.data('post-id') || 0;
            
            if (!postId) {
                alert('No post ID specified');
                return;
            }
            
            // Confirm rollback action
            if (!confirm('Are you sure you want to rollback accessibility fixes for this page?\n\nThis will restore the previous content from backup.')) {
                return;
            }
            
            // Disable button while processing
            $btn.prop('disabled', true).css('opacity', '0.6');
            
            // Make AJAX request to rollback
            $.ajax({
                url: slosScanner.ajax_url,
                type: 'POST',
                data: {
                    action: 'slos_rollback_fixes',
                    // Use the autofix nonce expected by ajax_rollback_fixes; fall back to scanner nonce
                    nonce: (typeof window.slosautoFixConfig !== 'undefined' ? window.slosautoFixConfig.nonce : slosScanner.nonce),
                    post_id: postId
                },
                success: function(response) {
                    $btn.prop('disabled', false).css('opacity', '1');
                    
                    if (response.success) {
                        alert('✓ Rollback Complete!\n\nContent has been restored to previous state.\n\nRefreshing page...');
                        
                        // Rescan after rollback
                        $.ajax({
                            url: slosScanner.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'slos_scan_single_post',
                                nonce: slosScanner.nonce,
                                post_id: postId
                            },
                            success: function() {
                                location.reload();
                            },
                            error: function() {
                                location.reload();
                            }
                        });
                    } else {
                        alert('❌ Rollback Failed\n\n' + (response.data || 'An error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).css('opacity', '1');
                    alert('❌ Rollback Request Failed\n\n' + error);
                    console.error('Rollback error:', error);
                }
            });
        });
        
        // Check for backup existence and show/hide rollback buttons on page load
        $('.slos-rollback-btn').each(function() {
            const $btn = $(this);
            const postId = $btn.data('post-id');
            
            // Check if backup exists for this post
            $.ajax({
                url: slosScanner.ajax_url,
                type: 'POST',
                data: {
                    action: 'slos_check_backup_exists',
                    nonce: slosScanner.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success && response.data.has_backup) {
                        $btn.show();
                    }
                }
            });
        });
    }
    
    // Initialize rollback handlers
    initRollbackHandlers();

    // Color Contrast Checker
    $(document).on('click', '#slos-check-contrast', function() {
        const $btn = $(this);
        const fgColor = $('#slos-fg-color').val().trim();
        const bgColor = $('#slos-bg-color').val().trim();
        const $result = $('#slos-contrast-result');
        
        if (!fgColor || !bgColor) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Enter both colors</div>').addClass('show fail').removeClass('pass');
            return;
        }
        
        // Validate hex format
        const hexPattern = /^#?[0-9A-Fa-f]{6}$/;
        if (!hexPattern.test(fgColor) || !hexPattern.test(bgColor)) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Invalid color format. Use #RRGGBB</div>').addClass('show fail').removeClass('pass');
            return;
        }
        
        $btn.prop('disabled', true).text('Checking...');
        $result.removeClass('show pass fail');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_check_color_contrast',
                nonce: slosScanner.nonce,
                fg_color: fgColor,
                bg_color: bgColor
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Check');
                
                if (response.success && response.data) {
                    const d = response.data;
                    let html = '<div style="display: flex; align-items: center; gap: 16px; padding: 16px; background: var(--slos-bg-card); border-radius: 6px;">';
                    html += '<div style="display: flex; gap: 8px;">';
                    html += '<div style="width: 40px; height: 40px; background: ' + fgColor + '; border: 1px solid var(--slos-border); border-radius: 4px;"></div>';
                    html += '<div style="width: 40px; height: 40px; background: ' + bgColor + '; border: 1px solid var(--slos-border); border-radius: 4px;"></div>';
                    html += '</div>';
                    html += '<div style="flex: 1;">';
                    html += '<div style="font-size: 18px; font-weight: 700; color: var(--slos-text-primary); margin-bottom: 4px;">Ratio: ' + d.ratio + ':1</div>';
                    html += '<div style="font-size: 13px; color: var(--slos-text-muted);">' + d.recommendation + '</div>';
                    html += '<div style="display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap;">';
                    html += '<span class="wcag-badge ' + (d.wcag_aa_normal ? 'pass' : 'fail') + '">AA Normal: ' + (d.wcag_aa_normal ? '✓' : '✗') + '</span>';
                    html += '<span class="wcag-badge ' + (d.wcag_aa_large ? 'pass' : 'fail') + '">AA Large: ' + (d.wcag_aa_large ? '✓' : '✗') + '</span>';
                    html += '<span class="wcag-badge ' + (d.wcag_aaa_normal ? 'pass' : 'fail') + '">AAA Normal: ' + (d.wcag_aaa_normal ? '✓' : '✗') + '</span>';
                    html += '</div></div></div>';
                    $result.html(html).addClass('show').removeClass('fail pass').addClass(d.passes_aa ? 'pass' : 'fail');
                } else {
                    $result.html('<div style="color: var(--slos-error);">Error: ' + (response.data || 'Unknown error') + '</div>').addClass('show fail').removeClass('pass');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Check');
                $result.html('<div style="color: var(--slos-error);">Network error</div>').addClass('show fail').removeClass('pass');
            }
        });
    });

    // Readability Score Checker
    $(document).on('click', '#slos-check-readability', function() {
        const $btn = $(this);
        const text = $('#slos-readability-text').val().trim();
        const $result = $('#slos-readability-result');
        
        if (!text) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Please enter text to analyze</div>').addClass('show fail').removeClass('pass');
            return;
        }
        
        if (text.split(/\s+/).length < 10) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Please enter at least 10 words for accurate analysis</div>').addClass('show fail').removeClass('pass');
            return;
        }
        
        $btn.prop('disabled', true).text('Analyzing...');
        $result.removeClass('show pass fail');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_check_readability',
                nonce: slosScanner.nonce,
                text: text
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Analyze');
                
                if (response.success && response.data) {
                    const d = response.data;
                    let html = '<div style="padding: 16px; background: var(--slos-bg-card); border-radius: 6px;">';
                    html += '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">';
                    html += '<div style="width: 60px; height: 60px; background: var(--slos-accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: white;">' + d.grade_level + '</div>';
                    html += '<div style="flex: 1;">';
                    html += '<div style="font-size: 16px; font-weight: 700; color: var(--slos-text-primary); margin-bottom: 4px;">Grade Level: ' + d.grade_level + '</div>';
                    html += '<div style="font-size: 13px; color: var(--slos-text-muted);">' + d.interpretation + '</div>';
                    html += '</div></div>';
                    html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; padding: 12px; background: var(--slos-bg-primary); border-radius: 4px;">';
                    html += '<div style="text-align: center;"><div style="font-size: 11px; color: var(--slos-text-muted); margin-bottom: 4px;">Words</div><div style="font-size: 18px; font-weight: 600; color: var(--slos-text-primary);">' + d.word_count + '</div></div>';
                    html += '<div style="text-align: center;"><div style="font-size: 11px; color: var(--slos-text-muted); margin-bottom: 4px;">Sentences</div><div style="font-size: 18px; font-weight: 600; color: var(--slos-text-primary);">' + d.sentence_count + '</div></div>';
                    html += '<div style="text-align: center;"><div style="font-size: 11px; color: var(--slos-text-muted); margin-bottom: 4px;">Syllables</div><div style="font-size: 18px; font-weight: 600; color: var(--slos-text-primary);">' + d.syllable_count + '</div></div>';
                    html += '<div style="text-align: center;"><div style="font-size: 11px; color: var(--slos-text-muted); margin-bottom: 4px;">Reading Ease</div><div style="font-size: 18px; font-weight: 600; color: var(--slos-text-primary);">' + d.flesch_score.toFixed(1) + '</div></div>';
                    html += '</div></div>';
                    
                    // Grade level color coding: <= 8 is good (pass), > 8 is harder (fail)
                    const passesReadability = d.grade_level <= 8;
                    $result.html(html).addClass('show').removeClass('fail pass').addClass(passesReadability ? 'pass' : 'fail');
                } else {
                    $result.html('<div style="color: var(--slos-error);">Error: ' + (response.data || 'Unknown error') + '</div>').addClass('show fail').removeClass('pass');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Analyze');
                $result.html('<div style="color: var(--slos-error);">Network error</div>').addClass('show fail').removeClass('pass');
            }
        });
    });

    // Link Text Validator
    $(document).on('click', '#slos-check-link', function() {
        const $btn = $(this);
        const linkText = $('#slos-link-text').val().trim();
        const $result = $('#slos-link-result');
        
        if (!linkText) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Please enter link text to validate</div>').addClass('show fail').removeClass('pass');
            return;
        }
        
        $btn.prop('disabled', true).text('Validating...');
        $result.removeClass('show pass fail');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_check_link_text',
                nonce: slosScanner.nonce,
                link_text: linkText
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Validate');
                
                if (response.success && response.data) {
                    const d = response.data;
                    let html = '<div style="padding: 16px; background: var(--slos-bg-card); border-radius: 6px;">';
                    
                    if (d.is_descriptive) {
                        html += '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">';
                        html += '<div style="width: 40px; height: 40px; background: var(--slos-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white;">✓</div>';
                        html += '<div style="flex: 1;">';
                        html += '<div style="font-size: 16px; font-weight: 700; color: var(--slos-success); margin-bottom: 4px;">Descriptive Link Text</div>';
                        html += '<div style="font-size: 13px; color: var(--slos-text-muted);">This link text appears to be descriptive and accessible.</div>';
                        html += '</div></div>';
                        html += '<div style="padding: 12px; background: var(--slos-bg-primary); border-radius: 4px; border-left: 3px solid var(--slos-success);">';
                        html += '<div style="font-size: 12px; font-weight: 600; color: var(--slos-text-primary); margin-bottom: 4px;">✓ WCAG 2.4.4 Compliant</div>';
                        html += '<div style="font-size: 11px; color: var(--slos-text-muted);">Link purpose can be determined from the link text alone.</div>';
                        html += '</div>';
                    } else {
                        html += '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">';
                        html += '<div style="width: 40px; height: 40px; background: var(--slos-error); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white;">✗</div>';
                        html += '<div style="flex: 1;">';
                        html += '<div style="font-size: 16px; font-weight: 700; color: var(--slos-error); margin-bottom: 4px;">Generic Link Text</div>';
                        html += '<div style="font-size: 13px; color: var(--slos-text-muted);">This link text is not descriptive enough.</div>';
                        html += '</div></div>';
                        
                        if (d.pattern_match) {
                            html += '<div style="padding: 12px; background: var(--slos-bg-primary); border-radius: 4px; border-left: 3px solid var(--slos-error); margin-bottom: 12px;">';
                            html += '<div style="font-size: 12px; font-weight: 600; color: var(--slos-text-primary); margin-bottom: 4px;">✗ WCAG 2.4.4 Issue</div>';
                            if (d.pattern_match === 'too_short') {
                                html += '<div style="font-size: 11px; color: var(--slos-text-muted);">Link text is too short to be descriptive.</div>';
                            } else {
                                html += '<div style="font-size: 11px; color: var(--slos-text-muted);">Detected generic pattern: "' + d.pattern_match + '"</div>';
                            }
                            html += '</div>';
                        }
                        
                        if (d.suggestions && d.suggestions.length > 0) {
                            html += '<div style="margin-top: 12px;">';
                            html += '<div style="font-size: 13px; font-weight: 600; color: var(--slos-text-primary); margin-bottom: 8px;">💡 Suggestions:</div>';
                            html += '<ul style="margin: 0; padding-left: 20px; font-size: 12px; color: var(--slos-text-muted); line-height: 1.6;">';
                            d.suggestions.forEach(function(suggestion) {
                                html += '<li style="margin-bottom: 4px;">' + suggestion + '</li>';
                            });
                            html += '</ul></div>';
                        }
                    }
                    
                    html += '</div>';
                    $result.html(html).addClass('show').removeClass('fail pass').addClass(d.is_descriptive ? 'pass' : 'fail');
                } else {
                    $result.html('<div style="color: var(--slos-error);">Error: ' + (response.data || 'Unknown error') + '</div>').addClass('show fail').removeClass('pass');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Validate');
                $result.html('<div style="color: var(--slos-error);">Network error</div>').addClass('show fail').removeClass('pass');
            }
        });
    });

    // Scanner Configuration - Save
    $(document).on('click', '#slos-save-config', function() {
        const $btn = $(this);
        const wcagLevel = $('#slos-wcag-level').val();
        const scanFrequency = $('#slos-scan-frequency').val();
        const postTypes = [];
        const checkers = [];
        
        $('.slos-scan-post-type:checked').each(function() {
            postTypes.push($(this).val());
        });
        
        $('.slos-checker-toggle:checked').each(function() {
            checkers.push($(this).val());
        });
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> Saving...');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_save_scanner_config',
                nonce: slosScanner.nonce,
                wcag_level: wcagLevel,
                scan_frequency: scanFrequency,
                scan_post_types: postTypes,
                active_checkers: checkers
            },
            success: function(response) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Configuration');
                
                if (response.success) {
                    // Show success notification
                    const $notification = $('<div class="slos-notification success">')
                        .html('<span class="dashicons dashicons-yes-alt"></span> Configuration saved successfully!')
                        .css({
                            position: 'fixed',
                            top: '32px',
                            right: '20px',
                            background: 'var(--slos-success)',
                            color: 'white',
                            padding: '12px 20px',
                            borderRadius: '6px',
                            zIndex: 10000,
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            boxShadow: '0 4px 12px rgba(0,0,0,0.3)'
                        });
                    
                    $('body').append($notification);
                    setTimeout(function() {
                        $notification.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    alert('Error: ' + (response.data?.message || 'Unknown error'));
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Configuration');
                alert('Network error while saving configuration');
            }
        });
    });

    // Scanner Configuration - Reset to Defaults
    $(document).on('click', '#slos-reset-config', function() {
        if (!confirm('Are you sure you want to reset all scanner configuration to defaults? This action cannot be undone.')) {
            return;
        }
        
        // Reset WCAG Level to AA
        $('#slos-wcag-level').val('AA');
        
        // Reset Scan Frequency to manual
        $('#slos-scan-frequency').val('manual');
        
        // Check only post and page
        $('.slos-scan-post-type').prop('checked', false);
        $('.slos-scan-post-type[value="post"], .slos-scan-post-type[value="page"]').prop('checked', true);
        
        // Uncheck all checkers (default is all enabled, but we'll uncheck for clarity)
        $('.slos-checker-toggle').prop('checked', false);
        
        alert('Configuration reset to defaults. Click "Save Configuration" to apply changes.');
    });

    // Scanner Configuration - Category Toggle All
    $(document).on('click', '.slos-category-toggle-all', function() {
        const category = $(this).data('category');
        const $checkers = $('.category-checkers[data-category="' + category + '"] .slos-checker-toggle');
        const allChecked = $checkers.length === $checkers.filter(':checked').length;
        
        $checkers.prop('checked', !allChecked);
    });

    // ========================================================================
    // 8. Widget Configuration Panel
    // ========================================================================

    // Initialize widget preview with current settings
    function initWidgetPreview() {
        var $preview = $('#slos-widget-preview');
        var position = $('input[name="slos-widget-position"]:checked').val() || 'bottom-right';
        var color = $('input[name="slos-widget-color"]:checked').val() || 'blue';

        // Set initial position
        $preview.removeClass('position-top-left position-top-right position-bottom-left position-bottom-right');
        $preview.addClass('position-' + position);

        // Set initial color theme
        $preview.removeClass('theme-blue theme-green theme-purple theme-orange theme-red theme-teal');
        $preview.addClass('theme-' + color);
    }

    // Update widget preview position
    $(document).on('change', 'input[name="slos-widget-position"]', function() {
        var position = $(this).val();
        var $preview = $('#slos-widget-preview');

        // Remove all position classes
        $preview.removeClass('position-top-left position-top-right position-bottom-left position-bottom-right');

        // Add new position class with animation
        setTimeout(function() {
            $preview.addClass('position-' + position);
        }, 50);
    });

    // Update widget preview color scheme
    $(document).on('change', 'input[name="slos-widget-color"]', function() {
        var color = $(this).val();
        var $preview = $('#slos-widget-preview');

        // Remove all theme classes
        $preview.removeClass('theme-blue theme-green theme-purple theme-orange theme-red theme-teal');

        // Add new theme class
        $preview.addClass('theme-' + color);
    });

    // Save Widget Configuration
    $(document).on('click', '#slos-save-widget-config', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var originalHtml = $btn.html();

        // Check if button is already loading
        if ($btn.hasClass('loading')) {
            return;
        }

        // Get configuration values
        var enabled = $('#slos-widget-enabled').is(':checked');
        var position = $('input[name="slos-widget-position"]:checked').val() || 'bottom-right';
        var color = $('input[name="slos-widget-color"]:checked').val() || 'blue';

        // Update button state
        $btn.addClass('loading').prop('disabled', true);
        $btn.html('<span class="dashicons dashicons-update spin"></span> Saving...');

        // Make AJAX request
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_save_widget_config',
                nonce: slosScanner.nonce,
                enabled: enabled,
                position: position,
                color: color
            },
            success: function(response) {
                if (response.success) {                    
                    // Update button state
                    $btn.removeClass('loading').prop('disabled', false);
                    $btn.html('<span class="dashicons dashicons-yes"></span> Saved!');

                    // Reset button after 2 seconds
                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 2000);

                    // Show inline success message
                    showReportNotification('success', response.data.message || 'Widget configuration saved successfully.');
                } else {
                    $btn.removeClass('loading').prop('disabled', false);
                    $btn.html(originalHtml);
                    showReportNotification('error', response.data.message || 'Failed to save widget configuration.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Widget Config Save Error:', error);
                $btn.removeClass('loading').prop('disabled', false);
                $btn.html(originalHtml);
                showReportNotification('error', 'An error occurred while saving widget configuration.');
            }
        });
    });

    // Initialize widget preview on page load
    if ($('#slos-widget-preview').length > 0) {
        initWidgetPreview();
    }

    // WCAG Card Action Buttons
    
    // Run Scan from WCAG Card
    $(document).on('click', '#slos-wcag-run-scan', function(e) {
        e.preventDefault();
        // Trigger the main scan button
        $('#slos-start-scan').trigger('click');
        
        // Scroll to scanner section
        $('html, body').animate({
            scrollTop: $('#slos-start-scan').closest('.slos-tools-card').offset().top - 100
        }, 500);
    });
    
    // Fix All from WCAG Card
    $(document).on('click', '#slos-wcag-fix-all', function(e) {
        e.preventDefault();
        const $btn = $(this);
        
        // Check if there are any issues to fix
        if ($btn.prop('disabled')) {
            return;
        }
        
        // Confirm action
        if (!confirm('This will attempt to automatically fix all accessibility issues across all pages. Continue?')) {
            return;
        }
        
        // Get all pages with issues from scan results
        const scanResults = slosScanner.lastScanResults || [];
        
        if (scanResults.length === 0) {
            alert('No scan results found. Please run a scan first.');
            return;
        }
        
        // Filter pages with issues
        const pagesWithIssues = scanResults.filter(function(page) {
            return page.issues_count && page.issues_count > 0;
        });
        
        if (pagesWithIssues.length === 0) {
            alert('No pages with issues found!');
            return;
        }
        
        // Use the Auto-Fix Progress modal to fix all pages
        if (typeof window.SLOSAutoFixProgress !== 'undefined') {
            const postIds = pagesWithIssues.map(function(page) {
                return page.post_id;
            });
            
            window.SLOSAutoFixProgress.show(postIds);
        } else {
            alert('Auto-fix system not available. Please refresh the page.');
        }
    });
    
    // Export from WCAG Card
    $(document).on('click', '#slos-wcag-export', function(e) {
        e.preventDefault();
        
        // Check if there are scan results
        const scanResults = slosScanner.lastScanResults || [];
        
        if (scanResults.length === 0) {
            alert('No scan results to export. Please run a scan first.');
            return;
        }
        
        // Trigger CSV export
        const csvData = scanResults.map(function(page) {
            return {
                post_id: page.post_id,
                title: page.title || 'Untitled',
                issues_count: page.issues_count || 0,
                critical_count: page.critical_count || 0,
                score: page.score || 100
            };
        });
        
        // Generate CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Post ID,Title,Total Issues,Critical Issues,Score\n";
        
        csvData.forEach(function(row) {
            const title = (row.title || '').replace(/"/g, '""');
            csvContent += row.post_id + ',"' + title + '",' + row.issues_count + ',' + row.critical_count + ',' + row.score + '\n';
        });
        
        // Download
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "wcag_compliance_report_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success notification
        if (typeof showNotification === 'function') {
            showNotification('success', 'Report exported successfully!');
        }
    });

});