/**
 * https://codyhouse.co/demo/add-to-cart-interaction/index.html
 */
define([
    'Magento_Checkout/js/view/minicart',
    'Magento_Customer/js/customer-data',
    'jquery',
    'Swissup_Ajaxpro/js/view/cart-sidebar',
    'mage/translate'
], function (Component, customerData, $) {
    'use strict';

    var addToCartCalls = 0,
        cartContainer,
        initialized = false,
        checkoutConfig;

    cartContainer = $('[data-block=\'ajaxpro-floating-cart\'] .cd-cart-container');

    if (window.checkout) {
        checkoutConfig = window.checkout;
    }

    return Component.extend({
        checkoutUrl: checkoutConfig.checkoutUrl,

        /**
         * @override
         */
        initialize: function (options) {
            var self = this,
                cartData = customerData.get('cart');

            checkoutConfig = window.checkout ? window.checkout : options['checkout_lite_config'];

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                this.update(updatedCart);
                self.initFloatingCart();
            }, this);
            $('[data-block="ajaxpro-floating-cart"]').on('contentLoading', function () {
                addToCartCalls++;
                self.isLoading(true);
            });

            if (cartData()['website_id'] !== checkoutConfig.websiteId) {
                customerData.reload(['cart'], false);
            }

            return this._super();
        },

        /**
         * @return {Boolean}
         */
        initFloatingCart: function () {

            if ($('[data-block=\'ajaxpro-floating-cart\'] .cd-cart-container').get(0) !== cartContainer.get(0)) {
                cartContainer = $('[data-block=\'ajaxpro-floating-cart\'] .cd-cart-container');
                initialized = false;
            }

            if (this.getCartLineItemsCount() > 0 && cartContainer.hasClass('empty')) {
                cartContainer.removeClass('empty');
            } else if (this.getCartLineItemsCount() === 0 && !cartContainer.hasClass('empty')) {
                cartContainer.removeClass('cart-open');
                cartContainer.addClass('empty');
            }

            $('[data-block=\'ajaxpro-floating-cart\']').trigger('contentUpdated');

            $('[data-block=\'ajaxpro-floating-cart\'] .cd-cart-trigger').off().on('click', function (event) {
                var cartIsOpen;

                event.preventDefault();

                cartIsOpen = cartContainer.hasClass('cart-open');

                if (cartIsOpen) {
                    $('body').removeClass('swissup-ajaxpro-floating-cart-open');
                    cartContainer.removeClass('cart-open');
                } else {
                    cartContainer.addClass('cart-open');
                    $('body').addClass('swissup-ajaxpro-floating-cart-open');
                }
            });

            cartContainer.off().on('click', function (event) {
                var target, currentTarget;

                target = $(event.target);
                currentTarget = $(event.currentTarget);

                if (cartContainer.hasClass('cart-open') && target.get(0) === currentTarget.get(0)) {
                    cartContainer.removeClass('cart-open');
                }
            });

            if (cartContainer.data('swissupCartSidebar')) {
                cartContainer.cartSidebar('update');
                cartContainer.cartSidebar('flushEvents');
            }

            cartContainer.trigger('contentUpdated');

            if (initialized) {

                return true;
            }

            initialized = true;
            cartContainer.cartSidebar({
                'targetElement': '[data-block=\'ajaxpro-floating-cart\'] .cd-cart-container',
                // 'targetElement': 'div.block.block-minicart',
                'url': {
                    'checkout': checkoutConfig.checkoutUrl,
                    'update': checkoutConfig.updateItemQtyUrl,
                    'remove': checkoutConfig.removeItemUrl,
                    'loginUrl': checkoutConfig.customerLoginUrl,
                    'isRedirectRequired': checkoutConfig.isRedirectRequired
                },
                'button': {
                    'checkout': '#top-cart-btn-checkout',
                    'remove': 'a.action.delete',
                    'close': '#btn-minicart-close'
                },
                'showcart': {
                    'parent': 'span.counter',
                    'qty': 'span.counter-number',
                    'label': 'span.counter-label'
                },
                'minicart': {
                    'list': '[data-block=\'ajaxpro-floating-cart\'] #mini-cart',
                    'content': '#minicart-content-wrapper',
                    'qty': 'div.items-total',
                    'subtotal': 'div.subtotal span.price',
                    'maxItemsVisible': checkoutConfig.minicartMaxItemsVisible
                },
                'item': {
                    'qty': ':input.cart-item-qty',
                    'button': ':button.update-cart-item'
                },
                'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the shopping cart?')
            });

            return true;
        }
    });
});
