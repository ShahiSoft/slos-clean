<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ScannerEngine {

	/**
	 * Registered checks
	 *
	 * @var CheckInterface[]
	 */
	private $checks = array();

	/**
	 * Register a check
	 *
	 * @param CheckInterface $check
	 */
	public function register_check( CheckInterface $check ) {
		$this->checks[ $check->get_id() ] = $check;
	}

	/**
	 * Run all checks on content
	 *
	 * @param string $content HTML content
	 * @return array Scan results
	 */
	public function scan( $content ) {
		$results = array();

		if ( empty( $content ) ) {
			return $results;
		}

		// Parse DOM once and share among checkers (huge performance boost)
		$dom = new \DOMDocument();
		@$dom->loadHTML( '<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING );

		foreach ( $this->checks as $check ) {
			try {
				// Pass both content and pre-parsed DOM to checkers
				$issues = method_exists( $check, 'check_dom' )
					? $check->check_dom( $dom, $content )
					: $check->check( $content );

				if ( ! empty( $issues ) ) {
					$results[ $check->get_id() ] = array(
						'id'          => $check->get_id(),
						'description' => $check->get_description(),
						'severity'    => $check->get_severity(),
						'issues'      => $issues,
					);
				}
			} catch ( \Exception $e ) {
				// Log error but continue scanning
				error_log( 'Accessibility Scanner Error in check ' . $check->get_id() . ': ' . $e->getMessage() );
			}
		}

		return $results;
	}
}

