<?php
/**
 * Admin template: list all email definitions.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 *
 * @var array<string, array<string, mixed>> $definitions All email definitions.
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

printf( '<div class="wrap">' );

printf( '<h1 class="wp-heading-inline">%s</h1>', esc_html__( 'Order Status Emails', 'payment-email-notifications' ) );

printf(
	'<a href="%s" class="page-title-action">%s</a>',
	esc_url(
		add_query_arg(
			[
				'page'   => ADMIN_PAGE_SLUG,
				'action' => 'add',
			],
			admin_url( 'admin.php' )
		)
	),
	esc_html__( 'Add New', 'payment-email-notifications' )
);

$current_tab = 'emails';
include PEN_PLUGIN_DIR . 'admin-templates/tab-navigation.php';

printf( '<hr class="wp-header-end">' );

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display check.
if ( isset( $_GET['updated'] ) ) {
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html__( 'Email definition saved.', 'payment-email-notifications' )
	);
}

if ( isset( $_GET['deleted'] ) ) {
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html__( 'Email definition deleted.', 'payment-email-notifications' )
	);
}
// phpcs:enable

if ( empty( $definitions ) ) {
	printf(
		'<div class="pen-empty-state"><p>%s</p><p><a href="%s" class="button button-primary">%s</a></p></div>',
		esc_html__( 'No email definitions configured yet.', 'payment-email-notifications' ),
		esc_url(
			add_query_arg(
				[
					'page'   => ADMIN_PAGE_SLUG,
					'action' => 'add',
				],
				admin_url( 'admin.php' )
			)
		),
		esc_html__( 'Create your first email definition', 'payment-email-notifications' )
	);
} else {
	$all_statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : [];

	printf( '<table class="wp-list-table widefat fixed striped">' );
	printf( '<thead><tr>' );
	printf( '<th>%s</th>', esc_html__( 'Label', 'payment-email-notifications' ) );
	printf( '<th>%s</th>', esc_html__( 'Order Status', 'payment-email-notifications' ) );
	printf( '<th>%s</th>', esc_html__( 'Days at Status', 'payment-email-notifications' ) );
	printf( '<th>%s</th>', esc_html__( 'Enabled', 'payment-email-notifications' ) );
	printf( '<th>%s</th>', esc_html__( 'Actions', 'payment-email-notifications' ) );
	printf( '</tr></thead>' );

	printf( '<tbody>' );

	foreach ( $definitions as $id => $definition ) {
		$status_label = isset( $all_statuses[ $definition['status'] ] )
			? $all_statuses[ $definition['status'] ]
			: $definition['status'];

		$enabled = isset( $definition['enabled'] )
			? (bool) filter_var( $definition['enabled'], FILTER_VALIDATE_BOOLEAN )
			: false;

		$edit_url = add_query_arg(
			[
				'page'       => ADMIN_PAGE_SLUG,
				'action'     => 'edit',
				'definition' => $id,
			],
			admin_url( 'admin.php' )
		);

		printf( '<tr>' );

		printf(
			'<td><a href="%s"><strong>%s</strong></a></td>',
			esc_url( $edit_url ),
			esc_html( $definition['label'] )
		);

		printf( '<td>%s</td>', esc_html( $status_label ) );
		printf( '<td>%d</td>', absint( $definition['days'] ) );

		printf(
			'<td><span class="pen-status pen-status-%s">%s</span></td>',
			$enabled ? 'enabled' : 'disabled',
			$enabled
				? esc_html__( 'Yes', 'payment-email-notifications' )
				: esc_html__( 'No', 'payment-email-notifications' )
		);

		printf(
			'<td><a href="%s" class="button button-small">%s</a> <button type="button" class="button button-small pen-test-email" data-definition-id="%s">%s</button></td>',
			esc_url( $edit_url ),
			esc_html__( 'Edit', 'payment-email-notifications' ),
			esc_attr( $id ),
			esc_html__( 'Test', 'payment-email-notifications' )
		);

		printf( '</tr>' );
	}

	printf( '</tbody></table>' );
}

printf( '</div>' );
