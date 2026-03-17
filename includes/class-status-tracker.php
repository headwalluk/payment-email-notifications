<?php
/**
 * Order status change tracker.
 *
 * Records when orders change status so we can calculate
 * how many days an order has been at its current status.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Status_Tracker class.
 *
 * Hooks into WooCommerce order status changes and stores
 * the timestamp in order meta.
 *
 * @since 0.1.0
 */
class Status_Tracker {

	/**
	 * Register hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'woocommerce_order_status_changed', array( $this, 'on_status_changed' ), 10, 1 );
	}

	/**
	 * Handle order status change.
	 *
	 * Records the current timestamp and clears the sent emails log
	 * so that emails can re-trigger if the order returns to a
	 * previously-seen status.
	 *
	 * @since 0.1.0
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return void
	 */
	public function on_status_changed( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$order->update_meta_data( META_STATUS_CHANGED_AT, pen_get_now_formatted() );
		$order->update_meta_data( META_EMAILS_SENT, [] );
		$order->save();
	}

	/**
	 * Get the number of whole days an order has been at its current status.
	 *
	 * Returns null if the status change timestamp is not recorded,
	 * which happens for orders that existed before the plugin was activated.
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order The WooCommerce order.
	 *
	 * @return int|null Number of days, or null if unknown.
	 */
	public function get_days_at_current_status( \WC_Order $order ): ?int {
		$result = null;

		$changed_at = $order->get_meta( META_STATUS_CHANGED_AT, true );

		if ( ! empty( $changed_at ) ) {
			try {
				$changed_date = new \DateTime( $changed_at, wp_timezone() );
				$now          = new \DateTime( 'now', wp_timezone() );
				$interval     = $changed_date->diff( $now );
				$result       = (int) $interval->days;
			} catch ( \Exception $e ) {
				$result = null;
			}
		}

		return $result;
	}

	/**
	 * Check whether a specific email definition has already been sent for an order.
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order         The WooCommerce order.
	 * @param string    $definition_id The email definition ID.
	 *
	 * @return bool True if already sent.
	 */
	public function has_email_been_sent( \WC_Order $order, string $definition_id ): bool {
		$sent = $order->get_meta( META_EMAILS_SENT, true );

		if ( ! is_array( $sent ) ) {
			$sent = [];
		}

		return in_array( $definition_id, $sent, true );
	}

	/**
	 * Record that an email definition has been sent for an order.
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order         The WooCommerce order.
	 * @param string    $definition_id The email definition ID.
	 *
	 * @return void
	 */
	public function record_email_sent( \WC_Order $order, string $definition_id ): void {
		$sent = $order->get_meta( META_EMAILS_SENT, true );

		if ( ! is_array( $sent ) ) {
			$sent = [];
		}

		if ( ! in_array( $definition_id, $sent, true ) ) {
			$sent[] = $definition_id;
			$order->update_meta_data( META_EMAILS_SENT, $sent );
			$order->save();
		}
	}
}
