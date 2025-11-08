define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {

            /**
             * Initialize tax configuration, initial settings, and options values.
             * @private
             */
            _initializeOptions: function () {
                var element;

                element = $(this.options.priceHolderSelector);

                if (!element.data('magePriceBox')) {
                    element.priceBox();
                }

                return this._super();
            },

            /**
             * Returns prices for configured products1
             *
             * @param {*} config - Products configuration
             * @returns {*}
             * @private
             */
            _calculatePrice: function (config) {
                var displayPrices, newPrices, priceBox;

                priceBox = this.element.closest('.product-info-main').find(this.options.priceHolderSelector);

                if (priceBox.length === 0) {
                    priceBox = $(this.options.priceHolderSelector);
                }

                displayPrices = priceBox.priceBox('option').prices;
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)] || {};

                _.each(displayPrices, function (price, code) {
                    displayPrices[code].amount = newPrices[code] ? newPrices[code].amount - displayPrices[code].amount : 0;
                });

                return displayPrices;
            }
        });

        return $.mage.configurable;
    };
});
