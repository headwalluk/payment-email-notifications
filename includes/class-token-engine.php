<?php
/**
 * Template token substitution engine.
 *
 * Replaces {{token.name}} placeholders in email subjects
 * and bodies with actual values from orders and customers.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Token_Engine class.
 *
 * Builds a token map from a WooCommerce order and replaces
 * tokens in template strings.
 *
 * @since 0.1.0
 */
class Token_Engine {

	/**
	 * Replace all tokens in a template string.
	 *
	 * @since 0.1.0
	 *
	 * @param string    $template The template string containing {{tokens}}.
	 * @param \WC_Order $order    The WooCommerce order for context.
	 *
	 * @return string The rendered string with tokens replaced.
	 */
	public function render( string $template, \WC_Order $order ): string {
		$tokens = $this->build_token_map( $order );

		/**
		 * Filter the available tokens for email rendering.
		 *
		 * @since 0.1.0
		 *
		 * @param array<string, string> $tokens   Token name => value map.
		 * @param \WC_Order             $order    The WooCommerce order.
		 * @param string                $template The original template string.
		 */
		$tokens = apply_filters( 'pen_email_tokens', $tokens, $order, $template );

		$result = $template;

		foreach ( $tokens as $token_name => $token_value ) {
			$result = str_replace( '{{' . $token_name . '}}', $token_value, $result );
		}

		return $result;
	}

	/**
	 * Build the token map from a WooCommerce order.
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order The WooCommerce order.
	 *
	 * @return array<string, string> Token name => value map.
	 */
	private function build_token_map( \WC_Order $order ): array {
		$tokens = [];

		// Customer tokens.
		$tokens['customer.first_name'] = $order->get_billing_first_name();
		$tokens['customer.last_name']  = $order->get_billing_last_name();
		$tokens['customer.full_name']  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$tokens['customer.email']      = $order->get_billing_email();
		$tokens['customer.company']    = $order->get_billing_company();

		// Order tokens.
		$tokens['order.id']    = (string) $order->get_order_number();
		$tokens['order.total'] = $order->get_formatted_order_total();
		$tokens['order.date']  = wc_format_datetime( $order->get_date_created() );
		$tokens['order.items'] = $this->render_order_items_table( $order );

		// Payment tokens.
		$tokens['order.payment_method'] = $order->get_payment_method_title();

		// Site tokens.
		$tokens['site.name'] = get_bloginfo( 'name' );
		$tokens['site.url']  = home_url();

		return $tokens;
	}

	/**
	 * Render a WooCommerce-styled order items table.
	 *
	 * Uses the WooCommerce email template for consistent styling.
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order The WooCommerce order.
	 *
	 * @return string The rendered HTML table.
	 */
	private function render_order_items_table( \WC_Order $order ): string {
		ob_start();

		wc_get_template(
			'emails/email-order-items.php',
			[
				'order'               => $order,
				'items'               => $order->get_items(),
				'show_sku'            => false,
				'show_image'          => false,
				'image_size'          => [],
				'plain_text'          => false,
				'sent_to_admin'       => false,
				'show_download_links' => false,
			]
		);

		$items_html = ob_get_clean();

		$result = sprintf(
			'<table cellspacing="0" cellpadding="6" border="1" style="width:100%%;border-collapse:collapse;border:1px solid #e5e5e5;"><thead><tr><th style="text-align:left;border:1px solid #e5e5e5;padding:12px;">%s</th><th style="text-align:left;border:1px solid #e5e5e5;padding:12px;">%s</th><th style="text-align:left;border:1px solid #e5e5e5;padding:12px;">%s</th></tr></thead><tbody>%s</tbody></table>',
			esc_html__( 'Product', 'payment-email-notifications' ),
			esc_html__( 'Quantity', 'payment-email-notifications' ),
			esc_html__( 'Price', 'payment-email-notifications' ),
			$items_html
		);

		return $result;
	}

	/**
	 * Get a list of all available tokens with descriptions.
	 *
	 * Used to display a reference list in the admin UI.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, string> Token name => description.
	 */
	public function get_token_reference(): array {
		return [
			'customer.first_name'  => __( 'Customer first name', 'payment-email-notifications' ),
			'customer.last_name'   => __( 'Customer last name', 'payment-email-notifications' ),
			'customer.full_name'   => __( 'Customer full name', 'payment-email-notifications' ),
			'customer.email'       => __( 'Customer email address', 'payment-email-notifications' ),
			'customer.company'     => __( 'Customer company name', 'payment-email-notifications' ),
			'order.id'             => __( 'Order number', 'payment-email-notifications' ),
			'order.total'          => __( 'Formatted order total', 'payment-email-notifications' ),
			'order.date'           => __( 'Order date', 'payment-email-notifications' ),
			'order.items'          => __( 'Order items table (HTML)', 'payment-email-notifications' ),
			'order.payment_method' => __( 'Payment method name', 'payment-email-notifications' ),
			'site.name'            => __( 'Site name', 'payment-email-notifications' ),
			'site.url'             => __( 'Site URL', 'payment-email-notifications' ),
		];
	}
}
