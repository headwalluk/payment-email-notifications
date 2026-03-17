<?php
/**
 * Plugin Name:       Payment Email Notifications
 * Plugin URI:        https://github.com/headwalluk/payment-email-notifications
 * Description:       Send scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Headwall
 * Author URI:        https://headwall.tech
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       payment-email-notifications
 * Domain Path:       /languages
 *
 * WC requires at least: 8.0
 * WC tested up to:      9.6
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

// Plugin constants.
require_once __DIR__ . '/constants.php';

// Private helper functions.
require_once __DIR__ . '/functions-private.php';

// Core classes.
require_once __DIR__ . '/includes/class-plugin.php';
require_once __DIR__ . '/includes/class-email-definitions.php';
require_once __DIR__ . '/includes/class-settings.php';
require_once __DIR__ . '/includes/class-status-tracker.php';
require_once __DIR__ . '/includes/class-token-engine.php';
require_once __DIR__ . '/includes/class-email-sender.php';

/**
 * Declare HPOS compatibility.
 *
 * @since 0.1.0
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}
);

/**
 * Check for WooCommerce dependency and initialise the plugin.
 *
 * @since 0.1.0
 */
add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				function () {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html__(
							'Payment Email Notifications requires WooCommerce to be installed and active.',
							'payment-email-notifications'
						)
					);
				}
			);
			return;
		}

		global $pen_plugin_instance;
		$pen_plugin_instance = new Plugin( __FILE__ );
		$pen_plugin_instance->run();
	}
);

/**
 * Schedule cron on activation.
 *
 * @since 0.1.0
 */
register_activation_hook( __FILE__, array( Email_Sender::class, 'schedule_cron' ) );

/**
 * Unschedule cron on deactivation.
 *
 * @since 0.1.0
 */
register_deactivation_hook( __FILE__, array( Email_Sender::class, 'unschedule_cron' ) );
