(function () {
    'use strict';

    $.view('ajaxproFloatingcart', 'minicart', {
        component: 'Swissup_Ajaxpro/js/view/floatingcart',
        checkoutUrl: window.checkout?.checkoutUrl,
        minicartSelector: '[data-block="ajaxpro-floating-cart"]',

        /** [create description] */
        afterRender: function () {
            this.initSidebar();
            this._super();
        },

        /** init sidebar widget */
        initSidebar: function () {
            this.initFloatingCart();
            this._super();
        },

        /** [getSidebarSettings description] */
        getSidebarSettings: function () {
            return $.extend(this._super(), {
                calcHeight: false
            });
        },

        /** [initFloatingCart description] */
        initFloatingCart: function () {
            var container = $('.cd-cart-container', $(this.minicartSelector));

            if (this.getCartLineItemsCount() > 0 && container.hasClass('empty')) {
                container.removeClass('empty');
            } else if (this.getCartLineItemsCount() === 0 && !container.hasClass('empty')) {
                container.removeClass('cart-open');
                container.addClass('empty');
            }

            $('.cd-cart-trigger', $(this.minicartSelector)).off().on('click', function (event) {
                var cartIsOpen = container.hasClass('cart-open');

                event.preventDefault();

                if (cartIsOpen) {
                    $('body').removeClass('swissup-ajaxpro-floating-cart-open');
                    container.removeClass('cart-open');
                } else {
                    container.addClass('cart-open');
                    $('body').addClass('swissup-ajaxpro-floating-cart-open');
                }
            });

            container.off().on('click', function (event) {
                var target, currentTarget;

                target = $(event.target);
                currentTarget = $(event.currentTarget);

                if (container.hasClass('cart-open') && target.get(0) === currentTarget.get(0)) {
                    container.removeClass('cart-open');
                }
            });
        }
    });
})();
