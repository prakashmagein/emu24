(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'underscore', 'dropdown', 'mage/translate'], factory);
    } else {
        /* global _ */
        $.qtySwitcher = factory($, _);
    }
}(function ($, _) {
    'use strict';

    const wrapperTemplate = '<div class="qty-wrapper qty-<%- switcherType %>"></div>';
    const dropdownTemplate = '<div class="action toggle trigger" ' +
            'data-toggle="dropdown" ' +
            'data-trigger-keypress-button="true"' +
            '><span>' + $.mage.__('Select qty') + '</span></div>' +
            '<ul class="dropdown" data-target="dropdown"></ul>';
    const dropdownItemTemplate = '<li tabindex="0"><%- label %></li>';

    return function (config, qtyInput) {

        var QuantitySwitcher = {
            /**
             * Find selected simple product for configurable product.
             *
             * @return {Number|undefined}
             */
            getSelectedProductForConfigurable: function () {
                var options = $('[data-role=swatch-options]'),
                    widget = options.data('mageSwatchRenderer') ?
                        options.data('mageSwatchRenderer') : options.data('mage-SwatchRenderer');

                if (widget) {
                    return widget.getProduct();
                }

                // Basic configurable dropdown
                return $('#product_addtocart_form').data('mageConfigurable')?.simpleProduct;
            },

            /**
             * Find selected simple product for grouped product.
             *
             * @return {Number|undefined}
             */
            getSelectedProductForGrouped: function () {
                // Match ID enclosed by square brackets
                return $(qtyInput).attr('name').match(/\[(.*?)\]/)[1];
            },

            /**
             * Listener for qty arrows
             *
             * @param  {Event} event
             */
            changeQty: function (event) {
                var stockConfig = this.getStockConfig(),
                    qtyInc, minQty, maxQty, value, curValue;

                if (_.isEmpty(stockConfig)) {
                    if (config[0].type === 'configurable') {
                        $('#product_addtocart_form').valid();
                    }

                    return;
                }

                qtyInc = stockConfig.qtyInc;
                minQty = stockConfig.minQty;
                maxQty = stockConfig.maxQty;

                if ($(qtyInput).prop('disabled')) {
                    return;
                }

                value = $(qtyInput).val();
                value = isNaN(value) ? minQty : parseFloat(value);

                if (value < minQty) {
                    this.setQtyValue(minQty);

                    return;
                } else if (value > maxQty) {
                    this.setQtyValue(maxQty);

                    return;
                }

                if ($(event.currentTarget).hasClass('qty-switcher-dec')) {
                    curValue = value - qtyInc;
                    this.setQtyValue(curValue >= minQty ? curValue : minQty);
                } else {
                    curValue = value + qtyInc;
                    this.setQtyValue(curValue <= maxQty ? curValue : maxQty);
                }
            },

            /**
             * Initialize qty switcher
             */
            initialize: function () {
                var decElement,
                    incElement,
                    wrapper;

                $(qtyInput).wrap(_.template(wrapperTemplate)({
                    switcherType: config[0].switcher
                }));

                if (config[0].switcher === 'dropdown') {
                    wrapper = $(qtyInput).parent();
                    $(qtyInput).after(_.template(dropdownTemplate)());

                    $('.action.trigger', wrapper)
                        .dropdown()
                        .on('click.toggleDropdown', function (event) {
                            _.defer(this.toogleDropdownClick.bind(this), event);
                        }.bind(this));
                    $('.dropdown', wrapper)
                        .on('click', (event) => this.dropdownItemClick(event.target))
                        .on('keydown', (event) => {
                            if (event.key === 'Enter') {
                                $(event.target).trigger('click');
                            }
                        });
                } else {
                    decElement = $('<div class="qty-switcher-dec"></div>');
                    incElement = $('<div class="qty-switcher-inc"></div>');

                    $(decElement).insertBefore(qtyInput);
                    $(incElement).insertAfter(qtyInput);

                    $(decElement).on('click', this.changeQty.bind(this));
                    $(incElement).on('click', this.changeQty.bind(this));
                }
            },

            /**
             * Rebuild items of qty dropdown
             *
             * @param  {jQuery} dropdown
             */
            rebuildItems: function (dropdown) {
                var html = '',
                    newItems = this.getDropdownItems();

                newItems.push('Custom');
                newItems.forEach(function (qty) {
                    html += _.template(dropdownItemTemplate)({
                        label: qty
                    });
                });
                dropdown.html(html);
            },

            /**
             * Get dropdown items for qty dropdown
             *
             * @return {Array}
             */
            getDropdownItems: function () {
                var stockConfig = this.getStockConfig(),
                    range,
                    items;

                if (_.isEmpty(stockConfig)) {
                    return [];
                }

                range = _.range(stockConfig.minQty, stockConfig.maxQty, stockConfig.qtyInc);
                items = range.slice(0, 5);
                items.push(range[9], range[19], range[49], range[74], stockConfig.maxQty);

                items = _.sortBy(items, function (num) {
                    return num;
                });

                return _.uniq(items.filter(Boolean));
            },

            /**
             * Get stock config for current product
             *
             * @return {Object}
             */
            getStockConfig: function () {
                if (config[0].type === 'configurable') {
                    return _.findWhere(config, {
                        id: this.getSelectedProductForConfigurable()
                    });
                } else if (config[0].type === 'grouped') {
                    return _.findWhere(config, {
                        id: this.getSelectedProductForGrouped()
                    });
                }

                return config[1];
            },

            /**
             * Listen click on dropdown item
             *
             * @param  {DOMElement} item
             */
            dropdownItemClick: function (item) {
                if ($(item).prop('tagName') === 'LI') {
                    if (isNaN($(item).html())) {
                        $(qtyInput).select().focus();
                    } else {
                        this.setQtyValue($(item).html());
                    }
                }
            },

            /**
             * Listen toggle dropdown to rebuild items
             *
             * @param  {Event} event
             */
            toogleDropdownClick: function (event) {
                if ($(event.target).attr('aria-expanded') === 'true') {
                    this.rebuildItems($(event.target).siblings('.dropdown'));
                }
            },

            /**
             * Qty field value setter
             *
             * @param {Number} value
             */
            setQtyValue: function (value) {
                $(qtyInput).val(value);
                $(qtyInput).trigger('input');
                $(qtyInput).trigger('change');
            }
        };

        QuantitySwitcher.initialize();
    };
}));
