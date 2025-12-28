jQuery(document).ready(function($) {
    const $widget = $('#slos-accessibility-widget');
    const $toggle = $('#slos-aw-toggle');
    const $panel = $('#slos-aw-panel');
    const $close = $('#slos-aw-close');
    const $body = $('body');
    
    // State
    let fontSize = 100;
    let lineHeight = 1.5;
    let letterSpacing = 0;
    
    // Toggle Panel
    $toggle.on('click', function() {
        const expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
        $panel.toggleClass('is-open');
        if (!expanded) {
            $close.focus();
        }
    });
    
    $close.on('click', function() {
        $toggle.attr('aria-expanded', 'false');
        $panel.removeClass('is-open');
        $toggle.focus();
    });
    
    // Close on escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $panel.hasClass('is-open')) {
            $close.click();
        }
    });
    
    // Tracking
    function trackEvent(action, details = '') {
        if (typeof slosWidget !== 'undefined') {
            $.post(slosWidget.ajax_url, {
                action: 'slos_track_widget_event',
                event: action,
                details: details
            });
        }
    }

    // Feature Handlers
    $('.slos-aw-btn').on('click', function() {
        const action = $(this).data('action');
        const $btn = $(this);
        trackEvent('feature_click', action);
        
        switch(action) {
            case 'increase-text':
                fontSize += 10;
                $body.css('font-size', fontSize + '%');
                break;
                
            case 'decrease-text':
                fontSize = Math.max(70, fontSize - 10);
                $body.css('font-size', fontSize + '%');
                break;
                
            case 'readable-font':
                $body.toggleClass('slos-aw-readable-font');
                $btn.toggleClass('is-active');
                break;
                
            case 'highlight-links':
                $body.toggleClass('slos-aw-highlight-links');
                $btn.toggleClass('is-active');
                break;

            case 'underline-links':
                $body.toggleClass('slos-aw-underline-links');
                $btn.toggleClass('is-active');
                break;
                
            case 'big-cursor':
                $body.toggleClass('slos-aw-big-cursor');
                $btn.toggleClass('is-active');
                break;
                
            case 'stop-animations':
                $body.toggleClass('slos-aw-stop-animations');
                $btn.toggleClass('is-active');
                break;

            case 'highlight-headings':
                $body.toggleClass('slos-aw-highlight-headings');
                $btn.toggleClass('is-active');
                break;

            case 'hide-images':
                $body.toggleClass('slos-aw-hide-images');
                $btn.toggleClass('is-active');
                break;

            case 'reading-guide':
                $body.toggleClass('slos-aw-reading-guide-active');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-reading-guide-active')) {
                    if ($('#slos-aw-reading-guide').length === 0) {
                        $body.append('<div id="slos-aw-reading-guide" class="slos-aw-reading-guide"></div>');
                        $(document).on('mousemove', function(e) {
                            $('#slos-aw-reading-guide').css('top', e.clientY + 'px');
                        });
                    }
                    $('#slos-aw-reading-guide').show();
                } else {
                    $('#slos-aw-reading-guide').hide();
                }
                break;

            case 'increase-line-height':
                lineHeight += 0.5;
                if (lineHeight > 3) lineHeight = 1.5;
                $body.css('line-height', lineHeight);
                break;

            case 'increase-letter-spacing':
                letterSpacing += 1;
                if (letterSpacing > 5) letterSpacing = 0;
                $body.css('letter-spacing', letterSpacing + 'px');
                break;

            case 'align-left':
                $body.removeClass('slos-aw-align-center slos-aw-align-right').toggleClass('slos-aw-align-left');
                $btn.toggleClass('is-active');
                $('[data-action="align-center"], [data-action="align-right"]').removeClass('is-active');
                break;

            case 'align-center':
                $body.removeClass('slos-aw-align-left slos-aw-align-right').toggleClass('slos-aw-align-center');
                $btn.toggleClass('is-active');
                $('[data-action="align-left"], [data-action="align-right"]').removeClass('is-active');
                break;

            case 'align-right':
                $body.removeClass('slos-aw-align-left slos-aw-align-center').toggleClass('slos-aw-align-right');
                $btn.toggleClass('is-active');
                $('[data-action="align-left"], [data-action="align-center"]').removeClass('is-active');
                break;
                
            case 'grayscale':
                $body.toggleClass('slos-aw-grayscale');
                $btn.toggleClass('is-active');
                // Disable conflicting modes
                if ($body.hasClass('slos-aw-grayscale')) {
                    $body.removeClass('slos-aw-high-contrast slos-aw-negative-contrast slos-aw-monochrome slos-aw-low-saturation');
                    $('[data-action="high-contrast"], [data-action="negative-contrast"], [data-action="monochrome"], [data-action="low-saturation"]').removeClass('is-active');
                }
                break;

            case 'monochrome':
                $body.toggleClass('slos-aw-monochrome');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-monochrome')) {
                    $body.removeClass('slos-aw-grayscale slos-aw-high-contrast slos-aw-negative-contrast slos-aw-low-saturation');
                    $('[data-action="grayscale"], [data-action="high-contrast"], [data-action="negative-contrast"], [data-action="low-saturation"]').removeClass('is-active');
                }
                break;

            case 'low-saturation':
                $body.toggleClass('slos-aw-low-saturation');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-low-saturation')) {
                    $body.removeClass('slos-aw-grayscale slos-aw-high-contrast slos-aw-negative-contrast slos-aw-monochrome slos-aw-dark-mode slos-aw-blue-light slos-aw-protanopia slos-aw-deuteranopia slos-aw-tritanopia');
                    $('[data-action="grayscale"], [data-action="high-contrast"], [data-action="negative-contrast"], [data-action="monochrome"], [data-action="dark-mode"], [data-action="blue-light-filter"], [data-action="protanopia"], [data-action="deuteranopia"], [data-action="tritanopia"]').removeClass('is-active');
                }
                break;

            case 'dark-mode':
                $body.toggleClass('slos-aw-dark-mode');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-dark-mode')) {
                    $body.removeClass('slos-aw-grayscale slos-aw-high-contrast slos-aw-negative-contrast slos-aw-monochrome slos-aw-low-saturation slos-aw-light-background');
                    $('[data-action="grayscale"], [data-action="high-contrast"], [data-action="negative-contrast"], [data-action="monochrome"], [data-action="low-saturation"], [data-action="light-background"]').removeClass('is-active');
                }
                break;

            case 'blue-light-filter':
                $body.toggleClass('slos-aw-blue-light');
                $btn.toggleClass('is-active');
                break;

            case 'protanopia':
                $body.toggleClass('slos-aw-protanopia');
                $btn.toggleClass('is-active');
                $body.removeClass('slos-aw-deuteranopia slos-aw-tritanopia');
                $('[data-action="deuteranopia"], [data-action="tritanopia"]').removeClass('is-active');
                break;

            case 'deuteranopia':
                $body.toggleClass('slos-aw-deuteranopia');
                $btn.toggleClass('is-active');
                $body.removeClass('slos-aw-protanopia slos-aw-tritanopia');
                $('[data-action="protanopia"], [data-action="tritanopia"]').removeClass('is-active');
                break;

            case 'tritanopia':
                $body.toggleClass('slos-aw-tritanopia');
                $btn.toggleClass('is-active');
                $body.removeClass('slos-aw-protanopia slos-aw-deuteranopia');
                $('[data-action="protanopia"], [data-action="deuteranopia"]').removeClass('is-active');
                break;

            case 'reading-mask':
                $body.toggleClass('slos-aw-reading-mask-active');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-reading-mask-active')) {
                    if ($('#slos-aw-reading-mask-top').length === 0) {
                        $body.append('<div id="slos-aw-reading-mask-top" class="slos-aw-reading-mask"></div><div id="slos-aw-reading-mask-bottom" class="slos-aw-reading-mask"></div>');
                        $(document).on('mousemove', function(e) {
                            const y = e.clientY;
                            const height = 100; // Mask opening height
                            $('#slos-aw-reading-mask-top').css('height', (y - height/2) + 'px');
                            $('#slos-aw-reading-mask-bottom').css('top', (y + height/2) + 'px');
                        });
                    }
                    $('.slos-aw-reading-mask').show();
                } else {
                    $('.slos-aw-reading-mask').hide();
                }
                break;

            case 'text-to-speech':
                $body.toggleClass('slos-aw-tts-active');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-tts-active')) {
                    // Simple TTS implementation
                    $(document).on('mouseover.slos-tts', function(e) {
                        if ($body.hasClass('slos-aw-tts-active')) {
                            const text = $(e.target).text().trim();
                            if (text && text.length > 0 && text.length < 200) { // Limit length
                                window.speechSynthesis.cancel();
                                const utterance = new SpeechSynthesisUtterance(text);
                                window.speechSynthesis.speak(utterance);
                            }
                        }
                    });
                } else {
                    $(document).off('mouseover.slos-tts');
                    window.speechSynthesis.cancel();
                }
                break;

            case 'tooltip-hover':
                $body.toggleClass('slos-aw-tooltip-hover');
                $btn.toggleClass('is-active');
                break;

            case 'page-structure':
                // Toggle a modal with page structure
                if ($('#slos-aw-structure-modal').length === 0) {
                    let structure = '<div id="slos-aw-structure-modal" class="slos-aw-modal"><div class="slos-aw-modal-content"><h3>Page Structure</h3><button class="slos-aw-modal-close">×</button><ul>';
                    $('h1, h2, h3, h4, h5, h6, [role="main"], [role="navigation"], [role="banner"], [role="contentinfo"]').each(function() {
                        const tag = this.tagName.toLowerCase();
                        const text = $(this).text().trim() || 'No text';
                        const role = $(this).attr('role') || '';
                        structure += `<li><strong>${tag}${role ? ' ('+role+')' : ''}</strong>: ${text}</li>`;
                    });
                    structure += '</ul></div></div>';
                    $body.append(structure);
                    
                    $('.slos-aw-modal-close').on('click', function() {
                        $('#slos-aw-structure-modal').hide();
                        $btn.removeClass('is-active');
                    });
                }
                $('#slos-aw-structure-modal').toggle();
                $btn.toggleClass('is-active');
                break;
                
            case 'high-contrast':
                $body.toggleClass('slos-aw-high-contrast');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-high-contrast')) {
                    $body.removeClass('slos-aw-grayscale slos-aw-negative-contrast');
                    $('[data-action="grayscale"], [data-action="negative-contrast"]').removeClass('is-active');
                }
                break;
                
            case 'negative-contrast':
                $body.toggleClass('slos-aw-negative-contrast');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-negative-contrast')) {
                    $body.removeClass('slos-aw-grayscale slos-aw-high-contrast');
                    $('[data-action="grayscale"], [data-action="high-contrast"]').removeClass('is-active');
                }
                break;
                
            case 'light-background':
                $body.toggleClass('slos-aw-light-background');
                $btn.toggleClass('is-active');
                break;

            case 'smart-contrast':
                $body.toggleClass('slos-aw-smart-contrast');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-smart-contrast')) {
                    $body.removeClass('slos-aw-grayscale slos-aw-high-contrast slos-aw-negative-contrast slos-aw-monochrome slos-aw-low-saturation slos-aw-dark-mode slos-aw-light-background');
                    $('[data-action="grayscale"], [data-action="high-contrast"], [data-action="negative-contrast"], [data-action="monochrome"], [data-action="low-saturation"], [data-action="dark-mode"], [data-action="light-background"]').removeClass('is-active');
                }
                break;

            case 'virtual-keyboard':
                $body.toggleClass('slos-aw-virtual-keyboard-active');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-virtual-keyboard-active')) {
                    if ($('#slos-aw-virtual-keyboard').length === 0) {
                        const keys = [
                            '1','2','3','4','5','6','7','8','9','0','Backspace',
                            'q','w','e','r','t','y','u','i','o','p',
                            'a','s','d','f','g','h','j','k','l','Enter',
                            'z','x','c','v','b','n','m',',','.','Space'
                        ];
                        let keyboardHtml = '<div id="slos-aw-virtual-keyboard" class="slos-aw-virtual-keyboard"><div class="slos-aw-vk-header">Virtual Keyboard <span class="slos-aw-vk-close">×</span></div><div class="slos-aw-vk-keys">';
                        keys.forEach(key => {
                            keyboardHtml += `<button class="slos-aw-vk-key" data-key="${key}">${key === 'Space' ? ' ' : key}</button>`;
                        });
                        keyboardHtml += '</div></div>';
                        $body.append(keyboardHtml);

                        // Drag functionality
                        const $vk = $('#slos-aw-virtual-keyboard');
                        let isDragging = false;
                        let offset = { x: 0, y: 0 };

                        $vk.find('.slos-aw-vk-header').on('mousedown', function(e) {
                            isDragging = true;
                            offset.x = e.clientX - $vk[0].offsetLeft;
                            offset.y = e.clientY - $vk[0].offsetTop;
                        });

                        $(document).on('mousemove', function(e) {
                            if (isDragging) {
                                $vk.css({
                                    left: (e.clientX - offset.x) + 'px',
                                    top: (e.clientY - offset.y) + 'px',
                                    bottom: 'auto',
                                    right: 'auto'
                                });
                            }
                        });

                        $(document).on('mouseup', function() {
                            isDragging = false;
                        });

                        // Key press functionality
                        $('.slos-aw-vk-key').on('click', function() {
                            const key = $(this).data('key');
                            const $active = $(document.activeElement);
                            if ($active.is('input, textarea')) {
                                const val = $active.val();
                                if (key === 'Backspace') {
                                    $active.val(val.slice(0, -1));
                                } else if (key === 'Enter') {
                                    // Trigger enter key event or insert newline
                                    $active.val(val + '\n');
                                } else if (key === 'Space') {
                                    $active.val(val + ' ');
                                } else {
                                    $active.val(val + key);
                                }
                            }
                        });

                        $('.slos-aw-vk-close').on('click', function() {
                            $body.removeClass('slos-aw-virtual-keyboard-active');
                            $('[data-action="virtual-keyboard"]').removeClass('is-active');
                            $vk.hide();
                        });
                    }
                    $('#slos-aw-virtual-keyboard').show();
                } else {
                    $('#slos-aw-virtual-keyboard').hide();
                }
                break;

            case 'dictionary':
                $body.toggleClass('slos-aw-dictionary-active');
                $btn.toggleClass('is-active');
                if ($body.hasClass('slos-aw-dictionary-active')) {
                    $(document).on('mouseup.slos-dictionary', function(e) {
                        const selection = window.getSelection().toString().trim();
                        if (selection && selection.length > 0 && selection.split(' ').length === 1) {
                            // Fetch definition
                            fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${selection}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (Array.isArray(data) && data.length > 0) {
                                        const word = data[0].word;
                                        const phonetic = data[0].phonetic || '';
                                        const meaning = data[0].meanings[0].definitions[0].definition;
                                        
                                        if ($('#slos-aw-dictionary-modal').length === 0) {
                                            $body.append('<div id="slos-aw-dictionary-modal" class="slos-aw-modal"><div class="slos-aw-modal-content"><h3>Dictionary</h3><button class="slos-aw-modal-close-dict">×</button><div id="slos-aw-dict-content"></div></div></div>');
                                            $('.slos-aw-modal-close-dict').on('click', function() {
                                                $('#slos-aw-dictionary-modal').hide();
                                            });
                                        }
                                        
                                        $('#slos-aw-dict-content').html(`<strong>${word}</strong> ${phonetic}<br><br>${meaning}`);
                                        $('#slos-aw-dictionary-modal').show();
                                    }
                                })
                                .catch(err => console.log('Dictionary API Error:', err));
                        }
                    });
                } else {
                    $(document).off('mouseup.slos-dictionary');
                    $('#slos-aw-dictionary-modal').hide();
                }
                break;

            case 'translate':
                window.open(`https://translate.google.com/translate?sl=auto&tl=en&u=${encodeURIComponent(window.location.href)}`, '_blank');
                break;

            case 'reader-mode':
                $body.toggleClass('slos-aw-reader-mode');
                $btn.toggleClass('is-active');
                break;

            // Profiles
            case 'profile-epilepsy':
                resetAll();
                $btn.addClass('is-active');
                $('[data-action="stop-animations"]').click();
                $('[data-action="low-saturation"]').click();
                break;

            case 'profile-visually-impaired':
                resetAll();
                $btn.addClass('is-active');
                $('[data-action="big-cursor"]').click();
                $('[data-action="high-contrast"]').click();
                $('[data-action="increase-text"]').click();
                $('[data-action="increase-text"]').click();
                break;

            case 'profile-cognitive':
                resetAll();
                $btn.addClass('is-active');
                $('[data-action="highlight-headings"]').click();
                $('[data-action="highlight-links"]').click();
                $('[data-action="reading-guide"]').click();
                break;

            case 'profile-adhd':
                resetAll();
                $btn.addClass('is-active');
                $('[data-action="reading-mask"]').click();
                $('[data-action="stop-animations"]').click();
                break;

            case 'profile-blind':
                resetAll();
                $btn.addClass('is-active');
                $('[data-action="text-to-speech"]').click();
                break;
        }
    });
    
    function resetAll() {
        fontSize = 100;
        lineHeight = 1.5;
        letterSpacing = 0;
        $body.css({
            'font-size': '',
            'line-height': '',
            'letter-spacing': ''
        });
        $body.removeClass('slos-aw-readable-font slos-aw-highlight-links slos-aw-underline-links slos-aw-big-cursor slos-aw-stop-animations slos-aw-grayscale slos-aw-high-contrast slos-aw-negative-contrast slos-aw-light-background slos-aw-highlight-headings slos-aw-hide-images slos-aw-reading-guide-active slos-aw-align-left slos-aw-align-center slos-aw-align-right slos-aw-monochrome slos-aw-low-saturation slos-aw-dark-mode slos-aw-blue-light slos-aw-protanopia slos-aw-deuteranopia slos-aw-tritanopia slos-aw-reading-mask-active slos-aw-tts-active slos-aw-tooltip-hover slos-aw-smart-contrast slos-aw-virtual-keyboard-active slos-aw-dictionary-active slos-aw-reader-mode');
        $('.slos-aw-btn').removeClass('is-active');
        $('#slos-aw-reading-guide').hide();
        $('.slos-aw-reading-mask').hide();
        $('#slos-aw-virtual-keyboard').hide();
        $('#slos-aw-dictionary-modal').hide();
        $(document).off('mouseover.slos-tts');
        $(document).off('mouseup.slos-dictionary');
        window.speechSynthesis.cancel();
        $('#slos-aw-structure-modal').hide();
    }

    // Reset
    $('#slos-aw-reset').on('click', resetAll);
});

