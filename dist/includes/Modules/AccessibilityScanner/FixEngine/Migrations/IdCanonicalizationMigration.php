<?php
/**
 * ID Canonicalization Migration Script
 *
 * Updates all stored fixer/checker IDs to canonical format.
 *
 * @package ShahiLegalFlowSuite
 * @subpackage FixEngine
 * @since 3.1.2
 */

namespace ShahiLegalFlowSuite\FixEngine\Migrations;

use ShahiLegalFlowSuite\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\FixEngine\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ID Migration Class
 */
class IdCanonicalizationMigration {

	/**
	 * Legacy to canonical ID map
	 *
	 * Maps old IDs (aliases) to canonical IDs
	 */
	private static $legacy_map = array(
		// Legacy key => Canonical ID..
		'generic-link'               => 'generic-link-text',
		'missing-label'              => 'missing-form-label',
		'redundant-alt'              => 'redundant-alt-text',
		'link-dest'                  => 'link-destination',
		'new-window'                 => 'new-window-link',
		'image-map'                  => 'image-map-alt',
		'alt-quality'                => 'alt-text-quality',
		'svg-access'                 => 'svg-accessibility',
		'bg-image'                   => 'background-image',
		'heading-unique'             => 'heading-uniqueness',
		'contrast'                   => 'text-color-contrast',
		'autocomplete'               => 'autocomplete-attribute',
		'required-attr'              => 'required-attribute',
		'skipped-heading'            => 'skipped-heading-level',
		'modal-access'               => 'modal-accessibility',
		'widget-keyboard'            => 'interactive-element',
		'aria-attr'                  => 'aria-attribute',
		'invalid-aria'               => 'invalid-aria-combination',
		'media-alt'                  => 'media-alternative',
		'video-access'               => 'video-accessibility',
		'audio-access'               => 'audio-accessibility',
		'audio_accessibility'        => 'audio-accessibility',
		'empty-cell'                 => 'empty-table-cell',
		'viewport'                   => 'viewport-check',
		'improper-viewport'          => 'viewport-check',
		'invalid-tabindex'           => 'positive-tabindex',
		'missing-focus-indicator'    => 'focus-indicator',
		'missing-iframe-title'       => 'iframe-title',
		'missing-image-map-alt'      => 'image-map-alt',
		'missing-svg-title'          => 'svg-accessibility',
		'link-opens-new-window'      => 'new-window-link',
		'missing-required-attribute' => 'required-attribute',
		'missing-fieldset-legend'    => 'fieldset-legend',
		'missing-error-description'  => 'error-message',
		'missing-skip-link'          => 'skip-link',
		'missing-lang-attribute'     => 'lang-attribute',
		'missing-page-title'         => 'page-title',
		'missing-button-type'        => 'button-type',
		'missing-figure-caption'     => 'figure-caption',
		'improper-list-structure'    => 'list-structure',
		'missing-table-scope'        => 'table-scope',
	);

	/**
	 * Run migration
	 *
	 * @param bool $dry_run Don't make changes, just report
	 * @return array Migration results
	 */
	public static function run( bool $dry_run = false ): array {
		global $wpdb;

		$results = array(
			'dry_run'          => $dry_run,
			'timestamp'        => current_time( 'mysql' ),
			'postmeta_updated' => 0,
			'options_updated'  => 0,
			'history_updated'  => 0,
			'changes'          => array(),
		);

		Logger::info( 'Starting ID canonicalization migration', array( 'dry_run' => $dry_run ) );

		// 1. Migrate postmeta..
		$results['postmeta_updated'] = self::migrate_postmeta( $dry_run, $results['changes'] );

		// 2. Migrate options..
		$results['options_updated'] = self::migrate_options( $dry_run, $results['changes'] );

		// 3. Migrate fix_history table..
		$results['history_updated'] = self::migrate_fix_history( $dry_run, $results['changes'] );

		Logger::info( 'ID canonicalization migration completed', $results );

		return $results;
	}

	/**
	 * Migrate postmeta values
	 *
	 * @param bool  $dry_run
	 * @param array &$changes
	 * @return int Number of rows updated
	 */
	private static function migrate_postmeta( bool $dry_run, array &$changes ): int {
		global $wpdb;

		$count     = 0;
		$meta_keys = array(
			'_slos_accessibility_scan_results',
			'_slos_last_fix_session',
		);

		foreach ( $meta_keys as $meta_key ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT meta_id, post_id, meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = %s',
					$meta_key
				)
			);

