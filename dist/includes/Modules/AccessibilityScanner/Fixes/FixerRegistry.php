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
	MediaAlternativeFixer,
	// Phase 3: New Fixer Classes..
	LanguageChangeFixer,
	StatusMessageFixer,
	ErrorIdentificationFixer,
	AnimationPauseFixer,
	TimingControlFixer
};
use ShahiLegalFlowSuite\FixEngine\CanonicalIds;

/**
 * Fixer Registry
 * Maps checker IDs to their corresponding fixer classes
 *
 * NOTE: All IDs are now canonical. Legacy aliases have been removed.
 * Use IdCanonicalizationMigration to update stored data.
 */
class FixerRegistry {
	private static $registry    = array();
	private static $initialized = false;

	/**
	 * Initialize the fixer registry
	 * Keys MUST match the canonical IDs defined in FixEngine/CanonicalIds.php
	 */
	public static function init() {
		// Skip initialization if autofix is dormant..
		if ( defined( 'SLOS_DORMANT_AUTOFIX' ) && SLOS_DORMANT_AUTOFIX ) {
			self::$initialized = true;
			self::$registry    = array();
			return;
		}

		if ( self::$initialized ) {
			return;
		}

		self::$registry = array(
			// Image Fixers - canonical IDs..
			'missing-alt-text'           => MissingAltTextFixer::class,
			'empty-alt-text'             => EmptyAltTextFixer::class,
			'redundant-alt-text'         => RedundantAltTextFixer::class,
			'decorative-image'           => DecorativeImageFixer::class,
			'complex-image'              => ComplexImageFixer::class,
			'missing-svg-title'          => SvgAccessibilityFixer::class,
			'background-image'           => BackgroundImageFixer::class,
			'logo-image'                 => LogoImageFixer::class,
			'missing-image-map-alt'      => ImageMapAltFixer::class,
			'alt-text-quality'           => AltTextQualityFixer::class,

			// Heading Fixers - canonical IDs..
			'missing-h1'                 => MissingH1Fixer::class,
			'multiple-h1'                => MultipleH1Fixer::class,
			'empty-heading'              => EmptyHeadingFixer::class,
			'skipped-heading-level'      => SkippedHeadingLevelFixer::class,
			'heading-nesting'            => HeadingNestingFixer::class,
			'heading-length'             => HeadingLengthFixer::class,
			'heading-uniqueness'         => HeadingUniquenessFixer::class,
			'heading-visual'             => HeadingVisualFixer::class,

			// Link Fixers - canonical IDs..
			'empty-link'                 => EmptyLinkFixer::class,
			'generic-link-text'          => GenericLinkTextFixer::class,
			'link-opens-new-window'      => NewWindowLinkFixer::class,
			'download-link'              => DownloadLinkFixer::class,
			'external-link'              => ExternalLinkFixer::class,
			'link-destination'           => LinkDestinationFixer::class,
			'missing-skip-link'          => SkipLinkFixer::class,

			// Form Fixers - canonical IDs..
			'missing-form-label'         => MissingFormLabelFixer::class,
			'missing-fieldset-legend'    => FieldsetLegendFixer::class,
			'missing-required-attribute' => RequiredAttributeFixer::class,
			'missing-error-description'  => ErrorMessageFixer::class,
			'autocomplete-attribute'     => AutocompleteFixer::class,
			'input-type'                 => InputTypeFixer::class,
			'placeholder-label'          => PlaceholderLabelFixer::class,
			'custom-control'             => CustomControlFixer::class,
			'button-label'               => ButtonLabelFixer::class,
			'orphaned-label'             => OrphanedLabelFixer::class,
			'form-aria'                  => FormAriaFixer::class,

			// Table Fixers - canonical IDs..
			'missing-table-headers'      => TableHeaderFixer::class,
			'missing-table-caption'      => TableCaptionFixer::class,
			'complex-table'              => ComplexTableFixer::class,
			'layout-table'               => LayoutTableFixer::class,
			'empty-table-cell'           => EmptyTableCellFixer::class,

			// Media Fixers - canonical IDs..
			'missing-iframe-title'       => IframeTitleFixer::class,
			'missing-video-caption'      => VideoAccessibilityFixer::class,
			'audio-accessibility'        => AudioAccessibilityFixer::class,
			'media-alternative'          => MediaAlternativeFixer::class,

			// Interactivity Fixers - canonical IDs..
			'invalid-tabindex'           => PositiveTabIndexFixer::class,
			'interactive-element'        => InteractiveElementFixer::class,
			'modal-accessibility'        => ModalAccessibilityFixer::class,
			'missing-focus-indicator'    => FocusIndicatorFixer::class,
			'keyboard-trap'              => KeyboardTrapFixer::class,
			'focus-order'                => FocusOrderFixer::class,

			// Color/Contrast Fixers - canonical IDs..
			'text-color-contrast'        => TextColorContrastFixer::class,
			'color-reliance'             => ColorRelianceFixer::class,
			'complex-contrast'           => ComplexContrastFixer::class,

			// Touch/Viewport Fixers - canonical IDs..
			'touch-target'               => TouchTargetFixer::class,
			'touch-gesture'              => TouchGestureFixer::class,
			'improper-viewport'          => ViewportFixer::class,

			// ARIA Fixers - canonical IDs..
			'aria-role'                  => AriaRoleFixer::class,
			'aria-attribute'             => AriaAttributeFixer::class,
			'aria-state'                 => AriaStateFixer::class,
			'missing-landmark'           => LandmarkRoleFixer::class,
			'redundant-aria'             => RedundantAriaFixer::class,
			'invalid-aria-combination'   => InvalidAriaCombinationFixer::class,
			'hidden-content'             => HiddenContentFixer::class,
			'semantic-html'              => SemanticHtmlFixer::class,
			'live-region'                => LiveRegionFixer::class,
			'page-structure'             => PageStructureFixer::class,

			// Additional Fixers - canonical IDs..
			'language-change'            => LanguageChangeFixer::class,      // WCAG 3.1.2
			'status-message'             => StatusMessageFixer::class,       // WCAG 4.1.3
			'error-identification'       => ErrorIdentificationFixer::class, // WCAG 3.3.1
			'animation-pause'            => AnimationPauseFixer::class,      // WCAG 2.2.2
			'timing-control'             => TimingControlFixer::class,       // WCAG 2.2.1
		);

		// Normalize registry keys to canonical IDs..
		$normalized = array();
		foreach ( self::$registry as $id => $class ) {
			$canonical                = CanonicalIds::canonicalize( $id ) ?? $id;
			$normalized[ $canonical ] = $class;
		}
		self::$registry = $normalized;

		self::$initialized = true;
	}

	/**
	 * Get fixer class for a given checker ID
	 *
	 * @param string $checker_id Canonical checker ID
	 * @return string|null
	 */
	public static function get_fixer_class( $checker_id ) {
		self::init();
		$canonical_id = CanonicalIds::canonicalize( $checker_id ) ?? $checker_id;

		return isset( self::$registry[ $canonical_id ] ) ? self::$registry[ $canonical_id ] : null;
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

		// Only count fixers that can actually be instantiated. This keeps..
		// diagnostics and any UI that relies on this value aligned with the..
		// real set of available fixers...
		$count = 0;
		foreach ( array_keys( self::$registry ) as $checker_id ) {
			if ( self::get_fixer( $checker_id ) ) {
				++$count;
			}
		}

		return $count;
	}
}
