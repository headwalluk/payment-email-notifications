<?php
/**
 * Admin template: add/edit email definition.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 *
 * @var array<string, mixed>  $definition    The definition data.
 * @var string                $definition_id The definition ID (empty for new).
 * @var bool                  $is_new        Whether this is a new definition.
 * @var array<string, string> $statuses      Available order statuses.
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

$page_title = $is_new
	? __( 'Add Email Definition', 'payment-email-notifications' )
	: __( 'Edit Email Definition', 'payment-email-notifications' );

$enabled = isset( $definition['enabled'] )
	? (bool) filter_var( $definition['enabled'], FILTER_VALIDATE_BOOLEAN )
	: false;

printf( '<div class="wrap">' );

printf( '<h1>%s</h1>', esc_html( $page_title ) );

printf(
	'<form method="post" action="%s">',
	esc_url(
		add_query_arg(
			[ 'page' => ADMIN_PAGE_SLUG ],
			admin_url( 'admin.php' )
		)
	)
);

wp_nonce_field( 'pen_save_definition', 'pen_nonce' );

printf( '<input type="hidden" name="pen_action" value="save">' );
printf( '<input type="hidden" name="pen_definition_id" value="%s">', esc_attr( $definition_id ) );

printf( '<table class="form-table">' );

// Label.
printf( '<tr>' );
printf(
	'<th><label for="pen_label">%s</label></th>',
	esc_html__( 'Label', 'payment-email-notifications' )
);
printf(
	'<td><input type="text" name="pen_label" id="pen_label" value="%s" class="regular-text" required><p class="description">%s</p></td>',
	esc_attr( $definition['label'] ),
	esc_html__( 'A name for this email definition (for your reference only).', 'payment-email-notifications' )
);
printf( '</tr>' );

// Order status.
printf( '<tr>' );
printf(
	'<th><label for="pen_status">%s</label></th>',
	esc_html__( 'Order Status', 'payment-email-notifications' )
);
printf( '<td><select name="pen_status" id="pen_status" required>' );
printf( '<option value="">%s</option>', esc_html__( '— Select —', 'payment-email-notifications' ) );

foreach ( $statuses as $status_slug => $status_label ) {
	printf(
		'<option value="%s" %s>%s</option>',
		esc_attr( $status_slug ),
		selected( $definition['status'], $status_slug, false ),
		esc_html( $status_label )
	);
}

printf( '</select>' );
printf(
	'<p class="description">%s</p></td>',
	esc_html__( 'The order status that triggers this email.', 'payment-email-notifications' )
);
printf( '</tr>' );

// Days at status.
printf( '<tr>' );
printf(
	'<th><label for="pen_days">%s</label></th>',
	esc_html__( 'Days at Status', 'payment-email-notifications' )
);
printf(
	'<td><input type="number" name="pen_days" id="pen_days" value="%d" min="0" step="1" class="small-text"><p class="description">%s</p></td>',
	absint( $definition['days'] ),
	esc_html__( 'Send this email when an order has been at the target status for this many days.', 'payment-email-notifications' )
);
printf( '</tr>' );

// Subject.
printf( '<tr>' );
printf(
	'<th><label for="pen_subject">%s</label></th>',
	esc_html__( 'Email Subject', 'payment-email-notifications' )
);
printf(
	'<td><input type="text" name="pen_subject" id="pen_subject" value="%s" class="large-text" required><p class="description">%s</p></td>',
	esc_attr( $definition['subject'] ),
	esc_html__( 'Supports tokens like {{customer.first_name}} and {{order.id}}.', 'payment-email-notifications' )
);
printf( '</tr>' );

// Body.
printf( '<tr>' );
printf(
	'<th><label for="pen_body">%s</label></th>',
	esc_html__( 'Email Body', 'payment-email-notifications' )
);
printf(
	'<td><textarea name="pen_body" id="pen_body" rows="12" class="large-text">%s</textarea><p class="description">%s</p></td>',
	esc_textarea( $definition['body'] ),
	esc_html__( 'Plain text with tokens. Line breaks will be preserved. Supports {{customer.first_name}}, {{order.id}}, {{order.total}}, {{order.items}}, etc.', 'payment-email-notifications' )
);
printf( '</tr>' );

// Enabled.
printf( '<tr>' );
printf(
	'<th>%s</th>',
	esc_html__( 'Enabled', 'payment-email-notifications' )
);
printf(
	'<td><label><input type="checkbox" name="pen_enabled" value="1" %s> %s</label></td>',
	checked( $enabled, true, false ),
	esc_html__( 'Enable this email definition', 'payment-email-notifications' )
);
printf( '</tr>' );

printf( '</table>' );

submit_button(
	$is_new
	? __( 'Add Email Definition', 'payment-email-notifications' )
	: __( 'Save Changes', 'payment-email-notifications' )
);

printf( '</form>' );

// Delete button for existing definitions.
if ( ! $is_new ) {
	printf( '<hr>' );
	printf(
		'<form method="post" action="%s" onsubmit="return confirm(\'%s\');">',
		esc_url(
			add_query_arg(
				[ 'page' => ADMIN_PAGE_SLUG ],
				admin_url( 'admin.php' )
			)
		),
		esc_js( __( 'Are you sure you want to delete this email definition?', 'payment-email-notifications' ) )
	);

	wp_nonce_field( 'pen_delete_definition', 'pen_nonce' );

	printf( '<input type="hidden" name="pen_action" value="delete">' );
	printf( '<input type="hidden" name="pen_definition_id" value="%s">', esc_attr( $definition_id ) );
	printf(
		'<button type="submit" class="button button-link-delete">%s</button>',
		esc_html__( 'Delete this email definition', 'payment-email-notifications' )
	);

	printf( '</form>' );
}

printf( '</div>' );
