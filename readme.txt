=== Payment Email Notifications ===
Contributors: headwall
Tags: woocommerce, email, notifications, payment, reminders
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.

== Description ==

Payment Email Notifications lets you create time-based email workflows triggered by WooCommerce order statuses. Define emails that send automatically when an order has been at a specific status for a configurable number of days.

**Example use cases:**

* **BACS payment reminders** — Send "payment upcoming", "payment due", and "payment overdue" emails at configured intervals
* **Review requests** — Send a review reminder 14 days after order completion
* **Follow-ups** — Any time-based email tied to an order status

**Features:**

* Simple admin interface to create and manage email definitions
* Token substitution for personalised emails (customer name, order details, line items)
* Emails styled using the WooCommerce email template system
* Hourly cron ensures emails are sent on schedule
* Developer-friendly with filters for customisation

== Installation ==

1. Upload the `payment-email-notifications` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and active
4. Navigate to WooCommerce → Payment Emails to configure your email definitions

== Frequently Asked Questions ==

= Does this require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Can I use custom order statuses? =

Yes. Any registered WooCommerce order status can be used as a trigger, including custom statuses added by other plugins.

= How often does the plugin check for emails to send? =

The plugin runs an hourly cron job that evaluates all orders against your email definitions.

= Can I customise the email content? =

Yes. Each email definition has its own subject and body with support for template tokens like `{{customer.first_name}}` and `{{order.total}}`. Developers can also use filters for advanced customisation.

== Changelog ==

= 1.0.0 =
* Initial release
