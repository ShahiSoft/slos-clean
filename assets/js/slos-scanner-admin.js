jQuery(document).ready(function($) {
    console.log('SLOS Scanner Admin JS Loaded');
    console.log('SLOSAutoFixProgress available:', typeof window.SLOSAutoFixProgress);
    console.log('slosautoFixConfig available:', typeof window.slosautoFixConfig);
    console.log('slosScanner available:', typeof window.slosScanner);
    console.log('.slos-fix-all-btn buttons found:', $('.slos-fix-all-btn').length);
    
    let postIds = [];
    let currentIdx = 0;
    let results = [];

    $('#slos-start-scan').on('click', function() {
        $(this).prop('disabled', true);
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

});