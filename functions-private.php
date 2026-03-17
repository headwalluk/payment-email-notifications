<?php
/**
 * Private/internal helper functions.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

/**
 * Get the plugin instance.
 *
 * @since 0.1.0
 *
 * @return Plugin The plugin instance.
 */
function pen_get_plugin(): Plugin {
	global $pen_plugin_instance;
	return $pen_plugin_instance;
}

/**
 * Get the current timestamp in human-readable format.
 *
 * @since 0.1.0
 *
 * @param string $format Date format string.
 *
 * @return string Formatted date string.
 */
function pen_get_now_formatted( string $format = 'Y-m-d H:i:s T' ): string {
	$now = new \DateTime( 'now', wp_timezone() );
	return $now->format( $format );
}
