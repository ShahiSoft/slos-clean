<?php
/**
 * Autocomplete Fixer
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
 * Class AutocompleteFixer
 *
 * Adds autocomplete attributes to form inputs based on their type/name/id patterns.
 */
final class AutocompleteFixer extends AbstractFixer {

	/**
	 * Mapping of field patterns to autocomplete values
	 */
	private const AUTOCOMPLETE_MAP = array(
		// Names..
		'name'             => 'name',
		'full_name'        => 'name',
		'fullname'         => 'name',
		'first_name'       => 'given-name',
		'firstname'        => 'given-name',
		'fname'            => 'given-name',
		'last_name'        => 'family-name',
		'lastname'         => 'family-name',
		'lname'            => 'family-name',
		'surname'          => 'family-name',

		// Contact..
		'email'            => 'email',
		'e-mail'           => 'email',
		'phone'            => 'tel',
		'telephone'        => 'tel',
		'tel'              => 'tel',
		'mobile'           => 'tel',

		// Address..
		'address'          => 'street-address',
		'address1'         => 'address-line1',
		'address2'         => 'address-line2',
		'city'             => 'address-level2',
		'state'            => 'address-level1',
		'province'         => 'address-level1',
		'zip'              => 'postal-code',
		'zipcode'          => 'postal-code',
		'postal'           => 'postal-code',
		'postcode'         => 'postal-code',
		'country'          => 'country-name',

		// Auth..
		'username'         => 'username',
		'user'             => 'username',
		'password'         => 'current-password',
		'pass'             => 'current-password',
		'new_password'     => 'new-password',
		'newpassword'      => 'new-password',
		'confirm_password' => 'new-password',

		// Payment (credit card)..
		'cc_number'        => 'cc-number',
		'card_number'      => 'cc-number',
		'cardnumber'       => 'cc-number',
		'cc_name'          => 'cc-name',
		'cc_exp'           => 'cc-exp',
		'expiry'           => 'cc-exp',
		'cc_csc'           => 'cc-csc',
		'cvv'              => 'cc-csc',
		'cvc'              => 'cc-csc',

		// Personal..
		'birthday'         => 'bday',
		'dob'              => 'bday',
		'birthdate'        => 'bday',
		'organization'     => 'organization',
		'company'          => 'organization',
		'job_title'        => 'organization-title',
		'jobtitle'         => 'organization-title',
	);

	public function get_id(): string {
		return 'autocomplete';
	}

	public function get_name(): string {
		return __( 'Autocomplete Attributes', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds appropriate autocomplete attributes to form inputs based on their purpose.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return array( '1.3.5' );
	}

	public function get_category(): string {
		return 'forms';
	}

	public function can_fix( string $content ): bool {
		return (bool) preg_match( '/<input[^>]*type\s*=\s*["\']?(text|email|tel|password|search)["\']?/i', $content );
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$doc = $this->parse_html( $content );

		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$inputs        = $this->query( '//input[not(@autocomplete)]' );
		$fixes_applied = 0;
		$details       = array();

		foreach ( $inputs as $input ) {
			$type = strtolower( $input->getAttribute( 'type' ) ?: 'text' );

			// Only process relevant input types..
			if ( ! in_array( $type, array( 'text', 'email', 'tel', 'password', 'search', 'url' ), true ) ) {
				continue;
			}

			// Handle email/tel type inputs specially..
			if ( $type === 'email' ) {
				$input->setAttribute( 'autocomplete', 'email' );
				++$fixes_applied;
				$details[] = array(
					'type'         => 'email',
					'autocomplete' => 'email',
				);
				continue;
			}

			if ( $type === 'tel' ) {
				$input->setAttribute( 'autocomplete', 'tel' );
				++$fixes_applied;
				$details[] = array(
					'type'         => 'tel',
					'autocomplete' => 'tel',
				);
				continue;
			}

			// Try to determine autocomplete value from name, id, or placeholder..
			$name        = strtolower( $input->getAttribute( 'name' ) );
			$id          = strtolower( $input->getAttribute( 'id' ) );
			$placeholder = strtolower( $input->getAttribute( 'placeholder' ) );

			$autocomplete = $this->determine_autocomplete( $name, $id, $placeholder, $type );

			if ( $autocomplete ) {
				$input->setAttribute( 'autocomplete', $autocomplete );
				++$fixes_applied;
				$details[] = array(
					'name'         => $input->getAttribute( 'name' ),
					'autocomplete' => $autocomplete,
				);
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No inputs found that need autocomplete attributes', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Determine the appropriate autocomplete value
	 */
	private function determine_autocomplete( string $name, string $id, string $placeholder, string $type ): ?string {
		// Check each against the mapping..
		foreach ( self::AUTOCOMPLETE_MAP as $pattern => $value ) {
			// Check if any identifier contains the pattern..
			if ( $this->contains_pattern( $name, $pattern ) ||
				$this->contains_pattern( $id, $pattern ) ||
				$this->contains_pattern( $placeholder, $pattern ) ) {
				return $value;
			}
		}

		// Special handling for password type..
		if ( $type === 'password' ) {
			return 'current-password';
		}

		return null;
	}

	/**
	 * Check if string contains pattern
	 */
	private function contains_pattern( string $haystack, string $pattern ): bool {
		if ( empty( $haystack ) ) {
			return false;
		}

		// Normalize separators..
		$haystack = str_replace( array( '-', '_', ' ' ), '', $haystack );
		$pattern  = str_replace( array( '-', '_', ' ' ), '', $pattern );

		return stripos( $haystack, $pattern ) !== false;
	}
}