			foreach ( $results as $row ) {
				$old_value = maybe_unserialize( $row->meta_value );
				$new_value = self::map_ids_recursive( $old_value, $changes );

				if ( $old_value !== $new_value ) {
					if ( ! $dry_run ) {
						update_post_meta( $row->post_id, $meta_key, $new_value );
					}
					++$count;
				}
			}
		}

		return $count;
	}

	/**
	 * Migrate options
	 *
	 * @param bool  $dry_run
	 * @param array &$changes
	 * @return int Number of options updated
	 */
	private static function migrate_options( bool $dry_run, array &$changes ): int {
		$count       = 0;
		$option_keys = array(
			'slos_last_scan_results',
			'slos_issues_by_type',
			'slos_active_fixes',
		);

		foreach ( $option_keys as $option_key ) {
			$old_value = get_option( $option_key );
			if ( $old_value === false ) {
				continue;
			}

			$new_value = self::map_ids_recursive( $old_value, $changes );

			if ( $old_value !== $new_value ) {
				if ( ! $dry_run ) {
					update_option( $option_key, $new_value, false );
				}
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Migrate fix_history table
	 *
	 * @param bool  $dry_run
	 * @param array &$changes
	 * @return int Number of rows updated
	 */
	private static function migrate_fix_history( bool $dry_run, array &$changes ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'slos_fix_history';

		// Check if table exists..
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return 0;
		}

		$count   = 0;
		$results = $wpdb->get_results( sprintf( 'SELECT id, fixer_id FROM %s', $wpdb->_escape( $table ) ) );

		foreach ( $results as $row ) {
			if ( isset( self::$legacy_map[ $row->fixer_id ] ) ) {
				$new_id    = self::$legacy_map[ $row->fixer_id ];
				$changes[] = array(
					'type' => 'fix_history',
					'id'   => $row->id,
					'old'  => $row->fixer_id,
					'new'  => $new_id,
				);

				if ( ! $dry_run ) {
					$wpdb->update(
						$table,
						array( 'fixer_id' => $new_id ),
						array( 'id' => $row->id ),
						array( '%s' ),
						array( '%d' )
					);
				}
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Recursively map IDs in data structures
	 *
	 * @param mixed $data
	 * @param array &$changes
	 * @return mixed
	 */
	private static function map_ids_recursive( $data, array &$changes ) {
		if ( is_array( $data ) ) {
			$new_data = array();
			foreach ( $data as $key => $value ) {
				// Check if key is a legacy ID..
				$new_key = $key;
				if ( isset( self::$legacy_map[ $key ] ) ) {
					$new_key   = self::$legacy_map[ $key ];
					$changes[] = array(
						'type' => 'array_key',
						'old'  => $key,
						'new'  => $new_key,
					);
				}

				// Check if value contains IDs..
				if ( is_string( $value ) && isset( self::$legacy_map[ $value ] ) ) {
					$new_value            = self::$legacy_map[ $value ];
					$changes[]            = array(
						'type' => 'array_value',
						'old'  => $value,
						'new'  => $new_value,
					);
					$new_data[ $new_key ] = $new_value;
				} elseif ( is_array( $value ) ) {
					$new_data[ $new_key ] = self::map_ids_recursive( $value, $changes );
				} else {
					$new_data[ $new_key ] = $value;
				}
			}
			return $new_data;
		}

		return $data;
	}

	/**
	 * Rollback migration
	 *
	 * @return array Rollback results
	 */
	public static function rollback(): array {
		// Create reverse map..
		$reverse_map = array_flip( self::$legacy_map );

		// Temporarily swap map..
		$original_map     = self::$legacy_map;
		self::$legacy_map = $reverse_map;

		// Run migration in reverse..
		$results = self::run( false );

		// Restore map..
		self::$legacy_map = $original_map;

		Logger::warning( 'ID canonicalization migration rolled back', $results );

		return $results;
	}

	/**
	 * Get migration report
	 *
	 * @return string
	 */
	public static function get_report(): string {
		$results = self::run( true );

		$report  = "ID Canonicalization Migration Report\n";
		$report .= "====================================\n\n";
		$report .= "Mode: DRY RUN\n";
		$report .= "Timestamp: {$results['timestamp']}\n\n";
		$report .= "Changes to be made:\n";
		$report .= "- Postmeta: {$results['postmeta_updated']} rows\n";
		$report .= "- Options: {$results['options_updated']} rows\n";
		$report .= "- Fix History: {$results['history_updated']} rows\n\n";

		if ( ! empty( $results['changes'] ) ) {
			$report .= "Sample Changes:\n";
			$sample  = array_slice( $results['changes'], 0, 10 );
			foreach ( $sample as $change ) {
				$report .= sprintf(
					"  [%s] %s -> %s\n",
					$change['type'],
					$change['old'],
					$change['new']
				);
			}

			if ( count( $results['changes'] ) > 10 ) {
				$report .= sprintf( "  ... and %d more\n", count( $results['changes'] ) - 10 );
			}
		}

		return $report;
	}
}
