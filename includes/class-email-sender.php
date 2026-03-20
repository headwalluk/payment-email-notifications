<?php
/**
 * Cron-based email sending engine.
 *
 * Evaluates all enabled email definitions against matching
 * orders and sends emails when conditions are met.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Email_Sender class.
 *
 * Registers the cron schedule and processes email sending
 * on each cron run.
 *
 * @since 0.1.0
 */
class Email_Sender {

	/**
	 * The email definitions instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Email_Definitions
	 */
	private Email_Definitions $definitions;

	/**
	 * The status tracker instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Status_Tracker
	 */
	private Status_Tracker $status_tracker;

	/**
	 * The token engine instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Token_Engine
	 */
	private Token_Engine $token_engine;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Email_Definitions $definitions    The email definitions instance.
	 * @param Status_Tracker    $status_tracker The status tracker instance.
	 * @param Token_Engine      $token_engine   The token engine instance.
	 */
	public function __construct(
		Email_Definitions $definitions,
		Status_Tracker $status_tracker,
		Token_Engine $token_engine
	) {
		$this->definitions    = $definitions;
		$this->status_tracker = $status_tracker;
		$this->token_engine   = $token_engine;
	}

	/**
	 * Schedule the cron event on plugin activation.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function schedule_cron(): void {
		if ( ! wp_next_scheduled( CRON_HOOK ) ) {
			wp_schedule_event( time(), 'hourly', CRON_HOOK );
		}
	}

	/**
	 * Unschedule the cron event on plugin deactivation.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function unschedule_cron(): void {
		$timestamp = wp_next_scheduled( CRON_HOOK );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, CRON_HOOK );
		}
	}

	/**
	 * Process all enabled email definitions.
	 *
	 * This is the main cron handler. For each enabled definition,
	 * it queries orders at the target status and sends emails
	 * where the days-at-status threshold is met and the email
	 * hasn't already been sent.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function process_emails(): void {
		if ( ! pen_is_within_send_schedule() ) {
			return;
		}

		$enabled_definitions = $this->definitions->get_enabled();

		foreach ( $enabled_definitions as $definition_id => $definition ) {
			$this->process_definition( $definition_id, $definition );
		}
	}

	/**
	 * Process a single email definition against all matching orders.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $definition_id The definition ID.
	 * @param array<string, mixed> $definition    The definition data.
	 *
	 * @return void
	 */
	private function process_definition( string $definition_id, array $definition ): void {
		$target_status = $definition['status'];
		$days          = isset( $definition['days'] ) ? absint( $definition['days'] ) : 0;

		// Strip 'wc-' prefix for wc_get_orders status query.
		$status_for_query = $target_status;
		if ( str_starts_with( $status_for_query, 'wc-' ) ) {
			$status_for_query = substr( $status_for_query, 3 );
		}

		$orders = wc_get_orders(
			array(
				'status' => $status_for_query,
				'limit'  => -1,
			)
		);

		foreach ( $orders as $order ) {
			$this->maybe_send_for_order( $definition_id, $definition, $order, $days );
		}
	}

	/**
	 * Check conditions and send email for a single order if appropriate.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $definition_id The definition ID.
	 * @param array<string, mixed> $definition    The definition data.
	 * @param \WC_Order            $order         The WooCommerce order.
	 * @param int                  $days          The days-at-status threshold.
	 *
	 * @return void
	 */
	private function maybe_send_for_order( string $definition_id, array $definition, \WC_Order $order, int $days ): void {
		// Skip if already sent for this status period.
		if ( $this->status_tracker->has_email_been_sent( $order, $definition_id ) ) {
			return;
		}

		// Skip if we can't determine days at status (pre-existing order without tracking).
		$days_at_status = $this->status_tracker->get_days_at_current_status( $order );

		if ( null === $days_at_status ) {
			return;
		}

		// Skip if threshold not yet met.
		if ( $days_at_status < $days ) {
			return;
		}

		/**
		 * Filter whether this email should be sent.
		 *
		 * @since 0.1.0
		 *
		 * @param bool                  $should_send   Whether to send the email.
		 * @param string                $definition_id The email definition ID.
		 * @param array<string, mixed>  $definition    The email definition data.
		 * @param \WC_Order             $order         The WooCommerce order.
		 */
		$should_send = apply_filters( 'pen_should_send_email', true, $definition_id, $definition, $order );

		if ( ! $should_send ) {
			return;
		}

		$this->send_email( $definition_id, $definition, $order );
	}

