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
	 * Register hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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
			plugin_dir_url( pen_get_plugin()->get_plugin_file() ) . 'assets/admin/pen-admin.css',
			[],
			VERSION
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
		$action        = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
		$definition_id = isset( $_GET['definition'] ) ? sanitize_text_field( wp_unslash( $_GET['definition'] ) ) : '';
		// phpcs:enable

		if ( 'edit' === $action || 'add' === $action ) {
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
		$days          = isset( $_POST['pen_days'] ) ? absint( $_POST['pen_days'] ) : 0;
		$enabled       = isset( $_POST['pen_enabled'] );
		// phpcs:enable

		if ( empty( $definition_id ) ) {
			$definition_id = $this->definitions->generate_id( $label );
		}

		$definition = [
			'label'   => $label,
			'subject' => $subject,
			'body'    => $body,
			'status'  => $status,
			'days'    => $days,
			'enabled' => $enabled,
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
	 * Render the list view of all email definitions.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function render_list_view(): void {
		$definitions = $this->definitions->get_all();
		$plugin_dir  = pen_get_plugin()->get_plugin_dir();

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
		$plugin_dir = pen_get_plugin()->get_plugin_dir();

		include $plugin_dir . 'admin-templates/edit-definition.php';
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
