define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            imageWidth: false,
            imageHeight: false,
            isShown: false,
            inverseVisibility: false,
            listens: {
                'imageWidth': 'onWidthChanged',
                'imageHeight': 'onHeightChanged'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this
                ._super()
                .observe('imageWidth')
                .observe('imageHeight');
        },

        /**
         * @param {Number} selected
         */
        onWidthChanged: function () {
            this.toggleVisibility();
        },

        /**
         * @param {Number} selected
         */
        onHeightChanged: function () {
            this.toggleVisibility();
        },

        /**
         * Toggle visibility state.
         *
         * @param {Number} selected
         */
        toggleVisibility: function () {
            this.isShown = false;

            // initial page load
            if (this.imageWidth() === false || this.imageHeight() === false) {
                this.isShown = true;
            }

            // observable events
            if (this.imageWidth() > 0 && this.imageHeight() > 0) {
                this.isShown = true;
            }

            this.isShown = this.inverseVisibility ? !this.isShown : this.isShown;

            if (!this.isShown) {
                this.value(false);
            }

            this.visible(this.isShown);
        }
    });
});
