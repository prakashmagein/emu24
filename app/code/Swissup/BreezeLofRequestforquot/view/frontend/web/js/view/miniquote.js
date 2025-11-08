(function () {
    'use strict';

    $.view('miniquote', {
        component: 'Lof_RequestForQuote/js/view/miniquote',
        defaults: {
            template: 'Swissup_BreezeLofRequestforquote/miniquote/content',
        },
        submitQuoteUrl: window.quotation.submitQuoteUrl,
        quote: {},
        shouldRender: ko.observable(false),
        isLoading: ko.observable(false),
        displaySubtotal: ko.observable(true),
        addToCartCalls: 0,
        minicartSelector: '[data-block="miniquote"]',

        create: function () {
            var self = this,
                cartData = $.sections.get('quote');

            this.update(cartData());

            this.cartSubscription = cartData.subscribe(function (updatedCart) {
                self.addToCartCalls--;
                self.isLoading(self.addToCartCalls > 0);
                self.update(updatedCart);
                self.initSidebar();
            });

            this.minicart()
                .one('dropdownDialog:open', function () {
                    self.shouldRender(true);
                })
                .on('dropdownDialog:open', function () {
                    self.initSidebar();
                })
                .on('contentLoading', function () {
                    self.addToCartCalls++;
                    self.isLoading(true);
                })
                .on('contentSkipped', function () {
                    self.addToCartCalls--;
                    self.isLoading(self.addToCartCalls > 0);
                });

            self.isLoading(true);
            $.sections.reload(['quote'], false);
        },

        destroy: function () {
            this.cartSubscription.dispose();
            this._super();
        },

        minicart: function () {
            return $(this.minicartSelector);
        },

        initSidebar: function () {
            var minicart = this.minicart(),
                sidebar = minicart.sidebar('instance');

            minicart.trigger('contentUpdated');

            if (sidebar) {
                return sidebar.update();
            }

            if (!$('[data-role=product-item]').length) {
                return false;
            }

            minicart.sidebar(this.getSidebarSettings());
        },

        getSidebarSettings: function () {
            return {
                'targetElement': 'div.block.block-miniquote',
                'url': {
                    'submitQuote': window.quotation.submitQuoteUrl,
                    'updateQuoteItem': window.quotation.updateItemQtyUrl,
                    'removeQuoteItem': window.quotation.removeItemUrl,
                    'loginUrl': window.quotation.customerLoginUrl,
                    'isRedirectRequired': window.quotation.isRedirectRequired
                },
                'button': {
                    'checkout': '#top-quote-btn-sumit',
                    'remove': '#mini-quote a.action.delete',
                    'close': '#btn-miniquote-close'
                },
                'showquote': {
                    'parent': 'span.counter',
                    'qty': 'span.counter-number',
                    'label': 'span.counter-label'
                },
                'minicart': {
                    'list': '#mini-quote',
                    'content': '#miniquote-content-wrapper',
                    'qty': 'div.items-total',
                    'subtotal': 'div.subtotal span.price',
                    'maxItemsVisible': window.quotation.miniquoteMaxItemsVisible
                },
                'miniquote': {
                    'list': '#mini-quote',
                    'content': '#miniquote-content-wrapper',
                    'qty': 'div.items-total',
                    'subtotal': 'div.subtotal span.price',
                    'maxItemsVisible': window.quotation.miniquoteMaxItemsVisible
                },
                'item': {
                    'qty': 'input.quote-item-qty',
                    'button': 'button.update-quote-item'
                },
                'confirmMessage': $.__(
                    'Are you sure you would like to remove this item from your quote list?'
                )
            };
        },

        /** Close mini shopping cart. */
        closeMinicart: function () {
            this.minicart().find('[data-role="dropdownDialog"]').dropdownDialog('close');
        },

        closeSidebar: function () {
            var miniquote = this.minicart();
            miniquote.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                miniquote.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {Object} item
         * @return {*|String}
         */
        getItemRenderer: function (item) {
            var renderer = this.options.itemRenderer[item.product_type];

            if (renderer && document.getElementById(renderer)) {
                return 'miniquote_' + renderer;
            }

            return 'miniquote_defaultRenderer';
        },

        /**
         * @param {Object} updatedCart
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.quote.hasOwnProperty(key)) {
                    this.quote[key] = ko.observable();
                }
                this.quote[key](value);
            }, this);
        },

        /**
         * @param {String} name
         * @return {*}
         */
        getCartParamUnsanitizedHtml: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.quote.hasOwnProperty(name)) {
                    this.quote[name] = ko.observable();
                }
            }

            return this.quote[name]();
        },

        /**
         * @param {String} name
         * @return {*}
         */
        getQuoteParam: function (name) {
            return this.getCartParamUnsanitizedHtml(name);
        },

        /**
         * Returns array of cart items, limited by 'maxItemsToDisplay' setting
         * @return []
         */
        getCartItems: function () {
            var items = this.getCartParamUnsanitizedHtml('items') || [];

            items = items.slice(parseInt(-this.maxItemsToDisplay, 10));

            return items;
        },

        /**
         * @return {Number}
         */
        getCartLineItemsCount: function () {
            var items = this.getCartParamUnsanitizedHtml('items') || [];

            return parseInt(items.length, 10);
        }
    });
})();
