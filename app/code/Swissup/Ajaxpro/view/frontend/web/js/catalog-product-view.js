define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/section-config',
    'underscore',
    'Swissup_Ajaxpro/js/get-product-view-request'
], function ($, customerData, sectionConfig, _, AjaxproGetProductViewRequest) {
    'use strict';

    var AjaxproCatalogProductView = {
        /**
         * Send ajax request
         * @param {Integer} productId
         */
        request: function (productId) {
            const { url, dataType, data } = AjaxproGetProductViewRequest.get(productId);

            $.ajax({
                url: url,
                dataType: dataType,
                data: data
            })
            .done(function (sections) {
                if (sections['ajaxpro-product']) {
                    customerData.set('ajaxpro-product', sections['ajaxpro-product']);
                    customerData.reload(['cart', 'messages']);
                }
            });
        }
    };

    function getLastUrlPart(href) {
        return href.substring(href.lastIndexOf('/') + 1);
    }

    $(document).on('ajaxComplete', function (event, xhr, settings) {
        var params = settings || xhr.settings,
            response = xhr.responseJSON || xhr.response?.body || {},
            sections = sectionConfig.getAffectedSections(params.url),
            isProductPage = $('body').hasClass('catalog-product-view'),
            isCartPage = $('body').hasClass('checkout-cart-index');

        if (!sections?.length ||
            !params.type.match(/post|put/i) ||
            !response?.ajaxpro?.product?.id
        ) {
            return;
        }

        const isTheSameProduct = getLastUrlPart(response?.backUrl) === getLastUrlPart(window.location.href);

        if (isProductPage && isTheSameProduct) {
            if (response?.backUrl) {
                customerData.reload(['messages']);
            }
            return;
        }

        if (isCartPage && params.url.includes('/in_cart/1/')) {
            window.location = response.backUrl;
            return;
        }

        AjaxproCatalogProductView.request(response.ajaxpro.product.id);
    });

    return AjaxproCatalogProductView;
});
