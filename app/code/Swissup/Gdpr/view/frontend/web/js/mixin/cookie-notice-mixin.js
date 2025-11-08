define([
    'jquery'
], function ($) {
    'use strict';

    return function (target) {
        if (!window.swissupGdprCookieSettings) {
            return target;
        }

        $.widget('mage.cookieNotices', target, {
            _create: function () {
                this._super();
                this.element.hide();
            }
        });

        return $.mage.cookieNotices;
    };
});
