# Hover Gallery

## Installation

### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)
 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/hover-gallery/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/hover-gallery/installation/manual/)

### For developers

Use this approach if you have access to our private repositories!

```bash
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-hover-gallery --prefer-source
bin/magento module:enable\
    Swissup_Core\
    Swissup_HoverGallery
bin/magento setup:upgrade
```

## Usage

Insert the code to `Magento_Catalog/templates/product/list.phtml` template right
after `<?= $productImage->toHtml() ?>` line:

```php
<?php
    if ($this->helper('Magento\Catalog\Helper\Data')->isModuleOutputEnabled('Swissup_HoverGallery')) {
        /* $imageDisplayArea is a string like 'category_page_grid' or 'category_page_list' */
        echo $this->helper('Swissup\HoverGallery\Helper\Data')->renderHoverImage($_product, $imageDisplayArea);
    }
?>
```
