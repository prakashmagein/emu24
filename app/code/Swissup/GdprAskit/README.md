# GDPR for Askit module

## Installation

### For clients

```bash
composer require swissup/module-gdpr-askit
bin/magento module:enable Swissup_GdprAskit
bin/magento setup:upgrade
```

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer http://swissup.github.io/packages/
composer require swissup/module-gdpr-askit --prefer-source
bin/magento module:enable Swissup_GdprAskit
bin/magento setup:upgrade
```
