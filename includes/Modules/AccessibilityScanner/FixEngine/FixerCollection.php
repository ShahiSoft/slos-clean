<?php
/**
 * Fixer Collection - Container for all registered fixers
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

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
	private $fixers = [];

	/**
	 * @var array<string, array<FixerInterface>>
	 */
	private $by_category = [];

	/**
	 * Register a fixer
	 *
	 * @param FixerInterface $fixer
	 * @return self
	 */
	public function register( FixerInterface $fixer ): self {
		$id = $fixer->get_id();
		$category = $fixer->get_category();

		$this->fixers[ $id ] = $fixer;

		if ( ! isset( $this->by_category[ $category ] ) ) {
			$this->by_category[ $category ] = [];
		}
		$this->by_category[ $category ][ $id ] = $fixer;

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
		return $this->by_category[ $category ] ?? [];
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
		$result = [];
		foreach ( $this->fixers as $id => $fixer ) {
			$result[] = [
				'id'          => $fixer->get_id(),
				'name'        => $fixer->get_name(),
				'description' => $fixer->get_description(),
				'category'    => $fixer->get_category(),
				'wcag'        => $fixer->get_wcag_criteria(),
			];
		}
		return $result;
	}
}
