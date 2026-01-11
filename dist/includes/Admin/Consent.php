<?php
/**
 * Consent Admin Page
 *
 * Provides the Consent & Compliance admin experience with list, filters,
 * and stats powered by the Consent_Service and REST API.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Core\Security;
use ShahiLegalFlowSuite\Services\Consent_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Admin Controller
 *
 * Renders the Consent & Compliance admin page and supplies view data.
 *
 * @since 3.0.1
 */
class Consent {

	/**
	 * Security helper
	 *
	 * @since 3.0.1
	 * @var Security
	 */
	private $security;

	/**
	 * Consent service
	 *
	 * @since 3.0.1
	 * @var Consent_Service
	 */
	private $service;

	/**
	 * Initialize controller
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		$this->security = new Security();
		$this->service  = new Consent_Service();
	}

	/**
	 * Render admin page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		?>
		<div class="wrap slos-consent-page">
			<h1><?php esc_html_e( 'Consent & Compliance', 'shahi-legalflowsuite' ); ?></h1>
			<?php $this->render_content(); ?>
		</div>
		<?php
	}

	/**
	 * Render just the content (for use in tabbed interface)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		$stats            = $this->normalize_stats( $this->service->get_statistics() );
		$recent_consents  = $this->format_consent_records( $this->service->get_recent_consents( 10 ) );
		$allowed_types    = $this->service->get_allowed_types();
		$allowed_statuses = $this->service->get_allowed_statuses();

		$filters = array(
			'periods'  => array(
				'today'   => __( 'Today', 'shahi-legalflowsuite' ),
				'7d'      => __( 'Last 7 days', 'shahi-legalflowsuite' ),
				'30d'     => __( 'Last 30 days', 'shahi-legalflowsuite' ),
				'quarter' => __( 'Quarter to date', 'shahi-legalflowsuite' ),
				'year'    => __( 'Year to date', 'shahi-legalflowsuite' ),
			),
			'types'    => $allowed_types,
			'statuses' => $allowed_statuses,
		);

		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/consent/manager.php';
	}

	/**
	 * Normalize statistics payload for the view.
	 *
	 * @since 3.0.1
	 * @param array $stats Raw stats array.
	 * @return array
	 */
	private function normalize_stats( array $stats ): array {
		$defaults = array(
			'by_type'   => array(),
			'by_status' => array(),
		);

		$stats = wp_parse_args( $stats, $defaults );

		return array(
			'by_type'   => $stats['by_type'],
			'by_status' => $stats['by_status'],
		);
	}

	/**
	 * Convert consent records to safe arrays for templating.
	 *
	 * @since 3.0.1
	 * @param array $records Raw records from repository/service.
	 * @return array
	 */
	private function format_consent_records( array $records ): array {
		$formatted = array();

		foreach ( $records as $record ) {
			$formatted[] = array(
				'id'         => absint( $record->id ?? 0 ),
				'user_id'    => absint( $record->user_id ?? 0 ),
				'type'       => $record->type ?? '',
				'status'     => $record->status ?? '',
				'created_at' => $record->created_at ?? '',
				'updated_at' => $record->updated_at ?? '',
			);
		}

		return $formatted;
	}
}
