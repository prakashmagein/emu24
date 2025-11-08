define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            resizer: false,
            responsive: false
        },

        /**
         * @param {Number} value
         */
        toggleResizer: function (value) {
            this.resizer = parseInt(value, 10);
            this.updateVisibility();
        },

        /**
         * @param {Number} value
         */
        toggleResponsive: function (value) {
            this.responsive = parseInt(value, 10);
            this.updateVisibility();
        },

        /**
         * Updates element visibility
         */
        updateVisibility: function () {
            this.visible(this.resizer && this.responsive);
        }
    });
});
