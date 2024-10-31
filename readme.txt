=== Ninja Shop - The Quickest Way to Start Selling ===
Contributors: wpninjasllc, jameslaws, kstover, kbjohnson90, mrpritchett
Tags: ecommerce, e-commerce, sales, sell, store
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 5.2
Stable tag: 1.1.11
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Ninja Shop is an easy to use eCommerce plugin, the quickest way to start selling your products with WordPress.

== Description ==

*Ninja Shop is not currently under active development. If this changes in the future, this readme will be updated to reflect that.*

Ninja Shop is a free eCommerce plugin that allows you to sell anything, quickly. Built to integrate seamlessly with WordPress, Ninja Shop is a simple and clean alternative to clunky eCommerce solutions.

While Ninja Shop is filled with extremely powerful features, you won’t be overwhelmed with complexity. Only enable what you need, and watch your products take center stage.
= Sell Your Stuff Online in Under 5 Minutes =

Ninja Shop makes it easy to get your store up and running quickly. Just install, activate, choose your payment methods, and start adding your products.
= A Simple Interface =

Your products are not blog posts, so we created an experience that makes creating products simpler and more intuitive.
= Simple but Extendable =

An online store can get pretty complicated, and not everyone needs the same things. With Ninja Shop, many features are split into add-ons. You only see what your store needs, making it simple to use and manage.

* Product Inventory - Set and track product inventories.
* Hidden Source Files for Digital Product Downloads - Use source file URLs from the WordPress media library or from Dropbox, Amazon, etc. and Ninja Shop automatically hides this source URL for digital product downloads.
* Download Expirations - Apply settings for download links to expire or to limit access to download links/file downloads.
* Simple Shipping - Adds flat rate and free shipping for your physical products.
* Simple Taxes - Collect US, Canadian, or EU Value Added Taxes.
* Customer Order Notes - Allow your customers to leave a note while placing an order.
* Terms of Service - Require your customers agree to your Terms of Service when purchasing your products.
* Product Categories and Tags - Organize your store by with categories and tags.
* Membership - Create as many memberships as you want with specific rules for what content your members can access. You can also use the Memberships add-on to delay (or drip) content access on a daily, weekly, or monthly basis. *(Coming Soon)*

= Sell Your Products Your Way =

* Single or Multi-item Cart Options - Allow customers to purchase single products quickly or multiple products with one transaction.
* Digital Downloads - Add a product type for distributing digital downloads through Ninja Shop.
* Guest Checkout - Enabling this add-on gives customers the ability to checkout as a guest, without registering.
* Customer Pricing - Let customers choose their price from a list of price options you create or let them enter their own price.
* Coupons - Generate basic coupons that apply to all products in your store. *(Coming Soon)*
* Product Availability - Limit when specific products are available for purchase using simple start and end dates. *(Coming Soon)*
= Get Paid with PayPal, Stripe, and more =

Out of the box, Ninja Shop offers the simplest process for accepting payments. Additional options can easily be enabled easily to provide even more flexibility.

* PayPal Payments Standard (Basic)
* Offline Payments
* PayPal Payments Standard (Secure) *(Premium)*
* Stripe *(Premium)*
* PayPal Pro *(Coming Soon)*
* Authorize.net *(Coming Soon)*
* 2checkout *(Coming Soon)*
= Customer & Payment Management Made Easy  =

Ninja Shop uses the built-in WordPress user system, while adding customer data to its own page. This makes it simple to edit user transactions, view available downloads, and make customer notes for your reference.

* Basic Reporting Dashboard Widget - View basic sales statistics from the WordPress admin dashboard.
* Payment/Transaction Details - See order number, payment total, status, customer, payment method, and date of payment for individual transactions.
* Customer Registration - Use Ninja Shop Registration or WordPress Registration settings.
* Customer Data - Manage customers by viewing products purchased, transactions, or adding notes to customer info.
* Issue Refunds or Resend Confirmation Emails - Easily issue refunds or resend confirmation emails for individual customers.

= Customizable Emails =

Use the WordPress WSIWYG editor to make custom email templates for Admin Sales Notification emails and Customer Receipt Emails. HTML is accepted.

