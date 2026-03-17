<?php
/**
 * Admin template: settings tab.
 *
 * @since 0.2.0
 *
 * @package Payment_Email_Notifications
 *
 * @var string $order_meta_keys    Newline-separated order meta keys.
 * @var string $customer_meta_keys Newline-separated customer meta keys.
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

$current_tab = 'settings';

printf( '<div class="wrap">' );

printf( '<h1>%s</h1>', esc_html__( 'Order Status Emails', 'payment-email-notifications' ) );

include PEN_PLUGIN_DIR . 'admin-templates/tab-navigation.php';

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display check.
if ( isset( $_GET['updated'] ) ) {
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html__( 'Settings saved.', 'payment-email-notifications' )
	);
}
// phpcs:enable

printf(
	'<form method="post" action="%s">',
	esc_url(
		add_query_arg(
			[ 'page' => ADMIN_PAGE_SLUG ],
			admin_url( 'admin.php' )
		)
	)
);

wp_nonce_field( 'pen_save_settings', 'pen_nonce' );

printf( '<input type="hidden" name="pen_action" value="save_settings">' );

printf( '<table class="form-table">' );

// Order meta keys.
printf( '<tr>' );
printf(
	'<th><label for="pen_order_meta_keys">%s</label></th>',
	esc_html__( 'Order Meta Keys', 'payment-email-notifications' )
);
printf(
	'<td><textarea name="pen_order_meta_keys" id="pen_order_meta_keys" rows="6" class="large-text">%s</textarea>',
	esc_textarea( $order_meta_keys )
);
printf(
	'<p class="description">%s</p></td>',
	esc_html__( 'One meta key per line. These become available as {{order.meta.KEY}} tokens in email templates.', 'payment-email-notifications' )
);
printf( '</tr>' );

// Customer meta keys.
printf( '<tr>' );
printf(
	'<th><label for="pen_customer_meta_keys">%s</label></th>',
	esc_html__( 'Customer Meta Keys', 'payment-email-notifications' )
);
printf(
	'<td><textarea name="pen_customer_meta_keys" id="pen_customer_meta_keys" rows="6" class="large-text">%s</textarea>',
	esc_textarea( $customer_meta_keys )
);
printf(
	'<p class="description">%s<br>%s</p></td>',
	esc_html__( 'One meta key per line. These become available as {{customer.meta.KEY}} tokens in email templates.', 'payment-email-notifications' ),
	esc_html__( 'Note: Customer meta tokens will return empty for guest orders.', 'payment-email-notifications' )
);
printf( '</tr>' );

printf( '</table>' );

submit_button( __( 'Save Settings', 'payment-email-notifications' ) );

printf( '</form>' );

printf( '</div>' );
