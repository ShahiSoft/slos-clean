<?php
/**
 * Config Service
 *
 * Handles configuration operations for accessibility scanner.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Services
 * @since      3.2.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;

/**
 * Service class for configuration operations
 *
 * @since 3.2.0
 */
class ConfigService {

	/**
	 * Scanner engine instance
	 *
	 * @var ScannerEngine
	 */
	private $scanner;

	/**
	 * Constructor
	 *
	 * @since 3.2.0
	 *
	 * @param ScannerEngine $scanner Scanner engine instance.
	 */
	public function __construct( ScannerEngine $scanner ) {
		$this->scanner = $scanner;
	}

	/**
	 * Get scanner engine instance
	 *
	 * @since 3.2.0
	 *
	 * @return ScannerEngine Scanner engine instance.
	 */
	public function get_scanner() {
		return $this->scanner;
	}

	/**
	 * Get mapping of checker keys to class names
	 *
	 * @since 3.2.0
	 *
	 * @return array Mapping of checker keys to fully qualified class names.
	 */
	public function get_check_mapping() {
		$namespace = 'ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\Scanner\\Checkers\\';

		return array(
			'missing-alt-text'     => $namespace . 'MissingAltTextCheck',
			'empty-alt-text'       => $namespace . 'EmptyAltTextCheck',
			'missing-h1'           => $namespace . 'MissingH1Check',
			'skipped-heading'      => $namespace . 'SkippedHeadingCheck',
			'empty-link'           => $namespace . 'EmptyLinkCheck',
			'generic-link'         => $namespace . 'GenericLinkTextCheck',
			'missing-label'        => $namespace . 'MissingLabelCheck',
			'redundant-alt'        => $namespace . 'RedundantAltTextCheck',
			'empty-heading'        => $namespace . 'EmptyHeadingCheck',
			'new-window'           => $namespace . 'NewWindowLinkCheck',
			'positive-tabindex'    => $namespace . 'PositiveTabIndexCheck',
			'image-map'            => $namespace . 'ImageMapCheck',
			'iframe-title'         => $namespace . 'IframeTitleCheck',
			'button-label'         => $namespace . 'ButtonLabelCheck',
			'table-header'         => $namespace . 'TableHeaderCheck',
			'alt-quality'          => $namespace . 'AltTextQualityCheck',
			'decorative-image'     => $namespace . 'DecorativeImageCheck',
			'complex-image'        => $namespace . 'ComplexImageCheck',
			'svg-access'           => $namespace . 'SvgAccessibilityCheck',
			'bg-image'             => $namespace . 'BackgroundImageCheck',
			'logo-image'           => $namespace . 'LogoImageCheck',
			'multiple-h1'          => $namespace . 'MultipleH1Check',
			'heading-visual'       => $namespace . 'VisualHeadingCheck',
			'heading-length'       => $namespace . 'HeadingLengthCheck',
			'heading-unique'       => $namespace . 'UniqueHeadingCheck',
			'heading-nesting'      => $namespace . 'HeadingNestingCheck',
			'fieldset-legend'      => $namespace . 'FieldsetLegendCheck',
			'autocomplete'         => $namespace . 'AutocompleteCheck',
			'input-type'           => $namespace . 'InputTypeCheck',
			'placeholder-label'    => $namespace . 'PlaceholderAsLabelCheck',
			'custom-control'       => $namespace . 'CustomControlCheck',
			'orphaned-label'       => $namespace . 'OrphanedLabelCheck',
			'required-attr'        => $namespace . 'RequiredAttributeCheck',
			'error-message'        => $namespace . 'ErrorMessageCheck',
			'form-aria'            => $namespace . 'FormAriaCheck',
			'link-dest'            => $namespace . 'LinkDestinationCheck',
			'skip-link'            => $namespace . 'SkipLinkCheck',
			'download-link'        => $namespace . 'DownloadLinkCheck',
			'external-link'        => $namespace . 'ExternalLinkCheck',
			'contrast'             => $namespace . 'ColorContrastCheck',
			'focus-indicator'      => $namespace . 'FocusIndicatorCheck',
			'color-reliance'       => $namespace . 'ColorRelianceCheck',
			'complex-contrast'     => $namespace . 'ComplexContrastCheck',
			'keyboard-trap'        => $namespace . 'KeyboardTrapCheck',
			'focus-order'          => $namespace . 'FocusOrderCheck',
			'interactive-element'  => $namespace . 'InteractiveElementCheck',
			'modal-access'         => $namespace . 'ModalAccessibilityCheck',
			'widget-keyboard'      => $namespace . 'CustomWidgetKeyboardCheck',
			'aria-role'            => $namespace . 'AriaRoleCheck',
			'aria-attr'            => $namespace . 'AriaAttributeCheck',
			'landmark-role'        => $namespace . 'LandmarkRoleCheck',
			'redundant-aria'       => $namespace . 'RedundantAriaCheck',
			'hidden-content'       => $namespace . 'HiddenContentCheck',
			'semantic-html'        => $namespace . 'SemanticHtmlCheck',
			'live-region'          => $namespace . 'LiveRegionCheck',
			'aria-state'           => $namespace . 'AriaStateCheck',
			'invalid-aria'         => $namespace . 'InvalidAriaCheck',
			'page-structure'       => $namespace . 'PageStructureCheck',
			'video-access'         => $namespace . 'VideoAccessibilityCheck',
			'audio-access'         => $namespace . 'AudioAccessibilityCheck',
			'media-alt'            => $namespace . 'MediaAlternativeCheck',
			'table-caption'        => $namespace . 'TableCaptionCheck',
			'complex-table'        => $namespace . 'ComplexTableCheck',
			'layout-table'         => $namespace . 'LayoutTableCheck',
			'empty-cell'           => $namespace . 'EmptyTableCellCheck',
			'viewport'             => $namespace . 'ViewportCheck',
			'touch-target'         => $namespace . 'TouchTargetCheck',
			'touch-gesture'        => $namespace . 'TouchGestureCheck',
			'language-change'      => $namespace . 'LanguageChangeCheck',
			'animation-pause'      => $namespace . 'AnimationPauseCheck',
			'timing-control'       => $namespace . 'TimingControlCheck',
			'status-message'       => $namespace . 'StatusMessageCheck',
			'error-identification' => $namespace . 'ErrorIdentificationCheck',
		);
	}
}
