<?php
/**
 * Plugin Name:       Payment Email Notifications
 * Plugin URI:        https://github.com/headwalluk/payment-email-notifications
 * Description:       Send scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.
 * Version:           0.2.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Headwall
 * Author URI:        https://headwall.tech
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       payment-email-notifications
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 8.0
 * WC tested up to:      9.6
 *
 * @package Payment_Email_Notifications
 */

defined( 'ABSPATH' ) || die();

define( 'PEN_PLUGIN_VERSION', '0.2.0' );
define( 'PEN_PLUGIN_NAME', 'payment-email-notifications' );
define( 'PEN_PLUGIN_FILE', __FILE__ );
define( 'PEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PEN_PLUGIN_ASSETS_URL', PEN_PLUGIN_URL . 'assets/' );

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
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Initialise the plugin.
 *
 * @since 0.1.0
 *
 * @return void
 */
function pen_plugin_run(): void {
	global $pen_plugin_instance;
	$pen_plugin_instance = new Payment_Email_Notifications\Plugin();
	$pen_plugin_instance->run();
}
pen_plugin_run();

/**
 * Schedule cron on activation.
 *
 * @since 0.1.0
 */
register_activation_hook( __FILE__, array( Payment_Email_Notifications\Email_Sender::class, 'schedule_cron' ) );

/**
 * Unschedule cron on deactivation.
 *
 * @since 0.1.0
 */
register_deactivation_hook( __FILE__, array( Payment_Email_Notifications\Email_Sender::class, 'unschedule_cron' ) );
