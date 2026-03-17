# Project Tracker

**Plugin:** Payment Email Notifications
**Version:** 0.1.0
**Last Updated:** 2026-03-17
**Current Phase:** M1 (Plugin Scaffold)
**Overall Progress:** 0%

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

- [ ] Create main plugin file with headers, WC dependency check, HPOS declaration
- [ ] Create `constants.php` with option keys, meta keys, default values
- [ ] Create `functions-private.php`
- [ ] Create `includes/class-plugin.php` — main orchestrator with hook registration
- [ ] Set up `phpcs.xml` configuration
- [ ] Verify plugin activates cleanly and appears in WP admin

### M2 — Order Status Tracking
> Track when orders change status so we can calculate "days at current status".

- [ ] Create `includes/class-status-tracker.php`
- [ ] Hook into `woocommerce_order_status_changed` to record `_pen_status_changed_at` order meta
- [ ] Clear `_pen_emails_sent` meta on status change (reset sent log for new status)
- [ ] Add helper method: get days at current status for an order
- [ ] Handle edge case: orders already at a status when plugin is first activated (backfill or graceful handling)

### M3 — Email Definitions CRUD
> Admin UI for creating, editing, enabling/disabling, and deleting email definitions.

- [ ] Create `includes/class-email-definitions.php` — get, save, delete definitions
- [ ] Create `includes/class-settings.php` — admin page under WooCommerce menu
- [ ] Admin list view: table of all definitions with status, days, enabled toggle
- [ ] Admin add/edit form: label, subject, body (textarea), target status, days threshold, enabled
- [ ] Nonce verification, capability checks, input sanitization on all saves
- [ ] Create admin-templates for list and edit views
- [ ] Enqueue minimal admin CSS for the settings pages

### M4 — Token Engine
> Template substitution system for email subject and body.

- [ ] Create `includes/class-token-engine.php`
- [ ] Implement core tokens: `customer.*`, `order.*`, `site.*`
- [ ] Implement `{{order.items}}` — render WooCommerce-styled line items table
- [ ] Add `pen_email_tokens` filter for extensibility
- [ ] Token reference display in admin edit form (list of available tokens)

### M5 — Cron & Email Sending
> The engine: hourly cron evaluates orders and sends emails.

- [ ] Create `includes/class-email-sender.php`
- [ ] Register/deregister WP-Cron schedule on activation/deactivation
- [ ] Cron handler: iterate definitions → query matching orders → check thresholds → send
- [ ] Use WooCommerce email system (`WC_Emails` / `WC_Email`) for styled output
- [ ] Record sent emails in `_pen_emails_sent` order meta
- [ ] Add `pen_email_content` filter (post-token-replacement)
- [ ] Add `pen_email_recipient` filter
- [ ] Add `pen_should_send_email` filter
- [ ] Handle errors gracefully: log failures, don't block other emails
- [ ] Add WooCommerce order note when email is sent

### M6 — Admin Enhancements
> Polish the admin experience.

- [ ] Email preview/test send from admin (send to admin email with sample data)
- [ ] Sent email log viewer per order (meta box on order edit screen)
- [ ] Inline help text / tooltips on settings page
- [ ] Admin notice if no email definitions are configured

### M7 — Final Testing & Release
> Get the plugin ready for v1.0.0.

- [ ] Write `docs/` developer documentation (hooks reference, extending the plugin)
- [ ] Final `phpcs` pass — zero violations
- [ ] Test full workflow end-to-end: create definition → order changes status → email sent on schedule
- [ ] Tag v1.0.0

---

## Technical Debt

_None yet — clean slate._

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
