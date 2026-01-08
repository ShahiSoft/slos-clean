
    // Readability Score Checker
    $(document).on('click', '#slos-check-readability', function() {
        const $btn = $(this);
        const text = $('#slos-readability-text').val().trim();
        const $result = $('#slos-readability-result');
        
        if (!text) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Please enter text to analyze</div>').addClass('show fail').removeClass('pass');
            return;
        }
        
        // Check minimum text length
        const words = text.split(/\s+/).filter(Boolean);
        if (words.length < 3) {
            $result.html('<div style="color: var(--slos-error);">⚠️ Please enter at least 3 words</div>').addClass('show fail').removeClass('pass');
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
                    html += '<div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 8px;">';
                    html += '<div style="font-size: 24px; font-weight: 700; color: var(--slos-text-primary);">Grade ' + d.grade_level + '</div>';
                    html += '<div style="font-size: 14px; color: var(--slos-text-muted);">(' + d.level_name + ')</div>';
                    html += '</div>';
                    html += '<div style="font-size: 13px; color: var(--slos-text-muted); margin-bottom: 12px;">' + d.interpretation + '</div>';
                    html += '<div style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: ' + (d.passes_wcag ? 'var(--slos-success)' : 'var(--slos-warning)') + ';">';
                    html += (d.passes_wcag ? '✓ ' : '⚠ ') + d.recommendation;
                    html += '</div>';
                    html += '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; font-size: 12px;">';
                    html += '<div style="text-align: center; padding: 8px; background: var(--slos-bg-primary); border-radius: 4px;">';
                    html += '<div style="font-weight: 600; color: var(--slos-text-primary);">' + d.words + '</div>';
                    html += '<div style="color: var(--slos-text-muted);">Words</div>';
                    html += '</div>';
                    html += '<div style="text-align: center; padding: 8px; background: var(--slos-bg-primary); border-radius: 4px;">';
                    html += '<div style="font-weight: 600; color: var(--slos-text-primary);">' + d.sentences + '</div>';
                    html += '<div style="color: var(--slos-text-muted);">Sentences</div>';
                    html += '</div>';
                    html += '<div style="text-align: center; padding: 8px; background: var(--slos-bg-primary); border-radius: 4px;">';
                    html += '<div style="font-weight: 600; color: var(--slos-text-primary);">' + d.syllables + '</div>';
                    html += '<div style="color: var(--slos-text-muted);">Syllables</div>';
                    html += '</div>';
                    html += '</div></div>';
                    $result.html(html).addClass('show').removeClass('fail pass').addClass(d.passes_wcag ? 'pass' : 'fail');
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
