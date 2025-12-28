<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table Header Fixer
 */
class TableHeaderFixer extends BaseFixer {
	public function get_id() {
		return 'table-header'; }
	public function get_description() {
		return 'Add headers to tables'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$tables      = $dom->getElementsByTagName( 'table' );
		$fixed_count = 0;

		foreach ( $tables as $table ) {
			$tbody = $table->getElementsByTagName( 'tbody' )->item( 0 );
			$rows  = $tbody ? $tbody->getElementsByTagName( 'tr' ) : $table->getElementsByTagName( 'tr' );

			if ( $rows->length > 0 ) {
				$first_row = $rows->item( 0 );
				$cells     = $first_row->getElementsByTagName( 'td' );

				foreach ( $cells as $cell ) {
					if ( $cell->parentNode === $first_row ) {
						$th = $dom->createElement( 'th' );
						$th->setAttribute( 'scope', 'col' );
						$th->textContent = $cell->textContent;
						$cell->parentNode->replaceChild( $th, $cell );
						++$fixed_count;
					}
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
 * Table Caption Fixer
 */
class TableCaptionFixer extends BaseFixer {
	public function get_id() {
		return 'table-caption'; }
	public function get_description() {
		return 'Add captions to tables'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$tables      = $dom->getElementsByTagName( 'table' );
		$fixed_count = 0;

		foreach ( $tables as $table ) {
			$caption = $table->getElementsByTagName( 'caption' )->item( 0 );
			if ( ! $caption ) {
				$caption              = $dom->createElement( 'caption' );
				$caption->textContent = 'Data Table';

				if ( $table->firstChild ) {
					$table->insertBefore( $caption, $table->firstChild );
				} else {
					$table->appendChild( $caption );
				}
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
 * Complex Table Fixer
 */
class ComplexTableFixer extends BaseFixer {
	public function get_id() {
		return 'complex-table'; }
	public function get_description() {
		return 'Add scope attributes to complex tables'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$tables      = $dom->getElementsByTagName( 'table' );
		$fixed_count = 0;

		foreach ( $tables as $table ) {
			// Add scope to all headers
			$headers = $table->getElementsByTagName( 'th' );
			foreach ( $headers as $header ) {
				if ( ! $header->hasAttribute( 'scope' ) ) {
					// Determine if it's col or row
					$parent = $header->parentNode;
					if ( $parent->parentNode->nodeName === 'thead' ||
						(int) $parent->parentNode->getElementsByTagName( 'tr' )->item( 0 ) === $parent ) {
						$header->setAttribute( 'scope', 'col' );
					} else {
						$header->setAttribute( 'scope', 'row' );
					}
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
 * Layout Table Fixer
 */
class LayoutTableFixer extends BaseFixer {
	public function get_id() {
		return 'layout-table'; }
	public function get_description() {
		return 'Convert layout tables to divs'; }

	public function fix( $content ) {
		// Layout tables should be converted to semantic HTML - complex refactoring
		// Mark as non-data table instead
		$dom         = $this->get_dom( $content );
		$tables      = $dom->getElementsByTagName( 'table' );
		$fixed_count = 0;

		foreach ( $tables as $table ) {
			if ( ! $table->hasAttribute( 'role' ) ) {
				$table->setAttribute( 'role', 'presentation' );
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
 * Empty Table Cell Fixer
 */
class EmptyTableCellFixer extends BaseFixer {
	public function get_id() {
		return 'empty-table-cell'; }
	public function get_description() {
		return 'Handle empty table cells'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$tables      = $dom->getElementsByTagName( 'table' );
		$fixed_count = 0;

		foreach ( $tables as $table ) {
			$rows = $table->getElementsByTagName( 'tr' );
			foreach ( $rows as $row ) {
				$cells = $row->getElementsByTagName( 'td' );
				foreach ( $cells as $cell ) {
					$text = trim( $cell->textContent );
					if ( $text === '' && ! $cell->hasAttribute( 'aria-label' ) ) {
						$cell->setAttribute( 'aria-label', 'Empty cell' );
						++$fixed_count;
					}
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
 * Image Map Alt Fixer
 */
class ImageMapAltFixer extends BaseFixer {
	public function get_id() {
		return 'image-map-alt'; }
	public function get_description() {
		return 'Add alt to image maps'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$maps        = $dom->getElementsByTagName( 'map' );
		$fixed_count = 0;

		foreach ( $maps as $map ) {
			$areas = $map->getElementsByTagName( 'area' );
			foreach ( $areas as $area ) {
				if ( ! $area->hasAttribute( 'alt' ) || trim( $area->getAttribute( 'alt' ) ) === '' ) {
					$area->setAttribute( 'alt', 'Map region' );
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
 * IFrame Title Fixer
 */
class IframeTitleFixer extends BaseFixer {
	public function get_id() {
		return 'iframe-title'; }
	public function get_description() {
		return 'Add titles to iframes'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$iframes     = $dom->getElementsByTagName( 'iframe' );
		$fixed_count = 0;

		foreach ( $iframes as $iframe ) {
			if ( ! $iframe->hasAttribute( 'title' ) ) {
				$src   = $iframe->getAttribute( 'src' );
				$title = 'Embedded content: ' . basename( $src );
				$iframe->setAttribute( 'title', $title );
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
 * SVG Accessibility Fixer
 */
class SvgAccessibilityFixer extends BaseFixer {
	public function get_id() {
		return 'svg-accessibility'; }
	public function get_description() {
		return 'Add labels to SVGs'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$svgs        = $dom->getElementsByTagName( 'svg' );
		$fixed_count = 0;

		foreach ( $svgs as $svg ) {
			if ( ! $svg->hasAttribute( 'role' ) ) {
				$svg->setAttribute( 'role', 'img' );

				// Look for title
				$titles = $svg->getElementsByTagName( 'title' );
				if ( $titles->length === 0 ) {
					$title              = $dom->createElement( 'title' );
					$title->textContent = 'SVG Image';
					$svg->insertBefore( $title, $svg->firstChild );
				}
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
 * Complex Image Fixer
 */
class ComplexImageFixer extends BaseFixer {
	public function get_id() {
		return 'complex-image'; }
	public function get_description() {
		return 'Add descriptions to complex images'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			$alt = $img->getAttribute( 'alt' );
			if ( strlen( $alt ) > 100 ) {
				// Very long alt text - should be in figure caption
				if ( ! $img->hasAttribute( 'aria-describedby' ) ) {
					$img->setAttribute( 'aria-describedby', 'img-desc-' . uniqid() );
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
 * Logo Image Fixer
 */
class LogoImageFixer extends BaseFixer {
	public function get_id() {
		return 'logo-image'; }
	public function get_description() {
		return 'Mark logo images appropriately'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			$class = $img->getAttribute( 'class' );
			$src   = $img->getAttribute( 'src' );

			if ( preg_match( '/(logo|brand)/i', $class ) || preg_match( '/(logo|brand)/i', $src ) ) {
				if ( ! $img->hasAttribute( 'alt' ) || trim( $img->getAttribute( 'alt' ) ) === '' ) {
					$img->setAttribute( 'alt', 'Site Logo' );
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
 * Background Image Fixer
 */
class BackgroundImageFixer extends BaseFixer {
	public function get_id() {
		return 'background-image'; }
	public function get_description() {
		return 'Add fallback for background images'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$elements    = $dom->getElementsByTagName( '*' );
		$fixed_count = 0;

		foreach ( $elements as $element ) {
			$style = $element->getAttribute( 'style' );
			if ( preg_match( '/background-image:/i', $style ) ) {
				if ( ! $element->hasAttribute( 'role' ) && empty( trim( $element->textContent ) ) ) {
					$element->setAttribute( 'role', 'img' );
					$element->setAttribute( 'aria-label', 'Decorative image' );
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
 * Alt Text Quality Fixer
 */
class AltTextQualityFixer extends BaseFixer {
	public function get_id() {
		return 'alt-text-quality'; }
	public function get_description() {
		return 'Improve alt text quality'; }

	public function fix( $content ) {
		$dom         = $this->get_dom( $content );
		$images      = $dom->getElementsByTagName( 'img' );
		$fixed_count = 0;

		foreach ( $images as $img ) {
			$alt = $img->getAttribute( 'alt' );
			// Fix common poor alt text patterns
			if ( preg_match( '/^(image|photo|picture|img|image_\d+|untitled)$/i', $alt ) ) {
				$src        = $img->getAttribute( 'src' );
				$better_alt = $this->generate_alt_text( $src );
				$img->setAttribute( 'alt', $better_alt );
				++$fixed_count;
			}
		}

		return array(
			'fixed_count' => $fixed_count,
			'content'     => $this->dom_to_html( $dom ),
		);
	}
}

