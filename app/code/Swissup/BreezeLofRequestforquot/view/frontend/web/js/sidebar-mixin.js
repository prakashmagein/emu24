(function () {
    'use strict';

    $.mixin('sidebar', {
        _addObservers: function (original) {
            var self = this;

            this.element.on('click', this.options.button.checkout, function () {
                var cart = $.sections.get('cart'),
                    customer = $.sections.get('customer'),
                    checkoutUrl = typeof(self.options.url.submitQuote) != "undefined" ? self.options.url.submitQuote : self.options.url.checkout;

                if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                    $.cookies.set('login_redirect', self.options.url.checkout);

                    if (self.options.url.isRedirectRequired) {
                        $(this).prop('disabled', true);
                        location.href = self.options.url.loginUrl;
                    } else {
                        $('.block-authentication').modal('openModal');
                    }

                    return false;
                }

                $(this).prop('disabled', true);
                location.href = checkoutUrl;
            }).on('click', this.options.button.remove, function (event) {
                event.preventDefault();
                event.stopPropagation();

                $.confirm({
                    content: self.options.confirmMessage,
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            self._removeItem($(event.currentTarget));
                        },

                        /** @inheritdoc */
                        always: function (e) {
                            e.stopImmediatePropagation();
                        }
                    }
                });
            }).on('keyup change', this.options.item.qty, function (event) {
                self._showItemButton($(event.target));
            }).on('click', this.options.item.button, function (event) {
                event.stopPropagation();
                self._updateItemQty($(event.currentTarget));
            }).on('focusout', this.options.item.qty, function (event) {
                self._validateQty($(event.currentTarget));
            });
        },

        _updateItemQty: function (original, elem) {
            var itemId = elem.data('cart-item'),
                updateUrl = typeof(this.options.url.updateQuoteItem) != "undefined" ? this.options.url.updateQuoteItem : this.options.url.update;

            this._ajax(updateUrl, {
                'item_id': itemId,
                'item_qty': this.element.find('#cart-item-' + itemId + '-qty').val()
            }, elem, this._updateItemQtyAfter);
        },

        _removeItem: function (original, elem) {
            var removeUrl = typeof(this.options.url.removeQuoteItem) != "undefined" ? this.options.url.removeQuoteItem : this.options.url.remove;

            this._ajax(removeUrl, {
                'item_id': elem.data('cart-item')
            }, elem, this._removeItemAfter);
        },

        _getProductById: function (original, productId) {
            var productData = original(productId);

            if (productData === undefined) {
                productData = $.sections.get('cart')().items.find(function (item) {
                    return productId === Number(item['item_id']);
                });
            }

            if(productData === undefined){
                productData = $.sections.get('quote')().items.find(function (item) {
                    return productId === Number(item['item_id']);
                });
            }

            return productData;
        }
    });
})();
