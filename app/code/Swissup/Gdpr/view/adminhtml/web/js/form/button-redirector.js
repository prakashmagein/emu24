define([
    'jquery',
    'Magento_Ui/js/form/button-adapter'
], function ($, Adapter) {
    'use strict';

    return Adapter.extend({
        /**
         * @returns {Object}
         */
        initActions: function () {
            var self = this;

            /** Callback function. */
            this.callback = function () {
                window.location = self.url;
            };

            return this;
        }
    });
});
