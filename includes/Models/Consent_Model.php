<?php
/**
 * Consent Model
 *
 * Represents a single consent record with type-safe accessors and manipulation methods.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Models
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Models;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Model Class
 *
 * Data object for consent records with strong typing and immutability.
 *
 * @since 3.0.1
 */
class Consent_Model {

	/**
	 * Unique consent ID
	 *
	 * @since 3.0.1
	 * @var int
	 */
	private $id;

	/**
	 * WordPress user ID (null for anonymous users)
	 *
	 * @since 3.0.1
	 * @var int|null
	 */
	private $user_id;

	/**
	 * SHA256 hash of IP address
	 *
	 * @since 3.0.1
	 * @var string|null
	 */
	private $ip_hash;

	/**
	 * Consent type (necessary, analytics, marketing, preferences)
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private $type;

	/**
	 * Consent status (accepted, rejected, withdrawn)
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private $status;

	/**
	 * JSON metadata (user agent, timestamp, etc.)
	 *
	 * @since 3.0.1
	 * @var array
	 */
	private $metadata;

	/**
	 * Creation timestamp
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private $created_at;

	/**
	 * Last update timestamp
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private $updated_at;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @param array $data Consent record data from database
	 */
	public function __construct( array $data = array() ) {
		$this->id         = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->user_id    = isset( $data['user_id'] ) ? (int) $data['user_id'] : null;
		$this->ip_hash    = $data['ip_hash'] ?? null;
		$this->type       = $data['type'] ?? '';
		$this->status     = $data['status'] ?? '';
		$this->created_at = $data['created_at'] ?? '';
		$this->updated_at = $data['updated_at'] ?? '';

		// Parse metadata from JSON string
		if ( isset( $data['metadata'] ) ) {
			if ( is_string( $data['metadata'] ) ) {
				$this->metadata = json_decode( $data['metadata'], true ) ?? array();
			} else {
				$this->metadata = (array) $data['metadata'];
			}
		} else {
			$this->metadata = array();
		}
	}

	/**
	 * Create model instance from database row
	 *
	 * @since 3.0.1
	 * @param object|array $row Database row
	 * @return self Model instance
	 */
	public static function from_database( $row ): self {
		return new self( (array) $row );
	}

	/**
	 * Get consent ID
	 *
	 * @since 3.0.1
	 * @return int|null Consent ID
	 */
	public function get_id(): ?int {
		return $this->id;
	}

	/**
	 * Get user ID
	 *
	 * @since 3.0.1
	 * @return int|null WordPress user ID or null if anonymous
	 */
	public function get_user_id(): ?int {
		return $this->user_id;
	}

	/**
	 * Get IP hash
	 *
	 * @since 3.0.1
	 * @return string|null SHA256 hash of IP address
	 */
	public function get_ip_hash(): ?string {
		return $this->ip_hash;
	}

	/**
	 * Get consent type
	 *
	 * @since 3.0.1
	 * @return string Consent type
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get consent status
	 *
	 * @since 3.0.1
	 * @return string Consent status
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get metadata
	 *
	 * @since 3.0.1
	 * @param string $key Optional specific metadata key
	 * @return mixed Metadata array or specific value
	 */
	public function get_metadata( string $key = '' ) {
		if ( empty( $key ) ) {
			return $this->metadata;
		}

		return $this->metadata[ $key ] ?? null;
	}

	/**
	 * Get creation timestamp
	 *
	 * @since 3.0.1
	 * @return string MySQL datetime format
	 */
	public function get_created_at(): string {
		return $this->created_at;
	}

	/**
	 * Get last update timestamp
	 *
	 * @since 3.0.1
	 * @return string MySQL datetime format
	 */
	public function get_updated_at(): string {
		return $this->updated_at;
	}

	/**
	 * Check if consent is accepted
	 *
	 * @since 3.0.1
	 * @return bool True if status is 'accepted'
	 */
	public function is_accepted(): bool {
		return 'accepted' === $this->status;
	}

	/**
	 * Check if consent is rejected
	 *
	 * @since 3.0.1
	 * @return bool True if status is 'rejected'
	 */
	public function is_rejected(): bool {
		return 'rejected' === $this->status;
	}

