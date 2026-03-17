<?php
/**
 * Email definitions CRUD.
 *
 * Manages the storage and retrieval of email definitions
 * from wp_options.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Email_Definitions class.
 *
 * Provides get, save, and delete operations for email definitions
 * stored as a serialized array in wp_options.
 *
 * @since 0.1.0
 */
class Email_Definitions {

	/**
	 * Get all email definitions.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, array<string, mixed>> Keyed by definition ID.
	 */
	public function get_all(): array {
		$definitions = get_option( OPT_EMAIL_DEFINITIONS, [] );

		if ( ! is_array( $definitions ) ) {
			$definitions = [];
		}

		return $definitions;
	}

	/**
	 * Get a single email definition by ID.
	 *
	 * @since 0.1.0
	 *
	 * @param string $definition_id The definition ID.
	 *
	 * @return array<string, mixed>|null The definition, or null if not found.
	 */
	public function get( string $definition_id ): ?array {
		$definitions = $this->get_all();
		$result      = null;

		if ( isset( $definitions[ $definition_id ] ) ) {
			$result = $definitions[ $definition_id ];
		}

		return $result;
	}

	/**
	 * Save an email definition.
	 *
	 * If the definition ID already exists, it will be updated.
	 * If it does not exist, it will be created.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $definition_id The definition ID.
	 * @param array<string, mixed> $definition    The definition data.
	 *
	 * @return bool True on success.
	 */
	public function save( string $definition_id, array $definition ): bool {
		$definitions                   = $this->get_all();
		$definitions[ $definition_id ] = $definition;

		return update_option( OPT_EMAIL_DEFINITIONS, $definitions );
	}

	/**
	 * Delete an email definition.
	 *
	 * @since 0.1.0
	 *
	 * @param string $definition_id The definition ID.
	 *
	 * @return bool True on success.
	 */
	public function delete( string $definition_id ): bool {
		$definitions = $this->get_all();
		$result      = false;

		if ( isset( $definitions[ $definition_id ] ) ) {
			unset( $definitions[ $definition_id ] );
			$result = update_option( OPT_EMAIL_DEFINITIONS, $definitions );
		}

		return $result;
	}

	/**
	 * Get only enabled email definitions.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, array<string, mixed>> Keyed by definition ID.
	 */
	public function get_enabled(): array {
		$definitions = $this->get_all();
		$result      = [];

		foreach ( $definitions as $id => $definition ) {
			$enabled = isset( $definition['enabled'] )
				? (bool) filter_var( $definition['enabled'], FILTER_VALIDATE_BOOLEAN )
				: false;

			if ( $enabled ) {
				$result[ $id ] = $definition;
			}
		}

		return $result;
	}

	/**
	 * Generate a unique definition ID from a label.
	 *
	 * @since 0.1.0
	 *
	 * @param string $label The human-readable label.
	 *
	 * @return string A unique slug-style ID.
	 */
	public function generate_id( string $label ): string {
		$base_id     = sanitize_title( $label );
		$definitions = $this->get_all();
		$id          = $base_id;
		$counter     = 1;

		while ( isset( $definitions[ $id ] ) ) {
			++$counter;
			$id = $base_id . '-' . $counter;
		}

		return $id;
	}

	/**
	 * Get the default structure for a new definition.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Default definition values.
	 */
	public function get_defaults(): array {
		return [
			'label'            => '',
			'subject'          => '',
			'body'             => '',
			'status'           => '',
			'days'             => 0,
			'enabled'          => false,
			'recipient'        => RECIPIENT_CUSTOMER,
			'recipient_custom' => '',
		];
	}
}
