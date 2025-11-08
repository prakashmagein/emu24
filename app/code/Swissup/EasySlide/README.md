# EasySlide

## Installation

### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)
 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/easyslider/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/easyslider/installation/manual/)

### For developers

Use this approach if you have access to our private repositories!

Instructions below are for Swissuplabs developers only!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-easy-slide --prefer-source
bin/magento module:enable Swissup_EasySlide Swissup_Core
bin/magento setup:upgrade
```

## Add slider in layout xml

```xml
<block class="Swissup\EasySlide\Block\Slider" name="easyslide.slider.name">
    <arguments>
        <argument name="identifier" xsi:type="string">slider-config-identifier</argument>
    </arguments>
</block>
```
