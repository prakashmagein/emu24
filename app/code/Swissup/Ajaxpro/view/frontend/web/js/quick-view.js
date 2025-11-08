define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'mage/translate',
    'Swissup_Ajaxpro/js/get-product-view-request'
], function ($, _, $t, AjaxproGetProductViewRequest) {
    'use strict';

    var options = false;

    return {
        component: 'Swissup_Ajaxpro/js/quick-view',
        loader: null,
        options: {
            position: {
                actions: '.products.product-items .actions-secondary',
                image: '.product-items .product-item-info'
            }
        },

        'Swissup_Ajaxpro/js/quick-view': function (settings) {
            var position = getComputedStyle(document.querySelector('.products') || document.body)
                .getPropertyValue('--ajaxpro-quick-view-position');

            if (options === false) {
                options = settings;
                $.async(this.options.position[position || 'image'], this.renderLabel.bind(this));
            }
        },

        destroy: function () {
            options = false;
            $('.action.quick-view').remove();
        },

        renderLabel: function (element) {
            var targetElement = $(element),
                targetContainer = $(element).closest('.product-item-info'),
                self = this,
                productIdElement, productId,
                quickViewElement;

            if (targetElement.length !== 1) {
                return;
            }

            productIdElement = targetContainer.find('form [name="product"]');
            productId = false;

            if (productIdElement.length === 1) {
                productId = productIdElement.val();
            } else {
                productIdElement = targetContainer.find('.product-item-details div.price-box.price-final_price');
                if (productIdElement.length !== 1) {
                    return;
                }
                productId = productIdElement.data('product-id');
            }

            if (!productId) {
                return;
            }

            targetElement.append(
                '<a class="action quick-view" href="#" title="'+ $t('Quick View') + '">' +
                    '<span>' + $t('Quick View') + '</span>' +
                '</a>'
            );

            quickViewElement = targetContainer.find('a.quick-view');

            quickViewElement.on('click', function (e) {
                e.preventDefault();
                self.request(productId, quickViewElement);
            });
        },

        /**
         * Send ajax request
         * @param {Integer} productId
         * @param {Object} element
         */
        request: function (productId, element) {
            const { url, dataType, data } = AjaxproGetProductViewRequest.get(productId);
            const me = this;

            $.ajax({
                url: url,
                dataType: dataType,
                data: data,

                beforeSend: function () {
                    element.css('color', 'transparent');
                    require(['Swissup_Ajaxpro/js/loader'], function (loader) {
                        if (!me.loader) {
                            loader.setLoaderImage(options.loaderImage)
                                .setLoaderImageMaxWidth(options.loaderImageMaxWidth);
                            me.loader = loader;
                        }

                        me.loader.startLoader(element);
                    });
                },

                success: function (sections) {
                    var sectionName = 'ajaxpro-product';

                    element.css('color', '');
                    require(['Swissup_Ajaxpro/js/loader'], function () {
                        me.loader.stopLoader(element);
                    });

                    if (sections[sectionName]) {
                        require([
                            'Magento_Customer/js/customer-data'
                        ], function (customerData) {
                            customerData.set(sectionName, sections[sectionName]);
                            customerData.reload(['cart', 'messages']);
                        });
                    }
                }
            });
        }
    };
});
