define([
    'jquery',
    'mage/translate',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'Magento_Catalog/js/product/view/product-info-resolver',
    'Magento_Catalog/js/catalog-add-to-cart',
    './catalog-product-view',
    'jquery-ui-modules/widget'
], function ($, $t, idsResolver, productInfoResolver) {
    'use strict';

    $.widget('swissup.catalogAddToCart', $.mage.catalogAddToCart, {
        ajaxSubmitProcessing: false,

        /**
         * Bind new Submit
         * @private
         */
        _bindSubmit: function () {
            var self = this,
            isValidation = !!this.options.submitForcePreventValidation;

            if (this.element.data('catalog-addtocart-initialized')) {
                return;
            }
            this.element.data('catalog-addtocart-initialized', true);

            if (isValidation) {
                this.element.mage('validation');
            }
            this.element.on('submit', function (e) {
                e.preventDefault();

                if (isValidation) {
                    if (self.element.valid()) {
                        self.submitForm($(this));
                    }
                } else {
                    self.submitForm($(this));
                }
            });
        },

        /**
         * Send ajax request
         * @param {jQuery} form
         */
        ajaxSubmit: function (form) {
            var self = this,
                productIds = idsResolver(form),
                productInfo = self.options.productInfoResolver(form),
                formData;

            if (self.ajaxSubmitProcessing) {
                return;
            }

            self.ajaxSubmitProcessing = true;

            $(self.options.minicartSelector).trigger('contentLoading');
            self.disableAddToCartButton(form);
            formData = new FormData(form[0]);

            $.ajax({
                url: form.attr('action'),
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,

                /** @inheritdoc */
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },

                /** @inheritdoc */
                success: function (res) {
                    var eventData, parameters;

                    $(document).trigger('ajax:addToCart', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'productInfo': productInfo,
                        'form': form,
                        'response': res
                    });

                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }

                    if (res && res.backUrl && !res.ajaxpro) {
                        eventData = {
                            'form': form,
                            'redirectParameters': []
                        };
                        // trigger global event, so other modules will be able add parameters to redirect url
                        $('body').trigger('catalogCategoryAddToCartRedirect', eventData);

                        if (eventData.redirectParameters.length > 0) {
                            parameters = res.backUrl.split('#');
                            parameters.push(eventData.redirectParameters.join('&'));
                            res.backUrl = parameters.join('#');
                        }

                        if (self._redirect) {
                            self._redirect(res.backUrl);
                        } else {
                            window.location = res.backUrl;
                        }

                        return;
                    }

                    if (res && res.messages) {
                        $(self.options.messagesSelector).html(res.messages);
                    }

                    if (res && res.minicart) {
                        $(self.options.minicartSelector).replaceWith(res.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }

                    if (res && res.product && res.product.statusText) {
                        $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    self.enableAddToCartButton(form, res);
                },

                /** @inheritdoc */
                error: function (res) {
                    $(document).trigger('ajax:addToCart:error', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'form': form,
                        'response': res
                    });
                },

                /** @inheritdoc */
                complete: function (res) {
                    if (res.state() === 'rejected') {
                        location.reload();
                    }
                    self.ajaxSubmitProcessing = false;
                    $(self.options.minicartSelector).trigger('contentUpdated');
                    //removeBlockLoader
                    const blockContentLoadingClass = '_block-content-loading';
                    const blockLoaderClass = 'loading-mask';
                    $(self.options.minicartSelector).find('.' + blockLoaderClass).remove();
                    $(self.options.minicartSelector).find('.' + blockContentLoadingClass)
                        .removeClass(blockContentLoadingClass);
                }
            });
        },

        /**
         * Override default function and add ajax behaviour
         *
         * @param  {Element} form
         * @param  {Object} response
         */
        enableAddToCartButton: function (form, response) {
            var self = this,
            addToCartButton = $(form).find(this.options.addToCartButtonSelector),
            timeout = 1500,
            isAjaxproProductView = false;

            response = response || {};

            isAjaxproProductView = response && response.ajaxpro &&
                response.ajaxpro.product &&
                response.ajaxpro.product['has_options'];

            if (!isAjaxproProductView) {
                setTimeout(function () {
                    var addToCartButtonTextAdded = self.options.addToCartButtonTextAdded || $t('Added');

                    addToCartButton.find('span').text(addToCartButtonTextAdded);
                    addToCartButton.attr('title', addToCartButtonTextAdded);
                }, timeout);
            }

            setTimeout(function () {
                var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');

                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(addToCartButtonTextDefault);
                addToCartButton.attr('title', addToCartButtonTextDefault);
            }, timeout * 2);
        }
    });

    return $.swissup.catalogAddToCart;
});
