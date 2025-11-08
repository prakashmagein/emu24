define([
    'jquery',
    'mage/utils/wrapper',
    'mage/translate',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, wrapper, $t) {
    'use strict';

    const eventPrefix = 'customerAlsoBought';

    /**
     * Update super attribute for related products
     *
     * @param  {jQuery} $productItemElement
     */
    async function _updateRelatedSuper($productItemElement) {
        const { helper } = await import(swissupSoldTogether.helper);
        var superAttribute = {},
            itemSuper = {},
            $checkbox;

        superAttribute = helper.getRelatedSupers();
        $checkbox = $('.soldtogether-tocart', $productItemElement);

        if ($checkbox.is(':checked')) {
            $(helper.findProductOptions($productItemElement.get(0)))
                .each(function () {
                    itemSuper[helper.getAttributeId(this)] = helper.getOptionSelected(this);
                });

            superAttribute[$checkbox.val()] = itemSuper;
        } else {
            delete superAttribute[$checkbox.val()];
        }

        helper.setRelatedSupers(superAttribute);
    }

    /**
     * Update selected related products ids
     *
     * @param  {jQuery} $checkbox
     */
    async function _updateRelated($checkbox) {
        const { helper } = await import(swissupSoldTogether.helper);
        var ids,
            index;

        ids = helper.getRelatedIds();

        if ($checkbox.is(':checked')) {
            ids.push($checkbox.val());
        } else {
            index = ids.indexOf($checkbox.val());

            if (index !== -1) {
                ids.splice(index, 1);
            }
        }

        helper.setRelatedIds(ids);
    }

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
            $('.block-content', el).prepend($messages);
        }

        return $messages;
    }

    $.widget('swissup.customerAlsoBought', {
        component: 'Swissup_SoldTogether/js/customer-also-bought',

        /**
         * {@inheritdoc}
         */
        _init: function () {
            this._on({
                'change .product-item-info': function (event) {
                    _updateRelated($(event.currentTarget).find('.soldtogether-tocart'));
                    _updateRelatedSuper($(event.currentTarget));
                }
            });

            if ($.breeze)
                this._initBreezeTocartFormValidation();
            else
                this._initLumaTocartFormValidation();
        },

        showErrorInvalidOptions: async function () {
            const { validator } = await import(swissupSoldTogether.validator);
            const $el = $(this.element);
            const $messages = _getMessagesContainer($el);

            validator.showMessage({
                type: 'error',
                text: $t('Choose options for all selected products.')
            }, $messages.get(0));

            window.scrollTo({
                left: 0,
                top: $el.offset().top - 40,
                behavior: 'smooth'
            });
        },

        /**
         * Initialize additional validation on add to cart form when it is ready
         * (At Luma-based storefront)
         */
        _initLumaTocartFormValidation: async function () {
            const { helper } = await import(swissupSoldTogether.helper);
            const { validator } = await import(swissupSoldTogether.validator);
            const $el = $(this.element);
            const self = this;
            const formValidation = $(this.options.tocartForm).data('mageValidation');

            if (formValidation) {
                // Wrap default form validate to validate product options
                formValidation.validate.form = wrapper.wrap(formValidation.validate.form,
                    function (original) {
                        const $items = $el.find('.soldtogether-tocart:checked').parents('.product-item');
                        const $options = $([]);
                        const status = {
                            valid: true
                        };

                        _getMessagesContainer($el).html('');
                        $items.each((_index, item) => {
                            $options.push(...helper.findProductOptions(item))
                        });
                        
                        status.valid = validator.isValidOptions($options.get());

                        $el.trigger(`${eventPrefix}:validate`, { $items, status });

                        if (status.valid) {
                            return original();
                        }

                        self.showErrorInvalidOptions();

                        return false;
                    }
                );
            } else {
                $el.one('click', this._initLumaTocartFormValidation.bind(this));
            }
        },

        /**
         * Initialize additional validation on add to cart form when it is ready
         * (At Breeze-based storefront)
         */
        _initBreezeTocartFormValidation: async function () {
            const { helper } = await import(swissupSoldTogether.helper);
            const $el = $(this.element);
            const self = this;

            $(this.options.tocartForm).on('validateAfter', function (event, data) {
                const $items = $el.find('.soldtogether-tocart:checked').parents('.product-item');
                const options = [];

                if (!data.result.valid) return;

                _getMessagesContainer($el).html('');
                $items.each((_index, item) => options.push(...helper.findProductOptions(item)));
                data.result.valid = validator.isValidOptions($(options).get());
                $el.trigger(`${eventPrefix}:validate`, { $items, status: data.result });

                if (!data.result.valid) self.showErrorInvalidOptions();
            });
        }
    });

    return $.swissup.customerAlsoBought;
});
