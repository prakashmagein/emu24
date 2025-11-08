# GDPR for Testimonials module

## Installation

### For clients

```bash
composer require swissup/module-gdpr-testimonials
bin/magento module:enable Swissup_GdprTestimonials
bin/magento setup:upgrade
```

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-gdpr-testimonials --prefer-source
bin/magento module:enable Swissup_GdprTestimonials
bin/magento setup:upgrade
```
