
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
