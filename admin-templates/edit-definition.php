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

$current_tab = 'emails';
include PEN_PLUGIN_DIR . 'admin-templates/tab-navigation.php';

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

// Recipient.
$recipient_type   = isset( $definition['recipient'] ) ? $definition['recipient'] : RECIPIENT_CUSTOMER;
$recipient_custom = isset( $definition['recipient_custom'] ) ? $definition['recipient_custom'] : '';

printf( '<tr>' );
printf(
	'<th><label for="pen_recipient">%s</label></th>',
	esc_html__( 'Recipient', 'payment-email-notifications' )
);
printf( '<td>' );
printf( '<select name="pen_recipient" id="pen_recipient">' );
printf(
	'<option value="%s" %s>%s</option>',
	esc_attr( RECIPIENT_CUSTOMER ),
	selected( $recipient_type, RECIPIENT_CUSTOMER, false ),
	esc_html__( 'Customer billing email', 'payment-email-notifications' )
);
printf(
	'<option value="%s" %s>%s</option>',
	esc_attr( RECIPIENT_ADMIN ),
	selected( $recipient_type, RECIPIENT_ADMIN, false ),
	esc_html__( 'Site admin email', 'payment-email-notifications' )
);
printf(
	'<option value="%s" %s>%s</option>',
	esc_attr( RECIPIENT_CUSTOM ),
	selected( $recipient_type, RECIPIENT_CUSTOM, false ),
	esc_html__( 'Custom email address', 'payment-email-notifications' )
);
printf( '</select>' );
printf(
	'<input type="email" name="pen_recipient_custom" id="pen_recipient_custom" value="%s" class="regular-text" style="margin-left:10px;%s" placeholder="%s">',
	esc_attr( $recipient_custom ),
	RECIPIENT_CUSTOM !== $recipient_type ? 'display:none;' : '',
	esc_attr__( 'email@example.com', 'payment-email-notifications' )
);
printf(
	'<p class="description">%s</p>',
	esc_html__( 'Who should receive this email.', 'payment-email-notifications' )
);
printf( '</td>' );
printf( '</tr>' );

// Subject.
printf( '<tr>' );
printf(
	'<th><label for="pen_subject">%s</label></th>',
	esc_html__( 'Email Subject', 'payment-email-notifications' )
);
printf(
	'<td><input type="text" name="pen_subject" id="pen_subject" value="%s" class="large-text" required></td>',
	esc_attr( $definition['subject'] )
);
printf( '</tr>' );

// Available tokens.
$token_engine    = new Token_Engine();
$token_reference = $token_engine->get_token_reference();

printf( '<tr>' );
printf(
	'<th>%s</th>',
	esc_html__( 'Available Tokens', 'payment-email-notifications' )
);
printf( '<td><div class="pen-token-pills">' );

foreach ( $token_reference as $token_name => $token_desc ) {
	printf(
		'<button type="button" class="pen-token-pill" data-token="{{%s}}" title="%s">%s</button>',
		esc_attr( $token_name ),
		esc_attr( $token_desc ),
		esc_html( '{{' . $token_name . '}}' )
	);
}

printf( '</div>' );
printf(
	'<p class="description">%s</p>',
	esc_html__( 'Click a token to copy it to your clipboard.', 'payment-email-notifications' )
);
printf( '</td>' );
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
	esc_html__( 'Plain text with tokens. Line breaks will be preserved in the email.', 'payment-email-notifications' )
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
