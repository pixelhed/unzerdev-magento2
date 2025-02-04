# Release Notes - Payment extension for Magento2 and Unzer Payment API (PAPI)
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.3.1](https://github.com/unzerdev/magento2/compare/2.3.0..2.3.1)

### Fixed
* removed old files, causing fatal errors

## [2.3.0](https://github.com/unzerdev/magento2/compare/2.2.0..2.3.0)

### Added
* Support for Magento 2.4.6 and PHP 8.2
* license files and missing licence texts in some php files

### Changed
* refactoring of old code to take advantage of newer PHP versions and to be compliant with Magento Coding Standards as far as possible

### Fixed
* "pending" order status with payment methods, which use redirects to external pages, like PayPal. The Status "pending_payment" is now set before the redirect happens, so Magento can cancel abandoned orders automatically
* problems with bundle products and how discounts are transferred to the Unzer servers. Previously discounts for cart items would have been transferred to the Unzer Servers per item. Now only the sum of all discounts for the whole cart is transferred, otherwise we would end up with rounding errors.

## [2.2.0](https://github.com/unzerdev/magento2/compare/2.1.1..2.2.0)

### Added
* Magento Vault support to credit card and PayPal payment methods
### Changed
* requirement of Unzer PHP SDK to use their new 3-digit versioning
### Removed
* support for end-of-life PHP Versions 7.1, 7.2, 7.3

## [2.1.1](https://github.com/unzerdev/magento2/compare/2.1.0..2.1.1)

### Fixed
* Checkout Problems with Bundle Products and Discounts

## [2.1.0](https://github.com/unzerdev/magento2/compare/2.0.0..2.1.0)

### Added
* new Payment Method Apple Pay

## [2.0.0](https://github.com/unzerdev/magento2/compare/1.4.2..2.0.0)

### Added
* new Payment Methods Paylater Invoice B2C and Paylater Invoice B2B
* Payment Methods Paylater Invoice B2C/B2B have a new setting to override general API Keys and use separate ones
  * Attention! The changes we had to make here, might be backwards incompatible changes, affecting all payment methods, depending on your own extensions of this module.

### Fixed
* Cancel of authorization payment methods (credit card / paypal) not being send to unzer account, resulting in an "offline" Cancel. Now "Online" Cancel is possible.
* Void of authorization payment methods (credit card / paypal) is now possible
* Order Emails now being send for method Unzer Prepayment

## [1.4.2](https://github.com/unzerdev/magento2/compare/1.4.1..1.4.2)
### Fixed
* php short tag in backend order template
* totals update in backend order
* invoice email not send in backend order
* state not correct on backend order creation

## [1.4.1](https://github.com/unzerdev/magento2/compare/1.4.0..1.4.1)
### Fixed
* Prices of basket items not including tax  
* basket items missing tax percent and reference id

## [1.4.0](https://github.com/unzerdev/magento2/compare/1.3.0..1.4.0)
### Added
* Requirement for unzerdev/php-sdk 1.2.x
* Support for backend order creation to Unzer Invoice Secured payment method

### Changed
* Authorization and capture handling to use unzerdev/php-sdk 1.2.x

### Fixed
* multiple order emails being sent in some cases
* invoice or credit memo emails showing total amount or negative amount instead of due amount in some cases

## [1.3.0](https://github.com/unzerdev/magento2/compare/1.2.0..1.3.0)
### Added
* configuration setting to be able to switch between base currency or customer (storeview) currency for transfers to unzer servers
* Payment Method Alipay
* Payment Method Bancontact (only Belgium)
* Payment Method Przelewy 24 (only Poland)
* Payment Method Wechat
* Payment Method Unzer Prepayment

### Fixed 
* amount and currency not matching on multistore installations with multiple currencies
* Fix an issue where the customer form was not rendered in checkout sometimes. Invoice Secured B2C/B2B and Sepa Direct Debit B2C were affected by that.

## [1.2.0](https://github.com/unzerdev/magento2/compare/1.1.1..1.2.0)
### Changed
* PHP 8.1 Compatibility
 
## [1.1.1](https://github.com/unzerdev/magento2/compare/1.1.0..1.1.1)

### Changed
* If no, or invalid keys are configured payment methods are not active in checkout.
* Update broken documentation links in readme.
* Set minimum php-sdk version [1.1.4.2](https://github.com/unzerdev/php-sdk/releases/tag/1.1.4.2).
* Change translation keys of invoice payment methods to avoid translation conflicts with shop system.
* Display Module version in Backend configuration.
* Several minor improvements.

### Fix
* Empty public key causing an exception in checkout.

## [1.1.0](https://github.com/unzerdev/magento2/compare/1.0.0..1.1.0)
### Added
*   Payment method EPS.
*   Payment method Giropay.

### Changed
* Checkout will be aborted now if customer creation fails. The error message will be displayed in checkout.
* Allow configuration of booking mode on store level for "Credit Card / Debit Card" and "PayPal".
* If possible, display a more descriptive message to the customer if card submission fails.

## [1.0.0](https://github.com/unzerdev/magento2/compare/06675c1be6009ce9f4e4cc78f8eecfc8447b2f5d..1.0.0)
### Changed
* Rebranding of the Plugin.
* Remove preconfigured test keypair from config.
* Switch to Unzer PHP SDK.
* Switch to Unzer UI components.
* Fixed an issue regarding inconsistent dependency of `messageManager` used in `AbstractPaymentAction`.
* Controller uses already existing `_redirect()` method for redirects now.
* Added necessary sources to whitelist for content security policy.

### Fix
* `Sepa Direct Debit Secured` now uses the merchant name configured in this payment method for the sepa direct debit mandate text. Previously the merchant name configured in `Sepa Direct Debit` was used.
* Adjust payment method templates: checkout-agreements-block moved beneath the payment form to avoid css conflicts that can causing the checkbox being not clickable.

[1.0.0]: https://github.com/unzerdev/magento2/compare/06675c1be6009ce9f4e4cc78f8eecfc8447b2f5d..1.0.0
