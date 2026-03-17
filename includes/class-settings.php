<?php
/**
 * Admin settings page.
 *
 * Handles the admin UI for managing email definitions
 * under the WooCommerce menu.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Settings class.
 *
 * Provides the admin list view and add/edit form for
 * email definitions.
 *
 * @since 0.1.0
 */
class Settings {

	/**
	 * The email definitions instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Email_Definitions
	 */
	private Email_Definitions $definitions;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Email_Definitions $definitions The email definitions instance.
	 */
	public function __construct( Email_Definitions $definitions ) {
		$this->definitions = $definitions;
	}

	/**
	 * Enqueue admin assets on our settings page only.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'woocommerce_page_' . ADMIN_PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'pen-admin',
			PEN_PLUGIN_URL . 'assets/admin/pen-admin.css',
			[],
			PEN_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'pen-admin',
			PEN_PLUGIN_URL . 'assets/admin/pen-admin.js',
			[ 'jquery' ],
			PEN_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'pen-admin',
			'penAdmin',
			[
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'testNonce'     => wp_create_nonce( 'pen_test_email' ),
				'testAction'    => AJAX_SEND_TEST_EMAIL,
				'promptMessage' => __( 'Enter the email address to send the test email to:', 'payment-email-notifications' ),
				'sendingText'   => __( 'Sending…', 'payment-email-notifications' ),
				'testText'      => __( 'Test', 'payment-email-notifications' ),
			]
		);
	}

	/**
	 * Handle form submissions and render the appropriate view.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render_page(): void {
		$this->handle_form_submission();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- No data modification, just routing.
		$tab           = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'emails';
		$action        = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
		$definition_id = isset( $_GET['definition'] ) ? sanitize_text_field( wp_unslash( $_GET['definition'] ) ) : '';
		// phpcs:enable

		if ( 'settings' === $tab ) {
			$this->render_settings_view();
		} elseif ( 'edit' === $action || 'add' === $action ) {
			$this->render_edit_view( $definition_id );
		} else {
			$this->render_list_view();
		}
	}

	/**
	 * Handle form submissions for save and delete actions.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function handle_form_submission(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in handle_save() and handle_delete().
		if ( ! isset( $_POST['pen_action'] ) ) {
			return;
		}

		$action = sanitize_text_field( wp_unslash( $_POST['pen_action'] ) );
		// phpcs:enable

		if ( 'save' === $action ) {
			$this->handle_save();
		}

		if ( 'delete' === $action ) {
			$this->handle_delete();
		}

		if ( 'save_settings' === $action ) {
			$this->handle_save_settings();
		}
	}

	/**
	 * Handle saving an email definition.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function handle_save(): void {
		if ( ! isset( $_POST['pen_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pen_nonce'] ) ), 'pen_save_definition' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'payment-email-notifications' ) );
		}

		if ( ! current_user_can( CAPABILITY ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'payment-email-notifications' ) );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$definition_id = isset( $_POST['pen_definition_id'] ) ? sanitize_text_field( wp_unslash( $_POST['pen_definition_id'] ) ) : '';
		$label         = isset( $_POST['pen_label'] ) ? sanitize_text_field( wp_unslash( $_POST['pen_label'] ) ) : '';
		$subject       = isset( $_POST['pen_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['pen_subject'] ) ) : '';
		$body          = isset( $_POST['pen_body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pen_body'] ) ) : '';
		$status        = isset( $_POST['pen_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pen_status'] ) ) : '';
		$days             = isset( $_POST['pen_days'] ) ? absint( $_POST['pen_days'] ) : 0;
		$enabled          = isset( $_POST['pen_enabled'] );
		$recipient        = isset( $_POST['pen_recipient'] ) ? sanitize_text_field( wp_unslash( $_POST['pen_recipient'] ) ) : RECIPIENT_CUSTOMER;
		$recipient_custom = isset( $_POST['pen_recipient_custom'] ) ? sanitize_email( wp_unslash( $_POST['pen_recipient_custom'] ) ) : '';
		// phpcs:enable

		// Validate recipient type.
		$valid_recipients = [ RECIPIENT_CUSTOMER, RECIPIENT_ADMIN, RECIPIENT_CUSTOM ];
		if ( ! in_array( $recipient, $valid_recipients, true ) ) {
			$recipient = RECIPIENT_CUSTOMER;
		}

		if ( empty( $definition_id ) ) {
			$definition_id = $this->definitions->generate_id( $label );
		}

		$definition = [
			'label'            => $label,
			'subject'          => $subject,
			'body'             => $body,
			'status'           => $status,
			'days'             => $days,
			'enabled'          => $enabled,
			'recipient'        => $recipient,
			'recipient_custom' => $recipient_custom,
		];

		$this->definitions->save( $definition_id, $definition );

		wp_safe_redirect(
			add_query_arg(
				[
					'page'    => ADMIN_PAGE_SLUG,
					'updated' => '1',
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Handle deleting an email definition.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function handle_delete(): void {
		if ( ! isset( $_POST['pen_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pen_nonce'] ) ), 'pen_delete_definition' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'payment-email-notifications' ) );
		}

		if ( ! current_user_can( CAPABILITY ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'payment-email-notifications' ) );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$definition_id = isset( $_POST['pen_definition_id'] ) ? sanitize_text_field( wp_unslash( $_POST['pen_definition_id'] ) ) : '';
		// phpcs:enable

		if ( ! empty( $definition_id ) ) {
			$this->definitions->delete( $definition_id );
		}

		wp_safe_redirect(
			add_query_arg(
				[
					'page'    => ADMIN_PAGE_SLUG,
					'deleted' => '1',
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Handle saving settings.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	private function handle_save_settings(): void {
		if ( ! isset( $_POST['pen_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pen_nonce'] ) ), 'pen_save_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'payment-email-notifications' ) );
		}

		if ( ! current_user_can( CAPABILITY ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'payment-email-notifications' ) );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$order_meta_keys    = isset( $_POST['pen_order_meta_keys'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pen_order_meta_keys'] ) ) : '';
		$customer_meta_keys = isset( $_POST['pen_customer_meta_keys'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pen_customer_meta_keys'] ) ) : '';
		// phpcs:enable

		update_option( OPT_ORDER_META_KEYS, $order_meta_keys );
		update_option( OPT_CUSTOMER_META_KEYS, $customer_meta_keys );

		wp_safe_redirect(
			add_query_arg(
				[
					'page'    => ADMIN_PAGE_SLUG,
					'tab'     => 'settings',
					'updated' => '1',
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Handle AJAX test email request.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function handle_test_email(): void {
		check_ajax_referer( 'pen_test_email', 'nonce' );

		if ( ! current_user_can( CAPABILITY ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'payment-email-notifications' ) ] );
		}

		$definition_id = isset( $_POST['definition_id'] ) ? sanitize_text_field( wp_unslash( $_POST['definition_id'] ) ) : '';
		$recipient     = isset( $_POST['recipient'] ) ? sanitize_email( wp_unslash( $_POST['recipient'] ) ) : '';

		if ( empty( $definition_id ) || empty( $recipient ) ) {
			wp_send_json_error( [ 'message' => __( 'Definition ID and recipient email are required.', 'payment-email-notifications' ) ] );
		}

		$definition = $this->definitions->get( $definition_id );

		if ( null === $definition ) {
			wp_send_json_error( [ 'message' => __( 'Email definition not found.', 'payment-email-notifications' ) ] );
		}

		// Get the most recent order.
		$orders = wc_get_orders(
			[
				'limit'   => 1,
				'orderby' => 'date',
				'order'   => 'DESC',
			]
		);

		if ( empty( $orders ) ) {
			wp_send_json_error( [ 'message' => __( 'No orders found in the system to use as sample data.', 'payment-email-notifications' ) ] );
		}

		$order        = $orders[0];
		$email_sender = pen_get_plugin()->get_email_sender();
		$result       = $email_sender->send_test_email( $definition, $order, $recipient );

		if ( $result ) {
			wp_send_json_success(
				[
					'message' => sprintf(
						/* translators: 1: recipient email address, 2: order ID. */
						__( 'Test email sent to %1$s using order #%2$d.', 'payment-email-notifications' ),
						$recipient,
						$order->get_id()
					),
				]
			);
		}

		wp_send_json_error( [ 'message' => __( 'Failed to send test email.', 'payment-email-notifications' ) ] );
	}

	/**
	 * Render the list view of all email definitions.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function render_list_view(): void {
		$definitions = $this->definitions->get_all();
		$plugin_dir  = PEN_PLUGIN_DIR;

		include $plugin_dir . 'admin-templates/list-definitions.php';
	}

	/**
	 * Render the add/edit form for a single email definition.
	 *
	 * @since 0.1.0
	 *
	 * @param string $definition_id The definition ID to edit, or empty for new.
	 *
	 * @return void
	 */
	private function render_edit_view( string $definition_id ): void {
		$definition = null;
		$is_new     = empty( $definition_id );

		if ( ! $is_new ) {
			$definition = $this->definitions->get( $definition_id );
		}

		if ( null === $definition ) {
			$definition    = $this->definitions->get_defaults();
			$definition_id = '';
			$is_new        = true;
		}

		$statuses   = $this->get_order_statuses();
		$plugin_dir = PEN_PLUGIN_DIR;

		include $plugin_dir . 'admin-templates/edit-definition.php';
	}

	/**
	 * Render the settings view.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	private function render_settings_view(): void {
		$order_meta_keys    = get_option( OPT_ORDER_META_KEYS, '' );
		$customer_meta_keys = get_option( OPT_CUSTOMER_META_KEYS, '' );
		$plugin_dir         = PEN_PLUGIN_DIR;

		include $plugin_dir . 'admin-templates/settings.php';
	}

	/**
	 * Get all registered WooCommerce order statuses.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, string> Status slug => label.
	 */
	private function get_order_statuses(): array {
		$statuses = [];

		if ( function_exists( 'wc_get_order_statuses' ) ) {
			$statuses = wc_get_order_statuses();
		}

		return $statuses;
	}
}
