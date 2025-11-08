define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/translate'
], function ($, utils, $t) {
    'use strict';

    const eventPrefix = 'frequentlyBoughtTogether';

    /**
     * Get contained to messages
     *
     * @param  {HTMLElement} el
     * @return {jQuery}
     */
    function _getMessagesContainer(el) {
        var $messages;

        $messages = $('.messages', el);

        if (!$messages.length) {
            $messages = $('<div class="messages"></div>');

            if ($(el).hasClass('amazon-stripe')) {
                $('.block-content', el).before($messages);
            } else {
                $('.amazonstyle-checkboxes', el).before($messages);
            }
        }

        return $messages;
    }

    function _getUiPriceBox($el) {
        try {
            return $el.priceBox('instance');
        } catch (error) {
            return $el.data('mage-priceBox') || $el.data('priceBox');
        }
    }

    $.widget('swissup.frequentlyBoughtTogether', {
        component: 'Swissup_SoldTogether/js/frequently-bought-together',

        options: {
            taxDisplay: '0',
            priceFormat: {},
            mainProductPriceBox: '.product-info-price [data-role=priceBox]'
        },

        /**
         * {@inheritdoc}
         */
        _init: function () {
            this.valuesToRestore = false;
            this._addObservers();
            this.updateTotals();
        },

        /**
         * Initialize observers
         */
        _addObservers: function () {
            this._on({
                'change .relatedorderamazon-checkbox': 'toggleItem',
                'click .soldtogether-cart-btn': 'addToCartItems',
                'click .details-toggler': 'handleDetailsToggle',
                // listen events from priceBox widget of Magento_Catalog
                'reloadPrice': 'updateTotals'
            });

            $(this.options.mainProductPriceBox).on(
                'updatePrice.frequentlyBoughtTogether',
                this._onUpdateMainProductPrice.bind(this)
            );
            $(document).on(
                'ajax:addToCart.frequentlyBoughtTogether',
                this.restoreRelatedProductsField.bind(this)
            );
        },

        handleDetailsToggle: function (event) {
            const { currentTarget: toggler } = event;
            event.stopPropagation();
            event.preventDefault();
            import(swissupSoldTogether.helper).then(({ helper }) =>
                helper.toggleElementHidden(toggler)
            );
        },

        /**
         * Get selected elements
         */
        getItems: function () {
            var items = $('.amazonstyle-checkboxes .product-item-details, .amazonstyle-images li', this.element);

            return items.filter((index, item) =>
                this.findToggleCheckbox($(item)).is(':checked')
            );
        },

        findToggleCheckbox: function ($item) {
            return $item.find('.checkbox[name="bought_related_products[]"]');
        },

        /**
         * Update totals near add to cart button
         */
        updateTotals: function () {
            const $priceContainer = $(this.element).find('.totalprice .price-box .price-container');
            const totals = this.collectTotalPrices();
            const formatPrice = price => utils.formatPrice(price, this.options.priceFormat)

            if (this.options.taxDisplay === '3') {
                $priceContainer
                    .find('.price-including-tax .price')
                    .html(formatPrice(totals.priceIncludingTax));
                $priceContainer
                    .find('.price-excluding-tax .price')
                    .html(formatPrice(totals.priceExcludingTax));
            } else {
                $priceContainer
                    .find('.price-wrapper .price')
                    .html(formatPrice(totals.price));
            }
        },

        collectTotalPrices: function () {
            const prices = {
                price: 0,
                priceExcludingTax: 0,
                priceIncludingTax: 0
            };

            this.getItems().each((i, item) => {
                const $priceContainer = $(item).find('.price-box .price-container');
                const priceBox = _getUiPriceBox($(item).find('.price-box'));

                prices.price += (priceBox && priceBox.cache) ?
                    priceBox.cache.displayPrices?.finalPrice?.amount :
                    ($priceContainer.find('[data-price-type="finalPrice"]').data('price-amount') || 0);

                if (this.options.taxDisplay === '3') {
                    prices.priceIncludingTax += $priceContainer.find('.price-including-tax').data('price-amount');
                    prices.priceExcludingTax += $priceContainer.find('.price-excluding-tax').data('price-amount');
                }
            });

            return prices;
        },

        /**
         * Add to cart selected items
         */
        addToCartItems: async function (event) {
            const { helper } = await import(swissupSoldTogether.helper);
            const $items = this.getItems();

            var submitIds = [],
                submitSuperAttribute = {};

            if (!(await this.validate())) {
                return;
            }

            this.valuesToRestore = helper.getValuesToRestore();

            window.scrollTo({top: 0, left: 0, behavior: 'smooth'});

            submitIds = this.findToggleCheckbox($items)
                .filter((index, checkbox) => !$(checkbox).hasClass('main-product'))
                .map((index, checkbox) => checkbox.value)
                .get();
            $items.each((index, item) => {
                const $checkbox = this.findToggleCheckbox($(item)),
                    $productOptions = $(helper.findProductOptions(item));

                var itemSuper = {},
                    itemCustom = '';

                if (!$checkbox.is(':checked')) {
                    return;
                }

                if ($productOptions.length) {
                    $productOptions.each((index, productOption) => {
                        itemSuper[helper.getAttributeId(productOption)] =
                            helper.getOptionSelected(productOption);
                    });
                    submitSuperAttribute[$checkbox.val()] = itemSuper;
                }
            });

            helper.setRelatedData(submitIds, submitSuperAttribute, 'order');

            $(this.element)
                .trigger(`${eventPrefix}:addToCartItems`, { $items });

            $('#product-addtocart-button').trigger('click');
        },

        /**
         * Validate selected items
         *
         * @return {Boolean}
         */
        validate: async function () {
            const { helper } = await import(swissupSoldTogether.helper);
            const { validator } = await import(swissupSoldTogether.validator);
            const $items = this.getItems();
            const status = {
                valid: true
            };

            var $messages,

            $messages = _getMessagesContainer(this.element).html('');

            // Check product options and show when they are invalid
            $items.each(function () {
                var $productOptions = $(helper.findProductOptions(this)),
                    $wrapper = $(this).find('.details-wrapper');

                if (!validator.isValidOptions($productOptions.get()) || $wrapper.find('.breeze-placeholder').length) {
                    status.valid = false;
                    $wrapper.each((i, d) => { d.hidden = false; });
                }
            });

            $(this.element)
                .trigger(`${eventPrefix}:validate`, { $items, status });

            if (status.valid) {
                return true;
            }

            validator.showMessage({
                type: 'error',
                text: $t('Choose options for all selected products.')
            }, $messages.get(0));

            window.scrollTo({
                left: 0,
                top: $messages.offset().top - 100,
                behavior: 'smooth'
            });

            return false;
        },

        /**
         * Remove items added in addToCartItems method
         */
        restoreRelatedProductsField: async function () {
            const { helper } = await import(swissupSoldTogether.helper);
            if (this.valuesToRestore !== false) {
                helper.restoreValuesToRelatedProductsField(this.valuesToRestore);
                this.valuesToRestore = false;
            }
        },

        /**
         * @param  {jQuery.Event} event
         * @return void
         */
        _onUpdateMainProductPrice: function (event) {
            const $outerBox = $(event.target);
            const outerPriceBox = _getUiPriceBox($outerBox);
            const $innerBox = this.element.find('[data-role="priceBox"][data-product-id="' + outerPriceBox?.options?.productId + '"]');
            const innerPriceBox = _getUiPriceBox($innerBox);

            if (innerPriceBox) {
                innerPriceBox.cache = $.extend({}, outerPriceBox.cache);
                innerPriceBox.element.find('[data-price-type="finalPrice"]').data(
                    'price-amount',
                    innerPriceBox.cache?.displayPrices?.finalPrice?.amount
                );
                innerPriceBox.element.trigger('reloadPrice');
            }
        },

        /**
         * Toggle item when checkbox changed
         *
         * @param  {Event} event
         */
        toggleItem: function (event) {
            var $checkbox = $(event.currentTarget),
                $image = $('#soldtogether-image-' + $checkbox.val());

            if ($checkbox.is(':checked')) {
                $image.removeClass('item-inactive');
                $image.prev('.plus').removeClass('item-inactive');
            } else {
                $image.addClass('item-inactive');
                $image.prev('.plus').addClass('item-inactive');
            }

            this.updateTotals();
        },

        destroy: function () {
            $(this.options.mainProductPriceBox).off('updatePrice.frequentlyBoughtTogether');
            $(document).off('ajax:addToCart.frequentlyBoughtTogether');
            this._super();
        }
    });

    return $.swissup.frequentlyBoughtTogether;
});
