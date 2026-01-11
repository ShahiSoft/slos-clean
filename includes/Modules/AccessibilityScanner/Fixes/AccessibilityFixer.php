<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry;

class AccessibilityFixer {

	private $active_fixes = array();

	public function __construct() {
		// Load active fixes from settings
		$this->active_fixes = get_option( 'slos_active_fixes', array() );

		// Initialize hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fix_assets' ) );
		add_action( 'wp_footer', array( $this, 'inject_js_fixes' ) );
		add_filter( 'language_attributes', array( $this, 'fix_language_attributes' ) );
		add_filter( 'the_content', array( $this, 'apply_content_fixes' ), 20 );
		add_action( 'wp_head', array( $this, 'fix_viewport_meta' ), 1 );
		add_filter( 'pre_get_document_title', array( $this, 'fix_page_title' ), 99 );
		add_filter( 'wp_title', array( $this, 'fix_page_title' ), 99 );
	}

	public function enqueue_fix_assets() {
		// Enqueue CSS first
		wp_enqueue_style(
			'slos-a11y-fixes',
			plugin_dir_url( __FILE__ ) . '../../../../assets/css/slos-a11y-fixes.css',
			array(),
			'1.1.0'
		);

		// Enqueue JavaScript (depends on no libraries - vanilla JS)
		wp_enqueue_script(
			'slos-a11y-fixes',
			plugin_dir_url( __FILE__ ) . '../../../../assets/js/slos-a11y-fixes.js',
			array(), // No dependencies - vanilla JavaScript
			'1.0.0',
			true // Load in footer
		);

		// Pass configuration to JavaScript
		wp_localize_script(
			'slos-a11y-fixes',
			'slosa11yConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slos_a11y_nonce' ),
				'i18n'    => array(
					'pauseAnimation'   => __( 'Pause animation', 'shahi-legalflowsuite' ),
					'playAnimation'    => __( 'Play animation', 'shahi-legalflowsuite' ),
					'pauseCarousel'    => __( 'Pause carousel', 'shahi-legalflowsuite' ),
					'playCarousel'     => __( 'Play carousel', 'shahi-legalflowsuite' ),
					'extendTime'       => __( 'Extend time', 'shahi-legalflowsuite' ),
					'cancelRefresh'    => __( 'Cancel refresh', 'shahi-legalflowsuite' ),
					'dialogClosed'     => __( 'Dialog closed', 'shahi-legalflowsuite' ),
					'animationPaused'  => __( 'Animation paused', 'shahi-legalflowsuite' ),
					'animationResumed' => __( 'Animation resumed', 'shahi-legalflowsuite' ),
					'timerPaused'      => __( 'Timer paused', 'shahi-legalflowsuite' ),
					'timerResumed'     => __( 'Timer resumed', 'shahi-legalflowsuite' ),
				),
			)
		);

		// Add body classes for CSS fixes
		$classes = array();
		if ( in_array( 'fix_focus_outlines', $this->active_fixes ) ) {
			$classes[] = 'slos-fix-focus-outlines';
		}
		if ( in_array( 'fix_link_underlines', $this->active_fixes ) ) {
			$classes[] = 'slos-fix-link-underlines';
		}
		if ( in_array( 'fix_link_warnings', $this->active_fixes ) ) {
			$classes[] = 'slos-fix-link-warnings';
		}
		if ( in_array( 'fix_color_contrast', $this->active_fixes ) ) {
			$classes[] = 'slos-fix-color-contrast';
		}

		if ( ! empty( $classes ) ) {
			add_filter(
				'body_class',
				function ( $body_classes ) use ( $classes ) {
					return array_merge( $body_classes, $classes );
				}
			);
		}
	}

	public function fix_page_title( $title ) {
		if ( in_array( 'add_page_titles', $this->active_fixes ) ) {
			if ( empty( $title ) ) {
				return get_bloginfo( 'name' );
			}
		}
		return $title;
	}

	public function inject_js_fixes() {
		?>
		<script>
		(function($) {
			'use strict';
			$(document).ready(function() {
				<?php if ( in_array( 'skip_links', $this->active_fixes ) ) : ?>
				// 1. Add Skip Links
				if ($('#skip-link').length === 0) {
					$('body').prepend('<a id="skip-link" class="slos-skip-link" href="#main">Skip to content</a>');
					if ($('#main').length === 0) {
						$('main, [role="main"], article, .content-area').first().attr('id', 'main');
					}
				}
				<?php endif; ?>

				<?php if ( in_array( 'block_new_window', $this->active_fixes ) ) : ?>
				// 4. Block New Window Links
				$('a[target="_blank"]').removeAttr('target');
				<?php endif; ?>

				<?php if ( in_array( 'label_search', $this->active_fixes ) ) : ?>
				// 7. Label Search Fields
				$('input[type="search"], .search-field').each(function() {
					if (!$(this).attr('aria-label') && !$(this).attr('id')) {
						$(this).attr('aria-label', 'Search');
					}
				});
				<?php endif; ?>

				<?php if ( in_array( 'label_comments', $this->active_fixes ) ) : ?>
				// 8. Label Comment Fields
				$('#comment').attr('aria-label', 'Comment');
				$('input#author').attr('aria-label', 'Name');
				$('input#email').attr('aria-label', 'Email');
				$('input#url').attr('aria-label', 'Website');
				<?php endif; ?>

				<?php if ( in_array( 'fix_tab_index', $this->active_fixes ) ) : ?>
				// 10. Fix Tab Index
				$('[tabindex]').each(function() {
					if (parseInt($(this).attr('tabindex')) > 0) {
						$(this).removeAttr('tabindex');
					}
				});
				<?php endif; ?>

				<?php if ( in_array( 'remove_title_attrs', $this->active_fixes ) ) : ?>
				// 11. Remove Title Attributes
				$('[title]').removeAttr('title');
				<?php endif; ?>

				<?php if ( in_array( 'add_landmarks', $this->active_fixes ) ) : ?>
				// 13. Add ARIA Landmarks
				$('header:not([role])').attr('role', 'banner');
				$('nav:not([role])').attr('role', 'navigation');
				$('main:not([role])').attr('role', 'main');
				$('footer:not([role])').attr('role', 'contentinfo');
				$('aside:not([role])').attr('role', 'complementary');
				$('form[role="search"]').attr('role', 'search');
				<?php endif; ?>

				<?php if ( in_array( 'add_form_labels', $this->active_fixes ) ) : ?>
				// 17. Add Form Labels (from placeholders)
				$('input:not([type="submit"]):not([type="hidden"]):not([type="button"]), textarea, select').each(function() {
					if (!$(this).attr('id') && !$(this).attr('aria-label') && !$(this).attr('aria-labelledby')) {
						var placeholder = $(this).attr('placeholder');
						if (placeholder) {
							$(this).attr('aria-label', placeholder);
						} else {
							$(this).attr('aria-label', 'Input field');
						}
					}
				});
				<?php endif; ?>

				<?php if ( in_array( 'add_button_labels', $this->active_fixes ) ) : ?>
				// 21. Add Button Labels
				$('button, a.button, .btn').each(function() {
					if ($(this).text().trim() === '' && !$(this).attr('aria-label')) {
						var icon = $(this).find('i, span.icon, svg').first();
						var label = 'Button';
						if (icon.length) {
							// Try to guess from class name
							var classNames = icon.attr('class') || '';
							if (classNames.includes('search')) label = 'Search';
							else if (classNames.includes('menu')) label = 'Menu';
							else if (classNames.includes('close')) label = 'Close';
							else if (classNames.includes('facebook')) label = 'Facebook';
							else if (classNames.includes('twitter')) label = 'Twitter';
						}
						$(this).attr('aria-label', label);
					}
				});
				<?php endif; ?>

				<?php if ( in_array( 'fix_modal_dialogs', $this->active_fixes ) ) : ?>
				// 24. Fix Modal Dialogs
				$('.modal, .popup, .dialog, [class*="modal"], [class*="popup"]').attr({
					'role': 'dialog',
					'aria-modal': 'true'
				});
				<?php endif; ?>

				<?php if ( in_array( 'fix_image_maps', $this->active_fixes ) ) : ?>
				// 20. Fix Image Maps
				$('area').each(function() {
					if (!$(this).attr('alt')) {
						$(this).attr('alt', 'Image Map Area');
					}
				});
				<?php endif; ?>

				<?php if ( in_array( 'add_live_regions', $this->active_fixes ) ) : ?>
				// 23. Add Live Regions
				$('.alert, .notice, .error, .success, [class*="message"], [class*="notification"]').attr('aria-live', 'polite');
				<?php endif; ?>

				<?php if ( in_array( 'generate_transcripts', $this->active_fixes ) ) : ?>
				// 25. Generate Transcripts (Structure)
				$('audio, video').each(function() {
					if ($(this).next('.slos-transcript').length === 0) {
						// Check if track exists
						if ($(this).find('track[kind="captions"], track[kind="subtitles"]').length === 0) {
							$(this).after('<div class="slos-transcript" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;"><p><strong>Transcript:</strong> <span style="font-style: italic; color: #666;">(No transcript available. Please contact site administrator.)</span></p></div>');
						}
					}
				});
				<?php endif; ?>
			});
		})(jQuery);
		</script>
		<?php
	}

	public function fix_language_attributes( $output ) {
		if ( in_array( 'add_lang_attr', $this->active_fixes ) ) {
			if ( strpos( $output, 'lang=' ) === false ) {
				$output .= ' lang="' . get_bloginfo( 'language' ) . '"';
			}
			if ( strpos( $output, 'dir=' ) === false ) {
				$output .= ' dir="' . ( is_rtl() ? 'rtl' : 'ltr' ) . '"';
			}
		}
		return $output;
	}

	public function fix_viewport_meta() {
		if ( in_array( 'scalable_viewport', $this->active_fixes ) ) {
			// Remove existing viewport meta if possible (hard in WP without output buffering)
			// Instead, we can inject JS to fix it or rely on theme support.
			// Here we'll try to output a correct one that might override.
			echo '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">';
		}
	}

	public function apply_content_fixes( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		// 12. Add Alt Text Placeholders
		if ( in_array( 'add_alt_placeholders', $this->active_fixes ) ) {
			$content = preg_replace( '/<img(?![^>]*\balt=)[^>]*>/i', '$0 alt="[Image]"', $content );
			$content = preg_replace( '/(<img[^>]*\balt=["\'])\s*(["\'][^>]*>)/i', '$1[Image]$2', $content );
		}

		// 14. Fix Empty Links
		if ( in_array( 'fix_empty_links', $this->active_fixes ) ) {
			$content = preg_replace_callback(
				'/<a([^>]*)>(.*?)<\/a>/is',
				function ( $matches ) {
					$attrs = $matches[1];
					$text  = trim( strip_tags( $matches[2] ) );
					if ( empty( $text ) && strpos( $attrs, 'aria-label' ) === false ) {
						// Check if it has an image
						if ( strpos( $matches[2], '<img' ) !== false ) {
							return $matches[0]; // Has image, assume image has alt (handled by other fix)
						}
						return '<a' . $attrs . ' aria-label="Link"></a>';
					}
					return $matches[0];
				},
				$content
			);
		}

		// 16. Add Table Headers
		if ( in_array( 'add_table_headers', $this->active_fixes ) ) {
			$content = preg_replace_callback(
				'/<table[^>]*>.*?<\/table>/is',
				function ( $matches ) {
					$table = $matches[0];
					// Find first tr
					if ( preg_match( '/<tr[^>]*>(.*?)<\/tr>/is', $table, $tr_match ) ) {
						$first_row = $tr_match[1];
						// Replace td with th in first row
						$new_first_row = str_replace( '<td', '<th scope="col"', $first_row );
						$new_first_row = str_replace( '</td>', '</th>', $new_first_row );
						$table         = str_replace( $first_row, $new_first_row, $table );
					}
					return $table;
				},
				$content
			);
		}

		// 22. Fix List Semantics (Wrap orphan li)
		if ( in_array( 'fix_list_semantics', $this->active_fixes ) ) {
			// This is complex regex, simplified for now: ensure <li> are inside <ul> or <ol>
			// WP usually handles this well, but custom HTML might not.
		}

		return $content;
	}

	/**
	 * Fix a specific issue on a page using content-aware fixers
	 *
	 * @param int    $post_id The post ID to fix
	 * @param string $issue_type The type of accessibility issue
	 * @return array|WP_Error Array with 'fixed_count' and 'content' keys or WP_Error
	 */
	public function fix_issue( $post_id, $issue_type ) {
		// Debug logging
		error_log( "SLOS FIX_ISSUE: Starting fix for post_id=$post_id, issue_type=$issue_type" );

		// Get page content
		$content = $this->get_page_content( $post_id );

		if ( empty( $content ) ) {
			error_log( "SLOS FIX_ISSUE: Content empty for post_id=$post_id" );
			return new \WP_Error( 'content_not_found', 'Could not retrieve page content' );
		}

		error_log( 'SLOS FIX_ISSUE: Content length=' . strlen( $content ) );

		// Initialize fixer registry and get the appropriate fixer
		FixerRegistry::init();
		$fixer = FixerRegistry::get_fixer( $issue_type );

		if ( ! $fixer ) {
			error_log( "SLOS FIX_ISSUE: No fixer found for issue_type=$issue_type" );
			return new \WP_Error( 'fixer_not_found', sprintf( 'No fixer available for issue type: %s', $issue_type ) );
		}

		error_log( 'SLOS FIX_ISSUE: Found fixer class=' . get_class( $fixer ) );

		// Apply the fix
		try {
			$result = $fixer->fix( $content );

			error_log( 'SLOS FIX_ISSUE: Fixer returned fixed_count=' . ( $result['fixed_count'] ?? 'null' ) );
			error_log( 'SLOS FIX_ISSUE: Content changed=' . ( $result['content'] !== $content ? 'YES' : 'NO' ) );

			if ( ! isset( $result['fixed_count'] ) || ! isset( $result['content'] ) ) {
				error_log( 'SLOS FIX_ISSUE: Invalid fixer result' );
				return new \WP_Error( 'invalid_fixer_result', 'Fixer returned invalid result' );
			}

			// If no fixes were applied, try a resilient fallback for known fixable types
			if ( intval( $result['fixed_count'] ) === 0 ) {
				$fallback_result = $this->apply_fallback_fix( $issue_type, $result['content'], $post_id );
				if ( $fallback_result && isset( $fallback_result['fixed_count'] ) && $fallback_result['fixed_count'] > 0 ) {
					$result = $fallback_result;
					error_log( "SLOS FIX_ISSUE: Fallback applied for issue_type=$issue_type fixed_count=" . $result['fixed_count'] );
				}
			}

			// Update post content with fixed content
			$post_update = array(
				'ID'           => $post_id,
				'post_content' => $result['content'],
			);
			$updated     = wp_update_post( $post_update );

			// Clear cache to ensure next fix operation gets fresh content
			if ( $updated && ! is_wp_error( $updated ) ) {
				clean_post_cache( $post_id );
			}

			return $result;
		} catch ( \Exception $e ) {
			error_log( 'SLOS FIX_ISSUE: Exception - ' . $e->getMessage() );
			return new \WP_Error( 'fixer_exception', sprintf( 'Error applying fix: %s', $e->getMessage() ) );
		}
	}

	/**
	 * Fallback fixes for common content-level issues when primary fixer returns zero changes.
	 * This is intentionally conservative and only targets a small set of issue types
	 * that should always be fixable via HTML mutations.
	 */
	private function apply_fallback_fix( $issue_type, $content, $post_id ) {
		$fallback_types = array(
			'missing-h1',
			'missing-form-label',
			'link-dest',
			'link-destination',
			'download-link',
			'external-link',
			'skip-link',
		);

		if ( ! in_array( $issue_type, $fallback_types, true ) ) {
			return null;
		}

		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		libxml_use_internal_errors( true );
		$wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>';
		$dom->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		if ( ! $body ) {
			$body = $dom->createElement( 'body' );
			$dom->appendChild( $body );
		}

		$fixed_count = 0;

		switch ( $issue_type ) {
			case 'missing-h1':
				$h1s = $dom->getElementsByTagName( 'h1' );
				if ( $h1s->length === 0 ) {
					$h1              = $dom->createElement( 'h1' );
					$title           = get_the_title( $post_id ) ?: get_bloginfo( 'name' );
					$h1->textContent = $title ?: 'Page Title';
					if ( $body->firstChild ) {
						$body->insertBefore( $h1, $body->firstChild );
					} else {
						$body->appendChild( $h1 );
					}
					$fixed_count = 1;
				}
				break;

			case 'missing-form-label':
				$xpath   = new \DOMXPath( $dom );
				$inputs  = $dom->getElementsByTagName( 'input' );
				$texts   = $dom->getElementsByTagName( 'textarea' );
				$selects = $dom->getElementsByTagName( 'select' );

				$elements = array();
				foreach ( array( $inputs, $texts, $selects ) as $nodeList ) {
					foreach ( $nodeList as $el ) {
						$elements[] = $el;
					}
				}

				foreach ( $elements as $el ) {
					$tag  = strtolower( $el->tagName );
					$type = strtolower( $el->getAttribute( 'type' ) ?: 'text' );

					if ( $tag === 'input' && in_array( $type, array( 'hidden', 'submit', 'button', 'reset', 'image' ), true ) ) {
						continue;
					}

					if ( $el->hasAttribute( 'aria-label' ) || $el->hasAttribute( 'aria-labelledby' ) ) {
						continue;
					}

					if ( $el->hasAttribute( 'id' ) ) {
						$id    = $el->getAttribute( 'id' );
						$label = $xpath->query( "//label[@for='$id']" );
						if ( $label->length > 0 ) {
							continue;
						}
					}

					$name        = $el->getAttribute( 'name' );
					$placeholder = $el->getAttribute( 'placeholder' );
					$label_text  = $placeholder ?: $name;
					if ( empty( $label_text ) ) {
						$label_text = ucfirst( $tag );
					}
					$el->setAttribute( 'aria-label', $label_text );
					++$fixed_count;
				}
				break;

			case 'link-dest':
			case 'link-destination':
				$links = $dom->getElementsByTagName( 'a' );
				foreach ( $links as $link ) {
					$href = $link->hasAttribute( 'href' ) ? trim( $link->getAttribute( 'href' ) ) : '';
					// Match empty href or ANY javascript: href
					if ( $href === '' || preg_match( '/^javascript:/i', $href ) ) {
						$link->setAttribute( 'href', '#' );
						if ( ! $link->hasAttribute( 'role' ) ) {
							$link->setAttribute( 'role', 'button' );
						}
						++$fixed_count;
					}
				}
				break;

			case 'download-link':
				$links = $dom->getElementsByTagName( 'a' );
				$exts  = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'ppt', 'pptx', 'csv', 'txt', 'rtf' );
				foreach ( $links as $link ) {
					$href = strtolower( $link->getAttribute( 'href' ) );
					if ( empty( $href ) ) {
						continue;
					}
					$is_download = $link->hasAttribute( 'download' );
					$file_ext    = '';
					foreach ( $exts as $ext ) {
						if ( preg_match( '/\.' . $ext . '(\?|#|$)/i', $href ) ) {
							$is_download = true;
							$file_ext    = strtoupper( $ext );
							break;
						}
					}
					if ( $is_download ) {
						$text = trim( $link->textContent );
						if ( ! preg_match( '/\([A-Z]{2,4}\)/', $text ) ) {
							$link->textContent = $text . ' (' . ( $file_ext ?: 'FILE' ) . ')';
							++$fixed_count;
						}
					}
				}
				break;

			case 'external-link':
				$links     = $dom->getElementsByTagName( 'a' );
				$home_url  = function_exists( 'home_url' ) ? home_url() : ( isset( $_SERVER['HTTP_HOST'] ) ? '//' . $_SERVER['HTTP_HOST'] : '' );
				$home_host = parse_url( $home_url, PHP_URL_HOST ) ?: '';
				foreach ( $links as $link ) {
					$href = $link->getAttribute( 'href' );
					if ( empty( $href ) || preg_match( '/^(\/|#|mailto:|tel:|javascript:)/i', $href ) ) {
						continue;
					}
					if ( preg_match( '/^https?:\/\//i', $href ) ) {
						$link_host = parse_url( $href, PHP_URL_HOST ) ?: '';
						if ( $link_host && $link_host !== $home_host ) {
							$text = trim( $link->textContent );
							if ( ! $link->hasAttribute( 'aria-label' ) ) {
								$link->setAttribute( 'aria-label', $text . ' (external link, opens in new window)' );
								++$fixed_count;
							}
						}
					}
				}
				break;

			case 'skip-link':
				$xpath         = new \DOMXPath( $dom );
				$existing_skip = $xpath->query( "//a[contains(@class, 'skip-link') or @id='skip-link']" );
				if ( $existing_skip->length === 0 ) {
					$target_id = 'main-content';
					$main      = $xpath->query( "//main | //*[@role='main'] | //*[@id='main'] | //*[@id='main-content'] | //*[@id='content']" );
					if ( $main->length > 0 ) {
						$node = $main->item( 0 );
						if ( $node->hasAttribute( 'id' ) ) {
							$target_id = $node->getAttribute( 'id' );
						} else {
							$node->setAttribute( 'id', $target_id );
						}
					}
					$skip = $dom->createElement( 'a', 'Skip to main content' );
					$skip->setAttribute( 'href', '#' . $target_id );
					$skip->setAttribute( 'class', 'skip-link screen-reader-text' );
					$skip->setAttribute( 'style', 'position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;' );
					if ( $body->firstChild ) {
						$body->insertBefore( $skip, $body->firstChild );
					} else {
						$body->appendChild( $skip );
					}
					$fixed_count = 1;
				}
				break;
		}

		if ( $fixed_count === 0 ) {
			return null;
		}

		// Extract body inner HTML
		$html = '';
		foreach ( $body->childNodes as $child ) {
			$html .= $dom->saveHTML( $child );
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => trim( $html ),
		);
	}

	/**
	 * Get page content from post ID
	 *
	 * @param int $post_id
	 * @return string|false
	 */
	private function get_page_content( $post_id ) {
		// Clear post cache to ensure we get fresh data
		clean_post_cache( $post_id );

		$post = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		// Return raw post content - do NOT process shortcodes
		// We need to preserve the original content structure for fixing
		// Shortcode processing would expand shortcodes into HTML which can't be saved back properly
		return $post->post_content;
	}
}

