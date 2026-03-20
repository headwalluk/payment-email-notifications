=== Order Status Email Notifications ===
Contributors: headwall
Tags: woocommerce, email, notifications, order-status, reminders
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.

== Description ==

Order Status Email Notifications lets you create time-based email workflows triggered by WooCommerce order statuses. Define emails that send automatically when an order has been at a specific status for a configurable number of days.

**Example use cases:**

* **BACS payment reminders** — Send "payment upcoming", "payment due", and "payment overdue" emails at configured intervals
* **Review requests** — Send a review reminder 14 days after order completion
* **Follow-ups** — Any time-based email tied to an order status

**Features:**

* Simple admin interface to create and manage email definitions
* Token substitution for personalised emails (customer name, order details, line items)
* Custom meta tokens — reference any order or customer meta field as `{{order.meta.KEY}}` or `{{customer.meta.KEY}}`
* Configurable recipient per email: customer, site admin, or custom address
* Test email sending from the admin list view
* Emails styled using the WooCommerce email template system
* Hourly cron ensures emails are sent on schedule
* Developer-friendly with filters for customisation

== Installation ==

1. Upload the `payment-email-notifications` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and active
4. Navigate to WooCommerce → Order Status Emails to configure your email definitions

== Frequently Asked Questions ==

= Does this require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Can I use custom order statuses? =

Yes. Any registered WooCommerce order status can be used as a trigger, including custom statuses added by other plugins.

= How often does the plugin check for emails to send? =

The plugin runs an hourly cron job that evaluates all orders against your email definitions.

= Can I include custom fields in my emails? =

Yes. Go to the Settings tab and add your order or customer meta keys (one per line). They become available as `{{order.meta.KEY}}` and `{{customer.meta.KEY}}` tokens in your email templates. This works with any meta field including ACF fields.

= Can I customise the email content? =

Yes. Each email definition has its own subject and body with support for template tokens like `{{customer.first_name}}` and `{{order.total}}`. Developers can also use filters for advanced customisation.

== Changelog ==

= 1.0.0 =
* Renamed plugin to Order Status Email Notifications
* Added send schedule settings: time-of-day window and day-of-week restrictions
* Added new tokens: `{{order.view_url}}`, `{{order.payment_url}}`, `{{order.status}}`
* Added Settings link on the Plugins page
* Added email definition count on Emails tab
* Added send schedule summary notice on Emails list view
* Added server timezone display on schedule settings
* Added `.distignore` for clean distribution builds
* Prefixed all admin template variables for Plugin Check compliance
* Added text domain loading for translation support

= 0.2.1 =
* Added GitHub Actions release workflow for automated zip builds on version tags

= 0.2.0 =
* Added admin tab navigation (Emails / Settings tabs)
* Added configurable custom meta tokens via Settings tab
* Added `{{order.meta.KEY}}` and `{{customer.meta.KEY}}` token syntax
* Added per-definition recipient type: customer billing email, site admin, or custom address
* Added test email sending from admin list view
* Added admin JS for token pill clipboard copy and test email AJAX

= 0.1.0 =
* Initial release — plugin scaffold, status tracking, email definitions CRUD, token engine, cron sending
