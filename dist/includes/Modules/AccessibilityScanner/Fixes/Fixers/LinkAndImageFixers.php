<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Alt Text Fixer
 */
class MissingAltTextFixer extends BaseFixer {
	public function get_id() {
		return 'missing-alt-text'; }
	public function get_description() {
		return 'Add alt text to images'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			if ( ! $img->hasAttribute( 'alt' ) || trim( $img->getAttribute( 'alt' ) ) === '' ) {
				$src      = $img->getAttribute( 'src' );
				$alt_text = $this->generate_alt_text( $src );
				$img->setAttribute( 'alt', $alt_text );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Empty Alt Text Fixer
 */
class EmptyAltTextFixer extends BaseFixer {
	public function get_id() {
		return 'empty-alt-text'; }
	public function get_description() {
		return 'Fix empty alt attributes'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			if ( ! $img->hasAttribute( 'alt' ) || trim( $img->getAttribute( 'alt' ) ) === '' ) {
				$src      = $img->getAttribute( 'src' );
				$alt_text = $this->generate_alt_text( $src );
				$img->setAttribute( 'alt', $alt_text );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Redundant Alt Text Fixer
 */
class RedundantAltTextFixer extends BaseFixer {
	public function get_id() {
		return 'redundant-alt-text'; }
	public function get_description() {
		return 'Remove redundant alt text'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			if ( $img->hasAttribute( 'alt' ) ) {
				$alt = trim( $img->getAttribute( 'alt' ) );
				// Remove "image of", "picture of", "photo of" prefix
				$alt = preg_replace( '/^(image|picture|photo) of /i', '', $alt );
				// Remove redundant image/png extensions
				$alt = preg_replace( '/\.(jpg|jpeg|png|gif|webp)$/i', '', $alt );
				$img->setAttribute( 'alt', $alt );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Decorative Image Fixer
 */
class DecorativeImageFixer extends BaseFixer {
	public function get_id() {
		return 'decorative-image'; }
	public function get_description() {
		return 'Mark decorative images with empty alt'; }

	public function fix( $content ) {
		// For decorative images, set alt to empty and add aria-hidden
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		// Check for common decorative patterns
		foreach ( $images as $img ) {
			$class = $img->getAttribute( 'class' );
			$src   = $img->getAttribute( 'src' );

			if ( preg_match( '/(icon|bullet|spacer|divider|decoration|ornament)/i', $class ) ||
				preg_match( '/(icon|bullet|spacer|divider|decoration)/i', $src ) ) {
				$img->setAttribute( 'alt', '' );
				$img->setAttribute( 'aria-hidden', 'true' );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Missing H1 Fixer
 */
class MissingH1Fixer extends BaseFixer {
	public function get_id() {
		return 'missing-h1'; }
	public function get_description() {
		return 'Add H1 to pages without one'; }

	public function fix( $content ) {
		$dom = $this->get_dom( $content );
		$h1s = $dom->getElementsByTagName( 'h1' );

		if ( $h1s->length === 0 ) {
			// Find the first H2 and promote it to H1
			$h2s = $dom->getElementsByTagName( 'h2' );
			if ( $h2s->length > 0 ) {
				$first_h2 = $h2s->item( 0 );
				$h1 = $dom->createElement( 'h1' );
				$h1->textContent = $first_h2->textContent;
				
				// Copy attributes
				foreach ( $first_h2->attributes as $attr ) {
					$h1->setAttribute( $attr->nodeName, $attr->nodeValue );
				}
				
				$first_h2->parentNode->replaceChild( $h1, $first_h2 );
				
				return array(
					'fixed_count' => 1,
					'content'     => $this->dom_to_html( $dom ),
				);
			}
			
			// No H2 found, insert H1 at the beginning of body
			$body = $dom->getElementsByTagName( 'body' )->item( 0 );
			if ( $body ) {
				$h1 = $dom->createElement( 'h1' );
				$h1->textContent = get_the_title() ?: get_bloginfo( 'name' );

				if ( $body->firstChild ) {
					$body->insertBefore( $h1, $body->firstChild );
				} else {
					$body->appendChild( $h1 );
				}

				return array(
					'fixed_count' => 1,
					'content'     => $this->dom_to_html( $dom ),
				);
			}
		}

		return array(
			'fixed_count' => 0,
			'content'     => $content,
		);
	}
}

/**
 * Multiple H1 Fixer
 */
class MultipleH1Fixer extends BaseFixer {
	public function get_id() {
		return 'multiple-h1'; }
	public function get_description() {
		return 'Convert extra H1s to H2s'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$h1s         = $dom->getElementsByTagName( 'h1' );
		$fixed_count = 0;

		// Keep first H1, convert rest to H2
		$h1_array = array();
		foreach ( $h1s as $h1 ) {
			$h1_array[] = $h1;
		}

		for ( $i = 1; $i < count( $h1_array ); $i++ ) {
			$h2              = $dom->createElement( 'h2' );
			$h2->textContent = $h1_array[ $i ]->textContent;
			$h1_array[ $i ]->parentNode->replaceChild( $h2, $h1_array[ $i ] );
			++$fixed_count;
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Empty Heading Fixer
 */
class EmptyHeadingFixer extends BaseFixer {
	public function get_id() {
		return 'empty-heading'; }
	public function get_description() {
		return 'Remove empty heading tags'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$xpath       = new \DOMXPath( $dom );
		$headings    = $xpath->query( '//h1 | //h2 | //h3 | //h4 | //h5 | //h6' );
		$fixed_count = 0;

		// Convert to array to avoid iterator issues
		$headings_array = array();
		foreach ( $headings as $h ) {
			$headings_array[] = $h;
		}

		foreach ( $headings_array as $heading ) {
			$text = trim( $heading->textContent );
			if ( $text === '' ) {
				$heading->parentNode->removeChild( $heading );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Empty Link Fixer
 */
class EmptyLinkFixer extends BaseFixer {
	public function get_id() {
		return 'empty-link'; }
	public function get_description() {
		return 'Add aria-label to empty links'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;

		foreach ( $links as $link ) {
			$text     = trim( $link->textContent );
			$has_aria = $link->hasAttribute( 'aria-label' ) && trim( $link->getAttribute( 'aria-label' ) ) !== '';

			if ( $text === '' && ! $has_aria ) {
				// Check for images with alt
				$images = $link->getElementsByTagName( 'img' );
				if ( $images->length === 0 ) {
					$href  = $link->getAttribute( 'href' );
					$label = $href ? basename( $href ) : 'Link';
					$link->setAttribute( 'aria-label', $label );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Generic Link Text Fixer
 * Improves vague/generic link text like "click here"
 */
class GenericLinkTextFixer extends BaseFixer {
	public function get_id() {
		return 'generic-link-text'; }
	public function get_description() {
		return 'Improve vague link text'; }

	public function fix( $content ) {
		$dom           = $this->get_dom( $content );
		$links         = $dom->getElementsByTagName( 'a' );
		$fixed_count   = 0;
		$generic_words = array( 'click here', 'read more', 'learn more', 'more', 'link', 'here' );

		// Convert to array to avoid modification issues
		$links_array = array();
		foreach ( $links as $link ) {
			$links_array[] = $link;
		}

		foreach ( $links_array as $link ) {
			$text = strtolower( trim( $link->textContent ) );

			if ( in_array( $text, $generic_words ) ) {
				$href = $link->getAttribute( 'href' );
				
				// Try to get better text from URL
				$new_text = $this->generate_link_text( $href, $text );
				$link->textContent = $new_text;
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Generate descriptive link text from URL
	 */
	private function generate_link_text( $href, $original_text ) {
		// Try to get page title if WordPress is loaded
		if ( function_exists( 'url_to_postid' ) && function_exists( 'get_the_title' ) ) {
			$post_id = url_to_postid( $href );
			if ( $post_id ) {
				$title = get_the_title( $post_id );
				if ( ! empty( $title ) ) {
					return "Read more about $title";
				}
			}
		}
		
		// Fallback: extract meaningful text from URL path
		$path = parse_url( $href, PHP_URL_PATH );
		if ( $path ) {
			$slug = basename( $path );
			$slug = preg_replace( '/\.[^.]+$/', '', $slug ); // Remove extension
			$slug = str_replace( array( '-', '_' ), ' ', $slug );
			$slug = ucwords( trim( $slug ) );
			if ( ! empty( $slug ) && strlen( $slug ) > 2 ) {
				return "Learn more about $slug";
			}
		}
		
		return 'Learn more';
	}
}

/**
 * New Window Link Fixer
 */
class NewWindowLinkFixer extends BaseFixer {
	public function get_id() {
		return 'new-window-link'; }
	public function get_description() {
		return 'Add warning to links that open in new window'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;

		foreach ( $links as $link ) {
			if ( $link->hasAttribute( 'target' ) && $link->getAttribute( 'target' ) === '_blank' ) {
				$text = trim( $link->textContent );
				if ( strpos( $text, '(opens in' ) === false ) {
					$link->textContent = $text . ' (opens in new window)';
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Download Link Fixer
 */
class DownloadLinkFixer extends BaseFixer {
	public function get_id() {
		return 'download-link'; }
	public function get_description() {
		return 'Mark download links with file type'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;

		$download_extensions = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'ppt', 'pptx', 'csv', 'txt', 'rtf', 'exe', 'dmg', 'pkg', 'rar', '7z', 'tar', 'gz' );

		// Convert to array to avoid issues with modifying during iteration
		$links_array = array();
		foreach ( $links as $link ) {
			$links_array[] = $link;
		}

		foreach ( $links_array as $link ) {
			$href = $link->getAttribute( 'href' );
			
			// Skip if no href
			if ( empty( $href ) ) {
				continue;
			}
			
			$is_download = false;
			$file_ext    = '';
			$href_lower  = strtolower( $href );

			// Check file extension
			foreach ( $download_extensions as $ext ) {
				if ( preg_match( '/\.' . preg_quote( $ext, '/' ) . '(\?|#|$)/i', $href_lower ) ) {
					$is_download = true;
					$file_ext    = strtoupper( $ext );
					break;
				}
			}
			
			// Also check for download attribute
			if ( ! $is_download && $link->hasAttribute( 'download' ) ) {
				$is_download = true;
				$path = parse_url( $href, PHP_URL_PATH );
				if ( $path ) {
					$path_ext = pathinfo( $path, PATHINFO_EXTENSION );
					$file_ext = $path_ext ? strtoupper( $path_ext ) : 'FILE';
				} else {
					$file_ext = 'FILE';
				}
			}

			if ( $is_download ) {
				$text = trim( $link->textContent );
				// Check if already has file type indicator (PDF), (2.5MB), etc.
				if ( ! preg_match( '/\([A-Z]{2,4}(\s*,?\s*[\d.]+\s*(KB|MB|GB))?\)/i', $text ) ) {
					// Clear existing content and set new
					while ( $link->firstChild ) {
						$link->removeChild( $link->firstChild );
					}
					$link->appendChild( $dom->createTextNode( $text . ' (' . $file_ext . ')' ) );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * External Link Fixer
 */
class ExternalLinkFixer extends BaseFixer {
	public function get_id() {
		return 'external-link'; }
	public function get_description() {
		return 'Mark external links'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;
		
		// Get home URL safely
		$home_url = function_exists( 'home_url' ) ? home_url() : ( isset( $_SERVER['HTTP_HOST'] ) ? '//' . $_SERVER['HTTP_HOST'] : '' );
		$home_host = parse_url( $home_url, PHP_URL_HOST ) ?: '';

		// Convert to array to avoid issues with modifying during iteration
		$links_array = array();
		foreach ( $links as $link ) {
			$links_array[] = $link;
		}

		foreach ( $links_array as $link ) {
			$href = $link->getAttribute( 'href' );
			
			// Skip empty hrefs
			if ( empty( $href ) ) {
				continue;
			}

			// Skip internal links (relative, anchors, mailto, tel)
			if ( preg_match( '/^(\/(?!\/)|#|mailto:|tel:|javascript:)/i', $href ) ) {
				continue;
			}
			
			// Check if it's an external URL (starts with http/https and different host)
			$is_external = false;
			if ( preg_match( '/^https?:\/\//i', $href ) ) {
				$link_host = parse_url( $href, PHP_URL_HOST ) ?: '';
				if ( $link_host && $link_host !== $home_host ) {
					$is_external = true;
				}
			}
			
			if ( $is_external ) {
				$text = trim( $link->textContent );
				// Check if already marked as external
				if ( strpos( $text, '(external' ) === false && 
				     strpos( $text, '(opens' ) === false && 
				     ! $link->hasAttribute( 'aria-label' ) ) {
					$link->setAttribute( 'aria-label', $text . ' (external link, opens in new window)' );
					++$fixed_count;
				}
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Link Destination Fixer
 * Fixes links with invalid or problematic href values
 */
class LinkDestinationFixer extends BaseFixer {
	public function get_id() {
		return 'link-destination'; }
	public function get_description() {
		return 'Ensure links have valid href'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$links       = $dom->getElementsByTagName( 'a' );
		$fixed_count = 0;

		// Convert NodeList to array to avoid issues during modification
		$links_array = array();
		foreach ( $links as $link ) {
			$links_array[] = $link;
		}

		foreach ( $links_array as $link ) {
			$href = $link->hasAttribute( 'href' ) ? trim( $link->getAttribute( 'href' ) ) : '';
			$modified = false;
			
			// Fix empty href
			if ( $href === '' ) {
				$link->setAttribute( 'href', '#' );
				$modified = true;
			}
			
			// Fix javascript: hrefs (any javascript: including void, functions, etc.)
			if ( ! $modified && preg_match( '/^javascript:/i', $href ) ) {
				$link->setAttribute( 'href', '#' );
				// Add role button since it's likely meant to be interactive
				if ( ! $link->hasAttribute( 'role' ) ) {
					$link->setAttribute( 'role', 'button' );
				}
				// Preserve onclick behavior hint
				if ( ! $link->hasAttribute( 'tabindex' ) ) {
					$link->setAttribute( 'tabindex', '0' );
				}
				$modified = true;
			}
			
			// Fix # only hrefs that have no id target
			if ( ! $modified && $href === '#' && ! $link->hasAttribute( 'role' ) ) {
				$link->setAttribute( 'role', 'button' );
				$modified = true;
			}
			
			if ( $modified ) {
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

/**
 * Skip Link Fixer
 * Adds skip navigation link to content
 */
class SkipLinkFixer extends BaseFixer {
	public function get_id() {
		return 'skip-link'; }
	public function get_description() {
		return 'Add skip to main content link'; }

	public function fix( $content ) {
		$dom = $this->get_dom( $content );
		$fixed_count = 0;
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		
		if ( ! $body ) {
			return array( 'fixed_count' => 0, 'content' => $content );
		}
		
		// Check if skip link already exists anywhere in the content
		$xpath = new \DOMXPath( $dom );
		$existing_skip = $xpath->query( "//a[contains(@class, 'skip-link') or contains(@class, 'skip-to') or @id='skip-link']" );
		
		if ( $existing_skip->length > 0 ) {
			return array( 'fixed_count' => 0, 'content' => $content );
		}
		
		// Also check text content for "skip to"
		$skip_text_check = $xpath->query( "//a[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'skip to')]" );
		if ( $skip_text_check->length > 0 ) {
			return array( 'fixed_count' => 0, 'content' => $content );
		}
		
		// Find or create main content target
		$main_content = $xpath->query( "//main | //*[@role='main'] | //*[@id='main'] | //*[@id='main-content'] | //*[@id='content']" );
		
		$target_id = 'main-content';
		if ( $main_content->length > 0 ) {
			$main = $main_content->item( 0 );
			if ( $main->hasAttribute( 'id' ) ) {
				$target_id = $main->getAttribute( 'id' );
			} else {
				$main->setAttribute( 'id', $target_id );
			}
		} else {
			// No main found, create an ID on the first substantial element
			$first_content = $xpath->query( "//div | //article | //section" );
			if ( $first_content->length > 0 ) {
				$first = $first_content->item( 0 );
				if ( ! $first->hasAttribute( 'id' ) ) {
					$first->setAttribute( 'id', $target_id );
				} else {
					$target_id = $first->getAttribute( 'id' );
				}
			}
		}
		
		// Create skip link element
		$skip_link = $dom->createElement( 'a', 'Skip to main content' );
		$skip_link->setAttribute( 'href', '#' . $target_id );
		$skip_link->setAttribute( 'class', 'skip-link screen-reader-text' );
		$skip_link->setAttribute( 'style', 'position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;' );
		
		// Insert at beginning of body
		if ( $body->firstChild ) {
			$body->insertBefore( $skip_link, $body->firstChild );
		} else {
			$body->appendChild( $skip_link );
		}
		$fixed_count = 1;
		
		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

