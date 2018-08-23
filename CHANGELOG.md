# Release Notes - CS-Cart4 1.3.0 (Aug 24, 2018) #

## Added ##
+ PLGCSCS-60: Add Afterpay as payment method
+ PLGCSCS-61: Add EPS as payment method
+ PLGCSCS-62: Add iDEALQR as payment method
+ PLGCSCS-63: Add TrustPay as payment method
+ PLGCSCS-40: Add Trustly as direct payment method to plugin
+ PLGCSCS-43: Add Alipay as payment method
+ PLGCSCS-54: Add Santander as payment method
+ PLGCSCS-23: Add payment logo's
+ PLGCSCS-5:  Add support for partial_refunded status

## Changed ##
+ PLGCSCS-59: Make logical sequence of displaying order statuses in the backend
+ PLGCSCS-53: Support direct transactions for PayPal
+ PLGCSCS-46: Support direct transactions for ING'HomePay / Alipay
+ PLGCSCS-30: Support direct transactions for KBC
+ PLGCSCS-39: Added disclaimer to new PHP files
+ PLGCSCS-77: Change defaults for order statuses
+ PLGCSCS-80: Status update on refund and partial refund

## Fixed ##
+ PLGCSCS-47: Refactor checkout_data when taxes are not set
+ PLGCSCS-44: Change gatewaycode for ING'HomePay to INGHOME
+ PLGCSCS-42: Make user notifications depend on status parameters instead of fixed
+ PLGCSCS-37: Surcharge title was not used in transaction requests
+ PLGCSCS-45: Rename KBC/CBC to KBC
+ PLGCSCS-38: Locale has wrong format within the transaction request

# Release Notes - CS-Cart4 1.2.0 (Jan 17, 2018) #

## Changes ##
+ Add Belfius & KBC/CBC & ING Direct to plugin
+ Send shopping cart data for all payment methods when creating transaction
+ Add PaySafeCard as payment method to plugin
+ Update header information
+ Code formatting
+ When selecting another currency then default the wrong values were added to the transaction
+ Install script did not update existing records
+ Correct Wallet gateway code
+ Empty Betaal na ontvangst Fee is added for every transaction
+ Fix checkout_data prices when taxes are used
+ Set correct payment fee id, the same for the shipping method

# Release Notes - CS-Cart4 1.1.0 (Jan 27, 2017) #

## Improvements ##
+ Add support for PHP-7

## Bugfix ##
+ Added missing templates for manual order creation using the backend

# Release Notes - CS-Cart4 1.0.2 (Dec 30, 2014) #

## Improvements ##
+ Better support for updating the orderstatus

# Release Notes - CS-Cart4 1.0.1 (Mar 24, 2014) #

## Improvements ##
+ Support for American Express

## Bugfix ##
+ Fixed bug with wiretransfer on returning.
+ Added billing country check for BnO. If billing country is not 'NL' then don't show gateway.
+ changed locale, use order language if available.

# Release Notes - CS-Cart4 1.0.0 (Nov 15, 2013) #

## New plug-in ##
+ Supports all payment methodes including Pay After Delivery
+ Support minimum and maximum value-restricions for showing a gateway.