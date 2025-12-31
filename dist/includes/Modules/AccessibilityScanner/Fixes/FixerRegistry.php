<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers\{
	MissingAltTextFixer,
	EmptyAltTextFixer,
	RedundantAltTextFixer,
	DecorativeImageFixer,
	MissingH1Fixer,
	MultipleH1Fixer,
	EmptyHeadingFixer,
	EmptyLinkFixer,
	GenericLinkTextFixer,
	NewWindowLinkFixer,
	DownloadLinkFixer,
	ExternalLinkFixer,
	LinkDestinationFixer,
	SkipLinkFixer,
	MissingFormLabelFixer,
	FieldsetLegendFixer,
	RequiredAttributeFixer,
	ErrorMessageFixer,
	AutocompleteFixer,
	InputTypeFixer,
	PlaceholderLabelFixer,
	CustomControlFixer,
	ButtonLabelFixer,
	OrphanedLabelFixer,
	FormAriaFixer,
	SkippedHeadingLevelFixer,
	HeadingNestingFixer,
	HeadingLengthFixer,
	HeadingUniquenessFixer,
	HeadingVisualFixer,
	TableHeaderFixer,
	TableCaptionFixer,
	ComplexTableFixer,
	LayoutTableFixer,
	EmptyTableCellFixer,
	ImageMapAltFixer,
	IframeTitleFixer,
	SvgAccessibilityFixer,
	ComplexImageFixer,
	LogoImageFixer,
	BackgroundImageFixer,
	AltTextQualityFixer,
	PositiveTabIndexFixer,
	InteractiveElementFixer,
	ModalAccessibilityFixer,
	FocusIndicatorFixer,
	KeyboardTrapFixer,
	FocusOrderFixer,
	TextColorContrastFixer,
	ColorRelianceFixer,
	ComplexContrastFixer,
	TouchTargetFixer,
	TouchGestureFixer,
	ViewportFixer,
	AriaRoleFixer,
	AriaAttributeFixer,
	AriaStateFixer,
	LandmarkRoleFixer,
	RedundantAriaFixer,
	InvalidAriaCombinationFixer,
	HiddenContentFixer,
	SemanticHtmlFixer,
	LiveRegionFixer,
	PageStructureFixer,
	VideoAccessibilityFixer,
	AudioAccessibilityFixer,
	MediaAlternativeFixer
};

/**
 * Fixer Registry
 * Maps checker IDs to their corresponding fixer classes
 */
class FixerRegistry {
	private static $registry    = array();
	private static $initialized = false;

	/**
	 * Map scanner checker IDs to legacy setting keys.
	 * This keeps fixes working even when the scanner uses a different ID
	 * than the settings/options keys stored in the DB.
	 */
	private static $aliases = array(
		// Link & Image aliases
		'generic-link-text'      => 'generic-link',
		'missing-form-label'     => 'missing-label',
		'redundant-alt-text'     => 'redundant-alt',
		'link-destination'       => 'link-dest',
		'new-window-link'        => 'new-window',
		'image-map-alt'          => 'image-map',
		'alt-text-quality'       => 'alt-quality',
		'svg-accessibility'      => 'svg-access',
		'background-image'       => 'bg-image',
		'heading-uniqueness'     => 'heading-unique',
		'text-color-contrast'    => 'contrast',

		// Form aliases
		'autocomplete-attribute' => 'autocomplete',
		'required-attribute'     => 'required-attr',
		'skipped-heading-level'  => 'skipped-heading',

		// ARIA/Structure aliases
		'modal-accessibility'    => 'modal-access',
		'custom-widget-keyboard' => 'widget-keyboard',
		'aria-attribute'         => 'aria-attr',
		'invalid-aria-combination' => 'invalid-aria',
		'media-alternative'      => 'media-alt',
		'video-accessibility'    => 'video-access',
		'audio-accessibility'    => 'audio-access',
		'empty-table-cell'       => 'empty-cell',
		'viewport-check'         => 'viewport',
	);

