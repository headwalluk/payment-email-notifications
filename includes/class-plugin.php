<?php
/**
 * Main plugin orchestrator.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Plugin class.
 *
 * Registers all hooks and initialises plugin components.
 *
 * @since 0.1.0
 */
class Plugin {

	/**
	 * The status tracker instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Status_Tracker|null
	 */
	private ?Status_Tracker $status_tracker = null;

	/**
	 * The email definitions instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Email_Definitions|null
	 */
	private ?Email_Definitions $email_definitions = null;

	/**
	 * The token engine instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Token_Engine|null
	 */
	private ?Token_Engine $token_engine = null;

	/**
	 * The email sender instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Email_Sender|null
	 */
	private ?Email_Sender $email_sender = null;

	/**
	 * The settings instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Settings|null
	 */
	private ?Settings $settings = null;

	/**
	 * Register all hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'woocommerce_order_status_changed', array( $this, 'on_order_status_changed' ), 10, 1 );
		add_action( CRON_HOOK, array( $this, 'on_cron_process_emails' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'on_admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_' . AJAX_SEND_TEST_EMAIL, array( $this, 'on_ajax_send_test_email' ) );
	}

	/**
	 * Get the status tracker instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Status_Tracker
	 */
	public function get_status_tracker(): Status_Tracker {
		if ( is_null( $this->status_tracker ) ) {
			$this->status_tracker = new Status_Tracker();
		}

		return $this->status_tracker;
	}

	/**
	 * Get the email definitions instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Email_Definitions
	 */
	public function get_email_definitions(): Email_Definitions {
		if ( is_null( $this->email_definitions ) ) {
			$this->email_definitions = new Email_Definitions();
		}

		return $this->email_definitions;
	}

	/**
	 * Get the token engine instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Token_Engine
	 */
	public function get_token_engine(): Token_Engine {
		if ( is_null( $this->token_engine ) ) {
			$this->token_engine = new Token_Engine();
		}

		return $this->token_engine;
	}

	/**
	 * Get the email sender instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Email_Sender
	 */
	public function get_email_sender(): Email_Sender {
		if ( is_null( $this->email_sender ) ) {
			$this->email_sender = new Email_Sender(
				$this->get_email_definitions(),
				$this->get_status_tracker(),
				$this->get_token_engine()
			);
		}

		return $this->email_sender;
	}

	/**
	 * Get the settings instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Settings
	 */
	public function get_settings(): Settings {
		if ( is_null( $this->settings ) ) {
			$this->settings = new Settings( $this->get_email_definitions() );
		}

		return $this->settings;
	}

	/**
	 * Handle order status change — delegates to Status_Tracker.
	 *
	 * @since 0.1.0
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return void
	 */
	public function on_order_status_changed( int $order_id ): void {
		$this->get_status_tracker()->on_status_changed( $order_id );
	}

	/**
	 * Handle cron email processing — delegates to Email_Sender.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function on_cron_process_emails(): void {
		$this->get_email_sender()->process_emails();
	}

	/**
	 * Enqueue admin assets — delegates to Settings.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function on_admin_enqueue_scripts( string $hook_suffix ): void {
		$this->get_settings()->enqueue_assets( $hook_suffix );
	}

	/**
	 * Handle AJAX test email request — delegates to Settings.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function on_ajax_send_test_email(): void {
		$this->get_settings()->handle_test_email();
	}

	/**
	 * Add the admin menu item under WooCommerce.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Order Status Emails', 'payment-email-notifications' ),
			__( 'Order Status Emails', 'payment-email-notifications' ),
			CAPABILITY,
			ADMIN_PAGE_SLUG,
			array( $this->get_settings(), 'render_page' )
		);
	}
}
