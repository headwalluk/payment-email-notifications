<?php
/**
 * Admin template: tab navigation partial.
 *
 * @since 0.2.0
 *
 * @package Payment_Email_Notifications
 *
 * @var string $pen_current_tab The currently active tab slug.
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

$pen_email_count = count( get_option( OPT_EMAIL_DEFINITIONS, array() ) );

$pen_tabs = array(
	'emails'   => sprintf(
		/* translators: %s: email definition count. */
		__( 'Emails (%s)', 'payment-email-notifications' ),
		number_format_i18n( $pen_email_count )
	),
	'settings' => __( 'Settings', 'payment-email-notifications' ),
);

printf( '<nav class="nav-tab-wrapper wp-clearfix">' );

foreach ( $pen_tabs as $pen_tab_slug => $pen_tab_label ) {
	$pen_tab_url = add_query_arg(
		array(
			'page' => ADMIN_PAGE_SLUG,
			'tab'  => $pen_tab_slug,
		),
		admin_url( 'admin.php' )
	);

	printf(
		'<a href="%s" class="nav-tab %s">%s</a>',
		esc_url( $pen_tab_url ),
		$pen_current_tab === $pen_tab_slug ? 'nav-tab-active' : '',
		esc_html( $pen_tab_label )
	);
}

printf( '</nav>' );
