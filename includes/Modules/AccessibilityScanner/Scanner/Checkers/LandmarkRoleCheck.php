<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for proper landmark role usage
 * WCAG 1.3.1 - Info and Relationships (Level A)
 */
class LandmarkRoleCheck extends AbstractCheck {

	/**
	 * ARIA landmark roles
	 */
	private $landmark_roles = array(
		'banner',
		'complementary',
		'contentinfo',
		'form',
		'main',
		'navigation',
		'region',
		'search',
	);

	/**
	 * HTML5 elements with implicit landmark roles
	 */
	private $implicit_landmarks = array(
		'main'    => 'main',
		'nav'     => 'navigation',
		'aside'   => 'complementary',
		'header'  => 'banner',      // Only when not nested
		'footer'  => 'contentinfo', // Only when not nested
		'form'    => 'form',        // Only with accessible name
		'section' => 'region',      // Only with accessible name
	);

	public function get_id() {
		return 'landmark-role';
	}

	public function get_description() {
		return 'Ensure landmark roles are used correctly, not duplicated without labels, and HTML5 sectioning elements are properly used.';
	}

	public function get_severity() {
		return 'warning';
	}

	public function get_wcag_criteria() {
		return '1.3.1';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Use HTML5 semantic elements (main, nav, header, footer, aside) or ARIA landmark roles. When multiple landmarks of same type exist, use aria-label to distinguish them.';
	}

	public function check( $content ) {
		$issues          = array();
		$dom             = $this->get_dom( $content );
		$xpath           = new \DOMXPath( $dom );
		$landmark_counts = array();

		// 1. Check explicit ARIA landmark roles
		$this->check_explicit_landmarks( $xpath, $landmark_counts, $issues );

		// 2. Check HTML5 implicit landmarks
		$this->check_implicit_landmarks( $xpath, $dom, $landmark_counts, $issues );

		// 3. Check for multiple main landmarks
		$this->check_multiple_main( $landmark_counts, $issues );

		// 4. Check for missing main landmark (full page only)
		$this->check_missing_main( $dom, $landmark_counts, $issues );

		return $issues;
	}

	/**
	 * Check explicit role="landmark" attributes
	 */
	private function check_explicit_landmarks( $xpath, &$counts, &$issues ) {
		foreach ( $this->landmark_roles as $landmark ) {
			$elements            = $xpath->query( "//*[@role='$landmark']" );
			$counts[ $landmark ] = ( $counts[ $landmark ] ?? 0 ) + $elements->length;

			// Check duplicates
			if ( $elements->length > 1 ) {
				foreach ( $elements as $element ) {
					if ( ! $this->has_accessible_name( $element ) ) {
						$issues[] = array(
							'element' => $element->tagName,
							'context' => $this->get_element_html( $element ),
							'message' => "Multiple '$landmark' landmarks found. Use aria-label or aria-labelledby to distinguish them.",
						);
					}
				}
			}
		}
	}

	/**
	 * Check HTML5 elements with implicit landmark semantics
	 */
	private function check_implicit_landmarks( $xpath, $dom, &$counts, &$issues ) {
		foreach ( $this->implicit_landmarks as $tag => $role ) {
			// Find elements without explicit role (to avoid double counting)
			$elements = $xpath->query( "//{$tag}[not(@role)]" );

			foreach ( $elements as $element ) {
				// header/footer only count as landmarks when not nested in sectioning content
				if ( in_array( $tag, array( 'header', 'footer' ), true ) ) {
					if ( $this->is_nested_in_sectioning( $element ) ) {
						continue; // Not a landmark when nested
					}
				}

				// form/section need accessible name to be landmarks
				if ( in_array( $tag, array( 'form', 'section' ), true ) ) {
					if ( ! $this->has_accessible_name( $element ) ) {
						if ( $tag === 'section' ) {
							// Suggest adding accessible name to section
							$issues[] = array(
								'element'  => $tag,
								'context'  => $this->get_element_html( $element ),
								'message'  => '<section> without aria-label/aria-labelledby is not exposed as a landmark region. Consider adding a label or using a heading.',
								'severity' => 'notice',
							);
						}
						continue;
					}
				}

				$counts[ $role ] = ( $counts[ $role ] ?? 0 ) + 1;
			}

			// Check for duplicates of implicit landmarks
			$total = $counts[ $role ] ?? 0;
			if ( $total > 1 ) {
				// Re-check elements for missing labels
				$all_elements = $xpath->query( "//{$tag}[not(@role)]" );
				$unlabeled    = 0;

				foreach ( $all_elements as $element ) {
					if ( in_array( $tag, array( 'header', 'footer' ), true ) && $this->is_nested_in_sectioning( $element ) ) {
						continue;
					}
					if ( ! $this->has_accessible_name( $element ) ) {
						$unlabeled++;
					}
				}

				if ( $unlabeled > 1 ) {
					$issues[] = array(
						'element' => $tag,
						'context' => "Multiple <$tag> elements",
						'message' => "Multiple '$role' landmarks found via <$tag> elements. Use aria-label to distinguish them.",
					);
				}
			}
		}
	}

	/**
	 * Check if element is nested inside sectioning content
	 * (article, section, aside, nav)
	 */
	private function is_nested_in_sectioning( $element ) {
		$sectioning_elements = array( 'article', 'section', 'aside', 'nav' );
		$parent              = $element->parentNode;

		while ( $parent && $parent instanceof \DOMElement ) {
			if ( in_array( strtolower( $parent->tagName ), $sectioning_elements, true ) ) {
				return true;
			}
			$parent = $parent->parentNode;
		}

		return false;
	}

	/**
	 * Check for multiple main landmarks
	 */
	private function check_multiple_main( $counts, &$issues ) {
		$main_count = $counts['main'] ?? 0;

		if ( $main_count > 1 ) {
			$issues[] = array(
				'element' => 'multiple',
				'context' => "Found $main_count main landmarks",
				'message' => 'A document should not have more than one visible main landmark. Use aria-hidden or hide duplicates visually.',
			);
		}
	}

	/**
	 * Check if full page is missing main landmark
	 */
	private function check_missing_main( $dom, $counts, &$issues ) {
		// Only check if this appears to be a full page
		$html = $dom->getElementsByTagName( 'html' );
		$body = $dom->getElementsByTagName( 'body' );

		if ( $html->length > 0 || $body->length > 0 ) {
			$main_count = $counts['main'] ?? 0;

			if ( $main_count === 0 ) {
				$issues[] = array(
					'element'  => 'page',
					'context'  => 'Document structure',
					'message'  => 'No main landmark found. Use <main> or role="main" to identify the primary content area.',
					'severity' => 'notice',
				);
			}
		}
	}
}

