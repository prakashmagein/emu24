# Quantity Switcher

Adds +/- buttons to the product pages that gives customers ability to change quantity

## Installation

### For clients

Please do not install this module. It will be installed automatically as a
Argento dependency.

### For developers

Use this approach if you have access to our private repositories!

```bash
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-quantity-switcher --prefer-source
bin/magento module:enable Swissup_Core Swissup_QuantitySwitcher
bin/magento setup:upgrade
```
