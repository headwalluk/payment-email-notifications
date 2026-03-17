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

// wp_options keys — prefix with OPT_.
const OPT_EMAIL_DEFINITIONS  = 'pen_email_definitions';
const OPT_ORDER_META_KEYS    = 'pen_order_meta_keys';
const OPT_CUSTOMER_META_KEYS = 'pen_customer_meta_keys';

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

// Recipient types.
const RECIPIENT_CUSTOMER = 'customer';
const RECIPIENT_ADMIN    = 'admin';
const RECIPIENT_CUSTOM   = 'custom';

// AJAX action for test emails.
const AJAX_SEND_TEST_EMAIL = 'pen_send_test_email';
