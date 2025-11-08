define(['jquery'], function ($) {
    'use strict';

    var modalWidgetMixin = {
        create: function () {
            var self = this;
            this.element.validator();

            this.element.on("submit.validate", function( event ) {
                if (window.addToQuote) {
                    self.element.catalogAddToQuote({
                        bindSubmit: false,
                        quoteFormUrl: window.addToQuote
                    });
                    self.element.catalogAddToQuote('instance').ajaxSubmit(self.element);
                    window.addToQuote = false;
                } else {
                    self.element.catalogAddToCart(self.options);
                    self.element.catalogAddToCart('instance').ajaxSubmit(self.element);
                }

                return false;
            });
        }
    };

    $.mixin('productValidate', modalWidgetMixin); // Breeze fix: apply mixin
});
