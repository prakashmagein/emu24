define([
    'jquery',
    'sidebar',
    'mage/decorate',
    'mage/collapsible'
], function ($) {
    'use strict';

    $.widget('swissup.cartSidebar', $.mage.sidebar, {

        /**
         * @private
         */
        _initContent: function () {
            this.element.decorate('list', this.options.isRecursive);

            this._addEvents();
            this._calcHeight();

            if (typeof this._isOverflowed === 'function') {
                this._isOverflowed();
            }
        },

        /**
         *
         * @return {Object}
         */
        _getEvents: function () {
            var self, events = {};

            self = this;
            // /**
            //  * @param {jQuery.Event} event
            //  */
            // events['click ' + this.options.button.close] = function (event) {
            //     event.stopPropagation();
            //     $(self.options.targetElement).dropdownDialog('close');
            // };

            /**
             *
             */
            events['click ' + this.options.button.checkout] = function () {
                require([
                    'Magento_Customer/js/customer-data',
                    'Magento_Customer/js/model/authentication-popup',
                    'mage/cookies'
                ], function (customerData, authenticationPopup) {
                    var cart = customerData.get('cart'),
                        customer = customerData.get('customer');

                    if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                        // set URL for redirect on successful login/registration. It's postprocessed on backend.
                        $.cookie('login_redirect', self.options.url.checkout);

                        if (self.options.url.isRedirectRequired) {
                            location.href = self.options.url.loginUrl;
                        } else {
                            authenticationPopup.showModal();
                        }

                        return false;
                    }
                    location.href = self.options.url.checkout;
                });
            };


            /**
             * @param {jQuery.Event} event
             */
            events['click ' + this.options.button.remove] =  function (event) {
                event.stopPropagation();
                require([
                    'Magento_Ui/js/modal/confirm'
                ], function (confirm) {
                    confirm({
                        content: self.options.confirmMessage,
                        actions: {
                            /** @inheritdoc */
                            confirm: function () {
                                self._removeItem($(event.currentTarget));
                            },

                            /** @inheritdoc */
                            always: function (e) {
                                if (e && typeof e.stopImmediatePropagation === 'function') {
                                    e.stopImmediatePropagation();
                                }
                            }
                        }
                    });
                });
            };

            /**
             * @param {jQuery.Event} event
             */
            events['keyup ' + this.options.item.qty] = function (event) {
                self._showItemButton($(event.target));
            };

            /**
             * @param {jQuery.Event} event
             */
            events['click ' + this.options.item.button] = function (event) {
                event.stopPropagation();
                self._updateItemQty($(event.currentTarget));
            };

            /**
             * @param {jQuery.Event} event
             */
            events['focusout ' + this.options.item.qty] = function (event) {
                self._validateQty($(event.currentTarget));
            };

            return events;
        },

        /**
         * @private
         */
        _addEvents: function () {
            this._on(this.element, this._getEvents());
        },

        /**
         * @public
         */
        flushEvents: function () {
            // console.log(Object.keys(this._getEvents()).join(','));
            this._off(this.element);
            this._addEvents();
        },

        /**
         * @param {HTMLElement} elem
         * @private
         */
        _showItemButton: function (elem) {
            var itemId = elem.data('cart-item'),
                itemQty = elem.data('item-qty');

            if (this._isValidQty(itemQty, elem.val())) {
                $(this.element).find('#update-cart-item-' + itemId).show('fade', 300);
            } else if (elem.val() == 0) { //eslint-disable-line eqeqeq
                this._hideItemButton(elem);
            } else {
                this._hideItemButton(elem);
            }
        },

        /**
         * @param {HTMLElement} elem
         * @private
         */
        _hideItemButton: function (elem) {
            var itemId = elem.data('cart-item');

            $(this.element).find('#update-cart-item-' + itemId).hide('fade', 300);
        },

        /**
         * @param {HTMLElement} elem
         * @private
         */
        _updateItemQty: function (elem) {
            var itemId = elem.data('cart-item');
            var itemQty = $(this.element).find('#cart-item-' + itemId + '-qty');

            if (itemQty.length === 0) {
                itemQty = $(this.element).find('input[data-cart-item="' + itemId + '"]');
            }

            this._ajax(this.options.url.update, {
                'item_id': itemId,
                'item_qty': itemQty.val()
            }, elem, this._updateItemQtyAfter);
        }

    });

    return $.swissup.cartSidebar;
});
