# Order Status Email Notifications

A WooCommerce plugin for sending scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.

## The original requirement

A client asked for something specific: automatically email customers whose orders had been in `bacs-processing` status for more than a set number of days, to remind them that payment was still outstanding. Then a follow-up after another threshold — something along the lines of "your account is on stop because this payment is overdue."

It's a reasonable requirement and not a difficult one. But it was also a good opportunity to build something more flexible. Instead of hardcoding logic around a single status and a couple of fixed thresholds, Order Status Email Notifications lets you configure any number of email definitions — each targeting any WooCommerce order status, with its own day threshold, email content, and recipient. The BACS use case is just one example; the plugin doesn't know or care which status you target.

## What it does

You create *email definitions* in the admin. Each definition says: "if an order has been in *this status* for at least *this many days*, send *this email* to *this recipient*." The hourly cron engine evaluates all enabled definitions against matching orders and sends emails when conditions are met.

Each email is sent once per status period. If the order changes status — for example, a BACS order moves from `pending` to `cancelled` — the sent-email log clears. If the order later returns to `pending`, the definitions can trigger again from scratch.

## Use cases

**BACS payment reminders.** The original use case. Set up two definitions targeting `bacs-processing`: one at 3 days (a polite nudge) and one at 7 days (a firmer "your account is on stop" message). The 3-day email fires once, and if the order is still unpaid at day 7, the second one fires.

**Review requests.** Target `completed` status with a threshold of 14 days. Drop in a personalised message with the order details and a link back to the site.

**Anything else.** Any WooCommerce order status, any threshold, any email content. Custom statuses registered by other plugins are included automatically.

## Setting up an email definition

Navigate to **WooCommerce → Order Status Emails** and click **Add New**.

Each definition has:

- **Label** — an internal name for the definition (not shown to customers)
- **Status** — the WooCommerce order status to target
- **Days at status** — how many whole days the order must have been at that status before the email fires
- **Subject** — the email subject line, with token support
- **Body** — the email content, with token support
- **Recipient** — customer billing email, site admin email, or a fixed custom address
- **Enabled/disabled toggle**

Save the definition and it starts being evaluated on the next hourly cron run.

## Token substitution

Email subjects and bodies support `{{token}}` placeholders that are replaced with real order data when the email is sent.

**Built-in tokens:**

- `{{customer.first_name}}` — customer first name
- `{{customer.last_name}}` — customer last name
- `{{customer.full_name}}` — customer full name
- `{{customer.email}}` — customer email address
- `{{customer.company}}` — customer company name
- `{{order.id}}` — order number
- `{{order.total}}` — formatted order total
- `{{order.date}}` — order date
- `{{order.items}}` — WooCommerce-styled order line items table (HTML)
- `{{order.view_url}}` — link to the order in My Account
- `{{order.payment_url}}` — direct link to the WooCommerce checkout payment page for this order
- `{{order.status}}` — human-readable status label
- `{{order.payment_method}}` — payment method name
- `{{site.name}}` — site name
- `{{site.url}}` — site URL

The `{{order.payment_url}}` token is particularly useful for BACS reminders — you can include a direct "pay now" link in the email without the customer needing to navigate through My Account.

## Custom meta tokens

The Settings tab lets you configure additional meta keys to use as tokens. Enter one key per line under **Order meta keys** or **Customer meta keys**, and the token becomes available in all email definitions as `{{order.meta.KEY}}` or `{{customer.meta.KEY}}`.

For example, if orders have a `_purchase_order_number` meta field, add it to the order meta keys list and use `{{order.meta._purchase_order_number}}` in your email body. The token reference in the edit form updates automatically to include the keys you've configured.

## How the cron engine works

On plugin activation, an hourly WP-Cron event is registered. On each run, the engine:

1. Checks whether the current time falls within the configured send schedule (see below)
2. Loads all enabled email definitions
3. For each definition, queries WooCommerce for orders at the target status
4. For each matching order, calculates how many whole days it has been at its current status
5. Skips the order if the days threshold hasn't been met
6. Skips the order if this definition has already been sent for the current status period
7. Sends the email if all conditions are met, then records it as sent in the order meta

