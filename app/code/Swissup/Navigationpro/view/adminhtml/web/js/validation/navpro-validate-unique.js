define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    return function (target) {

        target['navpro-validate-unique'] = {
            handler: function (value, used) {
                return used.indexOf(value) === -1;
            },
            message: $.mage.__('This name is already in use.')
        };

        return target;
    }
});