	/**
	 * Initialize the fixer registry
	 * Keys MUST match the scanner's check mapping keys exactly
	 */
	public static function init() {
		if ( self::$initialized ) {
			return;
		}

		// Load grouped fixer files that contain multiple classes
		$fixers_dir = __DIR__ . '/Fixers/';
		$grouped_files = array(
			'BaseFixer.php',
			'LinkAndImageFixers.php',
			'HeadingFixers.php',
			'FormFixers.php',
			'ContentFixers.php',
			'InteractivityFixers.php',
			'AriaAndSemanticFixers.php',
		);
		foreach ( $grouped_files as $file ) {
			$path = $fixers_dir . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		self::$registry = array(
			// Image Fixers - keys match scanner check IDs
			'missing-alt-text'    => MissingAltTextFixer::class,
			'empty-alt-text'      => EmptyAltTextFixer::class,
			'redundant-alt'       => RedundantAltTextFixer::class,
			'decorative-image'    => DecorativeImageFixer::class,
			'complex-image'       => ComplexImageFixer::class,
			'svg-access'          => SvgAccessibilityFixer::class,
			'bg-image'            => BackgroundImageFixer::class,
			'logo-image'          => LogoImageFixer::class,
			'image-map'           => ImageMapAltFixer::class,
			'alt-quality'         => AltTextQualityFixer::class,

			// Heading Fixers
			'missing-h1'          => MissingH1Fixer::class,
			'multiple-h1'         => MultipleH1Fixer::class,
			'empty-heading'       => EmptyHeadingFixer::class,
			'skipped-heading'     => SkippedHeadingLevelFixer::class,
			'heading-nesting'     => HeadingNestingFixer::class,
			'heading-length'      => HeadingLengthFixer::class,
			'heading-unique'      => HeadingUniquenessFixer::class,
			'heading-visual'      => HeadingVisualFixer::class,

			// Link Fixers
			'empty-link'          => EmptyLinkFixer::class,
			'generic-link'        => GenericLinkTextFixer::class,
			'new-window'          => NewWindowLinkFixer::class,
			'download-link'       => DownloadLinkFixer::class,
			'external-link'       => ExternalLinkFixer::class,
			'link-dest'           => LinkDestinationFixer::class,
			'skip-link'           => SkipLinkFixer::class,

			// Form Fixers
			'missing-label'       => MissingFormLabelFixer::class,
			'fieldset-legend'     => FieldsetLegendFixer::class,
			'required-attr'       => RequiredAttributeFixer::class,
			'error-message'       => ErrorMessageFixer::class,
			'autocomplete'        => AutocompleteFixer::class,
			'input-type'          => InputTypeFixer::class,
			'placeholder-label'   => PlaceholderLabelFixer::class,
			'custom-control'      => CustomControlFixer::class,
			'button-label'        => ButtonLabelFixer::class,
			'orphaned-label'      => OrphanedLabelFixer::class,
			'form-aria'           => FormAriaFixer::class,

			// Table Fixers
			'table-header'        => TableHeaderFixer::class,
			'table-caption'       => TableCaptionFixer::class,
			'complex-table'       => ComplexTableFixer::class,
			'layout-table'        => LayoutTableFixer::class,
			'empty-cell'          => EmptyTableCellFixer::class,

			// Media Fixers
			'iframe-title'        => IframeTitleFixer::class,
			'video-access'        => VideoAccessibilityFixer::class,
			'audio-access'        => AudioAccessibilityFixer::class,
			'media-alt'           => MediaAlternativeFixer::class,

			// Interactivity Fixers
			'positive-tabindex'   => PositiveTabIndexFixer::class,
			'interactive-element' => InteractiveElementFixer::class,
			'modal-access'        => ModalAccessibilityFixer::class,
			'focus-indicator'     => FocusIndicatorFixer::class,
			'keyboard-trap'       => KeyboardTrapFixer::class,
			'focus-order'         => FocusOrderFixer::class,
			'widget-keyboard'     => InteractiveElementFixer::class, // Alias

			// Color/Contrast Fixers (mostly manual)
			'contrast'            => TextColorContrastFixer::class,
			'color-reliance'      => ColorRelianceFixer::class,
			'complex-contrast'    => ComplexContrastFixer::class,

			// Touch/Viewport Fixers
			'touch-target'        => TouchTargetFixer::class,
			'touch-gesture'       => TouchGestureFixer::class,
			'viewport'            => ViewportFixer::class,

			// ARIA Fixers
			'aria-role'           => AriaRoleFixer::class,
			'aria-attr'           => AriaAttributeFixer::class,
			'aria-state'          => AriaStateFixer::class,
			'landmark-role'       => LandmarkRoleFixer::class,
			'redundant-aria'      => RedundantAriaFixer::class,
			'invalid-aria'        => InvalidAriaCombinationFixer::class,
			'hidden-content'      => HiddenContentFixer::class,
			'semantic-html'       => SemanticHtmlFixer::class,
			'live-region'         => LiveRegionFixer::class,
			'page-structure'      => PageStructureFixer::class,
		);

		// Register alias keys so fixer lookup works with both scanner IDs and legacy setting keys
		foreach ( self::$aliases as $scanner_id => $legacy_key ) {
			if ( isset( self::$registry[ $legacy_key ] ) && ! isset( self::$registry[ $scanner_id ] ) ) {
				self::$registry[ $scanner_id ] = self::$registry[ $legacy_key ];
			}
		}

		self::$initialized = true;
	}

	/**
	 * Get fixer class for a given checker ID
	 *
	 * @param string $checker_id
	 * @return string|null
	 */
	public static function get_fixer_class( $checker_id ) {
		self::init();
		
		// First, check aliases to translate scanner checker IDs to registry keys
		$key = isset( self::$aliases[ $checker_id ] ) ? self::$aliases[ $checker_id ] : $checker_id;
		
		return isset( self::$registry[ $key ] ) ? self::$registry[ $key ] : null;
	}

	/**
	 * Get fixer instance for a given checker ID
	 *
	 * @param string $checker_id
	 * @return object|null
	 */
	public static function get_fixer( $checker_id ) {
		$class = self::get_fixer_class( $checker_id );
		if ( $class && class_exists( $class ) ) {
			return new $class();
		}
		return null;
	}

	/**
	 * Check if fixer exists for checker ID
	 *
	 * @param string $checker_id
	 * @return bool
	 */
	public static function has_fixer( $checker_id ) {
		return self::get_fixer_class( $checker_id ) !== null;
	}

	/**
	 * Get all registered fixer IDs
	 *
	 * @return array
	 */
	public static function get_all_fixer_ids() {
		self::init();
		return array_keys( self::$registry );
	}

	/**
	 * Get fixer count
	 *
	 * @return int
	 */
	public static function get_fixer_count() {
		self::init();
		return count( self::$registry );
	}
}

