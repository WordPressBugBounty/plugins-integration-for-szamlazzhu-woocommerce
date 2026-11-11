=== Integration for Szamlazz.hu and WooCommerce ===
Contributors: passatgt
Tags: szamlazz.hu, szamlazz, woocommerce, szamlazo, magyar
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 6.3.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connect WooCommerce with Számlázz.hu, the Hungarian online invoicing service, and create invoices for your orders.

== Description ==

> **PRO version**
> A PRO version of the plugin is available from €30 per year, which you can buy here: [https://visztpeter.me](https://visztpeter.me/woocommerce-szamlazz-hu/)
> The license key can be activated on one website and is valid for 1 year. It includes e-mail support for setup, customization and configuration.
> Buying it also supports the development of the plugin, even if you don't need the features of the PRO version.

= Features =

* **Invoice generation**
Create an invoice for any order with one click:
 * The generated invoice is saved in the order details.
 * The invoice PDF is also downloaded to your website and stored with a random file name in the wp-content/uploads/wc_szamlazz folder.
 * You can set a payment deadline and a note for the invoices.
 * Works with coupons, they appear as negative line items on the invoice.
 * Shipping is added to the invoice too.
 * The PDF can be downloaded right from the Orders table, using the order actions or the optional Számlázz.hu column.

* **Proforma, deposit and reverse invoices, delivery notes**
You can create every document type supported by Számlázz.hu for your orders.

* **Bulk actions**
Use the bulk actions on the Orders page to create invoices and to download or print them all at once.

* **VAT number field**
A new VAT number field is added to the billing details. The VAT number is validated, saved, and shown in the e-mail sent to the customer and in the order details.

* **Logging**
The order notes show exactly when and which document was created.

* **Mark as paid automatically**
Choose the payment methods for which the invoice should be created as already paid.

* **International invoices**
If you sell abroad, for example in euros, you can change the language of the invoice and show the current exchange rate of the Hungarian National Bank (MNB) on it. Compatible with WPML and Polylang.

* **And much more**
Paper and electronic invoices, VAT rate settings, invoice number prefix, invoices downloadable from the customer's account, e-mail notification about failed invoice generation and more.

= Available in the PRO version =

* **Automatic invoice generation**
Create invoices and other documents (proforma and deposit invoices, delivery notes, reverse invoices) automatically based on the order status, with custom conditions.

* **Invoice notification**
In the free version, Számlázz.hu sends the invoice notification to the customer. In the PRO version, the invoice can be attached to the e-mails sent by WooCommerce, so you don't need to use the Számlázz.hu invoice notification and your customer gets one less e-mail.

* **Automatic handling of bank transfers**
With the Számlázz.hu Autokassza feature, your store can be notified when a proforma invoice is paid, and the invoice can be marked as paid automatically.

* **Special VAT rates**
Set special VAT rates (EUKT, EUT, EUFADE, etc.) for certain products on the invoice, based on custom conditions.

* **Bulk actions**
With the bulk document generation action you can create not just invoices, but proforma and deposit invoices, delivery notes and reverse invoices too.

* **Override invoice data**
Use conditions to change the prefix and bank account details shown on the invoice.

* **E-Receipt**
If you sell electronic products, tickets or downloadable content, you don't need to ask for the customer's billing address, just the e-mail address, and the plugin creates an electronic receipt.

* **Fill in company details automatically**
The VAT number field is validated with the Számlázz.hu API and the billing details are filled in automatically.

* **EU VAT number**
The VAT number field accepts EU VAT numbers too, and removes VAT from the order automatically if the number is valid (validated with the VIES API).

* **Accounting details**
Set accounting details for each product category, separately for Hungarian and foreign purchases: general ledger number, revenue ledger number, economic event and VAT economic event. The user ID is saved as the customer's ledger ID.

= Usage =
You can find the detailed documentation [here](https://visztpeter.me/dokumentacio/).
After installation, enter your Agent key on the WooCommerce / Settings / Számlázz.hu page.
A new box appears on the right side of every order, where you can create the invoice with one click. With the Options button, you can override the values from the settings for a single invoice.
If automatic invoice generation is turned on, the invoice is created automatically when the order status changes.
Invoice generation can be turned off for a single order in the Options dropdown.
The finished invoice can be downloaded on the order page, or by clicking the PDF icon in the last column of the Orders table.

**IMPORTANT:** Always check that the invoices are created correctly, and ask your accountant whether the generated invoices are right for them too. I can't test every possible case (different VAT rates, product variations, coupons, etc.), so make sure to test it before using it on a live store. If something is wrong, let me know and I'll try to fix it.

= For developers =

The plugin generates an XML file and sends it to Számlázz.hu, which sends back the finished invoice as a PDF. The XML can be modified before it's sent with the `wc_szamlazz_xml` filter. Always add your changes to the functions.php file of your active theme, so a plugin update won't remove them. You can find more developer documentation [here](https://visztpeter.me/dokumentacio/).

== Installation ==

1. Download the plugin
2. Upload it in WordPress under Plugins / Add New
3. You'll find the settings under WooCommerce / Settings / Számlázz.hu. At least the Agent key field must be filled in.
4. Done

== Frequently Asked Questions ==

= What is the difference between the PRO and the free version? =

The PRO version has a lot of useful features, which you can read about [here](https://visztpeter.me/woocommerce-szamlazz-hu/). The most important one is automatic invoice generation. It also includes 1 year of e-mail support.

= How can I test invoice generation? =

You can turn on [test mode](https://tudastar.szamlazz.hu/gyik/szamlazz-hu-kipobalasa-elofizetes-nelkul) in your Számlázz.hu account.

= I'm in test mode, but the invoice notification isn't sent to the customer's e-mail address. =

In test mode, the invoice notification isn't sent to the customer's e-mail address, but to the e-mail address of your Számlázz.hu account.

== Screenshots ==

1. General settings
2. VAT rates and tax settings
3. Invoice line item settings
4. VAT number field settings
5. Automation settings
6. Invoice notification settings
7. E-Receipt settings
8. Invoice box on the order page

== Changelog ==

6.3.2
* WooCommerce Block e-mail editor fix
* EU VAT exempt only if shipping country is the same

6.3.1
* Security fix: the VAT number field is validated and cleaned on the block checkout, and a validation result saved in the session is only used for the same VAT number
* Security fix: all data shown in the invoice preview is escaped
* Security hardening: input handling, output escaping and nonce checks improved across the plugin
* Marked compatible with the latest WP version

6.3
* More secure PDF storage and access: handled by a new PHP class (WC_Szamlazz_Document_Storage), with new filters and actions: wc_szamlazz_can_download_document, wc_szamlazz_download_file_name, wc_szamlazz_before_document_download
* wp_remote_post instead of cURL (simpler, more secure, filterable)
* Fixed an error when deleting an order
* Fixed EU VAT number validation on the block checkout
* Marked compatible with the latest WP and Woo versions

6.2.4
* Fixed printing in Safari
* Marked compatible with the latest WP and Woo versions
* The number of decimals used for calculations can be changed with the wc_szamlazz_default_rounding_precision filter

6.2.3
* EU VAT number fixes

6.2.2
* EU VAT number validation improvements
* Fixed IPN with HPOS and custom order numbers

6.2.1
* WP 7.0 compatibility fixes
* A reason can be entered when reversing an invoice manually

6.2
* VAT number field for all countries: with this setting, the required VAT number field is shown for non-EU countries too, but its value isn't validated
* The wc_szamlazz_vat_number_validation_results filter also runs for invalid VAT numbers, so the result can be changed

6.1.16
* Fixed product category and tag conditions (takes into account when the order has multiple categories and tags)
* "Only" condition (e.g. only runs if the order contains only the selected category)
* Marked compatible with the latest WC version

6.1.15
* Custom logo setting field

6.1.14
* wc_szamlazz_deposit_negative_lines filter
* Marked compatible with the latest WC version
* Fixed EU VAT number validation
* Product tag condition

6.1.13
* Fixed 21% VAT rate

6.1.12
* HO VAT rate
* Marked compatible with WP 6.9 and WC 10.3.6
* Fixed EU VAT number validation (treats the number as valid if there's a rate limit error)

6.1.11
* Fix for the previous version

6.1.10
* Compatibility with WooCommerce versions before 10.3

6.1.9
* Fixed receipt generation

6.1.8
* Compatibility fix with the latest Woo version

6.1.7
* Added missing percentage VAT rates
* Marked compatible with the latest Woo version
* EU VAT number validation fixes

6.1.6
* Fixed invoice e-mail attachment
* Fixed invoice notification text setting
* Fixed erasure code

6.1.5
* wc_szamlazz_ipn_target_order_status filter
* Erasure code option in the product settings Advanced tab (if checked, an erasure code is added to the line item automatically)
* 21% VAT rate
* Marked compatible with the latest Woo version

6.1.4
* Delivery notes can be attached to e-mails too
* Radio button selector for company invoicing on the classic checkout

6.1.3
* Fixed shipping cost VAT calculation in the latest Woo version

6.1.2
* Compatibility with the latest Woo version

6.1.1
* Fixed PHP notice
* User role condition

6.1.0.1
* Fixed Woo Payments setting

6.1
* VAT number bug fix on the block checkout
* wc_szamlazz_defer_invoice_in_bulk_action filter
* Fixed TranslatePress compatibility

6.0.16
* JS bug fix
* Marked compatible with the latest WP/WC versions

6.0.15
* Fixed PRO version modal

6.0.14
* Aggregator code
* Marked compatible with the latest WC version

6.0.13
* Fixed changing the VAT number with WooCommerce Subscriptions

6.0.11 & 6.0.12
* Fixed EU VAT number field on the checkout block

6.0.10
* Fixed VAT number field on the block checkout

6.0.9
* EU VAT number validation fixes
* A new delivery note can be created even if one already exists (it replaces the existing one)

6.0.8
* Fixed WPML compatibility

6.0.7
* Fixed a fatal error on the checkout page when e-mail sending was set up

6.0.6
* Fixed Loco Translate compatibility
* Marked compatible with the latest WP/WC versions

6.0.5
* Fixed PHP warning related to the VAT number

6.0.4
* Fixed the paper/electronic invoice switch for companies

6.0.3
* Checkout block VAT number fix
* Marked compatible with the latest WP/WC versions

6.0.2
* Fixed the invoices in the customer account feature
* Fixed Checkout Manager compatibility

6.0.1
* WooCommerce Subscriptions fix
* Polylang fix

6.0
* New interface for bulk document generation: the page doesn't reload, you can follow the whole process in a window
* New bulk print and download feature: all documents can be downloaded or printed at once without reloading the page
* Completely new settings page: a Számlázz.hu main menu split into submenus for better clarity
* You can set the order status to change to after invoice generation (and during bulk generation you see the change without reloading the page)
* Paid (or unpaid) invoices can be generated when creating invoices manually too
* The custom automation option is now the default (new installs only show this one)
* The completion date can be set for delivery note automations too
* Smaller size: 2.5 MB instead of 4.3 MB (JS-based PDF printing instead of PHP)
* WooCommerce marked as a required plugin, so this plugin can only be activated when WooCommerce is active (this also fixes an activation bug)
* Removed all admin notice messages
* The {current_date} placeholder can be used in the note to show the current date (useful for warranty cards, for example)
* The VAT number field can be made required
* Fixed EUK VAT rate when asking for your own EU VAT number
* Fixed a bug with VAT rate overrides
* Fixed the VAT number block in Firefox
* Fixed EU VAT number validation in some server environments
* Fixed data overrides in the condition settings
* Fixed conditional mode settings
* Grouped invoice generation is visible with HPOS too
* Removed old compatibility code
* VAT number bug fix on the block checkout
* The VAT number validation result can be changed with the wc_szamlazz_vat_number_validation_results filter
* WooCommerce Subscriptions compatibility fix: existing proforma invoices are also deleted in a new subscription cycle
* Set minimum PHP (7.4) and WP (6.0) versions
* Marked compatible with the latest WP/WC versions
