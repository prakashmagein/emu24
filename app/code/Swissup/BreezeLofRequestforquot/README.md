# LOF Magento 2 Quote Extension Breeze Frontend Integration

## Required patches

In the [Breeze Settings](https://breezefront.com/docs/settings) section in theme configuration,
add `/quotation/quote/` to the `Disable Breeze for specified URLs` field.

`app/code/Lof/RequestForQuote/view/frontend/templates/ajax/success.phtml`

Replace code at lines 49 and 53:

```js
    parent.jQuery.fancybox.close();
```

with

```js
    $('#rfq-confirm').modal('closeModal');
    $(document).on('modal:closed', () => {
        $('.rfq-confirm-modal').remove();
    });
```

## Installation

```bash
composer require swissup/module-breeze-lof-requestforquote
bin/magento setup:upgrade --safe-mode=1
```
