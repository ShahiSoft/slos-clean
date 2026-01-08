jQuery(document).ready(function($) {
    console.log('SLOS Scanner Admin JS Loaded');
    let postIds = [];
    let currentIdx = 0;
    let results = [];

    $('#slos-start-scan').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);

        // Prefer the modal-based progress UI when available
        if (typeof window.SLOSScanProgress !== 'undefined' && typeof window.SLOSScanProgress.start === 'function') {
            if (typeof window.SLOSScanProgress.init === 'function' && !$('#slos-scan-progress-overlay').length) {
                window.SLOSScanProgress.init();
            }

            $btn.prop('disabled', true).text('Scanning...');

            window.SLOSScanProgress.start({
                onComplete: function() {
                    $btn.prop('disabled', false).text('Start Full Scan');
                }
            });

            $('#slos-scan-status, #slos-progress-bar-wrapper').hide();
            return;
        }

        // Fallback to legacy inline progress
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

    // Statement Generator
    $('#slos-generate-statement').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: slosScanner.ajax_url,
            type: 'POST',
            data: {
                action: 'slos_generate_statement',
                nonce: slosScanner.nonce
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Generate Accessibility Statement');
                if (response.success) {
                    $('#slos-statement-result').html(
                        '<div class="notice notice-success inline"><p>Statement generated! <a href="' + response.data.edit_link + '" target="_blank">Edit Page</a> | <a href="' + response.data.view_link + '" target="_blank">View Page</a></p></div>'
                    );
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Generate Accessibility Statement');
                alert('Network error.');
            }
        });
    });

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
        let $status = $('#slos-report-status');
        if (!$status.length) {
            const $container = $('#slos-schedule-report').closest('.slos-tool-content');
            $status = $('<div id="slos-report-status"></div>').css({
                marginTop: '10px',
                padding: '10px 12px',
                borderRadius: '6px',
                border: '1px solid #d1d5db',
                background: '#f9fafb',
                color: '#111827',
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
                borderColor: isError ? '#ef4444' : '#10b981',
                background: isError ? 'rgba(239,68,68,0.12)' : 'rgba(16,185,129,0.12)'
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

    function slosIsValidEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
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

        if (!emails.length || !emails.every(slosIsValidEmail)) {
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

        if (!email || !slosIsValidEmail(email)) {
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
        // "Auto Fix All" / "Fix All" button handler - uses new progress modal
        $(document).on('click', '.slos-autofix-trigger, #slos-autofix-all-btn, .slos-fix-all-btn, .slos-autofix-post-btn', function(e) {
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
                        // Update button state temporarily
                        const originalHtml = $btn.html();
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
                        
                        // Reset button after delay
                        setTimeout(function() {
                            $btn.removeClass('fixed').html(originalHtml);
                        }, 3000);
                    }
                    
                    // Show completion notification
                    let message = 'Auto-fix complete! Fixed ' + results.fixed + ' issue(s).';
                    if (results.errors > 0) {
                        message += ' ' + results.errors + ' error(s) occurred.';
                    }
                    
                    // Optional: Ask to refresh if fixes were applied
                    if (results.fixed > 0) {
                        setTimeout(function() {
                            if (confirm(message + '\n\nWould you like to refresh the page to see updated results?')) {
                                location.reload();
                            }
                        }, 500);
                    }
                }
            });
        });
    }
    
    // Initialize auto-fix handlers
    initAutoFixHandlers();

});