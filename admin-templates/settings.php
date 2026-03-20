<?php
/**
 * Admin template: settings tab.
 *
 * @since 0.2.0
 *
 * @package Payment_Email_Notifications
 *
 * @var string $order_meta_keys     Newline-separated order meta keys.
 * @var string $customer_meta_keys  Newline-separated customer meta keys.
 * @var bool   $send_window_enabled Whether the time-of-day window is enabled.
 * @var string $send_window_start   Window start time (H:i).
 * @var string $send_window_end     Window end time (H:i).
 * @var bool   $send_days_enabled   Whether day-of-week restriction is enabled.
 * @var array  $send_days           Allowed days of the week.
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

$pen_current_tab = 'settings';

printf( '<div class="wrap">' );

printf( '<h1>%s</h1>', esc_html__( 'Order Status Emails', 'payment-email-notifications' ) );

require PEN_PLUGIN_DIR . 'admin-templates/tab-navigation.php';

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
			array( 'page' => ADMIN_PAGE_SLUG ),
			admin_url( 'admin.php' )
		)
	)
);

wp_nonce_field( 'pen_save_settings', 'pen_nonce' );

printf( '<input type="hidden" name="pen_action" value="save_settings">' );

// --- Send Schedule section ---

printf( '<h2>%s</h2>', esc_html__( 'Send Schedule', 'payment-email-notifications' ) );

printf(
	'<p class="description">%s</p>',
	esc_html__( 'Use these options to restrict when emails are sent. Emails that fall outside the schedule will simply be sent on the next eligible cron run.', 'payment-email-notifications' )
);

printf( '<table class="form-table">' );

// Time-of-day window — checkbox.
printf( '<tr>' );
printf(
	'<th>%s</th>',
	esc_html__( 'Time-of-Day Window', 'payment-email-notifications' )
);
printf(
	'<td><label><input type="checkbox" name="pen_send_window_enabled" id="pen_send_window_enabled" value="1" %s> %s</label></td>',
	checked( $send_window_enabled, true, false ),
	esc_html__( 'Only send emails within a specific time window', 'payment-email-notifications' )
);
printf( '</tr>' );

// Time-of-day window — start/end times.
printf( '<tr class="pen-send-window-times" %s>', $send_window_enabled ? '' : 'style="display:none;"' );
printf(
	'<th>%s</th>',
	esc_html__( 'Window Times', 'payment-email-notifications' )
);
printf(
	'<td><input type="time" name="pen_send_window_start" id="pen_send_window_start" value="%s"> %s <input type="time" name="pen_send_window_end" id="pen_send_window_end" value="%s">',
	esc_attr( $send_window_start ),
	esc_html__( 'to', 'payment-email-notifications' ),
	esc_attr( $send_window_end )
);
printf(
	'<p class="description">%s</p></td>',
	sprintf(
		/* translators: %s: server timezone name, e.g. "Europe/London". */
		esc_html__( 'Server timezone: %s. Emails due outside this window will be sent on the next cron run within the window.', 'payment-email-notifications' ),
		esc_html( wp_timezone_string() )
	)
);
printf( '</tr>' );

// Day-of-week — checkbox.
printf( '<tr>' );
printf(
	'<th>%s</th>',
	esc_html__( 'Allowed Days', 'payment-email-notifications' )
);
printf(
	'<td><label><input type="checkbox" name="pen_send_days_enabled" id="pen_send_days_enabled" value="1" %s> %s</label></td>',
	checked( $send_days_enabled, true, false ),
	esc_html__( 'Only send emails on specific days of the week', 'payment-email-notifications' )
);
printf( '</tr>' );

// Day-of-week — day checkboxes.
$pen_day_labels = array(
	'mon' => __( 'Mon', 'payment-email-notifications' ),
	'tue' => __( 'Tue', 'payment-email-notifications' ),
	'wed' => __( 'Wed', 'payment-email-notifications' ),
	'thu' => __( 'Thu', 'payment-email-notifications' ),
	'fri' => __( 'Fri', 'payment-email-notifications' ),
	'sat' => __( 'Sat', 'payment-email-notifications' ),
	'sun' => __( 'Sun', 'payment-email-notifications' ),
);

printf( '<tr class="pen-send-days-options" %s>', $send_days_enabled ? '' : 'style="display:none;"' );
printf(
	'<th>%s</th>',
	esc_html__( 'Send On', 'payment-email-notifications' )
);
printf( '<td><fieldset class="pen-day-checkboxes">' );

foreach ( $pen_day_labels as $pen_day_value => $pen_day_label ) {
	printf(
		'<label><input type="checkbox" name="pen_send_days[]" value="%s" %s> %s</label>',
		esc_attr( $pen_day_value ),
		checked( in_array( $pen_day_value, $send_days, true ), true, false ),
		esc_html( $pen_day_label )
	);
}

printf( '</fieldset>' );
printf(
	'<p class="description">%s</p></td>',
	esc_html__( 'Emails due on excluded days will be sent on the next allowed day.', 'payment-email-notifications' )
);
printf( '</tr>' );

printf( '</table>' );

// --- Custom Meta Tokens section ---

printf( '<h2>%s</h2>', esc_html__( 'Custom Meta Tokens', 'payment-email-notifications' ) );

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
