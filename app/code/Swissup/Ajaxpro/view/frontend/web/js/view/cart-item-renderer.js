/**
 *  Fix for 2.4.2
 *  after commit https://github.com/magento/magento2/commit/9df9db2d742feb1f1e28e9394a437c13a2b5a054
 */
define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * Prepare the product name value to be rendered as HTML
         *
         * @param {String} productName
         * @return {String}
         */
        getProductNameUnsanitizedHtml: function (productName) {
            // product name has already escaped on backend
            return productName;
        },

        /**
         * Prepare the given option value to be rendered as HTML
         *
         * @param {String} optionValue
         * @return {String}
         */
        getOptionValueUnsanitizedHtml: function (optionValue) {
            // option value has already escaped on backend
            return optionValue;
        }
    });
});
