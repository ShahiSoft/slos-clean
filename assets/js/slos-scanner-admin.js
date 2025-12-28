jQuery(document).ready(function($) {
    console.log('SLOS Scanner Admin JS Loaded');
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
});


