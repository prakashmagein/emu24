define([
    'Swissup_Gdpr/js/model/cookie-manager'
], function (cookieManager) {
    'use strict';

    var original;

    if (!window.swissupGdprCookieSettings) {
        return;
    }

    original =
        Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
        Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');

    Object.defineProperty(document, 'cookie', {
        configurable: true,

        /**
         * @return {String}
         */
        get: function () {
            return original.get.apply(document);
        },

        /**
         * @param {String} value
         */
        set: function (value) {
            var params, pair, name;

            if (value.split) {
                params = value.split(';');
                pair = params[0].split('=');
                name = pair[0];

                if (!cookieManager.cookie(name).status()) {
                    return;
                }
            }

            return original.set.apply(document, arguments);
        }
    });
});
