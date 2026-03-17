# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Initial project structure and development roadmap
- Plugin scaffold: main plugin file, constants, Plugin class, HPOS declaration
- WooCommerce dependency check with admin notice
- Admin menu item under WooCommerce (placeholder page)
- PHPCS configuration with WordPress coding standards
- Order status change tracking with `_pen_status_changed_at` order meta
- Sent email log per order with `_pen_emails_sent` order meta (cleared on status change)
- Helper methods: days at current status, has-email-been-sent, record-email-sent
