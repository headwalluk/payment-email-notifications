# Payment Email Notifications

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue?logo=wordpress)
![WooCommerce](https://img.shields.io/badge/WooCommerce-8.0%2B-96588a?logo=woocommerce)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green)

Send scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.

## Use Cases

- **BACS payment reminders** — Remind customers before payment is due, when it's due, and if it becomes overdue
- **Review requests** — Nudge customers to leave a review 14 days after order completion
- **Any status-based timing** — Configure emails for any WooCommerce order status with flexible day thresholds

## How It Works

1. **Define emails** in the admin: pick a target order status, set a "days at status" threshold, and write the email content
2. **Token substitution** lets you personalise emails with customer names, order details, and line item tables
3. **Hourly cron** checks all orders and sends emails when conditions are met — each email is only sent once per status period

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 8.0+

## Installation

1. Upload the `payment-email-notifications` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Navigate to **WooCommerce → Payment Emails** to configure your email definitions

## Extending

The plugin provides filters for developers to customise behaviour:

- `pen_email_tokens` — Add or modify template tokens
- `pen_email_content` — Filter rendered email content before sending
- `pen_email_recipient` — Override the email recipient
- `pen_should_send_email` — Conditionally suppress an email

See the `docs/` directory for full developer documentation.

## License

GPL v2 or later. See [LICENSE](LICENSE) for details.