	/**
	 * Check if consent is withdrawn
	 *
	 * @since 3.0.1
	 * @return bool True if status is 'withdrawn'
	 */
	public function is_withdrawn(): bool {
		return 'withdrawn' === $this->status;
	}

	/**
	 * Check if consent is active (accepted and not withdrawn)
	 *
	 * @since 3.0.1
	 * @return bool True if consent is active
	 */
	public function is_active(): bool {
		return $this->is_accepted() && ! $this->is_withdrawn();
	}

	/**
	 * Check if consent is from anonymous user (IP-based)
	 *
	 * @since 3.0.1
	 * @return bool True if no user_id set
	 */
	public function is_anonymous(): bool {
		return null === $this->user_id;
	}

	/**
	 * Get age in seconds (time since creation)
	 *
	 * @since 3.0.1
	 * @return int|false Age in seconds or false if timestamp invalid
	 */
	public function get_age_seconds() {
		if ( empty( $this->created_at ) ) {
			return false;
		}

		$created = strtotime( $this->created_at );
		if ( false === $created ) {
			return false;
		}

		return time() - $created;
	}

	/**
	 * Check if consent has been recently modified
	 *
	 * @since 3.0.1
	 * @param int $seconds Number of seconds
	 * @return bool True if modified within last N seconds
	 */
	public function is_recently_modified( int $seconds = 3600 ): bool {
		if ( empty( $this->updated_at ) ) {
			return false;
		}

		$updated = strtotime( $this->updated_at );
		if ( false === $updated ) {
			return false;
		}

		return ( time() - $updated ) <= $seconds;
	}

	/**
	 * Convert to array for database operations
	 *
	 * @since 3.0.1
	 * @param bool $include_id Include ID in array
	 * @return array Consent data as array
	 */
	public function to_array( bool $include_id = true ): array {
		$data = array(
			'user_id'    => $this->user_id,
			'ip_hash'    => $this->ip_hash,
			'type'       => $this->type,
			'status'     => $this->status,
			'metadata'   => wp_json_encode( $this->metadata ),
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		);

		if ( $include_id && $this->id ) {
			$data['id'] = $this->id;
		}

		return $data;
	}

	/**
	 * Convert to REST API response format
	 *
	 * @since 3.0.1
	 * @return array REST response data
	 */
	public function to_rest_response(): array {
		return array(
			'id'         => $this->id,
			'user_id'    => $this->user_id,
			'ip_hash'    => $this->ip_hash,
			'type'       => $this->type,
			'status'     => $this->status,
			'metadata'   => $this->metadata,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
			'is_active'  => $this->is_active(),
		);
	}

	/**
	 * Validate model integrity
	 *
	 * @since 3.0.1
	 * @return bool True if model is valid
	 */
	public function is_valid(): bool {
		// Must have type and status
		if ( empty( $this->type ) || empty( $this->status ) ) {
			return false;
		}

		// Must have either user_id or ip_hash
		if ( null === $this->user_id && null === $this->ip_hash ) {
			return false;
		}

		return true;
	}

	/**
	 * Get validation errors
	 *
	 * @since 3.0.1
	 * @return array Array of validation error messages
	 */
	public function get_validation_errors(): array {
		$errors = array();

		if ( empty( $this->type ) ) {
			$errors[] = 'Consent type is required';
		}

		if ( empty( $this->status ) ) {
			$errors[] = 'Consent status is required';
		}

		if ( null === $this->user_id && null === $this->ip_hash ) {
			$errors[] = 'Either user_id or ip_hash is required';
		}

		return $errors;
	}

	/**
	 * Magic method: allow read-only property access
	 *
	 * @since 3.0.1
	 * @param string $name Property name
	 * @return mixed Property value
	 */
	public function __get( string $name ) {
		$getter = 'get_' . $name;

		if ( method_exists( $this, $getter ) ) {
			return $this->$getter();
		}

		return null;
	}

	/**
	 * Magic method: prevent modification
	 *
	 * @since 3.0.1
	 * @param string $name Property name
	 * @param mixed  $value Value
	 */
	public function __set( string $name, $value ) {
		// Prevent modification - model should be immutable
		_doing_it_wrong(
			__METHOD__,
			'Consent_Model is immutable. Use Consent_Service to modify consent data.',
			'3.0.1'
		);
	}
}
