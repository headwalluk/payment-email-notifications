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
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		printf(
			'<div class="wrap"><h1>%s</h1><p>%s</p></div>',
			esc_html__( 'Payment Email Notifications', 'payment-email-notifications' ),
			esc_html__( 'Email definitions will be managed here.', 'payment-email-notifications' )
		);
	}
}
