# Project Tracker

**Plugin:** Order Status Email Notifications
**Version:** 1.0.0
**Last Updated:** 2026-03-20
**Current Phase:** v1.0.0 Released
**Overall Progress:** 100%

---

## Overview

WordPress/WooCommerce plugin that sends scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status.

**Use cases:**
- BACS payment reminders: "Your invoice is due in 7 days" → "Payment is now due" → "Payment is overdue"
- Review reminders: 14 days after order completion, nudge for a product review
- Any time-based email triggered by order status duration

**Core model:** Email definitions stored in `wp_options`. Each definition targets an order status + a "days at status" threshold. Hourly cron evaluates all orders against all definitions and sends emails that haven't already been sent.

---

## Milestones

### M1 — Plugin Scaffold & Infrastructure
> Get the plugin loading, WooCommerce dependency check, constants, and file structure in place.

- [x] Create main plugin file with headers, WC dependency check, HPOS declaration
- [x] Create `constants.php` with option keys, meta keys, default values
- [x] Create `functions-private.php`
- [x] Create `includes/class-plugin.php` — main orchestrator with hook registration
- [x] Set up `phpcs.xml` configuration
- [x] Verify plugin activates cleanly and appears in WP admin

### M2 — Order Status Tracking
> Track when orders change status so we can calculate "days at current status".

- [x] Create `includes/class-status-tracker.php`
- [x] Hook into `woocommerce_order_status_changed` to record `_pen_status_changed_at` order meta
- [x] Clear `_pen_emails_sent` meta on status change (reset sent log for new status)
- [x] Add helper method: get days at current status for an order
- [x] Handle edge case: orders already at a status when plugin is first activated (returns null — graceful skip)

### M3 — Email Definitions CRUD
> Admin UI for creating, editing, enabling/disabling, and deleting email definitions.

- [x] Create `includes/class-email-definitions.php` — get, save, delete definitions
- [x] Create `includes/class-settings.php` — admin page under WooCommerce menu
- [x] Admin list view: table of all definitions with status, days, enabled toggle
- [x] Admin add/edit form: label, subject, body (textarea), target status, days threshold, enabled
- [x] Nonce verification, capability checks, input sanitization on all saves
- [x] Create admin-templates for list and edit views
- [x] Enqueue minimal admin CSS for the settings pages

### M4 — Token Engine
> Template substitution system for email subject and body.

- [x] Create `includes/class-token-engine.php`
- [x] Implement core tokens: `customer.*`, `order.*`, `site.*`
- [x] Implement `{{order.items}}` — render WooCommerce-styled line items table
- [x] Implement `{{order.view_url}}`, `{{order.payment_url}}`, `{{order.status}}`
- [x] Add `pen_email_tokens` filter for extensibility
- [x] Token reference display in admin edit form (clickable pills with clipboard copy)

### M5 — Cron & Email Sending
> The engine: hourly cron evaluates orders and sends emails.

- [x] Create `includes/class-email-sender.php`
- [x] Register/deregister WP-Cron schedule on activation/deactivation
- [x] Cron handler: iterate definitions → query matching orders → check thresholds → send
- [x] Use WooCommerce email system (`WC_Emails` / `WC_Email`) for styled output
- [x] Record sent emails in `_pen_emails_sent` order meta
- [x] Add `pen_email_content` filter (post-token-replacement)
- [x] Add `pen_email_recipient` filter
- [x] Add `pen_should_send_email` filter
- [x] Handle errors gracefully: log failures, don't block other emails
- [x] Add WooCommerce order note when email is sent
- [x] Send schedule gating: time-of-day window and day-of-week restrictions

### M6 — Admin Enhancements
> Polish the admin experience.

- [x] Recipient type per definition: customer billing email, site admin email, or custom address
- [x] Test email send from admin: "Test" button on list view prompts for recipient, sends using most recent order
- [x] Admin tab navigation (Emails / Settings tabs)
- [x] Custom meta tokens: configurable order & customer meta keys via Settings tab (`{{order.meta.KEY}}`, `{{customer.meta.KEY}}`)
- [x] Send schedule settings UI (time-of-day window, day-of-week checkboxes)
- [x] Email definition count on Emails tab
- [x] Send schedule summary notice on Emails list view
- [x] Settings link on Plugins page
- [x] Server timezone display on schedule settings

### M7 — Final Testing & Release
> Get the plugin ready for v1.0.0.

- [x] Final `phpcs` pass — zero violations
- [x] Plugin Check compliance (prefixed template variables, `.distignore`)
- [x] Text domain loading for translation support
- [x] Update all version references and changelogs
- [x] Tag v1.0.0

---

## Technical Debt

_None — clean slate._

---

## Notes for Development

- **Plugin prefix:** `pen_` for functions, hooks, and option names
- **Namespace:** `Payment_Email_Notifications`
- **Text domain:** `payment-email-notifications`
- **Min PHP:** 8.0 | **Requires:** WooCommerce
- **Data storage:** `wp_options` for definitions, order meta for tracking
- **Email rendering:** WooCommerce email template system (header/footer chrome + styled HTML)
- **HPOS:** Must be fully compatible — never use `get_post_meta()` for order data
- **Status change reset:** When an order changes status, `_pen_emails_sent` is cleared so emails re-trigger if the order returns to a previous status
