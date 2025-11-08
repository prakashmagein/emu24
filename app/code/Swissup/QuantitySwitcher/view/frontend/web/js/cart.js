define([
    'jquery'
], function($) {
    'use strict';

    $.widget('swissup.quantitySwitcherCart', {
        component: 'Swissup_QuantitySwitcher/js/cart',
        wrapperTemplate: '<div class="qty-wrapper qty-arrows"></div>',
        qtyInput: null,

        _create: function () {
            const decElement = $('<div class="qty-switcher-dec"></div>'),
                  incElement = $('<div class="qty-switcher-inc"></div>');

            this.qtyInput = this.element;
            this.element.wrap(this.wrapperTemplate);

            decElement.insertBefore(this.element);
            incElement.insertAfter(this.element);

            decElement.on('click', this.changeQty.bind(this));
            incElement.on('click', this.changeQty.bind(this));
        },

        /**
         * Listener for qty arrows
         *
         * @param  {Event} event
         */
        changeQty: function (event) {
            var qtyInc = 1,
                minQty = 1,
                value,
                curValue;

            if ($(this.qtyInput).attr('disabled') === 'disabled') {
                return;
            }

            value = $(this.qtyInput).val();
            value = isNaN(value) ? minQty : parseFloat(value);

            if (value < minQty) {
                this.setQtyValue(minQty);

                return;
            }

            if ($(event.currentTarget).hasClass('qty-switcher-dec')) {
                curValue = value - qtyInc;
                this.setQtyValue(curValue >= minQty ? curValue : minQty);
            } else {
                curValue = value + qtyInc;
                this.setQtyValue(curValue);
            }
        },

        /**
         * Qty field value setter
         *
         * @param {Number} value
         */
        setQtyValue: function (value) {
            $(this.qtyInput).val(value);
            $(this.qtyInput).trigger('input');
            $(this.qtyInput).trigger('change');
        }
    });

    return $.swissup.quantitySwitcherCart;
});
