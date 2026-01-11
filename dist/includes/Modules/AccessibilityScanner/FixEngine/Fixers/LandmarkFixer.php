<?php
/**
 * Landmark Fixer
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
 * Class LandmarkFixer
 *
 * Adds appropriate ARIA landmark roles to page regions.
 */
final class LandmarkFixer extends AbstractFixer {

	public function get_id(): string {
		return 'landmark-role';
	}

	public function get_name(): string {
		return __( 'Landmark Roles', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds ARIA landmark roles to identify page regions for screen reader navigation.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.1', '2.4.1' );
	}

	public function get_category(): string {
		return 'semantic';
	}

	public function can_fix( string $content ): bool {
		return strpos( $content, '<div' ) !== false;
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details       = array();

		// Map of class patterns to landmark roles
		$landmark_patterns = array(
			'navigation'    => array(
				'classes' => array( 'nav', 'navigation', 'menu', 'navbar', 'site-navigation' ),
				'role'    => 'navigation',
			),
			'main'          => array(
				'classes' => array( 'main', 'content', 'main-content', 'site-content', 'page-content' ),
				'role'    => 'main',
				'ids'     => array( 'main', 'content', 'primary', 'main-content' ),
			),
			'banner'        => array(
				'classes' => array( 'header', 'site-header', 'page-header', 'masthead' ),
				'role'    => 'banner',
				'ids'     => array( 'header', 'masthead' ),
			),
			'contentinfo'   => array(
				'classes' => array( 'footer', 'site-footer', 'page-footer' ),
				'role'    => 'contentinfo',
				'ids'     => array( 'footer', 'colophon' ),
			),
			'complementary' => array(
				'classes' => array( 'sidebar', 'aside', 'widget-area', 'secondary' ),
				'role'    => 'complementary',
				'ids'     => array( 'sidebar', 'secondary' ),
			),
			'search'        => array(
				'classes' => array( 'search', 'search-form', 'site-search' ),
				'role'    => 'search',
				'ids'     => array( 'search' ),
			),
		);

		// Track which roles have been assigned
		$assigned_roles = array();

		foreach ( $landmark_patterns as $landmark => $config ) {
			// Build XPath for matching
			$xpath_parts = array();

			foreach ( $config['classes'] as $class ) {
				$xpath_parts[] = "contains(@class, '{$class}')";
			}

			if ( isset( $config['ids'] ) ) {
				foreach ( $config['ids'] as $id ) {
					$xpath_parts[] = "@id='{$id}'";
				}
			}

			$xpath    = '//div[not(@role) and (' . implode( ' or ', $xpath_parts ) . ')]';
			$elements = $this->query( $xpath );

			foreach ( $elements as $element ) {
				// Skip if already has a role
				if ( $element->hasAttribute( 'role' ) ) {
					continue;
				}

				// Only allow one main and one banner
				if ( in_array( $config['role'], array( 'main', 'banner', 'contentinfo' ) ) ) {
					if ( isset( $assigned_roles[ $config['role'] ] ) ) {
						continue;
					}
					$assigned_roles[ $config['role'] ] = true;
				}

				$element->setAttribute( 'role', $config['role'] );
				++$fixes_applied;

				$details[] = array(
					'element' => 'div',
					'class'   => $element->getAttribute( 'class' ),
					'id'      => $element->getAttribute( 'id' ),
					'role'    => $config['role'],
				);
			}
		}

		// Also check for semantic elements missing roles (for older browsers)
		$semantic_role_map = array(
			'header' => 'banner',
			'footer' => 'contentinfo',
			'nav'    => 'navigation',
			'aside'  => 'complementary',
			'main'   => 'main',
		);

		foreach ( $semantic_role_map as $tag => $role ) {
			$elements = $this->query( "//{$tag}[not(@role)]" );

			foreach ( $elements as $element ) {
				// For header/footer, only add role if they're direct children of body (page-level)
				if ( in_array( $tag, array( 'header', 'footer' ) ) ) {
					$parent = $element->parentNode;
					if ( $parent && $parent->nodeName !== 'body' ) {
						continue; // Skip nested headers/footers
					}

					// Only one banner/contentinfo
					if ( isset( $assigned_roles[ $role ] ) ) {
						continue;
					}
					$assigned_roles[ $role ] = true;
				}

				$element->setAttribute( 'role', $role );
				++$fixes_applied;

				$details[] = array(
					'element' => $tag,
					'class'   => $element->getAttribute( 'class' ),
					'role'    => $role,
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No landmark role fixes needed', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}
}
