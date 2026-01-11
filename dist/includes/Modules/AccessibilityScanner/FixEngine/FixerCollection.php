<?php
/**
 * Fixer Collection - Container for all registered fixers
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

use ShahiLegalFlowSuite\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\FixEngine\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FixerCollection
 *
 * Manages all registered fixers.
 * Single Responsibility: Fixer registration and retrieval.
 * Open/Closed: Add fixers without modifying collection.
 */
final class FixerCollection implements \Countable, \IteratorAggregate {

	/**
	 * @var array<string, FixerInterface>
	 */
	private $fixers = array();

	/**
	 * @var array<string, array<FixerInterface>>
	 */
	private $by_category = array();

	/**
	 * Register a fixer
	 *
	 * @param FixerInterface $fixer
	 * @return self
	 * @throws \InvalidArgumentException If ID not canonical
	 */
	public function register( FixerInterface $fixer ): self {
		$id           = $fixer->get_id();
		$canonical_id = method_exists( $fixer, 'get_canonical_id' )
			? $fixer->get_canonical_id()
			: CanonicalIds::canonicalize( $id );

		// Validate against canonical IDs
		if ( ! $canonical_id ) {
			Logger::error(
				'Attempted to register fixer with non-canonical ID',
				array(
					'fixer_id'    => $id,
					'fixer_class' => get_class( $fixer ),
				)
			);

			throw new \InvalidArgumentException(
				sprintf( 'Cannot register fixer with non-canonical ID: %s', $id )
			);
		}

		$category = $fixer->get_category();

		$this->fixers[ $canonical_id ] = $fixer;

		if ( ! isset( $this->by_category[ $category ] ) ) {
			$this->by_category[ $category ] = array();
		}
		$this->by_category[ $category ][ $canonical_id ] = $fixer;

		Logger::debug(
			'Fixer registered',
			array(
				'fixer_id' => $canonical_id,
				'category' => $category,
			)
		);

		return $this;
	}

	/**
	 * Check if fixer exists
	 *
	 * @param string $id
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->fixers[ $id ] );
	}

	/**
	 * Get fixer by ID
	 *
	 * @param string $id
	 * @return FixerInterface|null
	 */
	public function get( string $id ): ?FixerInterface {
		return $this->fixers[ $id ] ?? null;
	}

	/**
	 * Get all fixers
	 *
	 * @return array<string, FixerInterface>
	 */
	public function all(): array {
		return $this->fixers;
	}

	/**
	 * Get all fixer IDs
	 *
	 * @return array<string>
	 */
	public function ids(): array {
		return array_keys( $this->fixers );
	}

	/**
	 * Get fixers by category
	 *
	 * @param string $category
	 * @return array<string, FixerInterface>
	 */
	public function by_category( string $category ): array {
		return $this->by_category[ $category ] ?? array();
	}

	/**
	 * Get all categories
	 *
	 * @return array<string>
	 */
	public function categories(): array {
		return array_keys( $this->by_category );
	}

	/**
	 * Get count of fixers
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->fixers );
	}

	/**
	 * Auto-discover and register all fixers from Fixers directory
	 * Phase 2.2: Dynamic Discovery
	 *
	 * @return int Number of fixers discovered
	 */
	public function auto_discover(): int {
		$fixers_dir = __DIR__ . '/Fixers';
		$count      = 0;

		if ( ! is_dir( $fixers_dir ) ) {
			Logger::error( 'Fixers directory not found', array( 'path' => $fixers_dir ) );
			return 0;
		}

		$files = glob( $fixers_dir . '/*.php' );

		foreach ( $files as $file ) {
			$class_name = basename( $file, '.php' );
			$fqcn       = __NAMESPACE__ . '\\Fixers\\' . $class_name;

			// Skip if class doesn't exist
			if ( ! class_exists( $fqcn ) ) {
				continue;
			}

			// Check if extends AbstractFixer
			if ( ! is_subclass_of( $fqcn, AbstractFixer::class ) ) {
				Logger::warning(
					'Class does not extend AbstractFixer',
					array( 'class' => $class_name )
				);
				continue;
			}

			try {
				$fixer = new $fqcn();
				$this->register( $fixer );
				++$count;
			} catch ( \Exception $e ) {
				Logger::error(
					'Failed to instantiate fixer',
					array(
						'class' => $class_name,
						'error' => $e->getMessage(),
					)
				);
			}
		}

		Logger::info( 'Auto-discovered fixers', array( 'count' => $count ) );

		return $count;
	}

	/**
	 * Get iterator for foreach
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): \Traversable {
		return new \ArrayIterator( $this->fixers );
	}

	/**
	 * Convert to array for JSON (frontend)
	 *
	 * @return array
	 */
	public function to_array(): array {
		$result = array();
		foreach ( $this->fixers as $canonical_id => $fixer ) {
			$export_id = $canonical_id;
			if ( method_exists( $fixer, 'get_canonical_id' ) ) {
				$export_id = $fixer->get_canonical_id();
			} else {
				$normalized = CanonicalIds::canonicalize( $fixer->get_id() );
				if ( $normalized ) {
					$export_id = $normalized;
				}
			}
			$result[] = array(
				'id'          => $export_id,
				'name'        => $fixer->get_name(),
				'description' => $fixer->get_description(),
				'category'    => $fixer->get_category(),
				'wcag'        => $fixer->get_wcag_criteria(),
			);
		}
		return $result;
	}
}
