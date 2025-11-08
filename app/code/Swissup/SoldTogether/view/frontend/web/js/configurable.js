define([
    'jquery',
    'configurable'
], function ($, mageConfigurable) {
    'use strict';

    if ($.breeze)
        mageConfigurable = 'configurable';

    $.widget('swissup.soldtogetherConfigurable', mageConfigurable, {
        component: 'Swissup_SoldTogether/js/configurable',

        /**
         * {@inheritdoc}
         */
        _create: function () {
            this._super();
            this._cachedMedia = this.options.spConfig.images;
            this._on({
                'change .field.configurable': '_updateOptionElementAttribute'
            });
        },

        /**
         * {@inheritdoc}
         */
        _initializeOptions: function () {
            var options = this.options;

            this._super();

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                this.element.parents('.column.main').find(options.superSelector);
        },

        /**
         * Update data attribute for option field
         *
         * @param  {jQuery.Event} event
         */
        _updateOptionElementAttribute: function (event) {
            var dropdown = event.target;

            $(event.currentTarget)
                .attr('data-option-selected', dropdown.value)
                .attr('data-attribute-id', dropdown.id.replace(/[a-z]*/, ''));
        },

        /**
         * {@inheritdoc}
         */
        _fillSelect: function (element) {
            this._super(element);

            $(element).parents('.field.configurable')
                .attr('data-option-selected', '')
                .attr('data-attribute-id', '');
        },

        /**
         * {@inheritdoc}
         */
        _changeProductImage: function () {
            var images,
                $block = this.element.parents('.soldtogether-block'),
                $context;

            if (!this.simpleProduct) {
                return;
            }

            images = this._cachedMedia[this.simpleProduct];
            if (images) {
                if ($block.hasClass('amazon-default')) {
                    $context = $('#soldtogether-image-' + this.getSuperProductId());
                } else {
                    $context = this.element.parents('.product-item');
                }
                $context.find('.product-image-photo').attr('src', images[0].img);
            } else {
                $.ajax({
                    url: this.options.mediaCallback,
                    cache: true,
                    type: 'GET',
                    dataType: 'json',
                    data: {'product_id': this.simpleProduct},
                    success: (data) => {
                        this._cachedMedia[this.simpleProduct] = [
                            {'img': data.medium}
                        ];
                        this._changeProductImage();
                    }
                });
            }
        },

        /**
         * @return {Number}
         */
        getSuperProductId: function () {
            return this.options.spConfig.productId;
        }
    });

    return $.swissup.soldtogetherConfigurable;
});
