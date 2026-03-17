# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Initial project structure and development roadmap
- Plugin scaffold: main plugin file, constants, Plugin class, HPOS declaration
- WooCommerce dependency check with admin notice
- Admin menu item under WooCommerce (placeholder page)
- PHPCS configuration with WordPress coding standards
- Email definitions CRUD with wp_options storage
- Admin settings page under WooCommerce menu (list view, add/edit form)
- Admin CSS for settings pages
- Token engine with {{customer.*}}, {{order.*}}, {{site.*}} substitution
- {{order.items}} renders WooCommerce-styled line items table
- `pen_email_tokens` filter for adding custom tokens
- Collapsible token reference in admin edit form
- Hourly cron engine for evaluating and sending scheduled emails
- WooCommerce email template wrapping (header/footer chrome)
- Order notes recorded when emails are sent
- `pen_should_send_email`, `pen_email_content`, `pen_email_recipient` filters
- Cron scheduled on activation, unscheduled on deactivation
- Order status change tracking with `_pen_status_changed_at` order meta
- Sent email log per order with `_pen_emails_sent` order meta (cleared on status change)
- Helper methods: days at current status, has-email-been-sent, record-email-sent