	/**
	 * Send the email and record it.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $definition_id The definition ID.
	 * @param array<string, mixed> $definition    The definition data.
	 * @param \WC_Order            $order         The WooCommerce order.
	 *
	 * @return void
	 */
	private function send_email( string $definition_id, array $definition, \WC_Order $order ): void {
		$subject = isset( $definition['subject'] ) ? $definition['subject'] : '';
		$body    = isset( $definition['body'] ) ? $definition['body'] : '';

		// Convert plain-text line breaks before token substitution so that
		// HTML tokens (e.g. {{order.items}}) are not mangled by nl2br().
		$body = nl2br( $body );

		// Render tokens.
		$subject   = $this->token_engine->render( $subject, $order );
		$body_html = $this->token_engine->render( $body, $order );

		$recipient = $this->resolve_recipient( $definition, $order );

		/**
		 * Filter the rendered email recipient.
		 *
		 * @since 0.1.0
		 *
		 * @param string                $recipient     The email recipient.
		 * @param string                $definition_id The email definition ID.
		 * @param array<string, mixed>  $definition    The email definition data.
		 * @param \WC_Order             $order         The WooCommerce order.
		 */
		$recipient = apply_filters( 'pen_email_recipient', $recipient, $definition_id, $definition, $order );

		/**
		 * Filter the rendered email content before sending.
		 *
		 * @since 0.1.0
		 *
		 * @param string                $body_html     The rendered HTML email body.
		 * @param string                $definition_id The email definition ID.
		 * @param array<string, mixed>  $definition    The email definition data.
		 * @param \WC_Order             $order         The WooCommerce order.
		 */
		$body_html = apply_filters( 'pen_email_content', $body_html, $definition_id, $definition, $order );

		// Wrap in WooCommerce email template.
		$wrapped_body = $this->wrap_in_wc_email_template( $body_html, $subject );

		// Send via WooCommerce mailer.
		$mailer = WC()->mailer();
		$result = $mailer->send( $recipient, $subject, $wrapped_body );

		if ( $result ) {
			// Record that this email has been sent.
			$this->status_tracker->record_email_sent( $order, $definition_id );

			// Add order note.
			$label = isset( $definition['label'] ) ? $definition['label'] : $definition_id;
			$order->add_order_note(
				sprintf(
					/* translators: 1: email definition label, 2: recipient email address. */
					__( 'Payment Email Notifications: "%1$s" sent to %2$s.', 'payment-email-notifications' ),
					$label,
					$recipient
				)
			);
		} else {
			// Log failure for troubleshooting.
			$label = isset( $definition['label'] ) ? $definition['label'] : $definition_id;
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional production error logging.
			error_log(
				sprintf(
					'Payment Email Notifications: Failed to send "%s" for order #%d to %s.',
					$label,
					$order->get_id(),
					$recipient
				)
			);
		}
	}

	/**
	 * Resolve the recipient email address for a definition and order.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $definition The definition data.
	 * @param \WC_Order            $order      The WooCommerce order.
	 *
	 * @return string The resolved email address.
	 */
	private function resolve_recipient( array $definition, \WC_Order $order ): string {
		$recipient_type = isset( $definition['recipient'] ) ? $definition['recipient'] : RECIPIENT_CUSTOMER;
		$result         = $order->get_billing_email();

		if ( RECIPIENT_ADMIN === $recipient_type ) {
			$result = get_option( 'admin_email' );
		}

		if ( RECIPIENT_CUSTOM === $recipient_type && ! empty( $definition['recipient_custom'] ) ) {
			$result = $definition['recipient_custom'];
		}

		return $result;
	}

	/**
	 * Send a test email for a definition using a given order.
	 *
	 * Does not record the email as sent or add order notes.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $definition The definition data.
	 * @param \WC_Order            $order      The WooCommerce order.
	 * @param string               $recipient  The test recipient email address.
	 *
	 * @return bool True on success.
	 */
	public function send_test_email( array $definition, \WC_Order $order, string $recipient ): bool {
		$subject = isset( $definition['subject'] ) ? $definition['subject'] : '';
		$body    = isset( $definition['body'] ) ? $definition['body'] : '';

		// Convert plain-text line breaks before token substitution so that
		// HTML tokens (e.g. {{order.items}}) are not mangled by nl2br().
		$body = nl2br( $body );

		$subject   = $this->token_engine->render( $subject, $order );
		$body_html = $this->token_engine->render( $body, $order );

		/** This filter is documented in includes/class-email-sender.php */
		$body_html = apply_filters( 'pen_email_content', $body_html, 'test', $definition, $order );

		$wrapped_body = $this->wrap_in_wc_email_template( $body_html, $subject );

		$mailer = WC()->mailer();
		$result = $mailer->send( $recipient, $subject, $wrapped_body );

		return (bool) $result;
	}

	/**
	 * Wrap email content in WooCommerce email header and footer.
	 *
	 * @since 0.1.0
	 *
	 * @param string $content The email body HTML.
	 * @param string $heading The email heading.
	 *
	 * @return string The fully wrapped email HTML.
	 */
	private function wrap_in_wc_email_template( string $content, string $heading ): string {
		ob_start();
		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $heading ) );
		echo wp_kses_post( $content );
		wc_get_template( 'emails/email-footer.php' );
		$result = ob_get_clean();

		return $result;
	}
}
