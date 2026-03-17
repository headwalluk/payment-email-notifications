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
	 * The plugin file path.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private string $plugin_file;

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
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $plugin_file The main plugin file path.
	 */
	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Register all hooks and initialise components.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function run(): void {
		$this->status_tracker = new Status_Tracker();
		$this->status_tracker->register_hooks();

		$this->email_definitions = new Email_Definitions();
		$this->token_engine      = new Token_Engine();

		$this->email_sender = new Email_Sender(
			$this->email_definitions,
			$this->status_tracker,
			$this->token_engine
		);
		$this->email_sender->register_hooks();

		$this->settings = new Settings( $this->email_definitions );
		$this->settings->register_hooks();

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Get the status tracker instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Status_Tracker|null
	 */
	public function get_status_tracker(): ?Status_Tracker {
		return $this->status_tracker;
	}

	/**
	 * Get the email definitions instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Email_Definitions|null
	 */
	public function get_email_definitions(): ?Email_Definitions {
		return $this->email_definitions;
	}

	/**
	 * Get the plugin file path.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_plugin_file(): string {
		return $this->plugin_file;
	}

	/**
	 * Get the plugin directory path.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_plugin_dir(): string {
		return plugin_dir_path( $this->plugin_file );
	}

	/**
	 * Get the plugin directory URL.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_plugin_url(): string {
		return plugin_dir_url( $this->plugin_file );
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
			__( 'Payment Email Notifications', 'payment-email-notifications' ),
			__( 'Payment Emails', 'payment-email-notifications' ),
			CAPABILITY,
			ADMIN_PAGE_SLUG,
			array( $this->settings, 'render_page' )
		);
	}
}
