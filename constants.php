<?php
/**
 * Plugin constants.
 *
 * @since 0.1.0
 *
 * @package Payment_Email_Notifications
 */

namespace Payment_Email_Notifications;

defined( 'ABSPATH' ) || die();

// Plugin version.
const VERSION = '0.1.0';

// Plugin slug.
const SLUG = 'payment-email-notifications';

// wp_options keys — prefix with OPT_.
const OPT_EMAIL_DEFINITIONS = 'pen_email_definitions';

// Order meta keys — prefix with META_.
const META_STATUS_CHANGED_AT = '_pen_status_changed_at';
const META_EMAILS_SENT       = '_pen_emails_sent';

// Cron hook name.
const CRON_HOOK = 'pen_process_scheduled_emails';

// Cron interval (seconds).
const CRON_INTERVAL = HOUR_IN_SECONDS;

// Admin page slug.
const ADMIN_PAGE_SLUG = 'pen-email-definitions';

// Capability required to manage email definitions.
const CAPABILITY = 'manage_woocommerce';
