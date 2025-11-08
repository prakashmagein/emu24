define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    return function (target) {

        target['easybanner-validate-pattern'] = {
            handler: function (value, pattern) {
                return new RegExp(pattern).test(value);
            },
            message: $.mage.__('Invalid format.')
        };

        return target;
    }
});
