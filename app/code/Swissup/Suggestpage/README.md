# Suggest Page

The Magento 2 module Suggest Page gives your customers custom page, shown after
product was added to shopping cart.

## Installation

### For clients

Please do not install this module. It will be installed automatically as a dependency.

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup/suggestpage vcs git@github.com:swissup/suggestpage.git
composer require swissup/module-suggestpage
bin/magento module:enable Swissup_Suggestpage
bin/magento setup:upgrade
```
