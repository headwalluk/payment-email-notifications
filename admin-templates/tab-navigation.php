<?php
/**
 * Admin template: tab navigation partial.
 *
 * @since 0.2.0
 *
 * @package Payment_Email_Notifications
 *
 * @var string $current_tab The currently active tab slug.
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

$tabs = [
	'emails'   => __( 'Emails', 'payment-email-notifications' ),
	'settings' => __( 'Settings', 'payment-email-notifications' ),
];

printf( '<nav class="nav-tab-wrapper wp-clearfix">' );

foreach ( $tabs as $tab_slug => $tab_label ) {
	$tab_url = add_query_arg(
		[
			'page' => ADMIN_PAGE_SLUG,
			'tab'  => $tab_slug,
		],
		admin_url( 'admin.php' )
	);

	printf(
		'<a href="%s" class="nav-tab %s">%s</a>',
		esc_url( $tab_url ),
		$current_tab === $tab_slug ? 'nav-tab-active' : '',
		esc_html( $tab_label )
	);
}

printf( '</nav>' );
