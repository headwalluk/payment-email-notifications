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
 * Check whether the current server time falls within the configured send schedule.
 *
 * Evaluates both the time-of-day window and the allowed days of the week.
 * Returns true (send allowed) when either feature is disabled.
 *
 * @since 0.3.0
 *
 * @return bool True if sending is allowed right now.
 */
function pen_is_within_send_schedule(): bool {
	$now = new \DateTime( 'now', wp_timezone() );

	// Time-of-day window check.
	$window_enabled = (bool) get_option( OPT_SEND_WINDOW_ENABLED, false );

	if ( $window_enabled ) {
		$start = get_option( OPT_SEND_WINDOW_START, DEFAULT_SEND_WINDOW_START );
		$end   = get_option( OPT_SEND_WINDOW_END, DEFAULT_SEND_WINDOW_END );
		$time  = $now->format( 'H:i' );

		if ( $time < $start || $time >= $end ) {
			return false;
		}
	}

	// Day-of-week check.
	$days_enabled = (bool) get_option( OPT_SEND_DAYS_ENABLED, false );

	if ( $days_enabled ) {
		$allowed_days = get_option( OPT_SEND_DAYS, ALL_DAYS );
		$today        = strtolower( $now->format( 'D' ) );

		if ( ! in_array( $today, $allowed_days, true ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Build a human-readable summary of the current send schedule.
 *
 * Returns an empty string when no schedule constraints are active.
 *
 * @since 1.0.0
 *
 * @return string Schedule summary, e.g. "Mon–Fri, 09:00–17:00 (Europe/London)".
 */
function pen_get_schedule_summary(): string {
	$window_enabled = (bool) get_option( OPT_SEND_WINDOW_ENABLED, false );
	$days_enabled   = (bool) get_option( OPT_SEND_DAYS_ENABLED, false );

	if ( ! $window_enabled && ! $days_enabled ) {
		return '';
	}

	$parts = array();

	if ( $days_enabled ) {
		$allowed_days = get_option( OPT_SEND_DAYS, ALL_DAYS );
		$day_labels   = array(
			'mon' => __( 'Mon', 'payment-email-notifications' ),
			'tue' => __( 'Tue', 'payment-email-notifications' ),
			'wed' => __( 'Wed', 'payment-email-notifications' ),
			'thu' => __( 'Thu', 'payment-email-notifications' ),
			'fri' => __( 'Fri', 'payment-email-notifications' ),
			'sat' => __( 'Sat', 'payment-email-notifications' ),
			'sun' => __( 'Sun', 'payment-email-notifications' ),
		);

		$day_names = array();
		foreach ( $day_labels as $key => $label ) {
			if ( in_array( $key, $allowed_days, true ) ) {
				$day_names[] = $label;
			}
		}

		$parts[] = implode( ', ', $day_names );
	}

	if ( $window_enabled ) {
		$start   = get_option( OPT_SEND_WINDOW_START, DEFAULT_SEND_WINDOW_START );
		$end     = get_option( OPT_SEND_WINDOW_END, DEFAULT_SEND_WINDOW_END );
		$parts[] = $start . '–' . $end;
	}

	$summary  = implode( ', ', $parts );
	$summary .= ' (' . wp_timezone_string() . ')';

	return $summary;
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
