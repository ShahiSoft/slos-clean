<?php
/**
 * ARIA Label Fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AriaLabelFixer
 * 
 * Fixes invalid or missing ARIA labels.
 */
final class AriaLabelFixer extends AbstractFixer {

	public function get_id(): string {
		return 'aria_label';
	}

	public function get_name(): string {
		return __( 'ARIA Labels', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds missing ARIA labels to interactive elements and fixes invalid aria-labelledby references.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '4.1.2' ];
	}

	public function get_category(): string {
		return 'aria';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, 'aria-' ) !== false || 
			   strpos( $content, 'role=' ) !== false ||
			   strpos( $content, '<nav' ) !== false ||
			   strpos( $content, '<main' ) !== false ||
			   strpos( $content, '<aside' ) !== false;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details = [];

		// Fix empty aria-label attributes
		$empty_labels = $this->query( '//*[@aria-label=""]' );
		foreach ( $empty_labels as $element ) {
			$element->removeAttribute( 'aria-label' );
			$fixes_applied++;
			$details[] = [
				'element' => $element->nodeName,
				'action'  => 'removed_empty_aria_label',
			];
		}

		// Fix invalid aria-labelledby references
		$labelledby_elements = $this->query( '//*[@aria-labelledby]' );
		foreach ( $labelledby_elements as $element ) {
			$ids = explode( ' ', $element->getAttribute( 'aria-labelledby' ) );
			$valid_ids = [];
			
			foreach ( $ids as $id ) {
				$id = trim( $id );
				if ( empty( $id ) ) {
					continue;
				}
				
				// Check if referenced element exists
				$referenced = $this->query( "//*[@id='{$id}']" );
				if ( count( $referenced ) > 0 ) {
					$valid_ids[] = $id;
				}
			}
			
			if ( count( $valid_ids ) !== count( $ids ) ) {
				if ( empty( $valid_ids ) ) {
					$element->removeAttribute( 'aria-labelledby' );
					$fixes_applied++;
					$details[] = [
						'element' => $element->nodeName,
						'action'  => 'removed_invalid_labelledby',
					];
				} else {
					$element->setAttribute( 'aria-labelledby', implode( ' ', $valid_ids ) );
					$fixes_applied++;
					$details[] = [
						'element' => $element->nodeName,
						'action'  => 'fixed_labelledby_references',
					];
				}
			}
		}

		// Add labels to landmark regions without them
		$landmarks = [
			'nav'     => __( 'Navigation', 'shahi-legalflowsuite' ),
			'main'    => __( 'Main content', 'shahi-legalflowsuite' ),
			'aside'   => __( 'Sidebar', 'shahi-legalflowsuite' ),
			'header'  => __( 'Header', 'shahi-legalflowsuite' ),
			'footer'  => __( 'Footer', 'shahi-legalflowsuite' ),
			'section' => null, // Only if has role
			'form'    => __( 'Form', 'shahi-legalflowsuite' ),
		];

		foreach ( $landmarks as $tag => $default_label ) {
			$elements = $this->query( "//{$tag}" );
			
			foreach ( $elements as $element ) {
				// Skip if already has label
				if ( $element->hasAttribute( 'aria-label' ) || $element->hasAttribute( 'aria-labelledby' ) ) {
					continue;
				}

				// For section, only label if it has a role
				if ( $tag === 'section' && ! $element->hasAttribute( 'role' ) ) {
					continue;
				}

				// Try to find heading inside for label
				$heading = $this->find_heading_in_element( $element );
				
				if ( $heading ) {
					// Use heading as label via aria-labelledby
					$heading_id = $heading->getAttribute( 'id' );
					
					if ( empty( $heading_id ) ) {
						$heading_id = 'slos-heading-' . wp_generate_uuid4();
						$heading->setAttribute( 'id', $heading_id );
					}
					
					$element->setAttribute( 'aria-labelledby', $heading_id );
					$fixes_applied++;
					$details[] = [
						'element' => $tag,
						'action'  => 'added_labelledby',
						'heading' => $heading->textContent,
					];
				} elseif ( $default_label ) {
					// Use default label
					$element->setAttribute( 'aria-label', $default_label );
					$fixes_applied++;
					$details[] = [
						'element' => $tag,
						'action'  => 'added_default_label',
						'label'   => $default_label,
					];
				}
			}
		}

		// Handle multiple nav elements - make labels unique
		$navs = $this->query( '//nav[@aria-label]' );
		if ( count( $navs ) > 1 ) {
			$nav_labels = [];
			
			foreach ( $navs as $nav ) {
				$label = $nav->getAttribute( 'aria-label' );
				
				if ( isset( $nav_labels[ $label ] ) ) {
					// Make unique
					$nav_labels[ $label ]++;
					$new_label = $label . ' ' . $nav_labels[ $label ];
					$nav->setAttribute( 'aria-label', $new_label );
					$fixes_applied++;
					$details[] = [
						'element'   => 'nav',
						'action'    => 'made_label_unique',
						'old_label' => $label,
						'new_label' => $new_label,
					];
				} else {
					$nav_labels[ $label ] = 1;
				}
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No ARIA label issues found', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Find heading inside element
	 *
	 * @param \DOMElement $element
	 * @return \DOMElement|null
	 */
	private function find_heading_in_element( \DOMElement $element ): ?\DOMElement {
		for ( $level = 1; $level <= 6; $level++ ) {
			$headings = $element->getElementsByTagName( "h{$level}" );
			if ( $headings->length > 0 ) {
				$heading = $headings->item( 0 );
				if ( ! empty( trim( $heading->textContent ) ) ) {
					return $heading;
				}
			}
		}
		return null;
	}
}
