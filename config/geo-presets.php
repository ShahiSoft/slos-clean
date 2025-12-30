<?php
/**
 * Geographic Region Presets
 *
 * Pre-configured geo targeting rules for common jurisdictions.
 * Each preset defines default consent model, banner template, and required legal documents.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Config
 * @since      3.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'EU' => array(
		'label'         => __( 'European Union / EEA', 'shahi-legalflowsuite' ),
		'countries'     => array( 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'IS', 'LI', 'NO' ),
		'consent_model' => 'opt-in',
		'framework'     => 'GDPR',
		'template'      => 'eu',
		'legal_docs'    => array( 'privacy-policy', 'cookie-policy' ),
		'description'   => __( 'Strict opt-in consent per GDPR and ePrivacy Directive. Covers all 30 EEA countries.', 'shahi-legalflowsuite' ),
		'priority'      => 10,
		'config'        => array(
			'show_banner'      => true,
			'show_reject'      => true,
			'require_explicit' => true,
			'record_proof'     => true,
			'allow_withdraw'   => true,
		),
	),

	'UK' => array(
		'label'         => __( 'United Kingdom', 'shahi-legalflowsuite' ),
		'countries'     => array( 'GB' ),
		'consent_model' => 'opt-in',
		'framework'     => 'UK-GDPR',
		'template'      => 'eu',
		'legal_docs'    => array( 'privacy-policy', 'cookie-policy' ),
		'description'   => __( 'UK GDPR mirrors EU requirements with local supervisory authority (ICO).', 'shahi-legalflowsuite' ),
		'priority'      => 10,
		'config'        => array(
			'show_banner'      => true,
			'show_reject'      => true,
			'require_explicit' => true,
			'record_proof'     => true,
			'allow_withdraw'   => true,
		),
	),

	'US-CA' => array(
		'label'         => __( 'California (CCPA/CPRA)', 'shahi-legalflowsuite' ),
		'countries'     => array( 'US' ),
		'states'        => array( 'CA' ),
		'consent_model' => 'opt-out',
		'framework'     => 'CCPA',
		'template'      => 'ccpa',
		'legal_docs'    => array( 'privacy-policy' ),
		'description'   => __( 'Opt-out model with "Do Not Sell My Personal Information" link required per CCPA/CPRA.', 'shahi-legalflowsuite' ),
		'priority'      => 5,
		'config'        => array(
			'show_banner'      => true,
			'show_reject'      => true,
			'require_explicit' => false,
			'record_proof'     => true,
			'allow_withdraw'   => true,
		),
	),

	'BR' => array(
		'label'         => __( 'Brazil (LGPD)', 'shahi-legalflowsuite' ),
		'countries'     => array( 'BR' ),
		'consent_model' => 'opt-in',
		'framework'     => 'LGPD',
		'template'      => 'advanced',
		'legal_docs'    => array( 'privacy-policy', 'cookie-policy' ),
		'description'   => __( 'LGPD (Lei Geral de Proteção de Dados) requires explicit consent for personal data processing.', 'shahi-legalflowsuite' ),
		'priority'      => 10,
		'config'        => array(
			'show_banner'      => true,
			'show_reject'      => true,
			'require_explicit' => true,
			'record_proof'     => true,
			'allow_withdraw'   => true,
		),
	),

	'ROW' => array(
		'label'         => __( 'Rest of World', 'shahi-legalflowsuite' ),
		'countries'     => array( '*' ),
		'consent_model' => 'notice-only',
		'framework'     => 'none',
		'template'      => 'simple',
		'legal_docs'    => array( 'privacy-policy' ),
		'description'   => __( 'Informational notice without blocking. Use for regions without strict privacy laws.', 'shahi-legalflowsuite' ),
		'priority'      => 1,
		'config'        => array(
			'show_banner'      => true,
			'show_reject'      => false,
			'require_explicit' => false,
			'record_proof'     => false,
			'allow_withdraw'   => true,
		),
	),
);
