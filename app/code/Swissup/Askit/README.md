# Askit
<sup>It's a magento2-module for the [metapackage](https://github.com/swissup/askit).</sup>

### [Installation](https://docs.swissuplabs.com/m2/extensions/askit/installation/)

###### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)

 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/askit/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/askit/installation/manual/)

###### For maintainers

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-askit
composer require swissup/module-askit --prefer-source --ignore-platform-reqs
bin/magento module:enable Swissup_Askit Swissup_Core
bin/magento setup:upgrade
```
