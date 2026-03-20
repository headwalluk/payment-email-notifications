# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.0.0] - 2026-03-20

### Added
- Send schedule settings: time-of-day window and day-of-week restrictions
- New tokens: `{{order.view_url}}`, `{{order.payment_url}}`, `{{order.status}}`
- Settings link on the Plugins page
- Email definition count on Emails tab
- Send schedule summary notice on Emails list view
- Server timezone display on schedule settings
- `.distignore` for clean distribution builds
- Text domain loading for translation support

### Changed
- Renamed plugin to Order Status Email Notifications
- Prefixed all admin template variables for Plugin Check compliance

## [0.2.1] - 2026-03-17

### Added
- GitHub Actions release workflow for automated zip builds on version tags

## [0.2.0] - 2026-03-17

### Added
- Admin tab navigation with Emails and Settings tabs
- Settings tab with configurable order and customer meta keys
- Custom meta tokens: `{{order.meta.KEY}}` and `{{customer.meta.KEY}}` syntax
- Meta token pills auto-appear in edit form when keys are configured
- Per-definition recipient type: customer billing email, site admin email, or custom address
- Test email sending from admin list view (uses most recent order as sample data)
- Admin JS for token pill clipboard copy and test email AJAX

## [0.1.0] - 2026-03-17

### Added
- Plugin scaffold: main plugin file, constants, Plugin class, HPOS declaration
- WooCommerce dependency check with admin notice
- Admin menu item under WooCommerce
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
