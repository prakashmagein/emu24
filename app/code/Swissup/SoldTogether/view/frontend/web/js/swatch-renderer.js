define([
    'jquery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, mageSwatchRenderer) {
    'use strict';

    if ($.breeze)
        mageSwatchRenderer = 'SwatchRenderer';

    $.widget('swissup.soldtogetherSwatchRenderer', mageSwatchRenderer, {
        component: 'Swissup_SoldTogether/js/swatch-renderer',

        /**
         * @return {Number}
         */
        getSuperProductId: function () {
            return this.options.jsonConfig.productId;
        },

        /**
         * {@inheritdoc}
         */
        updateBaseImage: function (images, context, isInProductView) {
            var $block = this.element.parents('.soldtogether-block');

            if ($block.hasClass('amazon-default') ||
                $block.hasClass('amazon-stripe')
            ) {
                context = $('#soldtogether-image-' + this.getSuperProductId());
            }

            return this._super(images, context, isInProductView);
        },

        /**
         * {@inheritdoc}
         */
        _EnableProductMediaLoader: function ($this) {
            var $block = $this.parents('.soldtogether-block'),
                $item = $this.parents('.product-item-info');

            if ($block.hasClass('amazon-default')) {
                $item = $('#soldtogether-image-' + this.getSuperProductId());
            }

            $item.find('.product-image-photo').addClass(this.options.classes.loader);
        },

        /**
         * {@inheritdoc}
         */
        _DisableProductMediaLoader: function ($this) {
            var $block = $this.parents('.soldtogether-block'),
                $item = $this.parents('.product-item-info');

            if ($block.hasClass('amazon-default')) {
                $item = $('#soldtogether-image-' + this.getSuperProductId());
            }

            $item.find('.product-image-photo').removeClass(this.options.classes.loader);
        },

        /**
         * {@inheritdoc}
         */
        _RenderControls: function () {
            this._super();
            this._updateARIA();
        },

        /**
         * Update ARIA attributes of swatch controls
         */
        _updateARIA: function() {
            const me = this;
            const uuid = me.uuid;

            $.each(me.options.jsonConfig.attributes, (i, item) => {
                const controlLabelId = 'option-label-' + item.code + '-' + item.id;
                const newControlLabelId = controlLabelId + '-uuid-' + uuid;

                me.element.find('#' + controlLabelId).attr('id', newControlLabelId);
                me.element.find('[aria-labelledby=' + controlLabelId + ']').attr('aria-labelledby', newControlLabelId);
                me.element.find('[aria-describedby=' + controlLabelId + ']').attr('aria-describedby', newControlLabelId);
                me.element.find('[id^=' + controlLabelId + '-item-]').each((i, el) => {
                    $(el).attr('id', el.id.replace(controlLabelId, newControlLabelId));
                });
            });
        }
    });

    return $.swissup.soldtogetherSwatchRenderer;
});
