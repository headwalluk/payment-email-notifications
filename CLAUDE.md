# Payment Email Notifications - Claude Code Context

## Project Overview

WordPress/WooCommerce plugin that sends scheduled, WooCommerce-styled emails to customers based on how long an order has been at a given status. Think: payment reminders for BACS orders, review requests after completion, etc.

**Plugin slug:** `payment-email-notifications`
**Text domain:** `payment-email-notifications`
**Namespace:** `Payment_Email_Notifications`
**Function prefix:** `pen_`
**Minimum PHP:** 8.0
**Requires:** WooCommerce

## Architecture

### Core Concept

Each **email definition** specifies:
- A target WooCommerce order status (e.g. `wc-bacs-processing`)
- A "days at status" threshold (e.g. 23 days)
- Subject and body templates with token substitution

An **hourly cron job** queries orders at each target status, checks how long they've been there, and sends emails that haven't already been sent.

### Admin UI

The admin page (WooCommerce → Payment Emails) uses tab navigation:
- **Emails tab** — list, add, edit, delete, and test email definitions
- **Settings tab** — configure custom order/customer meta keys for token substitution

### Data Storage

- **Email definitions** — stored as a serialized array in `wp_options` (`pen_email_definitions`)
- **Custom meta keys** — `wp_options` (`pen_order_meta_keys`, `pen_customer_meta_keys`)
- **Status change tracking** — order meta `_pen_status_changed_at` (updated on every status change)
- **Sent log** — order meta `_pen_emails_sent` (array of definition IDs sent for current status; cleared on status change)

### Token System

Templates support `{{token.name}}` substitution:
- `{{customer.first_name}}`, `{{customer.last_name}}`, `{{customer.full_name}}`
- `{{order.id}}`, `{{order.total}}`, `{{order.date}}`
- `{{order.items}}` — renders HTML line items table
- `{{site.name}}`, `{{site.url}}`
- `{{order.meta.KEY}}` — any order meta field (configured in Settings tab)
- `{{customer.meta.KEY}}` — any user meta field (configured in Settings tab; empty for guest orders)
- Extensible via `pen_email_tokens` filter

Custom meta keys are stored as newline-separated strings in `wp_options` (`pen_order_meta_keys`, `pen_customer_meta_keys`) and managed via the admin Settings tab.

### Key Filters

- `pen_email_tokens` — add/modify available tokens
- `pen_email_content` — filter rendered email content
- `pen_email_recipient` — override recipient
- `pen_should_send_email` — conditionally suppress sending

## Coding Standards

See `.github/copilot-instructions.md` for full standards. Key points:
- Follow WordPress Coding Standards (spaces in parentheses, tabs for indentation)
- PHP 8.0+ features (type hints, union types, named arguments)
- **No `declare(strict_types=1)`**
- SESE pattern (single entry, single exit) for functions
- Constants in `constants.php`, no magic strings/numbers
- All dates as `Y-m-d H:i:s T` format, never Unix timestamps
- HPOS-compatible: use `WC_Order` methods, never `get_post_meta()` for orders
- All template files use `printf()`/`echo`, no inline HTML mixing
- Sanitize all input, escape all output, verify nonces

## File Structure

```
payment-email-notifications/
├── payment-email-notifications.php   # Main plugin file
├── constants.php                     # All constants
├── functions-private.php             # Internal helper functions
├── includes/                         # Core classes
│   ├── class-plugin.php              # Main plugin orchestrator
│   ├── class-settings.php            # Admin settings page
│   ├── class-email-definitions.php   # CRUD for email definitions
│   ├── class-status-tracker.php      # Order status change tracking
│   ├── class-email-sender.php        # Cron job & sending logic
│   └── class-token-engine.php        # Template token substitution
├── admin-templates/                  # Admin page templates
├── templates/                        # Email templates
├── assets/
│   └── admin/                        # Admin CSS/JS
├── dev-notes/                        # Internal development docs
├── docs/                             # Developer/contributor docs
├── languages/                        # Translation files
├── readme.txt                        # WordPress.org readme
├── README.md                         # GitHub readme
└── CHANGELOG.md                      # Version history
```

## Development Workflow

- Project tracker: `dev-notes/00-project-tracker.md`
- Run `phpcs` before committing
- Commit message format: `type: description` (feat/fix/chore/refactor/docs/style/test)