The status change timestamp is stored in `_pen_status_changed_at` order meta when WooCommerce fires the `woocommerce_order_status_changed` hook. Orders that existed before the plugin was activated won't have this meta and are skipped — they'd need a manual status transition to start being tracked.

When an email is sent successfully, an order note is added: `Payment Email Notifications: "Definition Name" sent to customer@example.com.` If sending fails, the failure is logged to the PHP error log.

## Send schedule

By default, the cron engine processes emails whenever the hourly job fires. The Settings tab gives you two optional constraints:

**Time-of-day window.** Enable this to restrict sending to a specific time range — for example, 09:00 to 17:00. The cron runs every hour, but if the current server time is outside the window, the entire run is skipped. The settings page shows your server's configured timezone so there's no ambiguity.

**Day-of-week restrictions.** Enable this to select which days sending is allowed. If today isn't in the list, the cron run is skipped.

Both constraints can be used together. If you want BACS reminders to only go out on weekday mornings — not at 2am on a Sunday — this is how you do it.

## Testing email definitions

From the email definitions list, each row has a **Test** button. Click it and enter an email address; the plugin pulls the most recent order from the system and uses it as sample data to render the tokens and send a test email. The test email goes through WooCommerce's mailer, so it uses the same headers, template, and delivery path as production sends — but it doesn't record the send or add an order note.

This makes it practical to verify the layout and token output before enabling a definition.

## Email presentation

All emails are wrapped in WooCommerce's standard email header and footer templates. This means they inherit your WooCommerce email branding — colours, logo, footer text — without any additional configuration. If you've customised WooCommerce's email templates, Order Status Email Notifications picks those up automatically.

## Extending the plugin

Four filter hooks are available for developers who need to customise behaviour.

### Add or modify tokens

```php
add_filter( 'pen_email_tokens', function( $tokens, $order, $template ) {
    // Add a custom token from an order meta field.
    $tokens['order.account_manager'] = $order->get_meta( '_account_manager_name' );
    return $tokens;
}, 10, 3 );
```

### Filter email content before sending

```php
add_filter( 'pen_email_content', function( $body_html, $definition_id, $definition, $order ) {
    // Append a custom footer to a specific definition.
    if ( 'bacs-reminder-7-day' === $definition_id ) {
        $body_html .= '<p>If you have any questions, please call us on 01234 567890.</p>';
    }
    return $body_html;
}, 10, 4 );
```

### Override the recipient

```php
add_filter( 'pen_email_recipient', function( $recipient, $definition_id, $definition, $order ) {
    // For wholesale orders, CC the account manager instead.
    if ( $order->get_meta( '_is_wholesale' ) ) {
        return get_option( 'wholesale_manager_email' );
    }
    return $recipient;
}, 10, 4 );
```

### Conditionally suppress an email

```php
add_filter( 'pen_should_send_email', function( $should_send, $definition_id, $definition, $order ) {
    // Don't send BACS reminders to customers on a payment plan.
    if ( $order->get_meta( '_on_payment_plan' ) ) {
        return false;
    }
    return $should_send;
}, 10, 4 );
```

## Technical notes

- **PHP 8.0+** — type hints and return types throughout
- **WordPress 6.0+** — uses the WordPress Settings API, WP-Cron, and standard admin patterns
- **WooCommerce 8.0+** — HPOS (High-Performance Order Storage) compatible; declared at activation
- **i18n** — ships with translations for German, French, and Polish, plus the `.pot` template for additional languages
- **No new database tables** — definitions stored in `wp_options`, tracking data in order meta
- **WordPress Coding Standards** — validated with `phpcs` using the WordPress ruleset

## Get it

Order Status Email Notifications is free and open source.

- **GitHub:** [github.com/headwalluk/payment-email-notifications](https://github.com/headwalluk/payment-email-notifications)
- **Version:** 1.0.0
- **Requirements:** WordPress 6.0+, WooCommerce 8.0+, PHP 8.0+
- **License:** GPL v2 or later
