<?php
/**
 * Compliance Score Dimension Constants
 *
 * Defines dimension constants and weights for the multi-dimensional
 * compliance readiness score calculation.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Config
 * @version     3.1.0
 * @since       3.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compliance Score Dimensions
 *
 * These constants represent the six dimensions evaluated in the
 * compliance readiness score.
 */
define( 'SLOS_DIMENSION_COOKIES', 'COOKIES' );
define( 'SLOS_DIMENSION_LEGAL_DOCS', 'LEGAL_DOCS' );
define( 'SLOS_DIMENSION_GEO_RULES', 'GEO_RULES' );
define( 'SLOS_DIMENSION_CONSENT_METADATA', 'CONSENT_METADATA' );
define( 'SLOS_DIMENSION_SCANNING_FRESHNESS', 'SCANNING_FRESHNESS' );
define( 'SLOS_DIMENSION_BANNER_CONFIG', 'BANNER_CONFIG' );

/**
 * Dimension Weights
 *
 * Total must equal 1.0 (100%)
 *
 * @return array Dimension weights
 */
function slos_get_dimension_weights() {
	return array(
		SLOS_DIMENSION_COOKIES            => 0.25, // 25%
		SLOS_DIMENSION_LEGAL_DOCS         => 0.25, // 25%
		SLOS_DIMENSION_GEO_RULES          => 0.15, // 15%
		SLOS_DIMENSION_CONSENT_METADATA   => 0.15, // 15%
		SLOS_DIMENSION_SCANNING_FRESHNESS => 0.10, // 10%
		SLOS_DIMENSION_BANNER_CONFIG      => 0.10, // 10%
	);
}

/**
 * Grade Thresholds
 *
 * Maps score ranges to letter grades and labels.
 *
 * @return array Grade mappings
 */
function slos_get_grade_mappings() {
	return array(
		array(
			'min'   => 90,
			'max'   => 100,
			'grade' => 'A',
			'label' => __( 'Excellent', 'shahi-legalflowsuite' ),
			'class' => 'grade-a',
		),
		array(
			'min'   => 80,
			'max'   => 89,
			'grade' => 'B',
			'label' => __( 'Good', 'shahi-legalflowsuite' ),
			'class' => 'grade-b',
		),
		array(
			'min'   => 70,
			'max'   => 79,
			'grade' => 'C',
			'label' => __( 'Fair', 'shahi-legalflowsuite' ),
			'class' => 'grade-c',
		),
		array(
			'min'   => 60,
			'max'   => 69,
			'grade' => 'D',
			'label' => __( 'Needs Work', 'shahi-legalflowsuite' ),
			'class' => 'grade-d',
		),
		array(
			'min'   => 0,
			'max'   => 59,
			'grade' => 'F',
			'label' => __( 'Critical', 'shahi-legalflowsuite' ),
			'class' => 'grade-f',
		),
	);
}

/**
 * Dimension Labels
 *
 * Human-readable labels for each dimension.
 *
 * @return array Dimension labels
 */
function slos_get_dimension_labels() {
	return array(
		SLOS_DIMENSION_COOKIES            => __( 'Cookies', 'shahi-legalflowsuite' ),
		SLOS_DIMENSION_LEGAL_DOCS         => __( 'Legal Docs', 'shahi-legalflowsuite' ),
		SLOS_DIMENSION_GEO_RULES          => __( 'Geo Rules', 'shahi-legalflowsuite' ),
		SLOS_DIMENSION_CONSENT_METADATA   => __( 'Consent Data', 'shahi-legalflowsuite' ),
		SLOS_DIMENSION_SCANNING_FRESHNESS => __( 'Scan Freshness', 'shahi-legalflowsuite' ),
		SLOS_DIMENSION_BANNER_CONFIG      => __( 'Banner Config', 'shahi-legalflowsuite' ),
	);
}

/**
 * Dimension Icons
 *
 * Dashicons class names for each dimension.
 *
 * @return array Dimension icons
 */
function slos_get_dimension_icons() {
	return array(
		SLOS_DIMENSION_COOKIES            => 'dashicons-admin-plugins',
		SLOS_DIMENSION_LEGAL_DOCS         => 'dashicons-media-document',
		SLOS_DIMENSION_GEO_RULES          => 'dashicons-admin-site-alt3',
		SLOS_DIMENSION_CONSENT_METADATA   => 'dashicons-list-view',
		SLOS_DIMENSION_SCANNING_FRESHNESS => 'dashicons-update',
		SLOS_DIMENSION_BANNER_CONFIG      => 'dashicons-format-image',
	);
}