* Admin Sales Notification Emails - Customize the email sent to admins for sales notifications.
* Customer Receipt Emails - Customize the receipt emails customers receive after making a purchase.
* Email Shortcode Functions - Use built-in shortcode functions in emails for customer name, full name, username, download list, order table, purchase date, total, payment id, receipt id, payment method, site name, and receipt links.

= Learn More =

[For more information on Ninja Shop features and available add-ons](https://getninjashop.com)
= Supported by a Passionate Team =

We've been building and supporting WordPress tools since 2011. One of our other products, Ninja Forms, has over 1 million active installs, so you can trust we're up to the challenge.

We hope you'll try Ninja Shop and love it. But we also want to hear what we can do to make it work best for your business. [Send us your ideas and feedback](https://getninjashop.com).

== Developer Emeriti ==
ithemes, blepoxp, layotte, aaroncampbell, mattdanner

== Installation ==

Upload the Ninja Shop plugin to your WordPress install, activate it, and enable the Digital Downloads add-on and a transaction method add-on. Follow the set-up wizard and start selling.

== Upgrade Notice ==

= 1.1.11 =

*Bugs:*

* Fixed a bug that caused cart actions not to fire on first attempt.

== Changelog ==

= 1.1.11 =

*Bugs:*

* Fixed a bug that caused cart actions not to fire on first attempt.

= 1.1.10 =

*Features:*

* Added a beta version of the Ninja Shop Store Block. Full release coming soon.

*Bugs:*

* Fixed a bug where products were not showing in the Customer Data admin view for migrated transactions.
* Fixed a bug where telemetry was throwing notices in the admin.
* Fixed a bug where debugging code was introduced into live views.
* Added backwards compatibility back to shortcodes that was erroneously deleted.

= 1.1.9 =

*Bugs:*

* Fixed a bug with upgrade routines for membership and recurring payments.
* Fixed a bug with product descriptions stripping html tags on save.
* Fixed a bug not allowing the shipping address to be selected or updated in checkout.
* Added backwards compatibility back to shortcodes that was erroneously deleted.

= 1.1.8 =

*Bugs:*

* Fixed a menu icon conflict with iThemes products.
* Fixed a bug with clearing delivery for free purchases.

= 1.1.7 =

*Changes:*

* Re-branded "Add-Ons" as "Features".

*Bugs:*

* Fixed a bug with deleting US state taxe rates.
* Fixed a bug with state being required for countries that do not support states.
* Fixed a conflict with iThemes products, ie BackupBuddy and iThemes Security.
* Fixed a bug with US Taxes when switching between states at checkout.

= 1.1.6 =

*Bugs:*

* Extended coverage for MySQL versions that didn't support longer index key lengths.

= 1.1.5 =

*Bugs:*

* Fixed PHP compatibility issues with PHP version 5.6.*.

= 1.1.4 =

*Changes:*

* Update diagnostic reporting opt-in.

= 1.1.3 =

*Changes:*

* Add setting to opt-in/opt-out of diagnostic reporting.
* Add a Setup Wizard option to receive email updates about Ninja Shop.

*Bugs:*

* Fixed a nonce issue with updating a customer's billing address.

= 1.1.2 =

*Changes:*

* Introduce basic opt-in telemetry for product improvement.
* Restore "Buy Now" functionality along side multi-item cart.
* Add a query parameter listener to add items to the cart dynamically.

*Bugs:*

* Fixed mismatched css selectors in the related javascript.
* Fixed broken nonces related to the product rename.

= 1.1.1 =

*Changes:*

* Updated the checkout-flow for simplicity.
* Reset version references in the codebase for re-release.

*Bugs:*

* Fixed additional hook action/filter pattern renaming.
* Fixed a warning message with nested setting configurations.

= 1.1.0 =

*Changes:*

* Prepare Ninja Shop for public release.
* Include basic tax add-ons in the core plugin.

= 1.0.1 =

*Bugs:*

* Fixed a bug that, with some database configurations, caused a Fatal Error.

= 1.0 =

* Initial Release

== Screenshots ==

1. Quick Setup
2. Add New Digital Product
3. Add New Physical Product
4. Features
5. Customer Detail
6. Dashboard Widget
7. Payments
8. Email Settings
9. Custom Email Styling
10. Pages Settings
